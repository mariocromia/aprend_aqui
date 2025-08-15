<?php
session_start();

// Incluir dependências
require_once '../includes/Environment.php';
require_once '../includes/SupabaseClient.php';
require_once '../includes/EmailManager.php';
require_once '../includes/Sanitizer.php';
require_once '../includes/CSRF.php';

Environment::load();

// Inicializar CSRF
$csrf = new CSRF();

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['forgot_password'])) {
        // Verificar token CSRF
        if (!$csrf->validateToken($_POST['csrf_token'])) {
            $mensagem = 'Token de segurança inválido. Recarregue a página e tente novamente.';
            $tipo_mensagem = 'error';
        } else {
            // Processar recuperação de senha
            $email = Sanitizer::sanitizeEmail($_POST['forgot_email']);
            $method = $_POST['recovery_method'] ?? 'email';
            
            if (!$email) {
                $mensagem = 'E-mail inválido. Verifique o formato.';
                $tipo_mensagem = 'error';
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
                                $tipo_mensagem = 'error';
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
                                        $tipo_mensagem = 'error';
                                    }
                                } else {
                                    $mensagem = 'Erro interno. Tente novamente mais tarde.';
                                    $tipo_mensagem = 'error';
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
                                        $tipo_mensagem = 'error';
                                    }
                                } else {
                                    error_log("Login.php: ERRO - Falha ao gerar código para: $email");
                                    $mensagem = 'Erro interno. Tente novamente mais tarde.';
                                    $tipo_mensagem = 'error';
                                }
                            } else {
                                // Método tradicional com token e link
                                $token = EmailManager::generateResetToken(null, $email);
                                if ($token) {
                                    $emailSent = EmailManager::sendPasswordReset($email, $token);
                                    if ($emailSent) {
                                        $mensagem = 'Se o e-mail estiver cadastrado, você receberá instruções para redefinir sua senha.';
                                        $tipo_mensagem = 'success';
                                    } else {
                                        $mensagem = 'Erro ao enviar email. Tente novamente mais tarde.';
                                        $tipo_mensagem = 'error';
                                    }
                                } else {
                                    $mensagem = 'Erro interno. Tente novamente mais tarde.';
                                    $tipo_mensagem = 'error';
                                }
                            }
                        }
                    } else {
                        // Não revelar se o email existe ou não
                        $mensagem = 'Se o e-mail estiver cadastrado, você receberá instruções para redefinir sua senha.';
                        $tipo_mensagem = 'success';
                    }
                    
                    // Registrar tentativa de recuperação
                    $supabase->logLoginAttempt($email, false, "recuperacao_senha_$method");
                    
                } catch (Exception $e) {
                    error_log("Erro na recuperação de senha: " . $e->getMessage());
                    $mensagem = 'Erro interno. Tente novamente mais tarde.';
                    $tipo_mensagem = 'error';
                }
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
                        $tipo_mensagem = 'error';
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
                    // TODO: Implementar verificação de senha com Supabase Auth
                    // Por enquanto, usar verificação mockada para admin@teste.com
                    if ($loginInput === 'admin@teste.com' && $senha === 'Admin123!') {
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
                        header('Location: ../gerador_prompt.php');
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
                            $tipo_mensagem = 'error';
                        } else {
                            $mensagem = 'Email ou senha incorretos.';
                            $tipo_mensagem = 'error';
                        }
                    }
                } // Fecha o bloco if (empty($mensagem))
            } else {
                // Usuário não encontrado
                $supabase->logLoginAttempt($loginInput, false, 'email_nao_encontrado');
                $mensagem = 'Email ou senha incorretos.';
                $tipo_mensagem = 'error';
            }
            
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            $mensagem = 'Erro interno. Tente novamente mais tarde.';
            $tipo_mensagem = 'error';
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
        <!-- Seção da Imagem (60%) -->
        <div class="image-section">
            <div class="hero-content">
                <div class="hero-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <h1>Prompt Builder IA</h1>
                <p>Crie prompts inteligentes com o poder da Inteligência Artificial. Transforme suas ideias em comandos precisos e eficazes.</p>
                
                <div class="hero-features">
                    <div class="hero-feature">
                        <i class="fas fa-magic"></i>
                        <span>IA Avançada</span>
                    </div>
                    <div class="hero-feature">
                        <i class="fas fa-rocket"></i>
                        <span>Resultados Rápidos</span>
                    </div>
                    <div class="hero-feature">
                        <i class="fas fa-shield-alt"></i>
                        <span>Seguro & Confiável</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção de Login (40%) -->
        <div class="login-section">
            <div class="login-container">
                <div class="logo">
                    <div class="logo-icon">PB</div>
                    <div class="logo-text">Prompt Builder</div>
                </div>

                <div class="login-header">
                    <h1>Bem-vindo</h1>
                    <p>Faça login para acessar sua conta</p>
                </div>

                <?php if (!empty($mensagem)): ?>
                    <div class="<?= $tipo_mensagem === 'success' ? 'success-message' : 'error-message' ?>">
                        <?= htmlspecialchars($mensagem) ?>
                    </div>
                <?php endif; ?>

                <!-- Informações de demonstração -->
                <div class="demo-info">
                    <strong>📋 Login de Demonstração:</strong><br>
                    Email: admin@teste.com<br>
                    Senha: Admin123!
                </div>

                <form method="POST" class="login-form" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf->getToken() ?>">
                    
                    <div class="form-group required">
                        <label for="login">E-mail</label>
                        <div class="input-wrapper">
                            <input 
                                type="email" 
                                id="login" 
                                name="login" 
                                class="form-input has-icon" 
                                placeholder="Digite seu e-mail"
                                required 
                                autocomplete="email"
                                value="<?= isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '' ?>"
                            >
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group required">
                        <label for="senha">Senha</label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                id="senha" 
                                name="senha" 
                                class="form-input has-icon has-toggle" 
                                placeholder="Digite sua senha"
                                required 
                                autocomplete="current-password"
                            >
                            <i class="fas fa-lock input-icon"></i>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                        </div>
                    </div>

                    <button type="submit" class="login-button">
                        <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>
                        Entrar
                    </button>
                </form>

                <div class="forgot-password-link">
                    <a href="#" onclick="openForgotPasswordModal()" id="forgotPasswordLink">
                        <i class="fas fa-key" style="margin-right: 5px;"></i>
                        Esqueci minha senha
                    </a>
                </div>

                <div class="signup-link">
                    Não tem uma conta? <a href="cadastro.php">Cadastre-se aqui</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Recuperação de Senha -->
    <div id="forgotPasswordModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeForgotPasswordModal()">&times;</span>
            <h2><i class="fas fa-key" style="margin-right: 10px; color: #667eea;"></i>Recuperar Senha</h2>
            <p>Escolha como você gostaria de recuperar sua senha:</p>
            
            <form method="POST" id="forgotPasswordForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf->getToken() ?>">
                <input type="hidden" name="forgot_password" value="1">
                
                <div class="form-group">
                    <label for="forgot_email">E-mail</label>
                    <div class="input-wrapper">
                        <input type="email" id="forgot_email" name="forgot_email" class="form-input has-icon" 
                               placeholder="Digite seu e-mail" required>
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                </div>
                
                <div class="recovery-methods">
                    <label class="method-option">
                        <input type="radio" name="recovery_method" value="email" checked>
                        <input type="hidden" name="use_code" value="1">
                        <div class="method-content">
                            <i class="fas fa-envelope"></i>
                            <span>Código por E-mail</span>
                            <small>Receba um código de 6 dígitos no seu e-mail (válido por 10 minutos)</small>
                        </div>
                    </label>
                    
                    <label class="method-option">
                        <input type="radio" name="recovery_method" value="whatsapp">
                        <div class="method-content">
                            <i class="fab fa-whatsapp"></i>
                            <span>Código por WhatsApp</span>
                            <small>Receba um código de 6 dígitos no WhatsApp (válido por 10 minutos)</small>
                        </div>
                    </label>
                    
                    <label class="method-option">
                        <input type="radio" name="recovery_method" value="email" onchange="document.querySelector('input[name=use_code]').value='0'">
                        <div class="method-content">
                            <i class="fas fa-link"></i>
                            <span>Link Tradicional</span>
                            <small>Receba um link seguro no e-mail (válido por 1 hora)</small>
                        </div>
                    </label>
                </div>
                
                <button type="submit" class="login-button" style="margin-top: 25px;">
                    <i class="fas fa-paper-plane" style="margin-right: 8px;"></i>
                    Enviar
                </button>
            </form>
        </div>
    </div>

    <script>
        // Função para alternar visibilidade da senha
        function togglePassword() {
            const passwordInput = document.getElementById('senha');
            const toggleIcon = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Validações em tempo real
        document.addEventListener('DOMContentLoaded', function() {
            const loginInput = document.getElementById('login');

            loginInput.addEventListener('input', function() {
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

        // Atualizar campo use_code quando método email tradicional é selecionado
        document.querySelectorAll('input[name="recovery_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'email') {
                    // Se for o último radio (link tradicional), setar use_code como 0
                    const isTraditionalLink = this.parentElement.querySelector('.fa-link');
                    document.querySelector('input[name="use_code"]').value = isTraditionalLink ? '0' : '1';
                }
            });
        });
    </script>
</body>
</html>