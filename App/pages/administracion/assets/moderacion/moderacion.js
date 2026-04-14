document.querySelectorAll(".abrirModal").forEach(btn => {

    btn.addEventListener("click", function () {

        document.getElementById("reporte_id").value = this.dataset.id;
        document.getElementById("receta_id").value = this.dataset.receta;

        let modal = new bootstrap.Modal(
            document.getElementById("modalModeracion")
        );

        modal.show();

    });

});