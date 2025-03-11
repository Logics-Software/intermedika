<?php
  class DetailPenjualan {
    // DB Stuff
    private $conn;
    private $table = 'detailpenjualan';

    // Properties
    public $nopenjualan;
    public $kodebarang;
    public $namabarang;
    public $satuan;
    public $jumlah;
    public $hargajual;
    public $discount;
    public $totalharga;
    public $nourut;
    
    // Constructor with DB
    public function __construct($db) {
      $this->conn = $db;
    }

  // Create Category
  public function create() {
    // Create Query
    $query = 'INSERT INTO ' .
      $this->table . ' SET
      nopenjualan = :nopenjualan, kodebarang = :kodebarang, namabarang = :namabarang, satuan = :satuan, jumlah = :jumlah, 
      hargajual = :hargajual, discount = :discount, totalharga = :totalharga, nourut = :nourut';

  // Prepare Statement
  $stmt = $this->conn->prepare($query);
  
  // Clean data
  $this->nopenjualan = htmlspecialchars(strip_tags($this->nopenjualan));
  $this->kodebarang = htmlspecialchars(strip_tags($this->kodebarang));
  $this->namabarang = htmlspecialchars(strip_tags($this->namabarang));
  $this->satuan = htmlspecialchars(strip_tags($this->satuan));
  $this->jumlah = htmlspecialchars(strip_tags($this->jumlah));
  $this->hargajual = htmlspecialchars(strip_tags($this->hargajual));
  $this->discount = htmlspecialchars(strip_tags($this->discount));
  $this->totalharga = htmlspecialchars(strip_tags($this->totalharga));
  $this->nourut = htmlspecialchars(strip_tags($this->nourut));
  
  // Bind data
  $stmt->bindParam(':nopenjualan', $this->nopenjualan);
  $stmt->bindParam(':kodebarang', $this->kodebarang);
  $stmt->bindParam(':namabarang', $this->namabarang);
  $stmt->bindParam(':satuan', $this->satuan);
  $stmt->bindParam(':jumlah', $this->jumlah);
  $stmt->bindParam(':hargajual', $this->hargajual);
  $stmt->bindParam(':discount', $this->discount);
  $stmt->bindParam(':totalharga', $this->totalharga);
  $stmt->bindParam(':nourut', $this->nourut);
  
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
    $query = 'DELETE FROM ' . $this->table  . ' WHERE nopenjualan = :nopenjualan';

    // Prepare Statement
    $stmt = $this->conn->prepare($query);

    // Clean data
    $this->nopenjualan = htmlspecialchars(strip_tags($this->nopenjualan));
    
    // Bind data
    $stmt->bindParam(':nopenjualan', $this->nopenjualan);
    
    // Execute query
    if($stmt->execute()) {
      return true;
    }

    // Print error if something goes wrong
    printf("Error: %stmt.\n", $stmt->error);

    return false;
    }
  }
