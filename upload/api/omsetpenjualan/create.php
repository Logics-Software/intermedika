<?php 
  // Headers
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Methods: POST');
  header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

  include_once '../../config/Database.php';
  include_once '../../models/OmsetPenjualan.php';

  // Instantiate DB & connect
  $database = new Database();
  $db = $database->connect();

  // Instantiate blog post object
  $omsetpenjualan = new OmsetPenjualan($db);

  // Get raw posted data
  $data = json_decode(file_get_contents("php://input"));

  $omsetpenjualan->kodesales = $data->kodesales;
  $omsetpenjualan->namasales = $data->namasales;
  $omsetpenjualan->bulan = $data->bulan;
  $omsetpenjualan->tahun = $data->tahun;
  $omsetpenjualan->penjualan = $data->penjualan;
  $omsetpenjualan->retur = $data->retur;
  $omsetpenjualan->penjualanbersih = $data->penjualanbersih;
  $omsetpenjualan->penerimaan = $data->penerimaan;
  $omsetpenjualan->cn = $data->cn;
  $omsetpenjualan->penerimaanbersih = $data->penerimaanbersih;
  $omsetpenjualan->targetpenjualan = $data->targetpenjualan;
  $omsetpenjualan->prosenpenjualan = $data->prosenpenjualan;
  $omsetpenjualan->targetpenerimaan = $data->targetpenerimaan;
  $omsetpenjualan->prosenpenerimaan = $data->prosenpenerimaan;
  $omsetpenjualan->lembar = $data->lembar;

  // Create post
  if($omsetpenjualan->create()) {
    echo json_encode(
      array('message' => 'Omset Penjualan Created')
    );
  } else {
    echo json_encode(
      array('message' => 'Omset Penjualan Not Created')
    );
  }

