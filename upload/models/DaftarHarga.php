<?php
  class DaftarHarga {
    // DB Stuff
    private $conn;
    private $table = 'daftarharga';

    // Properties
    public $namabarang;
    public $satuan;
    public $namapabrik;
    public $namagolongan;
    public $stokakhir;
    public $hpp;
    public $hargajual;
    public $discount;
    public $kondisi;
    public $kodebarang;
    public $nopembelian;
    public $nomorbatch;
    public $expireddate;

    // Constructor with DB
    public function __construct($db) {
      $this->conn = $db;
    }

  // Create Category
  public function create() {
    // Create Query
    $query = 'INSERT INTO ' .
      $this->table . ' SET
      namabarang = :namabarang, satuan = :satuan, namapabrik = :namapabrik, namagolongan = :namagolongan, 
      stokakhir = :stokakhir, hpp = :hpp, hargajual = :hargajual, discount = :discount, kondisi = :kondisi, 
      kodebarang = :kodebarang, nopembelian = :nopembelian, nomorbatch = :nomorbatch, expireddate = :expireddate';

  // Prepare Statement
  $stmt = $this->conn->prepare($query);

  // Clean data
  $this->namabarang = htmlspecialchars(strip_tags($this->namabarang));
  $this->satuan = htmlspecialchars(strip_tags($this->satuan));
  $this->namapabrik = htmlspecialchars(strip_tags($this->namapabrik));
  $this->namagolongan = htmlspecialchars(strip_tags($this->namagolongan));
  $this->stokakhir = htmlspecialchars(strip_tags($this->stokakhir));
  $this->hpp = htmlspecialchars(strip_tags($this->hpp));
  $this->hargajual = htmlspecialchars(strip_tags($this->hargajual));
  $this->discount = htmlspecialchars(strip_tags($this->discount));
  $this->kondisi = htmlspecialchars(strip_tags($this->kondisi));
  $this->kodebarang = htmlspecialchars(strip_tags($this->kodebarang));
  $this->nopembelian = htmlspecialchars(strip_tags($this->nopembelian));
  $this->nomorbatch = htmlspecialchars(strip_tags($this->nomorbatch));
  $this->expireddate = htmlspecialchars(strip_tags($this->expireddate));

  // Bind data
  $stmt->bindParam(':namabarang', $this->namabarang);
  $stmt->bindParam(':satuan', $this->satuan);
  $stmt->bindParam(':namapabrik', $this->namapabrik);
  $stmt->bindParam(':namagolongan', $this->namagolongan);
  $stmt->bindParam(':stokakhir', $this->stokakhir);
  $stmt->bindParam(':hpp', $this->hpp);
  $stmt->bindParam(':hargajual', $this->hargajual);
  $stmt->bindParam(':discount', $this->discount);
  $stmt->bindParam(':kondisi', $this->kondisi);
  $stmt->bindParam(':kodebarang', $this->kodebarang);
  $stmt->bindParam(':nopembelian', $this->nopembelian);
  $stmt->bindParam(':nomorbatch', $this->nomorbatch);
  $stmt->bindParam(':expireddate', $this->expireddate);

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
