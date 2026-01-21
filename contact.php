<?php
/**
 * Moduł Kontakt - Wersja Dark Mode (Kompatybilna z Twoją logiką)
 */

// 1. Wyświetlanie formularza
function PokazKontakt() {
    return "
    <div class='contact-container' style='max-width: 600px; margin: 0 auto;'>
        <h2 style='text-align: center; margin-bottom: 10px; color: inherit;'>Skontaktuj się ze mną 👋</h2>
        <p style='text-align: center; color: #bbb; margin-bottom: 30px;'>Masz pytania? Napisz!</p>

        <form method='post' action='index.php?idp=contact' style='background: #1e1e1e; padding: 30px; border-radius: 10px; border: 1px solid #333; box-shadow: 0 4px 15px rgba(0,0,0,0.2);'>
            <input type='hidden' name='action' value='send_contact'>

            <label style='display: block; margin-bottom: 5px; color: #3498db; font-weight: bold;'>Twój Email:</label>
            <input type='email' name='email' required 
                   style='width: 100%; padding: 12px; background: #2c2c2c; border: 1px solid #444; color: #fff; border-radius: 5px; margin-bottom: 20px;'>

            <label style='display: block; margin-bottom: 5px; color: #3498db; font-weight: bold;'>Temat:</label>
            <input type='text' name='temat' required 
                   style='width: 100%; padding: 12px; background: #2c2c2c; border: 1px solid #444; color: #fff; border-radius: 5px; margin-bottom: 20px;'>

            <label style='display: block; margin-bottom: 5px; color: #3498db; font-weight: bold;'>Wiadomość:</label>
            <textarea name='tresc' rows='6' required 
                      style='width: 100%; padding: 12px; background: #2c2c2c; border: 1px solid #444; color: #fff; border-radius: 5px; margin-bottom: 20px;'></textarea>

            <button type='submit' 
                    style='width: 100%; padding: 15px; background: #3498db; color: white; border: none; border-radius: 5px; font-size: 1.1em; cursor: pointer; transition: 0.3s;'>
                Wyślij wiadomość 🚀
            </button>
        </form>
        
        <div style='text-align: center; margin-top: 20px;'>
            <form method='post' action='index.php?idp=contact'>
                <input type='hidden' name='action' value='remind_password'>
                <button type='submit' style='background:none; border:none; color:#777; cursor:pointer; text-decoration:underline;'>Zapomniałem hasła</button>
            </form>
        </div>
    </div>";
}

// 2. Obsługa wysyłki (Symulacja)
function WyslijMailKontakt($odbiorca) {
    if (empty($odbiorca)) {
        // Jeśli brak maila, pokaż formularz ponownie
        return PokazKontakt();
    }

    // Tutaj normalnie byłby kod mail(), np:
    // mail("admin@sklep.pl", "Formularz", $_POST['tresc']);

    // Zwracamy komunikat sukcesu + formularz (żeby można było wysłać kolejną wiadomość)
    $msg = "<div style='background: #27ae60; color: #fff; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #2ecc71; text-align: center; max-width: 600px; margin: 0 auto;'>
                Wiadomość została wysłana do: <b>".htmlspecialchars($odbiorca)."</b> (Symulacja) 📨
            </div>";
            
    return $msg . PokazKontakt();
}

// 3. Przypominanie hasła
function PrzypomnijHaslo() {
    return "
    <div class='contact-container' style='max-width: 400px; margin: 50px auto; text-align: center;'>
        <h3 style='color:inherit;'>Zapomniałeś hasła? 🔒</h3>
        <form method='post' action='index.php?idp=contact' style='background: #1e1e1e; padding: 20px; border-radius: 10px; border: 1px solid #333;'>
            <input type='hidden' name='action' value='send_password_reset'>
            
            <input type='email' name='email_rec' placeholder='Podaj swój email' required
                   style='width: 100%; padding: 10px; margin-bottom: 15px; background: #2c2c2c; border: 1px solid #444; color: white; border-radius:5px;'>
                   
            <button type='submit' style='width: 100%; padding: 10px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor:pointer;'>Resetuj hasło</button>
        </form>
        <br>
        <a href='index.php?idp=contact' style='color:#777;'>&larr; Wróć do kontaktu</a>
    </div>";
}
?>