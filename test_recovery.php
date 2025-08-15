<?php
/**
 * Teste do sistema de recuperação de senha
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/EmailManager.php';
require_once 'includes/SupabaseClient.php';

echo "<h1>Teste de Recuperação de Senha</h1>";

$email_teste = 'teste@exemplo.com'; // Altere para um email real

echo "<h2>1. Teste de Geração de Token</h2>";
echo "Email: $email_teste<br>";

try {
    $token = EmailManager::generateResetToken(null, $email_teste);
    
    if ($token) {
        echo "✅ Token gerado: " . substr($token, 0, 10) . "...<br>";
        
        echo "<h2>2. Teste de Verificação de Token</h2>";
        $verification = EmailManager::verifyResetToken($token);
        
        if ($verification && $verification['valid']) {
            echo "✅ Token válido para: " . $verification['email'] . "<br>";
            
            echo "<h2>3. Teste de Envio de Email</h2>";
            $emailSent = EmailManager::sendPasswordReset($email_teste, $token);
            
            if ($emailSent) {
                echo "✅ Email de recuperação enviado<br>";
            } else {
                echo "❌ Falha no envio do email<br>";
            }
            
            echo "<h2>4. Teste de Reset de Senha</h2>";
            $newPassword = 'NovaSenha123!';
            $resetResult = EmailManager::resetPassword($token, $newPassword);
            
            if ($resetResult['success']) {
                echo "✅ " . $resetResult['message'] . "<br>";
            } else {
                echo "❌ " . $resetResult['message'] . "<br>";
            }
            
        } else {
            echo "❌ Token inválido<br>";
        }
        
    } else {
        echo "❌ Falha ao gerar token<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>Instruções</h2>";
echo "<ol>";
echo "<li>Execute este teste após criar a tabela password_reset_tokens no Supabase</li>";
echo "<li>Altere o email de teste para um email real se quiser receber o email</li>";
echo "<li>Verifique os logs do sistema para detalhes dos erros</li>";
echo "</ol>";

echo "<h2>SQL para criar a tabela</h2>";
echo "<pre>";
echo htmlspecialchars(file_get_contents('docs/create_password_reset_table.sql'));
echo "</pre>";
?>