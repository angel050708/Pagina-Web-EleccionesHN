<?php
declare(strict_types=1);

/**
 * Se ejecuta un script  en la  terminal: php scripts/seed_locations.php
 */

require_once __DIR__ . '/../includes/database.php';

$pdo = db();

$departamentos = [
    ['codigo' => '01', 'nombre' => 'Atlántida', 'cabecera' => 'La Ceiba', 'diputados_cupos' => 8, 'candidatos' => 40],
    ['codigo' => '02', 'nombre' => 'Colón', 'cabecera' => 'Trujillo', 'diputados_cupos' => 4, 'candidatos' => 16],
    ['codigo' => '03', 'nombre' => 'Comayagua', 'cabecera' => 'Comayagua', 'diputados_cupos' => 7, 'candidatos' => 35],
    ['codigo' => '04', 'nombre' => 'Copán', 'cabecera' => 'Santa Rosa de Copán', 'diputados_cupos' => 7, 'candidatos' => 35],
    ['codigo' => '05', 'nombre' => 'Cortés', 'cabecera' => 'San Pedro Sula', 'diputados_cupos' => 20, 'candidatos' => 100],
    ['codigo' => '06', 'nombre' => 'Choluteca', 'cabecera' => 'Choluteca', 'diputados_cupos' => 9, 'candidatos' => 45],
    ['codigo' => '07', 'nombre' => 'El Paraíso', 'cabecera' => 'Yuscarán', 'diputados_cupos' => 6, 'candidatos' => 30],
    ['codigo' => '08', 'nombre' => 'Francisco Morazán', 'cabecera' => 'Distrito Central', 'diputados_cupos' => 23, 'candidatos' => 115],
    ['codigo' => '09', 'nombre' => 'Gracias a Dios', 'cabecera' => 'Puerto Lempira', 'diputados_cupos' => 1, 'candidatos' => 5],
    ['codigo' => '10', 'nombre' => 'Intibucá', 'cabecera' => 'La Esperanza', 'diputados_cupos' => 3, 'candidatos' => 15],
    ['codigo' => '11', 'nombre' => 'Islas de la Bahía', 'cabecera' => 'Roatán', 'diputados_cupos' => 1, 'candidatos' => 5],
    ['codigo' => '12', 'nombre' => 'La Paz', 'cabecera' => 'La Paz', 'diputados_cupos' => 3, 'candidatos' => 15],
    ['codigo' => '13', 'nombre' => 'Lempira', 'cabecera' => 'Gracias', 'diputados_cupos' => 5, 'candidatos' => 13],
    ['codigo' => '14', 'nombre' => 'Ocotepeque', 'cabecera' => 'Ocotepeque', 'diputados_cupos' => 2, 'candidatos' => 10],
    ['codigo' => '15', 'nombre' => 'Olancho', 'cabecera' => 'Juticalpa', 'diputados_cupos' => 7, 'candidatos' => 35],
    ['codigo' => '16', 'nombre' => 'Santa Bárbara', 'cabecera' => 'Santa Bárbara', 'diputados_cupos' => 9, 'candidatos' => 45],
    ['codigo' => '17', 'nombre' => 'Valle', 'cabecera' => 'Nacaome', 'diputados_cupos' => 4, 'candidatos' => 16],
    ['codigo' => '18', 'nombre' => 'Yoro', 'cabecera' => 'Yoro', 'diputados_cupos' => 9, 'candidatos' => 45],
];

$municipios = [
    'Atlántida' => [
        ['codigo' => '0101', 'nombre' => 'La Ceiba'],
        ['codigo' => '0102', 'nombre' => 'El Porvenir'],
        ['codigo' => '0103', 'nombre' => 'Esparta'],
        ['codigo' => '0104', 'nombre' => 'Jutiapa'],
        ['codigo' => '0105', 'nombre' => 'La Masica'],
        ['codigo' => '0106', 'nombre' => 'San Francisco'],
        ['codigo' => '0107', 'nombre' => 'Tela'],
        ['codigo' => '0108', 'nombre' => 'Arizona'],
    ],
    'Colón' => [
        ['codigo' => '0201', 'nombre' => 'Trujillo'],
        ['codigo' => '0202', 'nombre' => 'Balfate'],
        ['codigo' => '0203', 'nombre' => 'Iriona'],
        ['codigo' => '0204', 'nombre' => 'Limón'],
        ['codigo' => '0205', 'nombre' => 'Sabá'],
        ['codigo' => '0206', 'nombre' => 'Santa Fe'],
        ['codigo' => '0207', 'nombre' => 'Santa Rosa de Aguán'],
        ['codigo' => '0208', 'nombre' => 'Sonaguera'],
        ['codigo' => '0209', 'nombre' => 'Tocoa'],
        ['codigo' => '0210', 'nombre' => 'Bonito Oriental'],
    ],
    'Comayagua' => [
        ['codigo' => '0301', 'nombre' => 'Comayagua'],
        ['codigo' => '0302', 'nombre' => 'Ajuterique'],
        ['codigo' => '0303', 'nombre' => 'El Rosario'],
        ['codigo' => '0304', 'nombre' => 'Esquías'],
        ['codigo' => '0305', 'nombre' => 'Humuya'],
        ['codigo' => '0306', 'nombre' => 'La Libertad'],
        ['codigo' => '0307', 'nombre' => 'Lamaní'],
        ['codigo' => '0308', 'nombre' => 'La Trinidad'],
        ['codigo' => '0309', 'nombre' => 'Lejamaní'],
        ['codigo' => '0310', 'nombre' => 'Meámbar'],
        ['codigo' => '0311', 'nombre' => 'Minas de Oro'],
        ['codigo' => '0312', 'nombre' => 'Ojos de Agua'],
        ['codigo' => '0313', 'nombre' => 'San Jerónimo'],
        ['codigo' => '0314', 'nombre' => 'San José de Comayagua'],
        ['codigo' => '0315', 'nombre' => 'San José del Potrero'],
        ['codigo' => '0316', 'nombre' => 'San Luis'],
        ['codigo' => '0317', 'nombre' => 'San Sebastián'],
        ['codigo' => '0318', 'nombre' => 'Siguatepeque'],
        ['codigo' => '0319', 'nombre' => 'Villa de San Antonio'],
        ['codigo' => '0320', 'nombre' => 'Las Lajas'],
        ['codigo' => '0321', 'nombre' => 'Taulabé'],
    ],
    'Copán' => [
        ['codigo' => '0401', 'nombre' => 'Santa Rosa de Copán'],
        ['codigo' => '0402', 'nombre' => 'Cabañas'],
        ['codigo' => '0403', 'nombre' => 'Concepción'],
        ['codigo' => '0404', 'nombre' => 'Copán Ruinas'],
        ['codigo' => '0405', 'nombre' => 'Corquín'],
        ['codigo' => '0406', 'nombre' => 'Cucuyagua'],
        ['codigo' => '0407', 'nombre' => 'Dolores'],
        ['codigo' => '0408', 'nombre' => 'Dulce Nombre'],
        ['codigo' => '0409', 'nombre' => 'El Paraíso'],
        ['codigo' => '0410', 'nombre' => 'Florida'],
        ['codigo' => '0411', 'nombre' => 'La Jigua'],
        ['codigo' => '0412', 'nombre' => 'La Unión'],
        ['codigo' => '0413', 'nombre' => 'Nueva Arcadia'],
        ['codigo' => '0414', 'nombre' => 'San Agustín'],
        ['codigo' => '0415', 'nombre' => 'San Antonio'],
        ['codigo' => '0416', 'nombre' => 'San Jerónimo'],
        ['codigo' => '0417', 'nombre' => 'San José'],
        ['codigo' => '0418', 'nombre' => 'San Juan de Opoa'],
        ['codigo' => '0419', 'nombre' => 'San Nicolás'],
        ['codigo' => '0420', 'nombre' => 'San Pedro'],
        ['codigo' => '0421', 'nombre' => 'Santa Rita'],
        ['codigo' => '0422', 'nombre' => 'Trinidad de Copán'],
        ['codigo' => '0423', 'nombre' => 'Veracruz'],
    ],
    'Cortés' => [
        ['codigo' => '0501', 'nombre' => 'San Pedro Sula'],
        ['codigo' => '0502', 'nombre' => 'Choloma'],
        ['codigo' => '0503', 'nombre' => 'Omoa'],
        ['codigo' => '0504', 'nombre' => 'Puerto Cortés'],
        ['codigo' => '0505', 'nombre' => 'San Antonio de Cortés'],
        ['codigo' => '0506', 'nombre' => 'San Francisco de Yojoa'],
        ['codigo' => '0507', 'nombre' => 'San Manuel'],
        ['codigo' => '0508', 'nombre' => 'Santa Cruz de Yojoa'],
        ['codigo' => '0509', 'nombre' => 'Villanueva'],
        ['codigo' => '0510', 'nombre' => 'La Lima'],
        ['codigo' => '0511', 'nombre' => 'Pimienta'],
        ['codigo' => '0512', 'nombre' => 'Potrerillos'],
    ],
    'Choluteca' => [
        ['codigo' => '0601', 'nombre' => 'Choluteca'],
        ['codigo' => '0602', 'nombre' => 'Apacilagua'],
        ['codigo' => '0603', 'nombre' => 'Concepción de María'],
        ['codigo' => '0604', 'nombre' => 'Duyure'],
        ['codigo' => '0605', 'nombre' => 'El Corpus'],
        ['codigo' => '0606', 'nombre' => 'El Triunfo'],
        ['codigo' => '0607', 'nombre' => 'Marcovia'],
        ['codigo' => '0608', 'nombre' => 'Morolica'],
        ['codigo' => '0609', 'nombre' => 'Namasigüe'],
        ['codigo' => '0610', 'nombre' => 'Orocuina'],
        ['codigo' => '0611', 'nombre' => 'Pespire'],
        ['codigo' => '0612', 'nombre' => 'San Antonio de Flores'],
        ['codigo' => '0613', 'nombre' => 'San Isidro'],
        ['codigo' => '0614', 'nombre' => 'San José'],
        ['codigo' => '0615', 'nombre' => 'San Marcos de Colón'],
        ['codigo' => '0616', 'nombre' => 'Santa Ana de Yusguare'],
    ],
    'El Paraíso' => [
        ['codigo' => '0701', 'nombre' => 'Yuscarán'],
        ['codigo' => '0702', 'nombre' => 'Alauca'],
        ['codigo' => '0703', 'nombre' => 'Danlí'],
        ['codigo' => '0704', 'nombre' => 'El Paraíso'],
        ['codigo' => '0705', 'nombre' => 'Güinope'],
        ['codigo' => '0706', 'nombre' => 'Jacaleapa'],
        ['codigo' => '0707', 'nombre' => 'Liure'],
        ['codigo' => '0708', 'nombre' => 'Morocelí'],
        ['codigo' => '0709', 'nombre' => 'Oropolí'],
        ['codigo' => '0710', 'nombre' => 'Potrerillos'],
        ['codigo' => '0711', 'nombre' => 'San Antonio de Flores'],
        ['codigo' => '0712', 'nombre' => 'San Lucas'],
        ['codigo' => '0713', 'nombre' => 'San Matías'],
        ['codigo' => '0714', 'nombre' => 'Soledad'],
        ['codigo' => '0715', 'nombre' => 'Teupasenti'],
        ['codigo' => '0716', 'nombre' => 'Texiguat'],
        ['codigo' => '0717', 'nombre' => 'Trojes'],
        ['codigo' => '0718', 'nombre' => 'Vado Ancho'],
        ['codigo' => '0719', 'nombre' => 'Yauyupe'],
    ],
    'Francisco Morazán' => [
        ['codigo' => '0801', 'nombre' => 'Distrito Central'],
        ['codigo' => '0802', 'nombre' => 'Alubarén'],
        ['codigo' => '0803', 'nombre' => 'Cedros'],
        ['codigo' => '0804', 'nombre' => 'Curarén'],
        ['codigo' => '0805', 'nombre' => 'El Porvenir'],
        ['codigo' => '0806', 'nombre' => 'Guaimaca'],
        ['codigo' => '0807', 'nombre' => 'La Libertad'],
        ['codigo' => '0808', 'nombre' => 'La Venta'],
        ['codigo' => '0809', 'nombre' => 'Lepaterique'],
        ['codigo' => '0810', 'nombre' => 'Maraita'],
        ['codigo' => '0811', 'nombre' => 'Marale'],
        ['codigo' => '0812', 'nombre' => 'Nueva Armenia'],
        ['codigo' => '0813', 'nombre' => 'Ojojona'],
        ['codigo' => '0814', 'nombre' => 'Orica'],
        ['codigo' => '0815', 'nombre' => 'Reitoca'],
        ['codigo' => '0816', 'nombre' => 'Sabanagrande'],
        ['codigo' => '0817', 'nombre' => 'San Antonio de Oriente'],
        ['codigo' => '0818', 'nombre' => 'San Buenaventura'],
        ['codigo' => '0819', 'nombre' => 'San Ignacio'],
        ['codigo' => '0820', 'nombre' => 'San Juan de Flores'],
        ['codigo' => '0821', 'nombre' => 'San Miguelito'],
        ['codigo' => '0822', 'nombre' => 'Santa Ana'],
        ['codigo' => '0823', 'nombre' => 'Santa Lucía'],
        ['codigo' => '0824', 'nombre' => 'Talanga'],
        ['codigo' => '0825', 'nombre' => 'Tatumbla'],
        ['codigo' => '0826', 'nombre' => 'Valle de Ángeles'],
        ['codigo' => '0827', 'nombre' => 'Villa de San Francisco'],
        ['codigo' => '0828', 'nombre' => 'Vallecillo'],
    ],
    'Gracias a Dios' => [
        ['codigo' => '0901', 'nombre' => 'Puerto Lempira'],
        ['codigo' => '0902', 'nombre' => 'Brus Laguna'],
        ['codigo' => '0903', 'nombre' => 'Ahuas'],
        ['codigo' => '0904', 'nombre' => 'Juan Francisco Bulnes'],
        ['codigo' => '0905', 'nombre' => 'Ramón Villeda Morales'],
        ['codigo' => '0906', 'nombre' => 'Wampusirpi'],
    ],
    'Intibucá' => [
        ['codigo' => '1001', 'nombre' => 'La Esperanza'],
        ['codigo' => '1002', 'nombre' => 'Camasca'],
        ['codigo' => '1003', 'nombre' => 'Colomoncagua'],
        ['codigo' => '1004', 'nombre' => 'Concepción'],
        ['codigo' => '1005', 'nombre' => 'Dolores'],
        ['codigo' => '1006', 'nombre' => 'Intibucá'],
        ['codigo' => '1007', 'nombre' => 'Jesús de Otoro'],
        ['codigo' => '1008', 'nombre' => 'Magdalena'],
        ['codigo' => '1009', 'nombre' => 'Masaguara'],
        ['codigo' => '1010', 'nombre' => 'San Antonio'],
        ['codigo' => '1011', 'nombre' => 'San Francisco de Opalaca'],
        ['codigo' => '1012', 'nombre' => 'San Isidro'],
        ['codigo' => '1013', 'nombre' => 'San Juan'],
        ['codigo' => '1014', 'nombre' => 'San Marcos de la Sierra'],
        ['codigo' => '1015', 'nombre' => 'San Miguel Guancapla'],
        ['codigo' => '1016', 'nombre' => 'Santa Lucía'],
        ['codigo' => '1017', 'nombre' => 'Yamaranguila'],
    ],
    'Islas de la Bahía' => [
        ['codigo' => '1101', 'nombre' => 'Roatán'],
        ['codigo' => '1102', 'nombre' => 'José Santos Guardiola'],
        ['codigo' => '1103', 'nombre' => 'Utila'],
        ['codigo' => '1104', 'nombre' => 'Guanaja'],
    ],
    'La Paz' => [
        ['codigo' => '1201', 'nombre' => 'La Paz'],
        ['codigo' => '1202', 'nombre' => 'Aguanqueterique'],
        ['codigo' => '1203', 'nombre' => 'Cabañas'],
        ['codigo' => '1204', 'nombre' => 'Cane'],
        ['codigo' => '1205', 'nombre' => 'Chinacla'],
        ['codigo' => '1206', 'nombre' => 'Guajiquiro'],
        ['codigo' => '1207', 'nombre' => 'Lauterique'],
        ['codigo' => '1208', 'nombre' => 'Marcala'],
        ['codigo' => '1209', 'nombre' => 'Mercedes de Oriente'],
        ['codigo' => '1210', 'nombre' => 'Opatoro'],
        ['codigo' => '1211', 'nombre' => 'San Antonio del Norte'],
        ['codigo' => '1212', 'nombre' => 'San José'],
        ['codigo' => '1213', 'nombre' => 'San Juan'],
        ['codigo' => '1214', 'nombre' => 'San Pedro de Tutule'],
        ['codigo' => '1215', 'nombre' => 'Santa Ana'],
        ['codigo' => '1216', 'nombre' => 'Santa Elena'],
        ['codigo' => '1217', 'nombre' => 'Santa María'],
        ['codigo' => '1218', 'nombre' => 'Santiago de Puringla'],
        ['codigo' => '1219', 'nombre' => 'Yarula'],
    ],
    'Lempira' => [
        ['codigo' => '1301', 'nombre' => 'Gracias'],
        ['codigo' => '1302', 'nombre' => 'Belén'],
        ['codigo' => '1303', 'nombre' => 'Candelaria'],
        ['codigo' => '1304', 'nombre' => 'Cololaca'],
        ['codigo' => '1305', 'nombre' => 'Erandique'],
        ['codigo' => '1306', 'nombre' => 'Gualcince'],
        ['codigo' => '1307', 'nombre' => 'Guarita'],
        ['codigo' => '1308', 'nombre' => 'La Campa'],
        ['codigo' => '1309', 'nombre' => 'La Iguala'],
        ['codigo' => '1310', 'nombre' => 'La Virtud'],
        ['codigo' => '1311', 'nombre' => 'Las Flores'],
        ['codigo' => '1312', 'nombre' => 'Lepaera'],
        ['codigo' => '1313', 'nombre' => 'Mapulaca'],
        ['codigo' => '1314', 'nombre' => 'Piraera'],
        ['codigo' => '1315', 'nombre' => 'San Andrés'],
        ['codigo' => '1316', 'nombre' => 'San Francisco'],
        ['codigo' => '1317', 'nombre' => 'San Juan Guarita'],
        ['codigo' => '1318', 'nombre' => 'San Manuel Colohete'],
        ['codigo' => '1319', 'nombre' => 'San Marcos de Caiquín'],
        ['codigo' => '1320', 'nombre' => 'San Rafael'],
        ['codigo' => '1321', 'nombre' => 'San Sebastián'],
        ['codigo' => '1322', 'nombre' => 'Santa Cruz'],
        ['codigo' => '1323', 'nombre' => 'Talgua'],
        ['codigo' => '1324', 'nombre' => 'Tambla'],
        ['codigo' => '1325', 'nombre' => 'Tomalá'],
        ['codigo' => '1326', 'nombre' => 'Valladolid'],
        ['codigo' => '1327', 'nombre' => 'Virginia'],
        ['codigo' => '1328', 'nombre' => 'La Unión'],
    ],
    'Ocotepeque' => [
        ['codigo' => '1401', 'nombre' => 'Ocotepeque'],
        ['codigo' => '1402', 'nombre' => 'Belén Gualcho'],
        ['codigo' => '1403', 'nombre' => 'Concepción'],
        ['codigo' => '1404', 'nombre' => 'Dolores Merendón'],
        ['codigo' => '1405', 'nombre' => 'Fraternidad'],
        ['codigo' => '1406', 'nombre' => 'La Encarnación'],
        ['codigo' => '1407', 'nombre' => 'La Labor'],
        ['codigo' => '1408', 'nombre' => 'Lucerna'],
        ['codigo' => '1409', 'nombre' => 'Mercedes'],
        ['codigo' => '1410', 'nombre' => 'San Fernando'],
        ['codigo' => '1411', 'nombre' => 'San Francisco del Valle'],
        ['codigo' => '1412', 'nombre' => 'San Jorge'],
        ['codigo' => '1413', 'nombre' => 'San Marcos'],
        ['codigo' => '1414', 'nombre' => 'Santa Fe'],
        ['codigo' => '1415', 'nombre' => 'Sensenti'],
        ['codigo' => '1416', 'nombre' => 'Sinuapa'],
    ],
    'Olancho' => [
        ['codigo' => '1501', 'nombre' => 'Juticalpa'],
        ['codigo' => '1502', 'nombre' => 'Campamento'],
        ['codigo' => '1503', 'nombre' => 'Catacamas'],
        ['codigo' => '1504', 'nombre' => 'Concordia'],
        ['codigo' => '1505', 'nombre' => 'Dulce Nombre de Culmí'],
        ['codigo' => '1506', 'nombre' => 'El Rosario'],
        ['codigo' => '1507', 'nombre' => 'Esquipulas del Norte'],
        ['codigo' => '1508', 'nombre' => 'Gualaco'],
        ['codigo' => '1509', 'nombre' => 'Guata'],
        ['codigo' => '1510', 'nombre' => 'Guayape'],
        ['codigo' => '1511', 'nombre' => 'Guarizama'],
        ['codigo' => '1512', 'nombre' => 'Jano'],
        ['codigo' => '1513', 'nombre' => 'La Unión'],
        ['codigo' => '1514', 'nombre' => 'Mangulile'],
        ['codigo' => '1515', 'nombre' => 'Manto'],
        ['codigo' => '1516', 'nombre' => 'Patuca'],
        ['codigo' => '1517', 'nombre' => 'Salamá'],
        ['codigo' => '1518', 'nombre' => 'San Esteban'],
        ['codigo' => '1519', 'nombre' => 'San Francisco de Becerra'],
        ['codigo' => '1520', 'nombre' => 'San Francisco de la Paz'],
        ['codigo' => '1521', 'nombre' => 'Santa María del Real'],
        ['codigo' => '1522', 'nombre' => 'Silca'],
        ['codigo' => '1523', 'nombre' => 'Yocón'],
    ],
    'Santa Bárbara' => [
        ['codigo' => '1601', 'nombre' => 'Santa Bárbara'],
        ['codigo' => '1602', 'nombre' => 'Arada'],
        ['codigo' => '1603', 'nombre' => 'Atima'],
        ['codigo' => '1604', 'nombre' => 'Azacualpa'],
        ['codigo' => '1605', 'nombre' => 'Ceguaca'],
        ['codigo' => '1606', 'nombre' => 'Chinda'],
        ['codigo' => '1607', 'nombre' => 'Concepción del Norte'],
        ['codigo' => '1608', 'nombre' => 'Concepción del Sur'],
        ['codigo' => '1609', 'nombre' => 'El Níspero'],
        ['codigo' => '1610', 'nombre' => 'Gualala'],
        ['codigo' => '1611', 'nombre' => 'Ilama'],
        ['codigo' => '1612', 'nombre' => 'Las Vegas'],
        ['codigo' => '1613', 'nombre' => 'Macuelizo'],
        ['codigo' => '1614', 'nombre' => 'Naranjito'],
        ['codigo' => '1615', 'nombre' => 'Nueva Celilac'],
        ['codigo' => '1616', 'nombre' => 'Petoa'],
        ['codigo' => '1617', 'nombre' => 'Protección'],
        ['codigo' => '1618', 'nombre' => 'Quimistán'],
        ['codigo' => '1619', 'nombre' => 'San Francisco de Ojuera'],
        ['codigo' => '1620', 'nombre' => 'San José de Colinas'],
        ['codigo' => '1621', 'nombre' => 'San Luis'],
        ['codigo' => '1622', 'nombre' => 'San Marcos'],
        ['codigo' => '1623', 'nombre' => 'San Nicolás'],
        ['codigo' => '1624', 'nombre' => 'San Pedro Zacapa'],
        ['codigo' => '1625', 'nombre' => 'Santa Rita'],
        ['codigo' => '1626', 'nombre' => 'San Vicente Centenario'],
        ['codigo' => '1627', 'nombre' => 'Trinidad'],
        ['codigo' => '1628', 'nombre' => 'Nueva Frontera'],
    ],
    'Valle' => [
        ['codigo' => '1701', 'nombre' => 'Nacaome'],
        ['codigo' => '1702', 'nombre' => 'Alianza'],
        ['codigo' => '1703', 'nombre' => 'Amapala'],
        ['codigo' => '1704', 'nombre' => 'Aramecina'],
        ['codigo' => '1705', 'nombre' => 'Caridad'],
        ['codigo' => '1706', 'nombre' => 'Goascorán'],
        ['codigo' => '1707', 'nombre' => 'Langue'],
        ['codigo' => '1708', 'nombre' => 'San Francisco de Coray'],
        ['codigo' => '1709', 'nombre' => 'San Lorenzo'],
    ],
    'Yoro' => [
        ['codigo' => '1801', 'nombre' => 'Yoro'],
        ['codigo' => '1802', 'nombre' => 'Arenal'],
        ['codigo' => '1803', 'nombre' => 'El Negrito'],
        ['codigo' => '1804', 'nombre' => 'El Progreso'],
        ['codigo' => '1805', 'nombre' => 'Jocón'],
        ['codigo' => '1806', 'nombre' => 'Morazán'],
        ['codigo' => '1807', 'nombre' => 'Olanchito'],
        ['codigo' => '1808', 'nombre' => 'Santa Rita'],
        ['codigo' => '1809', 'nombre' => 'Sulaco'],
        ['codigo' => '1810', 'nombre' => 'Victoria'],
        ['codigo' => '1811', 'nombre' => 'Yorito'],
    ],
];

$pdo->beginTransaction();

try {
    $insertDepto = $pdo->prepare('INSERT INTO departamentos (codigo, nombre, cabecera, diputados_cupos, candidatos_diputados) VALUES (:codigo, :nombre, :cabecera, :cupos, :candidatos)
        ON DUPLICATE KEY UPDATE cabecera = VALUES(cabecera), diputados_cupos = VALUES(diputados_cupos), candidatos_diputados = VALUES(candidatos_diputados)');

    foreach ($departamentos as $depto) {
        $insertDepto->execute([
            ':codigo' => $depto['codigo'],
            ':nombre' => $depto['nombre'],
            ':cabecera' => $depto['cabecera'],
            ':cupos' => $depto['diputados_cupos'],
            ':candidatos' => $depto['candidatos'],
        ]);
    }

    $deptoMap = [];
    $consultaDeptos = $pdo->query('SELECT id, nombre FROM departamentos');
    while ($fila = $consultaDeptos->fetch()) {
        $deptoMap[$fila['nombre']] = (int) $fila['id'];
    }

    $insertMunicipio = $pdo->prepare('INSERT INTO municipios (departamento_id, codigo, nombre) VALUES (:departamento_id, :codigo, :nombre)
        ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)');

    $insertCentro = $pdo->prepare('INSERT INTO centros_votacion (municipio_id, nombre, codigo, direccion, capacidad) VALUES (:municipio_id, :nombre, :codigo, :direccion, :capacidad)
        ON DUPLICATE KEY UPDATE direccion = VALUES(direccion), capacidad = VALUES(capacidad)');

    $insertAutoridad = $pdo->prepare('INSERT INTO autoridades_municipales (municipio_id, alcalde, vice_alcalde, periodo_inicio, periodo_fin) VALUES (:municipio_id, :alcalde, :vice_alcalde, :inicio, :fin)
        ON DUPLICATE KEY UPDATE alcalde = VALUES(alcalde), vice_alcalde = VALUES(vice_alcalde), periodo_inicio = VALUES(periodo_inicio), periodo_fin = VALUES(periodo_fin)');

    foreach ($municipios as $nombreDepto => $listaMunicipios) {
        if (!isset($deptoMap[$nombreDepto])) {
            throw new RuntimeException('No se encontró el departamento ' . $nombreDepto . ' en la base de datos.');
        }

        $departamentoId = $deptoMap[$nombreDepto];

        foreach ($listaMunicipios as $municipio) {
            $insertMunicipio->execute([
                ':departamento_id' => $departamentoId,
                ':codigo' => $municipio['codigo'],
                ':nombre' => $municipio['nombre'],
            ]);

            $municipioId = (int) dbQuery('SELECT id FROM municipios WHERE codigo = :codigo LIMIT 1', [
                ':codigo' => $municipio['codigo'],
            ])->fetchColumn();

            $insertCentro->execute([
                ':municipio_id' => $municipioId,
                ':nombre' => 'Centro Municipal de ' . $municipio['nombre'],
                ':codigo' => 'CM-' . $municipio['codigo'],
                ':direccion' => $municipio['nombre'] . ', ' . $nombreDepto,
                ':capacidad' => 1200,
            ]);

            $insertAutoridad->execute([
                ':municipio_id' => $municipioId,
                ':alcalde' => 'Alcalde designado ' . $municipio['nombre'],
                ':vice_alcalde' => 'Vicealcalde designado ' . $municipio['nombre'],
                ':inicio' => 2026,
                ':fin' => 2030,
            ]);
        }
    }

    $pdo->commit();
    echo "Departamentos y municipios cargados correctamente." . PHP_EOL;
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Error al sembrar ubicaciones: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
