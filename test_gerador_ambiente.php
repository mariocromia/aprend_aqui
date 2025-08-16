<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste - Integração Ambiente Dinâmico</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 20px;
            background: #f8fafc;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .category-section {
            margin: 20px 0;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
        }
        .category-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .category-icon i {
            font-size: 1.5rem;
            color: #3b82f6;
        }
        .category-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }
        .subcategories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
        }
        .subcategory-card {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .subcategory-card:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        .subcategory-card.selected {
            border-color: #3b82f6;
            background: #dbeafe;
        }
        .subcategory-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .subcategory-desc {
            font-size: 0.875rem;
            color: #64748b;
        }
        .debug-info {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 0.875rem;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <h1>🧪 Teste - Integração Ambiente Dinâmico</h1>

    <div class="test-section">
        <h2>1. Status do Sistema</h2>
        <?php
        session_start();
        $_SESSION['usuario_id'] = 1; // Simular usuário logado

        try {
            require_once 'includes/CenaManager.php';
            require_once 'includes/CenaRendererPrompt.php';
            
            $cenaManager = new CenaManager();
            $cenaRenderer = new CenaRendererPrompt($cenaManager);
            
            echo "<p>✅ Sistema inicializado com sucesso</p>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
            $cenaRenderer = null;
        }
        ?>
    </div>

    <div class="test-section">
        <h2>2. Renderização de Ambiente Dinâmico</h2>
        <?php if ($cenaRenderer): ?>
            <div id="ambiente-dinamico">
                <?php echo $cenaRenderer->renderizarAbaAmbiente(); ?>
            </div>
        <?php else: ?>
            <p style="color: red;">Sistema não disponível</p>
        <?php endif; ?>
    </div>

    <div class="test-section">
        <h2>3. Dados JavaScript</h2>
        <div class="debug-info" id="js-data">Carregando...</div>
    </div>

    <div class="test-section">
        <h2>4. Seleção Atual</h2>
        <div class="debug-info" id="selecao-atual">Nenhuma seleção</div>
    </div>

    <?php if ($cenaRenderer): ?>
        <?php echo $cenaRenderer->gerarJavaScriptIntegracao(); ?>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar dados JavaScript carregados
            const jsDataElement = document.getElementById('js-data');
            if (window.cenasAmbienteData) {
                jsDataElement.textContent = JSON.stringify(window.cenasAmbienteData, null, 2);
                
                // Adicionar listeners para teste
                document.querySelectorAll('.subcategory-card').forEach(card => {
                    card.addEventListener('click', function() {
                        // Remover seleção anterior
                        document.querySelectorAll('.subcategory-card').forEach(c => c.classList.remove('selected'));
                        // Adicionar seleção atual
                        this.classList.add('selected');
                        
                        const valor = this.dataset.value;
                        const cenaData = window.cenasAmbienteData[valor];
                        
                        // Mostrar seleção atual
                        const selecaoElement = document.getElementById('selecao-atual');
                        if (cenaData) {
                            selecaoElement.textContent = `Valor: ${valor}\nTítulo: ${cenaData.titulo}\nBloco: ${cenaData.bloco}\nPrompt: ${cenaData.prompt}`;
                        } else {
                            selecaoElement.textContent = `Valor selecionado: ${valor} (dados não encontrados)`;
                        }
                    });
                });
            } else {
                jsDataElement.textContent = 'Nenhum dado JavaScript carregado';
            }
            
            // Testar alinhamento
            setTimeout(function() {
                const categoriesGrid = document.querySelector('.categories-grid');
                const alignmentStatus = document.getElementById('alignment-status');
                const blockCount = document.getElementById('block-count');
                const appliedClass = document.getElementById('applied-class');
                
                if (categoriesGrid) {
                    const blocks = categoriesGrid.querySelectorAll('.category-section');
                    const count = blocks.length;
                    
                    blockCount.textContent = count;
                    
                    if (categoriesGrid.classList.contains('few-blocks')) {
                        appliedClass.textContent = 'few-blocks (alinhado à esquerda)';
                        alignmentStatus.textContent = 'Funcionando - poucos blocos alinhados à esquerda';
                        alignmentStatus.style.color = 'green';
                    } else if (categoriesGrid.classList.contains('many-blocks')) {
                        appliedClass.textContent = 'many-blocks (distribuído igualmente)';
                        alignmentStatus.textContent = 'Funcionando - muitos blocos distribuídos';
                        alignmentStatus.style.color = 'blue';
                    } else {
                        appliedClass.textContent = 'Nenhuma classe aplicada';
                        alignmentStatus.textContent = 'Problema - nenhuma classe de alinhamento detectada';
                        alignmentStatus.style.color = 'red';
                    }
                } else {
                    alignmentStatus.textContent = 'Erro - grid não encontrado';
                    alignmentStatus.style.color = 'red';
                }
            }, 500);
        });
    </script>

    <div class="test-section">
        <h2>5. Teste de Alinhamento</h2>
        <p><strong>Status do Alinhamento:</strong> <span id="alignment-status">Verificando...</span></p>
        <p><strong>Quantidade de Blocos:</strong> <span id="block-count">0</span></p>
        <p><strong>Classe Aplicada:</strong> <span id="applied-class">Nenhuma</span></p>
    </div>

    <div class="test-section">
        <h2>6. Links de Navegação</h2>
        <p>
            <a href="admin-cards.php" target="_blank">🔧 Admin Cards</a> | 
            <a href="gerador_prompt_modern.php" target="_blank">🎯 Gerador de Prompt</a> | 
            <a href="inserir_dados_ambiente_teste.php" target="_blank">📥 Inserir Dados de Teste</a>
        </p>
    </div>
</body>
</html>