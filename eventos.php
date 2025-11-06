<?php
// eventos.php - Gestão de eventos
require_once 'configuracao.php';
requer_autenticacao();

if (!e_treinador()) {
    header('Location: inicio.php');
    exit();
}

$titulo_pagina = 'Gestão de Eventos';
$subtitulo_pagina = 'Criar e gerir treinos e jogos';

$id_treinador = $_SESSION['utilizador_id'];

// Adicionar novo evento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'adicionar') {
    $opcao = limpar_entrada($_POST['opcao']);
    $data = $_POST['data'];
    $localizacao = limpar_entrada($_POST['localizacao']);
    
    if (empty($opcao) || empty($data) || empty($localizacao)) {
        definir_mensagem('erro', 'Preencha todos os campos.');
    } else {
        try {
            $stmt = $ligacao_bd->prepare("INSERT INTO eventos (id_treinador, opcao, data, localizacao) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_treinador, $opcao, $data, $localizacao]);
            definir_mensagem('sucesso', 'Evento criado com sucesso!');
            header('Location: eventos.php');
            exit();
        } catch (PDOException $e) {
            definir_mensagem('erro', 'Erro ao criar evento: ' . $e->getMessage());
        }
    }
}

// Atualizar evento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar') {
    $id = intval($_POST['id']);
    $opcao = limpar_entrada($_POST['opcao']);
    $data = $_POST['data'];
    $localizacao = limpar_entrada($_POST['localizacao']);
    
    if (empty($opcao) || empty($data) || empty($localizacao)) {
        definir_mensagem('erro', 'Preencha todos os campos.');
    } else {
        try {
            $stmt = $ligacao_bd->prepare("UPDATE eventos SET opcao = ?, data = ?, localizacao = ? WHERE id = ? AND id_treinador = ?");
            $stmt->execute([$opcao, $data, $localizacao, $id, $id_treinador]);
            definir_mensagem('sucesso', 'Evento atualizado com sucesso!');
            header('Location: eventos.php');
            exit();
        } catch (PDOException $e) {
            definir_mensagem('erro', 'Erro ao atualizar evento: ' . $e->getMessage());
        }
    }
}

// Eliminar evento
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    try {
        $stmt = $ligacao_bd->prepare("DELETE FROM eventos WHERE id = ? AND id_treinador = ?");
        $stmt->execute([$id, $id_treinador]);
        definir_mensagem('sucesso', 'Evento eliminado com sucesso!');
        header('Location: eventos.php');
        exit();
    } catch (PDOException $e) {
        definir_mensagem('erro', 'Erro ao eliminar evento.');
    }
}

// Obter eventos do treinador
try {
    $stmt = $ligacao_bd->prepare("
        SELECT e.*,
        (SELECT COUNT(*) FROM convocatorias WHERE id_evento = e.id) as total_convocatorias
        FROM eventos e
        WHERE e.id_treinador = ?
        ORDER BY e.data DESC
    ");
    $stmt->execute([$id_treinador]);
    $eventos = $stmt->fetchAll();
} catch (PDOException $e) {
    $eventos = [];
    definir_mensagem('erro', 'Erro ao carregar eventos: ' . $e->getMessage());
}

include 'cabecalho.php';
?>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1rem; font-size: 1.25rem; font-weight: 700;">
        <?php echo isset($_GET['editar']) ? 'Editar Evento' : 'Criar Novo Evento'; ?>
    </h2>
    
    <?php
    $evento_editar = null;
    if (isset($_GET['editar'])) {
        $id_editar = intval($_GET['editar']);
        $stmt = $ligacao_bd->prepare("SELECT * FROM eventos WHERE id = ? AND id_treinador = ?");
        $stmt->execute([$id_editar, $id_treinador]);
        $evento_editar = $stmt->fetch();
    }
    ?>
    
    <form method="POST" class="formulario" style="padding: 0;">
        <input type="hidden" name="acao" value="<?php echo $evento_editar ? 'editar' : 'adicionar'; ?>">
        <?php if ($evento_editar): ?>
            <input type="hidden" name="id" value="<?php echo $evento_editar['id']; ?>">
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="grupo-formulario">
                <label>Tipo de Evento</label>
                <select name="opcao" required>
                    <option value="">Selecione...</option>
                    <option value="Treino" <?php echo ($evento_editar && $evento_editar['opcao'] === 'Treino') ? 'selected' : ''; ?>>Treino</option>
                    <option value="Jogo" <?php echo ($evento_editar && $evento_editar['opcao'] === 'Jogo') ? 'selected' : ''; ?>>Jogo</option>
                    <option value="Torneio" <?php echo ($evento_editar && $evento_editar['opcao'] === 'Torneio') ? 'selected' : ''; ?>>Torneio</option>
                    <option value="Outro" <?php echo ($evento_editar && $evento_editar['opcao'] === 'Outro') ? 'selected' : ''; ?>>Outro</option>
                </select>
            </div>
            
            <div class="grupo-formulario">
                <label>Data e Hora</label>
                <input type="datetime-local" name="data" 
                       value="<?php echo $evento_editar ? date('Y-m-d\TH:i', strtotime($evento_editar['data'])) : ''; ?>" 
                       required>
            </div>
            
            <div class="grupo-formulario">
                <label>Localização</label>
                <input type="text" name="localizacao" 
                       value="<?php echo $evento_editar ? $evento_editar['localizacao'] : ''; ?>" 
                       required 
                       placeholder="Ex: Campo Principal">
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-primario">
                <?php echo $evento_editar ? '💾 Guardar Alterações' : '➕ Criar Evento'; ?>
            </button>
            <?php if ($evento_editar): ?>
                <a href="eventos.php" class="btn btn-outline">Cancelar</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.5rem; font-weight: 700;">Meus Eventos</h2>
    </div>
    
    <?php if (count($eventos) > 0): ?>
        <div class="tabela-contentor">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Data e Hora</th>
                        <th>Localização</th>
                        <th>Convocatórias</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eventos as $evento): ?>
                        <tr>
                            <td>
                                <?php
                                $classe_badge = 'badge-info';
                                if ($evento['opcao'] === 'Jogo') $classe_badge = 'badge-sucesso';
                                elseif ($evento['opcao'] === 'Torneio') $classe_badge = 'badge-destaque';
                                ?>
                                <span class="badge <?php echo $classe_badge; ?>"><?php echo $evento['opcao']; ?></span>
                            </td>
                            <td><?php echo formatar_data_hora($evento['data']); ?></td>
                            <td><?php echo $evento['localizacao']; ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo $evento['total_convocatorias']; ?></span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="detalhes-evento.php?id=<?php echo $evento['id']; ?>" class="btn btn-primario" style="padding: 0.5rem 1rem; font-size: 0.875rem;">👁️ Ver</a>
                                    <a href="eventos.php?editar=<?php echo $evento['id']; ?>" class="btn btn-secundario" style="padding: 0.5rem 1rem; font-size: 0.875rem;">✏️ Editar</a>
                                    <a href="eventos.php?eliminar=<?php echo $evento['id']; ?>" 
                                       class="btn btn-perigo" 
                                       style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                                       onclick="return confirmarAcao('Tem a certeza que deseja eliminar este evento?');">🗑️ Eliminar</a>
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
            <span>Ainda não criou nenhum evento. Crie o seu primeiro evento acima.</span>
        </div>
    <?php endif; ?>
</div>

<?php include 'rodape.php'; ?>