export function initProductGallery() {
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
