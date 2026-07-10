export function initPageLoader() {
    var loader = document.getElementById('page-loader');
    var root = document.documentElement;

    if (!loader) {
      return;
    }

    root.classList.add('is-page-loading');
    loader.setAttribute('aria-busy', 'true');

    var minVisibleMs = 380;
    var maxVisibleMs = 12000;
    var startedAt = Date.now();
    var finished = false;

    function finish() {
      if (finished) {
        return;
      }
      finished = true;

      var delay = Math.max(0, minVisibleMs - (Date.now() - startedAt));

      window.setTimeout(function () {
        loader.classList.add('is-hidden');
        loader.setAttribute('aria-busy', 'false');
        root.classList.remove('is-page-loading');

        window.setTimeout(function () {
          if (loader.parentNode) {
            loader.parentNode.removeChild(loader);
          }
        }, 520);
      }, delay);
    }

    if (document.readyState === 'complete') {
      finish();
    } else {
      window.addEventListener('load', finish, { once: true });
    }

    window.setTimeout(finish, maxVisibleMs);

    window.addEventListener('pageshow', function (e) {
      if (e.persisted) {
        finish();
      }
    });
  }
