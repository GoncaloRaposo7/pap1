<?php
// meus-eventos.php - Eventos do jogador
require_once 'configuracao.php';
requer_autenticacao();

if (!e_jogador()) {
    header('Location: painel.php');
    exit();
}

$titulo_pagina = 'Meus Eventos';
$subtitulo_pagina = 'Convocatórias e próximos eventos';

$id_jogador = $_SESSION['utilizador_id'];

// Obter escalão do jogador
try {
    $stmt = $ligacao_bd->prepare("SELECT id_escalao FROM jogadores WHERE id = ?");
    $stmt->execute([$id_jogador]);
    $jogador = $stmt->fetch();
    
    $eventos = [];
    
    if ($jogador && $jogador['id_escalao']) {
        // Obter eventos/convocatórias do escalão
        $stmt = $ligacao_bd->prepare("
            SELECT e.*, c.estado, c.id as convocatoria_id,
            t.nome as treinador_nome
            FROM eventos e
            INNER JOIN convocatorias c ON e.id = c.id_evento
            INNER JOIN treinadores t ON e.id_treinador = t.id
            WHERE c.id_escalao = ?
            ORDER BY e.data DESC
        ");
        $stmt->execute([$jogador['id_escalao']]);
        $eventos = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $eventos = [];
    definir_mensagem('erro', 'Erro ao carregar eventos: ' . $e->getMessage());
}

include 'cabecalho.php';
?>

<div class="card">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">Convocatórias e Eventos</h2>
    
    <?php if (count($eventos) > 0): ?>
        <div class="tabela-contentor">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Data e Hora</th>
                        <th>Localização</th>
                        <th>Treinador</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eventos as $evento): ?>
                        <?php
                        $data_evento = strtotime($evento['data']);
                        $e_futuro = $data_evento >= time();
                        $classe_linha = $e_futuro ? '' : 'opacity: 0.6;';
                        ?>
                        <tr style="<?php echo $classe_linha; ?>">
                            <td>
                                <?php
                                $classe_badge = 'badge-info';
                                if ($evento['opcao'] === 'Jogo') $classe_badge = 'badge-sucesso';
                                elseif ($evento['opcao'] === 'Torneio') $classe_badge = 'badge-destaque';
                                ?>
                                <span class="badge <?php echo $classe_badge; ?>">
                                    <?php echo htmlspecialchars($evento['opcao']); ?>
                                </span>
                                <?php if ($e_futuro): ?>
                                    <span class="badge badge-info" style="margin-left: 0.5rem; font-size: 0.75rem;">Próximo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo formatar_data_hora($evento['data']); ?></strong>
                                    <?php if (!$e_futuro): ?>
                                        <div style="font-size: 0.75rem; color: var(--cor-texto-claro);">Passado</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($evento['localizacao']); ?></td>
                            <td><?php echo htmlspecialchars($evento['treinador_nome']); ?></td>
                            <td>
                                <?php
                                $classe_badge_estado = 'badge-pendente';
                                $texto_estado = 'Pendente';
                                
                                if ($evento['estado'] === 'confirmado') {
                                    $classe_badge_estado = 'badge-sucesso';
                                    $texto_estado = 'Confirmado';
                                } elseif ($evento['estado'] === 'cancelado') {
                                    $classe_badge_estado = 'badge-recusado';
                                    $texto_estado = 'Cancelado';
                                }
                                ?>
                                <span class="badge <?php echo $classe_badge_estado; ?>">
                                    <?php echo $texto_estado; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alerta alerta-info">
            <span>ℹ️</span>
            <span>Não existem eventos ou convocatórias no momento.</span>
        </div>
    <?php endif; ?>
</div>

<?php
// Separar eventos futuros
$eventos_futuros = array_filter($eventos, function($e) {
    return strtotime($e['data']) >= time();
});

if (count($eventos_futuros) > 0):
?>
<div class="card" style="margin-top: 2rem; border-left: 4px solid var(--cor-destaque);">
    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">
        ⚠️ Próximos Eventos - Prepare-se!
    </h3>
    <p style="color: var(--cor-texto-claro);">
        Tem <?php echo count($eventos_futuros); ?> evento(s) marcado(s). Não se esqueça de verificar o horário e local!
    </p>
</div>
<?php endif; ?>

<?php include 'rodape.php'; ?>