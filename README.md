# 🚀 CentroService - Landing Page para Criação de Vídeos com IA

Uma landing page profissional e moderna para venda de serviços de criação de vídeos institucionais, VSL, Reels e conteúdo para redes sociais criados através de Inteligência Artificial.

## ✨ Características

- **Design Moderno**: Interface limpa e profissional com foco em conversão
- **Totalmente Responsiva**: Otimizada para desktop, tablet e mobile
- **Hero Section com Vídeo**: Background de vídeo em tela cheia
- **Animações Suaves**: Efeitos de scroll e interações elegantes
- **Formulário de Contato**: Validação em tempo real e processamento PHP
- **SEO Otimizado**: Meta tags, estrutura semântica e performance
- **Acessibilidade**: Navegação por teclado e leitores de tela

## 🎯 Seções da Landing Page

1. **Hero Section**: Vídeo de background + call-to-action principal
2. **Serviços**: Apresentação dos 4 tipos de serviços oferecidos
3. **Vantagens**: 6 benefícios de escolher a CentroService
4. **Portfólio**: Galeria de trabalhos realizados
5. **Preços**: 3 planos com diferentes níveis de serviço
6. **CTA**: Call-to-action secundário
7. **Contato**: Formulário + informações de contato
8. **Footer**: Links úteis e redes sociais

## 🛠️ Tecnologias Utilizadas

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Estilização**: CSS Grid, Flexbox, CSS Custom Properties
- **Animações**: CSS Animations, Intersection Observer API
- **Ícones**: Font Awesome 6.4.0
- **Fontes**: Google Fonts (Inter)
- **Responsividade**: Mobile-first approach

## 📁 Estrutura do Projeto

```
centroservice/
├── index.php                 # Página principal
├── process_contact.php      # Processamento do formulário
├── assets/
│   ├── css/
│   │   └── style.css       # Estilos principais
│   ├── js/
│   │   └── main.js         # Funcionalidades JavaScript
│   └── images/             # Imagens e recursos visuais
│       ├── logo.png        # Logo da empresa
│       ├── favicon.ico     # Ícone do site
│       └── portfolio-*.jpg # Imagens do portfólio
└── README.md               # Este arquivo
```

## 🚀 Instalação

### Pré-requisitos

- Servidor web (Apache, Nginx, ou XAMPP)
- PHP 7.4 ou superior
- Navegador moderno com suporte a ES6+

### Passos de Instalação

1. **Clone ou baixe o projeto**
   ```bash
   git clone [URL_DO_REPOSITORIO]
   cd centroservice
   ```

2. **Configure o servidor web**
   - Coloque os arquivos na pasta `htdocs` (XAMPP) ou diretório raiz do seu servidor
   - Certifique-se de que o PHP está habilitado

3. **Configure o vídeo de background**
   - Substitua o URL do vídeo em `index.php` linha 95:
   ```html
   <source src="https://centroservice.com.br/midia/video.mp4" type="video/mp4">
   ```
   - Ou coloque seu vídeo na pasta `assets/videos/` e atualize o caminho

4. **Configure as imagens**
   - Adicione sua logo em `assets/images/logo.png`
   - Adicione imagens do portfólio em `assets/images/portfolio-*.jpg`
   - Adicione favicon em `assets/images/favicon.ico`

5. **Configure o formulário de contato**
   - Edite `process_contact.php` e atualize o email de destino (linha 67)
   - Configure as informações de contato em `index.php`

## ⚙️ Configuração

### Personalização de Cores

As cores principais estão definidas em `assets/css/style.css`:

```css
:root {
    --primary-color: #2563eb;      /* Azul principal */
    --secondary-color: #3b82f6;    /* Azul secundário */
    --accent-color: #10b981;       /* Verde de sucesso */
    --text-color: #1f2937;         /* Texto principal */
    --text-secondary: #6b7280;     /* Texto secundário */
    --background-light: #f8fafc;   /* Fundo claro */
    --background-dark: #1f2937;    /* Fundo escuro */
}
```

### Configuração de Serviços

Edite a seção de serviços em `index.php` para personalizar:

```html
<div class="service-card">
    <div class="service-icon">
        <i class="fas fa-building"></i>
    </div>
    <h3 class="service-title">Vídeos Institucionais</h3>
    <p class="service-description">Descrição do serviço...</p>
    <ul class="service-features">
        <li><i class="fas fa-check"></i> Característica 1</li>
        <!-- Adicione mais características -->
    </ul>
</div>
```

### Configuração de Preços

Atualize os planos e preços em `index.php`:

```html
<div class="pricing-card">
    <div class="pricing-header">
        <h3 class="pricing-title">Nome do Plano</h3>
        <div class="pricing-price">
            <span class="currency">R$</span>
            <span class="amount">97</span>
            <span class="period">/mês</span>
        </div>
    </div>
    <!-- Lista de recursos -->
</div>
```

### Configuração de Contato

Atualize as informações de contato em `index.php`:

```html
<div class="contact-item">
    <div class="contact-icon">
        <i class="fas fa-phone"></i>
    </div>
    <div class="contact-details">
        <h3>Telefone</h3>
        <p>+55 (11) 99999-9999</p>
    </div>
</div>
```

## 📧 Configuração do Formulário

### Email

O formulário está configurado para enviar emails usando a função `mail()` do PHP. Para melhor funcionamento, configure:

1. **SMTP do servidor** (recomendado)
2. **Serviços de email** como SendGrid, Mailgun, ou AWS SES

### Banco de Dados (Opcional)

Para salvar os contatos em banco de dados, edite `process_contact.php`:

```php
// Descomente e configure a conexão MySQL
try {
    $pdo = new PDO('mysql:host=localhost;dbname=centroservice', 'username', 'password');
    // ... resto do código
}
```

### WhatsApp (Opcional)

Para notificações via WhatsApp, implemente em `process_contact.php`:

```php
// Integre com WhatsApp Business API ou serviços como Twilio
function sendWhatsAppNotification($data) {
    // Sua implementação aqui
}
```

## 🎨 Personalização

### Logo e Marca

- Substitua `assets/images/logo.png` pela sua logo
- Atualize o nome da empresa em `index.php`
- Personalize as cores no CSS

### Conteúdo

- Edite todos os textos para refletir sua empresa
- Atualize as estatísticas na hero section
- Personalize os serviços oferecidos
- Ajuste os preços e planos

### Imagens

- Adicione suas próprias imagens de portfólio
- Substitua as imagens placeholder
- Otimize as imagens para web (formato WebP recomendado)

## 📱 Responsividade

A landing page é totalmente responsiva com breakpoints:

- **Desktop**: 1200px+
- **Tablet**: 768px - 1199px
- **Mobile**: 320px - 767px

### Teste de Responsividade

1. Use as ferramentas de desenvolvedor do navegador
2. Teste em diferentes dispositivos
3. Verifique a navegação mobile
4. Teste o formulário em telas pequenas

## 🚀 Otimizações de Performance

### Implementadas

- Lazy loading de imagens
- CSS e JS minificados
- Preload de recursos críticos
- Debounce em eventos de scroll
- Otimização de animações

### Recomendadas

- Comprima imagens (WebP, AVIF)
- Use CDN para recursos externos
- Implemente cache do navegador
- Otimize o vídeo de background

## 🔒 Segurança

### Implementada

- Validação de entrada no frontend e backend
- Sanitização de dados
- Proteção contra XSS
- Validação de email e telefone
- Rate limiting básico

### Recomendada

- Implemente HTTPS
- Adicione reCAPTCHA
- Configure CSP headers
- Implemente logging de segurança

## 📊 Analytics e Tracking

### Google Analytics

Adicione o código do Google Analytics em `index.php`:

```html
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'GA_MEASUREMENT_ID');
</script>
```

### Facebook Pixel

Para campanhas de marketing digital:

```html
<!-- Facebook Pixel -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', 'YOUR_PIXEL_ID');
  fbq('track', 'PageView');
</script>
```

## 🧪 Testes

### Testes Recomendados

1. **Funcionalidade**
   - Navegação entre seções
   - Formulário de contato
   - Links externos
   - Menu mobile

2. **Responsividade**
   - Diferentes tamanhos de tela
   - Orientação portrait/landscape
   - Navegadores móveis

3. **Performance**
   - PageSpeed Insights
   - GTmetrix
   - WebPageTest

4. **Acessibilidade**
   - Navegação por teclado
   - Leitores de tela
   - Contraste de cores

## 🐛 Solução de Problemas

### Problemas Comuns

1. **Vídeo não carrega**
   - Verifique o URL do vídeo
   - Confirme se o formato é suportado
   - Teste em diferentes navegadores

2. **Formulário não envia**
   - Verifique se o PHP está funcionando
   - Confirme as configurações de email
   - Verifique os logs de erro

3. **Layout quebrado**
   - Limpe o cache do navegador
   - Verifique se todos os arquivos CSS/JS carregaram
   - Teste em modo incógnito

### Logs de Erro

- **PHP**: Verifique o log de erros do servidor
- **JavaScript**: Abra o console do navegador (F12)
- **CSS**: Use as ferramentas de desenvolvedor

## 📈 Métricas e Conversão

### KPIs Recomendados

- **Taxa de conversão** do formulário
- **Tempo na página** por seção
- **Scroll depth** (profundidade de rolagem)
- **CTR** dos botões de call-to-action
- **Bounce rate** e tempo de sessão

### A/B Testing

Teste diferentes elementos:

- Títulos e subtítulos
- Cores dos botões
- Posicionamento dos CTAs
- Conteúdo das seções
- Formulário de contato

## 🔄 Atualizações e Manutenção

### Checklist Mensal

- [ ] Verificar funcionamento do formulário
- [ ] Atualizar portfólio com novos trabalhos
- [ ] Revisar e atualizar preços
- [ ] Verificar links externos
- [ ] Analisar métricas de conversão

### Checklist Trimestral

- [ ] Revisar e atualizar conteúdo
- [ ] Otimizar imagens e vídeos
- [ ] Atualizar dependências
- [ ] Revisar SEO e meta tags
- [ ] Testar em novos navegadores

## 📚 Recursos Adicionais

### Documentação

- [HTML5 Specification](https://developer.mozilla.org/en-US/docs/Web/HTML)
- [CSS Grid Layout](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Grid_Layout)
- [JavaScript ES6+](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
- [PHP Documentation](https://www.php.net/docs.php)

### Ferramentas Úteis

- **Design**: Figma, Adobe XD, Sketch
- **Desenvolvimento**: VS Code, Sublime Text, PHPStorm
- **Testes**: BrowserStack, LambdaTest
- **Performance**: PageSpeed Insights, GTmetrix

## 🤝 Suporte

### Contato

- **Email**: contato@centroservice.com.br
- **Website**: centroservice.com.br
- **Documentação**: Este README

### Contribuições

1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 🙏 Agradecimentos

- Font Awesome pelos ícones
- Google Fonts pelas tipografias
- Comunidade open source
- Todos os contribuidores

---

**Desenvolvido com ❤️ pela CentroService**

*Transformando marcas através de vídeos profissionais criados com tecnologia de IA de ponta.*
#   a p r e n d _ a q u i  
 