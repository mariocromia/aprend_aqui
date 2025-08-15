<?php
ini_set('log_errors', 1);
ini_set('error_log', 'debug_email.log');
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste Contexto Web</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Debug - Contexto Web vs CLI</h1>
    
    <?php
    if (isset($_POST['test'])) {
        echo "<h2>Executando teste via WEB...</h2>";
        
        // Log de início
        error_log("=== INICIO TESTE WEB ===");
        
        try {
            require_once 'vendor/autoload.php';
            require_once 'includes/Environment.php';
            require_once 'includes/EmailManager.php';
            
            use PHPMailer\PHPMailer\PHPMailer;
            use PHPMailer\PHPMailer\Exception;
            
            Environment::load();
            
            echo "<h3>1. Informações do Ambiente</h3>";
            echo "PHP SAPI: " . php_sapi_name() . "<br>";
            echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "<br>";
            echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "<br>";
            echo "User: " . get_current_user() . "<br>";
            
            echo "<h3>2. Teste PHPMailer Direto</h3>";
            
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = Environment::get('SMTP_USERNAME');
            $mail->Password = Environment::get('SMTP_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            
            $mail->setFrom('contato@centroservice.com.br', 'Centro Service');
            $mail->addAddress('mariocromia@gmail.com', 'Mario');
            $mail->Subject = 'Teste WEB - ' . date('H:i:s');
            $mail->Body = 'Teste do contexto web';
            
            try {
                $directResult = $mail->send();
                if ($directResult) {
                    echo "✅ PHPMailer direto: <strong>SUCESSO</strong><br>";
                } else {
                    echo "❌ PHPMailer direto: <strong>FALHOU</strong><br>";
                }
            } catch (Exception $e) {
                echo "❌ PHPMailer Exceção: " . $e->getMessage() . "<br>";
                error_log("PHPMailer Exceção WEB: " . $e->getMessage());
            }
            
            echo "<h3>3. Teste EmailManager</h3>";
            
            try {
                $emailManagerResult = EmailManager::sendRecoveryCodeByEmail('mariocromia@gmail.com', '888888', 'Mario');
                
                if ($emailManagerResult) {
                    echo "✅ EmailManager: <strong>SUCESSO</strong><br>";
                } else {
                    echo "❌ EmailManager: <strong>FALHOU</strong><br>";
                }
            } catch (Exception $e) {
                echo "❌ EmailManager Exceção: " . $e->getMessage() . "<br>";
                error_log("EmailManager Exceção WEB: " . $e->getMessage());
            }
            
            echo "<h3>4. Logs de Debug</h3>";
            echo "<p>Verifique o arquivo <code>debug_email.log</code> para detalhes.</p>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>Erro geral: " . $e->getMessage() . "</p>";
            error_log("Erro geral WEB: " . $e->getMessage());
        }
        
        error_log("=== FIM TESTE WEB ===");
        
        echo "<hr>";
        echo "<h3>Comparação</h3>";
        echo "<p>• CLI: Sempre funciona ✅</p>";
        echo "<p>• WEB: Resultado acima</p>";
    }
    ?>
    
    <form method="post">
        <button type="submit" name="test" style="padding: 15px 30px; font-size: 16px; background: #007bff; color: white; border: none; border-radius: 5px;">
            🧪 Executar Teste via WEB
        </button>
    </form>
    
    <hr>
    <h2>Instruções</h2>
    <p>Este teste vai comparar o comportamento entre CLI e WEB.</p>
    <p>Se falhar aqui mas funcionar via CLI, o problema é configuração do servidor web.</p>
</body>
</html>