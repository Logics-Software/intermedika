<br>
<?php
function namaBulan(int $bl){
    if ($bl == 1){
        return "Januari";
    }elseif ($bl == 2){
        return "Februari";
    }elseif ($bl == 3){
        return "Maret";
    }elseif ($bl == 4){
        return "April";
    }elseif ($bl == 5){
        return "Mei";
    }elseif ($bl == 6){
        return "Juni";
    }elseif ($bl == 7){
        return "Juli";
    }elseif ($bl == 8){
        return "Agustus";
    }elseif ($bl == 9){
        return "September";
    }elseif ($bl == 10){
        return "Oktober";
    }elseif ($bl == 11){
        return "Nopember";
    }else {
        return "Desember";
    }
}
?>
<?php echomsg(); ?>
<div class="panel panel-primary">
	<div class="panel-heading" id="handme"></i><i class="fa fa-search" aria-hidden="true"></i> Periode Transaksi</div>
	<div class="panel-body" id="hideme">
        <form name="" method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Periode Omset :</label>
                <input type="radio" id="periodeBulan" name="periode" onchange="handleChange(this);" value="bulan" checked>
                <label for="periodeBulan">Bulan</label>
                <input type="radio" id="periodeTahun" name="periode" onchange="handleChange(this);" value="tanggal">
                <label for="periodeBulan">Tanggal</label><br>
            </div>
            <script language="javascript">
                    function handleChange(src) {
                        if (src.value == "bulan") {
                            document.getElementById('bulan').disabled = false;
                            document.getElementById('tahun').disabled = false;
                            document.getElementById('tanggalawal').disabled = true;
                            document.getElementById('tanggalakhir').disabled = true;
                        } else {
                            document.getElementById('bulan').disabled = true;
                            document.getElementById('tahun').disabled = true;
                            document.getElementById('tanggalawal').disabled = false;
                            document.getElementById('tanggalakhir').disabled = false;
                        }
                    }        
            </script>
            <div class="form-group">
                <label>Tanggal : </label>
                <input type="date" class="date-today" id="tanggalawal" name="tanggalawal" disabled/>
                <label> S/D </label>
                <input type="date" class="date-today" id="tanggalakhir" name="tanggalakhir" disabled/>
            </div>
            <script language="javascript">
                document.getElementById('tanggalawal').value = "<?php echo date("Y-m-d"); ?>";
                document.getElementById('tanggalakhir').value = "<?php echo date("Y-m-d"); ?>";
            </script>
            <div style = "margin:auto;"  class="form-group-periode">
                <div style = "float:left; padding-right: 10px; width: 50%;"  class="form-group">
                    <label>Bulan</label>
                    <select name="bulan" id="bulan" class="form-control" onchange="document.getElementById('pilih_bulan').value=this.options[this.selectedIndex].text">
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                    <input type="hidden" name="nama_bulan" id="pilih_bulan" value="" />
                </div>

                <div style = "float:right; width: 50%;" class="form-group">
                    <label>Tahun</label>
                    <select name="tahun" id="tahun" class="form-control" onchange="document.getElementById('pilih_tahun').value=this.options[this.selectedIndex].text>
                        <?php
                        $A_THNAWAL = 2010;
                        for($A_I=$A_THNAWAL; $A_I<=date("Y"); $A_I++) {
                            ?>
                            <option value="<?php echo $A_I; ?>"><?php echo $A_I; ?></option>
                            <?php
                        }
                        echo "<script> document.getElementById('bulan').value = ".date("m")."; </script>";
                        echo "<script> document.getElementById('tahun').value = ".date("Y")."; </script>";
                        ?>
                    </select>
                    <input type="hidden" name="nama_tahun" id="pilih_tahun" value="" />
                </div>
            </div>
            <br/>
            <div class="form-group">
                <label>Sales</label>
                <select name="namasalesman" id="namasalesman" class="form-control">
                    <option value="">SEMUA SALES</option>
                    <?php
                        $A_SQL = mysqli_query($A_CONNECT,"SELECT DISTINCTROW namasalesman FROM penjualan ORDER BY namasalesman");
                        while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
                        ?>
                            <option value="<?php echo $A_RES['namasalesman']; ?>"><?php echo $A_RES['namasalesman']; ?></option>
                        <?php
                        }
                    ?>
                </select>
            </div>
            <button type="submit" name="submitlaporan" class="btn btn-primary btn-block">
                <i class="fa fa-television" aria-hidden="true"></i> Tampilkan Customer Non Aktif
            </button>
        </form>
    </div>
</div>          

<?php
if(isset($_POST['submitlaporan'])){
    $A_NAMASALESMAN = $_POST['namasalesman'];
    $PERIODE = $_POST['periode'];
    if ($PERIODE == 'bulan') {
        $A_BULAN = $_POST['bulan'];
        $A_TAHUN = $_POST['tahun'];

        $TANGGALAWAL = $A_TAHUN."-".$A_BULAN."-01";
        $TANGGALAKHIR = $A_TAHUN."-".$A_BULAN."-01";

        $TANGGALAWAL = date("Y-m-01", strtotime($TANGGALAWAL));
        $TANGGALAKHIR = date("Y-m-t", strtotime($TANGGALAKHIR));
    } else {
        $TANGGALAWAL = $_POST['tanggalawal'];
        $TANGGALAKHIR = $_POST['tanggalakhir'];        
    }
    $TGLPROSES1 = date('Y-m-d',(strtotime ( '-182 day' , strtotime ( $TANGGALAWAL) ) ));
    $TGLPROSES2 = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $TANGGALAWAL) ) ));
    ?>
    <h3>
        <?php 
        if ($PERIODE == 'bulan') {
            echo "Rekap Penjualan Customer Non Aktif Bulan: ".namaBulan($A_BULAN)." ".$A_TAHUN; 
        } else {
            echo "Rekap Penjualan Customer Non Aktif Tanggal: ".$TANGGALAWAL." S/D ".$TANGGALAKHIR;
        }
        ?><br/><?php
        if ($A_NAMASALESMAN==""){
            echo "SEMUA SALES";
        } else {
            echo "SALES: ".$A_NAMASALESMAN;
        }
        ?>
    </h3>
    <table class="table table-bordered">
        <thead>
            <tr class="heading-table">
                <td align="center">Nama Customer</td>
                <td align="center">Januari</td>
                <td align="center">February</td>
                <td align="center">Maret</td>
                <td align="center">April</td>
                <td align="center">Mei</td>
                <td align="center">Juni</td>
                <td align="center">Total</td>
            </tr>
        </thead>
        <tbody>
            <?php
                $TOTALCUSTOMER = 0;
                $TOTALPENJUALAN = 0;
                $TOTALBULAN1 = 0;
                $TOTALBULAN2 = 0;
                $TOTALBULAN3 = 0;
                $TOTALBULAN4 = 0;
                $TOTALBULAN5 = 0;
                $TOTALBULAN6 = 0;
                $TOTALTRANSAKSI = 0;

                if ($A_NAMASALESMAN==""){
                    $A_SQL = mysqli_query($A_CONNECT,
                    "SELECT DISTINCT j.namacustomer, j.alamatcustomer, j.kodesalesman,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '2025-01-01' AND '2025-01-31' ) AS bulan1,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '2025-02-01' AND '2025-02-28' ) AS bulan2,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '2025-03-01' AND '2025-03-31' ) AS bulan3,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '2025-04-01' AND '2025-04-30' ) AS bulan4,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '2025-05-01' AND '2025-05-30' ) AS bulan5,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '2025-06-01' AND '2025-06-30' ) AS bulan6
                    from penjualan j 
                    WHERE j.tanggal BETWEEN '".$TGLPROSES1."' AND '".$TGLPROSES2."' AND j.kodecustomer NOT IN 
                    (SELECT kodecustomer FROM penjualan WHERE tanggal BETWEEN '".$TANGGALAWAL."' AND '".$TANGGALAKHIR."')");
                } else {
                    $A_SQL = mysqli_query($A_CONNECT,
                    "SELECT DISTINCT j.namacustomer, j.alamatcustomer, j.kodesalesman,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '2025-01-01' AND '2025-01-31' ) AS bulan1,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '2025-02-01' AND '2025-02-28' ) AS bulan2,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '2025-03-01' AND '2025-03-31' ) AS bulan3,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '2025-04-01' AND '2025-04-30' ) AS bulan4,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '2025-05-01' AND '2025-05-30' ) AS bulan5,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '2025-06-01' AND '2025-06-30' ) AS bulan6
                    from penjualan j 
                    WHERE j.tanggal BETWEEN '".$TGLPROSES1."' AND '".$TGLPROSES2."' AND namasalesman = '".$A_NAMASALESMAN."' AND j.kodecustomer NOT IN 
                    (SELECT kodecustomer FROM penjualan WHERE tanggal BETWEEN '".$TANGGALAWAL."' AND '".$TANGGALAKHIR."')");
                }

                while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
                ?>
                <tr>
                    <?php
                        $TOTALCUSTOMER += 1;
                        $TOTALPENJUALAN = $A_RES['bulan1']+$A_RES['bulan2']+$A_RES['bulan3']+$A_RES['bulan4']+$A_RES['bulan5']+$A_RES['bulan6'];
                        $TOTALBULAN1 += $A_RES['bulan1'];
                        $TOTALBULAN2 += $A_RES['bulan2'];
                        $TOTALBULAN3 += $A_RES['bulan3'];
                        $TOTALBULAN4 += $A_RES['bulan4'];
                        $TOTALBULAN5 += $A_RES['bulan5'];
                        $TOTALBULAN6 += $A_RES['bulan6'];
                        $TOTALTRANSAKSI += $TOTALPENJUALAN;
                    ?>
                    <tr>
                    <td><?php echo $A_RES['namacustomer']; ?></td>
                    <td align="right"><?php echo number_format($A_RES['bulan1']); ?></td>
                    <td align="right"><?php echo number_format($A_RES['bulan2']); ?></td>
                    <td align="right"><?php echo number_format($A_RES['bulan3']); ?></td>
                    <td align="right"><?php echo number_format($A_RES['bulan4']); ?></td>
                    <td align="right"><?php echo number_format($A_RES['bulan5']); ?></td>
                    <td align="right"><?php echo number_format($A_RES['bulan6']); ?></td>
                    <td align="right"><?php echo number_format($TOTALPENJUALAN); ?></td>
                </tr>
                <?php
                }
                ?>
        </tbody>
        <tfoot>
            <tr class="heading-table">
                <td>TOTAL PENJUALAN</td>
                <td align="right"><?php echo number_format($TOTALBULAN1); ?></td>
                <td align="right"><?php echo number_format($TOTALBULAN2); ?></td>
                <td align="right"><?php echo number_format($TOTALBULAN3); ?></td>
                <td align="right"><?php echo number_format($TOTALBULAN4); ?></td>
                <td align="right"><?php echo number_format($TOTALBULAN5); ?></td>
                <td align="right"><?php echo number_format($TOTALBULAN6); ?></td>
                <td align="right"><?php echo number_format($TOTALTRANSAKSI); ?></td>
            </tr>
        </tfoot>
    </table>

    <?php
    if ($PERIODE == 'bulan') {
        echo "<script> document.getElementById('bulan').value = ".$A_BULAN."; </script>";
        echo "<script> document.getElementById('tahun').value = ".$A_TAHUN."; </script>";
    } else {
        echo "<script> document.getElementById('periode').value = ".$PERIODE."; </script>";
    }
    echo "<script> document.getElementById('namasalesman').value = '".$A_NAMASALESMAN."'; </script>";
}?>
