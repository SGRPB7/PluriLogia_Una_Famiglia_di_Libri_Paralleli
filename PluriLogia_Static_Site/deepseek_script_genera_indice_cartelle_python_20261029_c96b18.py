import os

base_url = "https://sgrpb7.github.io/PluriLogia_Una_Famiglia_di_Libri_Paralleli/PluriLogia_Static_Site/"
cartella_locale = "/My/My/B/Books/GitHub_Repo_Clone/PluriLogia_Una_Famiglia_di_Libri_Paralleli/PluriLogia_Static_Site"  # <-- METTI QUI IL PERCORSO

link_html = ""
for root, dirs, files in os.walk(cartella_locale):
    for f in files:
        percorso_completo = os.path.join(root, f)
        relativo = os.path.relpath(percorso_completo, cartella_locale).replace("\\", "/")
        url = base_url + relativo
        link_html += f'<li><a href="{url}">{relativo}</a></li>\n'

html = f"""<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Elenco file</title></head>
<body><h1>Elenco file in PluriLogia_Static_Site</h1><ul>
{link_html}
</ul></body></html>"""

with open(os.path.join(cartella_locale, "index.html"), "w", encoding="utf-8") as out:
    out.write(html)

print(f"Generato index.html con {link_html.count('<li>')} link.")