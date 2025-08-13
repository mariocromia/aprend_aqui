<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prompt Builder IA - CentroService</title>
    <meta name="description" content="Gerador inteligente de prompts para IA - Stable Diffusion, Midjourney, Flux, VEO/Opal e mais">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/prompt-builder.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header Simplificado -->
    <header class="header-simple">
        <div class="header-container">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
            <h1 class="app-title">
                <i class="fas fa-magic"></i>
                Prompt Builder IA
            </h1>
            <div class="header-actions">
                <button class="btn-help" id="helpBtn">
                    <i class="fas fa-question-circle"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Container Principal -->
    <main class="main-container">
        
        <!-- Área A: Progressão de Etapas -->
        <section class="progress-section">
            <div class="stepper" id="stepper">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Tipo</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Assunto</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Estilo</div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-number">4</div>
                    <div class="step-label">Iluminação</div>
                </div>
                <div class="step" data-step="5">
                    <div class="step-number">5</div>
                    <div class="step-label">Câmera</div>
                </div>
                <div class="step" data-step="6">
                    <div class="step-number">6</div>
                    <div class="step-label">Personagens</div>
                </div>
                <div class="step" data-step="7">
                    <div class="step-number">7</div>
                    <div class="step-label">Ambiente</div>
                </div>
                <div class="step" data-step="8">
                    <div class="step-number">8</div>
                    <div class="step-label">Qualidade</div>
                </div>
                <div class="step" data-step="9">
                    <div class="step-number">9</div>
                    <div class="step-label">Parâmetros</div>
                </div>
                <div class="step" data-step="10">
                    <div class="step-number">10</div>
                    <div class="step-label">Revisão</div>
                </div>
            </div>
        </section>

        <!-- Área B: Opções da Etapa Atual -->
        <section class="options-section">
            <div class="step-content" id="stepContent">
                <!-- Conteúdo será carregado dinamicamente via JavaScript -->
            </div>
            
            <div class="navigation-buttons">
                <button class="btn btn-secondary" id="prevBtn" disabled>
                    <i class="fas fa-chevron-left"></i>
                    Anterior
                </button>
                <button class="btn btn-primary" id="nextBtn">
                    Próximo
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </section>

        <!-- Área C: Campo do Prompt -->
        <section class="prompt-section">
            <div class="prompt-header">
                <h3>
                    <i class="fas fa-code"></i>
                    Prompt Gerado
                </h3>
                <div class="prompt-stats">
                    <span id="charCount">0 caracteres</span>
                    <span id="wordCount">0 palavras</span>
                    <span id="tokenEstimate">~0 tokens</span>
                </div>
            </div>
            
            <div class="prompt-container">
                <textarea 
                    id="promptText" 
                    placeholder="Seu prompt será gerado automaticamente conforme você faz suas escolhas..."
                    readonly
                ></textarea>
                
                <div class="prompt-actions">
                    <button class="btn btn-copy" id="copyBtn">
                        <i class="fas fa-copy"></i>
                        Copiar Prompt
                    </button>
                    <button class="btn btn-clear" id="clearBtn">
                        <i class="fas fa-trash"></i>
                        Limpar
                    </button>
                    <button class="btn btn-export" id="exportBtn">
                        <i class="fas fa-download"></i>
                        Exportar JSON
                    </button>
                </div>
            </div>
        </section>

    </main>

    <!-- Toast para notificações -->
    <div class="toast" id="toast">
        <div class="toast-content">
            <i class="toast-icon"></i>
            <span class="toast-message"></span>
        </div>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal" id="helpModal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Como usar o Prompt Builder IA</h3>
                <button class="modal-close" id="closeHelp">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="help-content">
                    <div class="help-section">
                        <h4><i class="fas fa-list-ol"></i> Etapas</h4>
                        <p>Siga as 10 etapas para construir seu prompt perfeito. Você pode navegar entre as etapas a qualquer momento.</p>
                    </div>
                    <div class="help-section">
                        <h4><i class="fas fa-mouse-pointer"></i> Seleções</h4>
                        <p>Clique nos cards para fazer suas escolhas. Você pode selecionar múltiplas opções ou usar campos livres.</p>
                    </div>
                    <div class="help-section">
                        <h4><i class="fas fa-code"></i> Prompt</h4>
                        <p>Seu prompt é gerado automaticamente. Use os botões para copiar, limpar ou exportar em JSON.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/prompt-builder.js"></script>
</body>
</html>