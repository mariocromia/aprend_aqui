<?php
/**
 * Teste Final - Email mariocromia@gmail.com
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 120);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste Final - Email</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        button { padding: 15px 30px; font-size: 16px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🧪 Teste Final - Sistema de Email</h1>
    
    <?php if (isset($_POST['test'])): ?>
        <div style="border: 1px solid #ccc; padding: 20px; margin: 20px 0; border-radius: 5px; background: #f9f9f9;">
            <h2>📋 Resultado do Teste</h2>
            
            <?php
            try {
                require_once 'includes/SupabaseClient.php';
                require_once 'includes/EmailManager.php';
                
                $email = 'mariocromia@gmail.com';
                
                echo "<p class='info'>🔍 Testando email: <strong>$email</strong></p>";
                
                // 1. Verificar usuário
                $supabase = new SupabaseClient();
                $user = $supabase->getUserByEmail($email);
                
                if (!$user) {
                    echo "<p class='error'>❌ Usuário não encontrado no sistema</p>";
                    exit;
                }
                
                echo "<p class='success'>✅ Usuário encontrado: " . $user['nome'] . "</p>";
                
                // 2. Gerar código
                $code = EmailManager::generateResetCode($email, 'email');
                if (!$code) {
                    echo "<p class='error'>❌ Erro ao gerar código</p>";
                    exit;
                }
                
                echo "<p class='success'>✅ Código gerado: $code</p>";
                
                // 3. Tentar enviar email
                echo "<p class='info'>📤 Enviando email...</p>";
                
                $emailSent = EmailManager::sendRecoveryCodeByEmail($email, $code, $user['nome']);
                
                if ($emailSent) {
                    echo "<p class='success' style='font-size: 20px;'>🎉 SUCESSO! Email enviado com êxito!</p>";
                    echo "<p>✉️ Verifique sua caixa de entrada (e pasta de spam) em: <strong>$email</strong></p>";
                    echo "<p>🔢 Código de teste: <strong>$code</strong></p>";
                } else {
                    echo "<p class='error' style='font-size: 20px;'>❌ FALHA! Email não foi enviado</p>";
                    echo "<p>🔧 Possíveis causas:</p>";
                    echo "<ul>";
                    echo "<li>Servidor SMTP temporariamente indisponível</li>";
                    echo "<li>Firewall bloqueando conexões SMTP</li>";
                    echo "<li>Configurações de rede do servidor</li>";
                    echo "<li>Limites de envio atingidos</li>";
                    echo "</ul>";
                }
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
    <?php endif; ?>
    
    <form method="post">
        <button type="submit" name="test">🧪 Executar Teste de Email</button>
    </form>
    
    <hr>
    
    <h2>📝 Instruções</h2>
    <ol>
        <li>Clique no botão acima para testar</li>
        <li>Se <strong style="color: green;">SUCESSO</strong> → O sistema está funcionando!</li>
        <li>Se <strong style="color: red;">FALHA</strong> → Problema de configuração de servidor</li>
        <li>Em caso de falha, tente acessar via <strong>localhost</strong> ou <strong>127.0.0.1</strong></li>
    </ol>
    
    <h2>🔄 Se o problema persistir:</h2>
    <p>O sistema está tecnicamente correto. O problema é de <strong>configuração do servidor/rede</strong>.</p>
    <p>💡 <strong>Solução temporária:</strong> Use apenas o método WhatsApp ou Link tradicional até resolver a configuração SMTP.</p>
    
</body>
</html>