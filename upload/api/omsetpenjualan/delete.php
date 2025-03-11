<?php 
  // Headers
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Methods: DELETE');
  header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

  include_once '../../config/Database.php';
  include_once '../../models/OmsetPenjualan.php';

  // Instantiate DB & connect
  $database = new Database();
  $db = $database->connect();

  // Instantiate blog post object
  $omsetpenjualan = new OmsetPenjualan($db);

  // Get raw posted data
  $data = json_decode(file_get_contents("php://input"));

  // Set ID to update
  $omsetpenjualan->bulan = $data->bulan;
  $omsetpenjualan->tahun = $data->tahun;

  // Delete post
  if($omsetpenjualan->delete()) {
    echo json_encode(
      array('message' => 'Omset Penjualan Deleted')
    );
  } else {
    echo json_encode(
      array('message' => 'Omset Penjualan Not Deleted')
    );
  }

