// Prompt Builder IA - JavaScript
class PromptBuilder {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 10;
        this.userChoices = {};
        this.promptTemplate = {
            image: `[SUBJECT], [STYLE] style, [LIGHTING] lighting, [CAMERA] composition, [ENVIRONMENT] setting, [CHARACTERS] details, [QUALITY] quality`,
            video: `[SUBJECT], [STYLE] style, [LIGHTING] lighting, [CAMERA] shot, [ENVIRONMENT] environment, [CHARACTERS] featuring, [DURATION] duration, [QUALITY] quality`
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
        document.getElementById('prevBtn').addEventListener('click', () => this.previousStep());
        document.getElementById('nextBtn').addEventListener('click', () => this.nextStep());
        
        // Stepper clickável
        document.querySelectorAll('.step').forEach(step => {
            step.addEventListener('click', (e) => {
                const stepNumber = parseInt(e.currentTarget.dataset.step);
                this.goToStep(stepNumber);
            });
        });

        // Ações do prompt
        document.getElementById('copyBtn').addEventListener('click', () => this.copyPrompt());
        document.getElementById('clearBtn').addEventListener('click', () => this.clearAll());
        document.getElementById('exportBtn').addEventListener('click', () => this.exportJSON());

        // Modal de ajuda
        document.getElementById('helpBtn').addEventListener('click', () => this.showHelp());
        document.getElementById('closeHelp').addEventListener('click', () => this.hideHelp());
        
        // Fechar modal clicando no overlay
        document.getElementById('helpModal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget || e.target.classList.contains('modal-overlay')) {
                this.hideHelp();
            }
        });

        // Salvar automaticamente
        setInterval(() => this.saveToStorage(), 5000);
    }

    loadStep(step) {
        const stepContent = document.getElementById('stepContent');
        const stepData = this.getStepData(step);
        
        stepContent.innerHTML = `
            <h2 class="step-title">
                <i class="${stepData.icon}"></i>
                ${stepData.title}
            </h2>
            <p class="step-description">${stepData.description}</p>
            <div class="options-grid" id="optionsGrid">
                ${stepData.options.map(option => this.createOptionCard(option)).join('')}
            </div>
            ${stepData.customField ? this.createCustomField(stepData.customField) : ''}
        `;

        // Bind events para as opções
        this.bindOptionEvents();
        
        // Restaurar seleções
        this.restoreSelections(step);
        
        // Atualizar prompt
        this.updatePrompt();
    }

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
                title: 'Assunto/Cena Principal',
                description: 'Defina o tema central da sua criação',
                icon: 'fas fa-eye',
                options: [
                    { id: 'person', title: 'Pessoa', description: 'Retrato ou figura humana', icon: 'fas fa-user' },
                    { id: 'animal', title: 'Animal', description: 'Criaturas e vida selvagem', icon: 'fas fa-paw' },
                    { id: 'landscape', title: 'Paisagem', description: 'Cenários naturais', icon: 'fas fa-mountain' },
                    { id: 'object', title: 'Objeto', description: 'Produtos ou itens', icon: 'fas fa-cube' },
                    { id: 'architecture', title: 'Arquitetura', description: 'Edifícios e estruturas', icon: 'fas fa-building' },
                    { id: 'abstract', title: 'Abstrato', description: 'Arte conceitual', icon: 'fas fa-palette' }
                ],
                customField: { label: 'Descreva sua cena personalizada', type: 'textarea', placeholder: 'Ex: Uma sereia nadando em águas cristalinas...' }
            },
            3: {
                title: 'Estilo Visual',
                description: 'Escolha o estilo artístico desejado',
                icon: 'fas fa-paint-brush',
                options: [
                    { id: 'realistic', title: 'Realista', description: 'Fotorrealismo detalhado', icon: 'fas fa-camera' },
                    { id: 'cinematic', title: 'Cinematográfico', description: 'Estilo de filme', icon: 'fas fa-film' },
                    { id: 'illustration', title: 'Ilustração', description: 'Arte digital ilustrativa', icon: 'fas fa-pencil-alt' },
                    { id: 'oil_painting', title: 'Pintura a Óleo', description: 'Estilo clássico de pintura', icon: 'fas fa-palette' },
                    { id: 'watercolor', title: 'Aquarela', description: 'Estilo aquarela suave', icon: 'fas fa-tint' },
                    { id: 'cartoon', title: 'Cartoon', description: 'Estilo animado/cartoon', icon: 'fas fa-smile' },
                    { id: 'anime', title: 'Anime', description: 'Estilo anime japonês', icon: 'fas fa-star' },
                    { id: 'pixar', title: 'Pixar-like', description: 'Estilo 3D Pixar', icon: 'fas fa-cube' }
                ],
                customField: { label: 'Referência de estilo personalizada', type: 'input', placeholder: 'Ex: No estilo de Van Gogh, Art Nouveau...' }
            },
            4: {
                title: 'Iluminação',
                description: 'Configure a iluminação da cena',
                icon: 'fas fa-lightbulb',
                options: [
                    { id: 'natural', title: 'Natural', description: 'Luz solar natural', icon: 'fas fa-sun' },
                    { id: 'golden_hour', title: 'Hora Dourada', description: 'Luz quente do entardecer', icon: 'fas fa-sun' },
                    { id: 'studio', title: 'Estúdio', description: 'Iluminação controlada', icon: 'fas fa-lightbulb' },
                    { id: 'dramatic', title: 'Dramática', description: 'Contraste forte', icon: 'fas fa-adjust' },
                    { id: 'soft', title: 'Suave', description: 'Luz difusa e suave', icon: 'fas fa-cloud' },
                    { id: 'neon', title: 'Neon', description: 'Luzes neon coloridas', icon: 'fas fa-lightbulb' },
                    { id: 'night', title: 'Noturna', description: 'Cena noturna', icon: 'fas fa-moon' },
                    { id: 'backlight', title: 'Contraluz', description: 'Iluminação traseira', icon: 'fas fa-circle' }
                ],
                customField: { label: 'Iluminação personalizada', type: 'input', placeholder: 'Ex: Luz de velas, holofotes azuis...' }
            },
            5: {
                title: 'Câmera e Composição',
                description: 'Defina o enquadramento e perspectiva',
                icon: 'fas fa-camera',
                options: [
                    { id: 'close_up', title: 'Close-up', description: 'Plano fechado/próximo', icon: 'fas fa-search-plus' },
                    { id: 'medium_shot', title: 'Plano Médio', description: 'Enquadramento médio', icon: 'fas fa-expand-arrows-alt' },
                    { id: 'wide_shot', title: 'Plano Geral', description: 'Visão ampla da cena', icon: 'fas fa-expand' },
                    { id: 'low_angle', title: 'Ângulo Baixo', description: 'Câmera de baixo para cima', icon: 'fas fa-arrow-up' },
                    { id: 'high_angle', title: 'Ângulo Alto', description: 'Câmera de cima para baixo', icon: 'fas fa-arrow-down' },
                    { id: 'birds_eye', title: 'Vista Aérea', description: 'Visão de cima', icon: 'fas fa-plane' },
                    { id: 'portrait', title: 'Retrato', description: 'Orientação vertical', icon: 'fas fa-mobile-alt' },
                    { id: 'landscape', title: 'Paisagem', description: 'Orientação horizontal', icon: 'fas fa-desktop' }
                ],
                customField: { label: 'Configuração de câmera personalizada', type: 'input', placeholder: 'Ex: lente 85mm, DOF raso, foco seletivo...' }
            },
            6: {
                title: 'Personagens e Objetos',
                description: 'Detalhe os elementos principais da cena',
                icon: 'fas fa-users',
                options: [
                    { id: 'single_person', title: 'Uma Pessoa', description: 'Personagem individual', icon: 'fas fa-user' },
                    { id: 'multiple_people', title: 'Múltiplas Pessoas', description: 'Grupo de personagens', icon: 'fas fa-users' },
                    { id: 'children', title: 'Crianças', description: 'Personagens infantis', icon: 'fas fa-child' },
                    { id: 'elderly', title: 'Idosos', description: 'Personagens idosos', icon: 'fas fa-blind' },
                    { id: 'professional', title: 'Profissional', description: 'Vestuário corporativo', icon: 'fas fa-briefcase' },
                    { id: 'casual', title: 'Casual', description: 'Roupas casuais', icon: 'fas fa-tshirt' },
                    { id: 'fantasy', title: 'Fantasia', description: 'Personagens fantásticos', icon: 'fas fa-dragon' },
                    { id: 'historical', title: 'Histórico', description: 'Período histórico', icon: 'fas fa-landmark' }
                ],
                customField: { label: 'Descrição detalhada dos personagens', type: 'textarea', placeholder: 'Ex: mulher jovem, cabelos castanhos, sorrindo, vestido azul...' }
            },
            7: {
                title: 'Ambiente e Locação',
                description: 'Configure o cenário e atmosfera',
                icon: 'fas fa-globe',
                options: [
                    { id: 'indoor', title: 'Interior', description: 'Ambiente fechado', icon: 'fas fa-home' },
                    { id: 'outdoor', title: 'Exterior', description: 'Ambiente aberto', icon: 'fas fa-tree' },
                    { id: 'urban', title: 'Urbano', description: 'Cidade/ambiente urbano', icon: 'fas fa-city' },
                    { id: 'nature', title: 'Natureza', description: 'Ambiente natural', icon: 'fas fa-leaf' },
                    { id: 'beach', title: 'Praia', description: 'Cenário praiano', icon: 'fas fa-umbrella-beach' },
                    { id: 'forest', title: 'Floresta', description: 'Ambiente florestal', icon: 'fas fa-tree' },
                    { id: 'desert', title: 'Deserto', description: 'Ambiente árido', icon: 'fas fa-sun' },
                    { id: 'space', title: 'Espacial', description: 'Ambiente espacial/sci-fi', icon: 'fas fa-rocket' }
                ],
                customField: { label: 'Descrição do ambiente', type: 'textarea', placeholder: 'Ex: laboratório futurista, biblioteca antiga, café parisiense...' }
            },
            8: {
                title: 'Qualidade e Formato',
                description: 'Defina a qualidade e proporções',
                icon: 'fas fa-cog',
                options: [
                    { id: 'square', title: '1:1 (Quadrado)', description: 'Formato quadrado', icon: 'fas fa-square' },
                    { id: 'portrait', title: '3:4 (Retrato)', description: 'Vertical para redes sociais', icon: 'fas fa-mobile-alt' },
                    { id: 'landscape', title: '4:3 (Paisagem)', description: 'Horizontal tradicional', icon: 'fas fa-desktop' },
                    { id: 'widescreen', title: '16:9 (Widescreen)', description: 'Formato cinema', icon: 'fas fa-tv' },
                    { id: 'instagram', title: '9:16 (Stories)', description: 'Vertical para stories', icon: 'fas fa-mobile' },
                    { id: 'hd', title: 'HD (1920x1080)', description: 'Alta definição', icon: 'fas fa-video' },
                    { id: '4k', title: '4K (3840x2160)', description: 'Ultra alta definição', icon: 'fas fa-video' },
                    { id: '8k', title: '8K (7680x4320)', description: 'Máxima qualidade', icon: 'fas fa-video' }
                ],
                customField: { label: 'Configurações personalizadas', type: 'input', placeholder: 'Ex: 300 DPI, formato PNG, duração 30s...' }
            },
            9: {
                title: 'Parâmetros Avançados',
                description: 'Configure parâmetros técnicos do modelo de IA',
                icon: 'fas fa-sliders-h',
                options: [
                    { id: 'cfg_low', title: 'CFG Baixo (5-7)', description: 'Mais criativo, menos aderente', icon: 'fas fa-unlock' },
                    { id: 'cfg_medium', title: 'CFG Médio (8-12)', description: 'Equilibrado (recomendado)', icon: 'fas fa-balance-scale' },
                    { id: 'cfg_high', title: 'CFG Alto (13-20)', description: 'Mais aderente ao prompt', icon: 'fas fa-lock' },
                    { id: 'steps_low', title: 'Steps Baixo (20-30)', description: 'Rápido, menos detalhado', icon: 'fas fa-tachometer-alt' },
                    { id: 'steps_medium', title: 'Steps Médio (30-50)', description: 'Equilibrado', icon: 'fas fa-equals' },
                    { id: 'steps_high', title: 'Steps Alto (50-100)', description: 'Lento, mais detalhado', icon: 'fas fa-search' },
                    { id: 'seed_random', title: 'Seed Aleatória', description: 'Resultado sempre diferente', icon: 'fas fa-random' },
                    { id: 'seed_fixed', title: 'Seed Fixa', description: 'Resultado reproduzível', icon: 'fas fa-anchor' }
                ],
                customField: { label: 'Prompt negativo (o que NÃO incluir)', type: 'textarea', placeholder: 'Ex: low quality, blurry, distorted, ugly...' }
            },
            10: {
                title: 'Revisão Final',
                description: 'Revise e ajuste seu prompt antes de finalizar',
                icon: 'fas fa-check-circle',
                options: [
                    { id: 'review_complete', title: 'Prompt Completo', description: 'Todas as etapas preenchidas', icon: 'fas fa-check-circle' },
                    { id: 'review_partial', title: 'Ajustes Necessários', description: 'Algumas etapas precisam de revisão', icon: 'fas fa-exclamation-triangle' }
                ]
            }
        };

        return steps[step] || steps[1];
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
        // Cards de opção
        document.querySelectorAll('.option-card').forEach(card => {
            card.addEventListener('click', (e) => {
                const option = e.currentTarget.dataset.option;
                this.toggleOption(option);
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
        const contentType = this.userChoices[1]?.[0] || 'image';
        let prompt = '';

        // Construir prompt baseado nas escolhas
        const parts = [];

        // Assunto (etapa 2)
        if (this.userChoices[2]) {
            const subjects = this.userChoices[2].map(s => this.getOptionLabel(2, s)).join(', ');
            parts.push(subjects);
        }
        if (this.userChoices['2_custom']) {
            parts.push(this.userChoices['2_custom']);
        }

        // Estilo (etapa 3)
        if (this.userChoices[3]) {
            const styles = this.userChoices[3].map(s => this.getOptionLabel(3, s)).join(', ');
            parts.push(`${styles} style`);
        }
        if (this.userChoices['3_custom']) {
            parts.push(this.userChoices['3_custom']);
        }

        // Iluminação (etapa 4)
        if (this.userChoices[4]) {
            const lighting = this.userChoices[4].map(l => this.getOptionLabel(4, l)).join(', ');
            parts.push(`${lighting} lighting`);
        }
        if (this.userChoices['4_custom']) {
            parts.push(this.userChoices['4_custom']);
        }

        // Câmera (etapa 5)
        if (this.userChoices[5]) {
            const camera = this.userChoices[5].map(c => this.getOptionLabel(5, c)).join(', ');
            parts.push(`${camera} shot`);
        }
        if (this.userChoices['5_custom']) {
            parts.push(this.userChoices['5_custom']);
        }

        // Personagens (etapa 6)
        if (this.userChoices[6]) {
            const characters = this.userChoices[6].map(c => this.getOptionLabel(6, c)).join(', ');
            parts.push(`featuring ${characters}`);
        }
        if (this.userChoices['6_custom']) {
            parts.push(this.userChoices['6_custom']);
        }

        // Ambiente (etapa 7)
        if (this.userChoices[7]) {
            const environment = this.userChoices[7].map(e => this.getOptionLabel(7, e)).join(', ');
            parts.push(`${environment} setting`);
        }
        if (this.userChoices['7_custom']) {
            parts.push(this.userChoices['7_custom']);
        }

        // Qualidade (etapa 8)
        if (this.userChoices[8]) {
            const quality = this.userChoices[8].map(q => this.getOptionLabel(8, q)).join(', ');
            parts.push(`${quality}`);
        }
        if (this.userChoices['8_custom']) {
            parts.push(this.userChoices['8_custom']);
        }

        // Parâmetros (etapa 9)
        if (this.userChoices[9]) {
            const params = this.userChoices[9].map(p => this.getOptionLabel(9, p)).join(', ');
            parts.push(`${params}`);
        }

        prompt = parts.filter(p => p.trim()).join(', ');

        // Prompt negativo
        if (this.userChoices['9_custom']) {
            prompt += `\n\nNegative prompt: ${this.userChoices['9_custom']}`;
        }

        // Atualizar textarea
        document.getElementById('promptText').value = prompt;

        // Atualizar estatísticas
        this.updateStats(prompt);
    }

    getOptionLabel(step, optionId) {
        const stepData = this.getStepData(step);
        const option = stepData.options.find(o => o.id === optionId);
        return option ? option.title : optionId;
    }

    updateStats(prompt) {
        const chars = prompt.length;
        const words = prompt.trim() ? prompt.trim().split(/\s+/).length : 0;
        const tokens = Math.ceil(words * 1.3); // Estimativa aproximada

        document.getElementById('charCount').textContent = `${chars} caracteres`;
        document.getElementById('wordCount').textContent = `${words} palavras`;
        document.getElementById('tokenEstimate').textContent = `~${tokens} tokens`;
    }

    restoreSelections(step) {
        if (this.userChoices[step]) {
            this.userChoices[step].forEach(option => {
                const card = document.querySelector(`[data-option="${option}"]`);
                if (card) card.classList.add('selected');
            });
        }

        const customField = document.getElementById('customField');
        if (customField && this.userChoices[`${step}_custom`]) {
            customField.value = this.userChoices[`${step}_custom`];
        }
    }

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

        // Marcar etapa anterior como completa
        if (step > this.currentStep) {
            document.querySelector(`[data-step="${this.currentStep}"]`).classList.add('completed');
        }

        this.currentStep = step;
        this.updateStepper();
        this.loadStep(step);
        this.updateNavigation();
    }

    updateStepper() {
        document.querySelectorAll('.step').forEach(step => {
            const stepNum = parseInt(step.dataset.step);
            step.classList.remove('active');
            
            if (stepNum === this.currentStep) {
                step.classList.add('active');
            } else if (stepNum < this.currentStep) {
                step.classList.add('completed');
            }
        });
    }

    updateNavigation() {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        prevBtn.disabled = this.currentStep === 1;
        
        if (this.currentStep === this.totalSteps) {
            nextBtn.textContent = 'Finalizar';
            nextBtn.innerHTML = '<i class="fas fa-check"></i> Finalizar';
        } else {
            nextBtn.innerHTML = 'Próximo <i class="fas fa-chevron-right"></i>';
        }
    }

    copyPrompt() {
        const promptText = document.getElementById('promptText').value;
        if (!promptText.trim()) {
            this.showToast('Nenhum prompt para copiar', 'warning');
            return;
        }

        navigator.clipboard.writeText(promptText).then(() => {
            this.showToast('Prompt copiado com sucesso!', 'success');
        }).catch(() => {
            // Fallback para navegadores mais antigos
            const textarea = document.getElementById('promptText');
            textarea.select();
            document.execCommand('copy');
            this.showToast('Prompt copiado!', 'success');
        });
    }

    clearAll() {
        if (confirm('Tem certeza que deseja limpar todas as seleções?')) {
            this.userChoices = {};
            this.currentStep = 1;
            this.updateStepper();
            this.loadStep(1);
            this.updateNavigation();
            document.getElementById('promptText').value = '';
            this.updateStats('');
            localStorage.removeItem('promptBuilder_choices');
            this.showToast('Todas as seleções foram limpas', 'success');
        }
    }

    exportJSON() {
        const data = {
            choices: this.userChoices,
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
        localStorage.setItem('promptBuilder_step', this.currentStep.toString());
    }

    loadFromStorage() {
        const savedChoices = localStorage.getItem('promptBuilder_choices');
        const savedStep = localStorage.getItem('promptBuilder_step');

        if (savedChoices) {
            this.userChoices = JSON.parse(savedChoices);
        }

        if (savedStep) {
            this.currentStep = parseInt(savedStep);
            this.updateStepper();
            this.loadStep(this.currentStep);
            this.updateNavigation();
        }
    }
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    new PromptBuilder();
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