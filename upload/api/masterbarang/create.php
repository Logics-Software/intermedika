<?php
  // Headers
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Methods: POST');
  header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization,X-Requested-With');

  include_once '../../config/Database.php';
  include_once '../../models/MasterBarang.php';

  // Instantiate DB & connect
  $database = new Database();
  $db = $database->connect();

  // Instantiate blog post object
  $masterbarang = new MasterBarang($db);

  // Get raw posted data
  $data = json_decode(file_get_contents("php://input"));

  $masterbarang->kodebarang = $data->kodebarang;
  $masterbarang->namabarang = $data->namabarang;
  $masterbarang->satuan = $data->satuan;
  $masterbarang->namapabrik = $data->namapabrik;
 
  // Create Category
  if($masterbarang->create()) {
    echo json_encode(
      array('message' => 'Master Barang Created')
    );
  } else {
    echo json_encode(
      array('message' => 'Master Barang Not Created')
    );
  }
