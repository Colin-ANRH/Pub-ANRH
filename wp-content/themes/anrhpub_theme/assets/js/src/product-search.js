export function initGlobalProductSearch() {
    var cfg = window.anrhpubProductSearch;
    var root = document.querySelector('[data-global-product-search]');
    var input = document.getElementById('global-catalogue-search');
    if (!cfg || !root || !input) {
      return;
    }

    function escapeHtml(str) {
      if (!str) return '';
      var el = document.createElement('div');
      el.textContent = String(str);
      return el.innerHTML;
    }

    var dropdown = root.querySelector('[data-search-dropdown]');
    var clearBtn = root.querySelector('[data-search-clear]');
    var i18n = cfg.i18n || {};
    var suggestTimer = null;
    var catalogueTimer = null;
    var activeController = null;
    var focusedIndex = -1;

    function buildCatalogueUrl(query, page) {
      var url = new URL(cfg.catalogueUrl, window.location.origin);
      var path = url.pathname.replace(/\/?$/, '/');
      if (page && page > 1) {
        url.pathname = path + 'page/' + page + '/';
      }
      if (query && query.length >= cfg.minSearch) {
        url.searchParams.set(cfg.searchKey, query);
      } else {
        url.searchParams.delete(cfg.searchKey);
      }
      return url.toString();
    }

    function setDropdownOpen(open) {
      root.classList.toggle('is-open', open);
      input.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (!open && dropdown) {
        dropdown.hidden = true;
      }
    }

    function hideDropdown() {
      setDropdownOpen(false);
      focusedIndex = -1;
    }

    function renderDropdown(data, query) {
      if (!dropdown) {
        return;
      }

      var html = '';
      var products = (data && data.products) || [];
      var categories = (data && data.categories) || [];

      if (!query || query.length < cfg.minSearch) {
        hideDropdown();
        return;
      }

      if (!products.length && !categories.length) {
        html = '<p class="header-search__status">' + (i18n.empty || '') + '</p>';
      } else {
        if (categories.length) {
          html += '<p class="header-search__group-title">' + (i18n.categories || '') + '</p><ul class="header-search__list">';
          categories.forEach(function (cat) {
            html += '<li class="header-search__item header-search__cat"><a href="' + escapeHtml(cat.url) + '">' + escapeHtml(cat.name) + '</a></li>';
          });
          html += '</ul>';
        }
        if (products.length) {
          html += '<p class="header-search__group-title">' + (i18n.products || '') + '</p><ul class="header-search__list" data-search-products>';
          products.forEach(function (p, idx) {
            var thumb = p.image
              ? '<img class="header-search__thumb" src="' + escapeHtml(p.image) + '" alt="" width="40" height="40" loading="lazy">'
              : '<span class="header-search__thumb header-search__thumb--empty" aria-hidden="true"></span>';
            html += '<li class="header-search__item" data-search-item="' + idx + '"><a href="' + escapeHtml(p.url) + '">' + thumb;
            html += '<span class="header-search__meta"><span class="header-search__title">' + escapeHtml(p.title) + '</span>';
            if (p.reference) {
              html += '<span class="header-search__ref">' + escapeHtml(p.reference) + '</span>';
            }
            html += '</span></a></li>';
          });
          html += '</ul>';
        }
        if (data.catalogue_url) {
          html += '<div class="header-search__footer"><a class="header-search__view-all" href="' + escapeHtml(data.catalogue_url) + '">' + escapeHtml(i18n.viewAll || '') + '</a></div>';
        }
      }

      dropdown.innerHTML = html;
      dropdown.hidden = false;
      setDropdownOpen(true);
    }

    function fetchSuggestions(query) {
      if (activeController) {
        activeController.abort();
      }
      if (!query || query.length < cfg.minSearch) {
        hideDropdown();
        return;
      }

      activeController = new AbortController();
      var url = cfg.ajaxUrl + '?action=anrhpub_product_search_suggest&nonce=' + encodeURIComponent(cfg.nonce) + '&q=' + encodeURIComponent(query);

      if (dropdown) {
        dropdown.innerHTML = '<p class="header-search__status">' + (i18n.loading || '') + '</p>';
        dropdown.hidden = false;
        setDropdownOpen(true);
      }

      fetch(url, { signal: activeController.signal, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res && res.success && res.data) {
            renderDropdown(res.data, query);
          } else {
            hideDropdown();
          }
        })
        .catch(function (err) {
          if (err && err.name !== 'AbortError') {
            hideDropdown();
          }
        })
        .finally(function () {
          activeController = null;
        });
    }

    function triggerCatalogueSearch(query) {
      var live = window.anrhpubCatalogueLive;
      if (!live || !document.querySelector('.catalogue-page')) {
        return false;
      }
      if (!query) {
        live.load(live.cfg.catalogueUrl);
        return true;
      }
      if (query.length < cfg.minSearch) {
        return true;
      }
      live.load(live.buildUrl(query));
      hideDropdown();
      return true;
    }

    function goToCatalogue(query) {
      if (triggerCatalogueSearch(query)) {
        return;
      }
      window.location.href = buildCatalogueUrl(query);
    }

    input.addEventListener('input', function () {
      var value = input.value.trim();
      if (clearBtn) {
        clearBtn.hidden = value.length === 0;
      }

      clearTimeout(suggestTimer);
      clearTimeout(catalogueTimer);

      suggestTimer = setTimeout(function () {
        fetchSuggestions(value);
      }, cfg.debounceMs || 320);

      if (document.querySelector('.catalogue-page') && window.anrhpubCatalogueLive) {
        catalogueTimer = setTimeout(function () {
          if (value.length === 0) {
            triggerCatalogueSearch('');
          } else if (value.length >= cfg.minSearch) {
            triggerCatalogueSearch(value);
          }
        }, 450);
      }
    });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        hideDropdown();
        return;
      }
      if (e.key === 'Enter') {
        e.preventDefault();
        clearTimeout(suggestTimer);
        clearTimeout(catalogueTimer);
        goToCatalogue(input.value.trim());
        return;
      }

      var items = dropdown ? dropdown.querySelectorAll('[data-search-item] a') : [];
      if (!items.length || dropdown.hidden) {
        return;
      }

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        focusedIndex = (focusedIndex + 1) % items.length;
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        focusedIndex = (focusedIndex - 1 + items.length) % items.length;
      } else {
        return;
      }

      items.forEach(function (el, i) {
        el.classList.toggle('is-focused', i === focusedIndex);
      });
      if (items[focusedIndex]) {
        items[focusedIndex].focus();
      }
    });

    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        input.value = '';
        clearBtn.hidden = true;
        hideDropdown();
        clearTimeout(suggestTimer);
        clearTimeout(catalogueTimer);
        goToCatalogue('');
        input.focus();
      });
    }

    dropdown.addEventListener('click', function (e) {
      var link = e.target.closest('a');
      if (!link || link.classList.contains('header-search__view-all')) {
        return;
      }
      if (document.querySelector('.catalogue-page') && link.classList.contains('header-search__view-all')) {
        return;
      }
      hideDropdown();
    });

    document.addEventListener('click', function (e) {
      if (!root.contains(e.target)) {
        hideDropdown();
      }
    });

    if (window.anrhpubCatalogueLive && window.anrhpubCatalogueLive.syncInput) {
      window.anrhpubCatalogueLive.syncInput(window.location.href);
    }
  }
