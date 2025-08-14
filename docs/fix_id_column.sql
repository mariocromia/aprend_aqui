-- ============================================================================
-- SCRIPT PARA CORRIGIR COLUNA ID DA TABELA usuarios
-- Execute este script para resolver o erro de ID NULL
-- ============================================================================

-- 1. VERIFICAR ESTRUTURA ATUAL
SELECT 'Estrutura atual da tabela usuarios:' as info;
SELECT 
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns 
WHERE table_name = 'usuarios' 
ORDER BY ordinal_position;

-- 2. VERIFICAR SE A COLUNA ID EXISTE
SELECT 'Verificando coluna ID:' as info;
SELECT 
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns 
WHERE table_name = 'usuarios' AND column_name = 'id';

-- 3. CORRIGIR COLUNA ID (Execute apenas se necessário)

-- Se a coluna id não existir, criar
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'id') THEN
        ALTER TABLE usuarios ADD COLUMN id UUID PRIMARY KEY DEFAULT gen_random_uuid();
        RAISE NOTICE 'Coluna ID criada com sucesso';
    ELSE
        RAISE NOTICE 'Coluna ID já existe';
    END IF;
END $$;

-- Se a coluna id existir mas não tiver DEFAULT, adicionar
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'id') THEN
        -- Verificar se já tem DEFAULT
        IF NOT EXISTS (
            SELECT 1 FROM information_schema.columns 
            WHERE table_name = 'usuarios' 
            AND column_name = 'id' 
            AND column_default IS NOT NULL
        ) THEN
            ALTER TABLE usuarios ALTER COLUMN id SET DEFAULT gen_random_uuid();
            RAISE NOTICE 'DEFAULT gen_random_uuid() adicionado à coluna ID';
        ELSE
            RAISE NOTICE 'Coluna ID já tem DEFAULT configurado';
        END IF;
        
        -- Garantir que a coluna é NOT NULL
        ALTER TABLE usuarios ALTER COLUMN id SET NOT NULL;
        RAISE NOTICE 'Coluna ID configurada como NOT NULL';
    END IF;
END $$;

-- 4. VERIFICAR SE AS EXTENSÕES NECESSÁRIAS EXISTEM
SELECT 'Verificando extensões:' as info;
SELECT 
    extname,
    extversion
FROM pg_extension 
WHERE extname IN ('uuid-ossp', 'pgcrypto');

-- Se uuid-ossp não existir, criar
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Se pgcrypto não existir, criar
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- 5. VERIFICAR ESTRUTURA FINAL
SELECT 'Estrutura final da tabela usuarios:' as info;
SELECT 
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns 
WHERE table_name = 'usuarios' 
ORDER BY ordinal_position;

-- 6. TESTAR INSERÇÃO
SELECT 'Testando inserção de usuário...' as info;
INSERT INTO usuarios (nome, email, senha_hash, whatsapp, whatsapp_confirmado, codigo_ativacao, codigo_gerado_em, ativo, email_verificado, criado_em, ultimo_login, tentativas_login_falhadas, conta_bloqueada_ate)
VALUES (
    'Usuário Teste',
    'teste_' || extract(epoch from now()) || '@exemplo.com',
    crypt('Teste123!', gen_salt('bf')),
    '11999999999',
    false,
    '123456',
    now(),
    true,
    false,
    now(),
    null,
    0,
    null
) RETURNING id, nome, email;

-- 7. LIMPAR USUÁRIO DE TESTE
DELETE FROM usuarios WHERE email LIKE 'teste_%@exemplo.com';

SELECT 'Correção concluída!' as resultado;
