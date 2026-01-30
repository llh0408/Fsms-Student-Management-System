<?php
    class database{
        private $dbh;
        private $dbn;
        private $dbu;
        private $dbp;
        
        public function __construct(){
            $this->dbh = $_ENV['DB_HOST'];
            $this->dbn = $_ENV['DB_NAME'];
            $this->dbu = $_ENV['DB_USER'];
            $this->dbp = $_ENV['DB_PASS'];
        }
        
        public function connect(){
            try {
                $pdo = new PDO("mysql:host=" . $this->dbh . ";dbname=" . $this->dbn, $this->dbu, $this->dbp);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $pdo;
            } catch(PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        
        public static function getConnection(){
            $db = new self();
            return $db->connect();
        }
    }
?>
