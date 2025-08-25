-- ========================================
-- POPULAÇÃO COMPLETA DA ABA TÉCNICA
-- 6 blocos com 52 cenas técnicas especializadas
-- ========================================

-- 1. CORRIGIR CONSTRAINT PARA INCLUIR 'TECNICA'
ALTER TABLE blocos_cenas DROP CONSTRAINT IF EXISTS blocos_cenas_tipo_aba_check;
ALTER TABLE blocos_cenas ADD CONSTRAINT blocos_cenas_tipo_aba_check 
CHECK (tipo_aba IN ('ambiente', 'estilo_visual', 'iluminacao', 'tecnica', 'elementos_especiais', 'qualidade', 'avatar', 'camera', 'voz', 'acao'));

-- 2. LIMPAR DADOS TÉCNICOS EXISTENTES
DELETE FROM cenas WHERE valor_selecao IN (
    'ultra_hd', '8k_resolution', '4k_cinema', 'alta_resolucao', 'super_resolution', 'hdr_plus', 'raw_quality', 'lossless',
    'photorealistic', 'hyperrealistic', 'ultra_detailed', 'crisp_focus', 'razor_sharp', 'macro_detail', 'perfect_clarity', 'studio_quality',
    'professional_lighting', 'cinematic_lighting', 'dramatic_lighting', 'soft_lighting', 'natural_lighting', 'studio_lights', 'golden_hour', 'blue_hour',
    'bokeh_background', 'depth_of_field', 'shallow_focus', 'tilt_shift', 'macro_lens', 'telephoto', 'wide_angle', 'fisheye',
    'smooth_render', 'anti_aliasing', 'ray_tracing', 'global_illumination', 'ambient_occlusion', 'subsurface_scattering', 'motion_blur', 'volumetric_lighting',
    'octane_render', 'unreal_engine', 'blender_cycles', 'arnold_render', 'vray_render', 'redshift_render', 'cinema4d', 'maya_render'
);

DELETE FROM cenas WHERE bloco_id IN (
    SELECT id FROM blocos_cenas WHERE tipo_aba = 'tecnica' OR titulo IN (
        'Qualidade e Resolução', 'Realismo e Detalhamento', 'Iluminação Técnica', 
        'Profundidade e Foco', 'Renderização Avançada', 'Engines e Software'
    )
);

DELETE FROM blocos_cenas WHERE tipo_aba = 'tecnica' OR titulo IN (
    'Qualidade e Resolução', 'Realismo e Detalhamento', 'Iluminação Técnica', 
    'Profundidade e Foco', 'Renderização Avançada', 'Engines e Software'
);

-- 3. INSERIR BLOCOS TÉCNICOS (6 blocos especializados)

INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES
('Qualidade e Resolução', 'high_quality', 'tecnica', 1, true),
('Realismo e Detalhamento', 'zoom_in', 'tecnica', 2, true),
('Iluminação Técnica', 'lightbulb', 'tecnica', 3, true),
('Profundidade e Foco', 'center_focus_strong', 'tecnica', 4, true),
('Renderização Avançada', 'settings', 'tecnica', 5, true),
('Engines e Software', 'memory', 'tecnica', 6, true);

-- 4. INSERIR TODAS AS 52 CENAS TÉCNICAS

-- BLOCO 1: Qualidade e Resolução (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade e Resolução' AND tipo_aba = 'tecnica'), 'Ultra HD', 'Resolução ultra alta definição', 'ultra HD, resolução ultra alta, qualidade suprema, nitidez extrema', 'ultra_hd', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade e Resolução' AND tipo_aba = 'tecnica'), '8K Resolution', 'Resolução 8K profissional', '8K resolution, ultra high definition, professional quality, crystal clear', '8k_resolution', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade e Resolução' AND tipo_aba = 'tecnica'), '4K Cinema', 'Qualidade cinematográfica 4K', '4K cinema quality, movie grade resolution, broadcast quality, ultra sharp', '4k_cinema', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade e Resolução' AND tipo_aba = 'tecnica'), 'Alta Resolução', 'Resolução maximizada', 'alta resolução, high resolution, maximum quality, enhanced detail', 'alta_resolucao', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade e Resolução' AND tipo_aba = 'tecnica'), 'Super Resolution', 'Super resolução AI upscale', 'super resolution, AI upscale, enhanced quality, resolution boost', 'super_resolution', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade e Resolução' AND tipo_aba = 'tecnica'), 'HDR Plus', 'Alto alcance dinâmico melhorado', 'HDR+, high dynamic range, enhanced contrast, vibrant colors', 'hdr_plus', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade e Resolução' AND tipo_aba = 'tecnica'), 'RAW Quality', 'Qualidade RAW sem compressão', 'RAW quality, uncompressed, lossless quality, professional grade', 'raw_quality', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Qualidade e Resolução' AND tipo_aba = 'tecnica'), 'Lossless', 'Qualidade sem perdas', 'lossless quality, perfect preservation, no compression artifacts, pristine', 'lossless', 8, true);

-- BLOCO 2: Realismo e Detalhamento (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Realismo e Detalhamento' AND tipo_aba = 'tecnica'), 'Photorealistic', 'Realismo fotográfico perfeito', 'photorealistic, realistic photography, lifelike, indistinguishable from reality', 'photorealistic', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Realismo e Detalhamento' AND tipo_aba = 'tecnica'), 'Hyperrealistic', 'Hiper-realismo extremo', 'hyperrealistic, extremely detailed, ultra realistic, beyond photographic', 'hyperrealistic', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Realismo e Detalhamento' AND tipo_aba = 'tecnica'), 'Ultra Detailed', 'Detalhamento extremo', 'ultra detailed, intricate details, fine textures, microscopic precision', 'ultra_detailed', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Realismo e Detalhamento' AND tipo_aba = 'tecnica'), 'Crisp Focus', 'Foco cristalino perfeito', 'crisp focus, razor sharp focus, perfect clarity, tack sharp', 'crisp_focus', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Realismo e Detalhamento' AND tipo_aba = 'tecnica'), 'Razor Sharp', 'Nitidez extrema', 'razor sharp, knife-edge sharpness, surgical precision, ultra crisp', 'razor_sharp', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Realismo e Detalhamento' AND tipo_aba = 'tecnica'), 'Macro Detail', 'Detalhes macro extremos', 'macro detail, microscopic detail, extreme close-up clarity, fine detail', 'macro_detail', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Realismo e Detalhamento' AND tipo_aba = 'tecnica'), 'Perfect Clarity', 'Clareza absoluta', 'perfect clarity, crystal clear, absolute sharpness, flawless definition', 'perfect_clarity', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Realismo e Detalhamento' AND tipo_aba = 'tecnica'), 'Studio Quality', 'Qualidade de estúdio', 'studio quality, professional grade, commercial photography, premium detail', 'studio_quality', 8, true);

-- BLOCO 3: Iluminação Técnica (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Iluminação Técnica' AND tipo_aba = 'tecnica'), 'Professional Lighting', 'Iluminação profissional', 'professional lighting, studio lighting setup, commercial grade lighting', 'professional_lighting', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Iluminação Técnica' AND tipo_aba = 'tecnica'), 'Cinematic Lighting', 'Iluminação cinematográfica', 'cinematic lighting, movie quality lighting, dramatic film lighting', 'cinematic_lighting', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Iluminação Técnica' AND tipo_aba = 'tecnica'), 'Dramatic Lighting', 'Iluminação dramática', 'dramatic lighting, high contrast lighting, theatrical lighting', 'dramatic_lighting', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Iluminação Técnica' AND tipo_aba = 'tecnica'), 'Soft Lighting', 'Iluminação suave profissional', 'soft lighting, diffused lighting, gentle illumination, flattering light', 'soft_lighting', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Iluminação Técnica' AND tipo_aba = 'tecnica'), 'Natural Lighting', 'Iluminação natural perfeita', 'natural lighting, perfect daylight, ambient lighting, outdoor lighting', 'natural_lighting', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Iluminação Técnica' AND tipo_aba = 'tecnica'), 'Studio Lights', 'Setup de luzes de estúdio', 'studio lights, softbox lighting, key and fill lighting, three-point lighting', 'studio_lights', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Iluminação Técnica' AND tipo_aba = 'tecnica'), 'Golden Hour', 'Hora dourada técnica', 'golden hour lighting, warm natural light, perfect sunset lighting', 'golden_hour', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Iluminação Técnica' AND tipo_aba = 'tecnica'), 'Blue Hour', 'Hora azul técnica', 'blue hour lighting, twilight lighting, magical evening light', 'blue_hour', 8, true);

-- BLOCO 4: Profundidade e Foco (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Profundidade e Foco' AND tipo_aba = 'tecnica'), 'Bokeh Background', 'Fundo desfocado artístico', 'beautiful bokeh, creamy background blur, shallow depth of field', 'bokeh_background', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Profundidade e Foco' AND tipo_aba = 'tecnica'), 'Depth of Field', 'Profundidade de campo controlada', 'precise depth of field, selective focus, professional DOF', 'depth_of_field', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Profundidade e Foco' AND tipo_aba = 'tecnica'), 'Shallow Focus', 'Foco raso extremo', 'shallow focus, ultra shallow DOF, extreme background separation', 'shallow_focus', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Profundidade e Foco' AND tipo_aba = 'tecnica'), 'Tilt Shift', 'Efeito tilt-shift técnico', 'tilt-shift effect, miniature effect, selective focus plane', 'tilt_shift', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Profundidade e Foco' AND tipo_aba = 'tecnica'), 'Macro Lens', 'Lente macro técnica', 'macro lens photography, extreme close-up, 1:1 magnification', 'macro_lens', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Profundidade e Foco' AND tipo_aba = 'tecnica'), 'Telephoto', 'Lente telefoto profissional', 'telephoto lens, compression effect, distant subject isolation', 'telephoto', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Profundidade e Foco' AND tipo_aba = 'tecnica'), 'Wide Angle', 'Grande angular técnica', 'wide angle lens, expansive view, architectural perspective', 'wide_angle', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Profundidade e Foco' AND tipo_aba = 'tecnica'), 'Fisheye', 'Olho de peixe técnico', 'fisheye lens effect, 180 degree view, spherical distortion', 'fisheye', 8, true);

-- BLOCO 5: Renderização Avançada (10 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Renderização Avançada' AND tipo_aba = 'tecnica'), 'Smooth Render', 'Renderização suave perfeita', 'smooth render, clean surfaces, perfect geometry, anti-aliased', 'smooth_render', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Renderização Avançada' AND tipo_aba = 'tecnica'), 'Anti-Aliasing', 'Anti-aliasing avançado', 'advanced anti-aliasing, smooth edges, no jagged lines, clean render', 'anti_aliasing', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Renderização Avançada' AND tipo_aba = 'tecnica'), 'Ray Tracing', 'Ray tracing realístico', 'ray tracing, realistic reflections, accurate lighting, physical rendering', 'ray_tracing', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Renderização Avançada' AND tipo_aba = 'tecnica'), 'Global Illumination', 'Iluminação global avançada', 'global illumination, realistic light bouncing, ambient lighting', 'global_illumination', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Renderização Avançada' AND tipo_aba = 'tecnica'), 'Ambient Occlusion', 'Oclusão ambiente técnica', 'ambient occlusion, realistic shadows, depth enhancement', 'ambient_occlusion', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Renderização Avançada' AND tipo_aba = 'tecnica'), 'Subsurface Scattering', 'Espalhamento subsuperficial', 'subsurface scattering, realistic skin, translucent materials', 'subsurface_scattering', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Renderização Avançada' AND tipo_aba = 'tecnica'), 'Motion Blur', 'Motion blur técnico', 'motion blur, speed effect, dynamic movement, realistic blur', 'motion_blur', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Renderização Avançada' AND tipo_aba = 'tecnica'), 'Volumetric Lighting', 'Iluminação volumétrica', 'volumetric lighting, light rays, atmospheric effects, god rays', 'volumetric_lighting', 8, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Renderização Avançada' AND tipo_aba = 'tecnica'), 'HDRI Lighting', 'Iluminação HDRI realística', 'HDRI lighting, environment lighting, realistic reflections', 'hdri_lighting', 9, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Renderização Avançada' AND tipo_aba = 'tecnica'), 'PBR Materials', 'Materiais PBR realísticos', 'PBR materials, physically based rendering, realistic surfaces', 'pbr_materials', 10, true);

-- BLOCO 6: Engines e Software (12 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Engines e Software' AND tipo_aba = 'tecnica'), 'Octane Render', 'Renderização Octane GPU', 'rendered in Octane, GPU rendering, unbiased rendering, photorealistic', 'octane_render', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Engines e Software' AND tipo_aba = 'tecnica'), 'Unreal Engine', 'Motor Unreal Engine 5', 'Unreal Engine 5, real-time rendering, Lumen lighting, Nanite geometry', 'unreal_engine', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Engines e Software' AND tipo_aba = 'tecnica'), 'Blender Cycles', 'Blender Cycles render', 'Blender Cycles, path tracing, open source rendering, realistic lighting', 'blender_cycles', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Engines e Software' AND tipo_aba = 'tecnica'), 'Arnold Render', 'Arnold render engine', 'Arnold renderer, Monte Carlo ray tracing, VFX quality', 'arnold_render', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Engines e Software' AND tipo_aba = 'tecnica'), 'V-Ray Render', 'V-Ray render engine', 'V-Ray rendering, architectural visualization, photorealistic output', 'vray_render', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Engines e Software' AND tipo_aba = 'tecnica'), 'Redshift Render', 'Redshift GPU render', 'Redshift rendering, GPU acceleration, production quality', 'redshift_render', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Engines e Software' AND tipo_aba = 'tecnica'), 'Cinema 4D', 'Cinema 4D render', 'Cinema 4D rendering, motion graphics quality, professional output', 'cinema4d', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Engines e Software' AND tipo_aba = 'tecnica'), 'Maya Render', 'Autodesk Maya render', 'Maya rendering, industry standard, film quality output', 'maya_render', 8, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Engines e Software' AND tipo_aba = 'tecnica'), 'Corona Render', 'Corona render engine', 'Corona renderer, architectural rendering, realistic materials', 'corona_render', 9, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Engines e Software' AND tipo_aba = 'tecnica'), 'KeyShot Render', 'KeyShot product render', 'KeyShot rendering, product visualization, real-time ray tracing', 'keyshot_render', 10, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Engines e Software' AND tipo_aba = 'tecnica'), 'Substance 3D', 'Adobe Substance 3D', 'Substance 3D materials, procedural textures, PBR workflow', 'substance_3d', 11, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Engines e Software' AND tipo_aba = 'tecnica'), 'Houdini Render', 'SideFX Houdini render', 'Houdini Mantra, procedural rendering, VFX pipeline', 'houdini_render', 12, true);

-- 5. VERIFICAÇÃO FINAL DOS RESULTADOS

-- Verificar blocos técnicos criados
SELECT 
    'BLOCOS TÉCNICOS CRIADOS' as tipo,
    bc.id,
    bc.titulo,
    bc.tipo_aba,
    bc.ordem_exibicao,
    bc.ativo
FROM blocos_cenas bc 
WHERE bc.tipo_aba = 'tecnica' 
ORDER BY bc.ordem_exibicao;

-- Verificar cenas técnicas por bloco
SELECT 
    'CENAS TÉCNICAS POR BLOCO' as tipo,
    bc.titulo as bloco,
    COUNT(c.id) as total_cenas,
    STRING_AGG(c.titulo, ', ' ORDER BY c.ordem_exibicao) as cenas
FROM blocos_cenas bc 
LEFT JOIN cenas c ON bc.id = c.bloco_id 
WHERE bc.tipo_aba = 'tecnica' 
GROUP BY bc.id, bc.titulo, bc.ordem_exibicao 
ORDER BY bc.ordem_exibicao;

-- Estatísticas finais da aba técnica
SELECT 
    'ESTATÍSTICAS TÉCNICA' as tipo,
    COUNT(DISTINCT bc.id) as total_blocos,
    COUNT(c.id) as total_cenas,
    COUNT(DISTINCT c.valor_selecao) as valores_unicos
FROM blocos_cenas bc 
LEFT JOIN cenas c ON bc.id = c.bloco_id 
WHERE bc.tipo_aba = 'tecnica';

-- Verificar se há duplicatas (deve retornar 0)
SELECT 
    'VERIFICAÇÃO DUPLICATAS TÉCNICA' as tipo,
    valor_selecao,
    COUNT(*) as duplicatas
FROM cenas 
WHERE valor_selecao IN (
    SELECT valor_selecao 
    FROM cenas c
    JOIN blocos_cenas bc ON c.bloco_id = bc.id 
    WHERE bc.tipo_aba = 'tecnica'
)
GROUP BY valor_selecao 
HAVING COUNT(*) > 1;

-- ========================================
-- RESUMO FINAL DA ABA TÉCNICA:
-- - 6 blocos especializados criados
-- - 52 cenas técnicas avançadas
-- - Cobertura completa de aspectos técnicos
-- - Qualidade, renderização, iluminação, foco
-- - Engines profissionais incluídos
-- - Sistema técnico completo e funcional!
-- ========================================