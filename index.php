<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EleccionesHN</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <header>
        <div class="bg-primary text-light py-2">
            <div class="container d-flex flex-wrap justify-content-between align-items-center small">
                <span class="fw-semibold"><i class="bi bi-megaphone me-2"></i>Elecciones Generales Honduras · 30 de noviembre de 2025</span>
                <span class="text-white-50"><i class="bi bi-shield-lock me-2"></i>Sitio oficial del Tribunal Supremo Electoral</span>
            </div>
        </div>

        <nav class="navbar navbar-expand-lg navbar-light navbar-elecciones">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="index.html">
                    <img class="navbar-logo me-2" src="imagen.php?img=cne_logo.png" alt="EleccionesHN">
                    <div class="navbar-tagline d-none d-md-block text-uppercase">Tribunal Supremo Electoral</div>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Mostrar navegación">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav ms-auto align-items-lg-center">
                        <li class="nav-item"><a class="nav-link" href="index.html">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="#planillas">Planillas</a></li>
                        <li class="nav-item"><a class="nav-link nav-nowrap" href="#proceso">Cómo votar</a></li>
                        <li class="nav-item"><a class="nav-link" href="#transparencia">Transparencia</a></li>
                        <li class="nav-item"><a class="nav-link" href="#testimonios">Testimonios</a></li>
                        <li class="nav-item"><a class="nav-link" href="#faq">FAQs</a></li>
                    </ul>
                <div class="d-lg-none mt-3 w-100">
                        <a class="btn btn-nav-cta w-100" href="login.php">Inicia Sesion</a>
                    </div>

                    <div class="d-none d-lg-block ms-4">
                        <a class="btn btn-nav-cta" href="login.php">Inicia Sesion</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero-banner" id="inicio">
            <div class="container position-relative">
                <div class="row align-items-center gy-5">
                    <div class="col-lg-6">
                        <div class="hero-copy text-center text-lg-start">
                            <span class="badge text-uppercase mb-3">Elecciones 2025</span>
                            <h1 class="display-5 fw-bold mb-4">Participa informado y haz que Honduras avance</h1>
                            <p class="lead mb-4">Ponemos a tu alcance toda la información oficial, herramientas digitales y acompañamiento para que tu voto sea seguro, transparente y accesible desde cualquier parte del país o el extranjero.</p>
                            <div class="hero-buttons d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                                <a class="btn btn-warning text-dark fw-semibold" href="#proceso">Conoce el proceso</a>
                                <a class="btn btn-outline-light fw-semibold" href="#video-modal" data-bs-toggle="modal" data-bs-target="#videoModal">
                                    <i class="bi bi-play-fill me-2"></i>Ver Video
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 ms-lg-auto">
                        <div class="hero-visual text-center text-lg-end">
                            <img class="img-fluid rounded-4 shadow-lg" src="imagen.php?img=ciudadano.jpg" alt="Ciudadanos participando en elecciones">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5" id="planillas">
            <div class="container">
                <div class="row align-items-center mb-4">
                    <div class="col-lg-10 col-xl-8">
                        <h2 class="section-heading mb-3">Planillas en contienda</h2>
                        <p class="section-sub mb-0">Selecciona la planilla correspondiente al cargo para conocer a los postulantes, sus propuestas y los requisitos validados por el Tribunal Supremo Electoral.</p>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <article class="card card-planilla h-100 p-4">
                            <span class="codigo">Numero #1</span>
                            <div class="icon-wrap icon-presidencial">
                                <i class="bi bi-building-check"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-2">Planillas Presidenciales</h3>
                            <p class="text-muted mb-4">Podra votar por los postulantes al puesto de Presidente de la Republica de Honduras</p>
                           
                        </article>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <article class="card card-planilla h-100 p-4">
                            <span class="codigo">Numero #2</span>
                            <div class="icon-wrap icon-legislativo">
                                <i class="bi bi-bank"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-2">Diputados al Congreso</h3>
                            <p class="text-muted mb-4">Los diputados que se postulan para el congreso nacional</p>
                            
                        </article>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <article class="card card-planilla h-100 p-4">
                            <span class="codigo">Numero #3</span>
                            <div class="icon-wrap icon-municipal">
                                <i class="bi bi-buildings"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-2">Alcaldia Departamental</h3>
                            <p class="text-muted mb-4">Conoce las planillas de alcaldías, regidurías y propuestas para tu municipio y departamento</p>
                            
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5 bg-white" id="proceso">
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-5">
                        <h2 class="section-heading mb-3">Tu camino para votar</h2>
                        <p class="section-sub">Asegura tu participación cumpliendo cada paso. Descarga guías, verifica tu DNI y conoce cómo funciona el voto asistido o en el exterior.</p>
                        <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                            
                            <a class="btn btn-outline-secondary" href="docs/sufragio.pdf" download>Descargar guía</a>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="block-highlight bg-light">
                            <div class="timeline-paso mb-4" data-paso="01">
                                <h3 class="h6 fw-semibold mb-1">Verifica tus datos</h3>
                                <p class="text-muted mb-0">Confirma que tu DNI esté vigente y tus datos estén actualizados en el Registro Nacional de las Personas. En caso de no estarlo asegurate de ir en cuanto antes a renovar tu DNI</p>
                            </div>
                            <div class="timeline-paso mb-4" data-paso="02">
                                <h3 class="h6 fw-semibold mb-1">Ubica tu centro de votación</h3>
                                <p class="text-muted mb-0">Utiliza nuestro buscador oficial que se ubica en nuestra pagina web actual o acercate a un puesto de algun partido politico y ellos te ayudaran para localizar tu junta receptora.</p>
                            </div>
                            <div class="timeline-paso mb-4" data-paso="03">
                                <h3 class="h6 fw-semibold mb-1">Inicia Sesion</h3>
                                <p class="text-muted mb-0">Una vez dentro de nuestro sitio web busca el boton de "inicio de sesion"  e ingresa tus datos correspondientes para el proceso de votacion, una vez dentro selecciona las planillas correspondientes a Presidente, Alcalde y Diputados de tu Departamento</p>
                            </div>
                            <div class="timeline-paso" data-paso="04">
                                <h3 class="h6 fw-semibold mb-1">Vota y confirma</h3>
                                <p class="text-muted mb-0">Despues de haber Iniciada la sesion escoge las planillas y selecciona a quien quieras darle tu voto, recuerda el voto es un derecho y es secreto! </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5" id="lugares-votacion">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="section-heading">Lugares de votación</h2>
                    <p class="section-sub mx-auto">En esta página web puedes encontrar información sobre los centros de votación ubicados en universidades, escuelas, colegios y puntos designados en Honduras.</p>
                </div>
                <div class="row g-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="text-center p-4 bg-light rounded-4 h-100">
                            <div class="mb-3">
                                <i class="bi bi-building display-4 text-primary"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-2">Universidades</h3>
                            <p class="text-muted mb-0">Centros de votación en campus universitarios públicos y privados a nivel nacional.</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="text-center p-4 bg-light rounded-4 h-100">
                            <div class="mb-3">
                                <i class="bi bi-book display-4 text-primary"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-2">Escuelas</h3>
                            <p class="text-muted mb-0">Centros educativos primarios habilitados como juntas receptoras de votos.</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="text-center p-4 bg-light rounded-4 h-100">
                            <div class="mb-3">
                                <i class="bi bi-mortarboard display-4 text-primary"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-2">Colegios</h3>
                            <p class="text-muted mb-0">Instituciones de educación secundaria designadas para el proceso electoral.</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="text-center p-4 bg-light rounded-4 h-100">
                            <div class="mb-3">
                                <i class="bi bi-geo-alt display-4 text-primary"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-2">En EleccionesHN nuestro sistema </h3>
                            <p class="text-muted mb-0">En nuestra pagina web EleccionesHN, aqui mismo puedes realizar tu voto siguiendo las instrucciones proporcionadas.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5 bg-white" id="transparencia">
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-5">
                        <h2 class="section-heading mb-3">Transparencia y fiscalización</h2>
                        <p class="section-sub">Publicamos datos abiertos, auditorías en tiempo real y reportes de observadores nacionales e internacionales para garantizar la legitimidad del proceso.</p>
                        
                    </div>
                    <div class="col-lg-7">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="dato-resumen">
                                    <p class="text-uppercase text-muted small mb-1">Actas digitalizadas</p>
                                    <p class="numero">12,450</p>
                                    <p class="mb-0 text-muted small">Disponibles para consulta y descarga ciudadana.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="dato-resumen">
                                    <p class="text-uppercase text-muted small mb-1">Auditorías en curso</p>
                                    <p class="numero">37</p>
                                    <p class="mb-0 text-muted small">Supervisadas por entes nacionales e internacionales.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="dato-resumen">
                                    <p class="text-uppercase text-muted small mb-1">Reportes ciudadanos</p>
                                    <p class="numero">+1,200</p>
                                    <p class="mb-0 text-muted small">Casos recibidos y atendidos por la mesa de incidencias.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="dato-resumen">
                                    <p class="text-uppercase text-muted small mb-1">Organismos aliados</p>
                                    <p class="numero">18</p>
                                    <p class="mb-0 text-muted small">Organizaciones nacionales y misiones internacionales.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5" id="testimonios">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="section-heading">Voces de la ciudadanía</h2>
                    <p class="section-sub mx-auto">Historias reales de hondureños que ya utilizaron los servicios de EleccionesHN y confían en el proceso.</p>
                </div>
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="testimonio h-100">
                            <div class="d-flex align-items-center mb-3">
                                <img class="rounded-circle me-3" src="imagen.php?img=testimonio-1.png" alt="Testimonio Tegucigalpa" width="56" height="56">
                                <div>
                                    <h3 class="h6 mb-0">María Elena López</h3>
                                    <small class="text-muted">Tegucigalpa, FM</small>
                                </div>
                            </div>
                            <p class="mb-0">“Recibí la capacitación virtual para miembros de mesa y todo el proceso fue claro. Ahora me siento preparada para garantizar un voto transparente.”</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="testimonio h-100">
                            <div class="d-flex align-items-center mb-3">
                                <img class="rounded-circle me-3" src="imagen.php?img=testimonio-2.jpg" alt="Testimonio San Pedro Sula" width="56" height="56">
                                <div>
                                    <h3 class="h6 mb-0">Luis Fernando Reyes</h3>
                                    <small class="text-muted">San Pedro Sula, CR</small>
                                </div>
                            </div>
                            <p class="mb-0">“La consulta de centros me ayudó a ubicar mi junta receptora y la de mis padres. Todo en minutos y desde el celular.”</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="testimonio h-100">
                            <div class="d-flex align-items-center mb-3">
                                <img class="rounded-circle me-3" src="imagen.php?img=testimonio-3.jpg" alt="Testimonio Exterior" width="56" height="56">
                                <div>
                                    <h3 class="h6 mb-0">Ana Paola Castillo</h3>
                                    <small class="text-muted">Madrid, España</small>
                                </div>
                            </div>
                            <p class="mb-0">“Desde el exterior, todo quedó registrado y recibí confirmación del voto. Es la primera vez que participo y fue sencillo.”</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5 bg-white" id="servicios">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="section-heading">Servicios del TSE</h2>
                    <p class="section-sub mx-auto">Responsabilidades y servicios oficiales que presta el Tribunal Supremo Electoral de Honduras a la ciudadanía y a las organizaciones políticas.</p>
                </div>
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                            <div class="mb-3">
                                <i class="bi bi-people-fill text-primary display-6"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-2">Administración del censo electoral</h3>
                            <p class="text-muted mb-0">Depuración constante del padrón, inscripción de nuevos ciudadanos y gestión de traslados para garantizar un registro confiable.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                            <div class="mb-3">
                                <i class="bi bi-clipboard-check text-primary display-6"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-2">Inscripción de candidaturas</h3>
                            <p class="text-muted mb-0">Recepción, verificación legal y aprobación de planillas presentadas por partidos políticos y candidaturas independientes.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                            <div class="mb-3">
                                <i class="bi bi-calendar-event text-primary display-6"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-2">Organización de procesos electorales</h3>
                            <p class="text-muted mb-0">Planificación logística, impresión de papeletas y coordinación de juntas receptoras para cada jornada electoral.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                            <div class="mb-3">
                                <i class="bi bi-person-workspace text-primary display-6"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-2">Capacitación electoral</h3>
                            <p class="text-muted mb-0">Programas formativos para miembros de mesas electorales, observadores y actores políticos sobre normativa y buenas prácticas.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                            <div class="mb-3">
                                <i class="bi bi-bar-chart text-primary display-6"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-2">Emisión de resultados oficiales</h3>
                            <p class="text-muted mb-0">Conteo preliminar, escrutinio definitivo y declaratoria de resultados con publicación de actas y memorias electorales.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                            <div class="mb-3">
                                <i class="bi bi-shield-check text-primary display-6"></i>
                            </div>
                            <h3 class="h5 fw-semibold mb-2">Fiscalización de partidos políticos</h3>
                            <p class="text-muted mb-0">Supervisión del cumplimiento de la ley electoral, control de financiamiento y acompañamiento a las organizaciones políticas.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5 bg-white" id="galeria">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="section-heading">Galería y materiales</h2>
                    <p class="section-sub mx-auto">Descarga piezas de comunicación, manuales y resúmenes visuales del proceso electoral.</p>
                </div>
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-thumb">
                            <img class="img-fluid" src="imagen.php?img=galeria-1.jpeg" alt="Jornada de capacitación">
                            <span>Capacitación ciudadana</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-thumb">
                            <img class="img-fluid" src="imagen.php?img=galeria-2.PNG" alt="Centro de votación">
                            <span>Centros de votación</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-thumb">
                            <img class="img-fluid" src="imagen.php?img=galeria-3.webp" alt="Observadores">
                            <span>Observadores en campo</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-thumb">
                            <img class="img-fluid" src="imagen.php?img=galeria-4.jpg" alt="Conteo de votos">
                            <span>Conteo transparente</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-thumb">
                            <img class="img-fluid" src="imagen.php?img=galeria-5.jpg" alt="Junta receptora">
                            <span>Juntas receptoras</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-thumb">
                            <img class="img-fluid" src="imagen.php?img=galeria-6.jpg" alt="Voto exterior">
                            <span>Voto en el exterior</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5" id="faq">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <h2 class="section-heading mb-3">Preguntas frecuentes</h2>
                        <p class="section-sub">Resolvemos las dudas más comunes sobre el proceso electoral. Si necesitas atención personalizada, comunícate con nuestra mesa de ayuda.</p>
                        
                    </div>
                    <div class="col-lg-7">
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item border-0 mb-3 shadow-sm">
                                <h2 class="accordion-header" id="faqHeadingOne">
                                    <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faqOne" aria-expanded="true" aria-controls="faqOne">
                                        ¿Cómo verifico si estoy habilitado para votar?
                                    </button>
                                </h2>
                                <div id="faqOne" class="accordion-collapse collapse show" aria-labelledby="faqHeadingOne" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Ingresa tu número de DNI en la plataforma oficial de consulta o llama a la línea gratuita 195. Si encuentras inconsistencias, agenda tu actualización en el RNP.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 mb-3 shadow-sm">
                                <h2 class="accordion-header" id="faqHeadingTwo">
                                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faqTwo" aria-expanded="false" aria-controls="faqTwo">
                                        ¿Cuáles documentos debo presentar el día de la elección?
                                    </button>
                                </h2>
                                <div id="faqTwo" class="accordion-collapse collapse" aria-labelledby="faqHeadingTwo" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Debes presentar tu DNI vigente. Si realizaste el voto en el exterior, también puedes mostrar la constancia digital de registro emitida por el consulado.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 shadow-sm">
                                <h2 class="accordion-header" id="faqHeadingThree">
                                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faqThree" aria-expanded="false" aria-controls="faqThree">
                                        ¿Dónde reporto una irregularidad durante la jornada?
                                    </button>
                                </h2>
                                <div id="faqThree" class="accordion-collapse collapse" aria-labelledby="faqHeadingThree" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Comunícate con la Mesa de Incidencias al 195 o reporta en línea a través del formulario de EleccionesHN. Los casos se atienden en menos de 30 minutos.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="pt-5 pb-0">
        <div class="container pb-4">
            <div class="row g-4 text-center">
                <div class="col-lg-4">
                    <div class="marca mb-2">EleccionesHN</div>
                    <p class="mb-3">Tribunal Supremo Electoral de Honduras<br>Democracia, confianza y participación ciudadana.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="https://www.facebook.com/CNEHondurasOficial#" aria-label="Facebook" target="_blank" rel="noopener"><i class="bi bi-facebook"></i></a>
                        <a href="https://x.com/CneHonduras" aria-label="Twitter" target="_blank" rel="noopener"><i class="bi bi-twitter"></i></a>
                        <a href="https://www.instagram.com/cne_hn/?hl=es" aria-label="Instagram" target="_blank" rel="noopener"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-uppercase small fw-semibold mb-3">Navegación</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a href="#inicio">Inicio</a></li>
                        <li class="mb-2"><a href="#proceso">Cómo votar</a></li>
                        <li class="mb-2"><a href="#lugares-votacion">Centros</a></li>
                        <li class="mb-2"><a href="#galeria">Galería</a></li>
                        <li><a href="#faq">Preguntas frecuentes</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-uppercase small fw-semibold mb-3">Contacto</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>Edificio Edificaciones del Río, Colonia El Prado, frente a SYRE, Tegucigalpa M.D.C., Honduras, C.A.​</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i>+504 2239-1058</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i>secretaria.general@cne.hn</li>
                        <li><i class="bi bi-clock me-2"></i>Lunes a viernes · 8:00am a 9pm</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom border-top border-white-25 text-center small py-3">
            <div class="container d-flex flex-column flex-md-row justify-content-center align-items-center gap-3">
                <p class="mb-0">&copy; 2025 Tribunal Supremo Electoral de Honduras. Todos los derechos reservados.</p>
                <div class="d-flex gap-3">
                    <a href="#">Política de privacidad</a>
                    <a href="#">Términos de uso</a>
                </div>
            </div>
        </div>
    </footer>

    <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="h5 modal-title" id="videoModalLabel">Honduras Muevete a votar!</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="ratio ratio-16x9">
                        <iframe id="promoVideo" src="https://www.youtube.com/embed/vlCndnsN1Es?start=3" title="Honduras Muévete a Votar" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/site.js"></script>
</body>
</html>
