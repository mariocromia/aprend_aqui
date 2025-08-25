<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth/login-fast.php');
    exit;
}

// Carregar apenas Environment para velocidade
require_once 'includes/Environment.php';

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
            'voice' => $_POST['selected_voice'] ?? null
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
    <title>Gerador de Prompts IA - Abas - <?= Environment::get('APP_NAME', 'Prompt Builder IA') ?></title>
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #5855eb;
            --secondary: #8b5cf6;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --border-hover: #cbd5e1;
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
            --radius: 0.75rem;
            --success: #10b981;
            --error: #ef4444;
        }

        * { box-sizing: border-box; }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            margin: 0;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        .main-container { min-height: 100vh; }

        .header {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: var(--primary);
            font-weight: bold;
            font-size: 1.25rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .content-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 700;
        }

        /* SISTEMA DE ABAS */
        .tabs-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .tabs-nav {
            display: flex;
            background: #f1f5f9;
            border-bottom: 1px solid var(--border);
            overflow-x: auto;
        }

        .tab-button {
            flex: 1;
            min-width: 120px;
            padding: 1rem 0.5rem;
            border: none;
            background: transparent;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-light);
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
            text-align: center;
            white-space: nowrap;
        }

        .tab-button:hover {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .tab-button.active {
            background: white;
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
            padding: 2rem;
            min-height: 600px;
        }

        .tab-content.active {
            display: block;
        }

        .tab-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .tab-header h2 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .tab-header p {
            color: var(--text-light);
            margin: 0;
        }

        /* CATEGORIAS E SUBCATEGORIAS */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .category-section {
            background: #f8fafc;
            border-radius: var(--radius);
            padding: 1.5rem;
            border: 1px solid var(--border);
        }

        .category-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border);
        }

        .category-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
        }

        .category-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0;
        }

        .subcategories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.75rem;
        }

        .subcategory-card {
            background: white;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 0.75rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .subcategory-card:hover {
            border-color: var(--primary);
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .subcategory-card.selected {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.1);
        }

        .subcategory-card.selected::after {
            content: '✓';
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            width: 1.25rem;
            height: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .subcategory-title {
            font-size: 0.875rem;
            font-weight: 600;
            margin: 0 0 0.25rem 0;
            line-height: 1.2;
        }

        .subcategory-desc {
            font-size: 0.75rem;
            color: var(--text-light);
            margin: 0;
            line-height: 1.3;
        }

        /* FORMULÁRIO E PREVIEW */
        .form-section {
            max-width: 800px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-textarea,
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            font-size: 1rem;
            font-family: inherit;
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .prompt-preview {
            background: #f8fafc;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .prompt-preview h3 {
            margin: 0 0 1rem 0;
            font-size: 1.125rem;
            font-weight: 600;
        }

        .prompt-text {
            background: white;
            padding: 1rem;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            font-family: ui-monospace, monospace;
            line-height: 1.5;
            word-break: break-word;
            min-height: 80px;
        }

        /* NAVEGAÇÃO */
        .tab-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ALERTAS */
        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d1fae5;
            border: 1px solid var(--success);
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            border: 1px solid var(--error);
            color: #991b1b;
        }

        /* AVATAR INTERFACE */
        .avatar-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: var(--radius);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .view-toggle {
            display: flex;
            gap: 0.25rem;
            background: white;
            padding: 0.25rem;
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        .view-btn {
            padding: 0.5rem;
            border: none;
            background: transparent;
            border-radius: calc(var(--radius) - 0.25rem);
            cursor: pointer;
            font-size: 1rem;
        }

        .view-btn.active {
            background: var(--primary);
            color: white;
        }

        .avatar-filters {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: white;
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .filter-group label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-light);
        }

        .filter-select,
        .filter-input {
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 0.875rem;
        }

        .filter-input {
            background: white;
        }

        .avatar-container {
            min-height: 300px;
        }

        .avatar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }

        .avatar-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            transition: all 0.2s;
            cursor: pointer;
        }

        .avatar-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .avatar-card.selected {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .avatar-preview {
            position: relative;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .avatar-image {
            width: 60px;
            height: 60px;
            background: var(--bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
            border: 2px solid var(--border);
        }

        .avatar-icon {
            font-size: 1.5rem;
            color: var(--text-light);
        }

        .avatar-actions {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            display: flex;
            gap: 0.25rem;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .avatar-card:hover .avatar-actions {
            opacity: 1;
        }

        .action-btn {
            width: 2rem;
            height: 2rem;
            border: none;
            border-radius: 50%;
            background: white;
            box-shadow: var(--shadow);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .select-btn:hover { background: var(--success); color: white; }
        .edit-btn:hover { background: var(--primary); color: white; }
        .delete-btn:hover { background: var(--error); color: white; }

        .avatar-info {
            padding: 1rem;
        }

        .avatar-name {
            font-size: 1rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
            color: var(--text);
        }

        .avatar-description {
            font-size: 0.875rem;
            color: var(--text-light);
            margin: 0 0 0.75rem 0;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .avatar-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            margin-bottom: 0.5rem;
        }

        .avatar-tag {
            padding: 0.125rem 0.5rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 1rem;
            font-size: 0.75rem;
            color: var(--text-light);
        }

        .avatar-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .no-avatars {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
        }

        .no-avatars .emoji {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        /* NOVA INTERFACE COMPACTA AVATAR */
        .avatar-type-selection {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .selection-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .selection-header h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
        }

        .selection-header p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .type-selector-container {
            max-width: 500px;
            margin: 0 auto;
        }

        .type-selector-container .form-group {
            margin-bottom: 0;
        }

        .type-selector-container label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.75rem;
            color: var(--text);
            font-size: 0.95rem;
        }

        .type-select {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            font-size: 1rem;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .type-select:hover {
            border-color: var(--border-hover);
        }

        .type-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .quick-actions {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border);
        }

        .action-row {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .saved-avatars-section {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .section-header h3 {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .saved-count {
            font-size: 0.875rem;
            color: var(--text-light);
            font-weight: normal;
        }

        .filter-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .compact-search,
        .compact-filter {
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }

        .compact-search {
            min-width: 200px;
        }

        .compact-filter {
            min-width: 120px;
        }

        .compact-search:focus,
        .compact-filter:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }

        .saved-avatars-container {
            min-height: 200px;
        }

        .avatars-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }

        .avatar-saved-card {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .avatar-saved-card:hover {
            border-color: var(--primary);
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .avatar-saved-card.placeholder {
            cursor: default;
            text-align: center;
            flex-direction: column;
            padding: 2rem;
            color: var(--text-muted);
        }

        .avatar-saved-icon {
            width: 48px;
            height: 48px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .avatar-saved-info h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
        }

        .avatar-saved-info p {
            margin: 0;
            font-size: 0.875rem;
            color: var(--text-light);
        }

        .quick-avatar-form {
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .form-header h3 {
            margin: 0;
            font-size: 1.125rem;
        }

        .close-form-btn {
            padding: 0.5rem;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .close-form-btn:hover {
            background: var(--bg);
        }

        .form-content {
            padding: 1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text);
        }

        .compact-input,
        .compact-select {
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 0.875rem;
            background: white;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        .folder-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius);
            max-width: 500px;
            width: 90%;
            max-height: 70vh;
            overflow: hidden;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.125rem;
        }

        .close-modal-btn {
            padding: 0.5rem;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .close-modal-btn:hover {
            background: var(--bg);
        }

        .modal-body {
            padding: 1rem;
            max-height: calc(70vh - 120px);
            overflow-y: auto;
        }

        .folder-actions {
            margin-bottom: 1rem;
        }

        .folder-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .folder-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: var(--bg);
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        .folder-name {
            font-weight: 500;
        }

        .folder-item-actions {
            display: flex;
            gap: 0.25rem;
        }

        /* RESPONSIVO */
        @media (max-width: 768px) {
            .content-container { padding: 1rem; }
            .tab-content { padding: 1rem; }
            .categories-grid { grid-template-columns: 1fr; }
            .category-section { padding: 1rem; }
            .subcategories-grid { grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); }
            .tabs-nav { flex-wrap: wrap; }
            .tab-button { min-width: 100px; font-size: 0.875rem; }

            .avatar-actions {
                flex-direction: column;
                gap: 1rem;
            }

            .filter-row {
                grid-template-columns: 1fr;
            }

            .avatar-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }

            .filter-actions {
                flex-direction: column;
                gap: 0.75rem;
            }

            .compact-search {
                min-width: auto;
            }

            .action-row {
                flex-direction: column;
                gap: 0.75rem;
            }

            .avatars-list {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Ícones em emoji */
        .emoji { font-style: normal; }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <span class="emoji">✨</span>
                    <span><?= Environment::get('APP_NAME', 'Prompt Builder IA') ?></span>
                </a>
                
                <div class="user-menu">
                    <div class="user-info">
                        <span class="emoji">👤</span>
                        <span><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?></span>
                    </div>
                    <a href="auth/logout.php" style="color: #6b7280; text-decoration: none; padding: 0.5rem;">
                        <span class="emoji">🚪</span>
                        Sair
                    </a>
                </div>
            </div>
        </header>

        <!-- Container Principal -->
        <div class="content-container">
            <div class="page-header">
                <h1><span class="emoji">🎨</span> Gerador de Prompts IA</h1>
                <p>Crie prompts profissionais com sistema de abas organizado</p>
            </div>

            <!-- Mensagens -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <span class="emoji">✅</span>
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <span class="emoji">⚠️</span>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <!-- Sistema de Abas -->
            <div class="tabs-container">
                <!-- Navegação das Abas -->
                <div class="tabs-nav">
                    <button class="tab-button active" data-tab="ambiente">
                        <span class="emoji">🌍</span> Cena/Ambiente
                    </button>
                    <button class="tab-button" data-tab="iluminacao">
                        <span class="emoji">💡</span> Iluminação
                    </button>
                    <button class="tab-button" data-tab="avatar">
                        <span class="emoji">👥</span> Avatar/Personagem
                    </button>
                    <button class="tab-button" data-tab="camera">
                        <span class="emoji">📷</span> Câmera
                    </button>
                    <button class="tab-button" data-tab="voz">
                        <span class="emoji">🎤</span> Voz
                    </button>
                    <button class="tab-button" data-tab="prompt">
                        <span class="emoji">📝</span> Seu Prompt
                    </button>
                </div>

                <!-- Form Principal -->
                <form id="promptForm" method="post">
                    <input type="hidden" name="action" value="save_prompt">
                    <input type="hidden" id="selected_environment" name="selected_environment">
                    <input type="hidden" id="selected_lighting" name="selected_lighting">
                    <input type="hidden" id="selected_character" name="selected_character">
                    <input type="hidden" id="selected_camera" name="selected_camera">
                    <input type="hidden" id="selected_voice" name="selected_voice">
                    <input type="hidden" id="settings" name="settings">

                    <!-- ABA 1: CENA/AMBIENTE -->
                    <div class="tab-content active" id="tab-ambiente">
                        <div class="tab-header">
                            <h2><span class="emoji">🌍</span> Cena e Ambiente</h2>
                            <p>Escolha o cenário e localização da sua criação</p>
                        </div>

                        <div class="categories-grid">
                            <!-- NATUREZA -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">🌳</div>
                                    <h3 class="category-title">Natureza</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="environment" data-value="praia_tropical">
                                        <div class="subcategory-title">Praia Tropical</div>
                                        <div class="subcategory-desc">Paraíso com palmeiras</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="cachoeira_gigante">
                                        <div class="subcategory-title">Cachoeira Gigante</div>
                                        <div class="subcategory-desc">Queda d'água majestosa</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="montanha_nevada">
                                        <div class="subcategory-title">Montanha Nevada</div>
                                        <div class="subcategory-desc">Picos cobertos de neve</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="floresta_amazonica">
                                        <div class="subcategory-title">Floresta Amazônica</div>
                                        <div class="subcategory-desc">Selva densa</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="deserto_sahara">
                                        <div class="subcategory-title">Deserto Sahara</div>
                                        <div class="subcategory-desc">Dunas infinitas</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="campo_lavanda">
                                        <div class="subcategory-title">Campo de Lavanda</div>
                                        <div class="subcategory-desc">Ondas roxas aromáticas</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="aurora_boreal">
                                        <div class="subcategory-title">Aurora Boreal</div>
                                        <div class="subcategory-desc">Luzes dançantes polares</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="vulcao_ativo">
                                        <div class="subcategory-title">Vulcão Ativo</div>
                                        <div class="subcategory-desc">Cratera incandescente</div>
                                    </div>
                                </div>
                            </div>

                            <!-- URBANO -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">🏙️</div>
                                    <h3 class="category-title">Urbano</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="environment" data-value="manhattan_ny">
                                        <div class="subcategory-title">Manhattan NY</div>
                                        <div class="subcategory-desc">Selva de concreto</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="tokyo_neon">
                                        <div class="subcategory-title">Tóquio Neon</div>
                                        <div class="subcategory-desc">Metrópole futurística</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="las_vegas_strip">
                                        <div class="subcategory-title">Las Vegas Strip</div>
                                        <div class="subcategory-desc">Cassinos luminosos</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="paris_boulevards">
                                        <div class="subcategory-title">Paris Boulevards</div>
                                        <div class="subcategory-desc">Elegância francesa</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="veneza_canais">
                                        <div class="subcategory-title">Veneza Canais</div>
                                        <div class="subcategory-desc">Cidade aquática</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="favela_rio">
                                        <div class="subcategory-title">Favela Rio</div>
                                        <div class="subcategory-desc">Comunidade colorida</div>
                                    </div>
                                </div>
                            </div>

                            <!-- INTERIOR -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">🏠</div>
                                    <h3 class="category-title">Interior</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="environment" data-value="loft_industrial">
                                        <div class="subcategory-title">Loft Industrial</div>
                                        <div class="subcategory-desc">Estética fabril</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="penthouse_luxo">
                                        <div class="subcategory-title">Penthouse Luxo</div>
                                        <div class="subcategory-desc">Cobertura sofisticada</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="biblioteca_antiga">
                                        <div class="subcategory-title">Biblioteca Antiga</div>
                                        <div class="subcategory-desc">Acervo centenário</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="home_theater">
                                        <div class="subcategory-title">Home Theater</div>
                                        <div class="subcategory-desc">Cinema particular</div>
                                    </div>
                                </div>
                            </div>

                            <!-- AQUÁTICO -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">🌊</div>
                                    <h3 class="category-title">Aquático</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="environment" data-value="oceano_profundo">
                                        <div class="subcategory-title">Oceano Profundo</div>
                                        <div class="subcategory-desc">Abismo marinho</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="recife_coral">
                                        <div class="subcategory-title">Recife Coral</div>
                                        <div class="subcategory-desc">Jardim submarino</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="cidade_atlantis">
                                        <div class="subcategory-title">Atlântida</div>
                                        <div class="subcategory-desc">Cidade submersa</div>
                                    </div>
                                </div>
                            </div>

                            <!-- ESPACIAL -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">🚀</div>
                                    <h3 class="category-title">Espacial</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="environment" data-value="estacao_espacial">
                                        <div class="subcategory-title">Estação Espacial</div>
                                        <div class="subcategory-desc">Laboratório orbital</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="superficie_lua">
                                        <div class="subcategory-title">Superfície Lunar</div>
                                        <div class="subcategory-desc">Paisagem com crateras</div>
                                    </div>
                                    <div class="subcategory-card" data-type="environment" data-value="marte_vermelho">
                                        <div class="subcategory-title">Marte</div>
                                        <div class="subcategory-desc">Planeta vermelho</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-navigation">
                            <div></div>
                            <button type="button" class="btn btn-primary" onclick="nextTab()">
                                Próxima <span class="emoji">➡️</span>
                            </button>
                        </div>
                    </div>

                    <!-- ABA 2: ILUMINAÇÃO -->
                    <div class="tab-content" id="tab-iluminacao">
                        <div class="tab-header">
                            <h2><span class="emoji">💡</span> Iluminação</h2>
                            <p>Configure a iluminação da cena</p>
                        </div>

                        <div class="categories-grid">
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">☀️</div>
                                    <h3 class="category-title">Luz Natural</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="lighting" data-value="natural">
                                        <div class="subcategory-title">Luz Natural</div>
                                        <div class="subcategory-desc">Iluminação do dia</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="dourada">
                                        <div class="subcategory-title">Hora Dourada</div>
                                        <div class="subcategory-desc">Luz quente do pôr do sol</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="azul_hora">
                                        <div class="subcategory-title">Hora Azul</div>
                                        <div class="subcategory-desc">Crepúsculo azul profundo</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="sunrise">
                                        <div class="subcategory-title">Nascer do Sol</div>
                                        <div class="subcategory-desc">Luz dourada matinal</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="daylight">
                                        <div class="subcategory-title">Luz do Dia</div>
                                        <div class="subcategory-desc">Natural balanceada</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="overcast">
                                        <div class="subcategory-title">Nublado</div>
                                        <div class="subcategory-desc">Luz difusa através nuvens</div>
                                    </div>
                                </div>
                            </div>

                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">🌙</div>
                                    <h3 class="category-title">Luz Noturna</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="lighting" data-value="noturna">
                                        <div class="subcategory-title">Noturna</div>
                                        <div class="subcategory-desc">Iluminação noturna</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="neon">
                                        <div class="subcategory-title">Neon</div>
                                        <div class="subcategory-desc">Luzes coloridas vibrantes</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="candlelight">
                                        <div class="subcategory-title">Luz de Vela</div>
                                        <div class="subcategory-desc">Iluminação íntima cálida</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="firelight">
                                        <div class="subcategory-title">Luz de Fogueira</div>
                                        <div class="subcategory-desc">Dançante alaranjada</div>
                                    </div>
                                </div>
                            </div>

                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">🎬</div>
                                    <h3 class="category-title">Luz Artística</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="lighting" data-value="dramatica">
                                        <div class="subcategory-title">Dramática</div>
                                        <div class="subcategory-desc">Contraste alto e sombras</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="cinematic">
                                        <div class="subcategory-title">Cinemática</div>
                                        <div class="subcategory-desc">Iluminação de filme</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="volumetrica">
                                        <div class="subcategory-title">Volumétrica</div>
                                        <div class="subcategory-desc">Raios através fumaca</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="rim_light">
                                        <div class="subcategory-title">Rim Light</div>
                                        <div class="subcategory-desc">Luz de contorno</div>
                                    </div>
                                </div>
                            </div>

                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">✨</div>
                                    <h3 class="category-title">Luz Especial</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="lighting" data-value="magical">
                                        <div class="subcategory-title">Mágica</div>
                                        <div class="subcategory-desc">Sobrenatural brilhante</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="ethereal">
                                        <div class="subcategory-title">Etérea</div>
                                        <div class="subcategory-desc">Celestial transcendente</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="aurora">
                                        <div class="subcategory-title">Aurora Boreal</div>
                                        <div class="subcategory-desc">Luzes polares dançantes</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="underwater">
                                        <div class="subcategory-title">Submersa</div>
                                        <div class="subcategory-desc">Luz filtrada pela água</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-navigation">
                            <button type="button" class="btn btn-secondary" onclick="prevTab()">
                                <span class="emoji">⬅️</span> Anterior
                            </button>
                            <button type="button" class="btn btn-primary" onclick="nextTab()">
                                Próxima <span class="emoji">➡️</span>
                            </button>
                        </div>
                    </div>

                    <!-- ABA 3: AVATAR/PERSONAGEM -->
                    <div class="tab-content" id="tab-avatar">
                        <div class="tab-header">
                            <h2><span class="emoji">👥</span> Avatar e Personagem</h2>
                            <p>Crie e gerencie avatares personalizados de forma rápida e intuitiva</p>
                        </div>

                        <!-- Avatar Type Selection -->
                        <div class="avatar-type-selection">
                            <div class="selection-header">
                                <h3><span class="emoji">🎭</span> Tipo de Ser</h3>
                                <p>Selecione o tipo de personagem para abrir o formulário de criação</p>
                            </div>
                            <div class="type-selector-container">
                                <div class="form-group">
                                    <label for="avatar-type-select">Escolha o tipo de ser:</label>
                                    <select id="avatar-type-select" class="type-select" onchange="window.avatarCompact ? avatarCompact.selectAvatarType(this.value) : null">
                                        <option value="">Selecione um tipo de ser</option>
                                        <option value="human">👤 Humano - Pessoas reais ou personagens humanoides</option>
                                        <option value="animal">🐾 Animal - Criaturas do mundo animal</option>
                                        <option value="fantasy">🧙‍♂️ Fantasia - Seres mágicos e mitológicos</option>
                                        <option value="robot">🤖 Robô/IA - Androides e inteligências artificiais</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="quick-actions">
                            <div class="action-row">
                                <button type="button" class="btn btn-secondary" onclick="window.avatarCompact ? avatarCompact.importAvatar() : null">
                                    <span class="emoji">📤</span> Importar Avatares
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="window.avatarCompact ? avatarCompact.exportAvatars() : null">
                                    <span class="emoji">📥</span> Exportar Avatares
                                </button>
                            </div>
                        </div>

                        <!-- Saved Avatars Section -->
                        <div class="saved-avatars-section">
                            <div class="section-header">
                                <h3><span class="emoji">💾</span> Avatares Salvos <span class="saved-count">0 salvos</span></h3>
                                <div class="filter-actions">
                                    <input type="text" id="avatar-search" class="compact-search" placeholder="Buscar avatares...">
                                    <select id="avatar-filter" class="compact-filter">
                                        <option value="">Todos os tipos</option>
                                        <option value="human">Humanos</option>
                                        <option value="animal">Animais</option>
                                        <option value="fantasy">Fantasia</option>
                                        <option value="robot">Robôs</option>
                                    </select>
                                </div>
                            </div>
                            <div class="saved-avatars-container">
                                <div id="saved-avatars-list" class="avatars-list">
                                    <!-- Avatares salvos serão carregados aqui -->
                                </div>
                            </div>
                        </div>


                        <div class="tab-navigation">
                            <button type="button" class="btn btn-secondary" onclick="prevTab()">
                                <span class="emoji">⬅️</span> Anterior
                            </button>
                            <button type="button" class="btn btn-primary" onclick="nextTab()">
                                Próxima <span class="emoji">➡️</span>
                            </button>
                        </div>
                    </div>

                    <!-- ABA 4: CÂMERA -->
                    <div class="tab-content" id="tab-camera">
                        <div class="tab-header">
                            <h2><span class="emoji">📷</span> Câmera e Técnica</h2>
                            <p>Defina aspectos técnicos e de qualidade</p>
                        </div>

                        <div class="categories-grid">
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">📸</div>
                                    <h3 class="category-title">Tipos de Lente</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="camera" data-value="wide_angle">
                                        <div class="subcategory-title">Grande Angular</div>
                                        <div class="subcategory-desc">Campo de visão amplo</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="telephoto">
                                        <div class="subcategory-title">Teleobjetiva</div>
                                        <div class="subcategory-desc">Lente longa</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="macro">
                                        <div class="subcategory-title">Macro</div>
                                        <div class="subcategory-desc">Detalhes extremos</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="fisheye">
                                        <div class="subcategory-title">Fisheye</div>
                                        <div class="subcategory-desc">Distorção circular</div>
                                    </div>
                                </div>
                            </div>

                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">🎥</div>
                                    <h3 class="category-title">Qualidade</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="camera" data-value="4k">
                                        <div class="subcategory-title">4K Ultra HD</div>
                                        <div class="subcategory-desc">Máxima qualidade</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="8k">
                                        <div class="subcategory-title">8K</div>
                                        <div class="subcategory-desc">Resolução extrema</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="cinematic">
                                        <div class="subcategory-title">Cinemático</div>
                                        <div class="subcategory-desc">Qualidade de cinema</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="hdr">
                                        <div class="subcategory-title">HDR</div>
                                        <div class="subcategory-desc">Alto contraste</div>
                                    </div>
                                </div>
                            </div>

                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">🖼️</div>
                                    <h3 class="category-title">Enquadramento</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="camera" data-value="portrait">
                                        <div class="subcategory-title">Retrato</div>
                                        <div class="subcategory-desc">Orientação vertical</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="landscape">
                                        <div class="subcategory-title">Paisagem</div>
                                        <div class="subcategory-desc">Orientação horizontal</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="square">
                                        <div class="subcategory-title">Quadrado</div>
                                        <div class="subcategory-desc">Formato 1:1</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="panoramica">
                                        <div class="subcategory-title">Panorâmica</div>
                                        <div class="subcategory-desc">Vista ampla</div>
                                    </div>
                                </div>
                            </div>

                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">🎨</div>
                                    <h3 class="category-title">Efeitos</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="camera" data-value="depth_field">
                                        <div class="subcategory-title">Profundidade de Campo</div>
                                        <div class="subcategory-desc">Foco seletivo</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="bokeh">
                                        <div class="subcategory-title">Bokeh</div>
                                        <div class="subcategory-desc">Desfoque cremoso</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="long_exposure">
                                        <div class="subcategory-title">Longa Exposição</div>
                                        <div class="subcategory-desc">Movimento em trilhas</div>
                                    </div>
                                    <div class="subcategory-card" data-type="camera" data-value="tilt_shift">
                                        <div class="subcategory-title">Tilt-Shift</div>
                                        <div class="subcategory-desc">Efeito miniatura</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-navigation">
                            <button type="button" class="btn btn-secondary" onclick="prevTab()">
                                <span class="emoji">⬅️</span> Anterior
                            </button>
                            <button type="button" class="btn btn-primary" onclick="nextTab()">
                                Próxima <span class="emoji">➡️</span>
                            </button>
                        </div>
                    </div>

                    <!-- ABA 5: VOZ -->
                    <div class="tab-content" id="tab-voz">
                        <div class="tab-header">
                            <h2><span class="emoji">🎤</span> Voz e Áudio</h2>
                            <p>Configure características de voz e som</p>
                        </div>

                        <div class="categories-grid">
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">🗣️</div>
                                    <h3 class="category-title">Tom de Voz</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="voice" data-value="grave_masculina">
                                        <div class="subcategory-title">Grave Masculina</div>
                                        <div class="subcategory-desc">Voz profunda autoritária</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="aguda_feminina">
                                        <div class="subcategory-title">Aguda Feminina</div>
                                        <div class="subcategory-desc">Voz clara e melodiosa</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="crianca_alegre">
                                        <div class="subcategory-title">Criança Alegre</div>
                                        <div class="subcategory-desc">Voz jovem energética</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="idoso_sabio">
                                        <div class="subcategory-title">Idoso Sábio</div>
                                        <div class="subcategory-desc">Voz experiente</div>
                                    </div>
                                </div>
                            </div>

                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">🎭</div>
                                    <h3 class="category-title">Estilo</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="voice" data-value="narrador_epico">
                                        <div class="subcategory-title">Narrador Épico</div>
                                        <div class="subcategory-desc">Voz de documentário</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="sussurro_misterioso">
                                        <div class="subcategory-title">Sussurro</div>
                                        <div class="subcategory-desc">Voz baixa misteriosa</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="grito_energico">
                                        <div class="subcategory-title">Energético</div>
                                        <div class="subcategory-desc">Voz alta empolgante</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="robotica_futurista">
                                        <div class="subcategory-title">Robótica</div>
                                        <div class="subcategory-desc">Voz sintética</div>
                                    </div>
                                </div>
                            </div>

                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">🌍</div>
                                    <h3 class="category-title">Sotaque</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="voice" data-value="brasileiro_neutro">
                                        <div class="subcategory-title">Brasileiro Neutro</div>
                                        <div class="subcategory-desc">Sem regionalismo</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="carioca_descontraido">
                                        <div class="subcategory-title">Carioca</div>
                                        <div class="subcategory-desc">Rio de Janeiro</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="paulista_urbano">
                                        <div class="subcategory-title">Paulista</div>
                                        <div class="subcategory-desc">São Paulo urbano</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="nordestino_caloroso">
                                        <div class="subcategory-title">Nordestino</div>
                                        <div class="subcategory-desc">Regional caloroso</div>
                                    </div>
                                </div>
                            </div>

                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">🎵</div>
                                    <h3 class="category-title">Áudio Ambiente</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="voice" data-value="eco_caverna">
                                        <div class="subcategory-title">Eco de Caverna</div>
                                        <div class="subcategory-desc">Reverberação profunda</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="estudio_limpo">
                                        <div class="subcategory-title">Estúdio Limpo</div>
                                        <div class="subcategory-desc">Sem ruído de fundo</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="multidao_distante">
                                        <div class="subcategory-title">Multidão Distante</div>
                                        <div class="subcategory-desc">Vozes ao fundo</div>
                                    </div>
                                    <div class="subcategory-card" data-type="voice" data-value="vento_natureza">
                                        <div class="subcategory-title">Natureza</div>
                                        <div class="subcategory-desc">Sons naturais</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-navigation">
                            <button type="button" class="btn btn-secondary" onclick="prevTab()">
                                <span class="emoji">⬅️</span> Anterior
                            </button>
                            <button type="button" class="btn btn-primary" onclick="nextTab()">
                                Próxima <span class="emoji">➡️</span>
                            </button>
                        </div>
                    </div>

                    <!-- ABA 6: SEU PROMPT -->
                    <div class="tab-content" id="tab-prompt">
                        <div class="tab-header">
                            <h2><span class="emoji">📝</span> Seu Prompt Final</h2>
                            <p>Finalize e salve seu prompt personalizado</p>
                        </div>

                        <div class="form-section">
                            <div class="form-group">
                                <label for="original_prompt" class="form-label">
                                    <span class="emoji">💡</span> Descreva sua ideia
                                </label>
                                <textarea 
                                    id="original_prompt" 
                                    name="original_prompt" 
                                    class="form-textarea" 
                                    placeholder="Ex: Um gato ninja saltando entre prédios em uma cidade cyberpunk..."
                                    rows="4"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="prompt_title" class="form-label">
                                    <span class="emoji">🏷️</span> Título do Prompt (opcional)
                                </label>
                                <input 
                                    type="text" 
                                    id="prompt_title" 
                                    name="title" 
                                    class="form-input"
                                    placeholder="Ex: Gato Ninja Cyberpunk">
                            </div>

                            <!-- Preview do Prompt -->
                            <div class="prompt-preview">
                                <h3>
                                    <span class="emoji">👁️</span>
                                    Preview do Prompt Final
                                </h3>
                                <div id="prompt_preview" class="prompt-text">
                                    Digite sua ideia acima e faça suas seleções nas abas para ver o preview...
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <input type="checkbox" name="is_favorite" style="margin-right: 0.5rem;">
                                    <span class="emoji">❤️</span>
                                    Salvar como favorito
                                </label>
                            </div>
                        </div>

                        <div class="tab-navigation">
                            <button type="button" class="btn btn-secondary" onclick="prevTab()">
                                <span class="emoji">⬅️</span> Anterior
                            </button>
                            <div style="display: flex; gap: 1rem;">
                                <button type="button" class="btn btn-primary" onclick="copyPrompt()">
                                    <span class="emoji">📋</span> Copiar Prompt
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <span class="emoji">💾</span> Salvar Prompt
                                </button>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="enhanced_prompt" name="enhanced_prompt">
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        class PromptGeneratorTabs {
            constructor() {
                this.currentTab = 'ambiente';
                this.selectedData = {
                    environment: null,
                    lighting: null,
                    character: null,
                    camera: null,
                    voice: null
                };
                this.init();
            }
            
            init() {
                this.bindEvents();
                this.updatePreview();
            }
            
            bindEvents() {
                // Navegação de abas
                document.querySelectorAll('.tab-button').forEach(button => {
                    button.addEventListener('click', (e) => {
                        this.switchTab(e.currentTarget.dataset.tab);
                    });
                });
                
                // Seleção de subcategorias
                document.querySelectorAll('.subcategory-card').forEach(card => {
                    card.addEventListener('click', (e) => {
                        this.selectSubcategory(e.currentTarget);
                    });
                });
                
                // Preview em tempo real
                document.getElementById('original_prompt')?.addEventListener('input', () => {
                    this.updatePreview();
                });
            }
            
            switchTab(tabId) {
                // Atualizar botões
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelector(`[data-tab="${tabId}"]`).classList.add('active');
                
                // Atualizar conteúdo
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(`tab-${tabId}`).classList.add('active');
                
                this.currentTab = tabId;
            }
            
            selectSubcategory(card) {
                const type = card.dataset.type;
                const value = card.dataset.value;
                
                // Remove seleção anterior do mesmo tipo
                document.querySelectorAll(`[data-type="${type}"]`).forEach(c => {
                    c.classList.remove('selected');
                });
                
                // Adiciona nova seleção
                card.classList.add('selected');
                
                // Salva dados
                this.selectedData[type] = value;
                document.getElementById(`selected_${type}`).value = value;
                
                this.updatePreview();
            }
            
            updatePreview() {
                const originalPrompt = document.getElementById('original_prompt')?.value || '';
                if (!originalPrompt.trim() && !this.hasSelections()) {
                    document.getElementById('prompt_preview').textContent = 'Digite sua ideia acima e faça suas seleções nas abas para ver o preview...';
                    return;
                }
                
                let enhancedPrompt = originalPrompt;
                
                // Adicionar personagem
                if (this.selectedData.character) {
                    const characterPrompt = this.getCharacterPrompt(this.selectedData.character);
                    enhancedPrompt = characterPrompt + (enhancedPrompt ? ' ' + enhancedPrompt : '');
                }
                
                // Adicionar ambiente
                if (this.selectedData.environment) {
                    const environmentPrompt = this.getEnvironmentPrompt(this.selectedData.environment);
                    enhancedPrompt = enhancedPrompt + (enhancedPrompt ? ' in ' : '') + environmentPrompt;
                }
                
                // Adicionar iluminação
                if (this.selectedData.lighting) {
                    const lightingPrompt = this.getLightingPrompt(this.selectedData.lighting);
                    enhancedPrompt = enhancedPrompt + (enhancedPrompt ? ' with ' : '') + lightingPrompt;
                }
                
                // Adicionar câmera
                if (this.selectedData.camera) {
                    const cameraPrompt = this.getCameraPrompt(this.selectedData.camera);
                    enhancedPrompt = enhancedPrompt + (enhancedPrompt ? ', ' : '') + cameraPrompt;
                }
                
                // Adicionar voz
                if (this.selectedData.voice) {
                    const voicePrompt = this.getVoicePrompt(this.selectedData.voice);
                    enhancedPrompt = enhancedPrompt + (enhancedPrompt ? ', ' : '') + voicePrompt;
                }
                
                if (enhancedPrompt) {
                    enhancedPrompt += ', highly detailed, professional quality, masterpiece';
                }
                
                document.getElementById('prompt_preview').textContent = enhancedPrompt || 'Faça suas seleções para gerar o prompt...';
                document.getElementById('enhanced_prompt').value = enhancedPrompt;
            }
            
            hasSelections() {
                return Object.values(this.selectedData).some(value => value !== null);
            }
            
            getEnvironmentPrompt(env) {
                const environments = {
                    'praia_tropical': 'tropical beach with palm trees',
                    'cachoeira_gigante': 'giant waterfall',
                    'montanha_nevada': 'snowy mountain peaks',
                    'floresta_amazonica': 'Amazon rainforest',
                    'deserto_sahara': 'Sahara desert',
                    'campo_lavanda': 'lavender fields',
                    'aurora_boreal': 'northern lights',
                    'vulcao_ativo': 'active volcano',
                    'manhattan_ny': 'Manhattan New York cityscape',
                    'tokyo_neon': 'neon-lit Tokyo',
                    'las_vegas_strip': 'Las Vegas Strip',
                    'paris_boulevards': 'Paris boulevards',
                    'veneza_canais': 'Venice canals',
                    'favela_rio': 'Rio favela',
                    'loft_industrial': 'industrial loft',
                    'penthouse_luxo': 'luxury penthouse',
                    'biblioteca_antiga': 'ancient library',
                    'home_theater': 'home theater',
                    'oceano_profundo': 'deep ocean',
                    'recife_coral': 'coral reef',
                    'cidade_atlantis': 'lost city of Atlantis',
                    'estacao_espacial': 'space station',
                    'superficie_lua': 'lunar surface',
                    'marte_vermelho': 'red planet Mars'
                };
                return environments[env] || env;
            }
            
            getLightingPrompt(light) {
                const lighting = {
                    'natural': 'natural lighting',
                    'dourada': 'golden hour lighting',
                    'azul_hora': 'blue hour twilight',
                    'sunrise': 'sunrise lighting',
                    'daylight': 'daylight',
                    'overcast': 'overcast lighting',
                    'noturna': 'night lighting',
                    'neon': 'neon lighting',
                    'candlelight': 'candlelight',
                    'firelight': 'firelight',
                    'dramatica': 'dramatic lighting',
                    'cinematic': 'cinematic lighting',
                    'volumetrica': 'volumetric lighting',
                    'rim_light': 'rim lighting',
                    'magical': 'magical lighting',
                    'ethereal': 'ethereal lighting',
                    'aurora': 'aurora lighting',
                    'underwater': 'underwater lighting'
                };
                return lighting[light] || light;
            }
            
            getCharacterPrompt(char) {
                // Check for custom avatar
                if (char === 'custom_avatar' && this.customAvatar) {
                    return window.avatarManager.generateAvatarPrompt(this.customAvatar);
                }
                
                const characters = {
                    'homem_jovem': 'young man',
                    'mulher_jovem': 'young woman',
                    'homem_maduro': 'mature man',
                    'mulher_madura': 'mature woman',
                    'crianca_menino': 'young boy',
                    'crianca_menina': 'young girl',
                    'idoso': 'elderly man',
                    'idosa': 'elderly woman',
                    'executivo': 'business executive',
                    'artista': 'artist',
                    'atleta': 'athlete',
                    'estudante': 'student',
                    'gato_domestico': 'domestic cat',
                    'cao_labrador': 'labrador dog',
                    'leao_majestoso': 'majestic lion',
                    'aguia_real': 'royal eagle',
                    'mago_sabio': 'wise wizard',
                    'guerreiro_epico': 'epic warrior',
                    'elfo_gracioso': 'graceful elf',
                    'dragao_antigo': 'ancient dragon'
                };
                return characters[char] || char;
            }
            
            getCameraPrompt(cam) {
                const cameras = {
                    'wide_angle': 'wide angle lens',
                    'telephoto': 'telephoto lens',
                    'macro': 'macro photography',
                    'fisheye': 'fisheye lens',
                    '4k': '4K ultra HD',
                    '8k': '8K resolution',
                    'cinematic': 'cinematic quality',
                    'hdr': 'HDR',
                    'portrait': 'portrait orientation',
                    'landscape': 'landscape orientation',
                    'square': 'square format',
                    'panoramica': 'panoramic view',
                    'depth_field': 'shallow depth of field',
                    'bokeh': 'bokeh effect',
                    'long_exposure': 'long exposure',
                    'tilt_shift': 'tilt-shift effect'
                };
                return cameras[cam] || cam;
            }
            
            getVoicePrompt(voice) {
                const voices = {
                    'grave_masculina': 'deep masculine voice',
                    'aguda_feminina': 'high feminine voice',
                    'crianca_alegre': 'cheerful child voice',
                    'idoso_sabio': 'wise elderly voice',
                    'narrador_epico': 'epic narrator voice',
                    'sussurro_misterioso': 'mysterious whisper',
                    'grito_energico': 'energetic voice',
                    'robotica_futurista': 'robotic voice',
                    'brasileiro_neutro': 'neutral Brazilian accent',
                    'carioca_descontraido': 'Rio de Janeiro accent',
                    'paulista_urbano': 'São Paulo accent',
                    'nordestino_caloroso': 'Northeast Brazilian accent',
                    'eco_caverna': 'cave echo',
                    'estudio_limpo': 'studio quality',
                    'multidao_distante': 'crowd background',
                    'vento_natureza': 'nature sounds'
                };
                return voices[voice] || voice;
            }
        }
        
        // Funções globais para navegação
        function nextTab() {
            const tabs = ['ambiente', 'iluminacao', 'avatar', 'camera', 'voz', 'prompt'];
            const currentIndex = tabs.indexOf(window.promptGenerator.currentTab);
            if (currentIndex < tabs.length - 1) {
                window.promptGenerator.switchTab(tabs[currentIndex + 1]);
            }
        }
        
        function prevTab() {
            const tabs = ['ambiente', 'iluminacao', 'avatar', 'camera', 'voz', 'prompt'];
            const currentIndex = tabs.indexOf(window.promptGenerator.currentTab);
            if (currentIndex > 0) {
                window.promptGenerator.switchTab(tabs[currentIndex - 1]);
            }
        }
        
        function copyPrompt() {
            const promptText = document.getElementById('prompt_preview').textContent;
            if (promptText && promptText !== 'Faça suas seleções para gerar o prompt...') {
                navigator.clipboard?.writeText(promptText);
                alert('Prompt copiado para a área de transferência!');
            } else {
                alert('Nenhum prompt para copiar. Faça suas seleções primeiro.');
            }
        }
        
        // AVATAR MANAGEMENT CLASS
        class AvatarManager {
            constructor() {
                this.avatars = [];
                this.folders = [{id: 0, name: 'Raiz', parent_id: null}];
                this.currentFolder = null;
                this.selectedAvatar = null;
                this.viewMode = 'grid';
                this.init();
            }
            
            init() {
                this.bindAvatarEvents();
                this.loadFolders();
                this.loadAvatars();
            }
            
            bindAvatarEvents() {
                // Botões principais
                document.getElementById('create-avatar-btn')?.addEventListener('click', () => {
                    this.showQuickForm();
                });
                
                document.getElementById('manage-folders-btn')?.addEventListener('click', () => {
                    this.showFolderModal();
                });
                
                document.getElementById('close-quick-form')?.addEventListener('click', () => {
                    this.hideQuickForm();
                });
                
                document.getElementById('save-quick-avatar')?.addEventListener('click', () => {
                    this.saveQuickAvatar();
                });
                
                document.getElementById('cancel-quick-avatar')?.addEventListener('click', () => {
                    this.hideQuickForm();
                });
                
                // Filtros
                document.getElementById('folder-filter')?.addEventListener('change', (e) => {
                    this.currentFolder = e.target.value || null;
                    this.filterAvatars();
                });
                
                document.getElementById('type-filter')?.addEventListener('change', () => {
                    this.filterAvatars();
                });
                
                document.getElementById('gender-filter')?.addEventListener('change', () => {
                    this.filterAvatars();
                });
                
                document.getElementById('search-filter')?.addEventListener('input', () => {
                    this.filterAvatars();
                });
                
                // View toggle
                document.querySelectorAll('.view-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        this.changeView(e.target.dataset.view);
                    });
                });
                
                // Folder modal
                document.getElementById('close-folder-modal')?.addEventListener('click', () => {
                    this.hideFolderModal();
                });
                
                document.getElementById('create-folder-btn')?.addEventListener('click', () => {
                    this.createFolder();
                });
            }
            
            showQuickForm() {
                document.getElementById('quick-avatar-form').style.display = 'block';
            }
            
            hideQuickForm() {
                document.getElementById('quick-avatar-form').style.display = 'none';
                this.clearQuickForm();
            }
            
            clearQuickForm() {
                document.getElementById('quick-name').value = '';
                document.getElementById('quick-appearance').value = '';
                document.getElementById('quick-clothing').value = '';
                document.getElementById('quick-tags').value = '';
                document.getElementById('quick-gender').value = 'neutro';
                document.getElementById('quick-age').value = 'adulto';
                document.getElementById('quick-folder').value = '0';
            }
            
            async saveQuickAvatar() {
                const data = {
                    nome: document.getElementById('quick-name').value,
                    genero: document.getElementById('quick-gender').value,
                    idade_categoria: document.getElementById('quick-age').value,
                    aparencia: document.getElementById('quick-appearance').value,
                    vestuario: document.getElementById('quick-clothing').value,
                    tags: document.getElementById('quick-tags').value,
                    pasta_id: document.getElementById('quick-folder').value,
                    publico: false
                };
                
                if (!data.nome) {
                    alert('Nome é obrigatório!');
                    return;
                }
                
                try {
                    // Simular salvamento - você pode implementar a chamada AJAX real aqui
                    const avatar = {
                        id: Date.now(),
                        ...data,
                        criado_em: new Date().toISOString(),
                        tags: data.tags ? data.tags.split(',').map(t => t.trim()) : []
                    };
                    
                    this.avatars.push(avatar);
                    this.renderAvatars();
                    this.hideQuickForm();
                    alert('Avatar criado com sucesso!');
                    
                } catch (error) {
                    console.error('Erro ao salvar avatar:', error);
                    alert('Erro ao salvar avatar. Tente novamente.');
                }
            }
            
            showFolderModal() {
                document.getElementById('folder-modal').style.display = 'flex';
                this.renderFolders();
            }
            
            hideFolderModal() {
                document.getElementById('folder-modal').style.display = 'none';
            }
            
            async createFolder() {
                const name = prompt('Nome da nova pasta:');
                if (!name) return;
                
                const folder = {
                    id: Date.now(),
                    name: name,
                    parent_id: 0,
                    criado_em: new Date().toISOString()
                };
                
                this.folders.push(folder);
                this.updateFolderSelects();
                this.renderFolders();
            }
            
            loadFolders() {
                // Simular carregamento de pastas - implementar chamada real aqui
                this.updateFolderSelects();
            }
            
            updateFolderSelects() {
                const selects = ['folder-filter', 'quick-folder'];
                
                selects.forEach(selectId => {
                    const select = document.getElementById(selectId);
                    if (!select) return;
                    
                    // Manter opções padrão
                    const options = select.innerHTML;
                    
                    this.folders.forEach(folder => {
                        if (folder.id !== 0) {
                            const option = document.createElement('option');
                            option.value = folder.id;
                            option.textContent = folder.name;
                            select.appendChild(option);
                        }
                    });
                });
            }
            
            renderFolders() {
                const container = document.getElementById('folder-list');
                if (!container) return;
                
                container.innerHTML = '';
                
                this.folders.forEach(folder => {
                    if (folder.id === 0) return; // Skip root
                    
                    const item = document.createElement('div');
                    item.className = 'folder-item';
                    item.innerHTML = `
                        <div class="folder-info">
                            <span class="emoji">📁</span>
                            <span class="folder-name">${folder.name}</span>
                        </div>
                        <div class="folder-item-actions">
                            <button class="action-btn edit-btn" onclick="window.avatarManager.renameFolder(${folder.id})" title="Renomear">
                                <span class="emoji">✏️</span>
                            </button>
                            <button class="action-btn delete-btn" onclick="window.avatarManager.deleteFolder(${folder.id})" title="Excluir">
                                <span class="emoji">🗑️</span>
                            </button>
                        </div>
                    `;
                    
                    container.appendChild(item);
                });
            }
            
            async renameFolder(folderId) {
                const folder = this.folders.find(f => f.id === folderId);
                if (!folder) return;
                
                const newName = prompt('Novo nome da pasta:', folder.name);
                if (!newName || newName === folder.name) return;
                
                folder.name = newName;
                this.updateFolderSelects();
                this.renderFolders();
            }
            
            async deleteFolder(folderId) {
                if (!confirm('Tem certeza que deseja excluir esta pasta? Os avatares dentro dela serão movidos para a raiz.')) return;
                
                // Mover avatares da pasta para a raiz
                this.avatars.forEach(avatar => {
                    if (avatar.pasta_id == folderId) {
                        avatar.pasta_id = 0;
                    }
                });
                
                // Remover pasta
                this.folders = this.folders.filter(f => f.id !== folderId);
                this.updateFolderSelects();
                this.renderFolders();
                this.renderAvatars();
            }
            
            loadAvatars() {
                // Simular alguns avatares para demonstração
                this.avatars = [
                    {
                        id: 1,
                        nome: "Ana Silva",
                        genero: "feminino",
                        idade_categoria: "adulto",
                        aparencia: "cabelo castanho, olhos verdes, altura média",
                        vestuario: "blazer azul, calça social",
                        tags: ["executiva", "formal", "profissional"],
                        pasta_id: 0,
                        criado_em: "2024-01-15T10:30:00Z"
                    },
                    {
                        id: 2,
                        nome: "João Santos",
                        genero: "masculino",
                        idade_categoria: "jovem",
                        aparencia: "cabelo loiro, olhos azuis, atlético",
                        vestuario: "camiseta casual, jeans",
                        tags: ["jovem", "casual", "atleta"],
                        pasta_id: 0,
                        criado_em: "2024-01-14T15:45:00Z"
                    }
                ];
                
                this.renderAvatars();
            }
            
            renderAvatars() {
                const container = document.getElementById('avatar-grid');
                const noAvatars = document.getElementById('no-avatars');
                const template = document.querySelector('.avatar-card.template');
                
                if (!container || !template) return;
                
                // Limpar avatares existentes (exceto o template)
                container.querySelectorAll('.avatar-card:not(.template)').forEach(card => card.remove());
                
                const filteredAvatars = this.getFilteredAvatars();
                
                if (filteredAvatars.length === 0) {
                    noAvatars.style.display = 'block';
                    return;
                }
                
                noAvatars.style.display = 'none';
                
                filteredAvatars.forEach(avatar => {
                    const card = template.cloneNode(true);
                    card.classList.remove('template');
                    card.style.display = 'block';
                    card.dataset.avatarId = avatar.id;
                    
                    // Preencher dados
                    card.querySelector('.avatar-name').textContent = avatar.nome;
                    card.querySelector('.avatar-description').textContent = [
                        avatar.aparencia,
                        avatar.vestuario
                    ].filter(Boolean).join(' - ') || 'Sem descrição';
                    
                    // Tags
                    const tagsContainer = card.querySelector('.avatar-tags');
                    tagsContainer.innerHTML = '';
                    if (avatar.tags && avatar.tags.length > 0) {
                        avatar.tags.forEach(tag => {
                            const tagSpan = document.createElement('span');
                            tagSpan.className = 'avatar-tag';
                            tagSpan.textContent = tag;
                            tagsContainer.appendChild(tagSpan);
                        });
                    }
                    
                    // Metadados
                    const folderName = this.folders.find(f => f.id == avatar.pasta_id)?.name || 'Raiz';
                    const date = new Date(avatar.criado_em).toLocaleDateString();
                    card.querySelector('.folder-name').textContent = folderName;
                    card.querySelector('.creation-date').textContent = date;
                    
                    // Ícone do avatar
                    const icon = card.querySelector('.avatar-icon');
                    icon.textContent = this.getAvatarIcon(avatar.genero);
                    
                    // Eventos dos botões
                    card.querySelector('.select-btn').addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.selectAvatar(avatar);
                    });
                    
                    card.querySelector('.edit-btn').addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.editAvatar(avatar);
                    });
                    
                    card.querySelector('.delete-btn').addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.deleteAvatar(avatar.id);
                    });
                    
                    container.appendChild(card);
                });
            }
            
            getAvatarIcon(genero) {
                switch (genero) {
                    case 'masculino': return '👨';
                    case 'feminino': return '👩';
                    case 'neutro': return '👤';
                    default: return '👤';
                }
            }
            
            getFilteredAvatars() {
                let filtered = [...this.avatars];
                
                // Filtro por pasta
                if (this.currentFolder !== null && this.currentFolder !== '') {
                    filtered = filtered.filter(avatar => avatar.pasta_id == this.currentFolder);
                }
                
                // Filtro por tipo
                const typeFilter = document.getElementById('type-filter')?.value;
                if (typeFilter) {
                    // Implementar filtro por tipo se necessário
                }
                
                // Filtro por gênero
                const genderFilter = document.getElementById('gender-filter')?.value;
                if (genderFilter) {
                    filtered = filtered.filter(avatar => avatar.genero === genderFilter);
                }
                
                // Filtro de busca
                const searchFilter = document.getElementById('search-filter')?.value.toLowerCase();
                if (searchFilter) {
                    filtered = filtered.filter(avatar => 
                        avatar.nome.toLowerCase().includes(searchFilter) ||
                        (avatar.tags && avatar.tags.some(tag => tag.toLowerCase().includes(searchFilter)))
                    );
                }
                
                return filtered;
            }
            
            filterAvatars() {
                this.renderAvatars();
            }
            
            selectAvatar(avatar) {
                // Remover seleção anterior
                document.querySelectorAll('.avatar-card.selected').forEach(card => {
                    card.classList.remove('selected');
                });
                
                // Adicionar seleção
                const card = document.querySelector(`[data-avatar-id="${avatar.id}"]`);
                if (card) {
                    card.classList.add('selected');
                }
                
                this.selectedAvatar = avatar;
                
                // Atualizar o prompt generator com o avatar selecionado
                if (window.promptGenerator) {
                    const characterPrompt = this.generateAvatarPrompt(avatar);
                    window.promptGenerator.selectedData.character = 'custom_avatar';
                    window.promptGenerator.customAvatar = avatar;
                    document.getElementById('selected_character').value = 'custom_avatar';
                    window.promptGenerator.updatePreview();
                }
                
                alert(`Avatar "${avatar.nome}" selecionado!`);
            }
            
            generateAvatarPrompt(avatar) {
                let prompt = avatar.nome;
                
                if (avatar.genero && avatar.genero !== 'neutro') {
                    prompt += `, ${avatar.genero}`;
                }
                
                if (avatar.idade_categoria) {
                    prompt += `, ${avatar.idade_categoria}`;
                }
                
                if (avatar.aparencia) {
                    prompt += `, ${avatar.aparencia}`;
                }
                
                if (avatar.vestuario) {
                    prompt += `, wearing ${avatar.vestuario}`;
                }
                
                return prompt;
            }
            
            editAvatar(avatar) {
                // Implementar edição completa - por enquanto apenas quick form
                this.populateQuickForm(avatar);
                this.showQuickForm();
            }
            
            populateQuickForm(avatar) {
                document.getElementById('quick-name').value = avatar.nome || '';
                document.getElementById('quick-appearance').value = avatar.aparencia || '';
                document.getElementById('quick-clothing').value = avatar.vestuario || '';
                document.getElementById('quick-tags').value = avatar.tags ? avatar.tags.join(', ') : '';
                document.getElementById('quick-gender').value = avatar.genero || 'neutro';
                document.getElementById('quick-age').value = avatar.idade_categoria || 'adulto';
                document.getElementById('quick-folder').value = avatar.pasta_id || '0';
            }
            
            deleteAvatar(avatarId) {
                const avatar = this.avatars.find(a => a.id === avatarId);
                if (!avatar) return;
                
                if (!confirm(`Tem certeza que deseja excluir o avatar "${avatar.nome}"?`)) return;
                
                this.avatars = this.avatars.filter(a => a.id !== avatarId);
                this.renderAvatars();
            }
            
            changeView(viewMode) {
                this.viewMode = viewMode;
                
                // Atualizar botões
                document.querySelectorAll('.view-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelector(`[data-view="${viewMode}"]`).classList.add('active');
                
                // Implementar mudança de view se necessário
                const grid = document.getElementById('avatar-grid');
                if (viewMode === 'list') {
                    grid.style.gridTemplateColumns = '1fr';
                } else {
                    grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(280px, 1fr))';
                }
            }
        }
        
        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            window.promptGenerator = new PromptGeneratorTabs();
            window.avatarManager = new AvatarManager();
        });
    </script>
</body>
</html>