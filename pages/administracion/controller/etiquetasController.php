<?php

require_once __DIR__ . '/AdministracionControllers.php';
require_once __DIR__ . '/../../../core/flash.php';

class EtiquetasController extends AdministracionControllers {

    public function index()
    {
        $datos['etiquetas'] = $this->obtenerEtiquetas();

        $this->mostrarAdministracion("etiquetas/etiquetas.php", "Gestión de Etiquetas", $datos);
    }

    private function obtenerEtiquetas()
    {

        try {

            $objEtiqueta = $this->cargarModelo("etiquetasModel");
            $resultado   = $objEtiqueta->obtenerEtiquetas();
            return is_array($resultado) ? $resultado : [];

        } catch (\Throwable $e) {

            error_log('[etiquetas:obtener] ' . $e->getMessage());
            Flash::error('No se pudieron cargar las etiquetas.');
            return [];

        }
    }

    public function crearEtiqueta()
    {
        $nombreEtiqueta = trim((string)($_POST['nombre'] ?? ''));

        if ($nombreEtiqueta === '') {
            Flash::error('Debes indicar un nombre para la etiqueta.');
            $this->redirigir();
            return;
        }

        if (mb_strlen($nombreEtiqueta) < 2 || mb_strlen($nombreEtiqueta) > 50) {
            Flash::error('El nombre de la etiqueta debe tener entre 2 y 50 caracteres.');
            $this->redirigir();
            return;
        }

        try {
            $objEtiqueta = $this->cargarModelo("etiquetasModel");

            if ($objEtiqueta->existeNombre($nombreEtiqueta)) {

                Flash::warning('Ya existe una etiqueta con ese nombre.');
                $this->redirigir();
                return;

            }

            $objEtiqueta->crearEtiqueta($nombreEtiqueta);
            Flash::success('Etiqueta creada correctamente.');
            
        } catch (\Throwable $e) {
            error_log('[etiquetas:crear] ' . $e->getMessage());
            Flash::error('No se pudo crear la etiqueta. Inténtalo de nuevo.');
        }

        $this->redirigir();
    }

    public function editarEtiqueta()
    {
        $idEtiqueta     = isset($_POST['etiqueta_id']) ? (int)$_POST['etiqueta_id'] : 0;
        $nombreEtiqueta = trim((string)($_POST['nombre'] ?? ''));

        if ($idEtiqueta <= 0 || $nombreEtiqueta === '') {
            Flash::error('Faltan datos para editar la etiqueta.');
            $this->redirigir();
            return;
        }

        if (mb_strlen($nombreEtiqueta) < 2 || mb_strlen($nombreEtiqueta) > 50) {
            Flash::error('El nombre de la etiqueta debe tener entre 2 y 50 caracteres.');
            $this->redirigir();
            return;
        }

        try {
            $objEtiqueta = $this->cargarModelo("etiquetasModel");

            if ($objEtiqueta->existeNombre($nombreEtiqueta, $idEtiqueta)) {
                Flash::warning('Ya existe otra etiqueta con ese nombre.');
                $this->redirigir();
                return;
            }

            $objEtiqueta->editarEtiqueta($nombreEtiqueta, $idEtiqueta);
            Flash::success('Etiqueta actualizada correctamente.');

        } catch (\Throwable $e) {

            error_log('[etiquetas:editar] ' . $e->getMessage());
            Flash::error('No se pudo editar la etiqueta. Inténtalo de nuevo.');

        }

        $this->redirigir();
    }

    public function eliminarEtiqueta()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id <= 0) {
            Flash::error('Etiqueta inválida.');
            $this->redirigir();
            return;
        }

        try {

            $objEtiqueta = $this->cargarModelo("etiquetasModel");
            $objEtiqueta->eliminarEtiqueta($id);
            Flash::success('Etiqueta eliminada correctamente.');

        } catch (\Throwable $e) {

            error_log('[etiquetas:eliminar] ' . $e->getMessage());
            
            Flash::error('No se pudo eliminar la etiqueta. Puede estar asociada a recetas.');

        }

        $this->redirigir();
    }

    
    private function redirigir(): void
    {

        header('Location: /pages/administracion/etiquetas');
        exit;

    }
}
