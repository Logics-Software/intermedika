<br/>
<div class="alert alert-success" style="display:none">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <i class="icon fa fa-check"></i>
    <strong> Perubahan berhasil disimpan! </strong>
</div>
<div class="panel panel-primary edit-akun">
    <div class="panel-heading">
        <h4 class="modal-title" id="label-admin">
            Edit Akun User
        </h4>
    </div>
    <div class="panel-body">
        <?php
			$A_SQL = mysqli_query($A_CONNECT,"SELECT * FROM user WHERE userid='".$_SESSION['userid']."'");
			while($A_RES = mysqli_fetch_array($A_SQL,MYSQLI_ASSOC)){
        ?>
		<form role="form" method="post" action="" enctype="multipart/form-data" data-toggle="validator" class="form-horizontal">
			<div class="form-group">
                <div class="col-xs-5">
    				<input type="hidden" name="userid" id="userid" class="form-control" readonly
                        value="<?php echo $A_RES['userid']; ?>">
                </div>
			</div>
			<div class="form-group">
                <div class="col-xs-12">
                    <label for="username">User Name</label>
                    <input type="text" name="username" id="username" class="form-control"
                        value="<?php echo $A_RES['username']; ?>">
                </div>
			</div>
			<div class="form-group">
                <div class="col-xs-10">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control"
                        value="<?php echo $A_RES['password'];?>">
                </div>
			</div>
			<div class="form-group">
                <div class="col-xs-12">
                    <label for="namalengkap">Nama Lengkap</label>
                    <input type="text" name="namalengkap" id="namalengkap" class="form-control"
                        value="<?php echo $A_RES['nama']; ?>">
                </div>
			</div>
			<div class="form-group">
                <div class="col-xs-12">
                    <label for="alamatemail">Email</label>
                    <input type="text" name="alamatemail" id="alamatemail" class="form-control"
                        value="<?php echo $A_RES['email']; ?>">
                </div>
			</div>
		</form>
        <?php
        }
    ?>
    </div>
    <div class="panel-footer">
        <button type="button" class="btn btn-default" onclick="history.back(1)"><i class="fa fa-sign-out" aria-hidden="true"></i> Keluar</button>
        <button type="submit" class="btn btn-primary btn-save" onclick="updateAkun()"><i class="fa fa-floppy-o" aria-hidden="true"></i> Update</button>
    </div>
</div>
