<?php 

session_start();

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    die("Page not available");
}

$conn = require_once "common/connectionDB.php";

function waardeBerekenen($hoeveelheid, $prijsProduct){
    return number_format((float)$hoeveelheid * (float)$prijsProduct, 2, '.', '');
}


function prijsOpvragen($conn, $param){
    $stmt = $conn->prepare("SELECT idArtikel, inkoopprijs FROM Artikel WHERE idArtikel = ?;");
    $stmt->bind_param("i", $param);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $data = $result->fetch_assoc(); 
        return $data['inkoopprijs']; 
    } else {
        return null; 
    }
}


if($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    if (!empty($_POST['locatie']) && !empty($_POST['product']) && !empty($_POST['aantal'])){
        $huidigeDatum = date('Y-m-d');
        

        $product = (int)$_POST['product'];
        $locatie = (int)$_POST['locatie'];

        $hoeveelheidBesteld = (int)trim($_POST['aantal']);
        $afgeleverd = "Ja";
        
        $prijs = NULL;
        $waardeBestelling = null;

        $stmt1 = $conn->prepare("SELECT Bestelling.bestellingsNummer, Bestelling.idArtikel, Bestelling.idLocaties, Bestelling.aantal_besteld, Bestelling.besteldatum, Bestelling.aankomstdatum, Bestelling.is_afgeleverd, Bestelling.waarde_bestelling FROM Bestelling WHERE Bestelling.idArtikel = ? AND Bestelling.idLocaties = ? and Bestelling.besteldatum = ? and Bestelling.is_afgeleverd != ?;");
        $stmt1->bind_param("iiss", $product, $locatie, $huidigeDatum,$afgeleverd);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        $data = $result1->fetch_assoc();

        if($result1->num_rows > 0){

            $nieuweHoeveelheid = $data['aantal_besteld'] + $hoeveelheidBesteld;
            $prijs = prijsOpvragen($conn,$product);
            $waardeBestelling = waardeBerekenen($nieuweHoeveelheid,$prijs);
            echo $waardeBestelling. "<br>". $nieuweHoeveelheid;

    
            $stmt2 = $conn->prepare("UPDATE Bestelling SET aantal_besteld = ?, waarde_bestelling = ? WHERE Bestelling.bestellingsNummer = ?;");
            $stmt2->bind_param("idi",$nieuweHoeveelheid, $waardeBestelling,$data['bestellingsNummer']);
            $stmt2->execute();
            $stmt2->close();

            echo "<script type='text/javascript'>
                    window.location.href = 'bestellingen.php';
                    </script>";
        }else{
    
            $prijs = prijsOpvragen($conn,$product);
            $waardeBestelling = waardeBerekenen($hoeveelheidBesteld,$prijs);
            $afleverstatus = "Nee";
            
            $stmt2 = $conn->prepare("INSERT INTO Bestelling(bestellingsNummer, idArtikel, idLocaties, aantal_besteld, besteldatum, is_afgeleverd, waarde_bestelling) VALUES (NULL,?,?,?,?,?,?);");
            $stmt2->bind_param("iiissd",$product, $locatie,$hoeveelheidBesteld, $huidigeDatum, $afleverstatus, $waardeBestelling);
            $stmt2->execute();
            $stmt2->close();
    
    
            echo "<script type='text/javascript'>
                    window.location.href = 'bestellingen.php';
                    </script>";
        }

    }else{
        echo "data not complete";
    }

    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestellen</title>
    <link rel="stylesheet" href="styling/opmaak.css">
</head>
<body>
    <div id = 'test'>
    <h1>Plaats bestelling:</h1>
        <form action="" method="POST">
        <div class="invoerPopUp-group">
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
                            echo "<option value='".$rowForm['idLocaties']."' >" .
                            htmlspecialchars($rowForm['plaatsnaam']).
                            "</option>";
                        }
                        $query->close();
                        ?>
                        </select>
                </div>
        <div class="invoerPopUp-group">
                <label for="product">Product:</label>
                    <select name="product" id="product">
                        <option value="">-- Selecteer een locatie --</option> 
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
                <label for="aantal">Aantal besteld:</label>
                <input type="text" id="aantal" name="aantal" placeholder="Aantal besteld">
        </div>
            
            <div>
                <button type="submit" class="submitBestelling">Submit</button>
                <button type="button" class="cancelBestelling" onClick="window.location.href='bestellingen.php'">Cancel</button>
            </div>
        </form>
    </div>
</body>
</html>
