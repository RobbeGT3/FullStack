<?php

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    die("Page not available");
}

$data = json_decode(file_get_contents('php://input'), true); 

$conn = require_once "common/connectionDB.php";

function waardeBerekenen($hoeveelheid, $prijsProduct){
    return number_format((float)$hoeveelheid * (float)$prijsProduct, 2, '.', '');
}

function bestellingUpdaten($conn, $param){
    $afleverstatus = "Ja";
    $aankomstDatum = date('Y-m-d');
    $stmt = $conn->prepare("UPDATE Bestelling SET is_afgeleverd =?,aankomstdatum =? WHERE Bestelling.bestellingsNummer =?;");
    $stmt->bind_param("ssi", $afleverstatus,$aankomstDatum,$param);
    $stmt->execute();
    $stmt->close();
}

if (isset($data['bestellingsNummer'])){

    $ingevoerdeAantal = $data['aantal_besteld'] ;
    $verkoopWaarde = null;
    $inkoopWaarde = null;
    $nieuwAantal = null;
    
    $stmt1 = $conn->prepare("SELECT Voorraad.aantal FROM Voorraad WHERE Voorraad.idLocaties = ? AND Voorraad.idArtikel = ?;");
    $stmt1->bind_param("ii", $data['idLocaties'], $data['idArtikel']);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $voorraadData = $result1->fetch_assoc();

    $stmt2 = $conn->prepare("SELECT Artikel.inkoopprijs, Artikel.verkoopprijs FROM Artikel WHERE Artikel.idArtikel = ?;");
    $stmt2->bind_param("i", $data['idArtikel']);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $prijsInformatie = $result2->fetch_assoc();

    if($result1->num_rows > 0){
        //bestaand product bijwerken

        $nieuwAantal = $voorraadData['aantal']+$data['aantal_besteld'];
        $verkoopWaarde = waardeBerekenen($nieuwAantal, $prijsInformatie['verkoopprijs']);
        $inkoopWaarde = waardeBerekenen($nieuwAantal,$prijsInformatie['inkoopprijs']);

        $stmt3 = $conn->prepare("UPDATE Voorraad SET  aantal = ?, inkoopwaarde = ?, verkoopwaarde = ? WHERE idLocaties = ? and idArtikel = ?;");
        $stmt3->bind_param("iddii", $nieuwAantal,$inkoopWaarde,$verkoopWaarde, $data['idLocaties'], $data['idArtikel']);
        $stmt3->execute();
        $stmt3->close();

        bestellingUpdaten($conn, $data['bestellingsNummer']);

        echo json_encode(["Bestelling afgehandeld"]);

    }else{
        //nieuw product
        $verkoopWaarde = waardeBerekenen($ingevoerdeAantal, $prijsInformatie['verkoopprijs']);
        $inkoopWaarde = waardeBerekenen($ingevoerdeAantal,$prijsInformatie['inkoopprijs']);

        if ($verkoopWaarde === null || $inkoopWaarde === null) {
            echo json_encode(["error" => "Prijsinformatie ontbreekt voor dit artikel."]);
            exit;
        }

        $stmt3 = $conn->prepare("INSERT INTO Voorraad(idLocaties, idArtikel, aantal, inkoopwaarde, verkoopwaarde) VALUES (?,?,?,?,?);");
        $stmt3->bind_param("iiidd",$data['idLocaties'],$data['idArtikel'],$data['aantal_besteld'],$inkoopWaarde,$verkoopWaarde);
        $stmt3->execute();
        $stmt3->close();

        bestellingUpdaten($conn, $data['bestellingsNummer']);

        echo json_encode(["Bestelling afgehandeld"]);
    }

    $stmt1->close();
    $stmt2->close();
    $conn->close();
}
?>