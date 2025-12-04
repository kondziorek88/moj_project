<?php
session_start();

include 'dane.php';        
require_once 'funkcje.php';

//include()
echo "<h3>Dane studenta:</h3>";
echo "Imię i nazwisko: $imie $nazwisko<br>";
echo "Numer indeksu: $indeks<br>";
echo "Grupa: $grupa<br><hr>";

//if / else / elseif
$temperatura = 15;

if ($temperatura < 0) {
    echo "Zimno!<br>";
} elseif ($temperatura < 20) {
    echo "Chłodno, ubierz się cieplej.<br>";
} else {
    echo "Ciepło, można iść w krótkim rękawie.<br>";
}

//Switch
$dzien = "wtorek";
switch ($dzien) {
    case "poniedziałek":
        echo "Początek tygodnia.<br>";
        break;
    case "piątek":
        echo "Prawie weekend!<br>";
        break;
    default:
        echo "Zwykły dzień tygodnia ($dzien).<br>";
}
echo "<hr>";

//Pętla for
echo "<b>Pętla for:</b><br>";
for ($i = 1; $i <= 5; $i++) {
    echo "Iteracja nr: $i<br>";
}

//Pętla while
echo "<br><b>Pętla while:</b><br>";
$j = 0;
while ($j < 3) {
    echo "Licznik: $j<br>";
    $j++;
}
echo "<hr>";

//$_GET
if (isset($_GET['nazwa'])) {
    echo "Dostałem z GET: " . htmlspecialchars($_GET['nazwa']) . "<br>";
} else {
    echo "Użyj adresu: ?nazwa=TwojeImie<br>";
}
echo "<hr>";

//$_POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $imie_post = $_POST['imie'] ?? '';
    echo "Dostałem z POST: " . htmlspecialchars($imie_post) . "<br>";
}
?>
<form method="post">
    <label>Podaj imię: <input type="text" name="imie"></label>
    <input type="submit" value="Wyślij POST">
</form>
<hr>

<?php
//$_SESSION
if (!isset($_SESSION['licznik'])) {
    $_SESSION['licznik'] = 1;
} else {
    $_SESSION['licznik']++;
}
echo "Liczba odświeżeń strony w tej sesji: " . $_SESSION['licznik'] . "<br>";

//Funkcja z require_once
echo "<hr><b>Średnia z trzech liczb:</b><br>";
echo "Średnia (5, 10, 15) = " . srednia(5, 10, 15);
?>
