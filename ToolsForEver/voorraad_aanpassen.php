<?php


$conn = require_once "common/connectionDB.php";
$locatieID = $_POST['plaats'];
$artikelID = $_POST['artikel'];

$stmt1 = $conn->prepare("SELECT Voorraad.aantal, Artikel.inkoopprijs, Artikel.verkoopprijs FROM Voorraad
INNER JOIN Artikel ON Voorraad.idArtikel = Artikel.idArtikel WHERE Voorraad.idLocaties = ? AND Voorraad.idArtikel = ?;");
$stmt1->bind_param("ii", $locatieID, $artikelID);
$stmt1->execute();
$result1 = $stmt1->get_result();
$voorraadData = $result1->fetch_assoc();

$ingevoerdeAantal = trim($_POST['aantal']);
$verkoopPrijs = null;
$inkoopPrijs = null;

function waardeBerekenen($hoeveelheid, $prijsProduct){
    return number_format($hoeveelheid * $prijsProduct, 2, '.', '');
}

if($result1->num_rows > 0){
    $nieuwAantal = null;
    $verkoopPrijs = $voorraadData['verkoopprijs'];
    $inkoopPrijs = $voorraadData['inkoopprijs'];

    if(!empty($_POST['aantal'])){

        if($_POST['actie'] === "Add"){
            $nieuwAantal = $voorraadData['aantal'] + $ingevoerdeAantal;
        
        }elseif($_POST['actie'] === "Delete"){
            $nieuwAantal = $voorraadData['aantal'] - $ingevoerdeAantal;
            
        }

    }

    if($nieuwAantal < 0){
        echo "Error: Kan niet meer afhalen dan beschikbaar is";
        exit;
    }

    if($nieuwAantal > 0){
        $verkoopWaarde = waardeBerekenen($nieuwAantal, $verkoopPrijs);
        $inkoopkoopWaarde = waardeBerekenen($nieuwAantal, $inkoopPrijs);

        $stmt2 = $conn->prepare("UPDATE Voorraad SET  aantal = ?, inkoopwaarde = ?, verkoopwaarde = ? WHERE idLocaties = ? and idArtikel = ?;");
        $stmt2->bind_param("iddii", $nieuwAantal,$inkoopkoopWaarde,$verkoopWaarde, $locatieID, $artikelID);
        $stmt2->execute();
        $stmt2->close();

    }else{

        $stmt2 = $conn->prepare("DELETE FROM Voorraad WHERE idLocaties = ? and idArtikel = ?;");
        $stmt2->bind_param("ii",$locatieID,$artikelID);
        $stmt2->execute();
        $stmt2->close();

    }


}else{

    $stmt2 = $conn->prepare("SELECT Artikel.idArtikel, Artikel.inkoopprijs, Artikel.verkoopprijs FROM Artikel WHERE Artikel.idArtikel = ?;");
    $stmt2->bind_param("i",$artikelID);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $prijzenData = $result2->fetch_assoc();

    $verkoopPrijs = $prijzenData['verkoopprijs'];
    $inkoopPrijs = $prijzenData['inkoopprijs'];
    

    $verkoopWaarde = waardeBerekenen($ingevoerdeAantal, $verkoopPrijs);
    $inkoopkoopWaarde = waardeBerekenen($ingevoerdeAantal, $inkoopPrijs);

    $stmt3 = $conn->prepare("INSERT INTO Voorraad(idLocaties, idArtikel, aantal, inkoopwaarde, verkoopwaarde) VALUES (?,?,?,?,?);");
    $stmt3->bind_param("iiidd",$locatieID,$artikelID,$ingevoerdeAantal,$inkoopkoopWaarde,$verkoopWaarde);
    $stmt3->execute();
    $stmt2->close();
    $stmt3->close();
}

$stmt1->close();
$conn->close();
header('location: voorraad.php');
exit;
 

?>