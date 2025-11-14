<?php
// corrigir_passwords.php - Script para encriptar passwords existentes
require_once 'configuracao.php';

echo "<!DOCTYPE html>
<html lang='pt-PT'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Corrigir Passwords</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .resultado {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .sucesso { color: #10b981; }
        .erro { color: #ef4444; }
        .info { color: #3b82f6; }
        h1 { color: #0f172a; }
        .credenciais {
            background: #f8fafc;
            padding: 15px;
            border-left: 4px solid #6366f1;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>🔐 Correção de Passwords</h1>";

// Mapeamento de passwords originais (do SQL)
$passwords_originais = [
    // Direção
    'direcao_clubes' => [
        'alcantrafc@gmail.com' => 'Alcantraclube87',
        'chiadofc@gmail.com' => 'CHIADOclube@2025',
        'Cascaisfc@gmail.com' => 'siacsacfc26#'
    ],
    // Treinadores
    'treinadores' => [
        'Jorgeconc@gmail.com' => 'Conceiçao.jorge#',
        'Rubenmour@gmail.com' => 'Ruben_mmourinhoo.',
        'Nunofariollii@gmail.com' => 'FARIOLLInuno1988'
    ],
    // Jogadores
    'jogadores' => [
        'Santos@gmail.com' => 'Santos;Gustavo#',
        'JoaquimGonçalves@gmail.com' => 'JOAQUIMG!!',
        'KevinRaposo@gmail.com' => '2009K_RAPOSO'
    ]
];

$total_corrigidos = 0;
$erros = 0;

// Processar cada tabela
foreach ($passwords_originais as $tabela => $utilizadores) {
    echo "<div class='resultado'>";
    echo "<h2>📋 Tabela: $tabela</h2>";
    
    foreach ($utilizadores as $email => $password_original) {
        try {
            // Verificar se o utilizador existe
            $stmt = $ligacao_bd->prepare("SELECT id, email, pass FROM $tabela WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Verificar se a password já está encriptada
                if (password_verify($password_original, $user['pass'])) {
                    echo "<div class='info'>✓ $email - Password já está encriptada</div>";
                } else {
                    // Encriptar e atualizar
                    $pass_hash = password_hash($password_original, PASSWORD_DEFAULT);
                    $stmt = $ligacao_bd->prepare("UPDATE $tabela SET pass = ? WHERE id = ?");
                    $stmt->execute([$pass_hash, $user['id']]);
                    
                    echo "<div class='sucesso'>✅ $email - Password encriptada com sucesso!</div>";
                    echo "<div class='credenciais'>";
                    echo "<strong>Email:</strong> $email<br>";
                    echo "<strong>Password:</strong> $password_original";
                    echo "</div>";
                    $total_corrigidos++;
                }
            } else {
                echo "<div class='erro'>❌ $email - Utilizador não encontrado</div>";
                $erros++;
            }
        } catch (PDOException $e) {
            echo "<div class='erro'>❌ Erro ao processar $email: " . $e->getMessage() . "</div>";
            $erros++;
        }
    }
    
    echo "</div>";
}

echo "<div class='resultado'>";
echo "<h2>📊 Resumo</h2>";
echo "<p><strong>Total corrigido:</strong> <span class='sucesso'>$total_corrigidos</span></p>";
echo "<p><strong>Erros:</strong> <span class='erro'>$erros</span></p>";
echo "<p class='info'>⚠️ <strong>Importante:</strong> Guarda as credenciais acima para fazer login!</p>";
echo "<p><a href='autenticacao.php' style='display: inline-block; margin-top: 20px; padding: 12px 24px; background: #6366f1; color: white; text-decoration: none; border-radius: 8px;'>Ir para Login</a></p>";
echo "</div>";

echo "</body></html>";
?>