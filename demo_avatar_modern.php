<?php
/**
 * DEMONSTRAÇÃO DO GERENCIADOR MODERNO DE AVATARES
 * Interface nova sem modais com campos de seleção avançados
 */

// Headers de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aba Avatar Moderna - Demo</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/avatar-manager-modern.css">
    
    <style>
        /* Reset e configurações básicas */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #334155;
            line-height: 1.6;
        }
        
        /* Demo header */
        .demo-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 1.5rem 2rem;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .demo-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .demo-header p {
            opacity: 0.9;
            font-size: 1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .demo-features {
            background: rgba(255, 255, 255, 0.1);
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 0.75rem;
        }
        
        .feature-item {
            text-align: center;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            font-size: 0.875rem;
        }
        
        .feature-item i {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }
        
        /* Container principal */
        .demo-container {
            height: calc(100vh - 180px);
            margin: 0;
            padding: 0;
        }
        
        /* Customizações específicas para demo */
        .avatar-manager-modern {
            height: 100%;
            margin: 0;
            border-radius: 0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .demo-header {
                padding: 1rem;
            }
            
            .demo-header h1 {
                font-size: 1.5rem;
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .demo-container {
                height: calc(100vh - 160px);
            }
        }
        
        @media (max-width: 480px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .demo-container {
                height: calc(100vh - 140px);
            }
        }
    </style>
</head>
<body>
    <!-- Header de demonstração -->
    <div class="demo-header">
        <h1>
            <i class="material-icons" style="font-size: 2rem;">auto_awesome</i>
            Aba Avatar Moderna
        </h1>
        <p>Interface compacta sem modais, com campos de seleção avançados e design responsivo</p>
        
        <div class="demo-features">
            <div class="features-grid">
                <div class="feature-item">
                    <i class="material-icons">dashboard</i>
                    <strong>Interface Compacta</strong><br>
                    Layout otimizado sem modais
                </div>
                <div class="feature-item">
                    <i class="material-icons">tune</i>
                    <strong>Campos Dinâmicos</strong><br>
                    Formulários que se adaptam ao tipo
                </div>
                <div class="feature-item">
                    <i class="material-icons">search</i>
                    <strong>Busca Avançada</strong><br>
                    Filtros inteligentes em tempo real
                </div>
                <div class="feature-item">
                    <i class="material-icons">grid_view</i>
                    <strong>Visualizações</strong><br>
                    Grade e lista responsivas
                </div>
                <div class="feature-item">
                    <i class="material-icons">info</i>
                    <strong>Painel Detalhes</strong><br>
                    Informações completas lateral
                </div>
                <div class="feature-item">
                    <i class="material-icons">auto_awesome</i>
                    <strong>Geração Prompt</strong><br>
                    Criação automática inteligente
                </div>
            </div>
        </div>
    </div>
    
    <!-- Container principal -->
    <div class="demo-container">
        <!-- Incluir o gerenciador moderno -->
        <?php include 'avatar_manager_modern.html'; ?>
    </div>
    
    <!-- JavaScript -->
    <script src="assets/js/avatar-manager-modern.js"></script>
    
    <!-- Scripts de demonstração -->
    <script>
        console.log('🎨 Aba Avatar Moderna - Demonstração');
        console.log('📱 Funcionalidades principais:');
        console.log('  ✅ Interface sem modais');
        console.log('  ✅ Campos de seleção avançados');
        console.log('  ✅ Filtros em tempo real');
        console.log('  ✅ Visualização grade/lista');
        console.log('  ✅ Painel de detalhes lateral');
        console.log('  ✅ Formulário de criação dinâmico');
        console.log('  ✅ Geração automática de prompts');
        console.log('  ✅ Design responsivo');
        
        // Aguardar carregamento do sistema
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                if (window.avatarManagerModern) {
                    console.log('✅ Sistema carregado com sucesso!');
                    console.log('💡 Dicas de uso:');
                    console.log('  • Selecione um tipo de avatar para ver campos dinâmicos');
                    console.log('  • Use os filtros para buscar avatares específicos');
                    console.log('  • Clique em um avatar para ver detalhes');
                    console.log('  • Teste a visualização em grade/lista');
                    console.log('  • Ajuste o tamanho dos ícones com o slider');
                } else {
                    console.warn('⚠️ Sistema não carregado ainda');
                }
            }, 1000);
        });
        
        // Monitor de performance
        const startTime = performance.now();
        window.addEventListener('load', () => {
            const loadTime = performance.now() - startTime;
            console.log(`⚡ Tempo de carregamento: ${loadTime.toFixed(2)}ms`);
        });
        
        // Monitor de responsividade
        function updateViewportInfo() {
            const width = window.innerWidth;
            const height = window.innerHeight;
            
            if (width <= 480) {
                console.log('📱 Viewport: Mobile (≤480px)');
            } else if (width <= 768) {
                console.log('📱 Viewport: Tablet (≤768px)');
            } else if (width <= 1024) {
                console.log('💻 Viewport: Desktop pequeno (≤1024px)');
            } else {
                console.log('🖥️ Viewport: Desktop grande (>1024px)');
            }
        }
        
        updateViewportInfo();
        window.addEventListener('resize', updateViewportInfo);
    </script>
</body>
</html>