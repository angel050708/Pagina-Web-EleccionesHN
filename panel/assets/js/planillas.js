function verDetalles(planillaId) {
    fetch(`obtener_planilla_detalles.php?id=${planillaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarModalDetalles(data.planilla);
            } else {
                alert('Error al cargar detalles: ' + data.error);
            }
        })
        .catch(error => {
            alert('Error de conexión');
        });
}

function eliminarPlanilla(planillaId) {
    if (confirm('¿Está seguro de eliminar esta planilla?')) {
        const formData = new FormData();
        formData.append('accion', 'eliminar');
        formData.append('id', planillaId);
        
        fetch('gestionar_planilla.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error de conexión');
        });
    }
}

function mostrarModalDetalles(planilla) {
    let ubicacion = 'Nacional';
    if (planilla.departamento_nombre) {
        ubicacion = planilla.departamento_nombre;
        if (planilla.municipio_nombre) {
            ubicacion += ' - ' + planilla.municipio_nombre;
        }
    }
    
    const modalHtml = `
        <div class="modal fade" id="modalDetalles" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles de Planilla</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Tipo:</strong> ${planilla.tipo}</p>
                                <p><strong>Partido:</strong> ${planilla.partido}</p>
                                <p><strong>Nombre:</strong> ${planilla.nombre}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Ubicación:</strong> ${ubicacion}</p>
                                <p><strong>Estado:</strong> ${planilla.estado}</p>
                                <p><strong>Candidatos:</strong> ${planilla.total_candidatos}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
    modal.show();
    
    document.getElementById('modalDetalles').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}