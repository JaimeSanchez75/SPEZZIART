<?php declare(strict_types=1); ?>
<?php

require_once __DIR__ . '/AdministracionControllers.php';

class PrincipalController extends AdministracionControllers
{
    public function ajax()
    {
        $datos = $this->recetasPorDia();
        header('Content-Type: application/json');
        echo json_encode($datos);
        exit;
    }
    public function index()
    {
        $modelo = $this->cargarModelo("principalModel");

        $usuariosNuevosHoy   = (int)$modelo->contarUsuariosNuevosHoy();
        $usuariosNuevosAyer  = (int)$modelo->contarUsuariosNuevosAyer();
        $recetasHoy          = (int)$modelo->contarRecetasHoy();
        $recetasAyer         = (int)$modelo->contarRecetasAyer();
        $pendientesRevision  = (int)$modelo->contarRecetasPendientes();
        $reportesNuevosHoy   = (int)$modelo->contarReportesNuevosHoy();
        $reportesNuevosAyer  = (int)$modelo->contarReportesNuevosAyer();
        $comentariosHoy      = (int)$modelo->contarComentariosHoy();
        $comentariosAyer     = (int)$modelo->contarComentariosAyer();

        $totalUsuarios       = (int)$modelo->contarTotalUsuarios();
        $totalRecetas        = (int)$modelo->contarTotalRecetas();
        $totalIngredientes   = (int)$modelo->contarTotalIngredientes();
        $totalEtiquetas      = (int)$modelo->contarTotalEtiquetas();

        $datos = [
            'usuariosNuevos'      => $usuariosNuevosHoy,
            'usuariosCambio'      => $this->calcularCambio($usuariosNuevosHoy, $usuariosNuevosAyer),
            'recetasHoy'          => $recetasHoy,
            'recetasCambio'       => $this->calcularCambio($recetasHoy, $recetasAyer),
            'pendientesRevision'  => $pendientesRevision,
            'pendientesCambio'    => $this->calcularCambio($reportesNuevosHoy, $reportesNuevosAyer),
            'comentariosHoy'      => $comentariosHoy,
            'comentariosCambio'   => $this->calcularCambio($comentariosHoy, $comentariosAyer),
            'totalUsuarios'       => $totalUsuarios,
            'totalRecetas'        => $totalRecetas,
            'totalIngredientes'   => $totalIngredientes,
            'totalEtiquetas'      => $totalEtiquetas,
            'ultimasAprobaciones' => $modelo->ultimasAprobaciones(),
        ];

        $this->mostrarAdministracion("principal/principal.php", "Panel de Administración", $datos);
    }

   
    private function recetasPorDia()
    {
        $objReceta = $this->cargarModelo("principalModel");

        return $objReceta->contarRecetasPorDia();
    }

    
    private function calcularCambio(int $hoy, int $ayer)
    {
        $diferencia = $hoy - $ayer;

        if ($diferencia > 0) return '+' . $diferencia;
        if ($diferencia < 0) return (string)$diferencia; 
        return '0';
    }
}
