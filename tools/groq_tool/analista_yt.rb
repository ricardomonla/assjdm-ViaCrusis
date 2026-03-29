#!/usr/bin/env ruby
# Archivo: tools/groq_tool/analista_yt.rb
# Creado para orquestar la descarga, desgrabación y análisis inteligente.

require_relative 'groq_client'
require_relative 'descargar_yt_a_mp3'
require_relative 'desgrabar_mp3'
require 'fileutils'
require 'time'

cronometro_inicio = Time.now

if ARGV.length < 1
  puts "Uso: #{$0} <URL_DE_YOUTUBE>"
  exit 1
end

url = ARGV[0]

# Generar un ID único basado en el video
video_id = url.include?("v=") ? url.split("v=").last.split("&").first : "video_#{rand(1000)}"
video_id = url.split("/").last.split("?").first if url.include?("youtu.be/")

# Usamos directorio caché local dentro de la tool
base_dir = File.join(File.dirname(__FILE__), "tmp", "reuniones_cache", video_id)
FileUtils.mkdir_p(base_dir)

puts "🚀  [MÓDULO 1] CONSUMIENDO SUB-HERRAMIENTA: DescargarYTaMP3..."
begin
  audio_path = DescargarYTaMP3.obtener_audio(url, base_dir)
rescue => e
  puts "❌ Error crítico fallando el eslabón de descarga: #{e.message}"
  exit 1
end

puts "\n🚀  [MÓDULO 2] CONSUMIENDO SUB-HERRAMIENTA: DesgrabarMP3..."
begin
  texto_bruto = DesgrabarMP3.procesar(audio_path, base_dir, video_id)
rescue => e
  puts "❌ Error crítico fallando la sub-rutina de IA de desgrabación: #{e.message}"
  exit 1
end

puts "\n🚀  [MÓDULO 3] ANALISTA RESUMIDOR LLaMa 3..."
output_final = File.join(base_dir, "Reporte_Reunion_Ejecutivo_#{video_id}.md")
mapeo_file = File.join(base_dir, "mapeo_voces_#{video_id}.md")

if File.exist?(output_final) && File.size(output_final) > 0
  puts "4️⃣  [CACHÉ] El reporte LLaMa ya estaba construido. Extrayendo del caché..."
  resumen = File.read(output_final)
elsif File.exist?(mapeo_file)
  puts "🔍 [Fase Final] Archivo de mapeo de voces encontrado."
  puts "🚀 Pensando conclusiones maestras a partir del texto y tus correcciones..."
  
  contenido_humano = File.read(mapeo_file).strip
  
  mapeo_inyectado = if contenido_humano.empty? || contenido_humano.include?("Edita las líneas de abajo")
                      "" 
                    else 
                      "\\n⚠️ REGLA ESTRICTA DE INTELIGENCIA HUMANA: Bautiza a los interlocutores usando esta regla exacta leída del archivo de mapeo:\\n#{contenido_humano}"
                    end
                    
  system_prompt = <<~PROMPT
    Eres el mejor secretario de actas corporativas y analista de proyectos.
    Recibiste la desgrabación cruda. Tu esquema definitivo es:
    # Análisis Inteligente de la Reunión

    ## 👥 Participantes:
    [Viñetas deducidas con sus nombres/roles]

    ## 📝 Resumen Ejecutivo:
    [De qué trató, problemas surgidos, decisiones técnicas discutidas. 3 a 5 párrafos concisos y directos.]

    ## 🎯 Acuerdos Clave:
    [Viñetas tipo bullet point]
    #{mapeo_inyectado}
  PROMPT

  # Límite anti-413 (TPM excedido en capa gratuita). 35000 chars = ~9000 tokens
  texto_a_resumir = texto_bruto.length > 35000 ? texto_bruto[0..35000] + "\\n\\n[...TEXTO TRUNCADO POR LÍMITES API...]" : texto_bruto

  begin
    resumen = GroqClient.chat(system_prompt, texto_a_resumir, "llama-3.3-70b-versatile")
    File.write(output_final, resumen)
    puts "✅ Reporte ejecutivo consolidado y guardado en #{output_final}."
    
    puts "\\n🚀 [Fase 3] Reconstruyendo Diálogo Completo con Nombres Reales (Llama 3.1 8b)..."
    File.open(output_final, 'a') { |f| f.puts("\\n\\n---\\n\\n# 📜 Transcripción Completa del Diálogo\\n") }
    
    chunks_dialogo = texto_bruto.chars.each_slice(12000).map(&:join)
    chunks_dialogo.each_with_index do |fragmento, idx|
      puts "   ➡️  Etiquetando fragmento de diálogo #{idx + 1}/#{chunks_dialogo.size}..."
      
      prompt_dialogo = <<~PROMPT
        Convierte este fragmento de transcripción bruta en un guion teatral.
        Identifica a los hablantes deduciéndolos desde el siguiente mapeo estricto del humano:
        #{contenido_humano}
        
        REGLAS MATEMÁTICAS DE ETIQUETADO (NO ROMPER): 
        1. Cada intervención debe iniciar estrictamente con "ID | NOMBRE: ".
        2. Ejemplo: si usas a L2 de la lista, debes escribir literalmente "L2 | César BAIGORRI: [Texto del diálogo]".
        3. Respeta las MAYÚSCULAS y la forma exacta en que el humano escribió los nombres en su mapeo.
        4. Si detectas una voz nueva o no estás muy seguro, pon "L? | Voz Desconocida: [Texto]".
        5. Devuelve ÚNICAMENTE el diálogo de la reunión transformado, sin comentarios extras.
      PROMPT
      
      begin
        # Llama 3.1 8b maneja el trabajo de trinchera mucho mejor bajo límites TPM gratuitos
        parseado = GroqClient.chat(prompt_dialogo, fragmento, "llama-3.1-8b-instant")
        File.open(output_final, 'a') { |f| f.puts("\\n" + parseado + "\\n") }
        sleep(2) # Respiro térmico TPM
      rescue => e
        puts "   ⚠️  Error en fragmento #{idx + 1}: #{e.message}. Inyectando crudo."
        File.open(output_final, 'a') { |f| f.puts("\\n[FRAGMENTO CRUDO API LIMIT]\\n" + fragmento + "\\n") }
      end
    end
    
    puts "✅ Documento definitivo sellado con Resumen Ejecutivo + Diálogos Etiquetados."
  rescue => e
    puts "❌ Hubo un error procesando el resumen LLaMA: #{e.message}"
  end
else
  puts "🔍 [Fase 1] Detección Inteligente de Voces y Roles (LLaMa 3)..."
  muestra = texto_bruto[0..8000] # Primeros caracteres bastan
    begin
      prompt_voces = "Lee este inicio de reunión y enlista los interlocutores encontrados.\n" \
                     "Debes asignar un ID numérico estricto a cada uno (L1, L2, L3...).\n" \
                     "Devuelve SOLO una lista con este exacto formato por línea:\n" \
                     "L1 | Locutor 1 : [Breve descripción / Referencia]"
      roles_detectados = GroqClient.chat(prompt_voces, muestra, "llama-3.3-70b-versatile")
      
      plantilla = "# 🧠 [INTELIGENCIA NATURAL REQUERIDA]\n" +
                  "# Edita las líneas de abajo para asignar nombres reales usando la estructura estricta:\n" +
                  "# ID | NOMBRE : Referencia\n" +
                  "# Ej: L1 | Ricardo : Líder de la reunión\n\n" +
                  roles_detectados + "\n"
                
    File.write(mapeo_file, plantilla)
    
    puts "\n⏸️  [PAUSA ASINCRÓNICA]"
    puts "🤖 LLaMa detectó los participantes y generó un archivo para que lo corrijas a tu ritmo."
    puts "👉 Abre este archivo en tu editor: #{mapeo_file}"
    puts "👉 Cuando termines, vuelve a correr EXACTAMENTE el mismo comando para la Fase Final."
    exit 0
  rescue => e
    puts "⚠️ Falló la pre-detección: #{e.message}. Creando archivo en blanco."
    File.write(mapeo_file, "Ocurrió un error detectando voces. Continúa para usar modo genérico.")
    exit 0
  end
end

puts "\n🎉 ¡ORQUESTADOR MODULAR CERRADO CON BROCHE DE ORO!"
puts "📝 Lee el documento maestro en este archivo definitivo: \n👉 #{output_final}\n"

puts "\n==== 📊 MÉTRICAS DEL MOTOR GROQ ===="
puts "   Llamadas API Dinámicas : #{GroqClient.api_calls} peticiones"
puts "   Audio Recién Procesado : #{(GroqClient.total_audio_seconds / 60.0).round(2)} Minutos"
puts "   Tokens LLM Impactados  : #{GroqClient.total_tokens} tokens"
puts "   Cronómetro Total       : #{((Time.now - cronometro_inicio) / 60.0).round(2)} Minutos de vida real"
puts "===================================\n"
