<?php
session_start();

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    die("Page not available");
}

$conn = require_once "common/connectionDB.php";

if($_SERVER['REQUEST_METHOD'] === 'GET'|| $_SERVER['REQUEST_METHOD'] === 'POST'){
    $stmt1 = $conn->prepare("SELECT Voorraad.idLocaties , Voorraad.idArtikel, Locatie.plaatsnaam, Artikel.product, Artikel.type, Artikel.minimum_aantal, Fabriek.fabrieksnaam, Voorraad.aantal, Voorraad.inkoopwaarde, Voorraad.verkoopwaarde FROM Voorraad
    INNER JOIN Artikel ON Voorraad.idArtikel = Artikel.idArtikel
    INNER JOIN Locatie ON Voorraad.idLocaties = Locatie.idLocaties
    INNER JOIN Fabriek ON Artikel.idFabriek = Fabriek.idFabriek");
    $stmt1->execute();
    $result = $stmt1->get_result();

}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $sqlQuery = "SELECT Voorraad.idLocaties , Voorraad.idArtikel, Locatie.plaatsnaam, Artikel.product, Artikel.type, Artikel.minimum_aantal, Fabriek.fabrieksnaam, Voorraad.aantal, Voorraad.inkoopwaarde, Voorraad.verkoopwaarde FROM Voorraad
    INNER JOIN Artikel ON Voorraad.idArtikel = Artikel.idArtikel
    INNER JOIN Locatie ON Voorraad.idLocaties = Locatie.idLocaties
    INNER JOIN Fabriek ON Artikel.idFabriek = Fabriek.idFabriek";

    $conditions = [];
    $params = [];
    $types = "";

    if(!empty($_POST['locatie'])){
        $conditions[] = "Locatie.plaatsnaam = ?";
        $params[] = $_POST['locatie'];
        $types .= "s";
    }

    if(!empty($_POST['product'])){
        $conditions[] = "Artikel.product LIKE ?";
        $params[] = "%" . trim($_POST['product']) . "%";
        $types .= "s";
    }

    if(!empty($_POST['type'])){
        $conditions[] = "Artikel.type LIKE ?";
        $params[] = "%" . trim($_POST['type']) . "%";
        $types .= "s";
    }

    if(!empty($_POST['fabriek'])){
        $conditions[] = "Fabriek.fabrieksnaam LIKE ?";
        $params[] = "%" . trim($_POST['fabriek']) . "%";
        $types .= "s";
    }

    if(!empty($conditions)){
        $sqlQuery .= " WHERE " . implode(" AND ", $conditions);
        $stmt1 = $conn->prepare($sqlQuery);
        $stmt1->bind_param($types, ...$params);
        $stmt1->execute();
        $result = $stmt1->get_result();
    }

}




?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voorraad ToolsForEver</title>
    <link rel="stylesheet" href="styling/opmaak.css">
</head>
<body>
    <audio id="myAudio">
    <source src="styling\Audios\omni-man-are-you-sure.mp3" type="audio/mpeg">
    </audio>
    
    <section class="topSection">
        <h1>Voorraad</h1>
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
    <div id="toevoegKnopDiv">
        <button onclick="openPopup('popupInvoerform', 'overlay')" <?php if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true){?> disabled <?php   } ?>>Voorraad bijwerken</button>
    </div>
    <section id="tableSection">
            <div class="opzoekForm">
                <form action="" method='POST'>
                    <div class="">
                        <h1>Opzoeken bij attribuut:</h1>
                    </div>
                    <div class="form-group">
                        <label for="locatie">Locatie:</label>
                        <select name="locatie" id="locatie">
                            <option value="">-- Selecteer een locatie --</option> 
                            <?php
                                $query = $conn->prepare("SELECT * FROM Locatie");
                                $query->execute();
                                $resultForm = $query->get_result();
                                if ($resultForm->num_rows == 0) {
                                    exit('No rows');
                                }

                                while ($rowForm = $resultForm->fetch_assoc()) {
                                    echo "<option value='".$rowForm['plaatsnaam']."' >" .
                                    htmlspecialchars($rowForm['plaatsnaam']).
                                    "</option>";
                                }
                                $query->close();
                                ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="product">Product:</label>
                        <input type="text" id="product" name="product" placeholder="Zoeken op product">
                    </div>
                    <div class="form-group">
                        <label for="type">Type:</label>
                        <input type="text" id="type" name="type" placeholder="Zoeken op modelnummer/type">
                    </div>
                    <div class="form-group">
                        <label for="fabriek">Fabriek:</label>
                        <input type="text" id="fabriek" name="fabriek" placeholder="Zoeken op fabriek">
                    </div>
                    <div>
                        <button type="submit" class="submit">Zoek item</button>
                    </div>

                </form>
            </div>
            <div class="voorraadTabel">
                <table>
                    <?php

                        echo "<th>Locatie</th>";
                        echo "<th>Product</th>";
                        echo "<th>Type</th>";
                        echo "<th>Fabriek</th>";
                        echo "<th>Aantal</th>";

                        if($_SESSION['userrole'] === "Admin"){
                            echo "<th>minimum aantal</th>";
                            echo "<th>Inkoopwaarde</th>";
                            echo "<th>Verkoopwaarde</th>";
                        }
                        

                        while($row = $result->fetch_assoc()){
                            $rowColor = '';
                            if ($row['aantal'] < $row['minimum_aantal']) {
                                $rowColor = 'rgba(255, 85, 0, 0.51)';
                            }else{
                                $rowColor = '';
                            }



                            echo "<tr>";
                            echo "<td>" . $row['plaatsnaam'] . "</td>";
                            echo "<td>" . $row['product'] . "</td>";
                            echo "<td>" . $row['type'] . "</td>";
                            echo "<td>" . $row['fabrieksnaam'] . "</td>";
                            echo "<td>" . $row['aantal'] . "</td>";

                            if($_SESSION['userrole'] === "Admin"){
                                echo "<td>" . $row['minimum_aantal'] . "</td>";
                                echo "<td>" ."€". $row['inkoopwaarde'] . "</td>";
                                echo "<td>" ."€". $row['verkoopwaarde'] . "</td>";
                            }
                            if($row['aantal']<$row['minimum_aantal']){
                                
                                echo "<td style='background-color: " .$rowColor. ";'>" ."  " . "</td>";
                            }
                            
                            echo "</tr>";

                        }

                        if ($result->num_rows == 0) {
                            exit('Geen data gevonden');
                        }
                        
                        
                            
                    ?>
                </table>
            </div>
    </section>

    <div id="overlay" class="overlay" onclick="closePopup('popupInvoerform', 'overlay')"></div>
    <div id="popupInvoerform" class="popupInvoerform">
        <form action="voorraad_aanpassen.php" method="POST">
            <div>
                <h2 class="titelPopUp">Invoersherm</h2>
                <button type="reset" class="cancel" onclick="closePopup('popupInvoerform', 'overlay')">Close</button>
                <hr>
            </div>
            <div class="invoerPopUp-group">
                <label for="plaats">Plaats:</label>
                <select name="plaats" id="plaats">
                    <?php 
                        $query1 = $conn->prepare("SELECT * FROM Locatie;");
                        $query1->execute();
                        $resultForm1 = $query1->get_result();

                        if ($resultForm1->num_rows == 0) {
                            exit('No rows');
                        }

                        while ($rowForm1 = $resultForm1->fetch_assoc()) {
                            echo "<option value='". $rowForm1["idLocaties"] ."' >" .
                            htmlspecialchars($rowForm1['plaatsnaam']).
                            "</option>";
                        }
                        $query1->close();
                    
                    
                    ?>

                    </select>
                    
            </div>
            <div class="row">
                <div class="invoerPopUp-group">
                    <label for="artikel">Product:</label>
                    <select name="artikel" id="artikel">
                            <?php
                                $query = $conn->prepare("SELECT Artikel.idArtikel, Artikel.product, Artikel.type ,Fabriek.fabrieksnaam FROM Artikel INNER join Fabriek on Artikel.idFabriek = Fabriek.idFabriek ORDER BY Artikel.product ASC;");
                                $query->execute();
                                $resultForm = $query->get_result();
                                if ($resultForm->num_rows == 0) {
                                    exit('No rows');
                                }

                                while ($rowForm = $resultForm->fetch_assoc()) {
                                    echo "<option value='".$rowForm['idArtikel']."' >" .
                                    htmlspecialchars($rowForm['product'])." - ".htmlspecialchars($rowForm['type'])." - ".htmlspecialchars($rowForm['fabrieksnaam']).
                                    "</option>";
                                }
                                $query->close();
                                ?>
                    </select>
                    
                </div>
                <div class="invoerPopUp-group">
                    <p>Geregistreerde product overzicht</p>
                    <Button type="Button" onclick="window.location.href = 'product.php'">Overzicht Producten</Button>
                </div>
            </div>
            
            <div class="invoerPopUp-group">
                <label for="aantal">Hoeveelheid:</label>
                <input type="text" id="aantal" name="aantal" required>
            </div>
            <div>
                <label for="actie">Toevoegen of Afhalen:</label><br>
                <input type="radio" id="" name="actie" value="Add" checked>
                <label for="actie">Toevoegen</label><br>
                <input type="radio" id="actie" name="actie" value="Delete">
                <label for="actie">Afhalen</label><br>
            </div>
            <div>
                <button type="submit" class="submit">Invoeren</button>
            </div>

                
        </form>

    </div>
    <script src="script.js"></script>
</body>
</html>