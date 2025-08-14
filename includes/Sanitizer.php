<?php
/**
 * Classe para Sanitização e Validação de Inputs
 * Implementa métodos seguros para limpar e validar dados de entrada
 */
class Sanitizer {
    
    /**
     * Sanitiza string removendo caracteres perigosos
     */
    public static function sanitizeString($input, $maxLength = 255) {
        if (!is_string($input)) {
            return '';
        }
        
        // Remover caracteres de controle
        $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);
        
        // Limitar tamanho
        if (strlen($input) > $maxLength) {
            $input = substr($input, 0, $maxLength);
        }
        
        // Remover tags HTML
        $input = strip_tags($input);
        
        // Escapar caracteres especiais
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        return trim($input);
    }
    
    /**
     * Sanitiza email
     */
    public static function sanitizeEmail($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        return $email;
    }
    
    /**
     * Sanitiza número de telefone
     */
    public static function sanitizePhone($phone) {
        // Remover tudo exceto números
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Validar formato brasileiro
        if (strlen($phone) >= 10 && strlen($phone) <= 13) {
            return $phone;
        }
        
        return false;
    }
    
    /**
     * Sanitiza URL
     */
    public static function sanitizeUrl($url) {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        return $url;
    }
    
    /**
     * Sanitiza número inteiro
     */
    public static function sanitizeInt($input, $min = null, $max = null) {
        $int = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        
        if ($int === false) {
            return false;
        }
        
        if ($min !== null && $int < $min) {
            return false;
        }
        
        if ($max !== null && $int > $max) {
            return false;
        }
        
        return (int)$int;
    }
    
    /**
     * Valida formato de senha forte
     */
    public static function validatePassword($password) {
        // Mínimo 8 caracteres, pelo menos 1 letra maiúscula, 1 minúscula, 1 número
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/', $password);
    }
    
    /**
     * Valida se string não está vazia
     */
    public static function validateRequired($input) {
        return !empty(trim($input));
    }
    
    /**
     * Valida tamanho mínimo
     */
    public static function validateMinLength($input, $minLength) {
        return strlen(trim($input)) >= $minLength;
    }
    
    /**
     * Valida tamanho máximo
     */
    public static function validateMaxLength($input, $maxLength) {
        return strlen(trim($input)) <= $maxLength;
    }
}
?>
