<?php
/**
 * API para Administração de Cards
 * Sistema de gerenciamento de blocos e cenas
 */

// Configurar relatórios de erro
error_reporting(E_ALL);
ini_set('display_errors', 0); // Não mostrar erros na saída JSON

// Buffer de saída para capturar qualquer output não intencional
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Função para limpar buffer e enviar resposta JSON
function sendJsonResponse($data) {
    // Limpar qualquer output anterior
    if (ob_get_level()) {
        ob_clean();
    }
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Verificar se arquivos necessários existem
$requiredFiles = [
    '../includes/CenaManager.php',
    '../includes/Environment.php'
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        sendJsonResponse([
            'success' => false,
            'message' => "Arquivo necessário não encontrado: {$file}",
            'error_code' => 'MISSING_FILE'
        ]);
    }
}

require_once '../includes/CenaManager.php';
require_once '../includes/Environment.php';

try {
    // Verificar ambiente
    $env = new Environment();
    
    // Instanciar o gerenciador
    $cenaManager = new CenaManager();
    
    // Determinar ação
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Para POST, verificar se é JSON
    if ($method === 'POST') {
        $input = file_get_contents('php://input');
        $postData = json_decode($input, true);
        
        if ($postData) {
            $action = $postData['action'] ?? $action;
            $_POST = array_merge($_POST, $postData);
        }
    }
    
    switch ($action) {
        case 'listar_blocos':
            listarBlocos($cenaManager);
            break;
            
        case 'listar_blocos_resumo':
            listarBlocosResumo($cenaManager);
            break;
            
        case 'listar_cenas':
            listarCenas($cenaManager);
            break;
            
        case 'listar_cenas_por_bloco':
            listarCenasPorBloco($cenaManager);
            break;
            
        case 'criar_bloco':
            criarBloco($cenaManager);
            break;
            
        case 'atualizar_bloco':
            atualizarBloco($cenaManager);
            break;
            
        case 'excluir_bloco':
            excluirBloco($cenaManager);
            break;
            
        case 'criar_cena':
            criarCena($cenaManager);
            break;
            
        case 'atualizar_cena':
            atualizarCena($cenaManager);
            break;
            
        case 'excluir_cena':
            excluirCena($cenaManager);
            break;
            
        default:
            throw new Exception('Ação não especificada ou inválida');
    }
    
} catch (Exception $e) {
    error_log("Erro na API admin-cards: " . $e->getMessage());
    
    http_response_code(500);
    sendJsonResponse([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'INTERNAL_ERROR'
    ]);
}

/**
 * Lista todos os blocos de cenas
 */
function listarBlocos($cenaManager) {
    try {
        $blocos = $cenaManager->listarTodosBlocos();
        
        // Adicionar contagem de cenas para cada bloco
        foreach ($blocos as &$bloco) {
            $bloco['total_cenas'] = $cenaManager->contarCenasPorBloco($bloco['id']);
            $bloco['ativo'] = $bloco['ativo'] ?? true; // Garantir campo ativo
        }
        
        sendJsonResponse([
            'success' => true,
            'data' => $blocos,
            'total' => count($blocos)
        ]);
        
    } catch (Exception $e) {
        throw new Exception('Erro ao listar blocos: ' . $e->getMessage());
    }
}

/**
 * Lista todas as cenas
 */
function listarCenas($cenaManager) {
    try {
        $cenas = $cenaManager->listarTodasCenas();
        
        // Garantir campo ativo para todas as cenas
        foreach ($cenas as &$cena) {
            $cena['ativo'] = $cena['ativo'] ?? true;
        }
        
        sendJsonResponse([
            'success' => true,
            'data' => $cenas,
            'total' => count($cenas)
        ]);
        
    } catch (Exception $e) {
        throw new Exception('Erro ao listar cenas: ' . $e->getMessage());
    }
}

/**
 * Lista apenas resumo dos blocos (otimizado)
 */
function listarBlocosResumo($cenaManager) {
    try {
        $blocos = $cenaManager->listarTodosBlocos();
        
        // Retornar apenas campos essenciais para melhor performance
        $blocosResumo = array_map(function($bloco) use ($cenaManager) {
            return [
                'id' => $bloco['id'],
                'titulo' => $bloco['titulo'],
                'icone' => $bloco['icone'],
                'tipo_aba' => $bloco['tipo_aba'],
                'ordem_exibicao' => $bloco['ordem_exibicao'],
                'ativo' => $bloco['ativo'] ?? true,
                'total_cenas' => $cenaManager->contarCenasPorBloco($bloco['id'])
            ];
        }, $blocos);
        
        sendJsonResponse([
            'success' => true,
            'data' => $blocosResumo,
            'total' => count($blocosResumo)
        ]);
        
    } catch (Exception $e) {
        throw new Exception('Erro ao listar resumo dos blocos: ' . $e->getMessage());
    }
}

/**
 * Lista cenas por bloco específico (otimizado)
 */
function listarCenasPorBloco($cenaManager) {
    try {
        $blocoId = $_GET['bloco_id'] ?? null;
        
        if (!$blocoId) {
            throw new Exception('ID do bloco não fornecido');
        }
        
        $cenas = $cenaManager->getCenasPorBloco($blocoId);
        
        // Garantir campo ativo
        foreach ($cenas as &$cena) {
            $cena['ativo'] = $cena['ativo'] ?? true;
            $cena['bloco_id'] = $blocoId; // Garantir bloco_id
        }
        
        sendJsonResponse([
            'success' => true,
            'data' => $cenas,
            'total' => count($cenas),
            'bloco_id' => $blocoId
        ]);
        
    } catch (Exception $e) {
        throw new Exception('Erro ao listar cenas por bloco: ' . $e->getMessage());
    }
}

/**
 * Cria um novo bloco
 */
function criarBloco($cenaManager) {
    try {
        $dados = validarDadosBloco();
        
        $resultado = $cenaManager->inserirBloco(
            $dados['titulo'],
            $dados['icone'],
            $dados['tipo_aba'],
            $dados['ordem_exibicao']
        );
        
        if ($resultado) {
            logAtividade('CRIAR_BLOCO', "Bloco '{$dados['titulo']}' criado");
            
            sendJsonResponse([
                'success' => true,
                'message' => 'Bloco criado com sucesso',
                'data' => is_array($resultado) ? $resultado : null
            ]);
        } else {
            throw new Exception('Falha ao criar bloco no banco de dados');
        }
        
    } catch (Exception $e) {
        throw new Exception('Erro ao criar bloco: ' . $e->getMessage());
    }
}

/**
 * Atualiza um bloco existente
 */
function atualizarBloco($cenaManager) {
    try {
        $id = $_POST['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            throw new Exception('ID do bloco inválido');
        }
        
        // Verificar se bloco existe
        $blocoExistente = $cenaManager->getBlocoPorId($id);
        if (!$blocoExistente) {
            throw new Exception('Bloco não encontrado');
        }
        
        $dados = validarDadosBloco();
        $ativo = $_POST['ativo'] ?? true;
        
        $sucesso = $cenaManager->atualizarBloco(
            $id,
            $dados['titulo'],
            $dados['icone'],
            $dados['tipo_aba'],
            $dados['ordem_exibicao'],
            $ativo
        );
        
        if ($sucesso) {
            logAtividade('ATUALIZAR_BLOCO', "Bloco ID {$id} atualizado para '{$dados['titulo']}'");
            
            sendJsonResponse([
                'success' => true,
                'message' => 'Bloco atualizado com sucesso'
            ]);
        } else {
            throw new Exception('Falha ao atualizar bloco no banco de dados');
        }
        
    } catch (Exception $e) {
        throw new Exception('Erro ao atualizar bloco: ' . $e->getMessage());
    }
}

/**
 * Exclui um bloco
 */
function excluirBloco($cenaManager) {
    try {
        $id = $_POST['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            throw new Exception('ID do bloco inválido');
        }
        
        // Verificar se bloco existe
        $blocoExistente = $cenaManager->getBlocoPorId($id);
        if (!$blocoExistente) {
            throw new Exception('Bloco não encontrado');
        }
        
        // Contar cenas associadas
        $totalCenas = $cenaManager->contarCenasPorBloco($id);
        
        $sucesso = $cenaManager->excluirBloco($id);
        
        if ($sucesso) {
            logAtividade('EXCLUIR_BLOCO', "Bloco '{$blocoExistente['titulo']}' excluído (tinha {$totalCenas} cenas)");
            
            sendJsonResponse([
                'success' => true,
                'message' => 'Bloco excluído com sucesso',
                'cenas_removidas' => $totalCenas
            ]);
        } else {
            throw new Exception('Falha ao excluir bloco do banco de dados');
        }
        
    } catch (Exception $e) {
        throw new Exception('Erro ao excluir bloco: ' . $e->getMessage());
    }
}

/**
 * Cria uma nova cena
 */
function criarCena($cenaManager) {
    try {
        $dados = validarDadosCena();
        
        $resultado = $cenaManager->inserirCena(
            $dados['bloco_id'],
            $dados['titulo'],
            $dados['subtitulo'],
            $dados['texto_prompt'],
            $dados['valor_selecao'],
            $dados['ordem_exibicao']
        );
        
        if ($resultado) {
            logAtividade('CRIAR_CENA', "Cena '{$dados['titulo']}' criada no bloco {$dados['bloco_id']}");
            
            sendJsonResponse([
                'success' => true,
                'message' => 'Cena criada com sucesso',
                'data' => is_array($resultado) ? $resultado : null
            ]);
        } else {
            throw new Exception('Falha ao criar cena no banco de dados');
        }
        
    } catch (Exception $e) {
        // Re-throw para manter mensagens específicas (como valor duplicado)
        throw $e;
    }
}

/**
 * Atualiza uma cena existente
 */
function atualizarCena($cenaManager) {
    try {
        $id = $_POST['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            throw new Exception('ID da cena inválido');
        }
        
        // Verificar se cena existe
        $cenaExistente = $cenaManager->getCenaPorId($id);
        if (!$cenaExistente) {
            throw new Exception('Cena não encontrada');
        }
        
        $dados = validarDadosCena();
        $ativo = $_POST['ativo'] ?? true;
        
        $sucesso = $cenaManager->atualizarCena(
            $id,
            $dados['bloco_id'],
            $dados['titulo'],
            $dados['subtitulo'],
            $dados['texto_prompt'],
            $dados['valor_selecao'],
            $dados['ordem_exibicao'],
            $ativo
        );
        
        if ($sucesso) {
            logAtividade('ATUALIZAR_CENA', "Cena ID {$id} atualizada para '{$dados['titulo']}'");
            
            sendJsonResponse([
                'success' => true,
                'message' => 'Cena atualizada com sucesso'
            ]);
        } else {
            throw new Exception('Falha ao atualizar cena no banco de dados');
        }
        
    } catch (Exception $e) {
        // Re-throw para manter mensagens específicas
        throw $e;
    }
}

/**
 * Exclui uma cena
 */
function excluirCena($cenaManager) {
    try {
        $id = $_POST['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            throw new Exception('ID da cena inválido');
        }
        
        // Verificar se cena existe
        $cenaExistente = $cenaManager->getCenaPorId($id);
        if (!$cenaExistente) {
            throw new Exception('Cena não encontrada');
        }
        
        $sucesso = $cenaManager->excluirCena($id);
        
        if ($sucesso) {
            logAtividade('EXCLUIR_CENA', "Cena '{$cenaExistente['titulo']}' excluída");
            
            sendJsonResponse([
                'success' => true,
                'message' => 'Cena excluída com sucesso'
            ]);
        } else {
            throw new Exception('Falha ao excluir cena do banco de dados');
        }
        
    } catch (Exception $e) {
        throw new Exception('Erro ao excluir cena: ' . $e->getMessage());
    }
}

/**
 * Valida dados do bloco
 */
function validarDadosBloco() {
    $titulo = trim($_POST['titulo'] ?? '');
    $icone = trim($_POST['icone'] ?? '');
    $tipo_aba = trim($_POST['tipo_aba'] ?? '');
    $ordem_exibicao = (int)($_POST['ordem_exibicao'] ?? 0);
    
    if (empty($titulo)) {
        throw new Exception('Título é obrigatório');
    }
    
    if (strlen($titulo) > 100) {
        throw new Exception('Título deve ter no máximo 100 caracteres');
    }
    
    if (empty($icone)) {
        throw new Exception('Ícone é obrigatório');
    }
    
    if (strlen($icone) > 50) {
        throw new Exception('Ícone deve ter no máximo 50 caracteres');
    }
    
    $tipos_validos = ['ambiente', 'iluminacao', 'avatar', 'camera', 'voz', 'acao'];
    if (!in_array($tipo_aba, $tipos_validos)) {
        throw new Exception('Tipo de aba inválido');
    }
    
    if ($ordem_exibicao < 0) {
        throw new Exception('Ordem de exibição deve ser um número positivo');
    }
    
    return [
        'titulo' => $titulo,
        'icone' => $icone,
        'tipo_aba' => $tipo_aba,
        'ordem_exibicao' => $ordem_exibicao
    ];
}

/**
 * Valida dados da cena
 */
function validarDadosCena() {
    $bloco_id = (int)($_POST['bloco_id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $subtitulo = trim($_POST['subtitulo'] ?? '');
    $texto_prompt = trim($_POST['texto_prompt'] ?? '');
    $valor_selecao = trim($_POST['valor_selecao'] ?? '');
    $ordem_exibicao = (int)($_POST['ordem_exibicao'] ?? 0);
    
    if ($bloco_id <= 0) {
        throw new Exception('Bloco é obrigatório');
    }
    
    if (empty($titulo)) {
        throw new Exception('Título é obrigatório');
    }
    
    if (strlen($titulo) > 100) {
        throw new Exception('Título deve ter no máximo 100 caracteres');
    }
    
    if (!empty($subtitulo) && strlen($subtitulo) > 200) {
        throw new Exception('Subtítulo deve ter no máximo 200 caracteres');
    }
    
    if (empty($texto_prompt)) {
        throw new Exception('Texto do prompt é obrigatório');
    }
    
    if (empty($valor_selecao)) {
        throw new Exception('Valor de seleção é obrigatório');
    }
    
    if (strlen($valor_selecao) > 100) {
        throw new Exception('Valor de seleção deve ter no máximo 100 caracteres');
    }
    
    // Validar formato do valor de seleção (apenas letras, números e underscore)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $valor_selecao)) {
        throw new Exception('Valor de seleção deve conter apenas letras, números e underscore');
    }
    
    if ($ordem_exibicao < 0) {
        throw new Exception('Ordem de exibição deve ser um número positivo');
    }
    
    return [
        'bloco_id' => $bloco_id,
        'titulo' => $titulo,
        'subtitulo' => $subtitulo ?: null,
        'texto_prompt' => $texto_prompt,
        'valor_selecao' => $valor_selecao,
        'ordem_exibicao' => $ordem_exibicao
    ];
}

/**
 * Sanitiza string para evitar XSS
 */
function sanitizar($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

/**
 * Log de atividades administrativas
 */
function logAtividade($acao, $detalhes = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $log = date('Y-m-d H:i:s') . " - {$acao}";
    if ($detalhes) {
        $log .= " - {$detalhes}";
    }
    $log .= " - IP: {$ip} - User-Agent: " . substr($userAgent, 0, 100);
    
    // Criar diretório de logs se não existir
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    error_log($log . "\n", 3, $logDir . '/admin-cards.log');
}
?>