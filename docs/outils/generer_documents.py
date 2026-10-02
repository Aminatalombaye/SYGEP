# -*- coding: utf-8 -*-
"""Produit les versions PDF et Word (mises en forme) du guide et du cahier de recette.
Usage : python3 docs/outils/generer_documents.py   (nécessite : markdown, beautifulsoup4, python-docx, pypdf, playwright + Chromium)"""
import os, re, sys, json, html, datetime, unicodedata
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import markdown
from bs4 import BeautifulSoup
from pypdf import PdfReader
import docgen
import cas_de_test as T

ICI = os.path.dirname(os.path.abspath(__file__)); DOCS = os.path.dirname(ICI)
OUT = os.path.join(DOCS, 'livrables'); os.makedirs(OUT, exist_ok=True)
TMP = os.path.join(DOCS, 'outils', '_tmp'); os.makedirs(TMP, exist_ok=True)
DATE = datetime.date.today().strftime('%d/%m/%Y')
PRE = json.load(open(os.path.join(ICI, 'pre_recette.json'), encoding='utf-8'))

CSS = r"""
@page { size: %(size)s; margin: 20mm 17mm 20mm 17mm;
  @bottom-left { content: "%(foot)s"; font: 8pt 'Liberation Sans', sans-serif; color:#6b7480; }
  @bottom-right { content: counter(page); font: 8pt 'Liberation Sans', sans-serif; color:#6b7480; } }
@page cover { margin:0; @bottom-left{content:none} @bottom-right{content:none} }
* { box-sizing: border-box; }
body { font-family: 'Liberation Sans', Arial, sans-serif; font-size: 10pt; line-height: 1.5; color:#1d2733; margin:0; }
.cover { page: cover; width: 100%%; height: %(ch)s; position: relative; color:#fff; overflow:hidden;
  background: linear-gradient(155deg,#16304f 0%%,#1f3a5f 55%%,#0e7c86 130%%); break-after: page; }
.cover .band { position:absolute; left:0; top:0; bottom:0; width:14mm; background:#0e7c86; }
.cover .inner { position:absolute; left:34mm; right:24mm; top:78mm; }
.cover .kicker { letter-spacing:3px; font-size:10pt; text-transform:uppercase; color:#9fd8de; margin-bottom:10mm; }
.cover h1 { font-size:46pt; line-height:1.05; margin:0 0 6mm; font-weight:700; border:none; }
.cover .sub { font-size:19pt; color:#d8ecef; margin:0 0 8mm; padding-top:7mm; border-top:3px solid #3fc1cc; width:140mm; }
.cover .org { font-size:11.5pt; color:#c3d3e6; }
.cover .meta { position:absolute; left:34mm; bottom:26mm; font-size:10pt; color:#c3d3e6; line-height:1.9; }
.cover .meta b { color:#fff; display:inline-block; width:34mm; }
.toc { break-after: page; }
.toc h2 { font-size:22pt; color:#1f3a5f; margin:0 0 6mm; border:none; padding:0; break-before:auto; }
.toc ul { list-style:none; margin:0; padding:0; }
.toc li { margin:0; }
.toc a { display:flex; align-items:baseline; gap:6px; text-decoration:none; color:#1d2733; }
.toc a .t { flex:0 1 auto; }
.toc a .d { flex:1 1 auto; border-bottom:1px dotted #9aa5b1; min-width:10px; transform: translateY(-3px); }
.toc a .n { flex:0 0 auto; color:#1f3a5f; font-variant-numeric: tabular-nums; }
.toc li.l1 { margin-top:3.2mm; font-weight:700; font-size:10.5pt; }
.toc li.l2 { margin-left:7mm; font-size:9.2pt; line-height:1.55; color:#38424e; }
h2 { font-size:20pt; color:#1f3a5f; margin:0 0 5mm; padding:0 0 3mm; border-bottom:3px solid #0e7c86; break-before: page; break-after: avoid; }
h3 { font-size:13.5pt; color:#1f3a5f; margin:8mm 0 2.5mm; break-after: avoid; padding-left:3mm; border-left:4px solid #0e7c86; }
h4 { font-size:11.2pt; color:#0e7c86; margin:6mm 0 2mm; break-after: avoid; }
h5 { font-size:10.2pt; color:#1f3a5f; margin:4mm 0 1.5mm; break-after: avoid; }
p { margin:0 0 2.6mm; orphans:3; widows:3; }
a { color:#1a5fb4; text-decoration:none; }
code { font-family:'Liberation Mono', monospace; font-size:8.8pt; background:#f1f3f5; color:#a31b5c; padding:0.5px 3px; border-radius:3px; }
pre { background:#f6f8fa; border:1px solid #dfe4ea; border-left:4px solid #0e7c86; border-radius:4px; padding:3mm 4mm; font-size:8pt; line-height:1.4; white-space:pre-wrap; word-break:break-word; break-inside:avoid; margin:2mm 0 4mm; }
pre code { background:none; color:#1d2733; padding:0; font-size:8pt; }
blockquote { margin:3mm 0 4mm; padding:2.5mm 4mm; background:#eef6f7; border-left:4px solid #0e7c86; border-radius:0 4px 4px 0; break-inside:avoid; }
blockquote p:last-child { margin-bottom:0; }
table { border-collapse:collapse; width:100%%; margin:2mm 0 5mm; font-size:8.8pt; line-height:1.4; }
th { background:#1f3a5f; color:#fff; text-align:left; padding:2mm 2.5mm; font-weight:700; border:1px solid #1f3a5f; }
td { padding:1.8mm 2.5mm; border:1px solid #cfd6de; vertical-align:top; }
tr { break-inside:avoid; }
tbody tr:nth-child(even) td { background:#f4f7fa; }
ul, ol { margin:0 0 3mm; padding-left:6mm; } li { margin-bottom:1mm; }
figure { margin:4mm 0 6mm; text-align:center; break-inside:avoid; }
figure img { max-width:100%%; max-height:150mm; border:1px solid #cfd6de; border-radius:4px; box-shadow:0 2px 8px rgba(0,0,0,.12); }
figcaption { font-size:8.5pt; color:#6b7480; font-style:italic; margin-top:2mm; }
hr { display:none; }
.ok { color:#1b7f3b; font-weight:700; } .ko { color:#c01f2f; font-weight:700; } .man { color:#8a6d00; font-weight:700; }
.prio-c { color:#c01f2f; font-weight:700; } .prio-m { color:#b45f06; font-weight:700; } .prio-l { color:#555f6b; }
.mod h2 { font-size:17pt; } .mod .obj { color:#555f6b; font-style:italic; margin-bottom:4mm; }
td.id { font-weight:700; color:#1f3a5f; white-space:nowrap; }
.sign td { height:16mm; }
.kpi { display:flex; gap:4mm; margin:3mm 0 5mm; } .kpi div { flex:1; border:1px solid #cfd6de; border-top:4px solid #0e7c86; border-radius:4px; padding:3mm; text-align:center; }
.kpi b { display:block; font-size:19pt; color:#1f3a5f; } .kpi span { font-size:8.5pt; color:#555f6b; }
"""


def slug(t):
    t = unicodedata.normalize('NFC', t.lower().strip())
    t = re.sub(r'[^\w\s-]', '', t)
    return re.sub(r'\s', '-', t)


def cover_html(titre, sous, orga, meta):
    m = ''.join(f'<div><b>{html.escape(k)}</b>{html.escape(v)}</div>' for k, v in meta)
    return (f'<section class="cover"><div class="band"></div><div class="inner"><div class="kicker">Documentation officielle</div>'
            f'<h1>{titre}</h1><div class="sub">{sous}</div><div class="org">{orga}</div></div><div class="meta">{m}</div></section>')


def make_toc(soup, numbers=None, levels=('h2', 'h3')):
    items = []
    for h in soup.find_all(list(levels)):
        items.append((1 if h.name == 'h2' else 2, h.get_text(' ', strip=True), h['id']))
    out = ['<section class="toc"><h2>Sommaire</h2><ul>']
    for lvl, t, i in items:
        n = (numbers or {}).get(i, '')
        out.append(f'<li class="l{lvl}"><a href="#{i}"><span class="t">{html.escape(t)}</span><span class="d"></span><span class="n">{n}</span></a></li>')
    out.append('</ul></section>')
    return ''.join(out), items


def page_html(body, css_size, foot, cover, toc):
    return (f'<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>{foot}</title><style>{CSS % {"size": css_size, "foot": foot, "ch": "210mm" if "landscape" in css_size else "297mm"}}</style></head>'
            f'<body>{cover}{toc}{body}</body></html>')


def find_pages(pdf_path, items, start_after):
    rd = PdfReader(pdf_path); texts = [re.sub(r'\s+', ' ', (p.extract_text() or '')) for p in rd.pages]
    res, cur = {}, start_after
    for lvl, t, i in items:
        key = re.sub(r'\s+', ' ', t)
        for pg in range(cur, len(texts)):
            if key in texts[pg]:
                res[i] = pg + 1; cur = pg; break
    return res, len(texts)


def build_pdf(name, body_soup_html, cover, size, foot, levels=('h2', 'h3'), landscape=False):
    soup = BeautifulSoup(body_soup_html, 'html.parser')
    for n, hd in enumerate(soup.find_all(['h2', 'h3', 'h4'])):
        if not hd.get('id'): hd['id'] = 'h%d-%s' % (n, slug(hd.get_text(' ', strip=True)))
    body_soup_html = str(soup)
    toc, items = make_toc(soup, None, levels)
    hp = os.path.join(TMP, name + '.html'); pdf = os.path.join(OUT, name + '.pdf')
    open(hp, 'w', encoding='utf-8').write(page_html(body_soup_html, size, foot, cover, toc))
    docgen.html_to_pdf(hp, pdf)
    for _ in range(2):  # 2 passes : recalcule les numéros de page puis revérifie
        nums, total = find_pages(pdf, items, 2)
        toc, _i = make_toc(soup, nums, levels)
        open(hp, 'w', encoding='utf-8').write(page_html(body_soup_html, size, foot, cover, toc))
        docgen.html_to_pdf(hp, pdf)
    return pdf, items


# ------------------------------------------------------------------ GUIDE
def guide():
    md = open(os.path.join(DOCS, 'GUIDE-COMPLET.md'), encoding='utf-8').read()
    md = md.split('\n', 1)[1]  # titre H1
    md = re.sub(r'\n## Sommaire.*?\n---\n', '\n', md, flags=re.S)
    md = re.sub(r'^> Un deuxième document.*\n', '', md, flags=re.M)
    md = md.replace('`CAHIER-DE-RECETTE.md`', 'le cahier de recette')
    md = re.sub(r'\[([^\]]+)\]\(CAHIER-DE-RECETTE\.md\)', r'\1', md)
    h = markdown.markdown(md, extensions=['tables', 'fenced_code', 'sane_lists'])
    s = BeautifulSoup(h, 'html.parser')
    for hd in s.find_all(['h2', 'h3', 'h4']): hd['id'] = slug(hd.get_text(' ', strip=True))
    for a in s.find_all('a'):
        href = a.get('href', '')
        if href.startswith('#') or href.startswith('http'): continue
        a.replace_with(a.get_text())  # liens relatifs vers d'autres fichiers
    for p in s.find_all('p'):
        imgs = p.find_all('img', recursive=False)
        if len(imgs) == 1 and p.get_text(strip=True) == '':
            fig = s.new_tag('figure'); im = imgs[0]; cap = s.new_tag('figcaption'); cap.string = im.get('alt', '')
            im2 = s.new_tag('img', src=im['src']); fig.append(im2); fig.append(cap); p.replace_with(fig)
    body = str(s)
    meta = [('Version', 'Édition du ' + DATE), ('Application', 'SYGEP – Laravel 13 / PHP 8.3'), ('Public', 'Responsables fonctionnels, administrateurs, hébergeur')]
    cov = cover_html('Guide complet', 'Installation, utilisation, règles de gestion et exploitation', 'SYGEP — Système de Gestion du Patrimoine Matériel<br>Ministère de l\'Emploi et de la Formation Professionnelle et Technique (MEFPT)', meta)
    base = os.path.join(DOCS)
    soup_for_pdf = body.replace('src="img/', 'src="../../img/')
    pdf, items = build_pdf('SYGEP-Guide-complet', soup_for_pdf, cov, 'A4', 'SYGEP — Guide complet')
    # Word
    dx = docgen.Docx('SYGEP — Guide complet')
    dx.cover('Guide complet', 'Installation, utilisation, règles de gestion et exploitation',
             'SYGEP — Système de Gestion du Patrimoine Matériel\nMinistère de l\'Emploi et de la Formation Professionnelle et Technique (MEFPT)', meta)
    dx.toc([(l, t) for l, t, i in items if True])
    dx.blocks(BeautifulSoup(body, 'html.parser'), img_root=DOCS)
    dx.save(os.path.join(OUT, 'SYGEP-Guide-complet.docx'))
    return pdf


# ------------------------------------------------------------------ CAHIER
def cahier():
    esc = html.escape
    PRIO = {'C': ('Critique', 'prio-c'), 'M': ('Majeur', 'prio-m'), 'm': ('Mineur', 'prio-l')}
    tot = sum(len(m[3]) for m in T.MODULES)
    ok = sum(1 for v in PRE.values() if v[0] == 'OK'); ko = sum(1 for v in PRE.values() if v[0] == 'KO')
    h = []
    h.append('<h2>Mode d\'emploi</h2><ol>'
             '<li>Installer l\'application avec les données de démonstration (<code>php artisan migrate --seed</code> puis <code>php artisan db:seed --class=DemoDataSeeder</code>).</li>'
             '<li>Pour chaque cas : se connecter avec le <strong>compte</strong> indiqué, vérifier les préconditions, suivre les <strong>étapes</strong>, comparer au <strong>résultat attendu</strong>.</li>'
             '<li>Cocher le résultat (OK / KO / Bloqué / N/A), puis noter la date, le testeur et les observations. Le classeur Excel fourni (<code>CAHIER-DE-RECETTE.xlsx</code>) calcule la synthèse automatiquement.</li>'
             '<li>Priorités : <span class="prio-c">Critique</span> = bloquant pour la mise en production ; <span class="prio-m">Majeur</span> ; <span class="prio-l">Mineur</span>.</li>'
             '<li><strong>Critère d\'acceptation</strong> : 100 % des cas critiques OK et aucune anomalie bloquante ouverte.</li>'
             '<li>La colonne « Pré-recette » donne le résultat de l\'exécution automatique ; elle ne remplace pas votre test.</li></ol>')
    h.append('<h3>Comptes de test</h3><p>Mot de passe de démonstration : <code>password</code></p><table><thead><tr><th data-w="1">Code</th><th data-w="3">E-mail</th><th data-w="4">Rôle</th></tr></thead><tbody>')
    for k, (e, r) in T.COMPTES.items(): h.append(f'<tr><td><strong>{k}</strong></td><td>{e}</td><td>{esc(r)}</td></tr>')
    h.append('</tbody></table>')
    h.append(f'<h2>Synthèse de la pré-recette</h2><div class="kpi"><div><b>{tot}</b><span>cas au cahier</span></div><div><b>{ok+ko}</b><span>vérifiés automatiquement</span></div>'
             f'<div><b class="ok">{ok}</b><span>OK</span></div><div><b class="ko">{ko}</b><span>KO (connu)</span></div><div><b>{tot-ok-ko}</b><span>à faire à la main</span></div></div>')
    h.append('<table><thead><tr><th data-w="6">Module</th><th data-w="1">Cas</th><th data-w="1">OK auto</th><th data-w="1">KO auto</th><th data-w="1">Manuel</th></tr></thead><tbody>')
    for code, titre, obj, cas in T.MODULES:
        o = sum(1 for c in cas if PRE.get(c[0], ['', ''])[0] == 'OK'); k = sum(1 for c in cas if PRE.get(c[0], ['', ''])[0] == 'KO')
        h.append(f'<tr><td>{code} – {esc(titre)}</td><td>{len(cas)}</td><td class="ok">{o}</td><td class="ko">{k or ""}</td><td>{len(cas)-o-k}</td></tr>')
    h.append('</tbody></table>')
    h.append('<h3>Anomalie connue</h3><ul>' + ''.join(f'<li><strong>{i}</strong> : {esc(v[1])}</li>' for i, v in PRE.items() if v[0] == 'KO') + '</ul>')
    h.append('<h3>Défauts trouvés et corrigés pendant la pré-recette</h3><ul>'
             '<li><strong>Sécurité</strong> : un utilisateur « local » pouvait ouvrir par URL directe une matière, une affectation ou un bon d\'un autre service — désormais 404 (SEC-12).</li>'
             '<li>Un utilisateur local ne pouvait pas transférer une matière à un agent de son service (AFF-24/25).</li>'
             '<li>Sortie de stock « par agent » : validation inatteignable, corrigée.</li>'
             '<li>Alerte de rupture non émise quand le stock tombe à zéro (STK-19).</li>'
             '<li>Débordement horizontal sur mobile ; seeders non idempotents à l\'installation neuve.</li></ul>')
    for code, titre, obj, cas in T.MODULES:
        h.append(f'<div class="mod"><h2>{code} – {esc(titre)}</h2><p class="obj">{esc(obj)}</p>'
                 '<table><thead><tr><th data-w="0.9">ID</th><th data-w="3.2">Test et préconditions</th><th data-w="1.6">Compte</th><th data-w="4.6">Étapes</th><th data-w="3.6">Résultat attendu</th>'
                 '<th data-w="1.1">Prio.</th><th data-w="1.5">Pré-recette</th><th data-w="2.0">Résultat</th></tr></thead><tbody>')
        for (i, test, cpt, pre, etapes, att, pr) in cas:
            r = PRE.get(i, ['—', '']); st = {'OK': ('OK', 'ok'), 'KO': ('KO', 'ko')}.get(r[0], ('Manuel', 'man'))
            pn, pc = PRIO[pr]
            steps = '<ol>' + ''.join(f'<li>{esc(e)}</li>' for e in etapes) + '</ol>'
            h.append(f'<tr><td class="id">{i}</td><td><strong>{esc(test)}</strong><br>{"" if pre in ("—","") else "Pré : " + esc(pre)}</td>'
                     f'<td>{esc(cpt)}</td><td>{steps}</td><td>{esc(att)}</td><td><span class="{pc}">{pn}</span></td>'
                     f'<td><span class="{st[1]}">{st[0]}</span></td><td style="white-space:nowrap">☐ OK<br>☐ KO<br>☐ Bloqué<br>☐ N/A</td></tr>')
        h.append('</tbody></table></div>')
    h.append('<h2>Procès-verbal de recette</h2><table class="sign"><tbody>'
             + ''.join(f'<tr><td data-w="1"><strong>{a}</strong></td><td data-w="2">{b}</td></tr>' for a, b in [
                 ('Application', 'SYGEP'), ('Version / commit', ''), ('Date de recette', ''), ('Testeurs', ''), ('Cas exécutés / OK / KO', ''),
                 ('Anomalies bloquantes ouvertes', ''), ('Décision', '☐ Acceptée   ☐ Acceptée avec réserves   ☐ Refusée'), ('Réserves', '')])
             + '</tbody></table><table class="sign"><thead><tr><th>Maîtrise d\'ouvrage (nom, date, signature)</th><th>Maîtrise d\'œuvre (nom, date, signature)</th></tr></thead><tbody><tr><td></td><td></td></tr></tbody></table>')
    body = ''.join(h)
    meta = [('Version', 'Édition du ' + DATE), ('Périmètre', f'{tot} cas de test, {len(T.MODULES)} modules'), ('Comptes', 'Jeu de démonstration (DemoDataSeeder)')]
    cov = cover_html('Cahier de recette', 'Plan de validation fonctionnelle et procès-verbal', 'SYGEP — Système de Gestion du Patrimoine Matériel<br>Ministère de l\'Emploi et de la Formation Professionnelle et Technique (MEFPT)', meta)
    pdf, items = build_pdf('SYGEP-Cahier-de-recette', body, cov, 'A4 landscape', 'SYGEP — Cahier de recette', levels=('h2',), landscape=True)
    dx = docgen.Docx('SYGEP — Cahier de recette', landscape=True)
    dx.cover('Cahier de recette', 'Plan de validation fonctionnelle et procès-verbal',
             'SYGEP — Système de Gestion du Patrimoine Matériel\nMinistère de l\'Emploi et de la Formation Professionnelle et Technique (MEFPT)', meta)
    dx.toc([(l, t) for l, t, i in items])
    dx.blocks(BeautifulSoup(body, 'html.parser'))
    dx.save(os.path.join(OUT, 'SYGEP-Cahier-de-recette.docx'))
    return pdf


if __name__ == '__main__':
    which = sys.argv[1:] or ['guide', 'cahier']
    if 'guide' in which: print(guide())
    if 'cahier' in which: print(cahier())
