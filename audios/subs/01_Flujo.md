# Flujo de Trabajo de Transcripción (H.I.T.L. Optimizado)

Este documento describe la verdadera secuencia del ciclo iterativo "Human-In-The-Loop", pensado para la creación, corrección y sincronización milimétrica de subtítulos del Vía Crucis. 

Este flujo rediseñado aprovecha las capacidades puras de Groq para audición ciega (evitando sesgos), y a Antigravity (IA) como director de orquesta que vincula los libretos y catálogos, bajo la supervisión y pulso rítmico del Humano.

## Fase 1: Transcripción Ciega (Groq) y Contextualización (IA) -> `v1.0.md`
*   **Paso 1.1 (Audición Ciega por Groq):** El motor (`genera_subs_v1.0.rb`) procesa el audio puramente. Sin conocer información previa ni catálogos, detecta cuántas voces distintas participan, arma una tabla básica de Personajes/Sinopsis a ciegas, y lista los subtítulos con sus marcas de tiempo.
*   **Paso 1.2 (Análisis y Enlace por Antigravity):** La IA contextual (Antigravity) recibe ese borrador crudo de Groq, lo coteja meticulosamente contra el catálogo mayor (`00_Personajes.md`), reasigna los IDPs globales correctos a cada voz y, si hubo personajes incidentales que no existían, los añade al catálogo maestro.
*   **Resultado:** Antigravity devuelve el archivo **`XXX_v1.0.md`** limpio, enlazado correctamente al ecosistema del Vía Crucis, con los subtítulos y tiempos básicos.

## Fase 2: Control y Validación de Ritmo (Humano)
*   **Responsable:** Director Humano.
*   **Acción:** Revisa el archivo `XXX_v1.0.md`. Aquí se ajusta la precisión del corte del audio (las marcas de tiempo en corchetes) y asegura empíricamente que la IA no haya confundido un personaje con otro. En este paso la ortografía perfecta no es crucial; lo que importa es el ritmo y la exactitud de los tiempos e identidades.
*   **Resultado:** Al finalizar, el Humano guarda el archivo y notifica por el chat a la IA: "El archivo está listo para la Fase 3".

## Fase 3: Inyección Literaria y Refinado Filológico (`IA -> v2.0.md`)
*   **Responsable:** Asistente IA (Antigravity).
*   **Acción:** La IA toma el archivo auditado en Fase 2 y va a buscar la libreto canónico maestro (`docs/Guion-vcby2026_Editado...md`).
*   **Proceso:** Inyecta de lleno los dramáticos poéticos, puntuaciones perfectas, intenciones y oraciones literarias del guion real sin romper la sincro ni los identificadores de personajes fijados previamente por el Humano.
*   **Resultado:** La IA genera y entrega la versión **`XXX_v2.0.md`**.

## Fase 4: Re-Verificación Final (Humano)
*   **Responsable:** Director Humano.
*   **Acción:** Lee el texto del `XXX_v2.0.md` garantizando que todo encaje perfecta y fielmente (verificándose sintaxis visual o matices finales de la inyección filológica). 
*   **Resultado:** Conforma el OK definitivo de toda la edición textual para su pase a producción. Informa a la IA para ensamblado final.

## Fase 5: Consolidación y Commit (`Sistema -> JSON y v3.0.md`)
*   **Responsable:** Compilador automatizado / IA.
*   **Acción:** El archivo definitivo (validado en Fase 4) es absorbido por el motor (`compilador.rb`), inyectándose ordenadamente dentro de `audios/subs/guion_completo.json`. 
*   **Proceso:** Una vez sellado con éxito en la base de datos de producción (donde la aplicación de Karaoke interactúa), el archivo markdown queda con la etiqueta final de vida útil (Ej. **`XXX_v4.0.md`**). Se encadena el `git push`.
*   **Bucle Autónomo (Pre-carga):** Inmediatamente al finalizar el commit, la IA tiene como instrucción disparar automáticamente la **Fase 1** (la audición de Groq) sobre el audio correlativo siguiente. Así, mientras tú terminas una pista, el sistema ya deja listo el `v1.0.md` de la pista venidera en bandeja.
