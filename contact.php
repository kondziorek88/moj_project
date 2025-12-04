<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'mail.php';


// Załaduj PHPMailer
require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

function PokazKontakt() {
    echo '
    <h2>Formularz kontaktowy</h2>
    <form method="POST">
        <label>Imię:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Wiadomość:</label><br>
        <textarea name="message" rows="5" required></textarea><br><br>

        <input type="hidden" name="action" value="send_contact">
        <button type="submit">Wyślij wiadomość</button>
    </form>

    <hr>

    <h3>Przypomnienie hasła</h3>
    <form method="POST">
        <label>Adres email admina:</label><br>
        <input type="email" name="admin_email" required><br><br>

        <input type="hidden" name="action" value="remind_password">
        <button type="submit">Przypomnij hasło</button>
    </form>
    ';
}

function WyslijMailKontakt($odbiorca) {
    $mail = new PHPMailer(true);

    try {
        // Konfiguracja SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Gmail SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'twoj_email@gmail.com'; // <-- TWÓJ EMAIL
        $mail->Password = 'haslo_do_smtp';        // <-- HASŁO APLIKACJI
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Nadawca
        $mail->setFrom($_POST['email'], $_POST['name']);

        // Odbiorca
        $mail->addAddress($odbiorca);

        // Treść
        $mail->isHTML(true);
        $mail->Subject = "Wiadomość ze strony";
        $mail->Body = "
            <b>Imię:</b> {$_POST['name']}<br>
            <b>Email:</b> {$_POST['email']}<br><br>
            <b>Wiadomość:</b><br>{$_POST['message']}
        ";

        $mail->send();
        echo "<p class='success'>Wiadomość została wysłana poprawnie.</p>";

    } catch (Exception $e) {
        echo "<p class='error'>Błąd przy wysyłaniu maila: {$mail->ErrorInfo}</p>";
    }
}

function PrzypomnijHaslo($conn) {
    $email = $_POST['admin_email'];

    $sql = "SELECT * FROM admin_users WHERE email='$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows == 0) {
        echo "<p class='error'>Nie znaleziono użytkownika o tym emailu!</p>";
        return;
    }

    $user = $result->fetch_assoc();
    $haslo = $user['password'];

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'twoj_email@gmail.com'; // TWÓJ EMAIL
        $mail->Password = 'haslo_do_smtp';        // HASŁO APLIKACJI
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('twoj_email@gmail.com', 'System przypomnienia hasła');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Przypomnienie hasła";
        $mail->Body = "
            <p>Twoje hasło administratora to:</p>
            <h3>$haslo</h3>
        ";

        $mail->send();
        echo "<p class='success'>Hasło zostało wysłane na email!</p>";

    } catch (Exception $e) {
        echo "<p class='error'>Błąd wysyłki maila: {$mail->ErrorInfo}</p>";
    }
}
?>
