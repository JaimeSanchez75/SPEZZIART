<?php

require_once __DIR__ . '/AdministracionControllers.php';
require_once __DIR__ . '/../../../core/flash.php';

class IngredientesController extends AdministracionControllers
{

    public function index()
    {

        $datos['ingredientesBase'] = $this->obtenerIngredientesBase();
        $datos['ingredientesUsu']  = $this->obtenerIngredientesUsu();

        $this->mostrarAdministracion("ingredientes/ingredientes.php", "Gestión de Ingredientes", $datos);
    }

    private function obtenerIngredientesBase()
    {

        try {

            $obj = $this->cargarModelo("ingredientesModel");
            $r   = $obj->obtenerIngredientesBase();
            return is_array($r) ? $r : [];
        } catch (\Throwable $e) {

            error_log('[ingredientes:obtenerBase] ' . $e->getMessage());
            Flash::error('No se pudieron cargar los ingredientes base.');
            return [];
        }
    }

    private function obtenerIngredientesUsu()
    {
        try {

            $obj = $this->cargarModelo("ingredientesModel");

            $r   = $obj->obtenerIngredientesUsu();
            return is_array($r) ? $r : [];
        } catch (\Throwable $e) {

            error_log('[ingredientes:obtenerUsu] ' . $e->getMessage());
            Flash::error('No se pudieron cargar los ingredientes de usuarios.');
            return [];
        }
    }

    private function normalizarDatos(array $datosPost): array
    {

        $errores = [];
        $nombre  = trim((string)($datosPost['nombre'] ?? ''));
        $unidad=trim((string)($datosPost['unidad'] ?? ''));

        if ($nombre === '') {
            $errores[] = 'El nombre del ingrediente es obligatorio.';
        } elseif (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 100) {
            $errores[] = 'El nombre del ingrediente debe tener entre 2 y 100 caracteres.';
        }

        $unidadesPermitidas = ['g', 'ml'];
        if ($unidad === '') {
            $errores[] = 'La unidad del ingrediente es obligatoria.';
        } elseif (!in_array(mb_strtolower($unidad), $unidadesPermitidas, true)) {
            $errores[] = 'La unidad "' . htmlspecialchars($unidad) . '" no es válida. Solo se permiten: g, ml.';
        } else {
            $unidad = mb_strtolower($unidad);
        }


        $datos = [
            'nombre'        => $nombre,
            'unidad'=> $unidad,
            'grasas'        => is_numeric($datosPost['grasas'] ?? null)        ? (float)$datosPost['grasas']        : 0,
            'calorias'      => is_numeric($datosPost['calorias'] ?? null)      ? (float)$datosPost['calorias']      : 0,
            'proteina'      => is_numeric($datosPost['proteina'] ?? null)      ? (float)$datosPost['proteina']      : 0,
            'carbohidratos' => is_numeric($datosPost['carbohidratos'] ?? null) ? (float)$datosPost['carbohidratos'] : 0,
        ];

        $maximos = ['grasas' => 999, 'calorias' => 9999, 'proteina' => 999, 'carbohidratos' => 999];

        foreach ($maximos as $clave => $max) {

            if ($datos[$clave] < 0) {
                $datos[$clave] = 0;
            } elseif ($datos[$clave] > $max) {
                $datos[$clave] = $max;
            }
        }

        return [$datos, $errores];
    }

    public function crearIngrediente()
    {

        $datosPost = $_POST['datos'] ?? [];
        $esAJAX = false;

        if (empty($datosPost)) {

            $datosPost = json_decode(file_get_contents("php://input"), true) ?: [];
            $esAJAX = true;
        }

        [$datos, $errores] = $this->normalizarDatos(is_array($datosPost) ? $datosPost : []);

        if (!empty($errores)) {

            if ($esAJAX) {
                echo json_encode([
                    'ok' => false,
                    'mensaje' => implode(' ', $errores)
                ]);
                exit;
            }

            Flash::error(implode(' ', $errores));
            $this->redirigir();
            return;
        }

        try {

            $obj = $this->cargarModelo("ingredientesModel");

            if ($obj->existeIngrediente($datos['nombre'])) {

                if ($esAJAX) {

                    echo json_encode([
                        'ok' => false,
                        'mensaje' => 'Ya existe un ingrediente con ese nombre.'
                    ]);

                    exit;
                }

                Flash::error('Ya existe un ingrediente con ese nombre.');
                $this->redirigir();
                return;
            }

            $result = $obj->crearIngrediente($datos);
            Flash::success('Ingrediente creado correctamente.');

        } catch (\Throwable $e) {

            error_log('[ingredientes:crear] ' . $e->getMessage());
            Flash::error('No se pudo crear el ingrediente. Inténtalo de nuevo.');
        }

        if ($esAJAX) {
            echo json_encode([
                'ok' => true,
                'mensaje' => 'Ingrediente creado correctamente.'
            ]);
            exit;
        }

        $this->redirigir();
    }

    public function editarIngrediente()
    {

        header('Content-Type: application/json; charset=UTF-8');

        $datosPost = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datosPost)) {
            $datosPost = $_POST;
        }

        $id = isset($datosPost['id']) ? (int)$datosPost['id'] : 0;

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ingrediente inválido.']);
            return;
        }

        // Obtener la unidad actual del ingrediente para no perderla al editar
        // (el formulario de tabla no incluye campo unidad)
        $obj            = $this->cargarModelo("ingredientesModel");
        $ingredienteActual = $obj->obtenerIngredientePorId($id);

        if (!$ingredienteActual) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Ingrediente no encontrado.']);
            return;
        }

        $datosNormalizados = [
            'nombre'        => $datosPost['nomb'] ?? $datosPost['nombre'] ?? null,
            'unidad'        => $datosPost['unidad'] ?? $ingredienteActual['Unidad_Base'],
            'grasas'        => $datosPost['gr']   ?? $datosPost['grasas'] ?? 0,
            'calorias'      => $datosPost['cal']  ?? $datosPost['calorias'] ?? 0,
            'proteina'      => $datosPost['prot'] ?? $datosPost['proteina'] ?? 0,
            'carbohidratos' => $datosPost['ch']   ?? $datosPost['carbohidratos'] ?? 0,
        ];

        [$datos, $errores] = $this->normalizarDatos($datosNormalizados);

        if (!empty($errores)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
            return;
        }

        try {

            $obj->editarIngrediente($datos, $id);
            echo json_encode(['success' => true, 'message' => 'Ingrediente actualizado.']);
            exit;
        } catch (\Throwable $e) {

            error_log('[ingredientes:editar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudo editar el ingrediente.']);
            exit;
        }
    }

    
    public function verificarIngrediente()
    {

        header('Content-Type: application/json; charset=UTF-8');

        $datosPost = json_decode(file_get_contents("php://input"), true);

        if (!is_array($datosPost)) {
            $datosPost = $_POST;
        }

        $confirmar = !empty($datosPost['confirmarSobrescritura']);

        // Inyectar la unidad del ingrediente original si no viene en el payload
        if (empty($datosPost['unidad']) && !empty($datosPost['id'])) {
            $objTemp      = $this->cargarModelo("ingredientesModel");
            $ingrediente  = $objTemp->obtenerIngredientePorId((int)$datosPost['id']);
            if ($ingrediente) {
                $datosPost['unidad'] = $ingrediente['Unidad_Base'];
            }
        }

        [$datos, $errores] = $this->normalizarDatos($datosPost);

        if (!empty($errores)) {
            $msg = implode(' ', $errores);
            Flash::error($msg);
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $msg]);
            exit;
        }

        try {

            $obj = $this->cargarModelo("ingredientesModel");

            $idExistente = $obj->existeIngrediente($datos['nombre']);

            if ($idExistente) {

                if (!$confirmar) {
                    echo json_encode([
                        'success'  => false,
                        'repetido' => true,
                        'idBase'   => $idExistente,
                        'message'  => 'Ya existe un ingrediente base con ese nombre.'
                    ]);
                    exit;
                }

                $obj->editarIngrediente($datos, $idExistente);

                Flash::success('Se sobrescribieron los datos del ingrediente base "' . $datos['nombre'] . '".');

                echo json_encode([
                    'success'     => true,
                    'sobrescrito' => true,
                    'idBase'      => $idExistente,
                    'message'     => 'Se sobrescribieron los datos del ingrediente base existente.'
                ]);
                exit;
            }

            $obj->crearIngrediente($datos);

            Flash::success('Ingrediente "' . $datos['nombre'] . '" añadido a la base correctamente.');

            echo json_encode([
                'success'     => true,
                'sobrescrito' => false,
                'message'     => 'Ingrediente añadido a la base correctamente.'
            ]);
            exit;

        } catch (\Throwable $e) {

            error_log('[ingredientes:verificar] ' . $e->getMessage());
            Flash::error('No se pudo verificar el ingrediente. Inténtalo de nuevo.');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudo verificar el ingrediente.']);
            exit;
        }
    }

    private function redirigir(): void
    {

        header('Location: /pages/administracion/ingredientes');
        exit;
    }

    public function importar()
    {

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {

            Flash::error('Error al subir el archivo. Inténtalo de nuevo.');
            $this->redirigir();
            return;
        }

        $archivo = $_FILES['archivo']['tmp_name'];
        $fichero = fopen($archivo, 'r');

        if ($fichero === false) {

            Flash::error('No se pudo abrir el archivo. Inténtalo de nuevo.');
            $this->redirigir();
            return;
        }

        $cabecera = fgets($fichero);
        $separador = substr_count($cabecera, ';') >= substr_count($cabecera, ',') ? ';' : ',';
        rewind($fichero);
        fgetcsv($fichero, 0, $separador);

        $obj = $this->cargarModelo("ingredientesModel");
        $error = false;

        while (($linea = fgetcsv($fichero, 0, $separador)) !== false) {

            if (count($linea) === 1 && trim($linea[0]) === '') continue;
            if (count($linea) < 6) continue;

            $linea = array_map(function ($campo) {
                $campo = str_replace("\xFF", '', $campo);
                $encoding = mb_detect_encoding($campo, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);
                $campo = mb_convert_encoding($campo, 'UTF-8', $encoding ?: 'Windows-1252');
                return trim($campo);
            }, $linea);

            $toFloat = fn($v) => (float)str_replace(',', '.', $v);

            // CSV: Ingrediente;Unidad;Calorias;Proteinas;Carbohidratos;Grasas
            $datosBrutos = [
                'nombre'        => $linea[0],
                'unidad'        => $linea[1],
                'calorias'      => $toFloat($linea[2]),
                'proteina'      => $toFloat($linea[3]),
                'carbohidratos' => $toFloat($linea[4]),
                'grasas'        => $toFloat($linea[5]),
            ];

            [$datos, $erroresLinea] = $this->normalizarDatos($datosBrutos);

            if (!empty($erroresLinea)) {
                Flash::warning('Se omitió la línea "' . htmlspecialchars((string)$linea[0]) . '": ' . implode(' ', $erroresLinea));
                $error = true;
                continue;
            }

            try {

                $id = $obj->existeIngrediente($datos['nombre']);

                if (!$id) {

                    $obj->crearIngrediente($datos);
                } else {

                    try {

                        $obj->editarIngrediente($datos, $id);
                    } catch (\Throwable $e) {

                        error_log('[ingredientes:importar-editar] ' . $e->getMessage());
                        Flash::error('Error al actualizar "' . $datos['nombre'] . '": ' . $e->getMessage());
                        $error = true;
                    }
                }
            } catch (\Throwable $e) {

                error_log('[ingredientes:importar] ' . $e->getMessage());
                Flash::error('Error al importar algunos ingredientes: ' . $e->getMessage());
                $error = true;
            }
        }

        fclose($fichero);

        if (!$error) {

            Flash::success('Ingredientes importados correctamente.');
        }

        $this->redirigir();
    }
}
