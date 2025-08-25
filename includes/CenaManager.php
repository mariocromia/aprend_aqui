<?php
/**
 * Gerenciador de Cenas - Sistema de Cards Dinâmicos
 * 
 * Esta classe gerencia os blocos de cenas e cenas individuais
 * carregados do banco de dados para o gerador de prompt.
 * 
 * @author Sistema AprendAqui
 * @version 1.0
 */

require_once 'SupabaseClient.php';
require_once 'DatabaseOptimizer.php';

class CenaManager {
    private $supabase;
    private $optimizer;
    private $cache = [];
    private $cacheTime = 3600; // 1 hora em segundos
    
    public function __construct() {
        $this->supabase = new SupabaseClient();
        $this->optimizer = DatabaseOptimizer::getInstance();
    }
    
    /**
     * Obtém todos os blocos de cenas de um tipo de aba específico
     * 
     * @param string $tipoAba - ambiente, iluminacao, avatar, camera, voz, acao
     * @return array Lista de blocos ordenados
     */
    public function getBlocosPorTipo($tipoAba) {
        try {
            // Usar consulta otimizada com cache automático
            $result = $this->optimizer->optimizedQuery(
                'blocos_cenas',
                [
                    'tipo_aba' => $tipoAba,
                    'ativo' => true
                ],
                'id,titulo,icone,tipo_aba,ordem_exibicao',
                50, // limit
                0,  // offset
                'ordem_exibicao.asc,titulo.asc'
            );
            
            if ($result['status'] === 200 && isset($result['data'])) {
                return $result['data'];
            }
            
            return [];
            
        } catch (Exception $e) {
            error_log("Erro ao buscar blocos: " . $e->getMessage());
            return $this->getFallbackBlocos($tipoAba);
        }
    }
    
    /**
     * Obtém todas as cenas de um bloco específico
     * 
     * @param int $blocoId ID do bloco
     * @return array Lista de cenas ordenadas
     */
    public function getCenasPorBloco($blocoId) {
        try {
            // Usar consulta otimizada com cache automático
            $result = $this->optimizer->optimizedQuery(
                'cenas',
                [
                    'bloco_id' => $blocoId,
                    'ativo' => true
                ],
                'id,titulo,subtitulo,texto_prompt,valor_selecao,ordem_exibicao',
                100, // limit
                0,   // offset
                'ordem_exibicao.asc,titulo.asc'
            );
            
            if ($result['status'] === 200 && isset($result['data'])) {
                return $result['data'];
            }
            
            return [];
            
        } catch (Exception $e) {
            error_log("Erro ao buscar cenas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtém todos os dados de uma aba (blocos + cenas)
     * 
     * @param string $tipoAba Tipo da aba
     * @return array Estrutura completa da aba
     */
    public function getDadosCompletos($tipoAba) {
        try {
            // Tentar usar preload otimizado primeiro
            $result = $this->optimizer->preloadCenaData($tipoAba);
            
            if ($result['status'] === 200 && isset($result['data'])) {
                // Dados já vêm estruturados com relacionamentos
                return array_map(function($bloco) {
                    return [
                        'id' => $bloco['id'],
                        'titulo' => $bloco['titulo'],
                        'icone' => $bloco['icone'],
                        'ordem' => $bloco['ordem_exibicao'],
                        'cenas' => array_map(function($cena) {
                            return [
                                'id' => $cena['id'],
                                'titulo' => $cena['titulo'],
                                'subtitulo' => $cena['subtitulo'],
                                'texto_prompt' => $cena['texto_prompt'],
                                'valor_selecao' => $cena['valor_selecao'],
                                'ordem' => $cena['ordem_exibicao']
                            ];
                        }, $cena['cenas'] ?? [])
                    ];
                }, $result['data']);
            }
            
            // Fallback para método tradicional se preload falhar
            $blocos = $this->getBlocosPorTipo($tipoAba);
            $dados = [];
            
            foreach ($blocos as $bloco) {
                $cenas = $this->getCenasPorBloco($bloco['id']);
                $dados[] = [
                    'id' => $bloco['id'],
                    'titulo' => $bloco['titulo'],
                    'icone' => $bloco['icone'],
                    'ordem' => $bloco['ordem_exibicao'],
                    'cenas' => array_map(function($cena) {
                        return [
                            'id' => $cena['id'],
                            'titulo' => $cena['titulo'],
                            'subtitulo' => $cena['subtitulo'],
                            'texto_prompt' => $cena['texto_prompt'],
                            'valor_selecao' => $cena['valor_selecao'],
                            'ordem' => $cena['ordem_exibicao']
                        ];
                    }, $cenas)
                ];
            }
            
            return $dados;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar dados completos: " . $e->getMessage());
            return $this->getFallbackDadosCompletos($tipoAba);
        }
    }
    
    /**
     * Organiza os dados brutos em estrutura hierárquica
     * 
     * @param array $dadosBrutos Dados do banco
     * @return array Estrutura organizada
     */
    private function organizarDadosCompletos($dadosBrutos) {
        $blocos = [];
        
        foreach ($dadosBrutos as $row) {
            $blocoId = $row['bloco_id'];
            
            // Inicializar bloco se não existe
            if (!isset($blocos[$blocoId])) {
                $blocos[$blocoId] = [
                    'id' => $blocoId,
                    'titulo' => $row['bloco_titulo'],
                    'icone' => $row['bloco_icone'],
                    'ordem' => $row['bloco_ordem'],
                    'cenas' => []
                ];
            }
            
            // Adicionar cena se existe
            if ($row['cena_id']) {
                $blocos[$blocoId]['cenas'][] = [
                    'id' => $row['cena_id'],
                    'titulo' => $row['cena_titulo'],
                    'subtitulo' => $row['cena_subtitulo'],
                    'texto_prompt' => $row['texto_prompt'],
                    'valor_selecao' => $row['valor_selecao'],
                    'ordem' => $row['cena_ordem']
                ];
            }
        }
        
        // Converter para array indexado e ordenar
        $resultado = array_values($blocos);
        usort($resultado, function($a, $b) {
            return $a['ordem'] <=> $b['ordem'];
        });
        
        return $resultado;
    }
    
    /**
     * Busca uma cena específica pelo valor de seleção
     * 
     * @param string $valorSelecao Valor único da cena
     * @return array|null Dados da cena ou null se não encontrada
     */
    public function getCenaPorValor($valorSelecao) {
        try {
            $endpoint = "cenas?valor_selecao=eq.{$valorSelecao}&ativo=eq.true&select=*&limit=1";
            $result = $this->supabase->makeRequest($endpoint, 'GET', null, true);
            
            if ($result['status'] === 200 && isset($result['data']) && !empty($result['data'])) {
                return $result['data'][0];
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar cena por valor: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Limpa o cache
     */
    public function limparCache() {
        $this->cache = [];
    }
    
    /**
     * Verifica se o cache é válido
     * 
     * @param string $key Chave do cache
     * @return bool
     */
    private function isValidCache($key) {
        return isset($this->cache[$key]) && 
               (time() - $this->cache[$key]['timestamp']) < $this->cacheTime;
    }
    
    /**
     * Define dados no cache
     * 
     * @param string $key Chave
     * @param mixed $data Dados
     */
    private function setCache($key, $data) {
        $this->cache[$key] = [
            'data' => $data,
            'timestamp' => time()
        ];
    }
    
    /**
     * Dados de fallback para quando o banco falha (apenas ambiente como exemplo)
     */
    private function getFallbackBlocos($tipoAba) {
        $fallbacks = [
            'ambiente' => [
                ['id' => 0, 'titulo' => 'Natureza', 'icone' => 'nature', 'ordem_exibicao' => 1],
                ['id' => 0, 'titulo' => 'Urbano', 'icone' => 'location_city', 'ordem_exibicao' => 2]
            ],
            'acao' => [
                ['id' => 0, 'titulo' => 'Corporais', 'icone' => 'directions_run', 'ordem_exibicao' => 1],
                ['id' => 0, 'titulo' => 'Expressões', 'icone' => 'sentiment_satisfied', 'ordem_exibicao' => 2]
            ]
        ];
        
        return $fallbacks[$tipoAba] ?? [];
    }
    
    /**
     * Dados de fallback completos
     */
    private function getFallbackDadosCompletos($tipoAba) {
        // Implementar fallback básico se necessário
        return [];
    }
    
    /**
     * Método para administração - inserir novo bloco
     */
    public function inserirBloco($titulo, $icone, $tipoAba, $ordem = 0) {
        try {
            $data = [
                'titulo' => $titulo,
                'icone' => $icone,
                'tipo_aba' => $tipoAba,
                'ordem_exibicao' => $ordem,
                'ativo' => true
            ];
            
            $result = $this->supabase->makeRequest('blocos_cenas', 'POST', $data, true);
            $this->limparCache(); // Limpar cache após inserção
            
            if ($result['status'] === 201) {
                return $result['data'][0] ?? true; // Retorna dados do bloco criado ou true
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao inserir bloco: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Método para administração - atualizar bloco existente
     */
    public function atualizarBloco($id, $titulo, $icone, $tipoAba, $ordem = 0, $ativo = true) {
        try {
            $data = [
                'titulo' => $titulo,
                'icone' => $icone,
                'tipo_aba' => $tipoAba,
                'ordem_exibicao' => $ordem,
                'ativo' => $ativo
            ];
            
            $result = $this->supabase->makeRequest("blocos_cenas?id=eq.{$id}", 'PATCH', $data, true);
            $this->limparCache(); // Limpar cache após atualização
            
            return $result['status'] === 200 || $result['status'] === 204;
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar bloco: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Método para administração - excluir bloco
     */
    public function excluirBloco($id) {
        try {
            // Primeiro excluir todas as cenas do bloco (CASCADE deve fazer isso automaticamente)
            $result = $this->supabase->makeRequest("blocos_cenas?id=eq.{$id}", 'DELETE', null, true);
            $this->limparCache(); // Limpar cache após exclusão
            
            return $result['status'] === 200 || $result['status'] === 204;
            
        } catch (Exception $e) {
            error_log("Erro ao excluir bloco: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Método para administração - buscar bloco por ID
     */
    public function getBlocoPorId($id) {
        try {
            $result = $this->supabase->makeRequest("blocos_cenas?id=eq.{$id}&select=*", 'GET', null, true);
            
            if ($result['status'] === 200 && !empty($result['data'])) {
                return $result['data'][0];
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar bloco por ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Método para administração - inserir nova cena
     */
    public function inserirCena($blocoId, $titulo, $subtitulo, $textoPrompt, $valorSelecao, $ordem = 0) {
        try {
            // Verificar se valor_selecao é único
            if ($this->valorSelecaoExiste($valorSelecao)) {
                throw new Exception("Valor de seleção '{$valorSelecao}' já existe");
            }
            
            $data = [
                'bloco_id' => $blocoId,
                'titulo' => $titulo,
                'subtitulo' => $subtitulo,
                'texto_prompt' => $textoPrompt,
                'valor_selecao' => $valorSelecao,
                'ordem_exibicao' => $ordem,
                'ativo' => true
            ];
            
            $result = $this->supabase->makeRequest('cenas', 'POST', $data, true);
            $this->limparCache(); // Limpar cache após inserção
            
            if ($result['status'] === 201) {
                return $result['data'][0] ?? true; // Retorna dados da cena criada ou true
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao inserir cena: " . $e->getMessage());
            throw $e; // Re-throw para manter a mensagem específica
        }
    }
    
    /**
     * Método para administração - atualizar cena existente
     */
    public function atualizarCena($id, $blocoId, $titulo, $subtitulo, $textoPrompt, $valorSelecao, $ordem = 0, $ativo = true) {
        try {
            // Verificar se valor_selecao é único (excluindo a própria cena)
            if ($this->valorSelecaoExiste($valorSelecao, $id)) {
                throw new Exception("Valor de seleção '{$valorSelecao}' já existe");
            }
            
            $data = [
                'bloco_id' => $blocoId,
                'titulo' => $titulo,
                'subtitulo' => $subtitulo,
                'texto_prompt' => $textoPrompt,
                'valor_selecao' => $valorSelecao,
                'ordem_exibicao' => $ordem,
                'ativo' => $ativo
            ];
            
            $result = $this->supabase->makeRequest("cenas?id=eq.{$id}", 'PATCH', $data, true);
            $this->limparCache(); // Limpar cache após atualização
            
            return $result['status'] === 200 || $result['status'] === 204;
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar cena: " . $e->getMessage());
            throw $e; // Re-throw para manter a mensagem específica
        }
    }
    
    /**
     * Método para administração - excluir cena
     */
    public function excluirCena($id) {
        try {
            $result = $this->supabase->makeRequest("cenas?id=eq.{$id}", 'DELETE', null, true);
            $this->limparCache(); // Limpar cache após exclusão
            
            return $result['status'] === 200 || $result['status'] === 204;
            
        } catch (Exception $e) {
            error_log("Erro ao excluir cena: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Método para administração - buscar cena por ID
     */
    public function getCenaPorId($id) {
        try {
            $result = $this->supabase->makeRequest("cenas?id=eq.{$id}&select=*", 'GET', null, true);
            
            if ($result['status'] === 200 && !empty($result['data'])) {
                return $result['data'][0];
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar cena por ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verificar se valor de seleção já existe
     */
    public function valorSelecaoExiste($valorSelecao, $excluirId = null) {
        try {
            $endpoint = "cenas?valor_selecao=eq.{$valorSelecao}&select=id";
            if ($excluirId) {
                $endpoint .= "&id=neq.{$excluirId}";
            }
            
            $result = $this->supabase->makeRequest($endpoint, 'GET', null, true);
            
            return $result['status'] === 200 && !empty($result['data']);
            
        } catch (Exception $e) {
            error_log("Erro ao verificar valor de seleção: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Listar todos os blocos (para admin)
     */
    public function listarTodosBlocos() {
        try {
            $result = $this->supabase->makeRequest("blocos_cenas?order=tipo_aba.asc,ordem_exibicao.asc&select=*", 'GET', null, true);
            
            if ($result['status'] === 200) {
                return $result['data'] ?? [];
            }
            
            return [];
            
        } catch (Exception $e) {
            error_log("Erro ao listar todos os blocos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Listar todas as cenas (para admin)
     */
    public function listarTodasCenas() {
        try {
            $result = $this->supabase->makeRequest("cenas?order=bloco_id.asc,ordem_exibicao.asc&select=*", 'GET', null, true);
            
            if ($result['status'] === 200) {
                return $result['data'] ?? [];
            }
            
            return [];
            
        } catch (Exception $e) {
            error_log("Erro ao listar todas as cenas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Contar cenas por bloco
     */
    public function contarCenasPorBloco($blocoId) {
        try {
            $result = $this->supabase->makeRequest("cenas?bloco_id=eq.{$blocoId}&select=id", 'GET', null, true);
            
            if ($result['status'] === 200) {
                return count($result['data'] ?? []);
            }
            
            return 0;
            
        } catch (Exception $e) {
            error_log("Erro ao contar cenas por bloco: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Reordenar blocos
     */
    public function reordenarBlocos($ordenacao) {
        try {
            $sucesso = true;
            
            foreach ($ordenacao as $item) {
                $result = $this->supabase->makeRequest(
                    "blocos_cenas?id=eq.{$item['id']}", 
                    'PATCH', 
                    ['ordem_exibicao' => $item['ordem']], 
                    true
                );
                
                if ($result['status'] !== 200 && $result['status'] !== 204) {
                    $sucesso = false;
                }
            }
            
            if ($sucesso) {
                $this->limparCache();
            }
            
            return $sucesso;
            
        } catch (Exception $e) {
            error_log("Erro ao reordenar blocos: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reordenar cenas
     */
    public function reordenarCenas($ordenacao) {
        try {
            $sucesso = true;
            
            foreach ($ordenacao as $item) {
                $result = $this->supabase->makeRequest(
                    "cenas?id=eq.{$item['id']}", 
                    'PATCH', 
                    ['ordem_exibicao' => $item['ordem']], 
                    true
                );
                
                if ($result['status'] !== 200 && $result['status'] !== 204) {
                    $sucesso = false;
                }
            }
            
            if ($sucesso) {
                $this->limparCache();
            }
            
            return $sucesso;
            
        } catch (Exception $e) {
            error_log("Erro ao reordenar cenas: " . $e->getMessage());
            return false;
        }
    }
}
?>