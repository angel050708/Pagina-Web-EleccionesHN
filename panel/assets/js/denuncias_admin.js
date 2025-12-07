function mostrarDetallesDenuncia(denunciaId) {
    fetch(`obtener_denuncia.php?id=${denunciaId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('contenidoDenuncia').innerHTML = data;
            new bootstrap.Modal(document.getElementById('modalVerDenuncia')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los detalles de la denuncia');
        });
}

function enviarRespuestaDenuncia(event, denunciaId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    formData.append('denuncia_id', denunciaId);
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
    
    fetch('responder_denuncia.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Respuesta enviada correctamente');
            bootstrap.Modal.getInstance(document.getElementById('modalVerDenuncia')).hide();
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'No se pudo enviar la respuesta'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al enviar la respuesta');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function cambiarEstadoDenuncia(denunciaId, estadoActual) {
    document.getElementById('denuncia_id_estado').value = denunciaId;
    const selectorEstado = document.getElementById('nuevo_estado');
    if (selectorEstado && estadoActual) {
        selectorEstado.value = estadoActual;
    }
    new bootstrap.Modal(document.getElementById('modalCambiarEstado')).show();
}

function asignarResponsableDenuncia(denunciaId, responsableId) {
    document.getElementById('denuncia_id_responsable').value = denunciaId;
    const selectorResponsable = document.getElementById('responsable_id');
    if (selectorResponsable) {
        selectorResponsable.value = responsableId && responsableId !== '' ? responsableId : '0';
    }
    new bootstrap.Modal(document.getElementById('modalAsignarResponsable')).show();
}

setInterval(function () {
    if (!document.hidden) {
        location.reload();
    }
}, 180000);