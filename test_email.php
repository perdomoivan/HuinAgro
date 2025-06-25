<?php
require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'dariopp432@gmail.com';
    $mail->Password = 'duyl wkzv bvnb gftj'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('dariopp432@gmail.com', 'HuinAgro Test');
    $mail->addAddress('dariopp432@gmail.com'); 
    $mail->Subject = 'Prueba de PHPMailer';
    $mail->Body = 'Este es un correo de prueba enviado desde PHPMailer.';

    $mail->SMTPDebug = 3;
    $mail->Debugoutput = 'html';

    $mail->send();
    echo 'Correo enviado exitosamente.';
} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
    echo "<br>Excepción: {$e->getMessage()}";
}
?>