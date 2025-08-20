<?php
/**
 * DEMONSTRAÇÃO DA ABA AVATAR COMPACTA E MODERNA
 * Todas as funcionalidades do demo_avatar_modern.php
 * Design moderno, compacto e atraente
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
    <title>Avatar Manager Compacto Moderno - Demo</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/avatar-manager-compact-modern.css">
    
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
            overflow: hidden;
        }
        
        /* Demo header */
        .demo-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 1rem 2rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 100;
        }
        
        .demo-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            letter-spacing: -0.025em;
        }
        
        .demo-header p {
            opacity: 0.9;
            font-size: 0.875rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .demo-features {
            background: rgba(255, 255, 255, 0.1);
            margin-top: 0.75rem;
            padding: 0.75rem;
            border-radius: 12px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .feature-item {
            text-align: center;
            padding: 0.625rem;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 8px;
            font-size: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        .feature-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }
        
        .feature-item i {
            display: block;
            font-size: 1.25rem;
            margin-bottom: 0.375rem;
            opacity: 0.95;
            color: #8b5cf6;
        }
        
        .feature-item strong {
            background: linear-gradient(45deg, #ffffff, #f1f5f9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 600;
            font-size: 0.8125rem;
        }
        
        /* Container principal */
        .demo-container {
            height: calc(100vh - 140px);
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
        .avatar-manager-compact-modern {
            height: 100%;
            margin: 0;
            border-radius: 0;
            box-shadow: none;
            background: transparent;
            backdrop-filter: blur(20px);
            border: none;
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
                padding: 0.75rem 1rem;
            }
            
            .demo-header h1 {
                font-size: 1.25rem;
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .features-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 0.5rem;
            }
            
            .demo-container {
                height: calc(100vh - 120px);
            }
            
            .feature-item {
                padding: 0.5rem;
                font-size: 0.6875rem;
            }
        }
        
        @media (max-width: 480px) {
            .demo-header {
                padding: 0.625rem 0.75rem;
            }
            
            .demo-header h1 {
                font-size: 1.125rem;
            }
            
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.375rem;
            }
            
            .demo-container {
                height: calc(100vh - 100px);
            }
            
            .feature-item {
                padding: 0.375rem;
            }
            
            .feature-item i {
                font-size: 1rem;
            }
        }

        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(20px);
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .loading-overlay.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .loading-content {
            text-align: center;
            color: #e2e8f0;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 3px solid rgba(139, 92, 246, 0.3);
            border-top: 3px solid #8b5cf6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 1.125rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .loading-subtitle {
            font-size: 0.875rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">Carregando Avatar Manager</div>
            <div class="loading-subtitle">Preparando interface moderna...</div>
        </div>
    </div>

    <!-- Header de demonstração -->
    <div class="demo-header">
        <h1>
            <i class="material-icons" style="font-size: 1.75rem;">auto_awesome</i>
            Avatar Manager Compacto Moderno
        </h1>
        <p>Interface compacta sem modais, design atraente e todas as funcionalidades do sistema original</p>
        
        <div class="demo-features">
            <div class="features-grid">
                <div class="feature-item">
                    <i class="material-icons">dashboard</i>
                    <strong>Compacto</strong><br>
                    Layout otimizado
                </div>
                <div class="feature-item">
                    <i class="material-icons">tune</i>
                    <strong>Dinâmico</strong><br>
                    Campos adaptativos
                </div>
                <div class="feature-item">
                    <i class="material-icons">search</i>
                    <strong>Busca</strong><br>
                    Filtros inteligentes
                </div>
                <div class="feature-item">
                    <i class="material-icons">grid_view</i>
                    <strong>Visualizações</strong><br>
                    Grade e lista
                </div>
                <div class="feature-item">
                    <i class="material-icons">info</i>
                    <strong>Detalhes</strong><br>
                    Painel lateral
                </div>
                <div class="feature-item">
                    <i class="material-icons">auto_awesome</i>
                    <strong>Prompts</strong><br>
                    Geração automática
                </div>
            </div>
        </div>
    </div>
    
    <!-- Container principal -->
    <div class="demo-container">
        <!-- Incluir o gerenciador moderno compacto -->
        <?php include 'avatar_manager_compact_modern.html'; ?>
    </div>
    
    <!-- JavaScript -->
    <script src="assets/js/avatar-manager-compact-modern.js"></script>
    
    <!-- Scripts de demonstração -->
    <script>
        console.log('🎨 Avatar Manager Compacto Moderno - Demonstração');
        console.log('📱 Funcionalidades implementadas:');
        console.log('  ✅ Interface compacta sem modais');
        console.log('  ✅ Campos de seleção dinâmicos');
        console.log('  ✅ Filtros em tempo real');
        console.log('  ✅ Visualização grade/lista');
        console.log('  ✅ Painel lateral para criação/detalhes');
        console.log('  ✅ Formulário de criação dinâmico');
        console.log('  ✅ Geração automática de prompts');
        console.log('  ✅ Design responsivo moderno');
        console.log('  ✅ Seleção múltipla e ações em lote');
        console.log('  ✅ Sistema de favoritos');
        console.log('  ✅ Compartilhamento de avatares');
        console.log('  ✅ Duplicação de avatares');
        console.log('  ✅ Busca avançada');
        console.log('  ✅ Filtros por categoria e status');
        console.log('  ✅ Ordenação personalizada');
        
        // Aguardar carregamento do sistema
        document.addEventListener('DOMContentLoaded', () => {
            // Simular carregamento
            setTimeout(() => {
                const loadingOverlay = document.getElementById('loading-overlay');
                if (loadingOverlay) {
                    loadingOverlay.classList.add('hidden');
                }
                
                // Verificar se o sistema carregou
                setTimeout(() => {
                    if (window.avatarManager) {
                        console.log('✅ Sistema carregado com sucesso!');
                        console.log('💡 Dicas de uso:');
                        console.log('  • Use a busca rápida no header');
                        console.log('  • Clique em "Criar" para adicionar avatares');
                        console.log('  • Selecione um tipo para ver campos dinâmicos');
                        console.log('  • Use os filtros avançados para refinar');
                        console.log('  • Clique em avatares para ver detalhes');
                        console.log('  • Teste as visualizações grade/lista');
                        console.log('  • Use Ctrl+Click para seleção múltipla');
                        console.log('  • Experimente todas as ações disponíveis');
                    } else {
                        console.warn('⚠️ Sistema não carregado ainda');
                    }
                }, 500);
            }, 2000);
        });
        
        // Monitor de performance
        const startTime = performance.now();
        window.addEventListener('load', () => {
            const loadTime = performance.now() - startTime;
            console.log(`⚡ Tempo de carregamento total: ${loadTime.toFixed(2)}ms`);
        });
        
        // Monitor de responsividade
        function updateViewportInfo() {
            const width = window.innerWidth;
            const height = window.innerHeight;
            
            let deviceType = '';
            if (width <= 480) {
                deviceType = '📱 Mobile (≤480px)';
            } else if (width <= 768) {
                deviceType = '📱 Tablet (≤768px)';
            } else if (width <= 1024) {
                deviceType = '💻 Desktop pequeno (≤1024px)';
            } else {
                deviceType = '🖥️ Desktop grande (>1024px)';
            }
            
            console.log(`📐 Viewport: ${deviceType} - ${width}x${height}`);
        }
        
        updateViewportInfo();
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(updateViewportInfo, 250);
        });

        // Atalhos de teclado
        document.addEventListener('keydown', (e) => {
            // Ctrl + N = Novo avatar
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                if (window.avatarManager) {
                    window.avatarManager.showCreationMode();
                    console.log('⌨️ Atalho: Novo avatar (Ctrl+N)');
                }
            }
            
            // Ctrl + F = Focar na busca
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                const searchInput = document.getElementById('quick-search');
                if (searchInput) {
                    searchInput.focus();
                    console.log('⌨️ Atalho: Focar busca (Ctrl+F)');
                }
            }
            
            // Escape = Fechar painel
            if (e.key === 'Escape') {
                if (window.avatarManager) {
                    window.avatarManager.closeSidePanel();
                    console.log('⌨️ Atalho: Fechar painel (Esc)');
                }
            }
        });

        // Analytics de uso (simulado)
        const analytics = {
            actions: [],
            
            track(action, data = {}) {
                this.actions.push({
                    action,
                    data,
                    timestamp: new Date().toISOString()
                });
                
                console.log(`📊 Ação rastreada: ${action}`, data);
            },
            
            getReport() {
                const report = this.actions.reduce((acc, item) => {
                    acc[item.action] = (acc[item.action] || 0) + 1;
                    return acc;
                }, {});
                
                console.log('📈 Relatório de uso:', report);
                return report;
            }
        };

        // Interceptar eventos para analytics
        window.addEventListener('click', (e) => {
            const target = e.target.closest('button, .avatar-card-compact');
            if (target) {
                const action = target.className.includes('btn-create') ? 'create_avatar' :
                              target.className.includes('avatar-card') ? 'view_avatar' :
                              target.className.includes('btn-view') ? 'change_view' :
                              'button_click';
                
                analytics.track(action, {
                    element: target.tagName,
                    class: target.className
                });
            }
        });

        // Relatório automático após 30 segundos
        setTimeout(() => {
            analytics.getReport();
        }, 30000);
    </script>
</body>
</html>