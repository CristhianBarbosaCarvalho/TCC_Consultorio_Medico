<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['recepcao']);
header('Content-Type: application/json');

$id_esp = intval($_GET['id_especialidade'] ?? 0);
if ($id_esp <= 0) { echo json_encode([]); exit; }

$sql = "SELECT m.id_medico, m.nome 
        FROM medicos m
        INNER JOIN medico_especialidade me ON me.id_medico = m.id_medico
        WHERE me.id_especialidade = ?
        ORDER BY m.nome";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_esp);
$stmt->execute();
$res = $stmt->get_result();

$medicos = [];
while ($r = $res->fetch_assoc()) $medicos[] = $r;
echo json_encode($medicos);
