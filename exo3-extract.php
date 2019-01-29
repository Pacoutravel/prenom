<?php

define("FICHIER_HTML", "exo3-html-departements.txt");
define("FICHIER_CSV", "exo3-departements.csv");

echo "Extraction des données de " . FICHIER_HTML . "\n";
$sContenuTable = extractTableFromFile();
$aDepartement = extractDataFromTable($sContenuTable);

echo "Ecriture du fichier " . FICHIER_CSV . "\n";
$fp = fopen(FICHIER_CSV, 'w');
fputcsv($fp, array('dep_code', 'dep-name'));
foreach($aDepartement as $aRow) {
    fputcsv($fp, $aRow);
}
fclose($fp);

echo "Terminé.\n";

//// Functions
function extractTableFromFile()
{
    //<table class="wikitable sortable centre">
    //... Table content ...
    //</table>

    $sTableContent = '';
    $bDebutTableTrouve = false;
    $bFinTableTrouve = false;

    $fp = fopen(FICHIER_HTML, 'r');

    while(!feof($fp) && !$bFinTableTrouve) {
        $sLine = fgets($fp);

        if (!$bDebutTableTrouve) {
            $recherche1 = stristr( $sLine, '<table' );
            $recherche2 = stristr( $sLine, 'class="wikitable sortable centre"' );

            if ( $recherche1!==false && $recherche2!==false ){
                $bDebutTableTrouve = true;
            }
        } else {
            $recherche3 = stristr( $sLine, '</table>' );

            if ( $recherche3!==false ){
                $bFinTableTrouve = true;
            } else {
                $sTableContent .= $sLine;
            }
        }
    }
    fclose($fp);

    return($sTableContent);
}

function extractDataFromTable($sTableContent)
{
    //<tr>
    //... Row content ...
    //</tr>

    $sTRTag = "<tr>";
    $nLenTag1 = strlen($sTRTag);
    $sEndTRTag = "</tr>";
    $nLenTag2 = strlen($sEndTRTag);

    $aTableData = false;

    $nPos1 = strpos($sTableContent, $sTRTag);
    while ($nPos1!==false) {
        $nPos2 = strpos($sTableContent, $sEndTRTag);

        $sTRContent = substr($sTableContent, $nPos1+$nLenTag1, $nPos2-$nPos1-$nLenTag1);

        $aDep =  extractDataFromLine($sTRContent);

        if ($aDep!==false) {
            if ($aTableData===false) {
                $aTableData = array();
            }
            array_push($aTableData, $aDep);
        }

        $sTableContent = substr($sTableContent, $nPos2+$nLenTag2);

        $nPos1 = strpos($sTableContent, $sTRTag);
    }

    return($aTableData);
}

function extractDataFromLine( $sTRContent )
{
    //<td><span data-sort-value="20-!" style="display:none;">&#8205;</span>2A</td>
    //<td><a href="/wiki/Corse-du-Sud" title="Corse-du-Sud">Corse-du-Sud</a> <br /><span style="font-size:0.8em; line-height: 1.2em;">(f.s.)</span></td>
    //<td>... ignored ...</td>

    $aLineData = false;

    $sTDTag = "<td";
    $sEndTDTag = "</td>";
    $nLenTag2 = strlen($sEndTDTag);

    $nCntTD = 0;
    $sDepartementCode = '';
    $sDepartementName = '';

    $nPos1 = strpos($sTRContent, $sTDTag);
    while ( ($nPos1!==false) && ($nCntTD<=2) ) {
        // Skip <td> tag
        $nPos3 = strpos($sTRContent, '>');
        $sTRContent = substr($sTRContent, $nPos3+1);

        // cut before end tag
        $nPos2 = strpos($sTRContent, $sEndTDTag);
        $sUnTD = substr($sTRContent, 0, $nPos2);
        $nCntTD++;

        switch($nCntTD) {
            case 1:
                $nPosSpan = strpos($sUnTD, '</span>');
                if ($nPosSpan !== false) {
                    $sUnTD = substr($sUnTD, $nPosSpan+strlen('</span>'));
                }
                $sDepartementCode = $sUnTD;
                break;
            case 2:
                $sUnTD = substr($sUnTD, 0, strpos($sUnTD, '</a>'));
                $sUnTD = substr($sUnTD, strpos($sUnTD, '>')+1);
                $sDepartementName = $sUnTD;
                break;
            default:
                break;
        }

        // keep the rest
        $sTRContent = substr($sTRContent, $nPos2+$nLenTag2);

        $nPos1 = strpos($sTRContent, $sTDTag);
    }

    if ($sDepartementCode!=='') {
        $aLineData = [
            'dep_code'       => $sDepartementCode,
            'dep_name'       => $sDepartementName
        ];
    }

    return($aLineData);
}
