/**
 * app.js — For Life S.A.
 * Toggle contraseña, validación en tiempo real y modal de confirmación.
 */

document.addEventListener('DOMContentLoaded', () => {
  initPasswordToggles();
  initRegistroValidation();
  initAdminModal();
});

/* =========================================
   TOGGLE MOSTRAR / OCULTAR CONTRASEÑA
   Aplica a todos los inputs type="password"
   ========================================= */
function initPasswordToggles() {
  document.querySelectorAll('input[type="password"]').forEach(input => {
    const wrap = document.createElement('div');
    wrap.className = 'campo-password-wrap';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-toggle-password';
    btn.setAttribute('aria-label', 'Mostrar u ocultar contraseña');
    btn.innerHTML = svgOjo(false);
    wrap.appendChild(btn);

    btn.addEventListener('click', () => {
      const mostrar = input.type === 'password';
      input.type = mostrar ? 'text' : 'password';
      btn.innerHTML = svgOjo(mostrar);
    });
  });
}

function svgOjo(visible) {
  if (visible) {
    return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8
               a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4
               c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19
               m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
      <line x1="1" y1="1" x2="23" y2="23"/>
    </svg>`;
  }
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
    <circle cx="12" cy="12" r="3"/>
  </svg>`;
}

/* =========================================
   VALIDACIÓN EN TIEMPO REAL — REGISTRO
   Solo activa si existe el campo #apellido1
   ========================================= */
function initRegistroValidation() {
  const form = document.querySelector('form.formulario');
  if (!form || !document.getElementById('apellido1')) return;

  const f = {
    usuario:   document.getElementById('usuario'),
    nombre:    document.getElementById('nombre'),
    apellido1: document.getElementById('apellido1'),
    apellido2: document.getElementById('apellido2'),
    password:  document.getElementById('password'),
    confirmar: document.getElementById('confirmar'),
  };

  // Validación en vivo mientras el usuario escribe
  f.usuario?.addEventListener('input', () => validarUsuario(f.usuario));
  f.usuario?.addEventListener('blur',  () => validarUsuario(f.usuario));

  f.nombre?.addEventListener('blur', () =>
    validarRequerido(f.nombre, 'El nombre es obligatorio.'));

  f.apellido1?.addEventListener('blur', () =>
    validarRequerido(f.apellido1, 'El primer apellido es obligatorio.'));

  f.apellido2?.addEventListener('blur', () =>
    validarRequerido(f.apellido2, 'El segundo apellido es obligatorio.'));

  f.password?.addEventListener('input', () => {
    validarPassword(f.password);
    if (f.confirmar?.value) validarConfirmar(f.confirmar, f.password);
  });

  f.confirmar?.addEventListener('input', () => validarConfirmar(f.confirmar, f.password));
  f.confirmar?.addEventListener('blur',  () => validarConfirmar(f.confirmar, f.password));

  // Bloquear envío si algún campo falla
  form.addEventListener('submit', e => {
    let valido = true;
    if (!validarUsuario(f.usuario))                                   valido = false;
    if (!validarRequerido(f.nombre,    'El nombre es obligatorio.'))  valido = false;
    if (!validarRequerido(f.apellido1, 'El primer apellido es obligatorio.')) valido = false;
    if (!validarRequerido(f.apellido2, 'El segundo apellido es obligatorio.')) valido = false;
    if (!validarPassword(f.password))                                 valido = false;
    if (!validarConfirmar(f.confirmar, f.password))                   valido = false;

    if (!valido) {
      e.preventDefault();
      form.querySelector('.campo-error input')?.focus();
    }
  });
}

function validarUsuario(input) {
  const v = input.value.trim();
  if (v.length < 4)                         return error(input, 'Mínimo 4 caracteres.');
  if (v.length > 50)                        return error(input, 'Máximo 50 caracteres.');
  if (!/^[a-zA-Z0-9_]+$/.test(v))          return error(input, 'Solo letras, números y guión bajo (_).');
  return valido(input);
}

function validarRequerido(input, msg) {
  if (!input.value.trim()) return error(input, msg);
  return valido(input);
}

function validarPassword(input) {
  if (input.value.length < 8) return error(input, 'Mínimo 8 caracteres.');
  return valido(input);
}

function validarConfirmar(inputC, inputP) {
  if (!inputC.value)               return error(inputC, 'Confirme su contraseña.');
  if (inputC.value !== inputP.value) return error(inputC, 'Las contraseñas no coinciden.');
  return valido(inputC);
}

/* =========================================
   HELPERS DE FEEDBACK VISUAL EN CAMPOS
   ========================================= */
function error(input, mensaje) {
  const campo = input.closest('.campo');
  if (!campo) return false;

  campo.classList.add('campo-error');
  campo.classList.remove('campo-valido');

  let msg = campo.querySelector('.campo-mensaje-error');
  if (!msg) {
    msg = document.createElement('span');
    msg.className = 'campo-mensaje-error';
    const ref = input.closest('.campo-password-wrap') || input;
    ref.insertAdjacentElement('afterend', msg);
  }
  msg.textContent = mensaje;
  return false;
}

function valido(input) {
  const campo = input.closest('.campo');
  if (!campo) return true;

  campo.classList.remove('campo-error');
  campo.classList.add('campo-valido');
  campo.querySelector('.campo-mensaje-error')?.remove();
  return true;
}

/* =========================================
   MODAL DE CONFIRMACIÓN — PANEL ADMIN
   Reemplaza el confirm() nativo del navegador
   ========================================= */
function initAdminModal() {
  const botones = document.querySelectorAll('.btn-accion');
  if (!botones.length) return;

  botones.forEach(btn => {
    const form = btn.closest('form');
    if (!form) return;

    // Quitar el confirm() nativo
    btn.removeAttribute('onclick');

    btn.addEventListener('click', e => {
      e.preventDefault();

      const accion       = form.querySelector('input[name="accion"]')?.value || 'desactivar';
      const esDesactivar = accion === 'desactivar';
      const fila         = btn.closest('tr');
      const nombreCelda  = fila?.cells[2]?.textContent?.trim() || 'este usuario';

      mostrarModal({
        accion,
        esDesactivar,
        nombreUsuario: nombreCelda,
        onConfirmar: () => form.submit(),
      });
    });
  });
}

function mostrarModal({ accion, esDesactivar, nombreUsuario, onConfirmar }) {
  document.getElementById('modal-confirmacion')?.remove();

  const verbo       = esDesactivar ? 'desactivar' : 'activar';
  const icono       = esDesactivar ? '⚠️' : '✅';
  const iconoClase  = esDesactivar ? '' : 'modal-icono-activar';
  const consecuencia = esDesactivar
    ? 'El usuario no podrá ingresar al sistema hasta que sea reactivado.'
    : 'El usuario recuperará acceso completo al sistema.';

  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay';
  overlay.id = 'modal-confirmacion';
  overlay.setAttribute('role', 'dialog');
  overlay.setAttribute('aria-modal', 'true');

  overlay.innerHTML = `
    <div class="modal-card">
      <div class="modal-icono ${iconoClase}">${icono}</div>
      <h3>¿Confirmar acción?</h3>
      <p>
        Está a punto de <strong>${verbo}</strong> a
        <strong>${nombreUsuario}</strong>.<br><br>
        ${consecuencia}
      </p>
      <div class="modal-divider"></div>
      <div class="modal-botones">
        <button class="modal-btn-cancelar" id="modal-cancelar">Cancelar</button>
        <button class="modal-btn-confirmar ${accion}" id="modal-confirmar">
          ${esDesactivar ? 'Desactivar' : 'Activar'}
        </button>
      </div>
    </div>
  `;

  document.body.appendChild(overlay);
  overlay.querySelector('#modal-confirmar').focus();

  overlay.querySelector('#modal-cancelar').addEventListener('click', cerrarModal);
  overlay.querySelector('#modal-confirmar').addEventListener('click', () => {
    cerrarModal();
    onConfirmar();
  });

  // Click fuera de la tarjeta cierra el modal
  overlay.addEventListener('click', e => { if (e.target === overlay) cerrarModal(); });

  // Tecla Escape
  document.addEventListener('keydown', escapeHandler);
}

function cerrarModal() {
  document.getElementById('modal-confirmacion')?.remove();
  document.removeEventListener('keydown', escapeHandler);
}

function escapeHandler(e) {
  if (e.key === 'Escape') cerrarModal();
}
