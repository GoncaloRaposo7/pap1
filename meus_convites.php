<?php
// meus-convites.php - Convites recebidos (para jogadores)
require_once 'configuracao.php';
requer_autenticacao();

if (!e_jogador()) {
    header('Location: inicio.php');
    exit();
}

$titulo_pagina = 'Meus Convites';
$subtitulo_pagina = 'Gerir convites recebidos';

$id_jogador = $_SESSION['utilizador_id'];

// Responder a convite
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && isset($_POST['resposta'])) {
    $id_convite = intval($_POST['id_convite']);
    $resposta = $_POST['resposta']; // 'aceite' ou 'recusado'
    
    if (in_array($resposta, ['aceite', 'recusado'])) {
        try {
            $stmt = $ligacao_bd->prepare("UPDATE convites SET estado = ? WHERE id = ? AND id_jogador = ?");
            $stmt->execute([$resposta, $id_convite, $id_jogador]);
            
            $mensagem = $resposta === 'aceite' ? 'Convite aceite!' : 'Convite recusado.';
            definir_mensagem('sucesso', $mensagem);
            header('Location: meus-convites.php');
            exit();
        } catch (PDOException $e) {
            definir_mensagem('erro', 'Erro ao responder ao convite.');
        }
    }
}

// Obter convites
try {
    $stmt = $ligacao_bd->prepare("
        SELECT c.*, t.nome as treinador_nome, t.email as treinador_email
        FROM convites c
        INNER JOIN treinadores t ON c.id_treinador = t.id
        WHERE c.id_jogador = ?
        ORDER BY 
            CASE c.estado 
                WHEN 'pendente' THEN 1 
                WHEN 'aceite' THEN 2 
                WHEN 'recusado' THEN 3 
            END,
            c.data_envio DESC
    ");
    $stmt->execute([$id_jogador]);
    $convites = $stmt->fetchAll();
} catch (PDOException $e) {
    $convites = [];
    definir_mensagem('erro', 'Erro ao carregar convites: ' . $e->getMessage());
}

include 'cabecalho.php';
?>

<div class="card">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">Convites Recebidos</h2>
    
    <?php if (count($convites) > 0): ?>
        <div style="display: grid; gap: 1.5rem;">
            <?php foreach ($convites as $convite): ?>
                <div class="card" style="border-left: 4px solid <?php 
                    echo $convite['estado'] === 'pendente' ? 'var(--cor-destaque)' : 
                        ($convite['estado'] === 'aceite' ? 'var(--cor-secundaria)' : 'var(--cor-perigo)'); 
                ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                <div class="avatar" style="width: 50px; height: 50px; font-size: 1.25rem;">
                                    <?php echo strtoupper(substr($convite['treinador_nome'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 0.25rem;">
                                        Convite de <?php echo $convite['treinador_nome']; ?>
                                    </h3>
                                    <p style="color: var(--cor-texto-claro); font-size: 0.875rem;">
                                        <?php echo $convite['treinador_email']; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem;">
                                <span style="font-size: 0.875rem; color: var(--cor-texto-claro);">
                                    📅 Enviado em <?php echo formatar_data_hora($convite['data_envio']); ?>
                                </span>
                                
                                <?php
                                $classe_badge = 'badge-pendente';
                                $texto_estado = 'Pendente';
                                $icone = '⏳';
                                
                                if ($convite['estado'] === 'aceite') {
                                    $classe_badge = 'badge-sucesso';
                                    $texto_estado = 'Aceite';
                                    $icone = '✅';
                                } elseif ($convite['estado'] === 'recusado') {
                                    $classe_badge = 'badge-recusado';
                                    $texto_estado = 'Recusado';
                                    $icone = '❌';
                                }
                                ?>
                                <span class="badge <?php echo $classe_badge; ?>">
                                    <?php echo $icone . ' ' . $texto_estado; ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if ($convite['estado'] === 'pendente'): ?>
                            <div style="display: flex; gap: 0.75rem;">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="responder">
                                    <input type="hidden" name="id_convite" value="<?php echo $convite['id']; ?>">
                                    <input type="hidden" name="resposta" value="aceite">
                                    <button type="submit" class="btn btn-secundario" style="padding: 0.75rem 1.25rem;">
                                        ✅ Aceitar
                                    </button>
                                </form>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="responder">
                                    <input type="hidden" name="id_convite" value="<?php echo $convite['id']; ?>">
                                    <input type="hidden" name="resposta" value="recusado">
                                    <button type="submit" class="btn btn-perigo" style="padding: 0.75rem 1.25rem;"
                                            onclick="return confirmarAcao('Tem a certeza que deseja recusar este convite?');">
                                        ❌ Recusar
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alerta alerta-info">
            <span>ℹ️</span>
            <span>Não tem convites no momento.</span>
        </div>
    <?php endif; ?>
</div>

<?php include 'rodape.php'; ?>