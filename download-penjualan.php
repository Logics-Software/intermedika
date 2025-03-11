<?php
// memanggil library FPDF
require('pdf/fpdf.php');
require_once 'config.php';
 
if (isset($_GET['querytext'])) {
  $data = mysqli_query($A_CONNECT,$_GET['querytext']);
} else {
  // Kosong
  $data = mysqli_query($A_CONNECT,"SELECT  * FROM penjualan WHERE saldopenjualan > 0  ORDER BY tanggal");
}

// intance object dan memberikan pengaturan halaman PDF
$pdf=new FPDF('P','mm','A4');
$pdf->AddPage();
 
$pdf->SetFont('Times','B',12);
$pdf->Cell(200,0,'PT. INDOPRIMA MEDIKA',0,1,'C');
$pdf->Cell(200,10,'DAFTAR TAGIHAN PIUTANG',0,0,'C');
 
$pdf->Cell(10,15,'',0,1);
$pdf->SetFont('Times','',8);
$pdf->Cell(10,7,'NO',1,0,'C');
$pdf->Cell(25,7,'NO.FAKTUR' ,1,0,'C');
$pdf->Cell(25,7,'TGL.FAKTUR',1,0,'C');
$pdf->Cell(15,7,'UMUR',1,0,'C');
$pdf->Cell(70,7,'NAMA CUSTOMER',1,0,'C');
$pdf->Cell(23,7,'NILAI FAKTUR',1,0,'C');
$pdf->Cell(23,7,'TAGIHAN',1,0,'C');
 
 
$pdf->Cell(10,7,'',0,1);
$pdf->SetFont('Times','',10);
$no=1;
$A_TOTALSALDO = 0;
$A_TOTALFAKTUR = 0;

while($d = mysqli_fetch_array($data)){
    $A_TOTALSALDO += $d['saldopenjualan'];
    $A_TOTALFAKTUR += $d['nilaipenjualan'];

    $date1 = strtotime($d['tanggal']);
    $date2 = strtotime(date('m/d/Y h:i:s a', time()));
    $diff = $date2 - $date1;
    $A_UMUR = floor($diff / (60 * 60 * 24));

    $pdf->Cell(10,6, $no++,1,0,'C');
    $pdf->Cell(25,6, $d['nopenjualan'],1,0);
    $pdf->Cell(25,6, date('d-m-Y', strtotime($d['tanggal'])),1,0, 'C');  
    $pdf->Cell(15,6, $A_UMUR,1,0, 'C');  
    $pdf->Cell(70,6, $d['namacustomer'],1,0);  
    $pdf->Cell(23,6, number_format($d['nilaipenjualan']),1,0,'R');
    $pdf->Cell(23,6, number_format($d['saldopenjualan']),1,1,'R');
}
$pdf->Cell(145,7, "TOTAL",1,0,'R');
$pdf->Cell(23,7, number_format($A_TOTALFAKTUR),1,0,'R');
$pdf->Cell(23,7, number_format($A_TOTALSALDO),1,1,'R');

$pdf->Output('D', 'daftar-tagihan-piutang.pdf', true); 
 
?>