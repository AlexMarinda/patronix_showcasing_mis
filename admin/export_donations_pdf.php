<?php
require('fpdf.php');
include 'config.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: menu/admin/admin_login.php");
    exit();
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Donation Report',0,1,'C');
$pdf->Ln(10);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(50,10,'Date',1);
$pdf->Cell(60,10,'User',1);
$pdf->Cell(50,10,'Category',1);
$pdf->Cell(30,10,'Amount',1);
$pdf->Ln();

$pdf->SetFont('Arial','',12);

$query = "SELECT dh.date, u.fullname AS user_name, v.category, dh.amount 
          FROM donation_history dh
          JOIN talents u ON dh.user_id = u.id
          JOIN videos v ON dh.video_id = v.id
          ORDER BY dh.date DESC";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $pdf->Cell(50,10,$row['date'],1);
    $pdf->Cell(60,10,$row['user_name'],1);
    $pdf->Cell(50,10,$row['category'],1);
    $pdf->Cell(30,10,number_format($row['amount'], 2),1,0,'R');
    $pdf->Ln();
}

$pdf->Output();