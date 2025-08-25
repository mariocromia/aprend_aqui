<?php
/**
 * CenaRenderer para Gerador de Prompt
 * Renderiza dinamicamente as cenas do banco de dados no formato do gerador de prompt
 */

class CenaRendererPrompt {
    private $cenaManager;
    
    public function __construct($cenaManager = null) {
        $this->cenaManager = $cenaManager ?: new CenaManager();
    }
    
    /**
     * Renderiza a aba ambiente completa com dados do banco
     */
    public function renderizarAbaAmbiente() {
        try {
            // Buscar todos os blocos ativos do tipo 'ambiente'
            $blocos = $this->cenaManager->getBlocosPorTipo('ambiente');
            
            if (empty($blocos)) {
                return $this->renderizarEstadoVazio('ambiente');
            }
            
            $html = '<div class="categories-grid">';
            
            foreach ($blocos as $bloco) {
                $cenas = $this->cenaManager->getCenasPorBloco($bloco['id']);
                $cenasAtivas = $cenas; // CenaManager já filtra apenas cenas ativas
                
                if (!empty($cenasAtivas)) {
                    $html .= $this->renderizarBlocoAmbiente($bloco, $cenasAtivas);
                }
            }
            
            $html .= '</div>';
            
            return $html;
            
        } catch (Exception $e) {
            error_log("Erro ao renderizar aba ambiente: " . $e->getMessage());
            return $this->renderizarErro('ambiente');
        }
    }

    /**
     * Renderiza a aba iluminação completa com dados do banco
     */
    public function renderizarAbaIluminacao() {
        try {
            // Buscar todos os blocos ativos do tipo 'iluminacao'
            $blocos = $this->cenaManager->getBlocosPorTipo('iluminacao');
            
            if (empty($blocos)) {
                return $this->renderizarEstadoVazio('iluminacao');
            }
            
            $html = '<div class="categories-grid">';
            
            foreach ($blocos as $bloco) {
                $cenas = $this->cenaManager->getCenasPorBloco($bloco['id']);
                $cenasAtivas = $cenas; // CenaManager já filtra apenas cenas ativas
                
                if (!empty($cenasAtivas)) {
                    $html .= $this->renderizarBlocoIluminacao($bloco, $cenasAtivas);
                }
            }
            
            $html .= '</div>';
            
            return $html;
            
        } catch (Exception $e) {
            error_log("Erro ao renderizar aba iluminacao: " . $e->getMessage());
            return $this->renderizarErro('iluminacao');
        }
    }

    /**
     * Renderiza a aba estilo visual completa com dados do banco
     */
    public function renderizarAbaEstiloVisual() {
        try {
            // Buscar todos os blocos ativos do tipo 'estilo_visual'
            $blocos = $this->cenaManager->getBlocosPorTipo('estilo_visual');
            
            if (empty($blocos)) {
                return $this->renderizarEstadoVazio('estilo_visual');
            }
            
            $html = '<div class="categories-grid">';
            
            foreach ($blocos as $bloco) {
                $cenas = $this->cenaManager->getCenasPorBloco($bloco['id']);
                $cenasAtivas = $cenas; // CenaManager já filtra apenas cenas ativas
                
                if (!empty($cenasAtivas)) {
                    $html .= $this->renderizarBlocoEstiloVisual($bloco, $cenasAtivas);
                }
            }
            
            $html .= '</div>';
            
            return $html;
            
        } catch (Exception $e) {
            error_log("Erro ao renderizar aba estilo_visual: " . $e->getMessage());
            return $this->renderizarErro('estilo_visual');
        }
    }

    /**
     * Renderiza a aba técnica completa com dados do banco
     */
    public function renderizarAbaTecnica() {
        try {
            // Buscar todos os blocos ativos do tipo 'tecnica'
            $blocos = $this->cenaManager->getBlocosPorTipo('tecnica');
            
            if (empty($blocos)) {
                return $this->renderizarEstadoVazio('tecnica');
            }
            
            $html = '<div class="categories-grid">';
            
            foreach ($blocos as $bloco) {
                $cenas = $this->cenaManager->getCenasPorBloco($bloco['id']);
                $cenasAtivas = $cenas; // CenaManager já filtra apenas cenas ativas
                
                if (!empty($cenasAtivas)) {
                    $html .= $this->renderizarBlocoTecnica($bloco, $cenasAtivas);
                }
            }
            
            $html .= '</div>';
            
            return $html;
            
        } catch (Exception $e) {
            error_log("Erro ao renderizar aba tecnica: " . $e->getMessage());
            return $this->renderizarErro('tecnica');
        }
    }

    /**
     * Renderiza a aba elementos especiais completa com dados do banco
     */
    public function renderizarAbaElementosEspeciais() {
        try {
            // Buscar todos os blocos ativos do tipo 'elementos_especiais'
            $blocos = $this->cenaManager->getBlocosPorTipo('elementos_especiais');
            
            if (empty($blocos)) {
                return $this->renderizarEstadoVazio('elementos_especiais');
            }
            
            $html = '<div class="categories-grid">';
            
            foreach ($blocos as $bloco) {
                $cenas = $this->cenaManager->getCenasPorBloco($bloco['id']);
                $cenasAtivas = $cenas; // CenaManager já filtra apenas cenas ativas
                
                if (!empty($cenasAtivas)) {
                    $html .= $this->renderizarBlocoElementosEspeciais($bloco, $cenasAtivas);
                }
            }
            
            $html .= '</div>';
            
            return $html;
            
        } catch (Exception $e) {
            error_log("Erro ao renderizar aba elementos_especiais: " . $e->getMessage());
            return $this->renderizarErro('elementos_especiais');
        }
    }

    /**
     * Renderiza a aba qualidade completa com dados do banco
     */
    public function renderizarAbaQualidade() {
        try {
            // Buscar todos os blocos ativos do tipo 'qualidade'
            $blocos = $this->cenaManager->getBlocosPorTipo('qualidade');
            
            if (empty($blocos)) {
                return $this->renderizarEstadoVazio('qualidade');
            }
            
            $html = '<div class="categories-grid">';
            
            foreach ($blocos as $bloco) {
                $cenas = $this->cenaManager->getCenasPorBloco($bloco['id']);
                $cenasAtivas = $cenas; // CenaManager já filtra apenas cenas ativas
                
                if (!empty($cenasAtivas)) {
                    $html .= $this->renderizarBlocoQualidade($bloco, $cenasAtivas);
                }
            }
            
            $html .= '</div>';
            
            return $html;
            
        } catch (Exception $e) {
            error_log("Erro ao renderizar aba qualidade: " . $e->getMessage());
            return $this->renderizarErro('qualidade');
        }
    }
    
    /**
     * Renderiza um bloco específico de ambiente
     */
    private function renderizarBlocoAmbiente($bloco, $cenas) {
        // Ordenar cenas por ordem de exibição
        usort($cenas, function($a, $b) {
            return ($a['ordem_exibicao'] ?? 0) - ($b['ordem_exibicao'] ?? 0);
        });
        
        $html = '
            <!-- ' . strtoupper($this->sanitizar($bloco['titulo'])) . ' -->
            <div class="category-section" data-bloco-id="' . $bloco['id'] . '">
                <div class="category-header">
                    <div class="category-icon">
                        <i class="material-icons">' . $this->sanitizar($bloco['icone']) . '</i>
                    </div>
                    <h3 class="category-title">' . $this->sanitizar($bloco['titulo']) . '</h3>
                </div>
                <div class="subcategories-grid">';
        
        foreach ($cenas as $cena) {
            $html .= $this->renderizarCenaAmbiente($cena);
        }
        
        $html .= '
                </div>
            </div>';
        
        return $html;
    }

    /**
     * Renderiza um bloco específico de iluminação
     */
    private function renderizarBlocoIluminacao($bloco, $cenas) {
        // Ordenar cenas por ordem de exibição
        usort($cenas, function($a, $b) {
            return ($a['ordem_exibicao'] ?? 0) - ($b['ordem_exibicao'] ?? 0);
        });
        
        $html = '
            <!-- ' . strtoupper($this->sanitizar($bloco['titulo'])) . ' -->
            <div class="category-section" data-bloco-id="' . $bloco['id'] . '">
                <div class="category-header">
                    <div class="category-icon">
                        <i class="material-icons">' . $this->sanitizar($bloco['icone']) . '</i>
                    </div>
                    <h3 class="category-title">' . $this->sanitizar($bloco['titulo']) . '</h3>
                </div>
                <div class="subcategories-grid">';
        
        foreach ($cenas as $cena) {
            $html .= $this->renderizarCenaIluminacao($cena);
        }
        
        $html .= '
                </div>
            </div>';
        
        return $html;
    }

    /**
     * Renderiza um bloco específico de estilo visual
     */
    private function renderizarBlocoEstiloVisual($bloco, $cenas) {
        // Ordenar cenas por ordem de exibição
        usort($cenas, function($a, $b) {
            return ($a['ordem_exibicao'] ?? 0) - ($b['ordem_exibicao'] ?? 0);
        });
        
        $html = '
            <!-- ' . strtoupper($this->sanitizar($bloco['titulo'])) . ' -->
            <div class="category-section" data-bloco-id="' . $bloco['id'] . '">
                <div class="category-header">
                    <div class="category-icon">
                        <i class="material-icons">' . $this->sanitizar($bloco['icone']) . '</i>
                    </div>
                    <h3 class="category-title">' . $this->sanitizar($bloco['titulo']) . '</h3>
                </div>
                <div class="subcategories-grid">';
        
        foreach ($cenas as $cena) {
            $html .= $this->renderizarCenaEstiloVisual($cena);
        }
        
        $html .= '
                </div>
            </div>';
        
        return $html;
    }

    /**
     * Renderiza um bloco específico de técnica
     */
    private function renderizarBlocoTecnica($bloco, $cenas) {
        // Ordenar cenas por ordem de exibição
        usort($cenas, function($a, $b) {
            return ($a['ordem_exibicao'] ?? 0) - ($b['ordem_exibicao'] ?? 0);
        });
        
        $html = '
            <!-- ' . strtoupper($this->sanitizar($bloco['titulo'])) . ' -->
            <div class="category-section" data-bloco-id="' . $bloco['id'] . '">
                <div class="category-header">
                    <div class="category-icon">
                        <i class="material-icons">' . $this->sanitizar($bloco['icone']) . '</i>
                    </div>
                    <h3 class="category-title">' . $this->sanitizar($bloco['titulo']) . '</h3>
                </div>
                <div class="subcategories-grid">';
        
        foreach ($cenas as $cena) {
            $html .= $this->renderizarCenaTecnica($cena);
        }
        
        $html .= '
                </div>
            </div>';
        
        return $html;
    }

    /**
     * Renderiza um bloco específico de elementos especiais
     */
    private function renderizarBlocoElementosEspeciais($bloco, $cenas) {
        // Ordenar cenas por ordem de exibição
        usort($cenas, function($a, $b) {
            return ($a['ordem_exibicao'] ?? 0) - ($b['ordem_exibicao'] ?? 0);
        });
        
        $html = '
            <!-- ' . strtoupper($this->sanitizar($bloco['titulo'])) . ' -->
            <div class="category-section" data-bloco-id="' . $bloco['id'] . '">
                <div class="category-header">
                    <div class="category-icon">
                        <i class="material-icons">' . $this->sanitizar($bloco['icone']) . '</i>
                    </div>
                    <h3 class="category-title">' . $this->sanitizar($bloco['titulo']) . '</h3>
                </div>
                <div class="subcategories-grid">';
        
        foreach ($cenas as $cena) {
            $html .= $this->renderizarCenaElementosEspeciais($cena);
        }
        
        $html .= '
                </div>
            </div>';
        
        return $html;
    }

    /**
     * Renderiza um bloco específico de qualidade
     */
    private function renderizarBlocoQualidade($bloco, $cenas) {
        // Ordenar cenas por ordem de exibição
        usort($cenas, function($a, $b) {
            return ($a['ordem_exibicao'] ?? 0) - ($b['ordem_exibicao'] ?? 0);
        });
        
        $html = '
            <!-- ' . strtoupper($this->sanitizar($bloco['titulo'])) . ' -->
            <div class="category-section" data-bloco-id="' . $bloco['id'] . '">
                <div class="category-header">
                    <div class="category-icon">
                        <i class="material-icons">' . $this->sanitizar($bloco['icone']) . '</i>
                    </div>
                    <h3 class="category-title">' . $this->sanitizar($bloco['titulo']) . '</h3>
                </div>
                <div class="subcategories-grid">';
        
        foreach ($cenas as $cena) {
            $html .= $this->renderizarCenaQualidade($cena);
        }
        
        $html .= '
                </div>
            </div>';
        
        return $html;
    }
    
    /**
     * Renderiza uma cena individual como card de ambiente
     */
    private function renderizarCenaAmbiente($cena) {
        $titulo = $this->sanitizar($cena['titulo']);
        $subtitulo = !empty($cena['subtitulo']) ? $this->sanitizar($cena['subtitulo']) : $this->truncarTexto($cena['texto_prompt'], 30);
        $valorSelecao = $this->sanitizar($cena['valor_selecao']);
        
        return '
                    <div class="subcategory-card" data-type="environment" data-value="' . $valorSelecao . '" data-cena-id="' . $cena['id'] . '">
                        <div class="subcategory-title">' . $titulo . '</div>
                        <div class="subcategory-desc">' . $subtitulo . '</div>
                    </div>';
    }

    /**
     * Renderiza uma cena individual como card de iluminação
     */
    private function renderizarCenaIluminacao($cena) {
        $titulo = $this->sanitizar($cena['titulo']);
        $subtitulo = !empty($cena['subtitulo']) ? $this->sanitizar($cena['subtitulo']) : $this->truncarTexto($cena['texto_prompt'], 30);
        $valorSelecao = $this->sanitizar($cena['valor_selecao']);
        
        return '
                    <div class="subcategory-card" data-type="lighting" data-value="' . $valorSelecao . '" data-cena-id="' . $cena['id'] . '">
                        <div class="subcategory-title">' . $titulo . '</div>
                        <div class="subcategory-desc">' . $subtitulo . '</div>
                    </div>';
    }

    /**
     * Renderiza uma cena individual como card de estilo visual
     */
    private function renderizarCenaEstiloVisual($cena) {
        $titulo = $this->sanitizar($cena['titulo']);
        $subtitulo = !empty($cena['subtitulo']) ? $this->sanitizar($cena['subtitulo']) : $this->truncarTexto($cena['texto_prompt'], 30);
        $valorSelecao = $this->sanitizar($cena['valor_selecao']);
        
        return '
                    <div class="subcategory-card" data-type="visual_style" data-value="' . $valorSelecao . '" data-cena-id="' . $cena['id'] . '">
                        <div class="subcategory-title">' . $titulo . '</div>
                        <div class="subcategory-desc">' . $subtitulo . '</div>
                    </div>';
    }

    /**
     * Renderiza uma cena individual como card de técnica
     */
    private function renderizarCenaTecnica($cena) {
        $titulo = $this->sanitizar($cena['titulo']);
        $subtitulo = !empty($cena['subtitulo']) ? $this->sanitizar($cena['subtitulo']) : $this->truncarTexto($cena['texto_prompt'], 30);
        $valorSelecao = $this->sanitizar($cena['valor_selecao']);
        
        return '
                    <div class="subcategory-card" data-type="technique" data-value="' . $valorSelecao . '" data-cena-id="' . $cena['id'] . '">
                        <div class="subcategory-title">' . $titulo . '</div>
                        <div class="subcategory-desc">' . $subtitulo . '</div>
                    </div>';
    }

    /**
     * Renderiza uma cena individual como card de elementos especiais
     */
    private function renderizarCenaElementosEspeciais($cena) {
        $titulo = $this->sanitizar($cena['titulo']);
        $subtitulo = !empty($cena['subtitulo']) ? $this->sanitizar($cena['subtitulo']) : $this->truncarTexto($cena['texto_prompt'], 30);
        $valorSelecao = $this->sanitizar($cena['valor_selecao']);
        
        return '
                    <div class="subcategory-card" data-type="special_elements" data-value="' . $valorSelecao . '" data-cena-id="' . $cena['id'] . '">
                        <div class="subcategory-title">' . $titulo . '</div>
                        <div class="subcategory-desc">' . $subtitulo . '</div>
                    </div>';
    }

    /**
     * Renderiza uma cena individual como card de qualidade
     */
    private function renderizarCenaQualidade($cena) {
        $titulo = $this->sanitizar($cena['titulo']);
        $subtitulo = !empty($cena['subtitulo']) ? $this->sanitizar($cena['subtitulo']) : $this->truncarTexto($cena['texto_prompt'], 30);
        $valorSelecao = $this->sanitizar($cena['valor_selecao']);
        
        return '
                    <div class="subcategory-card" data-type="quality" data-value="' . $valorSelecao . '" data-cena-id="' . $cena['id'] . '">
                        <div class="subcategory-title">' . $titulo . '</div>
                        <div class="subcategory-desc">' . $subtitulo . '</div>
                    </div>';
    }
    
    /**
     * Renderiza estado vazio quando não há blocos/cenas
     */
    private function renderizarEstadoVazio($tipo = 'ambiente') {
        $configs = [
            'ambiente' => [
                'icone' => 'landscape',
                'titulo' => 'Nenhum ambiente encontrado',
                'descricao' => 'Configure ambientes no painel administrativo para exibi-los aqui.'
            ],
            'iluminacao' => [
                'icone' => 'wb_sunny',
                'titulo' => 'Nenhuma opção de iluminação encontrada',
                'descricao' => 'Configure opções de iluminação no painel administrativo para exibi-las aqui.'
            ],
            'estilo_visual' => [
                'icone' => 'palette',
                'titulo' => 'Nenhum estilo visual encontrado',
                'descricao' => 'Configure estilos visuais no painel administrativo para exibi-los aqui.'
            ],
            'tecnica' => [
                'icone' => 'settings',
                'titulo' => 'Nenhuma opção técnica encontrada',
                'descricao' => 'Configure opções técnicas no painel administrativo para exibi-las aqui.'
            ],
            'elementos_especiais' => [
                'icone' => 'auto_awesome',
                'titulo' => 'Nenhum elemento especial encontrado',
                'descricao' => 'Configure elementos especiais no painel administrativo para exibi-los aqui.'
            ]
        ];
        
        $config = $configs[$tipo] ?? $configs['ambiente'];
        
        return '
        <div class="categories-grid">
            <div class="category-section">
                <div class="empty-state-' . $tipo . '">
                    <i class="material-icons" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem;">' . $config['icone'] . '</i>
                    <h3 style="color: #64748b; margin-bottom: 0.5rem;">' . $config['titulo'] . '</h3>
                    <p style="color: #94a3b8;">' . $config['descricao'] . '</p>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Renderiza estado de erro
     */
    private function renderizarErro($tipo = 'ambiente') {
        $titulos = [
            'ambiente' => 'Erro ao carregar ambientes',
            'iluminacao' => 'Erro ao carregar opções de iluminação',
            'estilo_visual' => 'Erro ao carregar estilos visuais'
        ];
        
        $titulo = $titulos[$tipo] ?? $titulos['ambiente'];
        
        return '
        <div class="categories-grid">
            <div class="category-section">
                <div class="error-state-' . $tipo . '">
                    <i class="material-icons" style="font-size: 4rem; color: #ef4444; margin-bottom: 1rem;">error</i>
                    <h3 style="color: #ef4444; margin-bottom: 0.5rem;">' . $titulo . '</h3>
                    <p style="color: #64748b;">Tente recarregar a página ou contate o administrador.</p>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Renderiza data attributes para JavaScript do gerador de prompt
     */
    public function renderizarDataAttributes($tipo = 'ambiente') {
        try {
            $blocos = $this->cenaManager->getBlocosPorTipo($tipo);
            $dataAttributes = [];
            
            foreach ($blocos as $bloco) {
                $cenas = $this->cenaManager->getCenasPorBloco($bloco['id']);
                foreach ($cenas as $cena) {
                    $dataAttributes[$cena['valor_selecao']] = [
                        'titulo' => $cena['titulo'],
                        'prompt' => $cena['texto_prompt'],
                        'bloco' => $bloco['titulo']
                    ];
                }
            }
            
            return $dataAttributes;
            
        } catch (Exception $e) {
            error_log("Erro ao gerar data attributes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Gera JavaScript para integração com sistema de seleção
     */
    public function gerarJavaScriptIntegracao($tipos = ['ambiente']) {
        $js = '<script>';
        
        // Gerar dados para cada tipo especificado
        foreach ($tipos as $tipo) {
            $dataAttributes = $this->renderizarDataAttributes($tipo);
            $varName = 'cenas' . ucfirst($tipo) . 'Data';
            
            $js .= '
        // Dados dinâmicos das cenas de ' . $tipo . '
        window.' . $varName . ' = ' . json_encode($dataAttributes, JSON_UNESCAPED_UNICODE) . ';';
        }
        
        $js .= '
        
        // Integração com sistema de seleção existente
        document.addEventListener("DOMContentLoaded", function() {';
        
        // Gerar verificações e integrações para cada tipo
        foreach ($tipos as $tipo) {
            $varName = 'cenas' . ucfirst($tipo) . 'Data';
            $functionName = 'atualizarSelecao' . ucfirst($tipo);
            
            $js .= '
            // Verificar se dados de ' . $tipo . ' foram carregados
            if (window.' . $varName . ' && Object.keys(window.' . $varName . ').length > 0) {
                console.log("Dados de ' . $tipo . ' carregados:", Object.keys(window.' . $varName . ').length, "cenas");
            } else {
                console.warn("Nenhum dado de ' . $tipo . ' carregado do banco de dados");
            }';
        }
        
        $js .= '
            
            // Ajustar alinhamento dos blocos após carregar conteúdo dinâmico
            if (typeof window.adjustCategoriesAlignment === "function") {
                window.adjustCategoriesAlignment();
            }
            
            // Adicionar event listeners para cards dinâmicos de todos os tipos
            document.querySelectorAll(".subcategory-card[data-cena-id]").forEach(function(card) {
                card.addEventListener("click", function() {
                    const valor = this.dataset.value;
                    const tipo = this.dataset.type;
                    let cenaData = null;
                    
                    // Buscar dados na variável correspondente ao tipo
                    if (tipo === "environment" && window.cenasAmbienteData) {
                        cenaData = window.cenasAmbienteData[valor];
                    } else if (tipo === "visual_style" && window.cenasEstilo_visualData) {
                        cenaData = window.cenasEstilo_visualData[valor];
                    } else if (tipo === "lighting" && window.cenasIluminacaoData) {
                        cenaData = window.cenasIluminacaoData[valor];
                    }
                    
                    if (cenaData) {
                        console.log("Cena selecionada:", cenaData.titulo, "- Prompt:", cenaData.prompt);
                        
                        // Integrar com sistema existente de seleção
                        if (tipo === "environment" && typeof window.atualizarSelecaoAmbiente === "function") {
                            window.atualizarSelecaoAmbiente(valor, cenaData);
                        } else if (tipo === "visual_style" && typeof window.atualizarSelecaoEstilo_visual === "function") {
                            window.atualizarSelecaoEstilo_visual(valor, cenaData);
                        } else if (tipo === "lighting" && typeof window.atualizarSelecaoIluminacao === "function") {
                            window.atualizarSelecaoIluminacao(valor, cenaData);
                        }
                    }
                });
            });
        });
        </script>';
        
        return $js;
    }
    
    /**
     * Utilitários
     */
    private function sanitizar($string) {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
    
    private function truncarTexto($texto, $limite) {
        if (!$texto) return '';
        return strlen($texto) > $limite ? substr($texto, 0, $limite) . '...' : $texto;
    }
    
    /**
     * Método para debug - lista todas as cenas de ambiente
     */
    public function debug() {
        try {
            $blocos = $this->cenaManager->getBlocosPorTipo('ambiente');
            $debug = [
                'total_blocos' => count($blocos),
                'blocos' => []
            ];
            
            foreach ($blocos as $bloco) {
                $cenas = $this->cenaManager->getCenasPorBloco($bloco['id']);
                $debug['blocos'][] = [
                    'id' => $bloco['id'],
                    'titulo' => $bloco['titulo'],
                    'ativo' => true, // CenaManager já filtra apenas ativos
                    'total_cenas' => count($cenas),
                    'cenas_ativas' => count($cenas) // Todas já são ativas
                ];
            }
            
            return $debug;
            
        } catch (Exception $e) {
            return ['erro' => $e->getMessage()];
        }
    }
}
?>