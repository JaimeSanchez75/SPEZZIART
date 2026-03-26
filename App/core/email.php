<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email
{
    public static function enviarEmail($para,$nombre, $asunto, $mensaje)
    {
        $email = new PHPMailer(true);

        try {

            $email->isSMTP();
            $email->Host = 'smtp.gmail.com';
            $email->SMTPAuth = true;
            $email->Username = "soportesppeziart@gmail.com";
            $email->Password = 'rlze ihlp jzns cnry';
            $email->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $email->Port = 587;
            $email->CharSet = 'UTF-8';
            $email->setFrom('soportesppeziart@gmail.com', 'SPEZZIART');
            $email->addAddress($para, $nombre);
            $email->isHTML(true);
            $email->Subject = $asunto;
            $email->Body = $mensaje;

            $email->send();

            return true;
            
        } catch (Exception $e) {

            return false;
        }
    }
}
