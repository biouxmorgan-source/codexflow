<?php

// What's new in each version, from newest to oldest. Shown in
// "What's new" (once after each update) and on the page of the same name.
return [

    '0.86.0' => [
        'date' => '2026-10-20',
        'title' => 'Edit a document without deleting it',
        'items' => [
            'On a document’s page, “Replace the file” puts a new version in place of the old one: the document keeps its title, settings and links, and the new version shows up at once in scenes, sheets, rules, maps, clues and with the characters who received it.',
            'An image used as a map is replaced by another image; tokens, grid and points stay in place.',
        ],
    ],

    '0.85.0' => [
        'date' => '2026-10-20',
        'title' => 'Roll dice from the sheet',
        'items' => [
            'On a character sheet, a small die next to each number and counter rolls the dice with that value: “Roll under… (threshold)” or “Add… to the roll (bonus)”. The dice tray opens ready to roll, and the result goes to the table as usual.',
            'On your own character’s sheet, the value stays linked to the sheet: if it changes, the saved roll follows. The GM can also roll from a player’s sheet.',
            '“My sheets” has its own dice: the same small die next to numeric values, and a “Dice” button at the bottom of the sheet. The result shows on the sheet, visible to you only.',
        ],
    ],

    '0.84.2' => [
        'date' => '2026-10-20',
        'title' => 'New maps for the demo',
        'items' => [
            'The demo "The Oath of Pierrecendre" has new illustrated maps: the coastal village for the campaign map, the lighthouse’s underground quay for the sub-map. Load it again from "My campaigns" to see them.',
        ],
    ],

    '0.84.1' => [
        'date' => '2026-10-19',
        'title' => 'More room in My sheets',
        'items' => [
            'A sheet now holds up to 200 blocks, 500 fields and 30 tabs. “+ New note” no longer leads to an error page: at the limit, a message explains it.',
        ],
    ],

    '0.84.0' => [
        'date' => '2026-10-19',
        'title' => 'Tabs in My sheets',
        'items' => [
            'In “Edit”, “+ New tab” splits a sheet into tabs you name (Character, Spells, Inventory…), reorder and remove.',
            'Each block picks its tab, and each tab places its blocks on its own three columns. An existing sheet keeps all its blocks in a first tab.',
        ],
    ],

    '0.83.0' => [
        'date' => '2026-10-19',
        'title' => 'Several notes and three columns in My sheets',
        'items' => [
            '“+ New note” adds a note block: make as many as you like (debts, rumours, contacts…). Your previous notes become a first “Notes” block.',
            'Blocks can now be placed on three columns, notes included, with “Arrange blocks”.',
        ],
    ],

    '0.82.0' => [
        'date' => '2026-10-19',
        'title' => 'An up-to-date demo',
        'items' => [
            'The demo campaign "The Oath of Pierrecendre" now shows the latest features: on the campaign map, the lighthouse leads to a sub-map, its cellar, ready for a fight with tokens, two area templates and an initiative order. Each point links several sheets and several scenes.',
            'Table dice: prepared rolls that read the characteristic from the sheet, a named bonus, and the rolls of session 1, including a GM request.',
            'Also: a sound from "My sounds", a scene "Played as planned" and another with "What happened", a "Lie" clue, a house sheet and a house rule with their official version, and players who complete their own portrait.',
            'Load the demo again from "My campaigns" to discover it: a copy you already loaded stays as it is.',
        ],
    ],

    '0.81.0' => [
        'date' => '2026-10-19',
        'title' => 'Campaign map: zoom, table and richer places',
        'items' => [
            'The campaign map zooms (wheel, + and −, pinch on a tablet) and pans by dragging. Tip: a 4K image (3840 × 2160) stays sharp.',
            'When the map is on the table screen, the sub-map opened from a point replaces it there, and the map above comes back when you go back up. In the sub-map, “This place” keeps the point’s scenes and sheets one click away.',
            'A point can now link to several scenes and several sheets: the place, its NPCs, every scene played there.',
        ],
    ],

    '0.80.0' => [
        'date' => '2026-10-19',
        'title' => 'Blocks, notes and checkboxes in My sheets',
        'items' => [
            'In “My sheets”, fields are arranged in blocks you name: Attributes, Skills, Equipment, Spells…',
            '“Arrange blocks”: drag a block by its handle, or use the arrows, to place it where you like, on one or two columns.',
            'Each field can carry a note and a checkbox, to tick during play: a successful skill, a prepared spell.',
        ],
    ],

    '0.79.0' => [
        'date' => '2026-10-19',
        'title' => 'Demo library',
        'items' => [
            'New "Demo library", from "My campaigns": ready-to-play campaigns (a setting, a short scenario, pregenerated characters) that you load as your own copy in one click.',
            'Each demo from an author or publisher keeps a "Buy the game" link, in the library and at the top of your copy.',
            'The Premium price now shows on the subscription button in Preferences: €4.99 per month.',
        ],
    ],

    '0.78.0' => [
        'date' => '2026-10-18',
        'title' => 'What happened, and the adventure book',
        'items' => [
            'Each scene now has two parts: "What\'s planned" and "What happened". If the table followed the plan, just tick "Played as planned".',
            'New "Adventure book" in the campaign: the played scenes, scenario by scenario, then the session summaries, to read, print or save as PDF.',
            'A written version can be added, by you or by the AI of your choice (text to copy, or your own API key, at your expense). The book stays GM-only.',
        ],
    ],

    '0.77.0' => [
        'date' => '2026-10-18',
        'title' => 'Arrange your screens your way',
        'items' => [
            'New “Arrange screen” button on the campaign page and in Session mode: drag each block by its ⠿ handle, with a mouse or a finger, or use the arrows.',
            'In Session mode, a block can move from one column to the other: music at the top, notes on the right, as you prefer.',
            '“Hide” removes a block you don’t use, “Show” brings it back, “Default layout” puts everything back. This layout is yours alone: players and co-GMs keep their own.',
        ],
    ],

    '0.76.0' => [
        'date' => '2026-10-18',
        'title' => 'Your sheet, even without a connection',
        'items' => [
            'Offline, your character\'s sheet stays usable: adjust your counters with − and +, edit the fields the GM lets you change and write notes.',
            'Everything is kept on the device and sent when the connection comes back, field by field: a counter receives what you removed or added, and whatever the GM changed in the meantime (a revealed secret, an edited field) is kept.',
            'After sending, a summary says what was updated and points out any field the GM changed at the same time as you.',
        ],
    ],

    '0.75.0' => [
        'date' => '2026-10-18',
        'title' => 'The big campaign map',
        'items' => [
            'New “Campaign map”: choose one map as the overview and place points of interest on it, a city, a dungeon, the place of a scene.',
            'A point can open a sub-map in one click, which has its own points; the breadcrumb takes you back up to the overview.',
            'Linked to a scene, points show what has been played, is in progress or is still to come; linked to a sheet, they open it. Everything stays with the GM, except the names of points marked “Visible at the table”, shown on the table screen.',
        ],
    ],

    '0.74.0' => [
        'date' => '2026-10-18',
        'title' => 'Your characters played elsewhere',
        'items' => [
            'New “My sheets” page: store the characters you play outside SagaWyn, at a friend’s, at a club or elsewhere. Only you can see them.',
            'Each character has its PDF sheet, a portrait, free fields (texts and counters to adjust with − and + during play) and your notes, with the game and table names to find them again.',
        ],
    ],

    '0.73.0' => [
        'date' => '2026-10-18',
        'title' => 'A sound library for all your campaigns',
        'items' => [
            'New “My sounds” page: your music and ambiences, stored once in your account, then added to the campaigns you run. The file is not copied and only counts once against your storage.',
            'On a campaign’s Sounds page: “From my library” to add your sounds, “Move to my library” to reuse a sound elsewhere, and a box to store a new upload straight in the library.',
            'Moving a sound already uploaded in another campaign into the library merges both copies: scenes keep their music and the space is freed.',
        ],
    ],

    '0.72.0' => [
        'date' => '2026-10-18',
        'title' => 'The how-to everywhere',
        'items' => [
            'The “?” button now appears on sounds, the relationship graph, the timeline, player feedback, the AI assistant, the log and the character sheet.',
            'On the graph, the timeline and the character sheet, players read a how-to written for them.',
        ],
    ],

    '0.71.1' => [
        'date' => '2026-10-18',
        'title' => 'Remove or add several points at once',
        'items' => [
            'Under each counter the player may change (HP, ammo, money…), an “Amount” box: type 8 then −, and the counter loses 8 at once. It defaults to 1.',
        ],
    ],

    '0.71.0' => [
        'date' => '2026-10-18',
        'title' => 'A how-to on more screens',
        'items' => [
            'The “?” button now also appears on the sheet (GM side), Session mode, scenarios and scenes, file import and campaign members.',
            'The Help page now explains raw rolls and bonuses read from the character’s sheet.',
        ],
    ],

    '0.70.0' => [
        'date' => '2026-10-18',
        'title' => 'A how-to on the trickier screens',
        'items' => [
            'A “?” button next to the title of screens that don’t explain themselves opens a few-line how-to: sheet types, game fields, tags, dice, maps, table screen remote, clues and reveal history.',
            'Each how-to links to the matching question on the Help page, which opens right on it.',
        ],
    ],

    '0.69.0' => [
        'date' => '2026-10-18',
        'title' => 'Bonus and threshold read from the sheet',
        'items' => [
            'In the dice tray, the bonus can take one or more values from the character’s sheet (attribute, skill, any number or counter field in the public zone), whole or as tens, half or fifth, on top of the free bonus. The threshold can also be read from the sheet. Values are read at the moment of the roll, from the sheet of each character who rolls.',
            'Several named bonuses (Weapon +2, Wound −1…) add up in the same roll, alongside sheet values.',
            'The ready-made examples are gone from the tray: it keeps your saved rolls.',
            'A saved roll is deleted in one tap: the × on its chip, or “Delete” once loaded.',
        ],
    ],

    '0.68.0' => [
        'date' => '2026-10-17',
        'title' => 'Raw roll',
        'items' => [
            'New “Raw” reading, selected by default: pick the dice types and counts (1d20 + 2d6…), roll, and each die shows as it falls, with no total, no threshold and no other rule.',
        ],
    ],

    '0.67.0' => [
        'date' => '2026-10-17',
        'title' => 'Your content, your rights',
        'items' => [
            'The “Privacy and legal notice” page now states that SagaWyn does not own what you put on it: every user keeps ownership of their content and is responsible for what they publish.',
            'New sections: “Importing and structuring content”, “Artificial intelligence services”, “Report illegal content”, “Availability and backups” and “Games and trademarks”. The import pages link to them.',
        ],
    ],

    '0.66.0' => [
        'date' => '2026-10-17',
        'title' => 'Mixed dice and your own saved rolls',
        'items' => [
            'One roll can mix several dice types: tap a d20 then two d6, and the tray rolls 1d20 + 2d6. Each type has its own count, which you can change or remove.',
            'The threshold applies either to the total (“Threshold on the total”, reached or not exceeded) or to each die (“Threshold per die”: every die at 4 or more is a success, for example).',
            'Everyone, player or GM, saves their own rolls in “My rolls”, reloads them in one tap and updates them as their character evolves. The GM also keeps the “Campaign rolls” for the whole table.',
        ],
    ],

    '0.65.1' => [
        'date' => '2026-10-17',
        'title' => 'Access checked everywhere',
        'items' => [
            'Typing an address by hand never opens a campaign, sheet or file you are not allowed to see. An account that is not a member now gets “Page not found”, just like for an address that does not exist: trying numbers does not even reveal that a campaign exists.',
        ],
    ],

    '0.65.0' => [
        'date' => '2026-10-17',
        'title' => 'Dice at the table',
        'items' => [
            'New “Dice” button at the bottom of every campaign page: the number and type of dice (4 to 100 sides, or other), then how to read them: add up, reach a threshold, stay under a threshold, count successes, keep the best or the lowest, read as digits. With a bonus, and dice that roll again on the max (+) or on a 1 (−).',
            'The GM asks for a roll from the whole table or from some characters, in plain view or discreetly. A window opens for the players concerned; without a threshold, each enters their own and adds their bonus, and a hidden threshold stays with the GM, who sees who rolled what.',
            'Rolls at the table tumble for a few seconds on the table screen; a discreet roll is shown to the GM only. The GM saves their usual rolls for the whole table, and dice can be switched off in “Campaign features”.',
        ],
    ],

    '0.64.1' => [
        'date' => '2026-10-17',
        'title' => 'Templates measured like the ruler',
        'items' => [
            'Templates are set in the same unit as the ruler (metres, feet…): the “Real size of a square”, under Grid, calibrates both, whatever the size of the squares on screen.',
            'A clearer “Delete” button on each template, and “Delete all templates” once the spell is over.',
            'Each “Join remotely” link has its QR code, to show so players can scan it with their phone.',
        ],
    ],

    '0.64.0' => [
        'date' => '2026-10-17',
        'title' => 'Area templates on the map',
        'items' => [
            'New templates on the map page: circle, square, rectangle, cone and line, sized in squares (or in the scale\'s unit). Drag them on the map, turn them, hide them, and the list of tokens caught in the area shows up straight away.',
            'Visible templates appear on the table screen, under the tokens.',
        ],
    ],

    '0.63.0' => [
        'date' => '2026-10-17',
        'title' => 'Shared battle map',
        'items' => [
            'On the shared table screen, each player moves their character\'s token. In the map, “Moved by” hands another token to a player: follower, familiar, controlled enemy…',
            'New initiative order, from the remote or the map: PCs and enemies with their value, current turn, rounds. The GM shows it over the screen whenever they like; hidden lines stay with them, and an “Also present” row shows NPCs or objects without initiative.',
            '“Join remotely” in the remote: the screen link for players, and a guest link without an account, read-only and revocable, for someone who only wants to watch.',
        ],
    ],

    '0.62.0' => [
        'date' => '2026-10-13',
        'title' => 'The community on Discord',
        'items' => [
            'SagaWyn has its own Discord server: a link at the bottom of every page and on the home page to ask questions, suggest ideas and meet other game masters.',
        ],
    ],

    '0.61.0' => [
        'date' => '2026-10-13',
        'title' => 'Demo mode before the Kickstarter',
        'items' => [
            'New demo mode, switched on in Administration › Plans: every new account gets the Demo plan, with its own copy of the demo campaign and a banner announcing the upcoming crowdfunding campaign.',
            'A demo account’s demo campaign is locked: everything can be explored, but only the session, notes, map tokens and table screen can be used. The account can also create a small campaign of its own (50 MB, 3 members, no AI or export).',
            'An invitation from an administrator opens a normal account. In “Accounts”, “Unlock” moves a demo account to the free plan and starts its trial, which the demo doesn’t use up.',
        ],
    ],

    '0.60.0' => [
        'date' => '2026-10-13',
        'title' => 'House content',
        'items' => [
            'New “House content” checkbox on sheets (NPCs, spells, items, places…): unticked, the sheet is official. A “House” badge marks it in the list and on the sheet, and an Official / House filter sorts the universe.',
            'For a house sheet or a rule whose origin is “House”, the official version it replaces can be noted alongside (for example initiative on a d10 instead of a d20): it stays in the GM zone, folded under the sheet or rule.',
            'The rules list can be filtered by origin. Campaign export and import keep house content and the official version.',
        ],
    ],

    '0.59.1' => [
        'date' => '2026-10-13',
        'title' => 'A more accurate go-live check',
        'items' => [
            'Administration: `sagawyn:check` no longer wrongly reports debug logging when LOG_LEVEL is warning.',
        ],
    ],

    '0.59.0' => [
        'date' => '2026-10-13',
        'title' => 'The Autistic Intelligence logo',
        'items' => [
            'The logo of Autistic Intelligence, SagaWyn’s publisher, now appears at the bottom of pages, on the sign-in screen and in the legal notice. It follows the light or dark theme.',
        ],
    ],

    '0.58.0' => [
        'date' => '2026-10-12',
        'title' => 'A clue can be a lie',
        'items' => [
            'Clues: new “Lie” nature, alongside Rumour, Clue and Truth, with its filter. Like the other natures, only the GM sees it: a player it is revealed to only reads the clue.',
        ],
    ],

    '0.57.0' => [
        'date' => '2026-10-12',
        'title' => 'Prepare a whole campaign with an AI',
        'items' => [
            'My campaigns → Import a campaign: new “Prepare the archive with an AI” link. The prompt (or the Claude skill) has your AI read the roleplaying game book and build a .zip archive: the game, the world, the campaign, the entries, the scenes and the documents are created in one go.',
        ],
    ],

    '0.56.1' => [
        'date' => '2026-10-11',
        'title' => 'Game and world pages readable on phones',
        'items' => [
            'On phones, a game or world image now sits above the name at full width: the name and description are no longer squeezed and the Edit button no longer covers them.',
        ],
    ],

    '0.56.0' => [
        'date' => '2026-10-11',
        'title' => 'Secrets become clues',
        'items' => [
            'The campaign’s “secrets” are now called “clues”: Clues page, panel on entries, scenes and documents, Session mode, search, reveals and help. Nothing changes in your data.',
        ],
    ],

    '0.54.0' => [
        'date' => '2026-10-11',
        'title' => 'Players edit their character',
        'items' => [
            'Fields page: “Editable by the player” can be ticked for a whole group (characteristics, skills…) or for every public field in one click.',
            'New option on the Players page: the GM can let each player edit their character’s portrait, summary and description. The GM zone stays hidden.',
        ],
    ],

    '0.51.0' => [
        'date' => '2026-10-11',
        'title' => 'Founder badge and gift codes',
        'items' => [
            'Kickstarter backers can be marked Founder in the admin console: a ★ Founder badge shown in preferences, the member list and the character sheet.',
            'Founders get extra storage on top of their plan, free or premium (500 MB by default, set in “Plans”).',
            'New “My rewards” page (from preferences): your Kickstarter reward, Premium gift codes to give by link, with the name of whoever used each one, and a box to use a code you received. The months are added after your current Premium.',
        ],
    ],

    '0.50.1' => [
        'date' => '2026-10-11',
        'title' => 'Health: role filter',
        'items' => [
            'In the Health tab, a filter by account role (without administrators, administrators, premium, trial, free).',
            'The demonstration campaign is no longer counted in the statistics.',
        ],
    ],

    '0.50.0' => [
        'date' => '2026-10-10',
        'title' => 'Platform health',
        'items' => [
            'New Health tab in the admin console: people logged in and number of logins per day, week or month, players per campaign and per GM, storage used (files, database, disk) and server response time hour by hour.',
            'The console warns when the server slows down: more than 5 % of requests over one second in 24 hours.',
        ],
    ],

    '0.49.1' => [
        'date' => '2026-10-10',
        'title' => 'More complete help',
        'items' => [
            'Help answers 19 new questions: game, world and campaign, the demo, search and links, tags, scenarios, Session mode, game fields, rules, duplication, exchanges, a player leaving, the journal, the end of Premium, and for players: sheet, notes, messages and feedback.',
        ],
    ],

    '0.49.0' => [
        'date' => '2026-10-10',
        'title' => 'The demo shows all of SagaWyn',
        'items' => [
            'The demo campaign now uses every feature: a player character made from a pregen, with what it has received (sheet, document, rule, secret, items) that the GM can “View as”; session notes; a “To play” list; pinned sheets; reference, document and two-type fields; a GM attachment; secrets of every kind; coloured tags; covers for the game and the world.',
            'Reloading the demo reuses the “Faction” sheet type already created instead of adding a second one.',
        ],
    ],

    '0.48.0' => [
        'date' => '2026-10-10',
        'title' => 'A fuller demonstration',
        'items' => [
            'The demonstration campaign “The Oath of Pierrecendre” now includes three original sound ambiences linked to scenes, a first session already played with its summary, and a feedback request to the players.',
            'Fix: the fade between two tracks no longer stops playback in some browsers.',
        ],
    ],

    '0.47.0' => [
        'date' => '2026-10-10',
        'title' => 'LoreMundi becomes SagaWyn',
        'items' => [
            'LoreMundi is now called SagaWyn, still published by Autistic Intelligence. Every world has a story.',
            'Your campaigns, accounts and archives stay the same: backups made with LoreMundi or CodexFlow still import. Downloaded files now start with “sagawyn-”.',
        ],
    ],

    '0.46.0' => [
        'date' => '2026-10-10',
        'title' => 'Player feedback',
        'items' => [
            'At the end of a session or campaign, ask your players for a rating from 1 to 5 stars, what they enjoyed and what could be better (“Player feedback” in the campaign, or from a session page).',
            'Anonymous or signed answers, as the GM chooses: players know it before answering. You see the average, the rating breakdown and every comment.',
        ],
    ],

    '0.45.0' => [
        'date' => '2026-10-10',
        'title' => 'Sound library',
        'items' => [
            'New “Sounds” page: upload your music and ambiences (mp3, ogg, m4a, wav, flac), sort them with tags and link them to scenes.',
            'In Session mode, the “Music” block offers the scene’s sounds first: “Here” plays them on your device, without stopping when you change pages; “Table” plays them on the table screen, with pause, loop and volume controlled from the session.',
        ],
    ],

    '0.44.0' => [
        'date' => '2026-10-09',
        'title' => 'Ready to go live',
        'items' => [
            'The administrator is warned in the console when the installation has a blocking issue (debugging on, e-mail not configured, unencrypted realtime…).',
            'The database and uploaded files are backed up every night on the server.',
        ],
    ],

    '0.43.0' => [
        'date' => '2026-10-09',
        'title' => 'Reliability',
        'items' => [
            'New automated tests check in a real browser the updates without realtime, “Remember me”, the “Skip to content” link, display on a phone with very large text, and a feature turned off by the GM while a page is open.',
        ],
    ],

    '0.42.0' => [
        'date' => '2026-10-09',
        'title' => 'Formatted descriptions',
        'items' => [
            'Game, world, scenario and document descriptions have the same editor as entries: bold, italics, subheadings, lists, quotes, and [[ ]] links to entries within a campaign.',
        ],
    ],

    '0.41.0' => [
        'date' => '2026-10-09',
        'title' => 'Shared fields',
        'items' => [
            'A field can concern several entry types at once, for example hit points for characters and creatures; game templates and field files keep this choice.',
        ],
    ],

    '0.40.0' => [
        'date' => '2026-10-09',
        'title' => 'App and security',
        'items' => [
            'The app installed on a phone has a description in your language and an icon fitted to Android’s round screens.',
            'Offline, pages stay readable and editing buttons are greyed out until the network returns.',
            'The browser now only accepts scripts, images and connections coming from LoreMundi itself.',
        ],
    ],

    '0.39.0' => [
        'date' => '2026-10-09',
        'title' => 'Account and administration',
        'items' => [
            'You can report a problem without an account, from the login and sign-up pages or the help, leaving an address for the reply.',
            'Preferences show the subscription start date, and that upgrading to Premium will open soon while payment isn\'t set up.',
            'Admin console: login days instead of logins, date of the last login, and a warning when push sending is impossible or fails.',
        ],
    ],

    '0.38.0' => [
        'date' => '2026-10-09',
        'title' => 'Search',
        'items' => [
            'Search also finds other French forms of a word: “lanternes” finds “lanterne”, “éteinte” finds “éteintes”.',
            'Search from the home page also looks in your worlds and games that aren\'t linked to any campaign.',
        ],
    ],

    '0.37.0' => [
        'date' => '2026-10-09',
        'title' => 'Players and roles',
        'items' => [
            'A revealed entry shows the player its public illustrations and files, and its public relations to entries they know.',
            'Players have a “Campaign feed” on their sheet: sessions, played events known to the table and group messages.',
            'Co-GMs see the game and world pages read-only, download the archive and the game template, and manage fields if the owner checks it in “Members”. A demoted co-GM doesn\'t keep the notifications received as GM.',
        ],
    ],

    '0.36.0' => [
        'date' => '2026-10-09',
        'title' => 'GM and session comfort',
        'items' => [
            'The pages of a PDF shown at the table can be turned from the remote, from the document page or with the arrows of the GM\'s screen, and players following the screen turn with it. The PDF viewer shows “page n / N” and lets you jump to a page.',
            'In Session mode, “Played event” adds the text you typed to the timeline, linked to the current session and scene.',
            'A character can be given back to any of its former players who returns to the campaign, who gets back their own private exchanges with the GM, without those of the players in between.',
            'An entry\'s status shows under its title; pregens (tag “pregen”) are offered first in “New character”; the counters of the Tags page list the tagged items; a demo loaded several times numbers its campaign, game and world; the spectator role states that it sees the table screen even when not shared.',
        ],
    ],

    '0.35.0' => [
        'date' => '2026-10-09',
        'title' => 'Fixes from the final acceptance test',
        'items' => [
            'A session page has a summary written by the GM, and shows the events played and everything revealed or given during the session.',
            'A sheet’s history now also keeps its relations, attached files, tags and status in the campaign. A world page shows its history, and a game page its sheet types.',
            'A feature switched off by the GM or by the plan is also off on pages left open, and Session mode opens when maps are switched off.',
            'Fixes: quantity announced in an exchange, a yes/no box never filled in, translated “not found” page, pages readable on phones with large text, logins counted once, help updated.',
        ],
    ],

    '0.34.0' => [
        'date' => '2026-10-08',
        'title' => 'Features per campaign',
        'items' => [
            'On the campaign page, the GM ticks the features their table needs: table screen, maps, exchanges between players, graph, timeline, AI assistant. An unticked feature disappears for everyone without deleting anything; it comes back as soon as it is ticked again.',
        ],
    ],

    '0.33.0' => [
        'date' => '2026-10-08',
        'title' => 'Finishing touches',
        'items' => [
            'Search shows the sheet field that holds the word found, with its name.',
            'Graph: overlapping names and labels are moved or hidden; hovering over an entry brings them back.',
            '“Reveal or give”: an “All active characters” box ticks the whole table at once.',
            'A player who was removed and invited again gets their former character back in one click, from the Characters page.',
            'Without real time (Reverb server missing or down), the bell, messages and sheets refresh every 30 seconds.',
        ],
    ],

    '0.32.0' => [
        'date' => '2026-10-08',
        'title' => 'PDFs and documents',
        'items' => [
            'PDFs open in a built-in viewer, the same on computer, tablet and phone, with zoom and download.',
            'On the table screen, a PDF is shown one page at a time, fitted to the screen; the arrow keys turn the pages.',
            'The character sheet keeps its original file name.',
            '“Used by” shows the scenario of each scene.',
            'Game and world pages can have an image.',
        ],
    ],

    '0.31.0' => [
        'date' => '2026-10-08',
        'title' => 'Premium features ✦',
        'items' => [
            'A small star ✦ marks Premium features. When a campaign owner’s plan does not include them, they stay visible, greyed out, with an explanation.',
            'When a trial or subscription ends, nothing is erased: campaigns, maps, messages and files remain; only ✦ features switch off.',
            'The administrator can offer a gift period (Christmas…) during which free accounts get every Premium feature.',
        ],
    ],

    '0.30.0' => [
        'date' => '2026-10-08',
        'title' => 'Writing and links',
        'items' => [
            '“Long text” fields and rules’ GM notes get the rich editor and [[ ]] links.',
            'On their sheet, the player sees long texts formatted, with links to the sheets their character knows.',
            'The quick session note suggests sheets as soon as you type “[[”.',
            'Copies are numbered (“copy 2”, “copy 3”) and an import never reuses the name of a game, world or campaign you already have.',
        ],
    ],

    '0.29.0' => [
        'date' => '2026-10-08',
        'title' => 'Account security',
        'items' => [
            'A new email address is only adopted after clicking the link it receives; the old address is then notified.',
            'Attempts on account forms are counted per form and per email address, and the waiting page says how many seconds to wait.',
            'Only LoreMundi’s own scripts can run in its pages.',
            'Account deletion now says that your messages are erased.',
        ],
    ],

    '0.28.0' => [
        'date' => '2026-10-08',
        'title' => 'Emails in LoreMundi colours',
        'items' => [
            'Emails (forgotten password, address change) carry the LoreMundi logo and colours.',
            'Each email is sent in the recipient’s language, even when the administrator sends it.',
        ],
    ],

    '0.27.0' => [
        'date' => '2026-10-08',
        'title' => 'A public showcase',
        'items' => [
            'A home page presents LoreMundi to visitors and search engines, in all 8 languages.',
            'Help can be read without an account and gains a “Your account” section: plans, email address, password, two-factor authentication, data.',
        ],
    ],

    '0.26.0' => [
        'date' => '2026-10-08',
        'title' => 'Fixes from the v0.25.0 acceptance test',
        'items' => [
            'A page left open checks your rights again on every action: a removed player or a demoted co-GM no longer receives anything new.',
            'A player who is given a character no longer reads the previous player’s private conversation with the GM.',
            'The demo campaign’s pre-generated characters are offered in “New character”.',
            'Two-factor authentication: recovery codes work on the sign-in screen, and the administrator can remove two-factor authentication from a locked-out account.',
            'New LoreMundi icons (browser tab, installed app, notifications).',
            'Shared table screen readable on phones; sheet and page headers fixed on phones and tablets, including with large text.',
            'Fixes: push notifications without errors, buttons of the “received” popup, sheet references after a rename, exchange quantity, map ruler, “Offline” banner, translated error messages, “Skip to content” link.',
        ],
    ],

    '0.25.0' => [
        'date' => '2026-10-08',
        'title' => 'Import a game book with an AI',
        'items' => [
            'In “Import”, “Prepare the files with an AI” gives you a prompt to paste into the AI of your choice with the PDF of a game or scenario: it prepares the import files (fields, sheets, rules, scenes) and a step-by-step guide. The prompt is also available as a Claude skill.',
        ],
    ],

    '0.24.0' => [
        'date' => '2026-10-08',
        'title' => 'Security and personal data',
        'items' => [
            '“My account”, in Preferences: change your name, your email address (the previous address is notified) and your password.',
            'Optional two-factor authentication, in Preferences: a code from an app on your phone, with recovery codes.',
            '“My data”, in Preferences: download what LoreMundi keeps about you, or delete your account.',
            'Passwords of at least 10 characters with letters and numbers; changing yours logs out your other devices. The admin console asks for the password again.',
            'New “Privacy and legal notice” page.',
            'A player removed from the campaign, or made a spectator, no longer plays their character and no longer receives anything revealed to it. Other permission checks were strengthened on the server.',
        ],
    ],

    '0.23.0' => [
        'date' => '2026-10-08',
        'title' => 'CodexFlow becomes LoreMundi',
        'items' => [
            'CodexFlow is now called LoreMundi, published by Autistic Intelligence. Every world has a story.',
            'Your campaigns, accounts and archives stay the same: backups made with CodexFlow still import. Downloaded files now start with “loremundi-”.',
        ],
    ],

    '0.22.0' => [
        'date' => '2026-10-08',
        'title' => 'Complete backup',
        'items' => [
            'The owner can download a complete backup: the campaign archive with the table (characters, what they received, sessions, notes, messages and log), without “Only me” notes or email addresses. On import, characters come back without a player, ready to be assigned.',
        ],
    ],

    '0.21.0' => [
        'date' => '2026-10-08',
        'title' => 'Search from the home page',
        'items' => [
            'Outside a campaign, the search bar looks through all your campaigns, their worlds and their games, each with your own rights: everything as GM, what your character knows as a player.',
        ],
    ],

    '0.20.0' => [
        'date' => '2026-10-08',
        'title' => 'Complete “Mentioned in”, duplication without statuses',
        'items' => [
            '“Mentioned in” also shows the timeline, the secrets and the fields of other sheets that mention the sheet.',
            'A duplicated campaign starts again from the original sheets: a “dead” or “prisoner” status is no longer copied, unless you tick the box to keep it.',
        ],
    ],

    '0.19.0' => [
        'date' => '2026-10-08',
        'title' => 'Secrets and new fields',
        'items' => [
            'Every secret has a kind (rumour, clue or truth) and a state worked out from who knows it: hidden, partial or revealed. Secrets can be filtered by either.',
            'Three new field types: web link, file (a campaign document) and reference to another sheet, which stays linked even if the sheet is renamed.',
            'A duplicated campaign keeps the links between its copied sheets.',
        ],
    ],

    '0.18.0' => [
        'date' => '2026-10-08',
        'title' => 'New character, My campaigns, game and world pages',
        'items' => [
            'When a player gets a new character, the GM ticks what passes from the previous one: knowledge, information, documents and rules are copied, items change hands.',
            'My campaigns: a “Resume” button, the date of the last session, and archived campaigns kept apart.',
            'Each game and each world has its own page: description, campaigns, rules, documents, fields or reusable sheets.',
        ],
    ],

    '0.17.0' => [
        'date' => '2026-10-08',
        'title' => 'Rich editor and player notes',
        'items' => [
            'Long texts (descriptions, GM notes, scenes, rules, timeline, player notes) get an editor with bold, italic, subheadings, lists and quotes; “[[” still suggests sheets to link.',
            'Players link their notes to the sheets their character knows, and only those.',
            'A session’s page also shows the notes players took during it, except the ones they keep to themselves.',
        ],
    ],

    '0.16.0' => [
        'date' => '2026-10-08',
        'title' => 'Premium subscription and free trial',
        'items' => [
            'Go Premium from “Preferences”: monthly or yearly payment secured by Stripe, with invoices and cancellation in the Stripe portal. Premium lasts until the end of the paid period.',
            'Free trial: six weeks with every feature, starting from your first campaign as GM. A player who is never a GM does not start it. The length is set in the admin console.',
            'A “Roadmap” tab in the admin console to keep the platform’s plans.',
        ],
    ],

    '0.15.0' => [
        'date' => '2026-10-08',
        'title' => 'Administration console and plans',
        'items' => [
            'An administration console: accounts with their plan, subscription dates, storage used, whether an AI key is set, campaigns and logins, with no personal data. The administrator sets each plan and can send a password reset link, without ever seeing the password.',
            'Three plans: administrator, premium and free. Storage, number of campaigns and the features of the free plan are set in the console; playing, being co-GM or spectator never counts.',
            'The backlog gathers reported problems, bugs and improvements, with status, priority and fix version; acceptance reports are kept there version after version. Your plan shows in “Preferences”.',
        ],
    ],

    '0.14.0' => [
        'date' => '2026-10-08',
        'title' => 'Acceptance follow-ups: exchanges approved by the GM',
        'items' => [
            'By default, the GM approves exchanges between players: the item or knowledge changes hands only once the exchange is accepted. A box in “Player characters” lets the GM allow them outright.',
            'Remote control: on the last item of the scene, “Next” becomes “Finish” and clears the screen.',
            'Clearer journal for validated items, clearer messages in “Report a problem”, and a single way of addressing you in each language.',
        ],
    ],

    '0.13.1' => [
        'date' => '2026-10-08',
        'title' => 'V1 acceptance fixes',
        'items' => [
            'The GM search also finds secrets, given information and items, players’ shared notes and scene tags.',
            'Duplicating a campaign copies its secrets and prepared timeline; a link to an entry opened during a session shows in a side panel, without leaving the session.',
            'Translated error pages, password email in your language, Session mode and Documents readable on phones, a menu for hidden links on small screens.',
            'A resting character and an item validated by the GM can no longer be changed by the player; the map’s temporary ruler clears by itself.',
        ],
    ],

    '0.13.0' => [
        'date' => '2026-10-08',
        'title' => 'Your own AI, no copy-paste',
        'items' => [
            'In “Preferences”, you can save an API key in your name with Claude (Anthropic), ChatGPT (OpenAI) or Le Chat (Mistral). The AI assistant then offers “Analyse directly”: suggestions arrive with no copy-paste.',
            'Calls are billed by the provider to your account. The key is encrypted, never shown again nor exported, and the copy-paste mode stays free.',
        ],
    ],

    '0.12.0' => [
        'date' => '2026-10-08',
        'title' => 'An AI assistant, no subscription',
        'items' => [
            'New “AI assistant” tool in the campaign: LoreMundi prepares a text with the session notes and the campaign context, to paste into the AI of your choice. Its answer, pasted back, becomes suggestions: summary, played events, relations, statuses, campaign notes, reveals.',
            'Each suggestion can be accepted, edited or rejected. Nothing changes in the campaign without you, and suggested relations or statuses stay specific to the campaign, without touching the shared world.',
        ],
    ],

    '0.11.1' => [
        'date' => '2026-10-08',
        'title' => 'The demonstration in your language',
        'items' => [
            'The demonstration campaign now exists in all eight interface languages. It loads in yours, or in the one chosen next to the button.',
        ],
    ],

    '0.11.0' => [
        'date' => '2026-10-08',
        'title' => 'A demonstration campaign',
        'items' => [
            'A demonstration campaign to load in one click from “My campaigns”: an invented game, “Brume & Serment”, and a complete three-session plot, with entries, portraits, relations, a map, secrets, rules, a timeline and pre-generated characters.',
            'The small icon buttons on the campaign page no longer move when hovered: the name appears in a tooltip, above everything else.',
            'Tags are entered the same way everywhere, and an entry offers “Add a tag” right under its title.',
        ],
    ],

    '0.10.0' => [
        'date' => '2026-10-07',
        'title' => 'A clearer campaign page',
        'items' => [
            'The campaign page tidied up: Session mode as a banner, four preparation areas, and the other tools as small icon buttons.',
            'Table screen ambience, chosen from the remote control: Night, Parchment, Slate or Grimoire.',
        ],
    ],

    '0.9.0' => [
        'date' => '2026-10-07',
        'title' => 'Taking your campaign with you',
        'items' => [
            'Export a whole campaign as a .zip archive: game, world, entries, scenarios, documents, maps, secrets, timeline and files.',
            'Import an archive from “My campaigns”: it recreates the campaign, for you or for another GM.',
            'Shareable game templates: entry types, fields, tags and rules, without any campaign content.',
        ],
    ],

    '0.8.0' => [
        'date' => '2026-10-07',
        'title' => 'The graph and the timeline',
        'items' => [
            'Relation graph: all linked entries, or the network around one entry, with a depth and a filter by type.',
            '“View as” in the graph: the network as a character knows it. Players reach it from their character.',
            'Timeline: world history, planned and played events, with free-form dates such as “Day 3”.',
            'A played event noted during the session is attached to the session and the current scene.',
        ],
    ],

    '0.7.0' => [
        'date' => '2026-10-07',
        'title' => 'Around the table',
        'items' => [
            'Maps: an image on the table screen that you zoom and move, with an optional square grid and a scale.',
            'Optional tokens, linked to entries (name and portrait): move, resize, show or hide them from players.',
            'Temporary ruler: draw a line and the distance appears in squares or in metres.',
            'Remote control: from your phone, clear the screen, move on to the scene’s next item, control the map.',
            'Players: whatever the GM reveals or gives you appears right away, without going through notifications.',
        ],
    ],

    '0.6.0' => [
        'date' => '2026-10-07',
        'title' => 'The campaign’s memory',
        'items' => [
            'Secrets: a standalone piece of information, linked to entries, scenes or documents, revealed in one click to a character or to the whole table.',
            'Reveal history: who learned what, when, during which session and which scene; every reveal can be undone.',
            '“View as”: the GM sees the campaign exactly as a character does, read-only.',
            '“Mentioned in” now also shows the rules and session notes that mention an entry.',
            'Relations: the reverse (“works for” / “employs”) fills itself in.',
        ],
    ],

    '0.5.0' => [
        'date' => '2026-10-07',
        'title' => 'Tidier, together',
        'items' => [
            'A “Tags” page to rename, colour, merge and delete your tags; scenes have tags too.',
            '“Duplicate” an entry, a scenario or a whole campaign, to replay with another table.',
            'New roles: co-GM, who prepares and runs games with you, and spectator, who watches the table screen.',
            '“Show at the table” from an entry, a portrait, an illustration, a document or a rule.',
            'Session mode: show an entry or a rule in one click, see the next scene, press N to take a note.',
        ],
    ],

    '0.4.1' => [
        'date' => '2026-10-07',
        'title' => 'Help and problem reports',
        'items' => [
            'A “Help” page answers the most common questions, for GMs and players alike.',
            '“Report a problem”, at the bottom of every page, sends your message to the team along with the page concerned.',
        ],
    ],

    '0.4.0' => [
        'date' => '2026-10-07',
        'title' => 'Every language',
        'items' => [
            'The interface speaks French, English, German, Spanish, Italian, Portuguese, Dutch and Polish.',
            'The language follows your browser; anyone can choose it in “Preferences”.',
            'Notifications arrive in the language of whoever receives them.',
            'The dark theme now stays on from one page to the next.',
            'After editing an entry from the Characters page, you go straight back to it.',
        ],
    ],

    '0.3.0' => [
        'date' => '2026-10-07',
        'title' => 'The living link',
        'items' => [
            'Messaging between the GM and their players, and a “Chat” panel always within reach (group and private).',
            'Notifications: reveals, items received, messages, with a counter in the header.',
            'Everything updates live: messages, counters, reveals, without reloading the page.',
            'LoreMundi installs as an app; the character sheet stays readable offline; notifications on the device.',
            'Table screen: maps, images, entries and announcements on the TV or projector, and shared with the players if the GM wishes.',
            'Characters give each other items and pass on what they know.',
            'Players note their knowledge and add their items; the GM approves the items.',
            'Dark theme, accent colour and text size in “Preferences”.',
        ],
    ],

    '0.2.0' => [
        'date' => '2026-10-07',
        'title' => 'The players',
        'items' => [
            'Player invitations by link.',
            'Player characters: entry, PDF sheet, counters (HP, magic, ammo…) and fields editable by the player.',
            'Reveals and “Give”: knowledge, possessions, documents and rules.',
            'Player space: private or shared notes, “To play” intentions, character journal.',
            'Change log: who, what, when, before and after.',
        ],
    ],

    '0.1.0' => [
        'date' => '2026-10-06',
        'title' => 'The GM alone',
        'items' => [
            'Worlds, campaigns and entries with a public zone and a GM zone, [[ ]] links between entries.',
            'Custom fields per game, entry types, CSV/JSON import.',
            'Scenarios, scenes, rules and document library.',
            'Session mode: current scene, useful entries, quick notes, “To play” and pins.',
            'Global search.',
        ],
    ],

];
