<?php
/**
 * Główny plik indeksowy projektu.
 * Wersja: v1.8
 * Autor: Konrad Sendlewski
 * Opis: Plik odpowiada za ładowanie struktury strony, nagłówka, menu oraz dynamicznej treści z bazy danych.
 */

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING); // Raportowanie błędów z pominięciem ostrzeżeń

include('cfg.php'); // Dołączenie konfiguracji i połączenia z bazą danych
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
        
        // Zabezpieczenie przed SQL Injection: rzutowanie na liczbę całkowitą (int)
        $idp_int = intval($idp);
        
        // Jeśli $idp_int to 0 (np. ktoś wpisał tekst zamiast liczby), ustawiamy domyślnie 1
        if ($idp_int == 0) $idp_int = 1;

        // Zapytanie SQL z limitem 1 rekordu dla optymalizacji
        $sql = "SELECT * FROM page_list WHERE id = $idp_int AND status = 1 LIMIT 1";
        $result = $conn->query($sql);

        // Wyświetlenie treści, jeśli znaleziono stronę
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "<article class='page'>";
            echo "<h2>{$row['page_title']}</h2>";
            // Wyświetlenie zawartości (HTML z bazy jest interpretowany)
            echo "<div class='page-content'>{$row['page_content']}</div>";
            echo "</article>";
        } else {
            echo "<p class='error'>Nie znaleziono strony o id = <strong>$idp_int</strong> lub jest ona nieaktywna.</p>";
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