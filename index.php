<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING); // Pomijanie drobnych ostrzeżeń

include('cfg.php'); // 🔹 połączenie z bazą danych
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Projekt PHP - Fotografia">
    <meta name="keywords" content="HTML, CSS, JS, PHP, projekt, fotografia">
    <meta name="author" content="Konrad Sendlewski">
    <title>Fotografia - moja pasja (v1.5)</title>
    <link rel="stylesheet" href="styles.css">
    <script src="timedate.js" type="text/javascript"></script>
</head>

<body onload="startclock()">

<!-- 🔹 Zegarek i data -->
<div id="zegarek"></div>
<div id="data"></div>

<header>
    <h1>📸 Fotografia - moja pasja</h1>
</header>

<!-- 🔹 MENU -->
<nav>
    <ul class="menu">
        <li><a href="index.php?idp=1">Strona główna</a></li>
        <li><a href="index.php?idp=2">Strona 2</a></li>
        <li><a href="index.php?idp=3">Strona 3</a></li>
        <li><a href="index.php?idp=4">Strona 4</a></li>
        <li><a href="index.php?idp=5">Strona 5</a></li>
        <li><a href="index.php?idp=6">Filmy</a></li>
        <li><a href="index.php?idp=contact">Kontakt</a></li>
    </ul>
</nav>

<main>

<?php
// =======================
// 1) PANEL ADMINA
// =======================
if (isset($_GET['admin']) && $_GET['admin'] == 1) {

    echo "<h2>Panel administratora</h2>";

    $sql = "SELECT * FROM page_list ORDER BY id ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table class='admin-table'>";
        echo "<tr><th>ID</th><th>Tytuł</th><th>Status</th><th>Opcje</th></tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['page_title']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "<td>
                    <a href='edit.php?id={$row['id']}'>Edytuj</a> |
                    <a href='delete.php?id={$row['id']}'>Usuń</a>
                  </td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Brak podstron w bazie.</p>";
    }

    echo "</main></body></html>";
    exit();
}
?>

<?php
// =======================
// 2) OBSŁUGA STRON + KONTAKT
// =======================

// pobieramy parametr idp
$idp = isset($_GET['idp']) ? $_GET['idp'] : 'glowna';

// obsługa specjalnych stron (kontakt)
if ($idp === 'contact') {
    include('contact.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'send_contact') {
            WyslijMailKontakt('twoj_email@example.com'); 
        } elseif ($action === 'remind_password') {
            PrzypomnijHaslo($conn);
        } else {
            PokazKontakt();
        }
    } else {
        PokazKontakt();
    }

} else {
    // standardowa obsługa z bazy danych
    $idp_int = intval($idp);
    $sql = "SELECT * FROM page_list WHERE id = $idp_int AND status = 1 LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<article class='page'>";
        echo "<h2>{$row['page_title']}</h2>";
        echo "<div class='page-content'>{$row['page_content']}</div>";
        echo "</article>";
    } else {
        echo "<p class='error'>Nie znaleziono strony o id = <strong>$idp_int</strong>.</p>";
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
