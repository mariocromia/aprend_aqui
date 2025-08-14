-- ============================================================================
-- SCRIPT DIRETO PARA REMOVER FOREIGN KEY INCORRETA
-- Remove especificamente a constraint usuarios_id_fkey
-- ============================================================================

-- 1. MOSTRAR TODAS AS CONSTRAINTS DA TABELA usuarios
SELECT '=== CONSTRAINTS EXISTENTES ===' as info;
SELECT 
    tc.constraint_name,
    tc.constraint_type,
    kcu.column_name,
    ccu.table_name AS foreign_table_name,
    ccu.column_name AS foreign_column_name
FROM information_schema.table_constraints tc
JOIN information_schema.key_column_usage kcu 
    ON tc.constraint_name = kcu.constraint_name
LEFT JOIN information_schema.constraint_column_usage ccu 
    ON ccu.constraint_name = tc.constraint_name
WHERE tc.table_name = 'usuarios';

-- 2. REMOVER ESPECIFICAMENTE A FOREIGN KEY usuarios_id_fkey
SELECT '=== REMOVENDO FOREIGN KEY usuarios_id_fkey ===' as info;

-- Tentar remover a constraint diretamente
DO $$
BEGIN
    BEGIN
        ALTER TABLE usuarios DROP CONSTRAINT usuarios_id_fkey;
        RAISE NOTICE 'Foreign key usuarios_id_fkey removida com sucesso';
    EXCEPTION WHEN OTHERS THEN
        RAISE NOTICE 'Erro ao remover usuarios_id_fkey: %', SQLERRM;
    END;
END $$;

-- 3. VERIFICAR SE AINDA EXISTEM OUTRAS FOREIGN KEYS
SELECT '=== VERIFICANDO FOREIGN KEYS RESTANTES ===' as info;
SELECT 
    tc.constraint_name,
    tc.constraint_type,
    kcu.column_name,
    ccu.table_name AS foreign_table_name
FROM information_schema.table_constraints tc
JOIN information_schema.key_column_usage kcu 
    ON tc.constraint_name = kcu.constraint_name
LEFT JOIN information_schema.constraint_column_usage ccu 
    ON ccu.constraint_name = tc.constraint_name
WHERE tc.table_name = 'usuarios' 
    AND tc.constraint_type = 'FOREIGN KEY';

-- 4. SE AINDA EXISTIR ALGUMA FOREIGN KEY, REMOVER TODAS
DO $$
DECLARE
    constraint_record RECORD;
BEGIN
    FOR constraint_record IN 
        SELECT tc.constraint_name
        FROM information_schema.table_constraints tc
        WHERE tc.table_name = 'usuarios' 
            AND tc.constraint_type = 'FOREIGN KEY'
    LOOP
        BEGIN
            EXECUTE 'ALTER TABLE usuarios DROP CONSTRAINT ' || constraint_record.constraint_name;
            RAISE NOTICE 'Foreign key % removida com sucesso', constraint_record.constraint_name;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Erro ao remover %: %', constraint_record.constraint_name, SQLERRM;
        END;
    END LOOP;
END $$;

-- 5. VERIFICAR SE A COLUNA ID TEM PRIMARY KEY
SELECT '=== VERIFICANDO PRIMARY KEY ===' as info;
SELECT 
    tc.constraint_name, 
    tc.constraint_type,
    kcu.column_name
FROM information_schema.table_constraints tc
JOIN information_schema.key_column_usage kcu 
    ON tc.constraint_name = kcu.constraint_name
WHERE tc.table_name = 'usuarios' 
    AND tc.constraint_type = 'PRIMARY KEY';

-- 6. SE NÃO TIVER PRIMARY KEY, ADICIONAR
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.table_constraints tc
        JOIN information_schema.key_column_usage kcu 
            ON tc.constraint_name = kcu.constraint_name
        WHERE tc.table_name = 'usuarios' 
            AND tc.constraint_type = 'PRIMARY KEY'
    ) THEN
        ALTER TABLE usuarios ADD PRIMARY KEY (id);
        RAISE NOTICE 'Primary Key adicionada na coluna ID';
    ELSE
        RAISE NOTICE 'Primary Key já existe na coluna ID';
    END IF;
END $$;

-- 7. CONFIGURAR COLUNA ID COM DEFAULT CORRETO
SELECT '=== CONFIGURANDO COLUNA ID ===' as info;
DO $$
BEGIN
    -- Garantir que uuid-ossp está habilitado
    CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
    
    -- Adicionar DEFAULT se não existir
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
    
    -- Garantir que é NOT NULL
    ALTER TABLE usuarios ALTER COLUMN id SET NOT NULL;
    RAISE NOTICE 'Coluna ID configurada como NOT NULL';
END $$;

-- 8. TESTAR INSERÇÃO SEM FOREIGN KEY
SELECT '=== TESTANDO INSERÇÃO ===' as info;
DO $$
DECLARE
    test_id UUID;
    test_email TEXT;
BEGIN
    test_email := 'teste_' || extract(epoch from now()) || '@exemplo.com';
    
    INSERT INTO usuarios (nome, email, senha_hash, whatsapp, whatsapp_confirmado, codigo_ativacao, codigo_gerado_em, ativo, email_verificado, criado_em, ultimo_login, tentativas_login_falhadas, conta_bloqueada_ate)
    VALUES (
        'Usuário Teste',
        test_email,
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
    ) RETURNING id INTO test_id;
    
    RAISE NOTICE '✅ Usuário criado com sucesso! ID: %, Email: %', test_id, test_email;
    
    -- Limpar usuário de teste
    DELETE FROM usuarios WHERE id = test_id;
    RAISE NOTICE 'Usuário de teste removido';
    
EXCEPTION WHEN OTHERS THEN
    RAISE NOTICE '❌ Erro ao criar usuário: %', SQLERRM;
END $$;

-- 9. VERIFICAR ESTRUTURA FINAL
SELECT '=== ESTRUTURA FINAL ===' as info;
SELECT 
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns 
WHERE table_name = 'usuarios' 
ORDER BY ordinal_position;

SELECT '=== CORREÇÃO CONCLUÍDA ===' as resultado;
