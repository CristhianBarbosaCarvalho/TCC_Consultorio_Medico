<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['medico']);

// Pega o ID da consulta passado na URL
$id_consulta = intval($_GET['id'] ?? 0);

if ($id_consulta <= 0) {
    echo "Consulta inválida.";
    exit();
}

// Consulta os dados da consulta e do paciente
$sql = "
    SELECT 
        c.id_consulta,
        c.data_hora,
        c.status AS status_consulta,
        c.observacoes,
        p.nome AS paciente,
        p.email AS paciente_email,
        p.telefone AS paciente_telefone,
        m.nome AS medico
    FROM consulta c
    INNER JOIN paciente p ON c.fk_id_paciente = p.id_paciente
    INNER JOIN medicos m ON c.fk_id_medico = m.id_medico
    WHERE c.id_consulta = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_consulta);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Consulta não encontrada.";
    exit();
}

$consulta = $result->fetch_assoc();

// Consulta o pagamento, se houver
$sqlPag = "SELECT valor, forma_de_pagamento, status FROM pagamentos WHERE fk_id_consulta = ?";
$stmtPag = $conn->prepare($sqlPag);
$stmtPag->bind_param("i", $id_consulta);
$stmtPag->execute();
$resultPag = $stmtPag->get_result();
$pagamento = $resultPag->fetch_assoc() ?? null;

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Detalhes da Consulta</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .container { width: 80%; margin: 40px auto; background: #fff; padding: 20px; border-radius: 8px; }
        h2 { text-align: center; margin-bottom: 20px; }
        .info { margin-bottom: 15px; }
        .info label { font-weight: bold; }
        .btn-voltar { display: block; margin: 30px auto; padding: 10px 20px; background-color: #3498db; color: #fff; text-decoration: none; border-radius: 6px; text-align: center; }
        .btn-voltar:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Detalhes da Consulta</h2>

        <div class="info"><label>Paciente:</label> <?= htmlspecialchars($consulta['paciente']) ?></div>
        <div class="info"><label>Email:</label> <?= htmlspecialchars($consulta['paciente_email']) ?></div>
        <div class="info"><label>Telefone:</label> <?= htmlspecialchars($consulta['paciente_telefone']) ?></div>
        <div class="info"><label>Médico:</label> <?= htmlspecialchars($consulta['medico']) ?></div>
        <div class="info"><label>Data/Hora:</label> <?= date('d/m/Y H:i', strtotime($consulta['data_hora'])) ?></div>
        <div class="info"><label>Status da Consulta:</label> <?= htmlspecialchars($consulta['status_consulta']) ?></div>
        <div class="info"><label>Observações:</label> <?= htmlspecialchars($consulta['observacoes']) ?></div>

        <?php if ($pagamento): ?>
            <div class="info"><label>Valor:</label> R$ <?= number_format($pagamento['valor'], 2, ',', '.') ?></div>
            <div class="info"><label>Forma de Pagamento:</label> <?= htmlspecialchars($pagamento['forma_de_pagamento']) ?></div>
            <div class="info"><label>Status do Pagamento:</label> <?= htmlspecialchars($pagamento['status']) ?></div>
        <?php else: ?>
            <div class="info"><label>Pagamento:</label> Não registrado</div>
        <?php endif; ?>

        <a href="../funcao_medico/consultas_agendadas.php" class="btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar para Consultas Agendadas
        </a>
    </div>
</body>
</html>