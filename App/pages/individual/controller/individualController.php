<?php
require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../model/individualModel.php';
require_once __DIR__ . '/../view/individualView.php';
require_once __DIR__ . '/../view/crearRecetaView.php';

class individualController {

    public function index() {

        // 🔐 USAR AUTH DEL ROUTER (NO requireAuth)
        $user = Auth::user();
        $userId = $user['id'];

        $model = new individualModel();
        $view = new IndividualView();

        // ---------- BUSCADOR ----------
        $busqueda = $_GET['buscar'] ?? '';

        if ($busqueda) {
            $misRecetas = $model->buscarRecetasUsuario($userId, $busqueda);
            $colecciones = $model->buscarColeccionesUsuario($userId, $busqueda);
        } else {
            $misRecetas = $model->getRecetasUsuario($userId);
            $colecciones = $model->getColeccionesUsuario($userId);
        }

        $guardadas = [];
        $etiquetas = [];

        // ⚙️ CONFIG DESDE JWT (ANTES SESSION)
        $config = [
            'ModoOscuro' => $user['ModoOscuro'] ?? false,
            'ModoFit' => $user['ModoFit'] ?? false
        ];

        // ---------- CREAR COLECCIÓN ----------
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombreColeccion'])) {

            $model->crearColeccion($userId, $_POST['nombreColeccion'], true);

            header("Location: /App/pages/individual");
            exit;
        }

        // ---------- AGREGAR RECETA A COLECCIÓN ----------
        if ($_SERVER['REQUEST_METHOD'] === 'POST' 
            && !empty($_POST['idColeccion']) 
            && !empty($_POST['idReceta'])) {

            $model->agregarRecetaAColeccion(
                $userId,
                $_POST['idReceta'],
                $_POST['idColeccion']
            );

            header("Location: /App/pages/individual");
            exit;
        }

        $view->render($misRecetas, $guardadas, $etiquetas, $config, $colecciones, $busqueda);
    }

    public function crear() {

        $user = Auth::user();

        $config = [
            'ModoOscuro' => $user['ModoOscuro'] ?? false,
            'ModoFit' => $user['ModoFit'] ?? false
        ];

        require_once __DIR__ . '/../view/CrearRecetaView.php';

        $view = new CrearRecetaView();
        $view->render($config);
    }

    public function guardar() {

        $user = Auth::user();
        $userId = $user['id'];

        $model = new individualModel();

        $data = [
            'titulo' => $_POST['titulo'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'tiempo' => $_POST['tiempo'] ?? 0,
            'porciones' => $_POST['porciones'] ?? 0,
            'imagen' => $_POST['imagen'] ?? '',
            'fit' => isset($_POST['fit']) ? 1 : 0
        ];

        $model->crearReceta($userId, $data);

        header("Location: /App/pages/individual");
        exit;
    }
}