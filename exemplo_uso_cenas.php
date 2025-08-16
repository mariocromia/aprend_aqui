<?php
/**
 * EXEMPLO DE USO DO SISTEMA DE CENAS
 * 
 * Este arquivo demonstra como integrar o sistema de cenas dinâmicas
 * no gerador_prompt_modern.php existente.
 */

require_once 'includes/CenaRenderer.php';
require_once 'includes/CenaManager.php';

// Exemplo 1: Renderizar uma aba completa
echo "=== EXEMPLO 1: ABA COMPLETA ===\n";
$htmlAbaAmbiente = CenaRenderer::gerarAba(
    'ambiente', 
    'tab-ambiente', 
    'Cena/Ambiente', 
    'Escolha o cenário para seu vídeo',
    'landscape'
);

echo "HTML gerado para aba ambiente:\n";
echo substr($htmlAbaAmbiente, 0, 500) . "...\n\n";

// Exemplo 2: Usar CenaManager diretamente
echo "=== EXEMPLO 2: DADOS DO MANAGER ===\n";
$cenaManager = new CenaManager();

// Buscar blocos de ambiente
$blocosAmbiente = $cenaManager->getBlocosPorTipo('ambiente');
echo "Blocos de ambiente encontrados: " . count($blocosAmbiente) . "\n";

foreach ($blocosAmbiente as $bloco) {
    echo "- {$bloco['titulo']} (ícone: {$bloco['icone']})\n";
}

// Buscar cenas de um bloco específico (exemplo: bloco ID 1)
if (!empty($blocosAmbiente)) {
    $primeiroBloco = $blocosAmbiente[0];
    $cenas = $cenaManager->getCenasPorBloco($primeiroBloco['id']);
    echo "\nCenas do bloco '{$primeiroBloco['titulo']}': " . count($cenas) . "\n";
    
    foreach ($cenas as $cena) {
        echo "- {$cena['titulo']}: {$cena['texto_prompt']}\n";
    }
}

echo "\n=== EXEMPLO 3: BUSCAR CENA ESPECÍFICA ===\n";
$cenaEspecifica = $cenaManager->getCenaPorValor('floresta');
if ($cenaEspecifica) {
    echo "Cena 'floresta' encontrada:\n";
    echo "- Título: {$cenaEspecifica['titulo']}\n";
    echo "- Texto prompt: {$cenaEspecifica['texto_prompt']}\n";
    echo "- Tipo aba: {$cenaEspecifica['tipo_aba']}\n";
}

echo "\n=== EXEMPLO 4: INTEGRAÇÃO NO ARQUIVO PRINCIPAL ===\n";
echo "
Para integrar no gerador_prompt_modern.php, substitua o HTML estático por:

<?php
require_once 'includes/CenaRenderer.php';

// No lugar do HTML da aba ambiente
echo CenaRenderer::gerarAba(
    'ambiente', 
    'tab-ambiente', 
    'Cena/Ambiente', 
    'Escolha o cenário para seu vídeo',
    'landscape'
);

// Para aba ação
echo CenaRenderer::gerarAba(
    'acao', 
    'tab-acao', 
    'Ações e Movimentos', 
    'Configure ações, movimentos e atividades dos personagens',
    'play_arrow'
);
?>

Isso substitui completamente o HTML estático pelos dados do banco!
";

echo "\n=== INSTRUÇÕES DE IMPLEMENTAÇÃO ===\n";
echo "
1. Execute: php setup_cenas_database.php
2. Modifique gerador_prompt_modern.php para usar CenaRenderer
3. Remova o HTML estático das abas
4. Use o sistema para adicionar/editar cenas dinamicamente

Vantagens:
✅ Cards gerenciados pelo banco de dados
✅ Fácil adição/remoção de cenas
✅ Consistent rendering
✅ Cache automático para performance
✅ Fallback para quando banco falha
✅ Sistema de administração pronto
";
?>