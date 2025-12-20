-- Esquema base para la plataforma de votación "SufragioDB"


CREATE DATABASE IF NOT EXISTS SufragioDB
  DEFAULT CHARACTER SET utf8mb4
  

USE SufragioDB;

SET NAMES utf8mb4;


CREATE TABLE IF NOT EXISTS departamentos (
    id SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo CHAR(2) NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    cabecera VARCHAR(120) NOT NULL,
    diputados_cupos TINYINT NOT NULL,
    candidatos_diputados SMALLINT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (codigo),
    UNIQUE (nombre)
);



CREATE TABLE IF NOT EXISTS municipios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departamento_id SMALLINT NOT NULL,
    codigo CHAR(4) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (codigo),
    UNIQUE (departamento_id, nombre),
    FOREIGN KEY (departamento_id) REFERENCES departamentos (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tipos_votacion (
    id TINYINT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    descripcion VARCHAR(120) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS centros_votacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    municipio_id INT NOT NULL,
    nombre VARCHAR(160) NOT NULL,
    codigo VARCHAR(25) NOT NULL,
    direccion VARCHAR(220) DEFAULT NULL,
    referencia VARCHAR(160) DEFAULT NULL,
    capacidad INT DEFAULT 0,
    estado ENUM('activo', 'inactivo', 'mantenimiento') DEFAULT 'activo',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (codigo),
    INDEX (municipio_id),
    FOREIGN KEY (municipio_id) REFERENCES municipios (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS centros_votacion_exterior (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pais VARCHAR(80) NOT NULL,
    estado VARCHAR(80) NOT NULL,
    ciudad VARCHAR(80) NOT NULL,
    sector_electoral VARCHAR(60) NOT NULL,
    juntas VARCHAR(160) NOT NULL,
    nombre VARCHAR(180) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (pais, estado, ciudad, nombre)
);

CREATE TABLE IF NOT EXISTS autoridades_municipales (
    municipio_id INT PRIMARY KEY,
    alcalde VARCHAR(120) NOT NULL,
    vice_alcalde VARCHAR(120) NOT NULL,
    periodo_inicio YEAR NULL,
    periodo_fin YEAR NULL,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (municipio_id) REFERENCES municipios (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS planillas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('presidencial', 'diputacion', 'diputados', 'alcaldia', 'vicealcaldia') NOT NULL,
    departamento_id SMALLINT DEFAULT NULL,
    municipio_id INT DEFAULT NULL,
    nombre VARCHAR(160) NOT NULL,
    partido VARCHAR(160) NOT NULL,
    descripcion TEXT,
    logo_url VARCHAR(255) DEFAULT NULL,
    estado ENUM('habilitada', 'inhabilitada') DEFAULT 'habilitada',
    creada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (departamento_id) REFERENCES departamentos (id) ON DELETE SET NULL,
    FOREIGN KEY (municipio_id) REFERENCES municipios (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS candidatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    planilla_id INT NOT NULL,
    nombre VARCHAR(160) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    numero_candidato INT DEFAULT NULL,
    foto_url VARCHAR(255) DEFAULT NULL,
    hoja_vida_url VARCHAR(255) DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (planilla_id) REFERENCES planillas (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dni CHAR(15) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    telefono VARCHAR(25) DEFAULT NULL,
    rol ENUM('votante', 'administrador') NOT NULL DEFAULT 'votante',
    tipo_votante ENUM('nacional', 'internacional') NOT NULL DEFAULT 'nacional',
    password_hash VARCHAR(255) NOT NULL,
    departamento_id SMALLINT DEFAULT NULL,
    municipio_id INT DEFAULT NULL,
    centro_votacion_id INT DEFAULT NULL,
    centro_votacion_exterior_id INT DEFAULT NULL,
    tipo_votacion_id TINYINT DEFAULT NULL,
    estado ENUM('activo', 'suspendido') NOT NULL DEFAULT 'activo',
    ultimo_acceso DATETIME DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (dni),
    INDEX (departamento_id),
    INDEX (municipio_id),
    INDEX (centro_votacion_id),
    INDEX (centro_votacion_exterior_id),
    INDEX (tipo_votacion_id),
    FOREIGN KEY (departamento_id) REFERENCES departamentos (id) ON DELETE SET NULL,
    FOREIGN KEY (municipio_id) REFERENCES municipios (id) ON DELETE SET NULL,
    FOREIGN KEY (centro_votacion_id) REFERENCES centros_votacion (id) ON DELETE SET NULL,
    FOREIGN KEY (tipo_votacion_id) REFERENCES tipos_votacion (id) ON DELETE SET NULL,
    FOREIGN KEY (centro_votacion_exterior_id) REFERENCES centros_votacion_exterior (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS votantes (
    usuario_id INT PRIMARY KEY,
    fecha_nacimiento DATE DEFAULT NULL,
    genero ENUM('M', 'F', 'X') DEFAULT NULL,
    direccion VARCHAR(220) DEFAULT NULL,
    municipio_emision VARCHAR(120) DEFAULT NULL,
    habilitado TINYINT(1) DEFAULT 1,
    fecha_verificacion DATETIME DEFAULT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS denuncias_actos_irregulares (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_votante ENUM('nacional', 'internacional') NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT NOT NULL,
    evidencia_url VARCHAR(255) DEFAULT NULL,
    estado ENUM('recibida', 'en_revision', 'resuelta', 'rechazada') DEFAULT 'recibida',
    creada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS votos (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    planilla_id INT NOT NULL,
    candidato_id INT NOT NULL,
    registrado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_origen VARCHAR(45) DEFAULT NULL,
    hash_verificacion CHAR(64) DEFAULT NULL,
    UNIQUE (usuario_id, candidato_id),
    INDEX (planilla_id),
    INDEX (candidato_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    FOREIGN KEY (planilla_id) REFERENCES planillas (id) ON DELETE CASCADE,
    FOREIGN KEY (candidato_id) REFERENCES candidatos (id) ON DELETE CASCADE
);

INSERT INTO tipos_votacion (codigo, descripcion) VALUES
    ('nacional', 'Voto dentro del territorio hondureño'),
    ('exterior', 'Voto en consulados o embajadas en el extranjero')
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion);



-- Insertar departamentos
INSERT INTO departamentos (codigo, nombre, cabecera, diputados_cupos, candidatos_diputados) VALUES
    ('01', 'Atlántida', 'La Ceiba', 8, 40),
    ('02', 'Colón', 'Trujillo', 4, 16),
    ('03', 'Comayagua', 'Comayagua', 7, 35),
    ('04', 'Copán', 'Santa Rosa de Copán', 7, 35),
    ('05', 'Cortés', 'San Pedro Sula', 20, 100),
    ('06', 'Choluteca', 'Choluteca', 9, 45),
    ('07', 'El Paraíso', 'Yuscarán', 6, 30),
    ('08', 'Francisco Morazán', 'Distrito Central', 23, 115),
    ('09', 'Gracias a Dios', 'Puerto Lempira', 1, 5),
    ('10', 'Intibucá', 'La Esperanza', 3, 15),
    ('11', 'Islas de la Bahía', 'Roatán', 1, 5),
    ('12', 'La Paz', 'La Paz', 3, 15),
    ('13', 'Lempira', 'Gracias', 5, 13),
    ('14', 'Ocotepeque', 'Ocotepeque', 2, 10),
    ('15', 'Olancho', 'Juticalpa', 7, 35),
    ('16', 'Santa Bárbara', 'Santa Bárbara', 9, 45),
    ('17', 'Valle', 'Nacaome', 4, 16),
    ('18', 'Yoro', 'Yoro', 9, 45)
ON DUPLICATE KEY UPDATE 
    nombre = VALUES(nombre), 
    cabecera = VALUES(cabecera), 
    diputados_cupos = VALUES(diputados_cupos), 
    candidatos_diputados = VALUES(candidatos_diputados);

-- Insertar TODOS los municipios
INSERT INTO municipios (departamento_id, codigo, nombre) VALUES
    -- Atlántida
    (1, '0101', 'La Ceiba'), (1, '0102', 'El Porvenir'), (1, '0103', 'Esparta'), (1, '0104', 'Jutiapa'), (1, '0105', 'La Masica'), (1, '0106', 'San Francisco'), (1, '0107', 'Tela'), (1, '0108', 'Arizona'),
    -- Colón  
    (2, '0201', 'Trujillo'), (2, '0202', 'Balfate'), (2, '0203', 'Iriona'), (2, '0204', 'Limón'), (2, '0205', 'Sabá'), (2, '0206', 'Santa Fe'), (2, '0207', 'Santa Rosa de Aguán'), (2, '0208', 'Sonaguera'), (2, '0209', 'Tocoa'), (2, '0210', 'Bonito Oriental'),
    -- Comayagua
    (3, '0301', 'Comayagua'), (3, '0302', 'Ajuterique'), (3, '0303', 'El Rosario'), (3, '0304', 'Esquías'), (3, '0305', 'Humuya'), (3, '0306', 'La Libertad'), (3, '0307', 'Lamaní'), (3, '0308', 'La Trinidad'), (3, '0309', 'Lejamaní'), (3, '0310', 'Meámbar'), (3, '0311', 'Minas de Oro'), (3, '0312', 'Ojos de Agua'), (3, '0313', 'San Jerónimo'), (3, '0314', 'San José de Comayagua'), (3, '0315', 'San José del Potrero'), (3, '0316', 'San Luis'), (3, '0317', 'San Sebastián'), (3, '0318', 'Siguatepeque'), (3, '0319', 'Villa de San Antonio'), (3, '0320', 'Las Lajas'), (3, '0321', 'Taulabé'),
    -- Copán
    (4, '0401', 'Santa Rosa de Copán'), (4, '0402', 'Cabañas'), (4, '0403', 'Concepción'), (4, '0404', 'Copán Ruinas'), (4, '0405', 'Corquín'), (4, '0406', 'Cucuyagua'), (4, '0407', 'Dolores'), (4, '0408', 'Dulce Nombre'), (4, '0409', 'El Paraíso'), (4, '0410', 'Florida'), (4, '0411', 'La Jigua'), (4, '0412', 'La Unión'), (4, '0413', 'Nueva Arcadia'), (4, '0414', 'San Agustín'), (4, '0415', 'San Antonio'), (4, '0416', 'San Jerónimo'), (4, '0417', 'San José'), (4, '0418', 'San Juan de Opoa'), (4, '0419', 'San Nicolás'), (4, '0420', 'San Pedro'), (4, '0421', 'Santa Rita'), (4, '0422', 'Trinidad de Copán'), (4, '0423', 'Veracruz'),
    -- Cortés
    (5, '0501', 'San Pedro Sula'), (5, '0502', 'Choloma'), (5, '0503', 'Omoa'), (5, '0504', 'Puerto Cortés'), (5, '0505', 'San Antonio de Cortés'), (5, '0506', 'San Francisco de Yojoa'), (5, '0507', 'San Manuel'), (5, '0508', 'Santa Cruz de Yojoa'), (5, '0509', 'Villanueva'), (5, '0510', 'La Lima'), (5, '0511', 'Pimienta'), (5, '0512', 'Potrerillos'),
    -- Choluteca
    (6, '0601', 'Choluteca'), (6, '0602', 'Apacilagua'), (6, '0603', 'Concepción de María'), (6, '0604', 'Duyure'), (6, '0605', 'El Corpus'), (6, '0606', 'El Triunfo'), (6, '0607', 'Marcovia'), (6, '0608', 'Morolica'), (6, '0609', 'Namasigüe'), (6, '0610', 'Orocuina'), (6, '0611', 'Pespire'), (6, '0612', 'San Antonio de Flores'), (6, '0613', 'San Isidro'), (6, '0614', 'San José'), (6, '0615', 'San Marcos de Colón'), (6, '0616', 'Santa Ana de Yusguare'),
    -- El Paraíso
    (7, '0701', 'Yuscarán'), (7, '0702', 'Alauca'), (7, '0703', 'Danlí'), (7, '0704', 'El Paraíso'), (7, '0705', 'Güinope'), (7, '0706', 'Jacaleapa'), (7, '0707', 'Liure'), (7, '0708', 'Morocelí'), (7, '0709', 'Oropolí'), (7, '0710', 'Potrerillos'), (7, '0711', 'San Antonio de Flores'), (7, '0712', 'San Lucas'), (7, '0713', 'San Matías'), (7, '0714', 'Soledad'), (7, '0715', 'Teupasenti'), (7, '0716', 'Texiguat'), (7, '0717', 'Trojes'), (7, '0718', 'Vado Ancho'), (7, '0719', 'Yauyupe'),
    -- Francisco Morazán
    (8, '0801', 'Distrito Central'), (8, '0802', 'Alubarén'), (8, '0803', 'Cedros'), (8, '0804', 'Curarén'), (8, '0805', 'El Porvenir'), (8, '0806', 'Guaimaca'), (8, '0807', 'La Libertad'), (8, '0808', 'La Venta'), (8, '0809', 'Lepaterique'), (8, '0810', 'Maraita'), (8, '0811', 'Marale'), (8, '0812', 'Nueva Armenia'), (8, '0813', 'Ojojona'), (8, '0814', 'Orica'), (8, '0815', 'Reitoca'), (8, '0816', 'Sabanagrande'), (8, '0817', 'San Antonio de Oriente'), (8, '0818', 'San Buenaventura'), (8, '0819', 'San Ignacio'), (8, '0820', 'San Juan de Flores'), (8, '0821', 'San Miguelito'), (8, '0822', 'Santa Ana'), (8, '0823', 'Santa Lucía'), (8, '0824', 'Talanga'), (8, '0825', 'Tatumbla'), (8, '0826', 'Valle de Ángeles'), (8, '0827', 'Villa de San Francisco'), (8, '0828', 'Vallecillo'),
    -- Gracias a Dios
    (9, '0901', 'Puerto Lempira'), (9, '0902', 'Brus Laguna'), (9, '0903', 'Ahuas'), (9, '0904', 'Juan Francisco Bulnes'), (9, '0905', 'Ramón Villeda Morales'), (9, '0906', 'Wampusirpi'),
    -- Intibucá
    (10, '1001', 'La Esperanza'), (10, '1002', 'Camasca'), (10, '1003', 'Colomoncagua'), (10, '1004', 'Concepción'), (10, '1005', 'Dolores'), (10, '1006', 'Intibucá'), (10, '1007', 'Jesús de Otoro'), (10, '1008', 'Magdalena'), (10, '1009', 'Masaguara'), (10, '1010', 'San Antonio'), (10, '1011', 'San Francisco de Opalaca'), (10, '1012', 'San Isidro'), (10, '1013', 'San Juan'), (10, '1014', 'San Marcos de la Sierra'), (10, '1015', 'San Miguel Guancapla'), (10, '1016', 'Santa Lucía'), (10, '1017', 'Yamaranguila'),
    -- Islas de la Bahía
    (11, '1101', 'Roatán'), (11, '1102', 'José Santos Guardiola'), (11, '1103', 'Utila'), (11, '1104', 'Guanaja'),
    -- La Paz
    (12, '1201', 'La Paz'), (12, '1202', 'Aguanqueterique'), (12, '1203', 'Cabañas'), (12, '1204', 'Cane'), (12, '1205', 'Chinacla'), (12, '1206', 'Guajiquiro'), (12, '1207', 'Lauterique'), (12, '1208', 'Marcala'), (12, '1209', 'Mercedes de Oriente'), (12, '1210', 'Opatoro'), (12, '1211', 'San Antonio del Norte'), (12, '1212', 'San José'), (12, '1213', 'San Juan'), (12, '1214', 'San Pedro de Tutule'), (12, '1215', 'Santa Ana'), (12, '1216', 'Santa Elena'), (12, '1217', 'Santa María'), (12, '1218', 'Santiago de Puringla'), (12, '1219', 'Yarula'),
    -- Lempira
    (13, '1301', 'Gracias'), (13, '1302', 'Belén'), (13, '1303', 'Candelaria'), (13, '1304', 'Cololaca'), (13, '1305', 'Erandique'), (13, '1306', 'Gualcince'), (13, '1307', 'Guarita'), (13, '1308', 'La Campa'), (13, '1309', 'La Iguala'), (13, '1310', 'La Virtud'), (13, '1311', 'Las Flores'), (13, '1312', 'Lepaera'), (13, '1313', 'Mapulaca'), (13, '1314', 'Piraera'), (13, '1315', 'San Andrés'), (13, '1316', 'San Francisco'), (13, '1317', 'San Juan Guarita'), (13, '1318', 'San Manuel Colohete'), (13, '1319', 'San Marcos de Caiquín'), (13, '1320', 'San Rafael'), (13, '1321', 'San Sebastián'), (13, '1322', 'Santa Cruz'), (13, '1323', 'Talgua'), (13, '1324', 'Tambla'), (13, '1325', 'Tomalá'), (13, '1326', 'Valladolid'), (13, '1327', 'Virginia'), (13, '1328', 'La Unión'),
    -- Ocotepeque
    (14, '1401', 'Ocotepeque'), (14, '1402', 'Belén Gualcho'), (14, '1403', 'Concepción'), (14, '1404', 'Dolores Merendón'), (14, '1405', 'Fraternidad'), (14, '1406', 'La Encarnación'), (14, '1407', 'La Labor'), (14, '1408', 'Lucerna'), (14, '1409', 'Mercedes'), (14, '1410', 'San Fernando'), (14, '1411', 'San Francisco del Valle'), (14, '1412', 'San Jorge'), (14, '1413', 'San Marcos'), (14, '1414', 'Santa Fe'), (14, '1415', 'Sensenti'), (14, '1416', 'Sinuapa'),
    -- Olancho
    (15, '1501', 'Juticalpa'), (15, '1502', 'Campamento'), (15, '1503', 'Catacamas'), (15, '1504', 'Concordia'), (15, '1505', 'Dulce Nombre de Culmí'), (15, '1506', 'El Rosario'), (15, '1507', 'Esquipulas del Norte'), (15, '1508', 'Gualaco'), (15, '1509', 'Guata'), (15, '1510', 'Guayape'), (15, '1511', 'Guarizama'), (15, '1512', 'Jano'), (15, '1513', 'La Unión'), (15, '1514', 'Mangulile'), (15, '1515', 'Manto'), (15, '1516', 'Patuca'), (15, '1517', 'Salamá'), (15, '1518', 'San Esteban'), (15, '1519', 'San Francisco de Becerra'), (15, '1520', 'San Francisco de la Paz'), (15, '1521', 'Santa María del Real'), (15, '1522', 'Silca'), (15, '1523', 'Yocón'),
    -- Santa Bárbara
    (16, '1601', 'Santa Bárbara'), (16, '1602', 'Arada'), (16, '1603', 'Atima'), (16, '1604', 'Azacualpa'), (16, '1605', 'Ceguaca'), (16, '1606', 'Chinda'), (16, '1607', 'Concepción del Norte'), (16, '1608', 'Concepción del Sur'), (16, '1609', 'El Níspero'), (16, '1610', 'Gualala'), (16, '1611', 'Ilama'), (16, '1612', 'Las Vegas'), (16, '1613', 'Macuelizo'), (16, '1614', 'Naranjito'), (16, '1615', 'Nueva Celilac'), (16, '1616', 'Petoa'), (16, '1617', 'Protección'), (16, '1618', 'Quimistán'), (16, '1619', 'San Francisco de Ojuera'), (16, '1620', 'San José de Colinas'), (16, '1621', 'San Luis'), (16, '1622', 'San Marcos'), (16, '1623', 'San Nicolás'), (16, '1624', 'San Pedro Zacapa'), (16, '1625', 'Santa Rita'), (16, '1626', 'San Vicente Centenario'), (16, '1627', 'Trinidad'), (16, '1628', 'Nueva Frontera'),
    -- Valle
    (17, '1701', 'Nacaome'), (17, '1702', 'Alianza'), (17, '1703', 'Amapala'), (17, '1704', 'Aramecina'), (17, '1705', 'Caridad'), (17, '1706', 'Goascorán'), (17, '1707', 'Langue'), (17, '1708', 'San Francisco de Coray'), (17, '1709', 'San Lorenzo'),
    -- Yoro
    (18, '1801', 'Yoro'), (18, '1802', 'Arenal'), (18, '1803', 'El Negrito'), (18, '1804', 'El Progreso'), (18, '1805', 'Jocón'), (18, '1806', 'Morazán'), (18, '1807', 'Olanchito'), (18, '1808', 'Santa Rita'), (18, '1809', 'Sulaco'), (18, '1810', 'Victoria'), (18, '1811', 'Yorito')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Insertar usuarios administradores y de prueba
INSERT INTO usuarios (dni, nombre, email, telefono, rol, password_hash, departamento_id, municipio_id, tipo_votante) VALUES
    ('0801-1990-00001', 'Administrador General', 'admin@elecciones.hn', '+504 2200-0001', 'administrador', '$2y$10$TKh2H1.PfQx37WO5.t9l/OrEqo4n8/j5qXzVCOZhpqnwvE3YD.8am', 8, 1, 'nacional'),
    ('0801-2001-12345', 'Juana Votante Ciudadana', 'juana.votante@example.com', '+504 9933-2211', 'votante', '$2y$10$TKh2H1.PfQx37WO5.t9l/OrEqo4n8/j5qXzVCOZhpqnwvE3YD.8am', 8, 1, 'nacional')
ON DUPLICATE KEY UPDATE 
    nombre = VALUES(nombre), 
    email = VALUES(email), 
    telefono = VALUES(telefono), 
    rol = VALUES(rol);

-- Insertar TODAS las planillas para TODOS los departamentos
INSERT INTO planillas (tipo, departamento_id, nombre, partido, estado) VALUES
    -- Atlántida (8 diputados)
    ('diputados', 1, 'Diputados Atlántida - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 1, 'Diputados Atlántida - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 1, 'Diputados Atlántida - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 1, 'Diputados Atlántida - PSH', 'PSH', 'habilitada'), ('diputados', 1, 'Diputados Atlántida - DC', 'DC', 'habilitada'),
    -- Colón (4 diputados)  
    ('diputados', 2, 'Diputados Colón - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 2, 'Diputados Colón - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 2, 'Diputados Colón - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 2, 'Diputados Colón - PSH', 'PSH', 'habilitada'),
    -- Comayagua (7 diputados)
    ('diputados', 3, 'Diputados Comayagua - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 3, 'Diputados Comayagua - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 3, 'Diputados Comayagua - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 3, 'Diputados Comayagua - PSH', 'PSH', 'habilitada'), ('diputados', 3, 'Diputados Comayagua - DC', 'DC', 'habilitada'),
    -- Copán (7 diputados)
    ('diputados', 4, 'Diputados Copán - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 4, 'Diputados Copán - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 4, 'Diputados Copán - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 4, 'Diputados Copán - PSH', 'PSH', 'habilitada'), ('diputados', 4, 'Diputados Copán - DC', 'DC', 'habilitada'),
    -- Cortés (20 diputados)
    ('diputados', 5, 'Diputados Cortés - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 5, 'Diputados Cortés - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 5, 'Diputados Cortés - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 5, 'Diputados Cortés - PSH', 'PSH', 'habilitada'),
    -- Choluteca (9 diputados)
    ('diputados', 6, 'Diputados Choluteca - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 6, 'Diputados Choluteca - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 6, 'Diputados Choluteca - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 6, 'Diputados Choluteca - DC', 'DC', 'habilitada'), ('diputados', 6, 'Diputados Choluteca - PSH', 'PSH', 'habilitada'),
    -- El Paraíso (6 diputados)
    ('diputados', 7, 'Diputados El Paraíso - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 7, 'Diputados El Paraíso - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 7, 'Diputados El Paraíso - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 7, 'Diputados El Paraíso - PSH', 'PSH', 'habilitada'), ('diputados', 7, 'Diputados El Paraíso - DC', 'DC', 'habilitada'),
    -- Francisco Morazán (23 diputados)
    ('diputados', 8, 'Diputados Francisco Morazán - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 8, 'Diputados Francisco Morazán - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 8, 'Diputados Francisco Morazán - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 8, 'Diputados Francisco Morazán - DC', 'DC', 'habilitada'), ('diputados', 8, 'Diputados Francisco Morazán - PSH', 'PSH', 'habilitada'),
    -- Gracias a Dios (1 diputado)
    ('diputados', 9, 'Diputados Gracias a Dios - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 9, 'Diputados Gracias a Dios - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 9, 'Diputados Gracias a Dios - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 9, 'Diputados Gracias a Dios - PSH', 'PSH', 'habilitada'), ('diputados', 9, 'Diputados Gracias a Dios - DC', 'DC', 'habilitada'),
    -- Intibucá (3 diputados)
    ('diputados', 10, 'Diputados Intibucá - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 10, 'Diputados Intibucá - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 10, 'Diputados Intibucá - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 10, 'Diputados Intibucá - PSH', 'PSH', 'habilitada'), ('diputados', 10, 'Diputados Intibucá - DC', 'DC', 'habilitada'),
    -- Islas de la Bahía (1 diputado)
    ('diputados', 11, 'Diputados Islas de la Bahía - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 11, 'Diputados Islas de la Bahía - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 11, 'Diputados Islas de la Bahía - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 11, 'Diputados Islas de la Bahía - PSH', 'PSH', 'habilitada'), ('diputados', 11, 'Diputados Islas de la Bahía - DC', 'DC', 'habilitada'),
    -- La Paz (3 diputados)
    ('diputados', 12, 'Diputados La Paz - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 12, 'Diputados La Paz - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 12, 'Diputados La Paz - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 12, 'Diputados La Paz - PSH', 'PSH', 'habilitada'), ('diputados', 12, 'Diputados La Paz - DC', 'DC', 'habilitada'),
    -- Lempira (5 diputados)
    ('diputados', 13, 'Diputados Lempira - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 13, 'Diputados Lempira - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 13, 'Diputados Lempira - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 13, 'Diputados Lempira - PSH', 'PSH', 'habilitada'), ('diputados', 13, 'Diputados Lempira - DC', 'DC', 'habilitada'),
    -- Ocotepeque (2 diputados)
    ('diputados', 14, 'Diputados Ocotepeque - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 14, 'Diputados Ocotepeque - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 14, 'Diputados Ocotepeque - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 14, 'Diputados Ocotepeque - PSH', 'PSH', 'habilitada'), ('diputados', 14, 'Diputados Ocotepeque - DC', 'DC', 'habilitada'),
    -- Olancho (7 diputados)
    ('diputados', 15, 'Diputados Olancho - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 15, 'Diputados Olancho - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 15, 'Diputados Olancho - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 15, 'Diputados Olancho - DC', 'DC', 'habilitada'), ('diputados', 15, 'Diputados Olancho - PSH', 'PSH', 'habilitada'),
    -- Santa Bárbara (9 diputados)
    ('diputados', 16, 'Diputados Santa Bárbara - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 16, 'Diputados Santa Bárbara - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 16, 'Diputados Santa Bárbara - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 16, 'Diputados Santa Bárbara - PSH', 'PSH', 'habilitada'), ('diputados', 16, 'Diputados Santa Bárbara - DC', 'DC', 'habilitada'),
    -- Valle (4 diputados)
    ('diputados', 17, 'Diputados Valle - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 17, 'Diputados Valle - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 17, 'Diputados Valle - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 17, 'Diputados Valle - PSH', 'PSH', 'habilitada'),
    -- Yoro (9 diputados)
    ('diputados', 18, 'Diputados Yoro - Partido Nacional', 'Partido Nacional', 'habilitada'), ('diputados', 18, 'Diputados Yoro - Partido Liberal', 'Partido Liberal', 'habilitada'), ('diputados', 18, 'Diputados Yoro - LIBRE', 'LIBRE', 'habilitada'), ('diputados', 18, 'Diputados Yoro - PSH', 'PSH', 'habilitada'), ('diputados', 18, 'Diputados Yoro - DC', 'DC', 'habilitada')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), partido = VALUES(partido);

-- Insertar TODOS los candidatos reales Honduras 2025

-- ATLÁNTIDA (8 diputados) - Planillas 1-5
INSERT INTO candidatos (planilla_id, nombre_completo, posicion, partido) VALUES
    -- Partido Liberal Atlántida
    (1, 'ALFONZO ORDOÑEZ RODRIGUEZ', 1, 'Partido Liberal'), (1, 'ALEJANDRO ANTONIO CANELAS CARDONA', 2, 'Partido Liberal'), (1, 'JOSE DOMINGO HENRIQUEZ MACHADO', 3, 'Partido Liberal'), (1, 'ENAN ESAU OCHOA FERRUFINO', 4, 'Partido Liberal'), (1, 'KERISHA ZONALY SPICER CLARK', 5, 'Partido Liberal'), (1, 'GLORIA JOSSELLY ARITA PORTILLO', 6, 'Partido Liberal'), (1, 'FANNY KARINA CABRERA GUARDADO', 7, 'Partido Liberal'), (1, 'ANA GABRIELA MARTINEZ GARAY', 8, 'Partido Liberal'),
    -- Partido Nacional Atlántida
    (2, 'MARCO ANTONIO MIDENCE MILLA', 1, 'Partido Nacional'), (2, 'DAVID SANIN MANAIZA RAMIREZ', 2, 'Partido Nacional'), (2, 'REMBERTO ALEXANDER ZAVALA ROZALES', 3, 'Partido Nacional'), (2, 'MARLA YADIRA ESCOBAR MONTUFAR', 4, 'Partido Nacional'), (2, 'IVETH OBDULIA MATUTE BETANCOURTH', 5, 'Partido Nacional'), (2, 'MARCIO RENE ESPINAL CARDONA', 6, 'Partido Nacional'), (2, 'ADELA ESMERALDA MARTINEZ', 7, 'Partido Nacional'), (2, 'MARILU AGUILAR', 8, 'Partido Nacional'),
    -- LIBRE Atlántida
    (3, 'ENRIQUE ALEJANDRO MATUTE DIAZ', 1, 'LIBRE'), (3, 'OSCAR ARIEL MONTOYA RODEZNO', 2, 'LIBRE'), (3, 'ANAEL MENDEZ RIVERA', 3, 'LIBRE'), (3, 'MARIO RENE CONTRERAS HERNANDEZ', 4, 'LIBRE'), (3, 'MARLENE ISABEL SANCHEZ SOLIS', 5, 'LIBRE'), (3, 'JOSELYN MARITZA ARDON GARCIA', 6, 'LIBRE'), (3, 'ZULMY ARELY LOPEZ NUFIO', 7, 'LIBRE'), (3, 'CARLOS ALFREDO ENAMORADO CARCAMO', 8, 'LIBRE'),
    -- PSH Atlántida
    (4, 'TOMAS ANTONIO RAMIREZ HERNANDEZ', 1, 'PSH'), (4, 'LORENA EDHIT VILLAFRANCA NUÑEZ', 2, 'PSH'), (4, 'MARIO RENE ORTEGA CASTILLO', 3, 'PSH'), (4, 'ANA ISABEL JIMENEZ MAJANO', 4, 'PSH'), (4, 'ALIRIO JOSE ARGUETA CRUZ', 5, 'PSH'), (4, 'GRICELDA MURILLO MARTINEZ', 6, 'PSH'), (4, 'INGRIS YELENI GARCIA PEÑA', 7, 'PSH'), (4, 'JORGE ROBERTO ORELLANA GARCIA', 8, 'PSH'),
    -- DC Atlántida
    (5, 'KENNIA YAMILETH MONTERO MARTINEZ', 1, 'DC'), (5, 'JOSE ANTONIO GALDAMES FUENTES', 2, 'DC'), (5, 'LUIS BERNARDO SARMIENTO NUÑEZ', 3, 'DC'), (5, 'INGRID ESTEFANIE SAMPSON RIVERA', 4, 'DC'), (5, 'ENRIQUE NASSAR BENITEZ', 5, 'DC'), (5, 'JORGE ALBERTO BUSTILLO ACOSTA', 6, 'DC'), (5, 'NIDIA WALESKA ESCOBAR PINEDA', 7, 'DC'), (5, 'BERTHA MARIA GARCIA VELIZ', 8, 'DC'),

-- COLÓN (4 diputados) - Planillas 6-9  
    -- Partido Nacional Colón
    (6, 'JUAN ALBERTO SAUCEDA CARDONA', 1, 'Partido Nacional'), (6, 'ARIANA MELISSA BANEGAS CARCAMO', 2, 'Partido Nacional'), (6, 'ELDA ISAURA MEJIA GARCIA', 3, 'Partido Nacional'), (6, 'DANY SALVADOR SANCHEZ JACKSON', 4, 'Partido Nacional'),
    -- LIBRE Colón
    (7, 'MARCO AURELIO MARADIAGA MOLINA', 1, 'LIBRE'), (7, 'DAIRI JAVIER AVILA GABARRETE', 2, 'LIBRE'), (7, 'SHERLY ADRIANA LOBO BONILLA', 3, 'LIBRE'), (7, 'DIGNA ISABEL VELASQUEZ CARDONA', 4, 'LIBRE'),
    -- Partido Liberal Colón
    (8, 'TIBDEO RICARDO ELENCOFF MARTINEZ', 1, 'Partido Liberal'), (8, 'CARLOS RENE ECHEVERRIA MATUTE', 2, 'Partido Liberal'), (8, 'BESSY KARINA GARCIA TURCIOS', 3, 'Partido Liberal'), (8, 'BARBARA MARCELA VARELA RIVERA', 4, 'Partido Liberal'),
    -- PSH Colón
    (9, 'LETICIA LIZETH ROMERO ROSALES', 1, 'PSH'), (9, 'LISANDRO ARRIOLA SOLANO', 2, 'PSH'), (9, 'MARITZA MARISOL MUNGUIA', 3, 'PSH'), (9, 'ALEXANDER ARIEL DURON MARTINEZ', 4, 'PSH'),

-- COMAYAGUA (7 diputados) - Planillas 10-14
    -- Partido Nacional Comayagua
    (10, 'ADRIAN JOSUE MARTINEZ SOLER', 1, 'Partido Nacional'), (10, 'JUAN CARLOS VARGAS RIOS', 2, 'Partido Nacional'), (10, 'CARLOS ALBERTO MEZA MEJIA', 3, 'Partido Nacional'), (10, 'DANIEL HUMBERTO DISCUA GUIFARRO', 4, 'Partido Nacional'), (10, 'STEPHANY JEANETH MACIAS ZELAYA', 5, 'Partido Nacional'), (10, 'CELESTE EMPERATRIZ MARTINEZ MEJIA', 6, 'Partido Nacional'), (10, 'ANA BELINDA VALENZUELA DUARTE', 7, 'Partido Nacional'),
    -- Partido Liberal Comayagua
    (11, 'ROLANDO ENRIQUE BARAHONA PUERTO', 1, 'Partido Liberal'), (11, 'ALBERTO EMILIO CRUZ ZELAYA', 2, 'Partido Liberal'), (11, 'GLORIA ARGENTINA BONILLA BONILLA', 3, 'Partido Liberal'), (11, 'YAVHE SALVADOR SABILLON CRUZ', 4, 'Partido Liberal'), (11, 'HERNAN JOSE MORAZAN DEDIEGO', 5, 'Partido Liberal'), (11, 'MIRIAN SUYAPA GONZALES CASTILLO', 6, 'Partido Liberal'), (11, 'JOSE FRANCISCO MORALES MARTINEZ', 7, 'Partido Liberal'),
    -- LIBRE Comayagua
    (12, 'RONALD EDGARDO PANCHAME URQUIA', 1, 'LIBRE'), (12, 'JAVIER ADOLFO MIRALDA VILLALOBOS', 2, 'LIBRE'), (12, 'JUAN RAMON FLORES BUEZO', 3, 'LIBRE'), (12, 'MA. JOSEFINA MEZA SARAVIA', 4, 'LIBRE'), (12, 'GERSON NOE SOLER VALLADARES', 5, 'LIBRE'), (12, 'KAREN SOFIA PONCE', 6, 'LIBRE'), (12, 'MARIA DEL CARMEN MALDONADO VELASQUEZ', 7, 'LIBRE'),
    -- PSH Comayagua
    (13, 'IRIS ALEJANDRA MEJIA MOLINA', 1, 'PSH'), (13, 'JOHANDRA SUSSETTE MEJIA BULNES', 2, 'PSH'), (13, 'JOSE ROLANDO BANEGAS PADILLA', 3, 'PSH'), (13, 'EMILIO SANCHEZ CANO', 4, 'PSH'), (13, 'JOSE ALFREDO RAMOS PALACIOS', 5, 'PSH'), (13, 'ENA MARLENIS DOMINGUEZ ESPINALES', 6, 'PSH'), (13, 'WALDINA LIZETH SANTOS MALDONADO', 7, 'PSH'),
    -- DC Comayagua
    (14, 'MOISES RECARTE ALVARADO', 1, 'DC'), (14, 'BETTY LIZETH MARTINEZ BANEGAS', 2, 'DC'), (14, 'JENCY ELIZABETH COREA DIAZ', 3, 'DC'), (14, 'YENSI NAHUN HERNANDEZ ESCOTO', 4, 'DC'), (14, 'ORFILIA AGUILAR', 5, 'DC'), (14, 'RUDY IRAEL HERNANDEZ BANEGAS', 6, 'DC'), (14, 'EDYS LEVID CASTAÑEDA GARCIA', 7, 'DC'),

-- COPÁN (7 diputados) - Planillas 15-19
    -- Partido Nacional Copán
    (15, 'ROY DAGOBERTO CRUZ PEREZ', 1, 'Partido Nacional'), (15, 'ERIK JOSE ALVARADO ALVARADO', 2, 'Partido Nacional'), (15, 'JUAN CARLOS LAGOS FUENTES', 3, 'Partido Nacional'), (15, 'ALBA YANIRA GUERRA MENDEZ', 4, 'Partido Nacional'), (15, 'DENIA MARISOL ROMERO MONGE', 5, 'Partido Nacional'), (15, 'OVIDIO LENIN AYALA ORELLANA', 6, 'Partido Nacional'), (15, 'DILCIA JANETH MANCIA CANELO', 7, 'Partido Nacional'),
    -- Partido Liberal Copán
    (16, 'FRANCIS OMAR CABRERA MIRANDA', 1, 'Partido Liberal'), (16, 'VALESKA YAMILETH VALENZUELA CHAVEZ', 2, 'Partido Liberal'), (16, 'CRISTHIAM JOSUE HERNANDEZ SAAVEDRA', 3, 'Partido Liberal'), (16, 'NORMA ARACELY AGUILAR CHACON', 4, 'Partido Liberal'), (16, 'MARLON GAMALIEL SANTOS BUESO', 5, 'Partido Liberal'), (16, 'EVELIN DANELIA RIOS DIAZ', 6, 'Partido Liberal'), (16, 'ALEX FABRICIO PORTILLO ARITA', 7, 'Partido Liberal'),
    -- LIBRE Copán
    (17, 'ISIS CAROLINA CUELLAR ERAZO', 1, 'LIBRE'), (17, 'EDUARDO JOSE ELVIR FERRUFINO', 2, 'LIBRE'), (17, 'BRENDA CECILIA SANTOS', 3, 'LIBRE'), (17, 'JORGE ENRIQUE LOPEZ LOPEZ', 4, 'LIBRE'), (17, 'CARLOS ROBERTO ALFARO CRUZ', 5, 'LIBRE'), (17, 'ZOILA MARILU ESPINOZA ALVARADO', 6, 'LIBRE'), (17, 'CARLOTA MORAN ESPINOZA', 7, 'LIBRE'),
    -- PSH Copán
    (18, 'DIDIER JAIR LOPEZ ESPINOZA', 1, 'PSH'), (18, 'NANCY JIESSEL DERAS PORTILLO', 2, 'PSH'), (18, 'HELEN ESKARLET PINTO ANDRADE', 3, 'PSH'), (18, 'MARLON ALBERTO ESCOBAR SU', 4, 'PSH'), (18, 'NELSON LEMUEL PONTAZA FAJARDO', 5, 'PSH'), (18, 'FRANKLIN ADONAY RAMOS AGUILAR', 6, 'PSH'), (18, 'EVELYN ESCOBAR GARCIA', 7, 'PSH'),
    -- DC Copán
    (19, 'MARIA MAGDALENA ORELLANA JIMENEZ', 1, 'DC'), (19, 'JOSE ADOLFO MEJIA', 2, 'DC'), (19, 'DENIS MIGUEL CACERES CACERES', 3, 'DC'), (19, 'MARIA GRISELDA ARITA', 4, 'DC'), (19, 'KEIDY NOHEMI DOMINGUEZ', 5, 'DC'), (19, 'PEDRO FRANCISCO ESPINOZA MIRANDA', 6, 'DC'), (19, 'YADIRA LIZETH ESPINOZA MOLINA', 7, 'DC'),

-- CORTÉS (20 diputados) - Planillas 20-23
    -- Partido Liberal Cortés (20 candidatos)
    (20, 'CARLOS ALBERTO UMAÑA DAVID', 1, 'Partido Liberal'), (20, 'GLORIA YASMIN MEZA ERAZO', 2, 'Partido Liberal'), (20, 'ALEJANDRA VALLECILLO PAVON', 3, 'Partido Liberal'), (20, 'MARLON GUILLERMO LARA ORELLANA', 4, 'Partido Liberal'), (20, 'SANDRA CAROLINA COLEMAN MILIAN', 5, 'Partido Liberal'), (20, 'ROBERTO PINEDA CHACON', 6, 'Partido Liberal'), (20, 'FERNANDO ENRIQUE CASTRO VALLE', 7, 'Partido Liberal'), (20, 'THIRSA GABRIELA LOPEZ SOLIS', 8, 'Partido Liberal'), (20, 'WENCESLAO LARA ORELLANA', 9, 'Partido Liberal'), (20, 'NORMAN ALBERTO JIMENEZ', 10, 'Partido Liberal'), (20, 'OSCAR RENE MUÑOZ CRUZ', 11, 'Partido Liberal'), (20, 'MARIA DEL CARMEN ANDRADE ZEPEDA', 12, 'Partido Liberal'), (20, 'GLORIA KAREN CARRANZA AMAYA', 13, 'Partido Liberal'), (20, 'KARLA CORITZA ANDRADE VELASQUEZ', 14, 'Partido Liberal'), (20, 'JOSE DANIEL PAZZETTI PAZ', 15, 'Partido Liberal'), (20, 'SANDRA DEL SOCORRO DIAZ', 16, 'Partido Liberal'), (20, 'JOSE RAMON MARTINEZ RODRIGUEZ', 17, 'Partido Liberal'), (20, 'NOEL DAVID INOCENTE DERAS', 18, 'Partido Liberal'), (20, 'LEIBY MELISSA TORRES ZALAVARRIA', 19, 'Partido Liberal'), (20, 'ARMANDO BARDALES PAZ', 20, 'Partido Liberal'),
    -- Partido Nacional Cortés (20 candidatos)
    (21, 'JOSE JAAR MUDENAT', 1, 'Partido Nacional'), (21, 'ROBERTO ENRIQUE COSENZA HERNANDEZ', 2, 'Partido Nacional'), (21, 'DAISY MARIA ANDONIE LOPEZ', 3, 'Partido Nacional'), (21, 'ALBERTO CHEDRANI CASTAÑEDA', 4, 'Partido Nacional'), (21, 'CINTHYA DAYANARA HAWIT FLORES', 5, 'Partido Nacional'), (21, 'BRENDA MERCEDES FLORES SERRANO', 6, 'Partido Nacional'), (21, 'SANTOS ROBERTO PEÑA ENAMORADO', 7, 'Partido Nacional'), (21, 'JOHANNA GORETTY COSTA ARITA', 8, 'Partido Nacional'), (21, 'SHAARON ELIZABETH IZAGUIRRE HEDMAN', 9, 'Partido Nacional'), (21, 'SANDRA NOHEMY RAMIREZ VILLANUEVA', 10, 'Partido Nacional'), (21, 'KAROL IVETTE PINEDA HERNANDEZ', 11, 'Partido Nacional'), (21, 'JESSICA WALDINA ORDOÑEZ BARDALES', 12, 'Partido Nacional'), (21, 'JUAN CARLOS SEGURA GALDAMEZ', 13, 'Partido Nacional'), (21, 'NANCY DEL CARMEN MORAN VASQUEZ', 14, 'Partido Nacional'), (21, 'JORGE EDWIN OSEGUERA', 15, 'Partido Nacional'), (21, 'FERNANDO ALONSO METZGEN MOREL', 16, 'Partido Nacional'), (21, 'MARVIN JOSUE GOMEZ ZAPATA', 17, 'Partido Nacional'), (21, 'JOSE LUIS ARGUETA RAMIREZ', 18, 'Partido Nacional'), (21, 'SUSY YANETH VIJIL LOPEZ', 19, 'Partido Nacional'), (21, 'NAHAMAN HUMBERTO GONZALEZ AVILA', 20, 'Partido Nacional'),
    -- LIBRE Cortés (20 candidatos)
    (22, 'LINDA FRANCES DONAIRE PORTILLO', 1, 'LIBRE'), (22, 'SCHERLY MELISSA ARRIAGA GOMEZ', 2, 'LIBRE'), (22, 'DUNIA YADIRA JIMENEZ AGUILAR', 3, 'LIBRE'), (22, 'RITA MARIA ZUNIGA MEMBREÑO', 4, 'LIBRE'), (22, 'MAURICIO ANTONIO CASTELLANOS QUIROZ', 5, 'LIBRE'), (22, 'IRIS YANETH PINEDA PAZ', 6, 'LIBRE'), (22, 'HECTOR SAMUEL MADRID SABILLON', 7, 'LIBRE'), (22, 'VICTALINA MONTENEGRO PINEDA', 8, 'LIBRE'), (22, 'LUIS ROLANDO REDONDO GUIFARRO', 9, 'LIBRE'), (22, 'RAMON ENRIQUE BARRIOS MALDONADO', 10, 'LIBRE'), (22, 'NETZER EDU MEJIA HERNANDEZ', 11, 'LIBRE'), (22, 'HEIDY GISSELL LAINEZ PONCE', 12, 'LIBRE'), (22, 'GLADIS ESMERALDA DELCID NIETO', 13, 'LIBRE'), (22, 'ALMA ISABEL MURILLO GABARRETE', 14, 'LIBRE'), (22, 'MARCO ANTONIO CASTELLANOS HERRERA', 15, 'LIBRE'), (22, 'HEBER FELIPE CRUZ LARA', 16, 'LIBRE'), (22, 'NATIVIDAD REYES RODRIGUEZ', 17, 'LIBRE'), (22, 'KENNIA CAROLINA ORDOÑEZ ALFARO', 18, 'LIBRE'), (22, 'WILMER LEONID EUCEDA MERAZ', 19, 'LIBRE'), (22, 'JOSE RICARDO GAMERO PAZ', 20, 'LIBRE'),
    -- PSH Cortés (20 candidatos)
    (23, 'ROLANDO CONTRERAS MENDOZA', 1, 'PSH'), (23, 'KEILYN STEPHANY LOPEZ GUZMAN', 2, 'PSH'), (23, 'ROGER ROSALES CHAVARRIA', 3, 'PSH'), (23, 'KITZIA SAYONARA FLORES RIVAS', 4, 'PSH'), (23, 'JERRY MEL DIAZ MURILLO', 5, 'PSH'), (23, 'ROBERTO DE JESUS CONTRERAS ESCOBAR', 6, 'PSH'), (23, 'ELIZABETH CONTRERAS', 7, 'PSH'), (23, 'MARIA MAGDALENA LEIVA MEJIA', 8, 'PSH'), (23, 'VICTORIA CAROLINA AGUILAR HOUSE', 9, 'PSH'), (23, 'DORIS YAMILETH CASTILLO ESTEVEZ', 10, 'PSH'), (23, 'BELKIS NOHEMY RODRIGUEZ BLANCO', 11, 'PSH'), (23, 'MOISES DE JESUS CANAHUATI TURCIOS', 12, 'PSH'), (23, 'KAREN VANESSA MARTINEZ BARAHONA', 13, 'PSH'), (23, 'RUBEN ORLANDO GARCIA FAJARDO', 14, 'PSH'), (23, 'JERONIMO MARTINEZ ROSALES', 15, 'PSH'), (23, 'DEYADIRA ELIZABETH ALGUERA CABRERA', 16, 'PSH'), (23, 'ELVIN FERNANDO LARIOS VELASQUEZ', 17, 'PSH'), (23, 'JOSE NATIVIDAD VASQUEZ CRUZ', 18, 'PSH'), (23, 'BLANCA MIRIAN CASTRO CRUZ', 19, 'PSH'), (23, 'HEBER JOSUE MALDONADO MEJIA', 20, 'PSH'),

-- CHOLUTECA (9 diputados) - Planillas 24-28
    -- Partido Nacional Choluteca
    (24, 'DILER MAURICIO MARTINEZ HERNANDEZ', 1, 'Partido Nacional'), (24, 'CARLOS ROBERTO LEDEZMA CASCO', 2, 'Partido Nacional'), (24, 'JAVIER ALEJANDRO MENDIETA SERVELLON', 3, 'Partido Nacional'), (24, 'ILEANA NAZARETH VELASQUEZ ORDOÑEZ', 4, 'Partido Nacional'), (24, 'EDGARDO HERNAN LOUCEL AGUILERA', 5, 'Partido Nacional'), (24, 'FRANCIS YOLANDA ARGEÑAL ECHENIQUE', 6, 'Partido Nacional'), (24, 'YOSELIN STEPFANIE CABRERA VASQUEZ', 7, 'Partido Nacional'), (24, 'MARIA ORFILIA CHINCHILLA CHACON', 8, 'Partido Nacional'), (24, 'IGNACIO ANTONIO MORENO SEVILLA', 9, 'Partido Nacional'),
    -- Partido Liberal Choluteca
    (25, 'YURY CRISTHIAN SABAS GUTIERREZ', 1, 'Partido Liberal'), (25, 'ALEX REMBERTO ORDOÑEZ ORDOÑEZ', 2, 'Partido Liberal'), (25, 'LUIS FERNANDO COELLO', 3, 'Partido Liberal'), (25, 'MENFFIS SAMANTHA VALLADARES SANCHEZ', 4, 'Partido Liberal'), (25, 'DAVID CARRANZA OCHOA', 5, 'Partido Liberal'), (25, 'KAREN GISSEL TALAVERA PONCE', 6, 'Partido Liberal'), (25, 'AMILCAR REINALDO RIVAS', 7, 'Partido Liberal'), (25, 'SANDRA RAQUEL ROJAS AVILA', 8, 'Partido Liberal'), (25, 'LUZ ARMIDA ALVAREZ CHAVEZ', 9, 'Partido Liberal'),
    -- LIBRE Choluteca
    (26, 'LUIS ENRIQUE ORTEGA SANCHEZ', 1, 'LIBRE'), (26, 'NIDIA GISSELA CASTILLO FUNEZ', 2, 'LIBRE'), (26, 'EDGARDO ALCIDES VARGAS PADILLA', 3, 'LIBRE'), (26, 'LUIS GEOVANY MARTINEZ SANCHEZ', 4, 'LIBRE'), (26, 'GUSTAVO ALBERTO CACERES BANEGAS', 5, 'LIBRE'), (26, 'ENRIQUE EDUARDO ARIAS GUILLEN', 6, 'LIBRE'), (26, 'VICTOR ALFONSO GALO MARADIAGA', 7, 'LIBRE'), (26, 'YASMINA LIZET FUENTES FONSECA', 8, 'LIBRE'), (26, 'MELIDA ELIZABETH HERRERA NUÑEZ', 9, 'LIBRE'),
    -- DC Choluteca
    (27, 'JOSE LUCIANO ORTIZ RODRIGUEZ', 1, 'DC'), (27, 'CARLOS ALBERTO CARRANZA ESTRADA', 2, 'DC'), (27, 'DARIELA ALEJANDRA GUANDIQUE BOCK', 3, 'DC'), (27, 'JAVIER MAURICIO AGUILAR MONTOYA', 4, 'DC'), (27, 'BESSY XIOMARA CONTRERAS FLORES', 5, 'DC'), (27, 'JIMY YONI MARADIAGA BAQUEDANO', 6, 'DC'), (27, 'KELVIN FARID RODRIGUEZ MARTINEZ', 7, 'DC'), (27, 'KATHERIN DARIELA CASTILLO MENDEZ', 8, 'DC'), (27, 'DILEYLA VANESSA ESPINOZA', 9, 'DC'),
    -- PSH Choluteca  
    (28, 'MERLIN MOISES CORRALES ORDOÑEZ', 1, 'PSH'), (28, 'JANORY CHRISDEL RAUDALES VIERA', 2, 'PSH'), (28, 'FABIOLA LOPEZ CACERES', 3, 'PSH'), (28, 'YELSON TEODORO AGUILAR FLORES', 4, 'PSH'), (28, 'ALEX ENRIQUE LOPEZ SANCHEZ', 5, 'PSH'), (28, 'GLORIA AZUCENA MARTINEZ GUILLEN', 6, 'PSH'), (28, 'GERSON HERNANDEZ OVIEDO', 7, 'PSH'), (28, 'YENIS KARINA AGUIRRE MEJIA', 8, 'PSH'), (28, 'ARIEL HUMBERTO SAUCEDA MONDRAGON', 9, 'PSH'),

-- EL PARAÍSO (6 diputados) - Planillas 29-33
    -- Partido Nacional El Paraíso
    (29, 'HIUDY GABRIELA MORALES AGUILAR', 1, 'Partido Nacional'), (29, 'GUSTAVO ADOLFO GONZALEZ AGUILAR', 2, 'Partido Nacional'), (29, 'WALTER ANTONIO CHAVEZ HERNANDEZ', 3, 'Partido Nacional'), (29, 'JENNIFER LARISSA PALMA AVILA', 4, 'Partido Nacional'), (29, 'IDANIA SUYAPA DOBLADO BANEGAS', 5, 'Partido Nacional'), (29, 'JOSE ANTONIO URRUTIA MARADIAGA', 6, 'Partido Nacional'),
    -- LIBRE El Paraíso
    (30, 'JOHN MILTON GARCIA FLORES', 1, 'LIBRE'), (30, 'EVER NAZAEL AGUILAR AGUILAR', 2, 'LIBRE'), (30, 'MARIO RAFAEL ARGEÑAL MEDINA', 3, 'LIBRE'), (30, 'OLGA RUTH FLORES SALAZAR', 4, 'LIBRE'), (30, 'NANCY ROSANA BARAHONA SALGADO', 5, 'LIBRE'), (30, 'MELVIN ALEXANDER IZAGUIRRE NUÑEZ', 6, 'LIBRE'),
    -- Partido Liberal El Paraíso
    (31, 'PEDRO MENDOZA FLORES', 1, 'Partido Liberal'), (31, 'YESICA LORENA CANO FLORES', 2, 'Partido Liberal'), (31, 'MARIO EDGARDO SEGURA AROCA', 3, 'Partido Liberal'), (31, 'PABLO HUMBERTO CARRASCO ASCENCIO', 4, 'Partido Liberal'), (31, 'MELVIN MATIAS BETANCO VARGAS', 5, 'Partido Liberal'), (31, 'EMILIO CABRERA CABRERA', 6, 'Partido Liberal'),
    -- PSH El Paraíso
    (32, 'JACOBO ADOLFO FLORES', 1, 'PSH'), (32, 'KARLA PRICILA ORTIZ', 2, 'PSH'), (32, 'SANTOS ERNESTO VELASQUEZ MEDINA', 3, 'PSH'), (32, 'JOSE RAMON SALCEDO LANDAVERDE', 4, 'PSH'), (32, 'YESENIA DAMARIS CLAROS DUARTE', 5, 'PSH'), (32, 'ELBA NUBIA MIGUEL', 6, 'PSH'),
    -- DC El Paraíso
    (33, 'MARLON ROBERTO PONCE', 1, 'DC'), (33, 'JAVIER AMILCAR MATUTE VELASQUEZ', 2, 'DC'), (33, 'LOURDES KARINA SANCHEZ', 3, 'DC'), (33, 'BREISY YORLENY CARRANZA VARGAS', 4, 'DC'), (33, 'GENRRY FABRICIO ESPINAL LOPEZ', 5, 'DC'), (33, 'LEYDIS SARAHI PEREZ CARRANZA', 6, 'DC'),

-- FRANCISCO MORAZÁN (23 diputados) - Planillas 34-38
    -- Partido Nacional Francisco Morazán (23 candidatos)
    (34, 'KILVETT ZABDIEL JOSSUA BERTRAND BARRIENTOS', 1, 'Partido Nacional'), (34, 'ARNOLD DANIEL BURGOS BORJAS', 2, 'Partido Nacional'), (34, 'ADOLFO RAQUEL PINEDA', 3, 'Partido Nacional'), (34, 'LISSI MARCELA MATUTE CANO', 4, 'Partido Nacional'), (34, 'JOHANA GUICEL BERMUDEZ LACAYO', 5, 'Partido Nacional'), (34, 'SARA ELIZABETH ESTRADA ZAVALA', 6, 'Partido Nacional'), (34, 'MARIA JOSE SOSA ROSALES', 7, 'Partido Nacional'), (34, 'OSWALDO JOSE RAMOS AGUILAR', 8, 'Partido Nacional'), (34, 'FABIOLA LUCILA ROSA VIGIL', 9, 'Partido Nacional'), (34, 'SUYAPA SEHAM MORALES VALERIANO', 10, 'Partido Nacional'), (34, 'ANTONIO CESAR RIVERA CALLEJAS', 11, 'Partido Nacional'), (34, 'KIMBERLY SARAI GUEVARA MIRALDA', 12, 'Partido Nacional'), (34, 'EDWIN JAVIER CRUZ PERDOMO', 13, 'Partido Nacional'), (34, 'JOSE LUIS ARGEÑAL VALLADARES', 14, 'Partido Nacional'), (34, 'YASSER ABDALAH HANDAL CARCAMO', 15, 'Partido Nacional'), (34, 'KEVYN DAVID SANDOVAL PADILLA', 16, 'Partido Nacional'), (34, 'MIRIAM ELISABETH TORRES MEJIA', 17, 'Partido Nacional'), (34, 'WALESKA JACKELINE ZELAYA SANCHEZ', 18, 'Partido Nacional'), (34, 'GRACE MARIELA SIERRA CRUZ', 19, 'Partido Nacional'), (34, 'OSKAR SALOMON FARAJ HENRIQUEZ', 20, 'Partido Nacional'), (34, 'KATERIN JAZMIN SALGADO QUIROZ', 21, 'Partido Nacional'), (34, 'LOURDES JANETH MEDINA', 22, 'Partido Nacional'), (34, 'ALBERTO ANTONIO AVILEZ FLORES', 23, 'Partido Nacional'),
    -- Partido Liberal Francisco Morazán (23 candidatos)
    (35, 'IROSKA LINDALY ELVIR FLORES', 1, 'Partido Liberal'), (35, 'SARAI PAMELA ESPINAL LOPEZ', 2, 'Partido Liberal'), (35, 'EDGARDO RASHID MEJIA GIANNINI', 3, 'Partido Liberal'), (35, 'JOSE SALOMON NAZAR ORDOÑEZ', 4, 'Partido Liberal'), (35, 'JHOSY SADDAM TOSCANO RAMIREZ', 5, 'Partido Liberal'), (35, 'ALIA NIÑO KAFATY', 6, 'Partido Liberal'), (35, 'LUZ ERNESTINA MEJIA PORTILLO', 7, 'Partido Liberal'), (35, 'MILAGROS DE JESUS GONZALEZ ZELAYA', 8, 'Partido Liberal'), (35, 'RAFAEL ANTONIO CANALES GIRBAL', 9, 'Partido Liberal'), (35, 'KARLA LIZETH ROMERO DAVILA', 10, 'Partido Liberal'), (35, 'BESAYDA SARAHI VASQUEZ RODRIGUEZ', 11, 'Partido Liberal'), (35, 'SANDRA MARIBEL FLORES ELVIR', 12, 'Partido Liberal'), (35, 'MAXIMINO GERMAN LOBO MUNGUIA', 13, 'Partido Liberal'), (35, 'KATHERINE ALEJANDRA HERNANDEZ PALENCIA', 14, 'Partido Liberal'), (35, 'YADIRA WALESKA CALIX GUERRERO', 15, 'Partido Liberal'), (35, 'BERNARDO BENJAMIN ANARIBA TOBAR', 16, 'Partido Liberal'), (35, 'LESLY YAQUELIN LOPEZ CORTES', 17, 'Partido Liberal'), (35, 'LUIS FERNANDO REYES PONCE', 18, 'Partido Liberal'), (35, 'MARTHA PATRICIA HERNANDEZ IZAGUIRRE', 19, 'Partido Liberal'), (35, 'MANUEL ENRIQUE ANDINO CALIX', 20, 'Partido Liberal'), (35, 'SALVADOR VIDESMUNDO CABRERA REYES', 21, 'Partido Liberal'), (35, 'WILFREDO GARCIA GODOY', 22, 'Partido Liberal'), (35, 'RAUL ALEXIS CHACON MEDINA', 23, 'Partido Liberal'),
    -- LIBRE Francisco Morazán (23 candidatos)
    (36, 'GUSTAVO ENRIQUE GONZALEZ MALDONADO', 1, 'LIBRE'), (36, 'KRITZA JERLIN PEREZ GALLEGOS', 2, 'LIBRE'), (36, 'HUGO ROLANDO NOE PINO', 3, 'LIBRE'), (36, 'LUCY MICHELL GUERRERO PAZ', 4, 'LIBRE'), (36, 'CLARA MARISABEL LOPEZ PEREZ', 5, 'LIBRE'), (36, 'CARLOS EDUARDO REINA GARCIA', 6, 'LIBRE'), (36, 'DIEGO JAVIER SANCHEZ CUEVA', 7, 'LIBRE'), (36, 'CARMEN HAYDEE LOPEZ FLORES', 8, 'LIBRE'), (36, 'JARI DIXON HERRERA HERNANDEZ', 9, 'LIBRE'), (36, 'JUAN ALBERTO BARAHONA MEJIA', 10, 'LIBRE'), (36, 'ROCIO WALKIRIA SANTOS REYES', 11, 'LIBRE'), (36, 'MOHSEN YAMIR MELGHEM RAMOS', 12, 'LIBRE'), (36, 'MARCO ELIUD GIRON PORTILLO', 13, 'LIBRE'), (36, 'MARIO ORLANDO SUAZO LARA', 14, 'LIBRE'), (36, 'SUYAPA ALEJANDRINA ANDINO FLORES', 15, 'LIBRE'), (36, 'MIRIAM JANETH OSORTO OSORTO', 16, 'LIBRE'), (36, 'ANDRES ALFREDO CASTRO TURCIOS', 17, 'LIBRE'), (36, 'BEVERLY HAZEL ALEGRIA MOLINA', 18, 'LIBRE'), (36, 'JOSE MANUEL RODRIGUEZ ROSALES', 19, 'LIBRE'), (36, 'GERMAN OMAR ORTIZ CARRASCO', 20, 'LIBRE'), (36, 'REYNA SAMANTA CASILDO ALVAREZ', 21, 'LIBRE'), (36, 'MARITZA YAMILETH GONZALES JOYA', 22, 'LIBRE'), (36, 'GERMAN RENE VILLALOBO BARAHONA', 23, 'LIBRE'),
    -- DC Francisco Morazán (23 candidatos)
    (37, 'GODOFREDO FAJARDO RAMIREZ', 1, 'DC'), (37, 'KAREN YOHANA GUANDIQUE ESTRADA', 2, 'DC'), (37, 'FELICITO AVILA ORDOÑEZ', 3, 'DC'), (37, 'CEIDY CELESTE CASTAÑEDA CARBAJAL', 4, 'DC'), (37, 'DARIO MANUEL SANCHEZ DUQUE', 5, 'DC'), (37, 'ANGELICA MARIA MARTIN RODRIGUEZ', 6, 'DC'), (37, 'KATHERINE SOFIA GARCIA CASTRO', 7, 'DC'), (37, 'NORA DEL CARMEN SUAREZ BUSTAMANTE', 8, 'DC'), (37, 'ASHTRY MARISOL MOLINA AGUILERA', 9, 'DC'), (37, 'MADAI GISSELL VILLALOBOS LAINEZ', 10, 'DC'), (37, 'JOEL NEMUEL NUÑEZ ORDOÑEZ', 11, 'DC'), (37, 'ROSELL EDGARDO RAMOS RODRIGUEZ', 12, 'DC'), (37, 'RONALD ALONZO MATUS IZAGUIRRE', 13, 'DC'), (37, 'ANDREA SOFIA VALDEZ CERRATO', 14, 'DC'), (37, 'EVER ADALI VARGAZ USEDA', 15, 'DC'), (37, 'MILLY DAYANA FONSECA ESPINAL', 16, 'DC'), (37, 'SAMUEL ERNESTO CARBAJAL GUZMAN', 17, 'DC'), (37, 'HILDA NORBELIS ANDINO BARAHONA', 18, 'DC'), (37, 'ANDREA MELISSA SANCHEZ ESPINAL', 19, 'DC'), (37, 'FRANKLIN ALONSO AVILA CARRASCO', 20, 'DC'), (37, 'RONY ALBERTO MONCADA MIDENCE', 21, 'DC'), (37, 'LUIS ALBERTO ZELAYA MONCADA', 22, 'DC'), (37, 'JORGE ADAN LOPEZ GUZMAN', 23, 'DC'),
    -- PSH Francisco Morazán (23 candidatos)
    (38, 'JOSE CARLENTON DAVILA MONDRAGON', 1, 'PSH'), (38, 'CHARA MARGOTH GONZALEZ PERALTA', 2, 'PSH'), (38, 'GERMAN EDGARDO LEITZELAR HERNANDEZ', 3, 'PSH'), (38, 'LUIS DANIEL LEON COELLO', 4, 'PSH'), (38, 'NELSON JHOVANNY ALVAREZ ALVAREZ', 5, 'PSH'), (38, 'OMAR ANDRES GARCIA CALDERON', 6, 'PSH'), (38, 'JOSE MANUEL MATHEU AMAYA', 7, 'PSH'), (38, 'JOSUE VLADIMIR PERDOMO BANEGAS', 8, 'PSH'), (38, 'JOSUE DAVID CAÑADAS VALLADARES', 9, 'PSH'), (38, 'SAMMAI MONTSERRAT GIRON GONZALEZ', 10, 'PSH'), (38, 'ELIZABETH PALMA', 11, 'PSH'), (38, 'ADALID IRIAS MARTINEZ', 12, 'PSH'), (38, 'EIBY MARILYN CARRANZA SANCHEZ', 13, 'PSH'), (38, 'OLY RIVIANA AMADOR MARTINEZ', 14, 'PSH'), (38, 'MARTHA PAULINA RODAS LAINEZ', 15, 'PSH'), (38, 'ERADIA CERNA SANCHEZ', 16, 'PSH'), (38, 'KARLA YOJANA ARZU HENRIQUEZ', 17, 'PSH'), (38, 'LILIAN MELISSA AGUILAR ZELAYA', 18, 'PSH'), (38, 'LESLIE ALEJANDRA MEDINA TORRES', 19, 'PSH'), (38, 'GLORIA AMELIA GALVEZ GALINDO', 20, 'PSH'), (38, 'JOSE ROBERTO MOLINA CACERES', 21, 'PSH'), (38, 'SARAMELIA LEIVA OLIVA', 22, 'PSH'), (38, 'HECTOR ALCIDES LOPEZ RUIZ', 23, 'PSH'),

-- RESTO DE DEPARTAMENTOS (Gracias a Dios - Yoro) - Candidatos restantes
    -- Gracias a Dios (1 diputado) - Planillas 39-43
    (39, 'ERIKA CORINA URTECHO ECHEVERRIA', 1, 'Partido Liberal'), (40, 'KENNEDY CALDERON MATEO', 1, 'LIBRE'), (41, 'ENDER CASTELLON PEREZ', 1, 'Partido Nacional'), (42, 'KLENK BOLEAN ESTRADA', 1, 'PSH'), (43, 'JHONN KENEDY SANTOS MANISTER', 1, 'DC'),
    -- Intibucá (3 diputados) - Planillas 44-48  
    (44, 'VICTOR NAPOLEON AMADOR MORALES', 1, 'Partido Nacional'), (44, 'NELSON JAVIER MARQUEZ EUCEDA', 2, 'Partido Nacional'), (44, 'GENOVEVA JACKELINE MARAVILLA JONES', 3, 'Partido Nacional'), (45, 'MARIO AMILCAR PORTILLO CONTRERAS', 1, 'LIBRE'), (45, 'WENDY VANESSA NOCHEZ REYES', 2, 'LIBRE'), (45, 'NORMAN ANTONIO MARQUEZ DIAZ', 3, 'LIBRE'), (46, 'RUMY NAHYP BUESO MEZA', 1, 'Partido Liberal'), (46, 'EDY CRISTOBAL MEZA ESPINO', 2, 'Partido Liberal'), (46, 'DANIA EDITH PERDOMO FUNES', 3, 'Partido Liberal'), (47, 'MARIBEL DOMINGUEZ DOMINGUEZ', 1, 'PSH'), (47, 'ENRIQUES CHICAS', 2, 'PSH'), (47, 'ALEX FERNANDO HERNANDEZ SANCHEZ', 3, 'PSH'), (48, 'BAYRON EDUARDO ORELLANA SANTIAGO', 1, 'DC'), (48, 'KARLA PATRICIA MANUELES INESTROZA', 2, 'DC'), (48, 'MA ASCENSION MARTINES', 3, 'DC'),
    -- Islas de la Bahía (1 diputado) - Planillas 49-53
    (49, 'STEPHEN GARRETT GARCIA ARCH', 1, 'Partido Nacional'), (50, 'RAYMOND SAMUEL CHERINGTON JOHNSON', 1, 'Partido Liberal'), (51, 'RENE ALEXANDER NUÑEZ SAVOFF', 1, 'LIBRE'), (52, 'YESSY MINELI DIAZ MARTINEZ', 1, 'PSH'), (53, 'OMAR ROBERTO RAUDALES', 1, 'DC'),
    -- La Paz (3 diputados) - Planillas 54-58
    (54, 'JUAN JOSE ZERON GONZALEZ', 1, 'Partido Nacional'), (54, 'GABINO ARGUETA GALVEZ', 2, 'Partido Nacional'), (54, 'GLORIA MARIA SUYAPA ARGUETA HERRERA', 3, 'Partido Nacional'), (55, 'ALLAN JOEL PADILLA', 1, 'Partido Liberal'), (55, 'CAMILO ERNESTO ALVARADO MARTINEZ', 2, 'Partido Liberal'), (55, 'ESAU SALOMON GARCIA ARGUETA', 3, 'Partido Liberal'), (56, 'BAYRON EDUARDO BANEGAS MEJIA', 1, 'LIBRE'), (56, 'ENRIQUE BELPRAN MARTINEZ AVILA', 2, 'LIBRE'), (56, 'AYDEE MARICRUZ HERNANDEZ SORTO', 3, 'LIBRE'), (57, 'RONY ARIEL GAMEZ CASTILLO', 1, 'PSH'), (57, 'RITA SUSELLE SUAZO RUBIO', 2, 'PSH'), (57, 'JUAN FERNANDO ULLOA MEDINA', 3, 'PSH'), (58, 'FRANCISCO ROBERTO MARTINEZ MORALES', 1, 'DC'), (58, 'LUCIO BENITEZ', 2, 'DC'), (58, 'DEISY YAMILETH LAZO PINEDA', 3, 'DC'),
    -- Lempira (5 diputados) - Planillas 59-63
    (59, 'LENIN DAVID VALERIANO MENJIVAR', 1, 'Partido Nacional'), (59, 'WILSON ROLANDO PINEDA DIAZ', 2, 'Partido Nacional'), (59, 'JOSE VIRGILIO GARCIA ALDANA', 3, 'Partido Nacional'), (59, 'STEFFANY MISHELL ROSA GUEVARA', 4, 'Partido Nacional'), (59, 'ABRAHAM ALVARENGA URBINA', 5, 'Partido Nacional'), (60, 'SELVIN OCTAVIO MORALES BONILLA', 1, 'LIBRE'), (60, 'DANY LEONEL MURILLO DIAZ', 2, 'LIBRE'), (60, 'RYNA MARISOL VASQUEZ SANCHEZ', 3, 'LIBRE'), (60, 'JOSUE DAVID ENAMORADO MADRID', 4, 'LIBRE'), (60, 'YESTER OMAR MUÑOZ', 5, 'LIBRE'), (61, 'MARCO TULIO RODRIGUEZ GAVARRETE', 1, 'Partido Liberal'), (61, 'MARIO ENRIQUE CALIX LARA', 2, 'Partido Liberal'), (61, 'WENDY YAMILETH ZUNIGA MORENO', 3, 'Partido Liberal'), (61, 'JOSE EDMAN MUÑOZ GOMEZ', 4, 'Partido Liberal'), (61, 'ANDREA DE JESUS DEL CID GONZALEZ', 5, 'Partido Liberal'), (62, 'JESUS HUMBERTO ALVARADO GOMEZ', 1, 'PSH'), (62, 'LUCIA SANTOS SOLA', 2, 'PSH'), (62, 'JOSE LEON QUINTANILLA', 3, 'PSH'), (62, 'MARIA SANTOS SUATE', 4, 'PSH'), (62, 'JESUS QUINTANILLA BEJARANO', 5, 'PSH'), (63, 'ADRIAN JAFET CACERES TOBAR', 1, 'DC'), (63, 'MARIA ESTELA CORTEZ BENITEZ', 2, 'DC'), (63, 'OSCAR YOVANY DE DIOS CHAVEZ', 3, 'DC'), (63, 'LINDER EVENOR BOBADILLA REYES', 4, 'DC'), (63, 'MARIA EMERITA REYES BENITEZ', 5, 'DC'),
    -- Ocotepeque (2 diputados) - Planillas 64-68
    (64, 'TANIA GABRIELA PINTO PACHECO', 1, 'Partido Nacional'), (64, 'EDWIN EDUARDO CHINCHILLA', 2, 'Partido Nacional'), (65, 'FANI NOEMI SANTOS PORTILLO', 1, 'Partido Liberal'), (65, 'OSCAR NECTALY ROSA MALDONADO', 2, 'Partido Liberal'), (66, 'OSCAR ORLANDO CACERES RUIZ', 1, 'LIBRE'), (66, 'DIANA MARIA ERAZO ARITA', 2, 'LIBRE'), (67, 'ASLY NICOLLE AVELAR MELGAR', 1, 'PSH'), (67, 'ANGEL MARIA JIMENEZ ESCOBAR', 2, 'PSH'), (68, 'OLGA ESPERANZA CABRERA ARITA', 1, 'DC'), (68, 'BRAYAN JOSUE PORTILLO HERNANDEZ', 2, 'DC'),
    -- Olancho (7 diputados) - Planillas 69-73
    (69, 'KARLA PATRICIA FIGUEROA CARDOZA', 1, 'Partido Nacional'), (69, 'OSCAR EDUARDO RIVERA HENRIQUEZ', 2, 'Partido Nacional'), (69, 'CARLOS EDUARDO CANO MARTINEZ', 3, 'Partido Nacional'), (69, 'FRANCISCO ANTONIO LOPEZ CRUZ', 4, 'Partido Nacional'), (69, 'KARLA MARTINEZ PAGUADA', 5, 'Partido Nacional'), (69, 'ANGEL ANTONIO VELASQUEZ MAZZONI', 6, 'Partido Nacional'), (69, 'KARLA YOLANY BARAHONA ANTUNEZ', 7, 'Partido Nacional'), (70, 'RAFAEL LEONARDO SARMIENTO AGUIRIANO', 1, 'LIBRE'), (70, 'ANGEL DAVID SANDOVAL CERRATO', 2, 'LIBRE'), (70, 'MARCOS RAMIRO LOBO ROSALES', 3, 'LIBRE'), (70, 'ARMINDA URTECHO MIRALDA', 4, 'LIBRE'), (70, 'ENNIDE MARIELA AGUILAR VELASQUEZ', 5, 'LIBRE'), (70, 'JOSE NECTALY JUAREZ', 6, 'LIBRE'), (70, 'LARYSSA JOELY AGUIRRE ORTIZ', 7, 'LIBRE'), (71, 'SAMUEL ARMANDO DE JESUS GARCIA SALGADO', 1, 'Partido Liberal'), (71, 'GUSTAVO ADOLFO ROSA BARAHONA', 2, 'Partido Liberal'), (71, 'SINDY GABRIELA ORTIZ ZELAYA', 3, 'Partido Liberal'), (71, 'GERARDO ALFREDO HERNANDEZ MOYA', 4, 'Partido Liberal'), (71, 'AMANDA CAROLA BANEGAS GALLEGOS', 5, 'Partido Liberal'), (71, 'DIPSON MOSEL MIRALDA CALIX', 6, 'Partido Liberal'), (71, 'RUTH GUILLEN BONILLA', 7, 'Partido Liberal'), (72, 'KEYLA MERARY MOLINA MEJIA', 1, 'DC'), (72, 'JOSE JULIO BARAHONA OLIVA', 2, 'DC'), (72, 'JUAN CARLOS GONZALES', 3, 'DC'), (72, 'CESAR ORFILIO MEZA GOMEZ', 4, 'DC'), (72, 'GENSUYA NICOLLE MARTINEZ MUNGUIA', 5, 'DC'), (72, 'BLANCA ROSA HERNANDEZ PADILLA', 6, 'DC'), (72, 'DELBIN NOHEL OCHOA OCHOA', 7, 'DC'), (73, 'DARLIN LLANERY FLORES ZELAYA', 1, 'PSH'), (73, 'VIRGILIO DE JESUS HERNANDEZ', 2, 'PSH'), (73, 'GREISY AZUCENA GARCIA MEJIA', 3, 'PSH'), (73, 'KARLA NAYELI BANEGAS GONZALES', 4, 'PSH'), (73, 'KEILYN NOHELIA HERNANDEZ RUIZ', 5, 'PSH'), (73, 'ESMIN JESUS CASTRO HERNANDEZ', 6, 'PSH'), (73, 'FIDEL ERNESTO CERRATO HERNANDEZ', 7, 'PSH'),
    -- Santa Bárbara (9 diputados) - Planillas 74-78
    (74, 'EDGARDO ANTONIO CASAÑA MEJIA', 1, 'LIBRE'), (74, 'LUZ ANGELICA SMITH MEJIA', 2, 'LIBRE'), (74, 'SERGIO ARTURO CASTELLANOS PERDOMO', 3, 'LIBRE'), (74, 'GERMAN OSWALDO ALTAMIRANO DIAZ', 4, 'LIBRE'), (74, 'CRISTIAN DE JESUS HERNANDEZ DIAZ', 5, 'LIBRE'), (74, 'EUNICE EUNICE RAMIREZ MEJIA', 6, 'LIBRE'), (74, 'JUAN ANGEL LANZA SABILLON', 7, 'LIBRE'), (74, 'ANGEL ADELSO REYES AGUILAR', 8, 'LIBRE'), (74, 'AYLIN LIZETH TEJADA HERNANDEZ', 9, 'LIBRE'), (75, 'MARIO ORLANDO REYES MEJIA', 1, 'Partido Nacional'), (75, 'MARCOS BERTILIO PAZ SABILLON', 2, 'Partido Nacional'), (75, 'MARIO ALONSO PEREZ LOPEZ', 3, 'Partido Nacional'), (75, 'MARIA FERNANDA SANDRES UMANZOR', 4, 'Partido Nacional'), (75, 'MIRIAN AZUCENA VILLANUEVA SARMIENTO', 5, 'Partido Nacional'), (75, 'HECTOR DANILO PERDOMO PAZ', 6, 'Partido Nacional'), (75, 'CARMEN JUDITH HERNANDEZ LEIVA', 7, 'Partido Nacional'), (75, 'KELLYN IVETH ENAMORADO CHAVEZ', 8, 'Partido Nacional'), (75, 'KRISTO JORDI MEJIA FLORES', 9, 'Partido Nacional'), (76, 'JOSE ROLANDO SABILLON MUÑOZ', 1, 'Partido Liberal'), (76, 'RAMON ESTUARDO ENAMORADO RODRIGUEZ', 2, 'Partido Liberal'), (76, 'SANDRA MARIBEL MANCIA FAJARDO', 3, 'Partido Liberal'), (76, 'JOSE BENIGNO PINEDA FERNANDEZ', 4, 'Partido Liberal'), (76, 'NIXON GEOVANY LEIVA RAPALO', 5, 'Partido Liberal'), (76, 'JORGE ADALI PAREDES DUBON', 6, 'Partido Liberal'), (76, 'TEODOLINDA ANDERSON MEJIA', 7, 'Partido Liberal'), (76, 'PABLO ERICK BARDALES PINEDA', 8, 'Partido Liberal'), (76, 'EMILSON DARIO BORJAS PINEDA', 9, 'Partido Liberal'), (77, 'MARIA TERESA MEJIA VALLECILLO', 1, 'PSH'), (77, 'GLEDIS GARCIA', 2, 'PSH'), (77, 'JOSE DAVID MARTINEZ PERDOMO', 3, 'PSH'), (77, 'GLORIA ESPERANZA HERNANDEZ RODRIGUEZ', 4, 'PSH'), (77, 'JULIO NOLASCO', 5, 'PSH'), (77, 'NARCISA CABALLERO RAMIREZ', 6, 'PSH'), (77, 'MAINOR JAVIER TROCHEZ HERNANDEZ', 7, 'PSH'), (77, 'ALMA ESPERANZA GARCIA GARCIA', 8, 'PSH'), (77, 'NELSON CASTELLANOS CASTELLANOS', 9, 'PSH'), (78, 'DONAL ARIEL REYES SANTOS', 1, 'DC'), (78, 'LILIAM ELISABETH ORELLANA BRIONES', 2, 'DC'), (78, 'ELMER ENAMORADO MURILLO', 3, 'DC'), (78, 'BENIGNO CASTELLANOS CASTELLANOS', 4, 'DC'), (78, 'DONALDO PINEDA CASTELLANOS', 5, 'DC'), (78, 'ANGELA ALEJANDRINA CASTELLANOS TROCHEZ', 6, 'DC'), (78, 'MAYRA LIZETH BARDALES PEÑA', 7, 'DC'), (78, 'ELVIS NAHUN MEJIA MUÑOZ', 8, 'DC'), (78, 'DINA ELIZABETH ROGEL GUARDADO', 9, 'DC'),
    -- Valle (4 diputados) - Planillas 79-82
    (79, 'JOSE TOMAS ZAMBRANO MOLINA', 1, 'Partido Nacional'), (79, 'LESLY CAROLINA FLORES MENDEZ', 2, 'Partido Nacional'), (79, 'JOSUE WILDER MALDONADO VELASQUEZ', 3, 'Partido Nacional'), (79, 'JUAN ESTEBAN SUAZO LEMUS', 4, 'Partido Nacional'), (80, 'ALEX FABRICIO ZORTO GARCIA', 1, 'Partido Liberal'), (80, 'JOSE ALFREDO SAAVEDRA PAZ', 2, 'Partido Liberal'), (80, 'CARLOS ROBERTO CAMPOS MANZANARES', 3, 'Partido Liberal'), (80, 'ALEYDA ANTONIA ALVAREZ SIERRA', 4, 'Partido Liberal'), (81, 'JOSUE FABRICIO CARBAJAL SANDOVAL', 1, 'LIBRE'), (81, 'LEVIS JOEL GUTIERREZ RODRIGUEZ', 2, 'LIBRE'), (81, 'PAMELA MAXIMINA COELLO ALMENDAREZ', 3, 'LIBRE'), (81, 'PATRICIA GISSEL BUSTILLO MENDOZA', 4, 'LIBRE'), (82, 'HUGO OMAR HERNANDEZ CALDERON', 1, 'PSH'), (82, 'MIRNA ESPERANZA GALEA VELASQUEZ', 2, 'PSH'), (82, 'JELSON ADALY OSORIO ZAVALA', 3, 'PSH'), (82, 'KEYBI NAYELI TORRES GUTIERREZ', 4, 'PSH'),
    -- Yoro (9 diputados) - Planillas 83-87
    (83, 'MAXIMA ALEJANDRA BURGOS HERNANDEZ', 1, 'Partido Nacional'), (83, 'MILTON JESUS PUERTO OSEGUERA', 2, 'Partido Nacional'), (83, 'MARCO TULIO GAMEZ DIAZ', 3, 'Partido Nacional'), (83, 'EDER LEONEL MEJIA LAINEZ', 4, 'Partido Nacional'), (83, 'LIDIA YOLANDA CASCO MARTINEZ', 5, 'Partido Nacional'), (83, 'JORGE ALBERTO GAMEZ ROBLES', 6, 'Partido Nacional'), (83, 'JOSE OTILIO VASQUEZ', 7, 'Partido Nacional'), (83, 'MARIA DEL CARMEN DOMINGUEZ MEJIA', 8, 'Partido Nacional'), (83, 'LESVI GLORINDA RAMOS', 9, 'Partido Nacional'), (84, 'MARCO AURELIO TINOCO URBINA', 1, 'Partido Liberal'), (84, 'GERLEN AMANDA BONILLA LOPEZ', 2, 'Partido Liberal'), (84, 'LEONEL LOPEZ ORELLANA', 3, 'Partido Liberal'), (84, 'DANIELA MARQUEZ RIVERA', 4, 'Partido Liberal'), (84, 'CHRISTIAN GABRIEL QUESADA ARMIJO', 5, 'Partido Liberal'), (84, 'JOHANNA JULIETH BUESO SANCHEZ', 6, 'Partido Liberal'), (84, 'MARLON YOVANNY MEJIA ULLOA', 7, 'Partido Liberal'), (84, 'MARLEN ARGENTINA AVILA DAVILA', 8, 'Partido Liberal'), (84, 'DAVID ANTONIO DURON SUAREZ', 9, 'Partido Liberal'), (85, 'FELIPE TOMAS PONCE ISAULA', 1, 'LIBRE'), (85, 'JENIFFER ALEXANDRA DIAZ PONCE', 2, 'LIBRE'), (85, 'MELBI CONCEPCION ORTIZ MURILLO', 3, 'LIBRE'), (85, 'ARAMINTA PEREIRA ORTEGA', 4, 'LIBRE'), (85, 'CARLOS ANDRES DIAZ BONILLA', 5, 'LIBRE'), (85, 'VICTOR MANUEL MATAMOROS VASQUEZ', 6, 'LIBRE'), (85, 'EBLIN VANESSA SOTO', 7, 'LIBRE'), (85, 'ZARVIA NOHEMI AMAYA TURCIOS', 8, 'LIBRE'), (85, 'EDGAR ANDRES MARTINEZ SEVILLA', 9, 'LIBRE'), (86, 'CARLOS DAVID FERRERA ASSAF', 1, 'PSH'), (86, 'LIBERATO MADRID CASTRO', 2, 'PSH'), (86, 'DAMARY VENTURA FIGUEROA URBINA', 3, 'PSH'), (86, 'OSMAN JEOVANIE CASTRO MEJIA', 4, 'PSH'), (86, 'LUZ MARIA MEJIA MEDINA', 5, 'PSH'), (86, 'JUAN CARLOS HERNANDEZ CACERES', 6, 'PSH'), (86, 'SINIA GABRIELA RODRIGUEZ MORALES', 7, 'PSH'), (86, 'KARLA YESENIA AMAYA CHAVARRIA', 8, 'PSH'), (86, 'OSCAR ALFREDO PEÑA AVILA', 9, 'PSH'), (87, 'JAVIER OMAR CASTILLO MOLINA', 1, 'DC'), (87, 'JUAN FRANCISCO SANTOS BONILLA', 2, 'DC'), (87, 'ROSA ESTELA LANDA RODRIGUEZ', 3, 'DC'), (87, 'ARLENIS DE JESUS GARCIA CARCAMO', 4, 'DC'), (87, 'GUADALUPE MEJIA RUIZ', 5, 'DC'), (87, 'LILIAN HAYDEE CARCAMO', 6, 'DC'), (87, 'NICOLAS INESTROZA NARVAEZ', 7, 'DC'), (87, 'GERARDO ANIBAL HERNANDEZ MENCIA', 8, 'DC'), (87, 'WUILMER OMAR CASTRO FIGUEROA', 9, 'DC')
ON DUPLICATE KEY UPDATE nombre_completo = VALUES(nombre_completo);

-- Insertar candidatos presidenciales para Honduras 2025
INSERT INTO planillas (departamento_id, partido, nivel_eleccion) VALUES
    (NULL, 'Partido Liberal', 'presidencial'),
    (NULL, 'Partido Nacional', 'presidencial'), 
    (NULL, 'LIBRE', 'presidencial'),
    (NULL, 'PSH', 'presidencial'),
    (NULL, 'DC', 'presidencial')
ON DUPLICATE KEY UPDATE partido = VALUES(partido);

-- Candidatos presidenciales reales Honduras 2025
INSERT INTO candidatos (planilla_id, nombre_completo, posicion, partido) VALUES
    -- Planilla Presidencial Liberal (estimado ID 87)
    (87, 'JORGE ANDRÉS CÁLIX LÓPEZ', 1, 'Partido Liberal'),
    (87, 'DORIS GUTIÉRREZ', 2, 'Partido Liberal'),
    -- Planilla Presidencial Nacional (estimado ID 88)  
    (88, 'NASRY JUAN ASFURA ZABLAH', 1, 'Partido Nacional'),
    (88, 'TOMÁS RAMÓN LOZANO', 2, 'Partido Nacional'),
    -- Planilla Presidencial LIBRE (estimado ID 89)
    (89, 'XIOMARA CASTRO DE ZELAYA', 1, 'LIBRE'),
    (89, 'SALVADOR NASRALLA', 2, 'LIBRE'),
    -- Planilla Presidencial PSH (estimado ID 90)
    (90, 'CARLOS HERNÁN ZELAYA ROJAS', 1, 'PSH'),
    (90, 'MARÍA LUISA BORJAS', 2, 'PSH'),
    -- Planilla Presidencial DC (estimado ID 91)
    (91, 'KELVIN AGUIRRE', 1, 'DC'),
    (91, 'ANA GARCÍA CARÍAS', 2, 'DC')
ON DUPLICATE KEY UPDATE nombre_completo = VALUES(nombre_completo);

INSERT INTO centros_votacion_exterior (pais, estado, ciudad, sector_electoral, juntas, nombre, direccion) VALUES
    ('Estados Unidos de América', 'Texas', 'Houston', 'Houston, TX', '19153, 19154', 'Princess Reception Hall', 'Princess Reception Hall, 16312 FM 529W, Houston, TX 77095.'),
    ('Estados Unidos de América', 'California', 'Los Ángeles', 'Los Angeles, CA', '19155', 'Church of Scientology of the Valley', '11455 Burbank Blvd, North Hollywood, CA 91601.'),
    ('Estados Unidos de América', 'Florida', 'Miami', 'Miami, FL', '19156, 19157', 'Hotel Holiday Inn', '7707 NW 103rd Street, Hialeah Gardens, FL 33016.'),
    ('Estados Unidos de América', 'Luisiana', 'New Orleans', 'New Orleans, LA', '19158', 'Apostolado Hispano', '2501 Maine Ave, Metairie, Louisiana 70003.'),
    ('Estados Unidos de América', 'Nueva York', 'New York', 'New York, NY', '19159', 'Our Lady of Sorrows, Roman Catholic Church', '104-11 37th Ave, Corona, NY 11368.'),
    ('Estados Unidos de América', 'Virginia', 'Washington D. C.', 'Washington D.C.', '19160, 19161', 'Hotel Comfort Inn', '6560 Loisdale Ct, Springfield, VA 22150.'),
    ('Estados Unidos de América', 'Georgia', 'Atlanta', 'Atlanta, GA', '19162', 'DCA Event Hall', '880 Indian Trail Rd Suite K, Lilburn, GA 30047.'),
    ('Estados Unidos de América', 'Illinois', 'Chicago', 'Chicago, IL', '19163', 'Hispanic Bible School', '7029 W Grand Ave, Chicago, IL 60707.'),
    ('Estados Unidos de América', 'Texas', 'Dallas', 'Dallas, TX', '19164', 'Salón de Eventos de Paris Beauty Salon Academy', '3160 Saturn Rd, Garland, TX 75041.'),
    ('Estados Unidos de América', 'Massachusetts', 'Boston', 'Boston, MA', '19165', 'La Colaborativa Survival Center', '63 Sixth Street, Chelsea, MA.'),
    ('Estados Unidos de América', 'California', 'San Francisco', 'San Francisco, CA', '19166', 'St Johns Presbyterian Church', '2727 College Ave, Berkeley, CA 94705.'),
    ('Estados Unidos de América', 'Carolina del Norte', 'Charlotte', 'Charlotte, NC', '19167', 'Hotel Fairfield Inn', '8540 East Independence Blvd, Charlotte, NC 28227.')
ON DUPLICATE KEY UPDATE
    sector_electoral = VALUES(sector_electoral),
    juntas = VALUES(juntas),
    nombre = VALUES(nombre),
    direccion = VALUES(direccion),
    actualizado_en = CURRENT_TIMESTAMP;


DELETE FROM candidatos
WHERE planilla_id IN (
    SELECT id FROM planillas WHERE tipo = 'alcaldia' AND municipio_id IN (228, 63, 64)
);

DELETE FROM planillas
WHERE tipo = 'alcaldia' AND municipio_id IN (228, 63, 64);


INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 15, 228, 'Alcaldía Juticalpa - DC', 'DC', 'Candidatos a alcalde y vicealcalde de Juticalpa por el partido DC', 'habilitada';

INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 15, 228, 'Alcaldía Juticalpa - Libre', 'Libre', 'Candidatos a alcalde y vicealcalde de Juticalpa por el partido Libre', 'habilitada';

INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 15, 228, 'Alcaldía Juticalpa - PINU', 'PINU', 'Candidatos a alcalde y vicealcalde de Juticalpa por el partido PINU', 'habilitada';

INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 15, 228, 'Alcaldía Juticalpa - Liberal', 'Liberal', 'Candidatos a alcalde y vicealcalde de Juticalpa por el partido Liberal', 'habilitada';

INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 15, 228, 'Alcaldía Juticalpa - Nacional', 'Nacional', 'Candidatos a alcalde y vicealcalde de Juticalpa por el partido Nacional', 'habilitada';

-- Candidatos Juticalpa
INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'Maria de jesus padilla carias', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 228 AND p.partido = 'DC';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'ever eduardo guillen rodriguez', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 228 AND p.partido = 'DC';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'victor manuel moreno torres', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 228 AND p.partido = 'Libre';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'martha argentina saenz rosales', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 228 AND p.partido = 'Libre';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'meyrida yaneth tejeda coleman', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 228 AND p.partido = 'PINU';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'francisco emilio rivera rivera', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 228 AND p.partido = 'PINU';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'jose guillermo trochez montalvan', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 228 AND p.partido = 'Liberal';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'Rolando antonio ordoñez montalvan', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 228 AND p.partido = 'Liberal';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'walner reginaldo castro rivera', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 228 AND p.partido = 'Nacional';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'mailin ibeth padilla rivera', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 228 AND p.partido = 'Nacional';


INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 5, 63, 'Alcaldía San Pedro Sula - DC', 'DC', 'Candidatos a alcalde y vicealcalde de San Pedro Sula por el partido DC', 'habilitada';

INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 5, 63, 'Alcaldía San Pedro Sula - Libre', 'Libre', 'Candidatos a alcalde y vicealcalde de San Pedro Sula por el partido Libre', 'habilitada';

INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 5, 63, 'Alcaldía San Pedro Sula - PINU', 'PINU', 'Candidatos a alcalde y vicealcalde de San Pedro Sula por el partido PINU', 'habilitada';

INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 5, 63, 'Alcaldía San Pedro Sula - Liberal', 'Liberal', 'Candidatos a alcalde y vicealcalde de San Pedro Sula por el partido Liberal', 'habilitada';

INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 5, 63, 'Alcaldía San Pedro Sula - Nacional', 'Nacional', 'Candidatos a alcalde y vicealcalde de San Pedro Sula por el partido Nacional', 'habilitada';

-- Candidatos San Pedro Sula
INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'jose delio boquin rapalo', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 63 AND p.partido = 'DC';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'jorge mendoza', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 63 AND p.partido = 'DC';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'rodoldo padilla', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 63 AND p.partido = 'Libre';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'mauricio ramos', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 63 AND p.partido = 'Libre';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'higiinio abarca', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 63 AND p.partido = 'PINU';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'rosa emilia mejia gutierrez', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 63 AND p.partido = 'PINU';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'roberto contreras mendoza', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 63 AND p.partido = 'Liberal';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'maritza anotnia soto portillo', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 63 AND p.partido = 'Liberal';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'yaudel burbara canahuati', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 63 AND p.partido = 'Nacional';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'jenny carolina fernandez erazo', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 63 AND p.partido = 'Nacional';


INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 5, 64, 'Alcaldía Choloma - DC', 'DC', 'Candidatos a alcalde y vicealcalde de Choloma por el partido DC', 'habilitada';

INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 5, 64, 'Alcaldía Choloma - Libre', 'Libre', 'Candidatos a alcalde y vicealcalde de Choloma por el partido Libre', 'habilitada';

INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 5, 64, 'Alcaldía Choloma - PINU', 'PINU', 'Candidatos a alcalde y vicealcalde de Choloma por el partido PINU', 'habilitada';

INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 5, 64, 'Alcaldía Choloma - Liberal', 'Liberal', 'Candidatos a alcalde y vicealcalde de Choloma por el partido Liberal', 'habilitada';

INSERT INTO planillas (tipo, departamento_id, municipio_id, nombre, partido, descripcion, estado)
SELECT 'alcaldia', 5, 64, 'Alcaldía Choloma - Nacional', 'Nacional', 'Candidatos a alcalde y vicealcalde de Choloma por el partido Nacional', 'habilitada';


INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'luis german miranda irias', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 64 AND p.partido = 'DC';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'rosa herminia', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 64 AND p.partido = 'DC';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'Gustavo antonio Mejia escobar', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 64 AND p.partido = 'Libre';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'juan molina', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 64 AND p.partido = 'Libre';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'elmer edgardo ortega', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 64 AND p.partido = 'PINU';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'Andrea isabel cartagena velasquez', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 64 AND p.partido = 'PINU';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'ramin edgardo miranda', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 64 AND p.partido = 'Liberal';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'clarissa giselle garcia talbott', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 64 AND p.partido = 'Liberal';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'carlos zerron', 'Alcalde', 1
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 64 AND p.partido = 'Nacional';

INSERT INTO candidatos (planilla_id, nombre, cargo, numero_candidato)
SELECT p.id, 'karla esperwanza escoto tosta', 'Vicealcalde', 2
FROM planillas p
WHERE p.tipo = 'alcaldia' AND p.municipio_id = 64 AND p.partido = 'Nacional';
