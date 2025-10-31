<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['recepcao']);

// Buscar especialidades
$especialidades = $conn->query("SELECT id_especialidade, nome FROM especialidade ORDER BY nome");

// Mensagem de retorno (via GET)
$mensagem = '';
$mensagem_tipo = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'sucesso') {
        $mensagem = "Consulta registrada com sucesso!";
        $mensagem_tipo = "alert-success";
    } elseif ($_GET['status'] === 'erro') {
        $erro = htmlspecialchars($_GET['msg'] ?? 'Ocorreu um erro ao salvar a consulta.');
        $mensagem = "Erro: $erro";
        $mensagem_tipo = "alert-danger";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Marcar Consulta</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body {
            background-color: #f7f9fb;
            font-family: Arial, sans-serif;
        }
        .card {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin-top: 40px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-label {
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }
        .form-control, select, textarea {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        .btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2563eb;
        }
        .resultados {
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-top: 6px;
            max-height: 140px;
            overflow: auto;
            display: none;
            background: #fff;
        }
        .resultados div {
            padding: 8px;
            cursor: pointer;
        }
        .resultados div:hover {
            background: #f1f5f9;
        }
        h2, h3 {
            color: #333;
            text-align: center;
            margin-bottom: 25px;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }
    </style>
</head>
<body>

<header class="header">
    <div class="container header-content">
        <h1>Marcar Consulta</h1>
    </div>
</header>

<nav class="navbar">
    <div class="container">
        <ul class="nav-list">
            <a href="../../dashboard_users/recepcao.php" class="nav-link">Voltar</a>
        </ul>
    </div>
</nav>

<div class="container" style="max-width: 700px; margin-top: 100px;">
    <?php if (!empty($mensagem)) : ?>
        <p class="alert <?= $mensagem_tipo ?>"><?= $mensagem ?></p>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="salvar_consulta.php" id="formConsulta" class="form-content">

            <div class="form-group">
                <label for="paciente_busca" class="form-label">Paciente (Nome ou CPF)</label>
                <input type="text" id="paciente_busca" class="form-control" autocomplete="off" placeholder="Digite nome ou CPF">
                <div id="resultado_paciente" class="resultados"></div>
                <input type="hidden" name="id_paciente" id="id_paciente" required>
            </div> 

            <div class="form-group">
                <label for="especialidade" class="form-label">Especialidade</label>
                <select name="id_especialidade" id="especialidade" class="form-control" required onchange="carregarMedicos(this.value)">
                    <option value="">Selecione</option>
                    <?php while($e = $especialidades->fetch_assoc()): ?>
                        <option value="<?= $e['id_especialidade'] ?>"><?= htmlspecialchars($e['nome']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="medico" class="form-label">Médico</label>
                <select name="id_medico" id="medico" class="form-control" required onchange="carregarDatas(this.value)">
                    <option value="">Selecione a especialidade primeiro</option>
                </select>
            </div>

            <div class="form-group">
                <label for="data_consulta" class="form-label">Data da Consulta</label>
                <select name="data_consulta" id="data_consulta" class="form-control" required onchange="carregarHorarios()">
                    <option value="">Selecione o médico primeiro</option>
                </select>
            </div>

            <div class="form-group">
                <label for="hora_consulta" class="form-label">Horário</label>
                <select name="hora_consulta" id="hora_consulta" class="form-control" required>
                    <option value="">Selecione a data primeiro</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status_consulta" class="form-label">Status da Consulta</label>
                <select name="status_consulta" id="status_consulta" class="form-control" required>
                    <option value="Agendada">Agendada</option>
                    <option value="Confirmada">Confirmada</option>
                    <option value="Cancelada">Cancelada</option>
                </select>
            </div>

            <div class="form-group">
                <label for="observacoes" class="form-label">Observações</label>
                <textarea name="observacoes" id="observacoes" class="form-control" rows="3"></textarea>
            </div>

            <hr>

            <h3>Pagamento</h3>

            <div class="form-group">
                <label for="valor" class="form-label">Valor</label>
                <input type="number" name="valor" id="valor" class="form-control" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="forma_de_pagamento" class="form-label">Forma de Pagamento</label>
                <select name="forma_de_pagamento" id="forma_de_pagamento" class="form-control" required>
                    <option value="">Selecione</option>
                    <option value="PIX">PIX</option>
                    <option value="Crédito">Crédito</option>
                    <option value="Débito">Débito</option>
                    <option value="Dinheiro">Dinheiro</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status_pagamento" class="form-label">Status do Pagamento</label>
                <select name="status_pagamento" id="status_pagamento" class="form-control" required>
                    <option value="Pago">Pago</option>
                    <option value="Pendente">Pendente</option>
                </select>
            </div>

            <div style="text-align:center; margin-top:25px;">
                <button type="submit" class="btn">Marcar Consulta</button>
            </div>

        </form>
    </div>
</div>

<script>
// Autocomplete de paciente
let debounceTimer;
document.getElementById('paciente_busca').addEventListener('input', function(){
    clearTimeout(debounceTimer);
    const q = this.value.trim();
    const resultadoDiv = document.getElementById('resultado_paciente');
    if (q.length < 2) { resultadoDiv.style.display = 'none'; resultadoDiv.innerHTML = ''; return; }

    debounceTimer = setTimeout(() => {
        fetch(`buscar_paciente.php?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => {
            resultadoDiv.innerHTML = '';
            if (!data.length) { resultadoDiv.style.display = 'none'; return; }
            data.forEach(p => {
                const el = document.createElement('div');
                el.textContent = `${p.nome} — CPF: ${p.cpf}`;
                el.onclick = () => {
                    document.getElementById('id_paciente').value = p.id_paciente;
                    document.getElementById('paciente_busca').value = p.nome;
                    resultadoDiv.innerHTML = '';
                    resultadoDiv.style.display = 'none';
                };
                resultadoDiv.appendChild(el);
            });
            resultadoDiv.style.display = 'block';
        });
    }, 250);
});

// Carregar médicos pela especialidade
function carregarMedicos(id_especialidade){
    const medicoSelect = document.getElementById('medico');
    medicoSelect.innerHTML = '<option value="">Carregando...</option>';
    fetch(`buscar_medicos.php?id_especialidade=${encodeURIComponent(id_especialidade)}`)
    .then(r => r.json())
    .then(data => {
        medicoSelect.innerHTML = '<option value="">Selecione</option>';
        data.forEach(m => {
            const op = document.createElement('option');
            op.value = m.id_medico;
            op.textContent = m.nome;
            medicoSelect.appendChild(op);
        });
        document.getElementById('data_consulta').innerHTML = '<option value="">Selecione o médico primeiro</option>';
        document.getElementById('hora_consulta').innerHTML = '<option value="">Selecione a data primeiro</option>';
    });
}

// Carregar datas
function carregarDatas(id_medico){
    const dataSelect = document.getElementById('data_consulta');
    dataSelect.innerHTML = '<option value="">Carregando...</option>';
    fetch(`buscar_datas.php?id_medico=${encodeURIComponent(id_medico)}`)
    .then(r => r.json())
    .then(dates => {
        dataSelect.innerHTML = '<option value="">Selecione</option>';
        if (!dates.length) {
            dataSelect.innerHTML = '<option value="">Nenhuma data disponível</option>';
            return;
        }
        dates.forEach(d => {
            const partes = d.data_agenda.split('-'); 
            const dataFormatada = `${partes[2]}/${partes[1]}/${partes[0]}`;
            const op = document.createElement('option');
            op.value = d.data_agenda;
            op.textContent = dataFormatada;
            dataSelect.appendChild(op);
        });
    });
}

// Carregar horários
function carregarHorarios(){
    const idMed = document.getElementById('medico').value;
    const data = document.getElementById('data_consulta').value;
    const sel = document.getElementById('hora_consulta');
    if (!idMed || !data) return;
    sel.innerHTML = '<option value="">Carregando...</option>';
    fetch(`buscar_horarios.php?id_medico=${encodeURIComponent(idMed)}&data=${encodeURIComponent(data)}`)
    .then(r => r.json())
    .then(horas => {
        sel.innerHTML = '<option value="">Selecione</option>';
        if (!horas.length) {
            sel.innerHTML = '<option value="">Nenhum horário disponível</option>';
            return;
        }
        horas.forEach(h => {
            const op = document.createElement('option');
            op.value = h.hora;
            op.textContent = h.hora;
            sel.appendChild(op);
        });
    });
}
</script>
</body>
</html>
