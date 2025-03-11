<!DOCTYPE html>
<html lang="en">

<head>
<link rel='shortcut icon' type='image/x-icon' href='layout/indoprima.png' />
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<!-- *.css -->
<link rel='stylesheet' href='layout/bootstrap/css/bootstrap.min.css' type='text/css' media='all' />
<link rel='stylesheet' href='layout/custom.css' type='text/css' media='all' />
<link rel="stylesheet" href="layout/awesome/css/font-awesome.min.css" type='text/css' media='all' >
</head>

<body>

<?php
require_once 'config.php';
if (isset($_GET['nopenjualan'])) {
    $A_QUERY = "SELECT  d.*, t.tanggal, t.jatuhtempo, t.namacustomer, t.alamatcustomer, t.namasalesman, t.nilaipenjualan, 
                t.saldopenjualan FROM detailpenjualan d 
                INNER JOIN penjualan t ON d.nopenjualan = t.nopenjualan
                WHERE d.nopenjualan = '" .$_GET['nopenjualan']. "' ORDER BY d.nourut";
  }
?>
<div class="container">
    <div class="row">
        <div class="col-sm-12">
                    
            <h3>Faktur Penjualan
            <a style="float: right" href="javascript:window.open('','_self').close();"><button>Tutup</button></a></h3>
        </h3>
            <?php
            $A_SQL = mysqli_query($A_CONNECT,$A_QUERY);
            $A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC);
            if(empty($A_RES)){
                echo "<h3>Data Detail Barang Faktur Tidak Ada!</h3>";
                exit();
            }
            $A_NILAIPENJUALAN = $A_RES['nilaipenjualan'] ;
            ?>
            <table class="table table-bordered">
                <thead>
                    <tr class="heading-table">
                        <td>No. Faktur</td>
                        <td><?php echo $A_RES['nopenjualan'] ?></td>
                    </tr>
                    <tr class="heading-table">
                        <td>Tanggal Faktur</td>
                        <td><?php echo date('d-m-Y', strtotime($A_RES['tanggal'])); ?></td>
                    </tr>
                    <tr class="heading-table">
                        <td>Jatuh Tempo</td>
                        <td><?php echo date('d-m-Y', strtotime($A_RES['jatuhtempo'])); ?></td>
                    </tr>
                    <tr class="heading-table">
                        <td>Nama Customer</td>
                        <td><?php echo $A_RES['namacustomer'] ?></td>
                    </tr>
                    <tr class="heading-table">
                        <td>Alamat Customer</td>
                        <td><?php echo $A_RES['alamatcustomer'] ?></td>
                    </tr>
                    <tr class="heading-table">
                        <td>Sales</td>
                        <td><?php echo $A_RES['namasalesman'] ?></td>
                    </tr>
                </thead>
            </tabel>

            <table class="table table-bordered">
                <thead>
                    <tr class="heading-table">
                        <td>Kode</td>
                        <td>Nama Barang</td>
                        <td>Satuan</td>
                        <td align="right">Qty</td>
                        <td align="right">Harga</td>
                        <td align="right">Disc</td>
                        <td align="right">Total</td>
                    </tr>
                </thead>

                <tbody>
                    <?php
                        $A_SQL = mysqli_query($A_CONNECT,$A_QUERY);
                        while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
                        ?>
                        <tr>
                            <td><?php echo $A_RES['kodebarang']; ?></td>
                            <td><?php echo $A_RES['namabarang']; ?></td>
                            <td><?php echo $A_RES['satuan']; ?></td>
                            <td align="right"><?php echo number_format($A_RES['jumlah']); ?></td>
                            <td align="right"><?php echo number_format($A_RES['hargajual']); ?></td>
                            <td align="right"><?php echo number_format($A_RES['discount'], 2, '.', ''); ?></td>
                            <td align="right"><?php echo number_format($A_RES['totalharga']); ?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    <tr>
                    <td></td>
                    <td></td>
                    <td>TOTAL</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td align="right"><?php echo number_format($A_NILAIPENJUALAN); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

	<!-- *.js -->
	<script type='text/javascript' src='layout/jquery/jquery.min.js'></script>
	<script type='text/javascript' src='layout/bootstrap/js/bootstrap.min.js'></script>
	<script type='text/javascript' src='layout/custom.js'></script>
</body>
</html>