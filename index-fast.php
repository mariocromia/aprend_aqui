<?php
session_start();

// Redirecionamento rápido baseado em sessão
if (isset($_SESSION['usuario_id'])) {
    header('Location: gerador_prompt2.php');
    exit;
} else {
    header('Location: auth/login-fast.php');
    exit;
}
?>