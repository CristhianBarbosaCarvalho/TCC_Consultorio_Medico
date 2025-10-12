<?php
function excluirRegistro($conn, $tabela, $coluna_id, $valor_id, $tipo_id = 'i') {

    // Regra especial: Administrador
    if ($tabela === 'administrador') {
        $res = $conn->query("SELECT COUNT(*) as total FROM administrador");
        $total = $res->fetch_assoc()['total'];

        if ($total <= 1) {
            return "Não é possível excluir este administrador. Deve haver pelo menos um cadastrado.";
        }
    }

    // Exclusão do registro principal
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
