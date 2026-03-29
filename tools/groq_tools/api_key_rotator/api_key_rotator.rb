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
require 'uri'
require 'openssl'
require 'base64'
require 'fileutils'

DIR = File.expand_path(File.dirname(__FILE__))
APIS_FILE = File.join(DIR, "apis.json")
SECRET_FILE = File.join(DIR, ".secret.key")

CANDADO_FILE = File.join(DIR, ".candado.key")

def get_passphrase
  if ENV['GROQ_ROTATOR_PASS'] && !ENV['GROQ_ROTATOR_PASS'].empty?
    return ENV['GROQ_ROTATOR_PASS']
  end

  if File.exist?(CANDADO_FILE)
    if (Time.now - File.mtime(CANDADO_FILE)) < 3600 # 1 Hora de validez
      return File.read(CANDADO_FILE).strip
    else
      File.delete(CANDADO_FILE)
      STDERR.puts "🔒 El candado expiró después de 1 hora. La clave es requerida de nuevo."
    end
  end

  hint = load_apis["_hint"] || "Sin pista configurada"
  unless STDIN.tty?
    raise "⚠️ No hay parámetro GROQ_ROTATOR_PASS, ni candado abierto, en un entorno no interactivo."
  end

  require 'io/console'
  STDERR.print "🔑 Ingresa la Frase Secreta (Pista: #{hint}): "
  pass = STDIN.noecho(&:gets).chomp
  STDERR.puts
  
  # Cerrar (Guardar) el candado por una hora
  File.write(CANDADO_FILE, pass)
  File.chmod(0600, CANDADO_FILE) rescue nil
  STDERR.puts "🔓 Candado cerrado en tu equipo. Válido por 1 hora."
  
  pass
end

def get_cipher_key
  # Deriva de una frase secreta provista por el usuario. 
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

def list_keys
  data = load_apis
  if data.empty?
    puts "No hay llaves guardadas en apis.json."
    return
  end
  puts "Llaves registradas para rotación:"
  data.each_with_index do |(ref, encrypted), i|
    begin
      desc = decrypt(encrypted)
      preview = "#{desc[0..5]}...#{desc[-4..-1]}"
      puts "#{i + 1}. [#{ref}] -> #{preview}"
    rescue
      puts "#{i + 1}. [#{ref}] -> (Error de desencriptado)"
    end
  end
end

def add_key(api_key, reference)
  data = load_apis
  data[reference] = encrypt(api_key)
  save_apis(data)
  puts "✅ Llave (Ref: '#{reference}') encriptada AES-256-CBC y guardada exitosamente en apis.json."
end

def execute_with_rotation
  apis = load_apis
  if apis.empty?
    raise "No hay llaves configuradas. Usa: ./api_key_rotator.rb add <API_KEY> <REFERENCIA>"
  end
  
  api_keys = apis.values.map { |enc| decrypt(enc) }
  api_refs = apis.keys
  
  current_idx = 0
  intentos = 0

  while intentos < api_keys.length
    key = api_keys[current_idx]
    ref_name = api_refs[current_idx]
    
    # Yield la llave y obtener la respuesta de Net::HTTP
    res = yield(key)
    
    if res.code == "200"
      return JSON.parse(res.body)
    elsif res.code == "429"
      retry_after = res["retry-after"]
      
      begin
        err_json = JSON.parse(res.body)
        msg = err_json.dig("error", "message")
        if msg && msg.match(/(?:try again in|Please try again in)\s*([0-9\.]+)s/)
          retry_after = $1
        end
      rescue
      end
      
      segundos = retry_after ? retry_after.to_f.ceil : nil
      wait_msg = segundos ? "Estará lista en ~#{segundos} seg" : "Cooldown desconocido"
      
      STDERR.puts "\n   ⚠️ [Rotator] Saturación (HTTP 429) en [#{ref_name}]. #{wait_msg}. Rotando..."
      
      if intentos >= api_keys.length - 1 && segundos
        STDERR.puts "   ⏳ ¡Todas las llaves agotadas! Pausando script #{segundos + 1} segundos para recargar..."
        sleep(segundos + 1)
        intentos = 0
        current_idx = (current_idx + 1) % api_keys.length
        next
      end

      current_idx = (current_idx + 1) % api_keys.length
      intentos += 1
      sleep 1
      next
    else
      raise "Error API (HTTP #{res.code}): #{res.body}"
    end
  end
  raise "❌ Se agotaron todas las #{api_keys.length} llaves API disponibles (Limites 429)."
end

def call_groq(system_prompt, user_prompt, model="llama-3.3-70b-versatile")
  uri = URI("https://api.groq.com/openai/v1/chat/completions")
  data = execute_with_rotation do |key|
    req = Net::HTTP::Post.new(uri)
    req["Authorization"] = "Bearer #{key}"
    req["Content-Type"] = "application/json"
    req.body = {
      model: model,
      messages: [
        { role: "system", content: system_prompt },
        { role: "user", content: user_prompt }
      ],
      temperature: 0.2
    }.to_json

    Net::HTTP.start(uri.hostname, uri.port, use_ssl: true) do |http|
      http.request(req)
    end
  end
  data.dig("choices", 0, "message", "content")
end

def call_groq_transcribe(file_path, prompt="", model="whisper-large-v3")
  uri = URI("https://api.groq.com/openai/v1/audio/transcriptions")
  
  unless File.exist?(file_path)
    raise "Archivo de audio no encontrado: #{file_path}"
  end

  data = execute_with_rotation do |key|
    req = Net::HTTP::Post.new(uri)
    req["Authorization"] = "Bearer #{key}"
    
    # Multipart form-data wrapper natively in Ruby
    form_data = [
      ['model', model],
      ['prompt', prompt],
      ['response_format', 'verbose_json'],
      ['file', File.open(file_path, 'rb'), { filename: File.basename(file_path) }]
    ]
    req.set_form(form_data, 'multipart/form-data')

    Net::HTTP.start(uri.hostname, uri.port, use_ssl: true) do |http|
      http.request(req)
    end
  end
  data
end

# Control de Comandos 
if ARGV.length > 0
  command = ARGV[0]
  if command == "add"
    if ARGV.length < 3
      puts "Uso: #{$0} add <API_KEY> <REFERENCIA>"
      exit 1
    end
    add_key(ARGV[1], ARGV[2])
  elsif command == "list"
    list_keys
  elsif command == "set_hint"
    if ARGV.length < 2
      puts "Uso: #{$0} set_hint <Frase de Pista>"
      exit 1
    end
    data = load_apis
    data["_hint"] = ARGV[1..-1].join(" ")
    save_apis(data)
    puts "✅ Pista configurada exitosamente."
  else
    puts "Comando desconocido."
    puts "  Añadir:    #{$0} add <API_KEY> <REFERENCIA>"
    puts "  Listar:    #{$0} list"
    puts "  Pista prop:#{$0} set_hint <Frase de pista>"
  end
  exit 0
end

# Si no hay argumentos, sirve de "Motor" esclavo procesando STDIN
if __FILE__ == $0 && ARGV.empty?
  begin
    input_data = JSON.parse(STDIN.read)
    respuesta = call_groq(input_data["system"], input_data["user"])
    puts JSON.generate({ "success" => true, "data" => respuesta })
  rescue => e
    puts JSON.generate({ "success" => false, "error" => e.message })
    exit 1
  end
end
