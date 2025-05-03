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
	<div class="panel-heading" id="handme"></i><i class="fa fa-search" aria-hidden="true"></i> Periode</div>
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
            <div class="form-group">
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
            <div class="form-group">
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
            <button type="submit" name="submitlaporan" class="btn btn-primary btn-block">
                <i class="fa fa-television" aria-hidden="true"></i> Tampilkan Omset Sales
            </button>
        </form>
    </div>
</div>          

<?php
if(isset($_POST['submitlaporan'])){
    $PERIODE = $_POST['periode'];
    if ($PERIODE == 'bulan') {
        $A_BULAN = $_POST['bulan'];
        $A_TAHUN = $_POST['tahun'];
    } else {
        $TANGGALAWAL = $_POST['tanggalawal'];
        $TANGGALAKHIR = $_POST['tanggalakhir'];        
    }
    ?>
    <h3>
        <?php 
        if ($PERIODE == 'bulan') {
            echo "Omset Penjualan Sales (".namaBulan($A_BULAN)." ".$A_TAHUN. ")"; 
        } else {
            echo "Omset Penjualan Sales Tanggal ".$TANGGALAWAL." S/D ".$TANGGALAKHIR;
        }
        ?>
    </h3>
    <table class="table table-bordered">
        <thead>
            <tr class="heading-table">
                <?php
                $detect = new Mobile_Detect;
                // Any mobile device (phones or tablets).
                if ( $detect->isMobile() ) {
                    ?>
                    <td>Sales</td>
                    <td align="right">Penjualan</td>
                    <td align="right">(%)</td>
                    <td align="right">Inkaso</td>
                    <td align="right">(%)</td>
                    <?php
                }else{
                    ?>
                    <td>Nama Sales</td>
                    <td align="right">Penjualan</td>
                    <td align="right">Retur</td>
                    <td align="right">Penjualan Bersih</td>
                    <td align="right">Penerimaan</td>
                    <td align="right">Target Penjualan</td>
                    <td align="right">% Penjualan</td>
                    <td align="right">Target Penerimaan</td>
                    <td align="right">% Penerimaan</td>
                    <td align="right">Lbr</td>
                    <?php
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
                $TOTAL_PENJUALAN = 0;
                $TOTAL_RETUR = 0;
                $TOTAL_PENJUALANBERSIH = 0;
                $TOTAL_PENERIMAAN = 0;
                $TOTAL_TARGET_PENJUALAN = 0;
                $TOTAL_PROSEN_PENJUALAN = 0;
                $TOTAL_TARGET_PENERIMAAN = 0;
                $TOTAL_PROSEN_PENERIMAAN = 0;
                $TOTAL_LEMBAR = 0;
                if ($PERIODE == 'bulan') {
                    if ($_SESSION['privilege']!='0'){
                        $A_SQL = mysqli_query($A_CONNECT,"SELECT namasales, kodesales, SUM(penjualan) AS penjualan, SUM(retur) AS retur, 
                                                        SUM(penjualanbersih) AS penjualanbersih, SUM(penerimaan) AS penerimaan, 
                                                        SUM(targetpenjualan) AS targetpenjualan, SUM(prosenpenjualan) AS prosenpenjualan, 
                                                        SUM(targetpenerimaan) AS targetpenerimaan, SUM(prosenpenerimaan) AS prosenpenerimaan, SUM(lembar) AS lembar
                                                        FROM omsetharian WHERE bulan = '".$A_BULAN."' AND tahun = '".$A_TAHUN."' GROUP BY namasales, kodesales");
                    }else{
                        $A_SQL = mysqli_query($A_CONNECT,"SELECT namasales, kodesales, SUM(penjualan) AS penjualan, SUM(retur) AS retur, 
                                                        SUM(penjualanbersih) AS penjualanbersih, SUM(penerimaan) AS penerimaan, 
                                                        SUM(targetpenjualan) AS targetpenjualan, SUM(prosenpenjualan) AS prosenpenjualan, 
                                                        SUM(targetpenerimaan) AS targetpenerimaan, SUM(prosenpenerimaan) AS prosenpenerimaan, SUM(lembar) AS lembar
                                                        FROM omsetharian WHERE kodesales = '".$_SESSION['kodesales']."' AND bulan = '".$A_BULAN."' AND tahun = '".$A_TAHUN."' GROUP BY namasales, kodesales");
                    }
                } else {
                    if ($_SESSION['privilege']!='0'){
                        $A_SQL = mysqli_query($A_CONNECT,"SELECT namasales, kodesales, SUM(penjualan) AS penjualan, SUM(retur) AS retur, 
                                                        SUM(penjualanbersih) AS penjualanbersih, SUM(penerimaan) AS penerimaan, 
                                                        SUM(targetpenjualan) AS targetpenjualan, SUM(prosenpenjualan) AS prosenpenjualan, 
                                                        SUM(targetpenerimaan) AS targetpenerimaan, SUM(prosenpenerimaan) AS prosenpenerimaan, SUM(lembar) AS lembar
                                                        FROM omsetharian WHERE tanggal BETWEEN '".$TANGGALAWAL."' AND '".$TANGGALAKHIR."' GROUP BY namasales, kodesales");
                    }else{
                        $A_SQL = mysqli_query($A_CONNECT,"SELECT namasales, kodesales, SUM(penjualan) AS penjualan, SUM(retur) AS retur, 
                                                        SUM(penjualanbersih) AS penjualanbersih, SUM(penerimaan) AS penerimaan, 
                                                        SUM(targetpenjualan) AS targetpenjualan, SUM(prosenpenjualan) AS prosenpenjualan, 
                                                        SUM(targetpenerimaan) AS targetpenerimaan, SUM(prosenpenerimaan) AS prosenpenerimaan, SUM(lembar) AS lembar
                                                        FROM omsetharian WHERE kodesales = '".$_SESSION['kodesales']."' AND tanggal BETWEEN '".$TANGGALAWAL."' AND '".$TANGGALAKHIR."' GROUP BY namasales, kodesales");
                    }
                }
                while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
                    $TOTAL_PENJUALAN = $TOTAL_PENJUALAN + $A_RES['penjualan'];
                    $TOTAL_RETUR = $TOTAL_RETUR + $A_RES['retur'];
                    $TOTAL_PENJUALANBERSIH = $TOTAL_PENJUALANBERSIH + $A_RES['penjualanbersih'];
                    $TOTAL_PENERIMAAN = $TOTAL_PENERIMAAN + $A_RES['penerimaan'];
                    $TOTAL_TARGET_PENJUALAN = $TOTAL_TARGET_PENJUALAN + $A_RES['targetpenjualan'];
                    $TOTAL_PROSEN_PENJUALAN = $TOTAL_PROSEN_PENJUALAN + $A_RES['prosenpenjualan'];
                    $TOTAL_TARGET_PENERIMAAN = $TOTAL_TARGET_PENERIMAAN + $A_RES['targetpenjualan'];
                    $TOTAL_PROSEN_PENERIMAAN = $TOTAL_PROSEN_PENERIMAAN + $A_RES['prosenpenjualan'];
                    $TOTAL_LEMBAR = $TOTAL_LEMBAR + $A_RES['lembar'];
                ?>
                <tr>
                    <?php
                    $detect = new Mobile_Detect;
                    // Any mobile device (phones or tablets).
                    if ( $detect->isMobile() ) {
                        ?>
                        <td><?php echo $A_RES['kodesales']; ?></td>
                        <td align="right"><?php echo number_format($A_RES['penjualanbersih']); ?></td>
                        <td align="right"><?php echo $A_RES['prosenpenjualan']; ?>%</td>
                        <td align="right"><?php echo number_format($A_RES['penerimaan']); ?></td>
                        <td align="right"><?php echo $A_RES['prosenpenerimaan']; ?>%</td>
                        <?php
                    }else{
                        ?>
                        <td><?php echo $A_RES['namasales'].' - '.$A_RES['kodesales']; ?></td>
                        <td align="right"><?php echo number_format($A_RES['penjualan']); ?></td>
                        <td align="right"><?php echo number_format($A_RES['retur']); ?></td>
                        <td align="right"><?php echo number_format($A_RES['penjualanbersih']); ?></td>
                        <td align="right"><?php echo number_format($A_RES['penerimaan']); ?></td>
                        <td align="right"><?php echo number_format($A_RES['targetpenjualan']); ?></td>
                        <?php
                            if ( $A_RES['targetpenjualan']>0 ) { ?>
                                <td align="right"><?php echo number_format(($A_RES['penjualanbersih']/$A_RES['targetpenjualan'])*100,2); ?>%</td>
                                <?php
                            } else {
                                ?>
                                <td align="right"><?php echo number_format(0,2); ?>%</td>
                                <?php
                            }
                        ?>
                        <td align="right"><?php echo number_format($A_RES['targetpenerimaan']); ?></td>
                        <?php
                            if ( $A_RES['targetpenerimaan']>0 ) { ?>
                                <td align="right"><?php echo number_format(($A_RES['penerimaan']/$A_RES['targetpenerimaan'])*100,2); ?>%</td>
                                <?php
                            } else {
                                ?>
                                <td align="right"><?php echo number_format(0,2); ?>%</td>
                                <?php
                            }
                        ?>
                        <td align="right"><?php echo $A_RES['lembar']; ?></td>
                        <?php
                    }
                    ?>
                </tr>
                <?php
                }
            if ($_SESSION['privilege']!='0'){
            ?>
                <tr class="heading-table">
                    <?php
                    $detect = new Mobile_Detect;
                        // Any mobile device (phones or tablets).
                        if ( $detect->isMobile() ) {
                            ?>
                            <td><b>TOTAL</b></td>
                            <td align="right"><?php echo number_format($TOTAL_PENJUALANBERSIH); ?></td>
                            <?php
                             if ( $TOTAL_TARGET_PENJUALAN>0 ) { ?>
                                <td align="right"><?php echo number_format(($TOTAL_PENJUALANBERSIH / $TOTAL_TARGET_PENJUALAN)*100,2); ?> %</td>
                                <?php
                            } else {
                                ?>
                                <td align="right"><?php echo number_format(0,2); ?> %</td>
                                <?php
                            }
                            ?>
                            <td align="right"><?php echo number_format($TOTAL_PENERIMAAN); ?></td>
                            <?php
                            if ( $TOTAL_TARGET_PENERIMAAN>0 ) { ?>
                                <td align="right"><?php echo number_format(($TOTAL_PENERIMAAN / $TOTAL_TARGET_PENERIMAAN)*100,2); ?> %</td>
                                <?php
                            } else {
                                ?>
                                <td align="right"><?php echo number_format(0,2); ?> %</td>
                                <?php
                            }
                    }else{
                        ?>
                        <td>TOTAL</td>
                        <td align="right"><?php echo number_format($TOTAL_PENJUALAN); ?></td>
                        <td align="right"><?php echo number_format($TOTAL_RETUR); ?></td>
                        <td align="right"><?php echo number_format($TOTAL_PENJUALANBERSIH); ?></td>
                        <td align="right"><?php echo number_format($TOTAL_PENERIMAAN); ?></td>
                        <td align="right"><?php echo number_format($TOTAL_TARGET_PENJUALAN); ?></td>
                        <?php
                        if ( $TOTAL_TARGET_PENJUALAN>0 ) { ?>
                            <td align="right"><?php echo number_format(($TOTAL_PENJUALANBERSIH / $TOTAL_TARGET_PENJUALAN)*100,2); ?> %</td>
                            <?php
                        } else {
                            ?>
                            <td align="right"><?php echo number_format(0,2); ?> %</td>
                            <?php
                        }
                        ?>
                        <td align="right"><?php echo number_format($TOTAL_TARGET_PENERIMAAN); ?></td>
                        <?php
                        if ( $TOTAL_TARGET_PENERIMAAN>0 ) { ?>
                            <td align="right"><?php echo number_format(($TOTAL_PENERIMAAN / $TOTAL_TARGET_PENERIMAAN)*100,2); ?> %</td>
                            <?php
                        } else {
                            ?>
                            <td align="right"><?php echo number_format(0,2); ?> %</td>
                            <?php
                        }
                        ?>
                        <td align="right"><?php echo number_format($TOTAL_LEMBAR); ?></td>
                        <?php
                    }
                    ?>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
    if ($PERIODE == 'bulan') {
        echo "<script> document.getElementById('bulan').value = ".$A_BULAN."; </script>";
        echo "<script> document.getElementById('tahun').value = ".$A_TAHUN."; </script>";
    } else {
        echo "<script> document.getElementById('periode').value = ".$PERIODE."; </script>";
    }
}else{
    $A_BULAN_TODAY = date('m');
    $A_TAHUN_TODAY = date('Y');
    ?>
    <h4>
    <?php echo "Omset Penjualan Sales (".namaBulan($A_BULAN_TODAY)." ".$A_TAHUN_TODAY. ")" ?>
    </h4>
    <table class="table table-bordered">
        <thead>
            <tr class="heading-table">
                <?php
                $detect = new Mobile_Detect;
                 // Any mobile device (phones or tablets).
                if ( $detect->isMobile() ) {
                    ?>
                    <td>Sales</td>
                    <td align="right">Penjualan</td>
                    <td align="right">(%)</td>
                    <td align="right">Inkaso</td>
                    <td align="right">(%)</td>
                    <?php
                }else{
                    ?>
                    <td>Nama Sales</td>
                    <td align="right">Penjualan</td>
                    <td align="right">Retur</td>
                    <td align="right">Penjualan Bersih</td>
                    <td align="right">Penerimaan</td>
                    <td align="right">Target Penjualan</td>
                    <td align="right">% Penjualan</td>
                    <td align="right">Target Penerimaan</td>
                    <td align="right">% Penerimaan</td>
                    <td align="right">Lbr</td>
                    <?php
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
                $TOTAL_PENJUALAN = 0;
                $TOTAL_RETUR = 0;
                $TOTAL_PENJUALANBERSIH = 0;
                $TOTAL_PENERIMAAN = 0;
                $TOTAL_TARGET_PENJUALAN = 0;
                $TOTAL_PROSEN_PENJUALAN = 0;
                $TOTAL_TARGET_PENERIMAAN = 0;
                $TOTAL_PROSEN_PENERIMAAN = 0;
                $TOTAL_LEMBAR = 0;
                if ($_SESSION['privilege']!='0'){
                    $A_SQL = mysqli_query($A_CONNECT,"SELECT namasales, kodesales, SUM(penjualan) AS penjualan, SUM(retur) AS retur, 
                                                      SUM(penjualanbersih) AS penjualanbersih, SUM(penerimaan) AS penerimaan, 
                                                      SUM(targetpenjualan) AS targetpenjualan, SUM(prosenpenjualan) AS prosenpenjualan, 
                                                      SUM(targetpenerimaan) AS targetpenerimaan, SUM(prosenpenerimaan) AS prosenpenerimaan, SUM(lembar) AS lembar
                                                      FROM omsetharian WHERE bulan = '".$A_BULAN_TODAY."' AND tahun = '".$A_TAHUN_TODAY."' GROUP BY namasales, kodesales");
                }else{
                    $A_SQL = mysqli_query($A_CONNECT,"SELECT namasales, kodesales, SUM(penjualan) AS penjualan, SUM(retur) AS retur, 
                                                      SUM(penjualanbersih) AS penjualanbersih, SUM(penerimaan) AS penerimaan, 
                                                      SUM(targetpenjualan) AS targetpenjualan, SUM(prosenpenjualan) AS prosenpenjualan, 
                                                      SUM(targetpenerimaan) AS targetpenerimaan, SUM(prosenpenerimaan) AS prosenpenerimaan, SUM(lembar) AS lembar
                                                      FROM omsetharian WHERE kodesales = '".$_SESSION['kodesales']."' AND bulan = '".$A_BULAN_TODAY."' AND tahun = '".$A_TAHUN_TODAY."' GROUP BY namasales, kodesales");
                }
                while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
                    $TOTAL_PENJUALAN = $TOTAL_PENJUALAN + $A_RES['penjualan'];
                    $TOTAL_RETUR = $TOTAL_RETUR + $A_RES['retur'];
                    $TOTAL_PENJUALANBERSIH = $TOTAL_PENJUALANBERSIH + $A_RES['penjualanbersih'];
                    $TOTAL_PENERIMAAN = $TOTAL_PENERIMAAN + $A_RES['penerimaan'];
                    $TOTAL_TARGET_PENJUALAN = $TOTAL_TARGET_PENJUALAN + $A_RES['targetpenjualan'];
                    $TOTAL_PROSEN_PENJUALAN = $TOTAL_PROSEN_PENJUALAN + $A_RES['prosenpenjualan'];
                    $TOTAL_TARGET_PENERIMAAN = $TOTAL_TARGET_PENERIMAAN + $A_RES['targetpenerimaan'];
                    $TOTAL_PROSEN_PENERIMAAN = $TOTAL_PROSEN_PENERIMAAN + $A_RES['prosenpenerimaan'];
                    $TOTAL_LEMBAR = $TOTAL_LEMBAR + $A_RES['lembar'];
                ?>
                <tr>
                    <?php
                    $detect = new Mobile_Detect;
                     // Any mobile device (phones or tablets).
                    if ( $detect->isMobile() ) {
                        ?>
                        <td><?php echo $A_RES['kodesales']; ?></td>
                        <td align="right"><?php echo number_format($A_RES['penjualanbersih']); ?></td>
                        <?php
                            if ( $A_RES['targetpenjualan']>0 ) { ?>
                                <td align="right"><?php echo number_format(($A_RES['penjualanbersih']/$A_RES['targetpenjualan'])*100,2); ?>%</td>
                                <?php
                            } else {
                                ?>
                                <td align="right"><?php echo number_format(0,2); ?>%</td>
                                <?php
                            }
                        ?>
                        <td align="right"><?php echo number_format($A_RES['penerimaan']); ?></td>
                        <?php
                            if ( $A_RES['targetpenerimaan']>0 ) { ?>
                                <td align="right"><?php echo number_format(($A_RES['penerimaan']/$A_RES['targetpenerimaan'])*100,2); ?>%</td>
                                <?php
                            } else {
                                ?>
                                <td align="right"><?php echo number_format(0,2); ?>%</td>
                                <?php
                            }
                        ?>
                        <?php
                    }else{
                        ?>
                        <td><?php echo $A_RES['namasales'].' - '.$A_RES['kodesales']; ?></td>
                        <td align="right"><?php echo number_format($A_RES['penjualan']); ?></td>
                        <td align="right"><?php echo number_format($A_RES['retur']); ?></td>
                        <td align="right"><?php echo number_format($A_RES['penjualanbersih']); ?></td>
                        <td align="right"><?php echo number_format($A_RES['penerimaan']); ?></td>
                        <td align="right"><?php echo number_format($A_RES['targetpenjualan']); ?></td>
                        <?php
                            if ( $A_RES['targetpenjualan']>0 ) { ?>
                                <td align="right"><?php echo number_format(($A_RES['penjualanbersih']/$A_RES['targetpenjualan'])*100,2); ?>%</td>
                                <?php
                            } else {
                                ?>
                                <td align="right"><?php echo number_format(0,2); ?>%</td>
                                <?php
                            }
                        ?>
                        <td align="right"><?php echo number_format($A_RES['targetpenerimaan']); ?></td>
                        <?php
                            if ( $A_RES['targetpenerimaan']>0 ) { ?>
                                <td align="right"><?php echo number_format(($A_RES['penerimaan']/$A_RES['targetpenerimaan'])*100,2); ?>%</td>
                                <?php
                            } else {
                                ?>
                                <td align="right"><?php echo number_format(0,2); ?>%</td>
                                <?php
                            }
                        ?>
                        <td align="right"><?php echo $A_RES['lembar']; ?></td>
                        <?php
                    }
                    ?>
                </tr>
                <?php
                }
            if ($_SESSION['privilege']!='0'){
            ?>
                <tr class="heading-table">
                    <?php
                    $detect = new Mobile_Detect;
                        // Any mobile device (phones or tablets).
                    if ( $detect->isMobile() ) {
                        ?>
                        <td><b>TOTAL</b></td>
                        <td align="right"><?php echo number_format($TOTAL_PENJUALANBERSIH); ?></td>
                        <?php
                         if ( $TOTAL_TARGET_PENJUALAN>0 ) { ?>
                            <td align="right"><?php echo number_format(($TOTAL_PENJUALANBERSIH / $TOTAL_TARGET_PENJUALAN)*100,2); ?> %</td>
                            <?php
                        } else {
                            ?>
                            <td align="right"><?php echo number_format(0,2); ?> %</td>
                            <?php
                        }
                        ?>
                        <td align="right"><?php echo number_format($TOTAL_PENERIMAAN); ?></td>
                        <?php
                        if ( $TOTAL_TARGET_PENERIMAAN>0 ) { ?>
                            <td align="right"><?php echo number_format(($TOTAL_PENERIMAAN / $TOTAL_TARGET_PENERIMAAN)*100,2); ?> %</td>
                            <?php
                        } else {
                            ?>
                            <td align="right"><?php echo number_format(0,2); ?> %</td>
                            <?php
                        }
                    }else{
                        ?>
                        <td>TOTAL</td>
                        <td align="right"><?php echo number_format($TOTAL_PENJUALAN); ?></td>
                        <td align="right"><?php echo number_format($TOTAL_RETUR); ?></td>
                        <td align="right"><?php echo number_format($TOTAL_PENJUALANBERSIH); ?></td>
                        <td align="right"><?php echo number_format($TOTAL_PENERIMAAN); ?></td>
                        <td align="right"><?php echo number_format($TOTAL_TARGET_PENJUALAN); ?></td>
                        <?php
                        if ( $TOTAL_TARGET_PENJUALAN>0 ) { ?>
                            <td align="right"><?php echo number_format(($TOTAL_PENJUALANBERSIH / $TOTAL_TARGET_PENJUALAN)*100,2); ?> %</td>
                            <?php
                        } else {
                            ?>
                            <td align="right"><?php echo number_format(0,2); ?> %</td>
                            <?php
                        }
                        ?>
                        <td align="right"><?php echo number_format($TOTAL_TARGET_PENERIMAAN); ?></td>
                        <?php
                        if ( $TOTAL_TARGET_PENERIMAAN>0 ) { ?>
                            <td align="right"><?php echo number_format(($TOTAL_PENERIMAAN / $TOTAL_TARGET_PENERIMAAN)*100,2); ?> %</td>
                            <?php
                        } else {
                            ?>
                            <td align="right"><?php echo number_format(0,2); ?> %</td>
                            <?php
                        }
                        ?>
                        <td align="right"><?php echo number_format($TOTAL_LEMBAR); ?></td>
                        <?php
                    }
                    ?>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
    echo "<script> document.getElementById('bulan').value = ".$A_BULAN_TODAY."; </script>";
    echo "<script> document.getElementById('tahun').value = ".$A_TAHUN_TODAY."; </script>";

}

function isMobileDevice(){
    $aMobileUA = array(
        '/iphone/i' => 'iPhone', 
        '/ipod/i' => 'iPod', 
        '/ipad/i' => 'iPad', 
        '/android/i' => 'Android', 
        '/blackberry/i' => 'BlackBerry', 
        '/webos/i' => 'Mobile'
    );

    //Return true if Mobile User Agent is detected
    foreach($aMobileUA as $sMobileKey => $sMobileOS){
        if(preg_match($sMobileKey, $_SERVER['HTTP_USER_AGENT'])){
            return true;
        }
    }
    //Otherwise return false..  
    return false;
}

?>
