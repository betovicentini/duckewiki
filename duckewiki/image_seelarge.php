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
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
//"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
//"<link rel='stylesheet' href='javascript/magiczoomplus/magiczoomplus/magiczoomplus.css' type='text/css' media='screen' />"
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
//$body = "bgcolor='#4C4646'";
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
        
echo "<img src=\"".$urlbig.$rusqw['FileName']."\">";
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,
//"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=FALSE);
?>