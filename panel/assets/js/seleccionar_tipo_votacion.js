// JavaScript para selección de tipo de votación
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.voting-type-card, .tipo-card');
    const radios = document.querySelectorAll('input[name="tipo_votante"]');
    const btnConfirmar = document.getElementById('btnConfirmar');

    // Manejar clics en las tarjetas
    cards.forEach(card => {
        card.addEventListener('click', function() {
            const type = this.dataset.type;
            const radio = document.getElementById(type);
            
            // Limpiar selecciones anteriores
            cards.forEach(c => c.classList.remove('selected'));
            radios.forEach(r => r.checked = false);
            
            // Seleccionar actual
            this.classList.add('selected');
            if (radio) {
                radio.checked = true;
            }
            
            // Habilitar botón
            if (btnConfirmar) {
                btnConfirmar.disabled = false;
            }
        });
    });

    // Manejar cambios en radio buttons
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            cards.forEach(c => c.classList.remove('selected'));
            const card = document.querySelector(`[data-type="${this.value}"]`);
            if (card) {
                card.classList.add('selected');
            }
            if (btnConfirmar) {
                btnConfirmar.disabled = false;
            }
        });
    });

    // Agregar efectos visuales responsive
    function checkScreenSize() {
        if (window.innerWidth <= 768) {
            cards.forEach(card => {
                card.style.marginBottom = '1rem';
            });
        }
    }

    window.addEventListener('resize', checkScreenSize);
    checkScreenSize();
});