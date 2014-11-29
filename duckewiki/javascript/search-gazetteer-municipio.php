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
	$municipioid = $_GET['municipioid']
?>
<?php

	$searchq = strip_tags($_GET['q']);
	$searchq = strtolower($searchq);
	$getRecord_sql = "
	SELECT DISTINCT CONCAT(gaz.PathName,' [',Municipio,'- ',Province,' - ',Country,']') as nome, gaz.GazetteerID as nomeid FROM Gazetteer as gaz JOIN Municipio  USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE gaz.PathName LIKE '%".$searchq."%' AND gaz.MunicipioID=".$municipioid." ORDER BY gaz.PathName";
	$getRecord = mysql_query($getRecord_sql,$conn);
	$ngetRecord = mysql_numrows($getRecord);

	if($ngetRecord>0){
			echo '<ul>';
			echo "<li><a href=\"javascript:substitui(' ','".$idtag."','".$idres."', '0', '".$nomeid."');\">----------</a></li>";
			while ($row = mysql_fetch_array($getRecord)) {
				echo "<li><a href=\"javascript:substitui('".($row['nome'])."','".$idtag."','".$idres."', '".$row['nomeid']."', '".$nomeid."');\">".($row['nome'])."</a></li>";
			} 	
			echo '</ul>';
	} elseif (strlen($searchq)>0) {
		echo '<ul>';
				echo "<li><a href=\"javascript:substitui(' ','".$idtag."','".$idres."', '0', '".$nomeid."');\">".GetLangVar('naoencontrado')."</a></li>";
			echo '</ul>';
	}
?>