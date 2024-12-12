// Configuración global de SweetAlert2 para mantener un estilo consistente
const sweetAlertOptions = {
    customClass: {
        popup: 'custom-swal-popup',
        title: 'custom-swal-title',
        confirmButton: 'custom-swal-button',
        cancelButton: 'custom-swal-button',
        content: 'custom-swal-content'
    },
    background: '#8A5021',
    color: '#fff'
};

// Función para mostrar las opciones de una mesa
function openTableOptions(tableId, status, romanTableId) {
    let options = [];
    
    if (status === 'free') {
        options = ['Ocupar Mesa', 'Reservar Mesa', 'Ver Reservas'];
    } else if (status === 'occupied') {
        options = ['Liberar Mesa', 'Ver Reservas'];
    } else if (status === 'reserved') {
        options = ['Ocupar Mesa', 'Reservar Mesa', 'Ver Reservas'];
    }

    Swal.fire({
        ...sweetAlertOptions,
        title: `Mesa ${romanTableId}`,
        text: `Estado actual: ${status === 'free' ? 'Libre' : status === 'occupied' ? 'Ocupada' : 'Reservada'}`,
        showCancelButton: true,
        showConfirmButton: false,
        cancelButtonText: 'Cancelar',
        html: options.map(option => 
            `<button class="swal2-confirm swal2-styled custom-swal-button" onclick="handleOption('${option}', ${tableId})">${option}</button>`
        ).join('')
    });
}

function handleOption(option, tableId) {
    switch(option) {
        case 'Ocupar Mesa':
            document.getElementById(`action${tableId}`).value = 'occupy';
            document.getElementById(`formMesa${tableId}`).submit();
            break;
        case 'Liberar Mesa':
            document.getElementById(`action${tableId}`).value = 'free';
            document.getElementById(`formMesa${tableId}`).submit();
            break;
        case 'Reservar Mesa':
            showReservationForm(tableId);
            break;
        case 'Ver Reservas':
            showReservations(tableId);
            break;
    }
}

function showReservationForm(tableId) {
    Swal.fire({
        ...sweetAlertOptions,
        title: 'Realizar Reserva',
        html: `
            <div class="form-group custom-swal-content">
                <label for="reservationDate">Fecha:</label>
                <input type="date" id="reservationDate" class="swal2-input" min="${new Date().toISOString().split('T')[0]}">
            </div>
            <div class="form-group custom-swal-content">
                <label for="startTime">Hora inicio:</label>
                <input type="time" id="startTime" class="swal2-input">
            </div>
            <div class="form-group custom-swal-content">
                <label for="endTime">Hora fin:</label>
                <input type="time" id="endTime" class="swal2-input">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Reservar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const date = document.getElementById('reservationDate').value;
            const start = document.getElementById('startTime').value;
            const end = document.getElementById('endTime').value;
            
            if (!date || !start || !end) {
                Swal.fire({
                    ...sweetAlertOptions,
                    title: 'Error',
                    text: 'Por favor complete todos los campos',
                    icon: 'error'
                });
                return;
            }
            if (start >= end) {
                Swal.fire({
                    ...sweetAlertOptions,
                    title: 'Error',
                    text: 'La hora de fin debe ser posterior a la hora de inicio',
                    icon: 'error'
                });
                return;
            }
            
            document.getElementById(`action${tableId}`).value = 'reserve';
            document.getElementById(`reservationDate${tableId}`).value = date;
            document.getElementById(`startTime${tableId}`).value = start;
            document.getElementById(`endTime${tableId}`).value = end;
            document.getElementById(`formMesa${tableId}`).submit();
        }
    });
}

function showReservations(tableId) {
    fetch(`${window.location.pathname}?action=viewReservations&tableId=${tableId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=viewReservations&tableId=${tableId}`
    })
    .then(response => response.text())
    .then(html => {
        Swal.fire({
            ...sweetAlertOptions,
            title: 'Reservas de la Mesa',
            html: html,
            width: 600,
            confirmButtonText: 'Cerrar'
        });
    });
}

function showReservationDetails(reservationsHtml, tableId) {
    Swal.fire({
        title: 'Reservas de la Mesa',
        html: reservationsHtml,
        width: 600,
        showCancelButton: true,
        confirmButtonText: 'Cerrar',
        cancelButtonText: 'Eliminar Reserva',
        preConfirm: () => {
            // Aquí podrías manejar la lógica para cerrar el modal
        },
        preCancel: () => {
            // Aquí podrías manejar la lógica para eliminar una reserva
            // Por ejemplo, podrías abrir otro SweetAlert para confirmar la eliminación
        }
    });
}

function logout() {
    Swal.fire({
        title: '¿Estás seguro de que quieres cerrar sesión?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../logout.php';
        }
    });
}

function deleteReservation(reservationId) {
    Swal.fire({
        ...sweetAlertOptions,
        title: '¿Estás seguro?',
        text: "No podrás revertir esta acción",
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteReservationId').value = reservationId;
            document.getElementById('deleteReservationForm').submit();
        }
    });
}
