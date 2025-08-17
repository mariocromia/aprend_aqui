<?php
/**
 * Database Optimizer - Otimizações de Performance
 * 
 * Implementa pool de conexões, cache avançado e otimizações
 * para melhorar a performance do banco de dados.
 */

class DatabaseOptimizer {
    private static $instance = null;
    private $connectionPool = [];
    private $queryCache = [];
    private $cacheStats = ['hits' => 0, 'misses' => 0];
    private $maxConnections = 5;
    private $cacheTimeout = 300; // 5 minutos
    
    private function __construct() {
        // Singleton pattern
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtém uma conexão do pool ou cria uma nova
     */
    public function getConnection() {
        if (count($this->connectionPool) > 0) {
            return array_pop($this->connectionPool);
        }
        
        if (count($this->connectionPool) < $this->maxConnections) {
            return new SupabaseClient();
        }
        
        // Se pool está cheio, retorna nova conexão temporária
        return new SupabaseClient();
    }
    
    /**
     * Retorna conexão para o pool
     */
    public function returnConnection($connection) {
        if (count($this->connectionPool) < $this->maxConnections) {
            $this->connectionPool[] = $connection;
        }
    }
    
    /**
     * Cache avançado de consultas
     */
    public function cacheQuery($key, $data, $ttl = null) {
        $ttl = $ttl ?: $this->cacheTimeout;
        $this->queryCache[$key] = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => time()
        ];
    }
    
    /**
     * Recupera dados do cache
     */
    public function getCachedQuery($key) {
        if (!isset($this->queryCache[$key])) {
            $this->cacheStats['misses']++;
            return null;
        }
        
        $cache = $this->queryCache[$key];
        if (time() > $cache['expires']) {
            unset($this->queryCache[$key]);
            $this->cacheStats['misses']++;
            return null;
        }
        
        $this->cacheStats['hits']++;
        return $cache['data'];
    }
    
    /**
     * Limpa cache expirado
     */
    public function cleanExpiredCache() {
        $now = time();
        foreach ($this->queryCache as $key => $cache) {
            if ($now > $cache['expires']) {
                unset($this->queryCache[$key]);
            }
        }
    }
    
    /**
     * Executa consulta com cache automático
     */
    public function cachedQuery($endpoint, $method = 'GET', $data = null, $useServiceKey = false, $cacheKey = null, $ttl = null) {
        // Gerar chave de cache se não fornecida
        if (!$cacheKey) {
            $cacheKey = md5($endpoint . $method . serialize($data) . ($useServiceKey ? '1' : '0'));
        }
        
        // Apenas cachear consultas GET
        if ($method === 'GET') {
            $cached = $this->getCachedQuery($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }
        
        // Executar consulta
        $connection = $this->getConnection();
        try {
            $result = $connection->makeRequest($endpoint, $method, $data, $useServiceKey);
            
            // Cachear apenas consultas GET bem-sucedidas
            if ($method === 'GET' && $result['status'] === 200) {
                $this->cacheQuery($cacheKey, $result, $ttl);
            }
            
            return $result;
        } finally {
            $this->returnConnection($connection);
        }
    }
    
    /**
     * Batch de consultas para reduzir latência
     */
    public function batchQueries($queries) {
        $results = [];
        $connection = $this->getConnection();
        
        try {
            foreach ($queries as $key => $query) {
                $results[$key] = $connection->makeRequest(
                    $query['endpoint'],
                    $query['method'] ?? 'GET',
                    $query['data'] ?? null,
                    $query['useServiceKey'] ?? false
                );
            }
        } finally {
            $this->returnConnection($connection);
        }
        
        return $results;
    }
    
    /**
     * Otimizar consultas com limit e paginação
     */
    public function optimizedQuery($table, $filters = [], $select = '*', $limit = 100, $offset = 0, $order = null) {
        $endpoint = $table . '?';
        
        // Adicionar filtros
        $queryParams = [];
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $queryParams[] = $field . '=' . $value['operator'] . '.' . urlencode($value['value']);
            } else {
                $queryParams[] = $field . '=eq.' . urlencode($value);
            }
        }
        
        // Adicionar select
        if ($select !== '*') {
            $queryParams[] = 'select=' . $select;
        }
        
        // Adicionar order
        if ($order) {
            $queryParams[] = 'order=' . $order;
        }
        
        // Adicionar limit e offset
        $queryParams[] = 'limit=' . $limit;
        if ($offset > 0) {
            $queryParams[] = 'offset=' . $offset;
        }
        
        $endpoint .= implode('&', $queryParams);
        
        // Gerar chave de cache
        $cacheKey = 'opt_' . md5($endpoint);
        
        return $this->cachedQuery($endpoint, 'GET', null, true, $cacheKey);
    }
    
    /**
     * Estatísticas de cache
     */
    public function getCacheStats() {
        $total = $this->cacheStats['hits'] + $this->cacheStats['misses'];
        return [
            'hits' => $this->cacheStats['hits'],
            'misses' => $this->cacheStats['misses'],
            'hit_rate' => $total > 0 ? round(($this->cacheStats['hits'] / $total) * 100, 2) : 0,
            'cached_queries' => count($this->queryCache),
            'pool_size' => count($this->connectionPool)
        ];
    }
    
    /**
     * Preparar consulta para cenas com pré-carregamento
     */
    public function preloadCenaData($tipoAba) {
        // Carregar blocos e cenas em uma única operação otimizada
        $endpoint = "blocos_cenas?tipo_aba=eq.{$tipoAba}&ativo=eq.true&select=id,titulo,icone,ordem_exibicao,cenas(id,titulo,subtitulo,texto_prompt,valor_selecao,ordem_exibicao)&order=ordem_exibicao.asc";
        
        return $this->cachedQuery($endpoint, 'GET', null, true, "preload_{$tipoAba}", 600); // Cache por 10 minutos
    }
    
    /**
     * Limpar todo o cache
     */
    public function clearCache() {
        $this->queryCache = [];
        $this->cacheStats = ['hits' => 0, 'misses' => 0];
    }
    
    /**
     * Fechar todas as conexões do pool
     */
    public function closeConnections() {
        $this->connectionPool = [];
    }
    
    /**
     * Destructor para cleanup
     */
    public function __destruct() {
        $this->cleanExpiredCache();
    }
}
?>