<?php
/**
 * Script para inserir dados completos de cenas baseado no arquivo cena.sql
 * Corrigido para usar as tabelas corretas: blocos_cenas e cenas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/CenaManager.php';

try {
    echo "<h1>Inserindo Dados Completos de Cenas</h1>";
    echo "<p><em>Script compatível com PostgreSQL/Supabase</em></p>";
    
    $cenaManager = new CenaManager();
    
    // Estrutura dos dados baseada no arquivo cena.sql
    $dadosCompletos = [
        // Bloco: Natureza
        [
            'bloco' => ['titulo' => 'Natureza', 'icone' => 'park', 'tipo_aba' => 'ambiente', 'ordem' => 1],
            'cenas' => [
                ['titulo' => 'Floresta', 'subtitulo' => 'Ambiente natural', 'prompt' => 'floresta densa com árvores altas, luz filtrada entre as copas, vegetação exuberante', 'valor' => 'floresta', 'ordem' => 1],
                ['titulo' => 'Praia', 'subtitulo' => 'Costa marítima', 'prompt' => 'praia tropical com areia branca e mar calmo, ondas suaves, céu azul', 'valor' => 'praia', 'ordem' => 2],
                ['titulo' => 'Montanha', 'subtitulo' => 'Paisagem montanhosa', 'prompt' => 'montanhas majestosas com picos nevados, ar puro, paisagem imponente', 'valor' => 'montanha', 'ordem' => 3],
                ['titulo' => 'Deserto', 'subtitulo' => 'Ambiente árido', 'prompt' => 'deserto vasto com dunas douradas, calor seco, horizonte infinito', 'valor' => 'deserto', 'ordem' => 4],
                ['titulo' => 'Campo', 'subtitulo' => 'Paisagem rural', 'prompt' => 'campo verde com flores silvestres, brisa suave, tranquilidade rural', 'valor' => 'campo', 'ordem' => 5],
                ['titulo' => 'Lago', 'subtitulo' => 'Corpo d\'água', 'prompt' => 'lago cristalino cercado por natureza, reflexos na água, serenidade', 'valor' => 'lago', 'ordem' => 6],
                ['titulo' => 'Cachoeira', 'subtitulo' => 'Queda d\'água', 'prompt' => 'cachoeira alta com névoa suave, som da água caindo, frescor natural', 'valor' => 'cachoeira', 'ordem' => 7],
                ['titulo' => 'Caverna', 'subtitulo' => 'Ambiente subterrâneo', 'prompt' => 'caverna com formações de estalactites, penumbra misteriosa, ecos naturais', 'valor' => 'caverna', 'ordem' => 8],
                ['titulo' => 'Selva', 'subtitulo' => 'Densa e úmida', 'prompt' => 'selva tropical com dossel fechado, umidade intensa, vida selvagem', 'valor' => 'selva', 'ordem' => 9],
                ['titulo' => 'Cânion', 'subtitulo' => 'Formação rochosa', 'prompt' => 'cânion profundo com paredes avermelhadas, erosão milenar, grandiosidade', 'valor' => 'canion', 'ordem' => 10],
                ['titulo' => 'Tundra', 'subtitulo' => 'Clima frio', 'prompt' => 'tundra gelada com vegetação rasteira, vento frio, paisagem ártica', 'valor' => 'tundra', 'ordem' => 11],
                ['titulo' => 'Manguezal', 'subtitulo' => 'Costeiro', 'prompt' => 'manguezal com raízes aéreas e água salobra, ecossistema único', 'valor' => 'manguezal', 'ordem' => 12],
                ['titulo' => 'Pântano', 'subtitulo' => 'Alagadiço', 'prompt' => 'pântano enevoado com águas escuras, mistério e umidade', 'valor' => 'pantano', 'ordem' => 13],
                ['titulo' => 'Savana', 'subtitulo' => 'Tropical sazonal', 'prompt' => 'savana com gramíneas altas e acácias, paisagem africana clássica', 'valor' => 'savana', 'ordem' => 14]
            ]
        ],
        
        // Bloco: Urbano
        [
            'bloco' => ['titulo' => 'Urbano', 'icone' => 'location_city', 'tipo_aba' => 'ambiente', 'ordem' => 2],
            'cenas' => [
                ['titulo' => 'Cidade', 'subtitulo' => 'Centro urbano', 'prompt' => 'paisagem urbana com arranha-céus, vida metropolitana, luzes da cidade', 'valor' => 'cidade', 'ordem' => 1],
                ['titulo' => 'Rua', 'subtitulo' => 'Via urbana', 'prompt' => 'rua movimentada com carros e pedestres, semáforos, vida urbana', 'valor' => 'rua', 'ordem' => 2],
                ['titulo' => 'Praça', 'subtitulo' => 'Espaço público', 'prompt' => 'praça arborizada com bancos, fonte central, convívio social', 'valor' => 'praca', 'ordem' => 3],
                ['titulo' => 'Metrô', 'subtitulo' => 'Transporte público', 'prompt' => 'estação de metrô com plataformas, movimento de pessoas, transporte urbano', 'valor' => 'metro', 'ordem' => 4],
                ['titulo' => 'Rooftop', 'subtitulo' => 'Cobertura', 'prompt' => 'rooftop com vista panorâmica da cidade, terraço urbano, perspectiva elevada', 'valor' => 'rooftop', 'ordem' => 5],
                ['titulo' => 'Beco Grafitado', 'subtitulo' => 'Arte urbana', 'prompt' => 'beco estreito com grafites coloridos, arte de rua, expressão urbana', 'valor' => 'beco_grafitado', 'ordem' => 6],
                ['titulo' => 'Ponte', 'subtitulo' => 'Infraestrutura', 'prompt' => 'ponte icônica sobre um rio, arquitetura impressionante, conexão urbana', 'valor' => 'ponte', 'ordem' => 7],
                ['titulo' => 'Mercado de Rua', 'subtitulo' => 'Feira', 'prompt' => 'mercado ao ar livre com barracas coloridas, comércio popular, vida local', 'valor' => 'mercado_rua', 'ordem' => 8],
                ['titulo' => 'Porto', 'subtitulo' => 'Zona portuária', 'prompt' => 'porto com guindastes e contêineres, atividade marítima, comércio global', 'valor' => 'porto', 'ordem' => 9],
                ['titulo' => 'Estação de Trem', 'subtitulo' => 'Transporte', 'prompt' => 'estação clássica com trilhos, locomotivas, viagem ferroviária', 'valor' => 'estacao_trem', 'ordem' => 10],
                ['titulo' => 'Zona Industrial', 'subtitulo' => 'Fábricas', 'prompt' => 'parque industrial com chaminés, produção em massa, paisagem fabril', 'valor' => 'zona_industrial', 'ordem' => 11],
                ['titulo' => 'Bairro Histórico', 'subtitulo' => 'Patrimônio', 'prompt' => 'ruas de pedra e fachadas antigas, arquitetura colonial, história preservada', 'valor' => 'bairro_historico', 'ordem' => 12]
            ]
        ],
        
        // Bloco: Interior
        [
            'bloco' => ['titulo' => 'Interior', 'icone' => 'home', 'tipo_aba' => 'ambiente', 'ordem' => 3],
            'cenas' => [
                ['titulo' => 'Escritório', 'subtitulo' => 'Ambiente corporativo', 'prompt' => 'escritório moderno com mesas e monitores, ambiente profissional, produtividade', 'valor' => 'escritorio', 'ordem' => 1],
                ['titulo' => 'Casa', 'subtitulo' => 'Residência', 'prompt' => 'sala de estar aconchegante, móveis confortáveis, ambiente familiar', 'valor' => 'casa', 'ordem' => 2],
                ['titulo' => 'Cozinha', 'subtitulo' => 'Culinária', 'prompt' => 'cozinha gourmet bem iluminada, utensílios modernos, arte culinária', 'valor' => 'cozinha', 'ordem' => 3],
                ['titulo' => 'Quarto', 'subtitulo' => 'Descanso', 'prompt' => 'quarto minimalista com cama arrumada, decoração serena, relaxamento', 'valor' => 'quarto', 'ordem' => 4],
                ['titulo' => 'Sala de Aula', 'subtitulo' => 'Educação', 'prompt' => 'sala de aula com quadro e carteiras, ambiente educacional, aprendizado', 'valor' => 'sala_de_aula', 'ordem' => 5],
                ['titulo' => 'Laboratório', 'subtitulo' => 'Pesquisa', 'prompt' => 'laboratório com bancadas e vidrarias, pesquisa científica, descobertas', 'valor' => 'laboratorio', 'ordem' => 6],
                ['titulo' => 'Biblioteca', 'subtitulo' => 'Estudo', 'prompt' => 'biblioteca com prateleiras altas, conhecimento organizado, silêncio studioso', 'valor' => 'biblioteca', 'ordem' => 7],
                ['titulo' => 'Hospital', 'subtitulo' => 'Saúde', 'prompt' => 'corredor de hospital limpo, ambiente médico, cuidados de saúde', 'valor' => 'hospital', 'ordem' => 8],
                ['titulo' => 'Estúdio Fotográfico', 'subtitulo' => 'Produção', 'prompt' => 'estúdio com softboxes e fundo infinito, produção fotográfica, criatividade', 'valor' => 'estudio_fotografico', 'ordem' => 9],
                ['titulo' => 'Oficina', 'subtitulo' => 'Mecânica/DIY', 'prompt' => 'oficina com ferramentas e bancadas, trabalho manual, criação artesanal', 'valor' => 'oficina', 'ordem' => 10],
                ['titulo' => 'Restaurante', 'subtitulo' => 'Gastronomia', 'prompt' => 'restaurante elegante com mesas postas, experiência gastronômica, requinte', 'valor' => 'restaurante', 'ordem' => 11],
                ['titulo' => 'Cafeteria', 'subtitulo' => 'Café', 'prompt' => 'cafeteria artesanal com balcão de madeira, aroma de café, encontros sociais', 'valor' => 'cafeteria', 'ordem' => 12],
                ['titulo' => 'Museu', 'subtitulo' => 'Cultura', 'prompt' => 'museu com grandes salas expositivas, arte e história, contemplação cultural', 'valor' => 'museu', 'ordem' => 13],
                ['titulo' => 'Galeria de Arte', 'subtitulo' => 'Exposição', 'prompt' => 'galeria minimalista com quadros, expressão artística, apreciação estética', 'valor' => 'galeria_arte', 'ordem' => 14],
                ['titulo' => 'Academia', 'subtitulo' => 'Treino', 'prompt' => 'academia com equipamentos modernos, exercícios físicos, saúde e fitness', 'valor' => 'academia', 'ordem' => 15]
            ]
        ],
        
        // Bloco: Temáticos Históricos
        [
            'bloco' => ['titulo' => 'Temáticos Históricos', 'icone' => 'castle', 'tipo_aba' => 'ambiente', 'ordem' => 4],
            'cenas' => [
                ['titulo' => 'Medieval', 'subtitulo' => 'Período histórico', 'prompt' => 'aldeia medieval com castelo ao fundo, arquitetura gótica, atmosfera histórica', 'valor' => 'medieval', 'ordem' => 1],
                ['titulo' => 'Renascentista', 'subtitulo' => 'Arte e cultura', 'prompt' => 'cidade renascentista com cúpulas, arte clássica, renascimento cultural', 'valor' => 'renascentista', 'ordem' => 2],
                ['titulo' => 'Vitoriano', 'subtitulo' => 'Arquitetura clássica', 'prompt' => 'rua vitoriana com postes de luz, elegância do século XIX, refinamento', 'valor' => 'vitoriano', 'ordem' => 3],
                ['titulo' => 'Barroco', 'subtitulo' => 'Ornamentado', 'prompt' => 'igreja barroca ricamente decorada, ornamentação exuberante, arte sacra', 'valor' => 'barroco', 'ordem' => 4],
                ['titulo' => 'Colonial', 'subtitulo' => 'Américas', 'prompt' => 'centro colonial com casarios coloridos, arquitetura portuguesa, história americana', 'valor' => 'colonial', 'ordem' => 5],
                ['titulo' => 'Faroeste', 'subtitulo' => 'Velho Oeste', 'prompt' => 'cidade do velho oeste com saloon, época dos cowboys, fronteira americana', 'valor' => 'faroeste', 'ordem' => 6],
                ['titulo' => 'Antiguidade Romana', 'subtitulo' => 'Clássico', 'prompt' => 'coliseu romano monumental, grandeza imperial, civilização antiga', 'valor' => 'antiguidade_romana', 'ordem' => 7],
                ['titulo' => 'Egito Antigo', 'subtitulo' => 'Desértico', 'prompt' => 'pirâmides e esfinge ao pôr do sol, mistérios do antigo Egito, monumentos milenares', 'valor' => 'egito_antigo', 'ordem' => 8]
            ]
        ],
        
        // Bloco: Futurista & Sci-Fi
        [
            'bloco' => ['titulo' => 'Futurista & Sci-Fi', 'icone' => 'rocket_launch', 'tipo_aba' => 'ambiente', 'ordem' => 5],
            'cenas' => [
                ['titulo' => 'Cidade Futurista', 'subtitulo' => 'Alta tecnologia', 'prompt' => 'cidade futurista com neon e hologramas, tecnologia avançada, futuro urbano', 'valor' => 'cidade_futurista', 'ordem' => 1],
                ['titulo' => 'Cyberpunk', 'subtitulo' => 'Sci-fi urbano', 'prompt' => 'megalópole cyberpunk sob chuva neon, realidade digital, futuro distópico', 'valor' => 'cyberpunk', 'ordem' => 2],
                ['titulo' => 'Nave Espacial', 'subtitulo' => 'Interior', 'prompt' => 'interior de nave com janelas estelares, viagem espacial, tecnologia alienígena', 'valor' => 'nave_espacial', 'ordem' => 3],
                ['titulo' => 'Estação Orbital', 'subtitulo' => 'Exterior/Interior', 'prompt' => 'estação espacial com anéis e docas, vida no espaço, civilização orbital', 'valor' => 'estacao_orbital', 'ordem' => 4],
                ['titulo' => 'Hangar', 'subtitulo' => 'Aeronaves', 'prompt' => 'hangar hi-tech com naves atracadas, base espacial, frota intergaláctica', 'valor' => 'hangar_scifi', 'ordem' => 5],
                ['titulo' => 'Mercado Alienígena', 'subtitulo' => 'Extraterrestre', 'prompt' => 'bazar alienígena com espécies diversas, comércio intergaláctico, culturas exóticas', 'valor' => 'mercado_alienigena', 'ordem' => 6],
                ['titulo' => 'Laboratório Hi-tech', 'subtitulo' => 'Pesquisa', 'prompt' => 'laboratório futurista com telas holográficas, pesquisa avançada, inovação científica', 'valor' => 'laboratorio_hitech', 'ordem' => 7],
                ['titulo' => 'Rua Neon', 'subtitulo' => 'Noite', 'prompt' => 'rua estreita iluminada por letreiros neon, vida noturna futurista, atmosfera cyberpunk', 'valor' => 'rua_neon', 'ordem' => 8]
            ]
        ]
    ];
    
    $totalBlocos = 0;
    $totalCenas = 0;
    
    foreach ($dadosCompletos as $grupo) {
        echo "<h2>📁 Inserindo bloco: {$grupo['bloco']['titulo']}</h2>";
        
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
                    echo "<p>&nbsp;&nbsp;🎬 Cena '{$cena['titulo']}' inserida</p>";
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
    echo "<div style='background: #f0f9ff; padding: 20px; border-radius: 8px; border-left: 4px solid #3b82f6;'>";
    echo "<p><strong>✅ Blocos inseridos:</strong> {$totalBlocos}</p>";
    echo "<p><strong>🎬 Cenas inseridas:</strong> {$totalCenas}</p>";
    echo "<p><strong>📈 Total de itens:</strong> " . ($totalBlocos + $totalCenas) . "</p>";
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