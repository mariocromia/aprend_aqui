<?php
/**
 * Script de Setup do Sistema de Cenas
 * 
 * Este script cria as tabelas e popula com dados iniciais
 * Execute apenas uma vez para configurar o sistema de cenas.
 */

require_once 'includes/SupabaseClient.php';

echo "🚀 Iniciando setup do Sistema de Cenas...\n\n";

try {
    $supabase = new SupabaseClient();
    
    // 1. Executar criação das tabelas
    echo "📋 Criando tabelas...\n";
    
    $createTablesSql = file_get_contents(__DIR__ . '/database/create_cenas_tables.sql');
    
    if (!$createTablesSql) {
        throw new Exception("Não foi possível ler o arquivo create_cenas_tables.sql");
    }
    
    // Dividir o SQL em comandos individuais
    $commands = explode(';', $createTablesSql);
    
    foreach ($commands as $command) {
        $command = trim($command);
        if (!empty($command) && !preg_match('/^--/', $command)) {
            try {
                $result = $supabase->rawQuery($command);
                echo "✅ Comando executado com sucesso\n";
            } catch (Exception $e) {
                echo "⚠️  Aviso: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n📊 Inserindo dados iniciais...\n";
    
    // 2. Executar inserção dos dados
    $insertDataSql = file_get_contents(__DIR__ . '/database/insert_cenas_data.sql');
    
    if (!$insertDataSql) {
        throw new Exception("Não foi possível ler o arquivo insert_cenas_data.sql");
    }
    
    // Dividir o SQL em comandos individuais
    $insertCommands = explode(';', $insertDataSql);
    
    foreach ($insertCommands as $command) {
        $command = trim($command);
        if (!empty($command) && !preg_match('/^--/', $command)) {
            try {
                $result = $supabase->rawQuery($command);
                echo "✅ Dados inseridos\n";
            } catch (Exception $e) {
                echo "⚠️  Aviso: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n🎉 Setup concluído com sucesso!\n\n";
    
    // 3. Verificar dados inseridos
    echo "📈 Verificando dados inseridos:\n";
    
    $verificacoes = [
        "SELECT COUNT(*) as total FROM blocos_cenas" => "Blocos de cenas",
        "SELECT COUNT(*) as total FROM cenas" => "Cenas individuais",
        "SELECT tipo_aba, COUNT(*) as total FROM blocos_cenas GROUP BY tipo_aba" => "Blocos por tipo"
    ];
    
    foreach ($verificacoes as $query => $desc) {
        try {
            $result = $supabase->rawQuery($query);
            if (isset($result['data'])) {
                echo "📊 {$desc}: ";
                if (strpos($query, 'GROUP BY') !== false) {
                    echo "\n";
                    foreach ($result['data'] as $row) {
                        echo "   - {$row['tipo_aba']}: {$row['total']}\n";
                    }
                } else {
                    echo $result['data'][0]['total'] ?? '0';
                    echo "\n";
                }
            }
        } catch (Exception $e) {
            echo "❌ Erro na verificação: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✨ Tudo pronto! O sistema de cenas já pode ser usado.\n";
    echo "💡 Agora você pode modificar o arquivo gerador_prompt_modern.php para usar os dados do banco.\n\n";
    
} catch (Exception $e) {
    echo "❌ Erro durante o setup: " . $e->getMessage() . "\n";
    echo "🔧 Verifique a configuração do banco de dados e tente novamente.\n";
}
?>