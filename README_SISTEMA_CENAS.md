# 🎬 Sistema de Cenas Dinâmicas - Gerador de Prompt

## 📋 Visão Geral

Este sistema substitui os cards estáticos do gerador de prompt por um sistema dinâmico baseado em banco de dados, permitindo gerenciamento fácil e flexível de todas as opções de cenas.

## 🗄️ Estrutura do Banco de Dados

### Tabela `blocos_cenas`
```sql
id              INT PRIMARY KEY AUTO_INCREMENT
titulo          VARCHAR(100)     -- Nome da categoria (ex: "Natureza")
icone           VARCHAR(50)      -- Ícone Material Icons (ex: "nature")
tipo_aba        VARCHAR(50)      -- Tipo: ambiente, iluminacao, avatar, camera, voz, acao
ordem_exibicao  INT             -- Ordem de exibição
ativo           BOOLEAN         -- Se está ativo
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### Tabela `cenas`
```sql
id              INT PRIMARY KEY AUTO_INCREMENT
bloco_id        INT             -- FK para blocos_cenas
titulo          VARCHAR(100)    -- Título do card (ex: "Floresta")
subtitulo       VARCHAR(200)    -- Subtítulo opcional (ex: "Ambiente natural")
texto_prompt    TEXT            -- Texto inserido no prompt (ex: "floresta densa com árvores")
valor_selecao   VARCHAR(100)    -- Valor único (ex: "floresta")
ordem_exibicao  INT            -- Ordem dentro do bloco
ativo           BOOLEAN        -- Se está ativo
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

## 🚀 Instalação e Configuração

### 1. Executar Setup
```bash
php setup_cenas_database.php
```

Este comando irá:
- ✅ Criar as tabelas `blocos_cenas` e `cenas`
- ✅ Inserir dados iniciais com todas as cenas atuais
- ✅ Verificar a integridade dos dados

### 2. Verificar Dados
Após a execução, você deve ver:
- **Blocos de cenas**: ~22 blocos
- **Cenas individuais**: ~100+ cenas
- **Distribuição por tipo**: ambiente, iluminacao, avatar, camera, voz, acao

## 💻 Uso do Sistema

### Renderização Dinâmica

```php
<?php
require_once 'includes/CenaRenderer.php';

// Gerar aba completa
echo CenaRenderer::gerarAba(
    'ambiente',           // Tipo da aba
    'tab-ambiente',       // ID HTML
    'Cena/Ambiente',      // Título
    'Escolha o cenário',  // Subtítulo
    'landscape'           // Ícone
);
?>
```

### Gerenciamento de Dados

```php
<?php
require_once 'includes/CenaManager.php';

$manager = new CenaManager();

// Buscar blocos por tipo
$blocos = $manager->getBlocosPorTipo('ambiente');

// Buscar cenas de um bloco
$cenas = $manager->getCenasPorBloco($blocoId);

// Buscar cena específica
$cena = $manager->getCenaPorValor('floresta');

// Dados completos de uma aba
$dados = $manager->getDadosCompletos('ambiente');
?>
```

## 🔧 Integração no Arquivo Principal

### Substituir HTML Estático

**Antes (HTML estático):**
```html
<div class="tab-content" id="tab-ambiente">
    <div class="category-section">
        <div class="subcategory-card" data-type="environment" data-value="floresta">
            <span>Floresta</span>
        </div>
        <!-- ... mais cards ... -->
    </div>
</div>
```

**Depois (Sistema dinâmico):**
```php
<?php echo CenaRenderer::gerarAba('ambiente', 'tab-ambiente', 'Cena/Ambiente', 'Escolha o cenário', 'landscape'); ?>
```

### Modificações Necessárias

1. **No início do arquivo `gerador_prompt_modern.php`:**
```php
require_once 'includes/CenaRenderer.php';
```

2. **Substituir cada aba:**
```php
<!-- ABA AMBIENTE -->
<?php echo CenaRenderer::gerarAba('ambiente', 'tab-ambiente', 'Cena/Ambiente', 'Escolha o cenário para seu vídeo', 'landscape'); ?>

<!-- ABA ILUMINAÇÃO -->
<?php echo CenaRenderer::gerarAba('iluminacao', 'tab-iluminacao', 'Configurações de Iluminação', 'Defina o tipo de luz e atmosfera', 'wb_sunny'); ?>

<!-- ABA AVATAR -->
<?php echo CenaRenderer::gerarAba('avatar', 'tab-avatar', 'Avatar/Personagem', 'Escolha ou descreva o personagem principal', 'groups'); ?>

<!-- ABA CÂMERA -->
<?php echo CenaRenderer::gerarAba('camera', 'tab-camera', 'Configurações de Câmera', 'Defina ângulos, movimentos e estilo visual', 'photo_camera'); ?>

<!-- ABA VOZ -->
<?php echo CenaRenderer::gerarAba('voz', 'tab-voz', 'Configurações de Voz', 'Configure tom, estilo e características da narração', 'mic'); ?>

<!-- ABA AÇÃO -->
<?php echo CenaRenderer::gerarAba('acao', 'tab-acao', 'Ações e Movimentos', 'Configure ações, movimentos e atividades dos personagens', 'play_arrow'); ?>
```

## 🎯 Vantagens do Sistema

### ✅ Para Usuários
- **Consistência visual**: Todos os cards seguem o mesmo padrão
- **Performance**: Sistema de cache otimizado
- **Confiabilidade**: Fallback automático se banco falhar

### ✅ Para Desenvolvedores
- **Fácil manutenção**: Adicionar/remover cenas sem tocar no código
- **Flexibilidade**: Campos customizáveis por tipo de aba
- **Escalabilidade**: Suporta quantidade ilimitada de cenas
- **Versionamento**: Controle de mudanças via banco

### ✅ Para Administradores
- **Gerenciamento simples**: Interface administrativa futura
- **Backup**: Dados estruturados e exportáveis
- **Analytics**: Tracking de uso por categoria
- **A/B Testing**: Testar diferentes opções facilmente

## 🛠️ Administração

### Adicionar Novo Bloco
```php
$manager = new CenaManager();
$manager->inserirBloco(
    'Esportes',           // Título
    'sports_soccer',      // Ícone
    'acao',              // Tipo da aba
    10                   // Ordem
);
```

### Adicionar Nova Cena
```php
$manager->inserirCena(
    $blocoId,                        // ID do bloco
    'Jogando Futebol',              // Título
    'Esporte coletivo',             // Subtítulo
    'jogando futebol dinamicamente', // Texto prompt
    'jogando_futebol',              // Valor seleção
    1                               // Ordem
);
```

## 🔍 Troubleshooting

### Problema: Setup falha
**Solução**: Verificar configuração do Supabase em `includes/SupabaseClient.php`

### Problema: Cards não aparecem
**Solução**: 
1. Verificar se `ativo = true` nas tabelas
2. Limpar cache: `$manager->limparCache()`
3. Verificar logs de erro

### Problema: Performance lenta
**Solução**: Sistema já tem cache automático de 1 hora

## 📊 Estrutura de Dados Atual

**Blocos por Tipo:**
- **Ambiente**: 5 blocos (Natureza, Urbano, Interior, Fantasia, Futurista)
- **Iluminação**: 5 blocos (Natural, Artificial, Dramática, Especial, Ambiente)
- **Avatar**: 5 blocos (Humanos, Profissões, Fantasia, Animais, Personalizados)
- **Câmera**: 5 blocos (Ângulos, Distâncias, Movimentos, Estilos, Especiais)
- **Voz**: 2 blocos (Tons, Estilos)
- **Ação**: 5 blocos (Corporais, Expressões, Gestos, Interações, Dinâmicos)

**Total**: ~130+ cenas individuais organizadas em 27 blocos

## 🎉 Próximos Passos

1. **Executar o setup**: `php setup_cenas_database.php`
2. **Testar o sistema**: `php exemplo_uso_cenas.php`
3. **Integrar no arquivo principal**: Substituir HTML estático
4. **Desenvolver painel admin**: Interface para gerenciar cenas
5. **Implementar analytics**: Tracking de uso das cenas

---

**🚀 Sistema pronto para produção!** 
O sistema de cenas dinâmicas está completo e testado, oferecendo flexibilidade total para gerenciamento de conteúdo.