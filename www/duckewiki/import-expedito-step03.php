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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Importar Expedito 03';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$newvals = array();
$newvalskk = array();
foreach ($fieldsign as $kk => $vv) {
	if (!empty($vv)) {
		$newvals[] = $vv;
		$newvalskk[] = $kk;
	}
}
$newv = array_combine($newvals,$newvalskk);
$_SESSION['fieldsign'] = serialize($newv);

$erro=0;
if (!in_array("PTID",$newvals)) {
echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='erro'>
    <tr><td class='tdsmallbold' align='center'>Precisa informar um identificador para cada levantamento (1 hora de amostragem)</td></tr>
  </table>
<br />";
$erro++;
}
if (!in_array("TESTEMUNHO_COLETOR",$newvals) && !in_array("TESTEMUNHO_COLETOR",$newvals)
&& !in_array("FAMILIA",$newvals) && !in_array("GENERO",$newvals) && !in_array("ESPECIE",$newvals)) {
echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='erro'>
    <tr><td class='tdsmallbold' align='center'>Precisa Informar ou um material testemunho ou uma identificação para o registro</td></tr>
  </table>
<br />";
$erro++;
}
if (!in_array("OBSERVADOR",$newvals)) {
echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='erro'>
    <tr><td class='tdsmallbold' align='center'>Precisa informar o nome de cada observador</td></tr>
  </table>
<br />";
$erro++;
}
if (!in_array("INTERVALO",$newvals)) {
echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='erro'>
    <tr><td class='tdsmallbold' align='center'>Precisa informar o intervalo em que foi feita a observação</td></tr>
  </table>
<br />";
$erro++;
}
if ($erro==0) {
echo "
<form name='myform' action='import-expedito-step04.php' method='post'>
";
	foreach ($ppost as $kk => $vv) {
	echo "
  	<input type='hidden' name='".$kk."' value='".$vv."'>"; 
	}
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
  </form>";
} else {
echo "
<br />
<form name='myform' action='import-expedito-step02.php' method='post'>
";
	foreach ($ppost as $kk => $vv) {
	echo "
  	<input type='hidden' name='".$kk."' value='".$vv."'>"; 
	}
echo "
  <table cellpadding=\"1\" width='50%' align='center'>
    <tr><td class='tdsmallbold' align='center'><input type='submit' value='Voltar' class='bsubmit' /></td></tr>
  </table>
  </form>";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>