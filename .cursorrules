# Pattern Care MVP — Workspace Rules (.cursorrules)

> Read this file before generating, refactoring, or reviewing any code in this project.

---

## 🏗️ Architectural Summary

**Pattern Care ERP** is a Vanilla HTML/CSS/JS + PHP/MySQL single-file application that
mimics Microsoft Dynamics 365 Business Central's UX for managing a medical-uniform
manufacturing business across multiple branches.

### Stack (exact)
| Layer       | Technology                                        |
|-------------|---------------------------------------------------|
| Frontend    | Vanilla HTML5 + CSS3 + JavaScript (ES2020+)       |
| Styling     | Tailwind CSS v3 via CDN · Custom CSS variables    |
| Charts      | Chart.js v4 via CDN                               |
| Dialogs     | SweetAlert2 via CDN                               |
| Icons       | FontAwesome 6 via CDN                             |
| Backend     | PHP 8+ with PDO (MySQL)                           |
| Database    | MySQL 8 (InnoDB, UTF-8mb4)                        |
| Auth        | sessionStorage (HTML) · $_SESSION (PHP)           |
| Images      | Cloudinary (primary) · /uploads/products/ (fallback) |
| Deployment  | IIS / Docker (see Dockerfile + docker-compose.yml)|
| Build step  | ❌ None — no npm, no package.json, no TypeScript  |

### Key files
- `login.html` — Auth page (shown first; redirects to index.html on success)
- `index.html` — Full standalone ERP (226 KB, requires pc_user in sessionStorage)
- `index.php`  — PHP version (synced with index.html; adds real DB + sessions)
- `conexion.php` — PDO connection helper
- `backend/*.php` — 9 PHP endpoint files (REST-style, JSON responses)
- `database.sql` — Original schema (run first)
- `database_v2_products.sql` — Products catalog schema (run after database.sql)

### Global JS state objects
- `bcState` — main ERP state (branch, inventory, production, transfers)
- `catalogoState` — product catalog module state
- `sessionStorage['pc_user']` — logged-in user JSON `{usuario, nombre, rol, sucursal, loginAt}`

---

## 🌐 Business Context

**Domain:** Medical uniform manufacturing (Gorros quirúrgicos, Chamarras, Cubrepolvos)

**Branches (4):**
1. `Matriz Planta` — main production plant
2. `Sucursal Hospital` — hospital-side sales
3. `Sucursal Zona Norte` — northern zone distribution
4. `Almacén Digital` — digital/online sales channel

**Staff roles:** Dueña / Encargada General · Área Administrativa · Corte y Calidad (×2) · Confección (×2)

**Fabric yield rules:**
- Tela SRA: 6 piezas / metro
- Tela CRO: 4 piezas + 50 cm sobrante / metro

**Stock alert threshold:** < 6 piezas = critical

---

## ✅ Standards — Modern Web Guidance

### HTML
- Use semantic elements: `<main>`, `<nav>`, `<section>`, `<article>`, `<aside>`, `<header>`, `<footer>`, `<figure>`, `<dialog>`, `<details>`.
- **No `<div>` soup** — every container must have a semantic purpose or an explicit `role` attribute.
- Every `<input>` and `<select>` **must** have a corresponding `<label>` (visible or `.sr-only`).
- Group related form controls with `<fieldset>` + `<legend>`.
- Use `<button type="button">` for JS actions; `<button type="submit">` only inside `<form>`.
- Do NOT use inline `onclick="..."` attributes — use `addEventListener` instead (CSP-safe).

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
- Extract repeated patterns into reusable CSS classes, not duplicated Tailwind strings.

### JavaScript
- **No `any`-equivalent patterns** — always validate inputs before use.
- **No `eval()`**, no `innerHTML` with raw user data (use `textContent` or DOM methods).
- Use `fetch` (Fetch API) for all HTTP requests — no jQuery, no XMLHttpRequest.
- Always handle three UI states: **loading · success · error**.
- Do not derive state inside event handlers — compute it in dedicated functions.
- Use `'use strict';` at the top of every script block.
- Prefer `const` / `let`; never `var`.
- Use optional chaining (`?.`) and nullish coalescing (`??`) instead of `||` for defaults.

### PHP / Backend
- **Always** use PDO prepared statements — never concatenate user input into SQL strings.
- Return consistent JSON: `{"success": true, "data": ...}` or `{"success": false, "error": "..."}`.
- Validate and sanitize every `$_POST` / `$_GET` value server-side.
- Use `password_hash()` / `password_verify()` for credentials when moving from hardcoded users.
- Set `Content-Type: application/json` before `echo`.

---

## 🚫 Anti-patterns — Never Do

| Anti-pattern                               | Correct alternative                              |
|--------------------------------------------|--------------------------------------------------|
| `onclick="fn()"` inline handlers           | `el.addEventListener('click', fn)`               |
| `innerHTML = userInput`                    | `textContent = userInput` or DOM API             |
| `var` declarations                         | `const` / `let`                                  |
| Unchecked `JSON.parse()` without try/catch | Wrap in try/catch, provide fallback              |
| SQL: `"SELECT * WHERE id=" + $_GET['id']`  | PDO: `$stmt->bindParam(':id', $id)`              |
| Adding dark backgrounds (`bg-slate-800`)   | Use `bg-white` / `bg-gray-50` / `bg-gray-100`   |
| Blocking the main thread with `alert()`    | Use SweetAlert2 or a custom dialog               |
| Removing PHP header when editing index.php | Preserve everything before `<!DOCTYPE html>`     |
| Re-inventing a library already in CDN      | Use Chart.js / SweetAlert2 / FontAwesome helpers |

---

## 🔄 Sync Rule (index.html ↔ index.php)

Whenever `index.html` is modified:
1. Find the `<!DOCTYPE html>` position in `index.php`.
2. Preserve everything **before** it (PHP session/DB header block).
3. Replace everything **from** `<!DOCTYPE html>` onward with the new HTML content.
4. Verify with: `node -e "const f=require('fs'),c=f.readFileSync('index.php','utf8'); console.log('PHP header ok:', c.startsWith('<?php'))"`

---

## 📋 Checklist Before Committing

- [ ] No dark Slate/dark backgrounds remaining (`grep -n "bg-slate-[7-9]"`)
- [ ] Auth guard present at top of `<script>` in `index.html`
- [ ] All new `<input>` elements have `<label>` tags
- [ ] All new event handlers use `addEventListener`
- [ ] PHP endpoints use prepared statements
- [ ] `database_v2_products.sql` runs AFTER `database.sql` (additive)
- [ ] New images go through Cloudinary helper in `gestionar_productos.php`
- [ ] `index.php` PHP header preserved after HTML sync
