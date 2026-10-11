# Archive de campagne SagaWyn (.zip)

À utiliser quand on peut exécuter du code (Claude Code, outil d'analyse) et qu'on veut **tout en un seul import** : portraits sur les fiches, tags, relations, documents liés aux scènes, statuts, secrets. Dans SagaWyn : **Mes campagnes → Importer une campagne**, sans dézipper. L'import crée un **nouveau jeu, un nouveau monde et une nouvelle campagne** dont l'utilisateur devient MJ.

Taille : limitée par le serveur ; au-delà, garder les images les plus utiles ou passer par l'import CSV et l'envoi de documents.

La référence exacte est une archive exportée depuis SagaWyn (page de la campagne → « Exporter la campagne »). En cas de doute, exporter une petite campagne (« Exporter la campagne ») et copier sa structure.

## Contenu du zip

```
campagne.json          le manifeste (≤ 50 Mo)
fichiers/1.jpg         images et PDF, référencés par leur chemin dans le manifeste
fichiers/2.pdf
…
```

Stocker les images sans recompression (`ZIP_STORED`), le JSON compressé. Écrire le JSON avec `ensure_ascii=False`.

## campagne.json

Tous les `id` sont des identifiants **locaux à l'archive** (1, 2, 3…), recalculés à l'import.

```json
{
  "format": "codexflow-campaign",
  "version": 1,
    "game_system": {"name": "…", "description": "…", "image": "fichiers/1.jpg"},
  "world": {"name": "…", "description": "…", "image": null},
  "campaign": {"name": "…", "description": "…", "table_theme": "parchemin",
               "exchanges_need_approval": true, "players_edit_profile": false, "disabled_features": []},
  "entity_types": [{"id": 1, "key": "character", "name": "Personnage"}, {"id": 2, "key": "place", "name": "Lieu"},
                   {"id": 3, "key": "organization", "name": "Organisation"}, {"id": 4, "key": "item", "name": "Objet"},
                   {"id": 5, "key": "creature", "name": "Créature"}, {"id": 7, "key": null, "name": "Sort"}],
  "tags": [{"id": 1, "name": "Indice", "color": "amber"}],
  "field_definitions": [{"id": 1, "entity_type_id": 1, "entity_type_ids": [1], "group": "Caractéristiques",
                         "name": "FOR", "type": "number", "options": null, "zone": "public", "position": 1,
                         "player_editable": false}],
  "entities": [{"id": 1, "in_world": false, "entity_type_id": 1, "name": "…", "summary": "≤ 500 car.",
                "description": "public", "gm_notes": "MJ", "field_values": {"1": 55},
                "image": "fichiers/3.jpg", "tags": [1], "attachments": []}],
  "relations": [{"from": 1, "to": 2, "in_world": false, "label": "employeur de", "reverse_label": "employé de", "zone": "gm"}],
  "entity_states": [{"entity_id": 1, "status": "Mort", "gm_notes": null, "overrides": {}}],
  "documents": [{"id": 1, "scope": "campaign", "title": "Indice - Lettre de Warner", "description": "…", "zone": "gm",
                 "original_name": "Indice - Lettre de Warner.jpg", "file": "fichiers/4.jpg", "tags": [1],
                 "entities": [1], "rules": []}],
  "audio_tracks": [],
  "rules": [{"id": 1, "in_campaign": false, "title": "…", "category": "…", "summary": "…", "procedure": "…",
             "gm_notes": "", "source": "Livre p. 42", "origin": "reference", "status": "available", "zone": "public", "tags": []}],
  "scenarios": [{"name": "1. …", "summary": "…", "scenes": [
      {"id": 1, "chapter": "…", "name": "…", "description": "### Ce qui est prévu\n\n…", "status": "planned",
       "tags": [], "entities": [{"id": 1, "note": null}], "documents": [1], "rules": [], "audio_tracks": []}]}],
  "pins": [1], "to_play": [], "secrets": [], "maps": [], "timeline": [],
  "table": {"player_sheets": true, "characters": [], "grants": [], "sessions": [], "session_notes": [],
            "character_notes": [], "messages": [], "journal": []}
}
```

Valeurs fermées :
- `type` de champ : `text`, `long_text`, `number`, `boolean`, `date`, `select`, `counter` (valeur `{"value": 11, "max": 11}`), `link`, `file`, `entity`.
- `zone` : `public` ou `gm`. `scope` de document : `campaign`, `world` ou `game_system`.
- `status` de scène : `planned`, `available`, `in_progress`, `played`, `skipped`.
- `origin` de règle : `reference`, `house`, `test` ; `status` : `available`, `to_test`, `testing`, `adopted`, `to_change`, `rejected`.
- `field_values` : clé = `id` du champ **en texte**.
- `in_world: true` : la fiche (ou la relation) appartient au monde et sert aux autres campagnes de ce monde.

**Liens dans l'archive** : `[[Libellé|id]]` où `id` est l'identifiant local de la fiche. Écrire les textes avec `[[Nom exact]]`, puis les convertir à la fin, en signalant tout nom inconnu.

Après génération : relire le zip (`zipfile`, `json.loads`), compter fiches/scènes/documents, vérifier que chaque `fichiers/…` cité existe dans le zip.
