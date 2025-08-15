<?php
/**
 * Debug específico para atualização de senha
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/SupabaseClient.php';

echo "<h1>Debug - Atualização de Senha</h1>";

$email_teste = 'teste@exemplo.com';

try {
    $supabase = new SupabaseClient();
    
    echo "<h2>1. Verificar se email existe na tabela usuarios</h2>";
    $userExists = $supabase->emailExists($email_teste);
    echo "Email $email_teste existe: " . ($userExists ? '✅ SIM' : '❌ NÃO') . "<br>";
    
    if (!$userExists) {
        echo "<h2>2. Criando usuário de teste</h2>";
        $userData = [
            'nome' => 'Usuário Teste',
            'email' => $email_teste,
            'senha' => password_hash('senhaAntiga123', PASSWORD_DEFAULT),
            'whatsapp' => '+5511999999999',
            'whatsapp_confirmado' => true,
            'ativo' => true,
            'email_verificado' => false,
            'criado_em' => date('c')
        ];
        
        $createResult = $supabase->createUser($userData);
        echo "Usuário criado: " . ($createResult ? '✅ SUCESSO' : '❌ FALHA') . "<br>";
        
        if ($createResult) {
            echo "Agora o email existe: " . ($supabase->emailExists($email_teste) ? '✅ SIM' : '❌ NÃO') . "<br>";
        }
    }
    
    echo "<h2>3. Teste de atualização de senha</h2>";
    $newPassword = 'NovaSenha123!';
    
    echo "Tentando atualizar senha para: $newPassword<br>";
    
    $updateResult = $supabase->updateUserPassword($email_teste, $newPassword);
    echo "Resultado da atualização: " . ($updateResult ? '✅ SUCESSO' : '❌ FALHA') . "<br>";
    
    echo "<h2>4. Verificar se senha foi atualizada</h2>";
    // Buscar usuário para verificar se senha foi alterada
    $response = $supabase->makeRequest("usuarios?email=eq.$email_teste", 'GET', null, true);
    
    if ($response['status'] === 200 && !empty($response['data'])) {
        $user = $response['data'][0];
        $passwordMatch = password_verify($newPassword, $user['senha']);
        echo "Nova senha confere: " . ($passwordMatch ? '✅ SIM' : '❌ NÃO') . "<br>";
        
        if (!$passwordMatch) {
            echo "Hash atual da senha: " . substr($user['senha'], 0, 20) . "...<br>";
        }
    } else {
        echo "❌ Não foi possível buscar usuário para verificação<br>";
        echo "Status: " . $response['status'] . "<br>";
        echo "Dados: " . json_encode($response['data']) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>Logs de Erro</h2>";
$error_log = error_get_last();
if ($error_log) {
    echo "<pre>" . print_r($error_log, true) . "</pre>";
}
?>