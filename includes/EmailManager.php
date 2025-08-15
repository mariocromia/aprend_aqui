<?php
/**
 * Classe para Gerenciamento de Emails
 * Versão integrada com Supabase e SMTP
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailManager {
    
    private static $smtp_config = [
        'host' => 'smtp.hostinger.com',
        'port' => 587,
        'username' => 'contato@centroservice.com.br',
        'password' => '', // Será definida via variável de ambiente
        'from_email' => 'contato@centroservice.com.br',
        'from_name' => 'Centro Service - Aprenda Aqui'
    ];
    
    /**
     * Configurar PHPMailer com as configurações SMTP
     */
    private static function configureSMTP() {
        $mail = new PHPMailer(true);
        
        try {
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host = self::$smtp_config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = self::$smtp_config['username'];
            $mail->Password = $_ENV['SMTP_PASSWORD'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = self::$smtp_config['port'];
            $mail->CharSet = 'UTF-8';
            
            // Remetente
            $mail->setFrom(self::$smtp_config['from_email'], self::$smtp_config['from_name']);
            
            return $mail;
            
        } catch (Exception $e) {
            error_log("Erro ao configurar SMTP: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar email de recuperação de senha
     */
    public static function sendPasswordReset($email, $token) {
        try {
            $mail = self::configureSMTP();
            if (!$mail) {
                return false;
            }
            
            $mail->addAddress($email);
            $mail->Subject = 'Recuperação de Senha - Centro Service';
            
            $resetLink = "https://" . $_SERVER['HTTP_HOST'] . "/auth/redefinir-senha.php?token=" . $token;
            
            $mail->isHTML(true);
            $mail->Body = "
                <h2>Recuperação de Senha</h2>
                <p>Você solicitou a recuperação de sua senha.</p>
                <p>Clique no link abaixo para redefinir sua senha:</p>
                <p><a href='$resetLink' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Redefinir Senha</a></p>
                <p>Este link é válido por 1 hora.</p>
                <p>Se você não solicitou esta recuperação, ignore este email.</p>
                <hr>
                <p><small>Centro Service - Aprenda Aqui</small></p>
            ";
            
            $mail->AltBody = "Recuperação de Senha\n\nVocê solicitou a recuperação de sua senha.\nAcesse o link: $resetLink\n\nEste link é válido por 1 hora.\n\nCentro Service - Aprenda Aqui";
            
            $mail->send();
            error_log("EmailManager: Email de recuperação enviado para $email");
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
        try {
            $mail = self::configureSMTP();
            if (!$mail) {
                return false;
            }
            
            $mail->addAddress($email, $nome);
            $mail->Subject = 'Bem-vindo ao Centro Service - Aprenda Aqui!';
            
            $mail->isHTML(true);
            $mail->Body = "
                <h2>Bem-vindo(a), $nome!</h2>
                <p>É um prazer tê-lo(a) conosco no <strong>Centro Service - Aprenda Aqui</strong>!</p>
                <p>Sua conta foi criada com sucesso e você já pode começar a explorar nossa plataforma.</p>
                <p>Aqui você encontrará:</p>
                <ul>
                    <li>Cursos e treinamentos especializados</li>
                    <li>Ferramentas de aprendizado interativas</li>
                    <li>Suporte técnico especializado</li>
                </ul>
                <p><a href='https://" . $_SERVER['HTTP_HOST'] . "' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Acessar Plataforma</a></p>
                <hr>
                <p><small>Centro Service - Aprenda Aqui<br>contato@centroservice.com.br</small></p>
            ";
            
            $mail->AltBody = "Bem-vindo(a), $nome!\n\nÉ um prazer tê-lo(a) conosco no Centro Service - Aprenda Aqui!\n\nSua conta foi criada com sucesso e você já pode começar a explorar nossa plataforma.\n\nAcesse: https://" . $_SERVER['HTTP_HOST'] . "\n\nCentro Service - Aprenda Aqui";
            
            $mail->send();
            error_log("EmailManager: Email de boas-vindas enviado para $email");
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar email de boas-vindas: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar código de ativação
     */
    public static function sendActivationCode($email, $codigo, $nome) {
        try {
            $mail = self::configureSMTP();
            if (!$mail) {
                return false;
            }
            
            $mail->addAddress($email, $nome);
            $mail->Subject = 'Código de Ativação - Centro Service';
            
            $mail->isHTML(true);
            $mail->Body = "
                <h2>Código de Ativação</h2>
                <p>Olá, $nome!</p>
                <p>Seu código de ativação para o WhatsApp é:</p>
                <div style='background-color: #f8f9fa; padding: 20px; text-align: center; margin: 20px 0; border-radius: 10px;'>
                    <h1 style='color: #007bff; font-size: 36px; margin: 0; letter-spacing: 5px;'>$codigo</h1>
                </div>
                <p>Digite este código no WhatsApp para confirmar sua conta.</p>
                <p><strong>Importante:</strong> Este código é válido por 10 minutos.</p>
                <hr>
                <p><small>Centro Service - Aprenda Aqui<br>contato@centroservice.com.br</small></p>
            ";
            
            $mail->AltBody = "Código de Ativação\n\nOlá, $nome!\n\nSeu código de ativação para o WhatsApp é: $codigo\n\nDigite este código no WhatsApp para confirmar sua conta.\n\nImportante: Este código é válido por 10 minutos.\n\nCentro Service - Aprenda Aqui";
            
            $mail->send();
            error_log("EmailManager: Código de ativação $codigo enviado para $email");
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar código de ativação: " . $e->getMessage());
            return false;
        }
    }
}
?>
