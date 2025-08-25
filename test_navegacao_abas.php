<!DOCTYPE html>
<html>
<head>
    <title>Teste de Navegação entre Abas</title>
    <style>
        .tab-button { padding: 10px; margin: 5px; border: 1px solid #ccc; background: #f5f5f5; cursor: pointer; }
        .tab-button.active { background: #007bff; color: white; }
        .tab-content { display: none; padding: 20px; border: 1px solid #ccc; margin-top: 10px; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <h1>🧪 Teste de Navegação entre Abas</h1>
    
    <div class="tabs-nav">
        <button class="tab-button active" data-tab="ambiente">Cena/Ambiente</button>
        <button class="tab-button" data-tab="estilo_visual">Estilo Visual</button>
        <button class="tab-button" data-tab="iluminacao">Iluminação</button>
        <button class="tab-button" data-tab="avatar">Avatar</button>
    </div>
    
    <div class="tab-content active" id="tab-ambiente">
        <h2>🏞️ Aba Ambiente</h2>
        <p>Conteúdo da aba ambiente</p>
    </div>
    
    <div class="tab-content" id="tab-estilo_visual">
        <h2>🎨 Aba Estilo Visual</h2>
        <p>Conteúdo da aba estilo visual</p>
    </div>
    
    <div class="tab-content" id="tab-iluminacao">
        <h2>💡 Aba Iluminação</h2>
        <p>Conteúdo da aba iluminação</p>
    </div>
    
    <div class="tab-content" id="tab-avatar">
        <h2>👤 Aba Avatar</h2>
        <p>Conteúdo da aba avatar</p>
    </div>

    <script>
        // Simulação da classe ModernPromptGenerator com as correções
        class TestPromptGenerator {
            constructor() {
                this.currentTab = 0;
                this.tabs = ['ambiente', 'estilo_visual', 'iluminacao', 'avatar']; // Array corrigido
                this.selections = {
                    environment: null,
                    visual_style: null, // Adicionado
                    lighting: null,
                    character: null
                };
                
                this.bindEvents();
            }
            
            bindEvents() {
                document.querySelectorAll('.tab-button').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const tabName = e.currentTarget.dataset.tab;
                        console.log('Clicou na aba:', tabName);
                        this.showTab(tabName);
                    });
                });
            }
            
            showTab(tabName) {
                console.log('Tentando mostrar aba:', tabName);
                console.log('Abas disponíveis:', this.tabs);
                
                const tabIndex = this.tabs.indexOf(tabName);
                console.log('Índice da aba:', tabIndex);
                
                if (tabIndex === -1) {
                    console.error('Aba não encontrada no array:', tabName);
                    return;
                }

                this.currentTab = tabIndex;

                // Update tab buttons
                document.querySelectorAll('.tab-button').forEach((btn, index) => {
                    btn.classList.toggle('active', index === tabIndex);
                });

                // Update tab contents
                document.querySelectorAll('.tab-content').forEach((content, index) => {
                    content.classList.toggle('active', index === tabIndex);
                });
                
                console.log('Aba ativada com sucesso:', tabName);
            }
        }
        
        // Inicializar teste
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Inicializando teste de navegação...');
            window.testGenerator = new TestPromptGenerator();
            
            // Teste automático
            setTimeout(() => {
                console.log('\n=== TESTE AUTOMÁTICO ===');
                console.log('Testando navegação para estilo_visual...');
                window.testGenerator.showTab('estilo_visual');
                
                setTimeout(() => {
                    console.log('Testando navegação para iluminacao...');
                    window.testGenerator.showTab('iluminacao');
                }, 2000);
            }, 1000);
        });
    </script>
    
    <div style="margin-top: 30px; padding: 20px; background: #f0f9ff; border-radius: 8px;">
        <h3>🔍 Status do Teste</h3>
        <p><strong>Objetivo:</strong> Verificar se a navegação entre abas funciona corretamente</p>
        <p><strong>Abas testadas:</strong></p>
        <ul>
            <li>✅ Ambiente</li>
            <li>🎨 Estilo Visual (nova aba)</li>
            <li>💡 Iluminação</li>
            <li>👤 Avatar</li>
        </ul>
        
        <p><strong>Verifique no console:</strong></p>
        <ol>
            <li>Array de abas inclui 'estilo_visual'</li>
            <li>Cliques nas abas geram logs corretos</li>
            <li>Função showTab() encontra o índice correto</li>
            <li>Classes 'active' são aplicadas corretamente</li>
        </ol>
        
        <p>Abra o console do navegador (F12) para ver os logs detalhados.</p>
    </div>
</body>
</html>