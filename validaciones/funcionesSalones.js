// Función para mostrar las opciones de ocupar librar de cada mesa
function openTableOptions(tableId, status, romanTableId, roomId) {
    const actions = [
        { label: status === 'free' ? 'Ocupar Mesa' : 'Desocupar Mesa', value: status === 'free' ? 'occupy' : 'free' },
    ];


    let optionsHtml = actions.map(action => 
        `<button onclick="submitAction(${tableId}, '${action.value}', ${roomId})"
                style="padding: 10px 20px; margin: 5px; background-color: #8A5021; color: white; 
                border: none; border-radius: 10px; cursor: pointer; width: 250px; text-align: center;">
            ${action.label}
        </button>`
    ).join('');

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

// Función para enviar la acción seleccionada
function submitAction(tableId, action, roomId) {
    // Establece el valor de la acción en el formulario oculto y envía el formulario
    document.getElementById(`action${tableId}`).value = action;

    if (action === 'move' && roomId) {
        document.getElementById(`newRoomId${tableId}`).value = roomId;
    }
    
    document.getElementById(`formMesa${tableId}`).submit();
}
