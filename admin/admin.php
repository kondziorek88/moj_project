<?php
/**
 * Panel Administracyjny CMS.
 * Wersja: v1.8
 * Odpowiada za: Logowanie, Edycję, Dodawanie i Usuwanie podstron.
 */

session_start();
include '../cfg.php'; // Połączenie z bazą danych ($link / $conn)
include 'sklep.php'; //połaczenie ze sklepem (obsługa kategorii)
/**
 * Generuje formularz logowania do panelu admina.
 * * @return string Kod HTML formularza logowania.
 */
function FormularzLogowania() {
    $wynik = '
        <div class="logowanie">
            <h1 class="heading">Panel CMS (v1.8): Logowanie</h1>
            <div class="logowanie-container">
                <form method="post" name="LoginForm" enctype="multipart/form-data" action="'.htmlspecialchars($_SERVER['REQUEST_URI']).'">
                    <table class="logowanie-table">
                        <tr>
                            <td class="log4_t">Email:</td>
                            <td><input type="text" name="login_email" class="logowanie-input" /></td>
                        </tr>
                        <tr>
                            <td class="log4_t">Hasło:</td>
                            <td><input type="password" name="login_pass" class="logowanie-input" /></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td><input type="submit" name="xl_submit" class="logowanie-btn" value="Zaloguj" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    ';
    return $wynik;
}

/**
 * Wyświetla listę wszystkich podstron z bazy danych.
 * Zawiera linki do edycji i usuwania.
 * * @param mysqli $link Połączenie do bazy danych.
 * @return void Bezpośrednio wypisuje HTML.
 */
function ListaPodstron($link) {
    echo '<hr>';
    echo '<h3>Lista podstron:</h3>';
    echo '<p><a href="admin.php?action=add" style="padding: 5px 10px; background: #28a745; color: #fff; text-decoration: none; border-radius: 4px;">[+] Dodaj Nową Podstronę</a></p>';
    
    // Pobranie ID, Tytułu i Statusu
    $query = "SELECT id, page_title, status FROM page_list ORDER BY id ASC";
    $result = mysqli_query($link, $query); 

    if ($result) {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #eee;'><th>ID</th><th>Tytuł</th><th>Status</th><th>Akcje</th></tr>";
        
        while ($row = mysqli_fetch_array($result)) {
            $status_txt = ($row['status'] == 1) ? '<span style="color:green">Aktywna</span>' : '<span style="color:gray">Nieaktywna</span>';
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td><b>{$row['page_title']}</b></td>";
            echo "<td>{$status_txt}</td>";
            echo "<td>
                    <a href='admin.php?action=edit&id={$row['id']}'>[Edytuj]</a> 
                    <a href='admin.php?action=delete&id={$row['id']}' onclick='return confirm(\"Czy na pewno usunąć?\")'>[Usuń]</a>
                  </td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Błąd zapytania SQL: " . mysqli_error($link);
    }
}

/**
 * Generuje formularz edycji podstrony.
 * * @param mysqli $link Połączenie do bazy.
 * @param int $id ID edytowanej strony.
 * @return string Kod HTML formularza.
 */
function EdytujPodstrone($link, $id) {
    $id = (int)$id; // Zabezpieczenie: rzutowanie na int
    
    // LIMIT 1 - optymalizacja zapytania
    $query = "SELECT * FROM page_list WHERE id = $id LIMIT 1"; 
    $result = mysqli_query($link, $query);
    $data = mysqli_fetch_array($result);

    if (!$data) {
        return '<p style="color: red;">Nie znaleziono podstrony o podanym ID.</p>';
    }

    $tytul = htmlspecialchars($data['page_title']);
    $tresc = htmlspecialchars($data['page_content']);
    $aktywny_checked = ($data['status'] == 1) ? 'checked' : '';

    $form = '
        <div class="edycja">
            <h2 class="heading">Edycja podstrony (ID: ' . $id . ')</h2>
            <form method="post" action="admin.php?action=edit&id=' . $id . '">
                <input type="hidden" name="id_edycji" value="' . $id . '">
                <table>
                    <tr>
                        <td>Tytuł strony:</td>
                        <td><input type="text" name="page_title" value="' . $tytul . '" size="60" required /></td>
                    </tr>
                    <tr>
                        <td>Treść HTML:</td>
                        <td><textarea name="page_content" rows="15" cols="80" required>' . $tresc . '</textarea></td>
                    </tr>
                    <tr>
                        <td>Status (Aktywna):</td>
                        <td><input type="checkbox" name="status" value="1" ' . $aktywny_checked . ' /></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input type="submit" name="edit_submit" value="Zapisz zmiany" style="background: #007bff; color: white; padding: 5px 15px; border: none; cursor: pointer;" />
                            <a href="admin.php">Anuluj</a>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    ';
    return $form;
}

/**
 * Generuje formularz dodawania nowej podstrony.
 * * @return string Kod HTML formularza.
 */
function DodajNowaPodstrone() {
    $form = '
        <div class="dodawanie">
            <h2 class="heading">Dodaj nową podstronę</h2>
            <form method="post" action="admin.php?action=add">
                <table>
                    <tr>
                        <td>Tytuł strony:</td>
                        <td><input type="text" name="page_title" size="60" required /></td>
                    </tr>
                    <tr>
                        <td>Treść HTML:</td>
                        <td><textarea name="page_content" rows="15" cols="80" required></textarea></td>
                    </tr>
                    <tr>
                        <td>Status (Aktywna):</td>
                        <td><input type="checkbox" name="status" value="1" checked /></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input type="submit" name="add_submit" value="Dodaj stronę" style="background: #28a745; color: white; padding: 5px 15px; border: none; cursor: pointer;" />
                            <a href="admin.php">Wróć</a>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    ';
    return $form;
}

/**
 * Usuwa podstronę z bazy danych.
 * * @param mysqli $link Połączenie do bazy.
 * @param int $id ID strony do usunięcia.
 * @return string Komunikat o sukcesie lub błędzie.
 */
function UsunPodstrone($link, $id) {
    $id = (int)$id; // Zabezpieczenie
    
    // LIMIT 1 - zapobiega przypadkowemu usunięciu większej liczby rekordów
    $query_delete = "DELETE FROM page_list WHERE id = $id LIMIT 1"; 
    
    if (mysqli_query($link, $query_delete)) {
        return '<p style="color: green; font-weight: bold;">Podstrona ID ' . $id . ' została usunięta.</p>';
    } else {
        return '<p style="color: red;">Błąd usuwania: ' . mysqli_error($link) . '</p>';
    }
}

// =============================================
// GŁÓWNA LOGIKA SKRYPTU
// =============================================

$error_message = '';

// 1. Obsługa logowania
if (!isset($_SESSION['logged_in'])) {
    if (isset($_POST['xl_submit'])) {
        $input_login = isset($_POST['login_email']) ? trim($_POST['login_email']) : '';
        $input_pass = isset($_POST['login_pass']) ? trim($_POST['login_pass']) : '';
        
        // Weryfikacja danych (proste porównanie ze zmiennymi z cfg.php)
        if ($input_login === $login && $input_pass === $pass) {
            $_SESSION['logged_in'] = true;
            header('Location: admin.php');
            exit();
        } else {
            $error_message = '<p style="color: red; font-weight: bold;">Błąd logowania: nieprawidłowe dane.</p>';
        }
    }
}

// 2. Obsługa akcji po zalogowaniu
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    
    // Pobranie parametrów akcji
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    echo '<div style="background: #f8f9fa; padding: 10px; border-bottom: 1px solid #ddd;">';
    echo 'Witaj w Panelu Administratora. <a href="../index.php" target="_blank">Zobacz stronę</a> | <a href="logout.php">Wyloguj</a>';
    echo '</div>';

    // Obsługa zapisu edycji
    if (isset($_POST['edit_submit'])) {
        $id_update = isset($_POST['id_edycji']) ? (int)$_POST['id_edycji'] : 0;
        
        // Zabezpieczenie SQL Injection (mysqli_real_escape_string)
        $new_title = mysqli_real_escape_string($link, $_POST['page_title']);
        $new_content = mysqli_real_escape_string($link, $_POST['page_content']);
        $new_status = isset($_POST['status']) ? 1 : 0;
        
        $query_update = "UPDATE page_list SET 
            page_title = '$new_title', 
            page_content = '$new_content', 
            status = $new_status 
            WHERE id = $id_update LIMIT 1"; // LIMIT 1 dla bezpieczeństwa
            
        if (mysqli_query($link, $query_update)) {
            echo '<p style="color: green;">Zaktualizowano stronę pomyślnie.</p>';
            $action = ''; // Wróć do listy
        } else {
            echo '<p style="color: red;">Błąd aktualizacji: ' . mysqli_error($link) . '</p>';
        }
    }

    // Obsługa zapisu nowej strony
    if (isset($_POST['add_submit'])) {
        // Zabezpieczenie SQL Injection
        $new_title = mysqli_real_escape_string($link, $_POST['page_title']);
        $new_content = mysqli_real_escape_string($link, $_POST['page_content']);
        $new_status = isset($_POST['status']) ? 1 : 0;
        
        $query_insert = "INSERT INTO page_list (page_title, page_content, status) 
                         VALUES ('$new_title', '$new_content', $new_status)";
        
        if (mysqli_query($link, $query_insert)) {
            echo '<p style="color: green;">Dodano nową stronę pomyślnie.</p>';
            $action = ''; // Wróć do listy
        } else {
            echo '<p style="color: red;">Błąd dodawania: ' . mysqli_error($link) . '</p>';
        }
    }

    // Wyświetlanie odpowiednich widoków w zależności od akcji
    switch ($action) {
        // --- Strony CMS ---
        case 'edit':
            if ($id > 0) echo EdytujPodstrone($link, $id);
            break;
        case 'add':
            echo DodajNowaPodstrone();
            break;
        case 'delete':
            if ($id > 0) echo UsunPodstrone($link, $id);
            ListaPodstron($link);
            break;
            
        // --- Obsługa Sklepu (Kategorie) ---
        case 'sklep_show':
            PokazKategorie($link);
            break;
        case 'sklep_add_form':
            echo FormularzDodajKategorie();
            break;
        case 'sklep_add':
            if (isset($_POST['submit_add_cat'])) {
                DodajKategorie($link);
            }
            PokazKategorie($link);
            break;
        case 'sklep_edit_form':
            if ($id > 0) echo EdytujKategorieForm($link, $id);
            break;
        case 'sklep_edit':
            if (isset($_POST['submit_edit_cat']) && $id > 0) {
                EdytujKategorie($link, $id);
            }
            PokazKategorie($link);
            break;
        case 'sklep_delete':
            if ($id > 0) UsunKategorie($link, $id);
            PokazKategorie($link);
            break;

        // Domyślnie - lista podstron + link do sklepu
        default:
            echo '<p><a href="admin.php?action=sklep_show" style="background:orange; color:white; padding:5px;">[ Zarządzaj Sklepem ]</a></p>';
            ListaPodstron($link);
            break;
    }
    
} else {
    // Jeśli niezalogowany, pokaż formularz
    echo $error_message; 
    echo FormularzLogowania();
}
?>