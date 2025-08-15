-- Setup Completo das Tabelas para Sistema de Recuperação
-- Execute este arquivo único no Supabase para configurar tudo

-- =============================================================================
-- 1. CORRIGIR/CRIAR TABELA USUARIOS
-- =============================================================================

-- Criar tabela usuarios se não existir
CREATE TABLE IF NOT EXISTS public.usuarios (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Adicionar colunas necessárias (uma por vez, se não existirem)
DO $$ 
BEGIN
    -- Coluna senha
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' AND column_name = 'senha' AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN senha VARCHAR(255);
        RAISE NOTICE '✅ Coluna senha adicionada';
    END IF;

    -- Coluna whatsapp
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' AND column_name = 'whatsapp' AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN whatsapp VARCHAR(20);
        RAISE NOTICE '✅ Coluna whatsapp adicionada';
    END IF;

    -- Coluna whatsapp_confirmado
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' AND column_name = 'whatsapp_confirmado' AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN whatsapp_confirmado BOOLEAN DEFAULT FALSE;
        RAISE NOTICE '✅ Coluna whatsapp_confirmado adicionada';
    END IF;

    -- Coluna codigo_ativacao
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' AND column_name = 'codigo_ativacao' AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN codigo_ativacao VARCHAR(10);
        RAISE NOTICE '✅ Coluna codigo_ativacao adicionada';
    END IF;

    -- Coluna codigo_gerado_em
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' AND column_name = 'codigo_gerado_em' AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN codigo_gerado_em TIMESTAMP WITH TIME ZONE;
        RAISE NOTICE '✅ Coluna codigo_gerado_em adicionada';
    END IF;

    -- Coluna ativo
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' AND column_name = 'ativo' AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN ativo BOOLEAN DEFAULT TRUE;
        RAISE NOTICE '✅ Coluna ativo adicionada';
    END IF;

    -- Coluna email_verificado
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' AND column_name = 'email_verificado' AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN email_verificado BOOLEAN DEFAULT FALSE;
        RAISE NOTICE '✅ Coluna email_verificado adicionada';
    END IF;

    -- Coluna ultimo_login
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' AND column_name = 'ultimo_login' AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN ultimo_login TIMESTAMP WITH TIME ZONE;
        RAISE NOTICE '✅ Coluna ultimo_login adicionada';
    END IF;

    -- Coluna tentativas_login_falhadas
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' AND column_name = 'tentativas_login_falhadas' AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN tentativas_login_falhadas INTEGER DEFAULT 0;
        RAISE NOTICE '✅ Coluna tentativas_login_falhadas adicionada';
    END IF;

    -- Coluna conta_bloqueada_ate
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' AND column_name = 'conta_bloqueada_ate' AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN conta_bloqueada_ate TIMESTAMP WITH TIME ZONE;
        RAISE NOTICE '✅ Coluna conta_bloqueada_ate adicionada';
    END IF;

    -- Coluna criado_em
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' AND column_name = 'criado_em' AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN criado_em TIMESTAMP WITH TIME ZONE DEFAULT NOW();
        RAISE NOTICE '✅ Coluna criado_em adicionada';
    END IF;
END $$;

-- Tornar colunas essenciais NOT NULL
ALTER TABLE public.usuarios ALTER COLUMN nome SET NOT NULL;
ALTER TABLE public.usuarios ALTER COLUMN email SET NOT NULL;

-- Definir senha padrão para registros sem senha e tornar NOT NULL
UPDATE public.usuarios 
SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE senha IS NULL OR senha = '';

ALTER TABLE public.usuarios ALTER COLUMN senha SET NOT NULL;

-- =============================================================================
-- 2. CRIAR TABELA PASSWORD_RESET_TOKENS
-- =============================================================================

CREATE TABLE IF NOT EXISTS public.password_reset_tokens (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    used BOOLEAN DEFAULT FALSE
);

-- =============================================================================
-- 3. CRIAR TABELA PASSWORD_RESET_CODES
-- =============================================================================

CREATE TABLE IF NOT EXISTS public.password_reset_codes (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(6) NOT NULL,
    method VARCHAR(20) NOT NULL DEFAULT 'email',
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    used BOOLEAN DEFAULT FALSE
);

-- =============================================================================
-- 4. CRIAR ÍNDICES
-- =============================================================================

-- Índices para usuarios
CREATE INDEX IF NOT EXISTS idx_usuarios_email ON public.usuarios(email);
CREATE INDEX IF NOT EXISTS idx_usuarios_whatsapp ON public.usuarios(whatsapp);
CREATE INDEX IF NOT EXISTS idx_usuarios_ativo ON public.usuarios(ativo);

-- Índices para password_reset_tokens
CREATE INDEX IF NOT EXISTS idx_password_reset_tokens_email ON public.password_reset_tokens(email);
CREATE INDEX IF NOT EXISTS idx_password_reset_tokens_token ON public.password_reset_tokens(token);
CREATE INDEX IF NOT EXISTS idx_password_reset_tokens_expires_at ON public.password_reset_tokens(expires_at);

-- Índices para password_reset_codes
CREATE INDEX IF NOT EXISTS idx_password_reset_codes_email ON public.password_reset_codes(email);
CREATE INDEX IF NOT EXISTS idx_password_reset_codes_code ON public.password_reset_codes(code);
CREATE INDEX IF NOT EXISTS idx_password_reset_codes_expires_at ON public.password_reset_codes(expires_at);

-- =============================================================================
-- 5. CONFIGURAR RLS E POLÍTICAS
-- =============================================================================

-- Habilitar RLS em todas as tabelas
ALTER TABLE public.usuarios ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.password_reset_tokens ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.password_reset_codes ENABLE ROW LEVEL SECURITY;

-- Criar políticas apenas se não existirem
DO $$
BEGIN
    -- Política para usuarios
    IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'usuarios' AND policyname = 'Permitir acesso via service key') THEN
        CREATE POLICY "Permitir acesso via service key" ON public.usuarios FOR ALL USING (auth.role() = 'service_role');
        RAISE NOTICE '✅ Política criada para usuarios';
    END IF;

    -- Política para password_reset_tokens
    IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'password_reset_tokens' AND policyname = 'Permitir acesso via service key') THEN
        CREATE POLICY "Permitir acesso via service key" ON public.password_reset_tokens FOR ALL USING (auth.role() = 'service_role');
        RAISE NOTICE '✅ Política criada para password_reset_tokens';
    END IF;

    -- Política para password_reset_codes
    IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'password_reset_codes' AND policyname = 'Permitir acesso via service key') THEN
        CREATE POLICY "Permitir acesso via service key" ON public.password_reset_codes FOR ALL USING (auth.role() = 'service_role');
        RAISE NOTICE '✅ Política criada para password_reset_codes';
    END IF;
END $$;

-- =============================================================================
-- 6. FUNÇÕES DE LIMPEZA
-- =============================================================================

-- Função para limpar tokens expirados
CREATE OR REPLACE FUNCTION cleanup_expired_tokens()
RETURNS void AS $$
BEGIN
    DELETE FROM public.password_reset_tokens 
    WHERE expires_at < NOW() OR used = true;
END;
$$ LANGUAGE plpgsql;

-- Função para limpar códigos expirados
CREATE OR REPLACE FUNCTION cleanup_expired_codes()
RETURNS void AS $$
BEGIN
    DELETE FROM public.password_reset_codes 
    WHERE expires_at < NOW() OR used = true;
END;
$$ LANGUAGE plpgsql;

-- =============================================================================
-- 7. INSERIR USUÁRIO DE TESTE
-- =============================================================================

INSERT INTO public.usuarios (
    nome, 
    email, 
    senha, 
    whatsapp, 
    whatsapp_confirmado, 
    ativo, 
    email_verificado,
    criado_em
) VALUES (
    'Usuário Teste',
    'teste@exemplo.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 'password'
    '+5511999999999',
    true,
    true,
    true,
    NOW()
) ON CONFLICT (email) DO UPDATE SET
    nome = EXCLUDED.nome,
    senha = EXCLUDED.senha,
    whatsapp = EXCLUDED.whatsapp,
    whatsapp_confirmado = EXCLUDED.whatsapp_confirmado,
    ativo = EXCLUDED.ativo,
    email_verificado = EXCLUDED.email_verificado;

-- =============================================================================
-- 8. VERIFICAÇÃO FINAL
-- =============================================================================

-- Mostrar estrutura da tabela usuarios
SELECT 
    'usuarios' as tabela,
    column_name,
    data_type,
    is_nullable
FROM information_schema.columns 
WHERE table_name = 'usuarios' AND table_schema = 'public'
ORDER BY ordinal_position;

-- Mensagem final de sucesso
DO $$
BEGIN
    RAISE NOTICE '🎉 Setup completo finalizado! Tabelas criadas: usuarios, password_reset_tokens, password_reset_codes';
    RAISE NOTICE '✅ Sistema de recuperação dual está pronto para uso!';
    RAISE NOTICE '📧 Métodos disponíveis: Email (código), WhatsApp, Email (link)';
END $$;