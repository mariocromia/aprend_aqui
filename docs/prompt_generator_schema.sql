-- Schema para o novo Gerador de Prompts v2.0

-- Tabela de categorias de IA
CREATE TABLE IF NOT EXISTS public.ai_categories (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(20),
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de estilos artísticos
CREATE TABLE IF NOT EXISTS public.art_styles (
    id BIGSERIAL PRIMARY KEY,
    category_id BIGINT REFERENCES ai_categories(id),
    name VARCHAR(100) NOT NULL,
    description TEXT,
    prompt_prefix TEXT,
    prompt_suffix TEXT,
    example_image_url TEXT,
    tags TEXT[], -- Array de tags
    popularity INTEGER DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de aspectos/dimensões
CREATE TABLE IF NOT EXISTS public.aspect_ratios (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    ratio VARCHAR(20) NOT NULL, -- Ex: "16:9", "1:1", "4:3"
    width INTEGER,
    height INTEGER,
    description TEXT,
    category VARCHAR(50), -- "square", "landscape", "portrait", "ultrawide"
    popular BOOLEAN DEFAULT FALSE
);

-- Tabela de qualidade/modelos
CREATE TABLE IF NOT EXISTS public.quality_presets (
    id BIGSERIAL PRIMARY KEY,
    category_id BIGINT REFERENCES ai_categories(id),
    name VARCHAR(100) NOT NULL,
    settings JSONB, -- Configurações específicas do modelo
    prompt_additions TEXT,
    description TEXT,
    is_premium BOOLEAN DEFAULT FALSE
);

-- Tabela de histórico de prompts do usuário
CREATE TABLE IF NOT EXISTS public.user_prompts (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES usuarios(id),
    category_id BIGINT REFERENCES ai_categories(id),
    title VARCHAR(200),
    original_prompt TEXT NOT NULL,
    enhanced_prompt TEXT,
    settings JSONB, -- Todas as configurações usadas
    result_urls TEXT[], -- URLs dos resultados gerados
    rating INTEGER CHECK (rating >= 1 AND rating <= 5),
    is_favorite BOOLEAN DEFAULT FALSE,
    is_public BOOLEAN DEFAULT FALSE,
    tags TEXT[],
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de templates prontos
CREATE TABLE IF NOT EXISTS public.prompt_templates (
    id BIGSERIAL PRIMARY KEY,
    category_id BIGINT REFERENCES ai_categories(id),
    title VARCHAR(200) NOT NULL,
    description TEXT,
    template_text TEXT NOT NULL,
    variables JSONB, -- Variáveis que podem ser substituídas
    example_result_url TEXT,
    difficulty_level VARCHAR(20), -- "beginner", "intermediate", "advanced"
    usage_count INTEGER DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    created_by BIGINT REFERENCES usuarios(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabela de colaborações/compartilhamentos
CREATE TABLE IF NOT EXISTS public.prompt_shares (
    id BIGSERIAL PRIMARY KEY,
    prompt_id BIGINT REFERENCES user_prompts(id),
    shared_by BIGINT REFERENCES usuarios(id),
    share_token VARCHAR(100) UNIQUE NOT NULL,
    access_count INTEGER DEFAULT 0,
    expires_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Inserir dados iniciais para categorias de IA
INSERT INTO public.ai_categories (name, slug, description, icon, color) VALUES
('Stable Diffusion', 'stable-diffusion', 'Geração de imagens artísticas e realistas', 'fas fa-image', '#8B5CF6'),
('Midjourney', 'midjourney', 'Arte conceitual e designs criativos', 'fas fa-palette', '#F59E0B'),
('DALL-E', 'dalle', 'Criação de imagens únicas e imaginativas', 'fas fa-magic', '#EF4444'),
('Flux', 'flux', 'Imagens de alta qualidade e fotorrealismo', 'fas fa-bolt', '#10B981'),
('ChatGPT', 'chatgpt', 'Prompts para conversação e texto', 'fas fa-comments', '#3B82F6'),
('Claude', 'claude', 'Assistente para análise e escrita', 'fas fa-brain', '#6366F1'),
('Video AI (Sora/Runway)', 'video-ai', 'Geração de vídeos com IA', 'fas fa-video', '#EC4899'),
('Leonardo AI', 'leonardo', 'Arte digital e concept art', 'fas fa-paint-brush', '#14B8A6')
ON CONFLICT (slug) DO NOTHING;

-- Inserir estilos artísticos populares
INSERT INTO public.art_styles (category_id, name, description, prompt_prefix, tags) VALUES
(1, 'Fotorrealista', 'Estilo ultra-realista como fotografia', 'photorealistic, highly detailed, 8k resolution', ARRAY['realistic', 'photo', 'detailed']),
(1, 'Anime/Manga', 'Estilo japonês de animação', 'anime style, manga art, cel shading', ARRAY['anime', 'manga', 'japanese']),
(1, 'Arte Digital', 'Arte conceitual digital moderna', 'digital art, concept art, artstation trending', ARRAY['digital', 'concept', 'modern']),
(1, 'Pintura a Óleo', 'Estilo clássico de pintura', 'oil painting, classical art, renaissance style', ARRAY['classical', 'painting', 'traditional']),
(2, 'Fantasia Épica', 'Mundos fantásticos e épicos', 'epic fantasy, magical realm, cinematic lighting', ARRAY['fantasy', 'epic', 'magical']),
(2, 'Cyberpunk', 'Futuro distópico e tecnológico', 'cyberpunk style, neon lights, futuristic', ARRAY['cyberpunk', 'futuristic', 'neon']),
(2, 'Steampunk', 'Era vitoriana com tecnologia a vapor', 'steampunk aesthetic, brass and copper, victorian era', ARRAY['steampunk', 'victorian', 'vintage'])
ON CONFLICT DO NOTHING;

-- Inserir proporções populares
INSERT INTO public.aspect_ratios (name, ratio, width, height, description, category, popular) VALUES
('Quadrado', '1:1', 1024, 1024, 'Formato quadrado para redes sociais', 'square', true),
('Paisagem HD', '16:9', 1920, 1080, 'Formato widescreen padrão', 'landscape', true),
('Retrato', '9:16', 1080, 1920, 'Formato vertical para mobile', 'portrait', true),
('Cinema', '21:9', 2560, 1080, 'Formato cinematográfico ultrawide', 'ultrawide', false),
('Poster', '2:3', 1365, 2048, 'Formato de poster vertical', 'portrait', true),
('Banner', '3:1', 1500, 500, 'Banner horizontal para web', 'landscape', false)
ON CONFLICT DO NOTHING;

-- Habilitar RLS (Row Level Security)
ALTER TABLE public.ai_categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.art_styles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.aspect_ratios ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.quality_presets ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.user_prompts ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.prompt_templates ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.prompt_shares ENABLE ROW LEVEL SECURITY;

-- Políticas de acesso público para leitura de dados básicos
CREATE POLICY "Permitir leitura pública de categorias" ON public.ai_categories FOR SELECT USING (true);
CREATE POLICY "Permitir leitura pública de estilos" ON public.art_styles FOR SELECT USING (true);
CREATE POLICY "Permitir leitura pública de proporções" ON public.aspect_ratios FOR SELECT USING (true);
CREATE POLICY "Permitir leitura pública de presets" ON public.quality_presets FOR SELECT USING (true);
CREATE POLICY "Permitir leitura pública de templates" ON public.prompt_templates FOR SELECT USING (true);

-- Políticas para prompts do usuário (apenas o próprio usuário ou públicos)
CREATE POLICY "Usuários podem ver seus próprios prompts" ON public.user_prompts 
FOR SELECT USING (auth.uid()::text = user_id::text OR is_public = true);

CREATE POLICY "Usuários podem criar seus próprios prompts" ON public.user_prompts 
FOR INSERT WITH CHECK (auth.uid()::text = user_id::text);

CREATE POLICY "Usuários podem atualizar seus próprios prompts" ON public.user_prompts 
FOR UPDATE USING (auth.uid()::text = user_id::text);

-- Política para service role (acesso total)
CREATE POLICY "Service role acesso total" ON public.user_prompts 
FOR ALL USING (auth.role() = 'service_role');

-- Índices para performance
CREATE INDEX IF NOT EXISTS idx_user_prompts_user_id ON public.user_prompts(user_id);
CREATE INDEX IF NOT EXISTS idx_user_prompts_category ON public.user_prompts(category_id);
CREATE INDEX IF NOT EXISTS idx_user_prompts_created_at ON public.user_prompts(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_user_prompts_favorites ON public.user_prompts(user_id, is_favorite) WHERE is_favorite = true;
CREATE INDEX IF NOT EXISTS idx_art_styles_category ON public.art_styles(category_id);
CREATE INDEX IF NOT EXISTS idx_prompt_templates_category ON public.prompt_templates(category_id);