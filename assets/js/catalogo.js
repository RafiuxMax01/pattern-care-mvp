// Modulo de catalogo: productos, variantes, numeros de pieza y fotos.

// ── Estado local del catálogo de productos ────────────────
const catalogoState = {
    productos: [],        // lista de productos con variantes
    pasoActual: 1,        // 1 = Info, 2 = Variantes, 3 = Foto
    variantes: [],        // variantes del formulario en curso
    fotoBase64: null,     // imagen seleccionada en base64
    fotoNombre: null,
    fotoUrlManual: null,
    editandoId: null      // null = nuevo, número = editar
};

// Datos semilla para modo standalone (sin backend)
const CATALOGO_SEMILLA = [
    { id: 1, numero_pieza: 'GQ-CLA-0001', nombre: 'Gorro Quirúrgico Clásico Antifluido', categoria: 'Gorros Quirúrgicos', coleccion: 'Clásica Hospitalaria', tipo_tela: 'SRA', precio_base: 120, stock_minimo: 6, foto_url: null, notas: 'Alta demanda hospitalaria', variantes: [
        { id: 1, sku: 'GQ-CLA-0001-AZC-U', color: 'Azul Cielo',     talla: 'Unitalla', activo: 1 },
        { id: 2, sku: 'GQ-CLA-0001-VER-U', color: 'Verde Quirófano', talla: 'Unitalla', activo: 1 },
        { id: 3, sku: 'GQ-CLA-0001-LIL-U', color: 'Lila',           talla: 'Unitalla', activo: 1 }
    ]},
    { id: 2, numero_pieza: 'GQ-EST-0002', nombre: 'Gorro Quirúrgico Estampado Odonto Smile', categoria: 'Gorros Quirúrgicos', coleccion: 'Odontología Smile', tipo_tela: 'SRA', precio_base: 150, stock_minimo: 6, foto_url: null, notas: 'Taller externo sublimado', variantes: [
        { id: 4, sku: 'GQ-EST-0002-MUL-U', color: 'Multicolor', talla: 'Unitalla', activo: 1 }
    ]},
    { id: 3, numero_pieza: 'CH-PRE-0001', nombre: 'Chamarra Médica Antifluido Stretch Premier', categoria: 'Chamarras Médicas', coleccion: 'Línea Premier', tipo_tela: 'CRO', precio_base: 680, stock_minimo: 6, foto_url: null, notas: 'Desabasto de cierres', variantes: [
        { id: 5, sku: 'CH-PRE-0001-BLA-S', color: 'Blanco Óptico', talla: 'S', activo: 1 },
        { id: 6, sku: 'CH-PRE-0001-BLA-M', color: 'Blanco Óptico', talla: 'M', activo: 1 },
        { id: 7, sku: 'CH-PRE-0001-BLA-L', color: 'Blanco Óptico', talla: 'L', activo: 1 },
        { id: 8, sku: 'CH-PRE-0001-AZM-M', color: 'Azul Marino',   talla: 'M', activo: 1 },
        { id: 9, sku: 'CH-PRE-0001-VER-S', color: 'Verde Quirófano', talla: 'S', activo: 1 }
    ]},
    { id: 4, numero_pieza: 'CP-IMP-0001', nombre: 'Cubrepolvo Impermeable con Ajuste', categoria: 'Cubrepolvos', coleccion: 'Protección Total', tipo_tela: 'CRO', precio_base: 320, stock_minimo: 6, foto_url: null, notas: 'Alta demanda institucional', variantes: [
        { id: 10, sku: 'CP-IMP-0001-NEG-U', color: 'Negro Oxford', talla: 'Unitalla', activo: 1 },
        { id: 11, sku: 'CP-IMP-0001-AZM-U', color: 'Azul Marino',  talla: 'Unitalla', activo: 1 }
    ]}
];

// ─────────────────────────────────────────────────────────
// RENDERIZADO CATÁLOGO
// ─────────────────────────────────────────────────────────
function renderizarCatalogo() {
    // Intentar cargar desde backend; si falla, usar estado local o semilla
    if (bcState.modoBackend === 'mysql') {
        fetchConSesion('backend/gestionar_productos.php?action=listar')
            .then(r => r.json())
            .then(j => {
                if (j.success) {
                    catalogoState.productos = j.data || [];
                } else {
                    _cargarCatalogoLocal();
                }
                _pintarCatalogo();
            })
            .catch(() => { _cargarCatalogoLocal(); _pintarCatalogo(); });
    } else {
        _cargarCatalogoLocal();
        _pintarCatalogo();
    }
}

function _cargarCatalogoLocal() {
    const raw = localStorage.getItem('pattern_care_catalogo');
    if (raw) {
        try { catalogoState.productos = JSON.parse(raw); return; } catch(e) {}
    }
    catalogoState.productos = JSON.parse(JSON.stringify(CATALOGO_SEMILLA));
}

function _guardarCatalogoLocal() {
    localStorage.setItem('pattern_care_catalogo', JSON.stringify(catalogoState.productos));
}

function _pintarCatalogo() {
    const grid = document.getElementById('catalogo-grid');
    if (!grid) return;

    const busq = (document.getElementById('filtro-catalogo-busqueda')?.value || '').toLowerCase();
    const cat  = document.getElementById('filtro-catalogo-cat')?.value || '';

    const filtrados = catalogoState.productos.filter(p => {
        const matchBusq = !busq || p.nombre.toLowerCase().includes(busq) || (p.numero_pieza || '').toLowerCase().includes(busq) || (p.coleccion || '').toLowerCase().includes(busq);
        const matchCat  = !cat  || p.categoria === cat;
        return matchBusq && matchCat;
    });

    if (filtrados.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                <i class="fa-solid fa-box-open text-4xl mb-3 text-gray-300"></i>
                <p class="font-semibold text-sm">Sin productos en el catálogo</p>
                <p class="text-xs mt-1">Agrega un modelo con el botón "Nuevo Modelo"</p>
                <button onclick="abrirModalProducto()" class="mt-4 px-4 py-2 rounded-lg bg-violet-600 text-white text-xs font-semibold shadow">
                    <i class="fa-solid fa-plus mr-1"></i> Nuevo Modelo
                </button>
            </div>`;
        return;
    }

    const catColor = { 'Gorros Quirúrgicos': 'bg-blue-50 text-blue-700 border-blue-200', 'Chamarras Médicas': 'bg-indigo-50 text-indigo-700 border-indigo-200', 'Cubrepolvos': 'bg-teal-50 text-teal-700 border-teal-200', 'Otros': 'bg-gray-100 text-gray-600 border-gray-200' };

    grid.innerHTML = filtrados.map(p => {
        const variActivas = (p.variantes || []).filter(v => v.activo !== 0);
        const colores = [...new Set(variActivas.map(v => v.color))];
        const tallas  = [...new Set(variActivas.map(v => v.talla))];
        const badgeCat = catColor[p.categoria] || catColor['Otros'];
        const imgSrc = p.foto_url 
            ? `<img src="${p.foto_url}" alt="${p.nombre}" class="w-full h-32 object-cover rounded-t-xl">`
            : `<div class="w-full h-32 rounded-t-xl bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center text-gray-300 border-b border-gray-200">
                    <i class="fa-regular fa-image text-3xl mb-1"></i>
                    <span class="text-[10px]">Sin foto</span>
               </div>`;

        return `
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:border-violet-300 transition group">
                ${imgSrc}
                <div class="p-3.5 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <span class="font-mono text-[11px] font-bold text-bc-blue">${p.numero_pieza || '—'}</span>
                            <h4 class="font-bold text-gray-900 text-sm leading-tight mt-0.5 truncate" title="${p.nombre}">${p.nombre}</h4>
                        </div>
                        <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-semibold border ${badgeCat}">${p.categoria.split(' ')[0]}</span>
                    </div>

                    <div class="text-[11px] text-gray-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-layer-group text-violet-400"></i>
                        <span>${variActivas.length} variante(s)</span>
                        <span class="text-gray-300">·</span>
                        <i class="fa-solid fa-tshirt text-gray-400"></i>
                        <span>${p.tipo_tela}</span>
                    </div>

                    ${colores.length ? `
                    <div class="flex flex-wrap gap-1">
                        ${colores.slice(0,4).map(c => `<span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 text-[10px] border border-gray-200">${c}</span>`).join('')}
                        ${colores.length > 4 ? `<span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 text-[10px]">+${colores.length-4}</span>` : ''}
                    </div>` : ''}

                    ${tallas.length ? `
                    <div class="flex flex-wrap gap-1">
                        ${tallas.map(t => `<span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 text-[10px] font-semibold border border-blue-100">${t}</span>`).join('')}
                    </div>` : ''}

                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                        <span class="font-bold text-gray-900 text-sm">$${Number(p.precio_base).toLocaleString('es-MX', {minimumFractionDigits:2})}</span>
                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                            <button onclick="editarProducto(${p.id})" title="Editar" class="p-1.5 rounded bg-gray-100 hover:bg-blue-100 text-gray-500 hover:text-bc-blue transition">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </button>
                            <button onclick="abrirDetProducto(${p.id})" title="Ver variantes" class="p-1.5 rounded bg-gray-100 hover:bg-violet-100 text-gray-500 hover:text-violet-600 transition">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </button>
                            <button onclick="eliminarProductoCatalogo(${p.id})" title="Eliminar" class="p-1.5 rounded bg-gray-100 hover:bg-rose-100 text-gray-500 hover:text-rose-600 transition">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
    }).join('');
}

function filtrarCatalogo() {
    _pintarCatalogo();
}

// ─────────────────────────────────────────────────────────
// MODAL MULTI-PASO
// ─────────────────────────────────────────────────────────
function abrirModalProducto(id = null) {
    catalogoState.pasoActual = 1;
    catalogoState.variantes  = [];
    catalogoState.fotoBase64 = null;
    catalogoState.fotoNombre = null;
    catalogoState.fotoUrlManual = null;
    catalogoState.editandoId = id;

    // Reset form
    ['prod-nombre','prod-coleccion','prod-descripcion','prod-notas','prod-numero-pieza','foto-url-manual'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    const cat = document.getElementById('prod-categoria');
    if (cat) cat.value = '';
    document.getElementById('prod-precio').value = '0.00';
    document.getElementById('prod-minimo').value = '6';
    document.getElementById('prod-tipo-tela').value = 'SRA';
    limpiarFoto();
    _actualizarListaVariantes();

    if (id) {
        const prod = catalogoState.productos.find(p => p.id == id);
        if (prod) {
            document.getElementById('modal-prod-titulo').innerText = 'Editar Modelo: ' + prod.numero_pieza;
            document.getElementById('prod-nombre').value       = prod.nombre;
            document.getElementById('prod-categoria').value    = prod.categoria;
            document.getElementById('prod-numero-pieza').value = prod.numero_pieza || '';
            document.getElementById('prod-coleccion').value    = prod.coleccion || '';
            document.getElementById('prod-tipo-tela').value    = prod.tipo_tela || 'SRA';
            document.getElementById('prod-precio').value       = prod.precio_base || 0;
            document.getElementById('prod-minimo').value       = prod.stock_minimo || 6;
            document.getElementById('prod-descripcion').value  = prod.descripcion || '';
            document.getElementById('prod-notas').value        = prod.notas || '';
            catalogoState.variantes = (prod.variantes || []).filter(v => v.activo !== 0).map(v => ({...v}));
            _actualizarListaVariantes();

            if (prod.foto_url) {
                document.getElementById('foto-url-manual').value = prod.foto_url;
                previewUrlManual();
            }
        }
    } else {
        document.getElementById('modal-prod-titulo').innerText = 'Nuevo Modelo de Prenda';
    }

    _irAPaso(1);
    document.getElementById('modal-producto').classList.remove('hidden');
}

function cerrarModalProducto() {
    document.getElementById('modal-producto').classList.add('hidden');
}

function _irAPaso(num) {
    catalogoState.pasoActual = num;
    [1,2,3].forEach(n => {
        document.getElementById('paso-' + n)?.classList.toggle('hidden', n !== num);
        const ind = document.getElementById('step-indicator-' + n);
        const lbl = document.getElementById('step-label-' + n);
        if (ind) {
            if (n < num) {
                ind.className = 'w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shrink-0';
                ind.innerHTML = '<i class="fa-solid fa-check text-xs"></i>';
            } else if (n === num) {
                ind.className = 'w-7 h-7 rounded-full bg-bc-blue text-white flex items-center justify-center font-bold text-xs shrink-0';
                ind.textContent = n;
            } else {
                ind.className = 'w-7 h-7 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center font-bold text-xs shrink-0';
                ind.textContent = n;
            }
        }
        if (lbl) {
            lbl.className = n === num ? 'font-semibold text-bc-blue' : (n < num ? 'text-emerald-600 font-medium' : 'text-gray-400');
        }
    });

    const btnAtras    = document.getElementById('btn-paso-atras');
    const btnSig      = document.getElementById('btn-paso-siguiente');
    const btnGuardar  = document.getElementById('btn-guardar-producto');
    if (btnAtras)   btnAtras.classList.toggle('hidden', num === 1);
    if (btnSig)     btnSig.classList.toggle('hidden', num === 3);
    if (btnGuardar) btnGuardar.classList.toggle('hidden', num !== 3);
}

function cambiarPasoProducto(delta) {
    const actual = catalogoState.pasoActual;
    const nuevo  = actual + delta;

    // Validación paso 1
    if (actual === 1 && delta > 0) {
        const nombre = document.getElementById('prod-nombre').value.trim();
        const cat    = document.getElementById('prod-categoria').value;
        if (!nombre) { Swal.fire('Atención', 'El nombre del modelo es obligatorio.', 'warning'); return; }
        if (!cat)    { Swal.fire('Atención', 'Selecciona una categoría.', 'warning'); return; }
        // Si no tiene número de pieza, generar uno
        const np = document.getElementById('prod-numero-pieza').value.trim();
        if (!np) solicitarNumeroPieza();
    }

    // Validación paso 2
    if (actual === 2 && delta > 0) {
        if (catalogoState.variantes.length === 0) {
            const ok = confirm('No has agregado variantes. ¿Continuar sin variantes?');
            if (!ok) return;
        }
    }

    if (nuevo >= 1 && nuevo <= 3) _irAPaso(nuevo);
}

// ─────────────────────────────────────────────────────────
// NÚMERO DE PIEZA AUTO
// ─────────────────────────────────────────────────────────
async function solicitarNumeroPieza() {
    const cat = document.getElementById('prod-categoria').value;
    if (!cat) return;

    const input = document.getElementById('prod-numero-pieza');
    if (input.value.trim()) return; // ya tiene valor

    if (bcState.modoBackend === 'mysql') {
        try {
            const r = await fetchConSesion('backend/gestionar_productos.php?action=generar_numero&categoria=' + encodeURIComponent(cat));
            const j = await r.json();
            if (j.success) { input.value = j.data.numero_pieza; return; }
        } catch(e) {}
    }
    // Offline: generar local
    const mapa = { 'Gorros Quirúrgicos':'GQ','Chamarras Médicas':'CH','Cubrepolvos':'CP','Otros':'OT' };
    const cod  = mapa[cat] || 'OT';
    const num  = (catalogoState.productos.filter(p => (p.numero_pieza||'').startsWith(cod)).length + 1);
    input.value = `${cod}-NUE-${String(num).padStart(4,'0')}`;
}

// ─────────────────────────────────────────────────────────
// VERIFICAR NOMBRE DUPLICADO
// ─────────────────────────────────────────────────────────
let _verNombreTimer = null;
function verificarNombreProducto() {
    clearTimeout(_verNombreTimer);
    _verNombreTimer = setTimeout(async () => {
        const nombre = document.getElementById('prod-nombre').value.trim();
        const icon   = document.getElementById('nombre-dup-icon');
        const msg    = document.getElementById('nombre-dup-msg');
        if (!nombre) { msg.classList.add('hidden'); return; }

        // Verificar en estado local primero
        const existe = catalogoState.productos.some(p =>
            p.nombre.toLowerCase().trim() === nombre.toLowerCase().trim() &&
            p.id != catalogoState.editandoId
        );

        if (existe) {
            msg.classList.remove('hidden');
            return;
        }

        // Si hay backend, verificar también
        if (bcState.modoBackend === 'mysql') {
            try {
                const url = `backend/gestionar_productos.php?action=verificar_nombre&nombre=${encodeURIComponent(nombre)}&excluir_id=${catalogoState.editandoId || 0}`;
                const r = await fetch(url);
                const j = await r.json();
                msg.classList.toggle('hidden', j.data?.disponible !== false);
            } catch(e) { msg.classList.add('hidden'); }
        } else {
            msg.classList.add('hidden');
        }
    }, 400);
}

// ─────────────────────────────────────────────────────────
// VARIANTES
// ─────────────────────────────────────────────────────────
function agregarVarianteLista() {
    const color = document.getElementById('var-color').value.trim();
    const talla = document.getElementById('var-talla').value;
    if (!color) { Swal.fire('Atención','Escribe el nombre del color.','info'); return; }

    // Verificar duplicado
    const dup = catalogoState.variantes.find(v => v.color.toLowerCase() === color.toLowerCase() && v.talla === talla);
    if (dup) { Swal.fire('Duplicado',`Ya existe la variante ${color} / ${talla}.`,'warning'); return; }

    const np = document.getElementById('prod-numero-pieza').value.trim() || 'XX-NUE-0000';
    const colCod = color.replace(/[^a-zA-Z]/g,'').toUpperCase().slice(0,3);
    const talCod = talla.replace(/[^a-zA-Z0-9]/g,'').toUpperCase();
    const sku = `${np}-${colCod}-${talCod}`;

    catalogoState.variantes.push({ id: Date.now(), sku, color, talla, activo: 1, _nuevo: true });
    document.getElementById('var-color').value = '';
    _actualizarListaVariantes();
}

function setColorRapido(color) {
    document.getElementById('var-color').value = color;
    document.getElementById('var-color').focus();
}

function eliminarVarianteLista(idx) {
    catalogoState.variantes.splice(idx, 1);
    _actualizarListaVariantes();
}

function _actualizarListaVariantes() {
    const lista  = document.getElementById('variantes-lista');
    const vacio  = document.getElementById('variantes-vacio');
    const count  = document.getElementById('variantes-count');
    const activas = catalogoState.variantes.filter(v => v.activo !== 0);

    if (count) count.textContent = activas.length;

    if (activas.length === 0) {
        lista.innerHTML = '';
        lista.appendChild(document.getElementById('variantes-vacio'));
        vacio?.classList.remove('hidden');
        return;
    }
    vacio?.classList.add('hidden');

    lista.innerHTML = activas.map((v, i) => {
        const colores = { 'Azul': 'bg-blue-100 text-blue-700', 'Verde': 'bg-emerald-100 text-emerald-700', 'Lila': 'bg-violet-100 text-violet-700', 'Rosa': 'bg-pink-100 text-pink-700', 'Blanco': 'bg-gray-100 text-gray-700', 'Negro': 'bg-gray-800 text-white', 'Azul Marino': 'bg-indigo-100 text-indigo-700', 'Multi': 'bg-orange-100 text-orange-700' };
        const colorKey = Object.keys(colores).find(k => v.color.startsWith(k)) || 'Azul';
        const cls = colores[colorKey] || 'bg-gray-100 text-gray-700';
        return `
            <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full ${cls.split(' ')[0]} border border-gray-300 shrink-0"></span>
                    <span class="font-semibold text-gray-800">${v.color}</span>
                    <span class="text-gray-400">/</span>
                    <span class="font-semibold text-blue-600">${v.talla}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-[10px] text-gray-400">${v.sku}</span>
                    <button onclick="eliminarVarianteLista(${i})" class="text-rose-400 hover:text-rose-600 p-1 rounded hover:bg-rose-50">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
            </div>`;
    }).join('');
}

// ─────────────────────────────────────────────────────────
// FOTO / IMAGEN
// ─────────────────────────────────────────────────────────
function handleFotoChange(event) {
    const file = event.target.files[0];
    if (file) procesarFotoFile(file);
}

function handleFotoDrop(event) {
    event.preventDefault();
    document.getElementById('foto-dropzone').classList.remove('border-bc-blue','bg-blue-50');
    const file = event.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) procesarFotoFile(file);
}

function procesarFotoFile(file) {
    if (file.size > 5 * 1024 * 1024) {
        Swal.fire('Archivo muy grande', 'La imagen no debe superar 5 MB.', 'warning');
        return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
        catalogoState.fotoBase64 = e.target.result;
        catalogoState.fotoNombre = file.name;
        document.getElementById('foto-preview-img').src = e.target.result;
        document.getElementById('foto-nombre-txt').textContent = file.name + ' · ' + (file.size/1024).toFixed(0) + ' KB';
        document.getElementById('foto-preview-wrap').classList.remove('hidden');
        document.getElementById('foto-dropzone').classList.add('hidden');
    };
    reader.readAsDataURL(file);
}

function previewUrlManual() {
    const url = document.getElementById('foto-url-manual').value.trim();
    if (!url) return;
    catalogoState.fotoUrlManual = url;
    document.getElementById('foto-preview-img').src = url;
    document.getElementById('foto-nombre-txt').textContent = 'URL externa';
    document.getElementById('foto-preview-wrap').classList.remove('hidden');
    document.getElementById('foto-dropzone').classList.add('hidden');
}

function limpiarFoto() {
    catalogoState.fotoBase64 = null;
    catalogoState.fotoNombre = null;
    catalogoState.fotoUrlManual = null;
    document.getElementById('foto-preview-img').src = '';
    document.getElementById('foto-preview-wrap').classList.add('hidden');
    document.getElementById('foto-dropzone').classList.remove('hidden');
    document.getElementById('foto-input').value = '';
    const urlEl = document.getElementById('foto-url-manual');
    if (urlEl) urlEl.value = '';
}

// ─────────────────────────────────────────────────────────
// GUARDAR PRODUCTO COMPLETO
// ─────────────────────────────────────────────────────────
async function guardarProducto() {
    const nombre    = document.getElementById('prod-nombre').value.trim();
    const categoria = document.getElementById('prod-categoria').value;
    const np        = document.getElementById('prod-numero-pieza').value.trim();
    const precio    = parseFloat(document.getElementById('prod-precio').value) || 0;
    const minimo    = parseInt(document.getElementById('prod-minimo').value) || 6;

    if (!nombre || !categoria) {
        Swal.fire('Campos incompletos', 'Nombre y Categoría son obligatorios.', 'warning');
        _irAPaso(1); return;
    }

    // Determinar foto_url final
    let fotoUrl = catalogoState.fotoUrlManual || null;

    // Si hay base64 e internet, subir a Cloudinary
    if (catalogoState.fotoBase64 && bcState.modoBackend === 'mysql') {
        try {
            const r = await fetchConSesion('backend/gestionar_productos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'subir_foto',
                    base64: catalogoState.fotoBase64,
                    product_id: catalogoState.editandoId || 0,
                    tipo: document.getElementById('foto-tipo').value,
                    nombre_archivo: catalogoState.fotoNombre || 'foto'
                })
            });
            const j = await r.json();
            if (j.success) fotoUrl = j.data.url;
        } catch(e) {
            // Guardar base64 localmente si falla el upload
            fotoUrl = catalogoState.fotoBase64;
        }
    } else if (catalogoState.fotoBase64) {
        fotoUrl = catalogoState.fotoBase64; // modo standalone: guardar como data URL
    }

    const payload = {
        action: catalogoState.editandoId ? 'actualizar' : 'crear',
        id: catalogoState.editandoId || undefined,
        nombre, categoria,
        numero_pieza: np,
        coleccion: document.getElementById('prod-coleccion').value.trim() || 'General',
        tipo_tela:  document.getElementById('prod-tipo-tela').value,
        precio_base: precio,
        stock_minimo: minimo,
        descripcion: document.getElementById('prod-descripcion').value.trim(),
        notas: document.getElementById('prod-notas').value.trim(),
        foto_url: fotoUrl,
        variantes: catalogoState.variantes.filter(v => v.activo !== 0)
    };

    if (bcState.modoBackend === 'mysql') {
        try {
            const r = await fetchConSesion('backend/gestionar_productos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const j = await r.json();
            if (!j.success) {
                Swal.fire('Error', j.message || 'No se pudo guardar.', 'error');
                return;
            }
            cerrarModalProducto();
            renderizarCatalogo();
            Swal.fire({ icon: 'success', title: '¡Guardado!', text: j.message, timer: 2000, showConfirmButton: false });
            return;
        } catch(e) { /* fallthrough a local */ }
    }

    // MODO LOCAL
    if (catalogoState.editandoId) {
        const idx = catalogoState.productos.findIndex(p => p.id == catalogoState.editandoId);
        if (idx !== -1) {
            catalogoState.productos[idx] = { ...catalogoState.productos[idx], ...payload, variantes: payload.variantes };
        }
    } else {
        const newId = Date.now();
        catalogoState.productos.push({
            id: newId,
            numero_pieza: np,
            nombre, categoria,
            coleccion: payload.coleccion,
            tipo_tela: payload.tipo_tela,
            precio_base: precio,
            stock_minimo: minimo,
            descripcion: payload.descripcion,
            foto_url: fotoUrl,
            notas: payload.notas,
            variantes: payload.variantes.map((v,i) => ({ ...v, id: Date.now()+i }))
        });
    }

    _guardarCatalogoLocal();
    cerrarModalProducto();
    _pintarCatalogo();
    Swal.fire({ icon: 'success', title: 'Guardado localmente', text: `Modelo "${nombre}" guardado con ${payload.variantes.length} variante(s).`, timer: 2000, showConfirmButton: false });
}

// ─────────────────────────────────────────────────────────
// EDITAR / VER / ELIMINAR
// ─────────────────────────────────────────────────────────
function editarProducto(id) {
    abrirModalProducto(id);
}

function abrirDetProducto(id) {
    const prod = catalogoState.productos.find(p => p.id == id);
    if (!prod) return;
    const vars = (prod.variantes || []).filter(v => v.activo !== 0);
    const rows = vars.map(v => `
        <tr class="hover:bg-gray-50">
            <td class="px-3 py-2 font-mono text-xs text-bc-blue font-bold">${v.sku}</td>
            <td class="px-3 py-2 text-xs text-gray-800">${v.color}</td>
            <td class="px-3 py-2 text-xs text-center"><span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 font-semibold border border-blue-100 text-[11px]">${v.talla}</span></td>
            <td class="px-3 py-2 text-xs text-center text-gray-500">${v.precio_variante ? '$'+Number(v.precio_variante).toFixed(2) : '—'}</td>
        </tr>`).join('');

    Swal.fire({
        title: prod.nombre,
        html: `
            <div class="text-left text-xs">
                <p class="text-gray-500 mb-3"><span class="font-mono font-bold text-blue-600">${prod.numero_pieza}</span> · ${prod.categoria} · ${prod.tipo_tela}</p>
                ${prod.foto_url ? `<img src="${prod.foto_url}" class="w-full h-32 object-contain rounded-lg mb-3 border border-gray-200">` : ''}
                <table class="w-full border border-gray-200 rounded-lg overflow-hidden text-left">
                    <thead class="bg-gray-50 text-[10px] uppercase text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-3 py-2">SKU</th>
                            <th class="px-3 py-2">Color</th>
                            <th class="px-3 py-2 text-center">Talla</th>
                            <th class="px-3 py-2 text-center">Precio</th>
                        </tr>
                    </thead>
                    <tbody>${rows || '<tr><td colspan="4" class="text-center p-4 text-gray-400">Sin variantes</td></tr>'}</tbody>
                </table>
                <p class="text-gray-400 mt-2">${vars.length} variante(s) · Stock mínimo: ${prod.stock_minimo} pzas</p>
            </div>`,
        confirmButtonColor: '#0078d4',
        confirmButtonText: 'Cerrar',
        width: '500px'
    });
}

async function eliminarProductoCatalogo(id) {
    const conf = await Swal.fire({
        title: '¿Eliminar modelo?',
        text: 'Se desactivará el modelo y todas sus variantes.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });
    if (!conf.isConfirmed) return;

    if (bcState.modoBackend === 'mysql') {
        try {
            const r = await fetchConSesion('backend/gestionar_productos.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'eliminar', id })
            });
            const j = await r.json();
            if (j.success) { renderizarCatalogo(); return; }
        } catch(e) {}
    }

    catalogoState.productos = catalogoState.productos.filter(p => p.id != id);
    _guardarCatalogoLocal();
    _pintarCatalogo();
}
