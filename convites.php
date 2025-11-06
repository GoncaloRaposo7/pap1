<?php
// convites.php - Sistema de convites (para treinadores)
require_once 'configuracao.php';
requer_autenticacao();

if (!e_treinador()) {
    header('Location: painel.php');
    exit();
}

$titulo_pagina = 'Convites';
$subtitulo_pagina = 'Enviar convites para jogadores';

$id_treinador = $_SESSION['utilizador_id'];

// Enviar convite
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'enviar') {
    $id_jogador = intval($_POST['id_jogador']);
    
    if ($id_jogador <= 0) {
        definir_mensagem('erro', 'Selecione um jogador.');
    } else {
        try {
            // Verificar se já existe convite pendente
            $stmt = $ligacao_bd->prepare("
                SELECT id FROM convites 
                WHERE id_treinador = ? AND id_jogador = ? AND estado = 'pendente'
            ");
            $stmt->execute([$id_treinador, $id_jogador]);
            
            if ($stmt->fetch()) {
                definir_mensagem('erro', 'Já existe um convite pendente para este jogador.');
            } else {
                $data_envio = date('Y-m-d H:i:s');
                $stmt = $ligacao_bd->prepare("
                    INSERT INTO convites (id_treinador, id_jogador, data_envio, estado) 
                    VALUES (?, ?, ?, 'pendente')
                ");
                $stmt->execute([$id_treinador, $id_jogador, $data_envio]);
                definir_mensagem('sucesso', 'Convite enviado com sucesso!');
                header('Location: convites.php');
                exit();
            }
        } catch (PDOException $e) {
            definir_mensagem('erro', 'Erro ao enviar convite: ' . $e->getMessage());
        }
    }
}

// Cancelar convite
if (isset($_GET['cancelar'])) {
    $id = intval($_GET['cancelar']);
    try {
        $stmt = $ligacao_bd->prepare("DELETE FROM convites WHERE id = ? AND id_treinador = ?");
        $stmt->execute([$id, $id_treinador]);
        definir_mensagem('sucesso', 'Convite cancelado!');
        header('Location: convites.php');
        exit();
    } catch (PDOException $e) {
        definir_mensagem('erro', 'Erro ao cancelar convite.');
    }
}

// Obter convites enviados
try {
    $stmt = $ligacao_bd->prepare("
        SELECT c.*, j.nome as jogador_nome, j.email as jogador_email, 
        e.nome as escalao_nome
        FROM convites c
        INNER JOIN jogadores j ON c.id_jogador = j.id
        LEFT JOIN escaloes e ON j.id_escalao = e.id
        WHERE c.id_treinador = ?
        ORDER BY c.data_envio DESC
    ");
    $stmt->execute([$id_treinador]);
    $convites = $stmt->fetchAll();
    
    // Obter jogadores dos escalões do treinador para o select
    $stmt = $ligacao_bd->prepare("
        SELECT j.*, e.nome as escalao_nome
        FROM jogadores j
        INNER JOIN escaloes e ON j.id_escalao = e.id
        INNER JOIN treinadores_escaloes te ON e.id = te.id_escalao
        WHERE te.id_treinador = ?
        ORDER BY e.nome, j.nome
    ");
    $stmt->execute([$id_treinador]);
    $jogadores = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $convites = [];
    $jogadores = [];
    definir_mensagem('erro', 'Erro ao carregar dados: ' . $e->getMessage());
}

include 'cabecalho.php';
?>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1rem; font-size: 1.25rem; font-weight: 700;">Enviar Novo Convite</h2>
    
    <form method="POST" class="formulario" style="padding: 0;">
        <input type="hidden" name="acao" value="enviar">
        
        <div class="grupo-formulario">
            <label>Selecionar Jogador</label>
            <select name="id_jogador" required style="width: 100%;">
                <option value="">Escolha um jogador...</option>
                <?php 
                $escalao_anterior = '';
                foreach ($jogadores as $jogador): 
                    if ($escalao_anterior !== $jogador['escalao_nome']) {
                        if ($escalao_anterior !== '') echo '</optgroup>';
                        echo '<optgroup label="' . $jogador['escalao_nome'] . '">';
                        $escalao_anterior = $jogador['escalao_nome'];
                    }
                ?>
                    <option value="<?php echo $jogador['id']; ?>">
                        <?php echo $jogador['nome'] . ' (' . $jogador['email'] . ')'; ?>
                    </option>
                <?php 
                endforeach; 
                if ($escalao_anterior !== '') echo '</optgroup>';
                ?>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primario">✉️ Enviar Convite</button>
    </form>
</div>

<div class="card">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">Convites Enviados</h2>
    
    <?php if (count($convites) > 0): ?>
        <div class="tabela-contentor">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Jogador</th>
                        <th>Escalão</th>
                        <th>Data de Envio</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($convites as $convite): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?php echo $convite['jogador_nome']; ?></strong>
                                    <div style="font-size: 0.875rem; color: var(--cor-texto-claro);">
                                        <?php echo $convite['jogador_email']; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info"><?php echo $convite['escalao_nome']; ?></span>
                            </td>
                            <td><?php echo formatar_data_hora($convite['data_envio']); ?></td>
                            <td>
                                <?php
                                $classe_badge = 'badge-pendente';
                                $texto_estado = 'Pendente';
                                
                                if ($convite['estado'] === 'aceite') {
                                    $classe_badge = 'badge-sucesso';
                                    $texto_estado = 'Aceite';
                                } elseif ($convite['estado'] === 'recusado') {
                                    $classe_badge = 'badge-recusado';
                                    $texto_estado = 'Recusado';
                                }
                                ?>
                                <span class="badge <?php echo $classe_badge; ?>"><?php echo $texto_estado; ?></span>
                            </td>
                            <td>
                                <?php if ($convite['estado'] === 'pendente'): ?>
                                    <a href="convites.php?cancelar=<?php echo $convite['id']; ?>" 
                                       class="btn btn-perigo" 
                                       style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                                       onclick="return confirmarAcao('Cancelar este convite?');">❌ Cancelar</a>
                                <?php else: ?>
                                    <span style="color: var(--cor-texto-claro);">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alerta alerta-info">
            <span>ℹ️</span>
            <span>Ainda não enviou convites. Envie o primeiro convite acima.</span>
        </div>
    <?php endif; ?>
</div>

<?php include 'rodape.php'; ?>