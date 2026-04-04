(function () {
  var deferredInstallPrompt = null;
  var checkerState = null;
  var checkerPanelOpen = false;

  function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  }

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
      '#anako-pwa-checker-btn {',
      '  position: fixed;',
      '  left: 16px;',
      '  bottom: 16px;',
      '  z-index: 1089;',
      '  border: 1px solid #cbd5e1;',
      '  border-radius: 999px;',
      '  background: #ffffff;',
      '  color: #0f172a;',
      '  font-weight: 700;',
      '  font-size: 13px;',
      '  padding: 10px 14px;',
      '  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);',
      '  cursor: pointer;',
      '}',
      '#anako-pwa-checker-panel {',
      '  position: fixed;',
      '  left: 16px;',
      '  bottom: 62px;',
      '  width: min(360px, calc(100vw - 32px));',
      '  background: #ffffff;',
      '  color: #0f172a;',
      '  border: 1px solid #cbd5e1;',
      '  border-radius: 12px;',
      '  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);',
      '  z-index: 1092;',
      '  display: none;',
      '  overflow: hidden;',
      '}',
      '#anako-pwa-checker-panel header {',
      '  display: flex;',
      '  align-items: center;',
      '  justify-content: space-between;',
      '  gap: 8px;',
      '  background: #f8fafc;',
      '  padding: 10px 12px;',
      '  border-bottom: 1px solid #e2e8f0;',
      '}',
      '#anako-pwa-checker-panel header strong {',
      '  font-size: 13px;',
      '}',
      '#anako-pwa-checker-close {',
      '  border: none;',
      '  background: transparent;',
      '  font-size: 18px;',
      '  line-height: 1;',
      '  cursor: pointer;',
      '  color: #334155;',
      '}',
      '#anako-pwa-checker-body {',
      '  padding: 12px;',
      '  font-size: 13px;',
      '}',
      '.anako-checker-summary {',
      '  margin-bottom: 10px;',
      '  font-weight: 600;',
      '}',
      '.anako-checker-list {',
      '  list-style: none;',
      '  padding: 0;',
      '  margin: 0;',
      '  display: grid;',
      '  gap: 8px;',
      '}',
      '.anako-checker-item {',
      '  display: flex;',
      '  align-items: flex-start;',
      '  gap: 8px;',
      '  padding: 8px 10px;',
      '  border-radius: 8px;',
      '  background: #f8fafc;',
      '}',
      '.anako-checker-item.pass { border-left: 4px solid #16a34a; }',
      '.anako-checker-item.fail { border-left: 4px solid #dc2626; }',
      '.anako-checker-item.warn { border-left: 4px solid #f59e0b; }',
      '.anako-checker-label { font-weight: 700; }',
      '.anako-checker-desc { color: #475569; font-size: 12px; margin-top: 2px; }',
      '.anako-checker-icon { width: 18px; flex: 0 0 18px; text-align: center; }',
      '@media (max-width: 576px) {',
      '  #anako-pwa-checker-btn {',
      '    left: 12px;',
      '    bottom: 12px;',
      '  }',
      '  #anako-pwa-checker-panel {',
      '    left: 12px;',
      '    right: 12px;',
      '    width: auto;',
      '    bottom: 58px;',
      '  }',
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

  function createCheckerButton() {
    if (document.getElementById('anako-pwa-checker-btn')) {
      return;
    }

    var button = document.createElement('button');
    button.id = 'anako-pwa-checker-btn';
    button.type = 'button';
    button.textContent = 'PWA Status';
    button.addEventListener('click', function () {
      setCheckerOpen(!checkerPanelOpen);
    });
    document.body.appendChild(button);
  }

  function createCheckerPanel() {
    if (document.getElementById('anako-pwa-checker-panel')) {
      return;
    }

    var panel = document.createElement('section');
    panel.id = 'anako-pwa-checker-panel';
    panel.setAttribute('aria-live', 'polite');
    panel.innerHTML = [
      '<header>',
      '  <strong>PWA install status</strong>',
      '  <button id="anako-pwa-checker-close" type="button" aria-label="Close">&times;</button>',
      '</header>',
      '<div id="anako-pwa-checker-body"></div>'
    ].join('');
    document.body.appendChild(panel);

    var close = document.getElementById('anako-pwa-checker-close');
    if (close) {
      close.addEventListener('click', function () {
        setCheckerOpen(false);
      });
    }
  }

  function setCheckerOpen(open) {
    checkerPanelOpen = open;
    var panel = document.getElementById('anako-pwa-checker-panel');
    if (!panel) {
      return;
    }
    panel.style.display = open ? 'block' : 'none';
    if (open) {
      renderChecker();
    }
  }

  function manifestUrl() {
    var link = document.querySelector('link[rel="manifest"]');
    if (!link) {
      return null;
    }
    return link.href || null;
  }

  async function evaluateManifest() {
    var url = manifestUrl();
    if (!url) {
      return {
        ok: false,
        message: 'Manifest link missing from page.'
      };
    }

    try {
      var response = await fetch(url, { cache: 'no-store' });
      if (!response.ok) {
        return { ok: false, message: 'Manifest could not be fetched.' };
      }

      var manifest = await response.json();
      var icons = Array.isArray(manifest.icons) ? manifest.icons : [];
      var has192 = icons.some(function (icon) { return String(icon.sizes || '').includes('192x192'); });
      var has512 = icons.some(function (icon) { return String(icon.sizes || '').includes('512x512'); });

      return {
        ok: has192 && has512,
        message: has192 && has512 ? 'Manifest and icons look good.' : 'Manifest is missing 192x192 or 512x512 icons.',
        details: {
          has192: has192,
          has512: has512
        }
      };
    } catch (error) {
      return {
        ok: false,
        message: 'Manifest could not be parsed or fetched.'
      };
    }
  }

  async function evaluateServiceWorker() {
    if (!('serviceWorker' in navigator)) {
      return { ok: false, message: 'Service workers are not supported in this browser.' };
    }

    try {
      var reg = await navigator.serviceWorker.getRegistration();
      if (!reg) {
        return { ok: false, message: 'Service worker is not registered yet.' };
      }

      var active = !!(reg.active || reg.waiting || reg.installing);
      return {
        ok: active,
        message: active ? 'Service worker is registered.' : 'Service worker registration is pending.'
      };
    } catch (error) {
      return { ok: false, message: 'Could not read service worker registration.' };
    }
  }

  function evaluateContext() {
    if (!window.isSecureContext) {
      return { ok: false, message: 'Page is not in a secure context (HTTPS or localhost required).' };
    }

    return { ok: true, message: 'Secure context is OK.' };
  }

  function evaluateStandalone() {
    if (isStandalone()) {
      return { ok: false, warn: true, message: 'App is already running in standalone mode.' };
    }

    return { ok: true, message: 'App is not installed yet.' };
  }

  function evaluatePromptSupport() {
    if ('BeforeInstallPromptEvent' in window || 'onbeforeinstallprompt' in window) {
      return { ok: true, message: 'Browser can expose the native install prompt.' };
    }

    return { ok: false, message: 'This browser may not expose beforeinstallprompt (common on Safari/iOS).' };
  }

  function renderCheckerItem(label, result) {
    var icon = result.ok ? '✓' : (result.warn ? '!' : '✕');
    var cls = result.ok ? 'pass' : (result.warn ? 'warn' : 'fail');
    return '<li class="anako-checker-item ' + cls + '"><div class="anako-checker-icon">' + icon + '</div><div><div class="anako-checker-label">' + label + '</div><div class="anako-checker-desc">' + result.message + '</div></div></li>';
  }

  async function renderChecker() {
    var body = document.getElementById('anako-pwa-checker-body');
    if (!body) {
      return;
    }

    var secure = evaluateContext();
    var prompt = evaluatePromptSupport();
    var standalone = evaluateStandalone();
    var sw = await evaluateServiceWorker();
    var manifest = await evaluateManifest();

    var issues = [secure, prompt, standalone, sw, manifest].filter(function (item) { return !item.ok && !item.warn; });
    var summary = issues.length === 0 ? 'No missing requirements detected.' : ('Missing requirements: ' + issues.map(function (item) { return item.message; }).join(' '));

    body.innerHTML = [
      '<div class="anako-checker-summary">' + summary + '</div>',
      '<ul class="anako-checker-list">',
      renderCheckerItem('Secure context', secure),
      renderCheckerItem('Native install prompt support', prompt),
      renderCheckerItem('Installed already', standalone),
      renderCheckerItem('Service worker', sw),
      renderCheckerItem('Manifest and icons', manifest),
      '</ul>'
    ].join('');
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
        setInstallButtonVisible(false);
      });
    });

    document.body.appendChild(button);
    button.style.display = 'none';
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
    createCheckerButton();
    createCheckerPanel();
    createInstallButton();
    updateOfflineBanner();

    if (isStandalone()) {
      setInstallButtonVisible(false);
      return;
    }

    setInstallButtonVisible(false);

    window.addEventListener('anako:pwa-sw-registered', function () {
      if (checkerPanelOpen) {
        renderChecker();
      }
    });
  });
})();
