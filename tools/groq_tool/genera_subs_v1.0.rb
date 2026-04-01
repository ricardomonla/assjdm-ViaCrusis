#!/usr/bin/env ruby
# Archivo: tools/groq_tool/genera_subs_v1.0.rb
# Ingesta el archivo v0.1.md vacío y utiliza LLaMA 3.3 para deducir y rellenar
# la columna IDPERSONAJE de la tabla de subtítulos creando el archivo v1.0.md.

require_relative 'groq_client'

module GeneradorSubsV1
  def self.procesar(id_pista)
    md_v0_path = "../../audios/subs/#{id_pista}_v0.1.md"
    md_v1_path = "../../audios/subs/#{id_pista}_v1.0.md"
    
    unless File.exist?(md_v0_path)
      puts "❌ Falta el archivo borrador: #{md_v0_path}"
      return
    end

    md_content = File.read(md_v0_path)

    system_prompt = <<~PROMPT
      Eres un asistente experto de dirección teatral. Se te entregará el archivo markdown de un Guion.
      Este guion tiene dos tablas:
      1. Tabla de Personajes (IDPERSONAJE y NOMBRE)
      2. Tabla de Subtítulos (donde la celda de IDPERSONAJE está vacía).
      
      Tu objetivo es leer el SUBTITULO, deducir de qué personaje proviene basándote en un contexto bíblico y escénico de la obra "Via Crucis", y COMPLETAR las celdas vacías con el IDPERSONAJE correspondiente de la Tabla 1.
      
      DEBES DEVOLVER EXACTAMENTE EL MISMO MARKDOWN con las celdas llenas. Ni una palabra más ni una menos fuera del Markdown. No uses bloques ```markdown, simplemente devuelve el texto estructurado como Markdown válido.
    PROMPT

    user_prompt = "Aquí tienes el archivo a rellenar:\n\n#{md_content}"

    puts "🧠 Enviando #{id_pista}.md a Llama 3.3 70B (Groq) para deducción de personajes..."
    
    begin
      respuesta = GroqClient.chat(system_prompt, user_prompt)
      
      # Limpiar posible markdown wrapper de ChatGPT
      respuesta = respuesta.gsub(/^```markdown/, "").gsub(/^```/, "").gsub(/```$/, "").strip
      
      # Verificar que devolvió una tabla
      if respuesta.include?("IDPERSONAJE") && respuesta.include?("MARCA")
        File.write(md_v1_path, respuesta)
        puts "✅ Intervención IA exitosa! Borrador v1.0 generado: #{md_v1_path}"
        puts "   Listo para revisión humana (v1.1)."
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
