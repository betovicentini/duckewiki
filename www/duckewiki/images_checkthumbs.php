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
$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Checar/Gerar Imagens por Arquivos de Baixa Resolução';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$erro=0;
$ok=0;

//some definitions
$path = "img/originais/";
$pthumb = "img/thumbnails/";
$imgbres = "img/copias_baixa_resolucao/";
$lowres = "img/lowres/";

if (isset($arquivos)) {
	$_SESSION['newfilestothumb'] = $_SESSION[$arquivos];
	$imgdone= 'returnvar';
}

if (!isset($imgdone)) {
	unset($_SESSION['newfilestothumb']);
	//check if files have all needed sizes exists
	$files1 = scandir($path);
	$notub=0;
	$noloweres=0;
	$nocopiabr=0;
	$filestothumb = array();
	foreach ($files1 as $ff) {  //for each original image
			if ($ff!="." && $ff!="..") {
				if (!file_exists($pthumb.$ff)) {
				  $filestothumb[] = $ff;
				  $notub++;
				}
				if (!file_exists($lowres.$ff)) {
				  $filestothumb[] = $ff;
				  $noloweres++;
			
				}
				if (!file_exists($imgbres.$ff)) {
				  $filestothumb[] = $ff;
				  $nocopiabr++;
				}
			}
	}
	$filestothumb = array_unique($filestothumb);
	//echopre($filestothumb);
	//it seems that spaces in file names preclude imagemagick transformations
	//thus, remove spaces and uptade Image table it that is the case
	$updated=0;
	$newfilestothumb= array();
	foreach ($filestothumb as $ff) { 
		$rn=0;
		$fn = explode(" ",$ff);
		if (count($fn)>1) {
			$nfn = implode("_",$fn);
			$newfilestothumb[] = $nfn;
			//rename files in folders;
			$inputfile = $path.$ff;
			$renamed = rename($inputfile,$path.$nfn);
			if ($renamed) {
				$rn++;
			}
			if (file_exists($pthumb.$ff)) {
				$inputfile = $pthumb.$ff;
				rename($inputfile,$pthumb.$nfn);
			}
			if (file_exists($lowres.$ff)) {
				$inputfile = $lowres.$ff;
				rename($inputfile,$lowres.$nfn);
			}
			if (file_exists($imgbres.$ff)) {
				$inputfile = $imgbres.$ff;
				rename($inputfile,$imgbres.$nfn);
			}
			if ($rn>0) {
				$qq = "SELECT * FROM `Imagens` WHERE `FileName` LIKE '".$ff."'";
				$rq = mysql_query($qq,$conn);
				$nrq = mysql_numrows($rq);
				if ($nrq==1) {
					$qq = "UPDATE `Imagens` SET `FileName`='".$nfn."' WHERE `FileName` LIKE '".$ff."'";
					$uq = mysql_query($qq,$conn);
					if ($up) {
						$updated++;
					}
				}
			}
		} else {
			$newfilestothumb[] = $ff;
		}
	}
} 
else {
	$newfilestothumb = unserialize($_SESSION['newfilestothumb']);
}

if (count($newfilestothumb)>0) {
	$newfilestothumb = array_values($newfilestothumb);
	$fname = $newfilestothumb[0];
	$ff = array($fname);
	//echopre($ff);
	unset($newfilestothumb[0]);
	$_SESSION['newfilestothumb'] = serialize($newfilestothumb);
	$_SESSION['newimagfiles'] = serialize($ff);
	$zz = explode("/",$_SERVER['SCRIPT_NAME']);
	$serv = $_SERVER['SERVER_NAME'];
	//echopre($_SERVER);
	//$returnto = $serv."/".$zz[1]."/images_checkthumbs.php";
	$returnto = $serv.$_SERVER['PHP_SELF'];
	//echo $returnto."<br />";
	//echo $zz[1]."<br />";
	//echo "imagick_function.php?returnto=$returnto&folder=$zz[1]&imgdone=returnvar&filename=$fname";
	echo "
<table><tr><td>Atualizando o arquivo $fname... aguarde!</td></tr></table>
<form  name='myform' action='../cgi-local/imagick_function.php' method='get'>
  <input type='hidden' value='".$returnto."' name='returnto' />
  <input type='hidden' value='".$zz[1]."' name='folder' />
  <input type='hidden' value='".$fname."' name='filename' />
  <input type='hidden' value='imgdone' name='returnvar' />
  <script language=\"JavaScript\">setTimeout('document.myform.submit()',1);</script>
</form>";
  //  



} 
else {
	echo "Não foram encontrados arquivos de imagens sem thumbnails<br />";
}
//	echo "Missing file sizes for<br />	Thumbnails:".$notub."	<br />Lowres:".$noloweres." <br />Copias baixa res:".$nocopiabr."<br />";
//echo "<br />Foram atualizados os nomes de ".$updated." arquivos<br />";	
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>