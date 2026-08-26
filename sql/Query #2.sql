CREATE TABLE `roles` (
  `id_rol` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255) UNIQUE NOT NULL COMMENT 'CLIENTE / BARBERO / ADMINISTRADOR',
  `descripcion` text
);

CREATE TABLE `usuarios` (
  `id_usuario` int PRIMARY KEY AUTO_INCREMENT,
  `id_rol` int NOT NULL,
  `nombre` varchar(255),
  `email` varchar(255) UNIQUE NOT NULL,
  `telefono` varchar(255),
  `password` varchar(255) NOT NULL,
  `estado` ENUM ('ACTIVO', 'INACTIVO', 'SUSPENDIDO') DEFAULT 'ACTIVO',
  `fecha_registro` timestamp DEFAULT (now()),
  `ultimo_acceso` timestamp,
  `numero_identificacion` varchar(255) COMMENT 'Solo aplica si id_rol = CLIENTE',
  `documento` varchar(255) COMMENT 'Aplica si id_rol = BARBERO o ADMINISTRADOR',
  `especialidad` varchar(255) COMMENT 'Solo aplica si id_rol = BARBERO',
  `fecha_ingreso` date COMMENT 'Solo aplica si id_rol = BARBERO',
  `cargo` varchar(255) COMMENT 'Solo aplica si id_rol = ADMINISTRADOR',
  `metodo_2fa` varchar(255) COMMENT 'Solo aplica si id_rol = ADMINISTRADOR (obligatorio - RNF-AD-01)'
);

CREATE TABLE `categorias_servicio` (
  `id_categoria` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `estado` ENUM ('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO'
);

CREATE TABLE `servicios` (
  `id_servicio` int PRIMARY KEY AUTO_INCREMENT,
  `id_categoria` int,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `precio` decimal,
  `duracion_min` int,
  `estado` ENUM ('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
  `fecha_creacion` timestamp DEFAULT (now())
);

CREATE TABLE `citas` (
  `id_cita` int PRIMARY KEY AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `id_barbero` int NOT NULL,
  `id_servicio` int NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `estado` ENUM ('PENDIENTE', 'ACEPTADA', 'CANCELADA', 'REPROGRAMADA', 'COMPLETADA') DEFAULT 'PENDIENTE',
  `fecha_creacion` timestamp DEFAULT (now()),
  `fecha_actualizacion` timestamp,
  `observacion` text,
  `motivo_cancelacion` text
);

CREATE TABLE `disponibilidad` (
  `id_disponibilidad` int PRIMARY KEY AUTO_INCREMENT,
  `id_barbero` int NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `disponible` boolean DEFAULT true
);

CREATE TABLE `turnos` (
  `id_turno` int PRIMARY KEY AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `id_cita` int UNIQUE,
  `fecha` date NOT NULL,
  `posicion` int,
  `estado` ENUM ('EN_ESPERA', 'EN_ATENCION', 'FINALIZADO') DEFAULT 'EN_ESPERA',
  `fecha_actualizacion` timestamp
);

CREATE TABLE `reprogramaciones` (
  `id_reprogramacion` int PRIMARY KEY AUTO_INCREMENT,
  `id_cita` int NOT NULL,
  `nueva_fecha` date NOT NULL,
  `nueva_hora` time NOT NULL,
  `estado` ENUM ('PENDIENTE', 'CONFIRMADA', 'CANCELADA', 'EXPIRADA') DEFAULT 'PENDIENTE',
  `fecha_solicitud` timestamp DEFAULT (now()),
  `fecha_limite_respuesta` timestamp COMMENT 'fecha_solicitud + 10 minutos (RF-BR-05 / HU-BR-05)'
);

CREATE TABLE `notificaciones` (
  `id_notificacion` int PRIMARY KEY AUTO_INCREMENT,
  `id_cita` int,
  `id_usuario_destino` int NOT NULL,
  `tipo` ENUM ('CONFIRMACION', 'CANCELACION', 'REPROGRAMACION', 'RECORDATORIO', 'OTRO') NOT NULL,
  `asunto` varchar(255),
  `mensaje` text,
  `medio` varchar(255) DEFAULT 'CORREO',
  `estado` ENUM ('ENVIADA', 'FALLIDA', 'PENDIENTE') DEFAULT 'PENDIENTE',
  `fecha_envio` timestamp,
  `fecha_lectura` timestamp
);

CREATE TABLE `reportes` (
  `id_reporte` int PRIMARY KEY AUTO_INCREMENT,
  `id_generador` int NOT NULL COMMENT 'Barbero o Administrador',
  `tipo_reporte` varchar(255),
  `fecha_inicio` date,
  `fecha_fin` date,
  `datos` text COMMENT 'JSON/serializado con el resultado del reporte',
  `fecha_generacion` timestamp DEFAULT (now())
);

CREATE TABLE `configuracion_sistema` (
  `id_configuracion` int PRIMARY KEY AUTO_INCREMENT,
  `nombre_negocio` varchar(255),
  `logo_url` varchar(255),
  `horario_apertura` time,
  `horario_cierre` time,
  `duracion_cita_min` int,
  `tiempo_cancelacion_min` int DEFAULT 10 COMMENT 'RF-CL-04',
  `tiempo_reprogramacion_min` int DEFAULT 10 COMMENT 'RF-BR-05 / HU-BR-05',
  `tiempo_notificacion_min` int DEFAULT 2 COMMENT 'RF-CL-03',
  `id_administrador_actualizacion` int,
  `fecha_actualizacion` timestamp
);

CREATE TABLE `auditoria` (
  `id_auditoria` int PRIMARY KEY AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `accion` varchar(255) NOT NULL,
  `fecha_hora` timestamp DEFAULT (now()),
  `resultado` varchar(255),
  `entidad_afectada` varchar(255),
  `detalle` text,
  `observacion` text
);

ALTER TABLE `roles` COMMENT = 'Catálogo de roles del sistema. Se precargan los 3 roles fijos definidos en el levantamiento de requerimientos';

ALTER TABLE `usuarios` COMMENT = 'Tabla única de usuarios; el rol se resuelve por FK a roles; los campos por rol quedan NULL cuando no aplican';

ALTER TABLE `configuracion_sistema` COMMENT = 'Registro único (singleton) de configuración global de la barbería';

ALTER TABLE `usuarios` ADD FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

ALTER TABLE `servicios` ADD FOREIGN KEY (`id_categoria`) REFERENCES `categorias_servicio` (`id_categoria`);

ALTER TABLE `citas` ADD FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `citas` ADD FOREIGN KEY (`id_barbero`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `citas` ADD FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`);

ALTER TABLE `disponibilidad` ADD FOREIGN KEY (`id_barbero`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `turnos` ADD FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `turnos` ADD FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`);

ALTER TABLE `reprogramaciones` ADD FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`);

ALTER TABLE `notificaciones` ADD FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`);

ALTER TABLE `notificaciones` ADD FOREIGN KEY (`id_usuario_destino`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `reportes` ADD FOREIGN KEY (`id_generador`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `configuracion_sistema` ADD FOREIGN KEY (`id_administrador_actualizacion`) REFERENCES `usuarios` (`id_usuario`);

ALTER TABLE `auditoria` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);
