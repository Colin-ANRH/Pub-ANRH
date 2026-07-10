#!/usr/bin/env python3
"""Découpe assets/js/main.js en modules ES sous assets/js/src/."""

from __future__ import annotations

import re
from pathlib import Path

THEME = Path(__file__).resolve().parents[1]
JS = THEME / "assets" / "js"
OUT = JS / "src"


def slice_between(body: str, start_marker: str, end_marker: str | None) -> str:
    s = body.index(start_marker)
    if end_marker:
        e = body.index(end_marker, s + 1)
        return body[s:e].rstrip()
    return body[s:].rstrip()


def main() -> None:
    text = (JS / "main.js").read_text(encoding="utf-8")
    match = re.match(r"\(function \(\) \{\s*'use strict';\s*(.*)\s*\}\)\(\);\s*$", text, re.S)
    if not match:
        raise SystemExit("main.js IIFE introuvable")
    body = match.group(1)

    chunks: dict[str, str] = {
        "constants.js": "export const MOBILE_BP = 992;\n",
        "page-loader.js": slice_between(body, "function initPageLoader()", "initPageLoader();").replace(
            "function initPageLoader", "export function initPageLoader", 1
        ),
        "navigation.js": slice_between(body, "var header = document.getElementById", "function initScrollAnimations()"),
        "scroll-animations.js": slice_between(body, "function initScrollAnimations()", "function initCatalogueFilters()"),
        "catalogue-filters.js": slice_between(body, "function initCatalogueFilters()", "function initCatalogueLive()"),
        "catalogue-live.js": slice_between(body, "function initCatalogueLive()", "function initGlobalProductSearch()"),
        "product-search.js": slice_between(body, "function initGlobalProductSearch()", "function anrhpubPlainToastMessage("),
        "toast.js": slice_between(body, "function anrhpubPlainToastMessage(", "function initAccountToasts()"),
        "account.js": slice_between(body, "function initAccountToasts()", "function initProductGallery()"),
        "product.js": slice_between(body, "function initProductGallery()", "function initQuoteCart()"),
        "quote-cart.js": slice_between(body, "function initQuoteCart()", "function initAutoCarousel("),
        "carousels.js": slice_between(body, "function initAutoCarousel(", "function initNewsletter()"),
        "newsletter.js": slice_between(body, "function initNewsletter()", "function initB2b()"),
        "b2b.js": slice_between(body, "function initB2b()", "function initAll()"),
    }

    nav = chunks["navigation.js"]
    nav = 'import { MOBILE_BP } from "./constants.js";\n\n' + nav
    nav = nav.replace("var MOBILE_BP = 992;\n  ", "")
    nav = nav.replace("  initDropdowns();\n  initAccountMenu();\n  initMegaMenu();\n", "")
    nav += "\n\nexport function bootstrapNavigation() {\n  initDropdowns();\n  initAccountMenu();\n  initMegaMenu();\n}\n"
    chunks["navigation.js"] = nav

    export_map = {
        "scroll-animations.js": "initScrollAnimations",
        "catalogue-filters.js": "initCatalogueFilters",
        "catalogue-live.js": "initCatalogueLive",
        "product-search.js": "initGlobalProductSearch",
        "quote-cart.js": "initQuoteCart",
        "newsletter.js": "initNewsletter",
    }
    for file_name, fn in export_map.items():
        chunks[file_name] = re.sub(rf"^function {fn}\(", f"export function {fn}(", chunks[file_name], count=1)

    for fn in ("initAccountToasts", "initAccountTabs", "initAccountFavorites"):
        chunks["account.js"] = re.sub(rf"^function {fn}\(", f"export function {fn}(", chunks["account.js"], count=1)

    for fn in ("initProductGallery", "initProductTabs"):
        chunks["product.js"] = re.sub(rf"^function {fn}\(", f"export function {fn}(", chunks["product.js"], count=1)

    chunks["toast.js"] = re.sub(r"^function anrhpubShowToast\(", "export function anrhpubShowToast(", chunks["toast.js"], count=1)
    chunks["toast.js"] += "\nwindow.anrhpubShowToast = anrhpubShowToast;\n"

    for fn in ("initHomeSlider", "initHomeSpotlight", "initTrustMarquee"):
        chunks["carousels.js"] = re.sub(rf"^function {fn}\(", f"export function {fn}(", chunks["carousels.js"], count=1)

    chunks["b2b.js"] = re.sub(r"^function initB2b\(", "export function initB2b(", chunks["b2b.js"], count=1)

    OUT.mkdir(parents=True, exist_ok=True)
    for name, content in chunks.items():
        (OUT / name).write_text(content.rstrip() + "\n", encoding="utf-8")

    entry = """import { initPageLoader } from './page-loader.js';
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
"""
    (OUT / "main.js").write_text(entry, encoding="utf-8")
    print(f"Modules écrits dans {OUT} ({len(chunks)} fichiers)")


if __name__ == "__main__":
    main()
