<?php
/**
 * Setup completo e teste do sistema de recuperação dual
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/SupabaseClient.php';
require_once 'includes/EmailManager.php';

echo "<h1>Setup e Teste do Sistema de Recuperação</h1>";

$email_teste = 'teste@exemplo.com';

try {
    $supabase = new SupabaseClient();
    
    echo "<h2>1. Verificar Estrutura das Tabelas</h2>";
    
    // Verificar tabela usuarios
    echo "<h3>Tabela usuarios:</h3>";
    $usuariosResponse = $supabase->makeRequest('usuarios?limit=1', 'GET', null, true);
    
    if ($usuariosResponse['status'] === 200) {
        echo "✅ Tabela usuarios existe<br>";
        
        if (!empty($usuariosResponse['data'])) {
            $columns = array_keys($usuariosResponse['data'][0]);
            echo "Colunas: " . implode(', ', $columns) . "<br>";
            
            // Verificar se tem a coluna senha
            if (in_array('senha', $columns)) {
                echo "✅ Coluna 'senha' encontrada<br>";
            } else {
                echo "❌ Coluna 'senha' NÃO encontrada<br>";
                echo "<strong>EXECUTE o SQL:</strong> docs/create_usuarios_table.sql<br>";
            }
        } else {
            echo "⚠️ Tabela vazia<br>";
        }
    } else {
        echo "❌ Tabela usuarios não existe<br>";
        echo "Status: " . $usuariosResponse['status'] . "<br>";
        echo "<strong>EXECUTE o SQL:</strong> docs/create_usuarios_table.sql<br>";
    }
    
    // Verificar tabela password_reset_tokens
    echo "<h3>Tabela password_reset_tokens:</h3>";
    $tokensResponse = $supabase->makeRequest('password_reset_tokens?limit=1', 'GET', null, true);
    
    if ($tokensResponse['status'] === 200) {
        echo "✅ Tabela password_reset_tokens existe<br>";
    } else {
        echo "❌ Tabela password_reset_tokens não existe<br>";
        echo "<strong>EXECUTE o SQL:</strong> docs/create_password_reset_table.sql<br>";
    }
    
    // Verificar tabela password_reset_codes
    echo "<h3>Tabela password_reset_codes:</h3>";
    $codesResponse = $supabase->makeRequest('password_reset_codes?limit=1', 'GET', null, true);
    
    if ($codesResponse['status'] === 200) {
        echo "✅ Tabela password_reset_codes existe<br>";
    } else {
        echo "❌ Tabela password_reset_codes não existe<br>";
        echo "<strong>EXECUTE o SQL:</strong> docs/create_password_reset_codes_table.sql<br>";
    }
    
    // Se todas as tabelas existem, continuar com teste
    if ($usuariosResponse['status'] === 200 && 
        $tokensResponse['status'] === 200 && 
        $codesResponse['status'] === 200) {
        
        echo "<h2>2. Teste do Sistema de Recuperação</h2>";
        
        // Verificar se usuário teste existe
        $user = $supabase->getUserByEmail($email_teste);
        
        if (!$user) {
            echo "<h3>Criando usuário de teste...</h3>";
            
            // Dados mínimos para criar usuário (verificar nome das colunas)
            $userData = [
                'nome' => 'Usuário Teste Recovery',
                'email' => $email_teste,
                'whatsapp' => '+5511999999999',
                'whatsapp_confirmado' => true,
                'ativo' => true,
                'email_verificado' => true,
                'criado_em' => date('c')
            ];
            
            // Verificar se usa 'senha' ou 'senha_hash'
            $response = $supabase->makeRequest('usuarios?limit=1', 'GET', null, true);
            if (!empty($response['data'])) {
                $columns = array_keys($response['data'][0]);
                if (in_array('senha_hash', $columns)) {
                    $userData['senha_hash'] = password_hash('senhaOriginal123', PASSWORD_DEFAULT);
                } else {
                    $userData['senha'] = password_hash('senhaOriginal123', PASSWORD_DEFAULT);
                }
            }
            
            $response = $supabase->makeRequest('usuarios', 'POST', $userData, true);
            
            if ($response['status'] === 201 || $response['status'] === 200) {
                echo "✅ Usuário criado com sucesso<br>";
                $user = $supabase->getUserByEmail($email_teste);
            } elseif ($response['status'] === 409) {
                echo "⚠️ Usuário já existe (erro 409)<br>";
                $user = $supabase->getUserByEmail($email_teste);
                if ($user) {
                    echo "✅ Usando usuário existente: " . $user['nome'] . "<br>";
                }
            } else {
                echo "❌ Erro ao criar usuário<br>";
                echo "Status: " . $response['status'] . "<br>";
                echo "Response: <pre>" . htmlspecialchars($response['raw']) . "</pre>";
                
                // Tentar buscar usuário mesmo assim
                $user = $supabase->getUserByEmail($email_teste);
                if ($user) {
                    echo "✅ Mas usuário existe, continuando teste...<br>";
                } else {
                    exit;
                }
            }
        } else {
            echo "✅ Usuário teste já existe: " . $user['nome'] . "<br>";
        }
        
        // Teste 1: Código por Email
        echo "<h3>Teste 1: Código por Email</h3>";
        $emailCode = EmailManager::generateResetCode($email_teste, 'email');
        
        if ($emailCode) {
            echo "✅ Código gerado: $emailCode<br>";
            
            $emailSent = EmailManager::sendRecoveryCodeByEmail($email_teste, $emailCode, $user['nome']);
            echo "Email enviado: " . ($emailSent ? '✅ SIM' : '❌ NÃO') . "<br>";
            
            $verification = EmailManager::verifyResetCode($email_teste, $emailCode);
            echo "Código válido: " . ($verification && $verification['valid'] ? '✅ SIM' : '❌ NÃO') . "<br>";
            
        } else {
            echo "❌ Falha ao gerar código para email<br>";
        }
        
        // Teste 2: Código por WhatsApp
        echo "<h3>Teste 2: Código por WhatsApp</h3>";
        $whatsappCode = EmailManager::generateResetCode($email_teste, 'whatsapp');
        
        if ($whatsappCode) {
            echo "✅ Código gerado: $whatsappCode<br>";
            
            $whatsappSent = EmailManager::sendRecoveryCodeByWhatsApp($user['whatsapp'], $whatsappCode, $user['nome']);
            echo "WhatsApp enviado: " . ($whatsappSent ? '✅ SIM' : '❌ NÃO') . "<br>";
            
            $verification = EmailManager::verifyResetCode($email_teste, $whatsappCode);
            echo "Código válido: " . ($verification && $verification['valid'] ? '✅ SIM' : '❌ NÃO') . "<br>";
            
        } else {
            echo "❌ Falha ao gerar código para WhatsApp<br>";
        }
        
        // Teste 3: Reset de senha com código
        if (isset($emailCode)) {
            echo "<h3>Teste 3: Reset de Senha</h3>";
            $newPassword = 'NovaSenha123!';
            $resetResult = EmailManager::resetPasswordWithCode($email_teste, $emailCode, $newPassword);
            
            if ($resetResult['success']) {
                echo "✅ " . $resetResult['message'] . "<br>";
                
                // Verificar se senha foi alterada
                $userUpdated = $supabase->getUserByEmail($email_teste);
                if ($userUpdated) {
                    // Verificar qual coluna de senha usar
                    $passwordHash = null;
                    if (isset($userUpdated['senha_hash'])) {
                        $passwordHash = $userUpdated['senha_hash'];
                    } elseif (isset($userUpdated['senha'])) {
                        $passwordHash = $userUpdated['senha'];
                    }
                    
                    if ($passwordHash && password_verify($newPassword, $passwordHash)) {
                        echo "✅ Senha alterada e verificada com sucesso!<br>";
                        echo "Coluna usada: " . (isset($userUpdated['senha_hash']) ? 'senha_hash' : 'senha') . "<br>";
                    } else {
                        echo "❌ Senha não foi alterada corretamente<br>";
                        echo "Hash encontrado: " . ($passwordHash ? 'SIM' : 'NÃO') . "<br>";
                    }
                } else {
                    echo "❌ Não foi possível buscar usuário atualizado<br>";
                }
                
            } else {
                echo "❌ " . $resetResult['message'] . "<br>";
            }
        }
        
        echo "<h2>✅ Sistema Funcionando!</h2>";
        echo "<p>Agora você pode testar manualmente:</p>";
        echo "<ol>";
        echo "<li>Acesse <a href='auth/login.php'>login.php</a></li>";
        echo "<li>Clique em 'Esqueci minha senha'</li>";
        echo "<li>Digite: $email_teste</li>";
        echo "<li>Escolha um método de recuperação</li>";
        echo "</ol>";
        
    } else {
        echo "<h2>❌ Configuração Incompleta</h2>";
        echo "<p>Execute os SQLs necessários antes de testar.</p>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>SQLs Necessários</h2>";
echo "<ol>";
echo "<li><a href='docs/create_usuarios_table.sql'>create_usuarios_table.sql</a> - Tabela principal de usuários</li>";
echo "<li><a href='docs/create_password_reset_table.sql'>create_password_reset_table.sql</a> - Tokens tradicionais</li>";
echo "<li><a href='docs/create_password_reset_codes_table.sql'>create_password_reset_codes_table.sql</a> - Códigos temporários</li>";
echo "</ol>";

echo "<h2>Debug Tools</h2>";
echo "<ul>";
echo "<li><a href='debug_table_structure.php'>Verificar estrutura das tabelas</a></li>";
echo "<li><a href='test_recovery_dual.php'>Teste original (após setup)</a></li>";
echo "</ul>";
?>