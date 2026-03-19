<?php
require_once __DIR__ . '/../model/individualModel.php';
require_once __DIR__ . '/../view/individualView.php';

class individualController {

    public function index() {
        if (session_status() == PHP_SESSION_NONE) session_start();

        // Simulación de usuario logueado para pruebas
        if (!isset($_SESSION['id'])) $_SESSION['id'] = 1;

        $model = new individualModel();
        $view = new IndividualView();

        // ---------- BUSCADOR ----------
        $busqueda = $_GET['buscar'] ?? '';

        if ($busqueda) {
            $misRecetas = $model->buscarRecetasUsuario($_SESSION['id'], $busqueda);
            $colecciones = $model->buscarColeccionesUsuario($_SESSION['id'], $busqueda);
        } else {
            $misRecetas = $model->getRecetasUsuario($_SESSION['id']);
            $colecciones = $model->getColeccionesUsuario($_SESSION['id']);
        }

        $guardadas = []; // implementar más adelante
        $etiquetas = []; // implementar más adelante
        $config = [
            'ModoOscuro' => $_SESSION['ModoOscuro'] ?? false,
            'ModoFit' => $_SESSION['ModoFit'] ?? false
        ];

        // ---------- CREAR COLECCIÓN DESDE FORM ----------
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombreColeccion'])) {
            $model->crearColeccion($_SESSION['id'], $_POST['nombreColeccion'], true);
            header("Location: /App/pages/individual");
            exit;
        }

        // ---------- AGREGAR RECETA A COLECCIÓN ----------
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['idColeccion']) && !empty($_POST['idReceta'])) {
            $model->agregarRecetaAColeccion($_POST['idReceta'], $_POST['idColeccion']);
            header("Location: /App/pages/individual");
            exit;
        }

        // Renderizamos la vista con todo
        $view->render($misRecetas, $guardadas, $etiquetas, $config, $colecciones);
    }

    // Vista para crear receta
    public function crear() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['id'])) $_SESSION['id'] = 1;

        require_once __DIR__ . '/../view/crearRecetaView.php';
    }

    // Guardar receta nueva
    public function guardar() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['id'])) $_SESSION['id'] = 1;

        $model = new individualModel();

        $data = [
            'titulo' => $_POST['titulo'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'tiempo' => $_POST['tiempo'] ?? 0,
            'porciones' => $_POST['porciones'] ?? 0,
            'imagen' => $_POST['imagen'] ?? '',
            'fit' => isset($_POST['fit']) ? 1 : 0
        ];

        $model->crearReceta($_SESSION['id'], $data);

        header("Location: /App/pages/individual");
        exit;
    }
}