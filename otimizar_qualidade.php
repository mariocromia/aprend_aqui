<?php
/**
 * Script para otimizar e limpar dados da aba qualidade
 */
require_once 'includes/Environment.php';

try {
    echo "<h1>Otimizando Aba Qualidade</h1>";
    
    // Configurações Supabase
    $supabaseUrl = Environment::get('SUPABASE_URL');
    $supabaseKey = Environment::get('SUPABASE_SERVICE_KEY');
    
    if (empty($supabaseUrl) || empty($supabaseKey)) {
        throw new Exception("Configurações Supabase não encontradas");
    }
    
    // Headers para requisições
    $headers = [
        'Content-Type: application/json',
        'apikey: ' . $supabaseKey,
        'Authorization: Bearer ' . $supabaseKey
    ];
    
    echo "<h2>1. Verificando blocos duplicados...</h2>";
    
    // Buscar todos os blocos de qualidade
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $supabaseUrl . '/rest/v1/blocos_cenas?tipo_aba=eq.qualidade&select=*');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $blocos = json_decode($response, true);
        echo "<p>Encontrados " . count($blocos) . " blocos de qualidade</p>";
        
        // Agrupar por título para encontrar duplicatas
        $grupos = [];
        foreach ($blocos as $bloco) {
            $titulo = $bloco['titulo'];
            if (!isset($grupos[$titulo])) {
                $grupos[$titulo] = [];
            }
            $grupos[$titulo][] = $bloco;
        }
        
        echo "<h3>Blocos encontrados por título:</h3>";
        $blocosParaRemover = [];
        foreach ($grupos as $titulo => $blocosGrupo) {
            $count = count($blocosGrupo);
            echo "<p><strong>{$titulo}</strong>: {$count} blocos</p>";
            
            if ($count > 1) {
                echo "<ul>";
                // Manter apenas o primeiro (mais antigo ou com menor ID)
                usort($blocosGrupo, function($a, $b) {
                    return $a['id'] - $b['id'];
                });
                
                $manter = array_shift($blocosGrupo); // Remove o primeiro e o mantém
                echo "<li>MANTER: ID {$manter['id']} (criado primeiro)</li>";
                
                foreach ($blocosGrupo as $bloco) {
                    echo "<li style='color: red;'>REMOVER: ID {$bloco['id']}</li>";
                    $blocosParaRemover[] = $bloco['id'];
                }
                echo "</ul>";
            }
        }
        
        // Remover blocos duplicados
        if (!empty($blocosParaRemover)) {
            echo "<h2>2. Removendo blocos duplicados...</h2>";
            foreach ($blocosParaRemover as $blocoId) {
                echo "<p>Removendo bloco ID: {$blocoId}...</p>";
                
                // Primeiro remover cenas associadas
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $supabaseUrl . "/rest/v1/cenas?bloco_id=eq.{$blocoId}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 204) {
                    echo "<p style='color: green;'>✅ Cenas removidas do bloco {$blocoId}</p>";
                } else {
                    echo "<p style='color: orange;'>⚠️ Nenhuma cena encontrada para bloco {$blocoId}</p>";
                }
                
                // Depois remover o bloco
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $supabaseUrl . "/rest/v1/blocos_cenas?id=eq.{$blocoId}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 204) {
                    echo "<p style='color: green;'>✅ Bloco {$blocoId} removido</p>";
                } else {
                    echo "<p style='color: red;'>❌ Erro ao remover bloco {$blocoId}: HTTP {$httpCode}</p>";
                }
            }
        } else {
            echo "<p style='color: green;'>✅ Nenhum bloco duplicado encontrado</p>";
        }
        
        echo "<h2>3. Verificando cenas órfãs...</h2>";
        
        // Buscar cenas que podem estar associadas a blocos inexistentes
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $supabaseUrl . '/rest/v1/cenas?select=id,titulo,bloco_id,valor_selecao&valor_selecao=like.*masterpiece*,*best_quality*,*ultra_high*,*premium*,*detailed*');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $cenas = json_decode($response, true);
            echo "<p>Encontradas " . count($cenas) . " cenas relacionadas à qualidade</p>";
            
            // Verificar se os blocos dessas cenas existem
            $blocosExistentes = array_column($blocos, 'id');
            $cenasOrfas = [];
            
            foreach ($cenas as $cena) {
                if (!in_array($cena['bloco_id'], $blocosExistentes)) {
                    $cenasOrfas[] = $cena;
                }
            }
            
            if (!empty($cenasOrfas)) {
                echo "<p style='color: orange;'>⚠️ Encontradas " . count($cenasOrfas) . " cenas órfãs</p>";
                foreach ($cenasOrfas as $cena) {
                    echo "<p>Cena órfã: {$cena['titulo']} (ID: {$cena['id']}, Bloco: {$cena['bloco_id']})</p>";
                }
            } else {
                echo "<p style='color: green;'>✅ Nenhuma cena órfã encontrada</p>";
            }
        }
        
        echo "<h2>4. Status final...</h2>";
        echo "<p><a href='test_qualidade_dinamica.php' style='color: #3b82f6; font-weight: bold;'>🧪 Testar aba qualidade após otimização</a></p>";
        echo "<p><a href='gerador_prompt_modern.php' style='color: #10b981; font-weight: bold;'>🚀 Ir para o gerador</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ Erro ao buscar blocos: HTTP {$httpCode}</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>