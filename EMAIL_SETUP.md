# Configuração de Email - Centro Service

## Configurações Implementadas

### SMTP Settings
- **Host:** smtp.hostinger.com
- **Porta:** 587
- **Segurança:** STARTTLS
- **Email:** contato@centroservice.com.br
- **Nome:** Centro Service - Aprenda Aqui

### Registros MX Configurados
- mx1.hostinger.com (Prioridade: 5)
- mx2.hostinger.com (Prioridade: 10)

## Para Ativar o Envio de Emails

### 1. Definir a Senha SMTP
No arquivo `env.config`, altere a linha:
```
SMTP_PASSWORD=
```
Para:
```
SMTP_PASSWORD=sua_senha_real_aqui
```

### 2. Ou Definir como Variável de Ambiente
```bash
export SMTP_PASSWORD="sua_senha_real"
```

### 3. Teste de Funcionamento
Acesse: `http://seu-dominio/test_email.php`

## Emails Implementados

### 1. Email de Boas-vindas
- **Método:** `EmailManager::sendWelcomeEmail($email, $nome)`
- **Usado em:** Cadastro de novos usuários
- **Template:** HTML responsivo com link para a plataforma

### 2. Código de Ativação WhatsApp
- **Método:** `EmailManager::sendActivationCode($email, $codigo, $nome)`
- **Usado em:** Confirmação de WhatsApp
- **Template:** Código destacado visualmente

### 3. Recuperação de Senha
- **Método:** `EmailManager::sendPasswordReset($email, $token)`
- **Usado em:** Reset de senha
- **Template:** Link seguro com token

## Segurança

- ✅ Senha não armazenada em código
- ✅ Conexão STARTTLS criptografada
- ✅ Templates HTML seguros
- ✅ Logs de envio para debugging

## Dependências

- **PHPMailer 6.9+** (instalado via Composer)
- **PHP 7.4+**
- **Extensão OpenSSL**

## Arquivos de Teste

### 1. Teste Básico
Acesse: `http://seu-dominio/test_email.php`

### 2. Debug Detalhado  
Acesse: `http://seu-dominio/debug_email.php`

### 3. Teste SMTP Direto
Acesse: `http://seu-dominio/test_smtp_direto.php`

## Melhorias Implementadas

### Fallback Automático
O sistema agora tenta automaticamente:
1. **Porta 587 com STARTTLS** (padrão)
2. **Porta 465 com SSL** (fallback)

### Logs Detalhados
- Logs de debug em modo desenvolvimento
- Rastreamento de tentativas de envio
- Informações de configuração

### Integração Completa
- Email de boas-vindas no cadastro
- Código de ativação por email (backup do WhatsApp)
- Recuperação de senha funcional

## Troubleshooting

### 1. Emails não são enviados
```bash
# Verificar configurações
php debug_email.php

# Verificar logs
tail -f /var/log/apache2/error.log
```

### 2. Erro de autenticação
- ✅ Verificar se a senha está correta no env.config
- ✅ Confirmar se o email existe no painel da Hostinger
- ✅ Verificar se não há autenticação de dois fatores

### 3. Erro de conexão
- ✅ Verificar firewall nas portas 587 e 465
- ✅ Testar conectividade: `telnet smtp.hostinger.com 587`
- ✅ O sistema tenta automaticamente porta alternativa

### 4. Emails não chegam ao destinatário
- ✅ Verificar pasta de spam
- ✅ Verificar logs do servidor
- ✅ Confirmar registros MX do domínio
- ✅ Testar com email real (não teste@exemplo.com)

### 5. Verificar status do envio
```php
// Verificar se foi enviado
if (EmailManager::sendWelcomeEmail($email, $nome)) {
    echo "Email enviado com sucesso";
} else {
    echo "Falha no envio - verificar logs";
}
```