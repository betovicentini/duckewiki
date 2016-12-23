<?php
set_time_limit(0);
session_start();
//$folder = $_POST['folder'];
$folder = 'botam';

$path = "../".$folder."/img/originais/";
$pthumb = "../".$folder."/img/thumbnails/";
$imgbres = "../".$folder."/img/copias_baixa_resolucao/";	
$lowres = "../".$folder."/img/lowres/";	

$files = scandir($path);
$zi =0;
$ok=0;
foreach ($files as $fname) {
	$ok=0;
	if (!file_exists($pthumb.$fname)) {
	  system("convert ".$path.$fname." -resize x80  ".$pthumb.$fname);
	  $zi++;
	  $ok++;
	}
	if (!file_exists($lowres.$fname)) {
	  //system("convert ".$path.$fname." -resize x400\>  ".$lowres.$fname);
	  //$ok++;

	}
	if (!file_exists($imgbres.$fname)) {
	  //system("convert ".$path.$fname." -resize x2000\>  ".$imgbres.$fname);
	  //$ok++;

	}
	if ($ok>0) {	
		echo $fname."<br>";
	}
	flush();
}
echo $zi;
//header("location: ../".$folder."/index.php");

?>