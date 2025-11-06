<?php
// jogadores.php - Lista de jogadores
require_once 'configuracao.php';
requer_autenticacao();

$titulo_pagina = 'Jogadores';
$subtitulo_pagina = 'Lista de jogadores registados';

// Obter jogadores baseado no tipo de utilizador
try {
    if (e_direcao()) {
        // Direção vê todos os jogadores
        $stmt = $ligacao_bd->query("
            SELECT j.*, e.nome as escalao_nome, c.nome as clube_nome
            FROM jogadores j
            LEFT JOIN escaloes e ON j.id_escalao = e.id
            LEFT JOIN clubes c ON e.id_clube = c.id
            ORDER BY c.nome, e.nome, j.nome
        ");
    } elseif (e_treinador()) {
        // Treinador vê jogadores dos seus escalões
        $id_treinador = $_SESSION['utilizador_id'];
        $stmt = $ligacao_bd->prepare("
            SELECT j.*, e.nome as escalao_nome, c.nome as clube_nome
            FROM jogadores j
            INNER JOIN escaloes e ON j.id_escalao = e.id
            INNER JOIN treinadores_escaloes te ON e.id = te.id_escalao
            LEFT JOIN clubes c ON e.id_clube = c.id
            WHERE te.id_treinador = ?
            ORDER BY e.nome, j.nome
        ");
        $stmt->execute([$id_treinador]);
    } elseif (e_jogador()) {
        // Jogador vê apenas jogadores do seu escalão
        $id_jogador = $_SESSION['utilizador_id'];
        $stmt = $ligacao_bd->prepare("
            SELECT j.*, e.nome as escalao_nome, c.nome as clube_nome
            FROM jogadores j
            INNER JOIN escaloes e ON j.id_escalao = e.id
            LEFT JOIN clubes c ON e.id_clube = c.id
            WHERE j.id_escalao = (SELECT id_escalao FROM jogadores WHERE id = ?)
            ORDER BY j.nome
        ");
        $stmt->execute([$id_jogador]);
    }
    
    $jogadores = $stmt->fetchAll();
} catch (PDOException $e) {
    $jogadores = [];
    definir_mensagem('erro', 'Erro ao carregar jogadores: ' . $e->getMessage());
}

include 'cabecalho.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.5rem; font-weight: 700;">Lista de Jogadores</h2>
        <div style="display: flex; gap: 1rem;">
            <input type="text" id="pesquisa" placeholder="🔍 Pesquisar jogador..." style="padding: 0.75rem 1rem; border: 2px solid var(--cor-borda); border-radius: 8px; width: 300px;">
        </div>
    </div>
    
    <?php if (count($jogadores) > 0): ?>
        <div class="tabela-contentor">
            <table class="tabela" id="tabela-jogadores">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Escalão</th>
                        <?php if (e_direcao()): ?>
                            <th>Clube</th>
                        <?php endif; ?>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jogadores as $jogador): ?>
                        <tr>
                            <td><strong><?php echo $jogador['nome']; ?></strong></td>
                            <td><?php echo $jogador['email']; ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo $jogador['escalao_nome']; ?></span>
                            </td>
                            <?php if (e_direcao()): ?>
                                <td><?php echo $jogador['clube_nome']; ?></td>
                            <?php endif; ?>
                            <td>
                                <a href="perfil-jogador.php?id=<?php echo $jogador['id']; ?>" class="btn btn-primario" style="padding: 0.5rem 1rem; font-size: 0.875rem;">👁️ Ver Perfil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alerta alerta-info">
            <span>ℹ️</span>
            <span>Não existem jogadores registados.</span>
        </div>
    <?php endif; ?>
</div>

<script>
    // Pesquisa em tempo real
    document.getElementById('pesquisa').addEventListener('keyup', function() {
        const pesquisa = this.value.toLowerCase();
        const linhas = document.querySelectorAll('#tabela-jogadores tbody tr');
        
        linhas.forEach(function(linha) {
            const texto = linha.textContent.toLowerCase();
            if (texto.indexOf(pesquisa) > -1) {
                linha.style.display = '';
            } else {
                linha.style.display = 'none';
            }
        });
    });
</script>

<?php include 'rodape.php'; ?>