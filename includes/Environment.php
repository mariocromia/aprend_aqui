<?php
/**
 * Classe para gerenciar variáveis de ambiente
 * Carrega configurações do arquivo env.config
 */
class Environment {
    private static $config = null;
    
    /**
     * Carrega as configurações do arquivo env.config
     */
    public static function load() {
        if (self::$config === null) {
            $envFile = __DIR__ . '/../env.config';
            
            // Se o arquivo não existir, usar valores padrão
            if (!file_exists($envFile)) {
                self::$config = self::getDefaultConfig();
                return self::$config;
            }
            
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            self::$config = [];
            
            foreach ($lines as $line) {
                // Ignorar comentários
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                // Processar linha de configuração
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remover aspas se existirem
                    if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                        (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                        $value = substr($value, 1, -1);
                    }
                    
                    self::$config[$key] = $value;
                }
            }
            
            // Mesclar com configurações padrão
            self::$config = array_merge(self::getDefaultConfig(), self::$config);
        }
        
        return self::$config;
    }
    
    /**
     * Configurações padrão quando não há arquivo env.config
     */
    private static function getDefaultConfig() {
        return [
            'APP_NAME' => 'Prompt Builder IA',
            'APP_VERSION' => '1.0.0',
            'HOME_URL' => 'http://localhost/aprend_aqui',
            'SMTP_HOST' => '',
            'SMTP_PORT' => '587',
            'SMTP_USERNAME' => '',
            'SMTP_PASSWORD' => '',
            'SMTP_FROM_EMAIL' => '',
            'SMTP_FROM_NAME' => 'Prompt Builder IA',
            'DATABASE_URL' => '',
            'SESSION_LIFETIME' => '3600'
        ];
    }
    
    /**
     * Obtém uma variável de ambiente
     */
    public static function get($key, $default = null) {
        $config = self::load();
        return $config[$key] ?? $default;
    }
    
    /**
     * Define uma variável de ambiente
     */
    public static function set($key, $value) {
        self::load();
        self::$config[$key] = $value;
    }
    
    /**
     * Verifica se uma variável existe
     */
    public static function has($key) {
        $config = self::load();
        return isset($config[$key]);
    }
}
?>
