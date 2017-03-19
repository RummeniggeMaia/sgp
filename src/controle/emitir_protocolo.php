<?php

use controle\Controlador;
use dao\Dao;
use modelo\Autorizacao;
use modelo\Protocolo;

require_once("$_SERVER[DOCUMENT_ROOT]/sgp/vendor/autoload.php");
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/bootstrap.php");

session_start();

if (!isset($_GET['n'])) {
    print "Protocolo inexistente.";
    return;
}
if (!isset($_SESSION['controladores'])) {
    print 'Sistema não iniciado.';
    return;
}
$ctrls = unserialize($_SESSION['controladores']);
if (!isset($ctrls[Controlador::CTRL_AUTENTICACAO])) {
    print 'Usuário não autenticado.';
    return;
}
$authCtrl = $ctrls[Controlador::CTRL_AUTENTICACAO];

if (!$authCtrl->contemAutorizacao(Autorizacao::ADMIN)) {
    print "Usuário sem permissão.";
    return;
}

$dao = new Dao($entityManager);
$aux = new Protocolo();
$aux->setNumero($_GET['n']);
$ps = $dao->pesquisar($aux, 1, 0);
if (empty($ps)) {
    print 'Não existe Protocolo com este número.';
    return;
}
$protocolo = $ps[0];

$pdf= new FPDF("P","pt","A4");

$pdf->AddPage();
$logo = "$_SERVER[DOCUMENT_ROOT]/sgp/web/imagens/sgp_logo3.png";
$pdf->SetFont('Arial', 'B', 16);
$pdf->setX($pdf->GetPageWidth() / 2 - 63.5);
$pdf->Cell(0, 14, $pdf->Image($logo), 0, 'C');
$pdf->Ln(24);
$pdf->MultiCell(0, 14, 'Sistema de Gerenciamento de Processos - SGP', 0, 'C');
$pdf->Ln(16);
$pdf->MultiCell(0, 14, utf8_decode('Protocolo de Autenticação de Processos'), 0, 'C');
$pdf->Ln(24);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(130, 14, utf8_decode('Nome do Funcionário:'));
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 14, utf8_decode($protocolo->getProcesso()->getFuncionario()->getNome()));
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(130, 14, 'Assunto do Processo:');
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 14, utf8_decode($protocolo->getProcesso()->getAssunto()->getDescricao()));
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(125, 14, 'Processo criado por:');
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 14, utf8_decode($protocolo->getProcesso()->getUsuario()->getNome()));
date_default_timezone_set("America/Sao_Paulo");
$dataHora = $protocolo->getDataHora();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(35, 14, 'Data:');
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 14, $dataHora->format('d/m/Y'));
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(35, 14, 'Hora:');
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 14, $dataHora->format('H:i:s'));
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(100, 14, utf8_decode('Nº do Protocolo:'));
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 14, $protocolo->getNumero());
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(170, 14, 'Verificador de Autenticidade:');
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 14, $protocolo->getNumero());

$pdf->Output('I', 'p_' . $protocolo->getNumero() . ".pdf", true);
