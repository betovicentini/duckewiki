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
$title = 'Importar dados Hub';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($_SESSION['importacaostep'])) {
	//apaga variaveis que ja foram extraidas e nao precisam ser passadas adiante
	unset($ppost['refdefined']);
	unset($ppost['fieldsign']);
	unset($ppost['imported']);
		
	//guarda definicao das colunas do arquivo importado
	$_SESSION['fieldsign'] = serialize($fieldsign);
	$_SESSION['firstdefinitions'] = serialize($ppost);
	//numero de passos da importacao vai para e volta desses arquivos
	$nsteps=10;
	$steps = array();
	$sttt = array(3,4,5,6,7,8,9,10,11,12,13,14);
	foreach ($sttt as $i) {
		$st = array('import-data-step'.$i.'.php');
		$steps = array_merge((array)$steps,(array)$st);
	}
	$_SESSION['importacaostep'] = serialize($steps);
} else {
	$steps = unserialize($_SESSION['importacaostep']);
}
$st = trim($steps[0]);
if (!empty($st)) {
echo "
  <form name='myform' action='".$st."' method='post'>
  <input type='hidden' name='ispopup' value=1>
  ";
	$zz = unserialize($_SESSION['firstdefinitions']);
	foreach ($zz as $kk => $vv) {
		if (!empty($vv)) {
			echo "
    <input type='hidden' name='".$kk."' value='".$vv."' />"; 
        }
	}
echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='success'>
    <tr><td class='tdsmallbold' align='center'>$st</td></tr>";
	if ($st!='import-data-step13.php') {	
		echo "
    <script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>";
	} else {
		echo "
    <tr><td class='tdsmallbold' align='center'><input style='cursor: pointer' type='submit' value='".GetLangVar('nameconcluir')."' class='bsubmit' /></td></tr>";
	}    
echo "
  </table>
</form>";	
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
