<?php
require_once __DIR__ . '/../../../core/db.php';
require_once __DIR__ . '/../../../core/logger.php';
class BusquedaModel
{
    private $db;
    public function __construct(){$this->db = Conexion::conectar();}
    public function buscarRecetas($busqueda = '', $etiquetasFiltro = [], $limit = 12, $offset = 0, $userId = null, $esfit = false)
    {
        $sql =
        "SELECT
        r.*, u.Nombre, u.FotoPerfil,
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
        if ($esfit) 
        {
            $sql .= " AND EXISTS (
                SELECT 1
                FROM Etiqueta_Receta erf
                WHERE erf.ID_Receta = r.ID_Receta
                AND erf.ID_Etiqueta = 43
            )";
        }
        if ($userId) 
        {
            $sql .= " AND (u.CuentaPublica = 1 OR u.ID_Usuario = ? OR EXISTS (SELECT 1 FROM Usuario_Seguidor WHERE ID_Usuario = u.ID_Usuario AND ID_Seguidor = ?))";
            $params[] = $userId; // para u.ID_Usuario = ?
            $params[] = $userId; // para ID_Seguidor = ?
        } 
        else {$sql .= " AND u.CuentaPublica = 1";}
        if (!empty($busqueda)) 
        {
            $sql .= 
            " AND (u.Nombre LIKE ? OR r.Titulo LIKE ? OR r.ID_Receta IN (
            SELECT ri.ID_Receta FROM Receta_Ingrediente ri
            JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente
            WHERE i.Nombre LIKE ?))";
            $term = "%$busqueda%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }
        if (!empty($etiquetasFiltro)) 
        {
            $tags = is_array($etiquetasFiltro) ? $etiquetasFiltro : [$etiquetasFiltro];
            $placeholders = implode(',', array_fill(0, count($tags), '?'));
            $sql .= 
            " AND r.ID_Receta IN (
            SELECT er2.ID_Receta FROM Etiqueta_Receta er2
            JOIN Etiqueta e2 ON er2.ID_Etiqueta = e2.ID_Etiqueta
            WHERE e2.Nombre IN ($placeholders)
            GROUP BY er2.ID_Receta
            HAVING COUNT(DISTINCT e2.Nombre) = ?)";
            foreach ($tags as $tag) $params[] = $tag;
            $params[] = count($tags);
        }
        $sql .= " ORDER BY r.FechaCreacion DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getRecetasRecomendadas($limit = 12, $userId = null)
    {
        $sql =
        "SELECT
        r.*, u.Nombre, u.FotoPerfil,
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
        if ($userId) 
        {
            $sql .= " AND (u.CuentaPublica = 1 OR u.ID_Usuario = ? OR EXISTS (SELECT 1 FROM Usuario_Seguidor WHERE ID_Usuario = u.ID_Usuario AND ID_Seguidor = ?))";
            $params[] = $userId; // para u.ID_Usuario = ?
            $params[] = $userId; // para ID_Seguidor = ?
        } 
        else {$sql .= " AND u.CuentaPublica = 1";}
        $sql .= " ORDER BY RAND(UNIX_TIMESTAMP(CURDATE())) LIMIT ?";
        $params[] = $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getEtiquetasDisponibles(){return $this->db->query("SELECT Nombre FROM Etiqueta ORDER BY Nombre ASC")->fetchAll(PDO::FETCH_ASSOC);}
    //Buscar usuario por nombre (para búsqueda con @).
    public function getUserByUsername(string $username): ?array
    {
        $username = trim($username);

        if ($username === '') {
            return null;
        }

        $likePattern = '%' . $username . '%';

        try {
            $sql = 
            "SELECT 
                u.ID_Usuario,
                u.Nombre,
                u.FotoPerfil,
                (SELECT COUNT(*) FROM Receta WHERE ID_Creador = u.ID_Usuario) AS TotalRecetas,
                (SELECT COUNT(*) FROM Usuario_Seguidor WHERE ID_Usuario = u.ID_Usuario) AS Seguidores
            FROM Usuario u
            WHERE u.Nombre LIKE ?
            ORDER BY 
                CASE 
                    WHEN u.Nombre = ? THEN 0
                    WHEN u.Nombre LIKE ? THEN 1
                    ELSE 2
                END,
                u.Nombre ASC
            LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $likePattern,
                $username,
                $username . '%'
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            Logger::info(
                'BusquedaModel',
                'getUserByUsername',
                'BD',
                "Búsqueda de usuario '$username' con LIKE '$likePattern' -> " . ($user ? 'encontrado' : 'no encontrado')
            );

            if (!$user) {
                return null;
            }

            $user['TotalRecetas'] = (int)($user['TotalRecetas'] ?? 0);
            $user['Seguidores'] = (int)($user['Seguidores'] ?? 0);

            return $user;
        } catch (\PDOException $e) {
            Logger::error(
                'BusquedaModel',
                'getUserByUsername',
                'BD',
                $e->getCode(),
                $e->getMessage()
            );

            return null;
        }
    }
}