<?php
/**
 * Cliente Supabase para PHP
 * Implementa conexão via API REST do Supabase
 */
require_once __DIR__ . '/Environment.php';

class SupabaseClient {
    private $supabaseUrl;
    private $supabaseKey;
    private $anonKey;
    
    public function __construct() {
        $this->supabaseUrl = Environment::get('SUPABASE_URL', '');
        $this->supabaseKey = Environment::get('SUPABASE_SERVICE_KEY', '');
        $this->anonKey = Environment::get('SUPABASE_ANON_KEY', '');
        
        if (empty($this->supabaseUrl) || empty($this->anonKey)) {
            throw new Exception('Configurações do Supabase não encontradas no env.config');
        }
    }
    
    /**
     * Fazer requisição para a API do Supabase
     */
    private function makeRequest($endpoint, $method = 'GET', $data = null, $useServiceKey = false) {
        $url = rtrim($this->supabaseUrl, '/') . '/rest/v1/' . ltrim($endpoint, '/');
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . ($useServiceKey ? $this->supabaseKey : $this->anonKey),
            'Authorization: Bearer ' . ($useServiceKey ? $this->supabaseKey : $this->anonKey)
        ];
        
        // Adicionar cabeçalho para retornar dados nas operações de escrita
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $headers[] = 'Prefer: return=representation';
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('Erro cURL: ' . $error);
        }
        
        return [
            'status' => $httpCode,
            'data' => json_decode($response, true),
            'raw' => $response
        ];
    }
    
    /**
     * Buscar usuário por email
     */
    public function getUserByEmail($email) {
        $response = $this->makeRequest('usuarios?email=eq.' . urlencode($email) . '&select=*');
        
        if ($response['status'] === 200 && !empty($response['data'])) {
            return $response['data'][0];
        }
        
        return null;
    }
    
    /**
     * Criar novo usuário
     */
    public function createUser($userData) {
        $response = $this->makeRequest('usuarios', 'POST', $userData, true);
        
        if ($response['status'] === 201) {
            // Supabase retorna array de objetos, pegar o primeiro elemento
            if (is_array($response['data']) && !empty($response['data'])) {
                return $response['data'][0];
            }
            // Se não retornou dados mas status é 201, foi criado com sucesso
            if (empty($response['data'])) {
                return ['success' => true, 'message' => 'Usuário criado com sucesso'];
            }
            return $response['data'];
        }
        
        // Adicionar mais detalhes de erro para debugging
        $errorMessage = 'Erro ao criar usuário: HTTP ' . $response['status'];
        if (!empty($response['data']['message'])) {
            $errorMessage .= ' - ' . $response['data']['message'];
        } elseif (!empty($response['raw'])) {
            $errorMessage .= ' - ' . $response['raw'];
        }
        
        throw new Exception($errorMessage);
    }
    
    /**
     * Atualizar usuário
     */
    public function updateUser($userId, $userData) {
        $response = $this->makeRequest('usuarios?id=eq.' . $userId, 'PATCH', $userData, true);
        
        if ($response['status'] === 204 || $response['status'] === 200) {
            return true;
        }
        
        throw new Exception('Erro ao atualizar usuário: ' . ($response['raw'] ?? 'Erro desconhecido'));
    }
    
    /**
     * Gerar token de recuperação de senha
     */
    public function generatePasswordResetToken($email) {
        $response = $this->makeRequest('rpc/generate_password_reset_token', 'POST', ['user_email' => $email]);
        
        if ($response['status'] === 200 && !empty($response['data'])) {
            return $response['data'][0];
        }
        
        return null;
    }
    
    /**
     * Verificar token de recuperação
     */
    public function verifyPasswordResetToken($token) {
        $response = $this->makeRequest('rpc/verify_password_reset_token', 'POST', ['reset_token' => $token]);
        
        if ($response['status'] === 200 && !empty($response['data'])) {
            return $response['data'][0];
        }
        
        return null;
    }
    
    /**
     * Registrar tentativa de login
     */
    public function logLoginAttempt($email, $success, $failureReason = null, $ipAddress = null, $userAgent = null) {
        $data = [
            'email' => $email,
            'sucesso' => $success,
            'motivo_falha' => $failureReason,
            'ip_address' => $ipAddress ?: $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'user_agent' => $userAgent ?: $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        $response = $this->makeRequest('user_login_attempts', 'POST', $data);
        
        return $response['status'] === 201;
    }
    
    /**
     * Buscar estatísticas de usuários
     */
    public function getUserStats() {
        $response = $this->makeRequest('usuarios_stats');
        
        if ($response['status'] === 200 && !empty($response['data'])) {
            return $response['data'][0];
        }
        
        return null;
    }
    
    /**
     * Limpar dados antigos
     */
    public function cleanupAuthData() {
        $response = $this->makeRequest('rpc/cleanup_auth_data', 'POST');
        
        if ($response['status'] === 200) {
            return $response['data'][0] ?? 'Limpeza concluída';
        }
        
        return 'Erro na limpeza';
    }
    
    /**
     * Verificar se email existe
     */
    public function emailExists($email) {
        $response = $this->makeRequest('usuarios?email=eq.' . urlencode($email) . '&select=id');
        
        return $response['status'] === 200 && !empty($response['data']);
    }
    
    /**
     * Buscar usuário por ID
     */
    public function getUserById($userId) {
        $response = $this->makeRequest('usuarios?id=eq.' . $userId . '&select=*');
        
        if ($response['status'] === 200 && !empty($response['data'])) {
            return $response['data'][0];
        }
        
        return null;
    }
}
?>
