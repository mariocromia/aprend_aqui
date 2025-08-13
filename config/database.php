<?php
/**
 * Configuração do Banco de Dados - CentroService
 * 
 * Este arquivo contém as configurações para conexão com banco de dados MySQL
 * e a estrutura da tabela para salvar os contatos do formulário.
 * 
 * IMPORTANTE: Este arquivo é opcional. Se não quiser usar banco de dados,
 * o formulário funcionará apenas com envio de email.
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');           // Host do banco de dados
define('DB_NAME', 'centroservice');       // Nome do banco de dados
define('DB_USER', 'root');                // Usuário do banco de dados
define('DB_PASS', '');                    // Senha do banco de dados
define('DB_CHARSET', 'utf8mb4');          // Charset do banco de dados
define('DB_COLLATE', 'utf8mb4_unicode_ci'); // Collation do banco de dados

// Configurações de segurança
define('DB_SSL', false);                  // Usar SSL para conexão
define('DB_SSL_VERIFY', false);           // Verificar certificado SSL
define('DB_TIMEOUT', 30);                 // Timeout da conexão em segundos

// Configurações de logging
define('DB_LOG_QUERIES', true);           // Logar todas as queries
define('DB_LOG_ERRORS', true);            // Logar erros de banco
define('DB_LOG_FILE', '../logs/db.log');  // Arquivo de log

/**
 * Classe para gerenciar conexão com banco de dados
 */
class Database {
    private static $instance = null;
    private $connection;
    private $connected = false;
    
    /**
     * Construtor privado (Singleton pattern)
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Obtém instância única da classe
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Estabelece conexão com o banco de dados
     */
    private function connect() {
        try {
            // DSN (Data Source Name)
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            // Opções do PDO
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATE,
                PDO::ATTR_TIMEOUT => DB_TIMEOUT,
                PDO::ATTR_PERSISTENT => false
            ];
            
            // Configurações SSL se habilitado
            if (DB_SSL) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = '/path/to/ca-cert.pem';
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = DB_SSL_VERIFY;
            }
            
            // Estabelece conexão
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->connected = true;
            
            // Log de sucesso
            if (DB_LOG_QUERIES) {
                $this->log("Conexão estabelecida com sucesso");
            }
            
        } catch (PDOException $e) {
            $this->connected = false;
            $this->log("Erro na conexão: " . $e->getMessage(), 'ERROR');
            
            // Em produção, não exponha detalhes do erro
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
                throw new Exception('Erro na conexão com banco de dados');
            } else {
                throw $e;
            }
        }
    }
    
    /**
     * Obtém a conexão PDO
     */
    public function getConnection() {
        if (!$this->connected) {
            $this->connect();
        }
        return $this->connection;
    }
    
    /**
     * Verifica se está conectado
     */
    public function isConnected() {
        return $this->connected;
    }
    
    /**
     * Executa uma query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            
            // Log da query
            if (DB_LOG_QUERIES) {
                $this->log("Query executada: " . $sql . " | Params: " . json_encode($params));
            }
            
            return $stmt;
            
        } catch (PDOException $e) {
            $this->log("Erro na query: " . $e->getMessage() . " | SQL: " . $sql, 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Executa uma query de inserção
     */
    public function insert($table, $data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($data);
            
            return $this->getConnection()->lastInsertId();
            
        } catch (PDOException $e) {
            $this->log("Erro na inserção: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Executa uma query de atualização
     */
    public function update($table, $data, $where, $whereParams = []) {
        try {
            $setClause = [];
            foreach (array_keys($data) as $column) {
                $setClause[] = "{$column} = :{$column}";
            }
            
            $sql = "UPDATE {$table} SET " . implode(', ', $setClause) . " WHERE {$where}";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute(array_merge($data, $whereParams));
            
            return $stmt->rowCount();
            
        } catch (PDOException $e) {
            $this->log("Erro na atualização: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Executa uma query de exclusão
     */
    public function delete($table, $where, $params = []) {
        try {
            $sql = "DELETE FROM {$table} WHERE {$where}";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount();
            
        } catch (PDOException $e) {
            $this->log("Erro na exclusão: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Busca um registro
     */
    public function findOne($table, $where, $params = []) {
        try {
            $sql = "SELECT * FROM {$table} WHERE {$where} LIMIT 1";
            
            $stmt = $this->query($sql, $params);
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            $this->log("Erro na busca: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Busca múltiplos registros
     */
    public function find($table, $where = '1', $params = [], $orderBy = '', $limit = '') {
        try {
            $sql = "SELECT * FROM {$table} WHERE {$where}";
            
            if ($orderBy) {
                $sql .= " ORDER BY {$orderBy}";
            }
            
            if ($limit) {
                $sql .= " LIMIT {$limit}";
            }
            
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            $this->log("Erro na busca múltipla: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Conta registros
     */
    public function count($table, $where = '1', $params = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$table} WHERE {$where}";
            
            $stmt = $this->query($sql, $params);
            $result = $stmt->fetch();
            
            return (int) $result['total'];
            
        } catch (PDOException $e) {
            $this->log("Erro na contagem: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Inicia uma transação
     */
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }
    
    /**
     * Confirma uma transação
     */
    public function commit() {
        return $this->getConnection()->commit();
    }
    
    /**
     * Reverte uma transação
     */
    public function rollback() {
        return $this->getConnection()->rollback();
    }
    
    /**
     * Fecha a conexão
     */
    public function close() {
        $this->connection = null;
        $this->connected = false;
    }
    
    /**
     * Log de mensagens
     */
    private function log($message, $level = 'INFO') {
        if (!DB_LOG_ERRORS && $level === 'ERROR') {
            return;
        }
        
        $logDir = dirname(DB_LOG_FILE);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        file_put_contents(DB_LOG_FILE, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Destrutor
     */
    public function __destruct() {
        $this->close();
    }
}

/**
 * Função para criar a tabela de contatos
 * Execute esta função uma vez para criar a estrutura do banco
 */
function createContactsTable() {
    try {
        $db = Database::getInstance();
        
        $sql = "
        CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            service ENUM('institucional', 'vsl', 'reels', 'ia') NOT NULL,
            message TEXT NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_service (service),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $db->query($sql);
        
        echo "Tabela 'contacts' criada com sucesso!\n";
        
    } catch (Exception $e) {
        echo "Erro ao criar tabela: " . $e->getMessage() . "\n";
    }
}

/**
 * Função para criar a tabela de logs
 */
function createLogsTable() {
    try {
        $db = Database::getInstance();
        
        $sql = "
        CREATE TABLE IF NOT EXISTS system_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            level ENUM('INFO', 'WARNING', 'ERROR', 'DEBUG') NOT NULL,
            message TEXT NOT NULL,
            context JSON,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_level (level),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $db->query($sql);
        
        echo "Tabela 'system_logs' criada com sucesso!\n";
        
    } catch (Exception $e) {
        echo "Erro ao criar tabela: " . $e->getMessage() . "\n";
    }
}

/**
 * Função para criar a tabela de configurações
 */
function createSettingsTable() {
    try {
        $db = Database::getInstance();
        
        $sql = "
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            description TEXT,
            is_public BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_key (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $db->query($sql);
        
        // Inserir configurações padrão
        $defaultSettings = [
            ['site_name', 'CentroService', 'Nome do site'],
            ['site_description', 'Criação de vídeos com IA', 'Descrição do site'],
            ['contact_email', 'contato@centroservice.com.br', 'Email de contato'],
            ['whatsapp_number', '+5511999999999', 'Número do WhatsApp'],
            ['maintenance_mode', '0', 'Modo de manutenção (0=off, 1=on)'],
            ['google_analytics_id', '', 'ID do Google Analytics'],
            ['facebook_pixel_id', '', 'ID do Facebook Pixel']
        ];
        
        foreach ($defaultSettings as $setting) {
            $db->query(
                "INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES (?, ?, ?)",
                $setting
            );
        }
        
        echo "Tabela 'settings' criada com sucesso!\n";
        
    } catch (Exception $e) {
        echo "Erro ao criar tabela: " . $e->getMessage() . "\n";
    }
}

/**
 * Função para criar todas as tabelas
 */
function createAllTables() {
    echo "Criando estrutura do banco de dados...\n";
    
    createContactsTable();
    createLogsTable();
    createSettingsTable();
    
    echo "Estrutura do banco criada com sucesso!\n";
}

/**
 * Função para testar a conexão
 */
function testConnection() {
    try {
        $db = Database::getInstance();
        
        if ($db->isConnected()) {
            echo "✅ Conexão com banco de dados estabelecida com sucesso!\n";
            echo "📊 Servidor: " . DB_HOST . "\n";
            echo "🗄️  Banco: " . DB_NAME . "\n";
            echo "👤 Usuário: " . DB_USER . "\n";
        } else {
            echo "❌ Falha na conexão com banco de dados\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Erro: " . $e->getMessage() . "\n";
    }
}

// Se executado diretamente, criar tabelas
if (php_sapi_name() === 'cli') {
    echo "🚀 CentroService - Configuração do Banco de Dados\n";
    echo "================================================\n\n";
    
    // Testar conexão
    testConnection();
    echo "\n";
    
    // Perguntar se quer criar as tabelas
    echo "Deseja criar as tabelas do banco de dados? (s/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) === 's') {
        createAllTables();
    } else {
        echo "Operação cancelada.\n";
    }
}

// Exemplo de uso em outros arquivos:
/*
// Incluir este arquivo
require_once 'config/database.php';

// Usar o banco de dados
try {
    $db = Database::getInstance();
    
    // Inserir contato
    $contactId = $db->insert('contacts', [
        'name' => 'João Silva',
        'email' => 'joao@email.com',
        'phone' => '+5511999999999',
        'service' => 'institucional',
        'message' => 'Gostaria de um orçamento',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'N/A'
    ]);
    
    echo "Contato inserido com ID: " . $contactId;
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
*/
?>
