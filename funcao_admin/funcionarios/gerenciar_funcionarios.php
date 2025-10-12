<?php
require_once '../../autenticacao/verificar_login.php';
require_once '../../config_BD/conexaoBD.php';

// Recebe filtros do GET
$id_funcionario = $_GET['id_funcionario'] ?? '';
$cargo = $_GET['cargo'] ?? '';

// Monta condições da query conforme filtros
$whereClauses = [];
$params = [];
$types = '';

if ($id_funcionario !== '') {
    $whereClauses[] = 'id = ?';
    $params[] = $id_funcionario;
    $types .= 'i';
}

if ($cargo !== '') {
    $whereClauses[] = 'cargo LIKE ?';
    $params[] = '%' . $cargo . '%';
    $types .= 's';
}

// Cria a query SQL que une os funcionários
$sql = "
    SELECT id_admin AS id, nome, 'Administrador' AS cargo, cpf, email, telefone FROM administrador
    UNION ALL
    SELECT id_medico AS id, nome, 'Médico' AS cargo, cpf, email, telefone FROM medicos
    UNION ALL
    SELECT id_recepcionista AS id, nome, 'Recepcionista' AS cargo, cpf, email, telefone FROM recepcionista
";

// Se tiver filtros, envolver e filtrar a query externa
if (count($whereClauses) > 0) {
    $sql = "SELECT * FROM ($sql) AS funcionario WHERE " . implode(' AND ', $whereClauses);
}

// Prepara e executa a query
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Erro na preparação da query: " . $conn->error);
}

if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

function formatarCPF($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) === 11) {
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }
    return $cpf;
}

function formatarTelefone($telefone) {
    $tel = preg_replace('/\D/', '', $telefone);
    if (strlen($tel) === 11) {
        return '(' . substr($tel, 0, 2) . ') ' . substr($tel, 2, 5) . '-' . substr($tel, 7, 4);
    } elseif (strlen($tel) === 10) {
        return '(' . substr($tel, 0, 2) . ') ' . substr($tel, 2, 4) . '-' . substr($tel, 6, 4);
    }
    return $telefone;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Funcionários Cadastrados</title>
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
            background-color: rgb(88, 91, 102);
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
        <h1>Funcionários Cadastrados</h1>
        <a href="../../autenticacao/logout.php" class="logout-btn">Sair</a>
    </div>
</header>

<nav class="navbar">
    <div class="container">
        <ul class="nav-list">
            <a href="../../dashboard_users/administracao.php" class="nav-link">Voltar</a>
        </ul>
    </div>
</nav>

<div class="container" style="margin-top: 40px;">
    <div class="card" id="filtros-container" style="padding: 10px; text-align: center;">
        <button onclick="toggleFiltros()" id="toggleBtn" class="btn btn-secondary" style="margin: 0 auto;">
            Mostrar Filtros
        </button>

        <div id="filtros" style="display: none; margin-top: 20px; text-align: left;">
            <form method="GET" action="">

                <div class="form-group">
                    <label class="form-label">ID do Funcionário:</label>
                    <input type="number" name="id_funcionario" value="<?= htmlspecialchars($id_funcionario) ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Cargo:</label>
                    <input type="text" name="cargo" value="<?= htmlspecialchars($cargo) ?>" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary" style="margin: 20px auto; display: block;">
                    Filtrar
                </button>

            </form>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <h2 class="form-title">Resultados</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Cargo</th>
                    <th>CPF</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= htmlspecialchars($row['cargo']) ?></td>
                        <td><?= htmlspecialchars(formatarCPF($row['cpf'])) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars(formatarTelefone($row['telefone'])) ?></td>
                        <td>
                            <button class="btn-sm btn-edit"
                                onclick="editarRegistro('../../funcao_admin/funcionarios/editar_funcionario.php?id=<?= $row['id'] ?>&cargo=<?= urlencode(strtolower($row['cargo'])) ?>')">
                                Editar
                            </button>

                            <button class="btn-sm btn-delete"
                                onclick="excluirRegistro('../../funcao_admin/funcionarios/excluir_funcionario.php?id=<?= $row['id'] ?>&cargo=<?= urlencode(strtolower($row['cargo'])) ?>')">
                                Excluir
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhum funcionário encontrado.</p>
    <?php endif; ?>
</div>

<script>
    function toggleFiltros() {
        const filtros = document.getElementById('filtros');
        const btn = document.getElementById('toggleBtn');
        if (filtros.style.display === 'none') {
            filtros.style.display = 'block';
            btn.textContent = 'Ocultar Filtros';
        } else {
            filtros.style.display = 'none';
            btn.textContent = 'Mostrar Filtros';
        }
    }
</script>

<script src="../../assets/Js/script_listar_pacientes.js"></script>
<script src="../../assets/Js/script_editar_excluir_generico.js"></script>

</body>
</html>
