 

    // selectConsultas.addEventListener('change', () => {
    //     const consultaSeleccionada = selectConsultas.value;
    //     if (consultaSeleccionada) {
    //         fetch('Obtener_consultas_admin.php?Consultas=' + encodeURIComponent(consultaSeleccionada))
    //             .then(response => response.json())
    //             .then(data => {
    //                 resultadosConsultas.innerHTML = data.html;
    //             })
    //             .catch(error => {
    //                 resultadosConsultas.innerHTML = '<p>Error al obtener los resultados.</p>';
    //                 console.error(error);
    //             });
    //     } else {
    //         resultadosConsultas.innerHTML = ''; // Limpiar si no hay selección
    //     }
    // });

   const selectConsultas = document.getElementById('Consultas');
    const resultadosConsultas = document.getElementById('resultados-consultas');
    const exportarCsvButton = document.getElementById('exportar-csv');
    const exportarPdfButton = document.getElementById('exportar-pdf');

    function cargarResultados(formato) {
        const consultaSeleccionada = selectConsultas.value;
        if (consultaSeleccionada) {
            fetch('Obtener_consultas_admin.php?Consultas=' + encodeURIComponent(consultaSeleccionada) + '&formato=' + formato)
                .then(response => {
                    if (formato === 'html') {
                        return response.json();
                    } else {
                        return response.text(); // Para CSV y PDF, obtenemos texto plano (aunque PDF no lo usamos directamente)
                    }
                })
                .then(data => {
                    if (formato === 'html') {
                        resultadosConsultas.innerHTML = data.html;
                    } else {
                        // No necesitamos mostrar nada en el div para CSV o PDF, la descarga se inicia automáticamente
                    }
                })
                .catch(error => {
                    resultadosConsultas.innerHTML = '<p>Error al obtener los resultados.</p>';
                    console.error(error);
                });
        } else {
            resultadosConsultas.innerHTML = '';
        }
    }

    selectConsultas.addEventListener('change', () => {
        cargarResultados('html'); // Cargar resultados HTML por defecto
    });

   exportarCsvButton.addEventListener('click', () => {
    const consultaSeleccionada = selectConsultas.value;
    if (consultaSeleccionada) {
        window.location.href = 'Obtener_consultas_admin.php?Consultas=' + encodeURIComponent(consultaSeleccionada) + '&formato=csv';
    }
});

exportarPdfButton.addEventListener('click', () => {
    const consultaSeleccionada = selectConsultas.value;
    if (consultaSeleccionada) {
        window.location.href = 'Obtener_consultas_admin.php?Consultas=' + encodeURIComponent(consultaSeleccionada) + '&formato=pdf';
    }
});