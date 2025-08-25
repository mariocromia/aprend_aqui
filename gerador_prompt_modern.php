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
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: #f8fafc;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(12px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--text-muted, #64748b);
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: rgba(59, 130, 246, 0.7);
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.05));
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2),
                        0 4px 16px rgba(59, 130, 246, 0.25);
            transform: translateY(-2px);
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
            padding-top: 2rem;
            margin-top: 2rem;
            position: relative;
        }
        
        .form-actions::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(59, 130, 246, 0.3), 
                rgba(139, 92, 246, 0.3), 
                transparent);
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
        
        /* Layout específico para aba avatar - 2 colunas e 3 linhas para cards */
        #tab-avatar .type-cards-grid {
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(3, 1fr);
            gap: 1rem;
            max-width: 100%;
        }
        
        /* Layout de 3 blocos para aba avatar */
        .three-blocks-container {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
            padding: 0 1rem;
        }
        
        .bloco-esquerdo, .bloco-meio, .bloco-direito {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 16px;
            overflow: hidden;
            min-height: 600px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), 
                        0 0 0 1px rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        .bloco-esquerdo:hover, .bloco-meio:hover, .bloco-direito:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4),
                        0 0 0 1px rgba(59, 130, 246, 0.3);
            border-color: rgba(59, 130, 246, 0.4);
        }
        
        .bloco-esquerdo::before, .bloco-meio::before, .bloco-direito::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, 
                #3b82f6 0%, 
                #8b5cf6 33%, 
                #06b6d4 66%, 
                #10b981 100%);
            border-radius: 16px 16px 0 0;
        }
        
        .bloco-header {
            padding: 1.5rem 1.5rem 1rem;
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
        }
        
        .bloco-header h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #e2e8f0;
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .bloco-header h3 i {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.5rem;
        }
        
        .bloco-header p {
            color: #94a3b8;
            margin: 0;
            font-size: 0.9rem;
            font-weight: 400;
        }
        
        .bloco-content {
            padding: 1.5rem;
            height: calc(100% - 90px);
            overflow-y: auto;
        }
        
        /* Scrollbar personalizada */
        .bloco-content::-webkit-scrollbar {
            width: 6px;
        }
        
        .bloco-content::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .bloco-content::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.3);
            border-radius: 3px;
        }
        
        .bloco-content::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.5);
        }
        
        /* Responsividade para 3 blocos */
        @media (max-width: 1200px) {
            .three-blocks-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .bloco-esquerdo, .bloco-meio, .bloco-direito {
                min-height: auto;
            }
        }
        
        /* Lista de avatares modernizada */
        
        
        .no-avatars-placeholder {
            text-align: center;
            padding: 3rem 2rem;
            color: #64748b;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            border: 1px dashed rgba(255, 255, 255, 0.1);
        }
        
        .no-avatars-placeholder i {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            opacity: 0.4;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .no-avatars-placeholder p:first-of-type {
            font-size: 1.1rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 0.5rem;
        }
        
        .no-avatars-placeholder p:last-of-type {
            font-size: 0.9rem;
            color: #64748b;
            line-height: 1.5;
        }
        
        .type-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            padding: 1.5rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(12px);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }
        
        .type-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255, 255, 255, 0.1), 
                transparent);
            transition: left 0.6s ease;
        }
        
        .type-card:hover::before {
            left: 100%;
        }
        
        .type-card:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(139, 92, 246, 0.1));
            border-color: rgba(59, 130, 246, 0.4);
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 12px 32px rgba(59, 130, 246, 0.3);
        }
        
        .type-card.selected {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(139, 92, 246, 0.2));
            border-color: rgba(59, 130, 246, 0.6);
            box-shadow: 0 0 30px rgba(59, 130, 246, 0.4);
            transform: translateY(-4px) scale(1.05);
        }
        
        .type-card.selected::after {
            content: '✓';
            position: absolute;
            top: 12px;
            right: 12px;
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
        }
        
        .type-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        }
        
        .type-card:hover .type-icon {
            transform: scale(1.15) rotate(8deg);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.5);
        }
        
        .type-icon .material-icons {
            font-size: 1.8rem;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .type-card h5 {
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #f1f5f9;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .type-card p {
            margin: 0;
            font-size: 0.85rem;
            color: #cbd5e1;
            line-height: 1.4;
            font-weight: 400;
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

        /* Estilos para a nova implementação de câmera baseada no camera.html */
        #camera-app-container {
            display: flex;
            flex-direction: row;
            width: 100%;
            height: 600px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        #camera-canvas-container {
            flex-grow: 1;
            position: relative;
            background: #2a2a2a;
        }

        #camera-canvas {
            background-color: #2a2a2a;
            border-radius: 10px;
            width: 100%;
            height: 100%;
        }

        .camera-controls-panel {
            width: 320px;
            background-color: #2a2a2a;
            padding: 15px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 12px;
            overflow-y: auto;
            border-left: 1px solid rgba(255, 255, 255, 0.1);
        }

        .camera-controls-panel h3 {
            color: #ffffff;
            margin: 0 0 15px 0;
            font-size: 18px;
            font-weight: 600;
        }

        .camera-controls-panel h4 {
            color: #ffffff;
            margin: 15px 0 5px 0;
            font-size: 14px;
            font-weight: 600;
        }

        .camera-info-text {
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.4;
            color: #cbd5e1;
            background: rgba(59, 130, 246, 0.1);
            padding: 12px;
            border-radius: 8px;
            border-left: 3px solid #3b82f6;
        }

        .camera-input-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 15px;
        }

        .camera-input-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        
        .camera-input-row label {
            flex-grow: 1;
            color: #cbd5e1;
            font-size: 14px;
        }

        .camera-input-row input {
            width: 80px;
            padding: 6px 8px;
            background-color: #1a1a1a;
            color: #fff;
            border: 1px solid #555;
            border-radius: 6px;
            font-size: 14px;
        }

        .camera-input-row input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .camera-button-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .camera-button-group button {
            padding: 8px 12px;
            background-color: #4a4a4a;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 13px;
        }

        .camera-button-group button:hover {
            background-color: #6a6a6a;
            transform: translateY(-1px);
        }

        .camera-button-group button:active {
            transform: translateY(0);
        }
        
        .camera-section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #4a4a4a;
            padding-bottom: 5px;
            color: #ffffff;
        }

        .camera-keyframe-list {
            height: 180px;
            overflow-y: auto;
            border: 1px solid #444;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
            background: #1a1a1a;
        }

        .camera-keyframe-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 8px;
            border-bottom: 1px solid #3a3a3a;
            font-size: 12px;
            color: #cbd5e1;
        }
        
        .camera-keyframe-item:last-child {
            border-bottom: none;
        }

        .camera-keyframe-item .coords {
            display: flex;
            gap: 8px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            font-weight: 600;
        }
        
        #camera-output-area {
            width: 100%;
            height: 250px;
            background-color: #111;
            border: 1px solid #4a4a4a;
            border-radius: 8px;
            padding: 12px;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
            white-space: pre-wrap;
            overflow-y: auto;
            font-size: 11px;
            color: #cbd5e1;
            resize: vertical;
            margin-bottom: 15px;
        }

        #camera-output-area:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        #camera-generate {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
            transition: all 0.2s;
        }

        #camera-generate:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        #camera-add-to-prompt {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            transition: all 0.2s;
        }

        #camera-add-to-prompt:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }

        .camera-keyframe-list:empty::before {
            content: "Adicione pontos para começar...";
            display: block;
            text-align: center;
            color: #6b7280;
            font-style: italic;
            padding: 20px;
        }

        /* ===== ESTILOS MODERNOS PARA CARDS DE AVATARES ===== */
        
        /* Header dos Avatares */
        .avatars-header-main {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        /* Toolbar de controles no bloco Meus Avatares */
        .avatars-controls-toolbar {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin: 0.75rem 0;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .search-create-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .avatar-search-group {
            flex: 1;
            min-width: 200px;
        }

        .filters-row {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .filter-group-inline {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-label-compact {
            color: #94a3b8;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
        }

        .search-input-compact {
            width: 100%;
            padding: 6px 12px 6px 36px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 6px;
            color: white;
            font-size: 13px;
            transition: all 0.2s ease;
        }

        .search-input-compact::placeholder {
            color: #94a3b8;
        }

        .search-input-compact:focus {
            outline: none;
            border-color: #3b82f6;
            background: rgba(255, 255, 255, 0.12);
        }

        .avatar-filters-compact {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-select-compact {
            padding: 6px 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 6px;
            color: white;
            font-size: 12px;
            min-width: 80px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-select-compact:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .filter-select-compact option {
            background: #1a1a1a;
            color: white;
        }

        .btn-create-compact {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 6px 10px;
            background: #3b82f6;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-create-compact:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .btn-create-compact i {
            font-size: 16px;
        }

        .avatars-title-section {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .avatars-title-section h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .avatars-count {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* Controles dos Avatares */
        .avatars-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }


        /* Ações em Massa */
        .bulk-actions {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 0.25rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .bulk-btn {
            background: transparent;
            border: none;
            padding: 0.5rem;
            border-radius: 6px;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bulk-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        .bulk-btn.danger:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        .bulk-btn i {
            font-size: 16px;
        }

        /* Menu de Ações */
        .avatars-menu {
            position: relative;
        }

        .menu-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.5rem;
            border-radius: 8px;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .menu-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .menu-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.8);
            min-width: 180px;
            z-index: 1000;
            display: none;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
        }

        .menu-dropdown.show {
            display: block;
        }

        .menu-item {
            width: 100%;
            background: transparent;
            border: none;
            padding: 0.75rem 1rem;
            color: #cbd5e1;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }

        .menu-divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 0.5rem 0;
        }

        /* Container de Avatares */
        .avatars-container {
            min-height: 400px;
            position: relative;
            overflow: visible;
        }

        /* Estado Vazio */
        .avatars-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
            text-align: center;
            min-height: 300px;
        }

        .empty-icon {
            background: rgba(59, 130, 246, 0.1);
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .empty-icon i {
            font-size: 2.5rem;
            color: #3b82f6;
        }

        .avatars-empty-state h4 {
            color: #ffffff;
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
        }

        .avatars-empty-state p {
            color: #94a3b8;
            font-size: 0.875rem;
            margin: 0 0 2rem 0;
            max-width: 280px;
        }

        .btn-create-first {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: #ffffff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-create-first:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Botões de ação */
        .action-btn {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        .action-btn.primary {
            background: #3b82f6;
            color: #ffffff;
        }

        .action-btn.primary:hover {
            background: #2563eb;
        }

        .action-btn i {
            font-size: 16px;
        }

        .favorite-btn {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .favorite-btn:hover {
            color: #fbbf24;
            background: rgba(251, 191, 36, 0.1);
        }

        .favorite-btn.active {
            color: #fbbf24;
        }

        /* Lista de Avatares */
        .avatars-list-view {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 0.5rem;
            overflow: visible;
        }

        .avatar-button {
            background: #19203a;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            min-height: 48px;
            z-index: 1;
        }

        .avatar-button.menu-open {
            z-index: 10000;
        }

        .avatar-button:hover {
            background: #242c4d;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .avatar-button.selected {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.05);
        }

        .avatar-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #667eea 0%, #a855f7 50%, #ec4899 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            position: relative;
            box-shadow: 0 1px 4px rgba(168, 85, 247, 0.3);
        }

        .avatar-icon i {
            font-size: 18px;
            color: white;
        }

        .avatar-name {
            font-size: 14px;
            font-weight: 600;
            color: white;
            flex: 1;
        }

        .avatar-type {
            font-size: 10px;
            color: #94a3b8;
            font-weight: 400;
            margin-left: auto;
            margin-right: 8px;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
            align-items: center;
        }

        /* Menu do Card de Avatar */
        .avatar-card-menu {
            position: relative;
        }

        .menu-trigger {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
            color: #94a3b8;
        }

        .menu-trigger:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .menu-trigger i {
            font-size: 16px;
        }

        .card-menu-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: #0f172a !important;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.9);
            z-index: 9999;
            display: none;
            padding: 0.5rem 0;
            margin-top: 0.25rem;
            min-width: 120px;
        }

        .card-menu-dropdown.show {
            display: block;
        }

        .card-menu-dropdown .menu-item {
            width: 100%;
            background: transparent;
            border: none;
            padding: 0.5rem 0.75rem;
            color: #cbd5e1;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 12px;
        }

        .card-menu-dropdown .menu-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .card-menu-dropdown .menu-item.danger:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .card-menu-dropdown .menu-item i {
            font-size: 14px;
        }

        .card-menu-dropdown .menu-divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 0.25rem 0;
        }

        .star-button {
            background: none;
            border: none;
            cursor: pointer;
            transition: transform 0.2s ease;
            padding: 2px;
        }

        .star-button:hover {
            transform: scale(1.2) rotate(20deg);
        }

        .star-button svg {
            width: 16px;
            height: 16px;
            fill: #fbbf24;
            filter: drop-shadow(0 1px 2px rgba(251, 191, 36, 0.3));
        }


        .add-button {
            width: 24px;
            height: 24px;
            background: #3b82f6;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: 0 1px 4px rgba(59, 130, 246, 0.4);
        }

        .add-button:hover {
            background: #2563eb;
            transform: scale(1.05);
        }

        .add-button svg {
            width: 14px;
            height: 14px;
            fill: white;
        }

        /* Responsivo para avatar buttons */
        @media (max-width: 480px) {
            .avatar-button {
                padding: 6px 8px;
                gap: 6px;
                min-height: 42px;
            }

            .avatar-name {
                font-size: 12px;
            }

            .avatar-type {
                font-size: 9px;
            }

            .avatar-list-checkbox {
                width: 14px;
                height: 14px;
            }

            .avatar-list-checkbox i {
                font-size: 10px;
            }

            .avatar-icon {
                width: 28px;
                height: 28px;
            }

            .avatar-icon i {
                font-size: 16px;
            }

            .add-button {
                width: 20px;
                height: 20px;
            }

            .add-button svg {
                width: 12px;
                height: 12px;
            }

            .star-button svg {
                width: 14px;
                height: 14px;
            }

            .menu-dots {
                gap: 2px;
            }

            .menu-dot {
                width: 3px;
                height: 3px;
            }

            .action-buttons {
                gap: 3px;
            }
        }

        .avatar-list-checkbox {
            width: 16px;
            height: 16px;
            background: rgba(0, 0, 0, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .avatar-list-checkbox i {
            font-size: 12px;
            color: #ffffff;
            display: none;
        }

        .avatar-button.selected .avatar-list-checkbox {
            background: #3b82f6;
            border-color: #3b82f6;
        }

        .avatar-button.selected .avatar-list-checkbox i {
            display: block;
        }

        .avatar-list-item.bulk-mode .avatar-list-checkbox {
            display: flex;
        }

        .avatar-list-item.selected .avatar-list-checkbox {
            background: #3b82f6;
            border-color: #3b82f6;
        }

        .avatar-list-checkbox i {
            font-size: 12px;
            color: #ffffff;
            display: none;
        }

        .avatar-list-item.selected .avatar-list-checkbox i {
            display: block;
        }

        .avatar-list-info {
            flex: 1;
            min-width: 0;
        }

        .avatar-list-title {
            color: #ffffff;
            font-weight: 600;
            margin: 0;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .avatar-list-meta {
            color: #64748b;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.25rem;
        }

        .avatar-list-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        /* Dropdown no item da lista */
        .avatar-list-item .avatar-card-menu {
            position: relative;
        }

        .avatar-list-item .card-menu-dropdown {
            position: absolute;
            bottom: 100%;
            right: 0;
            background: #0f172a !important;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 6px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.9);
            min-width: 120px;
            z-index: 1000;
            display: none;
            padding: 0.25rem 0;
            margin-bottom: 0.5rem;
        }

        .avatar-list-item .card-menu-dropdown.show {
            display: block;
        }

        .avatar-list-item .card-menu-item {
            width: 100%;
            background: transparent;
            border: none;
            padding: 0.5rem 0.75rem;
            color: #cbd5e1;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            text-align: left;
        }

        .avatar-list-item .card-menu-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }

        .avatar-list-item .card-menu-item.danger:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }

        /* Loading */
        .avatars-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
            text-align: center;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(59, 130, 246, 0.1);
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .avatars-loading p {
            color: #94a3b8;
            font-size: 0.875rem;
            margin: 0;
        }

        /* Menu de ações do card */
        .avatar-card-menu {
            position: relative;
        }

        .card-menu-btn {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-menu-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        .card-menu-btn i {
            font-size: 16px;
        }

        .card-menu-dropdown {
            position: absolute;
            bottom: 100%;
            right: 0;
            background: #0f172a !important;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.9);
            min-width: 140px;
            z-index: 1000;
            display: none;
            padding: 0.5rem 0;
            margin-bottom: 0.5rem;
        }

        .card-menu-dropdown.show {
            display: block;
        }

        .card-menu-item {
            width: 100%;
            background: transparent;
            border: none;
            padding: 0.75rem 1rem;
            color: #cbd5e1;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
            text-align: left;
        }

        .card-menu-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }

        .card-menu-item.danger:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }

        /* Modal de ações */
        .avatar-action-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .avatar-action-modal.show {
            display: flex;
            opacity: 1;
        }

        .modal-content-avatar {
            background: #1e293b;
            border-radius: 12px;
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            margin: 0 1rem;
            transform: scale(0.9);
            transition: transform 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .avatar-action-modal.show .modal-content-avatar {
            transform: scale(1);
        }

        .modal-header-avatar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-title-avatar {
            color: #ffffff;
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-close-avatar {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.2s;
            font-size: 1.5rem;
            line-height: 1;
        }

        .modal-close-avatar:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        .modal-body-avatar {
            margin-bottom: 2rem;
        }

        .avatar-info-modal {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .avatar-name-modal {
            color: #ffffff;
            font-size: 1rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
        }

        .avatar-type-modal {
            color: #3b82f6;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .avatar-description-modal {
            color: #94a3b8;
            font-size: 0.875rem;
            line-height: 1.4;
            margin: 0;
        }

        .modal-actions-avatar {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .modal-btn-primary {
            background: #3b82f6;
            color: #ffffff;
        }

        .modal-btn-primary:hover {
            background: #2563eb;
        }

        .modal-btn-danger {
            background: #ef4444;
            color: #ffffff;
        }

        .modal-btn-danger:hover {
            background: #dc2626;
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .avatars-header-main {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }

            .avatars-controls {
                justify-content: space-between;
            }

            .search-create-row {
                flex-direction: column;
                gap: 0.5rem;
                align-items: stretch;
            }

            .avatar-search-group {
                min-width: auto;
            }

            .filters-row {
                flex-direction: column;
                gap: 0.5rem;
                align-items: stretch;
            }

            .filter-group-inline {
                justify-content: space-between;
            }

            .filter-select-compact {
                flex: 1;
                min-width: auto;
                margin-left: 0.5rem;
            }

            .avatars-grid-view {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 0.75rem;
            }

            .avatar-card-header {
                padding: 0.75rem;
            }

            .avatar-card-footer {
                padding: 0.75rem;
            }

            .modal-content-avatar {
                margin: 0 0.5rem;
                padding: 1.5rem;
            }

            .modal-actions-avatar {
                flex-direction: column;
            }

            .modal-btn {
                justify-content: center;
            }
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
                    <button class="tab-button" data-tab="camera2">
                        <i class="material-icons">videocam</i>
                        <span>Câmera 2</span>
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
                        </div>

                        
                        <!-- Três blocos lado a lado -->
                        <div class="three-blocks-container">
                            <!-- Bloco esquerdo - Tipos de Avatar -->
                            <div class="bloco-esquerdo">
                                <div class="bloco-header">
                                    <h3><i class="material-icons">category</i> Tipos de Avatar</h3>
                                    <p>Selecione o tipo desejado</p>
                                </div>
                                <div class="bloco-content">
                                                                        <!-- Seleção de Tipo - Cards -->
                                        <div class="avatar-type-selection" id="avatar-type-selection">
                                            <div class="type-cards-grid">
                                                <div class="type-card" data-type="humano">
                                                    <div class="type-icon">
                                                        <i class="material-icons">face</i>
                                                    </div>
                                                    <h5>Humano</h5>
                                                    <p>Personagens humanos</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="animal">
                                                    <div class="type-icon">
                                                        <i class="material-icons">pets</i>
                                                    </div>
                                                    <h5>Animal</h5>
                                                    <p>Personagens animais</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="fantastico">
                                                    <div class="type-icon">
                                                        <i class="material-icons">auto_awesome</i>
                                                    </div>
                                                    <h5>Fantástico</h5>
                                                    <p>Criaturas mágicas</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="extraterrestres">
                                                    <div class="type-icon">
                                                        <i class="material-icons">rocket_launch</i>
                                                    </div>
                                                    <h5>Extraterrestres</h5>
                                                    <p>Aliens e seres espaciais</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="robos">
                                                    <div class="type-icon">
                                                        <i class="material-icons">smart_toy</i>
                                                    </div>
                                                    <h5>Robôs</h5>
                                                    <p>Máquinas e androides</p>
                                                </div>
                                                
                                                <div class="type-card" data-type="outros">
                                                    <div class="type-icon">
                                                        <i class="material-icons">more_horiz</i>
                                                    </div>
                                                    <h5>Outros</h5>
                                                    <p>Outros tipos especiais</p>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            </div>
                            
                            <!-- Bloco do meio - Formulário do Avatar -->
                            <div class="bloco-meio">
                                <div class="bloco-header">
                                    <h3><i class="material-icons">edit</i> Formulário do Avatar</h3>
                                    <p>Preencha os campos específicos</p>
                                </div>
                                <div class="bloco-content">
                                    <!-- Formulário Humano -->
                                    <div class="avatar-form" id="form-humano" style="display: none;">
                                        <h4><i class="material-icons">face</i> Formulário Humano</h4>
                                        <div class="form-group">
                                            <label>Nome do Avatar:</label>
                                            <input type="text" name="nome_humano" placeholder="Ex: João, Maria, Alex..." required>
                                        </div>
                                        
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label>Gênero:</label>
                                                <select name="genero_humano">
                                                    <option value="">Selecione...</option>
                                                    <option value="masculino">Masculino</option>
                                                    <option value="feminino">Feminino</option>
                                                    <option value="nao_binario">Não-binário</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Idade:</label>
                                                <input type="number" name="idade_humano" placeholder="Ex: 25" min="1" max="120">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label>Peso (em kg):</label>
                                                <input type="number" name="peso_humano" placeholder="Ex: 70" min="20" max="300" step="0.1">
                                            </div>
                                            <div class="form-group">
                                                <label>Altura (em metros):</label>
                                                <input type="number" name="altura_humano" placeholder="Ex: 1.75" min="0.5" max="2.5" step="0.01">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label>Cor da Pele:</label>
                                                <select name="cor_pele_humano">
                                                    <option value="">Selecione...</option>
                                                    <option value="branca">Branca</option>
                                                    <option value="morena_clara">Morena Clara</option>
                                                    <option value="morena">Morena</option>
                                                    <option value="morena_escura">Morena Escura</option>
                                                    <option value="negra">Negra</option>
                                                    <option value="amarela">Amarela</option>
                                                    <option value="vermelha">Vermelha</option>
                                                    <option value="oliva">Oliva</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Cor do Cabelo:</label>
                                                <select name="cor_cabelo_humano">
                                                    <option value="">Selecione...</option>
                                                    <option value="preto">Preto</option>
                                                    <option value="castanho_escuro">Castanho Escuro</option>
                                                    <option value="castanho">Castanho</option>
                                                    <option value="castanho_claro">Castanho Claro</option>
                                                    <option value="loiro_escuro">Loiro Escuro</option>
                                                    <option value="loiro">Loiro</option>
                                                    <option value="loiro_claro">Loiro Claro</option>
                                                    <option value="ruivo">Ruivo</option>
                                                    <option value="grisalho">Grisalho</option>
                                                    <option value="branco">Branco</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label>Altura do Cabelo:</label>
                                                <select name="altura_cabelo_humano">
                                                    <option value="">Selecione...</option>
                                                    <option value="careca">Careca</option>
                                                    <option value="muito_curto">Muito Curto</option>
                                                    <option value="curto">Curto</option>
                                                    <option value="medio">Médio</option>
                                                    <option value="longo">Longo</option>
                                                    <option value="muito_longo">Muito Longo</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Corte do Cabelo:</label>
                                                <select name="corte_cabelo_humano">
                                                    <option value="">Selecione...</option>
                                                    <option value="liso">Liso</option>
                                                    <option value="ondulado">Ondulado</option>
                                                    <option value="crespo">Crespo</option>
                                                    <option value="cacheado">Cacheado</option>
                                                    <option value="afro">Afro</option>
                                                    <option value="coque">Coque</option>
                                                    <option value="tranca">Trança</option>
                                                    <option value="rabo_cavalo">Rabo de Cavalo</option>
                                                    <option value="moicano">Moicano</option>
                                                    <option value="undercut">Undercut</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Cor dos Olhos:</label>
                                            <select name="cor_olhos_humano">
                                                <option value="">Selecione...</option>
                                                <option value="castanhos_escuros">Castanhos Escuros</option>
                                                <option value="castanhos">Castanhos</option>
                                                <option value="castanhos_claros">Castanhos Claros</option>
                                                <option value="azuis_escuros">Azuis Escuros</option>
                                                <option value="azuis">Azuis</option>
                                                <option value="azuis_claros">Azuis Claros</option>
                                                <option value="verdes">Verdes</option>
                                                <option value="verdes_azulados">Verdes Azulados</option>
                                                <option value="cinza">Cinza</option>
                                                <option value="pretos">Pretos</option>
                                                <option value="mel">Mel</option>
                                                <option value="heterocromia">Heterocromia</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Descrição Complementar:</label>
                                            <textarea name="descricao_complementar_humano" placeholder="Descreva outras características como tatuagens, cicatrizes, marcas de nascença, piercings, etc..." rows="3"></textarea>
                                        </div>
                                        <div class="form-actions">
                                            <button type="button" class="btn-secondary" onclick="clearAvatarSelection()">
                                                <i class="material-icons">clear</i>
                                                Limpar
                                            </button>
                                            <button type="button" class="btn-primary" onclick="createAvatar('humano')">
                                                <i class="material-icons">save</i>
                                                Criar Avatar
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Formulário Animal -->
                                    <div class="avatar-form" id="form-animal" style="display: none;">
                                        <h4><i class="material-icons">pets</i> Formulário Animal</h4>
                                        <div class="form-group">
                                            <label>Nome do Avatar:</label>
                                            <input type="text" name="nome_animal" placeholder="Ex: Rex, Fluffy, Lobo..." required>
                                        </div>
                                        <div class="form-group">
                                            <label>Espécie:</label>
                                            <select name="especie_animal">
                                                <option value="">Selecione...</option>
                                                <option value="cachorro">Cachorro</option>
                                                <option value="gato">Gato</option>
                                                <option value="cavalo">Cavalo</option>
                                                <option value="urso">Urso</option>
                                                <option value="lobo">Lobo</option>
                                                <option value="tigre">Tigre</option>
                                                <option value="leao">Leão</option>
                                                <option value="aguia">Águia</option>
                                                <option value="dragao">Dragão</option>
                                                <option value="unicornio">Unicórnio</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Cor da Pelagem:</label>
                                            <input type="text" name="pelagem_animal" placeholder="Ex: Marrom com manchas brancas">
                                        </div>
                                        <div class="form-group">
                                            <label>Características Especiais:</label>
                                            <textarea name="caracteristicas_animal" placeholder="Ex: Asas, chifres, cauda longa..." rows="2"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label>Comportamento:</label>
                                            <select name="comportamento_animal">
                                                <option value="">Selecione...</option>
                                                <option value="selvagem">Selvagem</option>
                                                <option value="domestico">Doméstico</option>
                                                <option value="misterioso">Misterioso</option>
                                                <option value="amigavel">Amigável</option>
                                            </select>
                                        </div>
                                        <div class="form-actions">
                                            <button type="button" class="btn-secondary" onclick="clearAvatarSelection()">
                                                <i class="material-icons">clear</i>
                                                Limpar
                                            </button>
                                            <button type="button" class="btn-primary" onclick="createAvatar('animal')">
                                                <i class="material-icons">save</i>
                                                Criar Avatar
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Formulário Fantástico -->
                                    <div class="avatar-form" id="form-fantastico" style="display: none;">
                                        <h4><i class="material-icons">auto_awesome</i> Formulário Fantástico</h4>
                                        <div class="form-group">
                                            <label>Nome do Avatar:</label>
                                            <input type="text" name="nome_fantastico" placeholder="Ex: Aragorn, Galadriel, Thorin..." required>
                                        </div>
                                        <div class="form-group">
                                            <label>Tipo de Criatura:</label>
                                            <select name="tipo_fantastico">
                                                <option value="">Selecione...</option>
                                                <option value="elfo">Elfo</option>
                                                <option value="anao">Anão</option>
                                                <option value="orc">Orc</option>
                                                <option value="fada">Fada</option>
                                                <option value="sereia">Sereia</option>
                                                <option value="centauro">Centauro</option>
                                                <option value="minotauro">Minotauro</option>
                                                <option value="grifo">Grifo</option>
                                                <option value="fenix">Fênix</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Elemento Mágico:</label>
                                            <select name="elemento_fantastico">
                                                <option value="">Selecione...</option>
                                                <option value="fogo">Fogo</option>
                                                <option value="agua">Água</option>
                                                <option value="terra">Terra</option>
                                                <option value="ar">Ar</option>
                                                <option value="luz">Luz</option>
                                                <option value="sombra">Sombra</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Poderes Especiais:</label>
                                            <textarea name="poderes_fantastico" placeholder="Ex: Voo, invisibilidade, telepatia..." rows="2"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label>Origem:</label>
                                            <select name="origem_fantastico">
                                                <option value="">Selecione...</option>
                                                <option value="floresta">Floresta</option>
                                                <option value="montanha">Montanha</option>
                                                <option value="oceano">Oceano</option>
                                                <option value="subterraneo">Subterrâneo</option>
                                                <option value="celestial">Celestial</option>
                                            </select>
                                        </div>
                                        <div class="form-actions">
                                            <button type="button" class="btn-secondary" onclick="clearAvatarSelection()">
                                                <i class="material-icons">clear</i>
                                                Limpar
                                            </button>
                                            <button type="button" class="btn-primary" onclick="createAvatar('fantastico')">
                                                <i class="material-icons">save</i>
                                                Criar Avatar
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Formulário Extraterrestres -->
                                    <div class="avatar-form" id="form-extraterrestres" style="display: none;">
                                        <h4><i class="material-icons">rocket_launch</i> Formulário Extraterrestres</h4>
                                        <div class="form-group">
                                            <label>Nome do Avatar:</label>
                                            <input type="text" name="nome_extraterrestres" placeholder="Ex: Zoltan, X'ara, Keplerian..." required>
                                        </div>
                                        <div class="form-group">
                                            <label>Planeta de Origem:</label>
                                            <input type="text" name="planeta_extraterrestres" placeholder="Ex: Marte, Alpha Centauri">
                                        </div>
                                        <div class="form-group">
                                            <label>Tipo de Alien:</label>
                                            <select name="tipo_extraterrestres">
                                                <option value="">Selecione...</option>
                                                <option value="greys">Greys (Cinzas)</option>
                                                <option value="reptilianos">Reptilianos</option>
                                                <option value="nordicos">Nórdicos</option>
                                                <option value="insetoides">Insetoides</option>
                                                <option value="cristalinos">Cristalinos</option>
                                                <option value="energeticos">Energéticos</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Características Físicas:</label>
                                            <textarea name="caracteristicas_extraterrestres" placeholder="Ex: Cabeça grande, olhos negros, pele translúcida..." rows="2"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label>Tecnologia:</label>
                                            <select name="tecnologia_extraterrestres">
                                                <option value="">Selecione...</option>
                                                <option value="avançada">Muito Avançada</option>
                                                <option value="media">Média</option>
                                                <option value="primitiva">Primitiva</option>
                                                <option value="organica">Orgânica</option>
                                            </select>
                                        </div>
                                        <div class="form-actions">
                                            <button type="button" class="btn-secondary" onclick="clearAvatarSelection()">
                                                <i class="material-icons">clear</i>
                                                Limpar
                                            </button>
                                            <button type="button" class="btn-primary" onclick="createAvatar('extraterrestres')">
                                                <i class="material-icons">save</i>
                                                Criar Avatar
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Formulário Robôs -->
                                    <div class="avatar-form" id="form-robos" style="display: none;">
                                        <h4><i class="material-icons">smart_toy</i> Formulário Robôs</h4>
                                        <div class="form-group">
                                            <label>Nome do Avatar:</label>
                                            <input type="text" name="nome_robos" placeholder="Ex: R2-D2, WALL-E, Optimus..." required>
                                        </div>
                                        <div class="form-group">
                                            <label>Tipo de Robô:</label>
                                            <select name="tipo_robos">
                                                <option value="">Selecione...</option>
                                                <option value="humanoide">Humanóide</option>
                                                <option value="animal">Animal</option>
                                                <option value="veiculo">Veículo</option>
                                                <option value="voador">Voador</option>
                                                <option value="aquatico">Aquático</option>
                                                <option value="modular">Modular</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Material Principal:</label>
                                            <select name="material_robos">
                                                <option value="">Selecione...</option>
                                                <option value="metal">Metal</option>
                                                <option value="plastico">Plástico</option>
                                                <option value="ceramica">Cerâmica</option>
                                                <option value="composito">Compósito</option>
                                                <option value="organico">Orgânico</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Cor:</label>
                                            <input type="text" name="cor_robos" placeholder="Ex: Prateado, azul metálico">
                                        </div>
                                        <div class="form-group">
                                            <label>Funcionalidade:</label>
                                            <select name="funcionalidade_robos">
                                                <option value="">Selecione...</option>
                                                <option value="servico">Serviço</option>
                                                <option value="combate">Combate</option>
                                                <option value="exploracao">Exploração</option>
                                                <option value="companhia">Companhia</option>
                                                <option value="especializada">Especializada</option>
                                            </select>
                                        </div>
                                        <div class="form-actions">
                                            <button type="button" class="btn-secondary" onclick="clearAvatarSelection()">
                                                <i class="material-icons">clear</i>
                                                Limpar
                                            </button>
                                            <button type="button" class="btn-primary" onclick="createAvatar('robos')">
                                                <i class="material-icons">save</i>
                                                Criar Avatar
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Formulário Outros -->
                                    <div class="avatar-form" id="form-outros" style="display: none;">
                                        <h4><i class="material-icons">more_horiz</i> Formulário Outros</h4>
                                        <div class="form-group">
                                            <label>Nome do Avatar:</label>
                                            <input type="text" name="nome_outros" placeholder="Ex: Phoenix, Ethereal, Cosmos..." required>
                                        </div>
                                        <div class="form-group">
                                            <label>Categoria:</label>
                                            <select name="categoria_outros">
                                                <option value="">Selecione...</option>
                                                <option value="elemental">Elemental</option>
                                                <option value="espiritual">Espiritual</option>
                                                <option value="mecanico">Mecânico</option>
                                                <option value="hibrido">Híbrido</option>
                                                <option value="abstrato">Abstrato</option>
                                                <option value="customizado">Customizado</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Descrição Específica:</label>
                                            <textarea name="descricao_outros" placeholder="Descreva detalhadamente o tipo de avatar..." rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label>Características Únicas:</label>
                                            <textarea name="caracteristicas_outros" placeholder="Ex: Poderes especiais, aparência única, habilidades..." rows="2"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label>Estilo Visual:</label>
                                            <select name="estilo_outros">
                                                <option value="">Selecione...</option>
                                                <option value="realista">Realista</option>
                                                <option value="cartoon">Cartoon</option>
                                                <option value="anime">Anime</option>
                                                <option value="3d">3D</option>
                                                <option value="pixel">Pixel Art</option>
                                                <option value="artistico">Artístico</option>
                                            </select>
                                        </div>
                                        <div class="form-actions">
                                            <button type="button" class="btn-secondary" onclick="clearAvatarSelection()">
                                                <i class="material-icons">clear</i>
                                                Limpar
                                            </button>
                                            <button type="button" class="btn-primary" onclick="createAvatar('outros')">
                                                <i class="material-icons">save</i>
                                                Criar Avatar
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Placeholder inicial -->
                                    <div class="content-placeholder" id="placeholder-inicial">
                                        <i class="material-icons">touch_app</i>
                                        <p>Selecione um tipo de avatar no bloco esquerdo para ver o formulário específico</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bloco direito - Gerenciador de Avatares Moderno -->
                            <div class="bloco-direito">
                                <div class="bloco-header">
                                    <div class="avatars-header-main">
                                        <div class="avatars-title-section">
                                            <h3><i class="material-icons">group</i> Meus Avatares</h3>
                                            <span class="avatars-count" id="avatars-count">0 avatares</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Controles de Busca e Filtros -->
                                    <div class="avatars-controls-toolbar">
                                        <!-- Linha 1: Barra de pesquisa e botão criar -->
                                        <div class="search-create-row">
                                            <div class="avatar-search-group">
                                                <div class="search-input-group">
                                                    <i class="material-icons search-icon">search</i>
                                                    <input type="text" id="avatar-search" placeholder="Buscar avatares..." class="search-input-compact">
                                                    <button class="clear-search" id="clear-search" style="display: none;">
                                                        <i class="material-icons">clear</i>
                                                    </button>
                                                </div>
                                            </div>

                                            <button class="btn-create-compact" onclick="openCreateAvatarDialog()">
                                                <i class="material-icons">add</i>
                                                <span>Criar</span>
                                            </button>
                                        </div>

                                        <!-- Linha 2: Filtros de classificação -->
                                        <div class="filters-row">
                                            <div class="filter-group-inline">
                                                <label class="filter-label-compact">Tipo:</label>
                                                <select id="avatar-type-filter" class="filter-select-compact">
                                                    <option value="meus" selected>Meus</option>
                                                    <option value="publicos">Públicos</option>
                                                    <option value="favoritos">Favoritos</option>
                                                </select>
                                            </div>

                                            <div class="filter-group-inline">
                                                <label class="filter-label-compact">Ordenar:</label>
                                                <select id="avatar-sort" class="filter-select-compact">
                                                    <option value="recentes" selected>Recentes</option>
                                                    <option value="nome_az">A-Z</option>
                                                    <option value="ultimos_usados">Usados</option>
                                                    <option value="tipos">Tipos</option>
                                                </select>
                                            </div>

                                            <!-- Menu de Ações -->
                                            <div class="avatars-menu" style="margin-left: 1rem;">
                                                <button class="menu-btn" id="avatars-menu-btn" title="Mais Opções">
                                                    <i class="material-icons">more_vert</i>
                                                </button>
                                                <div class="menu-dropdown" id="avatars-menu-dropdown">
                                                    <button class="menu-item" id="export-avatars">
                                                        <i class="material-icons">download</i>
                                                        Exportar Avatares
                                                    </button>
                                                    <button class="menu-item" id="import-avatars">
                                                        <i class="material-icons">upload</i>
                                                        Importar Avatares
                                                    </button>
                                                    <hr class="menu-divider">
                                                    <button class="menu-item" id="enable-bulk-select">
                                                        <i class="material-icons">checklist</i>
                                                        Seleção em Massa
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="avatars-header-main" style="margin-top: 0.5rem;">
                                        <div style="flex: 1;"></div>
                                        <div class="avatars-controls">
                                            <!-- Seleção em Massa -->
                                            <div class="bulk-actions" id="avatars-bulk-actions" style="display: none;">
                                                <button class="bulk-btn" id="bulk-select-all" title="Selecionar Todos">
                                                    <i class="material-icons">select_all</i>
                                                </button>
                                                <button class="bulk-btn" id="bulk-favorite" title="Favoritar Selecionados">
                                                    <i class="material-icons">star</i>
                                                </button>
                                                <button class="bulk-btn" id="bulk-add-to-prompt" title="Adicionar ao Prompt">
                                                    <i class="material-icons">add_to_queue</i>
                                                </button>
                                                <button class="bulk-btn danger" id="bulk-delete" title="Excluir Selecionados">
                                                    <i class="material-icons">delete</i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bloco-content">
                                    <!-- Container de Avatares -->
                                    <div class="avatars-container" id="avatars-container">
                                        <!-- Estado Vazio -->
                                        <div class="avatars-empty-state" id="avatars-empty-state">
                                            <div class="empty-icon">
                                                <i class="material-icons">person_add</i>
                                            </div>
                                            <h4>Nenhum avatar criado</h4>
                                            <p>Crie seu primeiro avatar selecionando um tipo no bloco esquerdo</p>
                                            <button class="btn-create-first" onclick="document.querySelector('.type-card').click()">
                                                <i class="material-icons">add</i>
                                                Criar Primeiro Avatar
                                            </button>
                                        </div>
                                        
                                        <!-- Lista de Avatares -->
                                        <div class="avatars-list-view" id="avatars-list-view">
                                            <!-- Itens de lista serão inseridos aqui dinamicamente -->
                                        </div>
                                    </div>
                                    
                                    <!-- Indicador de Carregamento -->
                                    <div class="avatars-loading" id="avatars-loading" style="display: none;">
                                        <div class="loading-spinner"></div>
                                        <p>Carregando avatares...</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Modal de Ações do Avatar -->
                            <div class="avatar-action-modal" id="avatar-action-modal">
                                <div class="modal-content-avatar">
                                    <div class="modal-header-avatar">
                                        <h3 class="modal-title-avatar" id="modal-title-avatar">
                                            <i class="material-icons">settings</i>
                                            Ações do Avatar
                                        </h3>
                                        <button class="modal-close-avatar" id="modal-close-avatar">×</button>
                                    </div>
                                    <div class="modal-body-avatar">
                                        <div class="avatar-info-modal" id="avatar-info-modal">
                                            <h4 class="avatar-name-modal" id="avatar-name-modal">Nome do Avatar</h4>
                                            <div class="avatar-type-modal" id="avatar-type-modal">Tipo</div>
                                            <p class="avatar-description-modal" id="avatar-description-modal">Descrição do avatar</p>
                                        </div>
                                    </div>
                                    <div class="modal-actions-avatar">
                                        <button class="modal-btn modal-btn-secondary" id="modal-cancel-avatar">
                                            <i class="material-icons">close</i>
                                            Cancelar
                                        </button>
                                        <button class="modal-btn modal-btn-primary" id="modal-edit-avatar">
                                            <i class="material-icons">edit</i>
                                            Editar
                                        </button>
                                        <button class="modal-btn modal-btn-danger" id="modal-delete-avatar">
                                            <i class="material-icons">delete</i>
                                            Excluir
                                        </button>
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




                    <!-- ABA 8: CÂMERA - BASEADO NO CAMERA.HTML -->
                    <div class="tab-content" id="tab-camera">
                        <div class="tab-header">
                            <h2><i class="material-icons">photo_camera</i> Gerador de Caminho de Câmera</h2>
                            <p>Configure movimentos de câmera e alvo para criar animações cinematográficas</p>
                        </div>

                        <!-- Container principal inspirado no camera.html -->
                        <div id="camera-app-container">
                            <!-- Área do canvas -->
                            <div id="camera-canvas-container">
                                <canvas id="camera-canvas" width="800" height="600"></canvas>
                            </div>
                            
                            <!-- Painel de controles -->
                            <div class="camera-controls-panel">
                                <h3>Controles de Câmera</h3>
                                
                                <!-- Instruções -->
                                <div class="camera-info-text">
                                    Clique no canvas para adicionar pontos para a <strong>Câmera</strong>. Segure <strong>Shift</strong> e clique para adicionar pontos para o <strong>Alvo</strong>.
                                </div>
                                
                                <!-- Configurações de altura e tempo -->
                                <div class="camera-input-group">
                                    <div class="camera-input-row">
                                        <label for="camera-height-input">Altura da Câmera (Y):</label>
                                        <input type="number" id="camera-height-input" value="1.5" step="0.1">
                                    </div>
                                    <div class="camera-input-row">
                                        <label for="look-at-height-input">Altura do Alvo (Y):</label>
                                        <input type="number" id="look-at-height-input" value="1.0" step="0.1">
                                    </div>
                                    <div class="camera-input-row">
                                        <label for="segment-time-input">Tempo por Segmento (s):</label>
                                        <input type="number" id="segment-time-input" value="2.0" step="0.1">
                                    </div>
                                </div>

                                <!-- Botões de controle -->
                                <div class="camera-button-group">
                                    <button id="camera-add-example">Adicionar Exemplo</button>
                                    <button id="camera-play">Reproduzir Animação</button>
                                    <button id="camera-pause">Pausar Animação</button>
                                    <button id="camera-reset">Reiniciar</button>
                                </div>
                                
                                <!-- Lista de pontos de controle -->
                                <div class="camera-section-title">Pontos de Controle</div>
                                <div class="camera-keyframe-list" id="camera-keyframe-list">
                                    <!-- Pontos serão adicionados aqui -->
                                </div>

                                <!-- Botão para gerar coordenadas -->
                                <button id="camera-generate" class="btn-primary">Gerar Coordenadas</button>
                                
                                <!-- Área de saída de dados -->
                                <h4>Dados de Posição</h4>
                                <textarea id="camera-output-area" readonly placeholder="Adicione pontos para começar."></textarea>
                                
                                <!-- Botão para adicionar ao prompt principal -->
                                <button id="camera-add-to-prompt" class="btn-primary">
                                    <i class="material-icons">add</i>
                                    Adicionar ao Prompt Principal
                                </button>
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


                    <!-- ABA 8.2: CÂMERA 2 - GERADOR VO3 -->
                    <div class="tab-content" id="tab-camera2">
                        <!-- Header com abas do VO3 -->
                        <div class="vo3-header">
                            <div class="vo3-tabs">
                                <button class="vo3-tab-btn active" data-vo3-tab="simple">
                                    <i class="material-icons">tune</i>
                                    Simples
                                </button>
                                <button class="vo3-tab-btn" data-vo3-tab="advanced">
                                    <i class="material-icons">grid_on</i>
                                    Avançado
                                </button>
                            </div>
                        </div>

                        <!-- Layout principal -->
                        <div class="vo3-main-layout">
                            <!-- Conteúdo principal (esquerda) -->
                            <div class="vo3-content">
                                <!-- MODO SIMPLES -->
                                <div class="vo3-mode-content active" id="vo3-simple-mode">
                                    <!-- Stepper -->
                                    <div class="vo3-stepper">
                                        <div class="step active" data-step="1">
                                            <span class="step-number">1</span>
                                            <span class="step-title">Enquadramento & Orientação</span>
                                        </div>
                                        <div class="step" data-step="2">
                                            <span class="step-number">2</span>
                                            <span class="step-title">Movimentos (Presets)</span>
                                        </div>
                                        <div class="step" data-step="3">
                                            <span class="step-number">3</span>
                                            <span class="step-title">Tempo & Intensidade</span>
                                        </div>
                                    </div>

                                    <!-- Passo 1: Enquadramento & Orientação -->
                                    <div class="vo3-step-content active" data-step="1">
                                        <div class="vo3-section">
                                            <h3>Enquadramento Inicial</h3>
                                            <div class="framing-options">
                                                <div class="framing-card" data-framing="general">
                                                    <div class="framing-preview">PG</div>
                                                    <span>Plano Geral</span>
                                                </div>
                                                <div class="framing-card" data-framing="medium">
                                                    <div class="framing-preview">PM</div>
                                                    <span>Médio</span>
                                                </div>
                                                <div class="framing-card" data-framing="bust">
                                                    <div class="framing-preview">PB</div>
                                                    <span>Busto</span>
                                                </div>
                                                <div class="framing-card" data-framing="close">
                                                    <div class="framing-preview">PP</div>
                                                    <span>Close</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="vo3-section">
                                            <h3>Orientação Inicial</h3>
                                            <div class="orientation-options">
                                                <div class="orientation-card" data-orientation="FRONT">
                                                    <i class="material-icons">person</i>
                                                    <span>Frente</span>
                                                </div>
                                                <div class="orientation-card" data-orientation="BACK">
                                                    <i class="material-icons">person_outline</i>
                                                    <span>Costas</span>
                                                </div>
                                                <div class="orientation-card" data-orientation="RIGHT">
                                                    <i class="material-icons">arrow_forward</i>
                                                    <span>Direita</span>
                                                </div>
                                                <div class="orientation-card" data-orientation="LEFT">
                                                    <i class="material-icons">arrow_back</i>
                                                    <span>Esquerda</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Passo 2: Movimentos -->
                                    <div class="vo3-step-content" data-step="2">
                                        <div class="vo3-section">
                                            <h3>Biblioteca de Movimentos</h3>
                                            <div class="movements-grid">
                                                <div class="movement-card" data-movement="dolly_in">
                                                    <i class="material-icons">zoom_in</i>
                                                    <h4>Dolly In</h4>
                                                    <p>Aproximação suave</p>
                                                </div>
                                                <div class="movement-card" data-movement="dolly_out">
                                                    <i class="material-icons">zoom_out</i>
                                                    <h4>Dolly Out</h4>
                                                    <p>Afastamento gradual</p>
                                                </div>
                                                <div class="movement-card" data-movement="truck_left">
                                                    <i class="material-icons">keyboard_arrow_left</i>
                                                    <h4>Truck Left</h4>
                                                    <p>Movimento lateral esquerda</p>
                                                </div>
                                                <div class="movement-card" data-movement="truck_right">
                                                    <i class="material-icons">keyboard_arrow_right</i>
                                                    <h4>Truck Right</h4>
                                                    <p>Movimento lateral direita</p>
                                                </div>
                                                <div class="movement-card" data-movement="pedestal_up">
                                                    <i class="material-icons">keyboard_arrow_up</i>
                                                    <h4>Pedestal Up</h4>
                                                    <p>Elevação vertical</p>
                                                </div>
                                                <div class="movement-card" data-movement="pedestal_down">
                                                    <i class="material-icons">keyboard_arrow_down</i>
                                                    <h4>Pedestal Down</h4>
                                                    <p>Descida vertical</p>
                                                </div>
                                                <div class="movement-card" data-movement="pan">
                                                    <i class="material-icons">sync</i>
                                                    <h4>Pan</h4>
                                                    <p>Giro horizontal</p>
                                                </div>
                                                <div class="movement-card" data-movement="tilt">
                                                    <i class="material-icons">swap_vert</i>
                                                    <h4>Tilt</h4>
                                                    <p>Inclinação vertical</p>
                                                </div>
                                                <div class="movement-card" data-movement="arc_quarter">
                                                    <i class="material-icons">rotate_90_degrees_ccw</i>
                                                    <h4>Arc ¼</h4>
                                                    <p>Arco de 90°</p>
                                                </div>
                                                <div class="movement-card" data-movement="arc_half">
                                                    <i class="material-icons">u_turn_left</i>
                                                    <h4>Arc ½</h4>
                                                    <p>Arco de 180°</p>
                                                </div>
                                                <div class="movement-card" data-movement="orbit_quarter">
                                                    <i class="material-icons">track_changes</i>
                                                    <h4>Orbit ¼</h4>
                                                    <p>Órbita de 90°</p>
                                                </div>
                                                <div class="movement-card" data-movement="orbit_half">
                                                    <i class="material-icons">360</i>
                                                    <h4>Orbit ½</h4>
                                                    <p>Órbita de 180°</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="vo3-section">
                                            <h3>Movimentos Selecionados</h3>
                                            <div class="selected-movements" id="selected-movements">
                                                <div class="empty-movements">
                                                    <p>Selecione movimentos acima para compor a sequência</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Passo 3: Tempo & Intensidade -->
                                    <div class="vo3-step-content" data-step="3">
                                        <div class="vo3-section">
                                            <h3>Configuração de Tempo</h3>
                                            <div class="time-controls">
                                                <label for="duration-slider">Duração Total: <span id="duration-value">8</span>s</label>
                                                <input type="range" id="duration-slider" min="1" max="8" value="8" step="0.5">
                                            </div>
                                        </div>

                                        <div class="vo3-section">
                                            <h3>Prévia da Sequência</h3>
                                            <div class="sequence-preview" id="sequence-preview">
                                                <p>Configure enquadramento e movimentos para ver a prévia</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODO AVANÇADO -->
                                <div class="vo3-mode-content" id="vo3-advanced-mode">
                                    <div class="vo3-advanced-layout">
                                        <div class="vo3-canvas-area">
                                            <canvas id="vo3-grid-canvas" width="800" height="800"></canvas>
                                            <div class="vo3-target-bust" id="vo3-target-bust">
                                                <svg width="40" height="40" viewBox="0 0 24 24" fill="#333">
                                                    <path d="M12,2A5,5 0 0,1 17,7A5,5 0 0,1 12,12A5,5 0 0,1 7,7A5,5 0 0,1 12,2M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"/>
                                                </svg>
                                            </div>
                                            <div class="vo3-camera-ref">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="#666">
                                                    <path d="M4,4H7L9,2H15L17,4H20A2,2 0 0,1 22,6V18A2,2 0 0,1 20,20H4A2,2 0 0,1 2,18V6A2,2 0 0,1 4,4M12,7A5,5 0 0,0 7,12A5,5 0 0,0 12,17A5,5 0 0,0 17,12A5,5 0 0,0 12,7M12,9A3,3 0 0,1 15,12A3,3 0 0,1 12,15A3,3 0 0,1 9,12A3,3 0 0,1 12,9Z"/>
                                                </svg>
                                                <span>Câmera</span>
                                            </div>
                                        </div>

                                        <div class="vo3-points-sidebar">
                                            <h3>Pontos de Movimento</h3>
                                            <div class="vo3-points-list" id="vo3-points-list">
                                                <div class="empty-points">
                                                    <p>Clique no grid para criar pontos</p>
                                                </div>
                                            </div>
                                            
                                            <div class="vo3-advanced-controls">
                                                <label>
                                                    <input type="checkbox" id="vo3-snap-toggle" checked>
                                                    Snap to grid
                                                </label>
                                                <button id="vo3-clear-points" class="btn-secondary">
                                                    <i class="material-icons">clear_all</i>
                                                    Limpar Pontos
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Controles principais -->
                                <div class="vo3-main-controls">
                                    <button id="vo3-generate-btn" class="btn-primary" disabled>
                                        <i class="material-icons">play_arrow</i>
                                        Gerar VO3
                                    </button>
                                    <button id="vo3-clear-btn" class="btn-secondary">
                                        <i class="material-icons">clear</i>
                                        Limpar
                                    </button>
                                </div>
                            </div>

                            <!-- Painel de saída (direita) -->
                            <div class="vo3-output-panel">
                                <h3>Saída do Prompt</h3>
                                
                                <div class="vo3-output-section">
                                    <h4>Resumo Natural</h4>
                                    <textarea id="vo3-natural-output" readonly placeholder="O resumo natural aparecerá aqui após gerar..."></textarea>
                                    <button id="vo3-copy-natural" class="btn-secondary">
                                        <i class="material-icons">content_copy</i>
                                        Copiar Resumo
                                    </button>
                                </div>

                                <div class="vo3-output-section">
                                    <h4>JSON VO3</h4>
                                    <textarea id="vo3-json-output" readonly placeholder="O JSON VO3 aparecerá aqui após gerar..."></textarea>
                                    <div class="vo3-json-controls">
                                        <button id="vo3-copy-json" class="btn-secondary">
                                            <i class="material-icons">content_copy</i>
                                            Copiar JSON VO3
                                        </button>
                                        <button id="vo3-add-json" class="btn-primary">
                                            <i class="material-icons">add</i>
                                            Adicionar JSON ao Prompt
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal para adicionar JSON externo -->
                        <div id="vo3-add-json-modal" class="position-modal" style="display: none;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3>Adicionar JSON VO3 Externo</h3>
                                    <button class="modal-close" id="vo3-modal-close">×</button>
                                </div>
                                <div class="modal-body">
                                    <p>Cole um JSON VO3 válido para adicionar ao prompt:</p>
                                    <textarea id="vo3-external-json" placeholder='Cole aqui o JSON VO3 externo...' rows="10"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" id="vo3-modal-cancel" class="btn-secondary">Cancelar</button>
                                    <button type="button" id="vo3-modal-add" class="btn-primary">Adicionar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal para configuração de pontos avançados -->
                        <div id="vo3-point-modal" class="position-modal" style="display: none;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 id="vo3-point-modal-title">Configurar Ponto</h3>
                                    <button class="modal-close" id="vo3-point-modal-close">×</button>
                                </div>
                                <div class="modal-body">
                                    <form id="vo3-point-form">
                                        <div id="vo3-orientation-group" class="form-group" style="display: none;">
                                            <label for="vo3-orientation">Orientação*</label>
                                            <select id="vo3-orientation" required>
                                                <option value="">Selecione...</option>
                                                <option value="FRONT">Frente</option>
                                                <option value="BACK">Costas</option>
                                                <option value="RIGHT">Direita</option>
                                                <option value="LEFT">Esquerda</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="vo3-time">Tempo (s)*</label>
                                            <input type="number" id="vo3-time" min="0" max="8" step="0.1" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="vo3-distance">Distância (m)*</label>
                                            <input type="number" id="vo3-distance" min="0" step="0.1" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="vo3-height">Altura (m)*</label>
                                            <input type="number" id="vo3-height" min="0" step="0.1" required>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" id="vo3-point-cancel" class="btn-secondary">Cancelar</button>
                                    <button type="button" id="vo3-point-save" class="btn-primary">Salvar</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Container de 3 colunas na base -->
                        <div class="bottom-controls-container">
                            <!-- Coluna 1: Campo de descrição personalizada -->
                            <div class="custom-description">
                                <label>
                                    <i class="material-icons">edit</i>
                                    Descrição Personalizada da Câmera VO3
                                </label>
                                <textarea 
                                    name="custom_camera_vo3" 
                                    placeholder="Descreva configurações específicas para geração VO3..."
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
            console.log('📋 Configurando sistema de seleção de avatares para bloco direito');
            
            const typeCards = document.querySelectorAll('#tab-avatar .type-card');
            
            // Adicionar eventos aos cards de tipo
            typeCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const type = this.dataset.type;
                    const typeName = this.querySelector('h5').textContent;
                    
                    console.log(`✅ Card selecionado: ${typeName} (${type}) - Modo BLOCO DIREITO`);
                    
                    // Marcar card como selecionado
                    typeCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    // Mostrar formulário no bloco direito
                    showAvatarForm(type, typeName);
                });
            });
        }
        
        // Função para mostrar formulário no bloco do meio
        function showAvatarForm(type, typeName) {
            console.log(`🎯 Exibindo formulário ${type} no bloco do meio`);
            
            // Ocultar placeholder
            const placeholder = document.getElementById('placeholder-inicial');
            if (placeholder) {
                placeholder.style.display = 'none';
            }
            
            // Ocultar o header do bloco central (título e descrição)
            const blocoHeader = document.querySelector('#tab-avatar .bloco-meio .bloco-header');
            if (blocoHeader) {
                blocoHeader.classList.add('hidden');
            }
            
            // Ocultar todos os formulários de avatar
            const allForms = document.querySelectorAll('#tab-avatar .avatar-form');
            allForms.forEach(form => {
                form.style.display = 'none';
                form.classList.remove('active');
            });
            
            // Mostrar formulário específico
            const targetForm = document.getElementById('form-' + type);
            if (targetForm) {
                targetForm.style.display = 'block';
                targetForm.classList.add('active');
                
                // Adicionar classe ao bloco content para maximizar espaço
                const blocoContent = document.querySelector('#tab-avatar .bloco-meio .bloco-content');
                if (blocoContent) {
                    blocoContent.classList.add('avatar-form-active');
                }
                
                console.log(`✅ Formulário ${type} exibido com sucesso`);
            } else {
                console.error(`❌ Formulário form-${type} não encontrado`);
            }
        }
        
        // Função para limpar seleção de avatar
        function clearAvatarSelection() {
            const typeCards = document.querySelectorAll('#tab-avatar .type-card');
            const placeholder = document.getElementById('placeholder-inicial');
            const blocoHeader = document.querySelector('#tab-avatar .bloco-meio .bloco-header');
            
            // Remover seleção dos cards
            typeCards.forEach(card => card.classList.remove('selected'));
            
            // Ocultar todos os formulários
            const allForms = document.querySelectorAll('#tab-avatar .avatar-form');
            allForms.forEach(form => {
                form.style.display = 'none';
                form.classList.remove('active');
                if (form.reset) form.reset();
            });
            
            // Restaurar o header do bloco central
            if (blocoHeader) {
                blocoHeader.classList.remove('hidden');
            }
            
            // Remover classe do bloco content
            const blocoContent = document.querySelector('#tab-avatar .bloco-meio .bloco-content');
            if (blocoContent) {
                blocoContent.classList.remove('avatar-form-active');
            }
            
            // Mostrar placeholder
            if (placeholder) {
                placeholder.style.display = 'flex';
            }
            
            console.log('🧹 Seleção de avatar limpa');
        }
        
        // Funções do gerenciador de avatares
        let createdAvatars = []; // Array para armazenar avatares criados
        
        function createAvatar(type) {
            console.log(`🎨 Criando avatar do tipo: ${type}`);
            
            // Coletar dados do formulário
            const form = document.getElementById('form-' + type);
            if (!form) {
                console.error('Formulário não encontrado');
                return;
            }
            
            const inputs = form.querySelectorAll('input, select, textarea');
            let avatarData = {
                id: Date.now(),
                type: type,
                created: new Date().toLocaleDateString('pt-BR'),
                isFavorite: false,
                isPublic: false
            };
            
            // Coletar dados do formulário
            inputs.forEach(input => {
                if (input.value.trim()) {
                    avatarData[input.name] = input.value;
                }
            });
            
            // Definir nome do avatar baseado no tipo
            const nameFields = {
                'humano': 'nome_humano',
                'animal': 'nome_animal', 
                'fantastico': 'nome_fantastico',
                'extraterrestres': 'nome_extraterrestres',
                'robos': 'nome_robos',
                'outros': 'nome_outros'
            };
            
            avatarData.name = avatarData[nameFields[type]] || `Avatar ${type}`;
            
            // Usar avatarManager se disponível, senão usar sistema legado
            if (window.avatarManager) {
                avatarManager.addAvatar(avatarData);
            } else {
                // Sistema legado
                createdAvatars.push(avatarData);
                if (typeof renderAvatarsList === 'function') {
                    renderAvatarsList();
                }
            }
            
            // Limpar formulário e seleção
            form.reset();
            clearAvatarSelection();
            
            // Mostrar mensagem de sucesso
            if (window.avatarManager) {
                avatarManager.showNotification(`Avatar "${avatarData.name}" criado com sucesso!`);
            }
            
            console.log('✅ Avatar criado com sucesso:', avatarData);
        }
        
        function updateAvatar(type, avatarId) {
            console.log(`🔄 Atualizando avatar ID: ${avatarId} do tipo: ${type}`);
            
            // Coletar dados do formulário
            const form = document.getElementById('form-' + type);
            if (!form) {
                console.error('Formulário não encontrado');
                return;
            }
            
            const inputs = form.querySelectorAll('input, select, textarea');
            let updatedData = {};
            
            // Coletar dados do formulário
            inputs.forEach(input => {
                if (input.value.trim()) {
                    updatedData[input.name] = input.value;
                }
            });
            
            // Definir nome do avatar baseado no tipo
            const nameFields = {
                'humano': 'nome_humano',
                'animal': 'nome_animal', 
                'fantastico': 'nome_fantastico',
                'extraterrestres': 'nome_extraterrestres',
                'robos': 'nome_robos',
                'outros': 'nome_outros'
            };
            
            updatedData.name = updatedData[nameFields[type]] || `Avatar ${type}`;
            
            // Atualizar avatar no avatarManager
            if (window.avatarManager) {
                const avatar = avatarManager.avatars.find(a => a.id === avatarId);
                if (avatar) {
                    // Manter dados importantes
                    updatedData.id = avatar.id;
                    updatedData.type = avatar.type;
                    updatedData.created = avatar.created;
                    updatedData.isFavorite = avatar.isFavorite;
                    updatedData.isPublic = avatar.isPublic;
                    
                    // Substituir avatar
                    const index = avatarManager.avatars.findIndex(a => a.id === avatarId);
                    avatarManager.avatars[index] = updatedData;
                    
                    // Atualizar interface
                    avatarManager.renderAvatars();
                }
            }
            
            // Limpar formulário e seleção
            form.reset();
            clearAvatarSelection();
            
            // Restaurar botão para criar
            const submitBtn = form.querySelector('.btn-primary');
            if (submitBtn) {
                submitBtn.textContent = 'Criar Avatar';
                submitBtn.setAttribute('onclick', `createAvatar('${type}')`);
            }
            
            // Mostrar mensagem de sucesso
            if (window.avatarManager) {
                avatarManager.showNotification(`Avatar "${updatedData.name}" atualizado com sucesso!`);
            }
            
            console.log('✅ Avatar atualizado com sucesso:', updatedData);
        }

        function openCreateAvatarDialog() {
            // Mostrar uma mensagem ou abrir modal para seleção de tipo
            if (window.avatarManager) {
                avatarManager.showNotification('Selecione um tipo de avatar no bloco esquerdo para criar um novo avatar!', 'info');
            } else {
                alert('Selecione um tipo de avatar no bloco esquerdo para criar um novo avatar!');
            }
        }

        function showSuccessMessage(message) {
            // Criar elemento de notificação
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #10b981;
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                font-weight: 500;
                z-index: 9999;
                box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
                animation: slideIn 0.3s ease-out;
            `;
            notification.textContent = message;
            
            // Adicionar animação CSS
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
            
            document.body.appendChild(notification);
            
            // Remover após 3 segundos
            setTimeout(() => {
                notification.remove();
                style.remove();
            }, 3000);
        }
        
        function getTypeName(type) {
            const typeNames = {
                'humano': 'Humano',
                'animal': 'Animal',
                'fantastico': 'Fantástico',
                'extraterrestres': 'Extraterrestres',
                'robos': 'Robôs',
                'outros': 'Outros'
            };
            return typeNames[type] || type;
        }
        
        function generateAvatarPreview(data) {
            const parts = [];
            Object.keys(data).forEach(key => {
                if (!['id', 'type', 'typeName', 'created', 'favorite', 'preview'].includes(key) && data[key]) {
                    parts.push(data[key]);
                }
            });
            return parts.slice(0, 3).join(', ');
        }
        
        function renderAvatarsList() {
            const container = document.getElementById('avatars-list');
            const noAvatars = document.getElementById('no-avatares');
            
            if (createdAvatars.length === 0) {
                noAvatars.style.display = 'block';
                return;
            }
            
            noAvatars.style.display = 'none';
            
            const avatarsHtml = createdAvatars.map(avatar => {
                const avatarName = getAvatarName(avatar);
                return `
                <div class="avatar-card" data-id="${avatar.id}">
                    <div class="avatar-card-header">
                        <h4 class="avatar-card-title">${avatarName}</h4>
                        <div class="avatar-card-actions">
                            <button class="avatar-action-btn favorite ${avatar.favorite ? 'active' : ''}" 
                                    onclick="toggleFavorite(${avatar.id})" title="Favoritar">
                                <i class="material-icons">star</i>
                            </button>
                            <button class="avatar-action-btn add-prompt" 
                                    onclick="addToPrompt(${avatar.id})" title="Adicionar ao Prompt">
                                <i class="material-icons">add</i>
                            </button>
                            <button class="avatar-action-btn" 
                                    onclick="editAvatar(${avatar.id})" title="Editar">
                                <i class="material-icons">edit</i>
                            </button>
                            <button class="avatar-action-btn" 
                                    onclick="deleteAvatar(${avatar.id})" title="Excluir">
                                <i class="material-icons">delete</i>
                            </button>
                        </div>
                    </div>
                    <div class="avatar-card-meta">
                        ${avatar.typeName} • Criado em: ${avatar.created}
                    </div>
                    <div class="avatar-card-preview">
                        ${avatar.preview}
                    </div>
                </div>
                `;
            }).join('');
            
            container.innerHTML = avatarsHtml;
        }
        
        function getAvatarName(avatar) {
            // Buscar o campo nome específico do tipo
            const nameFields = ['nome_humano', 'nome_animal', 'nome_fantastico', 'nome_extraterrestres', 'nome_robos', 'nome_outros'];
            for (let field of nameFields) {
                if (avatar[field]) {
                    return avatar[field];
                }
            }
            // Fallback para o tipo se não houver nome
            return avatar.typeName;
        }
        
        
        function addToPrompt(id) {
            const avatar = createdAvatars.find(a => a.id === id);
            if (avatar) {
                // Aqui você pode implementar a lógica para adicionar ao prompt
                console.log('➕ Adicionando avatar ao prompt:', avatar);
                alert(`Avatar "${avatar.typeName}" adicionado ao prompt!`);
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
            const targetForm = document.getElementById('form-' + type);
            
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

        // ================================================
        // SISTEMA DE POSICIONAMENTO DE CÂMERA
        // ================================================

        // Estado da aplicação
        const cameraState = {
            target: { x: 50, y: 50 },
            points: [],
            selectedPosition: null,
            isDragging: false,
            dragStartPos: null,
            currentEditingPosition: null
        };

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            initCameraPositioning();
            initCameraPositioning2();
            initVO3System();
        });

        function initCameraPositioning() {
            // Nova implementação baseada no camera.html
            const canvas = document.getElementById('camera-canvas');
            if (!canvas) {
                console.log('Canvas da câmera não encontrado, pulando inicialização');
                return;
            }

            console.log('Inicializando nova implementação de câmera baseada em camera.html');
            
            // Inicializar sistema da nova câmera
            initNewCameraSystem();
        }

        function setupCanvas() {
            const canvas = document.getElementById('camera-positioning-grid');
            const ctx = canvas.getContext('2d');
            
            // Configurar canvas para alta resolução
            const rect = canvas.getBoundingClientRect();
            const devicePixelRatio = window.devicePixelRatio || 1;
            
            canvas.width = 800 * devicePixelRatio;
            canvas.height = 800 * devicePixelRatio;
            ctx.scale(devicePixelRatio, devicePixelRatio);
            
            canvas.style.width = '800px';
            canvas.style.height = '800px';
        }

        function bindEvents() {
            const canvas = document.getElementById('camera-positioning-grid');
            const modal = document.getElementById('position-modal');
            const modalClose = document.getElementById('modal-close');
            const modalCancel = document.getElementById('modal-cancel');
            const modalSave = document.getElementById('modal-save');
            const clearAllBtn = document.getElementById('clear-all-btn');
            const exportBtn = document.getElementById('export-prompt-btn');
            const allowDragToggle = document.getElementById('allow-drag-toggle');

            // Canvas events
            canvas.addEventListener('click', handleCanvasClick);
            canvas.addEventListener('mousedown', handleMouseDown);
            canvas.addEventListener('mousemove', handleMouseMove);
            canvas.addEventListener('mouseup', handleMouseUp);
            canvas.addEventListener('mouseleave', handleMouseUp);

            // Modal events
            modalClose.addEventListener('click', closeModal);
            modalCancel.addEventListener('click', closeModal);
            modalSave.addEventListener('click', savePosition);
            
            // Control events
            clearAllBtn.addEventListener('click', clearAllPositions);
            if (exportBtn) {
                exportBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Botão exportar clicado');
                    exportPrompt();
                });
            }

            // Fechar modal ao clicar fora
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Fechar modal com ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display !== 'none') {
                    closeModal();
                }
            });
        }

        function renderGrid() {
            const canvas = document.getElementById('camera-positioning-grid');
            const ctx = canvas.getContext('2d');
            
            // Limpar canvas
            ctx.clearRect(0, 0, 800, 800);
            
            // Desenhar grid
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
            ctx.lineWidth = 1;
            
            // Linhas verticais
            for (let x = 0; x <= 800; x += 80) {
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, 800);
                ctx.stroke();
            }
            
            // Linhas horizontais
            for (let y = 0; y <= 800; y += 80) {
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(800, y);
                ctx.stroke();
            }
            
            // Linhas mais marcadas a cada 4 divisões (320px)
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
            ctx.lineWidth = 2;
            
            for (let x = 0; x <= 800; x += 320) {
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, 800);
                ctx.stroke();
            }
            
            for (let y = 0; y <= 800; y += 320) {
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(800, y);
                ctx.stroke();
            }
        }

        function renderTarget() {
            // O target SVG já está posicionado via CSS
            // Apenas garantir que está visível
            const targetIcon = document.getElementById('target-icon');
            if (targetIcon) {
                targetIcon.style.display = 'block';
            }
        }

        function renderPositions() {
            // Remover marcadores existentes
            const existingMarkers = document.querySelectorAll('.camera-marker');
            existingMarkers.forEach(marker => marker.remove());
            
            // Criar novos marcadores
            cameraState.points.forEach((point, index) => {
                createMarker(point, index + 1);
            });
            
            // Desenhar linhas conectoras
            drawConnectorLines();
            
            // Atualizar lista lateral
            updatePositionsList();
            
            // Atualizar preview do prompt
            updatePromptPreview();
        }

        function createMarker(point, number) {
            const container = document.querySelector('.grid-container');
            const marker = document.createElement('div');
            marker.className = 'camera-marker';
            marker.textContent = number;
            marker.style.left = (point.x / 100 * 800) + 'px';
            marker.style.top = (point.y / 100 * 800) + 'px';
            marker.dataset.index = number - 1;
            
            if (cameraState.selectedPosition === number - 1) {
                marker.classList.add('selected');
            }
            
            marker.addEventListener('click', function(e) {
                e.stopPropagation();
                selectPosition(number - 1);
            });
            
            container.appendChild(marker);
        }

        function drawConnectorLines() {
            const canvas = document.getElementById('camera-positioning-grid');
            const ctx = canvas.getContext('2d');
            
            if (cameraState.points.length < 2) return;
            
            ctx.strokeStyle = 'rgba(59, 130, 246, 0.6)';
            ctx.lineWidth = 2;
            ctx.setLineDash([5, 5]);
            
            ctx.beginPath();
            cameraState.points.forEach((point, index) => {
                const x = point.x / 100 * 800;
                const y = point.y / 100 * 800;
                
                if (index === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            });
            ctx.stroke();
            ctx.setLineDash([]);
        }

        function handleCanvasClick(e) {
            if (cameraState.isDragging) return;
            
            const rect = e.target.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            
            // Verificar se pode criar nova posição
            if (cameraState.points.length > 0) {
                const lastPoint = cameraState.points[cameraState.points.length - 1];
                if (lastPoint.tempo_s >= 8) {
                    showError('Não é possível criar mais posições. O tempo máximo de 8s foi atingido.');
                    return;
                }
            }
            
            createNewPosition(x, y);
        }

        function createNewPosition(x, y) {
            const position = {
                idx: cameraState.points.length + 1,
                x: parseFloat(x.toFixed(2)),
                y: parseFloat(y.toFixed(2)),
                distancia_m: null,
                altura_m: null,
                tempo_s: cameraState.points.length === 0 ? 0 : null,
                orientacao: cameraState.points.length === 0 ? null : undefined
            };
            
            cameraState.currentEditingPosition = position;
            openModal(position);
        }

        function openModal(position) {
            const modal = document.getElementById('position-modal');
            const modalTitle = document.getElementById('modal-title');
            const orientationGroup = document.getElementById('orientation-group');
            const timeInput = document.getElementById('time');
            const distanceInput = document.getElementById('distance');
            const heightInput = document.getElementById('height');
            const orientationSelect = document.getElementById('orientation');
            
            // Configurar título
            modalTitle.textContent = `Configurar Posição ${position.idx}`;
            
            // Mostrar/ocultar orientação (apenas para primeira posição)
            if (position.idx === 1) {
                orientationGroup.style.display = 'block';
                orientationSelect.required = true;
                orientationSelect.value = position.orientacao || '';
            } else {
                orientationGroup.style.display = 'none';
                orientationSelect.required = false;
            }
            
            // Configurar tempo
            if (position.idx === 1) {
                timeInput.value = 0;
                timeInput.readOnly = true;
            } else {
                timeInput.readOnly = false;
                timeInput.value = position.tempo_s || '';
                const lastTime = cameraState.points[cameraState.points.length - 1]?.tempo_s || 0;
                timeInput.min = lastTime + 0.1;
            }
            
            // Configurar outros campos
            distanceInput.value = position.distancia_m || '';
            heightInput.value = position.altura_m || '';
            
            modal.style.display = 'flex';
        }

        function closeModal() {
            const modal = document.getElementById('position-modal');
            modal.style.display = 'none';
            cameraState.currentEditingPosition = null;
            
            // Limpar mensagens de erro
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(msg => msg.remove());
        }

        function savePosition() {
            const position = cameraState.currentEditingPosition;
            if (!position) return;
            
            const timeInput = document.getElementById('time');
            const distanceInput = document.getElementById('distance');
            const heightInput = document.getElementById('height');
            const orientationSelect = document.getElementById('orientation');
            
            // Validações
            const distance = parseFloat(distanceInput.value);
            const height = parseFloat(heightInput.value);
            
            if (isNaN(distance) || distance < 0) {
                showError('Distância deve ser um número maior ou igual a 0.');
                return;
            }
            
            if (isNaN(height) || height < 0) {
                showError('Altura deve ser um número maior ou igual a 0.');
                return;
            }
            
            if (position.idx === 1) {
                if (!orientationSelect.value) {
                    showError('Orientação inicial é obrigatória.');
                    return;
                }
                position.orientacao = orientationSelect.value;
                position.tempo_s = 0;
            } else {
                const time = parseFloat(timeInput.value);
                const lastTime = cameraState.points[cameraState.points.length - 1]?.tempo_s || 0;
                
                if (isNaN(time) || time <= lastTime || time > 8) {
                    showError(`Tempo deve ser maior que ${lastTime}s e no máximo 8s.`);
                    return;
                }
                
                position.tempo_s = time;
            }
            
            position.distancia_m = distance;
            position.altura_m = height;
            
            // Adicionar à lista ou atualizar existente
            const existingIndex = cameraState.points.findIndex(p => p.idx === position.idx);
            if (existingIndex >= 0) {
                cameraState.points[existingIndex] = position;
            } else {
                cameraState.points.push(position);
            }
            
            // Renderizar novamente
            renderGrid();
            renderPositions();
            
            closeModal();
        }

        function selectPosition(index) {
            cameraState.selectedPosition = index;
            const position = cameraState.points[index];
            if (position) {
                cameraState.currentEditingPosition = { ...position };
                openModal(cameraState.currentEditingPosition);
            }
            renderPositions();
        }

        function updatePositionsList() {
            const listContainer = document.getElementById('positions-list');
            
            if (cameraState.points.length === 0) {
                listContainer.innerHTML = '<div class="empty-positions"><p>Clique no grid para criar posições</p></div>';
                return;
            }
            
            let html = '';
            cameraState.points.forEach((point, index) => {
                const isSelected = cameraState.selectedPosition === index;
                html += `
                    <div class="position-item ${isSelected ? 'selected' : ''}" onclick="selectPosition(${index})">
                        <div class="position-header">
                            <div class="position-number">${point.idx}</div>
                            <div class="position-time">${point.tempo_s}s</div>
                        </div>
                        <div class="position-details">
                            Distância: ${point.distancia_m}m | Altura: ${point.altura_m}m
                            ${point.orientacao ? `<br>Orientação: ${point.orientacao}` : ''}
                        </div>
                        <div class="position-coords">
                            x: ${point.x}, y: ${point.y}
                        </div>
                    </div>
                `;
            });
            
            listContainer.innerHTML = html;
        }

        function clearAllPositions() {
            if (cameraState.points.length === 0) return;
            
            if (confirm('Deseja realmente limpar todas as posições?')) {
                cameraState.points = [];
                cameraState.selectedPosition = null;
                renderGrid();
                renderPositions();
            }
        }

        function updatePromptPreview() {
            const previewContainer = document.getElementById('prompt-preview');
            
            if (cameraState.points.length === 0) {
                previewContainer.innerHTML = '<div class="empty-prompt"><p>Crie posições no grid para gerar o prompt</p></div>';
                return;
            }
            
            const prompt = generatePromptText();
            previewContainer.textContent = prompt;
            
            // Auto scroll para o final
            previewContainer.scrollTop = previewContainer.scrollHeight;
        }

        function generatePromptText() {
            let prompt = 'CAMERA_PATH (GRID FIXO)\n';
            prompt += `Target: x=${cameraState.target.x}, y=${cameraState.target.y}  // busto humano central fixo\n`;
            
            if (cameraState.points.length > 0) {
                const firstPoint = cameraState.points[0];
                prompt += 'InitialPosition:\n';
                prompt += `  index=${firstPoint.idx}, t=${firstPoint.tempo_s}s, x=${firstPoint.x}, y=${firstPoint.y}, distancia_m=${firstPoint.distancia_m}, altura_m=${firstPoint.altura_m}, orientacao=${firstPoint.orientacao}\n`;
            }
            
            if (cameraState.points.length > 1) {
                prompt += 'Positions:\n';
                cameraState.points.slice(1).forEach(point => {
                    prompt += `  ${point.idx}: t=${point.tempo_s}s, x=${point.x}, y=${point.y}, distancia_m=${point.distancia_m}, altura_m=${point.altura_m}\n`;
                });
            }
            
            prompt += `Constraints: 0 = t1 < t2 < ... ≤ 8s; grid=fixo 800x800; unidades x,y em 0–100\n`;
            
            return prompt;
        }

        function exportPrompt() {
            console.log('Function addToPrompt chamada');
            console.log('Posições criadas:', cameraState.points.length);
            
            if (cameraState.points.length === 0) {
                showExportError('Não há posições para adicionar ao prompt.');
                return;
            }
            
            const prompt = generatePromptText();
            console.log('Prompt gerado:', prompt);
            
            // Buscar o textarea do prompt principal
            const promptTextarea = document.querySelector('textarea[name="enhanced_prompt"], textarea[name="original_prompt"], #enhanced_prompt, #original_prompt');
            
            if (promptTextarea) {
                // Adicionar o prompt de câmera ao conteúdo existente
                const currentContent = promptTextarea.value;
                const newContent = currentContent ? currentContent + '\n\n' + prompt : prompt;
                promptTextarea.value = newContent;
                
                // Focar no textarea e scroll para o final
                promptTextarea.focus();
                promptTextarea.scrollTop = promptTextarea.scrollHeight;
                
                showSuccess('Prompt de câmera adicionado com sucesso!');
                console.log('Prompt adicionado ao textarea principal');
            } else {
                // Fallback: copiar para área de transferência
                console.log('Textarea principal não encontrado, copiando para área de transferência');
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(prompt).then(() => {
                        console.log('Prompt copiado com sucesso via clipboard API');
                        showSuccess('Textarea principal não encontrado. Prompt copiado para a área de transferência!');
                    }).catch((err) => {
                        console.log('Erro no clipboard API, usando fallback:', err);
                        copyToClipboardFallback(prompt);
                    });
                } else {
                    console.log('Clipboard API não disponível, usando fallback');
                    copyToClipboardFallback(prompt);
                }
            }
        }

        function copyToClipboardFallback(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.top = '-9999px';
            textArea.style.left = '-9999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    console.log('Prompt copiado com sucesso via fallback');
                    showSuccess('Prompt copiado para a área de transferência!');
                } else {
                    console.log('Falha ao copiar via fallback');
                    showExportError('Falha ao copiar prompt. Tente novamente.');
                }
            } catch (err) {
                console.log('Erro no fallback:', err);
                showExportError('Erro ao copiar prompt: ' + err.message);
            } finally {
                document.body.removeChild(textArea);
            }
        }

        function handleMouseDown(e) {
            const allowDrag = document.getElementById('allow-drag-toggle').checked;
            if (!allowDrag) return;
            
            const marker = e.target.closest('.camera-marker');
            if (!marker) return;
            
            e.preventDefault();
            cameraState.isDragging = true;
            cameraState.dragStartPos = {
                x: e.clientX,
                y: e.clientY,
                markerIndex: parseInt(marker.dataset.index)
            };
            
            marker.classList.add('dragging');
        }

        function handleMouseMove(e) {
            if (!cameraState.isDragging) return;
            
            const rect = e.target.getBoundingClientRect();
            const x = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
            const y = Math.max(0, Math.min(100, ((e.clientY - rect.top) / rect.height) * 100));
            
            const markerIndex = cameraState.dragStartPos.markerIndex;
            const marker = document.querySelector(`[data-index="${markerIndex}"]`);
            
            if (marker) {
                marker.style.left = (x / 100 * 800) + 'px';
                marker.style.top = (y / 100 * 800) + 'px';
            }
        }

        function handleMouseUp(e) {
            if (!cameraState.isDragging) return;
            
            const rect = e.target.getBoundingClientRect();
            const x = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
            const y = Math.max(0, Math.min(100, ((e.clientY - rect.top) / rect.height) * 100));
            
            const markerIndex = cameraState.dragStartPos.markerIndex;
            const point = cameraState.points[markerIndex];
            
            if (point) {
                point.x = parseFloat(x.toFixed(2));
                point.y = parseFloat(y.toFixed(2));
            }
            
            // Limpar estado de arrasto
            cameraState.isDragging = false;
            cameraState.dragStartPos = null;
            
            const marker = document.querySelector(`[data-index="${markerIndex}"]`);
            if (marker) {
                marker.classList.remove('dragging');
            }
            
            // Re-renderizar
            renderGrid();
            renderPositions();
        }

        function showError(message) {
            // Remover mensagens existentes
            const existingMessages = document.querySelectorAll('.error-message, .success-message');
            existingMessages.forEach(msg => msg.remove());
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            
            const modalBody = document.querySelector('.modal-body');
            modalBody.insertBefore(errorDiv, modalBody.firstChild);
        }

        function showSuccess(message) {
            // Remover mensagens existentes
            const existingMessages = document.querySelectorAll('.error-message, .success-message');
            existingMessages.forEach(msg => msg.remove());
            
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.textContent = message;
            
            const container = document.querySelector('.camera-instructions');
            container.appendChild(successDiv);
            
            // Remover após 3 segundos
            setTimeout(() => {
                successDiv.remove();
            }, 3000);
        }

        function showExportError(message) {
            // Remover mensagens existentes
            const existingMessages = document.querySelectorAll('.error-message, .success-message');
            existingMessages.forEach(msg => msg.remove());
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            
            const container = document.querySelector('.camera-instructions');
            container.appendChild(errorDiv);
            
            // Remover após 5 segundos
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }

        // ================================================
        // NOVA IMPLEMENTAÇÃO DE CÂMERA BASEADA NO CAMERA.HTML
        // ================================================

        // Sistema inspirado no camera.html com canvas interativo
        function initNewCameraSystem() {
            const canvas = document.getElementById('camera-canvas');
            const ctx = canvas.getContext('2d');
            const controlsPanel = document.querySelector('.camera-controls-panel');
            const keyframeListContainer = document.getElementById('camera-keyframe-list');
            const outputArea = document.getElementById('camera-output-area');
            const cameraHeightInput = document.getElementById('camera-height-input');
            const lookAtHeightInput = document.getElementById('look-at-height-input');
            const segmentTimeInput = document.getElementById('segment-time-input');
            const addExampleButton = document.getElementById('camera-add-example');
            const playButton = document.getElementById('camera-play');
            const pauseButton = document.getElementById('camera-pause');
            const resetButton = document.getElementById('camera-reset');
            const generateButton = document.getElementById('camera-generate');
            const addToPromptButton = document.getElementById('camera-add-to-prompt');

            let cameraPoints = [];
            let lookAtPoints = [];
            let isAnimating = false;
            let animationProgress = 0;
            let animationFrameId;

            const scale = 10; // Pixels por unidade
            const gridSize = 50; // Tamanho total da grelha

            function setupCanvas() {
                const container = document.getElementById('camera-canvas-container');
                const rect = container.getBoundingClientRect();
                
                // Definir tamanho fixo mais apropriado
                canvas.width = Math.min(rect.width || 600, 600);
                canvas.height = Math.min(rect.height || 450, 450);
                
                // Definir tamanho visual do canvas
                canvas.style.width = canvas.width + 'px';
                canvas.style.height = canvas.height + 'px';
                
                draw();
            }
            
            function draw() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.fillStyle = '#2a2a2a';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // Desenhar a grelha
                ctx.strokeStyle = '#3a3a3a';
                ctx.lineWidth = 0.5;
                const gridStep = scale * 5;
                const centerX = canvas.width / 2;
                const centerY = canvas.height / 2;

                for (let x = -centerX; x <= centerX; x += gridStep) {
                    ctx.beginPath();
                    ctx.moveTo(centerX + x, 0);
                    ctx.lineTo(centerX + x, canvas.height);
                    ctx.stroke();
                }
                for (let y = -centerY; y <= centerY; y += gridStep) {
                    ctx.beginPath();
                    ctx.moveTo(0, centerY + y);
                    ctx.lineTo(canvas.width, centerY + y);
                    ctx.stroke();
                }

                // Desenhar os eixos
                ctx.strokeStyle = '#6a6a6a';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(0, centerY);
                ctx.lineTo(canvas.width, centerY);
                ctx.moveTo(centerX, 0);
                ctx.lineTo(centerX, canvas.height);
                ctx.stroke();

                // Desenhar o caminho da câmara e os pontos
                if (cameraPoints.length > 0) {
                    ctx.strokeStyle = '#ff5722';
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    ctx.moveTo(cameraPoints[0].x * scale + centerX, -cameraPoints[0].y * scale + centerY);
                    for (let i = 1; i < cameraPoints.length; i++) {
                        ctx.lineTo(cameraPoints[i].x * scale + centerX, -cameraPoints[i].y * scale + centerY);
                    }
                    ctx.stroke();

                    // Desenhar pontos da câmera maiores e numerados
                    cameraPoints.forEach((point, index) => {
                        const screenX = point.x * scale + centerX;
                        const screenY = -point.y * scale + centerY;
                        
                        // Círculo externo
                        ctx.fillStyle = '#ff5722';
                        ctx.beginPath();
                        ctx.arc(screenX, screenY, 6, 0, 2 * Math.PI);
                        ctx.fill();
                        
                        // Número do ponto
                        ctx.fillStyle = 'white';
                        ctx.font = '12px Arial';
                        ctx.textAlign = 'center';
                        ctx.fillText((index + 1).toString(), screenX, screenY + 4);
                    });
                }
                
                // Desenhar o caminho do alvo e os pontos
                if (lookAtPoints.length > 0) {
                    ctx.strokeStyle = '#00ff00';
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    ctx.moveTo(lookAtPoints[0].x * scale + centerX, -lookAtPoints[0].y * scale + centerY);
                    for (let i = 1; i < lookAtPoints.length; i++) {
                        ctx.lineTo(lookAtPoints[i].x * scale + centerX, -lookAtPoints[i].y * scale + centerY);
                    }
                    ctx.stroke();

                    // Desenhar pontos do alvo maiores e numerados
                    lookAtPoints.forEach((point, index) => {
                        const screenX = point.x * scale + centerX;
                        const screenY = -point.y * scale + centerY;
                        
                        // Círculo externo
                        ctx.fillStyle = '#00ff00';
                        ctx.beginPath();
                        ctx.arc(screenX, screenY, 6, 0, 2 * Math.PI);
                        ctx.fill();
                        
                        // Número do ponto
                        ctx.fillStyle = 'black';
                        ctx.font = '12px Arial';
                        ctx.textAlign = 'center';
                        ctx.fillText((index + 1).toString(), screenX, screenY + 4);
                    });
                }
            }

            function updateKeyframeList() {
                keyframeListContainer.innerHTML = '';
                
                cameraPoints.forEach((point, i) => {
                    const item = document.createElement('div');
                    item.className = 'camera-keyframe-item';
                    item.style.color = '#ff5722';
                    item.innerHTML = `
                        <span>Câmera ${i + 1}</span>
                        <div class="coords">X: ${point.x.toFixed(2)}, Y: ${parseFloat(cameraHeightInput.value).toFixed(2)}, Z: ${point.y.toFixed(2)}</div>
                    `;
                    keyframeListContainer.appendChild(item);
                });

                lookAtPoints.forEach((point, i) => {
                    const item = document.createElement('div');
                    item.className = 'camera-keyframe-item';
                    item.style.color = '#00ff00';
                    item.innerHTML = `
                        <span>Alvo ${i + 1}</span>
                        <div class="coords">X: ${point.x.toFixed(2)}, Y: ${parseFloat(lookAtHeightInput.value).toFixed(2)}, Z: ${point.y.toFixed(2)}</div>
                    `;
                    keyframeListContainer.appendChild(item);
                });
            }

            function resetApp() {
                isAnimating = false;
                animationProgress = 0;
                cameraPoints = [];
                lookAtPoints = [];
                cameraHeightInput.value = '1.5';
                lookAtHeightInput.value = '1.0';
                segmentTimeInput.value = '2.0';
                draw();
                updateKeyframeList();
                outputArea.value = 'Adicione pontos para começar.';
                console.log('Sistema resetado');
            }

            function addExamplePaths() {
                resetApp();
                
                cameraPoints = [
                    { x: -15, y: -10 },
                    { x: 15, y: -10 },
                    { x: 15, y: 10 },
                    { x: -15, y: 10 }
                ];

                lookAtPoints = [
                    { x: -15, y: 10 },
                    { x: 15, y: 10 },
                    { x: 15, y: -10 },
                    { x: -15, y: -10 }
                ];
                
                draw();
                updateKeyframeList();
                outputArea.value = 'Exemplo de caminho adicionado. Clique em "Reproduzir Animação" para ver.';
            }

            // Event listeners
            canvas.addEventListener('click', (event) => {
                const rect = canvas.getBoundingClientRect();
                
                // Calcular posição real do mouse no canvas
                const scaleX = canvas.width / rect.width;
                const scaleY = canvas.height / rect.height;
                
                const canvasX = (event.clientX - rect.left) * scaleX;
                const canvasY = (event.clientY - rect.top) * scaleY;
                
                // Converter para coordenadas do mundo (-25 a +25 para X e Y)
                const worldX = (canvasX - canvas.width / 2) / scale;
                const worldY = -(canvasY - canvas.height / 2) / scale;
                
                console.log(`Clique em: Canvas(${canvasX.toFixed(1)}, ${canvasY.toFixed(1)}) -> Mundo(${worldX.toFixed(2)}, ${worldY.toFixed(2)})`);
                
                if (event.shiftKey) {
                    lookAtPoints.push({ x: worldX, y: worldY });
                    console.log(`Adicionado ponto de alvo: ${worldX.toFixed(2)}, ${worldY.toFixed(2)}`);
                } else {
                    cameraPoints.push({ x: worldX, y: worldY });
                    console.log(`Adicionado ponto de câmera: ${worldX.toFixed(2)}, ${worldY.toFixed(2)}`);
                }
                draw();
                updateKeyframeList();
                isAnimating = false;
            });

            addExampleButton.addEventListener('click', addExamplePaths);
            
            playButton.addEventListener('click', () => {
                if (cameraPoints.length < 2 || lookAtPoints.length < 2) {
                    outputArea.value = 'Adicione pelo menos 2 pontos para cada caminho para iniciar a animação.';
                    return;
                }
                isAnimating = true;
                animationProgress = 0;
                outputArea.value = 'Iniciando animação...';
                console.log('Animação iniciada');
            });
            
            pauseButton.addEventListener('click', () => { 
                isAnimating = false; 
                outputArea.value = 'Animação pausada.';
                console.log('Animação pausada');
            });
            
            resetButton.addEventListener('click', resetApp);

            generateButton.addEventListener('click', () => {
                if (cameraPoints.length < 2 || lookAtPoints.length < 2) {
                    outputArea.value = 'Adicione pelo menos 2 pontos para cada caminho para gerar as coordenadas.';
                    return;
                }
                
                isAnimating = false;

                const numSteps = 100;
                const generatedData = [];
                const camHeight = parseFloat(cameraHeightInput.value);
                const lookAtHeight = parseFloat(lookAtHeightInput.value);
                const totalTime = parseFloat(segmentTimeInput.value) * (Math.max(cameraPoints.length, lookAtPoints.length) - 1);

                for (let i = 0; i < numSteps; i++) {
                    const t = i / (numSteps - 1);
                    const camPos = getPointOnPath(cameraPoints, t);
                    const lookAtPos = getPointOnPath(lookAtPoints, t);
                    
                    generatedData.push({
                        time: (t * totalTime).toFixed(2),
                        position: [camPos.x.toFixed(2), camHeight.toFixed(2), camPos.y.toFixed(2)],
                        lookAt: [lookAtPos.x.toFixed(2), lookAtHeight.toFixed(2), lookAtPos.y.toFixed(2)]
                    });
                }
                
                outputArea.value = JSON.stringify(generatedData, null, 2);
            });

            addToPromptButton.addEventListener('click', () => {
                if (outputArea.value === 'Adicione pontos para começar.' || !outputArea.value.trim()) {
                    alert('Gere as coordenadas primeiro usando o botão "Gerar Coordenadas"');
                    return;
                }

                // Buscar o textarea do prompt principal
                const promptTextarea = document.querySelector('textarea[name="enhanced_prompt"], textarea[name="original_prompt"], #enhanced_prompt, #original_prompt');
                
                if (promptTextarea) {
                    const currentContent = promptTextarea.value;
                    const newContent = currentContent ? currentContent + '\n\n// CAMERA PATH\n' + outputArea.value : '// CAMERA PATH\n' + outputArea.value;
                    promptTextarea.value = newContent;
                    
                    promptTextarea.focus();
                    promptTextarea.scrollTop = promptTextarea.scrollHeight;
                    
                    alert('Dados de câmera adicionados ao prompt principal!');
                } else {
                    // Copiar para área de transferência como fallback
                    navigator.clipboard.writeText(outputArea.value).then(() => {
                        alert('Textarea principal não encontrado. Dados copiados para a área de transferência!');
                    }).catch(() => {
                        alert('Erro ao copiar dados. Textarea principal não encontrado.');
                    });
                }
            });

            function getPointOnPath(pathPoints, t) {
                if (pathPoints.length < 2) return { x: 0, y: 0 };
                
                const totalSegments = pathPoints.length - 1;
                const segmentIndex = Math.floor(t * totalSegments);
                const segmentProgress = (t * totalSegments) - segmentIndex;
                
                const startPoint = pathPoints[segmentIndex];
                const endPoint = pathPoints[Math.min(segmentIndex + 1, totalSegments)];

                const x = startPoint.x + segmentProgress * (endPoint.x - startPoint.x);
                const y = startPoint.y + segmentProgress * (endPoint.y - startPoint.y);
                
                return { x, y };
            }

            // Função de animação
            function animate() {
                animationFrameId = requestAnimationFrame(animate);

                if (!isAnimating || cameraPoints.length < 2 || lookAtPoints.length < 2) {
                    draw();
                    return;
                }

                const camPos = getPointOnPath(cameraPoints, animationProgress);
                const lookAtPos = getPointOnPath(lookAtPoints, animationProgress);
                
                draw();

                const centerX = canvas.width / 2;
                const centerY = canvas.height / 2;
                const camScreenX = camPos.x * scale + centerX;
                const camScreenY = -camPos.y * scale + centerY;
                const lookAtScreenX = lookAtPos.x * scale + centerX;
                const lookAtScreenY = -lookAtPos.y * scale + centerY;
                
                const cameraHeight = parseFloat(cameraHeightInput.value);
                const lookAtHeight = parseFloat(lookAtHeightInput.value);
                const dist3D = Math.sqrt(Math.pow(lookAtPos.x - camPos.x, 2) + Math.pow(lookAtHeight - cameraHeight, 2) + Math.pow(lookAtPos.y - camPos.y, 2)).toFixed(2);
                
                // Desenhar a linha de visão durante animação
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.7)';
                ctx.lineWidth = 2;
                ctx.beginPath();
                ctx.moveTo(camScreenX, camScreenY);
                ctx.lineTo(lookAtScreenX, lookAtScreenY);
                ctx.stroke();

                // Desenhar ícone da câmara (triângulo) durante animação
                ctx.fillStyle = '#ffaa00';
                const angle = Math.atan2(lookAtScreenY - camScreenY, lookAtScreenX - camScreenX);
                ctx.beginPath();
                ctx.moveTo(camScreenX + 12 * Math.cos(angle), camScreenY + 12 * Math.sin(angle));
                ctx.lineTo(camScreenX + 12 * Math.cos(angle + 2 * Math.PI / 3), camScreenY + 12 * Math.sin(angle + 2 * Math.PI / 3));
                ctx.lineTo(camScreenX + 12 * Math.cos(angle + 4 * Math.PI / 3), camScreenY + 12 * Math.sin(angle + 4 * Math.PI / 3));
                ctx.closePath();
                ctx.fill();
                
                // Desenhar ícone do alvo (círculo) durante animação
                ctx.fillStyle = '#00ffaa';
                ctx.beginPath();
                ctx.arc(lookAtScreenX, lookAtScreenY, 8, 0, 2 * Math.PI);
                ctx.fill();
                
                // Atualizar a área de saída com dados em tempo real
                const currentTime = (animationProgress * parseFloat(segmentTimeInput.value) * (cameraPoints.length - 1)).toFixed(2);
                outputArea.value = `ANIMAÇÃO EM EXECUÇÃO:\n` +
                                  `Tempo: ${currentTime}s\n` +
                                  `Distância: ${dist3D}\n` +
                                  `Pos. Câmera: X: ${camPos.x.toFixed(2)}, Y: ${cameraHeight.toFixed(2)}, Z: ${camPos.y.toFixed(2)}\n` +
                                  `Pos. Alvo: X: ${lookAtPos.x.toFixed(2)}, Y: ${lookAtHeight.toFixed(2)}, Z: ${lookAtPos.y.toFixed(2)}`;

                animationProgress += (1 / 60) / (parseFloat(segmentTimeInput.value) * (cameraPoints.length - 1));
                if (animationProgress > 1) {
                    animationProgress = 0;
                }
            }

            // Redimensionar canvas quando a janela muda
            window.addEventListener('resize', setupCanvas);

            // Inicializar
            setupCanvas();
            resetApp();
            
            // Iniciar loop de animação
            animate();
        }

        // ================================================
        // SISTEMA DE POSICIONAMENTO DE CÂMERA 2
        // ================================================

        // Estado da segunda câmera
        const cameraState2 = {
            target: { x: 50, y: 50 },
            points: [],
            selectedPosition: null,
            isDragging: false,
            dragStartPos: null,
            currentEditingPosition: null
        };

        function initCameraPositioning2() {
            const canvas = document.getElementById('camera-positioning-grid-2');
            if (!canvas) return;

            setupCanvas2();
            bindEvents2();
            renderGrid2();
            renderTarget2();
            updatePromptPreview2();
        }

        function setupCanvas2() {
            const canvas = document.getElementById('camera-positioning-grid-2');
            const ctx = canvas.getContext('2d');
            
            const devicePixelRatio = window.devicePixelRatio || 1;
            canvas.width = 800 * devicePixelRatio;
            canvas.height = 800 * devicePixelRatio;
            ctx.scale(devicePixelRatio, devicePixelRatio);
            
            canvas.style.width = '800px';
            canvas.style.height = '800px';
        }

        function bindEvents2() {
            const canvas = document.getElementById('camera-positioning-grid-2');
            const modal = document.getElementById('position-modal-2');
            const modalClose = document.getElementById('modal-close-2');
            const modalCancel = document.getElementById('modal-cancel-2');
            const modalSave = document.getElementById('modal-save-2');
            const clearAllBtn = document.getElementById('clear-all-btn-2');
            const exportBtn = document.getElementById('export-prompt-btn-2');

            canvas.addEventListener('click', handleCanvasClick2);
            canvas.addEventListener('mousedown', handleMouseDown2);
            canvas.addEventListener('mousemove', handleMouseMove2);
            canvas.addEventListener('mouseup', handleMouseUp2);
            canvas.addEventListener('mouseleave', handleMouseUp2);

            modalClose.addEventListener('click', closeModal2);
            modalCancel.addEventListener('click', closeModal2);
            modalSave.addEventListener('click', savePosition2);
            
            clearAllBtn.addEventListener('click', clearAllPositions2);
            if (exportBtn) {
                exportBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    exportPrompt2();
                });
            }

            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal2();
                }
            });
        }

        function renderGrid2() {
            const canvas = document.getElementById('camera-positioning-grid-2');
            const ctx = canvas.getContext('2d');
            
            ctx.clearRect(0, 0, 800, 800);
            
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
            ctx.lineWidth = 1;
            
            for (let x = 0; x <= 800; x += 80) {
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, 800);
                ctx.stroke();
            }
            
            for (let y = 0; y <= 800; y += 80) {
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(800, y);
                ctx.stroke();
            }
            
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
            ctx.lineWidth = 2;
            
            for (let x = 0; x <= 800; x += 320) {
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, 800);
                ctx.stroke();
            }
            
            for (let y = 0; y <= 800; y += 320) {
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(800, y);
                ctx.stroke();
            }
        }

        function renderTarget2() {
            const targetIcon = document.getElementById('target-icon-2');
            if (targetIcon) {
                targetIcon.style.display = 'block';
            }
        }

        function renderPositions2() {
            const existingMarkers = document.querySelectorAll('.camera-marker-2');
            existingMarkers.forEach(marker => marker.remove());
            
            cameraState2.points.forEach((point, index) => {
                createMarker2(point, index + 1);
            });
            
            drawConnectorLines2();
            updatePositionsList2();
            updatePromptPreview2();
        }

        function createMarker2(point, number) {
            const container = document.querySelector('#tab-camera2 .grid-container');
            const marker = document.createElement('div');
            marker.className = 'camera-marker camera-marker-2';
            marker.textContent = number;
            marker.style.left = (point.x / 100 * 800) + 'px';
            marker.style.top = (point.y / 100 * 800) + 'px';
            marker.dataset.index = number - 1;
            
            if (cameraState2.selectedPosition === number - 1) {
                marker.classList.add('selected');
            }
            
            marker.addEventListener('click', function(e) {
                e.stopPropagation();
                selectPosition2(number - 1);
            });
            
            container.appendChild(marker);
        }

        function drawConnectorLines2() {
            const canvas = document.getElementById('camera-positioning-grid-2');
            const ctx = canvas.getContext('2d');
            
            if (cameraState2.points.length < 2) return;
            
            ctx.strokeStyle = 'rgba(59, 130, 246, 0.6)';
            ctx.lineWidth = 2;
            ctx.setLineDash([5, 5]);
            
            ctx.beginPath();
            cameraState2.points.forEach((point, index) => {
                const x = point.x / 100 * 800;
                const y = point.y / 100 * 800;
                
                if (index === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            });
            ctx.stroke();
            ctx.setLineDash([]);
        }

        function handleCanvasClick2(e) {
            if (cameraState2.isDragging) return;
            
            const rect = e.target.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            
            if (cameraState2.points.length > 0) {
                const lastPoint = cameraState2.points[cameraState2.points.length - 1];
                if (lastPoint.tempo_s >= 8) {
                    showExportError2('Não é possível criar mais posições. O tempo máximo de 8s foi atingido.');
                    return;
                }
            }
            
            createNewPosition2(x, y);
        }

        function createNewPosition2(x, y) {
            const position = {
                idx: cameraState2.points.length + 1,
                x: parseFloat(x.toFixed(2)),
                y: parseFloat(y.toFixed(2)),
                distancia_m: null,
                altura_m: null,
                tempo_s: cameraState2.points.length === 0 ? 0 : null,
                orientacao: cameraState2.points.length === 0 ? null : undefined
            };
            
            cameraState2.currentEditingPosition = position;
            openModal2(position);
        }

        function openModal2(position) {
            const modal = document.getElementById('position-modal-2');
            const modalTitle = document.getElementById('modal-title-2');
            const orientationGroup = document.getElementById('orientation-group-2');
            const timeInput = document.getElementById('time-2');
            const distanceInput = document.getElementById('distance-2');
            const heightInput = document.getElementById('height-2');
            const orientationSelect = document.getElementById('orientation-2');
            
            modalTitle.textContent = `Configurar Posição ${position.idx}`;
            
            if (position.idx === 1) {
                orientationGroup.style.display = 'block';
                orientationSelect.required = true;
                orientationSelect.value = position.orientacao || '';
            } else {
                orientationGroup.style.display = 'none';
                orientationSelect.required = false;
            }
            
            if (position.idx === 1) {
                timeInput.value = 0;
                timeInput.readOnly = true;
            } else {
                timeInput.readOnly = false;
                timeInput.value = position.tempo_s || '';
                const lastTime = cameraState2.points[cameraState2.points.length - 1]?.tempo_s || 0;
                timeInput.min = lastTime + 0.1;
            }
            
            distanceInput.value = position.distancia_m || '';
            heightInput.value = position.altura_m || '';
            
            modal.style.display = 'flex';
        }

        function closeModal2() {
            const modal = document.getElementById('position-modal-2');
            modal.style.display = 'none';
            cameraState2.currentEditingPosition = null;
            
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(msg => msg.remove());
        }

        function savePosition2() {
            const position = cameraState2.currentEditingPosition;
            if (!position) return;
            
            const timeInput = document.getElementById('time-2');
            const distanceInput = document.getElementById('distance-2');
            const heightInput = document.getElementById('height-2');
            const orientationSelect = document.getElementById('orientation-2');
            
            const distance = parseFloat(distanceInput.value);
            const height = parseFloat(heightInput.value);
            
            if (isNaN(distance) || distance < 0) {
                showError2('Distância deve ser um número maior ou igual a 0.');
                return;
            }
            
            if (isNaN(height) || height < 0) {
                showError2('Altura deve ser um número maior ou igual a 0.');
                return;
            }
            
            if (position.idx === 1) {
                if (!orientationSelect.value) {
                    showError2('Orientação inicial é obrigatória.');
                    return;
                }
                position.orientacao = orientationSelect.value;
                position.tempo_s = 0;
            } else {
                const time = parseFloat(timeInput.value);
                const lastTime = cameraState2.points[cameraState2.points.length - 1]?.tempo_s || 0;
                
                if (isNaN(time) || time <= lastTime || time > 8) {
                    showError2(`Tempo deve ser maior que ${lastTime}s e no máximo 8s.`);
                    return;
                }
                
                position.tempo_s = time;
            }
            
            position.distancia_m = distance;
            position.altura_m = height;
            
            const existingIndex = cameraState2.points.findIndex(p => p.idx === position.idx);
            if (existingIndex >= 0) {
                cameraState2.points[existingIndex] = position;
            } else {
                cameraState2.points.push(position);
            }
            
            renderGrid2();
            renderPositions2();
            closeModal2();
        }

        function selectPosition2(index) {
            cameraState2.selectedPosition = index;
            const position = cameraState2.points[index];
            if (position) {
                cameraState2.currentEditingPosition = { ...position };
                openModal2(cameraState2.currentEditingPosition);
            }
            renderPositions2();
        }

        function updatePositionsList2() {
            const listContainer = document.getElementById('positions-list-2');
            
            if (cameraState2.points.length === 0) {
                listContainer.innerHTML = '<div class="empty-positions"><p>Clique no grid para criar posições</p></div>';
                return;
            }
            
            let html = '';
            cameraState2.points.forEach((point, index) => {
                const isSelected = cameraState2.selectedPosition === index;
                html += `
                    <div class="position-item ${isSelected ? 'selected' : ''}" onclick="selectPosition2(${index})">
                        <div class="position-header">
                            <div class="position-number">${point.idx}</div>
                            <div class="position-time">${point.tempo_s}s</div>
                        </div>
                        <div class="position-details">
                            Distância: ${point.distancia_m}m | Altura: ${point.altura_m}m
                            ${point.orientacao ? `<br>Orientação: ${point.orientacao}` : ''}
                        </div>
                        <div class="position-coords">
                            x: ${point.x}, y: ${point.y}
                        </div>
                    </div>
                `;
            });
            
            listContainer.innerHTML = html;
        }

        function clearAllPositions2() {
            if (cameraState2.points.length === 0) return;
            
            if (confirm('Deseja realmente limpar todas as posições?')) {
                cameraState2.points = [];
                cameraState2.selectedPosition = null;
                renderGrid2();
                renderPositions2();
            }
        }

        function updatePromptPreview2() {
            const previewContainer = document.getElementById('prompt-preview-2');
            
            if (cameraState2.points.length === 0) {
                previewContainer.innerHTML = '<div class="empty-prompt"><p>Crie posições no grid para gerar o prompt</p></div>';
                return;
            }
            
            const prompt = generatePromptText2();
            previewContainer.textContent = prompt;
            previewContainer.scrollTop = previewContainer.scrollHeight;
        }

        function generatePromptText2() {
            let prompt = 'CAMERA_PATH_2 (GRID FIXO)\n';
            prompt += `Target: x=${cameraState2.target.x}, y=${cameraState2.target.y}  // busto humano central fixo\n`;
            
            if (cameraState2.points.length > 0) {
                const firstPoint = cameraState2.points[0];
                prompt += 'InitialPosition:\n';
                prompt += `  index=${firstPoint.idx}, t=${firstPoint.tempo_s}s, x=${firstPoint.x}, y=${firstPoint.y}, distancia_m=${firstPoint.distancia_m}, altura_m=${firstPoint.altura_m}, orientacao=${firstPoint.orientacao}\n`;
            }
            
            if (cameraState2.points.length > 1) {
                prompt += 'Positions:\n';
                cameraState2.points.slice(1).forEach(point => {
                    prompt += `  ${point.idx}: t=${point.tempo_s}s, x=${point.x}, y=${point.y}, distancia_m=${point.distancia_m}, altura_m=${point.altura_m}\n`;
                });
            }
            
            prompt += `Constraints: 0 = t1 < t2 < ... ≤ 8s; grid=fixo 800x800; unidades x,y em 0–100\n`;
            
            return prompt;
        }

        function exportPrompt2() {
            if (cameraState2.points.length === 0) {
                showExportError2('Não há posições para adicionar ao prompt.');
                return;
            }
            
            const prompt = generatePromptText2();
            const promptTextarea = document.querySelector('textarea[name="enhanced_prompt"], textarea[name="original_prompt"], #enhanced_prompt, #original_prompt');
            
            if (promptTextarea) {
                const currentContent = promptTextarea.value;
                const newContent = currentContent ? currentContent + '\n\n' + prompt : prompt;
                promptTextarea.value = newContent;
                
                promptTextarea.focus();
                promptTextarea.scrollTop = promptTextarea.scrollHeight;
                
                showSuccess2('Prompt da câmera 2 adicionado com sucesso!');
            } else {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(prompt).then(() => {
                        showSuccess2('Textarea principal não encontrado. Prompt copiado para a área de transferência!');
                    }).catch(() => {
                        copyToClipboardFallback2(prompt);
                    });
                } else {
                    copyToClipboardFallback2(prompt);
                }
            }
        }

        function copyToClipboardFallback2(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.top = '-9999px';
            textArea.style.left = '-9999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showSuccess2('Prompt copiado com sucesso via fallback');
                } else {
                    showExportError2('Falha ao copiar prompt. Tente novamente.');
                }
            } catch (err) {
                showExportError2('Erro ao copiar prompt: ' + err.message);
            } finally {
                document.body.removeChild(textArea);
            }
        }

        function handleMouseDown2(e) {
            const allowDrag = document.getElementById('allow-drag-toggle-2').checked;
            if (!allowDrag) return;
            
            const marker = e.target.closest('.camera-marker-2');
            if (!marker) return;
            
            e.preventDefault();
            cameraState2.isDragging = true;
            cameraState2.dragStartPos = {
                x: e.clientX,
                y: e.clientY,
                markerIndex: parseInt(marker.dataset.index)
            };
            
            marker.classList.add('dragging');
        }

        function handleMouseMove2(e) {
            if (!cameraState2.isDragging) return;
            
            const rect = e.target.getBoundingClientRect();
            const x = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
            const y = Math.max(0, Math.min(100, ((e.clientY - rect.top) / rect.height) * 100));
            
            const markerIndex = cameraState2.dragStartPos.markerIndex;
            const marker = document.querySelector(`.camera-marker-2[data-index="${markerIndex}"]`);
            
            if (marker) {
                marker.style.left = (x / 100 * 800) + 'px';
                marker.style.top = (y / 100 * 800) + 'px';
            }
        }

        function handleMouseUp2(e) {
            if (!cameraState2.isDragging) return;
            
            const rect = e.target.getBoundingClientRect();
            const x = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
            const y = Math.max(0, Math.min(100, ((e.clientY - rect.top) / rect.height) * 100));
            
            const markerIndex = cameraState2.dragStartPos.markerIndex;
            const point = cameraState2.points[markerIndex];
            
            if (point) {
                point.x = parseFloat(x.toFixed(2));
                point.y = parseFloat(y.toFixed(2));
            }
            
            cameraState2.isDragging = false;
            cameraState2.dragStartPos = null;
            
            const marker = document.querySelector(`.camera-marker-2[data-index="${markerIndex}"]`);
            if (marker) {
                marker.classList.remove('dragging');
            }
            
            renderGrid2();
            renderPositions2();
        }

        function showError2(message) {
            const existingMessages = document.querySelectorAll('.error-message, .success-message');
            existingMessages.forEach(msg => msg.remove());
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            
            const modalBody = document.querySelector('#position-modal-2 .modal-body');
            modalBody.insertBefore(errorDiv, modalBody.firstChild);
        }

        function showSuccess2(message) {
            const existingMessages = document.querySelectorAll('.error-message, .success-message');
            existingMessages.forEach(msg => msg.remove());
            
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.textContent = message;
            
            const container = document.querySelector('#tab-camera2 .camera-instructions');
            container.appendChild(successDiv);
            
            setTimeout(() => {
                successDiv.remove();
            }, 3000);
        }

        function showExportError2(message) {
            const existingMessages = document.querySelectorAll('.error-message, .success-message');
            existingMessages.forEach(msg => msg.remove());
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            
            const container = document.querySelector('#tab-camera2 .camera-instructions');
            container.appendChild(errorDiv);
            
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }

        // ================================================
        // SISTEMA VO3 - CÂMERA 2
        // ================================================

        // Estado global do sistema VO3
        const vo3State = {
            currentMode: 'simple', // 'simple' | 'advanced'
            currentStep: 1, // Para modo simples: 1, 2, 3
            
            // Dados do Modo Simples
            simple: {
                framing: '', // 'general', 'medium', 'bust', 'close'
                orientation: '', // 'FRONT', 'BACK', 'RIGHT', 'LEFT'
                movements: [], // Array de objetos: {type, intensity, seconds}
                totalDuration: 8 // segundos
            },
            
            // Dados do Modo Avançado
            advanced: {
                points: [], // Array de pontos: {id, t, x, y, height_m, distance_m, orientation?}
                selectedPointId: null,
                snapToGrid: true
            },
            
            // Cache dos resultados
            lastGenerated: {
                naturalText: '',
                vo3Json: ''
            }
        };

        // Inicialização do sistema VO3
        function initVO3System() {
            console.log('🎬 Inicializando Sistema VO3');
            
            initVO3Tabs();
            initVO3Stepper();
            initVO3SimpleMode();
            initVO3AdvancedMode();
            initVO3OutputPanel();
            initVO3Modals();
            
            // Verificar se a aba câmera2 existe
            const camera2Tab = document.getElementById('tab-camera2');
            if (camera2Tab) {
                console.log('✅ Aba Câmera 2 encontrada - Sistema VO3 carregado');
            } else {
                console.warn('⚠️ Aba Câmera 2 não encontrada');
            }
        }

        // Sistema de abas VO3 (Simples/Avançado)
        function initVO3Tabs() {
            const tabBtns = document.querySelectorAll('.vo3-tab-btn');
            const modeContents = document.querySelectorAll('.vo3-mode-content');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetMode = btn.getAttribute('data-vo3-tab');
                    switchVO3Mode(targetMode);
                });
            });
        }

        function switchVO3Mode(mode) {
            vo3State.currentMode = mode;
            
            // Atualizar botões das abas
            document.querySelectorAll('.vo3-tab-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.getAttribute('data-vo3-tab') === mode) {
                    btn.classList.add('active');
                }
            });
            
            // Atualizar conteúdo
            document.querySelectorAll('.vo3-mode-content').forEach(content => {
                content.classList.remove('active');
            });
            
            const targetContent = document.getElementById(`vo3-${mode}-mode`);
            if (targetContent) {
                targetContent.classList.add('active');
            }
            
            updateVO3GenerateButton();
        }

        // Sistema de stepper para modo simples
        function initVO3Stepper() {
            const steps = document.querySelectorAll('.step[data-step]');
            
            steps.forEach(step => {
                step.addEventListener('click', () => {
                    const stepNumber = parseInt(step.getAttribute('data-step'));
                    if (canAccessStep(stepNumber)) {
                        switchToStep(stepNumber);
                    }
                });
            });
        }

        function canAccessStep(stepNumber) {
            // Passo 1 sempre acessível
            if (stepNumber === 1) return true;
            
            // Passo 2 requer enquadramento e orientação
            if (stepNumber === 2) {
                return vo3State.simple.framing && vo3State.simple.orientation;
            }
            
            // Passo 3 requer pelo menos um movimento
            if (stepNumber === 3) {
                return vo3State.simple.movements.length > 0;
            }
            
            return false;
        }

        function switchToStep(stepNumber) {
            vo3State.currentStep = stepNumber;
            
            // Atualizar stepper visual
            document.querySelectorAll('.step').forEach(step => {
                const num = parseInt(step.getAttribute('data-step'));
                step.classList.toggle('active', num === stepNumber);
            });
            
            // Atualizar conteúdo dos passos
            document.querySelectorAll('.vo3-step-content').forEach(content => {
                content.classList.remove('active');
            });
            
            const targetContent = document.querySelector(`.vo3-step-content[data-step="${stepNumber}"]`);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        }

        // Modo Simples - Funcionalidades
        function initVO3SimpleMode() {
            initFramingSelection();
            initOrientationSelection();
            initMovementLibrary();
            initDurationSlider();
        }

        function initFramingSelection() {
            const framingCards = document.querySelectorAll('.framing-card');
            
            framingCards.forEach(card => {
                card.addEventListener('click', () => {
                    const framing = card.getAttribute('data-framing');
                    selectFraming(framing);
                });
            });
        }

        function selectFraming(framing) {
            vo3State.simple.framing = framing;
            
            // Atualizar visual
            document.querySelectorAll('.framing-card').forEach(card => {
                card.classList.remove('selected');
                if (card.getAttribute('data-framing') === framing) {
                    card.classList.add('selected');
                }
            });
            
            updateStepNavigation();
            updateSequencePreview();
        }

        function initOrientationSelection() {
            const orientationCards = document.querySelectorAll('.orientation-card');
            
            orientationCards.forEach(card => {
                card.addEventListener('click', () => {
                    const orientation = card.getAttribute('data-orientation');
                    selectOrientation(orientation);
                });
            });
        }

        function selectOrientation(orientation) {
            vo3State.simple.orientation = orientation;
            
            // Atualizar visual
            document.querySelectorAll('.orientation-card').forEach(card => {
                card.classList.remove('selected');
                if (card.getAttribute('data-orientation') === orientation) {
                    card.classList.add('selected');
                }
            });
            
            updateStepNavigation();
            updateSequencePreview();
        }

        function initMovementLibrary() {
            const movementCards = document.querySelectorAll('.movement-card');
            
            movementCards.forEach(card => {
                card.addEventListener('click', () => {
                    const movement = card.getAttribute('data-movement');
                    addMovement(movement);
                });
            });
        }

        function addMovement(movementType) {
            // Abrir modal para configurar tempo e intensidade
            openMovementModal(movementType);
        }

        function openMovementModal(movementType) {
            const movementNames = {
                'dolly_in': 'Dolly In',
                'dolly_out': 'Dolly Out',
                'truck_left': 'Truck Left',
                'truck_right': 'Truck Right',
                'pedestal_up': 'Pedestal Up',
                'pedestal_down': 'Pedestal Down',
                'pan': 'Pan',
                'tilt': 'Tilt',
                'arc_quarter': 'Arc ¼',
                'arc_half': 'Arc ½',
                'orbit_quarter': 'Orbit ¼',
                'orbit_half': 'Orbit ½'
            };

            const movementName = movementNames[movementType] || movementType;
            
            // Criar modal dinamicamente
            const modal = document.createElement('div');
            modal.className = 'position-modal';
            modal.style.display = 'block';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Configurar ${movementName}</h3>
                        <button class="modal-close" onclick="closeMovementModal()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="movement-duration">Duração (segundos)*</label>
                            <input type="number" id="movement-duration" min="0.5" max="4" step="0.1" value="2" required>
                            <small>Entre 0.5s e 4s</small>
                        </div>
                        <div class="form-group">
                            <label for="movement-intensity">Intensidade*</label>
                            <select id="movement-intensity" required>
                                <option value="light">Leve</option>
                                <option value="medium" selected>Média</option>
                                <option value="strong">Forte</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closeMovementModal()">Cancelar</button>
                        <button type="button" class="btn-primary" onclick="confirmAddMovement('${movementType}')">Adicionar</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Focar no campo de duração
            setTimeout(() => {
                document.getElementById('movement-duration').focus();
            }, 100);
        }

        function closeMovementModal() {
            const modal = document.querySelector('.position-modal:last-child');
            if (modal) {
                modal.remove();
            }
        }

        function confirmAddMovement(movementType) {
            const durationInput = document.getElementById('movement-duration');
            const intensitySelect = document.getElementById('movement-intensity');
            
            const duration = parseFloat(durationInput.value);
            const intensity = intensitySelect.value;
            
            // Validações
            if (isNaN(duration) || duration < 0.5 || duration > 4) {
                showVO3Error('Duração deve estar entre 0.5 e 4 segundos.');
                return;
            }
            
            // Verificar se não excede o tempo total
            const currentTotal = vo3State.simple.movements.reduce((sum, mov) => sum + mov.seconds, 0);
            if (currentTotal + duration > vo3State.simple.totalDuration) {
                showVO3Error(`Tempo total seria ${(currentTotal + duration).toFixed(1)}s, excedendo o limite de ${vo3State.simple.totalDuration}s. Ajuste a duração total primeiro.`);
                return;
            }
            
            const movement = {
                type: movementType,
                intensity: intensity,
                seconds: duration
            };
            
            vo3State.simple.movements.push(movement);
            updateSelectedMovements();
            updateStepNavigation();
            updateSequencePreview();
            updateVO3GenerateButton();
            
            closeMovementModal();
            showVO3Success(`Movimento adicionado: ${duration}s`);
        }

        function updateSelectedMovements() {
            const container = document.getElementById('selected-movements');
            if (!container) return;
            
            if (vo3State.simple.movements.length === 0) {
                container.innerHTML = '<div class="empty-movements"><p>Selecione movimentos acima para compor a sequência</p></div>';
                return;
            }
            
            const movementNames = {
                'dolly_in': 'Dolly In',
                'dolly_out': 'Dolly Out',
                'truck_left': 'Truck Left',
                'truck_right': 'Truck Right',
                'pedestal_up': 'Pedestal Up',
                'pedestal_down': 'Pedestal Down',
                'pan': 'Pan',
                'tilt': 'Tilt',
                'arc_quarter': 'Arc ¼',
                'arc_half': 'Arc ½',
                'orbit_quarter': 'Orbit ¼',
                'orbit_half': 'Orbit ½'
            };

            const intensityNames = {
                'light': 'Leve',
                'medium': 'Médio',
                'strong': 'Forte'
            };

            // Calcular tempo total usado
            const totalUsed = vo3State.simple.movements.reduce((sum, mov) => sum + mov.seconds, 0);
            
            const html = `
                <div class="movement-sequence">
                    <div style="margin-bottom: 1rem; padding: 0.5rem; background: rgba(59, 130, 246, 0.1); border-radius: 4px; font-size: 12px;">
                        <strong>Tempo usado: ${totalUsed.toFixed(1)}s / ${vo3State.simple.totalDuration}s</strong>
                        ${totalUsed > vo3State.simple.totalDuration ? ' <span style="color: #ef4444;">⚠️ Excedendo limite!</span>' : ''}
                    </div>
                    ${vo3State.simple.movements.map((mov, index) => `
                        <div class="movement-item">
                            <div style="flex: 1;">
                                <strong>${movementNames[mov.type] || mov.type}</strong>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                    ${mov.seconds}s • ${intensityNames[mov.intensity] || mov.intensity}
                                </div>
                            </div>
                            <button class="remove-btn" onclick="removeMovement(${index})" title="Remover movimento">×</button>
                        </div>
                    `).join('')}
                </div>
            `;
            
            container.innerHTML = html;
        }

        function removeMovement(index) {
            vo3State.simple.movements.splice(index, 1);
            updateSelectedMovements();
            updateStepNavigation();
            updateSequencePreview();
        }

        function initDurationSlider() {
            const slider = document.getElementById('duration-slider');
            const valueDisplay = document.getElementById('duration-value');
            
            if (slider && valueDisplay) {
                slider.addEventListener('input', (e) => {
                    const value = parseFloat(e.target.value);
                    vo3State.simple.totalDuration = value;
                    valueDisplay.textContent = value;
                    updateSequencePreview();
                });
            }
        }

        function updateStepNavigation() {
            // Atualizar qual step pode ser acessado
            const steps = document.querySelectorAll('.step');
            
            steps.forEach(step => {
                const stepNumber = parseInt(step.getAttribute('data-step'));
                const canAccess = canAccessStep(stepNumber);
                step.style.opacity = canAccess ? '1' : '0.5';
                step.style.cursor = canAccess ? 'pointer' : 'not-allowed';
            });
        }

        function updateSequencePreview() {
            const preview = document.getElementById('sequence-preview');
            if (!preview) return;
            
            if (!vo3State.simple.framing || !vo3State.simple.orientation || vo3State.simple.movements.length === 0) {
                preview.textContent = 'Configure enquadramento e movimentos para ver a prévia';
                return;
            }
            
            const framingNames = {
                'general': 'Plano Geral',
                'medium': 'Médio', 
                'bust': 'Busto',
                'close': 'Close'
            };
            
            const orientationNames = {
                'FRONT': 'Frente',
                'BACK': 'Costas',
                'RIGHT': 'Direita',
                'LEFT': 'Esquerda'
            };
            
            const movementNames = {
                'dolly_in': 'Dolly In',
                'dolly_out': 'Dolly Out',
                'truck_left': 'Truck Left',
                'truck_right': 'Truck Right',
                'pedestal_up': 'Pedestal Up',
                'pedestal_down': 'Pedestal Down',
                'pan': 'Pan',
                'tilt': 'Tilt',
                'arc_quarter': 'Arc ¼',
                'arc_half': 'Arc ½',
                'orbit_quarter': 'Orbit ¼',
                'orbit_half': 'Orbit ½'
            };

            const intensityNames = {
                'light': 'leve',
                'medium': 'médio',
                'strong': 'forte'
            };

            // Calcular tempo total real
            const totalRealTime = vo3State.simple.movements.reduce((sum, mov) => sum + mov.seconds, 0);
            
            let previewText = `CENA: estúdio neutro; sujeito estático.
Enquadramento inicial: ${framingNames[vo3State.simple.framing]}; Orientação: ${orientationNames[vo3State.simple.orientation]}.

MOVIMENTOS (dur=${totalRealTime.toFixed(1)}s):`;
            
            let currentTime = 0;
            
            vo3State.simple.movements.forEach((mov, index) => {
                const startTime = currentTime;
                currentTime += mov.seconds;
                previewText += `\n${index + 1}) ${startTime.toFixed(1)}–${currentTime.toFixed(1)}s: ${movementNames[mov.type]} ${intensityNames[mov.intensity]} (${mov.seconds}s).`;
            });

            // Adicionar aviso se houver discrepância
            if (Math.abs(totalRealTime - vo3State.simple.totalDuration) > 0.1) {
                previewText += `\n\n⚠️ AVISO: Tempo real dos movimentos (${totalRealTime.toFixed(1)}s) difere da duração configurada (${vo3State.simple.totalDuration}s).`;
            }
            
            preview.textContent = previewText;
        }

        // Modo Avançado - Funcionalidades
        function initVO3AdvancedMode() {
            initVO3Canvas();
            initVO3PointsList();
            initVO3AdvancedControls();
        }

        function initVO3Canvas() {
            const canvas = document.getElementById('vo3-grid-canvas');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            
            // Desenhar grid
            drawVO3Grid(ctx, canvas.width, canvas.height);
            
            // Event listeners
            canvas.addEventListener('click', handleVO3CanvasClick);
        }

        function drawVO3Grid(ctx, width, height) {
            ctx.clearRect(0, 0, width, height);
            
            // Grid lines
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
            ctx.lineWidth = 1;
            
            const gridSize = 40; // 20x20 grid
            
            for (let x = 0; x <= width; x += gridSize) {
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, height);
                ctx.stroke();
            }
            
            for (let y = 0; y <= height; y += gridSize) {
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(width, y);
                ctx.stroke();
            }
            
            // Desenhar pontos existentes
            drawVO3Points(ctx, width, height);
        }

        function drawVO3Points(ctx, width, height) {
            if (vo3State.advanced.points.length === 0) return;
            
            // Desenhar linhas conectoras
            if (vo3State.advanced.points.length > 1) {
                ctx.strokeStyle = 'rgba(59, 130, 246, 0.6)';
                ctx.lineWidth = 2;
                ctx.setLineDash([5, 5]);
                
                ctx.beginPath();
                for (let i = 0; i < vo3State.advanced.points.length; i++) {
                    const point = vo3State.advanced.points[i];
                    const canvasX = (point.x / 100) * width;
                    const canvasY = (point.y / 100) * height;
                    
                    if (i === 0) {
                        ctx.moveTo(canvasX, canvasY);
                    } else {
                        ctx.lineTo(canvasX, canvasY);
                    }
                }
                ctx.stroke();
                ctx.setLineDash([]);
            }
            
            // Desenhar pontos
            vo3State.advanced.points.forEach((point, index) => {
                const canvasX = (point.x / 100) * width;
                const canvasY = (point.y / 100) * height;
                
                // Círculo do ponto
                ctx.fillStyle = point.id === vo3State.advanced.selectedPointId ? '#3b82f6' : '#1e40af';
                ctx.beginPath();
                ctx.arc(canvasX, canvasY, 15, 0, 2 * Math.PI);
                ctx.fill();
                
                // Borda
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 2;
                ctx.stroke();
                
                // Número
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 12px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText((index + 1).toString(), canvasX, canvasY);
            });
        }

        function handleVO3CanvasClick(e) {
            const canvas = e.target;
            const rect = canvas.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            
            // Verificar se clicou em um ponto existente
            const clickedPoint = findPointAtPosition(x, y);
            if (clickedPoint) {
                selectVO3Point(clickedPoint.id);
                return;
            }
            
            // Verificar limites de tempo
            if (vo3State.advanced.points.length > 0) {
                const lastPoint = vo3State.advanced.points[vo3State.advanced.points.length - 1];
                if (lastPoint.t >= 8) {
                    showVO3Error('Não é possível criar mais pontos. O tempo máximo de 8s foi atingido.');
                    return;
                }
            }
            
            // Criar novo ponto
            createVO3Point(x, y);
        }

        function findPointAtPosition(x, y) {
            const threshold = 5; // 5% de tolerância
            return vo3State.advanced.points.find(point => {
                const distance = Math.sqrt(Math.pow(point.x - x, 2) + Math.pow(point.y - y, 2));
                return distance <= threshold;
            });
        }

        function createVO3Point(x, y) {
            const pointId = Date.now();
            const isFirstPoint = vo3State.advanced.points.length === 0;
            
            const point = {
                id: pointId,
                t: isFirstPoint ? 0 : null, // Será definido no modal
                x: parseFloat(x.toFixed(2)),
                y: parseFloat(y.toFixed(2)),
                height_m: null,
                distance_m: null,
                orientation: isFirstPoint ? null : undefined // Apenas primeiro ponto tem orientação
            };
            
            openVO3PointModal(point);
        }

        function selectVO3Point(pointId) {
            vo3State.advanced.selectedPointId = pointId;
            updateVO3PointsList();
            redrawVO3Canvas();
            
            // Abrir modal para edição
            const point = vo3State.advanced.points.find(p => p.id === pointId);
            if (point) {
                openVO3PointModal(point);
            }
        }

        function openVO3PointModal(point) {
            const modal = document.getElementById('vo3-point-modal');
            const titleEl = document.getElementById('vo3-point-modal-title');
            const orientationGroup = document.getElementById('vo3-orientation-group');
            const orientationSelect = document.getElementById('vo3-orientation');
            const timeInput = document.getElementById('vo3-time');
            const distanceInput = document.getElementById('vo3-distance');
            const heightInput = document.getElementById('vo3-height');
            
            if (!modal) return;
            
            // Configurar título
            const isNew = !vo3State.advanced.points.find(p => p.id === point.id);
            const pointIndex = isNew ? vo3State.advanced.points.length + 1 : vo3State.advanced.points.findIndex(p => p.id === point.id) + 1;
            titleEl.textContent = `Configurar Ponto ${pointIndex}`;
            
            // Configurar campos
            const isFirstPoint = pointIndex === 1;
            
            // Orientação (apenas primeiro ponto)
            if (orientationGroup) {
                orientationGroup.style.display = isFirstPoint ? 'block' : 'none';
                if (orientationSelect && isFirstPoint) {
                    orientationSelect.value = point.orientation || '';
                    orientationSelect.required = true;
                }
            }
            
            // Tempo
            if (timeInput) {
                if (isFirstPoint) {
                    timeInput.value = '0';
                    timeInput.readOnly = true;
                } else {
                    timeInput.value = point.t || '';
                    timeInput.readOnly = false;
                    
                    // Definir mínimo baseado no ponto anterior
                    const prevPoint = vo3State.advanced.points[pointIndex - 2];
                    if (prevPoint) {
                        timeInput.min = (prevPoint.t + 0.1).toFixed(1);
                    }
                }
            }
            
            // Distância e altura
            if (distanceInput) distanceInput.value = point.distance_m || '';
            if (heightInput) heightInput.value = point.height_m || '';
            
            // Armazenar ponto sendo editado
            modal.dataset.editingPointId = point.id;
            modal.dataset.editingPointX = point.x;
            modal.dataset.editingPointY = point.y;
            
            modal.style.display = 'block';
        }

        function initVO3PointsList() {
            // Lista será atualizada dinamicamente
            updateVO3PointsList();
        }

        function updateVO3PointsList() {
            const container = document.getElementById('vo3-points-list');
            if (!container) return;
            
            if (vo3State.advanced.points.length === 0) {
                container.innerHTML = '<div class="empty-points"><p>Clique no grid para criar pontos</p></div>';
                return;
            }
            
            const html = vo3State.advanced.points.map((point, index) => `
                <div class="vo3-point-item ${point.id === vo3State.advanced.selectedPointId ? 'selected' : ''}" 
                     onclick="selectVO3Point(${point.id})">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <strong>Ponto ${index + 1}</strong>
                        <span>t=${point.t}s</span>
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted);">
                        <div>Posição: x=${point.x}, y=${point.y}</div>
                        <div>Dist: ${point.distance_m}m, Alt: ${point.height_m}m</div>
                        ${point.orientation ? `<div>Orientação: ${point.orientation}</div>` : ''}
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = html;
        }

        function initVO3AdvancedControls() {
            const snapToggle = document.getElementById('vo3-snap-toggle');
            const clearBtn = document.getElementById('vo3-clear-points');
            
            if (snapToggle) {
                snapToggle.addEventListener('change', (e) => {
                    vo3State.advanced.snapToGrid = e.target.checked;
                });
            }
            
            if (clearBtn) {
                clearBtn.addEventListener('click', clearVO3Points);
            }
        }

        function clearVO3Points() {
            if (vo3State.advanced.points.length > 0) {
                if (confirm('Tem certeza que deseja limpar todos os pontos?')) {
                    vo3State.advanced.points = [];
                    vo3State.advanced.selectedPointId = null;
                    updateVO3PointsList();
                    redrawVO3Canvas();
                    updateVO3GenerateButton();
                }
            }
        }

        function redrawVO3Canvas() {
            const canvas = document.getElementById('vo3-grid-canvas');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                drawVO3Grid(ctx, canvas.width, canvas.height);
            }
        }

        // Painel de saída
        function initVO3OutputPanel() {
            const generateBtn = document.getElementById('vo3-generate-btn');
            const clearBtn = document.getElementById('vo3-clear-btn');
            const copyNaturalBtn = document.getElementById('vo3-copy-natural');
            const copyJsonBtn = document.getElementById('vo3-copy-json');
            const addJsonBtn = document.getElementById('vo3-add-json');
            
            if (generateBtn) generateBtn.addEventListener('click', generateVO3Output);
            if (clearBtn) clearBtn.addEventListener('click', clearVO3Everything);
            if (copyNaturalBtn) copyNaturalBtn.addEventListener('click', () => copyVO3ToClipboard('natural'));
            if (copyJsonBtn) copyJsonBtn.addEventListener('click', () => copyVO3ToClipboard('json'));
            if (addJsonBtn) addJsonBtn.addEventListener('click', addVO3ToPrompt);
        }

        function updateVO3GenerateButton() {
            const generateBtn = document.getElementById('vo3-generate-btn');
            if (!generateBtn) return;
            
            let canGenerate = false;
            
            if (vo3State.currentMode === 'simple') {
                canGenerate = vo3State.simple.framing && vo3State.simple.orientation && vo3State.simple.movements.length > 0;
            } else if (vo3State.currentMode === 'advanced') {
                canGenerate = vo3State.advanced.points.length > 0;
            }
            
            generateBtn.disabled = !canGenerate;
        }

        function generateVO3Output() {
            if (vo3State.currentMode === 'simple') {
                generateSimpleVO3();
            } else {
                generateAdvancedVO3();
            }
        }

        function generateSimpleVO3() {
            // Converter modo simples para pontos
            const points = convertSimpleToPoints();
            
            // Gerar saídas
            const naturalText = generateNaturalText(points, true);
            const vo3Json = generateVO3JSON(points);
            
            // Atualizar interface
            updateVO3Outputs(naturalText, vo3Json);
            
            vo3State.lastGenerated = { naturalText, vo3Json };
        }

        function generateAdvancedVO3() {
            // Validar pontos
            if (!validateAdvancedPoints()) return;
            
            // Gerar saídas
            const naturalText = generateNaturalText(vo3State.advanced.points, false);
            const vo3Json = generateVO3JSON(vo3State.advanced.points);
            
            // Atualizar interface
            updateVO3Outputs(naturalText, vo3Json);
            
            vo3State.lastGenerated = { naturalText, vo3Json };
        }

        function convertSimpleToPoints() {
            // Converter presets para pontos baseado no enquadramento e movimentos configurados
            const points = [];
            
            // Ponto inicial baseado no enquadramento
            const initialDistance = getDistanceFromFraming(vo3State.simple.framing);
            const initialPos = getPositionFromOrientation(vo3State.simple.orientation);
            
            points.push({
                id: 1,
                t: 0,
                x: initialPos.x,
                y: initialPos.y,
                height_m: 1.7, // Altura padrão
                distance_m: initialDistance,
                orientation: vo3State.simple.orientation
            });
            
            // Usar os tempos reais configurados para cada movimento
            let currentTime = 0;
            let currentX = initialPos.x;
            let currentY = initialPos.y;
            let currentDistance = initialDistance;
            let currentHeight = 1.7;
            let pointId = 2;
            
            vo3State.simple.movements.forEach((movement, index) => {
                // Para movimentos curvos (orbit e arc), gerar múltiplos pontos
                if (movement.type === 'orbit_quarter' || movement.type === 'orbit_half' || 
                    movement.type === 'arc_quarter' || movement.type === 'arc_half') {
                    const curvePoints = generateCurvePoints(movement, currentTime, currentX, currentY, currentDistance, currentHeight);
                    
                    curvePoints.forEach(curvePoint => {
                        points.push({
                            id: pointId++,
                            t: parseFloat(curvePoint.t.toFixed(2)),
                            x: parseFloat(curvePoint.x.toFixed(2)),
                            y: parseFloat(curvePoint.y.toFixed(2)),
                            height_m: parseFloat(curvePoint.height_m.toFixed(2)),
                            distance_m: parseFloat(curvePoint.distance_m.toFixed(2))
                        });
                    });
                    
                    // Atualizar posição atual para o último ponto da curva
                    const lastCurvePoint = curvePoints[curvePoints.length - 1];
                    currentTime = lastCurvePoint.t;
                    currentX = lastCurvePoint.x;
                    currentY = lastCurvePoint.y;
                } else {
                    // Movimento linear normal
                    currentTime += movement.seconds;
                    
                    // Aplicar transformação baseada no tipo e intensidade do movimento
                    const delta = applyMovementDelta(movement.type, movement.seconds, movement.intensity);
                    currentX = Math.max(0, Math.min(100, currentX + delta.x));
                    currentY = Math.max(0, Math.min(100, currentY + delta.y));
                    currentDistance = Math.max(0.5, currentDistance + delta.distance);
                    currentHeight = Math.max(0.5, currentHeight + delta.height);
                    
                    points.push({
                        id: pointId++,
                        t: parseFloat(currentTime.toFixed(2)),
                        x: parseFloat(currentX.toFixed(2)),
                        y: parseFloat(currentY.toFixed(2)),
                        height_m: parseFloat(currentHeight.toFixed(2)),
                        distance_m: parseFloat(currentDistance.toFixed(2))
                    });
                }
            });
            
            return points;
        }

        function getDistanceFromFraming(framing) {
            const distances = {
                'general': 5.0,
                'medium': 3.0,
                'bust': 2.0,
                'close': 1.5
            };
            return distances[framing] || 3.0;
        }

        function getPositionFromOrientation(orientation) {
            const positions = {
                'FRONT': { x: 50, y: 30 },
                'BACK': { x: 50, y: 70 },
                'RIGHT': { x: 30, y: 50 },
                'LEFT': { x: 70, y: 50 }
            };
            return positions[orientation] || { x: 50, y: 30 };
        }

        function applyMovementDelta(movementType, duration, intensity = 'medium') {
            // Para movimentos curvos (orbit e arc), usar lógica especial
            if (movementType === 'orbit_quarter' || movementType === 'orbit_half' || 
                movementType === 'arc_quarter' || movementType === 'arc_half') {
                return { x: 0, y: 0, distance: 0, height: 0 }; // Será tratado em convertSimpleToPoints
            }
            
            // Calcular multiplicador baseado na intensidade
            const intensityMultipliers = {
                'light': 0.6,
                'medium': 1.0,
                'strong': 1.5
            };
            
            const intensityFactor = intensityMultipliers[intensity] || 1.0;
            const timeFactor = duration / 2; // Normalizar por 2 segundos
            const factor = intensityFactor * timeFactor;
            
            const deltas = {
                'dolly_in': { x: 0, y: 0, distance: -0.8 * factor, height: 0 },
                'dolly_out': { x: 0, y: 0, distance: 0.8 * factor, height: 0 },
                'truck_left': { x: -12 * factor, y: 0, distance: 0, height: 0 },
                'truck_right': { x: 12 * factor, y: 0, distance: 0, height: 0 },
                'pedestal_up': { x: 0, y: 0, distance: 0, height: 0.4 * factor },
                'pedestal_down': { x: 0, y: 0, distance: 0, height: -0.4 * factor },
                'pan': { x: 18 * factor, y: 0, distance: 0, height: 0 },
                'tilt': { x: 0, y: 12 * factor, distance: 0, height: 0 }
            };
            
            return deltas[movementType] || { x: 0, y: 0, distance: 0, height: 0 };
        }

        function generateCurvePoints(movement, startTime, startX, startY, startDistance, startHeight) {
            const isOrbit = movement.type.startsWith('orbit');
            const isArc = movement.type.startsWith('arc');
            const isQuarter = movement.type.includes('quarter');
            const duration = movement.seconds;
            
            // Configurar ângulo total baseado no tipo
            let totalAngle;
            if (isOrbit) {
                totalAngle = isQuarter ? Math.PI / 2 : Math.PI; // 90° ou 180°
            } else if (isArc) {
                totalAngle = isQuarter ? Math.PI / 4 : Math.PI / 2; // 45° ou 90° (arco é menor que órbita)
            }
            
            // Raio baseado na intensidade e tipo de movimento
            const intensityMultipliers = {
                'light': isOrbit ? 15 : 10,
                'medium': isOrbit ? 20 : 15, 
                'strong': isOrbit ? 30 : 25
            };
            const radius = intensityMultipliers[movement.intensity] || (isOrbit ? 20 : 15);
            
            // Número de pontos intermediários baseado na duração
            const numPoints = Math.max(2, Math.floor(duration * 1.5)); // Mais pontos para movimento suave
            const points = [];
            
            // Para órbita: girar ao redor do centro (50,50)
            // Para arco: movimento mais direcional
            let centerX, centerY, initialAngle;
            
            if (isOrbit) {
                centerX = 50;
                centerY = 50;
                initialAngle = Math.atan2(startY - centerY, startX - centerX);
            } else {
                // Arc: movimento mais linear com curvatura
                centerX = startX;
                centerY = startY;
                initialAngle = 0; // Começar na direção horizontal
            }
            
            for (let i = 1; i <= numPoints; i++) {
                const progress = i / numPoints;
                let x, y;
                
                if (isOrbit) {
                    // Movimento circular ao redor do alvo
                    const angle = initialAngle + (totalAngle * progress);
                    x = centerX + radius * Math.cos(angle);
                    y = centerY + radius * Math.sin(angle);
                } else {
                    // Arc: movimento curvado mais suave
                    const angle = totalAngle * progress;
                    const forwardDistance = radius * progress;
                    const sidewaysDistance = radius * Math.sin(angle) * 0.5;
                    
                    x = startX + forwardDistance;
                    y = startY + sidewaysDistance;
                }
                
                points.push({
                    id: 'temp',
                    t: startTime + (duration * progress),
                    x: Math.max(5, Math.min(95, x)), // Manter dentro dos limites
                    y: Math.max(5, Math.min(95, y)),
                    height_m: startHeight,
                    distance_m: startDistance
                });
            }
            
            return points;
        }

        function validateAdvancedPoints() {
            if (vo3State.advanced.points.length === 0) {
                showVO3Error('Nenhum ponto foi criado.');
                return false;
            }
            
            // Verificar se todos os pontos têm dados completos
            for (let point of vo3State.advanced.points) {
                if (point.distance_m === null || point.height_m === null) {
                    showVO3Error('Todos os pontos devem ter distância e altura configuradas.');
                    return false;
                }
                
                if (point.t === null) {
                    showVO3Error('Todos os pontos devem ter tempo configurado.');
                    return false;
                }
            }
            
            // Verificar se primeiro ponto tem orientação
            const firstPoint = vo3State.advanced.points[0];
            if (!firstPoint.orientation) {
                showVO3Error('O primeiro ponto deve ter orientação configurada.');
                return false;
            }
            
            return true;
        }

        function generateNaturalText(points, isSimple) {
            const framingNames = {
                'general': 'Plano Geral',
                'medium': 'Médio',
                'bust': 'Busto', 
                'close': 'Close'
            };
            
            const orientationNames = {
                'FRONT': 'Frente',
                'BACK': 'Costas',
                'RIGHT': 'Direita',
                'LEFT': 'Esquerda'
            };
            
            let text = `CENA: estúdio neutro; sujeito estático. `;
            
            if (isSimple) {
                text += `Enquadramento inicial: ${framingNames[vo3State.simple.framing]}; `;
            } else {
                text += `Enquadramento inicial: baseado na distância; `;
            }
            
            const firstPoint = points[0];
            text += `Orientação: ${orientationNames[firstPoint.orientation]}.\n\n`;
            
            const maxTime = points[points.length - 1].t;
            text += `MOVIMENTOS (dur=${maxTime}s):\n`;
            
            for (let i = 1; i < points.length; i++) {
                const prevPoint = points[i - 1];
                const currentPoint = points[i];
                
                text += `${i}) ${prevPoint.t}–${currentPoint.t}s: Movimento de `;
                text += `(${prevPoint.x.toFixed(1)},${prevPoint.y.toFixed(1)}) para `;
                text += `(${currentPoint.x.toFixed(1)},${currentPoint.y.toFixed(1)}), `;
                text += `distância ${prevPoint.distance_m}m→${currentPoint.distance_m}m, `;
                text += `altura ${prevPoint.height_m}m→${currentPoint.height_m}m.\n`;
            }
            
            return text;
        }

        function generateVO3JSON(points) {
            const jsonData = {
                "type": "VO3_CAMERA_PATH",
                "language": "pt-BR", 
                "strict": true,
                
                "intent": "render_from_camera_pov_only",
                "do_not_render": ["camera", "tripod", "operator", "ui", "overlays"],
                
                "scene": {
                    "description": "Pessoa em ambiente controlado, permanece imóvel durante toda a sequência. Apenas respiração sutil permitida."
                },
                
                "composition": {
                    "look_at_target": true,
                    "frame_lock": getFrameLockFromFraming(),
                    "lens_mm": 35
                },
                
                "grid": {
                    "coords": { "x_range": [0, 100], "y_range": [0, 100] },
                    "target": { "x": 50.0, "y": 50.0 }
                },
                
                "interpolation": { 
                    "position": "linear", 
                    "height": "linear", 
                    "fps": 24 
                },
                
                "points": points.map(point => {
                    const jsonPoint = {
                        "id": point.id,
                        "t": parseFloat(point.t.toFixed(2)),
                        "x": parseFloat(point.x.toFixed(2)),
                        "y": parseFloat(point.y.toFixed(2)),
                        "height_m": parseFloat(point.height_m.toFixed(2)),
                        "distance_m": parseFloat(point.distance_m.toFixed(2))
                    };
                    
                    if (point.orientation) {
                        jsonPoint.orientation = point.orientation;
                    }
                    
                    return jsonPoint;
                })
            };
            
            return JSON.stringify(jsonData, null, 2);
        }

        function getFrameLockFromFraming() {
            const framingToLock = {
                'general': 'full_body',
                'medium': 'medium_shot', 
                'bust': 'bust',
                'close': 'close_up'
            };
            return framingToLock[vo3State.simple.framing] || 'full_body';
        }

        function updateVO3Outputs(naturalText, vo3Json) {
            const naturalOutput = document.getElementById('vo3-natural-output');
            const jsonOutput = document.getElementById('vo3-json-output');
            
            if (naturalOutput) naturalOutput.value = naturalText;
            if (jsonOutput) jsonOutput.value = vo3Json;
        }

        function clearVO3Everything() {
            if (confirm('Tem certeza que deseja limpar tudo?')) {
                // Reset state
                vo3State.simple = {
                    framing: '',
                    orientation: '',
                    movements: [],
                    totalDuration: 4
                };
                
                vo3State.advanced = {
                    points: [],
                    selectedPointId: null,
                    snapToGrid: true
                };
                
                vo3State.lastGenerated = {
                    naturalText: '',
                    vo3Json: ''
                };
                
                // Reset UI
                document.querySelectorAll('.framing-card, .orientation-card, .movement-card').forEach(card => {
                    card.classList.remove('selected');
                });
                
                updateSelectedMovements();
                updateVO3PointsList();
                redrawVO3Canvas();
                updateVO3Outputs('', '');
                updateVO3GenerateButton();
                
                // Reset stepper
                switchToStep(1);
            }
        }

        function copyVO3ToClipboard(type) {
            const text = type === 'natural' ? vo3State.lastGenerated.naturalText : vo3State.lastGenerated.vo3Json;
            
            if (!text) {
                showVO3Error('Nada para copiar. Gere o VO3 primeiro.');
                return;
            }
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    showVO3Success(type === 'natural' ? 'Resumo copiado!' : 'JSON VO3 copiado!');
                }).catch(() => {
                    fallbackCopyToClipboard(text, type);
                });
            } else {
                fallbackCopyToClipboard(text, type);
            }
        }

        function fallbackCopyToClipboard(text, type) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            
            try {
                document.execCommand('copy');
                showVO3Success(type === 'natural' ? 'Resumo copiado!' : 'JSON VO3 copiado!');
            } catch (err) {
                showVO3Error('Erro ao copiar para área de transferência.');
            }
            
            document.body.removeChild(textArea);
        }

        function addVO3ToPrompt() {
            if (!vo3State.lastGenerated.vo3Json) {
                showVO3Error('Nada para adicionar. Gere o VO3 primeiro.');
                return;
            }
            
            // Encontrar textarea principal do prompt
            const promptTextarea = document.querySelector('textarea[name="enhanced_prompt"], textarea[name="original_prompt"], #enhanced_prompt, #original_prompt');
            
            if (promptTextarea) {
                const currentContent = promptTextarea.value;
                const newContent = currentContent ? currentContent + '\n\n' + vo3State.lastGenerated.vo3Json : vo3State.lastGenerated.vo3Json;
                promptTextarea.value = newContent;
                
                // Focar e rolar para o final
                promptTextarea.focus();
                promptTextarea.scrollTop = promptTextarea.scrollHeight;
                
                showVO3Success('JSON VO3 adicionado ao prompt principal!');
            } else {
                // Fallback: copiar para área de transferência
                copyVO3ToClipboard('json');
                showVO3Error('Prompt principal não encontrado. JSON copiado para área de transferência.');
            }
        }

        // Modais
        function initVO3Modals() {
            initVO3PointModal();
            initVO3AddJsonModal();
        }

        function initVO3PointModal() {
            const modal = document.getElementById('vo3-point-modal');
            const closeBtn = document.getElementById('vo3-point-modal-close');
            const cancelBtn = document.getElementById('vo3-point-cancel');
            const saveBtn = document.getElementById('vo3-point-save');
            
            if (closeBtn) closeBtn.addEventListener('click', closeVO3PointModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeVO3PointModal);
            if (saveBtn) saveBtn.addEventListener('click', saveVO3Point);
            
            // Fechar clicando fora
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        closeVO3PointModal();
                    }
                });
            }
        }

        function closeVO3PointModal() {
            const modal = document.getElementById('vo3-point-modal');
            if (modal) {
                modal.style.display = 'none';
                delete modal.dataset.editingPointId;
                delete modal.dataset.editingPointX;
                delete modal.dataset.editingPointY;
            }
        }

        function saveVO3Point() {
            const modal = document.getElementById('vo3-point-modal');
            const orientationSelect = document.getElementById('vo3-orientation');
            const timeInput = document.getElementById('vo3-time');
            const distanceInput = document.getElementById('vo3-distance');
            const heightInput = document.getElementById('vo3-height');
            
            if (!modal) return;
            
            const pointId = parseInt(modal.dataset.editingPointId);
            const x = parseFloat(modal.dataset.editingPointX);
            const y = parseFloat(modal.dataset.editingPointY);
            
            // Validações
            const time = parseFloat(timeInput.value);
            const distance = parseFloat(distanceInput.value);
            const height = parseFloat(heightInput.value);
            const orientation = orientationSelect ? orientationSelect.value : '';
            
            if (isNaN(time) || time < 0 || time > 8) {
                showVO3Error('Tempo deve estar entre 0 e 8 segundos.');
                return;
            }
            
            if (isNaN(distance) || distance < 0) {
                showVO3Error('Distância deve ser um número positivo.');
                return;
            }
            
            if (isNaN(height) || height < 0) {
                showVO3Error('Altura deve ser um número positivo.');
                return;
            }
            
            // Verificar se é primeiro ponto e precisa de orientação
            const isFirstPoint = vo3State.advanced.points.length === 0;
            if (isFirstPoint && !orientation) {
                showVO3Error('O primeiro ponto deve ter orientação definida.');
                return;
            }
            
            // Verificar ordem de tempo
            if (!isFirstPoint) {
                const existingPoints = vo3State.advanced.points.filter(p => p.id !== pointId);
                const prevTime = existingPoints.length > 0 ? Math.max(...existingPoints.map(p => p.t)) : 0;
                if (time <= prevTime) {
                    showVO3Error(`Tempo deve ser maior que ${prevTime}s.`);
                    return;
                }
            }
            
            // Criar ou atualizar ponto
            const pointData = {
                id: pointId,
                t: time,
                x: x,
                y: y,
                height_m: height,
                distance_m: distance
            };
            
            if (isFirstPoint) {
                pointData.orientation = orientation;
            }
            
            // Verificar se é novo ou edição
            const existingIndex = vo3State.advanced.points.findIndex(p => p.id === pointId);
            if (existingIndex >= 0) {
                vo3State.advanced.points[existingIndex] = pointData;
            } else {
                vo3State.advanced.points.push(pointData);
                vo3State.advanced.points.sort((a, b) => a.t - b.t);
            }
            
            updateVO3PointsList();
            redrawVO3Canvas();
            updateVO3GenerateButton();
            closeVO3PointModal();
        }

        function initVO3AddJsonModal() {
            const modal = document.getElementById('vo3-add-json-modal');
            const addJsonBtn = document.getElementById('vo3-add-json');
            const closeBtn = document.getElementById('vo3-modal-close');
            const cancelBtn = document.getElementById('vo3-modal-cancel');
            const addBtn = document.getElementById('vo3-modal-add');
            
            if (addJsonBtn) {
                addJsonBtn.addEventListener('click', () => {
                    modal.style.display = 'block';
                });
            }
            
            if (closeBtn) closeBtn.addEventListener('click', () => modal.style.display = 'none');
            if (cancelBtn) cancelBtn.addEventListener('click', () => modal.style.display = 'none');
            if (addBtn) addBtn.addEventListener('click', processExternalVO3JSON);
            
            // Fechar clicando fora
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            }
        }

        function processExternalVO3JSON() {
            const textarea = document.getElementById('vo3-external-json');
            const modal = document.getElementById('vo3-add-json-modal');
            
            if (!textarea || !modal) return;
            
            const jsonText = textarea.value.trim();
            if (!jsonText) {
                showVO3Error('Cole um JSON VO3 válido.');
                return;
            }
            
            try {
                // Extrair JSON do texto (pode ter prefixo {VO3_JSON})
                const jsonStart = jsonText.indexOf('{');
                if (jsonStart === -1) {
                    throw new Error('JSON não encontrado');
                }
                
                const jsonOnly = jsonText.substring(jsonStart);
                const parsed = JSON.parse(jsonOnly);
                
                // Validar estrutura básica VO3
                if (parsed.type !== 'VO3_CAMERA_PATH') {
                    throw new Error('Tipo VO3_CAMERA_PATH não encontrado');
                }
                
                if (!parsed.points || !Array.isArray(parsed.points)) {
                    throw new Error('Array de pontos não encontrado');
                }
                
                // Adicionar ao prompt principal
                const promptTextarea = document.querySelector('textarea[name="enhanced_prompt"], textarea[name="original_prompt"], #enhanced_prompt, #original_prompt');
                
                if (promptTextarea) {
                    const currentContent = promptTextarea.value;
                    const newContent = currentContent ? currentContent + '\n\n' + jsonText : jsonText;
                    promptTextarea.value = newContent;
                    
                    promptTextarea.focus();
                    promptTextarea.scrollTop = promptTextarea.scrollHeight;
                    
                    showVO3Success('JSON VO3 externo adicionado ao prompt!');
                    modal.style.display = 'none';
                    textarea.value = '';
                } else {
                    throw new Error('Prompt principal não encontrado');
                }
                
            } catch (error) {
                showVO3Error('JSON inválido para VO3. Verifique campos obrigatórios.');
            }
        }

        // Utilitários de mensagens
        function showVO3Success(message) {
            const existingMessages = document.querySelectorAll('#tab-camera2 .success-message, #tab-camera2 .error-message');
            existingMessages.forEach(msg => msg.remove());
            
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.textContent = message;
            successDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #10b981;
                color: white;
                padding: 12px 20px;
                border-radius: 6px;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            `;
            
            document.body.appendChild(successDiv);
            
            setTimeout(() => {
                successDiv.remove();
            }, 3000);
        }

        function showVO3Error(message) {
            const existingMessages = document.querySelectorAll('#tab-camera2 .success-message, #tab-camera2 .error-message');
            existingMessages.forEach(msg => msg.remove());
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            errorDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #ef4444;
                color: white;
                padding: 12px 20px;
                border-radius: 6px;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            `;
            
            document.body.appendChild(errorDiv);
            
            setTimeout(() => {
                errorDiv.remove();
            }, 4000);
        }

        // ===== SISTEMA MODERNO DE GERENCIAMENTO DE AVATARES =====
        
        class AvatarManager {
            constructor() {
                this.avatars = [];
                this.selectedAvatars = new Set();
                this.bulkMode = false;
                this.searchQuery = '';
                this.currentFilter = 'meus';
                this.currentSort = 'recentes';
                this.init();
            }

            init() {
                this.bindEvents();
                this.loadSampleAvatars();
                this.updateView();
            }

            addAvatar(avatarData) {
                // Adicionar avatar à lista
                this.avatars.push(avatarData);
                
                // Atualizar interface
                this.renderAvatars();
                
                console.log('✅ Avatar adicionado ao gerenciador:', avatarData);
            }

            toggleFavorite(avatarId, event) {
                if (event) event.stopPropagation();
                
                const avatar = this.avatars.find(a => a.id === avatarId);
                if (avatar) {
                    avatar.isFavorite = !avatar.isFavorite;
                    this.renderAvatars();
                    
                    const action = avatar.isFavorite ? 'favoritado' : 'removido dos favoritos';
                    this.showNotification(`Avatar "${avatar.name}" ${action}!`);
                    console.log(`⭐ Avatar ${action}:`, avatar);
                }
            }

            toggleSelection(avatarId, event) {
                if (event) event.stopPropagation();
                
                if (this.selectedAvatars.has(avatarId)) {
                    this.selectedAvatars.delete(avatarId);
                } else {
                    this.selectedAvatars.add(avatarId);
                }
                
                this.renderAvatars();
                console.log('🔲 Seleção atualizada:', Array.from(this.selectedAvatars));
            }

            addToPrompt(avatarId) {
                const avatar = this.avatars.find(a => a.id === avatarId);
                if (avatar) {
                    // Aqui você pode implementar a lógica específica para adicionar ao prompt
                    this.showNotification(`Avatar "${avatar.name}" adicionado ao prompt!`);
                    console.log('➕ Avatar adicionado ao prompt:', avatar);
                }
            }

            editAvatar(avatarId) {
                const avatar = this.avatars.find(a => a.id === avatarId);
                if (avatar) {
                    // Limpar seleção atual
                    clearAvatarSelection();
                    
                    // Selecionar o tipo do avatar
                    const typeCard = document.querySelector(`[data-type="${avatar.type}"]`);
                    if (typeCard) {
                        typeCard.click();
                        
                        // Aguardar o formulário aparecer e preencher
                        setTimeout(() => {
                            this.fillForm(avatar);
                        }, 100);
                    }
                    
                    console.log('✏️ Editando avatar:', avatar);
                }
            }

            fillForm(avatar) {
                const form = document.getElementById(`form-${avatar.type}`);
                if (form) {
                    // Preencher todos os campos do formulário
                    Object.keys(avatar).forEach(key => {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input && avatar[key]) {
                            input.value = avatar[key];
                        }
                    });
                    
                    // Marcar para edição (alterar texto do botão)
                    const submitBtn = form.querySelector('.btn-primary');
                    if (submitBtn) {
                        submitBtn.textContent = 'Atualizar Avatar';
                        submitBtn.setAttribute('onclick', `updateAvatar('${avatar.type}', ${avatar.id})`);
                    }
                    
                    this.showNotification(`Dados do avatar "${avatar.name}" carregados para edição!`);
                }
            }

            deleteAvatar(avatarId) {
                // Fechar menu dropdown
                this.closeAllMenus();
                
                const avatar = this.avatars.find(a => a.id === avatarId);
                if (avatar && confirm(`Tem certeza que deseja excluir o avatar "${avatar.name}"?`)) {
                    // Remover o avatar da lista
                    this.avatars = this.avatars.filter(a => a.id !== avatarId);
                    
                    // Remover da seleção se estava selecionado
                    this.selectedAvatars.delete(avatarId);
                    
                    // Atualizar a visualização
                    this.renderAvatars();
                    
                    // Atualizar visibilidade das ações em massa
                    this.updateBulkActionsVisibility();
                    
                    this.showNotification(`Avatar "${avatar.name}" excluído com sucesso!`);
                    console.log('🗑️ Avatar excluído:', avatar);
                }
            }

            confirmDelete(avatarId) {
                const avatar = this.avatars.find(a => a.id === avatarId);
                if (avatar) {
                    // Fechar menus
                    document.querySelectorAll('.card-menu-dropdown').forEach(menu => {
                        menu.classList.remove('show');
                    });
                    
                    // Mostrar modal de confirmação
                    this.showDeleteModal(avatar);
                }
            }

            showDeleteModal(avatar) {
                const modal = document.getElementById('avatar-action-modal');
                const title = document.getElementById('modal-title-avatar');
                const name = document.getElementById('avatar-name-modal');
                const type = document.getElementById('avatar-type-modal');
                const description = document.getElementById('avatar-description-modal');
                const editBtn = document.getElementById('modal-edit-avatar');
                const deleteBtn = document.getElementById('modal-delete-avatar');

                title.innerHTML = '<i class="material-icons">warning</i> Excluir Avatar';
                name.textContent = avatar.name;
                type.textContent = avatar.type.charAt(0).toUpperCase() + avatar.type.slice(1);
                description.textContent = 'Esta ação não pode ser desfeita.';
                
                editBtn.style.display = 'none';
                deleteBtn.textContent = 'Confirmar Exclusão';
                deleteBtn.onclick = () => {
                    this.deleteAvatar(avatar.id);
                    modal.classList.remove('show');
                };
                
                modal.classList.add('show');
            }

            toggleAvatarMenu(avatarId, event) {
                // Prevenir propagação do evento para não interferir com outros cliques
                if (event) {
                    event.stopPropagation();
                }
                
                // Remover classe menu-open de todos os cards
                document.querySelectorAll('.avatar-button.menu-open').forEach(button => {
                    button.classList.remove('menu-open');
                });
                
                // Fechar outros menus
                document.querySelectorAll('.card-menu-dropdown').forEach(menu => {
                    if (menu.id !== `card-menu-list-${avatarId}`) {
                        menu.classList.remove('show');
                    }
                });
                
                // Toggle do menu atual
                const menu = document.getElementById(`card-menu-list-${avatarId}`);
                const avatarButton = document.querySelector(`[data-avatar-id="${avatarId}"]`);
                
                if (menu) {
                    const isShowing = menu.classList.contains('show');
                    menu.classList.toggle('show');
                    
                    // Adicionar/remover classe menu-open no card
                    if (avatarButton) {
                        if (isShowing) {
                            avatarButton.classList.remove('menu-open');
                        } else {
                            avatarButton.classList.add('menu-open');
                        }
                    }
                }
            }

            toggleBulkMode() {
                this.bulkMode = !this.bulkMode;
                
                if (!this.bulkMode) {
                    this.selectedAvatars.clear();
                }
                
                this.updateView();
                console.log('📋 Modo seleção em massa:', this.bulkMode ? 'ativado' : 'desativado');
            }

            selectAll() {
                this.selectedAvatars.clear();
                this.avatars.forEach(avatar => {
                    this.selectedAvatars.add(avatar.id);
                });
                this.renderAvatars();
                console.log('☑️ Todos os avatares selecionados');
            }

            bulkFavorite() {
                const count = this.selectedAvatars.size;
                this.selectedAvatars.forEach(avatarId => {
                    const avatar = this.avatars.find(a => a.id === avatarId);
                    if (avatar) {
                        avatar.isFavorite = true;
                    }
                });
                
                this.renderAvatars();
                this.showNotification(`${count} avatar(es) favoritado(s)!`);
                console.log(`⭐ ${count} avatares favoritados em massa`);
            }

            bulkAddToPrompt() {
                const count = this.selectedAvatars.size;
                const selectedAvatars = this.avatars.filter(a => this.selectedAvatars.has(a.id));
                
                // Aqui você pode implementar a lógica específica para adicionar múltiplos avatares ao prompt
                this.showNotification(`${count} avatar(es) adicionado(s) ao prompt!`);
                console.log('➕ Avatares adicionados ao prompt em massa:', selectedAvatars);
            }

            bulkDelete() {
                const count = this.selectedAvatars.size;
                const selectedAvatars = this.avatars.filter(a => this.selectedAvatars.has(a.id));
                const names = selectedAvatars.map(a => a.name).join(', ');
                
                if (confirm(`Tem certeza que deseja excluir ${count} avatar(es)?\n\nAvatares: ${names}`)) {
                    this.avatars = this.avatars.filter(a => !this.selectedAvatars.has(a.id));
                    this.selectedAvatars.clear();
                    this.renderAvatars();
                    
                    this.showNotification(`${count} avatar(es) excluído(s) com sucesso!`);
                    console.log(`🗑️ ${count} avatares excluídos em massa`);
                }
            }

            searchAvatars(query) {
                this.searchQuery = query.toLowerCase();
                this.renderAvatars();
                console.log('🔍 Buscando por:', query);
            }

            filterAvatars(type) {
                this.currentFilter = type;
                this.renderAvatars();
                console.log('🔽 Filtro aplicado:', type);
            }

            sortAvatars(sortBy) {
                this.currentSort = sortBy;
                this.renderAvatars();
                console.log('📊 Ordenação:', sortBy);
            }

            getFilteredAvatars() {
                let filteredAvatars = [...this.avatars];

                // Aplicar busca
                if (this.searchQuery) {
                    filteredAvatars = filteredAvatars.filter(avatar => 
                        avatar.name.toLowerCase().includes(this.searchQuery) ||
                        avatar.type.toLowerCase().includes(this.searchQuery)
                    );
                }

                // Aplicar filtros
                if (this.currentFilter === 'favoritos') {
                    filteredAvatars = filteredAvatars.filter(avatar => avatar.isFavorite);
                } else if (this.currentFilter === 'publicos') {
                    filteredAvatars = filteredAvatars.filter(avatar => avatar.isPublic);
                }
                // 'meus' mostra todos os avatares (padrão)

                // Aplicar ordenação
                switch (this.currentSort) {
                    case 'nome_az':
                        filteredAvatars.sort((a, b) => a.name.localeCompare(b.name));
                        break;
                    case 'ultimos_usados':
                        // Por enquanto, usar data de criação como proxy
                        filteredAvatars.sort((a, b) => new Date(b.created) - new Date(a.created));
                        break;
                    case 'tipos':
                        filteredAvatars.sort((a, b) => a.type.localeCompare(b.type));
                        break;
                    case 'recentes':
                    default:
                        filteredAvatars.sort((a, b) => b.id - a.id); // Mais recentes primeiro
                        break;
                }

                return filteredAvatars;
            }

            bindEvents() {
                // Menu dropdown principal
                const menuBtn = document.getElementById('avatars-menu-btn');
                const menuDropdown = document.getElementById('avatars-menu-dropdown');
                
                if (menuBtn && menuDropdown) {
                    menuBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        menuDropdown.classList.toggle('show');
                    });

                    // Fechar menu ao clicar fora
                    document.addEventListener('click', () => {
                        menuDropdown.classList.remove('show');
                    });
                }

                // Ações do menu
                document.getElementById('enable-bulk-select')?.addEventListener('click', () => {
                    this.toggleBulkMode();
                });

                document.getElementById('export-avatars')?.addEventListener('click', () => {
                    this.exportAvatars();
                });

                document.getElementById('import-avatars')?.addEventListener('click', () => {
                    this.importAvatars();
                });

                // Ações em massa
                document.getElementById('bulk-select-all')?.addEventListener('click', () => {
                    this.selectAll();
                });

                document.getElementById('bulk-favorite')?.addEventListener('click', () => {
                    this.bulkFavorite();
                });

                document.getElementById('bulk-add-to-prompt')?.addEventListener('click', () => {
                    this.bulkAddToPrompt();
                });

                document.getElementById('bulk-delete')?.addEventListener('click', () => {
                    this.bulkDelete();
                });

                // Modal de ações
                this.bindModalEvents();

                // Eventos de busca e filtros
                const searchInput = document.getElementById('avatar-search');
                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        this.searchAvatars(e.target.value);
                    });
                }

                const typeFilter = document.getElementById('avatar-type-filter');
                if (typeFilter) {
                    typeFilter.addEventListener('change', (e) => {
                        this.filterAvatars(e.target.value);
                    });
                }

                const sortSelect = document.getElementById('avatar-sort');
                if (sortSelect) {
                    sortSelect.addEventListener('change', (e) => {
                        this.sortAvatars(e.target.value);
                    });
                }

                const clearSearch = document.getElementById('clear-search');
                if (clearSearch) {
                    clearSearch.addEventListener('click', () => {
                        searchInput.value = '';
                        this.searchAvatars('');
                        clearSearch.style.display = 'none';
                    });
                }

                // Mostrar/ocultar botão de limpar busca
                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        const clearBtn = document.getElementById('clear-search');
                        if (clearBtn) {
                            clearBtn.style.display = e.target.value ? 'block' : 'none';
                        }
                    });
                }

                // Fechar menus de cards ao clicar fora
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.avatar-card-menu')) {
                        document.querySelectorAll('.card-menu-dropdown').forEach(menu => {
                            menu.classList.remove('show');
                        });
                        
                        // Remover classe menu-open de todos os cards
                        document.querySelectorAll('.avatar-button.menu-open').forEach(button => {
                            button.classList.remove('menu-open');
                        });
                    }
                });
            }

            bindModalEvents() {
                const modal = document.getElementById('avatar-action-modal');
                const closeBtn = document.getElementById('modal-close-avatar');
                const cancelBtn = document.getElementById('modal-cancel-avatar');
                const editBtn = document.getElementById('modal-edit-avatar');
                const deleteBtn = document.getElementById('modal-delete-avatar');

                // Fechar modal
                [closeBtn, cancelBtn].forEach(btn => {
                    btn?.addEventListener('click', () => {
                        this.closeModal();
                    });
                });

                // Fechar modal ao clicar no backdrop
                modal?.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        this.closeModal();
                    }
                });

                // Fechar modal com ESC
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && modal?.classList.contains('show')) {
                        this.closeModal();
                    }
                });

                // Ações do modal
                editBtn?.addEventListener('click', () => {
                    this.editAvatarFromModal();
                });

                deleteBtn?.addEventListener('click', () => {
                    this.deleteAvatarFromModal();
                });
            }

            loadSampleAvatars() {
                // Dados de exemplo - na implementação real, carregaria do backend
                this.avatars = [
                    {
                        id: 1,
                        name: "Guerreiro Mágico",
                        type: "humano",
                        description: "Um guerreiro poderoso com armadura brilhante e espada mágica",
                        isFavorite: true,
                        isPublic: true,
                        usedAt: "2024-01-20"
                    },
                    {
                        id: 2,
                        name: "Elfa Arqueira",
                        type: "fantastico",
                        description: "Arqueira élfica com orelhas pontudas e arco élfico encantado",
                        isFavorite: false,
                        isPublic: false,
                        usedAt: "2024-01-18"
                    },
                    {
                        id: 3,
                        name: "Robô Futurista",
                        type: "robos",
                        description: "Androide avançado com design metálico e LED azuis",
                        isFavorite: true,
                        isPublic: true,
                        usedAt: "2024-01-19"
                    },
                    {
                        id: 4,
                        name: "Feiticeira Sombria",
                        type: "fantastico",
                        description: "Maga poderosa vestida em robes escuros com cajado cristalino",
                        isFavorite: false,
                        isPublic: true,
                        usedAt: "2024-01-16"
                    },
                    {
                        id: 5,
                        name: "Rex Dálmata",
                        type: "animal",
                        description: "Cachorro dálmata com manchas pretas e olhos expressivos",
                        isFavorite: true,
                        isPublic: false,
                        usedAt: "2024-01-15"
                    },
                    {
                        id: 6,
                        name: "Zephyr X-42",
                        type: "extraterrestres",
                        description: "Alien de Proxima Centauri com pele azulada e três olhos",
                        isFavorite: false,
                        isPublic: true,
                        usedAt: "2024-01-14"
                    }
                ];
            }

            closeModal() {
                const modal = document.getElementById('avatar-action-modal');
                if (modal) {
                    modal.classList.remove('show');
                }
            }

            editAvatarFromModal() {
                // Este método seria chamado pelo modal, mas já temos editAvatar
                this.closeModal();
            }

            deleteAvatarFromModal() {
                // Este método seria chamado pelo modal, mas já temos deleteAvatar
                this.closeModal();
            }

            // Função removida - agora só temos lista

            updateBulkActionsVisibility() {
                const bulkActions = document.getElementById('avatars-bulk-actions');
                if (bulkActions) {
                    // Mostra as ações em massa se há avatares selecionados
                    bulkActions.style.display = this.selectedAvatars.size > 0 ? 'flex' : 'none';
                }
            }

            renderAvatars() {
                const emptyState = document.getElementById('avatars-empty-state');
                const listView = document.getElementById('avatars-list-view');
                const countDisplay = document.getElementById('avatars-count');

                const filteredAvatars = this.getFilteredAvatars();

                // Atualizar contador
                if (countDisplay) {
                    countDisplay.textContent = `${filteredAvatars.length} ${filteredAvatars.length === 1 ? 'avatar' : 'avatares'}`;
                }

                if (filteredAvatars.length === 0) {
                    emptyState.style.display = 'flex';
                    listView.style.display = 'none';
                    return;
                }

                emptyState.style.display = 'none';
                listView.style.display = 'flex';
                this.renderListView();
            }

            renderListView() {
                const container = document.getElementById('avatars-list-view');
                container.innerHTML = '';

                const filteredAvatars = this.getFilteredAvatars();
                filteredAvatars.forEach(avatar => {
                    const item = this.createAvatarListItem(avatar);
                    container.appendChild(item);
                });
            }


            createAvatarListItem(avatar) {
                const item = document.createElement('div');
                item.className = `avatar-button ${this.bulkMode ? 'bulk-mode' : ''} ${this.selectedAvatars.has(avatar.id) ? 'selected' : ''}`;
                item.dataset.avatarId = avatar.id;

                const typeIcons = {
                    humano: 'face',
                    animal: 'pets',
                    fantastico: 'auto_awesome',
                    extraterrestres: 'rocket_launch',
                    robos: 'smart_toy',
                    outros: 'more_horiz'
                };

                item.innerHTML = `
                    <div class="avatar-icon">
                        <i class="material-icons">${typeIcons[avatar.type] || 'person'}</i>
                    </div>
                    
                    <div class="avatar-name">${avatar.name || 'Nome do Avatar'}</div>
                    <div class="avatar-type">${avatar.type ? avatar.type.charAt(0).toUpperCase() + avatar.type.slice(1) : 'Tipo'}</div>
                    
                    <div class="action-buttons">
                        <button class="star-button ${avatar.isFavorite ? 'active' : ''}" onclick="avatarManager.toggleFavorite(${avatar.id}, event)" aria-label="Favoritar">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </button>
                        
                        <button class="add-button" onclick="avatarManager.addToPrompt(${avatar.id})" aria-label="Adicionar">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                            </svg>
                        </button>
                        
                        <div class="avatar-card-menu">
                            <button class="menu-trigger" onclick="avatarManager.toggleAvatarMenu(${avatar.id}, event)" aria-label="Mais opções">
                                <i class="material-icons">more_vert</i>
                            </button>
                            <div class="card-menu-dropdown" id="card-menu-list-${avatar.id}">
                                <button class="menu-item" onclick="avatarManager.editAvatar(${avatar.id})">
                                    <i class="material-icons">edit</i>
                                    Editar
                                </button>
                                <hr class="menu-divider">
                                <button class="menu-item danger" onclick="avatarManager.deleteAvatar(${avatar.id})">
                                    <i class="material-icons">delete</i>
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                // Adicionar evento de clique no item para seleção automática
                item.addEventListener('click', (e) => {
                    if (!e.target.closest('button')) {
                        this.toggleSelection(avatar.id, e);
                        this.updateBulkActionsVisibility();
                    }
                });

                // Adicionar evento de duplo clique para editar
                item.addEventListener('dblclick', (e) => {
                    if (!e.target.closest('button')) {
                        this.editAvatar(avatar.id);
                    }
                });

                return item;
            }

            toggleSelection(avatarId, event) {
                event.stopPropagation();
                
                if (this.selectedAvatars.has(avatarId)) {
                    this.selectedAvatars.delete(avatarId);
                } else {
                    this.selectedAvatars.add(avatarId);
                }
                
                this.updateSelectionUI();
            }

            updateSelectionUI() {
                const cards = document.querySelectorAll(`[data-avatar-id]`);
                cards.forEach(card => {
                    const avatarId = parseInt(card.dataset.avatarId);
                    card.classList.toggle('selected', this.selectedAvatars.has(avatarId));
                });
            }

            selectAllAvatars() {
                this.selectedAvatars.clear();
                this.avatars.forEach(avatar => {
                    this.selectedAvatars.add(avatar.id);
                });
                this.updateSelectionUI();
            }

            selectAvatar(avatarId) {
                const avatar = this.avatars.find(a => a.id === avatarId);
                if (avatar) {
                    console.log('Avatar selecionado:', avatar);
                    // Aqui você pode adicionar lógica para mostrar detalhes do avatar
                }
            }


            editAvatar(avatarId) {
                // Fechar menu dropdown
                this.closeAllMenus();
                
                const avatar = this.avatars.find(a => a.id === avatarId);
                if (avatar) {
                    console.log('Editando avatar:', avatar);
                    
                    // Limpar seleção atual no lado direito
                    clearAvatarSelection();
                    
                    // Selecionar o tipo do avatar no lado esquerdo
                    const typeCard = document.querySelector(`#tab-avatar .type-card[data-type="${avatar.type}"]`);
                    if (typeCard) {
                        // Simular clique no card do tipo
                        typeCard.click();
                        
                        // Aguardar o formulário aparecer e então preencher os campos
                        setTimeout(() => {
                            this.fillEditForm(avatar);
                        }, 100);
                    }
                    
                    this.showNotification(`Editando avatar "${avatar.name}"`);
                }
            }

            fillEditForm(avatar) {
                // Preencher o formulário com os dados do avatar
                const form = document.querySelector(`#tab-avatar .avatar-form.active`);
                if (!form) {
                    console.error('Formulário de avatar não encontrado');
                    return;
                }

                // Preencher campos comuns
                const inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    const fieldName = input.name || input.id;
                    if (avatar.hasOwnProperty(fieldName)) {
                        if (input.type === 'checkbox' || input.type === 'radio') {
                            input.checked = avatar[fieldName];
                        } else {
                            input.value = avatar[fieldName];
                        }
                    }
                });

                // Marcar que estamos editando
                form.dataset.editingId = avatar.id;
                
                console.log('Formulário preenchido para edição:', avatar);
            }

            confirmDelete(avatarId) {
                // Fechar menu dropdown
                this.closeAllMenus();
                
                const avatar = this.avatars.find(a => a.id === avatarId);
                if (avatar && confirm(`Tem certeza que deseja excluir "${avatar.name}"?`)) {
                    this.avatars = this.avatars.filter(a => a.id !== avatarId);
                    this.selectedAvatars.delete(avatarId);
                    this.renderAvatars();
                    this.showNotification('Avatar excluído com sucesso');
                }
            }

            closeAllMenus() {
                document.querySelectorAll('.card-menu-dropdown').forEach(menu => {
                    menu.classList.remove('show');
                });
                
                // Remover classe menu-open de todos os cards
                document.querySelectorAll('.avatar-button.menu-open').forEach(button => {
                    button.classList.remove('menu-open');
                });
            }


            addToPrompt(avatarId) {
                const avatar = this.avatars.find(a => a.id === avatarId);
                if (avatar) {
                    const promptText = `Avatar: ${avatar.name} - ${avatar.description}`;
                    
                    // Buscar textarea do prompt principal
                    const promptTextarea = document.querySelector('textarea[name="enhanced_prompt"], textarea[name="original_prompt"], #enhanced_prompt, #original_prompt');
                    
                    if (promptTextarea) {
                        const currentContent = promptTextarea.value;
                        const newContent = currentContent ? currentContent + '\n\n' + promptText : promptText;
                        promptTextarea.value = newContent;
                        
                        promptTextarea.focus();
                        promptTextarea.scrollTop = promptTextarea.scrollHeight;
                        
                        this.showNotification('Avatar adicionado ao prompt!');
                    } else {
                        navigator.clipboard.writeText(promptText).then(() => {
                            this.showNotification('Avatar copiado para área de transferência!');
                        }).catch(() => {
                            this.showNotification('Erro ao adicionar avatar ao prompt');
                        });
                    }
                }
            }

            bulkFavorite() {
                if (this.selectedAvatars.size === 0) {
                    this.showNotification('Selecione avatares para favoritar');
                    return;
                }

                this.selectedAvatars.forEach(avatarId => {
                    const avatar = this.avatars.find(a => a.id === avatarId);
                    if (avatar) avatar.isFavorite = true;
                });

                this.renderAvatars();
                this.showNotification(`${this.selectedAvatars.size} avatares favoritados`);
            }

            bulkAddToPrompt() {
                if (this.selectedAvatars.size === 0) {
                    this.showNotification('Selecione avatares para adicionar ao prompt');
                    return;
                }

                const selectedAvatarsList = this.avatars.filter(a => this.selectedAvatars.has(a.id));
                const promptText = selectedAvatarsList.map(avatar => 
                    `Avatar: ${avatar.name} - ${avatar.description}`
                ).join('\n');

                const promptTextarea = document.querySelector('textarea[name="enhanced_prompt"], textarea[name="original_prompt"], #enhanced_prompt, #original_prompt');
                
                if (promptTextarea) {
                    const currentContent = promptTextarea.value;
                    const newContent = currentContent ? currentContent + '\n\n' + promptText : promptText;
                    promptTextarea.value = newContent;
                    
                    promptTextarea.focus();
                    promptTextarea.scrollTop = promptTextarea.scrollHeight;
                    
                    this.showNotification(`${this.selectedAvatars.size} avatares adicionados ao prompt!`);
                } else {
                    navigator.clipboard.writeText(promptText).then(() => {
                        this.showNotification('Avatares copiados para área de transferência!');
                    });
                }
            }

            bulkDelete() {
                if (this.selectedAvatars.size === 0) {
                    this.showNotification('Selecione avatares para excluir');
                    return;
                }

                if (confirm(`Tem certeza que deseja excluir ${this.selectedAvatars.size} avatares selecionados?`)) {
                    this.avatars = this.avatars.filter(a => !this.selectedAvatars.has(a.id));
                    this.selectedAvatars.clear();
                    this.renderAvatars();
                    this.showNotification('Avatares excluídos com sucesso');
                }
            }

            exportAvatars() {
                const dataStr = JSON.stringify(this.avatars, null, 2);
                const dataBlob = new Blob([dataStr], {type: 'application/json'});
                
                const link = document.createElement('a');
                link.href = URL.createObjectURL(dataBlob);
                link.download = 'meus-avatares.json';
                link.click();
                
                this.showNotification('Avatares exportados com sucesso');
            }

            importAvatars() {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = '.json';
                
                input.onchange = (e) => {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            try {
                                const importedAvatars = JSON.parse(e.target.result);
                                if (Array.isArray(importedAvatars)) {
                                    this.avatars = [...this.avatars, ...importedAvatars];
                                    this.renderAvatars();
                                    this.showNotification(`${importedAvatars.length} avatares importados`);
                                }
                            } catch (error) {
                                this.showNotification('Erro ao importar avatares');
                            }
                        };
                        reader.readAsText(file);
                    }
                };
                
                input.click();
            }

            updateView() {
                this.renderAvatars();
            }

            // Gerenciar menu de card

            // Abrir modal de ações
            openActionModal(avatarId) {
                this.currentAvatarId = avatarId;
                const avatar = this.avatars.find(a => a.id === avatarId);
                
                if (!avatar) return;

                // Fechar menus de cards
                document.querySelectorAll('.card-menu-dropdown').forEach(menu => {
                    menu.classList.remove('show');
                });

                // Preencher dados do modal
                document.getElementById('avatar-name-modal').textContent = avatar.name;
                document.getElementById('avatar-type-modal').textContent = avatar.type.charAt(0).toUpperCase() + avatar.type.slice(1);
                document.getElementById('avatar-description-modal').textContent = avatar.description;

                // Mostrar modal
                const modal = document.getElementById('avatar-action-modal');
                modal.classList.add('show');
                
                // Foco no modal para acessibilidade
                setTimeout(() => {
                    document.getElementById('modal-edit-avatar')?.focus();
                }, 100);
            }

            // Fechar modal
            closeModal() {
                const modal = document.getElementById('avatar-action-modal');
                modal.classList.remove('show');
                this.currentAvatarId = null;
            }

            // Editar avatar via modal
            editAvatarFromModal() {
                if (this.currentAvatarId) {
                    this.editAvatar(this.currentAvatarId);
                    this.closeModal();
                }
            }

            // Excluir avatar via modal
            deleteAvatarFromModal() {
                if (this.currentAvatarId) {
                    const avatar = this.avatars.find(a => a.id === this.currentAvatarId);
                    if (avatar && confirm(`Tem certeza que deseja excluir "${avatar.name}"?`)) {
                        this.avatars = this.avatars.filter(a => a.id !== this.currentAvatarId);
                        this.selectedAvatars.delete(this.currentAvatarId);
                        this.renderAvatars();
                        this.showNotification('Avatar excluído com sucesso');
                        this.closeModal();
                    }
                }
            }

            showNotification(message, type = 'success') {
                const colors = {
                    success: '#10b981',
                    error: '#ef4444',
                    info: '#3b82f6',
                    warning: '#f59e0b'
                };

                const notification = document.createElement('div');
                notification.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: ${colors[type] || colors.success};
                    color: white;
                    padding: 12px 20px;
                    border-radius: 8px;
                    z-index: 10000;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                    animation: slideInRight 0.3s ease;
                    max-width: 400px;
                    word-wrap: break-word;
                `;
                notification.textContent = message;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }
        }

        // Inicializar o gerenciador de avatares quando a página carregar
        let avatarManager;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Aguardar um pouco para garantir que outros scripts carregaram
            setTimeout(() => {
                avatarManager = new AvatarManager();
            }, 500);
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