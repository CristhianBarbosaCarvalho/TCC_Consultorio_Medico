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
function verificarPacienteExistente($conn, $cpf, $email) {
    $verifica = $conn->prepare("SELECT id_paciente FROM paciente WHERE cpf = ? OR email = ?");
    $verifica->bind_param("ss", $cpf, $email);
    $verifica->execute();
    $verifica->store_result();

    $existe = $verifica->num_rows > 0;
    $verifica->close();

    return $existe;
}