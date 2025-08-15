-- Setup Básico - Execute linha por linha se necessário
-- Cada comando é independente e seguro

-- 1. Criar tabela usuarios básica
CREATE TABLE IF NOT EXISTS public.usuarios (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 2. Adicionar coluna senha (se não existir)
ALTER TABLE public.usuarios ADD COLUMN IF NOT EXISTS senha VARCHAR(255);

-- 3. Adicionar outras colunas essenciais
ALTER TABLE public.usuarios ADD COLUMN IF NOT EXISTS whatsapp VARCHAR(20);
ALTER TABLE public.usuarios ADD COLUMN IF NOT EXISTS whatsapp_confirmado BOOLEAN DEFAULT FALSE;
ALTER TABLE public.usuarios ADD COLUMN IF NOT EXISTS ativo BOOLEAN DEFAULT TRUE;
ALTER TABLE public.usuarios ADD COLUMN IF NOT EXISTS email_verificado BOOLEAN DEFAULT FALSE;

-- 4. Definir senha padrão para registros sem senha
UPDATE public.usuarios 
SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE senha IS NULL OR senha = '';

-- 5. Criar tabela de tokens (recuperação tradicional)
CREATE TABLE IF NOT EXISTS public.password_reset_tokens (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    used BOOLEAN DEFAULT FALSE
);

-- 6. Criar tabela de códigos (recuperação por código)
CREATE TABLE IF NOT EXISTS public.password_reset_codes (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(6) NOT NULL,
    method VARCHAR(20) NOT NULL DEFAULT 'email',
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    used BOOLEAN DEFAULT FALSE
);

-- 7. Criar índices básicos
CREATE INDEX IF NOT EXISTS idx_usuarios_email ON public.usuarios(email);
CREATE INDEX IF NOT EXISTS idx_password_reset_tokens_email ON public.password_reset_tokens(email);
CREATE INDEX IF NOT EXISTS idx_password_reset_codes_email ON public.password_reset_codes(email);

-- 8. Habilitar RLS
ALTER TABLE public.usuarios ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.password_reset_tokens ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.password_reset_codes ENABLE ROW LEVEL SECURITY;

-- 9. Inserir usuário de teste
INSERT INTO public.usuarios (nome, email, senha, whatsapp, whatsapp_confirmado, ativo, email_verificado) 
VALUES ('Teste Recovery', 'teste@exemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+5511999999999', true, true, true)
ON CONFLICT (email) DO NOTHING;

-- 10. Verificar se funcionou
SELECT 'Setup concluído - tabelas criadas:' as status;
SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name IN ('usuarios', 'password_reset_tokens', 'password_reset_codes');