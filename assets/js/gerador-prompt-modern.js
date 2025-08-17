/**
 * Modern Prompt Generator - JavaScript
 * Sistema de geração de prompts com navegação por abas
 */

class ModernPromptGenerator {
    constructor() {
        this.currentTab = 0;
        this.tabs = ['ambiente', 'estilo_visual', 'iluminacao', 'tecnica', 'elementos_especiais', 'qualidade', 'avatar', 'camera', 'voz', 'acao'];
        this.loadedTabs = new Set(['ambiente']); // Primeira aba carregada por padrão
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
    }

    bindEvents() {
        // Tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', (e) => {
                const tabName = e.currentTarget.dataset.tab;
                this.showTab(tabName);
            });
        });

        // Subcategory cards
        document.querySelectorAll('.subcategory-card').forEach(card => {
            card.addEventListener('click', (e) => {
                const type = e.currentTarget.dataset.type;
                const value = e.currentTarget.dataset.value;
                this.selectOption(type, value, e.currentTarget);
            });
        });

        // Custom description textareas
        document.querySelectorAll('[name^="custom_"]').forEach(textarea => {
            textarea.addEventListener('input', (e) => {
                const type = e.target.name.replace('custom_', '');
                this.customDescriptions[type] = e.target.value;
                this.updatePromptPreview();
            });
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
            content.classList.toggle('active', index === tabIndex);
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

        // Show skeleton loading
        this.showSkeletonLoader(tabContent, tabName);

        // Load content asynchronously
        this.loadDynamicContent(tabName).then(() => {
            // Mark tab as loaded
            this.loadedTabs.add(tabName);
            
            // Remove skeleton and show real content
            this.hideSkeletonLoader(tabContent);
            
            // Initialize tab-specific functionality
            this.initializeTabContent(tabName);
            
        }).catch(error => {
            console.error(`Error loading tab ${tabName}:`, error);
            this.showErrorState(tabContent, tabName);
        });
    }

    async loadDynamicContent(tabName) {
        // For static tabs (already loaded), return immediately
        if (['elementos_especiais', 'qualidade', 'avatar', 'camera', 'voz', 'acao'].includes(tabName)) {
            await new Promise(resolve => setTimeout(resolve, 300)); // Simulate loading
            return;
        }

        // For dynamic tabs (ambiente, estilo_visual, iluminacao, tecnica), load from server
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
        const subcategoryCount = Math.floor(Math.random() * 4) + 4; // 4-7 subcategories

        for (let i = 0; i < subcategoryCount; i++) {
            subcategoriesHTML += '<div class="skeleton-subcategory skeleton-loader shimmer"></div>';
        }

        return subcategoriesHTML;
    }

    getCategoryCount(tabName) {
        // Return expected number of categories for each tab
        const categoryCounts = {
            'ambiente': 4,
            'estilo_visual': 3,
            'iluminacao': 3,
            'tecnica': 2,
            'elementos_especiais': 2,
            'qualidade': 2,
            'avatar': 3,
            'camera': 2,
            'voz': 2,
            'acao': 2
        };
        return categoryCounts[tabName] || 3;
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
        const tabContent = document.querySelector(`[data-tab-content="${tabName}"]`);
        if (!tabContent) return;

        // Re-bind events for the newly loaded content
        const cards = tabContent.querySelectorAll('.subcategory-card');
        cards.forEach(card => {
            if (!card.hasAttribute('data-events-bound')) {
                card.addEventListener('click', (e) => {
                    const type = e.currentTarget.dataset.type;
                    const value = e.currentTarget.dataset.value;
                    this.selectOption(type, value, e.currentTarget);
                });
                card.setAttribute('data-events-bound', 'true');
            }
        });

        // Re-bind custom description textareas
        const textareas = tabContent.querySelectorAll('[name^="custom_"]');
        textareas.forEach(textarea => {
            if (!textarea.hasAttribute('data-events-bound')) {
                textarea.addEventListener('input', (e) => {
                    const type = e.target.name.replace('custom_', '');
                    this.customDescriptions[type] = e.target.value;
                    this.updatePromptPreview();
                });
                textarea.setAttribute('data-events-bound', 'true');
            }
        });
    }

    selectOption(type, value, element) {
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
        }

        // Atualiza o preview do prompt
        this.updatePromptPreview();

        // Navega para a próxima aba após curto delay
        setTimeout(() => {
            this.autoNavigateToNextTab();
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

    autoNavigateToNextTab() {
        // Move to the next tab by index, garantindo uma seleção única por aba
        const currentIndex = this.tabs.findIndex(t => t === this.currentTab);
        const nextIndex = currentIndex >= 0 ? currentIndex + 1 : 0;

        if (nextIndex < this.tabs.length) {
            const nextTab = this.tabs[nextIndex];
            this.showTab(nextTab);
            this.showNavigationNotification(nextTab);
        } else {
            // Já na última aba, manter ou ir para a última conforme UX
            const lastIndex = this.tabs.length - 1;
            const lastTab = this.tabs[lastIndex];
            if (this.currentTab !== lastTab) {
                this.showTab(lastTab);
                this.showNavigationNotification(lastTab);
            }
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