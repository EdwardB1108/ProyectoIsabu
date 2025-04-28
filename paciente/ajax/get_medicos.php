<?php
require_once '../../config/db.php';
require_once '../../classes/Cita.php';

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_especialidad'])) {
    try {
        $database = new Database();
        $conn = $database->connect();
        $cita = new Cita($conn);
        
        $id_especialidad = $_POST['id_especialidad'];
        $medicos = $cita->obtenerMedicosPorEspecialidad($id_especialidad);
        
        $output = '';
        if (!empty($medicos)) {
            foreach ($medicos as $medico) {
                $output .= "<option value='{$medico['id_medico']}'>";
                $output .= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']);
                $output .= "</option>";
            }
        } else {
            $output = "<option value=''>No hay médicos disponibles</option>";
        }
        
        echo $output;
    } catch (Exception $e) {
        error_log("Error en get_medicos.php: " . $e->getMessage());
        echo "<option value=''>Error al cargar médicos</option>";
    }
} else {
    echo "<option value=''>Seleccione una especialidad primero</option>";
}
?>