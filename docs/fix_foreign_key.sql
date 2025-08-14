-- ============================================================================
-- SCRIPT PARA CORRIGIR FOREIGN KEY INCORRETA
-- Remove a constraint que está causando erro de inserção
-- ============================================================================

-- 1. VERIFICAR CONSTRAINTS EXISTENTES
SELECT 'Verificando constraints da tabela usuarios:' as info;
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

-- 2. VERIFICAR SE EXISTE FOREIGN KEY PARA TABELA 'users'
SELECT 'Verificando se existe foreign key para tabela users:' as info;
SELECT 
    tc.constraint_name,
    tc.constraint_type
FROM information_schema.table_constraints tc
WHERE tc.table_name = 'usuarios' 
    AND tc.constraint_type = 'FOREIGN KEY'
    AND tc.constraint_name LIKE '%users%';

-- 3. REMOVER FOREIGN KEY INCORRETA (se existir)
DO $$
DECLARE
    constraint_name text;
BEGIN
    -- Procurar por foreign keys que referenciam a tabela 'users'
    SELECT tc.constraint_name INTO constraint_name
    FROM information_schema.table_constraints tc
    JOIN information_schema.key_column_usage kcu 
        ON tc.constraint_name = kcu.constraint_name
    LEFT JOIN information_schema.constraint_column_usage ccu 
        ON ccu.constraint_name = tc.constraint_name
    WHERE tc.table_name = 'usuarios' 
        AND tc.constraint_type = 'FOREIGN KEY'
        AND (ccu.table_name = 'users' OR tc.constraint_name LIKE '%users%');
    
    IF constraint_name IS NOT NULL THEN
        EXECUTE 'ALTER TABLE usuarios DROP CONSTRAINT ' || constraint_name;
        RAISE NOTICE 'Foreign key % removida com sucesso', constraint_name;
    ELSE
        RAISE NOTICE 'Nenhuma foreign key para tabela users encontrada';
    END IF;
END $$;

-- 4. VERIFICAR SE A COLUNA ID TEM PRIMARY KEY
SELECT 'Verificando se coluna ID tem PRIMARY KEY:' as info;
SELECT 
    tc.constraint_name, 
    tc.constraint_type,
    kcu.column_name
FROM information_schema.table_constraints tc
JOIN information_schema.key_column_usage kcu 
    ON tc.constraint_name = kcu.constraint_name
WHERE tc.table_name = 'usuarios' 
    AND tc.constraint_type = 'PRIMARY KEY';

-- 5. SE NÃO TIVER PRIMARY KEY, ADICIONAR
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

-- 6. GARANTIR QUE A COLUNA ID TEM DEFAULT CORRETO
DO $$
BEGIN
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
END $$;

-- 7. TESTAR INSERÇÃO SEM FOREIGN KEY
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

-- 8. LIMPAR USUÁRIO DE TESTE
DELETE FROM usuarios WHERE email LIKE 'teste_%@exemplo.com';

-- 9. VERIFICAR ESTRUTURA FINAL
SELECT 'Estrutura final da tabela usuarios:' as info;
SELECT 
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns 
WHERE table_name = 'usuarios' 
ORDER BY ordinal_position;

SELECT 'Correção de Foreign Key concluída!' as resultado;
