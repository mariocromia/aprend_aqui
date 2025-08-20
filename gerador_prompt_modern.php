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
        
        /* Cards de seleção de tipo */
        .avatar-type-selection {
            padding: 1rem 0;
        }
        
        .type-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
        }
        
        .type-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1.25rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        
        .type-card:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary, #3b82f6);
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(59, 130, 246, 0.2);
        }
        
        .type-card.selected {
            background: rgba(59, 130, 246, 0.15);
            border-color: var(--primary, #3b82f6);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
        }
        
        .type-icon {
            margin-bottom: 0.75rem;
        }
        
        .type-icon .material-icons {
            font-size: 2rem;
            color: var(--primary, #3b82f6);
            transition: all 0.3s ease;
        }
        
        .type-card:hover .type-icon .material-icons {
            transform: scale(1.1);
            color: #60a5fa;
        }
        
        .type-card h5 {
            margin: 0 0 0.5rem 0;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary, #e2e8f0);
        }
        
        .type-card p {
            margin: 0;
            font-size: 0.75rem;
            color: var(--text-muted, #64748b);
            line-height: 1.4;
        }
        
        /* Formulários dinâmicos inline */
        .dynamic-forms-inline {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            animation: slideInDown 0.3s ease-out;
        }
        
        .selected-type-indicator {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .type-indicator-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .selected-type-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary, #e2e8f0);
        }
        
        .btn-clear-selection {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 6px;
            padding: 6px 8px;
            color: var(--text-muted, #64748b);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .btn-clear-selection:hover {
            background: rgba(255, 255, 255, 0.12);
            color: var(--text-primary, #e2e8f0);
        }
        
        .btn-clear-selection .material-icons {
            font-size: 18px;
        }
        
        .avatar-form {
            display: none; /* Oculto por padrão */
            animation: fadeIn 0.3s ease-out;
        }
        
        .avatar-form.active {
            display: block !important; /* Forçar exibição quando ativo */
        }
        
        /* Animações */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
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
                                            <p>Escolha o tipo de avatar para começar</p>
                                        </div>
                                        
                                        <!-- Seleção de Tipo - Cards -->
                                        <div class="avatar-type-selection" id="avatar-type-selection">
                                            <div class="type-cards-grid">
                                                <div class="type-card" data-type="humano">
                                                    <div class="type-icon">
                                                        <i class="material-icons">person</i>
                                                    </div>
                                                    <h5>Humano</h5>
                                                    <p>Personagens humanos com características realistas</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="animal">
                                                    <div class="type-icon">
                                                        <i class="material-icons">pets</i>
                                                    </div>
                                                    <h5>Animal</h5>
                                                    <p>Criaturas animais domésticas ou selvagens</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="fantastico">
                                                    <div class="type-icon">
                                                        <i class="material-icons">auto_fix_high</i>
                                                    </div>
                                                    <h5>Fantástico</h5>
                                                    <p>Seres mágicos, elfos, dragões e criaturas míticas</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="extraterrestre">
                                                    <div class="type-icon">
                                                        <i class="material-icons">rocket_launch</i>
                                                    </div>
                                                    <h5>Extraterrestre</h5>
                                                    <p>Alienígenas e seres de outros planetas</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="robotico">
                                                    <div class="type-icon">
                                                        <i class="material-icons">smart_toy</i>
                                                    </div>
                                                    <h5>Robótico/IA</h5>
                                                    <p>Robôs, androides e inteligências artificiais</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Formulários Dinâmicos Inline (aparecem abaixo dos cards) -->
                                        <div class="dynamic-forms-inline" id="dynamic-forms-inline" style="display: none;">
                                            <!-- Indicador do tipo selecionado -->
                                            <div class="selected-type-indicator">
                                                <div class="type-indicator-content">
                                                    <span class="selected-type-name" id="selected-type-name"></span>
                                                    <button type="button" class="btn-clear-selection" id="btn-clear-selection" title="Limpar seleção">
                                                        <i class="material-icons">close</i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Formulário para Humano -->
                                            <form class="avatar-form humano-form" id="humano-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="humano-name">Nome</label>
                                                        <input type="text" id="humano-name" name="name" placeholder="Ex: Elena Rodriguez" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="humano-gender">Gênero</label>
                                                            <select id="humano-gender" name="gender">
                                                                <option value="masculino">Masculino</option>
                                                                <option value="feminino">Feminino</option>
                                                                <option value="outro">Outro</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="humano-age">Idade</label>
                                                            <select id="humano-age" name="age">
                                                                <option value="crianca">Criança (5-12)</option>
                                                                <option value="adolescente">Adolescente (13-17)</option>
                                                                <option value="jovem_adulto" selected>Jovem Adulto (18-30)</option>
                                                                <option value="adulto">Adulto (31-50)</option>
                                                                <option value="meia_idade">Meia-idade (51-65)</option>
                                                                <option value="idoso">Idoso (65+)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="humano-ethnicity">Etnia</label>
                                                            <select id="humano-ethnicity" name="ethnicity">
                                                                <option value="brasileiro" selected>Brasileiro</option>
                                                                <option value="caucasiano">Caucasiano</option>
                                                                <option value="afrodescendente">Afrodescendente</option>
                                                                <option value="asiatico">Asiático</option>
                                                                <option value="latino">Latino</option>
                                                                <option value="indigena">Indígena</option>
                                                                <option value="misto">Miscigenado</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="humano-body-type">Tipo Físico</label>
                                                            <select id="humano-body-type" name="body_type">
                                                                <option value="magro">Magro/Esbelto</option>
                                                                <option value="atletico" selected>Atlético</option>
                                                                <option value="musculoso">Musculoso</option>
                                                                <option value="curvilineo">Curvilíneo</option>
                                                                <option value="robusto">Robusto</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="humano-hair-color">Cor do Cabelo</label>
                                                            <select id="humano-hair-color" name="hair_color">
                                                                <option value="preto" selected>Preto</option>
                                                                <option value="castanho_escuro">Castanho escuro</option>
                                                                <option value="castanho_claro">Castanho claro</option>
                                                                <option value="loiro_escuro">Loiro escuro</option>
                                                                <option value="loiro_claro">Loiro claro</option>
                                                                <option value="ruivo">Ruivo</option>
                                                                <option value="grisalho">Grisalho</option>
                                                                <option value="branco">Branco</option>
                                                                <option value="careca">Careca</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="humano-eye-color">Cor dos Olhos</label>
                                                            <select id="humano-eye-color" name="eye_color">
                                                                <option value="castanho" selected>Castanho</option>
                                                                <option value="azul">Azul</option>
                                                                <option value="verde">Verde</option>
                                                                <option value="mel">Mel</option>
                                                                <option value="cinza">Cinza</option>
                                                                <option value="preto">Preto</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="humano-profession">Profissão</label>
                                                        <input type="text" id="humano-profession" name="profession" placeholder="Ex: Médico, Artista, Estudante">
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="humano-description">Descrição</label>
                                                        <textarea id="humano-description" name="description" placeholder="Descreva características específicas..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearForm('humano')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Avatar Humano
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Animal -->
                                            <form class="avatar-form animal-form" id="animal-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="animal-name">Nome</label>
                                                        <input type="text" id="animal-name" name="name" placeholder="Ex: Rex, Miau, Lobo Alfa" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="animal-species">Espécie</label>
                                                            <select id="animal-species" name="species" required>
                                                                <option value="">Selecione...</option>
                                                                <option value="gato">Gato</option>
                                                                <option value="cachorro">Cachorro</option>
                                                                <option value="lobo">Lobo</option>
                                                                <option value="leao">Leão</option>
                                                                <option value="tigre">Tigre</option>
                                                                <option value="urso">Urso</option>
                                                                <option value="aguia">Águia</option>
                                                                <option value="coruja">Coruja</option>
                                                                <option value="serpente">Serpente</option>
                                                                <option value="outro">Outro</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="animal-size">Tamanho</label>
                                                            <select id="animal-size" name="size">
                                                                <option value="pequeno">Pequeno</option>
                                                                <option value="medio" selected>Médio</option>
                                                                <option value="grande">Grande</option>
                                                                <option value="gigante">Gigante</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="animal-fur-pattern">Padrão da Pelagem</label>
                                                            <input type="text" id="animal-fur-pattern" name="fur_pattern" placeholder="Ex: Listrado, Manchado, Sólido">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="animal-primary-color">Cor Primária</label>
                                                            <input type="text" id="animal-primary-color" name="primary_color" placeholder="Ex: Marrom, Preto, Branco">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="animal-description">Descrição</label>
                                                        <textarea id="animal-description" name="description" placeholder="Descreva características específicas do animal..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearForm('animal')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Avatar Animal
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Fantástico -->
                                            <form class="avatar-form fantastico-form" id="fantastico-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="fantastico-name">Nome</label>
                                                        <input type="text" id="fantastico-name" name="name" placeholder="Ex: Elrond, Draconius, Lyralei" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="fantastico-type">Tipo de Criatura</label>
                                                            <select id="fantastico-type" name="fantasy_type" required>
                                                                <option value="">Selecione...</option>
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
                                                                <option value="unicornio">Unicórnio</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="fantastico-gender">Gênero</label>
                                                            <select id="fantastico-gender" name="gender">
                                                                <option value="neutro">Neutro</option>
                                                                <option value="masculino">Masculino</option>
                                                                <option value="feminino">Feminino</option>
                                                                <option value="outro">Outro</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="fantastico-abilities">Habilidades Mágicas</label>
                                                        <textarea id="fantastico-abilities" name="magical_abilities" rows="2" placeholder="Ex: Controle de fogo, telepatia, cura"></textarea>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="fantastico-features">Características Especiais</label>
                                                        <textarea id="fantastico-features" name="special_features" rows="2" placeholder="Ex: Asas, chifres, escamas, aura luminosa"></textarea>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="fantastico-description">Descrição</label>
                                                        <textarea id="fantastico-description" name="description" placeholder="Descreva o ser fantástico..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearForm('fantastico')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Avatar Fantástico
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Extraterrestre -->
                                            <form class="avatar-form extraterrestre-form" id="extraterrestre-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="extraterrestre-name">Nome</label>
                                                        <input type="text" id="extraterrestre-name" name="name" placeholder="Ex: Zyx'tel, Commander Kral" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="extraterrestre-origin">Planeta de Origem</label>
                                                            <input type="text" id="extraterrestre-origin" name="alien_origin" placeholder="Ex: Andrômeda, Zeta Reticuli">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="extraterrestre-eyes">Número de Olhos</label>
                                                            <select id="extraterrestre-eyes" name="number_of_eyes">
                                                                <option value="1">1</option>
                                                                <option value="2" selected>2</option>
                                                                <option value="3">3</option>
                                                                <option value="4">4</option>
                                                                <option value="6">6</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="extraterrestre-skin">Textura da Pele</label>
                                                            <select id="extraterrestre-skin" name="skin_texture">
                                                                <option value="lisa">Lisa</option>
                                                                <option value="escamosa">Escamosa</option>
                                                                <option value="rugosa">Rugosa</option>
                                                                <option value="metalica">Metálica</option>
                                                                <option value="translucida">Translúcida</option>
                                                                <option value="cristalina">Cristalina</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="extraterrestre-communication">Comunicação</label>
                                                            <select id="extraterrestre-communication" name="communication_method">
                                                                <option value="verbal">Verbal</option>
                                                                <option value="telepatico">Telepático</option>
                                                                <option value="gestual">Gestual</option>
                                                                <option value="luminoso">Sinais Luminosos</option>
                                                                <option value="quimico">Químico</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="extraterrestre-description">Descrição</label>
                                                        <textarea id="extraterrestre-description" name="description" placeholder="Descreva o alienígena..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearForm('extraterrestre')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Avatar Extraterrestre
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Robótico/IA -->
                                            <form class="avatar-form robotico-form" id="robotico-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="robotico-name">Nome</label>
                                                        <input type="text" id="robotico-name" name="name" placeholder="Ex: ARIA, X-01, Commander Data" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="robotico-type">Tipo de Robô</label>
                                                            <select id="robotico-type" name="robot_type" required>
                                                                <option value="">Selecione...</option>
                                                                <option value="android_humanoid">Android Humanoide</option>
                                                                <option value="cyborg">Cyborg</option>
                                                                <option value="robo_industrial">Robô Industrial</option>
                                                                <option value="ia_holografica">IA Holográfica</option>
                                                                <option value="mecha">Mecha</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="robotico-ai-level">Nível de IA</label>
                                                            <select id="robotico-ai-level" name="ai_level">
                                                                <option value="basico">Básico</option>
                                                                <option value="avancado" selected>Avançado</option>
                                                                <option value="superinteligente">Superinteligente</option>
                                                                <option value="consciente">Consciente</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="robotico-power">Fonte de Energia</label>
                                                        <select id="robotico-power" name="power_source">
                                                            <option value="bateria">Bateria</option>
                                                            <option value="energia_solar">Energia Solar</option>
                                                            <option value="nuclear">Nuclear</option>
                                                            <option value="plasma">Plasma</option>
                                                            <option value="cristal_energetico">Cristal Energético</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="robotico-description">Descrição</label>
                                                        <textarea id="robotico-description" name="description" placeholder="Descreva o robô/IA..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearForm('robotico')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Avatar Robótico
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
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




                    <!-- ABA 8: CÂMERA -->
                    <div class="tab-content" id="tab-camera">
                        <div class="tab-header">
                            <h2><i class="material-icons">photo_camera</i> Configurações de Câmera</h2>
                            <p>Configure ângulos, planos e perspectivas da filmagem</p>
                        </div>

                        <!-- Área principal para conteúdo dos avatares -->
                        <div class="avatars-content-area" style="margin: 0;">
                            <!-- Barra de controles superior -->
                            <div class="avatars-toolbar-modern">
                                <!-- Barra de pesquisa (1/3 da largura) -->
                                <div class="search-container">
                                    <div class="search-input-group">
                                        <i class="material-icons search-icon">search</i>
                                        <input type="text" id="camera-search" placeholder="Buscar configurações de câmera..." class="search-input-modern">
                                        <button class="clear-search" id="clear-camera-search" style="display: none;">
                                            <i class="material-icons">clear</i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Filtros e controles -->
                                <div class="controls-container">
                                    <!-- Filtro por tipo -->
                                    <div class="filter-group">
                                        <label class="filter-label">Tipo:</label>
                                        <select id="camera-type-filter" class="filter-select-modern">
                                            <option value="meus" selected>Meus</option>
                                            <option value="publicos">Públicos</option>
                                            <option value="favoritos">Favoritos</option>
                                        </select>
                                    </div>

                                    <!-- Ordenação -->
                                    <div class="filter-group">
                                        <label class="filter-label">Ordenar:</label>
                                        <select id="camera-sort" class="filter-select-modern">
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
                                    <button class="btn-create-avatar-modern" id="btn-create-camera">
                                        <i class="material-icons">add</i>
                                        <span>Criar</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Área dos avatares -->
                            <div class="avatars-display-area" id="camera-display">
                                <!-- Layout dividido horizontalmente -->
                                <div class="avatars-main-layout">
                                    <!-- Bloco esquerdo - Cards de avatares -->
                                    <div class="avatars-grid-section">
                                        <div class="section-header">
                                            <h4>
                                                <i class="material-icons">camera_alt</i>
                                                Minhas Configurações de Câmera
                                            </h4>
                                            <span class="avatar-count" id="camera-count-display">0 configurações</span>
                                        </div>
                                        
                                        <!-- Grid de avatares -->
                                        <div class="avatars-grid" id="camera-grid">
                                            <!-- Estado vazio inicial -->
                                            <div class="empty-grid-state" id="camera-empty-grid-state">
                                                <div class="empty-grid-icon">
                                                    <i class="material-icons">camera_alt</i>
                                                </div>
                                                <p>Nenhuma configuração de câmera criada ainda</p>
                                                <small>Use o formulário ao lado para criar sua primeira configuração</small>
                                            </div>
                                            
                                            <!-- Cards de configurações de câmera serão inseridos aqui dinamicamente -->
                                        </div>
                                    </div>

                                    <!-- Bloco direito - Formulário de cadastro -->
                                    <div class="avatar-form-section">
                                        <div class="section-header">
                                            <h4>
                                                <i class="material-icons">add_circle</i>
                                                Criar Nova Configuração de Câmera
                                            </h4>
                                            <p>Escolha o tipo de configuração para começar</p>
                                        </div>
                                        
                                        <!-- Seleção de Tipo - Cards -->
                                        <div class="avatar-type-selection" id="camera-type-selection">
                                            <div class="type-cards-grid">
                                                <div class="type-card" data-type="angulo">
                                                    <div class="type-icon">
                                                        <i class="material-icons">camera_alt</i>
                                                    </div>
                                                    <h5>Ângulo</h5>
                                                    <p>Configurações de ângulo da câmera</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="plano">
                                                    <div class="type-icon">
                                                        <i class="material-icons">crop</i>
                                                    </div>
                                                    <h5>Plano</h5>
                                                    <p>Configurações de plano da câmera</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="movimento">
                                                    <div class="type-icon">
                                                        <i class="material-icons">video_camera_front</i>
                                                    </div>
                                                    <h5>Movimento</h5>
                                                    <p>Configurações de movimento da câmera</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="lente">
                                                    <div class="type-icon">
                                                        <i class="material-icons">center_focus_strong</i>
                                                    </div>
                                                    <h5>Lente</h5>
                                                    <p>Configurações de lente da câmera</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="iluminacao_camera">
                                                    <div class="type-icon">
                                                        <i class="material-icons">wb_sunny</i>
                                                    </div>
                                                    <h5>Iluminação</h5>
                                                    <p>Configurações de iluminação para câmera</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Formulários Dinâmicos Inline (aparecem abaixo dos cards) -->
                                        <div class="dynamic-forms-inline" id="camera-dynamic-forms-inline" style="display: none;">
                                            <!-- Indicador do tipo selecionado -->
                                            <div class="selected-type-indicator">
                                                <div class="type-indicator-content">
                                                    <span class="selected-type-name" id="camera-selected-type-name"></span>
                                                    <button type="button" class="btn-clear-selection" id="camera-btn-clear-selection" title="Limpar seleção">
                                                        <i class="material-icons">close</i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Formulário para Ângulo -->
                                            <form class="avatar-form angulo-form" id="camera-angulo-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="camera-angulo-name">Nome da Configuração</label>
                                                        <input type="text" id="camera-angulo-name" name="name" placeholder="Ex: Close-up Dramático" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="camera-angulo-tipo">Tipo de Ângulo</label>
                                                            <select id="camera-angulo-tipo" name="angle_type">
                                                                <option value="eye_level">Eye Level</option>
                                                                <option value="low_angle">Low Angle</option>
                                                                <option value="high_angle">High Angle</option>
                                                                <option value="birds_eye">Bird's Eye</option>
                                                                <option value="dutch_angle">Dutch Angle</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="camera-angulo-altura">Altura da Câmera</label>
                                                            <select id="camera-angulo-altura" name="camera_height">
                                                                <option value="baixa">Baixa</option>
                                                                <option value="media" selected>Média</option>
                                                                <option value="alta">Alta</option>
                                                                <option value="muito_alta">Muito Alta</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="camera-angulo-descricao">Descrição</label>
                                                        <textarea id="camera-angulo-descricao" name="description" placeholder="Descreva a configuração de ângulo..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearCameraForm('angulo')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração de Ângulo
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Plano -->
                                            <form class="avatar-form plano-form" id="camera-plano-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="camera-plano-name">Nome da Configuração</label>
                                                        <input type="text" id="camera-plano-name" name="name" placeholder="Ex: Plano Médio Americano" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="camera-plano-tipo">Tipo de Plano</label>
                                                            <select id="camera-plano-tipo" name="shot_type">
                                                                <option value="close_up">Close-up</option>
                                                                <option value="medium_shot">Medium Shot</option>
                                                                <option value="wide_shot">Wide Shot</option>
                                                                <option value="extreme_close_up">Extreme Close-up</option>
                                                                <option value="long_shot">Long Shot</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="camera-plano-distancia">Distância</label>
                                                            <select id="camera-plano-distancia" name="distance">
                                                                <option value="proxima">Próxima</option>
                                                                <option value="media" selected>Média</option>
                                                                <option value="distante">Distante</option>
                                                                <option value="muito_distante">Muito Distante</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="camera-plano-descricao">Descrição</label>
                                                        <textarea id="camera-plano-descricao" name="description" placeholder="Descreva a configuração de plano..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearCameraForm('plano')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração de Plano
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Movimento -->
                                            <form class="avatar-form movimento-form" id="camera-movimento-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="camera-movimento-name">Nome da Configuração</label>
                                                        <input type="text" id="camera-movimento-name" name="name" placeholder="Ex: Travelling Suave" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="camera-movimento-tipo">Tipo de Movimento</label>
                                                            <select id="camera-movimento-tipo" name="movement_type">
                                                                <option value="travelling">Travelling</option>
                                                                <option value="pan">Pan</option>
                                                                <option value="tilt">Tilt</option>
                                                                <option value="zoom">Zoom</option>
                                                                <option value="dolly">Dolly</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="camera-movimento-velocidade">Velocidade</label>
                                                            <select id="camera-movimento-velocidade" name="speed">
                                                                <option value="lenta">Lenta</option>
                                                                <option value="media" selected>Média</option>
                                                                <option value="rapida">Rápida</option>
                                                                <option value="muito_rapida">Muito Rápida</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="camera-movimento-descricao">Descrição</label>
                                                        <textarea id="camera-movimento-descricao" name="description" placeholder="Descreva a configuração de movimento..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearCameraForm('movimento')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração de Movimento
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Lente -->
                                            <form class="avatar-form lente-form" id="camera-lente-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="camera-lente-name">Nome da Configuração</label>
                                                        <input type="text" id="camera-lente-name" name="name" placeholder="Ex: Lente Grande Angular" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="camera-lente-tipo">Tipo de Lente</label>
                                                            <select id="camera-lente-tipo" name="lens_type">
                                                                <option value="grande_angular">Grande Angular</option>
                                                                <option value="normal">Normal</option>
                                                                <option value="teleobjetiva">Teleobjetiva</option>
                                                                <option value="macro">Macro</option>
                                                                <option value="fisheye">Fisheye</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="camera-lente-distancia_focal">Distância Focal</label>
                                                            <input type="text" id="camera-lente-distancia_focal" name="focal_length" placeholder="Ex: 24mm, 50mm, 200mm">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="camera-lente-descricao">Descrição</label>
                                                        <textarea id="camera-lente-descricao" name="description" placeholder="Descreva a configuração de lente..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearCameraForm('lente')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração de Lente
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Iluminação de Câmera -->
                                            <form class="avatar-form iluminacao_camera-form" id="camera-iluminacao_camera-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="camera-iluminacao_camera-name">Nome da Configuração</label>
                                                        <input type="text" id="camera-iluminacao_camera-name" name="name" placeholder="Ex: Iluminação Dramática" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="camera-iluminacao_camera-tipo">Tipo de Iluminação</label>
                                                            <select id="camera-iluminacao_camera-tipo" name="lighting_type">
                                                                <option value="natural">Natural</option>
                                                                <option value="artificial">Artificial</option>
                                                                <option value="mista">Mista</option>
                                                                <option value="dramatica">Dramática</option>
                                                                <option value="suave">Suave</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="camera-iluminacao_camera-intensidade">Intensidade</label>
                                                            <select id="camera-iluminacao_camera-intensidade" name="intensity">
                                                                <option value="baixa">Baixa</option>
                                                                <option value="media" selected>Média</option>
                                                                <option value="alta">Alta</option>
                                                                <option value="muito_alta">Muito Alta</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="camera-iluminacao_camera-descricao">Descrição</label>
                                                        <textarea id="camera-iluminacao_camera-descricao" name="description" placeholder="Descreva a configuração de iluminação..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearCameraForm('iluminacao_camera')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração de Iluminação
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nova seção com dois blocos de 50% -->
                            <div class="avatar-split-section">
                                <!-- Bloco esquerdo -->
                                <div class="split-block left-block">
                                    <div class="block-header">
                                        <h3>
                                            <i class="material-icons">camera_alt</i>
                                            Configurações Salvas
                                        </h3>
                                        <p>Suas configurações de câmera favoritas</p>
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
                                            Dicas de Câmera
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
                                    Descrição Personalizada da Câmera
                                </label>
                                <textarea 
                                    name="custom_camera" 
                                    placeholder="Descreva configurações específicas de câmera que não estão nas opções acima..."
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

                        <!-- Área principal para conteúdo dos avatares -->
                        <div class="avatars-content-area" style="margin: 0;">
                            <!-- Barra de controles superior -->
                            <div class="avatars-toolbar-modern">
                                <!-- Barra de pesquisa (1/3 da largura) -->
                                <div class="search-container">
                                    <div class="search-input-group">
                                        <i class="material-icons search-icon">search</i>
                                        <input type="text" id="voz-search" placeholder="Buscar configurações de voz..." class="search-input-modern">
                                        <button class="clear-search" id="clear-voz-search" style="display: none;">
                                            <i class="material-icons">clear</i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Filtros e controles -->
                                <div class="controls-container">
                                    <!-- Filtro por tipo -->
                                    <div class="filter-group">
                                        <label class="filter-label">Tipo:</label>
                                        <select id="voz-type-filter" class="filter-select-modern">
                                            <option value="meus" selected>Meus</option>
                                            <option value="publicos">Públicos</option>
                                            <option value="favoritos">Favoritos</option>
                                        </select>
                                    </div>

                                    <!-- Ordenação -->
                                    <div class="filter-group">
                                        <label class="filter-label">Ordenar:</label>
                                        <select id="voz-sort" class="filter-select-modern">
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
                                    <button class="btn-create-avatar-modern" id="btn-create-voz">
                                        <i class="material-icons">add</i>
                                        <span>Criar</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Área dos avatares -->
                            <div class="avatars-display-area" id="voz-display">
                                <!-- Layout dividido horizontalmente -->
                                <div class="avatars-main-layout">
                                    <!-- Bloco esquerdo - Cards de avatares -->
                                    <div class="avatars-grid-section">
                                        <div class="section-header">
                                            <h4>
                                                <i class="material-icons">mic</i>
                                                Minhas Configurações de Voz
                                            </h4>
                                            <span class="avatar-count" id="voz-count-display">0 configurações</span>
                                        </div>
                                        
                                        <!-- Grid de avatares -->
                                        <div class="avatars-grid" id="voz-grid">
                                            <!-- Estado vazio inicial -->
                                            <div class="empty-grid-state" id="voz-empty-grid-state">
                                                <div class="empty-grid-icon">
                                                    <i class="material-icons">mic</i>
                                                </div>
                                                <p>Nenhuma configuração de voz criada ainda</p>
                                                <small>Use o formulário ao lado para criar sua primeira configuração</small>
                                            </div>
                                            
                                            <!-- Cards de configurações de voz serão inseridos aqui dinamicamente -->
                                        </div>
                                    </div>

                                    <!-- Bloco direito - Formulário de cadastro -->
                                    <div class="avatar-form-section">
                                        <div class="section-header">
                                            <h4>
                                                <i class="material-icons">add_circle</i>
                                                Criar Nova Configuração de Voz
                                            </h4>
                                            <p>Escolha o tipo de configuração para começar</p>
                                        </div>
                                        
                                        <!-- Seleção de Tipo - Cards -->
                                        <div class="avatar-type-selection" id="voz-type-selection">
                                            <div class="type-cards-grid">
                                                <div class="type-card" data-type="tom">
                                                    <div class="type-icon">
                                                        <i class="material-icons">mood</i>
                                                    </div>
                                                    <h5>Tom</h5>
                                                    <p>Configurações de tom da voz</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="estilo">
                                                    <div class="type-icon">
                                                        <i class="material-icons">record_voice_over</i>
                                                    </div>
                                                    <h5>Estilo</h5>
                                                    <p>Configurações de estilo da voz</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="caracteristica">
                                                    <div class="type-icon">
                                                        <i class="material-icons">person</i>
                                                    </div>
                                                    <h5>Características</h5>
                                                    <p>Características específicas da voz</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="narracao">
                                                    <div class="type-icon">
                                                        <i class="material-icons">auto_stories</i>
                                                    </div>
                                                    <h5>Narração</h5>
                                                    <p>Configurações de narração</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="sotaque">
                                                    <div class="type-icon">
                                                        <i class="material-icons">language</i>
                                                    </div>
                                                    <h5>Sotaque</h5>
                                                    <p>Configurações de sotaque</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Formulários Dinâmicos Inline (aparecem abaixo dos cards) -->
                                        <div class="dynamic-forms-inline" id="voz-dynamic-forms-inline" style="display: none;">
                                            <!-- Indicador do tipo selecionado -->
                                            <div class="selected-type-indicator">
                                                <div class="type-indicator-content">
                                                    <span class="selected-type-name" id="voz-selected-type-name"></span>
                                                    <button type="button" class="btn-clear-selection" id="voz-btn-clear-selection" title="Limpar seleção">
                                                        <i class="material-icons">close</i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Formulário para Tom -->
                                            <form class="avatar-form tom-form" id="voz-tom-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="voz-tom-name">Nome da Configuração</label>
                                                        <input type="text" id="voz-tom-name" name="name" placeholder="Ex: Voz Grave Masculina" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="voz-tom-tipo">Tipo de Tom</label>
                                                            <select id="voz-tom-tipo" name="tone_type">
                                                                <option value="grave">Grave</option>
                                                                <option value="medio">Médio</option>
                                                                <option value="agudo">Agudo</option>
                                                                <option value="grave_masculino">Grave Masculino</option>
                                                                <option value="suave_feminino">Suave Feminino</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="voz-tom-intensidade">Intensidade</label>
                                                            <select id="voz-tom-intensidade" name="intensity">
                                                                <option value="baixa">Baixa</option>
                                                                <option value="media" selected>Média</option>
                                                                <option value="alta">Alta</option>
                                                                <option value="muito_alta">Muito Alta</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="voz-tom-descricao">Descrição</label>
                                                        <textarea id="voz-tom-descricao" name="description" placeholder="Descreva a configuração de tom..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearVozForm('tom')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração de Tom
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Estilo -->
                                            <form class="avatar-form estilo-form" id="voz-estilo-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="voz-estilo-name">Nome da Configuração</label>
                                                        <input type="text" id="voz-estilo-name" name="name" placeholder="Ex: Estilo Documentário" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="voz-estilo-tipo">Tipo de Estilo</label>
                                                            <select id="voz-estilo-tipo" name="style_type">
                                                                <option value="documentario">Documentário</option>
                                                                <option value="radio">Rádio</option>
                                                                <option value="teatro">Teatro</option>
                                                                <option value="cinema">Cinema</option>
                                                                <option value="publicidade">Publicidade</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="voz-estilo-energia">Energia</label>
                                                            <select id="voz-estilo-energia" name="energy">
                                                                <option value="calma">Calma</option>
                                                                <option value="moderada" selected>Moderada</option>
                                                                <option value="energetica">Energética</option>
                                                                <option value="muito_energetica">Muito Energética</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="voz-estilo-descricao">Descrição</label>
                                                        <textarea id="voz-estilo-descricao" name="description" placeholder="Descreva a configuração de estilo..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearVozForm('estilo')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração de Estilo
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Características -->
                                            <form class="avatar-form caracteristica-form" id="voz-caracteristica-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="voz-caracteristica-name">Nome da Configuração</label>
                                                        <input type="text" id="voz-caracteristica-name" name="name" placeholder="Ex: Voz com Sotaque" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="voz-caracteristica-idade">Faixa Etária</label>
                                                            <select id="voz-caracteristica-idade" name="age_range">
                                                                <option value="crianca">Criança</option>
                                                                <option value="adolescente">Adolescente</option>
                                                                <option value="jovem_adulto" selected>Jovem Adulto</option>
                                                                <option value="adulto">Adulto</option>
                                                                <option value="idoso">Idoso</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="voz-caracteristica-genero">Gênero</label>
                                                            <select id="voz-caracteristica-genero" name="gender">
                                                                <option value="masculino">Masculino</option>
                                                                <option value="feminino">Feminino</option>
                                                                <option value="neutro">Neutro</option>
                                                                <option value="outro">Outro</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="voz-caracteristica-descricao">Descrição</label>
                                                        <textarea id="voz-caracteristica-descricao" name="description" placeholder="Descreva as características da voz..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearVozForm('caracteristica')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração de Características
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Narração -->
                                            <form class="avatar-form narracao-form" id="voz-narracao-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="voz-narracao-name">Nome da Configuração</label>
                                                        <input type="text" id="voz-narracao-name" name="name" placeholder="Ex: Narrador Onisciente" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="voz-narracao-tipo">Tipo de Narração</label>
                                                            <select id="voz-narracao-tipo" name="narration_type">
                                                                <option value="onisciente">Onisciente</option>
                                                                <option value="primeira_pessoa">Primeira Pessoa</option>
                                                                <option value="terceira_pessoa">Terceira Pessoa</option>
                                                                <option value="limitada">Limitada</option>
                                                                <option value="objetiva">Objetiva</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="voz-narracao-ritmo">Ritmo</label>
                                                            <select id="voz-narracao-ritmo" name="pace">
                                                                <option value="lento">Lento</option>
                                                                <option value="moderado" selected>Moderado</option>
                                                                <option value="rapido">Rápido</option>
                                                                <option value="variavel">Variável</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="voz-narracao-descricao">Descrição</label>
                                                        <textarea id="voz-narracao-descricao" name="description" placeholder="Descreva a configuração de narração..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearVozForm('narracao')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração de Narração
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Sotaque -->
                                            <form class="avatar-form sotaque-form" id="voz-sotaque-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="voz-sotaque-name">Nome da Configuração</label>
                                                        <input type="text" id="voz-sotaque-name" name="name" placeholder="Ex: Sotaque Brasileiro" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="voz-sotaque-regiao">Região</label>
                                                            <select id="voz-sotaque-regiao" name="region">
                                                                <option value="norte">Norte</option>
                                                                <option value="nordeste">Nordeste</option>
                                                                <option value="centro_oeste">Centro-Oeste</option>
                                                                <option value="sudeste" selected>Sudeste</option>
                                                                <option value="sul">Sul</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="voz-sotaque-intensidade">Intensidade</label>
                                                            <select id="voz-sotaque-intensidade" name="accent_intensity">
                                                                <option value="suave">Suave</option>
                                                                <option value="moderado" selected>Moderado</option>
                                                                <option value="forte">Forte</option>
                                                                <option value="muito_forte">Muito Forte</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="voz-sotaque-descricao">Descrição</label>
                                                        <textarea id="voz-sotaque-descricao" name="description" placeholder="Descreva o sotaque..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearVozForm('sotaque')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração de Sotaque
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nova seção com dois blocos de 50% -->
                            <div class="avatar-split-section">
                                <!-- Bloco esquerdo -->
                                <div class="split-block left-block">
                                    <div class="block-header">
                                        <h3>
                                            <i class="material-icons">mic</i>
                                            Configurações Salvas
                                        </h3>
                                        <p>Suas configurações de voz favoritas</p>
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
                                            Dicas de Voz
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
                                    Descrição Personalizada da Voz
                                </label>
                                <textarea 
                                    name="custom_voice" 
                                    placeholder="Descreva configurações específicas de voz que não estão nas opções acima..."
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

                        <!-- Área principal para conteúdo dos avatares -->
                        <div class="avatars-content-area" style="margin: 0;">
                            <!-- Barra de controles superior -->
                            <div class="avatars-toolbar-modern">
                                <!-- Barra de pesquisa (1/3 da largura) -->
                                <div class="search-container">
                                    <div class="search-input-group">
                                        <i class="material-icons search-icon">search</i>
                                        <input type="text" id="acao-search" placeholder="Buscar configurações de ação..." class="search-input-modern">
                                        <button class="clear-search" id="clear-acao-search" style="display: none;">
                                            <i class="material-icons">clear</i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Filtros e controles -->
                                <div class="controls-container">
                                    <!-- Filtro por tipo -->
                                    <div class="filter-group">
                                        <label class="filter-label">Tipo:</label>
                                        <select id="acao-type-filter" class="filter-select-modern">
                                            <option value="meus" selected>Meus</option>
                                            <option value="publicos">Públicos</option>
                                            <option value="favoritos">Favoritos</option>
                                        </select>
                                    </div>

                                    <!-- Ordenação -->
                                    <div class="filter-group">
                                        <label class="filter-label">Ordenar:</label>
                                        <select id="acao-sort" class="filter-select-modern">
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
                                    <button class="btn-create-avatar-modern" id="btn-create-acao">
                                        <i class="material-icons">add</i>
                                        <span>Criar</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Área dos avatares -->
                            <div class="avatars-display-area" id="acao-display">
                                <!-- Layout dividido horizontalmente -->
                                <div class="avatars-main-layout">
                                    <!-- Bloco esquerdo - Cards de avatares -->
                                    <div class="avatars-grid-section">
                                        <div class="section-header">
                                            <h4>
                                                <i class="material-icons">play_arrow</i>
                                                Minhas Configurações de Ação
                                            </h4>
                                            <span class="avatar-count" id="acao-count-display">0 configurações</span>
                                        </div>
                                        
                                        <!-- Grid de avatares -->
                                        <div class="avatars-grid" id="acao-grid">
                                            <!-- Estado vazio inicial -->
                                            <div class="empty-grid-state" id="acao-empty-grid-state">
                                                <div class="empty-grid-icon">
                                                    <i class="material-icons">play_arrow</i>
                                                </div>
                                                <p>Nenhuma configuração de ação criada ainda</p>
                                                <small>Use o formulário ao lado para criar sua primeira configuração</small>
                                            </div>
                                            
                                            <!-- Cards de configurações de ação serão inseridos aqui dinamicamente -->
                                        </div>
                                    </div>

                                    <!-- Bloco direito - Formulário de cadastro -->
                                    <div class="avatar-form-section">
                                        <div class="section-header">
                                            <h4>
                                                <i class="material-icons">add_circle</i>
                                                Criar Nova Configuração de Ação
                                            </h4>
                                            <p>Escolha o tipo de configuração para começar</p>
                                        </div>
                                        
                                        <!-- Seleção de Tipo - Cards -->
                                        <div class="avatar-type-selection" id="acao-type-selection">
                                            <div class="type-cards-grid">
                                                <div class="type-card" data-type="corporal">
                                                    <div class="type-icon">
                                                        <i class="material-icons">directions_run</i>
                                                    </div>
                                                    <h5>Corporal</h5>
                                                    <p>Ações físicas do corpo</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="expressao">
                                                    <div class="type-icon">
                                                        <i class="material-icons">sentiment_satisfied</i>
                                                    </div>
                                                    <h5>Expressão</h5>
                                                    <p>Expressões faciais</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="gesto">
                                                    <div class="type-icon">
                                                        <i class="material-icons">pan_tool</i>
                                                    </div>
                                                    <h5>Gesto</h5>
                                                    <p>Gestos com mãos</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="interacao">
                                                    <div class="type-icon">
                                                        <i class="material-icons">handshake</i>
                                                    </div>
                                                    <h5>Interação</h5>
                                                    <p>Interações sociais</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="movimento">
                                                    <div class="type-icon">
                                                        <i class="material-icons">speed</i>
                                                    </div>
                                                    <h5>Movimento</h5>
                                                    <p>Movimentos dinâmicos</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Formulários Dinâmicos Inline (aparecem abaixo dos cards) -->
                                        <div class="dynamic-forms-inline" id="acao-dynamic-forms-inline" style="display: none;">
                                            <!-- Indicador do tipo selecionado -->
                                            <div class="selected-type-indicator">
                                                <div class="type-indicator-content">
                                                    <span class="selected-type-name" id="acao-selected-type-name"></span>
                                                    <button type="button" class="btn-clear-selection" id="acao-btn-clear-selection" title="Limpar seleção">
                                                        <i class="material-icons">close</i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Formulário para Ação Corporal -->
                                            <form class="avatar-form corporal-form" id="acao-corporal-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="acao-corporal-name">Nome da Configuração</label>
                                                        <input type="text" id="acao-corporal-name" name="name" placeholder="Ex: Correndo Rapidamente" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="acao-corporal-tipo">Tipo de Ação</label>
                                                            <select id="acao-corporal-tipo" name="action_type">
                                                                <option value="correndo">Correndo</option>
                                                                <option value="caminhando">Caminhando</option>
                                                                <option value="saltando">Saltando</option>
                                                                <option value="dancando">Dançando</option>
                                                                <option value="sentado">Sentado</option>
                                                                <option value="deitado">Deitado</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="acao-corporal-velocidade">Velocidade</label>
                                                            <select id="acao-corporal-velocidade" name="speed">
                                                                <option value="lenta">Lenta</option>
                                                                <option value="moderada" selected>Moderada</option>
                                                                <option value="rapida">Rápida</option>
                                                                <option value="muito_rapida">Muito Rápida</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="acao-corporal-descricao">Descrição</label>
                                                        <textarea id="acao-corporal-descricao" name="description" placeholder="Descreva a ação corporal..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearAcaoForm('corporal')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração Corporal
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Expressão -->
                                            <form class="avatar-form expressao-form" id="acao-expressao-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="acao-expressao-name">Nome da Configuração</label>
                                                        <input type="text" id="acao-expressao-name" name="name" placeholder="Ex: Sorriso Genuíno" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="acao-expressao-tipo">Tipo de Expressão</label>
                                                            <select id="acao-expressao-tipo" name="expression_type">
                                                                <option value="sorrindo">Sorrindo</option>
                                                                <option value="pensativo">Pensativo</option>
                                                                <option value="surpreso">Surpreso</option>
                                                                <option value="concentrado">Concentrado</option>
                                                                <option value="conversando">Conversando</option>
                                                                <option value="gritando">Gritando</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="acao-expressao-intensidade">Intensidade</label>
                                                            <select id="acao-expressao-intensidade" name="intensity">
                                                                <option value="suave">Suave</option>
                                                                <option value="moderada" selected>Moderada</option>
                                                                <option value="intensa">Intensa</option>
                                                                <option value="muito_intensa">Muito Intensa</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="acao-expressao-descricao">Descrição</label>
                                                        <textarea id="acao-expressao-descricao" name="description" placeholder="Descreva a expressão..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearAcaoForm('expressao')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração de Expressão
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Gesto -->
                                            <form class="avatar-form gesto-form" id="acao-gesto-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="acao-gesto-name">Nome da Configuração</label>
                                                        <input type="text" id="acao-gesto-name" name="name" placeholder="Ex: Apontando com Precisão" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="acao-gesto-tipo">Tipo de Gesto</label>
                                                            <select id="acao-gesto-tipo" name="gesture_type">
                                                                <option value="apontando">Apontando</option>
                                                                <option value="acenando">Acenando</option>
                                                                <option value="aplaudindo">Aplaudindo</option>
                                                                <option value="segurando">Segurando</option>
                                                                <option value="escrevendo">Escrevendo</option>
                                                                <option value="digitando">Digitando</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="acao-gesto-parte">Parte do Corpo</label>
                                                            <select id="acao-gesto-parte" name="body_part">
                                                                <option value="maos">Mãos</option>
                                                                <option value="dedos">Dedos</option>
                                                                <option value="braços">Braços</option>
                                                                <option value="cabeca">Cabeça</option>
                                                                <option value="torso">Torso</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="acao-gesto-descricao">Descrição</label>
                                                        <textarea id="acao-gesto-descricao" name="description" placeholder="Descreva o gesto..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearAcaoForm('gesto')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração de Gesto
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Interação -->
                                            <form class="avatar-form interacao-form" id="acao-interacao-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="acao-interacao-name">Nome da Configuração</label>
                                                        <input type="text" id="acao-interacao-name" name="name" placeholder="Ex: Cumprimento Formal" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="acao-interacao-tipo">Tipo de Interação</label>
                                                            <select id="acao-interacao-tipo" name="interaction_type">
                                                                <option value="cumprimentando">Cumprimentando</option>
                                                                <option value="abraçando">Abraçando</option>
                                                                <option value="ensinando">Ensinando</option>
                                                                <option value="apresentando">Apresentando</option>
                                                                <option value="ajudando">Ajudando</option>
                                                                <option value="observando">Observando</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="acao-interacao-nivel">Nível de Intimidade</label>
                                                            <select id="acao-interacao-nivel" name="intimacy_level">
                                                                <option value="formal">Formal</option>
                                                                <option value="casual" selected>Casual</option>
                                                                <option value="intimo">Íntimo</option>
                                                                <option value="profissional">Profissional</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="acao-interacao-descricao">Descrição</label>
                                                        <textarea id="acao-interacao-descricao" name="description" placeholder="Descreva a interação..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearAcaoForm('interacao')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração de Interação
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário para Movimento -->
                                            <form class="avatar-form movimento-form" id="acao-movimento-form" style="display: none;">
                                                <div class="form-grid">
                                                    <div class="form-group">
                                                        <label for="acao-movimento-name">Nome da Configuração</label>
                                                        <input type="text" id="acao-movimento-name" name="name" placeholder="Ex: Voo Suave" required>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            <label for="acao-movimento-tipo">Tipo de Movimento</label>
                                                            <select id="acao-movimento-tipo" name="movement_type">
                                                                <option value="voando">Voando</option>
                                                                <option value="escalando">Escalando</option>
                                                                <option value="nadando">Nadando</option>
                                                                <option value="pedalando">Pedalando</option>
                                                                <option value="dirigindo">Dirigindo</option>
                                                                <option value="flutuando">Flutuando</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="acao-movimento-direcao">Direção</label>
                                                            <select id="acao-movimento-direcao" name="direction">
                                                                <option value="frente">Frente</option>
                                                                <option value="tras">Trás</option>
                                                                <option value="esquerda">Esquerda</option>
                                                                <option value="direita">Direita</option>
                                                                <option value="cima">Cima</option>
                                                                <option value="baixo">Baixo</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="acao-movimento-descricao">Descrição</label>
                                                        <textarea id="acao-movimento-descricao" name="description" placeholder="Descreva o movimento..." rows="3"></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <button type="button" class="btn-secondary" onclick="clearAcaoForm('movimento')">
                                                        <i class="material-icons">clear</i>
                                                        Limpar
                                                    </button>
                                                    <button type="submit" class="btn-primary">
                                                        <i class="material-icons">save</i>
                                                        Criar Configuração de Movimento
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nova seção com dois blocos de 50% -->
                            <div class="avatar-split-section">
                                <!-- Bloco esquerdo -->
                                <div class="split-block left-block">
                                    <div class="block-header">
                                        <h3>
                                            <i class="material-icons">play_arrow</i>
                                            Configurações Salvas
                                        </h3>
                                        <p>Suas configurações de ação favoritas</p>
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
                                            Dicas de Ação
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
                                <label for="custom_action">
                                    <i class="material-icons">edit</i>
                                    Ação Personalizada
                                </label>
                                <textarea 
                                    id="custom_action" 
                                    name="custom_action" 
                                    placeholder="Descreva configurações específicas de ação que não estão nas opções acima..."
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



                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/gerador-prompt-modern.js"></script>
    <!-- Avatar scripts temporariamente comentados para evitar conflitos -->
    <!-- <script src="assets/js/avatar-compact.js"></script>
    <script src="assets/js/avatar-compact-extended.js"></script>
    <script src="assets/js/avatar-manager-compact-modern.js"></script> -->
    
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
            
            // Inicializar apenas nosso sistema inline de avatares
            // (Scripts externos temporariamente desabilitados para evitar modais)
            
            // Inicializar sistema de tipos de avatar inline
            console.log('🎯 Inicializando sistema inline de avatares (sem modais)');
            
            // Aguardar que o DOM esteja totalmente carregado
            setTimeout(() => {
                initAvatarTypeSelection();
                initCameraTypeSelection();
                initVozTypeSelection();
                initAcaoTypeSelection();
                
                // Teste de elementos
                const cards = document.querySelectorAll('.type-card');
                const formsArea = document.getElementById('dynamic-forms-inline');
                console.log(`🔍 Verificação inicial:`, {
                    cards: cards.length,
                    formsArea: !!formsArea
                });
            }, 500);
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
    
    <script>
        // Sistema de seleção de tipos de avatar (INLINE - SEM MODAIS)
        function initAvatarTypeSelection() {
            console.log('📋 Configurando sistema inline de seleção de avatares');
            
            const typeCards = document.querySelectorAll('#tab-avatar .type-card');
            const dynamicFormsInline = document.getElementById('dynamic-forms-inline');
            const selectedTypeName = document.getElementById('selected-type-name');
            const clearSelectionButton = document.getElementById('btn-clear-selection');
            
            // Prevenir qualquer modal que possa ser aberto por outros scripts
            document.addEventListener('click', function(e) {
                // Prevenir abertura de modais nos cards de tipo
                if (e.target.closest('.type-card') && e.target.closest('.modal')) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('🚫 Modal bloqueado - usando sistema inline');
                }
            }, true);
            
            // Adicionar eventos aos cards de tipo
            typeCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const type = this.dataset.type;
                    const typeName = this.querySelector('h5').textContent;
                    
                    console.log(`✅ Card selecionado: ${typeName} (${type}) - Modo INLINE`);
                    
                    // Marcar card como selecionado
                    typeCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    // Mostrar formulário correspondente inline
                    showFormInline(type, typeName);
                });
            });
            
            // Botão limpar seleção
            if (clearSelectionButton) {
                clearSelectionButton.addEventListener('click', function() {
                    clearTypeSelection();
                });
            }
        }
        
        // Sistema de seleção de tipos de câmera
        function initCameraTypeSelection() {
            console.log('📋 Configurando sistema inline de seleção de câmera');
            
            const typeCards = document.querySelectorAll('#tab-camera .type-card');
            const dynamicFormsInline = document.getElementById('camera-dynamic-forms-inline');
            const selectedTypeName = document.getElementById('camera-selected-type-name');
            const clearSelectionButton = document.getElementById('camera-btn-clear-selection');
            
            // Adicionar eventos aos cards de tipo
            typeCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const type = this.dataset.type;
                    const typeName = this.querySelector('h5').textContent;
                    
                    console.log(`✅ Card de câmera selecionado: ${typeName} (${type})`);
                    
                    // Marcar card como selecionado
                    typeCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    // Mostrar formulário correspondente inline
                    showCameraFormInline(type, typeName);
                });
            });
            
            // Botão limpar seleção
            if (clearSelectionButton) {
                clearSelectionButton.addEventListener('click', function() {
                    clearCameraTypeSelection();
                });
            }
        }
        
        // Sistema de seleção de tipos de voz
        function initVozTypeSelection() {
            console.log('📋 Configurando sistema inline de seleção de voz');
            
            const typeCards = document.querySelectorAll('#tab-voz .type-card');
            const dynamicFormsInline = document.getElementById('voz-dynamic-forms-inline');
            const selectedTypeName = document.getElementById('voz-selected-type-name');
            const clearSelectionButton = document.getElementById('voz-btn-clear-selection');
            
            // Adicionar eventos aos cards de tipo
            typeCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const type = this.dataset.type;
                    const typeName = this.querySelector('h5').textContent;
                    
                    console.log(`✅ Card de voz selecionado: ${typeName} (${type})`);
                    
                    // Marcar card como selecionado
                    typeCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    // Mostrar formulário correspondente inline
                    showVozFormInline(type, typeName);
                });
            });
            
            // Botão limpar seleção
            if (clearSelectionButton) {
                clearSelectionButton.addEventListener('click', function() {
                    clearVozTypeSelection();
                });
            }
        }
        
        // Sistema de seleção de tipos de ação
        function initAcaoTypeSelection() {
            console.log('📋 Configurando sistema inline de seleção de ação');
            
            const typeCards = document.querySelectorAll('#tab-acao .type-card');
            const dynamicFormsInline = document.getElementById('acao-dynamic-forms-inline');
            const selectedTypeName = document.getElementById('acao-selected-type-name');
            const clearSelectionButton = document.getElementById('acao-btn-clear-selection');
            
            // Adicionar eventos aos cards de tipo
            typeCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const type = this.dataset.type;
                    const typeName = this.querySelector('h5').textContent;
                    
                    console.log(`✅ Card de ação selecionado: ${typeName} (${type})`);
                    
                    // Marcar card como selecionado
                    typeCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    // Mostrar formulário correspondente inline
                    showAcaoFormInline(type, typeName);
                });
            });
            
            // Botão limpar seleção
            if (clearSelectionButton) {
                clearSelectionButton.addEventListener('click', function() {
                    clearAcaoTypeSelection();
                });
            }
        }
        
        function showFormInline(type, typeName) {
            console.log(`🎯 INICIANDO showFormInline para: ${typeName} (${type})`);
            
            const dynamicFormsInline = document.getElementById('dynamic-forms-inline');
            const selectedTypeName = document.getElementById('selected-type-name');
            const targetForm = document.getElementById(type + '-form');
            
            console.log(`🔍 Elementos encontrados:`, {
                dynamicFormsInline: !!dynamicFormsInline,
                selectedTypeName: !!selectedTypeName,
                targetForm: !!targetForm
            });
            
            if (!dynamicFormsInline || !selectedTypeName || !targetForm) {
                console.error('❌ Elementos necessários não encontrados!');
                return;
            }
            
            // 1. Mostrar área de formulários inline
            dynamicFormsInline.style.display = 'block';
            console.log('✅ Área de formulários exibida');
            
            // 2. Atualizar nome do tipo selecionado
            selectedTypeName.textContent = `Criando ${typeName}`;
            console.log('✅ Nome do tipo atualizado');
            
            // 3. Ocultar todos os formulários primeiro (apenas do tab avatar)
            const allForms = document.querySelectorAll('#tab-avatar .avatar-form');
            console.log(`🔄 Encontrados ${allForms.length} formulários de avatar`);
            
            allForms.forEach((form, index) => {
                form.classList.remove('active');
                form.style.display = 'none'; // Garantir ocultação
                console.log(`   ${index + 1}. ${form.id} - ocultado`);
            });
            
            // 4. Mostrar apenas o formulário específico
            targetForm.classList.add('active');
            targetForm.style.display = 'block'; // Forçar exibição
            console.log(`✅ Formulário ${type}-form EXIBIDO`);
            
            // 5. Verificação final
            const visibleForm = document.querySelector('.avatar-form.active');
            console.log(`🎯 Formulário visível final:`, visibleForm ? visibleForm.id : 'NENHUM');
            
            // 6. Scroll suave
            setTimeout(() => {
                dynamicFormsInline.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                console.log('📜 Scroll executado');
            }, 200);
        }
        
        function clearTypeSelection() {
            const typeCards = document.querySelectorAll('#tab-avatar .type-card');
            const dynamicFormsInline = document.getElementById('dynamic-forms-inline');
            
            // Remover seleção dos cards
            typeCards.forEach(card => card.classList.remove('selected'));
            
            // Ocultar área de formulários
            dynamicFormsInline.style.display = 'none';
            
            // Limpar e ocultar todos os formulários do avatar
            const allForms = document.querySelectorAll('#tab-avatar .avatar-form');
            allForms.forEach(form => {
                form.classList.remove('active');
                form.style.display = 'none'; // Garantir ocultação
                form.reset();
            });
            
            console.log('🧹 Todos os formulários foram limpos e ocultados');
        }
        
        function clearCameraTypeSelection() {
            const typeCards = document.querySelectorAll('#tab-camera .type-card');
            const dynamicFormsInline = document.getElementById('camera-dynamic-forms-inline');
            
            // Remover seleção dos cards
            typeCards.forEach(card => card.classList.remove('selected'));
            
            // Ocultar área de formulários
            dynamicFormsInline.style.display = 'none';
            
            // Limpar e ocultar todos os formulários
            const allForms = document.querySelectorAll('#tab-camera .avatar-form');
            allForms.forEach(form => {
                form.classList.remove('active');
                form.reset();
            });
            
            console.log('🧹 Todos os formulários de câmera foram limpos e ocultados');
        }
        
        function clearVozTypeSelection() {
            const typeCards = document.querySelectorAll('#tab-voz .type-card');
            const dynamicFormsInline = document.getElementById('voz-dynamic-forms-inline');
            
            // Remover seleção dos cards
            typeCards.forEach(card => card.classList.remove('selected'));
            
            // Ocultar área de formulários
            dynamicFormsInline.style.display = 'none';
            
            // Limpar e ocultar todos os formulários
            const allForms = document.querySelectorAll('#tab-voz .avatar-form');
            allForms.forEach(form => {
                form.classList.remove('active');
                form.reset();
            });
            
            console.log('🧹 Todos os formulários de voz foram limpos e ocultados');
        }
        
        function clearAcaoTypeSelection() {
            const typeCards = document.querySelectorAll('#tab-acao .type-card');
            const dynamicFormsInline = document.getElementById('acao-dynamic-forms-inline');
            
            // Remover seleção dos cards
            typeCards.forEach(card => card.classList.remove('selected'));
            
            // Ocultar área de formulários
            dynamicFormsInline.style.display = 'none';
            
            // Limpar e ocultar todos os formulários
            const allForms = document.querySelectorAll('#tab-acao .avatar-form');
            allForms.forEach(form => {
                form.classList.remove('active');
                form.reset();
            });
            
            console.log('🧹 Todos os formulários de ação foram limpos e ocultados');
        }
        
        function clearForm(type) {
            const form = document.getElementById(type + '-form');
            if (form) {
                form.reset();
            }
        }
        
        // Funções para a aba Câmera
        function clearCameraForm(type) {
            const form = document.getElementById('camera-' + type + '-form');
            if (form) {
                form.reset();
            }
        }
        
        // Funções para a aba Voz
        function clearVozForm(type) {
            const form = document.getElementById('voz-' + type + '-form');
            if (form) {
                form.reset();
            }
        }
        
        // Funções para a aba Ação
        function clearAcaoForm(type) {
            const form = document.getElementById('acao-' + type + '-form');
            if (form) {
                form.reset();
            }
        }
        
        // Interceptar submissão dos formulários
        document.addEventListener('submit', function(e) {
            if (e.target.classList.contains('avatar-form')) {
                e.preventDefault();
                
                const formData = new FormData(e.target);
                const formType = e.target.id.replace('-form', '');
                
                // Aqui você pode adicionar a lógica para enviar os dados
                console.log('Criando configuração do tipo:', formType);
                console.log('Dados do formulário:', Object.fromEntries(formData));
                
                // Simular sucesso
                alert(`Configuração ${formType} criada com sucesso!`);
                
                // Limpar seleção após criação baseado na aba atual
                if (e.target.closest('#tab-avatar')) {
                    clearTypeSelection();
                } else if (e.target.closest('#tab-camera')) {
                    clearCameraTypeSelection();
                } else if (e.target.closest('#tab-voz')) {
                    clearVozTypeSelection();
                } else if (e.target.closest('#tab-acao')) {
                    clearAcaoTypeSelection();
                }
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