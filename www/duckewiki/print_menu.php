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

$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/colorbuttons.css' />"
);
$which_java = array();
$title = 'Imprimir';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
$minwidth = '300px';
echo "
<div style='padding: 10px; width: 100%;'>
<a href=\"#\" style=\"width: ".$minwidth .";\" class=\"menuicons_azulescuro\" onclick = \"javascript:small_window('label-form.php?ispopup=1',800,600,'Imprimir Etiquetas');\">Imprimir Etiquetas</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_roxo\" onclick = \"javascript:small_window('formulario-pdf-form.php?ispopup=1',800,600,'Imprimir Formulário de Variáveis');\">Imprimir Formulário de Variáveis</a>";
echo "
</div>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>