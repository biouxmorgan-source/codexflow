<?php

/*
 * Campanha de demonstração, texto em português europeu (tradução de fr.php).
 *
 * Só o texto está aqui: a estrutura (quem está ligado a quem, os tokens, as ligações) está em
 * App\Actions\Demo\LoadDemoCampaign e é a mesma em todas as línguas. Cada tradução
 * retoma exatamente estas chaves. Nas cenas, «[[chave]]» designa uma ficha pela sua chave.
 */
return [
    'campaign' => [
        'name' => 'O Juramento de Pierrecendre',
        'description' => 'Campanha de demonstração: três sessões na cidade portuária de Pierrecendre, onde um juramento esquecido volta para cobrar o que lhe é devido. Todo o conteúdo é original e livre de direitos.',
    ],
    'game' => [
        'name' => 'Bruma e Juramento',
        'description' => 'Jogo de investigação e de juramentos, inventado para a demonstração. Quatro características de 1 a 5, juramentos que pesam nos lançamentos, nenhuma mecânica proprietária.',
    ],
    'world' => [
        'name' => 'Vehrmund',
        'description' => 'Um arquipélago de marcas alagadas e de portos erguidos sobre a cinza, onde a palavra dada vale como contrato.',
    ],

    'types' => [
        'faction' => 'Fação',
        'pregen' => 'Pré-criado',
    ],

    'groups' => [
        'traits' => 'Características',
        'profile' => 'Perfil',
        'secrets' => 'Segredos',
        'landmarks' => 'Referências',
    ],

    'fields' => [
        'body' => 'Corpo',
        'skill' => 'Destreza',
        'mind' => 'Mente',
        'heart' => 'Coração',
        'breath' => 'Fôlego',
        'oaths' => 'Juramentos cumpridos',
        'trade' => 'Ofício',
        'trait' => 'Traço marcante',
        'ties' => 'Laços',
        'hidden_oath' => 'Juramento oculto',
        'betrayal' => 'O que o levaria a trair',
        'allegiance' => 'Lealdade verdadeira',
        'danger' => 'Perigo',
        'reference' => 'Documento de referência',
    ],

    'tags' => [
        'city' => 'cidade',
        'act1' => 'ato 1',
        'act2' => 'ato 2',
        'act3' => 'ato 3',
        'intrigue' => 'intriga',
        'hall' => 'salão',
        'quays' => 'cais',
        'marshes' => 'marcas',
        'guard' => 'guarda',
        'pregen' => 'pré-criado',
        'base' => 'base',
        'oaths' => 'juramentos',
        'house' => 'da casa',
        'ambience' => 'ambiente',
    ],

    'quay_state' => [
        'status' => 'sob recolher obrigatório',
        'notes' => 'Fechado à noite desde que Gueffroy se afogou.',
    ],

    'entities' => [
        'city' => [
            'name' => 'Pierrecendre',
            'summary' => 'Cidade portuária erguida sobre a escoada de cinza de um vulcão extinto.',
            'description' => "Quinze mil almas, duas colinas e uma baía em meia-lua. Vive-se do sal, do vidro e dos juramentos: todo o contrato feito no Salão fica gravado numa telha de cinza vitrificada.\n\nA cidade cheira a sargaço e a enxofre frio. As ruas altas pertencem às casas de comércio; as ruas baixas, a quem trabalha a água.",
            'gm_notes' => 'O verdadeiro poder está no Salão, não na Guarda. Se os jogadores ameaçarem a Guarda, Mornevent cede; se ameaçarem o Salão, a cidade inteira fecha-se.',
        ],
        'hall' => [
            'name' => 'O Salão dos Juramentos',
            'summary' => 'Edifício de pedra clara onde os juramentos da cidade são gravados e guardados.',
            'description' => 'Uma nave sem deus, cheia de prateleiras de telhas vitrificadas. Cada telha traz um juramento, o seu dia e as suas testemunhas. Entra-se de cabeça descoberta; sai-se comprometido.',
            'gm_notes' => 'As telhas do ano da grande bruma foram retiradas. Elzevir sabe onde estão: na cave do farol, não no Salão.',
        ],
        'quay' => [
            'name' => 'O Cais das Lanternas',
            'summary' => 'O cais dos pescadores, iluminado a noite inteira por lanternas de óleo de peixe.',
            'description' => 'Trinta lanternas, acesas ao crepúsculo por um miúdo pago à semana. Quando uma se apaga, os velhos vão para casa sem acabar o copo.',
            'gm_notes' => 'A terceira lanterna a contar de norte nunca é reacesa: é o sinal do Barqueiro.',
        ],
        'marshes' => [
            'name' => 'As Marcas Alagadas',
            'summary' => 'Sapais salgados que separam Pierrecendre do continente, transitáveis na maré baixa.',
            'description' => 'Três horas de caminho seguro por maré; doze horas de espera, se não. Varas cravadas no lodo marcam o vau. Alguém as anda a mudar de sítio.',
            'gm_notes' => 'As varas são mudadas pelos Perjuros, para que os viajantes se percam e desapareçam.',
        ],
        'lighthouse' => [
            'name' => 'O Farol de Orvent',
            'summary' => 'Farol abandonado na ponta sul, cuja lanterna ainda se acende em certas noites.',
            'description' => 'Trinta e dois metros de pedra, uma escada em caracol, uma cave inundada na maré cheia.',
            'gm_notes' => 'As telhas desaparecidas do Salão estão na cave, numa caixa de sal. O Desconhecido guarda-as.',
        ],
        'ysane' => [
            'name' => 'Dona Ysane Korr',
            'summary' => 'Guardiã dos juramentos: grava as telhas e testemunha os contratos.',
            'description' => 'Sessenta anos, mãos queimadas pelo forno de vitrificar, uma memória que ninguém se atreve a contradizer.',
            'gm_notes' => 'Foi ela quem mandou retirar as telhas do ano da grande bruma: o seu próprio nome está numa delas. Não é má, está aterrorizada.',
            'fields' => [
                'trade' => 'Guardiã dos juramentos',
                'trait' => 'Nunca olha duas vezes nos olhos a mesma pessoa',
                'hidden_oath' => 'Jurou, há trinta anos, deixar os Afogados levar um barco por ano. Desde então, a cidade não voltou a ter naufrágios.',
                'betrayal' => 'A segurança da neta',
            ],
        ],
        'brannoc' => [
            'name' => 'Brannoc, o Barqueiro',
            'summary' => 'Faz passar gente e caixotes pelas marcas, à hora que lhe convém.',
            'description' => 'Alto, lento, fala pouco e conta depressa. Conhece o vau de cor, mesmo quando o mudam.',
            'gm_notes' => 'Sabe que as varas se mexem. Calar-se-á até que lhe proponham resgatar a sua dívida ao Fio Cinzento.',
            'fields' => [
                'trade' => 'Barqueiro',
                'trait' => 'Nunca jura, o que na cidade passa por insulto',
                'hidden_oath' => 'Deve onze anos de travessias gratuitas à Companhia do Fio Cinzento.',
                'betrayal' => 'O perdão da sua dívida',
            ],
        ],
        'elzevir' => [
            'name' => 'Mestre Elzevir',
            'summary' => 'Arquivista do Salão, que sabe ler as telhas mais antigas.',
            'description' => 'Baixo, empoado de cinza, incapaz de mentir sem tossir.',
            'gm_notes' => 'Copiou as telhas retiradas antes de as levarem. A cópia está no forro do casaco.',
            'fields' => [
                'trade' => 'Arquivista',
                'trait' => 'Tosse quando mente',
                'hidden_oath' => 'Jurou a Ysane nunca falar do ano da grande bruma.',
                'betrayal' => 'Que lhe prometam que as telhas voltarão ao seu lugar',
            ],
        ],
        'vanne' => [
            'name' => 'Irmã Vanne',
            'summary' => 'Trata afogados e queimados, sem perguntar de que lado estão.',
            'description' => 'Mantém uma enfermaria de seis camas por cima de uma cordoaria.',
            'gm_notes' => 'Tratou dois Perjuros na semana passada. Só o dirá em troca de sal e de ligaduras.',
            'fields' => [
                'trade' => 'Curandeira',
                'trait' => 'Chama «pequeno» a toda a gente',
            ],
        ],
        'mornevent' => [
            'name' => 'Capitão Hald Mornevent',
            'summary' => 'Comanda a Guarda dos Cais: vinte e dois homens e um barco.',
            'description' => 'Competente, cansado, perfeitamente consciente de que não tem meios para o cargo.',
            'gm_notes' => 'Encobre o desaparecimento de três viajantes para não alarmar a cidade. Aceitará ajuda se lha oferecerem sem plateia.',
            'fields' => [
                'trade' => 'Capitão da Guarda',
                'trait' => 'Aponta tudo num caderno que nunca relê',
                'hidden_oath' => 'Prometeu ao conselho que ninguém desapareceria sob o seu comando.',
                'betrayal' => 'Salvar a face perante o conselho',
            ],
        ],
        'stranger' => [
            'name' => 'O Desconhecido do Farol',
            'summary' => 'Aquele que volta a acender a lanterna do farol de Orvent. Ninguém o viu de perto.',
            'gm_notes' => 'É Gueffroy, o miúdo das lanternas, afogado há seis meses e devolvido pelos Afogados. Guarda as telhas e só espera uma coisa: que alguém diga o seu nome em voz alta.',
            'fields' => [
                'trade' => 'Acendedor de lanternas',
                'trait' => 'Cheira a sal frio',
                'hidden_oath' => 'Jurou, ao morrer, reacender as lanternas até que lhe devolvam o nome.',
            ],
        ],
        'drowned' => [
            'name' => 'Os Afogados',
            'summary' => 'O que sobe das marcas quando a bruma dura mais de três dias.',
            'description' => 'Descrevem-nos como silhuetas que caminham debaixo da água pouco funda, à altura de um homem.',
            'gm_notes' => 'Não matam: reclamam. Um Afogado larga a presa se alguém cumprir, no lugar dela, o juramento que ele veio buscar.',
        ],
        'seal' => [
            'name' => 'O Selo de Cinza',
            'summary' => 'O punção que grava as telhas do Salão. Sem ele, nenhum juramento é válido.',
            'description' => 'Um cilindro de vidro negro, pesado, com as armas da cidade gravadas em baixo-relevo.',
            'gm_notes' => 'Ysane escondeu-o. Torná-lo público encerra a campanha pela negociação; destruí-lo encerra-a pela rutura.',
        ],
        'greythread' => [
            'name' => 'A Companhia do Fio Cinzento',
            'summary' => 'Casa de comércio que compra dívidas e revende serviços.',
            'description' => 'Três feitorias, nenhum navio próprio e um livro de dívidas mais grosso do que o registo da cidade.',
            'gm_notes' => 'Quer o Selo de Cinza: quem grava os juramentos fixa o preço das dívidas.',
        ],
        'broken' => [
            'name' => 'Os Perjuros',
            'summary' => 'Os que quebraram um juramento e vivem agora fora da cidade, nas marcas.',
            'gm_notes' => 'Mudam as varas para que a cidade tenha, enfim, medo da água. Quem os lidera é a filha de Ysane.',
        ],
        'guard' => [
            'name' => 'A Guarda dos Cais',
            'summary' => 'Vinte e dois homens encarregados do porto, das lanternas e do recolher obrigatório.',
            'gm_notes' => 'Dois deles são pagos pelo Fio Cinzento. Mornevent não sabe.',
        ],
        'teska' => [
            'name' => 'Teska, a Remadora',
            'summary' => 'Rema desde criança e conhece a baía melhor do que a Guarda.',
            'description' => 'Você jurou ao seu irmão nunca deixar Pierrecendre. Ele partiu no mês passado.',
            'fields' => [
                'trade' => 'Remadora',
                'trait' => 'Diz tudo, e logo',
                'ties' => 'O irmão, que partiu sem uma palavra. Brannoc, que lhe deve um barco.',
            ],
        ],
        'oriel' => [
            'name' => 'Oriel Chantegrèle',
            'summary' => 'Testemunha de ofício: pagam-lhe para assistir aos juramentos e para se lembrar deles.',
            'description' => 'Você testemunhou duzentos juramentos. Esqueceu um só, de propósito.',
            'fields' => [
                'trade' => 'Testemunha',
                'trait' => 'Repete as frases importantes em voz baixa',
                'ties' => 'Mestre Elzevir, com quem fez a aprendizagem. O Fio Cinzento, que o contrata vezes demais.',
            ],
        ],
        'dorn' => [
            'name' => 'Dorn Ferro-Frio',
            'summary' => 'Antigo guarda, despedido por se recusar a aplicar um recolher obrigatório.',
            'description' => 'Você jurou nunca mais obedecer a uma ordem que não compreenda.',
            'fields' => [
                'trade' => 'Guarda despedido',
                'trait' => 'Põe-se sempre entre a porta e os outros',
                'ties' => 'Mornevent, que o despediu a contragosto. A Irmã Vanne, que já o coseu duas vezes.',
            ],
        ],
        'lisenn' => [
            'name' => 'Lisenn dos Dois Nomes',
            'summary' => 'Vem das marcas e vive na cidade sob um nome que não é o seu.',
            'description' => 'Você quebrou um juramento. Aqui, ninguém o sabe ainda.',
            'fields' => [
                'trade' => 'Guia das marcas',
                'trait' => 'Nunca dorme duas noites no mesmo sítio',
                'ties' => 'Os Perjuros, que ela deixou. Lisenn, a morta cujo nome usa.',
            ],
        ],
    ],

    // Rótulo da relação, depois o rótulo inverso (null: sem inverso).
    'relations' => [
        'ysane_hall' => ['guarda', 'guardado por'],
        'elzevir_hall' => ['trabalha em', 'emprega'],
        'elzevir_ysane' => ['jurou silêncio a', 'prende-o com um juramento'],
        'brannoc_marshes' => ['conhece o vau de', 'atravessadas por'],
        'brannoc_greythread' => ['tem uma dívida com', 'detém a dívida de'],
        'mornevent_guard' => ['comanda', 'comandada por'],
        'guard_quay' => ['vigia', 'vigiado por'],
        'greythread_guard' => ['comprou dois homens de', null],
        'greythread_seal' => ['cobiça', 'cobiçado por'],
        'broken_marshes' => ['vivem em', 'abrigam'],
        'broken_ysane' => ['são liderados pela filha dela', null],
        'drowned_marshes' => ['sobem de', null],
        'drowned_ysane' => ['têm um juramento com', null],
        'stranger_lighthouse' => ['reacende', 'reacendido por'],
        'stranger_quay' => ['acendia as lanternas de', null],
        'vanne_broken' => ['tratou dois deles', null],
        'hall_city' => ['ergue-se em', 'abriga'],
        'quay_city' => ['margina', 'abre-se para'],
        'seal_hall' => ['grava as telhas de', null],
        'teska_brannoc' => ['emprestou-lhe um barco', 'deve-lhe um barco'],
        'oriel_elzevir' => ['foi seu aprendiz', 'formou'],
        'dorn_mornevent' => ['serviu sob as ordens de', 'despediu'],
        'lisenn_broken' => ['deixou-os', null],
    ],

    'documents' => [
        'tile' => [
            'title' => 'Telha 1147 — juramento da grande bruma',
            'description' => 'A transcrição que Elzevir copiou antes de as telhas saírem do Salão.',
            'lines' => [
                'Transcrição da telha 1147, Salão dos Juramentos de Pierrecendre.',
                '',
                'Jurante: Ysane Korr, guardiã.',
                'Juramento: «Um barco por ano, e a baía ficará calma.»',
                'Testemunhas: Elzevir, arquivista. Gueffroy, acendedor de lanternas.',
                '',
                'Nota do arquivista: telha retirada da prateleira no dia 3 do mês do sal.',
            ],
        ],
        'notice' => [
            'title' => 'Aviso de recolher obrigatório',
            'description' => 'Afixado no Cais das Lanternas. Mostrar aos jogadores logo na primeira cena.',
            'lines' => [
                'Por ordem do capitão Hald Mornevent, Guarda dos Cais.',
                '',
                'O Cais das Lanternas está fechado da última lanterna até ao amanhecer.',
                'Ninguém se faz ao mar sem um salvo-conduto da Guarda.',
                'Qualquer lanterna apagada deve ser comunicada ao posto.',
                '',
                'Este aviso vale como juramento: quem o infringir responde perante o Salão.',
            ],
        ],
        'tides' => [
            'title' => 'Tabela de marés das Marcas Alagadas',
            'description' => 'Ajuda de jogo: três horas de vau por maré baixa.',
            'lines' => [
                'Marcas Alagadas — travessia do vau',
                '',
                'Maré baixa: três horas de caminho seguro, varas visíveis.',
                'Maré a encher: uma hora de tréguas, água pelas coxas.',
                'Maré cheia: nenhuma passagem. Doze horas de espera.',
                '',
                'As varas são recolocadas todos os meses pela Guarda.',
            ],
        ],
        'plan' => [
            'title' => 'Planta do porto de Pierrecendre',
            'description' => 'O porto, os seus cais e a ponta do farol. Pode ser mostrada à mesa.',
            'file' => 'planta-do-porto',
        ],
    ],

    'map' => [
        'name' => 'O porto de Pierrecendre',
        'unit' => 'm',
    ],

    'rules' => [
        'roll' => [
            'title' => 'Lançamento de juramento',
            'category' => 'Base',
            'summary' => 'Característica + 1d6 contra uma dificuldade de 4 a 9.',
            'procedure' => "1. Anuncie a característica usada e o que a personagem quer obter.\n2. Lance 1d6 e some a característica.\n3. 4 para uma tarefa do ofício, 7 para uma tarefa difícil, 9 para o impossível.\n4. Se a personagem age para cumprir um juramento, some +1 por juramento cumprido, até +3.",
            'source' => 'Livro base, p. 12',
        ],
        'breath' => [
            'title' => 'Fôlego',
            'category' => 'Base',
            'summary' => 'O Fôlego substitui os pontos de vida: gasta-se para aguentar, não para encaixar golpes.',
            'procedure' => "Gaste 1 de Fôlego para relançar um dado, para continuar apesar de um ferimento ou para recusar um Afogado.\nA 0, a personagem para: não morreu, mas não pode prometer mais nada até ao próximo descanso.",
            'source' => 'Livro base, p. 18',
        ],
        'breaking' => [
            'title' => 'Quebrar um juramento',
            'category' => 'Juramentos',
            'summary' => 'Quebrar um juramento dá uma vantagem imediata e cobra um preço duradouro.',
            'procedure' => "O jogador descreve o que a rutura lhe permite: obtém-no, sem lançamento.\nDepois perde todos os juramentos cumpridos, e a mesa anota quem ficou a saber.",
            'gm_notes' => 'Nunca recusar uma rutura. O preço paga-se na ficção, pela reação de quem fica a saber.',
            'source' => 'Livro base, p. 24',
        ],
        'mist' => [
            'title' => 'Contagem da bruma',
            'category' => 'Da casa',
            'summary' => 'Regra da casa: a bruma sobe um nível a cada sessão, até os Afogados caminharem pela cidade.',
            'procedure' => "Mantenha uma contagem de 0 a 6, à vista da mesa.\n+1 no fim de cada sessão, +1 sempre que um juramento é quebrado diante de testemunhas.\nA 3, o vau torna-se incerto. A 6, os Afogados entram em Pierrecendre.",
            'gm_notes' => 'Contagem atrás do ecrã, em segredo até 3.',
        ],
        'word' => [
            'title' => 'Palavra dada à mesa',
            'category' => 'Da casa',
            'summary' => 'Por testar: uma promessa feita em voz alta pelo jogador conta como juramento.',
            'procedure' => 'Quando um jogador promete algo a uma personagem, anote-o. Se a cumprir, +1 juramento cumprido; se não, aplica-se a rutura.',
            'gm_notes' => 'Testar na sessão 2. Risco: os jogadores deixarem de se atrever a prometer o que quer que seja.',
        ],
    ],

    'scenario' => [
        'name' => 'O Juramento de Pierrecendre',
        'summary' => 'Três sessões: uma lanterna apagada, um vau que mente, um farol que reclama um nome.',
    ],

    'chapters' => [
        's1' => 'Sessão 1 — A lanterna apagada',
        's2' => 'Sessão 2 — O vau que mente',
        's3' => 'Sessão 3 — O nome devolvido',
    ],

    // «notes»: a nota de cada ficha na cena, por chave de ficha (ausente: sem nota).
    'scenes' => [
        'lantern' => [
            'name' => 'A terceira lanterna',
            'description' => 'Ao crepúsculo, [[quay]]: a terceira lanterna a contar de norte recusa-se a acender. O aviso de recolher obrigatório ainda está fresco na parede.',
            'gm_notes' => 'É o sinal de [[brannoc]]. Deixe os jogadores descobri-lo observando quem se aproxima do cais.',
            'notes' => ['brannoc' => 'chega pela água, sem ruído', 'guard' => 'dois homens de ronda'],
        ],
        'register' => [
            'name' => 'O registo recusado',
            'description' => '[[hall]], entre as prateleiras de telhas: [[ysane]] recusa o acesso à fila do ano da grande bruma. [[elzevir]] tosse.',
            'gm_notes' => 'Elzevir cede se o apanharem à parte, longe da vista de Ysane. Caso contrário, tosse e muda de assunto.',
            'notes' => ['ysane' => 'atrás da escrivaninha', 'elzevir' => 'entre as prateleiras'],
        ],
        'poles' => [
            'name' => 'As varas mudadas',
            'description' => '[[marshes]], maré baixa: faltam duas varas e uma terceira foi recravada torta. A bruma não levanta há quatro dias.',
            'gm_notes' => 'Um lançamento de Mente a 7 deteta o embuste. Em caso de falha, a maré sobe sobre um jogador: ocasião para gastar Fôlego.',
            'notes' => ['brannoc' => 'sabe, e cala-se', 'broken' => 'observam-nos de longe'],
        ],
        'notebook' => [
            'name' => 'O caderno de Mornevent',
            'description' => '[[mornevent]] recebe-os a contragosto, no posto onde [[guard]] tem o seu quartel. Há três nomes riscados no caderno dele.',
            'gm_notes' => 'Fala se não houver testemunhas. Os três nomes são os dos viajantes desaparecidos no vau.',
            'notes' => ['marshes' => 'mencionadas, não visitadas'],
        ],
        'ward' => [
            'name' => 'A enfermaria das seis camas',
            'description' => '[[vanne]] tem dois feridos recentes que cheiram a sal. Troca o que sabe por sal e ligaduras.',
            'notes' => ['broken' => 'dois deles, tratados na semana passada'],
        ],
        'cellar' => [
            'name' => 'A cave do farol',
            'description' => '[[lighthouse]] tem a cave inundada na maré cheia. Numa caixa de sal: as telhas retiradas do Salão.',
            'gm_notes' => 'A telha 1147 está no topo da pilha, bem à vista. [[stranger]] espera que alguém a leia em voz alta.',
            'notes' => ['stranger' => 'no cimo da escada', 'seal' => 'não está na caixa'],
        ],
        'rising' => [
            'name' => 'O que sobe',
            'description' => '[[drowned]] caminham pela baía, à altura de um homem, direitos a [[city]]. A contagem da bruma está em 6.',
            'gm_notes' => 'Param se alguém cumprir, no lugar de Ysane, o juramento da telha 1147, ou se o nome de Gueffroy for dito diante de testemunhas.',
            'notes' => ['ysane' => 'no cais, sem o seu selo'],
        ],
        'recast' => [
            'name' => 'O juramento refundido',
            'description' => '[[hall]], diante da cidade inteira: devolver [[seal]] ao Salão, ou quebrá-lo.',
            'gm_notes' => 'Dois finais, nenhum bom. Devolvê-lo: a cidade aguenta-se, Ysane cai. Quebrá-lo: nenhum juramento volta a prender ninguém, e o Fio Cinzento compra tudo.',
            'notes' => ['greythread' => 'presente, à espera da sua vez'],
        ],
    ],

    'secrets' => [
        'ysane_oath' => [
            'title' => 'Ysane jurou aos Afogados um barco por ano',
            'body' => 'Há trinta anos, Ysane Korr prometeu aos Afogados um barco por ano para que a baía ficasse calma. A telha 1147 traz o texto, e o nome dela.',
        ],
        'stranger' => [
            'title' => 'O Desconhecido do Farol é Gueffroy, o acendedor afogado',
            'body' => 'Gueffroy era o miúdo pago para acender as lanternas do cais. Afogado há seis meses, foi devolvido pelos Afogados. Reacende o farol e espera que alguém diga o seu nome.',
        ],
        'poles' => [
            'title' => 'As varas do vau são mudadas de propósito',
            'body' => 'Os Perjuros mudam as varas para que Pierrecendre tenha medo da sua água. Três viajantes já lá desapareceram.',
        ],
        'daughter' => [
            'title' => 'Quem lidera os Perjuros é a filha de Ysane',
            'body' => 'Quem lidera os Perjuros é a filha da guardiã. Foi por ela que Ysane escondeu as telhas, e é por ela que trairia a cidade.',
        ],
        'bought' => [
            'title' => 'O Fio Cinzento comprou dois guardas do cais',
            'body' => 'Dois homens da Guarda dos Cais são pagos pela Companhia do Fio Cinzento. Mornevent não sabe, e descobri-lo vai destroçá-lo.',
        ],
    ],

    // Data livre, título, descrição.
    'timeline' => [
        'ash' => ['Há 300 anos', 'A cinza cobre a baía', 'A erupção do monte Orvent extingue o vulcão e dá à cidade o seu chão cinzento.'],
        'first_oath' => ['Há 180 anos', 'Primeiro juramento gravado', 'O Salão é construído e o primeiro juramento é vitrificado numa telha de cinza.'],
        'great_mist' => ['Há 30 anos', 'O ano da grande bruma', 'Uma bruma de oito meses, onze barcos perdidos, e depois nenhum naufrágio durante trinta anos.'],
        'tile_1147' => ['Há 30 anos', 'O juramento da telha 1147', 'Ysane Korr promete aos Afogados um barco por ano. Duas testemunhas: Elzevir e Gueffroy.'],
        'drowning' => ['Há seis meses', 'Gueffroy afoga-se no cais', 'O acendedor de lanternas cai do Cais das Lanternas. O corpo nunca é encontrado.'],
        'missing' => ['No mês passado', 'Três viajantes desaparecem no vau', 'Mornevent risca três nomes no caderno e não avisa o conselho.'],
        'session1' => ['Sessão 1', 'A terceira lanterna continua apagada', 'As personagens descobrem o sinal do Barqueiro e é-lhes recusado o acesso à fila da grande bruma.'],
        'poles_moved' => ['Sessão 2', 'As varas são mudadas', 'Se ninguém intervier, um quarto viajante desaparece nas marcas.'],
        'invasion' => ['Sessão 3', 'Os Afogados entram na cidade', 'Com a contagem da bruma em 6, sobem a baía e caminham até ao Salão.'],
        'ending' => ['Fim', 'O selo devolvido ou quebrado', 'Devolver o Selo faz cair Ysane; quebrá-lo liberta a cidade de todo o juramento, e o Fio Cinzento de qualquer limite.'],
    ],

    // Ambiances sonores de la bibliothèque.
    'sounds' => [
        'tide' => 'Maré baixa nos pântanos',
        'mist' => 'Bruma de Pierrecendre',
        'storm' => 'Tempestade sobre o farol',
    ],

    // Séance 1, déjà jouée : son résumé.
    'session' => [
        'summary' => '[[quay]]: as personagens desembarcam e [[brannoc]] mostra-lhes que a terceira lanterna continua apagada, o sinal do Barqueiro que ninguém notou. [[hall]]: [[elzevir]] recusa abrir a prateleira da grande bruma, e [[ysane]] agradece-lhes depressa demais. A sessão termina à beira da água, na maré baixa: [[marshes]].',
        'notes' => [
            'lantern' => 'Os jogadores suspeitaram logo de [[brannoc]]; a Teska seguiu-o até aos pântanos sem ser vista.',
            'register' => '[[elzevir]] não disse nada enquanto [[ysane]] esteve na sala. Retomar na sessão 2, chamando-o à parte.',
            'end' => 'Próxima sessão: abrir na maré baixa, com o sino do vau ao longe. Lembrar a contagem da bruma.',
        ],
    ],

    // Niveaux du champ « Danger » (lieux et créatures), du plus calme au plus mortel.
    'danger_levels' => [
        'calm' => 'calmo',
        'tense' => 'tenso',
        'dangerous' => 'perigoso',
        'deadly' => 'mortal',
    ],

    // Pièce jointe réservée au MJ, sur la fiche du Sceau.
    'attachments' => [
        'seal' => [
            'title' => 'Decalque do Selo de cinza',
            'file' => 'decalque-do-selo',
            'lines' => [
                'Tirado a carvão por Elzevir, há trinta anos.',
                'No centro: um barco virado, três ondas.',
                'Na borda: «O que é prometido à água à água regressa.»',
                'No verso, raspado: um nome, apagado de propósito.',
            ],
        ],
    ],

    // Ce que le personnage de Teska a reçu en séance 1 : titre, texte.
    'character' => [
        'lantern' => ['Lanterna do cais', 'Tirada do gancho da terceira lanterna. Ainda cheira a óleo.'],
        'coins' => ['Moedas de cinza', 'O pagamento de uma semana ao remo.'],
        'rumour' => ['Dizem que o farol se acende sozinho', 'Os pescadores juram ter visto uma luz no Farol de Orvent na noite do afogamento.'],
    ],

    // La liste « À jouer ».
    'to_play' => [
        'curfew' => 'Mostrar o aviso de recolher obrigatório assim que chegarem ao cais.',
        'bell' => 'Fazer soar o sino do vau durante a travessia.',
        'mist' => 'Iniciar a contagem da bruma na primeira saída de barco.',
        'debt' => 'Lembrar à Teska que o Brannoc lhe deve um barco.',
    ],
];
