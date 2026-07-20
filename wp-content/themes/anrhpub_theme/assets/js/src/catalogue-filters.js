export function initCatalogueFilters() {
  var root = document.querySelector('.catalogue-filters--accordion');
  if (!root || root.dataset.accordionBound === '1') {
    return;
  }

  root.dataset.accordionBound = '1';

  function setExpanded(group, expanded) {
    var btn = group.querySelector('.catalogue-filters__toggle');
    var panel = group.querySelector('.catalogue-filters__children');
    group.classList.toggle('is-expanded', expanded);
    if (btn) {
      btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    }
    if (panel) {
      if (expanded) {
        panel.removeAttribute('hidden');
      } else {
        panel.setAttribute('hidden', '');
      }
    }
  }

  root.addEventListener('click', function (e) {
    var btn = e.target.closest('.catalogue-filters__toggle');
    if (!btn || !root.contains(btn)) {
      return;
    }

    e.preventDefault();
    var group = btn.closest('.catalogue-filters__group');
    if (!group) {
      return;
    }

    var willExpand = !group.classList.contains('is-expanded');

    // Un seul dropdown ouvert à la fois.
    root.querySelectorAll('.catalogue-filters__group.is-expanded').forEach(function (other) {
      if (other !== group) {
        setExpanded(other, false);
      }
    });

    setExpanded(group, willExpand);
  });
}
