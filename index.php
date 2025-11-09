<?php
// index.php - Página inicial pública (Landing Page)
require_once 'configuracao.php';

// Se já estiver autenticado, redirecionar para o painel
if (esta_autenticado()) {
    header('Location: painel.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GESTTEAM - Sistema Moderno de Gestão de Clubes Desportivos</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos adicionais específicos para landing */
        .feature-icon-wrapper {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            border-radius: var(--raio);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3);
        }
        
        .feature-icon-wrapper span {
            font-size: 2rem;
        }
        
        .stats-section {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-primaria-escura));
            color: white;
            padding: 5rem 2rem;
            text-align: center;
        }
        
        .stats-grid {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            margin-top: 3rem;
        }
        
        .stat-box {
            padding: 2rem;
        }
        
        .stat-number {
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #ffffff, rgba(255, 255, 255, 0.8));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-label {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }
        
        .cta-section {
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            padding: 7rem 2rem;
            text-align: center;
        }
        
        .cta-box {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 4rem;
            border-radius: var(--raio-xl);
            box-shadow: var(--sombra-xl);
            border: 1px solid var(--cor-borda);
        }
        
        .cta-box h2 {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            color: var(--cor-texto);
        }
        
        .cta-box p {
            font-size: 1.25rem;
            color: var(--cor-texto-claro);
            margin-bottom: 2.5rem;
        }
    </style>
</head>
<body>
    <!-- Navegação -->
    <nav class="navbar-landing">
        <div class="navbar-container">
            <div class="navbar-logo">
                <h1>⚽ GESTTEAM</h1>
            </div>
            <div class="navbar-links">
                <a href="#inicio">Início</a>
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="#sobre">Sobre</a>
                <a href="#contactos">Contactos</a>
                <a href="autenticacao.php" class="btn btn-primario" style="padding: 0.75rem 1.5rem;">Entrar</a>
            </div>
        </div>
    </nav>

    <!-- Secção Hero -->
    <section id="inicio" class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-titulo">Gestão Inteligente do Seu Clube</h1>
                <p class="hero-subtitulo">
                    A plataforma completa para modernizar a gestão do seu clube desportivo. 
                    Organize equipas, eventos, convocatórias e muito mais com eficiência total.
                </p>
                <div class="hero-buttons">
                    <a href="autenticacao.php" class="btn btn-primario btn-grande">🚀 Começar Agora</a>
                    <a href="#funcionalidades" class="btn btn-outline btn-grande">Saber Mais</a>
                </div>
            </div>
            <div class="hero-imagem">
                <div class="hero-card">⚽</div>
            </div>
        </div>
    </section>

    <!-- Estatísticas -->
    <section class="stats-section">
        <div class="container">
            <h2 style="font-size: 2.5rem; font-weight: 900; margin-bottom: 1rem;">Sistema Confiável e Eficiente</h2>
            <p style="font-size: 1.25rem; opacity: 0.9;">Desenvolvido para clubes modernos em Portugal</p>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Gratuito</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">3</div>
                    <div class="stat-label">Tipos de Utilizadores</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Sempre Disponível</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">∞</div>
                    <div class="stat-label">Escalões Ilimitados</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Secção Funcionalidades -->
    <section id="funcionalidades" class="funcionalidades-section">
        <div class="container">
            <h2 class="section-titulo">Funcionalidades Poderosas</h2>
            <p class="section-subtitulo">Tudo o que o seu clube precisa num único sistema integrado</p>
            
            <div class="funcionalidades-grid">
                <!-- Direção do Clube -->
                <div class="funcionalidade-card">
                    <div class="feature-icon-wrapper">
                        <span>👔</span>
                    </div>
                    <h3>Para Direção</h3>
                    <ul class="funcionalidade-lista">
                        <li>✓ Gestão completa de clubes e infraestruturas</li>
                        <li>✓ Criação e gestão de escalões</li>
                        <li>✓ Atribuição de treinadores a equipas</li>
                        <li>✓ Visualização global de jogadores</li>
                        <li>✓ Dashboard com relatórios detalhados</li>
                        <li>✓ Controlo total do sistema</li>
                    </ul>
                </div>

                <!-- Treinadores -->
                <div class="funcionalidade-card destaque">
                    <div class="feature-icon-wrapper" style="background: rgba(255, 255, 255, 0.2);">
                        <span>👨‍🏫</span>
                    </div>
                    <h3>Para Treinadores</h3>
                    <ul class="funcionalidade-lista">
                        <li>✓ Criar e gerir treinos e jogos</li>
                        <li>✓ Sistema de convocatórias inteligente</li>
                        <li>✓ Enviar convites a jogadores</li>
                        <li>✓ Gerir múltiplos escalões simultaneamente</li>
                        <li>✓ Calendário integrado de eventos</li>
                        <li>✓ Comunicação direta com atletas</li>
                    </ul>
                </div>

                <!-- Jogadores -->
                <div class="funcionalidade-card">
                    <div class="feature-icon-wrapper">
                        <span>⚽</span>
                    </div>
                    <h3>Para Jogadores</h3>
                    <ul class="funcionalidade-lista">
                        <li>✓ Ver convocatórias em tempo real</li>
                        <li>✓ Responder a convites rapidamente</li>
                        <li>✓ Consultar calendário de eventos</li>
                        <li>✓ Ver informações dos colegas de equipa</li>
                        <li>✓ Notificações de treinos e jogos</li>
                        <li>✓ Perfil personalizado</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Secção Sobre -->
    <section id="sobre" class="sobre-section">
        <div class="container">
            <div class="sobre-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: center;">
                <div class="sobre-content">
                    <h2 class="section-titulo" style="text-align: left;">Sobre o GESTTEAM</h2>
                    <p class="sobre-texto" style="font-size: 1.125rem; line-height: 1.8;">
                        O <strong>GESTTEAM</strong> é uma plataforma digital inovadora desenvolvida 
                        especificamente para simplificar a gestão de clubes desportivos em Portugal.
                    </p>
                    <p class="sobre-texto" style="font-size: 1.125rem; line-height: 1.8;">
                        Criado com foco na experiência do utilizador e na eficiência operacional, 
                        o sistema conecta direções, treinadores e jogadores numa plataforma unificada 
                        e intuitiva.
                    </p>
                    <div style="margin-top: 2.5rem;">
                        <a href="autenticacao.php" class="btn btn-primario btn-grande">Começar Gratuitamente</a>
                    </div>
                </div>
                <div class="sobre-imagem">
                    <div class="sobre-card" style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 1.5rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                            <span style="font-size: 2rem;">🎯</span> Missão
                        </h4>
                        <p style="font-size: 1.0625rem; line-height: 1.7;">
                            Simplificar e modernizar a gestão de clubes desportivos através da tecnologia, 
                            tornando-a acessível a todos.
                        </p>
                    </div>
                    <div class="sobre-card" style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 1.5rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                            <span style="font-size: 2rem;">💡</span> Visão
                        </h4>
                        <p style="font-size: 1.0625rem; line-height: 1.7;">
                            Ser a plataforma de referência para gestão de clubes desportivos em Portugal 
                            e expandir para toda a Europa.
                        </p>
                    </div>
                    <div class="sobre-card">
                        <h4 style="font-size: 1.5rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                            <span style="font-size: 2rem;">⭐</span> Valores
                        </h4>
                        <p style="font-size: 1.0625rem; line-height: 1.7;">
                            Simplicidade, eficiência, inovação e compromisso total com o 
                            desenvolvimento do desporto.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="cta-box">
            <h2>Pronto para Começar?</h2>
            <p>Junte-se aos clubes que já modernizaram a sua gestão com o GESTTEAM</p>
            <div style="display: flex; gap: 1.25rem; justify-content: center;">
                <a href="autenticacao.php" class="btn btn-primario btn-grande">🚀 Criar Conta Grátis</a>
                <a href="#funcionalidades" class="btn btn-outline btn-grande">Ver Funcionalidades</a>
            </div>
        </div>
    </section>

    <!-- Secção Contactos -->
    <section id="contactos" class="contactos-section" style="padding: 7rem 2rem; background: white;">
        <div class="container">
            <h2 class="section-titulo">Entre em Contacto</h2>
            <p class="section-subtitulo">Tem dúvidas? A nossa equipa está pronta para ajudar!</p>
            
            <div class="contactos-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-bottom: 4rem;">
                <div class="contacto-card" style="text-align: center; padding: 2.5rem; background: var(--cor-fundo); border-radius: var(--raio-lg); border: 1px solid var(--cor-borda);">
                    <div class="feature-icon-wrapper" style="margin: 0 auto 1.5rem;">
                        <span>📧</span>
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">Email</h3>
                    <p style="color: var(--cor-texto-claro); margin-bottom: 0.5rem;">suporte@gestteam.pt</p>
                    <p style="color: var(--cor-texto-claro);">info@gestteam.pt</p>
                </div>
                
                <div class="contacto-card" style="text-align: center; padding: 2.5rem; background: var(--cor-fundo); border-radius: var(--raio-lg); border: 1px solid var(--cor-borda);">
                    <div class="feature-icon-wrapper" style="margin: 0 auto 1.5rem;">
                        <span>📱</span>
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">Telefone</h3>
                    <p style="color: var(--cor-texto-claro); margin-bottom: 0.5rem;">+351 21 XXX XXXX</p>
                    <p style="color: var(--cor-texto-claro);">Segunda a Sexta: 9h-18h</p>
                </div>
                
                <div class="contacto-card" style="text-align: center; padding: 2.5rem; background: var(--cor-fundo); border-radius: var(--raio-lg); border: 1px solid var(--cor-borda);">
                    <div class="feature-icon-wrapper" style="margin: 0 auto 1.5rem;">
                        <span>📍</span>
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">Localização</h3>
                    <p style="color: var(--cor-texto-claro); margin-bottom: 0.5rem;">Lisboa, Portugal</p>
                    <p style="color: var(--cor-texto-claro);">Rua Exemplo, nº 123</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Rodapé -->
    <footer class="footer-landing">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3 style="font-size: 1.5rem; font-weight: 900; margin-bottom: 1rem;">⚽ GESTTEAM</h3>
                    <p style="color: rgba(255, 255, 255, 0.7); line-height: 1.6;">
                        Sistema moderno de gestão para clubes desportivos em Portugal. 
                        Simplifique a gestão do seu clube hoje.
                    </p>
                </div>
                <div class="footer-section">
                    <h4>Links Rápidos</h4>
                    <ul>
                        <li><a href="#inicio">Início</a></li>
                        <li><a href="#funcionalidades">Funcionalidades</a></li>
                        <li><a href="#sobre">Sobre</a></li>
                        <li><a href="autenticacao.php">Entrar</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Suporte</h4>
                    <ul>
                        <li><a href="#contactos">Contactos</a></li>
                        <li><a href="#">Central de Ajuda</a></li>
                        <li><a href="#">Política de Privacidade</a></li>
                        <li><a href="#">Termos de Serviço</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Siga-nos</h4>
                    <ul>
                        <li><a href="#">Facebook</a></li>
                        <li><a href="#">Instagram</a></li>
                        <li><a href="#">LinkedIn</a></li>
                        <li><a href="#">Twitter</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> GESTTEAM. Todos os direitos reservados. Feito com ❤️ em Portugal.</p>
            </div>
        </div>
    </footer>

    <script>
        // Scroll suave para âncoras
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Efeito navbar ao fazer scroll
        let lastScroll = 0;
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-landing');
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                navbar.style.background = 'rgba(15, 23, 42, 0.98)';
                navbar.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
            } else {
                navbar.style.background = 'rgba(15, 23, 42, 0.95)';
                navbar.style.boxShadow = 'none';
            }
            
            lastScroll = currentScroll;
        });

        // Animação dos cards ao aparecer no viewport
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.funcionalidade-card, .contacto-card, .sobre-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>