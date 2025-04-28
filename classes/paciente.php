<?php
require_once (__DIR__ . '/../config/db.php');

class Paciente {
    public $id_paciente;
    public $id_usuario;
    public $cedula;
    public $nombre;
    public $apellido;
    public $email;
    public $telefono;
    public $id_eps;
    public $fecha_nacimiento;
    public $genero;
    public $direccion;
    public $alergias;
    public $condiciones_medicas;
    
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Crea un registro básico del paciente después del registro de usuario
     * @param int $id_usuario ID del usuario asociado
     * @param int $id_eps ID de la EPS del paciente
     * @return bool True si la creación fue exitosa
     */
    public function crearPacienteBasico($id_usuario, $id_eps) {
        try {
            // Primero obtenemos los datos básicos del usuario
            $conn = $this->db->connect();
            $stmt = $conn->prepare("
                SELECT id_cedula as cedula, nombre, apellido, email 
                FROM usuarios 
                WHERE id_usuario = :id_usuario
            ");
            
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $usuario_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Crear el registro del paciente con datos básicos
                $stmt = $conn->prepare("
                    INSERT INTO pacientes (id_usuario, cedula, nombre, apellido, email, id_eps) 
                    VALUES (:id_usuario, :cedula, :nombre, :apellido, :email, :id_eps)
                ");
                
                $stmt->bindParam(':id_usuario', $id_usuario);
                $stmt->bindParam(':cedula', $usuario_data['cedula']);
                $stmt->bindParam(':nombre', $usuario_data['nombre']);
                $stmt->bindParam(':apellido', $usuario_data['apellido']);
                $stmt->bindParam(':email', $usuario_data['email']);
                $stmt->bindParam(':id_eps', $id_eps);
                
                return $stmt->execute();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en crearPacienteBasico: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene un paciente por su ID de usuario
     * @param int $id_usuario ID del usuario asociado
     * @return array|bool Datos del paciente o false si no existe
     */
    public function obtenerPorUsuario($id_usuario) {
        try {
            $conn = $this->db->connect();
            
            // Verificar primero si el usuario existe
            $stmt = $conn->prepare("SELECT 1 FROM usuarios WHERE id_usuario = :id_usuario LIMIT 1");
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return false;
            }
            
            // Obtener datos completos del paciente
            $stmt = $conn->prepare("
                SELECT p.*, u.*, e.nombre as nombre_eps
                FROM pacientes p
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                LEFT JOIN eps e ON u.id_eps = e.id_eps
                WHERE p.id_usuario = :id_usuario
                LIMIT 1
            ");
            
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Asignar datos al objeto
                $this->id_paciente = $paciente['id_paciente'];
                $this->id_usuario = $paciente['id_usuario'];
                $this->cedula = $paciente['cedula'];
                $this->nombre = $paciente['nombre'];
                $this->apellido = $paciente['apellido'];
                $this->email = $paciente['email'];
                $this->telefono = $paciente['telefono'];
                $this->id_eps = $paciente['id_eps'];
                $this->fecha_nacimiento = $paciente['fecha_nacimiento'];
                $this->genero = $paciente['genero'];
                $this->direccion = $paciente['direccion'];
                $this->alergias = $paciente['alergias'];
                $this->condiciones_medicas = $paciente['condiciones_medicas'];
                
                return $paciente;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en obtenerPorUsuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza los datos de información médica de un paciente en la tabla usuarios
     * @param int $id_usuario ID del usuario asociado
     * @param array $datos Datos a actualizar
     * @return bool True si la actualización fue exitosa
     */
    public function actualizarInfo($id_usuario, $datos) {
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("
                UPDATE usuarios SET 
                fecha_nacimiento = :fecha_nacimiento,
                genero = :genero,
                direccion = :direccion,
                telefono = :telefono,
                alergias = :alergias,
                condiciones_medicas = :condiciones_medicas
                WHERE id_usuario = :id_usuario
            ");
            
            $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);
            $stmt->bindParam(':genero', $datos['genero']);
            $stmt->bindParam(':direccion', $datos['direccion']);
            $stmt->bindParam(':telefono', $datos['telefono']);
            $stmt->bindParam(':alergias', $datos['alergias']);
            $stmt->bindParam(':condiciones_medicas', $datos['condiciones_medicas']);
            $stmt->bindParam(':id_usuario', $id_usuario);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en actualizarInfo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza los datos completos de un paciente
     * @return bool True si la actualización fue exitosa
     */
    public function updatePaciente() {
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("
                UPDATE usuarios SET 
                telefono = :telefono,
                id_eps = :id_eps,
                fecha_nacimiento = :fecha_nacimiento,
                genero = :genero,
                direccion = :direccion,
                alergias = :alergias,
                condiciones_medicas = :condiciones_medicas
                WHERE id_usuario = :id_usuario
            ");
            
            $stmt->bindParam(':telefono', $this->telefono);
            $stmt->bindParam(':id_eps', $this->id_eps);
            $stmt->bindParam(':fecha_nacimiento', $this->fecha_nacimiento);
            $stmt->bindParam(':genero', $this->genero);
            $stmt->bindParam(':direccion', $this->direccion);
            $stmt->bindParam(':alergias', $this->alergias);
            $stmt->bindParam(':condiciones_medicas', $this->condiciones_medicas);
            $stmt->bindParam(':id_usuario', $this->id_usuario);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en updatePaciente: " . $e->getMessage());
            return false;
        }
    }
}