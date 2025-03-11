<?php
ob_start();
session_start();

require_once 'config.php';
require_once 'functions.php';
require_once 'Mobile_Detect.php';

date_default_timezone_set('Asia/Jakarta');
?>
<!DOCTYPE html>
<html lang="en">

<head>
<link rel='shortcut icon' type='image/x-icon' href='layout/indoprima.png' />
<?php
if(!isset($_SESSION['indoprima-login'])){
	?>
	<title>PT. SATRIA MEDIKA SEJAHTERA - Login</title>
	<?php
}else{
	if ($_SESSION['privilege']=='0'){
		?>
		<title>:: Halaman - Sales ::</title>
		<?php	
	}elseif ($_SESSION['privilege']=='1'){
		?>
		<title>:: Halaman - Admin ::</title>
		<?php
	}elseif ($_SESSION['privilege']=='1'){
		?>
		<title>:: Halaman - Customer ::</title>
		<?php	
	}else{
	?>
	<title>:: Halaman - Supervisor ::</title>
	<?php	
	}
}
	?>	
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<!-- *.css -->
<link rel='stylesheet' href='layout/bootstrap/css/bootstrap.min.css' type='text/css' media='all' />
<link rel='stylesheet' href='layout/custom.css' type='text/css' media='all' />
<link rel="stylesheet" href="layout/awesome/css/font-awesome.min.css" type='text/css' media='all' >
</head>

<body>
	<?php
	if(!isset($_SESSION['indoprima-login'])){
	?>
		<div class="container">
			<div class="row">
				<div class="col-sm-4">&nbsp;</div>
				<div class="col-sm-4">
					<img src="layout/logo-indoprima.png" alt="Online" height="150" 
					style = "display: block; margin-left: auto; margin-right: auto; margin-bottom: 20px;"/>
					<?php echomsg(); ?>
					<form method="POST" action="">
						<div class="form-group">
							<label for="">User Name</label>
							<input type="text" name="username" id="username" class="form-control" placeholder="Username">
						</div>
						<div class="form-group">
							<label for="password">Password</label>
							<input type="password" name="password" id="password" class="form-control" placeholder="Password">
						</div>
						<button type="submit" name="login" class="btn btn-primary btn-block">LOGIN</button>                        
					</form>
					<?php
					if(isset($_POST['login'])){
						$A_USERID = mysqli_real_escape_string($A_CONNECT,$_POST['username']);
						$A_PASSID = mysqli_real_escape_string($A_CONNECT,$_POST['password']);

						$A_SQL 	 = mysqli_query($A_CONNECT,"SELECT * FROM user WHERE username = '".$A_USERID."' AND password = '".$A_PASSID."'");
						$A_RES 	 = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC);
						$A_VALID = mysqli_num_rows($A_SQL);

						if($A_VALID){
							$_SESSION['indoprima-login'] 	= 'true';
                            $_SESSION['userid'] 		= $A_RES['userid'];
							$_SESSION['password'] 		= $A_RES['password'];
                            $_SESSION['username'] 		= $A_RES['username'];
                            $_SESSION['namauser'] 		= $A_RES['nama'];
							$_SESSION['email'] 			= $A_RES['email'];
                            $_SESSION['privilege'] 		= $A_RES['privilege'];
							$_SESSION['kodesales'] 		= $A_RES['kodesales'];
                            $_SESSION['status'] 		= $A_RES['status'];
							header('Location: index.php');
						}else{
							$_SESSION['form-msg'] 		= 'Data User Tidak Terdaftar';
							$_SESSION['form-msg-type'] 	= 'danger';
							header('Location: index.php');
						}
					}
					?>
				</div>
				<div class="col-sm-4">&nbsp;</div>
			</div>
		</div>
	<?php
	}else{
		require_once 'content.php';
	}
	?>

	<!-- *.js -->
	<script type='text/javascript' src='layout/jquery/jquery.min.js'></script>
	<script type='text/javascript' src='layout/bootstrap/js/bootstrap.min.js'></script>
	<script type='text/javascript' src='layout/custom.js'></script>
</body>
</html>
