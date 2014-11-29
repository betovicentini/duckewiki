<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

//Start session
session_start();

//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
//$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}

$tbname = 'temp_ppp_mortas';

$qq = "DROP TABLE ".$tbname;
$rq = @mysql_query($qq,$conn);
	
$qnu = "UPDATE `temp_ppp_plantas` SET percentage=1"; 
mysql_query($qnu);
session_write_close();
	
$qq = "(SELECT 
PlantaID,
TAGtxt ,
TAG,
traitvalueplantas(".$statustraitid.",pltb.PlantaID,'',0,0) AS STATUS,
traitvalueplantas(".$traitsilica.",pltb.PlantaID,'',0,0) AS SILICA,
LOCALSIMPLES,
LOCAL,
FAMILIA,
NOME
FROM checklist_pllist as pltb";

//echo $qq."<br >";

$qz = 'SELECT COUNT(*) as nrecs FROM checklist_pllist as pltb';
$rz = mysql_query($qz,$conn);
$rwz = mysql_fetch_assoc($rz);
$nrz = $rwz['nrecs'];
$stepsize = 10000;
$nsteps = ceil($nrz/$stepsize);

$step=0;
while ( $step<=$nsteps ) {
	if ($step==0) {
		$st1 = 0;
		$qbase = "CREATE TABLE IF NOT EXISTS ".$tbname;
	} 
	else {
		$st1 = $st1+$stepsize;
		$qbase = "INSERT INTO ".$tbname;
	}
	$qqq = $qbase." ".$qq." LIMIT $st1,$stepsize)";
	//echo $qqq."<br>";
	$check = mysql_query($qqq,$conn);
	if ($check) {
		$perc = ceil(($step/$nsteps)*100);
		$qnu = "UPDATE `temp_ppp_plantas` SET percentage=".$perc; 
		mysql_query($qnu);
		session_write_close();
	}
	$step = $step+1;
} 
if ($step>$nsteps) {
		$qq = "ALTER TABLE ".$tbname." ADD PRIMARY KEY(PlantaID)";
		mysql_query($qq,$conn);
}
	$perc =100;
	$qnu = "UPDATE `temp_ppp_plantas` SET percentage=".$perc; 
	mysql_query($qnu);
	echo "Concluido";

session_write_close();
?>