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
$tagunitid = strip_tags($_GET['tagunitid']);
?>
<?php
$searchq = strip_tags($_GET['q']);
$searchq = strtolower($searchq);
$getRecord_sql = "SELECT CONCAT(tr.TraitName,' (',tr1.TraitName,') [',tr.TraitTipo,']') as nome,tr.TraitID,tr.TraitTipo,tr.TraitUnit FROM Traits as tr JOIN Traits as tr1 ON tr.ParentID=tr1.TraitID WHERE (tr.TraitTipo LIKE '%Categoria%' OR tr.TraitTipo LIKE '%Quantita%' OR tr.TraitTipo LIKE '%Texto%') AND CONCAT(tr.TraitName,' (',tr1.TraitName,') [',tr.TraitTipo,']') LIKE '%".$searchq."%' ORDER BY tr.TraitName,tr1.TraitName";
$getRecord = mysql_query($getRecord_sql,$conn);
$ngetRecord = mysql_numrows($getRecord);
if($ngetRecord>0){
echo "
<ul>
  <li ><a href=\"javascript:substitui('','".$idtag."','".$idres."', '', '".$nomeid."');\">------------</a></li>
  ";
while ($row = mysql_fetch_array($getRecord)) {
	$zz = explode("|",$row['TraitTipo']);
	$tgn = $row['nome'];
	if ($zz[1]=='Quantitativo') {
		$tgunit = $row['TraitUnit'];
		echo "
  <li ><a href=\"javascript:substituiwithunit('".$tgn."','".$idtag."','".$idres."', '".$row['TraitID']."', '".$nomeid."','".$tagunitid."','".$tgunit."');\">".$tgn."</a></li>";
	} else {
		echo "
  <li ><a href=\"javascript:substitui('".$tgn."','".$idtag."','".$idres."', '".$row['TraitID']."', '".$nomeid."');\">".$tgn."</a></li>";
	}
}
echo '
</ul>';
} elseif (strlen($searchq)>0) {
echo "
<ul>
  <li>".GetLangVar('naoencontrado')."</li>
</ul>";
}
?>