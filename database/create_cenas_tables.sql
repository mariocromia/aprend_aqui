-- ========================================
-- SISTEMA DE CENAS - GERADOR DE PROMPT
-- ========================================

-- Tabela de Blocos de Cenas (Categorias principais)
CREATE TABLE IF NOT EXISTS blocos_cenas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    icone VARCHAR(50) NOT NULL,
    tipo_aba VARCHAR(50) NOT NULL, -- ambiente, iluminacao, avatar, camera, voz, acao
    ordem_exibicao INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Cenas (Subcategorias/Cards individuais)
CREATE TABLE IF NOT EXISTS cenas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bloco_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    subtitulo VARCHAR(200) DEFAULT NULL,
    texto_prompt TEXT NOT NULL, -- Texto que será inserido no prompt
    valor_selecao VARCHAR(100) NOT NULL, -- Valor único para identificação (ex: escritorio_moderno)
    ordem_exibicao INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Relacionamento com blocos_cenas
    FOREIGN KEY (bloco_id) REFERENCES blocos_cenas(id) ON DELETE CASCADE,
    
    -- Índices para performance
    INDEX idx_bloco_id (bloco_id),
    INDEX idx_tipo_valor (bloco_id, valor_selecao),
    INDEX idx_ativo_ordem (ativo, ordem_exibicao)
);

-- Comentários das tabelas
ALTER TABLE blocos_cenas COMMENT = 'Tabela de categorias principais (blocos) do gerador de prompt';
ALTER TABLE cenas COMMENT = 'Tabela de cenas individuais (cards) dentro de cada bloco';

-- Comentários das colunas - blocos_cenas
ALTER TABLE blocos_cenas 
    MODIFY COLUMN titulo VARCHAR(100) NOT NULL COMMENT 'Nome do bloco exibido na interface',
    MODIFY COLUMN icone VARCHAR(50) NOT NULL COMMENT 'Nome do ícone Material Icons',
    MODIFY COLUMN tipo_aba VARCHAR(50) NOT NULL COMMENT 'Tipo da aba: ambiente, iluminacao, avatar, camera, voz, acao',
    MODIFY COLUMN ordem_exibicao INT DEFAULT 0 COMMENT 'Ordem de exibição na interface',
    MODIFY COLUMN ativo BOOLEAN DEFAULT TRUE COMMENT 'Se o bloco está ativo/visível';

-- Comentários das colunas - cenas
ALTER TABLE cenas 
    MODIFY COLUMN bloco_id INT NOT NULL COMMENT 'ID do bloco pai',
    MODIFY COLUMN titulo VARCHAR(100) NOT NULL COMMENT 'Título principal do card',
    MODIFY COLUMN subtitulo VARCHAR(200) DEFAULT NULL COMMENT 'Subtítulo/descrição do card',
    MODIFY COLUMN texto_prompt TEXT NOT NULL COMMENT 'Texto que será inserido no prompt final',
    MODIFY COLUMN valor_selecao VARCHAR(100) NOT NULL COMMENT 'Valor único para identificação no JavaScript',
    MODIFY COLUMN ordem_exibicao INT DEFAULT 0 COMMENT 'Ordem de exibição dentro do bloco',
    MODIFY COLUMN ativo BOOLEAN DEFAULT TRUE COMMENT 'Se a cena está ativa/visível';