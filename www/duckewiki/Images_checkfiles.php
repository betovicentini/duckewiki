<?php
set_time_limit(0);
//Start session
session_start();
//Check whether the session variable
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);
	
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$title = 'Atualiza Imagens';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$erro=0;
$ok=0;

//some definitions
$path = "img/originais/";
$pthumb = "img/thumbnails/";
$imgbres = "img/copias_baixa_resolucao/";	
$lowres = "img/lowres/";	


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
} else {
	$newfilestothumb = unserialize($_SESSION['newfilestothumb']);
}

if (count($newfilestothumb)>0) {
	$newfilestothumb = array_values($newfilestothumb);
	//agora cria os thumbs para os arquivos
	$fname = $newfilestothumb[0];
	$ff = array($fname);
	unset($newfilestothumb[0]);
	$_SESSION['newfilestothumb'] = serialize($newfilestothumb);
	$_SESSION['newimagfiles'] = serialize($ff);
	$zz = explode("/",$_SERVER['SCRIPT_NAME']);
	$serv = $_SERVER['SERVER_NAME'];
	$returnto = $serv."/".$zz[1]."/Images_checkfiles.php";
	
	echo "";
	echo "<form  name='myform' action='../cgi-local/imagick_function.php' method='get'>		
			<input type='hidden' value='".$returnto."' name='returnto'>
			<input type='hidden' value='".$zz[1]."' name='folder'>
		<input type='hidden' value='imgdone' name='returnvar'>
		<table><tr><td>Atualizando o arquivo $fname... aguarde!</td></tr></table>
		<script language=\"JavaScript\">setTimeout('document.myform.submit()',1);</script>
		</form>";
} else {
	echo "Não foram encontrados arquivos de imagens sem thumbnails<br>";
}
//	echo "Missing file sizes for<br>	Thumbnails:".$notub."	<br>Lowres:".$noloweres." <br>Copias baixa res:".$nocopiabr."<br>";
//echo "<br>Foram atualizados os nomes de ".$updated." arquivos<br>";	



$which_java = array();
FazFooter($which_java,$calendar=TRUE,$footer=$menu);

?>