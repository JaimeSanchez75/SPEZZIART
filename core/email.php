<?php
declare(strict_types=1);
require_once __DIR__ . '/logger.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class Email // Clase para manejar el envío de correos electrónicos utilizando PHPMailer. Se configura para usar SMTP con Gmail y se registran los eventos de envío en el logger.
{
    public static function enviarEmail($para, $nombre, $asunto, $mensaje) // Devuelve true si el correo se envió correctamente, false en caso de error.
    {
        $email = new PHPMailer(true);
        try 
        {
            $email->isSMTP();
            $email->Host = 'smtp.gmail.com';
            $email->SMTPAuth = true;
            $email->Username = "soportesppeziart@gmail.com";
            $email->Password = $_ENV['PWDAPLICACION'];
            $email->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $email->Port = 587;
            $email->CharSet = 'UTF-8';
            $email->setFrom('soportesppeziart@gmail.com', 'SPEZZIART');
            $email->addAddress($para, $nombre);
            $email->isHTML(true);
            $email->Subject = $asunto;
            $email->Body = $mensaje;
            $email->send();
            Logger::info('email.php', 'Email::enviarEmail', 'SMTP', "Correo enviado a $para (asunto: $asunto)");
            return true;
        } 
        catch (Exception $e) 
        {
            Logger::error('email.php', 'Email::enviarEmail', 'SMTP', $e->getCode(), $e->getMessage());
            return false;
        }
    }
}