<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração de Cards - Sistema de Cenas</title>
    <link rel="stylesheet" href="assets/css/admin-cards.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="header-content">
                <h1><i class="fas fa-cards-blank"></i> Gerenciamento de Cards</h1>
                <p>Sistema de administração para blocos e cenas do gerador de prompt</p>
            </div>
            <nav class="admin-nav">
                <a href="#" class="nav-btn active" data-section="blocos">
                    <i class="fas fa-layer-group"></i> Blocos
                </a>
                <a href="#" class="nav-btn" data-section="cenas">
                    <i class="fas fa-images"></i> Cenas
                </a>
                <a href="index.php" class="nav-btn secondary">
                    <i class="fas fa-home"></i> Voltar ao Site
                </a>
            </nav>
        </header>

        <main class="admin-main">
            <!-- Seção de Blocos -->
            <section id="blocos-section" class="admin-section active">
                <div class="section-header">
                    <h2><i class="fas fa-layer-group"></i> Gerenciar Blocos de Cenas</h2>
                    <button class="btn btn-primary" onclick="abrirModalBloco()">
                        <i class="fas fa-plus"></i> Novo Bloco
                    </button>
                </div>

                <div class="filter-bar">
                    <select id="filtro-tipo-bloco" onchange="filtrarBlocos()">
                        <option value="">Todos os tipos</option>
                        <option value="ambiente">Ambiente</option>
                        <option value="iluminacao">Iluminação</option>
                        <option value="avatar">Avatar</option>
                        <option value="camera">Câmera</option>
                        <option value="voz">Voz</option>
                        <option value="acao">Ação</option>
                    </select>
                    <input type="text" id="busca-bloco" placeholder="Buscar blocos..." onkeyup="buscarBlocos()">
                </div>

                <div id="lista-blocos" class="cards-grid">
                    <!-- Blocos serão carregados via JavaScript -->
                </div>
            </section>

            <!-- Seção de Cenas -->
            <section id="cenas-section" class="admin-section">
                <div class="section-header">
                    <h2><i class="fas fa-images"></i> Gerenciar Cenas</h2>
                    <button class="btn btn-primary" onclick="abrirModalCena()">
                        <i class="fas fa-plus"></i> Nova Cena
                    </button>
                </div>

                <div class="filter-bar">
                    <select id="filtro-bloco-cena" onchange="filtrarCenas()">
                        <option value="">Todos os blocos</option>
                        <!-- Opções carregadas via JavaScript -->
                    </select>
                    <input type="text" id="busca-cena" placeholder="Buscar cenas..." onkeyup="buscarCenas()">
                </div>

                <div id="lista-cenas" class="cards-grid">
                    <!-- Cenas serão carregadas via JavaScript -->
                </div>
            </section>
        </main>
    </div>

    <!-- Modal para Blocos -->
    <div id="modal-bloco" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-bloco-titulo">Novo Bloco</h3>
                <button class="modal-close" onclick="fecharModalBloco()">&times;</button>
            </div>
            <form id="form-bloco" onsubmit="salvarBloco(event)">
                <input type="hidden" id="bloco-id" name="id">
                
                <div class="form-group">
                    <label for="bloco-titulo">Título do Bloco</label>
                    <input type="text" id="bloco-titulo" name="titulo" required maxlength="100">
                </div>

                <div class="form-group">
                    <label for="bloco-icone">Ícone (Material Icons)</label>
                    <div class="icon-input">
                        <input type="text" id="bloco-icone" name="icone" required maxlength="50" placeholder="Ex: nature, photo_camera">
                        <div class="icon-preview">
                            <i class="material-icons" id="preview-icone">star</i>
                        </div>
                    </div>
                    <small>Use nomes do Material Icons. Ex: nature, photo_camera, groups</small>
                </div>

                <div class="form-group">
                    <label for="bloco-tipo">Tipo da Aba</label>
                    <select id="bloco-tipo" name="tipo_aba" required>
                        <option value="">Selecione o tipo</option>
                        <option value="ambiente">Ambiente</option>
                        <option value="iluminacao">Iluminação</option>
                        <option value="avatar">Avatar</option>
                        <option value="camera">Câmera</option>
                        <option value="voz">Voz</option>
                        <option value="acao">Ação</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="bloco-ordem">Ordem de Exibição</label>
                    <input type="number" id="bloco-ordem" name="ordem_exibicao" min="0" value="0">
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="fecharModalBloco()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Bloco</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Cenas -->
    <div id="modal-cena" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-cena-titulo">Nova Cena</h3>
                <button class="modal-close" onclick="fecharModalCena()">&times;</button>
            </div>
            <form id="form-cena" onsubmit="salvarCena(event)">
                <input type="hidden" id="cena-id" name="id">
                
                <div class="form-group">
                    <label for="cena-bloco">Bloco</label>
                    <select id="cena-bloco" name="bloco_id" required>
                        <option value="">Selecione um bloco</option>
                        <!-- Opções carregadas via JavaScript -->
                    </select>
                </div>

                <div class="form-group">
                    <label for="cena-titulo">Título da Cena</label>
                    <input type="text" id="cena-titulo" name="titulo" required maxlength="100">
                </div>

                <div class="form-group">
                    <label for="cena-subtitulo">Subtítulo (opcional)</label>
                    <input type="text" id="cena-subtitulo" name="subtitulo" maxlength="200">
                </div>

                <div class="form-group">
                    <label for="cena-texto-prompt">Texto do Prompt</label>
                    <textarea id="cena-texto-prompt" name="texto_prompt" required rows="3" placeholder="Texto que será inserido no prompt quando a cena for selecionada"></textarea>
                </div>

                <div class="form-group">
                    <label for="cena-valor-selecao">Valor de Seleção</label>
                    <input type="text" id="cena-valor-selecao" name="valor_selecao" required maxlength="100" placeholder="Valor único para identificação (ex: floresta_densa)">
                    <small>Use apenas letras, números e underscore. Este valor deve ser único.</small>
                </div>

                <div class="form-group">
                    <label for="cena-ordem">Ordem de Exibição</label>
                    <input type="number" id="cena-ordem" name="ordem_exibicao" min="0" value="0">
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="fecharModalCena()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Cena</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Carregando...</p>
        </div>
    </div>

    <!-- Notificação -->
    <div id="notification" class="notification">
        <div class="notification-content">
            <i class="notification-icon"></i>
            <span class="notification-message"></span>
        </div>
    </div>

    <script src="assets/js/admin-cards.js"></script>
</body>
</html>