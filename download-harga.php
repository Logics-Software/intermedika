<?php
// memanggil library FPDF
require('pdf/fpdf.php');
require_once 'config.php';
 
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
$pdf->Cell(200,10,'DAFTAR STOK DAN HARGA BARANG',0,0,'C');
 
$pdf->Cell(10,15,'',0,1);
$pdf->SetFont('Times','B',9);
$pdf->Cell(10,7,'NO',1,0,'C');
$pdf->Cell(100,7,'NAMA BARANG' ,1,0,'C');
$pdf->Cell(15,7,'SATUAN',1,0,'C');
$pdf->Cell(15,7,'STOK',1,0,'C');
$pdf->Cell(25,7,'HARGA JUAL',1,0,'C');
$pdf->Cell(15,7,'DISC',1,0,'C');
 
$pdf->Cell(10,7,'',0,1);
$pdf->SetFont('Times','',10);
$no=1;

while($d = mysqli_fetch_array($data)){
  $pdf->Cell(10,6, $no++,1,0,'C');
  $pdf->Cell(100,6, $d['namabarang'],1,0);
  $pdf->Cell(15,6, $d['satuan'],1,0);  
  $pdf->Cell(15,6, number_format($d['stokakhir']),1,0,'R');
  $pdf->Cell(25,6, number_format($d['hargajual']),1,0,'R');
  $pdf->Cell(15,6, number_format($d['discount'],2),1,1,'R');
}
 
$pdf->Output('D', 'daftar-harga.pdf', true); 
?>