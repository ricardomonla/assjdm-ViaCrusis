# frozen_string_literal: true
#
# lib/model_router.rb
# Sistema de enrutamiento inteligente de tareas a modelos LLM
#

module ModelRouter
  # Catálogo central de modelos y sus configuraciones óptimas
  # Actualizado: Abril 2026 — Solo modelos de producción Groq
  # Ref: https://console.groq.com/docs/models
  TASK_PROFILES = {
    transcribe: {
      model: "whisper-large-v3",
      endpoint: "/audio/transcriptions",
      params: { language: "es", response_format: "verbose_json" },
      description: "Transcripción de audio a texto (español/inglés)"
    },
    summarize: {
      model: "llama-3.3-70b-versatile",
      endpoint: "/chat/completions",
      params: { temperature: 0.2, max_tokens: 4096 },
      description: "Resumir textos, analizar reuniones, extraer conclusiones"
    },
    creative_write: {
      model: "llama-3.1-8b-instant",
      endpoint: "/chat/completions",
      params: { temperature: 0.8, max_tokens: 2048 },
      description: "Escritura creativa, emails, contenido humano"
    },
    analyze_code: {
      model: "llama-3.3-70b-versatile",
      endpoint: "/chat/completions",
      params: { temperature: 0.1, max_tokens: 4096 },
      description: "Análisis de código, debugging, explicaciones técnicas"
    },
    long_context: {
      model: "llama-3.3-70b-versatile",
      endpoint: "/chat/completions",
      params: { temperature: 0.5, max_tokens: 8192 },
      description: "Documentos largos, contexto extendido"
    },
    technical_explain: {
      model: "llama-3.1-8b-instant",
      endpoint: "/chat/completions",
      params: { temperature: 0.3, max_tokens: 2048 },
      description: "Explicaciones técnicas, instrucciones paso a paso"
    },
    quick_chat: {
      model: "llama-3.1-8b-instant",
      endpoint: "/chat/completions",
      params: { temperature: 0.5, max_tokens: 1024 },
      description: "Chat rápido, preguntas simples, bajo costo"
    },
    improve_doc: {
      model: "llama-3.3-70b-versatile",
      endpoint: "/chat/completions",
      params: { temperature: 0.3, max_tokens: 8192 },
      description: "Mejorar/reescribir documentos, planes, READMEs completos"
    }
  }.freeze

  # Patrones de palabras clave para detección automática de tarea
  TASK_PATTERNS = {
    transcribe: [
      /transcrib/i, /audio/i, /mp3/i, /whisper/i, /voz/i, /habla/i, /reunión/i
    ],
    summarize: [
      /resum/i, /analiz/i, /síntesis/i, /conclusión/i, /extracto/i, /puntos clave/i
    ],
    creative_write: [
      /escrib/i, /creativ/i, /poem/i, /email/i, /carta/i, /story/i, /narrativ/i
    ],
    analyze_code: [
      /código/i, /función/i, /clase/i, /debug/i, /bug/i, /refactor/i, /script/i, /\.rb/i, /\.py/i, /\.js/i
    ],
    long_context: [
      /documento.*largo/i, /contexto.*extenso/i, />8k/i, /32k/i, /texto.*extenso/i
    ],
    technical_explain: [
      /explicá/i, /cómo hacer/i, /tutorial/i, /guía/i, /paso a paso/i, /instrucc/i
    ],
    improve_doc: [
      /mejor/i, /refactor/i, /reescrib/i, /optimiz/i, /pulir/i, /improve/i
    ]
  }.freeze

  class << self
    # Sugiere tipo de tarea basado en descripción del usuario
    def suggest_task(description)
      return :summarize if description.nil? || description.strip.empty?

      # Contar coincidencias por tipo de tarea
      scores = Hash.new(0)

      TASK_PATTERNS.each do |task_type, patterns|
        patterns.each do |pattern|
          scores[task_type] += 1 if description.match?(pattern)
        end
      end

      # Retornar tarea con más coincidencias, o default
      scores.max_by { |_, v| v }&.first || :summarize
    end

    # Obtiene perfil completo para una tarea
    def get_profile(task_type)
      TASK_PROFILES[task_type] || TASK_PROFILES[:summarize]
    end

    # Lista todos los modelos únicos disponibles
    def list_unique_models
      TASK_PROFILES.values.map { |p| p[:model] }.uniq
    end

    # Dada una descripción, retorna el modelo óptimo directo
    def suggest_model(description)
      task_type = suggest_task(description)
      TASK_PROFILES[task_type][:model]
    end
  end
end
