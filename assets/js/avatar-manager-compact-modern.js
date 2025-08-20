/**
 * AVATAR MANAGER COMPACTO E MODERNO
 * JavaScript para todas as funcionalidades do demo_avatar_modern.php
 * Design moderno, compacto e atraente
 */

class AvatarManagerCompactModern {
    constructor() {
        this.container = document.getElementById('avatar-manager-compact');
        this.avatars = [];
        this.filteredAvatars = [];
        this.selectedAvatars = new Set();
        this.currentView = 'grid';
        this.currentMode = 'creation'; // 'creation' ou 'details'
        this.selectedAvatar = null;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadAvatars();
        this.updateStats();
        this.setupDynamicFields();
        
        console.log('🎨 Avatar Manager Compact Modern inicializado!');
    }

    bindEvents() {
        // Busca rápida
        const quickSearch = document.getElementById('quick-search');
        if (quickSearch) {
            quickSearch.addEventListener('input', (e) => this.handleQuickSearch(e.target.value));
        }

        // Filtros avançados
        this.bindFilterEvents();

        // Controles de visualização
        this.bindViewControls();

        // Botões de ação
        this.bindActionButtons();

        // Formulário de criação
        this.bindCreationForm();

        // Painel lateral
        this.bindSidePanelEvents();

        // Ações em lote
        this.bindBulkActions();
    }

    bindFilterEvents() {
        // Filtro por categoria
        const categoryFilter = document.getElementById('filter-category');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', () => this.applyFilters());
        }

        // Checkboxes de status
        const statusCheckboxes = document.querySelectorAll('.status-checkboxes input[type="checkbox"]');
        statusCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => this.applyFilters());
        });

        // Ordenação
        const sortBy = document.getElementById('sort-by');
        if (sortBy) {
            sortBy.addEventListener('change', () => this.applyFilters());
        }

        // Toggle filtros avançados
        const filterToggle = document.getElementById('filter-toggle');
        if (filterToggle) {
            filterToggle.addEventListener('click', () => this.toggleAdvancedFilters());
        }
    }

    bindViewControls() {
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const view = e.currentTarget.dataset.view;
                this.switchView(view);
            });
        });
    }

    bindActionButtons() {
        // Botão criar avatar
        const btnCreate = document.getElementById('btn-create-avatar');
        if (btnCreate) {
            btnCreate.addEventListener('click', () => this.showCreationMode());
        }

        const btnCreateFirst = document.getElementById('create-first-avatar');
        if (btnCreateFirst) {
            btnCreateFirst.addEventListener('click', () => this.showCreationMode());
        }

        // Botão refresh
        const btnRefresh = document.getElementById('refresh-avatars');
        if (btnRefresh) {
            btnRefresh.addEventListener('click', () => this.refreshAvatars());
        }
    }

    bindCreationForm() {
        const form = document.getElementById('avatar-creation-form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        // Campo tipo - campos dinâmicos
        const typeSelect = document.getElementById('avatar-type');
        if (typeSelect) {
            typeSelect.addEventListener('change', (e) => this.updateDynamicFields(e.target.value));
        }

        // Botão limpar
        const btnClear = document.getElementById('clear-form');
        if (btnClear) {
            btnClear.addEventListener('click', () => this.clearForm());
        }
    }

    bindSidePanelEvents() {
        // Toggle do painel
        const panelToggle = document.getElementById('panel-toggle');
        if (panelToggle) {
            panelToggle.addEventListener('click', () => this.toggleSidePanel());
        }

        // Fechar criação
        const closeCreation = document.getElementById('close-creation');
        if (closeCreation) {
            closeCreation.addEventListener('click', () => this.closeSidePanel());
        }

        // Fechar detalhes
        const closeDetails = document.getElementById('close-details');
        if (closeDetails) {
            closeDetails.addEventListener('click', () => this.closeSidePanel());
        }

        // Ações do painel de detalhes
        this.bindDetailsActions();
    }

    bindDetailsActions() {
        // Favoritar
        const btnFavorite = document.getElementById('favorite-avatar');
        if (btnFavorite) {
            btnFavorite.addEventListener('click', () => this.toggleFavorite());
        }

        // Duplicar
        const btnDuplicate = document.getElementById('duplicate-avatar');
        if (btnDuplicate) {
            btnDuplicate.addEventListener('click', () => this.duplicateAvatar());
        }

        // Compartilhar
        const btnShare = document.getElementById('share-avatar');
        if (btnShare) {
            btnShare.addEventListener('click', () => this.shareAvatar());
        }

        // Gerar prompt
        const btnGeneratePrompt = document.getElementById('generate-prompt');
        if (btnGeneratePrompt) {
            btnGeneratePrompt.addEventListener('click', () => this.generatePrompt());
        }

        // Copiar prompt
        const btnCopyPrompt = document.getElementById('copy-prompt');
        if (btnCopyPrompt) {
            btnCopyPrompt.addEventListener('click', () => this.copyPrompt());
        }

        // Adicionar ao prompt
        const btnAddToPrompt = document.getElementById('add-to-prompt');
        if (btnAddToPrompt) {
            btnAddToPrompt.addEventListener('click', () => this.addToPrompt());
        }

        // Editar avatar
        const btnEdit = document.getElementById('edit-avatar');
        if (btnEdit) {
            btnEdit.addEventListener('click', () => this.editAvatar());
        }

        // Deletar avatar
        const btnDelete = document.getElementById('delete-avatar');
        if (btnDelete) {
            btnDelete.addEventListener('click', () => this.deleteAvatar());
        }
    }

    bindBulkActions() {
        // Ações em lote
        const btnBulkFavorite = document.getElementById('bulk-favorite');
        if (btnBulkFavorite) {
            btnBulkFavorite.addEventListener('click', () => this.bulkFavorite());
        }

        const btnBulkPublic = document.getElementById('bulk-public');
        if (btnBulkPublic) {
            btnBulkPublic.addEventListener('click', () => this.bulkMakePublic());
        }

        const btnBulkDelete = document.getElementById('bulk-delete');
        if (btnBulkDelete) {
            btnBulkDelete.addEventListener('click', () => this.bulkDelete());
        }
    }

    // ===== FUNCIONALIDADES PRINCIPAIS =====

    loadAvatars() {
        // Simular carregamento de avatares
        this.showLoading(true);
        
        setTimeout(() => {
            this.avatars = this.generateMockAvatars();
            this.applyFilters();
            this.showLoading(false);
            this.updateStats();
        }, 1500);
    }

    generateMockAvatars() {
        const types = ['humano', 'animal', 'fantastico', 'extraterrestre', 'robotico'];
        const genders = ['masculino', 'feminino', 'neutro', 'outro'];
        const mockAvatars = [];

        for (let i = 1; i <= 12; i++) {
            const type = types[Math.floor(Math.random() * types.length)];
            mockAvatars.push({
                id: i,
                name: `Avatar ${i}`,
                type: type,
                gender: genders[Math.floor(Math.random() * genders.length)],
                age: Math.floor(Math.random() * 60) + 18,
                description: `Descrição do avatar ${i} com características únicas.`,
                tags: ['tag1', 'tag2', 'tag3'],
                visibility: Math.random() > 0.5 ? 'publico' : 'privado',
                favorite: Math.random() > 0.7,
                created: new Date(Date.now() - Math.random() * 30 * 24 * 60 * 60 * 1000),
                lastUsed: new Date(Date.now() - Math.random() * 7 * 24 * 60 * 60 * 1000),
                prompt: ''
            });
        }

        return mockAvatars;
    }

    handleQuickSearch(query) {
        this.currentSearchQuery = query.toLowerCase();
        this.applyFilters();
    }

    applyFilters() {
        let filtered = [...this.avatars];

        // Busca rápida
        if (this.currentSearchQuery) {
            filtered = filtered.filter(avatar => 
                avatar.name.toLowerCase().includes(this.currentSearchQuery) ||
                avatar.description.toLowerCase().includes(this.currentSearchQuery) ||
                avatar.tags.some(tag => tag.toLowerCase().includes(this.currentSearchQuery))
            );
        }

        // Filtro por categoria
        const categoryFilter = document.getElementById('filter-category');
        if (categoryFilter && categoryFilter.value) {
            filtered = filtered.filter(avatar => avatar.type === categoryFilter.value);
        }

        // Filtro por status
        const statusFilters = this.getActiveStatusFilters();
        if (statusFilters.length > 0) {
            filtered = filtered.filter(avatar => {
                return statusFilters.some(status => {
                    switch (status) {
                        case 'meus': return avatar.visibility === 'privado';
                        case 'publicos': return avatar.visibility === 'publico';
                        case 'favoritos': return avatar.favorite;
                        default: return true;
                    }
                });
            });
        }

        // Ordenação
        const sortBy = document.getElementById('sort-by');
        if (sortBy) {
            this.sortAvatars(filtered, sortBy.value);
        }

        this.filteredAvatars = filtered;
        this.renderAvatars();
        this.updateResultsCount();
    }

    getActiveStatusFilters() {
        const checkboxes = document.querySelectorAll('.status-checkboxes input[type="checkbox"]:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    sortAvatars(avatars, sortBy) {
        switch (sortBy) {
            case 'name':
                avatars.sort((a, b) => a.name.localeCompare(b.name));
                break;
            case 'created':
                avatars.sort((a, b) => new Date(b.created) - new Date(a.created));
                break;
            case 'used':
                avatars.sort((a, b) => new Date(b.lastUsed) - new Date(a.lastUsed));
                break;
            case 'type':
                avatars.sort((a, b) => a.type.localeCompare(b.type));
                break;
        }
    }

    switchView(view) {
        this.currentView = view;
        
        // Update view buttons
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.view === view);
        });

        // Update grid class
        const grid = document.getElementById('avatars-grid');
        if (grid) {
            grid.classList.toggle('list-view', view === 'list');
        }

        this.renderAvatars();
    }

    renderAvatars() {
        const grid = document.getElementById('avatars-grid');
        if (!grid) return;

        // Limpar grid
        grid.innerHTML = '';

        // Verificar se há avatares
        if (this.filteredAvatars.length === 0) {
            this.showEmptyState();
            return;
        }

        // Renderizar avatares
        this.filteredAvatars.forEach(avatar => {
            const card = this.createAvatarCard(avatar);
            grid.appendChild(card);
        });
    }

    createAvatarCard(avatar) {
        const card = document.createElement('div');
        card.className = 'avatar-card-compact';
        card.dataset.avatarId = avatar.id;
        
        if (this.selectedAvatars.has(avatar.id)) {
            card.classList.add('selected');
        }

        const iconClass = this.getIconForType(avatar.type);
        
        card.innerHTML = `
            <div class="avatar-actions-hover">
                <button class="action-btn-micro" onclick="avatarManager.toggleSelection(${avatar.id})" title="Selecionar">
                    <i class="material-icons">${this.selectedAvatars.has(avatar.id) ? 'check' : 'radio_button_unchecked'}</i>
                </button>
                ${avatar.favorite ? '<button class="action-btn-micro" title="Favorito"><i class="material-icons">star</i></button>' : ''}
            </div>
            
            <div class="avatar-icon-compact">
                <i class="material-icons">${iconClass}</i>
            </div>
            
            <div class="avatar-card-info-compact">
                <div class="avatar-name-compact">${avatar.name}</div>
                <div class="avatar-type-compact">${this.getTypeLabel(avatar.type)}</div>
            </div>
        `;

        // Event listeners
        card.addEventListener('click', (e) => {
            if (!e.target.closest('.action-btn-micro')) {
                this.showAvatarDetails(avatar);
            }
        });

        return card;
    }

    getIconForType(type) {
        const icons = {
            'humano': 'person',
            'animal': 'pets',
            'fantastico': 'auto_awesome',
            'extraterrestre': 'rocket_launch',
            'robotico': 'smart_toy'
        };
        return icons[type] || 'person';
    }

    getTypeLabel(type) {
        const labels = {
            'humano': 'Humano',
            'animal': 'Animal',
            'fantastico': 'Fantástico',
            'extraterrestre': 'Extraterrestre',
            'robotico': 'Robótico/IA'
        };
        return labels[type] || type;
    }

    showLoading(show) {
        const loadingState = document.getElementById('loading-state');
        const emptyState = document.getElementById('empty-state');
        
        if (loadingState) {
            loadingState.style.display = show ? 'flex' : 'none';
        }
        if (emptyState) {
            emptyState.style.display = 'none';
        }
    }

    showEmptyState() {
        const loadingState = document.getElementById('loading-state');
        const emptyState = document.getElementById('empty-state');
        
        if (loadingState) {
            loadingState.style.display = 'none';
        }
        if (emptyState) {
            emptyState.style.display = 'flex';
        }
    }

    updateStats() {
        const totalCount = this.avatars.length;
        const publicCount = this.avatars.filter(a => a.visibility === 'publico').length;
        const favoriteCount = this.avatars.filter(a => a.favorite).length;

        const avatarCount = document.getElementById('avatar-count');
        if (avatarCount) {
            avatarCount.textContent = `${totalCount} avatares`;
        }
    }

    updateResultsCount() {
        const filteredCountEl = document.getElementById('filtered-count');
        const totalCountEl = document.getElementById('total-count');
        
        if (filteredCountEl) {
            filteredCountEl.textContent = this.filteredAvatars.length;
        }
        if (totalCountEl) {
            totalCountEl.textContent = this.avatars.length;
        }
    }

    // ===== GERENCIAMENTO DO PAINEL LATERAL =====

    showCreationMode() {
        this.currentMode = 'creation';
        this.showSidePanel();
        this.clearForm();
        
        const creationMode = document.getElementById('creation-mode');
        const detailsMode = document.getElementById('details-mode');
        
        if (creationMode) creationMode.style.display = 'flex';
        if (detailsMode) detailsMode.style.display = 'none';
    }

    showAvatarDetails(avatar) {
        this.selectedAvatar = avatar;
        this.currentMode = 'details';
        this.showSidePanel();
        
        const creationMode = document.getElementById('creation-mode');
        const detailsMode = document.getElementById('details-mode');
        
        if (creationMode) creationMode.style.display = 'none';
        if (detailsMode) detailsMode.style.display = 'flex';
        
        this.populateAvatarDetails(avatar);
    }

    showSidePanel() {
        const sidePanel = document.getElementById('side-panel');
        if (sidePanel) {
            sidePanel.classList.remove('collapsed');
        }
    }

    closeSidePanel() {
        const sidePanel = document.getElementById('side-panel');
        if (sidePanel) {
            sidePanel.classList.add('collapsed');
        }
    }

    toggleSidePanel() {
        const sidePanel = document.getElementById('side-panel');
        if (sidePanel) {
            sidePanel.classList.toggle('collapsed');
        }
    }

    populateAvatarDetails(avatar) {
        // Nome
        const nameDisplay = document.getElementById('avatar-name-display');
        if (nameDisplay) nameDisplay.textContent = avatar.name;

        // Tipo badge
        const typeBadge = document.getElementById('avatar-type-badge');
        if (typeBadge) typeBadge.textContent = this.getTypeLabel(avatar.type);

        // Metadados
        const createdDate = document.getElementById('created-date');
        if (createdDate) createdDate.textContent = this.formatDate(avatar.created);

        const lastUsed = document.getElementById('last-used');
        if (lastUsed) lastUsed.textContent = this.formatDate(avatar.lastUsed);

        const avatarStatus = document.getElementById('avatar-status');
        if (avatarStatus) avatarStatus.textContent = avatar.visibility === 'publico' ? 'Público' : 'Privado';

        // Descrição
        const descriptionDisplay = document.getElementById('avatar-description-display');
        if (descriptionDisplay) descriptionDisplay.textContent = avatar.description;

        // Tags
        const tagsDisplay = document.getElementById('tags-display');
        if (tagsDisplay) {
            tagsDisplay.innerHTML = avatar.tags.map(tag => 
                `<span class="tag-compact">${tag}</span>`
            ).join('');
        }

        // Ícone do avatar
        const avatarImage = document.getElementById('avatar-image');
        if (avatarImage) {
            const iconClass = this.getIconForType(avatar.type);
            avatarImage.innerHTML = `<i class="material-icons">${iconClass}</i>`;
        }

        // Estado do botão favorito
        const favoriteBtn = document.getElementById('favorite-avatar');
        if (favoriteBtn) {
            const icon = favoriteBtn.querySelector('i');
            if (icon) {
                icon.textContent = avatar.favorite ? 'star' : 'star_border';
            }
        }
    }

    formatDate(date) {
        return new Intl.DateTimeFormat('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(date));
    }

    // ===== CAMPOS DINÂMICOS =====

    setupDynamicFields() {
        // Configurar campos dinâmicos inicial
        this.updateDynamicFields('');
    }

    updateDynamicFields(type) {
        const container = document.getElementById('dynamic-fields');
        if (!container) return;

        container.innerHTML = '';

        const fields = this.getDynamicFieldsForType(type);
        fields.forEach(field => {
            const fieldElement = this.createDynamicField(field);
            container.appendChild(fieldElement);
        });
    }

    getDynamicFieldsForType(type) {
        const fieldConfigs = {
            'humano': [
                { name: 'profissao', label: 'Profissão', type: 'text', placeholder: 'Ex: Médica' },
                { name: 'personalidade', label: 'Personalidade', type: 'select', options: ['Extrovertido', 'Introvertido', 'Ambos'] }
            ],
            'animal': [
                { name: 'especie', label: 'Espécie', type: 'text', placeholder: 'Ex: Gato doméstico' },
                { name: 'habitat', label: 'Habitat', type: 'select', options: ['Doméstico', 'Selvagem', 'Aquático'] }
            ],
            'fantastico': [
                { name: 'poder', label: 'Poder Principal', type: 'text', placeholder: 'Ex: Magia elemental' },
                { name: 'origem', label: 'Origem', type: 'select', options: ['Místico', 'Lendário', 'Mitológico'] }
            ],
            'extraterrestre': [
                { name: 'planeta', label: 'Planeta de Origem', type: 'text', placeholder: 'Ex: Kepler-442b' },
                { name: 'tecnologia', label: 'Nível Tecnológico', type: 'select', options: ['Básico', 'Avançado', 'Transcendente'] }
            ],
            'robotico': [
                { name: 'modelo', label: 'Modelo/Versão', type: 'text', placeholder: 'Ex: HAL-9000' },
                { name: 'funcao', label: 'Função Principal', type: 'select', options: ['Assistente', 'Combate', 'Pesquisa', 'Entretenimento'] }
            ]
        };

        return fieldConfigs[type] || [];
    }

    createDynamicField(field) {
        const div = document.createElement('div');
        div.className = 'form-group-compact';

        const label = document.createElement('label');
        label.textContent = field.label;

        let input;
        if (field.type === 'select') {
            input = document.createElement('select');
            input.innerHTML = '<option value="">Selecione...</option>';
            field.options.forEach(option => {
                const optionEl = document.createElement('option');
                optionEl.value = option.toLowerCase();
                optionEl.textContent = option;
                input.appendChild(optionEl);
            });
        } else {
            input = document.createElement('input');
            input.type = field.type;
            input.placeholder = field.placeholder || '';
        }

        input.name = field.name;
        input.id = `dynamic-${field.name}`;

        div.appendChild(label);
        div.appendChild(input);

        return div;
    }

    // ===== FORMULÁRIO DE CRIAÇÃO =====

    handleFormSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const avatarData = Object.fromEntries(formData.entries());
        
        // Adicionar campos dinâmicos
        const dynamicFields = document.querySelectorAll('#dynamic-fields input, #dynamic-fields select');
        dynamicFields.forEach(field => {
            if (field.value) {
                avatarData[field.name] = field.value;
            }
        });

        // Processar tags
        if (avatarData.tags) {
            avatarData.tags = avatarData.tags.split(',').map(tag => tag.trim()).filter(tag => tag);
        }

        this.createAvatar(avatarData);
    }

    createAvatar(avatarData) {
        // Simular criação do avatar
        const newAvatar = {
            id: Date.now(),
            ...avatarData,
            created: new Date(),
            lastUsed: new Date(),
            favorite: false,
            prompt: ''
        };

        this.avatars.unshift(newAvatar);
        this.applyFilters();
        this.updateStats();
        this.closeSidePanel();
        
        this.showNotification('Avatar criado com sucesso!', 'success');
    }

    clearForm() {
        const form = document.getElementById('avatar-creation-form');
        if (form) {
            form.reset();
            this.updateDynamicFields('');
        }
    }

    // ===== AÇÕES DOS AVATARES =====

    toggleFavorite() {
        if (!this.selectedAvatar) return;

        this.selectedAvatar.favorite = !this.selectedAvatar.favorite;
        this.populateAvatarDetails(this.selectedAvatar);
        this.renderAvatars();
        
        const action = this.selectedAvatar.favorite ? 'adicionado aos' : 'removido dos';
        this.showNotification(`Avatar ${action} favoritos!`, 'info');
    }

    duplicateAvatar() {
        if (!this.selectedAvatar) return;

        const duplicatedAvatar = {
            ...this.selectedAvatar,
            id: Date.now(),
            name: `${this.selectedAvatar.name} (Cópia)`,
            created: new Date(),
            lastUsed: new Date()
        };

        this.avatars.unshift(duplicatedAvatar);
        this.applyFilters();
        this.updateStats();
        
        this.showNotification('Avatar duplicado com sucesso!', 'success');
    }

    shareAvatar() {
        if (!this.selectedAvatar) return;

        // Simular compartilhamento
        const shareData = {
            title: `Avatar: ${this.selectedAvatar.name}`,
            text: this.selectedAvatar.description,
            url: window.location.href
        };

        if (navigator.share) {
            navigator.share(shareData);
        } else {
            // Fallback: copiar para clipboard
            navigator.clipboard.writeText(`${shareData.title}\n${shareData.text}\n${shareData.url}`);
            this.showNotification('Link copiado para a área de transferência!', 'info');
        }
    }

    generatePrompt() {
        if (!this.selectedAvatar) return;

        // Simular geração de prompt
        const avatar = this.selectedAvatar;
        const promptText = `${avatar.name}, ${this.getTypeLabel(avatar.type).toLowerCase()} ${avatar.gender} de ${avatar.age} anos. ${avatar.description} Tags: ${avatar.tags.join(', ')}.`;
        
        avatar.prompt = promptText;
        
        const promptDisplay = document.querySelector('.prompt-text-compact');
        if (promptDisplay) {
            promptDisplay.textContent = promptText;
            promptDisplay.style.fontStyle = 'normal';
        }
        
        this.showNotification('Prompt gerado com sucesso!', 'success');
    }

    copyPrompt() {
        if (!this.selectedAvatar || !this.selectedAvatar.prompt) {
            this.showNotification('Gere um prompt primeiro!', 'warning');
            return;
        }

        navigator.clipboard.writeText(this.selectedAvatar.prompt).then(() => {
            this.showNotification('Prompt copiado para a área de transferência!', 'success');
        });
    }

    addToPrompt() {
        if (!this.selectedAvatar) return;

        // Simular adição ao prompt principal (seria integração com o sistema principal)
        this.showNotification('Avatar adicionado ao prompt principal!', 'success');
        this.closeSidePanel();
    }

    editAvatar() {
        if (!this.selectedAvatar) return;

        // Mudar para modo de edição (popular formulário com dados do avatar)
        this.populateFormForEdit(this.selectedAvatar);
        this.showCreationMode();
    }

    populateFormForEdit(avatar) {
        // Popular formulário com dados do avatar para edição
        const form = document.getElementById('avatar-creation-form');
        if (!form) return;

        // Campos básicos
        form.querySelector('#avatar-name').value = avatar.name || '';
        form.querySelector('#avatar-type').value = avatar.type || '';
        form.querySelector('#avatar-gender').value = avatar.gender || '';
        form.querySelector('#avatar-age').value = avatar.age || '';
        form.querySelector('#avatar-visibility').value = avatar.visibility || '';
        form.querySelector('#avatar-description').value = avatar.description || '';
        form.querySelector('#avatar-tags').value = avatar.tags ? avatar.tags.join(', ') : '';

        // Atualizar campos dinâmicos
        this.updateDynamicFields(avatar.type);

        // Popular campos dinâmicos se existirem
        setTimeout(() => {
            const dynamicFields = document.querySelectorAll('#dynamic-fields input, #dynamic-fields select');
            dynamicFields.forEach(field => {
                if (avatar[field.name]) {
                    field.value = avatar[field.name];
                }
            });
        }, 100);
    }

    deleteAvatar() {
        if (!this.selectedAvatar) return;

        if (confirm(`Tem certeza que deseja excluir o avatar "${this.selectedAvatar.name}"?`)) {
            this.avatars = this.avatars.filter(a => a.id !== this.selectedAvatar.id);
            this.applyFilters();
            this.updateStats();
            this.closeSidePanel();
            
            this.showNotification('Avatar excluído com sucesso!', 'success');
        }
    }

    // ===== SELEÇÃO E AÇÕES EM LOTE =====

    toggleSelection(avatarId) {
        if (this.selectedAvatars.has(avatarId)) {
            this.selectedAvatars.delete(avatarId);
        } else {
            this.selectedAvatars.add(avatarId);
        }

        this.updateBulkActionsVisibility();
        this.renderAvatars(); // Re-render para atualizar estado visual
    }

    updateBulkActionsVisibility() {
        const bulkActions = document.getElementById('bulk-actions');
        const selectedCount = document.getElementById('selected-count');
        
        if (bulkActions && selectedCount) {
            const count = this.selectedAvatars.size;
            bulkActions.style.display = count > 0 ? 'flex' : 'none';
            selectedCount.textContent = count;
        }
    }

    bulkFavorite() {
        const selectedIds = Array.from(this.selectedAvatars);
        selectedIds.forEach(id => {
            const avatar = this.avatars.find(a => a.id === id);
            if (avatar) avatar.favorite = true;
        });

        this.renderAvatars();
        this.clearSelection();
        this.showNotification(`${selectedIds.length} avatares adicionados aos favoritos!`, 'success');
    }

    bulkMakePublic() {
        const selectedIds = Array.from(this.selectedAvatars);
        selectedIds.forEach(id => {
            const avatar = this.avatars.find(a => a.id === id);
            if (avatar) avatar.visibility = 'publico';
        });

        this.renderAvatars();
        this.clearSelection();
        this.updateStats();
        this.showNotification(`${selectedIds.length} avatares tornados públicos!`, 'success');
    }

    bulkDelete() {
        const selectedIds = Array.from(this.selectedAvatars);
        
        if (confirm(`Tem certeza que deseja excluir ${selectedIds.length} avatares?`)) {
            this.avatars = this.avatars.filter(a => !selectedIds.includes(a.id));
            this.applyFilters();
            this.updateStats();
            this.clearSelection();
            
            this.showNotification(`${selectedIds.length} avatares excluídos!`, 'success');
        }
    }

    clearSelection() {
        this.selectedAvatars.clear();
        this.updateBulkActionsVisibility();
        this.renderAvatars();
    }

    // ===== UTILITÁRIOS =====

    refreshAvatars() {
        this.showNotification('Atualizando avatares...', 'info');
        this.loadAvatars();
    }

    toggleAdvancedFilters() {
        const advancedFilters = document.getElementById('advanced-filters');
        if (advancedFilters) {
            // Implementar toggle dos filtros avançados se necessário
            console.log('Toggle filtros avançados');
        }
    }

    showNotification(message, type = 'info') {
        // Criar sistema de notificação simples
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 10000;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        `;

        document.body.appendChild(notification);

        // Remover após 3 segundos
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    window.avatarManager = new AvatarManagerCompactModern();
});

// Exportar para uso global
window.AvatarManagerCompactModern = AvatarManagerCompactModern;