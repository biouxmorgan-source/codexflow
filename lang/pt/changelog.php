<?php

// Novidades por versão, da mais recente à mais antiga. Exibidas em
// «Novidades» (uma vez após cada atualização) e na página de mesmo nome.
return [

    '0.18.0' => [
        'date' => '2026-10-08',
        'title' => 'Novo personagem, Minhas campanhas, páginas de jogo e de mundo',
        'items' => [
            'Quando um jogador recebe um novo personagem, o Mestre marca o que passa do anterior: conhecimentos, informações, documentos e regras são copiados, os itens mudam de mãos.',
            'Minhas campanhas: botão “Retomar”, data da última sessão e campanhas arquivadas à parte.',
            'Cada jogo e cada mundo tem sua página: descrição, campanhas, regras, documentos, campos ou fichas reutilizáveis.',
        ],
    ],

    '0.17.0' => [
        'date' => '2026-10-08',
        'title' => 'Editor de texto e notas dos jogadores',
        'items' => [
            'Os textos longos (descrições, notas do Mestre, cenas, regras, linha do tempo, notas dos jogadores) têm um editor com negrito, itálico, subtítulos, listas e citações; “[[” continua sugerindo fichas para vincular.',
            'Os jogadores vinculam suas notas às fichas que o personagem conhece, e só a elas.',
            'A página de uma sessão mostra também as notas que os jogadores fizeram durante ela, exceto as que guardam para si.',
        ],
    ],

    '0.16.0' => [
        'date' => '2026-10-08',
        'title' => 'Assinatura Premium e teste gratuito',
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
        'items' => [
            'Em «Preferências» pode guardar uma chave de API em seu nome da Claude (Anthropic), ChatGPT (OpenAI) ou Le Chat (Mistral). O assistente IA propõe então «Analisar diretamente»: as propostas chegam sem copiar e colar.',
            'As chamadas são faturadas pelo fornecedor na sua conta. A chave é cifrada, nunca volta a ser mostrada nem exportada, e o modo «texto a colar» continua gratuito.',
        ],
    ],

    '0.12.0' => [
        'date' => '2026-10-08',
        'title' => 'Um assistente IA, sem subscrição',
        'items' => [
            'Nova ferramenta «Assistente IA» na campanha: o CodexFlow prepara um texto com as notas da sessão e o contexto da campanha, para colar na IA da sua escolha. A resposta, colada de volta, torna-se propostas: resumo, eventos jogados, relações, estados, notas de campanha, revelações.',
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
        'items' => [
            'Campanha de demonstração para carregar num clique a partir de «As minhas campanhas»: um jogo inventado, «Brume & Serment», e um enredo completo de três sessões, com fichas, retratos, relações, mapa, segredos, regras, cronologia e personagens pré-criadas.',
            'Os pequenos botões com ícone da página da campanha já não se deslocam ao passar o rato: o nome surge numa dica, por cima do resto.',
            'As etiquetas escrevem-se da mesma maneira em todo o lado, e uma ficha propõe «Adicionar uma etiqueta» logo por baixo do título.',
        ],
    ],

    '0.10.0' => [
        'date' => '2026-10-07',
        'title' => 'Uma página de campanha mais clara',
        'items' => [
            'A página da campanha arrumada: o modo Sessão em destaque, quatro zonas de preparação e as restantes ferramentas como pequenos botões com ícone.',
            'Ambiente do ecrã de mesa, à escolha no comando: Noite, Pergaminho, Ardósia ou Grimório.',
        ],
    ],

    '0.9.0' => [
        'date' => '2026-10-07',
        'title' => 'Levar a campanha consigo',
        'items' => [
            'Exportar uma campanha inteira num arquivo .zip: jogo, mundo, fichas, cenários, documentos, mapas, segredos, cronologia e ficheiros.',
            'Importar um arquivo a partir de «As minhas campanhas»: recria a campanha, para si ou para outro mestre.',
            'Modelos de jogo partilháveis: tipos de ficha, campos, etiquetas e regras, sem conteúdo de campanha.',
        ],
    ],

    '0.8.0' => [
        'date' => '2026-10-07',
        'title' => 'O grafo e a cronologia',
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
        'items' => [
            'Uma página «Ajuda» responde às perguntas mais frequentes, para o Mestre e para os jogadores.',
            '«Relatar um problema», no rodapé de cada página, envia sua mensagem à equipe junto com a página em questão.',
        ],
    ],

    '0.4.0' => [
        'date' => '2026-10-07',
        'title' => 'Todos os idiomas',
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
        'items' => [
            'Mensagens entre o Mestre e os jogadores, e painel «Conversa» sempre à mão (grupo e privado).',
            'Notificações: revelações, objetos recebidos, mensagens, com um contador no cabeçalho.',
            'Tudo se atualiza ao vivo: mensagens, contadores, revelações, sem recarregar a página.',
            'O CodexFlow pode ser instalado como um aplicativo; a ficha do personagem continua legível offline; notificações no dispositivo.',
            'Tela da mesa: mapas, imagens, fichas e anúncios na TV ou no projetor, e compartilhada com os jogadores se o Mestre quiser.',
            'Os personagens dão objetos uns aos outros e passam adiante o que sabem.',
            'Os jogadores anotam seus conhecimentos e adicionam seus objetos; o Mestre valida os objetos.',
            'Tema escuro, cor de destaque e tamanho do texto em «Preferências».',
        ],
    ],

    '0.2.0' => [
        'date' => '2026-10-07',
        'title' => 'Os jogadores',
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
        'items' => [
            'Mundos, campanhas e fichas com zona pública e zona do Mestre, links [[ ]] entre fichas.',
            'Campos livres por jogo, tipos de ficha, importação CSV/JSON.',
            'Cenários, cenas, regras e biblioteca de documentos.',
            'Modo Sessão: cena em andamento, fichas úteis, notas rápidas, «Para jogar» e fixados.',
            'Busca global.',
        ],
    ],

];
