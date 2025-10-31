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

// Buscar dados existentes da consulta/paciente
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

    // Validação
    if (empty($sintomas) && empty($diagnostico) && empty($prescricao)) {
        $errors[] = "Preencha pelo menos um campo: sintomas, diagnóstico ou prescrição.";
    }

    if (empty($errors)) {
        // Inserir no histórico clínico
        $insert = $conn->prepare("INSERT INTO historico_clinico 
            (fk_id_paciente, fk_id_medico, sintomas, diagnostico, prescricao, observacoes) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("iissss", $consulta['fk_id_paciente'], $medico_id, $sintomas, $diagnostico, $prescricao, $observacoes);
        $ok1 = $insert->execute();
        $insert->close();

        // Atualizar consulta com exames solicitados
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
<style>
form {
    max-width: 800px;
    margin: 30px auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 0 8px rgba(0,0,0,0.08);
}
textarea, input {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
}
button {
    padding: 10px 15px;
    background: #4e5251;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
</style>
</head>
<body>
<form method="POST">
    <h2>Prontuário - <?= htmlspecialchars($consulta['paciente']) ?></h2>

    <?php if (!empty($errors)): ?>
        <div style="background:#f8d7da;padding:10px;border-radius:5px;margin-bottom:10px;color:#721c24;">
            <?php foreach($errors as $e) echo "<div>" . htmlspecialchars($e) . "</div>"; ?>
        </div>
    <?php endif; ?>

    <label>Sintomas</label>
    <textarea name="sintomas" rows="4"><?= htmlspecialchars($_POST['sintomas'] ?? $consulta['sintomas']) ?></textarea>

    <label>Diagnóstico</label>
    <textarea name="diagnostico" rows="4"><?= htmlspecialchars($_POST['diagnostico'] ?? $consulta['diagnostico']) ?></textarea>

    <label>Prescrição</label>
    <textarea name="prescricao" rows="4"><?= htmlspecialchars($_POST['prescricao'] ?? $consulta['prescricao']) ?></textarea>

    <label>Exames Solicitados</label>
    <textarea name="exames_solicitados" rows="4"><?= htmlspecialchars($_POST['exames_solicitados'] ?? $consulta['exames_solicitados']) ?></textarea>

    <label>Observações</label>
    <textarea name="observacoes" rows="4"><?= htmlspecialchars($_POST['observacoes'] ?? $consulta['observacoes']) ?></textarea>

    <div style="display:flex;gap:10px;margin-top:15px;">
        <button type="submit">Salvar Prontuário</button>
        <a href="prontuario_pdf.php?id_consulta=<?= $id_consulta ?>" target="_blank" style="display:inline-block;padding:10px 15px;background:#6c757d;color:#fff;border-radius:5px;text-decoration:none">Gerar PDF</a>
        <a href="consultas_agendadas.php" style="display:inline-block;padding:10px 15px;background:#adb5bd;color:#000;border-radius:5px;text-decoration:none">Voltar</a>
    </div>
</form>
</body>
</html>
