<?php
require_once '../../autenticacao/verificar_login.php';
require_once '../../config_BD/conexaoBD.php';
require_once '../../functions/busca.php';
 
function calcularIdade($data_nascimento) {
    $data = new DateTime($data_nascimento);
    $hoje = new DateTime();
    return $hoje->diff($data)->y;
}

// Coleta os filtros da URL
$filtros = [
    'nome' => $_GET['nome'] ?? '',
    'idade' => $_GET['idade'] ?? '',
    'cpf' => $_GET['cpf'] ?? '',
    'endereco' => $_GET['endereco'] ?? ''
];

// Configura os campos e seus tipos/operadores
$config_campos = [
    'nome' => ['tipo' => 's', 'operador' => 'like'],
    'idade' => ['tipo' => 'i', 'operador' => 'idade'],
    'cpf' => ['tipo' => 's', 'operador' => 'like'],
    'endereco' => ['tipo' => 's', 'operador' => 'like'],
];

// Chama a função genérica
$result = buscarDados($conn, 'paciente', $filtros, $config_campos);
?>

<!DOCTYPE html>
 
<html>

<head>

    <title>Pacientes Cadastrados</title>

    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .btn-edit {
            background-color:rgb(88, 91, 102);
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
        }
        
    </style>

</head>

<body> 

    <header class="header">
        <div class="container header-content">
            <h1>Pacientes Cadastrados</h1>
            <a href="../../autenticacao/logout.php" class="logout-btn">Sair</a>
        </div>
    </header>

    <nav class="navbar">
        <div class="container">
            <ul class="nav-list">
                <a href="../../dashboard_users/recepcao.php" class="nav-link">Voltar</a>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 40px;">
        <div class="card" id="filtros-container" style="padding: 10px; text-align: center;">
            <button onclick="toggleFiltros()" id="toggleBtn" class="btn btn-secondary" style="display: block; margin: 0 auto;">
                 Mostrar Filtros
            </button>

            <div id="filtros" style="display: none; margin-top: 20px; text-align: left;">
                <form method="GET" action="">

                    <div class="form-group">
                        <label class="form-label">Nome:</label>
                        <input type="text" name="nome" value="<?= htmlspecialchars($nome) ?>" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Idade:</label>
                        <input type="number" name="idade" value="<?= htmlspecialchars($idade) ?>" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">CPF:</label>
                        <input type="text" name="cpf" value="<?= htmlspecialchars($cpf) ?>" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Endereço:</label>
                        <input type="text" name="endereco" value="<?= htmlspecialchars($endereco) ?>" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary" style="display: block; margin: 20px auto;">
                        Filtrar
                    </button>

                </form>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">

        <h2 class="form-title">Resultados</h2>

        <?php if ($result->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Idade</th>
                        <th>Data Nascimento</th> 
                        <th>CPF</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Endereço</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nome']) ?></td>
                            <td><?= calcularIdade($row['data_nascimento']) ?> anos</td>
                            <td><?= date('d/m/Y', strtotime($row['data_nascimento'])) ?></td>
                            <!-- Exibe o CPF formatado -->
                            <td><?= htmlspecialchars($row['cpf']) ?></td> <!-- Exibe o CPF diretamente -->
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <!-- Exibe o Telefone formatado -->
                            <td><?= htmlspecialchars($row['telefone']) ?></td> <!-- Exibe o telefone diretamente -->
                            <td><?= htmlspecialchars($row['endereco']) ?></td>
                            <td>
                               <button class="btn-sm btn-edit"
                                    onclick="editarRegistro('../../funcao_recepcao/pacientes/editar_paciente.php?id_paciente=<?= $row['id_paciente'] ?>')">
                                    Editar
                                </button>

                                <button class="btn-sm btn-delete"
                                    onclick="excluirRegistro('../../funcao_recepcao/pacientes/excluir_paciente.php?id_paciente=<?= $row['id_paciente'] ?>')">
                                    Excluir
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        <?php else: ?>
            <p>Nenhum paciente encontrado.</p>
        <?php endif; ?>

    </div>

    <script src="../../assets/Js/script_listar_pacientes.js"></script>
    <script src="../../assets/Js/script_editar_excluir_generico.js"></script>

</body>

</html>
