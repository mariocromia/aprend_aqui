<?php
/**
 * Debug da Aba Técnica
 * Testa se os componentes estão funcionando
 */

session_start();
$_SESSION['usuario_id'] = 1; // Simular usuário logado

require_once 'includes/Environment.php';
require_once 'includes/CenaManager.php';
require_once 'includes/CenaRendererPrompt.php';

echo "🔍 DEBUG ABA TÉCNICA\n";
echo "===================\n\n";

try {
    // Teste 1: CenaManager
    echo "1️⃣ Testando CenaManager...\n";
    $cenaManager = new CenaManager();
    echo "   ✅ CenaManager criado\n";
    
    // Teste 2: Buscar blocos técnicos
    echo "\n2️⃣ Buscando blocos técnicos...\n";
    $blocos = $cenaManager->getBlocosPorTipo('tecnica');
    echo "   📦 Blocos encontrados: " . count($blocos) . "\n";
    
    if (empty($blocos)) {
        echo "   ❌ PROBLEMA: Nenhum bloco técnico encontrado!\n";
        echo "   💡 SOLUÇÃO: Execute sql_popular_aba_tecnica.sql\n";
    } else {
        foreach ($blocos as $bloco) {
            echo "      • {$bloco['titulo']} (ID: {$bloco['id']})\n";
        }
    }
    
    // Teste 3: CenaRenderer
    echo "\n3️⃣ Testando CenaRenderer...\n";
    $cenaRenderer = new CenaRendererPrompt($cenaManager);
    echo "   ✅ CenaRenderer criado\n";
    
    // Teste 4: Renderizar aba técnica
    echo "\n4️⃣ Testando renderização...\n";
    $html = $cenaRenderer->renderizarAbaTecnica();
    echo "   📄 HTML gerado: " . strlen($html) . " caracteres\n";
    
    if (strlen($html) < 100) {
        echo "   ❌ PROBLEMA: HTML muito pequeno\n";
        echo "   🔍 Conteúdo: " . substr($html, 0, 200) . "...\n";
    } else {
        echo "   ✅ HTML parece OK\n";
        echo "   🔍 Início: " . substr($html, 0, 100) . "...\n";
    }
    
    // Teste 5: Simular API
    echo "\n5️⃣ Simulando API call...\n";
    $_GET['tab'] = 'tecnica';
    
    ob_start();
    include 'api/load_tab_content.php';
    $apiOutput = ob_get_clean();
    
    echo "   📡 API Response: " . strlen($apiOutput) . " caracteres\n";
    
    $jsonData = json_decode($apiOutput, true);
    if ($jsonData && isset($jsonData['success'])) {
        echo "   ✅ JSON válido\n";
        echo "   📊 Success: " . ($jsonData['success'] ? 'true' : 'false') . "\n";
        
        if (!$jsonData['success']) {
            echo "   ❌ Erro: " . ($jsonData['message'] ?? 'Desconhecido') . "\n";
        }
    } else {
        echo "   ❌ JSON inválido ou erro\n";
        echo "   🔍 Output: " . substr($apiOutput, 0, 200) . "...\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n🎯 CHECKLIST PARA RESOLVER:\n";
echo "□ Executar SQL: sql_popular_aba_tecnica.sql\n";
echo "□ Verificar se constraint permite 'tecnica'\n";
echo "□ Testar API endpoint diretamente\n";
echo "□ Verificar logs de erro do PHP\n";
?>