/**
 * Redireciona para uma URL de edição já pronta
 * @param {string} url - URL completa para editar o registro
 */
function editarRegistro(url) {
    window.location.href = url;
}

/**
 * Redireciona para uma URL de exclusão já pronta, com confirmação
 * @param {string} url - URL completa para excluir o registro
 */
function excluirRegistro(url) {
    if (confirm("Tem certeza que deseja excluir este registro?")) {
        window.location.href = url;
    }
}

/*
Importe o novo script genérico:
<script src="../../assets/Js/script_editar_excluir_generico.js"></script>
*/