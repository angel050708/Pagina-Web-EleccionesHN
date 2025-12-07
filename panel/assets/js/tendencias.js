const datosPartidos = JSON.parse(document.getElementById('datos-partidos').textContent);
const datosDepartamentos = JSON.parse(document.getElementById('datos-departamentos').textContent);
const datosEnTiempo = JSON.parse(document.getElementById('datos-tiempo').textContent);

Highcharts.setOptions({
    colors: ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8'],
    chart: {
        backgroundColor: 'transparent',
        style: {
            fontFamily: 'Inter, sans-serif'
        }
    },
    title: {
        style: {
            display: 'none'
        }
    }
});

function crearGraficaPartidos() {
    Highcharts.chart('grafica-partidos', {
        chart: {
            type: 'pie',
            height: 400
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f}%'
                },
                showInLegend: true
            }
        },
        series: [{
            name: 'Votos',
            colorByPoint: true,
            data: datosPartidos
        }],
        legend: {
            align: 'right',
            verticalAlign: 'middle',
            layout: 'vertical'
        }
    });
}

function crearGraficaDepartamentos() {
    Highcharts.chart('grafica-departamentos', {
        chart: {
            type: 'column',
            height: 400
        },
        xAxis: {
            type: 'category'
        },
        yAxis: {
            title: {
                text: 'Número de votos'
            }
        },
        plotOptions: {
            column: {
                dataLabels: {
                    enabled: true
                }
            }
        },
        series: [{
            name: 'Votos',
            data: datosDepartamentos
        }]
    });
}

function crearGraficaTiempo() {
    Highcharts.chart('grafica-tiempo', {
        chart: {
            type: 'spline',
            height: 400
        },
        xAxis: {
            type: 'datetime',
            title: {
                text: 'Tiempo'
            }
        },
        yAxis: {
            title: {
                text: 'Votos acumulados'
            }
        },
        plotOptions: {
            spline: {
                marker: {
                    enabled: true
                }
            }
        },
        series: [{
            name: 'Votos acumulados',
            data: datosEnTiempo
        }]
    });
}

document.addEventListener('DOMContentLoaded', function() {
    crearGraficaPartidos();
    crearGraficaDepartamentos();
    crearGraficaTiempo();
});

function actualizarGraficas() {
    location.reload();
}

function exportarReporte() {
    window.open('exportar_reporte_tendencias.php', '_blank');
}

function imprimirGraficas() {
    window.print();
}

setInterval(function() {
    if (!document.hidden) {
        actualizarGraficas();
    }
}, 300000);