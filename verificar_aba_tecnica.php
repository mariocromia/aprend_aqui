<?php
/**
 * Script para Verificar se a Aba Técnica foi Populada Corretamente
 */

require_once 'includes/Environment.php';
require_once 'includes/SupabaseClient.php';

try {
    echo "🔍 VERIFICANDO ABA TÉCNICA...\n\n";
    
    $supabase = new SupabaseClient();
    
    // Verificar blocos técnicos
    echo "📦 Verificando blocos da aba técnica...\n";
    
    $blocosTecnicos = $supabase->makeRequest(
        'blocos_cenas?tipo_aba=eq.tecnica&select=*&order=ordem_exibicao',
        'GET', null, true
    );
    
    if ($blocosTecnicos['status'] === 200 && !empty($blocosTecnicos['data'])) {
        echo "✅ Blocos técnicos encontrados: " . count($blocosTecnicos['data']) . "\n";
        
        foreach ($blocosTecnicos['data'] as $bloco) {
            echo "   • ID {$bloco['id']}: {$bloco['titulo']} (ordem: {$bloco['ordem_exibicao']})\n";
            
            // Verificar cenas de cada bloco
            $cenas = $supabase->makeRequest(
                "cenas?bloco_id=eq.{$bloco['id']}&select=titulo&order=ordem_exibicao",
                'GET', null, true
            );
            
            if ($cenas['status'] === 200) {
                echo "     → Cenas: " . count($cenas['data']) . "\n";
            }
        }
    } else {
        echo "❌ PROBLEMA: Nenhum bloco técnico encontrado!\n";
        echo "📋 Resposta: " . json_encode($blocosTecnicos) . "\n\n";
        
        echo "🛠️ SOLUÇÃO: Execute o SQL sql_popular_aba_tecnica.sql no Supabase\n";
        return;
    }
    
    echo "\n🎨 Verificando cenas técnicas...\n";
    
    // Verificar total de cenas técnicas
    $totalCenas = $supabase->makeRequest(
        'cenas?bloco_id=in.(' . implode(',', array_column($blocosTecnicos['data'], 'id')) . ')&select=count',
        'GET', null, true
    );
    
    echo "✅ Total de cenas técnicas: ";
    if ($totalCenas['status'] === 200) {
        echo count($totalCenas['data']) . "\n";
    }
    
    echo "\n🔄 Verificando carregamento via API...\n";
    
    // Testar endpoint de carregamento
    $testAPI = $supabase->makeRequest(
        'blocos_cenas?tipo_aba=eq.tecnica&select=id,titulo,icone,ordem_exibicao&order=ordem_exibicao',
        'GET', null, true
    );
    
    if ($testAPI['status'] === 200) {
        echo "✅ API responde corretamente\n";
        echo "📊 Dados retornados: " . json_encode($testAPI['data'], JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Problema na API: " . json_encode($testAPI) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
?>