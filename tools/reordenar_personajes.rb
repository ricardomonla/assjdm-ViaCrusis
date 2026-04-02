#!/usr/bin/env ruby
require 'fileutils'

dir = File.expand_path("../audios/subs", __dir__)
personajes_file = File.join(dir, "00_Personajes.md")

# 1. Leer el catálogo oficial antiguo
old_catalog = {} # { "P02" => {name: "JESÚS", synopsis: "El Mesías"}, ... }
lines = File.readlines(personajes_file)
lines.each do |line|
  if line =~ /^\|\s*(P[0-9\?]+)\s*\|\s*(.+?)\s*\|\s*(.+?)\s*\|/
    idp, name, syn = $1, $2, $3
    old_catalog[idp.strip] = { name: name.strip, synopsis: syn.strip }
  end
end

puts "✅ Catálogo original: #{old_catalog.size} personajes encontrados."

# Nombres especiales (anclados)
anclados = {
  "Música / Ambiente" => "P00",
  "NARRADOR" => "P01",
  "MUSICA SAETA" => "P99" 
}
# Mapas inversos para facilitar
name_to_old_id = old_catalog.map { |k, v| [v[:name], k] }.to_h

global_order = []

# Módulos para extraer info
files = Dir.glob(File.join(dir, "[0-9][0-9][0-9]_v*.md")).sort

files.each do |file|
  content = File.read(file)
  # Buscar la tabla de subtítulos
  in_subtitulos = false
  content.each_line do |line|
    if line.include?("MARCA | IDP | SUBTITULO")
      in_subtitulos = true
      next
    end
    if in_subtitulos
      if line =~ /^\|\s*\[[\d\.:]+\]\s*\|\s*(P[0-9\?]+)\s*\|/
        idp = $1
        name = old_catalog[idp] ? old_catalog[idp][:name] : nil
        next unless name
        # Si no es de los pinuados y no está, lo agregamos
        unless anclados.key?(name) || global_order.include?(name)
          global_order << name
        end
      end
    end
  end
end

# Agregar cualquier personaje del catálogo original que no apareció 
# para no perderlo.
old_catalog.values.each do |v|
  name = v[:name]
  unless anclados.key?(name) || global_order.include?(name)
    global_order << name
  end
end

puts "✅ Cronología analizada: #{global_order.size} voces únicas secuenciales."

# 2. Re-generar mapas de nuevos IDP
new_catalog = {} # name -> idp
counter = 2
global_order.each do |name|
  new_idp = sprintf("P%02d", counter)
  new_catalog[name] = new_idp
  counter += 1
end

# Insertar anclados
anclados.each do |name, idp|
  new_catalog[name] = idp
end

# 3. Reescribir el archivo 00_Personajes.md
File.open(personajes_file, "w") do |f|
  f.puts "# Catálogo Maestro de Personajes Cronológico"
  f.puts ""
  f.puts "## 1. Personajes"
  f.puts ""
  f.puts "| IDP | NOMBRE | SYNOPSYS |"
  f.puts "|:---|:---|:---|"
  
  # Pintar primero P00 y P01
  f.puts "| P00 | Música / Ambiente | #{old_catalog[name_to_old_id["Música / Ambiente"]][:synopsis]} |"
  f.puts "| P01 | NARRADOR | #{old_catalog[name_to_old_id["NARRADOR"]][:synopsis]} |"
  
  # Resto en orden
  global_order.each do |name|
    synopsis = old_catalog[name_to_old_id[name]][:synopsis]
    f.puts "| #{new_catalog[name]} | #{name} | #{synopsis} |"
  end
  
  # Especiales
  if name_to_old_id["MUSICA SAETA"]
    synopsis = old_catalog[name_to_old_id["MUSICA SAETA"]][:synopsis]
    f.puts "| P99 | MUSICA SAETA | #{synopsis} |"
  end
end

puts "✅ 00_Personajes.md reescrito con éxito."

# 4. Modificar internamente cada archivo de pista!
files.each do |file|
  lines = File.readlines(file)
  
  # Mapas locales antiguos -> nuevos
  old_to_new_local = {}
  
  in_personajes = false
  in_subtitulos = false
  
  new_lines = []
  
  lines.each do |line|
    if line.include?("| IDP | NOMBRE |")
      in_personajes = true
      in_subtitulos = false
      new_lines << line
      next
    end
    if line.include?("| MARCA | IDP |")
      in_subtitulos = true
      in_personajes = false
      new_lines << line
      next
    end
    
    # Dentro de la tabla de personajes
    if in_personajes && line =~ /^\|\s*(P[0-9\?]+)\s*\|\s*(.+?)\s*\|/
      old_id = $1
      name = $2.strip
      
      if new_catalog[name]
        old_to_new_local[old_id] = new_catalog[name]
        # Reemplazar la ID en esa línea
        line = line.sub(/\|\s*#{old_id}\s*\|/, "| #{new_catalog[name]} |")
      end
    end
    
    # Dentro de subtítulos
    if in_subtitulos && line =~ /^\|\s*(\[[\d\.:]+\])\s*\|\s*(P[0-9\?]+)\s*\|/
      marca = $1
      old_id = $2
      if old_to_new_local[old_id]
        line = line.sub(/\|\s*#{old_id}\s*\|/, "| #{old_to_new_local[old_id]} |")
      end
    end
    
    new_lines << line
  end
  
  # Ordenar la tabla local de personajes
  # (Opcional, pero dejaremos el archivo reescrito nomás por ahora)
  File.write(file, new_lines.join)
end

puts "✅ ¡Listo! Se actualizaron #{files.size} archivos de de la pista de subtítulos."
puts "➡️ Ejecución terminada exitosamente."
