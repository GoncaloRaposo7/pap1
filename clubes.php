<?php
// clubes.php - Gestão de clubes (apenas para direção)
require_once 'configuracao.php';
requer_autenticacao();
requer_direcao();

$titulo_pagina = 'Gestão de Clubes';
$subtitulo_pagina = 'Gerir clubes desportivos';

// Adicionar clube
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'adicionar') {
    $nome = limpar_entrada($_POST['nome']);
    $localizacao = limpar_entrada($_POST['localizacao']);
    $valor_quota = intval($_POST['valor_quota']);
    
    if (empty($nome) || empty($localizacao)) {
        definir_mensagem('erro', 'Preencha todos os campos obrigatórios.');
    } else {
        try {
            $stmt = $ligacao_bd->prepare("INSERT INTO clubes (nome, localizacao, valor_quota) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $localizacao, $valor_quota]);
            definir_mensagem('sucesso', 'Clube criado com sucesso!');
            header('Location: clubes.php');
            exit();
        } catch (PDOException $e) {
            definir_mensagem('erro', 'Erro ao criar clube: ' . $e->getMessage());
        }
    }
}

// Atualizar clube
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar') {
    $id = intval($_POST['id']);
    $nome = limpar_entrada($_POST['nome']);
    $localizacao = limpar_entrada($_POST['localizacao']);
    $valor_quota = intval($_POST['valor_quota']);
    
    if (empty($nome) || empty($localizacao)) {
        definir_mensagem('erro', 'Preencha todos os campos obrigatórios.');
    } else {
        try {
            $stmt = $ligacao_bd->prepare("UPDATE clubes SET nome = ?, localizacao = ?, valor_quota = ? WHERE id = ?");
            $stmt->execute([$nome, $localizacao, $valor_quota, $id]);
            definir_mensagem('sucesso', 'Clube atualizado com sucesso!');
            header('Location: clubes.php');
            exit();
        } catch (PDOException $e) {
            definir_mensagem('erro', 'Erro ao atualizar clube: ' . $e->getMessage());
        }
    }
}

// Eliminar clube
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    try {
        $stmt = $ligacao_bd->prepare("DELETE FROM clubes WHERE id = ?");
        $stmt->execute([$id]);
        definir_mensagem('sucesso', 'Clube eliminado com sucesso!');
        header('Location: clubes.php');
        exit();
    } catch (PDOException $e) {
        definir_mensagem('erro', 'Erro ao eliminar clube. Verifique se não existem dependências (escalões, etc.).');
    }
}

// Obter clubes
try {
    $stmt = $ligacao_bd->query("
        SELECT c.*,
        (SELECT COUNT(*) FROM escaloes WHERE id_clube = c.id) as total_escaloes
        FROM clubes c
        ORDER BY c.nome
    ");
    $clubes = $stmt->fetchAll();
} catch (PDOException $e) {
    $clubes = [];
    definir_mensagem('erro', 'Erro ao carregar clubes: ' . $e->getMessage());
}

include 'cabecalho.php';
?>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1rem; font-size: 1.25rem; font-weight: 700;">
        <?php echo isset($_GET['editar']) ? 'Editar Clube' : 'Adicionar Novo Clube'; ?>
    </h2>
    
    <?php
    $clube_editar = null;
    if (isset($_GET['editar'])) {
        $id_editar = intval($_GET['editar']);
        $stmt = $ligacao_bd->prepare("SELECT * FROM clubes WHERE id = ?");
        $stmt->execute([$id_editar]);
        $clube_editar = $stmt->fetch();
    }
    ?>
    
    <form method="POST" class="formulario" style="padding: 0;">
        <input type="hidden" name="acao" value="<?php echo $clube_editar ? 'editar' : 'adicionar'; ?>">
        <?php if ($clube_editar): ?>
            <input type="hidden" name="id" value="<?php echo $clube_editar['id']; ?>">
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="grupo-formulario">
                <label>Nome do Clube</label>
                <input type="text" name="nome" value="<?php echo $clube_editar ? $clube_editar['nome'] : ''; ?>" required placeholder="Ex: FC Porto">
            </div>
            
            <div class="grupo-formulario">
                <label>Localização</label>
                <input type="text" name="localizacao" value="<?php echo $clube_editar ? $clube_editar['localizacao'] : ''; ?>" required placeholder="Ex: Porto, Portugal">
            </div>
            
            <div class="grupo-formulario">
                <label>Valor da Quota (€)</label>
                <input type="number" name="valor_quota" value="<?php echo $clube_editar ? $clube_editar['valor_quota'] : '0'; ?>" min="0" required>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-primario">
                <?php echo $clube_editar ? '💾 Guardar Alterações' : '➕ Adicionar Clube'; ?>
            </button>
            <?php if ($clube_editar): ?>
                <a href="clubes.php" class="btn btn-outline">Cancelar</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.5rem; font-weight: 700;">Lista de Clubes</h2>
    </div>
    
    <?php if (count($clubes) > 0): ?>
        <div class="tabela-contentor">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Localização</th>
                        <th>Valor Quota</th>
                        <th>Escalões</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clubes as $clube): ?>
                        <tr>
                            <td><strong><?php echo $clube['nome']; ?></strong></td>
                            <td><?php echo $clube['localizacao']; ?></td>
                            <td><?php echo number_format($clube['valor_quota'], 2, ',', '.'); ?>€</td>
                            <td>
                                <span class="badge badge-info"><?php echo $clube['total_escaloes']; ?> escalão(ões)</span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="clubes.php?editar=<?php echo $clube['id']; ?>" class="btn btn-secundario" style="padding: 0.5rem 1rem; font-size: 0.875rem;">✏️ Editar</a>
                                    <a href="clubes.php?eliminar=<?php echo $clube['id']; ?>" 
                                       class="btn btn-perigo" 
                                       style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                                       onclick="return confirmarAcao('Tem a certeza que deseja eliminar este clube? Todos os escalões associados também serão afetados.');">🗑️ Eliminar</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alerta alerta-info">
            <span>ℹ️</span>
            <span>Não existem clubes registados. Crie o primeiro clube acima.</span>
        </div>
    <?php endif; ?>
</div>

<?php include 'rodape.php'; ?>