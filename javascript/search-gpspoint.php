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

	$searchq		=	strip_tags($_GET['q']);
	$searchq		=	strtolower($searchq);
	$getRecord_sql	=	"
	SELECT DISTINCT CONCAT('Ponto ',gps.Name,' --',gaz.PathName,' ',Municipio,' ',Province,' ',Country,IF((gps.GPSName+0)>0,CONCAT(' [',equip.Name,'] '),'')) as nome, PointID as nomeid FROM GPS_DATA as gps LEFT JOIN Gazetteer as gaz USING(GazetteerID) LEFT  JOIN Municipio  USING(MunicipioID) LEFT  JOIN Province USING(ProvinceID) LEFT  JOIN Country USING(CountryID) LEFT JOIN Equipamentos as equip ON gps.GPSName=equip.EquipamentoID WHERE LOWER(CONCAT('Ponto ',gps.Name,' --',gaz.PathName,' ',Municipio,' ',Province,' ',Country)) LIKE '%".$searchq."%' AND LOWER(gps.Type)='waypoint' ORDER BY gaz.PathName,gps.Name";
	$getRecord	= mysql_query($getRecord_sql,$conn);
	$ngetRecord	= mysql_numrows($getRecord);

	if($ngetRecord>0){
			echo "
<ul>
<li><a href=\"javascript:substitui('','".$idtag."','".$idres."', '0', '".$nomeid."');\">-----------------</a></li>";

			while ($row = mysql_fetch_array($getRecord)) {
				echo "
<li><a href=\"javascript:substitui('".($row['nome'])."','".$idtag."','".$idres."', '".$row['nomeid']."', '".$nomeid."');\">".($row['nome'])."</a></li>";
			} 	
			echo '</ul>';
	} elseif (strlen($searchq)>0) {
		echo "
<ul>
<li><a href=\"javascript:substitui('','".$idtag."','".$idres."', '0', '".$nomeid."');\">".GetLangVar('naoencontrado')."</a></li>
</ul>";

	}
?>