<?php
require_once __DIR__ . '/../model/individualModel.php';
require_once __DIR__ . '/../view/individualView.php';

class individualController 
{
    private function separarDescripcionYPasos(?string $contenido): array
    {
        $contenido = trim((string)$contenido);
        if ($contenido === '') {
            return ['descripcion' => '', 'pasos' => []];
        }

        $separador = "\n\nPASOS:\n";
        $posicionSeparador = strrpos($contenido, $separador);
        if ($posicionSeparador === false) {
            return ['descripcion' => $contenido, 'pasos' => []];
        }

        $descripcion = trim(substr($contenido, 0, $posicionSeparador));
        $bloquePasos = trim(substr($contenido, $posicionSeparador + strlen($separador)));
        $pasos = [];

        foreach (preg_split('/\R+/', $bloquePasos) as $linea) {
            $linea = trim($linea);
            if ($linea === '') {
                continue;
            }

            $pasos[] = preg_replace('/^\d+\.\s*/', '', $linea);
        }

        return ['descripcion' => $descripcion, 'pasos' => $pasos];
    }

    private function unirDescripcionYPasos(string $descripcion, array $pasos): string
    {
        $descripcion = trim($descripcion);
        $pasosLimpios = [];

        foreach ($pasos as $paso) {
            $paso = trim((string)$paso);
            if ($paso !== '') {
                $pasosLimpios[] = $paso;
            }
        }

        if (empty($pasosLimpios)) {
            return $descripcion;
        }

        $bloquePasos = [];
        foreach ($pasosLimpios as $indice => $paso) {
            $bloquePasos[] = ($indice + 1) . '. ' . $paso;
        }

        $contenido = implode("\n", $bloquePasos);
        if ($descripcion === '') {
            return "PASOS:\n" . $contenido;
        }

        return $descripcion . "\n\nPASOS:\n" . $contenido;
    }

    public function index() 
    {
        $user = Auth::user();
        $userId = $user['id'];
        $model = new individualModel(); 
        $view = new IndividualView();
        try 
        {
            $busqueda = $_GET['q'] ?? '';
            if (!empty($busqueda)) 
            {
                $misRecetas = $model->buscarRecetasUsuario($userId, $busqueda);
                $colecciones = $model->buscarColeccionesUsuario($userId, $busqueda);
            } 
            else 
            {
                $misRecetas = $model->getRecetasUsuario($userId);
                $colecciones = $model->getColeccionesUsuario($userId);
            }
            $guardadas = []; // pendiente de implementar recetas guardadas
            $config = [
                'ModoOscuro' => $user['ModoOscuro'] ?? false,
                'ModoFit'    => $user['ModoFit'] ?? false
            ];
            $view->render($misRecetas, $guardadas, [], $config, $colecciones, $busqueda);
        } 
        catch (Exception $e) {die("Error en index: " . $e->getMessage());}
    }
    // ---------- CREAR / EDITAR RECETA ----------
    public function crear() 
    {
        $userId = Auth::id();
        $model = new individualModel();
        $receta = null;

        if (!empty($_GET['id'])) 
        {
            $id = (int)$_GET['id'];
            $receta = $model->getRecetaByIdAndUser($id, $userId);
            if (!$receta) {die("Receta no encontrada o no tienes permiso para editarla.");}
        }

        $contenidoReceta = $this->separarDescripcionYPasos($receta['Descripcion'] ?? '');
        $descripcionFormulario = $contenidoReceta['descripcion'];
        $pasosFormulario = !empty($contenidoReceta['pasos']) ? $contenidoReceta['pasos'] : [''];
        require_once __DIR__ . '/../view/crearRecetaView.php';
    }

    // ---------- GUARDAR RECETA ----------
    public function guardar() 
    {
        $userId = Auth::id();
        try 
        {
            $model = new individualModel();

            $data = 
            [
                'titulo'      => $_POST['titulo'] ?? '',
                'descripcion' => $this->unirDescripcionYPasos(
                    $_POST['descripcion'] ?? '',
                    $_POST['pasos'] ?? []
                ),
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
        $contenidoReceta = $this->separarDescripcionYPasos($receta['Descripcion'] ?? '');
        $receta['DescripcionVisible'] = $contenidoReceta['descripcion'];
        $receta['Pasos'] = $contenidoReceta['pasos'];
        require_once __DIR__ . '/../view/verRecetaView.php';
    }

    // ---------- ELIMINAR RECETA ----------
    public function eliminar() 
    {
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
}
