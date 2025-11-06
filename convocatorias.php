<?php
// convocatorias.php - Gestão de convocatórias
require_once 'configuracao.php';
requer_autenticacao();

if (!e_treinador()) {
    header('Location: inicio.php');
    exit();
}

$titulo_pagina = 'Convocatórias';
$subtitulo_pagina = 'Gerir convocatórias para eventos';

$id_treinador = $_SESSION['utilizador_id'];

// Criar convocatória
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'criar') {
    $id_evento = intval($_POST['id_evento']);
    $id_escalao = intval($_POST['id_escalao']);
    $estado = 'pendente';
    
    if ($id_evento <= 0 || $id_escalao <= 0) {
        definir_mensagem('erro', 'Selecione o evento e o escalão.');
    } else {
        try {
            // Verificar se já existe convocatória
            $stmt = $ligacao_bd->prepare("SELECT id FROM convocatorias WHERE id_evento = ? AND id_escalao = ?");
            $stmt->execute([$id_evento, $id_escalao]);
            
            if ($stmt->fetch()) {
                definir_mensagem('erro', 'Já existe uma convocatória para este evento e escalão.');
            } else {
                $stmt = $ligacao_bd->prepare("INSERT INTO convocatorias (id_evento, id_escalao, estado) VALUES (?, ?, ?)");
                $stmt->execute([$id_evento, $id_escalao, $estado]);
                definir_mensagem('sucesso', 'Convocatória criada com sucesso!');
                header('Location: convocatorias.php');
                exit();
            }
        } catch (PDOException $e) {
            definir_mensagem('erro', 'Erro ao criar convocatória: ' . $e->getMessage());
        }
    }
}

// Atualizar estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar_estado') {
    $id = intval($_POST['id']);
    $estado = limpar_entrada($_POST['estado']);
    
    try {
        $stmt = $ligacao_bd->prepare("UPDATE convocatorias SET estado = ? WHERE id = ?");
        $stmt->execute([$estado, $id]);
        definir_mensagem('sucesso', 'Estado atualizado!');
        header('Location: convocatorias.php');
        exit();
    } catch (PDOException $e) {
        definir_mensagem('erro', 'Erro ao atualizar estado.');
    }
}

// Eliminar convocatória
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    try {
        $stmt = $ligacao_bd->prepare("DELETE FROM convocatorias WHERE id = ?");
        $stmt->execute([$id]);
        definir_mensagem('sucesso', 'Convocatória eliminada!');
        header('Location: convocatorias.php');
        exit();
    } catch (PDOException $e) {
        definir_mensagem('erro', 'Erro ao eliminar convocatória.');
    }
}

// Obter convocatórias
try {
    $stmt = $ligacao_bd->prepare("
        SELECT c.*, e.opcao, e.data, e.localizacao, esc.nome as escalao_nome,
        (SELECT COUNT(*) FROM jogadores WHERE id_escalao = c.id_escalao) as total_jogadores
        FROM convocatorias c
        INNER JOIN eventos e ON c.id_evento = e.id
        INNER JOIN escaloes esc ON c.id_escalao = esc.id
        WHERE e.id_treinador = ?
        ORDER BY e.data DESC
    ");
    $stmt->execute([$id_treinador]);
    $convocatorias = $stmt->fetchAll();
    
    // Obter eventos do treinador
    $stmt = $ligacao_bd->prepare("SELECT * FROM eventos WHERE id_treinador = ? AND data >= NOW() ORDER BY data");
    $stmt->execute([$id_treinador]);
    $eventos = $stmt->fetchAll();
    
    // Obter escalões do treinador
    $stmt = $ligacao_bd->prepare("
        SELECT e.* FROM escaloes e
        INNER JOIN treinadores_escaloes te ON e.id = te.id_escalao
        WHERE te.id_treinador = ?
        ORDER BY e.nome
    ");
    $stmt->execute([$id_treinador]);
    $escaloes = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $convocatorias = [];
    definir_mensagem('erro', 'Erro ao carregar convocatórias: ' . $e->getMessage());
}

include 'cabecalho.php';
?>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1rem; font-size: 1.25rem; font-weight: 700;">Nova Convocatória</h2>
    
    <form method="POST" class="formulario" style="padding: 0;">
        <input type="hidden" name="acao" value="criar">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="grupo-formulario">
                <label>Evento</label>
                <select name="id_evento" required>
                    <option value="">Selecione o evento...</option>
                    <?php foreach ($eventos as $evento): ?>
                        <option value="<?php echo $evento['id']; ?>">
                            <?php echo $evento['opcao'] . ' - ' . formatar_data_hora($evento['data']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="grupo-formulario">
                <label>Escalão</label>
                <select name="id_escalao" required>
                    <option value="">Selecione o escalão...</option>
                    <?php foreach ($escaloes as $escalao): ?>
                        <option value="<?php echo $escalao['id']; ?>">
                            <?php echo $escalao['nome']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primario" style="margin-top: 1rem;">➕ Criar Convocatória</button>
    </form>
</div>

<div class="card">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">Minhas Convocatórias</h2>
    
    <?php if (count($convocatorias) > 0): ?>
        <div class="tabela-contentor">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Data</th>
                        <th>Escalão</th>
                        <th>Jogadores</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($convocatorias as $conv): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?php echo $conv['opcao']; ?></strong>
                                    <div style="font-size: 0.875rem; color: var(--cor-texto-claro);">
                                        <?php echo $conv['localizacao']; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo formatar_data_hora($conv['data']); ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo $conv['escalao_nome']; ?></span>
                            </td>
                            <td><?php echo $conv['total_jogadores']; ?> jogador(es)</td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="atualizar_estado">
                                    <input type="hidden" name="id" value="<?php echo $conv['id']; ?>">
                                    <select name="estado" onchange="this.form.submit()" style="padding: 0.375rem 0.75rem; border-radius: 9999px; border: 2px solid var(--cor-borda);">
                                        <option value="pendente" <?php echo $conv['estado'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="confirmado" <?php echo $conv['estado'] === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                                        <option value="cancelado" <?php echo $conv['estado'] === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <a href="convocatorias.php?eliminar=<?php echo $conv['id']; ?>" 
                                   class="btn btn-perigo" 
                                   style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                                   onclick="return confirmarAcao('Eliminar esta convocatória?');">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alerta alerta-info">
            <span>ℹ️</span>
            <span>Ainda não criou convocatórias. Crie a primeira acima.</span>
        </div>
    <?php endif; ?>
</div>

<?php include 'rodape.php'; ?>