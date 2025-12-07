// Función para mostrar detalles del usuario
function mostrarDetallesUsuario(usuarioId) {
    const modal = new bootstrap.Modal(document.getElementById('modalVerUsuario'));
    const content = document.getElementById('detallesUsuarioContent');
    
    // Mostrar loading
    content.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Cargar datos del usuario
    fetch('obtener_usuario.php?id=' + usuarioId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const usuario = data.usuario;
                content.innerHTML = `
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                <div class="user-avatar-large me-3">
                                    <i class="bi bi-person-circle fs-1"></i>
                                </div>
                                <div>
                                    <h4 class="mb-1">${escapeHtml(usuario.nombre)}</h4>
                                    <span class="badge bg-${usuario.rol === 'administrador' ? 'danger' : usuario.rol === 'observador' ? 'warning' : 'info'}">${capitalize(usuario.rol)}</span>
                                    <span class="badge bg-${usuario.estado === 'activo' ? 'success' : 'secondary'} ms-2">${capitalize(usuario.estado)}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Email</label>
                            <div class="fw-medium">${escapeHtml(usuario.email || 'No especificado')}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">DNI</label>
                            <div class="fw-medium">${escapeHtml(usuario.dni || 'No especificado')}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Teléfono</label>
                            <div class="fw-medium">${escapeHtml(usuario.telefono || 'No especificado')}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Género</label>
                            <div class="fw-medium">${capitalize(usuario.genero || 'No especificado')}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Fecha de nacimiento</label>
                            <div class="fw-medium">${usuario.fecha_nacimiento ? formatearFecha(usuario.fecha_nacimiento) : 'No especificada'}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Edad</label>
                            <div class="fw-medium">${usuario.fecha_nacimiento ? calcularEdad(usuario.fecha_nacimiento) + ' años' : 'N/A'}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">Dirección</label>
                            <div class="fw-medium">${escapeHtml(usuario.direccion || 'No especificada')}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Departamento</label>
                            <div class="fw-medium">${escapeHtml(usuario.departamento_nombre || 'No especificado')}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Municipio</label>
                            <div class="fw-medium">${escapeHtml(usuario.municipio_nombre || 'No especificado')}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Fecha de registro</label>
                            <div class="fw-medium">${usuario.creado_en ? formatearFechaHora(usuario.creado_en) : 'No disponible'}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Última actualización</label>
                            <div class="fw-medium">${usuario.actualizado_en ? formatearFechaHora(usuario.actualizado_en) : 'No disponible'}</div>
                        </div>
                        ${usuario.rol === 'votante' ? `
                            <div class="col-12 mt-3">
                                <hr>
                                <h6 class="mb-3">Información de votante</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Centro de votación</label>
                                <div class="fw-medium">${escapeHtml(usuario.centro_votacion_nombre || 'No asignado')}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">¿Ha votado?</label>
                                <div class="fw-medium">${usuario.ha_votado ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>'}</div>
                            </div>
                        ` : ''}
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${escapeHtml(data.error || 'Error al cargar los datos del usuario')}
                    </div>
                `;
            }
        })
        .catch(error => {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error al cargar los datos del usuario
                </div>
            `;
        });
}

// Función para editar usuario
function editarUsuario(usuarioId) {
    const modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
    modal.show();
    
    // Cargar departamentos
    cargarDepartamentos();
    
    // Cargar datos del usuario
    fetch('obtener_usuario.php?id=' + usuarioId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const usuario = data.usuario;
                
                // Llenar el formulario
                document.getElementById('editar_usuario_id').value = usuario.id;
                document.getElementById('editar_nombre').value = usuario.nombre || '';
                document.getElementById('editar_email').value = usuario.email || '';
                document.getElementById('editar_dni').value = usuario.dni || '';
                document.getElementById('editar_telefono').value = usuario.telefono || '';
                document.getElementById('editar_rol').value = usuario.rol || 'votante';
                document.getElementById('editar_estado').value = usuario.estado || 'activo';
                document.getElementById('editar_genero').value = usuario.genero || '';
                document.getElementById('editar_fecha_nacimiento').value = usuario.fecha_nacimiento || '';
                document.getElementById('editar_direccion').value = usuario.direccion || '';
                
                // Cargar departamento y municipio
                if (usuario.departamento_id) {
                    setTimeout(() => {
                        document.getElementById('editar_departamento').value = usuario.departamento_id;
                        cargarMunicipios(usuario.departamento_id, 'editar', usuario.municipio_id);
                    }, 500);
                }
            } else {
                alert('Error: ' + data.error);
                modal.hide();
            }
        })
        .catch(error => {
            alert('Error al cargar los datos del usuario');
            modal.hide();
        });
}

function cargarDepartamentos() {
    fetch('../admin/obtener_departamentos.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('editar_departamento');
            select.innerHTML = '<option value="">Seleccionar departamento</option>';
            
            if (data.success && data.departamentos) {
                data.departamentos.forEach(depto => {
                    select.innerHTML += `<option value="${depto.id}">${escapeHtml(depto.nombre)}</option>`;
                });
            }
        });
}

function cargarMunicipios(departamentoId, prefijo = 'editar', municipioSeleccionado = null) {
    const select = document.getElementById(prefijo + '_municipio');
    select.innerHTML = '<option value="">Cargando...</option>';
    
    if (!departamentoId) {
        select.innerHTML = '<option value="">Seleccionar municipio</option>';
        return;
    }
    
    fetch('../admin/obtener_municipios.php?departamento_id=' + departamentoId)
        .then(response => response.json())
        .then(data => {
            select.innerHTML = '<option value="">Seleccionar municipio</option>';
            
            if (data.success && data.municipios) {
                data.municipios.forEach(muni => {
                    const selected = municipioSeleccionado == muni.id ? 'selected' : '';
                    select.innerHTML += `<option value="${muni.id}" ${selected}>${escapeHtml(muni.nombre)}</option>`;
                });
            }
        });
}

// Event listener para cambio de departamento
document.addEventListener('DOMContentLoaded', function() {
    const editarDepartamento = document.getElementById('editar_departamento');
    if (editarDepartamento) {
        editarDepartamento.addEventListener('change', function() {
            cargarMunicipios(this.value, 'editar');
        });
    }
});

// Envío del formulario de edición
document.getElementById('formEditarUsuario').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = document.getElementById('editar_password').value;
    const passwordConfirm = document.getElementById('editar_password_confirm').value;
    
    if (password && password !== passwordConfirm) {
        alert('Las contraseñas no coinciden.');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('actualizar_usuario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Usuario actualizado correctamente');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error al actualizar el usuario');
    });
});

function cambiarEstadoUsuario(usuarioId, estadoDestino) {
    const accion = estadoDestino === 'activo' ? 'activar' : 'suspender';
    if (confirm(`¿Seguro que quieres ${accion} este usuario?`)) {
        fetch('cambiar_estado_usuario.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: usuarioId,
                estado: estadoDestino
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}

function borrarUsuario(usuarioId) {
    if (confirm('¿Seguro que quieres eliminar este usuario? No se puede deshacer.')) {
        fetch('eliminar_usuario.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: usuarioId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}

document.getElementById('formCrearUsuario').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    
    if (password !== passwordConfirm) {
        alert('Las contraseñas no son iguales.');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('crear_usuario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
});

// Funciones auxiliares
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatearFecha(fecha) {
    if (!fecha) return '';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-HN', { year: 'numeric', month: 'long', day: 'numeric' });
}

function formatearFechaHora(fecha) {
    if (!fecha) return '';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-HN', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function calcularEdad(fechaNacimiento) {
    if (!fechaNacimiento) return 0;
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }
    return edad;
}