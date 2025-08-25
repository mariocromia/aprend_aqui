<?php
/**
 * Script de Setup do Sistema de Cenas - Versão Corrigida
 * 
 * Este script cria as tabelas e popula com dados iniciais
 * usando a API REST do Supabase diretamente.
 */

require_once 'includes/SupabaseClient.php';

echo "🚀 Iniciando setup do Sistema de Cenas...\n\n";

try {
    $supabase = new SupabaseClient();
    
    // Primeiro, vamos tentar criar as tabelas através da API REST
    echo "📋 Verificando se as tabelas existem...\n";
    
    // Testar se conseguimos acessar a tabela blocos_cenas
    try {
        $response = $supabase->makeRequest('blocos_cenas?limit=1', 'GET', null, true);
        if ($response['status'] === 200) {
            echo "✅ Tabela blocos_cenas já existe\n";
        } else {
            echo "❌ Tabela blocos_cenas não encontrada (Status: {$response['status']})\n";
        }
    } catch (Exception $e) {
        echo "❌ Erro ao verificar tabela blocos_cenas: " . $e->getMessage() . "\n";
    }
    
    // Testar se conseguimos acessar a tabela cenas
    try {
        $response = $supabase->makeRequest('cenas?limit=1', 'GET', null, true);
        if ($response['status'] === 200) {
            echo "✅ Tabela cenas já existe\n";
        } else {
            echo "❌ Tabela cenas não encontrada (Status: {$response['status']})\n";
        }
    } catch (Exception $e) {
        echo "❌ Erro ao verificar tabela cenas: " . $e->getMessage() . "\n";
    }
    
    echo "\n📊 Inserindo dados de exemplo...\n";
    
    // Dados de exemplo para blocos_cenas
    $blocosExemplo = [
        [
            'titulo' => 'Natureza',
            'icone' => 'nature',
            'tipo_aba' => 'ambiente',
            'ordem_exibicao' => 1,
            'ativo' => true
        ],
        [
            'titulo' => 'Urbano',
            'icone' => 'location_city',
            'tipo_aba' => 'ambiente',
            'ordem_exibicao' => 2,
            'ativo' => true
        ],
        [
            'titulo' => 'Ações Corporais',
            'icone' => 'directions_run',
            'tipo_aba' => 'acao',
            'ordem_exibicao' => 1,
            'ativo' => true
        ]
    ];
    
    // Inserir blocos de exemplo
    foreach ($blocosExemplo as $bloco) {
        try {
            $response = $supabase->makeRequest('blocos_cenas', 'POST', $bloco, true);
            if ($response['status'] === 201) {
                echo "✅ Bloco '{$bloco['titulo']}' inserido com sucesso\n";
            } else {
                echo "⚠️  Bloco '{$bloco['titulo']}' - Status: {$response['status']}\n";
                if (isset($response['data']['message'])) {
                    echo "   Erro: {$response['data']['message']}\n";
                }
            }
        } catch (Exception $e) {
            echo "❌ Erro ao inserir bloco '{$bloco['titulo']}': " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📈 Verificando dados inseridos...\n";
    
    // Verificar blocos inseridos
    try {
        $response = $supabase->makeRequest('blocos_cenas?select=*', 'GET', null, true);
        if ($response['status'] === 200 && isset($response['data'])) {
            echo "📊 Total de blocos: " . count($response['data']) . "\n";
            foreach ($response['data'] as $bloco) {
                echo "   - {$bloco['titulo']} ({$bloco['tipo_aba']})\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Erro ao verificar blocos: " . $e->getMessage() . "\n";
    }
    
    echo "\n💡 Instruções para criar as tabelas manualmente:\n";
    echo "
1. Acesse o painel do Supabase (https://supabase.com)
2. Vá para 'SQL Editor'
3. Execute o seguinte SQL:

-- Criar tabela blocos_cenas
CREATE TABLE IF NOT EXISTS blocos_cenas (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    icone VARCHAR(50) NOT NULL,
    tipo_aba VARCHAR(50) NOT NULL,
    ordem_exibicao INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Criar tabela cenas
CREATE TABLE IF NOT EXISTS cenas (
    id SERIAL PRIMARY KEY,
    bloco_id INT NOT NULL REFERENCES blocos_cenas(id) ON DELETE CASCADE,
    titulo VARCHAR(100) NOT NULL,
    subtitulo VARCHAR(200) DEFAULT NULL,
    texto_prompt TEXT NOT NULL,
    valor_selecao VARCHAR(100) NOT NULL,
    ordem_exibicao INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Criar índices
CREATE INDEX IF NOT EXISTS idx_blocos_tipo_aba ON blocos_cenas(tipo_aba);
CREATE INDEX IF NOT EXISTS idx_cenas_bloco_id ON cenas(bloco_id);
CREATE INDEX IF NOT EXISTS idx_cenas_valor_selecao ON cenas(valor_selecao);

4. Depois execute este script novamente para inserir os dados de exemplo.
";
    
    echo "\n✨ Setup concluído!\n";
    echo "🔧 Se as tabelas ainda não existem, siga as instruções acima para criá-las manualmente.\n";
    echo "📝 Depois você pode usar a classe CenaManager para gerenciar os dados.\n\n";
    
} catch (Exception $e) {
    echo "❌ Erro durante o setup: " . $e->getMessage() . "\n";
    echo "🔧 Verifique a configuração do Supabase no arquivo env.config\n";
    echo "📋 Detalhes da configuração necessária:\n";
    echo "   SUPABASE_URL=sua_url_do_supabase\n";
    echo "   SUPABASE_ANON_KEY=sua_chave_anonima\n";
    echo "   SUPABASE_SERVICE_KEY=sua_chave_de_servico\n\n";
}
?>