<?php
session_start();

// Iniciar otimização de recursos primeiro
require_once 'includes/ResourceOptimizer.php';
ResourceOptimizer::startPageOptimization();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Carregar apenas Environment para velocidade
require_once 'includes/Environment.php';

// Carregar sistema de cenas dinâmicas
require_once 'includes/CenaManager.php';
require_once 'includes/CenaRendererPrompt.php';

// Inicializar renderer de cenas
try {
    $cenaManager = new CenaManager();
    $cenaRenderer = new CenaRendererPrompt($cenaManager);
} catch (Exception $e) {
    error_log("Erro ao inicializar sistema de cenas: " . $e->getMessage());
    $cenaRenderer = null;
}

// Processar salvamento de prompt apenas quando necessário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_prompt') {
    try {
        require_once 'includes/PromptManager.php';
        $promptManager = new PromptManager();
        
        $result = $promptManager->saveUserPrompt($_SESSION['usuario_id'], [
            'title' => $_POST['title'] ?? 'Prompt sem título',
            'original_prompt' => $_POST['original_prompt'] ?? '',
            'enhanced_prompt' => $_POST['enhanced_prompt'] ?? '',
            'settings' => $_POST['settings'] ?? '{}',
            'environment' => $_POST['selected_environment'] ?? null,
            'lighting' => $_POST['selected_lighting'] ?? null,
            'character' => $_POST['selected_character'] ?? null,
            'camera' => $_POST['selected_camera'] ?? null,
            'voice' => $_POST['selected_voice'] ?? null,
            'action' => $_POST['selected_action'] ?? null,
            'custom_descriptions' => json_encode([
                'environment' => $_POST['custom_environment'] ?? '',
                'lighting' => $_POST['custom_lighting'] ?? '',
                'character' => $_POST['custom_character'] ?? '',
                'camera' => $_POST['custom_camera'] ?? '',
                'voice' => $_POST['custom_voice'] ?? '',
                'action' => $_POST['custom_action'] ?? ''
            ])
        ]);
        
        if ($result) {
            $success_message = 'Prompt salvo com sucesso!';
        } else {
            $error_message = 'Erro ao salvar prompt.';
        }
    } catch (Exception $e) {
        error_log("Erro ao salvar prompt: " . $e->getMessage());
        $error_message = 'Erro interno. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Prompts IA - Design Moderno - Gerador de Prompt - AprendAqui</title>
    
    <link rel="stylesheet" href="assets/css/gerador-prompt-modern.css">
    <link rel="stylesheet" href="assets/css/avatar-compact-styles.css">
    <link rel="stylesheet" href="assets/css/avatar-manager-compact-modern.css">
    
    <!-- Preloader para carregamento inicial -->
    <style>
        .page-preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--bg-primary, #0f172a);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 9999;
            transition: opacity 0.3s ease;
        }
        
        .preloader-logo {
            width: 80px;
            height: 80px;
            border: 4px solid rgba(59, 130, 246, 0.3);
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }
        
        .preloader-text {
            color: #cbd5e1;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .preloader-subtext {
            color: #94a3b8;
            font-size: 14px;
        }
        
        .page-preloader.hidden {
            opacity: 0;
            pointer-events: none;
        }
        
        /* Estilos específicos do Avatar Manager Compacto Moderno integrado */
        .tab-content .avatar-manager-compact-modern {
            height: auto;
            min-height: 600px;
            margin: 0;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        /* Otimizações para integração com as abas */
        .tab-content#tab-avatar {
            padding: 2rem;
        }
        
        .tab-content#tab-avatar .avatars-content-area {
            margin: 0;
        }
        
        .tab-content#tab-avatar .bottom-controls-container {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Estilos da toolbar de avatares */
        .avatars-toolbar-modern {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            margin: 2rem 0;
            backdrop-filter: blur(10px);
        }
        
        /* Barra de pesquisa (1/3 da largura) */
        .search-container {
            flex: 1;
            max-width: 33.333%;
            min-width: 280px;
        }
        
        .search-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .search-icon {
            position: absolute;
            left: 12px;
            color: var(--text-muted, #64748b);
            font-size: 20px;
            z-index: 1;
        }
        
        .search-input-modern {
            width: 100%;
            padding: 12px 16px 12px 44px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 8px;
            color: var(--text-primary, #e2e8f0);
            font-size: 14px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .search-input-modern::placeholder {
            color: var(--text-muted, #64748b);
        }
        
        .search-input-modern:focus {
            outline: none;
            border-color: var(--primary, #3b82f6);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: rgba(255, 255, 255, 0.12);
        }
        
        .clear-search {
            position: absolute;
            right: 8px;
            background: none;
            border: none;
            color: var(--text-muted, #64748b);
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .clear-search:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary, #e2e8f0);
        }
        
        /* Container de controles */
        .controls-container {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            flex: 1;
            justify-content: flex-end;
        }
        
        /* Grupos de filtros */
        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-label {
            font-size: 14px;
            color: var(--text-secondary, #94a3b8);
            font-weight: 500;
            white-space: nowrap;
        }
        
        .filter-select-modern {
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 6px;
            color: var(--text-primary, #e2e8f0);
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .filter-select-modern:focus {
            outline: none;
            border-color: var(--primary, #3b82f6);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }
        
        /* Toggle de visualização */
        .view-toggle-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .view-toggle-buttons {
            display: flex;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 6px;
            padding: 2px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .view-btn-modern {
            background: none;
            border: none;
            padding: 6px 8px;
            color: var(--text-muted, #64748b);
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }
        
        .view-btn-modern:hover {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-primary, #e2e8f0);
        }
        
        .view-btn-modern.active {
            background: var(--primary, #3b82f6);
            color: white;
        }
        
        /* Botão Criar */
        .btn-create-avatar-modern {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 10px 16px;
            background: linear-gradient(135deg, var(--primary, #3b82f6), #1d4ed8);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }
        
        .btn-create-avatar-modern:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.4);
        }
        
        .btn-create-avatar-modern:active {
            transform: translateY(0);
        }
        
        /* Área de exibição dos avatares */
        .avatars-display-area {
            min-height: 500px;
            padding: 0;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            margin: 2rem 0;
        }
        
        /* Layout principal dividido */
        .avatars-main-layout {
            display: flex;
            height: 100%;
            min-height: 500px;
        }
        
        /* Seção dos cards de avatares (lado esquerdo) */
        .avatars-grid-section {
            flex: 1;
            width: 50%;
            padding: 1.5rem;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        /* Seção do formulário (lado direito) */
        .avatar-form-section {
            flex: 1;
            width: 50%;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.02);
        }
        
        /* Headers das seções */
        .section-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .section-header h4 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 0 0.5rem 0;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary, #e2e8f0);
        }
        
        .section-header h4 .material-icons {
            font-size: 1.25rem;
            color: var(--primary, #3b82f6);
        }
        
        .section-header p {
            margin: 0;
            font-size: 14px;
            color: var(--text-secondary, #94a3b8);
        }
        
        .avatar-count {
            font-size: 13px;
            color: var(--text-muted, #64748b);
            font-weight: 400;
        }
        
        /* Grid de avatares */
        .avatars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            min-height: 300px;
        }
        
        /* Estado vazio do grid */
        .empty-grid-state {
            grid-column: 1 / -1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-muted, #64748b);
        }
        
        .empty-grid-icon {
            margin-bottom: 1rem;
        }
        
        .empty-grid-icon .material-icons {
            font-size: 3rem;
            color: var(--text-muted, #64748b);
            opacity: 0.6;
        }
        
        .empty-grid-state p {
            font-size: 16px;
            font-weight: 500;
            color: var(--text-secondary, #94a3b8);
            margin: 0 0 0.5rem 0;
        }
        
        .empty-grid-state small {
            font-size: 14px;
            color: var(--text-muted, #64748b);
        }
        
        /* Estilos do formulário */
        .avatar-creation-form {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .form-grid {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .form-row {
            display: flex;
            gap: 1rem;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .form-group label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary, #e2e8f0);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 12px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 6px;
            color: var(--text-primary, #e2e8f0);
            font-size: 14px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--text-muted, #64748b);
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary, #3b82f6);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
            background: rgba(255, 255, 255, 0.12);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        /* Ações do formulário */
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin-top: auto;
        }
        
        .btn-primary,
        .btn-secondary {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary, #3b82f6), #1d4ed8);
            color: white;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-secondary, #94a3b8);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
            color: var(--text-primary, #e2e8f0);
        }
        
        /* Responsividade */
        @media (max-width: 1200px) {
            .avatars-toolbar-modern {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }
            
            .search-container {
                max-width: 100%;
            }
            
            .controls-container {
                justify-content: center;
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .avatars-main-layout {
                flex-direction: column;
            }
            
            .avatars-grid-section,
            .avatar-form-section {
                width: 100%;
                border-right: none;
            }
            
            .avatars-grid-section {
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            }
            
            .avatars-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .avatars-toolbar-modern {
                padding: 1rem;
            }
            
            .controls-container {
                gap: 0.75rem;
            }
            
            .filter-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
            
            .btn-create-avatar-modern span {
                display: none;
            }
            
            .avatars-grid-section,
            .avatar-form-section {
                padding: 1rem;
            }
            
            .avatars-main-layout {
                min-height: auto;
            }
            
            .avatars-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 0.75rem;
                min-height: 250px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
        
        /* Estilos para nova seção com dois blocos */
        .avatar-split-section {
            display: flex;
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .split-block {
            flex: 1;
            width: 50%;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .split-block:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        .block-header {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .block-header h3 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 0 0.5rem 0;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary, #e2e8f0);
        }
        
        .block-header h3 .material-icons {
            font-size: 1.25rem;
            color: var(--primary, #3b82f6);
        }
        
        .block-header p {
            margin: 0;
            font-size: 14px;
            color: var(--text-secondary, #94a3b8);
        }
        
        .block-content {
            padding: 1.5rem;
            min-height: 200px;
        }
        
        .content-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            height: 100%;
            min-height: 150px;
            color: var(--text-muted, #64748b);
            padding: 2rem;
        }
        
        .content-placeholder p {
            margin: 0.5rem 0;
            font-size: 14px;
        }
        
        .content-placeholder p:first-child {
            font-weight: 500;
            color: var(--text-secondary, #94a3b8);
        }
        
        /* Diferenciação visual dos blocos */
        .left-block .block-header h3 .material-icons {
            color: #10b981; /* Verde */
        }
        
        .right-block .block-header h3 .material-icons {
            color: #f59e0b; /* Amarelo/Laranja */
        }
        
        /* Responsividade para a seção split */
        @media (max-width: 768px) {
            .avatar-split-section {
                flex-direction: column;
                gap: 1rem;
            }
            
            .split-block {
                width: 100%;
            }
            
            .block-header {
                padding: 1rem 1rem 0.75rem;
            }
            
            .block-content {
                padding: 1rem;
                min-height: 150px;
            }
            
            .content-placeholder {
                min-height: 100px;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Page Preloader -->
    <div class="page-preloader" id="pagePreloader">
        <div class="preloader-logo"></div>
        <div class="preloader-text">Carregando Gerador de Prompts</div>
        <div class="preloader-subtext">Preparando experiência otimizada...</div>
    </div>

    <div class="main-container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="material-icons">auto_fix_high</i>
                    <span class="logo-text">Gerador de Prompt</span>
                </a>
                
                <div class="user-menu">
                    <div class="nav-actions">
                        <a href="#" class="action-btn" title="Notificações">
                            <i class="material-icons">notifications</i>
                        </a>
                        <a href="#" class="action-btn" title="Configurações">
                            <i class="material-icons">settings</i>
                        </a>
                    </div>
                    
                    <div class="user-account">
                        <div class="user-avatar">
                            <?= strtoupper(substr($_SESSION['usuario_nome'] ?? 'U', 0, 2)) ?>
                        </div>
                    <div class="user-info">
                            <div class="user-name"><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?></div>
                            <div class="user-email"><?= htmlspecialchars($_SESSION['usuario_email'] ?? 'usuario@exemplo.com') ?></div>
                    </div>
                        
                        <div class="account-dropdown">
                            <a href="#" class="dropdown-item">
                                <i class="material-icons">person</i>
                                Meu Perfil
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="material-icons">history</i>
                                Histórico de Prompts
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="material-icons">bookmark</i>
                                Prompts Salvos
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="material-icons">account_circle</i>
                                Configurações da Conta
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="material-icons">help</i>
                                Ajuda & Suporte
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="auth/logout.php" class="dropdown-item">
                        <i class="material-icons">logout</i>
                        Sair
                    </a>
                        </div>

                    </div>
                </div>
            </div>
        </header>

        <!-- Container Principal -->
        <div class="content-container">
            <div class="page-header">
            </div>

            <!-- Mensagens -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="material-icons">check_circle</i>
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="material-icons">error</i>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>



            <!-- Sistema de Abas -->
            <div class="tabs-container">
                <!-- Navegação das Abas -->
                <div class="tabs-nav">
                    <button class="tab-button active" data-tab="ambiente">
                        <i class="material-icons">landscape</i>
                        <span>Cena/Ambiente</span>
                    </button>
                    <button class="tab-button" data-tab="estilo_visual">
                        <i class="material-icons">palette</i>
                        <span>Estilo Visual</span>
                    </button>
                    <button class="tab-button" data-tab="iluminacao">
                        <i class="material-icons">wb_sunny</i>
                        <span>Iluminação</span>
                    </button>
                    <button class="tab-button" data-tab="tecnica">
                        <i class="material-icons">settings</i>
                        <span>Técnica</span>
                    </button>
                    <button class="tab-button" data-tab="elementos_especiais">
                        <i class="material-icons">auto_awesome</i>
                        <span>Elementos Especiais</span>
                    </button>
                    <button class="tab-button" data-tab="qualidade">
                        <i class="material-icons">high_quality</i>
                        <span>Qualidade</span>
                    </button>
                    <button class="tab-button" data-tab="avatar">
                        <i class="material-icons">groups</i>
                        <span>Avatar/Personagem</span>
                    </button>
                    <button class="tab-button" data-tab="camera">
                        <i class="material-icons">photo_camera</i>
                        <span>Câmera</span>
                    </button>
                    <button class="tab-button" data-tab="voz">
                        <i class="material-icons">mic</i>
                        <span>Voz</span>
                    </button>
                    <button class="tab-button" data-tab="acao">
                        <i class="material-icons">play_arrow</i>
                        <span>Ação</span>
                    </button>
                </div>

                <!-- Form Principal -->
                <form id="promptForm" method="post">
                    <input type="hidden" name="action" value="save_prompt">
                    <input type="hidden" id="selected_environment" name="selected_environment">
                    <input type="hidden" id="selected_visual_style" name="selected_visual_style">
                    <input type="hidden" id="selected_lighting" name="selected_lighting">
                    <input type="hidden" id="selected_technique" name="selected_technique">
                    <input type="hidden" id="selected_special_elements" name="selected_special_elements">
                    <input type="hidden" id="selected_quality" name="selected_quality">
                    <input type="hidden" id="selected_character" name="selected_character">
                    <input type="hidden" id="selected_camera" name="selected_camera">
                    <input type="hidden" id="selected_voice" name="selected_voice">
                    <input type="hidden" id="selected_action" name="selected_action">
                    <input type="hidden" id="settings" name="settings">

                                         <!-- ABA 1: CENA/AMBIENTE - DESIGN COMPACTO E MODERNO -->
                     <div class="tab-content active" id="tab-ambiente">
                         <?php
                         // Renderizar ambientes dinamicamente do banco de dados
                         if ($cenaRenderer) {
                             echo $cenaRenderer->renderizarAbaAmbiente();
                         } else {
                             echo '<div class="categories-grid">
                            <div class="category-section">
                                         <div class="error-state-ambiente">
                                             <i class="material-icons" style="font-size: 4rem; color: #ef4444; margin-bottom: 1rem;">error</i>
                                             <h3 style="color: #ef4444; margin-bottom: 0.5rem;">Sistema temporariamente indisponível</h3>
                                             <p style="color: #64748b;">As opções de ambiente estão sendo carregadas...</p>
                                    </div>
                                </div>
                                   </div>';
                         }
                         ?>

                                                                         <!-- Container de 3 colunas na base -->
                        <div class="bottom-controls-container">
                            <!-- Coluna 1: Campo de descrição personalizada -->
                            <div class="custom-description">
                                <label>
                                    <i class="material-icons">edit</i>
                                    Descrição Personalizada do Ambiente
                                </label>
                                <textarea 
                                    name="custom_environment" 
                                    placeholder="Descreva um ambiente específico que não está nas opções abaixo..."
                                    rows="3"></textarea>
                            </div>

                            <!-- Coluna 2: Controles de navegação -->
                            <div class="tab-navigation">
                                <div class="nav-buttons">
                                    <button type="button" class="btn btn-secondary" onclick="goToFirstTab()" title="Início">
                                        <i class="material-icons">home</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="prevTab()" title="Anterior">
                                        <i class="material-icons">arrow_back</i>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="nextTab()" title="Próxima">
                                        <i class="material-icons">arrow_forward</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="goToLastTab()" title="Fim">
                                        <i class="material-icons">flag</i>
                                    </button>
                                </div>
                                <button type="button" class="btn-prompt" onclick="gerarPrompt()">
                                    PROMPT
                                </button>
                            </div>


                            <!-- Coluna 3: Espaço para propaganda -->
                            <div class="advertisement-container">
                                <div class="advertisement-content">
                                    <i class="material-icons" style="font-size: 2rem; color: var(--text-muted);">campaign</i>
                                    <div class="advertisement-placeholder">
                                        Espaço para propaganda<br>
                                        Anúncios e promoções
                                    </div>
                                </div>
                            </div>

                        </div>

                     </div>

                                         <!-- ABA 2: ESTILO VISUAL -->
                     <div class="tab-content" id="tab-estilo_visual">
                         <div class="tab-header">
                         </div>

                         <?php 
                         // Renderizar aba estilo visual dinamicamente
                         if ($cenaRenderer) {
                             echo $cenaRenderer->renderizarAbaEstiloVisual();
                         } else {
                             echo '<div class="categories-grid"><div class="category-section"><div class="error-state-estilo_visual"><i class="material-icons" style="font-size: 4rem; color: #ef4444;">error</i><h3 style="color: #ef4444;">Sistema de estilo visual indisponível</h3><p style="color: #64748b;">Carregando configurações padrão...</p></div></div></div>';
                         }
                         ?>

                                                 <!-- Container de 3 colunas na base -->
                         <div class="bottom-controls-container">
                             <!-- Coluna 1: Campo de descrição personalizada -->
                             <div class="custom-description">
                                 <label>
                                     <i class="material-icons">edit</i>
                                     Descrição Personalizada do Estilo Visual
                                 </label>
                                 <textarea 
                                     name="custom_visual_style" 
                                     placeholder="Descreva um estilo visual específico que não está nas opções abaixo..."
                                     rows="3"></textarea>
                             </div>

                             <!-- Coluna 2: Controles de navegação -->
                            <div class="tab-navigation">
                                <div class="nav-buttons">
                                    <button type="button" class="btn btn-secondary" onclick="goToFirstTab()" title="Início">
                                        <i class="material-icons">home</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="prevTab()" title="Anterior">
                                        <i class="material-icons">arrow_back</i>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="nextTab()" title="Próxima">
                                        <i class="material-icons">arrow_forward</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="goToLastTab()" title="Fim">
                                        <i class="material-icons">flag</i>
                                    </button>
                                </div>
                                <button type="button" class="btn-prompt" onclick="gerarPrompt()">
                                    PROMPT
                                </button>
                            </div>


                            <!-- Coluna 3: Espaço para propaganda -->
                            <div class="advertisement-container">
                                <div class="advertisement-content">
                                    <i class="material-icons" style="font-size: 2rem; color: var(--text-muted);">campaign</i>
                                    <div class="advertisement-placeholder">
                                        Espaço para propaganda<br>
                                        Anúncios e promoções
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>


                                         <!-- ABA 3: ILUMINAÇÃO -->
                     <div class="tab-content" id="tab-iluminacao">
                         <div class="tab-header">
                         </div>

                         <?php 
                         // Renderizar aba iluminação dinamicamente
                         if ($cenaRenderer) {
                             echo $cenaRenderer->renderizarAbaIluminacao();
                         } else {
                             echo '<div class="categories-grid"><div class="category-section"><div class="error-state-iluminacao"><i class="material-icons" style="font-size: 4rem; color: #ef4444;">error</i><h3 style="color: #ef4444;">Sistema de iluminação indisponível</h3><p style="color: #64748b;">Carregando configurações padrão...</p></div></div></div>';
                         }
                         ?>

                                                 <!-- Container de 3 colunas na base -->
                         <div class="bottom-controls-container">
                             <!-- Coluna 1: Campo de descrição personalizada -->
                             <div class="custom-description">
                                 <label>
                                     <i class="material-icons">edit</i>
                                     Descrição Personalizada da Iluminação
                                 </label>
                                 <textarea 
                                     name="custom_lighting" 
                                     placeholder="Descreva um tipo de iluminação específico que não está nas opções abaixo..."
                                     rows="3"></textarea>
                             </div>

                             <!-- Coluna 2: Controles de navegação -->
                            <div class="tab-navigation">
                                <div class="nav-buttons">
                                    <button type="button" class="btn btn-secondary" onclick="goToFirstTab()" title="Início">
                                        <i class="material-icons">home</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="prevTab()" title="Anterior">
                                        <i class="material-icons">arrow_back</i>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="nextTab()" title="Próxima">
                                        <i class="material-icons">arrow_forward</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="goToLastTab()" title="Fim">
                                        <i class="material-icons">flag</i>
                                    </button>
                                </div>
                                <button type="button" class="btn-prompt" onclick="gerarPrompt()">
                                    PROMPT
                                </button>
                            </div>


                            <!-- Coluna 3: Espaço para propaganda -->
                            <div class="advertisement-container">
                                <div class="advertisement-content">
                                    <i class="material-icons" style="font-size: 2rem; color: var(--text-muted);">campaign</i>
                                    <div class="advertisement-placeholder">
                                        Espaço para propaganda<br>
                                        Anúncios e promoções
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>


                    <!-- ABA 4: TÉCNICA -->
                    <div class="tab-content" id="tab-tecnica">
                        <div class="tab-header">
                        </div>

                        <?php 
                        // Renderizar aba técnica dinamicamente
                        if ($cenaRenderer) {
                            echo $cenaRenderer->renderizarAbaTecnica();
                        } else {
                            echo '<div class="categories-grid"><div class="category-section"><div class="error-state-tecnica"><i class="material-icons" style="font-size: 4rem; color: #ef4444;">error</i><h3 style="color: #ef4444;">Sistema técnico indisponível</h3><p style="color: #64748b;">Carregando configurações padrão...</p></div></div></div>';
                        }
                        ?>

                        <!-- Container de 3 colunas na base -->
                        <div class="bottom-controls-container">
                            <!-- Coluna 1: Campo de descrição personalizada -->
                            <div class="custom-description">
                                <label>
                                    <i class="material-icons">edit</i>
                                    Descrição Personalizada da Técnica
                                </label>
                                <textarea 
                                    name="custom_technique" 
                                    placeholder="Descreva técnicas específicas que não estão nas opções abaixo..."
                                    rows="3"></textarea>
                            </div>

                            <!-- Coluna 2: Controles de navegação -->
                            <div class="tab-navigation">
                                <div class="nav-buttons">
                                    <button type="button" class="btn btn-secondary" onclick="goToFirstTab()" title="Início">
                                        <i class="material-icons">home</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="prevTab()" title="Anterior">
                                        <i class="material-icons">arrow_back</i>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="nextTab()" title="Próxima">
                                        <i class="material-icons">arrow_forward</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="goToLastTab()" title="Fim">
                                        <i class="material-icons">flag</i>
                                    </button>
                                </div>
                                <button type="button" class="btn-prompt" onclick="gerarPrompt()">
                                    PROMPT
                                </button>
                            </div>

                            <!-- Coluna 3: Espaço para propaganda -->
                            <div class="advertisement-container">
                                <div class="advertisement-content">
                                    <i class="material-icons" style="font-size: 2rem; color: var(--text-muted);">campaign</i>
                                    <div class="advertisement-placeholder">
                                        Espaço para propaganda<br>
                                        Anúncios e promoções
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>


                    <!-- ABA 5: ELEMENTOS ESPECIAIS -->
                    <div class="tab-content" id="tab-elementos_especiais">
                        <div class="tab-header">
                        </div>

                        <?php 
                        // Renderizar aba elementos especiais dinamicamente
                        if ($cenaRenderer) {
                            echo $cenaRenderer->renderizarAbaElementosEspeciais();
                        } else {
                            echo '<div class="categories-grid"><div class="category-section"><div class="error-state-elementos_especiais"><i class="material-icons" style="font-size: 4rem; color: #ef4444;">error</i><h3 style="color: #ef4444;">Sistema de elementos especiais indisponível</h3><p style="color: #64748b;">Carregando configurações padrão...</p></div></div></div>';
                        }
                        ?>

                        <!-- Container de 3 colunas na base -->
                        <div class="bottom-controls-container">
                            <!-- Coluna 1: Campo de descrição personalizada -->
                            <div class="custom-description">
                                <label>
                                    <i class="material-icons">edit</i>
                                    Descrição Personalizada dos Elementos
                                </label>
                                <textarea 
                                    name="custom_special_elements" 
                                    placeholder="Descreva elementos especiais que não estão nas opções abaixo..."
                                    rows="3"></textarea>
                            </div>

                            <!-- Coluna 2: Controles de navegação -->
                            <div class="tab-navigation">
                                <div class="nav-buttons">
                                    <button type="button" class="btn btn-secondary" onclick="goToFirstTab()" title="Início">
                                        <i class="material-icons">home</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="prevTab()" title="Anterior">
                                        <i class="material-icons">arrow_back</i>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="nextTab()" title="Próxima">
                                        <i class="material-icons">arrow_forward</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="goToLastTab()" title="Fim">
                                        <i class="material-icons">flag</i>
                                    </button>
                                </div>
                                <button type="button" class="btn-prompt" onclick="gerarPrompt()">
                                    PROMPT
                                </button>
                            </div>

                            <!-- Coluna 3: Espaço para propaganda -->
                            <div class="advertisement-container">
                                <div class="advertisement-content">
                                    <i class="material-icons" style="font-size: 2rem; color: var(--text-muted);">campaign</i>
                                    <div class="advertisement-placeholder">
                                        Espaço para propaganda<br>
                                        Anúncios e promoções
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>


                    <!-- ABA 6: QUALIDADE -->
                    <div class="tab-content" id="tab-qualidade">
                        <div class="tab-header">
                        </div>

                        <?php 
                        // Renderizar aba qualidade dinamicamente
                        if ($cenaRenderer) {
                            echo $cenaRenderer->renderizarAbaQualidade();
                        } else {
                            echo '<div class="categories-grid"><div class="category-section"><div class="error-state-qualidade"><i class="material-icons" style="font-size: 4rem; color: #ef4444;">error</i><h3 style="color: #ef4444;">Sistema de qualidade indisponível</h3><p style="color: #64748b;">Carregando configurações padrão...</p></div></div></div>';
                        }
                        ?>

                        <!-- Container de 3 colunas na base -->
                        <div class="bottom-controls-container">
                            <!-- Coluna 1: Campo de descrição personalizada -->
                            <div class="custom-description">
                                <label>
                                    <i class="material-icons">edit</i>
                                    Descrição Personalizada da Qualidade
                                </label>
                                <textarea 
                                    name="custom_quality" 
                                    placeholder="Descreva configurações de qualidade que não estão nas opções abaixo..."
                                    rows="3"></textarea>
                            </div>

                            <!-- Coluna 2: Controles de navegação -->
                            <div class="tab-navigation">
                                <div class="nav-buttons">
                                    <button type="button" class="btn btn-secondary" onclick="goToFirstTab()" title="Início">
                                        <i class="material-icons">home</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="prevTab()" title="Anterior">
                                        <i class="material-icons">arrow_back</i>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="nextTab()" title="Próxima">
                                        <i class="material-icons">arrow_forward</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="goToLastTab()" title="Fim">
                                        <i class="material-icons">flag</i>
                                    </button>
                                </div>
                                <button type="button" class="btn-prompt" onclick="gerarPrompt()">
                                    PROMPT
                                </button>
                            </div>

                            <!-- Coluna 3: Espaço para propaganda -->
                            <div class="advertisement-container">
                                <div class="advertisement-content">
                                    <i class="material-icons" style="font-size: 2rem; color: var(--text-muted);">campaign</i>
                                    <div class="advertisement-placeholder">
                                        Espaço para propaganda<br>
                                        Anúncios e promoções
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>


                    <!-- ABA 7: AVATAR/PERSONAGEM -->
                    <div class="tab-content" id="tab-avatar">
                        <div class="tab-header">
                            <h2><i class="material-icons">groups</i> Avatar/Personagem</h2>
                            <p>Configure as características do seu personagem</p>
                        </div>

                        <!-- Área principal para conteúdo dos avatares -->
                        <div class="avatars-content-area" style="margin: 0;">
                            <!-- Barra de controles superior -->
                            <div class="avatars-toolbar-modern">
                                <!-- Barra de pesquisa (1/3 da largura) -->
                                <div class="search-container">
                                    <div class="search-input-group">
                                        <i class="material-icons search-icon">search</i>
                                        <input type="text" id="avatar-search" placeholder="Buscar avatares por nome..." class="search-input-modern">
                                        <button class="clear-search" id="clear-search" style="display: none;">
                                            <i class="material-icons">clear</i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Filtros e controles -->
                                <div class="controls-container">
                                    <!-- Filtro por tipo -->
                                    <div class="filter-group">
                                        <label class="filter-label">Tipo:</label>
                                        <select id="avatar-type-filter" class="filter-select-modern">
                                            <option value="meus" selected>Meus</option>
                                            <option value="publicos">Públicos</option>
                                            <option value="favoritos">Favoritos</option>
                                        </select>
                                    </div>

                                    <!-- Ordenação -->
                                    <div class="filter-group">
                                        <label class="filter-label">Ordenar:</label>
                                        <select id="avatar-sort" class="filter-select-modern">
                                            <option value="recentes" selected>Recentes</option>
                                            <option value="nome_az">Nomes A-Z</option>
                                            <option value="ultimos_usados">Últimos usados</option>
                                            <option value="tipos">Tipos</option>
                                        </select>
                                    </div>

                                    <!-- Visualização -->
                                    <div class="view-toggle-group">
                                        <label class="filter-label">Exibir:</label>
                                        <div class="view-toggle-buttons">
                                            <button class="view-btn-modern active" data-view="cards" title="Cards">
                                                <i class="material-icons">grid_view</i>
                                            </button>
                                            <button class="view-btn-modern" data-view="list" title="Lista">
                                                <i class="material-icons">view_list</i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Botão Criar -->
                                    <button class="btn-create-avatar-modern" id="btn-create-avatar">
                                        <i class="material-icons">add</i>
                                        <span>Criar</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Área dos avatares -->
                            <div class="avatars-display-area" id="avatars-display">
                                <!-- Layout dividido horizontalmente -->
                                <div class="avatars-main-layout">
                                    <!-- Bloco esquerdo - Cards de avatares -->
                                    <div class="avatars-grid-section">
                                        <div class="section-header">
                                            <h4>
                                                <i class="material-icons">people</i>
                                                Meus Avatares
                                            </h4>
                                            <span class="avatar-count" id="avatar-count-display">0 avatares</span>
                                        </div>
                                        
                                        <!-- Grid de avatares -->
                                        <div class="avatars-grid" id="avatars-grid">
                                            <!-- Estado vazio inicial -->
                                            <div class="empty-grid-state" id="empty-grid-state">
                                                <div class="empty-grid-icon">
                                                    <i class="material-icons">person_add_alt</i>
                                                </div>
                                                <p>Nenhum avatar criado ainda</p>
                                                <small>Use o formulário ao lado para criar seu primeiro avatar</small>
                                            </div>
                                            
                                            <!-- Cards de avatares serão inseridos aqui dinamicamente -->
                                        </div>
                                    </div>

                                    <!-- Bloco direito - Formulário de cadastro -->
                                    <div class="avatar-form-section">
                                        <div class="section-header">
                                            <h4>
                                                <i class="material-icons">add_circle</i>
                                                Criar Novo Avatar
                                            </h4>
                                            <p>Preencha os campos para criar seu avatar</p>
                                        </div>
                                        
                                        <!-- Formulário de criação -->
                                        <form class="avatar-creation-form" id="avatar-creation-form">
                                            <div class="form-grid">
                                                <div class="form-group">
                                                    <label for="avatar-name">Nome do Avatar</label>
                                                    <input type="text" id="avatar-name" name="name" placeholder="Ex: Elena Rodriguez" required>
                                                </div>
                                                
                                                <div class="form-row">
                                                    <div class="form-group">
                                                        <label for="avatar-type">Tipo</label>
                                                        <select id="avatar-type" name="type" required>
                                                            <option value="">Selecione...</option>
                                                            <option value="humano">Humano</option>
                                                            <option value="animal">Animal</option>
                                                            <option value="fantastico">Fantástico</option>
                                                            <option value="extraterrestre">Extraterrestre</option>
                                                            <option value="robotico">Robótico/IA</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="avatar-gender">Gênero</label>
                                                        <select id="avatar-gender" name="gender">
                                                            <option value="neutro">Neutro</option>
                                                            <option value="masculino">Masculino</option>
                                                            <option value="feminino">Feminino</option>
                                                            <option value="outro">Outro</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-row">
                                                    <div class="form-group">
                                                        <label for="avatar-age">Idade</label>
                                                        <input type="number" id="avatar-age" name="age" placeholder="25" min="1" max="120" value="25">
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="avatar-visibility">Visibilidade</label>
                                                        <select id="avatar-visibility" name="visibility">
                                                            <option value="privado" selected>Privado</option>
                                                            <option value="publico">Público</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="avatar-description">Descrição</label>
                                                    <textarea id="avatar-description" name="description" placeholder="Descreva as características principais..." rows="3"></textarea>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="avatar-tags">Tags</label>
                                                    <input type="text" id="avatar-tags" name="tags" placeholder="médica, jovem, profissional">
                                                </div>
                                            </div>
                                            
                                            <div class="form-actions">
                                                <button type="button" class="btn-secondary" id="clear-form">
                                                    <i class="material-icons">clear</i>
                                                    Limpar
                                                </button>
                                                <button type="submit" class="btn-primary">
                                                    <i class="material-icons">save</i>
                                                    Criar Avatar
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Nova seção com dois blocos de 50% -->
                            <div class="avatar-split-section">
                                <!-- Bloco esquerdo -->
                                <div class="split-block left-block">
                                    <div class="block-header">
                                        <h3>
                                            <i class="material-icons">add_circle</i>
                                            Bloco Esquerdo
                                        </h3>
                                        <p>Conteúdo do primeiro bloco</p>
                                    </div>
                                    <div class="block-content">
                                        <!-- Conteúdo personalizado aqui -->
                                        <div class="content-placeholder">
                                            <p>Este é o bloco esquerdo (50% da largura)</p>
                                            <p>Adicione aqui o conteúdo desejado</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bloco direito -->
                                <div class="split-block right-block">
                                    <div class="block-header">
                                        <h3>
                                            <i class="material-icons">info</i>
                                            Bloco Direito
                                        </h3>
                                        <p>Conteúdo do segundo bloco</p>
                                    </div>
                                    <div class="block-content">
                                        <!-- Conteúdo personalizado aqui -->
                                        <div class="content-placeholder">
                                            <p>Este é o bloco direito (50% da largura)</p>
                                            <p>Adicione aqui o conteúdo desejado</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Container de 3 colunas na base -->
                        <div class="bottom-controls-container">
                            <!-- Coluna 1: Campo de descrição personalizada -->
                            <div class="custom-description">
                                <label>
                                    <i class="material-icons">edit</i>
                                    Descrição Personalizada do Personagem
                                </label>
                                <textarea 
                                    name="custom_character" 
                                    placeholder="Descreva características específicas do seu personagem que não estão nas opções acima..."
                                    rows="3"></textarea>
                            </div>

                            <!-- Coluna 2: Controles de navegação -->
                            <div class="tab-navigation">
                                <div class="nav-buttons">
                                    <button type="button" class="btn btn-secondary" onclick="goToFirstTab()" title="Início">
                                        <i class="material-icons">home</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="prevTab()" title="Anterior">
                                        <i class="material-icons">arrow_back</i>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="nextTab()" title="Próxima">
                                        <i class="material-icons">arrow_forward</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="goToLastTab()" title="Fim">
                                        <i class="material-icons">flag</i>
                                    </button>
                                </div>
                                <button type="button" class="btn-prompt" onclick="gerarPrompt()">
                                    PROMPT
                                </button>
                            </div>

                            <!-- Coluna 3: Espaço para propaganda -->
                            <div class="advertisement-container">
                                <div class="advertisement-content">
                                    <i class="material-icons" style="font-size: 2rem; color: var(--text-muted);">campaign</i>
                                    <div class="advertisement-placeholder">
                                        Espaço para propaganda<br>
                                        Anúncios e promoções
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content" id="tab-camera">
                        <div class="tab-header">
                            <h2><i class="material-icons">photo_camera</i> Configurações de Câmera</h2>
                            <p>Configure ângulos, planos e perspectivas da filmagem</p>
                        </div>

                        <div class="categories-grid">
                            <!-- ÂNGULOS -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">camera_alt</i>
                                    </div>
                                    <h3 class="category-title">Ângulos</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="camera" data-value="eye_level">
                                        <div class="subcategory-title">Eye Level</div>
                                        <div class="subcategory-desc">Altura dos olhos</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="low_angle">
                                        <div class="subcategory-title">Low Angle</div>
                                        <div class="subcategory-desc">Contra-plongé</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="high_angle">
                                        <div class="subcategory-title">High Angle</div>
                                        <div class="subcategory-desc">Plongé</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="birds_eye">
                                        <div class="subcategory-title">Bird's Eye</div>
                                        <div class="subcategory-desc">Vista aérea</div>
                                    </div>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="age_range">Faixa Etária</label>
                                            <select id="age_range" name="age_range">
                                                <option value="">Selecione</option>
                                                <option value="crianca">Criança (5-12 anos)</option>
                                                <option value="adolescente">Adolescente (13-17 anos)</option>
                                                <option value="jovem_adulto">Jovem Adulto (18-30 anos)</option>
                                                <option value="adulto">Adulto (31-50 anos)</option>
                                                <option value="meia_idade">Meia-idade (51-65 anos)</option>
                                                <option value="idoso">Idoso (65+ anos)</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="ethnicity">Etnia</label>
                                            <select id="ethnicity" name="ethnicity">
                                                <option value="">Selecione</option>
                                                <option value="brasileiro">Brasileiro</option>
                                                <option value="caucasiano">Caucasiano</option>
                                                <option value="afrodescendente">Afrodescendente</option>
                                                <option value="asiatico">Asiático</option>
                                                <option value="latino">Latino</option>
                                                <option value="indigena">Indígena</option>
                                                <option value="middle_eastern">Oriente Médio</option>
                                                <option value="misto">Miscigenado</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="body_type">Tipo Físico</label>
                                            <select id="body_type" name="body_type">
                                                <option value="">Selecione</option>
                                                <option value="magro">Magro/Esbelto</option>
                                                <option value="atletico">Atlético</option>
                                                <option value="musculoso">Musculoso</option>
                                                <option value="curvilineo">Curvilíneo</option>
                                                <option value="robusto">Robusto</option>
                                                <option value="obeso">Acima do peso</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="height">Altura</label>
                                            <select id="height" name="height">
                                                <option value="">Selecione</option>
                                                <option value="muito_baixo">Muito baixo (< 1.50m)</option>
                                                <option value="baixo">Baixo (1.50-1.65m)</option>
                                                <option value="medio">Médio (1.65-1.80m)</option>
                                                <option value="alto">Alto (1.80-1.95m)</option>
                                                <option value="muito_alto">Muito alto (> 1.95m)</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="hair_color">Cor do Cabelo</label>
                                            <select id="hair_color" name="hair_color">
                                                <option value="">Selecione</option>
                                                <option value="preto">Preto</option>
                                                <option value="castanho_escuro">Castanho escuro</option>
                                                <option value="castanho_claro">Castanho claro</option>
                                                <option value="loiro_escuro">Loiro escuro</option>
                                                <option value="loiro_claro">Loiro claro</option>
                                                <option value="ruivo">Ruivo</option>
                                                <option value="grisalho">Grisalho</option>
                                                <option value="branco">Branco</option>
                                                <option value="colorido">Colorido (artificial)</option>
                                                <option value="careca">Careca</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="eye_color">Cor dos Olhos</label>
                                            <select id="eye_color" name="eye_color">
                                                <option value="">Selecione</option>
                                                <option value="castanho">Castanho</option>
                                                <option value="azul">Azul</option>
                                                <option value="verde">Verde</option>
                                                <option value="mel">Mel</option>
                                                <option value="cinza">Cinza</option>
                                                <option value="preto">Preto</option>
                                                <option value="heterocromia">Heterocromia</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="profession">Profissão/Ocupação</label>
                                            <input type="text" id="profession" name="profession" placeholder="Ex: Médico, Artista, Estudante">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CARACTERÍSTICAS DE ANIMAIS -->
                            <div id="animal-fields" class="species-fields" style="display: none;">
                                <div class="form-section">
                                    <div class="section-header">
                                        <div class="section-title">
                                            <i class="material-icons">pets</i>
                                            <h3>Características do Animal</h3>
                                        </div>
                                    </div>
                                    
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="animal_species">Espécie</label>
                                            <select id="animal_species" name="animal_species">
                                                <option value="">Selecione</option>
                                                <option value="gato">Gato</option>
                                                <option value="cachorro">Cachorro</option>
                                                <option value="lobo">Lobo</option>
                                                <option value="leao">Leão</option>
                                                <option value="tigre">Tigre</option>
                                                <option value="urso">Urso</option>
                                                <option value="aguia">Águia</option>
                                                <option value="coruja">Coruja</option>
                                                <option value="serpente">Serpente</option>
                                                <option value="dragao_komodo">Dragão de Komodo</option>
                                                <option value="tubarao">Tubarão</option>
                                                <option value="golfinho">Golfinho</option>
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
                                            <label for="fur_pattern">Padrão da Pelagem</label>
                                            <input type="text" id="fur_pattern" name="fur_pattern" placeholder="Ex: Listrado, Manchado, Sólido">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="primary_color">Cor Primária</label>
                                            <input type="text" id="primary_color" name="primary_color" placeholder="Ex: Marrom, Preto, Branco">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CARACTERÍSTICAS FANTÁSTICAS -->
                            <div id="fantasy-fields" class="species-fields" style="display: none;">
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="material-icons">auto_fix_high</i>
                                        <h3>Características Fantásticas</h3>
                                    </div>
                                    
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="fantasy_type">Tipo de Criatura</label>
                                            <select id="fantasy_type" name="fantasy_type">
                                                <option value="">Selecione</option>
                                                <option value="elfo">Elfo</option>
                                                <option value="anao">Anão</option>
                                                <option value="orc">Orc</option>
                                                <option value="dragao">Dragão</option>
                                                <option value="vampiro">Vampiro</option>
                                                <option value="lobisomem">Lobisomem</option>
                                                <option value="anjo">Anjo</option>
                                                <option value="demonio">Demônio</option>
                                                <option value="fada">Fada</option>
                                                <option value="centauro">Centauro</option>
                                                <option value="sereia">Sereia</option>
                                                <option value="minotauro">Minotauro</option>
                                                <option value="fenix">Fênix</option>
                                                <option value="unicornio">Unicórnio</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="magical_abilities">Habilidades Mágicas</label>
                                            <textarea id="magical_abilities" name="magical_abilities" rows="3" placeholder="Descreva as habilidades mágicas especiais"></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="special_features">Características Especiais</label>
                                            <textarea id="special_features" name="special_features" rows="3" placeholder="Ex: Asas, chifres, escamas, aura luminosa"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CARACTERÍSTICAS DE ALIEN -->
                            <div id="alien-fields" class="species-fields" style="display: none;">
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="material-icons">emoji_nature</i>
                                        <h3>Características Alienígenas</h3>
                                    </div>
                                    
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="alien_origin">Planeta de Origem</label>
                                            <input type="text" id="alien_origin" name="alien_origin" placeholder="Ex: Andrômeda, Zeta Reticuli">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="skin_texture">Textura da Pele</label>
                                            <select id="skin_texture" name="skin_texture">
                                                <option value="">Selecione</option>
                                                <option value="lisa">Lisa</option>
                                                <option value="escamosa">Escamosa</option>
                                                <option value="rugosa">Rugosa</option>
                                                <option value="metalica">Metálica</option>
                                                <option value="translucida">Translúcida</option>
                                                <option value="cristalina">Cristalina</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="number_of_eyes">Número de Olhos</label>
                                            <input type="number" id="number_of_eyes" name="number_of_eyes" min="0" max="10" placeholder="Ex: 2, 3, 4">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="communication_method">Método de Comunicação</label>
                                            <select id="communication_method" name="communication_method">
                                                <option value="">Selecione</option>
                                                <option value="verbal">Verbal</option>
                                                <option value="telepatico">Telepático</option>
                                                <option value="gestual">Gestual</option>
                                                <option value="luminoso">Sinais Luminosos</option>
                                                <option value="quimico">Químico (feromônios)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CARACTERÍSTICAS DE ROBÔ/ANDROID -->
                            <div id="robot-fields" class="species-fields" style="display: none;">
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="material-icons">smart_toy</i>
                                        <h3>Características Robóticas</h3>
                                    </div>
                                    
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="robot_type">Tipo de Robô</label>
                                            <select id="robot_type" name="robot_type">
                                                <option value="">Selecione</option>
                                                <option value="android_humanoid">Android Humanoide</option>
                                                <option value="cyborg">Cyborg</option>
                                                <option value="robo_industrial">Robô Industrial</option>
                                                <option value="ia_holografica">IA Holográfica</option>
                                                <option value="mecha">Mecha</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="power_source">Fonte de Energia</label>
                                            <select id="power_source" name="power_source">
                                                <option value="">Selecione</option>
                                                <option value="bateria">Bateria</option>
                                                <option value="energia_solar">Energia Solar</option>
                                                <option value="nuclear">Nuclear</option>
                                                <option value="plasma">Plasma</option>
                                                <option value="cristal_energetico">Cristal Energético</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="ai_level">Nível de IA</label>
                                            <select id="ai_level" name="ai_level">
                                                <option value="">Selecione</option>
                                                <option value="basico">Básico</option>
                                                <option value="avancado">Avançado</option>
                                                <option value="superinteligente">Superinteligente</option>
                                                <option value="consciente">Consciente</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>






                        </div>


                        </div>

                    </div>


                    <!-- ABA 8: CÂMERA -->
                    <div class="tab-content" id="tab-camera">
                        <div class="tab-header">
                            <h2><i class="material-icons">photo_camera</i> Configurações de Câmera</h2>
                            <p>Configure ângulos, planos e perspectivas da filmagem</p>
                        </div>

                        <div class="categories-grid">
                            <!-- ÂNGULOS -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">camera_alt</i>
                                    </div>
                                    <h3 class="category-title">Ângulos</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="camera" data-value="eye_level">
                                        <div class="subcategory-title">Eye Level</div>
                                        <div class="subcategory-desc">Altura dos olhos</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="low_angle">
                                        <div class="subcategory-title">Low Angle</div>
                                        <div class="subcategory-desc">Contra-plongé</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="high_angle">
                                        <div class="subcategory-title">High Angle</div>
                                        <div class="subcategory-desc">Plongé</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="birds_eye">
                                        <div class="subcategory-title">Bird's Eye</div>
                                        <div class="subcategory-desc">Vista aérea</div>
                                    </div>
                                </div>
                            </div>

                            <!-- PLANOS -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">crop</i>
                                    </div>
                                    <h3 class="category-title">Planos</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="camera" data-value="close_up">
                                        <div class="subcategory-title">Close-up</div>
                                        <div class="subcategory-desc">Primeiro plano</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="medium_shot">
                                        <div class="subcategory-title">Medium Shot</div>
                                        <div class="subcategory-desc">Plano médio</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="wide_shot">
                                        <div class="subcategory-title">Wide Shot</div>
                                        <div class="subcategory-desc">Plano aberto</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                                                 <!-- Container de 3 colunas na base -->
                         <div class="bottom-controls-container">
                             <!-- Coluna 1: Campo de descrição personalizada -->
                             <div class="custom-description">
                                 <label>
                                     <i class="material-icons">edit</i>
                                     Descrição Personalizada da Câmera
                                 </label>
                                 <textarea 
                                     name="custom_camera" 
                                     placeholder="Descreva um ângulo ou movimento de câmera específico que não está nas opções abaixo..."
                                     rows="3"></textarea>
                             </div>

                             <!-- Coluna 2: Controles de navegação -->
                            <div class="tab-navigation">
                                <div class="nav-buttons">
                                    <button type="button" class="btn btn-secondary" onclick="goToFirstTab()" title="Início">
                                        <i class="material-icons">home</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="prevTab()" title="Anterior">
                                        <i class="material-icons">arrow_back</i>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="nextTab()" title="Próxima">
                                        <i class="material-icons">arrow_forward</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="goToLastTab()" title="Fim">
                                        <i class="material-icons">flag</i>
                                    </button>
                                </div>
                                <button type="button" class="btn-prompt" onclick="gerarPrompt()">
                                    PROMPT
                                </button>
                            </div>


                            <!-- Coluna 3: Espaço para propaganda -->
                            <div class="advertisement-container">
                                <div class="advertisement-content">
                                    <i class="material-icons" style="font-size: 2rem; color: var(--text-muted);">campaign</i>
                                    <div class="advertisement-placeholder">
                                        Espaço para propaganda<br>
                                        Anúncios e promoções
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>


                    <!-- ABA 9: VOZ -->
                    <div class="tab-content" id="tab-voz">
                        <div class="tab-header">
                            <h2><i class="material-icons">mic</i> Configurações de Voz</h2>
                            <p>Configure tom, estilo e características da narração</p>
                        </div>

                        <div class="categories-grid">
                            <!-- TONS -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">mood</i>
                                    </div>
                                    <h3 class="category-title">Tons</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="voice" data-value="voz_grave_masculina">
                                        <div class="subcategory-title">Grave Masculina</div>
                                        <div class="subcategory-desc">Tom profundo</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="voz_suave_feminina">
                                        <div class="subcategory-title">Suave Feminina</div>
                                        <div class="subcategory-desc">Tom delicado</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="voz_energetica">
                                        <div class="subcategory-title">Energética</div>
                                        <div class="subcategory-desc">Tom vibrante</div>
                                    </div>
                                </div>
                            </div>

                            <!-- ESTILOS -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">record_voice_over</i>
                                    </div>
                                    <h3 class="category-title">Estilos</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="voice" data-value="narrador_documentario">
                                        <div class="subcategory-title">Documentário</div>
                                        <div class="subcategory-desc">Narração informativa</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="locutor_radio">
                                        <div class="subcategory-title">Locutor Rádio</div>
                                        <div class="subcategory-desc">Estilo radiofônico</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                                                 <!-- Container de 3 colunas na base -->
                         <div class="bottom-controls-container">
                             <!-- Coluna 1: Campo de descrição personalizada -->
                             <div class="custom-description">
                                 <label>
                                     <i class="material-icons">edit</i>
                                     Descrição Personalizada da Voz
                                 </label>
                                 <textarea 
                                     name="custom_voice" 
                                     placeholder="Descreva um tipo de voz ou narração específica que não está nas opções abaixo..."
                                     rows="3"></textarea>
                             </div>

                             <!-- Coluna 2: Controles de navegação -->
                            <div class="tab-navigation">
                                <div class="nav-buttons">
                                    <button type="button" class="btn btn-secondary" onclick="goToFirstTab()" title="Início">
                                        <i class="material-icons">home</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="prevTab()" title="Anterior">
                                        <i class="material-icons">arrow_back</i>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="nextTab()" title="Próxima">
                                        <i class="material-icons">arrow_forward</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="goToLastTab()" title="Fim">
                                        <i class="material-icons">flag</i>
                                    </button>
                                </div>
                                <button type="button" class="btn-prompt" onclick="gerarPrompt()">
                                    PROMPT
                                </button>
                            </div>


                            <!-- Coluna 3: Espaço para propaganda -->
                            <div class="advertisement-container">
                                <div class="advertisement-content">
                                    <i class="material-icons" style="font-size: 2rem; color: var(--text-muted);">campaign</i>
                                    <div class="advertisement-placeholder">
                                        Espaço para propaganda<br>
                                        Anúncios e promoções
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>


                    <!-- ABA 10: AÇÃO -->
                    <div class="tab-content" id="tab-acao">
                        <div class="tab-header">
                            <h2><i class="material-icons">play_arrow</i> Ações e Movimentos</h2>
                            <p>Configure ações, movimentos e atividades dos personagens</p>
                        </div>

                        <div class="categories-grid">
                            <!-- Ações Corporais -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">directions_run</i>
                                    </div>
                                    <h3 class="category-title">Ações Corporais</h3>
                                </div>
                                
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="acao" data-value="correndo">
                                        <i class="material-icons">directions_run</i>
                                        <span>Correndo</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="caminhando">
                                        <i class="material-icons">directions_walk</i>
                                        <span>Caminhando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="saltando">
                                        <i class="material-icons">fitness_center</i>
                                        <span>Saltando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="dancando">
                                        <i class="material-icons">music_note</i>
                                        <span>Dançando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="sentado">
                                        <i class="material-icons">chair</i>
                                        <span>Sentado</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="deitado">
                                        <i class="material-icons">bed</i>
                                        <span>Deitado</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Expressões Faciais -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">sentiment_satisfied</i>
                                    </div>
                                    <h3 class="category-title">Expressões</h3>
                                </div>
                                
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="acao" data-value="sorrindo">
                                        <i class="material-icons">sentiment_very_satisfied</i>
                                        <span>Sorrindo</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="pensativo">
                                        <i class="material-icons">psychology</i>
                                        <span>Pensativo</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="surpreso">
                                        <i class="material-icons">sentiment_neutral</i>
                                        <span>Surpreso</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="concentrado">
                                        <i class="material-icons">visibility</i>
                                        <span>Concentrado</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="conversando">
                                        <i class="material-icons">chat</i>
                                        <span>Conversando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="gritando">
                                        <i class="material-icons">volume_up</i>
                                        <span>Gritando</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Gestos com Mãos -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">pan_tool</i>
                                    </div>
                                    <h3 class="category-title">Gestos</h3>
                                </div>
                                
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="acao" data-value="apontando">
                                        <i class="material-icons">touch_app</i>
                                        <span>Apontando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="acenando">
                                        <i class="material-icons">waving_hand</i>
                                        <span>Acenando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="aplaudindo">
                                        <i class="material-icons">celebration</i>
                                        <span>Aplaudindo</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="segurando">
                                        <i class="material-icons">pan_tool</i>
                                        <span>Segurando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="escrevendo">
                                        <i class="material-icons">edit</i>
                                        <span>Escrevendo</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="digitando">
                                        <i class="material-icons">keyboard</i>
                                        <span>Digitando</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Interações -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">handshake</i>
                                    </div>
                                    <h3 class="category-title">Interações</h3>
                                </div>
                                
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="acao" data-value="cumprimentando">
                                        <i class="material-icons">handshake</i>
                                        <span>Cumprimentando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="abraçando">
                                        <i class="material-icons">favorite</i>
                                        <span>Abraçando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="ensinando">
                                        <i class="material-icons">school</i>
                                        <span>Ensinando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="apresentando">
                                        <i class="material-icons">present_to_all</i>
                                        <span>Apresentando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="ajudando">
                                        <i class="material-icons">help</i>
                                        <span>Ajudando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="observando">
                                        <i class="material-icons">visibility</i>
                                        <span>Observando</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Movimentos Dinâmicos -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">speed</i>
                                    </div>
                                    <h3 class="category-title">Dinâmicos</h3>
                                </div>
                                
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="acao" data-value="voando">
                                        <i class="material-icons">flight</i>
                                        <span>Voando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="escalando">
                                        <i class="material-icons">terrain</i>
                                        <span>Escalando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="nadando">
                                        <i class="material-icons">pool</i>
                                        <span>Nadando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="pedalando">
                                        <i class="material-icons">pedal_bike</i>
                                        <span>Pedalando</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="dirigindo">
                                        <i class="material-icons">drive_eta</i>
                                        <span>Dirigindo</span>
                                    </div>
                                    <div class="subcategory-card" data-type="acao" data-value="flutuando">
                                        <i class="material-icons">air</i>
                                        <span>Flutuando</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Container de 3 colunas na base -->
                        <div class="bottom-controls-container">
                            <!-- Coluna 1: Campo de descrição personalizada -->
                            <div class="custom-description">
                                <label for="custom_action">
                                    <i class="material-icons">edit</i>
                                    Ação Personalizada
                                </label>
                                <textarea 
                                    id="custom_action" 
                                    name="custom_action" 
                                    placeholder="Descreva uma ação ou movimento específico que não está nas opções abaixo..."
                                    rows="3"></textarea>
                            </div>

                            <!-- Coluna 2: Controles de navegação -->
                            <div class="tab-navigation">
                                <div class="nav-buttons">
                                    <button type="button" class="btn btn-secondary" onclick="goToFirstTab()" title="Início">
                                        <i class="material-icons">home</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="prevTab()" title="Anterior">
                                        <i class="material-icons">arrow_back</i>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="nextTab()" title="Próxima">
                                        <i class="material-icons">arrow_forward</i>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="goToLastTab()" title="Fim">
                                        <i class="material-icons">flag</i>
                                    </button>
                                </div>
                                <button type="button" class="btn-prompt" onclick="gerarPrompt()">
                                    PROMPT
                                </button>
                            </div>


                            <!-- Coluna 3: Propaganda ou conteúdo promocional -->
                            <div class="advertisement-container">
                                <div class="advertisement-content">
                                    <div class="advertisement-placeholder">
                                        Espaço para propaganda
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>



                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/gerador-prompt-modern.js"></script>
    <script src="assets/js/avatar-compact.js"></script>
    <script src="assets/js/avatar-compact-extended.js"></script>
    <script src="assets/js/avatar-manager-compact-modern.js"></script>
    
    <!-- Otimização de carregamento e remoção de preloader -->
    <script>
        // Remove preloader rapidamente após carregamento da página
        document.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('pagePreloader');
            
            // Performance otimizada - remove preloader após 500ms
            setTimeout(() => {
                if (preloader) {
                    preloader.classList.add('hidden');
                    // Remove completamente do DOM após transição
                    setTimeout(() => {
                        if (preloader.parentNode) {
                            preloader.parentNode.removeChild(preloader);
                        }
                    }, 300);
                }
            }, 500);
            
            // Força inicialização das abas estáticas
            if (window.promptGenerator) {
                window.promptGenerator.loadedTabs.add('qualidade');
                window.promptGenerator.loadedTabs.add('avatar');
                window.promptGenerator.loadedTabs.add('camera');
                window.promptGenerator.loadedTabs.add('voz');
                window.promptGenerator.loadedTabs.add('acao');
                
                // Inicializa funcionalidade das abas carregadas
                ['qualidade', 'avatar', 'camera', 'voz', 'acao'].forEach(tabName => {
                    window.promptGenerator.initializeTabContent(tabName);
                });
            }
            
            // Inicializar sistema de avatar compacto moderno
            if (window.avatarManager) {
                window.avatarManager.init();
                console.log('✅ Sistema de Avatar Compacto Moderno inicializado');
            } else {
                // Aguardar carregamento do script
                setTimeout(() => {
                    if (window.avatarManager) {
                        window.avatarManager.init();
                        console.log('✅ Sistema de Avatar Compacto Moderno inicializado (atrasado)');
                    }
                }, 1000);
            }
        });
        
        // Fallback para caso o script principal não carregue
        window.addEventListener('load', function() {
            const preloader = document.getElementById('pagePreloader');
            if (preloader && !preloader.classList.contains('hidden')) {
                preloader.classList.add('hidden');
                setTimeout(() => {
                    if (preloader.parentNode) {
                        preloader.parentNode.removeChild(preloader);
                    }
                }, 300);
            }
        });
    </script>
    
    <?php
    // Adicionar JavaScript de integração com sistema dinâmico de cenas para ambiente, estilo visual e iluminação
    if ($cenaRenderer) {
        echo $cenaRenderer->gerarJavaScriptIntegracao(['ambiente', 'estilo_visual', 'iluminacao']);
    }
    ?>
</body>
</html>