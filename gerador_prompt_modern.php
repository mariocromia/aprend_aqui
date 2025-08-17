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

                        <div class="bottom-controls-container">
                            <div class="custom-description">
                                <label>
                                    <i class="material-icons">edit</i>
                                    Descrição Personalizada da Técnica
                                </label>
                                <textarea 
                                    name="custom_technique" 
                                    placeholder="Descreva técnicas específicas..."
                                    rows="3"></textarea>
                            </div>

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

                        <div class="bottom-controls-container">
                            <div class="custom-description">
                                <label>
                                    <i class="material-icons">edit</i>
                                    Descrição Personalizada dos Elementos
                                </label>
                                <textarea 
                                    name="custom_special_elements" 
                                    placeholder="Descreva elementos especiais..."
                                    rows="3"></textarea>
                            </div>

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

                        <div class="categories-grid">
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">high_quality</i>
                                    </div>
                                    <h3 class="category-title">Qualidade da Imagem</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card" data-type="quality" data-value="ultra_hd">
                                        <div class="subcategory-title">Ultra HD</div>
                                        <div class="subcategory-desc">4K, 8K, máxima qualidade</div>
                                    </div>
                                    <div class="subcategory-card" data-type="quality" data-value="high_quality">
                                        <div class="subcategory-title">Alta Qualidade</div>
                                        <div class="subcategory-desc">HD, detalhes nítidos</div>
                                    </div>
                                    <div class="subcategory-card" data-type="quality" data-value="photorealistic">
                                        <div class="subcategory-title">Fotorrealista</div>
                                        <div class="subcategory-desc">Hiper-realismo</div>
                                    </div>
                                    <div class="subcategory-card" data-type="quality" data-value="artistic">
                                        <div class="subcategory-title">Artístico</div>
                                        <div class="subcategory-desc">Estilo mais estilizado</div>
                                    </div>
                                    <div class="subcategory-card" data-type="quality" data-value="cinematic">
                                        <div class="subcategory-title">Cinematográfico</div>
                                        <div class="subcategory-desc">Qualidade de filme</div>
                                    </div>
                                    <div class="subcategory-card" data-type="quality" data-value="professional">
                                        <div class="subcategory-title">Profissional</div>
                                        <div class="subcategory-desc">Qualidade de estúdio</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bottom-controls-container">
                            <div class="custom-description">
                                <label>
                                    <i class="material-icons">edit</i>
                                    Descrição Personalizada da Qualidade
                                </label>
                                <textarea 
                                    name="custom_quality" 
                                    placeholder="Descreva configurações de qualidade..."
                                    rows="3"></textarea>
                            </div>

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


                    <!-- ABA 7: AVATAR/PERSONAGEM - DESIGN COMPACTO -->
                    <div class="tab-content" id="tab-avatar">
                        <!-- Header Compacto -->
                        <div class="avatar-header-compact">
                            <div class="header-main">
                                <div class="header-info">
                                    <h2><i class="material-icons">auto_fix_high</i> Criador de Avatares</h2>
                                    <div class="header-stats">
                                        <span class="stat"><i class="material-icons">person</i> <span id="avatars-count">0</span> salvos</span>
                                    </div>
                                </div>
                                <div class="progress-compact">
                                    <div class="progress-bar-mini">
                                        <div class="progress-fill-mini" id="creation-progress"></div>
                                    </div>
                                    <span class="progress-text">Etapa <span id="current-step-text">1</span> de 4</span>
                                </div>
                            </div>
                        </div>

                        <!-- Layout Compacto em Duas Colunas -->
                        <div class="avatar-compact-container">
                            <!-- Coluna Principal: Formulário -->
                            <div class="main-form-panel">
                                
                                <!-- STEP 1: Informações Básicas -->
                                <div class="creation-step active" id="step-1">
                                    <div class="compact-card">
                                        <div class="compact-header">
                                            <h3><i class="material-icons">person_add</i> Informações Básicas</h3>
                                        </div>
                                        
                                        <div class="compact-content">
                                            <div class="form-row">
                                                <div class="input-compact">
                                                    <label for="avatar_name">Nome do Avatar</label>
                                                    <input type="text" id="avatar_name" name="avatar_name" placeholder="Digite o nome do avatar">
                                                </div>
                                            </div>
                                            
                                            <div class="form-row">
                                                <label class="section-label">Tipo de Ser</label>
                                                <div class="type-grid-compact">
                                                    <div class="type-option-compact" data-type="humano">
                                                        <i class="material-icons">person</i>
                                                        <span>Humano</span>
                                                    </div>
                                                    <div class="type-option-compact" data-type="animal">
                                                        <i class="material-icons">pets</i>
                                                        <span>Animal</span>
                                                    </div>
                                                    <div class="type-option-compact" data-type="criatura_fantastica">
                                                        <i class="material-icons">auto_fix_high</i>
                                                        <span>Fantasia</span>
                                                    </div>
                                                    <div class="type-option-compact" data-type="alien">
                                                        <i class="material-icons">emoji_nature</i>
                                                        <span>Alien</span>
                                                    </div>
                                                    <div class="type-option-compact" data-type="robo_android">
                                                        <i class="material-icons">smart_toy</i>
                                                        <span>Robô</span>
                                                    </div>
                                                    <div class="type-option-compact" data-type="elemental">
                                                        <i class="material-icons">whatshot</i>
                                                        <span>Elemental</span>
                                                    </div>
                                                </div>
                                                <input type="hidden" id="avatar_type" name="avatar_type">
                                            </div>
                                        </div>
                                        
                                        <div class="compact-actions">
                                            <button type="button" class="btn-compact btn-primary" onclick="nextCreationStep()">
                                                Continuar <i class="material-icons">arrow_forward</i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 2: Características Específicas -->
                                <div class="creation-step" id="step-2">
                                    <div class="compact-card">
                                        <div class="compact-header">
                                            <h3><i class="material-icons">palette</i> Características</h3>
                                        </div>
                                        
                                        <div class="compact-content">
                                            <div id="dynamic-characteristics">
                                                <div class="placeholder-content">
                                                    <i class="material-icons">touch_app</i>
                                                    <p>Selecione um tipo de ser na etapa anterior</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="compact-actions">
                                            <button type="button" class="btn-compact btn-secondary" onclick="prevCreationStep()">
                                                <i class="material-icons">arrow_back</i> Voltar
                                            </button>
                                            <button type="button" class="btn-compact btn-primary" onclick="nextCreationStep()">
                                                Continuar <i class="material-icons">arrow_forward</i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 3: Aparência e Estilo -->
                                <div class="creation-step" id="step-3">
                                    <div class="compact-card" id="appearance-section">
                                        <div class="compact-header">
                                            <h3><i class="material-icons">style</i> Aparência e Estilo</h3>
                                        </div>
                                        
                                        <div class="compact-content">
                                            <div class="form-grid-compact">
                                                <div class="input-compact">
                                                    <label for="clothing_style">Estilo de Vestimenta</label>
                                                    <select id="clothing_style" name="clothing_style">
                                                        <option value="">Selecione um estilo</option>
                                                        <option value="casual">Casual</option>
                                                        <option value="formal">Formal</option>
                                                        <option value="esportivo">Esportivo</option>
                                                        <option value="gotico">Gótico</option>
                                                        <option value="cyberpunk">Cyberpunk</option>
                                                        <option value="medieval">Medieval</option>
                                                        <option value="futurista">Futurista</option>
                                                        <option value="bohemio">Boêmio</option>
                                                        <option value="militar">Militar</option>
                                                        <option value="vintage">Vintage</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="input-compact">
                                                    <label for="accessories">Acessórios</label>
                                                    <textarea id="accessories" name="accessories" rows="2" placeholder="Ex: Óculos, joias, armas"></textarea>
                                                </div>
                                                
                                                <div class="input-compact">
                                                    <label for="distinctive_marks">Marcas Distintivas</label>
                                                    <textarea id="distinctive_marks" name="distinctive_marks" rows="2" placeholder="Ex: Cicatrizes, tatuagens"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="compact-actions">
                                            <button type="button" class="btn-compact btn-secondary" onclick="prevCreationStep()">
                                                <i class="material-icons">arrow_back</i> Voltar
                                            </button>
                                            <button type="button" class="btn-compact btn-primary" onclick="nextCreationStep()">
                                                Finalizar <i class="material-icons">check</i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 4: Finalização -->
                                <div class="creation-step" id="step-4">
                                    <div class="compact-card">
                                        <div class="compact-header">
                                            <h3><i class="material-icons">auto_awesome</i> Avatar Criado!</h3>
                                        </div>
                                        
                                        <div class="compact-content">
                                            <div class="prompt-result-compact">
                                                <div class="prompt-header-compact">
                                                    <h4>Prompt Gerado</h4>
                                                    <div class="prompt-actions-compact">
                                                        <button type="button" class="btn-icon-mini" onclick="generateAvatarPrompt()" title="Regenerar">
                                                            <i class="material-icons">refresh</i>
                                                        </button>
                                                        <button type="button" class="btn-icon-mini primary" onclick="copyAvatarPrompt()" title="Copiar">
                                                            <i class="material-icons">content_copy</i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="prompt-display-compact" id="avatar-prompt-display">
                                                    <div class="prompt-placeholder">
                                                        <i class="material-icons">auto_awesome</i>
                                                        <p>O prompt será gerado automaticamente</p>
                                                    </div>
                                                </div>
                                                <div class="prompt-stats-compact" id="prompt-stats">
                                                    <span class="stat-mini"><i class="material-icons">text_fields</i> <span id="character-count">0</span></span>
                                                    <span class="stat-mini"><i class="material-icons">article</i> <span id="word-count">0</span></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="compact-actions">
                                            <button type="button" class="btn-compact btn-secondary" onclick="prevCreationStep()">
                                                <i class="material-icons">arrow_back</i> Voltar
                                            </button>
                                            <button type="button" class="btn-compact btn-success" onclick="saveAvatar()">
                                                <i class="material-icons">save</i> Salvar
                                            </button>
                                            <button type="button" class="btn-compact btn-outline" onclick="resetCreation()">
                                                <i class="material-icons">refresh</i> Novo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar Compacta -->
                            <div class="sidebar-compact">
                                <!-- Preview Compacto -->
                                <div class="preview-compact">
                                    <div class="preview-header-compact">
                                        <h4><i class="material-icons">visibility</i> Preview</h4>
                                    </div>
                                    <div class="preview-info">
                                        <div class="avatar-icon-display">
                                            <i class="material-icons" id="avatar-icon-preview">person_outline</i>
                                        </div>
                                        <div class="avatar-info-compact">
                                            <div class="info-item">
                                                <strong>Nome:</strong> <span id="preview-name">-</span>
                                            </div>
                                            <div class="info-item">
                                                <strong>Tipo:</strong> <span id="preview-type">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Avatares Salvos Compacto -->
                                <div class="saved-compact">
                                    <div class="saved-header-compact">
                                        <h4><i class="material-icons">folder</i> Salvos</h4>
                                        <span class="count-badge" id="saved-count">0</span>
                                    </div>
                                    <div class="saved-list-compact" id="saved-avatars-list">
                                        <div class="avatar-item-compact placeholder">
                                            <i class="material-icons">add</i>
                                            <span>Nenhum avatar</span>
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

                            <!-- CAMPO DE PROMPT GERADO -->
                            <div class="form-section prompt-section">
                                <div class="section-header">
                                    <i class="material-icons">auto_awesome</i>
                                    <h3>Prompt Gerado</h3>
                                    <div class="prompt-actions">
                                        <button type="button" class="btn btn-secondary btn-icon" onclick="generateAvatarPrompt()" title="Regenerar Prompt">
                                            <i class="material-icons">refresh</i>
                                        </button>
                                        <button type="button" class="btn btn-primary btn-icon" onclick="copyAvatarPrompt()" title="Copiar Prompt">
                                            <i class="material-icons">content_copy</i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="prompt-container">
                                    <div class="prompt-display" id="avatar-prompt-display">
                                        <div class="prompt-placeholder">
                                            <i class="material-icons">auto_awesome</i>
                                            <p>Preencha os campos acima para gerar automaticamente o prompt do avatar</p>
                                        </div>
                                    </div>
                                    <div class="prompt-stats" id="prompt-stats">
                                        <span class="stat-item">
                                            <i class="material-icons">text_fields</i>
                                            <span id="character-count">0</span> caracteres
                                        </span>
                                        <span class="stat-item">
                                            <i class="material-icons">article</i>
                                            <span id="word-count">0</span> palavras
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- SEÇÃO DE APARÊNCIA E ESTILO -->
                            <div class="form-section" id="appearance-section">
                                <div class="section-header">
                                    <div class="section-title">
                                        <i class="material-icons">style</i>
                                        <h3>Aparência e Estilo</h3>
                                    </div>
                                </div>
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="clothing_style">Estilo de Vestimenta</label>
                                        <select id="clothing_style" name="clothing_style">
                                            <option value="">Selecione</option>
                                            <option value="casual">Casual</option>
                                            <option value="formal">Formal</option>
                                            <option value="esportivo">Esportivo</option>
                                            <option value="gotico">Gótico</option>
                                            <option value="cyberpunk">Cyberpunk</option>
                                            <option value="medieval">Medieval</option>
                                            <option value="futurista">Futurista</option>
                                            <option value="bohemio">Boêmio</option>
                                            <option value="militar">Militar</option>
                                            <option value="vintage">Vintage</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="accessories">Acessórios Especiais</label>
                                        <textarea id="accessories" name="accessories" rows="2" placeholder="Ex: Óculos, joias, armas, equipamentos"></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="distinctive_marks">Marcas Distintivas</label>
                                        <textarea id="distinctive_marks" name="distinctive_marks" rows="2" placeholder="Ex: Cicatrizes, tatuagens, marcas de nascença"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- BOTÕES DE AÇÃO -->
                            <div class="form-actions">
                                <button type="button" class="btn btn-primary" onclick="saveAvatar()">
                                    <i class="material-icons">save</i>
                                    Salvar Avatar
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="previewAvatar()">
                                    <i class="material-icons">visibility</i>
                                    Visualizar
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="clearAvatarForm()">
                                    <i class="material-icons">clear</i>
                                    Limpar Formulário
                                </button>
                            </div>
                        </div>

                        <!-- LISTA DE AVATARES SALVOS -->
                        <div class="saved-avatars-section">
                            <div class="section-header">
                                <i class="material-icons">folder</i>
                                <h3>Avatares Salvos</h3>
                            </div>
                            <div id="saved-avatars-list" class="avatars-grid">
                                <!-- Avatares salvos serão carregados aqui via JavaScript -->
                                <div class="avatar-card placeholder">
                                    <div class="avatar-preview">
                                        <i class="material-icons">add</i>
                                    </div>
                                    <div class="avatar-info">
                                        <div class="avatar-name">Criar Novo Avatar</div>
                                        <div class="avatar-type">Clique para começar</div>
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
                                <button type="button" class="btn btn-secondary" onclick="promptGenerator.previousTab()">
                                    <i class="material-icons">arrow_back</i>
                                </button>
                                <button type="button" class="btn btn-primary" onclick="promptGenerator.nextTab()">
                                    <i class="material-icons">arrow_forward</i>
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
    
    <?php
    // Adicionar JavaScript de integração com sistema dinâmico de cenas para ambiente, estilo visual e iluminação
    if ($cenaRenderer) {
        echo $cenaRenderer->gerarJavaScriptIntegracao(['ambiente', 'estilo_visual', 'iluminacao']);
    }
    ?>
</body>
</html>