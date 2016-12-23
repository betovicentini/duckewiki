<?php
//IMPORTA UMA TABELA QUALQUER AO MYSQL
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
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />");
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Apagar Variável';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$qq = "SELECT * FROM Traits WHERE TraitID='".$traitid."'";
$rs = mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($rs);
$ttipo = $rw['TraitTipo'];
$ok=0;
if ($ttipo!='Classe') {
	$qq = "SELECT * FROM Traits_variation WHERE TraitID='".$traitid."'";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	if ($nres>0) {
		echo "
<p class='erro' >".GetLangVar('traitdeleteerro1')."</p>";
	} else {
		$qq = "DELETE FROM Traits WHERE TraitID='".$traitid."'";
		$okr = mysql_query($qq,$conn);
		if ($ork) {$ok++;}
		$qq = "DELETE FROM Traits WHERE ParentID='".$traitid."'";
		$okr2 = mysql_query($qq,$conn);
		if ($okr2) {$ok++;}
	}
} else {
	$qq = "SELECT * FROM Traits WHERE ParentID='".$traitid."'";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	if ($nres>0) {
			echo "
<p class='erro' >".GetLangVar('traitdeleteerro2')."</p>";
	} else {
		$qq = "DELETE FROM Traits WHERE TraitID='".$traitid."'";
		$ork = mysql_query($qq,$conn);
		if ($ork) {$ok++;}
	}
}

if ($ok>0) { 
	echo "
<p class='success' >".GetLangVar('traitdeleteok')."</p>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
