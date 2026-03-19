(function () {
  var deferredInstallPrompt = null;

  function injectStyles() {
    if (document.getElementById('anako-pwa-ui-style')) {
      return;
    }

    var style = document.createElement('style');
    style.id = 'anako-pwa-ui-style';
    style.textContent = [
      '#anako-offline-banner {',
      '  position: fixed;',
      '  top: 0;',
      '  left: 0;',
      '  right: 0;',
      '  z-index: 1100;',
      '  background: #b91c1c;',
      '  color: #ffffff;',
      '  text-align: center;',
      '  font-size: 14px;',
      '  font-weight: 600;',
      '  padding: 10px 12px;',
      '  display: none;',
      '  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);',
      '}',
      '#anako-install-btn {',
      '  position: fixed;',
      '  right: 16px;',
      '  bottom: 16px;',
      '  z-index: 1090;',
      '  border: none;',
      '  border-radius: 999px;',
      '  background: #02a75a;',
      '  color: #ffffff;',
      '  font-weight: 700;',
      '  font-size: 14px;',
      '  padding: 12px 18px;',
      '  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);',
      '  cursor: pointer;',
      '  display: none;',
      '}',
      '#anako-install-btn:hover {',
      '  background: #018a4a;',
      '}',
      '@media (max-width: 576px) {',
      '  #anako-install-btn {',
      '    left: 12px;',
      '    right: 12px;',
      '    width: auto;',
      '    text-align: center;',
      '  }',
      '}'
    ].join('\n');

    document.head.appendChild(style);
  }

  function createOfflineBanner() {
    if (document.getElementById('anako-offline-banner')) {
      return;
    }

    var banner = document.createElement('div');
    banner.id = 'anako-offline-banner';
    banner.setAttribute('role', 'status');
    banner.setAttribute('aria-live', 'polite');
    banner.textContent = 'You are offline. Some features may be unavailable.';
    document.body.appendChild(banner);
  }

  function updateOfflineBanner() {
    var banner = document.getElementById('anako-offline-banner');
    if (!banner) {
      return;
    }

    banner.style.display = navigator.onLine ? 'none' : 'block';
  }

  function createInstallButton() {
    if (document.getElementById('anako-install-btn')) {
      return;
    }

    var button = document.createElement('button');
    button.id = 'anako-install-btn';
    button.type = 'button';
    button.textContent = 'Install App';

    button.addEventListener('click', function () {
      if (!deferredInstallPrompt) {
        return;
      }

      deferredInstallPrompt.prompt();
      deferredInstallPrompt.userChoice.finally(function () {
        deferredInstallPrompt = null;
        button.style.display = 'none';
      });
    });

    document.body.appendChild(button);
  }

  function setInstallButtonVisible(visible) {
    var button = document.getElementById('anako-install-btn');
    if (!button) {
      return;
    }

    button.style.display = visible ? 'inline-block' : 'none';
  }

  window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    deferredInstallPrompt = event;
    setInstallButtonVisible(true);
  });

  window.addEventListener('appinstalled', function () {
    deferredInstallPrompt = null;
    setInstallButtonVisible(false);
  });

  window.addEventListener('online', updateOfflineBanner);
  window.addEventListener('offline', updateOfflineBanner);

  window.addEventListener('DOMContentLoaded', function () {
    injectStyles();
    createOfflineBanner();
    createInstallButton();
    updateOfflineBanner();
  });
})();
