#!/usr/bin/env ruby
# Archivo: tools/api_key_rotator/api_key_rotator.rb
#
# Cliente de IA y Procesamiento Genérico con "API Key Rotation".
# Esta herramienta extrae y encripta las llaves hacia un apis.json separado
# para mayor seguridad de los tokens.
#
# Comandos de CLI:
#   ./api_key_rotator.rb add <API_KEY> <REFERENCIA>  # Encripta y añade llave
#   ./api_key_rotator.rb list                        # Muestra llaves encriptadas
# Si se llama sin argumentos, lee un STDIN JSON como cliente LLM.

require 'net/http'
require 'json'
require 'uri'
require 'openssl'
require 'base64'
require 'fileutils'
require 'socket'
require 'time'

# Módulo para uso programático (desde otras clases)
module ApiRotator
  DIR = File.expand_path(File.dirname(__FILE__))
  APIS_FILE = File.join(DIR, "apis.json")
  HEALTH_FILE = File.join(DIR, ".key_health.json")
  SECRET_FILE = File.join(DIR, ".secret.key")
  SOCK_PATH = "/tmp/groq_candado_#{Process.uid rescue '0'}.sock"

  class << self
    def get_passphrase
      if ENV['GROQ_ROTATOR_PASS'] && !ENV['GROQ_ROTATOR_PASS'].empty?
        return ENV['GROQ_ROTATOR_PASS']
      end

      # Intentar leer desde el demonio en RAM
      begin
        if File.exist?(SOCK_PATH)
          sock = UNIXSocket.new(SOCK_PATH)
          pass = sock.gets
          sock.close
          return pass.chomp if pass && !pass.strip.empty?
        end
      rescue
        # El socket no existe o el demonio murió
      end

      hint = load_apis["_hint"] || "Sin pista configurada"
      unless STDIN.tty?
        raise "⚠️ No hay parámetro GROQ_ROTATOR_PASS configurado, y el entorno no es interactivo."
      end

      require 'io/console'
      STDERR.print "🔑 Ingresa la Frase Secreta (Pista: #{hint}): "
      pass = STDIN.noecho(&:gets).chomp
      STDERR.puts

      if pass && !pass.empty?
        spawn_memorizer_daemon(pass)
        STDERR.puts "🔓 Candado virtual abierto. Memorizado de forma segura en RAM por 1 hora."
      end

      pass
    end

    def spawn_memorizer_daemon(pass)
      File.delete(SOCK_PATH) if File.exist?(SOCK_PATH)

      pid = fork do
        begin
          Process.setsid
          server = UNIXServer.new(SOCK_PATH)
          File.chmod(0600, SOCK_PATH)

          Thread.new do
            sleep 3600
            File.delete(SOCK_PATH) rescue nil
            exit!
          end

          loop do
            client = server.accept
            client.puts pass
            client.close
          end
        rescue
          exit!
        end
      end
      Process.detach(pid) rescue nil
    end

    def get_cipher_key
      pass = get_passphrase
      OpenSSL::Digest::SHA256.digest(pass)
    end

    def encrypt(data)
      cipher = OpenSSL::Cipher.new('aes-256-cbc')
      cipher.encrypt
      cipher.key = get_cipher_key
      iv = cipher.random_iv
      encrypted = cipher.update(data) + cipher.final
      Base64.strict_encode64(iv + encrypted)
    end

    def decrypt(data_base64)
      raw_data = Base64.strict_decode64(data_base64)
      iv = raw_data[0..15]
      encrypted = raw_data[16..-1]
      decipher = OpenSSL::Cipher.new('aes-256-cbc')
      decipher.decrypt
      decipher.key = get_cipher_key
      decipher.iv = iv
      decipher.update(encrypted) + decipher.final
    end

    def load_apis
      return {} unless File.exist?(APIS_FILE)
      JSON.parse(File.read(APIS_FILE))
    end

    def save_apis(data)
      File.write(APIS_FILE, JSON.pretty_generate(data))
    end

    # Estado de salud de las keys (persistido en .key_health.json)
    # Formato: { ref_name => { status: :available|:rate_limited|:restricted,
    #                           available_at: Time, reason: String } }
    def key_health
      @key_health ||= load_health
    end

    def load_health
      return {} unless File.exist?(HEALTH_FILE)
      raw = JSON.parse(File.read(HEALTH_FILE))
      health = {}
      raw.each do |ref, data|
        avail = begin
          Time.parse(data["available_at"].to_s)
        rescue
          Time.now
        end
        since = begin
          Time.parse(data["restricted_since"].to_s)
        rescue
          nil
        end
        health[ref] = {
          status: (data["status"] || "available").to_sym,
          available_at: avail,
          reason: data["reason"],
          restricted_since: since,
          fail_count: data["fail_count"].to_i
        }
      end
      health
    rescue
      {}
    end

    def save_health
      data = {}
      key_health.each do |ref, h|
        entry = {
          "status" => h[:status].to_s,
          "available_at" => h[:available_at].iso8601,
          "reason" => h[:reason],
          "fail_count" => h[:fail_count].to_i
        }
        entry["restricted_since"] = h[:restricted_since].iso8601 if h[:restricted_since]
        data[ref] = entry
      end
      File.write(HEALTH_FILE, JSON.pretty_generate(data))
    end

    def list_keys
      data = load_apis
      if data.empty?
        puts "No hay llaves guardadas en apis.json."
        return
      end
      puts "\n🔑 LLAVES REGISTRADAS PARA ROTACIÓN\n"
      puts "=" * 65

      counts = { available: 0, rate_limited: 0, restricted: 0 }

      data.each_with_index do |(ref, encrypted), i|
        next if ref == "_hint"
        begin
          desc = decrypt(encrypted)
          preview = "#{desc[0..5]}...#{desc[-4..-1]}"

          health = key_health[ref]
          if health.nil? || health[:status] == :available
            status_icon = "🟢"
            status_text = "disponible"
            counts[:available] += 1
          elsif health[:status] == :rate_limited
            secs = [(health[:available_at] - Time.now).ceil, 0].max
            status_icon = "🟡"
            status_text = secs > 0 ? "rate limit (libre en #{secs}s)" : "disponible"
            counts[secs > 0 ? :rate_limited : :available] += 1
          elsif health[:status] == :restricted
            secs = [(health[:available_at] - Time.now).ceil, 0].max
            since = health[:restricted_since]
            since_text = since ? "desde #{since.strftime('%d/%m %H:%M')}" : ""
            fails = health[:fail_count].to_i
            status_icon = "🔴"
            if secs > 0
              status_text = "restringida #{since_text} (reintento en #{format_duration(secs)}, fallos: #{fails})"
            else
              status_text = "restringida #{since_text} (reintentable ahora, fallos: #{fails})"
            end
            counts[:restricted] += 1
          end

          puts "  #{status_icon} #{i + 1}. [#{ref}] -> #{preview}"
          puts "       #{status_text}"
        rescue
          puts "  ⚫ #{i + 1}. [#{ref}] -> (Error de desencriptado)"
        end
      end
      puts "=" * 65
      puts "  📊 Resumen: 🟢 #{counts[:available]} disponibles | 🟡 #{counts[:rate_limited]} rate limited | 🔴 #{counts[:restricted]} restringidas"
      puts
    end

    def add_key(api_key, reference)
      data = load_apis
      data[reference] = encrypt(api_key)
      save_apis(data)
      key_health[reference] = { status: :available, available_at: Time.now, reason: nil }
      puts "✅ Llave (Ref: '#{reference}') encriptada AES-256-CBC y guardada exitosamente en apis.json."
    end

    def execute_with_rotation
      apis = load_apis
      if apis.empty?
        raise "No hay llaves configuradas. Usa: ./api_key_rotator.rb add <API_KEY> <REFERENCIA>"
      end

      api_keys = []
      api_refs = []

      apis.each do |ref, enc|
        next if ref == "_hint"
        begin
          api_keys << decrypt(enc)
          api_refs << ref
        rescue
          STDERR.puts "   ⚠️ Llave de [#{ref}] corrupta. Ignorando."
        end
      end

      if api_keys.empty?
        raise "❌ No quedó ninguna llave válida después de desencriptar."
      end

      # Inicializar salud de keys nuevas
      api_refs.each do |ref|
        key_health[ref] ||= { status: :available, available_at: Time.now, reason: nil }
      end

      max_ciclos = 3

      max_ciclos.times do |ciclo|
        # Ordenar: disponibles primero, luego por available_at más cercano
        indices = (0...api_keys.length).sort_by do |i|
          h = key_health[api_refs[i]]
          case h[:status]
          when :available then [0, 0]
          when :rate_limited then [1, h[:available_at].to_f]
          when :restricted then [2, h[:available_at].to_f]
          else [3, 0]
          end
        end

        intento_alguna = false

        indices.each do |idx|
          ref_name = api_refs[idx]
          key = api_keys[idx]
          health = key_health[ref_name]

          # Si está en cooldown, verificar si ya pasó
          if health[:status] != :available
            if Time.now >= health[:available_at]
              STDERR.puts "   🔄 [Rotator] Reintentando [#{ref_name}] (cooldown expirado)..."
              health[:status] = :available
              health[:reason] = nil
            else
              next
            end
          end

          intento_alguna = true
          res = yield(key)

          # Si es un Hash (ya parseado), asumir exitoso
          if res.is_a?(Hash)
            mark_key_available(ref_name)
            return res
          end

          case res.code
          when "200"
            mark_key_available(ref_name)
            return JSON.parse(res.body)

          when "429"
            segundos = extract_retry_seconds(res)
            mark_key_rate_limited(ref_name, segundos)
            next

          when "400", "403"
            error_msg = extract_error_message(res)
            if error_msg.include?("restricted") || error_msg.include?("invalid") || error_msg.include?("decommissioned")
              mark_key_restricted(ref_name, 300, error_msg)
              next
            else
              raise "Error API (HTTP #{res.code}): #{res.body}"
            end

          when "401"
            mark_key_restricted(ref_name, 3600, "API key invalida o expirada")
            next

          else
            raise "Error API (HTTP #{res.code}): #{res.body}"
          end
        end

        # Si no intentó ninguna, todas en cooldown — esperar la más próxima (no-restringida primero)
        unless intento_alguna
          # Priorizar rate_limited (segundos) sobre restricted (horas)
          waitables = key_health.select { |ref, h| api_refs.include?(ref) && h[:status] == :rate_limited }
          waitables = key_health.select { |ref, _| api_refs.include?(ref) } if waitables.empty?

          earliest_ref, earliest_h = waitables.min_by { |_, h| h[:available_at] }

          if earliest_ref
            wait_secs = [(earliest_h[:available_at] - Time.now).ceil, 1].max
            # No esperar más de 60s en una iteración (las restringidas pueden tener horas)
            wait_secs = [wait_secs, 60].min
            STDERR.puts "\n   ⏳ [Rotator] Todas las llaves en cooldown. Esperando #{wait_secs}s por [#{earliest_ref}]..."
            sleep(wait_secs)
          else
            raise "❌ No hay llaves disponibles."
          end
        end
      end

      STDERR.puts "\n   ❌ [Rotator] Estado final tras #{max_ciclos} ciclos:"
      api_refs.each do |ref|
        h = key_health[ref]
        secs = [(h[:available_at] - Time.now).ceil, 0].max
        icon = h[:status] == :available ? "🟢" : (h[:status] == :rate_limited ? "🟡" : "🔴")
        STDERR.puts "      #{icon} [#{ref}] -> #{h[:status]} (#{h[:reason] || 'ok'}, libre en #{secs}s)"
      end
      raise "❌ Se agotaron todos los intentos con #{api_keys.length} llaves API."
    end

    private

    def mark_key_available(ref_name)
      key_health[ref_name] = {
        status: :available, available_at: Time.now,
        reason: nil, restricted_since: nil, fail_count: 0
      }
      save_health
    end

    def mark_key_rate_limited(ref_name, segundos)
      cooldown = segundos || 30
      key_health[ref_name] = {
        status: :rate_limited,
        available_at: Time.now + cooldown,
        reason: "429 rate limit (#{cooldown}s)"
      }
      save_health
      STDERR.puts "\n   🟡 [Rotator] [#{ref_name}] -> rate limit. Disponible en ~#{cooldown}s. Rotando..."
    end

    def mark_key_restricted(ref_name, _base_cooldown, reason)
      prev = key_health[ref_name] || {}
      fail_count = (prev[:fail_count] || 0) + 1
      since = prev[:restricted_since] || Time.now

      # Backoff exponencial: 5min → 1hr → 6hr → 24hr (max)
      cooldown = [300 * (2 ** (fail_count - 1)), 86400].min

      key_health[ref_name] = {
        status: :restricted,
        available_at: Time.now + cooldown,
        reason: reason.slice(0, 60),
        restricted_since: since,
        fail_count: fail_count
      }
      save_health
      STDERR.puts "\n   🔴 [Rotator] [#{ref_name}] -> restringida: #{reason.slice(0, 60)}. Reintento en #{format_duration(cooldown)} (fallo ##{fail_count})."
    end

    def format_duration(secs)
      if secs >= 3600
        "#{(secs / 3600.0).round(1)}h"
      elsif secs >= 60
        "#{(secs / 60.0).round(0)}min"
      else
        "#{secs}s"
      end
    end

    def extract_retry_seconds(res)
      retry_after = res["retry-after"]
      begin
        err_json = JSON.parse(res.body)
        msg = err_json.dig("error", "message")
        if msg && msg.match(/(?:try again in|Please try again in)\s*([0-9\.]+)s/)
          retry_after = $1
        end
      rescue
      end
      retry_after ? retry_after.to_f.ceil : 30
    end

    def extract_error_message(res)
      begin
        err_json = JSON.parse(res.body)
        err_json.dig("error", "message") || ""
      rescue
        res.body.to_s
      end
    end
  end
end

# CLI de gestión de claves y encriptación (solo cuando se ejecuta directamente)
if __FILE__ == $0 && ARGV.length > 0
  command = ARGV[0]
  if command == "add"
    if ARGV.length < 3
      puts "Uso: #{$0} add <API_KEY> <REFERENCIA>"
      exit 1
    end
    ApiRotator.add_key(ARGV[1], ARGV[2])
  elsif command == "list"
    ApiRotator.list_keys
  elsif command == "set_hint"
    if ARGV.length < 2
      puts "Uso: #{$0} set_hint <Frase de Pista>"
      exit 1
    end
    data = ApiRotator.load_apis
    data["_hint"] = ARGV[1..-1].join(" ")
    ApiRotator.save_apis(data)
    puts "✅ Pista configurada exitosamente."
  else
    puts "Comando desconocido."
    puts "  Añadir:    #{$0} add <API_KEY> <REFERENCIA>"
    puts "  Listar:    #{$0} list"
    puts "  Pista prop:#{$0} set_hint <Frase de pista>"
  end
  exit 0
end
