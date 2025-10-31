<?php
require_once '../config_BD/conexaoBD.php';
require_once '../autenticacao/verificar_login.php';
verificarAcesso(['medico']);
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_GET['id_consulta'])) die("Consulta não especificada.");
$id_consulta = intval($_GET['id_consulta']);

// Buscar dados da consulta + paciente + médico
$sql = "SELECT c.*, p.nome AS paciente, p.data_nascimento, p.cpf AS paciente_cpf, p.telefone AS paciente_telefone, 
               m.nome AS medico_nome, m.crm
        FROM consulta c
        INNER JOIN paciente p ON c.fk_id_paciente = p.id_paciente
        INNER JOIN medicos m ON c.fk_id_medico = m.id_medico
        WHERE c.id_consulta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_consulta);
$stmt->execute();
$consulta = $stmt->get_result()->fetch_assoc();
if (!$consulta) die("Consulta não encontrada.");

// Carregar FPDF
require_once __DIR__ . '/../vendor/fpdf/fpdf.php';

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 8, utf8_decode('Clínica Carvalho de Oliveira'), 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 6, utf8_decode('Rua das Oliveiras, 123 - Centro - São Paulo/SP'), 0, 1, 'C');
        $this->Ln(5);
        $this->SetDrawColor(150, 150, 150);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(8);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 8, utf8_decode('Prontuário do Paciente'), 0, 1, 'C');
        $this->Ln(6);
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 11);

function fix($str) {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str ?? '');
}

function campo($pdf, $titulo, $valor) {
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(50, 7, fix($titulo), 0, 0);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 7, fix($valor), 0, 1);
}

campo($pdf, 'Nome do Paciente:', $consulta['paciente']);
campo($pdf, 'Data de Nascimento:', ($consulta['data_nascimento'] ? date('d/m/Y', strtotime($consulta['data_nascimento'])) : ''));
campo($pdf, 'CPF:', $consulta['paciente_cpf']);
campo($pdf, 'Telefone:', $consulta['paciente_telefone']);
campo($pdf, 'Médico:', $consulta['medico_nome'].' - CRM: '.$consulta['crm']);
campo($pdf, 'Data/Hora da Consulta:', date('d/m/Y H:i', strtotime($consulta['data_hora'])));
$pdf->Ln(5);

function blocoTexto($pdf, $titulo, $texto) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, fix($titulo), 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->MultiCell(0, 7, fix(trim($texto) !== '' ? $texto : '-'));
    $pdf->Ln(3);
}

blocoTexto($pdf, 'Sintomas', $consulta['sintomas']);
blocoTexto($pdf, 'Exame Físico / Observações', $consulta['observacoes']);
blocoTexto($pdf, 'Diagnóstico', $consulta['diagnostico']);
blocoTexto($pdf, 'Prescrição', $consulta['prescricao']);
blocoTexto($pdf, 'Exames Solicitados', $consulta['exames_solicitados']);

$pdf->Ln(12);

// Linha de assinatura
$lineWidth = 110;
$centerX = ($pdf->GetPageWidth() / 2);
$lineX = $centerX - ($lineWidth / 2);
$lineY = $pdf->GetY() + 10;
$pdf->SetDrawColor(90, 90, 90);
$pdf->SetLineWidth(0.4);
$pdf->Line($lineX, $lineY, $lineX + $lineWidth, $lineY);

// Fonte manuscrita simulando assinatura
$pdf->AddFont('Ballet','','Ballet-Regular-VariableFont_opsz.php');
$pdf->SetFont('Ballet','',28);
$pdf->SetTextColor(30,30,30);
$nomeMedico = fix(trim($consulta['medico_nome']));
$txtWidth = $pdf->GetStringWidth($nomeMedico);
$pdf->SetXY($centerX - ($txtWidth / 2), $lineY - 16);
$pdf->Cell($txtWidth, 10, $nomeMedico, 0, 1, 'C');

$pdf->Ln(8);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(90, 90, 90);
$datetime = date('d/m/Y H:i');
$validLine = 'Assinatura digital — '.$consulta['medico_nome'].' — CRM '.$consulta['crm'].' — Emitido em '.$datetime;
$pdf->MultiCell(0, 6, fix($validLine), 0, 'C');

$pdf->Ln(6);
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 7, fix('Observações adicionais: _________________________________________'), 0, 1);

$pdf->Output('I', 'Prontuario_consulta_'.$id_consulta.'.pdf');
exit;
?>
