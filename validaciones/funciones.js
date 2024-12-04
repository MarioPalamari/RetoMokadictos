// Consolidate logout functions into one
function logout1() {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Seguro que quieres cerrar sesión?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'cerrarSesion/logout.php';
        }
    });
}
function logout() {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Seguro que quieres cerrar sesión?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../cerrarSesion/logout.php';
        }
    });
}
