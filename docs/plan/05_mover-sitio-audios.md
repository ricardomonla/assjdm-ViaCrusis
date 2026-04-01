# Plan 05: Mover Sitio de Audios

> **Estado**: ✅ Completado
> **Fecha**: 2026-04-01
> **Servidor**: srvv-nginx-rm

---

## Progreso General

```text
██████████████████████████████ 100% — COMPLETADO
```

| Fase | Descripción | Estado |
|:---|:---|:---|
| 1 | Creación del directorio y movimiento de archivos | ✅ |
| 2 | Centralización de definiciones (incs/elementos.php) | ✅ |
| 3 | Actualización de rutas y enlaces internos | ✅ |
| 4 | Ajustes de seguridad (kerberos) y streaming (serve.php) | ✅ |
| 5 | Crear índice en la raíz para redirigir/navegar | ✅ |

---

## Objetivo

Trasladar la aplicación principal de catálogo y reproducción de audios (actualmente en la raíz web) hacia un subdirectorio dedicado `/audios/`, reorganizando el esquema general del proyecto para dar espacio a otras secciones (como el guion). La reestructuración mantendrá la funcionalidad intacta, pero operando desde el nuevo directorio.

---

## Fase 1: Creación del directorio y movimiento de archivos

```text
██████████████████████████████ 100%
```

- [x] Crear el directorio `audios/` en la raíz del proyecto.
- [x] Mover `index.php` (Catálogo general) hacia `audios/index.php`.
- [x] Mover `play.php` (Reproductor de pista individual) a `audios/play.php`.
- [x] Mover el directorio `media/` de audio hacia `audios/media/`.
- [x] Analizar si los directorios `css`, `jss` e `incs` deben moverse a `/audios/` o dejarse en la raíz como recursos globales. En caso de aplicar, mover sus referencias.

**Notas/Hallazgos**:
Se determinó que `css`, `jss` e `incs` deben mantenerse en la raíz del proyecto como recursos globales. Esto simplificará la reutilización de código (estilos, funciones y definiciones) para las demás secciones que se vayan a construir como `/guion/`. El directorio `media` (que contiene los mp3) sí fue movido a `/audios/media/` ya que pertenece estrictamente al catálogo de audios.
(Evaluar la arquitectura de recursos globales o compartidos entre `/audios/` y futuras utilidades como `/guion/`).

---

## Fase 2: Centralización de definiciones (incs/elementos.php)

```text
██████████████████████████████ 100%
```

- [x] Crear `incs/elementos.php` para almacenar o generar la estructura de los audios (Grupos 0-Desfile, 1-La Pasión, etc.).
- [x] Extraer la lógica de definición de grupos de `incs/functions.php` y ubicarla en el nuevo archivo común.
- [x] Asegurarse de que este catálogo sirva de única fuente de verdad tanto para listar audios en`/audios/` como para referencias cruzadas en el desarrollo de `/guion/`.

**Notas/Hallazgos**:
El array estático que definía los grupos fue refactorizado y exportado a la función `getMediaGroupsStructure()` dentro del nuevo archivo `incs/elementos.php`. En `funcs.php` se importó esto usando `__DIR__` de forma segura.

---

## Fase 3: Actualización de rutas y enlaces internos

```text
██████████████████████████████ 100%
```

- [x] Corregir llamadas a `require` e `includes` en `audios/index.php` y `audios/play.php` (prefijadas con `../incs/`).
- [x] Actualizar URLs relativas referenciadas para los assets (`../css/style.css` y `../jss/js.js`).
- [x] Ajustar la lógica del botón "Compartir en WhatsApp" para que construya la URL absoluta apuntando correctamente a `rmonla.duckdns.org/vcby/audios/play.php`.
- [x] Actualizar las rutas devueltas u originadas hacia las pistas reproducibles (`../serve.php?file=...`).

**Notas/Hallazgos**:
En `incs/header.php`, el `<link rel="stylesheet">` ahora requiere una notación de `../css/...` al estar pensado para operar en subcarpetas modulares. Asimismo, los `src` de medios y los scripts ahora emplean directivas de un nivel superior. El botón de Whatsapp usa la variable `$baseURL` calculada por el server, la cual es agnóstica a la ruta, por lo que hereda el prefijo correcto de subdirectorios `/audios` automáticamente.

---

## Fase 4: Ajustes de seguridad (kerberos) y streaming (serve.php)

```text
██████████████████████████████ 100%
```

- [x] Mover o ajustar `serve.php` garantizando que siga sirviendo correctamente los audios de `media/` apuntando a `audios/media/`.
- [x] Revisar el archivo de administración o autenticación `kerberos.php` para asegurar que las variables y ruteos (`__DIR__`) operan con independencia del directorio de ejecución.
- [x] Validar que los candados (URL flag `?key=VCV2026`) operan con plena capacidad.
- [x] Comprobar el funcionamiento de la pulsación oculta bajo las nuevas rutas.

**Notas/Hallazgos**:
Se ha dejado `serve.php` en el root incrementando su robustez y apuntando internamente a `audios/media`. Respecto a `incs/kerberos.php`, se actualizaron los includes (header/footer) usando el método seguro `__DIR__` de PHP, previniendo errores de ubicación independientemente de si se invoca desde `css/index.php` o futuras sub-rutas. Asimismo reemplazamos el redirect huérfano a `error.php` por un bloque `die()` con código HTTP 403.

---

## Fase 5: Crear índice en la raíz para redirigir/navegar

```text
██████████████████████████████ 100%
```

- [x] Generar un nuevo `index.php` en la raíz.
- [x] El nuevo `index.php` base debe actuar como un menú de navegación que direccione a los módulos, con enlaces a `/audios/` y `/guion/`. (Alternativamente hacer una redirección a `/audios/` si el menú no es requerido para la audiencia general).
- [x] Validar flujos de trabajo en un dispositivo móvil.

**Notas/Hallazgos**:
Se optó por la "Opción B". El archivo `index.php` en el root consiste ahora únicamente en una re-dirección HTTP (`Location: audios/`) que salta directamente al módulo reestructurado, protegiendo del escrutinio general al futuro módulo `/guion/` hasta su debida inauguración.

---

## Resumen de archivos creados/modificados

| Archivo | Ubicación | Estado |
|:---|:---|:---|
| `05_mover-sitio-audios.md` | `docs/plan/` | ✅ |

## Seguridad

Es vital que durante la migración de archivos no queden expuestas rutas al directorio `media/`. El método por el cual `serve.php` despacha flujos debe mantenerse protegido y ocultar la ubicación local de los MP3.

## Rollback

Si ocurren errores fatales, se deberán deshacer los últimos cambios de Git moviendo `index.php` y `play.php` a la raíz del proyecto.
