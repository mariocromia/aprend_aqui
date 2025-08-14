-- ============================================================================
-- SCRIPT PARA VERIFICAR E CORRIGIR ESTRUTURA DA TABELA usuarios
-- Execute este script para diagnosticar problemas na tabela
-- ============================================================================

-- 1. VERIFICAR ESTRUTURA ATUAL DA TABELA usuarios
SELECT 
    column_name,
    data_type,
    is_nullable,
    column_default,
    character_maximum_length
FROM information_schema.columns 
WHERE table_name = 'usuarios' 
ORDER BY ordinal_position;

-- 2. VERIFICAR SE A TABELA TEM PRIMARY KEY
SELECT 
    tc.constraint_name, 
    tc.constraint_type,
    kcu.column_name
FROM information_schema.table_constraints tc
JOIN information_schema.key_column_usage kcu 
    ON tc.constraint_name = kcu.constraint_name
WHERE tc.table_name = 'usuarios' 
    AND tc.constraint_type = 'PRIMARY KEY';

-- 3. VERIFICAR SE EXISTEM EXTENSÕES NECESSÁRIAS
SELECT extname FROM pg_extension WHERE extname IN ('uuid-ossp', 'pgcrypto');

-- 4. SE A TABELA NÃO TIVER ESTRUTURA CORRETA, RECRIAR
-- (Execute apenas se necessário)

-- Opção A: Recriar tabela do zero (PERDE DADOS EXISTENTES)
/*
DROP TABLE IF EXISTS usuarios CASCADE;
CREATE TABLE usuarios (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha_hash VARCHAR(255),
    whatsapp VARCHAR(20),
    whatsapp_confirmado BOOLEAN DEFAULT FALSE,
    codigo_ativacao VARCHAR(10),
    codigo_gerado_em TIMESTAMP WITH TIME ZONE,
    tentativas_codigo INTEGER DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    email_verificado BOOLEAN DEFAULT FALSE,
    ultimo_login TIMESTAMP WITH TIME ZONE,
    tentativas_login_falhadas INTEGER DEFAULT 0,
    conta_bloqueada_ate TIMESTAMP WITH TIME ZONE,
    criado_em TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    atualizado_em TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);
*/

-- Opção B: Corrigir apenas a coluna ID (MANTÉM DADOS EXISTENTES)
-- Execute apenas se a coluna id não existir ou não tiver DEFAULT
/*
ALTER TABLE usuarios ALTER COLUMN id SET DEFAULT gen_random_uuid();
ALTER TABLE usuarios ALTER COLUMN id SET NOT NULL;
*/

-- 5. VERIFICAR PERMISSÕES
SELECT 
    grantee,
    privilege_type,
    is_grantable
FROM information_schema.role_table_grants 
WHERE table_name = 'usuarios';

-- 6. VERIFICAR RLS (Row Level Security)
SELECT 
    schemaname,
    tablename,
    rowsecurity
FROM pg_tables 
WHERE tablename = 'usuarios';
