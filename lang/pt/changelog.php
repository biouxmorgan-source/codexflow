<?php

// Novidades por versão, da mais recente à mais antiga. Exibidas em
// «Novidades» (uma vez após cada atualização) e na página de mesmo nome.
return [

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
        'items' => [
            'A pesquisa também encontra outras formas francesas de uma palavra: «lanternes» encontra «lanterne», «éteinte» encontra «éteintes».',
            'A pesquisa na página inicial também procura nos seus mundos e jogos que não estão ligados a nenhuma campanha.',
        ],
    ],

    '0.37.0' => [
        'date' => '2026-10-09',
        'title' => 'Jogadores e papéis',
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
        'items' => [
            'Em «Importar», «Prepare os ficheiros com uma IA» dá-lhe um prompt para colar na IA que escolher com o PDF de um jogo ou cenário: ela prepara os ficheiros de importação (campos, fichas, regras, cenas) e um guia passo a passo. O prompt também existe como skill do Claude.',
        ],
    ],

    '0.24.0' => [
        'date' => '2026-10-08',
        'title' => 'Segurança e dados pessoais',
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
        'items' => [
            'O CodexFlow chama-se agora LoreMundi, publicado pela Autistic Intelligence. Every world has a story.',
            'As suas campanhas, contas e arquivos não mudam: as cópias feitas com o CodexFlow continuam a ser importadas. Os ficheiros transferidos começam agora por «loremundi-».',
        ],
    ],

    '0.22.0' => [
        'date' => '2026-10-08',
        'title' => 'Backup completo',
        'items' => [
            'O proprietário pode baixar um backup completo: o arquivo da campanha com a mesa (personagens, o que receberam, sessões, notas, mensagens e registro), sem notas “Só eu” nem endereços de e-mail. Na importação, os personagens voltam sem jogador, prontos para serem atribuídos.',
        ],
    ],

    '0.21.0' => [
        'date' => '2026-10-08',
        'title' => 'Pesquisa a partir do início',
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
