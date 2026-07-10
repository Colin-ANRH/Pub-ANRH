export function initNewsletter() {
    var form = document.querySelector('[data-newsletter-form]');
    var cfg = window.anrhpubNewsletter;

    if (!form || !cfg || !cfg.ajaxUrl) {
      return;
    }

    var feedback = form.querySelector('[data-newsletter-feedback]');
    var submitBtn = form.querySelector('[data-newsletter-submit]');
    var defaultLabel = submitBtn ? submitBtn.textContent : '';

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      if (feedback) {
        feedback.hidden = true;
        feedback.classList.remove('is-success', 'is-error');
      }

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = (cfg.i18n && cfg.i18n.sending) || '…';
      }

      var body = new FormData(form);
      body.append('action', cfg.action || 'anrhpub_newsletter_subscribe');
      body.append('nonce', cfg.nonce || '');

      fetch(cfg.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: body
      })
        .then(function (res) {
          return res.json().then(function (data) {
            return { ok: res.ok, data: data };
          });
        })
        .then(function (result) {
          var message = '';
          var success = false;

          if (result.data && result.data.success) {
            success = true;
            message = (result.data.data && result.data.data.message) || '';
            form.classList.add('is-success');
          } else {
            message = (result.data && result.data.data && result.data.data.message)
              || (cfg.i18n && cfg.i18n.error)
              || '';
          }

          if (feedback && message) {
            feedback.textContent = message;
            feedback.hidden = false;
            feedback.classList.add(success ? 'is-success' : 'is-error');
          }

          if (success) {
            form.reset();
          }
        })
        .catch(function () {
          if (feedback) {
            feedback.textContent = (cfg.i18n && cfg.i18n.error) || '';
            feedback.hidden = false;
            feedback.classList.add('is-error');
          }
        })
        .finally(function () {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = defaultLabel;
          }
        });
    });
  }
