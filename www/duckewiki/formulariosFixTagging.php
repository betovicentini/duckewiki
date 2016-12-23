<?php
//Start session
set_time_limit(0);

session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";

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
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);
HTMLheaders($body);


$qq = "SELECT * FROM Formularios";
$res = mysql_query($qq,$conn);
$nres = mysql_numrows($res);
if ($nres>0) {
echo "
<br><table cellpadding='7' class='myformtable' align='left'>
<thead>
 <tr><td colspan='100%'>Atualização de Formulários</td></tr>
 <tr class='subhead'><td>Nome</td><td>N variáveis</td></tr>
</thead>
<tbody>";
while ($row = mysql_fetch_assoc($res)) {
	$traitarr = explode(";",$row['FormFieldsIDS']);
	$traitarr = array_unique($traitarr);
	$fnome = $row['FormName'];
	$formnome = 'formid_'.$row['FormID'];
	$qq = "UPDATE `Traits` SET `FormulariosIDS`=removeformularioidfromtraits(`FormulariosIDS`,'".$formnome."') WHERE `FormulariosIDS` LIKE '%".$formnome."' OR `FormulariosIDS` LIKE '%".$formnome.";%'";
	$nr = mysql_query($qq,$conn);
	$updated=0;
	foreach ($traitarr as $value) {
		$vlu = $value+0;
		$sql = "UPDATE `Traits` SET Traits.`FormulariosIDS`=IF(Traits.`FormulariosIDS`<>'',CONCAT(Traits.`FormulariosIDS`,';','".$formnome."'),'".$formnome."') WHERE Traits.`TraitID`='".$vlu."'";
		$upsql = mysql_query($sql,$conn);
		if ($upsql) {
			$updated++;
		}
	}
	if ($updated==count($traitarr)) {
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
		echo "
  <tr bgcolor = $bgcolor><td  align='center'>$fnome</td><td align='center'>".count($traitarr)."</td></tr>";
	}
	//flush();	
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <form action='index.php' method='post'>
  <td align='center' colspan='100%'><input type='submit' value='".GetLangVar('nameconcluir')."' class='bsubmit'></td>
  </form>
</tr>
</tbody>
</table>";
}

HTMLtrailers();

?>