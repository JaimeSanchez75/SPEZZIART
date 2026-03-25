<?php

require_once __DIR__ . '/AdministracionControllers.php';
require_once __DIR__ . '/../../../core/email.php';

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

    public function resetearContrasena() {
        header('Content-Type: application/json; charset=UTF-8');
    $idUsuario = $_POST['id'] ?? null;
    
    if (!$idUsuario) {
        echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
        exit;
    }

    $model = $this->cargarModelo("usuariosModel");
    $usuario = $model->obtenerUsuarioPorId($idUsuario);

    if ($usuario) {
        $token = bin2hex(random_bytes(32)); 
        $model->guardarTokenRecuperacion($idUsuario, $token);

        $enlace = "http://localhost/App/pages/login/resetear/" . $token;
        
        $asunto = "Restablece tu contraseña - SPEZZIART";
        $mensaje = "<h1>Hola {$usuario['Nombre']}</h1>
                    <p>Un administrador ha solicitado el restablecimiento de tu contraseña.</p>
                    <p>Haz clic en el enlace para elegir una nueva clave (vence en 1 hora):</p>
                    <a href='$enlace' style='background:#800020; color:white; padding:10px; text-decoration:none;'>Restablecer ahora</a>";

        require_once __DIR__ . '/../../../core/email.php';
        
        
        if (Email::enviarEmail($usuario['Email'], $usuario['Nombre'], $asunto, $mensaje)) {
            echo json_encode(['status' => 'success', 'message' => 'Email enviado a ' . $usuario['Email']]);
            exit; 
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al enviar el correo']);
            exit; 
        }
    }
}

        

}