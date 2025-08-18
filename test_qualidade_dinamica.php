<?php
// Teste da aba de qualidade dinâmica
require_once 'includes/Environment.php';
require_once 'includes/CenaManager.php';
require_once 'includes/CenaRendererPrompt.php';

try {
    echo "<h1>Teste da Aba de Qualidade Dinâmica</h1>";
    
    // Inicializar sistema
    $cenaManager = new CenaManager();
    $cenaRenderer = new CenaRendererPrompt($cenaManager);
    
    echo "<h2>1. Verificando blocos de qualidade...</h2>";
    
    // Buscar blocos de qualidade
    $blocosQualidade = $cenaManager->getBlocosPorTipo('qualidade');
    
    if (empty($blocosQualidade)) {
        echo "<p style='color: red;'>❌ Nenhum bloco de qualidade encontrado!</p>";
        echo "<p>Isso significa que a aba de qualidade não tem dados no banco.</p>";
        
        // Verificar se existem outros tipos de aba
        echo "<h3>Verificando outros tipos de aba disponíveis:</h3>";
        echo "<p>Verificando manualmente os tipos de aba conhecidos...</p>";
        $tiposConhecidos = ['ambiente', 'estilo_visual', 'iluminacao', 'tecnica', 'elementos_especiais', 'qualidade', 'avatar', 'camera', 'voz', 'acao'];
        echo "<ul>";
        foreach ($tiposConhecidos as $tipo) {
            $blocos = $cenaManager->getBlocosPorTipo($tipo);
            $count = count($blocos);
            $status = $count > 0 ? "✅ ({$count})" : "❌ (0)";
            echo "<li>{$tipo}: {$status}</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p style='color: green;'>✅ " . count($blocosQualidade) . " blocos de qualidade encontrados!</p>";
        
        echo "<h3>Blocos encontrados:</h3>";
        echo "<ul>";
        foreach ($blocosQualidade as $bloco) {
            echo "<li><strong>{$bloco['titulo']}</strong> (ID: {$bloco['id']}, Ícone: {$bloco['icone']})</li>";
            
            // Buscar cenas deste bloco
            $cenas = $cenaManager->getCenasPorBloco($bloco['id']);
            echo "<ul>";
            foreach ($cenas as $cena) {
                echo "<li>{$cena['titulo']} - {$cena['subtitulo']}</li>";
            }
            echo "</ul>";
        }
        echo "</ul>";
        
        echo "<h2>2. Testando renderização da aba...</h2>";
        
        // Renderizar a aba completa
        $htmlRenderizado = $cenaRenderer->renderizarAbaQualidade();
        
        echo "<h3>HTML Renderizado:</h3>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
        echo htmlspecialchars($htmlRenderizado);
        echo "</div>";
        
        echo "<h3>Visualização:</h3>";
        echo $htmlRenderizado;
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>3. Verificando estrutura do banco...</h2>";

try {
    // Verificar se as tabelas existem
    $db = new PDO("pgsql:host=localhost;dbname=aprend_aqui", "postgres", "123456");
    
    // Verificar tabela blocos_cenas
    $stmt = $db->query("SELECT COUNT(*) as total FROM blocos_cenas WHERE tipo_aba = 'qualidade'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total de blocos de qualidade na tabela blocos_cenas: <strong>{$result['total']}</strong></p>";
    
    // Verificar tabela cenas
    $stmt = $db->query("SELECT COUNT(*) as total FROM cenas c JOIN blocos_cenas b ON c.bloco_id = b.id WHERE b.tipo_aba = 'qualidade'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total de cenas de qualidade na tabela cenas: <strong>{$result['total']}</strong></p>";
    
    // Verificar estrutura das tabelas
    echo "<h3>Estrutura da tabela blocos_cenas:</h3>";
    $stmt = $db->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'blocos_cenas' ORDER BY ordinal_position");
    echo "<ul>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>{$row['column_name']}: {$row['data_type']}</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao verificar banco: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>4. Soluções possíveis:</h2>";
echo "<ol>";
echo "<li><strong>Executar o arquivo SQL:</strong> sql_popular_aba_qualidade.sql</li>";
echo "<li><strong>Verificar se as tabelas existem</strong> e têm a estrutura correta</li>";
echo "<li><strong>Verificar se o tipo_aba 'qualidade' está na constraint</strong> da tabela</li>";
echo "<li><strong>Verificar se há dados ativos</strong> (campo ativo = true)</li>";
echo "</ol>";

echo "<p><a href='sql_popular_aba_qualidade.sql' target='_blank'>📄 Ver arquivo SQL de qualidade</a></p>";
?>
