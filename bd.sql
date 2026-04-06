
-- ------------------------------------------------------------
-- TIPOS DE SERVICIO (mantenimiento preventivo)
-- ------------------------------------------------------------
CREATE TABLE tipos_servicio (
  id_tipo_servicio  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre            VARCHAR(80)  NOT NULL,
  descripcion       TEXT         DEFAULT NULL,
  intervalo_km      INT UNSIGNED DEFAULT NULL,
  intervalo_dias    INT UNSIGNED DEFAULT NULL
) ENGINE=InnoDB;



insert into tipos_servicio (nombre, descripcion, intervalo_km, intervalo_dias) values ('SERVICEQUE', 'MAMOSELAS', 10, 10);

-- ------------------------------------------------------------
-- TIPOS DE REPARACIÓN (mantenimiento correctivo)
-- ------------------------------------------------------------
CREATE TABLE tipos_reparacion (
  id_tipo_reparacion  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre              VARCHAR(80) NOT NULL,
  categoria           VARCHAR(60) DEFAULT NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- VEHÍCULOS  (la placa es el identificador principal)
-- ------------------------------------------------------------
CREATE TABLE vehiculos (
  placa           VARCHAR(20)  NOT NULL PRIMARY KEY,
  numero_serie    VARCHAR(50)  NOT NULL UNIQUE,
  marca           VARCHAR(60)  NOT NULL,
  modelo          VARCHAR(60)  NOT NULL,
  anio            YEAR         NOT NULL,
  color           VARCHAR(40)  NOT NULL,
  tipo            VARCHAR(60)  NOT NULL,
  km_actuales     INT UNSIGNED NOT NULL DEFAULT 0,
  estado          ENUM('Alta','Baja','Taller') NOT NULL DEFAULT 'Alta',
  fecha_ingreso   DATE         NOT NULL,
  observaciones   TEXT         DEFAULT NULL
) ENGINE=InnoDB;

  ALTER TABLE vehiculos 
    ADD COLUMN foto_frente    VARCHAR(255) DEFAULT NULL AFTER observaciones,
    ADD COLUMN tarjeta_pdf    VARCHAR(255) DEFAULT NULL AFTER foto_frente;

-- ------------------------------------------------------------
-- SERVICIOS (mantenimiento preventivo)
-- ------------------------------------------------------------
CREATE TABLE servicios (
  id_servicio         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  placa               VARCHAR(20)  NOT NULL,
  id_tipo_servicio    INT UNSIGNED NOT NULL,
  fecha_realizado     DATE         NOT NULL,
  km_al_servicio      INT UNSIGNED NOT NULL,
  km_proximo_servicio INT UNSIGNED DEFAULT NULL,
  fecha_proximo       DATE         DEFAULT NULL,
  observaciones       TEXT         DEFAULT NULL,
  responsable         VARCHAR(100) DEFAULT NULL,
  CONSTRAINT fk_servicio_vehiculo FOREIGN KEY (placa)            REFERENCES vehiculos      (placa),
  CONSTRAINT fk_servicio_tipo     FOREIGN KEY (id_tipo_servicio) REFERENCES tipos_servicio (id_tipo_servicio)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- REPARACIONES (mantenimiento correctivo)
-- ------------------------------------------------------------
CREATE TABLE reparaciones (
  id_reparacion       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  placa               VARCHAR(20)   NOT NULL,
  id_tipo_reparacion  INT UNSIGNED  NOT NULL,
  fecha_inicio        DATE          NOT NULL,
  fecha_fin           DATE          DEFAULT NULL,
  descripcion         TEXT          NOT NULL,
  costo               DECIMAL(10,2) DEFAULT NULL,
  proveedor           VARCHAR(100)  DEFAULT NULL,
  numero_orden        VARCHAR(60)   DEFAULT NULL,
  CONSTRAINT fk_reparacion_vehiculo FOREIGN KEY (placa)               REFERENCES vehiculos        (placa),
  CONSTRAINT fk_reparacion_tipo     FOREIGN KEY (id_tipo_reparacion)   REFERENCES tipos_reparacion (id_tipo_reparacion)
) ENGINE=InnoDB;

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