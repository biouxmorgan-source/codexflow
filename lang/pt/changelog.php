<?php

// Novidades por versão, da mais recente à mais antiga. Exibidas em
// «Novidades» (uma vez após cada atualização) e na página de mesmo nome.
return [

    '0.95.1' => [
        'date' => '2026-10-23',
        'title' => 'Informações legais: editor identificado e conteúdos dos utilizadores',
        'items' => [
            'A página «Privacidade e informações legais» indica Morgan Bioux ao lado da Autistic Intelligence, mesmo que a linha LEGAL_OWNER esteja vazia no servidor.',
            'A secção sobre conteúdos dos utilizadores diz claramente que os conteúdos publicados pertencem aos utilizadores, que se transferem a qualquer momento (os meus dados, CSV, exportação de campanha) e que a responsabilidade pelos dados carregados cabe ao utilizador.',
        ],
    ],

    '0.95.0' => [
        'date' => '2026-10-23',
        'title' => '« Novidades » vai ao essencial',
        'short' => 'Uma frase por novidade, e só quando algo muda mesmo para si.',
        'items' => [
            '« Novidades » mostra agora uma versão curta: uma frase por versão, e só para as versões importantes. As pequenas correções já não fazem aparecer a janela.',
            'A conta de administrador mantém o detalhe completo de cada versão, com por baixo de cada uma a frase vista pelas outras contas, ou a indicação de que não lhes é mostrada.',
        ],
    ],

    '0.94.0' => [
        'date' => '2026-10-23',
        'title' => 'Blocos em 1, 2 ou 3 colunas, e página de mapa organizável',
        'short' => '«Organizar o ecrã» define a largura de cada bloco (1, 2 ou 3 colunas), e a página de um mapa também se organiza.',
        'items' => [
            '«Organizar o ecrã» escolhe agora a largura de cada bloco: 1, 2 ou 3 colunas, na página da campanha, no modo Sessão e em «As minhas fichas». Um bloco largo passa para baixo dos blocos anteriores; os outros ficam lado a lado.',
            'As setas ← e → mudam a coluna de um bloco, ↑ e ↓ a sua posição na coluna. No modo «Organizar», as colunas são desenhadas em fundo para apontar a uma coluna vazia.',
            'A página de um mapa também se organiza: o mapa, os marcadores, os modelos, a iniciativa, a grelha e o nome podem ser movidos, alargados ou ocultados, apenas para cada MJ. Por exemplo, o mapa em toda a largura e as definições lado a lado por baixo.',
        ],
    ],

    '0.93.0' => [
        'date' => '2026-10-23',
        'title' => 'Uma nova página inicial',
        'short' => 'Uma nova página inicial, e a ligação para o Discord da comunidade em todo o lado.',
        'items' => [
            'A página inicial pública apresenta agora tudo o que o SagaWyn sabe fazer, em três momentos: preparar, jogar à mesa e manter a campanha viva entre sessões. Mapa de batalha, dados, mapa da campanha, sons, livro de aventuras e «As minhas fichas» têm o seu lugar.',
            'A ligação para o Discord da comunidade aparece agora por omissão, no topo e no fundo da página inicial, na ajuda e no rodapé.',
        ],
    ],

    '0.92.0' => [
        'date' => '2026-10-23',
        'title' => 'Importação com IA: prompt em inglês, língua à escolha',
        'short' => 'A importação com uma IA permite-lhe escolher a língua do conteúdo importado.',
        'items' => [
            'O prompt e o skill do Claude de «Preparar a importação com uma IA» estão agora em inglês, a língua que as IA seguem melhor.',
            'Um menu «Língua do conteúdo importado» escolhe a língua em que a IA escreve as fichas, as cenas, os títulos dos documentos e o guia (a da interface por omissão). Com o skill, também se pode pedir outra língua no momento da importação.',
            'O Livro de aventuras reconhece as secções «O que está previsto» e «O que aconteceu» escritas em qualquer uma das línguas do SagaWyn.',
        ],
    ],

    '0.89.1' => [
        'date' => '2026-10-23',
        'title' => 'Escolhas mais claras ao criar uma campanha',
        'items' => [
            'Em «Nova campanha», as listas «Jogo» e «Mundo» só propõem os de uma campanha em curso ou arquivada. Os de uma campanha eliminada já não aparecem; o seu conteúdo continua acessível pela pesquisa.',
        ],
    ],

    '0.89.0' => [
        'date' => '2026-10-21',
        'title' => 'Declaração de direitos em cada importação',
        'short' => 'Antes de cada importação, uma caixa pede-lhe que confirme que tem os direitos sobre os conteúdos importados.',
        'items' => [
            'Antes de cada importação (ficheiro CSV ou JSON, arquivo de campanha, modelo de jogo), uma caixa desmarcada por predefinição pede para certificar que dispõe dos direitos necessários sobre os conteúdos importados. Sem ela, o botão fica cinzento e o servidor recusa a importação.',
            'Cada declaração é registada com a data, a versão do texto aceite e o resultado da importação, sem cópia dos ficheiros. Aparece em «Transferir os meus dados».',
            'As condições de utilização ganham uma secção «Conteúdos dos utilizadores e propriedade intelectual», acessível a partir de cada ecrã de importação.',
        ],
    ],

    '0.88.0' => [
        'date' => '2026-10-21',
        'title' => 'Identificar os acessos oferecidos',
        'items' => [
            'Consola de administração, página Contas: uma caixa «Acesso oferecido» por conta, com uma nota opcional (amigo, testador…). Os acessos oferecidos aparecem sob o endereço, têm o seu total no topo da página e o filtro «Exibir».',
        ],
    ],

    '0.87.1' => [
        'date' => '2026-10-20',
        'title' => 'Nomes dos pontos legíveis no mapa',
        'items' => [
            'No tema escuro, os nomes dos pontos do mapa da campanha apareciam escuros sobre fundo preto: voltam a ser brancos, tal como os painéis de iniciativa e de dados no ecrã da mesa e os botões de página dos PDF.',
        ],
    ],

    '0.87.0' => [
        'date' => '2026-10-20',
        'title' => 'Rolagens salvas para cada ficha',
        'short' => 'Em « As minhas fichas », cada personagem guarda os seus próprios lançamentos de dados gravados.',
        'items' => [
            'Nos dados de «Minhas fichas», «Salvar esta rolagem» guarda uma rolagem com a ficha: cada personagem tem as próprias rolagens (Bola de fogo, Ataque…), recarregadas com um toque, atualizadas e removidas com ×.',
        ],
    ],

    '0.86.2' => [
        'date' => '2026-10-20',
        'title' => '«Substituir o ficheiro» funciona',
        'items' => [
            'O botão «Substituir» ficava desativado depois de escolher o novo ficheiro: volta a poder ser clicado assim que o ficheiro é enviado.',
        ],
    ],

    '0.86.1' => [
        'date' => '2026-10-20',
        'title' => 'Substituir um mapa por uma imagem de outro tamanho',
        'items' => [
            'Quando o documento serve de mapa, «Substituir o ficheiro» mostra o tamanho da imagem atual e aceita uma imagem de outro tamanho. Peões, moldes e pontos mantêm a posição em píxeis: um aviso indica que vão deslocar-se no desenho.',
        ],
    ],

    '0.86.0' => [
        'date' => '2026-10-20',
        'title' => 'Modificar um documento sem o apagar',
        'short' => '« Substituir o ficheiro » atualiza um documento sem perder o título nem as ligações.',
        'items' => [
            'Na página de um documento, «Substituir o ficheiro» coloca uma nova versão no lugar da antiga: o documento mantém o título, as definições e as ligações, e a nova versão aparece logo nas cenas, fichas, regras, mapas, pistas e nas personagens que o receberam.',
            'Uma imagem que serve de mapa é substituída por outra imagem; peões, grelha e pontos ficam no lugar.',
        ],
    ],

    '0.85.0' => [
        'date' => '2026-10-20',
        'title' => 'Rolar os dados a partir da ficha',
        'short' => 'Um pequeno dado ao lado dos valores da ficha lança os dados com esse valor.',
        'items' => [
            'Na ficha de um personagem, um pequeno dado ao lado de cada número e de cada contador rola os dados com esse valor: «Rolar abaixo de… (limiar)» ou «Somar… à rolagem (bônus)». A bandeja de dados abre pronta para rolar e o resultado vai para a mesa como sempre.',
            'Na ficha do seu próprio personagem, o valor continua ligado à ficha: se mudar, a rolagem salva acompanha. O mestre também pode rolar a partir da ficha de um jogador.',
            '«Minhas fichas» tem os próprios dados: o mesmo pequeno dado ao lado dos valores numéricos e um botão «Dados» no fim da ficha. O resultado aparece na ficha, visível só para você.',
        ],
    ],

    '0.84.2' => [
        'date' => '2026-10-20',
        'title' => 'Novos mapas para a demo',
        'items' => [
            'A demonstração tem novos mapas ilustrados: a aldeia costeira para o mapa da campanha e o cais subterrâneo do farol para o submapa. Volte a carregá-la em «Minhas campanhas» para os ver.',
        ],
    ],

    '0.84.1' => [
        'date' => '2026-10-19',
        'title' => 'Mais espaço em As minhas fichas',
        'items' => [
            'Uma ficha guarda agora até 200 blocos, 500 campos e 30 separadores. «+ Nova nota» já não leva a uma página de erro: no limite, uma mensagem explica-o.',
        ],
    ],

    '0.84.0' => [
        'date' => '2026-10-19',
        'title' => 'Separadores em As minhas fichas',
        'short' => '« As minhas fichas » organiza-se em separadores com o nome que escolher.',
        'items' => [
            'Em «Editar», «+ Novo separador» divide uma ficha em separadores a que dá um nome (Personagem, Feitiços, Inventário…), que ordena e remove.',
            'Cada bloco escolhe o seu separador, e cada separador coloca os seus blocos nas suas próprias três colunas. Uma ficha existente mantém todos os blocos num primeiro separador.',
        ],
    ],

    '0.83.0' => [
        'date' => '2026-10-19',
        'title' => 'Várias notas e três colunas em As minhas fichas',
        'short' => '« As minhas fichas » aceita vários blocos de notas, em três colunas.',
        'items' => [
            '«+ Nova nota» adiciona um bloco de notas: quantos quiser (dívidas, rumores, contactos…). As suas notas anteriores passam a um primeiro bloco «Notas».',
            'Os blocos colocam-se agora em três colunas, notas incluídas, com «Organizar os blocos».',
        ],
    ],

    '0.82.0' => [
        'date' => '2026-10-19',
        'title' => 'Uma demo atualizada',
        'short' => 'A campanha de demonstração mostra as últimas novidades.',
        'items' => [
            'A campanha de demonstração mostra agora as últimas novidades: no mapa da campanha, o farol leva a um submapa, a sua cave, pronta para o combate com marcadores, dois moldes de área e uma ordem de iniciativa. Cada ponto liga várias fichas e várias cenas.',
            'Dados à mesa: lançamentos preparados que leem a característica da ficha, um bónus com nome e os lançamentos da sessão 1, incluindo um pedido do mestre.',
            'Também: um som de «Os meus sons», uma cena «Jogada como previsto» e outra com «O que aconteceu», uma pista «Mentira», uma ficha e uma regra da casa com a sua versão oficial, e jogadores que completam o próprio retrato.',
            'Volte a carregar a demonstração em «Minhas campanhas» para a descobrir: uma cópia já carregada fica como está.',
        ],
    ],

    '0.81.0' => [
        'date' => '2026-10-19',
        'title' => 'Mapa da campanha: zoom, mesa e locais mais ricos',
        'short' => 'O mapa da campanha permite zoom, e cada local leva às suas cenas, fichas ou a um submapa.',
        'items' => [
            'O mapa da campanha faz zoom (roda, + e −, pinça no tablet) e desloca-se arrastando. Conselho: uma imagem 4K (3840 × 2160) mantém-se nítida.',
            'Quando o mapa está no ecrã da mesa, o submapa aberto a partir de um ponto substitui-o lá, e o mapa de cima volta quando sobe. No submapa, «Este local» mantém as cenas e fichas do ponto à distância de um clique.',
            'Um ponto pode agora ligar-se a várias cenas e várias fichas: o local, os seus PNJ, todas as cenas que lá se jogam.',
        ],
    ],

    '0.80.0' => [
        'date' => '2026-10-19',
        'title' => 'Blocos, notas e caixas de seleção em As minhas fichas',
        'short' => '« As minhas fichas » organiza-se em blocos móveis, com uma nota e uma caixa por campo.',
        'items' => [
            'Em «As minhas fichas», os campos arrumam-se em blocos que nomeia: Características, Perícias, Equipamento, Feitiços…',
            '«Organizar os blocos»: arraste um bloco pela pega, ou use as setas, para o colocar onde quiser, numa ou duas colunas.',
            'Cada campo pode ter uma nota e uma caixa de seleção para marcar durante a partida: uma perícia bem-sucedida, um feitiço preparado.',
        ],
    ],

    '0.79.0' => [
        'date' => '2026-10-19',
        'title' => 'Biblioteca de demos',
        'short' => 'Uma biblioteca de demonstrações para descobrir e carregar.',
        'items' => [
            'Nova «Biblioteca de demos», a partir de «Minhas campanhas»: campanhas prontas a jogar (um cenário, uma aventura curta, personagens pré-gerados) de que carrega a sua própria cópia num clique.',
            'Cada demo de um autor ou editora mantém uma ligação «Comprar o jogo», na biblioteca e no topo da sua cópia.',
            'O preço do Premium aparece no botão de subscrição das Preferências: 4,99 € por mês.',
        ],
    ],

    '0.78.0' => [
        'date' => '2026-10-18',
        'title' => 'O que aconteceu, e o livro de aventuras',
        'short' => 'O livro de aventuras conta a campanha, cena após cena.',
        'items' => [
            'Cada cena tem agora duas partes: «O que está previsto» e «O que aconteceu». Se a mesa seguiu o plano, basta marcar «Jogada como previsto».',
            'Novo «Livro de aventuras» na campanha: as cenas jogadas, cenário a cenário, e depois os resumos das sessões, para ler, imprimir ou guardar em PDF.',
            'Pode juntar-se uma versão redigida, escrita por si ou pela IA da sua escolha (texto para copiar, ou a sua própria chave de API, a seu cargo). O livro continua reservado ao mestre.',
        ],
    ],

    '0.77.0' => [
        'date' => '2026-10-18',
        'title' => 'Organize os seus ecrãs à sua maneira',
        'short' => 'O Mestre organiza os seus ecrãs à sua maneira.',
        'items' => [
            'Novo botão «Organizar o ecrã» na página da campanha e no modo Sessão: arraste cada bloco pela sua pega ⠿, com o rato ou com o dedo, ou use as setas.',
            'No modo Sessão, um bloco pode passar de uma coluna para a outra: a música em cima, as notas à direita, como preferir.',
            '«Ocultar» retira um bloco que não usa, «Mostrar» trá-lo de volta, «Disposição predefinida» repõe tudo. Esta disposição é só sua: jogadores e co-mestres mantêm a deles.',
        ],
    ],

    '0.76.0' => [
        'date' => '2026-10-18',
        'title' => 'Sua ficha, mesmo sem rede',
        'short' => 'A sua ficha edita-se mesmo sem rede e sincroniza quando a ligação volta.',
        'items' => [
            'Offline, a ficha do seu personagem continua utilizável: ajuste seus contadores com − e +, edite os campos que o mestre permite e escreva notas.',
            'Tudo fica guardado no dispositivo e é enviado quando a conexão volta, campo por campo: um contador recebe o que você tirou ou acrescentou, e o que o mestre mudou nesse meio-tempo (um segredo revelado, um campo editado) é mantido.',
            'Depois do envio, um resumo diz o que foi atualizado e aponta qualquer campo que o mestre mudou ao mesmo tempo que você.',
        ],
    ],

    '0.75.0' => [
        'date' => '2026-10-18',
        'title' => 'O grande mapa da campanha',
        'short' => 'Um grande mapa da campanha, com os seus locais.',
        'items' => [
            'Novo «Mapa da campanha»: escolha um mapa como visão geral e coloque nele pontos de interesse, uma cidade, uma masmorra, o local de uma cena.',
            'Um ponto pode abrir um submapa com um clique, que tem os seus próprios pontos; o caminho de navegação permite voltar à visão geral.',
            'Ligados a uma cena, os pontos mostram o que foi jogado, está em curso ou ainda está por vir; ligados a uma ficha, abrem-na. Tudo fica com o mestre, exceto o nome dos pontos «Visíveis na mesa», mostrado no ecrã da mesa.',
        ],
    ],

    '0.74.0' => [
        'date' => '2026-10-18',
        'title' => 'As suas personagens jogadas noutro sítio',
        'short' => '« As minhas fichas » guarda as personagens que joga noutros sítios, só para si.',
        'items' => [
            'Nova página «As minhas fichas»: guarde as personagens que joga fora do SagaWyn, em casa de um amigo, num clube ou noutro sítio. Só você as vê.',
            'Cada personagem tem a sua ficha PDF, um retrato, campos livres (textos e contadores a ajustar com − e + durante a partida) e as suas notas, com o nome do jogo e da mesa para a encontrar.',
        ],
    ],

    '0.73.0' => [
        'date' => '2026-10-18',
        'title' => 'Uma biblioteca de sons para todas as suas campanhas',
        'short' => 'Uma biblioteca de sons pessoal, para todas as suas campanhas.',
        'items' => [
            'Nova página «Os meus sons»: as suas músicas e ambientes, guardados uma só vez na sua conta e depois adicionados às campanhas que conduz. O ficheiro não é copiado e só conta uma vez no seu espaço.',
            'Na página Sons de uma campanha: «Da minha biblioteca» para adicionar os seus sons, «Guardar na minha biblioteca» para reutilizar um som noutro lado, e uma caixa para guardar um novo envio diretamente na biblioteca.',
            'Guardar na biblioteca um som já enviado noutra campanha junta as duas cópias: as cenas mantêm a música e o espaço é libertado.',
        ],
    ],

    '0.72.0' => [
        'date' => '2026-10-18',
        'title' => 'O modo de usar em todo o lado',
        'short' => 'Um botão « ? » explica os ecrãs do SagaWyn.',
        'items' => [
            'O botão «?» chega aos sons, ao grafo de relações, à cronologia, às opiniões dos jogadores, ao assistente IA, ao registo e à ficha de personagem.',
            'No grafo, na cronologia e na ficha de personagem, os jogadores leem um modo de usar escrito para eles.',
        ],
    ],

    '0.71.1' => [
        'date' => '2026-10-18',
        'title' => 'Subtrair ou somar vários pontos de uma vez',
        'short' => 'Os contadores da sua personagem mudam vários pontos de uma vez.',
        'items' => [
            'Sob cada contador que o jogador pode alterar (PV, munição, dinheiro…), um campo «Quantidade»: escreve 8 e depois −, e o contador perde 8 de uma vez. O valor padrão é 1.',
        ],
    ],

    '0.71.0' => [
        'date' => '2026-10-18',
        'title' => 'Instruções em mais telas',
        'items' => [
            'O botão «?» aparece também na ficha (lado do Mestre), no modo Sessão, nos cenários e cenas, na importação de arquivos e nos membros da campanha.',
            'A página «Ajuda» agora explica a rolagem bruta e os bônus lidos na ficha do personagem.',
        ],
    ],

    '0.70.0' => [
        'date' => '2026-10-18',
        'title' => 'Instruções nas telas mais delicadas',
        'items' => [
            'Um botão «?» ao lado do título das telas que não se explicam sozinhas abre as instruções em poucas linhas: tipos de ficha, campos do jogo, tags, dados, mapas, controle remoto da tela da mesa, pistas e histórico de revelações.',
            'Cada instrução leva à pergunta correspondente da página «Ajuda», que abre direto nela.',
        ],
    ],

    '0.69.0' => [
        'date' => '2026-10-18',
        'title' => 'Bônus e limiar lidos na ficha',
        'short' => 'Os dados leem os bónus e os limiares na ficha da personagem.',
        'items' => [
            'Nos dados, o bônus pode pegar um ou mais valores da ficha do personagem (característica, perícia, qualquer campo número ou contador da zona pública), inteiros ou em dezena, metade ou quinto, além do bônus livre. O limiar também pode ser lido na ficha. Os valores são lidos no momento da rolagem, na ficha de cada personagem que rola.',
            'Vários bônus com nome (Arma +2, Ferimento −1…) se somam na mesma rolagem, junto com os valores da ficha.',
            'Os exemplos prontos saem da bandeja: ela mantém suas rolagens salvas.',
            'Uma rolagem salva é excluída com um toque: o × na etiqueta, ou «Excluir» depois de carregada.',
        ],
    ],

    '0.68.0' => [
        'date' => '2026-10-17',
        'title' => 'Rolagem bruta',
        'items' => [
            'Nova leitura «Bruto», escolhida por padrão: escolha o tipo e a quantidade de dados (1d20 + 2d6…), role, e cada dado aparece como caiu, sem total, sem limiar nem outra regra.',
        ],
    ],

    '0.67.0' => [
        'date' => '2026-10-17',
        'title' => 'Os seus conteúdos, os seus direitos',
        'items' => [
            'A página «Privacidade e informações legais» esclarece que o SagaWyn não é proprietário do que lá coloca: cada utilizador continua proprietário dos seus conteúdos e responsável pelo que publica.',
            'Novas secções: «Importação e estruturação de conteúdos», «Serviços de inteligência artificial», «Denunciar um conteúdo ilícito», «Disponibilidade e cópias de segurança» e «Jogos e marcas». As páginas de importação remetem para elas.',
        ],
    ],

    '0.66.0' => [
        'date' => '2026-10-17',
        'title' => 'Dados misturados e rolagens próprias',
        'short' => 'Vários tipos de dados no mesmo lançamento, e lançamentos só para si.',
        'items' => [
            'Uma mesma rolagem pode misturar vários tipos de dados: toque em um d20 e depois em dois d6, e são rolados 1d20 + 2d6. Cada tipo tem sua quantidade, que pode ser ajustada ou removida.',
            'O limiar vale para o total («Limiar sobre o total», atingido ou não ultrapassado) ou para cada dado («Limiar por dado»: por exemplo, todo dado com 4 ou mais é um sucesso).',
            'Cada pessoa, jogador ou Mestre, salva suas próprias rolagens em «Minhas rolagens», recarrega-as com um toque e as atualiza quando o personagem evolui. O Mestre mantém também as «Rolagens da campanha» para toda a mesa.',
        ],
    ],

    '0.65.1' => [
        'date' => '2026-10-17',
        'title' => 'Acessos verificados em todo o lado',
        'items' => [
            'Escrever um endereço à mão nunca abre uma campanha, uma ficha ou um ficheiro a que não se tem direito. Uma conta que não é membro recebe agora «Página não encontrada», como para um endereço que não existe: experimentar números nem sequer revela que uma campanha existe.',
        ],
    ],

    '0.65.0' => [
        'date' => '2026-10-17',
        'title' => 'Os dados na mesa',
        'short' => 'Um tabuleiro de dados partilhado à mesa, e o Mestre pode pedir-lhe um lançamento.',
        'items' => [
            'Novo botão «Dados» no rodapé de cada página da campanha: o número e o tipo de dados (de 4 a 100 faces, ou outro), e como lê-los: somar, atingir um limiar, ficar abaixo de um limiar, contar sucessos, manter os melhores ou os mais baixos, ler em algarismos. Com um bônus e dados que rolam de novo no máximo (+) ou no 1 (−).',
            'O Mestre pede uma rolagem a toda a mesa ou a alguns personagens, à vista de todos ou em discreto. Uma janela abre para os jogadores envolvidos; sem limiar, cada um informa o seu e soma seu bônus, e um limiar oculto fica com o Mestre, que vê quem rolou o quê.',
            'As rolagens à mesa rolam alguns segundos na tela da mesa; uma rolagem discreta só aparece para o Mestre. O Mestre salva suas rolagens habituais para toda a mesa, e os dados podem ser desligados em «Funções da campanha».',
        ],
    ],

    '0.64.1' => [
        'date' => '2026-10-17',
        'title' => 'Modelos medidos como a régua',
        'items' => [
            'Os modelos ajustam-se na mesma unidade que a régua (metros, pés…): o «Tamanho real de uma casa», em Grelha, calibra os dois, seja qual for o tamanho das casas no ecrã.',
            'Um botão «Eliminar» mais visível em cada modelo, e «Eliminar todos os modelos» no fim do feitiço.',
            'Cada ligação de «Juntar-se à distância» tem o seu código QR, para os jogadores o lerem com o telemóvel.',
        ],
    ],

    '0.64.0' => [
        'date' => '2026-10-17',
        'title' => 'Modelos de área no mapa',
        'short' => 'Modelos de área no mapa de combate.',
        'items' => [
            'Novos modelos na página do mapa: círculo, quadrado, retângulo, cone e linha, medidos em casas (ou na unidade da escala). Arrastam-se no mapa, orientam-se, ocultam-se, e a lista de tokens atingidos aparece logo.',
            'Os modelos visíveis aparecem no ecrã da mesa, por baixo dos tokens.',
        ],
    ],

    '0.63.0' => [
        'date' => '2026-10-17',
        'title' => 'Mapa de combate partilhado',
        'short' => 'Um mapa de combate partilhado: move o seu marcador, mesmo à distância.',
        'items' => [
            'No ecrã de mesa partilhado, cada jogador move a ficha da sua personagem. No mapa, «Movido por» confia outra ficha a um jogador: seguidor, familiar, inimigo controlado…',
            'Nova ordem de iniciativa, a partir do comando ou do mapa: PJ e inimigos com o seu valor, turno atual, rondas. O MJ mostra-a sobre o ecrã quando quiser; as linhas ocultas ficam com ele, e uma fila «Também presentes» mostra PNJ ou objetos sem iniciativa.',
            '«Juntar-se à distância» no comando: a ligação do ecrã para os jogadores e uma ligação de convidado sem conta, só de leitura e revogável, para quem só quer ver.',
        ],
    ],

    '0.62.0' => [
        'date' => '2026-10-13',
        'title' => 'A comunidade no Discord',
        'short' => 'A comunidade SagaWyn reúne-se no Discord.',
        'items' => [
            'O SagaWyn tem o seu servidor Discord: uma ligação no fundo de cada página e na página inicial para fazer perguntas, sugerir ideias e conhecer outros mestres de jogo.',
        ],
    ],

    '0.61.0' => [
        'date' => '2026-10-13',
        'title' => 'Modo demo antes do Kickstarter',
        'items' => [
            'Novo modo demo, a ativar em Administração › Planos: cada nova conta recebe o plano Demo, com a sua própria cópia da campanha de demonstração e um aviso que anuncia a próxima campanha de financiamento coletivo.',
            'A demonstração de uma conta demo está bloqueada: tudo pode ser visitado, mas só se usam a sessão, as notas, as fichas do mapa e o ecrã de mesa. A conta também pode criar uma pequena campanha própria (50 MB, 3 membros, sem IA nem exportação).',
            'Um convite de um administrador abre uma conta normal. Em «Contas», «Desbloquear» passa uma conta demo para o plano gratuito e inicia o seu período de teste, que a demo não gasta.',
        ],
    ],

    '0.60.0' => [
        'date' => '2026-10-13',
        'title' => 'Conteúdo caseiro',
        'short' => 'O Mestre distingue o conteúdo caseiro do conteúdo oficial.',
        'items' => [
            'Nova caixa «Conteúdo caseiro» nas fichas (PNJ, feitiços, objetos, lugares…): sem marcar, a ficha é oficial. Um distintivo «Caseiro» assinala-a na lista e na ficha, e um filtro Oficial / Caseiro ordena o universo.',
            'Para uma ficha caseira ou uma regra de origem «Caseira», a versão oficial substituída anota-se ao lado (por exemplo a iniciativa com 1d10 em vez de 1d20): fica na zona do MJ, recolhida sob a ficha ou a regra.',
            'A lista de regras filtra-se por origem. A exportação e a importação da campanha mantêm o conteúdo caseiro e a versão oficial.',
        ],
    ],

    '0.59.1' => [
        'date' => '2026-10-13',
        'title' => 'Verificação de publicação mais precisa',
        'items' => [
            'Administração: `sagawyn:check` deixa de indicar por engano um registo em modo de depuração quando LOG_LEVEL é warning.',
        ],
    ],

    '0.59.0' => [
        'date' => '2026-10-13',
        'title' => 'O logótipo da Autistic Intelligence',
        'items' => [
            'O logótipo da Autistic Intelligence, editora do SagaWyn, aparece agora no fundo das páginas, no ecrã de início de sessão e no aviso legal. Segue o tema claro ou escuro.',
        ],
    ],

    '0.58.0' => [
        'date' => '2026-10-12',
        'title' => 'Uma pista pode ser uma mentira',
        'items' => [
            'Pistas: nova natureza «Mentira», ao lado de Boato, Pista e Verdade, com o seu filtro. Como as outras naturezas, só o mestre a vê: o jogador a quem é revelada só lê a pista.',
        ],
    ],

    '0.57.0' => [
        'date' => '2026-10-12',
        'title' => 'Preparar uma campanha inteira com uma IA',
        'short' => 'Uma IA pode preparar uma campanha inteira para importar a partir de um livro de jogo.',
        'items' => [
            'Minhas campanhas → Importar uma campanha: nova ligação «Prepare o arquivo com uma IA». O prompt (ou o skill do Claude) faz a sua IA ler o livro de RPG e criar um arquivo .zip: o jogo, o mundo, a campanha, as fichas, as cenas e os documentos são criados de uma vez.',
        ],
    ],

    '0.56.1' => [
        'date' => '2026-10-11',
        'title' => 'Páginas de jogo e mundo legíveis no telemóvel',
        'items' => [
            'No telemóvel, a imagem de um jogo ou mundo aparece por cima do nome em largura total: o nome e a descrição já não ficam espremidos e o botão Editar já não os tapa.',
        ],
    ],

    '0.56.0' => [
        'date' => '2026-10-11',
        'title' => 'Os segredos passam a ser pistas',
        'short' => 'Os segredos passam a chamar-se pistas.',
        'items' => [
            'Os «segredos» da campanha agora se chamam «pistas»: página Pistas, painel das fichas, cenas e documentos, modo Sessão, busca, revelações e ajuda. Nada muda nos seus dados.',
        ],
    ],

    '0.54.0' => [
        'date' => '2026-10-11',
        'title' => 'Os jogadores editam a sua personagem',
        'short' => 'O Mestre pode deixá-lo editar a sua personagem.',
        'items' => [
            'Página Campos: «Editável pelo jogador» marca-se para um grupo inteiro (características, perícias…) ou para todos os campos públicos num clique.',
            'Nova opção na página Jogadores: o mestre pode deixar cada jogador editar o retrato, o resumo e a descrição da sua personagem. A zona do mestre continua escondida.',
        ],
    ],

    '0.51.0' => [
        'date' => '2026-10-11',
        'title' => 'Distintivo de Fundador e códigos de oferta',
        'short' => 'Um distintivo de Fundador e códigos de oferta Premium para oferecer.',
        'items' => [
            'Os apoiadores do Kickstarter podem ser marcados como Fundador na consola de administração: um distintivo ★ Fundador visível nas preferências, na lista de membros e na ficha da personagem.',
            'Os Fundadores têm armazenamento extra além do seu plano, gratuito ou premium (500 MB por omissão, ajustável em «Planos»).',
            'Nova página «As minhas recompensas» (a partir das preferências): a sua recompensa Kickstarter, códigos de oferta Premium para oferecer por link, com o nome de quem usou cada um, e um campo para usar um código recebido. Os meses somam-se ao fim do Premium atual.',
        ],
    ],

    '0.50.1' => [
        'date' => '2026-10-11',
        'title' => 'Saúde: filtro por função',
        'items' => [
            'No separador Saúde, um filtro por função da conta (sem administradores, administradores, premium, avaliação, gratuito).',
            'A campanha de demonstração deixa de ser contada nas estatísticas.',
        ],
    ],

    '0.50.0' => [
        'date' => '2026-10-10',
        'title' => 'Saúde da plataforma',
        'items' => [
            'Novo separador Saúde na consola de administração: pessoas ligadas e número de ligações por dia, semana ou mês, jogadores por campanha e por MJ, espaço ocupado (ficheiros, base, disco) e tempo de resposta do servidor hora a hora.',
            'A consola avisa quando o servidor abranda: mais de 5 % dos pedidos acima de um segundo em 24 horas.',
        ],
    ],

    '0.49.1' => [
        'date' => '2026-10-10',
        'title' => 'Uma ajuda mais completa',
        'items' => [
            'A ajuda responde a 19 novas perguntas: jogo, mundo e campanha, a demonstração, pesquisa e ligações, tags, cenários, modo Sessão, campos do jogo, regras, duplicação, trocas, a saída de um jogador, o diário, o fim do Premium e, para os jogadores: ficha, notas, mensagens e opinião.',
        ],
    ],

    '0.49.0' => [
        'date' => '2026-10-10',
        'title' => 'A demonstração mostra todo o SagaWyn',
        'short' => 'A campanha de demonstração usa todas as funções do SagaWyn.',
        'items' => [
            'A campanha de demonstração usa agora cada função: uma personagem jogadora tirada de uma pré-gerada, com o que recebeu (ficha, documento, regra, segredo, objetos) e que o MJ pode «Ver como»; notas de sessão; uma lista «Para jogar»; fichas fixadas; campos de referência, documento e comuns a dois tipos; um anexo do MJ; segredos de cada natureza; etiquetas coloridas; capas para o jogo e o mundo.',
            'Recarregar a demonstração reutiliza o tipo de ficha «Fação» já criado em vez de acrescentar outro.',
        ],
    ],

    '0.48.0' => [
        'date' => '2026-10-10',
        'title' => 'Uma demonstração mais completa',
        'items' => [
            'A campanha de demonstração «O Juramento de Pierrecendre» inclui agora três ambientes sonoros originais ligados às cenas, uma primeira sessão já jogada com o seu resumo e um pedido de opinião aos jogadores.',
            'Correção: a transição entre duas faixas já não interrompe a reprodução em alguns navegadores.',
        ],
    ],

    '0.47.0' => [
        'date' => '2026-10-10',
        'title' => 'LoreMundi passa a ser SagaWyn',
        'short' => 'O LoreMundi passa a chamar-se SagaWyn.',
        'items' => [
            'O LoreMundi chama-se agora SagaWyn, sempre publicado pela Autistic Intelligence. Every world has a story.',
            'As suas campanhas, contas e arquivos não mudam: as cópias feitas com o LoreMundi ou o CodexFlow continuam a ser importadas. Os ficheiros transferidos começam agora por «sagawyn-».',
        ],
    ],

    '0.46.0' => [
        'date' => '2026-10-10',
        'title' => 'Opiniões dos jogadores',
        'short' => 'O Mestre pode pedir a sua opinião sobre uma sessão ou uma campanha.',
        'items' => [
            'No fim de uma sessão ou campanha, peça aos seus jogadores uma nota de 1 a 5 estrelas, o que gostaram e o que poderia ser melhor («Opiniões dos jogadores» na campanha, ou a partir da página de uma sessão).',
            'Respostas anónimas ou assinadas, à escolha do Mestre: os jogadores sabem-no antes de responder. Vê a média, a distribuição das notas e todos os comentários.',
        ],
    ],

    '0.45.0' => [
        'date' => '2026-10-10',
        'title' => 'Biblioteca de som',
        'short' => 'Uma biblioteca de músicas e ambientes para as sessões.',
        'items' => [
            'Nova página «Sons»: envie as suas músicas e ambientes (mp3, ogg, m4a, wav, flac), organize-os com etiquetas e ligue-os às cenas.',
            'No modo Sessão, o bloco «Música» propõe primeiro os sons da cena: «Aqui» toca-os no seu dispositivo, sem parar quando muda de página; «Mesa» toca-os no ecrã da mesa, com pausa, repetição e volume controlados a partir da sessão.',
        ],
    ],

    '0.44.0' => [
        'date' => '2026-10-09',
        'title' => 'Pronto para publicar',
        'items' => [
            'A administração é avisada na consola quando a instalação tem um ponto bloqueante (depuração ativa, e-mail não configurado, tempo real não cifrado…).',
            'A base de dados e os ficheiros enviados são guardados todas as noites no servidor.',
        ],
    ],

    '0.43.0' => [
        'date' => '2026-10-09',
        'title' => 'Fiabilidade',
        'items' => [
            'Novos testes automáticos verificam num navegador real a atualização sem tempo real, «Lembrar-me», a ligação «Ir para o conteúdo», a apresentação no telemóvel com texto muito grande e uma função desativada pelo mestre com uma página aberta.',
        ],
    ],

    '0.42.0' => [
        'date' => '2026-10-09',
        'title' => 'Descrições formatadas',
        'items' => [
            'As descrições de jogo, mundo, cenário e documento têm o mesmo editor que as fichas: negrito, itálico, subtítulos, listas, citações e ligações [[ ]] para fichas numa campanha.',
        ],
    ],

    '0.41.0' => [
        'date' => '2026-10-09',
        'title' => 'Campos partilhados',
        'items' => [
            'Um campo pode abranger vários tipos de ficha ao mesmo tempo, por exemplo os pontos de vida de personagens e criaturas; os modelos de jogo e os ficheiros de campos mantêm esta escolha.',
        ],
    ],

    '0.40.0' => [
        'date' => '2026-10-09',
        'title' => 'Aplicação e segurança',
        'items' => [
            'A aplicação instalada no telemóvel tem uma descrição no seu idioma e um ícone adaptado aos ícones redondos do Android.',
            'Sem ligação, as páginas continuam legíveis e os botões de edição ficam a cinzento até a rede voltar.',
            'O navegador passa a aceitar apenas scripts, imagens e ligações vindos do próprio LoreMundi.',
        ],
    ],

    '0.39.0' => [
        'date' => '2026-10-09',
        'title' => 'Conta e administração',
        'items' => [
            'É possível relatar um problema sem conta, nas páginas de login e cadastro ou na ajuda, deixando um endereço para a resposta.',
            'As Preferências mostram a data de início da assinatura e, enquanto o pagamento não estiver disponível, que a mudança para Premium abrirá em breve.',
            'Console de administração: dias de acesso em vez de acessos, data do último acesso e alerta quando o envio push é impossível ou falha.',
        ],
    ],

    '0.38.0' => [
        'date' => '2026-10-09',
        'title' => 'Pesquisa',
        'short' => 'A pesquisa também encontra as outras formas de uma palavra.',
        'items' => [
            'A pesquisa também encontra outras formas francesas de uma palavra: «lanternes» encontra «lanterne», «éteinte» encontra «éteintes».',
            'A pesquisa na página inicial também procura nos seus mundos e jogos que não estão ligados a nenhuma campanha.',
        ],
    ],

    '0.37.0' => [
        'date' => '2026-10-09',
        'title' => 'Jogadores e papéis',
        'short' => 'Uma ficha revelada mostra as suas ilustrações, e a sua ficha tem o fio da campanha.',
        'items' => [
            'Uma ficha revelada mostra ao jogador suas ilustrações e arquivos públicos, e suas relações públicas com fichas que ele conhece.',
            'Os jogadores têm um «Feed da campanha» na ficha: sessões, eventos jogados conhecidos pela mesa e mensagens ao grupo.',
            'Os co-Mestres veem as páginas do jogo e do mundo somente leitura, baixam o arquivo e o modelo do jogo, e gerenciam os campos se o proprietário marcar em «Membros». Um co-Mestre rebaixado não mantém as notificações recebidas como Mestre.',
        ],
    ],

    '0.36.0' => [
        'date' => '2026-10-09',
        'title' => 'Conforto do Mestre e da sessão',
        'items' => [
            'As páginas de um PDF mostrado na mesa são viradas pelo controle remoto, pela página do documento ou com as setas da tela do Mestre, e os jogadores que seguem a tela viram junto. O leitor de PDF indica «página n / N» e permite ir a uma página.',
            'No modo Sessão, «Evento jogado» adiciona o texto digitado à cronologia, ligado à sessão e à cena em curso.',
            'Um personagem pode ser devolvido a qualquer um de seus antigos jogadores que volte à campanha, que recupera suas próprias trocas privadas com o Mestre, sem as dos jogadores intermediários.',
            'O status de uma ficha aparece sob o título; os pré-criados (etiqueta «pré-criado») aparecem primeiro em «Novo personagem»; os contadores da página Etiquetas listam os itens; uma demo carregada várias vezes numera sua campanha, jogo e mundo; o papel de espectador indica que ele vê a tela da mesa mesmo não compartilhada.',
        ],
    ],

    '0.35.0' => [
        'date' => '2026-10-09',
        'title' => 'Correções do teste de aceitação final',
        'items' => [
            'A página de uma sessão tem um resumo escrito pelo Mestre e mostra os acontecimentos jogados e tudo o que foi revelado ou dado durante a sessão.',
            'O histórico de uma ficha guarda também suas relações, arquivos anexados, tags e seu estado na campanha. A página de um mundo mostra sua história, e a de um jogo seus tipos de ficha.',
            'Uma função desativada pelo Mestre ou pelo plano também fica desativada nas páginas que ficaram abertas, e o modo Sessão abre quando os mapas estão desativados.',
            'Correções: quantidade anunciada numa troca, caixa sim/não nunca preenchida, página «não encontrada» traduzida, páginas legíveis no celular com texto grande, logins contados uma vez, ajuda atualizada.',
        ],
    ],

    '0.34.0' => [
        'date' => '2026-10-08',
        'title' => 'Funções por campanha',
        'short' => 'O Mestre escolhe as funções de que a sua mesa precisa.',
        'items' => [
            'Na página da campanha, o Mestre marca as funções de que a sua mesa precisa: ecrã de mesa, mapas, trocas entre jogadores, grafo, cronologia, assistente de IA. Uma função desmarcada desaparece para todos sem apagar nada; volta assim que for marcada de novo.',
        ],
    ],

    '0.33.0' => [
        'date' => '2026-10-08',
        'title' => 'Acabamentos',
        'items' => [
            'A pesquisa mostra o campo da ficha que contém a palavra encontrada, com o seu nome.',
            'Grafo: nomes e etiquetas sobrepostos são deslocados ou ocultados; passar sobre uma ficha fá-los reaparecer.',
            '«Revelar ou dar»: uma caixa «Todas as personagens ativas» marca toda a mesa de uma vez.',
            'Um jogador removido e convidado de novo recupera a sua antiga personagem com um clique, na página Personagens.',
            'Sem tempo real (servidor Reverb ausente ou em baixo), o sino, as mensagens e as fichas atualizam-se a cada 30 segundos.',
        ],
    ],

    '0.32.0' => [
        'date' => '2026-10-08',
        'title' => 'PDF e documentos',
        'short' => 'Os PDF abrem num leitor integrado, em todos os dispositivos.',
        'items' => [
            'Os PDF abrem num visualizador integrado, igual no computador, tablet e telemóvel, com zoom e transferência.',
            'No ecrã de mesa, um PDF é apresentado página a página, ajustado ao ecrã; as setas viram as páginas.',
            'A ficha de personagem mantém o nome de ficheiro original.',
            '«Usado por» indica o cenário de cada cena.',
            'As páginas de um jogo e de um mundo podem ter uma imagem.',
        ],
    ],

    '0.31.0' => [
        'date' => '2026-10-08',
        'title' => 'Funções Premium ✦',
        'short' => 'Uma estrela ✦ assinala as funções Premium, e nada é apagado no fim de uma subscrição.',
        'items' => [
            'Uma pequena estrela ✦ indica as funções Premium. Quando o plano do dono de uma campanha não as inclui, elas continuam visíveis, em cinza, com uma explicação.',
            'No fim de um teste ou de uma assinatura nada é apagado: campanhas, mapas, mensagens e arquivos continuam; só as funções ✦ são desligadas.',
            'O administrador pode oferecer um período de presente (Natal…) em que as contas gratuitas têm todas as funções Premium.',
        ],
    ],

    '0.30.0' => [
        'date' => '2026-10-08',
        'title' => 'Escrita e links',
        'items' => [
            'Os campos «texto longo» e as notas do Mestre das regras têm o editor rico e os links [[ ]].',
            'Na sua ficha, o jogador vê os textos longos formatados, com links para as fichas que o personagem conhece.',
            'A nota rápida da sessão sugere fichas assim que você digita «[[».',
            'As cópias são numeradas («cópia 2», «cópia 3») e uma importação nunca reutiliza o nome de um jogo, mundo ou campanha que você já tem.',
        ],
    ],

    '0.29.0' => [
        'date' => '2026-10-08',
        'title' => 'Segurança da conta',
        'items' => [
            'Um novo e-mail só é adotado depois de um clique no link que ele recebe; depois o endereço antigo é avisado.',
            'As tentativas nos formulários da conta são contadas por formulário e por e-mail, e a página de espera diz quantos segundos aguardar.',
            'Só os scripts do LoreMundi podem ser executados nas suas páginas.',
            'A exclusão da conta avisa que suas mensagens são apagadas.',
        ],
    ],

    '0.28.0' => [
        'date' => '2026-10-08',
        'title' => 'E-mails com as cores do LoreMundi',
        'items' => [
            'Os e-mails (senha esquecida, troca de endereço) trazem o logotipo e as cores do LoreMundi.',
            'Cada e-mail sai no idioma do destinatário, mesmo quando é o administrador que o envia.',
        ],
    ],

    '0.27.0' => [
        'date' => '2026-10-08',
        'title' => 'Uma vitrine pública',
        'items' => [
            'Uma página inicial apresenta o LoreMundi aos visitantes e aos buscadores, nos 8 idiomas.',
            'A ajuda pode ser lida sem conta e ganha uma seção «Sua conta»: planos, e-mail, senha, autenticação em dois fatores, dados.',
        ],
    ],

    '0.26.0' => [
        'date' => '2026-10-08',
        'title' => 'Correções do teste de aceitação v0.25.0',
        'items' => [
            'Uma página que continua aberta verifica de novo as suas permissões a cada ação: um jogador removido ou um co-mestre rebaixado não recebe mais nada novo.',
            'O jogador que recebe um personagem não lê mais a conversa privada do jogador anterior com o mestre.',
            'Os personagens prontos da campanha de demonstração aparecem em «Novo personagem».',
            'Autenticação de dois fatores: os códigos de recuperação funcionam no login, e o administrador pode removê-la de uma conta bloqueada.',
            'Novos ícones do LoreMundi (aba, aplicativo instalado, notificações).',
            'Tela da mesa compartilhada legível no celular; cabeçalhos de fichas e páginas corrigidos no celular e no tablet, inclusive com texto grande.',
            'Correções: notificações push sem erros, botões da janela «recebido», referências a fichas após renomear, quantidade de uma troca, régua dos mapas, aviso «Offline», mensagens de erro traduzidas, link «Ir para o conteúdo».',
        ],
    ],

    '0.25.0' => [
        'date' => '2026-10-08',
        'title' => 'Importar um livro de jogo com uma IA',
        'short' => 'Uma IA pode preparar os ficheiros de importação a partir de um livro de jogo.',
        'items' => [
            'Em «Importar», «Prepare os ficheiros com uma IA» dá-lhe um prompt para colar na IA que escolher com o PDF de um jogo ou cenário: ela prepara os ficheiros de importação (campos, fichas, regras, cenas) e um guia passo a passo. O prompt também existe como skill do Claude.',
        ],
    ],

    '0.24.0' => [
        'date' => '2026-10-08',
        'title' => 'Segurança e dados pessoais',
        'short' => 'Nas Preferências: a sua conta, uma autenticação de dois fatores opcional e os seus dados para descarregar.',
        'items' => [
            '«A minha conta», em Preferências: mude o nome, o e-mail (o endereço anterior é avisado) e a palavra-passe.',
            'Autenticação de dois fatores opcional, em Preferências: um código de uma aplicação do seu telemóvel, com códigos de recuperação.',
            '«Os meus dados», em Preferências: transfira o que o LoreMundi guarda sobre si ou elimine a sua conta.',
            'Palavras-passe com pelo menos 10 caracteres, com letras e números; mudar a sua termina a sessão nos outros dispositivos. A consola de administração volta a pedir a palavra-passe.',
            'Nova página «Privacidade e informações legais».',
            'Um jogador retirado da campanha, ou tornado espectador, deixa de jogar a sua personagem e deixa de receber o que lhe é revelado. Outras verificações de permissões foram reforçadas no servidor.',
        ],
    ],

    '0.23.0' => [
        'date' => '2026-10-08',
        'title' => 'CodexFlow passa a ser LoreMundi',
        'short' => 'O CodexFlow passa a chamar-se LoreMundi.',
        'items' => [
            'O CodexFlow chama-se agora LoreMundi, publicado pela Autistic Intelligence. Every world has a story.',
            'As suas campanhas, contas e arquivos não mudam: as cópias feitas com o CodexFlow continuam a ser importadas. Os ficheiros transferidos começam agora por «loremundi-».',
        ],
    ],

    '0.22.0' => [
        'date' => '2026-10-08',
        'title' => 'Backup completo',
        'short' => 'O Mestre pode descarregar uma cópia de segurança completa da sua campanha.',
        'items' => [
            'O proprietário pode baixar um backup completo: o arquivo da campanha com a mesa (personagens, o que receberam, sessões, notas, mensagens e registro), sem notas “Só eu” nem endereços de e-mail. Na importação, os personagens voltam sem jogador, prontos para serem atribuídos.',
        ],
    ],

    '0.21.0' => [
        'date' => '2026-10-08',
        'title' => 'Pesquisa a partir do início',
        'short' => 'A partir do início, a pesquisa percorre todas as suas campanhas.',
        'items' => [
            'Fora de uma campanha, a barra de pesquisa procura em todas as suas campanhas, seus mundos e jogos, cada uma com suas permissões: tudo como Mestre, o que seu personagem conhece como jogador.',
        ],
    ],

    '0.20.0' => [
        'date' => '2026-10-08',
        'title' => '“Citado em” completo, duplicação sem status',
        'items' => [
            '“Citado em” mostra também a linha do tempo, os segredos e os campos de outras fichas que mencionam a ficha.',
            'Uma campanha duplicada parte das fichas originais: o status “morto” ou “prisioneiro” não é mais copiado, a menos que você marque a caixa para mantê-lo.',
        ],
    ],

    '0.19.0' => [
        'date' => '2026-10-08',
        'title' => 'Segredos e novos campos',
        'items' => [
            'Cada segredo tem um tipo (boato, pista ou verdade) e um estado que depende de quem o conhece: oculto, parcial ou revelado. Você pode filtrar os segredos pelos dois.',
            'Três novos tipos de campo: link da web, arquivo (um documento da campanha) e referência a outra ficha, que continua ligada mesmo se a ficha for renomeada.',
            'Uma campanha duplicada mantém os links entre suas fichas copiadas.',
        ],
    ],

    '0.18.0' => [
        'date' => '2026-10-08',
        'title' => 'Novo personagem, Minhas campanhas, páginas de jogo e de mundo',
        'short' => 'Cada jogo e cada mundo tem a sua página, e « Minhas campanhas » leva-o de volta à última sessão.',
        'items' => [
            'Quando um jogador recebe um novo personagem, o Mestre marca o que passa do anterior: conhecimentos, informações, documentos e regras são copiados, os itens mudam de mãos.',
            'Minhas campanhas: botão “Retomar”, data da última sessão e campanhas arquivadas à parte.',
            'Cada jogo e cada mundo tem sua página: descrição, campanhas, regras, documentos, campos ou fichas reutilizáveis.',
        ],
    ],

    '0.17.0' => [
        'date' => '2026-10-08',
        'title' => 'Editor de texto e notas dos jogadores',
        'short' => 'Um editor de texto formatado, e notas de jogador ligadas às fichas conhecidas.',
        'items' => [
            'Os textos longos (descrições, notas do Mestre, cenas, regras, linha do tempo, notas dos jogadores) têm um editor com negrito, itálico, subtítulos, listas e citações; “[[” continua sugerindo fichas para vincular.',
            'Os jogadores vinculam suas notas às fichas que o personagem conhece, e só a elas.',
            'A página de uma sessão mostra também as notas que os jogadores fizeram durante ela, exceto as que guardam para si.',
        ],
    ],

    '0.16.0' => [
        'date' => '2026-10-08',
        'title' => 'Assinatura Premium e teste gratuito',
        'short' => 'Uma subscrição Premium, e seis semanas de experiência gratuita.',
        'items' => [
            'Assine o Premium em “Preferências”: pagamento mensal ou anual protegido pelo Stripe, com faturas e cancelamento no portal do Stripe. O Premium dura até o fim do período pago.',
            'Teste gratuito: seis semanas com todas as funções, a partir da sua primeira campanha como Mestre. Um jogador que nunca é Mestre não o inicia. A duração é ajustada no console de administração.',
            'Uma aba “Evoluções” no console de administração para manter o roteiro da plataforma.',
        ],
    ],

    '0.15.0' => [
        'date' => '2026-10-08',
        'title' => 'Console de administração e planos',
        'items' => [
            'Um console de administração: as contas com seu plano, as datas de assinatura, o armazenamento usado, se há chave de IA, as campanhas e os acessos, sem dados pessoais. O administrador define o plano de cada conta e pode enviar um link de redefinição de senha, sem nunca vê-la.',
            'Três planos: administrador, premium e gratuito. Armazenamento, número de campanhas e funções do plano gratuito são ajustados no console; jogar, ser co-mestre ou espectador nunca conta.',
            'O backlog reúne os problemas relatados, os bugs e as melhorias, com status, prioridade e versão da correção; as homologações ficam guardadas lá versão após versão. Seu plano aparece em “Preferências”.',
        ],
    ],

    '0.14.0' => [
        'date' => '2026-10-08',
        'title' => 'Após a homologação: trocas validadas pelo mestre',
        'short' => 'O Mestre valida as trocas entre jogadores.',
        'items' => [
            'Por padrão, o mestre valida as trocas entre jogadores: o item ou o conhecimento só muda de mãos depois que a troca é aceita. Uma caixa em “Personagens dos jogadores” permite autorizá-las direto.',
            'Controle remoto: no último elemento da cena, “Próximo” vira “Terminar” e limpa a tela.',
            'Diário mais legível para itens validados, mensagens mais claras em “Relatar um problema” e uma só forma de tratamento em cada idioma.',
        ],
    ],

    '0.13.1' => [
        'date' => '2026-10-08',
        'title' => 'Correções do teste de aceitação V1',
        'items' => [
            'A pesquisa do mestre também encontra segredos, informações e objetos dados, notas partilhadas dos jogadores e etiquetas de cena.',
            'Duplicar uma campanha copia os seus segredos e a cronologia preparada; uma ligação para uma ficha aberta durante a sessão mostra-se num painel lateral, sem sair da sessão.',
            'Páginas de erro traduzidas, e-mail da palavra-passe no seu idioma, modo Sessão e Documentos legíveis no telemóvel, um menu para as ligações escondidas em ecrãs pequenos.',
            'Uma personagem em repouso e um objeto validado pelo mestre já não podem ser alterados pelo jogador; a régua temporária do mapa apaga-se sozinha.',
        ],
    ],

    '0.13.0' => [
        'date' => '2026-10-08',
        'title' => 'A sua própria IA, sem copiar e colar',
        'short' => 'A sua própria chave de IA, para um assistente sem copiar e colar.',
        'items' => [
            'Em «Preferências» pode guardar uma chave de API em seu nome da Claude (Anthropic), ChatGPT (OpenAI) ou Le Chat (Mistral). O assistente IA propõe então «Analisar diretamente»: as propostas chegam sem copiar e colar.',
            'As chamadas são faturadas pelo fornecedor na sua conta. A chave é cifrada, nunca volta a ser mostrada nem exportada, e o modo «texto a colar» continua gratuito.',
        ],
    ],

    '0.12.0' => [
        'date' => '2026-10-08',
        'title' => 'Um assistente IA, sem subscrição',
        'short' => 'Um assistente de IA ajuda o Mestre a tirar partido das notas da sessão.',
        'items' => [
            'Nova ferramenta «Assistente IA» na campanha: o LoreMundi prepara um texto com as notas da sessão e o contexto da campanha, para colar na IA da sua escolha. A resposta, colada de volta, torna-se propostas: resumo, eventos jogados, relações, estados, notas de campanha, revelações.',
            'Cada proposta aceita-se, altera-se ou rejeita-se. Nada muda na campanha sem si, e as relações ou estados propostos ficam próprios da campanha, sem tocar no mundo partilhado.',
        ],
    ],

    '0.11.1' => [
        'date' => '2026-10-08',
        'title' => 'A demonstração no seu idioma',
        'items' => [
            'A campanha de demonstração existe nos oito idiomas da interface. Carrega-se no seu, ou no escolhido ao lado do botão.',
        ],
    ],

    '0.11.0' => [
        'date' => '2026-10-08',
        'title' => 'Uma campanha de demonstração',
        'short' => 'Uma campanha de demonstração para carregar com um clique.',
        'items' => [
            'Campanha de demonstração para carregar num clique a partir de «Minhas campanhas»: um jogo inventado, «Brume & Serment», e um enredo completo de três sessões, com fichas, retratos, relações, mapa, segredos, regras, cronologia e personagens pré-criadas.',
            'Os pequenos botões com ícone da página da campanha já não se deslocam ao passar o rato: o nome surge numa dica, por cima do resto.',
            'As etiquetas escrevem-se da mesma maneira em todo o lado, e uma ficha propõe «Adicionar uma etiqueta» logo por baixo do título.',
        ],
    ],

    '0.10.0' => [
        'date' => '2026-10-07',
        'title' => 'Uma página de campanha mais clara',
        'short' => 'Uma página de campanha mais clara.',
        'items' => [
            'A página da campanha arrumada: o modo Sessão em destaque, quatro zonas de preparação e as restantes ferramentas como pequenos botões com ícone.',
            'Ambiente do ecrã de mesa, à escolha no comando: Noite, Pergaminho, Ardósia ou Grimório.',
        ],
    ],

    '0.9.0' => [
        'date' => '2026-10-07',
        'title' => 'Levar a campanha consigo',
        'short' => 'Exportar e importar uma campanha inteira.',
        'items' => [
            'Exportar uma campanha inteira num arquivo .zip: jogo, mundo, fichas, cenários, documentos, mapas, segredos, cronologia e ficheiros.',
            'Importar um arquivo a partir de «Minhas campanhas»: recria a campanha, para si ou para outro mestre.',
            'Modelos de jogo partilháveis: tipos de ficha, campos, etiquetas e regras, sem conteúdo de campanha.',
        ],
    ],

    '0.8.0' => [
        'date' => '2026-10-07',
        'title' => 'O grafo e a cronologia',
        'short' => 'O grafo das relações e a cronologia.',
        'items' => [
            'Grafo de relações: todas as fichas ligadas, ou a rede à volta de uma ficha, com profundidade e filtro por tipo.',
            '«Ver como» no grafo: a rede tal como um personagem a conhece. Os jogadores acedem a partir do seu personagem.',
            'Cronologia: história do mundo, acontecimentos previstos e jogados, com datas livres como «Dia 3».',
            'Um acontecimento jogado anotado durante a sessão fica ligado à sessão e à cena em curso.',
        ],
    ],

    '0.7.0' => [
        'date' => '2026-10-07',
        'title' => 'Em volta da mesa',
        'short' => 'Mapas, marcadores e comando para o ecrã da mesa.',
        'items' => [
            'Mapas: uma imagem na tela da mesa, que você amplia e move, com uma grade quadriculada opcional e uma escala.',
            'Tokens opcionais, vinculados às fichas (nome e retrato): mover, redimensionar, mostrar ou ocultar dos jogadores.',
            'Régua temporária: trace uma linha e a distância aparece em quadrados ou em metros.',
            'Controle remoto: pelo celular, limpe a tela, passe para o próximo item da cena, controle o mapa.',
            'Jogadores: o que o Mestre revela ou dá a você aparece na hora, sem passar pelas notificações.',
        ],
    ],

    '0.6.0' => [
        'date' => '2026-10-07',
        'title' => 'A memória da campanha',
        'short' => 'Segredos revelados com um clique, e o histórico de quem soube o quê.',
        'items' => [
            'Segredos: uma informação à parte, vinculada a fichas, cenas ou documentos, revelada com um clique a um personagem ou à mesa toda.',
            'Histórico de revelações: quem soube o quê, quando, em qual sessão e em qual cena; cada revelação pode ser desfeita.',
            '«Ver como»: o Mestre vê a campanha exatamente como um personagem, somente leitura.',
            '«Citado em» também mostra as regras e as notas de sessão que mencionam uma ficha.',
            'Relações: o inverso («trabalha para» / «emprega») é preenchido automaticamente.',
        ],
    ],

    '0.5.0' => [
        'date' => '2026-10-07',
        'title' => 'Organizar melhor, em grupo',
        'short' => 'Etiquetas, duplicação, co-Mestre e espectadores.',
        'items' => [
            'Uma página «Tags» para renomear, colorir, mesclar e excluir suas tags; as cenas também têm tags.',
            '«Duplicar» uma ficha, um cenário ou uma campanha inteira, para jogar de novo com outra mesa.',
            'Novos papéis: co-Mestre, que prepara e conduz com você, e espectador, que assiste à tela da mesa.',
            '«Mostrar na mesa» a partir de uma ficha, um retrato, uma ilustração, um documento ou uma regra.',
            'Modo Sessão: mostrar uma ficha ou uma regra com um clique, ver a próxima cena, tecla N para anotar.',
        ],
    ],

    '0.4.1' => [
        'date' => '2026-10-07',
        'title' => 'Ajuda e relatos de problemas',
        'short' => 'Uma página de ajuda, e « Relatar um problema » em cada página.',
        'items' => [
            'Uma página «Ajuda» responde às perguntas mais frequentes, para o Mestre e para os jogadores.',
            '«Relatar um problema», no rodapé de cada página, envia sua mensagem à equipe junto com a página em questão.',
        ],
    ],

    '0.4.0' => [
        'date' => '2026-10-07',
        'title' => 'Todos os idiomas',
        'short' => 'A interface fala oito línguas.',
        'items' => [
            'A interface fala francês, inglês, alemão, espanhol, italiano, português, neerlandês e polonês.',
            'O idioma segue o do navegador; cada um pode escolhê-lo em «Preferências».',
            'As notificações chegam no idioma de quem as recebe.',
            'O tema escuro se mantém de uma página para outra.',
            'Depois de editar uma ficha a partir da página Personagens, você volta direto para ela.',
        ],
    ],

    '0.3.0' => [
        'date' => '2026-10-07',
        'title' => 'O vínculo vivo',
        'short' => 'Mensagens, notificações em direto, aplicação instalável e ecrã da mesa.',
        'items' => [
            'Mensagens entre o Mestre e os jogadores, e painel «Conversa» sempre à mão (grupo e privado).',
            'Notificações: revelações, objetos recebidos, mensagens, com um contador no cabeçalho.',
            'Tudo se atualiza ao vivo: mensagens, contadores, revelações, sem recarregar a página.',
            'O LoreMundi pode ser instalado como um aplicativo; a ficha do personagem continua legível offline; notificações no dispositivo.',
            'Tela da mesa: mapas, imagens, fichas e anúncios na TV ou no projetor, e compartilhada com os jogadores se o Mestre quiser.',
            'Os personagens dão objetos uns aos outros e passam adiante o que sabem.',
            'Os jogadores anotam seus conhecimentos e adicionam seus objetos; o Mestre valida os objetos.',
            'Tema escuro, cor de destaque e tamanho do texto em «Preferências».',
        ],
    ],

    '0.2.0' => [
        'date' => '2026-10-07',
        'title' => 'Os jogadores',
        'short' => 'Convites, personagens dos jogadores, revelações e registo.',
        'items' => [
            'Convites para os jogadores por link.',
            'Personagens dos jogadores: ficha, ficha em PDF, contadores (PV, magia, munição…) e campos editáveis pelo jogador.',
            'Revelações e «Dar»: conhecimentos, posses, documentos e regras.',
            'Espaço do jogador: notas privadas ou compartilhadas, intenções «Para jogar», diário do personagem.',
            'Diário de alterações: quem, o quê, quando, antes e depois.',
        ],
    ],

    '0.1.0' => [
        'date' => '2026-10-06',
        'title' => 'O Mestre sozinho',
        'short' => 'Mundos, fichas, cenas, modo Sessão e pesquisa para o Mestre.',
        'items' => [
            'Mundos, campanhas e fichas com zona pública e zona do Mestre, links [[ ]] entre fichas.',
            'Campos livres por jogo, tipos de ficha, importação CSV/JSON.',
            'Cenários, cenas, regras e biblioteca de documentos.',
            'Modo Sessão: cena em andamento, fichas úteis, notas rápidas, «Para jogar» e fixados.',
            'Busca global.',
        ],
    ],

];
