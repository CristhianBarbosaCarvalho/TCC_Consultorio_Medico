<?php
require_once '../../autenticacao/verificar_login.php';
require_once '../../config_BD/conexaoBD.php';
require_once '../../functions/busca.php';
require_once '../../functions/update.php';
require_once '../../functions/validacoes.php';

$mensagem = '';
$mensagem_tipo = '';
$paciente = null;

if (isset($_GET['id_paciente']) && is_numeric($_GET['id_paciente'])) {
    $id = intval($_GET['id_paciente']);

    // Usando a função genérica de busca para obter os dados do paciente
    $filtros = ['id_paciente' => $id];
    $config_campos = [
        'id_paciente' => ['operador' => '=', 'tipo' => 'i']
    ];
    $resultado = buscarDados($conn, 'paciente', $filtros, $config_campos);

    if ($resultado->num_rows === 1) {
        $paciente = $resultado->fetch_assoc();
    } else {
        $mensagem = "Paciente não encontrado.";
        $mensagem_tipo = "alert-danger";
    }

    // Atualizar dados se o formulário for enviado
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nome = $_POST['nome'];
        $data_nascimento = $_POST['data_nascimento'];
        $cpf = limparNumero($_POST['cpf']);
        $email = $_POST['email'];
        $telefone = limparNumero($_POST['telefone']);
        $endereco = $_POST['endereco'];

        // Validações
        if (!validarCPF($cpf)) {
            $mensagem = "CPF inválido.";
            $mensagem_tipo = "alert-danger";
        } elseif (!validarTelefone($telefone)) {
            $mensagem = "Telefone inválido.";
            $mensagem_tipo = "alert-danger";
        } elseif (($res = validarDataNascimento($data_nascimento)) !== true) {
            $mensagem = $res;
            $mensagem_tipo = "alert-danger";
        } else {
            // Dados para atualização
            $dados = [
                'nome' => $nome,
                'data_nascimento' => $data_nascimento,
                'cpf' => $cpf,
                'email' => $email,
                'telefone' => $telefone,
                'endereco' => $endereco
            ];

            $resultado = atualizarDados('paciente', $dados, 'id_paciente', $id);

            if ($resultado === true) {
                $mensagem = "Paciente atualizado com sucesso!";
                $mensagem_tipo = "alert-success";
                $paciente = $dados; // Atualiza os dados no formulário
            } else {
                $mensagem = "Erro ao atualizar: $resultado";
                $mensagem_tipo = "alert-danger";
            }
        }
    }
} else {
    $mensagem = "ID de paciente inválido.";
    $mensagem_tipo = "alert-danger";
}

function formatarCPF($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf); // remove tudo que não for número
    if (strlen($cpf) === 11) {
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }
    return $cpf; // retorna o valor original se estiver incompleto
}

function formatarTelefone($telefone) {
    $telefone = preg_replace('/\D/', '', $telefone); // remove tudo que não for número
    if (strlen($telefone) === 10) {
        // formato (XX) XXXX-XXXX
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6);
    } elseif (strlen($telefone) === 11) {
        // formato (XX) XXXXX-XXXX
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
    }
    return $telefone; // retorna o valor original se estiver incompleto
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Editar Paciente</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<script src="../../assets/Js/script_formatar_editar.js"></script>
<body>

<header class="header">
    <div class="container header-content">
        <h1>Editar Paciente</h1>
        <a href="../../views/logout.php" class="logout-btn">Sair</a>
    </div>
</header>

<nav class="navbar">
    <div class="container">
        <ul class="nav-list">
            <a href="pacientes_cadastrados.php" class="nav-link">Voltar</a>
        </ul>
    </div>
</nav>

<div class="container" style="margin-top: 100px; max-width: 600px;">
    <?php if ($mensagem): ?>
        <p class="alert <?= $mensagem_tipo ?>"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <?php if ($paciente): ?>
        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nome:</label>
                    <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($paciente['nome']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Data de Nascimento:</label>
                    <input type="date" name="data_nascimento" class="form-control" value="<?= htmlspecialchars($paciente['data_nascimento']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">CPF:</label>
                    <input type="text" name="cpf" class="form-control" value="<?= formatarCPF($paciente['cpf']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($paciente['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Telefone:</label>
                    <input type="text" name="telefone" class="form-control" value="<?= formatarTelefone($paciente['telefone']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Endereço:</label>
                    <input type="text" name="endereco" class="form-control" value="<?= htmlspecialchars($paciente['endereco']) ?>" required>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div> 
</body>
</html>
