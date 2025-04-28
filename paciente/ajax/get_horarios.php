<?php
require_once '../../config/db.php';
require_once '../../classes/Cita.php';

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_medico']) && isset($_POST['fecha'])) {
    try {
        $database = new Database();
        $conn = $database->connect();
        $cita = new Cita($conn);
        
        $id_medico = $_POST['id_medico'];
        $fecha = $_POST['fecha'];
        $id_cita = $_POST['id_cita'] ?? null;
        
        $horariosDisponibles = $cita->obtenerHorariosDisponibles($id_medico, $fecha, $id_cita);
        
        $output = '';
        
        if (!empty($horariosDisponibles)) {
            foreach ($horariosDisponibles as $horario) {
                $horaSlot = $horario['hora_inicio'];
                $horaFormateada = date('h:i A', strtotime($horaSlot));
                $output .= "<div class='time-slot' data-horario='{$horaSlot}'>{$horaFormateada}</div>";
            }
        } else {
            $output = '<div class="no-slots">No hay horarios disponibles para esta fecha</div>';
        }
        
        echo $output;
    } catch (Exception $e) {
        error_log("Error en get_horarios.php: " . $e->getMessage());
        echo '<div class="no-slots">Error al cargar horarios</div>';
    }
} else {
    echo '<div class="no-slots">Datos incompletos</div>';
}
?>