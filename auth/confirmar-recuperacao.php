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
        } elseif (strlen($newPassword) < 8) {
            $mensagem = 'Senha deve ter pelo menos 8 caracteres.';
        } elseif (!preg_match('/[A-Z]/', $newPassword)) {
            $mensagem = 'Senha deve conter pelo menos uma letra maiúscula.';
        } elseif (!preg_match('/[a-z]/', $newPassword)) {
            $mensagem = 'Senha deve conter pelo menos uma letra minúscula.';
        } elseif (!preg_match('/[0-9]/', $newPassword)) {
            $mensagem = 'Senha deve conter pelo menos um número.';
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .recovery-container {
            max-width: 500px;
            width: 100%;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            backdrop-filter: blur(10px);
        }

        .logo-icon i {
            font-size: 28px;
            color: white;
        }

        .logo-text {
            color: white;
            font-size: 24px;
            font-weight: 600;
        }

        .recovery-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .recovery-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .recovery-header h1 {
            color: #1f2937;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .recovery-header p {
            color: #6b7280;
            font-size: 16px;
        }

        .method-info {
            background: #f3f4f6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
            text-align: center;
        }

        .method-info i {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .method-info.email i {
            color: #3b82f6;
        }

        .method-info.whatsapp i {
            color: #25d366;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            background-color: #f9fafb;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            background-color: white;
        }

        .has-icon {
            padding-left: 50px;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 16px;
        }

        .recovery-button {
            width: 100%;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .recovery-button:hover {
            transform: translateY(-2px);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            color: #3b82f6;
        }

        .error-message {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .code-input {
            text-align: center;
            font-size: 24px;
            letter-spacing: 10px;
            font-weight: 600;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6b7280;
            font-size: 16px;
        }

        .toggle-password:hover {
            color: #3b82f6;
        }

        .resend-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 12px;
            margin-top: 15px;
            font-size: 14px;
            color: #1e40af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <!-- Logo -->
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <span class="logo-text"><?= Environment::get('APP_NAME', 'Prompt Builder IA') ?></span>
        </div>
        
        <!-- Card de Recuperação -->
        <div class="recovery-card">
            <div class="recovery-header">
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
            
            <form method="post" action="">
                <?= CSRF::getHiddenField() ?>
                
                <div class="form-group">
                    <label for="recovery_code">Código de Verificação</label>
                    <div class="input-wrapper">
                        <i class="fas fa-key input-icon"></i>
                        <input type="text" id="recovery_code" name="recovery_code" class="form-input has-icon code-input" 
                               placeholder="000000" maxlength="6" pattern="[0-9]{6}" required 
                               autocomplete="off">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nova Senha</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="new_password" name="new_password" class="form-input has-icon" 
                               placeholder="Sua nova senha" required>
                        <i class="fas fa-eye toggle-password" id="toggleNewPassword"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Senha</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input has-icon" 
                               placeholder="Confirme sua nova senha" required>
                        <i class="fas fa-eye toggle-password" id="toggleConfirmPassword"></i>
                    </div>
                </div>
                
                <button type="submit" class="recovery-button">Redefinir Senha</button>
            </form>
            
            <div class="resend-info">
                <i class="fas fa-info-circle"></i>
                Não recebeu o código? <a href="login.php">Tente novamente</a>
            </div>
            
            <div class="back-link">
                <a href="login.php"><i class="fas fa-arrow-left"></i> Voltar ao login</a>
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
        });

        // Auto-focus on page load
        document.getElementById('recovery_code').focus();
    </script>
</body>
</html>