<?php
session_start();
$folder = $_GET['folder'];

$path = "../".$folder."/img/originais/";
$pthumb = "../".$folder."/img/thumbnails/";
$imgbres = "../".$folder."/img/copias_baixa_resolucao/";
$lowres = "../".$folder."/img/lowres/";


$vserialized = $_SESSION['newimagfiles'];
$vv = unserialize($vserialized);
$returnto = $_GET['returnto'];
$returnvar = $_GET['returnvar'];

//print_r($_GET);
//print_r($_SESSION);
//print_r($vv);
$ok=0;
foreach ($vv as $fname) {
	if (!file_exists($pthumb.$fname)) {
	  system("convert ".$path.$fname." -resize x80  ".$pthumb.$fname);
	  $ok++;
	}
	if (!file_exists($lowres.$fname)) {
	  system("convert ".$path.$fname." -resize x400\>  ".$lowres.$fname);
	  $ok++;

	}
	if (!file_exists($imgbres.$fname)) {
	  system("convert ".$path.$fname." -resize x2000\>  ".$imgbres.$fname);
	  $ok++;

	}
}
$nfs = count($vv);
if ($ok==$nfs*3) {
	header("location: http://".$returnto."?".$returnvar."=2");
} else {
	header("location: http://".$returnto."?".$returnvar."=3");
}

?>