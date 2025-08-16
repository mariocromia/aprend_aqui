<?php
/**
 * Script para inserir dados de teste de ambiente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/CenaManager.php';

try {
    echo "<h1>Inserindo Dados de Teste - Ambiente</h1>";
    
    $cenaManager = new CenaManager();
    
    // Criar blocos de ambiente
    $blocosAmbiente = [
        ['titulo' => 'Natureza', 'icone' => 'nature', 'ordem' => 1],
        ['titulo' => 'Urbano', 'icone' => 'location_city', 'ordem' => 2],
        ['titulo' => 'Interior', 'icone' => 'home', 'ordem' => 3]
    ];
    
    $blocosInseridos = [];
    
    foreach ($blocosAmbiente as $bloco) {
        echo "<h2>Inserindo bloco: {$bloco['titulo']}</h2>";
        
        $resultado = $cenaManager->inserirBloco(
            $bloco['titulo'],
            $bloco['icone'],
            'ambiente',
            $bloco['ordem']
        );
        
        if ($resultado) {
            $blocoId = is_array($resultado) && isset($resultado['id']) ? $resultado['id'] : $resultado;
            $blocosInseridos[] = ['id' => $blocoId, 'titulo' => $bloco['titulo']];
            echo "<p>✅ Bloco inserido com ID: {$blocoId}</p>";
        } else {
            echo "<p>❌ Erro ao inserir bloco</p>";
        }
    }
    
    // Inserir cenas para cada bloco
    $cenasAmbiente = [
        'Natureza' => [
            ['titulo' => 'Praia Tropical', 'subtitulo' => 'Paraíso com palmeiras', 'prompt' => 'Uma praia tropical paradisíaca com areias brancas, águas cristalinas azul-turquesa, palmeiras balançando suavemente na brisa do mar', 'valor' => 'praia_tropical'],
            ['titulo' => 'Montanha Nevada', 'subtitulo' => 'Picos cobertos de neve', 'prompt' => 'Majestosas montanhas cobertas de neve, picos rochosos se erguendo contra um céu azul claro, paisagem alpina pristina', 'valor' => 'montanha_nevada'],
            ['titulo' => 'Floresta Amazônica', 'subtitulo' => 'Selva densa', 'prompt' => 'Densa floresta tropical amazônica, vegetação exuberante, árvores gigantescas, luz solar filtrando pela copa das árvores', 'valor' => 'floresta_amazonica']
        ],
        'Urbano' => [
            ['titulo' => 'Manhattan NY', 'subtitulo' => 'Selva de concreto', 'prompt' => 'Arranha-céus imponentes de Manhattan, ruas movimentadas, luzes da cidade, vida urbana agitada de Nova York', 'valor' => 'manhattan_ny'],
            ['titulo' => 'Tóquio Neon', 'subtitulo' => 'Metrópole futurística', 'prompt' => 'Distrito de Shibuya em Tóquio à noite, neons brilhantes, placas luminosas em japonês, arquitetura futurística', 'valor' => 'tokyo_neon']
        ],
        'Interior' => [
            ['titulo' => 'Loft Industrial', 'subtitulo' => 'Estética fabril', 'prompt' => 'Loft industrial moderno com paredes de tijolo expostas, tubulações aparentes, móveis de design contemporâneo', 'valor' => 'loft_industrial'],
            ['titulo' => 'Biblioteca Antiga', 'subtitulo' => 'Acervo centenário', 'prompt' => 'Biblioteca clássica com estantes de madeira até o teto, livros antigos, escadas rolantes, atmosfera acadêmica solene', 'valor' => 'biblioteca_antiga']
        ]
    ];
    
    foreach ($blocosInseridos as $bloco) {
        if (isset($cenasAmbiente[$bloco['titulo']])) {
            echo "<h3>Inserindo cenas para {$bloco['titulo']}</h3>";
            
            $ordem = 1;
            foreach ($cenasAmbiente[$bloco['titulo']] as $cena) {
                $resultado = $cenaManager->inserirCena(
                    $bloco['id'],
                    $cena['titulo'],
                    $cena['subtitulo'],
                    $cena['prompt'],
                    $cena['valor'],
                    $ordem++
                );
                
                if ($resultado) {
                    echo "<p>✅ Cena '{$cena['titulo']}' inserida</p>";
                } else {
                    echo "<p>❌ Erro ao inserir cena '{$cena['titulo']}'</p>";
                }
            }
        }
    }
    
    echo "<h2>✅ Dados de teste inseridos com sucesso!</h2>";
    echo "<p><a href='admin-cards.php'>Ver Admin Cards</a> | <a href='gerador_prompt_modern.php'>Ver Gerador de Prompt</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>