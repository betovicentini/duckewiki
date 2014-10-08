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

//CABECALHO
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Plot Trees in Map';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<table align='center' class='myformtable' cellspacing='0' cellpadding='7'>
<thead>
<tr><td colspan='100%'>Mapear árvores em uma parcela</td></tr>
</thead>
<tbody>
<form name='finalform' action='plotTreesInMap-exec.php' method='post' >";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldleft'>Parcela</td>
  <td>
    <select name='gazetteerid' onchange='this.form.submit();'>
      <option selected value=''>".GetLangVar('nameselect')."</option>";
			//$qq = "SELECT DISTINCT gaz.GazetteerID,gaz.GazetteerTIPOtxt,gaz.Gazetteer FROM Plantas JOIN Gazetteer as gaz USING(GazetteerID) WHERE gaz.DimX>0 AND gaz.DimY>0 ORDER BY gaz.ParentID,gaz.GazetteerTIPOtxt,gaz.Gazetteer";
			$qq = "SELECT DISTINCT gaz.GazetteerID,gaz.Gazetteer FROM Plantas JOIN Gazetteer as gaz USING(GazetteerID) WHERE gaz.DimX>0 AND gaz.DimY>0 ORDER BY gaz.ParentID,gaz.Gazetteer";


			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
      <option value='".$rr['GazetteerID']."'>".$rr['Gazetteer']."</option>";
      //<option value='".$rr['GazetteerID']."'>".$rr['GazetteerTIPOtxt']." ".$rr['Gazetteer']."</option>";
			}
	echo "
    </select>
</td>
</tr>
</tbody>
</table>
</form>
";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>