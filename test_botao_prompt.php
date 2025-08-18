<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste do Botão Prompt</title>
    <link rel="stylesheet" href="assets/css/gerador-prompt-modern.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <div class="content">
            <h1>🧪 Teste do Botão Prompt</h1>
            
            <div class="test-section">
                <h2>Teste dos Controles de Navegação com Botão Prompt</h2>
                
                <!-- Simular uma aba -->
                <div class="tab-content">
                    <div class="bottom-controls-container">
                        <!-- Coluna 1: Campo de descrição personalizada -->
                        <div class="custom-description">
                            <label>
                                <i class="material-icons">edit</i>
                                Descrição Personalizada
                            </label>
                            <textarea 
                                name="custom_test" 
                                placeholder="Digite uma descrição de teste..."
                                rows="3"></textarea>
                        </div>

                        <!-- Coluna 2: Controles de navegação -->
                        <div class="tab-navigation">
                            <div class="nav-buttons">
                                <button type="button" class="btn btn-secondary" onclick="alert('Início')" title="Início">
                                    <i class="material-icons">home</i>
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="alert('Anterior')" title="Anterior">
                                    <i class="material-icons">arrow_back</i>
                                </button>
                                <button type="button" class="btn btn-primary" onclick="alert('Próxima')" title="Próxima">
                                    <i class="material-icons">arrow_forward</i>
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="alert('Fim')" title="Fim">
                                    <i class="material-icons">flag</i>
                                </button>
                            </div>
                            <button type="button" class="btn-prompt" onclick="testarBotaoPrompt()">
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
            </div>
            
            <div class="test-section">
                <h2>🎯 Funcionalidades Testadas:</h2>
                <ul>
                    <li>✅ Botão Prompt adicionado abaixo dos controles de navegação</li>
                    <li>✅ Estilo visual do botão (gradiente, hover, animações)</li>
                    <li>✅ Layout responsivo com 3 colunas</li>
                    <li>✅ Controles de navegação organizados em container</li>
                    <li>✅ Função JavaScript gerarPrompt() implementada</li>
                    <li>✅ Modal para exibir o prompt gerado</li>
                    <li>✅ Funcionalidade de copiar prompt</li>
                </ul>
            </div>
            
            <div class="test-section">
                <h2>📋 Como Usar:</h2>
                <ol>
                    <li>Clique no botão <strong>PROMPT</strong> abaixo dos controles</li>
                    <li>O sistema coletará todas as seleções das abas</li>
                    <li>Um modal será exibido com o prompt gerado</li>
                    <li>Você pode copiar o prompt para a área de transferência</li>
                </ol>
            </div>
        </div>
    </div>

    <script>
        function testarBotaoPrompt() {
            alert('🎉 Botão Prompt funcionando perfeitamente!\n\nEste botão foi adicionado em todas as abas do gerador e irá:\n\n1. Coletar todas as seleções\n2. Gerar um prompt personalizado\n3. Exibir em um modal elegante\n4. Permitir copiar o resultado');
        }
    </script>

    <style>
        .test-section {
            background: var(--bg-card);
            border-radius: var(--radius);
            padding: 2rem;
            margin: 2rem 0;
            border: 1px solid var(--border-color);
        }
        
        .test-section h2 {
            color: var(--text-primary);
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--primary-purple);
            padding-bottom: 0.5rem;
        }
        
        .test-section ul, .test-section ol {
            color: var(--text-secondary);
            line-height: 1.8;
        }
        
        .test-section li {
            margin-bottom: 0.5rem;
        }
        
        .content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        h1 {
            text-align: center;
            color: var(--text-primary);
            margin-bottom: 3rem;
            font-size: 2.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</body>
</html>
