<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

header('Content-Type: application/json');

require_once '../includes/PromptManager.php';

try {
    $promptManager = new PromptManager();
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'categories':
            $data = $promptManager->getAICategories();
            break;
            
        case 'styles':
            $categoryId = $_GET['category_id'] ?? null;
            $data = $promptManager->getArtStyles($categoryId);
            break;
            
        case 'aspect_ratios':
            $data = $promptManager->getAspectRatios();
            break;
            
        case 'templates':
            $categoryId = $_GET['category_id'] ?? null;
            $difficulty = $_GET['difficulty'] ?? null;
            $data = $promptManager->getPromptTemplates($categoryId, $difficulty);
            break;
            
        case 'user_prompts':
            $limit = $_GET['limit'] ?? 20;
            $offset = $_GET['offset'] ?? 0;
            $data = $promptManager->getUserPrompts($_SESSION['usuario_id'], $limit, $offset);
            break;
            
        case 'enhance_prompt':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $originalPrompt = $input['prompt'] ?? '';
            $settings = $input['settings'] ?? [];
            
            $enhancedPrompt = $promptManager->enhancePrompt($originalPrompt, $settings);
            $data = ['enhanced_prompt' => $enhancedPrompt];
            break;
            
        default:
            throw new Exception('Ação não reconhecida');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>