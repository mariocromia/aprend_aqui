<?php
/**
 * Script para inserir dados completos de iluminação
 * Baseado no arquivo docs/seed_iluminacao.php
 * Adaptado para usar CenaManager e Supabase
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/CenaManager.php';

try {
    echo "<h1>Inserindo Dados Completos de Iluminação</h1>";
    echo "<p><em>Baseado no arquivo seed_iluminacao.php - Compatível com PostgreSQL/Supabase</em></p>";
    
    $cenaManager = new CenaManager();
    
    // Estrutura dos dados baseada no arquivo seed_iluminacao.php
    $dadosIluminacao = [
        // Bloco: Natural
        [
            'bloco' => ['titulo' => 'Natural', 'icone' => 'wb_sunny', 'tipo_aba' => 'iluminacao', 'ordem' => 1],
            'cenas' => [
                ['titulo' => 'Golden Hour', 'subtitulo' => 'Luz dourada suave', 'prompt' => 'luz natural dourada de golden hour, sombras longas e quentes, atmosfera cinematográfica', 'valor' => 'golden_hour', 'ordem' => 1],
                ['titulo' => 'Blue Hour', 'subtitulo' => 'Crepúsculo azulado', 'prompt' => 'luz natural azulada do blue hour, atmosfera serena e melancólica', 'valor' => 'blue_hour', 'ordem' => 2],
                ['titulo' => 'Amanhecer Suave', 'subtitulo' => 'Início do dia', 'prompt' => 'luz de amanhecer suave com névoa leve, tons rosados e dourados', 'valor' => 'amanhecer_suave', 'ordem' => 3],
                ['titulo' => 'Meio-dia', 'subtitulo' => 'Sol a pino intenso', 'prompt' => 'luz natural dura de meio-dia com sombras curtas e contrastes altos', 'valor' => 'meio_dia', 'ordem' => 4],
                ['titulo' => 'Tarde Dourada', 'subtitulo' => 'Quente e confortável', 'prompt' => 'luz de tarde dourada com tonalidade quente e acolhedora', 'valor' => 'tarde_dourada', 'ordem' => 5],
                ['titulo' => 'Luar Noturno', 'subtitulo' => 'Clarão lunar', 'prompt' => 'luz fria do luar com sombras sutis e atmosfera misteriosa', 'valor' => 'luar_noturno', 'ordem' => 6],
                ['titulo' => 'Céu Nublado Difuso', 'subtitulo' => 'Soft natural', 'prompt' => 'luz natural difusa de céu nublado, sem sombras marcadas', 'valor' => 'ceu_nublado_difuso', 'ordem' => 7],
                ['titulo' => 'Neblina Leitosa', 'subtitulo' => 'Difusão pesada', 'prompt' => 'luz filtrada por neblina espessa, contraste reduzido, suavidade extrema', 'valor' => 'neblina_leitosa', 'ordem' => 8],
                ['titulo' => 'Céu Tempestuoso', 'subtitulo' => 'Drama natural', 'prompt' => 'luz dramática sob nuvens carregadas com aberturas ocasionais de sol', 'valor' => 'ceu_tempestuoso', 'ordem' => 9],
                ['titulo' => 'Alpenglow', 'subtitulo' => 'Montanhas rosadas', 'prompt' => 'brilho rosado pós-pôr do sol nas montanhas (alpenglow), tons mágicos', 'valor' => 'alpenglow', 'ordem' => 10],
                ['titulo' => 'God Rays Naturais', 'subtitulo' => 'Feixes solares', 'prompt' => 'feixes de luz solar atravessando nuvens/árvores com poeira visível', 'valor' => 'god_rays_naturais', 'ordem' => 11],
                ['titulo' => 'Contraluz Solar', 'subtitulo' => 'Silhuetas naturais', 'prompt' => 'contraluz natural com o sol atrás do sujeito, rim light natural', 'valor' => 'contraluz_solar', 'ordem' => 12],
                ['titulo' => 'Reflexo d\'Água', 'subtitulo' => 'Rebatida natural', 'prompt' => 'luz refletida da água criando padrões móveis e brilhos dinâmicos', 'valor' => 'reflexo_agua', 'ordem' => 13],
                ['titulo' => 'Sombra de Folhagens', 'subtitulo' => 'Dappled light', 'prompt' => 'manchas de luz filtradas por folhagens, padrões orgânicos', 'valor' => 'sombra_folhagens', 'ordem' => 14],
                ['titulo' => 'Arco-íris', 'subtitulo' => 'Fenômeno difrativo', 'prompt' => 'presença de arco-íris iluminando a cena com cores espectrais', 'valor' => 'arco_iris', 'ordem' => 15],
                ['titulo' => 'Neve Brilhante', 'subtitulo' => 'Albedo alto', 'prompt' => 'luz rebatida pela neve, ambiente muito claro e uniforme', 'valor' => 'neve_brilhante', 'ordem' => 16]
            ]
        ],
        
        // Bloco: Artificial
        [
            'bloco' => ['titulo' => 'Artificial', 'icone' => 'lightbulb', 'tipo_aba' => 'iluminacao', 'ordem' => 2],
            'cenas' => [
                ['titulo' => 'Neon Cyberpunk', 'subtitulo' => 'Luzes coloridas vibrantes', 'prompt' => 'neons saturados magenta/ciano criando reflexos molhados, estética cyberpunk', 'valor' => 'neon_cyberpunk', 'ordem' => 1],
                ['titulo' => 'LED Frio', 'subtitulo' => 'Branco azulado', 'prompt' => 'luz de LED fria com temperatura alta (6000–7000K), ambiente moderno', 'valor' => 'led_frio', 'ordem' => 2],
                ['titulo' => 'LED Quente', 'subtitulo' => 'Tom aconchegante', 'prompt' => 'luz de LED quente (2700–3200K) com tom amarelado acolhedor', 'valor' => 'led_quente', 'ordem' => 3],
                ['titulo' => 'Tungstênio Quente', 'subtitulo' => 'Amarelo aconchegante', 'prompt' => 'tungstênio 3200K com tom âmbar vintage e acolhedor', 'valor' => 'tungstenio_quente', 'ordem' => 4],
                ['titulo' => 'Fluorescente Verde', 'subtitulo' => 'Hospitalar', 'prompt' => 'luz fluorescente com leve dominante verde, ambiente clínico', 'valor' => 'fluorescente_verde', 'ordem' => 5],
                ['titulo' => 'Halógeno Nítido', 'subtitulo' => 'Spot claro', 'prompt' => 'luz halógena com boa reprodução de cor e recorte definido', 'valor' => 'halogeno_nitido', 'ordem' => 6],
                ['titulo' => 'Incandescente Vintage', 'subtitulo' => 'Edison', 'prompt' => 'lâmpada incandescente edison âmbar visível, estética retrô', 'valor' => 'incandescente_vintage', 'ordem' => 7],
                ['titulo' => 'Blacklight UV', 'subtitulo' => 'Ultravioleta', 'prompt' => 'iluminação UV realçando materiais fluorescentes, efeito especial', 'valor' => 'blacklight_uv', 'ordem' => 8],
                ['titulo' => 'Vapor de Sódio', 'subtitulo' => 'Rua alaranjada', 'prompt' => 'lâmpadas de vapor de sódio criando tom laranja urbano noturno', 'valor' => 'vapor_sodio', 'ordem' => 9],
                ['titulo' => 'Vapor de Mercúrio', 'subtitulo' => 'Azulado antigo', 'prompt' => 'lâmpadas de mercúrio com tonalidade azul-esverdeada vintage', 'valor' => 'vapor_mercurio', 'ordem' => 10],
                ['titulo' => 'Letreiro Neon Vintage', 'subtitulo' => 'Retro glow', 'prompt' => 'letreiros neon antigos com brilho suave e nostalgia urbana', 'valor' => 'neon_vintage', 'ordem' => 11],
                ['titulo' => 'RGB Ambiente Gamer', 'subtitulo' => 'Color wash', 'prompt' => 'wash RGB em paredes/teclados estilo setup gamer colorido', 'valor' => 'rgb_gamer', 'ordem' => 12],
                ['titulo' => 'Luz de Projetor', 'subtitulo' => 'Cone visível', 'prompt' => 'feixe de projetor com partículas no ar, atmosfera teatral', 'valor' => 'luz_projetor', 'ordem' => 13],
                ['titulo' => 'Strobo Festa', 'subtitulo' => 'Flashes intermitentes', 'prompt' => 'strobe rápido criando congelamento de movimento em festa', 'valor' => 'strobo_festa', 'ordem' => 14],
                ['titulo' => 'Laser Show', 'subtitulo' => 'Feixes precisos', 'prompt' => 'feixes de laser varrendo a cena com fumaça, show de luzes', 'valor' => 'laser_show', 'ordem' => 15],
                ['titulo' => 'Painel Soft LED', 'subtitulo' => 'Difuso e uniforme', 'prompt' => 'painel LED com difusor grande e luz uniforme profissional', 'valor' => 'painel_soft_led', 'ordem' => 16],
                ['titulo' => 'Ring Light', 'subtitulo' => 'Retrato', 'prompt' => 'ring light criando catchlight circular nos olhos, iluminação beauty', 'valor' => 'ring_light', 'ordem' => 17],
                ['titulo' => 'Lanternas de Papel', 'subtitulo' => 'Suavidade quente', 'prompt' => 'lanternas de papel amarelas penduradas, ambiente festivo asiático', 'valor' => 'lanternas_papel', 'ordem' => 18]
            ]
        ],
        
        // Bloco: Cinematográfica
        [
            'bloco' => ['titulo' => 'Cinematográfica', 'icone' => 'movie_creation', 'tipo_aba' => 'iluminacao', 'ordem' => 3],
            'cenas' => [
                ['titulo' => 'Three Point', 'subtitulo' => 'Configuração clássica', 'prompt' => 'key + fill + backlight equilibrados, setup cinematográfico padrão', 'valor' => 'three_point', 'ordem' => 1],
                ['titulo' => 'High Key', 'subtitulo' => 'Iluminação clara', 'prompt' => 'altíssima luz, sombras suaves e contraste baixo, atmosfera otimista', 'valor' => 'high_key', 'ordem' => 2],
                ['titulo' => 'Low Key', 'subtitulo' => 'Sombras dramáticas', 'prompt' => 'grande contraste, predominância de sombras, clima noir', 'valor' => 'low_key', 'ordem' => 3],
                ['titulo' => 'Contra-luz', 'subtitulo' => 'Silhueta rimada', 'prompt' => 'backlight forte gerando silhueta e rim light dramático', 'valor' => 'contra_luz', 'ordem' => 4],
                ['titulo' => 'Rembrandt', 'subtitulo' => 'Triângulo de luz', 'prompt' => 'triângulo de luz na bochecha oposta, técnica clássica de retrato', 'valor' => 'rembrandt', 'ordem' => 5],
                ['titulo' => 'Butterfly/Paramount', 'subtitulo' => 'Sombra borboleta', 'prompt' => 'luz frontal e alta criando sombra sob o nariz em forma de borboleta', 'valor' => 'butterfly_paramount', 'ordem' => 6],
                ['titulo' => 'Split Light', 'subtitulo' => 'Metade iluminada', 'prompt' => 'metade do rosto em luz, metade em sombra, contraste dramático', 'valor' => 'split_light', 'ordem' => 7],
                ['titulo' => 'Loop Light', 'subtitulo' => 'Sombra pequena', 'prompt' => 'sombra do nariz forma pequeno loop na bochecha, retrato suave', 'valor' => 'loop_light', 'ordem' => 8],
                ['titulo' => 'Edge/Rim Light', 'subtitulo' => 'Contorno', 'prompt' => 'luz de recorte delineando as bordas do sujeito, separação do fundo', 'valor' => 'rim_light', 'ordem' => 9],
                ['titulo' => 'Top Light', 'subtitulo' => 'De cima', 'prompt' => 'luz superior teatral com sombras oculares dramáticas', 'valor' => 'top_light', 'ordem' => 10],
                ['titulo' => 'Underlight', 'subtitulo' => 'De baixo (terror)', 'prompt' => 'luz vinda de baixo criando efeito inquietante e sombras invertidas', 'valor' => 'underlight', 'ordem' => 11],
                ['titulo' => 'Motivated Lighting', 'subtitulo' => 'Fonte diegética', 'prompt' => 'luz justificada por elementos da cena (janela, abajur), realismo', 'valor' => 'motivated_lighting', 'ordem' => 12],
                ['titulo' => 'Practical Lights', 'subtitulo' => 'Luzes do cenário', 'prompt' => 'luzes visíveis no quadro contribuindo para a exposição', 'valor' => 'practical_lights', 'ordem' => 13],
                ['titulo' => 'Softbox Difuso', 'subtitulo' => 'Suavidade', 'prompt' => 'softbox grande com difusão ampla para retrato suave', 'valor' => 'softbox_difuso', 'ordem' => 14],
                ['titulo' => 'Hard Light Direcional', 'subtitulo' => 'Recorte marcado', 'prompt' => 'luz dura com sombras bem definidas e contraste alto', 'valor' => 'hard_light', 'ordem' => 15],
                ['titulo' => 'Gel Teal & Orange', 'subtitulo' => 'Contraste de cor', 'prompt' => 'key teal + fill/ambient orange cinematográfico moderno', 'valor' => 'gel_teal_orange', 'ordem' => 16],
                ['titulo' => 'Gel Magenta & Ciano', 'subtitulo' => 'Cruzado colorido', 'prompt' => 'preenchimentos magenta/ciano para atmosfera estilizada', 'valor' => 'gel_magenta_ciano', 'ordem' => 17],
                ['titulo' => 'Chiaroscuro', 'subtitulo' => 'Luz e sombra', 'prompt' => 'composição dramaticamente contrastada ao estilo noir clássico', 'valor' => 'chiaroscuro', 'ordem' => 18],
                ['titulo' => 'Silhueta com Haze', 'subtitulo' => 'Volumetria', 'prompt' => 'silhuetas com feixes visíveis em haze, atmosfera cinematográfica', 'valor' => 'silhueta_haze', 'ordem' => 19],
                ['titulo' => 'Bounce/Reflector', 'subtitulo' => 'Rebatida', 'prompt' => 'luz rebatida em superfícies/brancos para preenchimento suave', 'valor' => 'bounce_reflector', 'ordem' => 20]
            ]
        ],
        
        // Bloco: Ambiente
        [
            'bloco' => ['titulo' => 'Ambiente', 'icone' => 'emoji_objects', 'tipo_aba' => 'iluminacao', 'ordem' => 4],
            'cenas' => [
                ['titulo' => 'Fogueira', 'subtitulo' => 'Chamas dançantes', 'prompt' => 'iluminação quente de fogueira com flicker natural e sombras dançantes', 'valor' => 'fogueira', 'ordem' => 1],
                ['titulo' => 'Velas Românticas', 'subtitulo' => 'Luz íntima tremulante', 'prompt' => 'várias velas com flicker suave criando atmosfera romântica', 'valor' => 'velas_romanticas', 'ordem' => 2],
                ['titulo' => 'Lanterna Terror', 'subtitulo' => 'Sombras assombradas', 'prompt' => 'lanterna de baixo criando sombras longas e atmosfera de terror', 'valor' => 'lanterna_terror', 'ordem' => 3],
                ['titulo' => 'Aurora Mágica', 'subtitulo' => 'Luzes fantasiosas', 'prompt' => 'aurora boreal tingindo o ambiente com cores místicas', 'valor' => 'aurora_magica', 'ordem' => 4],
                ['titulo' => 'Lareira', 'subtitulo' => 'Conforto', 'prompt' => 'luz de lareira refletida no ambiente, aconchego doméstico', 'valor' => 'lareira', 'ordem' => 5],
                ['titulo' => 'Relâmpagos', 'subtitulo' => 'Pulsos dramáticos', 'prompt' => 'iluminação por flashes de relâmpago, drama natural intenso', 'valor' => 'relampagos', 'ordem' => 6],
                ['titulo' => 'Luz de Poste', 'subtitulo' => 'Noite urbana', 'prompt' => 'poste de rua alaranjado formando halo de luz noturna', 'valor' => 'luz_de_poste', 'ordem' => 7],
                ['titulo' => 'Farol Marinho', 'subtitulo' => 'Feixe rotativo', 'prompt' => 'feixe rotatório de farol cortando a névoa marítima', 'valor' => 'farol_marinho', 'ordem' => 8],
                ['titulo' => 'Holofote de Estádio', 'subtitulo' => 'Alta intensidade', 'prompt' => 'holofotes potentes com cones de luz e atmosfera esportiva', 'valor' => 'holofote_estadio', 'ordem' => 9],
                ['titulo' => 'Vitrine Noturna', 'subtitulo' => 'Reflexos', 'prompt' => 'luzes de vitrine coloridas refletindo no vidro molhado', 'valor' => 'vitrine_noturna', 'ordem' => 10],
                ['titulo' => 'Janela com Sol', 'subtitulo' => 'Feixes quentes', 'prompt' => 'raios de sol entrando pela janela criando feixes visíveis', 'valor' => 'janela_com_sol', 'ordem' => 11],
                ['titulo' => 'Claraboia', 'subtitulo' => 'Luz zenital difusa', 'prompt' => 'claraboia ampla iluminando o interior com luz natural difusa', 'valor' => 'claraboia', 'ordem' => 12],
                ['titulo' => 'Headlamp', 'subtitulo' => 'Feixe estreito', 'prompt' => 'lanterna de cabeça iluminando o caminho com feixe concentrado', 'valor' => 'headlamp', 'ordem' => 13],
                ['titulo' => 'Sirenes de Emergência', 'subtitulo' => 'Vermelho/Azul alternado', 'prompt' => 'lavagem de cor de viaturas emergenciais, atmosfera de urgência', 'valor' => 'sirenes_emergencia', 'ordem' => 14],
                ['titulo' => 'Luz de Palco', 'subtitulo' => 'Spot teatral', 'prompt' => 'spot em artista com fumaça leve, performance teatral', 'valor' => 'luz_de_palco', 'ordem' => 15],
                ['titulo' => 'Vitral Colorido', 'subtitulo' => 'Projeções cromáticas', 'prompt' => 'luz atravessando vitrais criando manchas de cor no ambiente', 'valor' => 'vitral_colorido', 'ordem' => 16]
            ]
        ],
        
        // Bloco: Direção da Luz
        [
            'bloco' => ['titulo' => 'Direção da Luz', 'icone' => 'north_east', 'tipo_aba' => 'iluminacao', 'ordem' => 5],
            'cenas' => [
                ['titulo' => 'Frontal', 'subtitulo' => 'Uniforme', 'prompt' => 'luz frontal direta, minimiza sombras no rosto, iluminação plana', 'valor' => 'frontal', 'ordem' => 1],
                ['titulo' => 'Frontal 45°', 'subtitulo' => 'Retrato clássico', 'prompt' => 'luz frontal lateral 45° criando volume e modelagem facial', 'valor' => 'frontal_45', 'ordem' => 2],
                ['titulo' => 'Lateral 90°', 'subtitulo' => 'Dramática', 'prompt' => 'luz lateral pura com forte contraste e drama', 'valor' => 'lateral_90', 'ordem' => 3],
                ['titulo' => 'Traseira (Backlight)', 'subtitulo' => 'Rim/silhueta', 'prompt' => 'luz por trás do sujeito criando recorte e separação', 'valor' => 'backlight', 'ordem' => 4],
                ['titulo' => '3/4 Frontal', 'subtitulo' => 'Modelagem', 'prompt' => 'luz 3/4 frontal com boa modelagem facial equilibrada', 'valor' => 'tres_quartos_frontal', 'ordem' => 5],
                ['titulo' => '3/4 Traseira', 'subtitulo' => 'Separação', 'prompt' => 'luz 3/4 traseira para separação do fundo e volume', 'valor' => 'tres_quartos_traseira', 'ordem' => 6],
                ['titulo' => 'Superior', 'subtitulo' => 'Top light', 'prompt' => 'luz acima do sujeito, sombras oculares dramáticas', 'valor' => 'superior', 'ordem' => 7],
                ['titulo' => 'Inferior', 'subtitulo' => 'Underlight', 'prompt' => 'luz vinda de baixo, efeito inquietante e não natural', 'valor' => 'inferior', 'ordem' => 8]
            ]
        ]
    ];
    
    $totalBlocos = 0;
    $totalCenas = 0;
    
    foreach ($dadosIluminacao as $grupo) {
        echo "<h2>💡 Inserindo bloco: {$grupo['bloco']['titulo']}</h2>";
        
        // Inserir bloco
        $bloco = $grupo['bloco'];
        $resultado = $cenaManager->inserirBloco(
            $bloco['titulo'],
            $bloco['icone'],
            $bloco['tipo_aba'],
            $bloco['ordem']
        );
        
        if ($resultado) {
            $blocoId = is_array($resultado) && isset($resultado['id']) ? $resultado['id'] : $resultado;
            $totalBlocos++;
            echo "<p>✅ Bloco inserido com ID: {$blocoId}</p>";
            
            // Inserir cenas do bloco
            foreach ($grupo['cenas'] as $cena) {
                $resultadoCena = $cenaManager->inserirCena(
                    $blocoId,
                    $cena['titulo'],
                    $cena['subtitulo'],
                    $cena['prompt'],
                    $cena['valor'],
                    $cena['ordem']
                );
                
                if ($resultadoCena) {
                    $totalCenas++;
                    echo "<p>&nbsp;&nbsp;🔆 Cena '{$cena['titulo']}' inserida</p>";
                } else {
                    echo "<p>&nbsp;&nbsp;❌ Erro ao inserir cena '{$cena['titulo']}'</p>";
                }
            }
        } else {
            echo "<p>❌ Erro ao inserir bloco '{$bloco['titulo']}'</p>";
        }
        
        echo "<hr>";
    }
    
    echo "<h2>📊 Resumo Final</h2>";
    echo "<div style='background: #fefce8; padding: 20px; border-radius: 8px; border-left: 4px solid #eab308;'>";
    echo "<p><strong>✅ Blocos de iluminação inseridos:</strong> {$totalBlocos}</p>";
    echo "<p><strong>💡 Cenas de iluminação inseridas:</strong> {$totalCenas}</p>";
    echo "<p><strong>📈 Total de itens:</strong> " . ($totalBlocos + $totalCenas) . "</p>";
    echo "</div>";
    
    echo "<h2>🎨 Categorias Inseridas</h2>";
    echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 8px;'>";
    echo "<ul>";
    echo "<li><strong>Natural:</strong> Golden hour, blue hour, contraluz solar, etc.</li>";
    echo "<li><strong>Artificial:</strong> Neon cyberpunk, LED, tungstênio, lasers, etc.</li>";
    echo "<li><strong>Cinematográfica:</strong> Three point, Rembrandt, low key, chiaroscuro, etc.</li>";
    echo "<li><strong>Ambiente:</strong> Fogueira, velas, aurora, relâmpagos, etc.</li>";
    echo "<li><strong>Direção da Luz:</strong> Frontal, lateral, backlight, superior, etc.</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>🔗 Links Úteis</h2>";
    echo "<p>";
    echo "<a href='admin-cards.php' target='_blank' style='margin-right: 15px;'>🔧 Gerenciar no Admin</a>";
    echo "<a href='gerador_prompt_modern.php' target='_blank' style='margin-right: 15px;'>🎯 Ver no Gerador</a>";
    echo "<a href='test_gerador_ambiente.php' target='_blank'>🧪 Testar Integração</a>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; padding: 20px; border-radius: 8px; border-left: 4px solid #ef4444;'>";
    echo "<p style='color: #dc2626;'><strong>❌ Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<details style='margin-top: 10px;'>";
    echo "<summary style='cursor: pointer; color: #7c2d12;'>Ver detalhes técnicos</summary>";
    echo "<pre style='background: #fff; padding: 10px; border-radius: 4px; margin-top: 10px;'>" . $e->getTraceAsString() . "</pre>";
    echo "</details>";
    echo "</div>";
}
?>