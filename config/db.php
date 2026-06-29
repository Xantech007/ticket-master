<?php

class Database {

    private $host = "sql207.infinityfree.com";
    private $dbname = "if0_42273705_ticket2";
    private $username = "if0_42273705";
    private $password = "MWJvmCfpNDKo";

    public function connect() {

        return new PDO(
            "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
            $this->username,
            $this->password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
}

$database = new Database();
$pdo = $database->connect();
?>
