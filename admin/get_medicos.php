<?php
require_once '../config/db.php';

// Verificar que se recibió el parámetro de especialidad
if (!isset($_GET['especialidad']) || empty($_GET['especialidad'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Especialidad no especificada']);
    exit;
}

$id_especialidad = filter_input(INPUT_GET, 'especialidad', FILTER_VALIDATE_INT);

if (!$id_especialidad) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID de especialidad inválido']);
    exit;
}

// Conectar a la base de datos
$database = new Database();
$conexion = $database->connect();

// Consultar médicos por especialidad
$sql = "SELECT m.id_medico, u.nombre, u.apellido, e.nombre as especialidad
        FROM medicos m
        JOIN usuarios u ON m.id_usuario = u.id_usuario
        JOIN especialidades e ON m.id_especialidad = e.id_especialidad
        WHERE m.id_especialidad = ? 
        AND m.estado = 'activo'
        ORDER BY u.nombre ASC";

$stmt = $conexion->prepare($sql);
$stmt->execute([$id_especialidad]);
$medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Devolver resultado en formato JSON
header('Content-Type: application/json');
echo json_encode($medicos);
?>