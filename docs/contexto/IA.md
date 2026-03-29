# Contexto del Proyecto — ViaCrucis BY2026

> Última actualización: 2026-03-28

## Descripción General

Aplicación web PHP para la gestión y reproducción de audios del Via Crucis del Barrio Yacampiz (2026). Permite listar, reproducir y compartir por WhatsApp los tracks de audio de la representación. Desplegada en un servidor NGINX propio con HTTPS.

## URL Pública

- **Sitio**: `https://rmonla.duckdns.org/vcby/`
- **Repositorio**: `ricardomonla/assjdm-ViaCrusis`

## 3. Normas de Interfaz (Frontend y Herramientas UI)

- **Estética Global**: El sitio web principal y los paneles paralelos deben mantener coherencia visual (Colores cálidos, tipografía legible).
- **Formatos Ligeros**: Priorizar Vanilla Script y CSS Grid/Flexbox sin anclarse estrictamente a compilaciones pesadas para herramientas internas. El uso de `Tailwind CDN` se permite para herramientas veloces.
- **Jerarquización del Audio/Guion**: Los audios o bloques de guion se estructuran en Grupos según su primer dígito (0 = Desfile, 1 = La Pasión, 2 = Calvario, 3 = Crucifixión, 4 = Resurrección). Siempre usar la etiqueta nativa `<details>` o acordeones para evitar el cansancio visual masivo.
- **Edición Rápida (In-Place)**: Aplicaciones de texto intensivo usan atributos `contenteditable` y escucha vía JavaScript en lugar de formularios tradicionales si es posible.

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

> Ficha completa del nodo en: `docs/hosting/srvv-nginx-rm.md`

## Estructura del Proyecto

```
assjdm-ViaCrusis/
├── index.php          # Página principal — lista de audios
├── play.php           # Reproductor de audio individual
├── serve.php          # Servidor de archivos MP3 (seguridad + range requests)
├── docker-compose.yml # Config Docker (entorno local)
├── css/
│   ├── style.css      # Estilos principales (modo claro/oscuro, responsive)
│   └── index.php      # Protección de directorio
├── incs/
│   ├── header.php     # Cabecera HTML
│   ├── footer.php     # Pie de página (versión)
│   ├── functions.php  # Funciones auxiliares (getAudioFiles, getBaseURL)
│   ├── versionLogs.php # Historial de versiones
│   └── kerberos.php   # Protección de directorio
├── jss/
│   └── js.js          # JavaScript (autoplay, navegación)
├── media/             # 34 archivos MP3 (000-403)
├── tools/             # Scripts de mantenimiento e Inteligencia Artificial
│   ├── api_key_rotator/ # Gestor Ruby de encriptación (LLMs y candados)
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

## Versión Actual

- **Versión**: 26.4 (2026-03-28)
- **Cambios**: Fix visual cross-browser, botón admin oculto (5 taps), sesión 30 min

## Estado del Sitio (2026-03-28)

| Aspecto | Estado |
|:---|:---|
| HTTPS activo | ✅ |
| Lista de 34 audios | ✅ |
| Compartir WhatsApp | ✅ |
| Diseño responsive | ✅ |
| PHP-FPM operativo | ✅ |
| Redirect HTTP→HTTPS | ✅ |

## Acceso al Servidor (Hallazgos)

> **Importante**: SSH directo al LXC con password **no funciona** desde fuera del host Proxmox.

| Método | Funciona | Comando |
|:---|:---|:---|
| SSH directo (password) | ❌ | `ssh -p 7022 root@190.114.205.17` — "Permission denied" |
| SSH directo (LAN) | ❌ | `ssh -p 7022 root@10.0.10.117` — "Permission denied" |
| Consola Proxmox (web) | ✅ | Desde la interfaz web de Proxmox, contraseña `UTNlarioja00WEB` |
| **Relay vía srv-pmox3** | ✅ | `ssh root@10.0.10.203 'pct exec 116 -- bash -c "COMANDO"'` |

**Método operativo recomendado (IA/scripts):**
```bash
# Acceso con clave pública a srv-pmox3, luego pct exec al LXC
ssh root@10.0.10.203 'pct exec 116 -- bash -c "cd /var/www/vcby && COMANDO"'
```

> `srv-pmox3` (10.0.10.203) acepta clave pública SSH sin contraseña.

## Deploy Automático

- **Método**: GitHub Webhook → `deploy.php` → `git pull`
- **Tiempo**: ~2 seg tras cada `git push` a `main`
- **Log**: `/var/log/vcby-deploy.log` en el servidor
- **Plan**: `docs/plan/01_webhook-deploy-automatico.md`

## Pendientes

- [x] ~~**Deploy automático**: Webhook GitHub~~ → Plan 01 ✅
- [x] ~~**Consistencia visual**: Fix cross-browser + admin oculto~~ → Plan 02 ✅
- [ ] **Guion Transcrito**: Identificar y formatear las transcripciones de 34 archivos → Plan 03 ⏳
- [ ] **Seguridad**: Configurar fail2ban y headers de seguridad en NGINX
- [ ] **Backup**: Configurar vzdump para respaldos automáticos del LXC
- [ ] **Monitorización**: Integrar sistema de monitoreo
- [ ] **Diseño**: Modernizar la estética del sitio

---

## Plantilla de Planes

Los planes se guardan en `docs/plan/NN_nombre-del-plan.md` donde `NN` es un número secuencial.

### Estructura obligatoria:

```markdown
# Plan NN: Título del Plan

> **Estado**: 📋 Planificado | ⏳ En progreso | ✅ Completado
> **Fecha**: YYYY-MM-DD
> **Servidor**: (si aplica)

---

## Progreso General

\```
░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░   0% — PLANIFICADO
▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░░░░░░░░░░░░░  50% — EN PROGRESO
██████████████████████████████ 100% — COMPLETADO
\```

| Fase | Descripción | Estado |
|:---|:---|:---|
| 1 | Nombre de la fase | 📋 / ⏳ / ✅ |
| 2 | Nombre de la fase | 📋 / ⏳ / ✅ |

---

## Objetivo

Descripción clara de qué se quiere lograr.

---

## Fase 1: Nombre

\```
░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░   0%
\```

- [ ] Tarea 1
- [ ] Tarea 2
- [ ] Tarea 3

**Notas/Hallazgos**: (documentar decisiones y descubrimientos)

---

## Fase N: Nombre

(repetir estructura por cada fase)

---

## Resumen de archivos creados/modificados

| Archivo | Ubicación | Estado |
|:---|:---|:---|
| `archivo` | Ruta | 📋 / ✅ |

## Seguridad

(si aplica — documentar medidas de seguridad)

## Rollback

(cómo revertir si algo sale mal)
```

### Convenciones:

| Símbolo | Significado |
|:---|:---|
| `░░░` | No iniciado |
| `▓▓▓` | En progreso |
| `███` | Completado |
| `📋` | Planificado |
| `⏳` | En progreso |
| `✅` | Completado |
| `- [ ]` | Tarea pendiente |
| `- [x]` | Tarea completada |

### Reglas:

1. **Al crear** un plan: estado `📋 Planificado`, barras en `░░░`, tareas `[ ]`
2. **Al ejecutar** cada fase: actualizar barra a `▓▓▓`, tareas a `[x]` al completar
3. **Al terminar**: estado `✅ Completado`, barras en `███`, documentar resultados reales
4. **Siempre** documentar hallazgos, decisiones y problemas encontrados
