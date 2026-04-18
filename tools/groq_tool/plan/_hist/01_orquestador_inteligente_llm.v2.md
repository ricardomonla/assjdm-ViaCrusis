# Plan: Orquestador Inteligente de Modelos LLM con Groq API
==============================================

## Introducción 📚
El objetivo de este plan es diseñar e implementar un orquestador inteligente que integre modelos LLM (Large Language Models) con la API de Groq, permitiendo una gestión eficiente y escalable de estos modelos.

## Arquitectura del Sistema 🏗️
El orquestador inteligente se basará en una arquitectura de microservicios, que permita la integración de diferentes componentes y servicios. Los principales componentes serán:

* **Modelos LLM**: se integrarán diferentes modelos LLM, cada uno con sus propias características y capacidades.
* **Groq API**: se utilizará la API de Groq para interactuar con los modelos LLM y realizar tareas de procesamiento de lenguaje natural.
* **Orquestador**: será el componente central que gestionará la ejecución de los modelos LLM y la interacción con la API de Groq.

## Funcionalidades del Orquestador 🤖
El orquestador inteligente tendrá las siguientes funcionalidades:

* **Gestión de modelos**: permitirá la carga, descarga y actualización de modelos LLM.
* **Ejecución de tareas**: permitirá la ejecución de tareas de procesamiento de lenguaje natural utilizando los modelos LLM y la API de Groq.
* **Monitoreo y logging**: permitirá el monitoreo y registro de las actividades del sistema.

## Beneficios del Orquestador 📈
El orquestador inteligente proporcionará los siguientes beneficios:

* **Escalabilidad**: permitirá la escalabilidad del sistema, permitiendo la integración de nuevos modelos LLM y la gestión de un gran volumen de tareas.
* **Flexibilidad**: permitirá la flexibilidad en la configuración y personalización del sistema, según las necesidades específicas de cada usuario.
* **Eficiencia**: permitirá la optimización del uso de recursos, reduciendo el tiempo y el costo de las tareas de procesamiento de lenguaje natural.

## Visión 1.0 🚀
### Introducción a la Visión
La visión para `groq_tool` es ambiciosa y se centra en transformar la herramienta de una colección de scripts especializados a un **sistema unificado de enrutamiento de tareas a modelos LLM**. Esto permitirá a los usuarios interactuar con la herramienta de manera más intuitiva y eficiente.

### Funcionalidades Clave
En este sistema unificado, el usuario podrá solicitar "lo que necesita" y el sistema automáticamente elegirá:
* **Modelo**: Seleccionará el modelo más adecuado para la tarea, ya sea Llama, Mistral, Gemma, etc.
* **Endpoint**: Determinará qué endpoint consumir, como chat, transcripción o visión, según las necesidades del usuario.
* **Configuración**: Aplicará la configuración óptima, incluyendo temperatura, tokens máximos, etc., para asegurar los mejores resultados.

### Beneficios de la Visión 1.0
La implementación de esta visión traerá numerosos beneficios, incluyendo:
| Beneficio | Descripción |
| --- | --- |
| **Mayor Eficiencia** | Los usuarios podrán trabajar de manera más eficiente, sin necesidad de conocer los detalles técnicos de cada modelo y endpoint. |
| **Mejora en la Experiencia del Usuario** | La interacción con la herramienta será más intuitiva y accesible, lo que mejorará la experiencia general del usuario. |
| **Flexibilidad y Escalabilidad** | El sistema unificado permitirá una mayor flexibilidad y escalabilidad, facilitando la incorporación de nuevos modelos y endpoints en el futuro. |

## Principio Central: "Task-First API" 📝
El objetivo de este enfoque es proporcionar una interfaz de usuario intuitiva y fácil de usar, donde el usuario solo necesita especificar la tarea que desea realizar, sin necesidad de conocer los detalles técnicos de los modelos o endpoints utilizados.

### Ejemplos de Uso 📊
En lugar de utilizar scripts específicos para cada tarea, como se muestra a continuación:
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
El sistema decide internamente qué modelo y endpoint utilizar para cada tarea.

### Selección de Modelos y Endpoints 🤖
La selección de modelos y endpoints se realiza de manera dinámica en función del tipo de tarea solicitada. A continuación, se muestra un ejemplo de cómo se puede implementar esta lógica:
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
De esta manera, el sistema puede adaptarse a diferentes tareas y modelos, proporcionando una experiencia de usuario más fluida y eficiente. 💻

## Catálogo de Modelos Groq API (2026)
### Introducción
El catálogo de modelos Groq API ofrece una variedad de opciones para diferentes tareas y necesidades. A continuación, se presentan los modelos disponibles, clasificados por tipo de tarea.

### Modelos de Texto (Chat Completions)
Los modelos de texto están diseñados para realizar tareas que involucran el procesamiento y generación de texto. A continuación, se presentan los modelos disponibles:

| Modelo | Uso Óptimo | Temperatura Sugerida | Tokens Máx | Costo Relativo |
|--------|------------|---------------------|------------|----------------|
| `llama-3.3-70b-versatile` | Razonamiento complejo, análisis, resumen 📊 | 0.2-0.5 | 8192 | Alto 💸 |
| `llama-3.1-8b-instant` | Tareas rápidas, escritura creativa 📝 | 0.7-0.9 | 8192 | Bajo 💰 |
| `mixtral-8x7b-32768` | Contexto largo (32K), multitarea 📈 | 0.5-0.7 | 32768 | Medio 📊 |
| `gemma2-9b-it` | Instrucciones técnicas, código 📚 | 0.1-0.3 | 8192 | Bajo 💰 |
| `codellama-34b-instruct` | Código, debugging, explicaciones técnicas 🐞 | 0.1-0.2 | 8192 | Medio 📊 |

### Modelos de Audio
Los modelos de audio están diseñados para realizar tareas que involucran el procesamiento y transcripción de audio. A continuación, se presentan los modelos disponibles:

| Modelo | Uso Óptimo | Límite | Notas |
|--------|------------|--------|-------|
| `whisper-large-v3` | Transcripción ES/EN, multi-idioma 🗣️ | 25 MB | Requiere endpoint `/audio/transcriptions` 📝 |
| `distil-whisper-large-v3-en` | Solo inglés, más rápido 🗣️ | 25 MB | Menor latencia ⏱️ |

### Matriz de Decisión por Tipo de Tarea
La siguiente matriz de decisión te ayudará a elegir el modelo adecuado para tu tarea:

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

### Ejemplos de Uso
A continuación, se presentan algunos ejemplos de uso de los modelos:

* Transcribir un archivo de audio: `whisper-large-v3`
* Resumir un texto: `llama-3.3-70b-versatile`
* Escribir un texto creativo: `llama-3.1-8b-instant`
* Analizar un código: `codellama-34b-instruct`

### Conclusión
El catálogo de modelos Groq API ofrece una variedad de opciones para diferentes tareas y necesidades. Al elegir el modelo adecuado, puedes aprovechar al máximo las capacidades de la API y obtener resultados óptimos. 🚀

## Arquitectura Propuesta
La arquitectura propuesta para el sistema de procesamiento de tareas se compone de tres componentes principales: el router de modelos, el cliente unificado y la interfaz de línea de comandos (CLI) con detección automática.

### 1. Router de Modelos (`lib/model_router.rb`)
El router de modelos es responsable de determinar qué modelo de lenguaje utilizar para una tarea específica. Esto se logra a través de un módulo Ruby que define un conjunto de perfiles de tareas, cada uno asociado con un modelo y un conjunto de parámetros.

```ruby
module GroqTool
  class ModelRouter
    # Definición de perfiles de tareas
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

    # Método para determinar el perfil de tarea según el tipo de tarea
    def self.route(task_type, **kwargs)
      profile = TASK_PROFILES[task_type]
      raise "Tarea desconocida: #{task_type}" unless profile

      # Override con kwargs si el usuario especifica
      profile.merge(kwargs)
    end

    # Método para sugerir el tipo de tarea según la descripción
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
El cliente unificado es responsable de interactuar con los modelos de lenguaje y realizar las tareas solicitadas. Esto se logra a través de una clase Ruby que define métodos para ejecutar tareas y comunicarse con los modelos.

```ruby
module GroqTool
  class UnifiedClient
    def initialize
      @rotator = ApiRotator.new
    end

    # Método para ejecutar una tarea
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

    # Método para realizar una transcripción
    def chat(input, profile, options)
      @rotator.execute_with_rotation do |key|
        # POST /chat/completions
      end
    end

    # Método para realizar una transcripción de audio
    def transcribe(input, profile, options)
      @rotator.execute_with_rotation do |key|
        # POST /audio/transcriptions
      end
    end
  end
end
```

### 3. CLI con Detección Automática (`bin/groq`)
La CLI con detección automática es la interfaz principal para interactuar con el sistema. Permite a los usuarios realizar tareas de manera explícita o automática.

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

### Ventajas y Desventajas
La arquitectura propuesta ofrece varias ventajas, como:

*   **Flexibilidad**: permite a los usuarios realizar tareas de manera explícita o automática.
*   **Escalabilidad**: puede manejar una variedad de tareas y modelos de lenguaje.
*   **Mantenimiento**: es fácil de mantener y actualizar, ya que cada componente es independiente.

Sin embargo, también hay algunas desventajas, como:

*   **Complejidad**: la arquitectura puede ser compleja de implementar y depurar.
*   **Dependencia**: depende de la calidad de los modelos de lenguaje y la precisión de la detección automática.

### Conclusiones
La arquitectura propuesta es una solución efectiva para el sistema de procesamiento de tareas. Ofrece flexibilidad, escalabilidad y mantenimiento, pero también requiere una implementación cuidadosa y una evaluación constante de la calidad de los modelos de lenguaje y la detección automática. 📊💻

## Caso Práctico: Refactorizar `analista_yt.rb` 📈
### Introducción 📊
En este caso práctico, se presenta la refactorización de un script Ruby llamado `analista_yt.rb`. El objetivo es mejorar la estructura y la reutilización del código, reduciendo la duplicación de lógica y aumentando la flexibilidad.

### Estado Actual 📝
El script actual utiliza un enfoque hardcoded, donde se especifica manualmente el modelo a utilizar para cada tarea. A continuación, se muestra un ejemplo del código:
```ruby
# analista_yt.rb - Hardcoded a Llama-3.3-70b
resumen = GroqClient.chat(system_prompt, texto_bruto, "llama-3.3-70b-versatile")
# ...
parseado = GroqClient.chat(prompt_dialogo, fragmento, "llama-3.1-8b-instant")
```
Este enfoque presenta varios problemas, como la rigidez y la falta de escalabilidad.

### Estado Futuro 🚀
La versión refactorizada del script introduce una clase `AnalistaYT` que encapsula la lógica de negocio y utiliza un enfoque más flexible y escalable. A continuación, se muestra el código:
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
Este enfoque permite una mayor flexibilidad y escalabilidad, ya que se puede agregar o modificar fácilmente la lógica de negocio sin afectar el resto del código.

### Beneficios 📈
A continuación, se presentan los beneficios de la refactorización:
| **Antes** | **Después** |
| --- | --- |
| Modelo fijo por script | Modelo dinámico por tarea |
| Si Groq cambia modelos, hay que reescribir | Se actualiza `TASK_PROFILES` y listo |
| 7 scripts con lógica duplicada | 1 CLI + módulos reutilizables |
La refactorización ha permitido reducir la duplicación de lógica, aumentar la flexibilidad y mejorar la escalabilidad del código. 🚀

## Sistema de Rotación de API Keys Mejorado
### Introducción 📚
El sistema de rotación de API keys es crucial para garantizar la disponibilidad y el rendimiento de las aplicaciones que dependen de servicios externos. En este contexto, se presenta una mejora al sistema actual, que busca optimizar la rotación de claves y minimizar los errores.

### Estado Actual 📊
El archivo `api_key_rotator.rb` soporta múltiples claves, pero presenta limitaciones:
* No hay metadatos que describan las capacidades de cada clave.
* Si una clave falla, se rota sin criterio, lo que puede generar problemas de rendimiento y disponibilidad.

### Mejora Propuesta: `apis.json` Enriquecido 📈
Se propone enriquecer el archivo `apis.json` con metadatos adicionales para cada clave, como se muestra a continuación:
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
Estos metadatos permiten una mejor gestión de las claves y una rotación más inteligente.

### Rotación Inteligente 🔄
Se implementa un módulo `SmartRotator` que selecciona la clave más adecuada para cada modelo, teniendo en cuenta la prioridad y el uso reciente de cada clave.
```ruby
module GroqTool
  class SmartRotator
    def select_key_for_model(model_name)
      # Filtrar claves que soportan ese modelo
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
Este enfoque garantiza una rotación más eficiente y reduce el riesgo de errores.

### Beneficios y Ventajas 📈
La implementación de este sistema de rotación de API keys mejorado ofrece varios beneficios, incluyendo:
* Mayor disponibilidad y rendimiento de las aplicaciones.
* Reducción de errores y problemas de conectividad.
* Mejora en la gestión de claves y metadatos.
* Flexibilidad y escalabilidad para adaptarse a necesidades cambiantes.

## Plan de Implementación
El plan de implementación se divide en cuatro fases, cada una con objetivos y tareas específicas. A continuación, se presentan las fases y sus respectivas tareas.

### Fase 1: Fundamentos (Semana 1) 📚
En esta fase, se crean los fundamentos del proyecto. Las tareas incluyen:
* Crear `lib/model_router.rb` con catálogo de modelos
* Crear `client/groq_unified_client.rb`
* Actualizar `api_key_rotator.rb` para soportar metadata

### Fase 2: Caso Piloto (Semana 2) 🚀
En esta fase, se implementa un caso piloto para probar la funcionalidad del proyecto. Las tareas incluyen:
* Refactorizar `analista_yt.rb` como primer caso de éxito
* Mantener script viejo como fallback (`analista_yt_legacy.rb`)
* Documentar: "De X a Y" migration guide

### Fase 3: Expansión (Semana 3-4) 📈
En esta fase, se expande la funcionalidad del proyecto. Las tareas incluyen:
* Refactorizar `genera_subs_v1.0.rb` (usa 2 modelos: Whisper + Llama)
* Refactorizar `desgrabar_mp3.rb`
* Unificar CLI (`bin/groq`)

### Fase 4: Pulido (Semana 5) 💫
En esta fase, se pulen los detalles del proyecto. Las tareas incluyen:
* Crear README con matriz de decisión
* Implementar tests de enrutamiento
* Deprecar scripts viejos

Este plan de implementación proporciona una visión general clara de las tareas y objetivos para cada fase del proyecto. Al seguir este plan, se puede asegurar que el proyecto se complete de manera eficiente y efectiva.

## Ejemplo de Uso Futuro
En esta sección, se presenta un ejemplo de cómo utilizar la herramienta `groq` para realizar diferentes tareas de procesamiento de lenguaje natural.

### Configuración Inicial
Antes de comenzar a utilizar la herramienta, es necesario configurar la clave de API y agregar los modelos de lenguaje que se desean utilizar. Esto se puede hacer ejecutando el siguiente comando:
```bash
cd tools/groq_tool
./api_key_rotator.rb add <KEY> groq_main --models llama-3.3-70b,whisper-large-v3
```
### Tareas de Procesamiento de Lenguaje Natural
A continuación, se presentan algunos ejemplos de cómo utilizar la herramienta `groq` para realizar diferentes tareas de procesamiento de lenguaje natural.

#### Transcripción de Audio
Para transcribir una reunión de 30 minutos, se puede ejecutar el siguiente comando:
```bash
$ groq ask "Quiero transcribir una reunión de 30 minutos"
```
La herramienta detectará la tarea como `TRANSCRIBE` y seleccionará el modelo `whisper-large-v3`. El resultado se guardará en un archivo de texto en la carpeta `tmp/transcripciones`.

🎯 Tarea detectada: TRANSCRIBE
🤖 Modelo seleccionado: whisper-large-v3
📊 Rate limit: 30/min (key: groq_main)
⏱️  Procesando... (esto puede tomar 2-3 min)
✅ Transcripción guardada: tmp/transcripciones/reunion_20260411.txt

#### Resumen de Texto
Para resumir la transcripción anterior, se puede ejecutar el siguiente comando:
```bash
$ groq ask "Resume la transcripción de arriba"
```
La herramienta detectará la tarea como `SUMMARIZE` y seleccionará el modelo `llama-3.3-70b-versatile`. El resultado se guardará en un archivo de texto en la carpeta `tmp/resumenes`.

🎯 Tarea detectada: SUMMARIZE  
🤖 Modelo seleccionado: llama-3.3-70b-versatile
📊 Rate limit: 30/min (key: groq_main)
✅ Resumen guardado: tmp/resumenes/reunion_20260411_resumen.md

#### Escritura Creativa
Para generar un email formal invitando a un evento, se puede ejecutar el siguiente comando:
```bash
$ groq ask "Escribe un email formal invitando al evento X"
```
La herramienta detectará la tarea como `CREATIVE_WRITE` y seleccionará el modelo `llama-3.1-8b-instant` con una temperatura de 0.8. El resultado se guardará en un archivo de texto en la carpeta `tmp/output`.

🎯 Tarea detectada: CREATIVE_WRITE
🤖 Modelo seleccionado: llama-3.1-8b-instant (temperature: 0.8)
📊 Rate limit: 60/min (key: groq_backup)
✅ Email generado: tmp/output/email_20260411.md

## Métricas de Éxito 📊
Las métricas de éxito son fundamentales para evaluar el rendimiento y la eficiencia de nuestro sistema. A continuación, se presentan las métricas clave antes y después de la implementación de los cambios objetivo.

### Resumen de Métricas
| Métrica | Valor Anterior | Valor Objetivo |
|---------|---------------|----------------|
| Modelos hardcoded | 7 scripts con 1 modelo cada uno | 0 (todos dinámicos) |
| Flexibilidad para cambiar modelo | Edición de 7 archivos | Edición de 1 hash (`TASK_PROFILES`) |
| Keys API sin metadata | Todas iguales | Priorizadas por modelo/tarea |
| Usuario elige modelo | Sí (manual) | Opcional (auto-sugerido) |
| Tasa de éxito en 1er intento | ~70% (puede fallar por modelo equivocado) | ~95% (modelo óptimo por defecto) |

### Análisis de las Métricas
* **Modelos hardcoded**: Se reduce de 7 a 0, lo que indica una mayor flexibilidad y capacidad de adaptación del sistema.
* **Flexibilidad para cambiar modelo**: Se simplifica el proceso de edición, pasando de 7 archivos a solo 1 hash (`TASK_PROFILES`).
* **Keys API sin metadata**: Se priorizan las keys API por modelo y tarea, lo que mejora la organización y el acceso a la información.
* **Usuario elige modelo**: Se mantiene la opción de selección manual, pero se agrega la capacidad de auto-sugerir el modelo óptimo.
* **Tasa de éxito en 1er intento**: Se incrementa significativamente, pasando de ~70% a ~95%, lo que indica una mayor eficiencia y precisión del sistema. 🚀

## Riesgos 🚨
Los riesgos potenciales en el proyecto se presentan a continuación, junto con las estrategias de mitigación implementadas para minimizar su impacto.

| Riesgo | Descripción | Mitigación |
|--------|-------------|------------|
| **Cambios en nombres de modelos** 📝 | Groq cambia nombres de modelos | Utilizar `TASK_PROFILES` como un hash centralizado, que se actualiza en un solo lugar, garantiza la consistencia y facilita la gestión de cambios. |
| **Necesidad de control manual** 🖥️ | Usuario quiere control manual sobre la selección de modelos | La interfaz de línea de comandos (CLI) acepta el parámetro `--model <nombre>` como override, permitiendo a los usuarios un control manual cuando sea necesario. |
| **Aumento de complejidad** 🤔 | Complejidad aumentada en la configuración y uso del sistema | Se proporciona documentación clara y ejemplos de uso para ayudar a los usuarios a entender y manejar el sistema de manera efectiva, reduciendo la curva de aprendizaje y el riesgo de errores. |

## Decisión Clave 📊
### Selección del Caso Piloto

Debemos decidir si comenzamos con `analista_yt.rb` o `genera_subs_v1.0.rb`. A continuación, se presentan los criterios de evaluación para cada opción:

| Criterio | analista_yt.rb | genera_subs_v1.0.rb |
|----------|----------------|---------------------|
| **Complejidad** | Alta (3 modelos: download + whisper + llama) | Media (2 modelos: whisper + llama) |
| **Frecuencia de uso** | Baja (reuniones) | Media (Vía Crucis activo) |
| **Impacto visible** | Alto (demo impresionante) | Alto (pipeline core) |
| **Recomendación** | ✅ **Elegir este** como caso piloto | Dejar para Fase 3 |

### Justificación de la Recomendación 🤔

La razón principal para elegir `analista_yt.rb` como caso piloto es que es más demostrativo y utiliza una variedad de tecnologías, incluyendo:

1. **Whisper** (audio → texto): permite la transcripción de audio a texto.
2. **Llama-3.3-70b** (razonamiento complejo): permite el análisis y procesamiento de información compleja.
3. **Llama-3.1-8b** (tarea rápida de formateo): permite la realización de tareas de formateo de manera rápida y eficiente.

Esto lo hace ideal para mostrar el enrutamiento inteligente y la orquestación de modelos LLM según la tarea.

### Información Adicional 📝
* **Fecha de generación:** 2026-04-11
* **Enfoque:** Orquestación inteligente de modelos LLM según tarea