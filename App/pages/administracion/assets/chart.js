document.addEventListener("DOMContentLoaded", function () {
    const canvasEl = document.getElementById("graficaRecetasSemanal");
    if (!canvasEl) return;

    const canvas = canvasEl.getContext("2d");

    fetch("/App/pages/administracion/principal/recetasPorDia?action=recetasPorDia")
        .then(response => response.json())
        .then(data => {
            const dias = data.map(item => item.dia);
            const totales = data.map(item => item.total);

            new Chart(canvas, {
                type: "line",
                data: {
                    labels: dias,
                    datasets: [{
                        label: "Recetas por día",
                        data: totales,
                        borderColor: "rgb(75, 192, 192)",
                        backgroundColor: "rgba(75, 192, 192, 0.2)",
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: { title: { display: true, text: "Día" } },
                        y: { title: { display: true, text: "Total de Recetas" }, beginAtZero: true }
                    }
                }
            });
        })
        
});