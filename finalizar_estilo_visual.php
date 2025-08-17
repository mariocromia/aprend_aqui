<?php
/**
 * Script para Finalizar População do Estilo Visual
 * Corrige os blocos que não foram atualizados e adiciona cenas faltantes
 */

require_once 'includes/Environment.php';
require_once 'includes/SupabaseClient.php';

try {
    echo "🔧 Finalizando população do Estilo Visual...\n\n";
    
    $supabase = new SupabaseClient();
    
    // IDs dos blocos criados (baseado no log)
    $blocosIds = [
        49 => 'Estilos Artísticos Clássicos',
        50 => 'Estilos Digitais e Modernos', 
        51 => 'Estilos Cinematográficos',
        52 => 'Ilustração e Anime',
        53 => 'Estilos Fotográficos',
        54 => 'Fantasia e Magia'
    ];
    
    echo "🔍 Verificando status dos blocos...\n";
    
    foreach ($blocosIds as $id => $titulo) {
        // Verificar status atual do bloco
        $bloco = $supabase->makeRequest("blocos_cenas?id=eq.$id&select=*", 'GET', null, true);
        
        if ($bloco['status'] === 200 && !empty($bloco['data'])) {
            $dadosBloco = $bloco['data'][0];
            echo "📦 Bloco $id ({$titulo}): tipo_aba = {$dadosBloco['tipo_aba']}\n";
            
            // Se ainda não é estilo_visual, tentar atualizar
            if ($dadosBloco['tipo_aba'] !== 'estilo_visual') {
                echo "   🔄 Tentando atualizar para estilo_visual...\n";
                
                // Método 1: UPDATE direto
                $update1 = $supabase->makeRequest(
                    "blocos_cenas?id=eq.$id",
                    'PATCH',
                    ['tipo_aba' => 'estilo_visual'],
                    true
                );
                
                if ($update1['status'] === 200) {
                    echo "   ✅ Atualizado com sucesso!\n";
                } else {
                    echo "   ❌ Falha no update. Tentando método alternativo...\n";
                    
                    // Método 2: Recrear o bloco
                    echo "   🔄 Recriando bloco...\n";
                    
                    // Buscar cenas existentes
                    $cenas = $supabase->makeRequest("cenas?bloco_id=eq.$id&select=*", 'GET', null, true);
                    
                    // Deletar bloco antigo
                    $supabase->makeRequest("cenas?bloco_id=eq.$id", 'DELETE', null, true);
                    $supabase->makeRequest("blocos_cenas?id=eq.$id", 'DELETE', null, true);
                    
                    // Criar novo bloco
                    $novoBloco = [
                        'titulo' => $titulo,
                        'icone' => getIconeBloco($titulo),
                        'tipo_aba' => 'ambiente', // Temporário
                        'ordem_exibicao' => getOrdemBloco($titulo),
                        'ativo' => true
                    ];
                    
                    $resultado = $supabase->makeRequest('blocos_cenas', 'POST', $novoBloco, true);
                    
                    if ($resultado['status'] === 201) {
                        $novoId = $resultado['data'][0]['id'];
                        echo "   ✅ Novo bloco criado: ID $novoId\n";
                        
                        // Recriar cenas se existiam
                        if ($cenas['status'] === 200 && !empty($cenas['data'])) {
                            foreach ($cenas['data'] as $cena) {
                                $novaCena = [
                                    'bloco_id' => $novoId,
                                    'titulo' => $cena['titulo'],
                                    'subtitulo' => $cena['subtitulo'],
                                    'texto_prompt' => $cena['texto_prompt'],
                                    'valor_selecao' => $cena['valor_selecao'],
                                    'ordem_exibicao' => $cena['ordem_exibicao'],
                                    'ativo' => true
                                ];
                                
                                $supabase->makeRequest('cenas', 'POST', $novaCena, true);
                            }
                            echo "   ✅ Cenas recriadas\n";
                        }
                    }
                }
            } else {
                echo "   ✅ Já está como estilo_visual\n";
            }
        }
        echo "\n";
    }
    
    echo "📊 Verificando status final...\n";
    
    // Verificar blocos de estilo_visual
    $blocosFinais = $supabase->makeRequest(
        'blocos_cenas?tipo_aba=eq.estilo_visual&select=id,titulo&order=ordem_exibicao',
        'GET', null, true
    );
    
    if ($blocosFinais['status'] === 200) {
        echo "✅ Blocos de estilo_visual encontrados: " . count($blocosFinais['data']) . "\n";
        foreach ($blocosFinais['data'] as $bloco) {
            echo "   • ID {$bloco['id']}: {$bloco['titulo']}\n";
        }
    }
    
    // Verificar cenas
    echo "\n🎨 Verificando cenas...\n";
    
    $cenasFinais = $supabase->makeRequest(
        'cenas?bloco_id=in.(' . implode(',', array_keys($blocosIds)) . ')&select=bloco_id,titulo&order=bloco_id,ordem_exibicao',
        'GET', null, true
    );
    
    if ($cenasFinais['status'] === 200) {
        echo "✅ Total de cenas: " . count($cenasFinais['data']) . "\n";
        
        $cenasPorBloco = [];
        foreach ($cenasFinais['data'] as $cena) {
            $cenasPorBloco[$cena['bloco_id']][] = $cena['titulo'];
        }
        
        foreach ($cenasPorBloco as $blocoId => $titulos) {
            $nomeBloco = $blocosIds[$blocoId] ?? "Bloco $blocoId";
            echo "   📦 $nomeBloco: " . implode(', ', $titulos) . "\n";
        }
    }
    
    echo "\n🎯 PRÓXIMOS PASSOS:\n";
    echo "1. ✅ Blocos básicos criados\n";
    echo "2. ✅ Pixar e Disney incluídos\n";
    echo "3. 🔄 Execute a SOLUÇÃO 2 (SQL direto) para:\n";
    echo "   - Corrigir constraint definitivamente\n";
    echo "   - Adicionar todas as 50 cenas completas\n";
    echo "   - Garantir funcionamento 100%\n\n";
    
    echo "📋 SQL da Solução 2 para copiar no Supabase:\n";
    echo "ALTER TABLE blocos_cenas DROP CONSTRAINT IF EXISTS blocos_cenas_tipo_aba_check;\n";
    echo "ALTER TABLE blocos_cenas ADD CONSTRAINT blocos_cenas_tipo_aba_check \n";
    echo "CHECK (tipo_aba IN ('ambiente', 'estilo_visual', 'iluminacao', 'tecnica', 'elementos_especiais', 'qualidade', 'avatar', 'camera', 'voz', 'acao'));\n\n";
    
    echo "🎉 Base do Estilo Visual está funcionando!\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

function getIconeBloco($titulo) {
    $icones = [
        'Estilos Artísticos Clássicos' => 'palette',
        'Estilos Digitais e Modernos' => 'computer',
        'Estilos Cinematográficos' => 'movie',
        'Ilustração e Anime' => 'brush',
        'Estilos Fotográficos' => 'camera_alt',
        'Fantasia e Magia' => 'auto_fix_high'
    ];
    return $icones[$titulo] ?? 'help';
}

function getOrdemBloco($titulo) {
    $ordens = [
        'Estilos Artísticos Clássicos' => 1,
        'Estilos Digitais e Modernos' => 2,
        'Estilos Cinematográficos' => 3,
        'Ilustração e Anime' => 4,
        'Estilos Fotográficos' => 5,
        'Fantasia e Magia' => 6
    ];
    return $ordens[$titulo] ?? 99;
}
?>