<?php
/**
 * Correção para problema de contexto web vs CLI
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Aumentar limites para contexto web
ini_set('max_execution_time', 120);
ini_set('memory_limit', '256M');
set_time_limit(120);

require_once 'includes/EmailManager.php';

echo "<h1>Teste com Configurações Otimizadas</h1>";

if (isset($_POST['send_email'])) {
    $email = 'mariocromia@gmail.com';
    
    echo "<h2>Enviando com configurações otimizadas...</h2>";
    
    try {
        // Forçar configurações para contexto web
        $result = EmailManager::sendRecoveryCodeByEmail($email, '777777', 'Mario Test');
        
        if ($result) {
            echo "<p style='color: green; font-size: 20px;'>✅ <strong>SUCESSO!</strong> Email enviado</p>";
            echo "<p>Verifique sua caixa de entrada: $email</p>";
        } else {
            echo "<p style='color: red; font-size: 20px;'>❌ <strong>FALHOU!</strong> Ainda não funciona</p>";
            
            // Tentar diagnóstico mais específico
            echo "<h3>Diagnóstico Adicional:</h3>";
            
            // Verificar se as funções existem
            if (!function_exists('curl_init')) {
                echo "❌ cURL não está disponível<br>";
            } else {
                echo "✅ cURL está disponível<br>";
            }
            
            if (!function_exists('openssl_encrypt')) {
                echo "❌ OpenSSL não está disponível<br>";
            } else {
                echo "✅ OpenSSL está disponível<br>";
            }
            
            // Testar conectividade básica
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10
                ]
            ]);
            
            $test_connection = @file_get_contents('https://www.google.com', false, $context);
            if ($test_connection !== false) {
                echo "✅ Conectividade internet OK<br>";
            } else {
                echo "❌ Sem conectividade internet<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Exceção: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}
?>

<form method="post">
    <button type="submit" name="send_email" style="padding: 15px 30px; font-size: 18px; background: #28a745; color: white; border: none; border-radius: 5px;">
        📧 Tentar Enviar Email (Configurações Otimizadas)
    </button>
</form>

<hr>

<h2>Possíveis Soluções se Continuar Falhando:</h2>

<ol>
    <li><strong>Firewall/Proxy:</strong> Servidor pode estar bloqueando SMTP</li>
    <li><strong>Configurações PHP:</strong> allow_url_fopen pode estar desabilitado</li>
    <li><strong>Limites de Servidor:</strong> Timeout muito baixo</li>
    <li><strong>Permissões:</strong> Apache pode não ter permissão para conexões externas</li>
</ol>

<h3>Verificar Configurações PHP:</h3>
<?php
echo "<p><strong>allow_url_fopen:</strong> " . (ini_get('allow_url_fopen') ? 'ON' : 'OFF') . "</p>";
echo "<p><strong>max_execution_time:</strong> " . ini_get('max_execution_time') . "s</p>";
echo "<p><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>default_socket_timeout:</strong> " . ini_get('default_socket_timeout') . "s</p>";
?>
