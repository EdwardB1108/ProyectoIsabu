-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-04-2025 a las 21:33:48
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `chatboot_isabu`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autorizaciones_examenes`
--

CREATE TABLE `autorizaciones_examenes` (
  `id_autorizacion` int(11) NOT NULL,
  `id_historial` int(11) NOT NULL,
  `id_examen` int(11) NOT NULL,
  `fecha_autorizacion` date NOT NULL,
  `instrucciones` text DEFAULT NULL,
  `estado` enum('pendiente','autorizado','realizado','cancelado') DEFAULT 'pendiente',
  `resultados` text DEFAULT NULL,
  `fecha_realizacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_medico` int(11) NOT NULL,
  `id_especialidad` int(11) DEFAULT NULL,
  `id_horario` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `motivo` text DEFAULT NULL,
  `estado` enum('pendiente','confirmada','completada','cancelada','no_asistio') DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cita`, `id_paciente`, `id_medico`, `id_especialidad`, `id_horario`, `fecha_hora`, `motivo`, `estado`, `notas`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(4, 8, 1, 1, 1, '2025-04-10 08:00:00', 'Consulta general por dolor de cabeza', 'pendiente', 'Paciente refiere cefalea persistente', '2025-04-08 01:57:54', '2025-04-08 01:57:54'),
(5, 8, 2, 2, 6, '2025-04-11 09:30:00', 'Control cardiológico', 'confirmada', 'Paciente con antecedentes de hipertensión', '2025-04-08 01:57:54', '2025-04-08 01:57:54'),
(6, 8, 3, 3, 11, '2025-04-08 10:00:00', 'Evaluación de migrañas', 'completada', 'Paciente asistió, evaluación neurológica completa', '2025-04-08 01:57:54', '2025-04-08 01:57:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eps`
--

CREATE TABLE `eps` (
  `id_eps` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `estado` enum('activa','inactiva') DEFAULT 'activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eps`
--

INSERT INTO `eps` (`id_eps`, `nombre`, `estado`, `fecha_creacion`) VALUES
(1, 'Sura EPS', 'activa', '2025-04-04 05:01:23'),
(2, 'Nueva EPS', 'activa', '2025-04-04 05:01:23'),
(3, 'Sanitas EPS', 'activa', '2025-04-04 05:01:23'),
(4, 'Compensar EPS', 'activa', '2025-04-04 05:01:23'),
(5, 'Famisanar', 'activa', '2025-04-04 05:01:23'),
(6, 'Salud Total', 'activa', '2025-04-04 05:01:23'),
(7, 'Medimás', 'activa', '2025-04-04 05:01:23'),
(8, 'Coosalud', 'activa', '2025-04-04 05:01:23'),
(9, 'Comfenalco Valle', 'activa', '2025-04-04 05:01:23'),
(10, 'Aliansalud', 'activa', '2025-04-04 05:01:23'),
(12, 'Capital Salud', 'activa', '2025-04-04 05:01:23'),
(13, 'ISABU EPS', 'activa', '2025-04-04 05:01:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

CREATE TABLE `especialidades` (
  `id_especialidad` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('activa','inactiva') DEFAULT 'activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especialidades`
--

INSERT INTO `especialidades` (`id_especialidad`, `nombre`, `descripcion`, `estado`, `fecha_creacion`) VALUES
(1, 'Medicina General', 'Consulta de medicina general', 'activa', '2025-04-05 06:02:51'),
(2, 'Cardiología', 'Especialidad en tratamiento de enfermedades cardiovasculares', 'activa', '2025-04-05 06:02:51'),
(3, 'Neurología', 'Especialidad en tratamiento de enfermedades neurológicas', 'activa', '2025-04-05 06:02:51'),
(4, 'Pediatría', 'Atención médica para niños y adolescentes', 'activa', '2025-04-05 06:02:51'),
(5, 'Ginecología', 'Especialidad en salud femenina', 'activa', '2025-04-05 06:02:51'),
(6, 'Traumatología', 'Especialidad en lesiones del sistema musculoesquelético', 'activa', '2025-04-05 06:02:51'),
(8, 'Oftalmología', 'Especialidad en enfermedades de los ojos', 'activa', '2025-04-05 06:02:51'),
(9, 'Otorrinolaringología', 'Especialidad en oído, nariz y garganta', 'activa', '2025-04-05 06:02:51'),
(10, 'Psiquiatría', 'Especialidad en salud mental', 'activa', '2025-04-05 06:02:51'),
(15, 'Dermatología', 'Especialidad médica enfocada en enfermedades de la piel', 'activa', '2025-04-05 06:27:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenes_medicos`
--

CREATE TABLE `examenes_medicos` (
  `id_examen` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_medico`
--

CREATE TABLE `historial_medico` (
  `id_historial` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_medico` int(11) NOT NULL,
  `id_cita` int(11) DEFAULT NULL,
  `diagnostico` text DEFAULT NULL,
  `tratamiento` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_medicos`
--

CREATE TABLE `horarios_medicos` (
  `id_horario` int(11) NOT NULL,
  `id_medico` int(11) NOT NULL,
  `dia_semana` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `duracion_cita` int(11) DEFAULT 30 COMMENT 'Duración en minutos',
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios_medicos`
--

INSERT INTO horarios_medicos (id_medico, dia_semana, hora_inicio, hora_fin, duracion_cita, estado) VALUES
-- Médico 1
(1, 'lunes', '07:00:00', '17:00:00', 30, 'activo'),
(1, 'martes', '07:00:00', '17:00:00', 30, 'activo'),
(1, 'miercoles', '07:00:00', '17:00:00', 30, 'activo'),
(1, 'jueves', '07:00:00', '17:00:00', 30, 'activo'),
(1, 'viernes', '07:00:00', '17:00:00', 30, 'activo'),

-- Médico 2
(2, 'lunes', '07:00:00', '17:00:00', 30, 'activo'),
(2, 'martes', '07:00:00', '17:00:00', 30, 'activo'),
(2, 'miercoles', '07:00:00', '17:00:00', 30, 'activo'),
(2, 'jueves', '07:00:00', '17:00:00', 30, 'activo'),
(2, 'viernes', '07:00:00', '17:00:00', 30, 'activo'),

-- Médico 3
(3, 'lunes', '07:00:00', '17:00:00', 30, 'activo'),
(3, 'martes', '07:00:00', '17:00:00', 30, 'activo'),
(3, 'miercoles', '07:00:00', '17:00:00', 30, 'activo'),
(3, 'jueves', '07:00:00', '17:00:00', 30, 'activo'),
(3, 'viernes', '07:00:00', '17:00:00', 30, 'activo'),

-- Médico 4
(4, 'lunes', '07:00:00', '17:00:00', 30, 'activo'),
(4, 'martes', '07:00:00', '17:00:00', 30, 'activo'),
(4, 'miercoles', '07:00:00', '17:00:00', 30, 'activo'),
(4, 'jueves', '07:00:00', '17:00:00', 30, 'activo'),
(4, 'viernes', '07:00:00', '17:00:00', 30, 'activo'),

-- Médico 5
(5, 'lunes', '07:00:00', '17:00:00', 30, 'activo'),
(5, 'martes', '07:00:00', '17:00:00', 30, 'activo'),
(5, 'miercoles', '07:00:00', '17:00:00', 30, 'activo'),
(5, 'jueves', '07:00:00', '17:00:00', 30, 'activo'),
(5, 'viernes', '07:00:00', '17:00:00', 30, 'activo'),

-- Médico 6
(6, 'lunes', '07:00:00', '17:00:00', 30, 'activo'),
(6, 'martes', '07:00:00', '17:00:00', 30, 'activo'),
(6, 'miercoles', '07:00:00', '17:00:00', 30, 'activo'),
(6, 'jueves', '07:00:00', '17:00:00', 30, 'activo'),
(6, 'viernes', '07:00:00', '17:00:00', 30, 'activo'),

-- Médico 7
(7, 'lunes', '07:00:00', '17:00:00', 30, 'activo'),
(7, 'martes', '07:00:00', '17:00:00', 30, 'activo'),
(7, 'miercoles', '07:00:00', '17:00:00', 30, 'activo'),
(7, 'jueves', '07:00:00', '17:00:00', 30, 'activo'),
(7, 'viernes', '07:00:00', '17:00:00', 30, 'activo'),

-- Médico 8
(8, 'lunes', '07:00:00', '17:00:00', 30, 'activo'),
(8, 'martes', '07:00:00', '17:00:00', 30, 'activo'),
(8, 'miercoles', '07:00:00', '17:00:00', 30, 'activo'),
(8, 'jueves', '07:00:00', '17:00:00', 30, 'activo'),
(8, 'viernes', '07:00:00', '17:00:00', 30, 'activo'),

-- Médico 9
(9, 'lunes', '07:00:00', '17:00:00', 45, 'activo'),
(9, 'martes', '07:00:00', '17:00:00', 45, 'activo'),
(9, 'miercoles', '07:00:00', '17:00:00', 45, 'activo'),
(9, 'jueves', '07:00:00', '17:00:00', 45, 'activo'),
(9, 'viernes', '07:00:00', '17:00:00', 45, 'activo'),

-- Médico 10
(10, 'lunes', '07:00:00', '17:00:00', 30, 'activo'),
(10, 'martes', '07:00:00', '17:00:00', 30, 'activo'),
(10, 'miercoles', '07:00:00', '17:00:00', 30, 'activo'),
(10, 'jueves', '07:00:00', '17:00:00', 30, 'activo'),
(10, 'viernes', '07:00:00', '17:00:00', 30, 'activo');

-- Actualizar también la información de disponibilidad en la tabla médicos
UPDATE medicos SET horario_disponibilidad = 'Lunes a Viernes: 7:00 AM - 5:00 PM' WHERE id_medico > 0;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicamentos`
--

CREATE TABLE `medicamentos` (
  `id_medicamento` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicos`
--

CREATE TABLE `medicos` (
  `id_medico` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_especialidad` int(11) DEFAULT NULL,
  `licencia_medica` varchar(50) NOT NULL,
  `horario_disponibilidad` text DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `medicos`
--

INSERT INTO `medicos` (`id_medico`, `id_usuario`, `id_especialidad`, `licencia_medica`, `horario_disponibilidad`, `estado`) VALUES
(1, 3, 1, 'LIC-MG-2025-001', 'Lunes a Viernes: 7:00 AM - 3:00 PM', 'activo'),
(2, 4, 2, 'LIC-PD-2025-002', 'Lunes a Viernes: 8:00 AM - 4:00 PM', 'activo'),
(3, 5, 3, 'LIC-GN-2025-003', 'Lunes, Miércoles y Viernes: 7:00 AM - 3:00 PM', 'activo'),
(4, 6, 4, 'LIC-CR-2025-004', 'Martes y Jueves: 8:00 AM - 5:00 PM', 'activo'),
(5, 7, 5, 'LIC-DM-2025-005', 'Lunes, Miércoles y Viernes: 9:00 AM - 5:00 PM', 'activo'),
(6, 8, 6, 'LIC-TR-2025-006', 'Martes y Jueves: 7:00 AM - 4:00 PM', 'activo'),
(7, 9, 7, 'LIC-OF-2025-007', 'Lunes a Viernes: 7:00 AM - 1:00 PM', 'activo'),
(8, 10, 8, 'LIC-NR-2025-008', 'Lunes, Miércoles y Viernes: 8:00 AM - 4:00 PM', 'activo'),
(9, 11, 9, 'LIC-PS-2025-009', 'Martes y Jueves: 9:00 AM - 5:00 PM', 'activo'),
(10, 12, 10, 'LIC-OT-2025-010', 'Lunes a Viernes: 7:00 AM - 3:00 PM', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id_mensaje` int(11) NOT NULL,
  `id_remitente` int(11) NOT NULL,
  `id_destinatario` int(11) NOT NULL,
  `tipo` enum('mensaje','notificacion') DEFAULT 'mensaje',
  `asunto` varchar(255) DEFAULT NULL,
  `contenido` text NOT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id_paciente` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `id_eps` int(11) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('masculino','femenino','otro') DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `alergias` text DEFAULT NULL,
  `condiciones_medicas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id_paciente`, `id_usuario`, `cedula`, `nombre`, `apellido`, `email`, `telefono`, `id_eps`, `fecha_nacimiento`, `genero`, `direccion`, `alergias`, `condiciones_medicas`) VALUES
(7, 13, '1234567890', 'Juan', 'Paciente', 'juan.paciente@example.com', '3001234567', 1, '1980-01-15', 'masculino', NULL, NULL, NULL),
(8, 17, '10017116', 'Juan', 'Paciente', 'paciente.4933@example.com', '337587179', 1, '1980-01-15', 'masculino', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recetas_medicas`
--

CREATE TABLE `recetas_medicas` (
  `id_receta` int(11) NOT NULL,
  `id_historial` int(11) NOT NULL,
  `id_medicamento` int(11) NOT NULL,
  `dosis` varchar(100) NOT NULL,
  `frecuencia` varchar(100) NOT NULL,
  `duracion` varchar(100) NOT NULL,
  `instrucciones` text DEFAULT NULL,
  `fecha_prescripcion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recuperacion_password`
--

CREATE TABLE `recuperacion_password` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `utilizado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_cedula` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','paciente') NOT NULL,
  `id_eps` int(11) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_ultimo_login` timestamp NULL DEFAULT NULL,
  `token_recuperacion` varchar(255) DEFAULT NULL,
  `token_expiracion` timestamp NULL DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `alergias` text DEFAULT NULL,
  `condiciones_medicas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_cedula`, `nombre`, `apellido`, `email`, `telefono`, `password`, `rol`, `id_eps`, `estado`, `fecha_registro`, `fecha_ultimo_login`, `token_recuperacion`, `token_expiracion`, `fecha_nacimiento`, `genero`, `direccion`, `alergias`, `condiciones_medicas`) VALUES
(1, '1098621838', 'Sebastian', 'Herreño', 'Administrador@isabu.gov', NULL, '$2y$10$nuE386G.cOlWwaZ4YD7p4.FQ9P9T2E2IsBf6JYveoEkmo9uvDg41e', 'admin', 2, 'activo', '2025-04-04 05:10:16', '2025-04-09 00:04:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, '1098621839', 'Wilmer', 'Hernadez', 'helpdesk.isabu@gmail.com', '3209539753', '$2y$10$TpLBSU0XGpA/exCKP6ZSzOZSfW.dbFdDmT8nOUPjj5APl6./Am9aW', 'paciente', 8, 'activo', '2025-04-05 00:33:15', '2025-04-10 03:38:04', NULL, NULL, '0000-00-00', 'masculino', '', 'camarones', ''),
(3, '1001234567', 'Carlos', 'Rodríguez', 'carlos.rodriguez@isabu.gov', '3151234567', '$2y$10$nuE386G.cOlWwaZ4YD7p4.FQ9P9T2E2IsBf6JYveoEkmo9uvDg41e', 'admin', 13, 'activo', '2025-04-05 06:27:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, '1001234568', 'Ana', 'Gómez', 'ana.gomez@isabu.gov', '3151234568', '$2y$10$nuE386G.cOlWwaZ4YD7p4.FQ9P9T2E2IsBf6JYveoEkmo9uvDg41e', 'admin', 13, 'activo', '2025-04-05 06:27:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, '1001234569', 'Juan', 'Pérez', 'juan.perez@isabu.gov', '3151234569', '$2y$10$nuE386G.cOlWwaZ4YD7p4.FQ9P9T2E2IsBf6JYveoEkmo9uvDg41e', 'admin', 13, 'activo', '2025-04-05 06:27:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, '1001234570', 'María', 'López', 'maria.lopez@isabu.gov', '3151234570', '$2y$10$nuE386G.cOlWwaZ4YD7p4.FQ9P9T2E2IsBf6JYveoEkmo9uvDg41e', 'admin', 13, 'activo', '2025-04-05 06:27:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, '1001234571', 'Luis', 'Martínez', 'luis.martinez@isabu.gov', '3151234571', '$2y$10$nuE386G.cOlWwaZ4YD7p4.FQ9P9T2E2IsBf6JYveoEkmo9uvDg41e', 'admin', 13, 'activo', '2025-04-05 06:27:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, '1001234572', 'Patricia', 'Sánchez', 'patricia.sanchez@isabu.gov', '3151234572', '$2y$10$nuE386G.cOlWwaZ4YD7p4.FQ9P9T2E2IsBf6JYveoEkmo9uvDg41e', 'admin', 13, 'activo', '2025-04-05 06:27:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, '1001234573', 'Gabriel', 'Torres', 'gabriel.torres@isabu.gov', '3151234573', '$2y$10$nuE386G.cOlWwaZ4YD7p4.FQ9P9T2E2IsBf6JYveoEkmo9uvDg41e', 'admin', 13, 'activo', '2025-04-05 06:27:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, '1001234574', 'Valentina', 'Díaz', 'valentina.diaz@isabu.gov', '3151234574', '$2y$10$nuE386G.cOlWwaZ4YD7p4.FQ9P9T2E2IsBf6JYveoEkmo9uvDg41e', 'admin', 13, 'activo', '2025-04-05 06:27:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, '1001234575', 'Daniel', 'Herrera', 'daniel.herrera@isabu.gov', '3151234575', '$2y$10$nuE386G.cOlWwaZ4YD7p4.FQ9P9T2E2IsBf6JYveoEkmo9uvDg41e', 'admin', 13, 'activo', '2025-04-05 06:27:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, '1001234576', 'Carolina', 'Castro', 'carolina.castro@isabu.gov', '3151234576', '$2y$10$nuE386G.cOlWwaZ4YD7p4.FQ9P9T2E2IsBf6JYveoEkmo9uvDg41e', 'admin', 13, 'activo', '2025-04-05 06:27:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, '1234567890', 'Juan', 'Paciente', 'juan.paciente@example.com', '3001234567', '$2y$10$nuE386G.cOlWwaZ4YD7p4.FQ9P9T2E2IsBf6JYveoEkmo9uvDg41e', 'paciente', 1, 'activo', '2025-04-08 01:53:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, '10017116', 'Juan', 'Paciente', 'paciente.4933@example.com', '337587179', '$2y$10$nuE386G.cOlWwaZ4YD7p4.FQ9P9T2E2IsBf6JYveoEkmo9uvDg41e', 'paciente', 1, 'activo', '2025-04-08 01:57:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `autorizaciones_examenes`
--
ALTER TABLE `autorizaciones_examenes`
  ADD PRIMARY KEY (`id_autorizacion`),
  ADD KEY `id_historial` (`id_historial`),
  ADD KEY `id_examen` (`id_examen`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `id_horario` (`id_horario`),
  ADD KEY `idx_citas_paciente` (`id_paciente`),
  ADD KEY `idx_citas_medico` (`id_medico`),
  ADD KEY `idx_citas_fecha` (`fecha_hora`),
  ADD KEY `citas_especialidad_fk` (`id_especialidad`);

--
-- Indices de la tabla `eps`
--
ALTER TABLE `eps`
  ADD PRIMARY KEY (`id_eps`);

--
-- Indices de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id_especialidad`);

--
-- Indices de la tabla `examenes_medicos`
--
ALTER TABLE `examenes_medicos`
  ADD PRIMARY KEY (`id_examen`);

--
-- Indices de la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_medico` (`id_medico`),
  ADD KEY `id_cita` (`id_cita`);

--
-- Indices de la tabla `horarios_medicos`
--
ALTER TABLE `horarios_medicos`
  ADD PRIMARY KEY (`id_horario`),
  ADD KEY `id_medico` (`id_medico`);

--
-- Indices de la tabla `medicamentos`
--
ALTER TABLE `medicamentos`
  ADD PRIMARY KEY (`id_medicamento`);

--
-- Indices de la tabla `medicos`
--
ALTER TABLE `medicos`
  ADD PRIMARY KEY (`id_medico`),
  ADD UNIQUE KEY `licencia_medica` (`licencia_medica`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_especialidad` (`id_especialidad`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id_mensaje`),
  ADD KEY `id_remitente` (`id_remitente`),
  ADD KEY `id_destinatario` (`id_destinatario`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id_paciente`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_eps` (`id_eps`);

--
-- Indices de la tabla `recetas_medicas`
--
ALTER TABLE `recetas_medicas`
  ADD PRIMARY KEY (`id_receta`),
  ADD KEY `id_historial` (`id_historial`),
  ADD KEY `id_medicamento` (`id_medicamento`);

--
-- Indices de la tabla `recuperacion_password`
--
ALTER TABLE `recuperacion_password`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `cedula` (`id_cedula`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_usuarios_email` (`email`),
  ADD KEY `idx_usuarios_cedula` (`id_cedula`),
  ADD KEY `idx_usuarios_eps` (`id_eps`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `autorizaciones_examenes`
--
ALTER TABLE `autorizaciones_examenes`
  MODIFY `id_autorizacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `eps`
--
ALTER TABLE `eps`
  MODIFY `id_eps` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `id_especialidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `examenes_medicos`
--
ALTER TABLE `examenes_medicos`
  MODIFY `id_examen` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `horarios_medicos`
--
ALTER TABLE `horarios_medicos`
  MODIFY `id_horario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `medicamentos`
--
ALTER TABLE `medicamentos`
  MODIFY `id_medicamento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `medicos`
--
ALTER TABLE `medicos`
  MODIFY `id_medico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id_mensaje` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id_paciente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `recetas_medicas`
--
ALTER TABLE `recetas_medicas`
  MODIFY `id_receta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recuperacion_password`
--
ALTER TABLE `recuperacion_password`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `autorizaciones_examenes`
--
ALTER TABLE `autorizaciones_examenes`
  ADD CONSTRAINT `autorizaciones_examenes_ibfk_1` FOREIGN KEY (`id_historial`) REFERENCES `historial_medico` (`id_historial`),
  ADD CONSTRAINT `autorizaciones_examenes_ibfk_2` FOREIGN KEY (`id_examen`) REFERENCES `examenes_medicos` (`id_examen`);

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_especialidad_fk` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades` (`id_especialidad`),
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`),
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `medicos` (`id_medico`),
  ADD CONSTRAINT `citas_ibfk_3` FOREIGN KEY (`id_horario`) REFERENCES `horarios_medicos` (`id_horario`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_eps`) REFERENCES `eps` (`id_eps`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
