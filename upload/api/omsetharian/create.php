<?php 
  // Headers
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Methods: POST');
  header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

  include_once '../../config/Database.php';
  include_once '../../models/omsetharian.php';

  // Instantiate DB & connect
  $database = new Database();
  $db = $database->connect();

  // Instantiate blog post object
  $omsetharian = new OmsetHarian($db);

  // Get raw posted data
  $data = json_decode(file_get_contents("php://input"));

  $omsetharian->kodesales = $data->kodesales;
  $omsetharian->namasales = $data->namasales;
  $omsetharian->bulan = $data->bulan;
  $omsetharian->tahun = $data->tahun;
  $omsetharian->tanggal = $data->tanggal;
  $omsetharian->penjualan = $data->penjualan;
  $omsetharian->retur = $data->retur;
  $omsetharian->penjualanbersih = $data->penjualanbersih;
  $omsetharian->penerimaan = $data->penerimaan;
  $omsetharian->targetpenjualan = $data->targetpenjualan;
  $omsetharian->prosenpenjualan = $data->prosenpenjualan;
  $omsetharian->targetpenerimaan = $data->targetpenerimaan;
  $omsetharian->prosenpenerimaan = $data->prosenpenerimaan;
  $omsetharian->lembar = $data->lembar;

  // Create post
  if($omsetharian->create()) {
    echo json_encode(
      array('message' => 'Omset Penjualan Created')
    );
  } else {
    echo json_encode(
      array('message' => 'Omset Penjualan Not Created')
    );
  }

