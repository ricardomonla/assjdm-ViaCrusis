# Plan 07: Entorno de Trabajo Móvil (Android Offline)

> **Estado**: ⏳ En progreso
> **Fecha**: 2026-04-02

---

## Progreso General

```text
▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░░░░░░░░  66% — EN PROGRESO
```

| Fase | Descripción | Estado |
|:---|:---|:---|
| 1 | Investigación de viabilidad Docker en Android | ✅ |
| 2 | Configuración del entorno base | ✅ |
| 3 | Sincronización offline y despliegue del sistema VCBY | ⏳ |

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
▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░░░░░░░░░░░░░  50%
```

- [x] Clonar el repositorio `assjdm-ViaCrusis` para uso offline.
- [x] Configurar el Nginx/PHP local del móvil para apuntar a la carpeta del proyecto.
- [x] Realizar pruebas de acceso a `localhost` desde el navegador del celular y confirmar funcionamiento sin internet.
- [ ] Cargar nombre y mail del commiter en Termux (`git config --global user.name`...).
- [ ] Generar un Personal Access Token (PAT) clásico en GitHub (o llave SSH local) para autorizar PUSH desde el dispositivo Android.
- [ ] Realizar un cambio de prueba y enviarlo al repositorio remoto (`git push`).

**Notas/Hallazgos**:
- El servidor `php -S 127.0.0.1:8080` resultó ideal. Se accede correctamente por el navegador.
- Al no haber entorno de escritorio con llavero, para *pushear* al repositorio será indispensable vincular la terminal móvil a GitHub mediante un Token de acceso o la CLI oficial o SSH.

---

## Resumen de archivos creados/modificados

| Archivo | Ubicación | Estado |
|:---|:---|:---|
| `07_entorno-android.md` | `docs/plan/` | 📋 |
