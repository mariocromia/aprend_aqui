<?php
/**
 * Script para inserir dados de Estilo Visual baseado nos dados de ambiente
 * Cria uma nova categoria tipo_aba = 'estilo_visual'
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/CenaManager.php';

try {
    echo "<h1>🎨 Inserindo Dados de Estilo Visual</h1>";
    echo "<p><em>Duplicando dados de ambiente adaptados para estilos visuais</em></p>";
    
    $cenaManager = new CenaManager();
    
    // Estrutura dos dados baseada nos ambientes, mas adaptada para estilos visuais
    $dadosEstiloVisual = [
        // Bloco: Estilos Naturais
        [
            'bloco' => ['titulo' => 'Estilos Naturais', 'icone' => 'nature', 'tipo_aba' => 'estilo_visual', 'ordem' => 1],
            'cenas' => [
                ['titulo' => 'Realismo Natural', 'subtitulo' => 'Fotorrealista', 'prompt' => 'estilo fotorrealista natural, detalhes hiper-realistas, texturas naturais autênticas', 'valor' => 'realismo_natural', 'ordem' => 1],
                ['titulo' => 'Paisagismo Clássico', 'subtitulo' => 'Pintura tradicional', 'prompt' => 'estilo pintura paisagística clássica, técnica tradicional, composição harmônica', 'valor' => 'paisagismo_classico', 'ordem' => 2],
                ['titulo' => 'Impressionismo', 'subtitulo' => 'Luz e movimento', 'prompt' => 'estilo impressionista, pinceladas soltas, jogo de luz e sombra, atmosfera etérea', 'valor' => 'impressionismo', 'ordem' => 3],
                ['titulo' => 'Aquarela Botânica', 'subtitulo' => 'Delicadeza natural', 'prompt' => 'estilo aquarela botânica, transparências suaves, detalhes delicados da natureza', 'valor' => 'aquarela_botanica', 'ordem' => 4],
                ['titulo' => 'Fotografia de Natureza', 'subtitulo' => 'Documental natural', 'prompt' => 'estilo fotografia documental de natureza, composição natural, cores autênticas', 'valor' => 'fotografia_natureza', 'ordem' => 5],
                ['titulo' => 'Arte Rupestre', 'subtitulo' => 'Primitivo natural', 'prompt' => 'estilo arte rupestre primitiva, traços simples, cores terrosas, expressão ancestral', 'valor' => 'arte_rupestre', 'ordem' => 6],
                ['titulo' => 'Pintura Plein Air', 'subtitulo' => 'Ao ar livre', 'prompt' => 'estilo plein air painting, captura da luz natural, espontaneidade, frescor', 'valor' => 'plein_air', 'ordem' => 7]
            ]
        ],
        
        // Bloco: Estilos Urbanos
        [
            'bloco' => ['titulo' => 'Estilos Urbanos', 'icone' => 'location_city', 'tipo_aba' => 'estilo_visual', 'ordem' => 2],
            'cenas' => [
                ['titulo' => 'Street Art', 'subtitulo' => 'Arte de rua', 'prompt' => 'estilo street art urbano, grafite colorido, expressão jovem, cultura de rua', 'valor' => 'street_art', 'ordem' => 1],
                ['titulo' => 'Arquitetura Moderna', 'subtitulo' => 'Linhas limpas', 'prompt' => 'estilo arquitetônico moderno, linhas geométricas, minimalismo estrutural', 'valor' => 'arquitetura_moderna', 'ordem' => 2],
                ['titulo' => 'Neo-noir', 'subtitulo' => 'Urbano sombrio', 'prompt' => 'estilo neo-noir urbano, contrastes dramáticos, atmosfera noir contemporânea', 'valor' => 'neo_noir', 'ordem' => 3],
                ['titulo' => 'Pop Art Urbano', 'subtitulo' => 'Cores vibrantes', 'prompt' => 'estilo pop art urbano, cores saturadas, elementos comerciais, cultura pop', 'valor' => 'pop_art_urbano', 'ordem' => 4],
                ['titulo' => 'Fotojornalismo', 'subtitulo' => 'Documental urbano', 'prompt' => 'estilo fotojornalismo urbano, narrativa social, momento decisivo', 'valor' => 'fotojornalismo', 'ordem' => 5],
                ['titulo' => 'Brutalismo', 'subtitulo' => 'Concreto bruto', 'prompt' => 'estilo brutalista, estruturas massivas de concreto, geometria industrial', 'valor' => 'brutalismo', 'ordem' => 6]
            ]
        ],
        
        // Bloco: Estilos Artísticos
        [
            'bloco' => ['titulo' => 'Estilos Artísticos', 'icone' => 'brush', 'tipo_aba' => 'estilo_visual', 'ordem' => 3],
            'cenas' => [
                ['titulo' => 'Surrealismo', 'subtitulo' => 'Onírico fantástico', 'prompt' => 'estilo surrealista, elementos oníricos, realidade distorcida, imaginação libertada', 'valor' => 'surrealismo', 'ordem' => 1],
                ['titulo' => 'Cubismo', 'subtitulo' => 'Fragmentação geométrica', 'prompt' => 'estilo cubista, fragmentação geométrica, múltiplas perspectivas, abstração', 'valor' => 'cubismo', 'ordem' => 2],
                ['titulo' => 'Art Nouveau', 'subtitulo' => 'Orgânico decorativo', 'prompt' => 'estilo art nouveau, formas orgânicas, ornamentação floral, elegância decorativa', 'valor' => 'art_nouveau', 'ordem' => 3],
                ['titulo' => 'Expressionismo', 'subtitulo' => 'Emoção intensa', 'prompt' => 'estilo expressionista, cores intensas, deformação emocional, dramaticidade', 'valor' => 'expressionismo', 'ordem' => 4],
                ['titulo' => 'Minimalismo', 'subtitulo' => 'Simplicidade essencial', 'prompt' => 'estilo minimalista, simplicidade extrema, formas puras, essência visual', 'valor' => 'minimalismo', 'ordem' => 5],
                ['titulo' => 'Hiperrealismo', 'subtitulo' => 'Precisão extrema', 'prompt' => 'estilo hiperrealista, precisão fotográfica extrema, detalhamento obsessivo', 'valor' => 'hiperrealismo', 'ordem' => 6],
                ['titulo' => 'Abstração Geométrica', 'subtitulo' => 'Formas puras', 'prompt' => 'estilo abstração geométrica, formas puras, composição matemática', 'valor' => 'abstracao_geometrica', 'ordem' => 7]
            ]
        ],
        
        // Bloco: Estilos Históricos
        [
            'bloco' => ['titulo' => 'Estilos Históricos', 'icone' => 'museum', 'tipo_aba' => 'estilo_visual', 'ordem' => 4],
            'cenas' => [
                ['titulo' => 'Renascimento', 'subtitulo' => 'Clássico refinado', 'prompt' => 'estilo renascentista, técnica clássica refinada, perspectiva perfeita, humanismo', 'valor' => 'renascimento', 'ordem' => 1],
                ['titulo' => 'Barroco', 'subtitulo' => 'Drama ornamental', 'prompt' => 'estilo barroco, dramaticidade ornamental, movimento dinâmico, riqueza visual', 'valor' => 'barroco', 'ordem' => 2],
                ['titulo' => 'Neoclássico', 'subtitulo' => 'Ordem clássica', 'prompt' => 'estilo neoclássico, ordem e proporção, inspiração greco-romana, sobriedade', 'valor' => 'neoclassico', 'ordem' => 3],
                ['titulo' => 'Romantismo', 'subtitulo' => 'Emoção sublime', 'prompt' => 'estilo romântico, emoção sublime, natureza dramática, individualismo', 'valor' => 'romantismo', 'ordem' => 4],
                ['titulo' => 'Arte Medieval', 'subtitulo' => 'Manuscritos iluminados', 'prompt' => 'estilo arte medieval, manuscritos iluminados, simbolismo religioso', 'valor' => 'arte_medieval', 'ordem' => 5],
                ['titulo' => 'Art Déco', 'subtitulo' => 'Elegância geométrica', 'prompt' => 'estilo art déco, elegância geométrica, luxo moderno, linhas estilizadas', 'valor' => 'art_deco', 'ordem' => 6]
            ]
        ],
        
        // Bloco: Estilos Futuristas
        [
            'bloco' => ['titulo' => 'Estilos Futuristas', 'icone' => 'auto_awesome', 'tipo_aba' => 'estilo_visual', 'ordem' => 5],
            'cenas' => [
                ['titulo' => 'Cyberpunk Visual', 'subtitulo' => 'Neo-futurismo', 'prompt' => 'estilo visual cyberpunk, neons saturados, tecnologia distópica, estética digital', 'valor' => 'cyberpunk_visual', 'ordem' => 1],
                ['titulo' => 'Synthwave', 'subtitulo' => 'Retro-futurismo', 'prompt' => 'estilo synthwave, retro-futurismo, paleta neon, nostalgia digital', 'valor' => 'synthwave', 'ordem' => 2],
                ['titulo' => 'Sci-fi Conceitual', 'subtitulo' => 'Ficção científica', 'prompt' => 'estilo concept art sci-fi, tecnologia avançada, visão futurista', 'valor' => 'scifi_conceitual', 'ordem' => 3],
                ['titulo' => 'Holográfico', 'subtitulo' => 'Projeção digital', 'prompt' => 'estilo holográfico, transparências digitais, efeitos de luz tecnológicos', 'valor' => 'holografico', 'ordem' => 4],
                ['titulo' => 'Biomecânico', 'subtitulo' => 'Orgânico tecnológico', 'prompt' => 'estilo biomecânico, fusão orgânico-tecnológica, estruturas híbridas', 'valor' => 'biomecanico', 'ordem' => 5],
                ['titulo' => 'Glitch Art', 'subtitulo' => 'Erro digital', 'prompt' => 'estilo glitch art, distorções digitais, erro como estética, fragmentação', 'valor' => 'glitch_art', 'ordem' => 6]
            ]
        ]
    ];
    
    $totalBlocos = 0;
    $totalCenas = 0;
    
    foreach ($dadosEstiloVisual as $grupo) {
        echo "<h2>🎨 Inserindo bloco: {$grupo['bloco']['titulo']}</h2>";
        
        // Inserir bloco
        $bloco = $grupo['bloco'];
        $resultado = $cenaManager->inserirBloco(
            $bloco['titulo'],
            $bloco['icone'],
            $bloco['tipo_aba'],
            $bloco['ordem']
        );
        
        if ($resultado) {
            $blocoId = is_array($resultado) && isset($resultado['id']) ? $resultado['id'] : $resultado;
            $totalBlocos++;
            echo "<p>✅ Bloco inserido com ID: {$blocoId}</p>";
            
            // Inserir cenas do bloco
            foreach ($grupo['cenas'] as $cena) {
                $resultadoCena = $cenaManager->inserirCena(
                    $blocoId,
                    $cena['titulo'],
                    $cena['subtitulo'],
                    $cena['prompt'],
                    $cena['valor'],
                    $cena['ordem']
                );
                
                if ($resultadoCena) {
                    $totalCenas++;
                    echo "<p>&nbsp;&nbsp;🎭 Estilo '{$cena['titulo']}' inserido</p>";
                } else {
                    echo "<p>&nbsp;&nbsp;❌ Erro ao inserir estilo '{$cena['titulo']}'</p>";
                }
            }
        } else {
            echo "<p>❌ Erro ao inserir bloco '{$bloco['titulo']}'</p>";
        }
        
        echo "<hr>";
    }
    
    echo "<h2>📊 Resumo Final</h2>";
    echo "<div style='background: #f0f9ff; padding: 20px; border-radius: 8px; border-left: 4px solid #3b82f6;'>";
    echo "<p><strong>✅ Blocos de estilo visual inseridos:</strong> {$totalBlocos}</p>";
    echo "<p><strong>🎨 Estilos visuais inseridos:</strong> {$totalCenas}</p>";
    echo "<p><strong>📈 Total de itens:</strong> " . ($totalBlocos + $totalCenas) . "</p>";
    echo "</div>";
    
    echo "<h2>🎭 Categorias de Estilo Visual Inseridas</h2>";
    echo "<div style='background: #fefce8; padding: 15px; border-radius: 8px;'>";
    echo "<ul>";
    echo "<li><strong>Estilos Naturais:</strong> Realismo natural, impressionismo, aquarela botânica, etc.</li>";
    echo "<li><strong>Estilos Urbanos:</strong> Street art, arquitetura moderna, neo-noir, etc.</li>";
    echo "<li><strong>Estilos Artísticos:</strong> Surrealismo, cubismo, minimalismo, hiperrealismo, etc.</li>";
    echo "<li><strong>Estilos Históricos:</strong> Renascimento, barroco, art déco, romantismo, etc.</li>";
    echo "<li><strong>Estilos Futuristas:</strong> Cyberpunk, synthwave, sci-fi, glitch art, etc.</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>🔗 Links Úteis</h2>";
    echo "<p>";
    echo "<a href='admin-cards.php' target='_blank' style='margin-right: 15px;'>🔧 Gerenciar no Admin</a>";
    echo "<a href='gerador_prompt_modern.php' target='_blank' style='margin-right: 15px;'>🎯 Ver no Gerador</a>";
    echo "<a href='test_estilo_visual_dinamico.php' target='_blank'>🧪 Testar Integração</a>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; padding: 20px; border-radius: 8px; border-left: 4px solid #ef4444;'>";
    echo "<p style='color: #dc2626;'><strong>❌ Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<details style='margin-top: 10px;'>";
    echo "<summary style='cursor: pointer; color: #7c2d12;'>Ver detalhes técnicos</summary>";
    echo "<pre style='background: #fff; padding: 10px; border-radius: 4px; margin-top: 10px;'>" . $e->getTraceAsString() . "</pre>";
    echo "</details>";
    echo "</div>";
}
?>