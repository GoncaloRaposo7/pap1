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
    <title>GESTTEAM - Sistema de Gestão de Clubes Desportivos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navegação -->
    <nav class="navbar-landing">
        <div class="navbar-container">
            <div class="navbar-logo">
                <h1> GESTTEAM</h1>
            </div>
            <div class="navbar-links">
                <a href="#inicio">Início</a>
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="#sobre">Sobre</a>
                <a href="#contactos">Contactos</a>
                <a href="autenticacao.php" class="btn btn-primario">Entrar</a>
            </div>
        </div>
    </nav>

    <!-- Secção Hero -->
    <section id="inicio" class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-titulo">Gestão de Clubes Simplificada</h1>
                <p class="hero-subtitulo">
                    Plataforma completa para gerir o seu clube desportivo. 
                    Organize equipas, eventos, convocatórias e muito mais, tudo num único lugar.
                </p>
                <div class="hero-buttons">
                    <a href="autenticacao.php" class="btn btn-primario btn-grande">Começar Agora</a>
                    <a href="#funcionalidades" class="btn btn-outline btn-grande">Saber Mais</a>
                </div>
            </div>
            <div class="hero-imagem">
                <div class="hero-card">⚽</div>
            </div>
        </div>
    </section>

    <!-- Secção Funcionalidades -->
    <section id="funcionalidades" class="funcionalidades-section">
        <div class="container">
            <h2 class="section-titulo">Funcionalidades Principais</h2>
            <p class="section-subtitulo">Tudo o que precisa para gerir o seu clube de forma eficiente</p>
            
            <div class="funcionalidades-grid">
                <!-- Direção do Clube -->
                <div class="funcionalidade-card">
                    <div class="funcionalidade-icon">👔</div>
                    <h3>Para Direção</h3>
                    <ul class="funcionalidade-lista">
                        <li>✓ Gestão completa de clubes</li>
                        <li>✓ Criação e gestão de escalões</li>
                        <li>✓ Atribuição de treinadores</li>
                        <li>✓ Visualização de todos os jogadores</li>
                        <li>✓ Relatórios e estatísticas</li>
                    </ul>
                </div>

                <!-- Treinadores -->
                <div class="funcionalidade-card destaque">
                    <div class="funcionalidade-icon">👨‍🏫</div>
                    <h3>Para Treinadores</h3>
                    <ul class="funcionalidade-lista">
                        <li>✓ Criar treinos e jogos</li>
                        <li>✓ Fazer convocatórias</li>
                        <li>✓ Enviar convites a jogadores</li>
                        <li>✓ Gerir múltiplos escalões</li>
                        <li>✓ Calendário de eventos</li>
                    </ul>
                </div>

                <!-- Jogadores -->
                <div class="funcionalidade-card">
                    <div class="funcionalidade-icon">⚽</div>
                    <h3>Para Jogadores</h3>
                    <ul class="funcionalidade-lista">
                        <li>✓ Ver convocatórias</li>
                        <li>✓ Responder a convites</li>
                        <li>✓ Consultar calendário</li>
                        <li>✓ Ver colegas de equipa</li>
                        <li>✓ Notificações de eventos</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Secção Sobre -->
    <section id="sobre" class="sobre-section">
        <div class="container">
            <div class="sobre-grid">
                <div class="sobre-content">
                    <h2 class="section-titulo">Sobre o Sistema</h2>
                    <p class="sobre-texto">
                        O <strong><?php echo NOME_SITE; ?></strong> é uma plataforma digital desenvolvida 
                        para facilitar a gestão de clubes desportivos em Portugal.
                    </p>
                    <p class="sobre-texto">
                        Criado com foco na simplicidade e eficiência, o sistema permite que direções, 
                        treinadores e jogadores estejam sempre conectados e informados sobre eventos, 
                        treinos e convocatórias.
                    </p>
                    <div class="sobre-stats">
                        <div class="stat-item">
                            <div class="stat-numero">3</div>
                            <div class="stat-label">Tipos de Utilizadores</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-numero">100%</div>
                            <div class="stat-label">Gratuito</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-numero">24/7</div>
                            <div class="stat-label">Disponível</div>
                        </div>
                    </div>
                </div>
                <div class="sobre-imagem">
                    <div class="sobre-card">
                        <h4>🎯 Missão</h4>
                        <p>Simplificar a gestão de clubes desportivos através da tecnologia.</p>
                    </div>
                    <div class="sobre-card">
                        <h4>💡 Visão</h4>
                        <p>Ser a plataforma de referência para clubes em Portugal.</p>
                    </div>
                    <div class="sobre-card">
                        <h4>⭐ Valores</h4>
                        <p>Simplicidade, eficiência e compromisso com o desporto.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Secção Contactos -->
    <section id="contactos" class="contactos-section">
        <div class="container">
            <h2 class="section-titulo">Entre em Contacto</h2>
            <p class="section-subtitulo">Tem dúvidas? Estamos aqui para ajudar!</p>
            
            <div class="contactos-grid">
                <div class="contacto-card">
                    <div class="contacto-icon">📧</div>
                    <h3>Email</h3>
                    <p>suporte@gestaoclube.pt</p>
                    <p>info@gestaoclube.pt</p>
                </div>
                
                <div class="contacto-card">
                    <div class="contacto-icon">📱</div>
                    <h3>Telefone</h3>
                    <p>+351 21 XXX XXXX</p>
                    <p>Segunda a Sexta: 9h-18h</p>
                </div>
                
                <div class="contacto-card">
                    <div class="contacto-icon">📍</div>
                    <h3>Localização</h3>
                    <p>Lisboa, Portugal</p>
                    <p>Rua Exemplo, nº 123</p>
                </div>
            </div>

            <div class="contacto-form-container">
                <form class="contacto-form" method="POST" action="#">
                    <h3>Envie-nos uma Mensagem</h3>
                    <div class="form-row">
                        <div class="grupo-formulario">
                            <label>Nome</label>
                            <input type="text" name="nome" required>
                        </div>
                        <div class="grupo-formulario">
                            <label>Email</label>
                            <input type="email" name="email" required>
                        </div>
                    </div>
                    <div class="grupo-formulario">
                        <label>Assunto</label>
                        <input type="text" name="assunto" required>
                    </div>
                    <div class="grupo-formulario">
                        <label>Mensagem</label>
                        <textarea name="mensagem" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primario">Enviar Mensagem</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Rodapé -->
    <footer class="footer-landing">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3><?php echo NOME_SITE; ?></h3>
                    <p>Sistema de gestão para clubes desportivos em Portugal.</p>
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
                        <li><a href="#">Ajuda</a></li>
                        <li><a href="#">Política de Privacidade</a></li>
                        <li><a href="#">Termos de Uso</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Siga-nos</h4>
                    <div class="social-links">
                        <a href="#">Facebook</a>
                        <a href="#">Instagram</a>
                        <a href="#">LinkedIn</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo NOME_SITE; ?>. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Scroll suave
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

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-landing');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(15, 23, 42, 0.95)';
                navbar.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
            } else {
                navbar.style.background = 'rgba(15, 23, 42, 0.9)';
                navbar.style.boxShadow = 'none';
            }
        });
    </script>
</body>
</html>