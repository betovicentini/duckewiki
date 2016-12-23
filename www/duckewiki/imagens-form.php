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
$which_java = array(
);
$title = 'Imagens';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
$minwidth = '350px';
echo "
<div style='padding: 10px; width: 100%;'>
<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_amarelo\" onclick = \"javascript:small_window('imagens-import-batch.php?ispopup=1',800,200,'Importar Imagens');\">Importar Imagens (com ou sem relação com registros da base)</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_azul\" onclick = \"javascript:small_window('imagens-link.php?ispopup=1',800,400,'Relacionar Imagens');\">Relacionar Imagens importadas por similaridade de data</a>
</div>";
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>