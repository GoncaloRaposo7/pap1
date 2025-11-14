<?php
// direcao.php - Gestão de contas de direção (apenas para direção)
require_once 'configuracao.php';
requer_autenticacao();
requer_direcao();

$titulo_pagina = 'Gestão de Direção';
$subtitulo_pagina = 'Gerir membros da direção do clube';

// Adicionar membro da direção
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'adicionar') {
    $nome = limpar_entrada($_POST['nome']);
    $email = limpar_entrada($_POST['email']);
    $palavra_passe = $_POST['palavra_passe'];
    $confirmar_senha = $_POST['confirmar_senha'];
    
    if (empty($nome) || empty($email) || empty($palavra_passe)) {
        definir_mensagem('erro', 'Preencha todos os campos obrigatórios.');
    } elseif ($palavra_passe !== $confirmar_senha) {
        definir_mensagem('erro', 'As palavras-passe não coincidem.');
    } elseif (strlen($palavra_passe) < 6) {
        definir_mensagem('erro', 'A palavra-passe deve ter pelo menos 6 caracteres.');
    } else {
        try {
            // Verificar se o email já existe
            $stmt = $ligacao_bd->prepare("SELECT id FROM direcao_clubes WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                definir_mensagem('erro', 'Este email já está registado.');
            } else {
                $pass_hash = password_hash($palavra_passe, PASSWORD_DEFAULT);
                $stmt = $ligacao_bd->prepare("INSERT INTO direcao_clubes (nome, email, pass) VALUES (?, ?, ?)");
                $stmt->execute([$nome, $email, $pass_hash]);
                definir_mensagem('sucesso', 'Membro da direção adicionado com sucesso!');
                header('Location: direcao.php');
                exit();
            }
        } catch (PDOException $e) {
            definir_mensagem('erro', 'Erro ao adicionar membro: ' . $e->getMessage());
        }
    }
}

// Editar membro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar') {
    $id = intval($_POST['id']);
    $nome = limpar_entrada($_POST['nome']);
    $email = limpar_entrada($_POST['email']);
    
    if (empty($nome) || empty($email)) {
        definir_mensagem('erro', 'Preencha todos os campos obrigatórios.');
    } else {
        try {
            $stmt = $ligacao_bd->prepare("UPDATE direcao_clubes SET nome = ?, email = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $id]);
            definir_mensagem('sucesso', 'Dados atualizados com sucesso!');
            header('Location: direcao.php');
            exit();
        } catch (PDOException $e) {
            definir_mensagem('erro', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }
}

// Alterar password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'alterar_senha') {
    $id = intval($_POST['id']);
    $senha_nova = $_POST['senha_nova'];
    $senha_confirmar = $_POST['senha_confirmar'];
    
    if (empty($senha_nova) || empty($senha_confirmar)) {
        definir_mensagem('erro', 'Preencha todos os campos de palavra-passe.');
    } elseif ($senha_nova !== $senha_confirmar) {
        definir_mensagem('erro', 'As palavras-passe não coincidem.');
    } elseif (strlen($senha_nova) < 6) {
        definir_mensagem('erro', 'A palavra-passe deve ter pelo menos 6 caracteres.');
    } else {
        try {
            $pass_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
            $stmt = $ligacao_bd->prepare("UPDATE direcao_clubes SET pass = ? WHERE id = ?");
            $stmt->execute([$pass_hash, $id]);
            definir_mensagem('sucesso', 'Palavra-passe alterada com sucesso!');
            header('Location: direcao.php');
            exit();
        } catch (PDOException $e) {
            definir_mensagem('erro', 'Erro ao alterar palavra-passe.');
        }
    }
}

// Eliminar membro
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    
    // Não permitir que o utilizador atual se elimine
    if ($id === $_SESSION['utilizador_id']) {
        definir_mensagem('erro', 'Não pode eliminar a sua própria conta.');
        header('Location: direcao.php');
        exit();
    }
    
    try {
        $stmt = $ligacao_bd->prepare("DELETE FROM direcao_clubes WHERE id = ?");
        $stmt->execute([$id]);
        definir_mensagem('sucesso', 'Membro eliminado com sucesso!');
        header('Location: direcao.php');
        exit();
    } catch (PDOException $e) {
        definir_mensagem('erro', 'Erro ao eliminar membro.');
    }
}

// Obter membros da direção
try {
    $stmt = $ligacao_bd->query("SELECT * FROM direcao_clubes ORDER BY nome");
    $membros = $stmt->fetchAll();
} catch (PDOException $e) {
    $membros = [];
    definir_mensagem('erro', 'Erro ao carregar membros: ' . $e->getMessage());
}

include 'cabecalho.php';
?>

<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1rem; font-size: 1.25rem; font-weight: 700;">
        <?php echo isset($_GET['editar']) ? 'Editar Membro' : 'Adicionar Novo Membro da Direção'; ?>
    </h2>
    
    <?php
    $membro_editar = null;
    if (isset($_GET['editar'])) {
        $id_editar = intval($_GET['editar']);
        $stmt = $ligacao_bd->prepare("SELECT * FROM direcao_clubes WHERE id = ?");
        $stmt->execute([$id_editar]);
        $membro_editar = $stmt->fetch();
    }
    ?>
    
    <?php if (!isset($_GET['editar'])): ?>
        <!-- Formulário de Adicionar -->
        <form method="POST" class="formulario" style="padding: 0;">
            <input type="hidden" name="acao" value="adicionar">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="grupo-formulario">
                    <label> Nome Completo</label>
                    <input type="text" name="nome" required placeholder="Ex: João Silva">
                </div>
                
                <div class="grupo-formulario">
                    <label> Email</label>
                    <input type="email" name="email" required placeholder="joao@clube.pt">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="grupo-formulario">
                    <label> Palavra-passe</label>
                    <input type="password" name="palavra_passe" minlength="6" required placeholder="Mínimo 6 caracteres">
                </div>
                
                <div class="grupo-formulario">
                    <label> Confirmar Palavra-passe</label>
                    <input type="password" name="confirmar_senha" minlength="6" required placeholder="Digite novamente">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primario" style="margin-top: 1rem;">
                ➕ Adicionar Membro
            </button>
        </form>
    <?php else: ?>
        <!-- Formulário de Editar -->
        <form method="POST" class="formulario" style="padding: 0;">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" value="<?php echo $membro_editar['id']; ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="grupo-formulario">
                    <label> Nome Completo</label>
                    <input type="text" name="nome" value="<?php echo $membro_editar['nome']; ?>" required>
                </div>
                
                <div class="grupo-formulario">
                    <label> Email</label>
                    <input type="email" name="email" value="<?php echo $membro_editar['email']; ?>" required>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primario"> Guardar Alterações</button>
                <a href="direcao.php" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
        
        <!-- Formulário de Alterar Senha -->
        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--cor-borda);">
            <h3 style="margin-bottom: 1rem; font-size: 1.125rem; font-weight: 700;"> Alterar Palavra-passe</h3>
            <form method="POST" class="formulario" style="padding: 0;">
                <input type="hidden" name="acao" value="alterar_senha">
                <input type="hidden" name="id" value="<?php echo $membro_editar['id']; ?>">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="grupo-formulario">
                        <label> Nova Palavra-passe</label>
                        <input type="password" name="senha_nova" minlength="6" required>
                    </div>
                    
                    <div class="grupo-formulario">
                        <label> Confirmar Nova Palavra-passe</label>
                        <input type="password" name="senha_confirmar" minlength="6" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-secundario" style="margin-top: 1rem;">
                     Alterar Palavra-passe
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.5rem; font-weight: 700;">Lista de Membros da Direção</h2>
        <span class="badge badge-info" style="font-size: 1rem; padding: 0.5rem 1rem;">
            Total: <?php echo count($membros); ?>
        </span>
    </div>
    
    <?php if (count($membros) > 0): ?>
        <div class="tabela-contentor">
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($membros as $membro): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div class="avatar" style="width: 40px; height: 40px; font-size: 1rem;">
                                        <?php echo strtoupper(substr($membro['nome'], 0, 1)); ?>
                                    </div>
                                    <strong><?php echo $membro['nome']; ?></strong>
                                </div>
                            </td>
                            <td><?php echo $membro['email']; ?></td>
                            <td>
                                <?php if ($membro['id'] === $_SESSION['utilizador_id']): ?>
                                    <span class="badge badge-sucesso">Você</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Membro</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="direcao.php?editar=<?php echo $membro['id']; ?>" 
                                       class="btn btn-secundario" 
                                       style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                         Editar
                                    </a>
                                    
                                    <?php if ($membro['id'] !== $_SESSION['utilizador_id']): ?>
                                        <a href="direcao.php?eliminar=<?php echo $membro['id']; ?>" 
                                           class="btn btn-perigo" 
                                           style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                                           onclick="return confirmarAcao('Tem a certeza que deseja eliminar este membro?');">
                                             Eliminar
                                        </a>
                                    <?php else: ?>
                                        <button class="btn" 
                                                style="padding: 0.5rem 1rem; font-size: 0.875rem; background: var(--cor-borda); color: var(--cor-texto-claro); cursor: not-allowed;" 
                                                disabled
                                                title="Não pode eliminar a sua própria conta">
                                             Eliminar
                                        </button>
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
            <span>Não existem membros da direção registados.</span>
        </div>
    <?php endif; ?>
</div>

<!-- Informações Importantes -->
<div class="card" style="margin-top: 2rem; border-left: 4px solid var(--cor-destaque);">
    <h3 style="margin-bottom: 1rem; font-size: 1.25rem; font-weight: 700;"> Informações Importantes</h3>
    <ul style="list-style: none; padding: 0; margin: 0;">
        <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--cor-borda);">
            <strong> Segurança:</strong> Todas as passwords são encriptadas automaticamente.
        </li>
        <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--cor-borda);">
            <strong> Acesso:</strong> Membros da direção têm acesso total ao sistema.
        </li>
        <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--cor-borda);">
            <strong> Autoexclusão:</strong> Não pode eliminar a sua própria conta.
        </li>
        <li style="padding: 0.75rem 0;">
            <strong> Email Único:</strong> Cada membro deve ter um email diferente.
        </li>
    </ul>
</div>

<?php include 'rodape.php'; ?>