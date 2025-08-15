<?php
/**
 * Teste da lógica de reset do contador após desbloqueio
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/SupabaseClient.php';

echo "<h1>🔐 Teste de Reset do Contador de Tentativas</h1>";

try {
    $supabase = new SupabaseClient();
    $email = 'mariocromia@gmail.com';
    
    echo "<h2>1. Estado atual da conta</h2>";
    $user = $supabase->getUserByEmail($email);
    
    if (!$user) {
        echo "❌ Usuário não encontrado<br>";
        exit;
    }
    
    echo "📧 Email: " . $user['email'] . "<br>";
    echo "👤 Nome: " . $user['nome'] . "<br>";
    echo "🔢 Tentativas falhadas: " . ($user['tentativas_login_falhadas'] ?? 0) . "<br>";
    echo "🚫 Bloqueada até: " . ($user['conta_bloqueada_ate'] ?? 'Não bloqueada') . "<br>";
    
    if (!empty($user['conta_bloqueada_ate'])) {
        $bloqueadaAte = strtotime($user['conta_bloqueada_ate']);
        $agora = time();
        
        if ($agora < $bloqueadaAte) {
            $minutosRestantes = ceil(($bloqueadaAte - $agora) / 60);
            echo "<p style='color: red;'>⏰ <strong>Conta ainda está BLOQUEADA por mais $minutosRestantes minuto(s)</strong></p>";
        } else {
            echo "<p style='color: orange;'>⏰ <strong>Conta pode ser DESBLOQUEADA (período expirou)</strong></p>";
            
            echo "<h2>2. Simulando desbloqueio</h2>";
            
            // Simular a lógica do login.php
            echo "<p>🔄 Executando reset automático...</p>";
            $updateResult = $supabase->updateUser($user['id'], [
                'conta_bloqueada_ate' => null,
                'tentativas_login_falhadas' => 0
            ]);
            
            if ($updateResult) {
                echo "<p style='color: green;'>✅ <strong>Reset realizado com sucesso!</strong></p>";
                
                // Verificar estado após reset
                $userAtualizado = $supabase->getUserByEmail($email);
                echo "<h3>Estado após reset:</h3>";
                echo "🔢 Tentativas falhadas: " . ($userAtualizado['tentativas_login_falhadas'] ?? 0) . "<br>";
                echo "🚫 Bloqueada até: " . ($userAtualizado['conta_bloqueada_ate'] ?? 'Não bloqueada') . "<br>";
                
                if (($userAtualizado['tentativas_login_falhadas'] ?? 0) == 0 && empty($userAtualizado['conta_bloqueada_ate'])) {
                    echo "<p style='color: green; font-size: 18px;'>🎉 <strong>SUCESSO!</strong> Contador resetado e conta desbloqueada</p>";
                } else {
                    echo "<p style='color: red;'>❌ Erro: Reset não funcionou corretamente</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Erro ao fazer reset</p>";
            }
        }
    } else {
        echo "<p style='color: green;'>✅ Conta não está bloqueada</p>";
        
        echo "<h2>2. Simular bloqueio para teste</h2>";
        echo "<form method='post'>";
        echo "<button type='submit' name='simular_bloqueio' style='padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 5px;'>";
        echo "🚫 Simular Bloqueio (5 tentativas)";
        echo "</button>";
        echo "</form>";
        
        if (isset($_POST['simular_bloqueio'])) {
            echo "<p>🔄 Simulando 5 tentativas falhadas...</p>";
            $bloqueioResult = $supabase->updateUser($user['id'], [
                'tentativas_login_falhadas' => 5,
                'conta_bloqueada_ate' => date('c', time() + 900) // 15 minutos
            ]);
            
            if ($bloqueioResult) {
                echo "<p style='color: orange;'>⚠️ Conta bloqueada por 15 minutos para teste</p>";
                echo "<p>🔄 <a href='" . $_SERVER['PHP_SELF'] . "'>Recarregar página</a> para ver o estado bloqueado</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>📋 Como funciona a correção:</h2>";
echo "<ol>";
echo "<li><strong>Usuário tenta fazer login</strong> → Sistema verifica se conta está bloqueada</li>";
echo "<li><strong>Se bloqueada mas período expirou</strong> → Reset automático do contador (0 tentativas)</li>";
echo "<li><strong>Usuário pode tentar novamente</strong> → Começa com contador zerado</li>";
echo "<li><strong>Antes da correção:</strong> Usuário seria bloqueado na primeira tentativa após expiração</li>";
echo "<li><strong>Após a correção:</strong> Usuário tem novamente 5 tentativas completas</li>";
echo "</ol>";

echo "<h2>🔄 Para testar o sistema completo:</h2>";
echo "<p>1. Se a conta estiver bloqueada, aguarde 15 minutos</p>";
echo "<p>2. Tente fazer login em <a href='auth/login.php'>login.php</a></p>";
echo "<p>3. O contador será resetado automaticamente</p>";
echo "<p>4. Você terá novamente 5 tentativas antes de ser bloqueado</p>";
?>