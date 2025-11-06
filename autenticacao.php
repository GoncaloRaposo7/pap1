<?php
// autenticacao.php - Página de login e registo
require_once 'configuracao.php';

// Se já estiver autenticado, redirecionar
if (esta_autenticado()) {
    header('Location: painel.php');
    exit();
}

$erro = '';
$sucesso = '';

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'login') {
    $email = limpar_entrada($_POST['email']);
    $palavra_passe = $_POST['palavra_passe'];
    $tipo = $_POST['tipo'];
    
    if (empty($email) || empty($palavra_passe) || empty($tipo)) {
        $erro = 'Preencha todos os campos.';
    } else {
        // Determinar tabela baseada no tipo
        $tabela = '';
        switch ($tipo) {
            case 'direcao':
                $tabela = 'direcao_clubes';
                break;
            case 'treinador':
                $tabela = 'treinadores';
                break;
            case 'jogador':
                $tabela = 'jogadores';
                break;
            default:
                $erro = 'Tipo de utilizador inválido.';
        }
        
        if (empty($erro)) {
            try {
                $stmt = $ligacao_bd->prepare("SELECT * FROM $tabela WHERE email = ?");
                $stmt->execute([$email]);
                $utilizador = $stmt->fetch();
                
                if ($utilizador && password_verify($palavra_passe, $utilizador['pass'])) {
                    $_SESSION['utilizador_id'] = $utilizador['id'];
                    $_SESSION['utilizador_nome'] = $utilizador['nome'];
                    $_SESSION['utilizador_email'] = $utilizador['email'];
                    $_SESSION['tipo_utilizador'] = $tipo;
                    
                    header('Location: painel.php');
                    exit();
                } else {
                    $erro = 'Email ou palavra-passe incorretos.';
                }
            } catch (PDOException $e) {
                $erro = 'Erro ao processar login: ' . $e->getMessage();
            }
        }
    }
}

// Processar registo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'registo') {
    $nome = limpar_entrada($_POST['nome']);
    $email = limpar_entrada($_POST['email']);
    $palavra_passe = $_POST['palavra_passe'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $tipo = $_POST['tipo'];
    
    if (empty($nome) || empty($email) || empty($palavra_passe) || empty($tipo)) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } elseif ($palavra_passe !== $confirmar_senha) {
        $erro = 'As palavras-passe não coincidem.';
    } elseif (strlen($palavra_passe) < 6) {
        $erro = 'A palavra-passe deve ter pelo menos 6 caracteres.';
    } else {
        // Determinar tabela
        $tabela = '';
        switch ($tipo) {
            case 'treinador':
                $tabela = 'treinadores';
                break;
            case 'jogador':
                $tabela = 'jogadores';
                break;
            default:
                $erro = 'Tipo de utilizador inválido para registo.';
        }
        
        if (empty($erro)) {
            try {
                // Verificar se email já existe
                $stmt = $ligacao_bd->prepare("SELECT id FROM $tabela WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    $erro = 'Este email já está registado.';
                } else {
                    $pass_hash = password_hash($palavra_passe, PASSWORD_DEFAULT);
                    
                    if ($tipo === 'jogador') {
                        // Para jogador, precisa de escalão
                        $id_escalao = isset($_POST['id_escalao']) ? intval($_POST['id_escalao']) : 0;
                        if ($id_escalao <= 0) {
                            $erro = 'Selecione um escalão.';
                        } else {
                            $stmt = $ligacao_bd->prepare("INSERT INTO jogadores (nome, email, pass, id_escalao) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$nome, $email, $pass_hash, $id_escalao]);
                            $sucesso = 'Registo efetuado com sucesso! Pode fazer login.';
                        }
                    } else {
                        $stmt = $ligacao_bd->prepare("INSERT INTO treinadores (nome, email, pass) VALUES (?, ?, ?)");
                        $stmt->execute([$nome, $email, $pass_hash]);
                        $sucesso = 'Registo efetuado com sucesso! Pode fazer login.';
                    }
                }
            } catch (PDOException $e) {
                $erro = 'Erro ao processar registo: ' . $e->getMessage();
            }
        }
    }
}

// Obter escalões para select
try {
    $stmt = $ligacao_bd->query("SELECT e.id, e.nome, c.nome as clube_nome FROM escaloes e INNER JOIN clubes c ON e.id_clube = c.id ORDER BY c.nome, e.nome");
    $escaloes = $stmt->fetchAll();
} catch (PDOException $e) {
    $escaloes = [];
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticação - GESTTEAM</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="contentor-autenticacao">
        <div class="card-autenticacao fade-in">
            <div class="logo-autenticacao">
                <h1>GESTTEAM</h1>
                <p style="color: #64748b; margin-top: 0.5rem;">Sistema de Gestão de Clubes Desportivos</p>
            </div>

            <?php if ($erro): ?>
                <div class="alerta alerta-erro">
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>

            <?php if ($sucesso): ?>
                <div class="alerta alerta-sucesso">
                    <?php echo $sucesso; ?>
                </div>
            <?php endif; ?>

            <div class="tabs-autenticacao">
                <button class="tab-btn ativo" onclick="mostrarTab('login')">Entrar</button>
                <button class="tab-btn" onclick="mostrarTab('registo')">Registar</button>
            </div>

            <!-- Formulário de Login -->
            <form method="POST" id="form-login" class="formulario" style="display: block; padding: 0;">
                <input type="hidden" name="acao" value="login">
                
                <div class="grupo-formulario">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="grupo-formulario">
                    <label>Palavra-passe</label>
                    <input type="password" name="palavra_passe" required>
                </div>

                <div class="grupo-formulario">
                    <label>Tipo de Utilizador</label>
                    <select name="tipo" required>
                        <option value="">Selecione...</option>
                        <option value="direcao">Direção do Clube</option>
                        <option value="treinador">Treinador</option>
                        <option value="jogador">Jogador</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primario" style="width: 100%;">Entrar</button>
            </form>

            <!-- Formulário de Registo -->
            <form method="POST" id="form-registo" class="formulario" style="display: none; padding: 0;">
                <input type="hidden" name="acao" value="registo">
                
                <div class="grupo-formulario">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" required>
                </div>

                <div class="grupo-formulario">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="grupo-formulario">
                    <label>Palavra-passe</label>
                    <input type="password" name="palavra_passe" minlength="6" required>
                </div>

                <div class="grupo-formulario">
                    <label>Confirmar Palavra-passe</label>
                    <input type="password" name="confirmar_senha" minlength="6" required>
                </div>

                <div class="grupo-formulario">
                    <label>Tipo de Utilizador</label>
                    <select name="tipo" id="tipo-registo" onchange="mostrarCamposAdicionais()" required>
                        <option value="">Selecione...</option>
                        <option value="treinador">Treinador</option>
                        <option value="jogador">Jogador</option>
                    </select>
                </div>

                <div class="grupo-formulario" id="campo-escalao" style="display: none;">
                    <label>Escalão</label>
                    <select name="id_escalao">
                        <option value="">Selecione...</option>
                        <?php foreach ($escaloes as $escalao): ?>
                            <option value="<?php echo $escalao['id']; ?>">
                                <?php echo $escalao['clube_nome'] . ' - ' . $escalao['nome']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primario" style="width: 100%;">Registar</button>
            </form>
        </div>
    </div>

    <script>
        function mostrarTab(tab) {
            const loginForm = document.getElementById('form-login');
            const registoForm = document.getElementById('form-registo');
            const tabs = document.querySelectorAll('.tab-btn');
            
            tabs.forEach(t => t.classList.remove('ativo'));
            
            if (tab === 'login') {
                loginForm.style.display = 'block';
                registoForm.style.display = 'none';
                tabs[0].classList.add('ativo');
            } else {
                loginForm.style.display = 'none';
                registoForm.style.display = 'block';
                tabs[1].classList.add('ativo');
            }
        }

        function mostrarCamposAdicionais() {
            const tipo = document.getElementById('tipo-registo').value;
            const campoEscalao = document.getElementById('campo-escalao');
            
            if (tipo === 'jogador') {
                campoEscalao.style.display = 'block';
                campoEscalao.querySelector('select').required = true;
            } else {
                campoEscalao.style.display = 'none';
                campoEscalao.querySelector('select').required = false;
            }
        }
    </script>
</body>
</html>