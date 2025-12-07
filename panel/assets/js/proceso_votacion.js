setInterval(function() {
    if (!document.hidden) {
        location.reload();
    }
}, 30000);

document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const accion = this.querySelector('input[name="accion"]')?.value;
        if (accion === 'finalizar_votacion') {
            if (!confirm('ATENCIÓN: Al finalizar la votación no se podrán registrar más votos. ¿Estás seguro?')) {
                e.preventDefault();
            }
        }
    });
});