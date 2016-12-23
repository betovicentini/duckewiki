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
$title = 'Formulários de variáveis';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
$minwidth = '400px';
$estilo = "width: ".$minwidth ."; padding: 10px;";
echo "
<div >";
echo "<a style=\"".$estilo."\" href=\"#\" class=\"menuicons_verde1\" onclick = \"javascript:small_window('formulariosnovo-exec.php?&formid=novo',600,400,'Criar formulários');\">Criar formulários</a>";
echo "<br>";
echo "
<a style=\"".$estilo."\" href=\"#\" class=\"menuicons_verde1\" onclick = \"javascript:small_window('formulariosnovo-exec.php',600,400,'Editar formulários');\">Editar formulários</a>";
echo "<br>";
echo "<a style=\"".$estilo."\" href=\"#\" class=\"menuicons_cinza\" onclick = \"javascript:small_window('formularios-duplicate.php',600,400,'Duplicar formulários');\">Duplicar formulários</a>";
echo "<br>";
echo "<a href=\"#\" style=\"".$estilo."\" class=\"menuicons_cinza\" onclick = \"javascript:small_window('formularios-merge.php',600,400,'Unir formulários');\">Unir formulários</a>";
echo "<br>";
echo "<a href=\"#\" style=\"".$estilo."\" class=\"menuicons_cinza\" onclick = \"javascript:small_window('formularios-delete.php',600,400,'Apagar formulários');\">Apagar formulários</a>";

echo "
</div>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>