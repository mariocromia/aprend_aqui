<?php
/**
 * Script para Corrigir Constraint da Tabela
 * Adiciona 'estilo_visual' aos valores permitidos na constraint tipo_aba
 * 
 * Execute ANTES do popular_estilo_visual.php
 */

require_once 'includes/Environment.php';
require_once 'includes/SupabaseClient.php';

try {
    echo "🔧 Verificando e corrigindo constraint da tabela blocos_cenas...\n\n";
    
    $supabase = new SupabaseClient();
    
    // Verificar constraint atual
    echo "🔍 Verificando constraint atual...\n";
    
    // Tentar inserir um registro de teste para verificar o erro
    $dadosTeste = [
        'titulo' => 'TESTE_CONSTRAINT',
        'icone' => 'test',
        'tipo_aba' => 'estilo_visual',
        'ordem_exibicao' => 999,
        'ativo' => true
    ];
    
    $resultado = $supabase->makeRequest('blocos_cenas', 'POST', $dadosTeste, true);
    
    if ($resultado['status'] === 201) {
        echo "✅ Constraint já permite 'estilo_visual'!\n";
        
        // Limpar registro de teste
        $testId = $resultado['data'][0]['id'];
        $supabase->makeRequest("blocos_cenas?id=eq.$testId", 'DELETE', null, true);
        echo "🧹 Registro de teste removido.\n\n";
        
        echo "✅ Tudo pronto! Você pode executar popular_estilo_visual.php\n";
        
    } else {
        echo "❌ Constraint não permite 'estilo_visual'.\n";
        echo "📋 Resposta do servidor: " . json_encode($resultado) . "\n\n";
        
        echo "🛠️  SOLUÇÕES POSSÍVEIS:\n\n";
        
        echo "1️⃣  VIA PAINEL SUPABASE:\n";
        echo "   - Acesse o painel Supabase\n";
        echo "   - Vá em Database > Tables > blocos_cenas\n";
        echo "   - Clique na coluna 'tipo_aba'\n";
        echo "   - Edite a constraint para incluir 'estilo_visual'\n";
        echo "   - Constraint atual provavelmente: CHECK (tipo_aba IN ('ambiente', 'iluminacao', ...))\n";
        echo "   - Nova constraint: CHECK (tipo_aba IN ('ambiente', 'iluminacao', 'estilo_visual', ...))\n\n";
        
        echo "2️⃣  VIA SQL DIRETO:\n";
        echo "   Execute este SQL no Supabase:\n\n";
        echo "   -- Remover constraint antiga\n";
        echo "   ALTER TABLE blocos_cenas DROP CONSTRAINT blocos_cenas_tipo_aba_check;\n\n";
        echo "   -- Adicionar nova constraint\n";
        echo "   ALTER TABLE blocos_cenas ADD CONSTRAINT blocos_cenas_tipo_aba_check \n";
        echo "   CHECK (tipo_aba IN ('ambiente', 'iluminacao', 'estilo_visual', 'tecnica', 'elementos_especiais', 'qualidade', 'avatar', 'camera', 'voz', 'acao'));\n\n";
        
        echo "3️⃣  VERIFICAR COLUNAS EXISTENTES:\n";
        
        // Verificar quais valores já existem na tabela
        $valoresExistentes = $supabase->makeRequest(
            'blocos_cenas?select=tipo_aba&order=tipo_aba',
            'GET', null, true
        );
        
        if ($valoresExistentes['status'] === 200) {
            $tipos = array_unique(array_column($valoresExistentes['data'], 'tipo_aba'));
            echo "   Valores já existentes na tabela: " . implode(', ', $tipos) . "\n";
            echo "   A constraint deve incluir todos estes valores MAIS 'estilo_visual'\n\n";
        }
        
        echo "4️⃣  CONSTRAINT SUGERIDA COMPLETA:\n";
        echo "   CHECK (tipo_aba IN (\n";
        echo "     'ambiente',\n";
        echo "     'estilo_visual',\n";
        echo "     'iluminacao',\n";
        echo "     'tecnica',\n";
        echo "     'elementos_especiais',\n";
        echo "     'qualidade',\n";
        echo "     'avatar',\n";
        echo "     'camera',\n";
        echo "     'voz',\n";
        echo "     'acao'\n";
        echo "   ))\n\n";
        
        echo "💡 Após corrigir a constraint, execute: php popular_estilo_visual.php\n";
    }

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "📍 Linha: " . $e->getLine() . "\n";
    
    if (php_sapi_name() !== 'cli') {
        echo "<br><pre>" . $e->getTraceAsString() . "</pre>";
    }
}
?>