<?php
// configuracao.php - Configurações gerais e ligação à base de dados

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações da base de dados
define('BD_SERVIDOR', 'localhost');
define('BD_UTILIZADOR', 'root');
define('BD_PALAVRA_PASSE', 'nova_password');
define('BD_NOME', 'pap');

// Configurações do site
define('NOME_SITE', 'GESTEAM');
define('URL_BASE', 'http://localhost/gestao-clube/');
define('PASTA_UPLOADS', 'uploads/');

// Fuso horário
date_default_timezone_set('Europe/Lisbon');

// Ligação à base de dados
try {
    $ligacao_bd = new PDO(
        "mysql:host=" . BD_SERVIDOR . ";dbname=" . BD_NOME . ";charset=utf8mb4",
        BD_UTILIZADOR,
        BD_PALAVRA_PASSE,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $erro) {
    die("Erro na ligação à base de dados: " . $erro->getMessage());
}

// Funções auxiliares para mensagens
function definir_mensagem($tipo, $texto) {
    $_SESSION['mensagem'] = [
        'tipo' => $tipo,
        'texto' => $texto
    ];
}

function obter_mensagem() {
    if (isset($_SESSION['mensagem'])) {
        $mensagem = $_SESSION['mensagem'];
        unset($_SESSION['mensagem']);
        return $mensagem;
    }
    return null;
}

// Verificar se utilizador está autenticado
function esta_autenticado() {
    return isset($_SESSION['utilizador_id']);
}

// Verificar tipo de utilizador
function e_direcao() {
    return isset($_SESSION['tipo_utilizador']) && $_SESSION['tipo_utilizador'] === 'direcao';
}

function e_treinador() {
    return isset($_SESSION['tipo_utilizador']) && $_SESSION['tipo_utilizador'] === 'treinador';
}

function e_jogador() {
    return isset($_SESSION['tipo_utilizador']) && $_SESSION['tipo_utilizador'] === 'jogador';
}

// Redirecionar se não estiver autenticado
function requer_autenticacao() {
    if (!esta_autenticado()) {
        header('Location: autenticacao.php');
        exit();
    }
}

// Redirecionar se não for direção
function requer_direcao() {
    if (!e_direcao()) {
        header('Location: inicio.php');
        exit();
    }
}

// Sanitizar entrada de dados
function limpar_entrada($dados) {
    $dados = trim($dados);
    $dados = stripslashes($dados);
    $dados = htmlspecialchars($dados, ENT_QUOTES, 'UTF-8');
    return $dados;
}

// Formatar data para português
function formatar_data($data) {
    return date('d/m/Y', strtotime($data));
}

// Formatar data e hora para português
function formatar_data_hora($data) {
    return date('d/m/Y H:i', strtotime($data));
}
?>