"use strict";
document.addEventListener("DOMContentLoaded", function () {
    const canvasEl = document.getElementById("graficaRecetasSemanal");
    if (!canvasEl) return;

    const canvas = canvasEl.getContext("2d");

    const diasMap = {
        "Monday":    "Lun",
        "Tuesday":   "Mar",
        "Wednesday": "Mié",
        "Thursday":  "Jue",
        "Friday":    "Vie",
        "Saturday":  "Sáb",
        "Sunday":    "Dom"
    };

    
    const letraDiaMap = {
        "Monday":    "L",
        "Tuesday":   "M",
        "Wednesday": "X",
        "Thursday":  "J",
        "Friday":    "V",
        "Saturday":  "S",
        "Sunday":    "D"
    };

    const rootStyles = getComputedStyle(document.documentElement);
    const colorVino       = rootStyles.getPropertyValue('--vino').trim()       || '#5d0a1a';
    const colorVinoLight  = rootStyles.getPropertyValue('--vino-light').trim() || '#a52a3a';
    const colorTextoSec   = rootStyles.getPropertyValue('--text-secondary').trim() || '#6c757d';
    const colorVinoClaro = colorVinoLight + '40';

    fetch("/pages/administracion/principal/recetasPorDia?action=recetasPorDia")
        .then(response => response.json())
        .then(data => {
            const labels = data.map(item => letraDiaMap[item.nombreDia] || (item.dia || '').slice(0, 1));

            const totales = data.map(item => Number(item.total) || 0);

            const maxValor = Math.max(...totales, 0);
            const indiceMax = totales.indexOf(maxValor);

            const backgroundColors = totales.map((v, i) =>  
                (v === maxValor && v > 0 && i === indiceMax) ? colorVino : colorVinoClaro
            );

            new Chart(canvas, {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Recetas por día",
                        data: totales,
                        backgroundColor: backgroundColors,
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.55,
                        categoryPercentage: 0.9
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#212529',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 8,
                            displayColors: false,
                            callbacks: {
                                title: function (items) {
                                    const idx = items[0].dataIndex;
                                    const nombre = data[idx] ? data[idx].nombreDia : '';
                                    return diasMap[nombre] || items[0].label;
                                },
                                label: function (ctx) {
                                    const v = ctx.parsed.y;
                                    return v + (v === 1 ? ' receta' : ' recetas');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid:   { display: false, drawBorder: false },
                            border: { display: false },
                            ticks:  { color: colorTextoSec, font: { size: 12 } }
                        },
                        y: {
                            display: false,
                            beginAtZero: true,
                            ticks: { precision: 0, stepSize: 1 }
                        }
                    }
                }
            });

            const total = totales.reduce((a, b) => a + b, 0);
            const promedio = total > 0 ? (total / totales.length) : 0;

            const graficaTotal    = document.getElementById('graficaTotalSemana');
            const graficaPromedio = document.getElementById('graficaPromedioDiario');
            const graficaMejor    = document.getElementById('graficaMejorDia');

            if (graficaTotal)    graficaTotal.textContent = total;

            if (graficaPromedio) graficaPromedio.textContent = promedio.toFixed(1);

            if (graficaMejor) {

                if (maxValor > 0 && data[indiceMax]) {

                    graficaMejor.textContent = diasMap[data[indiceMax].nombreDia] || '—';

                } else {

                    graficaMejor.textContent = '—';
                    
                }
            }
        });
});
