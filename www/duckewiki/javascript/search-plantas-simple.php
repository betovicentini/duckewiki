<?php 
session_start();
//Check whether the session variable
if(!isset($_SESSION['userid']) || 
	(trim($_SESSION['userid'])=='')) {
		header("location: access-denied.php");
	exit();
} 

include "../functions/databaseSettings.php";
require_once "../".$relativepathtoroot.$databaseconnection;

$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
	
$idtag = strip_tags($_GET['idtag']);
$idres = strip_tags($_GET['idres']);
$nomeid = strip_tags($_GET['nomeid']);
?>
<?php

	$searchq = strip_tags($_GET['q']);
	$searchq = mb_strtolower($searchq);
	//$getRecord_sql= "SELECT PlantaID,PlantaTag,GazetteerTIPOtxt,Gazetteer,InSituExSitu FROM Plantas JOIN Gazetteer USING(GazetteerID) WHERE PlantaTag LIKE '".$searchq."%' ORDER BY SUBSTRING(PlantaTag FROM 6)+0";
	$getRecord_sql= "SELECT PlantaID,PlantaTag, gettaxonname(DetID,1,0) as especie FROM Plantas LEFT JOIN Gazetteer as gaz ON Plantas.GazetteerID=gaz.GazetteerID LEFT JOIN Gazetteer as par ON par.GazetteerID=gaz.ParentID WHERE PlantaTag LIKE '".$searchq."%' ORDER BY PlantaTag LIMIT 0,20";
	//echo "<br >".$getRecord_sql."<br />";
	$getRecord = mysql_query($getRecord_sql,$conn);
	$ngetRecord = mysql_numrows($getRecord);
	if($ngetRecord>0){
echo "
<ul>
  <li><a href=\"javascript:substitui('','".$idtag."','".$idres."', '0', '".$nomeid."');\">----------------------</a></li>";
			while ($row = mysql_fetch_array($getRecord)) {
				$plantnum = $row['PlantaTag'];
				$gaz = $row['Local'];
				$tgn = $plantnum." - ".$gaz." [".$row['especie']."]";
				echo "
  <li><a href=\"javascript:substitui('".$tgn."','".$idtag."','".$idres."', '".$row['PlantaID']."', '".$nomeid."');\">".$tgn."</a></li>";
			} 	
			echo '
</ul>';
	} elseif (strlen($searchq)>0) {
		echo "
<ul>
  <li><a href=\"javascript:substitui('','".$idtag."','".$idres."', '0', '".$nomeid."');\">".GetLangVar('naoencontrado')."</a></li>
</ul>";
	}
?>