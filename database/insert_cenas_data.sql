-- ========================================
-- DADOS INICIAIS - SISTEMA DE CENAS
-- ========================================

-- Limpar dados existentes (cuidado em produção!)
DELETE FROM cenas;
DELETE FROM blocos_cenas;

-- Reset AUTO_INCREMENT
ALTER TABLE blocos_cenas AUTO_INCREMENT = 1;
ALTER TABLE cenas AUTO_INCREMENT = 1;

-- ========================================
-- BLOCOS DE CENAS (CATEGORIAS PRINCIPAIS)
-- ========================================

-- ABA AMBIENTE
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao) VALUES
('Natureza', 'nature', 'ambiente', 1),
('Urbano', 'location_city', 'ambiente', 2),
('Interior', 'home', 'ambiente', 3),
('Fantasia', 'auto_fix_high', 'ambiente', 4),
('Futurista', 'rocket_launch', 'ambiente', 5);

-- ABA ILUMINAÇÃO
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao) VALUES
('Natural', 'wb_sunny', 'iluminacao', 1),
('Artificial', 'lightbulb', 'iluminacao', 2),
('Dramática', 'theater_comedy', 'iluminacao', 3),
('Especial', 'auto_fix_high', 'iluminacao', 4),
('Ambiente', 'nights_stay', 'iluminacao', 5);

-- ABA AVATAR/PERSONAGEM
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao) VALUES
('Humanos', 'person', 'avatar', 1),
('Profissões', 'work', 'avatar', 2),
('Fantasia', 'auto_fix_high', 'avatar', 3),
('Animais', 'pets', 'avatar', 4),
('Personalizados', 'face', 'avatar', 5);

-- ABA CÂMERA
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao) VALUES
('Ângulos', 'photo_camera', 'camera', 1),
('Distâncias', 'zoom_in', 'camera', 2),
('Movimentos', 'videocam', 'camera', 3),
('Estilos', 'camera_alt', 'camera', 4),
('Especiais', 'movie_creation', 'camera', 5);

-- ABA VOZ
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao) VALUES
('Tons', 'record_voice_over', 'voz', 1),
('Estilos', 'psychology', 'voz', 2);

-- ABA AÇÃO
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao) VALUES
('Ações Corporais', 'directions_run', 'acao', 1),
('Expressões', 'sentiment_satisfied', 'acao', 2),
('Gestos', 'pan_tool', 'acao', 3),
('Interações', 'handshake', 'acao', 4),
('Dinâmicos', 'speed', 'acao', 5);

-- ========================================
-- CENAS (CARDS INDIVIDUAIS)
-- ========================================

-- AMBIENTE - NATUREZA
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(1, 'Floresta', 'Ambiente natural', 'floresta densa com árvores altas', 'floresta', 1),
(1, 'Praia', 'Costa marítima', 'praia tropical com areia branca', 'praia', 2),
(1, 'Montanha', 'Paisagem montanhosa', 'montanha majestosa com picos nevados', 'montanha', 3),
(1, 'Deserto', 'Ambiente árido', 'deserto vasto com dunas douradas', 'deserto', 4),
(1, 'Campo', 'Paisagem rural', 'campo verde com flores silvestres', 'campo', 5),
(1, 'Lago', 'Corpo d\'água', 'lago cristalino cercado por natureza', 'lago', 6);

-- AMBIENTE - URBANO
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(2, 'Cidade', 'Centro urbano', 'cidade moderna com arranha-céus', 'cidade', 1),
(2, 'Rua', 'Via urbana', 'rua movimentada com pedestres', 'rua', 2),
(2, 'Praça', 'Espaço público', 'praça urbana com fontes e bancos', 'praca', 3),
(2, 'Shopping', 'Centro comercial', 'shopping center moderno', 'shopping', 4),
(2, 'Estação', 'Terminal de transporte', 'estação de trem movimentada', 'estacao', 5),
(2, 'Ponte', 'Estrutura urbana', 'ponte moderna sobre rio urbano', 'ponte', 6);

-- AMBIENTE - INTERIOR
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(3, 'Escritório', 'Ambiente corporativo', 'escritório moderno com tecnologia', 'escritorio', 1),
(3, 'Casa', 'Residência', 'casa aconchegante e familiar', 'casa', 2),
(3, 'Escola', 'Ambiente educacional', 'sala de aula moderna', 'escola', 3),
(3, 'Hospital', 'Ambiente médico', 'hospital limpo e organizado', 'hospital', 4),
(3, 'Restaurante', 'Estabelecimento gastronômico', 'restaurante elegante', 'restaurante', 5),
(3, 'Biblioteca', 'Ambiente de estudos', 'biblioteca silenciosa com livros', 'biblioteca', 6);

-- AMBIENTE - FANTASIA
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(4, 'Castelo', 'Fortaleza medieval', 'castelo medieval mágico', 'castelo', 1),
(4, 'Floresta Mágica', 'Natureza encantada', 'floresta mágica com criaturas', 'floresta_magica', 2),
(4, 'Reino', 'Terra fantástica', 'reino fantástico com magia', 'reino', 3),
(4, 'Caverna', 'Gruta misteriosa', 'caverna misteriosa com cristais', 'caverna', 4),
(4, 'Portal', 'Passagem dimensional', 'portal mágico interdimensional', 'portal', 5),
(4, 'Torre', 'Estrutura mágica', 'torre mágica nas nuvens', 'torre', 6);

-- AMBIENTE - FUTURISTA
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(5, 'Nave Espacial', 'Veículo espacial', 'nave espacial futurística', 'nave_espacial', 1),
(5, 'Cidade Futura', 'Metrópole avançada', 'cidade futurística com tecnologia', 'cidade_futura', 2),
(5, 'Laboratório', 'Centro de pesquisa', 'laboratório high-tech', 'laboratorio', 3),
(5, 'Estação Espacial', 'Base orbital', 'estação espacial avançada', 'estacao_espacial', 4),
(5, 'Planeta Alienígena', 'Mundo extraterrestre', 'planeta alienígena exótico', 'planeta_alienigena', 5),
(5, 'Cibercidade', 'Realidade virtual', 'cibercidade digital neon', 'cibercidade', 6);

-- ILUMINAÇÃO - NATURAL
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(6, 'Luz Solar', 'Iluminação diurna', 'luz solar brilhante e natural', 'luz_solar', 1),
(6, 'Pôr do Sol', 'Luz dourada', 'luz dourada do pôr do sol', 'por_do_sol', 2),
(6, 'Nascer do Sol', 'Luz matinal', 'luz suave do nascer do sol', 'nascer_do_sol', 3),
(6, 'Luz da Lua', 'Iluminação noturna', 'luz prateada da lua cheia', 'luz_da_lua', 4),
(6, 'Luz Difusa', 'Iluminação suave', 'luz difusa através das nuvens', 'luz_difusa', 5),
(6, 'Contraluz', 'Efeito dramático', 'contraluz criando silhuetas', 'contraluz', 6);

-- ILUMINAÇÃO - ARTIFICIAL
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(7, 'LED', 'Iluminação moderna', 'iluminação LED branca e limpa', 'led', 1),
(7, 'Neon', 'Luz colorida', 'luzes neon vibrantes e coloridas', 'neon', 2),
(7, 'Fluorescente', 'Luz comercial', 'iluminação fluorescente uniforme', 'fluorescente', 3),
(7, 'Incandescente', 'Luz quente', 'luz incandescente amarelada', 'incandescente', 4),
(7, 'Spot', 'Foco direcionado', 'spot light direcionado', 'spot', 5),
(7, 'Holofote', 'Luz intensa', 'holofote poderoso', 'holofote', 6);

-- VOZ - TONS
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(16, 'Grave Masculina', 'Tom profundo', 'voz masculina grave e profunda', 'voz_grave_masculina', 1),
(16, 'Suave Feminina', 'Tom delicado', 'voz feminina suave e melodiosa', 'voz_suave_feminina', 2),
(16, 'Energética', 'Tom vibrante', 'voz energética e animada', 'voz_energetica', 3),
(16, 'Calma', 'Tom tranquilo', 'voz calma e relaxante', 'voz_calma', 4),
(16, 'Autoritária', 'Tom firme', 'voz autoritária e confiante', 'voz_autoritaria', 5),
(16, 'Jovem', 'Tom juvenil', 'voz jovem e dinâmica', 'voz_jovem', 6);

-- VOZ - ESTILOS
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(17, 'Documentário', 'Narração informativa', 'estilo de narração de documentário', 'narrador_documentario', 1),
(17, 'Locutor Rádio', 'Estilo radiofônico', 'estilo de locutor de rádio', 'locutor_radio', 2),
(17, 'Conversacional', 'Tom informal', 'estilo conversacional natural', 'conversacional', 3),
(17, 'Teatral', 'Dramatização', 'estilo teatral expressivo', 'teatral', 4),
(17, 'Sussurro', 'Tom íntimo', 'estilo de sussurro envolvente', 'sussurro', 5),
(17, 'Robótico', 'Voz artificial', 'estilo robótico futurista', 'robotico', 6);

-- AÇÃO - AÇÕES CORPORAIS
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(18, 'Correndo', 'Em movimento', 'correndo dinamicamente', 'correndo', 1),
(18, 'Caminhando', 'Movimento calmo', 'caminhando naturalmente', 'caminhando', 2),
(18, 'Saltando', 'Ação dinâmica', 'saltando energicamente', 'saltando', 3),
(18, 'Dançando', 'Movimento rítmico', 'dançando graciosamente', 'dancando', 4),
(18, 'Sentado', 'Posição estática', 'sentado confortavelmente', 'sentado', 5),
(18, 'Deitado', 'Posição relaxada', 'deitado relaxadamente', 'deitado', 6);

-- AÇÃO - EXPRESSÕES
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(19, 'Sorrindo', 'Expressão alegre', 'sorrindo alegremente', 'sorrindo', 1),
(19, 'Pensativo', 'Expressão reflexiva', 'com expressão pensativa', 'pensativo', 2),
(19, 'Surpreso', 'Expressão de surpresa', 'com expressão de surpresa', 'surpreso', 3),
(19, 'Concentrado', 'Foco intenso', 'concentrado intensamente', 'concentrado', 4),
(19, 'Conversando', 'Interação verbal', 'conversando animadamente', 'conversando', 5),
(19, 'Gritando', 'Expressão intensa', 'gritando expressivamente', 'gritando', 6);

-- AÇÃO - GESTOS
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(20, 'Apontando', 'Gesto direcionado', 'apontando direcionalmente', 'apontando', 1),
(20, 'Acenando', 'Gesto de saudação', 'acenando amigavelmente', 'acenando', 2),
(20, 'Aplaudindo', 'Gesto de aprovação', 'aplaudindo entusiasticamente', 'aplaudindo', 3),
(20, 'Segurando', 'Gesto de posse', 'segurando cuidadosamente', 'segurando', 4),
(20, 'Escrevendo', 'Ação manual', 'escrevendo concentradamente', 'escrevendo', 5),
(20, 'Digitando', 'Ação tecnológica', 'digitando rapidamente', 'digitando', 6);

-- AÇÃO - INTERAÇÕES
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(21, 'Cumprimentando', 'Saudação formal', 'cumprimentando cordialmente', 'cumprimentando', 1),
(21, 'Abraçando', 'Gesto afetivo', 'abraçando carinhosamente', 'abracando', 2),
(21, 'Ensinando', 'Ação educativa', 'ensinando pacientemente', 'ensinando', 3),
(21, 'Apresentando', 'Ação demonstrativa', 'apresentando profissionalmente', 'apresentando', 4),
(21, 'Ajudando', 'Ação solidária', 'ajudando solicitamente', 'ajudando', 5),
(21, 'Observando', 'Ação contemplativa', 'observando atentamente', 'observando', 6);

-- AÇÃO - DINÂMICOS
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(22, 'Voando', 'Movimento aéreo', 'voando graciosamente', 'voando', 1),
(22, 'Escalando', 'Movimento vertical', 'escalando habilmente', 'escalando', 2),
(22, 'Nadando', 'Movimento aquático', 'nadando fluidamente', 'nadando', 3),
(22, 'Pedalando', 'Movimento ciclístico', 'pedalando dinamicamente', 'pedalando', 4),
(22, 'Dirigindo', 'Movimento vehicular', 'dirigindo concentradamente', 'dirigindo', 5),
(22, 'Flutuando', 'Movimento suspenso', 'flutuando suavemente', 'flutuando', 6);