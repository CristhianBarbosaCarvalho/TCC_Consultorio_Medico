document.addEventListener("DOMContentLoaded", () => {
  const especialidadeSelect = document.getElementById("especialidade");
  const medicoSelect = document.getElementById("medico");
  const dataInput = document.getElementById("data_agendamento");
  const horariosDiv = document.getElementById("horarios");
  const horaHidden = document.getElementById("hora_agendada");

  especialidadeSelect.addEventListener("change", () => {
    const esp = especialidadeSelect.value;
    if (!esp) return;

    fetch(`buscar.php?tabela=medico&especialidade=${encodeURIComponent(esp)}`)
      .then(res => res.json())
      .then(data => {
        medicoSelect.innerHTML = `<option value="">Selecione o médico</option>`;
        data.forEach(med => {
          medicoSelect.innerHTML += `<option value="${med.id_medico}">${med.nome}</option>`;
        });
        horariosDiv.innerHTML = "";
        horaHidden.value = "";
      });
  });

  medicoSelect.addEventListener("change", carregarHorarios);
  dataInput.addEventListener("change", carregarHorarios);

  function carregarHorarios() {
    const idMedico = medicoSelect.value;
    const data = dataInput.value;
    if (!idMedico || !data) return;

    fetch(`buscar.php?tabela=horarios_disponiveis&id_medico=${idMedico}&dia_consulta=${data}`)
      .then(res => res.json())
      .then(horarios => {
        horariosDiv.innerHTML = "";
        horaHidden.value = "";

        if (!horarios || horarios.length === 0) {
          horariosDiv.innerHTML = "<p>Nenhum horário disponível.</p>";
          return;
        }

        horarios.forEach(h => {
          const btn = document.createElement("button");
          btn.type = "button";
          btn.className = "btn-horario";
          btn.dataset.hora = h.hora;
          btn.textContent = h.hora;
          btn.addEventListener("click", () => {
            document.querySelectorAll(".btn-horario").forEach(b => b.classList.remove("ativo"));
            btn.classList.add("ativo");
            horaHidden.value = h.hora;
          });
          horariosDiv.appendChild(btn);
        });
      });
  }
});
