#!/usr/bin/env python3
"""Genere docs/DETTE-TECHNIQUE-ANRPUB.pdf — audit dette technique ANRHPUB."""

from __future__ import annotations

import locale
from datetime import date
from pathlib import Path

from fpdf import FPDF
from fpdf.enums import XPos, YPos

ROOT = Path(__file__).resolve().parent
OUT = ROOT / "DETTE-TECHNIQUE-ANRPUB.pdf"
FONT = Path(r"C:\Windows\Fonts\arial.ttf")
FONT_B = Path(r"C:\Windows\Fonts\arialbd.ttf")

MARGIN = 14
CONTENT_W = 210 - 2 * MARGIN

C_PRIMARY = (0, 51, 102)
C_ACCENT = (0, 100, 160)
C_TEXT = (25, 30, 35)
C_ACTION = (0, 70, 120)
C_MUTED = (90, 95, 100)

RESOLVED_QUALITY = [
    ("tests/ + composer.json", "Aucun test automatisé.", "PHPUnit : auth B2B, panier devis, PDF devis (CI)."),
    ("assets/js/src/ + Vite", "Monolithe main.js 2700+ lignes.", "14 modules ES + build Vite (fallback Python)."),
    ("functions.php", "10+ CSS sur toutes les pages.", "Enqueue conditionnel : vitrine, pages, B2B par contexte."),
    ("inc/catalogue-search.php", "Requêtes illimitées par recherche.", "Limite 500 résultats, requête catégories groupée, cache transient."),
    ("inc/product-colors/", "God-module 1200+ lignes.", "Scindé en taxonomy, admin-term, admin-product, frontend."),
    ("inc/demo-data.php", "Données démo au switch thème.", "Désactivé hors local/development (WP_ENVIRONMENT_TYPE)."),
]

RESOLVED_SECURITY = [
    ("inc/b2b/pricing.php", "Calcul prix HT sans gate centralisé.", "anrhpub_is_price_visible_context() intégré dans get_unit_price_ht()."),
    ("inc/b2b/client-pro.php", "Compte sans meta = approuvé par défaut.", "Statut pending par défaut + migration comptes existants."),
    ("inc/contact-form.php", "Formulaire devis sans rate limiting ni CAPTCHA.", "Throttling IP + captcha arithmétique."),
    ("inc/newsletter.php", "Inscription AJAX sans limitation.", "Rate limit par IP et par e-mail."),
    ("inc/b2b/quotes.php", "PDF devis : capability edit_posts trop large.", "Accès restreint via edit_post sur le devis."),
    ("inc/gdpr.php", "Bandeau cookies sans blocage scripts.", "CMP minimal : blocage scripts analytics + activation au consentement."),
]

RESOLVED_CRITICAL = [
    ("bin/reset-client-access.php", "Mots de passe en dur dans le dépôt.", "Mots de passe générés via CLI/env — plus de secrets commités."),
    ("tools/migrate-prestashop.php", "Script migration accessible via HTTP.", "Exécution CLI uniquement (HTTP 403)."),
    ("inc/b2b/tools.php", "Export CSV panier sans contrôle prix B2B.", "Export réservé aux clients connectés et approuvés."),
    ("inc/b2b/tools.php", "Comparateur AJAX sans nonce.", "check_ajax_referer + nonce JS ajoutés."),
    (".github/workflows/", "Déploiement sans validation préalable.", "Job validate (lint PHP, grep secrets) avant FTP."),
    ("deploy-staging.yml", "Déploiement non vérifié après envoi.", "Smoke test HTTP post-deploy + exclusion bin/tools du bundle."),
]

SECTIONS = [
    {
        "title": "Déploiement et infrastructure",
        "items": [
            ("Élevé", "FTP OVH", "Protocole FTP en clair, déploiement non atomique.", "SFTP si disponible ; healthcheck (fait)."),
            ("Élevé", "deploy-staging.yml", "Fichiers orphelins non supprimés sur le serveur.", "Sync miroir ou nettoyage ciblé."),
            ("Moyen", "staging-health.php", "Script diagnostic en webroot.", "Retirer après validation."),
            ("Moyen", "export-staging-db.ps1", "Export DB manuel Windows uniquement.", "wp-cli search-replace cross-platform."),
            ("Faible", "README.md", "Documentation partiellement obsolète.", "Aligner sur WP 7.0 et scope deploy."),
        ],
    },
    {
        "title": "Configuration et WordPress core",
        "items": [
            ("Élevé", "wp-config.template.php", "Sels WordPress statiques dans le repo.", "Sels uniques par environnement via secrets."),
            ("Élevé", ".gitignore", "WordPress core versionné (~5500 fichiers).", "Déployer le core via Composer/wp-cli."),
            ("Moyen", "wp-config.template.php", "Pas de DISALLOW_FILE_EDIT, FORCE_SSL_ADMIN.", "Durcissement production."),
            ("Faible", "plugins/", "Akismet inutile, UpdraftPlus lourd.", "Nettoyer les plugins."),
        ],
    },
]

PRIORITY = [
    ("P1 — Important", [
        "Sels WordPress uniques par environnement",
        "Export DB avec wp search-replace (serialized-safe)",
    ]),
    ("P2 — Amélioration", [
        "Retirer staging-health du deploy automatique",
        "Réduire le scope git (core WP hors repo)",
        "Étendre la couverture PHPUnit (intégration WP)",
    ]),
    ("P3 — Confort", [
        "Audit accessibilité WCAG 2.1 AA",
        "Internationalisation complète (.pot)",
        "Stratégie production documentée",
    ]),
]

MONTHS_FR = [
    "janvier", "février", "mars", "avril", "mai", "juin",
    "juillet", "août", "septembre", "octobre", "novembre", "décembre",
]


def today_fr() -> str:
    d = date.today()
    return f"{d.day} {MONTHS_FR[d.month - 1]} {d.year}"


class DebtPDF(FPDF):
    def __init__(self) -> None:
        super().__init__(orientation="P", unit="mm", format="A4")
        self.set_margins(MARGIN, MARGIN, MARGIN)
        self.set_auto_page_break(auto=True, margin=18)
        self.add_font("Arial", "", str(FONT))
        self.add_font("Arial", "B", str(FONT_B))

    def header_bar(self, title: str) -> None:
        self.set_fill_color(*C_PRIMARY)
        self.rect(0, 0, 210, 12, style="F")
        self.set_xy(MARGIN, 3)
        self.set_font("Arial", "B", 8)
        self.set_text_color(255, 255, 255)
        self.cell(0, 6, "ANRH Publications — Dette technique", new_x=XPos.LMARGIN, new_y=YPos.NEXT)
        self.set_xy(MARGIN, 16)
        self.set_font("Arial", "B", 13)
        self.set_text_color(*C_PRIMARY)
        self.cell(0, 8, title, new_x=XPos.LMARGIN, new_y=YPos.NEXT)
        self.set_draw_color(*C_ACCENT)
        self.line(MARGIN, 26, 210 - MARGIN, 26)
        self.ln(6)

    def footer(self) -> None:
        self.set_y(-12)
        self.set_font("Arial", "", 8)
        self.set_text_color(*C_MUTED)
        self.cell(
            0,
            8,
            f"Document généré le {date.today().strftime('%d/%m/%Y')} — Page {self.page_no()}",
            align="C",
        )


def sev_style(level: str) -> tuple[tuple[int, int, int], tuple[int, int, int]]:
    styles = {
        "Critique": ((180, 30, 30), (255, 235, 235)),
        "Élevé": ((200, 90, 0), (255, 243, 230)),
        "Moyen": ((160, 120, 0), (255, 250, 230)),
        "Faible": ((40, 120, 70), (235, 248, 240)),
        "RÉSOLU": ((0, 110, 60), (225, 245, 235)),
    }
    return styles.get(level, ((0, 0, 0), (245, 245, 245)))


def draw_badge(pdf: DebtPDF, x: float, y: float, text: str, fg: tuple, bg: tuple, w: float = 24) -> None:
    pdf.set_xy(x, y)
    pdf.set_fill_color(*bg)
    pdf.set_text_color(*fg)
    pdf.set_font("Arial", "B", 7.5)
    pdf.cell(w, 5.5, text, fill=True, align="C")


def write_label_value(pdf: DebtPDF, label: str, value: str, label_color: tuple) -> None:
    pdf.set_x(MARGIN)
    pdf.set_font("Arial", "B", 9)
    pdf.set_text_color(*label_color)
    label_w = pdf.get_string_width(label) + 1
    pdf.cell(label_w, 5, label)
    pdf.set_font("Arial", "", 9)
    pdf.set_text_color(*C_TEXT)
    pdf.multi_cell(CONTENT_W - label_w, 5, value)


def write_item(pdf: DebtPDF, num: int, level: str, path: str, problem: str, action: str) -> None:
    if pdf.get_y() > 248:
        pdf.add_page()
        pdf.header_bar("Suite")

    y0 = pdf.get_y()
    fg, bg = sev_style(level)
    draw_badge(pdf, 210 - MARGIN - 24, y0, level, fg, bg)

    pdf.set_xy(MARGIN, y0)
    pdf.set_font("Arial", "B", 9.5)
    pdf.set_text_color(*C_PRIMARY)
    pdf.multi_cell(CONTENT_W - 28, 5, f"#{num}  {path}")

    pdf.ln(1)
    write_label_value(pdf, "Problème : ", problem, C_TEXT)
    pdf.ln(0.5)
    write_label_value(pdf, "Action : ", action, C_ACTION)

    pdf.ln(2)
    pdf.set_draw_color(215, 220, 225)
    pdf.line(MARGIN, pdf.get_y(), 210 - MARGIN, pdf.get_y())
    pdf.ln(3)


def main() -> None:
    try:
        locale.setlocale(locale.LC_TIME, "fr_FR.UTF-8")
    except locale.Error:
        pass

    pdf = DebtPDF()

    # Couverture
    pdf.add_page()
    pdf.set_fill_color(*C_PRIMARY)
    pdf.rect(0, 0, 210, 297, style="F")
    pdf.set_xy(0, 72)
    pdf.set_font("Arial", "B", 26)
    pdf.set_text_color(255, 255, 255)
    pdf.cell(0, 12, "ANRH Publications", align="C", new_x=XPos.LMARGIN, new_y=YPos.NEXT)
    pdf.set_font("Arial", "", 15)
    pdf.cell(0, 10, "Audit de dette technique", align="C", new_x=XPos.LMARGIN, new_y=YPos.NEXT)
    pdf.ln(6)
    pdf.set_font("Arial", "", 11)
    pdf.set_text_color(210, 225, 240)
    pdf.cell(0, 7, today_fr(), align="C", new_x=XPos.LMARGIN, new_y=YPos.NEXT)
    pdf.cell(0, 7, "https://pub.anrh.fr (staging)", align="C", new_x=XPos.LMARGIN, new_y=YPos.NEXT)
    pdf.ln(28)
    pdf.set_font("Arial", "B", 12)
    pdf.set_text_color(255, 255, 255)
    pdf.cell(0, 8, "6 dettes CRITIQUES corrigées", align="C", new_x=XPos.LMARGIN, new_y=YPos.NEXT)
    pdf.set_font("Arial", "", 10)
    pdf.set_text_color(210, 225, 240)
    pdf.cell(0, 7, "6 SÉCURITÉ + 6 QUALITÉ corrigées — sections 1 à 3", align="C", new_x=XPos.LMARGIN, new_y=YPos.NEXT)

    # Correctifs
    pdf.add_page()
    pdf.header_bar("1. Correctifs critiques appliqués")
    pdf.set_font("Arial", "", 9.5)
    pdf.set_text_color(*C_TEXT)
    pdf.multi_cell(
        CONTENT_W,
        5,
        "Les points suivants ont été corrigés le "
        + date.today().strftime("%d/%m/%Y")
        + ". Ils ne nécessitent plus d'action immédiate.",
    )
    pdf.ln(4)
    for i, (path, problem, action) in enumerate(RESOLVED_CRITICAL, 1):
        write_item(pdf, i, "RÉSOLU", path, problem, action)

    # Sécurité applicative
    pdf.add_page()
    pdf.header_bar("2. Sécurité applicative — corrigée")
    pdf.set_font("Arial", "", 9.5)
    pdf.set_text_color(*C_TEXT)
    pdf.multi_cell(
        CONTENT_W,
        5,
        "Les 6 points de la section « Sécurité applicative (restant) » ont été corrigés le "
        + date.today().strftime("%d/%m/%Y")
        + ".",
    )
    pdf.ln(4)
    for i, (path, problem, action) in enumerate(RESOLVED_SECURITY, len(RESOLVED_CRITICAL) + 1):
        write_item(pdf, i, "RÉSOLU", path, problem, action)

    # Qualité de code
    pdf.add_page()
    pdf.header_bar("3. Qualité de code et architecture — corrigée")
    pdf.set_font("Arial", "", 9.5)
    pdf.set_text_color(*C_TEXT)
    pdf.multi_cell(
        CONTENT_W,
        5,
        "Les 6 points de la section « Qualité de code et architecture » ont été corrigés le "
        + date.today().strftime("%d/%m/%Y")
        + ".",
    )
    pdf.ln(4)
    base = len(RESOLVED_CRITICAL) + len(RESOLVED_SECURITY)
    for i, (path, problem, action) in enumerate(RESOLVED_QUALITY, base + 1):
        write_item(pdf, i, "RÉSOLU", path, problem, action)

    # Restant
    item_no = 1
    sec_num = 4
    for section in SECTIONS:
        pdf.add_page()
        pdf.header_bar(f"{sec_num}. {section['title']}")
        sec_num += 1
        for level, path, problem, action in section["items"]:
            write_item(pdf, item_no, level, path, problem, action)
            item_no += 1

    # Roadmap
    pdf.add_page()
    pdf.header_bar(f"{sec_num}. Plan de remédiation")
    for title, bullets in PRIORITY:
        pdf.set_font("Arial", "B", 10.5)
        pdf.set_text_color(*C_PRIMARY)
        pdf.cell(0, 7, title, new_x=XPos.LMARGIN, new_y=YPos.NEXT)
        pdf.set_font("Arial", "", 9)
        pdf.set_text_color(*C_TEXT)
        for bullet in bullets:
            pdf.set_x(MARGIN + 2)
            pdf.cell(4, 5, "•")
            pdf.multi_cell(CONTENT_W - 6, 5, bullet)
        pdf.ln(2)

    pdf.output(str(OUT))
    print(f"PDF généré : {OUT}")


if __name__ == "__main__":
    main()
