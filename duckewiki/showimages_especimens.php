<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

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
"<style type=\"text/css\">
a img,:link img,:visited img { border: none; }
.clearfix:after{clear:both;content:\".\";display:block;font-size:0;height:0;line-height:0;visibility:hidden;}
.clearfix{display:block;zoom:1}
ul#thumblist{display: inline-block;}
ul#thumblist li {float:left; margin-bottom: 4px; list-style:none;}
ul#thumblist li a{display:block;border:1px solid #CCC;}
ul#thumblist li a.zoomThumbActive{ border:1px solid red; }
.jqzoom{
text-decoration:none;
float:left;
}
</style>"
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
</script>",
"<script type=\"text/javascript\">
function changetxtmenu(menuid,txtid){
    var menudest = document.getElementById('imgmenu');
    var txtdest = document.getElementById('oidtxt');
    menudest.innerHTML = document.getElementById(menuid).innerHTML;
    txtdest.innerHTML = document.getElementById(txtid).innerHTML;
}
</script>"
);
$title = 'Mostrar Imagens';
$body = "bgcolor='#4C4646'";
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";
	$url = $_SERVER['SERVER_NAME'];
	$url2 = $_SERVER['SCRIPT_NAME'];
	$uu = explode("/",$url2);
	$nu = count($uu)-1;
	unset($uu[$nu]);
	$url2 = implode("/",$uu);
	$urlbig = "img/originais/";
	$urllow = "img/lowres/";
	$pthumb = "img/thumbnails/";
	$path =   "img/copias_baixa_resolucao/";

	//echo $path;
	$stilo =" border:1px solid #cccccc;  -webkit-box-shadow:inset 0 0 6px #cccccc; -moz-box-shadow: inset 0 0 6px #cccccc; cursor: pointer;";
	$iconheight = "height=\"20\"";
	$tohide ='';
	$detimg = '';
	$menushow=FALSE;
	$tt2 = '';
	$txt = "";
	$menu='';
	$imgsee = "<img src=\"icons/search_plus_blue.png\" ".$iconheight." style=\"".$stilo."\" onclick = \"javascript:small_window('".$urlbig."/".$imagename."',1000,600,'Vendo imagem original');\" onmouseover=\"Tip('Para ver a imagem original');\" />&nbsp;";
	$menu = $imgsee.$menu;
	$txt .= "
<div class=\"clearfix\" id=\"content\"  style=\"width: 98%; border: thin white;\" >
    <div class=\"clearfix\" style=\"margin-left: 180px; margin-top: 10px; height:500px; width: 800px; position: absolute;\" >
        <span id='imgmenu' >".$menu."</span>&nbsp;<span style=\"color: white; font-size: 0.6em;\" >Aqui para ver a imagen original</span>
<br />
        <a href=\"".$path.$imagename."\" class=\"jqzoom\" rel='gal1'  >
            <img src=\"".$urllow.$imagename."\"    title=\"\" style=\"border: 4px solid #666;\">
        </a>
    </div>
<!--
    <div class=\"clearfix\" style=\"position: absolute; margin-top: 10px; height:500px; width:170px; overflow-y: auto;  border: 1px solid #ffffff;\">
      <ul id=\"thumblist\" class=\"clearfix\" >
      <li><a class=\"zoomThumbActive\" href='javascript:void(0);' rel=\"{gallery: 'gal1', smallimage: '".$urllow.$imagename."',largeimage: '".$path.$imagename."', title: '".$imagename."' }\"><img width=100px; src='".$urllow.$imagename."' onmouseover=\"Tip('".$imagename."');\"  onclick=\"javascript:changetxtmenu('".$vimg."menu','".$vimg."txt');\" ></a></li>
      </ul>
</div>
-->
</div>
     ";
//    &nbsp;<span style=\"color: white; font-size: 0.6em;\" >Passe o mouse na imagem para zoom!</span>
$tohide .= "<span id='".$vimg."menu' style='visibility:hidden;' >".$menu."</span><span id='".$vimg."txt' style='visibility:hidden;' >".$oidtxt."</span>";
echo $txt;
echo "<br>".$tohide;
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,
//"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=FALSE);
?>