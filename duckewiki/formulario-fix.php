<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//echopre($ppost);
//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$title = 'Ajustar Formularios';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$qq = "SELECT * FROM Formularios";
$res = mysql_query($qq,$conn);
$nres = mysql_numrows($res);
if ($nres>0) {
echo "
<br><table cellpadding='7' class='myformtable' align='center'>
<thead>
 <tr><td colspan='100%'>Atualização de Formulários</td></tr>
 <tr class='subhead'><td>Nome</td><td>N variáveis</td></tr>
</thead>
<tbody>";
while ($row = mysql_fetch_assoc($res)) {
	$traitarr = explode(";",$row['FormFieldsIDS']);
	$traitarr = array_unique($traitarr);
	$fnome = $row['FormName'];
	$formid = $row['FormID'];
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
				$qq = "CREATE TABLE IF NOT EXISTS FormulariosTraitsList (
FormID INT(10),
TraitID INT(10),
Ordem INT(10))
CHARACTER SET utf8";
 @mysql_query($qq,$conn);
		$qn = "DELETE FROM FormulariosTraitsList WHERE FormID='".$formid."'";
		@mysql_query($qn,$conn);
		//$trarr = explode(";",$trids);
		//echopre($traitarr);
		$nz = count($traitarr);
		$count = 0;
		foreach ($traitarr as $tri) {
			$tri = $tri+0;
			if ($tri>0) {
			$qz = "INSERT INTO FormulariosTraitsList (FormID,TraitID,Ordem) SELECT ".$formid.",TraitID,".$count." FROM Traits WHERE TraitID=".$tri;
			//echo $qz."<br /><br />";
			$rr = mysql_query($qz,$conn);
			if ($rr) {
				$count++;
			}
			}
		}

	}
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



$which_java = array();
FazFooter($which_java,$calendar=TRUE,$footer=$menu);

?>