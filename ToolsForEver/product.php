<?php 
session_start();

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    die("Page not available");
}

$conn = require_once "common/connectionDB.php";
$stmt1 = $conn->prepare("SELECT Artikel.idArtikel, Artikel.product, Artikel.type, Fabriek.idFabriek, Fabriek.fabrieksnaam, Artikel.inkoopprijs, Artikel.verkoopprijs, Artikel.minimum_aantal FROM Artikel INNER JOIN Fabriek on Artikel.idFabriek = Fabriek.idFabriek ORDER BY Artikel.product ASC;");
$stmt1->execute();
$result1 = $stmt1->get_result();

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $ingevoerdeProduct = trim($_POST['product']);
    $ingevoerdeModel = trim($_POST['type']);
    $fabriek = null;
    $ingevoerdeInkoopprijs = number_format((float)str_replace(',','.',$_POST['inkoopprijs']), 2, '.', '');
    $ingevoerdeVerkoopprijs = number_format((float)str_replace(',','.',$_POST['verkoopprijs']), 2, '.', '');

    
    $stmt2 = $conn->prepare("SELECT Artikel.idArtikel, Artikel.product, Artikel.type, Fabriek.idFabriek, Fabriek.fabrieksnaam, Artikel.inkoopprijs, Artikel.verkoopprijs FROM Artikel INNER JOIN Fabriek on Artikel.idFabriek = Fabriek.idFabriek WHERE Artikel.product = ? AND Artikel.type = ? AND Artikel.idFabriek = ?;");
    $stmt2->bind_param("ssi",$ingevoerdeProduct, $ingevoerdeModel, $_POST['fabriek']);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $result2Data = $result2->fetch_assoc();

    if($result2->num_rows > 0){

        echo "<script type='text/javascript'>
                alert('Product al geregistreerd');
                </script>"; 

    }else{

        if(!empty($_POST['product']) && isset($_POST['fabriek']) && $_POST['fabriek'] !== ""){
            $fabriek = $_POST['fabriek'];
            

            $columns = [];
            $params = [];
            $types = "";

            $columns[] = "product = ?";
            $params[] = $ingevoerdeProduct;
            $types .= "s"; 
    
            if (!empty($_POST['type'])) {
                $columns[] = "type = ?";
                $params[] = $ingevoerdeModel;
                $types .= "s"; 
            }
            
            $columns[] = "idFabriek = ?";
            $params[] = $fabriek;
            $types .= "i"; 
    
            if (!empty($_POST['inkoopprijs'])) {
                $columns[] = "inkoopprijs = ?";
                $params[] = $ingevoerdeInkoopprijs;
                $types .= "d";
            }
    
            if (!empty($_POST['verkoopprijs'])) {
                $columns[] = "verkoopprijs = ?";
                $params[] = $ingevoerdeVerkoopprijs;
                $types .= "d";
            }

            if (!empty($_POST['minimum_aantal'])) {
                $columns[] = "minimum_aantal = ?";
                $params[] = $ingevoerdeModel;
                $types .= "i"; 
            }
    
            
            if (!empty($columns)) {
                $sqlQuery = "INSERT INTO Artikel SET " . implode(", ", $columns);
                $stmt = $conn->prepare($sqlQuery);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $stmt->close();

                echo "<script type='text/javascript'>
                alert('Product toegevoegd');
                </script>"; 

            } else {
                echo "<script type='text/javascript'>
                alert('Er is een fout opgetreden');
                </script>"; 
            }
        }

    }

        

};


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product toevoegen</title>
    <link rel="stylesheet" href="styling/opmaak.css">
</head>
<body>
    <audio id="myAudio">
    <source src="styling\Audios\omni-man-are-you-sure.mp3" type="audio/mpeg">
    </audio>

    <section class="topSection">
        <h1>Producten Overzicht</h1>
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
    <section id="tableSection2">
        <div class="productToevoegForm">
            <form action="" method='POST'>
                <div class="">
                    <h1>product voegen:</h1>
                </div>
                <div class="">
                    <label for="product">product:</label>
                    <input type="text" id="product" name="product" required placeholder="Product">
                </div>
                <div class="">
                    <label for="type">type:</label>
                    <input type="text" id="type" name="type" placeholder="Type/Modelnummer">
                </div>
                <div class="">
                    <label for="fabriek">fabriek:</label>
                    <select name="fabriek" id="fabriek" >
                    <option value="">-- Select a factory --</option> 
                        <?php
                            $query = $conn->prepare("SELECT Fabriek.idFabriek, Fabriek.fabrieksnaam FROM Fabriek;");
                            $query->execute();
                            $resultForm = $query->get_result();
                            if ($resultForm->num_rows == 0) {
                                exit('No rows');
                            }

                            while ($rowForm = $resultForm->fetch_assoc()) {
                                echo "<option value='".$rowForm['idFabriek']."' >" .
                                htmlspecialchars($rowForm['fabrieksnaam']).
                                "</option>";
                            }
                            $query->close();
                            ?>
                    </select>
                </div>
                <div class="">
                    <label for="inkoopprijs">inkoopprijs:</label>
                    <input type="text" id="inkoopprijs" name="inkoopprijs" placeholder="Inkoopprijs product">
                </div>
                <div class="">
                    <label for="verkoopprijs">verkoopprijs:</label>
                    <input type="text" id="verkoopprijs" name="verkoopprijs" placeholder="Verkoopprijs product">
                </div>

                <div class="">
                    <label for="minimum_aantal">minimum aantal:</label>
                    <input type="text" id="minimum_aantal" name="minimum_aantal" placeholder="minimum aantal product">
                </div>

                <div>
                    <button type="submit" class="submit">Voeg product toe</button>
                </div>

            </form>
        </div>
        <div class="voorraadTabel">
            <h1>Geregistreerde producten</h1>
            <table>
                <?php 
                    echo "<th>id</th>";
                    echo "<th>Product</th>";
                    echo "<th>Type</th>";
                    echo "<th>Fabriek</th>";
                    if($_SESSION['userrole'] === "Admin"){
                        echo "<th>Inkoopprijs</th>";
                        echo "<th>Verkoopprijs</th>";
                        echo "<th>minimum_aantal</th>";
                    }

                    while($row = $result1->fetch_assoc()){
                        echo "<tr>";
                        echo "<td>" . $row['idArtikel'] . "</td>";
                        echo "<td>" . $row['product'] . "</td>";
                        echo "<td>" . $row['type'] . "</td>";
                        echo "<td>" . $row['fabrieksnaam'] . "</td>";
                        if($_SESSION['userrole'] === "Admin"){
                            echo "<td>" ."€". $row['inkoopprijs'] . "</td>";
                            echo "<td>" ."€". $row['verkoopprijs'] . "</td>";
                            echo "<td>" . $row['minimum_aantal'] . "</td>";
                        }
                        echo "</tr>";
                            
                    if ($result1->num_rows == 0) {
                            exit('Geen data gevonden');
                    
                        }    
                    }
                
                
                ?>
            </table>
        </div>
    </section>
    <script src="script.js"></script>
</body>
</html>
