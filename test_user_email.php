<?php
/**
 * Teste específico para verificar recuperação de senha para email do usuário
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/SupabaseClient.php';
require_once 'includes/EmailManager.php';

echo "<h1>Teste de Recuperação para Seu Email</h1>";

// Verificar se foi enviado um email via GET/POST
$email = $_GET['email'] ?? $_POST['email'] ?? '';

if (empty($email)) {
    echo "<form method='post'>";
    echo "<p><strong>Digite seu email para testar:</strong></p>";
    echo "<input type='email' name='email' required placeholder='seu@email.com' style='padding: 10px; width: 300px;'>";
    echo "<button type='submit' style='padding: 10px 20px; margin-left: 10px;'>Testar</button>";
    echo "</form>";
    exit;
}

echo "<h2>Testando recuperação para: <strong>$email</strong></h2>";

try {
    $supabase = new SupabaseClient();
    
    echo "<h3>1. Verificar se o email existe no sistema</h3>";
    $user = $supabase->getUserByEmail($email);
    
    if (!$user) {
        echo "❌ <strong>PROBLEMA ENCONTRADO!</strong><br>";
        echo "Este email não existe no sistema de usuários.<br>";
        echo "Para corrigir: cadastre-se primeiro ou use um email já cadastrado.<br>";
        exit;
    }
    
    echo "✅ Email encontrado no sistema<br>";
    echo "Nome: " . $user['nome'] . "<br>";
    echo "Ativo: " . ($user['ativo'] ? 'Sim' : 'Não') . "<br>";
    
    echo "<h3>2. Tentar gerar código de recuperação</h3>";
    $code = EmailManager::generateResetCode($email, 'email');
    
    if (!$code) {
        echo "❌ <strong>PROBLEMA:</strong> Falha ao gerar código de recuperação<br>";
        echo "Verifique se as tabelas de recuperação estão criadas no banco.<br>";
        exit;
    }
    
    echo "✅ Código gerado: <strong>$code</strong><br>";
    
    echo "<h3>3. Tentar enviar email com código</h3>";
    $emailSent = EmailManager::sendRecoveryCodeByEmail($email, $code, $user['nome']);
    
    if (!$emailSent) {
        echo "❌ <strong>PROBLEMA:</strong> Falha ao enviar email<br>";
        echo "Possíveis causas:<br>";
        echo "- Configurações SMTP incorretas<br>";
        echo "- Email de destino inválido<br>";
        echo "- Servidor SMTP temporariamente indisponível<br>";
        echo "- Limites de envio atingidos<br>";
    } else {
        echo "✅ <strong>Email enviado com sucesso!</strong><br>";
        echo "Verifique sua caixa de entrada (e spam) em: <strong>$email</strong><br>";
        echo "<p>Se tudo funcionou, o problema pode estar na interface de login.</p>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>ERRO:</strong> " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>Instruções:</h3>";
echo "<ol>";
echo "<li>Se o email foi enviado com sucesso aqui, o sistema está funcionando</li>";
echo "<li>Se não funcionou, verifique se o email existe no sistema</li>";
echo "<li>Para cadastrar um novo usuário de teste, acesse: <a href='auth/cadastro.php'>cadastro.php</a></li>";
echo "<li>Para testar com outro email: <a href='?email='>clique aqui</a></li>";
echo "</ol>";
?>