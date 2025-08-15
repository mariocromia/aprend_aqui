<?php
/**
 * Teste direto do WAHA para verificar envio de mensagem
 */

// Simular dados de teste
$whatsapp = "5521988689055"; // Número de teste
$codigo = "123456";
$nome = "Teste";
$sessionName = 'dev_aprend_aqui_cadastro';
$wahaServer = 'https://waha.zapfunil.app';

echo "<h1>🧪 Teste Direto WAHA</h1>\n";
echo "<pre>\n";

echo "1. Configurações:\n";
echo "   Servidor: $wahaServer\n";
echo "   Sessão: $sessionName\n";
echo "   WhatsApp: $whatsapp\n";
echo "   Código: $codigo\n\n";

// Verificar status da sessão
echo "2. Verificando status da sessão...\n";
$statusUrl = "$wahaServer/api/sessions/$sessionName";
$statusResponse = file_get_contents($statusUrl);
$statusData = json_decode($statusResponse, true);
echo "   Status: " . ($statusData['status'] ?? 'UNKNOWN') . "\n\n";

if ($statusData['status'] !== 'WORKING') {
    echo "❌ Sessão não está WORKING. Parando teste.\n";
    exit;
}

// Preparar mensagem
$message = "🔐 *Prompt Builder IA*\n\n" .
          "Olá, {$nome}!\n\n" .
          "Seu código de ativação é:\n" .
          "*{$codigo}*\n\n" .
          "Este código expira em 10 minutos.\n\n" .
          "Se você não solicitou este código, ignore esta mensagem.";

echo "3. Enviando mensagem...\n";
echo "   Mensagem: " . substr($message, 0, 50) . "...\n";

// Enviar mensagem
$sendUrl = "$wahaServer/api/sendText";
$sendData = [
    'session' => $sessionName,
    'chatId' => $whatsapp . '@c.us',
    'text' => $message
];

echo "   URL: $sendUrl\n";
echo "   Dados: " . json_encode($sendData) . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $sendUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "4. Resultado:\n";
echo "   HTTP Code: $httpCode\n";
echo "   cURL Error: " . ($curlError ?: 'Nenhum') . "\n";
echo "   Resposta: $response\n\n";

if ($response) {
    $result = json_decode($response, true);
    if ($result) {
        echo "5. Análise da resposta:\n";
        foreach ($result as $key => $value) {
            echo "   $key: " . json_encode($value) . "\n";
        }
        
        // Verificar se foi sucesso
        $success = false;
        if (isset($result['sent']) && $result['sent']) {
            $success = true;
        } elseif (isset($result['success']) && $result['success']) {
            $success = true;
        } elseif (isset($result['id']) && !empty($result['id'])) {
            $success = true;
        } elseif (isset($result['key']) && isset($result['messageTimestamp'])) {
            $success = true;
        } elseif (!isset($result['error']) && !empty($result)) {
            $success = true;
        }
        
        echo "\n6. Conclusão: " . ($success ? "✅ SUCESSO" : "❌ FALHA") . "\n";
    }
}

echo "</pre>\n";
?>