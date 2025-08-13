<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prompt Builder IA - CentroService</title>
    <meta name="description" content="Gerador inteligente de prompts para IA - Stable Diffusion, Midjourney, Flux, VEO/Opal e mais">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/prompt-builder.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header Simplificado -->
    <header class="header-simple">
        <div class="header-container">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
            <h1 class="app-title">
                <i class="fas fa-magic"></i>
                Prompt Builder IA
            </h1>
            <div class="header-actions">
                <button class="btn-help" id="helpBtn">
                    <i class="fas fa-question-circle"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Container Principal -->
    <main class="main-container">
        
        <!-- Área de Progressão Compacta -->
        <section class="progress-compact">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="step-indicator">
                <span class="current-step">Etapa <span id="currentStepNumber">1</span> de 7</span>
                <span class="step-title" id="currentStepTitle">Tipo de Conteúdo</span>
            </div>
        </section>

        <!-- Layout Principal em 2 Colunas -->
        <div class="content-grid">
            
            <!-- Coluna Esquerda: Opções -->
            <section class="options-column">
                <div class="step-content" id="stepContent">
                    <!-- Conteúdo será carregado dinamicamente -->
                </div>
                
                <div class="navigation-compact">
                    <button class="btn-nav btn-prev" id="prevBtn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="nav-info">
                        <span id="navSteps">1 / 7</span>
                    </div>
                    <button class="btn-nav btn-next" id="nextBtn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </section>

            <!-- Coluna Direita: Prompt -->
            <section class="prompt-column">
                <div class="prompt-header-compact">
                    <h3>
                        <i class="fas fa-code"></i>
                        Prompt Gerado
                    </h3>
                    <div class="prompt-stats-compact">
                        <span id="charCount">0</span>
                        <span id="wordCount">0</span>
                        <span id="tokenEstimate">0</span>
                    </div>
                </div>
                
                <div class="prompt-textarea-container">
                    <textarea 
                        id="promptText" 
                        placeholder="Seu prompt será construído conforme você faz suas escolhas..."
                        readonly
                    ></textarea>
                </div>
                
                <div class="prompt-actions-compact">
                    <button class="btn-action btn-copy" id="copyBtn" title="Copiar Prompt">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button class="btn-action btn-clear" id="clearBtn" title="Limpar Tudo">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button class="btn-action btn-export" id="exportBtn" title="Exportar JSON">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </section>

        </div>

    </main>

    <!-- Toast para notificações -->
    <div class="toast" id="toast">
        <div class="toast-content">
            <i class="toast-icon"></i>
            <span class="toast-message"></span>
        </div>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal" id="helpModal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Como usar o Prompt Builder IA</h3>
                <button class="modal-close" id="closeHelp">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="help-content">
                    <div class="help-section">
                        <h4><i class="fas fa-list-ol"></i> Etapas</h4>
                        <p>Siga as 7 etapas para construir seu prompt perfeito. Você pode navegar entre as etapas a qualquer momento.</p>
                    </div>
                    <div class="help-section">
                        <h4><i class="fas fa-mouse-pointer"></i> Seleções</h4>
                        <p>Clique nos cards para fazer suas escolhas. Você pode selecionar múltiplas opções ou usar campos livres.</p>
                    </div>
                    <div class="help-section">
                        <h4><i class="fas fa-code"></i> Prompt</h4>
                        <p>Seu prompt é gerado automaticamente. Use os botões para copiar, limpar ou exportar em JSON.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/prompt-builder.js"></script>
    <script>
        // Implementação com subcategorias
        let currentStep = 1;
        let currentSubstep = 0; // 0 = categoria principal, 1+ = subcategorias
        let selectedChoices = {};
        let selectedSubcategories = {};

        const steps = {
            1: {
                title: 'Tipo de Conteúdo',
                description: 'Escolha o tipo de conteúdo que deseja gerar',
                icon: 'fas fa-image',
                options: [
                    { id: 'image', title: 'Imagem', description: 'Gerar imagem estática', icon: 'fas fa-image' },
                    { id: 'video', title: 'Vídeo', description: 'Gerar vídeo/animação', icon: 'fas fa-video' }
                ]
            },
            2: {
                title: 'Ambiente',
                description: 'Configure o cenário e localização da cena',
                icon: 'fas fa-globe',
                options: [
                    { 
                        id: 'natureza', 
                        title: 'Natureza', 
                        description: 'Ambientes naturais e paisagens', 
                        icon: 'fas fa-tree',
                        subcategories: [
                            { id: 'praia_tropical', title: 'Praia Tropical', description: 'Paraíso com palmeiras e águas cristalinas', icon: 'fas fa-umbrella-beach' },
                            { id: 'cachoeira_gigante', title: 'Cachoeira Gigante', description: 'Queda d\'água majestosa em penhasco', icon: 'fas fa-water' },
                            { id: 'montanha_nevada', title: 'Montanha Nevada', description: 'Picos cobertos de neve eterna', icon: 'fas fa-mountain' },
                            { id: 'deserto_sahara', title: 'Deserto do Sahara', description: 'Dunas infinitas sob sol escaldante', icon: 'fas fa-sun' },
                            { id: 'floresta_amazonica', title: 'Floresta Amazônica', description: 'Selva densa com biodiversidade', icon: 'fas fa-tree' },
                            { id: 'canyon_colorado', title: 'Canyon do Colorado', description: 'Formação rochosa estratificada', icon: 'fas fa-mountain' },
                            { id: 'lago_montanha', title: 'Lago de Montanha', description: 'Espelho d\'água em altitude', icon: 'fas fa-water' },
                            { id: 'campo_lavanda', title: 'Campo de Lavanda', description: 'Ondas roxas aromáticas', icon: 'fas fa-seedling' },
                            { id: 'vulcao_ativo', title: 'Vulcão Ativo', description: 'Cratera com lava incandescente', icon: 'fas fa-fire' },
                            { id: 'geleira_antartica', title: 'Geleira Antártica', description: 'Vastidão de gelo azul-cristalino', icon: 'fas fa-snowflake' },
                            { id: 'savana_africana', title: 'Savana Africana', description: 'Planície com acácias esparsas', icon: 'fas fa-tree' },
                            { id: 'fiorde_noruegues', title: 'Fiorde Norueguês', description: 'Vale glacial inundado', icon: 'fas fa-water' },
                            { id: 'bambuzal_asiatico', title: 'Bambuzal Asiático', description: 'Floresta de bambu zen', icon: 'fas fa-leaf' },
                            { id: 'oasis_desertico', title: 'Oásis Desértico', description: 'Refúgio verdejante no deserto', icon: 'fas fa-tint' },
                            { id: 'taiga_siberiana', title: 'Taiga Siberiana', description: 'Floresta boreal congelada', icon: 'fas fa-tree' }
                        ]
                    },
                    { 
                        id: 'urbano', 
                        title: 'Urbano', 
                        description: 'Ambientes de cidade e construções', 
                        icon: 'fas fa-city',
                        subcategories: [
                            { id: 'manhattan_ny', title: 'Manhattan NY', description: 'Selva de concreto e vidro', icon: 'fas fa-building' },
                            { id: 'tokyo_neon', title: 'Tóquio Neon', description: 'Metrópole futurística japonesa', icon: 'fas fa-city' },
                            { id: 'veneza_canais', title: 'Veneza dos Canais', description: 'Cidade aquática histórica', icon: 'fas fa-water' },
                            { id: 'paris_boulevards', title: 'Boulevards de Paris', description: 'Elegância urbana francesa', icon: 'fas fa-road' },
                            { id: 'favela_brasileira', title: 'Favela Brasileira', description: 'Comunidade colorida nas encostas', icon: 'fas fa-home' },
                            { id: 'dubai_futurista', title: 'Dubai Futurista', description: 'Oásis moderno no deserto', icon: 'fas fa-building' },
                            { id: 'mercado_marrakech', title: 'Mercado de Marrakech', description: 'Bazar árabe tradicional', icon: 'fas fa-store' },
                            { id: 'chinatown_hong_kong', title: 'Chinatown Hong Kong', description: 'Densidade urbana asiática', icon: 'fas fa-city' },
                            { id: 'estacao_central', title: 'Estação Central', description: 'Terminal ferroviário histórico', icon: 'fas fa-train' },
                            { id: 'ponte_golden_gate', title: 'Ponte Golden Gate', description: 'Ícone arquitetônico suspenso', icon: 'fas fa-bridge' },
                            { id: 'praca_publica', title: 'Praça Pública', description: 'Coração social da cidade', icon: 'fas fa-fountain' },
                            { id: 'beco_graffiti', title: 'Beco com Graffiti', description: 'Arte urbana em vielas', icon: 'fas fa-spray-can' },
                            { id: 'rooftop_urbano', title: 'Rooftop Urbano', description: 'Terraço com vista da cidade', icon: 'fas fa-building' },
                            { id: 'metro_subterraneo', title: 'Metrô Subterrâneo', description: 'Túneis de transporte urbano', icon: 'fas fa-subway' }
                        ]
                    },
                    { 
                        id: 'interior', 
                        title: 'Interior', 
                        description: 'Ambientes fechados e construções internas', 
                        icon: 'fas fa-home',
                        subcategories: [
                            { id: 'loft_industrial', title: 'Loft Industrial', description: 'Espaço amplo com estética fabril', icon: 'fas fa-industry' },
                            { id: 'penthouse_luxo', title: 'Penthouse de Luxo', description: 'Cobertura sofisticada', icon: 'fas fa-crown' },
                            { id: 'cozinha_rustica', title: 'Cozinha Rústica', description: 'Ambiente campestre acolhedor', icon: 'fas fa-utensils' },
                            { id: 'biblioteca_antiga', title: 'Biblioteca Antiga', description: 'Acervo centenário em madeira', icon: 'fas fa-book-open' },
                            { id: 'atelier_artista', title: 'Ateliê de Artista', description: 'Estúdio criativo com luz natural', icon: 'fas fa-palette' },
                            { id: 'spa_zen', title: 'Spa Zen', description: 'Santuário de relaxamento', icon: 'fas fa-leaf' },
                            { id: 'wine_cellar', title: 'Adega de Vinhos', description: 'Cave subterrânea para vinhos', icon: 'fas fa-wine-bottle' },
                            { id: 'home_theater', title: 'Home Theater', description: 'Cinema particular luxuoso', icon: 'fas fa-film' },
                            { id: 'greenhouse', title: 'Estufa/Greenhouse', description: 'Jardim interno climatizado', icon: 'fas fa-seedling' },
                            { id: 'laboratory', title: 'Laboratório', description: 'Espaço científico high-tech', icon: 'fas fa-flask' },
                            { id: 'dance_studio', title: 'Estúdio de Dança', description: 'Sala com espelhos e barras', icon: 'fas fa-music' },
                            { id: 'recording_studio', title: 'Estúdio de Gravação', description: 'Cabine acústica profissional', icon: 'fas fa-microphone' },
                            { id: 'game_room', title: 'Sala de Jogos', description: 'Ambiente gamer com neon', icon: 'fas fa-gamepad' },
                            { id: 'chapel_interior', title: 'Interior de Capela', description: 'Espaço sacro contemplativo', icon: 'fas fa-cross' }
                        ]
                    },
                    { 
                        id: 'fantasia', 
                        title: 'Fantasia', 
                        description: 'Ambientes mágicos e fantásticos', 
                        icon: 'fas fa-magic',
                        subcategories: [
                            { id: 'castelo_nuvens', title: 'Castelo nas Nuvens', description: 'Fortaleza flutuante etérea', icon: 'fas fa-cloud' },
                            { id: 'floresta_bioluminescente', title: 'Floresta Bioluminescente', description: 'Mata que brilha no escuro', icon: 'fas fa-tree' },
                            { id: 'portal_temporal', title: 'Portal Temporal', description: 'Passagem através do tempo', icon: 'fas fa-clock' },
                            { id: 'cidade_steampunk', title: 'Cidade Steampunk', description: 'Metrópole vitoriana futurista', icon: 'fas fa-cogs' },
                            { id: 'palacio_atlantico', title: 'Palácio Atlântico', description: 'Reino subaquático majestoso', icon: 'fas fa-fish' },
                            { id: 'caverna_dragoes', title: 'Caverna dos Dragões', description: 'Covil repleto de tesouros', icon: 'fas fa-dragon' },
                            { id: 'observatorio_espacial', title: 'Observatório Espacial', description: 'Torre celestial para as estrelas', icon: 'fas fa-telescope' },
                            { id: 'jardim_suspenso', title: 'Jardins Suspensos', description: 'Paraíso botânico aéreo', icon: 'fas fa-seedling' },
                            { id: 'biblioteca_infinita', title: 'Biblioteca Infinita', description: 'Acervo interdimensional', icon: 'fas fa-infinity' },
                            { id: 'vulcao_magico', title: 'Vulcão Mágico', description: 'Cratera com energia arcana', icon: 'fas fa-fire' },
                            { id: 'labirinto_cristal', title: 'Labirinto de Cristal', description: 'Maze refratário luminoso', icon: 'fas fa-gem' },
                            { id: 'nave_espacial', title: 'Nave Espacial', description: 'Interior futurístico alienígena', icon: 'fas fa-rocket' },
                            { id: 'dimensao_sombrias', title: 'Dimensão das Sombras', description: 'Plano etéreo sombrio', icon: 'fas fa-ghost' },
                            { id: 'templo_elementais', title: 'Templo dos Elementais', description: 'Santuário dos quatro elementos', icon: 'fas fa-yin-yang' }
                        ]
                    },
                    { 
                        id: 'historico', 
                        title: 'Histórico', 
                        description: 'Épocas e civilizações do passado', 
                        icon: 'fas fa-landmark',
                        subcategories: [
                            { id: 'coliseu_romano', title: 'Coliseu Romano', description: 'Arena gladiatorial épica', icon: 'fas fa-chess-rook' },
                            { id: 'piramide_egipcia', title: 'Pirâmide Egípcia', description: 'Monumento faraônico grandioso', icon: 'fas fa-mountain' },
                            { id: 'castelo_medieval', title: 'Castelo Medieval', description: 'Fortaleza da Idade Média', icon: 'fas fa-chess-rook' },
                            { id: 'templo_grego', title: 'Templo Grego', description: 'Arquitetura clássica ateniense', icon: 'fas fa-university' },
                            { id: 'vila_viking', title: 'Vila Viking', description: 'Assentamento nórdico guerreiro', icon: 'fas fa-ship' },
                            { id: 'pagode_chines', title: 'Pagode Chinês', description: 'Torre oriental tradicional', icon: 'fas fa-pagoda' },
                            { id: 'machu_picchu', title: 'Machu Picchu', description: 'Cidade inca perdida', icon: 'fas fa-mountain' },
                            { id: 'stonehenge', title: 'Stonehenge', description: 'Círculo megalítico misterioso', icon: 'fas fa-circle' },
                            { id: 'palacio_versalhes', title: 'Palácio de Versalhes', description: 'Opulência barroca francesa', icon: 'fas fa-crown' },
                            { id: 'cidade_pompeia', title: 'Cidade de Pompeia', description: 'Ruínas preservadas pelo vulcão', icon: 'fas fa-fire' },
                            { id: 'taj_mahal', title: 'Taj Mahal', description: 'Mausoléu de mármore indiano', icon: 'fas fa-mosque' },
                            { id: 'muralha_china', title: 'Muralha da China', description: 'Fortificação milenar serpenteante', icon: 'fas fa-wall-brick' }
                        ]
                    },
                    { 
                        id: 'futurista', 
                        title: 'Futurista', 
                        description: 'Visões do amanhã e ficção científica', 
                        icon: 'fas fa-rocket',
                        subcategories: [
                            { id: 'metropole_2080', title: 'Metrópole 2080', description: 'Cidade neo-futurística', icon: 'fas fa-city' },
                            { id: 'estacao_espacial', title: 'Estação Espacial', description: 'Habitat orbital avançado', icon: 'fas fa-satellite' },
                            { id: 'laboratorio_genetico', title: 'Laboratório Genético', description: 'Centro de bioengenharia', icon: 'fas fa-dna' },
                            { id: 'datacenter_quantic', title: 'Datacenter Quântico', description: 'Supercomputador holográfico', icon: 'fas fa-microchip' },
                            { id: 'fazenda_vertical', title: 'Fazenda Vertical', description: 'Agricultura hidropônica urbana', icon: 'fas fa-seedling' },
                            { id: 'portal_teletransporte', title: 'Portal Teletransporte', description: 'Tecnologia de viagem instantânea', icon: 'fas fa-bolt' },
                            { id: 'cidade_marte', title: 'Cidade em Marte', description: 'Colônia no planeta vermelho', icon: 'fas fa-globe-mars' },
                            { id: 'fabrica_robots', title: 'Fábrica de Robôs', description: 'Linha de produção automatizada', icon: 'fas fa-robot' },
                            { id: 'habitat_lunar', title: 'Habitat Lunar', description: 'Base científica na Lua', icon: 'fas fa-moon' },
                            { id: 'cybercafe_neural', title: 'Cybercafé Neural', description: 'Interface cérebro-computador', icon: 'fas fa-brain' },
                            { id: 'parque_holografico', title: 'Parque Holográfico', description: 'Recreação em realidade virtual', icon: 'fas fa-vr-cardboard' },
                            { id: 'nave_intergalactica', title: 'Nave Intergaláctica', description: 'Explorador do espaço profundo', icon: 'fas fa-space-shuttle' }
                        ]
                    },
                    { 
                        id: 'subaquatico', 
                        title: 'Subaquático', 
                        description: 'Mundos aquáticos e oceânicos', 
                        icon: 'fas fa-fish',
                        subcategories: [
                            { id: 'recife_coral', title: 'Recife de Coral', description: 'Ecossistema marinho colorido', icon: 'fas fa-seedling' },
                            { id: 'abismo_oceanico', title: 'Abismo Oceânico', description: 'Profundezas misteriosas', icon: 'fas fa-water' },
                            { id: 'navio_naufragado', title: 'Navio Naufragado', description: 'Destroços históricos submersos', icon: 'fas fa-ship' },
                            { id: 'cidade_atlantis', title: 'Cidade de Atlântis', description: 'Civilização subaquática perdida', icon: 'fas fa-city' },
                            { id: 'caverna_submarina', title: 'Caverna Submarina', description: 'Gruta inundada com ar', icon: 'fas fa-mountain' },
                            { id: 'kelp_forest', title: 'Floresta de Kelp', description: 'Algas gigantes ondulantes', icon: 'fas fa-tree' },
                            { id: 'fonte_termal', title: 'Fonte Termal Submarina', description: 'Oásis geotérmico no oceano', icon: 'fas fa-fire' },
                            { id: 'banco_tubaroes', title: 'Banco de Tubarões', description: 'Predadores em formação', icon: 'fas fa-fish' },
                            { id: 'jardim_anemonas', title: 'Jardim de Anêmonas', description: 'Flores do mar dançantes', icon: 'fas fa-seedling' },
                            { id: 'laboratorio_subaquatico', title: 'Laboratório Subaquático', description: 'Estação de pesquisa marinha', icon: 'fas fa-flask' },
                            { id: 'cemiterio_baleias', title: 'Cemitério de Baleias', description: 'Ossadas no fundo oceânico', icon: 'fas fa-skull' },
                            { id: 'vulcao_submarino', title: 'Vulcão Submarino', description: 'Erupção nas profundezas', icon: 'fas fa-fire' }
                        ]
                    },
                    { 
                        id: 'aereo', 
                        title: 'Aéreo', 
                        description: 'Alturas, céus e atmosfera', 
                        icon: 'fas fa-plane',
                        subcategories: [
                            { id: 'topo_everest', title: 'Topo do Everest', description: 'Pico mais alto do mundo', icon: 'fas fa-mountain' },
                            { id: 'balao_ar_quente', title: 'Balão de Ar Quente', description: 'Voo panorâmico sereno', icon: 'fas fa-hot-tub' },
                            { id: 'cockpit_avioes', title: 'Cockpit de Aviões', description: 'Cabine de comando aérea', icon: 'fas fa-plane' },
                            { id: 'base_nuvens', title: 'Base nas Nuvens', description: 'Plataforma flutuante etérea', icon: 'fas fa-cloud' },
                            { id: 'paraglider_voo', title: 'Voo de Paraglider', description: 'Planando entre montanhas', icon: 'fas fa-parachute-box' },
                            { id: 'tempestade_raios', title: 'Tempestade com Raios', description: 'Fenômeno elétrico atmosférico', icon: 'fas fa-bolt' },
                            { id: 'aurora_boreal', title: 'Aurora Boreal', description: 'Luzes dançantes polares', icon: 'fas fa-star' },
                            { id: 'dirigivel_vintage', title: 'Dirigível Vintage', description: 'Aeronave retrô elegante', icon: 'fas fa-plane' },
                            { id: 'plataforma_petroleo', title: 'Plataforma Petrolífera', description: 'Estrutura oceânica elevada', icon: 'fas fa-industry' },
                            { id: 'torre_controle', title: 'Torre de Controle', description: 'Central de tráfego aéreo', icon: 'fas fa-tower' },
                            { id: 'parapente_montanha', title: 'Parapente na Montanha', description: 'Voo livre alpino', icon: 'fas fa-mountain' },
                            { id: 'helicoptero_resgate', title: 'Helicóptero Resgate', description: 'Missão aérea heroica', icon: 'fas fa-helicopter' }
                        ]
                    }
                ]
            },
            3: {
                title: 'Estilo Visual',
                description: 'Defina o estilo artístico da criação',
                icon: 'fas fa-palette',
                options: [
                    { id: 'realista', title: 'Realista', description: 'Estilo fotográfico e realista', icon: 'fas fa-camera' },
                    { id: 'anime', title: 'Anime/Manga', description: 'Estilo japonês animado', icon: 'fas fa-star' },
                    { id: 'cartoon', title: 'Cartoon', description: 'Estilo de desenho animado', icon: 'fas fa-smile' },
                    { id: 'pintura', title: 'Pintura', description: 'Estilo de pintura artística', icon: 'fas fa-brush' },
                    { id: 'minimalista', title: 'Minimalista', description: 'Estilo limpo e simples', icon: 'fas fa-circle' },
                    { id: 'cyberpunk', title: 'Cyberpunk', description: 'Estilo futurístico e neon', icon: 'fas fa-robot' }
                ]
            },
            4: {
                title: 'Iluminação',
                description: 'Configure a iluminação da cena',
                icon: 'fas fa-lightbulb',
                options: [
                    { id: 'natural', title: 'Luz Natural', description: 'Iluminação natural do dia', icon: 'fas fa-sun' },
                    { id: 'dourada', title: 'Hora Dourada', description: 'Luz quente do pôr do sol', icon: 'fas fa-sunset' },
                    { id: 'noturna', title: 'Noturna', description: 'Iluminação noturna', icon: 'fas fa-moon' },
                    { id: 'neon', title: 'Neon', description: 'Luzes coloridas e vibrantes', icon: 'fas fa-bolt' },
                    { id: 'dramatica', title: 'Dramática', description: 'Contraste alto e sombras', icon: 'fas fa-theater-masks' },
                    { id: 'suave', title: 'Suave', description: 'Luz difusa e suave', icon: 'fas fa-cloud' }
                ]
            },
            5: {
                title: 'Qualidade e Técnica',
                description: 'Defina aspectos técnicos e de qualidade',
                icon: 'fas fa-cog',
                options: [
                    { id: '4k', title: '4K Ultra HD', description: 'Máxima qualidade de imagem', icon: 'fas fa-tv' },
                    { id: 'cinematic', title: 'Cinemático', description: 'Qualidade de cinema', icon: 'fas fa-film' },
                    { id: 'hdr', title: 'HDR', description: 'Alto contraste dinâmico', icon: 'fas fa-adjust' },
                    { id: 'macro', title: 'Macro', description: 'Detalhes extremamente próximos', icon: 'fas fa-search-plus' },
                    { id: 'panoramica', title: 'Panorâmica', description: 'Vista ampla e abrangente', icon: 'fas fa-expand-arrows-alt' },
                    { id: 'vintage', title: 'Vintage', description: 'Estilo retro e nostálgico', icon: 'fas fa-history' }
                ]
            },
            6: {
                title: 'Elementos Especiais',
                description: 'Adicione elementos extras à sua criação',
                icon: 'fas fa-stars',
                options: [
                    { id: 'particulas', title: 'Partículas', description: 'Efeitos de partículas flutuantes', icon: 'fas fa-sparkles' },
                    { id: 'reflexos', title: 'Reflexos', description: 'Reflexos em superfícies', icon: 'fas fa-mirror' },
                    { id: 'movimento', title: 'Movimento', description: 'Sensação de movimento dinâmico', icon: 'fas fa-running' },
                    { id: 'textura', title: 'Texturas', description: 'Texturas detalhadas e táteis', icon: 'fas fa-th' },
                    { id: 'profundidade', title: 'Profundidade', description: 'Efeito de profundidade de campo', icon: 'fas fa-layer-group' },
                    { id: 'atmosfera', title: 'Atmosfera', description: 'Elementos atmosféricos como névoa', icon: 'fas fa-cloud-meatball' }
                ]
            },
            7: {
                title: 'Seres',
                description: 'Defina os personagens e criaturas da sua criação',
                icon: 'fas fa-users',
                options: [
                    { 
                        id: 'humanos', 
                        title: 'Humanos', 
                        description: 'Personagens humanos em diferentes estilos', 
                        icon: 'fas fa-user',
                        subcategories: [
                            { id: 'homem_jovem', title: 'Homem Jovem', description: 'Entre 18-30 anos, físico atlético', icon: 'fas fa-male' },
                            { id: 'mulher_jovem', title: 'Mulher Jovem', description: 'Entre 18-30 anos, elegante e moderna', icon: 'fas fa-female' },
                            { id: 'homem_maduro', title: 'Homem Maduro', description: 'Entre 40-60 anos, experiente e confiante', icon: 'fas fa-user-tie' },
                            { id: 'mulher_madura', title: 'Mulher Madura', description: 'Entre 40-60 anos, sofisticada e sábia', icon: 'fas fa-user-graduate' },
                            { id: 'crianca_menino', title: 'Criança Menino', description: 'Entre 5-12 anos, brincalhão e curioso', icon: 'fas fa-child' },
                            { id: 'crianca_menina', title: 'Criança Menina', description: 'Entre 5-12 anos, alegre e expressiva', icon: 'fas fa-baby' },
                            { id: 'idoso', title: 'Idoso', description: 'Acima de 65 anos, sábio e respeitável', icon: 'fas fa-user-clock' },
                            { id: 'idosa', title: 'Idosa', description: 'Acima de 65 anos, carinhosa e experiente', icon: 'fas fa-female' },
                            { id: 'executivo', title: 'Executivo', description: 'Profissional em traje formal', icon: 'fas fa-briefcase' },
                            { id: 'artista', title: 'Artista', description: 'Criativo com estilo bohemio', icon: 'fas fa-palette' },
                            { id: 'atleta', title: 'Atleta', description: 'Físico musculoso e definido', icon: 'fas fa-running' },
                            { id: 'estudante', title: 'Estudante', description: 'Jovem acadêmico com livros', icon: 'fas fa-graduation-cap' }
                        ]
                    },
                    { 
                        id: 'animais', 
                        title: 'Animais', 
                        description: 'Criaturas do reino animal', 
                        icon: 'fas fa-paw',
                        subcategories: [
                            { id: 'cachorro_labrador', title: 'Cachorro Labrador', description: 'Cão amigável e leal, porte grande', icon: 'fas fa-dog' },
                            { id: 'gato_persa', title: 'Gato Persa', description: 'Felino elegante de pelo longo', icon: 'fas fa-cat' },
                            { id: 'cavalo_arabe', title: 'Cavalo Árabe', description: 'Equino nobre e majestoso', icon: 'fas fa-horse' },
                            { id: 'leao_africano', title: 'Leão Africano', description: 'Rei da selva, poderoso e imponente', icon: 'fas fa-chess-king' },
                            { id: 'aguia_real', title: 'Águia Real', description: 'Ave de rapina majestosa em voo', icon: 'fas fa-dove' },
                            { id: 'lobo_cinzento', title: 'Lobo Cinzento', description: 'Predador selvagem e astuto', icon: 'fas fa-wolf-pack-battalion' },
                            { id: 'urso_pardo', title: 'Urso Pardo', description: 'Gigante da floresta, forte e imponente', icon: 'fas fa-bear' },
                            { id: 'elefante_africano', title: 'Elefante Africano', description: 'Colosso gentil com presas de marfim', icon: 'fas fa-elephant' },
                            { id: 'golfinho_nariz_garrafa', title: 'Golfinho Nariz-de-garrafa', description: 'Mamífero marinho inteligente', icon: 'fas fa-fish' },
                            { id: 'tigre_siberiano', title: 'Tigre Siberiano', description: 'Felino listrado feroz e solitário', icon: 'fas fa-cat' },
                            { id: 'panda_gigante', title: 'Panda Gigante', description: 'Urso preto e branco adorável', icon: 'fas fa-yin-yang' },
                            { id: 'coruja_buraqueira', title: 'Coruja Buraqueira', description: 'Ave noturna sábia e observadora', icon: 'fas fa-eye' }
                        ]
                    },
                    { 
                        id: 'fantasticos', 
                        title: 'Seres Fantásticos', 
                        description: 'Criaturas mágicas e mitológicas', 
                        icon: 'fas fa-dragon',
                        subcategories: [
                            { id: 'dragao_fogo', title: 'Dragão de Fogo', description: 'Criatura alada que cospe chamas', icon: 'fas fa-fire' },
                            { id: 'unicornio_branco', title: 'Unicórnio Branco', description: 'Cavalo mágico com chifre espiralado', icon: 'fas fa-horse-head' },
                            { id: 'fenix_dourada', title: 'Fênix Dourada', description: 'Ave imortal que renasce das cinzas', icon: 'fas fa-dove' },
                            { id: 'elfo_florestal', title: 'Elfo Florestal', description: 'Ser mágico guardião da natureza', icon: 'fas fa-tree' },
                            { id: 'sereia_oceano', title: 'Sereia do Oceano', description: 'Meio mulher, meio peixe, encantadora', icon: 'fas fa-fish' },
                            { id: 'centauro_guerreiro', title: 'Centauro Guerreiro', description: 'Meio homem, meio cavalo, nobre', icon: 'fas fa-chess-knight' },
                            { id: 'grifo_real', title: 'Grifo Real', description: 'Meio águia, meio leão, majestoso', icon: 'fas fa-crow' },
                            { id: 'pegasus_alado', title: 'Pégasus Alado', description: 'Cavalo branco com asas divinas', icon: 'fas fa-feather' },
                            { id: 'minotauro_labirinto', title: 'Minotauro do Labirinto', description: 'Meio homem, meio touro, guardião', icon: 'fas fa-chess-rook' },
                            { id: 'sphinx_enigmatica', title: 'Esfinge Enigmática', description: 'Criatura com corpo de leão e cabeça humana', icon: 'fas fa-question-circle' },
                            { id: 'anjo_guardiao', title: 'Anjo Guardião', description: 'Ser celestial com asas luminosas', icon: 'fas fa-angel' },
                            { id: 'demonio_sombras', title: 'Demônio das Sombras', description: 'Entidade sombria com chifres', icon: 'fas fa-ghost' }
                        ]
                    },
                    { 
                        id: 'robots', 
                        title: 'Robôs e IA', 
                        description: 'Seres artificiais e tecnológicos', 
                        icon: 'fas fa-robot',
                        subcategories: [
                            { id: 'androide_humanoid', title: 'Androide Humanóide', description: 'Robô com aparência humana avançada', icon: 'fas fa-user-astronaut' },
                            { id: 'robo_combate', title: 'Robô de Combate', description: 'Máquina de guerra blindada', icon: 'fas fa-shield-alt' },
                            { id: 'cyborg_militar', title: 'Cyborg Militar', description: 'Humano com implantes cibernéticos', icon: 'fas fa-cogs' },
                            { id: 'ia_holografica', title: 'IA Holográfica', description: 'Inteligência artificial em projeção', icon: 'fas fa-cube' },
                            { id: 'robo_assistente', title: 'Robô Assistente', description: 'Ajudante doméstico amigável', icon: 'fas fa-hands-helping' },
                            { id: 'mech_gigante', title: 'Mech Gigante', description: 'Robô pilotado de grande porte', icon: 'fas fa-robot' },
                            { id: 'nano_bots', title: 'Nano-bots', description: 'Enxame de micro-robôs', icon: 'fas fa-microchip' },
                            { id: 'robo_explorador', title: 'Robô Explorador', description: 'Máquina para expedições', icon: 'fas fa-search' },
                            { id: 'synth_avatar', title: 'Avatar Sintético', description: 'Corpo artificial para consciência digital', icon: 'fas fa-user-circle' },
                            { id: 'guardian_ai', title: 'IA Guardiã', description: 'Inteligência protetora da humanidade', icon: 'fas fa-shield-check' },
                            { id: 'worker_bot', title: 'Robô Operário', description: 'Máquina industrial especializada', icon: 'fas fa-hard-hat' },
                            { id: 'companion_droid', title: 'Droide Companheiro', description: 'Robô de companhia emocional', icon: 'fas fa-heart' }
                        ]
                    },
                    { 
                        id: 'aliens', 
                        title: 'Aliens', 
                        description: 'Seres extraterrestres de outros mundos', 
                        icon: 'fas fa-user-astronaut',
                        subcategories: [
                            { id: 'grey_classico', title: 'Grey Clássico', description: 'Alien pequeno, pele cinza, olhos grandes', icon: 'fas fa-eye' },
                            { id: 'reptiliano_verde', title: 'Reptiliano Verde', description: 'Humanoide com características de réptil', icon: 'fas fa-dragon' },
                            { id: 'nordico_alto', title: 'Nórdico Alto', description: 'Alien humanóide loiro e alto', icon: 'fas fa-user-tie' },
                            { id: 'insectoide_mantis', title: 'Insectóide Mantis', description: 'Criatura com características de inseto', icon: 'fas fa-bug' },
                            { id: 'cristalino_energia', title: 'Ser Cristalino', description: 'Entidade feita de energia cristalizada', icon: 'fas fa-gem' },
                            { id: 'aquatico_tentaculos', title: 'Aquático Tentáculos', description: 'Ser marinho com múltiplos tentáculos', icon: 'fas fa-octopus' },
                            { id: 'gasoso_eterico', title: 'Ser Gasoso', description: 'Entidade semi-transparente etérea', icon: 'fas fa-cloud' },
                            { id: 'mecanico_hibrido', title: 'Mecânico Híbrido', description: 'Alien com partes orgânicas e mecânicas', icon: 'fas fa-cogs' },
                            { id: 'avatar_azul', title: 'Avatar Azul', description: 'Humanoide alto de pele azul', icon: 'fas fa-user-circle' },
                            { id: 'shapeshifter', title: 'Metamorfo', description: 'Ser capaz de mudar de forma', icon: 'fas fa-exchange-alt' },
                            { id: 'energy_being', title: 'Ser de Energia', description: 'Entidade de energia pura luminosa', icon: 'fas fa-bolt' },
                            { id: 'plant_alien', title: 'Alien Vegetal', description: 'Criatura com características vegetais', icon: 'fas fa-leaf' }
                        ]
                    }
                ]
            }
        };

        function loadStep(step, substep = 0) {
            const stepData = steps[step];
            if (!stepData) return;

            currentStep = step;
            currentSubstep = substep;

            const stepContent = document.getElementById('stepContent');

            if (substep === 0) {
                // Mostrar categorias principais
                stepContent.innerHTML = `
                    <h2 class="step-title">
                        <i class="${stepData.icon}"></i>
                        ${stepData.title}
                    </h2>
                    <p class="step-description">${stepData.description}</p>
                    <div class="options-grid">
                        ${stepData.options.map(option => `
                            <div class="option-card" onclick="selectCategory('${option.id}')" data-option="${option.id}">
                                <div class="option-icon">
                                    <i class="${option.icon}"></i>
                                </div>
                                <div class="option-title">${option.title}</div>
                                <div class="option-description">${option.description}</div>
                                ${option.subcategories ? '<div class="has-subcategories"><i class="fas fa-chevron-right"></i></div>' : ''}
                            </div>
                        `).join('')}
                    </div>
                `;

                // Restaurar seleção da categoria principal
                if (selectedChoices[step]) {
                    const selectedCard = document.querySelector(`[data-option="${selectedChoices[step]}"]`);
                    if (selectedCard) {
                        selectedCard.classList.add('selected');
                    }
                }
            } else {
                // Mostrar subcategorias
                const selectedCategory = selectedChoices[step];
                const categoryData = stepData.options.find(opt => opt.id === selectedCategory);
                
                if (categoryData && categoryData.subcategories) {
                    stepContent.innerHTML = `
                        <div class="breadcrumb">
                            <button onclick="loadStep(${step}, 0)" class="breadcrumb-btn">
                                <i class="fas fa-arrow-left"></i>
                                ${stepData.title}
                            </button>
                            <span class="breadcrumb-separator">/</span>
                            <span class="breadcrumb-current">${categoryData.title}</span>
                        </div>
                        <h2 class="step-title">
                            <i class="${categoryData.icon}"></i>
                            ${categoryData.title}
                        </h2>
                        <p class="step-description">Escolha um tipo específico de ${categoryData.title.toLowerCase()}</p>
                        <div class="options-grid">
                            ${categoryData.subcategories.map(subcat => `
                                <div class="option-card" onclick="selectSubcategory('${subcat.id}')" data-option="${subcat.id}">
                                    <div class="option-icon">
                                        <i class="${subcat.icon}"></i>
                                    </div>
                                    <div class="option-title">${subcat.title}</div>
                                    <div class="option-description">${subcat.description}</div>
                                </div>
                            `).join('')}
                        </div>
                    `;

                    // Restaurar seleção da subcategoria
                    if (selectedSubcategories[step]) {
                        const selectedCard = document.querySelector(`[data-option="${selectedSubcategories[step]}"]`);
                        if (selectedCard) {
                            selectedCard.classList.add('selected');
                        }
                    }
                }
            }

            // Atualizar indicadores
            document.getElementById('currentStepNumber').textContent = step;
            document.getElementById('currentStepTitle').textContent = substep > 0 ? 
                `${stepData.title} - ${stepData.options.find(opt => opt.id === selectedChoices[step])?.title || ''}` : 
                stepData.title;
            document.getElementById('navSteps').textContent = `${step} / 7`;
            
            // Atualizar barra de progresso
            const progressFill = document.getElementById('progressFill');
            progressFill.style.width = `${(step / 7) * 100}%`;

            // Atualizar botões de navegação
            const hasSubcategories = substep === 0 && selectedChoices[step] && 
                stepData.options.find(opt => opt.id === selectedChoices[step])?.subcategories;
            
            document.getElementById('prevBtn').disabled = step === 1 && substep === 0;
            document.getElementById('nextBtn').disabled = (step === 7 && substep === 0) || 
                (hasSubcategories && !selectedSubcategories[step]);
        }

        function selectCategory(categoryId) {
            // Remover seleção anterior
            document.querySelectorAll('.option-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Adicionar seleção atual
            const selectedCard = document.querySelector(`[data-option="${categoryId}"]`);
            if (selectedCard) {
                selectedCard.classList.add('selected');
            }

            // Salvar escolha
            selectedChoices[currentStep] = categoryId;

            // Verificar se tem subcategorias
            const stepData = steps[currentStep];
            const categoryData = stepData.options.find(opt => opt.id === categoryId);
            
            if (categoryData && categoryData.subcategories) {
                // Ir para subcategorias
                setTimeout(() => {
                    loadStep(currentStep, 1);
                }, 300);
            } else {
                // Não tem subcategorias, atualizar prompt
                updatePrompt();
            }

            console.log('Selected category:', categoryId, 'for step:', currentStep);
        }

        function selectSubcategory(subcategoryId) {
            // Remover seleção anterior
            document.querySelectorAll('.option-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Adicionar seleção atual
            const selectedCard = document.querySelector(`[data-option="${subcategoryId}"]`);
            if (selectedCard) {
                selectedCard.classList.add('selected');
            }

            // Salvar escolha
            selectedSubcategories[currentStep] = subcategoryId;

            // Atualizar prompt
            updatePrompt();

            console.log('Selected subcategory:', subcategoryId, 'for step:', currentStep);
        }

        function updatePrompt() {
            let prompt = '';
            let parts = [];
            
            // Etapa 1: Tipo de Conteúdo
            if (selectedChoices[1] === 'image') {
                prompt = 'Crie uma imagem';
            } else if (selectedChoices[1] === 'video') {
                prompt = 'Crie um vídeo';
            }

            // Etapa 2: Ambiente
            if (selectedChoices[2]) {
                let ambienteText = '';
                
                if (selectedSubcategories[2]) {
                    // Usar subcategoria específica
                    const ambientesDetalhados = {
                        // Natureza
                        'praia': 'em uma praia paradisíaca com areia dourada e ondas cristalinas',
                        'cachoeira': 'próximo a uma majestosa cachoeira em meio à mata tropical',
                        'montanha': 'em paisagens montanhosas com picos imponentes e vales verdejantes',
                        'deserto': 'em um vasto deserto com dunas douradas sob céu azul infinito',
                        'floresta': 'em uma densa floresta com árvores centenárias e luz filtrada',
                        'canyon': 'em um impressionante canyon com formações rochosas esculpidas pelo tempo',
                        'lago': 'às margens de um lago sereno com águas espelhadas',
                        'campo_flores': 'em um campo florido multicolorido que se estende até o horizonte',
                        
                        // Urbano
                        'centro_cidade': 'no movimentado centro urbano com arranha-céus e vida pulsante',
                        'arranha_ceus': 'entre imponentes arranha-céus modernos de vidro e aço',
                        'rua_comercial': 'em uma animada rua comercial com vitrines iluminadas',
                        'ponte': 'em uma elegante ponte com vista panorâmica da cidade',
                        'parque_urbano': 'em um tranquilo parque urbano cercado pela arquitetura da cidade',
                        'estacao_trem': 'na atmosférica estação ferroviária com arquitetura clássica',
                        'aeroporto': 'no moderno terminal aeroportuário com design futurístico',
                        'porto': 'no vibrante porto marítimo com navios e atividade portuária',
                        
                        // Interior
                        'sala_estar': 'em uma aconchegante sala de estar com decoração contemporânea',
                        'cozinha': 'em uma moderna cozinha gourmet com equipamentos de alto padrão',
                        'quarto': 'em um sereno quarto com ambiente relaxante e iluminação suave',
                        'escritorio': 'em um elegante escritório com mobiliário sofisticado',
                        'biblioteca': 'em uma majestosa biblioteca repleta de livros centenários',
                        'atelier': 'em um inspirador ateliê artístico banhado por luz natural',
                        'cafe': 'em uma charmosa cafeteria com atmosfera acolhedora',
                        'museu': 'em um prestigioso museu com arquitetura imponente',
                        
                        // Fantasia
                        'castelo_magico': 'em um majestoso castelo mágico flutuando nas nuvens',
                        'floresta_encantada': 'em uma floresta encantada habitada por seres místicos',
                        'portal_dimensional': 'diante de um portal dimensional cintilante entre realidades',
                        'cidade_flutuante': 'em uma fantástica cidade flutuante suspensa no céu',
                        'underwater_kingdom': 'em um reino subaquático com arquitetura de corais luminosos',
                        'cristal_cave': 'em uma caverna de cristais que emanam luz multicolorida',
                        'sky_temple': 'em um templo celestial suspenso entre as estrelas',
                        'magic_garden': 'em um jardim mágico com plantas que brilham e dançam'
                    };
                    
                    if (ambientesDetalhados[selectedSubcategories[2]]) {
                        ambienteText = ambientesDetalhados[selectedSubcategories[2]];
                    }
                } else {
                    // Usar categoria geral
                    const ambientes = {
                        'natureza': 'em um ambiente natural com paisagens exuberantes',
                        'urbano': 'em um cenário urbano moderno',
                        'interior': 'em um ambiente interno bem decorado',
                        'fantasia': 'em um mundo fantástico e mágico'
                    };
                    
                    if (ambientes[selectedChoices[2]]) {
                        ambienteText = ambientes[selectedChoices[2]];
                    }
                }
                
                if (ambienteText) {
                    parts.push(ambienteText);
                }
            }

            // Etapa 3: Estilo Visual
            if (selectedChoices[3]) {
                const estilos = {
                    'realista': 'com estilo fotorrealista e detalhado',
                    'anime': 'no estilo anime/manga japonês',
                    'cartoon': 'em estilo cartoon colorido',
                    'pintura': 'como uma pintura artística clássica',
                    'minimalista': 'com design minimalista e limpo',
                    'cyberpunk': 'no estilo cyberpunk futurístico'
                };
                if (estilos[selectedChoices[3]]) {
                    parts.push(estilos[selectedChoices[3]]);
                }
            }

            // Etapa 4: Iluminação
            if (selectedChoices[4]) {
                const iluminacao = {
                    'natural': 'com iluminação natural suave',
                    'dourada': 'banhado pela luz dourada do pôr do sol',
                    'noturna': 'sob iluminação noturna atmosférica',
                    'neon': 'com luzes neon vibrantes',
                    'dramatica': 'com iluminação dramática e contrastante',
                    'suave': 'com luz difusa e suave'
                };
                if (iluminacao[selectedChoices[4]]) {
                    parts.push(iluminacao[selectedChoices[4]]);
                }
            }

            // Etapa 5: Qualidade e Técnica
            if (selectedChoices[5]) {
                const qualidade = {
                    '4k': 'em resolução 4K ultra HD',
                    'cinematic': 'com qualidade cinemática profissional',
                    'hdr': 'com alto range dinâmico (HDR)',
                    'macro': 'com detalhes macro extremamente próximos',
                    'panoramica': 'em vista panorâmica ampla',
                    'vintage': 'com estética vintage nostálgica'
                };
                if (qualidade[selectedChoices[5]]) {
                    parts.push(qualidade[selectedChoices[5]]);
                }
            }

            // Etapa 6: Elementos Especiais
            if (selectedChoices[6]) {
                const elementos = {
                    'particulas': 'com efeitos de partículas flutuantes',
                    'reflexos': 'incluindo reflexos realistas',
                    'movimento': 'capturando sensação de movimento',
                    'textura': 'com texturas ricas e detalhadas',
                    'profundidade': 'com profundidade de campo artística',
                    'atmosfera': 'com elementos atmosféricos envolventes'
                };
                if (elementos[selectedChoices[6]]) {
                    parts.push(elementos[selectedChoices[6]]);
                }
            }

            // Etapa 7: Seres
            if (selectedChoices[7]) {
                let seresText = '';
                
                if (selectedSubcategories[7]) {
                    // Usar subcategoria específica
                    const seresDetalhados = {
                        // Humanos
                        'homem_jovem': 'apresentando um homem jovem atlético e confiante',
                        'mulher_jovem': 'com uma mulher jovem elegante e moderna',
                        'homem_maduro': 'mostrando um homem maduro experiente e respeitável',
                        'mulher_madura': 'com uma mulher madura sofisticada e sábia',
                        'crianca_menino': 'incluindo uma criança menino brincalhona e curiosa',
                        'crianca_menina': 'com uma criança menina alegre e expressiva',
                        'idoso': 'apresentando um idoso sábio e respeitável',
                        'idosa': 'mostrando uma idosa carinhosa e experiente',
                        'executivo': 'com um executivo profissional em traje formal',
                        'artista': 'incluindo um artista criativo com estilo bohemio',
                        'atleta': 'mostrando um atleta com físico musculoso e definido',
                        'estudante': 'com um jovem estudante acadêmico portando livros',
                        
                        // Animais
                        'cachorro_labrador': 'com um labrador amigável e leal de porte grande',
                        'gato_persa': 'incluindo um gato persa elegante de pelo longo',
                        'cavalo_arabe': 'mostrando um majestoso cavalo árabe',
                        'leao_africano': 'com um poderoso leão africano, rei da selva',
                        'aguia_real': 'apresentando uma águia real majestosa em voo',
                        'lobo_cinzento': 'incluindo um lobo cinzento selvagem e astuto',
                        'urso_pardo': 'com um imponente urso pardo, gigante da floresta',
                        'elefante_africano': 'mostrando um colossal elefante africano gentil',
                        'golfinho_nariz_garrafa': 'com um inteligente golfinho nariz-de-garrafa',
                        'tigre_siberiano': 'incluindo um feroz tigre siberiano listrado',
                        'panda_gigante': 'apresentando um adorável panda gigante',
                        'coruja_buraqueira': 'com uma sábia coruja buraqueira observadora',
                        
                        // Seres Fantásticos
                        'dragao_fogo': 'com um majestoso dragão de fogo cuspindo chamas',
                        'unicornio_branco': 'incluindo um unicórnio branco com chifre espiralado',
                        'fenix_dourada': 'mostrando uma fênix dourada renascendo das cinzas',
                        'elfo_florestal': 'com um elfo florestal guardião da natureza',
                        'sereia_oceano': 'apresentando uma sereia encantadora do oceano',
                        'centauro_guerreiro': 'incluindo um nobre centauro guerreiro',
                        'grifo_real': 'com um majestoso grifo real meio águia meio leão',
                        'pegasus_alado': 'mostrando um pégasus alado com asas divinas',
                        'minotauro_labirinto': 'incluindo um minotauro guardião do labirinto',
                        'sphinx_enigmatica': 'com uma esfinge enigmática de corpo felino',
                        'anjo_guardiao': 'apresentando um anjo guardião com asas luminosas',
                        'demonio_sombras': 'mostrando um demônio sombrio das trevas',
                        
                        // Robôs e IA
                        'androide_humanoid': 'com um androide humanóide de aparência avançada',
                        'robo_combate': 'incluindo um robô de combate blindado',
                        'cyborg_militar': 'mostrando um cyborg militar com implantes',
                        'ia_holografica': 'com uma IA holográfica em projeção',
                        'robo_assistente': 'apresentando um robô assistente amigável',
                        'mech_gigante': 'incluindo um mech gigante pilotado',
                        'nano_bots': 'com um enxame de nano-bots microscópicos',
                        'robo_explorador': 'mostrando um robô explorador especializado',
                        'synth_avatar': 'incluindo um avatar sintético avançado',
                        'guardian_ai': 'com uma IA guardiã protetora',
                        'worker_bot': 'apresentando um robô operário industrial',
                        'companion_droid': 'mostrando um droide companheiro emocional',
                        
                        // Aliens
                        'grey_classico': 'com um alien grey clássico de olhos grandes',
                        'reptiliano_verde': 'incluindo um reptiliano verde humanoide',
                        'nordico_alto': 'mostrando um alien nórdico alto e loiro',
                        'insectoide_mantis': 'com um insectóide mantis de múltiplos braços',
                        'cristalino_energia': 'apresentando um ser cristalino de energia',
                        'aquatico_tentaculos': 'incluindo um ser aquático com tentáculos',
                        'gasoso_eterico': 'mostrando um ser gasoso semi-transparente',
                        'mecanico_hibrido': 'com um alien mecânico híbrido orgânico',
                        'avatar_azul': 'incluindo um avatar azul de pele azulada',
                        'shapeshifter': 'apresentando um metamorfo em transformação',
                        'energy_being': 'mostrando um ser de energia pura luminosa',
                        'plant_alien': 'com um alien vegetal de características vegetais'
                    };
                    
                    if (seresDetalhados[selectedSubcategories[7]]) {
                        seresText = seresDetalhados[selectedSubcategories[7]];
                    }
                } else {
                    // Usar categoria geral
                    const seres = {
                        'humanos': 'incluindo personagens humanos',
                        'animais': 'com criaturas do reino animal',
                        'fantasticos': 'apresentando seres fantásticos e mitológicos',
                        'robots': 'incluindo robôs e inteligências artificiais',
                        'aliens': 'com seres extraterrestres'
                    };
                    
                    if (seres[selectedChoices[7]]) {
                        seresText = seres[selectedChoices[7]];
                    }
                }
                
                if (seresText) {
                    parts.push(seresText);
                }
            }

            // Construir prompt final
            if (parts.length > 0) {
                prompt += ' ' + parts.join(', ');
            }

            // Finalizar com orientações técnicas
            if (prompt) {
                prompt += '. Altamente detalhado, qualidade profissional, composição harmoniosa.';
            }

            document.getElementById('promptText').value = prompt;
        }

        // Event listeners para navegação
        document.getElementById('nextBtn').addEventListener('click', () => {
            if (currentSubstep > 0) {
                // Está em subcategoria, voltar para próxima etapa principal
                if (currentStep < 7) {
                    currentStep++;
                    loadStep(currentStep, 0);
                }
            } else if (currentStep < 7) {
                // Verificar se tem subcategorias pendentes
                const stepData = steps[currentStep];
                const selectedCategory = selectedChoices[currentStep];
                const hasSubcategories = selectedCategory && 
                    stepData.options.find(opt => opt.id === selectedCategory)?.subcategories;
                
                if (hasSubcategories && !selectedSubcategories[currentStep]) {
                    // Ir para subcategorias
                    loadStep(currentStep, 1);
                } else {
                    // Próxima etapa
                    currentStep++;
                    loadStep(currentStep, 0);
                }
            }
        });

        document.getElementById('prevBtn').addEventListener('click', () => {
            if (currentSubstep > 0) {
                // Está em subcategoria, voltar para categoria principal
                loadStep(currentStep, 0);
            } else if (currentStep > 1) {
                // Voltar para etapa anterior
                currentStep--;
                
                // Verificar se a etapa anterior tem subcategorias selecionadas
                const prevStepData = steps[currentStep];
                const prevSelectedCategory = selectedChoices[currentStep];
                const hasSubcategories = prevSelectedCategory && 
                    prevStepData.options.find(opt => opt.id === prevSelectedCategory)?.subcategories;
                
                if (hasSubcategories && selectedSubcategories[currentStep]) {
                    // Ir para subcategorias da etapa anterior
                    loadStep(currentStep, 1);
                } else {
                    // Ir para categoria principal da etapa anterior
                    loadStep(currentStep, 0);
                }
            }
        });

        // Event listeners para ações
        document.getElementById('copyBtn').addEventListener('click', () => {
            const promptText = document.getElementById('promptText').value;
            navigator.clipboard.writeText(promptText).then(() => {
                alert('Prompt copiado!');
            });
        });

        document.getElementById('clearBtn').addEventListener('click', () => {
            if (confirm('Limpar todas as seleções?')) {
                selectedChoices = {};
                selectedSubcategories = {};
                currentStep = 1;
                currentSubstep = 0;
                loadStep(1, 0);
                document.getElementById('promptText').value = '';
            }
        });

        // Inicializar quando o DOM carregar
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Inicializando Prompt Builder...');
            loadStep(1);
            console.log('Prompt Builder carregado!');
        });
    </script>
</body>
</html>