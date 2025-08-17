<?php
session_start();

// Otimizado - carregar apenas o essencial
require_once '../includes/Environment.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    // Login simplificado e rápido
    if ($loginInput && $senha) {
        try {
            // Fallback rápido para admin
            if ($loginInput === 'admin@teste.com' && $senha === 'Admin123!') {
                $_SESSION['usuario_id'] = 1;
                $_SESSION['usuario_nome'] = 'Administrador';
                $_SESSION['usuario_email'] = 'admin@teste.com';
                
                session_regenerate_id(true);
                header('Location: ../gerador_prompt_modern.php');
                exit;
            }
            
            // Carregar SupabaseClient apenas se necessário
            require_once '../includes/SupabaseClient.php';
            $supabase = new SupabaseClient();
            
            $usuario = $supabase->getUserByEmail($loginInput);
            
            if ($usuario) {
                $senhaValida = false;
                $senhaHash = $usuario['senha_hash'] ?? $usuario['senha'] ?? '';
                
                if ($senhaHash) {
                    if (str_starts_with($senhaHash, '$2y$')) {
                        $senhaValida = password_verify($senha, $senhaHash);
                    } else {
                        $senhaValida = ($senha === $senhaHash);
                    }
                }
                
                if ($senhaValida) {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    $_SESSION['usuario_email'] = $usuario['email'];
                    
                    session_regenerate_id(true);
                    header('Location: ../gerador_prompt_modern.php');
                    exit;
                } else {
                    $mensagem = 'Email ou senha incorretos.';
                }
            } else {
                $mensagem = 'Email ou senha incorretos.';
            }
            
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            $mensagem = 'Erro interno. Tente novamente.';
        }
    } else {
        $mensagem = 'Preencha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= Environment::get('APP_NAME', 'Prompt Builder IA') ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .login-container {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-icon {
            width: 3rem;
            height: 3rem;
            background: #6366f1;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .logo-text {
            display: block;
            font-size: 1.25rem;
            font-weight: bold;
            color: #1e293b;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #64748b;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #6366f1;
        }
        
        .input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
        
        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9ca3af;
        }
        
        .login-button {
            width: 100%;
            background: #6366f1;
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .login-button:hover {
            background: #5855eb;
        }
        
        .error-message {
            background: #fee2e2;
            border: 1px solid #ef4444;
            color: #991b1b;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }
        
        .signup-link a {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .fas {
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }
        
        .fa-magic:before { content: "✨"; }
        .fa-user:before { content: "👤"; }
        .fa-lock:before { content: "🔒"; }
        .fa-eye:before { content: "👁"; }
        .fa-eye-slash:before { content: "🙈"; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-icon">
                <span class="fas fa-magic"></span>
            </div>
            <span class="logo-text"><?= Environment::get('APP_NAME', 'Prompt Builder IA') ?></span>
        </div>
        
        <div class="login-header">
            <h1>Bem-vindo de volta</h1>
            <p>Entre na sua conta</p>
        </div>
        
        <?php if (!empty($mensagem)): ?>
            <div class="error-message">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="login">E-mail</label>
                <div class="input-wrapper">
                    <span class="fas fa-user input-icon"></span>
                    <input type="text" name="login" id="login" class="form-input" 
                           placeholder="seu@email.com" required 
                           value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha</label>
                <div class="input-wrapper">
                    <span class="fas fa-lock input-icon"></span>
                    <input type="password" name="senha" id="senha" class="form-input" 
                           placeholder="••••••••" required>
                    <span class="fas fa-eye password-toggle" id="togglePassword"></span>
                </div>
            </div>
            
            <button type="submit" class="login-button">Entrar</button>
        </form>
        
        <div class="signup-link">
            Não tem uma conta? <a href="cadastro.php">Cadastre-se</a>
        </div>
    </div>

    <script>
        // Toggle senha
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const passwordField = document.getElementById('senha');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁' : '🙈';
        });
        
        // Auto-focus no primeiro campo
        document.getElementById('login')?.focus();
    </script>
</body>
</html>