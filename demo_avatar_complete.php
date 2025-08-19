<?php
/**
 * DEMONSTRAÇÃO COMPLETA - AVATAR MANAGER + PROMPT INTEGRATION
 * Sistema integrado de gerenciamento de avatares com prompt
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
    <title>Sistema Completo de Avatares - Demo</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/avatar-manager-modern.css">
    <link rel="stylesheet" href="assets/css/prompt-avatars-manager.css">
    
    <style>
        /* Reset e configurações */
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
        
        /* Layout principal */
        .demo-layout {
            display: grid;
            grid-template-rows: auto 1fr;
            height: 100vh;
        }
        
        /* Header */
        .demo-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .demo-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .demo-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        /* Conteúdo principal */
        .demo-content {
            display: grid;
            grid-template-rows: auto 1fr;
            overflow: hidden;
        }
        
        /* Seção do prompt */
        .prompt-section {
            padding: 1.5rem 2rem 0;
            background: #f8fafc;
        }
        
        /* Avatar manager */
        .avatar-section {
            overflow: hidden;
        }
        
        .avatar-manager-modern {
            height: 100%;
            border-radius: 0;
            margin: 0;
        }
        
        /* Customizações responsivas */
        @media (max-width: 768px) {
            .demo-header {
                padding: 0.75rem 1rem;
            }
            
            .demo-header h1 {
                font-size: 1.25rem;
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            
            .prompt-section {
                padding: 1rem;
            }
        }
        
        /* Indicadores visuais */
        .integration-badge {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            margin-left: 1rem;
        }
        
        .integration-badge i {
            font-size: 1rem;
        }
        
        /* Loading state */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.8);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }
        
        .loading-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid rgba(255, 255, 255, 0.2);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .loading-text {
            font-size: 1.125rem;
            font-weight: 500;
        }
        
        .loading-tips {
            margin-top: 1rem;
            text-align: center;
            opacity: 0.8;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-spinner"></div>
        <div class="loading-text">Carregando Sistema de Avatares...</div>
        <div class="loading-tips">
            Integrando componentes e preparando interface
        </div>
    </div>

    <!-- Layout principal -->
    <div class="demo-layout">
        <!-- Header -->
        <div class="demo-header">
            <h1>
                <i class="material-icons" style="font-size: 1.75rem;">auto_awesome</i>
                Sistema Completo de Avatares
                <span class="integration-badge">
                    <i class="material-icons">link</i>
                    Integrado
                </span>
            </h1>
            <p>Gerenciamento de avatares com integração completa ao sistema de prompts</p>
        </div>
        
        <!-- Conteúdo -->
        <div class="demo-content">
            <!-- Seção do gerenciador de prompts -->
            <div class="prompt-section">
                <?php include 'prompt_avatars_manager.html'; ?>
            </div>
            
            <!-- Seção do gerenciador de avatares -->
            <div class="avatar-section">
                <?php include 'avatar_manager_modern.html'; ?>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="assets/js/prompt-avatars-manager.js"></script>
    <script src="assets/js/avatar-manager-modern.js"></script>
    
    <!-- Script de integração e demonstração -->
    <script>
        // Sistema de inicialização
        class DemoSystem {
            constructor() {
                this.initStartTime = performance.now();
                this.componentsLoaded = {
                    promptManager: false,
                    avatarManager: false
                };
                
                this.init();
            }
            
            init() {
                console.log('🚀 Inicializando Sistema Completo de Avatares');
                
                // Aguardar carregamento dos componentes
                this.waitForComponents();
                
                // Configurar integração
                this.setupIntegration();
                
                // Adicionar dados de demonstração extras
                this.loadDemoData();
            }
            
            waitForComponents() {
                const checkInterval = setInterval(() => {
                    if (window.promptAvatarsManager) {
                        this.componentsLoaded.promptManager = true;
                        console.log('✅ Gerenciador de Prompts carregado');
                    }
                    
                    if (window.avatarManagerModern) {
                        this.componentsLoaded.avatarManager = true;
                        console.log('✅ Gerenciador de Avatares carregado');
                    }
                    
                    if (this.componentsLoaded.promptManager && this.componentsLoaded.avatarManager) {
                        clearInterval(checkInterval);
                        this.onSystemReady();
                    }
                }, 100);
                
                // Timeout de segurança
                setTimeout(() => {
                    if (!this.componentsLoaded.promptManager || !this.componentsLoaded.avatarManager) {
                        console.warn('⚠️ Alguns componentes não carregaram no tempo esperado');
                        this.onSystemReady();
                    }
                }, 5000);
            }
            
            onSystemReady() {
                const loadTime = performance.now() - this.initStartTime;
                
                console.log('🎉 Sistema totalmente carregado!');
                console.log(`⚡ Tempo de inicialização: ${loadTime.toFixed(2)}ms`);
                
                // Ocultar loading
                const loadingOverlay = document.getElementById('loading-overlay');
                if (loadingOverlay) {
                    loadingOverlay.style.opacity = '0';
                    setTimeout(() => {
                        loadingOverlay.style.display = 'none';
                    }, 300);
                }
                
                // Mostrar dicas de uso
                this.showUsageTips();
                
                // Configurar eventos personalizados
                this.setupCustomEvents();
            }
            
            setupIntegration() {
                // Verificar se os sistemas estão integrados corretamente
                console.log('🔗 Configurando integração entre componentes...');
            }
            
            loadDemoData() {
                setTimeout(() => {
                    if (window.avatarManagerModern) {
                        // Adicionar avatares extras para demonstração
                        const extraAvatars = [
                            {
                                id: 6,
                                name: 'Professor Magnus',
                                type: 'humano',
                                gender: 'masculino',
                                age: 'idoso',
                                description: 'Sábio professor de magia antiga, especialista em runas e encantamentos.',
                                tags: ['professor', 'sábio', 'magia', 'runas'],
                                visibility: 'publico',
                                favorite: false,
                                created: '2024-01-02T09:15:00Z',
                                lastUsed: null,
                                characteristics: {
                                    profissao: 'Professor de Magia',
                                    especialidade: 'Runas Antigas',
                                    personalidade: 'Sábio e paciente'
                                }
                            },
                            {
                                id: 7,
                                name: 'Luna Silverpaw',
                                type: 'animal',
                                gender: 'feminino',
                                age: 'jovem',
                                description: 'Loba prateada com habilidades místicas, guardiã da floresta encantada.',
                                tags: ['loba', 'prateada', 'mística', 'guardiã'],
                                visibility: 'privado',
                                favorite: true,
                                created: '2024-01-04T14:30:00Z',
                                lastUsed: '2024-01-15T11:20:00Z',
                                characteristics: {
                                    especie: 'Canis lupus mysticus',
                                    habitat: 'Floresta Encantada',
                                    comportamento: 'Protetora e mystical'
                                }
                            }
                        ];
                        
                        window.avatarManagerModern.avatars.push(...extraAvatars);
                        window.avatarManagerModern.renderAvatars();
                        window.avatarManagerModern.updateStats();
                        
                        console.log('📊 Dados de demonstração adicionais carregados');
                    }
                }, 1500);
            }
            
            showUsageTips() {
                console.log('💡 DICAS DE USO:');
                console.log('  1. 🎯 Clique em "+" nos avatares para adicionar ao prompt');
                console.log('  2. 📝 Use o painel lateral para criar novos avatares');
                console.log('  3. 🔍 Teste os filtros e busca em tempo real');
                console.log('  4. 👁️ Clique em um avatar para ver detalhes completos');
                console.log('  5. 📋 O prompt combinado é gerado automaticamente');
                console.log('  6. ❌ Remova avatares do prompt clicando no "X"');
                console.log('  7. 📱 Interface totalmente responsiva');
            }
            
            setupCustomEvents() {
                // Eventos customizados para integração
                document.addEventListener('avatarAddedToPrompt', (e) => {
                    console.log('🎭 Avatar adicionado ao prompt:', e.detail);
                });
                
                document.addEventListener('avatarRemovedFromPrompt', (e) => {
                    console.log('🗑️ Avatar removido do prompt:', e.detail);
                });
            }
        }
        
        // Inicializar sistema quando DOM estiver pronto
        document.addEventListener('DOMContentLoaded', () => {
            window.demoSystem = new DemoSystem();
        });
        
        // Monitor de performance
        window.addEventListener('load', () => {
            const loadTime = performance.now();
            console.log(`🏁 Página totalmente carregada em ${loadTime.toFixed(2)}ms`);
        });
        
        // Log de recursos carregados
        console.log('📦 Recursos carregados:');
        console.log('  ✅ avatar-manager-modern.css');
        console.log('  ✅ prompt-avatars-manager.css');
        console.log('  ✅ prompt-avatars-manager.js');
        console.log('  ✅ avatar-manager-modern.js');
        console.log('  ✅ Material Icons');
        console.log('  ✅ Google Fonts (Inter + Fira Code)');
    </script>
</body>
</html>