(function () {
  if (!('serviceWorker' in navigator)) {
    return;
  }

  window.addEventListener('load', function () {
    var basePath = window.ANAKO_APP_BASE || '';
    var swUrl = basePath + '/service-worker.js';
    var scope = (basePath || '') + '/';

    navigator.serviceWorker.register(swUrl, { scope: scope }).catch(function (err) {
      console.error('Service worker registration failed:', err);
    }).then(function (registration) {
      window.__ANAKO_PWA_SW_REGISTRATION__ = registration || null;
      window.dispatchEvent(new CustomEvent('anako:pwa-sw-registered', {
        detail: { registration: registration || null }
      }));
    });
  });
})();
