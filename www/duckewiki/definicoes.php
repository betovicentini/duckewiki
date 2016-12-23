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
$ispopup=1;
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/colorbuttons.css' />"
);
$which_java = array();
$title = 'Definições e Métodos';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
$minwidth = '300px';
echo "
<div style='padding: 10px; width: 100%;'>
<a href=\"#\" style=\"width: ".$minwidth .";\" class=\"menuicons_cinza\" onclick = \"javascript:small_window('censo-edit-form.php?ispopup=1',900,400,'Censos');\">Censos - definir/atualizar</a>";
echo "<br>
<a href=\"#\" style=\"width: ".$minwidth .";\" class=\"menuicons_azulescuro\" onclick = \"javascript:small_window('projeto-form.php?ispopup=1',800,400,'Projetos');\">Projetos - editar/criar</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_amarelo\" onclick = \"javascript:small_window('equipamentos-form.php?ispopup=1',800,400,'Importar Imagens');\">Equipamentos - editar/cadastrar</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_verde\" onclick = \"javascript:small_window('avistamento_menu.php?ispopup=1',400,300,'Avistamento');\">Método do Avistamento</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_roxo\" onclick = \"javascript:small_window('expedito_menu.php?ispopup=1',400,300,'Expedito');\">Método Expedito</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_branco\" onclick = \"javascript:small_window('monografia-form.php?ispopup=1',1000,700,'Monografia');\">Monografias Taxonômicas</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_azul\" onclick = \"javascript:small_window('grupospp-form.php?ispopup=1',800,600,'Grupos de Espécies');\">Editar/Definir Grupos de Espécies</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_cinza\" onclick = \"javascript:small_window('especialista-gridsave.php?ispopup=1',800,600,'Especialistas Botânicos');\">Especialistas</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_amarelo\" onclick = \"javascript:small_window('herbaria.php?ispopup=1',800,600,'Herbaria');\">Herbarios</a>";

//echo "<br>";
//echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_verde\" ></a>";
//echo "<br>";
//echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_amarelo\"></a>";
//echo "<br>";
//echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_branco\" ></a>";
echo "</div>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>