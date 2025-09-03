document.addEventListener('DOMContentLoaded', function () {
    const cpfInput = document.querySelector('input[name="cpf"]');
    const telefoneInput = document.querySelector('input[name="telefone"]');

    if (cpfInput) {
        cpfInput.addEventListener('input', () => {
            let valor = cpfInput.value.replace(/\D/g, '');
            if (valor.length > 11) valor = valor.slice(0, 11);
            cpfInput.value = valor
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        });

        // Formatar valor existente ao carregar a página
        cpfInput.dispatchEvent(new Event('input'));
    }

    if (telefoneInput) {
        telefoneInput.addEventListener('input', () => {
            let valor = telefoneInput.value.replace(/\D/g, '');
            if (valor.length > 11) valor = valor.slice(0, 11);
            telefoneInput.value = valor
                .replace(/^(\d{2})(\d)/, '($1) $2')
                .replace(/(\d{5})(\d{4})$/, '$1-$2');
        });

        // Formatar valor existente ao carregar a página
        telefoneInput.dispatchEvent(new Event('input'));
    }
});
