<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/EmailManager.php';
require_once 'includes/Environment.php';

echo "<h1>Teste Direto de Email</h1>";

try {
    Environment::load();
    
    echo "<h2>1. Configurações SMTP</h2>";
    echo "Host: " . Environment::get('SMTP_HOST') . "<br>";
    echo "Port: " . Environment::get('SMTP_PORT') . "<br>";  
    echo "Username: " . Environment::get('SMTP_USERNAME') . "<br>";
    echo "Password length: " . strlen(Environment::get('SMTP_PASSWORD')) . "<br>";
    
    echo "<h2>2. Teste de Geração de Código</h2>";
    $code = EmailManager::generateResetCode('teste@exemplo.com', 'email');
    echo "Código gerado: " . ($code ? $code : 'FALHOU') . "<br>";
    
    if ($code) {
        echo "<h2>3. Teste de Envio de Email</h2>";
        $result = EmailManager::sendRecoveryCodeByEmail('teste@exemplo.com', $code, 'Teste User');
        echo "Email enviado: " . ($result ? '✅ SUCESSO' : '❌ FALHOU') . "<br>";
        
        if (!$result) {
            echo "<p>Verifique os logs para mais detalhes.</p>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>