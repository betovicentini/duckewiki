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
$searchq = strtolower($searchq);
$getRecord_sql = "SELECT DISTINCT CONCAT(IF(child.HabitatTipo='Local',parent.PathName,UPPER(child.PathName)),' ',IF(child.GPSPointID>0,CONCAT(gpsgaz.PathName,'  GPS-Pt-',gps.Name),
IF(child.LocalityID>0,gaz.PathName,child.Habitat))) as nome,child.HabitatID, child.HabitatTipo FROM Habitat as child LEFT JOIN Gazetteer as gaz ON child.LocalityID=gaz.GazetteerID LEFT JOIN Habitat as parent ON parent.HabitatID=child.ParentID LEFT JOIN GPS_DATA as gps ON child.GPSPointID=gps.PointID LEFT JOIN Gazetteer as gpsgaz ON gps.GazetteerID=gpsgaz.GazetteerID WHERE LOWER(CONCAT(IF(child.HabitatTipo='Local',parent.PathName,UPPER(child.PathName)),' ',IF(child.GPSPointID>0,CONCAT(gpsgaz.PathName,'  GPS-Pt-',gps.Name),
IF(child.LocalityID>0,gaz.PathName,IF(child.Habitat IS NOT NULL,child.Habitat,''))))) LIKE '%".$searchq."%' ORDER BY child.HabitatTipo,child.PathName ASC";
$getRecord = mysql_query($getRecord_sql,$conn);
$ngetRecord = mysql_numrows($getRecord);

if($ngetRecord>0){
echo "
<ul>
  <li><a href=\"javascript:substitui('','".$idtag."','".$idres."', '0', '".$nomeid."');\">-----------------</a></li>";
  	$ht =  '';
	while ($row = mysql_fetch_array($getRecord)) {
		$tipo = $row['HabitatTipo'];
		if (strtoupper($tipo)!=$ht) {
echo "  <li style='background-color: #FFCC00'><a href=\"javascript:substitui('','".$idtag."','".$idres."', '0', '".$nomeid."');\">$tipo</a></li>"; 			
			$ht= strtoupper($tipo);		
		}
		if ($tipo!='Local') {
			echo "
  <li style='background-color: lightyellow'>";
		} else {
			echo "
  <li>";
		}
			echo "<a href=\"javascript:substitui('".($row['nome'])."','".$idtag."','".$idres."', '".$row['HabitatID']."', '".$nomeid."');\">".$row['nome']."</a></li>";
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