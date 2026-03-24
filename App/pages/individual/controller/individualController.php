<?php
require_once __DIR__ . '/../model/individualModel.php';
require_once __DIR__ . '/../view/individualView.php';

class individualController {

    private function initSession() {
        if(session_status() === PHP_SESSION_NONE) session_start();
        if(!isset($_SESSION['id'])) $_SESSION['id'] = 1;
    }

    // ---------- LISTADO PRINCIPAL ----------
    public function index() {
        $this->initSession();
        try {
            $model = new individualModel();
            $view = new IndividualView();

            $busqueda = $_GET['q'] ?? '';

            if (!empty($busqueda)) {
                $misRecetas = $model->buscarRecetasUsuario($_SESSION['id'], $busqueda);
                $colecciones = $model->buscarColeccionesUsuario($_SESSION['id'], $busqueda);
            } else {
                $misRecetas = $model->getRecetasUsuario($_SESSION['id']);
                $colecciones = $model->getColeccionesUsuario($_SESSION['id']);
            }

            $guardadas = [];
            $etiquetas = [];
            $config = [
                'ModoOscuro' => $_SESSION['ModoOscuro'] ?? false,
                'ModoFit' => $_SESSION['ModoFit'] ?? false
            ];

            $view->render($misRecetas, $guardadas, $etiquetas, $config, $colecciones, $busqueda);

        } catch (Exception $e) {
            die("Error en index: " . $e->getMessage());
        }
    }

    // ---------- CREAR / EDITAR RECETA ----------
    public function crear() {
        $this->initSession();
        $model = new individualModel();
        $receta = null;

        if (!empty($_GET['id'])) {
            $receta = $model->getRecetaById((int)$_GET['id']);
        }

        require_once __DIR__ . '/../view/crearRecetaView.php';
    }

    // ---------- GUARDAR RECETA ----------
    public function guardar() {
        $this->initSession();
        try {
            $model = new individualModel();

            $data = [
                'titulo' => $_POST['titulo'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'tiempo' => $_POST['tiempo'] ?? 0,
                'porciones' => $_POST['porciones'] ?? 0,
                'imagen' => $_POST['imagen'] ?? '',
                'fit' => isset($_POST['fit']) ? 1 : 0
            ];

            if(!empty($_POST['id'])) {
                $model->actualizarReceta((int)$_POST['id'], $data);
            } else {
                $model->crearReceta($_SESSION['id'], $data);
            }

            header("Location: /App/pages/individual");
            exit;

        } catch (Exception $e) {
            die("Error al guardar receta: " . $e->getMessage());
        }
    }

    // ---------- VER RECETA ----------
    public function ver() {
        $this->initSession();
        $model = new individualModel();

        if(empty($_GET['id'])) die("Receta no encontrada");

        $receta = $model->getRecetaById((int)$_GET['id']);
        if(!$receta) die("Receta no existe");

        require_once __DIR__ . '/../view/verRecetaView.php';
    }

    // ---------- ELIMINAR RECETA ----------
    public function eliminar() {
        $this->initSession();
        if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['idReceta'])) {
            $model = new individualModel();
            $model->eliminarReceta((int)$_POST['idReceta']);
        }
        header("Location: /App/pages/individual");
        exit;
    }

    // ---------- CREAR COLECCIÓN ----------
    public function crearColeccion() {
        $this->initSession();
        if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombreColeccion'])) {
            $model = new individualModel();
            $model->crearColeccion($_SESSION['id'], $_POST['nombreColeccion'], true);
        }
        header("Location: /App/pages/individual");
        exit;
    }

    // ---------- VER COLECCIÓN ----------
    public function verColeccion() {
        $this->initSession();

        if(empty($_GET['id'])) die("Colección no encontrada");

        $model = new individualModel();
        $coleccionId = (int)$_GET['id'];

        $recetas = $model->getRecetasDeColeccion($coleccionId);
        $colecciones = $model->getColeccionesUsuario($_SESSION['id']);
        $coleccion = null;

        foreach($colecciones as $col) {
            if($col['ID_Coleccion'] == $coleccionId) {
                $coleccion = $col;
                break;
            }
        }

        if(!$coleccion) die("Colección no encontrada");

        require_once __DIR__ . '/../view/coleccionView.php';
        $view = new ColeccionView();
        $config = ['ModoOscuro' => $_SESSION['ModoOscuro'] ?? false];
        $view->render($coleccion, $recetas, $config);
    }

    // ---------- AGREGAR RECETA A COLECCIÓN ----------
    public function agregarReceta() {
        $this->initSession();
        if($_SERVER['REQUEST_METHOD'] === 'POST' 
            && !empty($_POST['idReceta']) 
            && !empty($_POST['idColeccion'])) {

            $model = new individualModel();
            $model->agregarRecetaAColeccion(
                (int)$_POST['idReceta'],
                (int)$_POST['idColeccion']
            );

            header("Location: /App/pages/individual/coleccion?id=" . (int)$_POST['idColeccion']);
            exit;
        }
        die("Error: datos incompletos.");
    }

    // ---------- ELIMINAR COLECCIÓN ----------
    public function eliminarColeccion() {
        $this->initSession();
        $model = new individualModel();

        if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['idColeccion'])) {
            $idColeccion = (int)$_POST['idColeccion'];

            // Validar que la colección pertenece al usuario
            $colecciones = $model->getColeccionesUsuario($_SESSION['id']);
            $esPropia = false;
            foreach($colecciones as $col) {
                if($col['ID_Coleccion'] === $idColeccion) {
                    $esPropia = true;
                    break;
                }
            }

            if($esPropia) {
                // Eliminar coleccion usando transacción segura
                $resultado = $model->eliminarColeccion($idColeccion);

                if(!$resultado) {
                    die("Error al eliminar la colección.");
                }
            } else {
                die("No tienes permisos para eliminar esta colección.");
            }
        }

        // Redirigir siempre al listado principal
        header("Location: /App/pages/individual");
        exit;
    }

    // ---------- ELIMINAR RECETA DE COLECCIÓN ----------
    public function eliminarRecetaDeColeccion() {
        $this->initSession();

        if($_SERVER['REQUEST_METHOD'] === 'POST' 
            && !empty($_POST['idReceta']) 
            && !empty($_POST['idColeccion'])) {

            $model = new individualModel();

            $idReceta = (int)$_POST['idReceta'];
            $idColeccion = (int)$_POST['idColeccion'];

            // Validar que la colección pertenece al usuario
            $colecciones = $model->getColeccionesUsuario($_SESSION['id']);
            $esPropia = false;

            foreach($colecciones as $col) {
                if($col['ID_Coleccion'] === $idColeccion) {
                    $esPropia = true;
                    break;
                }
            }

            if($esPropia) {
                $model->eliminarRecetaDeColeccion($idReceta, $idColeccion);
            } else {
                die("No tienes permisos.");
            }

            // Volver a la colección
            header("Location: /App/pages/individual/coleccion?id=" . $idColeccion);
            exit;
        }

        die("Datos incompletos.");
    }
}