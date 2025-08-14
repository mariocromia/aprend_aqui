-- ============================================================================
-- CONSULTAS ÚTEIS - SISTEMA DE AUTENTICAÇÃO
-- Exemplos práticos para administração e monitoramento
-- ============================================================================

-- ============================================================================
-- 1. CONSULTAS DE USUÁRIOS
-- ============================================================================

-- Listar todos os usuários ativos
SELECT 
    id,
    nome,
    email,
    whatsapp,
    email_verificado,
    whatsapp_confirmado,
    ultimo_login,
    criado_em
FROM usuarios 
WHERE ativo = TRUE
ORDER BY criado_em DESC;

-- Usuários cadastrados nos últimos 7 dias
SELECT 
    nome,
    email,
    criado_em,
    CASE 
        WHEN ultimo_login IS NOT NULL THEN 'Já fez login'
        ELSE 'Nunca fez login'
    END as status_login
FROM usuarios 
WHERE criado_em > NOW() - INTERVAL '7 days'
ORDER BY criado_em DESC;

-- Usuários inativos há mais de 30 dias
SELECT 
    nome,
    email,
    ultimo_login,
    criado_em,
    NOW() - ultimo_login as tempo_inativo
FROM usuarios 
WHERE ultimo_login < NOW() - INTERVAL '30 days'
   OR ultimo_login IS NULL
ORDER BY ultimo_login ASC NULLS FIRST;

-- Contas bloqueadas
SELECT 
    nome,
    email,
    tentativas_login_falhadas,
    conta_bloqueada_ate,
    conta_bloqueada_ate - NOW() as tempo_restante
FROM usuarios 
WHERE conta_bloqueada_ate > NOW()
ORDER BY conta_bloqueada_ate DESC;

-- ============================================================================
-- 2. ANÁLISE DE SEGURANÇA
-- ============================================================================

-- Top 10 IPs com mais tentativas de login falhadas (últimas 24h)
SELECT 
    ip_address,
    COUNT(*) as total_tentativas,
    COUNT(*) FILTER (WHERE sucesso = FALSE) as tentativas_falhadas,
    COUNT(DISTINCT email) as emails_diferentes,
    MAX(tentativa_em) as ultima_tentativa
FROM user_login_attempts 
WHERE tentativa_em > NOW() - INTERVAL '24 hours'
GROUP BY ip_address
HAVING COUNT(*) FILTER (WHERE sucesso = FALSE) >= 3
ORDER BY tentativas_falhadas DESC
LIMIT 10;

-- Tentativas de login por hora (últimas 24h)
SELECT 
    DATE_TRUNC('hour', tentativa_em) as hora,
    COUNT(*) as total_tentativas,
    COUNT(*) FILTER (WHERE sucesso = TRUE) as sucessos,
    COUNT(*) FILTER (WHERE sucesso = FALSE) as falhas,
    ROUND(
        COUNT(*) FILTER (WHERE sucesso = TRUE) * 100.0 / COUNT(*), 
        2
    ) as taxa_sucesso_pct
FROM user_login_attempts 
WHERE tentativa_em > NOW() - INTERVAL '24 hours'
GROUP BY hora
ORDER BY hora DESC;

-- Emails mais atacados (tentativas falhadas)
SELECT 
    email,
    COUNT(*) as tentativas_falhadas,
    COUNT(DISTINCT ip_address) as ips_diferentes,
    MIN(tentativa_em) as primeira_tentativa,
    MAX(tentativa_em) as ultima_tentativa
FROM user_login_attempts 
WHERE sucesso = FALSE
  AND tentativa_em > NOW() - INTERVAL '7 days'
GROUP BY email
HAVING COUNT(*) >= 5
ORDER BY tentativas_falhadas DESC;

-- ============================================================================
-- 3. RECUPERAÇÃO DE SENHAS
-- ============================================================================

-- Tokens de recuperação ativos
SELECT 
    pr.email,
    pr.token,
    pr.created_at,
    pr.expires_at,
    pr.expires_at - NOW() as tempo_restante,
    CASE 
        WHEN pr.usado THEN 'Usado'
        WHEN pr.expires_at < NOW() THEN 'Expirado'
        ELSE 'Ativo'
    END as status
FROM password_resets pr
ORDER BY pr.created_at DESC;

-- Estatísticas de recuperação de senha (último mês)
SELECT 
    DATE_TRUNC('day', created_at) as dia,
    COUNT(*) as tokens_gerados,
    COUNT(*) FILTER (WHERE usado = TRUE) as tokens_usados,
    ROUND(
        COUNT(*) FILTER (WHERE usado = TRUE) * 100.0 / COUNT(*), 
        2
    ) as taxa_uso_pct
FROM password_resets 
WHERE created_at > NOW() - INTERVAL '30 days'
GROUP BY dia
ORDER BY dia DESC;

-- Usuários que mais solicitam recuperação
SELECT 
    u.nome,
    u.email,
    COUNT(pr.id) as total_solicitacoes,
    MAX(pr.created_at) as ultima_solicitacao
FROM usuarios u
JOIN password_resets pr ON u.id = pr.user_id
WHERE pr.created_at > NOW() - INTERVAL '30 days'
GROUP BY u.id, u.nome, u.email
HAVING COUNT(pr.id) >= 3
ORDER BY total_solicitacoes DESC;

-- ============================================================================
-- 4. SESSÕES (apenas PostgreSQL genérico)
-- ============================================================================

-- Sessões ativas por usuário
SELECT 
    u.nome,
    u.email,
    COUNT(us.id) as sessoes_ativas,
    MAX(us.last_activity) as ultima_atividade
FROM usuarios u
JOIN user_sessions us ON u.id = us.user_id
WHERE us.ativo = TRUE 
  AND us.expires_at > NOW()
GROUP BY u.id, u.nome, u.email
ORDER BY ultima_atividade DESC;

-- Dispositivos mais usados
SELECT 
    device_info->>'platform' as plataforma,
    device_info->>'browser' as navegador,
    COUNT(*) as total_sessoes,
    COUNT(DISTINCT user_id) as usuarios_unicos
FROM user_sessions 
WHERE created_at > NOW() - INTERVAL '30 days'
  AND device_info IS NOT NULL
GROUP BY plataforma, navegador
ORDER BY total_sessoes DESC;

-- ============================================================================
-- 5. RELATÓRIOS DE CRESCIMENTO
-- ============================================================================

-- Crescimento de usuários por mês
SELECT 
    DATE_TRUNC('month', criado_em) as mes,
    COUNT(*) as novos_usuarios,
    SUM(COUNT(*)) OVER (ORDER BY DATE_TRUNC('month', criado_em)) as total_acumulado
FROM usuarios
GROUP BY mes
ORDER BY mes DESC;

-- Taxa de ativação de email e WhatsApp
SELECT 
    DATE_TRUNC('week', criado_em) as semana,
    COUNT(*) as total_cadastros,
    COUNT(*) FILTER (WHERE email_verificado = TRUE) as emails_verificados,
    COUNT(*) FILTER (WHERE whatsapp_confirmado = TRUE) as whatsapp_confirmados,
    ROUND(
        COUNT(*) FILTER (WHERE email_verificado = TRUE) * 100.0 / COUNT(*), 
        2
    ) as taxa_email_pct,
    ROUND(
        COUNT(*) FILTER (WHERE whatsapp_confirmado = TRUE) * 100.0 / COUNT(*), 
        2
    ) as taxa_whatsapp_pct
FROM usuarios
WHERE criado_em > NOW() - INTERVAL '12 weeks'
GROUP BY semana
ORDER BY semana DESC;

-- ============================================================================
-- 6. CONSULTAS DE MANUTENÇÃO
-- ============================================================================

-- Limpar dados antigos (execute com cuidado!)
-- 
-- DELETE FROM user_login_attempts 
-- WHERE tentativa_em < NOW() - INTERVAL '90 days';
-- 
-- DELETE FROM password_resets 
-- WHERE created_at < NOW() - INTERVAL '7 days';

-- Verificar integridade dos dados
SELECT 
    'usuarios' as tabela,
    COUNT(*) as total_registros,
    COUNT(*) FILTER (WHERE nome IS NULL OR nome = '') as nomes_vazios,
    COUNT(*) FILTER (WHERE email IS NULL OR email = '') as emails_vazios,
    COUNT(*) FILTER (WHERE senha_hash IS NULL OR senha_hash = '') as senhas_vazias
FROM usuarios

UNION ALL

SELECT 
    'password_resets' as tabela,
    COUNT(*) as total_registros,
    COUNT(*) FILTER (WHERE user_id IS NULL) as user_id_nulos,
    COUNT(*) FILTER (WHERE token IS NULL OR token = '') as tokens_vazios,
    COUNT(*) FILTER (WHERE expires_at < created_at) as datas_invalidas
FROM password_resets

UNION ALL

SELECT 
    'user_login_attempts' as tabela,
    COUNT(*) as total_registros,
    COUNT(*) FILTER (WHERE email IS NULL OR email = '') as emails_vazios,
    COUNT(*) FILTER (WHERE ip_address IS NULL) as ips_nulos,
    COUNT(*) FILTER (WHERE tentativa_em IS NULL) as datas_nulas
FROM user_login_attempts;

-- ============================================================================
-- 7. DASHBOARD EXECUTIVO
-- ============================================================================

-- Resumo executivo (execute como uma consulta)
WITH stats AS (
    SELECT 
        COUNT(*) as total_usuarios,
        COUNT(*) FILTER (WHERE ativo = TRUE) as usuarios_ativos,
        COUNT(*) FILTER (WHERE criado_em > NOW() - INTERVAL '7 days') as novos_7_dias,
        COUNT(*) FILTER (WHERE ultimo_login > NOW() - INTERVAL '30 days') as ativos_30_dias,
        COUNT(*) FILTER (WHERE email_verificado = TRUE) as emails_verificados,
        COUNT(*) FILTER (WHERE whatsapp_confirmado = TRUE) as whatsapp_confirmados
    FROM usuarios
),
security_stats AS (
    SELECT 
        COUNT(*) as tentativas_24h,
        COUNT(*) FILTER (WHERE sucesso = FALSE) as falhas_24h,
        COUNT(DISTINCT ip_address) as ips_unicos_24h
    FROM user_login_attempts 
    WHERE tentativa_em > NOW() - INTERVAL '24 hours'
),
reset_stats AS (
    SELECT 
        COUNT(*) as tokens_ativos,
        COUNT(*) FILTER (WHERE created_at > NOW() - INTERVAL '24 hours') as novos_tokens_24h
    FROM password_resets 
    WHERE expires_at > NOW() AND usado = FALSE
)
SELECT 
    -- Usuários
    s.total_usuarios,
    s.usuarios_ativos,
    s.novos_7_dias,
    s.ativos_30_dias,
    ROUND(s.emails_verificados * 100.0 / s.total_usuarios, 1) as taxa_email_pct,
    ROUND(s.whatsapp_confirmados * 100.0 / s.total_usuarios, 1) as taxa_whatsapp_pct,
    
    -- Segurança
    sec.tentativas_24h,
    sec.falhas_24h,
    sec.ips_unicos_24h,
    CASE 
        WHEN sec.tentativas_24h > 0 THEN 
            ROUND((sec.tentativas_24h - sec.falhas_24h) * 100.0 / sec.tentativas_24h, 1)
        ELSE 0 
    END as taxa_sucesso_24h_pct,
    
    -- Recuperação
    r.tokens_ativos,
    r.novos_tokens_24h
FROM stats s, security_stats sec, reset_stats r;

-- ============================================================================
-- 8. ALERTAS DE SEGURANÇA
-- ============================================================================

-- IPs para bloquear (mais de 20 tentativas falhadas em 1 hora)
SELECT 
    ip_address,
    COUNT(*) as tentativas_falhadas,
    MIN(tentativa_em) as primeira_tentativa,
    MAX(tentativa_em) as ultima_tentativa,
    COUNT(DISTINCT email) as emails_atacados,
    'BLOQUEAR IMEDIATAMENTE' as acao_recomendada
FROM user_login_attempts 
WHERE tentativa_em > NOW() - INTERVAL '1 hour'
  AND sucesso = FALSE
GROUP BY ip_address
HAVING COUNT(*) >= 20
ORDER BY tentativas_falhadas DESC;

-- Contas com atividade suspeita
SELECT 
    u.nome,
    u.email,
    u.tentativas_login_falhadas,
    COUNT(ula.id) as tentativas_ultima_hora,
    u.ultimo_login,
    CASE 
        WHEN u.conta_bloqueada_ate > NOW() THEN 'BLOQUEADA'
        WHEN COUNT(ula.id) >= 10 THEN 'ATIVIDADE SUSPEITA'
        WHEN u.tentativas_login_falhadas >= 3 THEN 'MONITORAR'
        ELSE 'NORMAL'
    END as status_seguranca
FROM usuarios u
LEFT JOIN user_login_attempts ula ON u.email = ula.email 
    AND ula.tentativa_em > NOW() - INTERVAL '1 hour'
    AND ula.sucesso = FALSE
WHERE u.tentativas_login_falhadas > 0 
   OR COUNT(ula.id) > 0
GROUP BY u.id, u.nome, u.email, u.tentativas_login_falhadas, u.ultimo_login, u.conta_bloqueada_ate
HAVING COUNT(ula.id) > 0 OR u.tentativas_login_falhadas >= 3
ORDER BY tentativas_ultima_hora DESC, u.tentativas_login_falhadas DESC;

-- ============================================================================
-- 9. PERFORMANCE E OTIMIZAÇÃO
-- ============================================================================

-- Verificar uso dos índices
SELECT 
    schemaname,
    tablename,
    indexname,
    idx_scan as utilizacoes,
    idx_tup_read as tuplas_lidas,
    idx_tup_fetch as tuplas_retornadas
FROM pg_stat_user_indexes 
WHERE schemaname = 'public'
  AND tablename IN ('usuarios', 'password_resets', 'user_login_attempts', 'user_sessions')
ORDER BY tablename, idx_scan DESC;

-- Tamanho das tabelas
SELECT 
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as tamanho_total,
    pg_size_pretty(pg_relation_size(schemaname||'.'||tablename)) as tamanho_dados,
    pg_total_relation_size(schemaname||'.'||tablename) as bytes_total
FROM pg_tables 
WHERE schemaname = 'public'
  AND tablename IN ('usuarios', 'password_resets', 'user_login_attempts', 'user_sessions')
ORDER BY bytes_total DESC;

-- ============================================================================
-- FINAL DO ARQUIVO
-- ============================================================================

-- Para executar limpeza automatizada diariamente:
-- SELECT cleanup_expired_tokens();           -- PostgreSQL genérico
-- SELECT cleanup_expired_sessions();         -- PostgreSQL genérico  
-- SELECT public.cleanup_auth_data();         -- Supabase

-- Para monitoramento contínuo, execute:
-- - Dashboard executivo (diário)
-- - Alertas de segurança (a cada hora)
-- - Relatórios de crescimento (semanal)
