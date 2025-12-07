(() => {
    const cargoOptions = [
        'Presidente',
        'Vicepresidente',
        'Designado',
        'Diputado',
        'Alcalde',
        'Vicealcalde',
    ];

    const config = window.planillaFormConfig || {};

    const state = {
        contador: 0,
        candidatosContainer: null,
        departamentoSelect: null,
        municipioSelect: null,
        municipiosUrl: config.municipiosUrl || 'obtener_municipios.php',
    };

    const escapeHtml = (value) => {
        if (value === null || value === undefined) {
            return '';
        }
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    const actualizarTitulos = () => {
        if (!state.candidatosContainer) {
            return;
        }

        const items = state.candidatosContainer.querySelectorAll('.candidato-item');
        items.forEach((item, index) => {
            const titulo = item.querySelector('[data-role="candidate-title"]');
            if (titulo) {
                titulo.textContent = `Candidato ${index + 1}`;
            }
        });
    };

    const eliminarCandidato = (wrapper) => {
        if (!state.candidatosContainer) {
            return;
        }

        wrapper.remove();

        if (state.candidatosContainer.querySelectorAll('.candidato-item').length === 0) {
            agregarCandidato();
        } else {
            actualizarTitulos();
        }
    };

    const crearOpcionesCargo = (cargoSeleccionado) => {
        return cargoOptions
            .reduce((html, cargo) => {
                const seleccionado = cargo === cargoSeleccionado ? 'selected' : '';
                return `${html}<option value="${escapeHtml(cargo)}" ${seleccionado}>${escapeHtml(cargo)}</option>`;
            }, '<option value="">Seleccionar</option>');
    };

    const agregarCandidato = (datos = {}) => {
        if (!state.candidatosContainer) {
            return;
        }

        state.contador += 1;
        const indice = state.contador;

        const wrapper = document.createElement('div');
        wrapper.className = 'candidato-item mb-3 p-3 border rounded bg-light';
        wrapper.dataset.candidateIndex = String(indice);

        const cargoSeleccionado = datos.cargo || '';
        const numero = datos.numero_candidato !== null && datos.numero_candidato !== undefined
            ? datos.numero_candidato
            : '';

        wrapper.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0" data-role="candidate-title">Candidato</h6>
                <div class="d-flex align-items-center gap-2">
                    ${datos.id ? `<span class="badge text-bg-light text-muted">ID ${escapeHtml(datos.id)}</span>` : ''}
                    <button type="button" class="btn btn-outline-danger btn-sm" data-remove-candidato="${indice}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <input type="hidden" name="candidatos[${indice}][id]" value="${datos.id ? escapeHtml(datos.id) : ''}">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Nombre completo *</label>
                    <input type="text" class="form-control" name="candidatos[${indice}][nombre]"
                           value="${escapeHtml(datos.nombre || '')}" placeholder="Nombre y apellidos" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cargo</label>
                    <select class="form-select" name="candidatos[${indice}][cargo]">
                        ${crearOpcionesCargo(cargoSeleccionado)}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Número</label>
                    <input type="number" class="form-control" name="candidatos[${indice}][numero_candidato]"
                           value="${escapeHtml(numero)}" min="1" placeholder="1">
                </div>
            </div>
        `;

        const botonEliminar = wrapper.querySelector('[data-remove-candidato]');
        if (botonEliminar) {
            botonEliminar.addEventListener('click', () => eliminarCandidato(wrapper));
        }

        state.candidatosContainer.appendChild(wrapper);
        actualizarTitulos();
    };

    const poblarMunicipios = (municipios, seleccionado) => {
        if (!state.municipioSelect) {
            return;
        }

        state.municipioSelect.innerHTML = '<option value="">Seleccionar municipio</option>';

        municipios.forEach((municipio) => {
            const option = document.createElement('option');
            option.value = String(municipio.id);
            option.textContent = municipio.nombre;
            if (seleccionado && String(municipio.id) === String(seleccionado)) {
                option.selected = true;
            }
            state.municipioSelect.appendChild(option);
        });

        state.municipioSelect.disabled = municipios.length === 0;
    };

    const cargarMunicipios = (departamentoId, municipioSeleccionado = null) => {
        if (!state.municipioSelect) {
            console.error('No se encontró el elemento municipio_id');
            return;
        }

        if (!departamentoId) {
            state.municipioSelect.innerHTML = '<option value="">Seleccionar municipio</option>';
            state.municipioSelect.disabled = true;
            return;
        }

        console.log('Cargando municipios para departamento:', departamentoId);
        
        fetch(`${state.municipiosUrl}?departamento_id=${encodeURIComponent(departamentoId)}`)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                console.log('Respuesta del servidor:', data);
                if (!data.success || !Array.isArray(data.municipios)) {
                    throw new Error('Respuesta inválida: ' + JSON.stringify(data));
                }
                poblarMunicipios(data.municipios, municipioSeleccionado);
            })
            .catch((error) => {
                console.error('Error al cargar municipios:', error);
                state.municipioSelect.innerHTML = '<option value="">Error al cargar municipios</option>';
                state.municipioSelect.disabled = true;
            });
    };

    const manejarCambioDepartamento = (event) => {
        const departamentoId = event.target.value;
        cargarMunicipios(departamentoId, null);
    };

    const inicializarMunicipios = () => {
        if (!state.departamentoSelect || !state.municipioSelect) {
            return;
        }

        const seleccionInicial = config.initialDepartamentoId || state.departamentoSelect.value;

        if (seleccionInicial) {
            state.departamentoSelect.value = String(seleccionInicial);

            const opcionesActuales = Array.from(state.municipioSelect.options || []);
            const existeSeleccion = config.initialMunicipioId
                && opcionesActuales.some((opt) => String(opt.value) === String(config.initialMunicipioId));

            if (!existeSeleccion) {
                cargarMunicipios(seleccionInicial, config.initialMunicipioId);
            } else {
                state.municipioSelect.value = String(config.initialMunicipioId);
                state.municipioSelect.disabled = false;
            }
        }
    };

    const inicializarCandidatos = () => {
        const iniciales = Array.isArray(config.initialCandidatos) && config.initialCandidatos.length
            ? config.initialCandidatos
            : [{}];

        iniciales.forEach((candidato) => agregarCandidato(candidato));
    };

    const init = () => {
        console.log('Inicializando formulario de planilla...');
        
        state.candidatosContainer = document.getElementById('candidatos-container');
        state.departamentoSelect = document.getElementById('departamento_id');
        state.municipioSelect = document.getElementById('municipio_id');

        console.log('Elementos encontrados:', {
            candidatosContainer: !!state.candidatosContainer,
            departamentoSelect: !!state.departamentoSelect,
            municipioSelect: !!state.municipioSelect
        });

        if (!state.candidatosContainer || !state.departamentoSelect) {
            console.error('No se encontraron elementos requeridos');
            return;
        }

        state.departamentoSelect.addEventListener('change', manejarCambioDepartamento);
        console.log('Event listener agregado al select de departamento');

        inicializarMunicipios();
        inicializarCandidatos();
    };

    document.addEventListener('DOMContentLoaded', init);

    window.planillaForm = {
        agregarCandidato,
    };

    // Compatibilidad con implementaciones previas
    window.agregarCandidato = agregarCandidato;
})();