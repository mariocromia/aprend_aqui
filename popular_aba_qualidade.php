<?php
// Script para popular a aba de qualidade via Supabase
require_once 'includes/Environment.php';
require_once 'includes/SupabaseClient.php';

try {
    echo "<h1>Populando Aba de Qualidade</h1>";
    
    // Inicializar cliente Supabase
    $supabase = new SupabaseClient();
    
    echo "<h2>1. Verificando dados existentes...</h2>";
    
    // Verificar se já existem blocos de qualidade
    $existingBlocos = $supabase->select('blocos_cenas', ['id', 'titulo'], ['tipo_aba' => 'qualidade']);
    
    if (!empty($existingBlocos)) {
        echo "<p style='color: orange;'>⚠️ Já existem " . count($existingBlocos) . " blocos de qualidade:</p>";
        echo "<ul>";
        foreach ($existingBlocos as $bloco) {
            echo "<li>{$bloco['titulo']} (ID: {$bloco['id']})</li>";
        }
        echo "</ul>";
        
        echo "<p>Deseja continuar e adicionar mais dados? (Pode haver duplicatas)</p>";
    }
    
    echo "<h2>2. Inserindo blocos de qualidade...</h2>";
    
    // Array de blocos de qualidade
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
        
        $result = $supabase->insert('blocos_cenas', $bloco);
        
        if ($result && isset($result['id'])) {
            $blocosIds[$bloco['titulo']] = $result['id'];
            echo "<p style='color: green;'>✅ Bloco inserido com ID: {$result['id']}</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao inserir bloco: " . json_encode($result) . "</p>";
        }
    }
    
    echo "<h2>3. Inserindo cenas de qualidade...</h2>";
    
    // Array de cenas por bloco
    $cenas = [
        'Qualidade Suprema' => [
            ['titulo' => 'Masterpiece', 'subtitulo' => 'Obra-prima absoluta', 'texto_prompt' => 'masterpiece, highest quality, perfect execution, artistic excellence, flawless creation', 'valor_selecao' => 'masterpiece', 'ordem_exibicao' => 1],
            ['titulo' => 'Best Quality', 'subtitulo' => 'Melhor qualidade possível', 'texto_prompt' => 'best quality, top tier, premium standard, exceptional quality, superior grade', 'valor_selecao' => 'best_quality', 'ordem_exibicao' => 2],
            ['titulo' => 'Ultra High Quality', 'subtitulo' => 'Qualidade ultra elevada', 'texto_prompt' => 'ultra high quality, maximum resolution, pristine condition, perfect clarity, supreme standard', 'valor_selecao' => 'ultra_high_quality', 'ordem_exibicao' => 3],
            ['titulo' => 'Premium Grade', 'subtitulo' => 'Classificação premium', 'texto_prompt' => 'premium grade, luxury standard, high-end quality, exclusive level, elite classification', 'valor_selecao' => 'premium_grade', 'ordem_exibicao' => 4],
            ['titulo' => 'Professional Level', 'subtitulo' => 'Nível profissional', 'texto_prompt' => 'professional level, industry standard, expert quality, commercial grade, professional execution', 'valor_selecao' => 'professional_level', 'ordem_exibicao' => 5],
            ['titulo' => 'Studio Grade', 'subtitulo' => 'Qualidade de estúdio', 'texto_prompt' => 'studio grade, production quality, professional studio, controlled environment, perfect conditions', 'valor_selecao' => 'studio_grade', 'ordem_exibicao' => 6],
            ['titulo' => 'Gallery Worthy', 'subtitulo' => 'Digno de galeria', 'texto_prompt' => 'gallery worthy, exhibition quality, museum standard, fine art level, collectible grade', 'valor_selecao' => 'gallery_worthy', 'ordem_exibicao' => 7],
            ['titulo' => 'Award Winning', 'subtitulo' => 'Qualidade premiada', 'texto_prompt' => 'award winning, competition winner, recognized excellence, celebrated quality, honored creation', 'valor_selecao' => 'award_winning', 'ordem_exibicao' => 8]
        ],
        'Detalhamento Profissional' => [
            ['titulo' => 'Ultra Detailed', 'subtitulo' => 'Detalhamento extremo', 'texto_prompt' => 'ultra detailed, extreme detail, microscopic precision, exhaustive detail, comprehensive rendering', 'valor_selecao' => 'ultra_detailed', 'ordem_exibicao' => 1],
            ['titulo' => 'Hyper Detailed', 'subtitulo' => 'Hiper detalhamento', 'texto_prompt' => 'hyper detailed, obsessive detail, meticulous precision, intensive detail work, perfectionist approach', 'valor_selecao' => 'hyper_detailed', 'ordem_exibicao' => 2],
            ['titulo' => 'Extremely Detailed', 'subtitulo' => 'Extremamente detalhado', 'texto_prompt' => 'extremely detailed, exceptional detail, intensive rendering, thorough execution, complete precision', 'valor_selecao' => 'extremely_detailed', 'ordem_exibicao' => 3],
            ['titulo' => 'Intricate Details', 'subtitulo' => 'Detalhes intrincados', 'texto_prompt' => 'intricate details, complex patterns, elaborate textures, sophisticated elements, nuanced features', 'valor_selecao' => 'intricate_details', 'ordem_exibicao' => 4],
            ['titulo' => 'Fine Details', 'subtitulo' => 'Detalhes refinados', 'texto_prompt' => 'fine details, delicate features, subtle elements, refined textures, elegant precision', 'valor_selecao' => 'fine_details', 'ordem_exibicao' => 5],
            ['titulo' => 'Perfect Anatomy', 'subtitulo' => 'Anatomia perfeita', 'texto_prompt' => 'perfect anatomy, accurate proportions, realistic structure, correct geometry, flawless form', 'valor_selecao' => 'perfect_anatomy', 'ordem_exibicao' => 6],
            ['titulo' => 'Flawless Composition', 'subtitulo' => 'Composição impecável', 'texto_prompt' => 'flawless composition, perfect balance, ideal arrangement, harmonious layout, optimal structure', 'valor_selecao' => 'flawless_composition', 'ordem_exibicao' => 7],
            ['titulo' => 'Meticulous Craftsmanship', 'subtitulo' => 'Artesanato meticuloso', 'texto_prompt' => 'meticulous craftsmanship, careful execution, precise workmanship, skilled technique, expert handling', 'valor_selecao' => 'meticulous_craftsmanship', 'ordem_exibicao' => 8]
        ],
        'Padrão Comercial' => [
            ['titulo' => 'Magazine Cover', 'subtitulo' => 'Capa de revista', 'texto_prompt' => 'magazine cover quality, editorial standard, publication grade, commercial appeal, market ready', 'valor_selecao' => 'magazine_cover', 'ordem_exibicao' => 1],
            ['titulo' => 'Portfolio Quality', 'subtitulo' => 'Qualidade de portfólio', 'texto_prompt' => 'portfolio quality, professional showcase, career defining, industry standard, presentation grade', 'valor_selecao' => 'portfolio_quality', 'ordem_exibicao' => 2],
            ['titulo' => 'Exhibition Grade', 'subtitulo' => 'Padrão de exposição', 'texto_prompt' => 'exhibition grade, display quality, public presentation, showcase standard, gallery level', 'valor_selecao' => 'exhibition_grade', 'ordem_exibicao' => 3],
            ['titulo' => 'Museum Quality', 'subtitulo' => 'Qualidade de museu', 'texto_prompt' => 'museum quality, archival standard, preservation grade, historical significance, cultural value', 'valor_selecao' => 'museum_quality', 'ordem_exibicao' => 4],
            ['titulo' => 'Commercial Grade', 'subtitulo' => 'Classificação comercial', 'texto_prompt' => 'commercial grade, business standard, market quality, professional use, industry approved', 'valor_selecao' => 'commercial_grade', 'ordem_exibicao' => 5],
            ['titulo' => 'Editorial Standard', 'subtitulo' => 'Padrão editorial', 'texto_prompt' => 'editorial standard, publishing quality, media grade, journalistic excellence, content quality', 'valor_selecao' => 'editorial_standard', 'ordem_exibicao' => 6],
            ['titulo' => 'Advertising Quality', 'subtitulo' => 'Qualidade publicitária', 'texto_prompt' => 'advertising quality, marketing standard, promotional grade, brand quality, campaign level', 'valor_selecao' => 'advertising_quality', 'ordem_exibicao' => 7],
            ['titulo' => 'Luxury Brand', 'subtitulo' => 'Marca de luxo', 'texto_prompt' => 'luxury brand quality, premium standard, high-end appeal, exclusive grade, sophisticated level', 'valor_selecao' => 'luxury_brand', 'ordem_exibicao' => 8]
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
            $cena['ativo'] = true;
            
            $result = $supabase->insert('cenas', $cena);
            
            if ($result && isset($result['id'])) {
                $cenasInseridas++;
                echo "<p style='color: green;'>✅ Cena '{$cena['titulo']}' inserida</p>";
            } else {
                echo "<p style='color: red;'>❌ Erro ao inserir cena '{$cena['titulo']}': " . json_encode($result) . "</p>";
            }
        }
    }
    
    echo "<h2>4. Resumo da operação</h2>";
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
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
