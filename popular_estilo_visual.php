<?php
/**
 * Script para Popular Aba Estilo Visual
 * Popula blocos e cenas da aba estilo_visual no banco de dados
 * 
 * Execute via: php popular_estilo_visual.php
 * Ou acesse via browser: /popular_estilo_visual.php
 */

require_once 'includes/Environment.php';
require_once 'includes/SupabaseClient.php';
require_once 'includes/DatabaseOptimizer.php';

try {
    echo "🎨 Iniciando população da aba Estilo Visual...\n\n";
    
    $supabase = new SupabaseClient();
    $optimizer = DatabaseOptimizer::getInstance();
    
    // Limpar dados existentes
    echo "🧹 Limpando dados existentes...\n";
    
    // Buscar blocos existentes de estilo_visual
    $blocosExistentes = $supabase->makeRequest(
        'blocos_cenas?tipo_aba=eq.estilo_visual&select=id', 
        'GET', null, true
    );
    
    if ($blocosExistentes['status'] === 200 && !empty($blocosExistentes['data'])) {
        foreach ($blocosExistentes['data'] as $bloco) {
            // Deletar cenas do bloco
            $supabase->makeRequest(
                "cenas?bloco_id=eq.{$bloco['id']}", 
                'DELETE', null, true
            );
        }
        
        // Deletar blocos
        $supabase->makeRequest(
            'blocos_cenas?tipo_aba=eq.estilo_visual', 
            'DELETE', null, true
        );
    }
    
    echo "✅ Dados existentes removidos.\n\n";
    
    // Estrutura de dados para população
    $estilosVisuais = [
        // BLOCO 1: Estilos Artísticos Clássicos
        [
            'titulo' => 'Estilos Artísticos Clássicos',
            'icone' => 'palette',
            'ordem' => 1,
            'cenas' => [
                ['titulo' => 'Realismo', 'subtitulo' => 'Representação fiel da realidade', 'prompt' => 'estilo realista, detalhes precisos, cores naturais, iluminação natural, textura realística', 'valor' => 'realismo'],
                ['titulo' => 'Impressionismo', 'subtitulo' => 'Pinceladas soltas e luz natural', 'prompt' => 'estilo impressionista, pinceladas visíveis, cores vibrantes, luz natural, atmosfera etérea', 'valor' => 'impressionismo'],
                ['titulo' => 'Art Nouveau', 'subtitulo' => 'Linhas orgânicas e florais', 'prompt' => 'estilo art nouveau, linhas sinuosas, motivos florais, ornamentação elegante, cores suaves', 'valor' => 'art_nouveau'],
                ['titulo' => 'Surrealismo', 'subtitulo' => 'Mundo dos sonhos e fantasia', 'prompt' => 'estilo surrealista, elementos oníricos, composição impossível, cores vibrantes, atmosfera fantástica', 'valor' => 'surrealismo'],
                ['titulo' => 'Cubismo', 'subtitulo' => 'Formas geométricas fragmentadas', 'prompt' => 'estilo cubista, formas geométricas, perspectivas múltiplas, fragmentação visual, cores contrastantes', 'valor' => 'cubismo'],
                ['titulo' => 'Expressionismo', 'subtitulo' => 'Emoções intensas e cores vibrantes', 'prompt' => 'estilo expressionista, cores intensas, pinceladas dramáticas, emoção intensa, distorção expressiva', 'valor' => 'expressionismo'],
                ['titulo' => 'Barroco', 'subtitulo' => 'Dramaticidade e ornamentação rica', 'prompt' => 'estilo barroco, dramaticidade intensa, ornamentação rica, contrastes de luz, composição dinâmica', 'valor' => 'barroco'],
                ['titulo' => 'Minimalismo', 'subtitulo' => 'Simplicidade e elementos essenciais', 'prompt' => 'estilo minimalista, simplicidade extrema, cores neutras, formas limpas, espaço negativo', 'valor' => 'minimalismo']
            ]
        ],
        
        // BLOCO 2: Estilos Digitais e Modernos
        [
            'titulo' => 'Estilos Digitais e Modernos',
            'icone' => 'computer',
            'ordem' => 2,
            'cenas' => [
                ['titulo' => 'Cyberpunk', 'subtitulo' => 'Futuro tecnológico neon', 'prompt' => 'estilo cyberpunk, luzes neon, tecnologia futurística, atmosfera urbana noturna, cores vibrantes', 'valor' => 'cyberpunk'],
                ['titulo' => 'Vaporwave', 'subtitulo' => 'Estética retrô-futurista dos anos 80', 'prompt' => 'estilo vaporwave, cores pastel, elementos dos anos 80, grade retrô, aesthetic nostálgico', 'valor' => 'vaporwave'],
                ['titulo' => 'Glitch Art', 'subtitulo' => 'Falhas digitais artísticas', 'prompt' => 'estilo glitch art, distorções digitais, cores RGB deslocadas, pixelação, efeitos de erro', 'valor' => 'glitch_art'],
                ['titulo' => 'Low Poly', 'subtitulo' => 'Formas geométricas simplificadas', 'prompt' => 'estilo low poly, formas geométricas, polígonos visíveis, cores flat, design simplificado', 'valor' => 'low_poly'],
                ['titulo' => 'Pixel Art', 'subtitulo' => 'Arte em pixels nostálgica', 'prompt' => 'estilo pixel art, pixels visíveis, cores limitadas, aesthetic retrô de videogame, detalhes em grade', 'valor' => 'pixel_art'],
                ['titulo' => 'Synthwave', 'subtitulo' => 'Ondas sintéticas dos anos 80', 'prompt' => 'estilo synthwave, cores neon, gradientes, grid futurista, aesthetic dos anos 80, luzes vibrantes', 'valor' => 'synthwave'],
                ['titulo' => 'Holográfico', 'subtitulo' => 'Efeitos iridescentes e metálicos', 'prompt' => 'estilo holográfico, efeitos iridescentes, reflexos metálicos, cores cambiantes, superfícies brilhantes', 'valor' => 'holografico'],
                ['titulo' => 'Neon Noir', 'subtitulo' => 'Filme noir com luzes neon', 'prompt' => 'estilo neon noir, contrastes dramáticos, luzes neon coloridas, sombras profundas, atmosfera misteriosa', 'valor' => 'neon_noir']
            ]
        ],
        
        // BLOCO 3: Estilos Cinematográficos
        [
            'titulo' => 'Estilos Cinematográficos',
            'icone' => 'movie',
            'ordem' => 3,
            'cenas' => [
                ['titulo' => 'Film Noir', 'subtitulo' => 'Drama em preto e branco', 'prompt' => 'estilo film noir, alto contraste, sombras dramáticas, iluminação lateral, atmosfera sombria', 'valor' => 'film_noir'],
                ['titulo' => 'Wes Anderson', 'subtitulo' => 'Simetria e paleta pastel', 'prompt' => 'estilo Wes Anderson, composição simétrica, cores pastel, enquadramento centralizado, aesthetic vintage', 'valor' => 'wes_anderson'],
                ['titulo' => 'Tim Burton', 'subtitulo' => 'Gótico e fantástico', 'prompt' => 'estilo Tim Burton, aesthetic gótico, cores sombrias, elementos fantásticos, atmosfera sinistra', 'valor' => 'tim_burton'],
                ['titulo' => 'Blade Runner', 'subtitulo' => 'Futuro distópico urbano', 'prompt' => 'estilo Blade Runner, futuro distópico, luzes urbanas, chuva, atmosfera noir futurística', 'valor' => 'blade_runner'],
                ['titulo' => 'Studio Ghibli', 'subtitulo' => 'Animação mágica e natural', 'prompt' => 'estilo Studio Ghibli, cores suaves, natureza exuberante, atmosfera mágica, detalhes delicados', 'valor' => 'studio_ghibli'],
                ['titulo' => 'Matrix', 'subtitulo' => 'Realidade digital verde', 'prompt' => 'estilo Matrix, código verde, realidade digital, efeitos de matriz, atmosfera cyber', 'valor' => 'matrix'],
                ['titulo' => 'Mad Max', 'subtitulo' => 'Pós-apocalíptico desértico', 'prompt' => 'estilo Mad Max, pós-apocalíptico, tons terrosos, veículos modificados, paisagem árida', 'valor' => 'mad_max'],
                ['titulo' => 'Tron', 'subtitulo' => 'Grid digital luminoso', 'prompt' => 'estilo Tron, grid digital, luzes azuis, formas geométricas, ambiente virtual futurístico', 'valor' => 'tron']
            ]
        ],
        
        // BLOCO 4: Ilustração e Anime
        [
            'titulo' => 'Ilustração e Anime',
            'icone' => 'brush',
            'ordem' => 4,
            'cenas' => [
                ['titulo' => 'Anime Clássico', 'subtitulo' => 'Estilo anime tradicional', 'prompt' => 'estilo anime clássico, olhos grandes, cores vibrantes, linhas limpas, cel shading', 'valor' => 'anime_classico'],
                ['titulo' => 'Manga', 'subtitulo' => 'Quadrinhos japoneses em preto e branco', 'prompt' => 'estilo manga, preto e branco, hachuras, linhas expressivas, composição dinâmica', 'valor' => 'manga'],
                ['titulo' => 'Chibi', 'subtitulo' => 'Personagens fofos e desproporcionais', 'prompt' => 'estilo chibi, proporções fofas, cabeça grande, expressões adoráveis, cores pastel', 'valor' => 'chibi'],
                ['titulo' => 'Concept Art', 'subtitulo' => 'Arte conceitual de jogos', 'prompt' => 'estilo concept art, pintura digital, atmosfera épica, detalhes elaborados, composição cinematográfica', 'valor' => 'concept_art'],
                ['titulo' => 'Watercolor', 'subtitulo' => 'Aquarela delicada', 'prompt' => 'estilo aquarela, texturas fluidas, cores translúcidas, bordas suaves, efeito de tinta molhada', 'valor' => 'watercolor'],
                ['titulo' => 'Comic Book', 'subtitulo' => 'Quadrinhos americanos', 'prompt' => 'estilo comic book, cores saturadas, linhas grossas, efeitos de ação, composição dinâmica', 'valor' => 'comic_book'],
                ['titulo' => 'Pixar', 'subtitulo' => 'Animação 3D Pixar', 'prompt' => 'estilo Pixar, animação 3D, personagens expressivos, cores vibrantes, renderização suave, design adorável', 'valor' => 'pixar'],
                ['titulo' => 'Disney', 'subtitulo' => 'Clássico Disney tradicional', 'prompt' => 'estilo Disney clássico, animação tradicional, personagens carismáticos, cores mágicas, linhas suaves', 'valor' => 'disney'],
                ['titulo' => 'Pin-up', 'subtitulo' => 'Arte pin-up vintage', 'prompt' => 'estilo pin-up, cores vintage, poses elegantes, aesthetic dos anos 50, ilustração glamourosa', 'valor' => 'pin_up'],
                ['titulo' => 'Cartoon', 'subtitulo' => 'Animação cartoon clássica', 'prompt' => 'estilo cartoon, formas exageradas, cores vibrantes, expressões caricatas, linhas curvas', 'valor' => 'cartoon']
            ]
        ],
        
        // BLOCO 5: Estilos Fotográficos
        [
            'titulo' => 'Estilos Fotográficos',
            'icone' => 'camera_alt',
            'ordem' => 5,
            'cenas' => [
                ['titulo' => 'Fotorealismo', 'subtitulo' => 'Realismo fotográfico perfeito', 'prompt' => 'fotorealismo, detalhes ultra precisos, textura realística, iluminação natural, qualidade 8K', 'valor' => 'fotorealismo'],
                ['titulo' => 'Vintage', 'subtitulo' => 'Fotografia antiga e nostálgica', 'prompt' => 'estilo vintage, cores desbotadas, grão de filme, tons sépia, aesthetic retrô', 'valor' => 'vintage'],
                ['titulo' => 'Polaroid', 'subtitulo' => 'Fotos instantâneas nostálgicas', 'prompt' => 'estilo polaroid, bordas brancas, cores saturadas, ligeiro desfoque, textura de filme', 'valor' => 'polaroid'],
                ['titulo' => 'HDR', 'subtitulo' => 'Alto alcance dinâmico', 'prompt' => 'estilo HDR, cores ultra saturadas, detalhes extremos, contraste intenso, processamento dramático', 'valor' => 'hdr'],
                ['titulo' => 'Macro', 'subtitulo' => 'Detalhes extremos em close-up', 'prompt' => 'estilo macro, detalhes microscópicos, profundidade de campo rasa, textura extrema, close-up intenso', 'valor' => 'macro'],
                ['titulo' => 'Tilt-Shift', 'subtitulo' => 'Efeito miniatura', 'prompt' => 'estilo tilt-shift, efeito miniatura, foco seletivo, cores saturadas, perspectiva única', 'valor' => 'tilt_shift'],
                ['titulo' => 'Long Exposure', 'subtitulo' => 'Exposição longa artística', 'prompt' => 'estilo long exposure, movimento borrado, rastros de luz, efeito sedoso, tempo suspenso', 'valor' => 'long_exposure'],
                ['titulo' => 'Double Exposure', 'subtitulo' => 'Dupla exposição criativa', 'prompt' => 'estilo double exposure, sobreposição criativa, transparências, fusão de imagens, efeito artístico', 'valor' => 'double_exposure']
            ]
        ],
        
        // BLOCO 6: Fantasia e Magia
        [
            'titulo' => 'Fantasia e Magia',
            'icone' => 'auto_fix_high',
            'ordem' => 6,
            'cenas' => [
                ['titulo' => 'Fantasy Art', 'subtitulo' => 'Arte fantástica épica', 'prompt' => 'estilo fantasy art, elementos mágicos, criaturas fantásticas, atmosfera épica, cores vibrantes', 'valor' => 'fantasy_art'],
                ['titulo' => 'Steampunk', 'subtitulo' => 'Tecnologia a vapor vitoriana', 'prompt' => 'estilo steampunk, engrenagens, vapor, bronze, era vitoriana, tecnologia retro-futurística', 'valor' => 'steampunk'],
                ['titulo' => 'Fairy Tale', 'subtitulo' => 'Contos de fadas encantados', 'prompt' => 'estilo fairy tale, atmosfera mágica, cores suaves, elementos encantados, fantasia delicada', 'valor' => 'fairy_tale'],
                ['titulo' => 'Dark Fantasy', 'subtitulo' => 'Fantasia sombria e gótica', 'prompt' => 'estilo dark fantasy, atmosfera sombria, elementos góticos, cores escuras, magia obscura', 'valor' => 'dark_fantasy'],
                ['titulo' => 'Mythology', 'subtitulo' => 'Mitologia antiga épica', 'prompt' => 'estilo mythology, elementos mitológicos, deuses antigos, atmosfera épica, simbolismo ancestral', 'valor' => 'mythology'],
                ['titulo' => 'Cosmic Horror', 'subtitulo' => 'Horror cósmico lovecraftiano', 'prompt' => 'estilo cosmic horror, tentáculos, dimensões alienígenas, cores não-terrestres, horror incompreensível', 'valor' => 'cosmic_horror'],
                ['titulo' => 'Ethereal', 'subtitulo' => 'Etéreo e transcendental', 'prompt' => 'estilo ethereal, atmosfera etérea, luz suave, transparências, elementos flutuantes, magia sutil', 'valor' => 'ethereal'],
                ['titulo' => 'Crystal Art', 'subtitulo' => 'Arte cristalina e prismática', 'prompt' => 'estilo crystal art, cristais brilhantes, reflexos prismáticos, cores iridescentes, geometria cristalina', 'valor' => 'crystal_art']
            ]
        ]
    ];
    
    $totalBlocos = 0;
    $totalCenas = 0;
    
    foreach ($estilosVisuais as $bloco) {
        echo "📦 Criando bloco: {$bloco['titulo']}\n";
        
        // Criar bloco
        $dadosBloco = [
            'titulo' => $bloco['titulo'],
            'icone' => $bloco['icone'],
            'tipo_aba' => 'estilo_visual',
            'ordem_exibicao' => $bloco['ordem'],
            'ativo' => true
        ];
        
        $resultadoBloco = $supabase->makeRequest('blocos_cenas', 'POST', $dadosBloco, true);
        
        if ($resultadoBloco['status'] !== 201) {
            throw new Exception("Erro ao criar bloco: " . json_encode($resultadoBloco));
        }
        
        $blocoId = $resultadoBloco['data'][0]['id'];
        $totalBlocos++;
        
        echo "   ✅ Bloco criado com ID: $blocoId\n";
        
        // Criar cenas do bloco
        $ordemCena = 1;
        foreach ($bloco['cenas'] as $cena) {
            $dadosCena = [
                'bloco_id' => $blocoId,
                'titulo' => $cena['titulo'],
                'subtitulo' => $cena['subtitulo'],
                'texto_prompt' => $cena['prompt'],
                'valor_selecao' => $cena['valor'],
                'ordem_exibicao' => $ordemCena,
                'ativo' => true
            ];
            
            $resultadoCena = $supabase->makeRequest('cenas', 'POST', $dadosCena, true);
            
            if ($resultadoCena['status'] !== 201) {
                throw new Exception("Erro ao criar cena: " . json_encode($resultadoCena));
            }
            
            echo "      ➕ {$cena['titulo']}\n";
            $ordemCena++;
            $totalCenas++;
        }
        
        echo "\n";
    }
    
    // Limpar cache
    $optimizer->clearCache();
    
    echo "🎉 POPULAÇÃO CONCLUÍDA COM SUCESSO!\n\n";
    echo "📊 ESTATÍSTICAS:\n";
    echo "   🗂️  Blocos criados: $totalBlocos\n";
    echo "   🎨 Cenas criadas: $totalCenas\n";
    echo "   📂 Aba: estilo_visual\n\n";
    
    echo "✨ BLOCOS CRIADOS:\n";
    foreach ($estilosVisuais as $bloco) {
        echo "   • {$bloco['titulo']} (" . count($bloco['cenas']) . " cenas)\n";
    }
    
    echo "\n🔄 Cache limpo para atualização imediata.\n";
    echo "✅ Aba Estilo Visual pronta para uso!\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "📍 Linha: " . $e->getLine() . "\n";
    echo "📄 Arquivo: " . $e->getFile() . "\n";
    
    if (php_sapi_name() !== 'cli') {
        echo "<br><pre>" . $e->getTraceAsString() . "</pre>";
    }
}
?>