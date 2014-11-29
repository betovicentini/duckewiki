<?php
//Start session
//ini_set("memory_limit","10000M");
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) ||  (trim($uuid)=='')) { header("location: access-denied.php"); exit(); } 

$ppost = unserialize($_SESSION['imgpost']);
$plantasids = unserialize($_SESSION['plantasidsimgs']);
$especimenesids = unserialize($_SESSION['especimenesids']);

//echopre($ppost);
//echopre($especimenesids);
//echopre($plantasids);


//CRIE A TABELA IMAGENS SE NAO EXISTIR
$qq = "CREATE TABLE IF NOT EXISTS Imagens ( ImageID INT(10) unsigned NOT NULL auto_increment, FileName VARCHAR(100), DateTimeOriginal VARCHAR(100), DateOriginal DATE, TimeOriginal TIME, Latitude VARCHAR(10), Longitude VARCHAR(10), Altitude VARCHAR(10), GPSMapDatum VARCHAR(10), Autores VARCHAR(20), Camera INT(10), GPSPointID INT(10), Deleted DATE, HabitatPhoto INT(1), AddedBy INT(10), AddedDate DATE, PRIMARY KEY (ImageID)) CHARACTER SET utf8 ENGINE INNODB";
@mysql_query($qq,$conn);
$qq = "ALTER TABLE Imagens ADD COLUMN TraitID INT(10)";
@mysql_query($qq,$conn);

//PEGA AS IMAGENS QUE ESTAO SENDO IMPORTADAS
$tbn = "uploadDir_". $_SESSION['userid'];
$dir = "uploads/batch_images/".$tbn;
$imgs_nomes = scandir($dir);
unset($imgs_nomes[0]);
unset($imgs_nomes[1]);
$imgs_nomes = array_values($imgs_nomes);





$qnu = "UPDATE `temp_imgprogress".$uuid ."` SET percentage=100"; 
mysql_query($qnu,$conn);
$txt = "<table style='font-size: 1em' ><tr><td><b>CONCLUIDO</b>. Foram inseridos:</td></tr>";
if ($counter_specs>0) {
	 $txt .= "<tr><td>".$counter_specs." imagens de especimenes</td></tr>";
}
if ($counter_plantas>0) {
	 $txt .=  "<tr><td>".$counter_plantas." imagens de plantas marcadas </td></tr>";
}
if ($counter_nolink>0) {
	 $txt .=  "<tr><td>".$counter_nolink." imagens sem relacionamento</td></tr>";
}
if ($nao_importou>0) {
	 $txt .=  "<tr><td>".$nao_importou." imagens NAO foram importadas</td></tr>";
}
echo $txt."</table>";
session_write_close();

?>