<br>
<h4>Daftar Admin</h4>
<hr>
<?php echomsg(); ?>
<div class="panel panel-primary">
	<div class="panel-heading" id="handme"></i><i class="fa fa-plus"></i> Tambah Data User <i class="fa fa-user"></i></div>
	<div class="panel-body" id="hideme">
	<form name="" method="POST" action="" enctype="multipart/form-data">
		<div class="form-group">
			<label>User Name</label>
			<input type="text" name="useradd" id="useradd" value = "" placeholder="nama user baru..." class="form-control" required>
		</div>
		<div class="form-group">
			<label>Password</label>
			<input type="password" name="password1" id="password1" value = "" placeholder="password..." class="form-control" required>
		</div>
		<div class="form-group">
			<label>Ulangi Password</label>
			<input type="password" name="password2" id="password2" class="form-control" required>
		</div>
		<div class="form-group">
			<label>Nama Lengkap</label>
			<input type="text" name="nama" id="nama" class="form-control" required>
		</div>
		<div class="form-group">
			<label>Email</label>
			<input type="text" name="email" id="email" class="form-control">
		</div>
		<div class="form-group">
			<label>Kode Sales</label>
			<input type="text" name="kodesales" id="kodesales" class="form-control">
		</div>
		<div class="form-group">
			<label>Privilege</label>
        	<select name="privilege" id="privilege" class="form-control" required>
			<option value="0">Sales</option>
			<option value="1">Admin</option>
			<option value="2">Customer</option>
			<option value="3">Supervisor</option>
        	</select>
		</div>
		<div class="form-group">
			<label>Status User</label>
        	<select name="status" id="status" class="form-control" required>
			<option value="0">Non Aktif</option>
            <option value="1">Aktif</option>
        	</select>
		</div>
		<button type="submit" name="submitupload" class="btn btn-success btn-block">
			<i class="fa fa-floppy-o" aria-hidden="true"></i> Simpan</button>
	</form>
	</div>
</div>

<div class="modal fade" id="edit-user" tabindex="-1" role="dialog"
     aria-labelledby="edit-user-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close"
                   data-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="label-sales">
                    Edit Data User
                </h4>
            </div>
            <div class="modal-body">
				<form role="form" method="post" action="" enctype="multipart/form-data" data-toggle="validator" >
					<div class="form-group">
						<input type="hidden" name="userid" id="userid" class="form-control" readonly> 
					</div>
					<div class="form-group">
						<label for="username">User Name</label>
						<input type="text" name="username" id="username" class="form-control">
					</div>
					<div class="form-group">
						<label for="password">Password</label>
						<input type="password" name="password" id="password" class="form-control">
					</div>
					<div class="form-group">
						<label for="namalengkap">Nama Lengkap</label>
						<input type="text" name="namalengkap" id="namalengkap" class="form-control">
					</div>
					<div class="form-group">
						<label for="alamatemail">Email</label>
						<input type="text" name="alamatemail" id="alamatemail" class="form-control">
					</div>
					<div class="form-group">
						<label for="kodesalesedit">Kode Sales</label>
						<input type="text" name="kodesalesedit" id="kodesalesedit" class="form-control">
					</div>
					<div class="form-group">
						<label>Privilege</label>
						<select name="privilegeedit" id="privilegeedit" class="form-control" required>
						<option value="0">Sales</option>
						<option value="1">Admin</option>
						<option value="2">Customer</option>
						<option value="3">Supervisor</option>
						</select>
					</div>
					<div class="form-group">
						<label>Status User</label>
						<select name="statusedit" id="statusedit" class="form-control" required>
						<option value="0">Non Aktif</option>
						<option value="1">Aktif</option>
						</select>
					</div>
				</form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-sign-out" aria-hidden="true"></i> Keluar</button>
                <button type="submit" class="btn btn-primary btn-save" onclick="updateUser()"><i class="fa fa-floppy-o" aria-hidden="true"></i> Update</button>
            </div>
        </div>
    </div>
</div>

<?php
if(isset($_POST['submitupload'])){
	$A_USERNAME  = $_POST['useradd'];
	$A_PASS1 	 = $_POST['password1'];
	$A_PASS2 	 = $_POST['password2'];
	$A_NAMA  = $_POST['nama'];
	$A_EMAIL  = $_POST['email'];
	$A_KODESALES  = $_POST['kodesales'];
	$A_PRIVILEGE  = $_POST['privilege'];
	$A_STATUS  = $_POST['status'];

	if($A_PASS1 == $A_PASS2){
		$A_INSERT = mysqli_query($A_CONNECT,"INSERT INTO user (
		`username`,
		`password`,
		`nama`,
		`email`,
		`kodesales`,
		`privilege`,
		`status`
		) VALUES (
		'".$A_USERNAME."',
		'".$A_PASS1."',
		'".$A_NAMA."',
		'".$A_EMAIL."',
		'".$A_KODESALES."',
		'".$A_PRIVILEGE."',
		'".$A_STATUS."'
		)");

		$_SESSION['form-msg'] 		= 'Proses Update Selesai!';
		$_SESSION['form-msg-type'] 	= 'success';
		header('Location: index.php?page=manajemen-user');
	}else{
		$_SESSION['form-msg'] 		= 'Password Tidak Sama';
		$_SESSION['form-msg-type'] 	= 'danger';
		header('Location: index.php?page=manajemen-user');
	}
}
?>
<hr>
<table class="table table-bordered">
	<thead>
		<tr class="heading-table">
			<td>Username</td>
			<td>Password</td>
			<td>Nama Lengkap</td>
			<td>Email</td>
			<td>Kode Sales</td>
			<td>Aksi</td>
		</tr>
	</thead>
	<tbody>
		<?php
			$A_SQL = mysqli_query($A_CONNECT,"SELECT * FROM user ORDER BY username DESC");
			while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
			?>
			<tr>
				<td><?php echo $A_RES['username']; ?></td>
				<td><?php echo $A_RES['password']; ?></td>
				<td><?php echo $A_RES['nama']; ?></td>
				<td><?php echo $A_RES['email']; ?></td>
				<td><?php echo $A_RES['kodesales']; ?></td>
				<td>
					<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#edit-user"
						onclick="editUser('<?php echo $A_RES['userid']; ?>')"> Edit
					</button>
					<button type="button" class="btn btn-danger btn-sm"
						onclick="hapusUser('<?php echo $A_RES['userid']; ?>')"> Hapus
					</button>
				</td>
			</tr>
			<?php
			}
		?>
	</tbody>
</table>
<?php

?>
