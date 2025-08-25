<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prompt Builder IA - CentroService</title>
    <link rel="stylesheet" href="assets/css/prompt-builder.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
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
        </div>
    </header>

    <main class="main-container">
        <section class="progress-compact">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: 16.67%"></div>
            </div>
            <div class="step-indicator">
                <span class="current-step">Etapa <span id="currentStepNumber">1</span> de 6</span>
                <span class="step-title" id="currentStepTitle">Tipo de Conteúdo</span>
            </div>
        </section>

        <div class="content-grid">
            <section class="options-column">
                <div class="step-content" id="stepContent">
                    <!-- Conteúdo será carregado aqui -->
                </div>
                
                <div class="navigation-compact">
                    <button class="btn-nav btn-prev" id="prevBtn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="nav-info">
                        <span id="navSteps">1 / 6</span>
                    </div>
                    <button class="btn-nav btn-next" id="nextBtn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </section>

            <section class="prompt-column">
                <div class="prompt-header-compact">
                    <h3>
                        <i class="fas fa-code"></i>
                        Prompt Gerado
                    </h3>
                </div>
                
                <div class="prompt-textarea-container">
                    <textarea 
                        id="promptText" 
                        placeholder="Seu prompt será construído conforme você faz suas escolhas..."
                        readonly
                    ></textarea>
                </div>
                
                <div class="prompt-actions-compact">
                    <button class="btn-action btn-copy" id="copyBtn" title="Copiar Prompt">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button class="btn-action btn-clear" id="clearBtn" title="Limpar Tudo">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </section>
        </div>
    </main>

    <script>
        // Implementação simples e funcional
        let currentStep = 1;
        let selectedChoices = {};

        const steps = {
            1: {
                title: 'Tipo de Conteúdo',
                description: 'Escolha o tipo de conteúdo que deseja gerar',
                icon: 'fas fa-image',
                options: [
                    { id: 'image', title: 'Imagem', description: 'Gerar imagem estática', icon: 'fas fa-image' },
                    { id: 'video', title: 'Vídeo', description: 'Gerar vídeo/animação', icon: 'fas fa-video' }
                ]
            },
            2: {
                title: 'Ambiente',
                description: 'Configure o cenário e localização da cena',
                icon: 'fas fa-globe',
                options: [
                    { id: 'natureza', title: 'Natureza', description: 'Ambientes naturais e paisagens', icon: 'fas fa-tree' },
                    { id: 'urbano', title: 'Urbano', description: 'Ambientes de cidade e construções', icon: 'fas fa-city' },
                    { id: 'interior', title: 'Interior', description: 'Ambientes fechados e construções internas', icon: 'fas fa-home' },
                    { id: 'fantasia', title: 'Fantasia', description: 'Ambientes mágicos e fantásticos', icon: 'fas fa-magic' }
                ]
            }
        };

        function loadStep(step) {
            const stepData = steps[step];
            if (!stepData) return;

            const stepContent = document.getElementById('stepContent');
            stepContent.innerHTML = `
                <h2 class="step-title">
                    <i class="${stepData.icon}"></i>
                    ${stepData.title}
                </h2>
                <p class="step-description">${stepData.description}</p>
                <div class="options-grid">
                    ${stepData.options.map(option => `
                        <div class="option-card" onclick="selectOption('${option.id}')" data-option="${option.id}">
                            <div class="option-icon">
                                <i class="${option.icon}"></i>
                            </div>
                            <div class="option-title">${option.title}</div>
                            <div class="option-description">${option.description}</div>
                        </div>
                    `).join('')}
                </div>
            `;

            // Atualizar indicadores
            document.getElementById('currentStepNumber').textContent = step;
            document.getElementById('currentStepTitle').textContent = stepData.title;
            document.getElementById('navSteps').textContent = `${step} / 6`;
            
            // Atualizar barra de progresso
            const progressFill = document.getElementById('progressFill');
            progressFill.style.width = `${(step / 6) * 100}%`;

            // Atualizar botões
            document.getElementById('prevBtn').disabled = step === 1;
            document.getElementById('nextBtn').disabled = step === 6;

            // Restaurar seleção
            if (selectedChoices[step]) {
                const selectedCard = document.querySelector(`[data-option="${selectedChoices[step]}"]`);
                if (selectedCard) {
                    selectedCard.classList.add('selected');
                }
            }
        }

        function selectOption(optionId) {
            // Remover seleção anterior
            document.querySelectorAll('.option-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Adicionar seleção atual
            const selectedCard = document.querySelector(`[data-option="${optionId}"]`);
            if (selectedCard) {
                selectedCard.classList.add('selected');
            }

            // Salvar escolha
            selectedChoices[currentStep] = optionId;

            // Atualizar prompt
            updatePrompt();

            console.log('Selected:', optionId, 'for step:', currentStep);
        }

        function updatePrompt() {
            let prompt = '';
            
            if (selectedChoices[1] === 'image') {
                prompt = 'Crie uma imagem';
            } else if (selectedChoices[1] === 'video') {
                prompt = 'Crie um vídeo';
            }

            if (selectedChoices[2]) {
                if (selectedChoices[2] === 'natureza') {
                    prompt += ' de um ambiente natural';
                } else if (selectedChoices[2] === 'urbano') {
                    prompt += ' de um ambiente urbano';
                } else if (selectedChoices[2] === 'interior') {
                    prompt += ' de um ambiente interno';
                } else if (selectedChoices[2] === 'fantasia') {
                    prompt += ' de um ambiente fantástico';
                }
            }

            document.getElementById('promptText').value = prompt;
        }

        // Event listeners para navegação
        document.getElementById('nextBtn').addEventListener('click', () => {
            if (currentStep < 6) {
                currentStep++;
                loadStep(currentStep);
            }
        });

        document.getElementById('prevBtn').addEventListener('click', () => {
            if (currentStep > 1) {
                currentStep--;
                loadStep(currentStep);
            }
        });

        // Event listeners para ações
        document.getElementById('copyBtn').addEventListener('click', () => {
            const promptText = document.getElementById('promptText').value;
            navigator.clipboard.writeText(promptText).then(() => {
                alert('Prompt copiado!');
            });
        });

        document.getElementById('clearBtn').addEventListener('click', () => {
            if (confirm('Limpar todas as seleções?')) {
                selectedChoices = {};
                currentStep = 1;
                loadStep(1);
                document.getElementById('promptText').value = '';
            }
        });

        // Inicializar quando o DOM carregar
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Inicializando Prompt Builder...');
            loadStep(1);
            console.log('Prompt Builder carregado!');
        });
    </script>
</body>
</html>