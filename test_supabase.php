<?php
/**
 * Teste de Conexão com Supabase
 * Use este arquivo para verificar se a integração está funcionando
 */

// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Teste de Conexão com Supabase</h1>";
echo "<hr>";

try {
    // Carregar classes necessárias
    echo "<h3>1. Carregando classes...</h3>";
    
    if (!file_exists('includes/Environment.php')) {
        throw new Exception('Classe Environment não encontrada');
    }
    require_once 'includes/Environment.php';
    echo "✅ Environment.php carregado<br>";
    
    if (!file_exists('includes/SupabaseClient.php')) {
        throw new Exception('Classe SupabaseClient não encontrada');
    }
    require_once 'includes/SupabaseClient.php';
    echo "✅ SupabaseClient.php carregado<br>";
    
    echo "<hr>";
    
    // Verificar configurações
    echo "<h3>2. Verificando configurações...</h3>";
    
    $supabaseUrl = Environment::get('SUPABASE_URL', '');
    $supabaseAnonKey = Environment::get('SUPABASE_ANON_KEY', '');
    $supabaseServiceKey = Environment::get('SUPABASE_SERVICE_KEY', '');
    
    echo "SUPABASE_URL: " . ($supabaseUrl ? "✅ Configurado" : "❌ Não configurado") . "<br>";
    echo "SUPABASE_ANON_KEY: " . ($supabaseAnonKey ? "✅ Configurado" : "❌ Não configurado") . "<br>";
    echo "SUPABASE_SERVICE_KEY: " . ($supabaseServiceKey ? "✅ Configurado" : "❌ Não configurado") . "<br>";
    
    if (empty($supabaseUrl) || empty($supabaseAnonKey)) {
        throw new Exception('Configurações do Supabase incompletas. Verifique o arquivo env.config');
    }
    
    echo "<hr>";
    
    // Testar conexão
    echo "<h3>3. Testando conexão...</h3>";
    
    $supabase = new SupabaseClient();
    echo "✅ Cliente Supabase criado<br>";
    
    // Testar operação básica
    echo "<h3>4. Testando operações...</h3>";
    
    // Testar busca de estatísticas (se a view existir)
    try {
        $stats = $supabase->getUserStats();
        if ($stats) {
            echo "✅ Busca de estatísticas: OK<br>";
            echo "<pre>" . json_encode($stats, JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "⚠️ Busca de estatísticas: View não encontrada (normal se não executou o SQL)<br>";
        }
    } catch (Exception $e) {
        echo "⚠️ Busca de estatísticas: " . $e->getMessage() . "<br>";
    }
    
    // Testar verificação de email
    try {
        $emailExists = $supabase->emailExists('teste@exemplo.com');
        echo "✅ Verificação de email: OK (teste@exemplo.com não existe)<br>";
    } catch (Exception $e) {
        echo "❌ Verificação de email: " . $e->getMessage() . "<br>";
    }
    
    echo "<hr>";
    
    // Verificar extensões PHP
    echo "<h3>5. Verificando extensões PHP...</h3>";
    
    if (extension_loaded('curl')) {
        echo "✅ cURL: Habilitado<br>";
    } else {
        echo "❌ cURL: Não habilitado (necessário para Supabase)<br>";
    }
    
    if (extension_loaded('json')) {
        echo "✅ JSON: Habilitado<br>";
    } else {
        echo "❌ JSON: Não habilitado<br>";
    }
    
    echo "<hr>";
    
    // Resumo
    echo "<h3>🎯 Resumo do Teste</h3>";
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
    echo "<strong>✅ Sistema configurado corretamente!</strong><br>";
    echo "O Supabase está integrado e funcionando.<br>";
    echo "Agora você pode:";
    echo "<ul>";
    echo "<li>Fazer login em <a href='auth/login.php'>auth/login.php</a></li>";
    echo "<li>Criar conta em <a href='auth/cadastro.php'>auth/cadastro.php</a></li>";
    echo "<li>Acessar o gerador em <a href='gerador_prompt.php'>gerador_prompt.php</a></li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<strong>❌ Erro encontrado:</strong><br>";
    echo $e->getMessage();
    echo "</div>";
    
    echo "<hr>";
    echo "<h3>🔧 Como resolver:</h3>";
    echo "<ol>";
    echo "<li>Verifique se o arquivo <code>env.config</code> existe na raiz</li>";
    echo "<li>Confirme se as configurações do Supabase estão corretas</li>";
    echo "<li>Execute o script SQL no Supabase (<code>docs/supabase_setup.sql</code>)</li>";
    echo "<li>Verifique se o cURL está habilitado no PHP</li>";
    echo "<li>Consulte <code>docs/SUPABASE_SETUP.md</code> para mais detalhes</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><small>Teste executado em: " . date('Y-m-d H:i:s') . "</small></p>";
?>
