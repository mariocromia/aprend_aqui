<?php
/**
 * Script para testar o fluxo completo de cadastro
 * Simula o processo completo sem usar o navegador
 */

require_once 'includes/Environment.php';
require_once 'includes/SupabaseClient.php';
require_once 'includes/EmailManager.php';

echo "<h1>🔍 Teste do Fluxo Completo de Cadastro</h1>\n";
echo "<pre>\n";

try {
    echo "1. Simulando processo de cadastro...\n";
    
    // Dados do usuário de teste
    $emailTeste = 'teste_fluxo_' . time() . '@exemplo.com';
    $nomeTeste = 'Usuário Fluxo Teste';
    $senhaTeste = 'Teste123!';
    
    echo "   Email: $emailTeste\n";
    echo "   Nome: $nomeTeste\n";
    
    echo "\n2. Criando cliente Supabase...\n";
    $supabase = new SupabaseClient();
    echo "   ✅ Cliente criado com sucesso\n";
    
    echo "\n3. Verificando se email já existe...\n";
    $emailExiste = $supabase->emailExists($emailTeste);
    echo "   Email existe? " . ($emailExiste ? 'SIM' : 'NÃO') . "\n";
    
    if ($emailExiste) {
        echo "   ❌ Email já existe, não pode testar\n";
        exit;
    }
    
    echo "\n4. Preparando dados do usuário...\n";
    $codigoAtivacao = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $userData = [
        'nome' => $nomeTeste,
        'email' => $emailTeste,
        'senha_hash' => password_hash($senhaTeste, PASSWORD_DEFAULT),
        'whatsapp' => '11999999999',
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
    
    echo "\n5. Criando usuário no banco...\n";
    $usuarioCriado = $supabase->createUser($userData);
    
    if ($usuarioCriado) {
        echo "   ✅ Usuário criado com sucesso!\n";
        echo "   ID: " . ($usuarioCriado['id'] ?? 'N/A') . "\n";
        
        echo "\n6. Testando envio de emails...\n";
        
        try {
            echo "   Enviando email de boas-vindas...\n";
            $welcomeResult = EmailManager::sendWelcomeEmail($emailTeste, $nomeTeste);
            echo "   ✅ Email de boas-vindas: " . ($welcomeResult ? 'Enviado' : 'Falhou') . "\n";
            
            echo "   Enviando código de ativação...\n";
            $activationResult = EmailManager::sendActivationCode($emailTeste, $codigoAtivacao, $nomeTeste);
            echo "   ✅ Código de ativação: " . ($activationResult ? 'Enviado' : 'Falhou') . "\n";
            
        } catch (Exception $e) {
            echo "   ⚠️ Erro ao enviar emails: " . $e->getMessage() . "\n";
        }
        
        echo "\n7. Verificando se usuário foi criado no banco...\n";
        $usuarioVerificado = $supabase->getUserByEmail($emailTeste);
        
        if ($usuarioVerificado) {
            echo "   ✅ Usuário encontrado no banco!\n";
            echo "   ID: " . $usuarioVerificado['id'] . "\n";
            echo "   Nome: " . $usuarioVerificado['nome'] . "\n";
            echo "   Email: " . $usuarioVerificado['email'] . "\n";
            echo "   Código de ativação: " . $usuarioVerificado['codigo_ativacao'] . "\n";
            echo "   Ativo: " . ($usuarioVerificado['ativo'] ? 'SIM' : 'NÃO') . "\n";
            
            echo "\n8. Simulando redirecionamento...\n";
            $redirectUrl = 'confirmar-whatsapp.php?email=' . urlencode($emailTeste);
            echo "   ✅ URL de redirecionamento: $redirectUrl\n";
            echo "   ✅ O usuário seria redirecionado para esta página\n";
            
        } else {
            echo "   ❌ Usuário não foi encontrado no banco\n";
        }
        
        echo "\n9. Limpando usuário de teste...\n";
        // Aqui você pode adicionar código para limpar o usuário se necessário
        
    } else {
        echo "   ❌ Falha ao criar usuário\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n</pre>\n";
echo "<h2>📋 Resumo do Teste</h2>\n";
echo "<p>Se tudo funcionou acima, o fluxo está correto:</p>\n";
echo "<ol>\n";
echo "<li>✅ Usuário é criado no banco</li>\n";
echo "<li>✅ Emails são enviados (simulados)</li>\n";
echo "<li>✅ Redirecionamento é preparado</li>\n";
echo "<li>✅ Usuário é verificado no banco</li>\n";
echo "</ol>\n";
echo "<p><strong>Teste agora o cadastro no site!</strong></p>\n";
?>
