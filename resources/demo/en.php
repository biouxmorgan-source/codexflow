<?php

/*
 * Demonstration campaign, English text.
 *
 * Only the text lives here: the structure (who is linked to whom, tokens, links) is in
 * App\Actions\Demo\LoadDemoCampaign and is the same in every language. Each translation
 * uses exactly these keys. In scenes, “[[key]]” refers to an entry by its key.
 */
return [
    'campaign' => [
        'name' => 'The Oath of Pierrecendre',
        'description' => 'Demonstration campaign: three sessions in the port town of Pierrecendre, where a forgotten oath comes back to collect what it is owed. All content is original and royalty-free.',
    ],
    'game' => [
        'name' => 'Mist & Oath',
        'description' => 'A game of investigation and oaths, invented for this demo. Four traits rated 1 to 5, oaths that weigh on the dice, no proprietary mechanics.',
    ],
    'world' => [
        'name' => 'Vehrmund',
        'description' => 'An archipelago of drowned marches and ports built on ash, where a word given is as good as a contract.',
    ],

    'types' => [
        'faction' => 'Faction',
        'pregen' => 'Pregen',
    ],

    'groups' => [
        'traits' => 'Traits',
        'profile' => 'Profile',
        'secrets' => 'Secrets',
        'landmarks' => 'Landmarks',
    ],

    'fields' => [
        'body' => 'Body',
        'skill' => 'Finesse',
        'mind' => 'Mind',
        'heart' => 'Heart',
        'breath' => 'Breath',
        'oaths' => 'Oaths kept',
        'trade' => 'Trade',
        'trait' => 'Defining trait',
        'ties' => 'Ties',
        'hidden_oath' => 'Hidden oath',
        'betrayal' => 'What would make them betray',
        'allegiance' => 'True allegiance',
        'danger' => 'Danger',
        'reference' => 'Reference document',
    ],

    'tags' => [
        'city' => 'city',
        'act1' => 'act 1',
        'act2' => 'act 2',
        'act3' => 'act 3',
        'intrigue' => 'intrigue',
        'hall' => 'hall',
        'quays' => 'quays',
        'marshes' => 'marches',
        'guard' => 'watch',
        'pregen' => 'pregen',
        'base' => 'core',
        'oaths' => 'oaths',
        'house' => 'house rule',
        'ambience' => 'ambience',
    ],

    'quay_state' => [
        'status' => 'under curfew',
        'notes' => 'Closed at night since Gueffroy drowned.',
    ],

    'entities' => [
        'city' => [
            'name' => 'Pierrecendre',
            'summary' => 'A port town built on the ash flow of a dead volcano.',
            'description' => "Fifteen thousand souls, two hills and a half-moon bay. The town lives on salt, glass and oaths: every contract made at the Hall is engraved on a tile of vitrified ash.\n\nThe streets smell of kelp and cold sulphur. The upper town belongs to the trading houses, the lower town to those who work the water.",
            'gm_notes' => 'Real power lies with the Hall, not the Watch. If the players threaten the Watch, Mornevent folds; if they threaten the Hall, the whole town closes ranks.',
        ],
        'hall' => [
            'name' => 'The Hall of Oaths',
            'summary' => 'A pale stone building where the town’s oaths are engraved and kept.',
            'description' => 'A godless nave lined with shelves of vitrified tiles. Each tile bears an oath, its date and its witnesses. You enter bareheaded; you leave bound.',
            'gm_notes' => 'The tiles from the year of the great mist have been removed. Elzevir knows where they are: in the lighthouse cellar, not at the Hall.',
        ],
        'quay' => [
            'name' => 'Lantern Quay',
            'summary' => 'The fishermen’s quay, lit all night by lanterns burning fish oil.',
            'description' => 'Thirty lanterns, lit at dusk by a boy paid by the week. When one goes out, the old-timers head home without finishing their drink.',
            'gm_notes' => 'The third lantern from the north is never relit: it is the Ferryman’s signal.',
        ],
        'marshes' => [
            'name' => 'The Drowned Marches',
            'summary' => 'Salt marshes separating Pierrecendre from the mainland, passable at low tide.',
            'description' => 'Three hours of safe going per tide, twelve hours of waiting otherwise. Poles driven into the mud mark the ford; someone keeps moving them.',
            'gm_notes' => 'The Oathbroken move the poles so that travellers lose their way and vanish.',
        ],
        'lighthouse' => [
            'name' => 'Orvent Lighthouse',
            'summary' => 'An abandoned lighthouse on the southern point whose lamp still burns on certain nights.',
            'description' => 'Thirty-two metres of stone, a spiral staircase, a cellar that floods at high tide.',
            'gm_notes' => 'The tiles missing from the Hall are in the cellar, in a salt crate. The Stranger guards them.',
        ],
        'ysane' => [
            'name' => 'Lady Ysane Korr',
            'summary' => 'Keeper of oaths: she engraves the tiles and witnesses the contracts.',
            'description' => 'Sixty years old, hands scarred by the vitrifying kiln, a memory no one dares contradict.',
            'gm_notes' => 'She is the one who had the tiles from the year of the great mist removed: her own name is on one of them. She is not wicked, she is terrified.',
            'fields' => [
                'trade' => 'Keeper of oaths',
                'trait' => 'Never meets the same person’s eyes twice',
                'hidden_oath' => 'Swore, thirty years ago, to let the Drowned take one boat a year. The town has had no shipwrecks since.',
                'betrayal' => 'Her granddaughter’s safety',
            ],
        ],
        'brannoc' => [
            'name' => 'Brannoc the Ferryman',
            'summary' => 'Takes people and crates across the marches, at whatever hour suits him.',
            'description' => 'Tall, slow, says little and counts fast. Knows the ford by heart, even when it moves.',
            'gm_notes' => 'He knows the poles are moving. He will keep quiet until someone offers to buy back his debt to the Grey Thread.',
            'fields' => [
                'trade' => 'Ferryman',
                'trait' => 'Never swears an oath, which in this town counts as an insult',
                'hidden_oath' => 'Owes the Grey Thread Company eleven years of free crossings.',
                'betrayal' => 'Having his debt wiped clean',
            ],
        ],
        'elzevir' => [
            'name' => 'Master Elzevir',
            'summary' => 'Archivist of the Hall, who can read the oldest tiles.',
            'description' => 'Small, dusted with ash, unable to lie without coughing.',
            'gm_notes' => 'He copied the removed tiles before they were taken away. His copy is sewn into the lining of his coat.',
            'fields' => [
                'trade' => 'Archivist',
                'trait' => 'Coughs when he lies',
                'hidden_oath' => 'Swore to Ysane never to speak of the year of the great mist.',
                'betrayal' => 'A promise that the tiles will be put back where they belong',
            ],
        ],
        'vanne' => [
            'name' => 'Sister Vanne',
            'summary' => 'Tends the drowned and the burned, without asking whose side they’re on.',
            'description' => 'Runs a six-bed ward above a ropewalk.',
            'gm_notes' => 'She treated two of the Oathbroken last week. She will only say so in exchange for salt and bandages.',
            'fields' => [
                'trade' => 'Healer',
                'trait' => 'Calls everyone “little one”',
            ],
        ],
        'mornevent' => [
            'name' => 'Captain Hald Mornevent',
            'summary' => 'Commands the Quay Watch: twenty-two men and one boat.',
            'description' => 'Competent, tired, and fully aware he lacks the means to do his job.',
            'gm_notes' => 'He is covering up the disappearance of three travellers so as not to panic the town. He will accept help if it is offered without an audience.',
            'fields' => [
                'trade' => 'Captain of the Watch',
                'trait' => 'Writes everything in a notebook he never rereads',
                'hidden_oath' => 'Promised the council that no one would vanish under his command.',
                'betrayal' => 'Saving face before the council',
            ],
        ],
        'stranger' => [
            'name' => 'The Stranger of the Lighthouse',
            'summary' => 'Whoever relights the lamp of Orvent Lighthouse. No one has seen him up close.',
            'gm_notes' => 'It is Gueffroy, the lantern boy, drowned six months ago and given back by the Drowned. He guards the tiles and wants only one thing: for someone to speak his name aloud.',
            'fields' => [
                'trade' => 'Lamplighter',
                'trait' => 'Smells of cold salt',
                'hidden_oath' => 'Swore, as he died, to keep relighting the lanterns until his name is given back to him.',
            ],
        ],
        'drowned' => [
            'name' => 'The Drowned',
            'summary' => 'What rises from the marches when the mist holds for more than three days.',
            'description' => 'People describe them as figures walking under the shallow water, at the height of a man.',
            'gm_notes' => 'They do not kill: they claim. One of the Drowned lets go of its catch if someone keeps, in its place, the oath it came to collect.',
        ],
        'seal' => [
            'name' => 'The Ash Seal',
            'summary' => 'The die that engraves the Hall’s tiles. Without it, no oath is valid.',
            'description' => 'A heavy cylinder of black glass, the town’s arms cut into it in intaglio.',
            'gm_notes' => 'Ysane has hidden it. Bringing it into the open ends the campaign through negotiation; destroying it ends it through rupture.',
        ],
        'greythread' => [
            'name' => 'The Grey Thread Company',
            'summary' => 'A trading house that buys debts and sells services.',
            'description' => 'Three counting houses, not a single ship of its own, and a debt ledger thicker than the town register.',
            'gm_notes' => 'Wants the Ash Seal: whoever engraves the oaths sets the price of debts.',
        ],
        'broken' => [
            'name' => 'The Oathbroken',
            'summary' => 'Those who broke an oath and now live outside the town, in the marches.',
            'gm_notes' => 'They move the poles so the town will finally fear the water. Their leader is Ysane’s daughter.',
        ],
        'guard' => [
            'name' => 'The Quay Watch',
            'summary' => 'Twenty-two men in charge of the harbour, the lanterns and the curfew.',
            'gm_notes' => 'Two of them are on the Grey Thread’s payroll. Mornevent doesn’t know.',
        ],
        'teska' => [
            'name' => 'Teska the Rower',
            'summary' => 'Has rowed since childhood and knows the bay better than the Watch.',
            'description' => 'You swore to your brother you would never leave Pierrecendre. He left last month.',
            'fields' => [
                'trade' => 'Rower',
                'trait' => 'Says everything, right away',
                'ties' => 'Her brother, who left without a word. Brannoc, who owes her a boat.',
            ],
        ],
        'oriel' => [
            'name' => 'Oriel Chantegrèle',
            'summary' => 'A professional witness: paid to attend oaths and remember them.',
            'description' => 'You have witnessed two hundred oaths. You have forgotten only one, on purpose.',
            'fields' => [
                'trade' => 'Witness',
                'trait' => 'Repeats important sentences under his breath',
                'ties' => 'Master Elzevir, who trained him. The Grey Thread, who hires him too often.',
            ],
        ],
        'dorn' => [
            'name' => 'Dorn Coldiron',
            'summary' => 'Former watchman, dismissed for refusing to enforce a curfew.',
            'description' => 'You swore never again to obey an order you don’t understand.',
            'fields' => [
                'trade' => 'Dismissed watchman',
                'trait' => 'Always stands between the door and everyone else',
                'ties' => 'Mornevent, who dismissed him with regret. Sister Vanne, who has stitched him up twice.',
            ],
        ],
        'lisenn' => [
            'name' => 'Lisenn of Two Names',
            'summary' => 'Comes from the marches and lives in town under a name that isn’t hers.',
            'description' => 'You have broken an oath. No one here knows it yet.',
            'fields' => [
                'trade' => 'Marsh guide',
                'trait' => 'Never sleeps two nights in the same place',
                'ties' => 'The Oathbroken, whom she left. Lisenn, the dead woman whose name she bears.',
            ],
        ],
    ],

    // Relation label, then its reverse label (null: no reverse).
    'relations' => [
        'ysane_hall' => ['keeps', 'kept by'],
        'elzevir_hall' => ['works at', 'employs'],
        'elzevir_ysane' => ['swore silence to', 'holds by an oath'],
        'brannoc_marshes' => ['knows the ford of', 'crossed by'],
        'brannoc_greythread' => ['owes a debt to', 'holds the debt of'],
        'mornevent_guard' => ['commands', 'commanded by'],
        'guard_quay' => ['patrols', 'patrolled by'],
        'greythread_guard' => ['has bought two men of', null],
        'greythread_seal' => ['covets', 'coveted by'],
        'broken_marshes' => ['live in', 'shelter'],
        'broken_ysane' => ['are led by her daughter', null],
        'drowned_marshes' => ['rise from', null],
        'drowned_ysane' => ['hold an oath with', null],
        'stranger_lighthouse' => ['relights', 'relit by'],
        'stranger_quay' => ['used to light the lanterns of', null],
        'vanne_broken' => ['has treated two of', null],
        'hall_city' => ['stands in', 'is home to'],
        'quay_city' => ['lines', 'opens onto'],
        'seal_hall' => ['engraves the tiles of', null],
        'teska_brannoc' => ['lent him a boat', 'owes her a boat'],
        'oriel_elzevir' => ['apprenticed under', 'trained'],
        'dorn_mornevent' => ['served under', 'dismissed'],
        'lisenn_broken' => ['left them', null],
    ],

    'documents' => [
        'tile' => [
            'title' => 'Tile 1147 — the oath of the great mist',
            'description' => 'The transcript Elzevir copied before the tiles left the Hall.',
            'lines' => [
                'Transcript of tile 1147, Hall of Oaths of Pierrecendre.',
                '',
                'Sworn by: Ysane Korr, keeper.',
                'Oath: “One boat a year, and the bay shall stay calm.”',
                'Witnesses: Elzevir, archivist. Gueffroy, lamplighter.',
                '',
                'Archivist’s note: tile removed from the shelf on the 3rd of the salt month.',
            ],
        ],
        'notice' => [
            'title' => 'Curfew notice',
            'description' => 'Posted on Lantern Quay. Show it to the players in the very first scene.',
            'lines' => [
                'By order of Captain Hald Mornevent, Quay Watch.',
                '',
                'Lantern Quay is closed from the last lantern until dawn.',
                'No one puts to sea without a pass from the Watch.',
                'Any lantern gone out must be reported to the guardhouse.',
                '',
                'This notice stands as an oath: whoever breaks it answers to the Hall.',
            ],
        ],
        'tides' => [
            'title' => 'Tide table for the Drowned Marches',
            'description' => 'Player handout: three hours of ford per low tide.',
            'lines' => [
                'The Drowned Marches — crossing the ford',
                '',
                'Low tide: three hours of safe going, poles visible.',
                'Rising tide: one hour’s grace, water up to mid-thigh.',
                'High tide: no crossing. Twelve hours’ wait.',
                '',
                'The poles are replanted every month by the Watch.',
            ],
        ],
        'plan' => [
            'title' => 'Map of Pierrecendre harbour',
            'description' => 'The harbour, its quays and the lighthouse point. Safe to show at the table.',
            'file' => 'harbour-map',
        ],
    ],

    'map' => [
        'name' => 'Pierrecendre harbour',
        'unit' => 'm',
    ],

    'rules' => [
        'roll' => [
            'title' => 'Oath roll',
            'category' => 'Core',
            'summary' => 'Trait + 1d6 against a difficulty of 4 to 9.',
            'procedure' => "1. Name the trait being used and what the character wants to achieve.\n2. Roll 1d6 and add the trait.\n3. 4 for a routine task of one’s trade, 7 for a hard task, 9 for the impossible.\n4. If the character is acting to keep an oath, add +1 per oath kept, up to +3.",
            'source' => 'Core booklet, p. 12',
        ],
        'breath' => [
            'title' => 'Breath',
            'category' => 'Core',
            'summary' => 'Breath replaces hit points: you spend it to hold on, not to soak damage.',
            'procedure' => "Spend 1 Breath to reroll a die, to keep going despite a wound, or to refuse one of the Drowned.\nAt 0, the character stops: not dead, but unable to promise anything until the next rest.",
            'source' => 'Core booklet, p. 18',
        ],
        'breaking' => [
            'title' => 'Breaking an oath',
            'category' => 'Oaths',
            'summary' => 'Breaking an oath grants an immediate advantage and a lasting price.',
            'procedure' => "The player describes what breaking the oath lets them do: they get it, no roll needed.\nThen they lose all their oaths kept, and the table notes who found out.",
            'gm_notes' => 'Never refuse a broken oath. The price is paid in the fiction, through the reaction of those who find out.',
            'source' => 'Core booklet, p. 24',
        ],
        'mist' => [
            'title' => 'Mist count',
            'category' => 'House rule',
            'summary' => 'House rule: the mist rises one notch each session, until the Drowned walk the streets.',
            'procedure' => "Keep a count from 0 to 6, visible to the table.\n+1 at the end of each session, +1 each time an oath is broken before a witness.\nAt 3, the ford becomes unreliable. At 6, the Drowned enter Pierrecendre.",
            'gm_notes' => 'Track it behind the GM screen, secretly until it reaches 3.',
        ],
        'word' => [
            'title' => 'Word given at the table',
            'category' => 'House rule',
            'summary' => 'To playtest: a promise the player makes out loud counts as an oath.',
            'procedure' => 'When a player promises something to a character, write it down. If they keep it, +1 oath kept; if not, the rules for breaking an oath apply.',
            'gm_notes' => 'Playtest in session 2. Risk: the players stop daring to promise anything.',
        ],
    ],

    'scenario' => [
        'name' => 'The Oath of Pierrecendre',
        'summary' => 'Three sessions: a lantern gone dark, a ford that lies, a lighthouse that wants a name.',
    ],

    'chapters' => [
        's1' => 'Session 1 — The Dark Lantern',
        's2' => 'Session 2 — The Lying Ford',
        's3' => 'Session 3 — The Name Returned',
    ],

    // “notes”: each entry’s note in the scene, by entry key (absent: no note).
    'scenes' => [
        'lantern' => [
            'name' => 'The Third Lantern',
            'description' => 'At dusk on [[quay]], the third lantern from the north refuses to light. The curfew notice is still fresh on the wall.',
            'gm_notes' => 'It is the signal of [[brannoc]]. Let the players work it out by watching who approaches the quay.',
            'notes' => ['brannoc' => 'arrives by water, without a sound', 'guard' => 'two men on patrol'],
        ],
        'register' => [
            'name' => 'The Refused Register',
            'description' => 'At [[hall]], [[ysane]] refuses access to the shelf for the year of the great mist. [[elzevir]] coughs.',
            'gm_notes' => 'Elzevir will give in if taken aside, out of Ysane’s sight. Otherwise he coughs and changes the subject.',
            'notes' => ['ysane' => 'behind the lectern', 'elzevir' => 'among the shelves'],
        ],
        'poles' => [
            'name' => 'The Moved Poles',
            'description' => 'In [[marshes]], at low tide, two poles are missing and a third has been replanted crooked. The mist has held for four days.',
            'gm_notes' => 'A Mind roll at 7 spots the trick. On a failure, the tide rises around one player: a chance to spend Breath.',
            'notes' => ['brannoc' => 'knows, and keeps quiet', 'broken' => 'watching from afar'],
        ],
        'notebook' => [
            'name' => 'Mornevent’s Notebook',
            'description' => '[[mornevent]] grudgingly receives visitors in the guardhouse of [[guard]]. Three names are crossed out in his notebook.',
            'gm_notes' => 'He talks if there are no witnesses. The three names belong to the travellers who vanished at the ford.',
            'notes' => ['marshes' => 'mentioned, not visited'],
        ],
        'ward' => [
            'name' => 'The Six-Bed Ward',
            'description' => 'At [[vanne]]’s, two recently wounded patients smell of salt. She trades what she knows for salt and bandages.',
            'notes' => ['broken' => 'two of them, treated last week'],
        ],
        'cellar' => [
            'name' => 'The Lighthouse Cellar',
            'description' => 'The cellar of [[lighthouse]] floods at high tide. In a salt crate: the tiles removed from the Hall.',
            'gm_notes' => 'Tile 1147 sits on top of the pile, in plain view. [[stranger]] is waiting for someone to read it aloud.',
            'notes' => ['stranger' => 'at the top of the stairs', 'seal' => 'not in the crate'],
        ],
        'rising' => [
            'name' => 'What Rises',
            'description' => '[[drowned]] walk through the bay, at the height of a man, straight toward [[city]]. The mist count stands at 6.',
            'gm_notes' => 'They stop if someone keeps the oath of tile 1147 in Ysane’s place, or if Gueffroy’s name is spoken before a witness.',
            'notes' => ['ysane' => 'on the quay, without her seal'],
        ],
        'recast' => [
            'name' => 'The Oath Recast',
            'description' => 'At [[hall]], before the whole town: return [[seal]] to the Hall, or break it.',
            'gm_notes' => 'Two endings, neither of them good. Return it: the town holds, Ysane falls. Break it: no oath binds anyone any more, and the Grey Thread buys everything.',
            'notes' => ['greythread' => 'present, waiting its turn'],
        ],
    ],

    'secrets' => [
        'ysane_oath' => [
            'title' => 'Ysane swore the Drowned one boat a year',
            'body' => 'Thirty years ago, Ysane Korr promised the Drowned one boat a year to keep the bay calm. Tile 1147 bears the text of that oath, and her name.',
        ],
        'stranger' => [
            'title' => 'The Stranger of the Lighthouse is Gueffroy, the drowned lamplighter',
            'body' => 'Gueffroy was the boy paid to light the quay’s lanterns. Drowned six months ago, he was given back by the Drowned. He relights the lighthouse and waits for someone to speak his name.',
        ],
        'poles' => [
            'title' => 'The ford’s poles are being moved on purpose',
            'body' => 'The Oathbroken move the poles so that Pierrecendre will fear its own water. Three travellers have already vanished there.',
        ],
        'daughter' => [
            'title' => 'The leader of the Oathbroken is Ysane’s daughter',
            'body' => 'The woman who leads the Oathbroken is the keeper’s daughter. It is for her that Ysane hid the tiles, and for her that she would betray the town.',
        ],
        'bought' => [
            'title' => 'The Grey Thread has bought two watchmen',
            'body' => 'Two men of the Quay Watch are paid by the Grey Thread Company. Mornevent has no idea, and finding out will break him.',
        ],
    ],

    // Free-form date, title, description.
    'timeline' => [
        'ash' => ['300 years ago', 'Ash buries the bay', 'The eruption of Mount Orvent spends the volcano and gives the town its grey ground.'],
        'first_oath' => ['180 years ago', 'The first oath is engraved', 'The Hall is built and the first oath is vitrified onto a tile of ash.'],
        'great_mist' => ['30 years ago', 'The year of the great mist', 'Eight months of mist, eleven boats lost, then not a single shipwreck for thirty years.'],
        'tile_1147' => ['30 years ago', 'The oath of tile 1147', 'Ysane Korr promises the Drowned one boat a year. Two witnesses: Elzevir and Gueffroy.'],
        'drowning' => ['Six months ago', 'Gueffroy drowns at the quay', 'The lamplighter falls from Lantern Quay. His body is never found.'],
        'missing' => ['Last month', 'Three travellers go missing at the ford', 'Mornevent crosses three names out of his notebook and does not warn the council.'],
        'session1' => ['Session 1', 'The third lantern stays dark', 'The characters spot the Ferryman’s signal and are refused the shelf of the great mist.'],
        'poles_moved' => ['Session 2', 'The poles are moved', 'If no one steps in, a fourth traveller vanishes in the marches.'],
        'invasion' => ['Session 3', 'The Drowned enter the town', 'When the mist count reaches 6, they come up the bay and walk all the way to the Hall.'],
        'ending' => ['End', 'The seal returned or broken', 'Returning the Seal brings Ysane down; breaking it frees the town from every oath, and the Grey Thread from every limit.'],
    ],

    // Ambiances sonores de la bibliothèque.
    'sounds' => [
        'tide' => 'Low tide on the marshes',
        'mist' => 'Pierrecendre mist',
        'storm' => 'Storm over the lighthouse',
    ],

    // Séance 1, déjà jouée : son résumé.
    'session' => [
        'summary' => '[[quay]]: the characters land, and [[brannoc]] shows them that the third lantern stays dark, the Ferryman’s signal that nobody has noticed. [[hall]]: [[elzevir]] refuses to open the shelf of the great mist, and [[ysane]] thanks them a little too quickly. The session ends at the water’s edge, at low tide: [[marshes]].',
        'notes' => [
            'lantern' => 'The players suspected [[brannoc]] straight away; Teska followed him to the marshes without being seen.',
            'register' => '[[elzevir]] gave nothing away while [[ysane]] was in the room. Pick it up in session 2 by taking him aside.',
            'end' => 'Next session: open at low tide, with the ford bell in the distance. Remember the mist count.',
        ],
    ],

    // Niveaux du champ « Danger » (lieux et créatures), du plus calme au plus mortel.
    'danger_levels' => [
        'calm' => 'calm',
        'tense' => 'tense',
        'dangerous' => 'dangerous',
        'deadly' => 'deadly',
    ],

    // Pièce jointe réservée au MJ, sur la fiche du Sceau.
    'attachments' => [
        'seal' => [
            'title' => 'Rubbing of the Ash Seal',
            'file' => 'rubbing-of-the-seal',
            'lines' => [
                'Taken in charcoal by Elzevir, thirty years ago.',
                'In the centre: an overturned boat, three waves.',
                'Around the rim: "What is promised to the water returns to the water."',
                'On the back, scratched out: a name, erased on purpose.',
            ],
        ],
    ],

    // Ce que le personnage de Teska a reçu en séance 1 : titre, texte.
    'character' => [
        'lantern' => ['Quay lantern', 'Taken from the hook of the third lantern. It still smells of oil.'],
        'coins' => ['Ash coins', 'A week’s pay at the oars.'],
        'rumour' => ['They say the lighthouse lights itself', 'Fishermen swear they saw a light at the Orvent Lighthouse on the night of the drowning.'],
    ],

    // La liste « À jouer ».
    'to_play' => [
        'curfew' => 'Show the curfew notice as soon as they reach the quay.',
        'bell' => 'Ring the ford bell during the crossing.',
        'mist' => 'Start the mist count on the first boat trip.',
        'debt' => 'Remind Teska that Brannoc owes her a boat.',
    ],
];
