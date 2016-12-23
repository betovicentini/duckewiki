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
$menu = FALSE;

if ($idd>0 && !empty($tableref)) {
	$qq = "DROP TABLE ".$tbname;
	@mysql_query($qq,$conn);
	$qq = "CREATE TABLE ".$tbname." (SELECT * FROM checklist_pllist WHERE isvalidlocal(GazetteerID,GPSPointID, ".$idd.", '".$tableref."'))"; 
	mysql_query($qq,$conn);
	$update=0;
}

$qq = "SELECT * FROM ".$tbname;
$rr = @mysql_query($qq,$conn);
$nr = @mysql_numrows($rr);
@mysql_free_result($rr);

if (($nr==0 || $update>0)) {
	$qnu = "UPDATE `temp_prog".$tbname."` SET percentage='1'"; 
	mysql_query($qnu);
	session_write_close();

	unset($_SESSION['plothervars']);
	unset($_SESSION['qq']);
	unset($qq);
	$qq = "DROP TABLE ".$tbname;
	$rq = @mysql_query($qq,$conn);

//IF(ABS(pltb.Longitude)>0,pltb.Longitude+0,IF(pltb.GPSPointID>0,gpspt.Longitude+0,IF(ABS(gaz.Longitude)>0,gaz.Longitude+0,NULL))) as LONGITUDE, 
//IF(ABS(pltb.Longitude)>0,pltb.Latitude+0,IF(pltb.GPSPointID>0,gpspt.Latitude+0,IF(ABS(gaz.Longitude)>0,gaz.Latitude+0,NULL))) as LATITUDE, 
//IF(ABS(pltb.Longitude)>0,pltb.Altitude+0,IF(pltb.GPSPointID>0,gpspt.Altitude+0,IF(ABS(gaz.Longitude)>0,gaz.Altitude+0,NULL))) as ALTITUDE,
//$nn = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
$qq = "(". checklistplantasqq($daptraitid,$alturatraitid,$habitotraitid,$statustraitid,$duplicatesTraitID,$quickview,$quicktbname,$checkoleo=0,$traitsilica);
$qwhere = '';
if ($filtro>0) {
	$qwhere =  " JOIN FiltrosSpecs as fl ON fl.PlantaID=pltb.PlantaID WHERE fl.FiltroID=".$filtro.")";
} else {
	if ($detid>0) {
			if ($infspecid>0) {
				$qwhere = " WHERE iddet.InfraEspecieID=".$infspecid;
			} else {
				if ($specid>0) {
					$qwhere = " WHERE iddet.EspecieID=".$specid;
				} else {
					if ($genid>0) {
						$qwhere = " WHERE iddet.GeneroID=".$genid;
					} 
					else {
						$qwhere = " WHERE iddet.FamiliaID=".$famid;
					}
				}
			}		
	}
	if ($quickview>0 && !empty($quicktbname)) {
		$qwhere=" WHERE filtertab.PlantaID>0";
	}
	if ($specimenid>0) {
		$qqz = "SELECT PlantaID FROM Especimenes WHERE EspecimenID=".$specimenid;
		$rr = @mysql_query($qqz);
		$nrr = @mysql_fetch_assoc($rr);
		$pltid = $nrr['PlantaID'];
		$qwhere=" WHERE pltb.PlantaID=".$pltid;
		mysql_free_result($rr);
		
	}

}
$qz = 'SELECT COUNT(*) as nrecs';
if ($quickview>0 && !empty($quicktbname)) {
	$qz .= " FROM ".$quicktbname." as filtertab JOIN Plantas as pltb USING(PlantaID)"; 
} 
else {
	$qz .= " FROM Plantas as pltb";
}
$qz .= "
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID ".$qwhere;
$rz = mysql_query($qz,$conn);
$rwz = mysql_fetch_assoc($rz);
$nrz = $rwz['nrecs'];
mysql_free_result($rz);
//$_SESSION['exportnresult'] = $nrz;
$stepsize = 2000;
$nsteps = ceil($nrz/$stepsize);
$qq = $qq.$qwhere;
$step=0;
//echo $step.'  '.$nsteps;
while ( $step<=$nsteps ) {
	if ($step==0) {
		$st1 = 0;
		$qbase = "CREATE TABLE IF NOT EXISTS ".$tbname;
	} 
	else {
		//$qq = $_SESSION['qq'];
		//$plothervars = unserialize($_SESSION['plothervars']);
		$st1 = $st1+$stepsize;
		$qbase = "INSERT INTO ".$tbname;
	}
	$qqq = $qbase." ".$qq." LIMIT $st1,$stepsize)";
	//echo $qqq."<br />";
	$check = mysql_query($qqq,$conn);
	if ($check) {
		$perc = ceil(($step/$nsteps)*100);
		$qnu = "UPDATE `temp_prog".$tbname."` SET percentage=".$perc; 
		mysql_query($qnu);
		session_write_close();
	}
	$step = $step+1;
} 


if ($step>$nsteps) {
	$qq = "ALTER TABLE ".$tbname." ADD PRIMARY KEY(PlantaID)";
	$rq = mysql_query($qq,$conn);
	//mysql_free_result($rq);
	//$sql = "CREATE INDEX PlantaID ON ".$tbname."  (PlantaID)";
	//$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX FAMILIA ON ".$tbname."  (FAMILIA)";
	$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX NOME ON ".$tbname."  (NOME)";
	$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX NOME ON ".$tbname."  (NOME_AUTOR)";
	$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX PAIS ON ".$tbname."  (PAIS)";
	$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX ESTADO ON ".$tbname."  (ESTADO)";
	$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX MUNICIPIO ON ".$tbname."  (MUNICIPIO)";
	$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX LOCAL ON ".$tbname."  (LOCAL)";
	$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX LOCALSIMPLES ON ".$tbname."  (LOCALSIMPLES)";
	$rq = mysql_query($sql,$conn);
	if ($daptraitid>0) {
		$sql = "CREATE INDEX DAPmm ON ".$tbname."  (DAPmm)";
		$rq = mysql_query($sql,$conn);
	}
	//$sql = "CREATE INDEX GazetteerID ON ".$tbname."  (GazetteerID)";
	//$rq = mysql_query($sql,$conn);
	//$sql = "CREATE INDEX GPSPointID ON ".$tbname."  (GPSPointID)";
	//$rq = mysql_query($sql,$conn);
	//unset($_SESSION['qq']);
	//unset($_SESSION['exportnresult']);
}
$qnu = "UPDATE `temp_prog".$tbname."` SET percentage=100"; 
mysql_query($qnu);
echo "Concluido";
session_write_close();
} 
else {
	//echo 'houve erro geral';
	$qnu = "UPDATE `temp_prog".$tbname."` SET percentage=100"; 
	mysql_query($qnu);
	echo "Concluido";
}
session_write_close();

?>
