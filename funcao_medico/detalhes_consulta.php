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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    .card {
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
        max-width: 800px;
        margin: 30px auto;
    }

    .info p {
        margin: 8px 0;
    }

    .btn {
        display: inline-block;
        padding: 10px 15px;
        border-radius: 6px;
        text-decoration: none;
        color: #fff;
        font-weight: 500;
    }

    .btn-pdf {
        background: #4e5251;
    }

    .btn-voltar {
        background: #6c757d;
    }
    </style>
</head>

<body>
    <header class="header">
        <div class="container header-content">
            <h1>Detalhes da Consulta</h1>
        </div>
    </header>

    <nav class="navbar">
        <div class="container">
            <ul class="nav-list">
                <a href="../funcao_medico/consultas_agendadas.php" class="nav-link">Voltar às Consultas</a>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Consulta de <?= htmlspecialchars($consulta['paciente']) ?></h2>
            <div class="info">
                <p><strong>Médico:</strong> <?= htmlspecialchars($consulta['medico']) ?></p>
                <p><strong>Data/Hora:</strong> <?= date('d/m/Y H:i', strtotime($consulta['data_hora'])) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($consulta['status']) ?></p>
                <p><strong>Observações:</strong><br><?= nl2br(htmlspecialchars($consulta['observacoes'])) ?></p>
                <p><strong>Sintomas:</strong><br><?= nl2br(htmlspecialchars($consulta['sintomas'])) ?></p>
                <p><strong>Diagnóstico:</strong><br><?= nl2br(htmlspecialchars($consulta['diagnostico'])) ?></p>
                <p><strong>Prescrição:</strong><br><?= nl2br(htmlspecialchars($consulta['prescricao'])) ?></p>
            </div>
            <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                <a href="prontuario_pdf.php?id_consulta=<?= $id_consulta ?>" target="_blank" class="btn btn-pdf">
                    <i class="fa-solid fa-file-pdf"></i> Gerar PDF
                </a>
            </div>
        </div>
    </div>
</body>

</html>