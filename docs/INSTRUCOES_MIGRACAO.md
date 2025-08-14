# 📋 Instruções para Migração das Tabelas

## 🎯 **Objetivo**
Atualizar as tabelas existentes no Supabase para incluir todas as colunas necessárias para o sistema de autenticação funcionar corretamente.

## ⚠️ **IMPORTANTE: Use o Script Básico**
- **NÃO execute** o arquivo `supabase_setup.sql` 
- **NÃO execute** o arquivo `auth_tables.sql`
- **Execute APENAS** o arquivo `migrate_tables_basic.sql`

## 🚀 **Passos para Executar a Migração**

### 1. **Acesse o Supabase**
- Vá para [supabase.com](https://supabase.com)
- Faça login na sua conta
- Acesse o seu projeto

### 2. **Abra o SQL Editor**
- No menu lateral esquerdo, clique em **"SQL Editor"**
- Clique em **"New query"** para criar uma nova consulta

### 3. **Cole o Script de Migração**
- Abra o arquivo `docs/migrate_tables_basic.sql`
- Copie todo o conteúdo
- Cole no SQL Editor do Supabase

### 4. **Execute a Migração**
- Clique no botão **"Run"** (▶️) ou pressione **Ctrl+Enter**
- Aguarde a execução completar
- Você verá mensagens de progresso no console

### 5. **Verifique o Resultado**
- No final, você deve ver a mensagem:
  ```
  ========================================
  MIGRAÇÃO CONCLUÍDA COM SUCESSO!
  ========================================
  ```

## 🔍 **O que o Script Faz**

### ✅ **Verifica e Adiciona Colunas Faltantes:**
- `senha_hash` - Para armazenar senhas criptografadas
- `whatsapp_confirmado` - Status de confirmação do WhatsApp
- `codigo_ativacao` - Código de ativação da conta
- `codigo_gerado_em` - Quando o código foi gerado
- `ultimo_login` - Último acesso do usuário
- `tentativas_login_falhadas` - Contador de tentativas
- `conta_bloqueada_ate` - Data de bloqueio da conta
- `atualizado_em` - Timestamp de atualização

### ✅ **Cria Tabelas Faltantes:**
- `password_resets` - Recuperação de senha
- `user_sessions` - Sessões ativas
- `user_login_attempts` - Tentativas de login

### ✅ **Cria Funções:**
- `generate_password_reset_token()` - Gera tokens de recuperação
- `verify_password_reset_token()` - Verifica tokens
- `cleanup_auth_data()` - Limpa dados antigos

### ✅ **Configura Segurança:**
- Índices para performance
- Triggers para timestamps automáticos
- RLS (Row Level Security) para proteção de dados

## 🧪 **Após a Migração**

### 1. **Teste o Cadastro**
- Acesse `auth/cadastro.php` no seu sistema
- Tente criar um novo usuário
- Verifique se não há mais erros

### 2. **Verifique os Logs**
- Se houver problemas, verifique o console do navegador
- Verifique os logs do PHP (XAMPP)

### 3. **Teste o Login**
- Tente fazer login com o usuário criado
- Verifique se a autenticação está funcionando

## 🆘 **Se Houver Problemas**

### **Erro de Permissão:**
```sql
-- Execute este comando para dar permissões
GRANT ALL ON ALL TABLES IN SCHEMA public TO authenticated;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO authenticated;
```

### **Erro de Extensão:**
```sql
-- Execute este comando para habilitar extensões
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
```

### **Verificar Estrutura da Tabela:**
```sql
-- Execute para ver a estrutura atual
\d usuarios
```

## 📞 **Suporte**

Se encontrar problemas durante a migração:

1. **Copie a mensagem de erro completa**
2. **Verifique se todas as etapas foram seguidas**
3. **Execute o script novamente** (é seguro executar múltiplas vezes)

## 🎉 **Resultado Esperado**

Após a migração bem-sucedida:
- ✅ Todas as colunas necessárias estarão presentes
- ✅ O sistema de cadastro funcionará corretamente
- ✅ Usuários serão criados no banco de dados
- ✅ O sistema de autenticação estará completo

---

**⚠️ Lembre-se: Execute APENAS o `migrate_tables_basic.sql`!**
