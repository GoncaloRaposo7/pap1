<?php
// treinadores.php - Gestão de treinadores (apenas para direção)
require_once 'configuracao.php';
requer_autenticacao();
requer_direcao();

$titulo_pagina = 'Gestão de Treinadores';
$subtitulo_pagina = 'Gerir treinadores e atribuir escalões';

// Atribuir escalão a treinador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atribuir_escalao') {
    $id_treinador = intval($_POST['id_treinador']);
    $id_escalao = intval($_POST['id_escalao']);
    
    if ($id_treinador > 0 && $id_escalao > 0) {
        try {
            // Verificar se já existe
            $stmt = $ligacao_bd->prepare("SELECT id FROM treinadores_escaloes WHERE id_treinador = ? AND id_escalao = ?");
            $stmt->execute([$id_treinador, $id_escalao]);
            
            if ($stmt->fetch()) {
                definir_mensagem('erro', 'Este treinador já está atribuído a este escalão.');
            } else {
                $stmt = $ligacao_bd->prepare("INSERT INTO treinadores_escaloes (id_treinador, id_escalao) VALUES (?, ?)");
                $stmt->execute([$id_treinador, $id_escalao]);
                definir_mensagem('sucesso', 'Escalão atribuído com sucesso!');
                header('Location: treinadores.php');
                exit();
            }
        } catch (PDOException $e) {
            definir_mensagem('erro', 'Erro ao atribuir escalão: ' . $e->getMessage());
        }
    }
}

// Remover atribuição
if (isset($_GET['remover'])) {
    $id = intval($_GET['remover']);
    try {
        $stmt = $ligacao_bd->prepare("DELETE FROM treinadores_escaloes WHERE id = ?");
        $stmt->execute([$id]);
        definir_mensagem('sucesso', 'Atribuição removida!');
        header('Location: treinadores.php');
        exit();
    } catch (PDOException $e) {
        definir_mensagem('erro', 'Erro ao remover atribuição.');
    }
}

// Obter treinadores
try {
    $stmt = $ligacao_bd->query("
        SELECT t.id, t.nome, t.email,
        (SELECT COUNT(*) FROM treinadores_escaloes WHERE id_treinador = t.id) as total_escaloes
        FROM treinadores t
        ORDER BY t.nome
    ");
    $treinadores = $stmt->fetchAll();
    
    // Obter escalões para o select
    $stmt = $ligacao_bd->query("
        SELECT e.id, e.nome, c.nome as clube_nome
        FROM escaloes e
        LEFT JOIN clubes c ON e.id_clube = c.id
        ORDER BY c.nome, e.nome
    ");
    $escaloes = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $treinadores = [];
    $escaloes = [];
    definir_mensagem('erro', 'Erro ao carregar dados: ' . $e->getMessage());
}

include 'cabecalho.php';
?>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1rem; font-size: 1.25rem; font-weight: 700;">Atribuir Escalão a Treinador</h2>
    
    <form method="POST" class="formulario" style="padding: 0;">
        <input type="hidden" name="acao" value="atribuir_escalao">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="grupo-formulario">
                <label>Treinador</label>
                <select name="id_treinador" required>
                    <option value="">Selecione o treinador...</option>
                    <?php foreach ($treinadores as $treinador): ?>
                        <option value="<?php echo $treinador['id']; ?>">
                            <?php echo htmlspecialchars($treinador['nome']); ?>
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
                            <?php echo htmlspecialchars($escalao['clube_nome'] . ' - ' . $escalao['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primario" style="margin-top: 1rem;">➕ Atribuir Escalão</button>
    </form>
</div>

<div class="card">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">Lista de Treinadores</h2>
    
    <?php if (count($treinadores) > 0): ?>
        <?php foreach ($treinadores as $treinador): ?>
            <div class="card" style="margin-bottom: 1.5rem; background: var(--cor-fundo);">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                    <div>
                        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">
                            <?php echo htmlspecialchars($treinador['nome']); ?>
                        </h3>
                        <p style="color: var(--cor-texto-claro);">
                            📧 <?php echo htmlspecialchars($treinador['email']); ?>
                        </p>
                    </div>
                    <span class="badge badge-info">
                        <?php echo $treinador['total_escaloes']; ?> escalão(ões)
                    </span>
                </div>
                
                <?php
                // Obter escalões deste treinador
                try {
                    $stmt = $ligacao_bd->prepare("
                        SELECT te.id, e.nome as escalao_nome, c.nome as clube_nome
                        FROM treinadores_escaloes te
                        INNER JOIN escaloes e ON te.id_escalao = e.id
                        LEFT JOIN clubes c ON e.id_clube = c.id
                        WHERE te.id_treinador = ?
                        ORDER BY c.nome, e.nome
                    ");
                    $stmt->execute([$treinador['id']]);
                    $escaloes_treinador = $stmt->fetchAll();
                    
                    if (count($escaloes_treinador) > 0):
                ?>
                    <div style="margin-top: 1rem;">
                        <strong style="display: block; margin-bottom: 0.5rem; color: var(--cor-texto-claro); font-size: 0.875rem;">ESCALÕES ATRIBUÍDOS:</strong>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <?php foreach ($escaloes_treinador as $esc): ?>
                                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: white; padding: 0.5rem 1rem; border-radius: 8px; border: 2px solid var(--cor-borda);">
                                    <span><?php echo htmlspecialchars($esc['clube_nome'] . ' - ' . $esc['escalao_nome']); ?></span>
                                    <a href="treinadores.php?remover=<?php echo $esc['id']; ?>" 
                                       style="color: var(--cor-perigo); text-decoration: none; font-weight: bold;"
                                       onclick="return confirm('Remover esta atribuição?');">✕</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php 
                    else:
                        echo '<p style="color: var(--cor-texto-claro); margin-top: 0.5rem;">Sem escalões atribuídos</p>';
                    endif;
                } catch (PDOException $e) {
                    // Silenciar erro
                }
                ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alerta alerta-info">
            <span>ℹ️</span>
            <span>Não existem treinadores registados.</span>
        </div>
    <?php endif; ?>
</div>

<?php include 'rodape.php'; ?>