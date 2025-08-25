<?php
/**
 * Classe para gerenciar WAHA - WhatsApp HTTP API
 * Adaptada do sistema da pasta base para nossa aplicação
 */

require_once 'Environment.php';

class WahaManager {
    private $sessionName;
    private $userId;
    private $wahaServer;
    private $timeout;
    
    public function __construct($userId, $userName = null) {
        $this->userId = $userId;
        $this->wahaServer = Environment::get('WAHA_SERVER', 'http://147.93.33.127:2142');
        $this->timeout = Environment::get('WAHA_TIMEOUT', 15);
        
        // Gerar nome da sessão usando o padrão da aplicação base
        $this->sessionName = $this->generateSessionName($userId, $userName);
        
        $this->logMessage("WahaManager construído - UserId: $userId, UserName: $userName, SessionName: {$this->sessionName}", 'DEBUG');
    }
    
    /**
     * Gerar nome da sessão seguindo padrão da aplicação base
     */
    private function generateSessionName($userId, $userName = null) {
        $prefix = Environment::get('WHATSAPP_SESSION_PREFIX', 'dev_aprend_aqui_cadastro');
        
        // Se não temos o nome, usar "usuario"
        if (!$userName) {
            $userName = "usuario";
        }
        
        // Limpar nome do usuário (apenas letras, números)
        $cleanUserName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $userName));
        
        // Se o nome ficou vazio após limpeza, usar "usuario"
        if (empty($cleanUserName)) {
            $cleanUserName = "usuario";
        }
        
        // Formato: prefix_nome_do_usuario_user_id
        return $prefix . '_' . $cleanUserName . '_' . $userId;
    }
    
    /**
     * Fazer requisição HTTP para WAHA
     */
    private function makeRequest($url, $method = 'GET', $data = null, $headers = null) {
        $this->logMessage("Fazendo requisição $method para: $url", 'DEBUG');
        
        // Headers padrão
        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        // Usar headers personalizados se fornecidos
        $requestHeaders = $headers ?: $defaultHeaders;
        
        // Preparar contexto para file_get_contents
        $contextOptions = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $requestHeaders),
                'timeout' => $this->timeout,
                'ignore_errors' => true
            ]
        ];
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $contextOptions['http']['content'] = json_encode($data);
        }
        
        $context = stream_context_create($contextOptions);
        
        $response = @file_get_contents($url, false, $context);
        
        // Extrair informações da resposta
        $httpCode = 0;
        $contentType = 'application/json';
        
        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                    $httpCode = (int)$matches[1];
                }
                if (preg_match('/^Content-Type:\s*(.+)/i', $header, $matches)) {
                    $contentType = trim($matches[1]);
                }
            }
        }
        
        $error = ($response === false) ? 'Falha na requisição HTTP' : null;
        
        $result = [
            'success' => $response !== false && $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'content_type' => $contentType,
            'response' => $response,
            'error' => $error,
            'data' => null
        ];
        
        if ($result['success'] && $response && strpos($contentType, 'application/json') !== false) {
            $result['data'] = json_decode($response, true);
        }
        
        $this->logMessage("Resposta HTTP $httpCode ($contentType): " . substr($response ?: '', 0, 200), 'DEBUG');
        
        return $result;
    }
    
    /**
     * Verificar status da sessão
     */
    public function getStatus() {
        $url = $this->wahaServer . "/api/sessions/{$this->sessionName}/status";
        $result = $this->makeRequest($url);
        
        if ($result['success'] && $result['data']) {
            return [
                'success' => true,
                'status' => $result['data']['status'] ?? 'UNKNOWN',
                'data' => $result['data']
            ];
        }
        
        return [
            'success' => false,
            'status' => 'NOT_FOUND',
            'error' => $result['error'] ?: 'Status não encontrado'
        ];
    }
    
    /**
     * Iniciar sessão WhatsApp
     */
    public function startSession() {
        $url = $this->wahaServer . "/api/sessions/{$this->sessionName}/start";
        $result = $this->makeRequest($url, 'POST', ['name' => $this->sessionName]);
        
        if ($result['success']) {
            $this->logMessage("Sessão iniciada: {$this->sessionName}", 'INFO');
            return [
                'success' => true,
                'message' => 'Sessão iniciada com sucesso',
                'session' => $this->sessionName
            ];
        }
        
        $this->logMessage("Erro ao iniciar sessão: " . ($result['error'] ?: 'Erro desconhecido'), 'ERROR');
        return [
            'success' => false,
            'error' => $result['error'] ?: 'Erro ao iniciar sessão'
        ];
    }
    
    /**
     * Obter QR Code para autenticação
     */
    public function getQRCode() {
        $url = $this->wahaServer . "/api/{$this->sessionName}/auth/qr";
        $result = $this->makeRequest($url);
        
        if ($result['success'] && $result['data']) {
            return [
                'success' => true,
                'qr_code' => $result['data']
            ];
        }
        
        return [
            'success' => false,
            'error' => 'QR Code não disponível'
        ];
    }
    
    /**
     * Enviar mensagem de texto
     */
    public function sendMessage($phone, $message) {
        $url = $this->wahaServer . "/api/sendText";
        
        $data = [
            'session' => $this->sessionName,
            'chatId' => $this->formatPhoneNumber($phone),
            'text' => $message
        ];
        
        $result = $this->makeRequest($url, 'POST', $data);
        
        if ($result['success']) {
            $this->logMessage("Mensagem enviada para $phone: " . substr($message, 0, 50), 'INFO');
            return [
                'success' => true,
                'message' => 'Mensagem enviada com sucesso'
            ];
        }
        
        $this->logMessage("Erro ao enviar mensagem para $phone: " . ($result['error'] ?: 'Erro desconhecido'), 'ERROR');
        return [
            'success' => false,
            'error' => $result['error'] ?: 'Erro ao enviar mensagem'
        ];
    }
    
    /**
     * Enviar mensagem genérica via WhatsApp (método estático)
     */
    public static function sendCustomMessage($whatsapp, $mensagem) {
        try {
            $sessionName = 'dev_aprend_aqui_cadastro';
            $wahaServer = Environment::get('WAHA_SERVER', 'https://waha.zapfunil.app');
            
            error_log("WahaManager: Enviando mensagem customizada para $whatsapp");
            
            // Verificar status da sessão
            $statusUrl = "$wahaServer/api/sessions/$sessionName";
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 10,
                    'ignore_errors' => true
                ]
            ]);
            
            $statusResponse = @file_get_contents($statusUrl, false, $context);
            if ($statusResponse === false) {
                error_log("WahaManager: Não foi possível conectar ao servidor WAHA");
                return false;
            }
            
            $statusData = json_decode($statusResponse, true);
            if (!isset($statusData['status']) || $statusData['status'] !== 'WORKING') {
                error_log("WahaManager: Sessão não está WORKING");
                return false;
            }
            
            // Formatar número corretamente
            $formattedNumber = preg_replace('/[^0-9]/', '', $whatsapp);
            if (!preg_match('/^55/', $formattedNumber)) {
                $formattedNumber = '55' . $formattedNumber;
            }
            $chatId = $formattedNumber . '@c.us';
            
            // Enviar mensagem
            $sendUrl = "$wahaServer/api/sendText";
            $sendData = [
                'session' => $sessionName,
                'chatId' => $chatId,
                'text' => $mensagem
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $sendUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $sendResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($sendResponse === false || !empty($curlError)) {
                error_log("WahaManager: Erro cURL: $curlError");
                return false;
            }
            
            $sendResult = json_decode($sendResponse, true);
            
            // Verificar sucesso
            $success = false;
            if (isset($sendResult['sent']) && $sendResult['sent']) {
                $success = true;
            } elseif (isset($sendResult['success']) && $sendResult['success']) {
                $success = true;
            } elseif (isset($sendResult['id']) && !empty($sendResult['id'])) {
                $success = true;
            } elseif (isset($sendResult['key']) && isset($sendResult['messageTimestamp'])) {
                $success = true;
            } elseif (!isset($sendResult['error']) && !empty($sendResult)) {
                $success = true;
            }
            
            if ($success) {
                error_log("WahaManager: ✅ Mensagem customizada enviada para $whatsapp");
                return true;
            } else {
                error_log("WahaManager: ❌ Falha no envio: " . json_encode($sendResult));
                return false;
            }
            
        } catch (Exception $e) {
            error_log("WahaManager: Exceção ao enviar mensagem: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar código de ativação via WhatsApp
     */
    public static function sendActivationCode($whatsapp, $codigo, $nome) {
        try {
            // Tentar primeiro usar sessão existente dev_aprend_aqui_cadastro
            $sessionName = 'dev_aprend_aqui_cadastro';
            $wahaServer = Environment::get('WAHA_SERVER', 'https://waha.zapfunil.app');
            
            error_log("🔥 WahaManager: Iniciando envio");
            error_log("🔥 WahaManager: Sessão: $sessionName");
            error_log("🔥 WahaManager: Servidor: $wahaServer");
            error_log("🔥 WahaManager: WhatsApp original: $whatsapp");
            error_log("🔥 WahaManager: Código: $codigo");
            error_log("🔥 WahaManager: Nome: $nome");
            
            // Verificar status da sessão existente via API direta
            $statusUrl = "$wahaServer/api/sessions/$sessionName";
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 10,
                    'ignore_errors' => true
                ]
            ]);
            
            $statusResponse = @file_get_contents($statusUrl, false, $context);
            
            if ($statusResponse === false) {
                error_log("WahaManager: Não foi possível conectar ao servidor WAHA em $wahaServer");
                return false;
            }
            
            $statusData = json_decode($statusResponse, true);
            error_log("WahaManager: Status da sessão: " . json_encode($statusData));
            
            if (!isset($statusData['status'])) {
                error_log("WahaManager: Resposta de status inválida");
                return false;
            }
            
            if ($statusData['status'] !== 'WORKING') {
                error_log("WahaManager: Sessão não está WORKING, status: " . $statusData['status']);
                return false;
            }
            
            // Sessão está WORKING, enviar mensagem diretamente
            error_log("WahaManager: ✅ Sessão $sessionName está WORKING, enviando mensagem");
            
            $message = "🔐 *Prompt Builder IA*\n\n" .
                      "Olá, {$nome}!\n\n" .
                      "Seu código de ativação é:\n" .
                      "*{$codigo}*\n\n" .
                      "Este código expira em 10 minutos.\n\n" .
                      "Se você não solicitou este código, ignore esta mensagem.";
            
            // Formatar número corretamente
            $formattedNumber = preg_replace('/[^0-9]/', '', $whatsapp);
            if (!preg_match('/^55/', $formattedNumber)) {
                $formattedNumber = '55' . $formattedNumber;
            }
            $chatId = $formattedNumber . '@c.us';
            
            // Usar formato correto da API WAHA
            $sendUrl = "$wahaServer/api/sendText";
            $sendData = [
                'session' => $sessionName,
                'chatId' => $chatId,
                'text' => $message
            ];
            
            error_log("🔥 WahaManager: Número formatado: $formattedNumber");
            error_log("🔥 WahaManager: ChatId: $chatId");
            error_log("🔥 WahaManager: URL: $sendUrl");
            error_log("🔥 WahaManager: Dados: " . json_encode($sendData));
            
            // Usar cURL para melhor controle da requisição POST
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $sendUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $sendResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($sendResponse === false || !empty($curlError)) {
                error_log("WahaManager: Erro cURL: $curlError (HTTP: $httpCode)");
                return false;
            }
            
            error_log("🔥 WahaManager: HTTP Code: $httpCode");
            error_log("🔥 WahaManager: cURL Error: " . ($curlError ?: 'Nenhum'));
            error_log("🔥 WahaManager: Resposta bruta: " . substr($sendResponse ?: 'null', 0, 500));
            
            $sendResult = json_decode($sendResponse, true);
            error_log("🔥 WahaManager: Resposta parseada: " . json_encode($sendResult));
            
            // Verificar diferentes formatos de resposta de sucesso
            $success = false;
            
            if (isset($sendResult['sent']) && $sendResult['sent']) {
                $success = true;
            } elseif (isset($sendResult['success']) && $sendResult['success']) {
                $success = true;
            } elseif (isset($sendResult['id']) && !empty($sendResult['id'])) {
                $success = true;
            } elseif (isset($sendResult['key']) && isset($sendResult['messageTimestamp'])) {
                // Formato WAHA com key e timestamp indica sucesso
                $success = true;
            } elseif (isset($sendResult['message']) && stripos($sendResult['message'], 'sent') !== false) {
                $success = true;
            } elseif (!isset($sendResult['error']) && !isset($sendResult['statusCode']) && !empty($sendResult)) {
                // Se não há erro explícito e há dados, considerar sucesso
                $success = true;
            }
            
            if ($success) {
                error_log("WahaManager: ✅ SUCESSO! Mensagem enviada para $whatsapp");
                return true;
            } else {
                error_log("WahaManager: ❌ Falha no envio: " . json_encode($sendResult));
                
                
                return false;
            }
            
        } catch (Exception $e) {
            error_log("WahaManager EXCEÇÃO: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar código de ativação (mantém compatibilidade com o sistema existente)
     */
    public static function validateActivationCode($email, $codigo) {
        try {
            require_once __DIR__ . '/SupabaseClient.php';
            $supabase = new SupabaseClient();
            $user = $supabase->getUserByEmail($email);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Usuário não encontrado'];
            }
            
            // Verificar se o código está correto
            if ($user['codigo_ativacao'] !== $codigo) {
                return ['success' => false, 'message' => 'Código incorreto'];
            }
            
            // Verificar se o código não expirou (10 minutos)
            $codigoGeradoEm = new DateTime($user['codigo_gerado_em']);
            $agora = new DateTime();
            $diferenca = $agora->getTimestamp() - $codigoGeradoEm->getTimestamp();
            
            if ($diferenca > 600) { // 10 minutos
                return ['success' => false, 'message' => 'Código expirado. Solicite um novo código.'];
            }
            
            // Ativar WhatsApp do usuário
            $updateData = [
                'whatsapp_confirmado' => true,
                'codigo_ativacao' => null,
                'codigo_gerado_em' => null
            ];
            
            $updated = $supabase->updateUser($user['id'], $updateData);
            
            if ($updated) {
                return ['success' => true, 'message' => 'WhatsApp confirmado com sucesso!', 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Erro ao confirmar WhatsApp'];
            }
            
        } catch (Exception $e) {
            error_log("WahaManager: Erro ao validar código: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno. Tente novamente.'];
        }
    }
    
    /**
     * Verificar se número de WhatsApp é válido
     */
    public static function isValidWhatsApp($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Verificar se é um número brasileiro válido
        return preg_match('/^(?:55)?(?:11|12|13|14|15|16|17|18|19|21|22|24|27|28|31|32|33|34|35|37|38|41|42|43|44|45|46|47|48|49|51|53|54|55|61|62|63|64|65|66|67|68|69|71|73|74|75|77|79|81|82|83|84|85|86|87|88|89|91|92|93|94|95|96|97|98|99)[0-9]{8,9}$/', $phone);
    }
    
    /**
     * Formatar número de telefone para WhatsApp
     */
    private function formatPhoneNumber($phone) {
        // Remover caracteres não numéricos
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Se começar com 0, remover
        if (substr($phone, 0, 1) === '0') {
            $phone = substr($phone, 1);
        }
        
        // Se não começar com 55 (Brasil), adicionar
        if (!preg_match('/^55/', $phone)) {
            $phone = '55' . $phone;
        }
        
        // Formato final: 5511999999999@c.us
        return $phone . '@c.us';
    }
    
    /**
     * Criar mensagem de ativação
     */
    private function createActivationMessage($codigo, $nome) {
        $appName = Environment::get('APP_NAME', 'Prompt Builder IA');
        
        return "🔐 *{$appName}*\n\n" .
               "Olá, {$nome}!\n\n" .
               "Seu código de ativação é:\n" .
               "*{$codigo}*\n\n" .
               "Este código expira em 10 minutos.\n\n" .
               "Se você não solicitou este código, ignore esta mensagem.";
    }
    
    /**
     * Fazer log de mensagens
     */
    private function logMessage($message, $level = 'INFO') {
        if (Environment::get('DEBUG_MODE', false)) {
            $timestamp = date('Y-m-d H:i:s');
            error_log("[$timestamp] [$level] WahaManager: $message");
        }
    }
    
    /**
     * Obter nome da sessão atual
     */
    public function getSessionName() {
        return $this->sessionName;
    }
}
?>