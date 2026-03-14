<?php
require_once __DIR__ . '/../../../core/db.php';

class FeedModel {
    public function getPosts($etiquetasFiltro = [], $busqueda = null) 
    { 
        $db = Conexion::conectar();
        
        $sql = "SELECT 
                    r.ID_Receta, r.Titulo, r.Descripcion, r.Imagen, 
                    r.FechaCreacion, u.Username, r.ID_Creador,
                    (SELECT GROUP_CONCAT(e.Nombre) 
                    FROM Etiqueta_Receta er 
                    JOIN Etiqueta e ON er.ID_Etiqueta = e.ID_Etiqueta 
                    WHERE er.ID_Receta = r.ID_Receta) as EtiquetasNombres
                FROM Receta r
                LEFT JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
                WHERE r.EsPublica = TRUE";

        $params = [];

        // 1. PRIORIDAD: BÚSQUEDA POR TEXTO
        if (!empty($busqueda)) {
            $sql .= " AND (
                u.Username LIKE ? 
                OR r.Titulo LIKE ? 
                OR r.ID_Receta IN (
                    SELECT ri.ID_Receta 
                    FROM Receta_Ingrediente ri 
                    JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente 
                    WHERE i.Nombre LIKE ?
                )
            )";
            $term = "%$busqueda%";
            $params[] = $term; // Coincide con u.Username
            $params[] = $term; // Coincide con r.Titulo
            $params[] = $term; // Coincide con i.Nombre
        } 
        // 2. FILTRO POR ETIQUETAS (Solo si no hay búsqueda)
        else if (!empty($etiquetasFiltro)) {
            // Aseguramos que $etiquetasFiltro sea un array plano de strings
            $tags = is_array($etiquetasFiltro) ? $etiquetasFiltro : [$etiquetasFiltro];
            $placeholders = implode(',', array_fill(0, count($tags), '?'));
            
            $sql .= " AND r.ID_Receta IN (
                SELECT er2.ID_Receta FROM Etiqueta_Receta er2 
                JOIN Etiqueta e2 ON er2.ID_Etiqueta = e2.ID_Etiqueta 
                WHERE e2.Nombre IN ($placeholders)
                GROUP BY er2.ID_Receta
                HAVING COUNT(DISTINCT e2.Nombre) = ?
            )";
            
            foreach($tags as $tag) {
                $params[] = $tag;
            }
            $params[] = count($tags); // El último ? es para el HAVING COUNT
        }

        $sql .= " ORDER BY r.FechaCreacion DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEtiquetasDisponibles() {
        $db = Conexion::conectar();
        return $db->query("SELECT Nombre FROM Etiqueta ORDER BY Nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearPost($userId, $titulo, $descripcion) {
        $db = Conexion::conectar();
        $stmt = $db->prepare("INSERT INTO Receta (ID_Creador, Titulo, Descripcion, EsPublica, FechaCreacion) VALUES (:usuario, :titulo, :descripcion, TRUE, NOW())");
        return $stmt->execute([
            ':usuario' => $userId, 
            ':titulo' => $titulo, 
            ':descripcion' => $descripcion
        ]);
    }
}