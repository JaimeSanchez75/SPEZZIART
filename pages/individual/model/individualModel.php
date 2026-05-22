<?php
require_once __DIR__ . '/../../../core/db.php';

class individualModel
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::conectar();
    }

    private function obtenerIngredientesDeReceta(int $idReceta): array
    {
        $stmt = $this->db->prepare("
            SELECT i.ID_Ingrediente, i.Nombre, i.Calorias, i.Proteina, i.Carbohidratos, i.Grasas, i.Verificada, i.Unidad_Base,
                ri.Cantidad
            FROM Ingrediente i
            JOIN Receta_Ingrediente ri ON i.ID_Ingrediente = ri.ID_Ingrediente
            WHERE ri.ID_Receta = :id
            ORDER BY i.Nombre ASC
        ");
        $stmt->execute(['id' => $idReceta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerEtiquetasDeReceta(int $idReceta): array
    {
        $stmt = $this->db->prepare(
            "
                SELECT e.ID_Etiqueta, e.Nombre
                FROM Etiqueta e
                JOIN Etiqueta_Receta er ON e.ID_Etiqueta = er.ID_Etiqueta
                WHERE er.ID_Receta = :id
                ORDER BY e.Nombre ASC
            "
        );
        $stmt->execute(['id' => $idReceta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function anexarDetallesARecetas(array $recetas): array
    {
        foreach ($recetas as &$receta) {
            $idReceta = (int)$receta['ID_Receta'];
            $receta['ingredientes'] = $this->obtenerIngredientesDeReceta($idReceta);
            $receta['etiquetas'] = $this->obtenerEtiquetasDeReceta($idReceta);
        }

        return $recetas;
    }

    private function esErrorFueraDeRango(PDOException $e): bool
    {
        return (($e->errorInfo[0] ?? null) === '22003')
            || str_contains($e->getMessage(), 'Out of range value');
    }
    
    private function anexarPreviewImagenAColecciones(array $colecciones): array
    {
        foreach ($colecciones as &$coleccion) {
            $coleccion['PreviewImage'] = null;

            $stmt = $this->db->prepare(
                "
                    SELECT r.Imagen
                    FROM Coleccion_Receta cr
                    INNER JOIN Receta r ON r.ID_Receta = cr.ID_Receta
                    WHERE cr.ID_Coleccion = :idColeccion
                      AND r.Imagen IS NOT NULL
                      AND TRIM(r.Imagen) <> ''
                    ORDER BY r.FechaCreacion DESC
                    LIMIT 1
                "
            );
            $stmt->execute(['idColeccion' => $coleccion['ID_Coleccion']]);

            $imagenes = $stmt->fetchColumn();
            if (!$imagenes) {
                continue;
            }

            $primeraImagen = trim((string)explode(',', (string)$imagenes)[0]);
            if ($primeraImagen !== '') {
                $coleccion['PreviewImage'] = $primeraImagen;
            }
        }

        return $colecciones;
    }

    public function beginTransaction(): void
    {
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }
    }

    public function getUnidadesIngrediente(int $idIngrediente): array
    {
        // La tabla Ingrediente_Unidad no existe en esta base de datos.
        // Se retorna vacío; la unidad base está en Ingrediente.Unidad_Base.
        return [];
    }

    public function commit(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }
    }

    public function rollBack(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    public function getRecetasUsuario($idUsuario)
    {
        $stmt = $this->db->prepare(
            "
                SELECT r.*, u.Username, u.FotoPerfil
                FROM Receta r
                JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
                WHERE r.ID_Creador = :id
                ORDER BY r.FechaCreacion DESC
            "
        );
        $stmt->execute(['id' => $idUsuario]);
        return $this->anexarDetallesARecetas($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getRecetaById($id)
    {
        $stmt = $this->db->prepare(
            "
                SELECT r.*, u.Username, u.FotoPerfil
                FROM Receta r
                JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
                WHERE r.ID_Receta = :id
            "
        );
        $stmt->execute(['id' => $id]);
        $receta = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($receta) {
            $receta['ingredientes'] = $this->obtenerIngredientesDeReceta((int)$id);
            $receta['etiquetas'] = $this->obtenerEtiquetasDeReceta((int)$id);
        }

        return $receta;
    }

    public function getRecetaByIdAndUser($id, $userId)
    {
        $stmt = $this->db->prepare(
            "
                SELECT r.*, u.Username, u.FotoPerfil
                FROM Receta r
                JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
                WHERE r.ID_Receta = :id AND r.ID_Creador = :userId
            "
        );
        $stmt->execute(['id' => $id, 'userId' => $userId]);
        $receta = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($receta) {
            $receta['ingredientes'] = $this->obtenerIngredientesDeReceta((int)$id);
            $receta['etiquetas'] = $this->obtenerEtiquetasDeReceta((int)$id);
        }

        return $receta;
    }

    public function crearReceta($idUsuario, $data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO Receta (ID_Creador, Titulo, Descripcion, Imagen, Tiempo, Porciones, EsFit, Pasos, Calorias, Proteina, Carbohidratos, Grasas)
            VALUES (:idCreador, :titulo, :descripcion, :imagen, :tiempo, :porciones, :esFit, :pasos, :calorias, :proteina, :carbohidratos, :grasas)
        ");
        $params = [
            'idCreador' => $idUsuario,
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'imagen' => $data['imagen'] ?? null,
            'tiempo' => $data['tiempo'] ?? null,
            'porciones' => $data['porciones'] ?? null,
            'esFit' => !empty($data['fit']) ? 1 : 0,
            'pasos' => $data['pasos'],
            'calorias' => $data['calorias'] ?? 0,
            'proteina' => $data['proteina'] ?? 0,
            'carbohidratos' => $data['carbohidratos'] ?? 0,
            'grasas' => $data['grasas'] ?? 0
        ];

        try {
            $stmt->execute($params);
        } catch (PDOException $e) {
            if (!$this->esErrorFueraDeRango($e)) {
                throw $e;
            }

            $params['calorias'] = 0;
            $params['proteina'] = 0;
            $params['carbohidratos'] = 0;
            $params['grasas'] = 0;
            $stmt->execute($params);
        }

        return (int)$this->db->lastInsertId();
    }

    public function actualizarReceta($id, $userId, $data)
    {
        $stmt = $this->db->prepare(
            "
                UPDATE Receta
                SET Titulo = :titulo,
                    Descripcion = :descripcion,
                    Imagen = :imagen,
                    Tiempo = :tiempo,
                    Porciones = :porciones,
                    EsFit = :fit,
                    Pasos = :pasos,
                    Calorias = :calorias,
                    Proteina = :proteina,
                    Carbohidratos = :carbohidratos,
                    Grasas = :grasas
                WHERE ID_Receta = :id AND ID_Creador = :userId
            "
        );

        $params = [
            'id' => $id,
            'userId' => $userId,
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'imagen' => $data['imagen'] ?? null,
            'tiempo' => $data['tiempo'] ?? null,
            'porciones' => $data['porciones'] ?? null,
            'fit' => !empty($data['fit']) ? 1 : 0,
            'pasos' => $data['pasos'],
            'calorias' => $data['calorias'] ?? 0,
            'proteina' => $data['proteina'] ?? 0,
            'carbohidratos' => $data['carbohidratos'] ?? 0,
            'grasas' => $data['grasas'] ?? 0
        ];

        try {
            return $stmt->execute($params);
        } catch (PDOException $e) {
            if (!$this->esErrorFueraDeRango($e)) {
                throw $e;
            }

            $params['calorias'] = 0;
            $params['proteina'] = 0;
            $params['carbohidratos'] = 0;
            $params['grasas'] = 0;
            return $stmt->execute($params);
        }
    }

    public function eliminarReceta($id, $userId)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT ID_Receta FROM Receta WHERE ID_Receta = :id AND ID_Creador = :userId");
            $stmt->execute(['id' => $id, 'userId' => $userId]);
            if (!$stmt->fetch()) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("DELETE FROM Coleccion_Receta WHERE ID_Receta = :id");
            $stmt->execute(['id' => $id]);

            $stmt = $this->db->prepare("DELETE FROM Receta_Ingrediente WHERE ID_Receta = :id");
            $stmt->execute(['id' => $id]);

            $stmt = $this->db->prepare("DELETE FROM Etiqueta_Receta WHERE ID_Receta = :id");
            $stmt->execute(['id' => $id]);

            $stmt = $this->db->prepare("DELETE FROM Receta WHERE ID_Receta = :id");
            $stmt->execute(['id' => $id]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

     public function buscarRecetasUsuario($idUsuario, $busqueda)
    {
        $like = '%' . $busqueda . '%';
        $stmt = $this->db->prepare(
            "
                SELECT r.*, u.Username, u.FotoPerfil
                FROM Receta r
                JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
                WHERE r.ID_Creador = :idUsuario
                  AND (r.Titulo LIKE :like1 OR r.Descripcion LIKE :like2)
                ORDER BY r.FechaCreacion DESC
            "
        );
        $stmt->execute([
            'idUsuario' => $idUsuario,
            'like1' => $like,
            'like2' => $like
        ]);
        return $this->anexarDetallesARecetas($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function buscarColeccionesUsuario($idUsuario, $busqueda)
    {
        $like = '%' . $busqueda . '%';
        $stmt = $this->db->prepare(
            "
                SELECT *
                FROM Coleccion
                WHERE ID_Creador = :idUsuario
                  AND Nombre LIKE :like
                ORDER BY ID_Coleccion DESC
            "
        );
        $stmt->execute(['idUsuario' => $idUsuario, 'like' => $like]);
        return $this->anexarPreviewImagenAColecciones($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getIngredientesDisponibles(?int $userId = null): array
    {
        $sql = "
            SELECT ID_Ingrediente, Nombre, Calorias, Proteina, Carbohidratos, Grasas, Verificada, Unidad_Base
            FROM Ingrediente
            WHERE ID_Creador IS NULL
        ";
        $params = [];
        if ($userId !== null) {
            $sql .= " OR ID_Creador = :userId";
            $params['userId'] = $userId;
        }
        $sql .= " ORDER BY Nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $ingredientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Añadir las unidades de conversión a cada ingrediente
        foreach ($ingredientes as &$ing) {
            $ing['unidades'] = $this->getUnidadesIngrediente((int)$ing['ID_Ingrediente']);
        }

        return $ingredientes;
    }

    public function getEtiquetasDisponibles(): array
    {
        $stmt = $this->db->query("SELECT ID_Etiqueta, Nombre FROM Etiqueta ORDER BY Nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerIngredientePorId(int $idIngrediente, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "
                SELECT ID_Ingrediente, Nombre, Calorias, Proteina, Carbohidratos, Grasas, ID_Creador
                FROM Ingrediente
                WHERE ID_Ingrediente = :idIngrediente
                  AND (ID_Creador IS NULL OR ID_Creador = :userId)
                LIMIT 1
            "
        );
        $stmt->execute([
            'idIngrediente' => $idIngrediente,
            'userId' => $userId
        ]);

        $ingrediente = $stmt->fetch(PDO::FETCH_ASSOC);
        return $ingrediente ?: null;
    }

    private function coincidenValoresNutricionales(array $origen, array $destino): bool
    {
        foreach (['calorias', 'proteina', 'carbohidratos', 'grasas'] as $campo) {
            $valorOrigen = round((float)($origen[$campo] ?? 0), 2);
            $valorDestino = round((float)($destino[$campo] ?? 0), 2);

            if (abs($valorOrigen - $valorDestino) > 0.01) {
                return false;
            }
        }

        return true;
    }

     public function obtenerOCrearIngrediente(string $nombre, int $userId, array $nutricion = [], ?string $unidadBase = null): int
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return 0;
        }

        $stmt = $this->db->prepare(
            "
                SELECT ID_Ingrediente, Calorias, Proteina, Carbohidratos, Grasas, ID_Creador
                FROM Ingrediente
                WHERE LOWER(Nombre) = LOWER(:nombre)
                  AND (ID_Creador IS NULL OR ID_Creador = :userIdFiltro)
                ORDER BY CASE WHEN ID_Creador = :userIdOrden THEN 0 ELSE 1 END, ID_Ingrediente ASC
            "
        );
        $stmt->execute([
            'nombre' => $nombre,
            'userIdFiltro' => $userId,
            'userIdOrden' => $userId
        ]);

        $ingredientesExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $nutricionNormalizada = [
            'calorias' => is_numeric($nutricion['calorias'] ?? null) ? (float)$nutricion['calorias'] : 0,
            'proteina' => is_numeric($nutricion['proteina'] ?? null) ? (float)$nutricion['proteina'] : 0,
            'carbohidratos' => is_numeric($nutricion['carbohidratos'] ?? null) ? (float)$nutricion['carbohidratos'] : 0,
            'grasas' => is_numeric($nutricion['grasas'] ?? null) ? (float)$nutricion['grasas'] : 0
        ];

        foreach ($ingredientesExistentes as $ingredienteExistente) {
            if ($this->coincidenValoresNutricionales($nutricionNormalizada, [
                'calorias' => $ingredienteExistente['Calorias'] ?? 0,
                'proteina' => $ingredienteExistente['Proteina'] ?? 0,
                'carbohidratos' => $ingredienteExistente['Carbohidratos'] ?? 0,
                'grasas' => $ingredienteExistente['Grasas'] ?? 0
            ])) {
                return (int)$ingredienteExistente['ID_Ingrediente'];
            }
        }

        foreach ($ingredientesExistentes as $ingredienteExistente) {
            if ((int)($ingredienteExistente['ID_Creador'] ?? 0) === $userId) {
                $stmtActualizar = $this->db->prepare(
                    "
                        UPDATE Ingrediente
                        SET Grasas = :grasas,
                            Calorias = :calorias,
                            Proteina = :proteina,
                            Carbohidratos = :carbohidratos
                        WHERE ID_Ingrediente = :idIngrediente AND ID_Creador = :userId
                    "
                );
                $stmtActualizar->execute([
                    'grasas' => $nutricionNormalizada['grasas'],
                    'calorias' => $nutricionNormalizada['calorias'],
                    'proteina' => $nutricionNormalizada['proteina'],
                    'carbohidratos' => $nutricionNormalizada['carbohidratos'],
                    'idIngrediente' => $ingredienteExistente['ID_Ingrediente'],
                    'userId' => $userId
                ]);

                return (int)$ingredienteExistente['ID_Ingrediente'];
            }
        }

        $stmt = $this->db->prepare(
            "
                INSERT INTO Ingrediente (Nombre, Grasas, Calorias, Proteina, Carbohidratos, ID_Creador, Verificada, Unidad_Base)
                VALUES (:nombre, :grasas, :calorias, :proteina, :carbohidratos, :userId, 0, :unidadBase)
            "
        );
        $stmt->execute([
            'nombre' => $nombre,
            'grasas' => $nutricionNormalizada['grasas'],
            'calorias' => $nutricionNormalizada['calorias'],
            'proteina' => $nutricionNormalizada['proteina'],
            'carbohidratos' => $nutricionNormalizada['carbohidratos'],
            'userId' => $userId,
            'unidadBase' => $unidadBase ?? 'g'   // por defecto g si no se recibe
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function guardarIngredientesDeReceta(int $idReceta, int $userId, array $ingredientes): void
    {
        $stmtDelete = $this->db->prepare("DELETE FROM Receta_Ingrediente WHERE ID_Receta = :idReceta");
        $stmtDelete->execute(['idReceta' => $idReceta]);

        $stmtInsert = $this->db->prepare("
            INSERT INTO Receta_Ingrediente (ID_Ingrediente, ID_Receta, Cantidad)
            VALUES (:idIngrediente, :idReceta, :cantidad)
        ");

        $registrados = [];

        foreach ($ingredientes as $ingrediente) {
            $idIngrediente = (int)($ingrediente['id'] ?? 0);
            $nombreNuevo   = trim((string)($ingrediente['nuevo'] ?? ''));
            $cantidadTexto = trim((string)($ingrediente['cantidad'] ?? ''));
            $nutricion = [
                'calorias'      => is_numeric($ingrediente['calorias'] ?? null) ? (float)$ingrediente['calorias'] : 0,
                'proteina'      => is_numeric($ingrediente['proteina'] ?? null) ? (float)$ingrediente['proteina'] : 0,
                'carbohidratos' => is_numeric($ingrediente['carbohidratos'] ?? null) ? (float)$ingrediente['carbohidratos'] : 0,
                'grasas'        => is_numeric($ingrediente['grasas'] ?? null) ? (float)$ingrediente['grasas'] : 0
            ];

            if ($idIngrediente <= 0 && $nombreNuevo === '') {
                continue;
            }

            if ($idIngrediente > 0) {
                $ingredienteExistente = $this->obtenerIngredientePorId($idIngrediente, $userId);
                if ($ingredienteExistente) {
                    $nombreBase = trim((string)($ingredienteExistente['Nombre'] ?? ''));
                    $nutricionExistente = [
                        'calorias'      => $ingredienteExistente['Calorias'] ?? 0,
                        'proteina'      => $ingredienteExistente['Proteina'] ?? 0,
                        'carbohidratos' => $ingredienteExistente['Carbohidratos'] ?? 0,
                        'grasas'        => $ingredienteExistente['Grasas'] ?? 0
                    ];
                    if (!$this->coincidenValoresNutricionales($nutricion, $nutricionExistente)) {
                        $idIngrediente = $this->obtenerOCrearIngrediente($nombreBase, $userId, $nutricion);
                    }
                }
            }

            if ($idIngrediente <= 0) {
                $unidadBase = trim((string)($ingrediente['unidad_base'] ?? ''));
                if (!in_array($unidadBase, ['g', 'ml'])) {
                    $unidadBase = 'g'; // fallback
                }
                $idIngrediente = $this->obtenerOCrearIngrediente($nombreNuevo, $userId, $nutricion, $unidadBase);
            }

            if ($idIngrediente <= 0 || isset($registrados[$idIngrediente])) {
                continue;
            }

            $stmtInsert->execute([
                'idIngrediente' => $idIngrediente,
                'idReceta'      => $idReceta,
                'cantidad'      => $cantidadTexto !== '' ? $cantidadTexto : null,
            ]);

            $registrados[$idIngrediente] = true;
        }
    }

    public function guardarEtiquetasDeReceta(int $idReceta, array $etiquetas, bool $esFit = false): void
    {
        $idEtiquetaFit = 43;

        $etiquetas = array_map('intval', $etiquetas);

        if ($esFit) {
            $etiquetas[] = $idEtiquetaFit;
        } else {
            $etiquetas = array_filter(
                $etiquetas,
                static fn($idEtiqueta) => (int)$idEtiqueta !== $idEtiquetaFit
            );
        }

        $stmtDelete = $this->db->prepare("DELETE FROM Etiqueta_Receta WHERE ID_Receta = :idReceta");
        $stmtDelete->execute(['idReceta' => $idReceta]);

        $stmtInsert = $this->db->prepare(
            "
                INSERT INTO Etiqueta_Receta (ID_Etiqueta, ID_Receta)
                VALUES (:idEtiqueta, :idReceta)
            "
        );

        $registradas = [];

        foreach ($etiquetas as $idEtiqueta) {
            $idEtiqueta = (int)$idEtiqueta;

            if ($idEtiqueta <= 0 || isset($registradas[$idEtiqueta])) {
                continue;
            }

            $stmtInsert->execute([
                'idEtiqueta' => $idEtiqueta,
                'idReceta' => $idReceta
            ]);

            $registradas[$idEtiqueta] = true;
        }
    }

    public function crearColeccion($idUsuario, $nombre, $esPublica = true)
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return false;
        }

        $stmt = $this->db->prepare(
            "
                INSERT INTO Coleccion (ID_Creador, Nombre, EsPublica)
                VALUES (:idUsuario, :nombre, :esPublica)
            "
        );

        return $stmt->execute([
            'idUsuario' => $idUsuario,
            'nombre' => $nombre,
            'esPublica' => $esPublica ? 1 : 0
        ]);
    }

    public function getColeccionesUsuario($idUsuario)
    {
        $stmt = $this->db->prepare(
            "
                SELECT *
                FROM Coleccion
                WHERE ID_Creador = :idUsuario
                ORDER BY ID_Coleccion DESC
            "
        );
        $stmt->execute(['idUsuario' => $idUsuario]);
        return $this->anexarPreviewImagenAColecciones($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getColeccionByIdAndUser($idColeccion, $userId)
    {
        $stmt = $this->db->prepare(
            "
                SELECT * FROM Coleccion
                WHERE ID_Coleccion = :id AND ID_Creador = :userId
            "
        );
        $stmt->execute(['id' => $idColeccion, 'userId' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function agregarRecetaAColeccion($idReceta, $idColeccion)
    {
        if (!$idReceta || !$idColeccion) {
            return false;
        }

        try {
            $check = $this->db->prepare(
                "
                    SELECT COUNT(*)
                    FROM Coleccion_Receta
                    WHERE ID_Receta = :idReceta AND ID_Coleccion = :idColeccion
                "
            );
            $check->execute(['idReceta' => $idReceta, 'idColeccion' => $idColeccion]);

            if ((int)$check->fetchColumn() > 0) {
                return true;
            }

            $stmt = $this->db->prepare(
                "
                    INSERT INTO Coleccion_Receta (ID_Receta, ID_Coleccion)
                    VALUES (:idReceta, :idColeccion)
                "
            );
            return $stmt->execute(['idReceta' => $idReceta, 'idColeccion' => $idColeccion]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getRecetasDeColeccion($idColeccion)
    {
        $stmt = $this->db->prepare("
            SELECT r.*, u.Username, u.FotoPerfil
            FROM Receta r
            INNER JOIN Coleccion_Receta cr ON r.ID_Receta = cr.ID_Receta
            INNER JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
            WHERE cr.ID_Coleccion = :idColeccion
            ORDER BY r.FechaCreacion DESC
        ");
        $stmt->execute(['idColeccion' => $idColeccion]);
        return $this->anexarDetallesARecetas($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function eliminarRecetaDeColeccion($idReceta, $idColeccion)
    {
        $stmt = $this->db->prepare("
            DELETE FROM Coleccion_Receta
            WHERE ID_Receta = :idReceta AND ID_Coleccion = :idColeccion
        ");
        return $stmt->execute(['idReceta' => $idReceta, 'idColeccion' => $idColeccion]);
    }

    public function getColeccionesDeReceta(int $idReceta, int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT cr.ID_Coleccion
            FROM Coleccion_Receta cr
            INNER JOIN Coleccion c ON c.ID_Coleccion = cr.ID_Coleccion
            WHERE cr.ID_Receta = :idReceta AND c.ID_Creador = :userId
        ");
        $stmt->execute(['idReceta' => $idReceta, 'userId' => $userId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function renombrarColeccion($idColeccion, $userId, $nuevoNombre): bool
    {
        $nuevoNombre = trim($nuevoNombre);
        if ($nuevoNombre === '') {
            return false;
        }
        $stmt = $this->db->prepare("
            UPDATE Coleccion
            SET Nombre = :nombre
            WHERE ID_Coleccion = :id AND ID_Creador = :userId
        ");
        return $stmt->execute(['nombre' => $nuevoNombre, 'id' => $idColeccion, 'userId' => $userId]);
    }

    public function eliminarColeccion($idColeccion)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("DELETE FROM Coleccion_Receta WHERE ID_Coleccion = :idColeccion");
            $stmt->execute(['idColeccion' => $idColeccion]);
            $stmt = $this->db->prepare("DELETE FROM Coleccion WHERE ID_Coleccion = :idColeccion");
            $stmt->execute(['idColeccion' => $idColeccion]);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function guardarEnCols($idReceta, $userId, $cols)
    {
        $idReceta = (int)$idReceta;
        $cols = array_values(array_unique(array_filter(array_map('intval', (array)$cols), static fn($id) => $id > 0)));

        if ($idReceta <= 0) {
            return ['ok' => false, 'msg' => 'La receta no es valida'];
        }

        if (empty($cols)) {
            return ['ok' => false, 'msg' => 'No se selecciono ninguna coleccion'];
        }

        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $stmt = $this->db->prepare("SELECT ID_Coleccion FROM Coleccion WHERE ID_Creador = ? AND ID_Coleccion IN ($placeholders)");
        $params = array_merge([$userId], $cols);
        $stmt->execute($params);
        $validas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $invalidas = array_diff($cols, $validas);

        if (!empty($invalidas)) {
            return ['ok' => false, 'msg' => 'Alguna coleccion no te pertenece'];
        }

        $insertados = 0;
        foreach ($validas as $idCol) {
            $antes = $this->db->prepare("SELECT COUNT(*) FROM Coleccion_Receta WHERE ID_Receta = ? AND ID_Coleccion = ?");
            $antes->execute([$idReceta, $idCol]);
            $yaExistia = (int)$antes->fetchColumn() > 0;

            if ($this->agregarRecetaAColeccion((int)$idReceta, (int)$idCol) && !$yaExistia) {
                $insertados++;
                continue;
            }
        }

        return ['ok' => true, 'insertados' => $insertados];
    }
}
