<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Carregar apenas Environment para velocidade
require_once 'includes/Environment.php';

// PromptManager carregado apenas se necessário
$promptManager = null;

// Processar salvamento de prompt apenas quando necessário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_prompt') {
    try {
        // Carregar PromptManager apenas quando saving
        require_once 'includes/PromptManager.php';
        $promptManager = new PromptManager();
        
        $result = $promptManager->saveUserPrompt($_SESSION['usuario_id'], [
            'title' => $_POST['title'] ?? 'Prompt sem título',
            'original_prompt' => $_POST['original_prompt'] ?? '',
            'enhanced_prompt' => $_POST['enhanced_prompt'] ?? '',
            'settings' => $_POST['settings'] ?? '{}',
            'environment' => $_POST['selected_environment'] ?? null,
            'lighting' => $_POST['selected_lighting'] ?? null,
            'character' => $_POST['selected_character'] ?? null
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

// Dados simplificados para evitar queries lentas
$categories = [];
$aspectRatios = [];
$userPrompts = [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Prompts IA v2.0 - <?= Environment::get('APP_NAME', 'Prompt Builder IA') ?></title>
    
    <!-- CSS otimizado inline -->
    <style>
        :root {
            --primary-color: #6366f1;
            --bg-secondary: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --radius-lg: 0.75rem;
        }
        
        * { box-sizing: border-box; }
        
        body {
            margin: 0;
            padding: 0;
            background: var(--bg-secondary);
            color: var(--text-primary);
            line-height: 1.6;
        }
        
        .main-container { min-height: 100vh; }
        
        .header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
        }
        
        .header-content {
            max-width: 1200px;
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
            color: var(--primary-color);
            font-weight: bold;
            font-size: 1.25rem;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .content-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .steps-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .steps-navigation {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
        }
        
        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            cursor: pointer;
        }
        
        .step-number {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .step-item.active .step-number {
            background: var(--primary-color);
            color: white;
        }
        
        .step-item.completed .step-number {
            background: #10b981;
            color: white;
        }
        
        .step-connector {
            width: 3rem;
            height: 2px;
            background: #e2e8f0;
        }
        
        .step-connector.completed {
            background: #10b981;
        }
        
        .content-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
        }
        
        .content-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .content-header h2 {
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .selection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .environment-card,
        .lighting-card,
        .character-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid var(--border-color);
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            text-align: center;
            position: relative;
        }
        
        .environment-card:hover,
        .lighting-card:hover,
        .character-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .environment-card.selected,
        .lighting-card.selected,
        .character-card.selected {
            border-color: var(--primary-color);
            background: rgba(99, 102, 241, 0.1);
        }
        
        .card-icon {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
        }
        
        .card-description {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin: 0;
        }
        
        .step-content {
            display: none;
        }
        
        .step-content.active {
            display: block;
        }
        
        .step-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-lg);
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: #5855eb;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            font-size: 1rem;
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .prompt-preview {
            background: #f8fafc;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .prompt-preview h3 {
            margin: 0 0 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .prompt-text {
            background: white;
            padding: 1rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            font-family: monospace;
            line-height: 1.5;
        }
        
        .alert {
            padding: 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-success {
            background: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
        }
        
        .alert-error {
            background: #fee2e2;
            border: 1px solid #ef4444;
            color: #991b1b;
        }
    </style>
    
    <!-- Font Awesome (versão local para velocidade) -->
    <style>
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
        
        /* Fonte rápida padrão */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-magic"></i>
                    <span><?= Environment::get('APP_NAME', 'Prompt Builder IA') ?></span>
                </a>
                
                <div class="user-menu">
                    <div class="user-info">
                        <i class="fas fa-user"></i>
                        <span><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?></span>
                    </div>
                    <a href="auth/logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Sair
                    </a>
                </div>
            </div>
        </header>

        <!-- Container Principal -->
        <div class="content-container">
            
            <!-- Cabeçalho das Etapas -->
            <div class="steps-header">
                <h1><i class="fas fa-wand-magic-sparkles"></i> Gerador de Prompts IA</h1>
                <p>Crie prompts profissionais para qualquer ferramenta de IA em etapas simples</p>
            </div>

            <!-- Mensagens -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <!-- Navegação das Etapas -->
            <div class="steps-navigation">
                <div class="step-item active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-title">Cena/Ambiente</div>
                </div>
                <div class="step-connector"></div>
                <div class="step-item" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-title">Iluminação</div>
                </div>
                <div class="step-connector"></div>
                <div class="step-item" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-title">Avatar/Personagem</div>
                </div>
                <div class="step-connector"></div>
                <div class="step-item" data-step="4">
                    <div class="step-number">4</div>
                    <div class="step-title">Seu Prompt</div>
                </div>
                <div class="step-connector"></div>
                <div class="step-item" data-step="5">
                    <div class="step-number">5</div>
                    <div class="step-title">Resultado</div>
                </div>
            </div>

            <!-- Form Principal -->
            <form id="promptForm" method="post">
                <input type="hidden" name="action" value="save_prompt">
                <input type="hidden" id="current_step" value="1">
                <input type="hidden" id="form_settings" name="settings">
                <input type="hidden" id="selected_environment" name="selected_environment">
                <input type="hidden" id="selected_lighting" name="selected_lighting">
                <input type="hidden" id="selected_character" name="selected_character">

                <!-- ETAPA 1: Cena/Ambiente -->
                <div class="step-content active" data-step="1">
                    <div class="content-card">
                        <div class="content-header">
                            <h2><i class="fas fa-globe"></i> Ambiente da Cena</h2>
                            <p>Configure o cenário e localização da sua criação</p>
                        </div>

                        <div class="environment-section">
                            <div class="environment-category">
                                <h3><i class="fas fa-tree"></i> Natureza</h3>
                                <div class="selection-grid">
                                    <div class="selection-card environment-card" data-environment="praia_tropical">
                                        <div class="card-icon" style="background: #06b6d4;">
                                            <i class="fas fa-umbrella-beach"></i>
                                        </div>
                                        <h4 class="card-title">Praia Tropical</h4>
                                        <p class="card-description">Paraíso com palmeiras e águas cristalinas</p>
                                    </div>
                                    
                                    <div class="selection-card environment-card" data-environment="cachoeira_gigante">
                                        <div class="card-icon" style="background: #0ea5e9;">
                                            <i class="fas fa-water"></i>
                                        </div>
                                        <h4 class="card-title">Cachoeira Gigante</h4>
                                        <p class="card-description">Queda d'água majestosa em penhasco</p>
                                    </div>
                                    
                                    <div class="selection-card environment-card" data-environment="montanha_nevada">
                                        <div class="card-icon" style="background: #64748b;">
                                            <i class="fas fa-mountain"></i>
                                        </div>
                                        <h4 class="card-title">Montanha Nevada</h4>
                                        <p class="card-description">Picos cobertos de neve eterna</p>
                                    </div>
                                    
                                    <div class="selection-card environment-card" data-environment="floresta_amazonica">
                                        <div class="card-icon" style="background: #16a34a;">
                                            <i class="fas fa-tree"></i>
                                        </div>
                                        <h4 class="card-title">Floresta Amazônica</h4>
                                        <p class="card-description">Selva densa com biodiversidade</p>
                                    </div>
                                    
                                    <div class="selection-card environment-card" data-environment="deserto_sahara">
                                        <div class="card-icon" style="background: #f59e0b;">
                                            <i class="fas fa-sun"></i>
                                        </div>
                                        <h4 class="card-title">Deserto do Sahara</h4>
                                        <p class="card-description">Dunas infinitas sob sol escaldante</p>
                                    </div>
                                    
                                    <div class="selection-card environment-card" data-environment="campo_lavanda">
                                        <div class="card-icon" style="background: #a855f7;">
                                            <i class="fas fa-seedling"></i>
                                        </div>
                                        <h4 class="card-title">Campo de Lavanda</h4>
                                        <p class="card-description">Ondas roxas aromáticas</p>
                                    </div>
                                    
                                    <div class="selection-card environment-card" data-environment="aurora_boreal">
                                        <div class="card-icon" style="background: #10b981;">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <h4 class="card-title">Aurora Boreal</h4>
                                        <p class="card-description">Luzes dançantes no céu polar</p>
                                    </div>
                                    
                                    <div class="selection-card environment-card" data-environment="vulcao_ativo">
                                        <div class="card-icon" style="background: #dc2626;">
                                            <i class="fas fa-fire"></i>
                                        </div>
                                        <h4 class="card-title">Vulcão Ativo</h4>
                                        <p class="card-description">Cratera com lava incandescente</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="step-navigation">
                            <div class="step-nav-left"></div>
                            <div class="step-nav-right">
                                <button type="button" class="btn btn-primary btn-lg" id="btn-next-1" disabled>
                                    <i class="fas fa-arrow-right"></i>
                                    Próxima Etapa
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ETAPA 2: Iluminação -->
                <div class="step-content" data-step="2">
                    <div class="content-card">
                        <div class="content-header">
                            <h2><i class="fas fa-lightbulb"></i> Iluminação</h2>
                            <p>Configure a iluminação da cena</p>
                        </div>

                        <div class="selection-grid">
                            <div class="selection-card lighting-card" data-lighting="natural">
                                <div class="card-icon" style="background: #f59e0b;">
                                    <i class="fas fa-sun"></i>
                                </div>
                                <h4 class="card-title">Luz Natural</h4>
                                <p class="card-description">Iluminação natural do dia</p>
                            </div>
                            
                            <div class="selection-card lighting-card" data-lighting="dourada">
                                <div class="card-icon" style="background: #f97316;">
                                    <i class="fas fa-sunset"></i>
                                </div>
                                <h4 class="card-title">Hora Dourada</h4>
                                <p class="card-description">Luz quente do pôr do sol</p>
                            </div>
                            
                            <div class="selection-card lighting-card" data-lighting="noturna">
                                <div class="card-icon" style="background: #1e293b;">
                                    <i class="fas fa-moon"></i>
                                </div>
                                <h4 class="card-title">Noturna</h4>
                                <p class="card-description">Iluminação noturna</p>
                            </div>
                            
                            <div class="selection-card lighting-card" data-lighting="neon">
                                <div class="card-icon" style="background: #a855f7;">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <h4 class="card-title">Neon</h4>
                                <p class="card-description">Luzes coloridas e vibrantes</p>
                            </div>
                            
                            <div class="selection-card lighting-card" data-lighting="dramatica">
                                <div class="card-icon" style="background: #374151;">
                                    <i class="fas fa-theater-masks"></i>
                                </div>
                                <h4 class="card-title">Dramática</h4>
                                <p class="card-description">Contraste alto e sombras</p>
                            </div>
                            
                            <div class="selection-card lighting-card" data-lighting="suave">
                                <div class="card-icon" style="background: #94a3b8;">
                                    <i class="fas fa-cloud"></i>
                                </div>
                                <h4 class="card-title">Suave</h4>
                                <p class="card-description">Luz difusa e suave</p>
                            </div>
                            
                            <div class="selection-card lighting-card" data-lighting="cinematic">
                                <div class="card-icon" style="background: #dc2626;">
                                    <i class="fas fa-film"></i>
                                </div>
                                <h4 class="card-title">Cinemática</h4>
                                <p class="card-description">Iluminação dramática de filme</p>
                            </div>
                            
                            <div class="selection-card lighting-card" data-lighting="magical">
                                <div class="card-icon" style="background: #10b981;">
                                    <i class="fas fa-magic"></i>
                                </div>
                                <h4 class="card-title">Mágica</h4>
                                <p class="card-description">Iluminação sobrenatural brilhante</p>
                            </div>
                        </div>

                        <div class="step-navigation">
                            <div class="step-nav-left">
                                <button type="button" class="btn btn-secondary btn-lg" onclick="previousStep()">
                                    <i class="fas fa-arrow-left"></i>
                                    Voltar
                                </button>
                            </div>
                            <div class="step-nav-right">
                                <button type="button" class="btn btn-secondary" onclick="skipStep()">
                                    Pular Etapa
                                </button>
                                <button type="button" class="btn btn-primary btn-lg" onclick="nextStep()">
                                    <i class="fas fa-arrow-right"></i>
                                    Próxima Etapa
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ETAPA 3: Avatar/Personagem -->
                <div class="step-content" data-step="3">
                    <div class="content-card">
                        <div class="content-header">
                            <h2><i class="fas fa-users"></i> Avatar/Personagem</h2>
                            <p>Defina os personagens e criaturas da sua criação</p>
                        </div>

                        <div class="character-section">
                            <div class="character-category">
                                <h3><i class="fas fa-user"></i> Humanos</h3>
                                <div class="selection-grid">
                                    <div class="selection-card character-card" data-character="homem_jovem">
                                        <div class="card-icon" style="background: #3b82f6;">
                                            <i class="fas fa-male"></i>
                                        </div>
                                        <h4 class="card-title">Homem Jovem</h4>
                                        <p class="card-description">Entre 18-30 anos, físico atlético</p>
                                    </div>
                                    
                                    <div class="selection-card character-card" data-character="mulher_jovem">
                                        <div class="card-icon" style="background: #ec4899;">
                                            <i class="fas fa-female"></i>
                                        </div>
                                        <h4 class="card-title">Mulher Jovem</h4>
                                        <p class="card-description">Entre 18-30 anos, elegante e moderna</p>
                                    </div>
                                    
                                    <div class="selection-card character-card" data-character="homem_maduro">
                                        <div class="card-icon" style="background: #374151;">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <h4 class="card-title">Homem Maduro</h4>
                                        <p class="card-description">Entre 40-60 anos, experiente e confiante</p>
                                    </div>
                                    
                                    <div class="selection-card character-card" data-character="mulher_madura">
                                        <div class="card-icon" style="background: #7c3aed;">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <h4 class="card-title">Mulher Madura</h4>
                                        <p class="card-description">Entre 40-60 anos, sofisticada e sábia</p>
                                    </div>
                                    
                                    <div class="selection-card character-card" data-character="crianca_menino">
                                        <div class="card-icon" style="background: #10b981;">
                                            <i class="fas fa-child"></i>
                                        </div>
                                        <h4 class="card-title">Criança Menino</h4>
                                        <p class="card-description">Entre 5-12 anos, brincalhão e curioso</p>
                                    </div>
                                    
                                    <div class="selection-card character-card" data-character="crianca_menina">
                                        <div class="card-icon" style="background: #f59e0b;">
                                            <i class="fas fa-baby"></i>
                                        </div>
                                        <h4 class="card-title">Criança Menina</h4>
                                        <p class="card-description">Entre 5-12 anos, alegre e expressiva</p>
                                    </div>
                                    
                                    <div class="selection-card character-card" data-character="artista">
                                        <div class="card-icon" style="background: #a855f7;">
                                            <i class="fas fa-palette"></i>
                                        </div>
                                        <h4 class="card-title">Artista</h4>
                                        <p class="card-description">Criativo com estilo boêmio</p>
                                    </div>
                                    
                                    <div class="selection-card character-card" data-character="atleta">
                                        <div class="card-icon" style="background: #dc2626;">
                                            <i class="fas fa-running"></i>
                                        </div>
                                        <h4 class="card-title">Atleta</h4>
                                        <p class="card-description">Físico musculoso e definido</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="step-navigation">
                            <div class="step-nav-left">
                                <button type="button" class="btn btn-secondary btn-lg" onclick="previousStep()">
                                    <i class="fas fa-arrow-left"></i>
                                    Voltar
                                </button>
                            </div>
                            <div class="step-nav-right">
                                <button type="button" class="btn btn-secondary" onclick="skipStep()">
                                    Pular Etapa
                                </button>
                                <button type="button" class="btn btn-primary btn-lg" onclick="nextStep()">
                                    <i class="fas fa-arrow-right"></i>
                                    Criar Prompt
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ETAPA 4: Seu Prompt -->
                <div class="step-content" data-step="4">
                    <div class="content-card">
                        <div class="content-header">
                            <h2><i class="fas fa-edit"></i> Descreva sua Ideia</h2>
                            <p>Agora descreva o que você quer criar. Seja específico e criativo!</p>
                        </div>

                        <div class="form-group">
                            <label for="original_prompt" class="form-label">
                                <i class="fas fa-lightbulb"></i>
                                Descreva sua ideia
                            </label>
                            <textarea 
                                id="original_prompt" 
                                name="original_prompt" 
                                class="form-textarea" 
                                placeholder="Ex: Um gato ninja saltando entre prédios em uma cidade cyberpunk..."
                                rows="5"
                                required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="prompt_title" class="form-label">
                                <i class="fas fa-tag"></i>
                                Título do Prompt (opcional)
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
                                <i class="fas fa-eye"></i>
                                Preview do Prompt Final
                            </h3>
                            <div id="prompt_preview" class="prompt-text">
                                Digite sua ideia acima para ver o preview...
                            </div>
                        </div>

                        <div class="step-navigation">
                            <div class="step-nav-left">
                                <button type="button" class="btn btn-secondary btn-lg" onclick="previousStep()">
                                    <i class="fas fa-arrow-left"></i>
                                    Voltar
                                </button>
                            </div>
                            <div class="step-nav-right">
                                <button type="button" class="btn btn-primary btn-lg" onclick="generateFinalPrompt()">
                                    <i class="fas fa-magic"></i>
                                    Gerar Prompt Final
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ETAPA 5: Resultado -->
                <div class="step-content" data-step="5">
                    <div class="content-card">
                        <div class="content-header">
                            <h2><i class="fas fa-star"></i> Seu Prompt Está Pronto!</h2>
                            <p>Copie e cole o prompt abaixo na sua ferramenta de IA</p>
                        </div>

                        <div class="prompt-preview">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <h3 style="margin: 0;">
                                    <i class="fas fa-clipboard"></i>
                                    Prompt Final
                                </h3>
                                <button type="button" class="btn btn-secondary" onclick="copyPrompt()">
                                    <i class="fas fa-copy"></i>
                                    Copiar
                                </button>
                            </div>
                            <div id="final_prompt" class="prompt-text">
                                <!-- Prompt final será inserido aqui -->
                            </div>
                            <input type="hidden" id="enhanced_prompt" name="enhanced_prompt">
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" name="is_favorite" style="margin-right: 0.5rem;">
                                <i class="fas fa-heart"></i>
                                Salvar como favorito
                            </label>
                        </div>

                        <div class="step-navigation">
                            <div class="step-nav-left">
                                <button type="button" class="btn btn-secondary btn-lg" onclick="previousStep()">
                                    <i class="fas fa-arrow-left"></i>
                                    Voltar
                                </button>
                            </div>
                            <div class="step-nav-right">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save"></i>
                                    Salvar Prompt
                                </button>
                                <button type="button" class="btn btn-primary btn-lg" onclick="startOver()">
                                    <i class="fas fa-plus"></i>
                                    Criar Outro
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>

            <!-- Histórico removido temporariamente para velocidade -->
        </div>
    </div>

    <!-- JavaScript otimizado inline -->
    <script>
        // JavaScript simplificado para velocidade
        class SimplePromptGenerator {
            constructor() {
                this.currentStep = 1;
                this.selectedData = {
                    environment: null,
                    lighting: null,
                    character: null
                };
                this.init();
            }
            
            init() {
                this.bindEvents();
            }
            
            bindEvents() {
                // Seleção de ambiente
                document.querySelectorAll('.environment-card').forEach(card => {
                    card.addEventListener('click', (e) => this.selectEnvironment(e.currentTarget));
                });
                
                // Seleção de iluminação
                document.querySelectorAll('.lighting-card').forEach(card => {
                    card.addEventListener('click', (e) => this.selectLighting(e.currentTarget));
                });
                
                // Seleção de personagem
                document.querySelectorAll('.character-card').forEach(card => {
                    card.addEventListener('click', (e) => this.selectCharacter(e.currentTarget));
                });
                
                // Navegação
                document.getElementById('btn-next-1')?.addEventListener('click', () => this.nextStep());
                
                // Preview
                document.getElementById('original_prompt')?.addEventListener('input', () => this.updatePreview());
            }
            
            selectEnvironment(card) {
                document.querySelectorAll('.environment-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                this.selectedData.environment = card.dataset.environment;
                document.getElementById('selected_environment').value = this.selectedData.environment;
                document.getElementById('btn-next-1').disabled = false;
                this.updatePreview();
            }
            
            selectLighting(card) {
                document.querySelectorAll('.lighting-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                this.selectedData.lighting = card.dataset.lighting;
                document.getElementById('selected_lighting').value = this.selectedData.lighting;
                this.updatePreview();
            }
            
            selectCharacter(card) {
                document.querySelectorAll('.character-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                this.selectedData.character = card.dataset.character;
                document.getElementById('selected_character').value = this.selectedData.character;
                this.updatePreview();
            }
            
            nextStep() {
                if (this.currentStep < 5) {
                    this.goToStep(this.currentStep + 1);
                }
            }
            
            goToStep(stepNumber) {
                document.querySelector(`.step-content[data-step="${this.currentStep}"]`)?.classList.remove('active');
                document.querySelector(`.step-content[data-step="${stepNumber}"]`)?.classList.add('active');
                
                // Atualizar navegação
                document.querySelectorAll('.step-item').forEach((item, index) => {
                    const step = index + 1;
                    item.classList.remove('active', 'completed');
                    if (step === stepNumber) item.classList.add('active');
                    else if (step < stepNumber) item.classList.add('completed');
                });
                
                this.currentStep = stepNumber;
                document.getElementById('current_step').value = stepNumber;
                
                if (stepNumber === 4) this.updatePreview();
            }
            
            updatePreview() {
                const originalPrompt = document.getElementById('original_prompt')?.value || '';
                if (!originalPrompt.trim()) {
                    document.getElementById('prompt_preview').textContent = 'Digite sua ideia acima para ver o preview...';
                    return;
                }
                
                let enhancedPrompt = originalPrompt;
                
                if (this.selectedData.character) {
                    enhancedPrompt = this.getCharacterPrompt(this.selectedData.character) + ' ' + enhancedPrompt;
                }
                
                if (this.selectedData.environment) {
                    enhancedPrompt = enhancedPrompt + ' in ' + this.getEnvironmentPrompt(this.selectedData.environment);
                }
                
                if (this.selectedData.lighting) {
                    enhancedPrompt = enhancedPrompt + ' with ' + this.getLightingPrompt(this.selectedData.lighting);
                }
                
                enhancedPrompt += ', highly detailed, professional quality';
                
                document.getElementById('prompt_preview').textContent = enhancedPrompt;
            }
            
            getEnvironmentPrompt(env) {
                const environments = {
                    'praia_tropical': 'tropical beach',
                    'cachoeira_gigante': 'giant waterfall',
                    'montanha_nevada': 'snowy mountain',
                    'floresta_amazonica': 'amazon forest',
                    'deserto_sahara': 'sahara desert',
                    'campo_lavanda': 'lavender field',
                    'aurora_boreal': 'northern lights',
                    'vulcao_ativo': 'active volcano'
                };
                return environments[env] || env;
            }
            
            getLightingPrompt(light) {
                const lighting = {
                    'natural': 'natural lighting',
                    'dourada': 'golden hour lighting',
                    'noturna': 'night lighting',
                    'neon': 'neon lighting',
                    'dramatica': 'dramatic lighting',
                    'suave': 'soft lighting',
                    'cinematic': 'cinematic lighting',
                    'magical': 'magical lighting'
                };
                return lighting[light] || light;
            }
            
            getCharacterPrompt(char) {
                const characters = {
                    'homem_jovem': 'young man',
                    'mulher_jovem': 'young woman',
                    'homem_maduro': 'mature man',
                    'mulher_madura': 'mature woman',
                    'crianca_menino': 'boy child',
                    'crianca_menina': 'girl child',
                    'artista': 'artist',
                    'atleta': 'athlete'
                };
                return characters[char] || char;
            }
            
            generateFinalPrompt() {
                this.updatePreview();
                const finalPrompt = document.getElementById('prompt_preview').textContent;
                document.getElementById('final_prompt').textContent = finalPrompt;
                document.getElementById('enhanced_prompt').value = finalPrompt;
                this.nextStep();
            }
            
            copyPrompt() {
                const promptText = document.getElementById('final_prompt').textContent;
                navigator.clipboard?.writeText(promptText);
                alert('Prompt copiado!');
            }
        }
        
        // Funções globais
        function nextStep() { window.promptGenerator.nextStep(); }
        function previousStep() { window.promptGenerator.goToStep(window.promptGenerator.currentStep - 1); }
        function skipStep() { window.promptGenerator.nextStep(); }
        function generateFinalPrompt() { window.promptGenerator.generateFinalPrompt(); }
        function copyPrompt() { window.promptGenerator.copyPrompt(); }
        function startOver() { location.reload(); }
        
        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            window.promptGenerator = new SimplePromptGenerator();
        });
    </script>
</body>
</html>