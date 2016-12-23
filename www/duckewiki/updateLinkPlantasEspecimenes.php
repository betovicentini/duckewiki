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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Updata Link Plantas & Especimenes';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


//$qq = 'SELECT EspecimenID FROM Especimenes as sp JOIN Plantas as pl ON pl.PlantaID=sp.PlantaID WHERE sp.PlantaID>0 AND pl.DetID<>sp.DetID';
//$res = mysql_query($qq,$conn);
//while ($row = mysql_fetch_assoc($res)) {
//	$especimenid = $row['EspecimenID'];
//	CreateorUpdateTableofChanges($especimenid,'EspecimenID','Especimenes',$conn);
//	echo $especimenid."<br />";
//}
//$qq = 'UPDATE Especimenes as sp, Plantas as pl SET sp.DetID=pl.DetID WHERE sp.PlantaID=pl.PlantaID  AND sp.DetID<>pl.DetID';
////mysql_query($qq,$conn);
//
//$qq = 'UPDATE Plantas as pl, Especimenes as sp SET pl.DetID=sp.DetID WHERE sp.PlantaID=pl.PlantaID AND (pl.DetID=0 or pl.DetID IS NULL)';
//mysql_query($qq,$conn);
$qq = 'UPDATE Especimenes as sp, Plantas as pl SET sp.DetID=pl.DetID WHERE sp.PlantaID=pl.PlantaID AND (sp.DetID=0 or sp.DetID IS NULL)';
mysql_query($qq,$conn);
//
//$qq = 'UPDATE Plantas as pl, Especimenes as sp SET pl.GazetteerID=sp.GazetteerID WHERE sp.PlantaID=pl.PlantaID AND (pl.GazetteerID=0 or pl.GazetteerID IS NULL)';
//mysql_query($qq,$conn);
$qq = 'UPDATE Especimenes as sp, Plantas as pl SET sp.GazetteerID=pl.GazetteerID WHERE sp.PlantaID=pl.PlantaID AND (sp.GazetteerID=0 or sp.GazetteerID IS NULL)';
mysql_query($qq,$conn);
//
//$qq = 'UPDATE Plantas as pl, Especimenes as sp SET pl.GPSPointID=sp.GPSPointID WHERE sp.PlantaID=pl.PlantaID AND (pl.GPSPointID=0 or pl.GPSPointID IS NULL)';
//mysql_query($qq,$conn);
//
$qq = 'UPDATE Especimenes as sp, Plantas as pl SET sp.GPSPointID=pl.GPSPointID WHERE sp.PlantaID=pl.PlantaID AND (sp.GPSPointID=0 or sp.GPSPointID IS NULL)';
mysql_query($qq,$conn);
//
//
//$qq = 'UPDATE Plantas as pl, Especimenes as sp SET pl.HabitatID=sp.HabitatID WHERE sp.PlantaID=pl.PlantaID AND (pl.HabitatID=0 or pl.HabitatID IS NULL)';
//mysql_query($qq,$conn);
//$qq = 'UPDATE Especimenes as sp, Plantas as pl SET sp.HabitatID=pl.HabitatID WHERE sp.PlantaID=pl.PlantaID AND (sp.HabitatID=0 or sp.HabitatID IS NULL)';
//mysql_query($qq,$conn);
//
//
//$qq = 'UPDATE Plantas as pl, Especimenes as sp SET pl.Latitude=sp.Latitude WHERE sp.PlantaID=pl.PlantaID AND (pl.Latitude=0 or pl.Latitude IS NULL)';
//mysql_query($qq,$conn);
//$qq = 'UPDATE Especimenes as sp, Plantas as pl SET sp.Latitude=pl.Latitude WHERE sp.PlantaID=pl.PlantaID AND (sp.Latitude=0 or sp.Latitude IS NULL)';
//mysql_query($qq,$conn);
//
//$qq = 'UPDATE Plantas as pl, Especimenes as sp SET pl.Longitude=sp.Latitude WHERE sp.PlantaID=pl.PlantaID AND (pl.Longitude=0 or pl.Longitude IS NULL)';
//mysql_query($qq,$conn);
//$qq = 'UPDATE Especimenes as sp, Plantas as pl SET sp.Longitude=pl.Latitude WHERE sp.PlantaID=pl.PlantaID AND (sp.Longitude=0 or sp.Longitude IS NULL)';
//mysql_query($qq,$conn);
//
//$qq = 'UPDATE Plantas as pl, Especimenes as sp SET pl.Altitude=sp.Altitude WHERE sp.PlantaID=pl.PlantaID AND (pl.Altitude=0 or pl.Altitude IS NULL)';
//mysql_query($qq,$conn);
//$qq = 'UPDATE Especimenes as sp, Plantas as pl SET sp.Altitude=pl.Altitude WHERE sp.PlantaID=pl.PlantaID AND (sp.Altitude=0 or sp.Altitude IS NULL)';
//mysql_query($qq,$conn);
//
//
$qq = 'UPDATE Traits_variation as tvar, Especimenes as sp SET tvar.EspecimenID=sp.EspecimenID WHERE tvar.PlantaID=sp.PlantaID AND (tvar.EspecimenID=0 OR tvar.EspecimenID IS NULL) AND sp.EspecimenID>0';
mysql_query($qq,$conn);
//$qq = 'UPDATE Traits_variation as tvar, Especimenes as sp SET tvar.PlantaID=sp.PlantaID WHERE tvar.EspecimenID=sp.EspecimenID AND (tvar.PlantaID=0 OR tvar.PlantaID IS NULL) AND sp.PlantaID>0';
//mysql_query($qq,$conn);

print("TTTTerminado");

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>