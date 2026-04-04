# Plan 07: Entorno de Trabajo Móvil (Android Offline)

> **Estado**: ✅ Completado
> **Fecha**: 2026-04-02 (inicial) / 2026-04-04 (auto-sync)

---

## Progreso General

```text
██████████████████████████████ 100% — COMPLETADO
```

| Fase | Descripción | Estado |
|:---|:---|:---|
| 1 | Investigación de viabilidad Docker en Android | ✅ |
| 2 | Configuración del entorno base | ✅ |
| 3 | Sincronización offline y despliegue del sistema VCBY | ✅ |
| 4 | Auto-sync SQLite→JSON y compatibilidad post-refactoring | ✅ |

---

## Objetivo

Crear un entorno en el celular (sistema Android) que permita ejecutar y trabajar sobre este sistema (VCBY u otras herramientas asociadas como Docker) de forma totalmente offline, independientemente de tener conectividad a internet. Basado en exploraciones previas de alternativas a Termux, udocker o virtualización de recursos en Android.

---

## Fase 1: Investigación de viabilidad Docker en Android

```text
██████████████████████████████ 100%
```

- [x] Revisar conclusiones y viabilidad de udocker u otras alternativas en dispositivos sin root.
- [x] Analizar requisitos de virtualización o proot para el proyecto web PHP/NGINX.
- [x] Definir el stack tecnológico a instalar (PHP nativo sin Docker).

**Notas/Hallazgos**:
- **Docker en Android**: No es posible correr el demonio oficial de Docker de forma nativa en un dispositivo Android sin acceso root (requiere acceso a cgroups y namespaces del kernel).
- **Alternativas**: Las opciones como emular Alpine Linux con QEMU consumen demasiada batería y recursos, mientras que `udocker` a veces tiene problemas de red y puertos.
- **Conclusión**: Analizando el `docker-compose.yml`, el proyecto sólo necesita levantar una imagen `php:apache`. Por lo tanto, ¡**NO necesitamos Docker en absoluto**! Todo esto se puede lograr instalando el paquete nativo de `php` directo en Termux y levantando el servidor embebido `php -S 0.0.0.0:8080`. Esto gastará poquísima batería y es 100% veloz.

---

## Fase 2: Configuración del entorno base

```text
██████████████████████████████ 100%
```

- [x] Instalar la plataforma elegida (Termux + repos, u otro entorno de virtualización).
- [x] Configurar el entorno (shell, git, permisos de almacenamiento local).
- [x] Configurar el servidor web / PHP interno en el móvil o el runtime contenedor.

**Notas/Hallazgos**:

---

## Fase 3: Sincronización offline y despliegue del sistema VCBY

```text
██████████████████████████████ 100%
```

- [x] Clonar el repositorio `assjdm-ViaCrusis` para uso offline.
- [x] Configurar el Nginx/PHP local del móvil para apuntar a la carpeta del proyecto.
- [x] Realizar pruebas de acceso a `localhost` desde el navegador del celular y confirmar funcionamiento sin internet.
- [x] Cambiar la visibilidad del repositorio en GitHub a **Público** para facilitar la descarga sin trabas de redes o SSH.
- [x] Configurar la URL remota estándar pura (`https`) para bajada (pull) directa en la terminal de Android.

**Notas/Hallazgos**:
- El servidor `php -S 127.0.0.1:8080` resultó ideal. Se accede correctamente por el navegador.
- Se experimentaron bloqueos agresivos de puertos y filtros SSH por parte del ISP celular (o Wi-Fi).
- **Decisión Final**: Puesto que el objetivo del teléfono es utilizar el sistema offline de forma práctica y consumir lo pre-generado, se pasó el repo a Público. Termux se conectará solo en modo lectura/descarga de actualizaciones (`pull`) mediante enlace limpio de HTTPS sin necesitar credenciales ni lidiar con cortafuegos.

---

## Fase 4: Auto-sync SQLite→JSON y compatibilidad Android

```text
██████████████████████████████ 100%
```

- [x] Fix: `play.php` — try/catch en SQLite para fallback a JSON cuando la BD no está disponible (Termux).
- [x] Fix: `js.js` — guard null en `autoplayMessage` (elemento removido del HTML).
- [x] Auto-sync: `save_changes.php` regenera `guion_completo.json` automáticamente tras cada edición del Director.
- [x] `start_termux.sh` descarga JSON fresco del servidor via `curl` al arrancar (independiente de git push).
- [x] Nuevo tool: `export_sqlite_to_json.php` (CLI + web) para exportación manual bajo demanda.

**Notas/Hallazgos**:
- **Problema detectado**: Tras la migración a SQLite (v26.8.15), `data/db.php` hardcodeaba la ruta `/var/www/vcby-data/vcby.db`. En Termux esa ruta no existe → `getCues()` lanzaba excepción PDO sin catch → HTTP 500, página en blanco.
- **Solución**: Try/catch permite fallback transparente al `guion_completo.json` estático.
- **Flujo automático final**: Director edita → SQLite + JSON auto-update → celular descarga al iniciar → funciona offline.
- **Sin intervención manual**: No requiere commit/push para sincronizar el guion al celular.

---

## Resumen de archivos creados/modificados

| Archivo | Ubicación | Estado |
|:---|:---|:---|
| `07_entorno-android.md` | `docs/plan/` | 📋 |
| `start_termux.sh` | `/` | ✅ Actualizado (paso curl JSON) |
| `play.php` | `audios/` | ✅ Fix try/catch SQLite |
| `js.js` | `jss/` | ✅ Fix autoplayMessage guard |
| `save_changes.php` | `audios/` | ✅ Auto-export JSON |
| `export_sqlite_to_json.php` | `tools/` | 🆕 Nuevo |
