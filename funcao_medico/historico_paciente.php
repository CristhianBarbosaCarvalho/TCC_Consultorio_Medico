<?php
require_once '../config_BD/conexaoBD.php';
require_once '../autenticacao/verificar_login.php';
verificarAcesso(['medico']);

$paciente_id = isset($_GET['id_paciente']) ? intval($_GET['id_paciente']) : 0;
$historicos = [];

if ($paciente_id > 0) {
    $sql = "SELECT h.*, m.nome AS medico_nome FROM historico_clinico h INNER JOIN medicos m ON h.fk_id_medico = m.id_medico WHERE h.fk_id_paciente = ? ORDER BY h.data_registro DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $paciente_id);
    $stmt->execute();
    $historicos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $term = trim($_POST['term']);
    $sql = "SELECT id_paciente, nome FROM paciente WHERE nome LIKE ? OR cpf LIKE ? LIMIT 20";
    $like = "%$term%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $patients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Histórico do Paciente</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container" style="max-width:1000px;margin:30px auto;">
        <h2>Histórico Clínico</h2>

        <?php if ($paciente_id <= 0): ?>
        <form method="POST" style="margin-bottom:20px;">
            <label>Buscar paciente por nome ou CPF:</label>
            <input type="text" name="term" required style="width:60%;padding:8px;">
            <button type="submit" style="padding:8px 12px;">Buscar</button>
        </form>

        <?php if (!empty($patients)): ?>
        <ul>
            <?php foreach($patients as $p): ?>
            <li>
                <?= htmlspecialchars($p['nome']) ?> —
                <a href="historico_paciente.php?id_paciente=<?= intval($p['id_paciente']) ?>">Ver histórico</a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <?php else: ?>
        <a href="historico_paciente.php" style="display:inline-block;margin-bottom:15px;">← Nova busca</a>
        <h3>Paciente ID <?= $paciente_id ?></h3>
        <?php if (empty($historicos)): ?>
        <p>Nenhum registro de histórico clínico encontrado para este paciente.</p>
        <?php else: ?>
        <?php foreach($historicos as $h): ?>
        <div
            style="background:#fff;padding:12px;margin-bottom:10px;border-radius:6px;box-shadow:0 0 6px rgba(0,0,0,0.06)">
            <strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($h['data_registro'])) ?> — <strong>Médico:</strong>
            <?= htmlspecialchars($h['medico_nome']) ?><br>
            <strong>Sintomas:</strong><br><?= nl2br(htmlspecialchars($h['sintomas'])) ?><br>
            <strong>Diagnóstico:</strong><br><?= nl2br(htmlspecialchars($h['diagnostico'])) ?><br>
            <strong>Prescrição:</strong><br><?= nl2br(htmlspecialchars($h['prescricao'])) ?><br>
            <strong>Observações:</strong><br><?= nl2br(htmlspecialchars($h['observacoes'])) ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
        <?php endif; ?>
        <a href="../funcao_medico/consultas_agendadas.php" class="btn" style="background:#6c757d">Voltar</a>
    </div>
</body>

</html>