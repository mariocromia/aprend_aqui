<?php
/**
 * Classe para Gerenciamento de Emails
 * Versão integrada com Supabase e SMTP
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Environment.php';
require_once __DIR__ . '/SupabaseClient.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailManager {
    
    /**
     * Configurar PHPMailer com as configurações SMTP
     */
    private static function configureSMTP($alternativeConfig = null) {
        // Configurações específicas para contexto web
        if (php_sapi_name() !== 'cli') {
            ini_set('max_execution_time', 120);
            set_time_limit(120);
        }
        
        $mail = new PHPMailer(true);
        
        try {
            // Carrega configurações do Environment
            Environment::load();
            
            $smtp_host = Environment::get('SMTP_HOST', 'smtp.hostinger.com');
            $smtp_port = Environment::get('SMTP_PORT', 587);
            $smtp_username = Environment::get('SMTP_USERNAME', 'contato@centroservice.com.br');
            $smtp_password = Environment::get('SMTP_PASSWORD', '');
            $smtp_from_email = Environment::get('SMTP_FROM_EMAIL', 'contato@centroservice.com.br');
            $smtp_from_name = Environment::get('SMTP_FROM_NAME', 'Centro Service - Aprenda Aqui');
            
            // Debug das configurações
            error_log("EmailManager Debug - Host: $smtp_host, Port: $smtp_port, Username: $smtp_username, From: $smtp_from_email");
            error_log("EmailManager Debug - Password length: " . strlen($smtp_password));
            
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_username;
            $mail->Password = $smtp_password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int)$smtp_port;
            $mail->CharSet = 'UTF-8';
            
            // Configurações específicas para contexto web
            $mail->Timeout = 60;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Habilitar debug SMTP apenas se necessário
            if (Environment::get('DEBUG_MODE', false) === 'true') {
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                $mail->Debugoutput = function($str, $level) {
                    error_log("SMTP Debug [$level]: $str");
                };
            }
            
            // Remetente
            $mail->setFrom($smtp_from_email, $smtp_from_name);
            
            return $mail;
            
        } catch (Exception $e) {
            error_log("Erro ao configurar SMTP: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tentar enviar email com configurações alternativas
     */
    private static function trySendWithFallback($mail, $maxRetries = 2) {
        $configs = [
            // Configuração padrão (STARTTLS porta 587)
            ['port' => 587, 'secure' => PHPMailer::ENCRYPTION_STARTTLS],
            // Alternativa SSL porta 465
            ['port' => 465, 'secure' => PHPMailer::ENCRYPTION_SMTPS]
        ];
        
        foreach ($configs as $index => $config) {
            try {
                error_log("EmailManager: Tentativa " . ($index + 1) . " - Porta {$config['port']}");
                
                $mail->Port = $config['port'];
                $mail->SMTPSecure = $config['secure'];
                
                $result = $mail->send();
                if ($result) {
                    error_log("EmailManager: Sucesso na tentativa " . ($index + 1));
                    return true;
                }
            } catch (Exception $e) {
                error_log("EmailManager: Falha na tentativa " . ($index + 1) . ": " . $e->getMessage());
                if ($index === count($configs) - 1) {
                    // Na última tentativa, não throw - apenas retornar false
                    error_log("EmailManager: Todas as tentativas de envio falharam");
                    return false;
                }
                // Reset para próxima tentativa
                $mail->clearAddresses();
                $mail->clearAttachments();
            }
        }
        
        return false;
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
            
            $result = self::trySendWithFallback($mail);
            if ($result) {
                error_log("EmailManager: Email de recuperação enviado para $email");
                return true;
            }
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar email de recuperação: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Gerar código temporário de recuperação (6 dígitos)
     */
    public static function generateResetCode($email, $method = 'email') {
        try {
            // Gerar código de 6 dígitos
            $code = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
            $expires = time() + 600; // 10 minutos
            
            // Salvar código no banco de dados
            $supabase = new SupabaseClient();
            $codeData = [
                'email' => $email,
                'code' => $code,
                'method' => $method, // 'email' ou 'whatsapp'
                'expires_at' => date('Y-m-d H:i:s', $expires),
                'created_at' => date('Y-m-d H:i:s'),
                'used' => false
            ];
            
            $result = $supabase->createPasswordResetCode($codeData);
            
            if ($result) {
                error_log("EmailManager: Código gerado para $email ($method): $code");
                return $code;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao gerar código de recuperação: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Gerar token de recuperação (para links por email)
     */
    public static function generateResetToken($userId, $email) {
        try {
            // Gerar token seguro localmente
            $token = bin2hex(random_bytes(32));
            $expires = time() + 3600; // 1 hora
            
            // Salvar token no banco de dados temporariamente
            $supabase = new SupabaseClient();
            $tokenData = [
                'email' => $email,
                'token' => $token,
                'expires_at' => date('Y-m-d H:i:s', $expires),
                'created_at' => date('Y-m-d H:i:s'),
                'used' => false
            ];
            
            $result = $supabase->createPasswordResetToken($tokenData);
            
            if ($result) {
                error_log("EmailManager: Token gerado para $email: $token");
                return $token;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao gerar token de recuperação: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se código temporário é válido
     */
    public static function verifyResetCode($email, $code) {
        try {
            $supabase = new SupabaseClient();
            $result = $supabase->verifyPasswordResetCode($email, $code);
            
            if ($result && isset($result['valid']) && $result['valid']) {
                return [
                    'email' => $result['email'],
                    'valid' => true
                ];
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao verificar código: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Redefinir senha usando código temporário
     */
    public static function resetPasswordWithCode($email, $code, $newPassword) {
        try {
            $codeData = self::verifyResetCode($email, $code);
            
            if (!$codeData || !$codeData['valid']) {
                return [
                    'success' => false,
                    'message' => 'Código inválido ou expirado'
                ];
            }
            
            // Atualizar senha no banco
            $supabase = new SupabaseClient();
            $updateResult = $supabase->updateUserPassword($email, $newPassword);
            
            if ($updateResult) {
                // Marcar código como usado
                $supabase->markCodeAsUsed($email, $code);
                
                error_log("EmailManager: Senha redefinida com código para $email");
                
                return [
                    'success' => true,
                    'message' => 'Senha redefinida com sucesso'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar senha. Tente novamente.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao redefinir senha com código: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente.'
            ];
        }
    }
    
    /**
     * Verificar se token é válido
     */
    public static function verifyResetToken($token) {
        try {
            $supabase = new SupabaseClient();
            $result = $supabase->verifyPasswordResetToken($token);
            
            if ($result && isset($result['valid']) && $result['valid']) {
                return [
                    'email' => $result['email'],
                    'valid' => true
                ];
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao verificar token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Redefinir senha usando token
     */
    public static function resetPassword($token, $newPassword) {
        try {
            $resetData = self::verifyResetToken($token);
            
            if (!$resetData || !$resetData['valid']) {
                return [
                    'success' => false,
                    'message' => 'Token inválido ou expirado'
                ];
            }
            
            $email = $resetData['email'];
            
            // Atualizar senha no banco
            $supabase = new SupabaseClient();
            $updateResult = $supabase->updateUserPassword($email, $newPassword);
            
            if ($updateResult) {
                // Marcar token como usado
                $supabase->markTokenAsUsed($token);
                
                error_log("EmailManager: Senha redefinida com sucesso para $email");
                
                return [
                    'success' => true,
                    'message' => 'Senha redefinida com sucesso'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar senha. Tente novamente.'
                ];
            }
            
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
            
            $result = self::trySendWithFallback($mail);
            if ($result) {
                error_log("EmailManager: Email de boas-vindas enviado para $email");
                return true;
            }
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar email de boas-vindas: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar código de recuperação por email
     */
    public static function sendRecoveryCodeByEmail($email, $codigo, $nome) {
        try {
            $mail = self::configureSMTP();
            if (!$mail) {
                return false;
            }
            
            $mail->addAddress($email, $nome);
            $mail->Subject = 'Código de Recuperação de Senha - Centro Service';
            
            $mail->isHTML(true);
            $mail->Body = "
                <h2>Recuperação de Senha</h2>
                <p>Olá, $nome!</p>
                <p>Você solicitou a recuperação de sua senha. Seu código de verificação é:</p>
                <div style='background-color: #f8f9fa; padding: 20px; text-align: center; margin: 20px 0; border-radius: 10px;'>
                    <h1 style='color: #dc3545; font-size: 36px; margin: 0; letter-spacing: 5px;'>$codigo</h1>
                </div>
                <p>Digite este código na página de recuperação para definir uma nova senha.</p>
                <p><strong>Importante:</strong> Este código é válido por 10 minutos.</p>
                <p>Se você não solicitou esta recuperação, ignore este email.</p>
                <hr>
                <p><small>Centro Service - Aprenda Aqui<br>contato@centroservice.com.br</small></p>
            ";
            
            $mail->AltBody = "Recuperação de Senha\n\nOlá, $nome!\n\nSeu código de recuperação é: $codigo\n\nDigite este código na página de recuperação.\n\nEste código é válido por 10 minutos.\n\nCentro Service - Aprenda Aqui";
            
            $result = self::trySendWithFallback($mail);
            if ($result) {
                error_log("EmailManager: Código de recuperação $codigo enviado por email para $email");
                return true;
            }
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar código de recuperação por email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar código de recuperação via WhatsApp
     */
    public static function sendRecoveryCodeByWhatsApp($whatsapp, $codigo, $nome) {
        try {
            require_once __DIR__ . '/WhatsAppManager.php';
            
            $message = "🔒 *Recuperação de Senha - Centro Service*\n\n";
            $message .= "Olá, *$nome*!\n\n";
            $message .= "Seu código de recuperação é:\n\n";
            $message .= "🔢 *$codigo*\n\n";
            $message .= "Digite este código na página de recuperação para definir uma nova senha.\n\n";
            $message .= "⏰ *Válido por 10 minutos*\n\n";
            $message .= "Se você não solicitou esta recuperação, ignore esta mensagem.";
            
            $result = WhatsAppManager::sendMessage($whatsapp, $message);
            
            if ($result) {
                error_log("EmailManager: Código de recuperação $codigo enviado via WhatsApp para $whatsapp");
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar código de recuperação via WhatsApp: " . $e->getMessage());
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
            
            $result = self::trySendWithFallback($mail);
            if ($result) {
                error_log("EmailManager: Código de ativação $codigo enviado para $email");
                return true;
            }
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar código de ativação: " . $e->getMessage());
            return false;
        }
    }
}
?>
