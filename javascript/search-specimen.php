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
	(SELECT DISTINCT CONCAT(Abreviacao,' ',Number) as nome, EspecimenID FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE LOWER(CONCAT(Abreviacao,' ',Number)) LIKE '%".$searchq."%' ORDER BY Abreviacao,Number+0)";
	$getRecord	= mysql_query($getRecord_sql,$conn);
	$ngetRecord	= mysql_numrows($getRecord);

	if($ngetRecord>0){
echo "
<ul>
  <li><a href=\"javascript:substitui('','".$idtag."','".$idres."', '0', '".$nomeid."');\">-----------------</a></li>";
 			while ($row = mysql_fetch_array($getRecord)) {
				echo "
  <li><a href=\"javascript:substitui('".($row['nome'])."','".$idtag."','".$idres."', '".$row['EspecimenID']."', '".$nomeid."');\">".$row['nome']."</a></li>";
			} 	
echo "
</ul>";
	} elseif (strlen($searchq)>0) {
		echo "
<ul>
  <li><a href=\"javascript:substitui('','".$idtag."','".$idres."', '0', '".$nomeid."');\">".GetLangVar('naoencontrado')."</a></li>
</ul>";
	}
?>