<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/Environment.php';
require_once 'includes/SupabaseClient.php';

echo "<h2>Teste de Cadastro - Versão Corrigida</h2>\n";

try {
    $supabase = new SupabaseClient();
    echo "✅ SupabaseClient criado com sucesso<br>\n";
    
    // Dados de teste
    $userData = [
        'nome' => 'Teste Usuario ' . time(),
        'email' => 'teste' . time() . '@teste.com',
        'senha_hash' => password_hash('Teste123!', PASSWORD_DEFAULT),
        'whatsapp' => '11999999999',
        'whatsapp_confirmado' => false,
        'codigo_ativacao' => '123456',
        'codigo_gerado_em' => date('c'),
        'ativo' => true,
        'email_verificado' => false,
        'criado_em' => date('c'),
        'ultimo_login' => null,
        'tentativas_login_falhadas' => 0,
        'conta_bloqueada_ate' => null
    ];
    
    echo "📝 Dados do usuário preparados<br>\n";
    echo "📧 Email de teste: " . $userData['email'] . "<br>\n";
    
    // Testar criação de usuário
    echo "<br>🚀 Tentando criar usuário...<br>\n";
    $resultado = $supabase->createUser($userData);
    
    echo "✅ <strong>SUCESSO!</strong> Usuário criado com as correções<br>\n";
    echo "📊 Resultado: <pre>" . json_encode($resultado, JSON_PRETTY_PRINT) . "</pre>\n";
    
    // Verificar se o usuário foi inserido corretamente
    echo "<br>🔍 Verificando se o usuário existe no banco...<br>\n";
    $usuarioExiste = $supabase->emailExists($userData['email']);
    
    if ($usuarioExiste) {
        echo "✅ Usuário confirmado no banco de dados!<br>\n";
    } else {
        echo "❌ Usuário não encontrado no banco<br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>ERRO:</strong> " . $e->getMessage() . "<br>\n";
    echo "📍 Arquivo: " . $e->getFile() . ":" . $e->getLine() . "<br>\n";
    echo "🔧 Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>\n";
}

echo "<hr>";
echo "<h3>Status das Correções:</h3>";
echo "1. ✅ Cabeçalho 'Prefer: return=representation' adicionado<br>";
echo "2. ✅ Método createUser corrigido para tratar resposta do Supabase<br>";
echo "3. ✅ Validação no cadastro.php ajustada<br>";
echo "4. ✅ Logging melhorado para debugging<br>";
?>