# Contexto del Proyecto — ViaCrucis BY2026

> Última actualización: 2026-04-04

## Descripción
**Proyecto**: Sistema de Audios Vía Crucis del Barrio Yacampiz (VCBY)
**Año**: 2026
**Versiones Actuales**: `26.8.22` (Auto-Sync SQLite→JSON + Android Fix)
Aplicación web PHP para la gestión y reproducción de audios del Via Crucis del Barrio Yacampiz (2026). Permite listar, reproducir y compartir por WhatsApp los tracks de audio de la representación. Desplegada en un servidor NGINX propio con HTTPS.

## URL Pública

- **Sitio**: `https://rmonla.duckdns.org/vcby/`
- **Repositorio**: `ricardomonla/assjdm-ViaCrusis`

## 3. Normas de Interfaz (Frontend y Herramientas UI)

- **Estética Global**: El sitio web principal y los paneles paralelos deben mantener coherencia visual (Colores cálidos, tipografía legible).
- **Formatos Ligeros**: Priorizar Vanilla Script y CSS Grid/Flexbox sin anclarse estrictamente a compilaciones pesadas para herramientas internas. El uso de `Tailwind CDN` se permite para herramientas veloces.
- **Jerarquización del Audio/Guion**: Los audios o bloques de guion se estructuran en Grupos según su primer dígito (0 = Desfile, 1 = La Pasión, 2 = Calvario, 3 = Crucifixión, 4 = Resurrección). Siempre usar la etiqueta nativa `<details>` o acordeones para evitar el cansancio visual masivo.
- **Edición Rápida (In-Place)**: Aplicaciones de texto intensivo usan atributos `contenteditable` y escucha vía JavaScript en lugar de formularios tradicionales si es posible.
- **Versionado Múltiple y Aislado**: Los sub-sitios internos (como `/guion`) deben manejar su propia bitácora de versiones cronológicas (`incs/versionLogs.php` local), evolucionando su semántica independientemente del sitio principal para proteger la estabilidad.
- **Testing con curl**: Siempre usar `curl` como primera herramienta de diagnóstico y prueba antes de recurrir al navegador. Es más rápido, eficiente y evita problemas de caché del browser.

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
| **Deploy** | Deploy key SSH desde GitHub |
| **Base de Datos** | SQLite 3 (`/var/www/vcby-data/vcby.db`, fuera del repo) |
| **PHP-SQLite** | `php8.2-sqlite3` + PDO |

> Ficha completa del nodo en: `docs/hosting/srvv-nginx-rm.md`

## Estructura del Proyecto

```
assjdm-ViaCrusis/
├── audios/            # Módulo de Reproducción
│   ├── index.php      # Página principal — lista de audios
│   ├── play.php       # Reproductor de audio individual (inyecta __cueData + __characters inline)
│   ├── api_cues.php   # API REST: cues desde SQLite (fallback)
│   ├── save_changes.php # Escritura SQLite + auto-regenera guion_completo.json
│   └── media/         # 34 archivos MP3 (000-403)
├── data/
│   └── db.php         # Capa CRUD SQLite (getCues, updateCue, insertCue)
├── serve.php          # Servidor de archivos MP3 (seguridad + range requests)
├── start_termux.sh    # Auto-arranque Android: git pull + curl JSON + PHP server
├── css/
│   ├── style.css      # Estilos principales (modo claro/oscuro, responsive)
│   └── index.php      # Protección de directorio
├── incs/
│   ├── elementos.php  # Estructura centralizada (Grupos y Metadatos de tracks)
│   ├── header.php     # Cabecera HTML
│   ├── footer.php     # Pie de página + Modal de Perfiles (Público/Director)
│   ├── functions.php  # Funciones auxiliares (getAudioFiles, getBaseURL)
│   ├── versionLogs.php # Historial de versiones
│   └── kerberos.php   # Protección de directorio
├── jss/
│   ├── js.js          # JavaScript (autoplay, navegación)
│   ├── karaoke.js     # Motor de karaoke (subtítulos sincronizados + Director tools)
│   ├── modal.js       # Sistema de modales inline (vcbyAlert, vcbyPrompt, vcbyInsertCue)
│   └── perfiles.js    # Gestión de perfiles Público/Director (localStorage + TTL)
├── tools/             # Scripts de mantenimiento e Inteligencia Artificial
│   ├── migrate_to_sqlite.php    # Migración JSON → SQLite
│   ├── export_sqlite_to_json.php # Exportación SQLite → JSON (CLI + web)
│   ├── api_key_rotator/ # Gestor Ruby de encriptación (LLMs y candados)
│   ├── compilar_json_v4.py # Compilador v4.0.md → guion_completo.json (con IDP)
│   ├── etiquetar_personajes.py # IA local
│   ├── transcribir_groq.py     # Transcripción API
│   └── renamer.py
└── docs/
    ├── hosting/       # Fichas de nodo
    └── contexto/      # Este archivo
```

## Gestión Transversal de APIs y Seguridad (Candados)

El repositorio incorpora con un sistema global y modular para consumir APIs de inteligencia artificial (Groq) protegiendo las credenciales mediante encriptación local y un "Candado de sesión".

**Archivo principal**: `tools/api_key_rotator/api_key_rotator.rb`

**Características**:
1. **Encriptación AES-256**: Los tokens se guardan ofuscados en un archivo versionado `apis.json`. Solo se descifran en memoria derivados de una "Frase Secreta" que solo conoce el autor.
2. **Candado de Sesión Temporal**: Al ingresar la frase por primera vez, el sistema abre un "Candado" local (`.candado.key`, exluido en gitignore). Esto permite que el usuario y los scripts interactuen con la API sin requerir contraseña constantemente.
3. **Auto-cierre**: Por seguridad, el Candado expira y se auto-destruye **exactamente a los 60 minutos** (3600s) de su apertura.
4. **Rotación Automática (Rate Limits)**: Si un llamado LLM choca contra el límite _Rate Limit (HTTP 429)_, el script intercepta el error y cicla el prompt simultáneamente hacia la siguiente cuenta encriptada en la lista.

**Uso desde la Consola (CLI)**:
- Establecer una frase pista: `./api_key_rotator.rb set_hint "Mi pista"`
- Cifrar y agregar llave: `./api_key_rotator.rb add "API_KEY" "Nombre de Referencia"`
- Listar llaves seguras: `./api_key_rotator.rb list`
- Cerrar candado manual: `./api_key_rotator.rb lock`

Cualquier nuevo script Python o Node dentro del proyecto que requiera IA puede ser implementado delegando el payload por *STDIN* hacia este subsistema.

## Funcionalidades

1. **Lista de audios** — Muestra los 34 tracks ordenados por número (000-403)
2. **Reproductor** — Página individual por track con controles HTML5
3. **Compartir WhatsApp** — Botón en cada track para enviar enlace + título
4. **Acceso con clave** — `?key=VCV2026` habilita descarga y navegación entre tracks
5. **Modo público** — Sin clave: solo lista + reproducción + compartir WhatsApp
6. **Serve seguro** — `serve.php` oculta rutas físicas, valida formato, soporta range requests
7. **Modo oscuro** — Automático vía `prefers-color-scheme`
8. **Responsive** — Diseño adaptable a móvil/tablet/desktop

## Flujo de Trabajo de Transcripción (Workflow)

Para mantener la calidad y agilizar la integración de nuevas pistas de audio al JSON del Karaoke, el equipo respeta estrictamente el siguiente ciclo iterativo H.I.T.L. (Human-In-The-Loop):

1. **`v0.1.md` (Plantilla Base / Input IA):** El asistente de IA recibe (o genera) un archivo temporal con la estructura mínima y se apoya en esto para despachar el audio al modelo de voz a texto (Groq/Whisper).
2. **`v1.0.md` (Transcripción Cruda H.I.T.L.):** La IA devuelve los resultados con marcas de tiempo `[xxx.mm.ss.ms]` y asignaciones de personajes automáticas (`Pxx`). La IA **guarda este resultado crudo** en `audios/subs/xxx_v1.0.md` y pausa el proceso.
3. **Pausa y Edición Humana:** El desarrollador (Humano) edita manualmente el `v1.0.md` corrigiendo pisadas de audio, ajustando o reasignando las marcas temporales e identificadores (`Pxx`) que requieren contexto fílmico real. **(Carga de trabajo principal humana)**.
4. **`v1.1.md` (Refinado Filológico):** Una vez que el usuario indica que "ya está editado el archivo v1.0", el asistente de IA retoma el trabajo. Lee el `v1.0.md` validado, y le inyecta la **redacción gramatical y puntuación teatral perfecta** proveniente del guion maestro semiótico (`docs/Guion-vcby2026_Editado...md`). La IA lo guarda como `v1.1.md`.
5. **Compilación y Cierre (`guion_completo.json`):** Finalmente, la IA ejecuta el motor `tools/groq_tool/compilador_v1.1.rb` inyectando la nueva pista `v1.1.md` depurada, pulida y temporalmente precisa dentro del Karaoke interactivo y termina el ciclo.

## Versión Actual

- **Versión:** `26.8.22` (Auto-Sync SQLite→JSON + Android/Termux Fix)
- **Ambiente:** Desarrollo Local sincronizado con Producción (`srv-pmox3`)
- **Estado:** Fases 4-7 completadas. Fase 1-2 pendientes de auditoría manual.
- **Plan activo:** 👉 `docs/plan/08_plan-de-pulido.md`

## Sincronización Offline (Android)

- **Flujo automático**: Director edita → `save_changes.php` guarda en SQLite + regenera `guion_completo.json` → celular lo descarga al arrancar via `curl`
- **Sin intervención manual**: No requiere commit/push para sincronizar el guion
- **Fallback**: Si no hay internet, el celular usa la copia local del JSON

## Pendientes Globales

- [x] Deploy automático (Webhook GitHub) → Plan 01 ✅
- [x] Consistencia visual → Plan 02 ✅
- [x] Mover Sitio a `/audios/` → Plan 05 ✅
- [x] Entorno Android Offline → Plan 07 ✅
- [ ] Pulido Fino Escénico → Plan 08 ⏳
- [ ] Seguridad: fail2ban + headers NGINX
- [ ] Backup: vzdump automático del LXC

## Deploy Automático

- **Método**: GitHub Webhook → `deploy.php` → `git pull`
- **Tiempo**: ~2 seg tras cada `git push` a `main`
- **Log**: `/var/log/vcby-deploy.log`
