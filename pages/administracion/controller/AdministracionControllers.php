<?php 
class AdministracionControllers
{
    public function cargarModelo($nombreModelo){

        require_once __DIR__ . '/../model/' . $nombreModelo . '.php'; 
        
        return new $nombreModelo();

    }

    public function mostrarAdministracion($__view, $__titulo, $__parametros = array()){

        foreach ($__parametros as $clave => $valor) {

            $$clave = $valor;

        }

        $userLogueado = Auth::user();

        if ($userLogueado && isset($userLogueado['id'])) {
            
            require_once __DIR__ . '/../../perfil/model/PerfilModel.php';

            $perfilModel = new PerfilModel();

            $datosActualizados = $perfilModel->getDatosUsuario((int)$userLogueado['id']);

            if ($datosActualizados && !empty($datosActualizados['FotoPerfil'])) {

                $_SESSION['user']['avatar'] = $datosActualizados['FotoPerfil'];

                $userLogueado['avatar'] = $datosActualizados['FotoPerfil'];
            }

        }

        if (!$userLogueado) {die("Error: Usuario no autenticado en el panel de administración.");}

        require_once __DIR__ . '/../view/dashboard.php';
    }

   
}
?>