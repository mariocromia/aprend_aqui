/**
 * GERENCIADOR COMPACTO DE AVATARES
 * Sistema completo de gerenciamento estilo explorador de arquivos
 */

class AvatarManagerCompact {
    constructor() {
        this.avatars = [];
        this.folders = [];
        this.currentFolder = '';
        this.currentView = 'grid';
        this.iconSize = 100;
        this.selectedAvatars = [];
        this.filters = {
            search: '',
            types: ['humano', 'animal', 'fantastico', 'extraterrestre', 'robotico'],
            visibility: ['meus', 'publicos']
        };
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadFolders();
        this.loadAvatars();
        this.setupSearch();
        this.setupFilters();
        this.setupViewControls();
    }
    
    // ===== EVENT BINDING =====
    bindEvents() {
        // Botões da toolbar
        document.getElementById('view-grid')?.addEventListener('click', () => this.setView('grid'));
        document.getElementById('view-list')?.addEventListener('click', () => this.setView('list'));
        document.getElementById('new-folder')?.addEventListener('click', () => this.showNewFolderModal());
        document.getElementById('new-avatar')?.addEventListener('click', () => this.showQuickCreateModal());
        
        // Busca
        document.getElementById('avatar-search')?.addEventListener('input', this.handleSearch.bind(this));
        document.getElementById('clear-search')?.addEventListener('click', this.clearSearch.bind(this));
        
        // Filtros
        document.getElementById('filter-btn')?.addEventListener('click', this.toggleFilters.bind(this));
        
        // Slider de tamanho
        document.getElementById('icon-size')?.addEventListener('input', this.handleSizeChange.bind(this));
        
        // Sidebar
        document.getElementById('toggle-sidebar')?.addEventListener('click', this.toggleSidebar.bind(this));
        
        // Painel de informações
        document.getElementById('close-info')?.addEventListener('click', () => this.hideInfoPanel());
        
        // Modais
        this.bindModalEvents();
        
        // Keyboard shortcuts
        document.addEventListener('keydown', this.handleKeyboard.bind(this));
        
        // Click fora para fechar dropdowns
        document.addEventListener('click', this.handleOutsideClick.bind(this));
    }
    
    bindModalEvents() {
        // Modal de criação rápida
        document.getElementById('close-quick-create')?.addEventListener('click', () => this.hideQuickCreateModal());
        document.getElementById('cancel-quick-create')?.addEventListener('click', () => this.hideQuickCreateModal());
        document.getElementById('advanced-create')?.addEventListener('click', () => this.showAdvancedCreate());
        document.getElementById('quick-create-form')?.addEventListener('submit', this.handleQuickCreate.bind(this));
        
        // Modal de nova pasta
        document.getElementById('close-new-folder')?.addEventListener('click', () => this.hideNewFolderModal());
        document.getElementById('cancel-new-folder')?.addEventListener('click', () => this.hideNewFolderModal());
        document.getElementById('new-folder-form')?.addEventListener('submit', this.handleNewFolder.bind(this));
    }
    
    // ===== CARREGAMENTO DE DADOS =====
    async loadAvatars() {
        try {
            this.showLoading(true);
            
            // Simular carregamento (substituir por API real)
            await this.delay(1000);
            
            this.avatars = [
                {
                    id: 1,
                    name: 'Elena Rodriguez',
                    type: 'humano',
                    category: 'pessoa',
                    description: 'Jovem médica especialista em emergências',
                    folder: '',
                    tags: ['médica', 'jovem', 'profissional'],
                    created: '2024-01-15',
                    used: '2024-01-20',
                    public: false,
                    favorite: true
                },
                {
                    id: 2,
                    name: 'Dragão Místico',
                    type: 'fantastico',
                    category: 'criatura',
                    description: 'Antigo dragão guardião das montanhas',
                    folder: 'fantasticos',
                    tags: ['dragão', 'antigo', 'guardião'],
                    created: '2024-01-10',
                    used: '2024-01-18',
                    public: true,
                    favorite: false
                },
                {
                    id: 3,
                    name: 'Wolf Alpha',
                    type: 'animal',
                    category: 'mamífero',
                    description: 'Lobo alfa líder da matilha',
                    folder: '',
                    tags: ['lobo', 'alfa', 'líder'],
                    created: '2024-01-12',
                    used: '2024-01-19',
                    public: false,
                    favorite: true
                }
            ];
            
            this.renderAvatars();
            this.updateItemCount();
            
        } catch (error) {
            console.error('Erro ao carregar avatares:', error);
            this.showError('Erro ao carregar avatares');
        } finally {
            this.showLoading(false);
        }
    }
    
    async loadFolders() {
        try {
            // Simular carregamento de pastas
            this.folders = [
                { id: 'fantasticos', name: 'Fantásticos', parent: '', count: 5 },
                { id: 'humanos', name: 'Humanos', parent: '', count: 12 },
                { id: 'animais', name: 'Animais', parent: '', count: 8 },
                { id: 'rpg', name: 'RPG', parent: 'fantasticos', count: 3 }
            ];
            
            this.renderFolderTree();
            this.updateFolderCounts();
            
        } catch (error) {
            console.error('Erro ao carregar pastas:', error);
        }
    }
    
    // ===== RENDERIZAÇÃO =====
    renderAvatars() {
        const grid = document.getElementById('avatar-grid');
        if (!grid) return;
        
        const filteredAvatars = this.getFilteredAvatars();
        
        if (filteredAvatars.length === 0) {
            this.showEmptyState();
            return;
        }
        
        grid.innerHTML = '';
        grid.className = `avatar-grid ${this.currentView}-view`;
        
        filteredAvatars.forEach(avatar => {
            const item = this.createAvatarItem(avatar);
            grid.appendChild(item);
        });
        
        this.hideEmptyState();
    }
    
    createAvatarItem(avatar) {
        const item = document.createElement('div');
        item.className = 'avatar-item';
        item.dataset.id = avatar.id;
        
        const typeIcons = {
            humano: 'person',
            animal: 'pets',
            fantastico: 'auto_fix_high',
            extraterrestre: 'rocket_launch',
            robotico: 'smart_toy'
        };
        
        item.innerHTML = `
            <div class="avatar-icon">
                <i class="material-icons">${typeIcons[avatar.type] || 'person'}</i>
            </div>
            <div class="avatar-info">
                <div class="avatar-name">${this.escapeHtml(avatar.name)}</div>
                <div class="avatar-type">${this.capitalize(avatar.type)}</div>
            </div>
            <div class="avatar-actions">
                <button class="btn-icon btn-tiny" title="Favorito" data-action="favorite">
                    <i class="material-icons">${avatar.favorite ? 'star' : 'star_border'}</i>
                </button>
                <button class="btn-icon btn-tiny" title="Mais opções" data-action="menu">
                    <i class="material-icons">more_vert</i>
                </button>
            </div>
        `;
        
        // Event listeners
        item.addEventListener('click', (e) => {
            if (!e.target.closest('.avatar-actions')) {
                this.selectAvatar(avatar.id);
                this.showInfoPanel(avatar);
            }
        });
        
        item.addEventListener('dblclick', () => {
            this.editAvatar(avatar.id);
        });
        
        // Action buttons
        const favoriteBtn = item.querySelector('[data-action="favorite"]');
        favoriteBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleFavorite(avatar.id);
        });
        
        const menuBtn = item.querySelector('[data-action="menu"]');
        menuBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.showContextMenu(avatar.id, e);
        });
        
        return item;
    }
    
    renderFolderTree() {
        const customTree = document.getElementById('custom-folder-tree');
        if (!customTree) return;
        
        customTree.innerHTML = '';
        
        this.folders.forEach(folder => {
            if (!folder.parent) { // Apenas pastas raiz por enquanto
                const item = this.createFolderItem(folder);
                customTree.appendChild(item);
            }
        });
    }
    
    createFolderItem(folder) {
        const item = document.createElement('div');
        item.className = 'tree-item';
        item.dataset.folder = folder.id;
        
        item.innerHTML = `
            <i class="material-icons">folder</i>
            <span>${this.escapeHtml(folder.name)}</span>
            <span class="item-count">${folder.count}</span>
        `;
        
        item.addEventListener('click', () => {
            this.selectFolder(folder.id);
        });
        
        return item;
    }
    
    // ===== FILTROS E BUSCA =====
    getFilteredAvatars() {
        return this.avatars.filter(avatar => {
            // Filtro de pasta
            if (this.currentFolder && avatar.folder !== this.currentFolder) {
                return false;
            }
            
            // Filtro de busca
            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                const searchFields = [
                    avatar.name,
                    avatar.description,
                    avatar.type,
                    ...avatar.tags
                ].join(' ').toLowerCase();
                
                if (!searchFields.includes(search)) {
                    return false;
                }
            }
            
            // Filtro de tipo
            if (!this.filters.types.includes(avatar.type)) {
                return false;
            }
            
            // Filtro de visibilidade
            const isMine = !avatar.public; // Assumindo que não público = meu
            if (!this.filters.visibility.includes('meus') && isMine) {
                return false;
            }
            if (!this.filters.visibility.includes('publicos') && avatar.public) {
                return false;
            }
            
            return true;
        });
    }
    
    handleSearch(e) {
        this.filters.search = e.target.value;
        this.renderAvatars();
        this.updateItemCount();
        
        // Mostrar/ocultar botão de limpar
        const clearBtn = document.getElementById('clear-search');
        const container = e.target.closest('.search-container');
        
        if (e.target.value) {
            container?.classList.add('has-value');
        } else {
            container?.classList.remove('has-value');
        }
    }
    
    clearSearch() {
        const searchInput = document.getElementById('avatar-search');
        if (searchInput) {
            searchInput.value = '';
            this.filters.search = '';
            this.renderAvatars();
            this.updateItemCount();
            
            const container = searchInput.closest('.search-container');
            container?.classList.remove('has-value');
        }
    }
    
    setupSearch() {
        // Implementar busca com debounce
        let searchTimeout;
        const searchInput = document.getElementById('avatar-search');
        
        searchInput?.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.handleSearch(e);
            }, 300);
        });
    }
    
    setupFilters() {
        const filterMenu = document.getElementById('filter-menu');
        if (!filterMenu) return;
        
        // Bind filter checkboxes
        const checkboxes = filterMenu.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.updateFilters();
            });
        });
    }
    
    updateFilters() {
        const filterMenu = document.getElementById('filter-menu');
        if (!filterMenu) return;
        
        // Update type filters
        const typeCheckboxes = filterMenu.querySelectorAll('.filter-section:first-child input[type="checkbox"]');
        this.filters.types = Array.from(typeCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        
        // Update visibility filters
        const visibilityCheckboxes = filterMenu.querySelectorAll('.filter-section:last-child input[type="checkbox"]');
        this.filters.visibility = Array.from(visibilityCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        
        this.renderAvatars();
        this.updateItemCount();
    }
    
    toggleFilters() {
        const filterMenu = document.getElementById('filter-menu');
        filterMenu?.classList.toggle('active');
    }
    
    // ===== CONTROLES DE VISUALIZAÇÃO =====
    setView(view) {
        this.currentView = view;
        
        // Update buttons
        document.getElementById('view-grid')?.classList.toggle('active', view === 'grid');
        document.getElementById('view-list')?.classList.toggle('active', view === 'list');
        
        this.renderAvatars();
    }
    
    handleSizeChange(e) {
        this.iconSize = parseInt(e.target.value);
        
        // Update CSS custom property
        document.documentElement.style.setProperty('--item-size', `${this.iconSize}px`);
        
        // Update icon font size
        const avatarItems = document.querySelectorAll('.avatar-icon');
        avatarItems.forEach(icon => {
            icon.style.fontSize = `${this.iconSize * 0.4}px`;
        });
    }
    
    setupViewControls() {
        // Initialize icon size
        this.handleSizeChange({ target: { value: this.iconSize } });
    }
    
    // ===== NAVEGAÇÃO =====
    selectFolder(folderId) {
        this.currentFolder = folderId;
        
        // Update active folder in sidebar
        document.querySelectorAll('.tree-item').forEach(item => {
            item.classList.toggle('active', item.dataset.folder === folderId);
        });
        
        // Update breadcrumb
        this.updateBreadcrumb(folderId);
        
        this.renderAvatars();
        this.updateItemCount();
    }
    
    updateBreadcrumb(folderId) {
        const breadcrumb = document.querySelector('.breadcrumb-path');
        if (!breadcrumb) return;
        
        breadcrumb.innerHTML = `
            <button class="breadcrumb-item ${!folderId ? 'active' : ''}" data-path="">
                <i class="material-icons">home</i>
                Avatares
            </button>
        `;
        
        if (folderId) {
            const folder = this.folders.find(f => f.id === folderId);
            if (folder) {
                breadcrumb.innerHTML += `
                    <i class="material-icons">chevron_right</i>
                    <button class="breadcrumb-item active" data-path="${folderId}">
                        ${this.escapeHtml(folder.name)}
                    </button>
                `;
            }
        }
        
        // Bind breadcrumb clicks
        breadcrumb.querySelectorAll('.breadcrumb-item').forEach(item => {
            item.addEventListener('click', () => {
                this.selectFolder(item.dataset.path);
            });
        });
    }
    
    toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar?.classList.toggle('collapsed');
    }
    
    // ===== SELEÇÃO E AÇÕES =====
    selectAvatar(avatarId) {
        // Single selection for now
        this.selectedAvatars = [avatarId];
        
        // Update visual selection
        document.querySelectorAll('.avatar-item').forEach(item => {
            item.classList.toggle('selected', item.dataset.id == avatarId);
        });
    }
    
    toggleFavorite(avatarId) {
        const avatar = this.avatars.find(a => a.id == avatarId);
        if (avatar) {
            avatar.favorite = !avatar.favorite;
            this.renderAvatars();
            
            // Update in backend
            this.updateAvatarFavorite(avatarId, avatar.favorite);
        }
    }
    
    async updateAvatarFavorite(avatarId, favorite) {
        try {
            // API call to update favorite status
            console.log(`Updating avatar ${avatarId} favorite status to ${favorite}`);
        } catch (error) {
            console.error('Erro ao atualizar favorito:', error);
        }
    }
    
    editAvatar(avatarId) {
        // Redirect to advanced editor
        console.log(`Editing avatar ${avatarId}`);
        // window.location.href = `avatar-editor.php?id=${avatarId}`;
    }
    
    // ===== PAINEL DE INFORMAÇÕES =====
    showInfoPanel(avatar) {
        const panel = document.getElementById('info-panel');
        if (!panel) return;
        
        // Populate info
        document.getElementById('info-name').textContent = avatar.name;
        document.getElementById('info-description').textContent = avatar.description;
        document.getElementById('info-type').textContent = this.capitalize(avatar.type);
        document.getElementById('info-category').textContent = this.capitalize(avatar.category);
        document.getElementById('info-created').textContent = this.formatDate(avatar.created);
        document.getElementById('info-used').textContent = this.formatDate(avatar.used);
        
        // Tags
        const tagList = document.getElementById('info-tag-list');
        if (tagList) {
            tagList.innerHTML = avatar.tags.map(tag => 
                `<span class="tag-item">${this.escapeHtml(tag)}</span>`
            ).join('');
        }
        
        panel.classList.add('active');
    }
    
    hideInfoPanel() {
        const panel = document.getElementById('info-panel');
        panel?.classList.remove('active');
    }
    
    // ===== MODAIS =====
    showQuickCreateModal() {
        const modal = document.getElementById('quick-create-modal');
        modal?.classList.add('active');
        
        // Focus first input
        document.getElementById('quick-name')?.focus();
    }
    
    hideQuickCreateModal() {
        const modal = document.getElementById('quick-create-modal');
        modal?.classList.remove('active');
        
        // Reset form
        document.getElementById('quick-create-form')?.reset();
    }
    
    showNewFolderModal() {
        const modal = document.getElementById('new-folder-modal');
        modal?.classList.add('active');
        
        // Populate parent folder options
        this.populateParentFolders();
        
        document.getElementById('folder-name')?.focus();
    }
    
    hideNewFolderModal() {
        const modal = document.getElementById('new-folder-modal');
        modal?.classList.remove('active');
        
        document.getElementById('new-folder-form')?.reset();
    }
    
    populateParentFolders() {
        const select = document.getElementById('parent-folder');
        if (!select) return;
        
        select.innerHTML = '<option value="">Pasta raiz</option>';
        
        this.folders.forEach(folder => {
            if (!folder.parent) {
                select.innerHTML += `<option value="${folder.id}">${this.escapeHtml(folder.name)}</option>`;
            }
        });
    }
    
    showAdvancedCreate() {
        this.hideQuickCreateModal();
        // Redirect to advanced creator
        console.log('Opening advanced creator...');
    }
    
    // ===== FORM HANDLERS =====
    async handleQuickCreate(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const avatarData = {
            name: formData.get('quick-name') || document.getElementById('quick-name').value,
            type: document.getElementById('quick-type').value,
            gender: document.getElementById('quick-gender').value,
            age: document.getElementById('quick-age').value,
            description: document.getElementById('quick-description').value,
            folder: document.getElementById('quick-folder').value,
            visibility: document.getElementById('quick-visibility').value
        };
        
        try {
            // Validate
            if (!avatarData.name || !avatarData.type) {
                this.showError('Nome e tipo são obrigatórios');
                return;
            }
            
            // Create avatar
            const newAvatar = await this.createAvatar(avatarData);
            
            // Add to list
            this.avatars.unshift(newAvatar);
            this.renderAvatars();
            this.updateItemCount();
            
            this.hideQuickCreateModal();
            this.showSuccess('Avatar criado com sucesso!');
            
        } catch (error) {
            console.error('Erro ao criar avatar:', error);
            this.showError('Erro ao criar avatar');
        }
    }
    
    async handleNewFolder(e) {
        e.preventDefault();
        
        const name = document.getElementById('folder-name').value;
        const parent = document.getElementById('parent-folder').value;
        
        try {
            if (!name) {
                this.showError('Nome da pasta é obrigatório');
                return;
            }
            
            const newFolder = await this.createFolder({ name, parent });
            
            this.folders.push(newFolder);
            this.renderFolderTree();
            
            this.hideNewFolderModal();
            this.showSuccess('Pasta criada com sucesso!');
            
        } catch (error) {
            console.error('Erro ao criar pasta:', error);
            this.showError('Erro ao criar pasta');
        }
    }
    
    // ===== API CALLS =====
    async createAvatar(data) {
        // Simulate API call
        await this.delay(500);
        
        return {
            id: Date.now(),
            name: data.name,
            type: data.type,
            category: 'custom',
            description: data.description,
            folder: data.folder,
            tags: data.description.split(' ').slice(0, 3),
            created: new Date().toISOString().split('T')[0],
            used: null,
            public: data.visibility === 'publico',
            favorite: false
        };
    }
    
    async createFolder(data) {
        // Simulate API call
        await this.delay(300);
        
        return {
            id: data.name.toLowerCase().replace(/\s+/g, '-'),
            name: data.name,
            parent: data.parent,
            count: 0
        };
    }
    
    // ===== KEYBOARD SHORTCUTS =====
    handleKeyboard(e) {
        // Esc - Close modals/panels
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal-overlay.active');
            if (activeModal) {
                activeModal.classList.remove('active');
                return;
            }
            
            const activePanel = document.querySelector('.info-panel.active');
            if (activePanel) {
                this.hideInfoPanel();
                return;
            }
        }
        
        // Ctrl+N - New avatar
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            this.showQuickCreateModal();
        }
        
        // F2 - Rename selected
        if (e.key === 'F2' && this.selectedAvatars.length === 1) {
            this.editAvatar(this.selectedAvatars[0]);
        }
    }
    
    handleOutsideClick(e) {
        // Close filter dropdown
        const filterBtn = document.getElementById('filter-btn');
        const filterMenu = document.getElementById('filter-menu');
        
        if (filterMenu && !filterBtn?.contains(e.target) && !filterMenu.contains(e.target)) {
            filterMenu.classList.remove('active');
        }
    }
    
    // ===== UTILITIES =====
    updateItemCount() {
        const count = this.getFilteredAvatars().length;
        const countElement = document.querySelector('.item-count');
        if (countElement) {
            countElement.textContent = `${count} ${count === 1 ? 'item' : 'itens'}`;
        }
    }
    
    updateFolderCounts() {
        // Update folder counts in sidebar
        this.folders.forEach(folder => {
            const folderItem = document.querySelector(`[data-folder="${folder.id}"] .item-count`);
            if (folderItem) {
                const count = this.avatars.filter(a => a.folder === folder.id).length;
                folderItem.textContent = count;
            }
        });
    }
    
    showLoading(show) {
        const placeholder = document.querySelector('.loading-placeholder');
        if (placeholder) {
            placeholder.style.display = show ? 'flex' : 'none';
        }
    }
    
    showEmptyState() {
        const emptyState = document.getElementById('empty-state');
        const grid = document.getElementById('avatar-grid');
        
        if (emptyState && grid) {
            emptyState.style.display = 'flex';
            grid.style.display = 'none';
        }
    }
    
    hideEmptyState() {
        const emptyState = document.getElementById('empty-state');
        const grid = document.getElementById('avatar-grid');
        
        if (emptyState && grid) {
            emptyState.style.display = 'none';
            grid.style.display = 'grid';
        }
    }
    
    showSuccess(message) {
        // Simple notification - can be enhanced with toast library
        alert(`✅ ${message}`);
    }
    
    showError(message) {
        // Simple notification - can be enhanced with toast library
        alert(`❌ ${message}`);
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    capitalize(text) {
        return text.charAt(0).toUpperCase() + text.slice(1);
    }
    
    formatDate(dateStr) {
        if (!dateStr) return '-';
        return new Date(dateStr).toLocaleDateString('pt-BR');
    }
    
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.avatarManager = new AvatarManagerCompact();
});