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

    $BULAN6 = date('m', strtotime($TANGGALAWAL))-1;
    if ($BULAN6 < 0) {
        $BULAN6 = 12;
        $TAHUN6 = date('Y', strtotime($TANGGALAWAL))-1;
    } else {
        $TAHUN6 = date('Y', strtotime($TANGGALAWAL));
    }

    $BULAN1 = $BULAN6 - 5;
    if ($BULAN1 < 0) {
        $BULAN1 = 12 + $BULAN1;
        $TAHUN1 = $TAHUN6 - 1;
    } else {
        $TAHUN1 = $TAHUN6;
    }

    $BULAN2 = $BULAN1 + 1;
    if ($BULAN2 > 12) {
        $BULAN2 = 01;
        $TAHUN2 = $TAHUN1 + 1;
    } else {
        $TAHUN2 = $TAHUN1;
    }

    $BULAN3 = $BULAN2 + 1;
    if ($BULAN3 > 12) {
        $BULAN3 = 01;
        $TAHUN3 = $TAHUN2 + 1;
    } else {
        $TAHUN3 = $TAHUN2;
    }

    $BULAN4 = $BULAN3 + 1;
    if ($BULAN4 > 12) {
        $BULAN4 = 01;
        $TAHUN4 = $TAHUN3 + 1;
    } else {
        $TAHUN4 = $TAHUN3;
    }   

   $BULAN5 = $BULAN4 + 1;
    if ($BULAN5 > 12) {
        $BULAN5 = 01;
        $TAHUN5 = $TAHUN4 + 1;
    } else {
        $TAHUN5 = $TAHUN4;
    }

    $BULAN1 = sprintf('%02d', $BULAN1);
    $BULAN2 = sprintf('%02d', $BULAN2);
    $BULAN3 = sprintf('%02d', $BULAN3);
    $BULAN4 = sprintf('%02d', $BULAN4);
    $BULAN5 = sprintf('%02d', $BULAN5);
    $BULAN6 = sprintf('%02d', $BULAN6);

    $OBJBULAN1 = DateTime::createFromFormat('!m', $BULAN1);
    $NMBULAN1 = $OBJBULAN1->format('F');
    $OBJBULAN2 = DateTime::createFromFormat('!m', $BULAN2);
    $NMBULAN2 = $OBJBULAN2->format('F');
    $OBJBULAN3 = DateTime::createFromFormat('!m', $BULAN3);
    $NMBULAN3 = $OBJBULAN3->format('F');
    $OBJBULAN4 = DateTime::createFromFormat('!m', $BULAN4);
    $NMBULAN4 = $OBJBULAN4->format('F');
    $OBJBULAN5 = DateTime::createFromFormat('!m', $BULAN5);
    $NMBULAN5 = $OBJBULAN5->format('F');
    $OBJBULAN6 = DateTime::createFromFormat('!m', $BULAN6);
    $NMBULAN6 = $OBJBULAN6->format('F');

    $TGAWAL1 = $TAHUN1."-".$BULAN1."-01";
    $TGAWAL2 = $TAHUN2."-".$BULAN2."-01";
    $TGAWAL3 =  $TAHUN3."-".$BULAN3."-01";
    $TGAWAL4 =  $TAHUN4."-".$BULAN4."-01";
    $TGAWAL5 =  $TAHUN5."-".$BULAN5."-01";
    $TGAWAL6 =  $TAHUN6."-".$BULAN6."-01";
    
    $TGAKHIR1 = new DateTime($TGAWAL1);
    $TGAKHIR1->modify('last day of this month');
    $TGAKHIR1->format('Y-m-d');
    $TGAKHIR2 = new DateTime($TGAWAL2);
    $TGAKHIR2->modify('last day of this month');
    $TGAKHIR2->format('Y-m-d');
    $TGAKHIR3 = new DateTime($TGAWAL3);
    $TGAKHIR3->modify('last day of this month');
    $TGAKHIR3->format('Y-m-d');
    $TGAKHIR4 = new DateTime($TGAWAL4);
    $TGAKHIR4->modify('last day of this month');
    $TGAKHIR4->format('Y-m-d');
    $TGAKHIR5 = new DateTime($TGAWAL5);
    $TGAKHIR5->modify('last day of this month');
    $TGAKHIR5->format('Y-m-d');
    $TGAKHIR6 = new DateTime($TGAWAL6);
    $TGAKHIR6->modify('last day of this month');
    $TGAKHIR6->format('Y-m-d');

    $TGAKHIR1 = date_format($TGAKHIR1, 'Y-m-d');
    $TGAKHIR2 = date_format($TGAKHIR2, 'Y-m-d');
    $TGAKHIR3 = date_format($TGAKHIR3, 'Y-m-d');
    $TGAKHIR4 = date_format($TGAKHIR4, 'Y-m-d');
    $TGAKHIR5 = date_format($TGAKHIR5, 'Y-m-d');
    $TGAKHIR6 = date_format($TGAKHIR6, 'Y-m-d');

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
                <td align="center" rowspan="2">Nama Customer</td>
                <td align="center"><?php echo $NMBULAN1?></td>
                <td align="center"><?php echo $NMBULAN2?></td>
                <td align="center"><?php echo $NMBULAN3?></td>
                <td align="center"><?php echo $NMBULAN4?></td>
                <td align="center"><?php echo $NMBULAN5?></td>
                <td align="center"><?php echo $NMBULAN6?></td>
                <td align="center"  rowspan="2">TOTAL</td>
            </tr>
            <tr class="heading-table">
                <td align="center"><?php echo $TAHUN1?></td>
                <td align="center"><?php echo $TAHUN2?></td>
                <td align="center"><?php echo $TAHUN3?></td>
                <td align="center"><?php echo $TAHUN4?></td>
                <td align="center"><?php echo $TAHUN5?></td>
                <td align="center"><?php echo $TAHUN6?></td>
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
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND (p.tanggal BETWEEN '".$TGAWAL1."' AND '".$TGAKHIR1."')) AS bulan1,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '".$TGAWAL2."' AND '".$TGAKHIR2."') AS bulan2,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '".$TGAWAL3."' AND '".$TGAKHIR3."') AS bulan3,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '".$TGAWAL4."' AND '".$TGAKHIR4."') AS bulan4,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '".$TGAWAL5."' AND '".$TGAKHIR5."') AS bulan5,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '".$TGAWAL6."' AND '".$TGAKHIR6."') AS bulan6
                    from penjualan j 
                    WHERE j.tanggal BETWEEN '".$TGLPROSES1."' AND '".$TGLPROSES2."' AND j.kodecustomer NOT IN 
                    (SELECT kodecustomer FROM penjualan WHERE tanggal BETWEEN '".$TANGGALAWAL."' AND '".$TANGGALAKHIR."')");
                } else {
                    $A_SQL = mysqli_query($A_CONNECT,
                    "SELECT DISTINCT j.namacustomer, j.alamatcustomer, j.kodesalesman,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '".$TGAWAL1."' AND '".$TGAKHIR1."') AS bulan1,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '".$TGAWAL2."' AND '".$TGAKHIR2."') AS bulan2,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '".$TGAWAL3."' AND '".$TGAKHIR3."') AS bulan3,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '".$TGAWAL4."' AND '".$TGAKHIR4."') AS bulan4,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '".$TGAWAL5."' AND '".$TGAKHIR5."') AS bulan5,
                    (SELECT SUM(p.nilaipenjualan) from penjualan p WHERE p.kodecustomer = j.kodecustomer AND p.tanggal BETWEEN '".$TGAWAL6."' AND '".$TGAKHIR6."') AS bulan6
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
