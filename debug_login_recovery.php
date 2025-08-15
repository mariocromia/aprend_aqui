<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/SupabaseClient.php';
require_once 'includes/EmailManager.php';

echo "<h1>Debug Login Recovery Process</h1>";

// Simular o mesmo fluxo do login.php
$email = 'teste@exemplo.com';

echo "<h2>1. Verificar se usuário existe</h2>";
try {
    $supabase = new SupabaseClient();
    $user = $supabase->getUserByEmail($email);
    
    if ($user) {
        echo "✅ Usuário encontrado: " . $user['nome'] . "<br>";
        
        echo "<h2>2. Gerar código de recuperação</h2>";
        $code = EmailManager::generateResetCode($email, 'email');
        
        if ($code) {
            echo "✅ Código gerado: $code<br>";
            
            echo "<h2>3. Enviar email com código</h2>";
            $emailSent = EmailManager::sendRecoveryCodeByEmail($email, $code, $user['nome']);
            
            if ($emailSent) {
                echo "✅ Email enviado com sucesso!<br>";
                echo "<p>O usuário seria redirecionado para: confirmar-recuperacao.php?email=" . urlencode($email) . "&method=email</p>";
            } else {
                echo "❌ <strong>ERRO: Email não foi enviado!</strong><br>";
                echo "Esta é a mensagem que o usuário vê: 'Erro ao enviar código por email. Tente novamente mais tarde.'<br>";
            }
            
        } else {
            echo "❌ Erro ao gerar código<br>";
        }
        
    } else {
        echo "❌ Usuário não encontrado<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Exceção: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Verificar Logs do Sistema</h2>";
echo "<p>Verifique os logs do PHP para mais detalhes.</p>";
?>