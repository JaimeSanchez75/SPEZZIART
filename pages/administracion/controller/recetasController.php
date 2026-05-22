<?php

require_once __DIR__ . '/AdministracionControllers.php';
require_once __DIR__ . '/../../../core/flash.php';

class RecetasController extends AdministracionControllers
{

    private function obtenerImagenesReceta(?string $imagenes): array
    {
        if ($imagenes === null || trim($imagenes) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $imagenes))));
    }

    private function serializarImagenesReceta(array $imagenes): ?string
    {
        $imagenes = array_filter(array_map('trim', $imagenes));

        if (empty($imagenes)) {
            return null;
        }

        return implode(',', $imagenes);
    }

    private function normalizarImagenesExistentesSeleccionadas(array $imagenesActuales): array
    {
        $imagenesSeleccionadas = $_POST['imagenes_existentes'] ?? [];
        if (!is_array($imagenesSeleccionadas)) {
            $imagenesSeleccionadas = [];
        }

        $imagenesValidas = [];

        foreach ($imagenesSeleccionadas as $imagen) {

            $imagen = trim((string)$imagen);

            if ($imagen === '' || !in_array($imagen, $imagenesActuales, true) || in_array($imagen, $imagenesValidas, true)) {
                continue;
            }

            $imagenesValidas[] = $imagen;
        }

        return $imagenesValidas;
    }

    private function normalizarArchivosSubidos(array $archivos): array
    {

        if (empty($archivos) || !isset($archivos['name'])) {
            return [];
        }

        if (!is_array($archivos['name'])) {
            return [$archivos];
        }

        $archivosNormalizados = [];

        foreach ($archivos['name'] as $indice => $valor) {

            $archivosNormalizados[] = [

                'name' => $archivos['name'][$indice] ?? '',
                'type' => $archivos['type'][$indice] ?? '',
                'tmp_name' => $archivos['tmp_name'][$indice] ?? '',
                'error' => $archivos['error'][$indice] ?? UPLOAD_ERR_NO_FILE,
                'size' => $archivos['size'][$indice] ?? 0

            ];
        }

        return $archivosNormalizados;
    }

    private function procesarArchivoImagen(array $archivo): ?string
    {
        if (empty($archivo) || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error upload: ' . $archivo['error']);
        }

        $tipoArchivo = mime_content_type($archivo['tmp_name']);

        switch ($tipoArchivo) {
            case 'image/jpeg':
                $crearImagen = fn($path) => imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $crearImagen = fn($path) => imagecreatefrompng($path);
                break;
            case 'image/webp':
                $crearImagen = fn($path) => imagecreatefromwebp($path);
                break;
            default:
                $crearImagen = null;
        };

        if (!$crearImagen) {
            throw new RuntimeException('Formato de imagen no permitido.');
        }

        $imagen = $crearImagen($archivo['tmp_name']);

        if (!$imagen) {
            throw new RuntimeException('No se pudo procesar la imagen.');
        }

        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);

        $maxAncho = 1200;

        if ($ancho > $maxAncho) {

            $nuevoAncho = $maxAncho;

            $nuevoAlto = intval(($alto / $ancho) * $nuevoAncho);

            $imagenRedimensionada = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

            imagecopyresampled($imagenRedimensionada, $imagen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

            imagedestroy($imagen);
            $imagen = $imagenRedimensionada;
        }

        $directorio = __DIR__ . '/../../../uploads/recetas';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0775, true);
        }

        $nombre = 'receta_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.jpg';
        $ruta = $directorio . '/' . $nombre;

        imagejpeg($imagen, $ruta, 75);
        imagedestroy($imagen);

        return '/uploads/recetas/' . $nombre;
    }

    private function procesarImagenesSubidas(array $archivos): array
    {
        $imagenes = [];

        foreach ($this->normalizarArchivosSubidos($archivos) as $archivo) {

            $rutaImagen = $this->procesarArchivoImagen($archivo);

            if ($rutaImagen !== null) {

                $imagenes[] = $rutaImagen;
            }
        }

        return $imagenes;
    }

    private function ordenarImagenesConPortada(array $imagenes, ?string $portadaSeleccionada): array
    {
        $imagenes = array_values(
            array_filter(
                array_map('trim', $imagenes)
            )
        );

        if (empty($imagenes) || $portadaSeleccionada === null || trim($portadaSeleccionada) === '') {

            return $imagenes;
        }

        $indicePortada = array_search($portadaSeleccionada, $imagenes, true);

        if ($indicePortada === false) {

            return $imagenes;
        }

        $portada = $imagenes[$indicePortada];

        unset($imagenes[$indicePortada]);

        array_unshift($imagenes, $portada);

        return array_values($imagenes);
    }

    private function resolverPortadaSeleccionada(array $imagenesSubidas): ?string
    {
        $portada = trim((string) ($_POST['portada_imagen'] ?? ''));

        if ($portada === '') {
            return null;
        }

        if (!str_starts_with($portada, '__new__:')) {
            return $portada;
        }

        $indice = (int) substr($portada, 8);

        return $imagenesSubidas[$indice] ?? null;
    }

    public function index()
    {
        $datos['recetas']      = $this->obtenerRecetasBase();
        $datos['etiquetas']    = $this->etiquetas();
        $datos['ingredientes'] = $this->obtenerIngredientesBase();

        $this->mostrarAdministracion("recetas/recetas.php", "Gestión de Recetas", $datos);
    }

    public function verReceta($id)
    {
        $id = (int)$id;

        if ($id <= 0) {
            Flash::error('Receta inválida.');
            $this->redirigir();
            return;
        }

        try {

            $objReceta = $this->cargarModelo("recetasModel");
            $receta    = $objReceta->obtenerRecetaCompleta($id);
        } catch (\Throwable $e) {

            error_log('[recetas:ver] ' . $e->getMessage());

            Flash::error('No se pudo cargar la receta.');

            $this->redirigir();

            return;
        }

        if (empty($receta)) {

            Flash::warning('La receta solicitada no existe.');
            $this->redirigir();
            return;
        }

        $datos = [
            'receta'       => $receta,
            'etiquetas'    => $this->etiquetas(),
            'ingredientes' => $this->obtenerIngredientesBase(),
        ];

        $this->mostrarAdministracion("recetas/verReceta.php", "Detalle de Receta", $datos);
    }

    private function obtenerRecetasBase()
    {
        try {

            $objReceta = $this->cargarModelo("recetasModel");

            $recetas   = $objReceta->obtenerRecetasBase();

            $recetas   = is_array($recetas) ? $recetas : [];

            foreach ($recetas as &$receta) {

                $etiquetas = $receta['Etiquetas'] ?? null;

                $receta['Etiquetas'] = $etiquetas
                    ? explode(', ', (string) $etiquetas)
                    : [];
            }

            return $recetas;

        } catch (\Throwable $e) {

            error_log('[recetas:obtenerBase] ' . $e->getMessage());
            Flash::error('No se pudieron cargar las recetas.');
            return [];
        }
    }

    function eliminarReceta()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id <= 0) {

            Flash::error('Receta inválida.');
            $this->redirigir();
            return;
        }

        try {

            $objReceta = $this->cargarModelo("recetasModel");
            $objReceta->eliminarReceta($id);
            Flash::success('Receta eliminada correctamente.');

        } catch (\Throwable $e) {

            error_log('[recetas:eliminar] ' . $e->getMessage());
            Flash::error('No se pudo eliminar la receta.');

        }

        $this->redirigir();
    }

    function etiquetas()
    {
        try {

            $objEtiqueta = $this->cargarModelo("etiquetasModel");
            $r           = $objEtiqueta->obtenerEtiquetas();

            return is_array($r) ? $r : [];
        } catch (\Throwable $e) {

            error_log('[recetas:etiquetas] ' . $e->getMessage());
            return [];
        }
    }

    function obtenerIngredientesBase()
    {
        try {

            $objIngrediente = $this->cargarModelo("ingredientesModel");
            $r              = $objIngrediente->obtenerIngredientesBase();

            return is_array($r) ? $r : [];
        } catch (\Throwable $e) {

            error_log('[recetas:ingredientes] ' . $e->getMessage());
            return [];

        }
    }

    private function inyectarEtiquetaFitSiProcede(array &$datos): void
    {
        if (empty($datos['esfit']) || $datos['esfit'] !== 'on') return;
        try {
            $objEtiqueta  = $this->cargarModelo("etiquetasModel");
            $etiquetaFit  = $objEtiqueta->obtenerEtiquetaPorNombre('fit');
            if (!$etiquetaFit) return;
            $idFit = (string)$etiquetaFit['ID_Etiqueta'];
            if (!in_array($idFit, $datos['Etiquetas'] ?? [], true)) {
                $datos['Etiquetas'][] = $idFit;
            }
        } catch (\Throwable $e) {
            error_log('[recetas:fitEtiqueta] ' . $e->getMessage());
        }
    }

    private function redirigir(): void
    {
        header('Location: /pages/administracion/recetas');
        exit;
    }

    
    function ingredientesJson()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->obtenerIngredientesBase(), JSON_UNESCAPED_UNICODE);
        exit;
    }

    
    private function sanearDatosReceta(array $datos): array
    {
        $datos['Titulo']      = trim((string)($datos['Titulo']      ?? ''));
        $datos['Descripcion'] = trim((string)($datos['Descripcion'] ?? ''));

        $pasosBrutos = $datos['paso'] ?? [];
        if (!is_array($pasosBrutos)) $pasosBrutos = [];
        $pasosLimpios = [];
        foreach ($pasosBrutos as $p) {
            $p = trim((string)$p);
            if ($p !== '') $pasosLimpios[] = $p;
        }
        $datos['paso'] = $pasosLimpios;

        $ingreBrutos = $datos['Ingrediente'] ?? [];
        $cantBrutos  = $datos['Cantidad']    ?? [];
        if (!is_array($ingreBrutos)) $ingreBrutos = [];
        if (!is_array($cantBrutos))  $cantBrutos  = [];

        $ingredientes = [];
        $cantidades   = [];
        foreach ($ingreBrutos as $i => $idIng) {
            $idIng = trim((string)$idIng);
            $cant  = trim((string)($cantBrutos[$i] ?? ''));
            if ($idIng === '' || $cant === '') continue;
            $ingredientes[] = $idIng;
            $cantidades[]   = $cant;
        }
        $datos['Ingrediente'] = $ingredientes;
        $datos['Cantidad']    = $cantidades;

        $etiBrutas = $datos['Etiquetas'] ?? [];
        if (!is_array($etiBrutas)) $etiBrutas = [];
        $etiquetas = [];
        foreach ($etiBrutas as $e) {
            $e = trim((string)$e);
            if ($e !== '') $etiquetas[] = $e;
        }
        $datos['Etiquetas'] = $etiquetas;

        return $datos;
    }

    private function validarDatosReceta(array $datos): array
    {
        $errores = [];

        $titulo      = trim((string)($datos['Titulo'] ?? ''));
        $descripcion = trim((string)($datos['Descripcion'] ?? ''));

        if ($titulo === '') {
            $errores[] = 'El título es obligatorio.';
        } elseif (mb_strlen($titulo) < 2 || mb_strlen($titulo) > 120) {
            $errores[] = 'El título debe tener entre 2 y 120 caracteres.';
        }

        if ($descripcion === '') {
            $errores[] = 'La descripción es obligatoria.';
        } elseif (mb_strlen($descripcion) < 5 || mb_strlen($descripcion) > 1000) {
            $errores[] = 'La descripción debe tener entre 5 y 1000 caracteres.';
        }

        if (!isset($datos['Tiempo']) || !is_numeric($datos['Tiempo']) || (int)$datos['Tiempo'] <= 0) {
            $errores[] = 'El tiempo debe ser un número mayor que 0.';
        } elseif ((int)$datos['Tiempo'] > 1440) {
            $errores[] = 'El tiempo no puede superar los 1440 minutos (24 horas).';
        }

        if (!isset($datos['Porciones']) || !is_numeric($datos['Porciones']) || (int)$datos['Porciones'] <= 0) {
            $errores[] = 'Las porciones deben ser un número mayor que 0.';
        } elseif ((int)$datos['Porciones'] > 100) {
            $errores[] = 'Las porciones no pueden superar 100.';
        }

        $pasos = (isset($datos['paso']) && is_array($datos['paso'])) ? $datos['paso'] : [];
        if (count($pasos) === 0) {
            $errores[] = 'La receta debe tener al menos un paso.';
        } else {
            foreach ($pasos as $paso) {
                if (mb_strlen($paso) > 500) {
                    $errores[] = 'Cada paso debe tener como máximo 500 caracteres.';
                    break;
                }
            }
        }

        $ingredientes = (isset($datos['Ingrediente']) && is_array($datos['Ingrediente'])) ? $datos['Ingrediente'] : [];
        $cantidades   = (isset($datos['Cantidad'])    && is_array($datos['Cantidad']))    ? $datos['Cantidad']    : [];

        if (count($ingredientes) === 0) {
            $errores[] = 'La receta debe tener al menos un ingrediente con su cantidad.';
        } else {
            foreach ($cantidades as $cantidad) {
                if (mb_strlen($cantidad) > 50) {
                    $errores[] = 'La cantidad de cada ingrediente no puede superar 50 caracteres.';
                    break;
                }
            }
        }

        return $errores;
    }

    
    private function esPeticionAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    
    private function responderReceta(bool $ok, string $mensaje, int $status = 200): void
    {
        if ($this->esPeticionAjax()) {
            if (!$ok) http_response_code($status > 0 ? $status : 422);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => $ok, 'message' => $mensaje], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($ok) {
            Flash::success($mensaje);
        } else {
            Flash::error($mensaje);
        }
        $this->redirigir();
    }

    function crearReceta()
    {

        $datos = $_POST['datos'] ?? [];

        if (!is_array($datos)) {

            $this->responderReceta(false, 'No se recibieron los datos de la receta.', 400);
            return;

        }

        $datos = $this->sanearDatosReceta($datos);
        $this->inyectarEtiquetaFitSiProcede($datos);

        $errores = $this->validarDatosReceta($datos);

        if (!empty($errores)) {

            $this->responderReceta(false, implode(' ', $errores), 422);
            return;

        }

        $datosReceta = [

            'esfit' => isset($datos['esfit']) && $datos['esfit'] === 'on' ? 1 : 0,
        ];

        foreach ($datos as $clave => $valor) {

            if (empty($valor) && $valor !== '0') {
                continue;
            }
            switch ($clave) {
                case 'Titulo':
                case 'Descripcion':
                case 'Tiempo':
                case 'Porciones':
                    $datosReceta[$clave] = $valor;
                    break;
            }
        }

        $datosReceta['paso'] = json_encode($datos['paso'] ?? [], JSON_UNESCAPED_UNICODE);

        try {

            $imagenesNuevas = $this->procesarImagenesSubidas($_FILES['imagen'] ?? []);

        } catch (\RuntimeException $e) {

            error_log('[recetas:crear imagen] ' . $e->getMessage());
            $this->responderReceta(false, 'No se pudo procesar alguna de las imágenes subidas.', 422);
            return;
        }

        $portada = $this->resolverPortadaSeleccionada($imagenesNuevas);
        $imagenesFinal = $this->ordenarImagenesConPortada($imagenesNuevas, $portada);
        $datosReceta['imagen'] = $this->serializarImagenesReceta($imagenesFinal);

        $idRecetaCreada = null;
        try {
            $objReceta = $this->cargarModelo("recetasModel");

            $idReceta = $objReceta->crearRecetaBase($datosReceta);

            if (empty($idReceta)) {

                $this->responderReceta(false, 'No se pudo crear la receta.'.$idReceta, 500);
                return;
            }

            $idRecetaCreada = (int)$idReceta;

            try {

                if (!empty($datos['Etiquetas']) && is_array($datos['Etiquetas'])) {
                    foreach ($datos['Etiquetas'] as $idEtiqueta) {
                        $objReceta->etiquetasEnReceta($idEtiqueta, $idReceta);
                    }
                }

                if (!empty($datos['Ingrediente']) && is_array($datos['Ingrediente'])) {
                    foreach ($datos['Ingrediente'] as $indice => $idIngrediente) {
                        $cantidad = $datos['Cantidad'][$indice] ?? '';
                        $objReceta->ingredientesEnReceta($idIngrediente, $idReceta, $cantidad);
                    }
                }

            } catch (\Throwable $eVinculo) {
                try {
                    $objReceta->eliminarReceta($idRecetaCreada);
                } catch (\Throwable $eClean) {
                    error_log('[recetas:crear rollback] ' . $eClean->getMessage());
                }
                $idRecetaCreada = null;
                throw $eVinculo;
            }

            $this->responderReceta(true, 'Receta creada correctamente.');
            return;

        } catch (\Throwable $e) {
            error_log('[recetas:crear] ' . $e->getMessage());
            $this->responderReceta(false, 'No se pudo crear la receta. Inténtalo de nuevo.'.$e, 500);
            return;
        }
    }

    function obtenerRecetaJson($id)
    {

        $objReceta = $this->cargarModelo("recetasModel");

        $receta = $objReceta->obtenerRecetaBasePorId($id);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($receta, JSON_UNESCAPED_UNICODE);
        exit;
    }

    
    function editarReceta()
    {
        $datos = $_POST['datos'] ?? [];
        if (!is_array($datos)) {
            $this->responderReceta(false, 'No se recibieron los datos de la receta.', 400);
            return;
        }

        $idReceta = isset($datos['id_receta']) ? (int)$datos['id_receta'] : 0;

        if ($idReceta <= 0) {

            $this->responderReceta(false, 'Falta el identificador de la receta a editar.', 422);
            return;
        }

        $datos = $this->sanearDatosReceta($datos);
        $this->inyectarEtiquetaFitSiProcede($datos);

        $errores = $this->validarDatosReceta($datos);

        if (!empty($errores)) {
            $this->responderReceta(false, implode(' ', $errores), 422);
            return;
        }

        $datosReceta = [
            'esfit' => isset($datos['esfit']) && $datos['esfit'] === 'on' ? 1 : 0,
        ];

        foreach ($datos as $clave => $valor) {
            if (empty($valor) && $valor !== '0') {
                continue;
            }
            switch ($clave) {
                case 'Titulo':
                case 'Descripcion':
                case 'Tiempo':
                case 'Porciones':
                    $datosReceta[$clave] = $valor;
                    break;
            }
        }

        $datosReceta['paso'] = json_encode($datos['paso'] ?? [], JSON_UNESCAPED_UNICODE);

        try {

            $objReceta = $this->cargarModelo("recetasModel");

            $recetaActual = $objReceta->obtenerRecetaBasePorId($idReceta);

            if (!$recetaActual) {

                $this->responderReceta(false, 'La receta ya no existe.', 404);
                return;
            }

            $imagenesActuales                = $this->obtenerImagenesReceta($recetaActual['Imagen'] ?? null);
            $imagenesExistentesSeleccionadas = $this->normalizarImagenesExistentesSeleccionadas($imagenesActuales);

            try {

                $imagenesNuevas = $this->procesarImagenesSubidas($_FILES['imagen'] ?? []);

            } catch (\RuntimeException $e) {

                error_log('[recetas:editar imagen] ' . $e->getMessage());
                $this->responderReceta(false, 'No se pudo procesar alguna de las imágenes subidas.', 422);
                return;

            }

            $portada       = $this->resolverPortadaSeleccionada($imagenesNuevas);

            $imagenesFinal = $this->ordenarImagenesConPortada(
                array_merge($imagenesExistentesSeleccionadas, $imagenesNuevas),
                $portada
            );

            $datosReceta['imagen'] = $this->serializarImagenesReceta($imagenesFinal);

            $objReceta->actualizarRecetaBase($idReceta, $datosReceta);

            $objReceta->eliminarEtiquetasDeReceta($idReceta);
            $objReceta->eliminarIngredientesDeReceta($idReceta);

            if (!empty($datos['Etiquetas']) && is_array($datos['Etiquetas'])) {

                foreach ($datos['Etiquetas'] as $idEtiqueta) {

                    $objReceta->etiquetasEnReceta($idEtiqueta, $idReceta);
                }
            }

            if (!empty($datos['Ingrediente']) && is_array($datos['Ingrediente'])) {

                foreach ($datos['Ingrediente'] as $indice => $idIngrediente) {

                    $cantidad = $datos['Cantidad'][$indice] ?? '';
                    $objReceta->ingredientesEnReceta($idIngrediente, $idReceta, $cantidad);

                }
            }

            $this->responderReceta(true, 'Receta actualizada correctamente.');
            return;

        } catch (\Throwable $e) {

            error_log('[recetas:editar] ' . $e->getMessage());
            $this->responderReceta(false, 'No se pudo editar la receta. Inténtalo de nuevo.', 500);
            return;

        }
    }
}
