<?php
/**
 * Teste direto do SMTP sem usar EmailManager
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';
require_once 'includes/Environment.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h1>Teste Direto SMTP</h1>";

// Carregar configurações
Environment::load();

$smtp_host = Environment::get('SMTP_HOST');
$smtp_port = Environment::get('SMTP_PORT');
$smtp_username = Environment::get('SMTP_USERNAME');
$smtp_password = Environment::get('SMTP_PASSWORD');
$smtp_from_email = Environment::get('SMTP_FROM_EMAIL');
$smtp_from_name = Environment::get('SMTP_FROM_NAME');

echo "<h2>Configurações Carregadas:</h2>";
echo "Host: $smtp_host<br>";
echo "Port: $smtp_port<br>";
echo "Username: $smtp_username<br>";
echo "Password: " . (strlen($smtp_password) > 0 ? '***configurada*** (' . strlen($smtp_password) . ' chars)' : 'NÃO CONFIGURADA') . "<br>";
echo "From Email: $smtp_from_email<br>";
echo "From Name: $smtp_from_name<br><br>";

if (empty($smtp_password)) {
    echo "❌ <strong>ERRO:</strong> Senha SMTP não configurada!<br>";
    echo "Configure SMTP_PASSWORD no arquivo env.config<br>";
    exit;
}

echo "<h2>Teste de Envio:</h2>";

$mail = new PHPMailer(true);

try {
    // Configurações do servidor
    $mail->isSMTP();
    $mail->Host = $smtp_host;
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_username;
    $mail->Password = $smtp_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int)$smtp_port;
    $mail->CharSet = 'UTF-8';

    // Habilitar debug detalhado
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo "SMTP Debug [$level]: " . htmlspecialchars($str) . "<br>";
    };

    // Remetente
    $mail->setFrom($smtp_from_email, $smtp_from_name);

    // Destinatário (altere para um email real para teste)
    $email_teste = 'seu-email@exemplo.com';
    $mail->addAddress($email_teste, 'Teste');

    // Conteúdo
    $mail->isHTML(true);
    $mail->Subject = 'Teste SMTP - Centro Service';
    $mail->Body = '<h1>Teste de Email</h1><p>Este é um email de teste do sistema SMTP.</p>';
    $mail->AltBody = 'Teste de Email - Este é um email de teste do sistema SMTP.';

    echo "<h3>Tentando enviar email para: $email_teste</h3>";
    
    $result = $mail->send();
    
    if ($result) {
        echo "<h3>✅ Email enviado com SUCESSO!</h3>";
    } else {
        echo "<h3>❌ Falha no envio</h3>";
    }

} catch (Exception $e) {
    echo "<h3>❌ Erro: {$mail->ErrorInfo}</h3>";
    echo "<p>Exceção: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Possíveis Soluções:</h2>";
echo "<ol>";
echo "<li>Verificar se as credenciais estão corretas</li>";
echo "<li>Confirmar se o email contato@centroservice.com.br existe</li>";
echo "<li>Verificar se a autenticação de dois fatores não está bloqueando</li>";
echo "<li>Tentar porta 465 com SSL em vez de 587 com STARTTLS</li>";
echo "<li>Verificar logs do servidor web</li>";
echo "</ol>";
?>