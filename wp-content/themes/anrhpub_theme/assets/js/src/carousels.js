/**
 * Carrousels accueil / spotlight / trust.
 */

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
      dot.setAttribute('aria-current', active ? 'true' : 'false');
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

  function prev() {
    goTo(current - 1);
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
      prev();
      startAuto();
    });
  }
  if (nextBtn) {
    nextBtn.addEventListener('click', function () {
      next();
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

export function initHomeSlider() {
  var root = document.querySelector('[data-home-hero-slider]');
  if (!root) {
    return;
  }

  var cfg = window.anrhpubHomeSlider || {};
  initAutoCarousel(root, {
    slideSelector: '.home-hero-slider__slide',
    dotSelector: '[data-home-slider-dot]',
    interval: cfg.interval || 5000,
  });
}

export function initHomeSpotlight() {
  var root = document.querySelector('[data-spotlight-carousel]');
  var cfg = window.anrhpubHomeSpotlight || {};
  var counterEl = root ? root.querySelector('[data-spotlight-current]') : null;
  initAutoCarousel(root, {
    slideSelector: '.hero-spotlight__slide',
    dotSelector: '[data-carousel-dot]',
    interval: cfg.interval || 6000,
    counterCurrent: counterEl,
  });
}

export function initTrustMarquee() {
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
