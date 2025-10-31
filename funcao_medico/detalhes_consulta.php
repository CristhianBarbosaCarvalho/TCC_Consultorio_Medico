<?php
require_once '../config_BD/conexaoBD.php';
require_once '../autenticacao/verificar_login.php';
verificarAcesso(['medico']);

if (!isset($_GET['id'])) {
    die("Consulta não encontrada.");
}
$id_consulta = intval($_GET['id']);

$sql = "SELECT c.*, p.nome AS paciente, p.cpf, p.email, p.telefone, m.nome AS medico
        FROM consulta c
        INNER JOIN paciente p ON c.fk_id_paciente = p.id_paciente
        INNER JOIN medicos m ON c.fk_id_medico = m.id_medico
        WHERE c.id_consulta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_consulta);
$stmt->execute();
$consulta = $stmt->get_result()->fetch_assoc();
if (!$consulta) {
    die("Consulta não encontrada.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Detalhes da Consulta</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    .card {
        max-width: 800px;
        margin: 40px auto;
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1)
    }

    .btn {
        display: inline-block;
        margin-top: 10px;
        padding: 8px 15px;
        background: #4e5251;
        color: white;
        text-decoration: none;
        border-radius: 5px
    }
    </style>
</head>

<body>
    <div class="card">
        <h2>Consulta de <?= htmlspecialchars($consulta['paciente']) ?></h2>
        <p><strong>Médico:</strong> <?= htmlspecialchars($consulta['medico']) ?></p>
        <p><strong>Data/Hora:</strong> <?= date('d/m/Y H:i', strtotime($consulta['data_hora'])) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($consulta['status']) ?></p>
        <p><strong>Observações:</strong><br><?= nl2br(htmlspecialchars($consulta['observacoes'])) ?></p>
        <p><strong>Sintomas:</strong><br><?= nl2br(htmlspecialchars($consulta['sintomas'])) ?></p>
        <p><strong>Diagnóstico:</strong><br><?= nl2br(htmlspecialchars($consulta['diagnostico'])) ?></p>
        <p><strong>Prescrição:</strong><br><?= nl2br(htmlspecialchars($consulta['prescricao'])) ?></p>

        <a href="prontuario.php?id_consulta=<?= $id_consulta ?>" class="btn">Editar/Registrar Prontuário</a>
        <a href="prontuario_pdf.php?id_consulta=<?= $id_consulta ?>" class="btn" target="_blank">Gerar PDF do
            Prontuário</a>
        <a href="../funcao_medico/consultas_agendadas.php" class="btn" style="background:#6c757d">Voltar</a>
    </div>
</body>

</html>