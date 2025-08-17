-- ========================================
-- POPULAÇÃO COMPLETA DA ABA QUALIDADE
-- 6 blocos com 56 aspectos de qualidade profissional
-- ========================================

-- 1. CORRIGIR CONSTRAINT PARA INCLUIR 'QUALIDADE'
ALTER TABLE blocos_cenas DROP CONSTRAINT IF EXISTS blocos_cenas_tipo_aba_check;
ALTER TABLE blocos_cenas ADD CONSTRAINT blocos_cenas_tipo_aba_check 
CHECK (tipo_aba IN ('ambiente', 'estilo_visual', 'iluminacao', 'tecnica', 'elementos_especiais', 'qualidade', 'avatar', 'camera', 'voz', 'acao'));

-- 2. LIMPAR DADOS DE QUALIDADE EXISTENTES
DELETE FROM cenas WHERE valor_selecao IN (
    'masterpiece', 'best_quality', 'ultra_high_quality', 'premium_grade', 'professional_level', 'studio_grade', 'gallery_worthy', 'award_winning',
    'ultra_detailed', 'hyper_detailed', 'extremely_detailed', 'intricate_details', 'fine_details', 'perfect_anatomy', 'flawless_composition', 'meticulous_craftsmanship',
    'magazine_cover', 'portfolio_quality', 'exhibition_grade', 'museum_quality', 'commercial_grade', 'editorial_standard', 'advertising_quality', 'luxury_brand',
    'perfect_lighting', 'professional_photography', 'studio_lighting', 'cinematic_quality', 'film_grade', 'broadcast_quality', 'imax_standard', 'dolby_vision',
    'trending_artstation', 'deviantart_featured', 'behance_showcase', 'dribbble_popular', 'instagram_viral', 'pinterest_trending', 'featured_artwork', 'viral_content',
    'contest_winner', 'competition_grade', 'juried_selection', 'curated_collection', 'editors_choice', 'critics_pick', 'peoples_choice', 'platinum_standard'
);

DELETE FROM cenas WHERE bloco_id IN (
    SELECT id FROM blocos_cenas WHERE tipo_aba = 'qualidade' OR titulo IN (
        'Qualidade Suprema', 'Detalhamento Profissional', 'Padrão Comercial', 
        'Excelência Técnica', 'Reconhecimento Digital', 'Premiações e Competições'
    )
);

DELETE FROM blocos_cenas WHERE tipo_aba = 'qualidade' OR titulo IN (
    'Qualidade Suprema', 'Detalhamento Profissional', 'Padrão Comercial', 
    'Excelência Técnica', 'Reconhecimento Digital', 'Premiações e Competições'
);

-- 3. INSERIR BLOCOS DE QUALIDADE (6 blocos especializados)

INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES
('Qualidade Suprema', 'star', 'qualidade', 1, true),
('Detalhamento Profissional', 'zoom_in', 'qualidade', 2, true),
('Padrão Comercial', 'business_center', 'qualidade', 3, true),
('Excelência Técnica', 'precision_manufacturing', 'qualidade', 4, true),
('Reconhecimento Digital', 'trending_up', 'qualidade', 5, true),
('Premiações e Competições', 'emoji_events', 'qualidade', 6, true);

-- 4. INSERIR TODAS AS 56 CENAS DE QUALIDADE

-- BLOCO 1: Qualidade Suprema (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade Suprema' AND tipo_aba = 'qualidade'), 'Masterpiece', 'Obra-prima absoluta', 'masterpiece, highest quality, perfect execution, artistic excellence, flawless creation', 'masterpiece', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade Suprema' AND tipo_aba = 'qualidade'), 'Best Quality', 'Melhor qualidade possível', 'best quality, top tier, premium standard, exceptional quality, superior grade', 'best_quality', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade Suprema' AND tipo_aba = 'qualidade'), 'Ultra High Quality', 'Qualidade ultra elevada', 'ultra high quality, maximum resolution, pristine condition, perfect clarity, supreme standard', 'ultra_high_quality', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade Suprema' AND tipo_aba = 'qualidade'), 'Premium Grade', 'Classificação premium', 'premium grade, luxury standard, high-end quality, exclusive level, elite classification', 'premium_grade', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade Suprema' AND tipo_aba = 'qualidade'), 'Professional Level', 'Nível profissional', 'professional level, industry standard, expert quality, commercial grade, professional execution', 'professional_level', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade Suprema' AND tipo_aba = 'qualidade'), 'Studio Grade', 'Qualidade de estúdio', 'studio grade, production quality, professional studio, controlled environment, perfect conditions', 'studio_grade', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade Suprema' AND tipo_aba = 'qualidade'), 'Gallery Worthy', 'Digno de galeria', 'gallery worthy, exhibition quality, museum standard, fine art level, collectible grade', 'gallery_worthy', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade Suprema' AND tipo_aba = 'qualidade'), 'Award Winning', 'Qualidade premiada', 'award winning, competition winner, recognized excellence, celebrated quality, honored creation', 'award_winning', 8, true);

-- BLOCO 2: Detalhamento Profissional (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Detalhamento Profissional' AND tipo_aba = 'qualidade'), 'Ultra Detailed', 'Detalhamento extremo', 'ultra detailed, extreme detail, microscopic precision, exhaustive detail, comprehensive rendering', 'ultra_detailed', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Detalhamento Profissional' AND tipo_aba = 'qualidade'), 'Hyper Detailed', 'Hiper detalhamento', 'hyper detailed, obsessive detail, meticulous precision, intensive detail work, perfectionist approach', 'hyper_detailed', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Detalhamento Profissional' AND tipo_aba = 'qualidade'), 'Extremely Detailed', 'Extremamente detalhado', 'extremely detailed, exceptional detail, intensive rendering, thorough execution, complete precision', 'extremely_detailed', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Detalhamento Profissional' AND tipo_aba = 'qualidade'), 'Intricate Details', 'Detalhes intrincados', 'intricate details, complex patterns, elaborate textures, sophisticated elements, nuanced features', 'intricate_details', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Detalhamento Profissional' AND tipo_aba = 'qualidade'), 'Fine Details', 'Detalhes refinados', 'fine details, delicate features, subtle elements, refined textures, elegant precision', 'fine_details', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Detalhamento Profissional' AND tipo_aba = 'qualidade'), 'Perfect Anatomy', 'Anatomia perfeita', 'perfect anatomy, accurate proportions, realistic structure, correct geometry, flawless form', 'perfect_anatomy', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Detalhamento Profissional' AND tipo_aba = 'qualidade'), 'Flawless Composition', 'Composição impecável', 'flawless composition, perfect balance, ideal arrangement, harmonious layout, optimal structure', 'flawless_composition', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Detalhamento Profissional' AND tipo_aba = 'qualidade'), 'Meticulous Craftsmanship', 'Artesanato meticuloso', 'meticulous craftsmanship, careful execution, precise workmanship, skilled technique, expert handling', 'meticulous_craftsmanship', 8, true);

-- BLOCO 3: Padrão Comercial (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Padrão Comercial' AND tipo_aba = 'qualidade'), 'Magazine Cover', 'Capa de revista', 'magazine cover quality, editorial standard, publication grade, commercial appeal, market ready', 'magazine_cover', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Padrão Comercial' AND tipo_aba = 'qualidade'), 'Portfolio Quality', 'Qualidade de portfólio', 'portfolio quality, professional showcase, career defining, industry standard, presentation grade', 'portfolio_quality', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Padrão Comercial' AND tipo_aba = 'qualidade'), 'Exhibition Grade', 'Padrão de exposição', 'exhibition grade, display quality, public presentation, showcase standard, gallery level', 'exhibition_grade', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Padrão Comercial' AND tipo_aba = 'qualidade'), 'Museum Quality', 'Qualidade de museu', 'museum quality, archival standard, preservation grade, historical significance, cultural value', 'museum_quality', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Padrão Comercial' AND tipo_aba = 'qualidade'), 'Commercial Grade', 'Classificação comercial', 'commercial grade, business standard, market quality, professional use, industry approved', 'commercial_grade', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Padrão Comercial' AND tipo_aba = 'qualidade'), 'Editorial Standard', 'Padrão editorial', 'editorial standard, publishing quality, media grade, journalistic excellence, content quality', 'editorial_standard', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Padrão Comercial' AND tipo_aba = 'qualidade'), 'Advertising Quality', 'Qualidade publicitária', 'advertising quality, marketing standard, promotional grade, brand quality, campaign level', 'advertising_quality', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Padrão Comercial' AND tipo_aba = 'qualidade'), 'Luxury Brand', 'Marca de luxo', 'luxury brand quality, premium standard, high-end appeal, exclusive grade, sophisticated level', 'luxury_brand', 8, true);

-- BLOCO 4: Excelência Técnica (10 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Excelência Técnica' AND tipo_aba = 'qualidade'), 'Perfect Lighting', 'Iluminação perfeita', 'perfect lighting, optimal illumination, flawless light setup, ideal exposure, professional lighting', 'perfect_lighting', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Excelência Técnica' AND tipo_aba = 'qualidade'), 'Professional Photography', 'Fotografia profissional', 'professional photography, expert technique, skilled execution, commercial photography, studio quality', 'professional_photography', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Excelência Técnica' AND tipo_aba = 'qualidade'), 'Studio Lighting', 'Iluminação de estúdio', 'studio lighting, controlled illumination, professional setup, optimal conditions, perfect environment', 'studio_lighting', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Excelência Técnica' AND tipo_aba = 'qualidade'), 'Cinematic Quality', 'Qualidade cinematográfica', 'cinematic quality, film grade, movie standard, theatrical level, cinema production', 'cinematic_quality', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Excelência Técnica' AND tipo_aba = 'qualidade'), 'Film Grade', 'Classificação cinematográfica', 'film grade, movie quality, cinema standard, theatrical production, professional film', 'film_grade', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Excelência Técnica' AND tipo_aba = 'qualidade'), 'Broadcast Quality', 'Qualidade de transmissão', 'broadcast quality, television standard, media grade, transmission ready, broadcast approved', 'broadcast_quality', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Excelência Técnica' AND tipo_aba = 'qualidade'), 'IMAX Standard', 'Padrão IMAX', 'IMAX standard, giant screen quality, premium format, ultra high resolution, immersive experience', 'imax_standard', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Excelência Técnica' AND tipo_aba = 'qualidade'), 'Dolby Vision', 'Tecnologia Dolby Vision', 'Dolby Vision, HDR excellence, enhanced dynamic range, superior color, premium visual', 'dolby_vision', 8, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Excelência Técnica' AND tipo_aba = 'qualidade'), 'Ray Traced', 'Renderização ray tracing', 'ray traced, realistic reflections, accurate lighting, physical rendering, cutting-edge technology', 'ray_traced', 9, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Excelência Técnica' AND tipo_aba = 'qualidade'), '8K Resolution', 'Resolução 8K ultra', '8K resolution, ultra high definition, maximum clarity, supreme detail, future standard', '8k_resolution_quality', 10, true);

-- BLOCO 5: Reconhecimento Digital (12 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Reconhecimento Digital' AND tipo_aba = 'qualidade'), 'Trending ArtStation', 'Tendência no ArtStation', 'trending on ArtStation, featured artwork, popular creation, community favorite, digital art excellence', 'trending_artstation', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Reconhecimento Digital' AND tipo_aba = 'qualidade'), 'DeviantArt Featured', 'Destacado no DeviantArt', 'featured on DeviantArt, daily deviation, community choice, artistic recognition, platform showcase', 'deviantart_featured', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Reconhecimento Digital' AND tipo_aba = 'qualidade'), 'Behance Showcase', 'Showcase do Behance', 'Behance showcase, creative portfolio, professional display, design excellence, creative recognition', 'behance_showcase', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Reconhecimento Digital' AND tipo_aba = 'qualidade'), 'Dribbble Popular', 'Popular no Dribbble', 'popular on Dribbble, design community, creative inspiration, trending design, shot of the day', 'dribbble_popular', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Reconhecimento Digital' AND tipo_aba = 'qualidade'), 'Instagram Viral', 'Viral no Instagram', 'Instagram viral, social media hit, trending content, popular post, viral sensation', 'instagram_viral', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Reconhecimento Digital' AND tipo_aba = 'qualidade'), 'Pinterest Trending', 'Tendência no Pinterest', 'trending on Pinterest, popular pin, viral board, inspiration board, trending idea', 'pinterest_trending', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Reconhecimento Digital' AND tipo_aba = 'qualidade'), 'Featured Artwork', 'Arte em destaque', 'featured artwork, highlighted creation, selected piece, showcase selection, editorial pick', 'featured_artwork', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Reconhecimento Digital' AND tipo_aba = 'qualidade'), 'Viral Content', 'Conteúdo viral', 'viral content, internet sensation, widespread sharing, popular creation, trending topic', 'viral_content', 8, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Reconhecimento Digital' AND tipo_aba = 'qualidade'), 'Reddit Front Page', 'Primeira página Reddit', 'Reddit front page, upvoted content, community favorite, popular submission, trending post', 'reddit_frontpage', 9, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Reconhecimento Digital' AND tipo_aba = 'qualidade'), 'Twitter Viral', 'Viral no Twitter', 'Twitter viral, retweeted content, trending hashtag, viral tweet, social phenomenon', 'twitter_viral', 10, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Reconhecimento Digital' AND tipo_aba = 'qualidade'), 'TikTok Trending', 'Tendência no TikTok', 'TikTok trending, viral video, popular content, trending sound, viral challenge', 'tiktok_trending', 11, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Reconhecimento Digital' AND tipo_aba = 'qualidade'), 'YouTube Featured', 'Destacado no YouTube', 'YouTube featured, trending video, popular channel, viral content, recommended video', 'youtube_featured', 12, true);

-- BLOCO 6: Premiações e Competições (10 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Premiações e Competições' AND tipo_aba = 'qualidade'), 'Contest Winner', 'Vencedor de concurso', 'contest winner, competition champion, first place, winning entry, victorious submission', 'contest_winner', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Premiações e Competições' AND tipo_aba = 'qualidade'), 'Competition Grade', 'Nível de competição', 'competition grade, contest quality, tournament standard, championship level, competitive excellence', 'competition_grade', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Premiações e Competições' AND tipo_aba = 'qualidade'), 'Juried Selection', 'Seleção do júri', 'juried selection, expert choice, professional jury, curated selection, critical acclaim', 'juried_selection', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Premiações e Competições' AND tipo_aba = 'qualidade'), 'Curated Collection', 'Coleção curada', 'curated collection, expert selection, museum curation, gallery choice, professional curation', 'curated_collection', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Premiações e Competições' AND tipo_aba = 'qualidade'), 'Editors Choice', 'Escolha do editor', 'editors choice, editorial selection, staff pick, recommended content, editorial favorite', 'editors_choice', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Premiações e Competições' AND tipo_aba = 'qualidade'), 'Critics Pick', 'Escolha da crítica', 'critics pick, critical acclaim, expert recommendation, professional review, critical favorite', 'critics_pick', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Premiações e Competições' AND tipo_aba = 'qualidade'), 'Peoples Choice', 'Escolha popular', 'peoples choice, audience favorite, popular vote, community selection, public acclaim', 'peoples_choice', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Premiações e Competições' AND tipo_aba = 'qualidade'), 'Platinum Standard', 'Padrão platina', 'platinum standard, highest tier, premium level, elite classification, top grade', 'platinum_standard', 8, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Premiações e Competições' AND tipo_aba = 'qualidade'), 'Hall of Fame', 'Hall da fama', 'hall of fame, legendary status, iconic quality, timeless excellence, immortal creation', 'hall_of_fame', 9, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Premiações e Competições' AND tipo_aba = 'qualidade'), 'Record Breaking', 'Quebra de recorde', 'record breaking, unprecedented quality, historic achievement, milestone creation, benchmark setting', 'record_breaking', 10, true);

-- 5. VERIFICAÇÃO FINAL DOS RESULTADOS

-- Verificar blocos de qualidade criados
SELECT 
    'BLOCOS QUALIDADE CRIADOS' as tipo,
    bc.id,
    bc.titulo,
    bc.tipo_aba,
    bc.ordem_exibicao,
    bc.ativo
FROM blocos_cenas bc 
WHERE bc.tipo_aba = 'qualidade' 
ORDER BY bc.ordem_exibicao;

-- Verificar cenas por bloco
SELECT 
    'CENAS POR BLOCO QUALIDADE' as tipo,
    bc.titulo as bloco,
    COUNT(c.id) as total_cenas,
    STRING_AGG(c.titulo, ', ' ORDER BY c.ordem_exibicao) as cenas
FROM blocos_cenas bc 
LEFT JOIN cenas c ON bc.id = c.bloco_id 
WHERE bc.tipo_aba = 'qualidade' 
GROUP BY bc.id, bc.titulo, bc.ordem_exibicao 
ORDER BY bc.ordem_exibicao;

-- Estatísticas finais
SELECT 
    'ESTATÍSTICAS QUALIDADE' as tipo,
    COUNT(DISTINCT bc.id) as total_blocos,
    COUNT(c.id) as total_cenas,
    COUNT(DISTINCT c.valor_selecao) as valores_unicos
FROM blocos_cenas bc 
LEFT JOIN cenas c ON bc.id = c.bloco_id 
WHERE bc.tipo_aba = 'qualidade';

-- Verificar se há duplicatas (deve retornar 0)
SELECT 
    'VERIFICAÇÃO DUPLICATAS QUALIDADE' as tipo,
    valor_selecao,
    COUNT(*) as duplicatas
FROM cenas 
WHERE valor_selecao IN (
    SELECT valor_selecao 
    FROM cenas c
    JOIN blocos_cenas bc ON c.bloco_id = bc.id 
    WHERE bc.tipo_aba = 'qualidade'
)
GROUP BY valor_selecao 
HAVING COUNT(*) > 1;

-- ========================================
-- RESUMO FINAL QUALIDADE:
-- - 6 blocos especializados criados
-- - 56 aspectos de qualidade profissional
-- - Suprema, detalhamento, comercial, técnica
-- - Reconhecimento digital, premiações
-- - Sistema completo de excelência!
-- ========================================