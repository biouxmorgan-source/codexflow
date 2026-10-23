# Prepare a SagaWyn import from a tabletop RPG book

You are helping a game master (GM) fill **SagaWyn**, their GM assistant. I am attaching one or more documents (rulebook, scenario, campaign, supplement, as PDF or images), and perhaps my session notes. @if ($archive)
Your mission: produce a **SagaWyn campaign archive** (`.zip`) that creates, in a single import, a new game, a new world and a new campaign with the whole content of the book, plus a **step-by-step import guide**.

The archive is built with a script (Python): you must be able to **run code** and produce downloadable files. If you cannot, say so in your first message and stop: the GM will create an empty campaign and use the {!! $ui('Préparez les fichiers avec une IA') !!} link on its {!! $ui('Importer') !!} screen, which produces CSV files without code.
@else
Your mission: produce **files ready to import** into SagaWyn and a **step-by-step import guide**.
@endif

This prompt is a **procedure to follow to the letter**, so that the result is the same whichever AI runs it: same files, same file names, same columns, same sections in the same order, same decision rules. When an instruction seems ambiguous, apply the decision rule given here rather than your own choice. Do not skip any step and do not change the order of the deliveries (see "Conversation flow").

## Language

Write all the content (entries, field names and groups, rules, scenes, document titles, `IMPORT-GUIDE.md` and `TO-CHECK.md`) in **{!! $language !!}**, unless the GM asks for another language in the conversation: then use that one everywhere. Keep the names from the book as they are printed (people, places, skills, spells), translating only what the book itself translates.

What does **not** change with the language: the **column headers** and the **closed values** (field types, zones, statuses, origins, `oui`/`non`) are SagaWyn identifiers, written below in French; copy them exactly. SagaWyn's screen names are quoted as the GM sees them ("{!! __('Importer') !!}", "{!! __('Documents') !!}"…).

@if ($archive)
## The campaign to create

The archive creates in SagaWyn a **new game**, a **new world** and a **new campaign**, with the GM as their game master:
- game: the name of the book's rules system ("Imported game" if it has no name, in {!! $language !!});
- world: the name of the book's world or setting ("Imported world" if it has no name, in {!! $language !!});
- campaign: the title of the book or of the campaign it contains;
- base entry types, already in SagaWyn: Personnage (character), Lieu (place), Organisation (organization), Objet (item), Créature (creature); any other type (spell, lore, vehicle…) is created by the archive;
- no field exists yet: the archive creates them all.
@else
## The campaign that will receive the import

- Game: {!! $game !!}
- World: {!! $world !!}
- Existing entry types: {!! $types !!}
@if ($fields !== [])
- Fields already defined for this game (reuse exactly these names rather than creating new ones):
@foreach ($fields as $field)
  - {!! $field !!}
@endforeach
@else
- No field is defined for this game yet.
@endif
@endif

## The goal

From a book (PDF) and possibly the GM's session notes, this work produces files that recreate the campaign in SagaWyn: **entries** (NPCs, places, creatures, organizations, items, spells), game **fields**, **rules**, **documents** (maps, clues, portraits) and **scenarios/scenes**.

The goal: the GM can **run the game from SagaWyn without opening the book again**. That is the quality criterion for every entry and every scene.

## Safeguards

1. **Personal use.** A commercial book is copyrighted. The files produced are for the GM who bought the book, for their own table. They never go into a public repository, a demo campaign, a shared template or a public message. Repeat this rule at the top of the guide.
2. **All available content, with the page.** Put in everything you can from the book, especially everything needed to play (flow, clues, tests, reactions, stat blocks, outcomes), in your own words and structured as a reference sheet. No bare "see book p. 42" instead of the content: the page goes **at the end** of the text, as its source. Read-aloud boxes are summarised, stating what they must convey.
3. **Nothing invented.** What the book does not say stays empty. An uncertain reading (jumbled stat table, illegible number) goes into `TO-CHECK.md` with its page, and is marked "(to check)" in the text, in {!! $language !!}.
4. **Public zone / GM zone.** What a player can learn by meeting the element goes in **Description** or in a `publique` field. Everything else (true identity, hidden motives, solution, opponents' stats, what will happen) goes in **Notes MJ** or in an `MJ` field. In a scene everything is for the GM; mark as **GM secret** (in {!! $language !!}) what must never be read to the players.
5. **No game-specific column invented by SagaWyn**: the game's vocabulary (Strength, Hit points, Fate points…) goes through the **fields** you define yourself.

## Method

### 1. Read the whole book

- Read the document **in full**, chapter by chapter, including sidebars, tables and appendices. Never work from the table of contents or a summary.
- Pages that are images (maps, plans, letters, scanned sidebars) are read visually; whatever they contain that is useful for play goes into the entries and scenes.
- If you can run code: extract the text (for example `pdftotext -layout`), crop the useful images (portraits, maps, handouts) and name them as in step 5. Otherwise: list those images in `TO-CHECK.md` with their page and the file name to give them, so that the GM crops them.
- If the GM provides session reports or character sheets, read them too: they feed "{!! $happened !!}" and the player characters' entries.

### 2. Inventory and table of names

Before writing, draw up the inventory and show it to the GM if they are available (otherwise put it at the top of the guide):
- the **entry types** needed (base types are Personnage, Lieu, Organisation, Objet, Créature; to create: spell, lore, vehicle…);
- the **fields** per type (characteristics, skills, creature stats…) with their type and zone;
- the **entries**: every named NPC, place, organization, important item, creature, spell;
- the **rules**: the book's special rules, glossary;
- the **documents**: every map, plan, clue, letter, portrait, with its visibility;
- the **scenarios** (one per chapter or adventure, numbered "1. Title") and their **scenes** in the book's order.

Set a **table of canonical names**: each element has one unique name, written identically everywhere (entries, `[[ ]]` links, scene Fiches/Documents/Règles columns). Pick the name most used in the book, without a variable title of address.

**Decision rules** (apply them as they are):
- a **scenario** = a chapter of the book (or an adventure if it contains several), named "N. Chapter title";
- a **scene** = each numbered or titled section of the chapter that describes a playable place, encounter or event, in the book's order;
- an **NPC gets an entry** if they are named and have a stat block **or** appear in at least two scenes; otherwise they are described in the scene where they appear;
- a **place gets an entry** if it has its own description section (or a map); a **creature** as soon as it has a stat block; an **item** if it has effects or is a stake of the plot;
- a **field** is created for each value that appears in the stat block of most entries of the same type; the rest goes in the Notes MJ.

### 3. Entries (complete)

- **Résumé** (≤ 500 characters): who or what it is and why it matters, in one or two sentences.
- **Description** (public): appearance, visible behaviour, known role, reputation; for a place, what one sees on arrival and its parts.
- **Notes MJ**, structured in Markdown with bold headings, **always in this order**, written in {!! $language !!}; a heading the book says nothing about is left out:
  - NPC: **True identity / What they hide**, **What they want**, **What they know** (the clues they can give, and on what terms), **How they react** (alliance, threat, flight, betrayal), **Stats** if not covered by the fields (weapons, spells, special abilities, fear or sanity according to the game), **Appears in** (scenes).
  - Place: areas or rooms one by one, **NPCs present**, **Dangers**, **Clues** (where, which test), **Secret**.
  - Creature: **Behaviour**, **Tactics**, **Attacks** and damage, **Armour**, **Abilities**, **Fear or sanity effect** (according to the game), **How to defeat it**.
  - Item / spell: effects, cost, duration, conditions, where it is.
- **Fields**: the book's exact values; a stat with dice or ranges goes in `texte` fields (see appendix A).
- **Links**: each name of another entry quoted in a text is written `[[Exact name]]` (at least at its first occurrence).
- One entries file per entry type (see "Deliverables").

### 4. Scenes: "{!! $planned !!}" (and "{!! $happened !!}")

One row per scene, in the book's order; optional scenes carry "(optional)", in {!! $language !!}, in their name. The **Description** starts with the heading `### {!! $planned !!}` (exactly this text: SagaWyn recognises it) and gives the following bold headings, **always in this order**, written in {!! $language !!}; a heading the book says nothing about is left out:
- **Stake**: what the scene must produce in the story;
- **When / Where / Situation**: trigger, setting, mood, what the characters see;
- **NPCs**: who is there, their attitude, what they say or want (`[[ ]]` links);
- **Clues**: each clue, how to get it (skill or test, difficulty), what it reveals;
- **Tests and dangers**: rolls asked for, consequences of failure, opponents (pointer to their entry, plus the essentials of their stats if they have no entry);
- **Outcomes**: the possible branches and their consequences for what follows;
- **To hand out**: handouts (exact document titles) and when to give them;
- **Rewards**: experience, items, other gains the game provides;
- **Advice** from the book to the GM (pace, improvisation, how to bring the players back);
- **Transitions**: towards which scenes;
- as the last line, the source: `Book p. 97-101.` (in {!! $language !!}).

A scene longer than 20,000 characters? Split it ("The temple (1/2)", "The temple (2/2)").

**{!! $happened !!}** (only if the GM provided an account of their sessions): for a played scene **that departed from the book**, add after the planned part a section `### {!! $happened !!} (session N)` (the heading text exactly, "session N" in {!! $language !!}) telling what really happened at the table: players' choices, NPCs killed or spared, missed clues, items obtained. A scene played as planned does not get this section: its **Statut** becomes `Jouée`, that is all. A scene the GM created outside the book is added at its place, with only its "{!! $happened !!}".

**Fiches**, **Documents**, **Règles** columns: everything used in the scene, by exact name, separated by ` | `. Statut: `Prévue` (or `Jouée`, `Ignorée` according to the GM's notes).

### 5. Named documents

In SagaWyn a document's title is its **file name without the extension**. Name each image or PDF with its final title, with a prefix that tells its nature, written in {!! $language !!}; in English, for example:
- `GM map - Inn floor plan.jpg` (GM zone), `Player map - Inn floor plan.jpg` (public);
- `Clue - Letter from widow Orsel.jpg`, `Handout - Telegram.jpg` (GM zone, handed out during play);
- `Portrait - Aldric Fenwick.jpg` (to set on the entry by hand, or automatically by the archive).

Sort the files into folders that share the same visibility and tags (upload of 10 files at most at a time, 50 MB each; formats pdf, jpg, png, gif, webp). A map with no player version is not shown to the players: keep it in the GM zone.

### 6. Rules

One row per special rule of the book (numbered procedure in steps) and per glossary term (a "Glossary" category, in {!! $language !!}, definition in Résumé). Origine `référence`, Statut `Disponible`, source = book and page.

### 7. Check, then deliver

1. Check (with a small script if you can run code, by hand otherwise): no duplicate name; every `[[ ]]` link and every name in the scenes' Fiches/Documents/Règles columns exists in another file or in the list of documents; no length limit exceeded; only numbers in `nombre` fields; known statuses; every CSV reads back with the `;` separator. Fix everything that fails.
2. Re-read three scenes and three entries at random against the book: nothing important for playing without the book may be missing.
3. Deliver a folder (and its `.zip`) numbered in import order, with `IMPORT-GUIDE.md` and `TO-CHECK.md`.

### 8. Writing style

Short sentences, present tense, third person, in {!! $language !!}. Bullet lists for clues, tests and outcomes. Skill names, test names and numeric values are the book's, not converted. No opinion, no added atmosphere that is not in the book.
@if ($archive)

### 9. Assemble the campaign archive

The CSV files of the deliverables are the **working files**: write and check them first (step 7), then convert them with a script into the archive, in the format of appendix C, **without rewriting anything by hand**:
- `game_system`, `world`, `campaign`: the names from "The campaign to create", with a description of a few lines taken from the book; `table_theme` `parchemin`, `exchanges_need_approval` `true`, `players_edit_profile` `false`, `disabled_features` empty.
- `entity_types`: the five base types with their `key` (`character`, `place`, `organization`, `item`, `creature`), then one type per created type, with `key` `null`.
- `field_definitions`: one entry per row of the `1-fields-…` files, `position` in row order; types `texte`→`text`, `texte long`→`long_text`, `nombre`→`number`, `oui/non`→`boolean`, `date`→`date`, `liste`→`select` (`options` = the choices), `compteur`→`counter`, `lien`→`link`, `fichier`→`file`, `référence`→`entity`; zone `publique`→`public`, `MJ`→`gm`.
- `entities`: one entry per row of the `2-entries-…` files, `in_world` `false` except for places, organizations and NPCs shared by the whole setting (`true`); `field_values`: numbers as numbers, counters as `{"value": 11, "max": 11}`, `oui`/`non` as booleans, the rest as text. An image `Portrait - <Name>` becomes the `image` of the entry with that name (and is not a document).
- `rules`: one entry per row of `3-rules.csv`, `in_campaign` `false`; origin `référence`→`reference`, `maison`→`house`, `test`→`test`; status `Disponible`→`available`, `À tester`→`to_test`, `En test`→`testing`, `Adoptée`→`adopted`, `À modifier`→`to_change`, `Rejetée`→`rejected`; `tags` taken into the `tags` list.
- `documents`: one per file of `4-documents-gm/` (zone `gm`) and `4-documents-players/` (zone `public`), `scope` `campaign`, `title` = file name without extension, file copied into `fichiers/` under a numbered name; `entities` = the entries named in the title.
- `scenarios`: one per value of the Scénario column, in row order; their scenes with `chapter`, `name`, `description`, `status` (`Prévue`→`planned`, `Disponible`→`available`, `En cours`→`in_progress`, `Jouée`→`played`, `Ignorée`→`skipped`), `entities` / `documents` / `rules` = the ids of the names in the Fiches / Documents / Règles columns.
- `relations`, `entity_states`, `tags`, `pins`, `maps`, `timeline`, `audio_tracks`, `secrets` and `table`: empty (`[]`, and `table` as in the example of appendix C) except for an explicit relation in the book (employer, parent, leader…), in zone `gm` if it is secret.
- Links: convert each `[[Name]]` into `[[Name|id]]` with the entry's local id; report in `TO-CHECK.md` any name that matches no entry.

Then read back the archive produced (open the zip, read the JSON, count entries, scenes, documents, check that every `fichiers/…` it cites exists) and give these numbers in the last message.
@endif

## Deliverables (fixed file names)

Deliver exactly these files, numbered in import order (one file per entry type, named after that type in English, plural, lowercase):

```
1-fields-<type>.csv        one per entry type that has fields (e.g. 1-fields-characters.csv)
2-entries-<type>.csv       one per entry type (e.g. 2-entries-characters.csv, 2-entries-places.csv)
3-rules.csv                special rules and glossary
4-documents-gm/            images and PDFs in the GM zone
4-documents-players/       images and PDFs visible to the players
5-scenes.csv               all the scenarios and their scenes
IMPORT-GUIDE.md            the step-by-step guide
TO-CHECK.md                uncertain readings and images to crop (with the pages)
@if ($archive)
SagaWyn - <Title>.zip      the archive to import, assembled in step 9 from the files above
@endif
```

## Conversation flow

1. **First message**: the step 2 inventory, as tables (entry types to create; fields with type and zone; entries per type; rules; documents with visibility; scenarios and scenes), followed by the table of names. End with "Reply "continue" to start the production." (in {!! $language !!}) and wait.
2. **Following messages**: files 1 to 3, then `5-scenes.csv` chapter by chapter (one message per chapter if the book is long). Each message ends with the list of what remains to produce. If you run out of room, stop at the end of a row and resume at the same place when the GM replies "continue".
@if ($archive)
3. **Last message**: the `SagaWyn - <Title>.zip` archive (step 9), `IMPORT-GUIDE.md`, `TO-CHECK.md`, then the result of the step 7 checks and of the archive read-back (each check with OK or the fix made).
@else
3. **Last message**: `IMPORT-GUIDE.md`, `TO-CHECK.md`, then the result of the step 7 checks (each check with OK or the fix made).
@endif

Each file is delivered as a downloadable file if you can, otherwise in a code block preceded by its name; at the end, a .zip archive of the whole set if you can.

## The import guide (IMPORT-GUIDE.md)

Short, in {!! $language !!}, for the GM:
1. Reminder: copyrighted content, personal use.
@if ($archive)
2. Table of the archive's content: number of entry types, fields, entries per type, rules, documents, scenarios and scenes.
3. In SagaWyn: **{!! $ui('Mes campagnes') !!} → {!! $ui('Importer une campagne') !!}**, choose `SagaWyn - <Title>.zip` (without unzipping it), **{!! $ui('Importer') !!}**. If the archive exceeds the maximum size shown, remove the least useful images and run step 9 again, or upload them afterwards from the **{!! $ui('Documents') !!}** page.
4. Check in the new campaign: a few entries (portrait, fields, GM notes), a scenario and its scenes (attached entries, documents and rules), the documents page.
5. The CSV files remain useful for an update (see below): keep them with the archive.
6. The points to check in the paper book (pointer to `TO-CHECK.md`).
@else
2. Table of the content: each file, its number of rows, where it goes in SagaWyn.
3. Open the campaign "{!! $campaign !!}" (or create one); create the missing **entry types** ({!! $ui('Types de fiche') !!}).
4. Campaign → **{!! $ui('Importer') !!}**: each fields file ({!! $ui('Une liste de champs pour le jeu :name', ['name' => '…']) !!}), then each entries file ({!! $ui('Des fiches (une ligne par fiche)') !!}), saying whether to store them in the world or the campaign. The preview must announce the right number of creations and **no column as {!! $ui('Nouveau champ') !!}**.
5. The rules ({!! $ui('Des règles ou aides de jeu') !!}), attached to the whole game.
6. **{!! $ui('Documents') !!}**: each folder in one upload, with the visibility and tags given.
7. Last, the scenes ({!! $ui('Des scénarios et leurs scènes') !!}): the preview must report **no element not found**.
8. If something goes wrong: unknown entry type = create it first; {!! $ui('Nouveau champ') !!} = import the fields file first; not found = import the entry or document with that name first; an import redone with the update option ticked replaces instead of duplicating.
9. The points to check in the paper book (pointer to `TO-CHECK.md`).
@endif

## Updating a campaign already imported

To enrich an existing campaign (more detailed content, sessions played) without touching statuses, links, characters and notes: produce `1-entries.csv` (`Nom;Type;Description;Notes MJ`) and `2-scenes.csv` (`Scénario;Scène;Description`) with **exactly** the existing names, and tell the GM to import them with the update option ticked ({!! $ui('Mettre à jour les fiches qui existent déjà sous le même nom et le même type (les cases vides ne changent rien)') !!}, {!! $ui('Mettre à jour les scènes qui existent déjà sous le même nom dans le même scénario (les cases vides ne changent rien)') !!}). The preview must announce 0 creations. Warn that descriptions edited by hand since the import will be replaced.

@if ($skill)
## Skill files

- `scripts/check.py`: `python3 -I scripts/check.py <CSV folder> <documents folder>` (add `--update` for files that complete an existing campaign) runs the step 7 checks; fix every "PROBLEM" line.
@unless ($archive)
- `references/archive-format.md`: the `.zip` campaign archive (a single import, with portraits, tags and relations), when you can run code and the GM wants to create a new campaign in one go.
@endunless
@endif

## Appendix A: SagaWyn CSV import formats

What a campaign's **{!! $ui('Importer') !!}** screen really accepts. Headers and closed values are written exactly as SagaWyn expects them (in French), whatever the content language.

### Common rules

- `.csv` file (or `.txt`, `.json`), **5 MB at most**, **2,000 rows at most**: beyond that, split into several files.
- UTF-8 encoding **with BOM** (Excel then opens it correctly), **semicolon** separator `;`.
- One header row, then one row per element. A value containing `;`, a line break or `"`: between double quotes, inner `"` doubled (`""`). In Python: `csv.writer(f, delimiter=';')` on a file opened with `encoding='utf-8-sig', newline=''`.
- Headers are recognised regardless of accents, case and punctuation, but keep exactly those below.
- Long texts (description, GM notes, procedure, scene): rendered **Markdown** (`###` headings, `**bold**`, `*italic*`, `-` lists, `>` quotes). A single line break stays a line break.
- **Internal links**: `[[Exact name of the entry]]`. In a CSV, never `[[Name|something else]]`: the part after `|` is reserved for a numeric id. A link to a missing entry shows as plain text. `[[ ]]` links only target **entries**: a rule or a document is quoted in italics (*Sold memories*) and attached through the scenes' `Règles`/`Documents` columns.
- An empty cell during an **update** changes nothing.

### 1. Game fields — {!! $ui('Une liste de champs pour le jeu :name', ['name' => '…']) !!}

Headers: `Nom;Groupe;Type;Zone;Choix;Type de fiche;Modifiable par le joueur`

| Column | Values |
|---|---|
| Nom | field name, as it will be used as a header in the entries files (`STR`, `Hit points`…) |
| Groupe | display section (`Characteristics`, `Skills`, `Creature stats`…) |
| Type | `texte` (1,000 chars), `texte long`, `nombre`, `oui/non`, `date`, `liste`, `compteur`, `lien` (web address), `fichier` (campaign document), `référence` (another entry) |
| Zone | `publique` (visible to players who know the entry) or `MJ` |
| Choix | only for `liste`: `d4|d6|d8` |
| Type de fiche | `Personnage`, `Créature`… (an existing entry type); empty = all types |
| Modifiable par le joueur | `oui` / `non` (e.g. `oui` for their character's hit points) |

The same field name may exist for two entry types (STR `nombre` for Personnage, STR `texte` for Créature): the entries import picks the right one from the `Type` column.

**Choosing the type**: `nombre` only if **all** values are plain numbers (`12`, `1,5`). For stats with dice or ranges (`3D6×5`, `40 to 60`, `55 %`), use `texte`. Blocks of weapons, skills, spells and abilities: `texte long`.

### 2. Entries — {!! $ui('Des fiches (une ligne par fiche)') !!}

Headers: `Nom;Type;Résumé;Description;Notes MJ` then one column per field (header = exact field name).

| Column | Limit | Content |
|---|---|---|
| Nom | 255 | unique per type; it is the key of links and updates |
| Type | — | an existing entry type: `Personnage`, `Lieu`, `Organisation`, `Objet`, `Créature` (or their key `character`, `place`, `organization`, `item`, `creature`), or a type created before the import |
| Résumé | **500** | the hook, visible in lists and on hover |
| Description | 20,000 | **public** zone: what players can know by meeting it |
| Notes MJ | 20,000 | **GM** zone: secrets, real role, full stats, appearances |

Field values: `nombre` in digits (`12`, `1,5`); `oui/non` as `oui`/`non`; `liste` = an exact choice; `compteur` as `11 / 11`; `date` as `DD/MM/YYYY`; `référence` = exact name of an entry; `lien` = `https://…`.

Options on screen: store **in the world** (places, organizations, NPCs shared by several campaigns) or **in the campaign**; update the entries that already exist under the same name and type.

An entry's portrait does not go through the CSV: it is added by hand on the entry.

### 3. Rules and glossary — {!! $ui('Des règles ou aides de jeu') !!}

Headers: `Titre;Catégorie;Résumé;Procédure;Notes MJ;Source;Origine;Statut;Zone;Tags`

| Column | Limit | Values |
|---|---|---|
| Titre | 255 | unique |
| Catégorie | 100 | `Combat`, `Magic`, `Glossary`… (in {!! $language !!}) |
| Résumé | 2,000 | the rule in one or two sentences (the definition for a glossary term) |
| Procédure | 20,000 | the steps, one per line, with the numbers |
| Notes MJ | 20,000 | refereeing advice |
| Source | 255 | `Core book p. 42` |
| Origine | — | `référence`, `maison` or `test` |
| Statut | — | `Disponible`, `À tester`, `En test`, `Adoptée`, `À modifier`, `Rejetée` |
| Zone | — | `publique` or `MJ` |
| Tags | 1,000 | comma-separated |

Attachment on screen: **the whole game** (used by the other campaigns of the same game) or **this campaign**.

### 4. Scenarios and scenes — {!! $ui('Des scénarios et leurs scènes') !!} (import last)

Headers: `Scénario;Résumé du scénario;Chapitre;Scène;Description;Statut;Fiches;Documents;Règles`

| Column | Limit | Content |
|---|---|---|
| Scénario | 255 | groups the scenes; creation order = row order |
| Résumé du scénario | 5,000 | on the **first** row of each scenario only |
| Chapitre | 100 | `Act 1`, `Part 2: The desert`… |
| Scène | 255 | unique within its scenario |
| Description | **20,000** | see the "{!! $planned !!}" model above; beyond that, split the scene |
| Statut | — | `Prévue`, `Disponible`, `En cours`, `Jouée`, `Ignorée` |
| Fiches | — | exact names separated by ` | ` (100 at most) |
| Documents | — | exact document titles (= file name without extension) |
| Règles | — | exact rule titles |

The entries, documents and rules must **already exist** in the campaign: the preview reports each name not found. Option to update the scenes that already exist under the same name in the same scenario.

### 5. Documents (handouts, maps, plans, portraits)

No CSV: the campaign's **{!! $ui('Documents') !!}** page, file upload.
- Formats `pdf`, `jpg`, `jpeg`, `png`, `gif`, `webp`; **50 MB** per file; **10 files** per upload.
- The document's **title** = **file name without extension**. So name each file exactly as it will be quoted in the scenes' `Documents` column.
- Visibility (public or GM), storage (campaign, world or game) and tags are chosen **for the whole upload**: sort the files into folders that share these three settings (`4-documents-gm/`, `4-documents-players/`, `4-clues/`…).
- A clue to hand out during play is in the **GM** zone; the GM then gives it to the characters ({!! $ui('Donner') !!} / {!! $ui('Révéler') !!}).

### 6. Updating a campaign already imported

Reduced files, with the update option ticked:
- entries: `Nom;Type;Description;Notes MJ` (only these columns are replaced);
- scenes: `Scénario;Scène;Description`.

The preview must announce **0 creations** and as many updates as rows; otherwise a name does not match.

## Appendix B: example at the right level of detail (invented content)

The example is written in English; write yours in {!! $language !!}. The headers, the closed values (`oui`, `Jouée`…) and the `### {!! $planned !!}` / `### {!! $happened !!}` headings stay exactly as shown.

NPC entries:

```csv
Nom;Type;Résumé;Description;Notes MJ;Vigour;Mind;Hit points;Skills;Weapons
Master Aldric Fenwick;Personnage;"Keeper of the Mistvale lighthouse, gruff and protective, who has not left his tower since last year's storm.";"A man of about sixty, short grey beard, hands covered in old burns. He speaks little, stares at the sea while answering and refuses any visit after sunset.

In the village, people say he lost his son in the storm and that he ""talks to the lantern"". He pays for his supplies with old coins nobody recognises.";"**What he hides**: the lighthouse lantern holds the spirit of his son, Tobin, drowned in the storm. Aldric feeds it with the whale oil sold to him by [[Widow Orsel]], at the price of memories he gives up one by one (he no longer remembers his wife's face).

**What he wants**: to keep Tobin ""alive"" at all costs. He lies about everything to do with the lantern, but never lies about the sea.

**What he knows**: the safe passes between the reefs (clue needed for the scene ""The Teeth pass""), the names of the three missing sailors.

**How he reacts**: threatened, he barricades himself in and puts out the light, which triggers ""The shipwreck"". If someone speaks gently to him about his son (hard Mind test), he confesses everything and asks for help.

**Stats**: Vigour 3, Mind 4, 12 HP. Iron-shod staff (1D6).

**Appears in**: The dark lighthouse, The Teeth pass.";3;4;12 / 12;"Navigation 70 %, Weather 60 %, Intimidate 40 %";"Iron-shod staff 50 %, damage 1D6"
Widow Orsel;Personnage;Oil and salt merchant at the harbour, friendly, who never seems to age.;"A slight woman in a blue shawl, always smiling, who keeps the stall at the harbour entrance. She knows the first name of every child in the village.";"**True identity**: a tide witch who buys memories. She cannot act against someone who has sold her nothing.

**What she wants**: Aldric's last memory, the one of his son, which would make her mistress of the lantern.

**Stats**: Vigour 2, Mind 6, 10 HP. Spell *Mist of oblivion* (see [[Mistvale Lantern]]).

**Appears in**: Arrival at the harbour, The shipwreck.";2;6;10 / 10;"Bargain 80 %, Psychology 65 %, Occult 75 %";
```

Scenes (one played as planned, one played that departed from the book):

```csv
Scénario;Résumé du scénario;Chapitre;Scène;Description;Statut;Fiches;Documents;Règles
1. The Mistvale Lantern;"The Mistvale lighthouse goes dark every other night, and fishermen disappear. The characters discover that the keeper holds his drowned son's spirit in the lantern, and that a tide witch wants to seize it.";Act 1: The harbour;Arrival at the harbour;"### {!! $planned !!}

**Stake**: show that the village fears the lighthouse and draw attention to Widow Orsel, without pointing at her.

**Situation**: the characters arrive by the evening coach. The harbour is deserted, the boats pulled up on the shore. Only [[Widow Orsel]]'s stall is lit.

**What people say**:
- the innkeeper: three sailors missing in a month, always on nights when the lighthouse was dark;
- the children: ""old Fenwick talks to his lamp"";
- Widow Orsel, very friendly, offers oil ""for the road"" and asks each of them for their finest memory, ""out of curiosity"".

**Clues**:
- *Listen* (easy): at nightfall, a child's voice seems to come from the lighthouse;
- *Psychology* (hard) on the Widow: her smile never reaches her eyes;
- *Spot hidden*: barrels marked with her seal leave for the lighthouse.

**To hand out**: *Player map - Mistvale*.

**If the players sell a memory**: apply the *Sold memories* rule; the Widow can now act against them (""The shipwreck"").

**Transitions**: to ""The dark lighthouse"" (by night) or a daytime visit to [[Master Aldric Fenwick]], who sends them away.

The Mistvale Lantern p. 3-4.";Jouée;Widow Orsel | Master Aldric Fenwick;Player map - Mistvale;Sold memories
1. The Mistvale Lantern;;Act 1: The harbour;The dark lighthouse;"### {!! $planned !!}

**Stake**: get into the Lantern and discover that the flame is Tobin's spirit.

**Situation**: every other night, the light goes out at midnight. Aldric then stands at the top, motionless, whispering.

**Getting in**: barred door (hard Vigour test to break it, noise that alerts Aldric) or first-floor window by the outside stairs (Climb).

**In the tower**: see [[Mistvale Lantern]] for the floors and clues. Tobin's notebook is the key piece: hand it out (*Clue - Letter from widow Orsel* is slipped inside).

**Aldric**: surprised, he threatens but only strikes when cornered. Calmed down, he confesses (see his entry).

**Outcomes**:
- Aldric confesses: he asks them to stop the Widow from taking his last memory; go to act 2;
- Aldric is hurt or humiliated: he puts out the light for good, a boat breaks on the reefs the following night.

**Reward**: saving a fisherman that night, +1 inspiration point.

The Mistvale Lantern p. 5-6.

### {!! $happened !!} (session 2)

The players did not force the door: Lise sold the Widow the memory of her first hunt to get the key. Aldric found them at the top and knocked the lantern over; Tobin appeared as a green mist and saved Bran from the fall. The notebook was not found (it is still in the bedroom).";Jouée;Master Aldric Fenwick | Mistvale Lantern;Clue - Letter from widow Orsel;Sold memories
```
@if ($archive)

## Appendix C: campaign archive format

{!! $archiveFormat !!}
@endif
