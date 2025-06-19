<?php
require('fpdf.php');
include '../includes/db-connection.php';

// Fetch approved payments with user details (ORDER BY ASC)
$query = "
    SELECT 
        s.signup_id,
        s.first_name,
        s.last_name,
        s.signup_email,
        s.mobile_number,
        p.reference_no,
        p.date_created,
        p.payment_amount
    FROM tbl_payment p
    JOIN tbl_signup_acc s ON p.signup_id = s.signup_id
    WHERE p.status = 'approved'
    ORDER BY p.date_created ASC
";
$result = mysqli_query($con, $query);

// Calculate total
$total = 0;
$rows = [];
while($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
    $total += $row['payment_amount'];
}

// PDF Generation
$pdf = new FPDF();
$pdf->AddPage();

// Header
$pdf->SetFont('Arial','B',18);
$pdf->Cell(0,10,'CineVault',0,1,'L');
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,6,'Online Movie Platform',0,1,'L');
$pdf->Cell(0,6,'',0,1,'L'); // Spacer

// Details (top right)
$pdf->SetFont('Arial','B',11);
$pdf->SetXY(140, 10);
$pdf->Cell(0,6,'Report Date: '.date('Y-m-d'),0,1,'L');
$pdf->SetXY(140, 16);
$pdf->Cell(0,6,'Report Type: Approved Payments',0,1,'L');

// Table header (adjusted widths, total 190mm for A4 minus margins)
$pdf->SetY(35);
$pdf->SetX(8); // Move table more to the left
$pdf->SetFont('Arial','B',10);
// Adjusted widths: total 194mm (A4 is 210mm wide, minus 8mm left/right margin)
$pdf->Cell(8,8,'#',1,0,'C');
$pdf->Cell(18,8,'Signup ID',1,0,'C');
$pdf->Cell(28,8,'Name',1,0,'C');
$pdf->Cell(38,8,'Email',1,0,'C');
$pdf->Cell(25,8,'Mobile',1,0,'C');
$pdf->Cell(32,8,'Reference No.',1,0,'C');
$pdf->Cell(25,8,'Date Created',1,0,'C');
$pdf->Cell(20,8,'Amount',1,0,'C');
$pdf->Ln();

// Table body
$pdf->SetFont('Arial','',8); // Slightly smaller font for better fit
$i = 1;
foreach($rows as $row) {
    $pdf->SetX(8);
    $pdf->Cell(8,8,$i++,1,0,'C');
    $pdf->Cell(18,8,$row['signup_id'],1,0,'C');
    $pdf->Cell(28,8,$row['first_name'].' '.$row['last_name'],1,0,'C');
    $pdf->Cell(38,8,$row['signup_email'],1,0,'C');
    $pdf->Cell(25,8,$row['mobile_number'],1,0,'C');
    $pdf->Cell(32,8,$row['reference_no'],1,0,'C');
    $pdf->Cell(25,8,date('Y-m-d', strtotime($row['date_created'])),1,0,'C');
    $pdf->Cell(20,8,number_format($row['payment_amount'],2),1,0,'R');
    $pdf->Ln();
}

// Subtotal/Total
$pdf->SetFont('Arial','B',10);
$pdf->SetX(8);
$pdf->Cell(174,8,'Total',1,0,'R');
$pdf->Cell(20,8,number_format($total,2),1,0,'R');

$pdf->Output('D', 'cinevault_approved_payments_report.pdf');
exit;
?>