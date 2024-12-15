document.addEventListener('DOMContentLoaded', function() {
    // Seleccionar todos los formularios de eliminación
    const formsEliminar = document.querySelectorAll('.form-eliminar');
    
    formsEliminar.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let mensaje = '¿Estás seguro de que deseas eliminar este elemento?';
            let tipo = form.getAttribute('data-tipo');
            
            // Personalizar mensaje según el tipo
            switch(tipo) {
                case 'usuario':
                    mensaje = '¿Estás seguro de que deseas eliminar este usuario?';
                    break;
                case 'mesa':
                    mensaje = '¿Estás seguro de que deseas eliminar esta mesa?';
                    break;
                case 'sala':
                    mensaje = '¿Estás seguro de que deseas eliminar esta sala?';
                    break;
                case 'reserva':
                    mensaje = '¿Estás seguro de que deseas eliminar esta reserva?';
                    break;
            }

            Swal.fire({
                title: '¿Confirmar eliminación?',
                text: mensaje,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
});
