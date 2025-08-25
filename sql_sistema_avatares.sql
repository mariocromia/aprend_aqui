-- =====================================
-- SISTEMA COMPLETO DE AVATARES
-- Criação das tabelas para gerenciamento de avatares
-- =====================================

-- 1. Tabela principal de avatares
CREATE TABLE IF NOT EXISTS avatares (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    genero VARCHAR(20) CHECK (genero IN ('masculino', 'feminino', 'neutro', 'outro')),
    idade_categoria VARCHAR(20) CHECK (idade_categoria IN ('crianca', 'adolescente', 'jovem', 'adulto', 'idoso')),
    etnia VARCHAR(50),
    tipo_fisico VARCHAR(50),
    altura VARCHAR(20),
    peso VARCHAR(20),
    cor_cabelo VARCHAR(30),
    estilo_cabelo VARCHAR(50),
    cor_olhos VARCHAR(30),
    cor_pele VARCHAR(30),
    expressao_facial VARCHAR(50),
    postura VARCHAR(50),
    profissao VARCHAR(100),
    personalidade TEXT,
    vestuario TEXT,
    acessorios TEXT,
    tatuagens_marcas TEXT,
    habilidades_especiais TEXT,
    historia_background TEXT,
    tags TEXT[], -- Array de tags para facilitar busca
    ativo BOOLEAN DEFAULT true,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    criado_por INTEGER REFERENCES usuarios(id),
    publico BOOLEAN DEFAULT false, -- Se outros usuários podem usar
    visualizacoes INTEGER DEFAULT 0,
    uso_count INTEGER DEFAULT 0
);

-- 2. Tabela de categorias de avatares
CREATE TABLE IF NOT EXISTS categorias_avatares (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    icone VARCHAR(50),
    cor VARCHAR(20),
    ordem_exibicao INTEGER DEFAULT 0,
    ativo BOOLEAN DEFAULT true,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabela de relacionamento avatar-categoria (muitos para muitos)
CREATE TABLE IF NOT EXISTS avatar_categorias (
    id SERIAL PRIMARY KEY,
    avatar_id INTEGER REFERENCES avatares(id) ON DELETE CASCADE,
    categoria_id INTEGER REFERENCES categorias_avatares(id) ON DELETE CASCADE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(avatar_id, categoria_id)
);

-- 4. Tabela de características físicas detalhadas
CREATE TABLE IF NOT EXISTS caracteristicas_fisicas (
    id SERIAL PRIMARY KEY,
    avatar_id INTEGER REFERENCES avatares(id) ON DELETE CASCADE,
    tipo VARCHAR(50) NOT NULL, -- 'rosto', 'corpo', 'cabelo', 'olhos', etc.
    caracteristica VARCHAR(100) NOT NULL,
    valor TEXT,
    descricao TEXT,
    ordem_exibicao INTEGER DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Tabela de roupas e acessórios
CREATE TABLE IF NOT EXISTS avatar_vestimentas (
    id SERIAL PRIMARY KEY,
    avatar_id INTEGER REFERENCES avatares(id) ON DELETE CASCADE,
    tipo VARCHAR(50) NOT NULL, -- 'roupa_superior', 'roupa_inferior', 'calcado', 'acessorio', etc.
    item VARCHAR(100) NOT NULL,
    cor VARCHAR(50),
    material VARCHAR(50),
    estilo VARCHAR(50),
    descricao TEXT,
    ordem_exibicao INTEGER DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Tabela de presets/templates de avatares
CREATE TABLE IF NOT EXISTS avatar_presets (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    categoria VARCHAR(50),
    configuracao JSON NOT NULL, -- Configuração completa do avatar em JSON
    preview_data JSON, -- Dados para preview rápido
    popularidade INTEGER DEFAULT 0,
    ativo BOOLEAN DEFAULT true,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    criado_por INTEGER REFERENCES usuarios(id)
);

-- 7. Tabela de histórico de uso de avatares
CREATE TABLE IF NOT EXISTS avatar_historico (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id) ON DELETE CASCADE,
    avatar_id INTEGER REFERENCES avatares(id) ON DELETE CASCADE,
    configuracao_usada JSON,
    prompt_gerado TEXT,
    usado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================
-- INSERÇÃO DE DADOS INICIAIS
-- =====================================

-- Categorias básicas de avatares
INSERT INTO categorias_avatares (nome, descricao, icone, cor, ordem_exibicao) VALUES
('Realista', 'Avatares com aparência humana realista', 'person', '#3b82f6', 1),
('Fantasia', 'Criaturas mágicas e seres fantásticos', 'auto_fix_high', '#8b5cf6', 2),
('Anime/Manga', 'Estilo japonês de animação', 'animation', '#ec4899', 3),
('Cartoon', 'Estilo cartoon e animado', 'face', '#f59e0b', 4),
('Histórico', 'Personagens de épocas históricas', 'history', '#10b981', 5),
('Profissional', 'Avatares para contextos profissionais', 'work', '#6b7280', 6),
('Esportivo', 'Atletas e personagens esportivos', 'sports', '#ef4444', 7),
('Futurista', 'Personagens de ficção científica', 'rocket_launch', '#06b6d4', 8);

-- Alguns avatares de exemplo
INSERT INTO avatares (nome, descricao, genero, idade_categoria, etnia, tipo_fisico, altura, peso, cor_cabelo, cor_olhos, profissao, personalidade, publico, tags) VALUES
('Executiva Moderna', 'Mulher de negócios confiante e determinada', 'feminino', 'adulto', 'caucasiana', 'atletico', '1.70m', '65kg', 'castanho', 'castanho', 'Executiva', 'Confiante, determinada, líder natural', true, ARRAY['negocios', 'lideranca', 'profissional']),
('Desenvolvedor Tech', 'Jovem programador apaixonado por tecnologia', 'masculino', 'jovem', 'asiatico', 'magro', '1.75m', '70kg', 'preto', 'castanho', 'Desenvolvedor', 'Criativo, analítico, inovador', true, ARRAY['tecnologia', 'programacao', 'inovacao']),
('Artista Boêmia', 'Pintora livre e expressiva', 'feminino', 'jovem', 'latina', 'esbelto', '1.65m', '55kg', 'ruivo', 'verde', 'Artista', 'Criativa, livre, expressiva', true, ARRAY['arte', 'criatividade', 'bohemia']),
('Guerreiro Élfico', 'Elfo guerreiro com habilidades mágicas', 'masculino', 'adulto', 'elfico', 'atletico', '1.85m', '75kg', 'loiro_platinado', 'azul', 'Guerreiro', 'Nobre, corajoso, protetor', true, ARRAY['fantasia', 'magia', 'combate']);

-- Características físicas de exemplo
INSERT INTO caracteristicas_fisicas (avatar_id, tipo, caracteristica, valor, descricao) VALUES
(1, 'rosto', 'formato', 'oval', 'Rosto de formato oval clássico'),
(1, 'rosto', 'sobrancelhas', 'arqueadas', 'Sobrancelhas bem definidas e arqueadas'),
(1, 'corpo', 'postura', 'ereta', 'Postura confiante e ereta'),
(2, 'rosto', 'oculos', 'sim', 'Óculos de armação moderna'),
(2, 'cabelo', 'estilo', 'moderno_lateral', 'Corte moderno com risco lateral'),
(3, 'cabelo', 'comprimento', 'medio_ondulado', 'Cabelo médio com ondas naturais'),
(3, 'rosto', 'sardas', 'sim', 'Sardas suaves no nariz e bochechas'),
(4, 'orelhas', 'formato', 'pontudas', 'Orelhas élficas pontudas'),
(4, 'olhos', 'brilho', 'magico', 'Olhos com brilho mágico sutil');

-- Vestimentas de exemplo
INSERT INTO avatar_vestimentas (avatar_id, tipo, item, cor, material, estilo, descricao) VALUES
(1, 'roupa_superior', 'blazer', 'azul_marinho', 'alfaiataria', 'executivo', 'Blazer alfaiataria azul marinho'),
(1, 'roupa_inferior', 'calca_social', 'azul_marinho', 'alfaiataria', 'executivo', 'Calça social combinando'),
(1, 'calcado', 'sapato_salto', 'preto', 'couro', 'executivo', 'Sapato de salto médio preto'),
(2, 'roupa_superior', 'camiseta', 'preto', 'algodao', 'casual', 'Camiseta preta com logo tech'),
(2, 'roupa_inferior', 'jeans', 'azul_escuro', 'denim', 'casual', 'Jeans azul escuro'),
(2, 'calcado', 'tenis', 'branco', 'sintetico', 'esportivo', 'Tênis branco moderno'),
(3, 'roupa_superior', 'blusa_fluida', 'terra', 'viscose', 'bohemio', 'Blusa fluida em tons terrosos'),
(3, 'acessorio', 'colar_artesanal', 'dourado', 'metal', 'bohemio', 'Colar artesanal dourado'),
(4, 'armadura', 'peitoral_elfico', 'prata', 'mithril', 'fantasia', 'Armadura élfica de mithril'),
(4, 'acessorio', 'capa', 'verde_floresta', 'tecido_magico', 'fantasia', 'Capa mágica verde floresta');

-- Presets populares
INSERT INTO avatar_presets (nome, descricao, categoria, configuracao, preview_data, popularidade) VALUES
('CEO Confiante', 'Preset para executivo(a) de alto nível', 'Profissional', 
 '{"genero": "neutro", "idade": "adulto", "vestuario": "executivo", "postura": "confiante"}',
 '{"preview": "Executivo(a) em traje formal, postura confiante"}', 150),
('Mago Arcano', 'Preset para personagem mágico poderoso', 'Fantasia',
 '{"genero": "neutro", "idade": "adulto", "vestuario": "robes_magicos", "habilidades": "magia_arcana"}',
 '{"preview": "Mago com robes místicos e aura mágica"}', 200),
('Streamer Gamer', 'Preset para content creator de games', 'Moderno',
 '{"genero": "neutro", "idade": "jovem", "vestuario": "casual_tech", "ambiente": "setup_gamer"}',
 '{"preview": "Jovem em setup gamer com headset"}', 180);

-- =====================================
-- ÍNDICES PARA PERFORMANCE
-- =====================================

CREATE INDEX idx_avatares_usuario ON avatares(criado_por);
CREATE INDEX idx_avatares_ativo ON avatares(ativo);
CREATE INDEX idx_avatares_publico ON avatares(publico);
CREATE INDEX idx_avatares_tags ON avatares USING GIN(tags);
CREATE INDEX idx_avatar_categorias_avatar ON avatar_categorias(avatar_id);
CREATE INDEX idx_avatar_categorias_categoria ON avatar_categorias(categoria_id);
CREATE INDEX idx_caracteristicas_avatar ON caracteristicas_fisicas(avatar_id);
CREATE INDEX idx_vestimentas_avatar ON avatar_vestimentas(avatar_id);
CREATE INDEX idx_historico_usuario ON avatar_historico(usuario_id);
CREATE INDEX idx_historico_avatar ON avatar_historico(avatar_id);

-- =====================================
-- TRIGGERS PARA ATUALIZAÇÃO AUTOMÁTICA
-- =====================================

-- Trigger para atualizar timestamp de atualização
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.atualizado_em = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_avatares_updated_at 
    BEFORE UPDATE ON avatares 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Trigger para incrementar contador de uso
CREATE OR REPLACE FUNCTION increment_avatar_usage()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE avatares 
    SET uso_count = uso_count + 1, visualizacoes = visualizacoes + 1
    WHERE id = NEW.avatar_id;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER increment_avatar_usage_trigger
    AFTER INSERT ON avatar_historico
    FOR EACH ROW EXECUTE FUNCTION increment_avatar_usage();

-- =====================================
-- VIEWS ÚTEIS
-- =====================================

-- View para avatares mais populares
CREATE OR REPLACE VIEW avatares_populares AS
SELECT 
    a.*,
    ac.nome as categoria_principal,
    a.uso_count + a.visualizacoes as popularidade_total
FROM avatares a
LEFT JOIN avatar_categorias ac_rel ON a.id = ac_rel.avatar_id
LEFT JOIN categorias_avatares ac ON ac_rel.categoria_id = ac.id
WHERE a.ativo = true AND a.publico = true
ORDER BY popularidade_total DESC;

-- View para avatares com todas as informações
CREATE OR REPLACE VIEW avatares_completos AS
SELECT 
    a.*,
    array_agg(DISTINCT ac.nome) as categorias,
    count(DISTINCT cf.id) as total_caracteristicas,
    count(DISTINCT av.id) as total_vestimentas
FROM avatares a
LEFT JOIN avatar_categorias ac_rel ON a.id = ac_rel.avatar_id
LEFT JOIN categorias_avatares ac ON ac_rel.categoria_id = ac.id
LEFT JOIN caracteristicas_fisicas cf ON a.id = cf.avatar_id
LEFT JOIN avatar_vestimentas av ON a.id = av.avatar_id
WHERE a.ativo = true
GROUP BY a.id;

-- =====================================
-- COMENTÁRIOS PARA DOCUMENTAÇÃO
-- =====================================

COMMENT ON TABLE avatares IS 'Tabela principal para armazenar avatares/personagens criados pelos usuários';
COMMENT ON TABLE categorias_avatares IS 'Categorias para organizar os tipos de avatares';
COMMENT ON TABLE avatar_categorias IS 'Relacionamento muitos-para-muitos entre avatares e categorias';
COMMENT ON TABLE caracteristicas_fisicas IS 'Características físicas detalhadas dos avatares';
COMMENT ON TABLE avatar_vestimentas IS 'Roupas, acessórios e vestimentas dos avatares';
COMMENT ON TABLE avatar_presets IS 'Templates pré-configurados para criação rápida de avatares';
COMMENT ON TABLE avatar_historico IS 'Histórico de uso dos avatares pelos usuários';

-- =====================================
-- FINALIZAÇÃO
-- =====================================

-- Verificar se tudo foi criado corretamente
SELECT 'Sistema de Avatares instalado com sucesso!' as status;
SELECT 'Tabelas criadas:' as info, count(*) as total_tabelas 
FROM information_schema.tables 
WHERE table_name LIKE '%avatar%' OR table_name = 'categorias_avatares';