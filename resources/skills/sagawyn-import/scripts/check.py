# Checks a folder of SagaWyn import files before handing them to the GM.
# Usage: python3 -I check.py <folder> [<documents folder>] [--update]
# --update: files that update an existing campaign; a link to an entry missing from the files
# is then only a note (it may already exist in the campaign).
# Reads every .csv in the folder (and subfolders), recognises each file by its headers, then
# checks: separator, headers, empty or duplicate names, SagaWyn's maximum lengths, [[Name]]
# links and scene Fiches/Documents/Règles columns that lead nowhere, undefined field columns,
# non-numeric values in "nombre" fields.
# The CSV headers and closed values are SagaWyn's (in French), whatever the content language.
import csv, io, os, re, sys, unicodedata

LIMITS = {
    'fiches': {'Nom': 255, 'Résumé': 500, 'Description': 20000, 'Notes MJ': 20000},
    'regles': {'Titre': 255, 'Catégorie': 100, 'Résumé': 2000, 'Procédure': 20000, 'Notes MJ': 20000, 'Source': 255, 'Tags': 1000},
    'scenes': {'Scénario': 255, 'Résumé du scénario': 5000, 'Chapitre': 100, 'Scène': 255, 'Description': 20000},
}
DOC_EXT = {'.pdf', '.jpg', '.jpeg', '.png', '.gif', '.webp'}
LINK = re.compile(r'\[\[([^\[\]|\n]+?)(?:\|([^\[\]\n]+?))?\]\]')
problems, notes = [], []
UPDATE = False


def key(text):
    text = unicodedata.normalize('NFKD', text or '').encode('ascii', 'ignore').decode().lower()
    return re.sub(r'[^a-z0-9]', '', text)


def kind(headers):
    h = {key(x) for x in headers}
    if {'scenario', 'scene'} <= h:
        return 'scenes'
    if 'titre' in h and ({'procedure', 'origine', 'categorie'} & h):
        return 'regles'
    if 'nom' in h and 'groupe' in h and 'zone' in h:
        return 'champs'
    if 'nom' in h:
        return 'fiches'
    return None


def read(path):
    raw = open(path, 'rb').read()
    if not raw.startswith(b'\xef\xbb\xbf'):
        notes.append(f'{path}: no UTF-8 BOM (Excel may show accented letters wrongly; SagaWyn still reads it)')
    text = raw.decode('utf-8-sig')
    first = text.split('\n', 1)[0]
    if first.count(';') < max(first.count(','), first.count('\t')):
        problems.append(f'{path}: the separator must be the semicolon')
    rows = list(csv.reader(io.StringIO(text), delimiter=';'))
    rows = [r for r in rows if any(c.strip() for c in r)]
    if len(rows) - 1 > 2000:
        problems.append(f'{path}: more than 2000 lines, split it')
    if os.path.getsize(path) > 5 * 1024 * 1024:
        problems.append(f'{path}: more than 5 MB, split it')
    return rows[0], [dict(zip(rows[0], r + [''] * (len(rows[0]) - len(r)))) | {'_line': i + 2} for i, r in enumerate(rows[1:])]


def main(folder, docs_folder=None):
    files = sorted(os.path.join(d, f) for d, _, fs in os.walk(folder) for f in fs if f.lower().endswith('.csv'))
    data = {'fiches': [], 'regles': [], 'scenes': [], 'champs': []}
    for path in files:
        headers, rows = read(path)
        k = kind(headers)
        if k is None:
            problems.append(f'{path}: unknown kind of file (headers {headers})')
            continue
        if len(set(headers)) != len(headers):
            problems.append(f'{path}: duplicate headers')
        data[k].append((path, headers, rows))

    entities, docs, rules, fields = {}, set(), set(), set()
    for path, headers, rows in data['champs']:
        for r in rows:
            fields.add(key(r.get('Nom', '')))
            if r.get('Type', '').strip().lower() not in ('texte', 'texte long', 'nombre', 'oui/non', 'date', 'liste', 'compteur', 'lien', 'fichier', 'référence', 'reference'):
                problems.append(f"{path} l.{r['_line']}: unknown field type '{r.get('Type')}'")
            if r.get('Zone', '').strip().lower() not in ('publique', 'mj'):
                problems.append(f"{path} l.{r['_line']}: zone '{r.get('Zone')}' (publique or MJ)")
    # Type of each field, by (name, entry type): STR may be a number for a Personnage
    # and a text ("3D6×5") for a Créature.
    ftype = {(key(r['Nom']), key(r.get('Type de fiche', ''))): r.get('Type', '').strip().lower()
             for _, _, rows in data['champs'] for r in rows}

    def is_number(field, etype):
        t = ftype.get((key(field), key(etype)), ftype.get((key(field), '')))
        return t == 'nombre'

    base = {'nom', 'type', 'resume', 'description', 'notesmj'}
    for path, headers, rows in data['fiches']:
        extra = [h for h in headers if key(h) not in base]
        missing = [h for h in extra if key(h) not in fields]
        if missing:
            notes.append(f"{path}: columns with no fields file ({', '.join(missing)}): define them first, otherwise SagaWyn creates them as 'new field'")
        for r in rows:
            name = r.get('Nom', '').strip()
            if not name:
                problems.append(f"{path} l.{r['_line']}: missing name")
                continue
            k = (r.get('Type', '').strip().lower(), name.lower())
            if k in entities and entities[k] == path:
                problems.append(f'{path} l.{r["_line"]}: duplicate "{name}"')
            entities[k] = path
            for col, mx in LIMITS['fiches'].items():
                if len(r.get(col, '')) > mx:
                    problems.append(f"{path} l.{r['_line']}: {col} of '{name}' exceeds {mx} characters ({len(r[col])})")
            for h in extra:
                v = r.get(h, '').strip()
                if v and is_number(h, r.get('Type', '')) and not re.fullmatch(r'-?\d+([.,]\d+)?', v):
                    problems.append(f"{path} l.{r['_line']}: {h} of '{name}' must be a number ('{v}')")
    names = {n for _, n in entities}

    for path, headers, rows in data['regles']:
        seen = set()
        for r in rows:
            t = r.get('Titre', '').strip()
            if not t:
                problems.append(f"{path} l.{r['_line']}: missing title")
            elif t.lower() in seen:
                problems.append(f'{path} l.{r["_line"]}: duplicate "{t}"')
            seen.add(t.lower())
            rules.add(t.lower())
            for col, mx in LIMITS['regles'].items():
                if len(r.get(col, '')) > mx:
                    problems.append(f"{path} l.{r['_line']}: {col} of '{t}' exceeds {mx} characters")

    if docs_folder:
        for d, _, fs in os.walk(docs_folder):
            for f in fs:
                stem, ext = os.path.splitext(f)
                if ext.lower() in DOC_EXT:
                    docs.add(stem.lower())
                    if os.path.getsize(os.path.join(d, f)) > 50 * 1024 * 1024:
                        problems.append(f'{os.path.join(d, f)}: more than 50 MB, refused by SagaWyn')
                elif not f.lower().endswith(('.csv', '.md', '.zip', '.txt')):
                    notes.append(f'{os.path.join(d, f)}: format not accepted for documents (pdf, jpg, png, gif, webp)')

    def check_links(path, line, label, text):
        for m in LINK.finditer(text or ''):
            if m.group(2) is not None and not m.group(2).isdigit():
                problems.append(f'{path} l.{line}: link [[{m.group(1)}|{m.group(2)}]]: in a CSV, write [[Exact name]] only')
            elif m.group(1).strip().lower() not in names:
                (notes if UPDATE else problems).append(f'{path} l.{line}: {label}: link to an unknown entry [[{m.group(1)}]]')

    for path, headers, rows in data['fiches']:
        for r in rows:
            for col in ('Résumé', 'Description', 'Notes MJ'):
                check_links(path, r['_line'], r.get('Nom', ''), r.get(col))

    count = 0
    for path, headers, rows in data['scenes']:
        seen = set()
        for r in rows:
            count += 1
            sc, name = r.get('Scénario', '').strip(), r.get('Scène', '').strip()
            if not sc or not name:
                problems.append(f"{path} l.{r['_line']}: missing scenario or scene")
            if (sc.lower(), name.lower()) in seen:
                problems.append(f'{path} l.{r["_line"]}: duplicate scene "{name}" in "{sc}"')
            seen.add((sc.lower(), name.lower()))
            for col, mx in LIMITS['scenes'].items():
                if len(r.get(col, '')) > mx:
                    problems.append(f"{path} l.{r['_line']}: {col} of '{name}' exceeds {mx} characters ({len(r[col])})")
            st = key(r.get('Statut', ''))
            if st and st not in ('prevue', 'disponible', 'encours', 'jouee', 'ignoree', 'planned', 'available', 'inprogress', 'played', 'skipped'):
                problems.append(f"{path} l.{r['_line']}: unknown status '{r['Statut']}'")
            check_links(path, r['_line'], name, r.get('Description'))
            for col, pool, label in (('Fiches', names, 'entry'), ('Documents', docs if docs_folder else None, 'document'), ('Règles', rules, 'rule')):
                for v in [x.strip() for x in re.split(r'[|\n]', r.get(col, '')) if x.strip()]:
                    if pool is not None and v.lower() not in pool:
                        (notes if UPDATE else problems).append(f"{path} l.{r['_line']}: {label} '{v}' not found (scene '{name}')")

    print(f"{sum(len(r) for _, _, r in data['champs'])} fields, {len(entities)} entries, {len(rules)} rules, "
          f"{len(docs) if docs_folder else '?'} documents, {count} scenes")
    for n in dict.fromkeys(notes):
        print('  note:', n)
    for p in dict.fromkeys(problems):
        print('  PROBLEM:', p)
    return 1 if problems else 0


if __name__ == '__main__':
    args = [a for a in sys.argv[1:] if a not in ('--update', '--mise-a-jour')]
    UPDATE = len(args) != len(sys.argv) - 1
    if not args:
        sys.exit('usage: check.py <folder> [<documents folder>] [--update]')
    sys.exit(main(args[0], args[1] if len(args) > 1 else None))
