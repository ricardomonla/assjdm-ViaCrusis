#!/usr/bin/env ruby
# Archivo: tools/groq_tool/compilador.rb
# Toma todos los archivos *_v3.1*.md locales y actualiza guion_completo.json,
# luego los renombra a _v4.0.md marcándolos como listos en producción.

require 'json'
require 'fileutils'

def parsear_tiempo(marca_str)
  if marca_str =~ /\[\d+\.(\d+)\.(\d+)\.(\d+)\]/
    h = $1.to_i
    m = $2.to_i
    s = $3.to_i
    return (h * 3600) + (m * 60) + s.to_f
  end
  if marca_str =~ /\[(\d+)\.(\d+)\.(\d+)\.(\d+)\]/
    h = $2.to_i
    m = $3.to_i
    s = $4.to_i
    return (h * 3600) + (m * 60) + s.to_f
  end
  nil
end

def procesar_md(path)
  content = File.read(path)
  personajes = {}
  subtitulos = []
  fase = :none
  
  content.each_line do |line|
    line.strip!
    if line.start_with?("## 1. Personajes")
      fase = :personajes
      next
    elsif line.start_with?("## 2. Subtítulos")
      fase = :subtitulos
      next
    end

    if fase == :personajes
      if line =~ /^\|\s*(P\d+)\s*\|\s*([^\|]+)\s*\|/
        personajes[$1.strip] = $2.strip
      end
    elsif fase == :subtitulos
      if line =~ /^\|\s*(\[[^\]]+\])\s*\|\s*(P\d+)\s*\|\s*(.*)\s*\|$/
        subtitulos << { "marca" => $1.strip, "idp" => $2.strip, "texto" => $3.strip }
      end
    end
  end

  resultado = []
  subtitulos.each_with_index do |sub, index|
    start_t = parsear_tiempo(sub["marca"])
    
    end_t = if index + 1 < subtitulos.length
              parsear_tiempo(subtitulos[index+1]["marca"])
            else
              start_t + 5.0
            end

    end_t = start_t + 1.0 if end_t <= start_t
    char_name = personajes[sub["idp"]] || "DESCONOCIDO"

    resultado << {
      "character" => char_name,
      "startTime" => start_t.round(2),
      "endTime"   => end_t.round(2),
      "text"      => sub["texto"]
    }
  end

  resultado
end

json_path = "../../audios/subs/guion_completo.json"
guion = JSON.parse(File.read(json_path))
archivos_md = Dir.glob("../../audios/subs/*_v3.1*.md").sort

if archivos_md.empty?
  puts "⚠️ No se encontraron archivos *_v3.1*.md listos para compilar."
  exit
end

actualizados = 0
archivos_md.each do |md_file|
  pista_match = md_file.match(/(\d+)_v3\.1.*\.md/)
  next unless pista_match

  id_pista = pista_match[1]
  puts "📦 Compilando pista #{id_pista} desde #{md_file}"
  
  bloques = procesar_md(md_file)
  
  if bloques.any?
    guion[id_pista] = bloques
    actualizados += 1
    
    # Renombrado a v4
    new_path = File.join(File.dirname(md_file), "#{id_pista}_v4.0.md")
    FileUtils.mv(md_file, new_path)
    puts "   -> Renombrado a #{File.basename(new_path)} (Paso 4 Completado)"
  else
    puts "⚠️  La pista #{id_pista} quedó vacía tras procesarse!"
  end
end

if actualizados > 0
  File.write(json_path, JSON.pretty_generate(guion))
  puts "✅ ¡Guión Completo JSON re-compilado exitosamente! (#{actualizados} pistas inyectadas y renombradas)."
else
  puts "✅ Sin cambios."
end
