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
                    <button class="btn-nav btn-first" id="firstBtn" disabled title="Ir para o início">
                        <i class="fas fa-angle-double-left"></i>
                    </button>
                    <button class="btn-nav btn-prev" id="prevBtn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="nav-info">
                        <span id="navSteps">1 / 7</span>
                    </div>
                    <button class="btn-nav btn-next" id="nextBtn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <button class="btn-nav btn-last" id="lastBtn" title="Ir para o final">
                        <i class="fas fa-angle-double-right"></i>
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

    <!-- Modal de Configuração de Seres -->
    <div class="seres-config-modal" id="seresConfigModal">
        <div class="seres-config-content">
            <div class="seres-config-header">
                <h3 id="modalTitle">
                    <i class="fas fa-users"></i>
                    Configurar Seres
                </h3>
                <button class="seres-config-close" id="closeSeresConfig">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="seres-config-body">
                <div class="seres-list" id="seresList">
                    <!-- Lista de seres será inserida aqui dinamicamente -->
                </div>

                <div class="ser-form" id="serForm" style="display: none;">
                    <!-- O conteúdo do formulário será gerado dinamicamente -->
                </div>

                <div class="form-actions" style="border-top: 1px solid var(--gray-200); padding-top: 1rem;">
                    <button type="button" class="btn-secondary-large" id="adicionarNovoSer">
                        <i class="fas fa-plus"></i>
                        Adicionar Novo Ser
                    </button>
                    <button type="button" class="btn-primary-large" id="finalizarSeres">
                        <i class="fas fa-check"></i>
                        Finalizar Configuração
                    </button>
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
        let configuredSeres = [];
        let editingSerIndex = -1;

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
                            { id: 'praia_rochosa', title: 'Praia Rochosa', description: 'Costa acidentada com formações pétreas', icon: 'fas fa-mountain' },
                            { id: 'praia_areia_preta', title: 'Praia de Areia Preta', description: 'Costa vulcânica exótica', icon: 'fas fa-fire' },
                            { id: 'cachoeira_gigante', title: 'Cachoeira Gigante', description: 'Queda d\'água majestosa em penhasco', icon: 'fas fa-water' },
                            { id: 'cachoeira_tropical', title: 'Cachoeira Tropical', description: 'Queda d\'água em floresta densa', icon: 'fas fa-leaf' },
                            { id: 'cachoeira_congelada', title: 'Cachoeira Congelada', description: 'Cortina de gelo cristalino', icon: 'fas fa-snowflake' },
                            { id: 'montanha_nevada', title: 'Montanha Nevada', description: 'Picos cobertos de neve eterna', icon: 'fas fa-mountain' },
                            { id: 'montanha_rochosa', title: 'Montanha Rochosa', description: 'Formações áridas e imponentes', icon: 'fas fa-mountain' },
                            { id: 'cordilheira_himalaia', title: 'Cordilheira Himalaia', description: 'Teto do mundo com picos altíssimos', icon: 'fas fa-mountain' },
                            { id: 'planalto_andino', title: 'Planalto Andino', description: 'Altiplano com paisagem única', icon: 'fas fa-mountain' },
                            { id: 'vale_verdejante', title: 'Vale Verdejante', description: 'Depressão fértil entre montanhas', icon: 'fas fa-seedling' },
                            { id: 'deserto_sahara', title: 'Deserto do Sahara', description: 'Dunas infinitas sob sol escaldante', icon: 'fas fa-sun' },
                            { id: 'deserto_atacama', title: 'Deserto do Atacama', description: 'O mais árido do mundo', icon: 'fas fa-skull' },
                            { id: 'deserto_gelo', title: 'Deserto de Gelo', description: 'Vastidão polar gelada', icon: 'fas fa-snowflake' },
                            { id: 'dunas_vermelhas', title: 'Dunas Vermelhas', description: 'Areia avermelhada ondulante', icon: 'fas fa-fire' },
                            { id: 'oasis_desertico', title: 'Oásis Desértico', description: 'Refúgio verdejante no deserto', icon: 'fas fa-tint' },
                            { id: 'floresta_amazonica', title: 'Floresta Amazônica', description: 'Selva densa com biodiversidade', icon: 'fas fa-tree' },
                            { id: 'floresta_temperada', title: 'Floresta Temperada', description: 'Mata de clima ameno', icon: 'fas fa-tree' },
                            { id: 'floresta_boreal', title: 'Floresta Boreal', description: 'Taiga com coníferas', icon: 'fas fa-tree' },
                            { id: 'floresta_bambu', title: 'Floresta de Bambu', description: 'Bosque oriental zen', icon: 'fas fa-leaf' },
                            { id: 'floresta_encantada', title: 'Floresta Encantada', description: 'Mata mística e misteriosa', icon: 'fas fa-magic' },
                            { id: 'clareira_magica', title: 'Clareira Mágica', description: 'Espaço aberto na floresta', icon: 'fas fa-circle' },
                            { id: 'canyon_colorado', title: 'Canyon do Colorado', description: 'Formação rochosa estratificada', icon: 'fas fa-mountain' },
                            { id: 'canyon_antelope', title: 'Canyon Antelope', description: 'Garganta esculpida pelo vento', icon: 'fas fa-wind' },
                            { id: 'gruta_cristal', title: 'Gruta de Cristal', description: 'Caverna com formações minerais', icon: 'fas fa-gem' },
                            { id: 'lago_montanha', title: 'Lago de Montanha', description: 'Espelho d\'água em altitude', icon: 'fas fa-water' },
                            { id: 'lago_craterico', title: 'Lago Cratérico', description: 'Lagoa em cratera vulcânica', icon: 'fas fa-fire' },
                            { id: 'lagoa_azul', title: 'Lagoa Azul', description: 'Águas cristalinas azuladas', icon: 'fas fa-tint' },
                            { id: 'campo_lavanda', title: 'Campo de Lavanda', description: 'Ondas roxas aromáticas', icon: 'fas fa-seedling' },
                            { id: 'campo_girassol', title: 'Campo de Girassol', description: 'Mar dourado de flores', icon: 'fas fa-sun' },
                            { id: 'pradaria_infinita', title: 'Pradaria Infinita', description: 'Planície herbácea sem fim', icon: 'fas fa-seedling' },
                            { id: 'savana_africana', title: 'Savana Africana', description: 'Planície com acácias esparsas', icon: 'fas fa-tree' },
                            { id: 'vulcao_ativo', title: 'Vulcão Ativo', description: 'Cratera com lava incandescente', icon: 'fas fa-fire' },
                            { id: 'geyser_yellowstone', title: 'Geyser Yellowstone', description: 'Jato de água termal', icon: 'fas fa-water' },
                            { id: 'geleira_antartica', title: 'Geleira Antártica', description: 'Vastidão de gelo azul-cristalino', icon: 'fas fa-snowflake' },
                            { id: 'iceberg_gigante', title: 'Iceberg Gigante', description: 'Montanha de gelo flutuante', icon: 'fas fa-mountain' },
                            { id: 'fiorde_noruegues', title: 'Fiorde Norueguês', description: 'Vale glacial inundado', icon: 'fas fa-water' },
                            { id: 'aurora_boreal', title: 'Aurora Boreal', description: 'Luzes dançantes no céu polar', icon: 'fas fa-star' },
                            { id: 'tundra_artica', title: 'Tundra Ártica', description: 'Planície polar sem árvores', icon: 'fas fa-snowflake' },
                            { id: 'delta_rio', title: 'Delta de Rio', description: 'Foz ramificada com canais', icon: 'fas fa-water' },
                            { id: 'pantanal', title: 'Pantanal', description: 'Planície alagável rica em vida', icon: 'fas fa-frog' },
                            { id: 'mangue_tropical', title: 'Mangue Tropical', description: 'Ecossistema costeiro único', icon: 'fas fa-tree' },
                            { id: 'archipelago', title: 'Arquipélago', description: 'Conjunto de ilhas paradisíacas', icon: 'fas fa-island-tropical' },
                            { id: 'atol_corais', title: 'Atol de Corais', description: 'Anel de recifes no oceano', icon: 'fas fa-ring' }
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
                            { id: 'las_vegas_strip', title: 'Las Vegas Strip', description: 'Avenida dos cassinos luminosos', icon: 'fas fa-dice' },
                            { id: 'times_square', title: 'Times Square', description: 'Cruzamento mais famoso do mundo', icon: 'fas fa-tv' },
                            { id: 'dubai_skyline', title: 'Dubai Skyline', description: 'Horizonte futurista no deserto', icon: 'fas fa-building' },
                            { id: 'singapore_gardens', title: 'Singapura Gardens', description: 'Cidade-jardim moderna', icon: 'fas fa-seedling' },
                            { id: 'veneza_canais', title: 'Veneza dos Canais', description: 'Cidade aquática histórica', icon: 'fas fa-water' },
                            { id: 'amsterdam_canais', title: 'Canais de Amsterdam', description: 'Pontes e casas estreitas', icon: 'fas fa-home' },
                            { id: 'paris_boulevards', title: 'Boulevards de Paris', description: 'Elegância urbana francesa', icon: 'fas fa-road' },
                            { id: 'londres_vitoriana', title: 'Londres Vitoriana', description: 'Arquitetura clássica inglesa', icon: 'fas fa-crown' },
                            { id: 'favela_rio', title: 'Favela do Rio', description: 'Comunidade colorida nas encostas', icon: 'fas fa-home' },
                            { id: 'shantytown_mumbai', title: 'Shantytown Mumbai', description: 'Densidade urbana extrema', icon: 'fas fa-city' },
                            { id: 'medina_marrakech', title: 'Medina de Marrakech', description: 'Labirinto de vielas árabes', icon: 'fas fa-mosque' },
                            { id: 'souq_istambul', title: 'Souq de Istambul', description: 'Grande bazar otomano', icon: 'fas fa-store' },
                            { id: 'chinatown_sf', title: 'Chinatown São Francisco', description: 'Enclave cultural asiático', icon: 'fas fa-yin-yang' },
                            { id: 'little_italy', title: 'Little Italy', description: 'Bairro italiano tradicional', icon: 'fas fa-pizza-slice' },
                            { id: 'wall_street', title: 'Wall Street', description: 'Coração financeiro mundial', icon: 'fas fa-dollar-sign' },
                            { id: 'champs_elysees', title: 'Champs-Élysées', description: 'Avenida mais bela do mundo', icon: 'fas fa-tree' },
                            { id: 'red_square', title: 'Praça Vermelha', description: 'Coração histórico de Moscou', icon: 'fas fa-landmark' },
                            { id: 'estacao_central_ny', title: 'Grand Central NY', description: 'Terminal majestoso centenário', icon: 'fas fa-train' },
                            { id: 'metro_paris', title: 'Metrô de Paris', description: 'Art Nouveau subterrâneo', icon: 'fas fa-subway' },
                            { id: 'tube_londres', title: 'Tube de Londres', description: 'Underground histórico', icon: 'fas fa-subway' },
                            { id: 'ponte_brooklyn', title: 'Ponte Brooklyn', description: 'Ícone arquitetônico suspenso', icon: 'fas fa-bridge' },
                            { id: 'ponte_tower', title: 'Tower Bridge', description: 'Ponte basculante londrina', icon: 'fas fa-bridge' },
                            { id: 'hollywood_boulevard', title: 'Hollywood Boulevard', description: 'Calçada da fama', icon: 'fas fa-star' },
                            { id: 'sunset_strip', title: 'Sunset Strip', description: 'Vida noturna de Los Angeles', icon: 'fas fa-cocktail' },
                            { id: 'beco_graffiti', title: 'Beco com Graffiti', description: 'Arte urbana em vielas', icon: 'fas fa-spray-can' },
                            { id: 'rooftop_manhattan', title: 'Rooftop Manhattan', description: 'Terraço com vista da cidade', icon: 'fas fa-building' },
                            { id: 'skybar_singapura', title: 'Sky Bar Singapura', description: 'Bar nas alturas', icon: 'fas fa-cocktail' },
                            { id: 'mercado_flutuante', title: 'Mercado Flutuante', description: 'Comércio aquático tailandês', icon: 'fas fa-ship' },
                            { id: 'night_market', title: 'Night Market', description: 'Mercado noturno asiático', icon: 'fas fa-moon' },
                            { id: 'food_truck_festival', title: 'Festival Food Trucks', description: 'Gastronomia sobre rodas', icon: 'fas fa-truck' },
                            { id: 'zona_industrial', title: 'Zona Industrial', description: 'Complexo fabril urbano', icon: 'fas fa-industry' },
                            { id: 'porto_comercial', title: 'Porto Comercial', description: 'Terminal marítimo movimentado', icon: 'fas fa-anchor' },
                            { id: 'aeroporto_internacional', title: 'Aeroporto Internacional', description: 'Hub de conexões globais', icon: 'fas fa-plane' }
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
                            { id: 'apartamento_minimalista', title: 'Apartamento Minimalista', description: 'Design clean e funcional', icon: 'fas fa-square' },
                            { id: 'casa_vitoriana', title: 'Casa Vitoriana', description: 'Elegância clássica ornamentada', icon: 'fas fa-home' },
                            { id: 'cabana_madeira', title: 'Cabana de Madeira', description: 'Refúgio rústico aconchegante', icon: 'fas fa-tree' },
                            { id: 'cozinha_gourmet', title: 'Cozinha Gourmet', description: 'Culinária de alto padrão', icon: 'fas fa-utensils' },
                            { id: 'cozinha_rustica', title: 'Cozinha Rústica', description: 'Ambiente campestre acolhedor', icon: 'fas fa-bread-slice' },
                            { id: 'cozinha_futurista', title: 'Cozinha Futurista', description: 'Tecnologia culinária avançada', icon: 'fas fa-robot' },
                            { id: 'biblioteca_antiga', title: 'Biblioteca Antiga', description: 'Acervo centenário em madeira', icon: 'fas fa-book-open' },
                            { id: 'biblioteca_moderna', title: 'Biblioteca Moderna', description: 'Design contemporâneo para leitura', icon: 'fas fa-tablet-alt' },
                            { id: 'escritorio_executivo', title: 'Escritório Executivo', description: 'Ambiente corporativo elegante', icon: 'fas fa-briefcase' },
                            { id: 'home_office', title: 'Home Office', description: 'Trabalho remoto organizado', icon: 'fas fa-laptop' },
                            { id: 'atelier_artista', title: 'Ateliê de Artista', description: 'Estúdio criativo com luz natural', icon: 'fas fa-palette' },
                            { id: 'estudio_fotografia', title: 'Estúdio de Fotografia', description: 'Espaço profissional para fotos', icon: 'fas fa-camera' },
                            { id: 'spa_zen', title: 'Spa Zen', description: 'Santuário de relaxamento', icon: 'fas fa-leaf' },
                            { id: 'sauna_finlandesa', title: 'Sauna Finlandesa', description: 'Banho de vapor tradicional', icon: 'fas fa-fire' },
                            { id: 'wine_cellar', title: 'Adega de Vinhos', description: 'Cave subterrânea para vinhos', icon: 'fas fa-wine-bottle' },
                            { id: 'home_theater', title: 'Home Theater', description: 'Cinema particular luxuoso', icon: 'fas fa-film' },
                            { id: 'sala_jogos', title: 'Sala de Jogos', description: 'Entretenimento e diversão', icon: 'fas fa-gamepad' },
                            { id: 'greenhouse', title: 'Estufa/Greenhouse', description: 'Jardim interno climatizado', icon: 'fas fa-seedling' },
                            { id: 'aquario_gigante', title: 'Aquário Gigante', description: 'Vida marinha em casa', icon: 'fas fa-fish' },
                            { id: 'laboratory', title: 'Laboratório', description: 'Espaço científico high-tech', icon: 'fas fa-flask' },
                            { id: 'oficina_mecanica', title: 'Oficina Mecânica', description: 'Conserto e criação automotiva', icon: 'fas fa-tools' },
                            { id: 'dance_studio', title: 'Estúdio de Dança', description: 'Sala com espelhos e barras', icon: 'fas fa-music' },
                            { id: 'recording_studio', title: 'Estúdio de Gravação', description: 'Cabine acústica profissional', icon: 'fas fa-microphone' },
                            { id: 'music_room', title: 'Sala de Música', description: 'Instrumentos e acústica perfeita', icon: 'fas fa-guitar' },
                            { id: 'chapel_interior', title: 'Interior de Capela', description: 'Espaço sacro contemplativo', icon: 'fas fa-cross' },
                            { id: 'catedral_gotica', title: 'Catedral Gótica', description: 'Arquitetura religiosa majestosa', icon: 'fas fa-church' },
                            { id: 'hospital_moderno', title: 'Hospital Moderno', description: 'Medicina de alta tecnologia', icon: 'fas fa-hospital' },
                            { id: 'escola_infantil', title: 'Escola Infantil', description: 'Educação lúdica e colorida', icon: 'fas fa-child' },
                            { id: 'universidade', title: 'Universidade', description: 'Campus acadêmico prestigioso', icon: 'fas fa-graduation-cap' },
                            { id: 'museu_arte', title: 'Museu de Arte', description: 'Galeria cultural refinada', icon: 'fas fa-paint-brush' },
                            { id: 'planetario', title: 'Planetário', description: 'Viagem pelo cosmos', icon: 'fas fa-globe-americas' },
                            { id: 'aquario_publico', title: 'Aquário Público', description: 'Mundo submarino educativo', icon: 'fas fa-fish' },
                            { id: 'shopping_center', title: 'Shopping Center', description: 'Centro comercial movimentado', icon: 'fas fa-shopping-bag' },
                            { id: 'teatro_opera', title: 'Teatro de Ópera', description: 'Arte dramática clássica', icon: 'fas fa-theater-masks' },
                            { id: 'cassino_luxo', title: 'Cassino de Luxo', description: 'Jogos e entretenimento adulto', icon: 'fas fa-dice' },
                            { id: 'boate_underground', title: 'Boate Underground', description: 'Vida noturna alternativa', icon: 'fas fa-music' }
                        ]
                    },
                    { 
                        id: 'fantasia', 
                        title: 'Fantasia', 
                        description: 'Ambientes mágicos e fantásticos', 
                        icon: 'fas fa-magic',
                        subcategories: [
                            { id: 'castelo_nuvens', title: 'Castelo nas Nuvens', description: 'Fortaleza flutuante etérea', icon: 'fas fa-cloud' },
                            { id: 'castelo_medieval', title: 'Castelo Medieval', description: 'Fortaleza de pedra antiga', icon: 'fas fa-chess-rook' },
                            { id: 'palacio_cristal', title: 'Palácio de Cristal', description: 'Estrutura transparente mágica', icon: 'fas fa-gem' },
                            { id: 'torre_mago', title: 'Torre do Mago', description: 'Observatório arcano misterioso', icon: 'fas fa-hat-wizard' },
                            { id: 'floresta_encantada', title: 'Floresta Encantada', description: 'Mata habitada por fadas', icon: 'fas fa-tree' },
                            { id: 'floresta_bioluminescente', title: 'Floresta Bioluminescente', description: 'Mata que brilha no escuro', icon: 'fas fa-seedling' },
                            { id: 'floresta_petrificada', title: 'Floresta Petrificada', description: 'Árvores transformadas em pedra', icon: 'fas fa-mountain' },
                            { id: 'jardim_suspenso', title: 'Jardins Suspensos', description: 'Paraíso botânico aéreo', icon: 'fas fa-leaf' },
                            { id: 'jardim_zen_magico', title: 'Jardim Zen Mágico', description: 'Harmonia espiritual transcendente', icon: 'fas fa-yin-yang' },
                            { id: 'portal_temporal', title: 'Portal Temporal', description: 'Passagem através do tempo', icon: 'fas fa-clock' },
                            { id: 'portal_dimensional', title: 'Portal Dimensional', description: 'Gateway entre realidades', icon: 'fas fa-door-open' },
                            { id: 'ponte_arco_iris', title: 'Ponte do Arco-Íris', description: 'Bifrost conectando mundos', icon: 'fas fa-rainbow' },
                            { id: 'cidade_steampunk', title: 'Cidade Steampunk', description: 'Metrópole vitoriana futurista', icon: 'fas fa-cogs' },
                            { id: 'cidade_cyberpunk', title: 'Cidade Cyberpunk', description: 'Distopia tecnológica neon', icon: 'fas fa-microchip' },
                            { id: 'cidade_flutuante', title: 'Cidade Flutuante', description: 'Metrópole suspensa no ar', icon: 'fas fa-cloud' },
                            { id: 'vila_medieval', title: 'Vila Medieval', description: 'Povoado de época feudal', icon: 'fas fa-home' },
                            { id: 'palacio_atlantico', title: 'Palácio Atlântico', description: 'Reino subaquático majestoso', icon: 'fas fa-fish' },
                            { id: 'cidade_submarina', title: 'Cidade Submarina', description: 'Metrópole nas profundezas', icon: 'fas fa-water' },
                            { id: 'caverna_dragoes', title: 'Caverna dos Dragões', description: 'Covil repleto de tesouros', icon: 'fas fa-dragon' },
                            { id: 'caverna_cristais', title: 'Caverna de Cristais', description: 'Gruta com gemas luminosas', icon: 'fas fa-gem' },
                            { id: 'mina_anoes', title: 'Mina dos Anões', description: 'Túneis de mineração épicos', icon: 'fas fa-hammer' },
                            { id: 'dungeon_perdida', title: 'Dungeon Perdida', description: 'Masmorra cheia de mistérios', icon: 'fas fa-dungeon' },
                            { id: 'observatorio_espacial', title: 'Observatório Espacial', description: 'Torre celestial para as estrelas', icon: 'fas fa-telescope' },
                            { id: 'estacao_espacial', title: 'Estação Espacial', description: 'Habitat orbital futurístico', icon: 'fas fa-satellite' },
                            { id: 'nave_espacial', title: 'Nave Espacial', description: 'Interior futurístico alienígena', icon: 'fas fa-rocket' },
                            { id: 'planeta_alienigena', title: 'Planeta Alienígena', description: 'Mundo extraterrestre exótico', icon: 'fas fa-globe' },
                            { id: 'colonia_marte', title: 'Colônia em Marte', description: 'Assentamento no planeta vermelho', icon: 'fas fa-planet-mars' },
                            { id: 'biblioteca_infinita', title: 'Biblioteca Infinita', description: 'Acervo interdimensional', icon: 'fas fa-infinity' },
                            { id: 'labirinto_cristal', title: 'Labirinto de Cristal', description: 'Maze refratário luminoso', icon: 'fas fa-gem' },
                            { id: 'labirinto_hedges', title: 'Labirinto de Arbustos', description: 'Maze verde e verdejante', icon: 'fas fa-tree' },
                            { id: 'templo_elementais', title: 'Templo dos Elementais', description: 'Santuário dos quatro elementos', icon: 'fas fa-fire' },
                            { id: 'templo_perdido', title: 'Templo Perdido', description: 'Ruína arqueológica mística', icon: 'fas fa-university' },
                            { id: 'santuario_dragao', title: 'Santuário do Dragão', description: 'Local sagrado reptiliano', icon: 'fas fa-dragon' },
                            { id: 'vulcao_magico', title: 'Vulcão Mágico', description: 'Cratera com energia arcana', icon: 'fas fa-fire' },
                            { id: 'lago_espelhos', title: 'Lago dos Espelhos', description: 'Águas que refletem outras dimensões', icon: 'fas fa-mirror' },
                            { id: 'dimensao_sombrias', title: 'Dimensão das Sombras', description: 'Plano etéreo sombrio', icon: 'fas fa-ghost' },
                            { id: 'reino_fadas', title: 'Reino das Fadas', description: 'Terra encantada diminuta', icon: 'fas fa-magic' },
                            { id: 'vale_unicornios', title: 'Vale dos Unicórnios', description: 'Santuário de criaturas puras', icon: 'fas fa-horse' },
                            { id: 'cemiterio_antigo', title: 'Cemitério Antigo', description: 'Necrópole assombrada', icon: 'fas fa-skull' },
                            { id: 'mansao_assombrada', title: 'Mansão Assombrada', description: 'Casa mal-assombrada vitoriana', icon: 'fas fa-ghost' },
                            { id: 'escola_magia', title: 'Escola de Magia', description: 'Academia para jovens bruxos', icon: 'fas fa-hat-wizard' },
                            { id: 'laboratorio_alquimia', title: 'Laboratório de Alquimia', description: 'Oficina de transformações mágicas', icon: 'fas fa-flask' },
                            { id: 'mercado_magico', title: 'Mercado Mágico', description: 'Feira de itens encantados', icon: 'fas fa-store' },
                            { id: 'taverna_aventureiros', title: 'Taverna dos Aventureiros', description: 'Ponto de encontro heroico', icon: 'fas fa-beer' }
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
                            <div class="option-card ${option.subcategories ? 'has-subs' : ''}" onclick="selectCategory('${option.id}')" data-option="${option.id}">
                                <div class="option-icon">
                                    <i class="${option.icon}"></i>
                                </div>
                                <div class="option-title">${option.title}</div>
                                <div class="option-description">${option.description}</div>
                                ${option.subcategories ? '<div class="has-subcategories"></div>' : ''}
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
            
            // Botões normais
            document.getElementById('prevBtn').disabled = step === 1 && substep === 0;
            document.getElementById('nextBtn').disabled = (step === 7 && substep === 0) || 
                (hasSubcategories && !selectedSubcategories[step]);
            
            // Botões início/final
            document.getElementById('firstBtn').disabled = step === 1 && substep === 0;
            document.getElementById('lastBtn').disabled = step === 7 && substep === 0;
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

            // Verificar se é etapa de seres
            if (currentStep === 7) {
                // Abrir modal de configuração direto para seres
                setTimeout(() => {
                    openSeresConfigModal(categoryId);
                }, 300);
                return;
            }

            // Verificar se tem subcategorias (para outras etapas)
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

            // Etapa de seres agora abre modal direto na seleção da categoria principal

            // Atualizar prompt
            updatePrompt();

            console.log('Selected subcategory:', subcategoryId, 'for step:', currentStep);
        }

        // Funções para modal de configuração de seres
        let currentSerType = '';
        
        function openSeresConfigModal(serType) {
            currentSerType = serType;
            document.getElementById('seresConfigModal').style.display = 'flex';
            document.getElementById('modalTitle').textContent = `Configurar ${serType.charAt(0).toUpperCase() + serType.slice(1)}`;
            showSerFormForType(serType);
            updateSeresPreview();
        }

        function closeSeresConfigModal() {
            document.getElementById('seresConfigModal').style.display = 'none';
        }

        function showSerFormForType(serType) {
            const formContainer = document.getElementById('serForm');
            
            if (serType === 'humanos') {
                formContainer.innerHTML = `
                    <div class="form-section">
                        <h4><i class="fas fa-user"></i> Informações Básicas</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="serNome">Nome/Identificação</label>
                                <input type="text" id="serNome" placeholder="Ex: Personagem Principal, Ser1, João...">
                            </div>
                            <div class="form-group">
                                <label for="serGenero">Gênero</label>
                                <select id="serGenero">
                                    <option value="">Selecione...</option>
                                    <option value="homem">Homem</option>
                                    <option value="mulher">Mulher</option>
                                    <option value="neutro">Neutro/Não-binário</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="serIdade">Idade: <span id="idadeValue">25</span> anos</label>
                                <input type="range" id="serIdade" min="1" max="80" value="25" oninput="document.getElementById('idadeValue').textContent = this.value">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4><i class="fas fa-ruler"></i> Características Físicas</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="serAltura">Altura: <span id="alturaValue">1.70</span>m</label>
                                <input type="range" id="serAltura" min="60" max="210" value="170" step="1" oninput="document.getElementById('alturaValue').textContent = (this.value/100).toFixed(2)">
                            </div>
                            <div class="form-group">
                                <label for="serPeso">Peso: <span id="pesoValue">70</span>kg</label>
                                <input type="range" id="serPeso" min="40" max="150" value="70" step="1" oninput="document.getElementById('pesoValue').textContent = this.value">
                            </div>
                            <div class="form-group">
                                <label for="serTomPele">Tom de Pele</label>
                                <select id="serTomPele">
                                    <option value="">Selecione...</option>
                                    <option value="muito_claro">Muito Claro</option>
                                    <option value="claro">Claro</option>
                                    <option value="medio">Médio</option>
                                    <option value="moreno">Moreno</option>
                                    <option value="escuro">Escuro</option>
                                    <option value="muito_escuro">Muito Escuro</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="serCorOlhos">Cor dos Olhos</label>
                                <select id="serCorOlhos">
                                    <option value="">Selecione...</option>
                                    <option value="azuis">Azuis</option>
                                    <option value="verdes">Verdes</option>
                                    <option value="castanhos">Castanhos</option>
                                    <option value="pretos">Pretos</option>
                                    <option value="mel">Mel</option>
                                    <option value="cinzas">Cinzas</option>
                                    <option value="heterocromia">Heterocromia</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4><i class="fas fa-cut"></i> Cabelo</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="serCorCabelo">Cor do Cabelo</label>
                                <select id="serCorCabelo">
                                    <option value="">Selecione...</option>
                                    <option value="loiro">Loiro</option>
                                    <option value="castanho_claro">Castanho Claro</option>
                                    <option value="castanho">Castanho</option>
                                    <option value="castanho_escuro">Castanho Escuro</option>
                                    <option value="preto">Preto</option>
                                    <option value="ruivo">Ruivo</option>
                                    <option value="grisalho">Grisalho</option>
                                    <option value="branco">Branco</option>
                                    <option value="colorido">Colorido</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="serTipoCabelo">Tipo de Cabelo</label>
                                <select id="serTipoCabelo">
                                    <option value="">Selecione...</option>
                                    <option value="liso">Liso</option>
                                    <option value="ondulado">Ondulado</option>
                                    <option value="cacheado">Cacheado</option>
                                    <option value="crespo">Crespo</option>
                                    <option value="careca">Careca</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="serComprimentoCabelo">Comprimento</label>
                                <select id="serComprimentoCabelo">
                                    <option value="">Selecione...</option>
                                    <option value="muito_curto">Muito Curto</option>
                                    <option value="curto">Curto</option>
                                    <option value="medio">Médio</option>
                                    <option value="longo">Longo</option>
                                    <option value="muito_longo">Muito Longo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4><i class="fas fa-tshirt"></i> Vestimenta</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="serTraje">Tipo de Traje</label>
                                <select id="serTraje">
                                    <option value="">Selecione...</option>
                                    <option value="casual">Casual</option>
                                    <option value="formal">Formal/Social</option>
                                    <option value="esportivo">Esportivo</option>
                                    <option value="elegante">Elegante</option>
                                    <option value="praia">Praia/Verão</option>
                                    <option value="inverno">Inverno</option>
                                    <option value="festa">Festa/Gala</option>
                                    <option value="trabalho">Trabalho/Profissional</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="serDescricaoRoupa">Descrição da Roupa</label>
                                <input type="text" id="serDescricaoRoupa" placeholder="Ex: camiseta azul, jeans, tênis branco...">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary-large" id="cancelarSer">Cancelar</button>
                        <button type="button" class="btn-primary-large" id="salvarSer">
                            <i class="fas fa-save"></i>
                            Salvar Ser
                        </button>
                    </div>
                `;
            } else if (serType === 'animais') {
                formContainer.innerHTML = `
                    <div class="form-section">
                        <h4><i class="fas fa-paw"></i> Informações Básicas</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="serNome">Nome/Identificação</label>
                                <input type="text" id="serNome" placeholder="Ex: Animal1, Rex, Mimi...">
                            </div>
                            <div class="form-group">
                                <label for="serTipoAnimal">Tipo de Animal</label>
                                <select id="serTipoAnimal">
                                    <option value="">Selecione...</option>
                                    <option value="cao">Cão</option>
                                    <option value="gato">Gato</option>
                                    <option value="cavalo">Cavalo</option>
                                    <option value="passaro">Pássaro</option>
                                    <option value="peixe">Peixe</option>
                                    <option value="coelho">Coelho</option>
                                    <option value="hamster">Hamster</option>
                                    <option value="tartaruga">Tartaruga</option>
                                    <option value="serpente">Serpente</option>
                                    <option value="outro">Outro</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="serPorte">Porte</label>
                                <select id="serPorte">
                                    <option value="">Selecione...</option>
                                    <option value="muito_pequeno">Muito Pequeno</option>
                                    <option value="pequeno">Pequeno</option>
                                    <option value="medio">Médio</option>
                                    <option value="grande">Grande</option>
                                    <option value="muito_grande">Muito Grande</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4><i class="fas fa-palette"></i> Características Físicas</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="serCorPrincipal">Cor Principal</label>
                                <select id="serCorPrincipal">
                                    <option value="">Selecione...</option>
                                    <option value="branco">Branco</option>
                                    <option value="preto">Preto</option>
                                    <option value="marrom">Marrom</option>
                                    <option value="cinza">Cinza</option>
                                    <option value="dourado">Dourado</option>
                                    <option value="ruivo">Ruivo</option>
                                    <option value="tigrado">Tigrado</option>
                                    <option value="malhado">Malhado</option>
                                    <option value="multicolorido">Multicolorido</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="serTexturaPelo">Textura do Pelo/Pena</label>
                                <select id="serTexturaPelo">
                                    <option value="">Selecione...</option>
                                    <option value="liso">Liso</option>
                                    <option value="ondulado">Ondulado</option>
                                    <option value="crespo">Crespo</option>
                                    <option value="fofo">Fofo</option>
                                    <option value="duro">Duro</option>
                                    <option value="escamas">Escamas</option>
                                    <option value="penas">Penas</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="serComprimentoPelo">Comprimento do Pelo</label>
                                <select id="serComprimentoPelo">
                                    <option value="">Selecione...</option>
                                    <option value="muito_curto">Muito Curto</option>
                                    <option value="curto">Curto</option>
                                    <option value="medio">Médio</option>
                                    <option value="longo">Longo</option>
                                    <option value="muito_longo">Muito Longo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4><i class="fas fa-info-circle"></i> Detalhes Adicionais</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="serDescricaoExtra">Descrição Adicional</label>
                                <input type="text" id="serDescricaoExtra" placeholder="Ex: com coleira vermelha, olhos azuis, rabo cortado...">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary-large" id="cancelarSer">Cancelar</button>
                        <button type="button" class="btn-primary-large" id="salvarSer">
                            <i class="fas fa-save"></i>
                            Salvar Animal
                        </button>
                    </div>
                `;
            } else if (serType === 'fantasticos') {
                formContainer.innerHTML = `
                    <div class="form-section">
                        <h4><i class="fas fa-dragon"></i> Ser Fantástico</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="serNome">Nome/Identificação</label>
                                <input type="text" id="serNome" placeholder="Ex: Dragão1, Unicórnio Dourado...">
                            </div>
                            <div class="form-group">
                                <label for="serTipoFantastico">Tipo</label>
                                <select id="serTipoFantastico">
                                    <option value="">Selecione...</option>
                                    <option value="dragao">Dragão</option>
                                    <option value="unicornio">Unicórnio</option>
                                    <option value="fenix">Fênix</option>
                                    <option value="elfo">Elfo</option>
                                    <option value="sereia">Sereia</option>
                                    <option value="centauro">Centauro</option>
                                    <option value="grifo">Grifo</option>
                                    <option value="pegasus">Pégasus</option>
                                    <option value="minotauro">Minotauro</option>
                                    <option value="sphinx">Esfinge</option>
                                    <option value="anjo">Anjo</option>
                                    <option value="demonio">Demônio</option>
                                    <option value="outro">Outro</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="serDescricaoExtra">Descrição Completa</label>
                                <textarea id="serDescricaoExtra" rows="4" placeholder="Descreva todas as características do ser fantástico: aparência, poderes, cores, tamanho, etc..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary-large" id="cancelarSer">Cancelar</button>
                        <button type="button" class="btn-primary-large" id="salvarSer">
                            <i class="fas fa-save"></i>
                            Salvar Ser Fantástico
                        </button>
                    </div>
                `;
            } else if (serType === 'robots') {
                formContainer.innerHTML = `
                    <div class="form-section">
                        <h4><i class="fas fa-robot"></i> Robô/IA</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="serNome">Nome/Identificação</label>
                                <input type="text" id="serNome" placeholder="Ex: R2-D2, JARVIS, Androide1...">
                            </div>
                            <div class="form-group">
                                <label for="serTipoRobo">Tipo</label>
                                <select id="serTipoRobo">
                                    <option value="">Selecione...</option>
                                    <option value="humanoide">Humanoide</option>
                                    <option value="industrial">Industrial</option>
                                    <option value="militar">Militar</option>
                                    <option value="domestico">Doméstico</option>
                                    <option value="explorador">Explorador</option>
                                    <option value="ia_pura">IA Pura (sem corpo)</option>
                                    <option value="androide">Androide</option>
                                    <option value="cyborg">Cyborg</option>
                                    <option value="outro">Outro</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="serDescricaoExtra">Descrição Completa</label>
                                <textarea id="serDescricaoExtra" rows="4" placeholder="Descreva o robô/IA: aparência, funcionalidades, material, cores, luzes, tamanho, etc..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary-large" id="cancelarSer">Cancelar</button>
                        <button type="button" class="btn-primary-large" id="salvarSer">
                            <i class="fas fa-save"></i>
                            Salvar Robô/IA
                        </button>
                    </div>
                `;
            } else if (serType === 'aliens') {
                formContainer.innerHTML = `
                    <div class="form-section">
                        <h4><i class="fas fa-user-astronaut"></i> Alien</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="serNome">Nome/Identificação</label>
                                <input type="text" id="serNome" placeholder="Ex: Zorg, ET, Alien1...">
                            </div>
                            <div class="form-group">
                                <label for="serTipoAlien">Tipo</label>
                                <select id="serTipoAlien">
                                    <option value="">Selecione...</option>
                                    <option value="humanoide">Humanoide</option>
                                    <option value="insectoide">Insectóide</option>
                                    <option value="reptiliano">Reptiliano</option>
                                    <option value="aquatico">Aquático</option>
                                    <option value="energetico">Energético</option>
                                    <option value="gasoso">Gasoso</option>
                                    <option value="cristalino">Cristalino</option>
                                    <option value="bioorganico">Bio-orgânico</option>
                                    <option value="outro">Outro</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="serDescricaoExtra">Descrição Completa</label>
                                <textarea id="serDescricaoExtra" rows="4" placeholder="Descreva o alien: aparência, pele, olhos, membros, tamanho, poderes especiais, etc..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary-large" id="cancelarSer">Cancelar</button>
                        <button type="button" class="btn-primary-large" id="salvarSer">
                            <i class="fas fa-save"></i>
                            Salvar Alien
                        </button>
                    </div>
                `;
            }
            
            formContainer.style.display = 'block';
        }

        function renderSeresList() {
            const seresList = document.getElementById('seresList');
            
            if (configuredSeres.length === 0) {
                seresList.innerHTML = `
                    <div style="text-align: center; padding: 2rem; color: var(--gray-500);">
                        <i class="fas fa-user-plus" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>Nenhum ser configurado ainda.</p>
                        <p>Clique em "Adicionar Novo Ser" para começar.</p>
                    </div>
                `;
                return;
            }

            seresList.innerHTML = configuredSeres.map((ser, index) => `
                <div class="ser-item">
                    <div class="ser-info">
                        <div class="ser-name">${ser.nome || `Ser ${index + 1}`}</div>
                        <div class="ser-description">${generateSerDescription(ser)}</div>
                    </div>
                    <div class="ser-actions">
                        <button class="btn-ser btn-edit" onclick="editSer(${index})">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn-ser btn-delete" onclick="deleteSer(${index})">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function generateSerDescription(ser) {
            let desc = [];
            
            if (ser.genero) desc.push(ser.genero);
            if (ser.idade) desc.push(ser.idade.replace('_', ' '));
            if (ser.altura) desc.push(ser.altura.replace('_', ' '));
            if (ser.peso) desc.push(ser.peso);
            if (ser.corCabelo) desc.push(`cabelo ${ser.corCabelo.replace('_', ' ')}`);
            if (ser.estiloRoupa) desc.push(`roupa ${ser.estiloRoupa}`);
            
            return desc.length > 0 ? desc.join(', ') : 'Sem configurações específicas';
        }

        function showSerForm(ser = null) {
            const form = document.getElementById('serForm');
            form.style.display = 'block';
            
            if (ser) {
                // Preencher formulário para edição
                document.getElementById('serNome').value = ser.nome || '';
                document.getElementById('serGenero').value = ser.genero || '';
                document.getElementById('serIdade').value = ser.idade || '';
                document.getElementById('serAltura').value = ser.altura || '';
                document.getElementById('serPeso').value = ser.peso || '';
                document.getElementById('serTomPele').value = ser.tomPele || '';
                document.getElementById('serCorCabelo').value = ser.corCabelo || '';
                document.getElementById('serComprimentoCabelo').value = ser.comprimentoCabelo || '';
                document.getElementById('serEstiloCabelo').value = ser.estiloCabelo || '';
                document.getElementById('serEstiloRoupa').value = ser.estiloRoupa || '';
                document.getElementById('serCorRoupaTexto').value = ser.corRoupaTexto || '';
                document.getElementById('serDescricaoExtra').value = ser.descricaoExtra || '';
            } else {
                // Limpar formulário para novo ser
                clearSerForm();
            }
            
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function hideSerForm() {
            document.getElementById('serForm').style.display = 'none';
            editingSerIndex = -1;
        }

        function clearSerForm() {
            document.getElementById('serNome').value = '';
            document.getElementById('serGenero').value = '';
            document.getElementById('serIdade').value = '';
            document.getElementById('serAltura').value = '';
            document.getElementById('serPeso').value = '';
            document.getElementById('serTomPele').value = '';
            document.getElementById('serCorCabelo').value = '';
            document.getElementById('serComprimentoCabelo').value = '';
            document.getElementById('serEstiloCabelo').value = '';
            document.getElementById('serEstiloRoupa').value = '';
            document.getElementById('serCorRoupaTexto').value = '';
            document.getElementById('serDescricaoExtra').value = '';
        }

        function saveCurrentSer() {
            const nome = document.getElementById('serNome').value;
            if (!nome.trim()) {
                alert('Por favor, preencha o nome/identificação do ser.');
                return;
            }

            let ser = {
                nome: nome,
                tipo: currentSerType
            };

            // Coletar dados específicos do tipo
            if (currentSerType === 'humanos') {
                ser = {
                    ...ser,
                    genero: document.getElementById('serGenero').value,
                    idade: document.getElementById('serIdade').value + ' anos',
                    altura: (document.getElementById('serAltura').value / 100).toFixed(2) + 'm',
                    peso: document.getElementById('serPeso').value + 'kg',
                    tomPele: document.getElementById('serTomPele').value,
                    corOlhos: document.getElementById('serCorOlhos').value,
                    corCabelo: document.getElementById('serCorCabelo').value,
                    tipoCabelo: document.getElementById('serTipoCabelo').value,
                    comprimentoCabelo: document.getElementById('serComprimentoCabelo').value,
                    traje: document.getElementById('serTraje').value,
                    descricaoRoupa: document.getElementById('serDescricaoRoupa').value
                };
            } else if (currentSerType === 'animais') {
                ser = {
                    ...ser,
                    tipoAnimal: document.getElementById('serTipoAnimal').value,
                    porte: document.getElementById('serPorte').value,
                    corPrincipal: document.getElementById('serCorPrincipal').value,
                    texturaPelo: document.getElementById('serTexturaPelo').value,
                    comprimentoPelo: document.getElementById('serComprimentoPelo').value,
                    descricaoExtra: document.getElementById('serDescricaoExtra').value
                };
            } else if (currentSerType === 'fantasticos') {
                ser = {
                    ...ser,
                    tipoFantastico: document.getElementById('serTipoFantastico').value,
                    descricaoExtra: document.getElementById('serDescricaoExtra').value
                };
            } else if (currentSerType === 'robots') {
                ser = {
                    ...ser,
                    tipoRobo: document.getElementById('serTipoRobo').value,
                    descricaoExtra: document.getElementById('serDescricaoExtra').value
                };
            } else if (currentSerType === 'aliens') {
                ser = {
                    ...ser,
                    tipoAlien: document.getElementById('serTipoAlien').value,
                    descricaoExtra: document.getElementById('serDescricaoExtra').value
                };
            }

            if (editingSerIndex >= 0) {
                // Editando ser existente
                configuredSeres[editingSerIndex] = ser;
            } else {
                // Adicionando novo ser
                configuredSeres.push(ser);
            }

            // Resetar formulário
            document.getElementById('serForm').style.display = 'none';
            editingSerIndex = -1;
            updateSeresPreview();
        }

        function updateSeresPreview() {
            const seresList = document.getElementById('seresList');
            
            if (configuredSeres.length === 0) {
                seresList.innerHTML = `
                    <div class="no-seres">
                        <i class="fas fa-users"></i>
                        <p>Nenhum ser configurado ainda</p>
                        <button type="button" class="btn-primary" onclick="showSerForm()">
                            <i class="fas fa-plus"></i>
                            Adicionar Primeiro Ser
                        </button>
                    </div>
                `;
                return;
            }

            let listHTML = '<div class="seres-configured">';
            configuredSeres.forEach((ser, index) => {
                let tipoLabel = '';
                let icone = '';
                let detalhes = '';
                
                switch(ser.tipo) {
                    case 'humanos':
                        tipoLabel = 'Humano';
                        icone = 'user';
                        detalhes = [ser.genero, ser.idade, ser.altura].filter(d => d).join(', ');
                        break;
                    case 'animais':
                        tipoLabel = 'Animal';
                        icone = 'paw';
                        detalhes = [ser.tipoAnimal, ser.porte, ser.corPrincipal].filter(d => d).join(', ');
                        break;
                    case 'fantasticos':
                        tipoLabel = 'Ser Fantástico';
                        icone = 'dragon';
                        detalhes = ser.tipoFantastico || '';
                        break;
                    case 'robots':
                        tipoLabel = 'Robô/IA';
                        icone = 'robot';
                        detalhes = ser.tipoRobo || '';
                        break;
                    case 'aliens':
                        tipoLabel = 'Alien';
                        icone = 'user-astronaut';
                        detalhes = ser.tipoAlien || '';
                        break;
                    default:
                        tipoLabel = ser.tipo;
                        icone = 'question';
                }

                listHTML += `
                    <div class="ser-item">
                        <div class="ser-info">
                            <h5>
                                <i class="fas fa-${icone}"></i>
                                ${ser.nome} (${tipoLabel})
                            </h5>
                            <p>${detalhes || 'Configuração básica'}</p>
                        </div>
                        <div class="ser-actions">
                            <button type="button" class="btn-icon" onclick="editSer(${index})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn-icon" onclick="deleteSer(${index})" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            listHTML += '</div>';
            
            seresList.innerHTML = listHTML;
        }

        function showSerForm(ser = null) {
            editingSerIndex = ser ? configuredSeres.indexOf(ser) : -1;
            showSerFormForType(currentSerType);
            
            if (ser) {
                // Preencher campos baseado no tipo
                document.getElementById('serNome').value = ser.nome || '';
                
                if (ser.tipo === 'humanos') {
                    document.getElementById('serGenero').value = ser.genero || '';
                    
                    // Idade (extrair apenas o número)
                    const idadeNumero = ser.idade ? ser.idade.replace(' anos', '') : '25';
                    document.getElementById('serIdade').value = idadeNumero;
                    document.getElementById('idadeValue').textContent = idadeNumero;
                    
                    // Altura (extrair número e converter para cm)
                    const alturaMetros = ser.altura ? parseFloat(ser.altura.replace('m', '')) : 1.70;
                    const alturaCm = Math.round(alturaMetros * 100);
                    document.getElementById('serAltura').value = alturaCm;
                    document.getElementById('alturaValue').textContent = alturaMetros.toFixed(2);
                    
                    // Peso (extrair apenas o número)
                    const pesoNumero = ser.peso ? ser.peso.replace('kg', '') : '70';
                    document.getElementById('serPeso').value = pesoNumero;
                    document.getElementById('pesoValue').textContent = pesoNumero;
                    document.getElementById('serTomPele').value = ser.tomPele || '';
                    document.getElementById('serCorOlhos').value = ser.corOlhos || '';
                    document.getElementById('serCorCabelo').value = ser.corCabelo || '';
                    document.getElementById('serTipoCabelo').value = ser.tipoCabelo || '';
                    document.getElementById('serComprimentoCabelo').value = ser.comprimentoCabelo || '';
                    document.getElementById('serTraje').value = ser.traje || '';
                    document.getElementById('serDescricaoRoupa').value = ser.descricaoRoupa || '';
                } else if (ser.tipo === 'animais') {
                    document.getElementById('serTipoAnimal').value = ser.tipoAnimal || '';
                    document.getElementById('serPorte').value = ser.porte || '';
                    document.getElementById('serCorPrincipal').value = ser.corPrincipal || '';
                    document.getElementById('serTexturaPelo').value = ser.texturaPelo || '';
                    document.getElementById('serComprimentoPelo').value = ser.comprimentoPelo || '';
                    document.getElementById('serDescricaoExtra').value = ser.descricaoExtra || '';
                } else if (ser.tipo === 'fantasticos') {
                    document.getElementById('serTipoFantastico').value = ser.tipoFantastico || '';
                    document.getElementById('serDescricaoExtra').value = ser.descricaoExtra || '';
                } else if (ser.tipo === 'robots') {
                    document.getElementById('serTipoRobo').value = ser.tipoRobo || '';
                    document.getElementById('serDescricaoExtra').value = ser.descricaoExtra || '';
                } else if (ser.tipo === 'aliens') {
                    document.getElementById('serTipoAlien').value = ser.tipoAlien || '';
                    document.getElementById('serDescricaoExtra').value = ser.descricaoExtra || '';
                }
            }
        }

        function editSer(index) {
            const ser = configuredSeres[index];
            showSerForm(configuredSeres[index]);
        }

        function deleteSer(index) {
            if (confirm('Tem certeza que deseja excluir este ser?')) {
                configuredSeres.splice(index, 1);
                updateSeresPreview();
            }
        }

        function finalizarSeresConfig() {
            if (configuredSeres.length === 0) {
                alert('Configure pelo menos um ser antes de finalizar.');
                return;
            }
            
            closeSeresConfigModal();
            updatePrompt();
        }

        function generateSeresPromptText() {
            if (configuredSeres.length === 0) return '';
            
            const seresDescriptions = configuredSeres.map((ser, index) => {
                let desc = [];
                
                // Nome/identificação
                const nome = ser.nome || `${ser.tipo === 'humanos' ? 'pessoa' : 'animal'} ${index + 1}`;
                desc.push(nome);
                
                if (ser.tipo === 'humanos') {
                    // Características básicas humanas
                    if (ser.genero && ser.idade) {
                        desc.push(`(${ser.genero}, ${ser.idade})`);
                    } else if (ser.genero) {
                        desc.push(`(${ser.genero})`);
                    } else if (ser.idade) {
                        desc.push(`(${ser.idade})`);
                    }
                    
                    // Características físicas
                    let fisicas = [];
                    if (ser.altura) fisicas.push(ser.altura);
                    if (ser.peso) fisicas.push(ser.peso);
                    if (ser.tomPele) fisicas.push(`pele ${ser.tomPele.replace('_', ' ')}`);
                    if (ser.corOlhos) fisicas.push(`olhos ${ser.corOlhos}`);
                    
                    if (fisicas.length > 0) {
                        desc.push(fisicas.join(', '));
                    }
                    
                    // Cabelo
                    let cabelo = [];
                    if (ser.corCabelo) cabelo.push(ser.corCabelo.replace('_', ' '));
                    if (ser.tipoCabelo && ser.tipoCabelo !== 'careca') cabelo.push(ser.tipoCabelo);
                    if (ser.comprimentoCabelo) cabelo.push(ser.comprimentoCabelo.replace('_', ' '));
                    
                    if (cabelo.length > 0) {
                        desc.push(`cabelo ${cabelo.join(' ')}`);
                    }
                    
                    // Vestimenta
                    if (ser.traje) {
                        let roupa = ser.traje;
                        if (ser.descricaoRoupa) {
                            roupa += ` (${ser.descricaoRoupa})`;
                        }
                        desc.push(`vestindo ${roupa}`);
                    }
                    
                } else if (ser.tipo === 'animais') {
                    // Características do animal
                    if (ser.tipoAnimal) {
                        desc.push(`(${ser.tipoAnimal})`);
                    }
                    
                    let caracteristicas = [];
                    if (ser.porte) caracteristicas.push(`porte ${ser.porte.replace('_', ' ')}`);
                    if (ser.corPrincipal) caracteristicas.push(`cor ${ser.corPrincipal}`);
                    if (ser.texturaPelo) caracteristicas.push(`pelo ${ser.texturaPelo}`);
                    if (ser.comprimentoPelo) caracteristicas.push(`${ser.comprimentoPelo.replace('_', ' ')}`);
                    
                    if (caracteristicas.length > 0) {
                        desc.push(caracteristicas.join(', '));
                    }
                    
                    if (ser.descricaoExtra) {
                        desc.push(ser.descricaoExtra);
                    }
                
                } else if (ser.tipo === 'fantasticos') {
                    // Ser fantástico
                    if (ser.tipoFantastico) {
                        desc.push(`(${ser.tipoFantastico})`);
                    }
                    
                    if (ser.descricaoExtra) {
                        desc.push(ser.descricaoExtra);
                    }
                
                } else if (ser.tipo === 'robots') {
                    // Robô ou IA
                    if (ser.tipoRobo) {
                        desc.push(`(${ser.tipoRobo})`);
                    }
                    
                    if (ser.descricaoExtra) {
                        desc.push(ser.descricaoExtra);
                    }
                
                } else if (ser.tipo === 'aliens') {
                    // Alien
                    if (ser.tipoAlien) {
                        desc.push(`(${ser.tipoAlien})`);
                    }
                    
                    if (ser.descricaoExtra) {
                        desc.push(ser.descricaoExtra);
                    }
                }
                
                return desc.join(', ');
            });
            
            if (configuredSeres.length === 1) {
                return `com ${seresDescriptions[0]}`;
            } else {
                return `com ${seresDescriptions.join(' e ')}`;
            }
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
                    // Usar subcategoria específica com base no ID real
                    const subcatId = selectedSubcategories[2];
                    
                    // Buscar a descrição da subcategoria selecionada
                    const mainCategory = steps[2].options.find(opt => opt.id === selectedChoices[2]);
                    if (mainCategory && mainCategory.subcategories) {
                        const subcategory = mainCategory.subcategories.find(sub => sub.id === subcatId);
                        if (subcategory) {
                            ambienteText = `em ${subcategory.title.toLowerCase()} - ${subcategory.description.toLowerCase()}`;
                        }
                    }
                } else if (selectedChoices[2]) {
                    // Usar categoria geral
                    const mainCategory = steps[2].options.find(opt => opt.id === selectedChoices[2]);
                    if (mainCategory) {
                        ambienteText = `em um ambiente de ${mainCategory.title.toLowerCase()} - ${mainCategory.description.toLowerCase()}`;
                    }
                }
                
                if (ambienteText) {
                    parts.push(ambienteText);
                }
            }

            // Etapas 3-6: Processar outras categorias se existirem
            for (let step = 3; step <= 6; step++) {
                if (selectedChoices[step]) {
                    let stepText = '';
                    
                    if (selectedSubcategories[step]) {
                        // Usar subcategoria específica
                        const mainCategory = steps[step].options.find(opt => opt.id === selectedChoices[step]);
                        if (mainCategory && mainCategory.subcategories) {
                            const subcategory = mainCategory.subcategories.find(sub => sub.id === selectedSubcategories[step]);
                            if (subcategory) {
                                stepText = `${subcategory.title.toLowerCase()}`;
                            }
                        }
                    } else {
                        // Usar categoria geral
                        const mainCategory = steps[step].options.find(opt => opt.id === selectedChoices[step]);
                        if (mainCategory) {
                            stepText = `${mainCategory.title.toLowerCase()}`;
                        }
                    }
                    
                    if (stepText) {
                        parts.push(stepText);
                    }
                }
            }

            // Etapa 7: Seres (tratamento especial para configurações detalhadas)
            if (selectedChoices[7] && configuredSeres.length > 0) {
                let seresText = generateSeresPromptText();
                if (seresText) {
                    parts.push(seresText);
                }
            } else if (selectedChoices[7]) {
                // Usar sistema antigo para outras categorias
                let stepText = '';
                
                if (selectedSubcategories[7]) {
                    const mainCategory = steps[7].options.find(opt => opt.id === selectedChoices[7]);
                    if (mainCategory && mainCategory.subcategories) {
                        const subcategory = mainCategory.subcategories.find(sub => sub.id === selectedSubcategories[7]);
                        if (subcategory) {
                            stepText = `com ${subcategory.title.toLowerCase()}`;
                        }
                    }
                } else {
                    const mainCategory = steps[7].options.find(opt => opt.id === selectedChoices[7]);
                    if (mainCategory) {
                        stepText = `com ${mainCategory.title.toLowerCase()}`;
                    }
                }
                
                if (stepText) {
                    parts.push(stepText);
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
            
            // Atualizar estatísticas
            updateStats(prompt);
        }
        
        function updateStats(prompt) {
            const chars = prompt.length;
            const words = prompt.trim() ? prompt.trim().split(/\s+/).length : 0;
            const tokens = Math.ceil(words * 1.3); // Estimativa aproximada

            document.getElementById('charCount').textContent = `${chars} caracteres`;
            document.getElementById('wordCount').textContent = `${words} palavras`;
            document.getElementById('tokenEstimate').textContent = `~${tokens} tokens`;
        }

        // Event listeners para modal de seres usando delegação
        document.addEventListener('click', (e) => {
            // Fechar modal
            if (e.target.id === 'closeSeresConfig' || e.target.id === 'cancelarSer') {
                document.getElementById('seresConfigModal').style.display = 'none';
            }
            
            // Adicionar novo ser
            if (e.target.id === 'adicionarNovoSer') {
                editingSerIndex = -1;
                showSerFormForType(currentSerType);
                document.getElementById('modalTitle').textContent = 'Configurar Novo Ser';
            }
            
            // Salvar ser
            if (e.target.id === 'salvarSer') {
                saveCurrentSer();
            }
            
            // Finalizar configuração
            if (e.target.id === 'finalizarSeres') {
                document.getElementById('seresConfigModal').style.display = 'none';
                updatePrompt();
            }
        });

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

        // Event listeners para navegação início/final
        document.getElementById('firstBtn').addEventListener('click', () => {
            currentStep = 1;
            currentSubstep = 0;
            loadStep(1, 0);
        });

        document.getElementById('lastBtn').addEventListener('click', () => {
            currentStep = 7;
            currentSubstep = 0;
            loadStep(7, 0);
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