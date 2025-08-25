<?php
/**
 * API para gerenciamento de avatares
 * Suporta operações CRUD para avatares e pastas
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

require_once '../includes/AvatarManager.php';

$avatarManager = new AvatarManager($_SESSION['usuario_id']);
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGet($avatarManager, $action);
            break;
        case 'POST':
            handlePost($avatarManager, $action);
            break;
        case 'PUT':
            handlePut($avatarManager, $action);
            break;
        case 'DELETE':
            handleDelete($avatarManager, $action);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno: ' . $e->getMessage()]);
}

function handleGet($avatarManager, $action) {
    switch ($action) {
        case 'list':
            // Listar avatares com filtros
            $filtros = [
                'categoria' => $_GET['categoria'] ?? null,
                'genero' => $_GET['genero'] ?? null,
                'idade_categoria' => $_GET['idade_categoria'] ?? null,
                'busca' => $_GET['busca'] ?? null,
                'pasta_id' => $_GET['pasta_id'] ?? null,
                'limite' => (int)($_GET['limite'] ?? 50)
            ];
            
            $avatares = $avatarManager->buscarAvatares($filtros);
            echo json_encode(['success' => true, 'data' => $avatares]);
            break;
            
        case 'get':
            // Obter avatar específico
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do avatar é obrigatório']);
                return;
            }
            
            $avatar = $avatarManager->obterAvatar($id);
            if ($avatar) {
                echo json_encode(['success' => true, 'data' => $avatar]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Avatar não encontrado']);
            }
            break;
            
        case 'folders':
            // Listar pastas (implementar se necessário)
            echo json_encode(['success' => true, 'data' => []]);
            break;
            
        case 'categories':
            // Listar categorias
            $categorias = $avatarManager->obterCategorias();
            echo json_encode(['success' => true, 'data' => $categorias]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação não reconhecida']);
            break;
    }
}

function handlePost($avatarManager, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'create':
            // Criar novo avatar
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Dados do avatar são obrigatórios']);
                return;
            }
            
            $resultado = $avatarManager->criarAvatar($input);
            if ($resultado['success']) {
                http_response_code(201);
            } else {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
            
        case 'create-folder':
            // Criar nova pasta (implementar se necessário)
            if (!$input || empty($input['name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Nome da pasta é obrigatório']);
                return;
            }
            
            // Implementar criação de pasta
            echo json_encode(['success' => true, 'message' => 'Pasta criada com sucesso']);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação não reconhecida']);
            break;
    }
}

function handlePut($avatarManager, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'update':
            // Atualizar avatar
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do avatar é obrigatório']);
                return;
            }
            
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Dados para atualização são obrigatórios']);
                return;
            }
            
            $resultado = $avatarManager->atualizarAvatar($id, $input);
            echo json_encode($resultado);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação não reconhecida']);
            break;
    }
}

function handleDelete($avatarManager, $action) {
    switch ($action) {
        case 'delete':
            // Excluir avatar
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do avatar é obrigatório']);
                return;
            }
            
            $resultado = $avatarManager->excluirAvatar($id);
            echo json_encode($resultado);
            break;
            
        case 'delete-folder':
            // Excluir pasta (implementar se necessário)
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID da pasta é obrigatório']);
                return;
            }
            
            // Implementar exclusão de pasta
            echo json_encode(['success' => true, 'message' => 'Pasta excluída com sucesso']);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação não reconhecida']);
            break;
    }
}
?>