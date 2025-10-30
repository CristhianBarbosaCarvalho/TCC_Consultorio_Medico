<?php 
function limparNumero($valor) {
    return preg_replace('/\D/', '', $valor);
}

function validarCPF($cpf) {
    $cpfLimpo = limparNumero($cpf);
    return preg_match('/^\d{11}$/', $cpfLimpo);
}

function validarTelefone($telefone) {
    $telefoneLimpo = limparNumero($telefone);
    return preg_match('/^\d{10,11}$/', $telefoneLimpo);
}

function validarDataNascimento($dataNascimento) {
    $hoje = new DateTime();
    $dataNasc = DateTime::createFromFormat('Y-m-d', $dataNascimento);

    if (!$dataNasc || $dataNasc >= $hoje) {
        return "Erro: A data de nascimento deve ser anterior à data atual.";
    }

    $idade = $hoje->diff($dataNasc)->y;
    if ($idade > 120) {
        return "Erro: A idade não pode ser superior a 120 anos.";
    }

    return true;
}

/**
 * Função interna para pacientes
 */
function verificarDuplicidade($conn, $tabela, $cpf, $email) {
    $cpfLimpo = limparNumero($cpf);

    $sql = "SELECT cpf, email FROM $tabela WHERE cpf = ? OR email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erro ao preparar consulta: " . $conn->error);
    }

    $stmt->bind_param("ss", $cpfLimpo, $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $row = $resultado->fetch_assoc();
        $stmt->close();

        if ($row['cpf'] === $cpfLimpo && $row['email'] === $email) {
            return "❌ Já existe um paciente com este CPF e e-mail.";
        } elseif ($row['cpf'] === $cpfLimpo) {
            return "❌ Já existe um paciente com este CPF.";
        } elseif ($row['email'] === $email) {
            return "❌ Já existe um paciente com este e-mail.";
        }
    }

    $stmt->close();
    return false; // Nenhum conflito encontrado
}

/**
 * Função específica para pacientes
 */
function verificarPacienteExistente($conn, $cpf, $email) {
    return verificarDuplicidade($conn, 'paciente', $cpf, $email);
}

/**
 * Mantida apenas para funcionários
 */
function verificarDuplicidadeFuncionario($conn, $cpf, $email) {
    $cpfLimpo = limparNumero($cpf);
    $tabelas = ['medicos', 'recepcionista', 'administrador'];

    foreach ($tabelas as $tabela) {
        $sql = "SELECT cpf, email FROM $tabela WHERE cpf = ? OR email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Erro ao preparar consulta em $tabela: " . $conn->error);
        }

        $stmt->bind_param("ss", $cpfLimpo, $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            $stmt->close();

            if ($row['cpf'] === $cpfLimpo && $row['email'] === $email) {
                return "❌ Já existe um funcionário com este CPF e e-mail na tabela $tabela.";
            } elseif ($row['cpf'] === $cpfLimpo) {
                return "❌ Já existe um funcionário com este CPF na tabela $tabela.";
            } elseif ($row['email'] === $email) {
                return "❌ Já existe um funcionário com este e-mail na tabela $tabela.";
            }
        }
        $stmt->close();
    }

    return false; // Nenhum conflito encontrado
}
?>
