<?php
/**
 * Classe para proteção CSRF
 * Implementa tokens CSRF para prevenir ataques de falsificação de requisições
 */
class CSRF {
    private static $tokenName = 'csrf_token';
    
    /**
     * Gera um token CSRF único
     */
    public static function generateToken() {
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][$token] = time();
        
        // Limpar tokens antigos (mais de 1 hora)
        self::cleanOldTokens();
        
        return $token;
    }
    
    /**
     * Verifica se um token CSRF é válido
     */
    public static function verifyToken($token) {
        if (!isset($_SESSION['csrf_tokens']) || !isset($_SESSION['csrf_tokens'][$token])) {
            return false;
        }
        
        // Verificar se o token não expirou (1 hora)
        if (time() - $_SESSION['csrf_tokens'][$token] > 3600) {
            unset($_SESSION['csrf_tokens'][$token]);
            return false;
        }
        
        // Permitir reutilização do token por 5 minutos após criação
        $tokenAge = time() - $_SESSION['csrf_tokens'][$token];
        if ($tokenAge > 300) { // 5 minutos
            unset($_SESSION['csrf_tokens'][$token]);
        }
        
        return true;
    }
    
    /**
     * Gera um campo hidden com token CSRF
     */
    public static function getHiddenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="' . self::$tokenName . '" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Verifica token em requisição POST
     */
    public static function verifyPostToken() {
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true; // Apenas verificar em POST
        }
        
        $token = $_POST[self::$tokenName] ?? $_GET[self::$tokenName] ?? null;
        
        if (!$token) {
            return false;
        }
        
        return self::verifyToken($token);
    }
    
    /**
     * Verifica token em requisição AJAX
     */
    public static function verifyAjaxToken() {
        $headers = getallheaders();
        $token = $headers['X-CSRF-Token'] ?? $_POST[self::$tokenName] ?? null;
        
        if (!$token) {
            return false;
        }
        
        return self::verifyToken($token);
    }
    
    /**
     * Limpa tokens antigos
     */
    private static function cleanOldTokens() {
        if (!isset($_SESSION['csrf_tokens'])) {
            return;
        }
        
        $currentTime = time();
        foreach ($_SESSION['csrf_tokens'] as $token => $timestamp) {
            if ($currentTime - $timestamp > 3600) {
                unset($_SESSION['csrf_tokens'][$token]);
            }
        }
    }
    
    /**
     * Gera resposta de erro CSRF
     */
    public static function errorResponse() {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Token CSRF inválido ou expirado',
            'message' => 'Erro de segurança detectado'
        ]);
        exit;
    }
}
?>
