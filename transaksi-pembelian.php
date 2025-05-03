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
            echo "Transaksi Pembelian Bulan (".namaBulan($A_BULAN)." ".$A_TAHUN. ")"; 
        } else {
            echo "Transaksi Pembelian Tanggal ".$TANGGALAWAL." S/D ".$TANGGALAKHIR;
        }
        ?>
    </h3>
    <table class="table table-bordered">
    <thead>
            <tr class="heading-table">
                <td>No.Faktur</td>
                <td>Tanggal</td>
                <td>JT.Tempo</td>
                <td>No.Ref</td>
                <td>Tgl.Ref</td>
                <td>Customer</td>
                <td align="right">Nilai pembelian</td>
                <td align="right">Saldo</td>
            </tr>
        </thead>
        <tbody>
            <?php
                $TOTAL_pembelian = 0;
                $TOTAL_SALDO = 0;
 
                if ($PERIODE == 'bulan') {
                    $A_SQL = mysqli_query($A_CONNECT,"SELECT * FROM pembelian 
                    WHERE MONTH(tanggal) = '".$A_BULAN."' AND YEAR(tanggal) = '".$A_TAHUN."' ORDER BY nopembelian");
                } else {
                    $A_SQL = mysqli_query($A_CONNECT,"SELECT * FROM pembelian 
                    WHERE tanggal BETWEEN '".$TANGGALAWAL."' AND '".$TANGGALAKHIR."' ORDER BY nopembelian");
                }
                while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
                    $TOTAL_pembelian = $TOTAL_pembelian + $A_RES['nilaipembelian'];
                    $TOTAL_SALDO = $TOTAL_SALDO + $A_RES['saldopembelian'];
                ?>
                <tr>
                    <td><?php echo $A_RES['nopembelian']; ?></td>
                    <td><?php echo $A_RES['tanggal']; ?></td>
                    <td><?php echo $A_RES['jatuhtempo']; ?></td>
                    <td><?php echo $A_RES['noreferensi']; ?></td>
                    <td><?php echo $A_RES['tanggalreferensi']; ?></td>
                    <td><?php echo $A_RES['namasupplier']; ?></td>
                    <td align="right"><?php echo number_format($A_RES['nilaipembelian']); ?></td>
                    <td align="right"><?php echo number_format($A_RES['saldopembelian']); ?></td>
                </tr>
                <?php
                }
                ?>
        </tbody>
        <tfoot>
            <tr class="heading-table">
                <td>TOTAL</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td align="right"><?php echo number_format($TOTAL_pembelian); ?></td>
                <td align="right"><?php echo number_format($TOTAL_SALDO); ?></td>
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
}else{
    $A_BULAN_TODAY = date('m');
    $A_TAHUN_TODAY = date('Y');
    ?>
    <h4>
    <?php echo "Transaksi Pembelian Bulan (".namaBulan($A_BULAN_TODAY)." ".$A_TAHUN_TODAY. ")" ?>
    </h4>
    <table class="table table-bordered">
        <thead>
            <tr class="heading-table">
                <td>No.Faktur</td>
                <td>Tanggal</td>
                <td>JT.Tempo</td>
                <td>No.Ref</td>
                <td>Tgl.Ref</td>
                <td>Customer</td>
                <td align="right">Nilai pembelian</td>
                <td align="right">Saldo</td>
            </tr>
        </thead>
        <tbody>
            <?php
            $TOTAL_pembelian = 0;
            $TOTAL_SALDO = 0;
            $A_SQL = mysqli_query($A_CONNECT,"SELECT * FROM pembelian 
                                  WHERE MONTH(tanggal) = '".$A_BULAN_TODAY."' AND YEAR(tanggal) = '".$A_TAHUN_TODAY."' ORDER BY nopembelian");

            while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
                $TOTAL_pembelian = $TOTAL_pembelian + $A_RES['nilaipembelian'];
                $TOTAL_SALDO = $TOTAL_SALDO + $A_RES['saldopembelian'];
            ?>
            <tr>
                <td><?php echo $A_RES['nopembelian']; ?></td>
                <td><?php echo $A_RES['tanggal']; ?></td>
                <td><?php echo $A_RES['jatuhtempo']; ?></td>
                <td><?php echo $A_RES['noreferensi']; ?></td>
                <td><?php echo $A_RES['tanggalreferensi']; ?></td>
                <td><?php echo $A_RES['namasupplier']; ?></td>
                <td align="right"><?php echo number_format($A_RES['nilaipembelian']); ?></td>
                <td align="right"><?php echo number_format($A_RES['saldopembelian']); ?></td>
            </tr>
            <?php
            }
            ?>
        </tbody>
        <tfoot>
            <tr class="heading-table">
                <td>TOTAL</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td align="right"><?php echo number_format($TOTAL_pembelian); ?></td>
                <td align="right"><?php echo number_format($TOTAL_SALDO); ?></td>
            </tr>
        </tfoot>
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
