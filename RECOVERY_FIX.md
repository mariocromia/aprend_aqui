# Correção da Recuperação de Senha

## Problema Identificado
O sistema de recuperação de senha estava apresentando "Erro interno" porque tentava usar funções RPC do Supabase que não existiam.

## Solução Implementada

### 1. Novo Sistema de Tokens
- ✅ Geração de tokens seguros localmente com `random_bytes(32)`
- ✅ Armazenamento em tabela dedicada `password_reset_tokens`
- ✅ Validação de expiração (1 hora)
- ✅ Controle de uso único

### 2. Tabela Criada
```sql
-- Execute no Supabase SQL Editor
CREATE TABLE IF NOT EXISTS public.password_reset_tokens (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    used BOOLEAN DEFAULT FALSE
);
```

### 3. Métodos Corrigidos

#### EmailManager
- ✅ `generateResetToken()` - Gera token e salva no banco
- ✅ `verifyResetToken()` - Verifica validade e expiração
- ✅ `resetPassword()` - Atualiza senha e marca token como usado

#### SupabaseClient
- ✅ `createPasswordResetToken()` - Salva token no banco
- ✅ `verifyPasswordResetToken()` - Valida token
- ✅ `updateUserPassword()` - Atualiza senha do usuário
- ✅ `markTokenAsUsed()` - Marca token como usado

## Como Aplicar a Correção

### Passo 1: Criar a Tabela
1. Acesse o Supabase Dashboard
2. Vá em SQL Editor
3. Execute o conteúdo de `docs/create_password_reset_table.sql`

### Passo 2: Testar o Sistema
1. Acesse `/test_recovery.php` para testar o fluxo completo
2. Verifique se não há erros nos logs

### Passo 3: Verificar Funcionamento
1. Acesse a página de login
2. Clique em "Esqueci minha senha"
3. Digite um email cadastrado
4. Verifique se recebe o email de recuperação

## Fluxo Completo

### 1. Solicitação de Recuperação
```
login.php → EmailManager::generateResetToken() → Salva token no banco → Envia email
```

### 2. Clique no Link
```
redefinir-senha.php → EmailManager::verifyResetToken() → Verifica se token é válido
```

### 3. Nova Senha
```
redefinir-senha.php → EmailManager::resetPassword() → Atualiza senha → Marca token usado
```

## Segurança Implementada

- ✅ **Tokens únicos** gerados com `random_bytes(32)`
- ✅ **Expiração** de 1 hora
- ✅ **Uso único** - token marcado como usado após reset
- ✅ **Limpeza automática** de tokens antigos
- ✅ **Hash seguro** das senhas com `password_hash()`

## Logs de Debug

O sistema agora registra detalhes completos:
- Geração de tokens
- Tentativas de verificação
- Atualizações de senha
- Erros específicos

## Teste Manual

Para testar manualmente:
1. Faça um cadastro com email real
2. Vá para login e clique "Esqueci minha senha"
3. Digite o email cadastrado
4. Verifique o email recebido
5. Clique no link e defina nova senha

## ⚠️ PROBLEMA IDENTIFICADO

O erro "Erro ao atualizar senha" acontece porque o usuário `teste@exemplo.com` **NÃO EXISTE** na tabela `usuarios`.

## 🔧 SOLUÇÃO RÁPIDA

### Opção 1: Criar usuário de teste
1. Acesse: `/create_test_user.php`
2. Execute para criar o usuário automaticamente
3. Execute novamente `/test_recovery.php`

### Opção 2: Usar usuário real
1. Faça um cadastro normal na aplicação
2. Use esse email real no teste de recuperação

### Opção 3: Teste completo automatizado
1. Acesse: `/fix_recovery_final.php`
2. Ele criará o usuário e testará tudo automaticamente

## 📝 VERIFICAÇÕES NECESSÁRIAS

### 1. Tabela usuarios existe?
Se der erro de "relation does not exist", execute no Supabase:

```sql
-- Criar tabela usuarios (se não existir)
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

-- Habilitar RLS
ALTER TABLE public.usuarios ENABLE ROW LEVEL SECURITY;

-- Política para service key
CREATE POLICY "Permitir acesso via service key" ON public.usuarios
FOR ALL USING (auth.role() = 'service_role');
```

### 2. Tabela password_reset_tokens existe?
Execute o SQL em `docs/create_password_reset_table.sql`

## ✅ APÓS RESOLVER

O sistema de recuperação funcionará **100%**:
- ✅ Geração de token
- ✅ Verificação de token  
- ✅ Envio de email
- ✅ Atualização de senha

**Agora o sistema de recuperação de senha deve funcionar corretamente!**