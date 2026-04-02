#!/usr/bin/env ruby
# Archivo: tools/groq_tool/genera_subs_v1.0.rb
# Ingesta el catálogo global 00_Personajes.md y el audio MP3.
# Llama a Whisper (para transcripción/tiempos) y luego a LLaMA 3.3
# para combinar y deducir los personajes, generando el borrador v1.0.md.

require_relative 'groq_client'
require 'json'
$stdout.sync = true

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

    md_personajes_path = "../../audios/subs/00_Personajes.md"
    md_v1_path = "../../audios/subs/#{id_pista}_v1.0.md"
    
    unless File.exist?(md_personajes_path)
      puts "❌ Falta el catálogo global: #{md_personajes_path}"
      return
    end

    md_content = File.read(md_personajes_path)

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

    if raw_segments.first["start"] > 1.0
      whisper_texto_array.unshift("MARCA: [#{id_pista}.00.00.00] | TEXTO: (Música de fondo / Ambiente silencioso)")
    end

    whisper_texto = whisper_texto_array.join("\n")

    puts "🧠 2. Pasando Transcripción y MD a LLaMA 3.3 (Groq) para deducción..."
    
    system_prompt = <<~PROMPT
      Eres un asistente experto de dirección teatral. Se te entregarán dos elementos:
      1. Un CATÁLOGO GLOBAL DE PERSONAJES (`00_Personajes.md`) con todos los personajes de la obra.
      2. Una TRANSCRIPCIÓN CON MARCAS EXACTAS DE TIEMPO extraídas de un audio específico por Whisper.
      
      Tu objetivo es generar un archivo Markdown `v1.0.md` para esta pista con el siguiente formato exacto:
      
      # Audio #{id_pista}
      
      ## 1. Personajes
      
      (Aquí debes armar una tabla de Personajes IDÉNTICA en estructura al catálogo, PERO INCLUYENDO EXCLUSIVAMENTE a los personajes que intervienen en los subtítulos de esta pista, más la infaltable fila de 'P00 Música / Ambiente').
      
      ## 2. Subtítulos
      
      (Aquí la tabla de subtítulos)
      
      REGLAS ESTRICTAS PARA LA TABLA 2:
      - Para cada línea de la TRANSCRIPCION, debes colocar:
        - MARCA: El tag de tiempo exacto provisto en la entrada (ej. `[#{id_pista}.00.00.00]`).
        - IDP: El ID de personaje (P01, P02, etc.) deducido leyendo el contexto de transcripción y cruzándolo con la Tabla 1. (Usa `P00` si el texto indica Música o Efectos).
        - SUBTITULO: El texto transcrito sin alterar.
      - DEBES devolver exactamente el archivo Markdown completo con ambas tablas.
      - DEBES añadir TODAS las líneas transcritas a la Tabla 2. Ninguna puede quedar afuera.
      - Solo devuelve el crudo markdown formateado. No uses el bloque delimitador de lenguaje ```markdown.
    PROMPT

    user_prompt = "--- CATÁLOGO GLOBAL DE PERSONAJES ---\n#{md_content}\n\n--- TRANSCRIPCIÓN CON MARCAS EXACTAS ---\n#{whisper_texto}"

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
