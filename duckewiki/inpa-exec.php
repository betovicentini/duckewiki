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
$title = 'Cadastra Herbário';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!empty($filtro)) { 
	$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
	$res = mysql_query($qq,$conn);
	$rr = mysql_fetch_assoc($res);
	$especimenesids= $rr['EspecimenesIDS'];
}
$specids = explode(";",$especimenesids);
$jatinham=0;
if (count($specids)>0) {
	foreach ($specids as $vv) {
		$qq = "SELECT * FROM Especimenes WHERE EspecimenID='$vv'";
		//echo $qq;
		$res = mysql_query($qq,$conn);
		$nres =mysql_numrows($res);
		if ($nres>0) {
			$row = mysql_fetch_assoc($res);
			$inpaid = $row['INPA_ID'];
		}
		if ($inpaid==0 || empty($inpaid)) {
			$arrayofvalues = array(
					'INPA_ID' => $numinicial);
			CreateorUpdateTableofChanges($vv,'EspecimenID','Especimenes',$conn);
			$updated = UpdateTable($vv,$arrayofvalues,'EspecimenID','Especimenes',$conn);
			if ($updated) {
				$novo++;
				$numinicial++;
			} else {
				$erro++;
			}
		} elseif ($inpaid>0) {
			$jatinham++;
		}
	}
}

if ($novo>0) {
	$final = $numinicial-1;
	echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>$novo registros. ".GetLangVar('sucesso1')."</td></tr>
  <tr><td class='tdsmallbold' align='center'><b>$final</b> foi o ultimo numero usado</td></tr>
</table>
<br />";
}

if ($jatinham>0) {
	echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>$jatinham registros ja tinham numero INPA</td></tr>
</table>
<br />";
}


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
