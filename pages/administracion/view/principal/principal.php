<?php
declare(strict_types=1); ?>
<section class="container-fluid py-4">
    <h2 class="subtitulo letraRomana fw-bold  m-0">Panel de Control</h2>
    <p class="texto text-secondary">Bienvenido de nuevo, <?php echo htmlspecialchars($_SESSION['user']['nombre']); ?>.</p>
    
    <div class="row g-3  mt-2 p-0 justify-content-start">
        <div class="col-12 col-sm-6 col-lg-3 p-2">
            <div class="d-flex flex-column p-4 sombra border rounded rounded-4 gap-2 bg-white efectoEscala h-100">
                <div class="mb-1">
                    <span class="bg-azulClaro p-2  pt-3 rounded rounded-3"><i class="bi bi-person-plus iconos texto-azul "></i></span>
                </div>
                <div>
                    <div class="texto text-secondary fw-semibold">Nuevos Usuarios Hoy</div>
                    <div class="d-flex align-items-end justify-content-between gap-2 mt-1">
                        <div class="fw-bold tituloPequeno"><?php echo (int)$usuariosNuevos; ?></div>
                        <div class="textoPequeno fw-semibold <?php echo $usuariosCambio >= 0 ? 'texto-verde' : 'texto-rojo'; ?>"><?php echo htmlspecialchars($usuariosCambio); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3  p-2">
            <div class=" d-flex flex-column p-4 sombra border rounded rounded-4 gap-2 bg-white efectoEscala h-100">
                <div class="mb-1">
                    <span class="bg-verdeClaro p-2 pt-3 rounded rounded-3"><i class="bi bi-egg-fried iconos texto-verde"></i></span>
                </div>
                <div>
                    <div class="texto text-secondary fw-semibold">Recetas Publicadas Hoy</div>
                    <div class="d-flex align-items-end justify-content-between gap-2 mt-1">
                        <div class="fw-bold tituloPequeno"><?php echo (int)$recetasHoy; ?></div>
                        <div class="textoPequeno fw-semibold <?php echo $recetasCambio >= 0 ? 'texto-verde' : 'texto-rojo'; ?>"><?php echo htmlspecialchars($recetasCambio); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 p-2">
            <div class=" d-flex flex-column p-4 sombra border rounded rounded-4 gap-2 bg-white efectoEscala h-100">
                <div class="mb-1">
                    <span class="bg-moradoClaro p-2 pt-3 rounded rounded-3"><i class="bi bi-clock iconos texto-morado"></i></span>
                </div>
                <div>
                    <div class="texto text-secondary fw-semibold">Pendientes Revisión</div>
                    <div class="d-flex align-items-end justify-content-between gap-2 mt-1">
                        <div class="fw-bold tituloPequeno"><?php echo (int)$pendientesRevision; ?></div>
                        <div class="textoPequeno fw-semibold <?php echo $pendientesCambio >= 0 ? 'texto-verde' : 'texto-rojo'; ?>"><?php echo htmlspecialchars($pendientesCambio); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 p-2 ">
            <div class=" d-flex flex-column p-4 sombra border rounded rounded-4 gap-2 bg-white efectoEscala h-100">
                <div class="mb-1">
                    <span class="bg-naranjaClaro p-2 pt-3 rounded rounded-3"><i class="bi bi-chat-dots iconos texto-naranja"></i></span>
                </div>
                <div>
                    <div class="texto text-secondary fw-semibold">Comentarios Hoy</div>
                    <div class="d-flex align-items-end justify-content-between gap-2 mt-1">
                        <div class="fw-bold tituloPequeno"><?php echo (int)$comentariosHoy; ?></div>
                        <div class="textoPequeno fw-semibold <?php echo $comentariosCambio >= 0 ? 'texto-verde' : 'texto-rojo'; ?>"><?php echo htmlspecialchars($comentariosCambio); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mt-3  ">
        
        <div class="col-12 col-xxl-7 pe-xxl-2">
            <div class=" sombra border rounded rounded-4 gap-2 bg-white p-4 h-100 efectoEscala">
                <h6 class="fw-bold mb-3 textoMediano">Actividad Semanal</h6>
                <div class="grafica"><canvas id="graficaRecetasSemanal"></canvas></div>
                <div class="row text-center border-top pt-3 mt-3 g-0">
                    <div class="col-4">
                        <div class="fw-bold tituloPequeno" id="graficaTotalSemana">0</div>
                        <div class="textoPequeno text-secondary">Total semana</div>
                    </div>
                    <div class="col-4 border-start border-end">
                        <div class="fw-bold tituloPequeno texto-rojo" id="graficaPromedioDiario">0</div>
                        <div class="textoPequeno text-secondary">Promedio diario</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold tituloPequeno" id="graficaMejorDia">—</div>
                        <div class="textoPequeno text-secondary">Mejor día</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-xxl-5 ps-xxl-2">
            <div class="sombra border rounded rounded-4 gap-2 bg-white p-4 h-100 efectoEscala d-flex flex-column overflow-hidden">
                <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
                    <h6 class="fw-bold textoMediano m-0">Últimos Reportes Revisados</h6>
                    <a href="/pages/administracion/moderacion/historial" class="texto-rojo texto fw-medium text-decoration-none d-inline-flex align-items-center gap-1">
                        Ver todas <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="flex-grow-1 overflow-y-auto overflow-x-hidden">
                <?php if (empty($ultimasAprobaciones))
                      { ?>
                        <div class="d-flex flex-column align-items-center justify-content-center text-center py-4 px-3">
                            <span class="bg-rojoClaro rounded rounded-3 d-inline-flex align-items-center justify-content-center mb-3 cajaW65">
                                <i class="bi bi-check2-all texto-rojo fs-2"></i>
                            </span>
                            <h5 class="fw-bold mb-2">Aún sin reportes revisados</h5>
                            <p class="text-secondary texto mb-4">Todavía no se ha tomado ninguna decisión sobre los reportes. Los últimos revisados aparecerán aquí.</p>
                            <a href="/pages/administracion/moderacion" class="rounded rounded-3 text-decoration-none fw-medium bg-rojo text-white px-4 py-1 texto d-inline-flex align-items-center gap-2">
                                <i class="bi bi-shield-check"></i>
                                Ir a moderación
                            </a>
                        </div>
                <?php }
                      else
                      {
                        $tiempoRelativo = function (?string $fecha): string
                        {
                            if (!$fecha) return '';
                            try
                            {
                                $ts    = strtotime($fecha);
                                $ahora = time();
                                $diff  = $ahora - $ts;
                                if ($diff < 60) return 'ahora';
                                if ($diff < 3600) return 'hace ' . floor($diff / 60) . ' min';
                                if ($diff < 86400) return 'hace ' . floor($diff / 3600) . ' h';
                                if ($diff < 172800) return 'ayer';
                                if ($diff < 604800) return 'hace ' . floor($diff / 86400) . ' días';
                                $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
                                return date('j', $ts) . ' ' . $meses[(int)date('n', $ts) - 1];
                            }
                            catch (\Throwable $e) {return '';}
                        };
                        foreach ($ultimasAprobaciones as $aprobacion)
                        {
                            $tipo       = $aprobacion['tipo']       ?? '';
                            $estado     = $aprobacion['estado']     ?? '';
                            $autor      = $aprobacion['autor']      ?? '';
                            $contenido  = $aprobacion['contenido']  ?? '';
                            $reportador = $aprobacion['reportador'] ?? '';
                            $motivo     = $aprobacion['Motivo']     ?? '';
                            $cuando     = $tiempoRelativo($aprobacion['Fecha'] ?? null);
                            switch ($tipo)
                            {
                                case 'receta':
                                    $titulo      = 'Receta «' . $contenido . '»';
                                    $subtitulo   = 'De @' . $autor . ' · Reportada por: ' . $motivo;
                                    $badgeClase  = 'bg-verdeClaro texto-verde';
                                    $badgeTexto  = 'RECETA';
                                    $iconoClase  = 'bi-egg-fried';
                                    $iconoBg     = 'bg-verdeClaro';
                                    $iconoColor  = 'texto-verde';
                                break;
                                case 'comentario':
                                    $titulo      = 'Comentario de @' . $autor;
                                    $subtitulo   = '«' . $contenido . '» · ' . $motivo;
                                    $badgeClase  = 'bg-azulClaro texto-azul';
                                    $badgeTexto  = 'COMENTARIO';
                                    $iconoClase  = 'bi-chat-dots';
                                    $iconoBg     = 'bg-azulClaro';
                                    $iconoColor  = 'texto-azul';
                                break;
                                case 'perfil':
                                    $titulo      = 'Perfil de @' . $autor;
                                    $subtitulo   = $contenido . ' · Reportado por: ' . $motivo;
                                    $badgeClase  = 'bg-moradoClaro texto-morado';
                                    $badgeTexto  = 'PERFIL';
                                    $iconoClase  = 'bi-person-circle';
                                    $iconoBg     = 'bg-moradoClaro';
                                    $iconoColor  = 'texto-morado';
                                break;
                            }
                            switch ($estado)
                            {
                                case 'Aprobado':
                                    $estadoTexto  = 'Aprobada';
                                    $estadoIcono  = 'bi-check-circle-fill';
                                    $estadoColor  = 'texto-verde';
                                break;
                                case 'Rechazado':
                                    $estadoTexto  = 'Rechazada';
                                    $estadoIcono  = 'bi-x-circle-fill';
                                    $estadoColor  = 'texto-rojo';
                                break;
                                default:
                                    $estadoTexto  = 'Revisada';
                                    $estadoIcono  = 'bi-check2-all';
                                    $estadoColor  = 'text-secondary';
                                break;
                            }?>
                            <div class="d-flex align-items-center justify-content-between py-2 aprobacionesReceta px-2 rounded rounded-4 mb-2">
                                <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                                    <span class="<?php echo $iconoBg; ?> rounded rounded-3 d-inline-flex align-items-center justify-content-center me-2 flex-shrink-0 cajaW40" >
                                        <i class="bi <?php echo $iconoClase; ?> <?php echo $iconoColor; ?> iconos" ></i>
                                    </span>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="fw-semibold small text-truncate"><?php echo htmlspecialchars($titulo); ?></div>
                                        <div class="text-muted small text-truncate"><?php echo htmlspecialchars($subtitulo); ?></div>
                                        <div class="text-muted textoPequeno d-flex align-items-center gap-1">
                                            <i class="bi <?php echo $estadoIcono; ?> <?php echo $estadoColor; ?>"></i>
                                            <span class="<?php echo $estadoColor; ?> fw-semibold"><?php echo htmlspecialchars($estadoTexto); ?></span>
                                            <?php if ($cuando) { ?>
                                                <span>· <?php echo htmlspecialchars($cuando); ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <span class="badge <?php echo $badgeClase; ?> ms-2 flex-shrink-0"><?php echo $badgeTexto; ?></span>
                            </div>
                    <?php }
                    } ?>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-3 mt-2 p-0 justify-content-start">
        <div class="col-12 col-sm-6 col-lg-3 p-2 ">
            <div class="d-flex align-items-center p-3 sombra border rounded rounded-4 gap-3 bg-white efectoEscala h-100">
                <span class="bg-light p-2 pt-3 rounded rounded-3">
                    <i class="bi bi-people iconos text-secondary"></i>
                </span>
                <div>
                    <div class="texto text-secondary fw-semibold">Total Usuarios</div>
                    <div class="fw-bold tituloPequeno"><?php echo (int)$totalUsuarios; ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 p-2 ">
            <div class="d-flex align-items-center p-3 sombra border rounded rounded-4 gap-3 bg-white efectoEscala h-100">
                <span class="bg-verdeClaro p-2 pt-3 rounded rounded-3">
                    <i class="bi bi-egg-fried iconos texto-verde"></i>
                </span>
                <div>
                    <div class="texto text-secondary fw-semibold">Total Recetas</div>
                    <div class="fw-bold tituloPequeno"><?php echo (int)$totalRecetas; ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 p-2 ">
            <div class="d-flex align-items-center p-3 sombra border rounded rounded-4 gap-3 bg-white efectoEscala h-100">
                <span class="bg-moradoClaro p-2 pt-3 rounded rounded-3">
                    <i class="bi bi-geo-alt iconos texto-morado"></i>
                </span>
                <div>
                    <div class="texto text-secondary fw-semibold">Ingredientes</div>
                    <div class="fw-bold tituloPequeno"><?php echo (int)$totalIngredientes; ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 p-2 ">
            <div class="d-flex align-items-center p-3 sombra border rounded rounded-4 gap-3 bg-white efectoEscala h-100">
                <span class="bg-naranjaClaro p-2 pt-3 rounded rounded-3">
                    <i class="bi bi-tags iconos texto-naranja"></i>
                </span>
                <div>
                    <div class="texto text-secondary fw-semibold">Etiquetas</div>
                    <div class="fw-bold tituloPequeno"><?php echo (int)$totalEtiquetas; ?></div>
                </div>
            </div>
        </div>
    </div>
</section>