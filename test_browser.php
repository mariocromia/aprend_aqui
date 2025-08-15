<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste de Email no Navegador</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Teste de Email via Navegador</h1>
    
    <?php
    if (isset($_POST['test_email'])) {
        require_once 'includes/SupabaseClient.php';
        require_once 'includes/EmailManager.php';
        
        $email = 'mariocromia@gmail.com';
        
        echo "<h2>Resultado do Teste</h2>";
        
        try {
            $supabase = new SupabaseClient();
            $user = $supabase->getUserByEmail($email);
            
            if ($user) {
                echo "<p>✅ Usuário encontrado: " . $user['nome'] . "</p>";
                
                $code = EmailManager::generateResetCode($email, 'email');
                if ($code) {
                    echo "<p>✅ Código gerado: $code</p>";
                    
                    $emailSent = EmailManager::sendRecoveryCodeByEmail($email, $code, $user['nome']);
                    if ($emailSent) {
                        echo "<p style='color: green;'><strong>✅ Email enviado com SUCESSO!</strong></p>";
                        echo "<p>Verifique sua caixa de entrada em: $email</p>";
                    } else {
                        echo "<p style='color: red;'><strong>❌ Falha no envio do email</strong></p>";
                        echo "<p>Este é o mesmo erro que você vê no sistema.</p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ Erro ao gerar código</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Usuário não encontrado</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Exceção: " . $e->getMessage() . "</p>";
        }
        
        echo "<hr>";
    }
    ?>
    
    <form method="post">
        <button type="submit" name="test_email" style="padding: 10px 20px; font-size: 16px;">
            🧪 Testar Envio de Email
        </button>
    </form>
    
    <hr>
    <h2>Instruções</h2>
    <ol>
        <li>Clique no botão acima para testar</li>
        <li>Se funcionar aqui mas falhar no login, pode ser cache do navegador</li>
        <li>Tente limpar o cache do navegador (Ctrl+Shift+R)</li>
        <li>Ou teste em aba anônima/privada</li>
    </ol>
</body>
</html>