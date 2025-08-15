-- Setup Simples - Execute seção por seção se houver erro
-- Ou execute tudo de uma vez

-- =============================================================================
-- SEÇÃO 1: TABELA USUARIOS
-- =============================================================================

-- Criar tabela usuarios se não existir
CREATE TABLE IF NOT EXISTS public.usuarios (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Adicionar coluna senha se não existir
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' AND column_name = 'senha' AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.usuarios ADD COLUMN senha VARCHAR(255);
    END IF;
END $$;

-- Adicionar outras colunas essenciais
DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'whatsapp') THEN
        ALTER TABLE public.usuarios ADD COLUMN whatsapp VARCHAR(20);
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'whatsapp_confirmado') THEN
        ALTER TABLE public.usuarios ADD COLUMN whatsapp_confirmado BOOLEAN DEFAULT FALSE;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'ativo') THEN
        ALTER TABLE public.usuarios ADD COLUMN ativo BOOLEAN DEFAULT TRUE;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'email_verificado') THEN
        ALTER TABLE public.usuarios ADD COLUMN email_verificado BOOLEAN DEFAULT FALSE;
    END IF;
END $$;

-- Definir senha padrão e tornar NOT NULL
UPDATE public.usuarios 
SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE senha IS NULL OR senha = '';

-- Tornar colunas essenciais NOT NULL
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'nome') THEN
        ALTER TABLE public.usuarios ALTER COLUMN nome SET NOT NULL;
    END IF;
    
    IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'email') THEN
        ALTER TABLE public.usuarios ALTER COLUMN email SET NOT NULL;
    END IF;
    
    IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'senha') THEN
        ALTER TABLE public.usuarios ALTER COLUMN senha SET NOT NULL;
    END IF;
END $$;

-- =============================================================================
-- SEÇÃO 2: TABELAS DE RECUPERAÇÃO
-- =============================================================================

-- Tabela de tokens (links tradicionais)
CREATE TABLE IF NOT EXISTS public.password_reset_tokens (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    used BOOLEAN DEFAULT FALSE
);

-- Tabela de códigos (temporários)
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
-- SEÇÃO 3: ÍNDICES
-- =============================================================================

CREATE INDEX IF NOT EXISTS idx_usuarios_email ON public.usuarios(email);
CREATE INDEX IF NOT EXISTS idx_usuarios_whatsapp ON public.usuarios(whatsapp);
CREATE INDEX IF NOT EXISTS idx_password_reset_tokens_email ON public.password_reset_tokens(email);
CREATE INDEX IF NOT EXISTS idx_password_reset_tokens_token ON public.password_reset_tokens(token);
CREATE INDEX IF NOT EXISTS idx_password_reset_codes_email ON public.password_reset_codes(email);
CREATE INDEX IF NOT EXISTS idx_password_reset_codes_code ON public.password_reset_codes(code);

-- =============================================================================
-- SEÇÃO 4: SEGURANÇA
-- =============================================================================

-- Habilitar RLS
ALTER TABLE public.usuarios ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.password_reset_tokens ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.password_reset_codes ENABLE ROW LEVEL SECURITY;

-- Criar políticas apenas se não existirem
DO $$
BEGIN
    -- usuarios
    IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'usuarios' AND policyname = 'Permitir acesso via service key') THEN
        CREATE POLICY "Permitir acesso via service key" ON public.usuarios FOR ALL USING (auth.role() = 'service_role');
    END IF;

    -- password_reset_tokens
    IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'password_reset_tokens' AND policyname = 'Permitir acesso via service key') THEN
        CREATE POLICY "Permitir acesso via service key" ON public.password_reset_tokens FOR ALL USING (auth.role() = 'service_role');
    END IF;

    -- password_reset_codes
    IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'password_reset_codes' AND policyname = 'Permitir acesso via service key') THEN
        CREATE POLICY "Permitir acesso via service key" ON public.password_reset_codes FOR ALL USING (auth.role() = 'service_role');
    END IF;
END $$;

-- =============================================================================
-- SEÇÃO 5: USUÁRIO DE TESTE
-- =============================================================================

INSERT INTO public.usuarios (
    nome, 
    email, 
    senha, 
    whatsapp, 
    whatsapp_confirmado, 
    ativo, 
    email_verificado,
    created_at
) VALUES (
    'Usuário Teste Recovery',
    'teste@exemplo.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '+5511999999999',
    true,
    true,
    true,
    NOW()
) ON CONFLICT (email) DO UPDATE SET
    nome = EXCLUDED.nome,
    whatsapp = EXCLUDED.whatsapp,
    whatsapp_confirmado = EXCLUDED.whatsapp_confirmado,
    ativo = EXCLUDED.ativo;

-- =============================================================================
-- SEÇÃO 6: VERIFICAÇÃO
-- =============================================================================

-- Ver estrutura da tabela usuarios
SELECT 
    column_name,
    data_type,
    is_nullable
FROM information_schema.columns 
WHERE table_name = 'usuarios' AND table_schema = 'public'
ORDER BY ordinal_position;