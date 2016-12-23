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
	$url = $_SERVER['HTTP_REFERER'];
	$uu = explode("/",$url);
	$nu = count($uu)-1;
	unset($uu[$nu]);
	$url = implode("/",$uu);
	$urlbig = $url."/img/originais/";
	$urllow = $url."/img/lowres/";
	$pthumb = $url."/img/thumbnails/";
	$path =   $url."/img/copias_baixa_resolucao/";
	$qusq = "SELECT FileName,addcolldescr(Autores) as Fotografos,DateOriginal FROM Imagens WHERE ImageID='".$imgid."'";
	$rusq = mysql_query($qusq,$conn);
	$rusqw = mysql_fetch_assoc($rusq);

//echo "<a href=\"".$urlbig.$rusqw['FileName']."\" class=\"jqzoom\" ><img src=\"".$urllow.$rusqw['FileName']."\"    title=\"\" style=\"border: 4px solid #666;\"></a>";
        
//echo "<img src=\"".$urlbig.$rusqw['FileName']."\">";
echo "
<applet code=\"ij.ImageJApplet.class\" archive='http://imageja.sourceforge.net//ij.jar'  width=800 height=600  security=all-permissions >
<param name=url1 value=\"".$urlbig.$rusqw['FileName']."\">
</applet>";
  
$which_java = array();
FazFooter($which_java,$calendar=TRUE,$footer=$menu);

?>