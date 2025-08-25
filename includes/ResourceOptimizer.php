<?php
/**
 * Resource Optimizer - Compressão e Otimização de Recursos
 * 
 * Implementa compressão GZIP, minificação e cache headers
 * para otimizar a entrega de recursos.
 */

class ResourceOptimizer {
    private static $instance = null;
    
    private function __construct() {
        // Singleton pattern
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Ativa compressão GZIP para toda a aplicação
     */
    public static function enableGzipCompression() {
        if (!headers_sent() && !ob_get_level()) {
            // Verificar se GZIP é suportado pelo cliente
            if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && 
                strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
                
                // Ativar buffer de saída com compressão GZIP
                if (function_exists('ob_gzhandler')) {
                    ob_start('ob_gzhandler');
                } else {
                    ob_start();
                }
            }
        }
    }
    
    /**
     * Define headers de cache otimizados
     */
    public static function setCacheHeaders($type = 'static', $maxAge = 3600) {
        if (headers_sent()) return;
        
        switch ($type) {
            case 'static': // CSS, JS, imagens
                header('Cache-Control: public, max-age=' . $maxAge);
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');
                header('Pragma: cache');
                break;
                
            case 'dynamic': // Conteúdo dinâmico mas cacheável
                header('Cache-Control: public, max-age=' . $maxAge . ', must-revalidate');
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');
                break;
                
            case 'no-cache': // Conteúdo sempre atualizado
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                break;
        }
        
        // ETag para validação de cache
        $etag = md5($_SERVER['REQUEST_URI'] . filemtime(__FILE__));
        header("ETag: \"$etag\"");
        
        // Verificar If-None-Match para 304 Not Modified
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && 
            $_SERVER['HTTP_IF_NONE_MATCH'] === "\"$etag\"") {
            http_response_code(304);
            exit;
        }
    }
    
    /**
     * Minifica CSS removendo espaços e comentários desnecessários
     */
    public static function minifyCSS($css) {
        // Remover comentários
        $css = preg_replace('/\/\*.*?\*\//s', '', $css);
        
        // Remover espaços em branco extras
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Remover espaços ao redor de caracteres especiais
        $css = str_replace([' {', '{ ', ' }', '} ', ' ;', '; ', ' :', ': ', ' ,', ', '], 
                          ['{', '{', '}', '}', ';', ';', ':', ':', ',', ','], $css);
        
        // Remover ponto e vírgula antes de }
        $css = str_replace(';}', '}', $css);
        
        return trim($css);
    }
    
    /**
     * Minifica JavaScript básico
     */
    public static function minifyJS($js) {
        // Remover comentários de linha única
        $js = preg_replace('/\/\/.*$/m', '', $js);
        
        // Remover comentários de múltiplas linhas
        $js = preg_replace('/\/\*.*?\*\//s', '', $js);
        
        // Remover espaços em branco extras (mas preservar quebras de linha importantes)
        $js = preg_replace('/\s+/', ' ', $js);
        
        // Remover espaços ao redor de caracteres especiais
        $js = str_replace([' {', '{ ', ' }', '} ', ' ;', '; ', ' =', '= ', ' +', '+ ', ' -', '- '], 
                         ['{', '{', '}', '}', ';', ';', '=', '=', '+', '+', '-', '-'], $js);
        
        return trim($js);
    }
    
    /**
     * Combina múltiplos arquivos CSS em um só
     */
    public static function combineCSS($files, $outputPath) {
        $combined = '';
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $css = file_get_contents($file);
                $combined .= "/* From: $file */\n" . $css . "\n\n";
            }
        }
        
        // Minificar CSS combinado
        $combined = self::minifyCSS($combined);
        
        // Salvar arquivo combinado
        if (file_put_contents($outputPath, $combined)) {
            return $outputPath;
        }
        
        return false;
    }
    
    /**
     * Combina múltiplos arquivos JavaScript em um só
     */
    public static function combineJS($files, $outputPath) {
        $combined = '';
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $js = file_get_contents($file);
                $combined .= "/* From: $file */\n" . $js . "\n\n";
            }
        }
        
        // Minificar JavaScript combinado
        $combined = self::minifyJS($combined);
        
        // Salvar arquivo combinado
        if (file_put_contents($outputPath, $combined)) {
            return $outputPath;
        }
        
        return false;
    }
    
    /**
     * Otimiza imagens (redimensionamento básico)
     */
    public static function optimizeImage($inputPath, $outputPath, $maxWidth = 1200, $quality = 85) {
        if (!file_exists($inputPath)) return false;
        
        $imageInfo = getimagesize($inputPath);
        if (!$imageInfo) return false;
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo[2];
        
        // Se a imagem já é menor que o máximo, apenas copiar
        if ($width <= $maxWidth) {
            return copy($inputPath, $outputPath);
        }
        
        // Calcular novas dimensões
        $newWidth = $maxWidth;
        $newHeight = round($height * ($maxWidth / $width));
        
        // Criar imagem a partir do arquivo original
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($inputPath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($inputPath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($inputPath);
                break;
            default:
                return false;
        }
        
        // Criar nova imagem redimensionada
        $destination = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preservar transparência para PNG
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
        }
        
        // Redimensionar
        imagecopyresampled($destination, $source, 0, 0, 0, 0, 
                          $newWidth, $newHeight, $width, $height);
        
        // Salvar imagem otimizada
        $result = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($destination, $outputPath, $quality);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($destination, $outputPath, 9);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($destination, $outputPath);
                break;
        }
        
        // Limpar memória
        imagedestroy($source);
        imagedestroy($destination);
        
        return $result;
    }
    
    /**
     * Gera um manifesto de cache para service worker
     */
    public static function generateCacheManifest($files, $version = null) {
        $version = $version ?: time();
        
        $manifest = [
            'version' => $version,
            'files' => []
        ];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $manifest['files'][] = [
                    'url' => $file,
                    'hash' => md5_file($file),
                    'size' => filesize($file),
                    'modified' => filemtime($file)
                ];
            }
        }
        
        return json_encode($manifest, JSON_PRETTY_PRINT);
    }
    
    /**
     * Inicia otimização automática da página
     */
    public static function startPageOptimization() {
        // Ativar compressão GZIP
        self::enableGzipCompression();
        
        // Definir headers de cache baseado no tipo de conteúdo
        $uri = $_SERVER['REQUEST_URI'];
        
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg)$/i', $uri)) {
            // Recursos estáticos - cache longo
            self::setCacheHeaders('static', 86400); // 24 horas
        } elseif (preg_match('/\.(php|html)$/i', $uri) || !pathinfo($uri, PATHINFO_EXTENSION)) {
            // Conteúdo dinâmico - cache curto
            self::setCacheHeaders('dynamic', 300); // 5 minutos
        }
    }
    
    /**
     * Finaliza otimização e flush do buffer
     */
    public static function endPageOptimization() {
        if (ob_get_level()) {
            ob_end_flush();
        }
    }
}

// Ativar otimização automática se não for linha de comando
if (php_sapi_name() !== 'cli') {
    ResourceOptimizer::startPageOptimization();
}
?>