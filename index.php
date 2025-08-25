<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CentroService - Criação de Vídeos com IA</title>
    <meta name="description" content="Criamos vídeos institucionais, VSL, Reels e conteúdo para redes sociais usando Inteligência Artificial. Transforme sua marca com vídeos profissionais!">
    <meta name="keywords" content="vídeos institucionais, VSL, reels, redes sociais, IA, inteligência artificial, marketing digital">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <img src="assets/images/logo.png" alt="CentroService Logo" class="logo">
                    <span class="logo-text">CentroService</span>
                </div>
                
                <div class="nav-menu" id="nav-menu">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="#home" class="nav-link">Início</a>
                        </li>
                        <li class="nav-item">
                            <a href="#services" class="nav-link">Serviços</a>
                        </li>
                        <li class="nav-item">
                            <a href="#benefits" class="nav-link">Vantagens</a>
                        </li>
                        <li class="nav-item">
                            <a href="#portfolio" class="nav-link">Portfólio</a>
                        </li>
                        <li class="nav-item">
                            <a href="#pricing" class="nav-link">Preços</a>
                        </li>
                        <li class="nav-item">
                            <a href="#faq" class="nav-link">FAQ</a>
                        </li>
                        <li class="nav-item">
                            <a href="#contact" class="nav-link">Contato</a>
                        </li>
                        <li class="nav-item nav-cta-mobile">
                            <a href="auth/login.php" class="nav-link btn-mobile-cta">
                                <i class="fas fa-magic"></i>
                                Gerador de Prompt
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-cta">
                    <a href="auth/login.php" class="btn btn-prompt-generator">
                        <i class="fas fa-magic"></i>
                        <span>Gerador de Prompt</span>
                    </a>
                </div>
                
                <div class="nav-toggle" id="nav-toggle">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <video class="hero-video" autoplay muted loop playsinline>
            <source src="https://centroservice.com.br/midia/video.mp4" type="video/mp4">
            Seu navegador não suporta vídeos.
        </video>
        
        <div class="hero-overlay">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="hero-badge">
                        <i class="fas fa-robot"></i>
                        <span>Powered by AI</span>
                    </div>
                    
                    <h1 class="hero-title">
                        Transforme sua marca com
                        <span class="highlight">Vídeos Profissionais</span>
                        criados por IA
                    </h1>
                    
                    <p class="hero-subtitle">
                        Criamos vídeos institucionais, VSL, Reels e conteúdo para redes sociais 
                        usando tecnologia de ponta em Inteligência Artificial
                    </p>
                    
                    <div class="hero-features">
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Entrega em 24h</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Qualidade profissional</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Suporte 24/7</span>
                        </div>
                    </div>
                    
                    <div class="hero-buttons">
                        <a href="#contact" class="btn btn-primary">
                            <i class="fas fa-rocket"></i>
                            Solicitar Orçamento
                        </a>
                        <a href="#portfolio" class="btn btn-secondary">
                            <i class="fas fa-play-circle"></i>
                            Ver Portfólio
                        </a>
                    </div>
                </div>
                

            </div>
        </div>
        
        <div class="hero-scroll-indicator">
            <div class="scroll-arrow">
                <i class="fas fa-chevron-down"></i>
            </div>
            <span>Role para baixo</span>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Nossos Serviços</h2>
                <p class="section-subtitle">Soluções completas em criação de vídeos com tecnologia de IA</p>
            </div>
            
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="service-title">Vídeos Institucionais</h3>
                    <p class="service-description">
                        Apresente sua empresa de forma profissional com vídeos que contam sua história 
                        e transmitem confiança aos clientes.
                    </p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Roteiro personalizado</li>
                        <li><i class="fas fa-check"></i> Narração profissional</li>
                        <li><i class="fas fa-check"></i> Música de fundo</li>
                        <li><i class="fas fa-check"></i> Revisões ilimitadas</li>
                    </ul>
                </div>
                
                <div class="service-card featured">
                    <div class="service-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="service-title">VSL (Vídeo Sales Letter)</h3>
                    <p class="service-description">
                        Vídeos persuasivos que convertem visitantes em clientes, 
                        aumentando suas vendas e ROI.
                    </p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Copywriting persuasivo</li>
                        <li><i class="fas fa-check"></i> Gatilhos emocionais</li>
                        <li><i class="fas fa-check"></i> Call-to-action eficaz</li>
                        <li><i class="fas fa-check"></i> Otimizado para conversão</li>
                    </ul>
                    <div class="featured-badge">Mais Popular</div>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="service-title">Reels e Stories</h3>
                    <p class="service-description">
                        Conteúdo envolvente para Instagram, TikTok e outras redes sociais 
                        que aumenta o engajamento da sua marca.
                    </p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Formato vertical otimizado</li>
                        <li><i class="fas fa-check"></i> Tendências atuais</li>
                        <li><i class="fas fa-check"></i> Hashtags estratégicas</li>
                        <li><i class="fas fa-check"></i> Música trending</li>
                    </ul>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3 class="service-title">Conteúdo com IA</h3>
                    <p class="service-description">
                        Aproveite o poder da Inteligência Artificial para criar vídeos 
                        únicos e personalizados em tempo recorde.
                    </p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Geração automática</li>
                        <li><i class="fas fa-check"></i> Personalização avançada</li>
                        <li><i class="fas fa-check"></i> Múltiplas variações</li>
                        <li><i class="fas fa-check"></i> Atualizações em tempo real</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="benefits">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Por que escolher a CentroService?</h2>
                <p class="section-subtitle">Descubra as vantagens de trabalhar conosco</p>
            </div>
            
            <div class="benefits-grid">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3>Entrega Rápida</h3>
                    <p>Receba seu vídeo em até 24 horas, sem comprometer a qualidade</p>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3>Tecnologia de IA</h3>
                    <p>Utilizamos as mais avançadas ferramentas de IA para criar vídeos únicos</p>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Suporte 24/7</h3>
                    <p>Nossa equipe está sempre disponível para ajudar você</p>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Garantia Total</h3>
                    <p>100% de satisfação garantida ou seu dinheiro de volta</p>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Design Personalizado</h3>
                    <p>Cada vídeo é criado exclusivamente para sua marca</p>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Resultados Comprovados</h3>
                    <p>Clientes relatam aumento médio de 300% no engajamento</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section id="portfolio" class="portfolio">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Nosso Portfólio</h2>
                <p class="section-subtitle">Confira alguns dos nossos trabalhos mais recentes</p>
            </div>
            
            <div class="portfolio-grid">
                <div class="portfolio-item">
                    <div class="portfolio-image">
                        <video muted loop preload="metadata" class="portfolio-video">
                            <source src="midia/cena02.mp4" type="video/mp4">
                            <img src="assets/images/portfolio-1.jpg" alt="Vídeo Institucional">
                        </video>
                        <div class="portfolio-overlay">
                            <a href="midia/cena02.mp4" class="portfolio-link" target="_blank">
                                <i class="fas fa-play"></i>
                            </a>
                        </div>
                    </div>
                    <div class="portfolio-info">
                        <h3>Vídeo Institucional</h3>
                        <p>Empresa de Tecnologia</p>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <div class="portfolio-image">
                        <img src="assets/images/portfolio-2.jpg" alt="VSL">
                        <div class="portfolio-overlay">
                            <a href="#" class="portfolio-link">
                                <i class="fas fa-play"></i>
                            </a>
                        </div>
                    </div>
                    <div class="portfolio-info">
                        <h3>VSL</h3>
                        <p>Produto Digital</p>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <div class="portfolio-image">
                        <img src="assets/images/portfolio-3.jpg" alt="Reels">
                        <div class="portfolio-overlay">
                            <a href="#" class="portfolio-link">
                                <i class="fas fa-play"></i>
                            </a>
                        </div>
                    </div>
                    <div class="portfolio-info">
                        <h3>Reels</h3>
                        <p>Moda e Lifestyle</p>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <div class="portfolio-image">
                        <img src="assets/images/portfolio-4.jpg" alt="Conteúdo IA">
                        <div class="portfolio-overlay">
                            <a href="#" class="portfolio-link">
                                <i class="fas fa-play"></i>
                            </a>
                        </div>
                    </div>
                    <div class="portfolio-info">
                        <h3>Conteúdo IA</h3>
                        <p>E-commerce</p>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <div class="portfolio-image">
                        <img src="assets/images/portfolio-5.jpg" alt="Motion Graphics">
                        <div class="portfolio-overlay">
                            <a href="#" class="portfolio-link">
                                <i class="fas fa-play"></i>
                            </a>
                        </div>
                    </div>
                    <div class="portfolio-info">
                        <h3>Motion Graphics</h3>
                        <p>Animação Corporativa</p>
                    </div>
                </div>
                
                <div class="portfolio-item">
                    <div class="portfolio-image">
                        <img src="assets/images/portfolio-6.jpg" alt="Publicidade Digital">
                        <div class="portfolio-overlay">
                            <a href="#" class="portfolio-link">
                                <i class="fas fa-play"></i>
                            </a>
                        </div>
                    </div>
                    <div class="portfolio-info">
                        <h3>Publicidade Digital</h3>
                        <p>Campanha Publicitária</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="pricing">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Planos e Preços</h2>
                <p class="section-subtitle">Escolha o plano ideal para suas necessidades</p>
            </div>
            
            <div class="pricing-grid">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3 class="pricing-title">Básico</h3>
                        <div class="pricing-price">
                            <span class="currency">R$</span>
                            <span class="amount">97</span>
                            <span class="period">/mês</span>
                        </div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> 2 vídeos por mês</li>
                        <li><i class="fas fa-check"></i> Duração até 60 segundos</li>
                        <li><i class="fas fa-check"></i> 2 revisões</li>
                        <li><i class="fas fa-check"></i> Suporte por email</li>
                    </ul>
                    <a href="#contact" class="btn btn-outline">Escolher Plano</a>
                </div>
                
                <div class="pricing-card featured">
                    <div class="pricing-header">
                        <h3 class="pricing-title">Profissional</h3>
                        <div class="pricing-price">
                            <span class="currency">R$</span>
                            <span class="amount">197</span>
                            <span class="period">/mês</span>
                        </div>
                        <div class="popular-badge">Mais Popular</div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> 5 vídeos por mês</li>
                        <li><i class="fas fa-check"></i> Duração até 3 minutos</li>
                        <li><i class="fas fa-check"></i> 5 revisões</li>
                        <li><i class="fas fa-check"></i> Suporte prioritário</li>
                        <li><i class="fas fa-check"></i> Música personalizada</li>
                        <li><i class="fas fa-check"></i> Narração profissional</li>
                    </ul>
                    <a href="#contact" class="btn btn-primary">Escolher Plano</a>
                </div>
                
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3 class="pricing-title">Enterprise</h3>
                        <div class="pricing-price">
                            <span class="currency">R$</span>
                            <span class="amount">497</span>
                            <span class="period">/mês</span>
                        </div>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check"></i> Vídeos ilimitados</li>
                        <li><i class="fas fa-check"></i> Duração ilimitada</li>
                        <li><i class="fas fa-check"></i> Revisões ilimitadas</li>
                        <li><i class="fas fa-check"></i> Suporte 24/7</li>
                        <li><i class="fas fa-check"></i> Consultoria personalizada</li>
                        <li><i class="fas fa-check"></i> Relatórios de performance</li>
                    </ul>
                    <a href="#contact" class="btn btn-outline">Escolher Plano</a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Pronto para transformar sua marca?</h2>
                <p>Comece hoje mesmo e veja a diferença que vídeos profissionais podem fazer</p>
                <a href="#contact" class="btn btn-primary btn-large">
                    <i class="fas fa-rocket"></i>
                    Começar Agora
                </a>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="faq">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Perguntas Frequentes</h2>
                <p class="section-subtitle">Tire suas dúvidas sobre nossos serviços de criação de vídeos com IA</p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Quanto tempo demora para criar um vídeo?</h3>
                        <div class="faq-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-content">
                            <p>Nosso prazo padrão é de 24 horas para a maioria dos projetos. Vídeos mais complexos podem levar até 48-72 horas. Oferecemos também serviço express de 12 horas com custo adicional.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Posso solicitar revisões no meu vídeo?</h3>
                        <div class="faq-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-content">
                            <p>Sim! Todos os nossos planos incluem revisões. O plano Básico inclui 2 revisões, o Profissional 5 revisões e o Enterprise revisões ilimitadas. Queremos que você fique 100% satisfeito com o resultado.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Que tipos de vídeo vocês criam?</h3>
                        <div class="faq-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-content">
                            <p>Criamos vídeos institucionais, VSL (Video Sales Letter), Reels e Stories para redes sociais, motion graphics, publicidade digital e conteúdo personalizado com IA. Trabalhamos com diversos formatos e estilos.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Como funciona o processo de criação com IA?</h3>
                        <div class="faq-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-content">
                            <p>Utilizamos ferramentas de IA de última geração para acelerar o processo criativo. Você fornece o briefing, nossa IA gera múltiplas opções criativas, e nossa equipe especializada refina e personaliza o resultado final.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Qual formato de arquivo recebo?</h3>
                        <div class="faq-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-content">
                            <p>Entregamos os vídeos em MP4 (Full HD 1080p), otimizados para redes sociais. Também fornecemos versões específicas para cada plataforma (Instagram, YouTube, TikTok) sem custo adicional.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Vocês oferecem garantia de satisfação?</h3>
                        <div class="faq-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-content">
                            <p>Sim! Oferecemos 100% de garantia de satisfação. Se não ficar completamente satisfeito com o resultado final, devolvemos seu dinheiro em até 7 dias após a entrega.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Preciso fornecer algum material?</h3>
                        <div class="faq-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-content">
                            <p>Depende do projeto. Para vídeos institucionais, pedimos logo, cores da marca e texto/roteiro. Para VSL, precisamos do roteiro de vendas. Nossa IA pode gerar imagens e elementos visuais, mas materiais próprios sempre enriquecem o resultado.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Como é feito o pagamento?</h3>
                        <div class="faq-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-content">
                            <p>Aceitamos PIX, cartão de crédito (até 12x), boleto bancário e transferência. Para planos mensais, o pagamento é recorrente. Para projetos avulsos, cobramos 50% no início e 50% na entrega.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Entre em Contato</h2>
                <p class="section-subtitle">Vamos conversar sobre seu projeto</p>
            </div>
            
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-details">
                            <h3>Telefone</h3>
                            <p>+55 (11) 99999-9999</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-details">
                            <h3>Email</h3>
                            <p>contato@centroservice.com.br</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-details">
                            <h3>Endereço</h3>
                            <p>São Paulo, SP - Brasil</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-details">
                            <h3>Horário</h3>
                            <p>Segunda a Sexta: 9h às 18h</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <form id="contactForm" method="POST" action="process_contact.php">
                        <div class="form-group">
                            <input type="text" id="name" name="name" placeholder="Seu nome completo" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="email" id="email" name="email" placeholder="Seu email" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="tel" id="phone" name="phone" placeholder="Seu telefone">
                        </div>
                        
                        <div class="form-group">
                            <select id="service" name="service" required>
                                <option value="">Selecione o serviço</option>
                                <option value="institucional">Vídeo Institucional</option>
                                <option value="vsl">VSL</option>
                                <option value="reels">Reels e Stories</option>
                                <option value="ia">Conteúdo com IA</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <textarea id="message" name="message" placeholder="Conte-nos sobre seu projeto" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-paper-plane"></i>
                            Enviar Mensagem
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="assets/images/logo.png" alt="CentroService Logo">
                        <span>CentroService</span>
                    </div>
                    <p>Transformando marcas através de vídeos profissionais criados com tecnologia de IA de ponta.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Serviços</h3>
                    <ul>
                        <li><a href="#services">Vídeos Institucionais</a></li>
                        <li><a href="#services">VSL</a></li>
                        <li><a href="#services">Reels e Stories</a></li>
                        <li><a href="#services">Conteúdo com IA</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Empresa</h3>
                    <ul>
                        <li><a href="#about">Sobre Nós</a></li>
                        <li><a href="#portfolio">Portfólio</a></li>
                        <li><a href="#pricing">Preços</a></li>
                        <li><a href="#contact">Contato</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Suporte</h3>
                    <ul>
                        <li><a href="#faq">FAQ</a></li>
                        <li><a href="#help">Central de Ajuda</a></li>
                        <li><a href="#terms">Termos de Uso</a></li>
                        <li><a href="#privacy">Política de Privacidade</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 CentroService. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>
