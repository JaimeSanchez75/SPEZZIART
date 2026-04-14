<?php

require_once __DIR__ . '/AdministracionControllers.php';

class moderacionController extends AdministracionControllers
{

    public function index()
    {
        $modelo = $this->cargarModelo("ModeracionModel");

        $datos['reportes'] = $modelo->getReportesPendientes();
        $datos['pendientes'] = $modelo->contarPendientes();

        $this->mostrarAdministracion(
            "moderacion/moderacion.php",
            "ModeraciÃ³n de Contenido",
            $datos
        );
    }


    /* ============================
       APROBAR REPORTE
    ============================ */

    public function aprobar()
    {
        if (isset($_GET['id'])) {

            $modelo = $this->cargarModelo("ModeracionModel");
            $modelo->marcarRevisado($_GET['id']);
        }

        header("Location: /App/pages/administracion/moderacion");
        exit;
    }


    /* ============================
       RECHAZAR REPORTE
    ============================ */

    public function rechazar()
    {
        if (isset($_GET['id'])) {

            $modelo = $this->cargarModelo("ModeracionModel");
            $modelo->rechazarReporte($_GET['id']);
        }

        header("Location: /App/pages/administracion/moderacion");
        exit;
    }

public function marcarRevisado()
{
    if (!isset($_GET['id'])) {
        header("Location: /moderacion");
        exit;
    }

    $modelo = $this->cargarModelo("ModeracionModel");

    $modelo->marcarRevisado($_GET['id']);

    header("Location: /App/pages/administracion/moderacion");
    exit;
}
public function aceptarReporte()
{
    if(!isset($_POST['reporte_id'])) {
        header("Location: /moderacion");
        exit;
    }

    $modelo = $this->cargarModelo("ModeracionModel");

    $reporte_id = $_POST['reporte_id'];
    $receta_id = $_POST['receta_id'];
    $accion = $_POST['accion'];
    $mensaje = $_POST['mensaje'];

    if($accion == "eliminar_receta")
    {
        $modelo->eliminarReceta($receta_id);
    }

    if($accion == "eliminar_usuario")
    {
        $modelo->eliminarUsuarioPorReceta($receta_id);
    }

    $modelo->marcarRevisado($reporte_id);

    header("Location: /App/pages/administracion/moderacion");
    exit;
}
}