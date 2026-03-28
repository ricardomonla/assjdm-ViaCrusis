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

## Pendientes

- [ ] **Seguridad**: Configurar fail2ban y headers de seguridad en NGINX
- [ ] **Backup**: Configurar vzdump para respaldos automáticos del LXC
- [ ] **Monitorización**: Integrar sistema de monitoreo
- [ ] **Diseño**: Modernizar la estética del sitio (actualmente funcional pero básico)
