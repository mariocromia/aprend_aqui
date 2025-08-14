<?php
/**
 * Script para testar o redirecionamento
 * Simula o processo de cadastro e verifica o redirecionamento
 */

// Simular dados de POST
$_POST = [
    'nome' => 'Teste Redirecionamento',
    'email' => 'teste_redirect_' . time() . '@exemplo.com',
    'senha' => 'Teste123!',
    'confirmar_senha' => 'Teste123!',
    'whatsapp' => '11999999999'
];

// Simular método POST
$_SERVER['REQUEST_METHOD'] = 'POST';

// Simular sessão
session_start();

// Incluir arquivos necessários
require_once 'includes/Environment.php';
require_once 'includes/CSRF.php';
require_once 'includes/Sanitizer.php';
require_once 'includes/EmailManager.php';
require_once 'includes/SupabaseClient.php';

echo "<h1>🔍 Teste de Redirecionamento</h1>\n";
echo "<pre>\n";

try {
    echo "1. Simulando processo de cadastro...\n";
    
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $whatsapp = Sanitizer::sanitizePhone($_POST['whatsapp'] ?? '');
    
    echo "   Nome: $nome\n";
    echo "   Email: $email\n";
    echo "   WhatsApp: $whatsapp\n";
    
    echo "\n2. Verificando validações...\n";
    
    // Validações
    $erro = false;
    
    if (strlen($nome) < 2) {
        echo "   ❌ Nome muito curto\n";
        $erro = true;
    } else {
        echo "   ✅ Nome válido\n";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "   ❌ Email inválido\n";
        $erro = true;
    } else {
        echo "   ✅ Email válido\n";
    }
    
    if (strlen($senha) < 8) {
        echo "   ❌ Senha muito curta\n";
        $erro = true;
    } else {
        echo "   ✅ Senha válida\n";
    }
    
    if ($senha !== $confirmar_senha) {
        echo "   ❌ Senhas não coincidem\n";
        $erro = true;
    } else {
        echo "   ✅ Senhas coincidem\n";
    }
    
    if ($erro) {
        echo "\n   ❌ Validações falharam\n";
        exit;
    }
    
    echo "\n3. Criando cliente Supabase...\n";
    $supabase = new SupabaseClient();
    echo "   ✅ Cliente criado com sucesso\n";
    
    echo "\n4. Verificando se email já existe...\n";
    $emailExiste = $supabase->emailExists($email);
    echo "   Email existe? " . ($emailExiste ? 'SIM' : 'NÃO') . "\n";
    
    if ($emailExiste) {
        echo "   ❌ Email já existe, não pode testar\n";
        exit;
    }
    
    echo "\n5. Preparando dados do usuário...\n";
    $codigoAtivacao = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    
    $userData = [
        'nome' => $nome,
        'email' => $email,
        'senha_hash' => $senhaHash,
        'whatsapp' => $whatsapp,
        'whatsapp_confirmado' => false,
        'codigo_ativacao' => $codigoAtivacao,
        'codigo_gerado_em' => date('c'),
        'ativo' => true,
        'email_verificado' => false,
        'criado_em' => date('c'),
        'ultimo_login' => null,
        'tentativas_login_falhadas' => 0,
        'conta_bloqueada_ate' => null
    ];
    
    echo "   Código de ativação: $codigoAtivacao\n";
    
    echo "\n6. Criando usuário no banco...\n";
    $usuarioCriado = $supabase->createUser($userData);
    
    if ($usuarioCriado) {
        echo "   ✅ Usuário criado com sucesso!\n";
        echo "   ID: " . ($usuarioCriado['id'] ?? 'N/A') . "\n";
        
        echo "\n7. Testando redirecionamento...\n";
        
        // Simular o redirecionamento
        $redirectUrl = 'confirmar-whatsapp.php?email=' . urlencode($email);
        echo "   URL de redirecionamento: $redirectUrl\n";
        
        // Verificar se o arquivo existe
        if (file_exists('auth/' . $redirectUrl)) {
            echo "   ✅ Arquivo de destino existe\n";
        } else {
            echo "   ❌ Arquivo de destino NÃO existe\n";
        }
        
        echo "\n8. Simulando envio de emails...\n";
        
        try {
            $welcomeResult = EmailManager::sendWelcomeEmail($email, $nome);
            echo "   ✅ Email de boas-vindas: " . ($welcomeResult ? 'Enviado' : 'Falhou') . "\n";
            
            $activationResult = EmailManager::sendActivationCode($email, $codigoAtivacao, $nome);
            echo "   ✅ Código de ativação: " . ($activationResult ? 'Enviado' : 'Falhou') . "\n";
            
        } catch (Exception $e) {
            echo "   ⚠️ Erro ao enviar emails: " . $e->getMessage() . "\n";
        }
        
        echo "\n9. Verificando usuário no banco...\n";
        $usuarioVerificado = $supabase->getUserByEmail($email);
        
        if ($usuarioVerificado) {
            echo "   ✅ Usuário encontrado no banco!\n";
            echo "   ID: " . $usuarioVerificado['id'] . "\n";
            echo "   Nome: " . $usuarioVerificado['nome'] . "\n";
            echo "   Email: " . $usuarioVerificado['email'] . "\n";
            echo "   Código de ativação: " . $usuarioVerificado['codigo_ativacao'] . "\n";
            
            echo "\n10. RESULTADO FINAL:\n";
            echo "   ✅ Usuário criado com sucesso\n";
            echo "   ✅ Redirecionamento preparado\n";
            echo "   ✅ URL: $redirectUrl\n";
            echo "   ✅ O usuário deveria ser redirecionado para esta página\n";
            
        } else {
            echo "   ❌ Usuário não foi encontrado no banco\n";
        }
        
    } else {
        echo "   ❌ Falha ao criar usuário\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n</pre>\n";
echo "<h2>📋 Resumo do Teste</h2>\n";
echo "<p>Se tudo funcionou acima, o problema pode ser:</p>\n";
echo "<ul>\n";
echo "<li>Cache do navegador</li>\n";
echo "<li>Problema com JavaScript</li>\n";
echo "<li>Erro no redirecionamento do navegador</li>\n";
echo "</ul>\n";
echo "<p><strong>Teste o cadastro no site novamente!</strong></p>\n";
?>
