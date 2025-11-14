<?php
// navegacao.php - Menu de navegação lateral (ATUALIZADO)
require_once 'configuracao.php';

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
                    <span></span>
                    <span>Início</span>
                </a>
            </li>
            
            <?php if (e_direcao()): ?>
                <!-- MENU DIREÇÃO -->
                <li style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                    <a href="relatorios.php" class="<?php echo ($pagina_atual === 'relatorios.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Relatórios</span>
                    </a>
                </li>
                
                <li>
                    <a href="clubes.php" class="<?php echo ($pagina_atual === 'clubes.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Clubes</span>
                    </a>
                </li>
                
                <li>
                    <a href="escaloes.php" class="<?php echo ($pagina_atual === 'escaloes.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Escalões</span>
                    </a>
                </li>
                
                <li>
                    <a href="treinadores.php" class="<?php echo ($pagina_atual === 'treinadores.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Treinadores</span>
                    </a>
                </li>
                
                <li>
                    <a href="jogadores.php" class="<?php echo ($pagina_atual === 'jogadores.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Jogadores</span>
                    </a>
                </li>
                
                <li style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                    <a href="quotas.php" class="<?php echo ($pagina_atual === 'quotas.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Quotas</span>
                    </a>
                </li>
                
                <li>
                    <a href="visitantes.php" class="<?php echo ($pagina_atual === 'visitantes.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Visitantes</span>
                    </a>
                </li>
                
                <li>
                    <a href="infraestruturas.php" class="<?php echo ($pagina_atual === 'infraestruturas.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Infraestruturas</span>
                    </a>
                </li>
                
            <?php elseif (e_treinador()): ?>
                <!-- MENU TREINADOR -->
                <li>
                    <a href="escaloes.php" class="<?php echo ($pagina_atual === 'escaloes.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Meus Escalões</span>
                    </a>
                </li>
                
                <li>
                    <a href="jogadores.php" class="<?php echo ($pagina_atual === 'jogadores.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Jogadores</span>
                    </a>
                </li>
                
                <li style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                    <a href="eventos.php" class="<?php echo ($pagina_atual === 'eventos.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Eventos</span>
                    </a>
                </li>
                
                <li>
                    <a href="convocatorias.php" class="<?php echo ($pagina_atual === 'convocatorias.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Convocatórias</span>
                    </a>
                </li>
                
                <li>
                    <a href="convites.php" class="<?php echo ($pagina_atual === 'convites.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Convites</span>
                    </a>
                </li>
                
            <?php elseif (e_jogador()): ?>
                <!-- MENU JOGADOR -->
                <li>
                    <a href="jogadores.php" class="<?php echo ($pagina_atual === 'jogadores.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Colegas de Equipa</span>
                    </a>
                </li>
                
                <li style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                    <a href="meus_eventos.php" class="<?php echo ($pagina_atual === 'meus_eventos.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Meus Eventos</span>
                    </a>
                </li>
                
                <li>
                    <a href="meus_convites.php" class="<?php echo ($pagina_atual === 'meus_convites.php') ? 'ativo' : ''; ?>">
                        <span></span>
                        <span>Meus Convites</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- MENU COMUM A TODOS -->
            <li style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                <a href="perfil.php" class="<?php echo ($pagina_atual === 'perfil.php') ? 'ativo' : ''; ?>">
                    <span></span>
                    <span>Perfil</span>
                </a>
            </li>
            
            <li>
                <a href="terminar_sessao.php" onclick="return confirm('Tem a certeza que deseja sair?');">
                    <span></span>
                    <span>Sair</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>