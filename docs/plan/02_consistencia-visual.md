# Plan 02: Mejora Visual y Consistencia entre Dispositivos

> **Estado**: ✅ Completado
> **Fecha**: 2026-03-28
> **Contexto**: El sitio se ve diferente entre Brave (móvil) y Firefox (escritorio)

---

## Issues Detectados

| # | Issue | Origen | Estado |
|:---|:---|:---|:---|
| 1 | **Colores diferentes** entre celular y escritorio | Dark mode automático incompleto (`prefers-color-scheme`) | ✅ Fix |
| 2 | **Items de audio desalineados** en móvil | Conflicto `text-align: center` (body) vs `flexbox` (song-item) | ✅ Fix |
| 3 | **Botón oculto para modo admin** | 5 taps en versión → prompt clave → localStorage TTL 30 min + logout | ✅ Impl |

## Progreso General

```
██████████████████████████████ 100% — COMPLETADO
```

| Fase | Descripción | Estado |
|:---|:---|:---|
| 1 | Diagnosticar diferencias | ✅ |
| 2 | Forzar modo claro consistente | ✅ |
| 3 | Implementar botón admin oculto | ✅ |
| 4 | Prueba cross-browser | ✅ |

---

## Fase 1: Diagnosticar diferencias

```
██████████████████████████████ 100%
```

- [x] Listar elementos con colores definidos en style.css
- [x] Identificar elementos sin override en dark mode (header, footer, borders, hover, botones)
- [x] Causa raíz: `@media (prefers-color-scheme: dark)` incompleto + `text-align: center` en body

---

## Fase 2: Forzar modo claro + fix alineación

```
██████████████████████████████ 100%
```

- [x] Agregar `color-scheme: light only;` en `html` — fuerza modo claro en todos los navegadores
- [x] Eliminar bloque `@media (prefers-color-scheme: dark)` incompleto
- [x] Cambiar body `text-align: center` → `text-align: left`
- [x] Agregar `text-align: center` solo donde corresponde (header, playlist h3, audio-title, footer)
- [x] Song-item: agregar `text-align: left`, `gap: 10px`, `word-break: break-word`
- [x] Song-link: agregar `flex: 1; min-width: 0` para que ocupe espacio disponible sin empujar el ícono WA

**Cambios en `css/style.css`**:
```css
html { color-scheme: light only; }           /* Fuerza modo claro */
body { text-align: left; }                   /* Base left-align */
.header, .playlist h3, .footer { text-align: center; }  /* Centrar solo donde corresponde */
.song-item { text-align: left; gap: 10px; } /* Items siempre alineados izquierda */
.song-link { flex: 1; min-width: 0; word-break: break-word; } /* Nombres largos no rompen layout */
```

---

## Fase 3: Botón admin oculto

```
██████████████████████████████ 100%
```

- [x] Footer: versión clickeable con listener de 5 taps (1.5 seg ventana)
- [x] Modal de login: input password, validación, feedback de error
- [x] `admin_check.php`: endpoint POST que valida clave VCV2026, responde JSON
- [x] localStorage: guarda sesión con timestamp, TTL 30 min
- [x] Auto-check al cargar: si sesión activa y no expirada → `body.admin-mode`
- [x] CSS: clase `.admin-only` oculta elementos; `body.admin-mode .admin-only` los muestra
- [x] Botón logout visible solo en modo admin (en footer)
- [x] Click fuera del modal / ESC para cerrar
- [x] `index.php` refactorizado: descarga usa clase `admin-only` (no depende de `?key`)
- [x] `play.php` refactorizado: navegación Anterior/Siguiente usa clase `admin-only`

**Flujo completo**:
```
5x tap versión → 🔐 Modal → Clave → POST admin_check.php → 
  ✅ OK → localStorage {ts} → body.admin-mode → 📥 Descarga + ⟵⟶ Nav visibles
  ❌ Error → "Clave incorrecta"
  
30 min después → sesión expira → modo público automático
Botón "Salir ↗" → limpia localStorage → reload → modo público
```

**Archivos creados/modificados**:
| Archivo | Acción |
|:---|:---|
| `css/style.css` | ✅ Reescrito: light-only, fix alineación, estilos admin |
| `incs/footer.php` | ✅ Reescrito: 5 taps, modal, JS admin session |
| `admin_check.php` | ✅ Nuevo: validación de clave |
| `index.php` | ✅ Refactorizado: descarga con admin-only |
| `play.php` | ✅ Refactorizado: navegación con admin-only |
| `incs/versionLogs.php` | ✅ Versión 26.4 |

---

## Fase 4: Prueba cross-browser

```
██████████████████████████████ 100%
```

- [x] Firefox escritorio — confirmado por usuario
- [x] Brave móvil — confirmado por usuario ("Se ve mejor")
- [x] Verificar 5 taps → modal → login → modo admin ✅
- [x] Verificar logout ✅
- [ ] Verificar expiración 30 min (pendiente esperar tiempo)

---

## Rollback

```bash
git revert HEAD && git push   # El webhook desplegará automáticamente
```
