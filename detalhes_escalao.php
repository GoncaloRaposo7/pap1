<?php
// detalhes-escalao.php - Detalhes de um escalão específico
require_once 'configuracao.php';
requer_autenticacao();

if (!isset($_GET['id'])) {
    definir_mensagem('erro', 'Escalão não especificado.');
    header('Location: escaloes.php');
    exit();
}

$id_escalao = intval($_GET['id']);

// Obter dados do escalão
try {
    $stmt = $ligacao_bd->prepare("
        SELECT e.*, c.nome as clube_nome, c.localizacao, c.valor_quota
        FROM escaloes e
        LEFT JOIN clubes c ON e.id_clube = c.id
        WHERE e.id = ?
    ");
    $stmt->execute([$id_escalao]);
    $escalao = $stmt->fetch();
    
    if (!$escalao) {
        definir_mensagem('erro', 'Escalão não encontrado.');
        header('Location: escaloes.php');
        exit();
    }
    
    // Obter jogadores do escalão
    $stmt = $ligacao_bd->prepare("
        SELECT * FROM jogadores 
        WHERE id_escalao = ? 
        ORDER BY nome
    ");
    $stmt->execute([$id_escalao]);
    $jogadores = $stmt->fetchAll();
    
    // Obter treinadores do escalão
    $stmt = $ligacao_bd->prepare("
        SELECT t.* FROM treinadores t
        INNER JOIN treinadores_escaloes te ON t.id = te.id_treinador
        WHERE te.id_escalao = ?
        ORDER BY t.nome
    ");
    $stmt->execute([$id_escalao]);
    $treinadores = $stmt->fetchAll();
    
    // Obter próximos eventos (se houver treinadores)
    $proximos_eventos = [];
    if (count($treinadores) > 0) {
        $ids_treinadores = array_column($treinadores, 'id');
        $placeholders = str_repeat('?,', count($ids_treinadores) - 1) . '?';
        
        $stmt = $ligacao_bd->prepare("
            SELECT e.*, c.estado, t.nome as treinador_nome
            FROM eventos e
            INNER JOIN convocatorias c ON e.id = c.id_evento
            INNER JOIN treinadores t ON e.id_treinador = t.id
            WHERE c.id_escalao = ? AND e.data >= NOW()
            AND e.id_treinador IN ($placeholders)
            ORDER BY e.data ASC
            LIMIT 5
        ");
        $params = array_merge([$id_escalao], $ids_treinadores);
        $stmt->execute($params);
        $proximos_eventos = $stmt->fetchAll();
    }
    
} catch (PDOException $e) {
    definir_mensagem('erro', 'Erro ao carregar dados: ' . $e->getMessage());
    header('Location: escaloes.php');
    exit();
}

$titulo_pagina = $escalao['nome'];
$subtitulo_pagina = $escalao['clube_nome'];

include 'cabecalho.php';
?>

<!-- Informações do Escalão -->
<div class="card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: start;">
        <div>
            <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 1rem;">
                <?php echo $escalao['nome']; ?>
            </h2>
            <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                <div>
                    <span style="color: var(--cor-texto-claro); font-size: 0.875rem;">🏢 Clube</span>
                    <div style="font-weight: 600; font-size: 1.125rem;"><?php echo $escalao['clube_nome']; ?></div>
                </div>
                <div>
                    <span style="color: var(--cor-texto-claro); font-size: 0.875rem;">📍 Localização</span>
                    <div style="font-weight: 600; font-size: 1.125rem;"><?php echo $escalao['localizacao']; ?></div>
                </div>
                <div>
                    <span style="color: var(--cor-texto-claro); font-size: 0.875rem;">💰 Quota</span>
                    <div style="font-weight: 600; font-size: 1.125rem;"><?php echo $escalao['valor_quota']; ?>€</div>
                </div>
            </div>
        </div>
        <a href="escaloes.php" class="btn btn-outline">← Voltar</a>
    </div>
</div>

<!-- Estatísticas -->
<div class="grelha-cards">
    <div class="card card-estatistica">
        <div class="card-titulo">Total de Jogadores</div>
        <div class="card-valor"><?php echo count($jogadores); ?></div>
    </div>
    
    <div class="card card-estatistica secundario">
        <div class="card-titulo">Treinadores</div>
        <div class="card-valor"><?php echo count($treinadores); ?></div>
    </div>
    
    <div class="card card-estatistica destaque">
        <div class="card-titulo">Próximos Eventos</div>
        <div class="card-valor"><?php echo count($proximos_eventos); ?></div>
    </div>
</div>

<!-- Treinadores -->
<?php if (count($treinadores) > 0): ?>
<div class="card" style="margin-top: 2rem;">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">👨‍🏫 Treinadores</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
        <?php foreach ($treinadores as $treinador): ?>
            <div class="card" style="background: var(--cor-fundo); border-left: 4px solid var(--cor-primaria);">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div class="avatar" style="width: 50px; height: 50px; font-size: 1.25rem;">
                        <?php echo strtoupper(substr($treinador['nome'], 0, 1)); ?>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 1.125rem;"><?php echo $treinador['nome']; ?></div>
                        <div style="color: var(--cor-texto-claro); font-size: 0.875rem;"><?php echo $treinador['email']; ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Próximos Eventos -->
<?php if (count($proximos_eventos) > 0): ?>
<div class="card" style="margin-top: 2rem;">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">📅 Próximos Eventos</h2>
    <div class="tabela-contentor">
        <table class="tabela">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Data</th>
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

<!-- Jogadores -->
<div class="card" style="margin-top: 2rem;">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">⚽ Jogadores</h2>
    
    <?php if (count($jogadores) > 0): ?>
        <div class="tabela-contentor">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jogadores as $jogador): ?>
                        <tr>
                            <td><strong><?php echo $jogador['nome']; ?></strong></td>
                            <td><?php echo $jogador['email']; ?></td>
                            <td>
                                <a href="perfil-jogador.php?id=<?php echo $jogador['id']; ?>" 
                                   class="btn btn-primario" 
                                   style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    👁️ Ver Perfil
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alerta alerta-info">
            <span>ℹ️</span>
            <span>Ainda não há jogadores neste escalão.</span>
        </div>
    <?php endif; ?>
</div>

<?php include 'rodape.php'; ?>