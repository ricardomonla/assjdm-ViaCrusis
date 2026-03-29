#!/usr/bin/env ruby
# Archivo: tools/groq_tool/desgrabar_mp3.rb

require_relative 'groq_client'
require 'fileutils'

module DesgrabarMP3
  def self.procesar(audio_path, output_dir, file_id = "default")
    fusion_txt = File.join(output_dir, "transcripcion_fusionada_#{file_id}.txt")
    
    if File.exist?(fusion_txt) && File.size(fusion_txt) > 0
      puts "2️⃣  [CACHÉ] Texto completo de '#{file_id}' ya desgrabado. Saltando Groq."
      return File.read(fusion_txt)
    end

    texto_completo = ""
    tamano_bytes = File.size(audio_path)
    
    # Límite Groq es 25MB, jugamos seguro con 24MB (24_000_000)
    limite_seguro_bytes = 24_000_000 
    
    if tamano_bytes <= limite_seguro_bytes
      puts "2️⃣  Audio de #{(tamano_bytes / 1024.0 / 1024.0).round(2)} MB pasa directo sin fraccionar."
      puts "3️⃣  Enviando a IA Transcripción (Groq Whisper)..."
      begin
        response = GroqClient.transcribe(audio_path, "Reunión institucional o de desarrollo. Temas IT.")
        texto_completo = response["text"].to_s
      rescue => e
        raise "❌ Falla directa en Groq: #{e.message}"
      end
    else
      puts "2️⃣  El audio es grande (#{(tamano_bytes / 1024.0 / 1024.0).round(2)} MB). Fragmentando (límite 25MB)..."
      chunk_pattern = File.join(output_dir, "chunk_#{file_id}_%03d.mp3")
      Dir.glob(File.join(output_dir, "chunk_#{file_id}_*.mp3")).each { |f| File.delete(f) }
      
      system("ffmpeg -i '#{audio_path}' -f segment -segment_time 900 -c:a libmp3lame -q:a 9 -ar 16000 '#{chunk_pattern}' -loglevel error -y")
      
      chunks = Dir.glob(File.join(output_dir, "chunk_#{file_id}_*.mp3")).sort
      raise "Error picando el archivo de audio. FFMPEG falló." if chunks.empty?
      
      puts "   ✂️ ¡El gigante fue rebanado con éxito en #{chunks.size} porciones!"
      puts "3️⃣  Enviando fragmentos a IA Transcripción..."

      chunks.each_with_index do |chunk, i|
        puts "   ➡️  Desgrabando bloque #{i+1} de #{chunks.size}..."
        begin
          response = GroqClient.transcribe(chunk, "Reunión institucional o de desarrollo. Temas IT.")
          texto_completo += response["text"].to_s + "\n\n"
        rescue => e
          puts "   ⚠️ Advertencia en fragmento #{i+1}: El motor no pudo procesarlo: #{e.message}"
        end
      end
      
      chunks.each { |f| File.delete(f) } # Limpieza térmica
    end

    if texto_completo.strip.empty?
      raise "❌ Fallo rotundo de Groq. El texto está vacío tras intentar todo."
    end

    File.write(fusion_txt, texto_completo)
    puts "✅ Texto extraído y zurcido (#{texto_completo.length} bytes)."
    
    return texto_completo
  end
end

if __FILE__ == $0 && ARGV.length > 0
  input_path = ARGV[0]
  output_dir = ARGV[1] || File.join(File.dirname(__FILE__), "tmp", "audios_yt")

  if File.directory?(input_path)
    puts "🎙️  Modo Lote Activado: Transcribiendo audios MP3 desde #{input_path}..."
    FileUtils.mkdir_p(output_dir)
    files = Dir.glob(File.join(input_path, "*.mp3")).sort
    
    files.each do |file|
      basename = File.basename(file, ".mp3")
      puts "\n=== 🎧 Procesando: #{basename} ==="
      begin
        DesgrabarMP3.procesar(file, output_dir, basename)
      rescue => e
        puts "❌ Error transcribiendo #{basename}: #{e.message}"
      end
    end
    puts "\n🎉 Proceso de transcripción en lote finalizado. Directorio completo."
  elsif File.exist?(input_path)
    # Es un archivo único
    puts DesgrabarMP3.procesar(input_path, output_dir, File.basename(input_path, ".mp3"))
  else
    puts "❌ Archivo o directorio no encontrado: #{input_path}"
  end
end
