function cerrarCentroVotacion(centroId) {
    document.getElementById('centro_id_cierre').value = centroId;
    new bootstrap.Modal(document.getElementById('modalCerrarCentro')).show();
}

function generarActaCentro(centroId) {
    if (confirm('¿Generar acta de cierre para este centro?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="accion" value="generar_acta_cierre">
            <input type="hidden" name="centro_id" value="${centroId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function mostrarDetallesCentro(centroId) {
    window.open(`detalle_centro_cierre.php?id=${centroId}`, '_blank');
}

function generarReporteGeneral() {
    window.open('reporte_cierre_general.php', '_blank');
}

function exportarEstadoCentros() {
    window.open('exportar_estado_centros.php', '_blank');
}

function actualizarEstado() {
    location.reload();
}

setInterval(function() {
    if (!document.hidden) {
        location.reload();
    }
}, 60000);

function abrirCentroVotacion(centroId) {
    if (confirm('¿Abrir nuevamente este centro de votación?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="accion" value="abrir_centro_individual">
            <input type="hidden" name="centro_id" value="${centroId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function reabrirCentros() {
    if (confirm('¿Reabrir todos los centros cerrados?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="accion" value="reabrir_centros">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}