-- ============================================================================
-- CONFIGURAÇÃO ESPECÍFICA PARA SUPABASE
-- Script otimizado para o Supabase PostgreSQL
-- ============================================================================

-- IMPORTANTE: Execute este script no Supabase SQL Editor
-- Este script é otimizado para as funcionalidades específicas do Supabase

-- ============================================================================
-- 1. HABILITAR EXTENSÕES NECESSÁRIAS
-- ============================================================================
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ============================================================================
-- 2. TABELAS PRINCIPAIS (compatível com Supabase Auth)
-- ============================================================================

-- Tabela de usuários (estende auth.users do Supabase)
CREATE TABLE IF NOT EXISTS public.usuarios (
    id UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
    
    -- Informações do perfil
    nome VARCHAR(100) NOT NULL,
    whatsapp VARCHAR(20),
    whatsapp_confirmado BOOLEAN DEFAULT FALSE,
    
    -- Códigos de ativação
    codigo_ativacao VARCHAR(10),
    codigo_gerado_em TIMESTAMP WITH TIME ZONE,
    tentativas_codigo INTEGER DEFAULT 0,
    
    -- Controle de acesso
    ativo BOOLEAN DEFAULT TRUE,
    tentativas_login_falhadas INTEGER DEFAULT 0,
    conta_bloqueada_ate TIMESTAMP WITH TIME ZONE,
    ultimo_login TIMESTAMP WITH TIME ZONE,
    
    -- Metadados
    criado_em TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    atualizado_em TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    
    -- Constraints
    CONSTRAINT usuarios_nome_check CHECK (LENGTH(nome) >= 2),
    CONSTRAINT usuarios_whatsapp_check CHECK (whatsapp IS NULL OR LENGTH(whatsapp) >= 10)
);

-- Tabela para tokens de recuperação de senha
CREATE TABLE IF NOT EXISTS public.password_resets (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    
    -- Relacionamento com usuário
    user_id UUID NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL,
    
    -- Token de recuperação
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    
    -- Status e metadados
    usado BOOLEAN DEFAULT FALSE,
    usado_em TIMESTAMP WITH TIME ZONE,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    
    -- Constraints
    CONSTRAINT password_resets_expires_check CHECK (expires_at > created_at),
    CONSTRAINT password_resets_token_check CHECK (LENGTH(token) >= 32)
);

-- Tabela para log de tentativas de login
CREATE TABLE IF NOT EXISTS public.user_login_attempts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    
    -- Identificação
    user_id UUID REFERENCES auth.users(id) ON DELETE SET NULL,
    email VARCHAR(255),
    
    -- Resultado da tentativa
    sucesso BOOLEAN NOT NULL,
    motivo_falha VARCHAR(100),
    
    -- Informações de acesso
    ip_address INET NOT NULL,
    user_agent TEXT,
    
    -- Timestamp
    tentativa_em TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    
    -- Constraints
    CONSTRAINT user_login_attempts_email_check CHECK (email IS NOT NULL)
);

-- ============================================================================
-- 3. ÍNDICES PARA PERFORMANCE
-- ============================================================================

-- Índices para usuarios
CREATE INDEX IF NOT EXISTS idx_usuarios_whatsapp ON public.usuarios(whatsapp);
CREATE INDEX IF NOT EXISTS idx_usuarios_ativo ON public.usuarios(ativo);
CREATE INDEX IF NOT EXISTS idx_usuarios_criado_em ON public.usuarios(criado_em);

-- Índices para password_resets
CREATE INDEX IF NOT EXISTS idx_password_resets_token ON public.password_resets(token);
CREATE INDEX IF NOT EXISTS idx_password_resets_user_id ON public.password_resets(user_id);
CREATE INDEX IF NOT EXISTS idx_password_resets_expires_at ON public.password_resets(expires_at);
CREATE INDEX IF NOT EXISTS idx_password_resets_email ON public.password_resets(email);

-- Índices para user_login_attempts
CREATE INDEX IF NOT EXISTS idx_user_login_attempts_email ON public.user_login_attempts(email);
CREATE INDEX IF NOT EXISTS idx_user_login_attempts_user_id ON public.user_login_attempts(user_id);
CREATE INDEX IF NOT EXISTS idx_user_login_attempts_ip_address ON public.user_login_attempts(ip_address);
CREATE INDEX IF NOT EXISTS idx_user_login_attempts_tentativa_em ON public.user_login_attempts(tentativa_em);

-- ============================================================================
-- 4. TRIGGERS PARA ATUALIZAÇÃO AUTOMÁTICA
-- ============================================================================

-- Função para atualizar timestamp
CREATE OR REPLACE FUNCTION public.handle_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.atualizado_em = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Trigger para usuarios
DROP TRIGGER IF EXISTS usuarios_updated_at ON public.usuarios;
CREATE TRIGGER usuarios_updated_at
    BEFORE UPDATE ON public.usuarios
    FOR EACH ROW
    EXECUTE FUNCTION public.handle_updated_at();

-- ============================================================================
-- 5. POLÍTICAS RLS (ROW LEVEL SECURITY)
-- ============================================================================

-- Habilitar RLS
ALTER TABLE public.usuarios ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.password_resets ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.user_login_attempts ENABLE ROW LEVEL SECURITY;

-- Políticas para usuarios
DROP POLICY IF EXISTS "Usuários podem ver seu próprio perfil" ON public.usuarios;
CREATE POLICY "Usuários podem ver seu próprio perfil"
    ON public.usuarios FOR SELECT
    TO authenticated
    USING (auth.uid() = id);

DROP POLICY IF EXISTS "Usuários podem atualizar seu próprio perfil" ON public.usuarios;
CREATE POLICY "Usuários podem atualizar seu próprio perfil"
    ON public.usuarios FOR UPDATE
    TO authenticated
    USING (auth.uid() = id);

DROP POLICY IF EXISTS "Permitir inserção de perfil" ON public.usuarios;
CREATE POLICY "Permitir inserção de perfil"
    ON public.usuarios FOR INSERT
    TO authenticated
    WITH CHECK (auth.uid() = id);

-- Políticas para password_resets
DROP POLICY IF EXISTS "Usuários podem ver seus próprios tokens" ON public.password_resets;
CREATE POLICY "Usuários podem ver seus próprios tokens"
    ON public.password_resets FOR SELECT
    TO authenticated
    USING (auth.uid() = user_id);

DROP POLICY IF EXISTS "Permitir inserção de tokens de reset" ON public.password_resets;
CREATE POLICY "Permitir inserção de tokens de reset"
    ON public.password_resets FOR INSERT
    TO anon, authenticated
    WITH CHECK (true);

-- Políticas para user_login_attempts (apenas inserção)
DROP POLICY IF EXISTS "Permitir inserção de tentativas de login" ON public.user_login_attempts;
CREATE POLICY "Permitir inserção de tentativas de login"
    ON public.user_login_attempts FOR INSERT
    TO anon, authenticated
    WITH CHECK (true);

-- ============================================================================
-- 6. FUNÇÕES PARA GESTÃO DE TOKENS E SEGURANÇA
-- ============================================================================

-- Função para gerar token de recuperação
CREATE OR REPLACE FUNCTION public.generate_password_reset_token(
    user_email TEXT
)
RETURNS TABLE(token TEXT, expires_at TIMESTAMP WITH TIME ZONE) AS $$
DECLARE
    user_record RECORD;
    new_token TEXT;
    expiry_time TIMESTAMP WITH TIME ZONE;
BEGIN
    -- Buscar usuário pelo email
    SELECT au.id, au.email INTO user_record
    FROM auth.users au
    WHERE au.email = user_email;
    
    IF NOT FOUND THEN
        RETURN; -- Não revelar se o email existe
    END IF;
    
    -- Gerar token
    new_token := encode(gen_random_bytes(32), 'hex');
    expiry_time := NOW() + INTERVAL '1 hour';
    
    -- Limpar tokens antigos do usuário
    DELETE FROM public.password_resets 
    WHERE user_id = user_record.id;
    
    -- Inserir novo token
    INSERT INTO public.password_resets (
        user_id, email, token, expires_at
    ) VALUES (
        user_record.id, user_record.email, new_token, expiry_time
    );
    
    RETURN QUERY SELECT new_token, expiry_time;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Função para verificar token de recuperação
CREATE OR REPLACE FUNCTION public.verify_password_reset_token(
    reset_token TEXT
)
RETURNS TABLE(user_id UUID, email TEXT, valid BOOLEAN) AS $$
DECLARE
    token_record RECORD;
BEGIN
    SELECT pr.user_id, pr.email, pr.expires_at, pr.usado
    INTO token_record
    FROM public.password_resets pr
    WHERE pr.token = reset_token;
    
    IF NOT FOUND THEN
        RETURN QUERY SELECT NULL::UUID, NULL::TEXT, FALSE;
        RETURN;
    END IF;
    
    -- Verificar se token não expirou e não foi usado
    IF token_record.usado OR token_record.expires_at < NOW() THEN
        RETURN QUERY SELECT token_record.user_id, token_record.email, FALSE;
        RETURN;
    END IF;
    
    RETURN QUERY SELECT token_record.user_id, token_record.email, TRUE;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Função para registrar tentativa de login
CREATE OR REPLACE FUNCTION public.log_login_attempt(
    user_email TEXT,
    login_success BOOLEAN,
    failure_reason TEXT DEFAULT NULL,
    client_ip INET DEFAULT NULL,
    client_user_agent TEXT DEFAULT NULL
)
RETURNS VOID AS $$
DECLARE
    user_record RECORD;
BEGIN
    -- Buscar usuário (pode não existir)
    SELECT au.id INTO user_record
    FROM auth.users au
    WHERE au.email = user_email;
    
    -- Inserir log da tentativa
    INSERT INTO public.user_login_attempts (
        user_id, email, sucesso, motivo_falha, ip_address, user_agent
    ) VALUES (
        user_record.id, user_email, login_success, failure_reason, client_ip, client_user_agent
    );
    
    -- Se sucesso, atualizar último login
    IF login_success AND user_record.id IS NOT NULL THEN
        UPDATE public.usuarios 
        SET ultimo_login = NOW(),
            tentativas_login_falhadas = 0,
            conta_bloqueada_ate = NULL
        WHERE id = user_record.id;
    ELSIF NOT login_success AND user_record.id IS NOT NULL THEN
        -- Incrementar tentativas falhadas
        UPDATE public.usuarios 
        SET tentativas_login_falhadas = COALESCE(tentativas_login_falhadas, 0) + 1
        WHERE id = user_record.id;
        
        -- Bloquear conta após 5 tentativas
        UPDATE public.usuarios 
        SET conta_bloqueada_ate = NOW() + INTERVAL '15 minutes'
        WHERE id = user_record.id 
          AND tentativas_login_falhadas >= 5;
    END IF;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Função para limpeza automática
CREATE OR REPLACE FUNCTION public.cleanup_auth_data()
RETURNS TEXT AS $$
DECLARE
    deleted_tokens INTEGER;
    deleted_attempts INTEGER;
BEGIN
    -- Limpar tokens expirados
    DELETE FROM public.password_resets 
    WHERE expires_at < NOW() OR usado = TRUE;
    GET DIAGNOSTICS deleted_tokens = ROW_COUNT;
    
    -- Limpar tentativas antigas (30 dias)
    DELETE FROM public.user_login_attempts 
    WHERE tentativa_em < NOW() - INTERVAL '30 days';
    GET DIAGNOSTICS deleted_attempts = ROW_COUNT;
    
    RETURN format('Limpeza concluída: %s tokens, %s tentativas removidas', 
                  deleted_tokens, deleted_attempts);
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- ============================================================================
-- 7. VIEWS ÚTEIS
-- ============================================================================

-- View para estatísticas (acessível apenas para authenticated)
CREATE OR REPLACE VIEW public.usuarios_stats AS
SELECT 
    COUNT(*) as total_usuarios,
    COUNT(*) FILTER (WHERE u.ativo = TRUE) as usuarios_ativos,
    COUNT(*) FILTER (WHERE au.email_confirmed_at IS NOT NULL) as emails_verificados,
    COUNT(*) FILTER (WHERE u.whatsapp_confirmado = TRUE) as whatsapp_confirmados,
    COUNT(*) FILTER (WHERE u.ultimo_login > NOW() - INTERVAL '30 days') as ativos_ultimo_mes
FROM public.usuarios u
JOIN auth.users au ON u.id = au.id;

-- ============================================================================
-- 8. CONFIGURAÇÕES DE SEGURANÇA SUPABASE
-- ============================================================================

-- Permitir acesso anônimo apenas para funções específicas
GRANT EXECUTE ON FUNCTION public.generate_password_reset_token(TEXT) TO anon, authenticated;
GRANT EXECUTE ON FUNCTION public.verify_password_reset_token(TEXT) TO anon, authenticated;
GRANT EXECUTE ON FUNCTION public.log_login_attempt(TEXT, BOOLEAN, TEXT, INET, TEXT) TO anon, authenticated;

-- Permitir apenas leitura das estatísticas para usuários autenticados
GRANT SELECT ON public.usuarios_stats TO authenticated;

-- ============================================================================
-- 9. DADOS INICIAIS / TESTE
-- ============================================================================

-- Criar usuário de teste (executar via Supabase Auth, não diretamente)
-- Este é apenas um exemplo - use o Supabase Dashboard para criar usuários

/*
-- Exemplo de como criar via API Supabase:
-- 1. Vá para Authentication > Users no Dashboard
-- 2. Clique em "Add user"
-- 3. Use: admin@teste.com / Admin123!
-- 4. Confirme o email
-- 5. Execute o INSERT abaixo para criar o perfil:

INSERT INTO public.usuarios (id, nome, ativo, whatsapp_confirmado)
SELECT 
    au.id,
    'Administrador',
    TRUE,
    TRUE
FROM auth.users au 
WHERE au.email = 'admin@teste.com'
AND NOT EXISTS (SELECT 1 FROM public.usuarios WHERE id = au.id);
*/

-- ============================================================================
-- 10. VERIFICAÇÕES FINAIS
-- ============================================================================

DO $$
BEGIN
    -- Verificar se as tabelas foram criadas
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'usuarios' AND table_schema = 'public') THEN
        RAISE NOTICE '✅ Tabela usuarios criada com sucesso';
    END IF;
    
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'password_resets' AND table_schema = 'public') THEN
        RAISE NOTICE '✅ Tabela password_resets criada com sucesso';
    END IF;
    
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'user_login_attempts' AND table_schema = 'public') THEN
        RAISE NOTICE '✅ Tabela user_login_attempts criada com sucesso';
    END IF;
    
    RAISE NOTICE '🚀 Configuração do Supabase concluída!';
    RAISE NOTICE '📧 Crie o usuário admin@teste.com via Dashboard';
    RAISE NOTICE '🔧 Execute: SELECT public.cleanup_auth_data(); para limpeza';
    RAISE NOTICE '📊 Execute: SELECT * FROM public.usuarios_stats; para estatísticas';
END $$;
