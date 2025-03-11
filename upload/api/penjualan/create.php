<?php
  // Headers
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Methods: POST');
  header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization,X-Requested-With');

  include_once '../../config/Database.php';
  include_once '../../models/Penjualan.php';

  // Instantiate DB & connect
  $database = new Database();
  $db = $database->connect();

  // Instantiate blog post object
  $penjualan = new Penjualan($db);

  // Get raw posted data
  $data = json_decode(file_get_contents("php://input"));

  $penjualan->nopenjualan = $data->nopenjualan;
  $penjualan->tanggal = $data->tanggal;
  $penjualan->jatuhtempo = $data->jatuhtempo;
  $penjualan->kodecustomer = $data->kodecustomer;
  $penjualan->namacustomer = $data->namacustomer;
  $penjualan->kodesalesman= $data->kodesalesman;
  $penjualan->namasalesman = $data->namasalesman;
  $penjualan->alamatcustomer = $data->alamatcustomer;
  $penjualan->nilaipenjualan = $data->nilaipenjualan;
  $penjualan->retur = $data->retur;
  $penjualan->tunai = $data->tunai;
  $penjualan->transfer = $data->transfer;
  $penjualan->giro = $data->giro;
  $penjualan->saldopenjualan = $data->saldopenjualan;
  $penjualan->userid = $data->userid;

  // Create Category
  if($penjualan->create()) {
    echo json_encode(
      array('message' => 'Tagihan Piutang Created')
    );
  } else {
    echo json_encode(
      array('message' => 'Tagihan Piutang Not Created')
    );
  }
