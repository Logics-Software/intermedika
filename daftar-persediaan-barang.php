<br>
<?php echomsg(); ?>
<div class="panel panel-primary">
	<div class="panel-heading" id="handme"></i><i class="fa fa-search" aria-hidden="true"></i> Search</div>
	<div class="panel-body" id="hideme">
        <form name="" method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Query Nama Barang</label>
                <input type="text" name="namabarang" id="namabarang" placeholder="Cari barang..." class="form-control">
            </div>
            <div class="form-group">
                <label>Stok Barang</label>
                <select name="stokbarang" id="stokbarang" class="form-control">
                    <option value="1">Semua Data Barang</option>
                    <option value="2">Stok > 0</option>
                </select>
            </div>
            <div class="form-group">
                <label>Pabrik</label>
                <select name="namapabrik" id="namapabrik" class="form-control">
                    <option value="">SEMUA PABRIK</option>
                    <?php
                        $A_SQL = mysqli_query($A_CONNECT,"SELECT DISTINCTROW namapabrik FROM daftarharga ORDER BY NamaPabrik");
                        while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
                        ?>
                            <option value="<?php echo $A_RES['namapabrik']; ?>"><?php echo $A_RES['namapabrik']; ?></option>
                        <?php
                        }
                    ?>
                </select>
            </div>
            <button type="submit" name="submitlaporan" class="btn btn-primary btn-block"><i class="fa fa-television" aria-hidden="true"></i> Tampilkan Daftar Harga</button>
        </form>
    </div>
</div>                   

<?php
if(isset($_POST['submitlaporan'])){
    $A_FIND = $_POST['namabarang'];
    $A_STOK = $_POST['stokbarang'];
    $A_NAMAPABRIK = $_POST['namapabrik'];
    $A_QUERY = '';
    $A_KONDISI1 = '';
    $A_KONDISI2 = '';
    $A_KONDISI3 = '';
    $A_TOTALPERSEDIAAN = 0;
    $A_FOLDER_PATH = '';
    ?>
    <h3>Daftar Persediaan Barang per Batch</h3>
    <?php
    $A_QUERY = "SELECT * FROM daftarharga ";
    if (trim($A_FIND) <> ''){
        $A_KONDISI1 = "namabarang LIKE '%".$A_FIND."%' ";
    }
    if ($A_STOK == 2){
        $A_KONDISI2 = "stokakhir > 0 ";
    }
    if (trim($A_NAMAPABRIK) <> ''){
        $A_KONDISI3 = "namapabrik = '".$A_NAMAPABRIK."' ";
    }

    if (trim($A_KONDISI1)<>'' && trim($A_KONDISI2)<>''){
        $A_KONDISI2 = " AND ".$A_KONDISI2;
    }
    if (trim(($A_KONDISI1)<>'' || trim($A_KONDISI2)<>'') && trim($A_KONDISI3)<>''){$A_KONDISI3 = " AND ".$A_KONDISI3;}

    if (trim($A_KONDISI1)<>'' || trim($A_KONDISI2)<>'' || trim($A_KONDISI3)<>''){
        $A_QUERY = $A_QUERY . " WHERE namabarang <> '' AND " . $A_KONDISI1 . $A_KONDISI2 . $A_KONDISI3 . " ORDER BY namabarang";
    }else{
        $A_QUERY = $A_QUERY . " WHERE namabarang <> '' ORDER BY namabarang";
    }
    $A_FOLDER_PATH = "download-persediaan.php?querytext=".$A_QUERY;
    ?>
    <a class="btn btn-warning btn-block" target="_blank" href=" <?=$A_FOLDER_PATH?> ">Download Persediaan Barang per Batch</a><br/>
    <table class="table table-bordered">
        <tbody>
            <?php
                $A_QUERY = "SELECT namabarang, namapabrik, sum(stokakhir) AS stokakhir, sum(stokakhir*hpp)/sum(stokakhir) AS hpp FROM daftarharga ";
                if (trim($A_FIND) <> ''){
                    $A_KONDISI1 = "namabarang LIKE '%".$A_FIND."%' ";
                }
                if ($A_STOK == 2){
                    $A_KONDISI2 = "stokakhir > 0 ";
                }
                if (trim($A_NAMAPABRIK) <> ''){
                    $A_KONDISI3 = "namapabrik = '".$A_NAMAPABRIK."' ";
                }
                
                if (trim($A_KONDISI1)<>'' && trim($A_KONDISI2)<>''){
                    $A_KONDISI2 = " AND ".$A_KONDISI2;
                }
                if (trim(($A_KONDISI1)<>'' || trim($A_KONDISI2)<>'') && trim($A_KONDISI3)<>''){$A_KONDISI3 = " AND ".$A_KONDISI3;}
    
                if (trim($A_KONDISI1)<>'' || trim($A_KONDISI2)<>'' || trim($A_KONDISI3)<>''){
                    $A_QUERY = $A_QUERY . " WHERE namabarang <> '' AND " . $A_KONDISI1 . $A_KONDISI2 . $A_KONDISI3 . " GROUP BY namabarang, namapabrik";
                }else{
                    $A_QUERY = $A_QUERY . " WHERE namabarang <> '' GROUP BY namabarang, namapabrik";
                }
                $A_SQL = mysqli_query($A_CONNECT,$A_QUERY);
                while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
                    $A_TOTALPERSEDIAAN = $A_TOTALPERSEDIAAN + ($A_RES['hpp']*$A_RES['stokakhir']);
                ?>
                <tr>
                    <td><?php echo $A_RES['namabarang']; ?></td>
                    <td><?php echo $A_RES['namapabrik']; ?></td>
                    <td align="right"><?php echo number_format($A_RES['stokakhir']); ?></td>
                    <td align="right"><?php echo number_format($A_RES['hpp']); ?></td>
                    <td align="right"><?php echo number_format($A_RES['hpp']*$A_RES['stokakhir']); ?></td>
                </tr>
                <?php
                }
            ?>
        </tbody>
        <tfoot>
            <tr class="heading-table">
                <td align="center">TOTAL PERSEDIAAN</td>
                <td></td>
                <td></td>
                <td align="right"></td>
                <td align="right"><?php echo number_format($A_TOTALPERSEDIAAN); ?></td>
            </tr>
        </tfoot>
    </table>
    <?php
    echo "<script> document.getElementById('namabarang').value = '".$A_FIND."'; </script>";
    echo "<script> document.getElementById('stokbarang').value = ".$A_STOK."; </script>";
    echo "<script> document.getElementById('namapabrik').value = '".$A_NAMAPABRIK."'; </script>";
}else{
    ?>
    <h3>Daftar Persediaan Barang per Batch</h3>
    <a class="btn btn-warning btn-block" href="download-persediaan.php" target="_blank">Download Persediaan Barang per Batch</a><br/>
    <table class="table table-bordered">
        <thead>
            <tr class="heading-table">
                <td align="center">Nama Barang</td>
                <td align="center">Pabrik</td>
                <td align="center">Stok</td>
                <td align="center">HPP</td>
                <td align="center">Persediaan</td>
            </tr>
        </thead>
        <tbody>
            <?php
                $A_TOTALPERSEDIAAN = 0;
                $A_QUERY = "SELECT namabarang, namapabrik, sum(stokakhir) AS stokakhir, sum(stokakhir*hpp)/sum(stokakhir) AS hpp FROM daftarharga GROUP BY namabarang, namapabrik";
                $A_SQL = mysqli_query($A_CONNECT,$A_QUERY);
                while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
                    $A_TOTALPERSEDIAAN = $A_TOTALPERSEDIAAN + ($A_RES['hpp']*$A_RES['stokakhir']);
                    ?>
                <tr>
                    <td><?php echo $A_RES['namabarang']; ?></td>
                    <td><?php echo $A_RES['namapabrik']; ?></td>
                    <td align="right"><?php echo number_format($A_RES['stokakhir']); ?></td>
                    <td align="right"><?php echo number_format($A_RES['hpp']); ?></td>
                    <td align="right"><?php echo number_format($A_RES['hpp']*$A_RES['stokakhir']); ?></td>
                </tr>
                <?php
                }
            ?>
        </tbody>
        <tfoot>
            <tr class="heading-table">
                <td align="center">TOTAL PERSEDIAAN</td>
                <td></td>
                <td></td>
                <td align="right"></td>
                <td align="right"><?php echo number_format($A_TOTALPERSEDIAAN); ?></td>
            </tr>
        </tfoot>
    </table>
    <?php
}
?>