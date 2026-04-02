# Flujo de Trabajo de Transcripción (H.I.T.L.)

Este documento describe el ciclo iterativo "Human-In-The-Loop" para la creación, corrección y sincronización de los subtítulos de las pistas de audio para el Karaoke del Vía Crucis.

El objetivo es combinar la potencia de la IA para transcripción y tiempos, la revisión humana para coherencia escénica, y el refinado filológico para emular perfectamente el libreto original.

## Paso 1: Extracción Inteligente Autónomo (`Script AI -> v1.0.md`)
*   **Base Teórica:** El catálogo universal de todos los personajes recae de manera centralizada en `00_Personajes.md`. No se requieren plantillas previas.
*   **Acción:** Se ejecuta el script automatizado (Ej: `ruby tools/groq_tool/genera_subs_v1.0.rb <Pista>`).
*   **Tecnología:** El audio es ingestido por *Whisper* obteniendo los tiempos. Luego, *LLaMA 3.3* lee la transcripción completa, consulta el archivo maestro `00_Personajes.md` global y devuelve la Tabla 1 de Personajes filtrada exclusivamente con aquellos que detectó que hablan, más la Tabla 2 con la transcripción base.
*   **Resultado:** Se genera automáticamente el archivo borrador `XXX_v1.0.md` en esta carpeta.

## Paso 2: Intervención y Validación Humana (`Humano -> v2.0.md`)
*   **Responsable:** Director humano del Vía Crucis.
*   **Acción:** Se abre el archivo `XXX_v1.0.md` generado en el paso anterior.
*   **Corrección:** El operador revisa meticulosamente el audio escuchando y ajustando los **personajes (IDP)** y los **cortes de tiempo**, validando que calcen a la perfección. Durante este paso la ortografía perfecta no es crucial, tan solo alinear la sincronización rítmica y la lógica.
*   **Resultado:** El humano guarda sus cambios **renombrando el archivo a `XXX_v2.0.md`**. Queda en sistema un bloque temporal validado 100% por el humano.

## Paso 3: Inyección Literaria y Refinado Filológico (`IA -> v3.0.md`)
*   **Responsable:** Asistente IA (Antigravity).
*   **Acción:** Tras recibir el archivo `v2.0.md` auditado por el Humano, el Asistente carga el archivo.
*   **Inyección:** Se cruza con el libreto maestro en la carpeta `docs` (`Guion-vcby2026_Editado...md`).
*   **Proceso:** El asistente inyecta las variaciones poéticas, puntuaciones, exclamaciones y la gramática sin desfasar ni los IDP que el humano determinó ni las marcas.
*   **Resultado:** El asistente renombra el archivo y genera la versión canónica definitiva: `XXX_v3.0.md`.

## Paso 4: Compilación a Json Maestro y UI (`Final -> v4.0.md`) 
*   **Responsable:** Asistente IA / Humano.
*   **Acción:** Se ejecuta el motor compilador adaptado a la nueva nomenclatura.
*   **Proceso:** Este script absorbe todos los archivos `_v3.0.md` listos, los renderiza y consolida en un único árbol. Una vez finalizado y empujado correctamente al JSON, el propio compilador los renombra marcando su inserción.
*   **Resultado:** El archivo se renombra a `XXX_v4.0.md` como estampa de que ya está en producción. Queda actualizado el registro maestro `audios/subs/guion_completo.json`. El Karaoke reflejará instantáneamente la pista.
