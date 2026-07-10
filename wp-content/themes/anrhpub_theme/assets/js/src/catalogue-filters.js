export function initCatalogueFilters() {
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
