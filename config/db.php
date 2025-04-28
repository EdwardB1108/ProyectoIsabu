<?php
class Database {
    private $host = 'localhost';
    private $port = '3307'; // Según tu SQL dump
    private $db_name = 'chatboot_isabu';
    private $username = 'root'; // Cambia según tu configuración
    private $password = ''; // Cambia según tu configuración
    private $conn;
    
    public function connect() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . 
                ';port=' . $this->port . 
                ';dbname=' . $this->db_name,
                $this->username,
                $this->password,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo 'Error de conexión: ' . $e->getMessage();
        }
        
        return $this->conn;
    }
}
?>