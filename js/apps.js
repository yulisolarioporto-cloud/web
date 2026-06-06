(function mostrarAlerta() {
    const params = new URLSearchParams(window.location.search);
    const msg    = params.get('msg');
    const container = document.getElementById('alerta-container');
    if (!msg || !container) return;

    const mensajes = {
        registrado:  ['success', 'bi-check-circle-fill', 'Producto registrado correctamente'],
        actualizado: ['success', 'bi-check-circle-fill', 'Producto actualizado correctamente'],
        desactivado: ['warning', 'bi-exclamation-triangle-fill', 'Producto desactivado'],
        reactivado:  ['success', 'bi-check-circle-fill', 'Producto reactivado'],
        entrada:     ['success', 'bi-arrow-down-circle-fill', 'Entrada registrada en Kardex'],
        salida:      ['success', 'bi-arrow-up-circle-fill', 'Salida registrada en Kardex'],
        sin_stock:   ['danger',  'bi-x-circle-fill', 'Stock insuficiente para esta operación'],
        vendido:     ['success', 'bi-bag-check-fill', '¡Venta registrada con éxito!'],
        usuario_registrado: ['success', 'bi-person-check-fill', 'Usuario registrado correctamente'],
        error:       ['danger',  'bi-x-circle-fill', 'Error: ' + (() => {
            const d = params.get('detalle');
            if (d === 'producto_duplicado') return 'Ya existe un producto con ese nombre';
            if (d === 'datos_invalidos')    return 'Datos inválidos, revisa el formulario';
            if (d === 'usuario_duplicado')  return 'El usuario o email ya está registrado';
            if (d === 'sin_permiso')        return 'No tienes permiso para esta acción';
            return d || 'Operación fallida';
        })()],
    };

    if (!mensajes[msg]) return;
    const [tipo, icono, texto] = mensajes[msg];
    container.innerHTML = `
        <div class="alert alert-${tipo} alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi ${icono}"></i> <span>${texto}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>`;
    setTimeout(() => container.innerHTML = '', 4500);
})();

document.getElementById('buscador')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.tarjeta-producto').forEach(card => {
        const modelo = card.getAttribute('data-modelo') || '';
        card.classList.toggle('d-none', !modelo.includes(q));
    });
});

document.getElementById('buscador-productos')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.fila-producto').forEach(row => {
        const modelo = row.getAttribute('data-modelo') || '';
        row.classList.toggle('d-none', !modelo.includes(q));
    });
});

document.querySelector('[name="precio"]')?.addEventListener('input', function () {
    if (this.value < 0) this.value = 0;
});

let carrito = [];

function agregarAlCarrito(id, modelo, precio, stock) {
    const existe = carrito.find(i => i.id === id);
    if (existe) {
        if (existe.cantidad >= stock) {
            mostrarToast('Stock máximo alcanzado para este producto', 'warning');
            return;
        }
        existe.cantidad++;
    } else {
        carrito.push({ id, modelo, precio, cantidad: 1, stock });
    }
    renderCarrito();
}

function cambiarCantidad(id, delta) {
    const item = carrito.find(i => i.id === id);
    if (!item) return;

    const nueva = item.cantidad + delta;
    if (nueva > item.stock) {
        mostrarToast('Sin más stock disponible', 'warning');
        return;
    }
    if (nueva <= 0) {
        carrito = carrito.filter(i => i.id !== id);
    } else {
        item.cantidad = nueva;
    }
    renderCarrito();
}

function renderCarrito() {
    const cont   = document.getElementById('items-carrito');
    const txtTot = document.getElementById('total-carrito');
    const btnV   = document.getElementById('btn-procesar-venta');
    if (!cont || !txtTot || !btnV) return;

    if (carrito.length === 0) {
        cont.innerHTML = `<div class="text-center text-muted py-4">
            <i class="bi bi-cart-x fs-1 d-block mb-2"></i>Carrito vacío</div>`;
        txtTot.textContent = '0.00';
        btnV.disabled = true;
        return;
    }

    let total = 0;
    cont.innerHTML = carrito.map(item => {
        const sub = item.precio * item.cantidad;
        total += sub;
        return `
        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom gap-2">
            <div style="flex:1;min-width:0">
                <div class="fw-semibold text-truncate small" title="${item.modelo}">${item.modelo}</div>
                <div class="text-muted" style="font-size:.8rem">S/. ${item.precio.toFixed(2)} c/u</div>
            </div>
            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                <button class="btn btn-sm btn-outline-secondary py-0 px-2"
                        onclick="cambiarCantidad(${item.id}, -1)">−</button>
                <span class="fw-bold px-1">${item.cantidad}</span>
                <button class="btn btn-sm btn-outline-secondary py-0 px-2"
                        onclick="cambiarCantidad(${item.id}, 1)">+</button>
                <span class="fw-bold text-success ms-1" style="min-width:70px;text-align:right">
                    S/. ${sub.toFixed(2)}
                </span>
            </div>
        </div>`;
    }).join('');

    txtTot.textContent = total.toFixed(2);
    btnV.disabled = false;
}

async function procesarVenta() {
    if (carrito.length === 0) return;
    const btn = document.getElementById('btn-procesar-venta');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando…';

    try {
        const res  = await fetch('php/ventas.php?action=vender_carrito', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(carrito)
        });
        const data = await res.json();

        if (data.status === 'success') {
            carrito = [];
            renderCarrito();
            setTimeout(() => window.location.href = 'index.php?vista=ventas&msg=vendido', 600);
        } else {
            mostrarToast('Error: ' + (data.message || 'No se pudo procesar la venta'), 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-bag-check-fill"></i> Realizar Venta';
        }
    } catch (err) {
        mostrarToast('Error de conexión con el servidor', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-bag-check-fill"></i> Realizar Venta';
    }
}

function mostrarToast(mensaje, tipo = 'info') {
    const id = 'toast-' + Date.now();
    const colors = { success: '#198754', danger: '#dc3545', warning: '#fd7e14', info: '#0d6efd' };
    const el = document.createElement('div');
    el.id = id;
    el.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;
        background:${colors[tipo]};color:#fff;padding:12px 20px;border-radius:12px;
        font-weight:600;box-shadow:0 6px 20px rgba(0,0,0,0.2);animation:fadeInUp .3s ease`;
    el.textContent = mensaje;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3200);
}
