<?php
// cabecalho.php - Componente de cabeçalho

require_once 'configuracao.php';

if (!esta_autenticado()) {
    header('Location: autenticacao.php');
    exit();
}

$iniciais = strtoupper(substr($_SESSION['utilizador_nome'], 0, 1));
$tipo_texto = '';
switch ($_SESSION['tipo_utilizador']) {
    case 'direcao':
        $tipo_texto = 'Direção';
        break;
    case 'treinador':
        $tipo_texto = 'Treinador';
        break;
    case 'jogador':
        $tipo_texto = 'Jogador';
        break;
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina . ' - ' : ''; ?><?php echo NOME_SITE; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="contentor-principal">
        <?php include 'navegacao.php'; ?>
        
        <div class="conteudo-principal">
            <div class="cabecalho fade-in">
                <div>
                    <h1><?php echo isset($titulo_pagina) ? $titulo_pagina : 'Dashboard'; ?></h1>
                    <?php if (isset($subtitulo_pagina)): ?>
                        <p style="color: var(--cor-texto-claro); margin-top: 0.5rem;"><?php echo $subtitulo_pagina; ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="info-utilizador">
                    <div style="text-align: right;">
                        <div style="font-weight: 600;"><?php echo $_SESSION['utilizador_nome']; ?></div>
                        <div style="font-size: 0.875rem; color: var(--cor-texto-claro);"><?php echo $tipo_texto; ?></div>
                    </div>
                    <div class="avatar"><?php echo $iniciais; ?></div>
                </div>
            </div>

            <?php
            $mensagem = obter_mensagem();
            if ($mensagem):
            ?>
                <div class="alerta alerta-<?php echo $mensagem['tipo']; ?> fade-in">
                    <?php echo $mensagem['texto']; ?>
                </div>
            <?php endif; ?>
            
            <div class="fade-in"  style="animation-delay: 0.1s;">