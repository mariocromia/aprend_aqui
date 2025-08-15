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

## Troubleshooting

### Erro de autenticação
- Verificar se a senha está correta
- Confirmar se o email existe no painel da Hostinger

### Erro de conexão
- Verificar firewall na porta 587
- Testar conectividade: `telnet smtp.hostinger.com 587`

### Emails não chegam
- Verificar pasta de spam
- Verificar logs do servidor
- Confirmar registros MX do domínio