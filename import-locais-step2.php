<?php
//Este script importa o arquivo CSV ou TXT selecionado para uma tabela temporaria mysql
//Depois sao perguntados quais colunas indicam amostras coletadas ou plantas marcadas
//Ultima atualizacao: 25 jun 2011 - AV
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
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
//, "<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Importar locais passo 01';
$body = '';
//FazHeader($title,$body,$which_css,$which_java,$menu);

###define as colunas parentid ou municipioid, dependendo do dado
$txt = '';
$i =0;
foreach ($ppost as $kk => $vv ) {
	if ($i==0) {
		$txt .=  $kk."=".$vv;
	} else {
		$txt .=  "&".$kk."=".$vv;
	}
	$i++;
}

#se tem colunas administrativas checa as validades ate encontrar municipio
if ($opcao==1) {
		header("location: import-locais-step3.php?".$txt);
} 
$ok=0;
#se tem um municipio para todos, adiciona
if ($opcao==2) {
		$cln = $tbprefix."MunicipioID";
		$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cln." INT(10) DEFAULT 0";
		@mysql_query($qq,$conn);
		$qq = "UPDATE `".$tbname."` SET `".$cln."`= ".$municipioid;
		@mysql_query($qq,$conn);
		$ok++;
}

#se tem um parent gazetteer para todos, adiciona
if ($opcao==3) {
		$cln = $tbprefix."ParentID";
		$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cln." INT(10) DEFAULT 0";
		@mysql_query($qq,$conn);
		$qq = "UPDATE `".$tbname."` SET `".$cln."`= ".$pparentid;
		@mysql_query($qq,$conn);
		$ok++;
}

if ($ok>0) {
		header("location: import-locais-step4.php?".$txt);
} 


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
//FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
