<?php
/**
 * Configurações de Segurança do Sistema
 * Implementa configurações seguras para sessões e cookies
 */
class SecurityConfig {
    
    /**
     * Configurar sessões seguras
     */
    public static function configureSecureSessions() {
        // Configurações apenas se sessão ainda não foi iniciada
        if (session_status() === PHP_SESSION_NONE) {
            
            // Configurações de segurança de sessão
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_secure', '1');
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_lifetime', '0');
            ini_set('session.gc_maxlifetime', '3600'); // 1 hora
            ini_set('session.gc_probability', '1');
            ini_set('session.gc_divisor', '100');
            
            // Nome de sessão personalizado
            session_name('APRENDAQUI_SESSID');
            
            // Configurar path de sessão se possível
            if (is_writable(sys_get_temp_dir())) {
                session_save_path(sys_get_temp_dir());
            }
        }
    }
    
    /**
     * Verificar se conexão é HTTPS
     */
    public static function requireHTTPS() {
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            if (Environment::get('FORCE_HTTPS', 'false') === 'true') {
                $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                header('Location: ' . $redirectURL, true, 301);
                exit();
            }
        }
    }
    
    /**
     * Configurar headers de segurança via PHP
     */
    public static function setSecurityHeaders() {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            
            // CSP simples para desenvolvimento
            header("Content-Security-Policy: default-src 'self' 'unsafe-inline' cdnjs.cloudflare.com");
        }
    }
    
    /**
     * Validar origem da requisição
     */
    public static function validateOrigin() {
        $allowedHosts = [
            'localhost',
            '127.0.0.1',
            'centroservice.com.br',
            'www.centroservice.com.br'
        ];
        
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            if (!in_array($host, $allowedHosts)) {
                error_log("SecurityConfig: Host não autorizado: $host");
                // Em produção, bloquear. Em desenvolvimento, apenas log
                if (Environment::get('APP_ENV', 'development') === 'production') {
                    http_response_code(403);
                    exit('Acesso negado');
                }
            }
        }
    }
    
    /**
     * Configurar limite de rate limiting básico
     */
    public static function rateLimitCheck($action = 'general', $maxAttempts = 30, $timeWindow = 3600) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit_{$action}_{$ip}";
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 1,
                'first_attempt' => time()
            ];
            return true;
        }
        
        $data = $_SESSION[$key];
        $timeElapsed = time() - $data['first_attempt'];
        
        // Reset contador se janela de tempo passou
        if ($timeElapsed > $timeWindow) {
            $_SESSION[$key] = [
                'count' => 1,
                'first_attempt' => time()
            ];
            return true;
        }
        
        // Incrementar contador
        $_SESSION[$key]['count']++;
        
        // Verificar se excedeu limite
        if ($data['count'] >= $maxAttempts) {
            error_log("SecurityConfig: Rate limit excedido para IP $ip na ação $action");
            return false;
        }
        
        return true;
    }
}
?>