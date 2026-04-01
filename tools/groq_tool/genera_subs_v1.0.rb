#!/usr/bin/env ruby
# Archivo: tools/groq_tool/genera_subs_v1.0.rb
# Ingesta el archivo v0.1.md (hecho por AI inicial) y el audio MP3.
# Llama a Whisper (para transcripción/tiempos) y luego a LLaMA 3.3
# para combinar y deducir los personajes, generando el borrador v1.0.md.

require_relative 'groq_client'
require 'json'

module GeneradorSubsV1
  def self.procesar(id_pista)
    audio_path = "../../audios/media/#{id_pista}.mp3"
    # Si no existe con ese nombre simple, buscar el más largo (ej. 101_v2503.mp3)
    unless File.exist?(audio_path)
      matches = Dir.glob("../../audios/media/#{id_pista}_*.mp3")
      if matches.any?
        audio_path = matches.first
      else
        puts "❌ Faltan el archivo mp3 para la pista #{id_pista}"
        return
      end
    end

    md_v0_path = "../../audios/subs/#{id_pista}_v0.1.md"
    md_v1_path = "../../audios/subs/#{id_pista}_v1.0.md"
    
    unless File.exist?(md_v0_path)
      puts "❌ Falta el archivo borrador inicial: #{md_v0_path}"
      return
    end

    md_content = File.read(md_v0_path)

    puts "🎙️  1. Transcribiendo #{audio_path} con Groq Whisper (Límite 25MB)..."
    begin
      data_audio = GroqClient.transcribe(audio_path, "Vía Crucis, diálogos, teatro")
      raw_segments = data_audio["segments"]
      
      if raw_segments.nil? || raw_segments.empty?
        puts "❌ No se obtuvieron segmentos de Whisper."
        return
      end
    rescue => e
      puts "❌ Error con Whisper: #{e.message}"
      return
    end

    whisper_texto_array = raw_segments.map do |row|
      total_sec = row["start"].round(2)
      h = (total_sec / 3600).floor
      m = ((total_sec / 60) % 60).floor
      s = (total_sec % 60).floor
      marca = format("[%s.%02d.%02d.%02d]", id_pista, h, m, s)
      "MARCA: #{marca} | TEXTO: #{row["text"].strip}"
    end

    whisper_texto = whisper_texto_array.join("\n")

    puts "🧠 2. Pasando Transcripción y MD a LLaMA 3.3 (Groq) para deducción..."
    
    system_prompt = <<~PROMPT
      Eres un asistente experto de dirección teatral. Se te entregarán dos elementos:
      1. Un ARCHIVO MARKDOWN inicial (`v0.1.md`) con una Tabla de Personajes y una Tabla de Subtítulos que puede tener la primera fila pre-cargada.
      2. Una TRANSCRIPCIÓN CON MARCAS EXACTAS DE TIEMPO extraídas del audio real por Whisper.
      
      Tu objetivo es reescribir EXCLUSIVAMENTE el ARCHIVO MARKDOWN completo completando la Tabla 2 (Subtítulos).
      
      REGLAS ESTRICTAS PARA LA TABLA 2:
      - Si la tabla de Subtítulos del archivo original arranca con una fila inicial de P00 pre-cargada por el humano, DEBES MANTENERLA como la fila número 1.
      - Para cada línea de la TRANSCRIPCION, debes colocar:
        - MARCA: El tag de tiempo exacto provisto en la entrada (ej. `[101.00.00.00]`).
        - IDP: El ID de personaje (P01, P02, etc.) deducido leyendo el contexto de transcripción y cruzándolo con la Tabla 1. (Usa `P00` si el texto indica Música o Efectos).
        - SUBTITULO: El texto transcrito sin alterar.
      - DEBES devolver exactamente el archivo Markdown completo con las dos tablas.
      - DEBES añadir TODAS las líneas transcritas a la Tabla 2. Ninguna puede quedar afuera.
      - Solo devuelve el crudo markdown formateado. No uses el bloque delimitador de lenguaje ```markdown.
    PROMPT

    user_prompt = "--- ARCHIVO MARKDOWN (`v0.1.md`) ---\n#{md_content}\n\n--- TRANSCRIPCIÓN CON MARCAS EXACTAS ---\n#{whisper_texto}"

    begin
      respuesta = GroqClient.chat(system_prompt, user_prompt)
      
      # Limpiar posible markdown wrapper de ChatGPT
      respuesta = respuesta.gsub(/^```markdown/, "").gsub(/^```/, "").gsub(/```$/, "").strip
      
      # Verificar que devolvió una tabla
      if respuesta.include?("IDP") && respuesta.include?("MARCA")
        File.write(md_v1_path, respuesta)
        puts "✅ Intervención IA exitosa! Borrador v1.0 generado de forma nativa: #{md_v1_path}"
        puts "   ¡Listo para que el director haga la revisión humana (v1.1)!"
      else
        puts "❌ El LLM no devolvió el formato esperado."
      end
    rescue => e
      puts "❌ Error mapeando con LLM: #{e.message}"
    end
  end
end

if __FILE__ == $0 && ARGV.length > 0
  id_pista = ARGV[0]
  GeneradorSubsV1.procesar(id_pista)
end
