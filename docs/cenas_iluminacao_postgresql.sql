-- Script SQL para PostgreSQL (Supabase) - Iluminação
-- Baseado no arquivo seed_iluminacao.php
-- Inserir dados completos de iluminação usando DO blocks

DO $$
DECLARE
    blk_natural INTEGER;
    blk_artificial INTEGER;
    blk_cinematografica INTEGER;
    blk_ambiente INTEGER;
    blk_direcao_luz INTEGER;
    blk_qualidade_luz INTEGER;
    blk_cor_gel INTEGER;
    blk_efeitos_particulas INTEGER;
    blk_modificadores INTEGER;
    blk_rebatidas_reflexoes INTEGER;
    blk_sombras_padroes INTEGER;
BEGIN

-- Bloco: Natural
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
VALUES ('Natural', 'wb_sunny', 'iluminacao', 1, true) 
RETURNING id INTO blk_natural;

INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES 
(blk_natural, 'Golden Hour', 'Luz dourada suave', 'luz natural dourada de golden hour, sombras longas e quentes, atmosfera cinematográfica', 'golden_hour', 1, true),
(blk_natural, 'Blue Hour', 'Crepúsculo azulado', 'luz natural azulada do blue hour, atmosfera serena e melancólica', 'blue_hour', 2, true),
(blk_natural, 'Amanhecer Suave', 'Início do dia', 'luz de amanhecer suave com névoa leve, tons rosados e dourados', 'amanhecer_suave', 3, true),
(blk_natural, 'Meio-dia', 'Sol a pino intenso', 'luz natural dura de meio-dia com sombras curtas e contrastes altos', 'meio_dia', 4, true),
(blk_natural, 'Tarde Dourada', 'Quente e confortável', 'luz de tarde dourada com tonalidade quente e acolhedora', 'tarde_dourada', 5, true),
(blk_natural, 'Luar Noturno', 'Clarão lunar', 'luz fria do luar com sombras sutis e atmosfera misteriosa', 'luar_noturno', 6, true),
(blk_natural, 'Céu Nublado Difuso', 'Soft natural', 'luz natural difusa de céu nublado, sem sombras marcadas', 'ceu_nublado_difuso', 7, true),
(blk_natural, 'Neblina Leitosa', 'Difusão pesada', 'luz filtrada por neblina espessa, contraste reduzido, suavidade extrema', 'neblina_leitosa', 8, true),
(blk_natural, 'Céu Tempestuoso', 'Drama natural', 'luz dramática sob nuvens carregadas com aberturas ocasionais de sol', 'ceu_tempestuoso', 9, true),
(blk_natural, 'Alpenglow', 'Montanhas rosadas', 'brilho rosado pós-pôr do sol nas montanhas (alpenglow), tons mágicos', 'alpenglow', 10, true),
(blk_natural, 'God Rays Naturais', 'Feixes solares', 'feixes de luz solar atravessando nuvens/árvores com poeira visível', 'god_rays_naturais', 11, true),
(blk_natural, 'Contraluz Solar', 'Silhuetas naturais', 'contraluz natural com o sol atrás do sujeito, rim light natural', 'contraluz_solar', 12, true),
(blk_natural, 'Reflexo d''Água', 'Rebatida natural', 'luz refletida da água criando padrões móveis e brilhos dinâmicos', 'reflexo_agua', 13, true),
(blk_natural, 'Sombra de Folhagens', 'Dappled light', 'manchas de luz filtradas por folhagens, padrões orgânicos', 'sombra_folhagens', 14, true),
(blk_natural, 'Arco-íris', 'Fenômeno difrativo', 'presença de arco-íris iluminando a cena com cores espectrais', 'arco_iris', 15, true),
(blk_natural, 'Neve Brilhante', 'Albedo alto', 'luz rebatida pela neve, ambiente muito claro e uniforme', 'neve_brilhante', 16, true);

-- Bloco: Artificial
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
VALUES ('Artificial', 'lightbulb', 'iluminacao', 2, true) 
RETURNING id INTO blk_artificial;

INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES 
(blk_artificial, 'Neon Cyberpunk', 'Luzes coloridas vibrantes', 'neons saturados magenta/ciano criando reflexos molhados, estética cyberpunk', 'neon_cyberpunk', 1, true),
(blk_artificial, 'LED Frio', 'Branco azulado', 'luz de LED fria com temperatura alta (6000–7000K), ambiente moderno', 'led_frio', 2, true),
(blk_artificial, 'LED Quente', 'Tom aconchegante', 'luz de LED quente (2700–3200K) com tom amarelado acolhedor', 'led_quente', 3, true),
(blk_artificial, 'Tungstênio Quente', 'Amarelo aconchegante', 'tungstênio 3200K com tom âmbar vintage e acolhedor', 'tungstenio_quente', 4, true),
(blk_artificial, 'Fluorescente Verde', 'Hospitalar', 'luz fluorescente com leve dominante verde, ambiente clínico', 'fluorescente_verde', 5, true),
(blk_artificial, 'Halógeno Nítido', 'Spot claro', 'luz halógena com boa reprodução de cor e recorte definido', 'halogeno_nitido', 6, true),
(blk_artificial, 'Incandescente Vintage', 'Edison', 'lâmpada incandescente edison âmbar visível, estética retrô', 'incandescente_vintage', 7, true),
(blk_artificial, 'Blacklight UV', 'Ultravioleta', 'iluminação UV realçando materiais fluorescentes, efeito especial', 'blacklight_uv', 8, true),
(blk_artificial, 'Vapor de Sódio', 'Rua alaranjada', 'lâmpadas de vapor de sódio criando tom laranja urbano noturno', 'vapor_sodio', 9, true),
(blk_artificial, 'Vapor de Mercúrio', 'Azulado antigo', 'lâmpadas de mercúrio com tonalidade azul-esverdeada vintage', 'vapor_mercurio', 10, true),
(blk_artificial, 'Letreiro Neon Vintage', 'Retro glow', 'letreiros neon antigos com brilho suave e nostalgia urbana', 'neon_vintage', 11, true),
(blk_artificial, 'RGB Ambiente Gamer', 'Color wash', 'wash RGB em paredes/teclados estilo setup gamer colorido', 'rgb_gamer', 12, true),
(blk_artificial, 'Luz de Projetor', 'Cone visível', 'feixe de projetor com partículas no ar, atmosfera teatral', 'luz_projetor', 13, true),
(blk_artificial, 'Strobo Festa', 'Flashes intermitentes', 'strobe rápido criando congelamento de movimento em festa', 'strobo_festa', 14, true),
(blk_artificial, 'Laser Show', 'Feixes precisos', 'feixes de laser varrendo a cena com fumaça, show de luzes', 'laser_show', 15, true),
(blk_artificial, 'Painel Soft LED', 'Difuso e uniforme', 'painel LED com difusor grande e luz uniforme profissional', 'painel_soft_led', 16, true),
(blk_artificial, 'Ring Light', 'Retrato', 'ring light criando catchlight circular nos olhos, iluminação beauty', 'ring_light', 17, true),
(blk_artificial, 'Lanternas de Papel', 'Suavidade quente', 'lanternas de papel amarelas penduradas, ambiente festivo asiático', 'lanternas_papel', 18, true);

-- Bloco: Cinematográfica
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
VALUES ('Cinematográfica', 'movie_creation', 'iluminacao', 3, true) 
RETURNING id INTO blk_cinematografica;

INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES 
(blk_cinematografica, 'Three Point', 'Configuração clássica', 'key + fill + backlight equilibrados, setup cinematográfico padrão', 'three_point', 1, true),
(blk_cinematografica, 'High Key', 'Iluminação clara', 'altíssima luz, sombras suaves e contraste baixo, atmosfera otimista', 'high_key', 2, true),
(blk_cinematografica, 'Low Key', 'Sombras dramáticas', 'grande contraste, predominância de sombras, clima noir', 'low_key', 3, true),
(blk_cinematografica, 'Contra-luz', 'Silhueta rimada', 'backlight forte gerando silhueta e rim light dramático', 'contra_luz', 4, true),
(blk_cinematografica, 'Rembrandt', 'Triângulo de luz', 'triângulo de luz na bochecha oposta, técnica clássica de retrato', 'rembrandt', 5, true),
(blk_cinematografica, 'Butterfly/Paramount', 'Sombra borboleta', 'luz frontal e alta criando sombra sob o nariz em forma de borboleta', 'butterfly_paramount', 6, true),
(blk_cinematografica, 'Split Light', 'Metade iluminada', 'metade do rosto em luz, metade em sombra, contraste dramático', 'split_light', 7, true),
(blk_cinematografica, 'Loop Light', 'Sombra pequena', 'sombra do nariz forma pequeno loop na bochecha, retrato suave', 'loop_light', 8, true),
(blk_cinematografica, 'Edge/Rim Light', 'Contorno', 'luz de recorte delineando as bordas do sujeito, separação do fundo', 'rim_light', 9, true),
(blk_cinematografica, 'Top Light', 'De cima', 'luz superior teatral com sombras oculares dramáticas', 'top_light', 10, true),
(blk_cinematografica, 'Underlight', 'De baixo (terror)', 'luz vinda de baixo criando efeito inquietante e sombras invertidas', 'underlight', 11, true),
(blk_cinematografica, 'Motivated Lighting', 'Fonte diegética', 'luz justificada por elementos da cena (janela, abajur), realismo', 'motivated_lighting', 12, true),
(blk_cinematografica, 'Practical Lights', 'Luzes do cenário', 'luzes visíveis no quadro contribuindo para a exposição', 'practical_lights', 13, true),
(blk_cinematografica, 'Softbox Difuso', 'Suavidade', 'softbox grande com difusão ampla para retrato suave', 'softbox_difuso', 14, true),
(blk_cinematografica, 'Hard Light Direcional', 'Recorte marcado', 'luz dura com sombras bem definidas e contraste alto', 'hard_light', 15, true),
(blk_cinematografica, 'Gel Teal & Orange', 'Contraste de cor', 'key teal + fill/ambient orange cinematográfico moderno', 'gel_teal_orange', 16, true),
(blk_cinematografica, 'Gel Magenta & Ciano', 'Cruzado colorido', 'preenchimentos magenta/ciano para atmosfera estilizada', 'gel_magenta_ciano', 17, true),
(blk_cinematografica, 'Chiaroscuro', 'Luz e sombra', 'composição dramaticamente contrastada ao estilo noir clássico', 'chiaroscuro', 18, true),
(blk_cinematografica, 'Silhueta com Haze', 'Volumetria', 'silhuetas com feixes visíveis em haze, atmosfera cinematográfica', 'silhueta_haze', 19, true),
(blk_cinematografica, 'Bounce/Reflector', 'Rebatida', 'luz rebatida em superfícies/brancos para preenchimento suave', 'bounce_reflector', 20, true);

-- Bloco: Ambiente
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
VALUES ('Ambiente', 'emoji_objects', 'iluminacao', 4, true) 
RETURNING id INTO blk_ambiente;

INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES 
(blk_ambiente, 'Fogueira', 'Chamas dançantes', 'iluminação quente de fogueira com flicker natural e sombras dançantes', 'fogueira', 1, true),
(blk_ambiente, 'Velas Românticas', 'Luz íntima tremulante', 'várias velas com flicker suave criando atmosfera romântica', 'velas_romanticas', 2, true),
(blk_ambiente, 'Lanterna Terror', 'Sombras assombradas', 'lanterna de baixo criando sombras longas e atmosfera de terror', 'lanterna_terror', 3, true),
(blk_ambiente, 'Aurora Mágica', 'Luzes fantasiosas', 'aurora boreal tingindo o ambiente com cores místicas', 'aurora_magica', 4, true),
(blk_ambiente, 'Lareira', 'Conforto', 'luz de lareira refletida no ambiente, aconchego doméstico', 'lareira', 5, true),
(blk_ambiente, 'Relâmpagos', 'Pulsos dramáticos', 'iluminação por flashes de relâmpago, drama natural intenso', 'relampagos', 6, true),
(blk_ambiente, 'Luz de Poste', 'Noite urbana', 'poste de rua alaranjado formando halo de luz noturna', 'luz_de_poste', 7, true),
(blk_ambiente, 'Farol Marinho', 'Feixe rotativo', 'feixe rotatório de farol cortando a névoa marítima', 'farol_marinho', 8, true),
(blk_ambiente, 'Holofote de Estádio', 'Alta intensidade', 'holofotes potentes com cones de luz e atmosfera esportiva', 'holofote_estadio', 9, true),
(blk_ambiente, 'Vitrine Noturna', 'Reflexos', 'luzes de vitrine coloridas refletindo no vidro molhado', 'vitrine_noturna', 10, true),
(blk_ambiente, 'Janela com Sol', 'Feixes quentes', 'raios de sol entrando pela janela criando feixes visíveis', 'janela_com_sol', 11, true),
(blk_ambiente, 'Claraboia', 'Luz zenital difusa', 'claraboia ampla iluminando o interior com luz natural difusa', 'claraboia', 12, true),
(blk_ambiente, 'Headlamp', 'Feixe estreito', 'lanterna de cabeça iluminando o caminho com feixe concentrado', 'headlamp', 13, true),
(blk_ambiente, 'Sirenes de Emergência', 'Vermelho/Azul alternado', 'lavagem de cor de viaturas emergenciais, atmosfera de urgência', 'sirenes_emergencia', 14, true),
(blk_ambiente, 'Luz de Palco', 'Spot teatral', 'spot em artista com fumaça leve, performance teatral', 'luz_de_palco', 15, true),
(blk_ambiente, 'Vitral Colorido', 'Projeções cromáticas', 'luz atravessando vitrais criando manchas de cor no ambiente', 'vitral_colorido', 16, true);

-- Bloco: Direção da Luz
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
VALUES ('Direção da Luz', 'north_east', 'iluminacao', 5, true) 
RETURNING id INTO blk_direcao_luz;

INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES 
(blk_direcao_luz, 'Frontal', 'Uniforme', 'luz frontal direta, minimiza sombras no rosto, iluminação plana', 'frontal', 1, true),
(blk_direcao_luz, 'Frontal 45°', 'Retrato clássico', 'luz frontal lateral 45° criando volume e modelagem facial', 'frontal_45', 2, true),
(blk_direcao_luz, 'Lateral 90°', 'Dramática', 'luz lateral pura com forte contraste e drama', 'lateral_90', 3, true),
(blk_direcao_luz, 'Traseira (Backlight)', 'Rim/silhueta', 'luz por trás do sujeito criando recorte e separação', 'backlight', 4, true),
(blk_direcao_luz, '3/4 Frontal', 'Modelagem', 'luz 3/4 frontal com boa modelagem facial equilibrada', 'tres_quartos_frontal', 5, true),
(blk_direcao_luz, '3/4 Traseira', 'Separação', 'luz 3/4 traseira para separação do fundo e volume', 'tres_quartos_traseira', 6, true),
(blk_direcao_luz, 'Superior', 'Top light', 'luz acima do sujeito, sombras oculares dramáticas', 'superior', 7, true),
(blk_direcao_luz, 'Inferior', 'Underlight', 'luz vinda de baixo, efeito inquietante e não natural', 'inferior', 8, true);

RAISE NOTICE 'Dados de iluminação inseridos com sucesso!';
RAISE NOTICE 'Total: 5 blocos principais, 98 cenas de iluminação';

END $$;