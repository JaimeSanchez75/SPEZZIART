<?php

    require_once __DIR__ . '/../../../core/db.php';

    class emailModel 
    {   
        private $db;

        public function obtenerUsuarioPorId($idUsuario) {

            $this->db = Conexion::conectar();
            $stmt = $this->db->prepare("SELECT ID_Usuario, Nombre, Email FROM usuario WHERE ID_Usuario = :id");
            $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        function guardarToken($idUsuario, $token, $expiracion) {
            $this->db = Conexion::conectar();
            $stmt = $this->db->prepare("UPDATE usuario SET ResetearToken = :token, ResetearExpira = :expiracion WHERE ID_Usuario = :id");
            $stmt->bindParam(':token', password_hash($token, PASSWORD_DEFAULT));
            $stmt->bindParam(':expiracion', $expiracion);
            $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
            return $stmt->execute();
        }

        function validarToken($token) {

            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT ID_Usuario FROM usuario WHERE ResetearToken is not null AND ResetearExpira > NOW()");
            $stmt->execute();
            $usuarios =$stmt->fetch(PDO::FETCH_ASSOC);

            foreach ($usuarios as $usuario) {
                if (password_verify($token, $usuario['ResetearToken'])) {
                    return $usuario['ID_Usuario'];
                }
            }

            return false;
        }


    }