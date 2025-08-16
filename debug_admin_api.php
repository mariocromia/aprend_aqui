<?php
/**
 * Debug - Teste direto da API admin-cards
 */

header('Content-Type: text/html; charset=utf-8');
echo "<h1>🔍 Debug API Admin Cards</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; }
.error { color: red; }
.info { color: blue; }
pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
.test { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
</style>";

// Teste 1: Verificar se arquivos existem
echo "<div class='test'>";
echo "<h2>📁 Teste 1: Verificação de Arquivos</h2>";

$arquivos = [
    'API' => 'api/admin-cards.php',
    'CenaManager' => 'includes/CenaManager.php',
    'SupabaseClient' => 'includes/SupabaseClient.php',
    'Environment' => 'includes/Environment.php'
];

foreach ($arquivos as $nome => $arquivo) {
    if (file_exists($arquivo)) {
        echo "<p class='success'>✅ {$nome}: {$arquivo}</p>";
    } else {
        echo "<p class='error'>❌ {$nome}: {$arquivo} - NÃO ENCONTRADO</p>";
    }
}
echo "</div>";

// Teste 2: Simular chamada da API para listar blocos
echo "<div class='test'>";
echo "<h2>🔧 Teste 2: Simulação API - Listar Blocos</h2>";

try {
    // Simular $_GET['action']
    $_GET['action'] = 'listar_blocos';
    
    // Capturar output
    ob_start();
    include 'api/admin-cards.php';
    $output = ob_get_clean();
    
    echo "<p class='info'>Resposta da API:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Tentar decodificar JSON
    $json = json_decode($output, true);
    if ($json) {
        echo "<p class='success'>✅ JSON válido</p>";
        echo "<p>Success: " . ($json['success'] ? 'true' : 'false') . "</p>";
        if (isset($json['data'])) {
            echo "<p>Total de itens: " . count($json['data']) . "</p>";
        }
        if (isset($json['message'])) {
            echo "<p>Mensagem: " . $json['message'] . "</p>";
        }
    } else {
        echo "<p class='error'>❌ JSON inválido</p>";
        echo "<p>Erro JSON: " . json_last_error_msg() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro na API: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Teste 3: Simular chamada da API para listar cenas
echo "<div class='test'>";
echo "<h2>🎭 Teste 3: Simulação API - Listar Cenas</h2>";

try {
    // Reset $_GET
    $_GET = ['action' => 'listar_cenas'];
    
    // Capturar output
    ob_start();
    include 'api/admin-cards.php';
    $output = ob_get_clean();
    
    echo "<p class='info'>Resposta da API:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Tentar decodificar JSON
    $json = json_decode($output, true);
    if ($json) {
        echo "<p class='success'>✅ JSON válido</p>";
        echo "<p>Success: " . ($json['success'] ? 'true' : 'false') . "</p>";
        if (isset($json['data'])) {
            echo "<p>Total de itens: " . count($json['data']) . "</p>";
        }
    } else {
        echo "<p class='error'>❌ JSON inválido</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro na API: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Teste 4: Testar via cURL
echo "<div class='test'>";
echo "<h2>🌐 Teste 4: Teste via cURL</h2>";

$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
$apiUrl = $baseUrl . '/api/admin-cards.php';

// Teste listar blocos
echo "<h3>Listar Blocos:</h3>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl . '?action=listar_blocos');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "<p class='error'>❌ Erro cURL: {$error}</p>";
} else {
    echo "<p class='info'>HTTP Code: {$httpCode}</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

// Teste listar cenas
echo "<h3>Listar Cenas:</h3>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl . '?action=listar_cenas');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "<p class='error'>❌ Erro cURL: {$error}</p>";
} else {
    echo "<p class='info'>HTTP Code: {$httpCode}</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

echo "</div>";

// Teste 5: Verificar logs de erro
echo "<div class='test'>";
echo "<h2>📋 Teste 5: Logs de Erro</h2>";

$errorLog = ini_get('error_log');
echo "<p>Log de erros PHP: " . ($errorLog ?: 'default') . "</p>";

// Verificar se há logs do admin-cards
$adminLog = 'logs/admin-cards.log';
if (file_exists($adminLog)) {
    echo "<p class='success'>✅ Log admin-cards encontrado</p>";
    $logContent = file_get_contents($adminLog);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -10); // Últimas 10 linhas
    
    echo "<p>Últimas entradas:</p>";
    echo "<pre>" . htmlspecialchars(implode("\n", $recentLines)) . "</pre>";
} else {
    echo "<p class='info'>ℹ️ Nenhum log admin-cards encontrado</p>";
}

echo "</div>";

// Teste 6: Verificar configurações
echo "<div class='test'>";
echo "<h2>⚙️ Teste 6: Configurações</h2>";

echo "<h3>Variáveis de Ambiente:</h3>";
require_once 'includes/Environment.php';

$configs = [
    'SUPABASE_URL' => Environment::get('SUPABASE_URL', 'NÃO DEFINIDA'),
    'SUPABASE_SERVICE_KEY' => Environment::get('SUPABASE_SERVICE_KEY', 'NÃO DEFINIDA') ? 'DEFINIDA' : 'NÃO DEFINIDA',
    'SUPABASE_ANON_KEY' => Environment::get('SUPABASE_ANON_KEY', 'NÃO DEFINIDA') ? 'DEFINIDA' : 'NÃO DEFINIDA'
];

foreach ($configs as $key => $value) {
    $class = ($value === 'NÃO DEFINIDA') ? 'error' : 'success';
    echo "<p class='{$class}'>{$key}: {$value}</p>";
}

echo "<h3>Configurações PHP:</h3>";
echo "<p>display_errors: " . ini_get('display_errors') . "</p>";
echo "<p>error_reporting: " . ini_get('error_reporting') . "</p>";
echo "<p>max_execution_time: " . ini_get('max_execution_time') . "</p>";

echo "</div>";

echo "<hr>";
echo "<p><small>Debug executado em: " . date('Y-m-d H:i:s') . "</small></p>";
?>