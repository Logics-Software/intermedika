<?php
    if(isset($_SESSION['intermedika-login'])){
	?>
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <nav class="navbar navbar-inverse navbar-fixed-top ">
                    <div class="container">
                            <div class="navbar-header">
                                <button style="float:left;margin-left:15px;" type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                                    <span class="sr-only">Menu</span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                </button>
                                <img src="layout/intermedika.png" alt="intermedika Online" height="50" style="margin-right:10px; float:left"/>
                                <a class="navbar-brand" href=""><strong>Online</strong></a>
                            </div>
                            <div id="navbar" class="collapse navbar-collapse">
                                <ul class="nav navbar-nav navbar-right">
                                    <li class="dropdown">
                                            <a href="#" data-toggle="dropdown">Stok & Harga <span class="caret"></span></a>
                                            <ul class="dropdown-menu">
                                                <li><a href="index.php?page=daftar-harga-jual">Daftar Stok dan Harga Barang</a></li>
                                                <li><a href="index.php?page=daftar-harga-batch">Daftar Stok dan Harga per Batch</a></li> 
                                                <li><a href="index.php?page=daftar-persediaan">Daftar Persediaan per Batch</a></li> 
                                            </ul>
                                    </li>
                                    <?php
                                    if ($_SESSION['privilege']!='2'){
                                        ?>
                                        <li class="dropdown">
                                            <a href="#" data-toggle="dropdown">Tagihan & Rekap <span class="caret"></span></a>
                                            <ul class="dropdown-menu">
                                                <li><a href="index.php?page=daftar-piutang">Tagihan Penjualan (Piutang)</a></li>
                                                <li><a href="index.php?page=daftar-hutang">Tagihan Pembelian (Hutang)</a></li>
                                                <li><a href="index.php?page=rekap-penjualan">Daftar Transaksi Penjualan</a></li>
                                                <li><a href="index.php?page=rekap-pembelian">Daftar Transaksi Pembelian</a></li>
                                            </ul>
                                        </li>
                                        <li><a href="index.php?page=omset-penjualan">Omset Sales</a></li>
                                        <?php
                                    }
                                    ?>
                                        <li><a href="index.php?page=customer-aktif">Customer Non Aktif</a></li>
                                    <?php
                                    if ($_SESSION['privilege']=='3'){
                                        ?>
                                        <li><a href="index.php?page=manajemen-user">Manajemen User</a></li>
                                        <?php
                                    }
                                    ?>
                                    <li class="dropdown">
                                        <a href="#" data-toggle="dropdown"><i class="fa fa-user fa-lg"></i> <?php echo $_SESSION['namauser'];?>  <span class="caret"></span></a>
    									<ul class="dropdown-menu">
    										<li><a href="index.php?page=akun">Pengaturan</a></li>
    										<li><a href="index.php?page=logout">Logout</a></li>
    									</ul>
    								</li>
                                </ul>
                            </div><!--/.nav-collapse -->
                        </div>
                    </nav>
                    <?php
                        if(isset($_GET['page'])){
                            $A_PAGE = $_GET['page'];
                        }else{
                            $A_PAGE = '';
                        }
                        if($A_PAGE == ''){
                            header("Location: index.php?page=daftar-harga-jual");
                        }
                        elseif($A_PAGE == 'omset-penjualan'){
                            require_once 'omset-penjualan.php';
                        }
                        elseif($A_PAGE == 'daftar-harga-jual'){
                            require_once 'daftar-harga-jual.php';
                        }
                        elseif($A_PAGE == 'daftar-harga-batch'){
                            require_once 'daftar-harga-batch.php';
                        }
                        elseif($A_PAGE == 'daftar-persediaan'){
                            require_once 'daftar-persediaan.php';
                        }
                        elseif($A_PAGE == 'daftar-piutang'){
                            require_once 'daftar-penjualan.php';
                        }
                        elseif($A_PAGE == 'daftar-hutang'){
                            require_once 'daftar-pembelian.php';
                        }
                        elseif($A_PAGE == 'rekap-penjualan'){
                            require_once 'transaksi-penjualan.php';
                        }
                        elseif($A_PAGE == 'rekap-pembelian'){
                            require_once 'transaksi-pembelian.php';
                        }
                        elseif($A_PAGE == 'customer-aktif'){
                            require_once 'daftar-customer-aktif.php';
                        }
                        elseif($A_PAGE == 'manajemen-user'){
                            require_once 'manajemen-user.php';
                        }
                        elseif($A_PAGE == 'akun'){
                            require_once 'akun.php';
                        }
                        elseif($A_PAGE == 'logout'){
                            session_destroy();
                            header('Location: index.php');
                        }
                        ?>
                </div>
            </div>
        </div>
    <?php
	}
?>
