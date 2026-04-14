<?php
require_once __DIR__ . '/../../../core/db.php';

class FeedModel
{
    private $db;

    public function __construct(){$this->db = Conexion::conectar();}

    public function getPostsFiltrados($busqueda = null, $etiquetasFiltro = [], $limit = 5, $offset = 0, $userId = null)
    {
        $sql = "SELECT 
                    r.*, u.Username, u.Nombre,
                    (SELECT COUNT(*) FROM Comentario WHERE ID_Receta = r.ID_Receta) as TotalComentarios,
                    (SELECT GROUP_CONCAT(e.Nombre SEPARATOR ',')
                     FROM Etiqueta_Receta er
                     JOIN Etiqueta e ON er.ID_Etiqueta = e.ID_Etiqueta
                     WHERE er.ID_Receta = r.ID_Receta) as EtiquetasNombres,
                    (SELECT COUNT(*) FROM Receta_Megusta WHERE ID_Receta = r.ID_Receta AND ID_Usuario = ?) as DioLike
                FROM Receta r
                LEFT JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
                WHERE r.EsPublica = 1";

        $params = [$userId ?? 0];

        // Búsqueda por texto
        if (!empty($busqueda)) 
        {
            $sql .= 
            " AND (u.Username LIKE ? OR r.Titulo LIKE ? OR r.ID_Receta IN (
            SELECT ri.ID_Receta FROM Receta_Ingrediente ri
            JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente
            WHERE i.Nombre LIKE ?))";
        
            $term = "%$busqueda%";
            $params[] = $term; 
            $params[] = $term; 
            $params[] = $term; 
        }

        // Filtro por etiquetas 
        if (!empty($etiquetasFiltro)) 
        {
            $tags = is_array($etiquetasFiltro) ? $etiquetasFiltro : [$etiquetasFiltro];
            $placeholders = implode(',', array_fill(0, count($tags), '?'));
            $sql .= " AND r.ID_Receta IN (
                        SELECT er2.ID_Receta FROM Etiqueta_Receta er2
                        JOIN Etiqueta e2 ON er2.ID_Etiqueta = e2.ID_Etiqueta
                        WHERE e2.Nombre IN ($placeholders)
                        GROUP BY er2.ID_Receta
                        HAVING COUNT(DISTINCT e2.Nombre) = ?)";

            foreach ($tags as $tag) {$params[] = $tag;}
            $params[] = count($tags);
        }

        $sql .= " ORDER BY r.FechaCreacion DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEtiquetasDisponibles(){return $this->db->query("SELECT Nombre FROM Etiqueta ORDER BY Nombre ASC")->fetchAll(PDO::FETCH_ASSOC);}

    public function getUserConfig($userId)
    {
        $stmt = $this->db->prepare("SELECT ModoOscuro, ModoFit, NotificacionOn, CuentaPublica FROM Usuario WHERE ID_Usuario = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function toggleLike($idReceta, $idUsuario)
    {
        // Verifica Like
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM Receta_Megusta WHERE ID_Receta = ? AND ID_Usuario = ?");
        $stmt->execute([$idReceta, $idUsuario]);
        $existe = $stmt->fetchColumn();

        if ($existe > 0) 
        {
            // Quitar Like
            $this->db->prepare("DELETE FROM Receta_Megusta WHERE ID_Receta = ? AND ID_Usuario = ?")->execute([$idReceta, $idUsuario]);
            $this->db->prepare("UPDATE Receta SET Megustas = Megustas - 1 WHERE ID_Receta = ?")->execute([$idReceta]);
            $accion = 'removed';
        } else 
        {
            // Poner Like
            $this->db->prepare("INSERT INTO Receta_Megusta (ID_Receta, ID_Usuario) VALUES (?, ?)")->execute([$idReceta, $idUsuario]);
            $this->db->prepare("UPDATE Receta SET Megustas = Megustas + 1 WHERE ID_Receta = ?")->execute([$idReceta]);
            $accion = 'added';
            
        }
        // Contador
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
        $stmt = $this->db->prepare("SELECT c.*, u.Username FROM Comentario c JOIN Usuario u ON c.ID_Creador = u.ID_Usuario WHERE c.ID_Receta = ? ORDER BY c.Fecha DESC");
        $stmt->execute([$idReceta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   public function agregarComentario($idReceta, $idUsuario, $texto)
    {
        $stmt = $this->db->prepare
        ("
            INSERT INTO Comentario (ID_Receta, ID_Creador, Descripcion) 
            VALUES (?, ?, ?)
        ");

        if ($stmt->execute([$idReceta, $idUsuario, $texto])) {return $this->db->lastInsertId();}

        return false; 
    }
    public function getNotificaciones($userId)
    {
        $sql = "SELECT n.*, u.Username 
                FROM Notificacion n
                LEFT JOIN Usuario u 
                    ON n.ID_Usuario_Origen = u.ID_Usuario
                WHERE n.ID_Usuario_Destino = ?
                ORDER BY n.Fecha DESC
                LIMIT 15";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function existeNotificacion($destino, $origen, $tipo, $idReceta = null)
    {
        $sql = "SELECT COUNT(*) FROM Notificacion 
                WHERE ID_Usuario_Destino = ?
                AND ID_Usuario_Origen = ?
                AND Tipo = ?";
        $params = [$destino, $origen, $tipo];
        if ($idReceta !== null) 
        {
            $sql .= " AND ID_Receta = ?";
            $params[] = $idReceta;
        } 
        else { $sql .= " AND ID_Receta IS NULL";}

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    public function contarNoLeidas($userId)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM Notificacion 
                WHERE ID_Usuario_Destino = ? AND Leida = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function marcarLeidas($userId)
    {
        $sql = "UPDATE Notificacion 
                SET Leida = 1 
                WHERE ID_Usuario_Destino = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }

    public function crearNotificacion($destino, $origen, $mensaje, $tipo, $idReceta = null, $idComentario = null)
    {
        $sql = "INSERT INTO Notificacion 
                (ID_Usuario_Destino, ID_Usuario_Origen, Mensaje, Tipo, ID_Receta, ID_Comentario)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        error_log("DEBUG: like - dueno=$destino, userId=$origen");
        return $stmt->execute(
        [
            $destino,
            $origen,
            $mensaje,
            $tipo,
            $idReceta,
            $idComentario
        ]);
    }
    public function buscarIngredientes($query, $userId)
    {
        $sql = "SELECT ID_Ingrediente, Nombre 
                FROM Ingrediente
                WHERE (Estado = 'aprobado' OR ID_Creador = ?)
                AND Nombre LIKE ?
                ORDER BY Nombre ASC
                LIMIT 10";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, "%$query%"]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function eliminarNotificacion($idNotificacion, $userId)
    {
        $stmt = $this->db->prepare("DELETE FROM notificaciones WHERE ID_Notificacion = ? AND ID_Usuario = ?");
        $stmt->execute([$idNotificacion, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Eliminar todas las notificaciones del usuario
     */
    public function limpiarNotificaciones($userId)
    {
        $stmt = $this->db->prepare("DELETE FROM notificaciones WHERE ID_Usuario = ?");
        $stmt->execute([$userId]);
        return $stmt->rowCount() >= 0; // Siempre es exitoso aunque no hubiera notificaciones
    }
}