<?php

require_once __DIR__ . '/AdministracionControllers.php';

class UsuariosController extends AdministracionControllers
{

    public function index()
    {
        $datos['usuarios'] = $this->obtenerUsuarios();
        $this->mostrarAdministracion("usuarios&admin/usuarios.php", "Gestión de Usuarios", $datos);
    }

    private function obtenerUsuarios()
    {
        $objUsuario = $this->cargarModelo("usuariosModel");
        return $objUsuario->obtenerUsuarios();
    }

}