<?php
  class Pembelian {
    // DB Stuff
    private $conn;
    private $table = 'pembelian';

    // Properties
    public $nopembelian;
    public $tanggal;
    public $jatuhtempo;
    public $kodesupplier;
    public $namasupplier;
    public $noreferensi;
    public $tanggalreferensi;
    public $alamatsupplier;
    public $nilaipembelian;
    public $retur;
    public $tunai;
    public $transfer;
    public $giro;
    public $saldopembelian;
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
      nopembelian = :nopembelian, tanggal = :tanggal, jatuhtempo = :jatuhtempo, kodesupplier = :kodesupplier, 
      noreferensi = :noreferensi, namasupplier = :namasupplier, tanggalreferensi = :tanggalreferensi, 
      alamatsupplier = :alamatsupplier, nilaipembelian = :nilaipembelian, retur = :retur, tunai = :tunai, 
      transfer = :transfer, giro = :giro, saldopembelian = :saldopembelian, userid = :userid';

  // Prepare Statement
  $stmt = $this->conn->prepare($query);

  // Clean data
  $this->nopembelian = htmlspecialchars(strip_tags($this->nopembelian));
  $this->tanggal = htmlspecialchars(strip_tags($this->tanggal));
  $this->jatuhtempo = htmlspecialchars(strip_tags($this->jatuhtempo));
  $this->kodesupplier = htmlspecialchars(strip_tags($this->kodesupplier));
  $this->namasupplier = htmlspecialchars(strip_tags($this->namasupplier));
  $this->noreferensi = htmlspecialchars(strip_tags($this->noreferensi));
  $this->tanggalreferensi = htmlspecialchars(strip_tags($this->tanggalreferensi));
  $this->alamatsupplier = htmlspecialchars(strip_tags($this->alamatsupplier));
  $this->nilaipembelian = htmlspecialchars(strip_tags($this->nilaipembelian));
  $this->retur = htmlspecialchars(strip_tags($this->retur));
  $this->tunai = htmlspecialchars(strip_tags($this->tunai));
  $this->transfer = htmlspecialchars(strip_tags($this->transfer));
  $this->giro = htmlspecialchars(strip_tags($this->giro));
  $this->saldopembelian = htmlspecialchars(strip_tags($this->saldopembelian));
  $this->userid = htmlspecialchars(strip_tags($this->userid));

  // Bind data
  $stmt->bindParam(':nopembelian', $this->nopembelian);
  $stmt->bindParam(':tanggal', $this->tanggal);
  $stmt->bindParam(':jatuhtempo', $this->jatuhtempo);
  $stmt->bindParam(':kodesupplier', $this->kodesupplier);
  $stmt->bindParam(':namasupplier', $this->namasupplier);
  $stmt->bindParam(':noreferensi', $this->noreferensi);
  $stmt->bindParam(':tanggalreferensi', $this->tanggalreferensi);
  $stmt->bindParam(':alamatsupplier', $this->alamatsupplier);
  $stmt->bindParam(':nilaipembelian', $this->nilaipembelian);
  $stmt->bindParam(':retur', $this->retur);
  $stmt->bindParam(':tunai', $this->tunai);
  $stmt->bindParam(':transfer', $this->transfer);
  $stmt->bindParam(':giro', $this->giro);
  $stmt->bindParam(':saldopembelian', $this->saldopembelian);
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
    $query = 'DELETE FROM ' . $this->table  . ' WHERE nopembelian = :nopembelian';

    // Prepare Statement
    $stmt = $this->conn->prepare($query);

    // Clean data
    $this->nopembelian = htmlspecialchars(strip_tags($this->nopembelian));
    
    // Bind data
    $stmt->bindParam(':nopembelian', $this->nopembelian);
    
    // Execute query
    if($stmt->execute()) {
      return true;
    }

    // Print error if something goes wrong
    printf("Error: $stmt.\n", $stmt->error);

    return false;
    }
  }
