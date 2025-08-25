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
        this.updateSavedAvatarsList();
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
        this.selectedType = type;
        
        // Abre modal do formulário
        this.openAvatarFormModal(type);
    }

    openAvatarFormModal(type) {
        // Cria modal se não existir
        if (!document.getElementById('avatar-form-modal')) {
            this.createAvatarFormModal();
        }
        
        const modal = document.getElementById('avatar-form-modal');
        const modalTitle = modal.querySelector('.modal-title');
        const characteristicsContainer = modal.querySelector('#modal-characteristics-container');
        
        // Atualiza título do modal
        modalTitle.textContent = `Criar ${this.getTypeLabel(type)}`;
        
        // Carrega características específicas
        characteristicsContainer.innerHTML = this.generateCharacteristicsHTML(type);
        this.bindCharacteristicEvents();
        
        // Mostra modal
        modal.style.display = 'flex';
        
        // Adiciona animação
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }

    createAvatarFormModal() {
        const modal = document.createElement('div');
        modal.id = 'avatar-form-modal';
        modal.className = 'avatar-modal';
        modal.innerHTML = `
            <div class="modal-backdrop"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Criar Avatar</h3>
                    <button type="button" class="modal-close" onclick="avatarCompact.closeAvatarFormModal()">
                        <i class="material-icons">close</i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="avatar-form" class="compact-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="avatar-name">Nome do Avatar</label>
                                <input type="text" id="avatar-name" class="form-input" placeholder="Ex: Ana Silva" required>
                            </div>
                            <div class="form-group">
                                <label for="avatar-category">Categoria</label>
                                <select id="avatar-category" class="form-select">
                                    <option value="principal">Principal</option>
                                    <option value="secundario">Secundário</option>
                                    <option value="background">Background</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="modal-characteristics-container" class="characteristics-container">
                            <!-- Características específicas serão inseridas aqui -->
                        </div>
                        
                        <div class="form-group">
                            <label for="avatar-description">Descrição Adicional</label>
                            <textarea id="avatar-description" class="form-textarea" rows="3" placeholder="Detalhes extras sobre aparência, personalidade, etc."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="avatarCompact.closeAvatarFormModal()">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="avatarCompact.saveAvatarFromModal()">Salvar Avatar</button>
                    <button type="button" class="btn btn-success" onclick="avatarCompact.generateAndSaveAvatar()">Gerar Prompt</button>
                </div>
            </div>
        `;
        
        // Adiciona estilos do modal
        this.addModalStyles();
        
        document.body.appendChild(modal);
    }

    closeAvatarFormModal() {
        const modal = document.getElementById('avatar-form-modal');
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                // Limpa formulário
                modal.querySelector('#avatar-form').reset();
            }, 300);
        }
    }

    addModalStyles() {
        if (document.getElementById('avatar-modal-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'avatar-modal-styles';
        styles.textContent = `
            .avatar-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .avatar-modal.show {
                opacity: 1;
            }
            
            .modal-backdrop {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
            }
            
            .modal-content {
                position: relative;
                background: white;
                border-radius: 12px;
                max-width: 600px;
                width: 90%;
                max-height: 90vh;
                overflow: hidden;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                transform: scale(0.95);
                transition: transform 0.3s ease;
            }
            
            .avatar-modal.show .modal-content {
                transform: scale(1);
            }
            
            .modal-header {
                padding: 1.5rem;
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            
            .modal-title {
                margin: 0;
                font-size: 1.25rem;
                font-weight: 600;
            }
            
            .modal-close {
                background: none;
                border: none;
                color: white;
                cursor: pointer;
                padding: 0.5rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background-color 0.2s;
            }
            
            .modal-close:hover {
                background: rgba(255, 255, 255, 0.1);
            }
            
            .modal-body {
                padding: 1.5rem;
                max-height: 60vh;
                overflow-y: auto;
            }
            
            .compact-form .form-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .compact-form .form-group {
                margin-bottom: 1rem;
            }
            
            .compact-form .form-group label {
                display: block;
                font-weight: 500;
                margin-bottom: 0.5rem;
                color: #374151;
                font-size: 0.875rem;
            }
            
            .compact-form .form-input,
            .compact-form .form-select,
            .compact-form .form-textarea {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 0.875rem;
                transition: border-color 0.2s, box-shadow 0.2s;
            }
            
            .compact-form .form-input:focus,
            .compact-form .form-select:focus,
            .compact-form .form-textarea:focus {
                outline: none;
                border-color: #6366f1;
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            }
            
            .characteristics-container {
                margin: 1.5rem 0;
            }
            
            .characteristics-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
            
            .characteristic-group {
                margin-bottom: 1rem;
            }
            
            .characteristic-label {
                display: block;
                font-weight: 500;
                margin-bottom: 0.5rem;
                color: #374151;
                font-size: 0.875rem;
            }
            
            .characteristic-select,
            .characteristic-input {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 0.875rem;
                transition: border-color 0.2s;
            }
            
            .characteristic-select:focus,
            .characteristic-input:focus {
                outline: none;
                border-color: #6366f1;
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            }
            
            .checkbox-group {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.5rem;
            }
            
            .checkbox-option {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                cursor: pointer;
                padding: 0.5rem;
                border-radius: 4px;
                transition: background-color 0.2s;
            }
            
            .checkbox-option:hover {
                background: #f3f4f6;
            }
            
            .checkbox-option input[type="checkbox"] {
                margin: 0;
            }
            
            .checkbox-option span {
                font-size: 0.875rem;
                color: #374151;
            }
            
            .modal-footer {
                padding: 1.5rem;
                border-top: 1px solid #e5e7eb;
                display: flex;
                justify-content: flex-end;
                gap: 1rem;
                background: #f9fafb;
            }
            
            .modal-footer .btn {
                padding: 0.75rem 1.5rem;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 500;
                font-size: 0.875rem;
                transition: all 0.2s;
            }
            
            .modal-footer .btn-secondary {
                background: #6b7280;
                color: white;
            }
            
            .modal-footer .btn-secondary:hover {
                background: #4b5563;
            }
            
            .modal-footer .btn-primary {
                background: #6366f1;
                color: white;
            }
            
            .modal-footer .btn-primary:hover {
                background: #5855eb;
            }
            
            .modal-footer .btn-success {
                background: #10b981;
                color: white;
            }
            
            .modal-footer .btn-success:hover {
                background: #059669;
            }
            
            @media (max-width: 768px) {
                .compact-form .form-grid,
                .characteristics-grid,
                .checkbox-group {
                    grid-template-columns: 1fr;
                }
                
                .modal-content {
                    width: 95%;
                    margin: 1rem;
                }
                
                .modal-footer {
                    flex-direction: column;
                }
            }
        `;
        
        document.head.appendChild(styles);
    }

    generateCharacteristicsHTML(type) {
        const templates = this.getCharacteristicTemplate(type);
        
        if (!templates || templates.length === 0) {
            return '<p style="text-align: center; color: #6b7280; padding: 1rem;">Nenhuma característica específica disponível.</p>';
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
                let options = '<option value="">Selecione uma opção</option>';
                template.options.forEach(option => {
                    options += `<option value="${option.value}">${option.label}</option>`;
                });
                return `<select class="characteristic-select" data-field="${template.field}">${options}</select>`;
                
            case 'input':
                return `<input type="text" class="characteristic-input" data-field="${template.field}" placeholder="${template.placeholder || 'Digite aqui...'}">`;
                
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
                return `<input type="text" class="characteristic-input" data-field="${template.field}" placeholder="Digite aqui...">`;
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

    saveAvatarFromModal() {
        const form = document.getElementById('avatar-form');
        const formData = new FormData(form);
        
        if (!this.selectedType) {
            this.showNotification('Erro: tipo de avatar não selecionado!', 'error');
            return;
        }

        // Valida campos obrigatórios
        const name = document.getElementById('avatar-name').value.trim();
        if (!name) {
            this.showNotification('Por favor, digite um nome para o avatar!', 'warning');
            return;
        }

        // Coleta dados do formulário
        const avatarData = {
            id: Date.now(),
            type: this.selectedType,
            name: name,
            category: document.getElementById('avatar-category').value,
            characteristics: { ...this.characteristics },
            customDescription: document.getElementById('avatar-description').value.trim(),
            createdAt: new Date().toISOString()
        };

        // Salva no storage
        this.savedAvatars.push(avatarData);
        this.saveSavedAvatars();
        
        // Atualiza interface
        this.updateSavedAvatarsList();
        this.updateSavedCount();
        
        // Fecha modal
        this.closeAvatarFormModal();
        
        this.showNotification(`Avatar "${avatarData.name}" salvo com sucesso!`, 'success');
    }

    generateAndSaveAvatar() {
        // Primeiro salva o avatar
        this.saveAvatarFromModal();
        
        // Depois gera o prompt
        setTimeout(() => {
            this.generateAvatarPrompt();
        }, 500);
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
                        { value: 'child', label: 'Criança (5-12 anos)' },
                        { value: 'teenager', label: 'Adolescente (13-17 anos)' },
                        { value: 'young adult', label: 'Jovem Adulto (18-25 anos)' },
                        { value: 'adult', label: 'Adulto (26-50 anos)' },
                        { value: 'middle aged', label: 'Meia-idade (51-65 anos)' },
                        { value: 'elderly', label: 'Idoso (65+ anos)' }
                    ]
                },
                {
                    field: 'gender',
                    label: 'Gênero',
                    type: 'select',
                    options: [
                        { value: 'male', label: 'Masculino' },
                        { value: 'female', label: 'Feminino' },
                        { value: 'non-binary', label: 'Não-binário' },
                        { value: 'androgynous', label: 'Andrógino' }
                    ]
                },
                {
                    field: 'hair_style',
                    label: 'Estilo de Cabelo',
                    type: 'multi-select',
                    options: [
                        { value: 'long hair', label: 'Cabelo Longo' },
                        { value: 'short hair', label: 'Cabelo Curto' },
                        { value: 'medium hair', label: 'Cabelo Médio' },
                        { value: 'curly hair', label: 'Cabelo Cacheado' },
                        { value: 'wavy hair', label: 'Cabelo Ondulado' },
                        { value: 'straight hair', label: 'Cabelo Liso' },
                        { value: 'braided hair', label: 'Cabelo Trançado' },
                        { value: 'ponytail', label: 'Rabo de Cavalo' },
                        { value: 'messy hair', label: 'Cabelo Bagunçado' },
                        { value: 'bald', label: 'Careca' }
                    ]
                },
                {
                    field: 'hair_color',
                    label: 'Cor do Cabelo',
                    type: 'multi-select',
                    options: [
                        { value: 'black hair', label: 'Cabelo Preto' },
                        { value: 'brown hair', label: 'Cabelo Castanho' },
                        { value: 'dark brown hair', label: 'Cabelo Castanho Escuro' },
                        { value: 'light brown hair', label: 'Cabelo Castanho Claro' },
                        { value: 'blonde hair', label: 'Cabelo Loiro' },
                        { value: 'platinum blonde', label: 'Loiro Platinado' },
                        { value: 'red hair', label: 'Cabelo Ruivo' },
                        { value: 'auburn hair', label: 'Cabelo Ruivo Escuro' },
                        { value: 'white hair', label: 'Cabelo Branco' },
                        { value: 'silver hair', label: 'Cabelo Prateado' },
                        { value: 'gray hair', label: 'Cabelo Grisalho' }
                    ]
                },
                {
                    field: 'eye_color',
                    label: 'Cor dos Olhos',
                    type: 'multi-select',
                    options: [
                        { value: 'brown eyes', label: 'Olhos Castanhos' },
                        { value: 'dark brown eyes', label: 'Olhos Castanho Escuro' },
                        { value: 'hazel eyes', label: 'Olhos Amendoados' },
                        { value: 'blue eyes', label: 'Olhos Azuis' },
                        { value: 'light blue eyes', label: 'Olhos Azul Claro' },
                        { value: 'dark blue eyes', label: 'Olhos Azul Escuro' },
                        { value: 'green eyes', label: 'Olhos Verdes' },
                        { value: 'emerald eyes', label: 'Olhos Verde Esmeralda' },
                        { value: 'gray eyes', label: 'Olhos Cinza' },
                        { value: 'amber eyes', label: 'Olhos Âmbar' },
                        { value: 'violet eyes', label: 'Olhos Violeta' }
                    ]
                },
                {
                    field: 'skin_tone',
                    label: 'Tom de Pele',
                    type: 'select',
                    options: [
                        { value: 'pale skin', label: 'Pele Pálida' },
                        { value: 'fair skin', label: 'Pele Clara' },
                        { value: 'light skin', label: 'Pele Clara Rosada' },
                        { value: 'medium skin', label: 'Pele Morena Clara' },
                        { value: 'olive skin', label: 'Pele Morena Olivácea' },
                        { value: 'tan skin', label: 'Pele Bronzeada' },
                        { value: 'brown skin', label: 'Pele Morena' },
                        { value: 'dark brown skin', label: 'Pele Morena Escura' },
                        { value: 'black skin', label: 'Pele Negra' },
                        { value: 'ebony skin', label: 'Pele Ébano' }
                    ]
                },
                {
                    field: 'facial_features',
                    label: 'Características Faciais',
                    type: 'multi-select',
                    options: [
                        { value: 'angular face', label: 'Rosto Angular' },
                        { value: 'round face', label: 'Rosto Redondo' },
                        { value: 'square face', label: 'Rosto Quadrado' },
                        { value: 'oval face', label: 'Rosto Oval' },
                        { value: 'sharp jawline', label: 'Maxilar Definido' },
                        { value: 'soft features', label: 'Traços Suaves' },
                        { value: 'high cheekbones', label: 'Maçãs do Rosto Altas' },
                        { value: 'dimples', label: 'Covinhas' },
                        { value: 'freckles', label: 'Sardas' },
                        { value: 'beauty mark', label: 'Pinta de Beleza' }
                    ]
                },
                {
                    field: 'body_type',
                    label: 'Tipo Corporal',
                    type: 'select',
                    options: [
                        { value: 'slim build', label: 'Magro/Esguio' },
                        { value: 'athletic build', label: 'Atlético' },
                        { value: 'muscular build', label: 'Musculoso' },
                        { value: 'average build', label: 'Físico Médio' },
                        { value: 'curvy figure', label: 'Curvilíneo' },
                        { value: 'stocky build', label: 'Robusto' },
                        { value: 'tall and lean', label: 'Alto e Magro' },
                        { value: 'short and sturdy', label: 'Baixo e Forte' }
                    ]
                },
                {
                    field: 'clothing_style',
                    label: 'Estilo de Roupa',
                    type: 'multi-select',
                    options: [
                        { value: 'casual clothes', label: 'Casual' },
                        { value: 'formal attire', label: 'Formal' },
                        { value: 'business suit', label: 'Terno Executivo' },
                        { value: 'elegant dress', label: 'Vestido Elegante' },
                        { value: 'vintage style', label: 'Vintage' },
                        { value: 'bohemian style', label: 'Boêmio' },
                        { value: 'gothic style', label: 'Gótico' },
                        { value: 'street wear', label: 'Street Wear' },
                        { value: 'athletic wear', label: 'Roupa Esportiva' },
                        { value: 'medieval clothing', label: 'Roupas Medievais' }
                    ]
                },
                {
                    field: 'accessories',
                    label: 'Acessórios',
                    type: 'multi-select',
                    options: [
                        { value: 'glasses', label: 'Óculos' },
                        { value: 'sunglasses', label: 'Óculos de Sol' },
                        { value: 'jewelry', label: 'Joias' },
                        { value: 'necklace', label: 'Colar' },
                        { value: 'earrings', label: 'Brincos' },
                        { value: 'bracelet', label: 'Pulseira' },
                        { value: 'watch', label: 'Relógio' },
                        { value: 'hat', label: 'Chapéu' },
                        { value: 'scarf', label: 'Lenço/Cachecol' },
                        { value: 'tattoos', label: 'Tatuagens' }
                    ]
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