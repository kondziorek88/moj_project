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
?>