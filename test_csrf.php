<?php
session_start();
require_once 'includes/CSRF.php';

echo "<h1>🔒 Teste CSRF</h1>\n";
echo "<pre>\n";

echo "1. Gerando novo token...\n";
$token = CSRF::generateToken();
echo "   Token: " . substr($token, 0, 20) . "...\n";

echo "\n2. Testando verificação do token...\n";
$_POST['csrf_token'] = $token;
$_SERVER['REQUEST_METHOD'] = 'POST';

$isValid = CSRF::verifyPostToken();
echo "   Válido: " . ($isValid ? 'SIM' : 'NÃO') . "\n";

echo "\n3. Testando token inválido...\n";
$_POST['csrf_token'] = 'token_invalido';
$isInvalid = CSRF::verifyPostToken();
echo "   Válido: " . ($isInvalid ? 'SIM' : 'NÃO') . "\n";

echo "\n4. Tokens na sessão:\n";
if (isset($_SESSION['csrf_tokens'])) {
    echo "   Total: " . count($_SESSION['csrf_tokens']) . "\n";
    foreach ($_SESSION['csrf_tokens'] as $t => $time) {
        echo "   " . substr($t, 0, 10) . "... => " . date('H:i:s', $time) . "\n";
    }
} else {
    echo "   Nenhum token na sessão\n";
}

echo "\n</pre>\n";

echo "<h2>Formulário de Teste</h2>\n";
echo "<form method='post'>\n";
echo CSRF::getHiddenField();
echo "<input type='text' name='nome' placeholder='Nome' required><br><br>\n";
echo "<button type='submit'>Testar Envio</button>\n";
echo "</form>\n";

if ($_POST && isset($_POST['nome'])) {
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0;'>\n";
    if (CSRF::verifyPostToken()) {
        echo "✅ CSRF válido! Nome enviado: " . htmlspecialchars($_POST['nome']);
    } else {
        echo "❌ CSRF inválido!";
    }
    echo "</div>\n";
}
?>