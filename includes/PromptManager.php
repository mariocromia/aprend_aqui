<?php
/**
 * Gerenciador de Prompts - Sistema avançado de geração de prompts para IA
 */
require_once __DIR__ . '/SupabaseClient.php';

class PromptManager {
    private $supabase;
    
    public function __construct() {
        $this->supabase = new SupabaseClient();
    }
    
    /**
     * Obter todas as categorias de IA disponíveis
     */
    public function getAICategories() {
        try {
            $response = $this->supabase->makeRequest('ai_categories?active=eq.true&order=name', 'GET', null, true);
            
            if ($response['status'] === 200) {
                return $response['data'] ?? [];
            }
            
            // Fallback para desenvolvimento
            return $this->getFallbackCategories();
            
        } catch (Exception $e) {
            error_log("Erro ao buscar categorias: " . $e->getMessage());
            return $this->getFallbackCategories();
        }
    }
    
    /**
     * Obter estilos artísticos por categoria
     */
    public function getArtStyles($categoryId = null) {
        try {
            $endpoint = 'art_styles?active=eq.true&order=popularity.desc,name';
            if ($categoryId) {
                $endpoint .= '&category_id=eq.' . $categoryId;
            }
            
            $response = $this->supabase->makeRequest($endpoint, 'GET', null, true);
            
            if ($response['status'] === 200) {
                return $response['data'] ?? [];
            }
            
            return $this->getFallbackStyles($categoryId);
            
        } catch (Exception $e) {
            error_log("Erro ao buscar estilos: " . $e->getMessage());
            return $this->getFallbackStyles($categoryId);
        }
    }
    
    /**
     * Obter proporções/aspectos disponíveis
     */
    public function getAspectRatios() {
        try {
            $response = $this->supabase->makeRequest('aspect_ratios?order=popular.desc,name', 'GET', null, true);
            
            if ($response['status'] === 200) {
                return $response['data'] ?? [];
            }
            
            return $this->getFallbackAspectRatios();
            
        } catch (Exception $e) {
            error_log("Erro ao buscar proporções: " . $e->getMessage());
            return $this->getFallbackAspectRatios();
        }
    }
    
    /**
     * Salvar prompt do usuário
     */
    public function saveUserPrompt($userId, $data) {
        try {
            $promptData = [
                'user_id' => $userId,
                'category_id' => $data['category_id'] ?? null,
                'title' => $data['title'] ?? 'Prompt sem título',
                'original_prompt' => $data['original_prompt'],
                'enhanced_prompt' => $data['enhanced_prompt'] ?? null,
                'settings' => json_encode($data['settings'] ?? []),
                'tags' => $data['tags'] ?? [],
                'is_favorite' => $data['is_favorite'] ?? false,
                'created_at' => date('c')
            ];
            
            $response = $this->supabase->makeRequest('user_prompts', 'POST', $promptData, true);
            
            if ($response['status'] === 201 && !empty($response['data'])) {
                return $response['data'][0];
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao salvar prompt: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obter histórico de prompts do usuário
     */
    public function getUserPrompts($userId, $limit = 20, $offset = 0) {
        try {
            $endpoint = "user_prompts?user_id=eq.$userId&order=created_at.desc&limit=$limit&offset=$offset";
            $response = $this->supabase->makeRequest($endpoint, 'GET', null, true);
            
            if ($response['status'] === 200) {
                return $response['data'] ?? [];
            }
            
            return [];
            
        } catch (Exception $e) {
            error_log("Erro ao buscar prompts do usuário: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter templates de prompts
     */
    public function getPromptTemplates($categoryId = null, $difficulty = null) {
        try {
            $endpoint = 'prompt_templates?order=is_featured.desc,usage_count.desc,title';
            
            if ($categoryId) {
                $endpoint .= '&category_id=eq.' . $categoryId;
            }
            
            if ($difficulty) {
                $endpoint .= '&difficulty_level=eq.' . $difficulty;
            }
            
            $response = $this->supabase->makeRequest($endpoint, 'GET', null, true);
            
            if ($response['status'] === 200) {
                return $response['data'] ?? [];
            }
            
            return $this->getFallbackTemplates($categoryId);
            
        } catch (Exception $e) {
            error_log("Erro ao buscar templates: " . $e->getMessage());
            return $this->getFallbackTemplates($categoryId);
        }
    }
    
    /**
     * Gerar prompt melhorado com base nas configurações
     */
    public function enhancePrompt($originalPrompt, $settings) {
        $enhanced = $originalPrompt;
        
        // Adicionar estilo se selecionado
        if (!empty($settings['style_prefix'])) {
            $enhanced = $settings['style_prefix'] . ', ' . $enhanced;
        }
        
        // Adicionar qualidade
        if (!empty($settings['quality_settings'])) {
            $enhanced .= ', ' . $settings['quality_settings'];
        }
        
        // Adicionar proporção se for para imagem
        if (!empty($settings['aspect_ratio']) && !empty($settings['category_slug']) && 
            in_array($settings['category_slug'], ['stable-diffusion', 'midjourney', 'dalle', 'flux', 'leonardo'])) {
            $enhanced .= ' --ar ' . $settings['aspect_ratio'];
        }
        
        // Adicionar parâmetros específicos do Midjourney
        if (!empty($settings['category_slug']) && $settings['category_slug'] === 'midjourney') {
            if (!empty($settings['stylize'])) {
                $enhanced .= ' --s ' . $settings['stylize'];
            }
            if (!empty($settings['version'])) {
                $enhanced .= ' --v ' . $settings['version'];
            }
        }
        
        // Adicionar sufixo do estilo
        if (!empty($settings['style_suffix'])) {
            $enhanced .= ', ' . $settings['style_suffix'];
        }
        
        return trim($enhanced);
    }
    
    /**
     * Dados fallback para desenvolvimento
     */
    private function getFallbackCategories() {
        return [
            ['id' => 1, 'name' => 'Stable Diffusion', 'slug' => 'stable-diffusion', 'icon' => 'fas fa-image', 'color' => '#8B5CF6'],
            ['id' => 2, 'name' => 'Midjourney', 'slug' => 'midjourney', 'icon' => 'fas fa-palette', 'color' => '#F59E0B'],
            ['id' => 3, 'name' => 'DALL-E', 'slug' => 'dalle', 'icon' => 'fas fa-magic', 'color' => '#EF4444'],
            ['id' => 4, 'name' => 'ChatGPT', 'slug' => 'chatgpt', 'icon' => 'fas fa-comments', 'color' => '#3B82F6'],
            ['id' => 5, 'name' => 'Claude', 'slug' => 'claude', 'icon' => 'fas fa-brain', 'color' => '#6366F1'],
            ['id' => 6, 'name' => 'Video AI', 'slug' => 'video-ai', 'icon' => 'fas fa-video', 'color' => '#EC4899']
        ];
    }
    
    private function getFallbackStyles($categoryId = null) {
        $allStyles = [
            ['id' => 1, 'category_id' => 1, 'name' => 'Fotorrealista', 'prompt_prefix' => 'photorealistic, highly detailed, 8k resolution'],
            ['id' => 2, 'category_id' => 1, 'name' => 'Anime/Manga', 'prompt_prefix' => 'anime style, manga art, cel shading'],
            ['id' => 3, 'category_id' => 1, 'name' => 'Arte Digital', 'prompt_prefix' => 'digital art, concept art, artstation trending'],
            ['id' => 4, 'category_id' => 2, 'name' => 'Fantasia Épica', 'prompt_prefix' => 'epic fantasy, magical realm, cinematic lighting'],
            ['id' => 5, 'category_id' => 2, 'name' => 'Cyberpunk', 'prompt_prefix' => 'cyberpunk style, neon lights, futuristic']
        ];
        
        if ($categoryId) {
            return array_filter($allStyles, function($style) use ($categoryId) {
                return $style['category_id'] == $categoryId;
            });
        }
        
        return $allStyles;
    }
    
    private function getFallbackAspectRatios() {
        return [
            ['id' => 1, 'name' => 'Quadrado', 'ratio' => '1:1', 'popular' => true],
            ['id' => 2, 'name' => 'Paisagem HD', 'ratio' => '16:9', 'popular' => true],
            ['id' => 3, 'name' => 'Retrato', 'ratio' => '9:16', 'popular' => true],
            ['id' => 4, 'name' => 'Cinema', 'ratio' => '21:9', 'popular' => false],
            ['id' => 5, 'name' => 'Poster', 'ratio' => '2:3', 'popular' => true]
        ];
    }
    
    private function getFallbackTemplates($categoryId = null) {
        $allTemplates = [
            [
                'id' => 1,
                'category_id' => 1,
                'title' => 'Retrato Profissional',
                'template_text' => 'Professional headshot of {subject}, {lighting} lighting, {background} background, sharp focus, high quality',
                'difficulty_level' => 'beginner'
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'title' => 'Paisagem Fantástica',
                'template_text' => 'Epic landscape of {location}, {time_of_day}, {weather}, cinematic composition, highly detailed',
                'difficulty_level' => 'intermediate'
            ]
        ];
        
        if ($categoryId) {
            return array_filter($allTemplates, function($template) use ($categoryId) {
                return $template['category_id'] == $categoryId;
            });
        }
        
        return $allTemplates;
    }
}
?>