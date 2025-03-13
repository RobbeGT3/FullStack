<?php

$conn = require_once "common/connectionDB.php";


if($_SERVER['REQUEST_METHOD'] === 'GET'|| $_SERVER['REQUEST_METHOD'] === 'POST'){
    $stmt1 = $conn->prepare("SELECT Voorraad.idLocaties , Voorraad.idArtikel, Locatie.plaatsnaam, Artikel.product, Artikel.type, Fabriek.fabrieksnaam, Voorraad.aantal, Voorraad.inkoopwaarde, Voorraad.verkoopwaarde FROM Voorraad
    INNER JOIN Artikel ON Voorraad.idArtikel = Artikel.idArtikel
    INNER JOIN Locatie ON Voorraad.idLocaties = Locatie.idLocaties
    INNER JOIN Fabriek ON Artikel.idFabriek = Fabriek.idFabriek");
    $stmt1->execute();
    $result = $stmt1->get_result();

}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $sqlQuery = "SELECT Voorraad.idLocaties , Voorraad.idArtikel, Locatie.plaatsnaam, Artikel.product, Artikel.type, Fabriek.fabrieksnaam, Voorraad.aantal, Voorraad.inkoopwaarde, Voorraad.verkoopwaarde FROM Voorraad
    INNER JOIN Artikel ON Voorraad.idArtikel = Artikel.idArtikel
    INNER JOIN Locatie ON Voorraad.idLocaties = Locatie.idLocaties
    INNER JOIN Fabriek ON Artikel.idFabriek = Fabriek.idFabriek";

    $conditions = [];
    $params = [];
    $types = "";

    if(!empty($_POST['locatie'])){
        $conditions[] = "Locatie.plaatsnaam LIKE ?";
        $params[] = "%" . trim($_POST['locatie']) . "%";
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <script src="script.js"></script>
    <section class="topSection">
        <div>
            <div>
                <button onclick="window.location.href='portaal.php'">terug</button>
            </div>
            <div>
                <h1>Voorraad</h1>
            </div>
            <div>
                <button onclick="window.location.href='voorraad.php'">refresh</button>
            </div>
        </div>
    </section>
    <div>
        <button onclick="openPopup2('popupInvoerform', 'overlay')">Toevoegen aan voorraad.</button>
        <button>Overzicht Opties.</button>
    </div>
    <section class="tableSection">
            <div class="opzoekForm">
                <form action="" method='POST'>
                    <h1>Opzoeken bij attribuut:</h1>
                    <div class="form-group">
                        <label for="locatie">Locatie:</label>
                        <input type="text" id="locatie" name="locatie">
                    </div>
                    <div class="form-group">
                        <label for="product">Product:</label>
                        <input type="text" id="product" name="product">
                    </div>
                    <div class="form-group">
                        <label for="type">Type:</label>
                        <input type="text" id="type" name="type">
                    </div>
                    <div class="form-group">
                        <label for="fabriek">Fabriek:</label>
                        <input type="text" id="fabriek" name="fabriek">
                    </div>
                    <div>
                        <button type="submit" class="submit">Search item</button>
                    </div>

                </form>
            </div>
            <div class="voorraadTabel">
                <table>
                    <tr>
                        <th>Locatie</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Fabriek</th>
                        <th>Aantal</th>
                        <th>Inkoopwaarde</th>
                        <th>Verkoopwaarde</th>

                    </tr>
                    <?php
                        while($row = $result->fetch_assoc()){
                            echo "<tr>";
                            echo "<td>" . $row['plaatsnaam'] . "</td>";
                            echo "<td>" . $row['product'] . "</td>";
                            echo "<td>" . $row['type'] . "</td>";
                            echo "<td>" . $row['fabrieksnaam'] . "</td>";
                            echo "<td>" . $row['aantal'] . "</td>";
                            echo "<td>" ."€". $row['inkoopwaarde'] . "</td>";
                            echo "<td>" ."€". $row['verkoopwaarde'] . "</td>";
                            echo "</tr>";
                        }  
                        
                        if ($result->num_rows == 0) {
                            exit('Geen data gevonden');
                        }
                    ?>
                </table>
            </div>
    </section>

    <div id="overlay" class="overlay" onclick="closePopup2('popupInvoerform', 'overlay')"></div>
    <div id="popupInvoerform" class="popupInvoerform">
        <form action="voorraad_aanpassen.php" method="POST">
            <div>
                <h2 class="titelPopUp">Invoersherm</h2>
                <button type="reset" class="cancel" onclick="closePopup2('popupInvoerform', 'overlay')">Close</button>
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
                                $query2 = $conn->prepare("SELECT Artikel.idArtikel, Artikel.product, Artikel.type ,Fabriek.fabrieksnaam FROM Artikel INNER join Fabriek on Artikel.idFabriek = Fabriek.idFabriek ORDER BY Artikel.product ASC;");
                                $query2->execute();
                                $resultForm2 = $query2->get_result();
                                if ($resultForm2->num_rows == 0) {
                                    exit('No rows');
                                }

                                while ($rowForm2 = $resultForm2->fetch_assoc()) {
                                    echo "<option value='".$rowForm2['idArtikel']."' >" .
                                    htmlspecialchars($rowForm2['product'])." - ".htmlspecialchars($rowForm2['type'])." - ".htmlspecialchars($rowForm2['fabrieksnaam']).
                                    "</option>";
                                }
                                $query2->close();
                                ?>
                    </select>
                    <label for="test">test</label>
                    <select name="test" id="test">
                        <option value="1">test</option>
                        <option value="2">test</option>
                        <option value="3">test</option>
                    </select>
                </div>
                <div class="invoerPopUp-group">
                    <p>Product niet in lijst?</p>
                    <Button type="Button" onclick="alert('Functie work in progress')">Voeg product toe</Button>
                </div>
            </div>
            
            <div class="invoerPopUp-group">
                <label for="aantal">Hoeveelheid:</label>
                <input type="text" id="aantal" name="aantal" required>
            </div>
            <div class="invoerPopUp-group">
                <label for="actie">Toevoegen of Afhalen:</label>
                <select name="actie" id="actie">
                    <option value="Add">Toevoegen</option>
                    <option value="Delete">Afhalen</option>
                </select>
            </div>
            <div>
                <button type="submit" class="submit">Invoeren</button>
            </div>

                
        </form>

    </div>
</body>
</html>