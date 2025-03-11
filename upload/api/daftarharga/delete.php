<?php
  // Headers
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Methods: DELETE');
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

  // Set ID to update
  $daftarharga->kodebarang = $data->kodebarang;
  
  // Delete post
  if($daftarharga->delete()) {
    echo json_encode(
      array('message' => 'Daftar Harga deleted')
    );
  } else {
    echo json_encode(
      array('message' => 'Daftar Harga not deleted')
    );
  }
