<?php
require_once '../../config_BD/conexaoBD.php';
require_once '../../autenticacao/verificar_login.php';
verificarAcesso(['recepcao']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dados vindos do formulário
    $id_medico         = $_POST['id_medico']          ?? null;
    $id_paciente       = $_POST['id_paciente']        ?? null;
    $data_consulta     = $_POST['data_consulta']      ?? null;
    $hora_consulta     = $_POST['hora_consulta']      ?? null;
    $status_consulta   = $_POST['status_consulta']    ?? 'pendente'; // valor original
    $valor             = $_POST['valor']              ?? null;
    $forma_pagamento   = $_POST['forma_de_pagamento'] ?? null;
    $status_pagamento  = $_POST['status_pagamento']   ?? null;
    $observacoes       = $_POST['observacoes']        ?? '';

    // ⚠️ Verificação básica
    if (!$id_medico || !$id_paciente || !$data_consulta || !$hora_consulta) {
        die("Erro: dados do formulário incompletos.");
    }

    // 🔄 Mapeamento para o ENUM do banco (somente para tabela agendamento)
    $status_map = [
        'confirmado'  => 'pago',
        'confirmada'  => 'pago',
        'agendada'    => 'pendente',
        'pendente'    => 'pendente',
        'cancelado'   => 'cancelado',
        'cancelada'   => 'cancelado',
        'finalizado'  => 'finalizado',
        'concluido'   => 'finalizado',
        'concluída'   => 'finalizado'
    ];

    $status_normalizado = strtolower(trim($status_consulta));
    $status_agendamento = $status_map[$status_normalizado] ?? 'pendente';

    // Buscar automaticamente o id_agenda correspondente
    $sqlAgenda = "SELECT id_agenda FROM agenda 
                  WHERE fk_id_medico = '$id_medico' 
                    AND data_agenda = '$data_consulta' 
                  LIMIT 1";
    $resAgenda = $conn->query($sqlAgenda);

    if ($resAgenda && $resAgenda->num_rows > 0) {
        $rowAgenda = $resAgenda->fetch_assoc();
        $id_agenda = $rowAgenda['id_agenda'];
    } else {
        die("Erro: Nenhuma agenda encontrada para o médico e data selecionados.");
    }

    // 🟢 Inserir agendamento (usa o status mapeado)
    $sqlAgendamento = "INSERT INTO agendamento 
        (fk_id_paciente, fk_id_agenda, dia_consulta, hora_agendada, status, observacoes)
        VALUES 
        ('$id_paciente', '$id_agenda', '$data_consulta', '$hora_consulta', '$status_agendamento', '$observacoes')";

    if ($conn->query($sqlAgendamento)) {
        $id_agendamento = $conn->insert_id;

        // 🟢 Inserir consulta (usa o valor original do formulário)
        $data_hora = $data_consulta . ' ' . $hora_consulta . ':00';
        $sqlConsulta = "INSERT INTO consulta 
            (data_hora, status, observacoes, fk_id_paciente, fk_id_medico, fk_id_agenda)
            VALUES 
            ('$data_hora', '$status_consulta', '$observacoes', '$id_paciente', '$id_medico', '$id_agenda')";

        if ($conn->query($sqlConsulta)) {
            $id_consulta = $conn->insert_id;

            // 🟢 Inserir pagamento
            $sqlPagamento = "INSERT INTO pagamentos 
                (data_pagamento, valor, forma_de_pagamento, status, fk_id_consulta)
                VALUES 
                (NOW(), '$valor', '$forma_pagamento', '$status_pagamento', '$id_consulta')";

            if ($conn->query($sqlPagamento)) {
                echo "
                    <script>
                        alert('Consulta registrada com sucesso!');
                        window.location.href = '../../dashboard_users/recepcao.php';
                    </script>
                ";
            } else {
                echo 'Erro ao registrar pagamento: ' . $conn->error;
            }
        } else {
            echo 'Erro ao registrar consulta: ' . $conn->error;
        }
    } else {
        echo 'Erro ao registrar agendamento: ' . $conn->error;
    }
} else {
    echo 'Método inválido.';
}
?>
