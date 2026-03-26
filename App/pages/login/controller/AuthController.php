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
            echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
            exit;
        }

        $model = new AuthModel();

        $usuario = $model->obtenerUsuarioPorEmail($emailUsuario);

        if ($usuario) {
            $token = bin2hex(random_bytes(32));

            $model->guardarTokenRecuperacion($usuario['ID_Usuario'], $token);

            $enlace = "http://localhost/App/pages/login/resetear/" . $token;

            $asunto = "Restablece tu contraseña - SPEZZIART";
            $mensaje = "<h1>Hola {$usuario['Nombre']}</h1>
                        <p>Un administrador ha solicitado el restablecimiento de tu contraseña.</p>
                        <p>Haz clic en el enlace para elegir una nueva clave (vence en 1 hora):</p>
                        <a href='$enlace' style='background:#800020; color:white; padding:10px; text-decoration:none;'>Restablecer ahora</a>";

            require_once __DIR__ . '/../../../core/email.php';


            if (Email::enviarEmail($usuario['Email'], $usuario['Nombre'], $asunto, $mensaje)) {
                echo json_encode(['status' => 'success', 'message' => 'Email enviado a ' . $usuario['Email']]);
                exit;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al enviar el correo']);
                exit;
            }
        }
    }

    
}