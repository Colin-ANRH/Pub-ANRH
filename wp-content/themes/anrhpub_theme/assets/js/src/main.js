import { initPageLoader } from './page-loader.js';
import { bootstrapNavigation } from './navigation.js';
import { initScrollAnimations } from './scroll-animations.js';
import { initCatalogueFilters } from './catalogue-filters.js';
import { initCatalogueLive } from './catalogue-live.js';
import { initGlobalProductSearch } from './product-search.js';
import { initAccountToasts, initAccountTabs, initAccountFavorites } from './account.js';
import { initProductGallery, initProductTabs } from './product.js';
import { initQuoteCart } from './quote-cart.js';
import { initHomeSlider, initHomeSpotlight, initTrustMarquee } from './carousels.js';
import { initNewsletter } from './newsletter.js';
import { initB2b } from './b2b.js';

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
