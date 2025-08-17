-- SEED: Apenas aba Cena/Ambiente (MySQL/MariaDB)
START TRANSACTION;
-- Bloco: Natureza
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Natureza', 'park', 'cena_ambiente', 1, 1);
SET @blk_natureza = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_natureza, 'Floresta', 'Ambiente natural', 'floresta densa com árvores altas', 'floresta', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_natureza, 'Praia', 'Costa marítima', 'praia tropical com areia branca e mar calmo', 'praia', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_natureza, 'Montanha', 'Paisagem montanhosa', 'montanhas majestosas com picos nevados', 'montanha', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_natureza, 'Deserto', 'Ambiente árido', 'deserto vasto com dunas douradas', 'deserto', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_natureza, 'Campo', 'Paisagem rural', 'campo verde com flores silvestres', 'campo', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_natureza, 'Lago', 'Corpo d''água', 'lago cristalino cercado por natureza', 'lago', 6, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_natureza, 'Cachoeira', 'Queda d''água', 'cachoeira alta com névoa suave', 'cachoeira', 7, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_natureza, 'Caverna', 'Ambiente subterrâneo', 'caverna com formações de estalactites', 'caverna', 8, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_natureza, 'Selva', 'Densa e úmida', 'selva tropical com dossel fechado', 'selva', 9, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_natureza, 'Cânion', 'Formação rochosa', 'cânion profundo com paredes avermelhadas', 'canion', 10, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_natureza, 'Tundra', 'Clima frio', 'tundra gelada com vegetação rasteira', 'tundra', 11, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_natureza, 'Manguezal', 'Costeiro', 'manguezal com raízes aéreas e água salobra', 'manguezal', 12, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_natureza, 'Pântano', 'Alagadiço', 'pântano enevoado com águas escuras', 'pantano', 13, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_natureza, 'Savana', 'Tropical sazonal', 'savana com gramíneas altas e acácias', 'savana', 14, 1);

-- Bloco: Urbano
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Urbano', 'location_city', 'cena_ambiente', 2, 1);
SET @blk_urbano = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_urbano, 'Cidade', 'Centro urbano', 'paisagem urbana com arranha-céus', 'cidade', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_urbano, 'Rua', 'Via urbana', 'rua movimentada com carros e pedestres', 'rua', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_urbano, 'Praça', 'Espaço público', 'praça arborizada com bancos', 'praca', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_urbano, 'Metrô', 'Transporte público', 'estação de metrô com plataformas', 'metro', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_urbano, 'Rooftop', 'Cobertura', 'rooftop com vista panorâmica', 'rooftop', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_urbano, 'Beco Grafitado', 'Arte urbana', 'beco estreito com grafites coloridos', 'beco_grafitado', 6, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_urbano, 'Ponte', 'Infraestrutura', 'ponte icônica sobre um rio', 'ponte', 7, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_urbano, 'Mercado de Rua', 'Feira', 'mercado ao ar livre com barracas', 'mercado_rua', 8, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_urbano, 'Porto', 'Zona portuária', 'porto com guindastes e contêineres', 'porto', 9, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_urbano, 'Estação de Trem', 'Transporte', 'estação clássica com trilhos', 'estacao_trem', 10, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_urbano, 'Zona Industrial', 'Fábricas', 'parque industrial com chaminés', 'zona_industrial', 11, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_urbano, 'Bairro Histórico', 'Patrimônio', 'ruas de pedra e fachadas antigas', 'bairro_historico', 12, 1);

-- Bloco: Interior
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Interior', 'home', 'cena_ambiente', 3, 1);
SET @blk_interior = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Escritório', 'Ambiente corporativo', 'escritório moderno com mesas e monitores', 'escritorio', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Casa', 'Residência', 'sala de estar aconchegante', 'casa', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Cozinha', 'Culinária', 'cozinha gourmet bem iluminada', 'cozinha', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Quarto', 'Descanso', 'quarto minimalista com cama arrumada', 'quarto', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Sala de Aula', 'Educação', 'sala de aula com quadro e carteiras', 'sala_de_aula', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Laboratório', 'Pesquisa', 'laboratório com bancadas e vidrarias', 'laboratorio', 6, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Biblioteca', 'Estudo', 'biblioteca com prateleiras altas', 'biblioteca', 7, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Hospital', 'Saúde', 'corredor de hospital limpo', 'hospital', 8, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Estúdio Fotográfico', 'Produção', 'estúdio com softboxes e fundo infinito', 'estudio_fotografico', 9, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Oficina', 'Mecânica/DIY', 'oficina com ferramentas e bancadas', 'oficina', 10, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Restaurante', 'Gastronomia', 'restaurante elegante com mesas postas', 'restaurante', 11, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Cafeteria', 'Café', 'cafeteria artesanal com balcão de madeira', 'cafeteria', 12, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Museu', 'Cultura', 'museu com grandes salas expositivas', 'museu', 13, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Galeria de Arte', 'Exposição', 'galeria minimalista com quadros', 'galeria_arte', 14, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_interior, 'Academia', 'Treino', 'academia com equipamentos modernos', 'academia', 15, 1);

-- Bloco: Temáticos Históricos
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Temáticos Históricos', 'castle', 'cena_ambiente', 4, 1);
SET @blk_temáticos_históricos = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_temáticos_históricos, 'Medieval', 'Período histórico', 'aldeia medieval com castelo ao fundo', 'medieval', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_temáticos_históricos, 'Renascentista', 'Arte e cultura', 'cidade renascentista com cúpulas', 'renascentista', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_temáticos_históricos, 'Vitoriano', 'Arquitetura clássica', 'rua vitoriana com postes de luz', 'vitoriano', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_temáticos_históricos, 'Barroco', 'Ornamentado', 'igreja barroca ricamente decorada', 'barroco', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_temáticos_históricos, 'Colonial', 'Américas', 'centro colonial com casarios coloridos', 'colonial', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_temáticos_históricos, 'Faroeste', 'Velho Oeste', 'cidade do velho oeste com saloon', 'faroeste', 6, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_temáticos_históricos, 'Antiguidade Romana', 'Clássico', 'coliseu romano monumental', 'antiguidade_romana', 7, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_temáticos_históricos, 'Egito Antigo', 'Desértico', 'pirâmides e esfinge ao pôr do sol', 'egito_antigo', 8, 1);

-- Bloco: Futurista & Sci‑Fi
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Futurista & Sci‑Fi', 'science', 'cena_ambiente', 5, 1);
SET @blk_futurista_scifi = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_futurista_scifi, 'Cidade Futurista', 'Alta tecnologia', 'cidade futurista com neon e hologramas', 'cidade_futurista', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_futurista_scifi, 'Cyberpunk', 'Sci‑fi urbano', 'megalópole cyberpunk sob chuva neon', 'cyberpunk', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_futurista_scifi, 'Nave Espacial', 'Interior', 'interior de nave com janelas estelares', 'nave_espacial', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_futurista_scifi, 'Estação Orbital', 'Exterior/Interior', 'estação espacial com anéis e docas', 'estacao_orbital', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_futurista_scifi, 'Hangar', 'Aeronaves', 'hangar hi‑tech com naves atracadas', 'hangar_scifi', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_futurista_scifi, 'Mercado Alienígena', 'Extraterrestre', 'bazar alienígena com espécies diversas', 'mercado_alienigena', 6, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_futurista_scifi, 'Laboratório Hi‑tech', 'Pesquisa', 'laboratório futurista com telas holográficas', 'laboratorio_hitech', 7, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_futurista_scifi, 'Rua Neon', 'Noite', 'rua estreita iluminada por letreiros neon', 'rua_neon', 8, 1);

-- Bloco: Pós‑apocalíptico
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Pós‑apocalíptico', 'coronavirus', 'cena_ambiente', 6, 1);
SET @blk_pósapocalíptico = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_pósapocalíptico, 'Ruínas Urbanas', 'Colapso', 'ruínas de arranha‑céus cobertas por plantas', 'ruinas_urbanas', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_pósapocalíptico, 'Rodovia Abandonada', 'Vazio', 'rodovia quebrada com carros abandonados', 'rodovia_abandonada', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_pósapocalíptico, 'Metrô Alagado', 'Subterrâneo', 'estações alagadas e escuras', 'metro_alagado', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_pósapocalíptico, 'Deserto Nuclear', 'Hostil', 'areia radioativa e céu opaco', 'deserto_nuclear', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_pósapocalíptico, 'Zona Quarentenada', 'Isolamento', 'barreiras e avisos de quarentena', 'zona_quarentena', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_pósapocalíptico, 'Fábrica Tomada', 'Natureza retoma', 'fábrica coberta por heras', 'fabrica_tomada', 6, 1);

-- Bloco: Clima
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Clima', 'cloud', 'cena_ambiente', 7, 1);
SET @blk_clima = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_clima, 'Céu limpo', 'Atmosfera', 'céu claro e luminoso', 'ceu_limpo', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_clima, 'Nublado', 'Atmosfera', 'céu encoberto e difuso', 'nublado', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_clima, 'Neblina', 'Atmosfera', 'neblina suave com baixa visibilidade', 'neblina', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_clima, 'Garoa', 'Precipitação', 'garoa fina constante', 'garoa', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_clima, 'Chuva leve', 'Precipitação', 'chuva fina com reflexos no chão', 'chuva_leve', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_clima, 'Toró', 'Precipitação', 'chuva intensa com poças e respingos', 'toro', 6, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_clima, 'Tempestade elétrica', 'Fenômeno', 'nuvens carregadas com relâmpagos', 'tempestade_eletrica', 7, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_clima, 'Neve', 'Precipitação', 'neve caindo e solo branco', 'neve', 8, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_clima, 'Nevasca', 'Precipitação', 'nevasca densa com vento', 'nevasca', 9, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_clima, 'Vento forte', 'Atmosfera', 'rajadas de vento levantando poeira', 'vento_forte', 10, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_clima, 'Tempestade de areia', 'Fenômeno', 'areia densa reduzindo visibilidade', 'tempestade_areia', 11, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_clima, 'Arco‑íris', 'Fenômeno', 'arco‑íris após chuva', 'arco_iris', 12, 1);

-- Bloco: Hora do Dia
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Hora do Dia', 'schedule', 'cena_ambiente', 8, 1);
SET @blk_hora_do_dia = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_hora_do_dia, 'Amanhecer', 'Luz suave', 'amanhecer com céu alaranjado', 'amanhecer', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_hora_do_dia, 'Manhã', 'Luz clara', 'manhã com iluminação natural', 'manha', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_hora_do_dia, 'Meio‑dia', 'Luz dura', 'meio‑dia com sombras curtas', 'meio_dia', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_hora_do_dia, 'Tarde', 'Luz quente', 'tarde dourada', 'tarde', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_hora_do_dia, 'Pôr do sol', 'Golden hour', 'pôr do sol intenso no horizonte', 'por_do_sol', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_hora_do_dia, 'Crepúsculo', 'Luz azul', 'céu azulado do blue hour', 'crepusculo', 6, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_hora_do_dia, 'Noite', 'Baixa luz', 'noite com iluminação artificial', 'noite', 7, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_hora_do_dia, 'Madrugada', 'Silêncio', 'madrugada com ruas vazias', 'madrugada', 8, 1);

-- Bloco: Estações do Ano
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Estações do Ano', 'calendar_month', 'cena_ambiente', 9, 1);
SET @blk_estações_do_ano = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_estações_do_ano, 'Primavera', 'Flores e cores', 'primavera com flores em destaque', 'primavera', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_estações_do_ano, 'Verão', 'Calor e luz', 'verão com sol intenso e céu azul', 'verao', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_estações_do_ano, 'Outono', 'Folhas douradas', 'outono com folhas avermelhadas', 'outono', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_estações_do_ano, 'Inverno', 'Frio', 'inverno com atmosfera fria e limpa', 'inverno', 4, 1);

-- Bloco: Corpos d’Água
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Corpos d’Água', 'water', 'cena_ambiente', 10, 1);
SET @blk_corpos_dágua = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_corpos_dágua, 'Oceano', 'Imensidão', 'oceano aberto com ondas longas', 'oceano', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_corpos_dágua, 'Mar', 'Costeiro', 'mar com falésias e espuma', 'mar', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_corpos_dágua, 'Rio', 'Curso d’água', 'rio sinuoso entre margens verdes', 'rio', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_corpos_dágua, 'Lagoa', 'Águas calmas', 'lagoa espelhada ao entardecer', 'lagoa', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_corpos_dágua, 'Cachoeira', 'Queda', 'cachoeira com arco‑íris na névoa', 'cachoeira_arcoiris', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_corpos_dágua, 'Mangue', 'Costeiro', 'mangue com raízes entrelaçadas', 'mangue', 6, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_corpos_dágua, 'Recife de Coral', 'Subaquático', 'recife de coral colorido com peixes', 'recife_coral', 7, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_corpos_dágua, 'Geleira Marinha', 'Polar', 'geleira encontrando o mar', 'geleira_marinha', 8, 1);

-- Bloco: Topografia & Relevo
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Topografia & Relevo', 'terrain', 'cena_ambiente', 11, 1);
SET @blk_topografia_relevo = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_topografia_relevo, 'Vale', 'Entre montanhas', 'vale verdejante com rio ao centro', 'vale', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_topografia_relevo, 'Planície', 'Terreno plano', 'planície ampla até o horizonte', 'planicie', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_topografia_relevo, 'Planalto', 'Altitude', 'planalto rochoso elevado', 'planalto', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_topografia_relevo, 'Penhasco', 'Borda abrupta', 'penhasco dramático sobre o mar', 'penhasco', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_topografia_relevo, 'Dunas', 'Areia', 'dunas onduladas ao vento', 'dunas', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_topografia_relevo, 'Falésia', 'Costeira', 'falésia branca sobre o mar', 'falesia', 6, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_topografia_relevo, 'Ravina', 'Erosão', 'ravina estreita e profunda', 'ravina', 7, 1);

-- Bloco: Rural & Agro
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Rural & Agro', 'agriculture', 'cena_ambiente', 12, 1);
SET @blk_rural_agro = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_rural_agro, 'Fazenda', 'Campo', 'fazenda com cercas e pasto', 'fazenda', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_rural_agro, 'Vinhedo', 'Vinhas', 'vinhedo ao pôr do sol', 'vinhedo', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_rural_agro, 'Plantação de Trigo', 'Cereal', 'campo de trigo dourado ao vento', 'trigo', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_rural_agro, 'Plantação de Café', 'Aromas', 'talhões de café em morros', 'cafe', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_rural_agro, 'Celeiro', 'Armazenamento', 'celeiro vermelho clássico', 'celeiro', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_rural_agro, 'Silo', 'Grãos', 'silo graneleiro metálico', 'silo', 6, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_rural_agro, 'Moinho de Vento', 'Icônico', 'moinho de vento em colina', 'moinho_vento', 7, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_rural_agro, 'Curral', 'Gado', 'curral de madeira com rebanho', 'curral', 8, 1);

-- Bloco: Industrial & Infra
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Industrial & Infra', 'factory', 'cena_ambiente', 13, 1);
SET @blk_industrial_infra = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_industrial_infra, 'Usina Elétrica', 'Energia', 'usina com torres e cabos', 'usina_eletrica', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_industrial_infra, 'Fábrica', 'Produção', 'linha de produção em operação', 'fabrica', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_industrial_infra, 'Refinaria', 'Petróleo', 'refinaria com tubulações e tochas', 'refinaria', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_industrial_infra, 'Aeroporto', 'Aviação', 'aeroporto com pistas e aviões', 'aeroporto', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_industrial_infra, 'Rodoviária', 'Ônibus', 'terminal rodoviário movimentado', 'rodoviaria', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_industrial_infra, 'Estaleiro', 'Naval', 'estaleiro com navios em construção', 'estaleiro', 6, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_industrial_infra, 'Pedreira', 'Mineração', 'pedreira com cortes em rocha', 'pedreira', 7, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_industrial_infra, 'Barragem', 'Hídrica', 'barragem e reservatório', 'barragem', 8, 1);

-- Bloco: Comercial & Varejo
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Comercial & Varejo', 'storefront', 'cena_ambiente', 14, 1);
SET @blk_comercial_varejo = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_comercial_varejo, 'Loja de Rua', 'Vitrine', 'loja de rua com vitrine iluminada', 'loja_rua', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_comercial_varejo, 'Shopping Center', 'Centro comercial', 'shopping moderno com claraboia', 'shopping_center', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_comercial_varejo, 'Mercado Municipal', 'Alimentos', 'mercado interno com bancas', 'mercado_municipal', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_comercial_varejo, 'Feira Livre', 'Ar livre', 'feira livre com barracas coloridas', 'feira_livre', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_comercial_varejo, 'Restaurante', 'Gastronomia', 'restaurante elegante com mesas postas', 'restaurante_comercial', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_comercial_varejo, 'Cafeteria', 'Café', 'cafeteria aconchegante', 'cafeteria_comercial', 6, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_comercial_varejo, 'Livraria', 'Cultura', 'livraria com estantes altas', 'livraria', 7, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_comercial_varejo, 'Floricultura', 'Flores', 'floricultura perfumada', 'floricultura', 8, 1);

-- Bloco: Cultura & Religião
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Cultura & Religião', 'temple_buddhist', 'cena_ambiente', 15, 1);
SET @blk_cultura_religião = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_cultura_religião, 'Igreja', 'Cristã', 'igreja histórica com vitrais', 'igreja', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_cultura_religião, 'Templo Budista', 'Ásia', 'templo budista com pagode', 'templo_budista', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_cultura_religião, 'Templo Hindu', 'Índia', 'templo hindu com esculturas ricas', 'templo_hindu', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_cultura_religião, 'Mesquita', 'Islâmica', 'mesquita com minaretes e pátio', 'mesquita', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_cultura_religião, 'Sinagoga', 'Judaica', 'sinagoga com cúpula e arca', 'sinagoga', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_cultura_religião, 'Teatro', 'Artes cênicas', 'teatro com palco e cortinas', 'teatro', 6, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_cultura_religião, 'Cinema', 'Exibição', 'sala de cinema com tela grande', 'cinema', 7, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_cultura_religião, 'Estádio', 'Esportes', 'estádio lotado sob refletores', 'estadio', 8, 1);

-- Bloco: Parques & Lazer
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Parques & Lazer', 'stadium', 'cena_ambiente', 16, 1);
SET @blk_parques_lazer = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_parques_lazer, 'Parque Urbano', 'Verde', 'parque urbano com trilhas e lago', 'parque_urbano', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_parques_lazer, 'Parque de Diversões', 'Brinquedos', 'parque de diversões com roda‑gigante', 'parque_diversoes', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_parques_lazer, 'Parque Aquático', 'Água', 'parque aquático com toboáguas', 'parque_aquatico', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_parques_lazer, 'Zoológico', 'Animais', 'zoológico com recintos naturais', 'zoologico', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_parques_lazer, 'Aquário', 'Marinho', 'aquário com grandes tanques', 'aquario', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_parques_lazer, 'Praia Urbana', 'Lazer', 'praia integrada à cidade', 'praia_urbana', 6, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_parques_lazer, 'Trilha', 'Aventura', 'trilha ecológica na mata', 'trilha', 7, 1);

-- Bloco: Subaquático
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Subaquático', 'scuba_diving', 'cena_ambiente', 17, 1);
SET @blk_subaquático = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_subaquático, 'Recife de Coral', 'Vida marinha', 'recife de coral colorido', 'recife_coral_sub', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_subaquático, 'Naufrágio', 'Histórico', 'naufrágio coberto por algas', 'naufragio', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_subaquático, 'Caverna Subaquática', 'Exploração', 'caverna submersa com feixes de luz', 'caverna_subaquatica', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_subaquático, 'Mar Aberto', 'Profundo', 'mar aberto com cardumes ao fundo', 'mar_aberto', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_subaquático, 'Fundo Arenoso', 'Raso', 'fundo arenoso com conchas', 'fundo_arenoso', 5, 1);

-- Bloco: Subterrâneo
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Subterrâneo', 'downhill_skiing', 'cena_ambiente', 18, 1);
SET @blk_subterrâneo = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_subterrâneo, 'Metrô', 'Túneis', 'túneis de metrô com iluminação esparsa', 'subterraneo_metro', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_subterrâneo, 'Mina', 'Extração', 'mina com trilhos e vagões', 'mina', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_subterrâneo, 'Catacumbas', 'Antigo', 'catacumbas estreitas com arcos', 'catacumbas', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_subterrâneo, 'Bunker', 'Abrigo', 'bunker de concreto com portas pesadas', 'bunker', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_subterrâneo, 'Caverna Calcária', 'Formações', 'caverna com estalactites e estalagmites', 'caverna_calcaria', 5, 1);

-- Bloco: Residencial
INSERT INTO blocos (titulo, icone, tipo_aba, ordem_exibicao, ativo) VALUES ('Residencial', 'holiday_village', 'cena_ambiente', 19, 1);
SET @blk_residencial = LAST_INSERT_ID();
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_residencial, 'Apartamento Moderno', 'Urbano', 'apartamento moderno com janelas amplas', 'apartamento_moderno', 1, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_residencial, 'Loft Industrial', 'Contemporâneo', 'loft com tijolo aparente e metal', 'loft_industrial', 2, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_residencial, 'Casa de Campo', 'Rural', 'casa de campo com varanda', 'casa_campo', 3, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_residencial, 'Casa de Praia', 'Litoral', 'casa de praia com deck de madeira', 'casa_praia', 4, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_residencial, 'Cabana na Floresta', 'Rústico', 'cabana de madeira entre pinheiros', 'cabana_floresta', 5, 1);
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES (@blk_residencial, 'Chalé Alpino', 'Montanha', 'chalé com telhado íngreme e neve', 'chale_alpino', 6, 1);

COMMIT;