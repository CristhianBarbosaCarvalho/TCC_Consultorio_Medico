<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['recepcao']);

// Buscar especialidades para popular select
$especialidades = $conn->query("SELECT id_especialidade, nome FROM especialidade ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Marcar Consulta</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .container { max-width: 780px; margin: 30px auto; background: #fff; padding: 25px; border-radius: 10px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
        label{display:block;margin-top:12px;font-weight:600;}
        input,select,textarea{width:100%;padding:8px;margin-top:6px;border:1px solid #ccc;border-radius:6px;}
        button{margin-top:18px;padding:10px 18px;background:#3b82f6;color:#fff;border:none;border-radius:8px;cursor:pointer;}
        .resultados{border:1px solid #ddd;border-radius:6px;margin-top:6px;max-height:140px;overflow:auto;}
        .resultados div{padding:8px;cursor:pointer;}
        .resultados div:hover{background:#f5f5f5;}
    </style>
</head>
<body>
<div class="container">
    <h2>Marcar Consulta</h2>

    <form method="POST" action="salvar_consulta.php" id="formConsulta">
        <!-- Paciente: busca por nome ou CPF -->
        <label for="paciente_busca">Paciente (Nome ou CPF)</label>
        <input type="text" id="paciente_busca" autocomplete="off" placeholder="Digite nome ou CPF">
        <div id="resultado_paciente" class="resultados" style="display:none;"></div>
        <input type="hidden" name="id_paciente" id="id_paciente" required>

        <!-- Especialidade -->
        <label for="especialidade">Especialidade</label>
        <select name="id_especialidade" id="especialidade" required onchange="carregarMedicos(this.value)">
            <option value="">Selecione</option>
            <?php while($e = $especialidades->fetch_assoc()): ?>
                <option value="<?= $e['id_especialidade'] ?>"><?= htmlspecialchars($e['nome']) ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Médico -->
        <label for="medico">Médico</label>
        <select name="id_medico" id="medico" required onchange="carregarDatas(this.value)">
            <option value="">Selecione a especialidade primeiro</option>
        </select>

        <!-- Datas disponíveis (apenas as data_agenda do médico) -->
        <label for="data_consulta">Data da Consulta (datas disponíveis somente)</label>
        <select name="data_consulta" id="data_consulta" required onchange="carregarHorarios()">
            <option value="">Selecione o médico primeiro</option>
        </select>

        <!-- Horários (de 1 em 1 hora, exclui já agendados) -->
        <label for="hora_consulta">Horário</label>
        <select name="hora_consulta" id="hora_consulta" required>
            <option value="">Selecione a data primeiro</option>
        </select>

        <!-- Observações e Status/Valor/Forma/Status Pagamento (mantido do original) -->
        <label for="status_consulta">Status da Consulta</label>
        <select name="status_consulta" id="status_consulta" required>
            <option value="Agendada">Agendada</option>
            <option value="Confirmada">Confirmada</option>
            <option value="Cancelada">Cancelada</option>
        </select>

        <label for="observacoes">Observações</label>
        <textarea name="observacoes" id="observacoes"></textarea>

        <hr>

        <h3>Pagamento</h3>
        <label for="valor">Valor</label>
        <input type="number" name="valor" id="valor" step="0.01" required>

        <label for="forma_de_pagamento">Forma de Pagamento</label>
        <select name="forma_de_pagamento" id="forma_de_pagamento" required>
            <option value="">Selecione</option>
            <option value="PIX">PIX</option>
            <option value="Crédito">Crédito</option>
            <option value="Débito">Débito</option>
            <option value="Dinheiro">Dinheiro</option>
        </select>

        <label for="status_pagamento">Status do Pagamento</label>
        <select name="status_pagamento" id="status_pagamento" required>
            <option value="Pago">Pago</option>
            <option value="Pendente">Pendente</option>
        </select>

        <button type="submit">Marcar Consulta</button>
    </form>
</div>

<script>
// Buscar paciente (autocomplete) — exibe resultados ao digitar
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
                el.dataset.id = p.id_paciente;
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
        // limpar datas/horarios
        document.getElementById('data_consulta').innerHTML = '<option value="">Selecione o médico primeiro</option>';
        document.getElementById('hora_consulta').innerHTML = '<option value="">Selecione a data primeiro</option>';
    });
}

// Carregar datas disponíveis (data_agenda) para o médico
function carregarDatas(id_medico){
    const dataSelect = document.getElementById('data_consulta');
    dataSelect.innerHTML = '<option value="">Carregando...</option>';
    fetch(`buscar_datas.php?id_medico=${encodeURIComponent(id_medico)}`)
    .then(r => r.json())
    .then(dates => {
        dataSelect.innerHTML = '<option value="">Selecione</option>';
        if (dates.length === 0) {
            dataSelect.innerHTML = '<option value="">Nenhuma data disponível</option>';
            document.getElementById('hora_consulta').innerHTML = '<option value="">Selecione a data primeiro</option>';
            return;
        }
        dates.forEach(d => {
            const op = document.createElement('option');
            op.value = d.data_agenda;
            op.textContent = d.data_agenda; // formato YYYY-MM-DD
            dataSelect.appendChild(op);
        });
        document.getElementById('hora_consulta').innerHTML = '<option value="">Selecione a data primeiro</option>';
    });
}

// Carregar horários 1h em 1h para o médico+data, removendo já agendados
function carregarHorarios(){
    const idMed = document.getElementById('medico').value;
    const data = document.getElementById('data_consulta').value;
    const sel = document.getElementById('hora_consulta');
    if (!idMed || !data) { sel.innerHTML = '<option value="">Selecione o médico e a data</option>'; return; }

    sel.innerHTML = '<option value="">Carregando...</option>';
    fetch(`buscar_horarios.php?id_medico=${encodeURIComponent(idMed)}&data=${encodeURIComponent(data)}`)
    .then(r => r.json())
    .then(horas => {
        sel.innerHTML = '<option value="">Selecione</option>';
        if (horas.length === 0) {
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
