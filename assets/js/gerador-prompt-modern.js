/**
 * Modern Prompt Generator - JavaScript
 * Sistema de geração de prompts com navegação por abas
 */

class ModernPromptGenerator {
    constructor() {
        this.currentTab = 0;
        this.tabs = ['ambiente', 'estilo_visual', 'iluminacao', 'tecnica', 'elementos_especiais', 'qualidade', 'avatar', 'camera', 'camera2', 'voz', 'acao'];
        this.loadedTabs = new Set(['ambiente', 'qualidade', 'avatar', 'camera', 'camera2', 'voz', 'acao']); // Abas renderizadas no servidor
        this.lazyLoadEnabled = true;
        this.selections = {
            environment: null,
            visual_style: null,
            lighting: null,
            character: null,
            camera: null,
            voice: null,
            action: null,
            quality: null,
            technique: null,
            special_elements: null
        };
        this.customDescriptions = {
            environment: '',
            visual_style: '',
            lighting: '',
            character: '',
            camera: '',
            voice: '',
            action: '',
            quality: '',
            technique: '',
            special_elements: ''
        };
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.updatePromptPreview();
        
        // Verificar se as abas estáticas existem e forçar carregamento se necessário
        ['qualidade', 'avatar', 'camera', 'camera2', 'voz', 'acao'].forEach(tabName => {
            const tab = document.querySelector(`#tab-${tabName}`);
            if (tab && tabName === 'qualidade') {
                const grid = tab.querySelector('.categories-grid');
                if (grid && grid.innerHTML.trim() === '') {
                    console.warn('Aba qualidade vazia - pode ser necessário recarregar dados');
                }
            }
        });
    }

    bindEvents() {
        // Tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', (e) => {
                const tabName = e.currentTarget.dataset.tab;
                this.showTab(tabName);
            });
        });

        // Use event delegation for subcategory cards to handle dynamically loaded content
        document.addEventListener('click', (e) => {
            if (e.target.closest('.subcategory-card')) {
                const card = e.target.closest('.subcategory-card');
                const type = card.dataset.type;
                const value = card.dataset.value;
                if (type && value) {
                    this.selectOption(type, value, card);
                }
            }
        });

        // Custom description textareas - use event delegation
        document.addEventListener('input', (e) => {
            if (e.target.name && e.target.name.startsWith('custom_')) {
                const type = e.target.name.replace('custom_', '');
                this.customDescriptions[type] = e.target.value;
                this.updatePromptPreview();
            }
        });

        // Original prompt textarea
        const originalPromptTextarea = document.querySelector('[name="original_prompt"]');
        if (originalPromptTextarea) {
            originalPromptTextarea.addEventListener('input', () => {
                this.updatePromptPreview();
            });
        }
    }

    showTab(tabName) {
        const tabIndex = this.tabs.indexOf(tabName);
        if (tabIndex === -1) return;

        this.currentTab = tabIndex;

        // Lazy load tab content if not loaded yet
        if (this.lazyLoadEnabled && !this.loadedTabs.has(tabName)) {
            this.loadTabContent(tabName);
        }

        // Update tab buttons
        document.querySelectorAll('.tab-button').forEach((btn, index) => {
            btn.classList.toggle('active', index === tabIndex);
        });

        // Update tab content
        document.querySelectorAll('.tab-content').forEach((content, index) => {
            const isActive = index === tabIndex;
            content.classList.toggle('active', isActive);
            
            // Otimização específica para aba qualidade
            if (content.id === 'tab-qualidade' && isActive) {
                // Garantir que a aba qualidade seja totalmente visível quando ativada
                const grid = content.querySelector('.categories-grid');
                if (grid) {
                    const styles = window.getComputedStyle(grid);
                    // Forçar visibilidade se necessário (correção de problemas de CSS)
                    if (styles.visibility === 'hidden' || styles.display === 'none') {
                        grid.style.visibility = 'visible';
                        grid.style.display = 'block';
                        grid.style.pointerEvents = 'auto';
                        grid.style.opacity = '1';
                    }
                }
            }
        });

        // Scroll to top of tab content
        const activeContent = document.querySelector('.tab-content.active');
        if (activeContent) {
            activeContent.scrollTop = 0;
        }

        // Preload next tab for better UX
        this.preloadNextTab(tabName);
    }

    loadTabContent(tabName) {
        const tabContent = document.querySelector(`#tab-${tabName}`);
        if (!tabContent || this.loadedTabs.has(tabName)) return;

        // Don't show skeleton for static tabs (already rendered server-side)
        const isStaticTab = ['qualidade', 'avatar', 'camera', 'camera2', 'voz', 'acao'].includes(tabName);
        
        if (!isStaticTab) {
            // Show skeleton loading only for dynamic tabs
            this.showSkeletonLoader(tabContent, tabName);
        }

        // Load content asynchronously
        this.loadDynamicContent(tabName).then(() => {
            // Mark tab as loaded
            this.loadedTabs.add(tabName);
            
            // Remove skeleton and show real content (only for dynamic tabs)
            if (!isStaticTab) {
                this.hideSkeletonLoader(tabContent);
            }
            
            // Initialize tab-specific functionality
            this.initializeTabContent(tabName);
            
        }).catch(error => {
            console.error(`Error loading tab ${tabName}:`, error);
            this.showErrorState(tabContent, tabName);
        });
    }

    async loadDynamicContent(tabName) {
        // For static tabs (already loaded), return immediately
        if (['qualidade', 'avatar', 'camera', 'camera2', 'voz', 'acao'].includes(tabName)) {
            // No delay for static tabs - they're already rendered server-side
            return;
        }

        // For dynamic tabs (ambiente, estilo_visual, iluminacao, tecnica, elementos_especiais), load from server
        try {
            const response = await fetch(`api/load_tab_content.php?tab=${tabName}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                // Update tab content with loaded data
                this.updateTabContent(tabName, data.html);
            } else {
                throw new Error(data.message || 'Failed to load content');
            }
        } catch (error) {
            // Fallback: content is already loaded server-side
            console.log(`Using server-side rendered content for ${tabName}`);
            await new Promise(resolve => setTimeout(resolve, 300));
        }
    }

    updateTabContent(tabName, html) {
        const tabContent = document.querySelector(`#tab-${tabName}`);
        if (tabContent && html) {
            const contentContainer = tabContent.querySelector('.categories-grid') || tabContent;
            contentContainer.innerHTML = html;
        }
    }

    preloadNextTab(currentTab) {
        const currentIndex = this.tabs.indexOf(currentTab);
        const nextIndex = currentIndex + 1;
        
        if (nextIndex < this.tabs.length) {
            const nextTab = this.tabs[nextIndex];
            if (!this.loadedTabs.has(nextTab)) {
                // Preload in background without showing
                setTimeout(() => {
                    this.loadedTabs.add(nextTab);
                }, 500);
            }
        }
    }

    showSkeletonLoader(tabContent, tabName) {
        const categoriesGrid = tabContent.querySelector('.categories-grid');
        if (!categoriesGrid) return;

        // Add loading class and create skeleton
        categoriesGrid.classList.add('loading');
        categoriesGrid.innerHTML = this.createSkeletonHTML(tabName);
    }

    hideSkeletonLoader(tabContent) {
        const categoriesGrid = tabContent.querySelector('.categories-grid');
        if (categoriesGrid) {
            categoriesGrid.classList.remove('loading');
        }
    }

    createSkeletonHTML(tabName) {
        const categoryCount = this.getCategoryCount(tabName);
        let skeletonHTML = '';

        for (let i = 0; i < categoryCount; i++) {
            skeletonHTML += `
                <div class="skeleton-category shimmer">
                    <div class="skeleton-category-title skeleton-loader"></div>
                    <div class="skeleton-subcategories">
                        ${this.createSkeletonSubcategories()}
                    </div>
                </div>
            `;
        }

        return skeletonHTML;
    }

    createSkeletonSubcategories() {
        let subcategoriesHTML = '';
        const subcategoryCount = 6; // Simplificado

        for (let i = 0; i < subcategoryCount; i++) {
            subcategoriesHTML += '<div class="skeleton-subcategory skeleton-loader shimmer"></div>';
        }

        return subcategoriesHTML;
    }

    getCategoryCount(tabName) {
        // Padronizado: 5 blocos para todas as abas
        return 5;
    }

    showErrorState(tabContent, tabName) {
        const categoriesGrid = tabContent.querySelector('.categories-grid');
        if (categoriesGrid) {
            categoriesGrid.classList.remove('loading');
            categoriesGrid.innerHTML = `
                <div class="content-loading">
                    <i class="material-icons" style="font-size: 4rem; color: #ef4444; margin-bottom: 1rem;">error</i>
                    <h3 style="color: #ef4444; margin-bottom: 0.5rem;">Erro ao carregar conteúdo</h3>
                    <p style="color: #64748b; text-align: center; margin-bottom: 1rem;">
                        Não foi possível carregar o conteúdo da aba ${tabName}.
                    </p>
                    <button onclick="window.location.reload()" class="btn btn-secondary">
                        <i class="material-icons">refresh</i>
                        Tentar novamente
                    </button>
                </div>
            `;
        }
    }

    showLoadingIndicator(tabContent) {
        // Fallback method for simple loading
        if (!tabContent.classList.contains('active')) {
            const loader = document.createElement('div');
            loader.className = 'content-loading';
            loader.innerHTML = `
                <div class="loading-pulse"></div>
                <div class="loading-dots">
                    <div class="loading-dot"></div>
                    <div class="loading-dot"></div>
                    <div class="loading-dot"></div>
                </div>
                <p class="loading-text">Carregando conteúdo...</p>
            `;
            tabContent.appendChild(loader);
        }
    }

    hideLoadingIndicator(tabContent) {
        const loader = tabContent.querySelector('.content-loading, .tab-loading');
        if (loader) {
            loader.remove();
        }
    }

    initializeTabContent(tabName) {
        // Initialize any tab-specific JavaScript functionality
        // With event delegation, no need to rebind events manually
        console.log(`Tab content initialized for: ${tabName}`);
    }

    selectOption(type, value, element) {
        console.log(`Card selecionado - Tipo: ${type}, Valor: ${value}`);
        
        // Limpa seleções apenas na aba atual
        const tabContainer = element.closest('.tab-content') || element.closest('.category-section');
        if (tabContainer) {
            tabContainer.querySelectorAll('.subcategory-card.selected').forEach(card => {
            card.classList.remove('selected');
        });
        } else {
            document.querySelectorAll('.subcategory-card.selected').forEach(card => card.classList.remove('selected'));
        }

        // Marca a opção selecionada
        element.classList.add('selected');

        // Armazena a seleção por tipo
        this.selections[type] = value;

        // Atualiza input oculto
        const input = document.getElementById(`selected_${type}`);
        if (input) {
            input.value = value;
            console.log(`Input atualizado: selected_${type} = ${value}`);
        } else {
            console.warn(`Input não encontrado: selected_${type}`);
        }

        // Atualiza o preview do prompt
        this.updatePromptPreview();

        // Navega para a próxima aba após curto delay
        setTimeout(() => {
            this.autoNavigateToNextTab(type);
        }, 500);
    }

    updatePromptPreview() {
        const originalPromptTextarea = document.querySelector('[name="original_prompt"]');
        const originalPrompt = originalPromptTextarea ? originalPromptTextarea.value : '';
        let enhancedPrompt = originalPrompt;

        // Add selected options to prompt
        const enhancements = [];

        if (this.selections.environment) {
            enhancements.push(`Ambiente: ${this.selections.environment.replace(/_/g, ' ')}`);
        }
        
        if (this.customDescriptions.environment) {
            enhancements.push(`Ambiente personalizado: ${this.customDescriptions.environment}`);
        }

        if (this.selections.lighting) {
            enhancements.push(`Iluminação: ${this.selections.lighting.replace(/_/g, ' ')}`);
        }
        
        if (this.customDescriptions.lighting) {
            enhancements.push(`Iluminação personalizada: ${this.customDescriptions.lighting}`);
        }

        if (this.selections.character) {
            enhancements.push(`Personagem: ${this.selections.character.replace(/_/g, ' ')}`);
        }
        
        if (this.customDescriptions.character) {
            enhancements.push(`Personagem personalizado: ${this.customDescriptions.character}`);
        }

        if (this.selections.camera) {
            enhancements.push(`Câmera: ${this.selections.camera.replace(/_/g, ' ')}`);
        }
        
        if (this.customDescriptions.camera) {
            enhancements.push(`Câmera personalizada: ${this.customDescriptions.camera}`);
        }

        if (this.selections.voice) {
            enhancements.push(`Voz: ${this.selections.voice.replace(/_/g, ' ')}`);
        }
        
        if (this.customDescriptions.voice) {
            enhancements.push(`Voz personalizada: ${this.customDescriptions.voice}`);
        }

        if (this.selections.visual_style) {
            enhancements.push(`Estilo Visual: ${this.selections.visual_style.replace(/_/g, ' ')}`);
        }
        
        if (this.customDescriptions.visual_style) {
            enhancements.push(`Estilo Visual personalizado: ${this.customDescriptions.visual_style}`);
        }

        if (this.selections.technique) {
            enhancements.push(`Técnica: ${this.selections.technique.replace(/_/g, ' ')}`);
        }
        
        if (this.customDescriptions.technique) {
            enhancements.push(`Técnica personalizada: ${this.customDescriptions.technique}`);
        }

        if (this.selections.special_elements) {
            enhancements.push(`Elementos Especiais: ${this.selections.special_elements.replace(/_/g, ' ')}`);
        }
        
        if (this.customDescriptions.special_elements) {
            enhancements.push(`Elementos Especiais personalizados: ${this.customDescriptions.special_elements}`);
        }

        if (this.selections.quality) {
            enhancements.push(`Qualidade: ${this.selections.quality.replace(/_/g, ' ')}`);
        }
        
        if (this.customDescriptions.quality) {
            enhancements.push(`Qualidade personalizada: ${this.customDescriptions.quality}`);
        }

        if (this.selections.action) {
            enhancements.push(`Ação: ${this.selections.action.replace(/_/g, ' ')}`);
        }
        
        if (this.customDescriptions.action) {
            enhancements.push(`Ação personalizada: ${this.customDescriptions.action}`);
        }

        if (enhancements.length > 0) {
            enhancedPrompt = originalPrompt + '\n\n' + enhancements.join(', ') + '.';
        }

        // Update preview and final textarea
        const previewElement = document.getElementById('enhanced-prompt-preview');
        const finalTextarea = document.getElementById('enhanced-prompt');
        
        if (previewElement) {
            previewElement.textContent = enhancedPrompt || 'O prompt aprimorado aparecerá aqui conforme você faz suas seleções...';
        }
        
        if (finalTextarea) {
            finalTextarea.value = enhancedPrompt;
        }

        // Update settings hidden input
        const settingsInput = document.getElementById('settings');
        if (settingsInput) {
            settingsInput.value = JSON.stringify({
                selections: this.selections,
                customDescriptions: this.customDescriptions
            });
        }
    }

    autoNavigateToNextTab(selectedType = null) {
        const currentTabName = this.tabs[this.currentTab];
        let nextTabName = null;
        
        console.log(`Auto-navegação - Tipo: ${selectedType}, Aba atual: ${currentTabName}`);
        
        // Definir navegação específica baseada no tipo selecionado e aba atual
        switch (selectedType) {
            case 'environment':
                if (currentTabName === 'ambiente') {
                    nextTabName = 'estilo_visual';
                }
                break;
            case 'visual_style':
                if (currentTabName === 'estilo_visual') {
                    nextTabName = 'iluminacao';
                }
                break;
            case 'lighting':
                if (currentTabName === 'iluminacao') {
                    nextTabName = 'tecnica';
                }
                break;
            case 'technique':
                if (currentTabName === 'tecnica') {
                    nextTabName = 'elementos_especiais';
                }
                break;
            case 'special_elements':
                if (currentTabName === 'elementos_especiais') {
                    nextTabName = 'qualidade';
                }
                break;
            case 'quality':
                // Última aba com navegação automática, pode ir para avatar ou permanecer
                if (currentTabName === 'qualidade') {
                    nextTabName = 'avatar';
                }
                break;
        }
        
        console.log(`Próxima aba determinada: ${nextTabName}`);
        
        // Se há uma próxima aba específica definida, navegar para ela
        if (nextTabName && this.tabs.includes(nextTabName)) {
            console.log(`Navegando para: ${nextTabName}`);
            this.showTab(nextTabName);
            this.showNavigationNotification(nextTabName);
            return;
        }
        
        // Fallback: navegação sequencial padrão
        const currentIndex = this.currentTab;
        const nextIndex = currentIndex + 1;

        if (nextIndex < this.tabs.length) {
            const nextTab = this.tabs[nextIndex];
            console.log(`Fallback - Navegando para: ${nextTab}`);
            this.showTab(nextTab);
            this.showNavigationNotification(nextTab);
        }
    }

    showNavigationNotification(tabName) {
        // Optional: Show notification when navigating to next tab
        // Implementation can be added if needed
    }

    nextTab() {
        if (this.currentTab < this.tabs.length - 1) {
            const nextTabName = this.tabs[this.currentTab + 1];
            this.showTab(nextTabName);
        }
    }

    previousTab() {
        if (this.currentTab > 0) {
            const prevTabName = this.tabs[this.currentTab - 1];
            this.showTab(prevTabName);
        }
    }
}

// Navigation functions
function nextTab() {
    if (window.promptGenerator && window.promptGenerator.currentTab < window.promptGenerator.tabs.length - 1) {
        const nextTabName = window.promptGenerator.tabs[window.promptGenerator.currentTab + 1];
        window.promptGenerator.showTab(nextTabName);
    }
}

function prevTab() {
    if (window.promptGenerator && window.promptGenerator.currentTab > 0) {
        const prevTabName = window.promptGenerator.tabs[window.promptGenerator.currentTab - 1];
        window.promptGenerator.showTab(prevTabName);
    }
}

function goToFirstTab() {
    if (window.promptGenerator) {
        window.promptGenerator.showTab('ambiente');
    }
}

function goToLastTab() {
    if (window.promptGenerator) {
        window.promptGenerator.showTab('acao');
    }
}

// Função para ajustar alinhamento dos blocos
function adjustCategoriesAlignment() {
    const categoriesGrids = document.querySelectorAll('.categories-grid');
    
    categoriesGrids.forEach(grid => {
        const categoryBlocks = grid.querySelectorAll('.category-section');
        const blockCount = categoryBlocks.length;
        
        // Remover classes anteriores
        grid.classList.remove('few-blocks', 'many-blocks');
        
        // Aplicar classe baseada na quantidade
        if (blockCount <= 3) {
            grid.classList.add('few-blocks');
        } else {
            grid.classList.add('many-blocks');
        }
    });
}

// Hide page preloader
function hidePagePreloader() {
    const preloader = document.getElementById('pagePreloader');
    if (preloader) {
        preloader.classList.add('hidden');
        setTimeout(() => {
            preloader.remove();
        }, 300);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.promptGenerator = new ModernPromptGenerator();
    
    // Check if dynamic content is loading slowly and show skeleton
    setTimeout(() => {
        const activeTab = document.querySelector('.tab-content.active');
        const categoriesGrid = activeTab?.querySelector('.categories-grid');
        
        // If categories grid is empty or has very few elements, show skeleton
        if (categoriesGrid && categoriesGrid.children.length < 2) {
            const tabName = activeTab.id?.replace('tab-', '') || 'ambiente';
            window.promptGenerator.showSkeletonLoader(activeTab, tabName);
            
            // Hide skeleton after content loads or timeout
            const checkContent = setInterval(() => {
                if (categoriesGrid.children.length > 2 && 
                    !categoriesGrid.querySelector('.skeleton-category')) {
                    window.promptGenerator.hideSkeletonLoader(activeTab);
                    clearInterval(checkContent);
                }
            }, 100);
            
            // Timeout after 3 seconds
            setTimeout(() => {
                clearInterval(checkContent);
                if (categoriesGrid.classList.contains('loading')) {
                    window.promptGenerator.hideSkeletonLoader(activeTab);
                }
            }, 3000);
        }
    }, 500); // Check after 500ms
    
    // Aplicar alinhamento correto baseado na quantidade de blocos
    adjustCategoriesAlignment();
    
    // Hide page preloader after everything is loaded
    setTimeout(hidePagePreloader, 800);
});

// Hide preloader when window is fully loaded
window.addEventListener('load', () => {
    setTimeout(hidePagePreloader, 200);
});

// ===== SISTEMA DE AVATAR/PERSONAGEM =====

// Variável global para armazenar avatares
let savedAvatars = JSON.parse(localStorage.getItem('savedAvatars')) || [];

/**
 * Alterna a exibição dos campos específicos baseado no tipo de ser selecionado
 */
function toggleSpeciesFields(avatarType) {
    // Esconder todos os campos específicos
    const allSpeciesFields = document.querySelectorAll('.species-fields');
    allSpeciesFields.forEach(field => {
        field.style.display = 'none';
    });
    
    // Controlar seção de aparência e estilo
    const appearanceSection = document.getElementById('appearance-section');
    
    if (appearanceSection) {
        if (avatarType === 'animal') {
            appearanceSection.style.display = 'none';
        } else {
            appearanceSection.style.display = 'block';
        }
    }
    
    // Mostrar campos específicos do tipo selecionado
    if (avatarType) {
        let fieldsToShow = '';
        
        switch(avatarType) {
            case 'humano':
                fieldsToShow = 'human-fields';
                break;
            case 'animal':
                fieldsToShow = 'animal-fields';
                break;
            case 'criatura_fantastica':
                fieldsToShow = 'fantasy-fields';
                break;
            case 'alien':
                fieldsToShow = 'alien-fields';
                break;
            case 'robo_android':
                fieldsToShow = 'robot-fields';
                break;
            case 'elemental':
            case 'espirito':
                fieldsToShow = 'fantasy-fields'; // Usa os mesmos campos de fantasia
                break;
            case 'hibrido':
                // Para híbridos, mostrar múltiplos campos
                document.getElementById('human-fields').style.display = 'block';
                document.getElementById('fantasy-fields').style.display = 'block';
                return;
        }
        
        if (fieldsToShow) {
            const fieldElement = document.getElementById(fieldsToShow);
            if (fieldElement) {
                fieldElement.style.display = 'block';
            }
        }
    }
}

/**
 * Coleta todos os dados do formulário de avatar
 */
function collectAvatarData() {
    const formData = {};
    
    // Informações básicas
    formData.name = document.getElementById('avatar_name')?.value || '';
    formData.type = document.getElementById('avatar_type')?.value || '';
    
    // Características humanas
    if (document.getElementById('gender')) {
        formData.gender = document.getElementById('gender').value;
        formData.age_range = document.getElementById('age_range').value;
        formData.ethnicity = document.getElementById('ethnicity').value;
        formData.body_type = document.getElementById('body_type').value;
        formData.height = document.getElementById('height').value;
        formData.hair_color = document.getElementById('hair_color').value;
        formData.eye_color = document.getElementById('eye_color').value;
        formData.profession = document.getElementById('profession').value;
    }
    
    // Características de animais
    if (document.getElementById('animal_species')) {
        formData.animal_species = document.getElementById('animal_species').value;
        formData.animal_size = document.getElementById('animal_size').value;
        formData.fur_pattern = document.getElementById('fur_pattern').value;
        formData.primary_color = document.getElementById('primary_color').value;
    }
    
    // Características fantásticas
    if (document.getElementById('fantasy_type')) {
        formData.fantasy_type = document.getElementById('fantasy_type').value;
        formData.magical_abilities = document.getElementById('magical_abilities').value;
        formData.special_features = document.getElementById('special_features').value;
    }
    
    // Características alien
    if (document.getElementById('alien_origin')) {
        formData.alien_origin = document.getElementById('alien_origin').value;
        formData.skin_texture = document.getElementById('skin_texture').value;
        formData.number_of_eyes = document.getElementById('number_of_eyes').value;
        formData.communication_method = document.getElementById('communication_method').value;
    }
    
    // Características robóticas
    if (document.getElementById('robot_type')) {
        formData.robot_type = document.getElementById('robot_type').value;
        formData.power_source = document.getElementById('power_source').value;
        formData.ai_level = document.getElementById('ai_level').value;
    }
    
    // Aparência e estilo
    formData.clothing_style = document.getElementById('clothing_style')?.value || '';
    formData.accessories = document.getElementById('accessories')?.value || '';
    formData.distinctive_marks = document.getElementById('distinctive_marks')?.value || '';
    
    return formData;
}

/**
 * Salva o avatar no localStorage
 */
function saveAvatar() {
    const avatarData = collectAvatarData();
    
    // Validação básica
    if (!avatarData.name.trim()) {
        alert('Por favor, digite um nome para o avatar.');
        document.getElementById('avatar_name').focus();
        return;
    }
    
    if (!avatarData.type) {
        alert('Por favor, selecione o tipo de ser.');
        document.getElementById('avatar_type').focus();
        return;
    }
    
    // Adicionar ID único e timestamp
    avatarData.id = Date.now().toString();
    avatarData.created_at = new Date().toISOString();
    avatarData.updated_at = new Date().toISOString();
    
    // Verificar se já existe um avatar com o mesmo nome
    const existingIndex = savedAvatars.findIndex(avatar => avatar.name.toLowerCase() === avatarData.name.toLowerCase());
    
    if (existingIndex !== -1) {
        if (confirm(`Já existe um avatar com o nome "${avatarData.name}". Deseja sobrescrever?`)) {
            avatarData.id = savedAvatars[existingIndex].id;
            avatarData.created_at = savedAvatars[existingIndex].created_at;
            savedAvatars[existingIndex] = avatarData;
        } else {
            return;
        }
    } else {
        savedAvatars.push(avatarData);
    }
    
    // Salvar no localStorage
    localStorage.setItem('savedAvatars', JSON.stringify(savedAvatars));
    
    // Atualizar a lista de avatares
    loadSavedAvatars();
    
    // Feedback visual
    showAvatarSaveSuccess(avatarData.name);
}

/**
 * Mostra feedback de sucesso ao salvar avatar
 */
function showAvatarSaveSuccess(avatarName) {
    // Criar elemento de notificação
    const notification = document.createElement('div');
    notification.className = 'avatar-save-notification';
    notification.innerHTML = `
        <i class="material-icons">check_circle</i>
        <span>Avatar "${avatarName}" salvo com sucesso!</span>
    `;
    
    // Adicionar estilos inline
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Remover após 3 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

/**
 * Gera preview do avatar baseado nos dados do formulário
 */
function previewAvatar() {
    const avatarData = collectAvatarData();
    
    if (!avatarData.name.trim()) {
        alert('Por favor, digite um nome para o avatar antes de visualizar.');
        return;
    }
    
    const preview = generateAvatarDescription(avatarData);
    
    // Criar modal de preview
    const modal = document.createElement('div');
    modal.className = 'avatar-preview-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="material-icons">visibility</i> Preview do Avatar</h3>
                <button onclick="closeAvatarPreview()" class="close-btn">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <h4>${avatarData.name}</h4>
                <div class="avatar-description">${preview}</div>
            </div>
            <div class="modal-footer">
                <button onclick="closeAvatarPreview()" class="btn btn-secondary">Fechar</button>
                <button onclick="saveAvatar(); closeAvatarPreview();" class="btn btn-primary">Salvar Avatar</button>
            </div>
        </div>
    `;
    
    // Adicionar estilos inline para o modal
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    `;
    
    document.body.appendChild(modal);
}

/**
 * Fecha o modal de preview
 */
function closeAvatarPreview() {
    const modal = document.querySelector('.avatar-preview-modal');
    if (modal) {
        document.body.removeChild(modal);
    }
}

/**
 * Gera descrição textual do avatar
 */
function generateAvatarDescription(avatarData) {
    let description = [];
    
    // Tipo de ser
    if (avatarData.type) {
        description.push(`<strong>Tipo:</strong> ${getTypeDescription(avatarData.type)}`);
    }
    
    // Características específicas baseadas no tipo
    if (avatarData.type === 'humano') {
        if (avatarData.gender) description.push(`<strong>Gênero:</strong> ${avatarData.gender}`);
        if (avatarData.age_range) description.push(`<strong>Idade:</strong> ${avatarData.age_range.replace('_', ' ')}`);
        if (avatarData.ethnicity) description.push(`<strong>Etnia:</strong> ${avatarData.ethnicity}`);
        if (avatarData.body_type) description.push(`<strong>Físico:</strong> ${avatarData.body_type}`);
        if (avatarData.height) description.push(`<strong>Altura:</strong> ${avatarData.height.replace('_', ' ')}`);
        if (avatarData.hair_color) description.push(`<strong>Cabelo:</strong> ${avatarData.hair_color.replace('_', ' ')}`);
        if (avatarData.eye_color) description.push(`<strong>Olhos:</strong> ${avatarData.eye_color}`);
        if (avatarData.profession) description.push(`<strong>Profissão:</strong> ${avatarData.profession}`);
    }
    
    if (avatarData.type === 'animal') {
        if (avatarData.animal_species) description.push(`<strong>Espécie:</strong> ${avatarData.animal_species}`);
        if (avatarData.animal_size) description.push(`<strong>Tamanho:</strong> ${avatarData.animal_size}`);
        if (avatarData.primary_color) description.push(`<strong>Cor:</strong> ${avatarData.primary_color}`);
        if (avatarData.fur_pattern) description.push(`<strong>Pelagem:</strong> ${avatarData.fur_pattern}`);
    }
    
    if (avatarData.type === 'criatura_fantastica') {
        if (avatarData.fantasy_type) description.push(`<strong>Criatura:</strong> ${avatarData.fantasy_type}`);
        if (avatarData.magical_abilities) description.push(`<strong>Habilidades:</strong> ${avatarData.magical_abilities}`);
        if (avatarData.special_features) description.push(`<strong>Características:</strong> ${avatarData.special_features}`);
    }
    
    // Estilo
    if (avatarData.clothing_style) {
        description.push(`<strong>Estilo:</strong> ${avatarData.clothing_style}`);
    }
    
    return description.join('<br>');
}

/**
 * Retorna descrição amigável do tipo de ser
 */
function getTypeDescription(type) {
    const types = {
        'humano': 'Humano',
        'animal': 'Animal',
        'criatura_fantastica': 'Criatura Fantástica',
        'alien': 'Alien/Extraterrestre',
        'robo_android': 'Robô/Android',
        'elemental': 'Elemental',
        'espirito': 'Espírito/Fantasma',
        'hibrido': 'Híbrido'
    };
    return types[type] || type;
}

/**
 * Limpa todos os campos do formulário de avatar
 */
function clearAvatarForm() {
    // Limpar todos os inputs de texto
    document.querySelectorAll('#tab-avatar input[type="text"], #tab-avatar input[type="number"]').forEach(input => {
        input.value = '';
    });
    
    // Limpar todos os selects
    document.querySelectorAll('#tab-avatar select').forEach(select => {
        select.selectedIndex = 0;
    });
    
    // Limpar todos os textareas
    document.querySelectorAll('#tab-avatar textarea').forEach(textarea => {
        textarea.value = '';
    });
    
    // Remover seleção de tipo
    document.querySelectorAll('.type-option, .type-option-compact').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Limpar container dinâmico
    const dynamicContainer = document.getElementById('dynamic-characteristics');
    if (dynamicContainer) {
        dynamicContainer.innerHTML = `
            <div class="placeholder-content">
                <i class="material-icons">touch_app</i>
                <p>Selecione um tipo de ser na etapa anterior</p>
            </div>
        `;
    }
    
    // Limpar prompt display
    const promptDisplay = document.getElementById('avatar-prompt-display');
    if (promptDisplay) {
        promptDisplay.innerHTML = `
            <div class="prompt-placeholder">
                <i class="material-icons">auto_awesome</i>
                <p>O prompt será gerado automaticamente</p>
            </div>
        `;
    }
    
    // Resetar estatísticas
    updatePromptStats('');
}

/**
 * Carrega avatares salvos e exibe na interface
 */
function loadSavedAvatars() {
    const avatarsGrid = document.getElementById('saved-avatars-list');
    if (!avatarsGrid) return;
    
    // Limpar grid mantendo apenas o placeholder
    const placeholder = avatarsGrid.querySelector('.placeholder');
    avatarsGrid.innerHTML = '';
    if (placeholder) {
        avatarsGrid.appendChild(placeholder);
    }
    
    // Adicionar avatares salvos
    savedAvatars.forEach(avatar => {
        const avatarCard = document.createElement('div');
        avatarCard.className = 'avatar-card';
        avatarCard.innerHTML = `
            <div class="avatar-preview">
                <i class="material-icons">${getAvatarIcon(avatar.type)}</i>
            </div>
            <div class="avatar-info">
                <div class="avatar-name">${avatar.name}</div>
                <div class="avatar-type">${getTypeDescription(avatar.type)}</div>
            </div>
            <div class="avatar-actions">
                <button onclick="loadAvatar('${avatar.id}')" class="btn-small">
                    <i class="material-icons">edit</i>
                </button>
                <button onclick="deleteAvatar('${avatar.id}')" class="btn-small delete">
                    <i class="material-icons">delete</i>
                </button>
            </div>
        `;
        
        avatarsGrid.appendChild(avatarCard);
    });
}

/**
 * Retorna ícone apropriado para o tipo de avatar
 */
function getAvatarIcon(type) {
    const icons = {
        'humano': 'person',
        'animal': 'pets',
        'criatura_fantastica': 'auto_fix_high',
        'alien': 'emoji_nature',
        'robo_android': 'smart_toy',
        'elemental': 'whatshot',
        'espirito': 'blur_on',
        'hibrido': 'merge_type'
    };
    return icons[type] || 'person';
}

/**
 * Carrega dados de um avatar específico no formulário
 */
function loadAvatar(avatarId) {
    const avatar = savedAvatars.find(a => a.id === avatarId);
    if (!avatar) return;
    
    // Preencher campos básicos
    if (document.getElementById('avatar_name')) {
        document.getElementById('avatar_name').value = avatar.name || '';
    }
    if (document.getElementById('avatar_type')) {
        document.getElementById('avatar_type').value = avatar.type || '';
        toggleSpeciesFields(avatar.type);
    }
    
    // Preencher campos específicos
    Object.keys(avatar).forEach(key => {
        const element = document.getElementById(key);
        if (element && avatar[key]) {
            element.value = avatar[key];
        }
    });
    
    // Scroll para o formulário
    document.querySelector('.avatar-creation-form').scrollIntoView({ behavior: 'smooth' });
}

/**
 * Deleta um avatar
 */
function deleteAvatar(avatarId) {
    const avatar = savedAvatars.find(a => a.id === avatarId);
    if (!avatar) return;
    
    if (confirm(`Tem certeza que deseja excluir o avatar "${avatar.name}"?`)) {
        savedAvatars = savedAvatars.filter(a => a.id !== avatarId);
        localStorage.setItem('savedAvatars', JSON.stringify(savedAvatars));
        loadSavedAvatars();
    }
}

/**
 * Gera prompt automaticamente baseado nos dados do avatar
 */
function generateAvatarPrompt() {
    const avatarData = collectAvatarData();
    const promptDisplay = document.getElementById('avatar-prompt-display');
    
    if (!avatarData.name || !avatarData.type) {
        promptDisplay.innerHTML = `
            <div class="prompt-placeholder">
                <i class="material-icons">warning</i>
                <p>Preencha pelo menos o nome e tipo do avatar para gerar o prompt</p>
            </div>
        `;
        updatePromptStats('');
        return;
    }
    
    // Mostrar efeito de typing
    promptDisplay.classList.add('typing');
    promptDisplay.innerHTML = 'Gerando prompt...';
    
    setTimeout(() => {
        const prompt = buildPromptFromData(avatarData);
        
        // Remover efeito de typing e mostrar prompt
        promptDisplay.classList.remove('typing');
        promptDisplay.textContent = prompt;
        
        // Atualizar estatísticas
        updatePromptStats(prompt);
        
        // Salvar prompt gerado no objeto do avatar
        avatarData.generated_prompt = prompt;
    }, 1000);
}

/**
 * Constrói o prompt baseado nos dados do avatar
 */
function buildPromptFromData(data) {
    let promptParts = [];
    
    // Tipo de ser
    if (data.type === 'humano') {
        promptParts.push(buildHumanPrompt(data));
    } else if (data.type === 'animal') {
        promptParts.push(buildAnimalPrompt(data));
    } else if (data.type === 'criatura_fantastica') {
        promptParts.push(buildFantasyPrompt(data));
    } else if (data.type === 'alien') {
        promptParts.push(buildAlienPrompt(data));
    } else if (data.type === 'robo_android') {
        promptParts.push(buildRobotPrompt(data));
    } else {
        promptParts.push(`${getTypeDescription(data.type)}`);
    }
    
    // Adicionar aparência e estilo
    if (data.clothing_style) {
        promptParts.push(`vestindo roupas no estilo ${data.clothing_style}`);
    }
    
    if (data.accessories) {
        promptParts.push(`com acessórios: ${data.accessories}`);
    }
    
    if (data.distinctive_marks) {
        promptParts.push(`marcas distintivas: ${data.distinctive_marks}`);
    }
    
    // Adicionar qualificadores de qualidade
    promptParts.push('imagem em alta resolução');
    promptParts.push('detalhes ultra realistas');
    promptParts.push('iluminação cinematográfica');
    promptParts.push('8K');
    promptParts.push('fotorrealista');
    
    return promptParts.filter(part => part.trim()).join(', ');
}

/**
 * Constrói prompt para humanos
 */
function buildHumanPrompt(data) {
    let parts = [];
    
    if (data.gender && data.age_range) {
        const genderMap = {
            'masculino': 'homem',
            'feminino': 'mulher',
            'nao_binario': 'pessoa'
        };
        const ageMap = {
            'crianca': 'criança',
            'adolescente': 'adolescente',
            'jovem_adulto': 'jovem',
            'adulto': 'adulto',
            'meia_idade': 'de meia-idade',
            'idoso': 'idoso'
        };
        parts.push(`${genderMap[data.gender] || data.gender} ${ageMap[data.age_range] || data.age_range}`);
    }
    
    if (data.ethnicity) {
        parts.push(data.ethnicity);
    }
    
    if (data.body_type) {
        const bodyMap = {
            'magro': 'magro',
            'atletico': 'atlético',
            'musculoso': 'musculoso',
            'curvilineo': 'curvilíneo',
            'robusto': 'robusto'
        };
        parts.push(`físico ${bodyMap[data.body_type] || data.body_type}`);
    }
    
    if (data.hair_color) {
        parts.push(`cabelo ${data.hair_color.replace('_', ' ')}`);
    }
    
    if (data.eye_color) {
        parts.push(`olhos ${data.eye_color}`);
    }
    
    if (data.profession) {
        parts.push(`trabalhando como ${data.profession}`);
    }
    
    return parts.join(', ');
}

/**
 * Constrói prompt para animais
 */
function buildAnimalPrompt(data) {
    let parts = [];
    
    if (data.animal_species) {
        parts.push(data.animal_species);
    }
    
    if (data.animal_size) {
        parts.push(`tamanho ${data.animal_size}`);
    }
    
    if (data.primary_color) {
        parts.push(`cor ${data.primary_color}`);
    }
    
    if (data.fur_pattern) {
        parts.push(`pelagem ${data.fur_pattern}`);
    }
    
    return parts.join(', ');
}

/**
 * Constrói prompt para criaturas fantásticas
 */
function buildFantasyPrompt(data) {
    let parts = [];
    
    if (data.fantasy_type) {
        parts.push(data.fantasy_type);
    }
    
    if (data.special_features) {
        parts.push(data.special_features);
    }
    
    if (data.magical_abilities) {
        parts.push(`com poderes: ${data.magical_abilities}`);
    }
    
    return parts.join(', ');
}

/**
 * Constrói prompt para aliens
 */
function buildAlienPrompt(data) {
    let parts = ['ser extraterrestre'];
    
    if (data.alien_origin) {
        parts.push(`do planeta ${data.alien_origin}`);
    }
    
    if (data.skin_texture) {
        parts.push(`pele ${data.skin_texture}`);
    }
    
    if (data.number_of_eyes && data.number_of_eyes > 0) {
        parts.push(`${data.number_of_eyes} olhos`);
    }
    
    if (data.communication_method) {
        parts.push(`comunicação ${data.communication_method}`);
    }
    
    return parts.join(', ');
}

/**
 * Constrói prompt para robôs
 */
function buildRobotPrompt(data) {
    let parts = [];
    
    if (data.robot_type) {
        const robotMap = {
            'android_humanoid': 'android humanoide',
            'cyborg': 'cyborg',
            'robo_industrial': 'robô industrial',
            'ia_holografica': 'IA holográfica',
            'mecha': 'mecha'
        };
        parts.push(robotMap[data.robot_type] || data.robot_type);
    }
    
    if (data.ai_level) {
        parts.push(`IA ${data.ai_level}`);
    }
    
    if (data.power_source) {
        parts.push(`energia ${data.power_source.replace('_', ' ')}`);
    }
    
    return parts.join(', ');
}

/**
 * Atualiza estatísticas do prompt
 */
function updatePromptStats(prompt) {
    const charCount = document.getElementById('character-count');
    const wordCount = document.getElementById('word-count');
    
    if (charCount) {
        charCount.textContent = prompt.length;
    }
    
    if (wordCount) {
        const words = prompt.trim() ? prompt.trim().split(/\s+/).length : 0;
        wordCount.textContent = words;
    }
}

/**
 * Copia o prompt para a área de transferência
 */
async function copyAvatarPrompt() {
    const promptDisplay = document.getElementById('avatar-prompt-display');
    const prompt = promptDisplay.textContent;
    
    if (!prompt || prompt.includes('Preencha') || prompt.includes('Gerando')) {
        showCopyFeedback('Nenhum prompt para copiar', 'error');
        return;
    }
    
    try {
        await navigator.clipboard.writeText(prompt);
        showCopyFeedback('Prompt copiado!', 'success');
        
        // Efeito visual no botão
        const copyBtn = document.querySelector('[onclick="copyAvatarPrompt()"]');
        if (copyBtn) {
            copyBtn.classList.add('copied');
            setTimeout(() => copyBtn.classList.remove('copied'), 2000);
        }
        
    } catch (err) {
        // Fallback para navegadores mais antigos
        const textArea = document.createElement('textarea');
        textArea.value = prompt;
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showCopyFeedback('Prompt copiado!', 'success');
        } catch (fallbackErr) {
            showCopyFeedback('Erro ao copiar', 'error');
        }
        
        document.body.removeChild(textArea);
    }
}

/**
 * Mostra feedback da operação de cópia
 */
function showCopyFeedback(message, type) {
    const feedback = document.createElement('div');
    feedback.className = `copy-feedback ${type}`;
    feedback.innerHTML = `
        <i class="material-icons">${type === 'success' ? 'check_circle' : 'error'}</i>
        <span>${message}</span>
    `;
    
    feedback.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)'};
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        font-size: 0.875rem;
    `;
    
    document.body.appendChild(feedback);
    
    setTimeout(() => {
        feedback.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (document.body.contains(feedback)) {
                document.body.removeChild(feedback);
            }
        }, 300);
    }, 2500);
}

// ===== FUNÇÕES PARA O NOVO LAYOUT DA ABA AVATAR =====

// Variáveis globais para o wizard
let currentStep = 1;
let totalSteps = 4;

/**
 * Seleciona tipo de ser no novo layout
 */
function selectAvatarType(element, type) {
    // Remover seleção anterior
    document.querySelectorAll('.type-option, .type-option-compact').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Selecionar novo tipo
    element.classList.add('selected');
    
    // Atualizar campo hidden
    const hiddenInput = document.getElementById('avatar_type');
    if (hiddenInput) {
        hiddenInput.value = type;
    }
    
    // Atualizar preview
    updateAvatarPreview();
    
    // Aplicar lógica existente
    toggleSpeciesFields(type);
    
    // Gerar prompt
    generateAvatarPrompt();
}

/**
 * Avança para o próximo step
 */
function nextCreationStep() {
    // Validar step atual
    if (!validateCurrentStep()) {
        return;
    }
    
    if (currentStep < totalSteps) {
        // Esconder step atual
        document.getElementById(`step-${currentStep}`).classList.remove('active');
        
        // Avançar step
        currentStep++;
        
        // Mostrar próximo step
        document.getElementById(`step-${currentStep}`).classList.add('active');
        
        // Atualizar progress bar
        updateProgressBar();
        
        // Atualizar preview
        updateAvatarPreview();
        
        // Se chegou no step 4, gerar prompt final
        if (currentStep === 4) {
            generateAvatarPrompt();
        }
    }
}

/**
 * Volta para o step anterior
 */
function prevCreationStep() {
    if (currentStep > 1) {
        // Esconder step atual
        document.getElementById(`step-${currentStep}`).classList.remove('active');
        
        // Voltar step
        currentStep--;
        
        // Mostrar step anterior
        document.getElementById(`step-${currentStep}`).classList.add('active');
        
        // Atualizar progress bar
        updateProgressBar();
    }
}

/**
 * Valida o step atual
 */
function validateCurrentStep() {
    switch(currentStep) {
        case 1:
            const avatarName = document.getElementById('avatar_name').value.trim();
            const avatarType = document.getElementById('avatar_type').value;
            
            if (!avatarName) {
                showValidationError('Por favor, digite um nome para o avatar.');
                return false;
            }
            
            if (!avatarType) {
                showValidationError('Por favor, selecione um tipo de ser.');
                return false;
            }
            
            return true;
            
        case 2:
            // Validação do step 2 (características específicas)
            return true;
            
        case 3:
            // Validação do step 3 (aparência)
            return true;
            
        default:
            return true;
    }
}

/**
 * Mostra erro de validação
 */
function showValidationError(message) {
    // Criar ou atualizar elemento de erro
    let errorElement = document.querySelector('.validation-error');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'validation-error';
        errorElement.style.cssText = `
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            margin: 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: shake 0.5s ease;
        `;
        
        const currentCard = document.querySelector(`#step-${currentStep} .card-content`);
        currentCard.insertBefore(errorElement, currentCard.firstChild);
    }
    
    errorElement.innerHTML = `
        <i class="material-icons">error</i>
        <span>${message}</span>
    `;
    
    // Remover após 4 segundos
    setTimeout(() => {
        if (errorElement.parentNode) {
            errorElement.parentNode.removeChild(errorElement);
        }
    }, 4000);
}

/**
 * Atualiza a barra de progresso
 */
function updateProgressBar() {
    const progressFill = document.getElementById('creation-progress');
    const progressSteps = document.querySelectorAll('.progress-steps .step');
    
    // Atualizar barra de progresso
    if (progressFill) {
        const progressPercentage = (currentStep / totalSteps) * 100;
        progressFill.style.width = `${progressPercentage}%`;
    }
    
    // Atualizar steps visuais
    progressSteps.forEach((step, index) => {
        if (index < currentStep) {
            step.classList.add('active');
        } else {
            step.classList.remove('active');
        }
    });
}

/**
 * Atualiza o preview do avatar
 */
function updateAvatarPreview() {
    const avatarData = collectAvatarData();
    
    // Atualizar nome
    const previewName = document.getElementById('preview-name');
    if (previewName) {
        previewName.textContent = avatarData.name || '-';
    }
    
    // Atualizar tipo
    const previewType = document.getElementById('preview-type');
    if (previewType) {
        previewType.textContent = avatarData.type ? getTypeDescription(avatarData.type) : '-';
    }
    
    // Atualizar contador de avatares criados
    const avatarsCount = document.getElementById('avatars-count');
    if (avatarsCount) {
        avatarsCount.textContent = savedAvatars.length;
    }
    
    // Atualizar contador de avatares salvos
    const savedCount = document.getElementById('saved-count');
    if (savedCount) {
        savedCount.textContent = savedAvatars.length;
    }
}

/**
 * Reseta o processo de criação
 */
function resetCreation() {
    if (confirm('Tem certeza que deseja limpar todos os dados e começar novamente?')) {
        // Voltar para o step 1
        document.getElementById(`step-${currentStep}`).classList.remove('active');
        currentStep = 1;
        document.getElementById('step-1').classList.add('active');
        
        // Limpar formulário
        clearAvatarForm();
        
        // Atualizar progress bar
        updateProgressBar();
        
        // Atualizar preview
        updateAvatarPreview();
    }
}

/**
 * Carrega características dinâmicas baseadas no tipo
 */
function loadDynamicCharacteristics(avatarType) {
    const dynamicContainer = document.getElementById('dynamic-characteristics');
    if (!dynamicContainer) return;
    
    let content = '';
    
    switch(avatarType) {
        case 'humano':
            content = `
                <div class="characteristics-grid">
                    <div class="input-group">
                        <label for="gender">Gênero</label>
                        <select id="gender" name="gender">
                            <option value="">Selecione</option>
                            <option value="masculino">Masculino</option>
                            <option value="feminino">Feminino</option>
                            <option value="nao_binario">Não-binário</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="age_range">Faixa Etária</label>
                        <select id="age_range" name="age_range">
                            <option value="">Selecione</option>
                            <option value="crianca">Criança (5-12 anos)</option>
                            <option value="adolescente">Adolescente (13-17 anos)</option>
                            <option value="jovem_adulto">Jovem Adulto (18-30 anos)</option>
                            <option value="adulto">Adulto (31-50 anos)</option>
                            <option value="idoso">Idoso (65+ anos)</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="ethnicity">Etnia</label>
                        <select id="ethnicity" name="ethnicity">
                            <option value="">Selecione</option>
                            <option value="brasileiro">Brasileiro</option>
                            <option value="caucasiano">Caucasiano</option>
                            <option value="afrodescendente">Afrodescendente</option>
                            <option value="asiatico">Asiático</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="hair_color">Cor do Cabelo</label>
                        <select id="hair_color" name="hair_color">
                            <option value="">Selecione</option>
                            <option value="preto">Preto</option>
                            <option value="castanho">Castanho</option>
                            <option value="loiro">Loiro</option>
                            <option value="ruivo">Ruivo</option>
                        </select>
                    </div>
                </div>
            `;
            break;
            
        case 'animal':
            content = `
                <div class="characteristics-grid">
                    <div class="input-group">
                        <label for="animal_species">Espécie</label>
                        <select id="animal_species" name="animal_species">
                            <option value="">Selecione</option>
                            <option value="gato">Gato</option>
                            <option value="cachorro">Cachorro</option>
                            <option value="lobo">Lobo</option>
                            <option value="leao">Leão</option>
                            <option value="aguia">Águia</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="animal_size">Tamanho</label>
                        <select id="animal_size" name="animal_size">
                            <option value="">Selecione</option>
                            <option value="pequeno">Pequeno</option>
                            <option value="medio">Médio</option>
                            <option value="grande">Grande</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="primary_color">Cor Principal</label>
                        <input type="text" id="primary_color" name="primary_color" placeholder="Ex: Marrom, Preto">
                    </div>
                </div>
            `;
            break;
            
        case 'criatura_fantastica':
            content = `
                <div class="characteristics-grid">
                    <div class="input-group">
                        <label for="fantasy_type">Tipo de Criatura</label>
                        <select id="fantasy_type" name="fantasy_type">
                            <option value="">Selecione</option>
                            <option value="elfo">Elfo</option>
                            <option value="dragao">Dragão</option>
                            <option value="vampiro">Vampiro</option>
                            <option value="anjo">Anjo</option>
                            <option value="demonio">Demônio</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="magical_abilities">Habilidades Mágicas</label>
                        <textarea id="magical_abilities" name="magical_abilities" rows="3" placeholder="Descreva as habilidades mágicas"></textarea>
                    </div>
                </div>
            `;
            break;
            
        default:
            content = `
                <div class="placeholder-content">
                    <i class="material-icons">info</i>
                    <p>Selecione um tipo de ser na etapa anterior para ver as características específicas.</p>
                </div>
            `;
    }
    
    dynamicContainer.innerHTML = content;
    
    // Adicionar listeners aos novos campos
    const newInputs = dynamicContainer.querySelectorAll('input, select, textarea');
    newInputs.forEach(input => {
        input.addEventListener('change', generateAvatarPrompt);
        input.addEventListener('input', debounce(generateAvatarPrompt, 500));
    });
}

// Inicializar sistema de avatares quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    // Carregar avatares salvos
    loadSavedAvatars();
    
    // Adicionar listeners para seleção de tipo (layout normal e compacto)
    document.querySelectorAll('.type-option, .type-option-compact').forEach(option => {
        option.addEventListener('click', function() {
            const type = this.getAttribute('data-type');
            selectAvatarType(this, type);
            loadDynamicCharacteristics(type);
        });
    });
    
    // Adicionar listener para input de nome
    const avatarNameInput = document.getElementById('avatar_name');
    if (avatarNameInput) {
        avatarNameInput.addEventListener('input', updateAvatarPreview);
    }
    
    // Inicializar preview
    updateAvatarPreview();
    updateProgressBar();
    
    // Adicionar listeners para geração automática de prompt
    const formInputs = document.querySelectorAll('#tab-avatar input, #tab-avatar select, #tab-avatar textarea');
    formInputs.forEach(input => {
        input.addEventListener('change', generateAvatarPrompt);
        input.addEventListener('input', debounce(generateAvatarPrompt, 500));
    });

    // Selecionar o primeiro bloco por padrão (Humano)
    if (avatarTypeBlocks.length > 0) {
        avatarTypeBlocks[0].click();
    }

    // Função para mostrar formulário específico do tipo de avatar
    function showAvatarForm(avatarType) {
        // Ocultar todos os formulários
        const allForms = document.querySelectorAll('.avatar-form');
        allForms.forEach(form => {
            form.style.display = 'none';
        });

        // Ocultar placeholder inicial
        const placeholder = document.getElementById('placeholder-inicial');
        if (placeholder) {
            placeholder.style.display = 'none';
        }

        // Mostrar formulário específico
        const targetForm = document.getElementById(`form-${avatarType}`);
        if (targetForm) {
            targetForm.style.display = 'block';
        }
    }

    // Adicionar event listeners para os blocos de tipo de avatar
    avatarTypeBlocks.forEach(block => {
        block.addEventListener('click', function() {
            const avatarType = this.getAttribute('data-type');
            
            // Mostrar formulário correspondente
            showAvatarForm(avatarType);
            
            // Log para debug
            console.log('Tipo de avatar selecionado:', avatarType);
        });
    });
});

// Função debounce para evitar muitas chamadas
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Função para gerar o prompt final
function gerarPrompt() {
    try {
        // Coletar todas as seleções das abas
        const promptData = {
            environment: getSelectedValue('environment'),
            visual_style: getSelectedValue('visual_style'),
            lighting: getSelectedValue('lighting'),
            technique: getSelectedValue('technique'),
            special_elements: getSelectedValue('special_elements'),
            quality: getSelectedValue('quality'),
            character: getSelectedValue('character'),
            camera: getSelectedValue('camera'),
            voice: getSelectedValue('voice'),
            action: getSelectedValue('action'),
            custom_descriptions: {
                environment: document.querySelector('[name="custom_environment"]')?.value || '',
                visual_style: document.querySelector('[name="custom_visual_style"]')?.value || '',
                lighting: document.querySelector('[name="custom_lighting"]')?.value || '',
                technique: document.querySelector('[name="custom_technique"]')?.value || '',
                special_elements: document.querySelector('[name="custom_special_elements"]')?.value || '',
                quality: document.querySelector('[name="custom_quality"]')?.value || '',
                character: document.querySelector('[name="custom_character"]')?.value || '',
                camera: document.querySelector('[name="custom_camera"]')?.value || '',
                voice: document.querySelector('[name="custom_voice"]')?.value || '',
                action: document.querySelector('[name="custom_action"]')?.value || ''
            }
        };

        // Gerar o prompt final
        const promptFinal = gerarPromptFinal(promptData);
        
        // Mostrar o prompt gerado
        mostrarPromptGerado(promptFinal);
        
        // Scroll para o topo para mostrar o resultado
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
    } catch (error) {
        console.error('Erro ao gerar prompt:', error);
        alert('Erro ao gerar o prompt. Verifique o console para mais detalhes.');
    }
}

// Função para obter o valor selecionado de um tipo específico
function getSelectedValue(type) {
    const selectedCard = document.querySelector(`.subcategory-card.selected[data-type="${type}"]`);
    return selectedCard ? selectedCard.dataset.value : null;
}

// Função para gerar o prompt final baseado nas seleções
function gerarPromptFinal(data) {
    let prompt = '';
    
    // Adicionar ambiente
    if (data.environment) {
        prompt += `Ambiente: ${data.environment}\n`;
    }
    if (data.custom_descriptions.environment) {
        prompt += `Descrição personalizada do ambiente: ${data.custom_descriptions.environment}\n`;
    }
    
    // Adicionar estilo visual
    if (data.visual_style) {
        prompt += `Estilo visual: ${data.visual_style}\n`;
    }
    if (data.custom_descriptions.visual_style) {
        prompt += `Descrição personalizada do estilo: ${data.custom_descriptions.visual_style}\n`;
    }
    
    // Adicionar iluminação
    if (data.lighting) {
        prompt += `Iluminação: ${data.lighting}\n`;
    }
    if (data.custom_descriptions.lighting) {
        prompt += `Descrição personalizada da iluminação: ${data.custom_descriptions.lighting}\n`;
    }
    
    // Adicionar técnica
    if (data.technique) {
        prompt += `Técnica: ${data.technique}\n`;
    }
    if (data.custom_descriptions.technique) {
        prompt += `Descrição personalizada da técnica: ${data.custom_descriptions.technique}\n`;
    }
    
    // Adicionar elementos especiais
    if (data.special_elements) {
        prompt += `Elementos especiais: ${data.special_elements}\n`;
    }
    if (data.custom_descriptions.special_elements) {
        prompt += `Descrição personalizada dos elementos: ${data.custom_descriptions.special_elements}\n`;
    }
    
    // Adicionar qualidade
    if (data.quality) {
        prompt += `Qualidade: ${data.quality}\n`;
    }
    if (data.custom_descriptions.quality) {
        prompt += `Descrição personalizada da qualidade: ${data.custom_descriptions.quality}\n`;
    }
    
    // Adicionar personagem
    if (data.character) {
        prompt += `Personagem: ${data.character}\n`;
    }
    if (data.custom_descriptions.character) {
        prompt += `Descrição personalizada do personagem: ${data.custom_descriptions.character}\n`;
    }
    
    // Adicionar câmera
    if (data.camera) {
        prompt += `Câmera: ${data.camera}\n`;
    }
    if (data.custom_descriptions.camera) {
        prompt += `Descrição personalizada da câmera: ${data.custom_descriptions.camera}\n`;
    }
    
    // Adicionar voz
    if (data.voice) {
        prompt += `Voz: ${data.voice}\n`;
    }
    if (data.custom_descriptions.voice) {
        prompt += `Descrição personalizada da voz: ${data.custom_descriptions.voice}\n`;
    }
    
    // Adicionar ação
    if (data.action) {
        prompt += `Ação: ${data.action}\n`;
    }
    if (data.custom_descriptions.action) {
        prompt += `Descrição personalizada da ação: ${data.action}\n`;
    }
    
    return prompt.trim() || 'Nenhuma opção selecionada. Selecione pelo menos uma opção para gerar o prompt.';
}

// Função para mostrar o prompt gerado
function mostrarPromptGerado(prompt) {
    // Criar ou atualizar o modal de resultado
    let modal = document.getElementById('prompt-result-modal');
    
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'prompt-result-modal';
        modal.className = 'prompt-result-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>🎯 Prompt Gerado</h3>
                    <button class="close-btn" onclick="fecharModalPrompt()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="prompt-content">
                        <pre>${prompt}</pre>
                    </div>
                    <div class="prompt-actions">
                        <button class="btn btn-primary" onclick="copiarPrompt('${prompt.replace(/'/g, "\\'")}')">
                            <i class="material-icons">content_copy</i>
                            Copiar Prompt
                        </button>
                        <button class="btn btn-secondary" onclick="fecharModalPrompt()">
                            <i class="material-icons">close</i>
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    } else {
        modal.querySelector('.prompt-content pre').textContent = prompt;
    }
    
    modal.style.display = 'flex';
}

// Função para fechar o modal
function fecharModalPrompt() {
    const modal = document.getElementById('prompt-result-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Função para copiar o prompt
function copiarPrompt(prompt) {
    navigator.clipboard.writeText(prompt).then(() => {
        alert('Prompt copiado para a área de transferência!');
    }).catch(err => {
        console.error('Erro ao copiar:', err);
        // Fallback para navegadores antigos
        const textArea = document.createElement('textarea');
        textArea.value = prompt;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Prompt copiado para a área de transferência!');
    });
}

// ========================================
// FUNCIONALIDADES PARA ABA AVATAR
// ========================================

// Variáveis globais para o sistema de avatares
let currentAvatarStep = 1;
let selectedAvatarType = null;
let avatarFormData = {};

// Função para mostrar o modal do criador de avatar
function showAvatarCreator() {
    const modal = document.getElementById('avatar-creator-modal');
    if (modal) {
        modal.style.display = 'flex';
        resetAvatarCreation();
    }
}

// Função para fechar o modal do criador de avatar
function closeAvatarCreator() {
    const modal = document.getElementById('avatar-creator-modal');
    if (modal) {
        modal.style.display = 'none';
        resetAvatarCreation();
    }
}

// Função para resetar a criação de avatar
function resetAvatarCreation() {
    currentAvatarStep = 1;
    selectedAvatarType = null;
    avatarFormData = {};
    
    // Resetar steps
    updateAvatarSteps();
    
    // Limpar formulário
    document.getElementById('avatar_name').value = '';
    document.getElementById('avatar_type').value = '';
    
    // Limpar seleções de tipo
    document.querySelectorAll('.type-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Mostrar apenas o primeiro step
    showAvatarStep(1);
}

// Função para atualizar os steps do avatar
function updateAvatarSteps() {
    const steps = document.querySelectorAll('.progress-steps .step');
    steps.forEach((step, index) => {
        const stepNumber = index + 1;
        step.classList.remove('active', 'completed');
        
        if (stepNumber === currentAvatarStep) {
            step.classList.add('active');
        } else if (stepNumber < currentAvatarStep) {
            step.classList.add('completed');
        }
    });
}

// Função para mostrar um step específico
function showAvatarStep(stepNumber) {
    // Esconder todos os steps
    document.querySelectorAll('.creation-step').forEach(step => {
        step.classList.remove('active');
    });
    
    // Mostrar o step atual
    const currentStep = document.getElementById(`step-${stepNumber}`);
    if (currentStep) {
        currentStep.classList.add('active');
    }
    
    // Atualizar steps
    updateAvatarSteps();
}

// Função para ir para o próximo step
function nextAvatarStep() {
    if (currentAvatarStep === 1) {
        // Validar step 1
        if (!validateAvatarStep1()) {
            return;
        }
    } else if (currentAvatarStep === 2) {
        // Validar step 2
        if (!validateAvatarStep2()) {
            return;
        }
    }
    
    if (currentAvatarStep < 3) {
        currentAvatarStep++;
        showAvatarStep(currentAvatarStep);
        
        // Se for o step 2, carregar características dinâmicas
        if (currentAvatarStep === 2) {
            loadDynamicCharacteristics();
        }
        
        // Se for o step 3, atualizar resumo
        if (currentAvatarStep === 3) {
            updateAvatarSummary();
        }
    }
}

// Função para ir para o step anterior
function prevAvatarStep() {
    if (currentAvatarStep > 1) {
        currentAvatarStep--;
        showAvatarStep(currentAvatarStep);
    }
}

// Função para validar o step 1
function validateAvatarStep1() {
    const avatarName = document.getElementById('avatar_name').value.trim();
    const avatarType = document.getElementById('avatar_type').value;
    
    if (!avatarName) {
        showAvatarError('Por favor, digite um nome para o avatar.');
        return false;
    }
    
    if (!avatarType) {
        showAvatarError('Por favor, selecione um tipo de ser.');
        return false;
    }
    
    return true;
}

// Função para validar o step 2
function validateAvatarStep2() {
    // Aqui você pode adicionar validações específicas para cada tipo de ser
    // Por enquanto, vamos apenas retornar true
    return true;
}

// Função para mostrar erro no avatar
function showAvatarError(message) {
    // Criar ou atualizar mensagem de erro
    let errorDiv = document.querySelector('.avatar-error-message');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'avatar-error-message';
        errorDiv.style.cssText = `
            background: #ef4444;
            color: white;
            padding: 1rem;
            border-radius: var(--radius);
            margin: 1rem 0;
            text-align: center;
        `;
        
        const modalBody = document.querySelector('.avatar-creator-modal .modal-body');
        if (modalBody) {
            modalBody.insertBefore(errorDiv, modalBody.firstChild);
        }
    }
    
    errorDiv.textContent = message;
    
    // Remover mensagem após 5 segundos
    setTimeout(() => {
        if (errorDiv) {
            errorDiv.remove();
        }
    }, 5000);
}

// Função para carregar características dinâmicas
function loadDynamicCharacteristics() {
    const characteristicsContainer = document.getElementById('dynamic-characteristics');
    if (!characteristicsContainer) return;
    
    const type = selectedAvatarType;
    let characteristicsHTML = '';
    
    switch (type) {
        case 'humano':
            characteristicsHTML = generateHumanCharacteristics();
            break;
        case 'animal':
            characteristicsHTML = generateAnimalCharacteristics();
            break;
        case 'fantasia':
            characteristicsHTML = generateFantasyCharacteristics();
            break;
        case 'alien':
            characteristicsHTML = generateAlienCharacteristics();
            break;
        case 'robo':
            characteristicsHTML = generateRobotCharacteristics();
            break;
        case 'elemental':
            characteristicsHTML = generateElementalCharacteristics();
            break;
        default:
            characteristicsHTML = '<div class="loading-placeholder"><p>Tipo não reconhecido</p></div>';
    }
    
    characteristicsContainer.innerHTML = characteristicsHTML;
}

// Função para gerar características humanas
function generateHumanCharacteristics() {
    return `
        <div class="characteristics-grid">
            <div class="form-group">
                <label for="human_age">Idade</label>
                <select id="human_age" name="human_age">
                    <option value="">Selecione</option>
                    <option value="crianca">Criança (5-12 anos)</option>
                    <option value="adolescente">Adolescente (13-17 anos)</option>
                    <option value="jovem">Jovem Adulto (18-30 anos)</option>
                    <option value="adulto">Adulto (31-50 anos)</option>
                    <option value="meia_idade">Meia-idade (51-65 anos)</option>
                    <option value="idoso">Idoso (65+ anos)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="human_gender">Gênero</label>
                <select id="human_gender" name="human_gender">
                    <option value="">Selecione</option>
                    <option value="masculino">Masculino</option>
                    <option value="feminino">Feminino</option>
                    <option value="nao_binario">Não-binário</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="human_ethnicity">Etnia</label>
                <select id="human_ethnicity" name="human_ethnicity">
                    <option value="">Selecione</option>
                    <option value="brasileiro">Brasileiro</option>
                    <option value="caucasiano">Caucasiano</option>
                    <option value="afrodescendente">Afrodescendente</option>
                    <option value="asiatico">Asiático</option>
                    <option value="latino">Latino</option>
                    <option value="indigena">Indígena</option>
                    <option value="misto">Miscigenado</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="human_profession">Profissão</label>
                <input type="text" id="human_profession" name="human_profession" placeholder="Ex: Médico, Artista, Estudante">
            </div>
            
            <div class="form-group">
                <label for="human_personality">Personalidade</label>
                <textarea id="human_personality" name="human_personality" rows="3" placeholder="Descreva a personalidade do personagem"></textarea>
            </div>
        </div>
    `;
}

// Função para gerar características de animais
function generateAnimalCharacteristics() {
    return `
        <div class="characteristics-grid">
            <div class="form-group">
                <label for="animal_species">Espécie</label>
                <select id="animal_species" name="animal_species">
                    <option value="">Selecione</option>
                    <option value="gato">Gato</option>
                    <option value="cachorro">Cachorro</option>
                    <option value="lobo">Lobo</option>
                    <option value="leao">Leão</option>
                    <option value="tigre">Tigre</option>
                    <option value="aguia">Águia</option>
                    <option value="coruja">Coruja</option>
                    <option value="outro">Outro</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="animal_size">Tamanho</label>
                <select id="animal_size" name="animal_size">
                    <option value="">Selecione</option>
                    <option value="miniatura">Miniatura</option>
                    <option value="pequeno">Pequeno</option>
                    <option value="medio">Médio</option>
                    <option value="grande">Grande</option>
                    <option value="gigante">Gigante</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="animal_behavior">Comportamento</label>
                <textarea id="animal_behavior" name="animal_behavior" rows="3" placeholder="Descreva o comportamento do animal"></textarea>
            </div>
        </div>
    `;
}

// Função para gerar características fantásticas
function generateFantasyCharacteristics() {
    return `
        <div class="characteristics-grid">
            <div class="form-group">
                <label for="fantasy_race">Raça Fantástica</label>
                <select id="fantasy_race" name="fantasy_race">
                    <option value="">Selecione</option>
                    <option value="elfo">Elfo</option>
                    <option value="anao">Anão</option>
                    <option value="orc">Orc</option>
                    <option value="dragao">Dragão</option>
                    <option value="vampiro">Vampiro</option>
                    <option value="fada">Fada</option>
                    <option value="outro">Outro</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="fantasy_magic">Habilidades Mágicas</label>
                <textarea id="fantasy_magic" name="fantasy_magic" rows="3" placeholder="Descreva as habilidades mágicas"></textarea>
            </div>
            
            <div class="form-group">
                <label for="fantasy_origin">Origem</label>
                <input type="text" id="fantasy_origin" name="fantasy_origin" placeholder="Ex: Reino dos Elfos, Montanhas dos Anões">
            </div>
        </div>
    `;
}

// Função para gerar características alienígenas
function generateAlienCharacteristics() {
    return `
        <div class="characteristics-grid">
            <div class="form-group">
                <label for="alien_planet">Planeta de Origem</label>
                <input type="text" id="alien_planet" name="alien_planet" placeholder="Ex: Andrômeda, Zeta Reticuli">
            </div>
            
            <div class="form-group">
                <label for="alien_technology">Nível Tecnológico</label>
                <select id="alien_technology" name="alien_technology">
                    <option value="">Selecione</option>
                    <option value="primitivo">Primitivo</option>
                    <option value="medieval">Medieval</option>
                    <option value="industrial">Industrial</option>
                    <option value="futurista">Futurista</option>
                    <option value="transcendental">Transcendental</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="alien_abilities">Habilidades Especiais</label>
                <textarea id="alien_abilities" name="alien_abilities" rows="3" placeholder="Descreva habilidades especiais"></textarea>
            </div>
        </div>
    `;
}

// Função para gerar características robóticas
function generateRobotCharacteristics() {
    return `
        <div class="characteristics-grid">
            <div class="form-group">
                <label for="robot_type">Tipo de Robô</label>
                <select id="robot_type" name="robot_type">
                    <option value="">Selecione</option>
                    <option value="android">Android Humanoide</option>
                    <option value="cyborg">Cyborg</option>
                    <option value="industrial">Robô Industrial</option>
                    <option value="hologram">IA Holográfica</option>
                    <option value="mecha">Mecha</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="robot_ai">Nível de IA</label>
                <select id="robot_ai" name="robot_ai">
                    <option value="">Selecione</option>
                    <option value="basico">Básico</option>
                    <option value="avancado">Avançado</option>
                    <option value="superinteligente">Superinteligente</option>
                    <option value="consciente">Consciente</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="robot_purpose">Propósito</label>
                <textarea id="robot_purpose" name="robot_purpose" rows="3" placeholder="Descreva o propósito do robô"></textarea>
            </div>
        </div>
    `;
}

// Função para gerar características elementais
function generateElementalCharacteristics() {
    return `
        <div class="characteristics-grid">
            <div class="form-group">
                <label for="elemental_type">Tipo Elemental</label>
                <select id="elemental_type" name="elemental_type">
                    <option value="">Selecione</option>
                    <option value="fogo">Fogo</option>
                    <option value="agua">Água</option>
                    <option value="terra">Terra</option>
                    <option value="ar">Ar</option>
                    <option value="luz">Luz</option>
                    <option value="sombra">Sombra</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="elemental_power">Nível de Poder</label>
                <select id="elemental_power" name="elemental_power">
                    <option value="">Selecione</option>
                    <option value="fraco">Fraco</option>
                    <option value="moderado">Moderado</option>
                    <option value="forte">Forte</option>
                    <option value="lendario">Lendário</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="elemental_manifestation">Manifestação</label>
                <textarea id="elemental_manifestation" name="elemental_manifestation" rows="3" placeholder="Descreva como o elemental se manifesta"></textarea>
            </div>
        </div>
    `;
}

// Função para atualizar o resumo do avatar
function updateAvatarSummary() {
    const name = document.getElementById('avatar_name').value;
    const type = selectedAvatarType;
    
    // Atualizar nome
    const summaryName = document.getElementById('summary-name');
    if (summaryName) summaryName.textContent = name || '-';
    
    // Atualizar tipo
    const summaryType = document.getElementById('summary-type');
    if (summaryType) summaryType.textContent = getTypeDisplayName(type) || '-';
    
    // Atualizar características
    const summaryCharacteristics = document.getElementById('summary-characteristics');
    if (summaryCharacteristics) {
        const characteristics = collectAvatarCharacteristics();
        summaryCharacteristics.textContent = characteristics || '-';
    }
    
    // Atualizar ícone
    const summaryIcon = document.getElementById('summary-avatar-icon');
    if (summaryIcon) {
        const iconElement = summaryIcon.querySelector('i');
        if (iconElement) {
            iconElement.className = `material-icons ${getTypeIcon(type)}`;
        }
    }
}

// Função para obter o nome de exibição do tipo
function getTypeDisplayName(type) {
    const typeNames = {
        'humano': 'Humano',
        'animal': 'Animal',
        'fantasia': 'Fantasia',
        'alien': 'Alienígena',
        'robo': 'Robô/IA',
        'elemental': 'Elemental'
    };
    return typeNames[type] || type;
}

// Função para obter o ícone do tipo
function getTypeIcon(type) {
    const typeIcons = {
        'humano': 'person',
        'animal': 'pets',
        'fantasia': 'auto_fix_high',
        'alien': 'emoji_nature',
        'robo': 'smart_toy',
        'elemental': 'whatshot'
    };
    return typeIcons[type] || 'person_outline';
}

// Função para coletar características do avatar
function collectAvatarCharacteristics() {
    const characteristics = [];
    
    // Coletar dados básicos
    const name = document.getElementById('avatar_name').value;
    if (name) characteristics.push(`Nome: ${name}`);
    
    // Coletar dados específicos do tipo
    const type = selectedAvatarType;
    if (type) {
        const typeSpecificFields = document.querySelectorAll(`[name^="${type}_"]`);
        typeSpecificFields.forEach(field => {
            if (field.value) {
                const label = field.previousElementSibling?.textContent || field.name;
                characteristics.push(`${label}: ${field.value}`);
            }
        });
    }
    
    return characteristics.join(', ');
}

// Função para criar o avatar
function createAvatar() {
    // Coletar todos os dados do formulário
    const avatarData = {
        name: document.getElementById('avatar_name').value,
        type: selectedAvatarType,
        characteristics: collectAvatarCharacteristics(),
        timestamp: new Date().toISOString()
    };
    
    // Aqui você pode implementar a lógica para salvar o avatar
    // Por exemplo, enviar para o servidor ou salvar no localStorage
    
    console.log('Avatar criado:', avatarData);
    
    // Mostrar mensagem de sucesso
    showAvatarSuccess('Avatar criado com sucesso!');
    
    // Fechar modal após 2 segundos
    setTimeout(() => {
        closeAvatarCreator();
        // Aqui você pode recarregar a lista de avatares
        loadAvatarsList();
    }, 2000);
}

// Função para mostrar sucesso
function showAvatarSuccess(message) {
    // Criar mensagem de sucesso
    const successDiv = document.createElement('div');
    successDiv.className = 'avatar-success-message';
    successDiv.style.cssText = `
        background: #10b981;
        color: white;
        padding: 1rem;
        border-radius: var(--radius);
        margin: 1rem 0;
        text-align: center;
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
    `;
    
    successDiv.innerHTML = `
        <i class="material-icons" style="margin-right: 0.5rem;">check_circle</i>
        ${message}
    `;
    
    document.body.appendChild(successDiv);
    
    // Remover após 3 segundos
    setTimeout(() => {
        if (successDiv.parentNode) {
            successDiv.parentNode.removeChild(successDiv);
        }
    }, 3000);
}

// Função para carregar lista de avatares
function loadAvatarsList() {
    // Aqui você pode implementar a lógica para carregar avatares do servidor
    // Por enquanto, vamos apenas atualizar o contador
    updateAvatarsCount();
}

// Função para atualizar contador de avatares
function updateAvatarsCount() {
    // Aqui você pode implementar a lógica para contar avatares
    // Por enquanto, vamos apenas incrementar o contador
    const countElement = document.getElementById('avatars-count');
    if (countElement) {
        const currentCount = parseInt(countElement.textContent) || 0;
        countElement.textContent = currentCount + 1;
    }
}

// Event Listeners para a aba Avatar
document.addEventListener('DOMContentLoaded', function() {
    // Seleção de tipo de ser
    document.addEventListener('click', function(e) {
        if (e.target.closest('.type-option')) {
            const typeOption = e.target.closest('.type-option');
            const type = typeOption.dataset.type;
            
            // Remover seleção anterior
            document.querySelectorAll('.type-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Selecionar nova opção
            typeOption.classList.add('selected');
            selectedAvatarType = type;
            document.getElementById('avatar_type').value = type;
        }
    });
    
    // Filtros de categoria
    document.addEventListener('click', function(e) {
        if (e.target.closest('.filter-btn')) {
            const filterBtn = e.target.closest('.filter-btn');
            const filter = filterBtn.dataset.filter;
            
            // Remover classe active de todos os botões
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Adicionar classe active ao botão clicado
            filterBtn.classList.add('active');
            
            // Aqui você pode implementar a lógica de filtro
            console.log('Filtro selecionado:', filter);
        }
    });
    
    // Campo de busca
    const searchInput = document.getElementById('avatar-search');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            // Aqui você pode implementar a lógica de busca
            console.log('Termo de busca:', searchTerm);
        });
    }
    
    // Select de ordenação
    const sortSelect = document.getElementById('avatar-sort');
    if (sortSelect) {
        sortSelect.addEventListener('change', function(e) {
            const sortValue = e.target.value;
            // Aqui você pode implementar a lógica de ordenação
            console.log('Ordenação selecionada:', sortValue);
        });
    }
});

// Fechar modal ao clicar fora dele
document.addEventListener('click', function(e) {
    const modal = document.getElementById('avatar-creator-modal');
    if (modal && e.target === modal) {
        closeAvatarCreator();
    }
});

// Funcionalidade para os blocos de tipos de avatar
document.addEventListener('DOMContentLoaded', function() {
    // Selecionar todos os blocos de tipo de avatar
    const avatarTypeBlocks = document.querySelectorAll('.avatar-type-block');
    
    // Adicionar evento de clique para cada bloco
    avatarTypeBlocks.forEach(block => {
        block.addEventListener('click', function() {
            // Remover seleção de todos os blocos
            avatarTypeBlocks.forEach(b => {
                b.classList.remove('selected');
                const checkIcon = b.querySelector('.avatar-type-check i');
                checkIcon.textContent = 'radio_button_unchecked';
                checkIcon.style.color = '';
            });
            
            // Selecionar o bloco clicado
            this.classList.add('selected');
            const checkIcon = this.querySelector('.avatar-type-check i');
            checkIcon.textContent = 'check_circle';
            checkIcon.style.color = 'var(--primary-blue)';
            
            // Armazenar o tipo selecionado (opcional)
            const selectedType = this.getAttribute('data-type');
            console.log('Tipo de avatar selecionado:', selectedType);
            
            // Aqui você pode adicionar lógica adicional quando um tipo é selecionado
            // Por exemplo, atualizar outros campos do formulário, fazer requisições, etc.
        });
    });
    
         // Selecionar o primeiro bloco por padrão (Humano)
     if (avatarTypeBlocks.length > 0) {
         avatarTypeBlocks[0].click();
     }
});