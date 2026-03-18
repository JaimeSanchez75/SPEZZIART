<?php

require_once __DIR__ . '/AdministracionControllers.php';

class UsuariosController extends AdministracionControllers
{

    public function index()
    {
        $datos['roles'] = $this->obtenerRoles();
        $datos['usuarios'] = $this->obtenerUsuarios();
        $this->mostrarAdministracion("usuarios&admin/usuarios.php", "Gestión de Usuarios", $datos);
    }



    private function obtenerUsuarios()
    {
        $objUsuario = $this->cargarModelo("usuariosModel");
        return is_array($objUsuario->obtenerUsuarios()) ? $objUsuario->obtenerUsuarios(): [];
    }

    private function obtenerRoles()
    {
        $objUsuario = $this->cargarModelo("usuariosModel");
        return is_array($objUsuario->obtenerRoles()) ? $objUsuario->obtenerRoles(): [];
    }


    private function obtenerTodosDatosUsuario()
    {

        $objUsuario = $this->cargarModelo("usuariosModel");
        return is_array($objUsuario->obtenerTodosDatoUsuario()) ? $objUsuario->obtenerTodosLosUsuario(): [];
    }

    function crearUsuario(){
        $datos = $_POST['datosUsuario'] ?? null;

        if (!$datos) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos de usuario no proporcionados']);
            return;
        }
        
        $objUsuario = $this->cargarModelo("usuariosModel");
        $objUsuario->crearUsuario($datos);

        header('Location: /App/pages/administracion/usuarios?mensaje=true');
    }

    function eliminarUsuario($idUsuario){
        $objUsuario = $this->cargarModelo("usuariosModel");
        $objUsuario->usuarioEliminar($idUsuario);

        header('Location: /App/pages/administracion/usuarios?mensaje=eliminado');
    }

        

}