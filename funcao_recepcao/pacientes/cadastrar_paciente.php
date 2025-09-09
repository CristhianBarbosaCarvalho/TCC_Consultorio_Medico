<?php
require_once '../../autenticacao/verificar_login.php';
require_once '../../config_BD/conexaoBD.php';
require_once '../../functions/validacoes.php';
require_once '../../functions/insert.php';

$mensagem = '';
$mensagem_tipo = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST['nome'];
    $data_nascimento = $_POST['data_nascimento'];
    $cpf = $_POST['cpf'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];

    $rua = $_POST['rua'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $estado = strtoupper($_POST['estado']);
    $numero = trim($_POST['numero']) !== '' ? $_POST['numero'] : 's/n';
    $endereco = "$rua, $numero - $bairro, $cidade - $estado";

    // Validações
    $erroData = validarDataNascimento($data_nascimento);
    if ($erroData !== true) {
        $mensagem = $erroData;
        $mensagem_tipo = "alert-danger";
    } elseif (!validarCPF($cpf)) {
        $mensagem = "Erro: CPF inválido. Deve conter 11 dígitos numéricos.";
        $mensagem_tipo = "alert-danger";
    } elseif (!validarTelefone($telefone)) {
        $mensagem = "Erro: Telefone inválido. Deve conter 10 ou 11 dígitos.";
        $mensagem_tipo = "alert-danger";
    } elseif (verificarPacienteExistente($conn, $cpf, $email)) {
        $mensagem = "Erro: CPF ou email já cadastrados!";
        $mensagem_tipo = "alert-danger";
    } else {
        $dadosPaciente = [
            'nome' => $nome,
            'data_nascimento' => $data_nascimento,
            'cpf' => $cpf,
            'email' => $email,
            'telefone' => $telefone,
            'endereco' => $endereco
        ];

        $resultado = inserirDados('paciente', $dadosPaciente);

        if ($resultado === true) {
            $mensagem = "Paciente cadastrado com sucesso!";
            $mensagem_tipo = "alert-success";
        } else {
            $mensagem = "Erro ao cadastrar paciente: " . $resultado;
            $mensagem_tipo = "alert-danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Paciente</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="../../assets/js/formatacao.js" defer></script>
</head>
<body>

<header class="header">
    <div class="container header-content">
        <h1>Cadastrar Paciente</h1>
        <a href="../../views/logout.php" class="logout-btn">Sair</a>
    </div>
</header>

<nav class="navbar">
    <div class="container">
        <ul class="nav-list">
            <a href="../../dashboard_users/recepcao.php" class="nav-link">Voltar</a>
        </ul>
    </div>
</nav>

<div class="container" style="margin-top: 100px; max-width: 600px;">
    <?php if (!empty($mensagem)) : ?>
        <p class="alert <?= $mensagem_tipo ?>"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <div class="card">
        <form method="POST" class="form-content">
            <div class="form-group">
                <label class="form-label">Nome:</label>
                <input type="text" name="nome" class="form-control" placeholder="Digite o nome completo" required>                
            </div>

            <div class="form-group">
                <label class="form-label">Data de Nascimento:</label>
                <input type="date" name="data_nascimento" class="form-control" required
                       min="<?= date('Y-m-d', strtotime('-120 years')) ?>"
                       max="<?= date('Y-m-d', strtotime('-1 day')) ?>">
            </div>

            <div class="form-group"> 
                <label class="form-label">CPF:</label>
                <input type="text" name="cpf" class="form-control cpf-mask" placeholder="123.456.789-00" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email:</label>
                <input type="email" name="email" class="form-control" placeholder="exemplo@email.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">Telefone:</label>
                <input type="text" name="telefone" class="form-control telefone-mask" placeholder="(00) 0000-0000" required>
            </div>

            <div class="form-group">
                <label class="form-label">Endereço:</label>
                <input type="text" name="rua" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Número:</label>
                <input type="text" name="numero" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">Bairro:</label>
                <input type="text" name="bairro" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Cidade:</label>
                <input type="text" name="cidade" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Estado:</label>
                <select name="estado" class="form-control" required>
                    <option value="">Selecione</option>
                    <option value="AC">AC</option>
                    <option value="AL">AL</option>
                    <option value="AP">AP</option>
                    <option value="AM">AM</option>
                    <option value="BA">BA</option>
                    <option value="CE">CE</option>
                    <option value="DF">DF</option>
                    <option value="ES">ES</option>
                    <option value="GO">GO</option>
                    <option value="MA">MA</option>
                    <option value="MT">MT</option>
                    <option value="MS">MS</option>
                    <option value="MG">MG</option>
                    <option value="PA">PA</option>
                    <option value="PB">PB</option>
                    <option value="PR">PR</option>
                    <option value="PE">PE</option>
                    <option value="PI">PI</option>
                    <option value="RJ">RJ</option>
                    <option value="RN">RN</option>
                    <option value="RS">RS</option>
                    <option value="RO">RO</option>
                    <option value="RR">RR</option>
                    <option value="SC">SC</option>
                    <option value="SP">SP</option>
                    <option value="SE">SE</option>
                    <option value="TO">TO</option>
                </select>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Cadastrar</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
