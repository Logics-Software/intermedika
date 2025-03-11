<?php
  class Penjualan {
    // DB Stuff
    private $conn;
    private $table = 'penjualan';

    // Properties
    public $nopenjualan;
    public $tanggal;
    public $jatuhtempo;
    public $kodecustomer;
    public $namacustomer;
    public $kodesalesman;
    public $namasalesman;
    public $alamatcustomer;
    public $nilaipenjualan;
    public $retur;
    public $tunai;
    public $transfer;
    public $giro;
    public $saldopenjualan;
    public $userid;

    // Constructor with DB
    public function __construct($db) {
      $this->conn = $db;
    }

  // Create Category
  public function create() {
    // Create Query
    $query = 'INSERT INTO ' .
      $this->table . ' SET
      nopenjualan = :nopenjualan, tanggal = :tanggal, jatuhtempo = :jatuhtempo, kodecustomer = :kodecustomer, 
      kodesalesman = :kodesalesman, namacustomer = :namacustomer, namasalesman = :namasalesman, 
      alamatcustomer = :alamatcustomer, nilaipenjualan = :nilaipenjualan, retur = :retur, tunai = :tunai, 
      transfer = :transfer, giro = :giro, saldopenjualan = :saldopenjualan, userid = :userid';

  // Prepare Statement
  $stmt = $this->conn->prepare($query);

  // Clean data
  $this->nopenjualan = htmlspecialchars(strip_tags($this->nopenjualan));
  $this->tanggal = htmlspecialchars(strip_tags($this->tanggal));
  $this->jatuhtempo = htmlspecialchars(strip_tags($this->jatuhtempo));
  $this->kodecustomer = htmlspecialchars(strip_tags($this->kodecustomer));
  $this->namacustomer = htmlspecialchars(strip_tags($this->namacustomer));
  $this->kodesalesman = htmlspecialchars(strip_tags($this->kodesalesman));
  $this->namasalesman = htmlspecialchars(strip_tags($this->namasalesman));
  $this->alamatcustomer = htmlspecialchars(strip_tags($this->alamatcustomer));
  $this->nilaipenjualan = htmlspecialchars(strip_tags($this->nilaipenjualan));
  $this->retur = htmlspecialchars(strip_tags($this->retur));
  $this->tunai = htmlspecialchars(strip_tags($this->tunai));
  $this->transfer = htmlspecialchars(strip_tags($this->transfer));
  $this->giro = htmlspecialchars(strip_tags($this->giro));
  $this->saldopenjualan = htmlspecialchars(strip_tags($this->saldopenjualan));
  $this->userid = htmlspecialchars(strip_tags($this->userid));

  // Bind data
  $stmt->bindParam(':nopenjualan', $this->nopenjualan);
  $stmt->bindParam(':tanggal', $this->tanggal);
  $stmt->bindParam(':jatuhtempo', $this->jatuhtempo);
  $stmt->bindParam(':kodecustomer', $this->kodecustomer);
  $stmt->bindParam(':namacustomer', $this->namacustomer);
  $stmt->bindParam(':kodesalesman', $this->kodesalesman);
  $stmt->bindParam(':namasalesman', $this->namasalesman);
  $stmt->bindParam(':alamatcustomer', $this->alamatcustomer);
  $stmt->bindParam(':nilaipenjualan', $this->nilaipenjualan);
  $stmt->bindParam(':retur', $this->retur);
  $stmt->bindParam(':tunai', $this->tunai);
  $stmt->bindParam(':transfer', $this->transfer);
  $stmt->bindParam(':giro', $this->giro);
  $stmt->bindParam(':saldopenjualan', $this->saldopenjualan);
  $stmt->bindParam(':userid', $this->userid);

  // Execute query
  if($stmt->execute()) {
    return true;
  }

  // Print error if something goes wrong
  printf("Error: $stmt.\n", $stmt->error);

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
    printf("Error: $stmt.\n", $stmt->error);

    return false;
    }
  }
