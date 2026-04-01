#!/usr/bin/env ruby
# Archivo: tools/groq_tool/genera_subs_v0.1.rb
# Transcribe el audio vía Whisper y genera *directamente* la plantilla Markdown .v0.1
# para el flujo HITL (Human-In-The-Loop) omitiendo JSONs crudos intermedios.

require_relative 'groq_client'
require 'fileutils'
require 'json'

module GeneradorSubsV0
  def self.procesar(audio_path, id_pista)
    master_path = "../../audios/subs/guion_completo.json"
    output_path = "../../audios/subs/#{id_pista}_v0.1.md"
    
    unless File.exist?(audio_path)
      puts "❌ Archivo de audio no encontrado: #{audio_path}"
      return
    end

    unless File.exist?(master_path)
      puts "❌ Falta el archivo maestro de guiones: #{master_path}"
      return
    end

    master_data = begin
      JSON.parse(File.read(master_path))[id_pista] || []
    rescue
      []
    end

    # 1. Enviar audio a Groq Whisper
    puts "🎙️  Enviando #{id_pista} a Groq Whisper (Límite 25MB)..."
    prompt_contexto = "Obra de teatro Vía Crucis. Diálogos actorales, religión, pasión de cristo."
    
    begin
      data = GroqClient.transcribe(audio_path, prompt_contexto)
    rescue => e
      puts "❌ Fallo en IA (Whisper): #{e.message}"
      return
    end

    if data["segments"].nil? || data["segments"].empty?
      puts "❌ No se encontraron segmentos de tiempo en la respuesta."
      return
    end

    raw_data = data["segments"]

    # 2. Determinar personajes únicos del guion antiguo
    personajes_unicos = master_data.map { |c| c["character"] }.uniq.reject { |c| c.nil? || c.empty? }
    
    # 3. Construir la cabecera Markdown
    md = "# Audio #{id_pista}\n\n"
    md += "## 1. Personajes\n\n"
    md += "| IDPERSONAJE | NOMBRE | SYNOPSYS |\n"
    md += "|:---|:---|:---|\n"
    
    personaje_map = {}
    personajes_unicos.each_with_index do |nombre, idx|
      pid = format("P%02d", idx + 1)
      personaje_map[nombre] = pid
      md += "| #{pid} | #{nombre} |  |\n"
    end
    
    if personajes_unicos.empty?
      md += "| P01 |  |  |\n"
    end

    md += "\n## 2. Subtítulos\n\n"
    md += "| MARCA | IDPERSONAJE | SUBTITULO |\n"
    md += "|:---|:---|:---|\n"

    # 4. Insertar las marcas crudas transcritas
    raw_data.each do |row|
      total_seconds = row["start"].round(2)
      h = (total_seconds / 3600).floor
      m = ((total_seconds / 60) % 60).floor
      s = (total_seconds % 60).floor
      marca_formateada = format("[%s.%02d.%02d.%02d]", id_pista, h, m, s)
      
      texto = row["text"].gsub("|", " ") # Evitar romper la tabla
      md += "| #{marca_formateada} |  | #{texto} |\n"
    end

    File.write(output_path, md)
    puts "✅ Markdown de validación humana creado con éxito directamente desde el audio: #{output_path}"
    puts "   Total de bloques transcritos: #{raw_data.size}"
  end
end

if __FILE__ == $0 && ARGV.length > 0
  audio_path = ARGV[0]
  id_pista = ARGV[1] || File.basename(audio_path).split('_')[0]
  GeneradorSubsV0.procesar(audio_path, id_pista)
end
