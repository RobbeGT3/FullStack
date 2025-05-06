<?php

session_start();

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    die("Page not available");
}

$conn = require_once "common/connectionDB.php";

$stmt1 = $conn->prepare("SELECT Bestelling.bestellingsNummer, Bestelling.idArtikel, Bestelling.idLocaties, Locatie.plaatsnaam, Artikel.product, Artikel.type, Fabriek.fabrieksnaam, Bestelling.aantal_besteld, Bestelling.besteldatum, Bestelling.aankomstdatum, Bestelling.is_afgeleverd, Bestelling.waarde_bestelling FROM Bestelling 
    INNER JOIN Artikel ON Bestelling.idArtikel = Artikel.idArtikel
    INNER JOIN Locatie ON Bestelling.idLocaties = Locatie.idLocaties
    INNER JOIN Fabriek ON Artikel.idFabriek = Fabriek.idFabriek
    ORDER BY Bestelling.is_afgeleverd DESC, Locatie.plaatsnaam ASC;");

$stmt1->execute();
$result1 = $stmt1->get_result();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestellingen ToolsForEver</title>
    <link rel="stylesheet" href="styling/opmaak.css">
</head>
<body>
    <script src="script.js"></script>
    <audio id="myAudio">
    <source src="styling\Audios\omni-man-are-you-sure.mp3" type="audio/mpeg">
    </audio>

    <section class="topSection">
        <h1>Bestellingen</h1>
        <img class="logo" id="logo" src="styling/images/SPOILER_omni-sem.png" alt="based">
    </section>
    <nav>
    <ul>
            <li><a href="voorraad.php">Voorraad</a></li>
            <li><a href="bestellingen.php">Bestellingen</a></li>
            <li><a href="product.php">Geregistreerde Producten</a></li>
            <li><a href="logout.php">Uitloggen</a></li>
        </ul>
    </nav>
    <section>
    <section id="tableSection2">
        <div id="toevoegKnopDiv">
        <button onClick="window.location.href='bestelling_plaatsen.php'">Bestelling plaatsen</button>    
        </div>
        <div>
            <table>
                <?php
                    echo "<th>Bestelnr</th>";
                    echo "<th>plaatsnaam</th>";
                    echo "<th>Artikel</th>";
                    echo "<th>Type</th>";
                    echo "<th>Fabriek</th>";
                    echo "<th>Aantal Besteld</th>";
                    echo "<th>Besteld-datum</th>";

                    if($_SESSION['userrole'] === "Admin"){
                        echo "<th>Waarde bestelling</th>";
                        echo "<th>Aankomst</th>";
                    }
                    
                    echo "<th>Afgeleverd?</th>";

                    

                    while($row = $result1->fetch_assoc()) {
                        $rowColor = ($row['is_afgeleverd'] === 'Ja') ? 'rgba(90, 249, 90, 0.65)' : '';

                        echo "<tr>";
                        echo "<td style='background-color: " .$rowColor. ";'>" . $row['bestellingsNummer'] . "</td>";
                        echo "<td style='background-color: " .$rowColor. ";'>" . $row['plaatsnaam'] . "</td>";
                        
                        echo "<td style='background-color: " .$rowColor. ";'>" . $row['product'] . "</td>";
                        echo "<td style='background-color: " .$rowColor. ";'>" . $row['type'] . "</td>";
                        echo "<td style='background-color: " .$rowColor. ";'>" . $row['fabrieksnaam'] . "</td>";
                        echo "<td style='background-color: " .$rowColor. ";'>" . $row['aantal_besteld'] . "</td>";
                        echo "<td style='background-color: " .$rowColor. ";'>" . $row['besteldatum'] . "</td>";
                        if($_SESSION['userrole'] === "Admin"){
                            echo "<td style='background-color: " .$rowColor. ";'>" ."€". $row['waarde_bestelling'] . "</td>";
                            echo "<td style='background-color: " .$rowColor. ";'>" . $row['aankomstdatum'] . "</td>";
                        }
                        echo "<td style='background-color: " .$rowColor. ";'>" . $row['is_afgeleverd'] . "</td>";
                        
                        if($row['is_afgeleverd'] !== "Ja"){
                            echo "<td>" ."<button id='row".$row['bestellingsNummer']."' onclick='afhandelenBestelling(". $row['bestellingsNummer'] .",".$row['idArtikel'].",".$row['idLocaties'].",".$row['aantal_besteld'].")'>Bestelling afhandelen</button>". "</td>";
                        }
                        echo "</tr>";

                    }
                    
                ?>
            </table>
        </div>
        
    </section>


    <script src="script.js"></script>
</body>
</html>