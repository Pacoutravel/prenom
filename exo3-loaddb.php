<?php

define("FICHIER_CSV", "exo3-departements.csv");
define("FICHIER_PRENOM", "dpt2017_txt.zip#dpt2017.txt");
define("FICHIER_ZIP", "dpt2017_txt.zip");

define("DB_HOST", "localhost");
define("DB_USER", "user_prenom");
define("DB_PWD",  "pwd_prenom");
define("DB_BASE", "prenom");

$dbLink = mysqli_connect(DB_HOST, DB_USER, DB_PWD)
    or die("Impossible de se connecter : " . mysqli_error());

if (mysqli_select_db($dbLink, DB_BASE)) {

    if (file_exists(FICHIER_CSV)) {
        truncateTable("departement");
        loadDepartement(FICHIER_CSV);
    }

    if (file_exists(FICHIER_ZIP)) {
        truncateTable("prenom");
        loadPrenom(FICHIER_PRENOM);
    }
}

mysqli_close($dbLink);


function loadDepartement($sFichierCsv)
{
    global $dbLink;

    if (($fp = fopen($sFichierCsv, 'r')) !== false) {

        $query = "INSERT INTO departement (dep_code, dep_name) VALUES (?,?)";
        $stmt = mysqli_prepare($dbLink, $query);
        mysqli_stmt_bind_param($stmt, "ss", $dep_code, $dep_name);

        $nRow = 0;
        while (($aData = fgetcsv($fp, 1024, ",")) !== false) {
            $nRow++;

            if ($nRow>1) {
                $dep_code = $aData[0];
                $dep_name = $aData[1];
                mysqli_stmt_execute($stmt);
            }
        }

        fclose($fp);
        echo $nRow. " départements importés\n";
    }

}

function loadPrenom($sFichierPrenom)
{
    global $dbLink;

    $nRow = 0;

    $query = "INSERT INTO prenom (prenom, sexe, annais, dep_code, nombre) VALUES (?,?,?,?,?)";
    $stmt = mysqli_prepare($dbLink, $query);
    mysqli_stmt_bind_param($stmt, "ssssd", $prenom, $sexe, $annais, $dep_code, $nombre);

    $fp = fopen("zip://$sFichierPrenom", 'r');
    while (!feof($fp)) {
        $sLine = fgets($fp);
        $aData = explode("\t", $sLine);
        $bReject = false;

        if ($aData[0]!=='1' && $aData[0]!=='2') {
            $bReject = true;
        }

        if (!ctype_digit($aData[2]) && !ctype_digit($aData[3]) && ! ctype_digit($aData[4])) {
            $bReject = true;
        }

        if (!$bReject) {
            $prenom = $aData[1];
            $sexe = str_replace(['1','2'], ['M','F'], $aData[0]);
            $annais = $aData[2];
            $dep_code = $aData[3];
            $nombre = intval($aData[4]);
            mysqli_stmt_execute($stmt);

            $nRow++;
        } else {
            echo "ligne rejetee " . $sLine;
        }

    }
    fclose($fp);
    echo $nRow . " lignes prénom importées\n";
}

function truncateTable($sTable)
{
    global $dbLink;

    $sQueryTemplate = "TRUNCATE TABLE %s";

    if (!mysqli_query($dbLink, sprintf($sQueryTemplate, $sTable))) {
           echo sprintf("Erreur : %s\n", mysqli_error($dbLink));
    } else {
        echo "Table $sTable truncated\n";
    }
}
