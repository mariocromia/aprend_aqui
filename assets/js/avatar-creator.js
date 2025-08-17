/**
 * SISTEMA MODERNO DE CRIAÇÃO DE AVATARES
 * JavaScript para funcionalidade dinâmica e interativa
 */

class AvatarCreator {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 3;
        this.selectedType = null;
        this.formData = {};
        this.tags = [];
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateProgress();
        this.loadFieldTemplates();
    }

    bindEvents() {
        // Navegação entre etapas
        const nextBtn = document.getElementById('next-step');
        const prevBtn = document.getElementById('prev-step');
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.nextStep());
        }
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.prevStep());
        }
        
        // Seleção de tipo de ser - usando múltiplas abordagens para garantir funcionamento
        this.setupBeingTypeSelection();

        // Input de tags
        const tagsInput = document.getElementById('tags-input');
        if (tagsInput) {
            tagsInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.addTag(e.target.value.trim());
                    e.target.value = '';
                }
            });
        }

        // Monitoramento de mudanças no formulário
        document.addEventListener('input', (e) => this.handleFormChange(e));
        document.addEventListener('change', (e) => this.handleFormChange(e));

        // Ações do header
        const resetBtn = document.getElementById('reset-form');
        const saveDraftBtn = document.getElementById('save-draft');
        const createAvatarBtn = document.getElementById('create-avatar');
        
        if (resetBtn) resetBtn.addEventListener('click', () => this.resetForm());
        if (saveDraftBtn) saveDraftBtn.addEventListener('click', () => this.saveDraft());
        if (createAvatarBtn) createAvatarBtn.addEventListener('click', () => this.createAvatar());

        // Preview actions
        const addToPromptBtn = document.getElementById('add-to-prompt');
        const copyPromptBtn = document.getElementById('copy-prompt');
        const refreshPreviewBtn = document.getElementById('refresh-preview');
        
        if (addToPromptBtn) addToPromptBtn.addEventListener('click', () => this.addToCurrentPrompt());
        if (copyPromptBtn) copyPromptBtn.addEventListener('click', () => this.copyPrompt());
        if (refreshPreviewBtn) refreshPreviewBtn.addEventListener('click', () => this.updatePreview());

        // Contador de caracteres
        const descInput = document.getElementById('avatar-description');
        if (descInput) {
            descInput.addEventListener('input', (e) => {
                this.updateCharCounter(e.target, 'desc-counter', 500);
            });
        }

        // Indicadores de etapa
        document.querySelectorAll('.step-dot').forEach(dot => {
            dot.addEventListener('click', (e) => {
                const step = parseInt(e.target.dataset.step);
                if (step <= this.currentStep || this.isStepCompleted(step - 1)) {
                    this.goToStep(step);
                }
            });
        });
    }

    // ===== NAVEGAÇÃO ENTRE ETAPAS =====
    nextStep() {
        if (this.validateCurrentStep() && this.currentStep < this.totalSteps) {
            this.currentStep++;
            this.showStep(this.currentStep);
            this.updateProgress();
            this.updateStepIndicators();
        }
    }

    prevStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.showStep(this.currentStep);
            this.updateProgress();
            this.updateStepIndicators();
        }
    }

    goToStep(step) {
        if (step >= 1 && step <= this.totalSteps) {
            this.currentStep = step;
            this.showStep(this.currentStep);
            this.updateProgress();
            this.updateStepIndicators();
        }
    }

    showStep(step) {
        // Esconder todas as etapas
        document.querySelectorAll('.form-step').forEach(stepEl => {
            stepEl.classList.remove('active');
        });

        // Mostrar etapa atual
        const currentStepEl = document.querySelector(`[data-step="${step}"]`);
        if (currentStepEl) {
            currentStepEl.classList.add('active');
        }

        // Atualizar botões de navegação
        document.getElementById('prev-step').disabled = step === 1;
        
        const nextBtn = document.getElementById('next-step');
        if (step === this.totalSteps) {
            nextBtn.innerHTML = '<i class="material-icons">check</i> Finalizar';
        } else {
            nextBtn.innerHTML = 'Próximo <i class="material-icons">arrow_forward</i>';
        }
    }

    updateProgress() {
        const progress = (this.currentStep / this.totalSteps) * 100;
        document.getElementById('form-progress').style.width = `${progress}%`;
        document.getElementById('progress-percentage').textContent = `${Math.round(progress)}%`;
    }

    updateStepIndicators() {
        document.querySelectorAll('.step-dot').forEach((dot, index) => {
            const stepNum = index + 1;
            dot.classList.remove('active', 'completed');
            
            if (stepNum === this.currentStep) {
                dot.classList.add('active');
            } else if (stepNum < this.currentStep) {
                dot.classList.add('completed');
            }
        });
    }

    // ===== SELEÇÃO DE TIPO DE SER =====
    selectBeingType(card) {
        // Remover seleção anterior
        document.querySelectorAll('.being-type-card').forEach(c => c.classList.remove('selected'));
        
        // Selecionar novo tipo
        card.classList.add('selected');
        this.selectedType = card.dataset.type;
        
        // Atualizar preview
        this.updateTypePreview();
        
        // Gerar campos dinâmicos
        this.generateDynamicFields();
        
        // Permitir avançar
        this.updateProgress();
    }

    updateTypePreview() {
        const placeholder = document.getElementById('avatar-placeholder');
        const badge = document.getElementById('type-badge');
        const previewType = document.getElementById('preview-type');
        
        const typeInfo = this.getTypeInfo(this.selectedType);
        
        // Atualizar placeholder
        placeholder.innerHTML = `
            <i class="material-icons" style="font-size: 4rem; color: ${typeInfo.color};">${typeInfo.icon}</i>
            <span style="color: ${typeInfo.color};">${typeInfo.name}</span>
        `;
        
        // Atualizar badge
        badge.innerHTML = `
            <i class="material-icons">${typeInfo.icon}</i>
            <span>${typeInfo.name}</span>
        `;
        badge.style.background = typeInfo.color;
        
        // Atualizar preview
        previewType.textContent = typeInfo.name;
    }

    getTypeInfo(type) {
        const types = {
            humano: { name: 'Humano', icon: 'person', color: '#3b82f6' },
            animal: { name: 'Animal', icon: 'pets', color: '#10b981' },
            fantastico: { name: 'Fantástico', icon: 'auto_fix_high', color: '#8b5cf6' },
            extraterrestre: { name: 'Extraterrestre', icon: 'rocket_launch', color: '#f59e0b' },
            robotico: { name: 'Robótico', icon: 'smart_toy', color: '#6b7280' },
            hibrido: { name: 'Híbrido', icon: 'merge_type', color: '#ec4899' }
        };
        
        return types[type] || types.humano;
    }

    // ===== GERAÇÃO DE CAMPOS DINÂMICOS =====
    generateDynamicFields() {
        const appearanceContainer = document.getElementById('appearance-fields');
        
        // Limpar container
        appearanceContainer.innerHTML = '';
        
        // Gerar campos baseados no tipo
        switch (this.selectedType) {
            case 'humano':
                this.generateHumanFields();
                break;
            case 'animal':
                this.generateAnimalFields();
                break;
            case 'fantastico':
                this.generateFantasticFields();
                break;
            case 'extraterrestre':
                this.generateAlienFields();
                break;
            case 'robotico':
                this.generateRoboticFields();
                break;
            case 'hibrido':
                this.generateHybridFields();
                break;
        }
        
        // Atualizar categorias
        this.updateCategoryOptions();
    }

    generateHumanFields() {
        const appearance = document.getElementById('appearance-fields');
        
        appearance.innerHTML = `
            <div class="appearance-compact-grid">
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">people</i>
                        Gênero
                    </label>
                    <select name="genero" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="masculino">Masculino</option>
                        <option value="feminino">Feminino</option>
                        <option value="neutro">Neutro</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">cake</i>
                        Idade
                    </label>
                    <select name="idade" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="5-10">5-10 anos</option>
                        <option value="10-15">10-15 anos</option>
                        <option value="15-20">15-20 anos</option>
                        <option value="20-25">20-25 anos</option>
                        <option value="25-30">25-30 anos</option>
                        <option value="30-35">30-35 anos</option>
                        <option value="35-40">35-40 anos</option>
                        <option value="40-45">40-45 anos</option>
                        <option value="45-50">45-50 anos</option>
                        <option value="50-55">50-55 anos</option>
                        <option value="55-60">55-60 anos</option>
                        <option value="60-65">60-65 anos</option>
                        <option value="65-70">65-70 anos</option>
                        <option value="70-75">70-75 anos</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">public</i>
                        Etnia/Origem
                    </label>
                    <select name="etnia" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="caucasiana">Caucasiana</option>
                        <option value="afrodescendente">Afrodescendente</option>
                        <option value="asiatica">Asiática</option>
                        <option value="latina">Latina</option>
                        <option value="indigena">Indígena</option>
                        <option value="mista">Mista</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">fitness_center</i>
                        Tipo Físico
                    </label>
                    <select name="tipo_fisico" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="magro">Magro/Esbelto</option>
                        <option value="atletico">Atlético</option>
                        <option value="medio">Médio/Normal</option>
                        <option value="forte">Forte/Robusto</option>
                        <option value="musculoso">Musculoso</option>
                        <option value="curvilinea">Curvilínea</option>
                        <option value="acima_peso">Acima do peso</option>
                        <option value="plus_size">Plus Size</option>
                        <option value="obeso">Obeso</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">cut</i>
                        Cor do Cabelo
                    </label>
                    <select name="cor_cabelo" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="preto">Preto</option>
                        <option value="castanho_escuro">Castanho Escuro</option>
                        <option value="castanho">Castanho</option>
                        <option value="castanho_claro">Castanho Claro</option>
                        <option value="loiro_escuro">Loiro Escuro</option>
                        <option value="loiro">Loiro</option>
                        <option value="loiro_platinado">Loiro Platinado</option>
                        <option value="ruivo">Ruivo</option>
                        <option value="ruivo_claro">Ruivo Claro</option>
                        <option value="grisalho">Grisalho</option>
                        <option value="branco">Branco</option>
                        <option value="rosa">Rosa</option>
                        <option value="azul">Azul</option>
                        <option value="roxo">Roxo</option>
                        <option value="verde">Verde</option>
                        <option value="colorido">Multicolorido</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">content_cut</i>
                        Tipo de Corte
                    </label>
                    <select name="tipo_corte" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="curto">Curto</option>
                        <option value="medio">Médio</option>
                        <option value="longo">Longo</option>
                        <option value="buzz_cut">Buzz Cut</option>
                        <option value="undercut">Undercut</option>
                        <option value="fade">Fade</option>
                        <option value="pompadour">Pompadour</option>
                        <option value="quiff">Quiff</option>
                        <option value="fringe">Franja</option>
                        <option value="bob">Bob</option>
                        <option value="pixie">Pixie</option>
                        <option value="layers">Em Camadas</option>
                        <option value="waves">Ondulado</option>
                        <option value="curly">Cacheado</option>
                        <option value="straight">Liso</option>
                        <option value="braids">Tranças</option>
                        <option value="dreadlocks">Dreadlocks</option>
                        <option value="afro">Afro</option>
                        <option value="mohawk">Moicano</option>
                        <option value="mullet">Mullet</option>
                        <option value="careca">Careca</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">visibility</i>
                        Cor dos Olhos
                    </label>
                    <select name="cor_olhos" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="castanho_escuro">Castanho Escuro</option>
                        <option value="castanho">Castanho</option>
                        <option value="castanho_claro">Castanho Claro</option>
                        <option value="azul_escuro">Azul Escuro</option>
                        <option value="azul">Azul</option>
                        <option value="azul_claro">Azul Claro</option>
                        <option value="verde_escuro">Verde Escuro</option>
                        <option value="verde">Verde</option>
                        <option value="verde_claro">Verde Claro</option>
                        <option value="cinza">Cinza</option>
                        <option value="preto">Preto</option>
                        <option value="amendoado">Amendoado</option>
                        <option value="mel">Mel</option>
                        <option value="avela">Avelã</option>
                        <option value="heterocromia">Heterocromia</option>
                        <option value="violeta">Violeta</option>
                        <option value="dourado">Dourado</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">palette</i>
                        Tom de Pele
                    </label>
                    <select name="cor_pele" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="muito_clara">Muito Clara</option>
                        <option value="clara">Clara</option>
                        <option value="media_clara">Média Clara</option>
                        <option value="media">Média</option>
                        <option value="morena_clara">Morena Clara</option>
                        <option value="morena">Morena</option>
                        <option value="morena_escura">Morena Escura</option>
                        <option value="escura">Escura</option>
                        <option value="muito_escura">Muito Escura</option>
                        <option value="bronzeada">Bronzeada</option>
                        <option value="albina">Albina</option>
                    </select>
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">
                        <i class="material-icons">checkroom</i>
                        Vestuário e Estilo
                    </label>
                    <textarea name="vestuario" class="form-textarea" rows="3"
                              placeholder="Descreva o estilo de roupas, acessórios, cores predominantes..."></textarea>
                </div>
            </div>
        `;
    }

    generateAnimalFields() {
        const appearance = document.getElementById('appearance-fields');
        
        appearance.innerHTML = `
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">pets</i>
                        Espécie
                    </label>
                    <input type="text" name="especie" class="form-input" 
                           placeholder="Ex: Lobo, Águia, Gato doméstico">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">straighten</i>
                        Tamanho
                    </label>
                    <select name="tamanho" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="muito_pequeno">Muito Pequeno</option>
                        <option value="pequeno">Pequeno</option>
                        <option value="medio">Médio</option>
                        <option value="grande">Grande</option>
                        <option value="muito_grande">Muito Grande</option>
                        <option value="gigantesco">Gigantesco</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">texture</i>
                        Pelagem/Pele
                    </label>
                    <input type="text" name="pelagem" class="form-input" 
                           placeholder="Ex: Pelo dourado e macio, Escamas verdes">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">palette</i>
                        Cores Predominantes
                    </label>
                    <input type="text" name="cores" class="form-input" 
                           placeholder="Ex: Marrom e branco, Preto com listras">
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">
                        <i class="material-icons">visibility</i>
                        Características Distintivas
                    </label>
                    <textarea name="caracteristicas" class="form-textarea" rows="2"
                              placeholder="Marcas especiais, cicatrizes, padrões únicos..."></textarea>
                </div>
            </div>
        `;
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">nature</i>
                        Habitat Natural
                    </label>
                    <input type="text" name="habitat" class="form-input" 
                           placeholder="Ex: Floresta tropical, Savana, Oceano">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">restaurant</i>
                        Dieta
                    </label>
                    <select name="dieta" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="carnivoro">Carnívoro</option>
                        <option value="herbivoro">Herbívoro</option>
                        <option value="onivoro">Onívoro</option>
                        <option value="insectivoro">Insetívoro</option>
                        <option value="nectarivoro">Nectarívoro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">groups</i>
                        Vida Social
                    </label>
                    <select name="vida_social" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="solitario">Solitário</option>
                        <option value="par">Em par</option>
                        <option value="grupo_pequeno">Grupo pequeno</option>
                        <option value="matilha">Matilha/Bando</option>
                        <option value="colonia">Colônia</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">schedule</i>
                        Atividade
                    </label>
                    <select name="atividade" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="diurno">Diurno</option>
                        <option value="noturno">Noturno</option>
                        <option value="crepuscular">Crepuscular</option>
                        <option value="cathemeral">Cathemeral (24h)</option>
                    </select>
                </div>
            </div>
        `;
    }

    generateFantasticFields() {
        const appearance = document.getElementById('appearance-fields');
        
        appearance.innerHTML = `
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">auto_fix_high</i>
                        Tipo de Ser
                    </label>
                    <input type="text" name="tipo_ser" class="form-input" 
                           placeholder="Ex: Elfo, Dragão, Fada, Vampiro">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">straighten</i>
                        Tamanho/Forma
                    </label>
                    <input type="text" name="tamanho_forma" class="form-input" 
                           placeholder="Ex: Humanoide alto, Quadrúpede gigante">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">texture</i>
                        Aparência Física
                    </label>
                    <input type="text" name="aparencia_fisica" class="form-input" 
                           placeholder="Ex: Pele azulada, Escamas douradas">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">star</i>
                        Características Mágicas
                    </label>
                    <input type="text" name="caracteristicas_magicas" class="form-input" 
                           placeholder="Ex: Olhos que brilham, Aura luminosa">
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">
                        <i class="material-icons">checkroom</i>
                        Vestimentas/Adornos
                    </label>
                    <textarea name="vestimentas" class="form-textarea" rows="2"
                              placeholder="Roupas mágicas, armaduras, joias encantadas..."></textarea>
                </div>
            </div>
        `;
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">auto_awesome</i>
                        Poderes Principais
                    </label>
                    <input type="text" name="poderes" class="form-input" 
                           placeholder="Ex: Controle do fogo, Telepatia, Voo">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">school</i>
                        Escola de Magia
                    </label>
                    <input type="text" name="escola_magia" class="form-input" 
                           placeholder="Ex: Elementalismo, Necromancia, Ilusão">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">gavel</i>
                        Fraquezas/Limitações
                    </label>
                    <input type="text" name="fraquezas" class="form-input" 
                           placeholder="Ex: Água benta, Ferro frio, Luz solar">
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">
                        <i class="material-icons">auto_stories</i>
                        Lore/Mitologia
                    </label>
                    <textarea name="lore" class="form-textarea" rows="3"
                              placeholder="História mítica, lendas, origem dos poderes..."></textarea>
                </div>
            </div>
        `;
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">terrain</i>
                        Reino/Dimensão
                    </label>
                    <input type="text" name="reino" class="form-input" 
                           placeholder="Ex: Reino Élfico, Submundo, Plano Astral">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">account_balance</i>
                        Posição Social
                    </label>
                    <input type="text" name="posicao_social" class="form-input" 
                           placeholder="Ex: Nobre, Pária, Guardião">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">groups</i>
                        Alianças/Inimigos
                    </label>
                    <input type="text" name="aliancas" class="form-input" 
                           placeholder="Ex: Conselho dos Magos, Vampiros">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">flag</i>
                        Missão/Propósito
                    </label>
                    <input type="text" name="proposito" class="form-input" 
                           placeholder="Ex: Proteger a floresta, Buscar vingança">
                </div>
            </div>
        `;
    }

    generateAlienFields() {
        const appearance = document.getElementById('appearance-fields');
        
        appearance.innerHTML = `
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">science</i>
                        Espécie Alienígena
                    </label>
                    <input type="text" name="especie_alien" class="form-input" 
                           placeholder="Ex: Greys, Reptiliano, Cristalino">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">architecture</i>
                        Estrutura Corporal
                    </label>
                    <input type="text" name="estrutura_corporal" class="form-input" 
                           placeholder="Ex: Humanoide, Insectoide, Gasoso">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">palette</i>
                        Coloração/Textura
                    </label>
                    <input type="text" name="coloracao" class="form-input" 
                           placeholder="Ex: Pele azul metálica, Translúcido">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">visibility</i>
                        Órgãos Sensoriais
                    </label>
                    <input type="text" name="orgaos_sensoriais" class="form-input" 
                           placeholder="Ex: Três olhos, Antenas, Echolocalização">
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">
                        <i class="material-icons">biotech</i>
                        Adaptações Biológicas
                    </label>
                    <textarea name="adaptacoes" class="form-textarea" rows="2"
                              placeholder="Resistências, habilidades especiais do organismo..."></textarea>
                </div>
            </div>
        `;
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">psychology</i>
                        Capacidades Mentais
                    </label>
                    <input type="text" name="capacidades_mentais" class="form-input" 
                           placeholder="Ex: Telepatia, Super inteligência">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">precision_manufacturing</i>
                        Tecnologia Integrada
                    </label>
                    <input type="text" name="tecnologia" class="form-input" 
                           placeholder="Ex: Implantes neurais, Nano-bots">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">translate</i>
                        Comunicação
                    </label>
                    <input type="text" name="comunicacao" class="form-input" 
                           placeholder="Ex: Frequências, Cores, Feromônios">
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">
                        <i class="material-icons">science</i>
                        Habilidades Xenobiológicas
                    </label>
                    <textarea name="habilidades_xeno" class="form-textarea" rows="2"
                              placeholder="Poderes únicos da espécie, manipulação da matéria..."></textarea>
                </div>
            </div>
        `;
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">public</i>
                        Planeta de Origem
                    </label>
                    <input type="text" name="planeta_origem" class="form-input" 
                           placeholder="Ex: Kepler-442b, Mundo Cristal">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">account_balance</i>
                        Civilização
                    </label>
                    <input type="text" name="civilizacao" class="form-input" 
                           placeholder="Ex: Império Galáctico, Nômades espaciais">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">rocket_launch</i>
                        Missão na Terra
                    </label>
                    <input type="text" name="missao_terra" class="form-input" 
                           placeholder="Ex: Exploração, Diplomacia, Invasão">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">timeline</i>
                        Era Tecnológica
                    </label>
                    <select name="era_tecnologica" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="primitiva">Primitiva</option>
                        <option value="medieval">Medieval</option>
                        <option value="industrial">Industrial</option>
                        <option value="moderna">Moderna</option>
                        <option value="futurista">Futurista</option>
                        <option value="pos_singularidade">Pós-Singularidade</option>
                    </select>
                </div>
            </div>
        `;
    }

    generateRoboticFields() {
        const appearance = document.getElementById('appearance-fields');
        
        appearance.innerHTML = `
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">smart_toy</i>
                        Tipo de Robô
                    </label>
                    <select name="tipo_robo" class="form-select">
                        <option value="">Selecionar tipo</option>
                        <option value="androide">Androide (humanoide)</option>
                        <option value="cyborg">Cyborg (híbrido)</option>
                        <option value="drone">Drone/Bot de tarefa</option>
                        <option value="mech">Mech/Robô gigante</option>
                        <option value="ia_holografica">IA Holográfica</option>
                        <option value="nanobots">Enxame de Nanobots</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">construction</i>
                        Material Principal
                    </label>
                    <input type="text" name="material" class="form-input" 
                           placeholder="Ex: Titânio, Nanocarbono, Liga alienígena">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">palette</i>
                        Esquema de Cores
                    </label>
                    <input type="text" name="esquema_cores" class="form-input" 
                           placeholder="Ex: Azul e prata, Preto fosco">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">lightbulb</i>
                        Iluminação/LEDs
                    </label>
                    <input type="text" name="iluminacao" class="form-input" 
                           placeholder="Ex: Olhos azuis LED, Circuitos luminosos">
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">
                        <i class="material-icons">build</i>
                        Design e Acessórios
                    </label>
                    <textarea name="design_acessorios" class="form-textarea" rows="2"
                              placeholder="Forma, modificações, armas integradas, ferramentas..."></textarea>
                </div>
            </div>
        `;
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">memory</i>
                        Sistema Operacional
                    </label>
                    <input type="text" name="sistema_operacional" class="form-input" 
                           placeholder="Ex: NeuroLinux, QuantumOS, AI-Matrix">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">psychology</i>
                        Tipo de IA
                    </label>
                    <select name="tipo_ia" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="reativa">Reativa</option>
                        <option value="limitada">Memória Limitada</option>
                        <option value="teoria_mente">Teoria da Mente</option>
                        <option value="autoconsciencia">Autoconsciência</option>
                        <option value="superinteligencia">Superinteligência</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">settings</i>
                        Funções Principais
                    </label>
                    <input type="text" name="funcoes" class="form-input" 
                           placeholder="Ex: Combate, Assistência, Exploração">
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">
                        <i class="material-icons">build_circle</i>
                        Capacidades Especiais
                    </label>
                    <textarea name="capacidades_especiais" class="form-textarea" rows="2"
                              placeholder="Hacking, transformação, regeneração, armas especiais..."></textarea>
                </div>
            </div>
        `;
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">factory</i>
                        Fabricante/Criador
                    </label>
                    <input type="text" name="fabricante" class="form-input" 
                           placeholder="Ex: CyberDyne, Dr. Robótica, Auto-evolução">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">assignment</i>
                        Missão/Programação
                    </label>
                    <input type="text" name="missao" class="form-input" 
                           placeholder="Ex: Proteger humanos, Explorar Marte">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">psychology</i>
                        Personalidade Simulada
                    </label>
                    <input type="text" name="personalidade_simulada" class="form-input" 
                           placeholder="Ex: Amigável, Militar, Excêntrico">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">bug_report</i>
                        Falhas/Quirks
                    </label>
                    <input type="text" name="falhas" class="form-input" 
                           placeholder="Ex: Glitch emocional, Obsessão por dados">
                </div>
            </div>
        `;
    }

    generateHybridFields() {
        const appearance = document.getElementById('appearance-fields');
        
        appearance.innerHTML = `
            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">
                        <i class="material-icons">merge_type</i>
                        Combinação de Tipos
                    </label>
                    <div class="checkbox-group">
                        <label class="checkbox-option">
                            <input type="checkbox" name="tipos_combinados" value="humano">
                            <span class="checkbox-mark"></span>
                            <span>Humano</span>
                        </label>
                        <label class="checkbox-option">
                            <input type="checkbox" name="tipos_combinados" value="animal">
                            <span class="checkbox-mark"></span>
                            <span>Animal</span>
                        </label>
                        <label class="checkbox-option">
                            <input type="checkbox" name="tipos_combinados" value="fantastico">
                            <span class="checkbox-mark"></span>
                            <span>Fantástico</span>
                        </label>
                        <label class="checkbox-option">
                            <input type="checkbox" name="tipos_combinados" value="extraterrestre">
                            <span class="checkbox-mark"></span>
                            <span>Extraterrestre</span>
                        </label>
                        <label class="checkbox-option">
                            <input type="checkbox" name="tipos_combinados" value="robotico">
                            <span class="checkbox-mark"></span>
                            <span>Robótico</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">
                        <i class="material-icons">transform</i>
                        Características Físicas Híbridas
                    </label>
                    <textarea name="caracteristicas_hibridas" class="form-textarea" rows="3"
                              placeholder="Descreva como as diferentes naturezas se manifestam fisicamente..."></textarea>
                </div>
            </div>
        `;
            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">
                        <i class="material-icons">auto_awesome</i>
                        Habilidades Combinadas
                    </label>
                    <textarea name="habilidades_combinadas" class="form-textarea" rows="3"
                              placeholder="Poderes únicos resultantes da hibridização..."></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">balance</i>
                        Conflitos Internos
                    </label>
                    <input type="text" name="conflitos" class="form-input" 
                           placeholder="Ex: Instinto vs Razão, Magia vs Tecnologia">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">trending_up</i>
                        Forma Dominante
                    </label>
                    <input type="text" name="forma_dominante" class="form-input" 
                           placeholder="Qual aspecto é mais forte ou visível">
                </div>
            </div>
        `;
            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">
                        <i class="material-icons">history</i>
                        Origem da Hibridização
                    </label>
                    <textarea name="origem_hibridizacao" class="form-textarea" rows="3"
                              placeholder="Como e por que a hibridização ocorreu..."></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">groups</i>
                        Aceitação Social
                    </label>
                    <select name="aceitacao_social" class="form-select">
                        <option value="">Não especificado</option>
                        <option value="aceito">Totalmente aceito</option>
                        <option value="tolerado">Tolerado</option>
                        <option value="suspeito">Visto com suspeita</option>
                        <option value="rejeitado">Rejeitado</option>
                        <option value="perseguido">Perseguido</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="material-icons">explore</i>
                        Busca por Identidade
                    </label>
                    <input type="text" name="busca_identidade" class="form-input" 
                           placeholder="O que busca para se entender melhor">
                </div>
            </div>
        `;
    }

    updateCategoryOptions() {
        const categorySelect = document.getElementById('avatar-category');
        const subcategorySelect = document.getElementById('avatar-subcategory');
        
        if (!categorySelect || !this.selectedType) return;
        
        const categories = this.getCategoriesByType(this.selectedType);
        
        categorySelect.innerHTML = '<option value="">Selecione uma categoria</option>';
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.value;
            option.textContent = category.label;
            categorySelect.appendChild(option);
        });
        
        // Reset subcategory
        subcategorySelect.innerHTML = '<option value="">Selecione uma categoria primeiro</option>';
    }

    getCategoriesByType(type) {
        const categories = {
            humano: [
                { value: 'profissional', label: 'Profissional' },
                { value: 'historico', label: 'Histórico' },
                { value: 'celebridade', label: 'Celebridade' },
                { value: 'ficticio', label: 'Personagem Fictício' },
                { value: 'cotidiano', label: 'Pessoa do Cotidiano' }
            ],
            animal: [
                { value: 'domestico', label: 'Animal Doméstico' },
                { value: 'selvagem', label: 'Animal Selvagem' },
                { value: 'marinho', label: 'Animal Marinho' },
                { value: 'aereo', label: 'Animal Aéreo' },
                { value: 'extinto', label: 'Animal Extinto' },
                { value: 'mitologico', label: 'Animal Mitológico' }
            ],
            fantastico: [
                { value: 'elemental', label: 'Elemental' },
                { value: 'magico', label: 'Ser Mágico' },
                { value: 'mitologico', label: 'Criatura Mitológica' },
                { value: 'demoniaco', label: 'Ser Demoníaco' },
                { value: 'celestial', label: 'Ser Celestial' },
                { value: 'morto_vivo', label: 'Morto-Vivo' }
            ],
            extraterrestre: [
                { value: 'humanoide', label: 'Humanoide Alienígena' },
                { value: 'insectoide', label: 'Insectoide' },
                { value: 'reptiliano', label: 'Reptiliano' },
                { value: 'energia', label: 'Ser de Energia' },
                { value: 'aquatico', label: 'Aquático Alienígena' },
                { value: 'gasoso', label: 'Forma Gasosa' }
            ],
            robotico: [
                { value: 'combate', label: 'Robô de Combate' },
                { value: 'assistencia', label: 'Assistente/Mordomo' },
                { value: 'exploracao', label: 'Exploração' },
                { value: 'industrial', label: 'Industrial/Trabalhador' },
                { value: 'medicina', label: 'Médico/Cirúrgico' },
                { value: 'entretenimento', label: 'Entretenimento' }
            ],
            hibrido: [
                { value: 'bio_tech', label: 'Bio-Tecnológico' },
                { value: 'animal_humano', label: 'Animal-Humano' },
                { value: 'magico_tech', label: 'Mágico-Tecnológico' },
                { value: 'alien_humano', label: 'Alienígena-Humano' },
                { value: 'multi_forma', label: 'Multi-Forma' }
            ]
        };
        
        return categories[type] || [];
    }

    // ===== SISTEMA DE TAGS =====
    addTag(tagText) {
        if (!tagText || this.tags.includes(tagText)) return;
        
        this.tags.push(tagText);
        this.renderTags();
        this.updatePreview();
    }

    removeTag(tagText) {
        this.tags = this.tags.filter(tag => tag !== tagText);
        this.renderTags();
        this.updatePreview();
    }

    renderTags() {
        const container = document.getElementById('tags-container');
        container.innerHTML = '';
        
        this.tags.forEach(tag => {
            const tagElement = document.createElement('div');
            tagElement.className = 'tag-item';
            tagElement.innerHTML = `
                <span>${tag}</span>
                <button type="button" class="tag-remove" onclick="avatarCreator.removeTag('${tag}')">×</button>
            `;
            container.appendChild(tagElement);
        });
    }

    // ===== VALIDAÇÃO E PREVIEW =====
    validateCurrentStep() {
        switch (this.currentStep) {
            case 1:
                return this.selectedType !== null;
            case 2:
                const name = document.getElementById('avatar-name')?.value.trim();
                return name && name.length > 0;
            default:
                return true;
        }
    }

    isStepCompleted(step) {
        switch (step) {
            case 1:
                return this.selectedType !== null;
            case 2:
                const name = document.getElementById('avatar-name')?.value.trim();
                return name && name.length > 0;
            default:
                return false;
        }
    }

    handleFormChange(e) {
        // Atualizar dados do formulário
        this.formData[e.target.name] = e.target.value;
        
        // Atualizar preview
        this.updatePreview();
        
        // Atualizar progresso se necessário
        this.updateProgress();
    }

    updatePreview() {
        // Atualizar nome
        const nameInput = document.getElementById('avatar-name');
        const previewName = document.getElementById('preview-name');
        if (nameInput && previewName) {
            previewName.textContent = nameInput.value || 'Nome do Ser';
        }
        
        // Atualizar descrição
        const descInput = document.getElementById('avatar-description');
        const previewDesc = document.getElementById('preview-description');
        if (descInput && previewDesc) {
            previewDesc.textContent = descInput.value || 'A descrição aparecerá aqui conforme você preenche o formulário...';
        }
        
        // Atualizar categoria
        const categorySelect = document.getElementById('avatar-category');
        const previewCategory = document.getElementById('preview-category');
        if (categorySelect && previewCategory) {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            previewCategory.textContent = selectedOption ? selectedOption.text : '-';
        }
        
        // Gerar prompt
        this.generatePrompt();
    }

    generatePrompt() {
        const promptContainer = document.getElementById('generated-prompt');
        const promptWords = document.getElementById('prompt-words');
        const promptChars = document.getElementById('prompt-chars');
        
        if (!promptContainer) return;
        
        const parts = [];
        
        // Nome
        const name = document.getElementById('avatar-name')?.value.trim();
        if (name) parts.push(name);
        
        // Tipo de ser e características específicas baseadas no tipo
        if (this.selectedType === 'humano') {
            this.addHumanPromptParts(parts);
        } else if (this.selectedType) {
            const typeInfo = this.getTypeInfo(this.selectedType);
            parts.push(typeInfo.name.toLowerCase());
        }
        
        // Descrição
        const description = document.getElementById('avatar-description')?.value.trim();
        if (description) parts.push(description);
        
        // Campos específicos do tipo
        const formInputs = document.querySelectorAll('#appearance-fields input, #appearance-fields select, #appearance-fields textarea');
        formInputs.forEach(input => {
            if (input.value.trim()) {
                parts.push(input.value.trim());
            }
        });
        
        // Tags
        if (this.tags.length > 0) {
            parts.push(...this.tags);
        }
        
        const prompt = parts.join(', ');
        promptContainer.textContent = prompt || 'O prompt será gerado automaticamente baseado nas suas escolhas...';
        
        // Atualizar estatísticas
        const words = prompt.split(/\s+/).filter(word => word.length > 0).length;
        const chars = prompt.length;
        
        if (promptWords) promptWords.textContent = words;
        if (promptChars) promptChars.textContent = chars;
    }

    updateCharCounter(input, counterId, maxLength) {
        const counter = document.getElementById(counterId);
        if (counter) {
            const currentLength = input.value.length;
            counter.textContent = currentLength;
            
            if (currentLength > maxLength) {
                counter.style.color = '#ef4444';
            } else if (currentLength > maxLength * 0.8) {
                counter.style.color = '#f59e0b';
            } else {
                counter.style.color = 'var(--text-muted)';
            }
        }
    }

    addHumanPromptParts(parts) {
        const formData = this.getFormData();
        
        // Gênero e idade
        if (formData.genero && formData.genero !== '') {
            parts.push(formData.genero);
        }
        
        if (formData.idade && formData.idade !== '') {
            parts.push(`${formData.idade} anos`);
        }
        
        // Características físicas
        const physicalParts = [];
        
        if (formData.tipo_fisico && formData.tipo_fisico !== '') {
            physicalParts.push(formData.tipo_fisico);
        }
        
        if (formData.etnia && formData.etnia !== '') {
            physicalParts.push(`etnia ${formData.etnia}`);
        }
        
        if (formData.cor_pele && formData.cor_pele !== '') {
            physicalParts.push(`pele ${formData.cor_pele}`);
        }
        
        // Cabelo
        const hairParts = [];
        if (formData.cor_cabelo && formData.cor_cabelo !== '') {
            hairParts.push(`cabelo ${formData.cor_cabelo}`);
        }
        if (formData.tipo_corte && formData.tipo_corte !== '') {
            hairParts.push(formData.tipo_corte);
        }
        if (hairParts.length > 0) {
            physicalParts.push(hairParts.join(' '));
        }
        
        // Olhos
        if (formData.cor_olhos && formData.cor_olhos !== '') {
            physicalParts.push(`olhos ${formData.cor_olhos}`);
        }
        
        if (physicalParts.length > 0) {
            parts.push(physicalParts.join(', '));
        }
        
        // Vestuário
        if (formData.vestuario && formData.vestuario.trim() !== '') {
            parts.push(`vestindo ${formData.vestuario.trim()}`);
        }
    }

    getFormData() {
        const formData = {};
        const inputs = document.querySelectorAll('#appearance-fields input, #appearance-fields select, #appearance-fields textarea');
        
        inputs.forEach(input => {
            if (input.name && input.value) {
                formData[input.name] = input.value;
            }
        });
        
        // Também pegar campos da etapa 2
        const name = document.getElementById('avatar-name')?.value;
        const description = document.getElementById('avatar-description')?.value;
        
        if (name) formData.nome = name;
        if (description) formData.descricao = description;
        
        return formData;
    }

    // ===== AÇÕES =====
    addToCurrentPrompt() {
        const avatarPrompt = document.getElementById('generated-prompt').textContent;
        
        if (!avatarPrompt || avatarPrompt === 'O prompt será gerado automaticamente baseado nas suas escolhas...') {
            alert('Primeiro configure o avatar para gerar o prompt.');
            return;
        }
        
        // Encontrar o campo de descrição personalizada da aba atual do gerador principal
        const currentTab = document.querySelector('.tab-content.active:not(#tab-avatar)');
        if (currentTab) {
            const customField = currentTab.querySelector('textarea[name*="custom"], textarea[id*="custom"]');
            if (customField) {
                // Adicionar o prompt do avatar ao campo atual
                const currentText = customField.value;
                const separator = currentText ? ', ' : '';
                customField.value = currentText + separator + avatarPrompt;
                
                // Feedback visual
                const button = document.getElementById('add-to-prompt');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="material-icons">check</i> Adicionado!';
                setTimeout(() => {
                    button.innerHTML = originalText;
                }, 2000);
                
                // Fechar o modal se necessário
                if (document.getElementById('save-modal').classList.contains('active')) {
                    document.getElementById('save-modal').classList.remove('active');
                }
            } else {
                alert('Campo de descrição personalizada não encontrado na aba atual.');
            }
        } else {
            alert('Nenhuma aba do gerador principal está ativa. Selecione uma aba primeiro.');
        }
    }

    copyPrompt() {
        const promptText = document.getElementById('generated-prompt').textContent;
        navigator.clipboard.writeText(promptText).then(() => {
            // Mostrar feedback visual
            const button = document.getElementById('copy-prompt');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="material-icons">check</i> Copiado!';
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        });
    }

    resetForm() {
        if (confirm('Tem certeza que deseja limpar todos os dados do formulário?')) {
            // Reset form data
            this.currentStep = 1;
            this.selectedType = null;
            this.formData = {};
            this.tags = [];
            
            // Reset UI
            document.querySelectorAll('.being-type-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            document.querySelectorAll('input, select, textarea').forEach(input => {
                input.value = '';
            });
            
            this.renderTags();
            this.showStep(1);
            this.updateProgress();
            this.updateStepIndicators();
            this.updatePreview();
            
            // Reset preview
            document.getElementById('avatar-placeholder').innerHTML = `
                <i class="material-icons">help_outline</i>
                <span>Selecione um tipo</span>
            `;
            document.getElementById('type-badge').innerHTML = `
                <i class="material-icons">label</i>
                <span>Tipo não selecionado</span>
            `;
        }
    }

    saveDraft() {
        const draftData = {
            currentStep: this.currentStep,
            selectedType: this.selectedType,
            formData: this.formData,
            tags: this.tags,
            timestamp: new Date().toISOString()
        };
        
        localStorage.setItem('avatar_creator_draft', JSON.stringify(draftData));
        
        // Mostrar feedback
        const button = document.getElementById('save-draft');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="material-icons">check</i> Salvo!';
        setTimeout(() => {
            button.innerHTML = originalText;
        }, 2000);
    }

    loadDraft() {
        const draft = localStorage.getItem('avatar_creator_draft');
        if (draft) {
            try {
                const draftData = JSON.parse(draft);
                
                this.currentStep = draftData.currentStep || 1;
                this.selectedType = draftData.selectedType;
                this.formData = draftData.formData || {};
                this.tags = draftData.tags || [];
                
                // Restore UI state
                if (this.selectedType) {
                    const typeCard = document.querySelector(`[data-type="${this.selectedType}"]`);
                    if (typeCard) {
                        typeCard.classList.add('selected');
                        this.updateTypePreview();
                        this.generateDynamicFields();
                    }
                }
                
                // Restore form values
                Object.keys(this.formData).forEach(name => {
                    const input = document.querySelector(`[name="${name}"]`);
                    if (input) {
                        input.value = this.formData[name];
                    }
                });
                
                this.renderTags();
                this.showStep(this.currentStep);
                this.updateProgress();
                this.updateStepIndicators();
                this.updatePreview();
                
            } catch (error) {
                console.error('Erro ao carregar rascunho:', error);
            }
        }
    }

    createAvatar() {
        if (this.currentStep === this.totalSteps && this.validateCurrentStep()) {
            // Mostrar modal de salvamento
            document.getElementById('save-modal').classList.add('active');
        } else {
            alert('Por favor, complete todos os campos obrigatórios antes de criar o avatar.');
        }
    }

    loadFieldTemplates() {
        // Carregar templates de campos se necessário
    }
    
    setupBeingTypeSelection() {
        // Event delegation para melhor performance
        const beingTypesGrid = document.querySelector('.being-types-grid');
        if (beingTypesGrid) {
            beingTypesGrid.addEventListener('click', (e) => {
                const card = e.target.closest('.being-type-card');
                if (card) {
                    this.selectBeingType(card);
                }
            });
        }
        
        // Listeners diretos como fallback
        const beingCards = document.querySelectorAll('.being-type-card');
        beingCards.forEach((card) => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', (e) => {
                this.selectBeingType(e.currentTarget);
            });
        });
    }
    
}

// ===== INICIALIZAÇÃO =====
let avatarCreator;

// Função para inicializar o sistema de avatares quando necessário
function initAvatarSystem() {
    // Verificar se a aba avatar está ativa
    const avatarTab = document.getElementById('tab-avatar');
    if (!avatarTab || !avatarTab.classList.contains('active')) {
        return;
    }
    
    // Inicializar apenas se não existir
    if (!avatarCreator) {
        avatarCreator = new AvatarCreator();
        avatarCreator.loadDraft();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar o sistema de avatar imediatamente se a aba existe
    const avatarTab = document.getElementById('tab-avatar');
    if (avatarTab) {
        // Se a aba avatar está ativa, inicializar imediatamente
        if (avatarTab.classList.contains('active')) {
            initAvatarSystem();
        }
        
        // Listener para quando a aba avatar for ativada
        document.addEventListener('click', (e) => {
            const avatarTabButton = e.target.closest('.tab-button[data-tab="avatar"]') || 
                                   e.target.closest('[onclick*="showTab"][onclick*="avatar"]');
            
            if (avatarTabButton) {
                setTimeout(() => {
                    initAvatarSystem();
                }, 50);
            }
        });
        
        // Observer para mudanças de classe na aba
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const target = mutation.target;
                    if (target.id === 'tab-avatar' && target.classList.contains('active')) {
                        setTimeout(() => {
                            initAvatarSystem();
                        }, 50);
                    }
                }
            });
        });
        
        observer.observe(avatarTab, { attributes: true });
    }
    
    // Modal controls
    document.getElementById('close-modal')?.addEventListener('click', () => {
        document.getElementById('save-modal').classList.remove('active');
    });
    
    // Click outside modal to close
    document.getElementById('save-modal')?.addEventListener('click', (e) => {
        if (e.target.id === 'save-modal') {
            document.getElementById('save-modal').classList.remove('active');
        }
    });
});

