// JavaScript para la página de denuncias
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textarea
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });

    // Validación del formulario
    const form = document.querySelector('form');
    const fileInput = document.getElementById('evidencia');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const files = this.files;
            const maxSize = 10 * 1024 * 1024; // 10MB
            
            for (let i = 0; i < files.length; i++) {
                if (files[i].size > maxSize) {
                    alert('El archivo "' + files[i].name + '" es demasiado grande. El tamaño máximo es 10MB.');
                    this.value = '';
                    break;
                }
            }
        });
    }

    // Mejorar interfaz de carga de archivos
    if (fileInput) {
        const dropZone = fileInput.closest('.file-upload-zone');
        if (dropZone) {
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });
            
            dropZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });
            
            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                fileInput.files = e.dataTransfer.files;
            });
        }
    }
});