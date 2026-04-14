<?php
require_once __DIR__ . '/../../../core/db.php';
require_once __DIR__ . '/../../../core/csrfcheck.php';
require_once __DIR__ . '/../model/AuthModel.php';

class AuthController
{
    private $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../../config/config.php';
    }
    private function jsonResponse($data, $code = 200) 
    {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data);
        exit;
    }
    public function login() 
    {
        csrf_verify();
        $login  = trim($_POST['login'] ?? '');
        $contra = $_POST['contra'] ?? '';

        if (!$login || !$contra) $this->jsonResponse(['error' => 'Campos obligatorios'], 400);

        $auth = new AuthModel();
        // Pasamos los datos de config al modelo
        $result = $auth->loginUsuario($login, $contra, $this->config['JWT_SECRET'], $this->config['JWT_EXP'] ?? 3600);

        if (!$result) $this->jsonResponse(['error' => 'Credenciales incorrectas'], 401);

        $this->establecerCookie($result['token']);
        $this->jsonResponse(['success' => true]);
    }
    public function register() 
    {
        csrf_verify();
        $nombre = trim($_POST['nombre'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['contra'] ?? '';

        if (!$nombre || !$username || !$email || !$password) 
        {
            $this->jsonResponse(['error' => 'Todos los campos son obligatorios'], 400);
        }
        try 
        {
            $auth = new AuthModel();
            if ($auth->crearUsuario($nombre, $username, $email, $password)) 
            {
                $result = $auth->loginUsuario($email, $password, $this->config['JWT_SECRET'], $this->config['JWT_EXP'] ?? 3600);
                $this->establecerCookie($result['token']);
                $this->jsonResponse(['success' => true]);
            }
        } 
        catch (PDOException $e) 
        {
            if ($e->getCode() === '23000') 
            {
                $this->jsonResponse(['error' => 'Email o username ya existe'], 409);
            } 
            else 
            {
                error_log($e->getMessage());
                $this->jsonResponse(['error' => 'Error del servidor'], 500);
            }
        }
    }
    private function establecerCookie($token) 
    {
        setcookie('token', $token, 
        [
            'expires'  => time() + ($this->config['JWT_EXP'] ?? 3600),
            'path'     => '/',
            'httponly' => true,
            //'secure'   => true, 
            'samesite' => 'Strict'
        ]);
    }
    public function logout()
    {
        setcookie('token', '', 
        [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        if (session_status() === PHP_SESSION_NONE) 
        {
            session_start();
        }
        $_SESSION = []; 
        session_unset();
        session_destroy();
        if (ini_get("session.use_cookies")) 
        {
            $params = session_get_cookie_params();
            setcookie
            (session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                //$params["secure"], 
                $params["httponly"]
            );
        }
        header('Location: /App/pages/feed'); 
        exit;
    }

    public function mostrarFormularioPassword($token) {
        $auth = new AuthModel();
        $usuario = $auth->validarTokenRecuperacion($token);

        if (!$usuario) {
            header('Location: /App/pages/login?error=token_invalido');
            exit;
        }

        $datos['token'] = $token;

        require_once __DIR__ . '/../view/resetearContraseña.php';
    }

    public function guardarContrasenaEditada(){
        $datos=$_POST['datos'];
        var_dump($datos);

        if ($datos['contrasena1'] !== $datos['contrasena']) {
            echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden']);
            exit;
        }

        $auth = new AuthModel();

        $auth->editarContraseñaUsuario($datos['contrasena'],$datos['email']);

        header('Location: /App/pages/login');

    }

    public function resetearContrasena()
    {
        header('Content-Type: application/json; charset=UTF-8');
        
        $emailUsuario = $_POST['email'] ?? null;

        if (!$emailUsuario) {
            header("Location:pages/recuperar?estado=error");
            exit;
        }

        $model = new AuthModel();

        $usuario = $model->obtenerUsuarioPorEmail($emailUsuario);

        if ($usuario) {
            $token = bin2hex(random_bytes(32));

            $model->guardarTokenRecuperacion($usuario['ID_Usuario'], $token);

            $enlace = "http://localhost/App/pages/login/resetear/" . $token;

            $asunto = "Restablece tu contraseña - SPEZZIART";
                    
                    $mensaje = "
                                <!DOCTYPE html>
                                <html>
                                <head>
                                    <meta charset='UTF-8'>
                                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                                    <style type='text/css'>
                                        @media only screen and (max-width: 600px) 
                                        {
                                            .responsive-table {width: 100% !important;}
                                            .inner-padding {padding: 30px 20px !important;}
                                            .btn-link 
                                            {
                                                display: block !important;
                                                width: 100% !important;
                                                text-align: center !important;
                                                padding: 14px 10px !important;
                                                font-size: 16px !important;
                                            }
                                            h1 {font-size: 24px !important;}
                                        }
                                    </style>
                                </head>
                                <body style='margin:0; padding:0; background-color:#FFF5F5; font-family: \"Segoe UI\", \"Helvetica Neue\", Helvetica, Arial, sans-serif;'>
                                    <table width='100%' cellpadding='0' cellspacing='0' border='0' bgcolor='#FFF5F5' style='background-color:#FFF5F5;'>
                                        <tr>
                                            <td align='center' style='padding: 40px 20px;'>
                                                <table class='responsive-table' width='560' cellpadding='0' cellspacing='0' border='0' bgcolor='#FFFFFF' style='width:100%; max-width:560px; background-color:#FFFFFF; border-radius:24px; box-shadow:0 8px 20px rgba(128, 0, 32, 0.08); border-collapse:separate; overflow:hidden;'>
                                                    <tr><td bgcolor='#800020' height='6' style='background-color:#800020; font-size:0; line-height:0;'>&nbsp;</td></tr>
                                                    <tr>
                                                        <td class='inner-padding' style='padding: 40px 40px 32px 40px;'>
                                                            <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                                                <tr><td align='center' style='padding-bottom: 12px;'><span class='logo-text' style='font-size: 32px; font-weight: 700; color:#800020; letter-spacing: 2px;'>SPEZZIART</span></td></tr>
                                                                <tr><td align='center'><div style='width: 60px; height: 3px; background-color:#800020; margin: 12px auto 20px auto; border-radius: 3px;'></div></td></tr>
                                                            </table>
                                                            <p style='font-size: 16px; line-height: 1.5; color:#2D3748; margin: 0 0 20px 0; font-weight: 400;'>Estimado/a <strong style='color:#800020;'>{$usuario['Nombre']}</strong>,</p>
                                                            <p style='font-size: 16px; line-height: 1.5; color:#2D3748; margin: 0 0 16px 0;'>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <strong style='color:#800020;'>SPEZZIART</strong>.</p>
                                                            <p style='font-size: 16px; line-height: 1.5; color:#2D3748; margin: 0 0 16px 0;'>Para continuar con el proceso, haz clic en el botón de abajo. Se te redirigirá a un formulario seguro donde podrás crear una nueva contraseña.</p>
                                                            <table width='100%' cellpadding='0' cellspacing='0' border='0' style='margin: 28px 0 20px 0;'><tr><td align='center'><a href='{$enlace}' class='btn-link' style='display:inline-block; background-color:#800020; color:#FFFFFF; font-size:16px; font-weight:600; text-decoration:none; padding:14px 32px; border-radius:50px; box-shadow:0 4px 8px rgba(128,0,32,0.2); text-align:center; border:1px solid #660019;'>Restablecer contraseña</a></td></tr></table>
                                                            <p style='font-size: 13px; line-height: 1.4; color:#6B7280; text-align:center; margin: 10px 0 0 0;'>
                                                                Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:<br>
                                                                <a href='{$enlace}' style='color:#800020; text-decoration:underline; word-break:break-all;'>{$enlace}</a>
                                                            </p>
                                                            <div style='background-color:#FFF5F5; border-left: 4px solid #800020; border-radius: 12px; padding: 16px 20px; margin: 32px 0 20px 0;'>
                                                                <p style='font-size: 14px; line-height: 1.4; color:#660019; margin: 0 0 6px 0; font-weight:500;'><strong>Importante:</strong> Este enlace de recuperación expirará en <strong>1 hora</strong> por motivos de seguridad.</p>
                                                                <p style='font-size: 13px; line-height: 1.4; color:#800020; margin: 0;'>El token de acceso te dará acceso al formulario de reinicio de contraseña durante este período.</p>
                                                            </div>
                                                            <hr style='border: none; height: 1px; background-color:#FFE4E4; margin: 20px 0 16px 0;'>
                                                            <p style='font-size: 13px; line-height: 1.4; color:#9CA3AF; text-align:center; margin: 12px 0 0 0;'>
                                                                <strong>¿No solicitaste este cambio?</strong> Por favor, ignora este correo electrónico.<br>
                                                                Tu contraseña actual seguirá siendo válida y tu cuenta permanecerá segura.
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor='#FEF9F9' style='background-color:#FEF9F9; padding: 20px 40px 28px 40px; border-top: 1px solid #FFE4E4;'>
                                                            <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                                                <tr><td align='center' style='font-size: 12px; color:#800020; font-weight: 500; letter-spacing: 0.5px;'>© 2025 · SPEZZIART · Todos los derechos reservados</td></tr>
                                                                <tr>
                                                                    <td align='center' style='padding-top: 12px;'>
                                                                        <p style='font-size: 12px; line-height: 1.4; color:#6B7280; margin:0;'>
                                                                            Este mensaje fue enviado automáticamente. Por favor, no responder a este correo.<br>
                                                                            Si necesitas asistencia, contacta con nuestro <a href='#' style='color:#800020; text-decoration:underline;'>equipo de soporte</a>.
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                                <tr><td align='center' style='padding-top: 18px;'><span style='font-size: 10px; color:#C4A0A0;'>Protegido con estándares de seguridad avanzados</span></td></tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <table width='100%' cellpadding='0' cellspacing='0' border='0' style='max-width:560px; margin-top:24px;'><tr><td align='center' style='font-size: 11px; color:#C27C7C; line-height:1.4;'>Si has recibido este correo por error, no se ha realizado ningún cambio en tu cuenta.</td></tr></table>
                                            </td>
                                        </tr>
                                    </table>
                                </body>
                                </html>";
            $mensaje = " <!DOCTYPE html>
                                <html>
                                <head>
                                    <meta charset='UTF-8'>
                                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                                    <style type='text/css'>
                                        @media only screen and (max-width: 600px) 
                                        {
                                            .responsive-table {width: 100% !important;}
                                            .inner-padding {padding: 30px 20px !important;}
                                            .btn-link 
                                            {
                                                display: block !important;
                                                width: 100% !important;
                                                text-align: center !important;
                                                padding: 14px 10px !important;
                                                font-size: 16px !important;
                                            }
                                            h1 {font-size: 24px !important;}
                                        }
                                    </style>
                                </head>
                                <body style='margin:0; padding:0; background-color:#FFF5F5; font-family: \"Segoe UI\", \"Helvetica Neue\", Helvetica, Arial, sans-serif;'>
                                    <table width='100%' cellpadding='0' cellspacing='0' border='0' bgcolor='#FFF5F5' style='background-color:#FFF5F5;'>
                                        <tr>
                                            <td align='center' style='padding: 40px 20px;'>
                                                <table class='responsive-table' width='560' cellpadding='0' cellspacing='0' border='0' bgcolor='#FFFFFF' style='width:100%; max-width:560px; background-color:#FFFFFF; border-radius:24px; box-shadow:0 8px 20px rgba(128, 0, 32, 0.08); border-collapse:separate; overflow:hidden;'>
                                                    <tr><td bgcolor='#800020' height='6' style='background-color:#800020; font-size:0; line-height:0;'>&nbsp;</td></tr>
                                                    <tr>
                                                        <td class='inner-padding' style='padding: 40px 40px 32px 40px;'>
                                                            <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                                                <tr><td align='center' style='padding-bottom: 12px;'><span class='logo-text' style='font-size: 32px; font-weight: 700; color:#800020; letter-spacing: 2px;'>SPEZZIART</span></td></tr>
                                                                <tr><td align='center'><div style='width: 60px; height: 3px; background-color:#800020; margin: 12px auto 20px auto; border-radius: 3px;'></div></td></tr>
                                                            </table>
                                                            <p style='font-size: 16px; line-height: 1.5; color:#2D3748; margin: 0 0 20px 0; font-weight: 400;'>Estimado/a <strong style='color:#800020;'>{$usuario['Nombre']}</strong>,</p>
                                                            <p style='font-size: 16px; line-height: 1.5; color:#2D3748; margin: 0 0 16px 0;'>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <strong style='color:#800020;'>SPEZZIART</strong>.</p>
                                                            <p style='font-size: 16px; line-height: 1.5; color:#2D3748; margin: 0 0 16px 0;'>Para continuar con el proceso, haz clic en el botón de abajo. Se te redirigirá a un formulario seguro donde podrás crear una nueva contraseña.</p>
                                                            <table width='100%' cellpadding='0' cellspacing='0' border='0' style='margin: 28px 0 20px 0;'><tr><td align='center'><a href='{$enlace}' class='btn-link' style='display:inline-block; background-color:#800020; color:#FFFFFF; font-size:16px; font-weight:600; text-decoration:none; padding:14px 32px; border-radius:50px; box-shadow:0 4px 8px rgba(128,0,32,0.2); text-align:center; border:1px solid #660019;'>Restablecer contraseña</a></td></tr></table>
                                                            <p style='font-size: 13px; line-height: 1.4; color:#6B7280; text-align:center; margin: 10px 0 0 0;'>
                                                                Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:<br>
                                                                <a href='{$enlace}' style='color:#800020; text-decoration:underline; word-break:break-all;'>{$enlace}</a>
                                                            </p>
                                                            <div style='background-color:#FFF5F5; border-left: 4px solid #800020; border-radius: 12px; padding: 16px 20px; margin: 32px 0 20px 0;'>
                                                                <p style='font-size: 14px; line-height: 1.4; color:#660019; margin: 0 0 6px 0; font-weight:500;'><strong>Importante:</strong> Este enlace de recuperación expirará en <strong>1 hora</strong> por motivos de seguridad.</p>
                                                                <p style='font-size: 13px; line-height: 1.4; color:#800020; margin: 0;'>El token de acceso te dará acceso al formulario de reinicio de contraseña durante este período.</p>
                                                            </div>
                                                            <hr style='border: none; height: 1px; background-color:#FFE4E4; margin: 20px 0 16px 0;'>
                                                            <p style='font-size: 13px; line-height: 1.4; color:#9CA3AF; text-align:center; margin: 12px 0 0 0;'>
                                                                <strong>¿No solicitaste este cambio?</strong> Por favor, ignora este correo electrónico.<br>
                                                                Tu contraseña actual seguirá siendo válida y tu cuenta permanecerá segura.
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor='#FEF9F9' style='background-color:#FEF9F9; padding: 20px 40px 28px 40px; border-top: 1px solid #FFE4E4;'>
                                                            <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                                                <tr><td align='center' style='font-size: 12px; color:#800020; font-weight: 500; letter-spacing: 0.5px;'>© 2025 · SPEZZIART · Todos los derechos reservados</td></tr>
                                                                <tr>
                                                                    <td align='center' style='padding-top: 12px;'>
                                                                        <p style='font-size: 12px; line-height: 1.4; color:#6B7280; margin:0;'>
                                                                            Este mensaje fue enviado automáticamente. Por favor, no responder a este correo.<br>
                                                                            Si necesitas asistencia, contacta con nuestro <a href='#' style='color:#800020; text-decoration:underline;'>equipo de soporte</a>.
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                                <tr><td align='center' style='padding-top: 18px;'><span style='font-size: 10px; color:#C4A0A0;'>Protegido con estándares de seguridad avanzados</span></td></tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <table width='100%' cellpadding='0' cellspacing='0' border='0' style='max-width:560px; margin-top:24px;'><tr><td align='center' style='font-size: 11px; color:#C27C7C; line-height:1.4;'>Si has recibido este correo por error, no se ha realizado ningún cambio en tu cuenta.</td></tr></table>
                                            </td>
                                        </tr>
                                    </table>
                                </body>
                                </html>";

            require_once __DIR__ . '/../../../core/email.php';
            if (Email::enviarEmail($usuario['Email'], $usuario['Nombre'], $asunto, $mensaje)) {
                header("Location:/App/pages/recuperar?estado=correcto");
                exit;
            } else {
               header("Location:/App/pages/recuperar?estado=error");
               exit;
            }
        }
        else{
            header("Location:/App/pages/recuperar?estado=error");
            exit;
        }
    }

    
}