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
$title = '';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$body='';
$title = 'Salva busca de plantas';
FazHeader($title,$body,$which_css,$which_java,$menu);
$specs = explode(";",$tagnumbers);
$ff = array();
$nf = array();
foreach ($specs as $vv) {
	$qq = "SELECT pl.PlantaID,sp.EspecimenID FROM Plantas as pl LEFT JOIN Especimenes AS sp ON sp.PlantaID=pl.PlantaID WHERE pl.FiltrosIDS LIKE '%filtroid_".trim($filtro)."%' AND pl.PlantaTag='".trim($vv)."' LIMIT 0,1";
	$res = mysql_query($qq,$conn);
	//echo $qq;
	//echo "<br />";
	$nr = mysql_numrows($res);
	if ($nr==1) {
		$row = mysql_fetch_assoc($res);
		$ff[$vv] = array($row['PlantaID'],$row['EspecimenID']); 
		//$ff[$vv] = $row['PlantaID']; 
	} else {
		$nf[] = $vv;
	}
	echo "&nbsp;";
	flush();
}

if (count($nf)>0) {
	echo "
<br />
<table class='erro' align='center' width='80%'>
  <tr><td>".count($nf)." registros não foram encontrados </td></tr>
  <tr><td><textarea>".implode(";",$nf)."</textarea></td></tr>
</table>";
}
if (count($ff)>0) {
	$tbname = "tempfiltro_".substr(session_id(),0,10);
	$qq = "DROP TABLE ".$tbname;
	mysql_query($qq,$conn);
	$qq = "CREATE TABLE IF NOT EXISTS ".$tbname." (
				tempid INT(10) unsigned NOT NULL auto_increment,
				EspecimenID INT(10),
				PlantaID INT(10),
				Ntimes INT(10),
				NCriteria INT(10),
				PRIMARY KEY (tempid)) CHARACTER SET utf8";
	mysql_query($qq,$conn);

	foreach ($ff as $tgn) {
		$tg1 = $tgn[0];
		$tg2 = $tgn[1];
		$qq = "INSERT INTO ".$tbname." (EspecimenID,PlantaID,Ntimes,NCriteria) (SELECT ".$tg2.",".$tg1.",1,1)";
    	@mysql_query($qq,$conn);
	}

	$myurl ="filtros-exec.php?tbname=".$tbname;
	echo "
<br />
<table class='success' align='center'>
  <tr><td>".count($ff)." registros foram encontrados </td></tr>
  <form >
  <tr><td><input type=button value='".GetLangVar('namesalvar')." ".strtolower(GetLangVar('namefiltro'))."' class='bsubmit' onclick = \"javascript:small_window('$myurl',350,280,'Filtro');\" /></td></tr>
  </form>
</table>";

}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>