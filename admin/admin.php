<?php
session_start();
include '../cfg.php';

function FormularzLogowania() {
    $wynik = '
        <div class="logowanie">
            <h1 class="heading">Panel CMS: </h1>
            <div class="logowanie">
                <form method="post" name="LoginForm" enctype="multipart/form-data" action="'.htmlspecialchars($_SERVER['REQUEST_URI']).'">
                    <table class="logowanie">
                        <tr><td class="log4_t">[email]</td><td><input type="text" name="login_email" class="logowanie" /></td></tr>
                        <tr><td class="log4_t">[hasło]</td><td><input type="password" name="login_pass" class="logowanie" /></td></tr>
                        <tr><td>&nbsp;</td><td><input type="submit" name="xl_submit" class="logowanie" value="zaloguj" /></td></tr>
                    </table>
                </form>
            </div>
        </div>
    ';
    return $wynik;
}

$error_message = '';

if (!isset($_SESSION['logged_in']))
{
    if((isset($_POST['xl_submit'])))
    {
        $input_login = isset($_POST['login_email']) ? trim($_POST['login_email']) : '';
        $input_pass = isset($_POST['login_pass']) ? trim($_POST['login_pass']) : '';
        if ($input_login === $login && $input_pass === $pass) 
        {
            $_SESSION['logged_in'] = true;
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();

        } else {
            $error_message = '<p style="color: red;">Błąd logowania: nieprawidłowy e-mail lub hasło.</p>';
        }
    }
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $content = '';

    if (isset($_POST['edit_submit'])) {
        $id_update = isset($_POST['id_edycji']) ? (int)$_POST['id_edycji'] : 0;
        $new_title = mysqli_real_escape_string($link, $_POST['page_title']);
        $new_content = mysqli_real_escape_string($link, $_POST['page_content']);
        $new_status = isset($_POST['status']) ? 1 : 0;
        $query_update = "UPDATE page_list SET 
            page_title = '$new_title', 
            page_content = '$new_content', 
            status = $new_status 
            WHERE id = $id_update LIMIT 1";
        if (mysqli_query($link, $query_update)) {
            $content = '<p style="color: green;">Podstrona ID ' . $id_update . ' została pomyślnie zaktualizowana.</p>';
            $action = '';
        } else {
            $content = '<p style="color: red;">Błąd aktualizacji: ' . mysqli_error($link) . '</p>';
        }
    }
    if (isset($_POST['add_submit'])) {
        $new_title = mysqli_real_escape_string($link, $_POST['page_title']);
        $new_content = mysqli_real_escape_string($link, $_POST['page_content']);
        $new_status = isset($_POST['status']) ? 1 : 0;
        
        $query_insert = "INSERT INTO page_list (page_title, page_content, status) 
                         VALUES ('$new_title', '$new_content', $new_status)";
        
        if (mysqli_query($link, $query_insert)) {
            $content = '<p style="color: green;">Nowa podstrona została pomyślnie dodana (ID: ' . mysqli_insert_id($link) . ').</p>';
            $action = '';
        } else {
            $content = '<p style="color: red;">Błąd dodawania: ' . mysqli_error($link) . '</p>';
        }
    }
    if ($action === 'edit' && $id > 0) {
        echo $content;
        echo EdytujPodstrone($link, $id);
     
    }elseif ($action === 'add') {
        echo DodajNowaPodstrone();
    }elseif ($action === 'delete' && $id > 0) {
        echo UsunPodstrone($link, $id); 
        ListaPodstron($link);
    }else {
        echo 'Witaj w Panelu CMS!';
        echo $content;
        ListaPodstron($link);
    }
    
} else {
    echo $error_message; 
    echo FormularzLogowania();
}


function ListaPodstron($link) {
    echo '<p><a href="admin.php?action=add" style="padding: 10px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">➕ Dodaj Nową Podstronę</a></p>';
    $query = "SELECT id, page_title FROM page_list ORDER BY status DESC";
    $result = mysqli_query($link, $query); 

    echo "<ul>";
    while ($row = mysqli_fetch_array($result)) {
        echo "<li>";
        echo $row['id'] . ' ' . $row['page_title'];
        echo ' <a href="admin.php?action=edit&id=' . $row['id'] . '">Edytuj</a>';
        echo ' <a href="admin.php?action=delete&id=' . $row['id'] . '">Usuń</a>';
        echo "</li>";
    }
    echo "</ul>";
}

function EdytujPodstrone($link, $id)
{
    $id = (int)$id;
    $query_select = "SELECT * FROM page_list WHERE id = $id LIMIT 1"; 
    $result = mysqli_query($link, $query_select);
    $data = mysqli_fetch_array($result);
    if (!$data) {
        return '<p style="color: red;">Nie znaleziono podstrony o podanym ID.</p>';
    }
    $tytul = htmlspecialchars($data['page_title']);
    $tresc = htmlspecialchars($data['page_content']);
    $aktywny_checked = ($data['status'] == 1) ? 'checked' : '';
    $form = '
        <h2 class="heading">Edycja podstrony: ' . $tytul . '</h2>
        <form method="post" action="admin.php?action=edit&id=' . $id . '">
            <input type="hidden" name="id_edycji" value="' . $id . '">
            <table>
                <tr>
                    <td>Tytuł:</td>
                    <td><input type="text" name="page_title" value="' . $tytul . '" required /></td>
                </tr>
                <tr>
                    <td>Treść:</td>
                    <td><textarea name="page_content" rows="10" cols="50" required>' . $tresc . '</textarea></td>
                </tr>
                <tr>
                    <td>Aktywna:</td>
                    <td><input type="checkbox" name="status" value="1" ' . $aktywny_checked . ' /></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="edit_submit" value="Zapisz zmiany" /></td>
                </tr>
            </table>
        </form>
    ';
    return $form;
}

function DodajNowaPodstrone() {
    $form = '
        <h2 class="heading">Dodaj nową podstronę</h2>
        <form method="post" action="admin.php?action=add">
            <table>
                <tr>
                    <td>Tytuł:</td>
                    <td><input type="text" name="page_title" value="" required /></td>
                </tr>
                <tr>
                    <td>Treść:</td>
                    <td><textarea name="page_content" rows="10" cols="50" required></textarea></td>
                </tr>
                <tr>
                    <td>Aktywna:</td>
                    <td><input type="checkbox" name="status" value="1" checked /></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" name="add_submit" value="Zatwierdź" /></td>
                </tr>
            </table>
        </form>
    ';
    return $form;
}

function UsunPodstrone($link, $id) {
    $id = (int)$id;
    
    $query_delete = "DELETE FROM page_list WHERE id = $id LIMIT 1"; 
    
    if (mysqli_query($link, $query_delete)) {
        return '<p style="color: green;">Podstrona ID ' . $id . ' została pomyślnie usunięta.</p>';
    } else {
        return '<p style="color: red;">Błąd usuwania: ' . mysqli_error($link) . '</p>';
    }
}

?>