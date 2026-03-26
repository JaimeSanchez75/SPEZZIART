<?php
require_once __DIR__ . '/../../../core/db.php';
class individualModel 
{
    private $db;
    public function __construct(){$this->db = Conexion::conectar();}
    // ================== RECETAS ==================
    public function getRecetasUsuario($idUsuario) 
    {
        $stmt = $this->db->prepare
        ("
            SELECT r.*, u.Username 
            FROM Receta r
            JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
            WHERE r.ID_Creador = :id
            ORDER BY r.FechaCreacion DESC
        ");
        $stmt->execute(['id' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getRecetaById($id) 
    {
        $stmt = $this->db->prepare
        ("
            SELECT r.*, u.Username
            FROM Receta r
            JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
            WHERE r.ID_Receta = :id
        ");
        $stmt->execute(['id' => $id]);
        $receta = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($receta) 
        {
            $stmt = $this->db->prepare
            ("
                SELECT i.Nombre
                FROM Ingrediente i
                JOIN Receta_Ingrediente ri ON i.ID_Ingrediente = ri.ID_Ingrediente
                WHERE ri.ID_Receta = :id
            ");
            $stmt->execute(['id' => $id]);
            $receta['ingredientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $receta;
    }
    // Obtener receta solo si pertenece al usuario
    public function getRecetaByIdAndUser($id, $userId)
    {
        $stmt = $this->db->prepare
        ("
            SELECT r.*, u.Username
            FROM Receta r
            JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
            WHERE r.ID_Receta = :id AND r.ID_Creador = :userId
        ");
        $stmt->execute(['id' => $id, 'userId' => $userId]);
        $receta = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($receta) 
        {
            $stmt = $this->db->prepare
            ("
                SELECT i.Nombre
                FROM Ingrediente i
                JOIN Receta_Ingrediente ri ON i.ID_Ingrediente = ri.ID_Ingrediente
                WHERE ri.ID_Receta = :id
            ");
            $stmt->execute(['id' => $id]);
            $receta['ingredientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $receta;
    }
    public function crearReceta($idUsuario, $data) 
    {
        $stmt = $this->db->prepare
        ("
            INSERT INTO Receta (ID_Creador, Titulo, Descripcion, Imagen, Tiempo, Porciones, EsFit)
            VALUES (:idCreador, :titulo, :descripcion, :imagen, :tiempo, :porciones, :esFit)
        ");
        $stmt->execute
        ([
            'idCreador' => $idUsuario,
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'imagen' => $data['imagen'] ?? null,
            'tiempo' => $data['tiempo'] ?? null,
            'porciones' => $data['porciones'] ?? null,
            'esFit' => !empty($data['fit']) ? 1 : 0
        ]);
        return $this->db->lastInsertId();
    }
    // Actualizar receta solo si pertenece al usuario
    public function actualizarReceta($id, $userId, $data) 
    {
        $stmt = $this->db->prepare
        ("
            UPDATE Receta 
            SET Titulo = :titulo,
                Descripcion = :descripcion,
                Imagen = :imagen,
                Tiempo = :tiempo,
                Porciones = :porciones,
                EsFit = :fit
            WHERE ID_Receta = :id AND ID_Creador = :userId
        ");
        return $stmt->execute
        ([
            'id' => $id,
            'userId' => $userId,
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'imagen' => $data['imagen'] ?? null,
            'tiempo' => $data['tiempo'] ?? null,
            'porciones' => $data['porciones'] ?? null,
            'fit' => !empty($data['fit']) ? 1 : 0
        ]);
    }
    // Eliminar receta solo si pertenece al usuario
    public function eliminarReceta($id, $userId) 
    {
        try 
        {
            $this->db->beginTransaction();
            // Verificar que la receta pertenece al usuario
            $stmt = $this->db->prepare("SELECT ID_Receta FROM Receta WHERE ID_Receta = :id AND ID_Creador = :userId");
            $stmt->execute(['id' => $id, 'userId' => $userId]);
            if (!$stmt->fetch()) {$this->db->rollBack(); return false;}
            // Eliminar relaciones
            $stmt = $this->db->prepare("DELETE FROM Coleccion_Receta WHERE ID_Receta = :id");
            $stmt->execute(['id' => $id]);
            // Eliminar receta
            $stmt = $this->db->prepare("DELETE FROM Receta WHERE ID_Receta = :id");
            $stmt->execute(['id' => $id]);
            $this->db->commit();
            return true;
        } 
        catch (PDOException $e) {$this->db->rollBack(); return false;}
    }
    // ================== BUSQUEDA ==================
    public function buscarRecetasUsuario($idUsuario, $busqueda) 
    {
        $like = "%" . $busqueda . "%";
        $stmt = $this->db->prepare
        ("
            SELECT r.*, u.Username
            FROM Receta r
            JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
            WHERE r.ID_Creador = :idUsuario
              AND (r.Titulo LIKE :like OR r.Descripcion LIKE :like)
            ORDER BY r.FechaCreacion DESC
        ");
        $stmt->execute(['idUsuario' => $idUsuario, 'like' => $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function buscarColeccionesUsuario($idUsuario, $busqueda) 
    {
        $like = "%" . $busqueda . "%";
        $stmt = $this->db->prepare
        ("
            SELECT * 
            FROM Coleccion
            WHERE ID_Creador = :idUsuario
              AND Nombre LIKE :like
            ORDER BY ID_Coleccion DESC
        ");
        $stmt->execute(['idUsuario' => $idUsuario, 'like' => $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // ================== COLECCIONES ==================
    public function crearColeccion($idUsuario, $nombre, $esPublica = true) 
    {
        $nombre = trim($nombre);
        if ($nombre === '') return false;
        $stmt = $this->db->prepare
        ("
            INSERT INTO Coleccion (ID_Creador, Nombre, EsPublica)
            VALUES (:idUsuario, :nombre, :esPublica)
        ");
        return $stmt->execute
        ([
            'idUsuario' => $idUsuario,
            'nombre' => $nombre,
            'esPublica' => $esPublica ? 1 : 0
        ]);
    }
    public function getColeccionesUsuario($idUsuario) 
    {
        $stmt = $this->db->prepare
        ("
            SELECT * 
            FROM Coleccion
            WHERE ID_Creador = :idUsuario
            ORDER BY ID_Coleccion DESC
        ");
        $stmt->execute(['idUsuario' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Obtener colección solo si pertenece al usuario
    public function getColeccionByIdAndUser($idColeccion, $userId) 
    {
        $stmt = $this->db->prepare
        ("
            SELECT * FROM Coleccion
            WHERE ID_Coleccion = :id AND ID_Creador = :userId
        ");
        $stmt->execute(['id' => $idColeccion, 'userId' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function agregarRecetaAColeccion($idReceta, $idColeccion) 
    {
        if (!$idReceta || !$idColeccion) return false;
        try 
        {
            $stmt = $this->db->prepare
            ("
                INSERT IGNORE INTO Coleccion_Receta (ID_Receta, ID_Coleccion)
                VALUES (:idReceta, :idColeccion)
            ");
            return $stmt->execute(['idReceta' => $idReceta, 'idColeccion' => $idColeccion]);
        } 
        catch (PDOException $e) {return false;}
    }
    public function getRecetasDeColeccion($idColeccion) 
    {
        $stmt = $this->db->prepare("
            SELECT r.*, u.Username
            FROM Receta r
            INNER JOIN Coleccion_Receta cr ON r.ID_Receta = cr.ID_Receta
            INNER JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
            WHERE cr.ID_Coleccion = :idColeccion
            ORDER BY r.FechaCreacion DESC
        ");
        $stmt->execute(['idColeccion' => $idColeccion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminarRecetaDeColeccion($idReceta, $idColeccion) 
    {
        $stmt = $this->db->prepare("
            DELETE FROM Coleccion_Receta 
            WHERE ID_Receta = :idReceta AND ID_Coleccion = :idColeccion
        ");
        return $stmt->execute(['idReceta' => $idReceta, 'idColeccion' => $idColeccion]);
    }
    public function eliminarColeccion($idColeccion) 
    {
        try 
        {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("DELETE FROM Coleccion_Receta WHERE ID_Coleccion = :idColeccion");
            $stmt->execute(['idColeccion' => $idColeccion]);
            $stmt = $this->db->prepare("DELETE FROM Coleccion WHERE ID_Coleccion = :idColeccion");
            $stmt->execute(['idColeccion' => $idColeccion]);
            $this->db->commit();
            return true;
        } 
        catch (PDOException $e) {$this->db->rollBack(); return false;}
    }
}