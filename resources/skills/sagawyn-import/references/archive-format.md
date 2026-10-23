# SagaWyn campaign archive (.zip)

Use it when you can run code (Claude Code, analysis tool) and want **everything in a single import**: portraits on entries, tags, relations, documents linked to scenes, statuses, secrets. In SagaWyn: **My campaigns → Import a campaign**, without unzipping. The import creates a **new game, a new world and a new campaign**, and the user becomes their GM.

Size: limited by the server; beyond it, keep the most useful images or switch to the CSV import and upload the documents separately.

The exact reference is an archive exported from SagaWyn (campaign page → "Export the campaign"). When in doubt, export a small campaign and copy its structure.

The JSON keys and closed values below are fixed identifiers: keep them exactly as written, whatever language the content is in. The names (`name`, `title`, `description`…) are content: write them in the requested language.

## Zip content

```
campagne.json          the manifest (≤ 50 MB)
fichiers/1.jpg         images and PDFs, referenced by their path in the manifest
fichiers/2.pdf
…
```

Store images without recompression (`ZIP_STORED`), the JSON compressed. Write the JSON with `ensure_ascii=False`.

## campagne.json

Every `id` is **local to the archive** (1, 2, 3…) and is recomputed on import.

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
                   {"id": 5, "key": "creature", "name": "Créature"}, {"id": 7, "key": null, "name": "Spell"}],
  "tags": [{"id": 1, "name": "Clue", "color": "amber"}],
  "field_definitions": [{"id": 1, "entity_type_id": 1, "entity_type_ids": [1], "group": "Characteristics",
                         "name": "STR", "type": "number", "options": null, "zone": "public", "position": 1,
                         "player_editable": false}],
  "entities": [{"id": 1, "in_world": false, "entity_type_id": 1, "name": "…", "summary": "≤ 500 chars",
                "description": "public", "gm_notes": "GM", "field_values": {"1": 55},
                "image": "fichiers/3.jpg", "tags": [1], "attachments": []}],
  "relations": [{"from": 1, "to": 2, "in_world": false, "label": "employer of", "reverse_label": "employed by", "zone": "gm"}],
  "entity_states": [{"entity_id": 1, "status": "Dead", "gm_notes": null, "overrides": {}}],
  "documents": [{"id": 1, "scope": "campaign", "title": "Clue - Letter from widow Orsel", "description": "…", "zone": "gm",
                 "original_name": "Clue - Letter from widow Orsel.jpg", "file": "fichiers/4.jpg", "tags": [1],
                 "entities": [1], "rules": []}],
  "audio_tracks": [],
  "rules": [{"id": 1, "in_campaign": false, "title": "…", "category": "…", "summary": "…", "procedure": "…",
             "gm_notes": "", "source": "Book p. 42", "origin": "reference", "status": "available", "zone": "public", "tags": []}],
  "scenarios": [{"name": "1. …", "summary": "…", "scenes": [
      {"id": 1, "chapter": "…", "name": "…", "description": "### What’s planned\n\n…", "status": "planned",
       "tags": [], "entities": [{"id": 1, "note": null}], "documents": [1], "rules": [], "audio_tracks": []}]}],
  "pins": [1], "to_play": [], "secrets": [], "maps": [], "timeline": [],
  "table": {"player_sheets": true, "characters": [], "grants": [], "sessions": [], "session_notes": [],
            "character_notes": [], "messages": [], "journal": []}
}
```

Closed values:
- field `type`: `text`, `long_text`, `number`, `boolean`, `date`, `select`, `counter` (value `{"value": 11, "max": 11}`), `link`, `file`, `entity`.
- `zone`: `public` or `gm`. Document `scope`: `campaign`, `world` or `game_system`.
- scene `status`: `planned`, `available`, `in_progress`, `played`, `skipped`.
- rule `origin`: `reference`, `house`, `test`; `status`: `available`, `to_test`, `testing`, `adopted`, `to_change`, `rejected`.
- `field_values`: key = the field's `id` **as a string**.
- `in_world: true`: the entry (or relation) belongs to the world and is shared with the other campaigns of that world.
- The five base entry types keep their `key`; their `name` may stay as shown (SagaWyn displays them in the GM's language).

**Links in the archive**: `[[Label|id]]` where `id` is the entry's local id. Write the texts with `[[Exact name]]`, then convert them at the end, reporting any unknown name.

After generating: read the zip back (`zipfile`, `json.loads`), count entries/scenes/documents, check that every `fichiers/…` it cites exists in the zip.
