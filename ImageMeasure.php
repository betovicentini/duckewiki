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

//echopre($ppost);
//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$title = 'Definir Censos';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<applet code=\"ij.ImageJApplet.class\" archive='http://imageja.sourceforge.net//ij.jar'  width=700 height=550  security=all-permissions >
<param name=url1 value=\"img/originais/2014-06-30_ACSA_51_DSC0051.jpg\">

</applet>";
  
$which_java = array();
FazFooter($which_java,$calendar=TRUE,$footer=$menu);

?>