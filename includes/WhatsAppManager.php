<?php
/**
 * Classe para Gerenciamento de Envio de WhatsApp
 * Suporte a múltiplas APIs: Twilio, Z-API, EvolutionAPI
 */

require_once __DIR__ . '/Environment.php';
require_once __DIR__ . '/WahaManager.php';

class WhatsAppManager {
    
    private static $provider;
    private static $config;
    
    /**
     * Inicializar configurações do WhatsApp
     */
    private static function init() {
        if (self::$provider) return;
        
        // Configurações da API de WhatsApp
        self::$provider = Environment::get('WHATSAPP_PROVIDER', 'waha');
        self::$config = [
            'waha' => [
                'server' => Environment::get('WAHA_SERVER', 'http://147.93.33.127:2142'),
                'timeout' => Environment::get('WAHA_TIMEOUT', 15),
                'session_prefix' => Environment::get('WHATSAPP_SESSION_PREFIX', 'dev_aprend_aqui_cadastro')
            ],
            'twilio' => [
                'account_sid' => Environment::get('TWILIO_ACCOUNT_SID'),
                'auth_token' => Environment::get('TWILIO_AUTH_TOKEN'),
                'whatsapp_number' => Environment::get('TWILIO_WHATSAPP_NUMBER', 'whatsapp:+14155238886')
            ],
            'zapi' => [
                'instance_id' => Environment::get('ZAPI_INSTANCE_ID'),
                'token' => Environment::get('ZAPI_TOKEN'),
                'base_url' => Environment::get('ZAPI_BASE_URL', 'https://api.z-api.io/instances')
            ],
            'evolution' => [
                'base_url' => Environment::get('EVOLUTION_BASE_URL', 'http://localhost:8080'),
                'api_key' => Environment::get('EVOLUTION_API_KEY'),
                'instance_name' => Environment::get('EVOLUTION_INSTANCE_NAME')
            ]
        ];
    }
    
    /**
     * Enviar código de ativação via WhatsApp
     */
    public static function sendActivationCode($whatsapp, $codigo, $nome) {
        self::init();
        
        if (empty($whatsapp)) {
            error_log("WhatsAppManager: Número de WhatsApp não fornecido");
            return false;
        }
        
        // Formatar número para padrão internacional
        $whatsapp = self::formatPhoneNumber($whatsapp);
        
        // Criar mensagem
        $mensagem = self::createActivationMessage($codigo, $nome);
        
        try {
            switch (self::$provider) {
                case 'waha':
                    $wahaResult = self::sendViaWaha($whatsapp, $mensagem, $nome);
                    // Se WAHA falhar, não fazer fallback - modo produção deve usar validação real
                    return $wahaResult;
                    
                case 'twilio':
                    return self::sendViaTwilio($whatsapp, $mensagem);
                    
                case 'zapi':
                    return self::sendViaZAPI($whatsapp, $mensagem);
                    
                case 'evolution':
                    return self::sendViaEvolution($whatsapp, $mensagem);
                    
                case 'demo':
                default:
                    return self::sendDemo($whatsapp, $mensagem, $codigo);
            }
            
        } catch (Exception $e) {
            error_log("WhatsAppManager: Erro ao enviar mensagem: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar via WAHA
     */
    private static function sendViaWaha($whatsapp, $mensagem, $nome) {
        require_once __DIR__ . '/WahaManager.php';
        
        try {
            // Extrair código da mensagem
            preg_match('/\*(\d{6})\*/', $mensagem, $matches);
            $codigo = $matches[1] ?? substr($mensagem, -6);
            
            error_log("WhatsAppManager PRODUÇÃO: Tentando enviar via WAHA para $whatsapp");
            
            // Usar WahaManager para enviar
            $result = WahaManager::sendActivationCode($whatsapp, $codigo, $nome);
            
            if ($result) {
                error_log("WhatsAppManager PRODUÇÃO: ✅ Mensagem WAHA enviada com sucesso para $whatsapp");
                return true;
            } else {
                error_log("WhatsAppManager PRODUÇÃO: ❌ WAHA falhou - NÃO ENVIADO (modo produção)");
                // Em modo produção, se WAHA falhar, não enviar
                // O usuário deve resolver a configuração WAHA
                return false;
            }
            
        } catch (Exception $e) {
            error_log("WhatsAppManager PRODUÇÃO: ❌ Exceção WAHA: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar via Twilio (Sandbox gratuito)
     */
    private static function sendViaTwilio($whatsapp, $mensagem) {
        $config = self::$config['twilio'];
        
        if (empty($config['account_sid']) || empty($config['auth_token'])) {
            error_log("WhatsAppManager: Configurações Twilio não encontradas");
            return false;
        }
        
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$config['account_sid']}/Messages.json";
        
        $data = [
            'From' => $config['whatsapp_number'],
            'To' => 'whatsapp:' . $whatsapp,
            'Body' => $mensagem
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $config['account_sid'] . ':' . $config['auth_token']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 201) {
            error_log("WhatsAppManager: Mensagem enviada via Twilio para $whatsapp");
            return true;
        } else {
            error_log("WhatsAppManager: Erro Twilio ($httpCode): $response");
            return false;
        }
    }
    
    /**
     * Enviar via Z-API
     */
    private static function sendViaZAPI($whatsapp, $mensagem) {
        $config = self::$config['zapi'];
        
        if (empty($config['instance_id']) || empty($config['token'])) {
            error_log("WhatsAppManager: Configurações Z-API não encontradas");
            return false;
        }
        
        $url = "{$config['base_url']}/{$config['instance_id']}/token/{$config['token']}/send-text";
        
        $data = [
            'phone' => $whatsapp,
            'message' => $mensagem
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            error_log("WhatsAppManager: Mensagem enviada via Z-API para $whatsapp");
            return true;
        } else {
            error_log("WhatsAppManager: Erro Z-API ($httpCode): $response");
            return false;
        }
    }
    
    /**
     * Enviar via Evolution API
     */
    private static function sendViaEvolution($whatsapp, $mensagem) {
        $config = self::$config['evolution'];
        
        if (empty($config['api_key']) || empty($config['instance_name'])) {
            error_log("WhatsAppManager: Configurações Evolution API não encontradas");
            return false;
        }
        
        $url = "{$config['base_url']}/message/sendText/{$config['instance_name']}";
        
        $data = [
            'number' => $whatsapp,
            'text' => $mensagem
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $config['api_key']
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 201 || $httpCode === 200) {
            error_log("WhatsAppManager: Mensagem enviada via Evolution API para $whatsapp");
            return true;
        } else {
            error_log("WhatsAppManager: Erro Evolution API ($httpCode): $response");
            return false;
        }
    }
    
    /**
     * Modo demo - apenas simular envio
     */
    private static function sendDemo($whatsapp, $mensagem, $codigo) {
        error_log("WhatsAppManager (DEMO): Enviaria para $whatsapp a mensagem: $mensagem");
        error_log("WhatsAppManager (DEMO): Código de ativação: $codigo");
        
        // Salvar código em arquivo temporário para demonstração
        $demoFile = '../temp/whatsapp_codes.json';
        
        // Criar diretório se não existir
        if (!file_exists(dirname($demoFile))) {
            mkdir(dirname($demoFile), 0755, true);
        }
        
        $codes = [];
        if (file_exists($demoFile)) {
            $codes = json_decode(file_get_contents($demoFile), true) ?: [];
        }
        
        $codes[$whatsapp] = [
            'codigo' => $codigo,
            'enviado_em' => date('Y-m-d H:i:s'),
            'mensagem' => $mensagem
        ];
        
        file_put_contents($demoFile, json_encode($codes, JSON_PRETTY_PRINT));
        
        return true;
    }
    
    /**
     * Formatar número de telefone para padrão internacional
     */
    private static function formatPhoneNumber($phone) {
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
        
        // Formato final: +5511999999999
        return '+' . $phone;
    }
    
    /**
     * Criar mensagem de ativação
     */
    private static function createActivationMessage($codigo, $nome) {
        $appName = Environment::get('APP_NAME', 'Prompt Builder IA');
        
        return "🔐 *{$appName}*\n\n" .
               "Olá, {$nome}!\n\n" .
               "Seu código de ativação é:\n" .
               "*{$codigo}*\n\n" .
               "Este código expira em 10 minutos.\n\n" .
               "Se você não solicitou este código, ignore esta mensagem.";
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
     * Reenviar código de ativação
     */
    public static function resendActivationCode($whatsapp, $email) {
        try {
            // Buscar usuário e gerar novo código
            require_once __DIR__ . '/SupabaseClient.php';
            $supabase = new SupabaseClient();
            $user = $supabase->getUserByEmail($email);
            
            if (!$user) {
                return false;
            }
            
            // Gerar novo código
            $novoCodigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Atualizar no banco
            $updateData = [
                'codigo_ativacao' => $novoCodigo,
                'codigo_gerado_em' => date('c')
            ];
            
            $updated = $supabase->updateUser($user['id'], $updateData);
            
            if ($updated) {
                return self::sendActivationCode($whatsapp, $novoCodigo, $user['nome']);
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("WhatsAppManager: Erro ao reenviar código: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar código de ativação
     */
    public static function validateActivationCode($email, $codigo) {
        self::init();
        
        // Se estivermos em modo demo, aceitar qualquer código de 6 dígitos
        if (self::$provider === 'demo') {
            if (preg_match('/^\d{6}$/', $codigo)) {
                error_log("WhatsAppManager: Modo demo - código $codigo aceito para $email");
                return [
                    'success' => true, 
                    'message' => 'WhatsApp confirmado com sucesso!',
                    'user' => ['email' => $email, 'id' => 'demo_' . time()]
                ];
            } else {
                return ['success' => false, 'message' => 'Código deve ter 6 dígitos'];
            }
        }
        
        // Se estivermos usando WAHA, delegar para WahaManager
        if (self::$provider === 'waha') {
            require_once __DIR__ . '/WahaManager.php';
            return WahaManager::validateActivationCode($email, $codigo);
        }
        
        // Fallback para validação tradicional
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
            error_log("WhatsAppManager: Erro ao validar código: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno. Tente novamente.'];
        }
    }
    
    /**
     * Enviar mensagem customizada via WhatsApp
     */
    public static function sendMessage($whatsapp, $mensagem) {
        self::init();
        
        if (empty($whatsapp)) {
            error_log("WhatsAppManager: Número de WhatsApp não fornecido");
            return false;
        }
        
        try {
            error_log("WhatsAppManager: Enviando mensagem customizada para $whatsapp");
            
            // Usar WahaManager para enviar
            $result = WahaManager::sendCustomMessage($whatsapp, $mensagem);
            
            if ($result) {
                error_log("WhatsAppManager: ✅ Mensagem customizada enviada para $whatsapp");
                return true;
            } else {
                error_log("WhatsAppManager: ❌ Falha ao enviar mensagem customizada para $whatsapp");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("WhatsAppManager: ❌ Exceção ao enviar mensagem: " . $e->getMessage());
            return false;
        }
    }
}
?>