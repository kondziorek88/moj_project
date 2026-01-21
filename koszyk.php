<?php
// koszyk.php v1.9 - Poprawiony

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['koszyk'])) {
    $_SESSION['koszyk'] = [];
}

function DodajDoKoszyka($link) {
    $id = intval($_POST['id']);
    $tytul = htmlspecialchars($_POST['tytul']);
    $cena = floatval($_POST['cena']);
    $ile = 1;

    $row = mysqli_fetch_assoc(mysqli_query($link, "SELECT ilosc_magazyn FROM products WHERE id=$id LIMIT 1"));
    if (!$row) return; // Zabezpieczenie jeśli produkt nie istnieje
    
    $stan_magazynowy = $row['ilosc_magazyn'];
    $ile_w_koszyku = isset($_SESSION['koszyk'][$id]) ? $_SESSION['koszyk'][$id]['ile'] : 0;

    if ($ile_w_koszyku + $ile > $stan_magazynowy) {
        echo "<script>alert('Nie mamy więcej sztuk tego produktu na magazynie!'); window.location='index.php?idp=4';</script>";
        exit(); // Ważne: przerywamy skrypt, żeby JS zadziałał
    }

    if (isset($_SESSION['koszyk'][$id])) {
        $_SESSION['koszyk'][$id]['ile']++;
    } else {
        $_SESSION['koszyk'][$id] = [
            'id' => $id, 'tytul' => $tytul, 'cena' => $cena, 'ile' => $ile, 'data' => time()
        ];
    }
    header("Location: index.php?idp=4"); // Przekierowanie (czyści POST)
    exit();
}

function UsunZKoszyka() {
    $id = intval($_POST['id']);
    if (isset($_SESSION['koszyk'][$id])) unset($_SESSION['koszyk'][$id]);
    header("Location: index.php?idp=4");
    exit();
}

function ZmienIlosc($link) {
    $id = intval($_POST['id']);
    $typ = $_POST['typ'];

    if (isset($_SESSION['koszyk'][$id])) {
        if ($typ == 'plus') {
            $row = mysqli_fetch_assoc(mysqli_query($link, "SELECT ilosc_magazyn FROM products WHERE id=$id LIMIT 1"));
            if ($_SESSION['koszyk'][$id]['ile'] + 1 > $row['ilosc_magazyn']) {
                echo "<script>alert('To już wszystkie dostępne sztuki!'); window.location='index.php?idp=4';</script>";
                exit();
            }
            $_SESSION['koszyk'][$id]['ile']++;
        } elseif ($typ == 'minus') {
            $_SESSION['koszyk'][$id]['ile']--;
            if ($_SESSION['koszyk'][$id]['ile'] <= 0) unset($_SESSION['koszyk'][$id]);
        }
    }
    header("Location: index.php?idp=4");
    exit();
}

// Nowa funkcja przeniesiona z index.php
function FinalizujZakup($link) {
    if (isset($_SESSION['koszyk']) && count($_SESSION['koszyk']) > 0) {
        foreach ($_SESSION['koszyk'] as $id_prod => $item) {
            $ile_kupiono = intval($item['ile']);
            $id_prod = intval($id_prod);

            // Aktualizacja magazynu
            $link->query("UPDATE products SET ilosc_magazyn = ilosc_magazyn - $ile_kupiono WHERE id = $id_prod");
            // Sprawdzenie dostępności
            $link->query("UPDATE products SET status_dostepnosci = 0 WHERE id = $id_prod AND ilosc_magazyn <= 0");
        }
        unset($_SESSION['koszyk']);
        // Przekierowanie z parametrem sukcesu
        header("Location: index.php?idp=4&msg=zakup_udany");
        exit();
    } else {
         header("Location: index.php?idp=4");
         exit();
    }
}

function PokazKoszyk() {
    // ... (Twoja funkcja PokazKoszyk bez zmian) ...
    // Skopiuj tutaj funkcję PokazKoszyk z poprzedniej odpowiedzi
    // Upewnij się tylko, że zwraca HTML, a nie robi echo
    
    // Skrócona wersja dla przypomnienia struktury:
    if (empty($_SESSION['koszyk'])) {
        return "<div style='border: 2px dashed #ccc; padding: 15px; text-align: center; color: #777; margin-bottom: 20px;'>Twój koszyk jest pusty 🛒</div>";
    }
    $suma = 0;
    $html = "<div class='cart-container' style='margin-bottom: 30px; border: 1px solid #28a745; padding: 15px; background: #fff;'><h3>🛒 Twój Koszyk</h3><table style='width: 100%; border-collapse: collapse;'>";
    foreach ($_SESSION['koszyk'] as $id => $item) {
        $wartosc = $item['cena'] * $item['ile'];
        $suma += $wartosc;
        $html .= "<tr style='border-bottom: 1px solid #eee;'><td style='padding:8px;'><b>{$item['tytul']}</b></td>
        <td style='padding:8px; display:flex; gap:5px;'>
        <form method='post' action='index.php?idp=4' style='display:inline;'><input type='hidden' name='action' value='update_qty'><input type='hidden' name='id' value='$id'><input type='hidden' name='typ' value='minus'><button>-</button></form>
        <b>{$item['ile']}</b>
        <form method='post' action='index.php?idp=4' style='display:inline;'><input type='hidden' name='action' value='update_qty'><input type='hidden' name='id' value='$id'><input type='hidden' name='typ' value='plus'><button>+</button></form>
        </td><td style='padding:8px;'>".number_format($wartosc,2)." zł</td>
        <td style='padding:8px;'><form method='post' action='index.php?idp=4'><input type='hidden' name='action' value='remove_item'><input type='hidden' name='id' value='$id'><button style='color:red'>X</button></form></td></tr>";
    }
    $html .= "</table><div style='text-align: right; margin-top: 15px;'>DO ZAPŁATY: <b>".number_format($suma,2)." zł</b></div><hr>";
    $html .= "<form method='post' action='index.php?idp=4' style='text-align: right;'><input type='hidden' name='action' value='checkout'><input type='submit' value='Finalizuj Zakup (Zapłać) ✅' style='background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px;'></form></div>";
    return $html;
}
?>