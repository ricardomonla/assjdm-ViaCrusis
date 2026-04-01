#!/usr/bin/env ruby
# Archivo: tools/groq_tool/mapear_personajes.rb

require_relative 'groq_client'
require 'json'

module MapeadorPersonajes
  def self.procesar(id_pista)
    raw_path = "../../audios/subs/#{id_pista}_raw.json"
    master_path = "../../audios/subs/guion_completo.json"
    
    unless File.exist?(raw_path) && File.exist?(master_path)
      puts "❌ Faltan archivos JSON para procesar el mapeo."
      return
    end

    raw_data = JSON.parse(File.read(raw_path))[id_pista]
    master_data = JSON.parse(File.read(master_path))[id_pista]

    system_prompt = <<~PROMPT
      Eres un director teatral experto. Recibirás dos listas de datos del mismo audio.
      1) "TRANSCRIPCIÓN CRUDA": Segmentos de texto con tiempos exactos, pero con el personaje "???"
      2) "GUION ANTIGUO": Textos aproximados con los NOMBRES DE PERSONAJE correctos.

      Tu tarea es devolver EXCLUSIVAMENTE un JSON válido que contenga la misma TRANSCRIPCIÓN CRUDA, pero reemplazando "???" por el NOMBRE DE PERSONAJE correcto inferido del GUION ANTIGUO. 
      Nunca inventes nombres, usa los del GUION ANTIGUO.
      Salida obligatoria: Sólo el JSON crudo, sin bloques markdown como ```json. Formato: array de objetos [{startTime, endTime, character, text}].
    PROMPT

    user_prompt = "--- TRANSCRIPCIÓN CRUDA ---\n#{JSON.pretty_generate(raw_data)}\n\n--- GUION ANTIGUO ---\n#{JSON.pretty_generate(master_data)}"

    puts "🧠 Enviando a Llama 3.3 70B (Groq) para deducción de personajes..."
    
    begin
      respuesta = GroqClient.chat(system_prompt, user_prompt)
      
      # Limpiar posible markdown
      respuesta = respuesta.gsub(/```json/, "").gsub(/```/, "").strip
      
      # Validar
      mapped_array = JSON.parse(respuesta)
      
      if mapped_array.is_a?(Array) && mapped_array.first.key?("character")
        puts "✅ Mapeo exitoso validado."
        
        # Inyectar al maestro
        full_master = JSON.parse(File.read(master_path))
        full_master[id_pista] = mapped_array
        
        File.write(master_path, JSON.pretty_generate(full_master))
        puts "💾 guion_completo.json actualizado con marcas de tiempo exactas para el audio #{id_pista}!"
      else
        puts "❌ El JSON devuelto no tiene el formato esperado."
      end
    rescue => e
      puts "❌ Error mapeando: #{e.message}"
      puts "Respuesta cruda: #{respuesta[0..200]}" if defined?(respuesta)
    end
  end
end

if __FILE__ == $0 && ARGV.length > 0
  id_pista = ARGV[0]
  MapeadorPersonajes.procesar(id_pista)
end
