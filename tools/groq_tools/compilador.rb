#!/usr/bin/env ruby
# Archivo: tools/groq_tools/compilador.rb
# Herramienta para compilar diálogos TXT etiquetados y agrupar personajes.

require 'fileutils'

if ARGV.length < 3
  puts "Uso: #{$0} <dir_etiquetados> <guion_salida.md> <personajes_salida.md>"
  exit 1
end

input_dir = ARGV[0]
guion_file = ARGV[1]
personajes_file = ARGV[2]

files = Dir.glob(File.join(input_dir, "*.txt")).sort
personajes_apariciones = Hash.new { |h, k| h[k] = [] }

guion_content = ["# Guion Teatral Compilado\n\n"]
guion_content << "> **Nota**: Este guion fue transcrito y etiquetado automáticamente.\n\n"

count_lineas = 0

files.each do |file|
  filename = File.basename(file)
  
  if filename =~ /^(\d+)_?(.*)\.txt$/
    escena_num, escena_titulo = $1, $2
  else
    escena_num, escena_titulo = "???", filename.sub('.txt', '')
  end
  escena_titulo = escena_titulo.gsub("_", " ")

  guion_content << "## Track #{escena_num}: #{escena_titulo}\n\n"
  
  lines = File.readlines(file)
  ultimo_personaje = nil

  lines.each do |line|
    line.strip!
    next if line.empty?

    if line =~ /^\[(\d{2}:\d{2})\]\s+([^:]+):\s+(.*)$/
      tiempo, personaje, dialogo = $1, $2, $3
      personaje = personaje.strip.upcase
      
      escena_identificador = "#{escena_num} (#{escena_titulo})"
      personajes_apariciones[personaje] << escena_identificador unless personajes_apariciones[personaje].include?(escena_identificador)

      if personaje != ultimo_personaje
        guion_content << "**#{personaje}** `[#{tiempo}]`\n"
        ultimo_personaje = personaje
      end
      guion_content << "#{dialogo}\n\n"
      count_lineas += 1
    else
      guion_content << "> #{line}\n\n"
    end
  end
  guion_content << "---\n\n"
end

File.write(guion_file, guion_content.join)
puts "✅ Guion final unificado compilado: #{guion_file} (#{count_lineas} diálogos detectados)"

# Generar lista de personajes
File.open(personajes_file, "w") do |f|
  f.puts "# Reparto de Personajes de Obra Textual\n\n"
  f.puts "| Personaje | Total Apariciones | Tracks / Escenas donde aparece |"
  f.puts "|:---|:---:|:---|"
  
  personajes_apariciones.keys.sort.each do |p|
    escenas = personajes_apariciones[p].sort
    f.puts "| **#{p}** | #{escenas.size} | • #{escenas.join("<br>• ")} |"
  end
end
puts "✅ Entidades de personajes mapeadas: #{personajes_file} (#{personajes_apariciones.size} actores identificados)"
