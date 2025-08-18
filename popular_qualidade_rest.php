<?php
/**
 * Script para popular aba qualidade usando REST API direta
 */
require_once 'includes/Environment.php';

try {
    echo "<h1>Populando Aba Qualidade via REST API</h1>";
    
    // Configurações Supabase
    $supabaseUrl = Environment::get('SUPABASE_URL');
    $supabaseKey = Environment::get('SUPABASE_SERVICE_KEY');
    
    if (empty($supabaseUrl) || empty($supabaseKey)) {
        throw new Exception("Configurações Supabase não encontradas");
    }
    
    echo "<p>URL: " . $supabaseUrl . "</p>";
    
    // Headers para requisições
    $headers = [
        'Content-Type: application/json',
        'apikey: ' . $supabaseKey,
        'Authorization: Bearer ' . $supabaseKey,
        'Prefer: return=representation'
    ];
    
    echo "<h2>1. Inserindo blocos de qualidade...</h2>";
    
    // Blocos de qualidade
    $blocos = [
        ['titulo' => 'Qualidade Suprema', 'icone' => 'star', 'tipo_aba' => 'qualidade', 'ordem_exibicao' => 1, 'ativo' => true],
        ['titulo' => 'Detalhamento Profissional', 'icone' => 'zoom_in', 'tipo_aba' => 'qualidade', 'ordem_exibicao' => 2, 'ativo' => true],
        ['titulo' => 'Padrão Comercial', 'icone' => 'business_center', 'tipo_aba' => 'qualidade', 'ordem_exibicao' => 3, 'ativo' => true],
        ['titulo' => 'Excelência Técnica', 'icone' => 'precision_manufacturing', 'tipo_aba' => 'qualidade', 'ordem_exibicao' => 4, 'ativo' => true],
        ['titulo' => 'Reconhecimento Digital', 'icone' => 'trending_up', 'tipo_aba' => 'qualidade', 'ordem_exibicao' => 5, 'ativo' => true],
        ['titulo' => 'Premiações e Competições', 'icone' => 'emoji_events', 'tipo_aba' => 'qualidade', 'ordem_exibicao' => 6, 'ativo' => true]
    ];
    
    $blocosIds = [];
    
    foreach ($blocos as $bloco) {
        echo "<p>Inserindo bloco: <strong>{$bloco['titulo']}</strong>...</p>";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $supabaseUrl . '/rest/v1/blocos_cenas');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bloco));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            echo "<p style='color: red;'>❌ Erro cURL: " . curl_error($ch) . "</p>";
        } elseif ($httpCode === 201) {
            $result = json_decode($response, true);
            if ($result && isset($result[0]['id'])) {
                $blocosIds[$bloco['titulo']] = $result[0]['id'];
                echo "<p style='color: green;'>✅ Bloco inserido com ID: {$result[0]['id']}</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Bloco inserido mas sem ID retornado</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Erro HTTP {$httpCode}: {$response}</p>";
        }
        
        curl_close($ch);
    }
    
    if (empty($blocosIds)) {
        echo "<p style='color: red;'>❌ Nenhum bloco foi inserido. Parando execução.</p>";
        exit;
    }
    
    echo "<h2>2. Inserindo cenas de qualidade...</h2>";
    
    // Cenas por bloco
    $cenas = [
        'Qualidade Suprema' => [
            ['titulo' => 'Masterpiece', 'subtitulo' => 'Obra-prima absoluta', 'texto_prompt' => 'masterpiece, highest quality, perfect execution, artistic excellence, flawless creation', 'valor_selecao' => 'masterpiece', 'ordem_exibicao' => 1, 'ativo' => true],
            ['titulo' => 'Best Quality', 'subtitulo' => 'Melhor qualidade possível', 'texto_prompt' => 'best quality, top tier, premium standard, exceptional quality, superior grade', 'valor_selecao' => 'best_quality', 'ordem_exibicao' => 2, 'ativo' => true],
            ['titulo' => 'Ultra High Quality', 'subtitulo' => 'Qualidade ultra elevada', 'texto_prompt' => 'ultra high quality, maximum resolution, pristine condition, perfect clarity, supreme standard', 'valor_selecao' => 'ultra_high_quality', 'ordem_exibicao' => 3, 'ativo' => true],
            ['titulo' => 'Premium Grade', 'subtitulo' => 'Classificação premium', 'texto_prompt' => 'premium grade, luxury standard, high-end quality, exclusive level, elite classification', 'valor_selecao' => 'premium_grade', 'ordem_exibicao' => 4, 'ativo' => true]
        ],
        'Detalhamento Profissional' => [
            ['titulo' => 'Ultra Detailed', 'subtitulo' => 'Detalhamento extremo', 'texto_prompt' => 'ultra detailed, extreme detail, microscopic precision, exhaustive detail, comprehensive rendering', 'valor_selecao' => 'ultra_detailed', 'ordem_exibicao' => 1, 'ativo' => true],
            ['titulo' => 'Hyper Detailed', 'subtitulo' => 'Hiper detalhamento', 'texto_prompt' => 'hyper detailed, obsessive detail, meticulous precision, intensive detail work, perfectionist approach', 'valor_selecao' => 'hyper_detailed', 'ordem_exibicao' => 2, 'ativo' => true],
            ['titulo' => 'Extremely Detailed', 'subtitulo' => 'Extremamente detalhado', 'texto_prompt' => 'extremely detailed, exceptional detail, intensive rendering, thorough execution, complete precision', 'valor_selecao' => 'extremely_detailed', 'ordem_exibicao' => 3, 'ativo' => true],
            ['titulo' => 'Perfect Anatomy', 'subtitulo' => 'Anatomia perfeita', 'texto_prompt' => 'perfect anatomy, accurate proportions, realistic structure, correct geometry, flawless form', 'valor_selecao' => 'perfect_anatomy', 'ordem_exibicao' => 4, 'ativo' => true]
        ]
    ];
    
    $cenasInseridas = 0;
    
    foreach ($cenas as $nomeBloco => $cenasBloco) {
        if (!isset($blocosIds[$nomeBloco])) {
            echo "<p style='color: red;'>❌ Bloco '{$nomeBloco}' não encontrado!</p>";
            continue;
        }
        
        $blocoId = $blocosIds[$nomeBloco];
        echo "<p>Inserindo cenas para o bloco: <strong>{$nomeBloco}</strong> (ID: {$blocoId})</p>";
        
        foreach ($cenasBloco as $cena) {
            $cena['bloco_id'] = $blocoId;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $supabaseUrl . '/rest/v1/cenas');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($cena));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_error($ch)) {
                echo "<p style='color: red;'>❌ Erro cURL: " . curl_error($ch) . "</p>";
            } elseif ($httpCode === 201) {
                $cenasInseridas++;
                echo "<p style='color: green;'>✅ Cena '{$cena['titulo']}' inserida</p>";
            } else {
                echo "<p style='color: red;'>❌ Erro HTTP {$httpCode} ao inserir '{$cena['titulo']}': {$response}</p>";
            }
            
            curl_close($ch);
        }
    }
    
    echo "<h2>3. Resumo da operação</h2>";
    echo "<p><strong>Blocos inseridos:</strong> " . count($blocosIds) . "</p>";
    echo "<p><strong>Cenas inseridas:</strong> {$cenasInseridas}</p>";
    
    if ($cenasInseridas > 0) {
        echo "<p style='color: green;'>🎉 Aba de qualidade populada com sucesso!</p>";
        echo "<p><a href='test_qualidade_dinamica.php'>🧪 Testar aba de qualidade</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Nenhuma cena foi inserida. Verifique os erros acima.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>