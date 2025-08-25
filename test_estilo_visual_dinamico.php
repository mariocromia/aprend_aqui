<?php
/**
 * Teste da renderização dinâmica da aba estilo visual
 * Verifica se o sistema está carregando dados do banco corretamente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/CenaManager.php';
require_once 'includes/CenaRendererPrompt.php';

echo "<h1>🎨 Teste da Aba Estilo Visual Dinâmica</h1>";
echo "<p><em>Verificando integração do sistema de cenas dinâmicas para estilos visuais</em></p>";

try {
    // Inicializar sistema
    echo "<h2>📡 Inicializando Sistema</h2>";
    $cenaManager = new CenaManager();
    $cenaRenderer = new CenaRendererPrompt($cenaManager);
    echo "<p>✅ CenaManager e CenaRenderer inicializados</p>";
    
    // Verificar blocos de estilo visual
    echo "<h2>🔍 Verificando Blocos de Estilo Visual</h2>";
    $blocosEstiloVisual = $cenaManager->getBlocosPorTipo('estilo_visual');
    echo "<p><strong>Total de blocos encontrados:</strong> " . count($blocosEstiloVisual) . "</p>";
    
    if (empty($blocosEstiloVisual)) {
        echo "<div style='background: #fef3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #fbbf24;'>";
        echo "<p><strong>⚠️ Aviso:</strong> Nenhum bloco de estilo visual encontrado!</p>";
        echo "<p>Execute o script de inserção de dados primeiro:</p>";
        echo "<ul>";
        echo "<li><a href='inserir_dados_estilo_visual.php' target='_blank'>📝 Script PHP de inserção</a></li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 8px;'>";
        echo "<h3>📋 Blocos de Estilo Visual Encontrados:</h3>";
        echo "<ul>";
        foreach ($blocosEstiloVisual as $bloco) {
            $cenas = $cenaManager->getCenasPorBloco($bloco['id']);
            echo "<li><strong>{$bloco['titulo']}</strong> - {$bloco['icone']} - " . count($cenas) . " estilos</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    // Testar renderização da aba
    echo "<h2>🎨 Teste de Renderização</h2>";
    $htmlRenderizado = $cenaRenderer->renderizarAbaEstiloVisual();
    
    if (!empty($htmlRenderizado)) {
        echo "<p>✅ HTML renderizado com sucesso!</p>";
        echo "<details style='margin: 10px 0;'>";
        echo "<summary style='cursor: pointer; font-weight: bold;'>📄 Ver HTML gerado</summary>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; max-height: 300px; overflow: auto; font-size: 12px;'>";
        echo htmlspecialchars($htmlRenderizado);
        echo "</pre>";
        echo "</details>";
    } else {
        echo "<p>❌ Falha na renderização do HTML</p>";
    }
    
    // Preview visual
    echo "<h2>👀 Preview Visual</h2>";
    echo "<div style='border: 2px solid #e5e7eb; padding: 20px; border-radius: 8px; background: white;'>";
    echo "<style>";
    echo "
    .categories-grid { display: flex; flex-wrap: wrap; gap: 1.5rem; justify-content: flex-start; }
    .category-section { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; min-width: 280px; }
    .category-header { display: flex; align-items: center; margin-bottom: 1rem; }
    .category-icon { margin-right: 0.75rem; }
    .category-icon i { font-size: 1.5rem; color: #8b5cf6; }
    .category-title { margin: 0; font-size: 1.25rem; font-weight: 600; }
    .subcategories-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.75rem; }
    .subcategory-card { background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 8px; padding: 0.75rem; cursor: pointer; transition: all 0.2s; }
    .subcategory-card:hover { background: #f3e8ff; border-color: #8b5cf6; }
    .subcategory-title { font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; }
    .subcategory-desc { font-size: 0.75rem; color: #64748b; }
    .empty-state-estilo_visual, .error-state-estilo_visual { text-align: center; padding: 2rem; }
    .categories-grid.few-blocks { justify-content: flex-start; }
    .categories-grid.many-blocks { justify-content: space-between; }
    ";
    echo "</style>";
    echo $htmlRenderizado;
    echo "</div>";
    
    // Testar JavaScript Data
    echo "<h2>⚙️ Dados JavaScript</h2>";
    $dataAttributes = $cenaRenderer->renderizarDataAttributes('estilo_visual');
    echo "<p><strong>Estilos para JavaScript:</strong> " . count($dataAttributes) . "</p>";
    
    if (!empty($dataAttributes)) {
        echo "<details style='margin: 10px 0;'>";
        echo "<summary style='cursor: pointer; font-weight: bold;'>📊 Ver dados JSON</summary>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; max-height: 200px; overflow: auto; font-size: 12px;'>";
        echo json_encode($dataAttributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "</pre>";
        echo "</details>";
    }
    
    // JavaScript de integração
    echo "<h2>🔗 JavaScript de Integração</h2>";
    $jsIntegracao = $cenaRenderer->gerarJavaScriptIntegracao(['estilo_visual']);
    echo "<p>✅ JavaScript de integração gerado</p>";
    echo "<details style='margin: 10px 0;'>";
    echo "<summary style='cursor: pointer; font-weight: bold;'>📜 Ver código JavaScript</summary>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; max-height: 300px; overflow: auto; font-size: 12px;'>";
    echo htmlspecialchars($jsIntegracao);
    echo "</pre>";
    echo "</details>";
    
    // Comparação com ambiente
    echo "<h2>🔄 Comparação com Sistema de Ambiente</h2>";
    $blocosAmbiente = $cenaManager->getBlocosPorTipo('ambiente');
    echo "<div style='background: #ecfdf5; padding: 15px; border-radius: 8px;'>";
    echo "<p><strong>Blocos de Ambiente:</strong> " . count($blocosAmbiente) . "</p>";
    echo "<p><strong>Blocos de Estilo Visual:</strong> " . count($blocosEstiloVisual) . "</p>";
    echo "<p><strong>Sistema funcionando:</strong> " . (count($blocosEstiloVisual) > 0 ? "✅ Sim" : "❌ Não") . "</p>";
    echo "</div>";
    
    echo "<h2>🎯 Próximos Passos</h2>";
    echo "<div style='background: #ecfdf5; padding: 15px; border-radius: 8px; border-left: 4px solid #10b981;'>";
    echo "<ol>";
    echo "<li>Certifique-se de que os dados de estilo visual foram inseridos no banco</li>";
    echo "<li>Acesse o gerador de prompt: <a href='gerador_prompt_modern.php' target='_blank'>gerador_prompt_modern.php</a></li>";
    echo "<li>Vá para a aba 'Estilo Visual' (entre Cena/Ambiente e Iluminação)</li>";
    echo "<li>Teste a seleção de diferentes estilos visuais</li>";
    echo "<li>Verifique se o alinhamento automático está funcionando</li>";
    echo "<li>Confirme que a aba está integrada com JavaScript</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; padding: 20px; border-radius: 8px; border-left: 4px solid #ef4444;'>";
    echo "<p style='color: #dc2626;'><strong>❌ Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<details style='margin-top: 10px;'>";
    echo "<summary style='cursor: pointer; color: #7c2d12;'>Ver detalhes técnicos</summary>";
    echo "<pre style='background: #fff; padding: 10px; border-radius: 4px; margin-top: 10px;'>" . $e->getTraceAsString() . "</pre>";
    echo "</details>";
    echo "</div>";
}

// Adicionar JavaScript para alinhamento
echo "
<script>
// Aplicar alinhamento automático
document.addEventListener('DOMContentLoaded', function() {
    const grids = document.querySelectorAll('.categories-grid');
    grids.forEach(grid => {
        const blockCount = grid.querySelectorAll('.category-section').length;
        console.log('Blocos encontrados:', blockCount);
        
        if (blockCount <= 3) {
            grid.style.justifyContent = 'flex-start';
            grid.classList.add('few-blocks');
            console.log('Aplicado alinhamento: flex-start (poucos blocos)');
        } else {
            grid.style.justifyContent = 'space-between';
            grid.classList.add('many-blocks');
            console.log('Aplicado alinhamento: space-between (muitos blocos)');
        }
    });
});
</script>
";
?>