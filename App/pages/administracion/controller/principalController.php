<?php

require_once __DIR__ . '/AdministracionControllers.php';

class PrincipalController extends AdministracionControllers
{

    public function ajax(){
        
        $datos = $this->recetasPorDia();
        header('Content-Type: application/json');
        echo json_encode($datos);
        exit;
        
    }

    public function index()
    {
        
        // recogemos los datos necesarios para mostrar en el dashboard
        $datos['usuariosTotales'] = $this->usuariosTotales();
        $datos['recetasTotalesSem'] = $this->recetasTotalesSem();
        $datos['pendientesRevision'] = $this->pendientesRevision();
        $datos['ultimasRecetasAprobadas'] = $this->ultimasRecetasAprobadas();

        $this->mostrarAdministracion("principal/principal.php", "Dashboard", $datos);

    }



    // usuariosTotales
    private function usuariosTotales()
    {

        $objUsuario = $this->cargarModelo("principalModel");
        return $objUsuario->contarUsuarios();

    }

    // recetasTotales esta semana
    private function recetasTotalesSem()
    {

        $objReceta = $this->cargarModelo("principalModel");
        return $objReceta->contarRecetasSemana();

    }
    
    // recetas por dia
    private function recetasPorDia(){
        $objReceta = $this->cargarModelo("principalModel");
        return $objReceta->contarRecetasPorDia();
    }

    // pendientes de revisión
    private function pendientesRevision()
    {

        $objReceta = $this->cargarModelo("principalModel");
        return $objReceta->contarRecetasPendientes();

    }

    // ultimas recetas aprobadas
    private function ultimasRecetasAprobadas()
    {

        $objReceta = $this->cargarModelo("principalModel");
        return $objReceta->ultimasRecetasAprobadas();

    }

}