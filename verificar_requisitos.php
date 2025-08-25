<?php
/**
 * Verificação de Requisitos do Sistema
 */

echo "🔍 VERIFICAÇÃO DE REQUISITOS\n";
echo "============================\n\n";

// Verificar extensões PHP necessárias
$extensoes = ['curl', 'json', 'mbstring'];
$problemas = [];

foreach ($extensoes as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext: OK\n";
    } else {
        echo "❌ $ext: FALTANDO\n";
        $problemas[] = $ext;
    }
}

echo "\n📋 STATUS GERAL:\n";
if (empty($problemas)) {
    echo "✅ Todos os requisitos atendidos!\n";
    echo "✅ Aba técnica pode carregar dados dinâmicos\n";
} else {
    echo "❌ Problemas encontrados:\n";
    foreach ($problemas as $problema) {
        echo "   • $problema não instalado\n";
    }
    
    echo "\n🛠️ SOLUÇÕES:\n";
    if (in_array('curl', $problemas)) {
        echo "Para curl:\n";
        echo "1. Windows (XAMPP): Descomente ;extension=curl no php.ini\n";
        echo "2. Ubuntu: sudo apt-get install php-curl\n";
        echo "3. CentOS: sudo yum install php-curl\n";
    }
}

echo "\n🎯 SOBRE A ABA TÉCNICA:\n";
echo "Status atual: Configurada para carregamento dinâmico\n";
echo "Dados necessários: Execute sql_popular_aba_tecnica.sql no Supabase\n";
echo "API endpoint: Configurado em load_tab_content.php\n";

echo "\n📱 TESTE MANUAL:\n";
echo "1. Acesse o gerador de prompt\n";
echo "2. Clique na aba 'Técnica'\n";
echo "3. Se mostrar 'Sistema técnico indisponível' = problema de conexão\n";
echo "4. Se mostrar skeleton loading infinito = dados não inseridos\n";

echo "\n✨ Skeleton loader: Revertido para 5 blocos padrão\n";
?>