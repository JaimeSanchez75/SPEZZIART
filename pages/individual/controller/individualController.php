<?php
require_once __DIR__ . '/../model/individualModel.php';
require_once __DIR__ . '/../view/individualView.php';

class individualController
{
    private const RECETAS_POR_PAGINA = 6;
    private const LIMITE_CARACTERES_TITULO = 60;
    private const LIMITE_CARACTERES_DESCRIPCION = 350;
    private const LIMITE_CARACTERES_INGREDIENTE = 60;
    private const LIMITE_CARACTERES_PASO = 100;
    private const LIMITE_TIEMPO_MINUTOS = 1440;
    private const LIMITE_PORCIONES = 100;
    private const LIMITE_CANTIDAD_INGREDIENTE = 9999.0;
    private const LIMITE_VALOR_NUTRICIONAL = 9999.0;

    private function anexarPreviewImagenAColecciones(array $colecciones, individualModel $model): array
    {
        foreach ($colecciones as &$coleccion) {
            $coleccion['PreviewImage'] = null;
            $recetasColeccion = $model->getRecetasDeColeccion((int)$coleccion['ID_Coleccion']);

            foreach ($recetasColeccion as $receta) {
                $imagenes = $this->obtenerImagenesReceta($receta['Imagen'] ?? null);
                if (!empty($imagenes[0])) {
                    $coleccion['PreviewImage'] = $imagenes[0];
                    break;
                }
            }
        }

        return $colecciones;
    }

    private function obtenerImagenesReceta(?string $imagenes): array
    {
        if ($imagenes === null || trim($imagenes) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn($imagen) => trim((string)$imagen),
            explode(',', $imagenes)
        ), static fn($imagen) => $imagen !== ''));
    }

    private function serializarImagenesReceta(array $imagenes): ?string
    {
        $imagenes = array_values(array_filter(array_map(
            static fn($imagen) => trim((string)$imagen),
            $imagenes
        ), static fn($imagen) => $imagen !== ''));

        return empty($imagenes) ? null : implode(',', $imagenes);
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

    private function ordenarImagenesConPortada(array $imagenes, ?string $portadaSeleccionada): array
    {
        $imagenes = array_values(array_filter(array_map(
            static fn($imagen) => trim((string)$imagen),
            $imagenes
        ), static fn($imagen) => $imagen !== ''));

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

    private function normalizarEnteroNoNegativo($valor): int
    {
        return max(0, (int)$valor);
    }

    private function contarCaracteres(string $texto): int
    {
        return mb_strlen($texto, 'UTF-8');
    }

    private function separarDescripcionYPasos(array $receta): array
    {
        $descripcion = trim((string)($receta['Descripcion'] ?? ''));
        $pasos = [];

        if (!empty($receta['Pasos'])) {
            $pasosJson = json_decode((string)$receta['Pasos'], true);
            if (is_array($pasosJson)) {
                $pasos = array_values(array_filter(array_map(static fn($paso) => trim((string)$paso), $pasosJson), static fn($paso) => $paso !== ''));
            }
        }

        if (empty($pasos)) {
            $separador = "\n\nPASOS:\n";
            $posicionSeparador = strrpos($descripcion, $separador);
            if ($posicionSeparador !== false) {
                $bloquePasos = trim(substr($descripcion, $posicionSeparador + strlen($separador)));
                $descripcion = trim(substr($descripcion, 0, $posicionSeparador));

                foreach (preg_split('/\R+/', $bloquePasos) as $linea) {
                    $linea = trim((string)$linea);
                    if ($linea === '') {
                        continue;
                    }
                    $pasos[] = preg_replace('/^\d+\.\s*/', '', $linea);
                }
            }
        }

        return [
            'descripcion' => $descripcion,
            'pasos' => $pasos
        ];
    }

    private function normalizarIngredientesFormulario(): array
    {
        $ids = $_POST['ingrediente_id'] ?? [];
        $cantidades = $_POST['ingrediente_cantidad'] ?? [];
        $nombres = $_POST['ingrediente_nombre'] ?? [];
        $calorias = $_POST['ingrediente_calorias'] ?? [];
        $proteinas = $_POST['ingrediente_proteina'] ?? [];
        $carbohidratos = $_POST['ingrediente_carbohidratos'] ?? [];
        $grasas = $_POST['ingrediente_grasas'] ?? [];
        $unidades = $_POST['ingrediente_unidad_nombre'] ?? [];
        $total = max(count($ids), count($cantidades), count($nombres), count($calorias), count($proteinas), count($carbohidratos), count($grasas));
        $ingredientes = [];
        for ($i = 0; $i < $total; $i++) 
        {
            $ingredientes[] = 
            [
                'id' => isset($ids[$i]) ? (int)$ids[$i] : 0,
                'cantidad' => $this->normalizarCantidadIngrediente($cantidades[$i] ?? ''),
                'nuevo' => trim((string)($nombres[$i] ?? '')),
                'calorias' => $this->normalizarValorNutricional($calorias[$i] ?? 0),
                'proteina' => $this->normalizarValorNutricional($proteinas[$i] ?? 0),
                'carbohidratos' => $this->normalizarValorNutricional($carbohidratos[$i] ?? 0),
                'grasas' => $this->normalizarValorNutricional($grasas[$i] ?? 0),
                'unidad_base' => trim((string)($unidades[$i] ?? ''))
            ];
        }
        return $ingredientes;
    }

    private function contarIngredientesValidos(array $ingredientes): int
    {
        $total = 0;

        foreach ($ingredientes as $ingrediente) {
            $idIngrediente = (int)($ingrediente['id'] ?? 0);
            $nombreIngrediente = trim((string)($ingrediente['nuevo'] ?? ''));

            if ($idIngrediente > 0 || $nombreIngrediente !== '') {
                $total++;
            }
        }

        return $total;
    }

    private function normalizarValorNutricional($valor): float
    {
        $numero = is_numeric($valor) ? (float)$valor : 0.0;
        $numero = max(0, min($numero, self::LIMITE_VALOR_NUTRICIONAL));
        return round($numero, 2);
    }

    private function normalizarCantidadIngrediente($valor): ?string
    {
        $valor = str_replace(',', '.', trim((string)$valor));

        if ($valor === '' || !is_numeric($valor)) {
            return null;
        }

        $cantidad = round((float)$valor, 2);

        if ($cantidad <= 0) {
            return null;
        }

        return rtrim(rtrim(number_format($cantidad, 2, '.', ''), '0'), '.');
    }

    private function calcularNutricionTotal(array $ingredientes): array
    {
        $totales = [
            'calorias' => 0.0,
            'proteina' => 0.0,
            'carbohidratos' => 0.0,
            'grasas' => 0.0
        ];

        foreach ($ingredientes as $ingrediente) {
            $totales['calorias'] += $this->normalizarValorNutricional($ingrediente['calorias'] ?? 0);
            $totales['proteina'] += $this->normalizarValorNutricional($ingrediente['proteina'] ?? 0);
            $totales['carbohidratos'] += $this->normalizarValorNutricional($ingrediente['carbohidratos'] ?? 0);
            $totales['grasas'] += $this->normalizarValorNutricional($ingrediente['grasas'] ?? 0);
        }

        return $totales;
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
        foreach ($archivos['name'] as $indice => $_) {
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

        $mime = mime_content_type($archivo['tmp_name']);

        $crearImagen = match ($mime) {
            'image/jpeg' => fn($path) => imagecreatefromjpeg($path),
            'image/png' => fn($path) => imagecreatefrompng($path),
            'image/webp' => fn($path) => imagecreatefromwebp($path),
            default => null
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

    private function procesarImagenes(array $archivos, array $imagenesActuales = []): array
    {
        $imagenes = $imagenesActuales;

        foreach ($this->normalizarArchivosSubidos($archivos) as $archivo) {
            $rutaImagen = $this->procesarArchivoImagen($archivo);
            if ($rutaImagen !== null) {
                $imagenes[] = $rutaImagen;
            }
        }

        return array_values(array_unique(array_filter($imagenes)));
    }

    private function resolverPortadaSeleccionada(array $imagenesSubidas): ?string
    {
        $portadaSeleccionada = trim((string)($_POST['portada_imagen'] ?? ''));
        if ($portadaSeleccionada === '') {
            return null;
        }

        if (str_starts_with($portadaSeleccionada, '__new__:')) {
            $indiceNueva = (int)substr($portadaSeleccionada, 8);
            return $imagenesSubidas[$indiceNueva] ?? null;
        }

        return $portadaSeleccionada;
    }

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

            $colecciones = $this->anexarPreviewImagenAColecciones($colecciones, $model);

            $totalRecetas = count($misRecetas);
            $totalPaginas = max(1, (int)ceil($totalRecetas / self::RECETAS_POR_PAGINA));
            $paginaActual = max(1, (int)($_GET['page'] ?? 1));
            $paginaActual = min($paginaActual, $totalPaginas);
            $offset = ($paginaActual - 1) * self::RECETAS_POR_PAGINA;
            $misRecetasPaginadas = array_slice($misRecetas, $offset, self::RECETAS_POR_PAGINA);

            $pagination = [
                'currentPage' => $paginaActual,
                'perPage' => self::RECETAS_POR_PAGINA,
                'totalItems' => $totalRecetas,
                'totalPages' => $totalPaginas
            ];

            $recetasGuardadas = [];
            $config = [
                'ModoOscuro' => $user['ModoOscuro'] ?? false,
                'ModoFit' => $user['ModoFit'] ?? false
            ];

            $view->render($misRecetasPaginadas, $recetasGuardadas, [], $config, $colecciones, $busqueda, $pagination);
        } catch (Exception $e) {
            die('Error en index: ' . $e->getMessage());
        }
    }

    public function crear()
    {
        $userId = Auth::id();
        $model = new individualModel();
        $receta = null;
        $modoFitUsuario = (bool)(Auth::user()['ModoFit'] ?? false);

        if (!empty($_GET['id'])) {
            $id = (int)$_GET['id'];
            $receta = $model->getRecetaByIdAndUser($id, $userId);
            if (!$receta) {
                echo "<script>window.history.back();</script>";
                exit;
            }
        }

        $receta = $receta ?: [];
        $imagenesReceta = $this->obtenerImagenesReceta($receta['Imagen'] ?? null);
        $contenidoReceta = $this->separarDescripcionYPasos($receta);
        $descripcionFormulario = $contenidoReceta['descripcion'];
        $pasosFormulario = !empty($contenidoReceta['pasos']) ? $contenidoReceta['pasos'] : [''];
        $ingredientesDisponibles = $model->getIngredientesDisponibles($userId);
        $etiquetasDisponibles = $model->getEtiquetasDisponibles();
        $ingredientesFormulario = !empty($receta['ingredientes']) ? $receta['ingredientes'] : [['ID_Ingrediente' => '', 'Nombre' => '', 'Cantidad' => '']];
        $etiquetasSeleccionadas = array_map(
            static fn($etiqueta) => (int)($etiqueta['ID_Etiqueta'] ?? 0),
            $receta['etiquetas'] ?? []
        );
        $nutricionFormulario = [
            'calorias' => (float)($receta['Calorias'] ?? 0),
            'proteina' => (float)($receta['Proteina'] ?? 0),
            'carbohidratos' => (float)($receta['Carbohidratos'] ?? 0),
            'grasas' => (float)($receta['Grasas'] ?? 0)
        ];

        require_once __DIR__ . '/../view/crearRecetaView.php';
    }

     public function guardar()
    {
        csrf_verify();
        $userId = Auth::id();
        $model = new individualModel();

        try {
            $idReceta = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $recetaActual = $idReceta ? $model->getRecetaByIdAndUser($idReceta, $userId) : null;

            if ($idReceta && !$recetaActual) {
                die('No tienes permiso para editar esta receta.');
            }

            $imagenesActuales = $this->obtenerImagenesReceta($recetaActual['Imagen'] ?? null);
            $imagenesExistentesSeleccionadas = $this->normalizarImagenesExistentesSeleccionadas($imagenesActuales);
            $imagenesNuevasSubidas = [];

            foreach ($this->normalizarArchivosSubidos($_FILES['imagen'] ?? []) as $archivo) {
                $rutaImagen = $this->procesarArchivoImagen($archivo);
                if ($rutaImagen !== null) {
                    $imagenesNuevasSubidas[] = $rutaImagen;
                }
            }

            $portadaSeleccionada = $this->resolverPortadaSeleccionada($imagenesNuevasSubidas);
            $imagenesGuardadas = $this->ordenarImagenesConPortada(
                array_merge($imagenesExistentesSeleccionadas, $imagenesNuevasSubidas),
                $portadaSeleccionada
            );
            $ingredientes = $this->normalizarIngredientesFormulario();
            $etiquetas = array_map('intval', $_POST['etiquetas'] ?? []);
            $nutricionTotal = $this->calcularNutricionTotal($ingredientes);
            $pasos = array_values(array_filter(array_map('trim', $_POST['pasos'] ?? []), static fn($paso) => $paso !== ''));
            $esFit = isset($_POST['fit']) ? 1 : 0;
            $titulo = trim((string)($_POST['titulo'] ?? ''));
            $descripcion = trim((string)($_POST['descripcion'] ?? ''));
            $tiempo = $this->normalizarEnteroNoNegativo($_POST['tiempo'] ?? 0);
            $porciones = $this->normalizarEnteroNoNegativo($_POST['porciones'] ?? 0);

            if ($titulo === '') {
                Flash::error('El titulo de la receta es obligatorio.');
                header('Location: /pages/individual/crear' . ($idReceta ? '?id=' . $idReceta : ''));
                exit;
            }

            if ($this->contarCaracteres($titulo) > self::LIMITE_CARACTERES_TITULO) {
                Flash::error('El titulo no puede superar los ' . self::LIMITE_CARACTERES_TITULO . ' caracteres.');
                header('Location: /pages/individual/crear' . ($idReceta ? '?id=' . $idReceta : ''));
                exit;
            }

            if ($this->contarCaracteres($descripcion) > self::LIMITE_CARACTERES_DESCRIPCION) {
                Flash::error('La descripcion no puede superar los ' . self::LIMITE_CARACTERES_DESCRIPCION . ' caracteres.');
                header('Location: /pages/individual/crear' . ($idReceta ? '?id=' . $idReceta : ''));
                exit;
            }

            foreach ($ingredientes as $ingrediente) {
                $nombreIngrediente = trim((string)($ingrediente['nuevo'] ?? ''));
                if ($nombreIngrediente !== '' && $this->contarCaracteres($nombreIngrediente) > self::LIMITE_CARACTERES_INGREDIENTE) {
                    Flash::error('Los ingredientes nuevos no pueden superar los ' . self::LIMITE_CARACTERES_INGREDIENTE . ' caracteres.');
                    header('Location: /pages/individual/crear' . ($idReceta ? '?id=' . $idReceta : ''));
                    exit;
                }
            }

            foreach ($pasos as $paso) {
                if ($this->contarCaracteres($paso) > self::LIMITE_CARACTERES_PASO) {
                    Flash::error('Cada paso no puede superar los ' . self::LIMITE_CARACTERES_PASO . ' caracteres.');
                    header('Location: /pages/individual/crear' . ($idReceta ? '?id=' . $idReceta : ''));
                    exit;
                }
            }

            if ($tiempo > self::LIMITE_TIEMPO_MINUTOS) {
                Flash::error('El tiempo no puede superar los ' . self::LIMITE_TIEMPO_MINUTOS . ' minutos.');
                header('Location: /pages/individual/crear' . ($idReceta ? '?id=' . $idReceta : ''));
                exit;
            }

            if ($porciones > self::LIMITE_PORCIONES) {
                Flash::error('Las porciones no pueden superar las ' . self::LIMITE_PORCIONES . '.');
                header('Location: /pages/individual/crear' . ($idReceta ? '?id=' . $idReceta : ''));
                exit;
            }

            foreach ($ingredientes as $ingrediente) {
                $idIngrediente = (int)($ingrediente['id'] ?? 0);
                $nombre = trim((string)($ingrediente['nuevo'] ?? ''));
                $unidadBase = trim((string)($ingrediente['unidad_base'] ?? ''));

                if (($idIngrediente > 0 || $nombre !== '') && ($ingrediente['cantidad'] ?? null) === null) {
                    Flash::error('Cada ingrediente debe tener una cantidad valida mayor que 0.');
                    header('Location: /pages/individual/crear' . ($idReceta ? '?id=' . $idReceta : ''));
                    exit;
                }

                if (($ingrediente['cantidad'] ?? null) !== null && (float)$ingrediente['cantidad'] > self::LIMITE_CANTIDAD_INGREDIENTE) {
                    Flash::error('La cantidad de cada ingrediente no puede superar ' . self::LIMITE_CANTIDAD_INGREDIENTE . '.');
                    header('Location: /pages/individual/crear' . ($idReceta ? '?id=' . $idReceta : ''));
                    exit;
                }

                if ($idIngrediente <= 0 && $nombre !== '' && !in_array($unidadBase, ['g', 'ml'])) {
                    Flash::error('Debes seleccionar la unidad (g o ml) para los ingredientes nuevos.');
                    header('Location: /pages/individual/crear' . ($idReceta ? '?id=' . $idReceta : ''));
                    exit;
                }
            }

            if ($this->contarIngredientesValidos($ingredientes) < 1) {
                Flash::error('Debes anadir al menos un ingrediente para guardar la receta.');
                header('Location: /pages/individual/crear' . ($idReceta ? '?id=' . $idReceta : ''));
                exit;
            }

            $data = [
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'tiempo' => $tiempo,
                'porciones' => $porciones,
                'imagen' => $this->serializarImagenesReceta($imagenesGuardadas),
                'fit' => $esFit,
                'pasos' => json_encode($pasos, JSON_UNESCAPED_UNICODE),
                'calorias' => $nutricionTotal['calorias'],
                'proteina' => $nutricionTotal['proteina'],
                'carbohidratos' => $nutricionTotal['carbohidratos'],
                'grasas' => $nutricionTotal['grasas']
            ];

            $model->beginTransaction();

            if ($idReceta) {
                $model->actualizarReceta($idReceta, $userId, $data);
            } else {
                $idReceta = $model->crearReceta($userId, $data);
            }

            $model->guardarIngredientesDeReceta($idReceta, $userId, $ingredientes);
            $model->guardarEtiquetasDeReceta($idReceta,$_POST['etiquetas'] ?? [],!empty($_POST['fit']));
            $model->commit();

            header('Location: /pages/individual');
            exit;
        } catch (Exception $e) {
            $model->rollBack();
            die('Error al guardar receta: ' . $e->getMessage());
        }
    }

    public function ver()
    {
        $model = new individualModel();

        if (empty($_GET['id'])) {
            die('Receta no encontrada');
        }

        $receta = $model->getRecetaById((int)$_GET['id']);
        if (!$receta) {
            die('Receta no existe.');
        }

        $contenidoReceta = $this->separarDescripcionYPasos($receta);
        $receta['DescripcionVisible'] = $contenidoReceta['descripcion'];
        $receta['Pasos'] = $contenidoReceta['pasos'];
        $receta['Imagenes'] = $this->obtenerImagenesReceta($receta['Imagen'] ?? null);

        require_once __DIR__ . '/../view/verRecetaView.php';
    }

    public function eliminar()
    {
        csrf_verify();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['idReceta'])) {
            $userId = Auth::id();
            $model = new individualModel();
            $id = (int)$_POST['idReceta'];

            if (!$model->eliminarReceta($id, $userId)) {
                die('No tienes permiso para eliminar esta receta.');
            }
        }

        header('Location: /pages/individual');
        exit;
    }

    public function crearColeccion()
    {
        csrf_verify();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombreColeccion'])) {
            $model = new individualModel();
            $model->crearColeccion(Auth::id(), $_POST['nombreColeccion'], true);
        }

        header('Location: /pages/individual');
        exit;
    }

    public function verColeccion()
    {
        $userId = Auth::id();
        if (empty($_GET['id'])) {
            die('Coleccion no encontrada');
        }

        $model = new individualModel();
        $coleccionId = (int)$_GET['id'];
        $coleccion = $model->getColeccionByIdAndUser($coleccionId, $userId);
        if (!$coleccion) {
            die('Coleccion no encontrada o no tienes acceso.');
        }

        $recetas = $model->getRecetasDeColeccion($coleccionId);
        $config = ['ModoOscuro' => Auth::user()['ModoOscuro'] ?? false];

        $totalRecetas  = count($recetas);
        $totalPaginas  = max(1, (int)ceil($totalRecetas / self::RECETAS_POR_PAGINA));
        $paginaActual  = max(1, (int)($_GET['page'] ?? 1));
        $paginaActual  = min($paginaActual, $totalPaginas);
        $offset        = ($paginaActual - 1) * self::RECETAS_POR_PAGINA;
        $recetasPaginadas = array_slice($recetas, $offset, self::RECETAS_POR_PAGINA);

        $pagination = [
            'currentPage' => $paginaActual,
            'totalPages'  => $totalPaginas,
            'totalItems'  => $totalRecetas,
        ];

        require_once __DIR__ . '/../view/coleccionView.php';
        $view = new ColeccionView();
        $view->render($coleccion, $recetasPaginadas, $config, $pagination);
    }

    public function agregarReceta()
    {
        csrf_verify();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['idReceta']) && !empty($_POST['idColeccion'])) {
            $userId = Auth::id();
            $model = new individualModel();
            $idReceta = (int)$_POST['idReceta'];
            $idColeccion = (int)$_POST['idColeccion'];

            if (!$model->getColeccionByIdAndUser($idColeccion, $userId)) {
                die('No tienes permiso para modificar esta coleccion.');
            }

            $model->agregarRecetaAColeccion($idReceta, $idColeccion);
            header('Location: /pages/individual/coleccion?id=' . $idColeccion);
            exit;
        }

        die('Error: datos incompletos.');
    }

    public function renombrarColeccion()
    {
        csrf_verify();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['idColeccion']) && isset($_POST['nuevoNombre'])) {
            $userId = Auth::id();
            $model = new individualModel();
            $idColeccion = (int)$_POST['idColeccion'];
            $nuevoNombre = mb_substr(trim((string)$_POST['nuevoNombre']), 0, 60);

            if (!$model->getColeccionByIdAndUser($idColeccion, $userId)) {
                die('No tienes permisos para renombrar esta coleccion.');
            }

            $model->renombrarColeccion($idColeccion, $userId, $nuevoNombre);
        }

        header('Location: /pages/individual');
        exit;
    }

    public function eliminarColeccion()
    {
        csrf_verify();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['idColeccion'])) {
            $userId = Auth::id();
            $model = new individualModel();
            $idColeccion = (int)$_POST['idColeccion'];

            if (!$model->getColeccionByIdAndUser($idColeccion, $userId)) {
                die('No tienes permisos para eliminar esta coleccion.');
            }

            if (!$model->eliminarColeccion($idColeccion)) {
                die('Error al eliminar la coleccion.');
            }
        }

        header('Location: /pages/individual');
        exit;
    }

    public function eliminarRecetaDeColeccion()
    {
        csrf_verify();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['idReceta']) && !empty($_POST['idColeccion'])) {
            $userId = Auth::id();
            $model = new individualModel();
            $idReceta = (int)$_POST['idReceta'];
            $idColeccion = (int)$_POST['idColeccion'];

            if (!$model->getColeccionByIdAndUser($idColeccion, $userId)) {
                die('No tienes permisos.');
            }

            $model->eliminarRecetaDeColeccion($idReceta, $idColeccion);
            header('Location: /pages/individual/coleccion?id=' . $idColeccion);
            exit;
        }

        die('Datos incompletos.');
    }

    public function obtenerColecciones()
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }

        $userId = Auth::id();
        $model = new individualModel();
        $colecciones = $model->getColeccionesUsuario($userId);

        $idReceta = !empty($_GET['receta_id']) ? (int)$_GET['receta_id'] : null;
        if ($idReceta) {
            $coleccionesConReceta = $model->getColeccionesDeReceta($idReceta, $userId);
            foreach ($colecciones as &$col) {
                $col['tieneReceta'] = in_array((int)$col['ID_Coleccion'], $coleccionesConReceta, true);
            }
            unset($col);
        }

        header('Content-Type: application/json');
        echo json_encode($colecciones);
        exit();
    }

    public function guardarRecetaApi()
    {
        csrf_verify();
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'msg' => 'No autenticado']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $idReceta = $input['id_receta'] ?? null;
        $colecciones = $input['colecciones'] ?? [];
        $userId = Auth::id();

        if (!$idReceta) {
            echo json_encode(['ok' => false, 'msg' => 'Falta ID de receta']);
            exit;
        }

        if (empty($colecciones)) {
            echo json_encode(['ok' => false, 'msg' => 'Selecciona al menos una coleccion']);
            exit;
        }

        $model = new individualModel();
        $resultado = $model->guardarEnCols($idReceta, $userId, $colecciones);

        header('Content-Type: application/json');
        if ($resultado['ok']) {
            echo json_encode(['ok' => true, 'msg' => 'Receta guardada en ' . $resultado['insertados'] . ' coleccion(es)']); exit();
        } else {
            echo json_encode(['ok' => false, 'msg' => $resultado['msg']]); exit();
        }
    }
}
