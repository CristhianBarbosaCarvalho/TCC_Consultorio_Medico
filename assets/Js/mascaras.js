document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");

  // Máscara genérica — CPF e Telefone
  function aplicarMascara(input, tipo) {
    input.addEventListener("input", () => {
      let value = input.value.replace(/\D/g, "");

      if (tipo === "cpf") {
        if (value.length > 11) value = value.slice(0, 11);
        if (value.length <= 3) input.value = value;
        else if (value.length <= 6)
          input.value = value.replace(/(\d{3})(\d+)/, "$1.$2");
        else if (value.length <= 9)
          input.value = value.replace(/(\d{3})(\d{3})(\d+)/, "$1.$2.$3");
        else
          input.value = value.replace(
            /(\d{3})(\d{3})(\d{3})(\d{1,2})/,
            "$1.$2.$3-$4"
          );
      }

      if (tipo === "telefone") {
        if (value.length > 11) value = value.slice(0, 11);
        if (value.length <= 2) input.value = value;
        else if (value.length <= 6)
          input.value = value.replace(/^(\d{2})(\d+)/, "($1) $2");
        else if (value.length <= 10)
          input.value = value.replace(/^(\d{2})(\d{4})(\d+)/, "($1) $2-$3");
        else
          input.value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3");
      }
    });
  }

  // Aplica máscaras automáticas aos campos com classes específicas
  document.querySelectorAll(".cpf-mask").forEach((el) => aplicarMascara(el, "cpf"));
  document.querySelectorAll(".telefone-mask").forEach((el) => aplicarMascara(el, "telefone"));

  // Antes de enviar o formulário — remove pontos, traços e parênteses
  if (form) {
    form.addEventListener("submit", () => {
      document.querySelectorAll(".cpf-mask, .telefone-mask").forEach((input) => {
        input.value = input.value.replace(/\D/g, "");
      });
    });
  }
});
