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
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' href='css/colorbuttons.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Método do Avistamento';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
$minwidth = '300px';
echo "
<div style='padding: 10px; width: 100%;'>
<a href=\"#\" style=\"width: ".$minwidth .";\" class=\"menuicons_azulescuro\" onclick = \"javascript:small_window('expedicao-form.php?ispopup=1',600,300,'Expedição');\">01 Expedição</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_amarelo\" onclick = \"javascript:small_window('ImportGPStracks.php?ispopup=1',800,400,'Importar dados de arquivo de GPS');\">02 Importar dados de arquivo de GPS</a>";

echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_roxo\" onclick = \"javascript:small_window('avistamento_trilhas.php?ispopup=1',600,300,'Trilhas de Avistamento');\">03 Trilhas de avistamento</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_verde\" onclick = \"javascript:small_window('avistamento-dados.php?ispopup=1',600,300,'Dados de Avistamento');\">04 Dados de avistamento</a>";
echo "
</div>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>