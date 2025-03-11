<br>
<?php echomsg(); ?>
<div class="panel panel-primary">
	<div class="panel-heading" id="handme"></i><i class="fa fa-search" aria-hidden="true"></i> Search</div>
	<div class="panel-body" id="hideme">
        <form name="" method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Query Nama Customer</label>
                <input type="text" name="namacustomer" id="namacustomer" placeholder="Cari customer..." class="form-control">
            </div>
            <button type="submit" name="submitlaporan" class="btn btn-primary btn-block"><i class="fa fa-television" aria-hidden="true"></i> Tampilkan Daftar Tagihan</button>
        </form>
    </div>
</div>                   

<?php
if(isset($_POST['submitlaporan'])){
    $A_FIND = $_POST['namacustomer'];
    $A_QUERY = '';
    $A_KONDISI1 = '';
    $A_TOTALSALDO = 0;
    ?>
    <?php
    if ($_SESSION['privilege']=='0'){
    ?>
        <h3> Daftar Tagihan Piutang - <?php echo $_SESSION['namauser']; ?></h3>
    <?php
    }else{
        ?>
        <h3>Daftar Tagihan Piutang</h3>
        <?php
    }?>
    <table class="table table-bordered">
        <thead>
            <tr class="heading-table">
                <td>No.Faktur</td>
                <td>Tgl.Faktur</td>
                <td>Nama Customer</td>
                <?php
                if ($_SESSION['privilege']!='0'){
                ?>
                <td>Sales</td>
                <?php
                }?>
                <td align="right">Nilai Faktur</td>
                <td align="right">Saldo Tagihan</td>
            </tr>
        </thead>
        <tbody>
            <?php
                $A_QUERY = "SELECT * FROM tagihan ";
                if (trim($A_FIND) <> ''){
                    $A_KONDISI1 = "namacustomer LIKE '%".$A_FIND."%' or namasalesman LIKE '%".$A_FIND."%'";
                }
                if (trim($A_KONDISI1)<>''){
                    if ($_SESSION['privilege']!='0'){    
                        $A_QUERY = $A_QUERY . " WHERE saldopenjualan > 0 AND  " . $A_KONDISI1 . " ORDER BY tanggal, nopenjualan";
                    }else{
                        $A_QUERY = $A_QUERY . " WHERE kodesalesman = '".$_SESSION['kodesales']."' AND saldopenjualan > 0 AND  " . $A_KONDISI1 . " ORDER BY tanggal, nopenjualan";
                    }
                }else{
                    if ($_SESSION['privilege']!='0'){    
                        $A_QUERY = $A_QUERY . " WHERE saldopenjualan > 0 ORDER BY tanggal, nopenjualan";
                    }else{
                        $A_QUERY = $A_QUERY . " WHERE kodesalesman = '".$_SESSION['kodesales']."' AND saldopenjualan > 0 ORDER BY tanggal, nopenjualan";
                    }
                }

                $A_SQL = mysqli_query($A_CONNECT,$A_QUERY);
                while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
                ?>
                <tr>
                    <td><?php echo $A_RES['nopenjualan']; ?></td>
                    <td><?php echo date('d-m-Y', strtotime($A_RES['tanggal'])); ?></td>
                    <td><?php echo $A_RES['namacustomer']; ?></td>
                    <?php
                    if ($_SESSION['privilege']!='0'){
                    ?>
                    <td><?php echo $A_RES['namasalesman']; ?></td>
                    <?php
                    }?>
                    <td align="right"><?php echo number_format($A_RES['nilaipenjualan']); ?></td>
                    <td align="right"><?php echo number_format($A_RES['saldopenjualan']); ?></td>
                </tr>
                <?php
                $A_TOTALSALDO += $A_RES['saldopenjualan'];
                }
            ?>
        </tbody>
        <tbody>
                <tr>
                    <td></td><td></td><td></td>
                    <?php
                    if ($_SESSION['privilege']!='0'){
                    ?>
                    <td></td><?php
                    }?>
                    </td>
                    <td align="center">Total Tagihan</td>
                    <td align="right"><?php echo number_format($A_TOTALSALDO); ?></td>
                </tr>
                <?php
            ?>
        </tbody>        
    </table>
    <?php
    echo "<script> document.getElementById('namacustomer').value = '".$A_FIND."'; </script>";
}else{
    $A_TOTALSALDO = 0;
    ?>
    <?php
    if ($_SESSION['privilege']=='0'){
    ?>
        <h3> Daftar Tagihan Piutang - <?php echo $_SESSION['namauser']; ?></h3>
    <?php
    }else{
        ?>
        <h3>Inkaso Piutang</h3>
        <?php
    }?>
    <form name="" method="POST" action="" enctype="multipart/form-data">
    <table class="table table-bordered">
        <thead>
            <tr class="heading-table">
                <td>No.Faktur</td>
                <td>Tgl.Faktur</td>
                <td>Nama Customer</td>
                <?php
                if ($_SESSION['privilege']!='0'){
                ?>
                <td>Sales</td>
                <?php
                }?>
                <td align="right">Nilai Faktur</td>
                <td align="right">Saldo Tagihan</td>
            </tr>
        </thead>
        <tbody>
            <?php
                if ($_SESSION['privilege']!='0'){
                    $A_QUERY = "SELECT * FROM tagihan WHERE saldopenjualan > 0 ORDER BY tanggal, nopenjualan";
                }else{
                    $A_QUERY = "SELECT * FROM tagihan WHERE kodesalesman = '".$_SESSION['kodesales']."' AND saldopenjualan > 0 ORDER BY tanggal, nopenjualan";
                }

                $A_SQL = mysqli_query($A_CONNECT,$A_QUERY);
                while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
                ?>
                <tr>
                    <td>
                    <input type="checkbox" name="nopenjualan[]" value= "<?php echo $A_RES['nopenjualan']; ?>"> <?php echo $A_RES['nopenjualan']; ?></td>
                    <td><?php echo date('d-m-Y', strtotime($A_RES['tanggal'])); ?></td>
                    <td><?php echo $A_RES['namacustomer']; ?></td>
                    <?php
                    if ($_SESSION['privilege']!='0'){
                    ?>
                    <td><?php echo $A_RES['namasalesman']; ?></td>
                    <?php
                    }?>
                    <td align="right"><?php echo number_format($A_RES['nilaipenjualan']); ?></td>
                    <td align="right"><input style="text-align: right;" type="number" name="inkaso[]" value= "<?php echo $A_RES['saldopenjualan']; ?>"><?php $A_RES['saldopenjualan']; ?></td>
                </tr>
                <?php
                $A_TOTALSALDO += $A_RES['saldopenjualan'];
                }
            ?>
        </tbody>
        <tbody>
                <tr>
                    <td></td><td></td><td></td>
                    <?php
                    if ($_SESSION['privilege']!='0'){
                    ?>
                    <td></td><?php
                    }?>
                    <td align="center">Total Tagihan</td>
                    <td align="right"><?php echo number_format($A_TOTALSALDO); ?></td>
                </tr>
                <?php
            ?>
        </tbody>        
    </table>
    <button type="submit" name="submitpembayaran" class="btn btn-danger btn-block"><i class="fa fa-save" aria-hidden="true"></i> Simpan Inkaso</button>
    </form>
    <?php
}
?>
