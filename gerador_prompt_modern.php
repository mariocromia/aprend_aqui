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
</head>
<body>
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

                        <div class="categories-grid">
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">settings</i>
                                    </div>
                                    <h3 class="category-title">Em desenvolvimento</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card">
                                        <div class="subcategory-title">Técnica será implementada em breve</div>
                                        <div class="subcategory-desc">Aguarde atualizações</div>
                                    </div>
                                </div>
                            </div>
                        </div>

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

                        <div class="categories-grid">
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <i class="material-icons">auto_awesome</i>
                                    </div>
                                    <h3 class="category-title">Em desenvolvimento</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card">
                                        <div class="subcategory-title">Elementos Especiais em breve</div>
                                        <div class="subcategory-desc">Aguarde atualizações</div>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                    <h3 class="category-title">Em desenvolvimento</h3>
                                </div>
                                <div class="subcategories-grid">
                                    <div class="subcategory-card">
                                        <div class="subcategory-title">Qualidade será implementada em breve</div>
                                        <div class="subcategory-desc">Aguarde atualizações</div>
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


                    <!-- ABA 7: AVATAR/PERSONAGEM -->
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