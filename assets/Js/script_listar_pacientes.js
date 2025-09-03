function toggleFiltros() {
    const filtros = document.getElementById('filtros');
    const btn = document.getElementById('toggleBtn');

    if (filtros.style.display === 'none' || filtros.style.display === '') {
        filtros.style.display = 'block';
        btn.textContent = 'Ocultar Filtros';
    } else {
        filtros.style.display = 'none';
        btn.textContent = 'Mostrar Filtros';
    }
}