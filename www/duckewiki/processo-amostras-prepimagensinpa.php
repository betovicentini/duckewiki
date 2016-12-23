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
if(!isset($uuid) ||  (trim($uuid)=='')) {
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
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Prepara imagens de processos';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<input  type='button'  style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Gerar arquivo para os processos'  onmouseover=\"Tip('Gerar arquivo para os processos');\" 
onclick = \"javascript:small_window('processo-exportinpa-preplabel.php?processoid=".$processoid."',800,400,'Gerar arquivo para os processos');\" />
";





$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>