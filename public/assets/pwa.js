(function registerServiceWorker() {
  if (!('serviceWorker' in navigator)) {
    return;
  }

  window.addEventListener('load', () => {
    navigator.serviceWorker
      .register('/project/public/service-worker.js')
      .then(registration => {
        console.info('[SADDO] Service Worker registrado', registration.scope);
      })
      .catch(error => {
        console.error('[SADDO] Error al registrar el Service Worker', error);
      });
  });
})();

let deferredInstallPrompt = null;

window.addEventListener('beforeinstallprompt', event => {
  event.preventDefault();
  deferredInstallPrompt = event;
  const installButton = document.querySelector('[data-install-app]');

  if (!installButton) {
    return;
  }

  installButton.hidden = false;
  installButton.disabled = false;
});

document.addEventListener('click', async event => {
  const target = event.target;
  if (!(target instanceof HTMLElement)) {
    return;
  }

  if (!target.matches('[data-install-app]')) {
    return;
  }

  if (!deferredInstallPrompt) {
    return;
  }

  target.disabled = true;
  deferredInstallPrompt.prompt();
  const { outcome } = await deferredInstallPrompt.userChoice;
  console.info(`[SADDO] Resultado de la instalación: ${outcome}`);
  deferredInstallPrompt = null;
  target.hidden = true;
});