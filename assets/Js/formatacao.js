document.addEventListener("DOMContentLoaded", function () {
    const cpfInput = document.querySelector(".cpf-mask");
    const telInput = document.querySelector(".telefone-mask");
    const form = document.querySelector("form");

    // Formatação ANTES de enviar o formulário
    if (form) {
        form.addEventListener("submit", function () {
            if (cpfInput) {
                let cpf = cpfInput.value.replace(/\D/g, '');
                // Garante que CPF com menos dígitos não seja cortado
                if (cpf.length >= 11) {
                    cpf = cpf.substring(0, 11);
                    cpfInput.value = cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                }
            }

            if (telInput) {
                let telefone = telInput.value.replace(/\D/g, '');
                if (telefone.length === 11) {
                    telInput.value = telefone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else if (telefone.length === 10) {
                    telInput.value = telefone.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                }
            }

            return true;
        });
    }

    // Máscara dinâmica para CPF (durante digitação)
    if (cpfInput) {
        cpfInput.addEventListener("input", () => {
            let value = cpfInput.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);

            if (value.length <= 3) {
                cpfInput.value = value;
            } else if (value.length <= 6) {
                cpfInput.value = value.replace(/(\d{3})(\d+)/, "$1.$2");
            } else if (value.length <= 9) {
                cpfInput.value = value.replace(/(\d{3})(\d{3})(\d+)/, "$1.$2.$3");
            } else {
                cpfInput.value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, "$1.$2.$3-$4");
            }
        });
    }

    // Máscara dinâmica para Telefone (durante digitação)
    if (telInput) {
        telInput.addEventListener("input", () => {
            let value = telInput.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);

            if (value.length <= 2) {
                telInput.value = value;
            } else if (value.length <= 6) {
                telInput.value = value.replace(/^(\d{2})(\d+)/, "($1) $2");
            } else if (value.length <= 10) {
                telInput.value = value.replace(/^(\d{2})(\d{4})(\d+)/, "($1) $2-$3");
            } else {
                telInput.value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3");
            }
        });
    }
});
