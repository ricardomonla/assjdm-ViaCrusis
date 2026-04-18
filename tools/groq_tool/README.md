# groq_tool 🤖

Orquestador inteligente de modelos LLM de Groq API.

## Uso Rápido

### 1. Configurar API Key (si no está hecha)

```bash
cd tools/groq_tool
./api_key_rotator/api_key_rotator.rb add <TU_API_KEY> mi_key
```

### 2. Usar la CLI

```bash
# Ver modelos disponibles
ruby bin/groq models

# Que el sistema sugiera modelo para tu tarea
ruby bin/groq suggest "transcribir una reunión"
ruby bin/groq suggest "resumir este texto"
ruby bin/groq suggest "mejorar este código Ruby"

# Preguntar directamente (modelo auto-seleccionado)
ruby bin/groq ask "¿Qué modelo es mejor para código?"

# Mejorar un archivo con IA (genera versión completa mejorada)
ruby bin/groq improve plan/01_orquestador_inteligente_llm.md
ruby bin/groq improve mi_script.rb

# Chat interactivo
ruby bin/groq chat
```

### 3. Usar con Passphrase

Si las keys están encriptadas (lo están), necesitás la passphrase:

```bash
# Opción A: Variable de entorno (sesión)
export GROQ_ROTATOR_PASS="tu-frase-secreta"
ruby bin/groq ask "tu pregunta"

# Opción B: Te la pide al ejecutar (más seguro)
ruby bin/groq ask "tu pregunta"
# → Te pide: 🔑 Ingresa la Frase Secreta (Pista: <pista>)
```

---

## Modelos Disponibles

| Tarea | Modelo | Temperatura | Uso |
|-------|--------|-------------|-----|
| `transcribe` | whisper-large-v3 | - | Audio → Texto |
| `summarize` | llama-3.3-70b-versatile | 0.2 | Análisis, resumen |
| `creative_write` | llama-3.1-8b-instant | 0.8 | Email, creativo |
| `analyze_code` | codellama-34b-instruct | 0.1 | Código, debugging |
| `long_context` | mixtral-8x7b-32768 | 0.5 | Documentos largos |
| `technical_explain` | gemma2-9b-it | 0.3 | Tutoriales, guías |
| `quick_chat` | llama-3.1-8b-instant | 0.5 | Chat rápido |
| `improve_doc` | mixtral-8x7b-32768 | 0.3 | Mejorar documentos |

---

## Ejemplos de Uso

### Dogfooding: Mejorar el propio plan

```bash
# Que la IA mejore un documento (genera versión completa + menú interactivo)
ruby bin/groq improve README.md
# → Genera README.md.mejorado.md
# → Te pregunta: [1] Ver diff, [2] Reemplazar, [3] Mantener ambos

# Mejorar código Ruby (usa CodeLlama)
ruby bin/groq improve mi_script.rb
```

### Transcribir audio (cuando esté implementado)

```bash
ruby bin/groq ask "Transcribí esta reunión: reunion.mp3"
```

### Analizar código

```bash
ruby bin/groq ask "¿Qué hace este script?" < mi_script.rb
```

---

## Arquitectura

```
bin/groq                  → CLI unificado
├── lib/model_router.rb   → Decide qué modelo usar
├── client/
│   └── groq_unified_client.rb  → Consume Groq API
└── api_key_rotator/
    └── api_key_rotator.rb      → Rotación de keys encriptadas
```

---

## Estado Actual

✅ **Funcional:**
- CLI con comandos: `models`, `suggest`, `ask`, `improve`, `chat`
- ModelRouter con 7 perfiles de tarea
- ApiRotator como módulo reusable

🚧 **En progreso:**
- Endpoint `/audio/transcriptions` para Whisper
- Tests básicos

---

## Troubleshooting

### "No hay llaves configuradas"
```bash
./api_key_rotator/api_key_rotator.rb add <API_KEY> mi_key
```

### "Llave corrupta"
La passphrase es incorrecta. Usá la correcta con:
```bash
export GROQ_ROTATOR_PASS="la-correcta"
```

### "HTTP 429" (Rate Limit)
El rotador cambia automáticamente de key. Si todas están saturadas, espera ~60s.
