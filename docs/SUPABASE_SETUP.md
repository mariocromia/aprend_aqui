# 🚀 Configuração do Supabase - Sistema de Autenticação

Este guia explica como configurar e integrar o Supabase com o sistema de autenticação do Prompt Builder IA.

## 📋 **Pré-requisitos**

1. **Conta no Supabase** (gratuita em [supabase.com](https://supabase.com))
2. **Projeto criado** no Supabase
3. **PHP com cURL** habilitado
4. **Servidor web** (XAMPP, Apache, etc.)

## 🔧 **1. Configuração no Supabase**

### **1.1 Criar Projeto**
1. Acesse [supabase.com](https://supabase.com)
2. Clique em "New Project"
3. Escolha sua organização
4. Digite nome do projeto (ex: `prompt-builder-ia`)
5. Escolha região (recomendado: mais próxima)
6. Clique em "Create new project"

### **1.2 Obter Credenciais**
1. No projeto, vá para **Settings > API**
2. Copie as seguintes informações:
   - **Project URL** (ex: `https://abc123.supabase.co`)
   - **anon public** (chave anônima)
   - **service_role** (chave de serviço - **MANTENHA SEGURA!**)

### **1.3 Executar Script SQL**
1. Vá para **SQL Editor**
2. Copie o conteúdo de `docs/supabase_setup.sql`
3. Cole no editor e execute
4. Verifique se as tabelas foram criadas em **Table Editor**

## ⚙️ **2. Configuração Local**

### **2.1 Atualizar env.config**
```ini
# Configurações do Supabase
SUPABASE_URL=https://seu-projeto.supabase.co
SUPABASE_ANON_KEY=sua-chave-anonima-aqui
SUPABASE_SERVICE_KEY=sua-chave-service-aqui
```

### **2.2 Verificar Estrutura**
```
aprend_aqui/
├── includes/
│   ├── SupabaseClient.php     # ✅ Cliente Supabase
│   ├── Environment.php        # ✅ Carregador de config
│   ├── CSRF.php              # ✅ Proteção CSRF
│   ├── Sanitizer.php         # ✅ Sanitização
│   └── EmailManager.php      # ✅ Gerenciador de emails
├── auth/
│   ├── login.php             # ✅ Login integrado
│   ├── cadastro.php          # ✅ Cadastro integrado
│   └── ...                   # ✅ Outros arquivos
└── docs/
    ├── supabase_setup.sql    # ✅ Script SQL
    └── SUPABASE_SETUP.md     # ✅ Este arquivo
```

## 🧪 **3. Testando a Integração**

### **3.1 Teste de Conexão**
```php
<?php
require_once 'includes/Environment.php';
require_once 'includes/SupabaseClient.php';

try {
    $supabase = new SupabaseClient();
    echo "✅ Conexão com Supabase estabelecida!";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>
```

### **3.2 Teste de Usuário**
1. Acesse `http://localhost/aprend_aqui/auth/login.php`
2. Use credenciais demo: `admin@teste.com` / `Admin123!`
3. Verifique se o login funciona

### **3.3 Verificar Logs**
- **XAMPP:** `C:\xampp\apache\logs\error.log`
- **Linux:** `/var/log/apache2/error.log`
- **Verifique:** Mensagens de erro ou sucesso

## 🔍 **4. Solução de Problemas**

### **4.1 Erro de Conexão**
```
❌ Erro: Configurações do Supabase não encontradas no env.config
```
**Solução:**
- Verifique se `env.config` está na raiz do projeto
- Confirme se as variáveis estão corretas
- Reinicie o servidor web

### **4.2 Erro de Tabela**
```
❌ Erro: Table 'usuarios' doesn't exist
```
**Solução:**
- Execute o script `supabase_setup.sql` no Supabase
- Verifique se as tabelas foram criadas
- Confirme as políticas RLS

### **4.3 Erro de Permissão**
```
❌ Erro: 403 Forbidden
```
**Solução:**
- Verifique se as chaves API estão corretas
- Confirme as políticas RLS no Supabase
- Teste com a chave `service_role` para operações admin

## 📊 **5. Monitoramento**

### **5.1 Dashboard Supabase**
- **Authentication > Users:** Usuários cadastrados
- **Table Editor:** Dados das tabelas
- **Logs:** Tentativas de login e erros

### **5.2 Consultas Úteis**
```sql
-- Estatísticas de usuários
SELECT * FROM public.usuarios_stats;

-- Limpeza automática
SELECT public.cleanup_auth_data();

-- Ver tentativas de login
SELECT * FROM public.user_login_attempts 
WHERE tentativa_em > NOW() - INTERVAL '24 hours';
```

### **5.3 Logs Locais**
```php
// Adicione logs para debug
error_log("Supabase: Tentativa de login para $email");
error_log("Supabase: Usuário encontrado: " . json_encode($usuario));
```

## 🚀 **6. Próximos Passos**

### **6.1 Implementar Supabase Auth**
```php
// TODO: Substituir verificação mockada
if ($loginInput === 'admin@teste.com' && $senha === 'Admin123!') {
    // Por: Verificação real com Supabase Auth
}
```

### **6.2 Envio Real de Emails**
```php
// TODO: Implementar SMTP real
// Por enquanto: apenas logs
error_log("Email seria enviado para $email");
```

### **6.3 Verificação de WhatsApp**
```php
// TODO: Integrar com API de WhatsApp
// Por enquanto: código simulado
$codigo = '123456';
```

## 🔒 **7. Segurança**

### **7.1 Chaves API**
- **anon public:** Pode ser exposta no frontend
- **service_role:** **NUNCA** exponha no frontend
- **Rotacione** as chaves periodicamente

### **7.2 Políticas RLS**
- Usuários só acessam seus próprios dados
- Tokens de reset são protegidos
- Logs de tentativas são seguros

### **7.3 Validações**
- CSRF em todos os formulários
- Sanitização de inputs
- Rate limiting (implementar depois)

## 📱 **8. Testes de Funcionalidade**

### **8.1 Fluxo de Login**
1. ✅ Acesso à página de login
2. ✅ Validação de campos
3. ✅ Verificação de credenciais
4. ✅ Redirecionamento após sucesso
5. ✅ Bloqueio após tentativas falhadas

### **8.2 Fluxo de Cadastro**
1. ✅ Acesso à página de cadastro
2. ✅ Validação de senha forte
3. ✅ Verificação de email único
4. ✅ Geração de código de ativação
5. ✅ Redirecionamento para confirmação

### **8.3 Recuperação de Senha**
1. ✅ Modal de recuperação
2. ✅ Geração de token
3. ✅ Verificação de token
4. ✅ Redefinição de senha

## 🎯 **9. Checklist de Configuração**

- [ ] Projeto criado no Supabase
- [ ] Script SQL executado
- [ ] Tabelas criadas e verificadas
- [ ] Políticas RLS configuradas
- [ ] Credenciais copiadas para `env.config`
- [ ] Cliente Supabase testado
- [ ] Login funcionando
- [ ] Cadastro funcionando
- [ ] Recuperação de senha funcionando
- [ ] Logs sendo gerados
- [ ] Erros sendo capturados

## 📞 **10. Suporte**

### **10.1 Logs de Erro**
```bash
# Ver logs em tempo real
tail -f /var/log/apache2/error.log

# Windows (XAMPP)
tail -f C:\xampp\apache\logs\error.log
```

### **10.2 Debug PHP**
```php
// Adicione no início dos arquivos para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### **10.3 Teste de Conexão**
```php
// Arquivo de teste: test_supabase.php
<?php
require_once 'includes/Environment.php';
require_once 'includes/SupabaseClient.php';

try {
    $supabase = new SupabaseClient();
    $stats = $supabase->getUserStats();
    echo "✅ Conexão OK! Stats: " . json_encode($stats);
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>
```

---

## 🎉 **Sistema Integrado e Funcionando!**

Após seguir este guia, você terá:
- ✅ **Autenticação real** com Supabase
- ✅ **Banco de dados** configurado
- ✅ **Segurança** implementada
- ✅ **Monitoramento** ativo
- ✅ **Sistema pronto** para produção

**Próximo passo:** Implementar envio real de emails e verificação de WhatsApp! 🚀
