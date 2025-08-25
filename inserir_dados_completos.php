<?php
/**
 * Script para inserir todos os dados do sistema de cenas
 */

require_once 'includes/CenaManager.php';

echo "🚀 Inserindo dados completos do sistema de cenas...\n\n";

try {
    $manager = new CenaManager();
    
    // Dados dos blocos
    $blocos = [
        // AMBIENTE
        ['Natureza', 'nature', 'ambiente', 1],
        ['Urbano', 'location_city', 'ambiente', 2],
        ['Interior', 'home', 'ambiente', 3],
        ['Fantasia', 'auto_fix_high', 'ambiente', 4],
        ['Futurista', 'rocket_launch', 'ambiente', 5],
        
        // ILUMINAÇÃO
        ['Natural', 'wb_sunny', 'iluminacao', 1],
        ['Artificial', 'lightbulb', 'iluminacao', 2],
        ['Dramática', 'theater_comedy', 'iluminacao', 3],
        ['Especial', 'auto_fix_high', 'iluminacao', 4],
        ['Ambiente', 'nights_stay', 'iluminacao', 5],
        
        // AVATAR
        ['Humanos', 'person', 'avatar', 1],
        ['Profissões', 'work', 'avatar', 2],
        ['Fantasia', 'auto_fix_high', 'avatar', 3],
        ['Animais', 'pets', 'avatar', 4],
        ['Personalizados', 'face', 'avatar', 5],
        
        // CÂMERA
        ['Ângulos', 'photo_camera', 'camera', 1],
        ['Distâncias', 'zoom_in', 'camera', 2],
        ['Movimentos', 'videocam', 'camera', 3],
        ['Estilos', 'camera_alt', 'camera', 4],
        ['Especiais', 'movie_creation', 'camera', 5],
        
        // VOZ
        ['Tons', 'record_voice_over', 'voz', 1],
        ['Estilos', 'psychology', 'voz', 2],
        
        // AÇÃO
        ['Ações Corporais', 'directions_run', 'acao', 1],
        ['Expressões', 'sentiment_satisfied', 'acao', 2],
        ['Gestos', 'pan_tool', 'acao', 3],
        ['Interações', 'handshake', 'acao', 4],
        ['Dinâmicos', 'speed', 'acao', 5],
    ];
    
    echo "📋 Inserindo blocos...\n";
    $blocosInseridos = [];
    
    foreach ($blocos as $index => $bloco) {
        list($titulo, $icone, $tipoAba, $ordem) = $bloco;
        
        if ($manager->inserirBloco($titulo, $icone, $tipoAba, $ordem)) {
            echo "✅ Bloco '{$titulo}' inserido\n";
            $blocosInseridos[] = $index + 1; // IDs começam em 1
        } else {
            echo "❌ Erro ao inserir bloco '{$titulo}'\n";
        }
    }
    
    echo "\n📊 Inserindo cenas de exemplo...\n";
    
    // Cenas de exemplo para alguns blocos
    $cenasExemplo = [
        // Natureza (bloco 1)
        [1, 'Floresta', 'Ambiente natural', 'floresta densa com árvores altas', 'floresta', 1],
        [1, 'Praia', 'Costa marítima', 'praia tropical com areia branca', 'praia', 2],
        [1, 'Montanha', 'Paisagem montanhosa', 'montanha majestosa com picos nevados', 'montanha', 3],
        [1, 'Deserto', 'Ambiente árido', 'deserto vasto com dunas douradas', 'deserto', 4],
        [1, 'Campo', 'Paisagem rural', 'campo verde com flores silvestres', 'campo', 5],
        [1, 'Lago', 'Corpo d\'água', 'lago cristalino cercado por natureza', 'lago', 6],
        
        // Urbano (bloco 2)
        [2, 'Cidade', 'Centro urbano', 'cidade moderna com arranha-céus', 'cidade', 1],
        [2, 'Rua', 'Via urbana', 'rua movimentada com pedestres', 'rua', 2],
        [2, 'Praça', 'Espaço público', 'praça urbana com fontes e bancos', 'praca', 3],
        [2, 'Shopping', 'Centro comercial', 'shopping center moderno', 'shopping', 4],
        [2, 'Estação', 'Terminal de transporte', 'estação de trem movimentada', 'estacao', 5],
        [2, 'Ponte', 'Estrutura urbana', 'ponte moderna sobre rio urbano', 'ponte', 6],
        
        // Ações Corporais (primeiro bloco de ação)
        [25, 'Correndo', 'Em movimento', 'correndo dinamicamente', 'correndo', 1],
        [25, 'Caminhando', 'Movimento calmo', 'caminhando naturalmente', 'caminhando', 2],
        [25, 'Saltando', 'Ação dinâmica', 'saltando energicamente', 'saltando', 3],
        [25, 'Dançando', 'Movimento rítmico', 'dançando graciosamente', 'dancando', 4],
        [25, 'Sentado', 'Posição estática', 'sentado confortavelmente', 'sentado', 5],
        [25, 'Deitado', 'Posição relaxada', 'deitado relaxadamente', 'deitado', 6],
    ];
    
    foreach ($cenasExemplo as $cena) {
        list($blocoId, $titulo, $subtitulo, $textoPrompt, $valorSelecao, $ordem) = $cena;
        
        if ($manager->inserirCena($blocoId, $titulo, $subtitulo, $textoPrompt, $valorSelecao, $ordem)) {
            echo "✅ Cena '{$titulo}' inserida no bloco {$blocoId}\n";
        } else {
            echo "❌ Erro ao inserir cena '{$titulo}'\n";
        }
    }
    
    echo "\n📈 Verificando dados inseridos...\n";
    
    // Verificar alguns tipos
    $tipos = ['ambiente', 'acao'];
    foreach ($tipos as $tipo) {
        $blocosTipo = $manager->getBlocosPorTipo($tipo);
        echo "📊 Tipo '{$tipo}': " . count($blocosTipo) . " blocos\n";
        
        foreach ($blocosTipo as $bloco) {
            $cenas = $manager->getCenasPorBloco($bloco['id']);
            echo "   - {$bloco['titulo']}: " . count($cenas) . " cenas\n";
        }
    }
    
    echo "\n🎉 Dados inseridos com sucesso!\n";
    echo "💡 Agora você pode usar CenaRenderer::gerarAba() no seu arquivo principal.\n\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>