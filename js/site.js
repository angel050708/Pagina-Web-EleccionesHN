
$(function () {
    // resaltar enlace activo según ruta básica
    const path = window.location.pathname.split('/').pop();
    const current = path === '' ? 'index.html' : path;

    $('.navbar-nav .nav-link').each(function () {
        const href = $(this).attr('href');
        if (href === current) {
            $(this).addClass('active');
        }
    });

    // placeholder para futuras animaciones

    const $videoModal = $('#videoModal');
    const $promoFrame = $('#promoVideo');

    if ($promoFrame.length) {
        const originalSrc = $promoFrame.attr('src');

        $videoModal.on('show.bs.modal', function () {
            if (!$promoFrame.attr('src')) {
                $promoFrame.attr('src', originalSrc);
            }
        });

        $videoModal.on('hidden.bs.modal', function () {
            $promoFrame.attr('src', '');
        });
    }
});
