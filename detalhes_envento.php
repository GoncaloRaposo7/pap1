<?php
// detalhes-evento.php - Detalhes de um evento
require_once 'configuracao.php';
requer_autenticacao();

if (!e_treinador()) {
    header('Location: painel.php');
    exit();
}

if (!isset($_GET['id'])) {
    definir_mensagem('erro', 'Evento não especificado.');
    header('Location: eventos.php');
    exit();
}

$id_evento = intval($_GET['id']);
$id_treinador = $_SESSION['utilizador_id'];

// Obter dados do evento
try {
    $stmt = $ligacao_bd->prepare("
        SELECT * FROM eventos 
        WHERE id = ? AND id_treinador = ?
    ");
    $stmt->execute([$id_evento, $id_treinador]);
    $evento = $stmt->fetch();
    
    if (!$evento) {
        definir_mensagem('erro', 'Evento não encontrado ou sem permissão.');
        header('Location: eventos.php');
        exit();
    }
    
    // Obter convocatórias do evento
    $stmt = $ligacao_bd->prepare("
        SELECT c.*, e.nome as escalao_nome,
        (SELECT COUNT(*) FROM jogadores WHERE id_escalao = c.id_escalao) as total_jogadores
        FROM convocatorias c
        INNER JOIN escaloes e ON c.id_escalao = e.id
        WHERE c.id_evento = ?
        ORDER BY e.nome
    ");
    $stmt->execute([$id_evento]);
    $convocatorias = $stmt->fetchAll();
    
    // Para cada convocatória, obter jogadores convocados
    foreach ($convocatorias as &$conv) {
        $stmt = $ligacao_bd->prepare("
            SELECT j.* FROM jogadores j
            WHERE j.id_escalao = ?
            ORDER BY j.nome
        ");
        $stmt->execute([$conv['id_escalao']]);
        $conv['jogadores'] = $stmt->fetchAll();
    }
    
} catch (PDOException $e) {
    definir_mensagem('erro', 'Erro ao carregar dados: ' . $e->getMessage());
    header('Location: eventos.php');
    exit();
}

$titulo_pagina = $evento['opcao'];
$subtitulo_pagina = formatar_data_hora($evento['data']) . ' - ' . $evento['localizacao'];

include 'cabecalho.php';
?>

<!-- Detalhes do Evento -->
<div class="card" style="margin-bottom: 2rem; border-left: 4px solid var(--cor-primaria);">
    <div style="display: flex; justify-content: space-between; align-items: start;">
        <div style="flex: 1;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                <span style="font-size: 3rem;">
                    <?php 
                    echo $evento['opcao'] === 'Jogo' ? '⚽' : 
                         ($evento['opcao'] === 'Treino' ? '🏃' : 
                         ($evento['opcao'] === 'Torneio' ? '🏆' : '📅')); 
                    ?>
                </span>
                <div>
                    <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;">
                        <?php echo $evento['opcao']; ?>
                    </h1>
                    <div style="color: var(--cor-texto-claro); font-size: 1.0625rem;">
                        <?php echo formatar_data_hora($evento['data']); ?>
                    </div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
                <div>
                    <span style="color: var(--cor-texto-claro); font-size: 0.875rem;">📍 Local</span>
                    <div style="font-weight: 600; font-size: 1.125rem; margin-top: 0.25rem;">
                        <?php echo $evento['localizacao']; ?>
                    </div>
                </div>
                <div>
                    <span style="color: var(--cor-texto-claro); font-size: 0.875rem;">📋 Convocatórias</span>
                    <div style="font-weight: 600; font-size: 1.125rem; margin-top: 0.25rem;">
                        <?php echo count($convocatorias); ?> escalão(ões)
                    </div>
                </div>
                <div>
                    <span style="color: var(--cor-texto-claro); font-size: 0.875rem;">⏰ Status</span>
                    <div style="margin-top: 0.25rem;">
                        <?php
                        $agora = time();
                        $data_evento = strtotime($evento['data']);
                        
                        if ($data_evento > $agora) {
                            echo '<span class="badge badge-info" style="font-size: 0.9375rem;">Próximo</span>';
                        } else {
                            echo '<span class="badge" style="background: #94a3b8; color: white; font-size: 0.9375rem;">Passado</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <a href="eventos.php?editar=<?php echo $evento['id']; ?>" class="btn btn-secundario">✏️ Editar</a>
            <a href="eventos.php" class="btn btn-outline">← Voltar</a>
        </div>
    </div>
</div>

<!-- Convocatórias por Escalão -->
<?php if (count($convocatorias) > 0): ?>
    <?php foreach ($convocatorias as $conv): ?>
        <div class="card" style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid var(--cor-borda);">
                <div>
                    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                        🏆 <?php echo $conv['escalao_nome']; ?>
                    </h2>
                    <div style="color: var(--cor-texto-claro);">
                        Total de jogadores: <?php echo $conv['total_jogadores']; ?>
                    </div>
                </div>
                <div>
                    <?php
                    $classe_badge = 'badge-pendente';
                    $texto_estado = 'Pendente';
                    
                    if ($conv['estado'] === 'confirmado') {
                        $classe_badge = 'badge-sucesso';
                        $texto_estado = 'Confirmado';
                    } elseif ($conv['estado'] === 'cancelado') {
                        $classe_badge = 'badge-recusado';
                        $texto_estado = 'Cancelado';
                    }
                    ?>
                    <span class="badge <?php echo $classe_badge; ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                        <?php echo $texto_estado; ?>
                    </span>
                </div>
            </div>
            
            <?php if (count($conv['jogadores']) > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
                    <?php foreach ($conv['jogadores'] as $jogador): ?>
                        <div class="card" style="background: var(--cor-fundo); padding: 1rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div class="avatar" style="width: 45px; height: 45px; font-size: 1.125rem;">
                                    <?php echo strtoupper(substr($jogador['nome'], 0, 1)); ?>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 700; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo $jogador['nome']; ?>
                                    </div>
                                    <div style="color: var(--cor-texto-claro); font-size: 0.875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo $jogador['email']; ?>
                                    </div>
                                </div>
                                <a href="perfil-jogador.php?id=<?php echo $jogador['id']; ?>" 
                                   class="btn btn-primario" 
                                   style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    👁️
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alerta alerta-info">
                    <span>ℹ️</span>
                    <span>Não há jogadores neste escalão.</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card">
        <div class="alerta alerta-info">
            <span>ℹ️</span>
            <span>Este evento ainda não tem convocatórias. <a href="convocatorias.php" style="color: var(--cor-primaria); font-weight: 600;">Criar convocatória</a></span>
        </div>
    </div>
<?php endif; ?>

<!-- Ações Rápidas -->
<div class="card" style="margin-top: 2rem; background: linear-gradient(135deg, var(--cor-fundo), white);">
    <h3 style="margin-bottom: 1rem; font-size: 1.25rem; font-weight: 700;">🚀 Ações Rápidas</h3>
    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="convocatorias.php" class="btn btn-primario">➕ Nova Convocatória</a>
        <a href="eventos.php?editar=<?php echo $evento['id']; ?>" class="btn btn-secundario">✏️ Editar Evento</a>
        <a href="eventos.php?eliminar=<?php echo $evento['id']; ?>" 
           class="btn btn-perigo"
           onclick="return confirmarAcao('Eliminar este evento? Todas as convocatórias também serão eliminadas.');">
            🗑️ Eliminar Evento
        </a>
    </div>
</div>

<?php include 'rodape.php'; ?>