<?php
session_start();

require_once '../includes/Environment.php';
require_once '../includes/CSRF.php';
require_once '../includes/Sanitizer.php';
require_once '../includes/EmailManager.php';
require_once '../includes/SupabaseClient.php';
require_once '../includes/WhatsAppManager.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!CSRF::verifyPostToken()) {
        $mensagem = 'Erro de segurança detectado. Tente novamente.';
    } else {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $senha = $_POST['senha'];
        $confirmar_senha = $_POST['confirmar_senha'];
        $whatsapp = Sanitizer::sanitizePhone($_POST['whatsapp'] ?? '');
        
        // Se o WhatsApp foi fornecido mas não é válido, definir como string vazia
        if ($_POST['whatsapp'] && !$whatsapp) {
            $whatsapp = '';
        }

        // Validação do nome
        if (strlen($nome) < 2) {
            $mensagem = 'Nome deve ter pelo menos 2 caracteres.';
        } 
        // Validação do email
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagem = 'E-mail inválido. Verifique o formato.';
        }
        // Validação do WhatsApp (OBRIGATÓRIO)
        elseif (!$_POST['whatsapp'] || !$whatsapp) {
            $mensagem = 'WhatsApp é obrigatório e deve ter um formato válido com DDD.';
        }
        // Validação básica da senha
        elseif (strlen($senha) < 6) {
            $mensagem = 'Senha deve ter pelo menos 6 caracteres.';
        }
        // Verifica se as senhas coincidem
        elseif ($senha !== $confirmar_senha) {
            $mensagem = 'As senhas não coincidem.';
        } else {
            try {
                $supabase = new SupabaseClient();
                
                // Verificar se o email já existe
                if ($supabase->emailExists($email)) {
                    $mensagem = 'Este e-mail já está cadastrado. <a href="login.php">Faça login</a>';
                } else {
                    try {
                        // Gerar código de ativação
                        $codigoAtivacao = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                        
                        // Hash da senha
                        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                        
                        // Dados do usuário para inserir na tabela usuarios
                        $userData = [
                            'nome' => $nome,
                            'email' => $email,
                            'senha_hash' => $senhaHash,
                            'whatsapp' => $whatsapp,
                            'whatsapp_confirmado' => false,
                            'codigo_ativacao' => $codigoAtivacao,
                            'codigo_gerado_em' => date('c'),
                            'ativo' => true,
                            'email_verificado' => false,
                            'criado_em' => date('c'),
                            'ultimo_login' => null,
                            'tentativas_login_falhadas' => 0,
                            'conta_bloqueada_ate' => null
                        ];
                        
                        // Criar usuário no banco de dados
                        error_log("Tentando criar usuário: Nome=$nome, Email=$email, WhatsApp=$whatsapp");
                        $usuarioCriado = $supabase->createUser($userData);
                        error_log("Resposta da criação de usuário: " . json_encode($usuarioCriado));
                        
                        // Verificar se o usuário foi criado com sucesso
                        if ($usuarioCriado && (isset($usuarioCriado['id']) || isset($usuarioCriado['success']))) {
                            $userId = $usuarioCriado['id'] ?? 'criado-com-sucesso';
                            error_log("Usuário criado com sucesso no banco: ID=$userId, Nome=$nome, Email=$email");
                            
                            // Enviar email de boas-vindas
                            error_log("📧 ENVIANDO EMAIL: Email de boas-vindas para $email");
                            try {
                                $emailEnviado = EmailManager::sendWelcomeEmail($email, $nome);
                                error_log("📧 RESULTADO EMAIL: " . ($emailEnviado ? 'SUCESSO' : 'FALHA'));
                            } catch (Exception $emailException) {
                                error_log("🚨 ERRO EMAIL: " . $emailException->getMessage());
                            }
                            
                            // Enviar código via WhatsApp (WAHA) e Email
                            error_log("🚀 INICIANDO ENVIO: Tentando enviar código via WAHA para $whatsapp");
                            error_log("📱 DADOS: Nome=$nome, Email=$email, Código=$codigoAtivacao");
                            
                            try {
                                // Enviar por WhatsApp
                                $whatsappEnviado = WhatsAppManager::sendActivationCode($whatsapp, $codigoAtivacao, $nome);
                                error_log("📤 RESULTADO WAHA: " . ($whatsappEnviado ? 'SUCESSO' : 'FALHA'));
                                
                                // Enviar também por email como backup
                                try {
                                    $emailCodigoEnviado = EmailManager::sendActivationCode($email, $codigoAtivacao, $nome);
                                    error_log("📧 RESULTADO EMAIL CÓDIGO: " . ($emailCodigoEnviado ? 'SUCESSO' : 'FALHA'));
                                } catch (Exception $emailException) {
                                    error_log("🚨 ERRO EMAIL CÓDIGO: " . $emailException->getMessage());
                                }
                                
                                if ($whatsappEnviado) {
                                    error_log("✅ CADASTRO COMPLETO: Usuário criado e código enviado para $whatsapp");
                                    
                                    // Redirecionar para confirmação
                                    $redirectUrl = 'confirmar-whatsapp.php?email=' . urlencode($email);
                                    error_log("🔄 REDIRECIONANDO: $redirectUrl");
                                    
                                    header('Location: ' . $redirectUrl);
                                    header('Cache-Control: no-cache, no-store, must-revalidate');
                                    header('Pragma: no-cache');
                                    header('Expires: 0');
                                    exit();
                                } else {
                                    error_log("❌ ERRO WAHA: Código não foi enviado para $whatsapp");
                                    throw new Exception('Não foi possível enviar código de ativação via WhatsApp. Verifique o número informado ou tente novamente.');
                                }
                            } catch (Exception $wahaException) {
                                error_log("🚨 EXCEÇÃO WAHA: " . $wahaException->getMessage());
                                throw $wahaException;
                            }
                        } else {
                            throw new Exception('Falha ao criar usuário no banco de dados');
                        }
                        
                    } catch (Exception $e) {
                        error_log("Erro ao criar usuário no banco: " . $e->getMessage());
                        error_log("Stack trace: " . $e->getTraceAsString());
                        
                        // Fornecer mensagem de erro mais específica em ambiente de desenvolvimento
                        if (Environment::get('DEBUG_MODE', false) || Environment::get('APP_ENV') === 'development') {
                            $mensagem = 'Erro ao criar conta: ' . $e->getMessage();
                        } else {
                            $mensagem = 'Erro ao criar conta. Tente novamente mais tarde.';
                        }
                    }
                }
                
            } catch (Exception $e) {
                error_log("Erro no cadastro: " . $e->getMessage());
                $mensagem = 'Erro interno. Tente novamente mais tarde.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - <?= Environment::get('APP_NAME', 'Prompt Builder IA') ?></title>
    <link rel="stylesheet" href="../assets/css/auth/auth-split.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <!-- Seção da Imagem (63%) -->
        <div class="image-section">
        </div>

        <!-- Seção do Formulário (37%) -->
        <div class="login-section">
            <div class="login-container">
                <!-- Logo -->
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-magic"></i>
                    </div>
                    <span class="logo-text"><?= Environment::get('APP_NAME', 'Prompt Builder IA') ?></span>
                </div>
                
                <div class="login-header">
                    <h1>Criar uma conta</h1>
                    <p>Cadastre-se para começar</p>
                </div>
                
                <?php if (!empty($mensagem)): ?>
                    <?php if (strpos($mensagem, 'sucesso') !== false): ?>
                        <div class="success-message"><?= $mensagem ?></div>
                    <?php else: ?>
                        <div class="error-message"><?= $mensagem ?></div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <form method="post" action="" class="login-form">
                    <?= CSRF::getHiddenField() ?>
                    
                    <div class="form-group required">
                        <label for="nome">Nome</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="nome" id="nome" class="form-input has-icon" placeholder="Insira seu nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group required">
                        <label for="email">E-mail</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" id="email" class="form-input has-icon" placeholder="Insira seu email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group required">
                        <label for="whatsapp">WhatsApp</label>
                        <div class="input-wrapper">
                            <i class="fab fa-whatsapp input-icon"></i>
                            <input type="tel" name="whatsapp" id="whatsapp" class="form-input has-icon" placeholder="(11) 99999-9999" maxlength="15" required value="<?= htmlspecialchars($_POST['whatsapp'] ?? '') ?>">
                        </div>
                        <div class="form-help">Formato: (11) 99999-9999 (obrigatório para receber código de ativação)</div>
                    </div>
                    
                    <div class="form-group required">
                        <label for="senha">Senha</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="senha" id="senha" class="form-input has-icon has-toggle" placeholder="••••••••" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('senha', this)"></i>
                        </div>
                        <div class="form-help">Mínimo 6 caracteres</div>
                    </div>
                    
                    <div class="form-group required">
                        <label for="confirmar_senha">Confirmar senha</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-input has-icon has-toggle" placeholder="Confirme sua senha" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('confirmar_senha', this)"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="login-button">Cadastrar</button>
                </form>
                
                <div class="signup-link">
                    Já tem uma conta? <a href="login.php">Entrar</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Função para alternar visibilidade da senha
        function togglePassword(fieldId, icon) {
            const field = document.getElementById(fieldId);
            const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
            field.setAttribute('type', type);
            
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        // Validações em tempo real
        document.addEventListener('DOMContentLoaded', function() {
            const nomeInput = document.getElementById('nome');
            const emailInput = document.getElementById('email');
            const senhaInput = document.getElementById('senha');
            const confirmarSenhaInput = document.getElementById('confirmar_senha');
            const whatsappInput = document.getElementById('whatsapp');

            // Validação do nome
            nomeInput.addEventListener('input', function() {
                const valor = this.value.trim();
                if (valor.length >= 2) {
                    this.classList.add('valid');
                    this.classList.remove('invalid');
                } else if (valor.length > 0) {
                    this.classList.add('invalid');
                    this.classList.remove('valid');
                } else {
                    this.classList.remove('valid', 'invalid');
                }
            });

            // Validação do email
            emailInput.addEventListener('input', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (emailRegex.test(this.value)) {
                    this.classList.add('valid');
                    this.classList.remove('invalid');
                } else if (this.value.length > 0) {
                    this.classList.add('invalid');
                    this.classList.remove('valid');
                } else {
                    this.classList.remove('valid', 'invalid');
                }
            });

            // Validação da senha
            senhaInput.addEventListener('input', function() {
                const senha = this.value;
                
                if (senha.length >= 6) {
                    this.classList.add('valid');
                    this.classList.remove('invalid');
                } else if (senha.length > 0) {
                    this.classList.add('invalid');
                    this.classList.remove('valid');
                } else {
                    this.classList.remove('valid', 'invalid');
                }
                
                // Revalida confirmação de senha se já foi preenchida
                if (confirmarSenhaInput.value) {
                    confirmarSenhaInput.dispatchEvent(new Event('input'));
                }
            });

            // Validação da confirmação de senha
            confirmarSenhaInput.addEventListener('input', function() {
                if (this.value === senhaInput.value && this.value.length > 0) {
                    this.classList.add('valid');
                    this.classList.remove('invalid');
                } else if (this.value.length > 0) {
                    this.classList.add('invalid');
                    this.classList.remove('valid');
                } else {
                    this.classList.remove('valid', 'invalid');
                }
            });


            // Máscara para WhatsApp
            whatsappInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length <= 11) {
                    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                    if (value.length < 14) {
                        value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                    }
                }
                
                e.target.value = value;
            });
        });
    </script>
</body>
</html>
