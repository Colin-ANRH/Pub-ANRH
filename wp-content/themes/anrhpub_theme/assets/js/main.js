(function () {
  'use strict';

const MOBILE_BP = 992;

function initPageLoader() {
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

var header = document.getElementById('site-header');
  var toggle = document.getElementById('nav-toggle');
  var nav = document.getElementById('site-nav');
  var backdrop = document.getElementById('nav-backdrop');
  var megaDesktopBound = false;

  function isMobile() {
    return window.innerWidth < MOBILE_BP;
  }

  function syncHeaderHeight() {
    if (!header) {
      return;
    }
    var h = Math.ceil(header.getBoundingClientRect().height);
    document.documentElement.style.setProperty('--header-h', h + 'px');
    header.style.setProperty('--nav-drawer-top', h + 'px');
  }

  function closeAllDropdowns(except) {
    document.querySelectorAll('.menu-item--dropdown.is-open, .menu-item-has-children.is-open, .menu-item--mega.is-open, .has-submenu.is-open').forEach(function (item) {
      if (except && item === except) {
        return;
      }
      item.classList.remove('is-open');
      var btn = item.querySelector('.nav-dropdown-toggle');
      if (btn) {
        btn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  function setDropdownOpen(item, open) {
    if (!item) {
      return;
    }
    item.classList.toggle('is-open', open);
    var btn = item.querySelector('.nav-dropdown-toggle');
    if (btn) {
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
  }

  function initDropdowns() {
    document.querySelectorAll('.menu-item--dropdown, .menu-item-has-children, .menu-item--mega, .has-submenu').forEach(function (item) {
      var btn = item.querySelector('.nav-dropdown-toggle');
      if (!btn) {
        return;
      }

      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var willOpen = !item.classList.contains('is-open');
        closeAllDropdowns(item);
        setDropdownOpen(item, willOpen);
        if (isMobile() && item.classList.contains('menu-item--mega') && willOpen) {
          var col = item.querySelector('.mega-menu__col');
          if (col) {
            window.setTimeout(function () {
              col.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }, 80);
          }
        }
      });
    });

    document.addEventListener('click', function (e) {
      if (!e.target.closest('.site-header')) {
        closeAllDropdowns();
        closeAllAccountMenus();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        if (nav && nav.classList.contains('is-open')) {
          setMenuOpen(false);
        } else {
          closeAllDropdowns();
          closeAllAccountMenus();
        }
      }
    });
  }

  function closeAllAccountMenus(except) {
    document.querySelectorAll('[data-account-menu].is-open').forEach(function (menu) {
      if (except && menu === except) {
        return;
      }
      menu.classList.remove('is-open');
      var toggle = menu.querySelector('.account-menu__toggle');
      var panel = menu.querySelector('.account-menu__panel');
      if (toggle) {
        toggle.setAttribute('aria-expanded', 'false');
      }
      if (panel) {
        panel.hidden = true;
      }
    });
  }

  function initAccountMenu() {
    document.querySelectorAll('[data-account-menu]').forEach(function (menu) {
      var toggle = menu.querySelector('.account-menu__toggle');
      var panel = menu.querySelector('.account-menu__panel');
      if (!toggle || !panel) {
        return;
      }

      toggle.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var willOpen = !menu.classList.contains('is-open');
        closeAllAccountMenus(menu);
        closeAllDropdowns();
        if (willOpen) {
          menu.classList.add('is-open');
          toggle.setAttribute('aria-expanded', 'true');
          panel.hidden = false;
        } else {
          closeAllAccountMenus();
        }
      });

      panel.addEventListener('click', function (e) {
        e.stopPropagation();
      });

      panel.querySelectorAll('a.account-menu__link').forEach(function (link) {
        link.addEventListener('click', function () {
          closeAllAccountMenus();
          if (isMobile() && nav && nav.classList.contains('is-open')) {
            setMenuOpen(false);
          }
        });
      });
    });
  }

  if (header) {
    var onScroll = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  function setMenuOpen(open) {
    if (nav) {
      nav.classList.toggle('is-open', open);
    }
    if (toggle) {
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    if (header) {
      header.classList.toggle('is-menu-open', open);
    }
    if (backdrop) {
      backdrop.hidden = !open;
      backdrop.classList.toggle('is-visible', open);
      backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
    }
    document.body.classList.toggle('nav-open', open);
    if (!open) {
      closeAllDropdowns();
      closeAllAccountMenus();
      if (header) {
        header.classList.remove('is-mega-open');
      }
    }
  }

  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      setMenuOpen(!nav.classList.contains('is-open'));
    });

    if (backdrop) {
      backdrop.addEventListener('click', function () {
        setMenuOpen(false);
      });
    }

    nav.querySelectorAll('.mega-menu a, .sub-menu a, .site-nav__sub a, .nav-link:not(.nav-link--parent)').forEach(function (link) {
      link.addEventListener('click', function () {
        if (!isMobile()) {
          return;
        }
        setMenuOpen(false);
      });
    });
  }

  function onViewportChange() {
    syncHeaderHeight();
    if (!isMobile()) {
      setMenuOpen(false);
      closeAllDropdowns();
      if (header) {
        header.classList.remove('is-mega-open');
      }
    }
  }

  window.addEventListener('resize', function () {
    onViewportChange();
    if (!isMobile() && !megaDesktopBound) {
      initMegaMenu();
    }
  });
  window.addEventListener('orientationchange', onViewportChange);
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(syncHeaderHeight);
  }
  syncHeaderHeight();


  function initMegaMenu() {
    var megaItem = document.querySelector('.menu-item--mega');
    var megaPanel = document.getElementById('mega-menu-catalogue');

    if (!megaItem || !header || !megaPanel) {
      return;
    }

    if (megaDesktopBound) {
      return;
    }

    if (isMobile()) {
      return;
    }

    megaDesktopBound = true;

    var closeTimer = null;

    function openMega() {
      if (isMobile()) {
        return;
      }
      if (closeTimer) {
        clearTimeout(closeTimer);
        closeTimer = null;
      }
      header.classList.add('is-mega-open');
      megaItem.classList.add('is-open');
      var megaToggle = megaItem.querySelector('.nav-dropdown-toggle');
      if (megaToggle) {
        megaToggle.setAttribute('aria-expanded', 'true');
      }
    }

    function scheduleClose() {
      if (isMobile()) {
        return;
      }
      closeTimer = setTimeout(function () {
        header.classList.remove('is-mega-open');
        megaItem.classList.remove('is-open');
        var megaToggle = megaItem.querySelector('.nav-dropdown-toggle');
        if (megaToggle) {
          megaToggle.setAttribute('aria-expanded', 'false');
        }
      }, 220);
    }

    megaItem.addEventListener('mouseenter', openMega);
    megaItem.addEventListener('mouseleave', function (e) {
      if (megaItem.contains(e.relatedTarget)) {
        return;
      }
      scheduleClose();
    });
    megaPanel.addEventListener('mouseenter', openMega);
    megaPanel.addEventListener('mouseleave', scheduleClose);
    megaItem.addEventListener('focusin', openMega);
    megaItem.addEventListener('focusout', function (e) {
      if (!megaItem.contains(e.relatedTarget) && !megaPanel.contains(e.relatedTarget)) {
        scheduleClose();
      }
    });
  }

function bootstrapNavigation() {
  initDropdowns();
  initAccountMenu();
  initMegaMenu();
}

function initScrollAnimations() {
    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var animated = document.querySelectorAll('[data-animate]');

    if (prefersReduced || !animated.length) {
      animated.forEach(function (el) {
        el.classList.add('is-visible');
      });
      return;
    }

    if (!('IntersectionObserver' in window)) {
      animated.forEach(function (el) {
        el.classList.add('is-visible');
      });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      },
      { root: null, rootMargin: '0px 0px -8% 0px', threshold: 0.12 }
    );

    animated.forEach(function (el) {
      observer.observe(el);
    });

    document.querySelectorAll('.product-grid').forEach(function (grid) {
      grid.querySelectorAll('.product-card').forEach(function (card, index) {
        card.setAttribute('data-animate', '');
        card.setAttribute('data-animate-delay', String((index % 4) + 1));
        observer.observe(card);
      });
    });
  }

function initCatalogueFilters() {
    var root = document.querySelector('.catalogue-filters--accordion');
    if (!root || root.dataset.accordionBound === '1') {
      return;
    }

    root.dataset.accordionBound = '1';
    root.addEventListener('click', function (e) {
      var btn = e.target.closest('.catalogue-filters__toggle');
      if (!btn || !root.contains(btn)) {
        return;
      }

      e.preventDefault();
      var group = btn.closest('.catalogue-filters__group');
      var expanded = !group.classList.contains('is-expanded');

      group.classList.toggle('is-expanded', expanded);
      btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    });
  }

function initCatalogueLive() {
    var page = document.querySelector('.catalogue-page');
    var results = document.getElementById('catalogue-results');
    var hero = document.getElementById('catalogue-hero');
    var cfg = window.anrhpubCatalogue;

    if (!page || !results || !cfg) {
      return;
    }

    var activeController = null;

    function toPartialUrl(href) {
      var url = new URL(href, window.location.origin);
      url.searchParams.set(cfg.partialKey, cfg.partialVal);
      return url.toString();
    }

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

    function syncSearchInputFromUrl(href) {
      var input = document.getElementById('global-catalogue-search');
      var clearBtn = input && input.closest('[data-global-product-search]')
        ? input.closest('[data-global-product-search]').querySelector('[data-search-clear]')
        : null;
      if (!input) {
        return;
      }

      var url = new URL(href || window.location.href, window.location.origin);
      var q = url.searchParams.get(cfg.searchKey) || '';

      input.value = q;
      if (clearBtn) {
        clearBtn.hidden = q.length === 0;
      }
    }

    function isCatalogueNavLink(link) {
      if (!link || !link.href || link.hasAttribute('download')) {
        return false;
      }
      if (link.closest('.product-card')) {
        return false;
      }
      if (link.closest('.catalogue-filters')) {
        return true;
      }
      if (link.closest('.catalogue-search-hints')) {
        return true;
      }
      return !!link.closest('.catalogue-results .pagination, .catalogue-results .nav-links');
    }

    function updateBreadcrumbs(html) {
      if (!html) {
        return;
      }
      var wrap = document.getElementById('site-breadcrumb');
      if (wrap) {
        wrap.outerHTML = html;
      }
    }

    function updateHero(heroData) {
      if (!hero || !heroData) {
        return;
      }
      var kicker = hero.querySelector('.page-hero__kicker');
      var title = hero.querySelector('.page-hero__title');
      var lead = hero.querySelector('.page-hero__lead');
      if (kicker) {
        kicker.textContent = heroData.kicker || '';
      }
      if (title) {
        title.textContent = heroData.title || '';
      }
      if (lead) {
        lead.textContent = heroData.lead || '';
      }
    }

    function markProductCardsVisible() {
      results.querySelectorAll('.product-card').forEach(function (card) {
        card.classList.add('is-visible');
      });
    }

    function setLoading(loading) {
      results.classList.toggle('is-loading', loading);
      results.setAttribute('aria-busy', loading ? 'true' : 'false');
    }

    function loadCatalogue(url, options) {
      options = options || {};

      if (activeController) {
        activeController.abort();
      }

      activeController = new AbortController();
      setLoading(true);

      fetch(toPartialUrl(url), {
        signal: activeController.signal,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('HTTP ' + response.status);
          }
          return response.json();
        })
        .then(function (data) {
          if (!data || !data.results) {
            throw new Error('Invalid payload');
          }

          results.innerHTML = data.results;

          if (data.filters) {
            var list = document.getElementById('catalogue-filters-list');
            if (list && list.parentNode) {
              list.outerHTML = data.filters;
            }
          }

          updateHero(data.hero);
          updateBreadcrumbs(data.breadcrumbs);

          if (data.title) {
            document.title = data.title;
          }

          var canonical = data.url || url;
          if (!options.replaceState) {
            history.pushState({ catalogue: true }, '', canonical);
          } else {
            history.replaceState({ catalogue: true }, '', canonical);
          }

          syncSearchInputFromUrl(canonical);
          markProductCardsVisible();

          if (!options.skipScroll) {
            var top = results.getBoundingClientRect().top + window.scrollY - (header ? header.offsetHeight + 16 : 80);
            window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
          }
        })
        .catch(function (err) {
          if (err && err.name === 'AbortError') {
            return;
          }
          window.location.href = url;
        })
        .finally(function () {
          setLoading(false);
          activeController = null;
        });
    }

    page.addEventListener('click', function (e) {
      var link = e.target.closest('a');
      if (!link || !isCatalogueNavLink(link)) {
        return;
      }
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) {
        return;
      }

      e.preventDefault();
      loadCatalogue(link.href);
    });

    window.addEventListener('popstate', function () {
      if (!page.isConnected) {
        return;
      }
      loadCatalogue(window.location.href, { replaceState: true, skipScroll: true });
    });

    window.anrhpubCatalogueLive = {
      load: loadCatalogue,
      buildUrl: buildCatalogueUrl,
      syncInput: syncSearchInputFromUrl,
      cfg: cfg
    };

    syncSearchInputFromUrl(window.location.href);
  }

function initGlobalProductSearch() {
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

function initAccountToasts() {
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

function initProductGallery() {
    document.querySelectorAll('[data-product-gallery]').forEach(function (gallery) {
      var main = gallery.querySelector('[data-gallery-main]');
      var thumbs = gallery.querySelectorAll('[data-gallery-thumb]');
      if (!main || !thumbs.length) {
        return;
      }

      thumbs.forEach(function (btn) {
        btn.addEventListener('click', function () {
          var src = btn.getAttribute('data-src');
          if (!src) {
            return;
          }
          main.src = src;
          if (main.srcset) {
            main.removeAttribute('srcset');
            main.removeAttribute('sizes');
          }
          thumbs.forEach(function (other) {
            var active = other === btn;
            other.classList.toggle('is-active', active);
            other.setAttribute('aria-selected', active ? 'true' : 'false');
          });
        });
      });
    });
  }

  function initProductTabs() {
    var root = document.querySelector('[data-product-tabs]');
    if (!root) {
      return;
    }

    var tabs = root.querySelectorAll('[data-product-tab]');
    var panels = root.querySelectorAll('[data-product-panel]');

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var name = tab.getAttribute('data-product-tab');
        tabs.forEach(function (t) {
          var active = t.getAttribute('data-product-tab') === name;
          t.classList.toggle('is-active', active);
          t.setAttribute('aria-selected', active ? 'true' : 'false');
          t.tabIndex = active ? 0 : -1;
        });
        panels.forEach(function (panel) {
          var active = panel.getAttribute('data-product-panel') === name;
          panel.classList.toggle('is-active', active);
          if (active) {
            panel.removeAttribute('hidden');
          } else {
            panel.setAttribute('hidden', '');
          }
        });
      });
    });
  }

function initQuoteCart() {
    var cfg = window.anrhpubQuoteCart;
    if (!cfg) {
      return;
    }

    function loadCart() {
      try {
        var raw = localStorage.getItem(cfg.storageKey);
        if (raw) {
          var parsed = JSON.parse(raw);
          if (Array.isArray(parsed)) {
            return parsed;
          }
        }
      } catch (err) {
        /* ignore */
      }
      return [];
    }

    function lineKey(productId, colorId) {
      return String(parseInt(productId, 10)) + ':' + String(parseInt(colorId, 10) || 0);
    }

    function getMinQty(productId, colorId, el) {
      var min = 1;
      if (el) {
        var qtyInput = el.matches && el.matches('[data-quote-qty-input]')
          ? el
          : el.querySelector && el.querySelector('[data-quote-qty-input]');
        if (qtyInput && qtyInput.getAttribute('data-quote-min-qty')) {
          min = parseInt(qtyInput.getAttribute('data-quote-min-qty'), 10);
        } else if (el.getAttribute('data-quote-min-qty')) {
          min = parseInt(el.getAttribute('data-quote-min-qty'), 10);
        }
      }
      if (min === 1 && productId) {
        var selector = '[data-quote-line][data-product-id="' + productId + '"]';
        if (colorId !== undefined && colorId !== null) {
          selector += '[data-color-id="' + (parseInt(colorId, 10) || 0) + '"]';
        }
        var row = document.querySelector(selector);
        if (row) {
          min = parseInt(row.getAttribute('data-quote-min-qty') || '1', 10);
        }
      }
      if (isNaN(min) || min < 1) {
        min = 1;
      }
      return Math.min(99999, min);
    }

    function getMaxQty(productId, colorId, el) {
      var max = 99999;
      if (el && el.getAttribute('data-quote-max-qty')) {
        max = parseInt(el.getAttribute('data-quote-max-qty'), 10);
      } else if (productId) {
        var selector = '[data-quote-line][data-product-id="' + productId + '"]';
        if (colorId !== undefined && colorId !== null) {
          selector += '[data-color-id="' + (parseInt(colorId, 10) || 0) + '"]';
        }
        var row = document.querySelector(selector);
        if (row && row.getAttribute('data-quote-max-qty')) {
          max = parseInt(row.getAttribute('data-quote-max-qty'), 10);
        }
      }
      if (isNaN(max) || max < 1) {
        max = 99999;
      }
      return Math.min(99999, max);
    }

    function getProductQuoteMaxQty(wrap) {
      return 99999;
    }

    function syncQuoteQtyControl(wrap) {
      if (!wrap) {
        return;
      }
      var input = wrap.querySelector('[data-quote-qty-input]');
      var minusBtn = wrap.querySelector('[data-quote-qty-minus]');
      var plusBtn = wrap.querySelector('[data-quote-qty-plus]');
      if (!input) {
        return;
      }
      var min = getMinQty(wrap.getAttribute('data-product-id'), getSelectedColorId(wrap), wrap);
      var max = Math.max(min, getProductQuoteMaxQty(wrap));
      var current = normalizeQty(input.value, min, max);
      input.value = String(current);
      input.setAttribute('min', String(min));
      input.setAttribute('max', String(max));
      input.setAttribute('data-quote-max-qty', String(max));
      wrap.setAttribute('data-quote-max-qty', String(max));
      if (minusBtn) {
        minusBtn.disabled = current <= min;
      }
      if (plusBtn) {
        plusBtn.disabled = current >= max;
      }
    }

    function applyProductColorLimits(wrap) {
      syncColorPickerSelection(wrap);
      syncQuoteQtyControl(wrap);
    }

    function syncColorPickerSelection(wrap) {
      if (!wrap) {
        return;
      }
      wrap.querySelectorAll('.product-color-picker__option').forEach(function (opt) {
        var radio = opt.querySelector('[data-quote-color-input]');
        opt.classList.toggle('is-selected', radio && radio.checked);
      });
    }

    function getSelectedColorId(wrap) {
      if (!wrap || wrap.getAttribute('data-requires-color') !== '1') {
        return 0;
      }
      var checked = wrap.querySelector('[data-quote-color-input]:checked');
      return checked ? parseInt(checked.value, 10) || 0 : 0;
    }

    function normalizeQty(qty, minQty, maxQty) {
      var min = minQty || 1;
      var max = maxQty || 99999;
      if (max < min) {
        max = min;
      }
      var n = parseInt(qty, 10);
      if (isNaN(n)) {
        n = min;
      }
      n = Math.max(min, Math.min(max, n));
      return n;
    }

    function sanitizeItems(items) {
      var map = {};
      var mins = {};
      var maxs = {};
      (items || []).forEach(function (item) {
        if (!item || !item.product_id) {
          return;
        }
        var id = parseInt(item.product_id, 10);
        var cid = parseInt(item.color_id, 10) || 0;
        if (id > 0) {
          var key = lineKey(id, cid);
          map[key] = (map[key] || 0) + normalizeQty(item.qty, 1);
          mins[key] = getMinQty(String(id), cid);
          maxs[key] = getMaxQty(String(id), cid);
        }
      });
      return Object.keys(map).map(function (key) {
        var parts = key.split(':');
        var pid = parseInt(parts[0], 10);
        var cid = parseInt(parts[1], 10) || 0;
        return {
          product_id: pid,
          color_id: cid,
          qty: normalizeQty(map[key], mins[key] || 1, maxs[key] || 99999)
        };
      });
    }

    function emitCartUpdated(detail) {
      window.dispatchEvent(
        new CustomEvent('anrhpub:cart-updated', {
          detail: detail || {}
        })
      );
    }

    function renderEmptyCartHtml() {
      var catalogue = cfg.catalogueUrl || '/catalogue/';
      return (
        '<div class="quote-cart-empty" data-quote-cart-empty>' +
        '<p>' + (cfg.i18n.empty || 'Votre panier devis est vide.') + '</p>' +
        '<a class="btn btn--primary" href="' + catalogue + '">' +
        'Parcourir le catalogue</a></div>'
      );
    }

    function updateRecapPanels(summary, hasItems) {
      var box = document.querySelector('[data-quote-recap-box]');
      var textEl = document.querySelector('[data-quote-recap-text]');
      var contactNotice = document.querySelector('[data-quote-contact-notice]');
      var contactText = document.querySelector('[data-quote-contact-summary]');

      if (!hasItems) {
        if (box) {
          box.hidden = true;
        }
        if (contactNotice) {
          contactNotice.hidden = true;
        }
        if (textEl) {
          textEl.textContent = '';
        }
        sessionStorage.removeItem('anrhpub_quote_summary');
        return;
      }

      if (textEl && summary) {
        textEl.textContent = summary;
      }
      if (box) {
        box.hidden = false;
      }
      if (contactNotice && contactText) {
        contactNotice.hidden = false;
        if (summary) {
          contactText.textContent = summary;
        }
      }
      if (summary) {
        sessionStorage.setItem('anrhpub_quote_summary', summary);
      }
    }

    function applyServerCartData(items, data) {
      data = data || {};
      var app = document.querySelector('[data-quote-cart-app]');

      if (app) {
        if (typeof data.html === 'string') {
          app.innerHTML = data.html;
        } else if (!items.length) {
          app.innerHTML = renderEmptyCartHtml();
        }
        app.classList.remove('is-syncing');
      }

      updateRecapPanels(data.summary || '', items.length > 0);
      emitCartUpdated({
        items: items,
        summary: data.summary || '',
        devisMessage: data.devis_message || '',
        productLines: data.product_lines || [],
        count: typeof data.count === 'number' ? data.count : items.length
      });
    }

    function saveCart(items) {
      var clean = sanitizeItems(items);
      localStorage.setItem(cfg.storageKey, JSON.stringify(clean));
      updateBadges(clean);

      var app = document.querySelector('[data-quote-cart-app]');
      if (app) {
        app.classList.add('is-syncing');
        if (!clean.length) {
          app.innerHTML = renderEmptyCartHtml();
        }
      }

      if (!clean.length) {
        updateRecapPanels('', false);
      }

      return syncServer(clean).then(function (res) {
        if (res.success && res.data) {
          applyServerCartData(clean, res.data);
        } else {
          if (app) {
            app.classList.remove('is-syncing');
          }
          emitCartUpdated({ items: clean });
          if (!clean.length) {
            updateRecapPanels('', false);
          }
        }
        return clean;
      });
    }

    function updateBadges(items) {
      var count = items.length;
      document.querySelectorAll('[data-quote-cart-count]').forEach(function (el) {
        el.textContent = String(count);
        el.hidden = count <= 0;
      });
    }

    function buildSummaryText(items) {
      if (!items.length) {
        return '';
      }
      var lines = ['Demande de devis — sélection catalogue:', ''];
      items.forEach(function (item) {
        var color = item.color_id ? ' (couleur #' + item.color_id + ')' : '';
        lines.push('- Produit #' + item.product_id + color + ' — ' + item.qty + ' unité(s)');
      });
      return lines.join('\n');
    }

    function buildSummaryFromHtml() {
      var rows = document.querySelectorAll('[data-quote-line]');
      if (!rows.length) {
        return '';
      }
      var lines = ['Demande de devis — sélection catalogue:', ''];
      rows.forEach(function (row) {
        var title = row.querySelector('.quote-cart-table__product a');
        var refCell = row.cells && row.cells[1] ? row.cells[1].textContent.trim() : '';
        var colorCell = row.querySelector('.quote-cart-table__color');
        var colorName = colorCell ? colorCell.textContent.replace(/\s+/g, ' ').trim() : '';
        var qtyInput = row.querySelector('[data-quote-line-qty]');
        var name = title ? title.textContent.trim() : '';
        var ref = refCell && refCell !== '—' ? ' [' + refCell + ']' : '';
        var color = colorName && colorName !== '—' ? ' — Couleur : ' + colorName : '';
        var qty = qtyInput ? qtyInput.value : '1';
        lines.push('- ' + name + ref + color + ' — ' + qty + ' unité(s)');
      });
      return lines.join('\n');
    }

    function syncServer(items) {
      var body = new FormData();
      body.append('action', 'anrhpub_sync_quote_cart');
      body.append('nonce', cfg.nonce);
      body.append('cart', JSON.stringify(items));

      return fetch(cfg.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (r) {
          return r.json();
        })
        .catch(function () {
          return { success: false };
        });
    }

    function addToCart(productId, qty, colorId, contextEl) {
      var items = loadCart();
      var id = parseInt(productId, 10);
      var cid = parseInt(colorId, 10) || 0;
      var min = getMinQty(String(id), cid, contextEl);
      var max = getMaxQty(String(id), cid, contextEl);
      var requested = parseInt(qty, 10);
      var addQty = normalizeQty(qty, min, max);
      var key = lineKey(id, cid);
      var found = false;
      var adjusted = !isNaN(requested) && requested < min;

      items = items.map(function (item) {
        if (lineKey(item.product_id, item.color_id) === key) {
          found = true;
          return {
            product_id: id,
            color_id: cid,
            qty: normalizeQty((parseInt(item.qty, 10) || 0) + addQty, min, max)
          };
        }
        return item;
      });

      if (!found) {
        items.push({ product_id: id, color_id: cid, qty: addQty });
      }

      return saveCart(items).then(function (result) {
        if (adjusted && window.anrhpubShowToast) {
          window.anrhpubShowToast(cfg.i18n.qtyAdjusted, 'info', 3200);
        }
        if (!isNaN(requested) && requested > max && window.anrhpubShowToast) {
          window.anrhpubShowToast(cfg.i18n.stockCapped, 'info', 3200);
        }
        return result;
      });
    }

    function setLineQty(productId, qty, colorId, contextEl) {
      var id = parseInt(productId, 10);
      var cid = parseInt(colorId, 10) || 0;
      var min = getMinQty(String(id), cid, contextEl);
      var max = getMaxQty(String(id), cid, contextEl);
      var requested = parseInt(qty, 10);
      var finalQty = normalizeQty(qty, min, max);
      var key = lineKey(id, cid);
      var items = loadCart().filter(function (item) {
        return lineKey(item.product_id, item.color_id) !== key;
      });
      if (finalQty >= min) {
        items.push({ product_id: id, color_id: cid, qty: finalQty });
      }
      return saveCart(items).then(function (result) {
        if (!isNaN(requested) && requested < min && window.anrhpubShowToast) {
          window.anrhpubShowToast(cfg.i18n.qtyAdjusted, 'info', 3200);
        }
        if (!isNaN(requested) && requested > max && window.anrhpubShowToast) {
          window.anrhpubShowToast(cfg.i18n.stockCapped, 'info', 3200);
        }
        return result;
      });
    }

    function removeLine(productId, colorId) {
      var key = lineKey(productId, colorId);
      var items = loadCart().filter(function (item) {
        return lineKey(item.product_id, item.color_id) !== key;
      });
      return saveCart(items);
    }

    window.anrhpubQuoteCartAdd = addToCart;

    if (cfg.syncOnLoad && cfg.serverCart && cfg.serverCart.length) {
      localStorage.setItem(cfg.storageKey, JSON.stringify(sanitizeItems(cfg.serverCart)));
    }

    var initial = sanitizeItems(loadCart());
    updateBadges(initial);

    if (document.querySelector('[data-quote-cart-app]') || (window.anrhpubContactDevis && window.anrhpubContactDevis.isDevis)) {
      syncServer(initial).then(function (res) {
        if (res.success && res.data) {
          applyServerCartData(initial, res.data);
        } else {
          emitCartUpdated({ items: initial });
        }
      });
    }

    document.querySelectorAll('[data-product-quote]').forEach(function (wrap) {
      var productId = wrap.getAttribute('data-product-id');
      var input = wrap.querySelector('[data-quote-qty-input]');
      var addBtn = wrap.querySelector('[data-quote-add]');

      function limits() {
        var colorId = getSelectedColorId(wrap);
        var min = getMinQty(productId, colorId, wrap);
        return {
          min: min,
          max: Math.max(min, getProductQuoteMaxQty(wrap))
        };
      }

      applyProductColorLimits(wrap);
      syncColorPickerSelection(wrap);

      wrap.querySelectorAll('[data-quote-color-input]').forEach(function (radio) {
        radio.addEventListener('change', function () {
          syncColorPickerSelection(wrap);
          applyProductColorLimits(wrap);
        });
      });

      wrap.querySelectorAll('[data-quote-qty-minus]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var lim = limits();
          input.value = String(normalizeQty(parseInt(input.value, 10) - 1, lim.min, lim.max));
          syncQuoteQtyControl(wrap);
        });
      });

      wrap.querySelectorAll('[data-quote-qty-plus]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var lim = limits();
          input.value = String(normalizeQty(parseInt(input.value, 10) + 1, lim.min, lim.max));
          syncQuoteQtyControl(wrap);
        });
      });

      if (input) {
        input.addEventListener('change', function () {
          var lim = limits();
          var before = parseInt(input.value, 10);
          input.value = String(normalizeQty(input.value, lim.min, lim.max));
          if (!isNaN(before) && before < lim.min && window.anrhpubShowToast) {
            window.anrhpubShowToast(cfg.i18n.qtyAdjusted, 'info', 3200);
          }
          syncQuoteQtyControl(wrap);
        });
        input.addEventListener('input', function () {
          syncQuoteQtyControl(wrap);
        });
      }

      if (addBtn) {
        addBtn.addEventListener('click', function () {
          var colorId = getSelectedColorId(wrap);
          if (wrap.getAttribute('data-requires-color') === '1' && !colorId) {
            if (window.anrhpubShowToast) {
              window.anrhpubShowToast(cfg.i18n.colorRequired, 'error');
            }
            return;
          }
          addToCart(productId, input.value, colorId, wrap).then(function () {
            var limAfter = limits();
            input.value = String(normalizeQty(input.value, limAfter.min, limAfter.max));
            if (window.anrhpubShowToast) {
              window.anrhpubShowToast(cfg.i18n.added, 'success', 3200);
            }
          });
        });
      }
    });

    var lineQtyDebounce = null;

    document.addEventListener('click', function (e) {
      var removeBtn = e.target.closest('[data-quote-line-remove]');
      if (removeBtn) {
        var row = removeBtn.closest('[data-quote-line]');
        if (row) {
          var productId = row.getAttribute('data-product-id');
          var colorId = row.getAttribute('data-color-id');
          var tbody = row.closest('tbody');
          row.classList.add('is-removing');
          row.remove();
          if (tbody && !tbody.querySelector('[data-quote-line]')) {
            var app = document.querySelector('[data-quote-cart-app]');
            if (app) {
              app.innerHTML = renderEmptyCartHtml();
            }
          }
          removeLine(productId, colorId).then(function () {
            if (window.anrhpubShowToast) {
              window.anrhpubShowToast(cfg.i18n.removed, 'info', 2800);
            }
          });
        }
        return;
      }

      var copyBtn = e.target.closest('[data-quote-copy-recap]');
      if (copyBtn) {
        var text = document.querySelector('[data-quote-recap-text]');
        if (text && navigator.clipboard) {
          navigator.clipboard.writeText(text.textContent || '');
          if (window.anrhpubShowToast) {
            window.anrhpubShowToast(cfg.i18n.copied, 'success', 2500);
          }
        }
      }
    });

    function commitLineQty(qtyInput, showToast) {
      var row = qtyInput.closest('[data-quote-line]');
      if (!row) {
        return;
      }
      setLineQty(row.getAttribute('data-product-id'), qtyInput.value, row.getAttribute('data-color-id'), row).then(function () {
        qtyInput.value = String(
          normalizeQty(
            qtyInput.value,
            getMinQty(row.getAttribute('data-product-id'), row.getAttribute('data-color-id'), row),
            getMaxQty(row.getAttribute('data-product-id'), row.getAttribute('data-color-id'), row)
          )
        );
        if (showToast && window.anrhpubShowToast) {
          window.anrhpubShowToast(cfg.i18n.updated, 'success', 2200);
        }
      });
    }

    document.addEventListener('change', function (e) {
      var qtyInput = e.target.closest('[data-quote-line-qty]');
      if (qtyInput) {
        commitLineQty(qtyInput, true);
      }
    });

    document.addEventListener('input', function (e) {
      var qtyInput = e.target.closest('[data-quote-line-qty]');
      if (!qtyInput) {
        return;
      }
      clearTimeout(lineQtyDebounce);
      lineQtyDebounce = setTimeout(function () {
        commitLineQty(qtyInput, false);
      }, 400);
    });

    function getDevisProductLineParts(line) {
      line = line || {};
      var title = line.title || '';
      var qty = Math.max(1, parseInt(line.qty, 10) || 1);
      var details = ['Quantité : ' + qty + (qty > 1 ? ' unités' : ' unité')];

      if (line.ref) {
        title += ' — réf. ' + line.ref;
      }
      if (line.color_name) {
        details.push('Couleur : ' + line.color_name);
      } else if (line.has_colors) {
        details.push('Couleur : à définir avec vous');
      }
      if (line.min_qty && parseInt(line.min_qty, 10) > 1) {
        details.push('Quantité minimum catalogue : ' + parseInt(line.min_qty, 10) + ' unités');
      }

      return { title: title, details: details };
    }

    function formatProductLine(line) {
      var parts = getDevisProductLineParts(line);
      return parts.title + ' · ' + parts.details.join(' · ');
    }

    function buildClientDevisMessage(productLines, devisCfg) {
      devisCfg = devisCfg || window.anrhpubContactDevis || {};
      var profile = devisCfg.clientProfile || {};
      var lines = productLines && productLines.length ? productLines : [];
      var totalQty = 0;
      var message = [];

      lines.forEach(function (line) {
        totalQty += Math.max(1, parseInt(line.qty, 10) || 1);
      });

      message.push('Bonjour,');
      message.push('');

      if (lines.length) {
        message.push('Je vous contacte pour obtenir un devis sur la sélection d’objets publicitaires ci-dessous. Voici le détail de ma demande.');
      } else {
        message.push('Je vous contacte pour obtenir un devis sur des objets publicitaires. Mon panier est encore vide sur le site : pourriez-vous m’aider à finaliser ma sélection ?');
      }

      message.push('');

      if (profile.name || profile.email || profile.company || profile.phone) {
        message.push('Mes coordonnées');
        if (profile.name) {
          message.push('Nom : ' + profile.name);
        }
        if (profile.company) {
          message.push('Société : ' + profile.company);
        }
        if (profile.email) {
          message.push('E-mail : ' + profile.email);
        }
        if (profile.phone) {
          message.push('Téléphone : ' + profile.phone);
        }
        message.push('');
      }

      message.push('Adresse de livraison souhaitée');
      if (devisCfg.deliveryAddress) {
        devisCfg.deliveryAddress.split(/\r\n|\r|\n/).forEach(function (row) {
          row = row.trim();
          if (row) {
            message.push(row);
          }
        });
      } else {
        message.push(devisCfg.noDeliveryHint || 'Je n’ai pas encore renseigné d’adresse de livraison dans mon compte. Merci de me recontacter pour la valider avant l’établissement du devis.');
      }

      message.push('');
      message.push('Marquage (logo / personnalisation)');
      if (devisCfg.brandLogoUrl) {
        message.push('Mon logo est enregistré sur mon compte client — lien pour téléchargement :');
        message.push(devisCfg.brandLogoUrl);
        message.push('Si vous avez besoin d’un fichier vectoriel (AI, EPS, PDF), je peux vous l’envoyer par e-mail.');
      } else {
        message.push('Je n’ai pas encore déposé de logo sur mon compte. Indiquez-moi comment vous souhaitez le recevoir pour chiffrer le marquage.');
      }

      message.push('');
      message.push('Détail des articles demandés');
      message.push('');

      if (lines.length) {
        lines.forEach(function (line, index) {
          var parts = getDevisProductLineParts(line);
          message.push((index + 1) + '. ' + parts.title);
          parts.details.forEach(function (detail) {
            message.push('   • ' + detail);
          });
          message.push('');
        });
        message.pop();
        message.push('');
        message.push('Total : ' + totalQty + ' unités sur l’ensemble de la commande.');
        message.push('Nombre de références : ' + lines.length + '.');
      } else {
        message.push('Aucun article dans le panier pour le moment.');
      }

      message.push('');
      message.push('Pourriez-vous me faire parvenir vos tarifs, les délais (fabrication et livraison) ainsi que les options de marquage disponibles (emplacements, couleurs, etc.) ?');
      message.push('');
      message.push('Merci d’avance pour votre retour.');
      message.push('');
      message.push('Cordialement,');
      if (profile.name) {
        message.push(profile.name);
      }
      if (profile.company) {
        message.push(profile.company);
      }

      return message.join('\n');
    }

    function buildCartLinesForDevisMessage(productLines) {
      if (productLines && productLines.length) {
        return productLines.map(function (line) {
          return {
            product_id: line.product_id || 0,
            title: line.title || '',
            ref: line.ref || '',
            color_name: line.color_name || '',
            qty: line.qty || 1,
            min_qty: line.min_qty || 1,
            has_colors: !!line.has_colors
          };
        });
      }

      var rows = document.querySelectorAll('[data-quote-line]');
      var lines = [];

      if (rows.length) {
        rows.forEach(function (row) {
          var title = row.querySelector('.quote-cart-table__product a');
          var refCell = row.cells && row.cells[1] ? row.cells[1].textContent.trim() : '';
          var colorCell = row.querySelector('.quote-cart-table__color');
          var colorName = colorCell ? colorCell.textContent.replace(/\s+/g, ' ').trim() : '';
          var qtyInput = row.querySelector('[data-quote-line-qty]');
          var name = title ? title.textContent.trim() : '';
          var ref = refCell && refCell !== '—' ? refCell : '';
          var color = colorName && colorName !== '—' ? colorName : '';
          var qty = qtyInput ? parseInt(qtyInput.value, 10) || 1 : 1;
          var minQty = parseInt(row.getAttribute('data-quote-min-qty') || '1', 10) || 1;
          lines.push({
            product_id: parseInt(row.getAttribute('data-product-id') || '0', 10) || 0,
            title: name,
            ref: ref,
            color_name: color,
            qty: qty,
            min_qty: minQty,
            has_colors: color !== '' || row.querySelector('.quote-cart-table__color-swatch') !== null
          });
        });
        return lines;
      }

      loadCart().forEach(function (item) {
        lines.push({
          product_id: item.product_id,
          title: 'Produit #' + item.product_id,
          ref: '',
          color_name: item.color_id ? 'couleur #' + item.color_id : '',
          qty: item.qty || 1,
          min_qty: 1,
          has_colors: !!item.color_id
        });
      });

      return lines;
    }

    function updateContactCartPreview(detail) {
      var preview = document.querySelector('[data-contact-cart-preview]');
      var list = document.querySelector('[data-contact-cart-list]');
      var empty = document.querySelector('[data-contact-cart-empty]');
      if (!preview || !list) {
        return;
      }

      var productLines = (detail && detail.productLines) || [];
      list.innerHTML = '';

      if (!productLines.length) {
        preview.hidden = true;
        if (empty) {
          empty.hidden = false;
        }
        return;
      }

      preview.hidden = false;
      if (empty) {
        empty.hidden = true;
      }

      productLines.forEach(function (line) {
        var li = document.createElement('li');
        li.textContent = formatProductLine(line);
        list.appendChild(li);
      });
    }

    function updateContactLogoBlock(devisCfg) {
      var box = document.querySelector('[data-contact-logo-box]');
      var img = document.querySelector('[data-contact-logo-preview]');
      var hidden = document.querySelector('[data-contact-logo-hidden]');
      var emptyHint = document.querySelector('[data-contact-logo-empty]');
      if (!box) {
        return;
      }

      var url = (devisCfg && devisCfg.brandLogoUrl) || '';
      if (url && img && hidden) {
        box.hidden = false;
        box.classList.remove('contact-devis-logo--empty');
        img.hidden = false;
        img.src = url;
        img.alt = (devisCfg && devisCfg.brandLogoAlt) || '';
        hidden.value = url;
        if (emptyHint) {
          emptyHint.hidden = true;
        }
      } else if (hidden) {
        hidden.value = '';
        if (img) {
          img.hidden = true;
          img.removeAttribute('src');
        }
        if (emptyHint) {
          emptyHint.hidden = false;
        }
        box.classList.add('contact-devis-logo--empty');
      }
    }

    function refreshContactFromCart(detail) {
      var devisCfg = window.anrhpubContactDevis;
      if (!devisCfg || !devisCfg.isDevis) {
        return;
      }

      var contactMessage = document.querySelector('[data-contact-message]');
      if (!contactMessage) {
        return;
      }

      detail = detail || {};
      var productLines = buildCartLinesForDevisMessage(detail.productLines);
      var message = detail.devisMessage || buildClientDevisMessage(productLines, devisCfg);

      contactMessage.value = message;
      sessionStorage.setItem('anrhpub_quote_summary', message);
      updateContactCartPreview(detail);
      updateContactLogoBlock(devisCfg);

      if (devisCfg.deliveryAddress) {
        var hidden = document.querySelector('[data-contact-delivery-hidden]');
        if (hidden) {
          hidden.value = devisCfg.deliveryAddress;
        }
      }
    }

    window.addEventListener('anrhpub:cart-updated', function (e) {
      refreshContactFromCart(e.detail || {});
    });

    window.addEventListener('storage', function (e) {
      if (e.key !== cfg.storageKey) {
        return;
      }
      var items = [];
      try {
        items = sanitizeItems(JSON.parse(e.newValue || '[]'));
      } catch (err) {
        items = [];
      }
      updateBadges(items);
      syncServer(items).then(function (res) {
        if (res.success && res.data) {
          applyServerCartData(items, res.data);
        }
      });
    });

    if (window.anrhpubContactDevis && window.anrhpubContactDevis.isDevis) {
      refreshContactFromCart({
        devisMessage: window.anrhpubContactDevis.serverMessage || '',
        productLines: []
      });
    }

    window.addEventListener('pageshow', function (e) {
      if (!e.persisted || !(window.anrhpubContactDevis && window.anrhpubContactDevis.isDevis)) {
        return;
      }
      var items = sanitizeItems(loadCart());
      syncServer(items).then(function (res) {
        if (res.success && res.data) {
          applyServerCartData(items, res.data);
        }
      });
    });

    document.querySelectorAll('[data-quote-contact-link]').forEach(function (link) {
      link.addEventListener('click', function () {
        var items = sanitizeItems(loadCart());
        syncServer(items).then(function (res) {
          if (res.success && res.data) {
            refreshContactFromCart({
              items: items,
              summary: res.data.summary,
              devisMessage: res.data.devis_message,
              productLines: res.data.product_lines
            });
          } else {
            refreshContactFromCart({ items: items, productLines: [] });
          }
        });
      });
    });
  }

function initAutoCarousel(root, options) {
    if (!root) {
      return;
    }

    options = options || {};
    var slideSelector = options.slideSelector || '[data-carousel-slide], .home-slider__slide';
    var dotSelector = options.dotSelector || '[data-carousel-dot], [data-home-slider-dot]';
    var counterCurrent = options.counterCurrent || null;

    var slides = root.querySelectorAll(slideSelector);
    var dots = root.querySelectorAll(dotSelector);

    if (slides.length < 2) {
      return;
    }

    var intervalMs = parseInt(options.interval, 10) || 5000;
    var current = 0;
    var timer = null;
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var hideInactive = options.hideInactive !== false;

    function goTo(index) {
      current = (index + slides.length) % slides.length;
      slides.forEach(function (slide, i) {
        var active = i === current;
        slide.classList.toggle('is-active', active);
        if (hideInactive) {
          if (active) {
            slide.removeAttribute('hidden');
            slide.removeAttribute('aria-hidden');
          } else {
            slide.setAttribute('hidden', '');
            slide.setAttribute('aria-hidden', 'true');
          }
        } else {
          slide.removeAttribute('hidden');
          slide.setAttribute('aria-hidden', active ? 'false' : 'true');
        }
      });
      dots.forEach(function (dot, i) {
        var active = i === current;
        dot.classList.toggle('is-active', active);
        dot.setAttribute('aria-selected', active ? 'true' : 'false');
      });
      if (counterCurrent) {
        counterCurrent.textContent = String(current + 1);
      }
      if (typeof options.onSlideChange === 'function') {
        options.onSlideChange(current);
      }
    }

    function next() {
      goTo(current + 1);
    }

    function startAuto() {
      stopAuto();
      if (reducedMotion) {
        return;
      }
      timer = window.setInterval(next, intervalMs);
    }

    function stopAuto() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        var idx = parseInt(dot.getAttribute('data-slide-index'), 10);
        if (!isNaN(idx)) {
          goTo(idx);
          startAuto();
        }
      });
    });

    var prevBtn = root.querySelector('[data-home-slider-prev]');
    var nextBtn = root.querySelector('[data-home-slider-next]');
    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        goTo(current - 1);
        startAuto();
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        goTo(current + 1);
        startAuto();
      });
    }

    root.addEventListener('mouseenter', stopAuto);
    root.addEventListener('mouseleave', startAuto);
    root.addEventListener('focusin', stopAuto);
    root.addEventListener('focusout', function (e) {
      if (!root.contains(e.relatedTarget)) {
        startAuto();
      }
    });

    goTo(0);
    startAuto();
  }

  function initHomeSlider() {
    var root = document.querySelector('[data-home-hero-slider]');
    if (!root) {
      return;
    }

    var cfg = window.anrhpubHomeSlider || {};
    initAutoCarousel(root, {
      slideSelector: '.home-hero-slider__slide',
      dotSelector: '[data-home-slider-dot]',
      interval: cfg.interval || 5000
    });
  }

  function initHomeSpotlight() {
    var root = document.querySelector('[data-spotlight-carousel]');
    var cfg = window.anrhpubHomeSpotlight || {};
    var counterEl = root ? root.querySelector('[data-spotlight-current]') : null;
    initAutoCarousel(root, {
      slideSelector: '.hero-spotlight__slide',
      dotSelector: '[data-carousel-dot]',
      interval: cfg.interval || 6000,
      counterCurrent: counterEl
    });
  }

  function initTrustMarquee() {
    document.querySelectorAll('[data-trust-marquee]').forEach(function (viewport) {
      viewport.addEventListener('mouseenter', function () {
        viewport.classList.add('is-paused');
      });
      viewport.addEventListener('mouseleave', function () {
        viewport.classList.remove('is-paused');
      });
      viewport.addEventListener('focusin', function () {
        viewport.classList.add('is-paused');
      });
      viewport.addEventListener('focusout', function (e) {
        if (!viewport.contains(e.relatedTarget)) {
          viewport.classList.remove('is-paused');
        }
      });
    });
  }

function initNewsletter() {
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

function initB2b() {
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

initPageLoader();

bootstrapNavigation();


function initAll() {
  initScrollAnimations();
  initHomeSlider();
  initHomeSpotlight();
  initTrustMarquee();
  initNewsletter();
  initCatalogueFilters();
  initCatalogueLive();
  initGlobalProductSearch();
  initAccountToasts();
  initAccountTabs();
  initAccountFavorites();
  initProductGallery();
  initProductTabs();
  initQuoteCart();
  initB2b();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initAll);
} else {
  initAll();
}
})();
