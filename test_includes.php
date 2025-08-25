<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste de Includes</h1>";

echo "<h2>1. Testando Environment.php:</h2>";
try {
    require_once 'includes/Environment.php';
    echo "✅ Environment.php carregado com sucesso<br>";
    echo "APP_NAME: " . Environment::get('APP_NAME', 'default') . "<br>";
} catch (Exception $e) {
    echo "❌ Erro no Environment.php: " . $e->getMessage() . "<br>";
}

echo "<h2>2. Testando SupabaseClient.php:</h2>";
try {
    require_once 'includes/SupabaseClient.php';
    echo "✅ SupabaseClient.php carregado com sucesso<br>";
    
    // Tentar instanciar
    try {
        $supabase = new SupabaseClient();
        echo "✅ SupabaseClient instanciado com sucesso<br>";
    } catch (Exception $e) {
        echo "⚠️ Erro ao instanciar SupabaseClient: " . $e->getMessage() . "<br>";
        echo "Isso é normal se não há configuração do Supabase<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro no SupabaseClient.php: " . $e->getMessage() . "<br>";
}

echo "<h2>3. Testando SecurityConfig.php:</h2>";
try {
    require_once 'includes/SecurityConfig.php';
    echo "✅ SecurityConfig.php carregado com sucesso<br>";
} catch (Exception $e) {
    echo "❌ Erro no SecurityConfig.php: " . $e->getMessage() . "<br>";
}

echo "<h2>4. Verificando arquivos CSS/JS:</h2>";
$files_to_check = [
    'assets/css/prompt-builder.css',
    'assets/js/prompt-builder.js'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
    } else {
        echo "❌ $file não encontrado<br>";
    }
}

echo "<h2>5. Informações do PHP:</h2>";
echo "Versão PHP: " . PHP_VERSION . "<br>";
echo "Extensões carregadas: " . implode(', ', get_loaded_extensions()) . "<br>";

echo "<h2>6. Sessão Atual:</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
?>