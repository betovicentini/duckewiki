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
$title = 'Herbários';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!empty($filtro)) { 
	$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
	$res = mysql_query($qq,$conn);
	$rr = mysql_fetch_assoc($res);
	$especimenesids= $rr['EspecimenesIDS'];
}

$herbarr = explode(";",$herbaria);
$specids = explode(";",$especimenesids);
$novo =0;
$jatinham=0;
$erro=0;
if (count($specids)>0) {
	$herbarr = explode(";",$herbaria);

	foreach ($specids as $vv) {
		$qq = "SELECT * FROM Especimenes WHERE EspecimenID='$vv'";
		//echo $qq;
		$res = mysql_query($qq,$conn);
		$nres =mysql_numrows($res);
		if ($nres>0) {
			$row = mysql_fetch_assoc($res);
			$oldherb = trim($row['Herbaria']);
			$herbariums = explode(";",$row['Herbaria']);
		}
		$update =0;
		if (!empty($oldherb)) {
			foreach ($herbarr as $herb) {
				if (!in_array($herb,$herbariums)) {
					$herbariums[] = $herb;
					$update++;
				} 
			}
			if ($update==0) { 
					$jatinham++;
				}
			$hh = implode(";",$herbariums);
		} else {
			$hh = $herbaria;
			$update++;
		}
		
		if ($update>0) {
			$arrayofvalues = array(
					'Herbaria' => $hh);
			CreateorUpdateTableofChanges($vv,'EspecimenID','Especimenes',$conn);
			$added = UpdateTable($vv,$arrayofvalues,'EspecimenID','Especimenes',$conn);
			if ($added) {
				$novo++;
			} else {
				$erro++;
			}
		} 
	}
}

if ($novo>0) {
	echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='success'>
    <tr><td class='tdsmallbold' align='center'>$novo registros. ".GetLangVar('sucesso1')."</td></tr>
  </table>
<br />";
}

if ($erro>0) {
	echo "<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>$erro registros não puderam ser atualizados</td></tr>
</table>
<br />";
}

if ($jatinham>0) {
	echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='erro'>
    <tr><td class='tdsmallbold' align='center'>$jatinham já tinham esse(s) herbário(s) registrado(s)</td></tr>
  </table>
<br />";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>