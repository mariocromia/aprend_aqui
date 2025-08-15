<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/SupabaseClient.php';
require_once 'includes/EmailManager.php';

echo "<h1>Teste Específico - mariocromia@gmail.com</h1>";

$email = 'mariocromia@gmail.com';

try {
    $supabase = new SupabaseClient();
    
    echo "<h2>1. Verificar usuário</h2>";
    $user = $supabase->getUserByEmail($email);
    
    if (!$user) {
        echo "❌ Email não encontrado<br>";
        exit;
    }
    
    echo "✅ Usuário: " . $user['nome'] . "<br>";
    
    echo "<h2>2. Gerar código</h2>";
    $code = EmailManager::generateResetCode($email, 'email');
    
    if (!$code) {
        echo "❌ Erro ao gerar código<br>";
        exit;
    }
    
    echo "✅ Código: $code<br>";
    
    echo "<h2>3. Enviar email</h2>";
    
    // Ativar logs detalhados
    error_log("=== INÍCIO DO TESTE PARA MARIO ===");
    
    $emailSent = EmailManager::sendRecoveryCodeByEmail($email, $code, $user['nome']);
    
    error_log("=== FIM DO TESTE PARA MARIO ===");
    
    if ($emailSent) {
        echo "✅ <strong>Email enviado com SUCESSO!</strong><br>";
        echo "<p>Verifique sua caixa de entrada: $email</p>";
    } else {
        echo "❌ <strong>Email NÃO foi enviado!</strong><br>";
        echo "<p>Isso é exatamente o problema que você está vendo.</p>";
    }
    
} catch (Exception $e) {
    echo "❌ Exceção: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Conclusão:</strong> Se este teste passou mas o sistema continua falhando,<br>";
echo "o problema pode estar no contexto do navegador ou em cache de sessão.</p>";
?>