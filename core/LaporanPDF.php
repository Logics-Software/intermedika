<?php
require_once __DIR__ . '/fpdf/fpdf.php';

class LaporanPDF extends FPDF {
    public $reportTitle = 'Laporan';
    public $reportSubtitle = '';
    public $printedBy = 'System';

    function Header() {
        $this->SetFont('Helvetica', 'B', 14);
        $this->Cell(0, 10, $this->reportTitle, 0, 1, 'C');
        
        if ($this->reportSubtitle) {
            $this->SetFont('Helvetica', '', 10);
            $this->MultiCell(0, 5, $this->reportSubtitle);
        }
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->Cell(0, 10, 'Dicetak oleh: ' . $this->printedBy . ' | Tanggal: ' . date('d F Y, H:i:s') . ' | Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Helper for table header
    function TableHeader($header, $widths) {
        $this->SetFont('Helvetica', 'B', 9);
        $this->SetFillColor(200, 200, 200);
        foreach ($header as $i => $col) {
            $this->Cell($widths[$i], 7, $col, 1, 0, 'C', true);
        }
        $this->Ln();
    }
}
