<?php
declare(strict_types=1);
require_once __DIR__ . '/../model/AuthModel.php';
require_once __DIR__ . '/../../../core/notifications.php';
class AuthController
{
    private AuthModel $authModel;
    public function __construct(){$this->authModel = new AuthModel();}
    private function jsonResponse(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    public function login(): void
    {
        csrf_verify();
        $recaptchaToken = $_POST['recaptcha_token'] ?? '';
        if (!$this->verifyRecaptcha($recaptchaToken, 'login')) {$this->jsonResponse(['error' => 'Verificación de seguridad fallida'], 400);}
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['contra'] ?? '';
        $remember = isset($_POST['remember_me']) ? true : false;
        if (!$login || !$password) {$this->jsonResponse(['error' => 'Campos obligatorios'], 400);}
        $user = $this->authModel->getUsuarioPorLogin($login);
        if (!$user || !password_verify($password, $user['Contrasena'])) {$this->jsonResponse(['error' => 'Credenciales incorrectas'], 401);}
    
        if (array_key_exists('Activa', $user) && (int)$user['Activa'] === 0) 
        {$this->jsonResponse(['error' => 'No puedes acceder a esta cuenta.Tu cuenta ha sido deshabilitada.'], 403);  }
        Auth::login($user, $remember);
        // Notificar a los administradores sobre el inicio de sesión (excepto si el que se loguea es admin).
        if (empty($user['EsAdmin'])) 
        {
            try 
            {
                NotificacionService::crearParaAdmins
                (
                    (int)$user['ID_Usuario'],
                    "El usuario {$user['Username']} ha iniciado sesión",
                    'login'
                );
            } 
            catch (Throwable $e) {error_log('Error notificando login a admins: ' . $e->getMessage());}
        }
        $this->jsonResponse
        ([
            'success' => true,
            'redirect' => '/pages/feed',
            'user' => 
            [
                'id' => $user['ID_Usuario'],
                'nombre' => $user['Nombre'],
                'username' => $user['Username'],
                'email' => $user['Email'],
                'role' => $user['EsAdmin'] ? 'admin' : 'user'
            ]
        ]);
    }
    public function register(): void
    {
        csrf_verify();
        $recaptchaToken = $_POST['recaptcha_token'] ?? '';
        if (!$this->verifyRecaptcha($recaptchaToken, 'register')) {$this->jsonResponse(['error' => 'Verificación de seguridad fallida'], 400);}
        $nombre = trim($_POST['nombre'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['contra'] ?? '';
        if (!$nombre || !$username || !$email || !$password) {$this->jsonResponse(['error' => 'Todos los campos son obligatorios'], 400);}
        try 
        {
            if ($this->authModel->crearUsuario($nombre, $username, $email, $password)) 
            {
                $user = $this->authModel->getUsuarioPorLogin($email);
                Auth::login($user, false);
                $this->authModel->otorgarLogroRegistro((int)$user['ID_Usuario']);
                $this->jsonResponse
                ([
                    'success' => true,
                    'redirect' => '/pages/feed',
                    'user' => 
                    [
                        'id' => $user['ID_Usuario'],
                        'nombre' => $user['Nombre'],
                        'username' => $user['Username'],
                        'email' => $user['Email']
                    ]
                ]);
                NotificacionService::crearParaAdmins(
                    (int)$user['ID_Usuario'],
                    "Nuevo usuario registrado: {$user['Username']}",
                    'Registro nuevo usuario'
                );
            }
        } 
        catch (PDOException $e) 
        {
            if ($e->getCode() === '23000') {$this->jsonResponse(['error' => 'Email o nombre de usuario ya existe'], 409);} 
            else {error_log($e->getMessage()); $this->jsonResponse(['error' => 'Error del servidor'], 500);}
        }
    }
    public function logout(): void{ Auth::logout(); $this->jsonResponse(['success' => true, 'redirect' => '/pages/login']);}
    private function verifyRecaptcha(string $token, string $action): bool
    {
        if (empty($token)) {return false;}
        $secret = $_ENV['RECAPTCHA_SECRET_KEY'] ?? null;
        $threshold = $_ENV['RECAPTCHA_SCORE_THRESHOLD'] ?? 0.5;
        if (!$secret) {error_log('RECAPTCHA_SECRET_KEY no configurada'); return false;}
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query
        ([
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response === false) {return false;}
        $data = json_decode($response, true);
        return $data['success'] && ($data['score'] ?? 0) >= $threshold && ($data['action'] ?? '') === $action;
    }
    public function mostrarFormularioPassword($token): void
    {
        $usuario = $this->authModel->validarTokenRecuperacion($token);
        if (!$usuario) {header('Location: /pages/login?error=token_invalido'); exit;}
        require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/login/view/resetearContrasena.php';
    }
    public function resetearContrasena()
    {
        header('Content-Type: application/json; charset=UTF-8');
        $emailUsuario = $_POST['email'] ?? null;
        if (!$emailUsuario) {header("Location:/pages/recuperar?estado=error"); exit;}
        $model = new AuthModel();
        $usuario = $model->obtenerUsuarioPorEmail($emailUsuario);
        if ($usuario) 
        {
            $token = bin2hex(random_bytes(32));
            $model->guardarTokenRecuperacion($usuario['ID_Usuario'], $token);
            $enlace = "http://a4.dawbaza.es/pages/login/resetear/" . $token;
            $asunto = "Restablece tu contraseña - SPEZZIART";
            $mensaje = //Compactado para legibilidad del resto del código pero básicamente es el correo de reinicio de contraseña. 
            "<!DOCTYPE html>
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
            require_once $_SERVER['DOCUMENT_ROOT'] . '/core/email.php';
            if (Email::enviarEmail($usuario['Email'], $usuario['Nombre'], $asunto, $mensaje)) {header("Location:/pages/recuperar?estado=correcto"); exit;} 
            else {header("Location:/pages/recuperar?estado=error"); exit;}
        } 
        else {header("Location:/pages/recuperar?estado=error"); exit;}
    }
    public function guardarContrasenaEditada()
    {
        $datos=$_POST['datos'];
        var_dump($datos);
        if ($datos['contrasena1'] !== $datos['contrasena']) {echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden']); exit;}
        $auth = new AuthModel();
        $auth->editarContraseñaUsuario($datos['contrasena'],$datos['email']);
        header('Location: /pages/login');
    }
}