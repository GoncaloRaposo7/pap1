<?php
// perfil-jogador.php - Perfil público de um jogador
require_once 'configuracao.php';
requer_autenticacao();

if (!isset($_GET['id'])) {
    definir_mensagem('erro', 'Jogador não especificado.');
    header('Location: jogadores.php');
    exit();
}

$id_jogador = intval($_GET['id']);

// Obter dados do jogador
try {
    $stmt = $ligacao_bd->prepare("
        SELECT j.*, e.nome as escalao_nome, c.nome as clube_nome
        FROM jogadores j
        LEFT JOIN escaloes e ON j.id_escalao = e.id
        LEFT JOIN clubes c ON e.id_clube = c.id
        WHERE j.id = ?
    ");
    $stmt->execute([$id_jogador]);
    $jogador = $stmt->fetch();
    
    if (!$jogador) {
        definir_mensagem('erro', 'Jogador não encontrado.');
        header('Location: jogadores.php');
        exit();
    }
    
    // Obter estatísticas do jogador
    $stmt = $ligacao_bd->prepare("
        SELECT COUNT(*) as total FROM convites 
        WHERE id_jogador = ? AND estado = 'aceite'
    ");
    $stmt->execute([$id_jogador]);
    $convites_aceites = $stmt->fetch()['total'];
    
    $stmt = $ligacao_bd->prepare("
        SELECT COUNT(*) as total FROM convites 
        WHERE id_jogador = ? AND estado = 'recusado'
    ");
    $stmt->execute([$id_jogador]);
    $convites_recusados = $stmt->fetch()['total'];
    
    $stmt = $ligacao_bd->prepare("
        SELECT COUNT(*) as total FROM convites 
        WHERE id_jogador = ? AND estado = 'pendente'
    ");
    $stmt->execute([$id_jogador]);
    $convites_pendentes = $stmt->fetch()['total'];
    
    // Obter próximos eventos do escalão
    $stmt = $ligacao_bd->prepare("
        SELECT e.*, c.estado, t.nome as treinador_nome
        FROM eventos e
        INNER JOIN convocatorias c ON e.id = c.id_evento
        INNER JOIN treinadores t ON e.id_treinador = t.id
        WHERE c.id_escalao = ? AND e.data >= NOW()
        ORDER BY e.data ASC
        LIMIT 5
    ");
    $stmt->execute([$jogador['id_escalao']]);
    $proximos_eventos = $stmt->fetchAll();
    
    // Obter colegas de equipa
    $stmt = $ligacao_bd->prepare("
        SELECT * FROM jogadores 
        WHERE id_escalao = ? AND id != ?
        ORDER BY nome
        LIMIT 10
    ");
    $stmt->execute([$jogador['id_escalao'], $id_jogador]);
    $colegas = $stmt->fetchAll();
    
} catch (PDOException $e) {
    definir_mensagem('erro', 'Erro ao carregar dados: ' . $e->getMessage());
    header('Location: jogadores.php');
    exit();
}

$titulo_pagina = 'Perfil de ' . $jogador['nome'];
$subtitulo_pagina = $jogador['escalao_nome'] . ' - ' . $jogador['clube_nome'];

include 'cabecalho.php';
?>

<!-- Cabeçalho do Perfil -->
<div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, var(--cor-primaria), var(--cor-primaria-escura)); color: white;">
    <div style="display: flex; align-items: center; gap: 2rem;">
        <div class="avatar" style="width: 100px; height: 100px; font-size: 2.5rem; border: 4px solid white;">
            <?php echo strtoupper(substr($jogador['nome'], 0, 1)); ?>
        </div>
        <div style="flex: 1;">
            <h1 style="font-size: 2.5rem; font-weight: 900; margin-bottom: 0.5rem; color: white;">
                <?php echo $jogador['nome']; ?>
            </h1>
            <div style="display: flex; gap: 2rem; margin-top: 1rem; flex-wrap: wrap;">
                <div>
                    <span style="opacity: 0.9; font-size: 0.875rem;">📧 Email</span>
                    <div style="font-weight: 600; font-size: 1.125rem;"><?php echo $jogador['email']; ?></div>
                </div>
                <div>
                    <span style="opacity: 0.9; font-size: 0.875rem;">🏆 Escalão</span>
                    <div style="font-weight: 600; font-size: 1.125rem;"><?php echo $jogador['escalao_nome']; ?></div>
                </div>
                <div>
                    <span style="opacity: 0.9; font-size: 0.875rem;">🏢 Clube</span>
                    <div style="font-weight: 600; font-size: 1.125rem;"><?php echo $jogador['clube_nome']; ?></div>
                </div>
            </div>
        </div>
        <a href="jogadores.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">← Voltar</a>
    </div>
</div>

<!-- Estatísticas -->
<div class="grelha-cards">
    <div class="card card-estatistica secundario">
        <div class="card-titulo">Convites Aceites</div>
        <div class="card-valor"><?php echo $convites_aceites; ?></div>
    </div>
    
    <div class="card card-estatistica perigo">
        <div class="card-titulo">Convites Recusados</div>
        <div class="card-valor"><?php echo $convites_recusados; ?></div>
    </div>
    
    <div class="card card-estatistica destaque">
        <div class="card-titulo">Convites Pendentes</div>
        <div class="card-valor"><?php echo $convites_pendentes; ?></div>
    </div>
</div>

<!-- Próximos Eventos -->
<?php if (count($proximos_eventos) > 0): ?>
<div class="card" style="margin-top: 2rem;">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">📅 Próximos Eventos</h2>
    <div class="tabela-contentor">
        <table class="tabela">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Data e Hora</th>
                    <th>Local</th>
                    <th>Treinador</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proximos_eventos as $evento): ?>
                    <tr>
                        <td>
                            <span class="badge badge-info"><?php echo $evento['opcao']; ?></span>
                        </td>
                        <td><?php echo formatar_data_hora($evento['data']); ?></td>
                        <td><?php echo $evento['localizacao']; ?></td>
                        <td><?php echo $evento['treinador_nome']; ?></td>
                        <td>
                            <?php
                            $classe_badge = 'badge-pendente';
                            if ($evento['estado'] === 'confirmado') $classe_badge = 'badge-sucesso';
                            elseif ($evento['estado'] === 'cancelado') $classe_badge = 'badge-recusado';
                            ?>
                            <span class="badge <?php echo $classe_badge; ?>">
                                <?php echo ucfirst($evento['estado']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Colegas de Equipa -->
<?php if (count($colegas) > 0): ?>
<div class="card" style="margin-top: 2rem;">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">👥 Colegas de Equipa</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem;">
        <?php foreach ($colegas as $colega): ?>
            <a href="perfil-jogador.php?id=<?php echo $colega['id']; ?>" 
               class="card" 
               style="background: var(--cor-fundo); text-decoration: none; color: inherit; transition: var(--transicao);"
               onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--sombra-md)';"
               onmouseout="this.style.transform=''; this.style.boxShadow='var(--sombra)';">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div class="avatar" style="width: 45px; height: 45px; font-size: 1.125rem;">
                        <?php echo strtoupper(substr($colega['nome'], 0, 1)); ?>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 700; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo $colega['nome']; ?>
                        </div>
                        <div style="color: var(--cor-texto-claro); font-size: 0.875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo $colega['email']; ?>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php include 'rodape.php'; ?>