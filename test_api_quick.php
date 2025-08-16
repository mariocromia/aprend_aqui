<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Rápido - API Admin Cards</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-button { background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .test-button:hover { background: #2563eb; }
        .result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background: #d1fae5; border: 1px solid #10b981; color: #065f46; }
        .error { background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; }
        .info { background: #dbeafe; border: 1px solid #3b82f6; color: #1e40af; }
        pre { background: #f8fafc; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .loading { color: #6b7280; }
        .clear-btn { background: #6b7280; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Teste Rápido - API Admin Cards</h1>
        
        <div style="margin: 20px 0;">
            <button class="test-button" onclick="testarListarBlocos()">📋 Testar Listar Blocos</button>
            <button class="test-button" onclick="testarListarCenas()">🎭 Testar Listar Cenas</button>
            <button class="test-button" onclick="testarCriarBloco()">➕ Testar Criar Bloco</button>
            <button class="test-button" onclick="testarApiDireta()">🔗 Testar API Direta</button>
            <button class="clear-btn" onclick="limparResultados()">🧹 Limpar</button>
        </div>
        
        <div id="resultados"></div>
    </div>

    <script>
        function adicionarResultado(tipo, titulo, conteudo) {
            const resultados = document.getElementById('resultados');
            const div = document.createElement('div');
            div.className = `result ${tipo}`;
            div.innerHTML = `<h3>${titulo}</h3><div>${conteudo}</div>`;
            resultados.appendChild(div);
            resultados.scrollTop = resultados.scrollHeight;
        }

        function limparResultados() {
            document.getElementById('resultados').innerHTML = '';
        }

        async function testarListarBlocos() {
            adicionarResultado('info', '📋 Testando Listar Blocos', 'Carregando...');
            
            try {
                const response = await fetch('api/admin-cards.php?action=listar_blocos');
                const text = await response.text();
                
                let resultado = `<strong>Status HTTP:</strong> ${response.status}<br>`;
                resultado += `<strong>Headers:</strong> ${response.headers.get('content-type')}<br>`;
                resultado += `<strong>Resposta (primeiros 500 chars):</strong><br><pre>${text.substring(0, 500)}${text.length > 500 ? '...' : ''}</pre>`;
                
                try {
                    const data = JSON.parse(text);
                    resultado += `<strong>JSON Válido:</strong> ✅<br>`;
                    resultado += `<strong>Success:</strong> ${data.success}<br>`;
                    if (data.data) {
                        resultado += `<strong>Total de blocos:</strong> ${data.data.length}<br>`;
                    }
                    if (data.message) {
                        resultado += `<strong>Mensagem:</strong> ${data.message}<br>`;
                    }
                    
                    adicionarResultado('success', '✅ Listar Blocos - Sucesso', resultado);
                } catch (jsonError) {
                    resultado += `<strong>Erro JSON:</strong> ${jsonError.message}<br>`;
                    adicionarResultado('error', '❌ Listar Blocos - JSON Inválido', resultado);
                }
                
            } catch (error) {
                adicionarResultado('error', '❌ Listar Blocos - Erro de Rede', error.message);
            }
        }

        async function testarListarCenas() {
            adicionarResultado('info', '🎭 Testando Listar Cenas', 'Carregando...');
            
            try {
                const response = await fetch('api/admin-cards.php?action=listar_cenas');
                const text = await response.text();
                
                let resultado = `<strong>Status HTTP:</strong> ${response.status}<br>`;
                resultado += `<strong>Resposta (primeiros 500 chars):</strong><br><pre>${text.substring(0, 500)}${text.length > 500 ? '...' : ''}</pre>`;
                
                try {
                    const data = JSON.parse(text);
                    resultado += `<strong>JSON Válido:</strong> ✅<br>`;
                    resultado += `<strong>Success:</strong> ${data.success}<br>`;
                    if (data.data) {
                        resultado += `<strong>Total de cenas:</strong> ${data.data.length}<br>`;
                    }
                    
                    adicionarResultado('success', '✅ Listar Cenas - Sucesso', resultado);
                } catch (jsonError) {
                    resultado += `<strong>Erro JSON:</strong> ${jsonError.message}<br>`;
                    adicionarResultado('error', '❌ Listar Cenas - JSON Inválido', resultado);
                }
                
            } catch (error) {
                adicionarResultado('error', '❌ Listar Cenas - Erro de Rede', error.message);
            }
        }

        async function testarCriarBloco() {
            adicionarResultado('info', '➕ Testando Criar Bloco', 'Enviando requisição...');
            
            const dadosBloco = {
                action: 'criar_bloco',
                titulo: `Teste API ${new Date().getTime()}`,
                icone: 'bug_report',
                tipo_aba: 'ambiente',
                ordem_exibicao: 999
            };
            
            try {
                const response = await fetch('api/admin-cards.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dadosBloco)
                });
                
                const text = await response.text();
                
                let resultado = `<strong>Status HTTP:</strong> ${response.status}<br>`;
                resultado += `<strong>Dados enviados:</strong><br><pre>${JSON.stringify(dadosBloco, null, 2)}</pre>`;
                resultado += `<strong>Resposta:</strong><br><pre>${text}</pre>`;
                
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        adicionarResultado('success', '✅ Criar Bloco - Sucesso', resultado);
                    } else {
                        adicionarResultado('error', '❌ Criar Bloco - Falha', resultado);
                    }
                } catch (jsonError) {
                    adicionarResultado('error', '❌ Criar Bloco - JSON Inválido', resultado);
                }
                
            } catch (error) {
                adicionarResultado('error', '❌ Criar Bloco - Erro de Rede', error.message);
            }
        }

        async function testarApiDireta() {
            adicionarResultado('info', '🔗 Testando API Direta', 'Verificando endpoint...');
            
            const tests = [
                { url: 'api/admin-cards.php', desc: 'Sem parâmetros' },
                { url: 'api/admin-cards.php?action=', desc: 'Action vazia' },
                { url: 'api/admin-cards.php?action=invalid', desc: 'Action inválida' }
            ];
            
            for (const test of tests) {
                try {
                    const response = await fetch(test.url);
                    const text = await response.text();
                    
                    let resultado = `<strong>URL:</strong> ${test.url}<br>`;
                    resultado += `<strong>Descrição:</strong> ${test.desc}<br>`;
                    resultado += `<strong>Status:</strong> ${response.status}<br>`;
                    resultado += `<strong>Resposta:</strong><br><pre>${text.substring(0, 300)}${text.length > 300 ? '...' : ''}</pre>`;
                    
                    const tipo = response.status >= 200 && response.status < 300 ? 'info' : 'error';
                    adicionarResultado(tipo, `🔗 API Direta - ${test.desc}`, resultado);
                    
                } catch (error) {
                    adicionarResultado('error', `❌ API Direta - ${test.desc}`, error.message);
                }
            }
        }

        // Executar teste inicial automaticamente
        window.addEventListener('load', () => {
            adicionarResultado('info', '🚀 Sistema Iniciado', 'Clique nos botões acima para testar a API');
        });
    </script>
</body>
</html>