var x = document.getElementById("myAudio"); 

const logo = document.getElementById("logo").addEventListener("click", playAudio);

function playAudio() { 
    x.play(); 
};


function openPopup(popupID, overlayID) {
    document.getElementById(popupID).style.display = 'block';
    document.getElementById(overlayID).style.display = 'block';
};


function closePopup(popupID, overlayID) {
    document.getElementById(popupID).style.display = 'none';
    document.getElementById(overlayID).style.display = 'none';
};


function afhandelenBestelling(bestelnummer,artikelID, locatieID, aantal) {
    currentOrdernummer = bestelnummer;
    currentLocatieID = locatieID;
    currentArtikelID = artikelID;
    currentAantal = aantal;
    if (currentOrdernummer) {
        fetch('bestelling_afhandelen.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                bestellingsNummer: currentOrdernummer,
                idLocaties: currentLocatieID,
                idArtikel: currentArtikelID,
                aantal_besteld: currentAantal
             }),
        })
        .then(response => response.text()) 
        .then(data => {
            alert(data);
            location.reload(); 
        })
        .catch(error => console.error('Error:', error));
    }
}