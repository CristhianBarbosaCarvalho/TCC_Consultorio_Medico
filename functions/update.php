<?php

/**
 * Atualiza dados em uma tabela genérica
 *  
 * @param string $tabela Nome da tabela
 * @param array $dados Associativo: ['coluna1' => valor1, ...]
 * @param string $colunaCondicao Nome da coluna de condição (ex: 'id')
 * @param mixed $valorCondicao Valor da condição (ex: 5)
 * @return bool|string true em sucesso ou mensagem de erro
 */
function atualizarDados($tabela, $dados, $colunaCondicao, $valorCondicao) {
    global $conn;

    $set = [];
    $valores = [];

    foreach ($dados as $coluna => $valor) {
        $set[] = "$coluna = ?";
        $valores[] = $valor;
    }

    $setString = implode(', ', $set);
    $sql = "UPDATE $tabela SET $setString WHERE $colunaCondicao = ?";

    $valores[] = $valorCondicao; // condição no final
    $tipos = obterTipos($valores);

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

/**
 * Detecta o tipo dos dados para bind_param (s, i, d)
 * @param array $valores
 * @return string
 */
function obterTipos($valores) {
    $tipos = '';
    foreach ($valores as $valor) {
        if (is_int($valor)) {
            $tipos .= 'i';
        } elseif (is_float($valor)) {
            $tipos .= 'd';
        } else {
            $tipos .= 's';
        }
    }
    return $tipos;
}

?>