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

    ?>
    <h3>
        <?php 
        if ($PERIODE == 'bulan') {
            echo "Distribusi Penjualan Barang Bulan: ".namaBulan($A_BULAN)." ".$A_TAHUN; 
        } else {
            echo "Distribusi Penjualan Barang Tanggal: ".$TANGGALAWAL." S/D ".$TANGGALAKHIR;
        }
        ?>
    </h3>


    <?php
        $TOTALQTY = 0;
        $TOTALPENJUALAN = 0;
        $NAMACUSTOMER = '';
        $NAMASALESMAN = '';

        if ($A_NAMASALESMAN==""){
            $A_SQL = mysqli_query($A_CONNECT,
            "SELECT DISTINCT j.namasalesman, j.kodesalesman, j.namacustomer, j.alamatcustomer, b.namabarang, b.satuan, b.namapabrik,
            SUM(d.jumlah) AS jumlah, SUM(d.totalharga)/SUM(D.Jumlah) AS hargarata, sum(d.totalharga) AS totalharga
            from penjualan j
            inner join detailpenjualan d on j.nopenjualan = d.nopenjualan
            inner join masterbarang b on d.kodebarang = b.kodebarang
            WHERE j.tanggal BETWEEN '".$TANGGALAWAL."' AND '".$TANGGALAKHIR."' 
            GROUP BY j.namasalesman, j.kodesalesman, j.namacustomer, j.alamatcustomer, b.namabarang, b.satuan, b.namapabrik
            ORDER BY j.namasalesman, j.kodesalesman, j.namacustomer, j.alamatcustomer, b.namabarang, b.satuan, b.namapabrik");
        } else {
            $A_SQL = mysqli_query($A_CONNECT,
            "SELECT DISTINCT j.namasalesman, j.kodesalesman, j.namacustomer, j.alamatcustomer, b.namabarang, b.satuan, b.namapabrik,
            SUM(d.jumlah) AS jumlah, SUM(d.totalharga)/SUM(D.Jumlah) AS hargarata, sum(d.totalharga) AS totalharga
            from penjualan j
            inner join detailpenjualan d on j.nopenjualan = d.nopenjualan
            inner join masterbarang b on d.kodebarang = b.kodebarang
            WHERE j.tanggal BETWEEN '".$TANGGALAWAL."' AND '".$TANGGALAKHIR."' AND namasalesman = '".$A_NAMASALESMAN."' 
            GROUP BY j.namasalesman, j.kodesalesman, j.namacustomer, j.alamatcustomer, b.namabarang, b.satuan, b.namapabrik");
        }

        while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
                if ($NAMASALESMAN != $A_RES['namasalesman']) {
                    echo "<div style=  'display: flex;
                                        margin: 0;
                                        font-size: 20px;
                                        padding-bottom: 10px;
                                        flex-direction: row;
                                        flex-wrap: wrap;
                                        justify-content: center;
                                        align-items: center;
                                        align-content: center;
                                        background-color: yellow;'><br/>";
                    echo "SALES: ".$A_RES['namasalesman'];
                    echo "</div>";
                    echo "<br/>";
                    $NAMASALESMAN = $A_RES['namasalesman'];
                }

                if ($NAMACUSTOMER != $A_RES['namacustomer']) {
                    if ($TOTALQTY != 0) {
                    ?>
                        <tr>
                        <td></td>
                        <td></td>
                        <td align="center" style="font-weight: bold">TOTAL</td>
                        <td align="right"><?php echo number_format($TOTALQTY); ?></td>
                        <td align="right"></td>
                        <td align="right"><?php echo number_format($TOTALPENJUALAN); ?></td>
                        </tr>
                    <?php
                    }
                    echo "<span style = 'font-size: 16px; color: red;'><b>";
                    echo $A_RES['namacustomer'];
                    echo "</b></span>";
                    $NAMACUSTOMER = $A_RES['namacustomer'];
                    ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr class="heading-table">
                                <td style='width: 33%' align="center">Nama Barang</td>
                                <td style='width: 8%' align="center">Sat.</td>
                                <td style='width: 25%' align="center">Pabrik</td>
                                <td style='width: 10%' align="center">Jumlah</td>
                                <td style='width: 12%' align="center">Harga Rt2</td>
                                <td style='width: 12%' align="center">Total</td>
                            </tr>
                        </thead>
                    <?php
                    $TOTALQTY = 0;
                    $TOTALPENJUALAN = 0;
                }

                ?>
                <tr>
                <td><?php echo $A_RES['namabarang']; ?></td>
                <td><?php echo $A_RES['satuan']; ?></td>
                <td><?php echo $A_RES['namapabrik']; ?></td>
                <td align="right"><?php echo number_format($A_RES['jumlah']); ?></td>
                <td align="right"><?php echo number_format($A_RES['hargarata']); ?></td>
                <td align="right"><?php echo number_format($A_RES['totalharga']); ?></td>
                </tr>
                <?php
                $TOTALQTY += $A_RES['jumlah'];
                $TOTALPENJUALAN += $A_RES['totalharga'];
        }
        ?>
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