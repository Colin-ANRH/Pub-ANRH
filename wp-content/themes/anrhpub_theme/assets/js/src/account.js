export function initAccountToasts() {
    var cfg = window.anrhpubAccount;
    if (!cfg) {
      return;
    }

    if (cfg.flash && cfg.flash.message) {
      anrhpubShowToast(cfg.flash.message, cfg.flash.type);
    }

    try {
      var url = new URL(window.location.href);
      if (url.searchParams.has('account_notice')) {
        url.searchParams.delete('account_notice');
        var clean = url.pathname + url.search + url.hash;
        window.history.replaceState({}, '', clean);
      }
    } catch (e) {
      /* ignore */
    }
  }

  function initAccountTabs() {
    var root = document.querySelector('[data-account-tabs]');
    if (!root) {
      return;
    }

    var tabs = root.querySelectorAll('.account-tabs__tab[role="tab"]');
    var panels = root.querySelectorAll('.account-tab-panel[role="tabpanel"]');

    if (!tabs.length || !panels.length) {
      return;
    }

    var hashMap = {
      '#account-profile': 'profile',
      '#account-password': 'password',
      '#account-favorites': 'favorites',
      '#account-orders': 'orders',
      '#account-credits': 'credits',
      '#account-addresses': 'addresses',
      '#panel-profile': 'profile',
      '#panel-password': 'password',
      '#panel-favorites': 'favorites',
      '#panel-orders': 'orders',
      '#panel-credits': 'credits',
      '#panel-addresses': 'addresses'
    };

    function activateTab(name) {
      tabs.forEach(function (tab) {
        var active = tab.getAttribute('data-account-tab') === name;
        tab.classList.toggle('is-active', active);
        tab.setAttribute('aria-selected', active ? 'true' : 'false');
        tab.tabIndex = active ? 0 : -1;
      });

      panels.forEach(function (panel) {
        var active = panel.getAttribute('data-account-panel') === name;
        panel.classList.toggle('is-active', active);
        if (active) {
          panel.removeAttribute('hidden');
        } else {
          panel.setAttribute('hidden', '');
        }
      });
    }

    root.querySelectorAll('[data-account-tab-jump]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = btn.getAttribute('data-account-tab-jump');
        if (target) {
          activateTab(target);
          var tabEl = root.querySelector('.account-tabs__tab[data-account-tab="' + target + '"]');
          if (tabEl) {
            tabEl.focus({ preventScroll: true });
            tabEl.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
          }
        }
      });
    });

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        activateTab(tab.getAttribute('data-account-tab'));
      });

      tab.addEventListener('keydown', function (e) {
        var idx = Array.prototype.indexOf.call(tabs, tab);
        var next = -1;

        if (e.key === 'ArrowRight') {
          next = (idx + 1) % tabs.length;
        } else if (e.key === 'ArrowLeft') {
          next = (idx - 1 + tabs.length) % tabs.length;
        } else if (e.key === 'Home') {
          next = 0;
        } else if (e.key === 'End') {
          next = tabs.length - 1;
        }

        if (next >= 0) {
          e.preventDefault();
          tabs[next].focus();
          activateTab(tabs[next].getAttribute('data-account-tab'));
        }
      });
    });

    var initial = hashMap[window.location.hash] || 'profile';
    activateTab(initial);

    window.addEventListener('hashchange', function () {
      var name = hashMap[window.location.hash];
      if (name) {
        activateTab(name);
      }
    });

    window.anrhpubActivateAccountTab = activateTab;
  }

  function initAccountFavorites() {
    var cfg = window.anrhpubAccount;
    if (!cfg) {
      return;
    }

    function refreshFavoritesList() {
      var list = document.getElementById('account-favorites-list');
      if (!list || !cfg.isLoggedIn) {
        return;
      }

      var body = new FormData();
      body.append('action', 'anrhpub_get_favorites_html');
      body.append('nonce', cfg.nonce);

      fetch(cfg.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (r) {
          return r.json();
        })
        .then(function (res) {
          if (res.success && res.data && res.data.html) {
            list.innerHTML = res.data.html;
            list.querySelectorAll('.product-card').forEach(function (card) {
              card.classList.add('is-visible');
            });
          }
        });
    }

    function updateNavCount(count) {
      document.querySelectorAll('[data-favorites-count]').forEach(function (badge) {
        badge.textContent = String(count);
      });
    }

    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.product-favorite');
      if (!btn) {
        return;
      }

      // Invité : lien vers /connexion (pas de toggle sans compte).
      if (btn.getAttribute('data-favorite-login') === '1' || btn.classList.contains('product-favorite--guest')) {
        return;
      }

      e.preventDefault();
      e.stopPropagation();

      if (!cfg || !cfg.isLoggedIn) {
        var loginBase = (cfg && cfg.loginUrl) ? cfg.loginUrl : '/connexion/';
        var loginUrl = new URL(loginBase, window.location.origin);
        loginUrl.searchParams.set('redirect_to', window.location.href);
        loginUrl.searchParams.set('account_notice', 'favorite_login');
        window.location.href = loginUrl.toString();
        return;
      }

      var productId = btn.getAttribute('data-product-id');
      if (!productId) {
        return;
      }

      btn.classList.add('is-loading');

      var body = new FormData();
      body.append('action', 'anrhpub_toggle_favorite');
      body.append('nonce', cfg.nonce);
      body.append('product_id', productId);

      fetch(cfg.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (r) {
          return r.json();
        })
        .then(function (res) {
          if (!res.success) {
            if (res.data && res.data.login_url) {
              var failUrl = new URL(res.data.login_url, window.location.origin);
              failUrl.searchParams.set('redirect_to', window.location.href);
              failUrl.searchParams.set('account_notice', 'favorite_login');
              window.location.href = failUrl.toString();
              return;
            }
            throw new Error('toggle failed');
          }
          var active = !!res.data.active;
          btn.classList.toggle('is-active', active);
          btn.setAttribute('aria-pressed', active ? 'true' : 'false');
          var label = res.data.label || (active ? cfg.i18n.remove : cfg.i18n.add);
          btn.setAttribute('aria-label', label);
          btn.setAttribute('title', label);
          var textEl = btn.querySelector('.product-favorite__text');
          if (textEl && res.data.text) {
            textEl.textContent = res.data.text;
          }
          if (typeof res.data.count === 'number') {
            updateNavCount(res.data.count);
          }
          if (res.data.message) {
            anrhpubShowToast(res.data.message, 'success', 3200);
          }
          refreshFavoritesList();
        })
        .catch(function () {
          anrhpubShowToast(cfg.i18n.error, 'error');
        })
        .finally(function () {
          btn.classList.remove('is-loading');
        });
    });
  }
