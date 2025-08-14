-- ============================================================================
-- SCRIPT DE MIGRAÇÃO SIMPLIFICADO - ATUALIZAR TABELAS EXISTENTES
-- Execute este script para atualizar a estrutura das tabelas sem perder dados
-- ============================================================================

-- 1. VERIFICAR E ATUALIZAR TABELA usuarios
DO $$
BEGIN
    -- Adicionar colunas que podem não existir
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'senha_hash') THEN
        ALTER TABLE usuarios ADD COLUMN senha_hash VARCHAR(255);
        RAISE NOTICE 'Coluna senha_hash adicionada';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'whatsapp_confirmado') THEN
        ALTER TABLE usuarios ADD COLUMN whatsapp_confirmado BOOLEAN DEFAULT FALSE;
        RAISE NOTICE 'Coluna whatsapp_confirmado adicionada';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'codigo_ativacao') THEN
        ALTER TABLE usuarios ADD COLUMN codigo_ativacao VARCHAR(10);
        RAISE NOTICE 'Coluna codigo_ativacao adicionada';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'codigo_gerado_em') THEN
        ALTER TABLE usuarios ADD COLUMN codigo_gerado_em TIMESTAMP WITH TIME ZONE;
        RAISE NOTICE 'Coluna codigo_gerado_em adicionada';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'tentativas_codigo') THEN
        ALTER TABLE usuarios ADD COLUMN tentativas_codigo INTEGER DEFAULT 0;
        RAISE NOTICE 'Coluna tentativas_codigo adicionada';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'ultimo_login') THEN
        ALTER TABLE usuarios ADD COLUMN ultimo_login TIMESTAMP WITH TIME ZONE;
        RAISE NOTICE 'Coluna ultimo_login adicionada';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'tentativas_login_falhadas') THEN
        ALTER TABLE usuarios ADD COLUMN tentativas_login_falhadas INTEGER DEFAULT 0;
        RAISE NOTICE 'Coluna tentativas_login_falhadas adicionada';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'conta_bloqueada_ate') THEN
        ALTER TABLE usuarios ADD COLUMN conta_bloqueada_ate TIMESTAMP WITH TIME ZONE;
        RAISE NOTICE 'Coluna conta_bloqueada_ate adicionada';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'atualizado_em') THEN
        ALTER TABLE usuarios ADD COLUMN atualizado_em TIMESTAMP WITH TIME ZONE DEFAULT NOW();
        RAISE NOTICE 'Coluna atualizado_em adicionada';
    END IF;
    
    RAISE NOTICE 'Verificação da tabela usuarios concluída';
END $$;

-- 2. VERIFICAR E ATUALIZAR TABELA password_resets
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'password_resets') THEN
        CREATE TABLE password_resets (
            id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            user_id UUID NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
            email VARCHAR(255) NOT NULL,
            token VARCHAR(255) NOT NULL UNIQUE,
            expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
            usado BOOLEAN DEFAULT FALSE,
            usado_em TIMESTAMP WITH TIME ZONE,
            ip_address INET,
            user_agent TEXT,
            created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
        );
        RAISE NOTICE 'Tabela password_resets criada';
    ELSE
        -- Adicionar colunas que podem não existir
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'password_resets' AND column_name = 'ip_address') THEN
            ALTER TABLE password_resets ADD COLUMN ip_address INET;
            RAISE NOTICE 'Coluna ip_address adicionada em password_resets';
        END IF;
        
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'password_resets' AND column_name = 'user_agent') THEN
            ALTER TABLE password_resets ADD COLUMN user_agent TEXT;
            RAISE NOTICE 'Coluna user_agent adicionada em password_resets';
        END IF;
    END IF;
    
    RAISE NOTICE 'Verificação da tabela password_resets concluída';
END $$;

-- 3. VERIFICAR E ATUALIZAR TABELA user_sessions
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'user_sessions') THEN
        CREATE TABLE user_sessions (
            id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            user_id UUID NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
            session_token VARCHAR(255) NOT NULL UNIQUE,
            ip_address INET,
            user_agent TEXT,
            created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
            expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
            last_activity TIMESTAMP WITH TIME ZONE DEFAULT NOW()
        );
        RAISE NOTICE 'Tabela user_sessions criada';
    END IF;
    
    RAISE NOTICE 'Verificação da tabela user_sessions concluída';
END $$;

-- 4. VERIFICAR E ATUALIZAR TABELA user_login_attempts
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'user_login_attempts') THEN
        CREATE TABLE user_login_attempts (
            id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            email VARCHAR(255) NOT NULL,
            sucesso BOOLEAN NOT NULL,
            motivo_falha TEXT,
            ip_address INET,
            user_agent TEXT,
            created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
        );
        RAISE NOTICE 'Tabela user_login_attempts criada';
    END IF;
    
    RAISE NOTICE 'Verificação da tabela user_login_attempts concluída';
END $$;

-- 5. VERIFICAR E ATUALIZAR VIEW usuarios_stats
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.views WHERE table_name = 'usuarios_stats') THEN
        CREATE VIEW usuarios_stats AS
        SELECT 
            COUNT(*) as total_usuarios,
            COUNT(CASE WHEN ativo = true THEN 1 END) as usuarios_ativos,
            COUNT(CASE WHEN email_verificado = true THEN 1 END) as emails_verificados,
            COUNT(CASE WHEN whatsapp_confirmado = true THEN 1 END) as whatsapp_confirmados,
            COUNT(CASE WHEN criado_em >= NOW() - INTERVAL '30 days' THEN 1 END) as novos_30_dias,
            COUNT(CASE WHEN ultimo_login >= NOW() - INTERVAL '7 days' THEN 1 END) as ativos_7_dias
        FROM usuarios;
        RAISE NOTICE 'View usuarios_stats criada';
    END IF;
    
    RAISE NOTICE 'Verificação da view usuarios_stats concluída';
END $$;

-- 6. VERIFICAR E CRIAR ÍNDICES NECESSÁRIOS
DO $$
BEGIN
    -- Índices para tabela usuarios
    IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE tablename = 'usuarios' AND indexname = 'idx_usuarios_email') THEN
        CREATE INDEX idx_usuarios_email ON usuarios(email);
        RAISE NOTICE 'Índice idx_usuarios_email criado';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE tablename = 'usuarios' AND indexname = 'idx_usuarios_whatsapp') THEN
        CREATE INDEX idx_usuarios_whatsapp ON usuarios(whatsapp);
        RAISE NOTICE 'Índice idx_usuarios_whatsapp criado';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE tablename = 'usuarios' AND indexname = 'idx_usuarios_ativo') THEN
        CREATE INDEX idx_usuarios_ativo ON usuarios(ativo);
        RAISE NOTICE 'Índice idx_usuarios_ativo criado';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE tablename = 'usuarios' AND indexname = 'idx_usuarios_criado_em') THEN
        CREATE INDEX idx_usuarios_criado_em ON usuarios(criado_em);
        RAISE NOTICE 'Índice idx_usuarios_criado_em criado';
    END IF;
    
    -- Índices para outras tabelas
    IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE tablename = 'password_resets' AND indexname = 'idx_password_resets_token') THEN
        CREATE INDEX idx_password_resets_token ON password_resets(token);
        RAISE NOTICE 'Índice idx_password_resets_token criado';
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE tablename = 'user_login_attempts' AND indexname = 'idx_user_login_attempts_email') THEN
        CREATE INDEX idx_user_login_attempts_email ON user_login_attempts(email);
        RAISE NOTICE 'Índice idx_user_login_attempts_email criado';
    END IF;
    
    RAISE NOTICE 'Verificação dos índices concluída';
END $$;

-- 7. VERIFICAR E CRIAR TRIGGERS NECESSÁRIOS
DO $$
BEGIN
    -- Trigger para atualizar timestamp de atualização
    IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'usuarios_updated_at_trigger') THEN
        CREATE OR REPLACE FUNCTION update_updated_at_column()
        RETURNS TRIGGER AS $$
        BEGIN
            NEW.atualizado_em = NOW();
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;
        
        CREATE TRIGGER usuarios_updated_at_trigger
            BEFORE UPDATE ON usuarios
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column();
        
        RAISE NOTICE 'Trigger usuarios_updated_at_trigger criado';
    END IF;
    
    RAISE NOTICE 'Verificação dos triggers concluída';
END $$;

-- 8. VERIFICAR E CONFIGURAR RLS (Row Level Security)
DO $$
BEGIN
    -- Habilitar RLS na tabela usuarios
    ALTER TABLE usuarios ENABLE ROW LEVEL SECURITY;
    
    -- Política para usuários verem apenas seus próprios dados
    IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'usuarios' AND policyname = 'usuarios_select_policy') THEN
        CREATE POLICY usuarios_select_policy ON usuarios
            FOR SELECT USING (auth.uid()::text = id::text);
        RAISE NOTICE 'Política RLS usuarios_select_policy criada';
    END IF;
    
    -- Política para usuários atualizarem apenas seus próprios dados
    IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'usuarios' AND policyname = 'usuarios_update_policy') THEN
        CREATE POLICY usuarios_update_policy ON usuarios
            FOR UPDATE USING (auth.uid()::text = id::text);
        RAISE NOTICE 'Política RLS usuarios_update_policy criada';
    END IF;
    
    -- Política para inserção (cadastro)
    IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'usuarios' AND policyname = 'usuarios_insert_policy') THEN
        CREATE POLICY usuarios_insert_policy ON usuarios
            FOR INSERT WITH CHECK (true);
        RAISE NOTICE 'Política RLS usuarios_insert_policy criada';
    END IF;
    
    RAISE NOTICE 'Configuração RLS concluída';
END $$;

-- 9. MENSAGEM FINAL
DO $$
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE 'MIGRAÇÃO SIMPLIFICADA CONCLUÍDA COM SUCESSO!';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Todas as tabelas e estruturas básicas foram verificadas e criadas/atualizadas.';
    RAISE NOTICE 'O sistema de autenticação está pronto para uso.';
    RAISE NOTICE '========================================';
END $$;
