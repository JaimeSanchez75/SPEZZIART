<?php declare(strict_types=1); ?>
<?php

require_once __DIR__ . '/AdministracionControllers.php';
require_once __DIR__ . '/../../../core/email.php';
require_once __DIR__ . '/../../../core/flash.php';

class moderacionController extends AdministracionControllers
{

    public function index()
    {

        try {

            $modelo = $this->cargarModelo("ModeracionModel");

            $datos['reportesRecetas']     = $modelo->getReportesPendientesRecetas();
            $datos['reportesComentarios'] = $modelo->getReportesPendientesComentarios();
            $datos['reportesPerfiles']    = $modelo->getReportesPendientesPerfiles();
            $datos['pendientes']          = $modelo->contarPendientes();

        } catch (\Throwable $e) {

            error_log('[moderacion:index] ' . $e->getMessage());
            Flash::error('No se pudieron cargar los reportes pendientes.');
            $datos = [
                'reportesRecetas'     => [],
                'reportesComentarios' => [],
                'reportesPerfiles'    => [],
                'pendientes'          => 0,
            ];

        }

        $this->mostrarAdministracion(
            "moderacion/moderacion.php",
            "Moderación de Contenido",
            $datos
        );
    }

    
    public function marcarRevisado()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id <= 0) {
            Flash::error('Reporte inválido.');
            $this->redirigir();
            return;
        }

        try {

            $modelo = $this->cargarModelo("ModeracionModel");
            $modelo->marcarRevisado($id);
            Flash::success('Reporte rechazado correctamente.');

        } catch (\Throwable $e) {

            error_log('[moderacion:marcarRevisado] ' . $e->getMessage());
            Flash::error('No se pudo marcar el reporte.');

        }

        $this->redirigir();
    }

    private function calcularNutricionReceta(){
        
    }

    
    public function historial()
    {
        try {

            $modelo = $this->cargarModelo("ModeracionModel");

            $datos['reportesRecetas']     = $modelo->getReportesHistorialRecetas();
            $datos['reportesComentarios'] = $modelo->getReportesHistorialComentarios();
            $datos['reportesPerfiles']    = $modelo->getReportesHistorialPerfiles();

        } catch (\Throwable $e) {

            error_log('[moderacion:historial] ' . $e->getMessage());
            Flash::error('No se pudo cargar el historial de reportes.');
            $datos = [
                'reportesRecetas'     => [],
                'reportesComentarios' => [],
                'reportesPerfiles'    => [],
            ];
        }

        $this->mostrarAdministracion(
            "moderacion/historial.php",
            "Historial de Moderación",
            $datos
        );
    }

    public function aceptarReporte()
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? null)) {

            http_response_code(403);
            Flash::error('Token CSRF inválido.');
            $this->redirigir();
            return;

        }

        $reporteId = isset($_POST['reporte_id']) ? (int)$_POST['reporte_id'] : 0;

        if ($reporteId <= 0) {

            Flash::error('Reporte inválido.');
            $this->redirigir();
            return;

        }

        $recetaId     = isset($_POST['receta_id'])     ? (int)$_POST['receta_id']     : 0;
        $comentarioId = isset($_POST['comentario_id']) ? (int)$_POST['comentario_id'] : 0;
        $usuario      = trim((string)($_POST['usuario_reportado'] ?? ''));
        $tipo         = trim((string)($_POST['tipo']    ?? ''));
        $accion       = trim((string)($_POST['accion']  ?? ''));

        if ($accion === 'eliminar_usuario') {
            $accion = 'deshabilitar_usuario';
        }

        $accionesValidas = ['eliminar_receta', 'eliminar_comentario', 'deshabilitar_usuario'];

        if (!in_array($accion, $accionesValidas, true)) {

            Flash::error('Acción de moderación no válida.');
            $this->redirigir();
            return;
        }

        try {

            $modelo = $this->cargarModelo("ModeracionModel");

            $usuarioAfectado = null;
            $tituloReceta    = '';
            $textoComentario = '';

            switch ($accion) {

                case "eliminar_receta":

                    if ($recetaId <= 0) {

                        Flash::error('No se proporcionó una receta válida.');
                        $this->redirigir();
                        return;

                    }
                    $usuarioAfectado = $modelo->obtenerUsuarioPorReceta($recetaId);
                    $tituloReceta    = $modelo->obtenerTituloReceta($recetaId);
                    $modelo->eliminarReceta($recetaId);
                    break;

                case "eliminar_comentario":

                    if ($comentarioId <= 0) {

                        Flash::error('No se proporcionó un comentario válido.');
                        $this->redirigir();
                        return;

                    }

                    $usuarioAfectado = $modelo->obtenerUsuarioPorComentario($comentarioId);
                    $textoComentario = $modelo->obtenerComentario($comentarioId);
                    $modelo->eliminarComentario($comentarioId);
                    break;

                case "deshabilitar_usuario":

                    if ($tipo === "receta" && $recetaId > 0) {

                        $usuarioAfectado = $modelo->obtenerUsuarioPorReceta($recetaId);

                        if (!$usuarioAfectado && $usuario !== '') {
                            $usuarioAfectado = $modelo->obtenerUsuarioPorUsername($usuario);
                        }

                    } elseif ($tipo === "comentario" && $comentarioId > 0) {

                        $usuarioAfectado = $modelo->obtenerUsuarioPorComentario($comentarioId);

                        if (!$usuarioAfectado && $usuario !== '') {
                            $usuarioAfectado = $modelo->obtenerUsuarioPorUsername($usuario);
                        }

                    } elseif ($tipo === "perfil" && $usuario !== '') {

                        $usuarioAfectado = $modelo->obtenerUsuarioPorUsername($usuario);

                    } else {

                        error_log('[moderacion:deshabilitar] Datos insuficientes - tipo=' . $tipo . ' recetaId=' . $recetaId . ' comentarioId=' . $comentarioId . ' usuario=' . $usuario);
                        Flash::error('Faltan datos para deshabilitar al usuario.');
                        $this->redirigir();
                        return;

                    }

                    if (!$usuarioAfectado || empty($usuarioAfectado['ID_Usuario'])) {

                        error_log('[moderacion:deshabilitar] No se encontró al usuario - tipo=' . $tipo . ' usuario=' . $usuario);
                        Flash::error('No se pudo encontrar al usuario que se quería deshabilitar.');
                        $this->redirigir();
                        return;
                    }

                    $okDeshabilitar = $modelo->deshabilitarUsuarioPorId((int)$usuarioAfectado['ID_Usuario']);

                    if (!$okDeshabilitar) {

                        error_log('[moderacion:deshabilitar] UPDATE Activa=0 no afectó filas - ID_Usuario=' . $usuarioAfectado['ID_Usuario']);
                        Flash::error('No se pudo deshabilitar al usuario.');
                        $this->redirigir();
                        return;
                    }

                    break;
            }

            if ($accion === 'deshabilitar_usuario' && $usuarioAfectado && !empty($usuarioAfectado['ID_Usuario'])) {

                $modelo->rechazarReporte($reporteId);
                $modelo->rechazarReportesPendientesDeUsuario(
                    (int)$usuarioAfectado['ID_Usuario'],
                    $reporteId
                );

            } else {

                $modelo->aprobarReporte($reporteId);
            }

            if ($usuarioAfectado && !empty($usuarioAfectado['Email'])) {
                try {

                    $this->enviarEmailModeracion($accion, $usuarioAfectado, $tituloReceta, $textoComentario);

                } catch (\Throwable $e) {

                    error_log('[moderacion:email] ' . $e->getMessage());

                }
            }

            Flash::success('Reporte gestionado correctamente.');
        } catch (\Throwable $e) {

            error_log('[moderacion:aceptarReporte] accion=' . $accion . ' tipo=' . $tipo
                . ' recetaId=' . $recetaId . ' comentarioId=' . $comentarioId
                . ' usuario=' . $usuario . ' :: ' . $e->getMessage()
                . ' @ ' . $e->getFile() . ':' . $e->getLine());
            Flash::error('No se pudo procesar el reporte. Inténtalo de nuevo.');

        }

        $this->redirigir();
    }

    private function enviarEmailModeracion($accion, $usuario, $tituloReceta = '', $textoComentario = '')
    {
        $nombre = $usuario['Nombre'] ?? $usuario['Username'] ?? 'usuario';
        $email  = $usuario['Email'];

        $asunto  = '';
        $mensaje = '';

        $cabecera = "<!DOCTYPE html>
            <html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>@media only screen and (max-width:600px){.responsive-table{width:100%!important}.inner-padding{padding:30px 20px!important}h1{font-size:24px!important}}</style>
            </head><body style='margin:0;padding:0;background-color:#FFF5F5;font-family:\"Segoe UI\",Helvetica,Arial,sans-serif'>
            <table width='100%' cellpadding='0' cellspacing='0' border='0' bgcolor='#FFF5F5'><tr><td align='center' style='padding:40px 20px'>
            <table class='responsive-table' width='560' cellpadding='0' cellspacing='0' border='0' bgcolor='#FFFFFF' style='width:100%;max-width:560px;background:#FFFFFF;border-radius:24px;box-shadow:0 8px 20px rgba(128,0,32,0.08);border-collapse:separate;overflow:hidden'>
            <tr><td bgcolor='#800020' height='6'></td></tr><tr><td class='inner-padding' style='padding:40px 40px 32px'>
            <table width='100%'><tr><td align='center' style='padding-bottom:12px'><span style='font-size:32px;font-weight:700;color:#800020;letter-spacing:2px'>SPEZZIART</span></td></tr>
            <tr><td align='center'><div style='width:60px;height:3px;background:#800020;margin:12px auto 20px;border-radius:3px'></div></td></tr></table>";

        $pie = "<hr style='border:none;height:1px;background:#FFE4E4;margin:20px 0 16px'>
            <p style='font-size:13px;color:#9CA3AF;text-align:center;margin:12px 0 0'><strong>¿Crees que es un error?</strong> Responde a este correo para revisar tu caso.</p>
            </td></tr><tr><td bgcolor='#FEF9F9' style='padding:20px 40px 28px;border-top:1px solid #FFE4E4'><table width='100%'><tr><td align='center' style='font-size:12px;color:#800020;font-weight:500'>© 2026 · SPEZZIART</td></tr>
            <tr><td align='center' style='padding-top:12px'><p style='font-size:12px;color:#6B7280;margin:0'>Mensaje automático del equipo de moderación.</p></td></tr>
            <tr><td align='center' style='padding-top:18px'><span style='font-size:10px;color:#C4A0A0'>Protegido con seguridad avanzada</span></td></tr></table></td></tr></table>
            <table width='100%' style='max-width:560px;margin-top:24px'><tr><td align='center' style='font-size:11px;color:#C27C7C'>Si recibiste este correo por error, no se realizaron cambios en tu cuenta.</td></tr></table>
            </td></tr></table></body></html>";

        $nombreSeguro = htmlspecialchars((string)$nombre);

        switch ($accion) {

            case "eliminar_receta":

                $asunto  = "Tu receta ha sido eliminada - SPEZZIART";
                $tituloRecetaSeguro = $tituloReceta ? htmlspecialchars($tituloReceta) : '';
                $bloqueReceta = $tituloRecetaSeguro
                    ? "<div style='background:#FFF5F5;border-left:4px solid #800020;border-radius:12px;padding:16px 20px;margin:24px 0 20px'>
                        <p style='font-size:13px;color:#800020;margin:0 0 4px;text-transform:uppercase;letter-spacing:1px'>Receta eliminada</p>
                        <p style='font-size:16px;color:#660019;margin:0;font-weight:600'>\"{$tituloRecetaSeguro}\"</p>
                       </div>"
                    : "";

                $cuerpo = "
                    <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 20px'>Estimado/a <strong style='color:#800020'>{$nombreSeguro}</strong>,</p>
                    <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 16px'>Te informamos que el equipo de moderación de <strong>SPEZZIART</strong> ha eliminado una de tus recetas tras revisar un reporte enviado por la comunidad.</p>
                    {$bloqueReceta}
                    <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 16px'>El contenido infringía nuestras normas de uso. Te recordamos que es importante respetar las directrices de la plataforma para garantizar una experiencia segura para todos.</p>";

                $mensaje = $cabecera . $cuerpo . $pie;
                break;

            case "eliminar_comentario":

                $asunto  = "Tu comentario ha sido eliminado - SPEZZIART";
                $textoSeguro = $textoComentario ? htmlspecialchars($textoComentario) : '';
                $bloqueComentario = $textoSeguro
                    ? "<div style='background:#FFF5F5;border-left:4px solid #800020;border-radius:12px;padding:16px 20px;margin:24px 0 20px'>
                        <p style='font-size:13px;color:#800020;margin:0 0 6px;text-transform:uppercase;letter-spacing:1px'>Comentario eliminado</p>
                        <p style='font-size:14px;color:#660019;margin:0;font-style:italic;line-height:1.5'>\"{$textoSeguro}\"</p>
                       </div>"
                    : "";

                $cuerpo = "
                    <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 20px'>Estimado/a <strong style='color:#800020'>{$nombreSeguro}</strong>,</p>
                    <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 16px'>Te informamos que el equipo de moderación de <strong>SPEZZIART</strong> ha eliminado uno de tus comentarios tras revisar un reporte enviado por la comunidad.</p>
                    {$bloqueComentario}
                    <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 16px'>El contenido infringía nuestras normas de convivencia. Te pedimos que mantengas un tono respetuoso en tus interacciones para garantizar una experiencia segura para todos.</p>";

                $mensaje = $cabecera . $cuerpo . $pie;
                break;

            case "deshabilitar_usuario":

                $asunto  = "Tu cuenta ha sido deshabilitada - SPEZZIART";
                $cuerpo = "
                    <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 20px'>Estimado/a <strong style='color:#800020'>{$nombreSeguro}</strong>,</p>
                    <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 16px'>Te informamos que tu cuenta en <strong>SPEZZIART</strong> ha sido <strong>deshabilitada</strong> por el equipo de moderación tras revisar uno o más reportes enviados por la comunidad.</p>
                    <div style='background:#FFF5F5;border-left:4px solid #800020;border-radius:12px;padding:16px 20px;margin:24px 0 20px'>
                        <p style='font-size:14px;color:#660019;margin:0 0 6px'><strong>Importante:</strong> Mientras la cuenta esté deshabilitada no podrás iniciar sesión.</p>
                        <p style='font-size:13px;color:#800020;margin:0'>Tus contenidos siguen guardados, pero permanecerán ocultos.</p>
                    </div>
                    <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 16px'>Esta decisión se ha tomado porque tu actividad o contenido vulneraba nuestras normas de uso. Si crees que se trata de un error puedes responder a este correo para revisar el caso.</p>";

                $mensaje = $cabecera . $cuerpo . $pie;
                break;

            default:
                return;
        }

        Email::enviarEmail($email, $nombre, $asunto, $mensaje);
    }

    private function redirigir(): void
    {
        header("Location: /pages/administracion/moderacion");
        exit;
    }
}
