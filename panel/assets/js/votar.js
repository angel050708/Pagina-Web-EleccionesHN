// JavaScript para la página de votación
document.addEventListener('DOMContentLoaded', function () {
    const modalPresidenteEl = document.getElementById('modalVotacionPresidente');
    const modalDiputadosEl = document.getElementById('modalVotacionDiputados');
    const modalAlcaldeEl = document.getElementById('modalVotacionAlcalde');

    const btnConfirmarPresidente = document.getElementById('btnConfirmarVotoPresidente');
    const inputCandidatoPresidente = document.getElementById('candidatoSeleccionadoPresidente');
    const planillaContainer = document.querySelector('.planilla-container');
    const planillaOpciones = document.querySelectorAll('.planilla-opcion');

    let marcaSeleccion = null;
    let candidatoSeleccionado = null;

    function crearOMostrarMarca(target) {
        if (!planillaContainer || !target) {
            return;
        }

        if (!marcaSeleccion) {
            marcaSeleccion = document.createElement('div');
            marcaSeleccion.className = 'marca-seleccion';
            marcaSeleccion.textContent = '';
            planillaContainer.appendChild(marcaSeleccion);
        }

        const containerRect = planillaContainer.getBoundingClientRect();
        const targetRect = target.getBoundingClientRect();

        marcaSeleccion.style.width = `${targetRect.width}px`;
        marcaSeleccion.style.height = `${targetRect.height}px`;
        marcaSeleccion.style.left = `${targetRect.left - containerRect.left}px`;
        marcaSeleccion.style.top = `${targetRect.top - containerRect.top}px`;
    }

    function seleccionarCandidato(id) {
        candidatoSeleccionado = String(id);
        inputCandidatoPresidente.value = candidatoSeleccionado;
        btnConfirmarPresidente.disabled = false;

        planillaOpciones.forEach(function (btn) {
            const isActive = btn.dataset.id === candidatoSeleccionado;
            btn.classList.toggle('active', isActive);
            if (isActive) {
                crearOMostrarMarca(btn);
            }
        });
    }

    function actualizarMarca() {
        if (!candidatoSeleccionado) {
            if (marcaSeleccion) {
                marcaSeleccion.remove();
                marcaSeleccion = null;
            }
            return;
        }

        const opcionActiva = document.querySelector(`.planilla-opcion[data-id="${candidatoSeleccionado}"]`);
        if (opcionActiva) {
            crearOMostrarMarca(opcionActiva);
        }
    }

    planillaOpciones.forEach(function (btn) {
        btn.addEventListener('click', function () {
            seleccionarCandidato(btn.dataset.id);
        });
    });

    if (modalPresidenteEl) {
        modalPresidenteEl.addEventListener('shown.bs.modal', actualizarMarca);
        modalPresidenteEl.addEventListener('hidden.bs.modal', function () {
            btnConfirmarPresidente.disabled = inputCandidatoPresidente.value === '';
        });
    }

    window.addEventListener('resize', actualizarMarca);

    const modales = {
        presidente: modalPresidenteEl,
        diputados: modalDiputadosEl,
        alcalde: modalAlcaldeEl
    };

    window.abrirVotacion = function (tipo) {
        const modalEl = modales[tipo];
        if (!modalEl) {
            return;
        }
        const instancia = bootstrap.Modal.getOrCreateInstance(modalEl);
        instancia.show();

        if (tipo === 'presidente') {
            setTimeout(actualizarMarca, 100);
        }
    };

    window.seleccionarCandidato = seleccionarCandidato; // Fallback por si se requiere globalmente

    // Lógica para diputados - selección múltiple
    const candidatosCheckboxes = document.querySelectorAll('.candidato-checkbox');
    const btnConfirmarDiputados = document.getElementById('btnConfirmarVotoDiputados');
    const inputCandidatosSeleccionados = document.getElementById('candidatosSeleccionados');
    const contadorSeleccionados = document.getElementById('contadorSeleccionados');
    
    let candidatosSeleccionados = [];
    const maxDiputados = parseInt(document.querySelector('[data-max-diputados]')?.dataset.maxDiputados || '3');

    function actualizarSeleccionDiputados() {
        // Obtener todos los checkboxes marcados
        const checkboxesMarcados = document.querySelectorAll('.candidato-checkbox:checked');
        candidatosSeleccionados = [];
        
        checkboxesMarcados.forEach(function(checkbox) {
            candidatosSeleccionados.push({
                nombre: checkbox.value,
                partido: checkbox.dataset.partido,
                planilla: checkbox.dataset.planilla
            });
        });
        
        // Actualizar contador
        if (contadorSeleccionados) {
            contadorSeleccionados.textContent = candidatosSeleccionados.length;
        }
        
        // Actualizar campo oculto
        if (inputCandidatosSeleccionados) {
            inputCandidatosSeleccionados.value = JSON.stringify(candidatosSeleccionados);
        }
        
        // Habilitar/deshabilitar botón
        if (btnConfirmarDiputados) {
            btnConfirmarDiputados.disabled = candidatosSeleccionados.length === 0;
        }
        
        // Deshabilitar checkboxes si se alcanzó el máximo
        candidatosCheckboxes.forEach(function(checkbox) {
            if (!checkbox.checked && candidatosSeleccionados.length >= maxDiputados) {
                checkbox.disabled = true;
                checkbox.closest('.list-group-item').classList.add('text-muted');
            } else {
                checkbox.disabled = false;
                checkbox.closest('.list-group-item').classList.remove('text-muted');
            }
        });
    }

    // Agregar event listeners a los checkboxes de candidatos
    candidatosCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', actualizarSeleccionDiputados);
    });

    // Inicializar estado
    actualizarSeleccionDiputados();

    // Función global para abrir candidatos de diputados
    window.abrirCandidatosDiputados = function(planillaId, partido) {
        const modalId = '#modalCandidatos' + planillaId;
        const modalEl = document.querySelector(modalId);
        if (modalEl) {
            const instancia = bootstrap.Modal.getOrCreateInstance(modalEl);
            instancia.show();
        }
    };

    // Función para volver del modal de candidatos al modal de selección de partido
    window.volverASeleccionPartido = function(planillaId) {
        // Cerrar el modal actual de candidatos
        const modalCandidatosId = '#modalCandidatos' + planillaId;
        const modalCandidatosEl = document.querySelector(modalCandidatosId);
        if (modalCandidatosEl) {
            const instanciaCandidatos = bootstrap.Modal.getInstance(modalCandidatosEl);
            if (instanciaCandidatos) {
                instanciaCandidatos.hide();
            }
        }

        // Mostrar el modal principal de diputados después de un pequeño delay
        setTimeout(() => {
            const modalDiputadosEl = document.getElementById('modalVotacionDiputados');
            if (modalDiputadosEl) {
                const instanciaDiputados = bootstrap.Modal.getOrCreateInstance(modalDiputadosEl);
                instanciaDiputados.show();
            }
        }, 300);
    };

    // Función global para abrir votación de alcalde por municipio
    window.abrirVotacionAlcalde = async function(municipioId, municipioNombre) {
        const modalEl = document.getElementById('modalVotacionAlcalde');
        const tituloModal = document.getElementById('tituloModalAlcalde');
        const contenidoModal = document.getElementById('contenidoModalAlcalde');
        
        if (!modalEl || !tituloModal || !contenidoModal) return;
        
        tituloModal.innerHTML = `<i class="bi bi-building"></i> Candidatos a Alcalde - ${municipioNombre}`;
        contenidoModal.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando candidatos...</p></div>';
        
        const instancia = bootstrap.Modal.getOrCreateInstance(modalEl);
        instancia.show();
        
        try {
            const response = await fetch(`../admin/obtener_alcaldes.php?municipio_id=${municipioId}&municipio_nombre=${encodeURIComponent(municipioNombre)}`);
            const data = await response.json();
            
            if (data.error) {
                contenidoModal.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }
            
            if (!data.planillas || data.planillas.length === 0) {
                contenidoModal.innerHTML = '<div class="alert alert-warning">No hay candidatos disponibles para este municipio.</div>';
                return;
            }
            
            const nombreMunicipio = data.municipio || municipioNombre;
            tituloModal.innerHTML = `<i class="bi bi-building"></i> Candidatos a Alcalde - ${nombreMunicipio}`;
            let html = '';
            
            if (data.imagen) {
                html += `<div class="text-center mb-4"><img src="../../imagen.php?img=${data.imagen}" alt="Planilla ${nombreMunicipio}" style="max-width: 100%; height: auto; max-height: 300px; object-fit: contain;"></div>`;
            }
            
            html += '<p class="text-muted mb-3">Selecciona el partido de tu preferencia. Tu voto contará para alcalde y vicealcalde:</p>';
            html += '<form method="POST" action="../../scripts/procesar_voto.php" id="formVotacionAlcalde">';
            html += '<input type="hidden" name="tipo_voto" value="alcalde">';
            html += '<input type="hidden" name="municipio_id" value="' + municipioId + '">';
            html += '<div class="list-group mb-3">';
            
            data.planillas.forEach(planilla => {
                html += `<label class="list-group-item"><div class="d-flex align-items-start"><input class="form-check-input me-3 mt-1" type="radio" name="planilla_id" value="${planilla.planilla_id}" required><div class="flex-fill"><div class="fw-bold mb-2">${planilla.partido}</div><div class="small"><div class="mb-1"><strong>Alcalde:</strong> ${planilla.alcalde || 'No especificado'}</div><div><strong>Vicealcalde:</strong> ${planilla.vicealcalde || 'No especificado'}</div></div></div></div></label>`;
            });
            
            html += '</div><div class="text-center"><button type="submit" class="btn btn-warning btn-lg"><i class="bi bi-check-circle"></i> Confirmar Voto por Alcalde</button></div></form>';
            contenidoModal.innerHTML = html;
            
        } catch (error) {
            console.error('Error al cargar candidatos:', error);
            contenidoModal.innerHTML = '<div class="alert alert-danger">Error al cargar los candidatos. Intenta nuevamente.</div>';
        }
    };
});