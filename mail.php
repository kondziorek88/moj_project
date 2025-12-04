<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function WyslijMailKontakt()
{
    if (!isset($_POST['imie']) || !isset($_POST['email']) || !isset($_POST['tresc'])) {
        echo "<p class='error'>Brak danych z formularza!</p>";
        return;
    }

    $mail = new PHPMailer(true);

    try {
        // --- KONFIGURACJA SMTP WP.PL ---
        $mail->isSMTP();
        $mail->Host = 'smtp.wp.pl';
        $mail->SMTPAuth = true;
        $mail->Username = 'kondziorek88pl@wp.pl';   // <-- tu podaj email
        $mail->Password = 'twoje_haslo';        // <-- tu podaj hasło
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // --- NADAWCA I ODBIORCA ---
        $mail->setFrom('twoj_email@wp.pl', 'Formularz kontaktowy');
        $mail->addAddress('twoj_email@wp.pl'); // Wyślij do siebie

        // --- TREŚĆ E-MAILA ---
        $mail->isHTML(true);
        $mail->Subject = "Wiadomość ze strony WWW";

        $mail->Body = "
            <h3>Nowa wiadomość z formularza:</h3>
            <p><strong>Imię:</strong> {$_POST['imie']}</p>
            <p><strong>Email:</strong> {$_POST['email']}</p>
            <p><strong>Treść:</strong><br>{$_POST['tresc']}</p>
        ";

        $mail->send();
        echo "<p class='ok'>Wiadomość została wysłana pomyślnie!</p>";

    } catch (Exception $e) {
        echo "<p class='error'>Błąd wysyłania: {$mail->ErrorInfo}</p>";
    }
}
