<?php
/**
 * Script para Verificar Número de Blocos por Aba
 * Para padronizar os skeleton loaders
 */

require_once 'includes/Environment.php';
require_once 'includes/SupabaseClient.php';

try {
    echo "📊 VERIFICANDO BLOCOS POR ABA...\n\n";
    
    $supabase = new SupabaseClient();
    
    $abas = ['ambiente', 'estilo_visual', 'iluminacao', 'tecnica', 'elementos_especiais', 'qualidade', 'avatar', 'camera', 'voz', 'acao'];
    
    $resultado = [];
    
    foreach ($abas as $aba) {
        echo "🔍 Verificando aba: $aba\n";
        
        $blocos = $supabase->makeRequest(
            "blocos_cenas?tipo_aba=eq.$aba&select=id,titulo&order=ordem_exibicao",
            'GET', null, true
        );
        
        if ($blocos['status'] === 200) {
            $count = count($blocos['data']);
            $resultado[$aba] = $count;
            
            echo "   ✅ $count blocos encontrados\n";
            
            if ($count > 0) {
                foreach ($blocos['data'] as $bloco) {
                    echo "      • {$bloco['titulo']}\n";
                }
            }
        } else {
            $resultado[$aba] = 0;
            echo "   ❌ Erro ou nenhum bloco encontrado\n";
        }
        echo "\n";
    }
    
    echo "📋 RESUMO FINAL:\n";
    echo "================\n";
    
    $blocosPopulados = 0;
    $blocosVazios = 0;
    
    foreach ($resultado as $aba => $count) {
        $status = $count > 0 ? '✅' : '❌';
        echo "$status $aba: $count blocos\n";
        
        if ($count > 0) {
            $blocosPopulados++;
        } else {
            $blocosVazios++;
        }
    }
    
    echo "\n📊 ESTATÍSTICAS:\n";
    echo "Abas com dados: $blocosPopulados\n";
    echo "Abas vazias: $blocosVazios\n";
    
    // Sugerir padrão
    $values = array_filter(array_values($resultado));
    if (!empty($values)) {
        $media = array_sum($values) / count($values);
        $moda = array_count_values($values);
        arsort($moda);
        $numeroMaisComum = array_key_first($moda);
        
        echo "\n💡 SUGESTÃO DE PADRONIZAÇÃO:\n";
        echo "Número mais comum: $numeroMaisComum blocos\n";
        echo "Média: " . round($media, 1) . " blocos\n";
        echo "Recomendação: Use $numeroMaisComum blocos para todas as abas no skeleton\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
?>