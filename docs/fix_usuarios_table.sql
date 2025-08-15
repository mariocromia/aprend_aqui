-- Correção da tabela usuarios - Adicionar coluna senha
-- Execute este SQL se a tabela usuarios já existir mas sem a coluna senha

-- 1. Adicionar coluna senha se não existir
DO $$ 
BEGIN
    -- Verificar se a coluna senha não existe e adicionar
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' 
        AND column_name = 'senha' 
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN senha VARCHAR(255);
        RAISE NOTICE 'Coluna senha adicionada à tabela usuarios';
    ELSE
        RAISE NOTICE 'Coluna senha já existe na tabela usuarios';
    END IF;
END $$;

-- 2. Verificar e adicionar outras colunas necessárias
DO $$ 
BEGIN
    -- Adicionar coluna whatsapp se não existir
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' 
        AND column_name = 'whatsapp' 
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN whatsapp VARCHAR(20);
        RAISE NOTICE 'Coluna whatsapp adicionada';
    END IF;

    -- Adicionar coluna whatsapp_confirmado se não existir
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' 
        AND column_name = 'whatsapp_confirmado' 
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN whatsapp_confirmado BOOLEAN DEFAULT FALSE;
        RAISE NOTICE 'Coluna whatsapp_confirmado adicionada';
    END IF;

    -- Adicionar coluna codigo_ativacao se não existir
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' 
        AND column_name = 'codigo_ativacao' 
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN codigo_ativacao VARCHAR(10);
        RAISE NOTICE 'Coluna codigo_ativacao adicionada';
    END IF;

    -- Adicionar coluna codigo_gerado_em se não existir
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' 
        AND column_name = 'codigo_gerado_em' 
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN codigo_gerado_em TIMESTAMP WITH TIME ZONE;
        RAISE NOTICE 'Coluna codigo_gerado_em adicionada';
    END IF;

    -- Adicionar coluna ativo se não existir
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' 
        AND column_name = 'ativo' 
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN ativo BOOLEAN DEFAULT TRUE;
        RAISE NOTICE 'Coluna ativo adicionada';
    END IF;

    -- Adicionar coluna email_verificado se não existir
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' 
        AND column_name = 'email_verificado' 
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN email_verificado BOOLEAN DEFAULT FALSE;
        RAISE NOTICE 'Coluna email_verificado adicionada';
    END IF;

    -- Adicionar coluna ultimo_login se não existir
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' 
        AND column_name = 'ultimo_login' 
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN ultimo_login TIMESTAMP WITH TIME ZONE;
        RAISE NOTICE 'Coluna ultimo_login adicionada';
    END IF;

    -- Adicionar coluna tentativas_login_falhadas se não existir
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' 
        AND column_name = 'tentativas_login_falhadas' 
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN tentativas_login_falhadas INTEGER DEFAULT 0;
        RAISE NOTICE 'Coluna tentativas_login_falhadas adicionada';
    END IF;

    -- Adicionar coluna conta_bloqueada_ate se não existir
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' 
        AND column_name = 'conta_bloqueada_ate' 
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN conta_bloqueada_ate TIMESTAMP WITH TIME ZONE;
        RAISE NOTICE 'Coluna conta_bloqueada_ate adicionada';
    END IF;
END $$;

-- 3. Tornar coluna senha NOT NULL se ainda não for
DO $$
BEGIN
    -- Verificar se há registros sem senha e atualizar
    UPDATE public.usuarios 
    SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'  -- password: 'password'
    WHERE senha IS NULL OR senha = '';
    
    -- Tornar coluna NOT NULL
    ALTER TABLE public.usuarios ALTER COLUMN senha SET NOT NULL;
    RAISE NOTICE 'Coluna senha agora é NOT NULL';
END $$;

-- 4. Criar índices se não existirem
CREATE INDEX IF NOT EXISTS idx_usuarios_email ON public.usuarios(email);
CREATE INDEX IF NOT EXISTS idx_usuarios_whatsapp ON public.usuarios(whatsapp);
CREATE INDEX IF NOT EXISTS idx_usuarios_ativo ON public.usuarios(ativo);

-- 5. Habilitar RLS se não estiver habilitado
ALTER TABLE public.usuarios ENABLE ROW LEVEL SECURITY;

-- 6. Criar política apenas se não existir
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_policies 
        WHERE tablename = 'usuarios' 
        AND policyname = 'Permitir acesso via service key'
        AND schemaname = 'public'
    ) THEN
        CREATE POLICY "Permitir acesso via service key" ON public.usuarios
        FOR ALL USING (auth.role() = 'service_role');
        RAISE NOTICE 'Política criada para tabela usuarios';
    ELSE
        RAISE NOTICE 'Política já existe para tabela usuarios';
    END IF;
END $$;

-- 7. Verificar estrutura final
SELECT 
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns 
WHERE table_name = 'usuarios' 
AND table_schema = 'public'
ORDER BY ordinal_position;