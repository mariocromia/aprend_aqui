<?php
/**
 * Correção final - Criar usuário e testar recuperação
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/SupabaseClient.php';
require_once 'includes/EmailManager.php';

echo "<h1>Correção Final - Sistema de Recuperação</h1>";

$email_teste = 'teste@exemplo.com';

try {
    $supabase = new SupabaseClient();
    
    echo "<h2>Passo 1: Verificar se usuário existe</h2>";
    $userExists = $supabase->emailExists($email_teste);
    echo "Usuário $email_teste existe: " . ($userExists ? '✅ SIM' : '❌ NÃO') . "<br><br>";
    
    if (!$userExists) {
        echo "<h2>Passo 2: Criando usuário de teste</h2>";
        
        $userData = [
            'nome' => 'Usuário Teste Recovery',
            'email' => $email_teste,
            'senha' => password_hash('senhaOriginal123', PASSWORD_DEFAULT),
            'whatsapp' => '+5511999999999',
            'whatsapp_confirmado' => true,
            'codigo_ativacao' => '123456',
            'codigo_gerado_em' => date('c'),
            'ativo' => true,
            'email_verificado' => true,
            'criado_em' => date('c'),
            'ultimo_login' => null,
            'tentativas_login_falhadas' => 0,
            'conta_bloqueada_ate' => null
        ];
        
        echo "Criando usuário...<br>";
        $createResult = $supabase->createUser($userData);
        
        if ($createResult) {
            echo "✅ Usuário criado com sucesso!<br>";
            $userExists = true;
        } else {
            echo "❌ Falha ao criar usuário<br>";
            // Tentar descobrir o erro
            $response = $supabase->makeRequest('usuarios', 'POST', $userData, true);
            echo "Detalhes do erro:<br>";
            echo "Status: " . $response['status'] . "<br>";
            echo "Response: <pre>" . htmlspecialchars($response['raw']) . "</pre>";
        }
    }
    
    if ($userExists) {
        echo "<h2>Passo 3: Teste completo de recuperação</h2>";
        
        // 1. Gerar token
        echo "3.1 Gerando token...<br>";
        $token = EmailManager::generateResetToken(null, $email_teste);
        
        if ($token) {
            echo "✅ Token gerado: " . substr($token, 0, 10) . "...<br>";
            
            // 2. Verificar token
            echo "3.2 Verificando token...<br>";
            $verification = EmailManager::verifyResetToken($token);
            
            if ($verification && $verification['valid']) {
                echo "✅ Token válido<br>";
                
                // 3. Buscar dados do usuário antes da alteração
                echo "3.3 Verificando dados atuais do usuário...<br>";
                $userBefore = $supabase->getUserByEmail($email_teste);
                if ($userBefore) {
                    echo "✅ Usuário encontrado: " . $userBefore['nome'] . "<br>";
                    echo "Hash atual da senha: " . substr($userBefore['senha'], 0, 20) . "...<br>";
                }
                
                // 4. Tentar reset de senha com logs detalhados
                echo "3.4 Tentando reset de senha...<br>";
                $newPassword = 'NovaSenha123!';
                
                echo "<strong>--- LOGS DETALHADOS ---</strong><br>";
                echo "Email: $email_teste<br>";
                echo "Nova senha: $newPassword<br>";
                echo "Token: " . substr($token, 0, 15) . "...<br><br>";
                
                $resetResult = EmailManager::resetPassword($token, $newPassword);
                
                echo "<strong>--- RESULTADO ---</strong><br>";
                if ($resetResult['success']) {
                    echo "✅ " . $resetResult['message'] . "<br>";
                    
                    // 5. Verificar se senha foi realmente alterada
                    echo "3.5 Verificando alteração...<br>";
                    $userAfter = $supabase->getUserByEmail($email_teste);
                    
                    if ($userAfter) {
                        $passwordChanged = ($userBefore['senha'] !== $userAfter['senha']);
                        $passwordVerifies = password_verify($newPassword, $userAfter['senha']);
                        
                        echo "Senha foi alterada: " . ($passwordChanged ? '✅ SIM' : '❌ NÃO') . "<br>";
                        echo "Nova senha confere: " . ($passwordVerifies ? '✅ SIM' : '❌ NÃO') . "<br>";
                        
                        if ($passwordChanged && $passwordVerifies) {
                            echo "<br>🎉 <strong>SUCESSO TOTAL!</strong> O sistema de recuperação está funcionando!<br>";
                        }
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
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Próximos passos</h2>";
echo "<ol>";
echo "<li>Se ainda houver erro, verifique os logs do PHP</li>";
echo "<li>Confirme se a tabela 'usuarios' existe no Supabase</li>";
echo "<li>Verifique se as permissões estão corretas</li>";
echo "<li>Teste com um usuário criado pelo cadastro normal</li>";
echo "</ol>";
?>