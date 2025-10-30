<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['recepcao']);
header('Content-Type: application/json');

$id_medico = intval($_GET['id_medico'] ?? 0);
$data = $_GET['data'] ?? null;

if ($id_medico <= 0 || !$data) { echo json_encode([]); exit; }

// Buscar todas as entradas de agenda do médico para essa data_agenda
$stmt = $conn->prepare("SELECT id_agenda, hora_inicio, hora_fim FROM agenda WHERE fk_id_medico = ? AND data_agenda = ? ORDER BY hora_inicio");
$stmt->bind_param("is", $id_medico, $data);
$stmt->execute();
$res = $stmt->get_result();

$disponiveis = [];

// Para cada faixa (hora_inicio - hora_fim) gerar slots de 1h
while ($row = $res->fetch_assoc()) {
    $id_agenda = (int)$row['id_agenda'];
    $inicio_ts = strtotime($row['hora_inicio']);
    $fim_ts = strtotime($row['hora_fim']);
    for ($t = $inicio_ts; $t < $fim_ts; $t += 3600) {
        $hora = date('H:i', $t);
        // verificar se já existe agendamento para essa agenda específica, data e hora
        $stmt2 = $conn->prepare("SELECT COUNT(*) AS total FROM agendamento WHERE fk_id_agenda = ? AND dia_consulta = ? AND hora_agendada = ?");
        $stmt2->bind_param("iss", $id_agenda, $data, $hora);
        $stmt2->execute();
        $r2 = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
        if ($r2['total'] == 0) {
            // incluir no array com referência ao id_agenda (útil ao salvar)
            $disponiveis[] = ['hora' => $hora, 'id_agenda' => $id_agenda];
        }
    }
}

// Para simplificar no frontend, retornamos apenas as horas (mas mantemos id_agenda na resposta)
echo json_encode($disponiveis);
