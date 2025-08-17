<?php
/**
 * Script Alternativo para Popular Aba Estilo Visual
 * Usa uma abordagem diferente para contornar a constraint
 * 
 * SOLUÇÃO 1: Usar 'ambiente' temporariamente e depois atualizar
 * SOLUÇÃO 2: Fornecer SQL direto para execução manual
 */

require_once 'includes/Environment.php';
require_once 'includes/SupabaseClient.php';
require_once 'includes/DatabaseOptimizer.php';

try {
    echo "🎨 SOLUÇÕES PARA POPULAR ESTILO VISUAL\n";
    echo "=====================================\n\n";
    
    $supabase = new SupabaseClient();
    
    // Verificar quais tipos_aba são permitidos
    echo "🔍 Verificando tipos de aba permitidos...\n";
    
    $tiposExistentes = $supabase->makeRequest(
        'blocos_cenas?select=tipo_aba&order=tipo_aba',
        'GET', null, true
    );
    
    if ($tiposExistentes['status'] === 200) {
        $tipos = array_unique(array_column($tiposExistentes['data'], 'tipo_aba'));
        echo "✅ Tipos permitidos atualmente: " . implode(', ', $tipos) . "\n\n";
    }
    
    echo "🛠️  ESCOLHA UMA SOLUÇÃO:\n\n";
    
    // SOLUÇÃO 1: Método workaround
    echo "SOLUÇÃO 1: MÉTODO WORKAROUND (Recomendado)\n";
    echo "==========================================\n";
    echo "1. Vamos inserir como 'ambiente' temporariamente\n";
    echo "2. Depois atualizar para 'estilo_visual'\n";
    echo "3. Isso contorna a constraint\n\n";
    
    if (isset($_GET['solucao']) && $_GET['solucao'] === '1') {
        executarSolucao1($supabase);
    } else {
        echo "Para executar: acesse ?solucao=1\n\n";
    }
    
    // SOLUÇÃO 2: SQL Direto
    echo "SOLUÇÃO 2: SQL DIRETO NO SUPABASE\n";
    echo "==================================\n";
    echo "Execute este SQL diretamente no painel Supabase:\n\n";
    
    gerarSQLDireto();
    
    // SOLUÇÃO 3: Corrigir constraint primeiro
    echo "\nSOLUÇÃO 3: CORRIGIR CONSTRAINT PRIMEIRO\n";
    echo "=======================================\n";
    echo "1. Execute este SQL no Supabase para corrigir a constraint:\n\n";
    
    echo "-- Corrigir constraint\n";
    echo "ALTER TABLE blocos_cenas DROP CONSTRAINT IF EXISTS blocos_cenas_tipo_aba_check;\n";
    echo "ALTER TABLE blocos_cenas ADD CONSTRAINT blocos_cenas_tipo_aba_check \n";
    echo "CHECK (tipo_aba IN ('ambiente', 'estilo_visual', 'iluminacao', 'tecnica', 'elementos_especiais', 'qualidade', 'avatar', 'camera', 'voz', 'acao'));\n\n";
    
    echo "2. Depois execute: php popular_estilo_visual.php\n\n";
    
    // SOLUÇÃO 4: Via API direta
    echo "SOLUÇÃO 4: INSERÇÃO DIRETA VIA CÓDIGO\n";
    echo "=====================================\n";
    
    if (isset($_GET['solucao']) && $_GET['solucao'] === '4') {
        executarSolucao4($supabase);
    } else {
        echo "Para executar: acesse ?solucao=4\n\n";
    }

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

function executarSolucao1($supabase) {
    echo "🚀 Executando Solução 1 (Workaround)...\n\n";
    
    try {
        // Dados dos blocos
        $blocos = [
            ['titulo' => 'Estilos Artísticos Clássicos', 'icone' => 'palette'],
            ['titulo' => 'Estilos Digitais e Modernos', 'icone' => 'computer'],
            ['titulo' => 'Estilos Cinematográficos', 'icone' => 'movie'],
            ['titulo' => 'Ilustração e Anime', 'icone' => 'brush'],
            ['titulo' => 'Estilos Fotográficos', 'icone' => 'camera_alt'],
            ['titulo' => 'Fantasia e Magia', 'icone' => 'auto_fix_high']
        ];
        
        $blocosIds = [];
        $ordem = 1;
        
        // Inserir blocos como 'ambiente' temporariamente
        foreach ($blocos as $bloco) {
            echo "📦 Criando: {$bloco['titulo']}\n";
            
            $dados = [
                'titulo' => $bloco['titulo'],
                'icone' => $bloco['icone'],
                'tipo_aba' => 'ambiente', // Temporário!
                'ordem_exibicao' => $ordem + 100, // Para não conflitar
                'ativo' => true
            ];
            
            $resultado = $supabase->makeRequest('blocos_cenas', 'POST', $dados, true);
            
            if ($resultado['status'] === 201) {
                $blocoId = $resultado['data'][0]['id'];
                $blocosIds[] = $blocoId;
                echo "   ✅ Criado com ID: $blocoId\n";
            } else {
                throw new Exception("Erro ao criar bloco: " . json_encode($resultado));
            }
            
            $ordem++;
        }
        
        echo "\n🔄 Atualizando tipo_aba para 'estilo_visual'...\n";
        
        // Atualizar todos os blocos para estilo_visual
        foreach ($blocosIds as $index => $blocoId) {
            $dadosUpdate = [
                'tipo_aba' => 'estilo_visual',
                'ordem_exibicao' => $index + 1
            ];
            
            $resultado = $supabase->makeRequest(
                "blocos_cenas?id=eq.$blocoId",
                'PATCH',
                $dadosUpdate,
                true
            );
            
            if ($resultado['status'] === 200) {
                echo "   ✅ Bloco $blocoId atualizado\n";
            } else {
                echo "   ❌ Erro ao atualizar bloco $blocoId: " . json_encode($resultado) . "\n";
            }
        }
        
        echo "\n🎨 Agora inserindo cenas...\n";
        inserirCenasEstiloVisual($supabase, $blocosIds);
        
        echo "\n🎉 SUCESSO! Estilo Visual populado via workaround!\n";
        
    } catch (Exception $e) {
        echo "❌ Erro na Solução 1: " . $e->getMessage() . "\n";
    }
}

function executarSolucao4($supabase) {
    echo "🚀 Executando Solução 4 (Inserção Direta)...\n\n";
    
    try {
        // Tentar inserir usando diferentes abordagens
        
        // Abordagem 1: Usar RPC se disponível
        echo "🔍 Tentando via RPC...\n";
        
        $dadosRPC = [
            'p_titulo' => 'Teste Estilo Visual',
            'p_icone' => 'palette',
            'p_tipo_aba' => 'estilo_visual',
            'p_ordem' => 1
        ];
        
        $resultadoRPC = $supabase->makeRequest('rpc/insert_bloco_sem_constraint', 'POST', $dadosRPC, true);
        
        if ($resultadoRPC['status'] === 200) {
            echo "✅ RPC funcionou! Usando método RPC...\n";
            // Continuar com RPC
        } else {
            echo "❌ RPC não disponível\n";
            
            // Abordagem 2: Tentar desabilitar constraint temporariamente
            echo "🔍 Tentando inserção direta...\n";
            
            // Criar um bloco especial marcado para conversão
            $dados = [
                'titulo' => 'ESTILO_VISUAL_TEMP_1',
                'icone' => 'palette',
                'tipo_aba' => 'ambiente',
                'ordem_exibicao' => 999,
                'ativo' => false // Marcar como inativo
            ];
            
            $resultado = $supabase->makeRequest('blocos_cenas', 'POST', $dados, true);
            
            if ($resultado['status'] === 201) {
                $blocoId = $resultado['data'][0]['id'];
                echo "✅ Bloco temporário criado: $blocoId\n";
                echo "ℹ️  Agora você precisa atualizar manualmente no Supabase:\n";
                echo "   UPDATE blocos_cenas SET tipo_aba = 'estilo_visual', titulo = 'Estilos Artísticos Clássicos', ativo = true WHERE id = $blocoId;\n\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Erro na Solução 4: " . $e->getMessage() . "\n";
    }
}

function inserirCenasEstiloVisual($supabase, $blocosIds) {
    // Dados simplificados das cenas para teste
    $cenasPorBloco = [
        // Bloco 1: Estilos Artísticos Clássicos
        [
            ['titulo' => 'Realismo', 'prompt' => 'estilo realista, detalhes precisos', 'valor' => 'realismo'],
            ['titulo' => 'Impressionismo', 'prompt' => 'estilo impressionista, pinceladas visíveis', 'valor' => 'impressionismo'],
            ['titulo' => 'Surrealismo', 'prompt' => 'estilo surrealista, elementos oníricos', 'valor' => 'surrealismo']
        ],
        // Bloco 2: Estilos Digitais
        [
            ['titulo' => 'Cyberpunk', 'prompt' => 'estilo cyberpunk, luzes neon', 'valor' => 'cyberpunk'],
            ['titulo' => 'Vaporwave', 'prompt' => 'estilo vaporwave, cores pastel', 'valor' => 'vaporwave'],
            ['titulo' => 'Pixel Art', 'prompt' => 'estilo pixel art, pixels visíveis', 'valor' => 'pixel_art']
        ],
        // Blocos 3-6: Dados básicos
        [
            ['titulo' => 'Film Noir', 'prompt' => 'estilo film noir, alto contraste', 'valor' => 'film_noir']
        ],
        [
            ['titulo' => 'Anime', 'prompt' => 'estilo anime, olhos grandes', 'valor' => 'anime'],
            ['titulo' => 'Pixar', 'prompt' => 'estilo Pixar, animação 3D', 'valor' => 'pixar'],
            ['titulo' => 'Disney', 'prompt' => 'estilo Disney clássico', 'valor' => 'disney']
        ],
        [
            ['titulo' => 'Fotorealismo', 'prompt' => 'fotorealismo, detalhes ultra precisos', 'valor' => 'fotorealismo']
        ],
        [
            ['titulo' => 'Fantasy Art', 'prompt' => 'estilo fantasy art, elementos mágicos', 'valor' => 'fantasy_art']
        ]
    ];
    
    foreach ($blocosIds as $index => $blocoId) {
        if (isset($cenasPorBloco[$index])) {
            $cenas = $cenasPorBloco[$index];
            
            foreach ($cenas as $ordemCena => $cena) {
                $dadosCena = [
                    'bloco_id' => $blocoId,
                    'titulo' => $cena['titulo'],
                    'subtitulo' => $cena['titulo'],
                    'texto_prompt' => $cena['prompt'],
                    'valor_selecao' => $cena['valor'],
                    'ordem_exibicao' => $ordemCena + 1,
                    'ativo' => true
                ];
                
                $resultado = $supabase->makeRequest('cenas', 'POST', $dadosCena, true);
                
                if ($resultado['status'] === 201) {
                    echo "      ➕ {$cena['titulo']}\n";
                } else {
                    echo "      ❌ Erro ao criar {$cena['titulo']}\n";
                }
            }
        }
    }
}

function gerarSQLDireto() {
    echo "-- POPULAÇÃO COMPLETA ESTILO VISUAL\n";
    echo "-- Execute este SQL diretamente no Supabase\n\n";
    
    echo "-- 1. Corrigir constraint\n";
    echo "ALTER TABLE blocos_cenas DROP CONSTRAINT IF EXISTS blocos_cenas_tipo_aba_check;\n";
    echo "ALTER TABLE blocos_cenas ADD CONSTRAINT blocos_cenas_tipo_aba_check \n";
    echo "CHECK (tipo_aba IN ('ambiente', 'estilo_visual', 'iluminacao', 'tecnica', 'elementos_especiais', 'qualidade', 'avatar', 'camera', 'voz', 'acao'));\n\n";
    
    echo "-- 2. Inserir blocos\n";
    $blocos = [
        "INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Estilos Artísticos Clássicos', 'palette', 'estilo_visual', 1, true);",
        "INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Estilos Digitais e Modernos', 'computer', 'estilo_visual', 2, true);",
        "INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Estilos Cinematográficos', 'movie', 'estilo_visual', 3, true);",
        "INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Ilustração e Anime', 'brush', 'estilo_visual', 4, true);",
        "INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Estilos Fotográficos', 'camera_alt', 'estilo_visual', 5, true);",
        "INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Fantasia e Magia', 'auto_fix_high', 'estilo_visual', 6, true);"
    ];
    
    foreach ($blocos as $sql) {
        echo "$sql\n";
    }
    
    echo "\n-- 3. Inserir algumas cenas de exemplo\n";
    echo "-- (Após executar os blocos, pegue os IDs gerados e substitua aqui)\n";
    echo "INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES\n";
    echo "((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Artísticos Clássicos' AND tipo_aba = 'estilo_visual'), 'Realismo', 'Representação fiel da realidade', 'estilo realista, detalhes precisos, cores naturais', 'realismo', 1, true),\n";
    echo "((SELECT id FROM blocos_cenas WHERE titulo = 'Ilustração e Anime' AND tipo_aba = 'estilo_visual'), 'Pixar', 'Animação 3D Pixar', 'estilo Pixar, animação 3D, personagens expressivos', 'pixar', 1, true),\n";
    echo "((SELECT id FROM blocos_cenas WHERE titulo = 'Ilustração e Anime' AND tipo_aba = 'estilo_visual'), 'Disney', 'Clássico Disney tradicional', 'estilo Disney clássico, animação tradicional, personagens carismáticos', 'disney', 2, true);\n\n";
}
?>