<?php
/**
 * Teste do sistema de recuperação dual (Email + WhatsApp)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/EmailManager.php';
require_once 'includes/SupabaseClient.php';

echo "<h1>Teste do Sistema de Recuperação Dual</h1>";

// Use um email real aqui para teste
$email_teste = 'teste@exemplo.com';
$whatsapp_teste = '+5511999999999';

try {
    $supabase = new SupabaseClient();
    
    echo "<h2>1. Verificar/Criar usuário de teste</h2>";
    
    $user = $supabase->getUserByEmail($email_teste);
    if (!$user) {
        echo "Criando usuário de teste...<br>";
        $userData = [
            'nome' => 'Teste Recovery Dual',
            'email' => $email_teste,
            'senha' => password_hash('senhaOriginal123', PASSWORD_DEFAULT),
            'whatsapp' => $whatsapp_teste,
            'whatsapp_confirmado' => true,
            'ativo' => true,
            'email_verificado' => true,
            'criado_em' => date('c')
        ];
        
        $created = $supabase->createUser($userData);
        if ($created) {
            echo "✅ Usuário criado com sucesso<br>";
            $user = $supabase->getUserByEmail($email_teste);
        } else {
            echo "❌ Falha ao criar usuário<br>";
            exit;
        }
    } else {
        echo "✅ Usuário já existe: " . $user['nome'] . "<br>";
    }
    
    echo "<h2>2. Teste de Recuperação por Email (Código)</h2>";
    
    // Gerar código para email
    $emailCode = EmailManager::generateResetCode($email_teste, 'email');
    if ($emailCode) {
        echo "✅ Código para email gerado: $emailCode<br>";
        
        // Enviar código por email
        $emailSent = EmailManager::sendRecoveryCodeByEmail($email_teste, $emailCode, $user['nome']);
        echo "Email enviado: " . ($emailSent ? '✅ SIM' : '❌ NÃO') . "<br>";
        
        // Verificar código
        $emailVerification = EmailManager::verifyResetCode($email_teste, $emailCode);
        echo "Código válido: " . ($emailVerification['valid'] ? '✅ SIM' : '❌ NÃO') . "<br>";
        
    } else {
        echo "❌ Falha ao gerar código para email<br>";
    }
    
    echo "<h2>3. Teste de Recuperação por WhatsApp</h2>";
    
    // Gerar código para WhatsApp
    $whatsappCode = EmailManager::generateResetCode($email_teste, 'whatsapp');
    if ($whatsappCode) {
        echo "✅ Código para WhatsApp gerado: $whatsappCode<br>";
        
        // Enviar código por WhatsApp
        $whatsappSent = EmailManager::sendRecoveryCodeByWhatsApp($user['whatsapp'], $whatsappCode, $user['nome']);
        echo "WhatsApp enviado: " . ($whatsappSent ? '✅ SIM' : '❌ NÃO') . "<br>";
        
        // Verificar código
        $whatsappVerification = EmailManager::verifyResetCode($email_teste, $whatsappCode);
        echo "Código válido: " . ($whatsappVerification['valid'] ? '✅ SIM' : '❌ NÃO') . "<br>";
        
    } else {
        echo "❌ Falha ao gerar código para WhatsApp<br>";
    }
    
    echo "<h2>4. Teste de Reset de Senha com Código</h2>";
    
    if (isset($emailCode)) {
        $newPassword = 'NovaSenha123!';
        $resetResult = EmailManager::resetPasswordWithCode($email_teste, $emailCode, $newPassword);
        
        if ($resetResult['success']) {
            echo "✅ " . $resetResult['message'] . "<br>";
            
            // Verificar se senha foi alterada
            $userUpdated = $supabase->getUserByEmail($email_teste);
            $passwordMatch = password_verify($newPassword, $userUpdated['senha']);
            echo "Nova senha confere: " . ($passwordMatch ? '✅ SIM' : '❌ NÃO') . "<br>";
            
        } else {
            echo "❌ " . $resetResult['message'] . "<br>";
        }
    }
    
    echo "<h2>5. Teste do Sistema Tradicional (Token)</h2>";
    
    // Gerar token tradicional
    $token = EmailManager::generateResetToken(null, $email_teste);
    if ($token) {
        echo "✅ Token tradicional gerado: " . substr($token, 0, 15) . "...<br>";
        
        // Enviar email com link
        $linkEmailSent = EmailManager::sendPasswordReset($email_teste, $token);
        echo "Email com link enviado: " . ($linkEmailSent ? '✅ SIM' : '❌ NÃO') . "<br>";
        
        // Verificar token
        $tokenVerification = EmailManager::verifyResetToken($token);
        echo "Token válido: " . ($tokenVerification['valid'] ? '✅ SIM' : '❌ NÃO') . "<br>";
        
    } else {
        echo "❌ Falha ao gerar token tradicional<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Resumo dos Métodos Implementados</h2>";
echo "<ul>";
echo "<li><strong>Email com Código:</strong> Código de 6 dígitos enviado por email (10 min)</li>";
echo "<li><strong>WhatsApp:</strong> Código de 6 dígitos enviado via WhatsApp (10 min)</li>";
echo "<li><strong>Email com Link:</strong> Link tradicional enviado por email (1 hora)</li>";
echo "</ul>";

echo "<h2>Como Testar Manualmente</h2>";
echo "<ol>";
echo "<li>Acesse a página de login</li>";
echo "<li>Clique em 'Esqueci minha senha'</li>";
echo "<li>Digite seu email</li>";
echo "<li>Escolha o método: Email (código), WhatsApp ou Email (link)</li>";
echo "<li>Siga as instruções recebidas</li>";
echo "</ol>";

echo "<h2>Tabelas Necessárias</h2>";
echo "<p>Execute no Supabase:</p>";
echo "<ul>";
echo "<li><code>docs/create_password_reset_tokens_table.sql</code> (para tokens tradicionais)</li>";
echo "<li><code>docs/create_password_reset_codes_table.sql</code> (para códigos temporários)</li>";
echo "</ul>";
?>