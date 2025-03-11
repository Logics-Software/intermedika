<?php
function echomsg(){
	if(isset($_SESSION['form-msg'])){
		echo '<div class="alert alert-'.$_SESSION['form-msg-type'].'" role="alert">'.$_SESSION['form-msg'].'</div>';
		unset($_SESSION['form-msg']);
		unset($_SESSION['form-msg-type']);
	}
}
?>