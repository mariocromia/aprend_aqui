<?php
// Arquivo de teste para diagnosticar problemas
echo "Teste básico de PHP funcionando!<br>";
echo "Versão do PHP: " . phpversion() . "<br>";
echo "Data e hora: " . date('d/m/Y H:i:s') . "<br>";
echo "Diretório atual: " . __DIR__ . "<br>";
echo "Servidor: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

// Testar se os arquivos existem
$files = [
    'index.php',
    'assets/css/style.css',
    'assets/js/main.js',
    'process_contact.php',
    'config/environment.php',
    'config/database.php'
];

echo "<h3>Verificação de arquivos:</h3>";
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ $file - Existe<br>";
    } else {
        echo "✗ $file - NÃO existe<br>";
    }
}

// Testar configurações do servidor
echo "<h3>Configurações do servidor:</h3>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";

// Testar se há erros de sintaxe
echo "<h3>Verificação de sintaxe:</h3>";
$syntax_check = shell_exec('php -l index.php 2>&1');
if (strpos($syntax_check, 'No syntax errors') !== false) {
    echo "✓ index.php - Sintaxe OK<br>";
} else {
    echo "✗ index.php - Erro de sintaxe:<br>";
    echo "<pre>" . htmlspecialchars($syntax_check) . "</pre>";
}

// Testar se há erros de sintaxe no process_contact.php
$syntax_check2 = shell_exec('php -l process_contact.php 2>&1');
if (strpos($syntax_check2, 'No syntax errors') !== false) {
    echo "✓ process_contact.php - Sintaxe OK<br>";
} else {
    echo "✗ process_contact.php - Erro de sintaxe:<br>";
    echo "<pre>" . htmlspecialchars($syntax_check2) . "</pre>";
}
?>
