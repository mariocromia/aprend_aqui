<?php
/**
 * Configurações da aplicação
 */

return [
    'name' => 'Prompt Builder IA',
    'version' => '1.0.0',
    'env' => 'development',
    'debug' => true,
    'timezone' => 'America/Sao_Paulo',
    
    'paths' => [
        'root' => dirname(__DIR__),
        'includes' => dirname(__DIR__) . '/includes',
        'auth' => dirname(__DIR__) . '/auth',
        'assets' => dirname(__DIR__) . '/assets',
        'temp' => dirname(__DIR__) . '/temp'
    ],
    
    'session' => [
        'lifetime' => 3600,
        'name' => 'APREND_AQUI_SESSION'
    ]
];
?>