<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//PEGA AS VARIAVEIS DE CONFIGURACAO (functions/databaseSettings....)
$_SESSION['dbname'] = $dbname;
$_SESSION['lang'] = $lang;

//CHECA SE O USUARIO ESTA LOGADO
if (!isset($_SESSION['userid'])) {
	$_SESSION['accesslevel'] = 'public';
	//$blockacess =0;
	$menu = TRUE;
} else {
	$menu = FALSE;
}

//SE ESTIVER LOGADO E NAO O SISTEMA NAO ESTIVER EM MANUTENCAO
if (($_SESSION['userid']>0 || count($listsarepublic)>0) && ($blockacess+0)==0 && !empty($dbname)) {
	header("location: check_listall.php");
	$location = 'check_listall.php';
	//echopre($_SESSION);
} else {
	$location = 'login-form.php';
}

//DEFINE CONTEUDO HEADER HTML
$title = $metatitle;
//ESTILOS
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
//"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
//JAVASCRIPTS
$which_java = array(
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
//FAZ CABECALHO
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";
omenudeicons($quais, $vertical=FALSE, $position='right' , $iconwidth='30', $iconheight='30' );

//SE ESTIVE BLOQUEADO MOSTRA MENSAGEM
if ($blockacess>0) {
	//MOSTRA MENU DE ICONES CASO ESTEJA EM MANUTENCAO E O USUARIO E ADMINISTRADOR
//	if ($_SESSION['accesslevel']=='admin') {
	//	omenudeicons($quais, $vertical=FALSE, $position='left' , $iconwidth='30', $iconheight='30' );
	//}
	echo "
<br />
<br />
<br />
<p align='center' style='margin-left: 50px; color: red; font-size: 1.5em; width: 50%;' >EM MANUTENÇÃO DE ATUALIZAÇÃO! POR FAVOR, VOLTE MAIS TARDE!</p>
<br>
";
} else {
	echo "
<div style='padding: 100px; font-family:\"Verdana\", Arial, sans-serif;  font-size: 1.2em; font-color: #800000;'>
".$introtext."
<br />
<br />
<div style='cursor: pointer; margin-left: 35%; width: 80%' onclick = \"javascript: window.open('".$location."','_self' );\" onmouseover = \"javascript: Tip('Entrar na base de plantas') ;\" >
<img src='icons/database.png' height='60px' />
<span style='font-family:\"Verdana\", Arial, sans-serif;  font-size: 1em; font-color: #800000;'>
ENTRAR NA BASE
</span>
</div>
</div>";
}
$stilo =" cursor: pointer;";
//echo "
//<div style='position: absolute; top: 150px; right: 30px;'>
// <img style=\"".$stilo."\"  height='100px' src='icons/inpa_principal.jpg' onmouseover = \"javascript: Tip('Instituto Nacional de Pesquisas da Amazônia') ;\"  onclick = \"javascript: window.open('http://www.inpa.gov.br','_blank' );\" />
//  <br />
//    <br />
//   <img style=\"".$stilo."\"  height='50px' src='icons/ctfs_logo.png' onmouseover = \"javascript: Tip('CTFS - Center for Tropical Forest Science') ;\"  onclick = \"javascript: window.open('http://www.forestgeo.si.edu','_blank' );\" />
//</div>";
//elseif (empty($_SESSION['userid'])) {
	//omenudeicons($quais, $vertical=FALSE, $position='right' , $iconwidth='30', $iconheight='30' );
//}


///JAVASCRIPTS DE RODAPE
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
//"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
//FAZ RODAPE E FECHA HTML
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
