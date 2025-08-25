# 🛠️ Instruções de Setup - Sistema de Cenas

## ⚠️ Problema Identificado

O erro `Call to undefined method SupabaseClient::rawQuery()` indica que a classe `SupabaseClient` existente não tem os métodos necessários para executar SQL diretamente.

## 🔧 Solução Implementada

Criei versões corrigidas que usam a API REST do Supabase ao invés de SQL direto.

## 📋 Passos para Setup

### 1. Criar Tabelas no Supabase

**Acesse o painel do Supabase:**
1. Vá para [https://supabase.com](https://supabase.com)
2. Entre no seu projeto
3. Navegue até **SQL Editor**
4. Execute o seguinte SQL:

```sql
-- Criar tabela blocos_cenas
CREATE TABLE IF NOT EXISTS blocos_cenas (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    icone VARCHAR(50) NOT NULL,
    tipo_aba VARCHAR(50) NOT NULL,
    ordem_exibicao INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Criar tabela cenas
CREATE TABLE IF NOT EXISTS cenas (
    id SERIAL PRIMARY KEY,
    bloco_id INT NOT NULL REFERENCES blocos_cenas(id) ON DELETE CASCADE,
    titulo VARCHAR(100) NOT NULL,
    subtitulo VARCHAR(200) DEFAULT NULL,
    texto_prompt TEXT NOT NULL,
    valor_selecao VARCHAR(100) NOT NULL,
    ordem_exibicao INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Criar índices para performance
CREATE INDEX IF NOT EXISTS idx_blocos_tipo_aba ON blocos_cenas(tipo_aba);
CREATE INDEX IF NOT EXISTS idx_cenas_bloco_id ON cenas(bloco_id);
CREATE INDEX IF NOT EXISTS idx_cenas_valor_selecao ON cenas(valor_selecao);

-- Habilitar RLS (Row Level Security) se necessário
ALTER TABLE blocos_cenas ENABLE ROW LEVEL SECURITY;
ALTER TABLE cenas ENABLE ROW LEVEL SECURITY;

-- Políticas básicas (permitir leitura para todos)
CREATE POLICY "Permitir leitura blocos_cenas" ON blocos_cenas FOR SELECT USING (true);
CREATE POLICY "Permitir leitura cenas" ON cenas FOR SELECT USING (true);
```

### 2. Testar Conexão

Execute o script corrigido:
```bash
php setup_cenas_database_fix.php
```

Este script irá:
- ✅ Verificar se as tabelas existem
- ✅ Tentar inserir dados de exemplo
- ✅ Mostrar status da conexão

### 3. Inserir Dados Completos

Se o teste funcionou, você pode usar o `CenaManager` para inserir todos os dados:

```php
<?php
require_once 'includes/CenaManager.php';

$manager = new CenaManager();

// Exemplo: Inserir bloco
$manager->inserirBloco('Natureza', 'nature', 'ambiente', 1);

// Exemplo: Inserir cena
$manager->inserirCena(1, 'Floresta', 'Ambiente natural', 'floresta densa com árvores altas', 'floresta', 1);
?>
```

### 4. Verificar Configuração

Confirme que o arquivo `env.config` tem as configurações corretas:
```
SUPABASE_URL=https://seu-projeto.supabase.co
SUPABASE_ANON_KEY=sua_chave_anonima
SUPABASE_SERVICE_KEY=sua_chave_de_servico
```

## 🚀 Após Setup Completo

### Usar o Sistema

```php
<?php
require_once 'includes/CenaRenderer.php';

// Renderizar aba completa
echo CenaRenderer::gerarAba('ambiente', 'tab-ambiente', 'Cena/Ambiente', 'Escolha o cenário', 'landscape');
?>
```

### Gerenciar Dados

```php
<?php
require_once 'includes/CenaManager.php';

$manager = new CenaManager();

// Buscar dados
$blocos = $manager->getBlocosPorTipo('ambiente');
$cenas = $manager->getCenasPorBloco(1);
$dadosCompletos = $manager->getDadosCompletos('ambiente');
?>
```

## 🔍 Troubleshooting

### Erro de Permissão
Se receber erro 403, verifique as políticas RLS no Supabase.

### Tabelas Não Encontradas
Execute o SQL de criação das tabelas no painel do Supabase.

### Dados Não Aparecem
1. Verifique se `ativo = true` nas tabelas
2. Use `$manager->limparCache()` para limpar cache
3. Verifique logs de erro no PHP

## 📞 Próximos Passos

1. **Execute o setup**: `php setup_cenas_database_fix.php`
2. **Crie as tabelas** no painel do Supabase (SQL acima)
3. **Teste o sistema** com `CenaManager`
4. **Integre no arquivo principal** substituindo HTML estático

---

🎯 **O sistema está pronto!** Use `CenaRenderer::gerarAba()` para substituir o HTML estático por dados dinâmicos do banco.