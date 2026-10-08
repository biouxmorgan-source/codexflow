# Préparer un import LoreMundi à partir de documents de jeu de rôle

Tu aides un meneur de jeu à remplir **LoreMundi**, son assistant de MJ. Je te joins un ou plusieurs documents (livre de règles, scénario, campagne, supplément, en PDF ou en images). Ta mission : produire des **fichiers CSV prêts à importer** dans LoreMundi et un **guide d'import pas à pas**.

Écris le contenu des fiches, les noms de champs et le guide en **{!! $language !!}**. Les **en-têtes de colonnes** restent exactement ceux donnés ci-dessous (en français) : LoreMundi les reconnaît ainsi.

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

## Règles à respecter

1. **Usage personnel.** Ces documents appartiennent à leurs éditeurs. Ne recopie pas de longs passages : écris des **résumés aide-mémoire** et indique la page (« Livre p. 42 ») pour que le MJ retrouve le texte original.
2. **Rien d'inventé.** Si une information manque dans le document, laisse la case vide.
3. **Secrets côté MJ.** Ce que les joueurs ne doivent pas savoir (identité cachée, motivations secrètes, solution d'une énigme, statistiques des adversaires) va dans « Notes MJ » ou dans des champs en zone MJ.
4. **Liens.** Dans les descriptions, entoure le nom exact d'une autre fiche de doubles crochets pour créer un lien : `[[Dr Eudora Lockhart]]`.
5. **Noms uniques et identiques partout.** Une fiche, une règle ou un document porte le même nom dans tous les fichiers (les scènes les retrouvent par leur nom).
6. **Document long ?** Commence par l'inventaire et le plan (étape 1), attends ma validation, puis produis les fichiers par lots.

## Étape 1 : inventaire et plan

Liste ce que contient le document : personnages (prétirés, PNJ), créatures, lieux, organisations, objets, sorts ou pouvoirs, règles et glossaire, scénarios et scènes, aides de jeu (cartes, plans, lettres, indices). Propose ensuite :
- les **types de fiche à créer** dans LoreMundi s'ils n'existent pas (par exemple « Sort »), en plus des types existants ;
- les **champs** à définir (caractéristiques, compétences, profil de créature…) ;
- la liste des fichiers que tu vas produire, dans l'ordre d'import.

## Étape 2 : les fichiers

Format commun à tous les CSV : encodage UTF-8, séparateur **point-virgule** `;`, une ligne d'en-têtes puis une ligne par élément. Mets entre guillemets doubles toute valeur qui contient `;`, un retour à la ligne ou un guillemet (et double les guillemets à l'intérieur : `""`). Numérote les fichiers dans l'ordre d'import (`1-champs-personnages.csv`, `2-pretires.csv`…).

### A. Champs du jeu (`champs-….csv`)

En-têtes : `Nom;Groupe;Type;Zone;Choix;Type de fiche;Modifiable par le joueur`

- **Type** : `texte`, `texte long`, `nombre`, `oui/non`, `date`, `liste`, `compteur` (points de vie, munitions…), `lien` (adresse web), `fichier` (document de la campagne) ou `référence` (une autre fiche).
- **Zone** : `publique` (visible des joueurs qui connaissent la fiche) ou `MJ`.
- **Choix** : seulement pour `liste`, valeurs séparées par `|` (`d4|d6|d8`).
- **Type de fiche** : le type auquel le champ s'applique (`Personnage`, `Créature`…), vide pour tous les types.
- **Modifiable par le joueur** : `oui` ou `non` (par exemple `oui` pour les points de vie de son personnage).

### B. Fiches (`fiches-….csv`, un fichier par type de fiche si possible)

En-têtes : `Nom;Type;Résumé;Description;Notes MJ` puis **une colonne par champ**, dont l'en-tête est le nom exact du champ (défini dans un fichier A ou déjà existant).

- **Type** : un type de fiche existant ou annoncé à l'étape 1.
- **Résumé** : une ou deux phrases, 500 caractères au plus.
- **Description** : le texte public (apparence, rôle, ce que tout le monde sait), avec la page du livre.
- Valeurs des champs : `nombre` avec des chiffres seulement (`12` ou `1,5`) ; `oui/non` en `oui` ou `non` ; `liste` avec l'un des choix exacts ; `compteur` en `valeur / maximum` (`11 / 11`) ; `date` en `JJ/MM/AAAA` ; `référence` avec le nom exact d'une autre fiche ; `lien` avec une adresse commençant par https://.

### C. Règles et glossaire (`regles.csv`)

En-têtes : `Titre;Catégorie;Résumé;Procédure;Notes MJ;Source;Origine;Statut;Zone;Tags`

- **Procédure** : les étapes de la règle, résumées (une étape par ligne).
- **Source** : le livre et la page.
- **Origine** : `référence` (règle du livre), `maison` ou `test`. **Statut** : `Disponible` pour une règle du livre.
- **Zone** : `publique` ou `MJ`. **Tags** : séparés par des virgules.
- Pour un glossaire : une ligne par terme, catégorie `Glossaire`, la définition dans « Résumé ».

### D. Aides de jeu et cartes (`documents.md`)

Tu ne peux pas forcément extraire les images : fais un tableau des aides de jeu à préparer, avec pour chacune le **titre** (qui sera aussi le nom du fichier image ou PDF), la **page** du document source, la visibilité (**MJ seulement** ou **consultable par les joueurs**) et des tags (`Carte`, `Indice`…). Si tu peux produire les fichiers, nomme-les exactement par leur titre.

### E. Scénarios et scènes (`scenes.csv`, à importer en dernier)

En-têtes : `Scénario;Résumé du scénario;Chapitre;Scène;Description;Statut;Fiches;Documents;Règles`

- Une ligne par scène, dans l'ordre du scénario. Le « Résumé du scénario » ne se remplit que sur la première ligne de chaque scénario.
- **Description** : ce qui se passe, ce que les personnages peuvent apprendre, la page.
- **Statut** : `Prévue`.
- **Fiches**, **Documents**, **Règles** : noms exacts séparés par `|` (fiches des fichiers B, titres du tableau D, titres du fichier C).

### F. Le guide (`GUIDE-IMPORT.md`)

Un guide pas à pas pour le MJ, en {!! $language !!}, qui suit cet ordre :
1. un tableau du contenu : chaque fichier, ce qu'il contient (nombre d'éléments) et où il va dans LoreMundi ;
2. créer la campagne (ou ouvrir « {!! $campaign !!} ») ;
3. créer les types de fiche manquants dans « Types de fiche » ;
4. dans la campagne, « Importer » : les fichiers de champs (« Une liste de champs pour le jeu »), puis les fiches (« Des fiches ») ;
5. importer les règles (« Des règles ou aides de jeu »), rattachées à tout le jeu si elles servent à d'autres campagnes ;
6. téléverser les aides de jeu dans « Documents », avec la visibilité et les tags du tableau D ;
7. importer les scènes en dernier (« Des scénarios et leurs scènes ») : l'aperçu ne doit signaler aucun élément « introuvable » ;
8. ce qu'il faut vérifier après chaque import (le nombre d'éléments annoncé par l'aperçu) et que faire si ça coince : une colonne annoncée « Nouveau champ » veut dire que son fichier de champs n'a pas été importé avant ; un « type de fiche inconnu » veut dire qu'il faut le créer d'abord ; un import refait met à jour les éléments du même nom au lieu de les dupliquer.

## Étape 3 : vérifications avant de me rendre les fichiers

- Chaque colonne de champ d'un fichier de fiches correspond à un champ d'un fichier A ou à un champ existant.
- Chaque type de fiche utilisé existe ou figure dans la liste à créer.
- Chaque nom cité dans les scènes existe dans un autre fichier ou dans le tableau D.
- Aucune ligne sans nom, aucun doublon de nom dans un même fichier.

Rends chaque fichier dans un bloc de code séparé précédé de son nom, ou en fichiers téléchargeables (une archive .zip si tu le peux).
