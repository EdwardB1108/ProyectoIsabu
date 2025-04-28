<?php
class CitasAdmin {
    private $db;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    // Obtener todas las citas con información de paciente y médico
    public function obtenerTodasLasCitas() {
        $query = "SELECT c.*, 
                  p.nombre AS nombre_paciente, p.apellido AS apellido_paciente,
                  m.nombre AS nombre_medico, m.apellido AS apellido_medico
                  FROM citas c
                  JOIN pacientes pa ON c.id_paciente = pa.id_paciente
                  JOIN usuarios p ON pa.id_usuario = p.id_usuario
                  JOIN medicos me ON c.id_medico = me.id_medico
                  JOIN usuarios m ON me.id_usuario = m.id_usuario
                  ORDER BY c.fecha_hora DESC";
        
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener datos para el gráfico de citas
    public function obtenerDatosGraficoCitas() {
        $query = "SELECT estado, COUNT(*) as cantidad 
                  FROM citas 
                  GROUP BY estado";
        
        $resultados = $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        $labels = [];
        $data = [];
        $colores = [];
        
        // Mapear estados a colores
        $coloresEstados = [
            'pendiente' => '#ffc107',
            'confirmada' => '#0d6efd',
            'completada' => '#198754',
            'cancelada' => '#dc3545',
            'no_asistio' => '#6c757d'
        ];
        
        foreach ($resultados as $fila) {
            $labels[] = ucfirst($fila['estado']);
            $data[] = $fila['cantidad'];
            $colores[] = $coloresEstados[$fila['estado']];
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'colores' => $colores
        ];
    }
    
    // Obtener lista de pacientes
    public function obtenerPacientes() {
        $query = "SELECT p.id_paciente, u.nombre, u.apellido 
                  FROM pacientes p
                  JOIN usuarios u ON p.id_usuario = u.id_usuario
                  ORDER BY u.nombre, u.apellido";
        
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener lista de médicos
    public function obtenerMedicos() {
        $query = "SELECT m.id_medico, u.nombre, u.apellido 
                  FROM medicos m
                  JOIN usuarios u ON m.id_usuario = u.id_usuario
                  ORDER BY u.nombre, u.apellido";
        
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Agendar una nueva cita
    public function agendarCita($datos) {
        $query = "INSERT INTO citas (id_paciente, id_medico, id_horario, fecha_hora, motivo, estado, notas)
                  VALUES (:id_paciente, :id_medico, NULL, :fecha_hora, :motivo, :estado, :notas)";
        
        $params = [
            ':id_paciente' => $datos['id_paciente'],
            ':id_medico' => $datos['id_medico'],
            ':fecha_hora' => $datos['fecha_hora'],
            ':motivo' => $datos['motivo'],
            ':estado' => $datos['estado'],
            ':notas' => $datos['notas'] ?? null
        ];
        
        return $this->db->query($query, $params);
    }
    
    // Modificar una cita existente
    public function modificarCita($datos) {
        $query = "UPDATE citas SET
                  id_paciente = :id_paciente,
                  id_medico = :id_medico,
                  fecha_hora = :fecha_hora,
                  motivo = :motivo,
                  estado = :estado,
                  notas = :notas
                  WHERE id_cita = :id_cita";
        
        $params = [
            ':id_paciente' => $datos['id_paciente'],
            ':id_medico' => $datos['id_medico'],
            ':fecha_hora' => $datos['fecha_hora'],
            ':motivo' => $datos['motivo'],
            ':estado' => $datos['estado'],
            ':notas' => $datos['notas'] ?? null,
            ':id_cita' => $datos['id_cita']
        ];
        
        return $this->db->query($query, $params);
    }
    
    // Eliminar una cita
    public function eliminarCita($id) {
        $query = "DELETE FROM citas WHERE id_cita = :id";
        return $this->db->query($query, [':id' => $id]);
    }
}
?>