<?php
session_start();

include_once __DIR__ . '/../../includes/funciones.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'votante') {
    header('Location: ../../login.php?error=Debes iniciar sesión como votante.');
    exit;
}

$paginaActiva = 'resultados';

// Obtener parámetros de filtro
$tipoFiltro = $_GET['tipo'] ?? 'presidencial';
$departamentoFiltro = $_GET['departamento'] ?? '';

// Obtener datos para filtros
$departamentos = dbQuery("SELECT id, nombre FROM departamentos ORDER BY nombre")->fetchAll();
$tiposPlanillas = obtenerTiposPlanillas();

// Obtener candidatos según filtros
$candidatos = obtenerCandidatosPorTipo($tipoFiltro, $departamentoFiltro ?: null);

// Estadísticas generales
$totalVotos = obtenerTotalVotos();
$candidatoLider = !empty($candidatos) ? $candidatos[0] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Resultados electorales · EleccionesHN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css" />
    <link rel="stylesheet" href="../assets/css/votante.css" />
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
</head>
<body>
    <div class="dashboard-shell">
        <aside class="dashboard-sidebar">
            <div class="sidebar-brand">
                <img src="../../imagen.php?img=cne_logo.png" alt="EleccionesHN">
                <span>EleccionesHN</span>
                <small>Portal ciudadano</small>
            </div>
            <nav class="sidebar-menu">
                <a class="sidebar-link" href="index.php">
                    <i class="bi bi-house-door"></i>
                    <span>Inicio</span>
                </a>
                <a class="sidebar-link" href="datos.php">
                    <i class="bi bi-person-circle"></i>
                    <span>Mis datos</span>
                </a>
                <a class="sidebar-link" href="votar.php">
                    <i class="bi bi-check-square"></i>
                    <span>Votar</span>
                </a>
                <a class="sidebar-link" href="recibo.php">
                    <i class="bi bi-receipt"></i>
                    <span>Mi recibo</span>
                </a>
                <a class="sidebar-link <?php echo $paginaActiva === 'resultados' ? 'is-active' : ''; ?>" href="resultados.php">
                    <i class="bi bi-trophy"></i>
                    <span>Resultados</span>
                </a>
            </nav>
            <div class="sidebar-footer">Elecciones Honduras 2025</div>
        </aside>
        <div class="dashboard-main">
            <header class="dashboard-topbar">
                <div class="topbar-context">
                    <h1>Resultados electorales</h1>
                    <span>Consulta los resultados oficiales de las elecciones</span>
                </div>
                <div class="topbar-meta">
                    <div class="topbar-chips">
                        <span class="chip"><i class="bi bi-person-check"></i>Votante</span>
                        <span class="chip chip--success"><i class="bi bi-trophy"></i>Resultados oficiales</span>
                    </div>
                    <a class="btn btn-outline-primary" href="../../scripts/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a>
                </div>
            </header>
            <main class="main-content">
                <div class="row g-4 mb-4">
                    <div class="col-lg-4 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--primary">
                                <i class="bi bi-bar-chart-fill"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo number_format($totalVotos); ?></h3>
                                <p>Total de votos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--primary">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo $candidatoLider ? htmlspecialchars($candidatoLider['nombre'], ENT_QUOTES, 'UTF-8') : 'N/A'; ?></h3>
                                <p>Líder actual</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                        <div class="stats-card">
                            <div class="stats-card__icon stats-card__icon--primary">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div class="stats-card__content">
                                <h3><?php echo date('H:i'); ?></h3>
                                <p>Última actualización</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtrar resultados</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label for="tipo" class="form-label">Tipo de elección</label>
                                <select class="form-select" name="tipo" id="tipo" onchange="this.form.submit()">
                                    <?php foreach ($tiposPlanillas as $tipo): ?>
                                        <option value="<?php echo $tipo['tipo']; ?>" <?php echo $tipoFiltro === $tipo['tipo'] ? 'selected' : ''; ?>>
                                            <?php 
                                                $nombres = [
                                                    'presidencial' => 'Presidencial',
                                                    'diputacion' => 'Diputados',
                                                    'alcaldia' => 'Alcaldías',
                                                    'vicealcaldia' => 'Vicealcaldías'
                                                ];
                                                echo $nombres[$tipo['tipo']] ?? ucfirst($tipo['tipo']);
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if ($tipoFiltro !== 'presidencial'): ?>
                            <div class="col-md-6">
                                <label for="departamento" class="form-label">Departamento</label>
                                <select class="form-select" name="departamento" id="departamento" onchange="this.form.submit()">
                                    <option value="">Todos los departamentos</option>
                                    <?php foreach ($departamentos as $depto): ?>
                                        <option value="<?php echo $depto['id']; ?>" <?php echo $departamentoFiltro == $depto['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($depto['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Gráfica circular de resultados -->
                <?php if (!empty($candidatos) && count($candidatos) > 0 && $tipoFiltro === 'presidencial'): ?>
                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-pie-chart me-2"></i>
                                    Gráfica de Resultados
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="resultsChart" style="height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Estadísticas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                            <div>
                                                <h6 class="mb-1">Total de Votos</h6>
                                                <h4 class="mb-0 text-primary"><?php echo number_format(array_sum(array_column($candidatos, 'total_votos'))); ?></h4>
                                            </div>
                                            <div class="text-end">
                                                <h6 class="mb-1">Candidatos</h6>
                                                <h4 class="mb-0 text-info"><?php echo count($candidatos); ?></h4>
                                            </div>
                                        </div>
                                        
                                        <h6 class="mb-3">Top 3 Candidatos:</h6>
                                        <?php 
                                        $totalVotos = array_sum(array_column($candidatos, 'total_votos'));
                                        $colores = ['#007bff', '#dc3545', '#28a745', '#ffc107', '#17a2b8'];
                                        foreach (array_slice($candidatos, 0, 3) as $index => $candidato): 
                                            $porcentaje = $totalVotos > 0 ? ($candidato['total_votos'] / $totalVotos) * 100 : 0;
                                        ?>
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="me-3" style="width: 20px; height: 20px; background-color: <?php echo $colores[$index]; ?>; border-radius: 3px;"></div>
                                                <div class="flex-grow-1">
                                                    <strong><?php echo htmlspecialchars($candidato['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($candidato['partido'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold"><?php echo number_format($porcentaje, 1); ?>%</div>
                                                    <small class="text-muted"><?php echo number_format($candidato['total_votos']); ?> votos</small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Resultados de candidatos -->
                <div class="card">
                    <div class="card-header bg-primary text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul me-2"></i>
                                Tabla completa - <?php 
                                    $nombres = [
                                        'presidencial' => 'Presidencial',
                                        'diputacion' => 'Diputados',
                                        'alcaldia' => 'Alcaldías', 
                                        'vicealcaldia' => 'Vicealcaldías'
                                    ];
                                    echo $nombres[$tipoFiltro] ?? ucfirst($tipoFiltro);
                                ?>
                                <?php if ($departamentoFiltro && $tipoFiltro !== 'presidencial'): ?>
                                    <?php
                                        $deptoBuscado = array_filter($departamentos, function($d) use ($departamentoFiltro) {
                                            return $d['id'] == $departamentoFiltro;
                                        });
                                        $deptoNombre = !empty($deptoBuscado) ? array_values($deptoBuscado)[0]['nombre'] : '';
                                    ?>
                                    - <?php echo htmlspecialchars($deptoNombre, ENT_QUOTES, 'UTF-8'); ?>
                                <?php endif; ?>
                            </h5>
                            <button class="btn btn-outline-light btn-sm" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($candidatos)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 60px;">Pos.</th>
                                            <th>Candidato</th>
                                            <th>Cargo</th>
                                            <th>Partido</th>
                                            <?php if ($tipoFiltro !== 'presidencial'): ?>
                                                <th>Ubicación</th>
                                            <?php endif; ?>
                                            <th class="text-center">Votos</th>
                                            <th class="text-center">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $totalVotosFiltrados = array_sum(array_column($candidatos, 'total_votos'));
                                        foreach ($candidatos as $index => $candidato): 
                                        ?>
                                            <tr class="<?php echo $index === 0 ? 'table-warning' : ''; ?>">
                                                <td class="text-center">
                                                    <?php if ($index === 0): ?>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-trophy-fill me-1"></i><?php echo $index + 1; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary"><?php echo $index + 1; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-primary">
                                                        <?php echo htmlspecialchars($candidato['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <?php echo htmlspecialchars($candidato['cargo'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">
                                                        <?php echo htmlspecialchars($candidato['partido'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($candidato['planilla_nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </small>
                                                </td>
                                                <?php if ($tipoFiltro !== 'presidencial'): ?>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php 
                                                            if ($candidato['departamento_nombre']) {
                                                                echo htmlspecialchars($candidato['departamento_nombre'], ENT_QUOTES, 'UTF-8');
                                                                if ($candidato['municipio_nombre']) {
                                                                    echo '<br>' . htmlspecialchars($candidato['municipio_nombre'], ENT_QUOTES, 'UTF-8');
                                                                }
                                                            } else {
                                                                echo 'Nacional';
                                                            }
                                                            ?>
                                                        </small>
                                                    </td>
                                                <?php endif; ?>
                                                <td class="text-center">
                                                    <span class="badge bg-primary fs-6">
                                                        <?php echo number_format($candidato['total_votos']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="fw-bold text-primary">
                                                        <?php echo $totalVotosFiltrados > 0 ? number_format(($candidato['total_votos'] / $totalVotosFiltrados) * 100, 1) : 0; ?>%
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <h4 class="text-muted mt-3">No hay candidatos</h4>
                                <p class="text-muted">No se encontraron candidatos para los filtros seleccionados.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Funcionalidad simple para la página de resultados
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit form when filters change
            const form = document.querySelector('form');
            if (form) {
                const selects = form.querySelectorAll('select');
                selects.forEach(select => {
                    select.addEventListener('change', function() {
                        form.submit();
                    });
                });
            }

            // Initialize circular chart
            <?php if (!empty($candidatos) && count($candidatos) > 0 && $tipoFiltro === 'presidencial'): ?>
            var chartDom = document.getElementById('resultsChart');
            if (chartDom) {
                var myChart = echarts.init(chartDom);
                
                var option = {
                    tooltip: {
                        trigger: 'item',
                        formatter: '{b}: {c} votos ({d}%)'
                    },
                    legend: {
                        orient: 'vertical',
                        left: 'left',
                        top: 'middle',
                        textStyle: {
                            fontSize: 12
                        }
                    },
                    series: [
                        {
                            name: 'Resultados',
                            type: 'pie',
                            radius: ['40%', '70%'],
                            center: ['60%', '50%'],
                            avoidLabelOverlap: false,
                            label: {
                                show: false,
                                position: 'center'
                            },
                            emphasis: {
                                label: {
                                    show: true,
                                    fontSize: '16',
                                    fontWeight: 'bold'
                                }
                            },
                            labelLine: {
                                show: false
                            },
                            data: [
                                <?php 
                                $colores = ['#dc3545','#007bff' , '#28a745', '#ffc107', '#17a2b8', '#6f42c1', '#fd7e14', '#20c997'];
                                $candidatoItems = [];
                                foreach ($candidatos as $index => $candidato): 
                                    $color = $colores[$index % count($colores)];
                                    $candidatoItems[] = "{
                                        value: " . $candidato['total_votos'] . ",
                                        name: '" . addslashes($candidato['nombre']) . "',
                                        itemStyle: {
                                            color: '$color'
                                        }
                                    }";
                                endforeach;
                                echo implode(',', $candidatoItems);
                                ?>
                            ]
                        }
                    ]
                };

                myChart.setOption(option);
                
                // Responsive resize
                window.addEventListener('resize', function() {
                    myChart.resize();
                });
            }
            <?php endif; ?>
        });
    </script>
</body>
</html>