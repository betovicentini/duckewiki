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
$getRecord_sql = "SELECT tr.TraitName as nome,tr.TraitID,tr.PathName FROM Traits as tr WHERE LOWER(tr.TraitName) LIKE '%".$searchq."%' OR getparentname(tr.ParentID) LIKE '%".$searchq."%'  ORDER BY tr.TraitName,getparentname(tr.ParentID)";
$getRecord = mysql_query($getRecord_sql,$conn);
$ngetRecord = mysql_numrows($getRecord);

	if($ngetRecord>0){
echo "<ul>
<li><a href=\"javascript:substitui('','".$idtag."','".$idres."', '0', '".$nomeid."');\">-----------------</a></li>";			
			while ($row = mysql_fetch_array($getRecord)) {
				$tgn = $row['nome']."  (".$row['PathName'].")";
				echo "
<li><a href=\"javascript:substitui('".$tgn."','".$idtag."','".$idres."', '".$row['TraitID']."', '".$nomeid."');\">".$tgn."</a></li>";
			} 	
			echo '</ul>';
	} elseif (strlen($searchq)>0) {
echo "
<ul>
<li><a href=\"javascript:substitui('','".$idtag."','".$idres."', '0', '".$nomeid."');\">".GetLangVar('naoencontrado')."</a></li>
</ul>";
	}
?>