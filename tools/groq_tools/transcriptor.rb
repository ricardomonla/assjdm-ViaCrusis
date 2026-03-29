#!/usr/bin/env ruby
# Archivo: tools/groq_tools/transcriptor.rb
# Transcribe automáticamente un directorio de audios utilizando la API de Groq
# Dependencia: api_key_rotator.rb para cifrado y rotación de cuentas.

require_relative 'api_key_rotator/api_key_rotator'
require 'fileutils'

if ARGV.length < 2
  puts "Uso: #{$0} <directorio_entrada_mp3> <directorio_salida_txt>"
  exit 1
end

input_dir = ARGV[0]
output_dir = ARGV[1]

FileUtils.mkdir_p(output_dir)
files = Dir.glob(File.join(input_dir, "*.mp3")).sort

puts "🎙️  Iniciando Transcripción Automática de #{files.length} archivos..."

files.each do |file|
  basename = File.basename(file, ".mp3")
  out_path = File.join(output_dir, "#{basename}.txt")
  
  if File.exist?(out_path) && File.size(out_path) > 0
    puts "⏭️  Saltando #{basename} (Ya transcrito)"
    next
  end

  puts "➡️  Transcribiendo vía Groq: #{basename}"
  begin
    response = call_groq_transcribe(file, "Pasa la voz a texto respetando la puntuación, en idioma español.")
    text = response["text"]
    
    File.write(out_path, text)
    puts "✅ Guardado en #{out_path}"
  rescue => e
    puts "❌ Error transcribiendo #{basename}: #{e.message}"
  end
end

puts "🎉 Proceso de transcripción finalizado exitosamente."
