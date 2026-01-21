<?php
/**
 * Moduł obsługi koszyka sklepowego (v2.0 - Dark Mode Ready).
 */

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
    if (!$row) return;
    
    $stan_magazynowy = $row['ilosc_magazyn'];
    $ile_w_koszyku = isset($_SESSION['koszyk'][$id]) ? $_SESSION['koszyk'][$id]['ile'] : 0;

    if ($ile_w_koszyku + $ile > $stan_magazynowy) {
        echo "<script>alert('Nie mamy więcej sztuk tego produktu na magazynie!'); window.location='index.php?idp=4';</script>";
        exit();
    }

    if (isset($_SESSION['koszyk'][$id])) {
        $_SESSION['koszyk'][$id]['ile']++;
    } else {
        $_SESSION['koszyk'][$id] = [
            'id' => $id, 'tytul' => $tytul, 'cena' => $cena, 'ile' => $ile, 'data' => time()
        ];
    }
    header("Location: index.php?idp=4");
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
        header("Location: index.php?idp=4&msg=zakup_udany");
        exit();
    } else {
         header("Location: index.php?idp=4");
         exit();
    }
}

/**
 * Wyświetla koszyk (Style przeniesione do CSS - klasa .cart-container)
 */
function PokazKoszyk() {
    // Pusty koszyk
    if (empty($_SESSION['koszyk'])) {
        return "<div class='cart-container' style='text-align: center; color: #aaa; padding: 20px;'>Twój koszyk jest pusty 🛒</div>";
    }

    $suma = 0;
    
    // USUNĘLIŚMY style="background: #fff" - teraz używa klasy .cart-container z CSS
    $html = "<div class='cart-container'>";
    $html .= "<h3>🛒 Twój Koszyk</h3>";
    
    // Tabela korzysta teraz ze stylów CSS dla table/th/td
    $html .= "<table>";
    $html .= "<tr>
                <th>Produkt</th>
                <th>Ilość</th>
                <th>Wartość</th>
                <th>Akcje</th>
              </tr>";

    foreach ($_SESSION['koszyk'] as $id => $item) {
        $wartosc = $item['cena'] * $item['ile'];
        $suma += $wartosc;

        $html .= "<tr>";
        $html .= "<td><b>{$item['tytul']}</b></td>";
        
        // Przyciski edycji ilości
        $html .= "<td style='display:flex; align-items:center; gap:10px;'>
                    <form method='post' action='index.php?idp=4' style='display:inline;'>
                        <input type='hidden' name='action' value='update_qty'>
                        <input type='hidden' name='id' value='$id'>
                        <input type='hidden' name='typ' value='minus'>
                        <button type='submit' style='padding:2px 8px; font-size:0.9em;'>-</button>
                    </form>
                    
                    <span style='font-weight:bold; font-size:1.1em;'>{$item['ile']}</span>
                    
                    <form method='post' action='index.php?idp=4' style='display:inline;'>
                        <input type='hidden' name='action' value='update_qty'>
                        <input type='hidden' name='id' value='$id'>
                        <input type='hidden' name='typ' value='plus'>
                        <button type='submit' style='padding:2px 8px; font-size:0.9em;'>+</button>
                    </form>
                  </td>";
        
        $html .= "<td>" . number_format($wartosc, 2) . " zł</td>";
        
        // Przycisk usuwania
        $html .= "<td>
                    <form method='post' action='index.php?idp=4'>
                        <input type='hidden' name='action' value='remove_item'>
                        <input type='hidden' name='id' value='$id'>
                        <button type='submit' style='background-color:#e74c3c; padding:5px 10px; font-size:0.8em;'>Usuń</button>
                    </form>
                  </td>";
        $html .= "</tr>";
    }

    $html .= "</table>";
    
    $html .= "<div style='text-align: right; margin-top: 20px; font-size: 1.2em; color: #3498db;'>DO ZAPŁATY: <b>" . number_format($suma, 2) . " zł</b></div>";
    
    $html .= "<hr style='border-color: #444;'>";
    
    // Przycisk finalizacji
    $html .= "<form method='post' action='index.php?idp=4' style='text-align: right;'>
                <input type='hidden' name='action' value='checkout'>
                <input type='submit' value='Finalizuj Zakup (Zapłać) ✅' style='background: #27ae60; color: white; padding: 12px 25px; font-size: 1.1em; border: none; cursor: pointer; border-radius: 5px;'>
              </form>";
              
    $html .= "</div>";

    return $html;
}
?>