<?php
/**
 * API Endpoint para carregamento assíncrono de conteúdo de abas
 * Otimiza o carregamento inicial mostrando apenas o conteúdo necessário
 */

session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autorizado']);
    exit;
}

// Headers para API
header('Content-Type: application/json');
header('Cache-Control: public, max-age=300'); // Cache de 5 minutos

try {
    // Carregar dependências necessárias
    require_once '../includes/Environment.php';
    require_once '../includes/CenaManager.php';
    require_once '../includes/CenaRendererPrompt.php';
    require_once '../includes/DatabaseOptimizer.php';

    $tab = $_GET['tab'] ?? '';
    
    // Validar aba solicitada
    $validTabs = ['ambiente', 'estilo_visual', 'iluminacao', 'tecnica', 'elementos_especiais'];
    if (!in_array($tab, $validTabs)) {
        echo json_encode(['success' => false, 'message' => 'Aba inválida']);
        exit;
    }

    // Inicializar sistema de cenas com otimizações
    $cenaManager = new CenaManager();
    $cenaRenderer = new CenaRendererPrompt($cenaManager);
    
    // Renderizar conteúdo específico da aba
    $html = '';
    $cacheKey = "tab_content_{$tab}_" . ($_SESSION['usuario_id'] ?? 'guest');
    
    // Tentar cache primeiro
    $optimizer = DatabaseOptimizer::getInstance();
    $cachedContent = $optimizer->getCachedQuery($cacheKey);
    
    if ($cachedContent) {
        $html = $cachedContent;
    } else {
        // Gerar conteúdo
        switch ($tab) {
            case 'ambiente':
                $html = $cenaRenderer->renderizarAbaAmbiente();
                break;
            case 'estilo_visual':
                $html = $cenaRenderer->renderizarAbaEstiloVisual();
                break;
            case 'iluminacao':
                $html = $cenaRenderer->renderizarAbaIluminacao();
                break;
            case 'tecnica':
                $html = $cenaRenderer->renderizarAbaTecnica();
                break;
            case 'elementos_especiais':
                $html = $cenaRenderer->renderizarAbaElementosEspeciais();
                break;
            default:
                throw new Exception('Aba não suportada');
        }
        
        // Cachear resultado por 10 minutos
        $optimizer->cacheQuery($cacheKey, $html, 600);
    }
    
    // Remover espaços em branco extras para reduzir tamanho
    $html = trim(preg_replace('/\s+/', ' ', $html));
    
    // Estatísticas de performance
    $stats = $optimizer->getCacheStats();
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'tab' => $tab,
        'cached' => $cachedContent !== null,
        'stats' => [
            'cache_hits' => $stats['hits'],
            'cache_misses' => $stats['misses'],
            'hit_rate' => $stats['hit_rate']
        ],
        'timestamp' => time()
    ]);

} catch (Exception $e) {
    error_log("API load_tab_content error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor',
        'error' => $e->getMessage()
    ]);
}
?>