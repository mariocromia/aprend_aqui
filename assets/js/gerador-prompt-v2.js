/**
 * Gerador de Prompts v2.0 - JavaScript Interativo
 */

class PromptGenerator {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 5;
        this.selectedData = {
            environment: null,
            lighting: null,
            character: null,
            settings: {}
        };
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadCategoryStyles();
        this.updatePreview();
    }
    
    bindEvents() {
        // Navegação de etapas
        document.querySelectorAll('.step-item').forEach(step => {
            step.addEventListener('click', (e) => {
                const stepNum = parseInt(e.currentTarget.dataset.step);
                if (stepNum <= this.currentStep + 1) {
                    this.goToStep(stepNum);
                }
            });
        });
        
        // Seleção de ambiente
        document.querySelectorAll('.environment-card').forEach(card => {
            card.addEventListener('click', (e) => {
                this.selectEnvironment(e.currentTarget);
            });
        });
        
        // Seleção de iluminação
        document.querySelectorAll('.lighting-card').forEach(card => {
            card.addEventListener('click', (e) => {
                this.selectLighting(e.currentTarget);
            });
        });
        
        // Seleção de personagem
        document.querySelectorAll('.character-card').forEach(card => {
            card.addEventListener('click', (e) => {
                this.selectCharacter(e.currentTarget);
            });
        });
        
        // Botões de navegação
        document.getElementById('btn-next-1')?.addEventListener('click', () => this.nextStep());
        
        // Preview em tempo real
        document.getElementById('original_prompt')?.addEventListener('input', () => {
            this.updatePreview();
        });
        
        // Prevenir submit acidental
        document.getElementById('promptForm')?.addEventListener('submit', (e) => {
            if (this.currentStep !== 5) {
                e.preventDefault();
            }
        });
    }
    
    selectEnvironment(card) {
        // Remove seleção anterior
        document.querySelectorAll('.environment-card').forEach(c => {
            c.classList.remove('selected');
        });
        
        // Adiciona nova seleção
        card.classList.add('selected');
        
        this.selectedData.environment = {
            id: card.dataset.environment,
            name: card.querySelector('.card-title').textContent,
            description: card.querySelector('.card-description').textContent
        };
        
        // Atualiza campo hidden
        document.getElementById('selected_environment').value = this.selectedData.environment.id;
        
        // Habilita botão próxima etapa
        document.getElementById('btn-next-1').disabled = false;
        
        this.updatePreview();
    }
    
    selectLighting(card) {
        // Remove seleção anterior
        document.querySelectorAll('.lighting-card').forEach(c => {
            c.classList.remove('selected');
        });
        
        // Adiciona nova seleção
        card.classList.add('selected');
        
        this.selectedData.lighting = {
            id: card.dataset.lighting,
            name: card.querySelector('.card-title').textContent,
            description: card.querySelector('.card-description').textContent
        };
        
        // Atualiza campo hidden
        document.getElementById('selected_lighting').value = this.selectedData.lighting.id;
        
        this.updatePreview();
    }
    
    selectCharacter(card) {
        // Remove seleção anterior
        document.querySelectorAll('.character-card').forEach(c => {
            c.classList.remove('selected');
        });
        
        // Adiciona nova seleção
        card.classList.add('selected');
        
        this.selectedData.character = {
            id: card.dataset.character,
            name: card.querySelector('.card-title').textContent,
            description: card.querySelector('.card-description').textContent
        };
        
        // Atualiza campo hidden
        document.getElementById('selected_character').value = this.selectedData.character.id;
        
        this.updatePreview();
    }
    
    getEnvironmentPrompt(environmentId) {
        const environments = {
            'praia_tropical': 'tropical beach with palm trees and crystal clear waters',
            'cachoeira_gigante': 'majestic waterfall on a cliff',
            'montanha_nevada': 'snow-covered mountain peaks',
            'floresta_amazonica': 'dense Amazon rainforest with biodiversity',
            'deserto_sahara': 'endless dunes under scorching sun',
            'campo_lavanda': 'aromatic purple lavender fields',
            'aurora_boreal': 'dancing northern lights in polar sky',
            'vulcao_ativo': 'active volcano crater with glowing lava'
        };
        
        return environments[environmentId] || environmentId;
    }
    
    getLightingPrompt(lightingId) {
        const lighting = {
            'natural': 'natural daylight',
            'dourada': 'golden hour warm sunset light',
            'noturna': 'nighttime illumination',
            'neon': 'colorful vibrant neon lights',
            'dramatica': 'dramatic lighting with high contrast and shadows',
            'suave': 'soft diffused light',
            'cinematic': 'dramatic cinematic film lighting',
            'magical': 'supernatural magical bright illumination'
        };
        
        return lighting[lightingId] || lightingId;
    }
    
    getCharacterPrompt(characterId) {
        const characters = {
            'homem_jovem': 'young man aged 18-30 years with athletic physique',
            'mulher_jovem': 'young woman aged 18-30 years, elegant and modern',
            'homem_maduro': 'mature man aged 40-60 years, experienced and confident',
            'mulher_madura': 'mature woman aged 40-60 years, sophisticated and wise',
            'crianca_menino': 'boy child aged 5-12 years, playful and curious',
            'crianca_menina': 'girl child aged 5-12 years, cheerful and expressive',
            'artista': 'creative artist with bohemian style',
            'atleta': 'athlete with muscular and defined physique'
        };
        
        return characters[characterId] || characterId;
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
    
    skipStep() {
        this.nextStep();
    }
    
    goToStep(stepNumber) {
        if (stepNumber < 1 || stepNumber > this.totalSteps) return;
        
        // Esconder etapa atual
        document.querySelector(`.step-content[data-step="${this.currentStep}"]`)?.classList.remove('active');
        
        // Mostrar nova etapa
        document.querySelector(`.step-content[data-step="${stepNumber}"]`)?.classList.add('active');
        
        // Atualizar navegação
        this.updateStepNavigation(stepNumber);
        
        this.currentStep = stepNumber;
        document.getElementById('current_step').value = stepNumber;
        
        // Ações específicas por etapa
        if (stepNumber === 4) {
            this.updatePreview();
        }
    }
    
    updateStepNavigation(stepNumber) {
        document.querySelectorAll('.step-item').forEach((item, index) => {
            const step = index + 1;
            item.classList.remove('active', 'completed');
            
            if (step === stepNumber) {
                item.classList.add('active');
            } else if (step < stepNumber) {
                item.classList.add('completed');
            }
        });
        
        document.querySelectorAll('.step-connector').forEach((connector, index) => {
            const step = index + 1;
            connector.classList.toggle('completed', step < stepNumber);
        });
    }
    
    updatePreview() {
        const originalPrompt = document.getElementById('original_prompt')?.value || '';
        if (!originalPrompt.trim()) {
            document.getElementById('prompt_preview').textContent = 'Digite sua ideia acima para ver o preview...';
            return;
        }
        
        let enhancedPrompt = originalPrompt;
        
        // Adicionar personagem
        if (this.selectedData.character) {
            const characterPrompt = this.getCharacterPrompt(this.selectedData.character.id);
            enhancedPrompt = characterPrompt + ' in ' + enhancedPrompt;
        }
        
        // Adicionar ambiente
        if (this.selectedData.environment) {
            const environmentPrompt = this.getEnvironmentPrompt(this.selectedData.environment.id);
            enhancedPrompt = enhancedPrompt + ' in ' + environmentPrompt;
        }
        
        // Adicionar iluminação
        if (this.selectedData.lighting) {
            const lightingPrompt = this.getLightingPrompt(this.selectedData.lighting.id);
            enhancedPrompt = enhancedPrompt + ' with ' + lightingPrompt;
        }
        
        // Adicionar qualidade padrão
        enhancedPrompt += ', highly detailed, professional quality, masterpiece';
        
        document.getElementById('prompt_preview').textContent = enhancedPrompt;
    }
    
    
    
    generateFinalPrompt() {
        this.updatePreview();
        const finalPrompt = document.getElementById('prompt_preview').textContent;
        
        document.getElementById('final_prompt').textContent = finalPrompt;
        document.getElementById('enhanced_prompt').value = finalPrompt;
        
        // Salvar configurações
        const settings = {
            environment: this.selectedData.environment,
            lighting: this.selectedData.lighting,
            character: this.selectedData.character
        };
        
        document.getElementById('form_settings').value = JSON.stringify(settings);
        
        this.nextStep();
    }
    
    
    copyPrompt() {
        const promptText = document.getElementById('final_prompt').textContent;
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(promptText).then(() => {
                this.showToast('Prompt copiado para a área de transferência!', 'success');
            }).catch(err => {
                console.error('Erro ao copiar:', err);
                this.fallbackCopyPrompt(promptText);
            });
        } else {
            this.fallbackCopyPrompt(promptText);
        }
    }
    
    fallbackCopyPrompt(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        
        try {
            document.execCommand('copy');
            this.showToast('Prompt copiado para a área de transferência!', 'success');
        } catch (err) {
            this.showToast('Erro ao copiar. Copie manualmente o texto.', 'error');
        }
        
        document.body.removeChild(textArea);
    }
    
    showToast(message, type = 'info') {
        // Criar toast notification
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? 'var(--success-color)' : type === 'error' ? 'var(--error-color)' : 'var(--primary-color)'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            animation: slideInRight 0.3s ease;
        `;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
    
    startOver() {
        // Reset dados
        this.selectedData = {
            environment: null,
            lighting: null,
            character: null,
            settings: {}
        };
        
        // Limpar formulário
        document.getElementById('promptForm').reset();
        
        // Remover seleções
        document.querySelectorAll('.selection-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Voltar para primeira etapa
        this.goToStep(1);
        
        // Desabilitar botão
        document.getElementById('btn-next-1').disabled = true;
        
        this.showToast('Formulário resetado. Comece um novo prompt!', 'info');
    }
    
    loadCategoryStyles() {
        // Inicialização vazia - não é mais necessária
    }
}

// Funções globais para os botões
function nextStep() {
    window.promptGenerator.nextStep();
}

function previousStep() {
    window.promptGenerator.previousStep();
}

function skipStep() {
    window.promptGenerator.skipStep();
}

function generateFinalPrompt() {
    window.promptGenerator.generateFinalPrompt();
}

function copyPrompt() {
    window.promptGenerator.copyPrompt();
}

function startOver() {
    window.promptGenerator.startOver();
}

// Adicionar estilos CSS para animações
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .alert {
        padding: 1rem 1.5rem;
        border-radius: var(--radius-md);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .alert-success {
        background: #d1fae5;
        border: 1px solid #10b981;
        color: #065f46;
    }
    
    .alert-error {
        background: #fee2e2;
        border: 1px solid #ef4444;
        color: #991b1b;
    }
`;
document.head.appendChild(style);

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.promptGenerator = new PromptGenerator();
});