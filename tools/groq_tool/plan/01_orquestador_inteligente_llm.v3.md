# Plan: Orquestador Inteligente de Modelos LLM con Groq API
==============================================
### Introducción 📚
El objetivo de este plan es diseñar e implementar un orquestador inteligente que integre modelos LLM (Large Language Models) con la API de Groq, permitiendo una gestión eficiente y escalable de tareas de procesamiento de lenguaje natural.

### Arquitectura del Sistema 🗺️
La arquitectura del orquestador inteligente se basará en los siguientes componentes:
* **Modelos LLM**: Integración de modelos de lenguaje avanzados para el procesamiento de texto.
* **API de Groq**: Utilización de la API de Groq para la gestión y el despliegue de los modelos LLM.
* **Orquestador**: Desarrollo de un orquestador que coordine y gestione las tareas de procesamiento de lenguaje natural.

### Funcionalidades del Orquestador 🤖
El orquestador inteligente tendrá las siguientes funcionalidades:
* **Gestión de Modelos**: Capacidad para gestionar y desplegar múltiples modelos LLM.
* **Procesamiento de Tareas**: Procesamiento eficiente de tareas de procesamiento de lenguaje natural.
* **Escalabilidad**: Capacidad para escalar horizontalmente para manejar grandes volúmenes de datos.

### Beneficios del Orquestador 📈
Los beneficios del orquestador inteligente incluyen:
* **Mejora de la Eficiencia**: Mejora en la eficiencia del procesamiento de tareas de lenguaje natural.
* **Escalabilidad**: Capacidad para manejar grandes volúmenes de datos de manera eficiente.
* **Flexibilidad**: Flexibilidad para integrar diferentes modelos LLM y APIs.

## Introducción 📚
El objetivo principal de este plan es el diseño e implementación de un **orquestador inteligente** que combine de manera efectiva los **modelos LLM (Large Language Models)** con la **API de Groq**. Esto permitirá una gestión **eficiente** y **escalable** de estos modelos, abriendo camino a nuevas posibilidades en el procesamiento y análisis de lenguaje natural. 🤖

## Arquitectura del Sistema 🏗️
La arquitectura del sistema se basa en una estructura de microservicios, lo que permite la integración flexible y escalable de diferentes componentes y servicios. Esta arquitectura ofrece varias ventajas, incluyendo la capacidad de actualizar y mantener componentes individuales sin afectar el funcionamiento general del sistema.

### Componentes Principales
Los componentes clave de la arquitectura del sistema son:

* **Modelos LLM (Large Language Models)**: se integrarán varios modelos LLM, cada uno con sus propias características y capacidades únicas. Esto permitirá abordar una amplia gama de tareas de procesamiento de lenguaje natural y aprovechar las fortalezas de cada modelo.
* **Groq API**: la API de Groq se utilizará como interfaz para interactuar con los modelos LLM y realizar tareas de procesamiento de lenguaje natural de manera eficiente. La API proporciona una capa de abstracción que facilita la integración y el uso de los modelos LLM.
* **Orquestador**: el orquestador es el componente central del sistema, responsable de gestionar la ejecución de los modelos LLM y la interacción con la API de Groq. Su función es asegurar que las tareas se ejecuten de manera coordinada y eficiente, garantizando el rendimiento óptimo del sistema.

### Ventajas de la Arquitectura
La elección de una arquitectura de microservicios ofrece varias ventajas, incluyendo:

| Ventaja | Descripción |
| --- | --- |
| **Flexibilidad** | Permite la integración de nuevos componentes y servicios sin afectar el funcionamiento del sistema existente. |
| **Escalabilidad** | Facilita el escalado individual de componentes para satisfacer las necesidades cambiantes del sistema. |
| **Mantenimiento** | Permite el mantenimiento y la actualización de componentes individuales sin interrumpir el funcionamiento general del sistema. |
| **Tolerancia a Fallos** | Si un componente falla, el sistema puede continuar funcionando, ya que los demás componentes pueden seguir operando de manera independiente. |

## Funcionalidades del Orquestador 🤖
El orquestador inteligente es un componente clave en el sistema, y se encargará de gestionar y coordinar las diferentes tareas y procesos. A continuación, se presentan las funcionalidades principales del orquestador:

### Gestión de Modelos 📈
* **Carga de modelos**: permitirá la carga de nuevos modelos LLM en el sistema.
* **Descarga de modelos**: permitirá la descarga de modelos LLM existentes en el sistema.
* **Actualización de modelos**: permitirá la actualización de modelos LLM existentes en el sistema para asegurar que siempre se utilicen las versiones más recientes y precisas.

### Ejecución de Tareas 🚀
* **Procesamiento de lenguaje natural**: permitirá la ejecución de tareas de procesamiento de lenguaje natural utilizando los modelos LLM y la API de Groq.
* **Integración con la API de Groq**: permitirá la integración con la API de Groq para aprovechar sus capacidades de procesamiento de lenguaje natural.

### Monitoreo y Logging 📊
* **Monitoreo de actividades**: permitirá el monitoreo de las actividades del sistema para asegurar que todo funcione correctamente.
* **Registro de eventos**: permitirá el registro de eventos y errores en el sistema para facilitar la depuración y el análisis de problemas.

## Beneficios del Orquestador 📈
El orquestador inteligente ofrece una serie de ventajas clave que mejoran el rendimiento y la eficiencia del sistema. A continuación, se presentan los beneficios principales:

* **Escalabilidad 🚀**: permite la integración de nuevos modelos LLM y la gestión de un gran volumen de tareas, lo que facilita la expansión del sistema según sea necesario.
* **Flexibilidad 🎯**: ofrece la posibilidad de personalizar y configurar el sistema de acuerdo con las necesidades específicas de cada usuario, lo que garantiza una experiencia adaptada a cada caso.
* **Eficiencia 📊**: optimiza el uso de recursos, reduciendo el tiempo y el costo asociados con las tareas de procesamiento de lenguaje natural, lo que conduce a una mayor productividad y rentabilidad.

Estos beneficios combinados permiten que el orquestador inteligente sea una herramienta fundamental para mejorar la eficiencia y la escalabilidad del sistema, lo que a su vez conduce a una mejor experiencia del usuario y una mayor competitividad en el mercado. 💡

## Visión 1.0 🚀
### Introducción a la Visión
La visión para `groq_tool` es ambiciosa y se centra en transformar la herramienta de una colección de scripts especializados a un **sistema unificado de enrutamiento de tareas a modelos LLM** 🤖. Esto permitirá a los usuarios interactuar con la herramienta de manera más intuitiva y eficiente, mejorando significativamente su experiencia de uso.

### Funcionalidades Clave
En este sistema unificado, el usuario podrá solicitar "lo que necesita" y el sistema automáticamente elegirá:
* **Modelo**: Seleccionará el modelo más adecuado para la tarea, ya sea Llama, Mistral, Gemma, etc. 📊
* **Endpoint**: Determinará qué endpoint consumir, como chat, transcripción o visión, según las necesidades del usuario 📈
* **Configuración**: Aplicará la configuración óptima, incluyendo temperatura, tokens máximos, etc., para asegurar los mejores resultados 📈

### Beneficios de la Visión 1.0
La implementación de esta visión traerá numerosos beneficios, incluyendo:
| Beneficio | Descripción |
| --- | --- |
| **Mayor Eficiencia** | Los usuarios podrán trabajar de manera más eficiente, sin necesidad de conocer los detalles técnicos de cada modelo y endpoint, lo que reduce el tiempo de aprendizaje y aumento la productividad 📊 |
| **Mejora en la Experiencia del Usuario** | La interacción con la herramienta será más intuitiva y accesible, lo que mejorará la experiencia general del usuario y aumentará la satisfacción 📈 |
| **Flexibilidad y Escalabilidad** | El sistema unificado permitirá una mayor flexibilidad y escalabilidad, facilitando la incorporación de nuevos modelos y endpoints en el futuro, lo que garantiza la evolución y el crecimiento de la herramienta 🚀 |

### Casos de Uso
Algunos ejemplos de cómo se puede utilizar el sistema unificado incluyen:
* **Análisis de texto**: El usuario puede solicitar un análisis de texto y el sistema seleccionará el modelo más adecuado para la tarea, como Llama o Mistral.
* **Generación de contenido**: El usuario puede solicitar la generación de contenido y el sistema determinará qué endpoint consumir, como chat o transcripción.
* **Resolución de problemas**: El usuario puede solicitar ayuda para resolver un problema y el sistema aplicará la configuración óptima para proporcionar la mejor solución.

### Próximos Pasos
Para lograr la visión 1.0, se deben seguir los siguientes pasos:
1. **Diseño del sistema unificado**: Se debe diseñar el sistema unificado, incluyendo la arquitectura y la infraestructura necesarias.
2. **Desarrollo de la herramienta**: Se debe desarrollar la herramienta, incluyendo la implementación de los modelos y endpoints.
3. **Pruebas y validación**: Se deben realizar pruebas y validación para asegurar que el sistema unificado funcione correctamente y cumpla con los requisitos del usuario.

## Principio Central: "Task-First API" 📝
El objetivo principal de este enfoque es diseñar una interfaz de usuario intuitiva y fácil de usar, donde el usuario solo necesita especificar la tarea que desea realizar, sin necesidad de conocer los detalles técnicos de los modelos o endpoints utilizados. Esto se logra a través de una arquitectura que se centra en la tarea como unidad fundamental de interacción.

### Ventajas del Enfoque "Task-First" 📈
- **Simplificación del Flujo de Trabajo**: Al centrarse en la tarea, el usuario puede interactuar con el sistema de manera más natural y sin necesidad de conocimientos técnicos profundos.
- **Flexibilidad y Adaptabilidad**: El sistema puede adaptarse dinámicamente a diferentes tareas y modelos, lo que permite una mayor flexibilidad y capacidad de respuesta a necesidades cambiantes.

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
El sistema decide internamente qué modelo y endpoint utilizar para cada tarea, lo que simplifica significativamente la interacción del usuario.

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
Esta aproximación permite una gran flexibilidad y capacidad de adaptación a diferentes escenarios de uso, mejorando así la experiencia del usuario y la eficiencia del sistema.

### Beneficios y Casos de Uso 🌟
El enfoque "Task-First API" ofrece varios beneficios, incluyendo:
- **Mayor accesibilidad**: Los usuarios no necesitan conocimientos técnicos profundos para interactuar con el sistema.
- **Mejora en la productividad**: Al simplificar el flujo de trabajo, los usuarios pueden centrarse en sus tareas principales.
- **Flexibilidad y escalabilidad**: El sistema puede adaptarse a nuevas tareas y modelos, lo que facilita su evolución y mantenimiento.

De esta manera, el sistema puede proporcionar una experiencia de usuario más fluida y eficiente, adaptándose a las necesidades cambiantes de los usuarios y mejorando su productividad y satisfacción. 💻

## Catálogo de Modelos Groq API (2026)
### Introducción 🌟
El catálogo de modelos Groq API ofrece una variedad de opciones para diferentes tareas y necesidades. A continuación, se presentan los modelos disponibles, clasificados por tipo de tarea. Estos modelos están diseñados para realizar tareas específicas, como procesamiento y generación de texto, transcripción de audio y más.

### Modelos de Texto (Chat Completions) 📝
Los modelos de texto están diseñados para realizar tareas que involucran el procesamiento y generación de texto. A continuación, se presentan los modelos disponibles:

| Modelo | Uso Óptimo | Temperatura Sugerida | Tokens Máx | Costo Relativo |
|--------|------------|---------------------|------------|----------------|
| `llama-3.3-70b-versatile` | Razonamiento complejo, análisis, resumen 📊 | 0.2-0.5 | 8192 | Alto 💸 |
| `llama-3.1-8b-instant` | Tareas rápidas, escritura creativa 📝 | 0.7-0.9 | 8192 | Bajo 💰 |
| `mixtral-8x7b-32768` | Contexto largo (32K), multitarea 📈 | 0.5-0.7 | 32768 | Medio 📊 |
| `gemma2-9b-it` | Instrucciones técnicas, código 📚 | 0.1-0.3 | 8192 | Bajo 💰 |
| `codellama-34b-instruct` | Código, debugging, explicaciones técnicas 🐞 | 0.1-0.2 | 8192 | Medio 📊 |

### Modelos de Audio 🗣️
Los modelos de audio están diseñados para realizar tareas que involucran el procesamiento y transcripción de audio. A continuación, se presentan los modelos disponibles:

| Modelo | Uso Óptimo | Límite | Notas |
|--------|------------|--------|-------|
| `whisper-large-v3` | Transcripción ES/EN, multi-idioma 🗣️ | 25 MB | Requiere endpoint `/audio/transcriptions` 📝 |
| `distil-whisper-large-v3-en` | Solo inglés, más rápido 🗣️ | 25 MB | Menor latencia ⏱️ |

### Matriz de Decisión por Tipo de Tarea 📊
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

### Ejemplos de Uso 📝
A continuación, se presentan algunos ejemplos de uso de los modelos:

* **Transcribir un archivo de audio**: Utiliza el modelo `whisper-large-v3` para transcribir un archivo de audio.
* **Resumir un texto**: Utiliza el modelo `llama-3.3-70b-versatile` para resumir un texto.
* **Escribir un texto creativo**: Utiliza el modelo `llama-3.1-8b-instant` con una temperatura de 0.8 para escribir un texto creativo.
* **Analizar un código**: Utiliza el modelo `codellama-34b-instruct` para analizar un código.

### Conclusión 🚀
El catálogo de modelos Groq API ofrece una variedad de opciones para diferentes tareas y necesidades. Al elegir el modelo adecuado, puedes aprovechar al máximo las capacidades de la API y obtener resultados óptimos. Recuerda consultar la documentación oficial para obtener más información sobre cada modelo y cómo utilizarlos de manera efectiva. 📚

## Arquitectura Propuesta
La arquitectura propuesta para el sistema de procesamiento de tareas se compone de tres componentes principales: 
1. **Router de Modelos**: responsable de determinar qué modelo de lenguaje utilizar para una tarea específica.
2. **Cliente Unificado**: interactúa con los modelos de lenguaje y realiza las tareas solicitadas.
3. **Interfaz de Línea de Comandos (CLI) con Detección Automática**: la interfaz principal para interactuar con el sistema.

### 1. Router de Modelos (`lib/model_router.rb`)
El router de modelos utiliza un módulo Ruby que define un conjunto de perfiles de tareas, cada uno asociado con un modelo y un conjunto de parámetros.

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
El cliente unificado interactúa con los modelos de lenguaje y realiza las tareas solicitadas.

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
La CLI con detección automática es la interfaz principal para interactuar con el sistema.

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
La arquitectura propuesta ofrece varias ventajas y desventajas:

#### Ventajas
| Ventaja | Descripción |
| --- | --- |
| **Flexibilidad** | Permite a los usuarios realizar tareas de manera explícita o automática. |
| **Escalabilidad** | Puede manejar una variedad de tareas y modelos de lenguaje. |
| **Mantenimiento** | Es fácil de mantener y actualizar, ya que cada componente es independiente. |

#### Desventajas
| Desventaja | Descripción |
| --- | --- |
| **Complejidad** | La arquitectura puede ser compleja de implementar y depurar. |
| **Dependencia** | Depende de la calidad de los modelos de lenguaje y la precisión de la detección automática. |

### Conclusiones
La arquitectura propuesta es una solución efectiva para el sistema de procesamiento de tareas. Ofrece flexibilidad, escalabilidad y mantenimiento, pero también requiere una implementación cuidadosa y una evaluación constante de la calidad de los modelos de lenguaje y la detección automática. 📊💻

## Caso Práctico: Refactorizar `analista_yt.rb` 📈
### Introducción 📊
En este caso práctico, se presenta la refactorización de un script Ruby llamado `analista_yt.rb`. El objetivo es mejorar la estructura y la reutilización del código, reduciendo la duplicación de lógica y aumentando la flexibilidad. 📈

### Estado Actual: Limitaciones del Enfoque Hardcoded 📝
El script actual utiliza un enfoque hardcoded, donde se especifica manualmente el modelo a utilizar para cada tarea. A continuación, se muestra un ejemplo del código:
```ruby
# analista_yt.rb - Hardcoded a Llama-3.3-70b
resumen = GroqClient.chat(system_prompt, texto_bruto, "llama-3.3-70b-versatile")
# ...
parseado = GroqClient.chat(prompt_dialogo, fragmento, "llama-3.1-8b-instant")
```
Este enfoque presenta varios problemas, como:
* Rigidez: el código es inflexible y no se puede adaptar fácilmente a cambios en los modelos o en la lógica de negocio.
* Falta de escalabilidad: el código no se puede escalar fácilmente para manejar tareas más complejas o un mayor volumen de datos.
* Duplicación de lógica: el código duplica la lógica para cada tarea, lo que puede llevar a errores y dificultar el mantenimiento.

### Estado Futuro: Enfoque Orientado a Objetos 🚀
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

### Beneficios de la Refactorización 📈
A continuación, se presentan los beneficios de la refactorización:
| **Antes** | **Después** |
| --- | --- |
| Modelo fijo por script | Modelo dinámico por tarea |
| Si Groq cambia modelos, hay que reescribir | Se actualiza `TASK_PROFILES` y listo |
| 7 scripts con lógica duplicada | 1 CLI + módulos reutilizables |
La refactorización ha permitido reducir la duplicación de lógica, aumentar la flexibilidad y mejorar la escalabilidad del código. 🚀

### Conclusión y Recomendaciones 📝
La refactorización del script `analista_yt.rb` ha demostrado ser una excelente oportunidad para mejorar la calidad y la mantenibilidad del código. Se recomienda aplicar este enfoque a otros scripts y proyectos para obtener beneficios similares. Además, se sugiere:
* Revisar y refactorizar regularmente el código para asegurarse de que se mantenga actualizado y eficiente.
* Utilizar principios de diseño de software y patrones de diseño para mejorar la estructura y la reutilización del código.
* Implementar pruebas unitarias y de integración para asegurarse de que el código funcione correctamente y sea fácil de mantener. 🚀

## Sistema de Rotación de API Keys Mejorado
### Introducción 📚
El sistema de rotación de API keys es fundamental para garantizar la disponibilidad y el rendimiento óptimo de las aplicaciones que dependen de servicios externos. En este contexto, se presenta una mejora significativa al sistema actual, diseñada para optimizar la rotación de claves y minimizar los errores, mejorando así la eficiencia y la confiabilidad de las aplicaciones.

### Estado Actual y Limitaciones 📊
El archivo `api_key_rotator.rb` actualmente soporta múltiples claves, pero presenta varias limitaciones clave:
* **Falta de Metadatos**: No hay información adicional que describa las capacidades específicas de cada clave, lo que complica la gestión y la rotación eficaz de las claves.
* **Rotación sin Criterio**: Si una clave falla, el sistema la rota sin considerar factores como la prioridad, el uso reciente, o las capacidades de la clave, lo que puede generar problemas de rendimiento y disponibilidad.

### Mejora Propuesta: `apis.json` Enriquecido 📈
Para abordar estas limitaciones, se propone enriquecer el archivo `apis.json` con metadatos adicionales para cada clave. Esto incluye detalles como:
* **Clave Encriptada**: La clave en sí, encriptada para garantizar la seguridad.
* **Modelos Soportados**: Los modelos específicos que cada clave puede manejar.
* **Límite de Tasa**: El límite de solicitudes por minuto para cada clave.
* **Prioridad**: La prioridad de cada clave, para determinar el orden de uso.

Un ejemplo de cómo se vería el archivo `apis.json` enriquecido:
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
Estos metadatos adicionales permiten una gestión más sofisticada y una rotación más inteligente de las claves.

### Rotación Inteligente 🔄
Se implementa un módulo `SmartRotator` que selecciona la clave más adecuada para cada modelo, considerando factores como la prioridad y el uso reciente de cada clave. El módulo `SmartRotator` se puede implementar de la siguiente manera:
```ruby
module GroqTool
  class SmartRotator
    def initialize(keys)
      @keys = keys
      @usage_count = Hash.new(0)
    end

    # Selecciona la clave más adecuada para un modelo específico
    def select_key_for_model(model_name)
      # Filtrar claves que soportan el modelo
      candidates = @keys.select { |k| k["models"].include?(model_name) }
      
      # Ordenar por prioridad y menos usadas
      candidates.min_by { |k| [k["priority"], @usage_count[k["encrypted_key"]]] }
    end

    # Ejecuta un bloque con la clave seleccionada y maneja la rotación
    def execute_with_rotation(model_name, &block)
      key = select_key_for_model(model_name)
      begin
        # Ejecutar el bloque con la clave seleccionada
        block.call(key)
      rescue StandardError => e
        # Manejar errores y rotar la clave si es necesario
        if e.message.include?("429")
          # Incrementar el contador de uso para la clave actual
          @usage_count[key["encrypted_key"]] += 1
          # Seleccionar una nueva clave y reintentar
          retry
        else
          # Manejar otros errores
          raise e
        end
      end
    end
  end
end
```
Este enfoque garantiza una rotación más eficiente y reduce significativamente el riesgo de errores.

### Beneficios y Ventajas 📈
La implementación de este sistema de rotación de API keys mejorado ofrece varios beneficios clave, incluyendo:
* **Mayor Disponibilidad y Rendimiento**: Las aplicaciones pueden funcionar de manera más estable y eficiente, gracias a la rotación inteligente de claves.
* **Reducción de Errores**: La gestión sofisticada de claves y la rotación basada en prioridad y uso reducen los errores de conectividad y los problemas de rendimiento.
* **Mejora en la Gestión de Claves y Metadatos**: El sistema permite una gestión más efectiva de las claves y sus metadatos, facilitando la escalabilidad y la adaptabilidad.
* **Flexibilidad y Escalabilidad**: El sistema está diseñado para adaptarse a necesidades cambiantes, permitiendo la incorporación de nuevas claves y modelos de manera eficiente.

### Implementación y Pruebas 🚀
Para implementar este sistema, se deben seguir los siguientes pasos:
1. **Actualizar el Archivo `apis.json`**: Agregar los metadatos necesarios para cada clave.
2. **Implementar el Módulo `SmartRotator`**: Desarrollar el módulo según las especificaciones proporcionadas.
3. **Integrar con la Aplicación**: Incorporar el módulo `SmartRotator` en la aplicación para manejar la rotación de claves.
4. **Pruebas y Validación**: Realizar pruebas exhaustivas para validar el funcionamiento correcto del sistema y su impacto en la disponibilidad y el rendimiento de la aplicación.

Al seguir estos pasos y implementar el sistema de rotación de API keys mejorado, las aplicaciones pueden beneficiarse de una mayor eficiencia, escalabilidad y confiabilidad.

## Plan de Implementación
El plan de implementación se divide en cuatro fases, cada una con objetivos y tareas específicas 📅. A continuación, se presentan las fases y sus respectivas tareas.

### Fase 1: Fundamentos (Semana 1) 📚
En esta fase, se crean los fundamentos del proyecto. Las tareas incluyen:
* Crear `lib/model_router.rb` con catálogo de modelos
* Crear `client/groq_unified_client.rb`
* Actualizar `api_key_rotator.rb` para soportar metadata

### Fase 2: Caso Piloto (Semana 2) 🚀
En esta fase, se implementa un caso piloto para probar la funcionalidad del proyecto. Las tareas incluyen:
* Refactorizar `analista_yt.rb` como primer caso de éxito
* Mantener script viejo como fallback (`analista_yt_legacy.rb`)
* Documentar: "De X a Y" migration guide 📄

### Fase 3: Expansión (Semana 3-4) 📈
En esta fase, se expande la funcionalidad del proyecto. Las tareas incluyen:
* Refactorizar `genera_subs_v1.0.rb` (usa 2 modelos: Whisper + Llama)
* Refactorizar `desgrabar_mp3.rb`
* Unificar CLI (`bin/groq`)

### Fase 4: Pulido (Semana 5) 💫
En esta fase, se pulen los detalles del proyecto. Las tareas incluyen:
* Crear README con matriz de decisión 📝
* Implementar tests de enrutamiento 🚧
* Deprecar scripts viejos ⚠️

### Resumen del Plan de Implementación
El siguiente resumen muestra las fases y tareas del plan de implementación:
| Fase | Semana | Tareas |
| --- | --- | --- |
| Fundamentos | 1 | Crear `lib/model_router.rb`, `client/groq_unified_client.rb`, actualizar `api_key_rotator.rb` |
| Caso Piloto | 2 | Refactorizar `analista_yt.rb`, mantener script viejo, documentar migration guide |
| Expansión | 3-4 | Refactorizar `genera_subs_v1.0.rb`, `desgrabar_mp3.rb`, unificar CLI |
| Pulido | 5 | Crear README, implementar tests de enrutamiento, deprecar scripts viejos |

Este plan de implementación proporciona una visión general clara de las tareas y objetivos para cada fase del proyecto 📈. Al seguir este plan, se puede asegurar que el proyecto se complete de manera eficiente y efectiva 💼.

## Ejemplo de Uso Futuro
En esta sección, se presenta un ejemplo detallado de cómo utilizar la herramienta `groq` para realizar diferentes tareas de procesamiento de lenguaje natural de manera efectiva y eficiente 🤖.

### Configuración Inicial 📈
Antes de comenzar a utilizar la herramienta, es necesario configurar la clave de API y agregar los modelos de lenguaje que se desean utilizar. Esto se puede hacer ejecutando el siguiente comando en la terminal:
```bash
cd tools/groq_tool
./api_key_rotator.rb add <KEY> groq_main --models llama-3.3-70b,whisper-large-v3
```
Es importante recordar que la configuración inicial es crucial para el funcionamiento adecuado de la herramienta.

### Tareas de Procesamiento de Lenguaje Natural 📊
A continuación, se presentan algunos ejemplos de cómo utilizar la herramienta `groq` para realizar diferentes tareas de procesamiento de lenguaje natural.

#### Transcripción de Audio 🎧
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

#### Resumen de Texto 📄
Para resumir la transcripción anterior, se puede ejecutar el siguiente comando:
```bash
$ groq ask "Resume la transcripción de arriba"
```
La herramienta detectará la tarea como `SUMMARIZE` y seleccionará el modelo `llama-3.3-70b-versatile`. El resultado se guardará en un archivo de texto en la carpeta `tmp/resumenes`.

🎯 Tarea detectada: SUMMARIZE  
🤖 Modelo seleccionado: llama-3.3-70b-versatile
📊 Rate limit: 30/min (key: groq_main)
✅ Resumen guardado: tmp/resumenes/reunion_20260411_resumen.md

#### Escritura Creativa 📝
Para generar un email formal invitando a un evento, se puede ejecutar el siguiente comando:
```bash
$ groq ask "Escribe un email formal invitando al evento X"
```
La herramienta detectará la tarea como `CREATIVE_WRITE` y seleccionará el modelo `llama-3.1-8b-instant` con una temperatura de 0.8. El resultado se guardará en un archivo de texto en la carpeta `tmp/output`.

🎯 Tarea detectada: CREATIVE_WRITE
🤖 Modelo seleccionado: llama-3.1-8b-instant (temperature: 0.8)
📊 Rate limit: 60/min (key: groq_backup)
✅ Email generado: tmp/output/email_20260411.md

### Conclusión y Recomendaciones 📚
La herramienta `groq` ofrece una amplia gama de posibilidades para el procesamiento de lenguaje natural. Es importante explorar las diferentes opciones y modelos disponibles para encontrar el mejor enfoque para cada tarea específica. Además, es recomendable revisar regularmente los límites de velocidad y las claves de API para asegurarse de que la herramienta funcione de manera óptima. 🚀

## Métricas de Éxito 📊
Las métricas de éxito son fundamentales para evaluar el rendimiento y la eficiencia de nuestro sistema. A continuación, se presentan las métricas clave antes y después de la implementación de los cambios objetivo.

### Resumen de Métricas
| Métrica | Valor Anterior | Valor Objetivo | Descripción |
|---------|---------------|----------------|-------------|
| Modelos hardcoded | 7 scripts con 1 modelo cada uno | 0 (todos dinámicos) | Reducción de modelos fijos para mejorar la flexibilidad |
| Flexibilidad para cambiar modelo | Edición de 7 archivos | Edición de 1 hash (`TASK_PROFILES`) | Simplificación del proceso de edición de modelos |
| Keys API sin metadata | Todas iguales | Priorizadas por modelo/tarea | Mejora en la organización y acceso a la información |
| Usuario elige modelo | Sí (manual) | Opcional (auto-sugerido) | Agregado de capacidad de auto-sugerir el modelo óptimo |
| Tasa de éxito en 1er intento | ~70% (puede fallar por modelo equivocado) | ~95% (modelo óptimo por defecto) | Incremento significativo en la eficiencia y precisión del sistema |

### Análisis de las Métricas
* **Modelos hardcoded**: La reducción de 7 a 0 modelos hardcoded indica una mayor flexibilidad y capacidad de adaptación del sistema, lo que permite una mejor respuesta a los cambios en el entorno 🌟.
* **Flexibilidad para cambiar modelo**: La simplificación del proceso de edición, pasando de 7 archivos a solo 1 hash (`TASK_PROFILES`), reduce el tiempo y el esfuerzo necesario para realizar cambios en el sistema 🕒.
* **Keys API sin metadata**: La priorización de las keys API por modelo y tarea mejora la organización y el acceso a la información, lo que facilita la gestión y el mantenimiento del sistema 📈.
* **Usuario elige modelo**: La opción de selección manual se mantiene, pero se agrega la capacidad de auto-sugerir el modelo óptimo, lo que mejora la experiencia del usuario y reduce el error humano 🤖.
* **Tasa de éxito en 1er intento**: El incremento significativo en la tasa de éxito en el primer intento, pasando de ~70% a ~95%, indica una mayor eficiencia y precisión del sistema, lo que reduce el tiempo y el esfuerzo necesario para lograr los objetivos 🚀.

### Conclusiones
En resumen, las métricas de éxito muestran un progreso significativo en la flexibilidad, eficiencia y precisión del sistema. La implementación de los cambios objetivo ha permitido mejorar la capacidad de adaptación, la organización y el acceso a la información, lo que se traduce en una mejor experiencia del usuario y una mayor eficiencia en el logro de los objetivos 📈.

## Riesgos 🚨
Los riesgos potenciales en el proyecto se presentan a continuación, junto con las estrategias de mitigación implementadas para minimizar su impacto. Es fundamental identificar y abordar estos riesgos para garantizar el éxito del proyecto.

### Tipos de Riesgos
A continuación, se detallan los riesgos identificados y las medidas adoptadas para mitigarlos:

| Riesgo | Descripción | Estrategia de Mitigación |
|--------|-------------|-------------------------|
| **Cambios en nombres de modelos** 📝 | Groq cambia nombres de modelos, lo que podría generar inconsistencias en la configuración | Utilizar `TASK_PROFILES` como un hash centralizado, que se actualiza en un solo lugar, garantiza la consistencia y facilita la gestión de cambios. |
| **Necesidad de control manual** 🖥️ | El usuario puede requerir control manual sobre la selección de modelos para ajustar a necesidades específicas | La interfaz de línea de comandos (CLI) acepta el parámetro `--model <nombre>` como override, permitiendo a los usuarios un control manual cuando sea necesario. |
| **Aumento de complejidad** 🤔 | La complejidad aumentada en la configuración y uso del sistema puede generar dificultades para los usuarios | Se proporciona documentación clara y ejemplos de uso para ayudar a los usuarios a entender y manejar el sistema de manera efectiva, reduciendo la curva de aprendizaje y el riesgo de errores. |

### Implementación de Estrategias de Mitigación
Para implementar estas estrategias de mitigación, se deben seguir los siguientes pasos:

1. **Configuración de TASK_PROFILES**: Asegurarse de que `TASK_PROFILES` esté configurado correctamente y se actualice en un solo lugar para mantener la consistencia.
2. **Uso de la CLI**: Familiarizarse con la interfaz de línea de comandos (CLI) y su parámetro `--model <nombre>` para permitir el control manual cuando sea necesario.
3. **Documentación y Ejemplos**: Revisar la documentación proporcionada y los ejemplos de uso para entender cómo manejar el sistema de manera efectiva y reducir la complejidad.

Al seguir estos pasos y implementar las estrategias de mitigación, se puede minimizar el impacto de los riesgos potenciales y garantizar el éxito del proyecto 🚀.

## Decisión Clave 📊
### Selección del Caso Piloto

Debemos tomar una decisión crucial sobre el caso piloto que iniciará nuestro proyecto. Las opciones son `analista_yt.rb` y `genera_subs_v1.0.rb`. A continuación, se presentan los criterios de evaluación para cada opción:

| Criterio | analista_yt.rb | genera_subs_v1.0.rb |
|----------|----------------|---------------------|
| **Complejidad** | Alta (3 modelos: download + whisper + llama) | Media (2 modelos: whisper + llama) |
| **Frecuencia de uso** | Baja (reuniones) | Media (Vía Crucis activo) |
| **Impacto visible** | Alto (demo impresionante) | Alto (pipeline core) |
| **Recomendación** | ✅ **Elegir este** como caso piloto | Dejar para Fase 3 |

### Justificación de la Recomendación 🤔

La elección de `analista_yt.rb` como caso piloto se basa en varias razones clave:
1. **Variedad de tecnologías**: Incorpora una variedad de tecnologías, incluyendo:
   * **Whisper** (audio → texto): permite la transcripción de audio a texto.
   * **Llama-3.3-70b** (razonamiento complejo): permite el análisis y procesamiento de información compleja.
   * **Llama-3.1-8b** (tarea rápida de formateo): permite la realización de tareas de formateo de manera rápida y eficiente.
2. **Demostración de enrutamiento inteligente**: Es ideal para mostrar el enrutamiento inteligente y la orquestación de modelos LLM según la tarea.
3. **Impacto visible**: Ofrece un alto impacto visible, lo que lo hace ideal para una demostración impresionante.

### Información Adicional 📝
* **Fecha de generación:** 2026-04-11
* **Enfoque:** Orquestación inteligente de modelos LLM según tarea
* **Consideraciones futuras**: Dejar `genera_subs_v1.0.rb` para la Fase 3, permitiendo una implementación escalonada y una evaluación continua del proyecto.