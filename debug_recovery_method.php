<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/EmailManager.php';

echo "<h1>Debug Método sendRecoveryCodeByEmail</h1>";

$email = 'mariocromia@gmail.com';
$codigo = '123456';
$nome = 'Mario';

echo "<p>Testando envio para: <strong>$email</strong></p>";
echo "<p>Código: <strong>$codigo</strong></p>";
echo "<p>Nome: <strong>$nome</strong></p>";

try {
    echo "<h2>Executando sendRecoveryCodeByEmail...</h2>";
    
    $result = EmailManager::sendRecoveryCodeByEmail($email, $codigo, $nome);
    
    if ($result) {
        echo "✅ <strong>SUCESSO!</strong> Método retornou true<br>";
    } else {
        echo "❌ <strong>FALHOU!</strong> Método retornou false<br>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>EXCEÇÃO:</strong> " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Verificando Logs</h2>";
echo "<p>Verifique os logs do PHP para mensagens detalhadas sobre tentativas de SMTP.</p>";
?>