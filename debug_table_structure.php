<?php
/**
 * Debug da estrutura da tabela usuarios
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/SupabaseClient.php';

echo "<h1>Debug - Estrutura da Tabela usuarios</h1>";

try {
    $supabase = new SupabaseClient();
    
    echo "<h2>1. Tentativa de consultar tabela usuarios</h2>";
    
    // Tentar fazer uma consulta simples
    $response = $supabase->makeRequest('usuarios?limit=1', 'GET', null, true);
    
    echo "Status da consulta: " . $response['status'] . "<br>";
    
    if ($response['status'] === 200) {
        echo "✅ Tabela usuarios existe!<br>";
        
        if (!empty($response['data'])) {
            echo "<h3>Estrutura encontrada (baseada no primeiro registro):</h3>";
            $firstUser = $response['data'][0];
            echo "<ul>";
            foreach (array_keys($firstUser) as $column) {
                echo "<li><strong>$column</strong>: " . gettype($firstUser[$column]) . "</li>";
            }
            echo "</ul>";
            
            echo "<h3>Dados do primeiro usuário:</h3>";
            echo "<pre>" . json_encode($firstUser, JSON_PRETTY_PRINT) . "</pre>";
            
        } else {
            echo "⚠️ Tabela existe mas está vazia<br>";
        }
        
    } elseif ($response['status'] === 404 || strpos($response['raw'], 'relation') !== false) {
        echo "❌ Tabela usuarios NÃO EXISTE<br>";
        echo "Raw response: <pre>" . htmlspecialchars($response['raw']) . "</pre>";
        
    } else {
        echo "❌ Erro na consulta<br>";
        echo "Status: " . $response['status'] . "<br>";
        echo "Raw response: <pre>" . htmlspecialchars($response['raw']) . "</pre>";
    }
    
    echo "<h2>2. Verificar se existe tabela com nome diferente</h2>";
    
    // Tentar algumas variações
    $possibleTables = ['users', 'user', 'profiles', 'auth_users'];
    
    foreach ($possibleTables as $table) {
        $testResponse = $supabase->makeRequest("$table?limit=1", 'GET', null, true);
        echo "Tabela '$table': ";
        
        if ($testResponse['status'] === 200) {
            echo "✅ EXISTE<br>";
            
            if (!empty($testResponse['data'])) {
                $firstRecord = $testResponse['data'][0];
                echo "Colunas: " . implode(', ', array_keys($firstRecord)) . "<br>";
            }
        } else {
            echo "❌ Não existe<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>3. SQL para criar tabela usuarios</h2>";
echo "<p>Se a tabela não existir, execute este SQL no Supabase:</p>";
echo "<textarea rows='25' cols='80'>";
echo "-- Criar tabela usuarios completa
CREATE TABLE IF NOT EXISTS public.usuarios (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,  -- Esta é a coluna que estava faltando
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

-- Criar índices
CREATE INDEX IF NOT EXISTS idx_usuarios_email ON public.usuarios(email);
CREATE INDEX IF NOT EXISTS idx_usuarios_whatsapp ON public.usuarios(whatsapp);

-- Habilitar RLS
ALTER TABLE public.usuarios ENABLE ROW LEVEL SECURITY;

-- Política para service key
CREATE POLICY \"Permitir acesso via service key\" ON public.usuarios
FOR ALL USING (auth.role() = 'service_role');

-- Comentários
COMMENT ON TABLE public.usuarios IS 'Tabela principal de usuários do sistema';
COMMENT ON COLUMN public.usuarios.senha IS 'Hash da senha do usuário';
COMMENT ON COLUMN public.usuarios.whatsapp IS 'Número do WhatsApp com código do país';";
echo "</textarea>";

echo "<h2>4. Alternativa: Verificar tabela auth.users</h2>";
echo "<p>Se estiver usando Supabase Auth, os usuários podem estar em auth.users:</p>";

try {
    $authResponse = $supabase->makeRequest('auth/v1/admin/users', 'GET', null, true);
    echo "Consulta auth.users: Status " . $authResponse['status'] . "<br>";
    
    if ($authResponse['status'] === 200) {
        echo "✅ Supabase Auth está configurado<br>";
    }
} catch (Exception $e) {
    echo "Auth check: " . $e->getMessage() . "<br>";
}
?>