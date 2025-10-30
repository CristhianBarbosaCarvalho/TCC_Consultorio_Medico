<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['recepcao']);
require_once '../../functions/update.php';

if (!isset($_GET['id_agenda'])) {
    header("Location: gerenciar_agenda.php");
    exit();
}

$id_agenda = intval($_GET['id_agenda']);
$agenda = $conn->query("SELECT * FROM agenda WHERE id_agenda = $id_agenda")->fetch_assoc();

if (!$agenda) {
    header("Location: gerenciar_agenda.php");
    exit();
}

$mensagem = '';
$mensagem_tipo = '';

$dias_pt = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];

// ===========================
// PROCESSAMENTO DO FORMULÁRIO
// ===========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dia_semana = $_POST['dia_semana'];
    $data_agenda = $_POST['data_agenda'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fim = $_POST['hora_fim'];

    $dados = [
        'dia_semana' => $dia_semana,
        'data_agenda' => $data_agenda,
        'hora_inicio' => $hora_inicio,
        'hora_fim' => $hora_fim
    ];

    $resultado = atualizarDados('agenda', $dados, 'id_agenda', $id_agenda);

    if ($resultado === true) {
        $mensagem = "Horário atualizado com sucesso!";
        $mensagem_tipo = "alert-success";
    } else {
        $mensagem = "Erro ao atualizar: $resultado";
        $mensagem_tipo = "alert-danger";
    }

    // Recarregar dados atualizados
    $agenda = $conn->query("SELECT * FROM agenda WHERE id_agenda = $id_agenda")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Editar Horário</title>
<link rel="stylesheet" href="../../assets/css/style.css" />
<link rel="stylesheet" href="../../assets/css/agenda.css" />
<link rel="stylesheet" href="../../assets/css/editar_agenda.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
<header class="header">
    <div class="container header-content">
        <h1>Editar Horário</h1>
        <a href="gerenciar_agenda.php?id_medico=<?= $agenda['fk_id_medico'] ?>" class="logout-btn"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
</header>

<main class="agenda-container">
    <?php if (!empty($mensagem)): ?>
        <p class="alert <?= $mensagem_tipo ?>"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <form method="POST" class="form-agenda">
        <label for="dia_semana">Dia da Semana:</label>
        <select name="dia_semana" id="dia_semana" required>
            <?php foreach ($dias_pt as $index => $dia): ?>
                <option value="<?= $dia ?>" <?= $agenda['dia_semana'] === $dia ? 'selected' : '' ?>><?= $dia ?></option>
            <?php endforeach; ?>
        </select>

        <label for="data_agenda">Data:</label>
        <input type="date" name="data_agenda" id="data_agenda" value="<?= $agenda['data_agenda'] ?>" required min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+6 months')) ?>">

        <label for="hora_inicio">Hora Início:</label>
        <input type="time" name="hora_inicio" id="hora_inicio" value="<?= substr($agenda['hora_inicio'],0,5) ?>" required>

        <label for="hora_fim">Hora Fim:</label>
        <input type="time" name="hora_fim" id="hora_fim" value="<?= substr($agenda['hora_fim'],0,5) ?>" required>

        <button type="submit"><i class="fas fa-save"></i> Atualizar Horário</button>
    </form>
</main>
</body>
</html>
