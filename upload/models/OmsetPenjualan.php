<?php 
  class OmsetPenjualan {
    // DB stuff
    private $conn;
    private $table = 'omsetpenjualan';

    // Omset Penjualan Properties
    public $kodesales;
    public $namasales;
    public $bulan;
    public $tahun;
    public $penjualan;
    public $retur;
    public $penjualanbersih;
    public $penerimaan;
    public $cn;
    public $penerimaanbersih;
    public $targetpenjualan;
    public $prosenpenjualan;
    public $targetpenerimaan;
    public $prosenpenerimaan;
    public $lembar;

    // Constructor with DB
    public function __construct($db) {
      $this->conn = $db;
    }
    
    // Create Post
    public function create() {
          // Create query
          $query = 'INSERT INTO ' . 
          $this->table . ' SET 
          kodesales = :kodesales, namasales = :namasales, bulan = :bulan,  tahun = :tahun, penjualan = :penjualan, 
          retur = :retur, penjualanbersih = :penjualanbersih, penerimaan = :penerimaan, cn = :cn, 
          penerimaanbersih = :penerimaanbersih, targetpenjualan = :targetpenjualan, prosenpenjualan = :prosenpenjualan, 
          targetpenerimaan = :targetpenerimaan, prosenpenerimaan = :prosenpenerimaan, lembar = :lembar';

          // Prepare statement
          $stmt = $this->conn->prepare($query);

          // Clean data
          $this->kodesales = htmlspecialchars(strip_tags($this->kodesales));
          $this->namasales = htmlspecialchars(strip_tags($this->namasales));
          $this->bulan = htmlspecialchars(strip_tags($this->bulan));
          $this->tahun = htmlspecialchars(strip_tags($this->tahun));
          $this->penjualan = htmlspecialchars(strip_tags($this->penjualan));
          $this->retur = htmlspecialchars(strip_tags($this->retur));
          $this->penjualanbersih = htmlspecialchars(strip_tags($this->penjualanbersih));
          $this->penerimaan = htmlspecialchars(strip_tags($this->penerimaan));
          $this->cn = htmlspecialchars(strip_tags($this->cn));
          $this->penerimaanbersih = htmlspecialchars(strip_tags($this->penerimaanbersih));
          $this->targetpenjualan = htmlspecialchars(strip_tags($this->targetpenjualan));
          $this->prosenpenjualan = htmlspecialchars(strip_tags($this->prosenpenjualan));
          $this->targetpenerimaan = htmlspecialchars(strip_tags($this->targetpenerimaan));
          $this->prosenpenerimaan = htmlspecialchars(strip_tags($this->prosenpenerimaan));
          $this->lembar = htmlspecialchars(strip_tags($this->lembar));

          // Bind data
          $stmt->bindParam(':kodesales', $this->kodesales);
          $stmt->bindParam(':namasales', $this->namasales);
          $stmt->bindParam(':bulan', $this->bulan);
          $stmt->bindParam(':tahun', $this->tahun);
          $stmt->bindParam(':penjualan', $this->penjualan);
          $stmt->bindParam(':retur', $this->retur);
          $stmt->bindParam(':penjualanbersih', $this->penjualanbersih);
          $stmt->bindParam(':penerimaan', $this->penerimaan);
          $stmt->bindParam(':cn', $this->cn);
          $stmt->bindParam(':penerimaanbersih', $this->penerimaanbersih);
          $stmt->bindParam(':targetpenjualan', $this->targetpenjualan);
          $stmt->bindParam(':prosenpenjualan', $this->prosenpenjualan);
          $stmt->bindParam(':targetpenerimaan', $this->targetpenerimaan);
          $stmt->bindParam(':prosenpenerimaan', $this->prosenpenerimaan);
          $stmt->bindParam(':lembar', $this->lembar);

          // Execute query
          if($stmt->execute()) {
            return true;
      }

      // Print error if something goes wrong
      printf("Error: %stmt.\n", $stmt->error);

      return false;
    }

    // Delete Post
    public function delete() {
          // Create query
          $query = 'DELETE FROM ' . $this->table . ' WHERE bulan = :bulan ' . 'AND' . ' tahun =:tahun';

          // Prepare statement
          $stmt = $this->conn->prepare($query);

          // Clean data
          $this->bulan = htmlspecialchars(strip_tags($this->bulan));
          $this->tahun = htmlspecialchars(strip_tags($this->tahun));

          // Bind data
          $stmt->bindParam(':bulan', $this->bulan);
          $stmt->bindParam(':tahun', $this->tahun);

          // Execute query
          if($stmt->execute()) {
            return true;
          }

          // Print error if something goes wrong
          printf("Error: %stmt.\n", $stmt->error);

          return false;
    } 
  }