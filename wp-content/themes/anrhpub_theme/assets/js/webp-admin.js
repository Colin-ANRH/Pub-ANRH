(function () {
  'use strict';

  var cfg = window.anrhpubWebpAdmin;
  var logEl = document.getElementById('anrhpub-webp-log');
  var batchBtn = document.getElementById('anrhpub-webp-batch');
  var themeBtn = document.getElementById('anrhpub-webp-theme');

  if (!cfg || !cfg.ajaxUrl) {
    return;
  }

  function setLog(text) {
    if (logEl) {
      logEl.textContent = text || '';
    }
  }

  function post(action, data) {
    var body = new FormData();
    body.append('action', action);
    body.append('nonce', cfg.nonce || '');

    Object.keys(data || {}).forEach(function (key) {
      body.append(key, data[key]);
    });

    return fetch(cfg.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: body
    }).then(function (res) {
      return res.json();
    });
  }

  function runBatch(offset) {
    setLog((cfg.i18n && cfg.i18n.running) || '…');

    return post('anrhpub_webp_batch', { offset: String(offset || 0) }).then(function (json) {
      if (!json || !json.success) {
        throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
      }

      var d = json.data || {};
      var remaining = typeof d.remaining === 'number' ? d.remaining : 0;
      var tpl = (cfg.i18n && cfg.i18n.progress) || '%1$d — %2$d';

      setLog(
        tpl.replace('%1$d', String(d.next_offset || offset)).replace('%2$d', String(remaining))
      );

      if (d.messages && d.messages.length && logEl) {
        logEl.textContent += ' ' + d.messages.join(' | ');
      }

      if (!d.done && d.processed > 0) {
        return runBatch(d.next_offset || offset + d.processed);
      }

      setLog((cfg.i18n && cfg.i18n.done) || 'OK');
      if (batchBtn) {
        batchBtn.disabled = remaining > 0;
      }
    });
  }

  if (batchBtn) {
    batchBtn.addEventListener('click', function () {
      batchBtn.disabled = true;
      if (themeBtn) {
        themeBtn.disabled = true;
      }

      runBatch(0)
        .catch(function (err) {
          setLog(err.message || (cfg.i18n && cfg.i18n.error));
        })
        .finally(function () {
          if (themeBtn) {
            themeBtn.disabled = false;
          }
        });
    });
  }

  if (themeBtn) {
    themeBtn.addEventListener('click', function () {
      themeBtn.disabled = true;
      if (batchBtn) {
        batchBtn.disabled = true;
      }
      setLog((cfg.i18n && cfg.i18n.running) || '…');

      post('anrhpub_webp_theme_assets', {})
        .then(function (json) {
          if (!json || !json.success) {
            throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
          }
          var d = json.data || {};
          setLog(
            ((cfg.i18n && cfg.i18n.done) || 'OK') +
              ' — ' +
              (d.converted || 0) +
              ' convertie(s), ' +
              (d.errors || 0) +
              ' erreur(s).'
          );
          if (d.messages && d.messages.length) {
            setLog(logEl.textContent + ' ' + d.messages.join(' | '));
          }
          if ((d.converted || 0) + (d.errors || 0) === 0) {
            themeBtn.disabled = true;
          }
        })
        .catch(function (err) {
          setLog(err.message || (cfg.i18n && cfg.i18n.error));
        })
        .finally(function () {
          if (batchBtn) {
            batchBtn.disabled = false;
          }
        });
    });
  }
})();
