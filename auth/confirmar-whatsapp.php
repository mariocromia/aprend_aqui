<?php
session_start();

require_once '../includes/Environment.php';
require_once '../includes/CSRF.php';
require_once '../includes/Sanitizer.php';
require_once '../includes/WhatsAppManager.php';

$mensagem = '';
$email = $_GET['email'] ?? ($_POST['email'] ?? '');
$codigo = $_POST['codigo'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $email) {
    // Verificar CSRF
    if (!CSRF::verifyPostToken()) {
        $mensagem = 'Erro de segurança detectado. Tente novamente.';
    } else {
        // Validar código de ativação usando WhatsAppManager
        $resultado = WhatsAppManager::validateActivationCode($email, $codigo);
        
        if ($resultado['success']) {
            // Login do usuário validado
            $user = $resultado['user'];
            
            // Se for modo demo, usar dados padrão
            if (strpos($user['id'], 'demo_') === 0) {
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nome'] = 'Usuário Demo';
                $_SESSION['usuario_email'] = $email;
                $_SESSION['usuario_whatsapp'] = 'Demo Mode';
            } else {
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nome'] = $user['nome'] ?? 'Usuário';
                $_SESSION['usuario_email'] = $user['email'];
                $_SESSION['usuario_whatsapp'] = $user['whatsapp'] ?? '';
            }
            
            session_regenerate_id(true);
            
            // Log do sucesso
            error_log("WhatsApp confirmado com sucesso para: " . $email);
            
            // Redirecionar para o gerador de prompts
            header('Location: ../gerador_prompt.php');
            exit;
        } else {
            $mensagem = $resultado['message'];
            error_log("Erro ao validar código WhatsApp para $email: " . $resultado['message']);
        }
    }
}

// Se não há email, redirecionar para cadastro
if (!$email) {
    header('Location: cadastro.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar WhatsApp - <?= Environment::get('APP_NAME', 'Prompt Builder IA') ?></title>
    <link rel="stylesheet" href="../assets/css/auth/auth.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .demo-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #1e40af;
        }
        
        .demo-info strong {
            color: #1d4ed8;
        }
        
        .verification-code {
            text-align: center;
            font-size: 18px;
            letter-spacing: 4px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Logo -->
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-magic"></i>
            </div>
            <span class="logo-text"><?= Environment::get('APP_NAME', 'Prompt Builder IA') ?></span>
        </div>
        
        <!-- Card de Confirmação -->
        <div class="login-card">
            <div class="login-header">
                <h1>Confirmação do WhatsApp</h1>
                <p>Digite o código que você recebeu</p>
            </div>
            
            <!-- Informações demo (apenas em modo demo) -->
            <?php if (Environment::get('WHATSAPP_PROVIDER', 'waha') === 'demo'): ?>
            <div class="demo-info">
                <strong>Demo:</strong> Use qualquer código de <strong>6 dígitos</strong> para continuar (ex: 123456)
            </div>
            <?php elseif (Environment::get('WHATSAPP_PROVIDER', 'waha') === 'waha'): ?>
            <div class="demo-info" style="background: #ecfdf5; border-color: #10b981; color: #065f46;">
                <strong>WAHA Ativo:</strong> O código foi enviado via WhatsApp pela API WAHA
            </div>
            <?php endif; ?>
            
            <?php if (!empty($mensagem)): ?>
                <div class="<?= strpos($mensagem, 'sucesso') !== false ? 'success-message' : 'error-message' ?>">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" class="login-form">
                <?= CSRF::getHiddenField() ?>
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                
                <div class="form-group">
                    <label>E-mail cadastrado:</label>
                    <div style="padding: 8px; background: #f9fafb; border-radius: 6px; color: #374151; font-weight: 500;">
                        <?= htmlspecialchars($email) ?>
                    </div>
                </div>
                
                <div class="form-group required">
                    <label for="codigo">Código de Ativação</label>
                    <div class="input-wrapper">
                        <i class="fas fa-key input-icon"></i>
                        <input type="text" name="codigo" id="codigo" class="form-input has-icon verification-code" maxlength="6" pattern="[0-9]{6}" required placeholder="123456" autocomplete="one-time-code">
                    </div>
                    <div class="form-help">Digite o código de 6 dígitos enviado por WhatsApp</div>
                </div>
                
                <button type="submit" class="login-button">Confirmar</button>
            </form>
            
            <div class="signup-link">
                <a href="cadastro.php">Voltar ao cadastro</a> |
                <a href="login.php">Fazer login</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const codigoInput = document.getElementById('codigo');
            
            // Aceitar apenas números
            codigoInput.addEventListener('input', function() {
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
            
            // Focar no campo ao carregar
            codigoInput.focus();
        });
    </script>
</body>
</html>
