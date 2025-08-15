# 🚨 Quick Fix - Erro "Could not find the 'senha' column"

## ❌ **PROBLEMA:**
```
Erro ao criar usuário: HTTP 400 - Could not find the 'senha' column of 'usuarios' in the schema cache
```

## ✅ **SOLUÇÃO:**

### 1. **Execute os SQLs no Supabase** (OBRIGATÓRIO)

No Supabase Dashboard → SQL Editor, execute na ordem:

```sql
-- 1. Tabela principal de usuários
-- Copie e cole o conteúdo de: docs/create_usuarios_table.sql
```

```sql  
-- 2. Tabela de tokens tradicionais
-- Copie e cole o conteúdo de: docs/create_password_reset_table.sql
```

```sql
-- 3. Tabela de códigos temporários  
-- Copie e cole o conteúdo de: docs/create_password_reset_codes_table.sql
```

### 2. **Verificar Setup**
Acesse: `/setup_and_test_recovery.php`

Este arquivo irá:
- ✅ Verificar se todas as tabelas existem
- ✅ Verificar se as colunas estão corretas
- ✅ Criar usuário de teste automaticamente
- ✅ Testar todos os métodos de recuperação

### 3. **Testar Sistema**
Após o setup, acesse: `/auth/login.php`
- Clique "Esqueci minha senha"
- Digite: `teste@exemplo.com`
- Escolha um método de recuperação

## 📋 **Checklist de Verificação:**

- [ ] Executei `create_usuarios_table.sql`
- [ ] Executei `create_password_reset_table.sql`  
- [ ] Executei `create_password_reset_codes_table.sql`
- [ ] Acessei `/setup_and_test_recovery.php`
- [ ] Todas as verificações mostraram ✅
- [ ] Testei recuperação manual no login

## 🔧 **Se ainda der erro:**

1. **Acesse:** `/debug_table_structure.php`
2. **Verifique** se as tabelas foram criadas
3. **Confira** se a coluna `senha` existe
4. **Re-execute** os SQLs se necessário

## 📁 **Arquivos Importantes:**

- `docs/create_usuarios_table.sql` - **Tabela principal**
- `setup_and_test_recovery.php` - **Setup automático**
- `debug_table_structure.php` - **Debug de tabelas**

**O erro acontece porque a tabela `usuarios` não existe ou não tem a coluna `senha`. Os SQLs resolvem isso!** 🚀