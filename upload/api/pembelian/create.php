<?php
  // Headers
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Methods: POST');
  header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization,X-Requested-With');

  include_once '../../config/Database.php';
  include_once '../../models/Pembelian.php';

  // Instantiate DB & connect
  $database = new Database();
  $db = $database->connect();

  // Instantiate blog post object
  $pembelian = new Pembelian($db);

  // Get raw posted data
  $data = json_decode(file_get_contents("php://input"));

  $pembelian->nopembelian = $data->nopembelian;
  $pembelian->tanggal = $data->tanggal;
  $pembelian->jatuhtempo = $data->jatuhtempo;
  $pembelian->kodesupplier = $data->kodesupplier;
  $pembelian->namasupplier = $data->namasupplier;
  $pembelian->noreferensi= $data->noreferensi;
  $pembelian->tanggalreferensi = $data->tanggalreferensi;
  $pembelian->alamatsupplier = $data->alamatsupplier;
  $pembelian->nilaipembelian = $data->nilaipembelian;
  $pembelian->retur = $data->retur;
  $pembelian->tunai = $data->tunai;
  $pembelian->transfer = $data->transfer;
  $pembelian->giro = $data->giro;
  $pembelian->saldopembelian = $data->saldopembelian;
  $pembelian->userid = $data->userid;

  // Create Category
  if($pembelian->create()) {
    echo json_encode(
      array('message' => 'Tagihan Piutang Created')
    );
  } else {
    echo json_encode(
      array('message' => 'Tagihan Piutang Not Created')
    );
  }
