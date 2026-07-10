import { MOBILE_BP } from "./constants.js";

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

export function bootstrapNavigation() {
  initDropdowns();
  initAccountMenu();
  initMegaMenu();
}
