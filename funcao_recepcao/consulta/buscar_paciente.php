<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['recepcao']);

$q = trim($_GET['q'] ?? '');
header('Content-Type: application/json');

if ($q === '') {
    echo json_encode([]);
    exit;
}

$like = "%$q%";
$stmt = $conn->prepare("SELECT id_paciente, nome, cpf FROM paciente WHERE nome LIKE ? OR cpf LIKE ? ORDER BY nome LIMIT 12");
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);
