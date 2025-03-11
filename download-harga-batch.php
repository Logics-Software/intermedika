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
$pdf->Cell(200,0,'PT. INDOPRIMA INTERMEDIKA',0,1,'C');
$pdf->Cell(200,10,'DAFTAR STOK BATCH DAN HARGA BARANG',0,0,'C');
 
$pdf->Cell(10,15,'',0,1);
$pdf->SetFont('Times','B',8);
$pdf->Cell(10,7,'NO',1,0,'C');
$pdf->Cell(75,7,'NAMA BARANG' ,1,0,'C');
$pdf->Cell(18,7,'BATCH' ,1,0,'C');
$pdf->Cell(17,7,'ED' ,1,0,'C');
$pdf->Cell(15,7,'SATUAN',1,0,'C');
$pdf->Cell(15,7,'STOK',1,0,'C');
$pdf->Cell(20,7,'HRG.JUAL',1,0,'C');
$pdf->Cell(15,7,'DISC',1,0,'C');
 
$pdf->Cell(10,7,'',0,1);
$pdf->SetFont('Times','',8);
$no=1;

while($d = mysqli_fetch_array($data)){
  $pdf->Cell(10,6, $no++,1,0,'C');
  $pdf->Cell(75,6, $d['namabarang'],1,0);
  $pdf->Cell(18,6, $d['nomorbatch'],1,0);
  $pdf->Cell(17,6, $d['expireddate'],1,0);
  $pdf->Cell(15,6, $d['satuan'],1,0);  
  $pdf->Cell(15,6, number_format($d['stokakhir']),1,0,'R');
  $pdf->Cell(20,6, number_format($d['hargajual']),1,0,'R');
  $pdf->Cell(15,6, number_format($d['discount'],2),1,1,'R');
}
 
$pdf->Output('D', 'daftar-harga-batch.pdf', true); 
?>