<?php
// escaloes.php - Gestão de escalões
require_once 'configuracao.php';
requer_autenticacao();

$titulo_pagina = 'Gestão de Escalões';
$subtitulo_pagina = 'Gerir escalões e equipas';

// Adicionar novo escalão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'adicionar') {
    if (e_direcao()) {
        $nome = limpar_entrada($_POST['nome']);
        $id_clube = intval($_POST['id_clube']);
        
        if (empty($nome) || $id_clube <= 0) {
            definir_mensagem('erro', 'Preencha todos os campos.');
        } else {
            try {
                $stmt = $ligacao_bd->prepare("INSERT INTO escaloes (nome, id_clube) VALUES (?, ?)");
                $stmt->execute([$nome, $id_clube]);
                definir_mensagem('sucesso', 'Escalão criado com sucesso!');
                header('Location: escaloes.php');
                exit();
            } catch (PDOException $e) {
                definir_mensagem('erro', 'Erro ao criar escalão: ' . $e->getMessage());
            }
        }
    }
}

// Atualizar escalão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar') {
    if (e_direcao()) {
        $id = intval($_POST['id']);
        $nome = limpar_entrada($_POST['nome']);
        $id_clube = intval($_POST['id_clube']);
        
        if (empty($nome) || $id_clube <= 0) {
            definir_mensagem('erro', 'Preencha todos os campos.');
        } else {
            try {
                $stmt = $ligacao_bd->prepare("UPDATE escaloes SET nome = ?, id_clube = ? WHERE id = ?");
                $stmt->execute([$nome, $id_clube, $id]);
                definir_mensagem('sucesso', 'Escalão atualizado com sucesso!');
                header('Location: escaloes.php');
                exit();
            } catch (PDOException $e) {
                definir_mensagem('erro', 'Erro ao atualizar escalão: ' . $e->getMessage());
            }
        }
    }
}

// Eliminar escalão
if (isset($_GET['eliminar']) && e_direcao()) {
    $id = intval($_GET['eliminar']);
    try {
        $stmt = $ligacao_bd->prepare("DELETE FROM escaloes WHERE id = ?");
        $stmt->execute([$id]);
        definir_mensagem('sucesso', 'Escalão eliminado com sucesso!');
        header('Location: escaloes.php');
        exit();
    } catch (PDOException $e) {
        definir_mensagem('erro', 'Erro ao eliminar escalão. Verifique se não existem dependências.');
    }
}

// Obter escalões
try {
    if (e_direcao()) {
        $stmt = $ligacao_bd->query("
            SELECT e.*, c.nome as clube_nome, 
            (SELECT COUNT(*) FROM jogadores WHERE id_escalao = e.id) as total_jogadores
            FROM escaloes e
            LEFT JOIN clubes c ON e.id_clube = c.id
            ORDER BY c.nome, e.nome
        ");
    } elseif (e_treinador()) {
        $id_treinador = $_SESSION['utilizador_id'];
        $stmt = $ligacao_bd->prepare("
            SELECT e.*, c.nome as clube_nome,
            (SELECT COUNT(*) FROM jogadores WHERE id_escalao = e.id) as total_jogadores
            FROM escaloes e
            INNER JOIN treinadores_escaloes te ON e.id = te.id_escalao
            LEFT JOIN clubes c ON e.id_clube = c.id
            WHERE te.id_treinador = ?
            ORDER BY c.nome, e.nome
        ");
        $stmt->execute([$id_treinador]);
    }
    $escaloes = $stmt->fetchAll();
    
    // Obter clubes para o select
    if (e_direcao()) {
        $stmt = $ligacao_bd->query("SELECT * FROM clubes ORDER BY nome");
        $clubes = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $escaloes = [];
    definir_mensagem('erro', 'Erro ao carregar escalões: ' . $e->getMessage());
}

include 'cabecalho.php';
?>

<?php if (e_direcao()): ?>
    <div class="card" style="margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem; font-size: 1.25rem; font-weight: 700;">
            <?php echo isset($_GET['editar']) ? 'Editar Escalão' : 'Adicionar Novo Escalão'; ?>
        </h2>
        
        <?php
        $escalao_editar = null;
        if (isset($_GET['editar'])) {
            $id_editar = intval($_GET['editar']);
            $stmt = $ligacao_bd->prepare("SELECT * FROM escaloes WHERE id = ?");
            $stmt->execute([$id_editar]);
            $escalao_editar = $stmt->fetch();
        }
        ?>
        
        <form method="POST" class="formulario" style="padding: 0;">
            <input type="hidden" name="acao" value="<?php echo $escalao_editar ? 'editar' : 'adicionar'; ?>">
            <?php if ($escalao_editar): ?>
                <input type="hidden" name="id" value="<?php echo $escalao_editar['id']; ?>">
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="grupo-formulario">
                    <label>Nome do Escalão</label>
                    <input type="text" name="nome" value="<?php echo $escalao_editar ? $escalao_editar['nome'] : ''; ?>" required placeholder="Ex: Sub-15, Juniores A">
                </div>
                
                <div class="grupo-formulario">
                    <label>Clube</label>
                    <select name="id_clube" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($clubes as $clube): ?>
                            <option value="<?php echo $clube['id']; ?>" 
                                <?php echo ($escalao_editar && $escalao_editar['id_clube'] == $clube['id']) ? 'selected' : ''; ?>>
                                <?php echo $clube['nome']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primario">
                    <?php echo $escalao_editar ? '💾 Guardar Alterações' : '➕ Adicionar Escalão'; ?>
                </button>
                <?php if ($escalao_editar): ?>
                    <a href="escaloes.php" class="btn btn-outline">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
<?php endif; ?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.5rem; font-weight: 700;">Lista de Escalões</h2>
    </div>
    
    <?php if (count($escaloes) > 0): ?>
        <div class="tabela-contentor">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Clube</th>
                        <th>Jogadores</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($escaloes as $escalao): ?>
                        <tr>
                            <td><strong><?php echo $escalao['nome']; ?></strong></td>
                            <td><?php echo $escalao['clube_nome']; ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo $escalao['total_jogadores']; ?> jogador(es)</span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="detalhes-escalao.php?id=<?php echo $escalao['id']; ?>" class="btn btn-primario" style="padding: 0.5rem 1rem; font-size: 0.875rem;">👁️ Ver</a>
                                    <?php if (e_direcao()): ?>
                                        <a href="escaloes.php?editar=<?php echo $escalao['id']; ?>" class="btn btn-secundario" style="padding: 0.5rem 1rem; font-size: 0.875rem;">✏️ Editar</a>
                                        <a href="escaloes.php?eliminar=<?php echo $escalao['id']; ?>" 
                                           class="btn btn-perigo" 
                                           style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                                           onclick="return confirmarAcao('Tem a certeza que deseja eliminar este escalão?');">🗑️ Eliminar</a>
                                    <?php endif; ?>
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
            <span>Não existem escalões registados.</span>
        </div>
    <?php endif; ?>
</div>

<?php include 'rodape.php'; ?>