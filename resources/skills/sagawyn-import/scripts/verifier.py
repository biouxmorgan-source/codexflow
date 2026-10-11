# Vérifie un dossier de fichiers d'import SagaWyn avant de les rendre au MJ.
# Usage : python3 -I verifier.py <dossier> [<dossier de documents>] [--mise-a-jour]
# --mise-a-jour : fichiers qui mettent à jour une campagne existante ; un lien vers une fiche
# absente des fichiers est alors signalé en note (elle existe peut-être déjà dans la campagne).
# Lit tous les .csv du dossier (et sous-dossiers), reconnaît leur nature par les en-têtes,
# puis contrôle : séparateur, en-têtes, noms vides ou en double, longueurs maximales de SagaWyn,
# liens [[Nom]] et colonnes Fiches/Documents/Règles des scènes qui ne mènent nulle part,
# colonnes de champs non définies, valeurs « nombre » non numériques.
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
        notes.append(f'{path} : pas de BOM UTF-8 (Excel risque de mal afficher les accents ; SagaWyn le lit quand même)')
    text = raw.decode('utf-8-sig')
    first = text.split('\n', 1)[0]
    if first.count(';') < max(first.count(','), first.count('\t')):
        problems.append(f'{path} : le séparateur doit être le point-virgule')
    rows = list(csv.reader(io.StringIO(text), delimiter=';'))
    rows = [r for r in rows if any(c.strip() for c in r)]
    if len(rows) - 1 > 2000:
        problems.append(f'{path} : plus de 2000 lignes, à découper')
    if os.path.getsize(path) > 5 * 1024 * 1024:
        problems.append(f'{path} : plus de 5 Mo, à découper')
    return rows[0], [dict(zip(rows[0], r + [''] * (len(rows[0]) - len(r)))) | {'_line': i + 2} for i, r in enumerate(rows[1:])]


def main(folder, docs_folder=None):
    files = sorted(os.path.join(d, f) for d, _, fs in os.walk(folder) for f in fs if f.lower().endswith('.csv'))
    data = {'fiches': [], 'regles': [], 'scenes': [], 'champs': []}
    for path in files:
        headers, rows = read(path)
        k = kind(headers)
        if k is None:
            problems.append(f'{path} : nature inconnue (en-têtes {headers})')
            continue
        if len(set(headers)) != len(headers):
            problems.append(f'{path} : en-têtes en double')
        data[k].append((path, headers, rows))

    entities, docs, rules, fields = {}, set(), set(), set()
    for path, headers, rows in data['champs']:
        for r in rows:
            fields.add(key(r.get('Nom', '')))
            if r.get('Type', '').strip().lower() not in ('texte', 'texte long', 'nombre', 'oui/non', 'date', 'liste', 'compteur', 'lien', 'fichier', 'référence', 'reference'):
                problems.append(f"{path} l.{r['_line']} : type de champ « {r.get('Type')} » inconnu")
            if r.get('Zone', '').strip().lower() not in ('publique', 'mj'):
                problems.append(f"{path} l.{r['_line']} : zone « {r.get('Zone')} » (publique ou MJ)")
    # Type de chaque champ, par (nom, type de fiche) : FOR peut être un nombre pour un Personnage
    # et un texte (« 3D6×5 ») pour une Créature.
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
            notes.append(f"{path} : colonnes sans fichier de champs ({', '.join(missing)}) : à définir avant, sinon SagaWyn les crée en « nouveau champ »")
        for r in rows:
            name = r.get('Nom', '').strip()
            if not name:
                problems.append(f"{path} l.{r['_line']} : nom manquant")
                continue
            k = (r.get('Type', '').strip().lower(), name.lower())
            if k in entities and entities[k] == path:
                problems.append(f'{path} l.{r["_line"]} : « {name} » en double')
            entities[k] = path
            for col, mx in LIMITS['fiches'].items():
                if len(r.get(col, '')) > mx:
                    problems.append(f"{path} l.{r['_line']} : {col} de « {name} » dépasse {mx} caractères ({len(r[col])})")
            for h in extra:
                v = r.get(h, '').strip()
                if v and is_number(h, r.get('Type', '')) and not re.fullmatch(r'-?\d+([.,]\d+)?', v):
                    problems.append(f"{path} l.{r['_line']} : {h} de « {name} » doit être un nombre (« {v} »)")
    names = {n for _, n in entities}

    for path, headers, rows in data['regles']:
        seen = set()
        for r in rows:
            t = r.get('Titre', '').strip()
            if not t:
                problems.append(f"{path} l.{r['_line']} : titre manquant")
            elif t.lower() in seen:
                problems.append(f'{path} l.{r["_line"]} : « {t} » en double')
            seen.add(t.lower())
            rules.add(t.lower())
            for col, mx in LIMITS['regles'].items():
                if len(r.get(col, '')) > mx:
                    problems.append(f"{path} l.{r['_line']} : {col} de « {t} » dépasse {mx} caractères")

    if docs_folder:
        for d, _, fs in os.walk(docs_folder):
            for f in fs:
                stem, ext = os.path.splitext(f)
                if ext.lower() in DOC_EXT:
                    docs.add(stem.lower())
                    if os.path.getsize(os.path.join(d, f)) > 50 * 1024 * 1024:
                        problems.append(f'{os.path.join(d, f)} : plus de 50 Mo, refusé par SagaWyn')
                elif not f.lower().endswith(('.csv', '.md', '.zip', '.txt')):
                    notes.append(f'{os.path.join(d, f)} : format non accepté par les documents (pdf, jpg, png, gif, webp)')

    def check_links(path, line, label, text):
        for m in LINK.finditer(text or ''):
            if m.group(2) is not None and not m.group(2).isdigit():
                problems.append(f'{path} l.{line} : lien [[{m.group(1)}|{m.group(2)}]] : dans un CSV, écrire [[Nom exact]] seulement')
            elif m.group(1).strip().lower() not in names:
                (notes if UPDATE else problems).append(f'{path} l.{line} : {label} lien vers une fiche inconnue [[{m.group(1)}]]')

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
                problems.append(f"{path} l.{r['_line']} : scénario ou scène manquant")
            if (sc.lower(), name.lower()) in seen:
                problems.append(f'{path} l.{r["_line"]} : scène « {name} » en double dans « {sc} »')
            seen.add((sc.lower(), name.lower()))
            for col, mx in LIMITS['scenes'].items():
                if len(r.get(col, '')) > mx:
                    problems.append(f"{path} l.{r['_line']} : {col} de « {name} » dépasse {mx} caractères ({len(r[col])})")
            st = key(r.get('Statut', ''))
            if st and st not in ('prevue', 'disponible', 'encours', 'jouee', 'ignoree', 'planned', 'available', 'inprogress', 'played', 'skipped'):
                problems.append(f"{path} l.{r['_line']} : statut « {r['Statut']} » inconnu")
            check_links(path, r['_line'], name, r.get('Description'))
            for col, pool, label in (('Fiches', names, 'fiche'), ('Documents', docs if docs_folder else None, 'document'), ('Règles', rules, 'règle')):
                for v in [x.strip() for x in re.split(r'[|\n]', r.get(col, '')) if x.strip()]:
                    if pool is not None and v.lower() not in pool:
                        (notes if UPDATE else problems).append(f"{path} l.{r['_line']} : {label} « {v} » introuvable (scène « {name} »)")

    print(f"{sum(len(r) for _, _, r in data['champs'])} champs, {len(entities)} fiches, {len(rules)} règles, "
          f"{len(docs) if docs_folder else '?'} documents, {count} scènes")
    for n in dict.fromkeys(notes):
        print('  note :', n)
    for p in dict.fromkeys(problems):
        print('  PROBLÈME :', p)
    return 1 if problems else 0


if __name__ == '__main__':
    args = [a for a in sys.argv[1:] if a != '--mise-a-jour']
    UPDATE = len(args) != len(sys.argv) - 1
    if not args:
        sys.exit('usage : verifier.py <dossier> [<dossier de documents>] [--mise-a-jour]')
    sys.exit(main(args[0], args[1] if len(args) > 1 else None))
