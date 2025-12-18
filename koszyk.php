<?php
/**
 * Moduł obsługi koszyka sklepowego.
 * Wersja: v1.8
 * Zarządza sesją $_SESSION['koszyk'].
 */

// Rozpoczynamy sesję, jeśli jeszcze nie wystartowała
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Dodaje produkt do koszyka sesyjnego.
 */
function DodajDoKoszyka() {
    $id = intval($_POST['id']);
    $tytul = htmlspecialchars($_POST['tytul']);
    $cena = floatval($_POST['cena']); // Cena brutto
    $ile = 1; // Domyślnie dodajemy 1 sztukę

    // Struktura produktu w koszyku
    $produkt = [
        'id' => $id,
        'tytul' => $tytul,
        'cena' => $cena,
        'ile' => $ile
    ];

    // Jeśli koszyk nie istnieje, stwórz go
    if (!isset($_SESSION['koszyk'])) {
        $_SESSION['koszyk'] = [];
    }

    // Sprawdź czy produkt już jest w koszyku - jeśli tak, zwiększ ilość
    $znaleziono = false;
    foreach ($_SESSION['koszyk'] as $key => $item) {
        if ($item['id'] == $id) {
            $_SESSION['koszyk'][$key]['ile']++;
            $znaleziono = true;
            break;
        }
    }

    // Jeśli nie znaleziono, dodaj nowy
    if (!$znaleziono) {
        $_SESSION['koszyk'][] = $produkt;
    }

    // Odśwież stronę, żeby wyczyścić POST (zapobiega dodaniu przy F5)
    header("Location: index.php?idp=4");
    exit();
}

/**
 * Wyświetla skrócony podgląd koszyka.
 */
function PokazKoszyk() {
    if (!isset($_SESSION['koszyk']) || count($_SESSION['koszyk']) == 0) {
        return "<div style='border: 2px dashed #ccc; padding: 10px; margin-bottom: 20px; text-align: center; color: #777;'>Twój koszyk jest pusty 🛒</div>";
    }

    $suma = 0;
    $html = "<div style='border: 2px solid #28a745; padding: 15px; margin-bottom: 20px; background: #f0fff4;'>";
    $html .= "<h3>🛒 Twój Koszyk</h3><ul>";

    foreach ($_SESSION['koszyk'] as $item) {
        $wartosc = $item['cena'] * $item['ile'];
        $suma += $wartosc;
        $html .= "<li><b>{$item['tytul']}</b> x{$item['ile']} - " . number_format($wartosc, 2) . " zł</li>";
    }

    $html .= "</ul>";
    $html .= "<h4 style='text-align: right; margin-top: 10px;'>RAZEM DO ZAPŁATY: " . number_format($suma, 2) . " zł</h4>";
    
    // Przycisk czyszczenia koszyka (opcjonalnie)
    $html .= '<form method="post" action="index.php?idp=4" style="text-align:right;">
                <input type="hidden" name="action" value="clear_cart">
                <input type="submit" value="Opróżnij koszyk 🗑️" style="background:#d9534f; color:white; border:none; padding:5px 10px; cursor:pointer;">
              </form>';
              
    $html .= "</div>";

    return $html;
}

/**
 * Czyści koszyk.
 */
function UsunKoszyk() {
    unset($_SESSION['koszyk']);
    header("Location: index.php?idp=4");
    exit();
}
?>