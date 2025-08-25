# 🎨 Gerador de Prompts v2.0 - Manual de Uso

## 📋 Visão Geral

O **Gerador de Prompts v2.0** é uma ferramenta avançada e intuitiva para criar prompts profissionais para diversas ferramentas de IA, incluindo:

- **Stable Diffusion** - Geração de imagens artísticas
- **Midjourney** - Arte conceitual e designs criativos  
- **DALL-E** - Criação de imagens únicas
- **ChatGPT** - Prompts para conversação e texto
- **Claude** - Assistente para análise e escrita
- **Video AI** - Geração de vídeos com IA

## 🚀 Como Usar

### Etapa 1: Escolher Ferramenta de IA
1. Selecione a ferramenta de IA desejada
2. Cada categoria tem características específicas otimizadas
3. Clique no card da ferramenta para prosseguir

### Etapa 2: Estilo & Tema
1. **Para geradores de imagem**: Escolha estilos como Fotorrealista, Anime, Arte Digital, etc.
2. **Para assistentes de texto**: Selecione o tom (Amigável, Profissional, Especialista)
3. **Opcional**: Pode pular esta etapa para prompts personalizados

### Etapa 3: Configurações Avançadas
1. **Proporções**: Para imagens, selecione formato (1:1, 16:9, 9:16, etc.)
2. **Configurações específicas**:
   - **Midjourney**: Stylize, Versão
   - **Stable Diffusion**: Qualidade, Sampling Steps
   - **Texto**: Tom de resposta, Formato

### Etapa 4: Sua Ideia
1. Descreva sua ideia de forma clara e específica
2. **Preview em tempo real** mostra como ficará o prompt final
3. Adicione um título para organizar seu histórico

### Etapa 5: Resultado Final
1. **Copie o prompt** otimizado para usar na ferramenta
2. **Salve como favorito** para reutilizar
3. **Crie outro prompt** ou **salve no histórico**

## 🎯 Recursos Principais

### ✨ Geração Inteligente
- **Otimização automática** para cada ferramenta de IA
- **Combinação inteligente** de estilos e configurações
- **Preview em tempo real** do prompt final

### 📚 Biblioteca de Estilos
- **Estilos pré-definidos** para diferentes categorias
- **Templates profissionais** para começar rapidamente
- **Configurações específicas** para cada ferramenta

### 💾 Histórico e Favoritos
- **Salvar prompts** criados
- **Marcar como favoritos** os melhores
- **Histórico completo** com busca e filtros
- **Compartilhar prompts** com outros usuários

### 🎨 Interface Moderna
- **Design responsivo** para desktop e mobile
- **Navegação em etapas** clara e intuitiva
- **Animações suaves** e feedback visual
- **Tema escuro/claro** automático

## 🛠️ Dicas Profissionais

### Para Geradores de Imagem:
1. **Seja específico**: "gato laranja" → "gato persa laranja com olhos verdes"
2. **Use qualificadores**: "bonito", "detalhado", "profissional"
3. **Defina estilo**: fotorrealista, arte digital, pintura
4. **Especifique iluminação**: "luz dourada", "iluminação dramática"

### Para Assistentes de Texto:
1. **Defina o papel**: "Você é um especialista em..."
2. **Especifique o formato**: lista, ensaio, resumo
3. **Dê contexto**: background, público-alvo
4. **Peça exemplos**: quando aplicável

### Exemplos de Prompts Gerados:

#### Stable Diffusion:
```
photorealistic, highly detailed, 8k resolution, professional photography, 
majestic mountain landscape at sunset, golden hour lighting, misty valleys, 
sharp peaks, dramatic sky, masterpiece, best quality --ar 16:9
```

#### ChatGPT:
```
You are a helpful marketing expert. Please respond friendly and conversational. 
Create a social media strategy for a small coffee shop. 
Please format your response in numbered steps.
```

## 📊 Estrutura do Banco de Dados

### Tabelas Principais:
- **ai_categories**: Categorias de ferramentas de IA
- **art_styles**: Estilos artísticos por categoria
- **aspect_ratios**: Proporções para imagens
- **user_prompts**: Histórico de prompts do usuário
- **prompt_templates**: Templates prontos
- **prompt_shares**: Compartilhamentos

### Recursos Avançados:
- **Busca por tags** nos prompts salvos
- **Avaliação** de prompts (1-5 estrelas)
- **Compartilhamento público** opcional
- **Templates da comunidade**

## 🔧 Configuração Técnica

### Arquivos Principais:
- `gerador_prompt2.php` - Interface principal
- `assets/css/gerador-prompt-v2.css` - Estilos modernos
- `assets/js/gerador-prompt-v2.js` - Interatividade
- `includes/PromptManager.php` - Lógica de negócio
- `api/prompt_data.php` - API para dados dinâmicos

### Banco de Dados:
Execute o arquivo `docs/prompt_generator_schema.sql` no Supabase para criar as tabelas necessárias.

### Dependências:
- **PHP 8.0+**
- **Supabase** (ou fallback local)
- **Font Awesome** para ícones
- **Google Fonts (Inter)** para tipografia

## 🎉 Próximas Funcionalidades

### Em Desenvolvimento:
- [ ] **Integração com APIs** das ferramentas de IA
- [ ] **Geração automática** de variações
- [ ] **Análise de qualidade** de prompts
- [ ] **Comunidade de prompts** compartilhados
- [ ] **Templates por categoria** (retratos, paisagens, etc.)
- [ ] **Exportação** em diferentes formatos
- [ ] **Histórico com imagens** dos resultados

### Recursos Avançados Planejados:
- [ ] **IA para melhorar prompts** automaticamente
- [ ] **Análise de tendências** em prompts populares
- [ ] **Colaboração em tempo real**
- [ ] **Plugin para ferramentas** populares
- [ ] **API pública** para desenvolvedores

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique este manual primeiro
2. Consulte os logs de erro
3. Entre em contato com o suporte técnico

---

**Versão**: 2.0.0  
**Última atualização**: 16/08/2025  
**Desenvolvido com**: PHP, JavaScript, CSS Grid, Supabase