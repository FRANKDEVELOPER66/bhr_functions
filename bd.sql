CREATE TABLE `destacamentos` (
  `id_destacamento` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `departamento` varchar(100) NOT NULL,
  `municipio` varchar(100) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_destacamento`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `reparaciones` (
  `id_reparacion` int NOT NULL AUTO_INCREMENT,
  `placa` varchar(20) NOT NULL,
  `id_tipo_reparacion` int NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `km_al_momento` int NOT NULL DEFAULT '0',
  `costo` decimal(10,2) DEFAULT NULL,
  `proveedor` varchar(150) DEFAULT NULL,
  `responsable` varchar(150) DEFAULT NULL,
  `estado` enum('En proceso','Finalizada') NOT NULL DEFAULT 'En proceso',
  `observaciones` text,
  PRIMARY KEY (`id_reparacion`),
  KEY `id_tipo_reparacion` (`id_tipo_reparacion`),
  KEY `reparaciones_ibfk_1` (`placa`),
  CONSTRAINT `reparaciones_ibfk_1` FOREIGN KEY (`placa`) REFERENCES `vehiculos` (`placa`) ON DELETE CASCADE,
  CONSTRAINT `reparaciones_ibfk_2` FOREIGN KEY (`id_tipo_reparacion`) REFERENCES `tipos_reparacion` (`id_tipo_reparacion`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `servicios` (
  `id_servicio` int unsigned NOT NULL AUTO_INCREMENT,
  `placa` varchar(20) NOT NULL,
  `id_tipo_servicio` int unsigned NOT NULL,
  `fecha_realizado` date NOT NULL,
  `km_al_servicio` int unsigned NOT NULL,
  `km_proximo_servicio` int unsigned DEFAULT NULL,
  `fecha_proximo` date DEFAULT NULL,
  `observaciones` text,
  `responsable` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_servicio`),
  KEY `fk_servicio_tipo` (`id_tipo_servicio`),
  KEY `fk_servicio_vehiculo` (`placa`),
  CONSTRAINT `fk_servicio_tipo` FOREIGN KEY (`id_tipo_servicio`) REFERENCES `tipos_servicio` (`id_tipo_servicio`),
  CONSTRAINT `fk_servicio_vehiculo` FOREIGN KEY (`placa`) REFERENCES `vehiculos` (`placa`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `tipos_reparacion` (
  `id_tipo_reparacion` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_tipo_reparacion`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `tipos_servicio` (
  `id_tipo_servicio` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `descripcion` text,
  `intervalo_km` int unsigned DEFAULT NULL,
  `intervalo_dias` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id_tipo_servicio`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `unidades` (
  `id_unidad` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `tipo` varchar(80) DEFAULT NULL,
  `id_destacamento` int DEFAULT NULL,
  PRIMARY KEY (`id_unidad`),
  KEY `id_destacamento` (`id_destacamento`),
  CONSTRAINT `unidades_ibfk_1` FOREIGN KEY (`id_destacamento`) REFERENCES `destacamentos` (`id_destacamento`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `vehiculos` (
  `placa` varchar(20) NOT NULL,
  `numero_serie` varchar(50) NOT NULL,
  `marca` varchar(60) NOT NULL,
  `modelo` varchar(60) NOT NULL,
  `anio` year NOT NULL,
  `color` varchar(40) NOT NULL,
  `tipo` varchar(60) NOT NULL,
  `km_actuales` int unsigned NOT NULL DEFAULT '0',
  `estado` enum('Alta','Baja','Taller') NOT NULL DEFAULT 'Alta',
  `fecha_ingreso` date NOT NULL,
  `observaciones` text,
  `foto_frente` varchar(255) DEFAULT NULL,
  `tarjeta_pdf` varchar(255) DEFAULT NULL,
  `id_unidad` int DEFAULT NULL,
  PRIMARY KEY (`placa`),
  UNIQUE KEY `numero_serie` (`numero_serie`),
  KEY `fk_vehiculo_unidad` (`id_unidad`),
  CONSTRAINT `fk_vehiculo_unidad` FOREIGN KEY (`id_unidad`) REFERENCES `unidades` (`id_unidad`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;





-- ============================================================
-- DATOS INICIALES
-- ============================================================

INSERT INTO tipos_servicio (nombre, descripcion, intervalo_km, intervalo_dias) VALUES
  ('Cambio de aceite',         'Aceite de motor y filtro',             5000,  90),
  ('Revisión de frenos',       'Pastillas, discos y líquido',          10000, 180),
  ('Cambio de llantas',        'Rotación o sustitución',               20000, 365),
  ('Revisión general',         'Inspección completa del vehículo',     NULL,  180),
  ('Cambio de filtro de aire', 'Filtro de admisión de aire',           10000, 180);

INSERT INTO tipos_reparacion (nombre, categoria) VALUES
  ('Falla de motor',          'Motor'),
  ('Falla eléctrica',         'Eléctrico'),
  ('Daño de carrocería',      'Carrocería'),
  ('Falla de transmisión',    'Transmisión'),
  ('Falla de suspensión',     'Suspensión'),
  ('Falla de frenos',         'Frenos'),
  ('Falla de sistema de AC',  'Climatización');