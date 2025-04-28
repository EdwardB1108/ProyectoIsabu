<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Proyecto_Isabu/config/db.php';

class Cita {
    private $conn;
    private $table = 'citas';

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Método para crear una nueva cita
    public function crearCita($id_paciente, $id_medico, $id_especialidad, $fecha_hora, $motivo) {
        $query = 'INSERT INTO citas (id_paciente, id_medico, id_especialidad, fecha_hora, motivo, estado, fecha_creacion) 
                 VALUES (:id_paciente, :id_medico, :id_especialidad, :fecha_hora, :motivo, "pendiente", NOW())';
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_paciente', $id_paciente);
            $stmt->bindParam(':id_medico', $id_medico);
            $stmt->bindParam(':id_especialidad', $id_especialidad);
            $stmt->bindParam(':fecha_hora', $fecha_hora);
            $stmt->bindParam(':motivo', $motivo);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch(PDOException $e) {
            error_log("Error al crear cita: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerDetalleCita($id_cita, $id_paciente) {
        $query = 'SELECT c.*, 
                 CONCAT(u.nombre, " ", u.apellido) as nombre_medico,
                 e.nombre as nombre_especialidad
                 FROM citas c
                 JOIN medicos m ON c.id_medico = m.id_medico
                 JOIN usuarios u ON m.id_usuario = u.id_usuario
                 JOIN especialidades e ON c.id_especialidad = e.id_especialidad
                 WHERE c.id_cita = :id_cita 
                 AND c.id_paciente = :id_paciente';

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_cita', $id_cita);
            $stmt->bindParam(':id_paciente', $id_paciente);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error al obtener detalle de cita: " . $e->getMessage());
            return false;
        }
    }

    public function cancelarCita($id_cita, $id_paciente) {
        $query = 'UPDATE citas SET estado = "cancelada", fecha_actualizacion = NOW() 
                 WHERE id_cita = :id_cita AND id_paciente = :id_paciente
                 AND estado IN ("pendiente", "confirmada")';

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_cita', $id_cita);
            $stmt->bindParam(':id_paciente', $id_paciente);
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error al cancelar cita: " . $e->getMessage());
            return false;
        }
    }

    public function modificarCita($id_cita, $id_paciente, $id_medico, $id_especialidad, $fecha_hora, $motivo) {
        $query = 'UPDATE citas SET 
                 id_medico = :id_medico,
                 id_especialidad = :id_especialidad,
                 fecha_hora = :fecha_hora,
                 motivo = :motivo,
                 fecha_actualizacion = NOW()
                 WHERE id_cita = :id_cita AND id_paciente = :id_paciente';

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_medico', $id_medico);
            $stmt->bindParam(':id_especialidad', $id_especialidad);
            $stmt->bindParam(':fecha_hora', $fecha_hora);
            $stmt->bindParam(':motivo', $motivo);
            $stmt->bindParam(':id_cita', $id_cita);
            $stmt->bindParam(':id_paciente', $id_paciente);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error al modificar cita: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerHorariosDisponibles($id_medico, $fecha, $exclude_cita_id = null) {
        if (!strtotime($fecha)) {
            return $this->generarHorariosPredeterminados();
        }

        $diaSemanaNum = date('w', strtotime($fecha));
        $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $diaSemana = $dias[$diaSemanaNum];

        $horariosConfigurados = $this->obtenerHorariosConfigurados($id_medico, $diaSemana);
        
        if (empty($horariosConfigurados)) {
            $horariosConfigurados = [
                ['hora_inicio' => '08:00:00', 'hora_fin' => '12:00:00'],
                ['hora_inicio' => '14:00:00', 'hora_fin' => '18:00:00']
            ];
        }

        $horariosDisponibles = [];
        foreach ($horariosConfigurados as $horario) {
            $inicio = strtotime($horario['hora_inicio']);
            $fin = strtotime($horario['hora_fin']);
            
            for ($time = $inicio; $time < $fin; $time += 1800) {
                $horaSlot = date('H:i:s', $time);
                $fechaHora = $fecha . ' ' . $horaSlot;
                
                if ($this->verificarDisponibilidadSlot($id_medico, $fechaHora, $exclude_cita_id)) {
                    $horariosDisponibles[] = [
                        'hora_inicio' => $horaSlot,
                        'hora_fin' => date('H:i:s', $time + 1800)
                    ];
                }
            }
        }

        return empty($horariosDisponibles) ? $this->generarHorariosPredeterminados() : $horariosDisponibles;
    }

    private function generarHorariosPredeterminados() {
        $horariosDisponibles = [];
        $duracion = 1800; // 30 minutos
        
        // Horario mañana
        for ($time = strtotime('08:00:00'); $time < strtotime('12:00:00'); $time += $duracion) {
            $horariosDisponibles[] = [
                'hora_inicio' => date('H:i:s', $time),
                'hora_fin' => date('H:i:s', $time + $duracion)
            ];
        }
        
        // Horario tarde
        for ($time = strtotime('14:00:00'); $time < strtotime('18:00:00'); $time += $duracion) {
            $horariosDisponibles[] = [
                'hora_inicio' => date('H:i:s', $time),
                'hora_fin' => date('H:i:s', $time + $duracion)
            ];
        }
        
        return $horariosDisponibles;
    }

    private function obtenerHorariosConfigurados($id_medico, $diaSemana) {
        $query = 'SELECT hora_inicio, hora_fin 
                 FROM horarios_medicos 
                 WHERE id_medico = :id_medico 
                 AND dia_semana = :dia_semana
                 AND estado = "activo"';
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_medico', $id_medico);
            $stmt->bindParam(':dia_semana', $diaSemana);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error al obtener horarios configurados: " . $e->getMessage());
            return [];
        }
    }

    private function verificarDisponibilidadSlot($id_medico, $fecha_hora, $exclude_cita_id = null) {
        $query = 'SELECT id_cita FROM citas 
                 WHERE id_medico = :id_medico 
                 AND fecha_hora = :fecha_hora
                 AND estado IN ("pendiente", "confirmada")';
        
        if ($exclude_cita_id) {
            $query .= ' AND id_cita != :exclude_cita_id';
        }
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_medico', $id_medico);
            $stmt->bindParam(':fecha_hora', $fecha_hora);
            if ($exclude_cita_id) {
                $stmt->bindParam(':exclude_cita_id', $exclude_cita_id);
            }
            $stmt->execute();
            
            return $stmt->rowCount() === 0;
        } catch(PDOException $e) {
            error_log("Error al verificar disponibilidad: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerMedicosPorEspecialidad($id_especialidad) {
        $query = 'SELECT m.id_medico, u.nombre, u.apellido 
                 FROM medicos m
                 JOIN usuarios u ON m.id_usuario = u.id_usuario
                 WHERE m.id_especialidad = :id_especialidad
                 AND m.estado = "activo"';

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_especialidad', $id_especialidad);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error al obtener médicos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerEspecialidades() {
        $query = 'SELECT id_especialidad, nombre 
                 FROM especialidades 
                 WHERE estado = "activa"';

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error al obtener especialidades: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerCitasPorPaciente($id_paciente) {
        $query = 'SELECT c.*, 
                 CONCAT(u.nombre, " ", u.apellido) as nombre_medico,
                 e.nombre as nombre_especialidad,
                 DATE_FORMAT(c.fecha_hora, "%Y-%m-%d") as fecha,
                 DATE_FORMAT(c.fecha_hora, "%H:%i") as hora
                 FROM citas c
                 JOIN medicos m ON c.id_medico = m.id_medico
                 JOIN usuarios u ON m.id_usuario = u.id_usuario
                 JOIN especialidades e ON c.id_especialidad = e.id_especialidad
                 WHERE c.id_paciente = :id_paciente
                 ORDER BY c.fecha_hora DESC';

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_paciente', $id_paciente);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error al obtener citas: " . $e->getMessage());
            return [];
        }
    }
}