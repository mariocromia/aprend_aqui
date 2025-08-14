// Prompt Builder IA - JavaScript
class PromptBuilder {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 6; // Mudou para 6 etapas: Tipo + 5 categorias principais
        this.userChoices = {};
        this.selectedCategories = {
            ambiente: {},
            seres: {},
            acao: {},
            camera: {},
            fala: {}
        };
        this.promptTemplate = {
            image: `Crie uma imagem`,
            video: `Crie um vídeo`
        };
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadStep(1);
        this.loadFromStorage();
    }

    bindEvents() {
        // Navegação entre etapas
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.previousStep());
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.nextStep());
        }

        // Ações do prompt
        const copyBtnPT = document.getElementById('copyBtnPT');
        const copyBtnEN = document.getElementById('copyBtnEN');
        const clearBtn = document.getElementById('clearBtn');
        const exportBtn = document.getElementById('exportBtn');
        
        if (copyBtnPT) {
            copyBtnPT.addEventListener('click', () => this.copyPrompt('PT'));
        }
        
        if (copyBtnEN) {
            copyBtnEN.addEventListener('click', () => this.copyPrompt('EN'));
        }
        
        if (clearBtn) {
            clearBtn.addEventListener('click', () => this.clearAll());
        }
        
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.exportJSON());
        }

        // Modal de ajuda
        const helpBtn = document.getElementById('helpBtn');
        const closeHelp = document.getElementById('closeHelp');
        const helpModal = document.getElementById('helpModal');
        
        if (helpBtn) {
            helpBtn.addEventListener('click', () => this.showHelp());
        }
        
        if (closeHelp) {
            closeHelp.addEventListener('click', () => this.hideHelp());
        }
        
        if (helpModal) {
            // Fechar modal clicando no overlay
            helpModal.addEventListener('click', (e) => {
                if (e.target === e.currentTarget || e.target.classList.contains('modal-overlay')) {
                    this.hideHelp();
                }
            });
        }

        // Salvar automaticamente
        setInterval(() => this.saveToStorage(), 5000);
    }

    // Função original removida - usando a nova loadStep mais abaixo

    getStepData(step) {
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
                isCategory: true,
                categoryType: 'ambiente',
                options: [
                    { 
                        id: 'natureza', 
                        title: 'Natureza', 
                        description: 'Ambientes naturais e paisagens', 
                        icon: 'fas fa-tree',
                        subcategories: {
                            'montanha': {
                                title: 'Montanha',
                                options: ['pico nevado', 'cordilheira', 'montanha rochosa', 'colina verde', 'vale montanhoso', 'encosta', 'precipício', 'planalto']
                            },
                            'cachoeira': {
                                title: 'Cachoeira',
                                options: ['cascata alta', 'queda d\'água', 'cachoeira tropical', 'piscina natural', 'rio com corredeira', 'cachoeira congelada']
                            },
                            'praia': {
                                title: 'Praia',
                                options: ['praia tropical', 'costa rochosa', 'praia deserta', 'praia com palmeiras', 'praia ao pôr do sol', 'praia de areia branca', 'praia vulcânica']
                            },
                            'deserto': {
                                title: 'Deserto',
                                options: ['dunas de areia', 'deserto rochoso', 'oásis', 'deserto com cactos', 'deserto gelado', 'deserto vermelho']
                            },
                            'floresta': {
                                title: 'Floresta',
                                options: ['floresta tropical', 'floresta temperada', 'floresta de pinheiros', 'floresta encantada', 'floresta densa', 'clareira', 'floresta bamboo']
                            },
                            'lago': {
                                title: 'Lago',
                                options: ['lago cristalino', 'lago de montanha', 'lagoa azul', 'lago congelado', 'lago com nenúfares', 'lago refletindo']
                            },
                            'campo': {
                                title: 'Campo',
                                options: ['campo de flores', 'pradaria', 'campo de trigo', 'pastagem verde', 'campo lavanda', 'savana']
                            },
                            'oceano': {
                                title: 'Oceano',
                                options: ['mar aberto', 'recife de coral', 'fundo do mar', 'ondas gigantes', 'mar calmo', 'tempestade marítima']
                            }
                        }
                    },
                    { 
                        id: 'urbano', 
                        title: 'Urbano', 
                        description: 'Ambientes de cidade e construções', 
                        icon: 'fas fa-city',
                        subcategories: {
                            'avenida': {
                                title: 'Avenida',
                                options: ['avenida movimentada', 'avenida à noite', 'avenida comercial', 'avenida arborizada', 'avenida principal']
                            },
                            'rua': {
                                title: 'Rua',
                                options: ['rua residencial', 'rua estreita', 'rua de paralelepípedos', 'rua com lojas', 'rua vazia', 'beco urbano']
                            },
                            'transito': {
                                title: 'Trânsito',
                                options: ['engarrafamento', 'cruzamento movimentado', 'semáforo', 'passagem de pedestres', 'estacionamento']
                            },
                            'predio': {
                                title: 'Prédio',
                                options: ['arranha-céu', 'prédio comercial', 'prédio residencial', 'edifício histórico', 'prédio moderno', 'fachada de vidro']
                            },
                            'praca': {
                                title: 'Praça',
                                options: ['praça central', 'praça com fonte', 'praça arborizada', 'praça de alimentação', 'praça histórica']
                            },
                            'ponte': {
                                title: 'Ponte',
                                options: ['ponte suspensa', 'ponte de pedra', 'ponte moderna', 'ponte sobre rio', 'viaduto urbano']
                            }
                        }
                    },
                    { 
                        id: 'interior', 
                        title: 'Interior', 
                        description: 'Ambientes fechados e construções internas', 
                        icon: 'fas fa-home',
                        subcategories: {
                            'casa': {
                                title: 'Casa',
                                options: ['sala de estar', 'cozinha moderna', 'quarto aconchegante', 'banheiro luxuoso', 'biblioteca', 'sótão', 'porão']
                            },
                            'escritorio': {
                                title: 'Escritório',
                                options: ['escritório corporativo', 'home office', 'sala de reunião', 'coworking', 'escritório moderno']
                            },
                            'loja': {
                                title: 'Loja',
                                options: ['shopping center', 'loja de roupas', 'supermercado', 'livraria', 'café', 'restaurante', 'loja de tecnologia']
                            },
                            'escola': {
                                title: 'Escola',
                                options: ['sala de aula', 'biblioteca escolar', 'laboratório', 'auditório', 'pátio escolar', 'universidade']
                            },
                            'hospital': {
                                title: 'Hospital',
                                options: ['quarto de hospital', 'centro cirúrgico', 'recepção médica', 'ambulância', 'laboratório médico']
                            },
                            'igreja': {
                                title: 'Igreja',
                                options: ['catedral gótica', 'igreja moderna', 'capela', 'altar', 'vitral colorido']
                            }
                        }
                    },
                    { 
                        id: 'fantasia', 
                        title: 'Fantasia', 
                        description: 'Ambientes mágicos e fantásticos', 
                        icon: 'fas fa-magic',
                        subcategories: {
                            'magico': {
                                title: 'Mágico',
                                options: ['floresta encantada', 'castelo flutuante', 'portal mágico', 'caverna cristalina', 'jardim mágico']
                            },
                            'medieval': {
                                title: 'Medieval',
                                options: ['castelo medieval', 'vila medieval', 'taverna', 'fortaleza', 'torre do mago', 'dungeon']
                            },
                            'futurista': {
                                title: 'Futurista',
                                options: ['cidade futurista', 'nave espacial', 'laboratório sci-fi', 'colônia espacial', 'planeta alienígena']
                            },
                            'apocaliptico': {
                                title: 'Apocalíptico',
                                options: ['cidade em ruínas', 'wasteland', 'bunker', 'mundo pós-apocalíptico', 'zona radioativa']
                            }
                        }
                    }
                ],
                customField: { label: 'Ambiente personalizado', type: 'textarea', placeholder: 'Descreva um ambiente específico...' }
            },
            3: {
                title: 'Seres',
                description: 'Defina os personagens e seres presentes na cena',
                icon: 'fas fa-users',
                isCategory: true,
                categoryType: 'seres',
                options: [
                    { 
                        id: 'humanos', 
                        title: 'Humanos', 
                        description: 'Pessoas e personagens humanos', 
                        icon: 'fas fa-user',
                        subcategories: {
                            'pessoa': {
                                title: 'Pessoa',
                                options: ['homem', 'mulher', 'criança', 'idoso', 'jovem', 'adolescente', 'bebê']
                            },
                            'profissao': {
                                title: 'Profissão',
                                options: ['médico', 'professor', 'policial', 'bombeiro', 'chef', 'artista', 'engenheiro', 'advogado', 'agricultor']
                            },
                            'estilo': {
                                title: 'Estilo',
                                options: ['casual', 'formal', 'esportivo', 'elegante', 'vintage', 'moderno', 'bohemio']
                            }
                        }
                    },
                    { 
                        id: 'animais', 
                        title: 'Animais', 
                        description: 'Fauna e vida selvagem', 
                        icon: 'fas fa-paw',
                        subcategories: {
                            'domesticos': {
                                title: 'Domésticos',
                                options: ['cão', 'gato', 'pássaro', 'coelho', 'hamster', 'peixe', 'cavalo']
                            },
                            'selvagens': {
                                title: 'Selvagens',
                                options: ['leão', 'tigre', 'elefante', 'urso', 'lobo', 'raposa', 'veado', 'águia']
                            },
                            'marinhos': {
                                title: 'Marinhos',
                                options: ['baleia', 'golfinho', 'tubarão', 'polvo', 'tartaruga marinha', 'peixe colorido']
                            }
                        }
                    },
                    { 
                        id: 'fantasticos', 
                        title: 'Fantásticos', 
                        description: 'Criaturas míticas e fantásticas', 
                        icon: 'fas fa-dragon',
                        subcategories: {
                            'mitologicos': {
                                title: 'Mitológicos',
                                options: ['dragão', 'unicórnio', 'fênix', 'grifo', 'centauro', 'sereia', 'minotauro']
                            },
                            'magicos': {
                                title: 'Mágicos',
                                options: ['fada', 'duende', 'elfo', 'mago', 'bruxa', 'anjo', 'demônio']
                            }
                        }
                    }
                ],
                customField: { label: 'Ser personalizado', type: 'textarea', placeholder: 'Descreva um personagem específico...' }
            },
            4: {
                title: 'Ação',
                description: 'Defina o que está acontecendo na cena',
                icon: 'fas fa-running',
                isCategory: true,
                categoryType: 'acao',
                options: [
                    { 
                        id: 'movimento', 
                        title: 'Movimento', 
                        description: 'Ações de movimento e deslocamento', 
                        icon: 'fas fa-running',
                        subcategories: {
                            'caminhando': {
                                title: 'Caminhando',
                                options: ['caminhando lentamente', 'passeando', 'caminhada rápida', 'andando pela rua', 'caminhada no parque']
                            },
                            'correndo': {
                                title: 'Correndo',
                                options: ['correndo rápido', 'corrida matinal', 'fugindo', 'corrida esportiva', 'sprint']
                            },
                            'voando': {
                                title: 'Voando',
                                options: ['voando alto', 'planando', 'voo rasante', 'voando em círculos', 'voo majestoso']
                            },
                            'nadando': {
                                title: 'Nadando',
                                options: ['nadando na piscina', 'mergulhando', 'flutuando', 'nado borboleta', 'nadando no mar']
                            }
                        }
                    },
                    { 
                        id: 'interacao', 
                        title: 'Interação', 
                        description: 'Ações sociais e de relacionamento', 
                        icon: 'fas fa-handshake',
                        subcategories: {
                            'conversando': {
                                title: 'Conversando',
                                options: ['conversa amigável', 'discussão', 'sussurrando', 'gritando', 'apresentação']
                            },
                            'abraçando': {
                                title: 'Abraçando',
                                options: ['abraço carinhoso', 'abraço de despedida', 'abraço de grupo', 'abraço romântico']
                            },
                            'brincando': {
                                title: 'Brincando',
                                options: ['jogando bola', 'brincadeira infantil', 'jogos de tabuleiro', 'videogame']
                            }
                        }
                    },
                    { 
                        id: 'trabalho', 
                        title: 'Trabalho', 
                        description: 'Ações profissionais e produtivas', 
                        icon: 'fas fa-briefcase',
                        subcategories: {
                            'escrevendo': {
                                title: 'Escrevendo',
                                options: ['digitando no computador', 'escrevendo à mão', 'tomando notas', 'assinando documento']
                            },
                            'construindo': {
                                title: 'Construindo',
                                options: ['martelando', 'pintando parede', 'soldando', 'usando ferramentas']
                            },
                            'cozinhando': {
                                title: 'Cozinhando',
                                options: ['preparando comida', 'cortando legumes', 'mexendo panela', 'assando']
                            }
                        }
                    }
                ],
                customField: { label: 'Ação personalizada', type: 'textarea', placeholder: 'Descreva uma ação específica...' }
            },
            5: {
                title: 'Câmera',
                description: 'Defina o enquadramento e perspectiva',
                icon: 'fas fa-camera',
                isCategory: true,
                categoryType: 'camera',
                options: [
                    { 
                        id: 'enquadramento', 
                        title: 'Enquadramento', 
                        description: 'Tipos de planos e enquadramentos', 
                        icon: 'fas fa-crop',
                        subcategories: {
                            'plano': {
                                title: 'Plano',
                                options: ['close-up', 'plano médio', 'plano geral', 'primeiro plano', 'plano americano', 'plano conjunto']
                            },
                            'angulo': {
                                title: 'Ângulo',
                                options: ['ângulo baixo', 'ângulo alto', 'vista aérea', 'ângulo holandês', 'contra-plongée', 'plongée']
                            },
                            'perspectiva': {
                                title: 'Perspectiva',
                                options: ['perspectiva frontal', 'perfil', 'três quartos', 'costas', 'vista lateral']
                            }
                        }
                    },
                    { 
                        id: 'tecnica', 
                        title: 'Técnica', 
                        description: 'Técnicas fotográficas e cinematográficas', 
                        icon: 'fas fa-camera-retro',
                        subcategories: {
                            'foco': {
                                title: 'Foco',
                                options: ['foco seletivo', 'profundidade de campo rasa', 'tudo em foco', 'desfoque de fundo', 'macro']
                            },
                            'movimento': {
                                title: 'Movimento',
                                options: ['câmera estática', 'panorâmica', 'travelling', 'zoom', 'câmera na mão']
                            },
                            'composicao': {
                                title: 'Composição',
                                options: ['regra dos terços', 'simetria', 'linhas guia', 'enquadramento natural', 'padrões']
                            }
                        }
                    },
                    { 
                        id: 'iluminacao', 
                        title: 'Iluminação', 
                        description: 'Tipos de iluminação e atmosfera', 
                        icon: 'fas fa-lightbulb',
                        subcategories: {
                            'natural': {
                                title: 'Natural',
                                options: ['luz do sol', 'hora dourada', 'luz difusa', 'contraluz', 'luz da manhã', 'pôr do sol']
                            },
                            'artificial': {
                                title: 'Artificial',
                                options: ['luz de estúdio', 'neon', 'luz de vela', 'luz fria', 'luz quente', 'holofotes']
                            },
                            'atmosfera': {
                                title: 'Atmosfera',
                                options: ['dramática', 'suave', 'misteriosa', 'romântica', 'sombria', 'brilhante']
                            }
                        }
                    }
                ],
                customField: { label: 'Configuração de câmera personalizada', type: 'input', placeholder: 'Ex: lente 85mm, DOF raso, foco seletivo...' }
            },
            6: {
                title: 'Fala',
                description: 'Adicione diálogos e elementos de comunicação',
                icon: 'fas fa-comment',
                isCategory: true,
                categoryType: 'fala',
                options: [
                    { 
                        id: 'dialogo', 
                        title: 'Diálogo', 
                        description: 'Conversas e falas entre personagens', 
                        icon: 'fas fa-comments',
                        subcategories: {
                            'conversa': {
                                title: 'Conversa',
                                options: ['conversa casual', 'conversa séria', 'discussão', 'sussurro', 'grito']
                            },
                            'emocao': {
                                title: 'Emoção',
                                options: ['feliz', 'triste', 'raiva', 'surpresa', 'medo', 'amor', 'desprezo']
                            },
                            'tom': {
                                title: 'Tom',
                                options: ['calmo', 'agitado', 'autoritário', 'gentil', 'sarcástico', 'romântico']
                            }
                        }
                    },
                    { 
                        id: 'expressao', 
                        title: 'Expressão', 
                        description: 'Expressões faciais e corporais', 
                        icon: 'fas fa-smile',
                        subcategories: {
                            'facial': {
                                title: 'Facial',
                                options: ['sorrindo', 'franzindo a testa', 'olhos arregalados', 'piscando', 'chorando', 'rindo']
                            },
                            'corporal': {
                                title: 'Corporal',
                                options: ['acenando', 'apontando', 'abraçando', 'gesticulando', 'encolhendo os ombros']
                            }
                        }
                    },
                    { 
                        id: 'texto', 
                        title: 'Texto', 
                        description: 'Elementos textuais na cena', 
                        icon: 'fas fa-font',
                        subcategories: {
                            'balao': {
                                title: 'Balão de Fala',
                                options: ['balão simples', 'balão de pensamento', 'balão de grito', 'balão sussurro']
                            },
                            'legenda': {
                                title: 'Legenda',
                                options: ['legenda inferior', 'título', 'texto sobreposto', 'nome do personagem']
                            },
                            'placas': {
                                title: 'Placas e Sinais',
                                options: ['placa de rua', 'outdoor', 'cartaz', 'letreiro luminoso', 'placa de trânsito']
                            }
                        }
                    }
                ],
                customField: { label: 'Fala personalizada', type: 'textarea', placeholder: 'Adicione diálogos ou textos específicos...' }
            };

        return steps[step] || steps[1];
    }

    // Nova função para lidar com categorias hierárquicas
    createCategoryInterface(stepData) {
        if (!stepData.isCategory) {
            return this.createStandardInterface(stepData);
        }

        let html = `
            <h2 class="step-title">
                <i class="${stepData.icon}"></i>
                ${stepData.title}
            </h2>
            <p class="step-description">${stepData.description}</p>
        `;

        // Se não há seleção de categoria principal ainda
        const categoryState = this.selectedCategories[stepData.categoryType];
        
        if (!categoryState.mainCategory) {
            html += `<div class="category-grid">`;
            stepData.options.forEach(category => {
                html += `
                    <div class="category-card" data-category="${category.id}">
                        <div class="category-icon">
                            <i class="${category.icon}"></i>
                        </div>
                        <div class="category-title">${category.title}</div>
                        <div class="category-description">${category.description}</div>
                    </div>
                `;
            });
            html += `</div>`;
        }
        // Se categoria principal foi selecionada, mostrar subcategorias
        else if (!this.selectedCategories[stepData.categoryType].subCategory) {
            const mainCategory = stepData.options.find(cat => 
                cat.id === this.selectedCategories[stepData.categoryType].mainCategory
            );
            
            html += `
                <div class="breadcrumb">
                    <span class="breadcrumb-item active">${mainCategory.title}</span>
                    <button class="btn-back" onclick="promptBuilder.resetCategory('${stepData.categoryType}', 'main')">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </button>
                </div>
                <div class="subcategory-grid">
            `;
            
            Object.keys(mainCategory.subcategories).forEach(subKey => {
                const sub = mainCategory.subcategories[subKey];
                html += `
                    <div class="subcategory-card" data-subcategory="${subKey}">
                        <div class="subcategory-title">${sub.title}</div>
                        <div class="subcategory-count">${sub.options.length} opções</div>
                    </div>
                `;
            });
            html += `</div>`;
        }
        // Se subcategoria foi selecionada, mostrar opções finais
        else {
            const mainCategory = stepData.options.find(cat => 
                cat.id === this.selectedCategories[stepData.categoryType].mainCategory
            );
            const subCategory = mainCategory.subcategories[this.selectedCategories[stepData.categoryType].subCategory];
            
            html += `
                <div class="breadcrumb">
                    <span class="breadcrumb-item">${mainCategory.title}</span>
                    <i class="fas fa-chevron-right"></i>
                    <span class="breadcrumb-item active">${subCategory.title}</span>
                    <button class="btn-back" onclick="promptBuilder.resetCategory('${stepData.categoryType}', 'sub')">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </button>
                </div>
                <div class="options-grid">
            `;
            
            subCategory.options.forEach(option => {
                const isSelected = this.selectedCategories[stepData.categoryType].finalOption === option ? 'selected' : '';
                html += `
                    <div class="option-card ${isSelected}" data-option="${option}">
                        <div class="option-title">${option}</div>
                    </div>
                `;
            });
            html += `</div>`;
        }

        if (stepData.customField) {
            html += this.createCustomField(stepData.customField);
        }

        return html;
    }

    createStandardInterface(stepData) {
        const optionsHTML = stepData.options.map(option => this.createOptionCard(option)).join('');
        
        return `
            <h2 class="step-title">
                <i class="${stepData.icon}"></i>
                ${stepData.title}
            </h2>
            <p class="step-description">${stepData.description}</p>
            <div class="options-grid">
                ${optionsHTML}
            </div>
            ${stepData.customField ? this.createCustomField(stepData.customField) : ''}
        `;
    }

    resetCategory(categoryType, level) {
        if (level === 'main') {
            this.selectedCategories[categoryType] = {};
        } else if (level === 'sub') {
            delete this.selectedCategories[categoryType].subCategory;
            delete this.selectedCategories[categoryType].finalOption;
        }
        this.loadStep(this.currentStep);
        this.updatePrompt();
    }

    loadStep(step) {
        const stepContent = document.getElementById('stepContent');
        if (!stepContent) {
            console.error('stepContent element not found!');
            return;
        }
        
        const stepData = this.getStepData(step);
        if (!stepData) {
            console.error('No step data found for step:', step);
            return;
        }
        
        try {
            stepContent.innerHTML = stepData.isCategory ? 
                this.createCategoryInterface(stepData) : 
                this.createStandardInterface(stepData);

            // Bind events para as opções
            this.bindOptionEvents();
            
            // Atualizar prompt
            this.updatePrompt();
        } catch (error) {
            console.error('Error loading step:', error);
            stepContent.innerHTML = '<h2>Erro ao carregar etapa</h2><p>Por favor, recarregue a página.</p>';
        }
    }

    createOptionCard(option) {
        const isSelected = this.userChoices[this.currentStep]?.includes(option.id) ? 'selected' : '';
        return `
            <div class="option-card ${isSelected}" data-option="${option.id}">
                <div class="option-icon">
                    <i class="${option.icon}"></i>
                </div>
                <div class="option-title">${option.title}</div>
                <div class="option-description">${option.description}</div>
            </div>
        `;
    }

    createCustomField(field) {
        const value = this.userChoices[`${this.currentStep}_custom`] || '';
        if (field.type === 'textarea') {
            return `
                <div class="custom-input">
                    <label for="customField">${field.label}</label>
                    <textarea id="customField" placeholder="${field.placeholder}" rows="3">${value}</textarea>
                </div>
            `;
        } else {
            return `
                <div class="custom-input">
                    <label for="customField">${field.label}</label>
                    <input type="text" id="customField" placeholder="${field.placeholder}" value="${value}">
                </div>
            `;
        }
    }

    bindOptionEvents() {
        // Cards de categoria principal
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', (e) => {
                const category = e.currentTarget.dataset.category;
                const stepData = this.getStepData(this.currentStep);
                this.selectedCategories[stepData.categoryType].mainCategory = category;
                this.loadStep(this.currentStep);
            });
        });

        // Cards de subcategoria
        document.querySelectorAll('.subcategory-card').forEach(card => {
            card.addEventListener('click', (e) => {
                const subcategory = e.currentTarget.dataset.subcategory;
                const stepData = this.getStepData(this.currentStep);
                this.selectedCategories[stepData.categoryType].subCategory = subcategory;
                this.loadStep(this.currentStep);
            });
        });

        // Cards de opção final
        document.querySelectorAll('.option-card').forEach(card => {
            card.addEventListener('click', (e) => {
                const option = e.currentTarget.dataset.option;
                const stepData = this.getStepData(this.currentStep);
                
                if (stepData.isCategory) {
                    // Para categorias hierárquicas
                    this.selectedCategories[stepData.categoryType].finalOption = option;
                    // Atualizar visual
                    document.querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
                    e.currentTarget.classList.add('selected');
                } else {
                    // Para opções normais
                    this.toggleOption(option);
                }
                this.updatePrompt();
            });
        });

        // Campo personalizado
        const customField = document.getElementById('customField');
        if (customField) {
            customField.addEventListener('input', (e) => {
                this.userChoices[`${this.currentStep}_custom`] = e.target.value;
                this.updatePrompt();
            });
        }
    }

    toggleOption(option) {
        if (!this.userChoices[this.currentStep]) {
            this.userChoices[this.currentStep] = [];
        }

        const index = this.userChoices[this.currentStep].indexOf(option);
        if (index > -1) {
            this.userChoices[this.currentStep].splice(index, 1);
        } else {
            // Para algumas etapas, permitir apenas uma seleção
            if ([1, 8].includes(this.currentStep)) {
                this.userChoices[this.currentStep] = [option];
            } else {
                this.userChoices[this.currentStep].push(option);
            }
        }

        // Atualizar visual
        document.querySelectorAll('.option-card').forEach(card => {
            card.classList.remove('selected');
        });

        this.userChoices[this.currentStep].forEach(selectedOption => {
            const card = document.querySelector(`[data-option="${selectedOption}"]`);
            if (card) card.classList.add('selected');
        });

        this.updatePrompt();
    }

    updatePrompt() {
        // Gerar prompt em português
        const promptPT = this.generatePrompt('PT');
        // Gerar prompt em inglês
        const promptEN = this.generatePrompt('EN');

        // Atualizar textareas
        const promptTextPT = document.getElementById('promptTextPT');
        const promptTextEN = document.getElementById('promptTextEN');
        
        if (promptTextPT) promptTextPT.value = promptPT;
        if (promptTextEN) promptTextEN.value = promptEN;

        // Atualizar estatísticas
        this.updateStats(promptPT, 'PT');
        this.updateStats(promptEN, 'EN');
    }

    generatePrompt(language) {
        const contentType = this.userChoices[1]?.[0] || 'image';
        let prompt = '';

        // Início do prompt
        if (language === 'PT') {
            prompt = contentType === 'image' ? 'Crie uma imagem' : 'Crie um vídeo';
        } else {
            prompt = contentType === 'image' ? 'Create an image' : 'Create a video';
        }

        const parts = [];

        // Ambiente (etapa 2)
        const ambienteData = this.selectedCategories.ambiente;
        if (ambienteData.finalOption) {
            const mainCat = this.getStepData(2).options.find(opt => opt.id === ambienteData.mainCategory);
            if (mainCat) {
                if (language === 'PT') {
                    if (ambienteData.mainCategory === 'natureza') {
                        parts.push(`de um ambiente natural`);
                    } else if (ambienteData.mainCategory === 'urbano') {
                        parts.push(`de um ambiente urbano`);
                    } else if (ambienteData.mainCategory === 'interior') {
                        parts.push(`de um ambiente interno`);
                    } else if (ambienteData.mainCategory === 'fantasia') {
                        parts.push(`de um ambiente fantástico`);
                    }
                    parts.push(`de ${this.translateToLanguage(ambienteData.finalOption, language)}`);
                } else {
                    if (ambienteData.mainCategory === 'natureza') {
                        parts.push(`in a natural environment`);
                    } else if (ambienteData.mainCategory === 'urbano') {
                        parts.push(`in an urban environment`);
                    } else if (ambienteData.mainCategory === 'interior') {
                        parts.push(`in an indoor environment`);
                    } else if (ambienteData.mainCategory === 'fantasia') {
                        parts.push(`in a fantasy environment`);
                    }
                    parts.push(`of ${this.translateToLanguage(ambienteData.finalOption, language)}`);
                }
            }
        }
        if (this.userChoices['2_custom']) {
            const prefix = language === 'PT' ? 'de' : 'of';
            parts.push(`${prefix} ${this.userChoices['2_custom']}`);
        }

        // Seres (etapa 3)
        const seresData = this.selectedCategories.seres;
        if (seresData.finalOption) {
            const prefix = language === 'PT' ? 'com' : 'with';
            parts.push(`${prefix} ${this.translateToLanguage(seresData.finalOption, language)}`);
        }
        if (this.userChoices['3_custom']) {
            const prefix = language === 'PT' ? 'com' : 'with';
            parts.push(`${prefix} ${this.userChoices['3_custom']}`);
        }

        // Ação (etapa 4)
        const acaoData = this.selectedCategories.acao;
        if (acaoData.finalOption) {
            parts.push(this.translateToLanguage(acaoData.finalOption, language));
        }
        if (this.userChoices['4_custom']) {
            parts.push(this.userChoices['4_custom']);
        }

        // Câmera (etapa 5)
        const cameraData = this.selectedCategories.camera;
        if (cameraData.finalOption) {
            parts.push(this.translateToLanguage(cameraData.finalOption, language));
        }
        if (this.userChoices['5_custom']) {
            parts.push(this.userChoices['5_custom']);
        }

        // Fala (etapa 6)
        const falaData = this.selectedCategories.fala;
        if (falaData.finalOption) {
            parts.push(this.translateToLanguage(falaData.finalOption, language));
        }
        if (this.userChoices['6_custom']) {
            parts.push(this.userChoices['6_custom']);
        }

        // Construir prompt final
        if (parts.length > 0) {
            prompt += ' ' + parts.join(', ');
        }

        return prompt;
    }

    translateToLanguage(text, language) {
        // Por simplicidade, retornar o texto original
        // Em uma implementação mais robusta, aqui haveria um dicionário de traduções
        return text;
    }

    getOptionLabel(step, optionId) {
        const stepData = this.getStepData(step);
        const option = stepData.options.find(o => o.id === optionId);
        return option ? option.title : optionId;
    }

    updateStats(prompt, language) {
        const chars = prompt.length;
        const words = prompt.trim() ? prompt.trim().split(/\s+/).length : 0;
        const tokens = Math.ceil(words * 1.3); // Estimativa aproximada

        const suffix = language || '';
        const charLabel = language === 'EN' ? 'characters' : 'caracteres';
        const wordLabel = language === 'EN' ? 'words' : 'palavras';
        
        const charCountEl = document.getElementById(`charCount${suffix}`);
        const wordCountEl = document.getElementById(`wordCount${suffix}`);
        const tokenEstimateEl = document.getElementById(`tokenEstimate${suffix}`);
        
        if (charCountEl) charCountEl.textContent = `${chars} ${charLabel}`;
        if (wordCountEl) wordCountEl.textContent = `${words} ${wordLabel}`;
        if (tokenEstimateEl) tokenEstimateEl.textContent = `~${tokens} tokens`;
    }

    // Função restoreSelections removida - não é mais necessária com o novo sistema hierárquico

    nextStep() {
        if (this.currentStep < this.totalSteps) {
            this.goToStep(this.currentStep + 1);
        }
    }

    previousStep() {
        if (this.currentStep > 1) {
            this.goToStep(this.currentStep - 1);
        }
    }

    goToStep(step) {
        if (step < 1 || step > this.totalSteps) return;

        this.currentStep = step;
        this.updateStepper();
        this.loadStep(step);
        this.updateNavigation();
    }

    updateStepper() {
        // Atualizar barra de progresso compacta
        const progressFill = document.getElementById('progressFill');
        const currentStepNumber = document.getElementById('currentStepNumber');
        const currentStepTitle = document.getElementById('currentStepTitle');
        const navSteps = document.getElementById('navSteps');
        
        if (progressFill) {
            const progressPercentage = (this.currentStep / this.totalSteps) * 100;
            progressFill.style.width = `${progressPercentage}%`;
        }
        
        if (currentStepNumber) {
            currentStepNumber.textContent = this.currentStep;
        }
        
        if (currentStepTitle) {
            const stepData = this.getStepData(this.currentStep);
            currentStepTitle.textContent = stepData.title;
        }
        
        if (navSteps) {
            navSteps.textContent = `${this.currentStep} / ${this.totalSteps}`;
        }
    }

    updateNavigation() {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        if (prevBtn) {
            prevBtn.disabled = this.currentStep === 1;
        }
        
        if (nextBtn) {
            if (this.currentStep === this.totalSteps) {
                nextBtn.innerHTML = '<i class="fas fa-check"></i>';
                nextBtn.title = 'Finalizar';
            } else {
                nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
                nextBtn.title = 'Próxima etapa';
            }
        }
    }

    copyPrompt(language) {
        const textareaId = language === 'EN' ? 'promptTextEN' : 'promptTextPT';
        const promptText = document.getElementById(textareaId)?.value || '';
        
        if (!promptText.trim()) {
            const message = language === 'EN' ? 'No prompt to copy' : 'Nenhum prompt para copiar';
            this.showToast(message, 'warning');
            return;
        }

        navigator.clipboard.writeText(promptText).then(() => {
            const message = language === 'EN' ? 'Prompt copied successfully!' : 'Prompt copiado com sucesso!';
            this.showToast(message, 'success');
        }).catch(() => {
            // Fallback para navegadores mais antigos
            const textarea = document.getElementById(textareaId);
            if (textarea) {
                textarea.select();
                document.execCommand('copy');
                const message = language === 'EN' ? 'Prompt copied!' : 'Prompt copiado!';
                this.showToast(message, 'success');
            }
        });
    }

    clearAll() {
        if (confirm('Tem certeza que deseja limpar todas as seleções?')) {
            this.userChoices = {};
            this.selectedCategories = {
                ambiente: {},
                seres: {},
                acao: {},
                camera: {},
                fala: {}
            };
            this.currentStep = 1;
            this.updateStepper();
            this.loadStep(1);
            this.updateNavigation();
            
            // Limpar ambas as textareas
            const promptTextPT = document.getElementById('promptTextPT');
            const promptTextEN = document.getElementById('promptTextEN');
            if (promptTextPT) promptTextPT.value = '';
            if (promptTextEN) promptTextEN.value = '';
            
            this.updateStats('', 'PT');
            this.updateStats('', 'EN');
            localStorage.removeItem('promptBuilder_choices');
            localStorage.removeItem('promptBuilder_categories');
            this.showToast('Todas as seleções foram limpas', 'success');
        }
    }

    exportJSON() {
        const data = {
            choices: this.userChoices,
            categories: this.selectedCategories,
            prompt: document.getElementById('promptText').value,
            timestamp: new Date().toISOString()
        };

        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `prompt-builder-${Date.now()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        this.showToast('Arquivo JSON exportado!', 'success');
    }

    showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastIcon = toast.querySelector('.toast-icon');
        const toastMessage = toast.querySelector('.toast-message');

        // Definir ícone baseado no tipo
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle'
        };

        toastIcon.className = `toast-icon ${icons[type] || icons.success}`;
        toastMessage.textContent = message;
        
        toast.className = `toast ${type}`;
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    showHelp() {
        document.getElementById('helpModal').classList.add('show');
    }

    hideHelp() {
        document.getElementById('helpModal').classList.remove('show');
    }

    saveToStorage() {
        localStorage.setItem('promptBuilder_choices', JSON.stringify(this.userChoices));
        localStorage.setItem('promptBuilder_categories', JSON.stringify(this.selectedCategories));
        localStorage.setItem('promptBuilder_step', this.currentStep.toString());
    }

    loadFromStorage() {
        const savedChoices = localStorage.getItem('promptBuilder_choices');
        const savedCategories = localStorage.getItem('promptBuilder_categories');
        const savedStep = localStorage.getItem('promptBuilder_step');

        if (savedChoices) {
            this.userChoices = JSON.parse(savedChoices);
        }

        if (savedCategories) {
            this.selectedCategories = JSON.parse(savedCategories);
        }

        if (savedStep) {
            this.currentStep = parseInt(savedStep);
            this.updateStepper();
            this.loadStep(this.currentStep);
            this.updateNavigation();
        }
    }
}

// Variável global para acessar o prompt builder
let promptBuilder;

// Teste simples primeiro
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    
    // Teste se o elemento existe
    const stepContent = document.getElementById('stepContent');
    console.log('stepContent found:', stepContent);
    
    if (stepContent) {
        // Inserir conteúdo de teste diretamente
        stepContent.innerHTML = `
            <h2 class="step-title">
                <i class="fas fa-image"></i>
                Tipo de Conteúdo - TESTE
            </h2>
            <p class="step-description">Escolha o tipo de conteúdo que deseja gerar</p>
            <div class="options-grid">
                <div class="option-card" style="background: lightblue; padding: 20px; margin: 10px; cursor: pointer;">
                    <div class="option-icon">
                        <i class="fas fa-image"></i>
                    </div>
                    <div class="option-title">Imagem TESTE</div>
                    <div class="option-description">Gerar imagem estática</div>
                </div>
                <div class="option-card" style="background: lightgreen; padding: 20px; margin: 10px; cursor: pointer;">
                    <div class="option-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <div class="option-title">Vídeo TESTE</div>
                    <div class="option-description">Gerar vídeo/animação</div>
                </div>
            </div>
        `;
        console.log('Test content inserted');
    } else {
        console.error('stepContent element not found!');
    }
    
    // Comentar a inicialização do PromptBuilder temporariamente
    /*
    try {
        promptBuilder = new PromptBuilder();
    } catch (error) {
        console.error('Error creating PromptBuilder:', error);
    }
    */
});

// Prevenir perda de dados ao sair da página
window.addEventListener('beforeunload', function(e) {
    const promptText = document.getElementById('promptText').value;
    if (promptText.trim()) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// Atalhos de teclado
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + C para copiar prompt
    if ((e.ctrlKey || e.metaKey) && e.key === 'c' && e.target.id === 'promptText') {
        e.preventDefault();
        document.getElementById('copyBtn').click();
    }
    
    // ESC para fechar modal
    if (e.key === 'Escape') {
        const helpModal = document.getElementById('helpModal');
        if (helpModal.classList.contains('show')) {
            helpModal.classList.remove('show');
        }
    }
});