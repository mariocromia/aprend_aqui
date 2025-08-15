<?php
/**
 * Teste rápido de recuperação com usuário existente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/SupabaseClient.php';
require_once 'includes/EmailManager.php';

echo "<h1>Teste Rápido - Recuperação com Usuário Existente</h1>";

$email_teste = 'teste@exemplo.com';

try {
    $supabase = new SupabaseClient();
    
    echo "<h2>1. Verificar usuário existente</h2>";
    $user = $supabase->getUserByEmail($email_teste);
    
    if ($user) {
        echo "✅ Usuário encontrado: " . $user['nome'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "WhatsApp: " . ($user['whatsapp'] ?? 'Não cadastrado') . "<br>";
        
        // Mostrar estrutura das colunas
        echo "<h3>Colunas disponíveis:</h3>";
        echo "<ul>";
        foreach (array_keys($user) as $column) {
            echo "<li><strong>$column</strong></li>";
        }
        echo "</ul>";
        
        echo "<h2>2. Teste de Geração de Código por Email</h2>";
        $emailCode = EmailManager::generateResetCode($email_teste, 'email');
        
        if ($emailCode) {
            echo "✅ Código gerado: $emailCode<br>";
            
            // Teste de verificação
            $verification = EmailManager::verifyResetCode($email_teste, $emailCode);
            echo "Código válido: " . ($verification && $verification['valid'] ? '✅ SIM' : '❌ NÃO') . "<br>";
            
            if ($verification && $verification['valid']) {
                echo "<h2>3. Teste de Reset de Senha</h2>";
                $newPassword = 'TesteSenha123!';
                
                echo "Senha antiga (coluna): ";
                if (isset($user['senha_hash'])) {
                    echo "senha_hash = " . substr($user['senha_hash'], 0, 20) . "...<br>";
                } elseif (isset($user['senha'])) {
                    echo "senha = " . substr($user['senha'], 0, 20) . "...<br>";
                } else {
                    echo "❌ Nenhuma coluna de senha encontrada<br>";
                }
                
                $resetResult = EmailManager::resetPasswordWithCode($email_teste, $emailCode, $newPassword);
                
                if ($resetResult['success']) {
                    echo "✅ " . $resetResult['message'] . "<br>";
                    
                    // Verificar se senha foi realmente alterada
                    $userUpdated = $supabase->getUserByEmail($email_teste);
                    
                    if ($userUpdated) {
                        echo "Senha nova (coluna): ";
                        $passwordHash = null;
                        
                        if (isset($userUpdated['senha_hash'])) {
                            $passwordHash = $userUpdated['senha_hash'];
                            echo "senha_hash = " . substr($passwordHash, 0, 20) . "...<br>";
                        } elseif (isset($userUpdated['senha'])) {
                            $passwordHash = $userUpdated['senha'];
                            echo "senha = " . substr($passwordHash, 0, 20) . "...<br>";
                        }
                        
                        if ($passwordHash) {
                            $passwordMatches = password_verify($newPassword, $passwordHash);
                            echo "Nova senha confere: " . ($passwordMatches ? '✅ SIM' : '❌ NÃO') . "<br>";
                            
                            if ($passwordMatches) {
                                echo "<h2>🎉 SUCESSO TOTAL!</h2>";
                                echo "<p>Sistema de recuperação funcionando perfeitamente!</p>";
                                
                                // Restaurar senha original para próximos testes
                                echo "<h3>Restaurando senha original...</h3>";
                                $originalPassword = 'password'; // senha padrão
                                $restoreResult = $supabase->updateUserPassword($email_teste, $originalPassword);
                                echo "Senha restaurada: " . ($restoreResult ? '✅ SIM' : '❌ NÃO') . "<br>";
                            }
                        }
                    }
                    
                } else {
                    echo "❌ " . $resetResult['message'] . "<br>";
                }
            }
            
        } else {
            echo "❌ Falha ao gerar código<br>";
        }
        
    } else {
        echo "❌ Usuário teste@exemplo.com não encontrado<br>";
        echo "<p>Execute primeiro: /setup_and_test_recovery.php</p>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Status das Tabelas</h2>";

try {
    $supabase = new SupabaseClient();
    
    $tables = ['usuarios', 'password_reset_tokens', 'password_reset_codes'];
    
    foreach ($tables as $table) {
        $response = $supabase->makeRequest("$table?limit=1", 'GET', null, true);
        echo "Tabela $table: " . ($response['status'] === 200 ? '✅ OK' : '❌ ERRO') . "<br>";
    }
    
} catch (Exception $e) {
    echo "Erro ao verificar tabelas: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>Próximos Passos</h2>";
echo "<ol>";
echo "<li>Se o teste passou, acesse <a href='auth/login.php'>login.php</a></li>";
echo "<li>Clique em 'Esqueci minha senha'</li>";
echo "<li>Digite: teste@exemplo.com</li>";
echo "<li>Escolha método: Email, WhatsApp ou Link</li>";
echo "</ol>";
?>