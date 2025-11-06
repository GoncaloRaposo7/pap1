<?php
// navegacao.php - Menu de navegação lateral
require_once 'configuracao.php'; // garante acesso às funções e constantes

$pagina_atual = basename($_SERVER['PHP_SELF']);
?>
<aside class="barra-lateral">
    <div class="logo-lateral">
        <h2> GESTEAM </h2>
        <p style="color: rgba(255,255,255,0.5); font-size: 0.875rem; margin-top: 0.5rem;">Gestão de Clubes</p>
    </div>
    
    <nav>
        <ul class="menu-navegacao">
            <li>
                <a href="painel.php" class="<?php echo ($pagina_atual === 'painel.php') ? 'ativo' : ''; ?>">
                    <span>🏠</span>
                    <span>Início</span>
                </a>
            </li>
            
            <?php if (e_direcao()): ?>
                <li>
                    <a href="clubes.php" class="<?php echo ($pagina_atual === 'clubes.php') ? 'ativo' : ''; ?>">
                        <span>🏢</span>
                        <span>Clubes</span>
                    </a>
                </li>
                <li>
                    <a href="treinadores.php" class="<?php echo ($pagina_atual === 'treinadores.php') ? 'ativo' : ''; ?>">
                        <span>👨‍🏫</span>
                        <span>Treinadores</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (e_direcao() || e_treinador()): ?>
                <li>
                    <a href="escaloes.php" class="<?php echo ($pagina_atual === 'escaloes.php') ? 'ativo' : ''; ?>">
                        <span>👥</span>
                        <span>Escalões</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <li>
                <a href="jogadores.php" class="<?php echo ($pagina_atual === 'jogadores.php') ? 'ativo' : ''; ?>">
                    <span>⚽</span>
                    <span>Jogadores</span>
                </a>
            </li>
            
            <?php if (e_treinador()): ?>
                <li>
                    <a href="eventos.php" class="<?php echo ($pagina_atual === 'eventos.php') ? 'ativo' : ''; ?>">
                        <span>📅</span>
                        <span>Eventos</span>
                    </a>
                </li>
                <li>
                    <a href="convocatorias.php" class="<?php echo ($pagina_atual === 'convocatorias.php') ? 'ativo' : ''; ?>">
                        <span>📋</span>
                        <span>Convocatórias</span>
                    </a>
                </li>
                <li>
                    <a href="convites.php" class="<?php echo ($pagina_atual === 'convites.php') ? 'ativo' : ''; ?>">
                        <span>✉️</span>
                        <span>Convites</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (e_jogador()): ?>
                <li>
                    <a href="meus_eventos.php" class="<?php echo ($pagina_atual === 'meus_eventos.php') ? 'ativo' : ''; ?>">
                        <span>📅</span>
                        <span>Meus Eventos</span>
                    </a>
                </li>
                <li>
                    <a href="meus_convites.php" class="<?php echo ($pagina_atual === 'meus_convites.php') ? 'ativo' : ''; ?>">
                        <span>✉️</span>
                        <span>Meus Convites</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <li style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                <a href="perfil.php" class="<?php echo ($pagina_atual === 'perfil.php') ? 'ativo' : ''; ?>">
                    <span>👤</span>
                    <span>Perfil</span>
                </a>
            </li>
            
            <li>
                <a href="terminar_sessao.php" onclick="return confirm('Tem a certeza que deseja sair?');">
                    <span>🚪</span>
                    <span>Sair</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
