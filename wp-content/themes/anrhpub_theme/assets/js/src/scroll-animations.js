export function initScrollAnimations() {
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
