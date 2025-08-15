<?php
/**
 * Teste de recuperação com usuário real
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/EmailManager.php';
require_once 'includes/SupabaseClient.php';

echo "<h1>Teste de Recuperação - Usuário Real</h1>";

try {
    $supabase = new SupabaseClient();
    
    echo "<h2>1. Buscar Usuários Existentes</h2>";
    $response = $supabase->makeRequest('usuarios?select=email,nome&limit=5', 'GET', null, true);
    
    if ($response['status'] === 200 && !empty($response['data'])) {
        echo "Usuários encontrados:<br>";
        foreach ($response['data'] as $user) {
            echo "- " . ($user['nome'] ?? 'Sem nome') . " (" . $user['email'] . ")<br>";
        }
        
        // Usar o primeiro usuário para teste
        $emailReal = $response['data'][0]['email'];
        $nomeReal = $response['data'][0]['nome'] ?? 'Usuário';
        
        echo "<br><strong>Testando com:</strong> $nomeReal ($emailReal)<br>";
        
        echo "<h2>2. Teste de Recuperação</h2>";
        
        // Gerar token
        $token = EmailManager::generateResetToken(null, $emailReal);
        
        if ($token) {
            echo "✅ Token gerado: " . substr($token, 0, 10) . "...<br>";
            
            // Verificar token
            $verification = EmailManager::verifyResetToken($token);
            
            if ($verification && $verification['valid']) {
                echo "✅ Token válido para: " . $verification['email'] . "<br>";
                
                // Tentar reset de senha
                $newPassword = 'NovaSenha123!';
                $resetResult = EmailManager::resetPassword($token, $newPassword);
                
                if ($resetResult['success']) {
                    echo "✅ " . $resetResult['message'] . "<br>";
                    
                    // Verificar se senha foi realmente alterada
                    echo "<h2>3. Verificação da Alteração</h2>";
                    $userUpdated = $supabase->getUserByEmail($emailReal);
                    
                    if ($userUpdated && password_verify($newPassword, $userUpdated['senha'])) {
                        echo "✅ Senha alterada e verificada com sucesso!<br>";
                        
                        // Restaurar senha original
                        echo "<br>Restaurando senha original...<br>";
                        $originalPassword = 'senhaOriginal123'; // Você pode alterar aqui
                        $restoreResult = $supabase->updateUserPassword($emailReal, $originalPassword);
                        echo "Senha restaurada: " . ($restoreResult ? '✅ SIM' : '❌ NÃO') . "<br>";
                        
                    } else {
                        echo "❌ Erro: Senha não foi alterada corretamente<br>";
                    }
                    
                } else {
                    echo "❌ " . $resetResult['message'] . "<br>";
                }
                
            } else {
                echo "❌ Token inválido<br>";
            }
            
        } else {
            echo "❌ Falha ao gerar token<br>";
        }
        
    } else {
        echo "❌ Nenhum usuário encontrado na tabela usuarios<br>";
        echo "Status: " . $response['status'] . "<br>";
        echo "Response: " . $response['raw'] . "<br>";
        
        echo "<h2>Sugestão</h2>";
        echo "1. Faça um cadastro real na aplicação primeiro<br>";
        echo "2. Ou execute test_user_exists.php para criar um usuário de teste<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>Logs Detalhados</h2>";
echo "Verifique os logs do sistema para mais detalhes sobre a atualização de senha.";
?>