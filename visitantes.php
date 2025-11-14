<?php
// visitantes.php - Gestão de visitantes (apenas para direção)
require_once 'configuracao.php';
requer_autenticacao();
requer_direcao();

$titulo_pagina = 'Gestão de Visitantes';
$subtitulo_pagina = 'Registar e gerir visitantes do clube';

// Registar novo visitante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'registar') {
    $nome = limpar_entrada($_POST['nome']);
    $email = limpar_entrada($_POST['email']);
    $telefone = limpar_entrada($_POST['telefone']);
    $motivo_visita = limpar_entrada($_POST['motivo_visita']);
    $data_entrada = date('Y-m-d H:i:s');
    
    if (empty($nome) || empty($motivo_visita)) {
        definir_mensagem('erro', 'Preencha os campos obrigatórios (Nome e Motivo).');
    } else {
        try {
            $stmt = $ligacao_bd->prepare("
                INSERT INTO visitantes (nome, email, telefone, motivo_visita, data_entrada, estado) 
                VALUES (?, ?, ?, ?, ?, 'dentro')
            ");
            $stmt->execute([$nome, $email, $telefone, $motivo_visita, $data_entrada]);
            definir_mensagem('sucesso', 'Visitante registado com sucesso!');
            header('Location: visitantes.php');
            exit();
        } catch (PDOException $e) {
            definir_mensagem('erro', 'Erro ao registar visitante: ' . $e->getMessage());
        }
    }
}

// Registar saída
if (isset($_GET['saida'])) {
    $id = intval($_GET['saida']);
    $data_saida = date('Y-m-d H:i:s');
    
    try {
        $stmt = $ligacao_bd->prepare("UPDATE visitantes SET data_saida = ?, estado = 'saiu' WHERE id = ?");
        $stmt->execute([$data_saida, $id]);
        definir_mensagem('sucesso', 'Saída registada!');
        header('Location: visitantes.php');
        exit();
    } catch (PDOException $e) {
        definir_mensagem('erro', 'Erro ao registar saída.');
    }
}

// Eliminar visitante
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    try {
        $stmt = $ligacao_bd->prepare("DELETE FROM visitantes WHERE id = ?");
        $stmt->execute([$id]);
        definir_mensagem('sucesso', 'Registo eliminado!');
        header('Location: visitantes.php');
        exit();
    } catch (PDOException $e) {
        definir_mensagem('erro', 'Erro ao eliminar registo.');
    }
}

// Obter visitantes
try {
    // Visitantes atualmente no clube
    $stmt = $ligacao_bd->query("
        SELECT * FROM visitantes 
        WHERE estado = 'dentro' 
        ORDER BY data_entrada DESC
    ");
    $visitantes_dentro = $stmt->fetchAll();
    
    // Histórico (últimos 30 dias)
    $stmt = $ligacao_bd->query("
        SELECT * FROM visitantes 
        WHERE estado = 'saiu' 
        AND data_entrada >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY data_saida DESC 
        LIMIT 50
    ");
    $historico = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $visitantes_dentro = [];
    $historico = [];
    definir_mensagem('erro', 'Erro ao carregar visitantes: ' . $e->getMessage());
}

include 'cabecalho.php';
?>

<div class="grelha-cards" style="margin-bottom: 2rem;">
    <div class="card card-estatistica">
        <div class="card-titulo">Visitantes no Clube</div>
        <div class="card-valor"><?php echo count($visitantes_dentro); ?></div>
    </div>
    
    <div class="card card-estatistica secundario">
        <div class="card-titulo">Visitas Hoje</div>
        <div class="card-valor">
            <?php 
            $stmt = $ligacao_bd->query("
                SELECT COUNT(*) as total FROM visitantes 
                WHERE DATE(data_entrada) = CURDATE()
            ");
            echo $stmt->fetch()['total'];
            ?>
        </div>
    </div>
    
    <div class="card card-estatistica destaque">
        <div class="card-titulo">Visitas Este Mês</div>
        <div class="card-valor">
            <?php 
            $stmt = $ligacao_bd->query("
                SELECT COUNT(*) as total FROM visitantes 
                WHERE MONTH(data_entrada) = MONTH(CURDATE())
            ");
            echo $stmt->fetch()['total'];
            ?>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1rem; font-size: 1.25rem; font-weight: 700;">Registar Novo Visitante</h2>
    
    <form method="POST" class="formulario" style="padding: 0;">
        <input type="hidden" name="acao" value="registar">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1rem;">
            <div class="grupo-formulario">
                <label>Nome Completo *</label>
                <input type="text" name="nome" required placeholder="Nome do visitante">
            </div>
            
            <div class="grupo-formulario">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@exemplo.com">
            </div>
            
            <div class="grupo-formulario">
                <label>Telefone</label>
                <input type="tel" name="telefone" placeholder="+351 xxx xxx xxx">
            </div>
            
            <div class="grupo-formulario">
                <label>Motivo da Visita *</label>
                <select name="motivo_visita" required>
                    <option value="">Selecione...</option>
                    <option value="Reunião">Reunião</option>
                    <option value="Treino">Assistir Treino</option>
                    <option value="Jogo">Assistir Jogo</option>
                    <option value="Inscrição">Inscrição</option>
                    <option value="Fornecedor">Fornecedor</option>
                    <option value="Manutenção">Manutenção</option>
                    <option value="Outro">Outro</option>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primario" style="margin-top: 1rem;">✅ Registar Entrada</button>
    </form>
</div>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">
        🟢 Visitantes Atualmente no Clube (<?php echo count($visitantes_dentro); ?>)
    </h2>
    
    <?php if (count($visitantes_dentro) > 0): ?>
        <div class="tabela-contentor">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email / Telefone</th>
                        <th>Motivo</th>
                        <th>Hora de Entrada</th>
                        <th>Tempo no Clube</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($visitantes_dentro as $visitante): ?>
                        <?php
                        $entrada = strtotime($visitante['data_entrada']);
                        $tempo_decorrido = time() - $entrada;
                        $horas = floor($tempo_decorrido / 3600);
                        $minutos = floor(($tempo_decorrido % 3600) / 60);
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($visitante['nome']); ?></strong></td>
                            <td>
                                <?php if ($visitante['email']): ?>
                                    <div style="font-size: 0.875rem;"><?php echo htmlspecialchars($visitante['email']); ?></div>
                                <?php endif; ?>
                                <?php if ($visitante['telefone']): ?>
                                    <div style="font-size: 0.875rem; color: var(--cor-texto-claro);">
                                        📱 <?php echo htmlspecialchars($visitante['telefone']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-info"><?php echo htmlspecialchars($visitante['motivo_visita']); ?></span>
                            </td>
                            <td><?php echo formatar_data_hora($visitante['data_entrada']); ?></td>
                            <td>
                                <strong style="color: var(--cor-primaria);">
                                    <?php echo $horas > 0 ? $horas . 'h ' : ''; echo $minutos; ?>min
                                </strong>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="visitantes.php?saida=<?php echo $visitante['id']; ?>" 
                                       class="btn btn-secundario" 
                                       style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                        🚪 Registar Saída
                                    </a>
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
            <span>Não há visitantes no clube neste momento.</span>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">
        📋 Histórico de Visitas (Últimos 30 dias)
    </h2>
    
    <?php if (count($historico) > 0): ?>
        <div class="tabela-contentor">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Motivo</th>
                        <th>Entrada</th>
                        <th>Saída</th>
                        <th>Duração</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historico as $visita): ?>
                        <?php
                        if ($visita['data_saida']) {
                            $entrada = strtotime($visita['data_entrada']);
                            $saida = strtotime($visita['data_saida']);
                            $duracao = $saida - $entrada;
                            $horas = floor($duracao / 3600);
                            $minutos = floor(($duracao % 3600) / 60);
                            $duracao_texto = ($horas > 0 ? $horas . 'h ' : '') . $minutos . 'min';
                        } else {
                            $duracao_texto = '-';
                        }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($visita['nome']); ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo htmlspecialchars($visita['motivo_visita']); ?></span>
                            </td>
                            <td style="font-size: 0.875rem;"><?php echo formatar_data_hora($visita['data_entrada']); ?></td>
                            <td style="font-size: 0.875rem;">
                                <?php echo $visita['data_saida'] ? formatar_data_hora($visita['data_saida']) : '-'; ?>
                            </td>
                            <td><?php echo $duracao_texto; ?></td>
                            <td>
                                <a href="visitantes.php?eliminar=<?php echo $visita['id']; ?>" 
                                   class="btn btn-perigo" 
                                   style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                                   onclick="return confirmarAcao('Eliminar este registo?');">
                                    🗑️
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alerta alerta-info">
            <span>ℹ️</span>
            <span>Sem histórico de visitas nos últimos 30 dias.</span>
        </div>
    <?php endif; ?>
</div>

<?php include 'rodape.php'; ?>