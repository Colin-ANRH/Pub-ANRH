#!/usr/bin/env python3
"""Assemble assets/js/src/ en un bundle IIFE assets/js/main.js (sans Node)."""

from __future__ import annotations

import re
from pathlib import Path

THEME = Path(__file__).resolve().parents[1]
SRC = THEME / "assets" / "js" / "src"
OUT = THEME / "assets" / "js" / "main.js"

ORDER = [
    "constants.js",
    "page-loader.js",
    "navigation.js",
    "scroll-animations.js",
    "catalogue-filters.js",
    "catalogue-live.js",
    "product-search.js",
    "toast.js",
    "account.js",
    "product.js",
    "quote-cart.js",
    "carousels.js",
    "newsletter.js",
    "b2b.js",
]


def strip_module_syntax(content: str) -> str:
    content = re.sub(r"^import\s+.*?;\s*\n", "", content, flags=re.M)
    content = re.sub(r"^export\s+", "", content, flags=re.M)
    return content


def main() -> None:
    parts = ["(function () {\n  'use strict';\n\n"]
    for name in ORDER:
        path = SRC / name
        if not path.exists():
            raise SystemExit(f"Module manquant : {path}")
        parts.append(strip_module_syntax(path.read_text(encoding="utf-8")))
        if not parts[-1].endswith("\n"):
            parts[-1] += "\n"
        parts.append("\n")

    entry = (SRC / "main.js").read_text(encoding="utf-8")
    entry = strip_module_syntax(entry)
    entry = entry.replace("initPageLoader();\n", "initPageLoader();\n\n")
    entry = entry.replace("bootstrapNavigation();\n", "bootstrapNavigation();\n\n")
    parts.append(entry)
    parts.append("})();\n")

    OUT.write_text("".join(parts), encoding="utf-8")
    print(f"Bundle écrit : {OUT} ({OUT.stat().st_size} octets)")


if __name__ == "__main__":
    main()
