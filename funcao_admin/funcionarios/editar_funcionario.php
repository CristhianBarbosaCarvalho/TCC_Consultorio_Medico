<?php
require_once '../../autenticacao/verificar_login.php';
require_once '../../config_BD/conexaoBD.php';
require_once '../../functions/busca.php';
require_once '../../functions/update.php';
require_once '../../functions/validacoes.php';

$mensagem = '';
$mensagem_tipo = '';
$funcionario = null;

// Verifica se veio o ID e o tipo (ex: medico, recepcionista, administrador)
if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['cargo'])) {
    $id = intval($_GET['id']);
    $cargo = strtolower($_GET['cargo']);

    // Define a tabela e o campo de ID conforme o cargo
    $tabelas = [
        'administrador' => ['tabela' => 'administrador', 'campo_id' => 'id_admin'],
        'médico'        => ['tabela' => 'medicos', 'campo_id' => 'id_medico'],
        'recepcionista' => ['tabela' => 'recepcionista', 'campo_id' => 'id_recepcionista']
    ];

    if (array_key_exists($cargo, $tabelas)) {
        $tabela = $tabelas[$cargo]['tabela'];
        $campo_id = $tabelas[$cargo]['campo_id'];

        // Busca o funcionário
        $filtros = [$campo_id => $id];
        $config_campos = [$campo_id => ['operador' => '=', 'tipo' => 'i']];
        $resultado = buscarDados($conn, $tabela, $filtros, $config_campos);

        if ($resultado->num_rows === 1) {
            $funcionario = $resultado->fetch_assoc();
        } else {
            $mensagem = "Funcionário não encontrado.";
            $mensagem_tipo = "alert-danger";
        }

        // Atualiza caso o formulário seja enviado
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $nome = $_POST['nome'];
            $cpf = limparNumero($_POST['cpf']);
            $email = $_POST['email'];
            $telefone = limparNumero($_POST['telefone']);
            $senha = $_POST['senha'] ?? '';

            // Campos específicos
            $dados = [
                'nome' => $nome,
                'cpf' => $cpf,
                'email' => $email,
                'telefone' => $telefone
            ];

            // Se for médico, também edita CRM e especialidade
            if ($cargo === 'médico') {
                $crm = $_POST['crm'];
                $especialidade = $_POST['especialidade'];
                $dados['crm'] = $crm;
                $dados['especialidade'] = $especialidade;
            }

            // Se tiver senha nova, atualiza
            if (!empty($senha)) {
                $dados['senha'] = password_hash($senha, PASSWORD_BCRYPT);
            }

            // Validações
            if (!validarCPF($cpf)) {
                $mensagem = "CPF inválido.";
                $mensagem_tipo = "alert-danger";
            } elseif (!validarTelefone($telefone)) {
                $mensagem = "Telefone inválido.";
                $mensagem_tipo = "alert-danger";
            } else {
                $resultado = atualizarDados($tabela, $dados, $campo_id, $id);

                if ($resultado === true) {
                    $mensagem = "Funcionário atualizado com sucesso!";
                    $mensagem_tipo = "alert-success";
                    $funcionario = array_merge($funcionario, $dados);
                } else {
                    $mensagem = "Erro ao atualizar: $resultado";
                    $mensagem_tipo = "alert-danger";
                }
            }
        }
    } else {
        $mensagem = "Cargo inválido.";
        $mensagem_tipo = "alert-danger";
    }
} else {
    $mensagem = "Dados inválidos para edição.";
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
    <title>Editar Funcionário</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="../../assets/Js/script_formatar_editar.js"></script>
</head>
<body>

<header class="header">
    <div class="container header-content">
        <h1>Editar Funcionário</h1>
        <a href="../../views/logout.php" class="logout-btn">Sair</a>
    </div>
</header>

<nav class="navbar">
    <div class="container">
        <ul class="nav-list">
            <a href="gerenciar_funcionarios.php" class="nav-link">Voltar</a>
        </ul>
    </div>
</nav>

<div class="container" style="margin-top: 100px; max-width: 600px;">
    <?php if ($mensagem): ?>
        <p class="alert <?= $mensagem_tipo ?>"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <?php if ($funcionario): ?>
        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nome:</label>
                    <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($funcionario['nome']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">CPF:</label>
                    <input type="text" name="cpf" class="form-control" value="<?= formatarCPF($funcionario['cpf']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($funcionario['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Telefone:</label>
                    <input type="text" name="telefone" class="form-control" value="<?= formatarTelefone($funcionario['telefone']) ?>" required>
                </div>

                <?php if ($cargo === 'médico'): ?>
                    <div class="form-group">
                        <label class="form-label">CRM:</label>
                        <input type="text" name="crm" class="form-control" value="<?= htmlspecialchars($funcionario['crm']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Especialidade:</label>
                        <input type="text" name="especialidade" class="form-control" value="<?= htmlspecialchars($funcionario['especialidade']) ?>" required>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Nova Senha (opcional):</label>
                    <input type="password" name="senha" class="form-control" placeholder="Deixe em branco para não alterar">
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
