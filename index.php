<?php
/**
 * Główny plik indeksowy projektu.
 * Wersja: v1.9 (z poprawką odświeżania)
 */

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
include('cfg.php');
include('koszyk.php'); // Dołączamy logikę koszyka tutaj
include('contact.php');

// =============================================
// LOGIKA KOSZYKA (Musi być przed HTML!)
// =============================================


if (isset($_POST['action'])) {
    // Dodawanie
    if ($_POST['action'] == 'add_to_cart') {
        DodajDoKoszyka($conn); 
    }
    // Usuwanie
    if ($_POST['action'] == 'remove_item') {
        UsunZKoszyka();
    }
    // Zmiana ilości
    if ($_POST['action'] == 'update_qty') {
        ZmienIlosc($conn);
    }
    // Finalizacja zakupu
    if ($_POST['action'] == 'checkout') {
        FinalizujZakup($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Projekt PHP - Fotografia">
    <meta name="keywords" content="HTML, CSS, JS, PHP, projekt, fotografia">
    <meta name="author" content="Konrad Sendlewski">
    <title>Fotografia - moja pasja (v1.8)</title>
    <link rel="stylesheet" href="styles.css">
    <script src="timedate.js" type="text/javascript"></script>
</head>

<body onload="startclock()">

    <div id="zegarek"></div>
    <div id="data"></div>

    <header>
        <h1>📸 Fotografia - moja pasja</h1>
    </header>

    <nav>
        <ul class="menu">
            <li><a href="index.php?idp=1">Strona główna</a></li>
            <li><a href="index.php?idp=2">Sprzęt</a></li>
            <li><a href="index.php?idp=3">Podróże i ciekawe miejsca</a></li>
            <li><a href="index.php?idp=4">Sklep</a></li>
            <li><a href="index.php?idp=5">Brak światła?</a></li>
            <li><a href="index.php?idp=6">Filmy</a></li>
            <li><a href="index.php?idp=contact">Kontakt</a></li>
        </ul>
    </nav>

    <main>
    <?php

    $idp = isset($_GET['idp']) ? htmlspecialchars($_GET['idp']) : '1'; 

    // Obsługa specjalnych podstron (np. formularz kontaktowy)
    if ($idp == 'contact') {
    echo "<article class='page'>"; // Dodajemy kontener dla stylu
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'send_contact') {
            echo WyslijMailKontakt($_POST['email']); 
        } elseif ($action === 'remind_password') {
            echo PrzypomnijHaslo();
        } else {
            echo PokazKontakt();
        }
    } else {
        // Domyślnie pokaż formularz
        echo PokazKontakt();
    }
    
    echo "</article>";

    } else {
        // Standardowa obsługa stron dynamicznych z bazy danych
        
        $idp_int = intval($idp);
        if ($idp_int == 0) $idp_int = 1;

        // --- SKLEP NA STRONIE 4 ---
        if ($idp_int == 4) {

            // Komunikat po udanym zakupie
            if (isset($_GET['msg']) && $_GET['msg'] == 'zakup_udany') {
                echo "<div style='background: #27ae60; color: #fff; padding: 20px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #2ecc71;'>
                        <h2>Dziękujemy za zakupy! 🎉</h2>
                        <p>Twoje zamówienie zostało przyjęte.</p>
                      </div>";
            }

            echo "<article class='page'>";
            
            // Wyświetlenie koszyka
            echo PokazKoszyk(); 
            echo "<hr>";

            // =========================================================
            // WIDOK SZCZEGÓŁOWY PRODUKTU
            // =========================================================
            if (isset($_GET['product_id'])) {
                // ... (Ten fragment kodu pozostaje BEZ ZMIAN, jak w poprzedniej wersji) ...
                // Skopiuj go z poprzedniego działającego kodu lub zostaw jak masz
                // Żeby nie wydłużać kodu tutaj, skupiam się na widoku listy poniżej.
                $id_prod = intval($_GET['product_id']);
                $sql = "SELECT * FROM products WHERE id = $id_prod LIMIT 1";
                $result = $conn->query($sql);
                
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $cena_netto = $row['cena_netto'];
                    $vat = $row['podatek_vat'];
                    $cena_brutto = $cena_netto + ($cena_netto * $vat / 100);
                    $img_path = $row['zdjecie'];
                    if (substr($img_path, 0, 3) === '../') { $img_path = substr($img_path, 3); }

                    echo "<div class='product-details'>";
                    echo "<a href='index.php?idp=4' style='display:inline-block; margin-bottom:15px; font-weight:bold;'>&larr; Wróć do sklepu</a>";
                    echo "<h2 style='margin-top:0;'>{$row['tytul']}</h2>";
                    echo "<div style='display:flex; flex-wrap:wrap; gap:30px;'>";
                        echo "<div style='flex:1; min-width:300px;'>";
                        if(!empty($img_path)) echo "<img src='{$img_path}' style='width:100%; height:auto; border-radius:8px; border:1px solid #444;'>";
                        else echo "<div style='height:300px; background:#2c2c2c; color:#aaa; display:flex; align-items:center; justify-content:center;'>Brak zdjęcia</div>";
                        echo "</div>";
                        echo "<div style='flex:1; min-width:300px;'>";
                            echo "<table style='width:100%; margin-bottom:20px;'><tr><td>Cena Netto:</td><td style='text-align:right;'>".number_format($cena_netto, 2)." zł</td></tr><tr><td>VAT:</td><td style='text-align:right;'>{$vat}%</td></tr><tr style='color:#3498db; font-size:1.3em;'><td><strong>Brutto:</strong></td><td style='text-align:right;'><strong>".number_format($cena_brutto, 2)." zł</strong></td></tr></table>";
                            if ($row['status_dostepnosci'] == 1 && $row['ilosc_magazyn'] > 0) {
                                echo "<p style='color:#2ecc71;'>🟢 Produkt dostępny ({$row['ilosc_magazyn']} szt.)</p>";
                                echo '<form method="post" action="index.php?idp=4&product_id='.$id_prod.'"><input type="hidden" name="action" value="add_to_cart"><input type="hidden" name="id" value="'.$row['id'].'"><input type="hidden" name="tytul" value="'.htmlspecialchars($row['tytul']).'"><input type="hidden" name="cena" value="'.$cena_brutto.'"><button type="submit" style="width:100%; padding:15px; margin-top:10px;">Dodaj do koszyka 🛒</button></form>';
                            } else { echo "<p style='color:#e74c3c;'>🔴 Produkt niedostępny</p>"; }
                            echo "<p style='margin-top:20px; font-size:0.9em; opacity:0.7;'>Gabaryt: ".htmlspecialchars($row['gabaryt'])."</p>";
                        echo "</div>";
                    echo "</div>";
                    echo "<hr style='border-color:#444;'><h3>Opis:</h3><div class='product-description' style='line-height:1.8; opacity:0.9;'>".nl2br(htmlspecialchars($row['opis']))."</div></div>";
                }
            } 
            // =========================================================
            // WIDOK LISTY PRODUKTÓW (Z FILTRACJĄ)
            // =========================================================
            else {
                echo "<h2>Sklep z Widokówkami 🏔️🌊</h2>";

                // --- 1. KATEGORIE (LINKI) ---
                echo "<div style='background: var(--card-bg); padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #333;'>";
                echo "<div style='display:flex; justify-content:space-between; align-items:center;'>";
                    echo "<h3 style='margin:0; border:none;'>Kategorie:</h3>";
                    // Przycisk resetowania filtra (pokaż wszystkie)
                    if (isset($_GET['cat_id'])) {
                        echo "<a href='index.php?idp=4' style='font-size:0.9em; color:#e74c3c;'>✕ Wyczyść filtr</a>";
                    }
                echo "</div>";
                echo "<hr style='border-color:#444; margin:15px 0;'>";

                $sql_mothers = "SELECT * FROM categories WHERE matka = 0 ORDER BY nazwa ASC";
                $result = $conn->query($sql_mothers);
                
                if ($result->num_rows > 0) {
                    echo '<ul class="shop-categories">';
                    while ($matka = $result->fetch_assoc()) {
                        $mid = $matka['id'];
                        // Link dla kategorii głównej
                        // Dodajemy klasę 'active' jeśli to ta kategoria jest wybrana
                        $active_style = (isset($_GET['cat_id']) && $_GET['cat_id'] == $mid) ? "color: #3498db; text-decoration:underline;" : "color: inherit;";
                        
                        echo '<li>';
                        echo '<a href="index.php?idp=4&cat_id='.$mid.'" style="text-decoration:none; '.$active_style.'"><strong>' . htmlspecialchars($matka['nazwa']) . '</strong></a>';

                        $sql_kids = "SELECT * FROM categories WHERE matka = $mid ORDER BY nazwa ASC";
                        $res_kids = $conn->query($sql_kids);
                        if ($res_kids->num_rows > 0) {
                            echo '<ul>';
                            while ($dziecko = $res_kids->fetch_assoc()) {
                                $kid = $dziecko['id'];
                                $active_style_kid = (isset($_GET['cat_id']) && $_GET['cat_id'] == $kid) ? "color: #3498db; font-weight:bold;" : "color: inherit;";
                                
                                // Link dla podkategorii
                                echo '<li><a href="index.php?idp=4&cat_id='.$kid.'" style="text-decoration:none; '.$active_style_kid.'">' . htmlspecialchars($dziecko['nazwa']) . '</a></li>';
                            }
                            echo '</ul>';
                        }
                        echo '</li>';
                    }
                    echo '</ul>';
                }
                echo "</div>"; 

                echo "<hr style='border-color: #444;'>";
                
                // --- 2. PRODUKTY (FILTRACJA SQL) ---
                $title_text = "Nasze Produkty:";
                
                // Bazowe zapytanie
                $sql_prod = "SELECT * FROM products 
                             WHERE status_dostepnosci = 1 
                             AND ilosc_magazyn > 0 
                             AND (data_wygasniecia IS NULL OR data_wygasniecia >= CURRENT_DATE())";
                
                // >>> LOGIKA FILTRACJI <<<
                if (isset($_GET['cat_id'])) {
                    $cat_filter = intval($_GET['cat_id']);
                    // Dodajemy warunek do zapytania SQL
                    $sql_prod .= " AND kategoria = $cat_filter";
                    $title_text = "Produkty z wybranej kategorii:";
                }

                $sql_prod .= " ORDER BY id DESC"; // Sortowanie na końcu
                
                echo "<h3>$title_text</h3>";

                $result_prod = $conn->query($sql_prod);
                
                if ($result_prod && $result_prod->num_rows > 0) {
                    echo "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";
                    
                    while($row = $result_prod->fetch_assoc()) {
                        $brutto = $row['cena_netto'] + ($row['cena_netto'] * $row['podatek_vat'] / 100);
                        $cena_display = number_format($brutto, 2);
                        $img_path = $row['zdjecie'];
                        if (substr($img_path, 0, 3) === '../') { $img_path = substr($img_path, 3); }

                        echo "<div class='product-card' style='padding:15px; width: 250px; border-radius:8px; display:flex; flex-direction:column; justify-content:space-between;'>";
                        
                        echo "<a href='index.php?idp=4&product_id={$row['id']}' style='text-decoration:none; color:inherit;'>";
                            if(!empty($img_path)) {
                                echo "<img src='{$img_path}' alt='foto' style='width:100%; height:150px; object-fit:cover; border-radius:5px; margin-bottom: 10px;'>";
                            } else {
                                echo "<div style='width:100%; height:150px; background:#2c2c2c; display:flex; align-items:center; justify-content:center; margin-bottom: 10px; color:#aaa; border-radius:5px;'>Brak foto</div>";
                            }
                            echo "<h3 style='margin: 0 0 10px 0; font-size: 1.1em;'>{$row['tytul']}</h3>";
                        echo "</a>";

                        echo "<div>";
                            echo "<p style='margin: 5px 0;'>Cena: <strong style='color:#3498db; font-size:1.2em;'>{$cena_display} zł</strong></p>";
                            echo "<p style='font-size:0.8em; color:#2ecc71;'>Dostępne: <b>{$row['ilosc_magazyn']} szt.</b></p>";
                            
                            echo "<a href='index.php?idp=4&product_id={$row['id']}' style='display:block; text-align:center; margin:10px 0; font-size:0.9em;'>Zobacz szczegóły &raquo;</a>";

                            echo '<form method="post" action="index.php?idp=4">';
                            echo '  <input type="hidden" name="action" value="add_to_cart">';
                            echo '  <input type="hidden" name="id" value="'.$row['id'].'">';
                            echo '  <input type="hidden" name="tytul" value="'.htmlspecialchars($row['tytul']).'">';
                            echo '  <input type="hidden" name="cena" value="'.$brutto.'">';
                            echo '  <button type="submit" style="width:100%; padding:10px; margin-top:5px;">Dodaj do koszyka 🛒</button>';
                            echo '</form>';
                        echo "</div>";

                        echo "</div>";
                    }
                    echo "</div>";
                } else {
                    echo "<div style='padding: 30px; text-align: center; border: 1px dashed #555; border-radius: 8px; color: #999;'>
                            <p>Brak produktów spełniających kryteria.</p>
                            <a href='index.php?idp=4' style='color: #3498db;'>Wróć do wszystkich produktów</a>
                          </div>";
                }
            }

            echo "</article>";
        
        } else {
            // --- RESZTA STRON (Z BAZY) ---
            $sql = "SELECT * FROM page_list WHERE id = $idp_int AND status = 1 LIMIT 1";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo "<article class='page'>";
                echo "<h2>{$row['page_title']}</h2>";
                echo "<div class='page-content'>{$row['page_content']}</div>";
                echo "</article>";
            } else {
                echo "<p class='error'>Nie znaleziono strony.</p>";
            }
        }
    }
    ?>


    </main>

    <footer>
    <?php
    $nr_indeksu = '175495';
    $nrGrupy = 'ISI3';
    echo "<p>Autor: <strong>Konrad Sendlewski</strong> | Indeks: $nr_indeksu | Grupa: $nrGrupy</p>";
    ?>
    </footer>

</body>
</html>