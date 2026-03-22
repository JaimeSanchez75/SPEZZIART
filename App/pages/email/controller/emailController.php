<?php
    require_once __DIR__ . '/../model/emailModel.php';
    require_once __DIR__ . '/../../../core/Email.php';

    class emailController 
    {   
        public function enviarEmail($idUsuario) {

            $__objEmail = new emailModel();

            $usuario = $__objEmail->obtenerUsuarioPorId($idUsuario);

            if (!$usuario) {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
                return;
            }

            $token = bin2hex(random_bytes(32));
            $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

            if ($__objEmail->guardarToken($idUsuario, $token, $expiracion)) {
                $enlace= "http://localhost/App/pages/recuperar?token=$token";

                $mensaje="<h2>Solicitud de cambio de contraseña</h2>
                        <p>Hola {$usuario['nombre']}, haz clic en el botón para cambiar tu clave. 
                        Si no fuiste tú, ignora este mensaje.</p>
                        <a href='{$enlace}' style='background: #2ecc71; color: white; padding: 10px; text-decoration: none;'>
                           Restablecer Contraseña
                        </a>";
                $enviado= Email::enviarEmail($usuario['email'], $usuario['nombre'],'Recuperar Contraseña', $mensaje);

                return $enviado ? true : false;
                    
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al generar el token']);
            }
        }
    }