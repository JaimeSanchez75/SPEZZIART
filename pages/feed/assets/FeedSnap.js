/* ============================================================
   FeedSnap.js - Spezziart
   Snap manual para rueda de raton, con una tarjeta por gesto.
   Respeta el scroll interno del contenido y pide mas recetas al
   llegar al final.
   ============================================================ */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const contenedor = document.getElementById('feed-container');
        if (!contenedor) return;

        let gestoConsumido = false;
        let timeoutFinGesto = null;
        let deltaAcumulado = 0;
        const PAUSA_FIN_GESTO = 90;
        const UMBRAL_GESTO = 8;
        const UMBRAL_SCROLL_INTERNO = 24;

        function scrollInternoActivo(evento) {
            const candidatos = [
                '.p-4.d-flex.flex-column.h-100',
                '.comments-body',
                '.descripcion-wrapper'
            ];

            for (const selector of candidatos) {
                const elemento = evento.target.closest(selector);
                if (!elemento) continue;

                const tieneScroll = elemento.scrollHeight - elemento.clientHeight > UMBRAL_SCROLL_INTERNO;
                if (!tieneScroll) continue;

                const arriba = elemento.scrollTop <= 0;
                const abajo = elemento.scrollTop + elemento.clientHeight >= elemento.scrollHeight - 1;

                if (evento.deltaY < 0 && !arriba) return true;
                if (evento.deltaY > 0 && !abajo) return true;
            }

            return false;
        }

        function indiceTarjetaActual(tarjetas) {
            const topContenedor = contenedor.getBoundingClientRect().top;
            let mejorIndice = 0;
            let menorDistancia = Infinity;

            tarjetas.forEach(function (tarjeta, i) {
                const distancia = Math.abs(tarjeta.getBoundingClientRect().top - topContenedor);
                if (distancia < menorDistancia) {
                    menorDistancia = distancia;
                    mejorIndice = i;
                }
            });

            return mejorIndice;
        }

        function saltarA(indice, tarjetas, behavior = 'auto') {
            if (indice < 0 || indice >= tarjetas.length) return;
            tarjetas[indice].scrollIntoView({ behavior, block: 'start' });
        }

        function normalizarDelta(evento) {
            if (evento.deltaMode === WheelEvent.DOM_DELTA_LINE) return evento.deltaY * 16;
            if (evento.deltaMode === WheelEvent.DOM_DELTA_PAGE) return evento.deltaY * contenedor.clientHeight;
            return evento.deltaY;
        }

        function reiniciarGestoCuandoPareLaRueda() {
            clearTimeout(timeoutFinGesto);
            timeoutFinGesto = setTimeout(function () {
                gestoConsumido = false;
                deltaAcumulado = 0;
            }, PAUSA_FIN_GESTO);
        }

        function esCampoEditable(elemento) {
            return elemento.closest('input, textarea, select, [contenteditable="true"]');
        }

        function moverUnaTarjeta(direccion, behavior = 'smooth') {
            const tarjetas = contenedor.querySelectorAll('.tarjeta-receta');
            if (tarjetas.length === 0) return;

            const actual = indiceTarjetaActual(tarjetas);
            const objetivo = Math.max(0, Math.min(tarjetas.length - 1, actual + direccion));

            if (direccion > 0 && actual === tarjetas.length - 1 && window.FeedApp?.loadMoreRecipes) {
                const totalAntes = tarjetas.length;

                Promise.resolve(window.FeedApp.loadMoreRecipes()).then(function () {
                    const tarjetasActualizadas = contenedor.querySelectorAll('.tarjeta-receta');
                    if (tarjetasActualizadas.length > totalAntes) {
                        saltarA(totalAntes, tarjetasActualizadas, behavior);
                    } else if (window.FeedApp?.revealEndOfFeed) {
                        window.FeedApp.revealEndOfFeed();
                    }
                });
                return;
            }

            saltarA(objetivo, tarjetas, behavior);
        }

        contenedor.addEventListener('wheel', function (evento) {
            if (scrollInternoActivo(evento)) return;

            evento.preventDefault();
            reiniciarGestoCuandoPareLaRueda();

            if (gestoConsumido) return;

            deltaAcumulado += normalizarDelta(evento);
            if (Math.abs(deltaAcumulado) < UMBRAL_GESTO) return;

            gestoConsumido = true;

            const direccion = deltaAcumulado > 0 ? 1 : -1;
            moverUnaTarjeta(direccion, 'smooth');
        }, { passive: false });

        document.addEventListener('keydown', function (evento) {
            if (esCampoEditable(evento.target)) return;
            if (document.querySelector('.modal.show, .comments-overlay.active')) return;

            if (evento.key === 'ArrowDown' || evento.key === 'PageDown') {
                evento.preventDefault();
                moverUnaTarjeta(1, 'smooth');
            } else if (evento.key === 'ArrowUp' || evento.key === 'PageUp') {
                evento.preventDefault();
                moverUnaTarjeta(-1, 'smooth');
            }
        });

        contenedor.setAttribute('tabindex', '0');
    });
})();
