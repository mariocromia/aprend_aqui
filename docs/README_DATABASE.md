# 📊 Documentação do Banco de Dados - Sistema de Autenticação

Esta documentação descreve a estrutura do banco de dados para o sistema de autenticação do Prompt Builder IA.

## 📁 Arquivos SQL

### `auth_tables.sql`
- **Descrição:** Script completo para PostgreSQL genérico
- **Uso:** Para instalações PostgreSQL tradicionais
- **Características:** 
  - Inclui todas as funcionalidades avançadas
  - Views, triggers e funções completas
  - Sistema de auditoria robusto

### `supabase_setup.sql`
- **Descrição:** Script otimizado para Supabase
- **Uso:** Para projetos usando Supabase como backend
- **Características:**
  - Integração com `auth.users` do Supabase
  - Políticas RLS (Row Level Security)
  - Funções específicas para API Supabase

## 🏗️ Estrutura das Tabelas

### 1. **usuarios**
Tabela principal de usuários do sistema.

```sql
-- Campos principais
id UUID PRIMARY KEY                    -- Identificador único
nome VARCHAR(100) NOT NULL             -- Nome do usuário
email VARCHAR(255) NOT NULL UNIQUE     -- Email (usado para login)
senha_hash VARCHAR(255) NOT NULL       -- Hash da senha (bcrypt)
whatsapp VARCHAR(20)                   -- Número WhatsApp (opcional)
whatsapp_confirmado BOOLEAN            -- Status confirmação WhatsApp
ativo BOOLEAN DEFAULT TRUE             -- Status ativo/inativo
email_verificado BOOLEAN               -- Email foi verificado
codigo_ativacao VARCHAR(10)            -- Código de ativação 6 dígitos
tentativas_login_falhadas INTEGER      -- Contador tentativas falhadas
conta_bloqueada_ate TIMESTAMP          -- Bloqueio temporário
ultimo_login TIMESTAMP                 -- Data do último login
criado_em TIMESTAMP                    -- Data de criação
atualizado_em TIMESTAMP                -- Data da última atualização
```

### 2. **password_resets**
Gerenciamento de tokens para recuperação de senha.

```sql
-- Campos principais
id UUID PRIMARY KEY                    -- Identificador único
user_id UUID REFERENCES usuarios(id)   -- Usuário relacionado
email VARCHAR(255) NOT NULL            -- Email do token
token VARCHAR(255) NOT NULL UNIQUE     -- Token de recuperação (64 chars)
expires_at TIMESTAMP NOT NULL          -- Data de expiração (1 hora)
usado BOOLEAN DEFAULT FALSE            -- Token foi usado
usado_em TIMESTAMP                     -- Quando foi usado
ip_address INET                        -- IP que solicitou
user_agent TEXT                        -- Navegador/dispositivo
created_at TIMESTAMP                   -- Data de criação
```

### 3. **user_sessions** (apenas PostgreSQL genérico)
Gerenciamento de sessões ativas.

```sql
-- Campos principais
id UUID PRIMARY KEY                    -- Identificador único
user_id UUID REFERENCES usuarios(id)   -- Usuário da sessão
session_id VARCHAR(255) NOT NULL       -- ID da sessão PHP
session_data TEXT                      -- Dados serializados da sessão
ip_address INET                        -- IP da sessão
user_agent TEXT                        -- Navegador/dispositivo
device_info JSONB                      -- Informações do dispositivo
created_at TIMESTAMP                   -- Data de criação
last_activity TIMESTAMP               -- Última atividade
expires_at TIMESTAMP                   -- Data de expiração
ativo BOOLEAN                          -- Sessão ativa
logout_em TIMESTAMP                    -- Data do logout
```

### 4. **user_login_attempts**
Log de tentativas de login para auditoria e segurança.

```sql
-- Campos principais
id UUID PRIMARY KEY                    -- Identificador único
user_id UUID REFERENCES usuarios(id)   -- Usuário (pode ser NULL)
email VARCHAR(255)                     -- Email usado na tentativa
sucesso BOOLEAN NOT NULL               -- Login foi bem-sucedido
motivo_falha VARCHAR(100)              -- Razão da falha
ip_address INET NOT NULL               -- IP da tentativa
user_agent TEXT                        -- Navegador/dispositivo
device_info JSONB                      -- Info do dispositivo (opcional)
pais VARCHAR(2)                        -- País (opcional)
cidade VARCHAR(100)                    -- Cidade (opcional)
tentativa_em TIMESTAMP                 -- Data/hora da tentativa
```

## 🔒 Segurança Implementada

### **Row Level Security (RLS)**
- Usuários só acessam seus próprios dados
- Tokens de recuperação protegidos por usuário
- Logs de tentativas com inserção liberada

### **Índices para Performance**
- Email dos usuários (busca rápida)
- Tokens de recuperação
- IPs suspeitos
- Datas para limpeza automática

### **Constraints e Validações**
- Email válido (regex)
- Nome mínimo 2 caracteres
- WhatsApp mínimo 10 dígitos
- Tokens mínimo 32 caracteres
- Datas de expiração lógicas

## 🔧 Funções Úteis

### **Limpeza Automática**
```sql
-- Limpar tokens expirados
SELECT cleanup_expired_tokens();

-- Limpar sessões expiradas
SELECT cleanup_expired_sessions();

-- Limpeza geral (Supabase)
SELECT public.cleanup_auth_data();
```

### **Verificação de Segurança**
```sql
-- Verificar tentativas suspeitas
SELECT check_suspicious_login_attempts('email@teste.com', '192.168.1.1');

-- Ver estatísticas
SELECT * FROM vw_usuarios_stats;
SELECT * FROM public.usuarios_stats; -- Supabase
```

### **Recuperação de Senha (Supabase)**
```sql
-- Gerar token
SELECT * FROM public.generate_password_reset_token('email@teste.com');

-- Verificar token
SELECT * FROM public.verify_password_reset_token('token_aqui');

-- Log de tentativa
SELECT public.log_login_attempt('email@teste.com', true, null, '192.168.1.1');
```

## 📊 Views para Monitoramento

### **vw_usuarios_stats / usuarios_stats**
```sql
total_usuarios          -- Total de usuários cadastrados
usuarios_ativos         -- Usuários com status ativo
emails_verificados      -- Emails confirmados
whatsapp_confirmados    -- WhatsApp confirmados
ativos_ultimo_mes      -- Login nos últimos 30 dias
novos_ultima_semana    -- Cadastros nos últimos 7 dias
```

### **vw_tentativas_suspeitas**
```sql
ip_address             -- IP com tentativas excessivas
total_tentativas       -- Total de tentativas
tentativas_falhadas    -- Tentativas que falharam
ultima_tentativa       -- Data da última tentativa
emails_tentados        -- Lista de emails tentados
```

## 🚀 Como Usar

### **1. Para PostgreSQL Genérico:**
```sql
-- Execute o arquivo completo
\i docs/auth_tables.sql

-- Ou copie e cole no seu cliente SQL
```

### **2. Para Supabase:**
```sql
-- No Supabase SQL Editor, execute:
-- 1. Copie o conteúdo de supabase_setup.sql
-- 2. Cole no SQL Editor
-- 3. Execute seção por seção ou completo
```

### **3. Criar Usuário Inicial:**

**PostgreSQL:**
```sql
-- Usuário já criado automaticamente
-- Email: admin@teste.com
-- Senha: Admin123!
```

**Supabase:**
```sql
-- 1. Crie via Dashboard (Authentication > Users)
-- 2. Use: admin@teste.com / Admin123!
-- 3. Execute o INSERT do perfil comentado no script
```

## 🔍 Monitoramento e Manutenção

### **Limpeza Recomendada (Diária)**
```sql
-- PostgreSQL
SELECT cleanup_expired_tokens();
SELECT cleanup_expired_sessions();

-- Supabase
SELECT public.cleanup_auth_data();
```

### **Verificação de Segurança (Semanal)**
```sql
-- Ver tentativas suspeitas
SELECT * FROM vw_tentativas_suspeitas;

-- Contas bloqueadas
SELECT nome, email, conta_bloqueada_ate 
FROM usuarios 
WHERE conta_bloqueada_ate > NOW();

-- Tokens pendentes
SELECT COUNT(*) as tokens_ativos 
FROM password_resets 
WHERE expires_at > NOW() AND usado = FALSE;
```

### **Estatísticas (Mensal)**
```sql
-- Crescimento de usuários
SELECT * FROM vw_usuarios_stats;

-- Tentativas de login por mês
SELECT 
    DATE_TRUNC('month', tentativa_em) as mes,
    COUNT(*) as total_tentativas,
    COUNT(*) FILTER (WHERE sucesso = TRUE) as sucessos,
    COUNT(*) FILTER (WHERE sucesso = FALSE) as falhas
FROM user_login_attempts 
WHERE tentativa_em > NOW() - INTERVAL '12 months'
GROUP BY mes 
ORDER BY mes;
```

## ⚠️ Considerações Importantes

### **Backup e Recuperação**
- Faça backup regular das tabelas `usuarios` e `user_login_attempts`
- Tokens de recuperação podem ser recriados
- Sessões são temporárias

### **GDPR e Privacidade**
- Logs de IP são mantidos por 30 dias
- User agent não contém dados pessoais
- Usuário pode solicitar exclusão completa

### **Performance**
- Índices otimizados para consultas frequentes
- Limpeza automática evita crescimento excessivo
- Views pré-calculadas para dashboards

### **Escalabilidade**
- UUIDs evitam conflitos em sistemas distribuídos
- Tabelas particionadas podem ser implementadas para `user_login_attempts`
- Índices podem ser ajustados conforme crescimento

---

## 📞 Suporte

Para dúvidas sobre implementação:
1. Verifique a documentação das funções no próprio SQL
2. Execute os scripts em ambiente de teste primeiro
3. Monitore logs durante a implementação inicial

**Sistema preparado para produção! 🚀**
