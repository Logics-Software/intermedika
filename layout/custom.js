// ------- Hide Panel
$(document).ready(function(){
	$("#hideme").hide();
	$("#handme").click(function(){
		$("#hideme").slideToggle();
	});

    $('.collapsible + .rest').hide();
    console.log($('.collapsible + tr .rest'));
    $('.check-details').click(function(e) {
        $(this).parents('.collapsible').next('.rest').slideToggle('fast');
        $(this).parent().toggleClass('active');
        e.preventDefault();
    });
});

// ------- User (Sales/Administrator)
function updateAkun() {
	var userid = $("#userid").val();
	var username = $("#username").val();
	var password = $("#password").val();
	var nama = $("#namalengkap").val();
	var email = $("#alamatemail").val();

	$.post("ajax.php?ajaxpage=updateakun", {
	       userid: userid,
	       username: username,
	       password: password,
		   nama: nama,
		   email: email
	   	},
	   	function (data, status) {
			alert('Data berhasil diperbarui !!');
			location.reload();
	   	}
	);
}

function updateUser() {
	var userid = $("#userid").val();
	var username = $("#username").val();
	var password = $("#password").val();
	var nama = $("#namalengkap").val();
	var email = $("#alamatemail").val();
	var privilege = $("#privilegeedit").val();
	var kodesales = $("#kodesalesedit").val();
	var status = $("#statusedit").val();

	$.post("ajax.php?ajaxpage=updateuser", {
	       userid: userid,
	       username: username,
	       password: password,
		   nama: nama,
		   email: email,
		   privilege: privilege,
		   kodesales:kodesales,
		   status: status
	   	},
	   	function (data, status) {
			alert('Data berhasil diperbarui !!');
			location.reload();
	   	}
	);
}

function editUser(id) {
    $('#edit-user form')[0].reset();
	$.ajax({
		url: "ajax.php?ajaxpage=lihatuser&userid="+id,
		type: "GET",
		dataType: "JSON",
		success: function(data){
			$("#userid").val(data.userid).attr('readonly', true);
			$("#username").val(data.username);
			$("#password").val(data.password);
			$("#namalengkap").val(data.nama);
			$("#alamatemail").val(data.email);
			$("#privilegeedit").val(data.privilege);
			$("#kodesalesedit").val(data.kodesales);
			$("#statusedit").val(data.status);
		},
		error: function(){
			alert("Tidak dapat menampilkan Data!");
		}
	});
}

function hapusUser(id) {
    if (confirm("Apakah yakin akan menghapus Data?")) {
        $.ajax({
            url: "ajax.php?ajaxpage=hapususer&userid="+id,
            type: "POST",
            data: {'_method' : 'DELETE', '_token' : $('meta[name=csrf-token]').attr('content')},
            success: function (data) {
				location.reload();
            },
            error: function () {
                alert("Tidak dapat menghapus Data!");
            }
        });
    }
}