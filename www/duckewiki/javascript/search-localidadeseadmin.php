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
$getRecord_sql= "
SELECT tbtemp.nome, tbtemp.nomeid, tbtemp.tipo FROM (
(SELECT Country as nome, CountryID as nomeid, 'country' as tipo FROM  Country WHERE Country LIKE '%".$searchq."%')
UNION
(SELECT CONCAT(Province,' [',Country,']') as nome, ProvinceID as nomeid, 'province' as tipo FROM  Province JOIN Country USING(CountryID) WHERE Province LIKE '%".$searchq."%')
UNION
(SELECT CONCAT(Municipio,' [',Province,' - ',Country,']') as nome, MunicipioID as nomeid, 'municipio' as tipo FROM Municipio JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE Municipio LIKE '%".$searchq."%')
UNION
(SELECT DISTINCT CONCAT(gaz.PathName,' [',Municipio,'- ',Province,' - ',Country,']') as nome, gaz.GazetteerID as nomeid, 'gazetteer'  FROM Gazetteer as gaz JOIN Municipio  USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE gaz.PathName LIKE '%".$searchq."%' ORDER BY gaz.PathName)) as tbtemp ORDER BY tbtemp.nome";
	$getRecord = mysql_query($getRecord_sql,$conn);
	$ngetRecord = mysql_numrows($getRecord);
	if($ngetRecord>0){
			echo '<ul>';
			echo "<li><a href=\"javascript:substitui(' ','".$idtag."','".$idres."', '0', '".$nomeid."');\">----------</a></li>";
			while ($row = mysql_fetch_array($getRecord)) {
				$iddn = $row['tipo']."_".$row['nomeid'];
				echo "<li><a href=\"javascript:substitui('".($row['nome'])."','".$idtag."','".$idres."', '".$iddn."', '".$nomeid."');\">".($row['nome'])."</a></li>";
			} 
			echo '</ul>';
	} elseif (strlen($searchq)>0) {
		echo '<ul>';
				echo "<li><a href=\"javascript:substitui(' ','".$idtag."','".$idres."', '0', '".$nomeid."');\">Não encontrado</a></li>";
			echo '</ul>';
	}
?>