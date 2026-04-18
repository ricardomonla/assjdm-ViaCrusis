# Plan: Orquestador Inteligente de Modelos LLM con Groq API

## Visión

Transformar `groq_tool` de una colección de scripts especializados a un **sistema unificado de enrutamiento de tareas a modelos LLM**, donde el usuario pide "lo que necesita" y el sistema elige automáticamente:
- **Qué modelo** usar (Llama, Mistral, Gemma, etc.)
- **Qué endpoint** consumir (chat, transcripción, visión)
- **Qué configuración** aplicar (temperatura, tokens máx, etc.)

---

## Principio Central: "Task-First API"

```bash
# En lugar de:
./analista_yt.rb <URL>           # Script hardcoded a Llama-3.3-70b
./genera_subs_v1.0.rb <ID>       # Script hardcoded a Llama-3.3-70b + Whisper

# El usuario pide:
groq ask "Resume este video: <URL>"
groq transcribe "reunion.mp3"
groq write "Escribe un email formal sobre X"
groq code "Explica qué hace este función"
```

El sistema decide internamente:
```ruby
case task_type
when :transcription
  { model: "whisper-large-v3", endpoint: "/audio/transcriptions" }
when :summarization
  { model: "llama-3.3-70b-versatile", temperature: 0.2 }
when :creative_writing
  { model: "llama-3.1-8b-instant", temperature: 0.8 }
when :code_analysis
  { model: "codellama-34b-instruct", temperature: 0.1 }
end
```

---

## Catálogo de Modelos Groq API (2026)

### Modelos de Texto (Chat Completions)

| Modelo | Uso Óptimo | Temperatura Sugerida | Tokens Máx | Costo Relativo |
|--------|------------|---------------------|------------|----------------|
| `llama-3.3-70b-versatile` | Razonamiento complejo, análisis, resumen | 0.2-0.5 | 8192 | Alto |
| `llama-3.1-8b-instant` | Tareas rápidas, escritura creativa | 0.7-0.9 | 8192 | Bajo |
| `mixtral-8x7b-32768` | Contexto largo (32K), multitarea | 0.5-0.7 | 32768 | Medio |
| `gemma2-9b-it` | Instrucciones técnicas, código | 0.1-0.3 | 8192 | Bajo |
| `codellama-34b-instruct` | Código, debugging, explicaciones técnicas | 0.1-0.2 | 8192 | Medio |

### Modelos de Audio

| Modelo | Uso Óptimo | Límite | Notas |
|--------|------------|--------|-------|
| `whisper-large-v3` | Transcripción ES/EN, multi-idioma | 25 MB | Requiere endpoint `/audio/transcriptions` |
| `distil-whisper-large-v3-en` | Solo inglés, más rápido | 25 MB | Menor latencia |

### Matriz de Decisión por Tipo de Tarea

```
┌─────────────────────────────────────────────────────────────────┐
│  ¿QUÉ NECESITAS?           →  MODELO RECOMENDADO               │
├─────────────────────────────────────────────────────────────────┤
│  Transcribir audio         →  whisper-large-v3                 │
│  Resumir texto/reunión     →  llama-3.3-70b-versatile          │
│  Escribir creativo          →  llama-3.1-8b-instant (temp 0.8)  │
│  Analizar código           →  codellama-34b-instruct           │
│  Contexto >8K tokens       →  mixtral-8x7b-32768               │
│  Instrucciones técnicas    →  gemma2-9b-it                     │
│  Razonamiento lógico       →  llama-3.3-70b-versatile          │
│  Chat rápido/low-cost      →  llama-3.1-8b-instant             │
└─────────────────────────────────────────────────────────────────┘
```

---

## Arquitectura Propuesta

### 1. Router de Modelos (`lib/model_router.rb`)

```ruby
module GroqTool
  class ModelRouter
    TASK_PROFILES = {
      transcribe: {
        model: "whisper-large-v3",
        endpoint: "/audio/transcriptions",
        params: { language: "es", response_format: "verbose_json" }
      },
      summarize: {
        model: "llama-3.3-70b-versatile",
        endpoint: "/chat/completions",
        params: { temperature: 0.2, max_tokens: 4096 }
      },
      creative_write: {
        model: "llama-3.1-8b-instant",
        endpoint: "/chat/completions",
        params: { temperature: 0.8, max_tokens: 2048 }
      },
      analyze_code: {
        model: "codellama-34b-instruct",
        endpoint: "/chat/completions",
        params: { temperature: 0.1, max_tokens: 4096 }
      },
      long_context: {
        model: "mixtral-8x7b-32768",
        endpoint: "/chat/completions",
        params: { temperature: 0.5 }
      }
    }

    def self.route(task_type, **kwargs)
      profile = TASK_PROFILES[task_type]
      raise "Tarea desconocida: #{task_type}" unless profile

      # Override con kwargs si el usuario especifica
      profile.merge(kwargs)
    end

    def self.suggest_task(description)
      # Heurística basada en palabras clave
      return :transcribe if description.match?(/transcrib|audio|mp3|whisper/i)
      return :summarize if description.match?(/resum|analiz|síntesis/i)
      return :creative_write if description.match?(/escrib|creativ|poem|email/i)
      return :analyze_code if description.match?(/código|función|clase|debug/i)
      return :long_context if description.match?(/documento.*largo|>8k|contexto.*extenso/i)
      
      :summarize # Default
    end
  end
end
```

### 2. Cliente Unificado (`client/groq_unified_client.rb`)

```ruby
module GroqTool
  class UnifiedClient
    def initialize
      @rotator = ApiRotator.new
    end

    def execute(task_type, input, **options)
      profile = ModelRouter.route(task_type)
      
      case profile[:endpoint]
      when "/audio/transcriptions"
        transcribe(input, profile, options)
      when "/chat/completions"
        chat(input, profile, options)
      end
    end

    private

    def chat(input, profile, options)
      @rotator.execute_with_rotation do |key|
        # POST /chat/completions
      end
    end

    def transcribe(input, profile, options)
      @rotator.execute_with_rotation do |key|
        # POST /audio/transcriptions
      end
    end
  end
end
```

### 3. CLI con Detección Automática (`bin/groq`)

```bash
# Modo explícito: tú dices qué tarea
groq task summarize "texto_a_resumir.txt"
groq task transcribe "audio.mp3"

# Modo automático: el sistema infiere
groq ask "Necesito transcribir esta reunión: reunion.mp3"
# → Detecta "transcribir" + ".mp3" → usa whisper-large-v3

groq ask "Escribe un email formal para el cliente X"
# → Detecta "escribe" + "email" → usa llama-3.1-8b-instant (temp 0.8)

groq ask "Analiza este código y dime qué hace"
# → Detecta "código" → usa codellama-34b-instruct
```

---

## Caso Práctico: Refactorizar `analista_yt.rb`

### Estado Actual
```ruby
# analista_yt.rb - Hardcoded a Llama-3.3-70b
resumen = GroqClient.chat(system_prompt, texto_bruto, "llama-3.3-70b-versatile")
# ...
parseado = GroqClient.chat(prompt_dialogo, fragmento, "llama-3.1-8b-instant")
```

### Estado Futuro
```ruby
# Orquestador inteligente
class AnalistaYT
  def initialize(url)
    @url = url
    @client = GroqTool::UnifiedClient.new
  end

  def ejecutar
    audio_path = descargar_audio
    transcripcion = @client.execute(:transcribe, audio_path)
    
    # El sistema elige el modelo según el contexto
    if transcripcion.length > 20000
      # Texto largo → Mixtral para contexto extendido
      resumen = @client.execute(:long_context, transcripcion, 
                                prompt: PROMPT_RESUMEN)
    else
      # Texto normal → Llama-3.3-70b para razonamiento
      resumen = @client.execute(:summarize, transcripcion,
                                prompt: PROMPT_RESUMEN)
    end
    
    # Diálogo etiquetado → Llama-3.1-8b (rápido, barato)
    dialogo = @client.execute(:creative_write, transcripcion,
                              prompt: PROMPT_DIALOGO,
                              temperature: 0.7)
  end
end
```

### Beneficios
| Antes | Después |
|-------|---------|
| Modelo fijo por script | Modelo dinámico por tarea |
| Si Groq cambia modelos, hay que reescribir | Se actualiza `TASK_PROFILES` y listo |
| 7 scripts con lógica duplicada | 1 CLI + módulos reutilizables |

---

## Sistema de Rotación de API Keys Mejorado

### Estado Actual
`api_key_rotator.rb` soporta múltiples keys pero:
- No hay metadata de qué puede cada key
- Si una key falla, se rota sin criterio

### Mejora Propuesta: `apis.json` Enriquecido

```json
{
  "groq_main": {
    "encrypted_key": "AES256:...",
    "models": ["llama-3.3-70b", "whisper-large-v3"],
    "rate_limit": "30/min",
    "priority": 1
  },
  "groq_backup": {
    "encrypted_key": "AES256:...",
    "models": ["llama-3.1-8b"],
    "rate_limit": "60/min",
    "priority": 2
  },
  "groq_code": {
    "encrypted_key": "AES256:...",
    "models": ["codellama-34b"],
    "rate_limit": "30/min",
    "priority": 1
  }
}
```

### Rotación Inteligente

```ruby
module GroqTool
  class SmartRotator
    def select_key_for_model(model_name)
      # Filtrar keys que soportan ese modelo
      candidates = @keys.select { |k| k["models"].include?(model_name) }
      
      # Ordenar por prioridad y menos usadas
      candidates.min_by { |k| [k["priority"], k["usage_count"]] }
    end

    def execute_with_rotation(model_name, &block)
      key = select_key_for_model(model_name)
      # ... lógica de retry con 429
    end
  end
end
```

---

## Plan de Implementación

### Fase 1: Fundamentos (Semana 1)
- [ ] Crear `lib/model_router.rb` con catálogo de modelos
- [ ] Crear `client/groq_unified_client.rb`
- [ ] Actualizar `api_key_rotator.rb` para soportar metadata

### Fase 2: Caso Piloto (Semana 2)
- [ ] Refactorizar `analista_yt.rb` como primer caso de éxito
- [ ] Mantener script viejo como fallback (`analista_yt_legacy.rb`)
- [ ] Documentar: "De X a Y" migration guide

### Fase 3: Expansión (Semana 3-4)
- [ ] Refactorizar `genera_subs_v1.0.rb` (usa 2 modelos: Whisper + Llama)
- [ ] Refactorizar `desgrabar_mp3.rb`
- [ ] Unificar CLI (`bin/groq`)

### Fase 4: Pulido (Semana 5)
- [ ] README con matriz de decisión
- [ ] Tests de enrutamiento
- [ ] Deprecar scripts viejos

---

## Ejemplo de Uso Futuro

```bash
# Config inicial (una sola vez)
cd tools/groq_tool
./api_key_rotator.rb add <KEY> groq_main --models llama-3.3-70b,whisper-large-v3

# Usuario pide tarea en lenguaje natural
$ groq ask "Quiero transcribir una reunión de 30 minutos"
🎯 Tarea detectada: TRANSCRIBE
🤖 Modelo seleccionado: whisper-large-v3
📊 Rate limit: 30/min (key: groq_main)
⏱️  Procesando... (esto puede tomar 2-3 min)
✅ Transcripción guardada: tmp/transcripciones/reunion_20260411.txt

# Usuario pide otra tarea
$ groq ask "Resume la transcripción de arriba"
🎯 Tarea detectada: SUMMARIZE  
🤖 Modelo seleccionado: llama-3.3-70b-versatile
📊 Rate limit: 30/min (key: groq_main)
✅ Resumen guardado: tmp/resumenes/reunion_20260411_resumen.md

# Usuario pide escribir algo creativo
$ groq ask "Escribe un email formal invitando al evento X"
🎯 Tarea detectada: CREATIVE_WRITE
🤖 Modelo seleccionado: llama-3.1-8b-instant (temperature: 0.8)
📊 Rate limit: 60/min (key: groq_backup)
✅ Email generado: tmp/output/email_20260411.md
```

---

## Métricas de Éxito

| Métrica | Antes | Después (Target) |
|---------|-------|------------------|
| Modelos hardcoded | 7 scripts con 1 modelo c/u | 0 (todos dinámicos) |
| Flexibilidad para cambiar modelo | Editar 7 archivos | Editar 1 hash (`TASK_PROFILES`) |
| Keys API sin metadata | Todas iguales | Priorizadas por modelo/tarea |
| Usuario elige modelo | Sí (manual) | Opcional (auto-sugerido) |
| Tasa de éxito en 1er intento | ~70% (puede fallar por modelo equivocado) | ~95% (modelo óptimo por defecto) |

---

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Groq cambia nombres de modelos | `TASK_PROFILES` es un hash centralizado, se actualiza en 1 lugar |
| Usuario quiere control manual | CLI acepta `--model <nombre>` como override |
| Complejidad aumentada | Documentación clara + ejemplos de uso |

---

## Decisión Clave

**¿Empezamos por `analista_yt.rb` o por `genera_subs_v1.0.rb`?**

| Criterio | analista_yt.rb | genera_subs_v1.0.rb |
|----------|----------------|---------------------|
| Complejidad | Alta (3 modelos: download + whisper + llama) | Media (2 modelos: whisper + llama) |
| Frecuencia de uso | Baja (reuniones) | Media (Vía Crucis activo) |
| Impacto visible | Alto (demo impresionante) | Alto (pipeline core) |
| **Recomendación** | ✅ **Elegir este** como caso piloto | Dejar para Fase 3 |

**Razón:** `analista_yt.rb` es más demostrativo porque usa:
1. Whisper (audio → texto)
2. Llama-3.3-70b (razonamiento complejo)
3. Llama-3.1-8b (tarea rápida de formateo)

Perfecto para mostrar el enrutamiento inteligente.

---

*Generado: 2026-04-11*  
*Enfoque: Orquestación inteligente de modelos LLM según tarea*
