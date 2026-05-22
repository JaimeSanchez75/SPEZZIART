<?php
require_once __DIR__ . '/../../../core/db.php';

class RecetaModel
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::conectar();
    }

    /**
     * Obtener receta completa con ingredientes, etiquetas, datos del creador y likes
     */
    public function getRecetaCompleta(int $idReceta, ?int $userId = null): ?array
    {
        // Usar solo parámetros con nombre para evitar el error HY093
        $stmt = $this->db->prepare("
            SELECT r.*, u.Username, u.FotoPerfil,
                COALESCE(r.Megustas, 0) as Likes,
                (SELECT COUNT(*) FROM Receta_Megusta WHERE ID_Receta = r.ID_Receta AND ID_Usuario = :userId) as DioLike,
                (SELECT COUNT(*) FROM Comentario WHERE ID_Receta = r.ID_Receta) as TotalComentarios
            FROM Receta r
            JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
            WHERE r.ID_Receta = :id
        ");
        $stmt->execute(['id' => $idReceta, 'userId' => $userId ?? 0]);
        $receta = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$receta) {
            return null;
        }

        $receta['Imagenes'] = !empty($receta['Imagen'])
            ? array_values(array_filter(array_map('trim', explode(',', (string)$receta['Imagen']))))
            : [];

        $receta['ingredientes'] = $this->obtenerIngredientesDeReceta($idReceta);
        $receta['etiquetas'] = $this->obtenerEtiquetasDeReceta($idReceta);

        $procesado = $this->procesarDescripcionYPasos($receta);
        $receta['DescripcionVisible'] = $procesado['descripcion'];

        if (empty($receta['Pasos']) && !empty($procesado['pasos'])) {
            $receta['Pasos'] = $procesado['pasos'];
        } elseif (!empty($receta['Pasos'])) {
            $pasosJson = json_decode($receta['Pasos'], true);
            if (is_array($pasosJson)) {
                $receta['Pasos'] = array_values(array_filter(array_map('trim', $pasosJson), fn($p) => $p !== ''));
            } else {
                $receta['Pasos'] = [];
            }
        } else {
            $receta['Pasos'] = [];
        }

        return $receta;
    }

    private function obtenerIngredientesDeReceta(int $idReceta): array
    {
        $stmt = $this->db->prepare("
            SELECT i.ID_Ingrediente, i.Nombre, i.Calorias, i.Proteina, i.Carbohidratos, i.Grasas, i.Unidad_Base, ri.Cantidad
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
        $stmt = $this->db->prepare("
            SELECT e.ID_Etiqueta, e.Nombre
            FROM Etiqueta e
            JOIN Etiqueta_Receta er ON e.ID_Etiqueta = er.ID_Etiqueta
            WHERE er.ID_Receta = :id
            ORDER BY e.Nombre ASC
        ");
        $stmt->execute(['id' => $idReceta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function procesarDescripcionYPasos(array $receta): array
    {
        $descripcion = trim($receta['Descripcion'] ?? '');
        $pasos = [];
        $separador = "\n\nPASOS:\n";
        $posicion = strrpos($descripcion, $separador);
        if ($posicion !== false) {
            $bloquePasos = trim(substr($descripcion, $posicion + strlen($separador)));
            $descripcion = trim(substr($descripcion, 0, $posicion));
            foreach (preg_split('/\R+/', $bloquePasos) as $linea) {
                $linea = trim($linea);
                if ($linea === '') continue;
                $pasos[] = preg_replace('/^\d+\.\s*/', '', $linea);
            }
        }
        return ['descripcion' => $descripcion, 'pasos' => $pasos];
    }
}