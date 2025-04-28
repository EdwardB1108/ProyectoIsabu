<?php
require_once './config/db.php';

class EPS {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Obtiene todas las EPS activas
     * @return array Lista de EPS activas
     */
    public function getActiveEPS() {
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("SELECT id_eps, nombre FROM eps WHERE estado = 'activa' ORDER BY nombre");
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtiene una EPS por su ID
     * @param int $id_eps ID de la EPS
     * @return array|bool Datos de la EPS o false si no existe
     */
    public function getEPSById($id_eps) {
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("SELECT id_eps, nombre, estado FROM eps WHERE id_eps = :id_eps");
            $stmt->bindParam(':id_eps', $id_eps);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
}