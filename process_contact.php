<?php
// Configurações de cabeçalho
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Função para limpar e validar dados
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Função para validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Função para validar telefone (formato brasileiro)
function validatePhone($phone) {
    // Remove todos os caracteres não numéricos
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Verifica se tem entre 10 e 11 dígitos (com ou sem DDD)
    if (strlen($phone) >= 10 && strlen($phone) <= 11) {
        return true;
    }
    
    return false;
}

// Função para enviar email
function sendEmail($data) {
    // Configurações do email
    $to = 'contato@centroservice.com.br'; // Email de destino
    $subject = 'Nova mensagem do site - CentroService';
    
    // Cria o corpo do email
    $message = "
    <html>
    <head>
        <title>Nova mensagem do site</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2563eb; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8fafc; padding: 20px; border-radius: 0 0 10px 10px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #1f2937; }
            .value { color: #4b5563; }
            .footer { text-align: center; margin-top: 20px; color: #6b7280; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Nova Mensagem do Site</h1>
                <p>CentroService - Criação de Vídeos com IA</p>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>Nome:</div>
                    <div class='value'>{$data['name']}</div>
                </div>
                <div class='field'>
                    <div class='label'>Email:</div>
                    <div class='value'>{$data['email']}</div>
                </div>
                <div class='field'>
                    <div class='label'>Telefone:</div>
                    <div class='value'>{$data['phone']}</div>
                </div>
                <div class='field'>
                    <div class='label'>Serviço de Interesse:</div>
                    <div class='value'>{$data['service']}</div>
                </div>
                <div class='field'>
                    <div class='label'>Mensagem:</div>
                    <div class='value'>" . nl2br($data['message']) . "</div>
                </div>
            </div>
            <div class='footer'>
                <p>Esta mensagem foi enviada através do formulário de contato do site centroservice.com.br</p>
                <p>Data e hora: " . date('d/m/Y H:i:s') . "</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Cabeçalhos do email
    $headers = array(
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: CentroService <noreply@centroservice.com.br>',
        'Reply-To: ' . $data['email'],
        'X-Mailer: PHP/' . phpversion()
    );
    
    // Tenta enviar o email
    if (mail($to, $subject, $message, implode("\r\n", $headers))) {
        return true;
    }
    
    return false;
}

// Função para salvar no banco de dados (opcional)
function saveToDatabase($data) {
    // Aqui você pode implementar a lógica para salvar no banco de dados
    // Por exemplo, usando MySQL, PostgreSQL, etc.
    
    // Exemplo com MySQL (descomente e configure se necessário):
    /*
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=centroservice', 'username', 'password');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("
            INSERT INTO contacts (name, email, phone, service, message, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['service'],
            $data['message']
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log('Erro no banco de dados: ' . $e->getMessage());
        return false;
    }
    */
    
    // Por padrão, retorna true (sem banco de dados)
    return true;
}

// Função para enviar notificação para WhatsApp (opcional)
function sendWhatsAppNotification($data) {
    // Aqui você pode implementar a integração com WhatsApp Business API
    // ou usar serviços como Twilio, MessageBird, etc.
    
    // Exemplo básico (substitua pela sua implementação):
    $message = "Nova mensagem do site:\n";
    $message .= "Nome: {$data['name']}\n";
    $message .= "Email: {$data['email']}\n";
    $message .= "Telefone: {$data['phone']}\n";
    $message .= "Serviço: {$data['service']}\n";
    $message .= "Mensagem: {$data['message']}";
    
    // Log da mensagem (para desenvolvimento)
    error_log('WhatsApp Notification: ' . $message);
    
    return true;
}

// Função para gerar resposta de sucesso
function successResponse($message = 'Mensagem enviada com sucesso!') {
    echo json_encode([
        'success' => true,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

// Função para gerar resposta de erro
function errorResponse($message = 'Erro ao enviar mensagem', $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

// Captura e valida os dados do formulário
try {
    // Verifica se os dados foram enviados
    if (empty($_POST)) {
        errorResponse('Nenhum dado recebido');
        exit;
    }
    
    // Campos obrigatórios
    $requiredFields = ['name', 'email', 'service', 'message'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        errorResponse('Campos obrigatórios não preenchidos: ' . implode(', ', $missingFields));
        exit;
    }
    
    // Limpa e valida os dados
    $data = [
        'name' => cleanInput($_POST['name']),
        'email' => cleanInput($_POST['email']),
        'phone' => isset($_POST['phone']) ? cleanInput($_POST['phone']) : '',
        'service' => cleanInput($_POST['service']),
        'message' => cleanInput($_POST['message'])
    ];
    
    // Validações específicas
    if (!validateEmail($data['email'])) {
        errorResponse('Email inválido');
        exit;
    }
    
    if (!empty($data['phone']) && !validatePhone($data['phone'])) {
        errorResponse('Telefone inválido');
        exit;
    }
    
    // Valida o serviço selecionado
    $validServices = ['institucional', 'vsl', 'reels', 'ia'];
    if (!in_array($data['service'], $validServices)) {
        errorResponse('Serviço inválido selecionado');
        exit;
    }
    
    // Valida o tamanho da mensagem
    if (strlen($data['message']) < 10) {
        errorResponse('A mensagem deve ter pelo menos 10 caracteres');
        exit;
    }
    
    if (strlen($data['message']) > 1000) {
        errorResponse('A mensagem deve ter no máximo 1000 caracteres');
        exit;
    }
    
    // Proteção contra spam básica
    if (strlen($data['name']) > 100 || strlen($data['email']) > 100) {
        errorResponse('Dados inválidos detectados');
        exit;
    }
    
    // Verifica se não é um bot (verificação simples)
    if (isset($_POST['honeypot']) && !empty($_POST['honeypot'])) {
        errorResponse('Acesso negado');
        exit;
    }
    
    // Processa o envio
    $emailSent = sendEmail($data);
    $databaseSaved = saveToDatabase($data);
    $whatsappSent = sendWhatsAppNotification($data);
    
    // Log da tentativa
    $logMessage = sprintf(
        "Formulário de contato - Nome: %s, Email: %s, Serviço: %s, IP: %s",
        $data['name'],
        $data['email'],
        $data['service'],
        $_SERVER['REMOTE_ADDR'] ?? 'N/A'
    );
    error_log($logMessage);
    
    // Verifica se pelo menos o email foi enviado
    if ($emailSent) {
        successResponse('Mensagem enviada com sucesso! Entraremos em contato em breve.');
    } else {
        errorResponse('Erro ao enviar email. Tente novamente ou entre em contato diretamente.', 500);
    }
    
} catch (Exception $e) {
    // Log do erro
    error_log('Erro no processamento do formulário: ' . $e->getMessage());
    
    // Resposta de erro genérica (não expõe detalhes internos)
    errorResponse('Erro interno do servidor. Tente novamente mais tarde.', 500);
}

// Função para limpeza automática de logs antigos (opcional)
function cleanupOldLogs() {
    $logFile = __DIR__ . '/contact_logs.txt';
    
    if (file_exists($logFile)) {
        $fileTime = filemtime($logFile);
        $daysOld = (time() - $fileTime) / (60 * 60 * 24);
        
        // Remove logs com mais de 30 dias
        if ($daysOld > 30) {
            unlink($logFile);
        }
    }
}

// Executa limpeza (uma vez por dia)
if (rand(1, 100) === 1) {
    cleanupOldLogs();
}
?>
