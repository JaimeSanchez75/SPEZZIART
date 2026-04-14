<?php
require_once __DIR__ . '/../model/individualModel.php';
require_once __DIR__ . '/../view/individualView.php';

class individualController 
{
    public function index() 
    {
        $user = Auth::user();
        $userId = $user['id'];
        $model = new individualModel();
        $view = new IndividualView();
        
        try {
            $busqueda = $_GET['q'] ?? '';
            if (!empty($busqueda)) {
                $misRecetas = $model->buscarRecetasUsuario($userId, $busqueda);
                $colecciones = $model->buscarColeccionesUsuario($userId, $busqueda);
            } else {
                $misRecetas = $model->getRecetasUsuario($userId);
                $colecciones = $model->getColeccionesUsuario($userId);
            }
            
            // ========== NUEVO: Obtener recetas guardadas (colección por defecto) ==========
            $recetasGuardadas = [];
            $coleccionDefecto = $this->colDefecto($userId); // llamamos al método auxiliar que ya creamos
            if ($coleccionDefecto) {
                $recetasGuardadas = $model->getRecetasDeColeccion($coleccionDefecto['ID_Coleccion']);
            }
            // ============================================================================
            
            $config = [
                'ModoOscuro' => $user['ModoOscuro'] ?? false,
                'ModoFit'    => $user['ModoFit'] ?? false
            ];
            
            // PASAMOS $recetasGuardadas en lugar del array vacío que había antes
            $view->render($misRecetas, $recetasGuardadas, [], $config, $colecciones, $busqueda);
        } catch (Exception $e) {
            die("Error en index: " . $e->getMessage());
        }
    }
    // ---------- CREAR / EDITAR RECETA ----------
    public function crear() 
    {
        csrf_verify();
        $userId = Auth::id();
        $model = new individualModel();
        $receta = null;

        if (!empty($_GET['id'])) 
        {
            $id = (int)$_GET['id'];
            $receta = $model->getRecetaByIdAndUser($id, $userId);
            if (!$receta) {die("Receta no encontrada o no tienes permiso para editarla.");}
        }
        require_once __DIR__ . '/../view/crearRecetaView.php';
    }

    // ---------- GUARDAR RECETA ----------
    public function guardar() 
    {
        csrf_verify();
        $userId = Auth::id();
        try 
        {
            $model = new individualModel();

            $data = 
            [
                'titulo'      => $_POST['titulo'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'tiempo'      => $_POST['tiempo'] ?? 0,
                'porciones'   => $_POST['porciones'] ?? 0,
                'imagen'      => $_POST['imagen'] ?? '',
                'fit'         => isset($_POST['fit']) ? 1 : 0
            ];
            if (!empty($_POST['id'])) 
            {
                // Edición: verificar propiedad antes de actualizar
                $id = (int)$_POST['id'];
                if (!$model->actualizarReceta($id, $userId, $data)) {die("No tienes permiso para editar esta receta.");}
            } 
            else {$model->crearReceta($userId, $data);}

            header("Location: /App/pages/individual");
            exit;
        } 
        catch (Exception $e) {die("Error al guardar receta: " . $e->getMessage());}
    }
    // ---------- VER RECETA ----------
    public function ver() 
    {
        $userId = Auth::id();
        $model = new individualModel();

        if (empty($_GET['id'])) die("Receta no encontrada");

        $receta = $model->getRecetaByIdAndUser((int)$_GET['id'], $userId);
        if (!$receta) {die("Receta no existe o no tienes permiso para verla.");}
        require_once __DIR__ . '/../view/verRecetaView.php';
    }

    // ---------- ELIMINAR RECETA ----------
    public function eliminar() 
    {
        csrf_verify();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['idReceta'])) 
        {
            $userId = Auth::id();
            $model = new individualModel();
            $id = (int)$_POST['idReceta'];

            // Verificar propiedad antes de eliminar
            if (!$model->eliminarReceta($id, $userId)) {die("No tienes permiso para eliminar esta receta.");}
        }
        header("Location: /App/pages/individual");
        exit;
    }
    // ---------- CREAR COLECCIÓN ----------
    public function crearColeccion() 
    {
        csrf_verify();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombreColeccion'])) 
        {
            $model = new individualModel();
            $model->crearColeccion(Auth::id(), $_POST['nombreColeccion'], true);
        }
        header("Location: /App/pages/individual");
        exit;
    }
    // ---------- VER COLECCIÓN ----------
    public function verColeccion() 
    {
        $userId = Auth::id();
        if (empty($_GET['id'])) die("Colección no encontrada");

        $model = new individualModel();
        $coleccionId = (int)$_GET['id'];
        // Verificar que la colección pertenece al usuario
        $coleccion = $model->getColeccionByIdAndUser($coleccionId, $userId);
        if (!$coleccion) {die("Colección no encontrada o no tienes acceso.");}
        $recetas = $model->getRecetasDeColeccion($coleccionId);
        $config = ['ModoOscuro' => Auth::user()['ModoOscuro'] ?? false];
        require_once __DIR__ . '/../view/coleccionView.php';
        $view = new ColeccionView();
        $view->render($coleccion, $recetas, $config);
    }

    // ---------- AGREGAR RECETA A COLECCIÓN ----------
    public function agregarReceta() 
    {
        csrf_verify();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' 
            && !empty($_POST['idReceta']) 
            && !empty($_POST['idColeccion'])) 
        {

            $userId = Auth::id();
            $model = new individualModel();
            $idReceta    = (int)$_POST['idReceta'];
            $idColeccion = (int)$_POST['idColeccion'];

            // Verificar que la colección pertenece al usuario
            if (!$model->getColeccionByIdAndUser($idColeccion, $userId)) {die("No tienes permiso para modificar esta colección.");}
            $model->agregarRecetaAColeccion($idReceta, $idColeccion);
            header("Location: /App/pages/individual/coleccion?id=" . $idColeccion);
            exit;
        }
        die("Error: datos incompletos.");
    }
    // ---------- ELIMINAR COLECCIÓN ----------
    public function eliminarColeccion() 
    {
        csrf_verify();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['idColeccion'])) 
        {
            $userId = Auth::id();
            $model = new individualModel();

            $idColeccion = (int)$_POST['idColeccion'];
            // Validar que la colección pertenece al usuario
            if (!$model->getColeccionByIdAndUser($idColeccion, $userId)) {die("No tienes permisos para eliminar esta colección.");}
            if (!$model->eliminarColeccion($idColeccion)) {die("Error al eliminar la colección.");}
        }
        header("Location: /App/pages/individual");
        exit;
    }
    // ---------- ELIMINAR RECETA DE COLECCIÓN ----------
    public function eliminarRecetaDeColeccion() 
    {   
        csrf_verify();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' 
            && !empty($_POST['idReceta']) 
            && !empty($_POST['idColeccion'])) 
        {

            $userId = Auth::id();
            $model = new individualModel();
            $idReceta    = (int)$_POST['idReceta'];
            $idColeccion = (int)$_POST['idColeccion'];
            // Verificar que la colección pertenece al usuario
            if (!$model->getColeccionByIdAndUser($idColeccion, $userId)) {die("No tienes permisos.");}
            $model->eliminarRecetaDeColeccion($idReceta, $idColeccion);
            header("Location: /App/pages/individual/coleccion?id=" . $idColeccion);
            exit;
        }
        die("Datos incompletos.");
    }
    public function misCols()
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
        $model = new individualModel();
        $cols = $model->getColeccionesUsuario(Auth::id());
        echo json_encode($cols);
    }

    // Obtiene o crea la colección por defecto "Guardadas"
    private function colDefecto($userId)
    {
        $model = new individualModel();
        $cols = $model->getColeccionesUsuario($userId);
        foreach ($cols as $c) {
            if ($c['Nombre'] === 'Guardadas') return $c;
        }
        // Crear si no existe
        $model->crearColeccion($userId, 'Guardadas', false);
        // Volver a buscar
        $cols = $model->getColeccionesUsuario($userId);
        foreach ($cols as $c) {
            if ($c['Nombre'] === 'Guardadas') return $c;
        }
        return null;
    }

    // Endpoint para guardar desde el feed
    public function guardarFeed()
    {
        csrf_verify();
        header('Content-Type: application/json');
        
        if (!Auth::check()) {
            echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $idReceta = (int)($input['id_receta'] ?? 0);
        $cols = $input['colecciones'] ?? []; // array de IDs
        $guardarDefecto = (bool)($input['guardarPorDefecto'] ?? false);
        
        if (!$idReceta) {
            echo json_encode(['ok' => false, 'msg' => 'ID de receta no válido']);
            exit;
        }
        
        $userId = Auth::id();
        $model = new individualModel();
        
        // Si no seleccionó ninguna colección pero quiere guardar por defecto
        if (empty($cols) && $guardarDefecto) {
            $def = $this->colDefecto($userId);
            if ($def) $cols = [$def['ID_Coleccion']];
            else {
                echo json_encode(['ok' => false, 'msg' => 'No se pudo crear la colección por defecto']);
                exit;
            }
        }
        
        if (empty($cols)) {
            echo json_encode(['ok' => false, 'msg' => 'No se seleccionó ninguna colección']);
            exit;
        }
        
        $res = $model->guardarEnCols($idReceta, $userId, $cols);
        if ($res['ok']) {
            echo json_encode(['ok' => true, 'msg' => 'Receta guardada correctamente']);
        } else {
            echo json_encode(['ok' => false, 'msg' => $res['msg']]);
        }
    }
}