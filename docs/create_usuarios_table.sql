-- Criar tabela usuarios completa
-- Execute este SQL no Supabase para criar a estrutura necessária

CREATE TABLE IF NOT EXISTS public.usuarios (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    whatsapp VARCHAR(20),
    whatsapp_confirmado BOOLEAN DEFAULT FALSE,
    codigo_ativacao VARCHAR(10),
    codigo_gerado_em TIMESTAMP WITH TIME ZONE,
    ativo BOOLEAN DEFAULT TRUE,
    email_verificado BOOLEAN DEFAULT FALSE,
    criado_em TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    ultimo_login TIMESTAMP WITH TIME ZONE,
    tentativas_login_falhadas INTEGER DEFAULT 0,
    conta_bloqueada_ate TIMESTAMP WITH TIME ZONE
);

-- Criar índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_usuarios_email ON public.usuarios(email);
CREATE INDEX IF NOT EXISTS idx_usuarios_whatsapp ON public.usuarios(whatsapp);
CREATE INDEX IF NOT EXISTS idx_usuarios_ativo ON public.usuarios(ativo);
CREATE INDEX IF NOT EXISTS idx_usuarios_email_verificado ON public.usuarios(email_verificado);

-- Habilitar RLS (Row Level Security)
ALTER TABLE public.usuarios ENABLE ROW LEVEL SECURITY;

-- Política para permitir acesso via service key
CREATE POLICY "Permitir acesso via service key" ON public.usuarios
FOR ALL USING (auth.role() = 'service_role');

-- Política para usuários autenticados verem apenas seus próprios dados
CREATE POLICY "Usuários podem ver seus próprios dados" ON public.usuarios
FOR SELECT USING (auth.uid()::text = id::text);

-- Política para usuários atualizarem apenas seus próprios dados
CREATE POLICY "Usuários podem atualizar seus próprios dados" ON public.usuarios
FOR UPDATE USING (auth.uid()::text = id::text);

-- Comentários para documentação
COMMENT ON TABLE public.usuarios IS 'Tabela principal de usuários do sistema';
COMMENT ON COLUMN public.usuarios.id IS 'ID único do usuário';
COMMENT ON COLUMN public.usuarios.nome IS 'Nome completo do usuário';
COMMENT ON COLUMN public.usuarios.email IS 'Email único do usuário';
COMMENT ON COLUMN public.usuarios.senha IS 'Hash da senha do usuário (PASSWORD_DEFAULT)';
COMMENT ON COLUMN public.usuarios.whatsapp IS 'Número do WhatsApp com código do país (+5511999999999)';
COMMENT ON COLUMN public.usuarios.whatsapp_confirmado IS 'Se o WhatsApp foi confirmado via código';
COMMENT ON COLUMN public.usuarios.codigo_ativacao IS 'Código de 6 dígitos para ativação';
COMMENT ON COLUMN public.usuarios.codigo_gerado_em IS 'Quando o código de ativação foi gerado';
COMMENT ON COLUMN public.usuarios.ativo IS 'Se a conta está ativa';
COMMENT ON COLUMN public.usuarios.email_verificado IS 'Se o email foi verificado';
COMMENT ON COLUMN public.usuarios.criado_em IS 'Data de criação da conta';
COMMENT ON COLUMN public.usuarios.ultimo_login IS 'Data do último login';
COMMENT ON COLUMN public.usuarios.tentativas_login_falhadas IS 'Número de tentativas de login falhadas';
COMMENT ON COLUMN public.usuarios.conta_bloqueada_ate IS 'Data até quando a conta está bloqueada';

-- Inserir usuário de teste (opcional)
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
) ON CONFLICT (email) DO NOTHING;

-- Verificar se a tabela foi criada corretamente
SELECT 
    table_name,
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns 
WHERE table_name = 'usuarios' 
AND table_schema = 'public'
ORDER BY ordinal_position;