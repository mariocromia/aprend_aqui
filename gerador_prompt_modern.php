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
            'voice' => $_POST['selected_voice'] ?? null,
            'custom_descriptions' => json_encode([
                'environment' => $_POST['custom_environment'] ?? '',
                'lighting' => $_POST['custom_lighting'] ?? '',
                'character' => $_POST['custom_character'] ?? '',
                'camera' => $_POST['custom_camera'] ?? '',
                'voice' => $_POST['custom_voice'] ?? ''
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
    
    <style>
        /* Import Google Icons */
        @import url('https://fonts.googleapis.com/icon?family=Material+Icons');
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        :root {
            /* Cores principais - Azul e Roxo mais fortes */
            --primary-blue: #1e40af;
            --primary-purple: #7c3aed;
            --secondary-blue: #3b82f6;
            --secondary-purple: #a855f7;
            --accent-cyan: #06b6d4;
            --accent-pink: #ec4899;
            
            /* Gradientes */
            --gradient-primary: linear-gradient(135deg, #1e40af 0%, #7c3aed 100%);
            --gradient-secondary: linear-gradient(135deg, #3b82f6 0%, #a855f7 100%);
            --gradient-accent: linear-gradient(135deg, #06b6d4 0%, #ec4899 100%);
            --gradient-card: linear-gradient(135deg, rgba(30, 64, 175, 0.05) 0%, rgba(124, 58, 237, 0.05) 100%);
            
            /* Cores neutras */
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-card: #334155;
            --bg-light: #f8fafc;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --text-dark: #1e293b;
            
            /* Borders e shadows */
            --border-color: #475569;
            --border-hover: #64748b;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            --shadow-glow: 0 0 20px rgba(124, 58, 237, 0.3);
            
            --radius: 1rem;
            --radius-sm: 0.5rem;
            --radius-lg: 1.5rem;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
        }

        /* Layout de página única */
        .main-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header fixo */
        .header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: var(--gradient-primary);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            box-shadow: var(--shadow-lg);
        }

        .header-content {
            max-width: 1800px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .logo .material-icons {
            font-size: 1.5rem;
            color: var(--accent-cyan);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            color: var(--text-primary);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .btn-logout {
            color: var(--text-secondary);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }

        /* Container principal */
        .content-container {
            flex: 1;
            max-width: 1800px;
            margin: 0 auto;
            padding: 1rem 2rem 2rem 2rem;
            width: 100%;
            overflow: visible;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 2rem;
            align-items: start;
        }

        .page-header {
            text-align: center;
            margin-bottom: -3rem;
            grid-column: 1 / -1;
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 800;
            background: var(--gradient-accent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .page-header p {
            font-size: 1.125rem;
            color: var(--text-secondary);
            font-weight: 300;
        }

        /* Sistema de abas moderno */
        .tabs-container {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            border: 1px solid var(--border-color);
            grid-column: 1 / -1;
        }

        .tabs-nav {
            display: flex;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .tabs-nav::-webkit-scrollbar {
            display: none;
        }

        .tab-button {
            flex: 1;
            min-width: 120px;
            padding: 0.75rem 0.5rem;
            border: none;
            background: transparent;
            cursor: pointer;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            text-align: center;
            white-space: nowrap;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .tab-button .material-icons {
            font-size: 1.25rem;
        }

        .tab-button:hover {
            background: rgba(124, 58, 237, 0.1);
            color: var(--secondary-purple);
        }

        .tab-button.active {
            background: var(--gradient-secondary);
            color: var(--text-primary);
            border-bottom-color: var(--accent-cyan);
            box-shadow: var(--shadow-glow);
        }

        .tab-button.active .material-icons {
            color: var(--accent-cyan);
        }

        /* Conteúdo das abas */
        .tab-content {
            display: none;
            padding: 2rem;
            height: auto;
            overflow: visible;
        }

        .tab-content.active {
            display: block;
        }



        .tab-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .tab-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: var(--gradient-secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .tab-header .material-icons {
            font-size: 2.5rem;
            color: var(--accent-cyan);
        }

        .tab-header p {
            color: var(--text-secondary);
            font-size: 1.25rem;
            font-weight: 300;
        }

        /* Container de 3 colunas na base das abas */
        .bottom-controls-container {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
            align-items: start;
        }

        /* Campo de descrição personalizada */
        .custom-description {
            background: var(--gradient-card);
            border-radius: var(--radius);
            padding: 0.75rem;
            border: 1px solid var(--border-color);
        }

        /* Container de propaganda */
        .advertisement-container {
            background: var(--gradient-card);
            border-radius: var(--radius);
            padding: 1rem;
            border: 1px solid var(--border-color);
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .advertisement-placeholder {
            color: var(--text-muted);
            font-size: 0.875rem;
            font-style: italic;
        }

        .advertisement-content {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 1rem;
        }

        .custom-description label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
        }

        .custom-description .material-icons {
            color: var(--accent-pink);
            font-size: 1.25rem;
        }

        .custom-description textarea {
            width: 100%;
            background: var(--bg-primary);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 0.5rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.875rem;
            resize: vertical;
            min-height: 50px;
            transition: all 0.3s ease;
        }

        .custom-description textarea:focus {
            outline: none;
            border-color: var(--secondary-purple);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }

        .custom-description textarea::placeholder {
            color: var(--text-muted);
        }

        /* Grid de categorias */
        .categories-grid {
            display: flex;
            gap: 1.5rem;
            overflow-x: auto;
            padding-bottom: 1rem;
            scrollbar-width: thin;
            -ms-overflow-style: none;
        }

        .categories-grid::-webkit-scrollbar {
            height: 8px;
        }

        .categories-grid::-webkit-scrollbar-track {
            background: var(--bg-primary);
            border-radius: 4px;
        }

        .categories-grid::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: 4px;
        }

        .categories-grid::-webkit-scrollbar-thumb:hover {
            background: var(--gradient-secondary);
        }

        .category-section {
            background: var(--gradient-card);
            border-radius: var(--radius);
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            min-width: 320px;
            flex-shrink: 0;
        }

        .category-section:hover {
            border-color: var(--secondary-purple);
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .category-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .category-icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            background: var(--gradient-primary);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
        }

        .category-icon .material-icons {
            font-size: 1.75rem;
        }

        .category-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
        }

        /* Grid de subcategorias */
        .subcategories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            max-height: 300px;
            overflow-y: auto;
            padding-right: 0.75rem;
            scrollbar-width: thin;
            -ms-overflow-style: none;
        }

        .subcategories-grid::-webkit-scrollbar {
            width: 6px;
        }

        .subcategories-grid::-webkit-scrollbar-track {
            background: var(--bg-primary);
            border-radius: 3px;
        }

        .subcategories-grid::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: 3px;
        }

        .subcategories-grid::-webkit-scrollbar-thumb:hover {
            background: var(--gradient-secondary);
        }

        .subcategory-card {
            background: var(--bg-primary);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .subcategory-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--gradient-accent);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 0;
        }

        .subcategory-card:hover::before {
            left: 0;
            opacity: 0.1;
        }

        .subcategory-card:hover {
            border-color: var(--accent-cyan);
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .subcategory-card.selected {
            border-color: var(--accent-cyan);
            background: var(--gradient-primary);
            color: var(--text-primary);
            box-shadow: var(--shadow-glow);
        }

        .subcategory-card.selected::after {
            content: 'check_circle';
            font-family: 'Material Icons';
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: var(--accent-cyan);
            color: var(--bg-primary);
            border-radius: 50%;
            width: 1.5rem;
            height: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: bold;
        }

        .subcategory-title {
            font-size: 1rem;
            font-weight: 600;
            margin: 0 0 0.75rem 0;
            line-height: 1.3;
            position: relative;
            z-index: 1;
        }

        .subcategory-desc {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.4;
            position: relative;
            z-index: 1;
        }

        .subcategory-card.selected .subcategory-desc {
            color: var(--text-secondary);
        }

        /* Formulário final */
        .form-section {
            max-width: 1000px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }

        .form-label .material-icons {
            color: var(--accent-pink);
            font-size: 1.5rem;
        }

        .form-textarea,
        .form-input {
            width: 100%;
            background: var(--bg-primary);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 1.25rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 1.125rem;
            transition: all 0.3s ease;
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-textarea:focus,
        .form-input:focus {
            outline: none;
            border-color: var(--secondary-purple);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }

        .form-textarea::placeholder,
        .form-input::placeholder {
            color: var(--text-muted);
        }

        /* Preview do prompt */
        .prompt-preview {
            background: var(--gradient-card);
            border: 2px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .prompt-preview h3 {
            margin: 0 0 2rem 0;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--text-primary);
        }

        .prompt-preview h3 .material-icons {
            color: var(--accent-cyan);
            font-size: 1.5rem;
        }

        .prompt-text {
            background: var(--bg-primary);
            padding: 1.25rem;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            line-height: 1.6;
            word-break: break-word;
            min-height: 80px;
            color: var(--text-secondary);
            font-size: 1rem;
        }

        /* Navegação das abas */
        .tab-navigation {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }

        /* Botões */
        .btn {
            width: 48px;
            height: 48px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: var(--text-primary);
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }

        .btn-secondary {
            background: var(--bg-card);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--gradient-secondary);
            color: var(--text-primary);
            border-color: var(--secondary-purple);
        }

        .btn-success {
            background: var(--gradient-accent);
            color: var(--text-primary);
            box-shadow: var(--shadow-md);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn .material-icons {
            font-size: 1.25rem;
        }

        /* Alertas */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            grid-column: 1 / -1;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(6, 182, 212, 0.1) 100%);
            border: 1px solid #10b981;
            color: #10b981;
        }

        .alert-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(236, 72, 153, 0.1) 100%);
            border: 1px solid #ef4444;
            color: #ef4444;
        }

        .alert .material-icons {
            font-size: 1.25rem;
        }

        /* Responsivo */
        @media (max-width: 1024px) {
            .content-container { 
                padding: 1.5rem;
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .page-header h1 { 
                font-size: 2.5rem; 
            }
            
            .categories-grid { 
                gap: 1rem;
                padding-bottom: 0.5rem;
            }
            
            .category-section {
                min-width: 280px;
            }
            
            .tab-content { 
                padding: 2rem 1.5rem; 
            }
            
            .bottom-controls-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .custom-description {
                margin-top: 1rem;
            }
        }

        @media (max-width: 768px) {
            .header-content { 
                padding: 0 1rem; 
            }
            
            .content-container { 
                padding: 1rem;
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .page-header h1 { 
                font-size: 2rem; 
            }
            
            .page-header p { 
                font-size: 1rem; 
            }
            
            .tab-content { 
                padding: 1.5rem 1rem; 
            }
            
            .category-section { 
                padding: 1.5rem;
                min-width: 260px;
            }
            
            .categories-grid {
                gap: 0.75rem;
                padding-bottom: 0.5rem;
            }
            
            .subcategories-grid { 
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                max-height: 250px;
            }
            
            .tabs-nav { 
                overflow-x: scroll; 
            }
            
            .tab-button { 
                min-width: 120px; 
                font-size: 0.875rem; 
                padding: 1rem 0.75rem;
            }
            
            .tab-navigation {
                flex-direction: column;
                gap: 1rem;
                margin-top: 1rem;
            }
            
            .bottom-controls-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .custom-description {
                margin-top: 1rem;
            }
            
            .btn {
                width: 44px;
                height: 44px;
            }
            
            .btn .material-icons {
                font-size: 1.125rem;
            }
        }

        /* Animações */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tab-content.active {
            animation: fadeInUp 0.5s ease;
        }

        /* Scrollbar personalizada apenas para os blocos de categoria */
        .subcategories-grid::-webkit-scrollbar {
            width: 6px;
        }

        .subcategories-grid::-webkit-scrollbar-track {
            background: var(--bg-primary);
            border-radius: 3px;
        }

        .subcategories-grid::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: 3px;
        }

        .subcategories-grid::-webkit-scrollbar-thumb:hover {
            background: var(--gradient-secondary);
        }

        .categories-grid::-webkit-scrollbar {
            height: 8px;
        }

        .categories-grid::-webkit-scrollbar-track {
            background: var(--bg-primary);
            border-radius: 4px;
        }

        .categories-grid::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: 4px;
        }

        .categories-grid::-webkit-scrollbar-thumb:hover {
            background: var(--gradient-secondary);
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="material-icons">auto_fix_high</i>
                    <span>Gerador de Prompt - AprendAqui</span>
                </a>
                
                <div class="user-menu">
                    <div class="user-info">
                        <i class="material-icons">person</i>
                        <span><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?></span>
                    </div>
                    <a href="auth/logout.php" class="btn-logout">
                        <i class="material-icons">logout</i>
                        Sair
                    </a>
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
                    <button class="tab-button" data-tab="iluminacao">
                        <i class="material-icons">wb_sunny</i>
                        <span>Iluminação</span>
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
                    <button class="tab-button" data-tab="prompt">
                        <i class="material-icons">edit_note</i>
                        <span>Seu Prompt</span>
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
                         <div class="categories-grid">
                            <!-- NATUREZA -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">nature</i>
                                    </div>
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
                                    <div class="category-icon">
                                        <i class="material-icons">location_city</i>
                                    </div>
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
                                    <div class="category-icon">
                                        <i class="material-icons">home</i>
                                    </div>
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
                                    <div class="category-icon">
                                        <i class="material-icons">waves</i>
                                    </div>
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
                                    <div class="category-icon">
                                        <i class="material-icons">rocket_launch</i>
                                    </div>
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

                                         <!-- ABA 2: ILUMINAÇÃO -->
                     <div class="tab-content" id="tab-iluminacao">
                         <div class="tab-header">
                             <h2><i class="material-icons">wb_sunny</i> Iluminação</h2>
                             <p>Configure o tipo e intensidade da luz para sua criação</p>
                         </div>

                         <div class="categories-grid">
                            <!-- NATURAL -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">wb_sunny</i>
                                    </div>
                                    <h3 class="category-title">Natural</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="lighting" data-value="golden_hour">
                                        <div class="subcategory-title">Golden Hour</div>
                                        <div class="subcategory-desc">Luz dourada suave</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="blue_hour">
                                        <div class="subcategory-title">Blue Hour</div>
                                        <div class="subcategory-desc">Crepúsculo azulado</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="meio_dia">
                                        <div class="subcategory-title">Meio-dia</div>
                                        <div class="subcategory-desc">Sol a pino intenso</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="luar_noturno">
                                        <div class="subcategory-title">Luar Noturno</div>
                                        <div class="subcategory-desc">Clarão lunar</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="tempestade">
                                        <div class="subcategory-title">Tempestade</div>
                                        <div class="subcategory-desc">Raios dramáticos</div>
                                    </div>
                                </div>
                            </div>

                            <!-- ARTIFICIAL -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">lightbulb</i>
                                    </div>
                                    <h3 class="category-title">Artificial</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="lighting" data-value="neon_cyberpunk">
                                        <div class="subcategory-title">Neon Cyberpunk</div>
                                        <div class="subcategory-desc">Luzes coloridas vibrantes</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="led_frio">
                                        <div class="subcategory-title">LED Frio</div>
                                        <div class="subcategory-desc">Branco azulado</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="tungstênio_quente">
                                        <div class="subcategory-title">Tungstênio Quente</div>
                                        <div class="subcategory-desc">Amarelo aconchegante</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="strobo_festa">
                                        <div class="subcategory-title">Strobo Festa</div>
                                        <div class="subcategory-desc">Flashes intermitentes</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="laser_show">
                                        <div class="subcategory-title">Laser Show</div>
                                        <div class="subcategory-desc">Feixes coloridos</div>
                                    </div>
                                </div>
                            </div>

                            <!-- CINEMATOGRÁFICA -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">videocam</i>
                                    </div>
                                    <h3 class="category-title">Cinematográfica</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="lighting" data-value="three_point">
                                        <div class="subcategory-title">Three Point</div>
                                        <div class="subcategory-desc">Configuração clássica</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="high_key">
                                        <div class="subcategory-title">High Key</div>
                                        <div class="subcategory-desc">Iluminação clara</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="low_key">
                                        <div class="subcategory-title">Low Key</div>
                                        <div class="subcategory-desc">Sombras dramáticas</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="contra_luz">
                                        <div class="subcategory-title">Contra-luz</div>
                                        <div class="subcategory-desc">Silhueta rimada</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="chiaroscuro">
                                        <div class="subcategory-title">Chiaroscuro</div>
                                        <div class="subcategory-desc">Contraste extremo</div>
                                    </div>
                                </div>
                            </div>

                            <!-- AMBIENTE -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">mood</i>
                                    </div>
                                    <h3 class="category-title">Ambiente</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="lighting" data-value="fogueira_acampamento">
                                        <div class="subcategory-title">Fogueira</div>
                                        <div class="subcategory-desc">Chamas dançantes</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="velas_romanticas">
                                        <div class="subcategory-title">Velas Românticas</div>
                                        <div class="subcategory-desc">Luz íntima tremulante</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="lanterna_terror">
                                        <div class="subcategory-title">Lanterna Terror</div>
                                        <div class="subcategory-desc">Sombras assombradas</div>
                                    </div>
                                    <div class="subcategory-card" data-type="lighting" data-value="aurora_magica">
                                        <div class="subcategory-title">Aurora Mágica</div>
                                        <div class="subcategory-desc">Luzes fantasiosas</div>
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
                                     Descrição Personalizada da Iluminação
                                 </label>
                                 <textarea 
                                     name="custom_lighting" 
                                     placeholder="Descreva um tipo de iluminação específico que não está nas opções abaixo..."
                                     rows="3"></textarea>
                             </div>

                             <!-- Coluna 2: Controles de navegação -->
                             <div class="tab-navigation">
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

                    <!-- ABA 3: AVATAR/PERSONAGEM -->
                    <div class="tab-content" id="tab-avatar">
                        <div class="tab-header">
                            <h2><i class="material-icons">groups</i> Avatar e Personagem</h2>
                            <p>Defina as características físicas e estilo do personagem</p>
                        </div>

                        <div class="categories-grid">
                            <!-- GÊNERO -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">people</i>
                                    </div>
                                    <h3 class="category-title">Gênero</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="character" data-value="mulher_jovem">
                                        <div class="subcategory-title">Mulher Jovem</div>
                                        <div class="subcategory-desc">18-30 anos</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="homem_jovem">
                                        <div class="subcategory-title">Homem Jovem</div>
                                        <div class="subcategory-desc">18-30 anos</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="mulher_madura">
                                        <div class="subcategory-title">Mulher Madura</div>
                                        <div class="subcategory-desc">30-50 anos</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="homem_maduro">
                                        <div class="subcategory-title">Homem Maduro</div>
                                        <div class="subcategory-desc">30-50 anos</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="crianca">
                                        <div class="subcategory-title">Criança</div>
                                        <div class="subcategory-desc">5-12 anos</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="idoso">
                                        <div class="subcategory-title">Idoso</div>
                                        <div class="subcategory-desc">60+ anos</div>
                                    </div>
                                </div>
                            </div>

                            <!-- ETNIAS -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">public</i>
                                    </div>
                                    <h3 class="category-title">Etnias</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="character" data-value="brasileiro_moreno">
                                        <div class="subcategory-title">Brasileiro</div>
                                        <div class="subcategory-desc">Pele morena</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="caucasiano">
                                        <div class="subcategory-title">Caucasiano</div>
                                        <div class="subcategory-desc">Pele clara</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="afrodescendente">
                                        <div class="subcategory-title">Afrodescendente</div>
                                        <div class="subcategory-desc">Pele escura</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="asiatico">
                                        <div class="subcategory-title">Asiático</div>
                                        <div class="subcategory-desc">Traços orientais</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="latino">
                                        <div class="subcategory-title">Latino</div>
                                        <div class="subcategory-desc">América Latina</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="indigena">
                                        <div class="subcategory-title">Indígena</div>
                                        <div class="subcategory-desc">Nativo americano</div>
                                    </div>
                                </div>
                            </div>

                            <!-- TIPOS FÍSICOS -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">fitness_center</i>
                                    </div>
                                    <h3 class="category-title">Físico</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="character" data-value="atlético_definido">
                                        <div class="subcategory-title">Atlético</div>
                                        <div class="subcategory-desc">Corpo definido</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="magro_esbelto">
                                        <div class="subcategory-title">Esbelto</div>
                                        <div class="subcategory-desc">Corpo magro</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="curvilíneo">
                                        <div class="subcategory-title">Curvilíneo</div>
                                        <div class="subcategory-desc">Formas arredondadas</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="forte_robusto">
                                        <div class="subcategory-title">Robusto</div>
                                        <div class="subcategory-desc">Porte forte</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="musculoso_bodybuilder">
                                        <div class="subcategory-title">Musculoso</div>
                                        <div class="subcategory-desc">Muito definido</div>
                                    </div>
                                </div>
                            </div>

                            <!-- PROFISSÕES -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">work</i>
                                    </div>
                                    <h3 class="category-title">Profissões</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="character" data-value="executivo_negocios">
                                        <div class="subcategory-title">Executivo</div>
                                        <div class="subcategory-desc">Terno e gravata</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="medico_jaleco">
                                        <div class="subcategory-title">Médico</div>
                                        <div class="subcategory-desc">Jaleco branco</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="artista_boemia">
                                        <div class="subcategory-title">Artista</div>
                                        <div class="subcategory-desc">Estilo boêmio</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="chef_cozinha">
                                        <div class="subcategory-title">Chef</div>
                                        <div class="subcategory-desc">Uniforme culinário</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="atleta_esportivo">
                                        <div class="subcategory-title">Atleta</div>
                                        <div class="subcategory-desc">Roupa esportiva</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="estudante_casual">
                                        <div class="subcategory-title">Estudante</div>
                                        <div class="subcategory-desc">Roupa casual</div>
                                    </div>
                                </div>
                            </div>

                            <!-- FANTASIA -->
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">auto_fix_high</i>
                                    </div>
                                    <h3 class="category-title">Fantasia</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="character" data-value="elfo_magico">
                                        <div class="subcategory-title">Elfo Mágico</div>
                                        <div class="subcategory-desc">Orelhas pontudas</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="vampiro_gotico">
                                        <div class="subcategory-title">Vampiro</div>
                                        <div class="subcategory-desc">Estilo gótico</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="anjo_celestial">
                                        <div class="subcategory-title">Anjo</div>
                                        <div class="subcategory-desc">Asas luminosas</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="demonio_sombrio">
                                        <div class="subcategory-title">Demônio</div>
                                        <div class="subcategory-desc">Chifres e poder</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="fada_natureza">
                                        <div class="subcategory-title">Fada</div>
                                        <div class="subcategory-desc">Asas delicadas</div>
                                    </div>
                                    <div class="subcategory-card" data-type="character" data-value="guerreiro_medieval">
                                        <div class="subcategory-title">Guerreiro</div>
                                        <div class="subcategory-desc">Armadura medieval</div>
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
                                     placeholder="Descreva um personagem específico que não está nas opções abaixo..."
                                     rows="3"></textarea>
                             </div>

                             <!-- Coluna 2: Controles de navegação -->
                             <div class="tab-navigation">
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

                    <!-- ABA 4: CÂMERA -->
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

                    <!-- ABA 5: VOZ -->
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

                    <!-- ABA 6: SEU PROMPT -->
                    <div class="tab-content" id="tab-prompt">
                        <div class="tab-header">
                            <h2><i class="material-icons">edit_note</i> Seu Prompt Final</h2>
                            <p>Finalize e personalize seu prompt antes de gerar</p>
                        </div>

                        <div class="form-section">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="material-icons">title</i>
                                    Título do Prompt
                                </label>
                                <input 
                                    type="text" 
                                    name="title" 
                                    class="form-input"
                                    placeholder="Digite um título para seu prompt..."
                                    required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="material-icons">description</i>
                                    Seu Prompt Original
                                </label>
                                <textarea 
                                    name="original_prompt" 
                                    class="form-textarea"
                                    placeholder="Digite seu prompt base aqui..."
                                    required></textarea>
                            </div>

                            <!-- Preview do Prompt Gerado -->
                            <div class="prompt-preview">
                                <h3>
                                    <i class="material-icons">visibility</i>
                                    Preview do Prompt Aprimorado
                                </h3>
                                <div id="enhanced-prompt-preview" class="prompt-text">
                                    O prompt aprimorado aparecerá aqui conforme você faz suas seleções...
                                </div>
                            </div>

                            <!-- Prompt Final -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="material-icons">auto_fix_high</i>
                                    Prompt Final Aprimorado
                                </label>
                                <textarea 
                                    name="enhanced_prompt" 
                                    id="enhanced-prompt" 
                                    class="form-textarea"
                                    placeholder="O prompt aprimorado aparecerá aqui automaticamente..."
                                    readonly></textarea>
                            </div>
                        </div>

                        <div class="tab-navigation">
                            <button type="button" class="btn btn-secondary" onclick="prevTab()">
                                <i class="material-icons">arrow_back</i> Anterior
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="material-icons">save</i> Salvar Prompt
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        class ModernPromptGenerator {
            constructor() {
                this.currentTab = 0;
                this.tabs = ['ambiente', 'iluminacao', 'avatar', 'camera', 'voz', 'prompt'];
                this.selections = {
                    environment: null,
                    lighting: null,
                    character: null,
                    camera: null,
                    voice: null
                };
                this.customDescriptions = {
                    environment: '',
                    lighting: '',
                    character: '',
                    camera: '',
                    voice: ''
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
            }

            selectOption(type, value, element) {
                // Remove previous selection
                const container = element.closest('.category-section');
                container.querySelectorAll('.subcategory-card').forEach(card => {
                    card.classList.remove('selected');
                });

                // Add selection to clicked element
                element.classList.add('selected');

                // Store selection
                this.selections[type] = value;

                // Update hidden input
                const input = document.getElementById(`selected_${type}`);
                if (input) {
                    input.value = value;
                }

                // Update prompt preview
                this.updatePromptPreview();
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
                window.promptGenerator.showTab('prompt');
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            window.promptGenerator = new ModernPromptGenerator();
        });
    </script>
</body>
</html>