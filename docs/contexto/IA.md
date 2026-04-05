# Contexto del Proyecto — ViaCrucis BY2026

> Última actualización: 2026-04-05

## Descripción
**Proyecto**: Sistema de Audios Vía Crucis del Barrio Yacampiz (VCBY)
**Año**: 2026
**Versión Actual**: `26.8.24`
Aplicación web PHP para la gestión y reproducción de audios del Via Crucis del Barrio Yacampiz (2026). Permite listar, reproducir y compartir por WhatsApp los tracks de audio de la representación. Desplegada en un servidor NGINX propio con HTTPS.

## URL Pública

- **Sitio**: `https://rmonla.duckdns.org/vcby/`
- **Repositorio**: `ricardomonla/assjdm-ViaCrusis`

## ⚠️ Reglas Críticas de Deploy

> **El sistema está en PRODUCCIÓN.** El deploy es automático tras cada `git push`.

1. **Cada `git push` DEBE ir precedido de un bump de versión** en `incs/versionLogs.php`
2. Formato de versión: `26.8.XX` (año.plan.incremento)
3. Nunca hacer push sin actualizar el versionLogs
4. Commits de solo documentación (sin código) pueden omitir el bump si no afectan el sitio

## Normas de Interfaz (Frontend y Herramientas UI)

- **Estética Global**: Colores cálidos (paleta terrosa: beige, marrón, #d2c4a8, #c4b494). Tipografía legible (Georgia, serif).
- **Formatos Ligeros**: Vanilla JS y CSS Grid/Flexbox sin frameworks pesados.
- **Jerarquización del Audio/Guion**: Grupos según primer dígito (0=Desfile, 1=Pasión, 2=Calvario, 3=Crucifixión, 4=Resurrección). Usar `<details>` o acordeones.
- **Edición Rápida (In-Place)**: `contenteditable` y escucha JS en vez de formularios.
- **Testing con curl**: Siempre `curl` como primera herramienta de diagnóstico.

## Infraestructura de Hosting

| Atributo | Valor |
|:---|:---|
| **Servidor** | `srvv-nginx-rm` (LXC VMID 116, unprivileged) |
| **Host** | `srv-pmox3` (Proxmox, 10.0.10.203) |
| **IP Pública** | `190.114.205.17` |
| **DNS** | `rmonla.duckdns.org` |
| **SO** | Debian 12 (Bookworm) |
| **NGINX** | 1.22.1 (puertos 80/443) |
| **PHP** | 8.2-FPM |
| **HTTPS** | Let's Encrypt (certbot), expira 2026-06-26 |
| **SSH** | Puerto 7022, usuario root |
| **Deploy** | Deploy key SSH — webhook GitHub → `deploy.php` → `git pull` (~2s) |
| **Base de Datos** | SQLite 3 (`/var/www/vcby-data/vcby.db`, fuera del repo) |
| **PHP-SQLite** | `php8.2-sqlite3` + PDO |

> Ficha completa del nodo en: `docs/hosting/srvv-nginx-rm.md`

## Estructura del Proyecto

```
assjdm-ViaCrusis/
├── audios/            # Módulo de Reproducción
│   ├── index.php      # Página principal — lista de audios + link a Personajes
│   ├── play.php       # Reproductor (inyecta __cueData + __characters inline)
│   ├── api_cues.php   # API REST: cues desde SQLite (fallback)
│   ├── save_changes.php # Escritura SQLite + auto-regenera guion_completo.json
│   └── media/         # 34 archivos MP3 (000-403)
├── casting/           # Módulo de Personajes/Casting
│   ├── index.php      # Listado de personajes + postulación
│   └── api.php        # API REST: signup, toggle, delete
├── data/
│   └── db.php         # Capa CRUD SQLite (getCues, updateCue, insertCue, casting)
├── serve.php          # Servidor de archivos MP3 (seguridad + range requests)
├── start_termux.sh    # Auto-arranque Android: git pull + curl JSON + PHP server
├── css/
│   └── style.css      # Estilos principales (modo claro/oscuro, responsive)
├── incs/
│   ├── elementos.php  # Estructura centralizada (Grupos y Metadatos de tracks)
│   ├── header.php     # Cabecera HTML
│   ├── footer.php     # Pie de página + Modal de Perfiles
│   ├── functions.php  # Funciones auxiliares (getAudioFiles, getBaseURL)
│   ├── versionLogs.php # ⚠️ Historial de versiones — ACTUALIZAR ANTES DE PUSH
│   └── kerberos.php   # Protección de directorio
├── jss/
│   ├── js.js          # JavaScript (autoplay, navegación)
│   ├── karaoke.js     # Motor de karaoke (subtítulos sincronizados + Director tools)
│   ├── modal.js       # Sistema de modales inline (vcbyAlert, vcbyPrompt, vcbyInsertCue)
│   └── perfiles.js    # Gestión de perfiles Público/Director (localStorage + TTL)
├── tools/             # Scripts de mantenimiento e IA
│   ├── migrate_to_sqlite.php
│   ├── export_sqlite_to_json.php
│   ├── api_key_rotator/
│   ├── compilar_json_v4.py
│   └── renamer.py
└── docs/
    ├── hosting/       # Fichas de nodo
    ├── contexto/      # Este archivo
    └── plan/          # Planes de desarrollo (activo: 08)
```

## Sistema de Perfiles

| Concepto | Detalle |
|:---|:---|
| **Detección** | `localStorage` clave `vcby_perfil` (NO sesión PHP) |
| **Clave Director** | `VCBY2026` |
| **TTL** | 60 minutos |
| **Archivo** | `jss/perfiles.js` |
| **CSS classes** | `director-mode`, `admin-mode` en `<body>` |
| **Elementos** | `.director-only` se muestran/ocultan por JS |

## Funcionalidades

1. **Lista de audios** — 34 tracks ordenados por número (000-403), agrupados
2. **Reproductor Karaoke** — Subtítulos sincronizados con burbujas agrupadas por personaje
3. **Compartir WhatsApp** — Botón en cada track
4. **Modo Director** — Edición in-place (doble-click), marcaje de tiempos (HH:MM:SS), CRUD completo:
   - `+` inserta genérico (burbuja P00 "NUEVO PERSONAJE" / línea "(nuevo diálogo)")
   - Doble-click en nombre → cambiar personaje del grupo
   - Doble-click en texto → editar inline
   - 🗑 eliminar líneas individuales
   - ⏱ toggle marcas de tiempo con nudge ◂▸
   - 🎯 stamp (fijar tiempo al audio actual)
5. **Personajes (Casting)** — Página `/casting/` con:
   - Listado de personajes + synopsis desde SQLite
   - Postulación pública (nombre, apellido, teléfono)
   - Teléfonos privados (solo Director)
   - Toggle habilitar/deshabilitar por personaje
6. **Modo oscuro** — Automático vía `prefers-color-scheme`
7. **Responsive** — Diseño adaptable a móvil/tablet/desktop

## Sincronización Offline (Android)

- **Flujo automático**: Director edita → `save_changes.php` guarda en SQLite + regenera JSON → celular descarga al arrancar via `curl`
- **Sin intervención manual**: No requiere commit/push para sincronizar el guion
- **Fallback**: Sin internet, usa copia local del JSON

## Versión Actual

- **Versión:** `26.8.24`
- **Ambiente:** Desarrollo Local sincronizado con Producción (`srv-pmox3`)
- **Estado:** Fases 4-9 completadas. Fase 1-2 pendientes de auditoría manual.
- **Plan activo:** 👉 `docs/plan/08_plan-de-pulido.md`

## Pendientes Globales

- [x] Deploy automático (Webhook GitHub) → Plan 01 ✅
- [x] Consistencia visual → Plan 02 ✅
- [x] Mover Sitio a `/audios/` → Plan 05 ✅
- [x] Entorno Android Offline → Plan 07 ✅
- [ ] Pulido Fino Escénico → Plan 08 ⏳ (85%)
- [ ] Seguridad: fail2ban + headers NGINX
- [ ] Backup: vzdump automático del LXC

## Deploy Automático

- **Método**: GitHub Webhook → `deploy.php` → `git pull`
- **Tiempo**: ~2 seg tras cada `git push` a `main`
- **Log**: `/var/log/vcby-deploy.log`
