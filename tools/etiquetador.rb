#!/usr/bin/env ruby
# Archivo: tools/groq_tools/etiquetador.rb
# Formatea y etiqueta personajes usando Llama 3 via Groq

require_relative 'groq_tool/groq_client'
require 'fileutils'

if ARGV.length < 2
  puts "Uso: #{$0} <directorio_textos_crudos> <directorio_etiquetados_salida>"
  exit 1
end

input_dir = ARGV[0]
output_dir = ARGV[1]

FileUtils.mkdir_p(output_dir)
files = Dir.glob(File.join(input_dir, "*.txt")).sort

SYSTEM_PROMPT = <<~PROMPT
  Quiero que analices el texto proporcionado y lo transformes en un guion teatral.
  Identifica los personajes, sus intervenciones, y agrega "[00:00]" como tiempo de ejemplo,
  FORMATO OBLIGATORIO DE SALIDA LINEA POR LINEA (SIN INTRODUCCIONES NI DESPEDIDAS NI TEXTO EXTRA):
  [00:00] PERSONAJE: Diálogo...
PROMPT

puts "🧠 Iniciando etiquetado Llama3 Vía Crucis en #{files.length} archivos..."

files.each do |file|
  basename = File.basename(file, ".txt")
  out_path = File.join(output_dir, "#{basename}.txt")
  
  if File.exist?(out_path) && File.size(out_path) > 0
    puts "⏭️  Saltando #{basename} (Ya preparado)"
    next
  end

  puts "➡️  Analizando Semántica Libre: #{basename}"
  begin
    text = File.read(file)
    user_prompt = "TEXTO A ANALIZAR:\n#{text}"
    
    result = GroqClient.chat(SYSTEM_PROMPT, user_prompt)
    
    File.write(out_path, result.strip)
    puts "✅ Limpiado y Formateado en #{out_path}"
  rescue => e
    puts "❌ Error etiquetando #{basename}: #{e.message}"
  end
end

puts "🎉 Etiquetado Finalizado."
