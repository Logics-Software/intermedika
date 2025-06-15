<?php
// memanggil library FPDF
require('pdf/fpdf.php');
require_once 'config.php';
 
$jenisharga=1;

if (isset($_GET['querytext'])) {
  $data = mysqli_query($A_CONNECT,$_GET['querytext']);
} else {
  // Kosong
  $data = mysqli_query($A_CONNECT,"SELECT  * FROM daftarharga ORDER BY namabarang");
}

// intance object dan memberikan pengaturan halaman PDF
$pdf=new FPDF('P','mm','A4');
$pdf->AddPage();
 
$pdf->SetFont('Times','B',13);
$pdf->Cell(200,0,'CV. INDOPRIMA MEDIKA',0,1,'C');
$pdf->Cell(200,10,'DAFTAR PERSEDIAAN BARANG',0,0,'C');
 
$pdf->Cell(10,15,'',0,1);
$pdf->SetFont('Times','B',8);
$pdf->Cell(10,7,'NO',1,0,'C');
$pdf->Cell(65,7,'NAMA BARANG' ,1,0,'C');
$pdf->Cell(33,7,'NAMA PABRIK' ,1,0,'C');
$pdf->Cell(15,7,'STOK',1,0,'C');
$pdf->Cell(20,7,'HPP',1,0,'C');
$pdf->Cell(25,7,'PERSEDIAAN',1,0,'C');
 
$pdf->Cell(10,7,'',0,1);
$pdf->SetFont('Times','',8);
$no=1;
$total=1;

while($d = mysqli_fetch_array($data)){
  $total+=$d['stokakhir']*$d['hpp'];
  $pdf->Cell(10,6, $no++,1,0,'C');
  $pdf->Cell(65,6, $d['namabarang'],1,0);
  $pdf->Cell(33,6, $d['namapabrik'],1,0);
  $pdf->Cell(15,6, number_format($d['stokakhir']),1,0,'R');
  $pdf->Cell(20,6, number_format($d['hpp']),1,0,'R');
  $pdf->Cell(25,6, number_format($d['stokakhir']*$d['hpp'],0),1,1,'R');
}

$pdf->Cell(10,6, ' ',1,0);
$pdf->Cell(80,6, 'TOTAL PERSEDIAAN',1,0, 'C');
$pdf->Cell(65,6, ' ',1,0);
$pdf->Cell(33,6, ' ',1,0);
$pdf->Cell(20,6, ' ',1,0);
$pdf->Cell(25,6, number_format($total,0),1,1,'R');

$pdf->Output('D', 'daftar-persediaan.pdf', true); 
?>