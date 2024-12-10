// Función para mostrar las opciones de una mesa
function openTableOptions(tableId, status, romanTableId) {
    const actions = [];

    // Lógica para determinar las opciones disponibles según el estado actual
    if (status === 'free') {
        actions.push({ label: 'Ocupar Mesa', value: 'occupy' });
        actions.push({ label: 'Reservar Mesa', value: 'reserve' });
    } else if (status === 'occupied') {
        actions.push({ label: 'Desocupar Mesa', value: 'free' });
    } else if (status === 'reserved') {
        actions.push({ label: 'Cancelar Reserva', value: 'free' });
        actions.push({ label: 'Ocupar Mesa', value: 'occupy' });
    }

    // Crear los botones dinámicamente
    let optionsHtml = actions.map(action => 
        `<button onclick="${action.value === 'reserve' ? `openReservationForm(${tableId}, '${romanTableId}')` : `submitAction(${tableId}, '${action.value}')`}"
                style="padding: 10px 20px; margin: 5px; background-color: #8A5021; color: white; 
                border: none; border-radius: 10px; cursor: pointer; width: 250px; text-align: center;">
            ${action.label}
        </button>`
    ).join('');

    // Mostrar el SweetAlert con las opciones
    Swal.fire({
        title: `<h2 style="color: white; font-family: 'Sancreek', cursive;">Mesa ${romanTableId}</h2>`,
        html: `<div style="display: flex; flex-direction: column; align-items: center;">${optionsHtml}</div>`,
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: '<span>Cancelar</span>',
        customClass: {
            popup: 'custom-swal-popup',
            title: 'custom-swal-title',
            content: 'custom-swal-content'
        },
        background: 'rgba(210, 180, 140, 0.8)',  
        backdrop: 'rgba(0, 0, 0, 0.5)'
    });
}

// Función para mostrar el formulario de reserva
function openReservationForm(tableId, romanTableId) {
    Swal.fire({
        title: `Reservar Mesa ${romanTableId}`,
        html: `
            <input type="date" id="reservationDate" class="swal2-input" placeholder="Fecha de reserva">
            <input type="time" id="startTime" class="swal2-input" placeholder="Hora de inicio">
            <input type="time" id="endTime" class="swal2-input" placeholder="Hora de fin">
        `,
        confirmButtonText: 'Reservar',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const reservationDate = document.getElementById('reservationDate').value;
            const startTime = document.getElementById('startTime').value;
            const endTime = document.getElementById('endTime').value;

            if (!reservationDate || !startTime || !endTime) {
                Swal.showValidationMessage('Todos los campos son obligatorios');
                return false;
            }

            return { reservationDate, startTime, endTime };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Actualiza el formulario oculto con los valores proporcionados
            document.getElementById(`reservationDate${tableId}`).value = result.value.reservationDate;
            document.getElementById(`startTime${tableId}`).value = result.value.startTime;
            document.getElementById(`endTime${tableId}`).value = result.value.endTime;

            // Cambia la acción a 'reserve' y envía el formulario
            submitAction(tableId, 'reserve');
        }
    });
}

// Función para enviar la acción seleccionada
function submitAction(tableId, action) {
    document.getElementById(`action${tableId}`).value = action;
    document.getElementById(`formMesa${tableId}`).submit();
}
