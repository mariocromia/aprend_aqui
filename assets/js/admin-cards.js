/**
 * JavaScript para Administração de Cards
 * Sistema de gerenciamento de blocos e cenas
 */

class AdminCards {
    constructor() {
        this.blocos = [];
        this.cenas = [];
        this.filtros = {
            blocos: { tipo: '', busca: '' },
            cenas: { bloco: '', busca: '' }
        };
        
        // Cache com timestamp para otimização
        this.cache = {
            blocos: { data: null, timestamp: 0, ttl: 300000 }, // 5 minutos
            cenas: { data: null, timestamp: 0, ttl: 300000 },
            cenasCarregadas: false
        };
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupIconPreview();
        // Carregar dados apenas quando necessário (lazy loading)
        this.inicializado = false;
    }
    
    setupEventListeners() {
        // Navegação entre seções
        document.querySelectorAll('.nav-btn[data-section]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.trocarSecao(btn.dataset.section);
            });
        });
        
        // Eventos de modal
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.fecharModais();
            }
        });
        
        // Escape para fechar modais
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.fecharModais();
            }
        });
    }
    
    setupIconPreview() {
        const iconeInput = document.getElementById('bloco-icone');
        const previewIcon = document.getElementById('preview-icone');
        
        if (iconeInput && previewIcon) {
            iconeInput.addEventListener('input', (e) => {
                const iconeName = e.target.value.trim();
                previewIcon.textContent = iconeName || 'star';
            });
        }
    }
    
    async trocarSecao(secao) {
        // Atualizar navegação
        document.querySelectorAll('.nav-btn[data-section]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.section === secao);
        });
        
        // Atualizar seções
        document.querySelectorAll('.admin-section').forEach(section => {
            section.classList.toggle('active', section.id === `${secao}-section`);
        });
        
        // Carregar dados apenas quando necessário (lazy loading)
        if (!this.inicializado) {
            await this.carregarDadosIniciais();
            this.inicializado = true;
        }
        
        // Renderizar seção específica
        if (secao === 'blocos') {
            this.renderizarBlocos();
        } else if (secao === 'cenas') {
            this.renderizarCenas();
            this.carregarOpcoesBloco();
        }
    }
    
    async carregarDadosIniciais() {
        this.mostrarLoading(true);
        
        try {
            console.log('Carregamento inicial lazy - carregando apenas blocos...');
            
            // Carregar apenas blocos inicialmente para speed up
            await this.carregarBlocos();
            
            console.log('Blocos carregados:', this.blocos.length);
            
            // Carregar cenas em background (não bloqueia a UI)
            this.carregarCenasBackground();
            
        } catch (error) {
            console.error('Erro ao carregar dados iniciais:', error);
            this.mostrarNotificacao('Erro ao carregar dados: ' + error.message, 'error');
        } finally {
            this.mostrarLoading(false);
        }
    }
    
    async carregarCenasBackground() {
        try {
            console.log('Carregando cenas em background...');
            await this.carregarCenas();
            console.log('Cenas carregadas em background:', this.cenas.length);
            
            // Atualizar opções de bloco se a aba de cenas estiver ativa
            const cenasSection = document.getElementById('cenas-section');
            if (cenasSection && cenasSection.classList.contains('active')) {
                this.carregarOpcoesBloco();
            }
        } catch (error) {
            console.error('Erro ao carregar cenas em background:', error);
        }
    }
    
    async carregarBlocos() {
        // Verificar cache primeiro
        if (this.isCacheValid('blocos')) {
            console.log('Usando blocos do cache');
            this.blocos = this.cache.blocos.data;
            return;
        }
        
        try {
            console.log('Carregando blocos da API (otimizado)...');
            const response = await fetch('api/admin-cards.php?action=listar_blocos_resumo');
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.blocos = data.data || [];
                // Salvar no cache
                this.setCache('blocos', this.blocos);
                console.log('Blocos carregados e cacheados:', this.blocos.length);
            } else {
                throw new Error(data.message || 'Erro ao carregar blocos');
            }
        } catch (error) {
            console.error('Erro ao carregar blocos:', error);
            this.blocos = [];
            throw error;
        }
    }
    
    async carregarCenas() {
        // Verificar cache primeiro
        if (this.isCacheValid('cenas')) {
            console.log('Usando cenas do cache');
            this.cenas = this.cache.cenas.data;
            this.cache.cenasCarregadas = true;
            return;
        }
        
        try {
            console.log('Carregando cenas da API...');
            const response = await fetch('api/admin-cards.php?action=listar_cenas');
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.cenas = data.data || [];
                // Salvar no cache
                this.setCache('cenas', this.cenas);
                this.cache.cenasCarregadas = true;
                console.log('Cenas carregadas e cacheadas:', this.cenas.length);
            } else {
                throw new Error(data.message || 'Erro ao carregar cenas');
            }
        } catch (error) {
            console.error('Erro ao carregar cenas:', error);
            this.cenas = [];
            throw error;
        }
    }
    
    // Métodos de cache
    isCacheValid(tipo) {
        const cache = this.cache[tipo];
        if (!cache || !cache.data) return false;
        
        const agora = Date.now();
        return (agora - cache.timestamp) < cache.ttl;
    }
    
    setCache(tipo, data) {
        this.cache[tipo] = {
            data: data,
            timestamp: Date.now(),
            ttl: this.cache[tipo].ttl
        };
    }
    
    clearCache(tipo = null) {
        if (tipo) {
            this.cache[tipo] = { data: null, timestamp: 0, ttl: this.cache[tipo].ttl };
        } else {
            // Limpar todo o cache
            Object.keys(this.cache).forEach(key => {
                if (key !== 'cenasCarregadas') {
                    this.cache[key] = { data: null, timestamp: 0, ttl: this.cache[key].ttl };
                }
            });
            this.cache.cenasCarregadas = false;
        }
    }
    
    renderizarBlocos() {
        const container = document.getElementById('lista-blocos');
        if (!container) {
            console.error('Container lista-blocos não encontrado');
            return;
        }
        
        try {
            const blocosFiltrados = this.filtrarBlocos();
            
            if (blocosFiltrados.length === 0) {
                container.innerHTML = this.getEmptyState('Nenhum bloco encontrado', 'layer-group');
                return;
            }
            
            // Renderização otimizada com DocumentFragment
            this.renderizarItensOtimizado(container, blocosFiltrados, 'bloco');
        } catch (error) {
            console.error('Erro ao renderizar blocos:', error);
            container.innerHTML = this.getEmptyState('Erro ao carregar blocos', 'error');
        }
    }
    
    renderizarCenas() {
        const container = document.getElementById('lista-cenas');
        if (!container) {
            console.error('Container lista-cenas não encontrado');
            return;
        }
        
        try {
            // Verificar se cenas foram carregadas
            if (!this.cache.cenasCarregadas && this.cenas.length === 0) {
                container.innerHTML = '<div class="loading-placeholder">Carregando cenas...</div>';
                // Carregar cenas se ainda não foram carregadas
                this.carregarCenas().then(() => {
                    this.renderizarCenas(); // Re-renderizar após carregar
                });
                return;
            }
            
            const cenasFiltradas = this.filtrarCenas();
            
            if (cenasFiltradas.length === 0) {
                container.innerHTML = this.getEmptyState('Nenhuma cena encontrada', 'images');
                return;
            }
            
            // Renderização otimizada com DocumentFragment
            this.renderizarItensOtimizado(container, cenasFiltradas, 'cena');
        } catch (error) {
            console.error('Erro ao renderizar cenas:', error);
            container.innerHTML = this.getEmptyState('Erro ao carregar cenas', 'error');
        }
    }
    
    // Método otimizado para renderização
    renderizarItensOtimizado(container, itens, tipo) {
        const startTime = performance.now();
        
        // Usar DocumentFragment para melhor performance
        const fragment = document.createDocumentFragment();
        
        // Renderizar em batches para não bloquear UI
        const BATCH_SIZE = 20;
        let index = 0;
        
        const renderBatch = () => {
            const batch = itens.slice(index, index + BATCH_SIZE);
            
            batch.forEach(item => {
                const div = document.createElement('div');
                div.innerHTML = tipo === 'bloco' ? 
                    this.gerarCardBloco(item) : 
                    this.gerarCardCena(item);
                fragment.appendChild(div.firstElementChild);
            });
            
            index += BATCH_SIZE;
            
            if (index < itens.length) {
                // Usar requestAnimationFrame para não bloquear UI
                requestAnimationFrame(renderBatch);
            } else {
                // Finalizar renderização
                container.innerHTML = '';
                container.appendChild(fragment);
                
                const endTime = performance.now();
                console.log(`Renderização de ${itens.length} ${tipo}s concluída em ${(endTime - startTime).toFixed(2)}ms`);
            }
        };
        
        renderBatch();
    }
    
    gerarCardBloco(bloco) {
        const cenasCount = this.cenas.filter(c => c.bloco_id == bloco.id).length;
        
        return `
            <div class="admin-card ${!bloco.ativo ? 'card-inactive' : ''}" data-id="${bloco.id}">
                <div class="card-header">
                    <div class="card-title">
                        <i class="material-icons card-icon">${bloco.icone}</i>
                        ${bloco.titulo}
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-secondary" onclick="adminCards.editarBloco(${bloco.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="adminCards.excluirBloco(${bloco.id})" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-badge ${bloco.tipo_aba}">${this.getTipoLabel(bloco.tipo_aba)}</div>
                    <p style="margin: 1rem 0 0;">
                        <strong>${cenasCount}</strong> cena${cenasCount !== 1 ? 's' : ''} cadastrada${cenasCount !== 1 ? 's' : ''}
                    </p>
                </div>
                <div class="card-meta">
                    <span>Ordem: ${bloco.ordem_exibicao}</span>
                    <span class="${bloco.ativo ? 'text-success' : 'text-danger'}">
                        ${bloco.ativo ? 'Ativo' : 'Inativo'}
                    </span>
                </div>
            </div>
        `;
    }
    
    gerarCardCena(cena) {
        const bloco = this.blocos.find(b => b.id == cena.bloco_id);
        const blocoNome = bloco ? bloco.titulo : 'Bloco não encontrado';
        
        return `
            <div class="admin-card ${!cena.ativo ? 'card-inactive' : ''}" data-id="${cena.id}">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-image card-icon"></i>
                        ${cena.titulo}
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-secondary" onclick="adminCards.editarCena(${cena.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="adminCards.excluirCena(${cena.id})" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-badge">${blocoNome}</div>
                    ${cena.subtitulo ? `<p style="margin: 0.5rem 0; color: #64748b; font-style: italic;">${cena.subtitulo}</p>` : ''}
                    <p style="margin: 0.5rem 0;"><strong>Prompt:</strong> ${this.truncarTexto(cena.texto_prompt, 80)}</p>
                    <p style="margin: 0.5rem 0;"><strong>Valor:</strong> <code>${cena.valor_selecao}</code></p>
                </div>
                <div class="card-meta">
                    <span>Ordem: ${cena.ordem_exibicao}</span>
                    <span class="${cena.ativo ? 'text-success' : 'text-danger'}">
                        ${cena.ativo ? 'Ativo' : 'Inativo'}
                    </span>
                </div>
            </div>
        `;
    }
    
    filtrarBlocos() {
        if (!Array.isArray(this.blocos)) {
            console.warn('this.blocos não é um array:', this.blocos);
            return [];
        }
        
        return this.blocos.filter(bloco => {
            if (!bloco) return false;
            
            const filtroTipo = !this.filtros.blocos.tipo || bloco.tipo_aba === this.filtros.blocos.tipo;
            const filtroBusca = !this.filtros.blocos.busca || 
                (bloco.titulo && bloco.titulo.toLowerCase().includes(this.filtros.blocos.busca.toLowerCase()));
            
            return filtroTipo && filtroBusca;
        });
    }
    
    filtrarCenas() {
        if (!Array.isArray(this.cenas)) {
            console.warn('this.cenas não é um array:', this.cenas);
            return [];
        }
        
        return this.cenas.filter(cena => {
            if (!cena) return false;
            
            const filtroBloco = !this.filtros.cenas.bloco || cena.bloco_id == this.filtros.cenas.bloco;
            const filtroBusca = !this.filtros.cenas.busca || 
                (cena.titulo && cena.titulo.toLowerCase().includes(this.filtros.cenas.busca.toLowerCase())) ||
                (cena.texto_prompt && cena.texto_prompt.toLowerCase().includes(this.filtros.cenas.busca.toLowerCase()));
            
            return filtroBloco && filtroBusca;
        });
    }
    
    carregarOpcoesBloco() {
        const selects = ['cena-bloco', 'filtro-bloco-cena'];
        
        selects.forEach(selectId => {
            const select = document.getElementById(selectId);
            if (select) {
                const opcoes = this.blocos.map(bloco => 
                    `<option value="${bloco.id}">${bloco.titulo} (${this.getTipoLabel(bloco.tipo_aba)})</option>`
                ).join('');
                
                const placeholder = selectId === 'cena-bloco' ? 
                    '<option value="">Selecione um bloco</option>' : 
                    '<option value="">Todos os blocos</option>';
                
                select.innerHTML = placeholder + opcoes;
            }
        });
    }
    
    getTipoLabel(tipo) {
        const tipos = {
            'ambiente': 'Ambiente',
            'iluminacao': 'Iluminação',
            'avatar': 'Avatar',
            'camera': 'Câmera',
            'voz': 'Voz',
            'acao': 'Ação'
        };
        return tipos[tipo] || tipo;
    }
    
    truncarTexto(texto, limite) {
        if (!texto) return '';
        return texto.length > limite ? texto.substring(0, limite) + '...' : texto;
    }
    
    getEmptyState(mensagem, icone) {
        return `
            <div class="empty-state">
                <i class="fas fa-${icone}"></i>
                <h3>${mensagem}</h3>
                <p>Comece criando um novo item usando o botão acima.</p>
            </div>
        `;
    }
    
    // Eventos de filtro
    aplicarFiltroBlocos() {
        const filtroTipoEl = document.getElementById('filtro-tipo-bloco');
        if (filtroTipoEl) {
            this.filtros.blocos.tipo = filtroTipoEl.value;
        }
        this.renderizarBlocos();
    }
    
    aplicarBuscaBlocos() {
        const buscaEl = document.getElementById('busca-bloco');
        if (buscaEl) {
            this.filtros.blocos.busca = buscaEl.value;
        }
        this.renderizarBlocos();
    }
    
    aplicarFiltroCenas() {
        const filtroBlocoEl = document.getElementById('filtro-bloco-cena');
        if (filtroBlocoEl) {
            this.filtros.cenas.bloco = filtroBlocoEl.value;
        }
        this.renderizarCenas();
    }
    
    aplicarBuscaCenas() {
        const buscaEl = document.getElementById('busca-cena');
        if (buscaEl) {
            this.filtros.cenas.busca = buscaEl.value;
        }
        this.renderizarCenas();
    }
    
    // Gerenciamento de Blocos
    abrirModalBloco(bloco = null) {
        const modal = document.getElementById('modal-bloco');
        const form = document.getElementById('form-bloco');
        const titulo = document.getElementById('modal-bloco-titulo');
        
        form.reset();
        
        if (bloco) {
            titulo.textContent = 'Editar Bloco';
            document.getElementById('bloco-id').value = bloco.id;
            document.getElementById('bloco-titulo').value = bloco.titulo;
            document.getElementById('bloco-icone').value = bloco.icone;
            document.getElementById('bloco-tipo').value = bloco.tipo_aba;
            document.getElementById('bloco-ordem').value = bloco.ordem_exibicao;
            document.getElementById('preview-icone').textContent = bloco.icone;
        } else {
            titulo.textContent = 'Novo Bloco';
            document.getElementById('bloco-id').value = '';
            document.getElementById('preview-icone').textContent = 'star';
        }
        
        modal.classList.add('active');
    }
    
    fecharModalBloco() {
        document.getElementById('modal-bloco').classList.remove('active');
    }
    
    async salvarBloco(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const blocoId = formData.get('id');
        
        const data = {
            action: blocoId ? 'atualizar_bloco' : 'criar_bloco',
            id: blocoId || undefined,
            titulo: formData.get('titulo'),
            icone: formData.get('icone'),
            tipo_aba: formData.get('tipo_aba'),
            ordem_exibicao: parseInt(formData.get('ordem_exibicao')) || 0,
            ativo: true
        };
        
        this.mostrarLoading(true);
        
        try {
            const response = await fetch('api/admin-cards.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.mostrarNotificacao(
                    blocoId ? 'Bloco atualizado com sucesso!' : 'Bloco criado com sucesso!',
                    'success'
                );
                this.fecharModalBloco();
                // Limpar cache e recarregar
                this.clearCache('blocos');
                await this.carregarBlocos();
                this.renderizarBlocos();
            } else {
                this.mostrarNotificacao(result.message || 'Erro ao salvar bloco', 'error');
            }
        } catch (error) {
            console.error('Erro ao salvar bloco:', error);
            this.mostrarNotificacao('Erro de conexão', 'error');
        } finally {
            this.mostrarLoading(false);
        }
    }
    
    editarBloco(id) {
        const bloco = this.blocos.find(b => b.id == id);
        if (bloco) {
            this.abrirModalBloco(bloco);
        }
    }
    
    async excluirBloco(id) {
        const bloco = this.blocos.find(b => b.id == id);
        if (!bloco) return;
        
        const cenasCount = this.cenas.filter(c => c.bloco_id == id).length;
        let mensagem = `Tem certeza que deseja excluir o bloco "${bloco.titulo}"?`;
        
        if (cenasCount > 0) {
            mensagem += `\n\nEste bloco possui ${cenasCount} cena(s) que também serão excluída(s).`;
        }
        
        if (!confirm(mensagem)) return;
        
        this.mostrarLoading(true);
        
        try {
            const response = await fetch('api/admin-cards.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'excluir_bloco',
                    id: id
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.mostrarNotificacao('Bloco excluído com sucesso!', 'success');
                // Limpar cache e recarregar
                this.clearCache();
                await this.carregarDadosIniciais();
                this.renderizarBlocos();
            } else {
                this.mostrarNotificacao(result.message || 'Erro ao excluir bloco', 'error');
            }
        } catch (error) {
            console.error('Erro ao excluir bloco:', error);
            this.mostrarNotificacao('Erro de conexão', 'error');
        } finally {
            this.mostrarLoading(false);
        }
    }
    
    // Gerenciamento de Cenas
    abrirModalCena(cena = null) {
        const modal = document.getElementById('modal-cena');
        const form = document.getElementById('form-cena');
        const titulo = document.getElementById('modal-cena-titulo');
        
        form.reset();
        this.carregarOpcoesBloco();
        
        if (cena) {
            titulo.textContent = 'Editar Cena';
            document.getElementById('cena-id').value = cena.id;
            document.getElementById('cena-bloco').value = cena.bloco_id;
            document.getElementById('cena-titulo').value = cena.titulo;
            document.getElementById('cena-subtitulo').value = cena.subtitulo || '';
            document.getElementById('cena-texto-prompt').value = cena.texto_prompt;
            document.getElementById('cena-valor-selecao').value = cena.valor_selecao;
            document.getElementById('cena-ordem').value = cena.ordem_exibicao;
        } else {
            titulo.textContent = 'Nova Cena';
            document.getElementById('cena-id').value = '';
        }
        
        modal.classList.add('active');
    }
    
    fecharModalCena() {
        document.getElementById('modal-cena').classList.remove('active');
    }
    
    async salvarCena(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const cenaId = formData.get('id');
        
        const data = {
            action: cenaId ? 'atualizar_cena' : 'criar_cena',
            id: cenaId || undefined,
            bloco_id: parseInt(formData.get('bloco_id')),
            titulo: formData.get('titulo'),
            subtitulo: formData.get('subtitulo') || null,
            texto_prompt: formData.get('texto_prompt'),
            valor_selecao: formData.get('valor_selecao'),
            ordem_exibicao: parseInt(formData.get('ordem_exibicao')) || 0,
            ativo: true
        };
        
        this.mostrarLoading(true);
        
        try {
            const response = await fetch('api/admin-cards.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.mostrarNotificacao(
                    cenaId ? 'Cena atualizada com sucesso!' : 'Cena criada com sucesso!',
                    'success'
                );
                this.fecharModalCena();
                await this.carregarCenas();
                this.renderizarCenas();
            } else {
                this.mostrarNotificacao(result.message || 'Erro ao salvar cena', 'error');
            }
        } catch (error) {
            console.error('Erro ao salvar cena:', error);
            this.mostrarNotificacao('Erro de conexão', 'error');
        } finally {
            this.mostrarLoading(false);
        }
    }
    
    editarCena(id) {
        const cena = this.cenas.find(c => c.id == id);
        if (cena) {
            this.abrirModalCena(cena);
        }
    }
    
    async excluirCena(id) {
        const cena = this.cenas.find(c => c.id == id);
        if (!cena) return;
        
        if (!confirm(`Tem certeza que deseja excluir a cena "${cena.titulo}"?`)) return;
        
        this.mostrarLoading(true);
        
        try {
            const response = await fetch('api/admin-cards.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'excluir_cena',
                    id: id
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.mostrarNotificacao('Cena excluída com sucesso!', 'success');
                await this.carregarCenas();
                this.renderizarCenas();
            } else {
                this.mostrarNotificacao(result.message || 'Erro ao excluir cena', 'error');
            }
        } catch (error) {
            console.error('Erro ao excluir cena:', error);
            this.mostrarNotificacao('Erro de conexão', 'error');
        } finally {
            this.mostrarLoading(false);
        }
    }
    
    // Utilitários
    fecharModais() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
    }
    
    mostrarLoading(mostrar) {
        const overlay = document.getElementById('loading-overlay');
        if (mostrar) {
            overlay.classList.add('active');
        } else {
            overlay.classList.remove('active');
        }
    }
    
    mostrarNotificacao(mensagem, tipo = 'info') {
        const notification = document.getElementById('notification');
        const icon = notification.querySelector('.notification-icon');
        const message = notification.querySelector('.notification-message');
        
        // Definir ícone baseado no tipo
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        
        icon.className = `notification-icon ${icons[tipo]}`;
        message.textContent = mensagem;
        
        // Remover classes de tipo anteriores e adicionar nova
        notification.className = `notification ${tipo}`;
        
        // Mostrar notificação
        notification.classList.add('show');
        
        // Ocultar após 5 segundos
        setTimeout(() => {
            notification.classList.remove('show');
        }, 5000);
    }
}

// Funções globais para uso nos eventos onclick
let adminCards;

function abrirModalBloco() {
    adminCards.abrirModalBloco();
}

function fecharModalBloco() {
    adminCards.fecharModalBloco();
}

function salvarBloco(event) {
    adminCards.salvarBloco(event);
}

function abrirModalCena() {
    adminCards.abrirModalCena();
}

function fecharModalCena() {
    adminCards.fecharModalCena();
}

function salvarCena(event) {
    adminCards.salvarCena(event);
}

function filtrarBlocos() {
    adminCards.aplicarFiltroBlocos();
}

function buscarBlocos() {
    adminCards.aplicarBuscaBlocos();
}

function filtrarCenas() {
    adminCards.aplicarFiltroCenas();
}

function buscarCenas() {
    adminCards.aplicarBuscaCenas();
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    adminCards = new AdminCards();
});