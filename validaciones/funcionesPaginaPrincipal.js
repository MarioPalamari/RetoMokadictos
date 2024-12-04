// Función para mostrar el estado de las mesas de Terraza 1, 2 y 3
function mostrarEstadoTerrazas() {
    // Obtener los datos desde los atributos data- de un elemento
    const estadoTerraza = document.getElementById('estadoTerraza');

    const mesasOcupadasT1 = estadoTerraza.getAttribute('data-ocupadas-t1');
    const mesasLibresT1 = estadoTerraza.getAttribute('data-libres-t1');
    const mesasOcupadasT2 = estadoTerraza.getAttribute('data-ocupadas-t2');
    const mesasLibresT2 = estadoTerraza.getAttribute('data-libres-t2');
    const mesasOcupadasT3 = estadoTerraza.getAttribute('data-ocupadas-t3');
    const mesasLibresT3 = estadoTerraza.getAttribute('data-libres-t3');

    Swal.fire({
        title: '<h2 style="color: white; font-family: \'Sancreek\', cursive;">Estado de Terrazas</h2>',
        html: `
            <div class="mesa-info">
                <p><strong>Terraza 1</strong><br>Mesas ocupadas: <strong>${mesasOcupadasT1}</strong><br>Mesas libres: <strong>${mesasLibresT1}</strong></p>
            </div>
            <div class="mesa-info">
                <p><strong>Terraza 2</strong><br>Mesas ocupadas: <strong>${mesasOcupadasT2}</strong><br>Mesas libres: <strong>${mesasLibresT2}</strong></p>
            </div>
            <div class="mesa-info">
                <p><strong>Terraza 3</strong><br>Mesas ocupadas: <strong>${mesasOcupadasT3}</strong><br>Mesas libres: <strong>${mesasLibresT3}</strong></p>
            </div>`,
        showConfirmButton: true,
        confirmButtonText: 'Cerrar',
        customClass: {
            popup: 'custom-swal-popup',
            title: 'custom-swal-title',
            content: 'custom-swal-content'
        },
        background: 'rgba(210, 180, 140, 0.8)', 
        backdrop: 'rgba(0, 0, 0, 0.5)'
    });
}

// Función para mostrar el estado de las mesas de Salones
function mostrarEstadoSalones() {
    const estadoTerraza = document.getElementById('estadoTerraza');
    
    const mesasOcupadasS1 = estadoTerraza.getAttribute('data-ocupadas-s1');
    const mesasLibresS1 = estadoTerraza.getAttribute('data-libres-s1');
    const mesasOcupadasS2 = estadoTerraza.getAttribute('data-ocupadas-s2');
    const mesasLibresS2 = estadoTerraza.getAttribute('data-libres-s2');
    
    Swal.fire({
        title: '<h2 style="color: white; font-family: \'Sancreek\', cursive;">Estado de Salones</h2>',
        html: `
            <div class="mesa-info">
                <p><strong>Salon 1</strong><br>Mesas ocupadas: <strong>${mesasOcupadasS1}</strong><br>Mesas libres: <strong>${mesasLibresS1}</strong></p>
            </div>
            <div class="mesa-info">
                <p><strong>Salon 2</strong><br>Mesas ocupadas: <strong>${mesasOcupadasS2}</strong><br>Mesas libres: <strong>${mesasLibresS2}</strong></p>
            </div>`,
        showConfirmButton: true,
        confirmButtonText: 'Cerrar',
        customClass: {
            popup: 'custom-swal-popup',
            title: 'custom-swal-title',
            content: 'custom-swal-content'
        },
        background: 'rgba(210, 180, 140, 0.8)', 
        backdrop: 'rgba(0, 0, 0, 0.5)'
    });
}

function mostrarEstadoVIPS() {
    const estadoTerraza = document.getElementById('estadoTerraza');
    
    const mesasOcupadasV1 = estadoTerraza.getAttribute('data-ocupadas-v1');
    const mesasLibresV1 = estadoTerraza.getAttribute('data-libres-v1');
    const mesasOcupadasV2 = estadoTerraza.getAttribute('data-ocupadas-v2');
    const mesasLibresV2 = estadoTerraza.getAttribute('data-libres-v2');
    const mesasOcupadasV3 = estadoTerraza.getAttribute('data-ocupadas-v3');
    const mesasLibresV3 = estadoTerraza.getAttribute('data-libres-v3');
    const mesasOcupadasV4 = estadoTerraza.getAttribute('data-ocupadas-v4');
    const mesasLibresV4 = estadoTerraza.getAttribute('data-libres-v4');
    
    Swal.fire({
        title: '<h2 style="color: white; font-family: \'Sancreek\', cursive;">Estado de VIPS</h2>',
        html: `
            <div class="mesa-info-container">
                <div class="mesa-info2">
                    <p><strong>VIP 1</strong><br>Mesas ocupadas: <strong>${mesasOcupadasV1}</strong><br>Mesas libres: <strong>${mesasLibresV1}</strong></p>
                </div>
                <div class="mesa-info2">
                    <p><strong>VIP 2</strong><br>Mesas ocupadas: <strong>${mesasOcupadasV2}</strong><br>Mesas libres: <strong>${mesasLibresV2}</strong></p>
                </div>
                <div class="mesa-info2">
                    <p><strong>VIP 3</strong><br>Mesas ocupadas: <strong>${mesasOcupadasV3}</strong><br>Mesas libres: <strong>${mesasLibresV3}</strong></p>
                </div>
                <div class="mesa-info2">
                    <p><strong>VIP 4</strong><br>Mesas ocupadas: <strong>${mesasOcupadasV4}</strong><br>Mesas libres: <strong>${mesasLibresV4}</strong></p>
                </div>
            </div>`,
        showConfirmButton: true,
        confirmButtonText: 'Cerrar',
        customClass: {
            popup: 'custom-swal-popup',
            title: 'custom-swal-title',
            content: 'custom-swal-content'
        },
        background: 'rgba(210, 180, 140, 0.8)', 
        backdrop: 'rgba(0, 0, 0, 0.5)'
    });
}