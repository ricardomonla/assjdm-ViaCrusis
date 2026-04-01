#!/usr/bin/env ruby
# Archivo: tools/groq_tool/transcribir_karaoke.rb

require_relative 'groq_client'
require 'fileutils'
require 'json'

module TranscribirKaraoke
  def self.procesar(audio_path, output_json, id_pista)
    unless File.exist?(audio_path)
      puts "❌ Archivo no encontrado: #{audio_path}"
      return
    end

    puts "🎙️  Enviando a Groq Whisper (Límite 25MB)..."
    
    # Contexto para Groq
    prompt_contexto = "Obra de teatro Vía Crucis. Diálogos actorales, religión, pasión de cristo."
    
    begin
      data = GroqClient.transcribe(audio_path, prompt_contexto)
    rescue => e
      puts "❌ Fallo en IA: #{e.message}"
      return
    end

    if data["segments"].nil? || data["segments"].empty?
      puts "❌ No se encontraron segmentos de tiempo en la respuesta."
      return
    end

    # Transformar a formato inicial (sin personaje todavía)
    karaoke_data = data["segments"].map do |segment|
      {
        "startTime" => segment["start"].round(2),
        "endTime" => segment["end"].round(2),
        "character" => "???",
        "text" => segment["text"].strip
      }
    end

    wrapper = {
      id_pista => karaoke_data
    }

    File.write(output_json, JSON.pretty_generate(wrapper))
    puts "✅ Segementos extraídos con éxito! Guardado en: #{output_json}"
    puts "   Total de bloques de diálogo: #{karaoke_data.size}"
  end
end

if __FILE__ == $0 && ARGV.length > 0
  audio_path = ARGV[0]
  id_pista = ARGV[1] || File.basename(audio_path).split('_')[0]
  output_json = ARGV[2] || "../../audios/subs/#{id_pista}_raw.json"
  
  TranscribirKaraoke.procesar(audio_path, output_json, id_pista)
end
