<?php
set_time_limit(0);

//Start session
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
include_once("functions/class.Numerical.php") ;


$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);


$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 


$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

HTMLheaders($body);

$qq  = "SELECT spid,PlantaID,PlantaTag FROM (SELECT IF(sp.EspecimenID>0,sp.EspecimenID,0) as spid,pl.PlantaID,pl.PlantaTag FROM Plantas as pl LEFT JOIN Especimenes as sp ON sp.PlantaID=pl.PlantaID 
JOIN FiltrosSpecs as fl ON fl.PlantaID=pl.PlantaID WHERE fl.FiltroID=".$filtro.") as zz WHERE zz.spid=0";
$rr = mysql_query($qq,$conn);
while($row = mysql_fetch_assoc($rr)) {
	$plid = $row["PlantaID"];
	$colnum = $row["PlantaTag"];

	$qu = "SELECT DISTINCT DataObs FROM Monitoramento WHERE PlantaID='".$plid."' LIMIT 0,1";
	$rer = mysql_query($qu,$conn);
	$rew = mysql_fetch_assoc($rer);
	$data = $rew['DataObs'];
	$dd = explode("-",$data);
	$coll = 74 ;
	$arrayofvalues = array(
		'ColetorID' => $coll,
		'Number' => $colnum,
		'Day' => $dd[2],
		'Mes' => $dd[1],
		'Ano' => $dd[0],
		'PlantaID' => $plid);
	//echopre($arrayofvalues);
	$newspec = InsertIntoTable($arrayofvalues,'EspecimenID','Especimenes',$conn);
	if ($newspec) {
		$arrayofvalues2 = array(
		'TraitID' => 99,
		'TraitVariation' => 102,
		'PlantaID' => $plid,
		'EspecimenID' => $newspec);
		$zz = InsertIntoTable($arrayofvalues2,'TraitVariationID','Traits_variation',$conn);
		if ($zz) {
			echo $colnum." ok<br>";
		}
	}
}



HTMLtrailers();

?>