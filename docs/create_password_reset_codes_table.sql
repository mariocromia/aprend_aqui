-- Criar tabela para códigos de recuperação temporários
CREATE TABLE IF NOT EXISTS public.password_reset_codes (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(6) NOT NULL,
    method VARCHAR(20) NOT NULL DEFAULT 'email', -- 'email' ou 'whatsapp'
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    used BOOLEAN DEFAULT FALSE
);

-- Criar índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_password_reset_codes_email ON public.password_reset_codes(email);
CREATE INDEX IF NOT EXISTS idx_password_reset_codes_code ON public.password_reset_codes(code);
CREATE INDEX IF NOT EXISTS idx_password_reset_codes_expires_at ON public.password_reset_codes(expires_at);
CREATE INDEX IF NOT EXISTS idx_password_reset_codes_method ON public.password_reset_codes(method);

-- Habilitar RLS (Row Level Security)
ALTER TABLE public.password_reset_codes ENABLE ROW LEVEL SECURITY;

-- Política para permitir inserção/atualização via service key (apenas se não existir)
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_policies 
        WHERE tablename = 'password_reset_codes' 
        AND policyname = 'Permitir acesso via service key'
        AND schemaname = 'public'
    ) THEN
        CREATE POLICY "Permitir acesso via service key" ON public.password_reset_codes
        FOR ALL USING (auth.role() = 'service_role');
        RAISE NOTICE 'Política criada para password_reset_codes';
    ELSE
        RAISE NOTICE 'Política já existe para password_reset_codes';
    END IF;
END $$;

-- Função para limpar códigos expirados automaticamente
CREATE OR REPLACE FUNCTION cleanup_expired_codes()
RETURNS void AS $$
BEGIN
    DELETE FROM public.password_reset_codes 
    WHERE expires_at < NOW() OR used = true;
END;
$$ LANGUAGE plpgsql;

-- Comentários para documentação
COMMENT ON TABLE public.password_reset_codes IS 'Tabela para armazenar códigos temporários de recuperação de senha';
COMMENT ON COLUMN public.password_reset_codes.email IS 'Email do usuário';
COMMENT ON COLUMN public.password_reset_codes.code IS 'Código de 6 dígitos';
COMMENT ON COLUMN public.password_reset_codes.method IS 'Método de envio: email ou whatsapp';
COMMENT ON COLUMN public.password_reset_codes.expires_at IS 'Data de expiração do código (10 minutos)';
COMMENT ON COLUMN public.password_reset_codes.used IS 'Se o código já foi utilizado';