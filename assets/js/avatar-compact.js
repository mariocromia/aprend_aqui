/**
 * JavaScript para a nova aba Avatar compacta e moderna
 * Funcionalidades: seleção de tipos, geração de características dinâmicas,
 * salvamento de avatares e integração com sistema de prompts
 */

class AvatarCompact {
    constructor() {
        this.selectedType = null;
        this.savedAvatars = this.loadSavedAvatars();
        this.characteristics = {};
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateSavedCount();
        this.loadCharacteristicTemplates();
    }

    bindEvents() {
        // Eventos dos cards de tipo
        document.querySelectorAll('.type-card').forEach(card => {
            card.addEventListener('click', () => {
                this.selectAvatarType(card.dataset.type);
            });
        });

        // Evento do botão gerar prompt
        const generateBtn = document.querySelector('.btn-generate');
        if (generateBtn) {
            generateBtn.addEventListener('click', () => {
                this.generateAvatarPrompt();
            });
        }

        // Evento do botão salvar avatar
        const saveBtn = document.querySelector('.btn-save-avatar');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                this.saveAvatar();
            });
        }

        // Eventos dos botões de ação rápida
        const importBtn = document.querySelector('.btn-import-avatar');
        if (importBtn) {
            importBtn.addEventListener('click', () => {
                this.importAvatar();
            });
        }

        const exportBtn = document.querySelector('.btn-export-avatars');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                this.exportAvatars();
            });
        }

        // Evento de mudança na descrição personalizada
        const customDesc = document.getElementById('avatar-custom-description');
        if (customDesc) {
            customDesc.addEventListener('input', () => {
                this.updatePromptPreview();
            });
        }
    }

    selectAvatarType(type) {
        // Remove seleção anterior
        document.querySelectorAll('.type-card').forEach(card => {
            card.classList.remove('selected');
        });

        // Adiciona nova seleção
        const selectedCard = document.querySelector(`[data-type="${type}"]`);
        if (selectedCard) {
            selectedCard.classList.add('selected');
            this.selectedType = type;
            
            // Mostra características específicas
            this.showSpecificCharacteristics(type);
            
            // Atualiza preview do prompt
            this.updatePromptPreview();
        }
    }

    showSpecificCharacteristics(type) {
        const characteristicsSection = document.getElementById('specific-characteristics');
        const container = document.getElementById('characteristics-container');
        
        if (!characteristicsSection || !container) return;

        // Mostra a seção
        characteristicsSection.style.display = 'block';
        
        // Adiciona estado de carregamento
        container.classList.add('loading');
        container.innerHTML = '<p style="text-align: center; color: var(--text-muted); padding: 1rem;">Carregando características...</p>';

        // Simula carregamento e carrega características
        setTimeout(() => {
            container.classList.remove('loading');
            container.innerHTML = this.generateCharacteristicsHTML(type);
            this.bindCharacteristicEvents();
        }, 500);
    }

    generateCharacteristicsHTML(type) {
        const templates = this.getCharacteristicTemplate(type);
        
        if (!templates || templates.length === 0) {
            return '<p style="text-align: center; color: var(--text-muted);">Nenhuma característica específica disponível.</p>';
        }

        let html = '<div class="characteristics-grid">';
        
        templates.forEach(template => {
            html += `
                <div class="characteristic-group">
                    <label class="characteristic-label">${template.label}</label>
                    ${this.generateInputHTML(template)}
                </div>
            `;
        });

        html += '</div>';
        return html;
    }

    generateInputHTML(template) {
        switch (template.type) {
            case 'select':
                let options = '<option value="">Selecione</option>';
                template.options.forEach(option => {
                    options += `<option value="${option.value}">${option.label}</option>`;
                });
                return `<select class="characteristic-select" data-field="${template.field}">${options}</select>`;
                
            case 'input':
                return `<input type="text" class="characteristic-input" data-field="${template.field}" placeholder="${template.placeholder || ''}">`;
                
            case 'multi-select':
                let checkboxes = '';
                template.options.forEach(option => {
                    checkboxes += `
                        <label class="checkbox-option">
                            <input type="checkbox" data-field="${template.field}" value="${option.value}">
                            <span>${option.label}</span>
                        </label>
                    `;
                });
                return `<div class="checkbox-group">${checkboxes}</div>`;
                
            default:
                return `<input type="text" class="characteristic-input" data-field="${template.field}">`;
        }
    }

    bindCharacteristicEvents() {
        // Eventos para inputs de características
        document.querySelectorAll('.characteristic-select, .characteristic-input').forEach(input => {
            input.addEventListener('change', () => {
                this.updateCharacteristics();
                this.updatePromptPreview();
            });
        });

        // Eventos para checkboxes
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.updateCharacteristics();
                this.updatePromptPreview();
            });
        });
    }

    updateCharacteristics() {
        this.characteristics = {};

        // Coleta valores dos selects e inputs
        document.querySelectorAll('.characteristic-select, .characteristic-input').forEach(input => {
            if (input.value.trim()) {
                this.characteristics[input.dataset.field] = input.value;
            }
        });

        // Coleta valores dos checkboxes
        const checkboxGroups = {};
        document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
            const field = checkbox.dataset.field;
            if (!checkboxGroups[field]) {
                checkboxGroups[field] = [];
            }
            checkboxGroups[field].push(checkbox.value);
        });

        // Adiciona grupos de checkbox às características
        Object.keys(checkboxGroups).forEach(field => {
            this.characteristics[field] = checkboxGroups[field].join(', ');
        });
    }

    generateAvatarPrompt() {
        if (!this.selectedType) {
            this.showNotification('Selecione um tipo de personagem primeiro!', 'warning');
            return;
        }

        // Coleta informações
        const customDescription = document.getElementById('avatar-custom-description').value.trim();
        
        // Gera prompt base
        let prompt = this.getBasePrompt(this.selectedType);
        
        // Adiciona características específicas
        Object.keys(this.characteristics).forEach(key => {
            if (this.characteristics[key]) {
                prompt += `, ${this.characteristics[key]}`;
            }
        });

        // Adiciona descrição personalizada
        if (customDescription) {
            prompt += `, ${customDescription}`;
        }

        // Adiciona qualificadores de qualidade
        prompt += ', highly detailed, masterpiece, best quality';

        // Atualiza o campo de prompt principal se existir
        this.updateMainPrompt(prompt);
        
        // Mostra notificação de sucesso
        this.showNotification('Prompt do avatar gerado com sucesso!', 'success');
        
        // Animação no botão
        const btn = document.querySelector('.btn-generate');
        btn.style.transform = 'scale(0.95)';
        setTimeout(() => {
            btn.style.transform = '';
        }, 150);
    }

    saveAvatar() {
        if (!this.selectedType) {
            this.showNotification('Selecione um tipo de personagem primeiro!', 'warning');
            return;
        }

        // Coleta dados do avatar
        const avatarData = {
            id: Date.now(),
            type: this.selectedType,
            characteristics: { ...this.characteristics },
            customDescription: document.getElementById('avatar-custom-description').value.trim(),
            createdAt: new Date().toISOString(),
            name: this.generateAvatarName()
        };

        // Salva no storage
        this.savedAvatars.push(avatarData);
        this.saveSavedAvatars();
        
        // Atualiza interface
        this.updateSavedAvatarsList();
        this.updateSavedCount();
        
        this.showNotification(`Avatar "${avatarData.name}" salvo com sucesso!`, 'success');
        
        // Animação no botão
        const btn = document.querySelector('.btn-save-avatar');
        btn.style.transform = 'scale(0.95)';
        setTimeout(() => {
            btn.style.transform = '';
        }, 150);
    }

    generateAvatarName() {
        const typeNames = {
            human: 'Humano',
            animal: 'Animal', 
            fantasy: 'Fantasia',
            robot: 'Robô'
        };
        
        const baseName = typeNames[this.selectedType] || 'Avatar';
        const count = this.savedAvatars.filter(avatar => avatar.type === this.selectedType).length + 1;
        
        return `${baseName} ${count}`;
    }

    updateSavedAvatarsList() {
        const container = document.getElementById('saved-avatars-list');
        if (!container) return;

        if (this.savedAvatars.length === 0) {
            container.innerHTML = `
                <div class="avatar-saved-card placeholder">
                    <div class="avatar-saved-icon">
                        <i class="material-icons">person_outline</i>
                    </div>
                    <div class="avatar-saved-info">
                        <h4>Nenhum avatar salvo</h4>
                        <p>Crie e salve avatares para usar rapidamente</p>
                    </div>
                </div>
            `;
            return;
        }

        let html = '';
        this.savedAvatars.forEach(avatar => {
            const icon = this.getTypeIcon(avatar.type);
            html += `
                <div class="avatar-saved-card" data-avatar-id="${avatar.id}">
                    <div class="avatar-saved-icon">
                        <i class="material-icons">${icon}</i>
                    </div>
                    <div class="avatar-saved-info">
                        <h4>${avatar.name}</h4>
                        <p>${this.getTypeLabel(avatar.type)} • ${this.formatDate(avatar.createdAt)}</p>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;

        // Adiciona eventos de clique
        container.querySelectorAll('.avatar-saved-card[data-avatar-id]').forEach(card => {
            card.addEventListener('click', () => {
                this.loadAvatar(parseInt(card.dataset.avatarId));
            });
        });
    }

    loadAvatar(avatarId) {
        const avatar = this.savedAvatars.find(a => a.id === avatarId);
        if (!avatar) return;

        // Carrega tipo
        this.selectAvatarType(avatar.type);
        
        // Aguarda características carregarem e depois popula
        setTimeout(() => {
            // Popula características
            Object.keys(avatar.characteristics).forEach(field => {
                const input = document.querySelector(`[data-field="${field}"]`);
                if (input) {
                    if (input.type === 'checkbox') {
                        const values = avatar.characteristics[field].split(', ');
                        values.forEach(value => {
                            const checkbox = document.querySelector(`[data-field="${field}"][value="${value}"]`);
                            if (checkbox) checkbox.checked = true;
                        });
                    } else {
                        input.value = avatar.characteristics[field];
                    }
                }
            });

            // Popula descrição personalizada
            if (avatar.customDescription) {
                document.getElementById('avatar-custom-description').value = avatar.customDescription;
            }

            // Atualiza preview
            this.updateCharacteristics();
            this.updatePromptPreview();
            
        }, 600);

        this.showNotification(`Avatar "${avatar.name}" carregado!`, 'success');
    }

    updateSavedCount() {
        const countElement = document.querySelector('.saved-count');
        if (countElement) {
            countElement.textContent = `${this.savedAvatars.length} salvos`;
        }
    }

    importAvatar() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.json';
        input.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    try {
                        const importedAvatars = JSON.parse(e.target.result);
                        if (Array.isArray(importedAvatars)) {
                            this.savedAvatars.push(...importedAvatars);
                            this.saveSavedAvatars();
                            this.updateSavedAvatarsList();
                            this.updateSavedCount();
                            this.showNotification(`${importedAvatars.length} avatares importados!`, 'success');
                        }
                    } catch (error) {
                        this.showNotification('Erro ao importar arquivo!', 'error');
                    }
                };
                reader.readAsText(file);
            }
        };
        input.click();
    }

    exportAvatars() {
        if (this.savedAvatars.length === 0) {
            this.showNotification('Nenhum avatar para exportar!', 'warning');
            return;
        }

        const dataStr = JSON.stringify(this.savedAvatars, null, 2);
        const dataBlob = new Blob([dataStr], { type: 'application/json' });
        
        const link = document.createElement('a');
        link.href = URL.createObjectURL(dataBlob);
        link.download = `avatares_salvos_${new Date().toISOString().split('T')[0]}.json`;
        link.click();
        
        this.showNotification('Avatares exportados com sucesso!', 'success');
    }

    // Métodos utilitários
    getBasePrompt(type) {
        const prompts = {
            human: 'portrait of a person',
            animal: 'a beautiful animal',
            fantasy: 'a fantasy creature',
            robot: 'a futuristic robot'
        };
        return prompts[type] || 'a character';
    }

    getTypeIcon(type) {
        const icons = {
            human: 'person',
            animal: 'pets',
            fantasy: 'auto_fix_high',
            robot: 'smart_toy'
        };
        return icons[type] || 'person_outline';
    }

    getTypeLabel(type) {
        const labels = {
            human: 'Humano',
            animal: 'Animal',
            fantasy: 'Fantasia',
            robot: 'Robô/IA'
        };
        return labels[type] || 'Avatar';
    }

    formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('pt-BR');
    }

    loadCharacteristicTemplates() {
        // Define templates de características por tipo
        this.characteristicTemplates = {
            human: [
                {
                    field: 'age',
                    label: 'Idade',
                    type: 'select',
                    options: [
                        { value: 'child', label: 'Criança' },
                        { value: 'teenager', label: 'Adolescente' },
                        { value: 'young adult', label: 'Jovem Adulto' },
                        { value: 'adult', label: 'Adulto' },
                        { value: 'elderly', label: 'Idoso' }
                    ]
                },
                {
                    field: 'gender',
                    label: 'Gênero',
                    type: 'select',
                    options: [
                        { value: 'male', label: 'Masculino' },
                        { value: 'female', label: 'Feminino' },
                        { value: 'non-binary', label: 'Não-binário' }
                    ]
                },
                {
                    field: 'hair_color',
                    label: 'Cor do Cabelo',
                    type: 'input',
                    placeholder: 'Ex: loiro, moreno, ruivo'
                },
                {
                    field: 'clothing',
                    label: 'Vestimenta',
                    type: 'input',
                    placeholder: 'Ex: casual, formal, medieval'
                }
            ],
            animal: [
                {
                    field: 'species',
                    label: 'Espécie',
                    type: 'select',
                    options: [
                        { value: 'cat', label: 'Gato' },
                        { value: 'dog', label: 'Cachorro' },
                        { value: 'horse', label: 'Cavalo' },
                        { value: 'bird', label: 'Pássaro' },
                        { value: 'wild animal', label: 'Animal Selvagem' }
                    ]
                },
                {
                    field: 'fur_color',
                    label: 'Cor da Pelagem',
                    type: 'input',
                    placeholder: 'Ex: marrom, preto, branco'
                },
                {
                    field: 'size',
                    label: 'Tamanho',
                    type: 'select',
                    options: [
                        { value: 'small', label: 'Pequeno' },
                        { value: 'medium', label: 'Médio' },
                        { value: 'large', label: 'Grande' },
                        { value: 'giant', label: 'Gigante' }
                    ]
                }
            ],
            fantasy: [
                {
                    field: 'creature_type',
                    label: 'Tipo de Criatura',
                    type: 'select',
                    options: [
                        { value: 'elf', label: 'Elfo' },
                        { value: 'dwarf', label: 'Anão' },
                        { value: 'fairy', label: 'Fada' },
                        { value: 'dragon', label: 'Dragão' },
                        { value: 'unicorn', label: 'Unicórnio' },
                        { value: 'phoenix', label: 'Fênix' }
                    ]
                },
                {
                    field: 'magical_abilities',
                    label: 'Habilidades Mágicas',
                    type: 'multi-select',
                    options: [
                        { value: 'fire magic', label: 'Magia do Fogo' },
                        { value: 'water magic', label: 'Magia da Água' },
                        { value: 'earth magic', label: 'Magia da Terra' },
                        { value: 'air magic', label: 'Magia do Ar' },
                        { value: 'healing magic', label: 'Magia de Cura' }
                    ]
                }
            ],
            robot: [
                {
                    field: 'robot_type',
                    label: 'Tipo de Robô',
                    type: 'select',
                    options: [
                        { value: 'android', label: 'Android' },
                        { value: 'cyborg', label: 'Cyborg' },
                        { value: 'mech', label: 'Mech' },
                        { value: 'AI assistant', label: 'Assistente IA' }
                    ]
                },
                {
                    field: 'materials',
                    label: 'Materiais',
                    type: 'multi-select',
                    options: [
                        { value: 'chrome metal', label: 'Metal Cromado' },
                        { value: 'carbon fiber', label: 'Fibra de Carbono' },
                        { value: 'titanium', label: 'Titânio' },
                        { value: 'synthetic skin', label: 'Pele Sintética' }
                    ]
                }
            ]
        };
    }

    getCharacteristicTemplate(type) {
        return this.characteristicTemplates[type] || [];
    }

    updateMainPrompt(prompt) {
        // Integração com o sistema principal de prompts
        if (window.promptGenerator) {
            window.promptGenerator.updateSelection('character', prompt);
        }
    }

    updatePromptPreview() {
        // Atualiza preview em tempo real se necessário
        if (window.promptGenerator) {
            window.promptGenerator.updatePromptPreview();
        }
    }

    showNotification(message, type = 'info') {
        // Cria notificação toast
        const notification = document.createElement('div');
        notification.className = `notification toast-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="material-icons">${this.getNotificationIcon(type)}</i>
                <span>${message}</span>
            </div>
        `;

        // Adiciona estilos inline para garantir visibilidade
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : type === 'error' ? '#ef4444' : '#3b82f6',
            color: 'white',
            padding: '12px 16px',
            borderRadius: '8px',
            zIndex: '10000',
            display: 'flex',
            alignItems: 'center',
            gap: '8px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            transform: 'translateX(100%)',
            transition: 'transform 0.3s ease'
        });

        document.body.appendChild(notification);

        // Animação de entrada
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Remove após 3 segundos
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'check_circle',
            warning: 'warning',
            error: 'error',
            info: 'info'
        };
        return icons[type] || 'info';
    }

    // Storage methods
    loadSavedAvatars() {
        try {
            const saved = localStorage.getItem('saved_avatars');
            return saved ? JSON.parse(saved) : [];
        } catch (error) {
            console.warn('Erro ao carregar avatares salvos:', error);
            return [];
        }
    }

    saveSavedAvatars() {
        try {
            localStorage.setItem('saved_avatars', JSON.stringify(this.savedAvatars));
        } catch (error) {
            console.warn('Erro ao salvar avatares:', error);
        }
    }
}

// Inicialização quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    // Verifica se estamos na aba avatar antes de inicializar
    if (document.getElementById('tab-avatar')) {
        window.avatarCompact = new AvatarCompact();
    }
});

// Integração com o sistema principal
window.selectAvatarType = function(type) {
    if (window.avatarCompact) {
        window.avatarCompact.selectAvatarType(type);
    }
};

window.generateAvatarPrompt = function() {
    if (window.avatarCompact) {
        window.avatarCompact.generateAvatarPrompt();
    }
};

window.saveAvatar = function() {
    if (window.avatarCompact) {
        window.avatarCompact.saveAvatar();
    }
};

window.importAvatar = function() {
    if (window.avatarCompact) {
        window.avatarCompact.importAvatar();
    }
};

window.exportAvatars = function() {
    if (window.avatarCompact) {
        window.avatarCompact.exportAvatars();
    }
};