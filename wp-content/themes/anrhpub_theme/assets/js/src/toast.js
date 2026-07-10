function anrhpubPlainToastMessage(message) {
    if (!message) {
      return '';
    }
    var div = document.createElement('div');
    div.innerHTML = String(message);
    var text = (div.textContent || div.innerText || '').replace(/\s+/g, ' ').trim();
    return text.replace(/^Erreur\s*:\s*/i, '');
  }

  function anrhpubShowToast(message, type, durationMs) {
    var root = document.getElementById('anrhpub-toast-root');
    message = anrhpubPlainToastMessage(message);
    if (!root || !message) {
      return;
    }

    var cfg = window.anrhpubAccount || {};
    var ms = durationMs || cfg.toastMs || 4800;
    var toastType = type || 'info';
    var toast = document.createElement('div');
    toast.className = 'anrhpub-toast anrhpub-toast--' + toastType;
    toast.setAttribute('role', toastType === 'error' ? 'alert' : 'status');

    var icon = document.createElement('span');
    icon.className = 'anrhpub-toast__icon';
    icon.setAttribute('aria-hidden', 'true');

    var text = document.createElement('p');
    text.className = 'anrhpub-toast__message';
    text.textContent = message;

    var closeLabel = (cfg.i18n && cfg.i18n.close) || 'Fermer';
    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'anrhpub-toast__close';
    closeBtn.setAttribute('aria-label', closeLabel);
    closeBtn.textContent = '\u00d7';

    toast.appendChild(icon);
    toast.appendChild(text);
    toast.appendChild(closeBtn);
    root.appendChild(toast);

    function dismiss() {
      if (toast.classList.contains('is-leaving')) {
        return;
      }
      toast.classList.remove('is-visible');
      toast.classList.add('is-leaving');
      window.setTimeout(function () {
        toast.remove();
      }, 320);
    }

    closeBtn.addEventListener('click', dismiss);
    window.requestAnimationFrame(function () {
      toast.classList.add('is-visible');
    });
    window.setTimeout(dismiss, ms);
  }

  window.anrhpubShowToast = anrhpubShowToast;
window.anrhpubShowToast = anrhpubShowToast;
