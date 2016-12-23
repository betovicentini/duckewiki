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
$title = 'Menu de exportação';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
$minwidth = '300px';
echo "
<div style='padding: 10px; width: 100%;'>
<a href=\"#\" style=\"width: ".$minwidth .";\" class=\"menuicons_azulescuro\" onclick = \"javascript:small_window('export-especimendata-form0.php?ispopup=1',900,600,'Exportar Dados de Especímenes');\">Exportar dados de Especímenes</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_amarelo\" onclick = \"javascript:small_window('export-plantadata-form0.php?ispopup=1',900,600,'Exportar dados de Plantas Marcadas');\">Exportar Dados de Plantas Marcadas</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_azul\" onclick = \"javascript:small_window('export-plantadataquick-form0.php',900,600,'Exportar dados Censos');\">Exportar Dados de Censos</a>";
echo "<br>";

//echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_amarelo\" onclick = \"javascript:small_window('export-taxadata-form0.php?ispopup=1',900,600,'Exportar dados ligados à TAXA');\">Exportar dados ligados à TAXA</a>";
echo "<br>";



echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_verde\" onclick = \"javascript:small_window('exportAsKML.php?ispopup=1',800,500,'Exportar para arquivo KML (GoogleEarth)');\">Exportar para arquivo KML (GoogleEarth)</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_roxo\" onclick = \"javascript:small_window('checklist_sisbio_form.php?ispopup=1',800,500,'Exportar Relatório SISBIO');\">Exportar para SISBIO</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_branco\" onclick = \"javascript:small_window('export-nir-spreadsheet.php?ispopup=1',800,500,'Exportar Planilha Para Antaris');\">Preparar planilha para NIR</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_cinza\" onclick = \"javascript:small_window('export-nir-data.php?ispopup=1',800,500,'Exportar Dados NIR');\">Exportar dados NIR</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_azul\" onclick = \"javascript:small_window('exportGPX.php?ispopup=1',800,500,'Exportar para GPX');\">Exportar para GPX</a><br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_azul\" onclick = \"javascript:small_window('odkcollect_inicio.php?ispopup=1',800,500,'Exportar ODK');\">Formulário ODK Collect</a>";
echo "<br>
</div>";
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
