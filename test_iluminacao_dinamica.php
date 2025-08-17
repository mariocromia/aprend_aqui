<?php
/**
 * Teste da renderização dinâmica da aba iluminação
 * Verifica se o sistema está carregando dados do banco corretamente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/CenaManager.php';
require_once 'includes/CenaRendererPrompt.php';

echo "<h1>🔆 Teste da Aba Iluminação Dinâmica</h1>";
echo "<p><em>Verificando integração do sistema de cenas dinâmicas</em></p>";

try {
    // Inicializar sistema
    echo "<h2>📡 Inicializando Sistema</h2>";
    $cenaManager = new CenaManager();
    $cenaRenderer = new CenaRendererPrompt($cenaManager);
    echo "<p>✅ CenaManager e CenaRenderer inicializados</p>";
    
    // Verificar blocos de iluminação
    echo "<h2>🔍 Verificando Blocos de Iluminação</h2>";
    $blocosIluminacao = $cenaManager->getBlocosPorTipo('iluminacao');
    echo "<p><strong>Total de blocos encontrados:</strong> " . count($blocosIluminacao) . "</p>";
    
    if (empty($blocosIluminacao)) {
        echo "<div style='background: #fef3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #fbbf24;'>";
        echo "<p><strong>⚠️ Aviso:</strong> Nenhum bloco de iluminação encontrado!</p>";
        echo "<p>Execute o script de inserção de dados primeiro:</p>";
        echo "<ul>";
        echo "<li><a href='inserir_dados_iluminacao_completo.php' target='_blank'>📝 Script PHP de inserção</a></li>";
        echo "<li>Ou execute o arquivo SQL: <code>docs/cenas_iluminacao_postgresql.sql</code></li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 8px;'>";
        echo "<h3>📋 Blocos de Iluminação Encontrados:</h3>";
        echo "<ul>";
        foreach ($blocosIluminacao as $bloco) {
            $cenas = $cenaManager->getCenasPorBloco($bloco['id']);
            echo "<li><strong>{$bloco['titulo']}</strong> - {$bloco['icone']} - " . count($cenas) . " cenas</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    // Testar renderização da aba
    echo "<h2>🎨 Teste de Renderização</h2>";
    $htmlRenderizado = $cenaRenderer->renderizarAbaIluminacao();
    
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
    .category-icon i { font-size: 1.5rem; color: #3b82f6; }
    .category-title { margin: 0; font-size: 1.25rem; font-weight: 600; }
    .subcategories-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.75rem; }
    .subcategory-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem; cursor: pointer; transition: all 0.2s; }
    .subcategory-card:hover { background: #e0e7ff; border-color: #3b82f6; }
    .subcategory-title { font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; }
    .subcategory-desc { font-size: 0.75rem; color: #64748b; }
    .empty-state-iluminacao, .error-state-iluminacao { text-align: center; padding: 2rem; }
    .categories-grid.few-blocks { justify-content: flex-start; }
    .categories-grid.many-blocks { justify-content: space-between; }
    ";
    echo "</style>";
    echo $htmlRenderizado;
    echo "</div>";
    
    // Testar JavaScript Data
    echo "<h2>⚙️ Dados JavaScript</h2>";
    $dataAttributes = $cenaRenderer->renderizarDataAttributes('iluminacao');
    echo "<p><strong>Cenas para JavaScript:</strong> " . count($dataAttributes) . "</p>";
    
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
    $jsIntegracao = $cenaRenderer->gerarJavaScriptIntegracao(['iluminacao']);
    echo "<p>✅ JavaScript de integração gerado</p>";
    echo "<details style='margin: 10px 0;'>";
    echo "<summary style='cursor: pointer; font-weight: bold;'>📜 Ver código JavaScript</summary>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; max-height: 300px; overflow: auto; font-size: 12px;'>";
    echo htmlspecialchars($jsIntegracao);
    echo "</pre>";
    echo "</details>";
    
    // Debug completo
    echo "<h2>🐛 Debug Completo</h2>";
    $debugInfo = $cenaRenderer->debug();
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px;'>";
    print_r($debugInfo);
    echo "</pre>";
    
    echo "<h2>🎯 Próximos Passos</h2>";
    echo "<div style='background: #ecfdf5; padding: 15px; border-radius: 8px; border-left: 4px solid #10b981;'>";
    echo "<ol>";
    echo "<li>Certifique-se de que os dados de iluminação foram inseridos no banco</li>";
    echo "<li>Acesse o gerador de prompt: <a href='gerador_prompt_modern.php' target='_blank'>gerador_prompt_modern.php</a></li>";
    echo "<li>Vá para a aba 'Iluminação' e verifique se os blocos aparecem dinamicamente</li>";
    echo "<li>Teste a seleção de diferentes tipos de iluminação</li>";
    echo "<li>Verifique se o alinhamento automático está funcionando (2-3 blocos = esquerda, +3 = distribuído)</li>";
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

// Adicionar CSS e JavaScript para melhor visualização
echo $cenaRenderer->gerarJavaScriptIntegracao(['iluminacao']);
echo "
<script>
// Aplicar alinhamento automático
document.addEventListener('DOMContentLoaded', function() {
    if (typeof adjustCategoriesAlignment === 'function') {
        adjustCategoriesAlignment();
    } else {
        // Função de fallback para alinhamento
        const grids = document.querySelectorAll('.categories-grid');
        grids.forEach(grid => {
            const blockCount = grid.querySelectorAll('.category-section').length;
            if (blockCount <= 3) {
                grid.style.justifyContent = 'flex-start';
            } else {
                grid.style.justifyContent = 'space-between';
            }
        });
    }
});
</script>
";
?>