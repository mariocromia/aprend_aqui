-- Script SQL para PostgreSQL (Supabase) - Versão Funcional
-- Inserir dados completos de ambiente usando DO blocks

DO $$
DECLARE
    blk_natureza INTEGER;
    blk_urbano INTEGER;
    blk_interior INTEGER;
    blk_tematicos_historicos INTEGER;
    blk_futurista_scifi INTEGER;
BEGIN

-- Bloco: Natureza
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
VALUES ('Natureza', 'park', 'ambiente', 1, true) 
RETURNING id INTO blk_natureza;

INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES 
(blk_natureza, 'Floresta', 'Ambiente natural', 'floresta densa com árvores altas, luz filtrada entre as copas, vegetação exuberante', 'floresta', 1, true),
(blk_natureza, 'Praia', 'Costa marítima', 'praia tropical com areia branca e mar calmo, ondas suaves, céu azul', 'praia', 2, true),
(blk_natureza, 'Montanha', 'Paisagem montanhosa', 'montanhas majestosas com picos nevados, ar puro, paisagem imponente', 'montanha', 3, true),
(blk_natureza, 'Deserto', 'Ambiente árido', 'deserto vasto com dunas douradas, calor seco, horizonte infinito', 'deserto', 4, true),
(blk_natureza, 'Campo', 'Paisagem rural', 'campo verde com flores silvestres, brisa suave, tranquilidade rural', 'campo', 5, true),
(blk_natureza, 'Lago', 'Corpo d''água', 'lago cristalino cercado por natureza, reflexos na água, serenidade', 'lago', 6, true),
(blk_natureza, 'Cachoeira', 'Queda d''água', 'cachoeira alta com névoa suave, som da água caindo, frescor natural', 'cachoeira', 7, true),
(blk_natureza, 'Caverna', 'Ambiente subterrâneo', 'caverna com formações de estalactites, penumbra misteriosa, ecos naturais', 'caverna', 8, true),
(blk_natureza, 'Selva', 'Densa e úmida', 'selva tropical com dossel fechado, umidade intensa, vida selvagem', 'selva', 9, true),
(blk_natureza, 'Cânion', 'Formação rochosa', 'cânion profundo com paredes avermelhadas, erosão milenar, grandiosidade', 'canion', 10, true),
(blk_natureza, 'Tundra', 'Clima frio', 'tundra gelada com vegetação rasteira, vento frio, paisagem ártica', 'tundra', 11, true),
(blk_natureza, 'Manguezal', 'Costeiro', 'manguezal com raízes aéreas e água salobra, ecossistema único', 'manguezal', 12, true),
(blk_natureza, 'Pântano', 'Alagadiço', 'pântano enevoado com águas escuras, mistério e umidade', 'pantano', 13, true),
(blk_natureza, 'Savana', 'Tropical sazonal', 'savana com gramíneas altas e acácias, paisagem africana clássica', 'savana', 14, true);

-- Bloco: Urbano
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
VALUES ('Urbano', 'location_city', 'ambiente', 2, true) 
RETURNING id INTO blk_urbano;

INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES 
(blk_urbano, 'Cidade', 'Centro urbano', 'paisagem urbana com arranha-céus, vida metropolitana, luzes da cidade', 'cidade', 1, true),
(blk_urbano, 'Rua', 'Via urbana', 'rua movimentada com carros e pedestres, semáforos, vida urbana', 'rua', 2, true),
(blk_urbano, 'Praça', 'Espaço público', 'praça arborizada com bancos, fonte central, convívio social', 'praca', 3, true),
(blk_urbano, 'Metrô', 'Transporte público', 'estação de metrô com plataformas, movimento de pessoas, transporte urbano', 'metro', 4, true),
(blk_urbano, 'Rooftop', 'Cobertura', 'rooftop com vista panorâmica da cidade, terraço urbano, perspectiva elevada', 'rooftop', 5, true),
(blk_urbano, 'Beco Grafitado', 'Arte urbana', 'beco estreito com grafites coloridos, arte de rua, expressão urbana', 'beco_grafitado', 6, true),
(blk_urbano, 'Ponte', 'Infraestrutura', 'ponte icônica sobre um rio, arquitetura impressionante, conexão urbana', 'ponte', 7, true),
(blk_urbano, 'Mercado de Rua', 'Feira', 'mercado ao ar livre com barracas coloridas, comércio popular, vida local', 'mercado_rua', 8, true),
(blk_urbano, 'Porto', 'Zona portuária', 'porto com guindastes e contêineres, atividade marítima, comércio global', 'porto', 9, true),
(blk_urbano, 'Estação de Trem', 'Transporte', 'estação clássica com trilhos, locomotivas, viagem ferroviária', 'estacao_trem', 10, true),
(blk_urbano, 'Zona Industrial', 'Fábricas', 'parque industrial com chaminés, produção em massa, paisagem fabril', 'zona_industrial', 11, true),
(blk_urbano, 'Bairro Histórico', 'Patrimônio', 'ruas de pedra e fachadas antigas, arquitetura colonial, história preservada', 'bairro_historico', 12, true);

-- Bloco: Interior
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
VALUES ('Interior', 'home', 'ambiente', 3, true) 
RETURNING id INTO blk_interior;

INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES 
(blk_interior, 'Escritório', 'Ambiente corporativo', 'escritório moderno com mesas e monitores, ambiente profissional, produtividade', 'escritorio', 1, true),
(blk_interior, 'Casa', 'Residência', 'sala de estar aconchegante, móveis confortáveis, ambiente familiar', 'casa', 2, true),
(blk_interior, 'Cozinha', 'Culinária', 'cozinha gourmet bem iluminada, utensílios modernos, arte culinária', 'cozinha', 3, true),
(blk_interior, 'Quarto', 'Descanso', 'quarto minimalista com cama arrumada, decoração serena, relaxamento', 'quarto', 4, true),
(blk_interior, 'Sala de Aula', 'Educação', 'sala de aula com quadro e carteiras, ambiente educacional, aprendizado', 'sala_de_aula', 5, true),
(blk_interior, 'Laboratório', 'Pesquisa', 'laboratório com bancadas e vidrarias, pesquisa científica, descobertas', 'laboratorio', 6, true),
(blk_interior, 'Biblioteca', 'Estudo', 'biblioteca com prateleiras altas, conhecimento organizado, silêncio studioso', 'biblioteca', 7, true),
(blk_interior, 'Hospital', 'Saúde', 'corredor de hospital limpo, ambiente médico, cuidados de saúde', 'hospital', 8, true),
(blk_interior, 'Estúdio Fotográfico', 'Produção', 'estúdio com softboxes e fundo infinito, produção fotográfica, criatividade', 'estudio_fotografico', 9, true),
(blk_interior, 'Oficina', 'Mecânica/DIY', 'oficina com ferramentas e bancadas, trabalho manual, criação artesanal', 'oficina', 10, true),
(blk_interior, 'Restaurante', 'Gastronomia', 'restaurante elegante com mesas postas, experiência gastronômica, requinte', 'restaurante', 11, true),
(blk_interior, 'Cafeteria', 'Café', 'cafeteria artesanal com balcão de madeira, aroma de café, encontros sociais', 'cafeteria', 12, true),
(blk_interior, 'Museu', 'Cultura', 'museu com grandes salas expositivas, arte e história, contemplação cultural', 'museu', 13, true),
(blk_interior, 'Galeria de Arte', 'Exposição', 'galeria minimalista com quadros, expressão artística, apreciação estética', 'galeria_arte', 14, true),
(blk_interior, 'Academia', 'Treino', 'academia com equipamentos modernos, exercícios físicos, saúde e fitness', 'academia', 15, true);

-- Bloco: Temáticos Históricos
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
VALUES ('Temáticos Históricos', 'castle', 'ambiente', 4, true) 
RETURNING id INTO blk_tematicos_historicos;

INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES 
(blk_tematicos_historicos, 'Medieval', 'Período histórico', 'aldeia medieval com castelo ao fundo, arquitetura gótica, atmosfera histórica', 'medieval', 1, true),
(blk_tematicos_historicos, 'Renascentista', 'Arte e cultura', 'cidade renascentista com cúpulas, arte clássica, renascimento cultural', 'renascentista', 2, true),
(blk_tematicos_historicos, 'Vitoriano', 'Arquitetura clássica', 'rua vitoriana com postes de luz, elegância do século XIX, refinamento', 'vitoriano', 3, true),
(blk_tematicos_historicos, 'Barroco', 'Ornamentado', 'igreja barroca ricamente decorada, ornamentação exuberante, arte sacra', 'barroco', 4, true),
(blk_tematicos_historicos, 'Colonial', 'Américas', 'centro colonial com casarios coloridos, arquitetura portuguesa, história americana', 'colonial', 5, true),
(blk_tematicos_historicos, 'Faroeste', 'Velho Oeste', 'cidade do velho oeste com saloon, época dos cowboys, fronteira americana', 'faroeste', 6, true),
(blk_tematicos_historicos, 'Antiguidade Romana', 'Clássico', 'coliseu romano monumental, grandeza imperial, civilização antiga', 'antiguidade_romana', 7, true),
(blk_tematicos_historicos, 'Egito Antigo', 'Desértico', 'pirâmides e esfinge ao pôr do sol, mistérios do antigo Egito, monumentos milenares', 'egito_antigo', 8, true);

-- Bloco: Futurista & Sci-Fi
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
VALUES ('Futurista & Sci-Fi', 'rocket_launch', 'ambiente', 5, true) 
RETURNING id INTO blk_futurista_scifi;

INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES 
(blk_futurista_scifi, 'Cidade Futurista', 'Alta tecnologia', 'cidade futurista com neon e hologramas, tecnologia avançada, futuro urbano', 'cidade_futurista', 1, true),
(blk_futurista_scifi, 'Cyberpunk', 'Sci-fi urbano', 'megalópole cyberpunk sob chuva neon, realidade digital, futuro distópico', 'cyberpunk', 2, true),
(blk_futurista_scifi, 'Nave Espacial', 'Interior', 'interior de nave com janelas estelares, viagem espacial, tecnologia alienígena', 'nave_espacial', 3, true),
(blk_futurista_scifi, 'Estação Orbital', 'Exterior/Interior', 'estação espacial com anéis e docas, vida no espaço, civilização orbital', 'estacao_orbital', 4, true),
(blk_futurista_scifi, 'Hangar', 'Aeronaves', 'hangar hi-tech com naves atracadas, base espacial, frota intergaláctica', 'hangar_scifi', 5, true),
(blk_futurista_scifi, 'Mercado Alienígena', 'Extraterrestre', 'bazar alienígena com espécies diversas, comércio intergaláctico, culturas exóticas', 'mercado_alienigena', 6, true),
(blk_futurista_scifi, 'Laboratório Hi-tech', 'Pesquisa', 'laboratório futurista com telas holográficas, pesquisa avançada, inovação científica', 'laboratorio_hitech', 7, true),
(blk_futurista_scifi, 'Rua Neon', 'Noite', 'rua estreita iluminada por letreiros neon, vida noturna futurista, atmosfera cyberpunk', 'rua_neon', 8, true);

RAISE NOTICE 'Dados de ambiente inseridos com sucesso!';
RAISE NOTICE 'Total: 5 blocos, 57 cenas';

END $$;