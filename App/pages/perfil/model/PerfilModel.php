<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../core/db.php';

class PerfilModel 
{
    public function getDatosUsuario(int $id) 
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT * FROM Usuario WHERE ID_Usuario = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRecetasUsuario(int $id) 
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT * FROM Receta WHERE ID_Creador = ? ORDER BY FechaCreacion DESC");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- SISTEMA DE LOGROS ---
    public function verificarYEntregarLogros(int $idUsuario): void 
    {
        $db = Conexion::conectar();
        $stmt = $db->query("SELECT * FROM Logros");
        $logrosDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //Contamos estadísticas actuales del usuario
        $stats = 
        [
            'recetas'     => $db->query("SELECT COUNT(*) FROM Receta WHERE ID_Creador = $idUsuario")->fetchColumn(),
            'seguidores'  => $db->query("SELECT COUNT(*) FROM Usuario_Seguidor WHERE ID_Usuario = $idUsuario")->fetchColumn(),
            'comentarios' => $db->query("SELECT COUNT(*) FROM Comentario WHERE ID_Creador = $idUsuario")->fetchColumn(),
            'fit'         => $db->query("SELECT COUNT(*) FROM Receta WHERE ID_Creador = $idUsuario AND EsFit = TRUE")->fetchColumn()
        ];
        foreach ($logrosDisponibles as $logro) 
        {
            $tipo = $logro['Tipo_Evento'];
            if (isset($stats[$tipo]) && $stats[$tipo] >= $logro['Meta_Cantidad']) 
            {
                try 
                {
                    $insert = $db->prepare("INSERT INTO Logros_Usuario (ID_Usuario, ID_Logro) VALUES (?, ?)");
                    $insert->execute([$idUsuario, $logro['ID_Logro']]);
                } 
                catch (PDOException $e) { }
            }
        }
    }

    public function getVitrinaLogros(int $idUsuario): array 
    {
        $db = Conexion::conectar();
        
        //Obtenemos todos los logros y marcamos cuáles tiene el usuario
        $sql = "SELECT l.*, 
                (SELECT COUNT(*) FROM Logros_Usuario WHERE ID_Usuario = ? AND ID_Logro = l.ID_Logro) as ganado
                FROM Logros l";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idUsuario]);
        $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtenemos los que el usuario eligió mostrar específicamente
        $stmtExp = $db->prepare("SELECT ID_Logro FROM Usuario_Logros_Expuestos WHERE ID_Usuario = ? ORDER BY Posicion ASC");
        $stmtExp->execute([$idUsuario]);
        $expuestosIds = $stmtExp->fetchAll(PDO::FETCH_COLUMN);

        $final = [];
        $usados = [];

        // Por prioridad: Los elegidos manualmente
        foreach($expuestosIds as $id) 
        {
            foreach($todos as $t) 
            {
                if($t['ID_Logro'] == $id) 
                { 
                    $final[] = $t; 
                    $usados[] = $id;
                }
            }
        }

        // Segunda prioridad: Rellenar con logros ganados (hasta llegar a 8)
        if(count($final) < 8) 
        {
            foreach($todos as $t) 
            {
                if($t['ganado'] && !in_array($t['ID_Logro'], $usados) && count($final) < 8) 
                {
                    $final[] = $t;
                    $usados[] = $t['ID_Logro'];
                }
            }
        }

        // Prioridad 3: Rellenar con bloqueados si aún no hay 8
        if(count($final) < 8) 
        {
            foreach($todos as $t) 
            {
                if(!in_array($t['ID_Logro'], $usados) && count($final) < 8) {$final[] = $t;}
            }
        }
        return $final;
    }

    public function actualizarVitrina(int $idUsuario, array $logrosIds): bool 
    {
        $db = Conexion::conectar();
        try 
        {
            $db->beginTransaction();
            
            // Limpiamos la vitrina
            $stmt = $db->prepare("DELETE FROM Usuario_Logros_Expuestos WHERE ID_Usuario = ?");
            $stmt->execute([$idUsuario]);

            // Insertamos los nuevos (limitando a 8 por seguridad)
            $pos = 1;
            foreach(array_slice($logrosIds, 0, 8) as $idLogro) 
            {
                $ins = $db->prepare("INSERT INTO Usuario_Logros_Expuestos (ID_Usuario, ID_Logro, Posicion) VALUES (?, ?, ?)");
                $ins->execute([$idUsuario, $idLogro, $pos]);
                $pos++;
            }
            
            $db->commit();
            return true;
        } 
        catch (Exception $e) 
        {
            $db->rollBack();
            return false;
        }
    }

    // --- SISTEMA SEGUIDORES ---
    public function comprobarSeguimiento(int $idDestino, int $idSeguidor): bool 
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT COUNT(*) FROM Usuario_Seguidor WHERE ID_Usuario = ? AND ID_Seguidor = ?");
        $stmt->execute([$idDestino, $idSeguidor]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function toggleSeguir(int $idDestino, int $idSeguidor): string 
    {
        $db = Conexion::conectar();
        $existe = $this->comprobarSeguimiento($idDestino, $idSeguidor);

        if ($existe) 
        {
            $stmt = $db->prepare("DELETE FROM Usuario_Seguidor WHERE ID_Usuario = ? AND ID_Seguidor = ?");
            $resp = "unfollowed";
        } 
        else 
        {
            $stmt = $db->prepare("INSERT INTO Usuario_Seguidor (ID_Usuario, ID_Seguidor) VALUES (?, ?)");
            $resp = "followed";
        }
        $stmt->execute([$idDestino, $idSeguidor]);
        
        // Actualizar contador  en la tabla Usuario
        $count = $db->query("SELECT COUNT(*) FROM Usuario_Seguidor WHERE ID_Usuario = $idDestino")->fetchColumn();
        $upd = $db->prepare("UPDATE Usuario SET Seguidores = ? WHERE ID_Usuario = ?");
        $upd->execute([$count, $idDestino]);

        return $resp;
    }
}