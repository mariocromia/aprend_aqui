-- ========================================
-- SISTEMA DE CENAS - CRIAÇÃO DAS TABELAS
-- Para Supabase/PostgreSQL
-- ========================================

-- Remover tabelas se existirem (cuidado em produção!)
DROP TABLE IF EXISTS cenas CASCADE;
DROP TABLE IF EXISTS blocos_cenas CASCADE;

-- ========================================
-- TABELA: blocos_cenas
-- Armazena as categorias principais (blocos) do gerador de prompt
-- ========================================

CREATE TABLE blocos_cenas (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    icone VARCHAR(50) NOT NULL,
    tipo_aba VARCHAR(50) NOT NULL CHECK (tipo_aba IN ('ambiente', 'iluminacao', 'avatar', 'camera', 'voz', 'acao')),
    ordem_exibicao INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Comentários da tabela blocos_cenas
COMMENT ON TABLE blocos_cenas IS 'Categorias principais (blocos) do gerador de prompt';
COMMENT ON COLUMN blocos_cenas.titulo IS 'Nome do bloco exibido na interface';
COMMENT ON COLUMN blocos_cenas.icone IS 'Nome do ícone Material Icons';
COMMENT ON COLUMN blocos_cenas.tipo_aba IS 'Tipo da aba: ambiente, iluminacao, avatar, camera, voz, acao';
COMMENT ON COLUMN blocos_cenas.ordem_exibicao IS 'Ordem de exibição na interface';
COMMENT ON COLUMN blocos_cenas.ativo IS 'Se o bloco está ativo/visível';

-- ========================================
-- TABELA: cenas
-- Armazena as cenas individuais (cards) dentro de cada bloco
-- ========================================

CREATE TABLE cenas (
    id SERIAL PRIMARY KEY,
    bloco_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    subtitulo VARCHAR(200) DEFAULT NULL,
    texto_prompt TEXT NOT NULL,
    valor_selecao VARCHAR(100) NOT NULL,
    ordem_exibicao INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    
    -- Chave estrangeira
    CONSTRAINT fk_cenas_bloco_id 
        FOREIGN KEY (bloco_id) 
        REFERENCES blocos_cenas(id) 
        ON DELETE CASCADE,
    
    -- Constraint para valor_selecao único
    CONSTRAINT uk_cenas_valor_selecao UNIQUE (valor_selecao)
);

-- Comentários da tabela cenas
COMMENT ON TABLE cenas IS 'Cenas individuais (cards) dentro de cada bloco';
COMMENT ON COLUMN cenas.bloco_id IS 'ID do bloco pai';
COMMENT ON COLUMN cenas.titulo IS 'Título principal do card';
COMMENT ON COLUMN cenas.subtitulo IS 'Subtítulo/descrição do card';
COMMENT ON COLUMN cenas.texto_prompt IS 'Texto que será inserido no prompt final';
COMMENT ON COLUMN cenas.valor_selecao IS 'Valor único para identificação no JavaScript';
COMMENT ON COLUMN cenas.ordem_exibicao IS 'Ordem de exibição dentro do bloco';
COMMENT ON COLUMN cenas.ativo IS 'Se a cena está ativa/visível';

-- ========================================
-- ÍNDICES PARA PERFORMANCE
-- ========================================

-- Índices para blocos_cenas
CREATE INDEX idx_blocos_tipo_aba ON blocos_cenas(tipo_aba);
CREATE INDEX idx_blocos_ativo_ordem ON blocos_cenas(ativo, ordem_exibicao);

-- Índices para cenas
CREATE INDEX idx_cenas_bloco_id ON cenas(bloco_id);
CREATE INDEX idx_cenas_valor_selecao ON cenas(valor_selecao);
CREATE INDEX idx_cenas_ativo_ordem ON cenas(ativo, ordem_exibicao);
CREATE INDEX idx_cenas_bloco_ativo_ordem ON cenas(bloco_id, ativo, ordem_exibicao);

-- ========================================
-- CONFIGURAÇÕES DE SEGURANÇA (RLS)
-- ========================================

-- Habilitar Row Level Security
ALTER TABLE blocos_cenas ENABLE ROW LEVEL SECURITY;
ALTER TABLE cenas ENABLE ROW LEVEL SECURITY;

-- Políticas para permitir acesso completo (ajuste conforme necessário)
CREATE POLICY "Permitir todas operações blocos_cenas" ON blocos_cenas
    FOR ALL 
    USING (true)
    WITH CHECK (true);

CREATE POLICY "Permitir todas operações cenas" ON cenas
    FOR ALL 
    USING (true)
    WITH CHECK (true);

-- ========================================
-- FUNÇÃO PARA ATUALIZAR updated_at
-- ========================================

-- Função para atualizar automaticamente o campo updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggers para atualizar updated_at automaticamente
CREATE TRIGGER update_blocos_cenas_updated_at 
    BEFORE UPDATE ON blocos_cenas 
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_cenas_updated_at 
    BEFORE UPDATE ON cenas 
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();

-- ========================================
-- DADOS INICIAIS - BLOCOS DE CENAS
-- ========================================

INSERT INTO blocos_cenas (titulo, icone, tipo_aba, ordem_exibicao) VALUES
-- ABA AMBIENTE
('Natureza', 'nature', 'ambiente', 1),
('Urbano', 'location_city', 'ambiente', 2),
('Interior', 'home', 'ambiente', 3),
('Fantasia', 'auto_fix_high', 'ambiente', 4),
('Futurista', 'rocket_launch', 'ambiente', 5),

-- ABA ILUMINAÇÃO
('Natural', 'wb_sunny', 'iluminacao', 1),
('Artificial', 'lightbulb', 'iluminacao', 2),
('Dramática', 'theater_comedy', 'iluminacao', 3),
('Especial', 'auto_fix_high', 'iluminacao', 4),
('Ambiente', 'nights_stay', 'iluminacao', 5),

-- ABA AVATAR/PERSONAGEM
('Humanos', 'person', 'avatar', 1),
('Profissões', 'work', 'avatar', 2),
('Fantasia', 'auto_fix_high', 'avatar', 3),
('Animais', 'pets', 'avatar', 4),
('Personalizados', 'face', 'avatar', 5),

-- ABA CÂMERA
('Ângulos', 'photo_camera', 'camera', 1),
('Distâncias', 'zoom_in', 'camera', 2),
('Movimentos', 'videocam', 'camera', 3),
('Estilos', 'camera_alt', 'camera', 4),
('Especiais', 'movie_creation', 'camera', 5),

-- ABA VOZ
('Tons', 'record_voice_over', 'voz', 1),
('Estilos', 'psychology', 'voz', 2),

-- ABA AÇÃO
('Ações Corporais', 'directions_run', 'acao', 1),
('Expressões', 'sentiment_satisfied', 'acao', 2),
('Gestos', 'pan_tool', 'acao', 3),
('Interações', 'handshake', 'acao', 4),
('Dinâmicos', 'speed', 'acao', 5);

-- ========================================
-- DADOS INICIAIS - CENAS (EXEMPLOS)
-- ========================================

-- AMBIENTE - NATUREZA (bloco_id = 1)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(1, 'Floresta', 'Ambiente natural', 'floresta densa com árvores altas', 'floresta', 1),
(1, 'Praia', 'Costa marítima', 'praia tropical com areia branca', 'praia', 2),
(1, 'Montanha', 'Paisagem montanhosa', 'montanha majestosa com picos nevados', 'montanha', 3),
(1, 'Deserto', 'Ambiente árido', 'deserto vasto com dunas douradas', 'deserto', 4),
(1, 'Campo', 'Paisagem rural', 'campo verde com flores silvestres', 'campo', 5),
(1, 'Lago', 'Corpo d''água', 'lago cristalino cercado por natureza', 'lago', 6);

-- AMBIENTE - URBANO (bloco_id = 2)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(2, 'Cidade', 'Centro urbano', 'cidade moderna com arranha-céus', 'cidade', 1),
(2, 'Rua', 'Via urbana', 'rua movimentada com pedestres', 'rua', 2),
(2, 'Praça', 'Espaço público', 'praça urbana com fontes e bancos', 'praca', 3),
(2, 'Shopping', 'Centro comercial', 'shopping center moderno', 'shopping', 4),
(2, 'Estação', 'Terminal de transporte', 'estação de trem movimentada', 'estacao', 5),
(2, 'Ponte', 'Estrutura urbana', 'ponte moderna sobre rio urbano', 'ponte', 6);

-- AMBIENTE - INTERIOR (bloco_id = 3)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(3, 'Escritório', 'Ambiente corporativo', 'escritório moderno com tecnologia', 'escritorio', 1),
(3, 'Casa', 'Residência', 'casa aconchegante e familiar', 'casa', 2),
(3, 'Escola', 'Ambiente educacional', 'sala de aula moderna', 'escola', 3),
(3, 'Hospital', 'Ambiente médico', 'hospital limpo e organizado', 'hospital', 4),
(3, 'Restaurante', 'Estabelecimento gastronômico', 'restaurante elegante', 'restaurante', 5),
(3, 'Biblioteca', 'Ambiente de estudos', 'biblioteca silenciosa com livros', 'biblioteca', 6);

-- ILUMINAÇÃO - NATURAL (bloco_id = 6)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(6, 'Luz Solar', 'Iluminação diurna', 'luz solar brilhante e natural', 'luz_solar', 1),
(6, 'Pôr do Sol', 'Luz dourada', 'luz dourada do pôr do sol', 'por_do_sol', 2),
(6, 'Nascer do Sol', 'Luz matinal', 'luz suave do nascer do sol', 'nascer_do_sol', 3),
(6, 'Luz da Lua', 'Iluminação noturna', 'luz prateada da lua cheia', 'luz_da_lua', 4),
(6, 'Luz Difusa', 'Iluminação suave', 'luz difusa através das nuvens', 'luz_difusa', 5),
(6, 'Contraluz', 'Efeito dramático', 'contraluz criando silhuetas', 'contraluz', 6);

-- VOZ - TONS (bloco_id = 21)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(21, 'Grave Masculina', 'Tom profundo', 'voz masculina grave e profunda', 'voz_grave_masculina', 1),
(21, 'Suave Feminina', 'Tom delicado', 'voz feminina suave e melodiosa', 'voz_suave_feminina', 2),
(21, 'Energética', 'Tom vibrante', 'voz energética e animada', 'voz_energetica', 3),
(21, 'Calma', 'Tom tranquilo', 'voz calma e relaxante', 'voz_calma', 4),
(21, 'Autoritária', 'Tom firme', 'voz autoritária e confiante', 'voz_autoritaria', 5),
(21, 'Jovem', 'Tom juvenil', 'voz jovem e dinâmica', 'voz_jovem', 6);

-- AÇÃO - AÇÕES CORPORAIS (bloco_id = 23)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(23, 'Correndo', 'Em movimento', 'correndo dinamicamente', 'correndo', 1),
(23, 'Caminhando', 'Movimento calmo', 'caminhando naturalmente', 'caminhando', 2),
(23, 'Saltando', 'Ação dinâmica', 'saltando energicamente', 'saltando', 3),
(23, 'Dançando', 'Movimento rítmico', 'dançando graciosamente', 'dancando', 4),
(23, 'Sentado', 'Posição estática', 'sentado confortavelmente', 'sentado', 5),
(23, 'Deitado', 'Posição relaxada', 'deitado relaxadamente', 'deitado', 6);

-- AÇÃO - EXPRESSÕES (bloco_id = 24)
INSERT INTO cenas (bloco_id, titulo, subtitulo, texto_prompt, valor_selecao, ordem_exibicao) VALUES
(24, 'Sorrindo', 'Expressão alegre', 'sorrindo alegremente', 'sorrindo', 1),
(24, 'Pensativo', 'Expressão reflexiva', 'com expressão pensativa', 'pensativo', 2),
(24, 'Surpreso', 'Expressão de surpresa', 'com expressão de surpresa', 'surpreso', 3),
(24, 'Concentrado', 'Foco intenso', 'concentrado intensamente', 'concentrado', 4),
(24, 'Conversando', 'Interação verbal', 'conversando animadamente', 'conversando', 5),
(24, 'Gritando', 'Expressão intensa', 'gritando expressivamente', 'gritando', 6);

-- ========================================
-- VERIFICAÇÕES FINAIS
-- ========================================

-- Verificar dados inseridos
SELECT 
    'Blocos criados' as tipo,
    COUNT(*) as total
FROM blocos_cenas
UNION ALL
SELECT 
    'Cenas criadas' as tipo,
    COUNT(*) as total
FROM cenas
UNION ALL
SELECT 
    'Blocos tipo: ' || tipo_aba as tipo,
    COUNT(*) as total
FROM blocos_cenas 
GROUP BY tipo_aba
ORDER BY tipo;

-- ========================================
-- INSTRUÇÕES FINAIS
-- ========================================

/*
✅ CRIAÇÃO CONCLUÍDA!

O que foi criado:
- 2 tabelas: blocos_cenas e cenas
- Relacionamento com chave estrangeira
- Índices para performance
- Políticas de segurança (RLS)
- Triggers para updated_at
- Dados iniciais de exemplo

Para usar:
1. Execute: php setup_cenas_database_fix.php (deve mostrar status 200)
2. Execute: php inserir_dados_completos.php (para mais dados)
3. Teste: php exemplo_uso_cenas.php

Para integrar:
- Use CenaRenderer::gerarAba() no lugar do HTML estático
- Use CenaManager para gerenciar dados

Total inserido:
- ~27 blocos distribuídos em 6 tipos de aba
- ~36 cenas de exemplo
- Sistema completo e funcional
*/