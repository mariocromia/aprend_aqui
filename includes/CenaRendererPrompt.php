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
                return $this->renderizarEstadoVazio();
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
            return $this->renderizarErro();
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
     * Renderiza estado vazio quando não há blocos/cenas
     */
    private function renderizarEstadoVazio() {
        return '
        <div class="categories-grid">
            <div class="category-section">
                <div class="empty-state-ambiente">
                    <i class="material-icons" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem;">landscape</i>
                    <h3 style="color: #64748b; margin-bottom: 0.5rem;">Nenhum ambiente encontrado</h3>
                    <p style="color: #94a3b8;">Configure ambientes no painel administrativo para exibi-los aqui.</p>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Renderiza estado de erro
     */
    private function renderizarErro() {
        return '
        <div class="categories-grid">
            <div class="category-section">
                <div class="error-state-ambiente">
                    <i class="material-icons" style="font-size: 4rem; color: #ef4444; margin-bottom: 1rem;">error</i>
                    <h3 style="color: #ef4444; margin-bottom: 0.5rem;">Erro ao carregar ambientes</h3>
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
    public function gerarJavaScriptIntegracao() {
        $dataAttributes = $this->renderizarDataAttributes('ambiente');
        
        $js = '
        <script>
        // Dados dinâmicos das cenas de ambiente
        window.cenasAmbienteData = ' . json_encode($dataAttributes, JSON_UNESCAPED_UNICODE) . ';
        
        // Integração com sistema de seleção existente
        document.addEventListener("DOMContentLoaded", function() {
            // Verificar se dados foram carregados
            if (window.cenasAmbienteData && Object.keys(window.cenasAmbienteData).length > 0) {
                console.log("Dados de ambiente carregados:", Object.keys(window.cenasAmbienteData).length, "cenas");
                
                // Ajustar alinhamento dos blocos após carregar conteúdo dinâmico
                if (typeof window.adjustCategoriesAlignment === "function") {
                    window.adjustCategoriesAlignment();
                }
                
                // Adicionar event listeners para cards dinâmicos
                document.querySelectorAll(".subcategory-card[data-cena-id]").forEach(function(card) {
                    card.addEventListener("click", function() {
                        const valor = this.dataset.value;
                        const cenaData = window.cenasAmbienteData[valor];
                        
                        if (cenaData) {
                            console.log("Cena selecionada:", cenaData.titulo, "- Prompt:", cenaData.prompt);
                            
                            // Integrar com sistema existente de seleção
                            if (typeof window.atualizarSelecaoAmbiente === "function") {
                                window.atualizarSelecaoAmbiente(valor, cenaData);
                            }
                        }
                    });
                });
            } else {
                console.warn("Nenhum dado de ambiente carregado do banco de dados");
            }
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