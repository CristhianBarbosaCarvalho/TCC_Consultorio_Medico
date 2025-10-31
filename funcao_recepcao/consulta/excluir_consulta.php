<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['recepcao']);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Consulta inválida.");
}

$id_consulta = intval($_GET['id']);

// Buscar a consulta e o agendamento associado
$sql = "SELECT c.*, a.id_agendamento
        FROM consulta c
        LEFT JOIN agendamento a ON a.fk_id_agenda = c.fk_id_agenda 
            AND a.dia_consulta = DATE(c.data_hora)
            AND a.hora_agendada = TIME(c.data_hora)
        WHERE c.id_consulta = $id_consulta";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    die("Consulta não encontrada.");
}

$consulta = $result->fetch_assoc();

$conn->begin_transaction();

try {
    // Excluir pagamento se existir
    $conn->query("DELETE FROM pagamentos WHERE fk_id_consulta = $id_consulta");

    // Excluir a consulta
    $conn->query("DELETE FROM consulta WHERE id_consulta = $id_consulta");

    // Liberar horário na agenda (remover agendamento se existir)
    if (!empty($consulta['id_agendamento'])) {
        $conn->query("DELETE FROM agendamento WHERE id_agendamento = {$consulta['id_agendamento']}");
    }

    $conn->commit();

    header("Location: relatorio_consulta.php?status=sucesso&msg=" . urlencode("Consulta excluída com sucesso!"));
    exit;
} catch (Exception $e) {
    $conn->rollback();
    die("Erro ao excluir consulta: " . $e->getMessage());
}
?>
