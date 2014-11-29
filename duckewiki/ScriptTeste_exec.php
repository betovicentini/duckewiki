<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

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
"<link href='css/jquery-ui.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$body='';
$title = 'Script Teste Executa';
FazHeader($title,$body,$which_css,$which_java,$menu);

$qq = "DROP TABLE temp_indets_04_2014"; 
$res = mysql_query($qq,$conn);

//$qnu = "UPDATE `temp_scripttesteprogress` SET percentage=5"; 
//mysql_query($qnu);
//session_write_close();

$getol = "SELECT  PlantaID FROM Plantas AS pltb  LEFT JOIN Gazetteer as gaz ON pltb.GazetteerID=gaz.GazetteerID LEFT JOIN Gazetteer as gaz2 ON gaz.ParentID=gaz2.GazetteerID LEFT JOIN Identidade as idd ON idd.DetID=pltb.DetID WHERE ((idd.EspecieID IS NULL) OR idd.EspecieID+0=0)  AND gaz2.Gazetteer LIKE '%25ha%'"; 
$rs = mysql_query($getol,$conn);
$nrs = mysql_numrows($rs);

//echo $getol."<br />";


$qbase = "SELECT  pltb.PlantaID AS WikiPlantaID,  pltb.PlantaTag as TAG,  getidentidade(pltb.DetID,1,0,1,0,0) AS Familia,  getidentidade(pltb.DetID,1,0,0,1,0) AS Genero,  getidentidade(pltb.DetID,1,0,0,0,1) AS Nome, localidadefields(pltb.GazetteerID, pltb.GPSPointID, 'GAZETTEER')  as GAZETTEER, parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'PARGAZ_SPEC')  as PAR_GAZ_SPEC, localidadefields(pltb.GazetteerID, pltb.GPSPointID, 'GAZETTEER_SPEC')  as GAZETTEER_SPEC, parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'STARTX')  as GAZ_Startx, parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'STARTY')  as GAZ_Starty, pltb.X as Pos_X, pltb.Y as Pos_Y, (traitvalueplantas(264, pltb.PlantaID, 'mm', 0, 0)+0) AS DAPmm, (traitvalueplantas(265, pltb.PlantaID, 'm', 0, 0)+0) AS ALTURA, traitvalueplantas(104, pltb.PlantaID, '', 0, 0) AS HABITO, traitvalueplantas(1407, pltb.PlantaID, '', 0, 0) AS STATUS, traitvalueplantas(1120, pltb.PlantaID, '', 0, 0) AS CODIGOS FROM Plantas AS pltb  LEFT JOIN Gazetteer as gaz ON pltb.GazetteerID=gaz.GazetteerID LEFT JOIN Gazetteer as gaz2 ON gaz.ParentID=gaz2.GazetteerID LEFT JOIN Identidade as idd ON idd.DetID=pltb.DetID WHERE ((idd.EspecieID IS NULL) OR idd.EspecieID+0=0)  AND gaz2.Gazetteer LIKE '%25ha%'"; 

$stepsize = 50;

//echo "<br /><br />";

$step=0;
while ($step<$nrs) {
	if ($step==0) {
	 $qq = "CREATE TABLE temp_indets_04_2014 ".$qbase;
	} else {
	 $qq = "INSERT INTO temp_indets_04_2014 ".$qbase;
	}
	$qlimit = " LIMIT ".$step.",".$stepsize;
	$qq = $qq.$qlimit;
	//echo $qq."<br /><br />";
	//session_write_close();
	$rs = mysql_query($qq,$conn);
	if ($rs) {
		$nperc = round((($step+$stepsize)/$nrs)*100,0);
		$qnu = "UPDATE `temp_scripttesteprogress` SET percentage=".$nperc; 
		mysql_query($qnu,$conn);
		session_write_close();
	}
	$step = $step+$stepsize+1;
}

//$qnu = "UPDATE `temp_scripttesteprogress` SET percentage=100"; 
//mysql_query($qnu);
echo "CONCLUIDO";
session_write_close();

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>