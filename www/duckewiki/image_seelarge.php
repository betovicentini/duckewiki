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
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
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
"<link rel=\"stylesheet\" href=\"javascript/jqzoom_ev-2.3/css/jquery.jqzoom.css\" type=\"text/css\">",
);
$which_java = array(
"<script src=\"javascript/jqzoom_ev-2.3/js/jquery-1.6.js\" type=\"text/javascript\"></script>",
"<script src=\"javascript/jqzoom_ev-2.3/js/jquery.jqzoom-core.js\" type=\"text/javascript\"></script>",
"<script type=\"text/javascript\">
$(document).ready(function() {
$('.jqzoom').jqzoom({
        zoomType: 'standard',
        lens:true,
        preloadImages: false,
        alwaysOn:false,
        zoomWidth: 300,
        zoomHeight: 300,
        xOffset: 0,
        yOffset: 50,
        position:'right',
        preloadText: 'Carregando o zoom',
        title: false
        });
});
</script>"
);

$title = 'Mostrar Imagens';
$body = "";
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
//echo $qusq."<br >";
$rusq = mysql_query($qusq,$conn);
$rusqw = mysql_fetch_assoc($rusq);

//echo "<a href=\"".$urlbig.$rusqw['FileName']."\" class=\"jqzoom\" ><img src=\"".$urllow.$rusqw['FileName']."\"    title=\"\" style=\"border: 4px solid #666;\"></a>";
$pathcpbres = $urlbig.$rusqw['FileName'];
$imgn = rand(1,4);
$imgn = "semimagem".$imgn.".jpg";
if (!file_exists($pathcpbres)) {
	$opath = $urlbig.$imgn;
} else {
	$opath = $urlbig.$rusqw['FileName'];
}
echo "<img src=\"".$opath."\">";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=FALSE);
?>