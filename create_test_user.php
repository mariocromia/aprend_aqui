<?php
/**
 * Criar usuário de teste rapidamente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/SupabaseClient.php';

echo "<h1>Criar Usuário de Teste</h1>";

$email_teste = 'teste@exemplo.com';

try {
    $supabase = new SupabaseClient();
    
    // Verificar se já existe
    $exists = $supabase->emailExists($email_teste);
    
    if ($exists) {
        echo "✅ Usuário $email_teste já existe!<br>";
    } else {
        echo "Criando usuário $email_teste...<br>";
        
        // Dados do usuário de teste
        $userData = [
            'nome' => 'Usuário Teste',
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
        
        // Fazer requisição direta
        $response = $supabase->makeRequest('usuarios', 'POST', $userData, true);
        
        echo "Status: " . $response['status'] . "<br>";
        
        if ($response['status'] === 201 || $response['status'] === 200) {
            echo "✅ Usuário criado com sucesso!<br>";
            echo "Dados: <pre>" . json_encode($response['data'], JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "❌ Erro ao criar usuário<br>";
            echo "Response: <pre>" . htmlspecialchars($response['raw']) . "</pre>";
            
            // Se der erro de tabela não existe, mostrar SQL
            if (strpos($response['raw'], 'relation') !== false || strpos($response['raw'], 'table') !== false) {
                echo "<h2>A tabela 'usuarios' não existe!</h2>";
                echo "Execute o SQL abaixo no Supabase:<br>";
                echo "<textarea rows='20' cols='80'>";
                echo "-- Criar tabela usuarios (se não existir)
CREATE TABLE IF NOT EXISTS public.usuarios (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    whatsapp VARCHAR(20),
    whatsapp_confirmado BOOLEAN DEFAULT FALSE,
    codigo_ativacao VARCHAR(10),
    codigo_gerado_em TIMESTAMP WITH TIME ZONE,
    ativo BOOLEAN DEFAULT TRUE,
    email_verificado BOOLEAN DEFAULT FALSE,
    criado_em TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    ultimo_login TIMESTAMP WITH TIME ZONE,
    tentativas_login_falhadas INTEGER DEFAULT 0,
    conta_bloqueada_ate TIMESTAMP WITH TIME ZONE
);

-- Habilitar RLS
ALTER TABLE public.usuarios ENABLE ROW LEVEL SECURITY;

-- Política para service key
CREATE POLICY \"Permitir acesso via service key\" ON public.usuarios
FOR ALL USING (auth.role() = 'service_role');";
                echo "</textarea>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Após criar o usuário, execute novamente o teste de recuperação.</strong></p>";
?>