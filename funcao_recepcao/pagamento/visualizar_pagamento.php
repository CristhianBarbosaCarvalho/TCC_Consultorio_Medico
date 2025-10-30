<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['medico', 'recepcao']);

// Verificar se o ID da consulta foi enviado
if (!isset($_GET['id_consulta'])) {
    header("Location: gerenciar_consultas.php");
    exit();
}

$id_consulta = intval($_GET['id_consulta']);

// Buscar dados da consulta
$consulta = $conn->query("
    SELECT c.data_hora, p.nome AS paciente_nome, m.nome AS medico_nome
    FROM consulta c
    JOIN paciente p ON c.fk_id_paciente = p.id_paciente
    JOIN medicos m ON c.fk_id_medico = m.id_medico
    WHERE c.id_consulta = $id_consulta
")->fetch_assoc();

// Buscar pagamento da consulta
$pagamento = $conn->query("
    SELECT *
    FROM pagamentos
    WHERE fk_id_consulta = $id_consulta
")->fetch_assoc();

// Garante que a sessão esteja ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define o link do painel de acordo com o tipo de usuário logado
$painelLink = '#';
if (isset($_SESSION['usuario_tipo'])) {
    switch ($_SESSION['usuario_tipo']) {
        case 'medico':
            $painelLink = '../../dashboard_users/medico.php';
            break;
        case 'recepcao':
            $painelLink = '../../dashboard_users/recepcao.php';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Pagamento</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .container {
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        p {
            margin: 10px 0;
            font-size: 1rem;
        }

        .btn-voltar {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #3b82f6;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.2s ease-in-out;
        }

        .btn-voltar:hover {
            background-color: #2563eb;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Detalhes do Pagamento</h2>

        <p><strong>Paciente:</strong> <?= htmlspecialchars($consulta['paciente_nome'] ?? '-') ?></p>
        <p><strong>Médico:</strong> <?= htmlspecialchars($consulta['medico_nome'] ?? '-') ?></p>
        <p><strong>Data da Consulta:</strong> <?= !empty($consulta['data_hora']) ? date("d/m/Y H:i", strtotime($consulta['data_hora'])) : '-' ?></p>

        <?php if ($pagamento): ?>
            <p><strong>Data do Pagamento:</strong> <?= date("d/m/Y", strtotime($pagamento['data_pagamento'])) ?></p>
            <p><strong>Valor:</strong> R$ <?= number_format($pagamento['valor'], 2, ',', '.') ?></p>
            <p><strong>Forma de Pagamento:</strong> <?= htmlspecialchars($pagamento['forma_de_pagamento']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($pagamento['status']) ?></p>
        <?php else: ?>
            <p style="font-style: italic; color: #6b7280;">Nenhum pagamento registrado para esta consulta.</p>
        <?php endif; ?>

        <a href="<?= $painelLink ?>" class="btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar ao Painel
        </a>
    </div>
</body>

</html>
