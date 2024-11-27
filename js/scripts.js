
function redirectBusqueda() {
    const selectedOption = document.getElementById('busquedas-especiales').value;
    if (selectedOption) {
        window.location.href = selectedOption;
    }
}

window.onload = loadNavbar;


// script.js

const form = document.getElementById('myForm');
const spinner = document.querySelector('.spinner');

form.addEventListener('submit', (event) => {
    event.preventDefault(); // Previene el envío del formulario por defecto
    spinner.style.display = 'inline-block'; // Muestra el spinner

    // Simulación de un proceso de búsqueda
    setTimeout(() => {
        // Aquí va la lógica para la búsqueda
        spinner.style.display = 'none'; // Oculta el spinner después de completar la búsqueda
    }, 2000); // Simulación de 2 segundos para la búsqueda
});
