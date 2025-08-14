<?php
/**
 * Classe para Gerenciamento de Emails
 * Versão integrada com Supabase
 */
class EmailManager {
    
    /**
     * Enviar email de recuperação de senha
     */
    public static function sendPasswordReset($email, $token) {
        try {
            // Por enquanto, apenas simular envio
            // TODO: Implementar envio real de email
            error_log("EmailManager: Enviaria email de recuperação para $email com token $token");
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar email de recuperação: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Gerar token de recuperação usando Supabase
     */
    public static function generateResetToken($userId, $email) {
        try {
            $supabase = new SupabaseClient();
            $result = $supabase->generatePasswordResetToken($email);
            
            if ($result && isset($result['token'])) {
                error_log("EmailManager: Token gerado para $email: " . $result['token']);
                return $result['token'];
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao gerar token de recuperação: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se token é válido usando Supabase
     */
    public static function verifyResetToken($token) {
        try {
            $supabase = new SupabaseClient();
            $result = $supabase->verifyPasswordResetToken($token);
            
            if ($result && isset($result['valid']) && $result['valid']) {
                return [
                    'user_id' => $result['user_id'],
                    'email' => $result['email'],
                    'expires' => time() + 3600, // 1 hora
                    'created' => time()
                ];
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao verificar token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Redefinir senha usando token do Supabase
     */
    public static function resetPassword($token, $newPassword) {
        try {
            $resetData = self::verifyResetToken($token);
            
            if (!$resetData) {
                return [
                    'success' => false,
                    'message' => 'Token inválido ou expirado'
                ];
            }
            
            // TODO: Implementar atualização de senha no Supabase Auth
            // Por enquanto, apenas simular
            error_log("EmailManager: Senha seria redefinida para usuário " . $resetData['user_id']);
            
            return [
                'success' => true,
                'message' => 'Senha redefinida com sucesso'
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao redefinir senha: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente.'
            ];
        }
    }
    
    /**
     * Enviar email de boas-vindas
     */
    public static function sendWelcomeEmail($email, $nome) {
        error_log("EmailManager: Enviaria email de boas-vindas para $email (Nome: $nome)");
        return true;
    }
    
    /**
     * Enviar código de ativação
     */
    public static function sendActivationCode($email, $codigo, $nome) {
        error_log("EmailManager: Enviaria código de ativação $codigo para $email (Nome: $nome)");
        return true;
    }
}
?>
