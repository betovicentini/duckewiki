<?php
session_start();

//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
require_once($relativepathtoroot.$databaseconnection_clean);

//$uuid = cleanQuery($_SESSION['userid'],$conn);


$uuid = $_POST['uuid'];
$specid = $_POST['especimenid'];
$hoje = $_POST['hoje'];

$txt = '';

if ($traitsilica>0) {
	if ($specid>0) {
			$qs = "SELECT TraitID FROM Traits WHERE ParentID='".$traitsilica."' AND LOWER(TraitName) LIKE '%silica%'";
			//echo $qs;
			$rs = mysql_query($qs);
			$nrs = mysql_numrows($rs);
			if ($nrs==1) {
					$rsw = mysql_fetch_assoc($rs);
					$silicavar = $rsw['TraitID'];
					$qn = "SELECT * FROM Traits_variation WHERE TraitID='".$traitsilica."' AND EspecimenID='".$specid."'  AND (TraitVariation LIKE '".$silicavar."%'  OR TraitVariation LIKE '%;".$silicavar."%') ";
					$rn = mysql_query($qn);
					$nrn = mysql_numrows($rn);
					if ($nrn>0) {
						$txt .=  "Já está marcado!";
					} else {
						$qnn = "SELECT * FROM Traits_variation WHERE TraitID='".$traitsilica."' AND EspecimenID='".$specid."'";
						$rnn = mysql_query($qnn);
						$nrnn = mysql_numrows($rnn);
						if ($nrnn>0) {
							$rww = mysql_fetch_assoc($rnn);
							$tri = $rww['TraitVariationID'];
							$oldvar = explode(";",$rww['TraitVariation']);
							$newvar =  array_merge((array)$oldvar,(array)$silicavar);
							$newvar = implode(";",$newvar);
							$qbse = "UPDATE Traits_variation SET TraitVariation='".$newvar."' WHERE TraitVariationID=".$tri; 
						} else {
							$qbse = "INSERT INTO Traits_variation (TraitID, TraitVariation,EspecimenID,AddedBy,AddedDate) VALUES (
'".$traitsilica."','".$silicavar."','".$specid."','".$uuid."','".$hoje."')"; 
						}
						$rii = mysql_query($qbse);
						if ($rii) {
							$txt .=  "Foi marcado com sucesso";
						} else {
							$txt .=  "Houve um erro e não foi marcado";
						}
					}
			} 
			else {
				$txt .=  "Não há definição de um caractere para amostras em silica";
			}
	} 
	else {
		$txt .=  "Não há definição do especímene";
	}
} 
else {
$txt .=  "Não há definição de um caractere para amostras em silica";
}

echo $txt;
?>