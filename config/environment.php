<?php
/**
 * Configuração de Ambiente - CentroService
 * 
 * Este arquivo contém configurações específicas para diferentes ambientes
 * (desenvolvimento, produção, teste) e variáveis globais do sistema.
 */

// Detectar ambiente automaticamente
if (!defined('ENVIRONMENT')) {
    $hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    if (in_array($hostname, ['localhost', '127.0.0.1', '::1']) || 
        strpos($hostname, '.local') !== false || 
        strpos($hostname, '.test') !== false) {
        define('ENVIRONMENT', 'development');
    } elseif (strpos($hostname, 'staging') !== false || 
              strpos($hostname, 'test') !== false) {
        define('ENVIRONMENT', 'staging');
    } else {
        define('ENVIRONMENT', 'production');
    }
}

// Configurações baseadas no ambiente
switch (ENVIRONMENT) {
    case 'development':
        // Desenvolvimento local
        define('DEBUG_MODE', true);
        define('ERROR_REPORTING', E_ALL);
        define('DISPLAY_ERRORS', true);
        define('LOG_LEVEL', 'DEBUG');
        define('CACHE_ENABLED', false);
        define('COMPRESSION_ENABLED', false);
        define('SSL_REQUIRED', false);
        break;
        
    case 'staging':
        // Ambiente de teste/staging
        define('DEBUG_MODE', true);
        define('ERROR_REPORTING', E_ALL & ~E_DEPRECATED & ~E_STRICT);
        define('DISPLAY_ERRORS', false);
        define('LOG_LEVEL', 'INFO');
        define('CACHE_ENABLED', true);
        define('COMPRESSION_ENABLED', true);
        define('SSL_REQUIRED', false);
        break;
        
    case 'production':
        // Produção
        define('DEBUG_MODE', false);
        define('ERROR_REPORTING', E_ERROR | E_WARNING | E_PARSE);
        define('DISPLAY_ERRORS', false);
        define('LOG_LEVEL', 'ERROR');
        define('CACHE_ENABLED', true);
        define('COMPRESSION_ENABLED', true);
        define('SSL_REQUIRED', true);
        break;
        
    default:
        // Fallback para desenvolvimento
        define('DEBUG_MODE', true);
        define('ERROR_REPORTING', E_ALL);
        define('DISPLAY_ERRORS', true);
        define('LOG_LEVEL', 'DEBUG');
        define('CACHE_ENABLED', false);
        define('COMPRESSION_ENABLED', false);
        define('SSL_REQUIRED', false);
        break;
}

// Configurações de erro e logging
error_reporting(ERROR_REPORTING);
ini_set('display_errors', DISPLAY_ERRORS ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de sessão
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', SSL_REQUIRED ? '1' : '0');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', '3600');
ini_set('session.cookie_lifetime', '0');

// Configurações de memória e tempo
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');
ini_set('post_max_size', '16M');
ini_set('upload_max_filesize', '8M');

// Configurações de cache
if (CACHE_ENABLED) {
    ini_set('opcache.enable', '1');
    ini_set('opcache.enable_cli', '1');
    ini_set('opcache.memory_consumption', '128');
    ini_set('opcache.interned_strings_buffer', '8');
    ini_set('opcache.max_accelerated_files', '4000');
    ini_set('opcache.revalidate_freq', '2');
    ini_set('opcache.fast_shutdown', '1');
} else {
    ini_set('opcache.enable', '0');
}

// Configurações de compressão
if (COMPRESSION_ENABLED) {
    ini_set('zlib.output_compression', '1');
    ini_set('zlib.output_compression_level', '6');
}

// Configurações de segurança
ini_set('allow_url_fopen', '0');
ini_set('allow_url_include', '0');
ini_set('expose_php', '0');
ini_set('max_input_vars', '1000');

// Configurações de email
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', '587');
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_FROM_EMAIL', 'noreply@centroservice.com.br');
define('SMTP_FROM_NAME', 'CentroService');

// Configurações de contato
define('CONTACT_EMAIL', 'contato@centroservice.com.br');
define('SUPPORT_EMAIL', 'suporte@centroservice.com.br');
define('SALES_EMAIL', 'vendas@centroservice.com.br');
define('PHONE_NUMBER', '+55 (11) 99999-9999');
define('WHATSAPP_NUMBER', '+5511999999999');
define('ADDRESS', 'São Paulo, SP - Brasil');
define('BUSINESS_HOURS', 'Segunda a Sexta: 9h às 18h');

// Configurações de redes sociais
define('FACEBOOK_URL', 'https://facebook.com/centroservice');
define('INSTAGRAM_URL', 'https://instagram.com/centroservice');
define('LINKEDIN_URL', 'https://linkedin.com/company/centroservice');
define('YOUTUBE_URL', 'https://youtube.com/centroservice');
define('TWITTER_URL', 'https://twitter.com/centroservice');

// Configurações de analytics e tracking
define('GOOGLE_ANALYTICS_ID', '');
define('FACEBOOK_PIXEL_ID', '');
define('GOOGLE_TAG_MANAGER_ID', '');
define('HOTJAR_ID', '');

// Configurações de SEO
define('SITE_NAME', 'CentroService');
define('SITE_DESCRIPTION', 'Criamos vídeos institucionais, VSL, Reels e conteúdo para redes sociais usando Inteligência Artificial. Transforme sua marca com vídeos profissionais!');
define('SITE_KEYWORDS', 'vídeos institucionais, VSL, reels, redes sociais, IA, inteligência artificial, marketing digital');
define('SITE_AUTHOR', 'CentroService');
define('SITE_LANGUAGE', 'pt-BR');
define('SITE_CHARSET', 'UTF-8');

// Configurações de URLs
define('SITE_URL', 'https://centroservice.com.br');
define('SITE_URL_WWW', 'https://www.centroservice.com.br');
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_URL', SITE_URL . '/uploads');
define('API_URL', SITE_URL . '/api');

// Configurações de diretórios
define('ROOT_PATH', dirname(__DIR__));
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('CACHE_PATH', ROOT_PATH . '/cache');
define('TEMP_PATH', ROOT_PATH . '/temp');

// Configurações de cache
define('CACHE_DURATION', 3600); // 1 hora
define('CACHE_PREFIX', 'centroservice_');
define('CACHE_DRIVER', 'file'); // file, redis, memcached

// Configurações de rate limiting
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_MAX_REQUESTS', 100); // por hora
define('RATE_LIMIT_WINDOW', 3600); // em segundos

// Configurações de backup
define('BACKUP_ENABLED', true);
define('BACKUP_RETENTION_DAYS', 30);
define('BACKUP_PATH', ROOT_PATH . '/backups');

// Configurações de monitoramento
define('MONITORING_ENABLED', true);
define('UPTIME_MONITOR_URL', '');
define('ERROR_TRACKING_SERVICE', ''); // Sentry, Bugsnag, etc.

// Configurações de desenvolvimento
define('DEV_TOOLS_ENABLED', DEBUG_MODE);
define('PROFILER_ENABLED', DEBUG_MODE);
define('DEBUG_BAR_ENABLED', DEBUG_MODE);

// Configurações de teste
define('TESTING_ENABLED', ENVIRONMENT === 'development' || ENVIRONMENT === 'staging');
define('TEST_DATABASE', 'centroservice_test');
define('TEST_EMAIL_ENABLED', false);

// Configurações de manutenção
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_ALLOWED_IPS', ['127.0.0.1', '::1']);
define('MAINTENANCE_MESSAGE', 'Site em manutenção. Volte em breve!');

// Configurações de CDN
define('CDN_ENABLED', ENVIRONMENT === 'production');
define('CDN_URL', 'https://cdn.centroservice.com.br');
define('CDN_FALLBACK', true);

// Configurações de compressão de imagens
define('IMAGE_OPTIMIZATION_ENABLED', true);
define('IMAGE_QUALITY', 85);
define('IMAGE_MAX_WIDTH', 1920);
define('IMAGE_MAX_HEIGHT', 1080);
define('WEBP_CONVERSION_ENABLED', true);

// Configurações de vídeo
define('VIDEO_MAX_SIZE', '100MB');
define('VIDEO_ALLOWED_FORMATS', ['mp4', 'webm', 'ogg', 'avi', 'mov']);
define('VIDEO_COMPRESSION_ENABLED', true);

// Configurações de segurança adicional
define('CSRF_PROTECTION_ENABLED', true);
define('XSS_PROTECTION_ENABLED', true);
define('SQL_INJECTION_PROTECTION_ENABLED', true);
define('FILE_UPLOAD_VALIDATION_ENABLED', true);

// Configurações de logging
define('LOG_ROTATION_ENABLED', true);
define('LOG_MAX_SIZE', '10MB');
define('LOG_MAX_FILES', 10);

// Configurações de performance
define('MINIFICATION_ENABLED', ENVIRONMENT === 'production');
define('CONCATENATION_ENABLED', ENVIRONMENT === 'production');
define('LAZY_LOADING_ENABLED', true);
define('PRELOAD_CRITICAL_RESOURCES', true);

// Configurações de acessibilidade
define('ACCESSIBILITY_ENABLED', true);
define('SCREEN_READER_SUPPORT', true);
define('KEYBOARD_NAVIGATION_ENABLED', true);
define('HIGH_CONTRAST_MODE', false);

// Configurações de internacionalização
define('I18N_ENABLED', false);
define('DEFAULT_LOCALE', 'pt_BR');
define('AVAILABLE_LOCALES', ['pt_BR', 'en_US', 'es_ES']);

// Configurações de API
define('API_ENABLED', false);
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 1000); // por hora
define('API_AUTHENTICATION_REQUIRED', true);

// Configurações de webhook
define('WEBHOOK_ENABLED', false);
define('WEBHOOK_SECRET', '');
define('WEBHOOK_TIMEOUT', 30);

// Configurações de notificação
define('EMAIL_NOTIFICATIONS_ENABLED', true);
define('SMS_NOTIFICATIONS_ENABLED', false);
define('PUSH_NOTIFICATIONS_ENABLED', false);
define('WHATSAPP_NOTIFICATIONS_ENABLED', false);

// Configurações de pagamento (se aplicável)
define('PAYMENT_ENABLED', false);
define('PAYMENT_GATEWAY', ''); // Stripe, PayPal, etc.
define('PAYMENT_TEST_MODE', ENVIRONMENT !== 'production');

// Configurações de integração
define('CRM_INTEGRATION_ENABLED', false);
define('EMAIL_MARKETING_INTEGRATION_ENABLED', false);
define('SOCIAL_MEDIA_INTEGRATION_ENABLED', false);

// Função para obter configuração
function getConfig($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

// Função para verificar se é ambiente de produção
function isProduction() {
    return ENVIRONMENT === 'production';
}

// Função para verificar se é ambiente de desenvolvimento
function isDevelopment() {
    return ENVIRONMENT === 'development';
}

// Função para verificar se é ambiente de staging
function isStaging() {
    return ENVIRONMENT === 'staging';
}

// Função para obter URL base
function getBaseUrl() {
    return SITE_URL;
}

// Função para obter URL de assets
function getAssetUrl($path) {
    return ASSETS_URL . '/' . ltrim($path, '/');
}

// Função para obter caminho absoluto
function getAbsolutePath($path) {
    return ROOT_PATH . '/' . ltrim($path, '/');
}

// Função para verificar se está em modo de manutenção
function isMaintenanceMode() {
    if (!MAINTENANCE_MODE) {
        return false;
    }
    
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
    return !in_array($clientIP, MAINTENANCE_ALLOWED_IPS);
}

// Função para verificar se deve usar HTTPS
function requiresSSL() {
    return SSL_REQUIRED || isProduction();
}

// Função para obter configuração de ambiente
function getEnvironment() {
    return ENVIRONMENT;
}

// Função para debug (apenas em desenvolvimento)
function debug($data, $exit = false) {
    if (!DEBUG_MODE) {
        return;
    }
    
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    
    if ($exit) {
        exit;
    }
}

// Função para log personalizado
function logMessage($message, $level = 'INFO', $context = []) {
    if (!is_dir(LOGS_PATH)) {
        mkdir(LOGS_PATH, 0755, true);
    }
    
    $logFile = LOGS_PATH . '/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logMessage = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Função para limpar cache
function clearCache() {
    if (is_dir(CACHE_PATH)) {
        $files = glob(CACHE_PATH . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

// Função para obter informações do sistema
function getSystemInfo() {
    return [
        'environment' => ENVIRONMENT,
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'timezone' => date_default_timezone_get(),
        'debug_mode' => DEBUG_MODE,
        'cache_enabled' => CACHE_ENABLED,
        'compression_enabled' => COMPRESSION_ENABLED
    ];
}

// Configurações de cabeçalhos de segurança
if (function_exists('header')) {
    // Headers de segurança básicos
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // HSTS apenas em produção com SSL
    if (requiresSSL()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
    
    // Permissions Policy
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// Configurações de sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuração de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configuração de locale
setlocale(LC_ALL, 'pt_BR.UTF-8', 'pt_BR', 'Portuguese_Brazil');

// Log de inicialização
if (DEBUG_MODE) {
    logMessage('Sistema inicializado', 'INFO', [
        'environment' => ENVIRONMENT,
        'timestamp' => date('Y-m-d H:i:s'),
        'memory_usage' => memory_get_usage(true)
    ]);
}

// Verificação de manutenção
if (isMaintenanceMode()) {
    http_response_code(503);
    include ROOT_PATH . '/maintenance.php';
    exit;
}

// Verificação de SSL
if (requiresSSL() && !isset($_SERVER['HTTPS'])) {
    $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: {$redirectUrl}", true, 301);
    exit;
}
?>
