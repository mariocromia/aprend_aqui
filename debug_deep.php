<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Capturar todos os erros e outputs
ob_start();
set_error_handler(function($severity, $message, $file, $line) {
    echo "<p style='color: orange;'>⚠️ <strong>PHP Warning:</strong> $message em $file linha $line</p>";
});

require_once 'vendor/autoload.php';
require_once 'includes/Environment.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h1>Debug Profundo - Falha de Email</h1>";

try {
    Environment::load();
    
    echo "<h2>1. Configurações</h2>";
    $smtp_host = Environment::get('SMTP_HOST');
    $smtp_port = Environment::get('SMTP_PORT');
    $smtp_username = Environment::get('SMTP_USERNAME');
    $smtp_password = Environment::get('SMTP_PASSWORD');
    $smtp_from_email = Environment::get('SMTP_FROM_EMAIL');
    $smtp_from_name = Environment::get('SMTP_FROM_NAME');
    
    echo "Host: $smtp_host<br>";
    echo "Port: $smtp_port<br>";
    echo "Username: $smtp_username<br>";
    echo "Password presente: " . (!empty($smtp_password) ? 'SIM' : 'NÃO') . "<br>";
    echo "Debug mode: " . Environment::get('DEBUG_MODE') . "<br>";
    
    echo "<h2>2. Teste Manual do PHPMailer</h2>";
    
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $smtp_host;
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_username;
    $mail->Password = $smtp_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int)$smtp_port;
    $mail->CharSet = 'UTF-8';
    
    // Debug mínimo
    $mail->SMTPDebug = SMTP::DEBUG_OFF; // Desativar debug por enquanto
    
    $mail->setFrom($smtp_from_email, $smtp_from_name);
    $mail->addAddress('mariocromia@gmail.com', 'Mario');
    $mail->Subject = 'Teste Debug Profundo - ' . date('H:i:s');
    $mail->Body = 'Teste direto do PHPMailer';
    
    echo "<p>Tentando enviar email direto via PHPMailer...</p>";
    
    $success = false;
    $error_message = '';
    
    try {
        $result = $mail->send();
        if ($result) {
            echo "<p style='color: green;'>✅ <strong>PHPMailer SUCESSO!</strong> Email enviado diretamente</p>";
            $success = true;
        } else {
            echo "<p style='color: red;'>❌ PHPMailer retornou false</p>";
            $error_message = 'PHPMailer retornou false';
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ <strong>Exceção PHPMailer:</strong> " . $e->getMessage() . "</p>";
        $error_message = $e->getMessage();
    }
    
    echo "<h2>3. Teste do Método EmailManager</h2>";
    
    if ($success) {
        echo "<p>Como PHPMailer funcionou, vamos testar EmailManager...</p>";
        
        require_once 'includes/EmailManager.php';
        
        $emailSent = EmailManager::sendRecoveryCodeByEmail('mariocromia@gmail.com', '999999', 'Mario');
        
        if ($emailSent) {
            echo "<p style='color: green;'>✅ <strong>EmailManager SUCESSO!</strong></p>";
        } else {
            echo "<p style='color: red;'>❌ <strong>EmailManager FALHOU!</strong></p>";
            echo "<p>Este é o problema! EmailManager não está funcionando mesmo com PHPMailer OK.</p>";
        }
    } else {
        echo "<p style='color: red;'>Como PHPMailer falhou, o problema é de conectividade/configuração.</p>";
        echo "<p><strong>Erro:</strong> $error_message</p>";
    }
    
    echo "<h2>4. Verificar Diferenças de Ambiente</h2>";
    echo "<p><strong>PHP SAPI:</strong> " . php_sapi_name() . "</p>";
    echo "<p><strong>User Agent:</strong> " . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "</p>";
    echo "<p><strong>Request Method:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'CLI') . "</p>";
    echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Exceção Geral:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

$output = ob_get_clean();
echo $output;
?>