<?php

use Firebase\JWT\JWT;

require_once __DIR__ . '/AdministracionControllers.php';
require_once __DIR__ . '/../../../core/email.php';
require_once __DIR__ . '/../../../core/flash.php';

class UsuariosController extends AdministracionControllers
{

    public function index()
    {
        $datos['roles']    = $this->obtenerRoles();
        $datos['usuarios'] = $this->obtenerUsuarios();
        $this->mostrarAdministracion("usuarios&admin/usuarios.php", "Gestión de Usuarios", $datos);
    }

    private function obtenerUsuarios()
    {
        try {

            $obj = $this->cargarModelo("usuariosModel");
            $r   = $obj->obtenerUsuarios();
            return is_array($r) ? $r : [];
        } catch (\Throwable $e) {

            error_log('[usuarios:obtener] ' . $e->getMessage());
            Flash::error('No se pudo cargar la lista de usuarios.');
            return [];
        }
    }

    private function obtenerRoles()
    {
        try {

            $obj = $this->cargarModelo("usuariosModel");
            $r   = $obj->obtenerRoles();
            return is_array($r) ? $r : [];
        } catch (\Throwable $e) {

            error_log('[usuarios:obtenerRoles] ' . $e->getMessage());
            return [];
        }
    }

    private function validarDatosUsuario(array $datos, bool $passwordObligatorio = true): array
    {
        $errores  = [];
        $apodo    = trim((string)($datos['apodo']    ?? ''));
        $username = trim((string)($datos['username'] ?? ''));
        $email    = trim((string)($datos['email']    ?? ''));
        $password = (string)($datos['password']  ?? '');
        $password2 = (string)($datos['password2'] ?? '');

        if ($apodo === '') {
            $errores[] = 'El apodo es obligatorio.';
        } elseif (mb_strlen($apodo) < 2 || mb_strlen($apodo) > 60) {
            $errores[] = 'El apodo debe tener entre 2 y 60 caracteres.';
        }

        if ($username === '') {
            $errores[] = 'El nombre de usuario es obligatorio.';
        } elseif (mb_strlen($username) < 3 || mb_strlen($username) > 30) {
            $errores[] = 'El nombre de usuario debe tener entre 3 y 30 caracteres.';
        } elseif (!preg_match('/^[a-zA-Z0-9_\.]+$/', $username)) {
            $errores[] = 'El nombre de usuario solo puede contener letras, números, "_" o ".".';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email no es válido.';
        } elseif (mb_strlen($email) > 120) {
            $errores[] = 'El email no puede superar 120 caracteres.';
        }

        if ($passwordObligatorio || $password !== '' || $password2 !== '') {

            if (strlen($password) < 6) {

                $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
            } elseif (strlen($password) > 100) {

                $errores[] = 'La contraseña no puede superar 100 caracteres.';
            }
            if (isset($datos['password2']) && $password !== $password2) {
                $errores[] = 'Las contraseñas no coinciden.';
            }
        }

        return [
            [
                'apodo'     => $apodo,
                'username'  => $username,
                'email'     => $email,
                'password'  => $password,
                'password2' => $password2,
            ],
            $errores
        ];
    }

    public function crearUsuario()
    {
        $datosPost = $_POST['datosUsuario'] ?? null;

        if (!is_array($datosPost)) {
            Flash::error('No se recibieron los datos del usuario.');
            $this->redirigir();
            return;
        }

        [$datos, $errores] = $this->validarDatosUsuario($datosPost, true);

        if (!empty($errores)) {

            Flash::error(implode(' ', $errores));
            $this->redirigir();
            return;
        }

        try {

            $obj = $this->cargarModelo("usuariosModel");

            $obj->crearUsuario($datos);

            Flash::success('Usuario creado correctamente.');
        } catch (\PDOException $e) {

            error_log('[usuarios:crear PDO] ' . $e->getMessage());
            if ($e->getCode() === '23000') {

                Flash::warning('Ya existe un usuario con ese email o nombre de usuario.');
            } else {

                Flash::error('No se pudo crear el usuario.');
            }
        } catch (\Throwable $e) {

            error_log('[usuarios:crear] ' . $e->getMessage());

            Flash::error('No se pudo crear el usuario. Inténtalo de nuevo.');
        }

        $this->redirigir();
    }

    public function eliminarUsuario()
    {
        $idUsuario = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($idUsuario <= 0) {

            Flash::error('Usuario inválido.');
            $this->redirigir();
            return;
        }

        if (isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === $idUsuario) {

            Flash::warning('No puedes eliminar tu propia cuenta desde aquí.');
            $this->redirigir();
            return;
        }

        try {
            $obj = $this->cargarModelo("usuariosModel");

            $usuario = $obj->obtenerUsuarioPorId($idUsuario);

            if (!$usuario) {

                Flash::warning('El usuario ya no existe.');
                $this->redirigir();
                return;
            }

            $obj->usuarioEliminar($idUsuario);

            if (!empty($usuario['Email'])) {

                $nombre = $usuario['Nombre'] ?? $usuario['Username'] ?? 'usuario';
                $asunto = "Tu cuenta ha sido eliminada - SPEZZIART";
                $mensaje = "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><style>@media only screen and (max-width:600px){.responsive-table{width:100%!important}.inner-padding{padding:30px 20px!important}h1{font-size:24px!important}}</style></head><body style='margin:0;padding:0;background-color:#FFF5F5;font-family:\"Segoe UI\",Helvetica,Arial,sans-serif'><table width='100%' cellpadding='0' cellspacing='0' border='0' bgcolor='#FFF5F5'><tr><td align='center' style='padding:40px 20px'><table class='responsive-table' width='560' cellpadding='0' cellspacing='0' border='0' bgcolor='#FFFFFF' style='width:100%;max-width:560px;background:#FFFFFF;border-radius:24px;box-shadow:0 8px 20px rgba(128,0,32,0.08);border-collapse:separate;overflow:hidden'><tr><td bgcolor='#800020' height='6'></td></tr><tr><td class='inner-padding' style='padding:40px 40px 32px'><table width='100%'><tr><td align='center' style='padding-bottom:12px'><span style='font-size:32px;font-weight:700;color:#800020;letter-spacing:2px'>SPEZZIART</span></td></tr><tr><td align='center'><div style='width:60px;height:3px;background:#800020;margin:12px auto 20px;border-radius:3px'></div></td></tr></table><p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 20px'>Estimado/a <strong style='color:#800020'>{$nombre}</strong>,</p><p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 16px'>Te informamos que tu cuenta en <strong>SPEZZIART</strong> ha sido <strong>eliminada</strong> por un administrador.</p><div style='background:#FFF5F5;border-left:4px solid #DC3545;border-radius:12px;padding:16px 20px;margin:24px 0 20px'><p style='font-size:14px;color:#B02A37;margin:0 0 6px'><strong>Cuenta eliminada:</strong> Ya no podrás iniciar sesión en la plataforma.</p><p style='font-size:13px;color:#DC3545;margin:0'>Tu acceso y contenidos asociados han sido desactivados permanentemente.</p></div><p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 16px'>Si crees que esto se trata de un error o necesitas más información, puedes responder a este correo para revisar tu caso.</p><hr style='border:none;height:1px;background:#FFE4E4;margin:20px 0 16px'><p style='font-size:13px;color:#9CA3AF;text-align:center;margin:12px 0 0'><strong>¿Necesitas ayuda?</strong> Responde a este correo y revisaremos tu situación.</p></td></tr><tr><td bgcolor='#FEF9F9' style='padding:20px 40px 28px;border-top:1px solid #FFE4E4'><table width='100%'><tr><td align='center' style='font-size:12px;color:#800020;font-weight:500'>© 2026 · SPEZZIART</td></tr><tr><td align='center' style='padding-top:12px'><p style='font-size:12px;color:#6B7280;margin:0'>Mensaje automático del equipo de SPEZZIART.</p></td></tr><tr><td align='center' style='padding-top:18px'><span style='font-size:10px;color:#C4A0A0'>Protegido con seguridad avanzada</span></td></tr></table></td></tr></table><table width='100%' style='max-width:560px;margin-top:24px'><tr><td align='center' style='font-size:11px;color:#C27C7C'>Si recibiste este correo por error, contacta con soporte lo antes posible.</td></tr></table></td></tr></table></body></html>";

                try {
                    Email::enviarEmail($usuario['Email'], $nombre, $asunto, $mensaje);
                } catch (\Throwable $e) {
                    error_log('[usuarios:eliminar email] ' . $e->getMessage());
                }
            }

            Flash::success('Usuario eliminado correctamente.');
        } catch (\Throwable $e) {
            error_log('[usuarios:eliminar] ' . $e->getMessage());
            Flash::error('No se pudo eliminar el usuario.');
        }

        $this->redirigir();
    }

    public function resetearContrasena()
    {

        header('Content-Type: application/json; charset=UTF-8');
        $idUsuario = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($idUsuario <= 0) {

            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado.']);
            exit;
        }

        try {

            $model   = $this->cargarModelo("usuariosModel");
            $usuario = $model->obtenerUsuarioPorId($idUsuario);

            if (!$usuario) {

                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado.']);
                exit;
            }

            if (empty($usuario['Email'])) {

                echo json_encode(['status' => 'error', 'message' => 'El usuario no tiene email registrado.']);
                exit;
            }

            $token = bin2hex(random_bytes(32));
            $model->guardarTokenRecuperacion($idUsuario, $token);

            $enlace  = "http://a4.dawbaza.es/pages/login/resetear/" . $token;
            $asunto  = "Restablece tu contraseña - SPEZZIART";
            $mensaje = "<!DOCTYPE html>
            <html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>@media only screen and (max-width:600px){.responsive-table{width:100%!important}.inner-padding{padding:30px 20px!important}.btn-link{display:block!important;width:100%!important;text-align:center!important;padding:14px 10px!important;font-size:16px!important}h1{font-size:24px!important}}</style>
            </head><body style='margin:0;padding:0;background-color:#FFF5F5;font-family:\"Segoe UI\",Helvetica,Arial,sans-serif'>
            <table width='100%' cellpadding='0' cellspacing='0' border='0' bgcolor='#FFF5F5'><tr><td align='center' style='padding:40px 20px'>
            <table class='responsive-table' width='560' cellpadding='0' cellspacing='0' border='0' bgcolor='#FFFFFF' style='width:100%;max-width:560px;background:#FFFFFF;border-radius:24px;box-shadow:0 8px 20px rgba(128,0,32,0.08);border-collapse:separate;overflow:hidden'>
            <tr><td bgcolor='#800020' height='6'></td></tr><tr><td class='inner-padding' style='padding:40px 40px 32px'>
            <table width='100%'><tr><td align='center' style='padding-bottom:12px'><span style='font-size:32px;font-weight:700;color:#800020;letter-spacing:2px'>SPEZZIART</span></td></tr>
            <tr><td align='center'><div style='width:60px;height:3px;background:#800020;margin:12px auto 20px;border-radius:3px'></div></td></tr></table>
            <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 20px'>Estimado/a <strong style='color:#800020'>{$usuario['Nombre']}</strong>,</p>
            <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 16px'>Hemos recibido una solicitud para restablecer tu contraseña en <strong>SPEZZIART</strong>.</p>
            <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 16px'>Haz clic en el botón para crear una nueva contraseña.</p>
            <table width='100%' style='margin:28px 0 20px'><tr><td align='center'><a href='{$enlace}' class='btn-link' style='display:inline-block;background:#800020;color:#FFF;font-size:16px;font-weight:600;text-decoration:none;padding:14px 32px;border-radius:50px;box-shadow:0 4px 8px rgba(128,0,32,0.2);border:1px solid #660019'>Restablecer contraseña</a></td></tr></table>
            <p style='font-size:13px;color:#6B7280;text-align:center;margin:10px 0 0'>Si el botón no funciona, copia este enlace:<br><a href='{$enlace}' style='color:#800020;word-break:break-all'>{$enlace}</a></p>
            <div style='background:#FFF5F5;border-left:4px solid #800020;border-radius:12px;padding:16px 20px;margin:32px 0 20px'><p style='font-size:14px;color:#660019;margin:0 0 6px'><strong>Importante:</strong> Este enlace expira en <strong>1 hora</strong>.</p><p style='font-size:13px;color:#800020;margin:0'>Usa el enlace para acceder al formulario seguro.</p></div>
            <hr style='border:none;height:1px;background:#FFE4E4;margin:20px 0 16px'>
            <p style='font-size:13px;color:#9CA3AF;text-align:center;margin:12px 0 0'><strong>¿No solicitaste este cambio?</strong> Ignora este correo.<br>Tu contraseña actual sigue siendo válida.</p>
            </td></tr><tr><td bgcolor='#FEF9F9' style='padding:20px 40px 28px;border-top:1px solid #FFE4E4'><table width='100%'><tr><td align='center' style='font-size:12px;color:#800020;font-weight:500'>© 2026 · SPEZZIART</td></tr>
            <tr><td align='center' style='padding-top:12px'><p style='font-size:12px;color:#6B7280;margin:0'>Mensaje automático, no responder.</p></td></tr>
            <tr><td align='center' style='padding-top:18px'><span style='font-size:10px;color:#C4A0A0'>Protegido con seguridad avanzada</span></td></tr></table></td></tr></table>
            <table width='100%' style='max-width:560px;margin-top:24px'><tr><td align='center' style='font-size:11px;color:#C27C7C'>Si recibiste este correo por error, no se realizaron cambios.</td></tr></table>
            </td></tr></table></body></html>";

            require_once __DIR__ . '/../../../core/email.php';

            if (Email::enviarEmail($usuario['Email'], $usuario['Nombre'], $asunto, $mensaje)) {

                echo json_encode(['status' => 'success', 'message' => 'Email enviado a ' . $usuario['Email']]);
                exit;
            } else {

                echo json_encode(['status' => 'error', 'message' => 'No se pudo enviar el correo.']);
                exit;
            }
        } catch (\Throwable $e) {

            error_log('[usuarios:resetear] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error interno al resetear la contraseña.']);
            exit;
        }
    }

    public function editarUsuario()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $datosPost = $_POST['datosUsuario'] ?? null;

        if (!is_array($datosPost)) {

            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            exit;
        }


        [$datos, $errores] = $this->validarDatosUsuario($datosPost, false);

        if (!empty($errores)) {

            http_response_code(422);
            echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
            exit;
        }

        $datos['ID_Usuario'] = (int)($_SESSION['user']['id'] ?? 0);

        if ($datos['ID_Usuario'] <= 0) {

            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Sesión no válida.']);
            exit;
        }

        try {

            $obj    = $this->cargarModelo("usuariosModel");
            $result = $obj->editarUsuario($datos);

            if (!$result) {

                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el perfil.']);
                exit;
            }

            $_SESSION['user']['nombre']   = $datos['apodo'];
            $_SESSION['user']['username'] = $datos['username'];
            $_SESSION['user']['email']    = $datos['email'];

            echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente.']);
            exit;
            
        } catch (\PDOException $e) {

            error_log('[usuarios:editar PDO] ' . $e->getMessage());

            if ($e->getCode() === '23000') {

                echo json_encode(['success' => false, 'message' => 'Ya existe un usuario con ese email o username.']);
                exit;
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil.']);
                exit;
            }
        } catch (\Throwable $e) {

            error_log('[usuarios:editar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil.']);
            exit;
        }
    }


    public function cambiarRolUsuario(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $datos = json_decode(file_get_contents('php://input'), true);
        if (!is_array($datos)) $datos = $_POST;

        $idUsuario = isset($datos['id'])      ? (int)$datos['id']      : 0;
        $esAdmin   = isset($datos['esAdmin']) ? (int)$datos['esAdmin'] : -1;

        if ($idUsuario <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Usuario inválido.']);
            return;
        }
        if ($esAdmin !== 0 && $esAdmin !== 1) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Rol no válido.']);
            return;
        }
        if (isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === $idUsuario) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'No puedes cambiar tu propio rol.']);
            return;
        }

        try {
            $obj = $this->cargarModelo("usuariosModel");

            if (!$obj->obtenerUsuarioPorId($idUsuario)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'El usuario no existe.']);
                return;
            }

            $obj->cambiarRolUsuario($idUsuario, $esAdmin);

            echo json_encode([
                'success' => true,
                'esAdmin' => $esAdmin,
                'message' => $esAdmin === 1
                    ? 'Usuario promovido a administrador correctamente.'
                    : 'Administrador degradado a usuario correctamente.',
            ]);
        } catch (\Throwable $e) {
            error_log('[usuarios:cambiarRol] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudo cambiar el rol del usuario.']);
        }
    }

    public function cambiarEstadoUsuario(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $datos = json_decode(file_get_contents('php://input'), true);
        if (!is_array($datos)) {
            $datos = $_POST;
        }

        $idUsuario = isset($datos['id'])     ? (int)$datos['id']     : 0;
        $activa    = isset($datos['activa']) ? (int)$datos['activa'] : -1;

        if ($idUsuario <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Usuario inválido.']);
            return;
        }

        if ($activa !== 0 && $activa !== 1) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Estado no válido.']);
            return;
        }

        if ($activa === 0 && isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === $idUsuario) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'No puedes deshabilitar tu propia cuenta desde aquí.']);
            return;
        }

        try {
            $obj = $this->cargarModelo("usuariosModel");

            $usuario = $obj->obtenerUsuarioPorId($idUsuario);
            if (!$usuario) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'El usuario ya no existe.']);
                return;
            }

            $obj->cambiarEstadoUsuario($idUsuario, $activa);

            if ($activa === 1) {
                $mensajeOk = 'Usuario activado correctamente.';
                $mensajeKo = 'Usuario activado correctamente, pero no se pudo enviar el email.';
                $this->enviarEmailActivacion($usuario);
            } else {
                $mensajeOk = 'Usuario deshabilitado correctamente.';
                $mensajeKo = 'Usuario deshabilitado correctamente, pero no se pudo enviar el email.';
                $this->enviarEmailDeshabilitacion($usuario);
            }


            echo json_encode([
                'success'      => true,
                'activa'       => $activa,
                'message'      => $mensajeFinal,
            ]);
            return;
        } catch (\Throwable $e) {
            error_log('[usuarios:cambiarEstado] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudo cambiar el estado del usuario.']);
            return;
        }
    }


    private function enviarEmailDeshabilitacion(array $usuario): void
    {
        $email = (string)($usuario['Email'] ?? '');
        if ($email === '') return;

        $nombre       = $usuario['Nombre'] ?? $usuario['Username'] ?? 'usuario';
        $nombreSeguro = htmlspecialchars((string)$nombre);

        $asunto  = 'Tu cuenta ha sido deshabilitada - SPEZZIART';
        $mensaje = "<!DOCTYPE html>
            <html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>@media only screen and (max-width:600px){.responsive-table{width:100%!important}.inner-padding{padding:30px 20px!important}h1{font-size:24px!important}}</style>
            </head><body style='margin:0;padding:0;background-color:#FFF5F5;font-family:\"Segoe UI\",Helvetica,Arial,sans-serif'>
            <table width='100%' cellpadding='0' cellspacing='0' border='0' bgcolor='#FFF5F5'><tr><td align='center' style='padding:40px 20px'>
            <table class='responsive-table' width='560' cellpadding='0' cellspacing='0' border='0' bgcolor='#FFFFFF' style='width:100%;max-width:560px;background:#FFFFFF;border-radius:24px;box-shadow:0 8px 20px rgba(128,0,32,0.08);border-collapse:separate;overflow:hidden'>
            <tr><td bgcolor='#800020' height='6'></td></tr><tr><td class='inner-padding' style='padding:40px 40px 32px'>
            <table width='100%'><tr><td align='center' style='padding-bottom:12px'><span style='font-size:32px;font-weight:700;color:#800020;letter-spacing:2px'>SPEZZIART</span></td></tr>
            <tr><td align='center'><div style='width:60px;height:3px;background:#800020;margin:12px auto 20px;border-radius:3px'></div></td></tr></table>
            <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 20px'>Estimado/a <strong style='color:#800020'>{$nombreSeguro}</strong>,</p>
            <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 16px'>Te informamos que tu cuenta en <strong>SPEZZIART</strong> ha sido <strong>deshabilitada</strong> por un administrador.</p>
            <div style='background:#FFF5F5;border-left:4px solid #800020;border-radius:12px;padding:16px 20px;margin:24px 0 20px'>
                <p style='font-size:14px;color:#660019;margin:0 0 6px'><strong>Importante:</strong> Mientras la cuenta esté deshabilitada no podrás iniciar sesión.</p>
                <p style='font-size:13px;color:#800020;margin:0'>Tus contenidos siguen guardados, pero permanecerán ocultos.</p>
            </div>
            <p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 16px'>Si crees que se trata de un error o necesitas más información, puedes responder a este correo para que revisemos tu caso.</p>
            <hr style='border:none;height:1px;background:#FFE4E4;margin:20px 0 16px'>
            <p style='font-size:13px;color:#9CA3AF;text-align:center;margin:12px 0 0'><strong>¿Crees que es un error?</strong> Responde a este correo para revisar tu caso.</p>
            </td></tr><tr><td bgcolor='#FEF9F9' style='padding:20px 40px 28px;border-top:1px solid #FFE4E4'><table width='100%'><tr><td align='center' style='font-size:12px;color:#800020;font-weight:500'>© 2026 · SPEZZIART</td></tr>
            <tr><td align='center' style='padding-top:12px'><p style='font-size:12px;color:#6B7280;margin:0'>Mensaje automático del equipo de SPEZZIART.</p></td></tr>
            <tr><td align='center' style='padding-top:18px'><span style='font-size:10px;color:#C4A0A0'>Protegido con seguridad avanzada</span></td></tr></table></td></tr></table>
            <table width='100%' style='max-width:560px;margin-top:24px'><tr><td align='center' style='font-size:11px;color:#C27C7C'>Si recibiste este correo por error, no se realizaron cambios definitivos.</td></tr></table>
            </td></tr></table></body></html>";

        require_once __DIR__ . '/../../../core/email.php';
        Email::enviarEmail($email, (string)$nombre, $asunto, $mensaje);
    }
    private function enviarEmailActivacion(array $usuario): void
    {
        $email = (string)($usuario['Email'] ?? '');
        if ($email === '') return;

        $nombre       = $usuario['Nombre'] ?? $usuario['Username'] ?? 'usuario';
        $nombreSeguro = htmlspecialchars((string)$nombre);

        $asunto  = 'Tu cuenta ha sido activada - SPEZZIART';
        $mensaje = "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><style>@media only screen and (max-width:600px){.responsive-table{width:100%!important}.inner-padding{padding:30px 20px!important}h1{font-size:24px!important}}</style></head><body style='margin:0;padding:0;background-color:#FFF5F5;font-family:\"Segoe UI\",Helvetica,Arial,sans-serif'><table width='100%' cellpadding='0' cellspacing='0' border='0' bgcolor='#FFF5F5'><tr><td align='center' style='padding:40px 20px'><table class='responsive-table' width='560' cellpadding='0' cellspacing='0' border='0' bgcolor='#FFFFFF' style='width:100%;max-width:560px;background:#FFFFFF;border-radius:24px;box-shadow:0 8px 20px rgba(128,0,32,0.08);border-collapse:separate;overflow:hidden'><tr><td bgcolor='#800020' height='6'></td></tr><tr><td class='inner-padding' style='padding:40px 40px 32px'><table width='100%'><tr><td align='center' style='padding-bottom:12px'><span style='font-size:32px;font-weight:700;color:#800020;letter-spacing:2px'>SPEZZIART</span></td></tr><tr><td align='center'><div style='width:60px;height:3px;background:#800020;margin:12px auto 20px;border-radius:3px'></div></td></tr></table><p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 20px'>Estimado/a <strong style='color:#800020'>{$nombreSeguro}</strong>,</p><p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 16px'>Te informamos que tu cuenta en <strong>SPEZZIART</strong> ha sido <strong>activada nuevamente</strong> por un administrador.</p><div style='background:#F4FFF7;border-left:4px solid #198754;border-radius:12px;padding:16px 20px;margin:24px 0 20px'><p style='font-size:14px;color:#146C43;margin:0 0 6px'><strong>Cuenta activa:</strong> Ya puedes volver a iniciar sesión con normalidad.</p><p style='font-size:13px;color:#198754;margin:0'>Todos tus contenidos y funcionalidades están disponibles nuevamente.</p></div><p style='font-size:16px;line-height:1.5;color:#2D3748;margin:0 0 16px'>Si no reconoces esta acción o tienes alguna duda, puedes responder a este correo para recibir ayuda.</p><hr style='border:none;height:1px;background:#FFE4E4;margin:20px 0 16px'><p style='font-size:13px;color:#9CA3AF;text-align:center;margin:12px 0 0'><strong>¿Necesitas ayuda?</strong> Responde a este correo y te atenderemos lo antes posible.</p></td></tr><tr><td bgcolor='#FEF9F9' style='padding:20px 40px 28px;border-top:1px solid #FFE4E4'><table width='100%'><tr><td align='center' style='font-size:12px;color:#800020;font-weight:500'>© 2026 · SPEZZIART</td></tr><tr><td align='center' style='padding-top:12px'><p style='font-size:12px;color:#6B7280;margin:0'>Mensaje automático del equipo de SPEZZIART.</p></td></tr><tr><td align='center' style='padding-top:18px'><span style='font-size:10px;color:#C4A0A0'>Protegido con seguridad avanzada</span></td></tr></table></td></tr></table><table width='100%' style='max-width:560px;margin-top:24px'><tr><td align='center' style='font-size:11px;color:#C27C7C'>Si recibiste este correo por error, no se realizaron cambios no autorizados.</td></tr></table></td></tr></table></body></html>";

        require_once __DIR__ . '/../../../core/email.php';
        Email::enviarEmail($email, (string)$nombre, $asunto, $mensaje);
    }

    private function redirigir(): void
    {
        header('Location: /pages/administracion/usuarios');
        exit;
    }
}
