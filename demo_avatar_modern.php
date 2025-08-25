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
            background: #0f172a;
            color: #e2e8f0;
            line-height: 1.6;
        }
        
        /* Demo header */
        .demo-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 1.5rem 2rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .demo-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            letter-spacing: -0.025em;
        }
        
        .demo-header p {
            opacity: 0.9;
            font-size: 1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .demo-features {
            background: rgba(255, 255, 255, 0.15);
            margin-top: 1.25rem;
            padding: 1.25rem;
            border-radius: 16px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.875rem;
            margin-top: 1rem;
        }
        
        .feature-item {
            text-align: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            font-size: 0.875rem;
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        .feature-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .feature-item i {
            display: block;
            font-size: 1.75rem;
            margin-bottom: 0.625rem;
            opacity: 0.95;
            color: #8b5cf6;
        }
        
        /* Container principal */
        .demo-container {
            height: calc(100vh - 200px);
            margin: 0;
            padding: 0;
            background: linear-gradient(145deg, #0f172a 0%, #1e293b 100%);
            position: relative;
        }
        
        .demo-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 20%, rgba(139, 92, 246, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(168, 85, 247, 0.06) 0%, transparent 50%);
            pointer-events: none;
        }
        
        /* Customizações específicas para demo */
        .avatar-manager-modern {
            height: 100%;
            margin: 0;
            border-radius: 0;
            box-shadow: none;
            background: transparent;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
            z-index: 1;
        }
        
        /* Efeitos visuais aprimorados */
        .feature-item strong {
            background: linear-gradient(45deg, #ffffff, #f1f5f9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 600;
        }
        
        /* Animações suaves */
        .demo-header {
            animation: slideInDown 0.6s ease-out;
        }
        
        .demo-container {
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .demo-header {
                padding: 1.25rem 1rem;
            }
            
            .demo-header h1 {
                font-size: 1.6rem;
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .demo-container {
                height: calc(100vh - 180px);
            }
            
            .feature-item {
                padding: 0.875rem;
            }
        }
        
        @media (max-width: 480px) {
            .demo-header {
                padding: 1rem 0.75rem;
            }
            
            .demo-header h1 {
                font-size: 1.4rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 0.625rem;
            }
            
            .demo-container {
                height: calc(100vh - 160px);
            }
            
            .feature-item {
                padding: 0.75rem;
                font-size: 0.8125rem;
            }
            
            .feature-item i {
                font-size: 1.5rem;
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