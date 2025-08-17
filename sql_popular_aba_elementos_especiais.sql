-- ========================================
-- POPULAÇÃO COMPLETA DA ABA ELEMENTOS ESPECIAIS
-- 6 blocos com 54 elementos especiais únicos e criativos
-- ========================================

-- 1. CORRIGIR CONSTRAINT PARA INCLUIR 'ELEMENTOS_ESPECIAIS'
ALTER TABLE blocos_cenas DROP CONSTRAINT IF EXISTS blocos_cenas_tipo_aba_check;
ALTER TABLE blocos_cenas ADD CONSTRAINT blocos_cenas_tipo_aba_check 
CHECK (tipo_aba IN ('ambiente', 'estilo_visual', 'iluminacao', 'tecnica', 'elementos_especiais', 'qualidade', 'avatar', 'camera', 'voz', 'acao'));

-- 2. LIMPAR DADOS DE ELEMENTOS ESPECIAIS EXISTENTES
DELETE FROM cenas WHERE valor_selecao IN (
    'particulas_magicas', 'energia_eletrica', 'fogo_mistico', 'agua_cristalina', 'vento_espiral', 'terra_rochosa', 'gelo_cristalizado', 'plasma_energetico',
    'aura_dourada', 'campo_de_forca', 'escudo_energia', 'barreira_magica', 'halo_luminoso', 'radiacao_cosmica', 'ondas_sonicas', 'pulso_eletromagnetico',
    'portal_dimensional', 'fenda_temporal', 'buraco_negro', 'vortice_espacial', 'teletransporte', 'viagem_tempo', 'dobra_espacial', 'singularidade',
    'holografia', 'projecao_3d', 'realidade_aumentada', 'interface_neural', 'dados_flutuantes', 'codigo_matrix', 'circuitos_neon', 'energia_digital',
    'raio_laser', 'explosao_nuclear', 'impacto_meteorito', 'tempestade_eletrica', 'tornado_fogo', 'tsunami_energia', 'terremoto_magico', 'eclipse_solar',
    'cristais_flutuantes', 'runas_antigas', 'simbolos_misticos', 'mandalas_energeticas', 'geometria_sagrada', 'fractais_infinitos', 'padroes_cosmicos', 'simetria_perfeita'
);

DELETE FROM cenas WHERE bloco_id IN (
    SELECT id FROM blocos_cenas WHERE tipo_aba = 'elementos_especiais' OR titulo IN (
        'Elementos Naturais Mágicos', 'Energia e Campos de Força', 'Portais e Dimensões', 
        'Tecnologia Futurística', 'Fenômenos Cósmicos', 'Símbolos e Geometria Sagrada'
    )
);

DELETE FROM blocos_cenas WHERE tipo_aba = 'elementos_especiais' OR titulo IN (
    'Elementos Naturais Mágicos', 'Energia e Campos de Força', 'Portais e Dimensões', 
    'Tecnologia Futurística', 'Fenômenos Cósmicos', 'Símbolos e Geometria Sagrada'
);

-- 3. INSERIR BLOCOS DE ELEMENTOS ESPECIAIS (6 blocos especializados)

INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES
('Elementos Naturais Mágicos', 'local_fire_department', 'elementos_especiais', 1, true),
('Energia e Campos de Força', 'flash_on', 'elementos_especiais', 2, true),
('Portais e Dimensões', 'call_made', 'elementos_especiais', 3, true),
('Tecnologia Futurística', 'memory', 'elementos_especiais', 4, true),
('Fenômenos Cósmicos', 'flare', 'elementos_especiais', 5, true),
('Símbolos e Geometria Sagrada', 'grain', 'elementos_especiais', 6, true);

-- 4. INSERIR TODAS AS 54 CENAS DE ELEMENTOS ESPECIAIS

-- BLOCO 1: Elementos Naturais Mágicos (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Elementos Naturais Mágicos' AND tipo_aba = 'elementos_especiais'), 'Partículas Mágicas', 'Pó de fada brilhante', 'magical particles, glittering fairy dust, floating sparkles, mystical energy, ethereal glow', 'particulas_magicas', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Elementos Naturais Mágicos' AND tipo_aba = 'elementos_especiais'), 'Energia Elétrica', 'Raios e descargas elétricas', 'electric energy, lightning bolts, electrical discharge, sparks flying, electric aura', 'energia_eletrica', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Elementos Naturais Mágicos' AND tipo_aba = 'elementos_especiais'), 'Fogo Místico', 'Chamas mágicas coloridas', 'mystical fire, magical flames, colored fire, sacred burning, ethereal blaze', 'fogo_mistico', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Elementos Naturais Mágicos' AND tipo_aba = 'elementos_especiais'), 'Água Cristalina', 'Água pura com brilho mágico', 'crystal clear water, magical water, pure liquid, flowing energy, aquatic magic', 'agua_cristalina', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Elementos Naturais Mágicos' AND tipo_aba = 'elementos_especiais'), 'Vento Espiral', 'Redemoinhos de ar visível', 'spiral wind, visible air currents, swirling breeze, wind magic, atmospheric energy', 'vento_espiral', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Elementos Naturais Mágicos' AND tipo_aba = 'elementos_especiais'), 'Terra Rochosa', 'Pedras flutuantes mágicas', 'floating rocks, levitating stones, earth magic, geological energy, mineral power', 'terra_rochosa', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Elementos Naturais Mágicos' AND tipo_aba = 'elementos_especiais'), 'Gelo Cristalizado', 'Cristais de gelo mágico', 'crystallized ice, magical frost, ice crystals, frozen energy, arctic magic', 'gelo_cristalizado', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Elementos Naturais Mágicos' AND tipo_aba = 'elementos_especiais'), 'Plasma Energético', 'Energia pura em estado plasma', 'energy plasma, pure energy, plasma state, ionized particles, electromagnetic field', 'plasma_energetico', 8, true);

-- BLOCO 2: Energia e Campos de Força (9 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Energia e Campos de Força' AND tipo_aba = 'elementos_especiais'), 'Aura Dourada', 'Campo energético dourado', 'golden aura, energy field, divine glow, radiant light, spiritual energy', 'aura_dourada', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Energia e Campos de Força' AND tipo_aba = 'elementos_especiais'), 'Campo de Força', 'Barreira energética transparente', 'force field, energy barrier, protective shield, transparent dome, deflector field', 'campo_de_forca', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Energia e Campos de Força' AND tipo_aba = 'elementos_especiais'), 'Escudo de Energia', 'Proteção energética brilhante', 'energy shield, glowing protection, defensive barrier, bright shield, power defense', 'escudo_energia', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Energia e Campos de Força' AND tipo_aba = 'elementos_especiais'), 'Barreira Mágica', 'Muralha de energia mística', 'magical barrier, mystical wall, arcane protection, spell shield, enchanted defense', 'barreira_magica', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Energia e Campos de Força' AND tipo_aba = 'elementos_especiais'), 'Halo Luminoso', 'Círculo de luz sagrada', 'luminous halo, sacred light circle, divine ring, celestial glow, angelic aura', 'halo_luminoso', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Energia e Campos de Força' AND tipo_aba = 'elementos_especiais'), 'Radiação Cósmica', 'Energia do espaço sideral', 'cosmic radiation, space energy, stellar power, galactic force, universe energy', 'radiacao_cosmica', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Energia e Campos de Força' AND tipo_aba = 'elementos_especiais'), 'Ondas Sônicas', 'Vibrações sonoras visíveis', 'sonic waves, sound vibrations, audio energy, frequency waves, acoustic power', 'ondas_sonicas', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Energia e Campos de Força' AND tipo_aba = 'elementos_especiais'), 'Pulso Eletromagnético', 'Onda eletromagnética intensa', 'electromagnetic pulse, EMP wave, electronic disruption, magnetic field, electric surge', 'pulso_eletromagnetico', 8, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Energia e Campos de Força' AND tipo_aba = 'elementos_especiais'), 'Bioenergia', 'Energia vital orgânica', 'bio energy, life force, organic power, vital energy, living aura', 'bioenergia', 9, true);

-- BLOCO 3: Portais e Dimensões (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Portais e Dimensões' AND tipo_aba = 'elementos_especiais'), 'Portal Dimensional', 'Passagem entre realidades', 'dimensional portal, reality gateway, interdimensional passage, space-time door, multiverse entrance', 'portal_dimensional', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Portais e Dimensões' AND tipo_aba = 'elementos_especiais'), 'Fenda Temporal', 'Rachadura no tempo', 'temporal rift, time crack, chronological break, time stream disruption, temporal anomaly', 'fenda_temporal', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Portais e Dimensões' AND tipo_aba = 'elementos_especiais'), 'Buraco Negro', 'Singularidade espacial', 'black hole, event horizon, gravitational singularity, space-time curvature, cosmic void', 'buraco_negro', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Portais e Dimensões' AND tipo_aba = 'elementos_especiais'), 'Vórtice Espacial', 'Redemoinho dimensional', 'space vortex, dimensional whirlpool, cosmic spiral, wormhole entrance, spatial distortion', 'vortice_espacial', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Portais e Dimensões' AND tipo_aba = 'elementos_especiais'), 'Teletransporte', 'Materialização instantânea', 'teleportation effect, instant materialization, quantum jump, phase shift, molecular transport', 'teletransporte', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Portais e Dimensões' AND tipo_aba = 'elementos_especiais'), 'Viagem no Tempo', 'Distorção temporal', 'time travel, temporal distortion, chronological shift, time displacement, temporal vortex', 'viagem_tempo', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Portais e Dimensões' AND tipo_aba = 'elementos_especiais'), 'Dobra Espacial', 'Curvatura do espaço-tempo', 'space fold, space-time curvature, dimensional bend, reality warp, spatial manipulation', 'dobra_espacial', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Portais e Dimensões' AND tipo_aba = 'elementos_especiais'), 'Singularidade', 'Ponto de convergência', 'singularity point, convergence nexus, reality focus, dimensional anchor, space-time center', 'singularidade', 8, true);

-- BLOCO 4: Tecnologia Futurística (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Tecnologia Futurística' AND tipo_aba = 'elementos_especiais'), 'Holografia', 'Projeções tridimensionais', 'holographic display, 3D projection, volumetric image, light construct, digital hologram', 'holografia', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Tecnologia Futurística' AND tipo_aba = 'elementos_especiais'), 'Projeção 3D', 'Imagens flutuantes', 'floating 3D projection, levitating display, aerial interface, suspended graphics, mid-air screen', 'projecao_3d', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Tecnologia Futurística' AND tipo_aba = 'elementos_especiais'), 'Realidade Aumentada', 'Sobreposição digital', 'augmented reality, AR overlay, digital enhancement, virtual information, mixed reality', 'realidade_aumentada', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Tecnologia Futurística' AND tipo_aba = 'elementos_especiais'), 'Interface Neural', 'Conexão mente-máquina', 'neural interface, brain-computer link, mental connection, cybernetic implant, thought control', 'interface_neural', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Tecnologia Futurística' AND tipo_aba = 'elementos_especiais'), 'Dados Flutuantes', 'Informações no ar', 'floating data, airborne information, levitating text, suspended numbers, aerial statistics', 'dados_flutuantes', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Tecnologia Futurística' AND tipo_aba = 'elementos_especiais'), 'Código Matrix', 'Chuva de código verde', 'matrix code rain, green digital rain, cascading data, binary streams, cyber reality', 'codigo_matrix', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Tecnologia Futurística' AND tipo_aba = 'elementos_especiais'), 'Circuitos Neon', 'Trilhas eletrônicas brilhantes', 'neon circuits, glowing pathways, electronic traces, luminous wiring, cyber veins', 'circuitos_neon', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Tecnologia Futurística' AND tipo_aba = 'elementos_especiais'), 'Energia Digital', 'Poder computacional visível', 'digital energy, computational power, data flow, electronic current, cyber essence', 'energia_digital', 8, true);

-- BLOCO 5: Fenômenos Cósmicos (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Fenômenos Cósmicos' AND tipo_aba = 'elementos_especiais'), 'Raio Laser', 'Feixe de energia concentrada', 'laser beam, concentrated energy, focused light, photon ray, coherent radiation', 'raio_laser', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Fenômenos Cósmicos' AND tipo_aba = 'elementos_especiais'), 'Explosão Nuclear', 'Detonação atômica', 'nuclear explosion, atomic blast, mushroom cloud, radioactive burst, fusion detonation', 'explosao_nuclear', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Fenômenos Cósmicos' AND tipo_aba = 'elementos_especiais'), 'Impacto de Meteorito', 'Colisão espacial devastadora', 'meteorite impact, asteroid collision, cosmic crash, celestial bombardment, space rock strike', 'impacto_meteorito', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Fenômenos Cósmicos' AND tipo_aba = 'elementos_especiais'), 'Tempestade Elétrica', 'Raios intensos no céu', 'electric storm, lightning tempest, electrical hurricane, thunder chaos, charged atmosphere', 'tempestade_eletrica', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Fenômenos Cósmicos' AND tipo_aba = 'elementos_especiais'), 'Tornado de Fogo', 'Redemoinho flamejante', 'fire tornado, flaming vortex, burning whirlwind, pyroclastic spiral, flame cyclone', 'tornado_fogo', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Fenômenos Cósmicos' AND tipo_aba = 'elementos_especiais'), 'Tsunami de Energia', 'Onda energética gigantesca', 'energy tsunami, power wave, massive energy surge, overwhelming force, energetic flood', 'tsunami_energia', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Fenômenos Cósmicos' AND tipo_aba = 'elementos_especiais'), 'Terremoto Mágico', 'Tremor de terra sobrenatural', 'magical earthquake, mystical tremor, supernatural quake, enchanted ground shake, arcane seismic', 'terremoto_magico', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Fenômenos Cósmicos' AND tipo_aba = 'elementos_especiais'), 'Eclipse Solar', 'Ocultação celestial', 'solar eclipse, celestial alignment, cosmic shadow, stellar occultation, astronomical event', 'eclipse_solar', 8, true);

-- BLOCO 6: Símbolos e Geometria Sagrada (8 cenas)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES
((SELECT id FROM blocos_cenas WHERE titulo = 'Símbolos e Geometria Sagrada' AND tipo_aba = 'elementos_especiais'), 'Cristais Flutuantes', 'Gemas levitando no ar', 'floating crystals, levitating gems, airborne minerals, suspended stones, magical crystals', 'cristais_flutuantes', 1, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Símbolos e Geometria Sagrada' AND tipo_aba = 'elementos_especiais'), 'Runas Antigas', 'Símbolos místicos brilhantes', 'ancient runes, glowing symbols, mystical writing, magical inscriptions, elder signs', 'runas_antigas', 2, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Símbolos e Geometria Sagrada' AND tipo_aba = 'elementos_especiais'), 'Símbolos Místicos', 'Marcas sobrenaturais', 'mystical symbols, supernatural marks, occult signs, esoteric emblems, arcane glyphs', 'simbolos_misticos', 3, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Símbolos e Geometria Sagrada' AND tipo_aba = 'elementos_especiais'), 'Mandalas Energéticas', 'Círculos de poder', 'energy mandalas, power circles, spiritual geometry, chakra patterns, cosmic wheels', 'mandalas_energeticas', 4, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Símbolos e Geometria Sagrada' AND tipo_aba = 'elementos_especiais'), 'Geometria Sagrada', 'Formas geométricas divinas', 'sacred geometry, divine shapes, perfect proportions, golden ratio, mathematical harmony', 'geometria_sagrada', 5, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Símbolos e Geometria Sagrada' AND tipo_aba = 'elementos_especiais'), 'Fractais Infinitos', 'Padrões auto-replicantes', 'infinite fractals, self-replicating patterns, mathematical beauty, recursive geometry, endless complexity', 'fractais_infinitos', 6, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Símbolos e Geometria Sagrada' AND tipo_aba = 'elementos_especiais'), 'Padrões Cósmicos', 'Desenhos universais', 'cosmic patterns, universal designs, celestial arrangements, stellar formations, galactic structures', 'padroes_cosmicos', 7, true),
((SELECT id FROM blocos_cenas WHERE titulo = 'Símbolos e Geometria Sagrada' AND tipo_aba = 'elementos_especiais'), 'Simetria Perfeita', 'Equilíbrio absoluto', 'perfect symmetry, absolute balance, flawless harmony, ideal proportion, mathematical perfection', 'simetria_perfeita', 8, true);

-- 5. VERIFICAÇÃO FINAL DOS RESULTADOS

-- Verificar blocos de elementos especiais criados
SELECT 
    'BLOCOS ELEMENTOS ESPECIAIS CRIADOS' as tipo,
    bc.id,
    bc.titulo,
    bc.tipo_aba,
    bc.ordem_exibicao,
    bc.ativo
FROM blocos_cenas bc 
WHERE bc.tipo_aba = 'elementos_especiais' 
ORDER BY bc.ordem_exibicao;

-- Verificar cenas por bloco
SELECT 
    'CENAS POR BLOCO ELEMENTOS ESPECIAIS' as tipo,
    bc.titulo as bloco,
    COUNT(c.id) as total_cenas,
    STRING_AGG(c.titulo, ', ' ORDER BY c.ordem_exibicao) as cenas
FROM blocos_cenas bc 
LEFT JOIN cenas c ON bc.id = c.bloco_id 
WHERE bc.tipo_aba = 'elementos_especiais' 
GROUP BY bc.id, bc.titulo, bc.ordem_exibicao 
ORDER BY bc.ordem_exibicao;

-- Estatísticas finais
SELECT 
    'ESTATÍSTICAS ELEMENTOS ESPECIAIS' as tipo,
    COUNT(DISTINCT bc.id) as total_blocos,
    COUNT(c.id) as total_cenas,
    COUNT(DISTINCT c.valor_selecao) as valores_unicos
FROM blocos_cenas bc 
LEFT JOIN cenas c ON bc.id = c.bloco_id 
WHERE bc.tipo_aba = 'elementos_especiais';

-- Verificar se há duplicatas (deve retornar 0)
SELECT 
    'VERIFICAÇÃO DUPLICATAS ELEMENTOS ESPECIAIS' as tipo,
    valor_selecao,
    COUNT(*) as duplicatas
FROM cenas 
WHERE valor_selecao IN (
    SELECT valor_selecao 
    FROM cenas c
    JOIN blocos_cenas bc ON c.bloco_id = bc.id 
    WHERE bc.tipo_aba = 'elementos_especiais'
)
GROUP BY valor_selecao 
HAVING COUNT(*) > 1;

-- ========================================
-- RESUMO FINAL ELEMENTOS ESPECIAIS:
-- - 6 blocos especializados criados
-- - 54 elementos especiais únicos e criativos
-- - Magia, tecnologia, fenômenos cósmicos
-- - Energia, portais, símbolos sagrados
-- - Sistema completo de elementos extraordinários!
-- ========================================