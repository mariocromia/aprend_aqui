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
    public function makeRequest($endpoint, $method = 'GET', $data = null, $useServiceKey = false) {
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
        $response = $this->makeRequest('usuarios?email=eq.' . urlencode($email) . '&select=*', 'GET', null, true);
        
        error_log("SupabaseClient: getUserByEmail - Status: {$response['status']}, Raw: " . $response['raw']);
        
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
     * Criar token de recuperação de senha na tabela
     */
    public function createPasswordResetToken($tokenData) {
        try {
            // Primeiro, remover tokens antigos para este email
            $this->cleanupExpiredTokens($tokenData['email']);
            
            $response = $this->makeRequest('password_reset_tokens', 'POST', $tokenData, true);
            
            if ($response['status'] === 201 || $response['status'] === 200) {
                return true;
            }
            
            error_log("Erro ao criar token de reset: " . json_encode($response));
            return false;
            
        } catch (Exception $e) {
            error_log("Exceção ao criar token de reset: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Limpar tokens expirados
     */
    private function cleanupExpiredTokens($email) {
        try {
            // Remover tokens antigos deste email
            $response = $this->makeRequest(
                "password_reset_tokens?email=eq.$email", 
                'DELETE', 
                null, 
                true
            );
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao limpar tokens antigos: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar token de recuperação
     */
    public function verifyPasswordResetToken($token) {
        try {
            $response = $this->makeRequest(
                "password_reset_tokens?token=eq.$token&used=eq.false", 
                'GET', 
                null, 
                true
            );
            
            if ($response['status'] === 200 && !empty($response['data'])) {
                $tokenData = $response['data'][0];
                
                // Verificar se não expirou
                $expiresAt = strtotime($tokenData['expires_at']);
                if ($expiresAt > time()) {
                    return [
                        'valid' => true,
                        'email' => $tokenData['email'],
                        'user_id' => null
                    ];
                } else {
                    error_log("Token expirado para: " . $tokenData['email']);
                }
            }
            
            return ['valid' => false];
            
        } catch (Exception $e) {
            error_log("Erro ao verificar token: " . $e->getMessage());
            return ['valid' => false];
        }
    }
    
    /**
     * Marcar token como usado
     */
    public function markTokenAsUsed($token) {
        try {
            $response = $this->makeRequest(
                "password_reset_tokens?token=eq.$token", 
                'PATCH', 
                ['used' => true], 
                true
            );
            
            return $response['status'] === 200;
            
        } catch (Exception $e) {
            error_log("Erro ao marcar token como usado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Criar código de recuperação temporário
     */
    public function createPasswordResetCode($codeData) {
        try {
            // Primeiro, remover códigos antigos para este email
            $this->cleanupExpiredCodes($codeData['email']);
            
            $response = $this->makeRequest('password_reset_codes', 'POST', $codeData, true);
            
            if ($response['status'] === 201 || $response['status'] === 200) {
                return true;
            }
            
            error_log("Erro ao criar código de reset: " . json_encode($response));
            return false;
            
        } catch (Exception $e) {
            error_log("Exceção ao criar código de reset: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar código de recuperação temporário
     */
    public function verifyPasswordResetCode($email, $code) {
        try {
            $response = $this->makeRequest(
                "password_reset_codes?email=eq." . urlencode($email) . "&code=eq.$code&used=eq.false", 
                'GET', 
                null, 
                true
            );
            
            if ($response['status'] === 200 && !empty($response['data'])) {
                $codeData = $response['data'][0];
                
                // Verificar se não expirou
                $expiresAt = strtotime($codeData['expires_at']);
                if ($expiresAt > time()) {
                    return [
                        'valid' => true,
                        'email' => $codeData['email'],
                        'method' => $codeData['method']
                    ];
                } else {
                    error_log("Código expirado para: " . $codeData['email']);
                }
            }
            
            return ['valid' => false];
            
        } catch (Exception $e) {
            error_log("Erro ao verificar código: " . $e->getMessage());
            return ['valid' => false];
        }
    }
    
    /**
     * Marcar código como usado
     */
    public function markCodeAsUsed($email, $code) {
        try {
            $response = $this->makeRequest(
                "password_reset_codes?email=eq." . urlencode($email) . "&code=eq.$code", 
                'PATCH', 
                ['used' => true], 
                true
            );
            
            return $response['status'] === 200;
            
        } catch (Exception $e) {
            error_log("Erro ao marcar código como usado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Limpar códigos expirados
     */
    private function cleanupExpiredCodes($email) {
        try {
            // Remover códigos antigos deste email
            $response = $this->makeRequest(
                "password_reset_codes?email=eq." . urlencode($email), 
                'DELETE', 
                null, 
                true
            );
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao limpar códigos antigos: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Detectar qual coluna de senha usar (senha ou senha_hash)
     */
    private function getPasswordColumn() {
        try {
            $response = $this->makeRequest('usuarios?limit=1', 'GET', null, true);
            
            if ($response['status'] === 200 && !empty($response['data'])) {
                $columns = array_keys($response['data'][0]);
                
                if (in_array('senha_hash', $columns)) {
                    return 'senha_hash';
                } elseif (in_array('senha', $columns)) {
                    return 'senha';
                }
            }
            
            // Fallback para 'senha' se não conseguir detectar
            return 'senha';
            
        } catch (Exception $e) {
            error_log("Erro ao detectar coluna de senha: " . $e->getMessage());
            return 'senha';
        }
    }
    
    /**
     * Atualizar senha do usuário
     */
    public function updateUserPassword($email, $newPassword) {
        try {
            error_log("SupabaseClient: Iniciando atualização de senha para $email");
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            error_log("SupabaseClient: Hash gerado: " . substr($hashedPassword, 0, 20) . "...");
            
            // Detectar qual coluna usar
            $passwordColumn = $this->getPasswordColumn();
            error_log("SupabaseClient: Usando coluna: $passwordColumn");
            
            $updateData = [$passwordColumn => $hashedPassword];
            error_log("SupabaseClient: Dados para atualização: " . json_encode($updateData));
            
            $response = $this->makeRequest(
                "usuarios?email=eq." . urlencode($email), 
                'PATCH', 
                $updateData, 
                true
            );
            
            error_log("SupabaseClient: Resposta da atualização - Status: {$response['status']}, Data: " . json_encode($response['data']) . ", Raw: " . $response['raw']);
            
            if ($response['status'] === 200 || $response['status'] === 204) {
                error_log("SupabaseClient: Senha atualizada com sucesso para $email");
                return true;
            }
            
            error_log("SupabaseClient: Erro ao atualizar senha - Status: {$response['status']}, Response: " . json_encode($response));
            return false;
            
        } catch (Exception $e) {
            error_log("SupabaseClient: Exceção ao atualizar senha: " . $e->getMessage());
            return false;
        }
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
