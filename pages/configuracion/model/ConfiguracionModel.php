<?php
require_once __DIR__ . '/../../../core/db.php';

class ConfiguracionModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function obtenerConfiguracion($userId) {
        $stmt = $this->db->prepare("SELECT Tema, ModoFit, NotificacionOn, CuentaPublica FROM Usuario WHERE ID_Usuario = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarConfiguracion($userId, $tema, $modoFit, $notificaciones, $cuentaPublica) {
        $stmt = $this->db->prepare("UPDATE Usuario SET Tema = ?, ModoFit = ?, NotificacionOn = ?, CuentaPublica = ? WHERE ID_Usuario = ?");
        return $stmt->execute([$tema, $modoFit, $notificaciones, $cuentaPublica, $userId]);
    }
}