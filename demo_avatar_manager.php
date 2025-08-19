<?php
/**
 * DEMONSTRAÇÃO DO GERENCIADOR COMPACTO DE AVATARES
 * Página para testar e demonstrar a nova interface compacta
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
    <title>Gerenciador de Avatares - Demo</title>
    
    <!-- CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/avatar-manager-compact.css">
    
    <!-- Variáveis CSS para cores -->
    <style>
        :root {
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --accent-cyan: #06b6d4;
            --item-size: 100px;
        }
        
        /* Reset básico */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
        }
        
        /* Header da demo */
        .demo-header {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
            padding: 1rem 2rem;
            text-align: center;
        }
        
        .demo-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .demo-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        /* Container principal */
        .demo-container {
            height: calc(100vh - 80px);
            overflow: hidden;
        }
    </style>
</head>
<body>
    <!-- Header da demonstração -->
    <div class="demo-header">
        <h1>🎨 Gerenciador Compacto de Avatares</h1>
        <p>Interface moderna estilo explorador de arquivos para gerenciar seus avatares</p>
    </div>
    
    <!-- Container principal -->
    <div class="demo-container">
        <!-- Incluir o gerenciador de avatares -->
        <?php include 'avatar_manager_compact.html'; ?>
    </div>
    
    <!-- JavaScript -->
    <script src="assets/js/avatar-manager-compact.js"></script>
    
    <!-- Script adicional para demo -->
    <script>
        // Adicionar dados de demonstração adicionais
        document.addEventListener('DOMContentLoaded', () => {
            // Aguardar a inicialização do manager
            setTimeout(() => {
                if (window.avatarManager) {
                    // Adicionar mais avatares de exemplo
                    const demoAvatars = [
                        {
                            id: 4,
                            name: 'Capitão Estrelas',
                            type: 'extraterrestre',
                            category: 'comandante',
                            description: 'Comandante galáctico de uma frota espacial',
                            folder: '',
                            tags: ['comandante', 'espaço', 'liderança'],
                            created: '2024-01-08',
                            used: '2024-01-16',
                            public: true,
                            favorite: false
                        },
                        {
                            id: 5,
                            name: 'ARIA-9000',
                            type: 'robotico',
                            category: 'android',
                            description: 'Androide assistente pessoal avançado',
                            folder: '',
                            tags: ['android', 'assistente', 'ia'],
                            created: '2024-01-05',
                            used: '2024-01-14',
                            public: false,
                            favorite: true
                        },
                        {
                            id: 6,
                            name: 'Fenrir',
                            type: 'animal',
                            category: 'lobo',
                            description: 'Lobo gigante da mitologia nórdica',
                            folder: 'animais',
                            tags: ['lobo', 'mitologia', 'gigante'],
                            created: '2024-01-03',
                            used: '2024-01-12',
                            public: true,
                            favorite: false
                        },
                        {
                            id: 7,
                            name: 'Marcus Blackwood',
                            type: 'humano',
                            category: 'detetive',
                            description: 'Detetive veterano especializado em crimes complexos',
                            folder: 'humanos',
                            tags: ['detetive', 'veterano', 'investigador'],
                            created: '2024-01-01',
                            used: '2024-01-10',
                            public: false,
                            favorite: true
                        }
                    ];
                    
                    // Adicionar aos avatares existentes
                    window.avatarManager.avatars.push(...demoAvatars);
                    
                    // Re-renderizar
                    window.avatarManager.renderAvatars();
                    window.avatarManager.updateItemCount();
                    window.avatarManager.updateFolderCounts();
                    
                    console.log('✅ Demo carregada com sucesso!');
                    console.log('💡 Dicas:');
                    console.log('- Use Ctrl+N para criar novo avatar');
                    console.log('- Clique duplo para editar');
                    console.log('- Use o slider para ajustar tamanho dos ícones');
                    console.log('- Teste os filtros e busca');
                }
            }, 1500);
        });
        
        // Log de debugging
        console.log('🎨 Gerenciador Compacto de Avatares carregado');
        console.log('📱 Funcionalidades:');
        console.log('  ✓ Visualização em grade e lista');
        console.log('  ✓ Sistema de pastas hierárquico');
        console.log('  ✓ Busca avançada com filtros');
        console.log('  ✓ Criação rápida de avatares');
        console.log('  ✓ Painel de informações detalhadas');
        console.log('  ✓ Interface responsiva');
        console.log('  ✓ Atalhos de teclado');
    </script>
</body>
</html>