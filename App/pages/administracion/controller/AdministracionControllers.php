<?php 

class AdministracionControllers
{
    
    public function cargarModelo($nombreModelo)
    {
        require_once __DIR__ . '/../Model/' . $nombreModelo . '.php';
        
        return new $nombreModelo();
    }



    public function mostrarAdministracion($__view, $__titulo, $__parametros = array())
    {
        foreach ($__parametros as $clave => $valor) {
            $$clave = $valor;
        }

        require_once __DIR__ . '/../view/dashboard.php';
    }
}
?>