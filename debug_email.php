<?php
/**
 * Debug detalhado do sistema de email
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/Environment.php';
require_once 'includes/EmailManager.php';

echo "<h1>Debug do Sistema de Email</h1>";

// Teste 1: Verificar configurações
echo "<h2>1. Configurações Carregadas</h2>";
Environment::load();

$configs = [
    'SMTP_HOST' => Environment::get('SMTP_HOST'),
    'SMTP_PORT' => Environment::get('SMTP_PORT'),
    'SMTP_USERNAME' => Environment::get('SMTP_USERNAME'),
    'SMTP_PASSWORD' => Environment::get('SMTP_PASSWORD') ? '***configurada***' : 'NÃO CONFIGURADA',
    'SMTP_FROM_EMAIL' => Environment::get('SMTP_FROM_EMAIL'),
    'SMTP_FROM_NAME' => Environment::get('SMTP_FROM_NAME')
];

echo "<table border='1'>";
foreach ($configs as $key => $value) {
    $status = !empty($value) ? '✅' : '❌';
    echo "<tr><td>$key</td><td>$value</td><td>$status</td></tr>";
}
echo "</table>";

// Teste 2: Verificar PHPMailer
echo "<h2>2. Verificação do PHPMailer</h2>";
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "✅ PHPMailer carregado com sucesso<br>";
} else {
    echo "❌ PHPMailer NÃO encontrado<br>";
}

// Teste 3: Teste de conexão SMTP básica
echo "<h2>3. Teste de Conexão SMTP</h2>";
try {
    $host = Environment::get('SMTP_HOST');
    $port = Environment::get('SMTP_PORT');
    
    echo "Testando conexão para $host:$port...<br>";
    
    $connection = @fsockopen($host, $port, $errno, $errstr, 10);
    if ($connection) {
        echo "✅ Conexão TCP estabelecida com sucesso<br>";
        fclose($connection);
    } else {
        echo "❌ Falha na conexão TCP: $errstr ($errno)<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro no teste de conexão: " . $e->getMessage() . "<br>";
}

// Teste 4: Teste de envio real
echo "<h2>4. Teste de Envio Real</h2>";

$email_teste = 'teste@exemplo.com'; // Altere para um email real para teste
$codigo = rand(100000, 999999);

echo "Tentando enviar código de ativação para: $email_teste<br>";
echo "Código gerado: $codigo<br><br>";

try {
    $result = EmailManager::sendActivationCode($email_teste, $codigo, 'Usuario Teste');
    
    if ($result) {
        echo "✅ Email enviado com sucesso!<br>";
    } else {
        echo "❌ Falha no envio do email<br>";
    }
} catch (Exception $e) {
    echo "❌ Exceção durante envio: " . $e->getMessage() . "<br>";
}

// Teste 5: Verificar logs de erro
echo "<h2>5. Logs de Erro Recentes</h2>";
$log_file = ini_get('error_log');
if ($log_file && file_exists($log_file)) {
    echo "Arquivo de log: $log_file<br>";
    $logs = file_get_contents($log_file);
    $recent_logs = array_slice(explode("\n", $logs), -20);
    echo "<pre>" . implode("\n", $recent_logs) . "</pre>";
} else {
    echo "Arquivo de log não encontrado ou não configurado<br>";
    echo "Logs podem estar em: /var/log/apache2/error.log ou /var/log/php_errors.log<br>";
}

echo "<hr>";
echo "<p><strong>Próximos passos se houver problemas:</strong></p>";
echo "<ol>";
echo "<li>Verificar se a senha SMTP está correta</li>";
echo "<li>Confirmar se o firewall permite conexões na porta 587</li>";
echo "<li>Testar com uma conta de email real no lugar de teste@exemplo.com</li>";
echo "<li>Verificar logs do servidor web</li>";
echo "</ol>";
?>