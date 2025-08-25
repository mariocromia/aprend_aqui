-- Script SQL para PostgreSQL (Supabase)
-- Inserir dados completos de ambiente
-- Baseado no arquivo cena.sql, corrigido para PostgreSQL

BEGIN;

-- Bloco: Natureza
INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao, ativo) 
VALUES ('Natureza', 'park', 'ambiente', 1, true) 
RETURNING id;

-- Inserir cenas para Natureza (usar o ID retornado acima)
-- Substitua {BLOCO_NATUREZA_ID} pelo ID real retornado
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao, ativo) VALUES 
({BLOCO_NATUREZA_ID}, 'Floresta', 'Ambiente natural', 'floresta densa com árvores altas, luz filtrada entre as copas, vegetação exuberante', 'floresta', 1, true),
({BLOCO_NATUREZA_ID}, 'Praia', 'Costa marítima', 'praia tropical com areia branca e mar calmo, ondas suaves, céu azul', 'praia', 2, true),
({BLOCO_NATUREZA_ID}, 'Montanha', 'Paisagem montanhosa', 'montanhas majestosas com picos nevados, ar puro, paisagem imponente', 'montanha', 3, true),
({BLOCO_NATUREZA_ID}, 'Deserto', 'Ambiente árido', 'deserto vasto com dunas douradas, calor seco, horizonte infinito', 'deserto', 4, true),
({BLOCO_NATUREZA_ID}, 'Campo', 'Paisagem rural', 'campo verde com flores silvestres, brisa suave, tranquilidade rural', 'campo', 5, true),
({BLOCO_NATUREZA_ID}, 'Lago', 'Corpo d''água', 'lago cristalino cercado por natureza, reflexos na água, serenidade', 'lago', 6, true),
({BLOCO_NATUREZA_ID}, 'Cachoeira', 'Queda d''água', 'cachoeira alta com névoa suave, som da água caindo, frescor natural', 'cachoeira', 7, true),
({BLOCO_NATUREZA_ID}, 'Caverna', 'Ambiente subterrâneo', 'caverna com formações de estalactites, penumbra misteriosa, ecos naturais', 'caverna', 8, true),
({BLOCO_NATUREZA_ID}, 'Selva', 'Densa e úmida', 'selva tropical com dossel fechado, umidade intensa, vida selvagem', 'selva', 9, true),
({BLOCO_NATUREZA_ID}, 'Cânion', 'Formação rochosa', 'cânion profundo com paredes avermelhadas, erosão milenar, grandiosidade', 'canion', 10, true),
({BLOCO_NATUREZA_ID}, 'Tundra', 'Clima frio', 'tundra gelada com vegetação rasteira, vento frio, paisagem ártica', 'tundra', 11, true),
({BLOCO_NATUREZA_ID}, 'Manguezal', 'Costeiro', 'manguezal com raízes aéreas e água salobra, ecossistema único', 'manguezal', 12, true),
({BLOCO_NATUREZA_ID}, 'Pântano', 'Alagadiço', 'pântano enevoado com águas escuras, mistério e umidade', 'pantano', 13, true),
({BLOCO_NATUREZA_ID}, 'Savana', 'Tropical sazonal', 'savana com gramíneas altas e acácias, paisagem africana clássica', 'savana', 14, true);

-- Continuar com outros blocos...
-- NOTA: Este script precisa ser executado manualmente substituindo os IDs

COMMIT;

-- INSTRUÇÕES:
-- 1. Execute cada INSERT INTO blocos_cenas individualmente
-- 2. Anote o ID retornado
-- 3. Substitua {BLOCO_X_ID} pelo ID real nos INSERTs das cenas
-- 4. Ou use o script PHP que faz isso automaticamente