<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
require_once($relativepathtoroot.$databaseconnection_clean);

//$uuid = cleanQuery($_SESSION['userid'],$conn);

$uuid = $_POST['uuid'];
$specid = $_POST['especimenid'];
$pltid = $_POST['plantaid'];
$hoje = $_POST['hoje'];
$minhares = '';
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
						$minhares .=  "Já está marcado!";
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
							$qpl = "SELECT PlantaID FROM Especimenes WHERE EspecimenID=".$specid;
							$rnpl = mysql_query($qpl);
							$rwpl = mysql_fetch_assoc($rnpl);
							$pltid = $rwpl['PlantaID']+0;
							$qbse = "INSERT INTO Traits_variation (TraitID, TraitVariation,EspecimenID,PlantaID,AddedBy,AddedDate) VALUES (
'".$traitsilica."','".$silicavar."','".$specid."','".$pltid."','".$uuid."','".$hoje."')"; 
						}
						$rii = mysql_query($qbse);
						if ($rii) {
							$minhares .=  "Foi marcado com sucesso";
						} else {
							$minhares .=  "Houve um erro e não foi marcado";
						}
					}
			} 
			else {
				$minhares .=  "Não há definição de um caractere para amostras em silica";
			}
	} 
	else {
		if ($pltid>0) {
					$qs = "SELECT TraitID FROM Traits WHERE ParentID='".$traitsilica."' AND LOWER(TraitName) LIKE '%silica%'";
			//echo $qs;
			$rs = mysql_query($qs);
			$nrs = mysql_numrows($rs);
			if ($nrs==1) {
					$rsw = mysql_fetch_assoc($rs);
					$silicavar = $rsw['TraitID'];
					$qn2 = "SELECT EspecimenID FROM Especimenes WHERE PlantaID='".$pltid."'";
					$rn2 = mysql_query($qn2);
					$nrn2 = mysql_numrows($rn2);
					$ntem = array();
					$osspecs = array();
					if ($nrn2>0) {
						while($rw2 = mysql_fetch_assoc($rn2)) {
							$osspecs[] = $rw2['EspecimenID'];
							$qn3 = "SELECT TraitVariationID FROM Traits_variation WHERE TraitID='".$traitsilica."' AND EspecimenID='".$rw2['EspecimenID']."'  AND (TraitVariation LIKE '".$silicavar."%'  OR TraitVariation LIKE '%;".$silicavar."%') ";
							$rn3 = mysql_query($qn3);
							$nrn3 = mysql_numrows($rn3);
							if ($nrn3>0) {
								$rw3 = mysql_fetch_assoc($rn3);
								$ntem[] = $rw3['TraitVariationID'];
							}
						}
					} 
					$qn = "SELECT * FROM Traits_variation WHERE TraitID='".$traitsilica."' AND PlantaID='".$pltid."'  AND (TraitVariation LIKE '".$silicavar."%'  OR TraitVariation LIKE '%;".$silicavar."%') ";
					$rn = mysql_query($qn);
					$nrn = mysql_numrows($rn);
					$ntem2 = count($ntem);
					#JÁ TEM VARIACAO ENTAO ATUALIZA APENAS
					if ($nrn>0 || $ntem2>0) {
						if ($ntem2>0) {
							foreach($ntem as $vtrvar) {
								$qbse3 = "UPDATE Traits_variation SET PlantaID='".$pltid."' WHERE TraitVariationID=".$vtrvar." AND ((PlantaID IS NULL) OR PlantaID=0)";
								mysql_query($qbase3);
							} 
						} 
						$minhares .=  "Já está marcado!";
					} else {
						#nao tem registro para especimenes da planta ou da planta
						$ospec = count($osspecs); #tem especimenes
						$traittem = array();
						if ($ospec>0) { #se tiver especimenes
							foreach ($osspecs as $osp) {
								$qnn4 = "SELECT TraitVariationID FROM Traits_variation WHERE TraitID='".$traitsilica."' AND EspecimenID='".$osp."'";
								$rn4 = mysql_query($qnn4);
								$nr4 = mysql_numrows($rn4);
								if($nr4>0) {
									$rw4 = mysql_fetch_assoc($rn4);
									$traittem[]  = $rw4['TraitVariationID'];
								}
							}
						} else {
							$qnn5 = "SELECT * FROM Traits_variation WHERE TraitID='".$traitsilica."' AND PlantaID='".$pltid."'";
							$rnn5 = mysql_query($qnn5);
							$nrnn5 = mysql_numrows($rnn5);
							if ($nrnn5>0) {
								$rww5 = mysql_fetch_assoc($rnn5);
								$traittem[] = $rww5['TraitVariationID'];
							}
						}
						$traittem = array_unique($traittem);
						$temtr = count($traittem);
						$atualizou =0;
						if ($temtr>0) {
							foreach($temtr as $tri) {
								$tri = $rww['TraitVariationID'];
								$qnn6 = "SELECT * FROM Traits_variation WHERE TraitVariationID='".$tri."'";
								$rnn6 = mysql_query($qnn6);
								$rnww6 = mysql_fetch_assoc($rnn6);
								$oldvar = explode(";",$rnww6['TraitVariation']);
								$newvar =  array_merge((array)$oldvar,(array)$silicavar);
								$newvar = array_unique($newvar);
								$newvar = implode(";",$newvar);
								$qbse = "UPDATE Traits_variation SET TraitVariation='".$newvar."' WHERE TraitVariationID=".$tri; 
								mysql_query("SET FOREIGN_KEY_CHECKS=0");
								$rii = mysql_query($qbse);
								mysql_query("SET FOREIGN_KEY_CHECKS=1");
								if ($rii) {
									$atualizou++;
								}
							}
						} 
						else {
							$ospec = count($osspecs); 
							if ($ospec>0) {
								$specid = $osspecs[0];
							} else {
								$specid = 0;
							}
							$qbse = "INSERT INTO Traits_variation (TraitID, TraitVariation,EspecimenID,PlantaID,AddedBy,AddedDate) VALUES (
'".$traitsilica."','".$silicavar."','".$specid."','".$pltid."','".$uuid."','".$hoje."')"; 
							mysql_query("SET FOREIGN_KEY_CHECKS=0");
							$rii = mysql_query($qbse);
							mysql_query("SET FOREIGN_KEY_CHECKS=1");
							if ($rii) {
								$atualizou++;
							}
						}
						if ($atualizou>0) {
							$minhares .=  "Foi marcado com sucesso";
						} else {
							$minhares .=  "Houve um erro e não foi marcado".$qbse;
						}
					}
			} 
			else {
				$minhares .=  "Não há definição de um caractere para amostras em silica";
			}
			} else {
				$minhares .=  "Não há definição do especímene";
			}
	}
} 
else {
$minhares .=  "Não há definição de um caractere para amostras em silica";
}
echo strtoupper("\n\n".$minhares);
?>