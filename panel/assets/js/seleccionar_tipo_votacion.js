// JavaScript para selección de tipo de votación
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.voting-type-card, .tipo-card');
    const radios = document.querySelectorAll('input[name="tipo_votante"]');
    const btnConfirmar = document.getElementById('btnConfirmar');

    
    cards.forEach(card => {
        card.addEventListener('click', function() {
            const type = this.dataset.type;
            const radio = document.getElementById(type);
            
            
            cards.forEach(c => c.classList.remove('selected'));
            radios.forEach(r => r.checked = false);
            
            
            this.classList.add('selected');
            if (radio) {
                radio.checked = true;
            }
            
            
            if (btnConfirmar) {
                btnConfirmar.disabled = false;
            }
        });
    });

    
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