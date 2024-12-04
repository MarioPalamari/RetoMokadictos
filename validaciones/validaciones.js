function validaNombre() {
    let nombre = document.getElementById("usuario").value; 
    let error_nombre = document.getElementById("error-nombre"); 

    if (nombre == null || nombre.length == 0) {
        error_nombre.innerHTML = "El nombre está vacío";
        return false;
    } else if (!isNaN(nombre)) {
        error_nombre.innerHTML = "El campo no debe contener números"; 
        return false;
    } else if (nombre.length < 3) { 
        error_nombre.innerHTML = "El nombre es demasiado corto";
        return false;
    } else {
        error_nombre.innerHTML = "";
        return true;
    }
}

function validaContraseña() {
    let contraseña = document.getElementById("password").value;
    let error_contraseña = document.getElementById("error_contraseña"); 

    if (contraseña == null || contraseña.length == 0 || /^\s+$/.test(contraseña)) { 
        error_contraseña.innerHTML = "Este campo no puede estar vacío.";
        return false;
    } else if (contraseña.length < 6) {
        error_contraseña.innerHTML = "La contraseña debe contener más de 6 caracteres.";
        return false;
    } else if (!(/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]+$/).test(contraseña)) { 
        error_contraseña.innerHTML = "La contraseña debe contener al menos un número y una letra.";
        return false;
    } else {
        error_contraseña.innerHTML = "";
        return true;
    }
}

function validarFormulario() {
    let isNombreValido = validaNombre();
    let isContraseñaValida = validaContraseña();
    return isNombreValido && isContraseñaValida;
}
