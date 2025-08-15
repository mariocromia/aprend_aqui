<?php
/**
 * Teste para verificar se usuário existe e criar se necessário
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/SupabaseClient.php';

echo "<h1>Teste - Verificação de Usuário</h1>";

$email_teste = 'teste@exemplo.com';

try {
    $supabase = new SupabaseClient();
    
    echo "<h2>1. Verificação de Existência</h2>";
    echo "Verificando se $email_teste existe...<br>";
    
    $exists = $supabase->emailExists($email_teste);
    echo "Resultado: " . ($exists ? '✅ EXISTE' : '❌ NÃO EXISTE') . "<br>";
    
    if (!$exists) {
        echo "<h2>2. Criando Usuário de Teste</h2>";
        
        $userData = [
            'nome' => 'Usuário Teste',
            'email' => $email_teste,
            'senha' => password_hash('senhaAntiga123', PASSWORD_DEFAULT),
            'whatsapp' => '+5511999999999',
            'whatsapp_confirmado' => true,
            'codigo_ativacao' => '123456',
            'codigo_gerado_em' => date('c'),
            'ativo' => true,
            'email_verificado' => false,
            'criado_em' => date('c'),
            'ultimo_login' => null,
            'tentativas_login_falhadas' => 0,
            'conta_bloqueada_ate' => null
        ];
        
        echo "Dados do usuário:<br>";
        echo "<pre>" . json_encode($userData, JSON_PRETTY_PRINT) . "</pre>";
        
        $createResult = $supabase->createUser($userData);
        echo "Resultado da criação: " . ($createResult ? '✅ SUCESSO' : '❌ FALHA') . "<br>";
        
        if ($createResult) {
            echo "Verificando novamente se usuário existe...<br>";
            $existsNow = $supabase->emailExists($email_teste);
            echo "Agora existe: " . ($existsNow ? '✅ SIM' : '❌ NÃO') . "<br>";
        } else {
            echo "<strong>Detalhes do erro:</strong><br>";
            // Tentar fazer requisição direta para ver o erro
            $response = $supabase->makeRequest('usuarios', 'POST', $userData, true);
            echo "Status: " . $response['status'] . "<br>";
            echo "Response: <pre>" . $response['raw'] . "</pre>";
        }
    }
    
    echo "<h2>3. Buscar Dados do Usuário</h2>";
    $user = $supabase->getUserByEmail($email_teste);
    
    if ($user) {
        echo "✅ Usuário encontrado:<br>";
        echo "ID: " . ($user['id'] ?? 'N/A') . "<br>";
        echo "Nome: " . ($user['nome'] ?? 'N/A') . "<br>";
        echo "Email: " . ($user['email'] ?? 'N/A') . "<br>";
        echo "Senha (hash): " . substr($user['senha'] ?? '', 0, 20) . "...<br>";
    } else {
        echo "❌ Usuário não encontrado<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Próximo Passo</h2>";
echo "Se o usuário existe, execute novamente o test_recovery.php";
?>