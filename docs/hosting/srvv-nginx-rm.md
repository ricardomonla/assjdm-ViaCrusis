# Ficha de Nodo: srvv-nginx-rm

| Atributo | Valor |
| :--- | :--- |
| **Hostname** | `srvv-nginx-rm` |
| **IP Pública** | `190.114.205.17` |
| **IP LAN** | `10.0.10.117` |
| **DNS** | `rmonla.duckdns.org` |
| **Sistema Operativo** | Debian 12 (Bookworm) |
| **Tipo** | Contenedor LXC (VMID 116, unprivileged) |
| **Rol** | Servidor Web NGINX |
| **Host** | `srv-pmox3` (10.0.10.203) |
| **SSH** | Puerto `7022`, usuario `root`, auth `password` |
| **Bóveda** | `srvv-nginx-rm:root` |

## Especificaciones de Hardware
- **CPU**: 2 cores (compartido LXC)
- **RAM**: 2 GB
- **Swap**: 512 MB
- **Disco**: 20 GB (local-lvm:vm-116-disk-0)

## Servicios y Responsabilidades
1. **NGINX 1.22.1** — Servidor web, puertos 80 (HTTP) y 443 (HTTPS)
2. **PHP 8.2-FPM** — Procesamiento de aplicaciones PHP
3. **Certbot 2.1.0** — Certificados Let's Encrypt, renovación automática
4. **OpenSSH** — Acceso remoto, puerto 7022

## Aplicaciones Desplegadas
| App | Ruta | Repo GitHub | Método Deploy |
| :--- | :--- | :--- | :--- |
| ViaCrucis | `/vcby` → `/var/www/vcby` | `ricardomonla/assjdm-ViaCrusis` | Deploy key SSH |

## Red
- **eth0**: `190.114.205.17/24` — Red pública, gw `190.114.205.1`
- **eth1**: `10.0.10.117/23` — Red interna, gw `10.0.10.1`
- **DNS**: `8.8.8.8`
- **Bridge**: `vmbr0` (capa 2 compartida público/privado)
- **DuckDNS**: `rmonla.duckdns.org` → `190.114.205.17`

## HTTPS / Certificado SSL
- **Certificado**: Let's Encrypt para `rmonla.duckdns.org`
- **Expiración**: 2026-06-26
- **Renovación**: Automática (certbot timer)
- **Redirect**: HTTP → HTTPS (301) activo

## Acceso y Mantenimiento
- **SSH**: `ssh -p 7022 root@10.0.10.117` o `root@190.114.205.17`
- **Candados**: `./adn/tools/run candados run srvv-nginx-rm:root SSHPASS 'sshpass -e ssh -p 7022 root@10.0.10.117'`
- **Proxmox**: `pct exec 116 -- bash` desde srv-pmox3
- **Web pública**: `https://rmonla.duckdns.org/vcby/`
- **Backup**: Pendiente configurar vzdump
- **Monitorización**: Pendiente integrar

## Dependencias
- `srv-pmox3` — Host Proxmox
- Proyecto `P2606_WebServer-NGINX`

## Historial de Cambios Relevantes
- 2026-03-28: LXC creado. NGINX instalado. IP pública verificada (HTTP 200).
- 2026-03-28: DNS DuckDNS configurado. Repo vcby clonado con deploy key. PHP 8.2-FPM instalado.
- 2026-03-28: HTTPS configurado con certbot/Let's Encrypt. Redirect HTTP→HTTPS activo.
- 2026-03-28: Clave root guardada en candados (`srvv-nginx-rm:root`).
