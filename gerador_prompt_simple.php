<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Prompts - Teste</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .user-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success">
            ✅ <strong>SUCESSO!</strong> Login funcionando perfeitamente!
        </div>
        
        <div class="user-info">
            <h3>Informações do Usuário:</h3>
            <p><strong>ID:</strong> <?= htmlspecialchars($_SESSION['usuario_id']) ?></p>
            <p><strong>Nome:</strong> <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'N/A') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['usuario_email'] ?? 'N/A') ?></p>
        </div>
        
        <h1>🎨 Gerador de Prompts para IA</h1>
        <p>Bem-vindo ao sistema! O login e redirecionamento estão funcionando corretamente.</p>
        
        <h2>Teste do Sistema:</h2>
        <ul>
            <li>✅ Login realizado com sucesso</li>
            <li>✅ Sessão criada corretamente</li>
            <li>✅ Redirecionamento funcionando</li>
            <li>✅ Página acessível</li>
        </ul>
        
        <h2>Links de Teste:</h2>
        <ul>
            <li><a href="gerador_prompt.php">Tentar Gerador Original</a></li>
            <li><a href="auth/logout.php">Fazer Logout</a></li>
            <li><a href="index.php">Página Inicial</a></li>
        </ul>
        
        <h2>Debug da Sessão:</h2>
        <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; overflow: auto;">
<?php print_r($_SESSION); ?>
        </pre>
    </div>
</body>
</html>