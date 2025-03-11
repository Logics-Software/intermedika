<?php
ob_start();
session_start();

require_once 'config.php';
require_once 'functions.php';

date_default_timezone_set('Asia/Jakarta');

if(isset($_GET['ajaxpage'])){
	$A_AJAXPAGE = $_GET['ajaxpage'];
}else{
	$A_AJAXPAGE = '';
}

$A_USERID = $_GET['userid'];

// ================== User edit ==================
if($A_AJAXPAGE == 'updateuser'){
	$query =
		"UPDATE `user` SET
			username='".$_POST['username']."',
			password='".$_POST['password']."',
			nama='".$_POST['nama']."',
			email='".$_POST['email']."',
			privilege='".$_POST['privilege']."',
			kodesales='".$_POST['kodesales']."',
			status='".$_POST['status']."'
		WHERE userid=".$_POST['userid']."";
	//echo $query;
	if (!$result = mysqli_query($A_CONNECT, $query)) {
		exit(mysqli_error($A_CONNECT));
	}

}elseif($A_AJAXPAGE == 'updateakun'){
	$query =
		"UPDATE `user` SET
			username='".$_POST['username']."',
			password='".$_POST['password']."',
			nama='".$_POST['nama']."',
			email='".$_POST['email']."'
		WHERE userid=".$_POST['userid']."";
	//echo $query;
	if (!$result = mysqli_query($A_CONNECT, $query)) {
		exit(mysqli_error($A_CONNECT));
	}

}elseif($A_AJAXPAGE == 'lihatuser'){
	$query = "SELECT * FROM `user` WHERE userid = ". $A_USERID ."";
	if (!$result = mysqli_query($A_CONNECT, $query)) {
		exit(mysqli_error($A_CONNECT));
	}
	$response = array();
	if(mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$response = $row;
		}
	}
	else
	{
		$response['status'] = 200;
		$response['message'] = "Data not found!";
	}
	echo json_encode($response);

}elseif($A_AJAXPAGE == 'hapususer'){
	$query = "DELETE FROM `user` WHERE userid = ". $A_USERID ."";
	if (!$result = mysqli_query($A_CONNECT, $query)) {
		exit(mysqli_error($A_CONNECT));
	}

}else{
	echo '<td colspan="4" style="text-align:center;font-weight:bold;height:50px">Terjadi Kesalahan !!</td>';
}
?>
