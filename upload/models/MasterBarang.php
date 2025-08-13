<?php
  class MasterBarang {
    // DB Stuff
    private $conn;
    private $table = 'masterbarang';

    // Properties
    public $kodebarang;
    public $namabarang;
    public $satuan;
    public $namapabrik;

    // Constructor with DB
    public function __construct($db) {
      $this->conn = $db;
    }

  // Create Category
  public function create() {
    // Create Query
    $query = 'INSERT INTO ' .
      $this->table . ' SET
      kodebarang = :kodebarang, namabarang = :namabarang, satuan = :satuan, namapabrik = :namapabrik';

  // Prepare Statement
  $stmt = $this->conn->prepare($query);

  // Clean data
  $this->kodebarang = htmlspecialchars(strip_tags($this->kodebarang));
  $this->namabarang = htmlspecialchars(strip_tags($this->namabarang));
  $this->satuan = htmlspecialchars(strip_tags($this->satuan));
  $this->namapabrik = htmlspecialchars(strip_tags($this->namapabrik));

  // Bind data
  $stmt->bindParam(':kodebarang', $this->kodebarang);
  $stmt->bindParam(':namabarang', $this->namabarang);
  $stmt->bindParam(':satuan', $this->satuan);
  $stmt->bindParam(':namapabrik', $this->namapabrik);

  // Execute query
  if($stmt->execute()) {
    return true;
  }

  // Print error if something goes wrong
  printf("Error: %stmt.\n", $stmt->error);

  return false;
  }

  // Delete Category
  public function delete() {
    // Create query
    $query = 'DELETE FROM ' . $this->table  . ' WHERE kodebarang = :kodebarang';

    // Prepare Statement
    $stmt = $this->conn->prepare($query);

    // Clean data
    $this->kodebarang = htmlspecialchars(strip_tags($this->kodebarang));
    
    // Bind data
    $stmt->bindParam(':kodebarang', $this->kodebarang);
    
    // Execute query
    if($stmt->execute()) {
      return true;
    }

    // Print error if something goes wrong
    printf("Error: %stmt.\n", $stmt->error);

    return false;
    }
  }
