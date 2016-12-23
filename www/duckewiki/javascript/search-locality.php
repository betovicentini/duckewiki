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
	(SELECT DISTINCT Country as nome, CONCAT('paisid_',CountryID) as nomeid FROM Country  WHERE LOWER(Country) LIKE '%".$searchq."%') 
	UNION 
	(SELECT DISTINCT CONCAT(Province,' (',Country,')')  as nome, CONCAT('provinceid_',ProvinceID)  as nomeid FROM Province JOIN Country USING(CountryID)  WHERE LOWER(Province) LIKE '%".$searchq."%') 
	UNION 
	(SELECT DISTINCT CONCAT(Municipio,' (',Province,' ',Country,')') as nome, CONCAT('municipioid_',MunicipioID) as nomeid FROM Municipio JOIN Province USING(ProvinceID) JOIN Country USING(CountryID)  WHERE LOWER(Municipio) LIKE '%".$searchq."%')
	UNION 
	(SELECT DISTINCT CONCAT(PathName,' (',Municipio,' ',Province,' ',Country,')') as nome, CONCAT('gazetteerid_',GazetteerID) as nomeid FROM Gazetteer JOIN Municipio  USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE LOWER(PathName) LIKE '%".$searchq."%')";
	$getRecord	= mysql_query($getRecord_sql,$conn);
	$ngetRecord	= mysql_numrows($getRecord);

	if($ngetRecord>0){
			echo "<ul>
<li><a href=\"javascript:substitui('','".$idtag."','".$idres."', '0', '".$nomeid."');\">-----------------</a></li>";			
			while ($row = mysql_fetch_array($getRecord)) {
				echo "
<li><a href=\"javascript:substitui('".($row['nome'])."','".$idtag."','".$idres."', '".$row['nomeid']."', '".$nomeid."');\">".($row['nome'])."</a></li>";
			} 	
			echo "
</ul>";
	} elseif (strlen($searchq)>0) {
		echo "
<ul>
<li>".GetLangVar('naoencontrado')."</li>
</ul>";
	}
?>