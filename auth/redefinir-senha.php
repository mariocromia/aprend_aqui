<?php
session_start();

require_once '../includes/Environment.php';
require_once '../includes/CSRF.php';
require_once '../includes/Sanitizer.php';
require_once '../includes/EmailManager.php';

$mensagem = '';
$token = $_GET['token'] ?? '';
$validToken = false;

// Verificar se o token é válido
if ($token) {
    $resetData = EmailManager::verifyResetToken($token);
    if ($resetData) {
        $validToken = true;
    } else {
        $mensagem = 'Link inválido ou expirado. Solicite uma nova recuperação de senha.';
    }
} else {
    $mensagem = 'Token não fornecido.';
}

// Processar redefinição de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    // Verificar CSRF
    if (!CSRF::verifyPostToken()) {
        $mensagem = 'Erro de segurança detectado. Tente novamente.';
    } else {
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        
        // Validações
        if (strlen($novaSenha) < 8) {
            $mensagem = 'A senha deve ter pelo menos 8 caracteres.';
        } elseif (!preg_match('/[A-Z]/', $novaSenha)) {
            $mensagem = 'A senha deve conter pelo menos uma letra maiúscula.';
        } elseif (!preg_match('/[a-z]/', $novaSenha)) {
            $mensagem = 'A senha deve conter pelo menos uma letra minúscula.';
        } elseif (!preg_match('/[0-9]/', $novaSenha)) {
            $mensagem = 'A senha deve conter pelo menos um número.';
        } elseif ($novaSenha !== $confirmarSenha) {
            $mensagem = 'As senhas não coincidem.';
        } else {
            // Redefinir senha
            $result = EmailManager::resetPassword($token, $novaSenha);
            
            if ($result['success']) {
                $mensagem = 'Senha redefinida com sucesso! Você pode fazer login com sua nova senha.';
                $validToken = false; // Desabilitar formulário
            } else {
                $mensagem = $result['message'];
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
    <title>Redefinir Senha - <?= Environment::get('APP_NAME', 'Prompt Builder IA') ?></title>
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
                    <h1>Redefinir senha</h1>
                    <p>Digite sua nova senha</p>
                </div>
                
                <?php if (!empty($mensagem)): ?>
                    <div class="<?= strpos($mensagem, 'sucesso') !== false ? 'success-message' : 'error-message' ?>">
                        <?= $mensagem ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($validToken): ?>
                    <form method="post" action="" class="login-form">
                        <?= CSRF::getHiddenField() ?>
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        
                        <div class="form-group required">
                            <label for="nova_senha">Nova senha</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="nova_senha" id="nova_senha" class="form-input has-icon has-toggle" placeholder="Digite sua nova senha" required>
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('nova_senha', this)"></i>
                            </div>
                            <div class="form-help">
                                Mínimo 8 caracteres, incluindo maiúscula, minúscula e número
                            </div>
                        </div>
                        
                        <div class="form-group required">
                            <label for="confirmar_senha">Confirmar nova senha</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-input has-icon has-toggle" placeholder="Confirme sua nova senha" required>
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('confirmar_senha', this)"></i>
                            </div>
                        </div>
                        
                        <button type="submit" class="login-button">Redefinir senha</button>
                    </form>
                <?php else: ?>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="login.php" class="login-button" style="display: inline-block; text-decoration: none;">
                            Voltar ao login
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="signup-link">
                    <a href="login.php">Voltar ao login</a>
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

        // Validação em tempo real
        document.addEventListener('DOMContentLoaded', function() {
            const novaSenhaInput = document.getElementById('nova_senha');
            const confirmarSenhaInput = document.getElementById('confirmar_senha');

            if (novaSenhaInput) {
                novaSenhaInput.addEventListener('input', function() {
                    const senha = this.value;
                    let isValid = true;
                    
                    // Verificar critérios
                    if (senha.length < 8 || 
                        !/[A-Z]/.test(senha) || 
                        !/[a-z]/.test(senha) || 
                        !/[0-9]/.test(senha)) {
                        isValid = false;
                    }
                    
                    if (isValid) {
                        this.classList.add('valid');
                        this.classList.remove('invalid');
                    } else if (senha.length > 0) {
                        this.classList.add('invalid');
                        this.classList.remove('valid');
                    } else {
                        this.classList.remove('valid', 'invalid');
                    }
                    
                    // Revalidar confirmação se já foi preenchida
                    if (confirmarSenhaInput && confirmarSenhaInput.value) {
                        confirmarSenhaInput.dispatchEvent(new Event('input'));
                    }
                });
            }

            if (confirmarSenhaInput) {
                confirmarSenhaInput.addEventListener('input', function() {
                    const novaSenha = novaSenhaInput ? novaSenhaInput.value : '';
                    const confirmarSenha = this.value;
                    
                    if (novaSenha === confirmarSenha && confirmarSenha.length > 0) {
                        this.classList.add('valid');
                        this.classList.remove('invalid');
                    } else if (confirmarSenha.length > 0) {
                        this.classList.add('invalid');
                        this.classList.remove('valid');
                    } else {
                        this.classList.remove('valid', 'invalid');
                    }
                });
            }
        });
    </script>
</body>
</html>
