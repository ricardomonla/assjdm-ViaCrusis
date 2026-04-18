# MVP Dogfooding: Usar la Tool Mientras la Construimos
======================================================

## Introducción
---------------

Este documento describe el estado actual y el uso de la herramienta de dogfooding, que permite mejorar la propia herramienta mientras se construye. La herramienta utiliza modelos de lenguaje para sugerir mejoras y realizar tareas específicas.

## Estado Actual (2026-04-11)
---------------------------

### ✅ Lo Que Ya Funciona

La herramienta ya puede realizar las siguientes tareas:

* Ver modelos disponibles: `ruby bin/groq models`
* Sugerir modelo según tarea: `ruby bin/groq suggest "transcribir audio"`
* Preguntar con modelo auto-seleccionado: `ruby bin/groq ask "¿Qué modelo es mejor para resumir textos largos?"`
* Mejorar archivos (dogfooding!): `ruby bin/groq improve plan/01_orquestador_inteligente_llm.md`
* Chat interactivo: `ruby bin/groq chat`

### 🔑 Requisito: API Key

Para utilizar la herramienta, es necesario tener al menos una API key de Groq válida. Puedes configurarla de la siguiente manera:

```bash
# Si ya está configurada en apis.json:
export GROQ_ROTATOR_PASS="tu-frase-secreta"

# Si necesitas agregar una nueva:
cd tools/groq_tool
./api_key_rotator/api_key_rotator.rb add gsk_xxxxxxxx mi_key_groq
```

## Caso de Uso Inmediato: Dogfooding del Plan
------------------------------------------

### Situación

Querés que la IA **mejore el plan de refactorización** mientras lo construís.

### Flujo

1. Ver qué modelo sugiere el sistema: `ruby bin/groq suggest "mejorar plan de refactorización de código"`
2. Pedir mejora específica: `ruby bin/groq ask "Analizá el plan en plan/01_orquestador_inteligente_llm.md y sugerí 3 mejoras concretas de priorización. Foco: qué implementar primero para máximo impacto." --model llama-3.3-70b-versatile`
3. Guardar output en archivo de mejora: `ruby bin/groq ask "..." > plan/02_feedback_ia.md`

## Matriz de Decisiones de Modelos (Quick Reference)
---------------------------------------------------

| Si necesitás... | Usá este modelo | Comando |
|-----------------|-----------------|---------|
| Transcribir audio | `whisper-large-v3` | `groq ask "transcribí X.mp3"` |
| Resumir/reasoning | `llama-3.3-70b-versatile` | `groq suggest "resumir"` |
| Código/debug | `codellama-34b-instruct` | `groq improve script.rb` |
| Texto creativo | `llama-3.1-8b-instant` (temp 0.8) | `groq ask "escribí un email"` |
| Contexto largo | `mixtral-8x7b-32768` | `groq ask --model mixtral...` |
| Explicación técnica | `gemma2-9b-it` | `groq ask "explicá cómo..."` |

## Próximos Pasos (Priorizados)
------------------------------

### Semana 1: Consolidar MVP

* [x] CLI básico funcional
* [x] ModelRouter con 7 perfiles
* [x] ApiRotator como módulo
* [ ] **Probar con API key real** (falta passphrase)
* [ ] Agregar logging de tokens usados

### Semana 2: Transcripción Whisper

* [ ] Implementar `groq transcribe <archivo>`
* [ ] Soporte para archivos >25MB (chunking)
* [ ] Cache de transcripciones

### Semana 3: Integración con Scripts Existentes

* [ ] `analista_yt.rb` → `groq analyze-yt <URL>`
* [ ] `genera_subs_v1.0.rb` → `groq subs <id_pista>`
* [ ] Documentar migración

### Semana 4: Tests y Pulido

* [ ] Smoke tests (al menos 5)
* [ ] README en español completo
* [ ] Ejemplos de cada comando

## Comandos para Probar YA
-------------------------

* Ver ayuda: `ruby bin/groq help`
* Listar modelos: `ruby bin/groq models`
* Sugerir modelo para tarea específica: `ruby bin/groq suggest "analizar código Ruby"`
* Dogfooding: que la IA mejore su propio código: `ruby bin/groq improve lib/model_router.rb`
* Dogfooding: que la IA mejore el plan: `ruby bin/groq improve plan/01_orquestador_inteligente_llm.md`
* Chat interactivo: `ruby bin/groq chat`

## Notas de Implementación
-------------------------

### Por Qué Este Enfoque

1. **Task-First**: El usuario piensa en "qué necesita", no en "qué modelo"
2. **Auto-selección**: El sistema elige por defecto, pero podés override
3. **Rotación transparente**: Múltiples API keys sin que el usuario piense
4. **Dogfooding**: Usás la tool para mejorar la tool

### Arquitectura Clave

```
┌─────────────────────────────────────────┐
│  ruby bin/groq ask "tu pregunta"        │
└───────────────┬─────────────────────────┘
                ▼
┌─────────────────────────────────────────┐
│  ModelRouter.suggest_task(pregunta)     │
│  → Detecta palabras clave               │
│  → Retorna :summarize / :analyze_code   │
└───────────────┬─────────────────────────┘
                ▼
┌─────────────────────────────────────────┐
│  TASK_PROFILES[:summarize]              │
│  → model: llama-3.3-70b-versatile       │
│  → temperature: 0.2                     │
└───────────────┬─────────────────────────┘
                ▼
┌─────────────────────────────────────────┐
│  ApiRotator.execute_with_rotation       │
│  → Prueba key #1                        │
│  → Si 429, rota a key #2                │
└───────────────┬─────────────────────────┘
                ▼
┌─────────────────────────────────────────┐
│  Groq API → Respuesta                   │
└─────────────────────────────────────────┘
```

## Feedback Esperado
--------------------

Después de usar la herramienta, contestar:

1. **¿La detección automática de tarea funciona?**
   - `groq suggest "X"` ¿sugiere el modelo correcto?
2. **¿El dogfooding es útil?**
   - `groq improve <archivo>` ¿da sugerencias prácticas?
3. **¿Qué falta para uso diario?**
   - ¿Transcripción de audio?
   - ¿Batch processing?
   - ¿UI web?

## Conclusión
------------

La herramienta de dogfooding es una herramienta poderosa que puede ayudar a mejorar la propia herramienta mientras se construye. Con la capacidad de sugerir modelos y realizar tareas específicas, puede ser una herramienta valiosa para cualquier proyecto. Sin embargo, es importante tener en cuenta que la herramienta aún está en desarrollo y puede requerir más trabajo para ser completamente funcional.