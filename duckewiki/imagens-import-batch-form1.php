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

/*DEFINE PASTA TEMPORARIA ONDE SAO ARMAZENADOS OS ARQUIVOS DURANTE A IMPORTACAO (HA REFERENCIA A ESTA PASTA EM imagesupload-doit.php)*/
$tbn = "uploadDir_". $_SESSION['userid'];
$dir = "uploads/batch_images/".$tbn;

/*CHECA SE AS INFORMAÇÕES PARA RELACIONAMENTO FORAM INDICADAS CORRETAMENTE*/
$erro=0;
$txte = "";
if ((!empty($fnpattern) && empty($fnpattern_sep)) || (empty($fnpattern) && !empty($fnpattern_sep)))  {
$txte .= "
<br />
<table cellpadding='2' cellspacing='0' class='erro' width='80%' align='center'>
<tr><td class='tdformnotes'><b>".GetLangVar('erro1')."</b></td></tr>";
	 if (empty($fnpattern)) {
	$txte .= "
  <tr><td class='tdformnotes'>PADRÃO no nome dos arquivos de imagens que identifica o coletor ou a planta</td><tr>";
  }
	if (empty($fnpattern_sep)) {
	$txte .= "
  <tr><td class='tdformnotes'>SIMBOLO SEPARADOR que permite extrair coletor e numero da coleta (ou planta tag ou wikiplantaid) do nome dos arquivos</td><tr>";
  }
	$txte .= "
</table>";
	$erro++;
}
if ((!empty($fnpattern_pl) && empty($fnpattern_seppl)) || (empty($fnpattern_pl) && !empty($fnpattern_seppl)) || ($fnpattern_pl==1 && empty($filtro)))  {
$txte .= "
<br />
<table cellpadding='2' cellspacing='0' class='erro' width='80%' align='center'>
<tr><td class='tdformnotes'><b>".GetLangVar('erro1')."</b></td></tr>";
	 if (empty($fnpattern_pl)) {
	$txte .= "
  <tr><td class='tdformnotes'>PADRÃO no nome dos arquivos de imagens que identifica a planta</td><tr>";
  }
	if (empty($fnpattern_seppl)) {
	$txte .= "
  <tr><td class='tdformnotes'>SIMBOLO SEPARADOR que permite extrair coletor e numero da coleta (ou planta tag ou wikiplantaid) do nome dos arquivos</td><tr>";
  }
  if (empty($filtro)) {
	$txte .= "
  <tr><td class='tdformnotes'>PRECISA INDICAR UM FILTRO pela opção selecionada</td><tr>";
  }
	$txte .= "
</table>";
	$erro++;
}

if (empty($traitid) && empty($linkposterior)) {
	$erro++;
	$txte .= "
<br />
<table cellpadding='2' cellspacing='0' class='erro' width='80%' align='center'>
  <tr><td class='tdformnotes'><b>".GetLangVar('erro1')."</b></td></tr>
  <tr><td class='tdformnotes'>VARIAVEL para armazenar as imagens ou indicação que a importação é SEM VINCULO</td><tr>
</table>";
}

//PEGA O NOME DOS ARQUIVOS DAS IMAGENS NO DIRETORIO DO USUARIO
$flt = scandir($dir);
//NUMERO DE ARQUIVOS (MENOS OS DOIS DE PASTA)
$filecount = count($flt)-2;
//SE NAO TEM IMAGENS ALI
if ($filecount<=0) {
	$erro++;
	$txte .= "
<br />
<table cellpadding='2' cellspacing='0' class='erro' width='80%' align='center'>
  <tr><td class='tdformnotes'><b>".GetLangVar('erro1')."</b></td></tr>
  <tr><td class='tdformnotes'>NAO ENCONTREI IMAGENS NA SUA PASTA DE IMPORTACAO</td><tr>
</table>";
}
//CASO TENHA INFORMADO TUDO
if ($erro==0) {
	//echopre($_SESSION);
	//echopre($ppost);
	$_SESSION['imgpost']  = $ppost;
	//echo "Chegou aqui corretamente";
	//echopre($_SESSION);
	header("location: imagens-import-batch-exec1.php");
} else {
	$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' >");
	$which_java = array();
	$title ='Importar várias imagens';
	$body='';
	FazHeader($title,$body,$which_css,$which_java,$menu);
		echo $txte;
	$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
	FazFooter($which_java,$calendar=FALSE,$footer=$menu);
}


?>