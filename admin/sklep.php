<?php
/**
 * Moduł zarządzania kategoriami sklepu.
 * Wersja: v1.8
 * Zawiera funkcje CRUD dla kategorii (Dodaj, Usuń, Edytuj, Pokaż).
 */

// Dołączenie konfiguracji (jeśli plik jest ładowany niezależnie)
// include('../cfg.php'); 

/**
 * Formularz dodawania nowej kategorii.
 */
function FormularzDodajKategorie() {
    return '
    <div class="sklep-form">
        <h3>Dodaj nową kategorię</h3>
        <form method="post" action="admin.php?action=sklep_add">
            <label>Nazwa kategorii:</label><br>
            <input type="text" name="nazwa" required /><br>
            <label>Kategoria nadrzędna (Matka):</label><br>
            <input type="number" name="matka" value="0" placeholder="0 dla głównej" /><br><br>
            <input type="submit" name="submit_add_cat" value="Dodaj Kategorię" />
        </form>
    </div>';
}

/**
 * Dodaje kategorię do bazy.
 */
function DodajKategorie($link) {
    $matka = intval($_POST['matka']); // Zabezpieczenie int
    $nazwa = mysqli_real_escape_string($link, $_POST['nazwa']); // Zabezpieczenie SQL Injection

    $sql = "INSERT INTO categories (matka, nazwa) VALUES ('$matka', '$nazwa')";
    if (mysqli_query($link, $sql)) {
        echo "<p class='success'>Dodano kategorię: $nazwa</p>";
    } else {
        echo "<p class='error'>Błąd: " . mysqli_error($link) . "</p>";
    }
}

/**
 * Usuwa kategorię.
 */
function UsunKategorie($link, $id) {
    $id = intval($id);
    // LIMIT 1 - zabezpieczenie przed usunięciem zbyt wielu rekordów
    $sql = "DELETE FROM categories WHERE id = $id LIMIT 1";
    if (mysqli_query($link, $sql)) {
        echo "<p class='success'>Usunięto kategorię ID: $id</p>";
    } else {
        echo "<p class='error'>Błąd usuwania: " . mysqli_error($link) . "</p>";
    }
}

/**
 * Formularz edycji kategorii.
 */
function EdytujKategorieForm($link, $id) {
    $id = intval($id);
    $sql = "SELECT * FROM categories WHERE id = $id LIMIT 1";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($result);

    return '
    <div class="sklep-form">
        <h3>Edytuj kategorię</h3>
        <form method="post" action="admin.php?action=sklep_edit&id='.$id.'">
            <label>Nazwa:</label><br>
            <input type="text" name="nazwa" value="'.$row['nazwa'].'" required /><br>
            <label>Matka:</label><br>
            <input type="number" name="matka" value="'.$row['matka'].'" /><br><br>
            <input type="submit" name="submit_edit_cat" value="Zapisz zmiany" />
        </form>
    </div>';
}

/**
 * Aktualizuje kategorię w bazie.
 */
function EdytujKategorie($link, $id) {
    $id = intval($id);
    $matka = intval($_POST['matka']);
    $nazwa = mysqli_real_escape_string($link, $_POST['nazwa']);

    $sql = "UPDATE categories SET nazwa = '$nazwa', matka = '$matka' WHERE id = $id LIMIT 1";
    if (mysqli_query($link, $sql)) {
        echo "<p class='success'>Zaktualizowano kategorię.</p>";
    } else {
        echo "<p class='error'>Błąd edycji: " . mysqli_error($link) . "</p>";
    }
}

/**
 * Wyświetla drzewo kategorii (Matki i Dzieci).
 * Wykorzystuje pętle zagnieżdżone zgodnie z zadaniem.
 */
function PokazKategorie($link) {
    echo '<h3>Drzewo Kategorii (Sklep: Widokówki)</h3>';
    echo '<a href="admin.php?action=sklep_add_form">[+ Dodaj nową kategorię]</a><br><br>';

    // 1. Pobierz kategorie główne (Matki)
    $sql_mothers = "SELECT * FROM categories WHERE matka = 0 ORDER BY id ASC";
    $result_mothers = mysqli_query($link, $sql_mothers);

    if (mysqli_num_rows($result_mothers) > 0) {
        echo '<ul class="category-tree">';
        
        while ($mother = mysqli_fetch_assoc($result_mothers)) {
            // Wyświetlenie Matki
            echo '<li>';
            echo '<b>' . $mother['id'] . '. ' . $mother['nazwa'] . '</b> ';
            echo ' <a href="admin.php?action=sklep_edit_form&id='.$mother['id'].'">[Edytuj]</a> ';
            echo ' <a href="admin.php?action=sklep_delete&id='.$mother['id'].'" onclick="return confirm(\'Usunąć?\')">[Usuń]</a>';

            // 2. Pętla zagnieżdżona - Pobierz Dzieci dla tej Matki
            $mother_id = $mother['id'];
            $sql_children = "SELECT * FROM categories WHERE matka = $mother_id ORDER BY id ASC";
            $result_children = mysqli_query($link, $sql_children);

            if (mysqli_num_rows($result_children) > 0) {
                echo '<ul>'; // Podlista dla dzieci
                while ($child = mysqli_fetch_assoc($result_children)) {
                    echo '<li>';
                    echo $child['id'] . '. ' . $child['nazwa'];
                    echo ' <a href="admin.php?action=sklep_edit_form&id='.$child['id'].'" style="font-size:0.8em">[E]</a> ';
                    echo ' <a href="admin.php?action=sklep_delete&id='.$child['id'].'" style="font-size:0.8em" onclick="return confirm(\'Usunąć?\')">[U]</a>';
                    echo '</li>';
                }
                echo '</ul>';
            }

            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>Brak kategorii w sklepie.</p>';
    }
}
function FormularzDodajProdukt($link) {
    // Pobierz kategorie do listy rozwijanej
    $cats_html = '';
    $query = "SELECT id, nazwa FROM categories ORDER BY nazwa ASC";
    $result = mysqli_query($link, $query);
    while($row = mysqli_fetch_assoc($result)) {
        $cats_html .= '<option value="'.$row['id'].'">'.$row['nazwa'].'</option>';
    }

    return '
    <div class="sklep-form">
        <h3>Dodaj nowy produkt</h3>
        <form method="post" action="admin.php?action=prod_add">
            <label>Tytuł:</label><br> <input type="text" name="tytul" required /><br>
            <label>Opis:</label><br> <textarea name="opis" rows="3"></textarea><br>
            <label>Cena Netto (zł):</label><br> <input type="number" step="0.01" name="cena_netto" required /><br>
            <label>VAT (%):</label><br> <input type="number" step="0.01" name="podatek_vat" value="23" /><br>
            <label>Ilość na magazynie:</label><br> <input type="number" name="ilosc_magazyn" required /><br>
            <label>Data wygaśnięcia:</label><br> <input type="date" name="data_wygasniecia" /><br>
            <label>Status dostępności:</label><br> 
            <select name="status_dostepnosci">
                <option value="1">Dostępny</option>
                <option value="0">Niedostępny</option>
            </select><br>
            <label>Kategoria:</label><br> <select name="kategoria">'.$cats_html.'</select><br>
            <label>Gabaryt:</label><br> <input type="text" name="gabaryt" placeholder="np. mały, duży" /><br>
            <label>Link do zdjęcia:</label><br> <input type="text" name="zdjecie" /><br><br>
            <input type="submit" name="submit_add_prod" value="Dodaj Produkt" />
        </form>
    </div>';
}

/**
 * Dodaje produkt do bazy.
 */
function DodajProdukt($link) {
    $tytul = mysqli_real_escape_string($link, $_POST['tytul']);
    $opis = mysqli_real_escape_string($link, $_POST['opis']);
    $cena = floatval($_POST['cena_netto']);
    $vat = floatval($_POST['podatek_vat']);
    $ilosc = intval($_POST['ilosc_magazyn']);
    $status = intval($_POST['status_dostepnosci']);
    $kat = intval($_POST['kategoria']);
    $gabaryt = mysqli_real_escape_string($link, $_POST['gabaryt']);
    $zdjecie = mysqli_real_escape_string($link, $_POST['zdjecie']);
    $wygasa = !empty($_POST['data_wygasniecia']) ? "'".$_POST['data_wygasniecia']."'" : "NULL";

    $sql = "INSERT INTO products (tytul, opis, cena_netto, podatek_vat, ilosc_magazyn, status_dostepnosci, kategoria, gabaryt, zdjecie, data_wygasniecia) 
            VALUES ('$tytul', '$opis', $cena, $vat, $ilosc, $status, $kat, '$gabaryt', '$zdjecie', $wygasa)";

    if (mysqli_query($link, $sql)) {
        echo "<p class='success'>Dodano produkt: $tytul</p>";
    } else {
        echo "<p class='error'>Błąd: " . mysqli_error($link) . "</p>";
    }
}

/**
 * Usuwa produkt.
 */
function UsunProdukt($link, $id) {
    $id = intval($id);
    if (mysqli_query($link, "DELETE FROM products WHERE id = $id LIMIT 1")) {
        echo "<p class='success'>Usunięto produkt ID: $id</p>";
    } else {
        echo "<p class='error'>Błąd: " . mysqli_error($link) . "</p>";
    }
}

/**
 * Edytuje produkt (Formularz + Zapis).
 * (Uproszczona wersja 2w1 dla oszczędności miejsca, normalnie rozdzielamy Form i Logic)
 */
function EdytujProdukt($link, $id) {
    $id = intval($id);
    
    // ZAPIS ZMIAN
    if (isset($_POST['submit_edit_prod'])) {
        $tytul = mysqli_real_escape_string($link, $_POST['tytul']);
        $opis = mysqli_real_escape_string($link, $_POST['opis']);
        $cena = floatval($_POST['cena_netto']);
        $vat = floatval($_POST['podatek_vat']);
        $ilosc = intval($_POST['ilosc_magazyn']);
        $status = intval($_POST['status_dostepnosci']);
        $kat = intval($_POST['kategoria']);
        $gabaryt = mysqli_real_escape_string($link, $_POST['gabaryt']);
        $zdjecie = mysqli_real_escape_string($link, $_POST['zdjecie']);
        $wygasa = !empty($_POST['data_wygasniecia']) ? "'".$_POST['data_wygasniecia']."'" : "NULL";

        $sql_update = "UPDATE products SET tytul='$tytul', opis='$opis', cena_netto=$cena, podatek_vat=$vat, ilosc_magazyn=$ilosc, 
                       status_dostepnosci=$status, kategoria=$kat, gabaryt='$gabaryt', zdjecie='$zdjecie', data_wygasniecia=$wygasa 
                       WHERE id=$id LIMIT 1";
        mysqli_query($link, $sql_update);
        echo "<p class='success'>Zaktualizowano produkt.</p>";
    }

    // POBRANIE DANYCH DO FORMULARZA
    $row = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM products WHERE id=$id LIMIT 1"));
    
    return '
    <div class="sklep-form">
        <h3>Edytuj produkt: '.$row['tytul'].'</h3>
        <form method="post" action="admin.php?action=prod_edit&id='.$id.'">
            <label>Tytuł:</label><br> <input type="text" name="tytul" value="'.$row['tytul'].'" required /><br>
            <label>Opis:</label><br> <textarea name="opis" rows="3">'.$row['opis'].'</textarea><br>
            <label>Cena Netto:</label><br> <input type="number" step="0.01" name="cena_netto" value="'.$row['cena_netto'].'" /><br>
            <label>VAT:</label><br> <input type="number" step="0.01" name="podatek_vat" value="'.$row['podatek_vat'].'" /><br>
            <label>Magazyn:</label><br> <input type="number" name="ilosc_magazyn" value="'.$row['ilosc_magazyn'].'" /><br>
            <label>Wygasa (RRRR-MM-DD):</label><br> <input type="date" name="data_wygasniecia" value="'.$row['data_wygasniecia'].'" /><br>
            <label>Status:</label><br> <select name="status_dostepnosci">
                <option value="1" '.($row['status_dostepnosci']==1?'selected':'').'>Dostępny</option>
                <option value="0" '.($row['status_dostepnosci']==0?'selected':'').'>Niedostępny</option>
            </select><br>
            <label>Kategoria ID:</label><br> <input type="number" name="kategoria" value="'.$row['kategoria'].'" /><br>
            <label>Gabaryt:</label><br> <input type="text" name="gabaryt" value="'.$row['gabaryt'].'" /><br>
            <label>Zdjęcie URL:</label><br> <input type="text" name="zdjecie" value="'.$row['zdjecie'].'" /><br><br>
            <input type="submit" name="submit_edit_prod" value="Zapisz zmiany" />
        </form>
    </div>';
}

/**
 * Wyświetla listę produktów z obliczoną ceną brutto i statusem.
 */
function PokazProdukty($link) {
    echo '<h3>Lista Produktów</h3>';
    echo '<a href="admin.php?action=prod_add_form">[+ Dodaj Produkt]</a> <a href="admin.php?action=sklep_show">[Zarządzaj Kategoriami]</a><br><br>';
    
    $query = "SELECT * FROM products ORDER BY id DESC";
    $result = mysqli_query($link, $query);

    echo "<table border='1' cellpadding='5' style='border-collapse:collapse; width:100%'>";
    echo "<tr style='background:#ddd'><th>ID</th><th>Zdjęcie</th><th>Tytuł</th><th>Cena Brutto</th><th>Magazyn</th><th>Status dostępności</th><th>Akcje</th></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        // Obliczenia
        $cena_brutto = $row['cena_netto'] + ($row['cena_netto'] * $row['podatek_vat'] / 100);
        $cena_brutto = number_format($cena_brutto, 2);
        
        // Logika dostępności
        $dzis = date('Y-m-d');
        $wygasl = (!is_null($row['data_wygasniecia']) && $row['data_wygasniecia'] < $dzis);
        $brak_towaru = ($row['ilosc_magazyn'] <= 0);
        $wylaczony = ($row['status_dostepnosci'] == 0);

        if ($wygasl) {
            $status_txt = "<span style='color:red'>Wygasł</span>";
        } elseif ($brak_towaru) {
            $status_txt = "<span style='color:orange'>Brak w mag.</span>";
        } elseif ($wylaczony) {
            $status_txt = "<span style='color:gray'>Wyłączony</span>";
        } else {
            $status_txt = "<span style='color:green'>Dostępny</span>";
        }

        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td><img src='{$row['zdjecie']}' style='height:50px; width:auto;'></td>";
        echo "<td><b>{$row['tytul']}</b><br><small>Kat ID: {$row['kategoria']}</small></td>";
        echo "<td>{$cena_brutto} zł</td>";
        echo "<td>{$row['ilosc_magazyn']} szt.</td>";
        echo "<td>{$status_txt} <br><small>Wygasa: ".($row['data_wygasniecia'] ?? 'Nigdy')."</small></td>";
        echo "<td>
            <a href='admin.php?action=prod_edit&id={$row['id']}'>[Edytuj]</a> 
            <a href='admin.php?action=prod_delete&id={$row['id']}' onclick='return confirm(\"Usunąć?\")'>[Usuń]</a>
        </td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>