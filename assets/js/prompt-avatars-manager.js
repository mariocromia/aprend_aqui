/**
 * GERENCIADOR DE AVATARES NO PROMPT
 * Sistema para adicionar, remover e gerenciar avatares no prompt atual
 */

class PromptAvatarsManager {
    constructor() {
        this.promptAvatars = [];
        this.isCollapsed = false;
        this.maxAvatars = 10; // Limite máximo de avatares por prompt
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.updateDisplay();
    }
    
    // ===== EVENT BINDING =====
    bindEvents() {
        // Toggle collapse/expand
        document.getElementById('toggle-prompt-avatars')?.addEventListener('click', this.toggleCollapse.bind(this));
        document.querySelector('.prompt-avatars-header')?.addEventListener('click', this.toggleCollapse.bind(this));
        
        // Clear all avatars
        document.getElementById('clear-all-avatars')?.addEventListener('click', this.clearAllAvatars.bind(this));
        
        // Prompt actions
        document.getElementById('regenerate-combined-prompt')?.addEventListener('click', this.regenerateCombinedPrompt.bind(this));
        document.getElementById('copy-combined-prompt')?.addEventListener('click', this.copyCombinedPrompt.bind(this));
    }
    
    // ===== AVATAR MANAGEMENT =====
    addAvatar(avatar) {
        // Check if avatar already exists
        if (this.promptAvatars.find(a => a.id === avatar.id)) {
            this.showNotification('Avatar já está no prompt', 'info');
            return false;
        }
        
        // Check max limit
        if (this.promptAvatars.length >= this.maxAvatars) {
            this.showNotification(`Máximo de ${this.maxAvatars} avatares por prompt`, 'error');
            return false;
        }
        
        // Add avatar
        this.promptAvatars.push({
            ...avatar,
            addedAt: new Date().toISOString(),
            promptPart: this.generatePromptPart(avatar)
        });
        
        this.updateDisplay();
        this.showNotification(`"${avatar.name}" adicionado ao prompt`, 'success');
        
        // Expand if collapsed
        if (this.isCollapsed) {
            this.toggleCollapse();
        }
        
        return true;
    }
    
    removeAvatar(avatarId) {
        const index = this.promptAvatars.findIndex(a => a.id === avatarId);
        if (index === -1) return false;
        
        const removedAvatar = this.promptAvatars[index];
        
        // Add removing animation class
        const avatarElement = document.querySelector(`[data-prompt-avatar-id="${avatarId}"]`);
        if (avatarElement) {
            avatarElement.classList.add('removing');
            
            // Remove after animation
            setTimeout(() => {
                this.promptAvatars.splice(index, 1);
                this.updateDisplay();
                this.showNotification(`"${removedAvatar.name}" removido do prompt`, 'success');
            }, 300);
        } else {
            this.promptAvatars.splice(index, 1);
            this.updateDisplay();
            this.showNotification(`"${removedAvatar.name}" removido do prompt`, 'success');
        }
        
        return true;
    }
    
    clearAllAvatars() {
        if (this.promptAvatars.length === 0) {
            this.showNotification('Nenhum avatar para remover', 'info');
            return;
        }
        
        if (confirm(`Remover todos os ${this.promptAvatars.length} avatares do prompt?`)) {
            this.promptAvatars = [];
            this.updateDisplay();
            this.showNotification('Todos os avatares foram removidos', 'success');
        }
    }
    
    // ===== DISPLAY MANAGEMENT =====
    updateDisplay() {
        this.updateCounter();
        this.updateAvatarsList();
        this.updateCombinedPrompt();
        this.updateEmptyState();
    }
    
    updateCounter() {
        const counter = document.getElementById('prompt-avatars-count');
        if (counter) {
            counter.textContent = this.promptAvatars.length;
        }
    }
    
    updateAvatarsList() {
        const list = document.getElementById('prompt-avatars-list');
        if (!list) return;
        
        list.innerHTML = '';
        
        this.promptAvatars.forEach(avatar => {
            const item = this.createAvatarItem(avatar);
            list.appendChild(item);
        });
    }
    
    createAvatarItem(avatar) {
        const item = document.createElement('div');
        item.className = 'prompt-avatar-item';
        item.dataset.promptAvatarId = avatar.id;
        
        const typeIcons = {
            humano: 'person',
            animal: 'pets',
            fantastico: 'auto_fix_high',
            extraterrestre: 'rocket_launch',
            robotico: 'smart_toy'
        };
        
        item.innerHTML = `
            <div class="prompt-avatar-icon">
                <i class="material-icons">${typeIcons[avatar.type] || 'person'}</i>
            </div>
            <div class="prompt-avatar-info">
                <h4 class="prompt-avatar-name">${this.escapeHtml(avatar.name)}</h4>
                <p class="prompt-avatar-type">${this.capitalize(avatar.type)}</p>
                <p class="prompt-avatar-preview">${this.truncate(avatar.promptPart, 80)}</p>
            </div>
            <div class="prompt-avatar-actions">
                <button class="btn-icon btn-edit" title="Editar prompt" data-action="edit">
                    <i class="material-icons">edit</i>
                </button>
                <button class="btn-icon btn-remove" title="Remover do prompt" data-action="remove">
                    <i class="material-icons">close</i>
                </button>
            </div>
        `;
        
        // Bind events
        const editBtn = item.querySelector('[data-action="edit"]');
        const removeBtn = item.querySelector('[data-action="remove"]');
        
        editBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.editAvatarPrompt(avatar.id);
        });
        
        removeBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.removeAvatar(avatar.id);
        });
        
        return item;
    }
    
    updateEmptyState() {
        const emptyState = document.getElementById('prompt-avatars-empty');
        const list = document.getElementById('prompt-avatars-list');
        
        if (emptyState && list) {
            if (this.promptAvatars.length === 0) {
                emptyState.style.display = 'block';
                list.style.display = 'none';
            } else {
                emptyState.style.display = 'none';
                list.style.display = 'flex';
            }
        }
    }
    
    updateCombinedPrompt() {
        const section = document.getElementById('prompt-preview-section');
        const display = document.getElementById('combined-prompt-display');
        
        if (!section || !display) return;
        
        if (this.promptAvatars.length > 0) {
            section.style.display = 'block';
            const combinedPrompt = this.generateCombinedPrompt();
            display.textContent = combinedPrompt;
        } else {
            section.style.display = 'none';
        }
    }
    
    // ===== PROMPT GENERATION =====
    generatePromptPart(avatar) {
        const parts = [];
        
        // Nome
        if (avatar.name) {
            parts.push(avatar.name);
        }
        
        // Características básicas
        if (avatar.gender && avatar.gender !== 'neutro') {
            parts.push(avatar.gender);
        }
        
        // Handle numeric age or age ranges
        if (avatar.age) {
            if (typeof avatar.age === 'number') {
                parts.push(`${avatar.age} anos`);
            } else if (avatar.age !== 'adulto') {
                parts.push(avatar.age);
            }
        }
        
        // Tipo
        parts.push(avatar.type);
        
        // Add physical characteristics for humans
        if (avatar.type === 'humano' && avatar.characteristics) {
            const physicalTraits = [];
            
            if (avatar.characteristics.cor_pele) {
                physicalTraits.push(`pele ${avatar.characteristics.cor_pele}`);
            }
            if (avatar.characteristics.altura) {
                physicalTraits.push(`altura ${avatar.characteristics.altura}`);
            }
            if (avatar.characteristics.peso) {
                physicalTraits.push(`peso ${avatar.characteristics.peso}`);
            }
            if (avatar.characteristics.cor_cabelo) {
                physicalTraits.push(`cabelo ${avatar.characteristics.cor_cabelo}`);
            }
            if (avatar.characteristics.tamanho_cabelo) {
                physicalTraits.push(`cabelo ${avatar.characteristics.tamanho_cabelo}`);
            }
            if (avatar.characteristics.tipo_corte) {
                physicalTraits.push(`corte ${avatar.characteristics.tipo_corte}`);
            }
            if (avatar.characteristics.cor_olhos) {
                physicalTraits.push(`olhos ${avatar.characteristics.cor_olhos}`);
            }
            if (avatar.characteristics.detalhes_fisicos) {
                physicalTraits.push(avatar.characteristics.detalhes_fisicos);
            }
            
            parts.push(...physicalTraits);
        } else {
            // Add characteristics for other types
            if (avatar.characteristics) {
                Object.entries(avatar.characteristics).forEach(([key, value]) => {
                    if (value) {
                        parts.push(`${value}`);
                    }
                });
            }
        }
        
        // Descrição
        if (avatar.description) {
            parts.push(avatar.description);
        }
        
        // Tags mais relevantes (máximo 3)
        if (avatar.tags && avatar.tags.length > 0) {
            const relevantTags = avatar.tags.slice(0, 3);
            parts.push(...relevantTags);
        }
        
        return parts.join(', ');
    }
    
    generateCombinedPrompt() {
        if (this.promptAvatars.length === 0) return '';
        
        if (this.promptAvatars.length === 1) {
            return this.promptAvatars[0].promptPart;
        }
        
        // Multiple avatars - create a scene
        const avatarDescriptions = this.promptAvatars.map(avatar => 
            `${avatar.name} (${avatar.type}): ${avatar.promptPart}`
        );
        
        return `Cena com múltiplos personagens:\n\n${avatarDescriptions.join('\n\n')}`;
    }
    
    regenerateCombinedPrompt() {
        // Regenerate prompt parts for all avatars
        this.promptAvatars.forEach(avatar => {
            avatar.promptPart = this.generatePromptPart(avatar);
        });
        
        this.updateDisplay();
        this.showNotification('Prompt regenerado', 'success');
    }
    
    copyCombinedPrompt() {
        const combinedPrompt = this.generateCombinedPrompt();
        
        if (!combinedPrompt) {
            this.showNotification('Nenhum prompt para copiar', 'info');
            return;
        }
        
        navigator.clipboard.writeText(combinedPrompt).then(() => {
            this.showNotification('Prompt copiado para a área de transferência', 'success');
        }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = combinedPrompt;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            this.showNotification('Prompt copiado para a área de transferência', 'success');
        });
    }
    
    // ===== UI INTERACTIONS =====
    toggleCollapse() {
        this.isCollapsed = !this.isCollapsed;
        
        const manager = document.getElementById('prompt-avatars-manager');
        if (manager) {
            manager.classList.toggle('collapsed', this.isCollapsed);
        }
        
        // Update icon
        const toggleIcon = document.querySelector('#toggle-prompt-avatars i');
        if (toggleIcon) {
            toggleIcon.textContent = this.isCollapsed ? 'expand_more' : 'expand_less';
        }
    }
    
    editAvatarPrompt(avatarId) {
        const avatar = this.promptAvatars.find(a => a.id === avatarId);
        if (!avatar) return;
        
        const newPrompt = prompt('Editar prompt do avatar:', avatar.promptPart);
        
        if (newPrompt !== null && newPrompt.trim() !== '') {
            avatar.promptPart = newPrompt.trim();
            this.updateDisplay();
            this.showNotification(`Prompt de "${avatar.name}" atualizado`, 'success');
        }
    }
    
    // ===== INTEGRATION METHODS =====
    getAvatarIds() {
        return this.promptAvatars.map(a => a.id);
    }
    
    getAvatarsCount() {
        return this.promptAvatars.length;
    }
    
    hasAvatar(avatarId) {
        return this.promptAvatars.some(a => a.id === avatarId);
    }
    
    getCombinedPrompt() {
        return this.generateCombinedPrompt();
    }
    
    exportAvatars() {
        return this.promptAvatars.map(avatar => ({
            id: avatar.id,
            name: avatar.name,
            type: avatar.type,
            promptPart: avatar.promptPart,
            addedAt: avatar.addedAt
        }));
    }
    
    importAvatars(avatars) {
        this.promptAvatars = [...avatars];
        this.updateDisplay();
        this.showNotification(`${avatars.length} avatares importados`, 'success');
    }
    
    // ===== NOTIFICATIONS =====
    showNotification(message, type = 'info') {
        // Remove existing notification
        const existing = document.querySelector('.prompt-notification');
        if (existing) {
            existing.remove();
        }
        
        // Create notification
        const notification = document.createElement('div');
        notification.className = `prompt-notification ${type}`;
        notification.textContent = message;
        
        // Add to DOM
        document.body.appendChild(notification);
        
        // Trigger animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Remove after delay
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 3000);
    }
    
    // ===== UTILITIES =====
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    capitalize(text) {
        return text.charAt(0).toUpperCase() + text.slice(1);
    }
    
    truncate(text, length) {
        if (text.length <= length) return text;
        return text.substring(0, length) + '...';
    }
}

// Global instance for integration
window.promptAvatarsManager = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.promptAvatarsManager = new PromptAvatarsManager();
});