<?php
/**
 * Script de teste detalhado para identificar problemas no cadastro
 * Execute este arquivo para ver exatamente onde está falhando
 */

require_once 'includes/Environment.php';
require_once 'includes/SupabaseClient.php';

echo "<h1>🔍 Teste Detalhado de Cadastro</h1>\n";
echo "<pre>\n";

try {
    echo "1. Verificando configurações...\n";
    echo "   SUPABASE_URL: " . Environment::get('SUPABASE_URL', 'NÃO CONFIGURADO') . "\n";
    echo "   SUPABASE_ANON_KEY: " . substr(Environment::get('SUPABASE_ANON_KEY', 'NÃO CONFIGURADO'), 0, 20) . "...\n";
    echo "   SUPABASE_SERVICE_KEY: " . substr(Environment::get('SUPABASE_SERVICE_KEY', 'NÃO CONFIGURADO'), 0, 20) . "...\n";
    
    echo "\n2. Criando cliente Supabase...\n";
    $supabase = new SupabaseClient();
    echo "   ✅ Cliente criado com sucesso\n";
    
    echo "\n3. Testando verificação de email...\n";
    $emailTeste = 'teste_' . time() . '@exemplo.com';
    echo "   Testando email: $emailTeste\n";
    
    try {
        $emailExiste = $supabase->emailExists($emailTeste);
        echo "   ✅ Método emailExists funcionando\n";
        echo "   Email existe? " . ($emailExiste ? 'SIM' : 'NÃO') . "\n";
        
        echo "\n4. Preparando dados do usuário...\n";
        $testUser = [
            'nome' => 'Usuário Teste',
            'email' => $emailTeste,
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
        
        echo "   Dados preparados:\n";
        foreach ($testUser as $key => $value) {
            echo "     $key: " . (is_null($value) ? 'NULL' : $value) . "\n";
        }
        
        echo "\n5. Testando criação de usuário...\n";
        echo "   Chamando SupabaseClient::createUser()...\n";
        
        try {
            $usuarioCriado = $supabase->createUser($testUser);
            echo "   ✅ Usuário criado com sucesso!\n";
            echo "   ID: " . ($usuarioCriado['id'] ?? 'N/A') . "\n";
            echo "   Email: " . ($usuarioCriado['email'] ?? 'N/A') . "\n";
            
            echo "\n6. Verificando se usuário foi criado no banco...\n";
            $usuarioVerificado = $supabase->getUserByEmail($emailTeste);
            if ($usuarioVerificado) {
                echo "   ✅ Usuário encontrado no banco!\n";
                echo "   ID: " . $usuarioVerificado['id'] . "\n";
                echo "   Nome: " . $usuarioVerificado['nome'] . "\n";
                echo "   Email: " . $usuarioVerificado['email'] . "\n";
                echo "   Ativo: " . ($usuarioVerificado['ativo'] ? 'SIM' : 'NÃO') . "\n";
            } else {
                echo "   ❌ Usuário não foi encontrado no banco\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ Erro ao criar usuário: " . $e->getMessage() . "\n";
            echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Erro ao verificar email: " . $e->getMessage() . "\n";
        echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n</pre>\n";
echo "<h2>📋 Resumo do Teste</h2>\n";
echo "<p>Se você viu erros acima, verifique:</p>\n";
echo "<ul>\n";
echo "<li>Se executou o script de correção no Supabase</li>\n";
echo "<li>Se as configurações no env.config estão corretas</li>\n";
echo "<li>Se o projeto Supabase está ativo</li>\n";
echo "<li>Se as tabelas foram criadas corretamente</li>\n";
echo "</ul>\n";
?>
