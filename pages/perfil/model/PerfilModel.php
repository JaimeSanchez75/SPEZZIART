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
    
    public function getNumSeguidores(int $id)
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare('SELECT COUNT(*) FROM Usuario_Seguidor WHERE ID_Usuario = ?');
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
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

        $stats = [
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
    
    // Obtener logro específico con detalles y progreso del usuario
    public function getLogroDetalle(int $idUsuario, int $idLogro): array {
        $db = Conexion::conectar();
        
        $stmt = $db->prepare("SELECT * FROM Logros WHERE ID_Logro = ?");
        $stmt->execute([$idLogro]);
        $logro = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$logro) return [];
        
        // Verificar si el usuario lo tiene desbloqueado
        $stmt = $db->prepare("SELECT Fecha FROM Logros_Usuario WHERE ID_Usuario = ? AND ID_Logro = ?");
        $stmt->execute([$idUsuario, $idLogro]);
        $obtenido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($obtenido) {
            $logro['desbloqueado'] = true;
            $logro['fecha'] = $obtenido['Fecha'];
            return $logro;
        }
        
        $logro['desbloqueado'] = false;
        $stats = [
            'recetas'     => $db->query("SELECT COUNT(*) FROM Receta WHERE ID_Creador = $idUsuario")->fetchColumn(),
            'seguidores'  => $db->query("SELECT COUNT(*) FROM Usuario_Seguidor WHERE ID_Usuario = $idUsuario")->fetchColumn(),
            'comentarios' => $db->query("SELECT COUNT(*) FROM Comentario WHERE ID_Creador = $idUsuario")->fetchColumn(),
            'fit'         => $db->query("SELECT COUNT(*) FROM Receta WHERE ID_Creador = $idUsuario AND EsFit = TRUE")->fetchColumn(),
            'especial'    => 0
        ];
        $tipo = $logro['Tipo_Evento'];
        $actual = $stats[$tipo] ?? 0;
        $meta = $logro['Meta_Cantidad'];
        $logro['progreso'] = min(100, round(($actual / $meta) * 100));
        $logro['actual'] = $actual;
        $logro['meta'] = $meta;
        
        return $logro;
    }

    // Obtener todos los logros con estado y progreso (para el modal principal)
    public function getTodosLogrosConEstado(int $idUsuario): array {
        $db = Conexion::conectar();
        $logros = [];
        
        $stmt = $db->query("SELECT * FROM Logros ORDER BY Meta_Cantidad ASC");
        $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stats = [
            'recetas'     => $db->query("SELECT COUNT(*) FROM Receta WHERE ID_Creador = $idUsuario")->fetchColumn(),
            'seguidores'  => $db->query("SELECT COUNT(*) FROM Usuario_Seguidor WHERE ID_Usuario = $idUsuario")->fetchColumn(),
            'comentarios' => $db->query("SELECT COUNT(*) FROM Comentario WHERE ID_Creador = $idUsuario")->fetchColumn(),
            'fit'         => $db->query("SELECT COUNT(*) FROM Receta WHERE ID_Creador = $idUsuario AND EsFit = TRUE")->fetchColumn()
        ];
        
        foreach ($todos as $logro) {
            $stmt = $db->prepare("SELECT Fecha FROM Logros_Usuario WHERE ID_Usuario = ? AND ID_Logro = ?");
            $stmt->execute([$idUsuario, $logro['ID_Logro']]);
            $obtenido = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($obtenido) {
                $logro['desbloqueado'] = true;
                $logro['fecha'] = $obtenido['Fecha'];
                $logro['progreso'] = 100;
            } else {
                $logro['desbloqueado'] = false;
                $tipo = $logro['Tipo_Evento'];
                $actual = $stats[$tipo] ?? 0;
                $meta = $logro['Meta_Cantidad'];
                $logro['progreso'] = min(100, round(($actual / $meta) * 100));
                $logro['actual'] = $actual;
                $logro['meta'] = $meta;
            }
            $logros[] = $logro;
        }
        return $logros;
    }
    
    public function getVitrinaLogros(int $idUsuario): array 
    {
        $db = Conexion::conectar();
        
        $sql = "SELECT l.*, 
                (SELECT COUNT(*) FROM Logros_Usuario WHERE ID_Usuario = ? AND ID_Logro = l.ID_Logro) as ganado
                FROM Logros l";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idUsuario]);
        $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener estadísticas para calcular progreso de logros no ganados
        $stats = [
            'recetas'     => $db->query("SELECT COUNT(*) FROM Receta WHERE ID_Creador = $idUsuario")->fetchColumn(),
            'seguidores'  => $db->query("SELECT COUNT(*) FROM Usuario_Seguidor WHERE ID_Usuario = $idUsuario")->fetchColumn(),
            'comentarios' => $db->query("SELECT COUNT(*) FROM Comentario WHERE ID_Creador = $idUsuario")->fetchColumn(),
            'fit'         => $db->query("SELECT COUNT(*) FROM Receta WHERE ID_Creador = $idUsuario AND EsFit = TRUE")->fetchColumn(),
            'especial'    => 0
        ];

        foreach ($todos as &$logro) {
            if ($logro['ganado']) {
                $logro['progreso'] = 100;
            } else {
                $tipo = $logro['Tipo_Evento'];
                $actual = $stats[$tipo] ?? 0;
                $meta = $logro['Meta_Cantidad'];
                $logro['progreso'] = min(100, round(($actual / $meta) * 100));
            }
        }
        
        $stmtExp = $db->prepare("SELECT ID_Logro FROM Usuario_Logros_Expuestos WHERE ID_Usuario = ? ORDER BY Posicion ASC");
        $stmtExp->execute([$idUsuario]);
        $expuestosIds = $stmtExp->fetchAll(PDO::FETCH_COLUMN);

        $final = [];
        $usados = [];

        // Prioridad 1: elegidos manualmente
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

        // Prioridad 2: rellenar con logros ganados hasta 8
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

        // Prioridad 3: rellenar con bloqueados si aún no hay 8
        if(count($final) < 8) 
        {
            foreach($todos as $t) 
            {
                if(!in_array($t['ID_Logro'], $usados) && count($final) < 8) 
                {
                    $final[] = $t;
                }
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
            $stmt = $db->prepare("DELETE FROM Usuario_Logros_Expuestos WHERE ID_Usuario = ?");
            $stmt->execute([$idUsuario]);

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
        
        return $resp;
    }
    
    public function getUserConfig($userId)
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT Tema, ModoFit, NotificacionOn, CuentaPublica FROM Usuario WHERE ID_Usuario = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function actualizarNombre(int $idUsuario, string $nuevoNombre): array 
    {
        $db = Conexion::conectar();
        $nuevoNombre = trim($nuevoNombre);

        if ($nuevoNombre === '') {
            return ['success' => false, 'error' => 'El nombre no puede estar vacío'];
        }

        if (mb_strlen($nuevoNombre, 'UTF-8') > 30) {
            return ['success' => false, 'error' => 'El nombre no puede superar los 30 caracteres'];
        }

        $stmt = $db->prepare("SELECT COUNT(*) FROM Usuario WHERE Nombre = ? AND ID_Usuario != ?");
        $stmt->execute([$nuevoNombre, $idUsuario]);

        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'error' => 'El nombre de usuario ya está en uso'];
        }

        $stmt = $db->prepare("UPDATE Usuario SET Nombre = ? WHERE ID_Usuario = ?");
        $stmt->execute([$nuevoNombre, $idUsuario]);

        return ['success' => true];
    }

    public function actualizarFoto(int $idUsuario, string $rutaFoto): bool {
        $db = Conexion::conectar();
        $stmt = $db->prepare("UPDATE Usuario SET FotoPerfil = ? WHERE ID_Usuario = ?");
        return $stmt->execute([$rutaFoto, $idUsuario]);
    }

    // --- GESTIÓN DE BANNERS ---
    public function obtenerBannersDesbloqueados(int $idUsuario): array {
        $db = Conexion::conectar();
        $stmt = $db->prepare("
            SELECT b.* FROM Banners b
            LEFT JOIN Usuario_Banners ub ON ub.ID_Banner = b.ID_Banner AND ub.ID_Usuario = ?
            WHERE ub.ID_Banner IS NOT NULL
        ");
        $stmt->execute([$idUsuario]);
        $desbloqueados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($desbloqueados)) {
            $stmtDef = $db->prepare("SELECT * FROM Banners WHERE ID_Logro_Requerido IS NULL");
            $stmtDef->execute();
            $defecto = $stmtDef->fetchAll(PDO::FETCH_ASSOC);
            foreach ($defecto as $b) {
                $ins = $db->prepare("INSERT INTO Usuario_Banners (ID_Usuario, ID_Banner) VALUES (?, ?)");
                $ins->execute([$idUsuario, $b['ID_Banner']]);
            }
            return $defecto;
        }
        return $desbloqueados;
    }

    public function obtenerBannerActual(int $idUsuario): ?array {
        $db = Conexion::conectar();
        $stmt = $db->prepare("
            SELECT b.* FROM Usuario u
            JOIN Banners b ON u.BannerActual = b.ID_Banner
            WHERE u.ID_Usuario = ?
        ");
        $stmt->execute([$idUsuario]);
        $banner = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$banner) {
            $banners = $this->obtenerBannersDesbloqueados($idUsuario);
            return $banners[0] ?? null;
        }
        return $banner;
    }

    public function cambiarBanner(int $idUsuario, int $idBanner): bool {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT COUNT(*) FROM Usuario_Banners WHERE ID_Usuario = ? AND ID_Banner = ?");
        $stmt->execute([$idUsuario, $idBanner]);
        if ($stmt->fetchColumn() == 0) return false;
        
        $stmt = $db->prepare("UPDATE Usuario SET BannerActual = ? WHERE ID_Usuario = ?");
        return $stmt->execute([$idBanner, $idUsuario]);
    }
    
    public function obtenerTodosBannersConEstado(int $idUsuario): array {
        $db = Conexion::conectar();
        $sql = "SELECT b.*, 
                    (SELECT COUNT(*) FROM Usuario_Banners WHERE ID_Usuario = ? AND ID_Banner = b.ID_Banner) as desbloqueado
                FROM Banners b
                ORDER BY b.ID_Banner";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function verificarYDesbloquearBanners(int $idUsuario): void {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Logro FROM Logros_Usuario WHERE ID_Usuario = ?");
        $stmt->execute([$idUsuario]);
        $logrosUsuario = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($logrosUsuario)) return;
        
        $placeholders = implode(',', array_fill(0, count($logrosUsuario), '?'));
        $sql = "SELECT ID_Banner FROM Banners WHERE ID_Logro_Requerido IN ($placeholders)
                AND ID_Banner NOT IN (SELECT ID_Banner FROM Usuario_Banners WHERE ID_Usuario = ?)";
        $params = array_merge($logrosUsuario, [$idUsuario]);
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $nuevos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($nuevos as $idBanner) {
            $ins = $db->prepare("INSERT INTO Usuario_Banners (ID_Usuario, ID_Banner) VALUES (?, ?)");
            $ins->execute([$idUsuario, $idBanner]);
        }
    }
    public function esPerfilPublico(int $idUsuario): bool {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT CuentaPublica FROM Usuario WHERE ID_Usuario = ?");
        $stmt->execute([$idUsuario]);
        return (bool)$stmt->fetchColumn();
    }

    public function solicitarSeguir(int $idDestino, int $idSolicitante): array {
        $db = Conexion::conectar();
        // Comprobar si ya existe solicitud pendiente
        $stmt = $db->prepare("SELECT Estado FROM Solicitudes_Seguimiento WHERE ID_Usuario_Destino = ? AND ID_Usuario_Solicitante = ?");
        $stmt->execute([$idDestino, $idSolicitante]);
        $existe = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existe) {
            if ($existe['Estado'] === 'pendiente') {
                return ['success' => false, 'message' => 'Ya has enviado una solicitud a este usuario'];
            } elseif ($existe['Estado'] === 'aceptada') {
                return ['success' => false, 'message' => 'Ya sigues a este usuario'];
            } else {
                // rechazada: permitir reenvío
                $stmt = $db->prepare("UPDATE Solicitudes_Seguimiento SET Estado = 'pendiente', Fecha_Solicitud = NOW() WHERE ID_Usuario_Destino = ? AND ID_Usuario_Solicitante = ?");
                $stmt->execute([$idDestino, $idSolicitante]);
                return ['success' => true, 'accion' => 'solicitado'];
            }
        }
        $stmt = $db->prepare("INSERT INTO Solicitudes_Seguimiento (ID_Usuario_Destino, ID_Usuario_Solicitante) VALUES (?, ?)");
        if ($stmt->execute([$idDestino, $idSolicitante])) {
            return ['success' => true, 'accion' => 'solicitado'];
        }
        return ['success' => false, 'message' => 'Error al crear solicitud'];
    }
    public function aceptarSolicitud(int $idDestino, int $idSolicitante, int $idNotificacion): bool
    {
        $db = Conexion::conectar();
        $db->beginTransaction();
        try {
            // Actualizar estado de solicitud
            $stmt = $db->prepare("UPDATE Solicitudes_Seguimiento SET Estado = 'aceptada' WHERE ID_Usuario_Destino = ? AND ID_Usuario_Solicitante = ? AND Estado = 'pendiente'");
            $stmt->execute([$idDestino, $idSolicitante]);
            if ($stmt->rowCount() == 0) {
                $db->rollBack();
                return false;
            }
            // Insertar relación de seguidor
            $stmt = $db->prepare("INSERT INTO Usuario_Seguidor (ID_Usuario, ID_Seguidor) VALUES (?, ?)");
            $stmt->execute([$idDestino, $idSolicitante]);

            // Eliminar la notificación de solicitud por su ID
            $stmtNotif = $db->prepare("DELETE FROM Notificacion WHERE ID_Notificacion = ?");
            $stmtNotif->execute([$idNotificacion]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Error en aceptarSolicitud: " . $e->getMessage());
            return false;
        }
    }
    public function limpiarSolicitudSeguimiento(int $idDestino, int $idSolicitante): void
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("DELETE FROM Solicitudes_Seguimiento WHERE ID_Usuario_Destino = ? AND ID_Usuario_Solicitante = ?");
        $stmt->execute([$idDestino, $idSolicitante]);
    }
    public function rechazarSolicitud(int $idDestino, int $idSolicitante, int $idNotificacion): bool
    {
        $db = Conexion::conectar();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE Solicitudes_Seguimiento SET Estado = 'rechazada' WHERE ID_Usuario_Destino = ? AND ID_Usuario_Solicitante = ? AND Estado = 'pendiente'");
            $stmt->execute([$idDestino, $idSolicitante]);
            if ($stmt->rowCount() == 0) {
                $db->rollBack();
                return false;
            }
            // Eliminar notificación por ID
            $stmtNotif = $db->prepare("DELETE FROM Notificacion WHERE ID_Notificacion = ?");
            $stmtNotif->execute([$idNotificacion]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Error en rechazarSolicitud: " . $e->getMessage());
            return false;
        }
    }

    public function getSolicitudesPendientes(int $idUsuario): array {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT s.*, u.Nombre as solicitante_nombre, u.Username as solicitante_username, u.FotoPerfil
                            FROM Solicitudes_Seguimiento s
                            JOIN Usuario u ON s.ID_Usuario_Solicitante = u.ID_Usuario
                            WHERE s.ID_Usuario_Destino = ? AND s.Estado = 'pendiente'");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function cancelarSolicitudSeguimiento(int $idDestino, int $idSolicitante): bool
    {
        $db = Conexion::conectar();

        try {
            $stmt = $db->prepare("
                DELETE FROM Solicitudes_Seguimiento
                WHERE ID_Usuario_Destino = ?
                AND ID_Usuario_Solicitante = ?
                AND Estado = 'pendiente'
            ");
            $stmt->execute([$idDestino, $idSolicitante]);

            if ($stmt->rowCount() === 0) {
                return false;
            }

            try {
                $stmtNotif = $db->prepare("
                    DELETE FROM Notificacion
                    WHERE ID_Usuario_Destino = ?
                    AND ID_Usuario_Origen = ?
                    AND Tipo = 'solicitud_seguimiento'
                ");
                $stmtNotif->execute([$idDestino, $idSolicitante]);
            } catch (Exception $e) {
                error_log("No se pudo borrar la notificación de solicitud: " . $e->getMessage());
            }

            return true;
        } catch (Exception $e) {
            error_log("Error en cancelarSolicitudSeguimiento: " . $e->getMessage());
            return false;
        }
    }
    public function tieneSolicitudPendiente(int $idDestino, int $idSolicitante): bool {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT COUNT(*) FROM Solicitudes_Seguimiento WHERE ID_Usuario_Destino = ? AND ID_Usuario_Solicitante = ? AND Estado = 'pendiente'");
        $stmt->execute([$idDestino, $idSolicitante]);
        return $stmt->fetchColumn() > 0;
    }

    
}