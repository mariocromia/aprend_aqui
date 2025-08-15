<?php
session_start();

require_once '../includes/Environment.php';
require_once '../includes/CSRF.php';
require_once '../includes/Sanitizer.php';
require_once '../includes/EmailManager.php';

$mensagem = '';
$email = $_GET['email'] ?? '';
$method = $_GET['method'] ?? 'email';

// Validar parâmetros
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!CSRF::verifyPostToken()) {
        $mensagem = 'Erro de segurança detectado. Tente novamente.';
    } else {
        $code = trim($_POST['recovery_code']);
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validações
        if (empty($code) || strlen($code) !== 6 || !ctype_digit($code)) {
            $mensagem = 'Código deve ter 6 dígitos.';
        } elseif (strlen($newPassword) < 6) {
            $mensagem = 'Senha deve ter pelo menos 6 caracteres.';
        } elseif ($newPassword !== $confirmPassword) {
            $mensagem = 'As senhas não coincidem.';
        } else {
            try {
                // Verificar código e redefinir senha
                $result = EmailManager::resetPasswordWithCode($email, $code, $newPassword);
                
                if ($result['success']) {
                    $_SESSION['recovery_success'] = true;
                    header('Location: login.php?recovery=success');
                    exit();
                } else {
                    $mensagem = $result['message'];
                }
                
            } catch (Exception $e) {
                error_log("Erro na confirmação de recuperação: " . $e->getMessage());
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
    <title>Confirmar Recuperação - <?= Environment::get('APP_NAME', 'Prompt Builder IA') ?></title>
    <link rel="stylesheet" href="../assets/css/auth/auth-split.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .method-info {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 16px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .method-info i {
            font-size: 18px;
            margin-bottom: 6px;
        }

        .method-info.email i {
            color: #667eea;
        }

        .method-info.whatsapp i {
            color: #25d366;
        }

        .method-info p {
            margin: 0;
            font-size: 13px;
            color: #374151;
        }

        .method-info strong {
            font-size: 14px;
            color: #1f2937;
        }

        .code-input {
            text-align: center;
            font-size: 18px;
            letter-spacing: 4px;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        .resend-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 10px;
            margin-top: 12px;
            font-size: 12px;
            color: #1e40af;
            text-align: center;
        }

        .resend-info a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .resend-info a:hover {
            text-decoration: underline;
        }
    </style>
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
                    <h1>Confirmar Recuperação</h1>
                    <p>Digite o código e sua nova senha</p>
                </div>
                
                <!-- Informações do método -->
                <div class="method-info <?= $method ?>">
                    <?php if ($method === 'whatsapp'): ?>
                        <i class="fab fa-whatsapp"></i>
                        <p><strong>Código enviado via WhatsApp</strong></p>
                        <p>Verifique seu WhatsApp para o código de 6 dígitos</p>
                    <?php else: ?>
                        <i class="fas fa-envelope"></i>
                        <p><strong>Código enviado por email</strong></p>
                        <p>Verifique seu email para o código de 6 dígitos</p>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($mensagem)): ?>
                    <div class="error-message">
                        <?= $mensagem ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="" class="login-form">
                    <?= CSRF::getHiddenField() ?>
                    
                    <div class="form-group required">
                        <label for="recovery_code">Código de Verificação</label>
                        <div class="input-wrapper">
                            <i class="fas fa-key input-icon"></i>
                            <input type="text" id="recovery_code" name="recovery_code" class="form-input has-icon code-input" 
                                   placeholder="000000" maxlength="6" pattern="[0-9]{6}" required 
                                   autocomplete="off">
                        </div>
                    </div>
                    
                    <div class="form-group required">
                        <label for="new_password">Nova Senha</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="new_password" name="new_password" class="form-input has-icon has-toggle" 
                                   placeholder="Sua nova senha" required>
                            <i class="fas fa-eye password-toggle" id="toggleNewPassword"></i>
                        </div>
                        <div class="form-help">Mínimo 6 caracteres</div>
                    </div>
                    
                    <div class="form-group required">
                        <label for="confirm_password">Confirmar Senha</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input has-icon has-toggle" 
                                   placeholder="Confirme sua nova senha" required>
                            <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="login-button">Redefinir Senha</button>
                </form>
                
                <div class="resend-info">
                    <i class="fas fa-info-circle"></i>
                    Não recebeu o código? <a href="login.php">Tente novamente</a>
                </div>
                
                <div class="signup-link">
                    <a href="login.php"><i class="fas fa-arrow-left"></i> Voltar ao login</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('toggleNewPassword').addEventListener('click', function() {
            const passwordField = document.getElementById('new_password');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordField = document.getElementById('confirm_password');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Auto-format code input
        document.getElementById('recovery_code').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            
            // Validar se tem 6 dígitos
            if (this.value.length === 6) {
                this.classList.add('valid');
                this.classList.remove('invalid');
            } else if (this.value.length > 0) {
                this.classList.add('invalid');
                this.classList.remove('valid');
            } else {
                this.classList.remove('valid', 'invalid');
            }
        });

        // Validação em tempo real das senhas
        document.addEventListener('DOMContentLoaded', function() {
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');

            newPasswordInput.addEventListener('input', function() {
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
                
                // Revalidar confirmação se já foi preenchida
                if (confirmPasswordInput.value) {
                    confirmPasswordInput.dispatchEvent(new Event('input'));
                }
            });

            confirmPasswordInput.addEventListener('input', function() {
                if (this.value === newPasswordInput.value && this.value.length > 0) {
                    this.classList.add('valid');
                    this.classList.remove('invalid');
                } else if (this.value.length > 0) {
                    this.classList.add('invalid');
                    this.classList.remove('valid');
                } else {
                    this.classList.remove('valid', 'invalid');
                }
            });

            // Auto-focus on page load
            document.getElementById('recovery_code').focus();
        });
    </script>
</body>
</html>