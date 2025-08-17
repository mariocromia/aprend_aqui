-- ========================================
-- SQL COMPLETO PARA ESTILO VISUAL
-- Execute este arquivo diretamente no Supabase
-- ========================================

-- 1. CORRIGIR CONSTRAINT PRIMEIRO
ALTER TABLE blocos_cenas DROP CONSTRAINT IF EXISTS blocos_cenas_tipo_aba_check;
ALTER TABLE blocos_cenas ADD CONSTRAINT blocos_cenas_tipo_aba_check 
CHECK (tipo_aba IN ('ambiente', 'estilo_visual', 'iluminacao', 'tecnica', 'elementos_especiais', 'qualidade', 'avatar', 'camera', 'voz', 'acao'));

-- 2. ATUALIZAR BLOCOS EXISTENTES (se existem)
UPDATE blocos_cenas SET tipo_aba = 'estilo_visual', ordem_exibicao = 1 WHERE id = 49 AND titulo = 'Estilos Artísticos Clássicos';
UPDATE blocos_cenas SET tipo_aba = 'estilo_visual', ordem_exibicao = 2 WHERE id = 50 AND titulo = 'Estilos Digitais e Modernos';
UPDATE blocos_cenas SET tipo_aba = 'estilo_visual', ordem_exibicao = 3 WHERE id = 51 AND titulo = 'Estilos Cinematográficos';
UPDATE blocos_cenas SET tipo_aba = 'estilo_visual', ordem_exibicao = 4 WHERE id = 52 AND titulo = 'Ilustração e Anime';
UPDATE blocos_cenas SET tipo_aba = 'estilo_visual', ordem_exibicao = 5 WHERE id = 53 AND titulo = 'Estilos Fotográficos';
UPDATE blocos_cenas SET tipo_aba = 'estilo_visual', ordem_exibicao = 6 WHERE id = 54 AND titulo = 'Fantasia e Magia';

-- 3. INSERIR BLOCOS (caso não existam)
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
SELECT 'Estilos Artísticos Clássicos', 'palette', 'estilo_visual', 1, true
WHERE NOT EXISTS (SELECT 1 FROM blocos_cenas WHERE titulo = 'Estilos Artísticos Clássicos' AND tipo_aba = 'estilo_visual');

INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
SELECT 'Estilos Digitais e Modernos', 'computer', 'estilo_visual', 2, true
WHERE NOT EXISTS (SELECT 1 FROM blocos_cenas WHERE titulo = 'Estilos Digitais e Modernos' AND tipo_aba = 'estilo_visual');

INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
SELECT 'Estilos Cinematográficos', 'movie', 'estilo_visual', 3, true
WHERE NOT EXISTS (SELECT 1 FROM blocos_cenas WHERE titulo = 'Estilos Cinematográficos' AND tipo_aba = 'estilo_visual');

INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
SELECT 'Ilustração e Anime', 'brush', 'estilo_visual', 4, true
WHERE NOT EXISTS (SELECT 1 FROM blocos_cenas WHERE titulo = 'Ilustração e Anime' AND tipo_aba = 'estilo_visual');

INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
SELECT 'Estilos Fotográficos', 'camera_alt', 'estilo_visual', 5, true
WHERE NOT EXISTS (SELECT 1 FROM blocos_cenas WHERE titulo = 'Estilos Fotográficos' AND tipo_aba = 'estilo_visual');

INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
SELECT 'Fantasia e Magia', 'auto_fix_high', 'estilo_visual', 6, true
WHERE NOT EXISTS (SELECT 1 FROM blocos_cenas WHERE titulo = 'Fantasia e Magia' AND tipo_aba = 'estilo_visual');

-- 4. LIMPAR CENAS EXISTENTES DOS BLOCOS DE ESTILO VISUAL
DELETE FROM cenas WHERE bloco_id IN (
    SELECT id FROM blocos_cenas WHERE tipo_aba = 'estilo_visual'
);

-- 5. INSERIR TODAS AS 50 CENAS

-- BLOCO 1: Estilos Artísticos Clássicos (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Artísticos Clássicos' AND tipo_aba = 'estilo_visual'), 'Realismo', 'Representação fiel da realidade', 'estilo realista, detalhes precisos, cores naturais, iluminação natural, textura realística', 'realismo', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Artísticos Clássicos' AND tipo_aba = 'estilo_visual'), 'Impressionismo', 'Pinceladas soltas e luz natural', 'estilo impressionista, pinceladas visíveis, cores vibrantes, luz natural, atmosfera etérea', 'impressionismo', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Artísticos Clássicos' AND tipo_aba = 'estilo_visual'), 'Art Nouveau', 'Linhas orgânicas e florais', 'estilo art nouveau, linhas sinuosas, motivos florais, ornamentação elegante, cores suaves', 'art_nouveau', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Artísticos Clássicos' AND tipo_aba = 'estilo_visual'), 'Surrealismo', 'Mundo dos sonhos e fantasia', 'estilo surrealista, elementos oníricos, composição impossível, cores vibrantes, atmosfera fantástica', 'surrealismo', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Artísticos Clássicos' AND tipo_aba = 'estilo_visual'), 'Cubismo', 'Formas geométricas fragmentadas', 'estilo cubista, formas geométricas, perspectivas múltiplas, fragmentação visual, cores contrastantes', 'cubismo', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Artísticos Clássicos' AND tipo_aba = 'estilo_visual'), 'Expressionismo', 'Emoções intensas e cores vibrantes', 'estilo expressionista, cores intensas, pinceladas dramáticas, emoção intensa, distorção expressiva', 'expressionismo', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Artísticos Clássicos' AND tipo_aba = 'estilo_visual'), 'Barroco', 'Dramaticidade e ornamentação rica', 'estilo barroco, dramaticidade intensa, ornamentação rica, contrastes de luz, composição dinâmica', 'barroco', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Artísticos Clássicos' AND tipo_aba = 'estilo_visual'), 'Minimalismo', 'Simplicidade e elementos essenciais', 'estilo minimalista, simplicidade extrema, cores neutras, formas limpas, espaço negativo', 'minimalismo', 8, true);

-- BLOCO 2: Estilos Digitais e Modernos (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Digitais e Modernos' AND tipo_aba = 'estilo_visual'), 'Cyberpunk', 'Futuro tecnológico neon', 'estilo cyberpunk, luzes neon, tecnologia futurística, atmosfera urbana noturna, cores vibrantes', 'cyberpunk', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Digitais e Modernos' AND tipo_aba = 'estilo_visual'), 'Vaporwave', 'Estética retrô-futurista dos anos 80', 'estilo vaporwave, cores pastel, elementos dos anos 80, grade retrô, aesthetic nostálgico', 'vaporwave', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Digitais e Modernos' AND tipo_aba = 'estilo_visual'), 'Glitch Art', 'Falhas digitais artísticas', 'estilo glitch art, distorções digitais, cores RGB deslocadas, pixelação, efeitos de erro', 'glitch_art', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Digitais e Modernos' AND tipo_aba = 'estilo_visual'), 'Low Poly', 'Formas geométricas simplificadas', 'estilo low poly, formas geométricas, polígonos visíveis, cores flat, design simplificado', 'low_poly', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Digitais e Modernos' AND tipo_aba = 'estilo_visual'), 'Pixel Art', 'Arte em pixels nostálgica', 'estilo pixel art, pixels visíveis, cores limitadas, aesthetic retrô de videogame, detalhes em grade', 'pixel_art', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Digitais e Modernos' AND tipo_aba = 'estilo_visual'), 'Synthwave', 'Ondas sintéticas dos anos 80', 'estilo synthwave, cores neon, gradientes, grid futurista, aesthetic dos anos 80, luzes vibrantes', 'synthwave', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Digitais e Modernos' AND tipo_aba = 'estilo_visual'), 'Holográfico', 'Efeitos iridescentes e metálicos', 'estilo holográfico, efeitos iridescentes, reflexos metálicos, cores cambiantes, superfícies brilhantes', 'holografico', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Digitais e Modernos' AND tipo_aba = 'estilo_visual'), 'Neon Noir', 'Filme noir com luzes neon', 'estilo neon noir, contrastes dramáticos, luzes neon coloridas, sombras profundas, atmosfera misteriosa', 'neon_noir', 8, true);

-- BLOCO 3: Estilos Cinematográficos (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Cinematográficos' AND tipo_aba = 'estilo_visual'), 'Film Noir', 'Drama em preto e branco', 'estilo film noir, alto contraste, sombras dramáticas, iluminação lateral, atmosfera sombria', 'film_noir', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Cinematográficos' AND tipo_aba = 'estilo_visual'), 'Wes Anderson', 'Simetria e paleta pastel', 'estilo Wes Anderson, composição simétrica, cores pastel, enquadramento centralizado, aesthetic vintage', 'wes_anderson', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Cinematográficos' AND tipo_aba = 'estilo_visual'), 'Tim Burton', 'Gótico e fantástico', 'estilo Tim Burton, aesthetic gótico, cores sombrias, elementos fantásticos, atmosfera sinistra', 'tim_burton', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Cinematográficos' AND tipo_aba = 'estilo_visual'), 'Blade Runner', 'Futuro distópico urbano', 'estilo Blade Runner, futuro distópico, luzes urbanas, chuva, atmosfera noir futurística', 'blade_runner', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Cinematográficos' AND tipo_aba = 'estilo_visual'), 'Studio Ghibli', 'Animação mágica e natural', 'estilo Studio Ghibli, cores suaves, natureza exuberante, atmosfera mágica, detalhes delicados', 'studio_ghibli', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Cinematográficos' AND tipo_aba = 'estilo_visual'), 'Matrix', 'Realidade digital verde', 'estilo Matrix, código verde, realidade digital, efeitos de matriz, atmosfera cyber', 'matrix', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Cinematográficos' AND tipo_aba = 'estilo_visual'), 'Mad Max', 'Pós-apocalíptico desértico', 'estilo Mad Max, pós-apocalíptico, tons terrosos, veículos modificados, paisagem árida', 'mad_max', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Cinematográficos' AND tipo_aba = 'estilo_visual'), 'Tron', 'Grid digital luminoso', 'estilo Tron, grid digital, luzes azuis, formas geométricas, ambiente virtual futurístico', 'tron', 8, true);

-- BLOCO 4: Ilustração e Anime (10 cenas) - INCLUINDO PIXAR E DISNEY
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Ilustração e Anime' AND tipo_aba = 'estilo_visual'), 'Anime Clássico', 'Estilo anime tradicional', 'estilo anime clássico, olhos grandes, cores vibrantes, linhas limpas, cel shading', 'anime_classico', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Ilustração e Anime' AND tipo_aba = 'estilo_visual'), 'Manga', 'Quadrinhos japoneses em preto e branco', 'estilo manga, preto e branco, hachuras, linhas expressivas, composição dinâmica', 'manga', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Ilustração e Anime' AND tipo_aba = 'estilo_visual'), 'Chibi', 'Personagens fofos e desproporcionais', 'estilo chibi, proporções fofas, cabeça grande, expressões adoráveis, cores pastel', 'chibi', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Ilustração e Anime' AND tipo_aba = 'estilo_visual'), 'Concept Art', 'Arte conceitual de jogos', 'estilo concept art, pintura digital, atmosfera épica, detalhes elaborados, composição cinematográfica', 'concept_art', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Ilustração e Anime' AND tipo_aba = 'estilo_visual'), 'Watercolor', 'Aquarela delicada', 'estilo aquarela, texturas fluidas, cores translúcidas, bordas suaves, efeito de tinta molhada', 'watercolor', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Ilustração e Anime' AND tipo_aba = 'estilo_visual'), 'Comic Book', 'Quadrinhos americanos', 'estilo comic book, cores saturadas, linhas grossas, efeitos de ação, composição dinâmica', 'comic_book', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Ilustração e Anime' AND tipo_aba = 'estilo_visual'), 'Pixar', 'Animação 3D Pixar', 'estilo Pixar, animação 3D, personagens expressivos, cores vibrantes, renderização suave, design adorável', 'pixar', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Ilustração e Anime' AND tipo_aba = 'estilo_visual'), 'Disney', 'Clássico Disney tradicional', 'estilo Disney clássico, animação tradicional, personagens carismáticos, cores mágicas, linhas suaves', 'disney', 8, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Ilustração e Anime' AND tipo_aba = 'estilo_visual'), 'Pin-up', 'Arte pin-up vintage', 'estilo pin-up, cores vintage, poses elegantes, aesthetic dos anos 50, ilustração glamourosa', 'pin_up', 9, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Ilustração e Anime' AND tipo_aba = 'estilo_visual'), 'Cartoon', 'Animação cartoon clássica', 'estilo cartoon, formas exageradas, cores vibrantes, expressões caricatas, linhas curvas', 'cartoon', 10, true);

-- BLOCO 5: Estilos Fotográficos (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Fotográficos' AND tipo_aba = 'estilo_visual'), 'Fotorealismo', 'Realismo fotográfico perfeito', 'fotorealismo, detalhes ultra precisos, textura realística, iluminação natural, qualidade 8K', 'fotorealismo', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Fotográficos' AND tipo_aba = 'estilo_visual'), 'Vintage', 'Fotografia antiga e nostálgica', 'estilo vintage, cores desbotadas, grão de filme, tons sépia, aesthetic retrô', 'vintage', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Fotográficos' AND tipo_aba = 'estilo_visual'), 'Polaroid', 'Fotos instantâneas nostálgicas', 'estilo polaroid, bordas brancas, cores saturadas, ligeiro desfoque, textura de filme', 'polaroid', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Fotográficos' AND tipo_aba = 'estilo_visual'), 'HDR', 'Alto alcance dinâmico', 'estilo HDR, cores ultra saturadas, detalhes extremos, contraste intenso, processamento dramático', 'hdr', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Fotográficos' AND tipo_aba = 'estilo_visual'), 'Macro', 'Detalhes extremos em close-up', 'estilo macro, detalhes microscópicos, profundidade de campo rasa, textura extrema, close-up intenso', 'macro', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Fotográficos' AND tipo_aba = 'estilo_visual'), 'Tilt-Shift', 'Efeito miniatura', 'estilo tilt-shift, efeito miniatura, foco seletivo, cores saturadas, perspectiva única', 'tilt_shift', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Fotográficos' AND tipo_aba = 'estilo_visual'), 'Long Exposure', 'Exposição longa artística', 'estilo long exposure, movimento borrado, rastros de luz, efeito sedoso, tempo suspenso', 'long_exposure', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Estilos Fotográficos' AND tipo_aba = 'estilo_visual'), 'Double Exposure', 'Dupla exposição criativa', 'estilo double exposure, sobreposição criativa, transparências, fusão de imagens, efeito artístico', 'double_exposure', 8, true);

-- BLOCO 6: Fantasia e Magia (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Fantasia e Magia' AND tipo_aba = 'estilo_visual'), 'Fantasy Art', 'Arte fantástica épica', 'estilo fantasy art, elementos mágicos, criaturas fantásticas, atmosfera épica, cores vibrantes', 'fantasy_art', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Fantasia e Magia' AND tipo_aba = 'estilo_visual'), 'Steampunk', 'Tecnologia a vapor vitoriana', 'estilo steampunk, engrenagens, vapor, bronze, era vitoriana, tecnologia retro-futurística', 'steampunk', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Fantasia e Magia' AND tipo_aba = 'estilo_visual'), 'Fairy Tale', 'Contos de fadas encantados', 'estilo fairy tale, atmosfera mágica, cores suaves, elementos encantados, fantasia delicada', 'fairy_tale', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Fantasia e Magia' AND tipo_aba = 'estilo_visual'), 'Dark Fantasy', 'Fantasia sombria e gótica', 'estilo dark fantasy, atmosfera sombria, elementos góticos, cores escuras, magia obscura', 'dark_fantasy', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Fantasia e Magia' AND tipo_aba = 'estilo_visual'), 'Mythology', 'Mitologia antiga épica', 'estilo mythology, elementos mitológicos, deuses antigos, atmosfera épica, simbolismo ancestral', 'mythology', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Fantasia e Magia' AND tipo_aba = 'estilo_visual'), 'Cosmic Horror', 'Horror cósmico lovecraftiano', 'estilo cosmic horror, tentáculos, dimensões alienígenas, cores não-terrestres, horror incompreensível', 'cosmic_horror', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Fantasia e Magia' AND tipo_aba = 'estilo_visual'), 'Ethereal', 'Etéreo e transcendental', 'estilo ethereal, atmosfera etérea, luz suave, transparências, elementos flutuantes, magia sutil', 'ethereal', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Fantasia e Magia' AND tipo_aba = 'estilo_visual'), 'Crystal Art', 'Arte cristalina e prismática', 'estilo crystal art, cristais brilhantes, reflexos prismáticos, cores iridescentes, geometria cristalina', 'crystal_art', 8, true);

-- 6. VERIFICAÇÃO FINAL
SELECT 
    bc.titulo as bloco,
    bc.tipo_aba,
    COUNT(c.id) as total_cenas,
    bc.ordem_exibicao as ordem
FROM blocos_cenas bc 
LEFT JOIN cenas c ON bc.id = c.bloco_id 
WHERE bc.tipo_aba = 'estilo_visual' 
GROUP BY bc.id, bc.titulo, bc.tipo_aba, bc.ordem_exibicao 
ORDER BY bc.ordem_exibicao;

-- ESTATÍSTICAS FINAIS
SELECT 
    'ESTILO VISUAL' as aba,
    COUNT(DISTINCT bc.id) as total_blocos,
    COUNT(c.id) as total_cenas
FROM blocos_cenas bc 
LEFT JOIN cenas c ON bc.id = c.bloco_id 
WHERE bc.tipo_aba = 'estilo_visual';

-- ========================================
-- RESUMO: 6 blocos com 50 cenas incluindo Pixar e Disney
-- ========================================