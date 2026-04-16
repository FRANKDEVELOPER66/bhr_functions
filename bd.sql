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
  `foto_lateral` varchar(255) DEFAULT NULL,
  `foto_trasera` varchar(255) DEFAULT NULL,
  `tarjeta_pdf` varchar(255) DEFAULT NULL,
  `id_unidad` int DEFAULT NULL,
  `cert_inventario` varchar(255) DEFAULT NULL COMMENT 'PDF certificación inventario',
  `cert_sicoin` varchar(255) DEFAULT NULL COMMENT 'PDF certificación SICOIN Web',
  PRIMARY KEY (`placa`),
  UNIQUE KEY `numero_serie` (`numero_serie`),
  KEY `fk_vehiculo_unidad` (`id_unidad`),
  CONSTRAINT `fk_vehiculo_unidad` FOREIGN KEY (`id_unidad`) REFERENCES `unidades` (`id_unidad`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `chequeos_vehiculo` (
  `id_chequeo`        INT NOT NULL AUTO_INCREMENT,
  `placa`             VARCHAR(20) NOT NULL,
  `fecha_chequeo`     DATE NOT NULL,
  `km_al_chequeo`     INT UNSIGNED NOT NULL DEFAULT 0,
  `realizado_por`     VARCHAR(150) DEFAULT NULL,
  `observaciones_gen` TEXT,
  `estado`            ENUM('Pendiente','Completado') NOT NULL DEFAULT 'Pendiente',
  PRIMARY KEY (`id_chequeo`),
  KEY `fk_chequeo_vehiculo` (`placa`),
  CONSTRAINT `fk_chequeo_vehiculo`
    FOREIGN KEY (`placa`) REFERENCES `vehiculos` (`placa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLA: chequeo_items
-- Detalle de cada ítem del chequeo
-- ============================================================
CREATE TABLE `chequeo_items` (
  `id_item`      INT NOT NULL AUTO_INCREMENT,
  `id_chequeo`   INT NOT NULL,
  `numero_item`  TINYINT UNSIGNED NOT NULL COMMENT '1-17',
  `resultado`    ENUM('BE','ME','MEI','NT') DEFAULT NULL,
  `observacion`  VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_item`),
  KEY `fk_item_chequeo` (`id_chequeo`),
  CONSTRAINT `fk_item_chequeo`
    FOREIGN KEY (`id_chequeo`) REFERENCES `chequeos_vehiculo` (`id_chequeo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLA: seguros
-- Se registra al ingresar un vehículo o en cualquier momento
-- ============================================================
CREATE TABLE `seguros` (
  `id_seguro`         INT NOT NULL AUTO_INCREMENT,
  `placa`             VARCHAR(20) NOT NULL,
  `aseguradora`       VARCHAR(150) NOT NULL,
  `numero_poliza`     VARCHAR(80) NOT NULL,
  `tipo_cobertura`    ENUM('Básico','Amplio','Todo riesgo') NOT NULL DEFAULT 'Básico',
  `fecha_inicio`      DATE NOT NULL,
  `fecha_vencimiento` DATE NOT NULL,
  `prima_anual`       DECIMAL(10,2) DEFAULT NULL,
  `agente_contacto`   VARCHAR(150) DEFAULT NULL,
  `telefono_agente`   VARCHAR(20) DEFAULT NULL,
  `archivo_poliza`    VARCHAR(255) DEFAULT NULL COMMENT 'Ruta al PDF de la póliza',
  `estado`            ENUM('Vigente','Vencido','Cancelado') NOT NULL DEFAULT 'Vigente',
  `observaciones`     TEXT,
  PRIMARY KEY (`id_seguro`),
  KEY `fk_seguro_vehiculo` (`placa`),
  UNIQUE KEY `uk_poliza` (`numero_poliza`),
  CONSTRAINT `fk_seguro_vehiculo`
    FOREIGN KEY (`placa`) REFERENCES `vehiculos` (`placa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- ============================================================
-- TABLA: accidentes
-- Historial de choques o accidentes por vehículo
-- El id_seguro es nullable: puede haber accidente sin seguro
-- ============================================================
CREATE TABLE `accidentes` (
  `id_accidente`         INT NOT NULL AUTO_INCREMENT,
  `placa`                VARCHAR(20) NOT NULL,
  `id_seguro`            INT DEFAULT NULL COMMENT 'Seguro activo al momento del accidente',
  `fecha_accidente`      DATE NOT NULL,
  `lugar`                VARCHAR(255) NOT NULL,
  `tipo_accidente`       ENUM('Colisión','Volcamiento','Atropello','Daño por tercero','Robo parcial','Otro') NOT NULL DEFAULT 'Colisión',
  `descripcion`          TEXT NOT NULL,
  `conductor_responsable` VARCHAR(150) DEFAULT NULL,
  `costo_estimado`       DECIMAL(10,2) DEFAULT NULL,
  `costo_real`           DECIMAL(10,2) DEFAULT NULL,
  `estado_caso`          ENUM('Reportado','En trámite','Cerrado','Sin seguro') NOT NULL DEFAULT 'Reportado',
  `numero_expediente`    VARCHAR(80) DEFAULT NULL COMMENT 'Número de reclamo ante la aseguradora',
  `archivo_fotos`        VARCHAR(255) DEFAULT NULL COMMENT 'Ruta a carpeta o zip de fotos',
  `archivo_informe`      VARCHAR(255) DEFAULT NULL COMMENT 'Ruta al informe policial o PDF',
  `observaciones`        TEXT,
  PRIMARY KEY (`id_accidente`),
  KEY `fk_accidente_vehiculo` (`placa`),
  KEY `fk_accidente_seguro` (`id_seguro`),
  CONSTRAINT `fk_accidente_vehiculo`
    FOREIGN KEY (`placa`) REFERENCES `vehiculos` (`placa`) ON DELETE CASCADE,
  CONSTRAINT `fk_accidente_seguro`
    FOREIGN KEY (`id_seguro`) REFERENCES `seguros` (`id_seguro`) ON DELETE SET NULL
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

  INSERT INTO destacamentos (nombre, departamento, municipio, descripcion) VALUES
('Primera Brigada de Infanterría', 'Petén', 'Flores', 'BHR Petén'),
('Septima Brigada de Infantería', 'Baja Verapaz', 'Salamá', 'BHR Salamá'),
('BHR Central', 'Guatemala', 'Guatemala', 'BHR Sede'),
('Brigada de Infantería de Marina', 'Izabal', 'Puerto Barrios', 'BHR Puerto Barrios'),
('Quinta Brigada de Infantería', 'Huehuetenango', 'Hehuetenango', 'BHR Huehuetenango'),
('Cuarta Brigada de Infantería', 'Mazatenango', 'Mazatenango', 'BHR Mazatenango');