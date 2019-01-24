<?php
// Lecture de la page wikipedia avec la liste des départements

echo "\nLecture de la page wikipedia\n";
$sHtml = '';
$fp = fopen("https://fr.wikipedia.org/wiki/Liste_des_d%C3%A9partements_fran%C3%A7ais", "r");

while(!feof($fp)) {
    echo "\tlecture de 32 Ko de données\n";
    $sRead = fread($fp, 32768);
    if ($sRead!==false) {
//        $sHtml = $sHtml . $sRead;
        $sHtml .= $sRead;
    }
}

fclose($fp);

echo "\nEcriture du fichier exo3-html-departements.txt\n";
$fp = fopen("exo3-html-departements.txt", "w");
fwrite($fp, $sHtml);
fclose($fp);

echo "Terminé\n";
