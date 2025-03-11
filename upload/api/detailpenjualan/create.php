<?php
  // Headers
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Methods: POST');
  header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization,X-Requested-With');

  include_once '../../config/Database.php';
  include_once '../../models/DetailPenjualan.php';

  // Instantiate DB & connect
  $database = new Database();
  $db = $database->connect();

  // Instantiate blog post object
  $detailpenjualan = new DetailPenjualan($db);

  // Get raw posted data
  $data = json_decode(file_get_contents("php://input"));

  $detailpenjualan->nopenjualan = $data->nopenjualan;
  $detailpenjualan->kodebarang = $data->kodebarang;
  $detailpenjualan->namabarang = $data->namabarang;
  $detailpenjualan->satuan = $data->satuan;
  $detailpenjualan->jumlah = $data->jumlah;
  $detailpenjualan->hargajual = $data->hargajual;
  $detailpenjualan->discount= $data->discount;
  $detailpenjualan->totalharga = $data->totalharga;
  $detailpenjualan->nourut = $data->nourut;

  // Create Category
  if($detailpenjualan->create()) {
    echo json_encode(
      array('message' => 'Detail Penjualan Created')
    );
  } else {
    echo json_encode(
      array('message' => 'Detail Penjualan Not Created')
    );
  }
