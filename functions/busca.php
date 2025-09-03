<?php

function buscarDados($conn, $tabela, $filtros, $config_campos) {
    $query = "SELECT * FROM $tabela WHERE 1=1";
    $params = [];
    $types = "";

    foreach ($config_campos as $campo => $config) {
        if (!empty($filtros[$campo])) {
            switch ($config['operador']) {
                case 'like':
                    $query .= " AND $campo LIKE ?";
                    $params[] = '%' . $filtros[$campo] . '%';
                    break;
                case '=':
                    $query .= " AND $campo = ?";
                    $params[] = $filtros[$campo];
                    break;
                case 'idade':
                    $query .= " AND TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) = ?";
                    $params[] = $filtros[$campo];
                    break;
                // Você pode adicionar mais operadores aqui conforme necessário
            }
            $types .= $config['tipo'];
        }
    }

    $stmt = $conn->prepare($query);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}
?>