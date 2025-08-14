<?php
session_start();

// Se o usuário já está logado, redirecionar para o gerador
if (isset($_SESSION['usuario_id'])) {
    header('Location: gerador_prompt.php');
    exit;
}

// Caso contrário, redirecionar para login
header('Location: auth/login.php');
exit;
?>
