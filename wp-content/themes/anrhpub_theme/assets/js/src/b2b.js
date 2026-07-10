export function initB2b() {
    var cfg = window.anrhpubB2b || {};
    var compareKey = cfg.compareKey || 'anrhpub_compare';

    function getCompareIds() {
      try {
        var raw = localStorage.getItem(compareKey);
        var parsed = raw ? JSON.parse(raw) : [];
        return Array.isArray(parsed) ? parsed.map(Number).filter(Boolean) : [];
      } catch (e) {
        return [];
      }
    }

    function setCompareIds(ids) {
      localStorage.setItem(compareKey, JSON.stringify(ids.slice(0, 4)));
    }

    var compareMax = cfg.compareMax || 4;
    var i18n = cfg.i18n || {};

    function escapeHtml(str) {
      if (!str) return '';
      var el = document.createElement('div');
      el.textContent = String(str);
      return el.innerHTML;
    }

    function compareCountLabel(count) {
      var tpl = i18n.compareCount || '%1$d / %2$d produits';
      return tpl.replace('%1$d', String(count)).replace('%2$d', String(compareMax));
    }

    function showCompareToast(message) {
      if (!message) return;
      var toast = document.getElementById('anr-compare-toast');
      if (!toast) {
        toast = document.createElement('div');
        toast.id = 'anr-compare-toast';
        toast.className = 'compare-toast';
        toast.setAttribute('role', 'status');
        document.body.appendChild(toast);
      }
      toast.textContent = message;
      toast.classList.add('is-visible');
      clearTimeout(showCompareToast._timer);
      showCompareToast._timer = setTimeout(function () {
        toast.classList.remove('is-visible');
      }, 2600);
    }

    function updateCompareBadge() {
      var count = getCompareIds().length;
      document.querySelectorAll('[data-compare-badge]').forEach(function (el) {
        el.textContent = String(count);
        el.hidden = count < 1;
      });
    }

    function updateCompareButtons() {
      var ids = getCompareIds();
      document.querySelectorAll('[data-compare-add]').forEach(function (btn) {
        var id = parseInt(btn.getAttribute('data-product-id'), 10);
        var inList = id && ids.indexOf(id) !== -1;
        btn.classList.toggle('is-active', inList);
        btn.setAttribute('aria-pressed', inList ? 'true' : 'false');
        var labelOn = btn.getAttribute('data-label-on') || 'Dans le comparateur';
        var labelOff = btn.getAttribute('data-label-off') || 'Comparer';
        var labelEl = btn.querySelector('[data-compare-label]');
        if (labelEl) {
          labelEl.textContent = inList ? labelOn : labelOff;
        }
      });
      updateCompareBadge();
    }

    document.querySelectorAll('[data-compare-add]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = parseInt(btn.getAttribute('data-product-id'), 10);
        if (!id) return;
        var ids = getCompareIds();
        if (ids.indexOf(id) !== -1) {
          setCompareIds(ids.filter(function (x) { return x !== id; }));
          updateCompareButtons();
          showCompareToast(i18n.compareRemoved || 'Retiré du comparateur.');
          return;
        }
        if (ids.length >= compareMax) {
          showCompareToast(i18n.compareMax || 'Maximum atteint.');
          return;
        }
        ids.push(id);
        setCompareIds(ids);
        updateCompareButtons();
        showCompareToast(i18n.compareAdded || 'Ajouté au comparateur.');
      });
    });
    updateCompareButtons();

    function renderCompareView(products) {
      if (!products || !products.length) {
        return (
          '<div class="compare-app__empty">' +
          '<div class="compare-app__empty-icon" aria-hidden="true"><svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect x="6" y="10" width="14" height="28" rx="2" stroke="currentColor" stroke-width="2"/><rect x="19" y="10" width="14" height="28" rx="2" stroke="currentColor" stroke-width="2"/><rect x="32" y="10" width="10" height="28" rx="2" stroke="currentColor" stroke-width="2" opacity="0.35"/></svg></div>' +
          '<p class="compare-app__empty-title">' + escapeHtml(i18n.compareEmpty || '') + '</p>' +
          '<p class="compare-app__empty-text">' + escapeHtml(i18n.compareEmptyHint || '') + '</p>' +
          '<a class="btn btn--primary" href="' + escapeHtml(cfg.catalogueUrl || '/') + '">' + escapeHtml(i18n.compareCatalogue || 'Catalogue') + '</a>' +
          '</div>'
        );
      }

      var catalogueUrl = cfg.catalogueUrl || '/';
      var html = '<div class="compare-toolbar">';
      html += '<span class="compare-toolbar__count">' + escapeHtml(compareCountLabel(products.length)) + '</span>';
      html += '<div class="compare-toolbar__actions">';
      html += '<button type="button" class="btn btn--outline btn--sm" data-compare-clear>' + escapeHtml(i18n.compareClear || 'Tout effacer') + '</button>';
      html += '<a class="btn btn--outline btn--sm" href="' + escapeHtml(catalogueUrl) + '">' + escapeHtml(i18n.compareCatalogue || 'Catalogue') + '</a>';
      html += '</div></div>';

      html += '<div class="compare-grid">';
      products.forEach(function (p) {
        html += '<article class="compare-card">';
        html += '<button type="button" class="compare-card__remove" data-compare-remove data-product-id="' + p.id + '" aria-label="' + escapeHtml(i18n.compareRemove || 'Retirer') + '"><span aria-hidden="true">×</span></button>';
        html += '<a class="compare-card__media" href="' + escapeHtml(p.link) + '">';
        if (p.image) {
          html += '<img src="' + escapeHtml(p.image) + '" alt="" loading="lazy" width="280" height="280">';
        } else {
          html += '<span class="compare-card__placeholder"></span>';
        }
        html += '</a>';
        html += '<div class="compare-card__body">';
        if (p.reference) {
          html += '<p class="compare-card__ref">' + escapeHtml(p.reference) + '</p>';
        }
        html += '<h2 class="compare-card__title"><a href="' + escapeHtml(p.link) + '">' + escapeHtml(p.title) + '</a></h2>';
        html += '<div class="compare-card__actions">';
        if (p.requires_color) {
          html += '<a class="btn btn--primary btn--sm" href="' + escapeHtml(p.link) + '#product-quote">' + escapeHtml(i18n.compareChooseColor || 'Choisir une couleur') + '</a>';
        } else {
          html += '<button type="button" class="btn btn--primary btn--sm" data-compare-add-cart data-product-id="' + p.id + '" data-quote-min-qty="' + (parseInt(p.min_qty, 10) || 1) + '">' + escapeHtml(i18n.compareAddCart || 'Ajouter au panier') + '</button>';
        }
        html += '<a class="btn btn--outline btn--sm" href="' + escapeHtml(p.link) + '">' + escapeHtml(i18n.compareView || 'Fiche') + '</a>';
        html += '</div></div></article>';
      });
      html += '</div>';

      function specRow(label, key) {
        var has = products.some(function (p) { return p[key]; });
        if (!has) return '';
        var row = '<tr><th scope="row">' + escapeHtml(label) + '</th>';
        products.forEach(function (p) {
          row += '<td>' + escapeHtml(p[key] || '—') + '</td>';
        });
        return row + '</tr>';
      }

      html += '<div class="compare-specs"><h2 class="compare-specs__title">' + escapeHtml(i18n.compareSpecs || 'Caractéristiques') + '</h2>';
      html += '<div class="compare-specs__scroll"><table class="compare-specs__table"><tbody>';
      html += specRow(i18n.rowReference || 'Référence', 'reference');
      html += specRow(i18n.rowCategory || 'Catégorie', 'category');
      html += specRow(i18n.rowMaterial || 'Matière', 'material');
      html += specRow(i18n.rowStock || 'Disponibilité', 'stock');
      html += specRow(i18n.rowPriceHt || 'Prix HT', 'price_ht');
      html += specRow(i18n.rowPriceTtc || 'Prix TTC', 'price_ttc');
      html += specRow(i18n.rowExcerpt || 'Description', 'excerpt');
      html += '</tbody></table></div></div>';

      return html;
    }

    function bindCompareApp(compareApp) {
      compareApp.querySelectorAll('[data-compare-remove]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var pid = parseInt(btn.getAttribute('data-product-id'), 10);
          setCompareIds(getCompareIds().filter(function (x) { return x !== pid; }));
          loadCompareApp(compareApp);
          updateCompareButtons();
          showCompareToast(i18n.compareRemoved || 'Retiré.');
        });
      });
      compareApp.querySelectorAll('[data-compare-add-cart]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var pid = parseInt(btn.getAttribute('data-product-id'), 10);
          var minQty = parseInt(btn.getAttribute('data-quote-min-qty'), 10) || 1;
          if (!pid || typeof window.anrhpubQuoteCartAdd !== 'function') {
            showCompareToast((window.anrhpubQuoteCart && window.anrhpubQuoteCart.i18n && window.anrhpubQuoteCart.i18n.error) || 'Erreur panier.');
            return;
          }
          var ctx = document.createElement('div');
          ctx.setAttribute('data-quote-min-qty', String(minQty));
          btn.disabled = true;
          window.anrhpubQuoteCartAdd(pid, minQty, 0, ctx)
            .then(function () {
              var cartI18n = window.anrhpubQuoteCart && window.anrhpubQuoteCart.i18n;
              if (window.anrhpubShowToast && cartI18n && cartI18n.added) {
                window.anrhpubShowToast(cartI18n.added, 'success', 3200);
              } else {
                showCompareToast(cartI18n && cartI18n.added ? cartI18n.added : 'Ajouté au panier.');
              }
            })
            .catch(function () {
              showCompareToast((window.anrhpubQuoteCart && window.anrhpubQuoteCart.i18n && window.anrhpubQuoteCart.i18n.error) || 'Erreur.');
            })
            .finally(function () {
              btn.disabled = false;
            });
        });
      });
      var clearBtn = compareApp.querySelector('[data-compare-clear]');
      if (clearBtn) {
        clearBtn.addEventListener('click', function () {
          setCompareIds([]);
          loadCompareApp(compareApp);
          updateCompareButtons();
        });
      }
    }

    function loadCompareApp(compareApp) {
      var ids = getCompareIds();
      if (!ids.length) {
        compareApp.innerHTML = renderCompareView([]);
        return;
      }
      compareApp.innerHTML = '<div class="compare-app__loading"><span class="compare-app__spinner" aria-hidden="true"></span><p>' + escapeHtml(i18n.compareLoading || '') + '</p></div>';
      var apiUrl = cfg.compareApiUrl || '';
      var compareNonce = cfg.compareNonce || '';
      var url = apiUrl
        ? apiUrl + (apiUrl.indexOf('?') === -1 ? '?' : '&') + 'ids=' + encodeURIComponent(ids.join(',')) + (compareNonce ? '&nonce=' + encodeURIComponent(compareNonce) : '')
        : '';
      var fetcher = url
        ? fetch(url, { credentials: 'same-origin' }).then(function (r) { return r.json(); })
        : Promise.resolve({ success: false });
      fetcher
        .then(function (res) {
          if (res && res.success && res.data && res.data.products && res.data.products.length) {
            return res.data.products;
          }
          var restBase = cfg.restProductsUrl || '';
          if (!restBase) {
            return [];
          }
          return fetch(restBase + '?include=' + ids.join(',') + '&_fields=id,title,link', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (items) {
              if (!Array.isArray(items)) return [];
              return items.map(function (it) {
                return {
                  id: it.id,
                  title: it.title && it.title.rendered ? it.title.rendered : '',
                  link: it.link || '',
                  reference: ''
                };
              });
            });
        })
        .catch(function () { return []; })
        .then(function (products) {
          if (!products.length) {
            compareApp.innerHTML =
              '<div class="compare-app__empty compare-app__empty--error">' +
              '<p class="compare-app__empty-title">' + escapeHtml(i18n.compareError || '') + '</p>' +
              '<button type="button" class="btn btn--outline" onclick="location.reload()">' + escapeHtml('Recharger') + '</button>' +
              '</div>';
            return;
          }
          compareApp.innerHTML = renderCompareView(products);
          bindCompareApp(compareApp);
        });
    }

    var compareApp = document.querySelector('[data-compare-app]');
    if (compareApp) {
      loadCompareApp(compareApp);
    }

    var banner = document.getElementById('anr-cookie-banner');

    function activateAnalyticsScripts() {
      document.querySelectorAll('script[type="text/plain"][data-anr-consent="analytics"]').forEach(function (node) {
        var script = document.createElement('script');
        var src = node.getAttribute('src');
        if (src) {
          script.src = src;
        } else {
          script.textContent = node.textContent;
        }
        node.parentNode.replaceChild(script, node);
      });
    }

    function setCookieConsent(mode) {
      document.cookie = 'anrhpub_cookie_consent_v1=' + mode + ';path=/;max-age=31536000;SameSite=Lax';
      window.anrhpubConsent = { analytics: mode === 'all' };
      if (mode === 'all') {
        activateAnalyticsScripts();
      }
    }

    if (window.anrhpubConsent && window.anrhpubConsent.analytics) {
      activateAnalyticsScripts();
    }

    if (banner && !document.cookie.match(/anrhpub_cookie_consent_v1=/)) {
      banner.hidden = false;
      banner.querySelector('[data-cookie-accept]')?.addEventListener('click', function () {
        setCookieConsent('all');
        banner.hidden = true;
      });
      banner.querySelector('[data-cookie-reject]')?.addEventListener('click', function () {
        setCookieConsent('essential');
        banner.hidden = true;
      });
    } else if (banner) {
      banner.hidden = true;
    }

    var draftBtn = document.querySelector('[data-quote-save-draft]');
    if (draftBtn && cfg.quoteDraftNonce) {
      draftBtn.addEventListener('click', function () {
        var cart = window.anrhpubQuoteCart && window.anrhpubQuoteCart.getItems ? window.anrhpubQuoteCart.getItems() : [];
        var body = new FormData();
        body.append('action', 'anrhpub_save_quote_draft');
        body.append('nonce', cfg.quoteDraftNonce);
        body.append('cart', JSON.stringify(cart));
        fetch((window.anrhpubQuoteCart && window.anrhpubQuoteCart.ajaxUrl) || '/wp-admin/admin-ajax.php', {
          method: 'POST',
          credentials: 'same-origin',
          body: body
        })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            window.alert((res.data && res.data.message) || (cfg.i18n && cfg.i18n.draftSaved) || '');
          });
      });
    }

    var listBtn = document.querySelector('[data-shared-list-save]');
    if (listBtn && cfg.sharedListNonce) {
      listBtn.addEventListener('click', function () {
        var name = window.prompt('Nom de la liste :');
        if (!name) return;
        var cart = window.anrhpubQuoteCart && window.anrhpubQuoteCart.getItems ? window.anrhpubQuoteCart.getItems() : [];
        var body = new FormData();
        body.append('action', 'anrhpub_save_shared_list');
        body.append('nonce', cfg.sharedListNonce);
        body.append('name', name);
        body.append('cart', JSON.stringify(cart));
        fetch('/wp-admin/admin-ajax.php', { method: 'POST', credentials: 'same-origin', body: body })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            window.alert((res.data && res.data.message) || 'OK');
          });
      });
    }
  }
