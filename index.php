<?php
/**
 * Główny plik indeksowy projektu.
 * Wersja: v1.8
 * Autor: Konrad Sendlewski
 * Opis: Plik odpowiada za ładowanie struktury strony, nagłówka, menu oraz dynamicznej treści z bazy danych.
 */

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING); // Raportowanie błędów z pominięciem ostrzeżeń

include('cfg.php'); // Dołączenie konfiguracji i połączenia z bazą danych
include('koszyk.php');
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
            <li><a href="index.php?idp=3">Strona 3</a></li>
            <li><a href="index.php?idp=4">Strona 4</a></li>
            <li><a href="index.php?idp=5">Strona 5</a></li>
            <li><a href="index.php?idp=6">Filmy</a></li>
            <li><a href="index.php?idp=contact">Kontakt</a></li>
        </ul>
    </nav>

    <main>
    <?php
    // =============================================
    // MECHANIZM ŁADOWANIA TREŚCI
    // =============================================

    // Pobranie ID strony z parametru GET.
    // Zabezpieczenie: htmlspecialchars chroni przed XSS, jeśli wyświetlamy zmienną.
    $idp = isset($_GET['idp']) ? htmlspecialchars($_GET['idp']) : '1'; 

    // Obsługa specjalnych podstron (np. formularz kontaktowy)
    if ($idp === 'contact') {
        include('contact.php');
        // Jeśli formularz został wysłany (POST), obsłuż akcję
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'send_contact') {
                // Wywołanie funkcji z contact.php
                WyslijMailKontakt('twoj_email@example.com'); 
            } elseif ($action === 'remind_password') {
                PrzypomnijHaslo($conn);
            } else {
                PokazKontakt();
            }
        } else {
            // Domyślnie pokaż formularz
            PokazKontakt();
        }

    } else {
        // Standardowa obsługa stron dynamicznych z bazy danych
        
        $idp_int = intval($idp);
        if ($idp_int == 0) $idp_int = 1;

        // --- SKLEP NA STRONIE 4 ---
        // --- SKLEP NA STRONIE 4 ---
        if ($idp_int == 4) {
            
            // ---------------------------------------------------------
            // 1. OBSŁUGA LOGIKI KOSZYKA (Dodawanie / Usuwanie)
            // ---------------------------------------------------------
            if (isset($_POST['action'])) {
                if ($_POST['action'] == 'add_to_cart') {
                    // Funkcja z pliku koszyk.php
                    DodajDoKoszyka(); 
                }
                if ($_POST['action'] == 'clear_cart') {
                    // Funkcja z pliku koszyk.php
                    UsunKoszyk();
                }
            }

            echo "<article class='page'>";
            echo "<h2>Sklep z Widokówkami 🏔️🌊</h2>";
            
            // ---------------------------------------------------------
            // 2. WIDOK KOSZYKA (Na górze strony)
            // ---------------------------------------------------------
            // Wyświetla zieloną ramkę z zakupami (funkcja z koszyk.php)
            echo PokazKoszyk();

            echo "<hr>";

            // ---------------------------------------------------------
            // 3. WIDOK DRZEWA KATEGORII (Zadanie 1)
            // ---------------------------------------------------------
            echo "<div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 30px;'>";
            echo "<h3>Kategorie:</h3>";
            
            $sql_mothers = "SELECT * FROM categories WHERE matka = 0 ORDER BY nazwa ASC";
            $result = $conn->query($sql_mothers);
            
            if ($result->num_rows > 0) {
                echo '<ul class="shop-categories">';
                while ($matka = $result->fetch_assoc()) {
                    echo '<li><strong>' . htmlspecialchars($matka['nazwa']) . '</strong>';
                    
                    // Pobierz podkategorie (dzieci)
                    $mid = $matka['id'];
                    $sql_kids = "SELECT * FROM categories WHERE matka = $mid ORDER BY nazwa ASC";
                    $res_kids = $conn->query($sql_kids);
                    
                    if ($res_kids->num_rows > 0) {
                        echo '<ul>';
                        while ($dziecko = $res_kids->fetch_assoc()) {
                            echo '<li>' . htmlspecialchars($dziecko['nazwa']) . '</li>';
                        }
                        echo '</ul>';
                    }
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo "<p>Brak kategorii.</p>";
            }
            echo "</div>"; 

            echo "<hr>";
            
            // ---------------------------------------------------------
            // 4. LISTA PRODUKTÓW (Zadanie 2)
            // ---------------------------------------------------------
            echo "<h3>Nasze Produkty:</h3>";

            // Pobieramy tylko dostępne produkty
            $dzis = date('Y-m-d');
            $sql_prod = "SELECT * FROM products 
                         WHERE status_dostepnosci = 1 
                         AND ilosc_magazyn > 0 
                         AND (data_wygasniecia IS NULL OR data_wygasniecia >= '$dzis')
                         ORDER BY id DESC";
            
            $result_prod = $conn->query($sql_prod);
            
            if ($result_prod && $result_prod->num_rows > 0) {
                echo "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";
                
                while($row = $result_prod->fetch_assoc()) {
                    // Obliczamy cenę brutto
                    $brutto = $row['cena_netto'] + ($row['cena_netto'] * $row['podatek_vat'] / 100);
                    $cena_display = number_format($brutto, 2);
                    
                    echo "<div class='product-card' style='border:1px solid #ddd; padding:15px; width: 250px; border-radius:8px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>";
                    
                    // Zdjęcie
                    if(!empty($row['zdjecie'])) {
                        echo "<img src='{$row['zdjecie']}' alt='foto' style='width:100%; height:150px; object-fit:cover; border-radius:5px; margin-bottom: 10px;'>";
                    } else {
                         echo "<div style='width:100%; height:150px; background:#eee; display:flex; align-items:center; justify-content:center; margin-bottom: 10px; color:#aaa;'>Brak foto</div>";
                    }

                    // Tytuł i Cena
                    echo "<h3 style='margin: 0 0 10px 0; font-size: 1.1em;'>{$row['tytul']}</h3>";
                    echo "<p style='margin: 5px 0;'>Cena: <strong style='color:#d9534f; font-size:1.2em;'>{$cena_display} zł</strong></p>";
                    echo "<p style='font-size:0.9em; color:#555; height: 40px; overflow: hidden;'>" . htmlspecialchars(substr($row['opis'], 0, 100)) . "...</p>";
                    
                    // FORMULARZ DODAWANIA DO KOSZYKA
                    echo '<form method="post" action="index.php?idp=4">';
                    echo '  <input type="hidden" name="action" value="add_to_cart">';
                    echo '  <input type="hidden" name="id" value="'.$row['id'].'">';
                    echo '  <input type="hidden" name="tytul" value="'.htmlspecialchars($row['tytul']).'">';
                    echo '  <input type="hidden" name="cena" value="'.$brutto.'">';
                    echo '  <button type="submit" style="background:#007bff; color:white; border:none; padding:10px; width:100%; cursor:pointer; border-radius: 4px; margin-top: 10px; transition:0.3s;">Dodaj do koszyka 🛒</button>';
                    echo '</form>';

                    echo "</div>";
                }
                echo "</div>";
            } else {
                echo "<p>Obecnie brak dostępnych produktów w sklepie.</p>";
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
    echo "<p>Autor: <strong>Konrad Sendlewski</strong> | Indeks: $nr_indeksu | Grupa: $nrGrupy | Wersja: v1.8</p>";
    ?>
    </footer>

</body>
</html>