<?php

/**
 * Gera uma string de tipos para bind_param, com base nos tipos dos valores fornecidos.
 *
 * @param array $valores Array de valores cujos tipos devem ser identificados.
 * @return string String com os tipos do bind_param ('i' para int, 'd' para float, 's' para string).
 */
function obterTipos($valores) {
    $tipos = '';
    foreach ($valores as $valor) {
        if (is_int($valor)) {
            $tipos .= 'i';
        } elseif (is_double($valor)) {
            $tipos .= 'd';
        } else {
            $tipos .= 's';
        }
    }
    return $tipos;
}

/**
 * Insere dados em uma tabela do banco de dados de forma genérica.
 *
 * @param string $tabela Nome da tabela onde os dados serão inseridos.
 * @param array $dados Array associativo no formato ['coluna1' => valor1, 'coluna2' => valor2].
 * @return true|string Retorna true em caso de sucesso ou uma mensagem de erro em caso de falha.
 */
function inserirDados($tabela, $dados) {
    global $conn;

    $colunas = implode(", ", array_keys($dados));
    $placeholders = implode(", ", array_fill(0, count($dados), '?'));
    $valores = array_values($dados);
    $tipos = obterTipos($valores);

    $sql = "INSERT INTO $tabela ($colunas) VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return $conn->error;
    }

    $stmt->bind_param($tipos, ...$valores);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $erro = $stmt->error;
        $stmt->close();
        return $erro;
    }
}
