# Préparer un import SagaWyn à partir d'un livre de jeu de rôle

Tu aides un meneur de jeu à remplir **SagaWyn**, son assistant de MJ. Je te joins un ou plusieurs documents (livre de règles, scénario, campagne, supplément, en PDF ou en images), et peut-être mes notes de partie. Ta mission : produire des **fichiers prêts à importer** dans SagaWyn et un **guide d'import pas à pas**.

Ce prompt est une **procédure à suivre à la lettre**, pour que le résultat soit le même quelle que soit l'IA qui l'exécute : mêmes fichiers, mêmes noms de fichiers, mêmes colonnes, mêmes rubriques dans le même ordre, mêmes règles de décision. Quand une consigne te paraît ambiguë, applique la règle de décision indiquée plutôt que ton propre choix. Ne saute aucune étape et ne change pas l'ordre des livraisons (voir « Déroulé de la conversation »).

Écris le contenu des fiches, les noms de champs et le guide en **{!! $language !!}**. Les **en-têtes de colonnes** et les valeurs fermées (types, zones, statuts) restent exactement ceux donnés ci-dessous (en français) : SagaWyn les reconnaît ainsi.

## La campagne qui recevra l'import

- Jeu : {!! $game !!}
- Monde : {!! $world !!}
- Types de fiche existants : {!! $types !!}
@if ($fields !== [])
- Champs déjà définis pour ce jeu (réutilise exactement ces noms plutôt que d'en créer de nouveaux) :
@foreach ($fields as $field)
  - {!! $field !!}
@endforeach
@else
- Aucun champ n'est encore défini pour ce jeu.
@endif

## Le but

Ce travail produit, à partir d'un livre (PDF) et éventuellement des notes de partie du MJ, des fichiers qui recréent la campagne dans SagaWyn : **fiches** (PNJ, lieux, créatures, organisations, objets, sorts), **champs** de jeu, **règles**, **documents** (cartes, indices, portraits) et **scénarios/scènes**.

Le but : que le MJ puisse **mener la partie depuis SagaWyn sans rouvrir le livre**. C'est le critère de qualité de chaque fiche et de chaque scène.

## Garde-fous

1. **Usage personnel.** Un livre du commerce est sous droits. Les fichiers produits servent au MJ qui a acheté le livre, pour sa table. Ils ne vont jamais dans un dépôt public, une campagne de démonstration, un modèle partagé ou un message public. Rappeler cette règle en tête du guide.
2. **Tout le contenu disponible, avec la page.** Mettre tout ce qu'on peut du livre, en particulier tout ce qui sert à jouer (déroulé, indices, tests, réactions, profils, issues) dans ses propres mots et en structure aide-mémoire. Pas de simple renvoi « voir livre p. 42 » à la place du contenu : la page se met **en fin** de texte, comme source. Les textes à lire aux joueurs (encadrés « à lire à voix haute ») sont résumés en indiquant ce qu'ils doivent transmettre.
3. **Rien d'inventé.** Ce que le livre ne dit pas reste vide. Une lecture incertaine (tableau de caractéristiques mélangé, chiffre illisible) est notée dans `A-VERIFIER.md` avec la page, et marquée « (à vérifier) » dans le texte.
4. **Zone publique / zone MJ.** Ce qu'un joueur peut apprendre en rencontrant l'élément va en **Description** ou en champ `publique`. Tout le reste (identité réelle, motivations cachées, solution, profil des adversaires, ce qui va arriver) va en **Notes MJ** ou en champ `MJ`. Dans une scène, tout est pour le MJ ; marquer **Secret MJ** ce qu'il ne faut surtout pas lire aux joueurs.
5. **Pas de colonne propre à un jeu inventée par SagaWyn** : le vocabulaire du jeu (Force, Points de vie, Points de destin…) passe par les **champs** que l'on définit soi-même.

## Méthode

### 1. Lire tout le livre

- Lire le document **en entier**, chapitre par chapitre, y compris les encadrés, tableaux et annexes. Ne jamais travailler à partir de la table des matières ou d'un résumé.
- Les pages qui sont des images (cartes, plans, lettres, encadrés scannés) se lisent visuellement ; ce qu'elles contiennent d'utile pour jouer est reporté dans les fiches et les scènes.
- Si tu peux exécuter du code : extraire le texte (par exemple `pdftotext -layout`), découper les images utiles (portraits, cartes, aides de jeu) et les nommer comme à l'étape 5. Sinon : lister ces images dans `A-VERIFIER.md` avec leur page et le nom de fichier à leur donner, pour que le MJ les découpe lui-même.
- Si le MJ fournit des comptes rendus de séance ou des fiches de personnages, les lire aussi : ils servent à « Ce qui s'est passé » et aux fiches des personnages joueurs.

### 2. Inventaire et table des noms

Avant d'écrire, dresser l'inventaire et le montrer au MJ s'il est disponible (sinon le mettre en tête du guide) :
- **types de fiche** nécessaires (les types de base sont Personnage, Lieu, Organisation, Objet, Créature ; à créer : Sort, Savoir, Véhicule…) ;
- **champs** par type (caractéristiques, compétences, profil de créature…) avec leur type et leur zone ;
- **fiches** : chaque PNJ nommé, lieu, organisation, objet important, créature, sort ;
- **règles** : règles spéciales du livre, glossaire ;
- **documents** : chaque carte, plan, indice, lettre, portrait, avec sa visibilité ;
- **scénarios** (un par chapitre ou par aventure, numérotés « 1. Titre ») et leurs **scènes** dans l'ordre du livre.

Fixer une **table des noms canoniques** : chaque élément a un nom unique, écrit à l'identique partout (fiches, liens `[[ ]]`, colonnes Fiches/Documents/Règles). Choisir le nom le plus courant dans le livre, sans titre de civilité variable.

**Règles de décision** (à appliquer telles quelles) :
- un **scénario** = un chapitre du livre (ou une aventure s'il en contient plusieurs), nommé « N. Titre du chapitre » ;
- une **scène** = chaque section numérotée ou titrée du chapitre qui décrit un lieu, une rencontre ou un événement jouable, dans l'ordre du livre ;
- un **PNJ a une fiche** s'il est nommé et qu'il a un profil chiffré **ou** qu'il apparaît dans au moins deux scènes ; sinon il est décrit dans la scène où il apparaît ;
- un **lieu a une fiche** s'il a sa propre section de description (ou un plan) ; une **créature** dès qu'elle a un profil ; un **objet** s'il a des effets ou s'il est un enjeu de l'intrigue ;
- un **champ** est créé pour chaque valeur qui figure dans le profil de la plupart des fiches d'un même type ; le reste va dans les Notes MJ.

### 3. Les fiches (complètes)

- **Résumé** (≤ 500 caractères) : qui c'est et pourquoi il compte, en une ou deux phrases.
- **Description** (publique) : apparence, comportement visible, rôle connu, réputation ; pour un lieu, ce qu'on voit en arrivant et ses parties.
- **Notes MJ**, structurées en Markdown avec des rubriques en gras, **toujours dans cet ordre** ; une rubrique sur laquelle le livre ne dit rien est omise :
  - PNJ : **Identité réelle / Ce qu'il cache**, **Ce qu'il veut**, **Ce qu'il sait** (les indices qu'il peut donner, à quelles conditions), **Comment il réagit** (alliance, menace, fuite, trahison), **Profil** si non couvert par les champs (armes, sorts, capacités spéciales, peur ou folie selon le jeu), **Apparaît dans** (scènes).
  - Lieu : zones ou salles une par une, **PNJ présents**, **Dangers**, **Indices** (où, quel test), **Secret**.
  - Créature : **Comportement**, **Tactique**, **Attaques** et dégâts, **Protection**, **Capacités**, **Effet de peur ou de folie** (selon le jeu), **Comment la vaincre**.
  - Objet / sort : effets, coût, durée, conditions, où il se trouve.
- **Champs** : valeurs exactes du livre ; un profil avec dés ou fourchettes va dans des champs de type `texte` (voir l'annexe A).
- **Liens** : chaque nom d'une autre fiche cité dans un texte s'écrit `[[Nom exact]]` (au moins à sa première occurrence).
- Un fichier de fiches par type de fiche (voir « Livrables »).

### 4. Les scènes : « Ce qui est prévu » (et « Ce qui s'est passé »)

Une ligne par scène, dans l'ordre du livre ; les scènes optionnelles portent « (optionnel) » dans leur nom. La **Description** commence par `### Ce qui est prévu` et donne les rubriques suivantes, en gras, **toujours dans cet ordre** ; une rubrique sur laquelle le livre ne dit rien est omise :
- **Enjeu** : ce que la scène doit produire dans l'histoire ;
- **Quand / Lieu / Situation** : déclencheur, décor, ambiance, ce que les personnages voient ;
- **PNJ** : qui est là, leur attitude, ce qu'ils disent ou veulent (liens `[[ ]]`) ;
- **Indices** : chaque indice, comment l'obtenir (compétence ou test, difficulté), ce qu'il apprend ;
- **Tests et dangers** : jets demandés, conséquences d'un échec, adversaires (renvoi à leur fiche, plus l'essentiel du profil s'il n'a pas de fiche) ;
- **Issues** : les branches possibles et leurs conséquences sur la suite ;
- **À remettre** : aides de jeu (titres exacts des documents) et moment où les donner ;
- **Récompenses** : expérience, objets, autres gains prévus par le jeu ;
- **Conseils** du livre au MJ (rythme, improvisation, comment ramener les joueurs) ;
- **Transitions** : vers quelles scènes ;
- en dernière ligne, la source : `Livre p. 97-101.`

Une scène dépasse 20 000 caractères ? La découper (« Le temple (1/2) », « Le temple (2/2) »).

**Ce qui s'est passé** (seulement si le MJ a fourni le récit de ses séances) : pour une scène jouée **qui s'est écartée du livre**, ajouter après « Ce qui est prévu » une section `### Ce qui s'est passé (séance N)` qui raconte ce qui a réellement eu lieu à la table : choix des joueurs, PNJ morts ou épargnés, indices manqués, objets obtenus. Une scène jouée comme prévu ne reçoit pas cette section : son **Statut** passe à `Jouée`, c'est tout. Une scène créée par le MJ hors du livre s'ajoute à sa place, avec son seul « Ce qui s'est passé ».

Colonnes **Fiches**, **Documents**, **Règles** : tout ce qui sert dans la scène, par nom exact, séparé par ` | `. Statut : `Prévue` (ou `Jouée`, `Ignorée` d'après les notes du MJ).

### 5. Documents nommés

Le titre d'un document dans SagaWyn est le **nom du fichier sans extension**. Nommer chaque image ou PDF par son titre définitif, avec un préfixe qui dit sa nature :
- `Carte MJ - Plan de l'auberge.jpg` (zone MJ), `Carte joueurs - Plan de l'auberge.jpg` (publique) ;
- `Indice - Lettre de la veuve Orsel.jpg`, `Aide de jeu - Télégramme.jpg` (zone MJ, remis en cours de partie) ;
- `Portrait - Aldric Fenouil.jpg` (à poser sur la fiche à la main, ou automatiquement par l'archive).

Ranger les fichiers par dossier de même visibilité et mêmes tags (envoi de 10 fichiers au plus à la fois, 50 Mo chacun ; formats pdf, jpg, png, gif, webp). Une carte sans version joueurs n'est pas montrée aux joueurs : la garder en zone MJ.

### 6. Règles

Une ligne par règle spéciale du livre (procédure chiffrée en étapes) et par terme de glossaire (catégorie `Glossaire`, définition dans Résumé). Origine `référence`, statut `Disponible`, source = livre et page.

### 7. Vérifier, puis livrer

1. Contrôler (par un petit script si tu peux exécuter du code, sinon à la main) : aucun nom en double ; chaque lien `[[ ]]` et chaque nom des colonnes Fiches/Documents/Règles des scènes existe dans un autre fichier ou dans la liste des documents ; aucune limite de longueur dépassée ; seulement des nombres dans les champs `nombre` ; statuts connus ; chaque CSV se relit avec le séparateur `;`. Corriger tout ce qui ne passe pas.
2. Relire trois scènes et trois fiches au hasard face au livre : rien d'important ne doit manquer pour jouer sans le livre.
3. Livrer un dossier (et son `.zip`) numéroté dans l'ordre d'import, avec `GUIDE-IMPORT.md` et `A-VERIFIER.md`.

### 8. Style d'écriture

Phrases courtes, au présent, à la troisième personne, en {!! $language !!}. Listes à puces pour les indices, les tests et les issues. Les noms de compétences, de tests et de valeurs chiffrées sont ceux du livre, sans les convertir. Aucune opinion, aucun ajout d'ambiance qui ne soit pas dans le livre.

## Livrables (noms de fichiers fixes)

Livre exactement ces fichiers, numérotés dans l'ordre d'import (un fichier par type de fiche, nommé avec ce type au pluriel, en minuscules et sans accents) :

```
1-champs-<type>.csv        un par type de fiche qui a des champs (ex. 1-champs-personnages.csv)
2-fiches-<type>.csv        un par type de fiche (ex. 2-fiches-personnages.csv, 2-fiches-lieux.csv)
3-regles.csv               règles spéciales et glossaire
4-documents-mj/            images et PDF en zone MJ
4-documents-joueurs/       images et PDF visibles des joueurs
5-scenes.csv               tous les scénarios et leurs scènes
GUIDE-IMPORT.md            le guide pas à pas
A-VERIFIER.md              lectures incertaines et images à découper (avec les pages)
```

## Déroulé de la conversation

1. **Premier message** : l'inventaire de l'étape 2, sous forme de tableaux (types de fiche à créer ; champs avec type et zone ; fiches par type ; règles ; documents avec visibilité ; scénarios et scènes), suivi de la table des noms. Termine par « Réponds « continue » pour lancer la production. » et attends.
2. **Messages suivants** : les fichiers 1 à 3, puis `5-scenes.csv` chapitre par chapitre (un message par chapitre si le livre est long). Chaque message se termine par la liste de ce qui reste à produire. Si la place manque, arrête-toi à la fin d'une ligne et reprends au même endroit quand on te répond « continue ».
3. **Dernier message** : `GUIDE-IMPORT.md`, `A-VERIFIER.md`, puis le résultat des contrôles de l'étape 7 (chaque contrôle avec OK ou la correction faite).

Chaque fichier est rendu en fichier téléchargeable si tu le peux, sinon dans un bloc de code précédé de son nom ; à la fin, une archive .zip de l'ensemble si tu le peux.

## Le guide d'import (GUIDE-IMPORT.md)

Court, en {!! $language !!}, pour le MJ :
1. Rappel : contenu sous droits, usage personnel.
2. Tableau du contenu : chaque fichier, son nombre de lignes, où il va dans SagaWyn.
3. Ouvrir la campagne « {!! $campaign !!} » (ou en créer une) ; créer les **types de fiche** manquants (« Types de fiche »).
4. Campagne → **Importer** : chaque fichier de champs (« Une liste de champs pour le jeu »), puis chaque fichier de fiches (« Des fiches »), en disant s'il faut les ranger dans le monde ou la campagne. L'aperçu doit annoncer le bon nombre de créations et **aucune colonne en « Nouveau champ »**.
5. Les règles (« Des règles ou aides de jeu »), rattachées au jeu entier.
6. **Documents** : chaque dossier en un envoi, avec la visibilité et les tags indiqués.
7. En dernier, les scènes (« Des scénarios et leurs scènes ») : l'aperçu ne doit signaler **aucun élément introuvable**.
8. Si ça coince : « type de fiche inconnu » = le créer d'abord ; « Nouveau champ » = importer d'abord le fichier de champs ; « introuvable » = importer d'abord la fiche ou le document de ce nom ; un import refait avec « Mettre à jour… » coché remplace au lieu de dupliquer.
9. Les points à vérifier dans le livre papier (renvoi à `A-VERIFIER.md`).

## Mettre à jour une campagne déjà importée

Pour enrichir une campagne existante (contenu plus détaillé, séances jouées) sans toucher aux statuts, liens, personnages et notes : produire `1-fiches.csv` (`Nom;Type;Description;Notes MJ`) et `2-scenes.csv` (`Scénario;Scène;Description`) avec **exactement** les noms existants, et dire d'importer avec « Mettre à jour… » coché. L'aperçu doit annoncer 0 création. Prévenir que les descriptions modifiées à la main depuis l'import seront remplacées.

@if ($skill)
## Fichiers du skill

- `scripts/verifier.py` : `python3 -I scripts/verifier.py <dossier des CSV> <dossier des documents>` (ajouter `--mise-a-jour` pour des fichiers qui complètent une campagne existante) fait les contrôles de l'étape 7 ; corriger chaque ligne « PROBLÈME ».
- `references/format-archive.md` : l'archive de campagne `.zip` (un seul import, avec portraits, tags et relations), quand on peut exécuter du code et que le MJ veut créer une nouvelle campagne d'un coup.
@endif

## Annexe A : formats d'import CSV de SagaWyn

Ce que l'écran **Importer** d'une campagne accepte réellement.

### Règles communes

- Fichier `.csv` (ou `.txt`, `.json`), **5 Mo au plus**, **2 000 lignes au plus** : au-delà, découper en plusieurs fichiers.
- Encodage UTF-8 **avec BOM** (Excel français l'ouvre alors correctement), séparateur **point-virgule** `;`.
- Une ligne d'en-têtes, puis une ligne par élément. Valeur contenant `;`, un retour à la ligne ou `"` : entre guillemets doubles, les `"` intérieurs doublés (`""`). En Python : `csv.writer(f, delimiter=';')` sur un fichier ouvert avec `encoding='utf-8-sig', newline=''`.
- Les en-têtes sont reconnus sans tenir compte des accents, majuscules et ponctuation, mais garder exactement ceux ci-dessous.
- Textes longs (description, notes MJ, procédure, scène) : **Markdown** affiché (titres `###`, `**gras**`, `*italique*`, listes `-`, citations `>`). Un simple retour à la ligne reste un retour à la ligne.
- **Liens internes** : `[[Nom exact de la fiche]]`. Dans un CSV, jamais `[[Nom|autre chose]]` : la partie après `|` est réservée à un identifiant numérique. Un lien vers une fiche absente s'affiche en simple texte. Les liens `[[ ]]` ne visent que des **fiches** : une règle ou un document se cite en italique (*Souvenirs vendus*) et se rattache par les colonnes `Règles`/`Documents` des scènes.
- Une case vide lors d'une **mise à jour** ne change rien.

### 1. Champs du jeu — « Une liste de champs pour le jeu … »

En-têtes : `Nom;Groupe;Type;Zone;Choix;Type de fiche;Modifiable par le joueur`

| Colonne | Valeurs |
|---|---|
| Nom | nom du champ, tel qu'il servira d'en-tête dans les fichiers de fiches (`FOR`, `Points de vie`…) |
| Groupe | rubrique d'affichage (`Caractéristiques`, `Compétences`, `Profil de créature`…) |
| Type | `texte` (1 000 car.), `texte long`, `nombre`, `oui/non`, `date`, `liste`, `compteur`, `lien` (adresse web), `fichier` (document de la campagne), `référence` (autre fiche) |
| Zone | `publique` (visible des joueurs qui connaissent la fiche) ou `MJ` |
| Choix | seulement pour `liste` : `d4|d6|d8` |
| Type de fiche | `Personnage`, `Créature`… ; vide = tous les types |
| Modifiable par le joueur | `oui` / `non` (ex. `oui` pour les PV de son personnage) |

Un même nom de champ peut exister pour deux types de fiche (FOR `nombre` pour Personnage, FOR `texte` pour Créature) : l'import des fiches choisit le bon selon la colonne `Type`.

**Choisir le type** : `nombre` seulement si **toutes** les valeurs sont des nombres simples (`12`, `1,5`). Pour des profils avec dés ou fourchettes (`3D6×5`, `40 à 60`, `55 %`), prendre `texte`. Les blocs d'armes, compétences, sorts et capacités : `texte long`.

### 2. Fiches — « Des fiches (une ligne par fiche) »

En-têtes : `Nom;Type;Résumé;Description;Notes MJ` puis une colonne par champ (en-tête = nom exact du champ).

| Colonne | Limite | Contenu |
|---|---|---|
| Nom | 255 | unique par type ; c'est la clé des liens et des mises à jour |
| Type | — | type de fiche existant : `Personnage`, `Lieu`, `Organisation`, `Objet`, `Créature`, ou un type créé avant l'import (`Sort`, `Savoir`…) |
| Résumé | **500** | l'accroche, visible en liste et au survol |
| Description | 20 000 | zone **publique** : ce que les joueurs peuvent savoir en le rencontrant |
| Notes MJ | 20 000 | zone **MJ** : secrets, rôle réel, profil complet, apparitions |

Valeurs de champs : `nombre` en chiffres (`12`, `1,5`) ; `oui/non` en `oui`/`non` ; `liste` = un choix exact ; `compteur` en `11 / 11` ; `date` en `JJ/MM/AAAA` ; `référence` = nom exact d'une fiche ; `lien` = `https://…`.

Options à l'écran : rangement **dans le monde** (lieux, organisations, PNJ partagés par plusieurs campagnes) ou **dans la campagne** ; **Mettre à jour les fiches qui existent déjà sous le même nom et le même type**.

Le portrait d'une fiche ne passe pas par le CSV : il s'ajoute à la main sur la fiche.

### 3. Règles et glossaire — « Des règles ou aides de jeu »

En-têtes : `Titre;Catégorie;Résumé;Procédure;Notes MJ;Source;Origine;Statut;Zone;Tags`

| Colonne | Limite | Valeurs |
|---|---|---|
| Titre | 255 | unique |
| Catégorie | 100 | `Combat`, `Magie`, `Glossaire`… |
| Résumé | 2 000 | la règle en une ou deux phrases (la définition pour un terme de glossaire) |
| Procédure | 20 000 | les étapes, une par ligne, avec les chiffres |
| Notes MJ | 20 000 | conseils d'arbitrage |
| Source | 255 | `Livre de base p. 42` |
| Origine | — | `référence`, `maison` ou `test` |
| Statut | — | `Disponible`, `À tester`, `En test`, `Adoptée`, `À modifier`, `Rejetée` |
| Zone | — | `publique` ou `MJ` |
| Tags | 1 000 | séparés par des virgules |

Rattachement à l'écran : **tout le jeu** (servira aux autres campagnes du même jeu) ou **cette campagne**.

### 4. Scénarios et scènes — « Des scénarios et leurs scènes » (à importer en dernier)

En-têtes : `Scénario;Résumé du scénario;Chapitre;Scène;Description;Statut;Fiches;Documents;Règles`

| Colonne | Limite | Contenu |
|---|---|---|
| Scénario | 255 | regroupe les scènes ; ordre de création = ordre des lignes |
| Résumé du scénario | 5 000 | sur la **première** ligne de chaque scénario seulement |
| Chapitre | 100 | `Acte 1`, `Partie 2 : Le désert`… |
| Scène | 255 | unique dans son scénario |
| Description | **20 000** | voir le modèle « Ce qui est prévu » plus haut ; au-delà, découper la scène |
| Statut | — | `Prévue`, `Disponible`, `En cours`, `Jouée`, `Ignorée` |
| Fiches | — | noms exacts séparés par ` | ` (100 au plus) |
| Documents | — | titres exacts des documents (= nom du fichier sans extension) |
| Règles | — | titres exacts des règles |

Les fiches, documents et règles doivent **déjà exister** dans la campagne : l'aperçu signale chaque nom « introuvable ». Option **Mettre à jour les scènes qui existent déjà sous le même nom dans le même scénario**.

### 5. Documents (aides de jeu, cartes, plans, portraits)

Pas de CSV : page **Documents** de la campagne, envoi de fichiers.
- Formats `pdf`, `jpg`, `jpeg`, `png`, `gif`, `webp` ; **50 Mo** par fichier ; **10 fichiers** par envoi.
- Le **titre** du document = **nom du fichier sans extension**. Nommer donc chaque fichier exactement comme il sera cité dans la colonne `Documents` des scènes.
- Visibilité (`publique` ou `MJ`), rangement (campagne, monde ou jeu) et tags sont choisis **pour tout l'envoi** : ranger les fichiers dans des dossiers qui partagent ces trois réglages (`2-cartes-mj/`, `3-cartes-joueurs/`, `4-indices/`…).
- Un indice à remettre en cours de partie est en zone **MJ** ; le MJ le donne ensuite aux personnages (« Donner »/« Révéler »).

### 6. Mise à jour d'une campagne déjà importée

Fichiers réduits, avec l'option « Mettre à jour… » cochée :
- fiches : `Nom;Type;Description;Notes MJ` (seules ces colonnes sont remplacées) ;
- scènes : `Scénario;Scène;Description`.

L'aperçu doit annoncer **0 création** et autant de mises à jour que de lignes ; sinon un nom ne correspond pas.

## Annexe B : exemple au bon niveau de détail (contenu inventé)

Fiches de PNJ :

```csv
Nom;Type;Résumé;Description;Notes MJ;Vigueur;Esprit;Points de vie;Compétences;Armes
Maître Aldric Fenouil;Personnage;"Gardien du phare de Brumeval, bourru et protecteur, qui n'a pas quitté sa tour depuis la tempête de l'an passé.";"Un homme d'une soixantaine d'années, barbe grise taillée court, mains couvertes de brûlures anciennes. Il parle peu, fixe la mer en répondant et refuse toute visite après le coucher du soleil.

Au village, on dit qu'il a perdu son fils dans la tempête et qu'il « parle à la lanterne ». Il paie ses provisions en pièces anciennes que personne ne reconnaît.";"**Ce qu'il cache** : la lanterne du phare abrite l'esprit de son fils, Tobin, noyé lors de la tempête. Aldric l'entretient avec l'huile de baleine que lui vend [[Veuve Orsel]], au prix de souvenirs qu'il lui cède un à un (il ne se rappelle plus le visage de sa femme).

**Ce qu'il veut** : garder Tobin « vivant » coûte que coûte. Il ment sur tout ce qui touche la lanterne, mais ne ment jamais sur la mer.

**Ce qu'il sait** : les passes sûres entre les récifs (indice nécessaire à la scène « La passe des Dents »), le nom des trois marins disparus.

**Comment il réagit** : menacé, il se barricade et éteint le phare, ce qui déclenche « Le naufrage ». Si on lui parle de son fils avec douceur (test d'Esprit difficile), il avoue tout et demande de l'aide.

**Profil** : Vigueur 3, Esprit 4, 12 PV. Bâton ferré (1D6).

**Apparaît dans** : Le phare éteint, La passe des Dents.";3;4;12 / 12;"Navigation 70 %, Météo 60 %, Intimidation 40 %";"Bâton ferré 50 %, dégâts 1D6"
Veuve Orsel;Personnage;Marchande d'huile et de sel au port, aimable, qui semble ne jamais vieillir.;"Une femme menue au châle bleu, toujours souriante, qui tient l'échoppe à l'entrée du port. Elle connaît le prénom de chaque enfant du village.";"**Identité réelle** : une sorcière des marées qui achète des souvenirs. Elle ne peut agir contre quelqu'un qui ne lui a rien vendu.

**Ce qu'elle veut** : le dernier souvenir d'Aldric, celui de son fils, qui ferait d'elle la maîtresse de la lanterne.

**Profil** : Vigueur 2, Esprit 6, 10 PV. Sort *Brume d'oubli* (voir [[Lanterne de Brumeval]]).

**Apparaît dans** : Arrivée au port, Le naufrage.";2;6;10 / 10;"Marchandage 80 %, Psychologie 65 %, Occultisme 75 %";
```

Scènes (une jouée comme prévu, une jouée qui a dévié) :

```csv
Scénario;Résumé du scénario;Chapitre;Scène;Description;Statut;Fiches;Documents;Règles
1. La Lanterne de Brumeval;"Le phare de Brumeval s'éteint une nuit sur deux, et des pêcheurs disparaissent. Les personnages découvrent que le gardien garde l'esprit de son fils noyé dans la lanterne, et qu'une sorcière des marées veut s'en emparer.";Acte 1 : Le port;Arrivée au port;"### Ce qui est prévu

**Enjeu** : faire comprendre que le village a peur du phare et attirer l'attention sur la Veuve Orsel, sans la désigner.

**Situation** : les personnages arrivent par la diligence du soir. Le port est désert, les barques tirées sur la grève. Seule l'échoppe de la [[Veuve Orsel]] est éclairée.

**Ce que disent les gens** :
- l'aubergiste : trois marins disparus en un mois, toujours les nuits où le phare était éteint ;
- les enfants : « le vieux Fenouil parle à sa lampe » ;
- la Veuve Orsel, très aimable, propose de l'huile « pour la route » et demande à chacun son plus beau souvenir, « par curiosité ».

**Indices** :
- *Écouter* (facile) : à la nuit tombée, une voix d'enfant semble venir du phare ;
- *Psychologie* (difficile) sur la Veuve : son sourire ne monte jamais aux yeux ;
- *Trouver objet caché* : des fûts marqués de son sceau partent vers le phare.

**À remettre** : *Carte joueurs - Brumeval*.

**Si les joueurs vendent un souvenir** : appliquer la règle *Souvenirs vendus* ; la Veuve peut désormais agir contre eux (« Le naufrage »).

**Transitions** : vers « Le phare éteint » (de nuit) ou une visite de jour à [[Maître Aldric Fenouil]], qui les éconduit.

La Lanterne de Brumeval p. 3-4.";Jouée;Veuve Orsel | Maître Aldric Fenouil;Carte joueurs - Brumeval;Souvenirs vendus
1. La Lanterne de Brumeval;;Acte 1 : Le port;Le phare éteint;"### Ce qui est prévu

**Enjeu** : entrer dans la Lanterne et découvrir que la flamme est l'esprit de Tobin.

**Situation** : une nuit sur deux, la lumière s'éteint à minuit. Aldric se tient alors au sommet, immobile, et chuchote.

**Entrer** : porte barrée (Vigueur difficile pour l'enfoncer, bruit qui alerte Aldric) ou fenêtre du premier étage par l'escalier extérieur (Escalade).

**Dans la tour** : voir [[Lanterne de Brumeval]] pour les étages et les indices. Le carnet de Tobin est la pièce maîtresse : le remettre (*Indice - Lettre de la veuve Orsel* est glissée dedans).

**Aldric** : surpris, il menace mais ne frappe qu'acculé. Calmé, il avoue (voir sa fiche).

**Issues** :
- Aldric avoue : il demande d'empêcher la Veuve de prendre son dernier souvenir ; passer à l'acte 2 ;
- Aldric est blessé ou humilié : il éteint le phare pour de bon, une barque se brise sur les récifs la nuit suivante.

**Récompense** : sauver un pêcheur cette nuit-là, +1 point d'inspiration.

La Lanterne de Brumeval p. 5-6.

### Ce qui s'est passé (séance 2)

Les joueurs n'ont pas forcé la porte : Lise a vendu à la Veuve le souvenir de sa première chasse pour obtenir la clé. Aldric les a trouvés au sommet et a fait tomber la lanterne ; Tobin s'est manifesté sous forme de brume verte et a sauvé Bran de la chute. Le carnet n'a pas été trouvé (il est toujours dans la chambre).";Jouée;Maître Aldric Fenouil | Lanterne de Brumeval;Indice - Lettre de la veuve Orsel;Souvenirs vendus
```
