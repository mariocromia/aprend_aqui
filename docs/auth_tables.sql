-- ============================================================================
-- SISTEMA DE AUTENTICAÇÃO - PROMPT BUILDER IA
-- Script de criação das tabelas para controle de acesso
-- ============================================================================

-- Remover tabelas existentes (cuidado em produção!)
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS user_sessions;
DROP TABLE IF EXISTS user_login_attempts;
DROP TABLE IF EXISTS usuarios;

-- ============================================================================
-- TABELA: usuarios
-- Armazena informações básicas dos usuários do sistema
-- ============================================================================
CREATE TABLE usuarios (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    
    -- Informações básicas
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    
    -- Contato
    whatsapp VARCHAR(20),
    whatsapp_confirmado BOOLEAN DEFAULT FALSE,
    
    -- Status da conta
    ativo BOOLEAN DEFAULT TRUE,
    email_verificado BOOLEAN DEFAULT FALSE,
    
    -- Códigos de ativação/verificação
    codigo_ativacao VARCHAR(10),
    codigo_gerado_em TIMESTAMP WITH TIME ZONE,
    tentativas_codigo INTEGER DEFAULT 0,
    
    -- Controle de acesso
    ultimo_login TIMESTAMP WITH TIME ZONE,
    tentativas_login_falhadas INTEGER DEFAULT 0,
    conta_bloqueada_ate TIMESTAMP WITH TIME ZONE,
    
    -- Metadados
    criado_em TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    atualizado_em TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    
    -- Constraints
    CONSTRAINT usuarios_email_check CHECK (email ~* '^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$'),
    CONSTRAINT usuarios_nome_check CHECK (LENGTH(nome) >= 2),
    CONSTRAINT usuarios_whatsapp_check CHECK (whatsapp IS NULL OR LENGTH(whatsapp) >= 10)
);

-- Índices para otimização
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_whatsapp ON usuarios(whatsapp);
CREATE INDEX idx_usuarios_ativo ON usuarios(ativo);
CREATE INDEX idx_usuarios_criado_em ON usuarios(criado_em);

-- ============================================================================
-- TABELA: password_resets
-- Gerencia tokens de recuperação de senha
-- ============================================================================
CREATE TABLE password_resets (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    
    -- Relacionamento com usuário
    user_id UUID NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL,
    
    -- Token de recuperação
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    
    -- Status
    usado BOOLEAN DEFAULT FALSE,
    usado_em TIMESTAMP WITH TIME ZONE,
    ip_address INET,
    user_agent TEXT,
    
    -- Metadados
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    
    -- Constraints
    CONSTRAINT password_resets_expires_check CHECK (expires_at > created_at),
    CONSTRAINT password_resets_token_check CHECK (LENGTH(token) >= 32)
);

-- Índices para otimização
CREATE INDEX idx_password_resets_token ON password_resets(token);
CREATE INDEX idx_password_resets_user_id ON password_resets(user_id);
CREATE INDEX idx_password_resets_expires_at ON password_resets(expires_at);
CREATE INDEX idx_password_resets_email ON password_resets(email);

-- ============================================================================
-- TABELA: user_sessions
-- Gerencia sessões ativas dos usuários
-- ============================================================================
CREATE TABLE user_sessions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    
    -- Relacionamento com usuário
    user_id UUID NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    
    -- Dados da sessão
    session_id VARCHAR(255) NOT NULL UNIQUE,
    session_data TEXT,
    
    -- Informações de acesso
    ip_address INET,
    user_agent TEXT,
    device_info JSONB,
    
    -- Controle de tempo
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    last_activity TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    
    -- Status
    ativo BOOLEAN DEFAULT TRUE,
    logout_em TIMESTAMP WITH TIME ZONE,
    
    -- Constraints
    CONSTRAINT user_sessions_expires_check CHECK (expires_at > created_at),
    CONSTRAINT user_sessions_session_id_check CHECK (LENGTH(session_id) >= 20)
);

-- Índices para otimização
CREATE INDEX idx_user_sessions_session_id ON user_sessions(session_id);
CREATE INDEX idx_user_sessions_user_id ON user_sessions(user_id);
CREATE INDEX idx_user_sessions_expires_at ON user_sessions(expires_at);
CREATE INDEX idx_user_sessions_ativo ON user_sessions(ativo);
CREATE INDEX idx_user_sessions_last_activity ON user_sessions(last_activity);

-- ============================================================================
-- TABELA: user_login_attempts
-- Log de tentativas de login (segurança e auditoria)
-- ============================================================================
CREATE TABLE user_login_attempts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    
    -- Identificação (pode não ter user_id se login falhou)
    user_id UUID REFERENCES usuarios(id) ON DELETE SET NULL,
    email VARCHAR(255),
    
    -- Resultado da tentativa
    sucesso BOOLEAN NOT NULL,
    motivo_falha VARCHAR(100), -- 'senha_incorreta', 'email_nao_encontrado', 'conta_bloqueada', etc.
    
    -- Informações de acesso
    ip_address INET NOT NULL,
    user_agent TEXT,
    device_info JSONB,
    
    -- Localização (opcional)
    pais VARCHAR(2),
    cidade VARCHAR(100),
    
    -- Timestamp
    tentativa_em TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    
    -- Constraints
    CONSTRAINT user_login_attempts_email_check CHECK (email IS NOT NULL),
    CONSTRAINT user_login_attempts_motivo_check CHECK (
        (sucesso = TRUE AND motivo_falha IS NULL) OR 
        (sucesso = FALSE AND motivo_falha IS NOT NULL)
    )
);

-- Índices para otimização e segurança
CREATE INDEX idx_user_login_attempts_email ON user_login_attempts(email);
CREATE INDEX idx_user_login_attempts_user_id ON user_login_attempts(user_id);
CREATE INDEX idx_user_login_attempts_ip_address ON user_login_attempts(ip_address);
CREATE INDEX idx_user_login_attempts_tentativa_em ON user_login_attempts(tentativa_em);
CREATE INDEX idx_user_login_attempts_sucesso ON user_login_attempts(sucesso);

-- ============================================================================
-- TRIGGERS PARA ATUALIZAÇÃO AUTOMÁTICA
-- ============================================================================

-- Trigger para atualizar atualizado_em na tabela usuarios
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.atualizado_em = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER trigger_usuarios_updated_at
    BEFORE UPDATE ON usuarios
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Trigger para atualizar last_activity nas sessões
CREATE OR REPLACE FUNCTION update_session_activity()
RETURNS TRIGGER AS $$
BEGIN
    NEW.last_activity = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER trigger_user_sessions_activity
    BEFORE UPDATE ON user_sessions
    FOR EACH ROW
    EXECUTE FUNCTION update_session_activity();

-- ============================================================================
-- FUNÇÕES ÚTEIS PARA SEGURANÇA
-- ============================================================================

-- Função para limpar tokens expirados
CREATE OR REPLACE FUNCTION cleanup_expired_tokens()
RETURNS INTEGER AS $$
DECLARE
    deleted_count INTEGER;
BEGIN
    DELETE FROM password_resets 
    WHERE expires_at < NOW() OR usado = TRUE;
    
    GET DIAGNOSTICS deleted_count = ROW_COUNT;
    RETURN deleted_count;
END;
$$ LANGUAGE plpgsql;

-- Função para limpar sessões expiradas
CREATE OR REPLACE FUNCTION cleanup_expired_sessions()
RETURNS INTEGER AS $$
DECLARE
    deleted_count INTEGER;
BEGIN
    DELETE FROM user_sessions 
    WHERE expires_at < NOW() OR 
          (last_activity < NOW() - INTERVAL '30 days');
    
    GET DIAGNOSTICS deleted_count = ROW_COUNT;
    RETURN deleted_count;
END;
$$ LANGUAGE plpgsql;

-- Função para verificar tentativas de login suspeitas
CREATE OR REPLACE FUNCTION check_suspicious_login_attempts(
    p_email VARCHAR(255),
    p_ip_address INET,
    p_time_window INTERVAL DEFAULT '15 minutes'
)
RETURNS INTEGER AS $$
DECLARE
    attempt_count INTEGER;
BEGIN
    SELECT COUNT(*) INTO attempt_count
    FROM user_login_attempts
    WHERE (email = p_email OR ip_address = p_ip_address)
      AND sucesso = FALSE
      AND tentativa_em > NOW() - p_time_window;
    
    RETURN attempt_count;
END;
$$ LANGUAGE plpgsql;

-- ============================================================================
-- POLÍTICAS DE SEGURANÇA RLS (Row Level Security)
-- ============================================================================

-- Habilitar RLS nas tabelas sensíveis
ALTER TABLE usuarios ENABLE ROW LEVEL SECURITY;
ALTER TABLE user_sessions ENABLE ROW LEVEL SECURITY;
ALTER TABLE password_resets ENABLE ROW LEVEL SECURITY;

-- Política: Usuários podem ver apenas seus próprios dados
CREATE POLICY usuarios_self_access ON usuarios
    FOR ALL TO authenticated
    USING (auth.uid() = id);

-- Política: Sessões próprias apenas
CREATE POLICY user_sessions_self_access ON user_sessions
    FOR ALL TO authenticated
    USING (auth.uid() = user_id);

-- Política: Tokens de reset próprios apenas
CREATE POLICY password_resets_self_access ON password_resets
    FOR ALL TO authenticated
    USING (auth.uid() = user_id);

-- ============================================================================
-- DADOS INICIAIS / SEED DATA
-- ============================================================================

-- Inserir usuário administrador padrão (senha: Admin123!)
INSERT INTO usuarios (
    nome, 
    email, 
    senha_hash, 
    ativo, 
    email_verificado, 
    whatsapp_confirmado
) VALUES (
    'Administrador',
    'admin@teste.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Admin123!
    TRUE,
    TRUE,
    TRUE
) ON CONFLICT (email) DO NOTHING;

-- ============================================================================
-- COMENTÁRIOS E DOCUMENTAÇÃO
-- ============================================================================

COMMENT ON TABLE usuarios IS 'Tabela principal de usuários do sistema';
COMMENT ON COLUMN usuarios.id IS 'Identificador único do usuário (UUID)';
COMMENT ON COLUMN usuarios.nome IS 'Nome completo do usuário';
COMMENT ON COLUMN usuarios.email IS 'Email único do usuário (usado para login)';
COMMENT ON COLUMN usuarios.senha_hash IS 'Hash da senha usando bcrypt';
COMMENT ON COLUMN usuarios.whatsapp IS 'Número do WhatsApp no formato internacional';
COMMENT ON COLUMN usuarios.codigo_ativacao IS 'Código de 6 dígitos para ativação';
COMMENT ON COLUMN usuarios.tentativas_login_falhadas IS 'Contador de tentativas de login falhadas consecutivas';
COMMENT ON COLUMN usuarios.conta_bloqueada_ate IS 'Data até quando a conta está bloqueada por tentativas excessivas';

COMMENT ON TABLE password_resets IS 'Tokens para recuperação de senha';
COMMENT ON COLUMN password_resets.token IS 'Token único de 64 caracteres para recuperação';
COMMENT ON COLUMN password_resets.expires_at IS 'Data de expiração do token (recomendado: 1 hora)';

COMMENT ON TABLE user_sessions IS 'Sessões ativas dos usuários';
COMMENT ON COLUMN user_sessions.session_id IS 'ID da sessão PHP/aplicação';
COMMENT ON COLUMN user_sessions.device_info IS 'Informações do dispositivo em formato JSON';

COMMENT ON TABLE user_login_attempts IS 'Log de todas as tentativas de login para auditoria e segurança';
COMMENT ON COLUMN user_login_attempts.motivo_falha IS 'Motivo específico da falha no login';

-- ============================================================================
-- ÍNDICES COMPOSTOS PARA CONSULTAS COMPLEXAS
-- ============================================================================

-- Índice para consultas de segurança por IP e tempo
CREATE INDEX idx_login_attempts_security ON user_login_attempts(ip_address, tentativa_em, sucesso);

-- Índice para consultas de sessões ativas por usuário
CREATE INDEX idx_user_sessions_active ON user_sessions(user_id, ativo, expires_at);

-- Índice para limpeza de tokens
CREATE INDEX idx_password_resets_cleanup ON password_resets(expires_at, usado);

-- ============================================================================
-- VIEWS ÚTEIS PARA ADMINISTRAÇÃO
-- ============================================================================

-- View para estatísticas de usuários
CREATE VIEW vw_usuarios_stats AS
SELECT 
    COUNT(*) as total_usuarios,
    COUNT(*) FILTER (WHERE ativo = TRUE) as usuarios_ativos,
    COUNT(*) FILTER (WHERE email_verificado = TRUE) as emails_verificados,
    COUNT(*) FILTER (WHERE whatsapp_confirmado = TRUE) as whatsapp_confirmados,
    COUNT(*) FILTER (WHERE ultimo_login > NOW() - INTERVAL '30 days') as ativos_ultimo_mes,
    COUNT(*) FILTER (WHERE criado_em > NOW() - INTERVAL '7 days') as novos_ultima_semana
FROM usuarios;

-- View para tentativas de login suspeitas
CREATE VIEW vw_tentativas_suspeitas AS
SELECT 
    ip_address,
    COUNT(*) as total_tentativas,
    COUNT(*) FILTER (WHERE sucesso = FALSE) as tentativas_falhadas,
    MAX(tentativa_em) as ultima_tentativa,
    array_agg(DISTINCT email) as emails_tentados
FROM user_login_attempts 
WHERE tentativa_em > NOW() - INTERVAL '24 hours'
GROUP BY ip_address
HAVING COUNT(*) FILTER (WHERE sucesso = FALSE) >= 5
ORDER BY tentativas_falhadas DESC;

-- ============================================================================
-- FINAL DO SCRIPT
-- ============================================================================

-- Verificar se as tabelas foram criadas corretamente
DO $$
BEGIN
    RAISE NOTICE 'Script executado com sucesso!';
    RAISE NOTICE 'Tabelas criadas: usuarios, password_resets, user_sessions, user_login_attempts';
    RAISE NOTICE 'Usuário admin criado: admin@teste.com (senha: Admin123!)';
    RAISE NOTICE 'Execute SELECT * FROM vw_usuarios_stats; para ver estatísticas';
END $$;
