<?php
/**
 * Teste do sistema de email
 */

require_once 'includes/EmailManager.php';

// Definir senha SMTP para teste (remover em produção)
$_ENV['SMTP_PASSWORD'] = 'SUA_SENHA_AQUI'; // Substitua pela senha real

echo "<h1>Teste do Sistema de Email</h1>";

// Teste 1: Email de boas-vindas
echo "<h2>Teste 1: Email de Boas-vindas</h2>";
$result1 = EmailManager::sendWelcomeEmail('teste@exemplo.com', 'João da Silva');
echo $result1 ? "✅ Email de boas-vindas enviado com sucesso<br>" : "❌ Erro ao enviar email de boas-vindas<br>";

// Teste 2: Código de ativação
echo "<h2>Teste 2: Código de Ativação</h2>";
$codigo = rand(100000, 999999);
$result2 = EmailManager::sendActivationCode('teste@exemplo.com', $codigo, 'João da Silva');
echo $result2 ? "✅ Código de ativação enviado com sucesso<br>" : "❌ Erro ao enviar código de ativação<br>";

// Teste 3: Recuperação de senha
echo "<h2>Teste 3: Recuperação de Senha</h2>";
$token = bin2hex(random_bytes(32));
$result3 = EmailManager::sendPasswordReset('teste@exemplo.com', $token);
echo $result3 ? "✅ Email de recuperação enviado com sucesso<br>" : "❌ Erro ao enviar email de recuperação<br>";

echo "<hr>";
echo "<p><strong>Configurações SMTP:</strong></p>";
echo "<ul>";
echo "<li>Host: smtp.hostinger.com</li>";
echo "<li>Porta: 587</li>";
echo "<li>Email: contato@centroservice.com.br</li>";
echo "<li>Segurança: STARTTLS</li>";
echo "</ul>";

echo "<p><strong>Nota:</strong> Para testar em produção, defina a variável de ambiente SMTP_PASSWORD com a senha real do email.</p>";
?>