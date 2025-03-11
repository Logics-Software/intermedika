<?php
  // Headers
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Methods: DELETE');
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

  // Set ID to update
  $penjualan->nopenjualan = $data->nopenjualan;
  
  // Delete post
  if($penjualan->delete()) {
    echo json_encode(
      array('message' => 'Tagihan Piutang deleted')
    );
  } else {
    echo json_encode(
      array('message' => 'Tagihan Piutang not deleted')
    );
  }
