document.getElementById('agruparMesasBtn').addEventListener('click', function() {
    // Obtener todas las mesas seleccionadas
    let selectedTables = [];
    let selectedTableNumbers = [];
    
    document.querySelectorAll('.mesa-checkbox:checked').forEach(function(checkbox) {
        selectedTables.push(checkbox.value);
        selectedTableNumbers.push(checkbox.getAttribute('data-table-number'));
    });

    // Verificar si hay mesas seleccionadas
    if (selectedTables.length > 0) {
        // Mostrar SweetAlert con las mesas seleccionadas
        Swal.fire({
            title: 'Mesas seleccionadas',
            html: `<p>Mesas: ${selectedTableNumbers.join(', ')}</p>`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Agrupar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                // Al confirmar, establecer las mesas seleccionadas en el formulario oculto
                document.getElementById('selectedTables').value = JSON.stringify(selectedTables);
                // Enviar el formulario
                document.getElementById('groupForm').submit();
            }
        });
    } else {
        Swal.fire({
            title: 'Error',
            text: 'Debe seleccionar al menos una mesa para agrupar.',
            icon: 'error',
            confirmButtonText: 'Cerrar'
        });
    }
});
