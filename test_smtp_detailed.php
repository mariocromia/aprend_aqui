<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';
require_once 'includes/Environment.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h1>Teste Detalhado de SMTP</h1>";

try {
    Environment::load();
    
    echo "<h2>1. Configurações Carregadas</h2>";
    $smtp_host = Environment::get('SMTP_HOST');
    $smtp_port = Environment::get('SMTP_PORT');
    $smtp_username = Environment::get('SMTP_USERNAME');
    $smtp_password = Environment::get('SMTP_PASSWORD');
    $smtp_from_email = Environment::get('SMTP_FROM_EMAIL');
    $smtp_from_name = Environment::get('SMTP_FROM_NAME');
    
    echo "Host: <strong>$smtp_host</strong><br>";
    echo "Port: <strong>$smtp_port</strong><br>";
    echo "Username: <strong>$smtp_username</strong><br>";
    echo "Password: <strong>" . (strlen($smtp_password) > 0 ? str_repeat('*', strlen($smtp_password)) : 'VAZIO!') . "</strong><br>";
    echo "From Email: <strong>$smtp_from_email</strong><br>";
    echo "From Name: <strong>$smtp_from_name</strong><br>";
    
    if (empty($smtp_password)) {
        echo "❌ <strong>PROBLEMA CRÍTICO:</strong> Senha SMTP está vazia!<br>";
        exit;
    }
    
    echo "<h2>2. Teste de Conexão SMTP</h2>";
    
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $smtp_host;
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_username;
    $mail->Password = $smtp_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    
    // Ativar debug detalhado
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo "SMTP [$level]: " . htmlspecialchars($str) . "<br>";
    };
    
    $mail->setFrom($smtp_from_email, $smtp_from_name);
    $mail->addAddress('mariocromia@gmail.com', 'Mario');
    $mail->Subject = 'Teste SMTP - ' . date('H:i:s');
    $mail->Body = 'Este é um teste de envio de email via SMTP.';
    
    echo "<h3>Tentando enviar email de teste...</h3>";
    
    if ($mail->send()) {
        echo "✅ <strong>SUCESSO!</strong> Email enviado via SMTP<br>";
    } else {
        echo "❌ <strong>FALHOU:</strong> " . $mail->ErrorInfo . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>EXCEÇÃO SMTP:</strong> " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>3. Teste com Porta Alternativa (465)</h2>";

try {
    $mail2 = new PHPMailer(true);
    $mail2->isSMTP();
    $mail2->Host = $smtp_host;
    $mail2->SMTPAuth = true;
    $mail2->Username = $smtp_username;
    $mail2->Password = $smtp_password;
    $mail2->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // SSL
    $mail2->Port = 465;
    $mail2->CharSet = 'UTF-8';
    
    $mail2->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail2->Debugoutput = function($str, $level) {
        echo "SMTP-465 [$level]: " . htmlspecialchars($str) . "<br>";
    };
    
    $mail2->setFrom($smtp_from_email, $smtp_from_name);
    $mail2->addAddress('mariocromia@gmail.com', 'Mario');
    $mail2->Subject = 'Teste SMTP 465 - ' . date('H:i:s');
    $mail2->Body = 'Este é um teste via porta 465.';
    
    echo "<h3>Tentando porta 465...</h3>";
    
    if ($mail2->send()) {
        echo "✅ <strong>SUCESSO na porta 465!</strong><br>";
    } else {
        echo "❌ <strong>FALHOU na porta 465:</strong> " . $mail2->ErrorInfo . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>EXCEÇÃO na porta 465:</strong> " . $e->getMessage() . "<br>";
}
?>