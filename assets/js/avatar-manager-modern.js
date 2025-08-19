/**
 * GERENCIADOR MODERNO DE AVATARES
 * Sistema completo sem modais, com campos de seleção avançados
 */

class AvatarManagerModern {
    constructor() {
        this.avatars = [];
        this.selectedAvatars = [];
        this.currentView = 'grid';
        this.currentSort = 'created';
        this.filters = {
            search: '',
            category: '',
            status: ['meus'],
            sortBy: 'created'
        };
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadSampleData();
        this.updateStats();
        this.renderAvatars();
        this.setupDynamicFields();
    }
    
    // ===== EVENT BINDING =====
    bindEvents() {
        // View controls
        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const target = e.target.closest('.btn-view');
                if (target && target.dataset.view) {
                    this.setView(target.dataset.view);
                }
            });
        });
        
        
        // Search
        document.getElementById('avatar-search')?.addEventListener('input', this.handleSearch.bind(this));
        document.getElementById('clear-search')?.addEventListener('click', this.clearSearch.bind(this));
        
        // Filters
        document.getElementById('filter-category')?.addEventListener('change', this.handleFilterChange.bind(this));
        document.getElementById('sort-by')?.addEventListener('change', this.handleSortChange.bind(this));
        
        // Filter checkboxes
        document.querySelectorAll('.checkbox-item input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', this.handleStatusFilter.bind(this));
        });
        
        // Avatar type selection for dynamic fields
        document.getElementById('avatar-type')?.addEventListener('change', this.handleTypeChange.bind(this));
        
        // Form submission
        document.getElementById('avatar-creation-form')?.addEventListener('submit', this.handleCreateAvatar.bind(this));
        document.getElementById('clear-form')?.addEventListener('click', this.clearForm.bind(this));
        
        // Details panel
        document.getElementById('close-details')?.addEventListener('click', this.hideDetails.bind(this));
        
        // Detail actions
        document.getElementById('add-to-prompt')?.addEventListener('click', this.addCurrentToPrompt.bind(this));
        document.getElementById('quick-add-to-prompt')?.addEventListener('click', this.addCurrentToPrompt.bind(this));
        document.getElementById('toggle-favorite')?.addEventListener('click', this.toggleCurrentFavorite.bind(this));
        document.getElementById('duplicate-avatar')?.addEventListener('click', this.duplicateCurrentAvatar.bind(this));
        document.getElementById('generate-prompt')?.addEventListener('click', this.generateCurrentPrompt.bind(this));
        document.getElementById('copy-prompt')?.addEventListener('click', this.copyCurrentPrompt.bind(this));
        document.getElementById('edit-avatar')?.addEventListener('click', this.editCurrentAvatar.bind(this));
        document.getElementById('delete-avatar')?.addEventListener('click', this.deleteCurrentAvatar.bind(this));
        
        // Refresh
        document.getElementById('refresh-avatars')?.addEventListener('click', this.refreshAvatars.bind(this));
    }
    
    // ===== DATA MANAGEMENT =====
    loadSampleData() {
        this.avatars = [
            {
                id: 1,
                name: 'Elena Rodriguez',
                type: 'humano',
                gender: 'feminino',
                age: 'adulto',
                description: 'Jovem médica especialista em emergências médicas, sempre pronta para ajudar.',
                tags: ['médica', 'emergência', 'jovem', 'profissional'],
                visibility: 'privado',
                favorite: true,
                created: '2024-01-15T10:30:00Z',
                lastUsed: '2024-01-20T14:20:00Z',
                characteristics: {
                    profissao: 'Médica',
                    especialidade: 'Emergências',
                    personalidade: 'Determinada e empática'
                }
            },
            {
                id: 2,
                name: 'Dragão Místico Azul',
                type: 'fantastico',
                gender: 'neutro',
                age: 'adulto',
                description: 'Antigo dragão guardião das montanhas cristalinas, protetor dos tesouros ancestrais.',
                tags: ['dragão', 'guardião', 'místico', 'azul'],
                visibility: 'publico',
                favorite: false,
                created: '2024-01-10T16:45:00Z',
                lastUsed: '2024-01-18T09:15:00Z',
                characteristics: {
                    elemento: 'Gelo',
                    poder: 'Sopro congelante',
                    origem: 'Montanhas Cristalinas'
                }
            },
            {
                id: 3,
                name: 'Alpha Wolf',
                type: 'animal',
                gender: 'masculino',
                age: 'adulto',
                description: 'Lobo alfa líder de uma grande matilha, respeitado por sua sabedoria e força.',
                tags: ['lobo', 'alfa', 'líder', 'selvagem'],
                visibility: 'privado',
                favorite: true,
                created: '2024-01-12T08:20:00Z',
                lastUsed: '2024-01-19T17:30:00Z',
                characteristics: {
                    especie: 'Canis lupus',
                    habitat: 'Floresta temperada',
                    comportamento: 'Dominante e protetor'
                }
            },
            {
                id: 4,
                name: 'Capitão Zephyr',
                type: 'extraterrestre',
                gender: 'masculino',
                age: 'adulto',
                description: 'Comandante experiente de uma frota intergaláctica, conhecido por sua diplomacia.',
                tags: ['comandante', 'espaço', 'diplomata', 'galáctico'],
                visibility: 'publico',
                favorite: false,
                created: '2024-01-08T12:10:00Z',
                lastUsed: '2024-01-16T11:45:00Z',
                characteristics: {
                    planeta: 'Kepler-442b',
                    tecnologia: 'Propulsão quântica',
                    missao: 'Exploração pacífica'
                }
            },
            {
                id: 5,
                name: 'ARIA-9000',
                type: 'robotico',
                gender: 'feminino',
                age: 'adulto',
                description: 'Androide assistente pessoal com IA avançada e personalidade carinhosa.',
                tags: ['android', 'assistente', 'ia', 'carinhosa'],
                visibility: 'privado',
                favorite: true,
                created: '2024-01-05T14:25:00Z',
                lastUsed: '2024-01-17T16:40:00Z',
                characteristics: {
                    processador: 'Quantum Core X1',
                    funcoes: 'Assistência doméstica',
                    emocoes: 'Protocolo empático ativo'
                }
            }
        ];
    }
    
    // ===== VIEW CONTROLS =====
    setView(view) {
        this.currentView = view;
        
        // Update buttons
        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.view === view);
        });
        
        // Update grid class
        const grid = document.getElementById('avatars-grid');
        if (grid) {
            if (view === 'list') {
                grid.classList.add('list-view');
            } else {
                grid.classList.remove('list-view');
            }
        }
    }
    
    
    // ===== FILTERING AND SEARCH =====
    handleSearch(e) {
        this.filters.search = e.target.value.toLowerCase();
        this.renderAvatars();
        
        // Show/hide clear button
        const clearBtn = document.getElementById('clear-search');
        if (clearBtn) {
            clearBtn.style.display = e.target.value ? 'block' : 'none';
        }
    }
    
    clearSearch() {
        const searchInput = document.getElementById('avatar-search');
        if (searchInput) {
            searchInput.value = '';
            this.filters.search = '';
            this.renderAvatars();
            
            const clearBtn = document.getElementById('clear-search');
            if (clearBtn) clearBtn.style.display = 'none';
        }
    }
    
    handleFilterChange(e) {
        this.filters.category = e.target.value;
        this.renderAvatars();
    }
    
    handleSortChange(e) {
        this.filters.sortBy = e.target.value;
        this.renderAvatars();
    }
    
    handleStatusFilter() {
        const checkboxes = document.querySelectorAll('.checkbox-item input[type="checkbox"]');
        this.filters.status = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        this.renderAvatars();
    }
    
    getFilteredAvatars() {
        let filtered = [...this.avatars];
        
        // Search filter
        if (this.filters.search) {
            filtered = filtered.filter(avatar => {
                const searchText = [
                    avatar.name,
                    avatar.description,
                    avatar.type,
                    ...avatar.tags
                ].join(' ').toLowerCase();
                return searchText.includes(this.filters.search);
            });
        }
        
        // Category filter
        if (this.filters.category) {
            filtered = filtered.filter(avatar => avatar.type === this.filters.category);
        }
        
        // Status filter
        filtered = filtered.filter(avatar => {
            if (this.filters.status.includes('meus') && avatar.visibility === 'privado') return true;
            if (this.filters.status.includes('publicos') && avatar.visibility === 'publico') return true;
            if (this.filters.status.includes('favoritos') && avatar.favorite) return true;
            return false;
        });
        
        // Sort
        filtered.sort((a, b) => {
            switch (this.filters.sortBy) {
                case 'name':
                    return a.name.localeCompare(b.name);
                case 'created':
                    return new Date(b.created) - new Date(a.created);
                case 'used':
                    if (!a.lastUsed && !b.lastUsed) return 0;
                    if (!a.lastUsed) return 1;
                    if (!b.lastUsed) return -1;
                    return new Date(b.lastUsed) - new Date(a.lastUsed);
                case 'type':
                    return a.type.localeCompare(b.type);
                default:
                    return 0;
            }
        });
        
        return filtered;
    }
    
    // ===== RENDERING =====
    renderAvatars() {
        const grid = document.getElementById('avatars-grid');
        const loadingState = document.getElementById('loading-state');
        const emptyState = document.getElementById('empty-state');
        
        if (!grid) return;
        
        const filteredAvatars = this.getFilteredAvatars();
        
        // Hide loading state
        if (loadingState) loadingState.style.display = 'none';
        
        if (filteredAvatars.length === 0) {
            grid.innerHTML = '';
            if (emptyState) emptyState.style.display = 'flex';
            this.updateResultsInfo(0, this.avatars.length);
            return;
        }
        
        if (emptyState) emptyState.style.display = 'none';
        
        grid.innerHTML = '';
        
        filteredAvatars.forEach(avatar => {
            const card = this.createAvatarCard(avatar);
            grid.appendChild(card);
        });
        
        this.updateResultsInfo(filteredAvatars.length, this.avatars.length);
    }
    
    createAvatarCard(avatar) {
        const card = document.createElement('div');
        card.className = `avatar-card ${avatar.favorite ? 'favorite' : ''}`;
        card.dataset.id = avatar.id;
        
        const typeIcons = {
            humano: 'person',
            animal: 'pets',
            fantastico: 'auto_fix_high',
            extraterrestre: 'rocket_launch',
            robotico: 'smart_toy'
        };
        
        card.innerHTML = `
            <div class="avatar-card-actions">
                <button class="card-action-btn" data-action="add-to-prompt" title="Adicionar ao Prompt">
                    <i class="material-icons">add_circle</i>
                </button>
                <button class="card-action-btn" data-action="select" title="Selecionar">
                    <i class="material-icons">check_box_outline_blank</i>
                </button>
            </div>
            <div class="avatar-icon-display">
                <i class="material-icons">${typeIcons[avatar.type] || 'person'}</i>
            </div>
            <div class="avatar-card-info">
                <div class="avatar-card-name">${this.escapeHtml(avatar.name)}</div>
                <div class="avatar-card-type">${this.capitalize(avatar.type)}</div>
            </div>
        `;
        
        // Event listeners
        card.addEventListener('click', (e) => {
            if (!e.target.closest('.avatar-card-actions')) {
                this.showAvatarDetails(avatar);
            }
        });
        
        // Action buttons
        const addToPromptBtn = card.querySelector('[data-action="add-to-prompt"]');
        addToPromptBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.addAvatarToPrompt(avatar.id);
        });
        
        const selectBtn = card.querySelector('[data-action="select"]');
        selectBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleAvatarSelection(avatar.id);
        });
        
        return card;
    }
    
    // ===== DYNAMIC FIELDS =====
    setupDynamicFields() {
        const typeSelect = document.getElementById('avatar-type');
        if (typeSelect) {
            typeSelect.addEventListener('change', this.handleTypeChange.bind(this));
        }
    }
    
    handleTypeChange(e) {
        const type = e.target.value;
        const container = document.getElementById('dynamic-fields');
        
        if (!container) return;
        
        container.innerHTML = '';
        
        if (!type) return;
        
        const fields = this.getFieldsForType(type);
        
        fields.forEach(field => {
            const fieldElement = this.createFormField(field);
            container.appendChild(fieldElement);
        });
    }
    
    getFieldsForType(type) {
        const fieldsByType = {
            humano: [
                { name: 'profissao', label: 'Profissão', type: 'text', placeholder: 'Ex: Médica, Engenheiro' },
                { name: 'personalidade', label: 'Personalidade', type: 'text', placeholder: 'Ex: Carismático, Tímido' },
                { name: 'vestuario', label: 'Vestuário', type: 'text', placeholder: 'Ex: Terno elegante, Roupas casuais' }
            ],
            animal: [
                { name: 'especie', label: 'Espécie', type: 'text', placeholder: 'Ex: Canis lupus, Felis catus' },
                { name: 'habitat', label: 'Habitat', type: 'text', placeholder: 'Ex: Floresta, Savana' },
                { name: 'comportamento', label: 'Comportamento', type: 'text', placeholder: 'Ex: Agressivo, Dócil' }
            ],
            fantastico: [
                { name: 'elemento', label: 'Elemento', type: 'select', options: ['Fogo', 'Água', 'Terra', 'Ar', 'Luz', 'Trevas'] },
                { name: 'poder', label: 'Poder Principal', type: 'text', placeholder: 'Ex: Sopro de fogo, Telepatia' },
                { name: 'origem', label: 'Origem', type: 'text', placeholder: 'Ex: Reino Élfico, Dimensão Sombria' }
            ],
            extraterrestre: [
                { name: 'planeta', label: 'Planeta de Origem', type: 'text', placeholder: 'Ex: Kepler-442b, Proxima Centauri' },
                { name: 'tecnologia', label: 'Tecnologia', type: 'text', placeholder: 'Ex: Propulsão quântica, Teletransporte' },
                { name: 'missao', label: 'Missão', type: 'text', placeholder: 'Ex: Exploração, Conquista' }
            ],
            robotico: [
                { name: 'processador', label: 'Processador', type: 'text', placeholder: 'Ex: Quantum Core X1, Neural Matrix' },
                { name: 'funcoes', label: 'Funções Principais', type: 'text', placeholder: 'Ex: Assistência, Combate' },
                { name: 'emocoes', label: 'Sistema Emocional', type: 'select', options: ['Desativado', 'Básico', 'Avançado', 'Humanoide'] }
            ]
        };
        
        return fieldsByType[type] || [];
    }
    
    createFormField(field) {
        const wrapper = document.createElement('div');
        wrapper.className = 'form-group';
        
        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = field.label;
        
        let input;
        
        if (field.type === 'select') {
            input = document.createElement('select');
            input.className = 'form-select';
            
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Selecione...';
            input.appendChild(defaultOption);
            
            field.options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.toLowerCase();
                optionElement.textContent = option;
                input.appendChild(optionElement);
            });
        } else {
            input = document.createElement('input');
            input.type = field.type;
            input.className = 'form-input';
            input.placeholder = field.placeholder || '';
        }
        
        input.name = field.name;
        input.id = `field-${field.name}`;
        
        wrapper.appendChild(label);
        wrapper.appendChild(input);
        
        return wrapper;
    }
    
    // ===== AVATAR CREATION =====
    handleCreateAvatar(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const avatarData = this.collectFormData(formData);
        
        // Validation
        if (!avatarData.name || !avatarData.type) {
            this.showNotification('Nome e tipo são obrigatórios', 'error');
            return;
        }
        
        // Create avatar
        const newAvatar = this.createNewAvatar(avatarData);
        this.avatars.unshift(newAvatar);
        
        // Update UI
        this.renderAvatars();
        this.updateStats();
        this.clearForm();
        
        this.showNotification('Avatar criado com sucesso!', 'success');
    }
    
    collectFormData(formData) {
        const data = {
            name: formData.get('name'),
            type: formData.get('type'),
            gender: formData.get('gender'),
            age: formData.get('age'),
            description: formData.get('description'),
            tags: formData.get('tags')?.split(',').map(t => t.trim()).filter(t => t) || [],
            visibility: formData.get('visibility'),
            characteristics: {}
        };
        
        // Collect dynamic fields
        const dynamicFields = document.querySelectorAll('#dynamic-fields input, #dynamic-fields select');
        dynamicFields.forEach(field => {
            if (field.value) {
                data.characteristics[field.name] = field.value;
            }
        });
        
        return data;
    }
    
    createNewAvatar(data) {
        return {
            id: Date.now(),
            name: data.name,
            type: data.type,
            gender: data.gender,
            age: data.age,
            description: data.description,
            tags: data.tags,
            visibility: data.visibility,
            favorite: false,
            created: new Date().toISOString(),
            lastUsed: null,
            characteristics: data.characteristics
        };
    }
    
    clearForm() {
        const form = document.getElementById('avatar-creation-form');
        if (form) {
            form.reset();
            document.getElementById('dynamic-fields').innerHTML = '';
        }
    }
    
    // ===== AVATAR DETAILS =====
    showAvatarDetails(avatar) {
        const detailsPanel = document.getElementById('avatar-details');
        if (!detailsPanel) return;
        
        this.currentAvatar = avatar;
        
        // Populate details
        document.getElementById('avatar-name-display').textContent = avatar.name;
        document.getElementById('avatar-type-badge').textContent = this.capitalize(avatar.type);
        document.getElementById('created-date').textContent = this.formatDate(avatar.created);
        document.getElementById('last-used').textContent = avatar.lastUsed ? this.formatDate(avatar.lastUsed) : 'Nunca';
        document.getElementById('avatar-status').textContent = avatar.visibility === 'publico' ? 'Público' : 'Privado';
        document.getElementById('avatar-description-display').textContent = avatar.description;
        
        // Update favorite button
        const favoriteBtn = document.getElementById('toggle-favorite');
        if (favoriteBtn) {
            const icon = favoriteBtn.querySelector('i');
            icon.textContent = avatar.favorite ? 'star' : 'star_border';
        }
        
        // Update tags
        const tagsContainer = document.getElementById('tags-display');
        if (tagsContainer) {
            tagsContainer.innerHTML = avatar.tags.map(tag => 
                `<span class="tag-item">${this.escapeHtml(tag)}</span>`
            ).join('');
        }
        
        // Update avatar icon
        const avatarImage = document.getElementById('avatar-image');
        if (avatarImage) {
            const typeIcons = {
                humano: 'person',
                animal: 'pets',
                fantastico: 'auto_fix_high',
                extraterrestre: 'rocket_launch',
                robotico: 'smart_toy'
            };
            avatarImage.innerHTML = `<i class="material-icons">${typeIcons[avatar.type] || 'person'}</i>`;
        }
        
        // Show panel
        detailsPanel.classList.add('active');
    }
    
    hideDetails() {
        const detailsPanel = document.getElementById('avatar-details');
        if (detailsPanel) {
            detailsPanel.classList.remove('active');
        }
        this.currentAvatar = null;
    }
    
    // ===== AVATAR ACTIONS =====
    toggleCurrentFavorite() {
        if (!this.currentAvatar) return;
        
        this.currentAvatar.favorite = !this.currentAvatar.favorite;
        
        // Update avatar in array
        const index = this.avatars.findIndex(a => a.id === this.currentAvatar.id);
        if (index !== -1) {
            this.avatars[index] = this.currentAvatar;
        }
        
        // Update UI
        this.renderAvatars();
        this.updateStats();
        this.showAvatarDetails(this.currentAvatar); // Refresh details
        
        this.showNotification(
            this.currentAvatar.favorite ? 'Adicionado aos favoritos' : 'Removido dos favoritos',
            'success'
        );
    }
    
    duplicateCurrentAvatar() {
        if (!this.currentAvatar) return;
        
        const duplicate = {
            ...this.currentAvatar,
            id: Date.now(),
            name: `${this.currentAvatar.name} (Cópia)`,
            created: new Date().toISOString(),
            lastUsed: null,
            favorite: false
        };
        
        this.avatars.unshift(duplicate);
        this.renderAvatars();
        this.updateStats();
        
        this.showNotification('Avatar duplicado com sucesso!', 'success');
    }
    
    generateCurrentPrompt() {
        if (!this.currentAvatar) return;
        
        const prompt = this.generatePromptForAvatar(this.currentAvatar);
        
        const promptDisplay = document.querySelector('.prompt-text');
        if (promptDisplay) {
            promptDisplay.textContent = prompt;
        }
        
        this.showNotification('Prompt gerado!', 'success');
    }
    
    generatePromptForAvatar(avatar) {
        const parts = [avatar.name];
        
        if (avatar.gender && avatar.gender !== 'neutro') {
            parts.push(avatar.gender);
        }
        
        if (avatar.age && avatar.age !== 'adulto') {
            parts.push(avatar.age);
        }
        
        parts.push(avatar.type);
        
        if (avatar.description) {
            parts.push(avatar.description);
        }
        
        // Add characteristics
        Object.entries(avatar.characteristics || {}).forEach(([key, value]) => {
            if (value) {
                parts.push(`${key}: ${value}`);
            }
        });
        
        if (avatar.tags.length > 0) {
            parts.push(`tags: ${avatar.tags.join(', ')}`);
        }
        
        return parts.join(', ');
    }
    
    copyCurrentPrompt() {
        const promptText = document.querySelector('.prompt-text');
        if (promptText && promptText.textContent) {
            navigator.clipboard.writeText(promptText.textContent).then(() => {
                this.showNotification('Prompt copiado!', 'success');
            }).catch(() => {
                this.showNotification('Erro ao copiar prompt', 'error');
            });
        }
    }
    
    editCurrentAvatar() {
        if (!this.currentAvatar) return;
        
        // Populate form with current avatar data
        this.populateFormWithAvatar(this.currentAvatar);
        this.hideDetails();
        
        this.showNotification('Avatar carregado no formulário para edição', 'info');
    }
    
    populateFormWithAvatar(avatar) {
        const form = document.getElementById('avatar-creation-form');
        if (!form) return;
        
        // Basic fields
        form.name.value = avatar.name;
        form.type.value = avatar.type;
        form.gender.value = avatar.gender;
        form.age.value = avatar.age;
        form.description.value = avatar.description;
        form.tags.value = avatar.tags.join(', ');
        form.visibility.value = avatar.visibility;
        
        // Trigger type change to load dynamic fields
        this.handleTypeChange({ target: { value: avatar.type } });
        
        // Populate dynamic fields
        setTimeout(() => {
            Object.entries(avatar.characteristics || {}).forEach(([key, value]) => {
                const field = document.querySelector(`[name="${key}"]`);
                if (field) {
                    field.value = value;
                }
            });
        }, 100);
    }
    
    deleteCurrentAvatar() {
        if (!this.currentAvatar) return;
        
        if (confirm(`Tem certeza que deseja excluir "${this.currentAvatar.name}"?`)) {
            const index = this.avatars.findIndex(a => a.id === this.currentAvatar.id);
            if (index !== -1) {
                this.avatars.splice(index, 1);
                this.renderAvatars();
                this.updateStats();
                this.hideDetails();
                
                this.showNotification('Avatar excluído com sucesso!', 'success');
            }
        }
    }
    
    // ===== PROMPT INTEGRATION =====
    addCurrentToPrompt() {
        if (!this.currentAvatar) {
            this.showNotification('Nenhum avatar selecionado', 'error');
            return;
        }
        
        // Check if prompt avatars manager is available
        if (window.promptAvatarsManager) {
            const success = window.promptAvatarsManager.addAvatar(this.currentAvatar);
            if (success) {
                // Update avatar's last used time
                this.currentAvatar.lastUsed = new Date().toISOString();
                
                // Update avatar in the list
                const index = this.avatars.findIndex(a => a.id === this.currentAvatar.id);
                if (index !== -1) {
                    this.avatars[index] = this.currentAvatar;
                }
                
                this.renderAvatars();
                this.showAvatarDetails(this.currentAvatar); // Refresh details
            }
        } else {
            // Fallback for when prompt manager is not available
            this.showNotification('Sistema de prompt não encontrado', 'error');
            console.warn('promptAvatarsManager not found. Make sure to include prompt-avatars-manager.js');
        }
    }
    
    addAvatarToPrompt(avatarId) {
        const avatar = this.avatars.find(a => a.id === avatarId);
        if (!avatar) {
            this.showNotification('Avatar não encontrado', 'error');
            return;
        }
        
        if (window.promptAvatarsManager) {
            const success = window.promptAvatarsManager.addAvatar(avatar);
            if (success) {
                // Update avatar's last used time
                avatar.lastUsed = new Date().toISOString();
                this.renderAvatars();
                
                // If this avatar is currently shown in details, refresh
                if (this.currentAvatar && this.currentAvatar.id === avatarId) {
                    this.showAvatarDetails(avatar);
                }
            }
        } else {
            this.showNotification('Sistema de prompt não encontrado', 'error');
        }
    }
    
    // Check if an avatar is in the current prompt
    isAvatarInPrompt(avatarId) {
        if (window.promptAvatarsManager) {
            return window.promptAvatarsManager.hasAvatar(avatarId);
        }
        return false;
    }
    
    // Get all avatars currently in prompt
    getPromptAvatars() {
        if (window.promptAvatarsManager) {
            return window.promptAvatarsManager.getAvatarIds();
        }
        return [];
    }
    
    // ===== SELECTION =====
    toggleAvatarSelection(avatarId) {
        const index = this.selectedAvatars.indexOf(avatarId);
        
        if (index === -1) {
            this.selectedAvatars.push(avatarId);
        } else {
            this.selectedAvatars.splice(index, 1);
        }
        
        this.updateSelectionUI();
        this.updateBulkActions();
    }
    
    updateSelectionUI() {
        const cards = document.querySelectorAll('.avatar-card');
        cards.forEach(card => {
            const id = parseInt(card.dataset.id);
            const isSelected = this.selectedAvatars.includes(id);
            
            card.classList.toggle('selected', isSelected);
            
            const selectBtn = card.querySelector('[data-action="select"] i');
            if (selectBtn) {
                selectBtn.textContent = isSelected ? 'check_box' : 'check_box_outline_blank';
            }
        });
    }
    
    updateBulkActions() {
        const bulkActions = document.getElementById('bulk-actions');
        if (!bulkActions) return;
        
        if (this.selectedAvatars.length > 0) {
            bulkActions.style.display = 'flex';
            bulkActions.querySelector('.selected-count').textContent = `${this.selectedAvatars.length} selecionados`;
        } else {
            bulkActions.style.display = 'none';
        }
    }
    
    // ===== UTILITIES =====
    updateStats() {
        const totalAvatars = this.avatars.length;
        const publicAvatars = this.avatars.filter(a => a.visibility === 'publico').length;
        const favoriteAvatars = this.avatars.filter(a => a.favorite).length;
        
        document.getElementById('total-avatars').textContent = totalAvatars;
        document.getElementById('public-avatars').textContent = publicAvatars;
        document.getElementById('favorite-avatars').textContent = favoriteAvatars;
    }
    
    updateResultsInfo(filtered, total) {
        document.getElementById('filtered-count').textContent = filtered;
        document.getElementById('total-count').textContent = total;
    }
    
    refreshAvatars() {
        this.showNotification('Atualizando avatares...', 'info');
        
        // Simulate refresh
        setTimeout(() => {
            this.renderAvatars();
            this.updateStats();
            this.showNotification('Avatares atualizados!', 'success');
        }, 500);
    }
    
    showNotification(message, type = 'info') {
        // Simple notification - can be enhanced with a proper toast system
        const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
        console.log(`${icon} ${message}`);
        
        // You can implement a more sophisticated notification system here
        if (type === 'error') {
            alert(message);
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    capitalize(text) {
        return text.charAt(0).toUpperCase() + text.slice(1);
    }
    
    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.avatarManagerModern = new AvatarManagerModern();
});