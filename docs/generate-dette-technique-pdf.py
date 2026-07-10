#!/usr/bin/env python3
"""Genere docs/DETTE-TECHNIQUE-ANRPUB.pdf — audit dette technique ANRHPUB."""

from __future__ import annotations

from datetime import date
from pathlib import Path

from fpdf import FPDF
from fpdf.enums import XPos, YPos

ROOT = Path(__file__).resolve().parent
OUT = ROOT / "DETTE-TECHNIQUE-ANRPUB.pdf"
FONT = Path(r"C:\Windows\Fonts\arial.ttf")
FONT_B = Path(r"C:\Windows\Fonts\arialbd.ttf")
FONT_I = Path(r"C:\Windows\Fonts\ariali.ttf")

# Couleurs ANRH (bleu marine + accent)
C_PRIMARY = (0, 51, 102)
C_ACCENT = (0, 120, 180)
C_TEXT = (33, 37, 41)
C_MUTED = (108, 117, 125)
C_BG = (245, 247, 250)

RESOLVED_CRITICAL = [
    ("Critique", "bin/reset-client-access.php", "Mots de passe en dur dans le depot.", "RESOLU — mots de passe generes via CLI/env, plus de secrets commites."),
    ("Critique", "tools/migrate-prestashop.php", "Script migration accessible via HTTP.", "RESOLU — execution CLI uniquement (HTTP 403)."),
    ("Critique", "inc/b2b/tools.php", "Export CSV panier sans controle prix B2B.", "RESOLU — export reserve aux clients connectes et approuves."),
    ("Critique", "inc/b2b/tools.php", "Comparateur AJAX sans nonce.", "RESOLU — check_ajax_referer + nonce JS."),
    ("Critique", ".github/workflows/", "Deploy sans validation prealable.", "RESOLU — job validate (lint PHP, grep secrets) avant FTP."),
    ("Critique", "deploy-staging.yml", "Deploy non verifie apres envoi.", "RESOLU — smoke test HTTP post-deploy + exclusion bin/tools du bundle."),
]

SECTIONS = [
    {
        "title": "Securite applicative (restant)",
        "items": [
            ("Eleve", "inc/b2b/pricing.php", "Calcul prix HT sans gate centralise.", "Ajouter anrhpub_can_view_prices() dans get_unit_price_ht pour les appels front."),
            ("Eleve", "inc/b2b/client-pro.php", "Compte sans meta = approuve par defaut.", "Statut pending par defaut pour nouveaux comptes."),
            ("Eleve", "inc/contact-form.php", "Formulaire devis sans rate limiting.", "Throttling IP + CAPTCHA."),
            ("Eleve", "inc/newsletter.php", "Inscription AJAX sans limitation.", "Rate limit par IP/email."),
            ("Moyen", "inc/b2b/quotes.php", "PDF devis : capability edit_posts trop large.", "Restreindre aux roles dedies."),
            ("Moyen", "inc/gdpr.php", "Bandeau cookies sans blocage scripts.", "Integrer CMP."),
        ],
    },
    {
        "title": "Qualite de code et architecture",
        "items": [
            ("Eleve", "Theme entier", "Aucun test automatise.", "PHPUnit sur auth B2B, panier, devis."),
            ("Eleve", "assets/js/main.js", "Monolithe 2700+ lignes.", "Decouper + build Vite."),
            ("Eleve", "functions.php", "10+ CSS sur toutes les pages.", "Enqueue conditionnel."),
            ("Eleve", "inc/catalogue-search.php", "Requetes illimitees par recherche.", "Pagination / index."),
            ("Moyen", "inc/product-colors.php", "God-module 1200+ lignes.", "Scinder en modules."),
            ("Moyen", "inc/demo-data.php", "Donnees demo au switch theme.", "Desactiver en production."),
        ],
    },
    {
        "title": "Deploiement et infrastructure",
        "items": [
            ("Eleve", "FTP OVH", "Protocole FTP en clair, deploy non atomique.", "SFTP si disponible ; healthcheck (fait)."),
            ("Eleve", "deploy-staging.yml", "Fichiers orphelins non supprimes sur serveur.", "Sync miroir ou clean cible."),
            ("Moyen", "staging-health.php", "Script diagnostic en webroot.", "Retirer apres validation."),
            ("Moyen", "export-staging-db.ps1", "Export DB manuel Windows uniquement.", "wp-cli search-replace cross-platform."),
            ("Faible", "README.md", "Documentation partiellement obsolete.", "Aligner sur WP 7.0 et scope deploy."),
        ],
    },
    {
        "title": "Configuration et WordPress core",
        "items": [
            ("Eleve", "wp-config.template.php", "Sels WordPress statiques dans le repo.", "Sels uniques par env via secrets."),
            ("Eleve", ".gitignore", "WordPress core versionne (~5500 fichiers).", "Deploy core via Composer/wp-cli."),
            ("Moyen", "wp-config.template.php", "Pas de DISALLOW_FILE_EDIT, FORCE_SSL_ADMIN.", "Durcissement production."),
            ("Faible", "plugins/", "Akismet inutile, UpdraftPlus lourd.", "Nettoyer plugins."),
        ],
    },
]

PRIORITY = [
    ("P1 — Important", ["Sels WP uniques", "Export DB wp search-replace", "Rate limiting formulaires", "Tests B2B minimaux"]),
    ("P2 — Amelioration", ["Decouper main.js", "CSS conditionnels", "Retirer staging-health du deploy", "Reduire scope git (core WP)"]),
    ("P3 — Confort", ["Audit accessibilite WCAG", "i18n .pot", "Strategie production documentee"]),
]


class DebtPDF(FPDF):
    def __init__(self) -> None:
        super().__init__(orientation="P", unit="mm", format="A4")
        self.set_auto_page_break(auto=True, margin=20)
        self.add_font("Arial", "", str(FONT))
        self.add_font("Arial", "B", str(FONT_B))
        self.add_font("Arial", "I", str(FONT_I))

    def header_bar(self, title: str) -> None:
        self.set_fill_color(*C_PRIMARY)
        self.rect(0, 0, 210, 14, "F")
        self.set_xy(12, 4)
        self.set_font("Arial", "B", 9)
        self.set_text_color(255, 255, 255)
        self.cell(0, 6, "ANRH Publications — Dette technique", new_x=XPos.LMARGIN, new_y=YPos.NEXT)
        self.set_xy(12, 18)
        self.set_font("Arial", "B", 13)
        self.set_text_color(*C_PRIMARY)
        self.cell(0, 8, title, new_x=XPos.LMARGIN, new_y=YPos.NEXT)
        self.set_draw_color(*C_ACCENT)
        self.line(12, 28, 198, 28)
        self.ln(4)

    def footer(self) -> None:
        self.set_y(-14)
        self.set_font("Arial", "", 8)
        self.set_text_color(*C_MUTED)
        self.cell(0, 8, f"Document genere le {date.today().strftime('%d/%m/%Y')} — Page {self.page_no()}", align="C")


def sev_style(level: str) -> tuple[tuple[int, int, int], tuple[int, int, int]]:
    styles = {
        "Critique": ((180, 30, 30), (255, 235, 235)),
        "Eleve": ((200, 90, 0), (255, 243, 230)),
        "Moyen": ((160, 120, 0), (255, 250, 230)),
        "Faible": ((40, 120, 70), (235, 248, 240)),
        "RESOLU": ((0, 100, 60), (230, 245, 235)),
    }
    return styles.get(level, ((0, 0, 0), (255, 255, 255)))


def badge(pdf: DebtPDF, text: str, fg: tuple, bg: tuple, w: float = 22) -> None:
    pdf.set_fill_color(*bg)
    pdf.set_text_color(*fg)
    pdf.set_font("Arial", "B", 8)
    pdf.cell(w, 5, text, fill=True, align="C")


def write_item(pdf: DebtPDF, num: int, level: str, path: str, problem: str, action: str) -> None:
    fg, bg = sev_style(level)
    y0 = pdf.get_y()
    if y0 > 265:
        pdf.add_page()
        pdf.header_bar("Suite")
    pdf.set_font("Arial", "B", 9)
    pdf.set_text_color(*C_TEXT)
    pdf.cell(0, 5, f"#{num}  {path}", new_x=XPos.LMARGIN, new_y=YPos.NEXT)
    x = pdf.get_x()
    y = pdf.get_y() - 5
    pdf.set_xy(150, y)
    badge(pdf, level, fg, bg)
    pdf.set_xy(x, y + 6)
    pdf.set_font("Arial", "", 8.5)
    pdf.set_text_color(*C_TEXT)
    pdf.multi_cell(186, 4.2, f"Probleme : {problem}")
    pdf.set_font("Arial", "I", 8.5)
    pdf.set_text_color(*C_MUTED)
    pdf.multi_cell(186, 4.2, f"Action : {action}")
    pdf.ln(2)


def main() -> None:
    pdf = DebtPDF()

    # Couverture
    pdf.add_page()
    pdf.set_fill_color(*C_PRIMARY)
    pdf.rect(0, 0, 210, 297, "F")
    pdf.set_xy(0, 70)
    pdf.set_font("Arial", "B", 28)
    pdf.set_text_color(255, 255, 255)
    pdf.cell(0, 14, "ANRH Publications", align="C", new_x=XPos.LMARGIN, new_y=YPos.NEXT)
    pdf.set_font("Arial", "", 16)
    pdf.cell(0, 10, "Audit de dette technique", align="C", new_x=XPos.LMARGIN, new_y=YPos.NEXT)
    pdf.ln(8)
    pdf.set_font("Arial", "", 11)
    pdf.set_text_color(200, 220, 240)
    pdf.cell(0, 7, date.today().strftime("%d %B %Y"), align="C", new_x=XPos.LMARGIN, new_y=YPos.NEXT)
    pdf.cell(0, 7, "https://pub.anrh.fr (staging)", align="C", new_x=XPos.LMARGIN, new_y=YPos.NEXT)
    pdf.ln(30)
    pdf.set_font("Arial", "B", 11)
    pdf.set_text_color(255, 255, 255)
    pdf.cell(0, 7, "6 dettes CRITIQUES corrigees", align="C", new_x=XPos.LMARGIN, new_y=YPos.NEXT)
    pdf.set_font("Arial", "", 10)
    pdf.set_text_color(200, 220, 240)
    pdf.cell(0, 6, "Voir section 1 — Correctifs appliques", align="C", new_x=XPos.LMARGIN, new_y=YPos.NEXT)

    # Correctifs critiques
    pdf.add_page()
    pdf.header_bar("1. Correctifs critiques appliques")
    pdf.set_font("Arial", "", 9)
    pdf.set_text_color(*C_TEXT)
    pdf.multi_cell(
        186,
        4.5,
        "Les points suivants ont ete corriges le "
        + date.today().strftime("%d/%m/%Y")
        + ". Ils ne necessitent plus d'action immediate.",
    )
    pdf.ln(3)
    for i, (level, path, problem, action) in enumerate(RESOLVED_CRITICAL, 1):
        write_item(pdf, i, "RESOLU", path, problem, action)

    # Dettes restantes
    item_no = 1
    sec_num = 2
    for section in SECTIONS:
        pdf.add_page()
        pdf.header_bar(f"{sec_num}. {section['title']}")
        sec_num += 1
        for level, path, problem, action in section["items"]:
            write_item(pdf, item_no, level, path, problem, action)
            item_no += 1

    # Roadmap
    pdf.add_page()
    pdf.header_bar(f"{sec_num}. Plan de remediation")
    for title, bullets in PRIORITY:
        pdf.set_font("Arial", "B", 10)
        pdf.set_text_color(*C_PRIMARY)
        pdf.cell(0, 7, title, new_x=XPos.LMARGIN, new_y=YPos.NEXT)
        pdf.set_font("Arial", "", 9)
        pdf.set_text_color(*C_TEXT)
        for b in bullets:
            pdf.cell(6, 5, "-")
            pdf.multi_cell(180, 5, b)
        pdf.ln(2)

    pdf.output(str(OUT))
    print(f"PDF genere : {OUT}")


if __name__ == "__main__":
    main()
