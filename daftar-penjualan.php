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
            <div class="form-group">
                <label>Status Faktur</label>
                <select name="jatuhtempo" id="jatuhtempo" class="form-control">
                    <option value="1">Jatuh Tempo</option>
                    <option value="2">Semua Data Tagihan</option>
                </select>
            </div>
            <button type="submit" name="submitlaporan" class="btn btn-primary btn-block"><i class="fa fa-television" aria-hidden="true"></i> Tampilkan Daftar Tagihan</button>
        </form>
    </div>
</div>                   

<?php
if(isset($_POST['submitlaporan'])){
    $A_FIND = $_POST['namacustomer'];
    $A_JTEMPO = $_POST['jatuhtempo'];
    $A_KONDISI1 = '';
    $A_KONDISI2 = '';
    $A_QUERY = "SELECT * FROM penjualan ";
    if (trim($A_FIND) <> ''){
        $A_KONDISI1 = "(namacustomer LIKE '%".$A_FIND."%' or namasalesman LIKE '%".$A_FIND."%')";
    } 
    if ($A_JTEMPO == 1){
        if (trim($A_KONDISI1) == ''){
            $A_KONDISI1 = "jatuhtempo <= now() ";
        } else {
            $A_KONDISI1 = $A_KONDISI1." AND jatuhtempo <= now() ";
        }
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

    $A_FOLDER_PATH = "download-penjualan.php?querytext=".$A_QUERY; ?>
    <a class="btn btn-warning btn-block" target="_blank" href=" <?=$A_FOLDER_PATH?> ">Download Tagihan Piutang</a><br/>
    <?php
    
    $A_QUERY = '';
    $A_KONDISI1 = '';
    $A_KONDISI2 = '';
    $A_NAMACUSTOMER = '';
    $A_TOTALSALDO = 0;
    $A_TOTALFAKTUR = 0;
    $A_UMUR = 0;
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
    }
    ?>
    
    <table class="table table-bordered">
        <thead>
            <tr class="heading-table">
                <td>No.Faktur</td>
                <td>Tgl.Faktur</td>
                <td>JT.Tempo</td>
                <td>UM</td>
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
                $A_QUERY = "SELECT * FROM penjualan ";
                if (trim($A_FIND) <> ''){
                    $A_KONDISI1 = "(namacustomer LIKE '%".$A_FIND."%' or namasalesman LIKE '%".$A_FIND."%')";
                } 
                if ($A_JTEMPO == 1){
                    if (trim($A_KONDISI1) == ''){
                        $A_KONDISI1 = "jatuhtempo <= now() ";
                    } else {
                        $A_KONDISI1 = $A_KONDISI1." AND jatuhtempo <= now() ";
                    }
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
                    <td>
                    <?php 
                    $date1 = strtotime($A_RES['tanggal']);
                    $date2 = strtotime(date('m/d/Y h:i:s a', time()));
                    $diff = $date2 - $date1;
                    $A_UMUR = floor($diff / (60 * 60 * 24));
                    ?>
                    <?php echo '<a target="_blank" href="detail-penjualan.php?nopenjualan='.$A_RES["nopenjualan"].'">' 
                    .$A_RES['nopenjualan']. '</a>' ?>
                    </td>
                    <td><?php echo date('d-m-Y', strtotime($A_RES['tanggal'])); ?></td>
                    <td><?php echo date('d-m-Y', strtotime($A_RES['jatuhtempo'])); ?></td>
                    <td align="right"><?php echo $A_UMUR; ?></td>
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
                $A_TOTALFAKTUR += $A_RES['nilaipenjualan'];
                $A_TOTALSALDO += $A_RES['saldopenjualan'];
                }
            ?>
        </tbody>
        <tbody>
                <tr>
                    <td></td><td></td><td></td><td></td>
                    <?php
                    if ($_SESSION['privilege']!='0'){
                    ?>
                    <td></td><?php
                    }?>
                    </td>
                    <td align="center">Total Tagihan</td>
                    <td align="right"><?php echo number_format($A_TOTALFAKTUR); ?></td>
                    <td align="right"><?php echo number_format($A_TOTALSALDO); ?></td>
                </tr>
                <?php
            ?>
        </tbody>        
    </table>
    <?php
    echo "<script> document.getElementById('namacustomer').value = '".$A_FIND."'; </script>";
    echo "<script> document.getElementById('jatuhtempo').value = '".$A_JTEMPO."'; </script>";
}else{
    $A_FIND = '';
    $A_JTEMPO = '';
    $A_QUERY = '';
    $A_KONDISI1 = '';
    $A_KONDISI2 = '';
    $A_NAMACUSTOMER = '';
    $A_TOTALSALDO = 0;
    $A_TOTALFAKTUR = 0;
    $A_UMUR = 0;
 
    $A_QUERY = "SELECT * FROM penjualan ";
    if (trim($A_FIND) <> ''){
        $A_KONDISI1 = "(namacustomer LIKE '%".$A_FIND."%' or namasalesman LIKE '%".$A_FIND."%')";
    } 
    if ($A_JTEMPO == 1){
        if (trim($A_KONDISI1) == ''){
            $A_KONDISI1 = "jatuhtempo <= now() ";
        } else {
            $A_KONDISI1 = $A_KONDISI1." AND jatuhtempo <= now() ";
        }
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

    $A_FOLDER_PATH = "download-penjualan.php?querytext=".$A_QUERY; 

    $A_TOTALFAKTUR = 0;
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
    <a class="btn btn-warning btn-block" target="_blank" href=" <?=$A_FOLDER_PATH?> ">Download Tagihan Piutang</a><br/>
    <table class="table table-bordered">
        <thead>
            <tr class="heading-table">
                <td>No.Faktur</td>
                <td>Tgl.Faktur</td>
                <td>JT.Tempo</td>
                <td>UM</td>
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
                    $A_QUERY = "SELECT * FROM penjualan WHERE saldopenjualan > 0 AND jatuhtempo <= now() ORDER BY tanggal, nopenjualan";
                }else{
                    $A_QUERY = "SELECT * FROM penjualan WHERE kodesalesman = '".$_SESSION['kodesales']."' AND saldopenjualan > 0 AND jatuhtempo <= now() ORDER BY tanggal, nopenjualan";
                }

                $A_SQL = mysqli_query($A_CONNECT,$A_QUERY);
                while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
                ?>
                <tr>
                    <?php 
                    $date1 = strtotime($A_RES['tanggal']);
                    $date2 = strtotime(date('m/d/Y h:i:s a', time()));
                    $diff = $date2 - $date1;
                    $A_UMUR = floor($diff / (60 * 60 * 24));
                    ?>
                    <td>
                        <?php echo '<a target="_blank" href="detail-penjualan.php?nopenjualan='.$A_RES["nopenjualan"].'">' 
                                    .$A_RES['nopenjualan']. '</a>' ?>
                    </td>
                    <td><?php echo date('d-m-Y', strtotime($A_RES['tanggal'])); ?></td>
                    <td><?php echo date('d-m-Y', strtotime($A_RES['jatuhtempo'])); ?></td>
                    <td align="right"><?php echo $A_UMUR; ?></td>
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
                $A_TOTALFAKTUR += $A_RES['nilaipenjualan'];
                $A_TOTALSALDO += $A_RES['saldopenjualan'];
                }
            ?>
        </tbody>
        <tbody>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <?php
                    if ($_SESSION['privilege']!='0'){
                    ?>
                    <td></td><?php
                    }?>
                    <td align="center">Total Tagihan</td>
                    <td align="right"><?php echo number_format($A_TOTALFAKTUR); ?></td>
                    <td align="right"><?php echo number_format($A_TOTALSALDO); ?></td>
                </tr>
                <?php
            ?>
        </tbody>        
    </table>
    <?php
}
?>