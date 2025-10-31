<?php
require_once '../../autenticacao/verificar_login.php';
require_once '../../config_BD/conexaoBD.php';

// Verifica se o ID da consulta foi enviado
if (!isset($_GET['id_consulta'])) {
    header("Location: ../agenda/gerenciar_agenda.php");
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

// Buscar pagamentos relacionados a essa consulta
$pagamentos = $conn->query("
    SELECT *
    FROM pagamentos
    WHERE fk_id_consulta = $id_consulta
    ORDER BY data_pagamento DESC
");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Pagamentos - Consulta</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .container {
            max-width: 900px;
            margin: 30px auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #3b82f6;
            color: white;
        }

        tr:hover {
            background-color: #f3f4f6;
        }

        .btn-voltar {
            display: inline-block;
            padding: 8px 15px;
            background: #3b82f6;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .btn-voltar:hover {
            background: #2563eb;
        }

        .footer-btn {
            text-align: center;
            margin-top: 25px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Pagamentos da Consulta</h2>

        <p><strong>Paciente:</strong> <?= htmlspecialchars($consulta['paciente_nome'] ?? '-') ?></p>
        <p><strong>Médico:</strong> <?= htmlspecialchars($consulta['medico_nome'] ?? '-') ?></p>
        <p><strong>Data da Consulta:</strong> <?= !empty($consulta['data_hora']) ? date("d/m/Y H:i", strtotime($consulta['data_hora'])) : '-' ?></p>

        <?php if ($pagamentos->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Data do Pagamento</th>
                        <th>Valor</th>
                        <th>Forma de Pagamento</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $pagamentos->fetch_assoc()): ?>
                        <tr>
                            <td><?= date("d/m/Y", strtotime($row['data_pagamento'])) ?></td>
                            <td>R$ <?= number_format($row['valor'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($row['forma_de_pagamento']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="font-style: italic; color: #6b7280;">Nenhum pagamento registrado para esta consulta.</p>
        <?php endif; ?>

        <!-- Botão Voltar no rodapé -->
        <div class="footer-btn">
            <a href="../agenda/gerenciar_agenda.php" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</body>
</html>