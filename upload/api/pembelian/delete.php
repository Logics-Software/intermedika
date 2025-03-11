<?php
  // Headers
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Methods: DELETE');
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

  // Set ID to update
  $pembelian->nopembelian = $data->nopembelian;
  
  // Delete post
  if($pembelian->delete()) {
    echo json_encode(
      array('message' => 'Tagihan Hutang deleted')
    );
  } else {
    echo json_encode(
      array('message' => 'Tagihan Hutang not deleted')
    );
  }
