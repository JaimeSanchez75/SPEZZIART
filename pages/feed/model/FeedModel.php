<?php
require_once __DIR__ . '/../../../core/db.php';
class FeedModel
{
    private $db;
    public function __construct(){$this->db = Conexion::conectar();}
    /**
     * Obtiene recetas filtradas con orden configurable
     * @param string|null $busqueda Término de búsqueda
     * @param array $etiquetasFiltro Lista de nombres de etiquetas
     * @param int $limit Límite de resultados
     * @param int $offset Desplazamiento
     * @param int|null $userId ID del usuario autenticado (opcional)
     * @param string $orden 'recientes' o 'populares'
     */
    public function getPostsFiltrados($busqueda = null, $etiquetasFiltro = [], $limit = 5, $offset = 0, $userId = null, $orden = 'recientes')
    {
        // Subconsultas para calcular nutrición desde los ingredientes
        $nutriCalorias = "(SELECT COALESCE(SUM(ri.Cantidad * i.Calorias / 100), 0) FROM Receta_Ingrediente ri JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente WHERE ri.ID_Receta = r.ID_Receta)";
        $nutriProteina = "(SELECT COALESCE(SUM(ri.Cantidad * i.Proteina / 100), 0) FROM Receta_Ingrediente ri JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente WHERE ri.ID_Receta = r.ID_Receta)";
        $nutriCarbs    = "(SELECT COALESCE(SUM(ri.Cantidad * i.Carbohidratos / 100), 0) FROM Receta_Ingrediente ri JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente WHERE ri.ID_Receta = r.ID_Receta)";
        $nutriGrasas   = "(SELECT COALESCE(SUM(ri.Cantidad * i.Grasas / 100), 0) FROM Receta_Ingrediente ri JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente WHERE ri.ID_Receta = r.ID_Receta)";

        $sql = "SELECT 
            r.ID_Receta, r.ID_Creador, r.Titulo, r.Descripcion, r.Imagen,
            r.EsPublica, r.Tiempo, r.Porciones, r.FechaCreacion, r.Megustas, r.EsFit,
            $nutriCalorias as Calorias,
            $nutriProteina as Proteina,
            $nutriCarbs as Carbohidratos,
            $nutriGrasas as Grasas,
            u.Username, u.Nombre, u.FotoPerfil,
            (SELECT COUNT(*) FROM Comentario WHERE ID_Receta = r.ID_Receta) as TotalComentarios,
            (SELECT GROUP_CONCAT(e.Nombre SEPARATOR ',')
             FROM Etiqueta_Receta er
             JOIN Etiqueta e ON er.ID_Etiqueta = e.ID_Etiqueta
             WHERE er.ID_Receta = r.ID_Receta) as EtiquetasNombres,
            (SELECT COUNT(*) FROM Receta_Megusta WHERE ID_Receta = r.ID_Receta AND ID_Usuario = ?) as DioLike";

        $scoreFormula = '';
        if ($orden === 'populares') {
            $scoreFormula = "((COALESCE(r.Megustas, 0) * 2 + COALESCE(c.total_comentarios, 0)) / POW(TIMESTAMPDIFF(HOUR, r.FechaCreacion, NOW()) + 2, 1.5))";
        }

        $sql .= $scoreFormula . " FROM Receta r
        LEFT JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
        LEFT JOIN (
            SELECT ID_Receta, COUNT(*) as total_comentarios 
            FROM Comentario 
            GROUP BY ID_Receta
        ) c ON c.ID_Receta = r.ID_Receta
        WHERE r.EsPublica = 1";

        $params = [$userId ?? 0];
        if ($userId) {
            $sql .= " AND (u.CuentaPublica = 1 OR u.ID_Usuario = ? OR EXISTS (SELECT 1 FROM Usuario_Seguidor WHERE ID_Usuario = u.ID_Usuario AND ID_Seguidor = ?))";
            $params[] = $userId;
            $params[] = $userId;
        } else {
            $sql .= " AND u.CuentaPublica = 1";
        }

        if (!empty($busqueda)) {
            $sql .= " AND (u.Username LIKE ? OR r.Titulo LIKE ? OR r.ID_Receta IN (
                        SELECT ri.ID_Receta FROM Receta_Ingrediente ri
                        JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente
                        WHERE i.Nombre LIKE ?))";
            $term = "%$busqueda%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($etiquetasFiltro)) {
            $tags = is_array($etiquetasFiltro) ? $etiquetasFiltro : [$etiquetasFiltro];
            $placeholders = implode(',', array_fill(0, count($tags), '?'));
            $sql .= " AND r.ID_Receta IN (
                        SELECT er2.ID_Receta FROM Etiqueta_Receta er2
                        JOIN Etiqueta e2 ON er2.ID_Etiqueta = e2.ID_Etiqueta
                        WHERE e2.Nombre IN ($placeholders)
                        GROUP BY er2.ID_Receta
                        HAVING COUNT(DISTINCT e2.Nombre) = ?)";
            foreach ($tags as $tag) { $params[] = $tag; }
            $params[] = count($tags);
        }

        if ($orden === 'populares') {
            $sql .= " ORDER BY (score + (RAND() * 0.5)) DESC, r.FechaCreacion DESC";
        } else {
            $sql .= " ORDER BY r.FechaCreacion DESC";
        }
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getPostsFiltradosCursor($busqueda = null, $etiquetasFiltro = [], $limit = 5, $lastScore = null, $lastId = null, $userId = null, $orden = 'populares', $seed = null)
    {
        $scoreFormula = "((r.Megustas * 2 + COALESCE(c.total_comentarios, 0)) / POW(TIMESTAMPDIFF(HOUR, r.FechaCreacion, NOW()) + 2, 1.5))";
        if ($seed !== null) {
            $randomPart = "RAND($seed)";
        } else {
            $randomPart = "((r.ID_Receta * 2654435761) & 2147483647) % 1000000 / 1000000.0";
        }
        $finalScore = "($scoreFormula + $randomPart * 0.5)";

        $nutriCalorias = "(SELECT COALESCE(SUM(ri.Cantidad * i.Calorias / 100), 0) FROM Receta_Ingrediente ri JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente WHERE ri.ID_Receta = r.ID_Receta)";
        $nutriProteina = "(SELECT COALESCE(SUM(ri.Cantidad * i.Proteina / 100), 0) FROM Receta_Ingrediente ri JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente WHERE ri.ID_Receta = r.ID_Receta)";
        $nutriCarbs    = "(SELECT COALESCE(SUM(ri.Cantidad * i.Carbohidratos / 100), 0) FROM Receta_Ingrediente ri JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente WHERE ri.ID_Receta = r.ID_Receta)";
        $nutriGrasas   = "(SELECT COALESCE(SUM(ri.Cantidad * i.Grasas / 100), 0) FROM Receta_Ingrediente ri JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente WHERE ri.ID_Receta = r.ID_Receta)";

        $sql = "SELECT 
            r.ID_Receta, r.ID_Creador, r.Titulo, r.Descripcion, r.Imagen,
            r.EsPublica, r.Tiempo, r.Porciones, r.FechaCreacion, r.Megustas, r.EsFit,
            $nutriCalorias as Calorias,
            $nutriProteina as Proteina,
            $nutriCarbs as Carbohidratos,
            $nutriGrasas as Grasas,
            u.Username, u.Nombre, u.FotoPerfil,
            (SELECT COUNT(*) FROM Comentario WHERE ID_Receta = r.ID_Receta) as TotalComentarios,
            (SELECT GROUP_CONCAT(e.Nombre SEPARATOR ',')
             FROM Etiqueta_Receta er
             JOIN Etiqueta e ON er.ID_Etiqueta = e.ID_Etiqueta
             WHERE er.ID_Receta = r.ID_Receta) as EtiquetasNombres,
            (SELECT COUNT(*) FROM Receta_Megusta WHERE ID_Receta = r.ID_Receta AND ID_Usuario = ?) as DioLike,
            $scoreFormula as base_score,
            $finalScore as final_score
        FROM Receta r
        LEFT JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
        LEFT JOIN (
            SELECT ID_Receta, COUNT(*) as total_comentarios 
            FROM Comentario 
            GROUP BY ID_Receta
        ) c ON c.ID_Receta = r.ID_Receta
        WHERE r.EsPublica = 1";

        $params = [$userId ?? 0];
        if ($userId) {
            $sql .= " AND (u.CuentaPublica = 1 OR u.ID_Usuario = ? OR EXISTS (SELECT 1 FROM Usuario_Seguidor WHERE ID_Usuario = u.ID_Usuario AND ID_Seguidor = ?))";
            $params[] = $userId;
            $params[] = $userId;
        } else {
            $sql .= " AND u.CuentaPublica = 1";
        }

        if (!empty($busqueda)) {
            $sql .= " AND (u.Username LIKE ? OR r.Titulo LIKE ? OR r.ID_Receta IN (
                        SELECT ri.ID_Receta FROM Receta_Ingrediente ri
                        JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente
                        WHERE i.Nombre LIKE ?))";
            $term = "%$busqueda%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($etiquetasFiltro)) {
            $tags = is_array($etiquetasFiltro) ? $etiquetasFiltro : [$etiquetasFiltro];
            $placeholders = implode(',', array_fill(0, count($tags), '?'));
            $sql .= " AND r.ID_Receta IN (
                        SELECT er2.ID_Receta FROM Etiqueta_Receta er2
                        JOIN Etiqueta e2 ON er2.ID_Etiqueta = e2.ID_Etiqueta
                        WHERE e2.Nombre IN ($placeholders)
                        GROUP BY er2.ID_Receta
                        HAVING COUNT(DISTINCT e2.Nombre) = ?)";
            foreach ($tags as $tag) { $params[] = $tag; }
            $params[] = count($tags);
        }

        if ($lastScore !== null && $lastId !== null) {
            $sql .= " AND ($finalScore < ? OR ($finalScore = ? AND r.ID_Receta < ?))";
            $params[] = floatval($lastScore);
            $params[] = floatval($lastScore);
            $params[] = intval($lastId);
        }
        $sql .= " ORDER BY final_score DESC, r.ID_Receta DESC";
        $sql .= " LIMIT ?";
        $params[] = (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getPostsFiltradosConSemilla($busqueda = null, $etiquetasFiltro = [], $limit = 5, $offset = 0, $userId = null, $orden = 'populares', $seed = 1)
    {
        $scoreFormula = "((COALESCE(r.Megustas, 0) * 2 + COALESCE(c.total_comentarios, 0)) / POW(TIMESTAMPDIFF(HOUR, r.FechaCreacion, NOW()) + 2, 1.5))";
        $randomPart = "RAND($seed) * 0.5";
        $finalScore = "($scoreFormula + $randomPart)";

        $nutriCalorias = "(SELECT COALESCE(SUM(ri.Cantidad * i.Calorias / 100), 0) FROM Receta_Ingrediente ri JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente WHERE ri.ID_Receta = r.ID_Receta)";
        $nutriProteina = "(SELECT COALESCE(SUM(ri.Cantidad * i.Proteina / 100), 0) FROM Receta_Ingrediente ri JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente WHERE ri.ID_Receta = r.ID_Receta)";
        $nutriCarbs    = "(SELECT COALESCE(SUM(ri.Cantidad * i.Carbohidratos / 100), 0) FROM Receta_Ingrediente ri JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente WHERE ri.ID_Receta = r.ID_Receta)";
        $nutriGrasas   = "(SELECT COALESCE(SUM(ri.Cantidad * i.Grasas / 100), 0) FROM Receta_Ingrediente ri JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente WHERE ri.ID_Receta = r.ID_Receta)";

        $sql = "SELECT 
            r.ID_Receta, r.ID_Creador, r.Titulo, r.Descripcion, r.Imagen,
            r.EsPublica, r.Tiempo, r.Porciones, r.FechaCreacion, r.Megustas, r.EsFit,
            $nutriCalorias as Calorias,
            $nutriProteina as Proteina,
            $nutriCarbs as Carbohidratos,
            $nutriGrasas as Grasas,
            u.Username, u.Nombre, u.FotoPerfil,
            (SELECT COUNT(*) FROM Comentario WHERE ID_Receta = r.ID_Receta) as TotalComentarios,
            (SELECT GROUP_CONCAT(e.Nombre SEPARATOR ',')
             FROM Etiqueta_Receta er
             JOIN Etiqueta e ON er.ID_Etiqueta = e.ID_Etiqueta
             WHERE er.ID_Receta = r.ID_Receta) as EtiquetasNombres,
            (SELECT COUNT(*) FROM Receta_Megusta WHERE ID_Receta = r.ID_Receta AND ID_Usuario = ?) as DioLike,
            $finalScore as final_score
        FROM Receta r
        LEFT JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
        LEFT JOIN (
            SELECT ID_Receta, COUNT(*) as total_comentarios 
            FROM Comentario 
            GROUP BY ID_Receta
        ) c ON c.ID_Receta = r.ID_Receta
        WHERE r.EsPublica = 1";

        $params = [$userId ?? 0];
        if ($userId) {
            $sql .= " AND (u.CuentaPublica = 1 OR u.ID_Usuario = ? OR EXISTS (SELECT 1 FROM Usuario_Seguidor WHERE ID_Usuario = u.ID_Usuario AND ID_Seguidor = ?))";
            $params[] = $userId;
            $params[] = $userId;
        } else {
            $sql .= " AND u.CuentaPublica = 1";
        }

        if (!empty($busqueda)) {
            $sql .= " AND (u.Username LIKE ? OR r.Titulo LIKE ? OR r.ID_Receta IN (
                        SELECT ri.ID_Receta FROM Receta_Ingrediente ri
                        JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente
                        WHERE i.Nombre LIKE ?))";
            $term = "%$busqueda%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($etiquetasFiltro)) {
            $tags = is_array($etiquetasFiltro) ? $etiquetasFiltro : [$etiquetasFiltro];
            $placeholders = implode(',', array_fill(0, count($tags), '?'));
            $sql .= " AND r.ID_Receta IN (
                        SELECT er2.ID_Receta FROM Etiqueta_Receta er2
                        JOIN Etiqueta e2 ON er2.ID_Etiqueta = e2.ID_Etiqueta
                        WHERE e2.Nombre IN ($placeholders)
                        GROUP BY er2.ID_Receta
                        HAVING COUNT(DISTINCT e2.Nombre) = ?)";
            foreach ($tags as $tag) { $params[] = $tag; }
            $params[] = count($tags);
        }

        $sql .= " ORDER BY final_score DESC, r.ID_Receta DESC";
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getEtiquetasDisponibles(){return $this->db->query("SELECT Nombre FROM Etiqueta ORDER BY Nombre ASC")->fetchAll(PDO::FETCH_ASSOC);}
    public function getUserConfig($userId)
    {
        $stmt = $this->db->prepare("SELECT Tema, ModoFit, NotificacionOn, CuentaPublica FROM Usuario WHERE ID_Usuario = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getFotoUsuarioActual($userId)
    {
        if ($userId && empty($_SESSION['user']['FotoPerfil'])) 
        {
            $stmt = $this->db->prepare("SELECT FotoPerfil FROM Usuario WHERE ID_Usuario = ?");
            $stmt->execute([$userId]);
            $foto = $stmt->fetchColumn();
            return $foto;
        }
        return null;
    }
    public function toggleLike($idReceta, $idUsuario)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM Receta_Megusta WHERE ID_Receta = ? AND ID_Usuario = ?");
        $stmt->execute([$idReceta, $idUsuario]);
        $existe = $stmt->fetchColumn();
        if ($existe > 0) 
        {
            $this->db->prepare("DELETE FROM Receta_Megusta WHERE ID_Receta = ? AND ID_Usuario = ?")->execute([$idReceta, $idUsuario]);
            $this->db->prepare("UPDATE Receta SET Megustas = Megustas - 1 WHERE ID_Receta = ?")->execute([$idReceta]);
            $accion = 'removed';
        } 
        else 
        {
            $this->db->prepare("INSERT INTO Receta_Megusta (ID_Receta, ID_Usuario) VALUES (?, ?)")->execute([$idReceta, $idUsuario]);
            $this->db->prepare("UPDATE Receta SET Megustas = Megustas + 1 WHERE ID_Receta = ?")->execute([$idReceta]);
            $accion = 'added';
        }
        $stmt = $this->db->prepare("SELECT Megustas FROM Receta WHERE ID_Receta = ?");
        $stmt->execute([$idReceta]);
        return ['accion' => $accion, 'likes' => $stmt->fetchColumn()];
    }
    public function getCreadorReceta($idReceta)
    {
        $stmt = $this->db->prepare("SELECT ID_Creador FROM Receta WHERE ID_Receta = ?");
        $stmt->execute([$idReceta]);
        return $stmt->fetchColumn();
    }
    public function getComentarios($idReceta)
    {
        $stmt = $this->db->prepare("SELECT c.*, u.Nombre, u.FotoPerfil FROM Comentario c JOIN Usuario u ON c.ID_Creador = u.ID_Usuario WHERE c.ID_Receta = ? ORDER BY c.Fecha DESC");
        $stmt->execute([$idReceta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function agregarComentario($idReceta, $idUsuario, $texto)
    {
        $stmt = $this->db->prepare("INSERT INTO Comentario (ID_Receta, ID_Creador, Descripcion) VALUES (?, ?, ?)");
        if ($stmt->execute([$idReceta, $idUsuario, $texto])) {return $this->db->lastInsertId();}
        return false;
    }
    public function getNotificaciones($userId)
    {
        $sql = 
        "SELECT n.*, u.Nombre, u.FotoPerfil
        FROM Notificacion n
        LEFT JOIN Usuario u ON n.ID_Usuario_Origen = u.ID_Usuario
        WHERE n.ID_Usuario_Destino = ? and n.EsAdmin = 0
        ORDER BY n.Fecha DESC
        LIMIT 15";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function existeNotificacion($destino, $origen, $tipo, $idReceta = null)
    {
        $sql = 
        "SELECT COUNT(*) FROM Notificacion WHERE ID_Usuario_Destino = ? AND ID_Usuario_Origen = ? AND Tipo = ? AND EsAdmin = 0";
        $params = [$destino, $origen, $tipo];
        if ($idReceta !== null) 
        {
            $sql .= " AND ID_Receta = ?";
            $params[] = $idReceta;
        } 
        else {$sql .= " AND ID_Receta IS NULL";}
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    public function contarNoLeidas($userId)
    {
        $sql = "SELECT COUNT(*) as total FROM Notificacion WHERE ID_Usuario_Destino = ? AND Leida = 0 AND EsAdmin = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    public function marcarLeidas($userId)
    {
        $sql = "UPDATE Notificacion SET Leida = 1 WHERE ID_Usuario_Destino = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
    public function crearNotificacion($destino, $origen, $mensaje, $tipo, $idReceta = null, $idComentario = null)
    {
        $sql = "INSERT INTO Notificacion (ID_Usuario_Destino, ID_Usuario_Origen, Mensaje, Tipo, ID_Receta, ID_Comentario) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$destino, $origen, $mensaje, $tipo, $idReceta, $idComentario]);
    }
    public function buscarIngredientes($query, $userId)
    {
        $sql = 
        "SELECT ID_Ingrediente, Nombre 
        FROM Ingrediente
        WHERE (Estado = 'aprobado' OR ID_Creador = ?) AND Nombre LIKE ?
        ORDER BY Nombre ASC LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, "%$query%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function eliminarNotificacion($idNotificacion, $userId)
    {
        $stmt = $this->db->prepare("DELETE FROM Notificacion WHERE ID_Notificacion = ? AND ID_Usuario_Destino = ?");
        $stmt->execute([$idNotificacion, $userId]);
        return $stmt->rowCount() > 0;
    }
    public function limpiarNotificaciones($userId)
    {
        $stmt = $this->db->prepare("DELETE FROM Notificacion WHERE ID_Usuario_Destino = ?");
        $stmt->execute([$userId]);
        return $stmt->rowCount() >= 0;
    }
    public function eliminarComentario($idComentario, $idUsuario)
    {
        $this->db->beginTransaction();
        try 
        {
            $stmt = $this->db->prepare("SELECT ID_Receta FROM Comentario WHERE ID_Comentario = ? AND ID_Creador = ?");
            $stmt->execute([$idComentario, $idUsuario]);
            $idReceta = $stmt->fetchColumn();
            if (!$idReceta) 
            {
                $this->db->rollBack();
                return false;
            }
            $stmt = $this->db->prepare("DELETE FROM Notificacion WHERE ID_Comentario = ?");
            $stmt->execute([$idComentario]);
            $stmt = $this->db->prepare("DELETE FROM Comentario WHERE ID_Comentario = ? AND ID_Creador = ?");
            $stmt->execute([$idComentario, $idUsuario]);
            $this->db->commit();
            return 
            [
                'success' => $stmt->rowCount() > 0,
                'id_receta' => $idReceta
            ];
        } 
        catch (Exception $e) 
        {
            $this->db->rollBack();
            return false;
        }
    }
}