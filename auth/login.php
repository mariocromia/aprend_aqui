<?php
session_start();

// Carregar classes de segurança
require_once '../includes/Environment.php';
require_once '../includes/CSRF.php';
require_once '../includes/Sanitizer.php';
require_once '../includes/EmailManager.php';
require_once '../includes/SupabaseClient.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!CSRF::verifyPostToken()) {
        $mensagem = 'Erro de segurança detectado. Tente novamente.';
    } else {
        // Verificar se é recuperação de senha
        if (isset($_POST['forgot_email']) || isset($_POST['recovery_method'])) {
            // Processar recuperação de senha
            $email = Sanitizer::sanitizeEmail($_POST['forgot_email']);
            $method = $_POST['recovery_method'] ?? 'email';
            
            if (!$email) {
                $mensagem = 'E-mail inválido. Verifique o formato.';
            } else {
                try {
                    $supabase = new SupabaseClient();
                    
                    // Verificar se o email existe e buscar dados do usuário
                    $user = $supabase->getUserByEmail($email);
                    if ($user) {
                        if ($method === 'whatsapp') {
                            // Verificar se usuário tem WhatsApp cadastrado
                            if (empty($user['whatsapp'])) {
                                $mensagem = 'Este usuário não possui WhatsApp cadastrado. Use a recuperação por email.';
                            } else {
                                // Gerar código e enviar via WhatsApp
                                $code = EmailManager::generateResetCode($email, 'whatsapp');
                                if ($code) {
                                    $whatsappSent = EmailManager::sendRecoveryCodeByWhatsApp($user['whatsapp'], $code, $user['nome']);
                                    if ($whatsappSent) {
                                        // Redirecionar para página de confirmação
                                        header('Location: confirmar-recuperacao.php?email=' . urlencode($email) . '&method=whatsapp');
                                        exit();
                                    } else {
                                        $mensagem = 'Erro ao enviar código via WhatsApp. Tente a recuperação por email.';
                                    }
                                } else {
                                    $mensagem = 'Erro interno. Tente novamente mais tarde.';
                                }
                            }
                        } else {
                            // Método por email - pode usar tanto código quanto token
                            $useCode = isset($_POST['use_code']) && $_POST['use_code'] === '1';
                            
                            if ($useCode) {
                                // Gerar código e enviar por email
                                error_log("Login.php: Tentativa de recuperação por código de email para: $email");
                                $code = EmailManager::generateResetCode($email, 'email');
                                if ($code) {
                                    error_log("Login.php: Código gerado com sucesso: $code para $email");
                                    $emailSent = EmailManager::sendRecoveryCodeByEmail($email, $code, $user['nome']);
                                    if ($emailSent) {
                                        error_log("Login.php: Email enviado com sucesso para: $email");
                                        // Redirecionar para página de confirmação
                                        header('Location: confirmar-recuperacao.php?email=' . urlencode($email) . '&method=email');
                                        exit();
                                    } else {
                                        error_log("Login.php: ERRO - Falha no envio de email para: $email");
                                        $mensagem = 'Erro ao enviar código por email. Tente novamente mais tarde.';
                                    }
                                } else {
                                    error_log("Login.php: ERRO - Falha ao gerar código para: $email");
                                    $mensagem = 'Erro interno. Tente novamente mais tarde.';
                                }
                            } else {
                                // Método tradicional com token e link
                                $token = EmailManager::generateResetToken(null, $email);
                                if ($token) {
                                    $emailSent = EmailManager::sendPasswordReset($email, $token);
                                    if ($emailSent) {
                                        $mensagem = 'Se o e-mail estiver cadastrado, você receberá instruções para redefinir sua senha.';
                                    } else {
                                        $mensagem = 'Erro ao enviar email. Tente novamente mais tarde.';
                                    }
                                } else {
                                    $mensagem = 'Erro interno. Tente novamente mais tarde.';
                                }
                            }
                        }
                    } else {
                        // Não revelar se o email existe ou não
                        $mensagem = 'Se o e-mail estiver cadastrado, você receberá instruções para redefinir sua senha.';
                    }
                    
                    // Registrar tentativa de recuperação
                    $supabase->logLoginAttempt($email, false, "recuperacao_senha_$method");
                    
                } catch (Exception $e) {
                    error_log("Erro na recuperação de senha: " . $e->getMessage());
                    $mensagem = 'Erro interno. Tente novamente mais tarde.';
                }
            }
        } else {
            // Processar login normal
            $loginInput = trim($_POST['login']);
            $senha = $_POST['senha'];

            try {
                $supabase = new SupabaseClient();
                
                // Buscar usuário por email
                $usuario = $supabase->getUserByEmail($loginInput);
                
                if ($usuario) {
                    // Verificar se a conta está bloqueada
                    if (!empty($usuario['conta_bloqueada_ate'])) {
                        $bloqueadaAte = strtotime($usuario['conta_bloqueada_ate']);
                        $agora = time();
                        
                        if ($agora < $bloqueadaAte) {
                            // Ainda está bloqueada
                            $minutosRestantes = ceil(($bloqueadaAte - $agora) / 60);
                            $mensagem = "Conta bloqueada. Tente novamente em $minutosRestantes minuto(s).";
                        } else {
                            // Período de bloqueio expirou - RESETAR contador de tentativas
                            error_log("Login.php: Conta desbloqueada para {$usuario['email']} - Resetando contador de tentativas");
                            $supabase->updateUser($usuario['id'], [
                                'conta_bloqueada_ate' => null,
                                'tentativas_login_falhadas' => 0
                            ]);
                            // Recarregar dados do usuário após reset
                            $usuario = $supabase->getUserByEmail($loginInput);
                        }
                    }
                    
                    // Só continuar se não estiver bloqueada
                    if (empty($mensagem)) {
                        // Verificar senha usando hash do banco de dados
                        $senhaValida = false;
                        
                        // Verificar qual coluna contém a senha
                        $senhaHash = null;
                        if (isset($usuario['senha_hash']) && !empty($usuario['senha_hash'])) {
                            $senhaHash = $usuario['senha_hash'];
                        } elseif (isset($usuario['senha']) && !empty($usuario['senha'])) {
                            $senhaHash = $usuario['senha'];
                        }
                        
                        if ($senhaHash) {
                            // Verificar se é um hash (começa com $2y$)
                            if (str_starts_with($senhaHash, '$2y$')) {
                                $senhaValida = password_verify($senha, $senhaHash);
                            } else {
                                // Fallback para senhas em texto puro (apenas para migração)
                                $senhaValida = ($senha === $senhaHash);
                            }
                        }
                        
                        // Fallback para usuário admin hardcoded
                        if (!$senhaValida && $loginInput === 'admin@teste.com' && $senha === 'Admin123!') {
                            $senhaValida = true;
                        }
                        
                        if ($senhaValida) {
                            // Login bem-sucedido
                            $_SESSION['usuario_id'] = $usuario['id'];
                            $_SESSION['usuario_nome'] = $usuario['nome'];
                            $_SESSION['usuario_email'] = $usuario['email'];
                            
                            // Regenerar ID da sessão para prevenir session fixation
                            session_regenerate_id(true);
                            
                            // Registrar tentativa de login bem-sucedida
                            $supabase->logLoginAttempt($loginInput, true);
                            
                            // Atualizar último login
                            $supabase->updateUser($usuario['id'], [
                                'ultimo_login' => date('c'),
                                'tentativas_login_falhadas' => 0,
                                'conta_bloqueada_ate' => null
                            ]);
                            
                            // Redirecionar para o gerador de prompts
                            header('Location: ../gerador_prompt_modern.php');
                            exit;
                        } else {
                        // Senha incorreta
                        $supabase->logLoginAttempt($loginInput, false, 'senha_incorreta');
                        
                        // Incrementar tentativas falhadas
                        $tentativas = ($usuario['tentativas_login_falhadas'] ?? 0) + 1;
                        $supabase->updateUser($usuario['id'], [
                            'tentativas_login_falhadas' => $tentativas
                        ]);
                        
                        // Bloquear conta após 5 tentativas
                        if ($tentativas >= 5) {
                            $supabase->updateUser($usuario['id'], [
                                'conta_bloqueada_ate' => date('c', time() + 900) // 15 minutos
                            ]);
                            $mensagem = 'Conta bloqueada por 15 minutos devido a múltiplas tentativas falhadas.';
                        } else {
                            $mensagem = 'Email ou senha incorretos.';
                        }
                    }
                    } // Fecha o bloco if (empty($mensagem))
                } else {
                    // Usuário não encontrado
                    $supabase->logLoginAttempt($loginInput, false, 'email_nao_encontrado');
                    $mensagem = 'Email ou senha incorretos.';
                }
                
            } catch (Exception $e) {
                error_log("Erro no login: " . $e->getMessage());
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
    <title>Login - <?= Environment::get('APP_NAME', 'Prompt Builder IA') ?></title>
    <link rel="stylesheet" href="../assets/css/auth/auth-split.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <!-- Seção da Imagem (63%) -->
        <div class="image-section">
        </div>

        <!-- Seção do Login (37%) -->
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
                    <h1>Bem-vindo de volta</h1>
                    <p>Entre na sua conta</p>
                </div>
                
                <?php if (!empty($mensagem)): ?>
                    <div class="<?= strpos($mensagem, 'sucesso') !== false || strpos($mensagem, 'receberá') !== false ? 'success-message' : 'error-message' ?>">
                        <?= $mensagem ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="" class="login-form">
                    <?= CSRF::getHiddenField() ?>
                    <div class="form-group required">
                        <label for="login">E-mail ou Telefone</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="login" id="login" class="form-input has-icon" placeholder="Insira seu E-mail ou telefone" required value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" autocomplete="username">
                        </div>
                    </div>
                    
                    <div class="form-group required">
                        <label for="senha">Senha</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="senha" id="senha" class="form-input has-icon has-toggle" placeholder="••••••••" required>
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="login-button">Entrar</button>
                </form>
                
                <div class="forgot-password-link">
                    <a href="#" onclick="openForgotPasswordModal()">Esqueceu a senha?</a>
                </div>
                
                <div class="signup-link">
                    Não tem uma conta? <a href="cadastro.php">Cadastre-se</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Recuperação de Senha -->
    <div id="forgotPasswordModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeForgotPasswordModal()">&times;</span>
            <h2>Recuperar Senha</h2>
            <p>Digite seu email e escolha como deseja receber o código de recuperação:</p>
            
            <form id="forgotPasswordForm" method="post">
                <?= CSRF::getHiddenField() ?>
                <div class="form-group required">
                    <label for="forgot_email">E-mail</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="forgot_email" name="forgot_email" class="form-input has-icon" placeholder="seu@email.com" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Método de Recuperação</label>
                    <div class="recovery-methods">
                        <label class="method-option">
                            <input type="radio" name="recovery_method" value="email" checked>
                            <div class="method-content">
                                <i class="fas fa-envelope"></i>
                                <span>Email</span>
                                <small>Código por email</small>
                            </div>
                        </label>
                        
                        <label class="method-option">
                            <input type="radio" name="recovery_method" value="whatsapp">
                            <div class="method-content">
                                <i class="fab fa-whatsapp"></i>
                                <span>WhatsApp</span>
                                <small>Código via WhatsApp</small>
                            </div>
                        </label>
                        
                        <label class="method-option">
                            <input type="radio" name="recovery_method" value="email" onclick="document.getElementById('use_code').value='0'">
                            <div class="method-content">
                                <i class="fas fa-link"></i>
                                <span>Link por Email</span>
                                <small>Link tradicional</small>
                            </div>
                        </label>
                    </div>
                    <input type="hidden" id="use_code" name="use_code" value="1">
                </div>
                
                <button type="submit" class="login-button">Enviar Código</button>
            </form>
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('senha');

        togglePassword.addEventListener('click', function() {
            // Alterna o tipo do campo entre password e text
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            // Alterna o ícone entre olho aberto e fechado
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Validações em tempo real
        document.addEventListener('DOMContentLoaded', function() {
            const loginInput = document.getElementById('login');

            loginInput.addEventListener('input', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const phoneRegex = /^\d{10,11}$/;
                const cleanValue = this.value.replace(/\D/g, '');
                
                if (emailRegex.test(this.value) || phoneRegex.test(cleanValue)) {
                    this.classList.add('valid');
                    this.classList.remove('invalid');
                } else if (this.value.length > 0) {
                    this.classList.add('invalid');
                    this.classList.remove('valid');
                } else {
                    this.classList.remove('valid', 'invalid');
                }
            });
        });

        // Funções para controlar o modal
        function openForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').style.display = 'block';
        }

        function closeForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').style.display = 'none';
            document.getElementById('forgotPasswordForm').reset();
        }

        // Fechar modal clicando fora dele
        window.onclick = function(event) {
            const modal = document.getElementById('forgotPasswordModal');
            if (event.target === modal) {
                closeForgotPasswordModal();
            }
        }
    </script>
</body>
</html>
