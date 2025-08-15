-- Criar tabela para tokens de recuperação de senha
CREATE TABLE IF NOT EXISTS public.password_reset_tokens (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    used BOOLEAN DEFAULT FALSE
);

-- Criar índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_password_reset_tokens_email ON public.password_reset_tokens(email);
CREATE INDEX IF NOT EXISTS idx_password_reset_tokens_token ON public.password_reset_tokens(token);
CREATE INDEX IF NOT EXISTS idx_password_reset_tokens_expires_at ON public.password_reset_tokens(expires_at);

-- Habilitar RLS (Row Level Security)
ALTER TABLE public.password_reset_tokens ENABLE ROW LEVEL SECURITY;

-- Política para permitir inserção/atualização via service key (apenas se não existir)
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_policies 
        WHERE tablename = 'password_reset_tokens' 
        AND policyname = 'Permitir acesso via service key'
        AND schemaname = 'public'
    ) THEN
        CREATE POLICY "Permitir acesso via service key" ON public.password_reset_tokens
        FOR ALL USING (auth.role() = 'service_role');
        RAISE NOTICE 'Política criada para password_reset_tokens';
    ELSE
        RAISE NOTICE 'Política já existe para password_reset_tokens';
    END IF;
END $$;

-- Função para limpar tokens expirados automaticamente
CREATE OR REPLACE FUNCTION cleanup_expired_tokens()
RETURNS void AS $$
BEGIN
    DELETE FROM public.password_reset_tokens 
    WHERE expires_at < NOW() OR used = true;
END;
$$ LANGUAGE plpgsql;

-- Comentários para documentação
COMMENT ON TABLE public.password_reset_tokens IS 'Tabela para armazenar tokens de recuperação de senha';
COMMENT ON COLUMN public.password_reset_tokens.email IS 'Email do usuário';
COMMENT ON COLUMN public.password_reset_tokens.token IS 'Token único de recuperação';
COMMENT ON COLUMN public.password_reset_tokens.expires_at IS 'Data de expiração do token';
COMMENT ON COLUMN public.password_reset_tokens.used IS 'Se o token já foi utilizado';