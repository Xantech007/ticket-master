<?php
class Database {
    private $host = "sql106.infinityfree.com";
    private $dbname = "if0_41489345_event";
    private $username = "if0_41489345";
    private $password = "1V1DyYiXWU4yVzp";

    public function connect(){
        try {
            $pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            return $pdo;
        } catch(PDOException $e){
            die("Database connection failed: " . $e->getMessage());
        }
    }
}
?>
