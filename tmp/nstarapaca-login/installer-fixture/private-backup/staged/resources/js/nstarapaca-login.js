(function () {
  'use strict';
  window.acceder = async function (event) {
    event.preventDefault();
    var button = document.getElementById('btnAcceder');
    var message = document.getElementById('errorLogin');
    var user = document.getElementById('usr').value.trim();
    var password = document.getElementById('pass').value;
    if (button.disabled) return;
    message.innerText = '';
    if (!user || !password) { message.innerText = 'Ingrese usuario y contraseña.'; return; }
    button.disabled = true;
    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Validando acceso...</span>';
    var abort = new AbortController();
    var timer = setTimeout(function () { abort.abort(); }, 15000);
    var accepted = false;
    try {
      var response = await fetch('/controllers/validarLogin.php', {
        method: 'POST', credentials: 'same-origin', signal: abort.signal,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', Accept: 'application/json' },
        body: new URLSearchParams({ nameUser: user, namePass: password }).toString()
      });
      var result;
      try { result = await response.json(); }
      catch (error) { throw new Error('El servidor devolvió una respuesta no válida. Revise la conexión de la base de datos.'); }
      if (!result || typeof result.isLogin !== 'boolean') throw new Error('La respuesta de ingreso del servidor no es válida.');
      if (!response.ok || !result.isLogin) throw new Error(result.message || 'No se pudo iniciar sesión.');
      try {
        sessionStorage.setItem('hostUrl', location.origin);
        sessionStorage.setItem('HostUrl', location.origin);
      } catch (storageError) { /* The server session remains authoritative. */ }
      var destination = result.redirect || '/views/V_VentaForm.php';
      if (destination.charAt(0) !== '/' || destination.slice(0, 2) === '//') destination = '/views/V_VentaForm.php';
      button.innerHTML = '<i class="fa-solid fa-circle-check"></i><span>Acceso concedido</span>';
      accepted = true;
      location.assign(destination);
    } catch (error) {
      message.innerText = error.name === 'AbortError'
        ? 'El servidor tardó demasiado en responder. Intente nuevamente.'
        : (error.message || 'No se pudo conectar con el servidor. Intente nuevamente.');
    } finally {
      clearTimeout(timer);
      if (!accepted) {
        button.disabled = false;
        button.innerHTML = '<span>Ingresar al ERP</span><i class="fa-solid fa-arrow-right-long"></i>';
      }
    }
  };
}());
