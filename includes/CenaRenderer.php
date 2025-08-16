<?php
/**
 * Renderizador de Cenas - Geração Dinâmica de HTML
 * 
 * Esta classe gera o HTML dos cards e blocos baseado nos dados do banco
 */

require_once 'CenaManager.php';

class CenaRenderer {
    private $cenaManager;
    
    public function __construct() {
        $this->cenaManager = new CenaManager();
    }
    
    /**
     * Renderiza uma aba completa com todos os blocos e cenas
     * 
     * @param string $tipoAba Tipo da aba (ambiente, iluminacao, etc.)
     * @param string $tabId ID da aba HTML
     * @param string $titulo Título da aba
     * @param string $subtitulo Subtítulo da aba
     * @param string $icone Ícone da aba
     * @return string HTML da aba completa
     */
    public function renderAbaCompleta($tipoAba, $tabId, $titulo, $subtitulo, $icone) {
        $dados = $this->cenaManager->getDadosCompletos($tipoAba);
        
        $html = '<div class="tab-content" id="' . htmlspecialchars($tabId) . '">';
        $html .= '<div class="tab-header">';
        $html .= '<h2><i class="material-icons">' . htmlspecialchars($icone) . '</i> ' . htmlspecialchars($titulo) . '</h2>';
        $html .= '<p>' . htmlspecialchars($subtitulo) . '</p>';
        $html .= '</div>';
        
        $html .= '<div class="categories-grid">';
        
        foreach ($dados as $bloco) {
            $html .= $this->renderBloco($bloco, $tipoAba);
        }
        
        $html .= '</div>';
        
        // Adicionar controles na base
        $html .= $this->renderControlesBase($tipoAba);
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Renderiza um bloco individual com suas cenas
     * 
     * @param array $bloco Dados do bloco
     * @param string $tipoAba Tipo da aba
     * @return string HTML do bloco
     */
    public function renderBloco($bloco, $tipoAba) {
        $html = '<div class="category-section">';
        
        // Header do bloco
        $html .= '<div class="category-header">';
        $html .= '<div class="category-icon">';
        $html .= '<i class="material-icons">' . htmlspecialchars($bloco['icone']) . '</i>';
        $html .= '</div>';
        $html .= '<h3 class="category-title">' . htmlspecialchars($bloco['titulo']) . '</h3>';
        $html .= '</div>';
        
        // Grid de cenas
        $html .= '<div class="subcategories-grid">';
        
        foreach ($bloco['cenas'] as $cena) {
            $html .= $this->renderCena($cena, $tipoAba);
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Renderiza uma cena individual (card)
     * 
     * @param array $cena Dados da cena
     * @param string $tipoAba Tipo da aba
     * @return string HTML da cena
     */
    public function renderCena($cena, $tipoAba) {
        $dataType = $this->getDataType($tipoAba);
        $valorSelecao = htmlspecialchars($cena['valor_selecao']);
        $titulo = htmlspecialchars($cena['titulo']);
        $subtitulo = htmlspecialchars($cena['subtitulo'] ?? '');
        
        $html = '<div class="subcategory-card" ';
        $html .= 'data-type="' . htmlspecialchars($dataType) . '" ';
        $html .= 'data-value="' . $valorSelecao . '" ';
        $html .= 'title="' . htmlspecialchars($cena['texto_prompt']) . '">';
        
        // Verificar se tem subtítulo para escolher o formato
        if (!empty($subtitulo)) {
            // Formato com título e subtítulo
            $html .= '<div class="subcategory-title">' . $titulo . '</div>';
            $html .= '<div class="subcategory-desc">' . $subtitulo . '</div>';
        } else {
            // Formato simples com ícone (quando apropriado) e título
            $icone = $this->getIconePorTipo($tipoAba, $cena['valor_selecao']);
            if ($icone) {
                $html .= '<i class="material-icons">' . htmlspecialchars($icone) . '</i>';
            }
            $html .= '<span>' . $titulo . '</span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Renderiza os controles na base da aba
     * 
     * @param string $tipoAba Tipo da aba
     * @return string HTML dos controles
     */
    private function renderControlesBase($tipoAba) {
        $customFieldName = "custom_" . $tipoAba;
        $placeholderText = $this->getPlaceholderText($tipoAba);
        
        $html = '<div class="bottom-controls-container">';
        
        // Campo de descrição personalizada
        $html .= '<div class="custom-description">';
        $html .= '<label for="' . htmlspecialchars($customFieldName) . '">';
        $html .= '<i class="material-icons">edit</i>';
        $html .= htmlspecialchars(ucfirst($tipoAba)) . ' Personalizado';
        $html .= '</label>';
        $html .= '<textarea ';
        $html .= 'id="' . htmlspecialchars($customFieldName) . '" ';
        $html .= 'name="' . htmlspecialchars($customFieldName) . '" ';
        $html .= 'placeholder="' . htmlspecialchars($placeholderText) . '" ';
        $html .= 'rows="3"></textarea>';
        $html .= '</div>';
        
        // Controles de navegação
        $html .= '<div class="tab-navigation">';
        $html .= '<button type="button" class="btn btn-secondary" onclick="promptGenerator.previousTab()">';
        $html .= '<i class="material-icons">arrow_back</i>';
        $html .= '</button>';
        $html .= '<button type="button" class="btn btn-primary" onclick="promptGenerator.nextTab()">';
        $html .= '<i class="material-icons">arrow_forward</i>';
        $html .= '</button>';
        $html .= '</div>';
        
        // Área de propaganda
        $html .= '<div class="advertisement-container">';
        $html .= '<div class="advertisement-content">';
        $html .= '<div class="advertisement-placeholder">';
        $html .= 'Espaço para propaganda';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Obtém o data-type baseado no tipo da aba
     */
    private function getDataType($tipoAba) {
        $mapping = [
            'ambiente' => 'environment',
            'iluminacao' => 'lighting',
            'avatar' => 'character',
            'camera' => 'camera',
            'voz' => 'voice',
            'acao' => 'acao'
        ];
        
        return $mapping[$tipoAba] ?? $tipoAba;
    }
    
    /**
     * Obtém texto de placeholder baseado no tipo da aba
     */
    private function getPlaceholderText($tipoAba) {
        $placeholders = [
            'ambiente' => 'Descreva um ambiente específico que não está nas opções abaixo...',
            'iluminacao' => 'Descreva um tipo de iluminação específico que não está nas opções abaixo...',
            'avatar' => 'Descreva um personagem específico que não está nas opções abaixo...',
            'camera' => 'Descreva um ângulo ou movimento de câmera específico que não está nas opções abaixo...',
            'voz' => 'Descreva um tipo de voz ou narração específica que não está nas opções abaixo...',
            'acao' => 'Descreva uma ação ou movimento específico que não está nas opções abaixo...'
        ];
        
        return $placeholders[$tipoAba] ?? "Descreva algo específico...";
    }
    
    /**
     * Obtém ícone baseado no tipo e valor (para casos simples)
     */
    private function getIconePorTipo($tipoAba, $valor) {
        // Mapeamento básico para ações que usam ícones
        $icones = [
            'acao' => [
                'correndo' => 'directions_run',
                'caminhando' => 'directions_walk',
                'saltando' => 'fitness_center',
                'dancando' => 'music_note',
                'sentado' => 'chair',
                'deitado' => 'bed',
                'sorrindo' => 'sentiment_very_satisfied',
                'pensativo' => 'psychology',
                'surpreso' => 'sentiment_neutral',
                'concentrado' => 'visibility',
                'conversando' => 'chat',
                'gritando' => 'volume_up',
                'apontando' => 'touch_app',
                'acenando' => 'waving_hand',
                'aplaudindo' => 'celebration',
                'segurando' => 'pan_tool',
                'escrevendo' => 'edit',
                'digitando' => 'keyboard',
                'cumprimentando' => 'handshake',
                'abracando' => 'favorite',
                'ensinando' => 'school',
                'apresentando' => 'present_to_all',
                'ajudando' => 'help',
                'observando' => 'visibility',
                'voando' => 'flight',
                'escalando' => 'terrain',
                'nadando' => 'pool',
                'pedalando' => 'pedal_bike',
                'dirigindo' => 'drive_eta',
                'flutuando' => 'air'
            ]
        ];
        
        return $icones[$tipoAba][$valor] ?? null;
    }
    
    /**
     * Método estático para uso rápido
     */
    public static function gerarAba($tipoAba, $tabId, $titulo, $subtitulo, $icone) {
        $renderer = new self();
        return $renderer->renderAbaCompleta($tipoAba, $tabId, $titulo, $subtitulo, $icone);
    }
}
?>