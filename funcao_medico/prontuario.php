<?php
require_once '../config_BD/conexaoBD.php';
require_once '../autenticacao/verificar_login.php';
verificarAcesso(['medico']);
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_GET['id_consulta'])) {
    die("Consulta não especificada.");
}
$id_consulta = intval($_GET['id_consulta']);
$medico_id = intval($_SESSION['usuario_id']);

$sql = "SELECT c.*, p.nome AS paciente, p.cpf, p.data_nascimento 
        FROM consulta c 
        INNER JOIN paciente p ON c.fk_id_paciente = p.id_paciente 
        WHERE c.id_consulta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_consulta);
$stmt->execute();
$consulta = $stmt->get_result()->fetch_assoc();
if (!$consulta) die("Consulta não encontrada.");

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sintomas = trim($_POST['sintomas'] ?? '');
    $diagnostico = trim($_POST['diagnostico'] ?? '');
    $prescricao = trim($_POST['prescricao'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    $exames_solicitados = trim($_POST['exames_solicitados'] ?? '');
    $status = 'concluida';

    if (empty($sintomas) && empty($diagnostico) && empty($prescricao)) {
        $errors[] = "Preencha pelo menos um campo: sintomas, diagnóstico ou prescrição.";
    }

    if (empty($errors)) {
        $insert = $conn->prepare("INSERT INTO historico_clinico 
            (fk_id_paciente, fk_id_medico, sintomas, diagnostico, prescricao, observacoes) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("iissss", $consulta['fk_id_paciente'], $medico_id, $sintomas, $diagnostico, $prescricao, $observacoes);
        $ok1 = $insert->execute();
        $insert->close();

        $update = $conn->prepare("UPDATE consulta 
            SET status = ?, sintomas = ?, diagnostico = ?, prescricao = ?, observacoes = ?, exames_solicitados = ? 
            WHERE id_consulta = ?");
        $update->bind_param("ssssssi", $status, $sintomas, $diagnostico, $prescricao, $observacoes, $exames_solicitados, $id_consulta);
        $ok2 = $update->execute();
        $update->close();

        if ($ok1 && $ok2) {
            header("Location: consultas_agendadas.php?msg=prontuario_salvo");
            exit;
        } else {
            $errors[] = "Erro ao salvar o prontuário. Tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Prontuário da Consulta</title>
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

    textarea,
    input {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    button,
    .btn {
        display: inline-block;
        padding: 10px 15px;
        border-radius: 6px;
        border: none;
        text-decoration: none;
        font-weight: 500;
    }

    .btn-salvar {
        background: #4e5251;
        color: #fff;
    }

    .btn-pdf {
        background: #6c757d;
        color: #fff;
    }

    .btn-voltar {
        background: #adb5bd;
        color: #000;
    }

    .error {
        background: #f8d7da;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
        color: #721c24;
    }
    </style>
</head>

<body>
    <header class="header">
        <div class="container header-content">
            <h1>Prontuário da Consulta</h1>
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
            <h2>Paciente: <?= htmlspecialchars($consulta['paciente']) ?></h2>

            <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach($errors as $e) echo "<div>" . htmlspecialchars($e) . "</div>"; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <label>Sintomas</label>
                <textarea name="sintomas"
                    rows="4"><?= htmlspecialchars($_POST['sintomas'] ?? $consulta['sintomas']) ?></textarea>

                <label>Diagnóstico</label>
                <textarea name="diagnostico"
                    rows="4"><?= htmlspecialchars($_POST['diagnostico'] ?? $consulta['diagnostico']) ?></textarea>

                <label>Prescrição</label>
                <textarea name="prescricao"
                    rows="4"><?= htmlspecialchars($_POST['prescricao'] ?? $consulta['prescricao']) ?></textarea>

                <label>Exames Solicitados</label>
                <textarea name="exames_solicitados"
                    rows="4"><?= htmlspecialchars($_POST['exames_solicitados'] ?? $consulta['exames_solicitados']) ?></textarea>

                <label>Observações</label>
                <textarea name="observacoes"
                    rows="4"><?= htmlspecialchars($_POST['observacoes'] ?? $consulta['observacoes']) ?></textarea>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:15px;">
                    <button type="submit" class="btn btn-salvar">
                        <i class="fa-solid fa-floppy-disk"></i> Salvar
                    </button>
                    <a href="prontuario_pdf.php?id_consulta=<?= $id_consulta ?>" target="_blank" class="btn btn-pdf">
                        <i class="fa-solid fa-file-pdf"></i> Gerar PDF
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>