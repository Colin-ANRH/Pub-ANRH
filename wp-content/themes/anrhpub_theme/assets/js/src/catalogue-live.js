export function initCatalogueLive() {
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
