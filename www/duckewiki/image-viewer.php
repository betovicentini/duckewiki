<?php
//Start session
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
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
$body='';
$title = '';

$fn = "1";
$files = explode(";",$fn);
	$big_path = "img/originais/";
	$thumb_path = "img/thumbnails/";

//ImgHeader($title,$body);

//echo "<table align='center' cellspacing='5' cellpading='3'>";
//echo "<thead><tr><td colspan=2>".GetLangVar('nameimagens')."</td></tr></thead><tbody>";
foreach ($files as $kkk => $vv) {
	$vv= trim($vv);
	if (!empty($vv)) {
			$qq = "SELECT * FROM Imagens WHERE ImageID='$vv'";
			$rt = mysql_query($qq,$conn);
			$rtw = mysql_fetch_assoc($rt);
			$filename = trim($rtw['FileName']);
			
			$autor = $rtw['Autores'];
			$autorarr = explode(";",$autor);
			if (count($autorarr)>0) {
			$j=1;
			foreach ($autorarr as $aut) {
				$qq = "SELECT * FROM Pessoas WHERE PessoaID='".$aut."'";
				$res = mysql_query($qq,$conn);
				$rwr = mysql_fetch_assoc($res);
				if ($j==1) {
					$autotxt = 	$rwr['Abreviacao'];
				} else {
					$autotxt = $autotxt."; ".$rwr['Abreviacao'];
				}
				$j++;
			}
			} 
			$fotodata = $rtw['DateOriginal'];
	
	//memory needed
	//$imageInfo = GetImageSize($path.$filename);
	//$memoryNeeded = Round(($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + Pow(2, 16)) * 1.65);
	//echo $memoryNeeded;
	//checar se thumbnail existe, senao cria um
	//$pthumb = "img/thumbnails/";
		//echo $path.$fn;
		//if (!file_exists($pthumb.$filename)) {
		//	createthumb($path.$filename,$pthumb.$filename,80,80);
		//	flush();
		//}
	
	//checar se imagen para visualizacao existe, senao cria uma
//		$imgbres = "img/copias_baixa_resolucao/";	
//		if (!file_exists($imgbres.$filename)) {
//			$zz = getimagesize($path.$filename);
//			$width=$zz[0];
//			$height = $zz[1];
//			if ($width>1200 || $height>1200) {
//				createthumb($path.$filename,$imgbres.$filename,1200,1200);
//			} else {
//				createthumb($path.$filename,$imgbres.$filename,$width,$height);
//			}
//			flush();
//		}
	
		$fn = explode("_",$filename);
		unset($fn[0]);
		unset($fn[1]);
		$fn = implode("_",$fn);

		$fntxt = $fn."  <br> [";
		if (!empty($autotxt)) { $fntxt = $fntxt." ".GetLangVar('namefotografo').": ".$autotxt." - ".$fotodata."]";} else {
			$fntxt = $fntxt.$fotodata."]";
		}

$href = curPageURL()."/botam/img/thumbnails/2010-10-28_2_Carvalho_1878_A208.jpg";
$zz = getimagesize('img/thumbnails/2010-10-28_2_Carvalho_1878_A208.jpg');
$width=$zz[0]*10;
$height = $zz[1]*10;
echo "
<html>
<head>
<title>ImageJA Applet</title>
</head>
<body>

<h1>".$fn."</h1>

<applet codebase='.' code='ij.ImageJApplet.class' archive='javascript/ij.jar' width=".$width." height=".$height." security=all-permissions>
<param name=url1 value=".$href." >
</applet>

</body>
</html>
";




	}
}

//PopupTrailers();

?>