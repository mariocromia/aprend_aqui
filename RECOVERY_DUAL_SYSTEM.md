# Sistema de Recuperação Dual - Email + WhatsApp

## 🎯 Visão Geral

Sistema completo de recuperação de senha com **3 métodos diferentes**:

1. **📧 Código por Email** - Código de 6 dígitos (10 minutos)
2. **📱 Código por WhatsApp** - Código de 6 dígitos (10 minutos)  
3. **🔗 Link por Email** - Link tradicional (1 hora)

## 🚀 Funcionalidades Implementadas

### Interface de Usuário
- ✅ **Modal modernizado** com seleção de método
- ✅ **Design responsivo** com visual profissional
- ✅ **Página de confirmação** dedicada para códigos
- ✅ **Validação em tempo real** de códigos
- ✅ **Feedback visual** para cada método

### Backend Robusto
- ✅ **Geração segura** de códigos de 6 dígitos
- ✅ **Tokens únicos** para links tradicionais
- ✅ **Expiração automática** de códigos/tokens
- ✅ **Uso único** garantido
- ✅ **Logs detalhados** para debugging

### Integração WhatsApp
- ✅ **Mensagens formatadas** com emojis
- ✅ **Verificação de número** cadastrado
- ✅ **Fallback para email** se WhatsApp falhar
- ✅ **Templates personalizados** por método

## 📋 Arquivos Criados/Modificados

### Novos Arquivos
- `auth/confirmar-recuperacao.php` - Página de confirmação de códigos
- `docs/create_password_reset_codes_table.sql` - Tabela para códigos temporários
- `test_recovery_dual.php` - Teste completo do sistema

### Arquivos Modificados
- `auth/login.php` - Modal com 3 opções de recuperação
- `includes/EmailManager.php` - Métodos para códigos e WhatsApp
- `includes/SupabaseClient.php` - Gerenciamento de códigos temporários

## 🗄️ Estrutura do Banco

### Tabela: password_reset_codes
```sql
- id (BIGSERIAL)
- email (VARCHAR 255) 
- code (VARCHAR 6)
- method (VARCHAR 20) -- 'email' ou 'whatsapp'
- expires_at (TIMESTAMP) -- 10 minutos
- created_at (TIMESTAMP)
- used (BOOLEAN)
```

### Tabela: password_reset_tokens  
```sql
- id (BIGSERIAL)
- email (VARCHAR 255)
- token (VARCHAR 255) 
- expires_at (TIMESTAMP) -- 1 hora
- created_at (TIMESTAMP)
- used (BOOLEAN)
```

## 🔧 Como Configurar

### 1. Criar Tabelas no Supabase
```sql
-- Execute os dois arquivos SQL:
-- docs/create_password_reset_tokens_table.sql
-- docs/create_password_reset_codes_table.sql
```

### 2. Configurar Email SMTP
```env
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=587
SMTP_USERNAME=contato@centroservice.com.br
SMTP_PASSWORD=sua_senha_aqui
```

### 3. Configurar WhatsApp (WAHA)
```env
WHATSAPP_PROVIDER=waha
WAHA_SERVER=https://waha.zapfunil.app
```

### 4. Testar Sistema
```bash
# Acesse para teste completo:
http://seu-dominio/test_recovery_dual.php
```

## 🎨 Fluxo de Usuário

### Método 1: Código por Email
1. Usuário clica "Esqueci minha senha"
2. Digita email e seleciona "Email (código)"
3. Recebe código de 6 dígitos no email
4. É redirecionado para página de confirmação
5. Digita código + nova senha
6. Senha é redefinida

### Método 2: Código por WhatsApp  
1. Usuário clica "Esqueci minha senha"
2. Digita email e seleciona "WhatsApp"
3. Recebe código de 6 dígitos no WhatsApp
4. É redirecionado para página de confirmação
5. Digita código + nova senha
6. Senha é redefinida

### Método 3: Link por Email
1. Usuário clica "Esqueci minha senha"
2. Digita email e seleciona "Link por Email"
3. Recebe email com link tradicional
4. Clica no link e vai para redefinir-senha.php
5. Define nova senha diretamente
6. Senha é redefinida

## 🔒 Segurança Implementada

### Códigos Temporários
- **6 dígitos aleatórios** (100.000 - 999.999)
- **Expiração de 10 minutos**
- **Uso único** garantido
- **Limpeza automática** de códigos antigos

### Tokens Tradicionais
- **64 caracteres hexadecimais** seguros
- **Expiração de 1 hora**
- **Uso único** garantido
- **Geração com random_bytes(32)**

### Validações
- ✅ **CSRF protection** em todos os formulários
- ✅ **Sanitização** de inputs
- ✅ **Validação de email** formato
- ✅ **Verificação de usuário** existente
- ✅ **Logs de tentativas** para auditoria

## 📱 Templates de Mensagem

### Email - Código de Recuperação
```html
<h2>Recuperação de Senha</h2>
<p>Seu código de verificação é:</p>
<div style="font-size: 36px; color: #dc3545;">123456</div>
<p>Válido por 10 minutos</p>
```

### WhatsApp - Código de Recuperação
```
🔒 *Recuperação de Senha - Centro Service*

Olá, *Nome*!

Seu código de recuperação é:

🔢 *123456*

Digite este código na página de recuperação.

⏰ *Válido por 10 minutos*
```

### Email - Link Tradicional
```html
<h2>Recuperação de Senha</h2>
<p>Clique no link abaixo para redefinir:</p>
<a href="link_com_token">Redefinir Senha</a>
<p>Válido por 1 hora</p>
```

## 🧪 Como Testar

### Teste Automatizado
```bash
# Acesse para teste completo:
http://seu-dominio/test_recovery_dual.php

# Testa todos os 3 métodos automaticamente
```

### Teste Manual
1. **Acesse** `/auth/login.php`
2. **Clique** "Esqueci minha senha"
3. **Escolha** um dos 3 métodos
4. **Siga** as instruções recebidas
5. **Verifique** se senha foi alterada

### Verificar Logs
```bash
# Logs detalhados em:
tail -f /var/log/apache2/error.log

# Procure por:
- "EmailManager: Código gerado"
- "WhatsApp enviado"
- "Senha redefinida"
```

## 🚨 Troubleshooting

### Código não é enviado
1. Verificar configurações SMTP/WhatsApp
2. Verificar se usuário existe na tabela `usuarios`
3. Verificar logs de erro

### Código não é aceito
1. Verificar se não expirou (10 min)
2. Verificar se não foi usado
3. Verificar se tabela `password_reset_codes` existe

### WhatsApp não funciona
1. Verificar se usuário tem WhatsApp cadastrado
2. Verificar configurações WAHA
3. Usar fallback por email

## 🎉 Resultado Final

O usuário agora tem **3 opções flexíveis** para recuperar a senha:

- **⚡ Rápido**: Código por WhatsApp (segundos)
- **🔒 Seguro**: Código por email (minutos)  
- **🔗 Tradicional**: Link por email (clássico)

**Sistema robusto, seguro e user-friendly!** 🚀