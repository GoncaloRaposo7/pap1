<?php
// perfil.php - Perfil do utilizador
require_once 'configuracao.php';
requer_autenticacao();

$titulo_pagina = 'Meu Perfil';
$subtitulo_pagina = 'Gerir informações da conta';

$id_utilizador = $_SESSION['utilizador_id'];
$tipo_utilizador = $_SESSION['tipo_utilizador'];

// Atualizar perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar') {
    $nome = limpar_entrada($_POST['nome']);
    $email = limpar_entrada($_POST['email']);
    
    if (empty($nome) || empty($email)) {
        definir_mensagem('erro', 'Preencha todos os campos.');
    } else {
        try {
            $tabela = '';
            switch ($tipo_utilizador) {
                case 'direcao':
                    $tabela = 'direcao_clubes';
                    break;
                case 'treinador':
                    $tabela = 'treinadores';
                    break;
                case 'jogador':
                    $tabela = 'jogadores';
                    break;
            }
            
            $stmt = $ligacao_bd->prepare("UPDATE $tabela SET nome = ?, email = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $id_utilizador]);
            
            $_SESSION['utilizador_nome'] = $nome;
            $_SESSION['utilizador_email'] = $email;
            
            definir_mensagem('sucesso', 'Perfil atualizado com sucesso!');
            header('Location: perfil.php');
            exit();
        } catch (PDOException $e) {
            definir_mensagem('erro', 'Erro ao atualizar perfil: ' . $e->getMessage());
        }
    }
}

// Alterar palavra-passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'alterar_senha') {
    $senha_atual = $_POST['senha_atual'];
    $senha_nova = $_POST['senha_nova'];
    $senha_confirmar = $_POST['senha_confirmar'];
    
    if (empty($senha_atual) || empty($senha_nova) || empty($senha_confirmar)) {
        definir_mensagem('erro', 'Preencha todos os campos de palavra-passe.');
    } elseif ($senha_nova !== $senha_confirmar) {
        definir_mensagem('erro', 'As palavras-passe novas não coincidem.');
    } elseif (strlen($senha_nova) < 6) {
        definir_mensagem('erro', 'A palavra-passe deve ter pelo menos 6 caracteres.');
    } else {
        try {
            $tabela = '';
            switch ($tipo_utilizador) {
                case 'direcao':
                    $tabela = 'direcao_clubes';
                    break;
                case 'treinador':
                    $tabela = 'treinadores';
                    break;
                case 'jogador':
                    $tabela = 'jogadores';
                    break;
            }
            
            $stmt = $ligacao_bd->prepare("SELECT pass FROM $tabela WHERE id = ?");
            $stmt->execute([$id_utilizador]);
            $utilizador = $stmt->fetch();
            
            if (!password_verify($senha_atual, $utilizador['pass'])) {
                definir_mensagem('erro', 'Palavra-passe atual incorreta.');
            } else {
                $pass_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
                $stmt = $ligacao_bd->prepare("UPDATE $tabela SET pass = ? WHERE id = ?");
                $stmt->execute([$pass_hash, $id_utilizador]);
                
                definir_mensagem('sucesso', 'Palavra-passe alterada com sucesso!');
                header('Location: perfil.php');
                exit();
            }
        } catch (PDOException $e) {
            definir_mensagem('erro', 'Erro ao alterar palavra-passe: ' . $e->getMessage());
        }
    }
}

// Obter dados do utilizador
try {
    $tabela = '';
    switch ($tipo_utilizador) {
        case 'direcao':
            $tabela = 'direcao_clubes';
            break;
        case 'treinador':
            $tabela = 'treinadores';
            break;
        case 'jogador':
            $tabela = 'jogadores';
            break;
    }
    
    $stmt = $ligacao_bd->prepare("SELECT * FROM $tabela WHERE id = ?");
    $stmt->execute([$id_utilizador]);
    $utilizador = $stmt->fetch();
    
    // Se for jogador, obter também o escalão
    if ($tipo_utilizador === 'jogador') {
        $stmt = $ligacao_bd->prepare("
            SELECT e.nome as escalao_nome, c.nome as clube_nome
            FROM escaloes e
            LEFT JOIN clubes c ON e.id_clube = c.id
            WHERE e.id = ?
        ");
        $stmt->execute([$utilizador['id_escalao']]);
        $info_escalao = $stmt->fetch();
    }
} catch (PDOException $e) {
    definir_mensagem('erro', 'Erro ao carregar perfil: ' . $e->getMessage());
    $utilizador = null;
}

include 'cabecalho.php';
?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Informações do Perfil -->
    <div class="card">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">Informações do Perfil</h2>
        
        <form method="POST" class="formulario" style="padding: 0;">
            <input type="hidden" name="acao" value="atualizar">
            
            <div class="grupo-formulario">
                <label>Nome Completo</label>
                <input type="text" name="nome" value="<?php echo $utilizador['nome']; ?>" required>
            </div>
            
            <div class="grupo-formulario">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo $utilizador['email']; ?>" required>
            </div>
            
            <div class="grupo-formulario">
                <label>Tipo de Utilizador</label>
                <input type="text" value="<?php 
                    switch($tipo_utilizador) {
                        case 'direcao': echo 'Direção do Clube'; break;
                        case 'treinador': echo 'Treinador'; break;
                        case 'jogador': echo 'Jogador'; break;
                    }
                ?>" disabled style="background: var(--cor-fundo); cursor: not-allowed;">
            </div>
            
            <?php if ($tipo_utilizador === 'jogador' && isset($info_escalao)): ?>
                <div class="grupo-formulario">
                    <label>Escalão</label>
                    <input type="text" value="<?php echo $info_escalao['escalao_nome'] . ' - ' . $info_escalao['clube_nome']; ?>" 
                           disabled style="background: var(--cor-fundo); cursor: not-allowed;">
                </div>
            <?php endif; ?>
            
            <button type="submit" class="btn btn-primario">💾 Guardar Alterações</button>
        </form>
    </div>
    
    <!-- Alterar Palavra-passe -->
    <div class="card">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">Alterar Palavra-passe</h2>
        
        <form method="POST" class="formulario" style="padding: 0;">
            <input type="hidden" name="acao" value="alterar_senha">
            
            <div class="grupo-formulario">
                <label>Palavra-passe Atual</label>
                <input type="password" name="senha_atual" required>
            </div>
            
            <div class="grupo-formulario">
                <label>Nova Palavra-passe</label>
                <input type="password" name="senha_nova" minlength="6" required>
            </div>
            
            <div class="grupo-formulario">
                <label>Confirmar Nova Palavra-passe</label>
                <input type="password" name="senha_confirmar" minlength="6" required>
            </div>
            
            <button type="submit" class="btn btn-secundario">🔒 Alterar Palavra-passe</button>
        </form>
    </div>
</div>

<!-- Informações Adicionais -->
<div class="card" style="margin-top: 2rem;">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">Estatísticas da Conta</h2>
    
    <div class="grelha-cards">
        <?php
        try {
            if ($tipo_utilizador === 'treinador') {
                // Estatísticas do treinador
                $stmt = $ligacao_bd->prepare("SELECT COUNT(*) as total FROM eventos WHERE id_treinador = ?");
                $stmt->execute([$id_utilizador]);
                $total_eventos = $stmt->fetch()['total'];
                
                $stmt = $ligacao_bd->prepare("SELECT COUNT(*) as total FROM convites WHERE id_treinador = ?");
                $stmt->execute([$id_utilizador]);
                $total_convites = $stmt->fetch()['total'];
                
                echo '<div class="card card-estatistica">
                        <div class="card-titulo">Eventos Criados</div>
                        <div class="card-valor">' . $total_eventos . '</div>
                      </div>';
                      
                echo '<div class="card card-estatistica secundario">
                        <div class="card-titulo">Convites Enviados</div>
                        <div class="card-valor">' . $total_convites . '</div>
                      </div>';
                      
            } elseif ($tipo_utilizador === 'jogador') {
                // Estatísticas do jogador
                $stmt = $ligacao_bd->prepare("SELECT COUNT(*) as total FROM convites WHERE id_jogador = ? AND estado = 'pendente'");
                $stmt->execute([$id_utilizador]);
                $convites_pendentes = $stmt->fetch()['total'];
                
                $stmt = $ligacao_bd->prepare("SELECT COUNT(*) as total FROM convites WHERE id_jogador = ? AND estado = 'aceite'");
                $stmt->execute([$id_utilizador]);
                $convites_aceites = $stmt->fetch()['total'];
                
                echo '<div class="card card-estatistica destaque">
                        <div class="card-titulo">Convites Pendentes</div>
                        <div class="card-valor">' . $convites_pendentes . '</div>
                      </div>';
                      
                echo '<div class="card card-estatistica secundario">
                        <div class="card-titulo">Convites Aceites</div>
                        <div class="card-valor">' . $convites_aceites . '</div>
                      </div>';
            }
        } catch (PDOException $e) {
            // Silenciar erros
        }
        ?>
    </div>
</div>

<?php include 'rodape.php'; ?>