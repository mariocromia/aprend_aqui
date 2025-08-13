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
                <span class="current-step">Etapa <span id="currentStepNumber">1</span> de 6</span>
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
                        <span id="navSteps">1 / 6</span>
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
                        <p>Siga as 10 etapas para construir seu prompt perfeito. Você pode navegar entre as etapas a qualquer momento.</p>
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
                            { id: 'praia', title: 'Praia', description: 'Costa marítima com areia e ondas', icon: 'fas fa-umbrella-beach' },
                            { id: 'cachoeira', title: 'Cachoeira', description: 'Quedas d\'água em meio à natureza', icon: 'fas fa-water' },
                            { id: 'montanha', title: 'Montanha', description: 'Picos elevados e vales', icon: 'fas fa-mountain' },
                            { id: 'deserto', title: 'Deserto', description: 'Paisagens áridas e dunas', icon: 'fas fa-sun' },
                            { id: 'floresta', title: 'Floresta', description: 'Mata densa com árvores altas', icon: 'fas fa-tree' },
                            { id: 'canyon', title: 'Canyon', description: 'Formações rochosas profundas', icon: 'fas fa-mountain' },
                            { id: 'lago', title: 'Lago', description: 'Espelho d\'água tranquilo', icon: 'fas fa-water' },
                            { id: 'campo_flores', title: 'Campo de Flores', description: 'Pradaria colorida florida', icon: 'fas fa-seedling' }
                        ]
                    },
                    { 
                        id: 'urbano', 
                        title: 'Urbano', 
                        description: 'Ambientes de cidade e construções', 
                        icon: 'fas fa-city',
                        subcategories: [
                            { id: 'centro_cidade', title: 'Centro da Cidade', description: 'Área central urbana movimentada', icon: 'fas fa-building' },
                            { id: 'arranha_ceus', title: 'Arranha-céus', description: 'Edifícios altos modernos', icon: 'fas fa-city' },
                            { id: 'rua_comercial', title: 'Rua Comercial', description: 'Via com lojas e comércios', icon: 'fas fa-store' },
                            { id: 'ponte', title: 'Ponte', description: 'Estrutura sobre rio ou vale', icon: 'fas fa-bridge' },
                            { id: 'parque_urbano', title: 'Parque Urbano', description: 'Área verde na cidade', icon: 'fas fa-tree' },
                            { id: 'estacao_trem', title: 'Estação de Trem', description: 'Terminal ferroviário', icon: 'fas fa-train' },
                            { id: 'aeroporto', title: 'Aeroporto', description: 'Terminal de aviação', icon: 'fas fa-plane' },
                            { id: 'porto', title: 'Porto', description: 'Área portuária marítima', icon: 'fas fa-ship' }
                        ]
                    },
                    { 
                        id: 'interior', 
                        title: 'Interior', 
                        description: 'Ambientes fechados e construções internas', 
                        icon: 'fas fa-home',
                        subcategories: [
                            { id: 'sala_estar', title: 'Sala de Estar', description: 'Ambiente de convivência familiar', icon: 'fas fa-couch' },
                            { id: 'cozinha', title: 'Cozinha', description: 'Espaço culinário moderno', icon: 'fas fa-utensils' },
                            { id: 'quarto', title: 'Quarto', description: 'Dormitório aconchegante', icon: 'fas fa-bed' },
                            { id: 'escritorio', title: 'Escritório', description: 'Ambiente de trabalho', icon: 'fas fa-desktop' },
                            { id: 'biblioteca', title: 'Biblioteca', description: 'Espaço repleto de livros', icon: 'fas fa-book' },
                            { id: 'atelier', title: 'Ateliê', description: 'Estúdio artístico criativo', icon: 'fas fa-palette' },
                            { id: 'cafe', title: 'Café', description: 'Cafeteria aconchegante', icon: 'fas fa-coffee' },
                            { id: 'museu', title: 'Museu', description: 'Galeria de arte ou história', icon: 'fas fa-university' }
                        ]
                    },
                    { 
                        id: 'fantasia', 
                        title: 'Fantasia', 
                        description: 'Ambientes mágicos e fantásticos', 
                        icon: 'fas fa-magic',
                        subcategories: [
                            { id: 'castelo_magico', title: 'Castelo Mágico', description: 'Fortaleza encantada', icon: 'fas fa-chess-rook' },
                            { id: 'floresta_encantada', title: 'Floresta Encantada', description: 'Mata com seres mágicos', icon: 'fas fa-tree' },
                            { id: 'portal_dimensional', title: 'Portal Dimensional', description: 'Passagem entre mundos', icon: 'fas fa-portal-exit' },
                            { id: 'cidade_flutuante', title: 'Cidade Flutuante', description: 'Metrópole nas nuvens', icon: 'fas fa-cloud' },
                            { id: 'underwater_kingdom', title: 'Reino Subaquático', description: 'Civilização no fundo do mar', icon: 'fas fa-fish' },
                            { id: 'cristal_cave', title: 'Caverna de Cristal', description: 'Gruta com formações luminosas', icon: 'fas fa-gem' },
                            { id: 'sky_temple', title: 'Templo Celestial', description: 'Santuário nas alturas', icon: 'fas fa-place-of-worship' },
                            { id: 'magic_garden', title: 'Jardim Mágico', description: 'Horto com plantas fantásticas', icon: 'fas fa-seedling' }
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
            document.getElementById('navSteps').textContent = `${step} / 6`;
            
            // Atualizar barra de progresso
            const progressFill = document.getElementById('progressFill');
            progressFill.style.width = `${(step / 6) * 100}%`;

            // Atualizar botões de navegação
            const hasSubcategories = substep === 0 && selectedChoices[step] && 
                stepData.options.find(opt => opt.id === selectedChoices[step])?.subcategories;
            
            document.getElementById('prevBtn').disabled = step === 1 && substep === 0;
            document.getElementById('nextBtn').disabled = (step === 6 && substep === 0) || 
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
                if (currentStep < 6) {
                    currentStep++;
                    loadStep(currentStep, 0);
                }
            } else if (currentStep < 6) {
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