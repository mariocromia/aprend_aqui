<?php
/**
 * AvatarManager - Gerenciador completo de avatares
 * Sistema avançado para criação, edição e gerenciamento de avatares/personagens
 */

class AvatarManager {
    private $supabase;
    private $usuario_id;
    
    public function __construct($usuario_id = null) {
        $this->supabase = new SupabaseClient();
        $this->usuario_id = $usuario_id;
    }
    
    /**
     * Criar novo avatar
     */
    public function criarAvatar($dados) {
        try {
            // Validar dados obrigatórios
            $this->validarDadosAvatar($dados);
            
            // Preparar dados para inserção
            $avatarData = [
                'nome' => $this->sanitizar($dados['nome']),
                'descricao' => $this->sanitizar($dados['descricao'] ?? ''),
                'genero' => $dados['genero'] ?? 'neutro',
                'idade_categoria' => $dados['idade_categoria'] ?? 'adulto',
                'etnia' => $this->sanitizar($dados['etnia'] ?? ''),
                'tipo_fisico' => $this->sanitizar($dados['tipo_fisico'] ?? ''),
                'altura' => $this->sanitizar($dados['altura'] ?? ''),
                'peso' => $this->sanitizar($dados['peso'] ?? ''),
                'cor_cabelo' => $this->sanitizar($dados['cor_cabelo'] ?? ''),
                'estilo_cabelo' => $this->sanitizar($dados['estilo_cabelo'] ?? ''),
                'cor_olhos' => $this->sanitizar($dados['cor_olhos'] ?? ''),
                'cor_pele' => $this->sanitizar($dados['cor_pele'] ?? ''),
                'expressao_facial' => $this->sanitizar($dados['expressao_facial'] ?? ''),
                'postura' => $this->sanitizar($dados['postura'] ?? ''),
                'profissao' => $this->sanitizar($dados['profissao'] ?? ''),
                'personalidade' => $this->sanitizar($dados['personalidade'] ?? ''),
                'vestuario' => $this->sanitizar($dados['vestuario'] ?? ''),
                'acessorios' => $this->sanitizar($dados['acessorios'] ?? ''),
                'tatuagens_marcas' => $this->sanitizar($dados['tatuagens_marcas'] ?? ''),
                'habilidades_especiais' => $this->sanitizar($dados['habilidades_especiais'] ?? ''),
                'historia_background' => $this->sanitizar($dados['historia_background'] ?? ''),
                'tags' => $this->processarTags($dados['tags'] ?? []),
                'criado_por' => $this->usuario_id,
                'publico' => $dados['publico'] ?? false,
                'ativo' => true
            ];
            
            // Inserir avatar no banco
            $response = $this->supabase->insert('avatares', $avatarData);
            
            if ($response && isset($response[0]['id'])) {
                $avatarId = $response[0]['id'];
                
                // Processar categorias
                if (!empty($dados['categorias'])) {
                    $this->adicionarCategoriasAvatar($avatarId, $dados['categorias']);
                }
                
                // Processar características físicas
                if (!empty($dados['caracteristicas'])) {
                    $this->adicionarCaracteristicasAvatar($avatarId, $dados['caracteristicas']);
                }
                
                // Processar vestimentas
                if (!empty($dados['vestimentas'])) {
                    $this->adicionarVestimentasAvatar($avatarId, $dados['vestimentas']);
                }
                
                return [
                    'success' => true,
                    'avatar_id' => $avatarId,
                    'message' => 'Avatar criado com sucesso!'
                ];
            }
            
            throw new Exception('Erro ao criar avatar no banco de dados');
            
        } catch (Exception $e) {
            error_log("Erro ao criar avatar: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao criar avatar: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Buscar avatares com filtros
     */
    public function buscarAvatares($filtros = []) {
        try {
            $query = "SELECT * FROM avatares_completos WHERE ativo = true";
            $params = [];
            
            // Aplicar filtros
            if (!empty($filtros['categoria'])) {
                $query .= " AND ? = ANY(categorias)";
                $params[] = $filtros['categoria'];
            }
            
            if (!empty($filtros['genero'])) {
                $query .= " AND genero = ?";
                $params[] = $filtros['genero'];
            }
            
            if (!empty($filtros['idade_categoria'])) {
                $query .= " AND idade_categoria = ?";
                $params[] = $filtros['idade_categoria'];
            }
            
            if (!empty($filtros['publico_apenas'])) {
                $query .= " AND publico = true";
            }
            
            if (!empty($filtros['usuario_proprio']) && $this->usuario_id) {
                $query .= " AND criado_por = ?";
                $params[] = $this->usuario_id;
            }
            
            if (!empty($filtros['busca'])) {
                $query .= " AND (nome ILIKE ? OR descricao ILIKE ? OR ? = ANY(tags))";
                $busca = '%' . $filtros['busca'] . '%';
                $params[] = $busca;
                $params[] = $busca;
                $params[] = $filtros['busca'];
            }
            
            // Ordenação
            $ordem = $filtros['ordem'] ?? 'criado_em DESC';
            $query .= " ORDER BY " . $ordem;
            
            // Limite
            $limite = $filtros['limite'] ?? 50;
            $query .= " LIMIT " . intval($limite);
            
            return $this->supabase->query($query, $params);
            
        } catch (Exception $e) {
            error_log("Erro ao buscar avatares: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter avatar completo por ID
     */
    public function obterAvatar($avatarId) {
        try {
            // Buscar avatar básico
            $avatar = $this->supabase->select('avatares', '*', ['id' => $avatarId]);
            
            if (empty($avatar)) {
                return null;
            }
            
            $avatarCompleto = $avatar[0];
            
            // Buscar categorias
            $categorias = $this->supabase->query(
                "SELECT c.* FROM categorias_avatares c 
                 INNER JOIN avatar_categorias ac ON c.id = ac.categoria_id 
                 WHERE ac.avatar_id = ?",
                [$avatarId]
            );
            $avatarCompleto['categorias'] = $categorias;
            
            // Buscar características
            $caracteristicas = $this->supabase->select(
                'caracteristicas_fisicas', 
                '*', 
                ['avatar_id' => $avatarId],
                'ordem_exibicao ASC'
            );
            $avatarCompleto['caracteristicas'] = $caracteristicas;
            
            // Buscar vestimentas
            $vestimentas = $this->supabase->select(
                'avatar_vestimentas', 
                '*', 
                ['avatar_id' => $avatarId],
                'ordem_exibicao ASC'
            );
            $avatarCompleto['vestimentas'] = $vestimentas;
            
            return $avatarCompleto;
            
        } catch (Exception $e) {
            error_log("Erro ao obter avatar: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Atualizar avatar
     */
    public function atualizarAvatar($avatarId, $dados) {
        try {
            // Verificar permissão
            if (!$this->verificarPermissaoAvatar($avatarId)) {
                throw new Exception('Sem permissão para editar este avatar');
            }
            
            // Validar dados
            $this->validarDadosAvatar($dados);
            
            // Preparar dados para atualização
            $avatarData = [
                'nome' => $this->sanitizar($dados['nome']),
                'descricao' => $this->sanitizar($dados['descricao'] ?? ''),
                'genero' => $dados['genero'] ?? 'neutro',
                'idade_categoria' => $dados['idade_categoria'] ?? 'adulto',
                'etnia' => $this->sanitizar($dados['etnia'] ?? ''),
                'tipo_fisico' => $this->sanitizar($dados['tipo_fisico'] ?? ''),
                'altura' => $this->sanitizar($dados['altura'] ?? ''),
                'peso' => $this->sanitizar($dados['peso'] ?? ''),
                'cor_cabelo' => $this->sanitizar($dados['cor_cabelo'] ?? ''),
                'estilo_cabelo' => $this->sanitizar($dados['estilo_cabelo'] ?? ''),
                'cor_olhos' => $this->sanitizar($dados['cor_olhos'] ?? ''),
                'cor_pele' => $this->sanitizar($dados['cor_pele'] ?? ''),
                'expressao_facial' => $this->sanitizar($dados['expressao_facial'] ?? ''),
                'postura' => $this->sanitizar($dados['postura'] ?? ''),
                'profissao' => $this->sanitizar($dados['profissao'] ?? ''),
                'personalidade' => $this->sanitizar($dados['personalidade'] ?? ''),
                'vestuario' => $this->sanitizar($dados['vestuario'] ?? ''),
                'acessorios' => $this->sanitizar($dados['acessorios'] ?? ''),
                'tatuagens_marcas' => $this->sanitizar($dados['tatuagens_marcas'] ?? ''),
                'habilidades_especiais' => $this->sanitizar($dados['habilidades_especiais'] ?? ''),
                'historia_background' => $this->sanitizar($dados['historia_background'] ?? ''),
                'tags' => $this->processarTags($dados['tags'] ?? []),
                'publico' => $dados['publico'] ?? false
            ];
            
            // Atualizar no banco
            $success = $this->supabase->update('avatares', $avatarData, ['id' => $avatarId]);
            
            if ($success) {
                // Atualizar categorias se fornecidas
                if (isset($dados['categorias'])) {
                    $this->atualizarCategoriasAvatar($avatarId, $dados['categorias']);
                }
                
                // Atualizar características se fornecidas
                if (isset($dados['caracteristicas'])) {
                    $this->atualizarCaracteristicasAvatar($avatarId, $dados['caracteristicas']);
                }
                
                // Atualizar vestimentas se fornecidas
                if (isset($dados['vestimentas'])) {
                    $this->atualizarVestimentasAvatar($avatarId, $dados['vestimentas']);
                }
                
                return [
                    'success' => true,
                    'message' => 'Avatar atualizado com sucesso!'
                ];
            }
            
            throw new Exception('Erro ao atualizar avatar no banco de dados');
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar avatar: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao atualizar avatar: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Excluir avatar
     */
    public function excluirAvatar($avatarId) {
        try {
            // Verificar permissão
            if (!$this->verificarPermissaoAvatar($avatarId)) {
                throw new Exception('Sem permissão para excluir este avatar');
            }
            
            // Soft delete - apenas marcar como inativo
            $success = $this->supabase->update('avatares', ['ativo' => false], ['id' => $avatarId]);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Avatar excluído com sucesso!'
                ];
            }
            
            throw new Exception('Erro ao excluir avatar');
            
        } catch (Exception $e) {
            error_log("Erro ao excluir avatar: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao excluir avatar: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obter categorias disponíveis
     */
    public function obterCategorias() {
        try {
            return $this->supabase->select(
                'categorias_avatares', 
                '*', 
                ['ativo' => true],
                'ordem_exibicao ASC'
            );
        } catch (Exception $e) {
            error_log("Erro ao obter categorias: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter presets populares
     */
    public function obterPresets($categoria = null) {
        try {
            $filtros = ['ativo' => true];
            if ($categoria) {
                $filtros['categoria'] = $categoria;
            }
            
            return $this->supabase->select(
                'avatar_presets', 
                '*', 
                $filtros,
                'popularidade DESC'
            );
        } catch (Exception $e) {
            error_log("Erro ao obter presets: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Registrar uso de avatar
     */
    public function registrarUsoAvatar($avatarId, $configuracao = [], $promptGerado = '') {
        try {
            $this->supabase->insert('avatar_historico', [
                'usuario_id' => $this->usuario_id,
                'avatar_id' => $avatarId,
                'configuracao_usada' => json_encode($configuracao),
                'prompt_gerado' => $promptGerado
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao registrar uso de avatar: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Métodos auxiliares privados
     */
    
    private function validarDadosAvatar($dados) {
        if (empty($dados['nome'])) {
            throw new Exception('Nome do avatar é obrigatório');
        }
        
        if (strlen($dados['nome']) > 100) {
            throw new Exception('Nome do avatar muito longo (máximo 100 caracteres)');
        }
        
        $generosValidos = ['masculino', 'feminino', 'neutro', 'outro'];
        if (!empty($dados['genero']) && !in_array($dados['genero'], $generosValidos)) {
            throw new Exception('Gênero inválido');
        }
        
        $idadesValidas = ['crianca', 'adolescente', 'jovem', 'adulto', 'idoso'];
        if (!empty($dados['idade_categoria']) && !in_array($dados['idade_categoria'], $idadesValidas)) {
            throw new Exception('Categoria de idade inválida');
        }
    }
    
    private function verificarPermissaoAvatar($avatarId) {
        if (!$this->usuario_id) {
            return false;
        }
        
        $avatar = $this->supabase->select('avatares', 'criado_por', ['id' => $avatarId]);
        return !empty($avatar) && $avatar[0]['criado_por'] == $this->usuario_id;
    }
    
    private function sanitizar($string) {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
    
    private function processarTags($tags) {
        if (is_string($tags)) {
            $tags = explode(',', $tags);
        }
        
        if (!is_array($tags)) {
            return [];
        }
        
        return array_map('trim', array_filter($tags));
    }
    
    private function adicionarCategoriasAvatar($avatarId, $categorias) {
        foreach ($categorias as $categoriaId) {
            $this->supabase->insert('avatar_categorias', [
                'avatar_id' => $avatarId,
                'categoria_id' => $categoriaId
            ]);
        }
    }
    
    private function adicionarCaracteristicasAvatar($avatarId, $caracteristicas) {
        foreach ($caracteristicas as $caracteristica) {
            $this->supabase->insert('caracteristicas_fisicas', [
                'avatar_id' => $avatarId,
                'tipo' => $caracteristica['tipo'],
                'caracteristica' => $caracteristica['caracteristica'],
                'valor' => $caracteristica['valor'] ?? '',
                'descricao' => $caracteristica['descricao'] ?? '',
                'ordem_exibicao' => $caracteristica['ordem'] ?? 0
            ]);
        }
    }
    
    private function adicionarVestimentasAvatar($avatarId, $vestimentas) {
        foreach ($vestimentas as $vestimenta) {
            $this->supabase->insert('avatar_vestimentas', [
                'avatar_id' => $avatarId,
                'tipo' => $vestimenta['tipo'],
                'item' => $vestimenta['item'],
                'cor' => $vestimenta['cor'] ?? '',
                'material' => $vestimenta['material'] ?? '',
                'estilo' => $vestimenta['estilo'] ?? '',
                'descricao' => $vestimenta['descricao'] ?? '',
                'ordem_exibicao' => $vestimenta['ordem'] ?? 0
            ]);
        }
    }
    
    private function atualizarCategoriasAvatar($avatarId, $categorias) {
        // Remover categorias existentes
        $this->supabase->delete('avatar_categorias', ['avatar_id' => $avatarId]);
        
        // Adicionar novas categorias
        $this->adicionarCategoriasAvatar($avatarId, $categorias);
    }
    
    private function atualizarCaracteristicasAvatar($avatarId, $caracteristicas) {
        // Remover características existentes
        $this->supabase->delete('caracteristicas_fisicas', ['avatar_id' => $avatarId]);
        
        // Adicionar novas características
        $this->adicionarCaracteristicasAvatar($avatarId, $caracteristicas);
    }
    
    private function atualizarVestimentasAvatar($avatarId, $vestimentas) {
        // Remover vestimentas existentes
        $this->supabase->delete('avatar_vestimentas', ['avatar_id' => $avatarId]);
        
        // Adicionar novas vestimentas
        $this->adicionarVestimentasAvatar($avatarId, $vestimentas);
    }
    
    /**
     * Gerar prompt a partir do avatar
     */
    public function gerarPromptAvatar($avatarId) {
        try {
            $avatar = $this->obterAvatar($avatarId);
            
            if (!$avatar) {
                throw new Exception('Avatar não encontrado');
            }
            
            $prompt = [];
            
            // Informações básicas
            if (!empty($avatar['nome'])) {
                $prompt[] = $avatar['nome'];
            }
            
            if (!empty($avatar['genero']) && $avatar['genero'] !== 'neutro') {
                $prompt[] = $avatar['genero'];
            }
            
            if (!empty($avatar['idade_categoria'])) {
                $prompt[] = $avatar['idade_categoria'];
            }
            
            if (!empty($avatar['etnia'])) {
                $prompt[] = $avatar['etnia'];
            }
            
            // Características físicas
            if (!empty($avatar['tipo_fisico'])) {
                $prompt[] = $avatar['tipo_fisico'];
            }
            
            if (!empty($avatar['altura'])) {
                $prompt[] = 'altura ' . $avatar['altura'];
            }
            
            if (!empty($avatar['cor_cabelo'])) {
                $prompt[] = 'cabelo ' . $avatar['cor_cabelo'];
            }
            
            if (!empty($avatar['cor_olhos'])) {
                $prompt[] = 'olhos ' . $avatar['cor_olhos'];
            }
            
            if (!empty($avatar['cor_pele'])) {
                $prompt[] = 'pele ' . $avatar['cor_pele'];
            }
            
            // Vestuário e acessórios
            if (!empty($avatar['vestuario'])) {
                $prompt[] = $avatar['vestuario'];
            }
            
            if (!empty($avatar['acessorios'])) {
                $prompt[] = $avatar['acessorios'];
            }
            
            // Profissão e contexto
            if (!empty($avatar['profissao'])) {
                $prompt[] = $avatar['profissao'];
            }
            
            if (!empty($avatar['postura'])) {
                $prompt[] = 'postura ' . $avatar['postura'];
            }
            
            if (!empty($avatar['expressao_facial'])) {
                $prompt[] = 'expressão ' . $avatar['expressao_facial'];
            }
            
            // Descrição personalizada
            if (!empty($avatar['descricao'])) {
                $prompt[] = $avatar['descricao'];
            }
            
            // Características detalhadas
            if (!empty($avatar['caracteristicas'])) {
                foreach ($avatar['caracteristicas'] as $caracteristica) {
                    if (!empty($caracteristica['valor'])) {
                        $prompt[] = $caracteristica['caracteristica'] . ' ' . $caracteristica['valor'];
                    }
                }
            }
            
            // Vestimentas detalhadas
            if (!empty($avatar['vestimentas'])) {
                foreach ($avatar['vestimentas'] as $vestimenta) {
                    $itemPrompt = $vestimenta['item'];
                    if (!empty($vestimenta['cor'])) {
                        $itemPrompt .= ' ' . $vestimenta['cor'];
                    }
                    if (!empty($vestimenta['material'])) {
                        $itemPrompt .= ' de ' . $vestimenta['material'];
                    }
                    $prompt[] = $itemPrompt;
                }
            }
            
            $promptFinal = implode(', ', array_filter($prompt));
            
            // Registrar uso
            $this->registrarUsoAvatar($avatarId, $avatar, $promptFinal);
            
            return $promptFinal;
            
        } catch (Exception $e) {
            error_log("Erro ao gerar prompt do avatar: " . $e->getMessage());
            return '';
        }
    }
}
?>