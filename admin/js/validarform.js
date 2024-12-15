// Archivo: formValidator.js

document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form:not(.filter-section form)');

    forms.forEach(form => {
        const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
        
        // Validar cada campo cuando pierda el foco
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                validarCampo(input);
            });

            // También validar cuando se modifique el valor
            input.addEventListener('input', () => {
                validarCampo(input);
            });
        });

        form.addEventListener('submit', (event) => {
            let isValid = true;
            
            // Validar todos los campos antes de enviar
            inputs.forEach(input => {
                if (!validarCampo(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                event.preventDefault();
            }
        });
    });
});

function validarCampo(input) {
    // Limpiar mensaje de error previo
    const errorPrevio = input.parentNode.querySelector('.error-message');
    if (errorPrevio) {
        errorPrevio.remove();
    }

    // Restablecer estilos
    input.classList.remove('is-invalid');
    input.style.borderColor = '';

    let isValid = true;
    let mensajeError = '';

    // Validación del valor vacío
    if (!input.value.trim()) {
        isValid = false;
        mensajeError = 'Este campo es obligatorio';
    } else {
        // Validaciones específicas según el tipo de campo
        switch(input.name) {
            case 'username':
                if (input.value.length < 3) {
                    isValid = false;
                    mensajeError = 'El usuario debe tener al menos 3 caracteres';
                }
                break;
            case 'password':
                if (!document.querySelector('[name="user_id"]') && input.value.length < 6) {
                    isValid = false;
                    mensajeError = 'La contraseña debe tener al menos 6 caracteres';
                }
                break;
            case 'capacidad':
                if (parseInt(input.value) <= 0) {
                    isValid = false;
                    mensajeError = 'La capacidad debe ser mayor que 0';
                }
                break;
            case 'numero':
                if (parseInt(input.value) <= 0) {
                    isValid = false;
                    mensajeError = 'El número de mesa debe ser mayor que 0';
                }
                break;
            case 'fecha':
                if (!isValidDate(input.value)) {
                    isValid = false;
                    mensajeError = 'Debe seleccionar una fecha válida';
                }
                break;
            case 'hora':
            case 'hora_fin':
                if (!input.value) {
                    isValid = false;
                    mensajeError = 'Debe seleccionar una hora válida';
                }
                break;
            case 'mesa_id':
                if (!input.value) {
                    isValid = false;
                    mensajeError = 'Debe seleccionar una mesa';
                }
                break;
        }
    }

    if (!isValid) {
        mostrarError(input, mensajeError);
    }

    return isValid;
}

function mostrarError(input, mensaje) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = 'red';
    errorDiv.style.fontSize = '0.8em';
    errorDiv.style.marginTop = '5px';
    errorDiv.textContent = mensaje;
    
    input.classList.add('is-invalid');
    input.style.borderColor = 'red';
    input.parentNode.appendChild(errorDiv);
}

function isValidDate(dateString) {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date);
}
