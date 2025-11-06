<?php
// painel.php - Dashboard após login (antiga inicio.php)
require_once 'configuracao.php';
requer_autenticacao();

$titulo_pagina = 'Dashboard';
$subtitulo_pagina = 'Bem-vindo ao sistema de gestão';

// Inicializar estatísticas
$estatisticas = array();

try {
    if (e_direcao()) {
        // Estatísticas para direção
        $stmt = $ligacao_bd->query("SELECT COUNT(*) as total FROM clubes");
        $resultado = $stmt->fetch();
        $estatisticas['clubes'] = $resultado ? $resultado['total'] : 0;
        
        $stmt = $ligacao_bd->query("SELECT COUNT(*) as total FROM treinadores");
        $resultado = $stmt->fetch();
        $estatisticas['treinadores'] = $resultado ? $resultado['total'] : 0;
        
        $stmt = $ligacao_bd->query("SELECT COUNT(*) as total FROM jogadores");
        $resultado = $stmt->fetch();
        $estatisticas['jogadores'] = $resultado ? $resultado['total'] : 0;
        
        $stmt = $ligacao_bd->query("SELECT COUNT(*) as total FROM escaloes");
        $resultado = $stmt->fetch();
        $estatisticas['escaloes'] = $resultado ? $resultado['total'] : 0;
        
    } elseif (e_treinador()) {
        // Estatísticas para treinador
        $id_treinador = $_SESSION['utilizador_id'];
        
        $stmt = $ligacao_bd->prepare("SELECT COUNT(*) as total FROM treinadores_escaloes WHERE id_treinador = ?");
        $stmt->execute(array($id_treinador));
        $resultado = $stmt->fetch();
        $estatisticas['escaloes'] = $resultado ? $resultado['total'] : 0;
        
        $stmt = $ligacao_bd->prepare("SELECT COUNT(*) as total FROM eventos WHERE id_treinador = ?");
        $stmt->execute(array($id_treinador));
        $resultado = $stmt->fetch();
        $estatisticas['eventos'] = $resultado ? $resultado['total'] : 0;
        
        $stmt = $ligacao_bd->prepare("SELECT COUNT(*) as total FROM convites WHERE id_treinador = ?");
        $stmt->execute(array($id_treinador));
        $resultado = $stmt->fetch();
        $estatisticas['convites'] = $resultado ? $resultado['total'] : 0;
        
        // Próximos eventos
        $stmt = $ligacao_bd->prepare("
            SELECT * FROM eventos 
            WHERE id_treinador = ? AND data >= NOW() 
            ORDER BY data ASC LIMIT 5
        ");
        $stmt->execute(array($id_treinador));
        $proximos_eventos = $stmt->fetchAll();
        
    } elseif (e_jogador()) {
        // Estatísticas para jogador
        $id_jogador = $_SESSION['utilizador_id'];
        
        $stmt = $ligacao_bd->prepare("
            SELECT COUNT(*) as total FROM convites 
            WHERE id_jogador = ? AND estado = 'pendente'
        ");
        $stmt->execute(array($id_jogador));
        $resultado = $stmt->fetch();
        $estatisticas['convites_pendentes'] = $resultado ? $resultado['total'] : 0;
        
        // Obter escalão do jogador
        $stmt = $ligacao_bd->prepare("SELECT id_escalao FROM jogadores WHERE id = ?");
        $stmt->execute(array($id_jogador));
        $jogador = $stmt->fetch();
        
        if ($jogador && isset($jogador['id_escalao'])) {
            // Próximas convocatórias
            $stmt = $ligacao_bd->prepare("
                SELECT e.*, c.estado
                FROM eventos e
                INNER JOIN convocatorias c ON e.id = c.id_evento
                WHERE c.id_escalao = ? AND e.data >= NOW()
                ORDER BY e.data ASC LIMIT 5
            ");
            $stmt->execute(array($jogador['id_escalao']));
            $proximas_convocatorias = $stmt->fetchAll();
        }
    }
} catch (PDOException $e) {
    definir_mensagem('erro', 'Erro ao carregar estatísticas.');
}

include 'cabecalho.php';
?>

<div class="grelha-cards">
    <?php if (e_direcao()): ?>
        <div class="card card-estatistica">
            <div class="card-titulo">Total de Clubes</div>
            <div class="card-valor"><?php echo isset($estatisticas['clubes']) ? $estatisticas['clubes'] : 0; ?></div>
        </div>
        
        <div class="card card-estatistica secundario">
            <div class="card-titulo">Treinadores</div>
            <div class="card-valor"><?php echo isset($estatisticas['treinadores']) ? $estatisticas['treinadores'] : 0; ?></div>
        </div>
        
        <div class="card card-estatistica destaque">
            <div class="card-titulo">Jogadores</div>
            <div class="card-valor"><?php echo isset($estatisticas['jogadores']) ? $estatisticas['jogadores'] : 0; ?></div>
        </div>
        
        <div class="card card-estatistica perigo">
            <div class="card-titulo">Escalões</div>
            <div class="card-valor"><?php echo isset($estatisticas['escaloes']) ? $estatisticas['escaloes'] : 0; ?></div>
        </div>
        
    <?php elseif (e_treinador()): ?>
        <div class="card card-estatistica">
            <div class="card-titulo">Escalões a Treinar</div>
            <div class="card-valor"><?php echo isset($estatisticas['escaloes']) ? $estatisticas['escaloes'] : 0; ?></div>
        </div>
        
        <div class="card card-estatistica secundario">
            <div class="card-titulo">Eventos Criados</div>
            <div class="card-valor"><?php echo isset($estatisticas['eventos']) ? $estatisticas['eventos'] : 0; ?></div>
        </div>
        
        <div class="card card-estatistica destaque">
            <div class="card-titulo">Convites Enviados</div>
            <div class="card-valor"><?php echo isset($estatisticas['convites']) ? $estatisticas['convites'] : 0; ?></div>
        </div>
        
    <?php elseif (e_jogador()): ?>
        <div class="card card-estatistica destaque">
            <div class="card-titulo">Convites Pendentes</div>
            <div class="card-valor"><?php echo isset($estatisticas['convites_pendentes']) ? $estatisticas['convites_pendentes'] : 0; ?></div>
        </div>
    <?php endif; ?>
</div>

<?php if (e_treinador() && isset($proximos_eventos) && count($proximos_eventos) > 0): ?>
    <div class="card" style="margin-top: 2rem;">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">Próximos Eventos</h2>
        <div class="tabela-contentor">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Data</th>
                        <th>Localização</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proximos_eventos as $evento): ?>
                        <tr>
                            <td>
                                <span class="badge badge-info"><?php echo htmlspecialchars($evento['opcao']); ?></span>
                            </td>
                            <td><?php echo formatar_data_hora($evento['data']); ?></td>
                            <td><?php echo htmlspecialchars($evento['localizacao']); ?></td>
                            <td>
                                <a href="eventos.php" class="btn btn-primario" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Ver Eventos</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if (e_jogador() && isset($proximas_convocatorias) && count($proximas_convocatorias) > 0): ?>
    <div class="card" style="margin-top: 2rem;">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">Próximas Convocatórias</h2>
        <div class="tabela-contentor">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Data</th>
                        <th>Localização</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proximas_convocatorias as $conv): ?>
                        <tr>
                            <td>
                                <span class="badge badge-info"><?php echo htmlspecialchars($conv['opcao']); ?></span>
                            </td>
                            <td><?php echo formatar_data_hora($conv['data']); ?></td>
                            <td><?php echo htmlspecialchars($conv['localizacao']); ?></td>
                            <td>
                                <?php
                                $classe_badge = 'badge-pendente';
                                if ($conv['estado'] === 'confirmado') {
                                    $classe_badge = 'badge-sucesso';
                                } elseif ($conv['estado'] === 'recusado') {
                                    $classe_badge = 'badge-recusado';
                                }
                                ?>
                                <span class="badge <?php echo $classe_badge; ?>"><?php echo ucfirst($conv['estado']); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if (e_direcao()): ?>
    <div class="card" style="margin-top: 2rem;">
        <h2 style="margin-bottom: 1rem; font-size: 1.5rem; font-weight: 700;">Ações Rápidas</h2>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="clubes.php" class="btn btn-primario">➕ Novo Clube</a>
            <a href="escaloes.php" class="btn btn-secundario">➕ Novo Escalão</a>
            <a href="treinadores.php" class="btn btn-outline">👥 Gerir Treinadores</a>
        </div>
    </div>
<?php endif; ?>

<?php if (e_treinador()): ?>
    <div class="card" style="margin-top: 2rem;">
        <h2 style="margin-bottom: 1rem; font-size: 1.5rem; font-weight: 700;">Ações Rápidas</h2>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="eventos.php" class="btn btn-primario">➕ Novo Evento</a>
            <a href="convites.php" class="btn btn-secundario">✉️ Enviar Convite</a>
            <a href="convocatorias.php" class="btn btn-outline">📋 Nova Convocatória</a>
        </div>
    </div>
<?php endif; ?>

<?php include 'rodape.php'; ?>