/* ============================================================
   PERMISOS UI — DELATEL
   Aplica permisos de rol reactivamente al DOM.
   Se ejecuta después de que carga la vista.
   ============================================================ */

(function aplicarPermisosUI() {
    const P   = window.PERMISOS   || {};
    const ROL = window.ROL_NIVEL  || 99;
    const vista = window.VISTA_ACTUAL || '';

    // ============================================================
    // OCULTAR / BLOQUEAR BOTONES SEGÚN PERMISOS
    // Convención: añadir data-perm="modulo.accion" a los botones
    // Ejemplo: <button data-perm="productos.crear">Registrar</button>
    // ============================================================
    document.querySelectorAll('[data-perm]').forEach(el => {
        const [modulo, accion] = (el.dataset.perm || '').split('.');
        if (!modulo || !accion) return;
        const permisoModulo = P[modulo] || {};
        if (!permisoModulo[accion]) {
            el.classList.add('perm-hidden');
        }
    });

    // ============================================================
    // OCULTAR FORMULARIOS COMPLETOS QUE REQUIEREN PERMISO
    // Convención: data-perm-form="modulo.crear"
    // ============================================================
    document.querySelectorAll('[data-perm-form]').forEach(el => {
        const [modulo, accion] = (el.dataset.permForm || '').split('.');
        if (!modulo || !accion) return;
        const permisoModulo = P[modulo] || {};
        if (!permisoModulo[accion]) {
            el.style.display = 'none';
        }
    });

    // ============================================================
    // CONTROL DE ACCIONES EN TABLAS
    // ============================================================

    // PRODUCTOS: ocultar botones Editar/Desactivar/Reactivar si no tiene permiso
    if (vista === 'productos') {
        const pProd = P['productos'] || {};

        if (!pProd.editar) {
            document.querySelectorAll('[data-accion="editar-producto"]').forEach(b => b.closest('td')
                ? bloquearBoton(b) : b.classList.add('perm-hidden'));
        }
        if (!pProd.eliminar) {
            document.querySelectorAll('[data-accion="desactivar-producto"], [data-accion="reactivar-producto"]')
                .forEach(b => bloquearBoton(b));
        }
        if (!pProd.crear) {
            const formRegistrar = document.querySelector('[data-perm-section="productos-crear"]');
            if (formRegistrar) formRegistrar.style.display = 'none';
        }
    }

    // KARDEX: bloquear si no puede crear
    if (vista === 'kardex') {
        const pK = P['kardex'] || {};
        if (!pK.crear) {
            const formK = document.querySelector('[data-perm-section="kardex-crear"]');
            if (formK) {
                formK.innerHTML = bannerRestringido('Registrar movimientos de kardex requiere permiso de creación.');
            }
        }
        // Ocultar botones de exportar si no puede ver
        if (!pK.ver) {
            document.querySelectorAll('[data-accion="exportar-kardex"]').forEach(b => b.classList.add('perm-hidden'));
        }
    }

    // HISTORIAL: ocultar exportar si no puede
    if (vista === 'historial') {
        const pH = P['historial'] || {};
        if (!pH.crear) {
            document.querySelectorAll('[data-accion="exportar-historial"]').forEach(b => b.classList.add('perm-hidden'));
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================

    function bloquearBoton(el) {
        el.disabled = true;
        el.classList.add('perm-locked-btn');
        el.title = 'Sin permiso para esta acción';
        el.setAttribute('aria-disabled', 'true');
    }

    function bannerRestringido(msg) {
        return `
        <div class="alert d-flex align-items-center gap-3 mb-0" style="
            background:#fef9ee;border:1px solid #fde68a;border-radius:12px;padding:16px 20px;">
            <i class="bi bi-lock-fill fs-5" style="color:#f59e0b;flex-shrink:0"></i>
            <div>
                <div class="fw-bold" style="color:#92400e;font-size:.875rem">Acción restringida</div>
                <div class="text-muted" style="font-size:.8rem">${msg}</div>
            </div>
        </div>`;
    }
})();
