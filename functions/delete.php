<?php
function excluirRegistro($conn, $tabela, $coluna_id, $valor_id, $tipo_id = 'i') {
    $sql = "DELETE FROM $tabela WHERE $coluna_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return "Erro na preparação: " . $conn->error;
    }

    $stmt->bind_param($tipo_id, $valor_id);
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $erro = $stmt->error;
        $stmt->close();
        return "Erro ao excluir: $erro";
    }
}
?>
