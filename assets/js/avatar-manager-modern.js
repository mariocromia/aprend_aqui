/**
 * GERENCIADOR MODERNO DE AVATARES V2
 * Sistema redesenhado com interface compacta e funcionalidades aprimoradas
 */

class AvatarManagerModern {
    constructor() {
        this.avatars = [];
        this.selectedAvatars = [];
        this.currentView = 'grid';
        this.currentAvatar = null;
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
        console.log('🎨 Avatar Manager Modern V2 inicializado');
    }
    
    // ===== EVENT BINDING =====
    bindEvents() {
        // Header controls
        this.bindHeaderControls();
        
        // Filter and search
        this.bindFilterControls();
        
        // Modal controls
        this.bindModalControls();
        
        // Avatar interactions
        this.bindAvatarControls();
        
        // Details panel
        this.bindDetailsControls();
    }
    
    bindHeaderControls() {
        // View toggle
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const target = e.target.closest('.view-btn');
                if (target && target.dataset.view) {
                    this.setView(target.dataset.view);
                }
            });
        });
        
        // Main search
        const mainSearch = document.getElementById('avatar-search-main');
        if (mainSearch) {
            mainSearch.addEventListener('input', this.handleMainSearch.bind(this));
        }
        
        // Create button
        const createBtn = document.getElementById('toggle-create-form');
        if (createBtn) {
            createBtn.addEventListener('click', this.showCreateModal.bind(this));
        }
        
        // Create first avatar
        const createFirstBtn = document.getElementById('create-first-avatar');
        if (createFirstBtn) {
            createFirstBtn.addEventListener('click', this.showCreateModal.bind(this));
        }
    }
    
    bindFilterControls() {
        // Filter toggle
        const filterToggle = document.getElementById('toggle-filters');
        if (filterToggle) {
            filterToggle.addEventListener('click', this.toggleFilters.bind(this));
        }
        
        // Close filters
        const closeFilters = document.getElementById('close-filters');
        if (closeFilters) {
            closeFilters.addEventListener('click', this.hideFilters.bind(this));
        }
        
        // Filter chips
        document.querySelectorAll('.filter-chip').forEach(chip => {
            chip.addEventListener('click', this.handleFilterChip.bind(this));
        });
        
        // Sort select
        const sortSelect = document.getElementById('sort-by-modern');
        if (sortSelect) {
            sortSelect.addEventListener('change', this.handleSortChange.bind(this));
        }
        
        // Refresh button
        const refreshBtn = document.getElementById('refresh-avatars');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', this.refreshAvatars.bind(this));
        }
    }
    
    bindModalControls() {
        // Close modal buttons
        document.querySelectorAll('.close-modal, .btn-cancel').forEach(btn => {
            btn.addEventListener('click', this.hideCreateModal.bind(this));
        });
        
        // Visibility toggle
        document.querySelectorAll('.visibility-btn').forEach(btn => {
            btn.addEventListener('click', this.handleVisibilityToggle.bind(this));
        });
        
        // Form submission
        const form = document.getElementById('avatar-creation-form-modern');
        if (form) {
            form.addEventListener('submit', this.handleCreateAvatar.bind(this));
        }
        
        // Clear form
        const clearBtn = document.getElementById('clear-form');
        if (clearBtn) {
            clearBtn.addEventListener('click', this.clearForm.bind(this));
        }
        
        // Avatar type change for dynamic fields
        const typeSelect = document.getElementById('avatar-type');
        if (typeSelect) {
            typeSelect.addEventListener('change', this.handleTypeChange.bind(this));
        }
    }
    
    bindAvatarControls() {
        // This will be called after rendering avatars
        this.bindAvatarCards();
    }
    
    bindDetailsControls() {
        // Close details
        const closeDetails = document.getElementById('close-details');
        if (closeDetails) {
            closeDetails.addEventListener('click', this.hideDetails.bind(this));
        }
        
        // Quick actions
        const addToPromptQuick = document.getElementById('add-to-prompt-quick');
        if (addToPromptQuick) {
            addToPromptQuick.addEventListener('click', this.addCurrentToPrompt.bind(this));
        }
        
        const toggleFavorite = document.getElementById('toggle-favorite');
        if (toggleFavorite) {
            toggleFavorite.addEventListener('click', this.toggleCurrentFavorite.bind(this));
        }
        
        const duplicateAvatar = document.getElementById('duplicate-avatar');
        if (duplicateAvatar) {
            duplicateAvatar.addEventListener('click', this.duplicateCurrentAvatar.bind(this));
        }
        
        const shareAvatar = document.getElementById('share-avatar');
        if (shareAvatar) {
            shareAvatar.addEventListener('click', this.shareCurrentAvatar.bind(this));
        }
        
        // Main actions
        const addToPrompt = document.getElementById('add-to-prompt');
        if (addToPrompt) {
            addToPrompt.addEventListener('click', this.addCurrentToPrompt.bind(this));
        }
        
        const editAvatar = document.getElementById('edit-avatar');
        if (editAvatar) {
            editAvatar.addEventListener('click', this.editCurrentAvatar.bind(this));
        }
        
        const deleteAvatar = document.getElementById('delete-avatar');
        if (deleteAvatar) {
            deleteAvatar.addEventListener('click', this.deleteCurrentAvatar.bind(this));
        }
        
        // Prompt actions
        const generatePrompt = document.getElementById('generate-prompt');
        if (generatePrompt) {
            generatePrompt.addEventListener('click', this.generateCurrentPrompt.bind(this));
        }
        
        const copyPrompt = document.getElementById('copy-prompt');
        if (copyPrompt) {
            copyPrompt.addEventListener('click', this.copyCurrentPrompt.bind(this));
        }
    }
    
    // ===== HEADER CONTROLS =====
    setView(view) {
        this.currentView = view;
        
        // Update active button
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.view === view);
        });
        
        // Update grid class
        const grid = document.getElementById('avatars-grid');
        if (grid) {
            grid.className = view === 'list' ? 'avatars-grid-modern list-view' : 'avatars-grid-modern';
        }
        
        console.log(`📋 Vista alterada para: ${view}`);
    }
    
    handleMainSearch(e) {
        this.filters.search = e.target.value.toLowerCase();
        this.debounceSearch();
    }
    
    debounceSearch() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.renderAvatars();
            console.log(`🔍 Busca: "${this.filters.search}"`);
        }, 300);
    }
    
    // ===== FILTER CONTROLS =====
    toggleFilters() {
        const filtersPanel = document.getElementById('filters-panel');
        if (filtersPanel) {
            filtersPanel.classList.toggle('active');
        }
    }
    
    hideFilters() {
        const filtersPanel = document.getElementById('filters-panel');
        if (filtersPanel) {
            filtersPanel.classList.remove('active');
        }
    }
    
    handleFilterChip(e) {
        const chip = e.target.closest('.filter-chip');
        if (!chip) return;
        
        const category = chip.dataset.category;
        const status = chip.dataset.status;
        
        if (category !== undefined) {
            // Category filter
            document.querySelectorAll('.filter-chip[data-category]').forEach(c => {
                c.classList.remove('active');
            });
            chip.classList.add('active');
            this.filters.category = category;
        } else if (status !== undefined) {
            // Status filter (toggle)
            chip.classList.toggle('active');
            if (chip.classList.contains('active')) {
                if (!this.filters.status.includes(status)) {
                    this.filters.status.push(status);
                }
            } else {
                this.filters.status = this.filters.status.filter(s => s !== status);
            }
        }
        
        this.renderAvatars();
    }
    
    handleSortChange(e) {
        this.filters.sortBy = e.target.value;
        this.renderAvatars();
    }
    
    // ===== MODAL CONTROLS =====
    showCreateModal() {
        const modal = document.getElementById('creation-modal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    hideCreateModal() {
        const modal = document.getElementById('creation-modal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
    
    handleVisibilityToggle(e) {
        const btn = e.target.closest('.visibility-btn');
        if (!btn) return;
        
        document.querySelectorAll('.visibility-btn').forEach(b => {
            b.classList.remove('active');
        });
        btn.classList.add('active');
        
        const hiddenInput = document.getElementById('avatar-visibility');
        if (hiddenInput) {
            hiddenInput.value = btn.dataset.value;
        }
    }
    
    handleCreateAvatar(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const avatarData = {
            id: Date.now(),
            name: formData.get('name'),
            type: formData.get('type'),
            gender: formData.get('gender'),
            age: parseInt(formData.get('age')) || 25,
            description: formData.get('description'),
            tags: formData.get('tags')?.split(',').map(t => t.trim()).filter(t => t) || [],
            visibility: formData.get('visibility'),
            favorite: false,
            created: new Date().toISOString(),
            lastUsed: null
        };
        
        this.avatars.unshift(avatarData);
        this.updateStats();
        this.renderAvatars();
        this.hideCreateModal();
        this.clearForm();
        
        console.log('✅ Avatar criado:', avatarData.name);
        this.showNotification(`Avatar "${avatarData.name}" criado com sucesso!`, 'success');
    }
    
    clearForm() {
        const form = document.getElementById('avatar-creation-form-modern');
        if (form) {
            form.reset();
            
            // Reset visibility toggle
            document.querySelectorAll('.visibility-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.value === 'privado');
            });
            
            const hiddenInput = document.getElementById('avatar-visibility');
            if (hiddenInput) {
                hiddenInput.value = 'privado';
            }
        }
    }
    
    handleTypeChange(e) {
        const type = e.target.value;
        this.updateDynamicFields(type);
    }
    
    updateDynamicFields(type) {
        const container = document.getElementById('dynamic-fields-modern');
        if (!container) return;
        
        container.innerHTML = '';
        
        if (!type) return;
        
        const fields = this.getDynamicFieldsForType(type);
        fields.forEach(field => {
            const fieldHtml = this.createDynamicField(field);
            container.insertAdjacentHTML('beforeend', fieldHtml);
        });
    }
    
    getDynamicFieldsForType(type) {
        const fieldsByType = {
            humano: [
                { name: 'cor_pele', label: 'Cor da Pele', type: 'select', options: ['clara', 'média', 'escura'] },
                { name: 'altura', label: 'Altura', type: 'text', placeholder: 'Ex: 1.75m' },
                { name: 'peso', label: 'Peso', type: 'text', placeholder: 'Ex: 70kg' },
                { name: 'cor_cabelo', label: 'Cor do Cabelo', type: 'select', options: ['loiro', 'castanho', 'preto', 'ruivo', 'grisalho'] }
            ],
            animal: [
                { name: 'especie', label: 'Espécie', type: 'text', placeholder: 'Ex: Gato, Cão, Lobo' },
                { name: 'cor_pelo', label: 'Cor do Pelo', type: 'text', placeholder: 'Ex: Marrom, Preto' },
                { name: 'tamanho', label: 'Tamanho', type: 'select', options: ['pequeno', 'médio', 'grande'] }
            ],
            fantastico: [
                { name: 'criatura', label: 'Tipo de Criatura', type: 'text', placeholder: 'Ex: Elfo, Anão, Dragão' },
                { name: 'poderes', label: 'Poderes', type: 'text', placeholder: 'Ex: Magia, Força' },
                { name: 'mundo_origem', label: 'Mundo de Origem', type: 'text', placeholder: 'Ex: Terra Média' }
            ],
            extraterrestre: [
                { name: 'planeta_origem', label: 'Planeta de Origem', type: 'text', placeholder: 'Ex: Vulcano, Krypton' },
                { name: 'tecnologia', label: 'Nível Tecnológico', type: 'select', options: ['primitivo', 'avançado', 'super-avançado'] },
                { name: 'aparencia', label: 'Aparência', type: 'text', placeholder: 'Descreva características únicas' }
            ],
            robotico: [
                { name: 'modelo', label: 'Modelo/Versão', type: 'text', placeholder: 'Ex: T-800, HAL 9000' },
                { name: 'funcao_primaria', label: 'Função Primária', type: 'text', placeholder: 'Ex: Assistente, Combate' },
                { name: 'nivel_ia', label: 'Nível de IA', type: 'select', options: ['básico', 'intermediário', 'avançado', 'senciente'] }
            ]
        };
        
        return fieldsByType[type] || [];
    }
    
    createDynamicField(field) {
        if (field.type === 'select') {
            const options = field.options.map(opt => `<option value="${opt}">${opt}</option>`).join('');
            return `
                <div class="form-field-modern">
                    <label class="field-label">${field.label}</label>
                    <div class="select-wrapper">
                        <select name="${field.name}" class="select-modern">
                            <option value="">Selecione...</option>
                            ${options}
                        </select>
                    </div>
                </div>
            `;
        } else {
            return `
                <div class="form-field-modern">
                    <label class="field-label">${field.label}</label>
                    <div class="input-wrapper">
                        <input type="text" name="${field.name}" placeholder="${field.placeholder}" class="input-modern">
                    </div>
                </div>
            `;
        }
    }
    
    // ===== AVATAR RENDERING =====
    renderAvatars() {
        const container = document.getElementById('avatars-grid');
        const loadingState = document.getElementById('loading-state');
        const emptyState = document.getElementById('empty-state');
        
        if (!container) return;
        
        // Show loading
        if (loadingState) loadingState.style.display = 'flex';
        if (emptyState) emptyState.style.display = 'none';
        container.innerHTML = '';
        
        setTimeout(() => {
            const filteredAvatars = this.getFilteredAvatars();
            
            if (loadingState) loadingState.style.display = 'none';
            
            if (filteredAvatars.length === 0) {
                if (emptyState) emptyState.style.display = 'flex';
                return;
            }
            
            filteredAvatars.forEach(avatar => {
                const avatarCard = this.createAvatarCard(avatar);
                container.insertAdjacentHTML('beforeend', avatarCard);
            });
            
            this.bindAvatarCards();
            this.updateCounts(filteredAvatars.length);
        }, 500);
    }
    
    getFilteredAvatars() {
        let filtered = [...this.avatars];
        
        // Search filter
        if (this.filters.search) {
            filtered = filtered.filter(avatar => 
                avatar.name.toLowerCase().includes(this.filters.search) ||
                avatar.description.toLowerCase().includes(this.filters.search) ||
                avatar.tags.some(tag => tag.toLowerCase().includes(this.filters.search))
            );
        }
        
        // Category filter
        if (this.filters.category) {
            filtered = filtered.filter(avatar => avatar.type === this.filters.category);
        }
        
        // Status filter
        if (this.filters.status.length > 0) {
            filtered = filtered.filter(avatar => {
                return this.filters.status.some(status => {
                    switch(status) {
                        case 'meus': return avatar.visibility === 'privado';
                        case 'publicos': return avatar.visibility === 'publico';
                        case 'favoritos': return avatar.favorite;
                        default: return false;
                    }
                });
            });
        }
        
        // Sort
        filtered.sort((a, b) => {
            switch(this.filters.sortBy) {
                case 'name': return a.name.localeCompare(b.name);
                case 'type': return a.type.localeCompare(b.type);
                case 'used': return new Date(b.lastUsed || 0) - new Date(a.lastUsed || 0);
                case 'created':
                default: return new Date(b.created) - new Date(a.created);
            }
        });
        
        return filtered;
    }
    
    createAvatarCard(avatar) {
        const typeIcons = {
            humano: 'person',
            animal: 'pets',
            fantastico: 'auto_awesome',
            extraterrestre: 'rocket_launch',
            robotico: 'smart_toy'
        };
        
        const icon = typeIcons[avatar.type] || 'person';
        const favoriteClass = avatar.favorite ? 'favorite' : '';
        
        return `
            <div class="avatar-card-modern ${favoriteClass}" data-avatar-id="${avatar.id}">
                <div class="avatar-icon-display">
                    <i class="material-icons">${icon}</i>
                </div>
                <div class="avatar-card-info">
                    <div class="avatar-card-name">${avatar.name}</div>
                    <div class="avatar-card-type">${avatar.type}</div>
                </div>
                ${avatar.favorite ? '<div class="favorite-indicator">⭐</div>' : ''}
            </div>
        `;
    }
    
    bindAvatarCards() {
        document.querySelectorAll('.avatar-card-modern').forEach(card => {
            card.addEventListener('click', (e) => {
                const avatarId = parseInt(card.dataset.avatarId);
                const avatar = this.avatars.find(a => a.id === avatarId);
                if (avatar) {
                    this.showAvatarDetails(avatar);
                }
            });
        });
    }
    
    // ===== DETAILS PANEL =====
    showAvatarDetails(avatar) {
        this.currentAvatar = avatar;
        const panel = document.getElementById('details-panel');
        if (!panel) return;
        
        this.updateDetailsPanel(avatar);
        panel.classList.add('active');
        
        console.log('📋 Detalhes do avatar:', avatar.name);
    }
    
    hideDetails() {
        const panel = document.getElementById('details-panel');
        if (panel) {
            panel.classList.remove('active');
        }
        this.currentAvatar = null;
    }
    
    updateDetailsPanel(avatar) {
        // Update subtitle
        const subtitle = document.getElementById('details-subtitle');
        if (subtitle) {
            subtitle.textContent = avatar.name;
        }
        
        // Update avatar name
        const nameDisplay = document.getElementById('avatar-name-display');
        if (nameDisplay) {
            nameDisplay.textContent = avatar.name;
        }
        
        // Update type badge
        const typeBadge = document.getElementById('avatar-type-badge');
        if (typeBadge) {
            typeBadge.textContent = avatar.type;
        }
        
        // Update status badge
        const statusBadge = document.getElementById('avatar-status-badge');
        if (statusBadge) {
            statusBadge.textContent = avatar.visibility;
        }
        
        // Update description
        const description = document.getElementById('avatar-description-display');
        if (description) {
            description.textContent = avatar.description || 'Sem descrição';
        }
        
        // Update metadata
        const createdDate = document.getElementById('created-date');
        if (createdDate) {
            createdDate.textContent = new Date(avatar.created).toLocaleDateString('pt-BR');
        }
        
        const lastUsed = document.getElementById('last-used');
        if (lastUsed) {
            lastUsed.textContent = avatar.lastUsed ? 
                new Date(avatar.lastUsed).toLocaleDateString('pt-BR') : 'Nunca';
        }
        
        // Update favorite button
        const favoriteBtn = document.getElementById('toggle-favorite');
        if (favoriteBtn) {
            const icon = favoriteBtn.querySelector('i');
            if (icon) {
                icon.textContent = avatar.favorite ? 'star' : 'star_border';
            }
        }
        
        // Update tags
        this.updateTagsDisplay(avatar.tags);
    }
    
    updateTagsDisplay(tags) {
        const container = document.getElementById('tags-display');
        if (!container) return;
        
        container.innerHTML = '';
        
        if (!tags || tags.length === 0) {
            container.innerHTML = '<span class="tag-modern">Sem tags</span>';
            return;
        }
        
        tags.forEach(tag => {
            const tagElement = document.createElement('span');
            tagElement.className = 'tag-modern';
            tagElement.textContent = tag;
            container.appendChild(tagElement);
        });
    }
    
    // ===== AVATAR ACTIONS =====
    addCurrentToPrompt() {
        if (!this.currentAvatar) return;
        
        const prompt = this.generatePromptText(this.currentAvatar);
        console.log('➕ Adicionando ao prompt:', this.currentAvatar.name);
        console.log('📝 Prompt gerado:', prompt);
        
        // Update last used
        this.currentAvatar.lastUsed = new Date().toISOString();
        this.updateDetailsPanel(this.currentAvatar);
        
        this.showNotification(`Avatar "${this.currentAvatar.name}" adicionado ao prompt!`, 'success');
    }
    
    toggleCurrentFavorite() {
        if (!this.currentAvatar) return;
        
        this.currentAvatar.favorite = !this.currentAvatar.favorite;
        this.updateDetailsPanel(this.currentAvatar);
        this.updateStats();
        this.renderAvatars();
        
        const action = this.currentAvatar.favorite ? 'adicionado aos' : 'removido dos';
        this.showNotification(`Avatar ${action} favoritos!`, 'info');
    }
    
    duplicateCurrentAvatar() {
        if (!this.currentAvatar) return;
        
        const duplicate = {
            ...this.currentAvatar,
            id: Date.now(),
            name: `${this.currentAvatar.name} (Cópia)`,
            created: new Date().toISOString(),
            lastUsed: null
        };
        
        this.avatars.unshift(duplicate);
        this.updateStats();
        this.renderAvatars();
        
        this.showNotification('Avatar duplicado com sucesso!', 'success');
    }
    
    shareCurrentAvatar() {
        if (!this.currentAvatar) return;
        
        // Simulate sharing functionality
        const shareData = {
            title: `Avatar: ${this.currentAvatar.name}`,
            text: this.currentAvatar.description,
            url: window.location.href
        };
        
        if (navigator.share) {
            navigator.share(shareData);
        } else {
            // Fallback - copy to clipboard
            navigator.clipboard.writeText(JSON.stringify(this.currentAvatar, null, 2));
            this.showNotification('Dados do avatar copiados para a área de transferência!', 'info');
        }
    }
    
    editCurrentAvatar() {
        if (!this.currentAvatar) return;
        
        console.log('✏️ Editando avatar:', this.currentAvatar.name);
        this.showNotification('Funcionalidade de edição em desenvolvimento...', 'info');
    }
    
    deleteCurrentAvatar() {
        if (!this.currentAvatar) return;
        
        if (confirm(`Tem certeza que deseja excluir o avatar "${this.currentAvatar.name}"?`)) {
            const index = this.avatars.findIndex(a => a.id === this.currentAvatar.id);
            if (index !== -1) {
                this.avatars.splice(index, 1);
                this.updateStats();
                this.renderAvatars();
                this.hideDetails();
                
                this.showNotification('Avatar excluído com sucesso!', 'success');
            }
        }
    }
    
    generateCurrentPrompt() {
        if (!this.currentAvatar) return;
        
        const prompt = this.generatePromptText(this.currentAvatar);
        const promptDisplay = document.querySelector('.prompt-text-modern');
        if (promptDisplay) {
            promptDisplay.textContent = prompt;
        }
        
        console.log('🤖 Prompt gerado:', prompt);
        this.showNotification('Prompt gerado com sucesso!', 'success');
    }
    
    copyCurrentPrompt() {
        const promptText = document.querySelector('.prompt-text-modern');
        if (promptText && promptText.textContent) {
            navigator.clipboard.writeText(promptText.textContent);
            this.showNotification('Prompt copiado para a área de transferência!', 'success');
        } else {
            this.showNotification('Gere um prompt primeiro!', 'warning');
        }
    }
    
    generatePromptText(avatar) {
        const characteristics = [];
        
        characteristics.push(`Nome: ${avatar.name}`);
        characteristics.push(`Tipo: ${avatar.type}`);
        characteristics.push(`Gênero: ${avatar.gender}`);
        characteristics.push(`Idade: ${avatar.age} anos`);
        
        if (avatar.description) {
            characteristics.push(`Descrição: ${avatar.description}`);
        }
        
        if (avatar.tags && avatar.tags.length > 0) {
            characteristics.push(`Tags: ${avatar.tags.join(', ')}`);
        }
        
        return `Crie um personagem com as seguintes características:\n\n${characteristics.join('\n')}`;
    }
    
    // ===== UTILITY METHODS =====
    updateStats() {
        const total = this.avatars.length;
        const publicCount = this.avatars.filter(a => a.visibility === 'publico').length;
        const favoriteCount = this.avatars.filter(a => a.favorite).length;
        
        // Update header stats
        const totalEl = document.getElementById('total-avatars');
        if (totalEl) totalEl.textContent = total;
        
        const publicEl = document.getElementById('public-avatars');
        if (publicEl) publicEl.textContent = publicCount;
        
        const favoriteEl = document.getElementById('favorite-avatars');
        if (favoriteEl) favoriteEl.textContent = favoriteCount;
    }
    
    updateCounts(filteredCount) {
        const filteredEl = document.getElementById('filtered-count');
        if (filteredEl) filteredEl.textContent = filteredCount;
        
        const totalEl = document.getElementById('total-count');
        if (totalEl) totalEl.textContent = this.avatars.length;
    }
    
    refreshAvatars() {
        console.log('🔄 Atualizando avatares...');
        this.renderAvatars();
        this.showNotification('Lista de avatares atualizada!', 'info');
    }
    
    setupDynamicFields() {
        // Initialize dynamic fields for form
        this.updateDynamicFields('');
    }
    
    loadSampleData() {
        this.avatars = [
            {
                id: 1,
                name: 'Elena Rodriguez',
                type: 'humano',
                gender: 'feminino',
                age: 28,
                description: 'Jovem médica especialista em emergências médicas, sempre pronta para ajudar.',
                tags: ['médica', 'emergência', 'jovem', 'profissional'],
                visibility: 'privado',
                favorite: true,
                created: '2024-01-15T10:30:00Z',
                lastUsed: '2024-01-20T14:20:00Z'
            },
            {
                id: 2,
                name: 'Capitão Sparks',
                type: 'robotico',
                gender: 'neutro',
                age: 5,
                description: 'Robô militar avançado com personalidade desenvolvida e senso de humor.',
                tags: ['robô', 'militar', 'humor', 'tecnologia'],
                visibility: 'publico',
                favorite: false,
                created: '2024-01-10T08:15:00Z',
                lastUsed: null
            },
            {
                id: 3,
                name: 'Luna Silvermoon',
                type: 'fantastico',
                gender: 'feminino',
                age: 150,
                description: 'Elfa ancestral com poderes de cura e conexão profunda com a natureza.',
                tags: ['elfa', 'magia', 'natureza', 'cura'],
                visibility: 'privado',
                favorite: true,
                created: '2024-01-12T16:45:00Z',
                lastUsed: '2024-01-18T11:30:00Z'
            }
        ];
    }
    
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: ${type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
            color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            font-weight: 500;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Auto remove
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.avatarManagerModern = new AvatarManagerModern();
});

// Export for global access
window.AvatarManagerModern = AvatarManagerModern;