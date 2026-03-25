<?php
class individualModel {

    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=localhost;dbname=SpezziArt;charset=utf8",
                "appuser",
                "G7@kP4!zQ9"
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    // ================== RECETAS ==================

    public function getRecetasUsuario($idUsuario) {
        $stmt = $this->pdo->prepare("
            SELECT r.*, u.Username 
            FROM Receta r
            JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
            WHERE r.ID_Creador = :id
            ORDER BY r.FechaCreacion DESC
        ");
        $stmt->execute(['id' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearReceta($idUsuario, $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO Receta (ID_Creador, Titulo, Descripcion, Imagen, Tiempo, Porciones, EsFit)
            VALUES (:idCreador, :titulo, :descripcion, :imagen, :tiempo, :porciones, :esFit)
        ");
        $stmt->execute([
            'idCreador' => $idUsuario,
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'imagen' => $data['imagen'],
            'tiempo' => $data['tiempo'],
            'porciones' => $data['porciones'],
            'esFit' => $data['fit']
        ]);
        return $this->pdo->lastInsertId();
    }

    // ================== BUSQUEDA ==================

    public function buscarRecetasUsuario($idUsuario, $busqueda) {
        $like = "%$busqueda%";
        $stmt = $this->pdo->prepare("
            SELECT r.*, u.Username
            FROM Receta r
            JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
            WHERE r.ID_Creador = :idUsuario
              AND (
                    r.Titulo LIKE :like 
                    OR r.Descripcion LIKE :like
                    OR EXISTS (
                        SELECT 1
                        FROM Receta_Ingrediente ri
                        JOIN Ingrediente i ON ri.ID_Ingrediente = i.ID_Ingrediente
                        WHERE ri.ID_Receta = r.ID_Receta
                          AND i.Nombre LIKE :like
                    )
              )
            ORDER BY r.FechaCreacion DESC
        ");
        $stmt->execute([
            'idUsuario' => $idUsuario,
            'like' => $like
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarColeccionesUsuario($idUsuario, $busqueda) {
        $like = "%$busqueda%";
        $stmt = $this->pdo->prepare("
            SELECT * 
            FROM Coleccion
            WHERE ID_Creador = :idUsuario
              AND Nombre LIKE :like
            ORDER BY ID_Coleccion DESC
        ");
        $stmt->execute([
            'idUsuario' => $idUsuario,
            'like' => $like
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ================== COLECCIONES ==================

    public function crearColeccion($idUsuario, $nombre, $esPublica = true) {
        $stmt = $this->pdo->prepare("
            INSERT INTO Coleccion (ID_Creador, Nombre, EsPublica)
            VALUES (:idUsuario, :nombre, :esPublica)
        ");
        $stmt->execute([
            'idUsuario' => $idUsuario,
            'nombre' => $nombre,
            'esPublica' => $esPublica
        ]);
        return $this->pdo->lastInsertId();
    }

    public function getColeccionesUsuario($idUsuario) {
        $stmt = $this->pdo->prepare("
            SELECT * 
            FROM Coleccion
            WHERE ID_Creador = :idUsuario
            ORDER BY ID_Coleccion DESC
        ");
        $stmt->execute(['idUsuario' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔐 VALIDACIONES (NUEVO - CLAVE)
    public function coleccionPerteneceAUsuario($idUsuario, $idColeccion) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM Coleccion
            WHERE ID_Coleccion = :idColeccion
              AND ID_Creador = :idUsuario
        ");
        $stmt->execute([
            'idColeccion' => $idColeccion,
            'idUsuario' => $idUsuario
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function recetaPerteneceAUsuario($idUsuario, $idReceta) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM Receta
            WHERE ID_Receta = :idReceta
              AND ID_Creador = :idUsuario
        ");
        $stmt->execute([
            'idReceta' => $idReceta,
            'idUsuario' => $idUsuario
        ]);
        return $stmt->fetchColumn() > 0;
    }

    // 🔥 MÉTODO CRÍTICO ARREGLADO
    public function agregarRecetaAColeccion($idUsuario, $idReceta, $idColeccion) {

        // VALIDAR PROPIEDAD
        if (!$this->coleccionPerteneceAUsuario($idUsuario, $idColeccion)) {
            return false;
        }

        if (!$this->recetaPerteneceAUsuario($idUsuario, $idReceta)) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO Coleccion_Receta (ID_Receta, ID_Coleccion)
            VALUES (:idReceta, :idColeccion)
        ");

        $stmt->execute([
            'idReceta' => $idReceta,
            'idColeccion' => $idColeccion
        ]);

        return true;
    }

    public function getRecetasDeColeccion($idColeccion) {
        $stmt = $this->pdo->prepare("
            SELECT r.*, u.Username
            FROM Receta r
            JOIN Coleccion_Receta cr ON r.ID_Receta = cr.ID_Receta
            JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
            WHERE cr.ID_Coleccion = :idColeccion
            ORDER BY r.FechaCreacion DESC
        ");
        $stmt->execute(['idColeccion' => $idColeccion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}