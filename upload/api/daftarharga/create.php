<?php
  // Headers
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Methods: POST');
  header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization,X-Requested-With');

  include_once '../../config/Database.php';
  include_once '../../models/DaftarHarga.php';

  // Instantiate DB & connect
  $database = new Database();
  $db = $database->connect();

  // Instantiate blog post object
  $daftarharga = new DaftarHarga($db);

  // Get raw posted data
  $data = json_decode(file_get_contents("php://input"));

  $daftarharga->namabarang = $data->namabarang;
  $daftarharga->satuan = $data->satuan;
  $daftarharga->namapabrik = $data->namapabrik;
  $daftarharga->namagolongan = $data->namagolongan;
  $daftarharga->stokakhir = $data->stokakhir;
  $daftarharga->hpp = $data->hpp;
  $daftarharga->hargajual = $data->hargajual;
  $daftarharga->discount = $data->discount;
  $daftarharga->kondisi = $data->kondisi;
  $daftarharga->kodebarang = $data->kodebarang;
  $daftarharga->nopembelian = $data->nopembelian;
  $daftarharga->nomorbatch = $data->nomorbatch;
  $daftarharga->expireddate = $data->expireddate;
 
  // Create Category
  if($daftarharga->create()) {
    echo json_encode(
      array('message' => 'Daftar Harga Created')
    );
  } else {
    echo json_encode(
      array('message' => 'Daftar Harga Not Created')
    );
  }
