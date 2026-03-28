# Contexto del Proyecto — ViaCrucis BY2026

> Última actualización: 2026-03-28

## Descripción General

Aplicación web PHP para la gestión y reproducción de audios del Via Crucis del Barrio Yacampiz (2026). Permite listar, reproducir y compartir por WhatsApp los tracks de audio de la representación. Desplegada en un servidor NGINX propio con HTTPS.

## URL Pública

- **Sitio**: `https://rmonla.duckdns.org/vcby/`
- **Repositorio**: `ricardomonla/assjdm-ViaCrusis`

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
├── media/             # 34 archivos MP3 (000-309)
├── tools/             # Scripts de mantenimiento (renombrado, conversión)
└── docs/
    ├── hosting/       # Fichas de nodo
    └── contexto/      # Este archivo
```

## Funcionalidades

1. **Lista de audios** — Muestra los 34 tracks ordenados por número (000-309)
2. **Reproductor** — Página individual por track con controles HTML5
3. **Compartir WhatsApp** — Botón en cada track para enviar enlace + título
4. **Acceso con clave** — `?key=VCV2026` habilita descarga y navegación entre tracks
5. **Modo público** — Sin clave: solo lista + reproducción + compartir WhatsApp
6. **Serve seguro** — `serve.php` oculta rutas físicas, valida formato, soporta range requests
7. **Modo oscuro** — Automático vía `prefers-color-scheme`
8. **Responsive** — Diseño adaptable a móvil/tablet/desktop

## Versión Actual

- **Versión**: 26.1 (2026-03-27)
- **Cambios**: Actualización año 2025→2026, nueva key VCV2026, acceso público sin key

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

- [ ] **Seguridad**: Configurar fail2ban y headers de seguridad en NGINX
- [ ] **Backup**: Configurar vzdump para respaldos automáticos del LXC
- [ ] **Monitorización**: Integrar sistema de monitoreo
- [ ] **Diseño**: Modernizar la estética del sitio (actualmente funcional pero básico)

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
