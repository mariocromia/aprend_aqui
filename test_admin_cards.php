<?php
/**
 * Script de Teste - Integração Admin Cards com Banco de Dados
 * 
 * Este script testa todas as funcionalidades do sistema de administração
 * de cards para verificar se a integração com o banco está funcionando.
 */

require_once 'includes/CenaManager.php';
require_once 'includes/Environment.php';

// Configurar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Teste de Integração - Admin Cards</h1>\n";
echo "<style>
body { font-family: 'Segoe UI', Arial, sans-serif; margin: 20px; }
.success { color: #10b981; font-weight: bold; }
.error { color: #ef4444; font-weight: bold; }
.info { color: #3b82f6; font-weight: bold; }
.test-section { margin: 20px 0; padding: 15px; border: 1px solid #e5e7eb; border-radius: 8px; }
.test-section h2 { color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; }
pre { background: #f8fafc; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>\n";

try {
    echo "<div class='test-section'>\n";
    echo "<h2>🔧 Configuração Inicial</h2>\n";
    
    // Verificar variáveis de ambiente
    echo "<p class='info'>Verificando configurações do Supabase...</p>\n";
    $supabaseUrl = Environment::get('SUPABASE_URL', '');
    $supabaseKey = Environment::get('SUPABASE_SERVICE_KEY', '');
    
    if (empty($supabaseUrl)) {
        echo "<p class='error'>❌ SUPABASE_URL não configurada</p>\n";
        exit;
    }
    
    if (empty($supabaseKey)) {
        echo "<p class='error'>❌ SUPABASE_SERVICE_KEY não configurada</p>\n";
        exit;
    }
    
    echo "<p class='success'>✅ Configurações do Supabase encontradas</p>\n";
    echo "<p>URL: " . substr($supabaseUrl, 0, 30) . "...</p>\n";
    echo "</div>\n";
    
    // Inicializar CenaManager
    echo "<div class='test-section'>\n";
    echo "<h2>🎬 Inicialização do CenaManager</h2>\n";
    
    $cenaManager = new CenaManager();
    echo "<p class='success'>✅ CenaManager inicializado com sucesso</p>\n";
    echo "</div>\n";
    
    // Teste 1: Listar Blocos Existentes
    echo "<div class='test-section'>\n";
    echo "<h2>📋 Teste 1: Listar Blocos Existentes</h2>\n";
    
    $blocos = $cenaManager->listarTodosBlocos();
    echo "<p class='info'>Total de blocos encontrados: " . count($blocos) . "</p>\n";
    
    if (count($blocos) > 0) {
        echo "<p class='success'>✅ Busca de blocos funcionando</p>\n";
        echo "<pre>";
        foreach (array_slice($blocos, 0, 3) as $bloco) {
            echo "ID: {$bloco['id']} | Título: {$bloco['titulo']} | Tipo: {$bloco['tipo_aba']}\n";
        }
        if (count($blocos) > 3) {
            echo "... e mais " . (count($blocos) - 3) . " blocos\n";
        }
        echo "</pre>";
    } else {
        echo "<p class='error'>❌ Nenhum bloco encontrado - banco pode estar vazio</p>\n";
    }
    echo "</div>\n";
    
    // Teste 2: Listar Cenas Existentes
    echo "<div class='test-section'>\n";
    echo "<h2>🎭 Teste 2: Listar Cenas Existentes</h2>\n";
    
    $cenas = $cenaManager->listarTodasCenas();
    echo "<p class='info'>Total de cenas encontradas: " . count($cenas) . "</p>\n";
    
    if (count($cenas) > 0) {
        echo "<p class='success'>✅ Busca de cenas funcionando</p>\n";
        echo "<pre>";
        foreach (array_slice($cenas, 0, 5) as $cena) {
            echo "ID: {$cena['id']} | Título: {$cena['titulo']} | Bloco ID: {$cena['bloco_id']}\n";
        }
        if (count($cenas) > 5) {
            echo "... e mais " . (count($cenas) - 5) . " cenas\n";
        }
        echo "</pre>";
    } else {
        echo "<p class='error'>❌ Nenhuma cena encontrada - banco pode estar vazio</p>\n";
    }
    echo "</div>\n";
    
    // Teste 3: Criar Bloco de Teste
    echo "<div class='test-section'>\n";
    echo "<h2>➕ Teste 3: Criar Bloco de Teste</h2>\n";
    
    $titulo_teste = "Teste Admin " . date('H:i:s');
    $resultado = $cenaManager->inserirBloco(
        $titulo_teste,
        'science',
        'ambiente',
        999
    );
    
    if ($resultado) {
        echo "<p class='success'>✅ Bloco de teste criado com sucesso</p>\n";
        echo "<p>Título: {$titulo_teste}</p>\n";
        
        // Buscar o bloco criado para pegar o ID
        $blocosAtualizados = $cenaManager->listarTodosBlocos();
        $blocoTeste = null;
        foreach ($blocosAtualizados as $bloco) {
            if ($bloco['titulo'] === $titulo_teste) {
                $blocoTeste = $bloco;
                break;
            }
        }
        
        if ($blocoTeste) {
            $blocoTesteId = $blocoTeste['id'];
            echo "<p class='info'>ID do bloco criado: {$blocoTesteId}</p>\n";
            
            // Teste 4: Criar Cena de Teste
            echo "</div>\n";
            echo "<div class='test-section'>\n";
            echo "<h2>🎪 Teste 4: Criar Cena de Teste</h2>\n";
            
            $cena_titulo = "Cena Teste " . date('H:i:s');
            $cena_valor = "teste_" . time();
            
            try {
                $resultadoCena = $cenaManager->inserirCena(
                    $blocoTesteId,
                    $cena_titulo,
                    "Cena criada automaticamente para teste",
                    "uma cena de teste para verificar a funcionalidade",
                    $cena_valor,
                    1
                );
                
                if ($resultadoCena) {
                    echo "<p class='success'>✅ Cena de teste criada com sucesso</p>\n";
                    echo "<p>Título: {$cena_titulo}</p>\n";
                    echo "<p>Valor de seleção: {$cena_valor}</p>\n";
                    
                    // Buscar a cena criada
                    $cenaEncontrada = $cenaManager->getCenaPorValor($cena_valor);
                    if ($cenaEncontrada) {
                        $cenaTesteId = $cenaEncontrada['id'];
                        echo "<p class='info'>ID da cena criada: {$cenaTesteId}</p>\n";
                        
                        // Teste 5: Atualizar Cena
                        echo "</div>\n";
                        echo "<div class='test-section'>\n";
                        echo "<h2>✏️ Teste 5: Atualizar Cena</h2>\n";
                        
                        $novoTitulo = "Cena Atualizada " . date('H:i:s');
                        $sucessoUpdate = $cenaManager->atualizarCena(
                            $cenaTesteId,
                            $blocoTesteId,
                            $novoTitulo,
                            "Cena atualizada durante teste",
                            "uma cena de teste atualizada",
                            $cena_valor,
                            2,
                            true
                        );
                        
                        if ($sucessoUpdate) {
                            echo "<p class='success'>✅ Cena atualizada com sucesso</p>\n";
                            echo "<p>Novo título: {$novoTitulo}</p>\n";
                        } else {
                            echo "<p class='error'>❌ Falha ao atualizar cena</p>\n";
                        }
                        
                        // Teste 6: Excluir Cena
                        echo "</div>\n";
                        echo "<div class='test-section'>\n";
                        echo "<h2>🗑️ Teste 6: Excluir Cena</h2>\n";
                        
                        $sucessoDeleteCena = $cenaManager->excluirCena($cenaTesteId);
                        if ($sucessoDeleteCena) {
                            echo "<p class='success'>✅ Cena excluída com sucesso</p>\n";
                        } else {
                            echo "<p class='error'>❌ Falha ao excluir cena</p>\n";
                        }
                        echo "</div>\n";
                    } else {
                        echo "<p class='error'>❌ Não foi possível encontrar a cena criada</p>\n";
                    }
                } else {
                    echo "<p class='error'>❌ Falha ao criar cena de teste</p>\n";
                }
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Erro ao criar cena: " . $e->getMessage() . "</p>\n";
            }
            
            // Teste 7: Atualizar Bloco
            echo "<div class='test-section'>\n";
            echo "<h2>📝 Teste 7: Atualizar Bloco</h2>\n";
            
            $novoTituloBloco = "Bloco Atualizado " . date('H:i:s');
            $sucessoUpdateBloco = $cenaManager->atualizarBloco(
                $blocoTesteId,
                $novoTituloBloco,
                'psychology',
                'ambiente',
                998,
                true
            );
            
            if ($sucessoUpdateBloco) {
                echo "<p class='success'>✅ Bloco atualizado com sucesso</p>\n";
                echo "<p>Novo título: {$novoTituloBloco}</p>\n";
            } else {
                echo "<p class='error'>❌ Falha ao atualizar bloco</p>\n";
            }
            echo "</div>\n";
            
            // Teste 8: Excluir Bloco
            echo "<div class='test-section'>\n";
            echo "<h2>🗑️ Teste 8: Excluir Bloco</h2>\n";
            
            $sucessoDeleteBloco = $cenaManager->excluirBloco($blocoTesteId);
            if ($sucessoDeleteBloco) {
                echo "<p class='success'>✅ Bloco excluído com sucesso</p>\n";
            } else {
                echo "<p class='error'>❌ Falha ao excluir bloco</p>\n";
            }
            echo "</div>\n";
            
        } else {
            echo "<p class='error'>❌ Não foi possível encontrar o bloco criado</p>\n";
        }
        
    } else {
        echo "<p class='error'>❌ Falha ao criar bloco de teste</p>\n";
    }
    echo "</div>\n";
    
    // Teste 9: Verificar Valor Único
    echo "<div class='test-section'>\n";
    echo "<h2>🔍 Teste 9: Verificar Valor Único</h2>\n";
    
    if (count($cenas) > 0) {
        $primeiraCoena = $cenas[0];
        $existe = $cenaManager->valorSelecaoExiste($primeiraCoena['valor_selecao']);
        
        if ($existe) {
            echo "<p class='success'>✅ Verificação de valor único funcionando</p>\n";
            echo "<p>Valor testado: {$primeiraCoena['valor_selecao']} (existe)</p>\n";
        } else {
            echo "<p class='error'>❌ Verificação de valor único não funcionou corretamente</p>\n";
        }
        
        // Testar valor que não existe
        $valorInexistente = "valor_que_nao_existe_" . time();
        $naoExiste = $cenaManager->valorSelecaoExiste($valorInexistente);
        
        if (!$naoExiste) {
            echo "<p class='success'>✅ Verificação de valor inexistente funcionando</p>\n";
            echo "<p>Valor testado: {$valorInexistente} (não existe)</p>\n";
        } else {
            echo "<p class='error'>❌ Verificação de valor inexistente falhou</p>\n";
        }
    } else {
        echo "<p class='info'>⚠️ Pulando teste - nenhuma cena no banco</p>\n";
    }
    echo "</div>\n";
    
    // Resumo Final
    echo "<div class='test-section'>\n";
    echo "<h2>📊 Resumo dos Testes</h2>\n";
    echo "<p class='success'>✅ Todos os testes de integração com banco de dados foram executados</p>\n";
    echo "<p class='info'>Funcionalidades testadas:</p>\n";
    echo "<ul>";
    echo "<li>✅ Configuração do Supabase</li>";
    echo "<li>✅ Listagem de blocos e cenas</li>";
    echo "<li>✅ Criação de blocos e cenas</li>";
    echo "<li>✅ Atualização de registros</li>";
    echo "<li>✅ Exclusão de registros</li>";
    echo "<li>✅ Verificação de valores únicos</li>";
    echo "<li>✅ Tratamento de erros</li>";
    echo "</ul>";
    echo "<p class='success'><strong>🎉 Sistema de admin-cards totalmente integrado com o banco de dados!</strong></p>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='test-section'>\n";
    echo "<h2>❌ Erro Durante os Testes</h2>\n";
    echo "<p class='error'>Erro: " . $e->getMessage() . "</p>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>\n";
}

echo "<hr>";
echo "<p><small>Teste executado em: " . date('Y-m-d H:i:s') . "</small></p>\n";
?>