# Pattern Care MVP — Workspace Rules

> Read this file before generating, refactoring, or reviewing any code in this project.
> The same content lives in `.cursorrules` (Cursor) and `.agent/rules/modern-web-guidance.md`
> (Antigravity). Edit both together.

---

## 🏗️ Architectural Summary

**Pattern Care ERP** is a Vanilla HTML/CSS/JS + PHP/MySQL application that mimics
Microsoft Dynamics 365 Business Central's UX for managing a medical-uniform
manufacturing business across multiple branches.

The UI is one page (`index.html`) with no build step: markup in `index.html`, styles
and scripts in `assets/`, JSON endpoints in `backend/`.

### Stack (exact)
| Layer       | Technology                                                  |
|-------------|-------------------------------------------------------------|
| Frontend    | Vanilla HTML5 + CSS3 + JavaScript (ES2020+), classic scripts |
| Styling     | Tailwind CSS v3 via CDN (theme in `assets/tailwind.config.js`) + `assets/estilos.css` |
| Charts      | Chart.js v4 via CDN                                         |
| Dialogs     | SweetAlert2 via CDN                                         |
| Icons       | FontAwesome 6 via CDN                                       |
| Backend     | PHP 8+ with PDO (MySQL)                                     |
| Database    | MySQL 8 (InnoDB, utf8mb4)                                   |
| Auth        | PHP session (`$_SESSION`) via `backend/auth_sesion.php`; bcrypt password hashes |
| Images      | Cloudinary when configured · `/uploads/products/` fallback  |
| Deployment  | IIS (`web.config`) / Docker (`Dockerfile` + `docker-compose.yml`) |
| Build step  | ❌ None — no npm, no package.json, no TypeScript            |

### Key files
- `login.html` — Sign-in page. Posts to `backend/auth_sesion.php`; on success goes to `./`.
  Opened via `file://` it jumps straight to `index.html` (standalone demo).
- `index.html` — **Single source of the UI markup.** No inline `<script>`/`<style>` blocks.
- `index.php` — Thin wrapper that serves `index.html` (IIS/Apache pick it as default document).
  **Never add markup or logic here; edit `index.html`.**
- `assets/estilos.css`, `assets/tailwind.config.js` — styles and Tailwind theme.
- `assets/js/` — classic scripts, loaded in this order and sharing the global scope:
  1. `auth.js` — `PatternCareAuth`, `fetchConSesion()`, redirect to `login.html`
  2. `app.js` — `bcState`, event delegation, inventory, transfers, production, waste, yield,
     global search, ERP alerts
  3. `catalogo.js` — `catalogoState`, product models, variants, part numbers, photos
  4. `accesos.js` — `PERMISO_POR_MODULO`, route protection, users & branch assignments
- `conexion.php` — PDO connection helper + `jsonResponse()`.
- `backend/*.php` — JSON endpoints. Shared pieces: `bootstrap_endpoint.php`
  (`arrancarEndpointJson()`), `middleware_auth.php` (401/403 + permission and branch checks),
  `modelo_accesos.php` (users, roles, password hashing).
- `database_master.sql` — Full schema + seed data (Docker runs it on a fresh volume).
- `database_v3_usuarios.sql` — Adds the access-control module to an existing database.
- `legacy/` — Historical schemas (`database.sql`, `database_v2_products.sql`). **Do not run.**

### Global JS state
- `PatternCareAuth` — session: `usuario`, `tienePermiso(clave)`, `sucursalesPermitidas()`,
  `puedeEnSucursal(id, accion)`, `cerrarSesion()`
- `bcState` — main ERP state (active branch, products, inventory, transfers, KPIs)
- `catalogoState` — product catalog module state
- `accesosState` — users & access administration module state

### Two runtime modes
- **Server** (Apache/IIS + MySQL): real session, data from `backend/obtener_datos.php`.
- **Standalone** (`index.html` opened with a double click, or served by a static server
  without PHP): demo session with all permissions, data from seed constants + `localStorage`.
  Keep both modes working.
- When PHP answers with an error (e.g. MySQL down) the app must **not** fall back to the demo
  session or demo data: it returns to `login.html?motivo=sin-servidor`, or shows
  "Sin conexión con la base de datos" if the session was already open.

---

## 🌐 Business Context

**Domain:** Medical uniform manufacturing (Gorros quirúrgicos, Chamarras, Cubrepolvos)

**Branches (4):**
1. `MAT-01` Matriz & Planta de Confección — main production plant
2. `SUC-HOSP` Sucursal Hospital General — hospital-side sales
3. `SUC-NORTE` Sucursal Zona Norte (Clínica Palmas) — northern zone distribution
4. `ALM-DIG` Almacén Digital & Envíos WhatsApp — digital/online sales channel

**Roles (5):** Dueña / Encargada General (all branches) · Área Administrativa ·
Corte y Control de Calidad · Confección Textil · Mostrador / Punto de Venta.
Seeded accounts: `encargada`, `administracion`, `corte1`, `corte2`, `confeccion1`,
`confeccion2`, `pv.hospital` (initial password `PatternCare2026`). Accounts are created by the
Dueña in **Usuarios y Accesos** — there is no self-registration.

**Fabric yield rules:**
- Tela SRA: 6 piezas / metro
- Tela CRO: 4 piezas + 50 cm sobrante / metro

**Stock alert threshold:** each variant has its own `stock_minimo` per branch (default 6).
A SKU is critical when stock < its minimum; in the consolidated view, when it is below the
minimum in at least one branch.

---

## 🔐 Access Control

- Every endpoint starts with `require_once __DIR__ . '/bootstrap_endpoint.php';` and
  `arrancarEndpointJson([...action => permission...], 'default.permission')`. The middleware
  answers `401` without a session and `403` when the permission or the branch is missing.
- Frontend calls to `backend/` go through `fetchConSesion()` (sends the cookie, turns `401`
  into a redirect to `login.html?motivo=expirada` and `403` into a visible notice).
- A module's permission is declared once, in `PERMISO_POR_MODULO` (`assets/js/accesos.js`).
  A module missing from that map opens for nobody. `cambiarModulo()` enforces it, so the
  ribbon, dropdowns, drawer, search and alerts all respect it.
- Permission keys use `modulo.accion` (e.g. `inventario.ajustar`). New keys go in
  `database_master.sql` (table `permisos` + `rol_permisos`).
- Client-side checks only hide UI. **The server is the authority** — never rely on the
  frontend alone to protect data.
- **Fail closed.** Compatibility mode (full permissions, for databases without the access
  module) is granted only when `estadoControlAccesos()` confirms `'no_instalado'`. If the check
  cannot run (`'desconocido'`), `autorizacionActiva()` answers `503`.

---

## ✅ Standards — Modern Web Guidance

### HTML
- Use semantic elements: `<main>`, `<nav>`, `<section>`, `<article>`, `<aside>`, `<header>`, `<footer>`, `<figure>`, `<dialog>`, `<details>`.
- **No `<div>` soup** — every container must have a semantic purpose or an explicit `role` attribute.
- Every `<input>` and `<select>` **must** have a corresponding `<label>` (visible or `.sr-only`).
- Group related form controls with `<fieldset>` + `<legend>`.
- Use `<button type="button">` for JS actions; `<button type="submit">` only inside `<form>`.
- Do NOT add inline `onclick="..."` attributes. Declare `data-action="..."` and handle it in
  the delegated `click` listener of `initEventListeners()` (`assets/js/app.js`). Older code
  still has inline handlers; migrate them when you touch that code.

### Accessibility (WCAG 2.1 AA)
- All interactive elements must be keyboard-reachable and show a visible focus ring.
- Use `aria-live="polite"` for dynamic status areas (alerts, loading states).
- Modals: trap focus, `role="dialog"`, `aria-modal="true"`, `aria-labelledby`, close on Escape.
- Color alone must never be the only visual indicator (pair with icon or text).
- Minimum contrast: 4.5:1 for body text, 3:1 for large text / UI components.

### CSS / Tailwind
- Prefer Tailwind utility classes; use custom CSS variables only for design tokens.
- **BC Color palette:** `--bc-blue: #0078d4` · `--bc-blue-dk: #005a9e` · `--bc-teal: #008575`
- Background: `bg-white` / `bg-gray-50`; never `bg-slate-800` or dark backgrounds.
- Responsive breakpoints: mobile-first (`sm:` `md:` `lg:`).
- Extract repeated patterns into reusable CSS classes in `assets/estilos.css`, not duplicated Tailwind strings.

### JavaScript
- Put code in the matching file under `assets/js/`; never add `<script>` blocks to `index.html`.
- Scripts are classic (not modules) and share one global scope: a top-level `const`/`let`
  name must be unique across the four files.
- **No `any`-equivalent patterns** — always validate inputs before use.
- **No `eval()`**, no `innerHTML` with raw user data (use `textContent` or DOM methods).
- Use `fetchConSesion()` for calls to `backend/`; plain `fetch` only for public resources.
- Always handle three UI states: **loading · success · error**.
- Do not derive state inside event handlers — compute it in dedicated functions.
- Prefer `const` / `let`; never `var`.
- Use optional chaining (`?.`) and nullish coalescing (`??`) instead of `||` for defaults.

### PHP / Backend
- **Always** use PDO prepared statements — never concatenate user input into SQL strings.
- Respond with `jsonResponse($success, $data, $message, $status)` from `conexion.php`, which
  returns `{"success": ..., "message": ..., "data": ..., "timestamp": ...}`.
- Validate and sanitize every `$_POST` / `$_GET` value server-side.
- Passwords: `hashPassword()` / `verificarPassword()` in `modelo_accesos.php` (bcrypt;
  seeded SHA-256 hashes are upgraded to bcrypt on first login). Never store or compare
  plain-text passwords.

### SQL
- SQL files are UTF-8 and start with `SET NAMES utf8mb4;` — keep it, or accents get stored
  double-encoded ("DueÃ±a").
- Schema changes go in `database_master.sql` (must stay re-runnable) and, when they extend
  an existing install, in a separate additive script like `database_v3_usuarios.sql`.

---

## 🚫 Anti-patterns — Never Do

| Anti-pattern                                   | Correct alternative                               |
|------------------------------------------------|---------------------------------------------------|
| New inline `onclick="fn()"` handlers           | `data-action` + the delegated listener            |
| `<script>` / `<style>` blocks inside `index.html` | `assets/js/*.js` / `assets/estilos.css`        |
| Markup or logic in `index.php`                 | Edit `index.html`; `index.php` only serves it     |
| Auth state in `sessionStorage` / `localStorage` | PHP session + `PatternCareAuth`                 |
| `fetch('backend/...')` without the session wrapper | `fetchConSesion('backend/...')`               |
| A new module without an entry in `PERMISO_POR_MODULO` | Add its permission key (it stays closed otherwise) |
| `innerHTML = userInput`                        | `textContent = userInput` or DOM API              |
| `var` declarations                             | `const` / `let`                                   |
| Unchecked `JSON.parse()` without try/catch     | Wrap in try/catch, provide fallback               |
| SQL: `"SELECT * WHERE id=" + $_GET['id']`      | PDO: `$stmt->bindParam(':id', $id)`               |
| Adding dark backgrounds (`bg-slate-800`)       | Use `bg-white` / `bg-gray-50` / `bg-gray-100`     |
| Blocking the main thread with `alert()`        | Use SweetAlert2 or a custom dialog                |
| Re-inventing a library already in CDN          | Use Chart.js / SweetAlert2 / FontAwesome helpers  |
| Running the schemas in `legacy/`               | `database_master.sql`                             |

---

## 📋 Checklist Before Committing

- [ ] No dark Slate/dark backgrounds remaining (`grep -n "bg-slate-[7-9]"`)
- [ ] `index.html` has no inline `<script>`/`<style>` blocks; `index.php` is still the wrapper
- [ ] `node --check` passes on every file in `assets/js/`
- [ ] All new `<input>` elements have `<label>` tags
- [ ] New interactions use `data-action`, not inline handlers
- [ ] New modules have an entry in `PERMISO_POR_MODULO`; new endpoints use `arrancarEndpointJson()`
- [ ] PHP endpoints use prepared statements
- [ ] SQL changes keep `SET NAMES utf8mb4;` and `database_master.sql` re-runnable
- [ ] New images go through the Cloudinary helper in `gestionar_productos.php`
- [ ] Works signed in through `login.html` **and** in standalone mode (`index.html` by double click)
