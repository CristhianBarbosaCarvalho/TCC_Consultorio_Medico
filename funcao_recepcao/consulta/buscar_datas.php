<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['recepcao']);
header('Content-Type: application/json');

$id_medico = intval($_GET['id_medico'] ?? 0);
if ($id_medico <= 0) { echo json_encode([]); exit; }

// Buscar datas distintas (data_agenda) para esse médico
$stmt = $conn->prepare("SELECT DISTINCT data_agenda FROM agenda WHERE fk_id_medico = ? ORDER BY data_agenda");
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$res = $stmt->get_result();

$datas = [];
while ($r = $res->fetch_assoc()) $datas[] = $r;
echo json_encode($datas);
